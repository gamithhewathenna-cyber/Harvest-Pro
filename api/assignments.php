<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();
$tenant = tenant_id();
$action = $_GET['action'] ?? 'list';

function calc_cost($type_plucking, $kg, $rate, $allow, $ded) {
  $pay = $type_plucking ? ($kg * $rate) : $rate; // plucking = kg*rate; else rate is flat day cost
  return $pay + $allow - $ded;
}

function assert_owns_estate($db, $estateId, $tenant) {
  $st = $db->prepare('SELECT 1 FROM estates WHERE id=? AND owner_user_id=?');
  $st->execute([(int)$estateId, $tenant]);
  if (!$st->fetchColumn()) fail('Invalid estate');
}

try {
  if ($action === 'list') {
    $w=['d.owner_user_id=?']; $p=[$tenant];
    foreach(['estate_id','section_id','employee_id'] as $f)
      if(!empty($_GET[$f])){$w[]="d.$f=?";$p[]=$_GET[$f];}
    if(!empty($_GET['from'])){$w[]="d.work_date>=?";$p[]=$_GET['from'];}
    if(!empty($_GET['to'])){$w[]="d.work_date<=?";$p[]=$_GET['to'];}
    $ws=' WHERE '.implode(' AND ',$w);
    $st=$db->prepare("SELECT d.*, e.full_name, e.emp_code, s.name section_name, es.name estate_name
      FROM daily_assignments d
      JOIN employees e ON e.id=d.employee_id
      LEFT JOIN sections s ON s.id=d.section_id
      LEFT JOIN estates es ON es.id=d.estate_id
      $ws ORDER BY d.work_date DESC, d.id DESC LIMIT 500");
    $st->execute($p);
    ok(['rows'=>$st->fetchAll()]);
  }

  if (!can_edit()) fail('Permission denied');
  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'save') {
    assert_owns_estate($db, $b['estate_id'], $tenant);
    if (!empty($b['id'])) {
      $st=$db->prepare('SELECT owner_user_id FROM daily_assignments WHERE id=?'); $st->execute([(int)$b['id']]);
      $owner=$st->fetchColumn();
      if ($owner===false || (int)$owner!==$tenant) fail('Record not found');
    }
    $isPluck = !empty($b['is_plucking']);
    $kg=num($b['kg']); $rate=num($b['rate']); $allow=num($b['allowance']); $ded=num($b['deduction']);
    $cost=calc_cost($isPluck,$kg,$rate,$allow,$ded);
    $fields=['work_date','estate_id','section_id','employee_id','assignment_type_id','assignment_type',
             'start_time','end_time','kg','rate','allowance','deduction','cost','supervisor','status','notes'];
    $vals=[s($b['work_date']),(int)$b['estate_id'],($b['section_id']?:null),(int)$b['employee_id'],
           ($b['assignment_type_id']?:null),s($b['assignment_type']),($b['start_time']?:null),($b['end_time']?:null),
           $kg,$rate,$allow,$ded,$cost,s($b['supervisor']??null),s($b['status']??'Recorded'),s($b['notes']??null)];
    if(!empty($b['id'])){
      $set=implode(',',array_map(fn($c)=>"$c=?",$fields));
      $db->prepare("UPDATE daily_assignments SET $set WHERE id=?")->execute([...$vals,(int)$b['id']]);
      ok(['id'=>(int)$b['id']]);
    }
    $fields[]='owner_user_id'; $vals[]=$tenant;
    $db->prepare("INSERT INTO daily_assignments (".implode(',',$fields).") VALUES (".implode(',',array_fill(0,count($fields),'?')).")")->execute($vals);
    ok(['id'=>$db->lastInsertId()]);
  }

  if ($action === 'bulk') {
    $rows = $b['rows'] ?? [];
    if (!$rows) fail('No rows to save');
    assert_owns_estate($db, $b['estate_id'], $tenant);
    $isPluck = !empty($b['is_plucking']);
    $stmt=$db->prepare("INSERT INTO daily_assignments
      (owner_user_id,work_date,estate_id,section_id,employee_id,assignment_type_id,assignment_type,kg,rate,allowance,deduction,cost,supervisor,status)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $db->beginTransaction(); $n=0;
    foreach($rows as $r){
      $kg=num($r['kg']); $rate=num($r['rate']); $allow=num($r['allowance']??0); $ded=num($r['deduction']??0);
      if($kg==0 && $rate==0) continue;
      $cost=calc_cost($isPluck,$kg,$rate,$allow,$ded);
      $stmt->execute([$tenant,s($b['work_date']),(int)$b['estate_id'],($b['section_id']?:null),(int)$r['employee_id'],
        ($b['assignment_type_id']?:null),s($b['assignment_type']),$kg,$rate,$allow,$ded,$cost,s($b['supervisor']??null),'Recorded']);
      $n++;
    }
    $db->commit();
    ok(['saved'=>$n]);
  }

  if ($action === 'delete') {
    $st=$db->prepare('SELECT owner_user_id FROM daily_assignments WHERE id=?'); $st->execute([(int)($b['id']??0)]);
    $owner=$st->fetchColumn();
    if ($owner===false || (int)$owner!==$tenant) fail('Record not found');
    $db->prepare("DELETE FROM daily_assignments WHERE id=?")->execute([(int)($b['id']??0)]);
    ok();
  }
  fail('Unknown action');
} catch (Throwable $e) { if($db->inTransaction())$db->rollBack(); fail($e->getMessage()); }
