<?php
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/coupons.php';
api_require_login();
$db = db();
$tenant = tenant_id();

// Whitelisted tables and their writable columns
$TABLES = [
  'estates' => ['name','code','location','total_acres','tea_acres','description','manager','status'],
  'sections'=> ['estate_id','name','code','acres','clone','num_plants','planted_date','status','notes'],
  'employees'=>['emp_code','full_name','nic','phone','address','gender','dob','joining_date','employment_type','job_role','estate_id','section_id','daily_rate','kg_rate','overtime_rate','bank_details','emergency_contact','status','notes'],
  'expenses'=>['expense_date','estate_id','section_id','category_id','category','supplier','description','quantity','amount','payment_method','reference','status','entered_by','approved_by','notes'],
  'reminders'=>['title','description','type','estate_id','section_id','due_date','priority','assigned_user','status'],
  'service_cycles'=>['service_name','description','unit_type','rate_per_unit','status'],
  'fertilizer_cycles'=>['estate_id','section_id','fertilizer_type','date_applied','next_due','quantity','cost','supplier','applied_by','notes'],
  'clearing_cycles'=>['estate_id','section_id','date_cleared','next_due','assigned_workers','cost','notes'],
  'tea_clones'=>['name','code','description'],
  'users'=>['name','email','phone','address','role','assigned_estate_ids','status'],
];
// Reference data shared across every tenant - not scoped by owner_user_id
$GLOBAL_TABLES = ['tea_clones'];

$table = $_GET['table'] ?? '';
if (!isset($TABLES[$table])) fail('Invalid table');
$cols = $TABLES[$table];
$action = $_GET['action'] ?? 'list';
$isGlobal = in_array($table, $GLOBAL_TABLES, true);
$isUsers = $table === 'users';

/** True if a `users` row belongs to the current tenant (the root Owner or one of their sub-users) */
function user_row_in_tenant($row, $tenant) {
    return (int)$row['id'] === $tenant || (int)$row['owner_user_id'] === $tenant;
}

// Never send password_hash to the browser
$selectCols = $isUsers
  ? 'id,name,email,phone,address,role,assigned_estate_ids,owner_user_id,is_platform_admin,status,avatar,last_login,created_at'
  : '*';

try {
  if ($action === 'list') {
    $where = []; $params = [];
    if ($isUsers) {
      $where[] = '(id=? OR owner_user_id=?)'; $params[]=$tenant; $params[]=$tenant;
    } elseif (!$isGlobal) {
      $where[] = 'owner_user_id=?'; $params[]=$tenant;
    }
    foreach (['estate_id','section_id','status'] as $f) {
      if (isset($_GET[$f]) && $_GET[$f] !== '') { $where[]="$f=?"; $params[]=$_GET[$f]; }
    }
    if (!empty($_GET['q']) && in_array($table,['employees','estates','expenses'])) {
      $qcol = $table==='employees'?'full_name':($table==='estates'?'name':'description');
      $where[]="$qcol LIKE ?"; $params[]='%'.$_GET['q'].'%';
    }
    $w = $where ? ' WHERE '.implode(' AND ',$where) : '';
    $st = $db->prepare("SELECT $selectCols FROM `$table`$w ORDER BY id DESC LIMIT 500");
    $st->execute($params);
    ok(['rows'=>$st->fetchAll()]);
  }

  if ($action === 'get') {
    if ($isUsers) {
      $st=$db->prepare("SELECT $selectCols FROM `$table` WHERE id=? AND (id=? OR owner_user_id=?)");
      $st->execute([(int)$_GET['id'], $tenant, $tenant]);
    } elseif ($isGlobal) {
      $st=$db->prepare("SELECT $selectCols FROM `$table` WHERE id=?"); $st->execute([(int)$_GET['id']]);
    } else {
      $st=$db->prepare("SELECT $selectCols FROM `$table` WHERE id=? AND owner_user_id=?");
      $st->execute([(int)$_GET['id'], $tenant]);
    }
    ok(['row'=>$st->fetch()]);
  }

  // Writes below
  if (!can_edit()) fail('Permission denied');
  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'save') {
    if ($table==='users' && !can_admin()) fail('Only admins manage users');
    $isInsert = empty($b['id']);

    // Ownership check on update - stops one tenant editing another tenant's row by guessing an id
    if (!$isInsert && !$isGlobal) {
      $st = $db->prepare("SELECT id, owner_user_id FROM `$table` WHERE id=?");
      $st->execute([(int)$b['id']]);
      $existing = $st->fetch();
      if (!$existing) fail('Record not found');
      $allowed = $isUsers ? user_row_in_tenant($existing, $tenant) : ((int)$existing['owner_user_id'] === $tenant);
      if (!$allowed) fail('Permission denied');
    }

    $data = [];
    foreach ($cols as $c) if (array_key_exists($c,$b)) $data[$c] = ($b[$c]==='')?null:$b[$c];

    // If this record references an estate, make sure that estate actually belongs to this tenant
    if (array_key_exists('estate_id',$data) && $data['estate_id'] !== null) {
      $chk = $db->prepare('SELECT 1 FROM estates WHERE id=? AND owner_user_id=?');
      $chk->execute([(int)$data['estate_id'], $tenant]);
      if (!$chk->fetchColumn()) fail('Invalid estate');
    }

    if (!$isInsert) {
      $set = implode(',', array_map(fn($c)=>"`$c`=?", array_keys($data)));
      $st=$db->prepare("UPDATE `$table` SET $set WHERE id=?");
      $st->execute([...array_values($data), (int)$b['id']]);
      ok(['id'=>(int)$b['id']]);
    }

    // New estate: requires a fresh, unused coupon, consumed atomically with the insert
    if ($table === 'estates') {
      $couponCode = trim($b['coupon_code'] ?? '');
      if ($couponCode === '') fail('A valid coupon code is required to add a new estate');
      $db->beginTransaction();
      try {
        $c = array_keys($data); $c[] = 'owner_user_id';
        $vals = array_values($data); $vals[] = $tenant;
        $st=$db->prepare("INSERT INTO `estates` (".implode(',',array_map(fn($x)=>"`$x`",$c)).") VALUES (".implode(',',array_fill(0,count($c),'?')).")");
        $st->execute($vals);
        $newId = (int)$db->lastInsertId();
        consume_coupon($db, $couponCode, current_user()['id'], $newId);
        $db->commit();
        ok(['id'=>$newId]);
      } catch (Throwable $e) {
        $db->rollBack();
        fail($e->getMessage());
      }
    }

    if (!$isGlobal) $data['owner_user_id'] = $tenant; // server sets this - never trust the client
    $c = array_keys($data);
    $st=$db->prepare("INSERT INTO `$table` (".implode(',',array_map(fn($x)=>"`$x`",$c)).") VALUES (".implode(',',array_fill(0,count($c),'?')).")");
    $st->execute(array_values($data));
    ok(['id'=>$db->lastInsertId()]);
  }

  if ($action === 'delete') {
    if ($isUsers && !can_admin()) fail('Permission denied');
    if (!$isGlobal) {
      $st = $db->prepare("SELECT id, owner_user_id FROM `$table` WHERE id=?");
      $st->execute([(int)($b['id']??0)]);
      $existing = $st->fetch();
      if (!$existing) fail('Record not found');
      $allowed = $isUsers ? user_row_in_tenant($existing, $tenant) : ((int)$existing['owner_user_id'] === $tenant);
      if (!$allowed) fail('Permission denied');
    }
    $st=$db->prepare("DELETE FROM `$table` WHERE id=?"); $st->execute([(int)($b['id']??0)]);
    ok();
  }

  if ($action === 'set_password' && $table==='users') {
    if (!can_admin()) fail('Permission denied');
    $st=$db->prepare("SELECT id, owner_user_id FROM users WHERE id=?"); $st->execute([(int)$b['id']]);
    $existing = $st->fetch();
    if (!$existing || !user_row_in_tenant($existing, $tenant)) fail('Permission denied');
    $h = password_hash($b['password'] ?? 'changeme123', PASSWORD_DEFAULT);
    $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$h,(int)$b['id']]);
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { if($db->inTransaction())$db->rollBack(); fail($e->getMessage()); }
