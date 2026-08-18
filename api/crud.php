<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();

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

$table = $_GET['table'] ?? '';
if (!isset($TABLES[$table])) fail('Invalid table');
$cols = $TABLES[$table];
$action = $_GET['action'] ?? 'list';

try {
  if ($action === 'list') {
    $where = []; $params = [];
    foreach (['estate_id','section_id','status'] as $f) {
      if (isset($_GET[$f]) && $_GET[$f] !== '') { $where[]="$f=?"; $params[]=$_GET[$f]; }
    }
    if (!empty($_GET['q']) && in_array($table,['employees','estates','expenses'])) {
      $qcol = $table==='employees'?'full_name':($table==='estates'?'name':'description');
      $where[]="$qcol LIKE ?"; $params[]='%'.$_GET['q'].'%';
    }
    $w = $where ? ' WHERE '.implode(' AND ',$where) : '';
    $order = in_array('created_at',$cols) ? '' : '';
    $st = $db->prepare("SELECT * FROM `$table`$w ORDER BY id DESC LIMIT 500");
    $st->execute($params);
    ok(['rows'=>$st->fetchAll()]);
  }

  if ($action === 'get') {
    $st=$db->prepare("SELECT * FROM `$table` WHERE id=?"); $st->execute([(int)$_GET['id']]);
    ok(['row'=>$st->fetch()]);
  }

  // Writes below
  if (!can_edit()) fail('Permission denied');
  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'save') {
    // users role protection
    if ($table==='users' && !can_admin()) fail('Only admins manage users');
    $data = [];
    foreach ($cols as $c) if (array_key_exists($c,$b)) $data[$c] = ($b[$c]==='')?null:$b[$c];
    if (!empty($b['id'])) {
      $set = implode(',', array_map(fn($c)=>"`$c`=?", array_keys($data)));
      $st=$db->prepare("UPDATE `$table` SET $set WHERE id=?");
      $st->execute([...array_values($data), (int)$b['id']]);
      ok(['id'=>(int)$b['id']]);
    } else {
      $c = array_keys($data);
      $st=$db->prepare("INSERT INTO `$table` (".implode(',',array_map(fn($x)=>"`$x`",$c)).") VALUES (".implode(',',array_fill(0,count($c),'?')).")");
      $st->execute(array_values($data));
      ok(['id'=>$db->lastInsertId()]);
    }
  }

  if ($action === 'delete') {
    if (in_array($table,['users']) && !can_admin()) fail('Permission denied');
    $st=$db->prepare("DELETE FROM `$table` WHERE id=?"); $st->execute([(int)($b['id']??0)]);
    ok();
  }

  if ($action === 'set_password' && $table==='users') {
    if (!can_admin()) fail('Permission denied');
    $h = password_hash($b['password'] ?? 'changeme123', PASSWORD_DEFAULT);
    $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$h,(int)$b['id']]);
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
