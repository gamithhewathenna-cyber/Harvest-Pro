<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();
$tenant = tenant_id();
$action = $_GET['action'] ?? 'preview';

try {
  if ($action === 'preview') {
    // Aggregate assignments per employee in period
    $from=$_GET['from']??date('Y-m-01'); $to=$_GET['to']??date('Y-m-t');
    $eid=(int)($_GET['estate_id']??0);
    $w="d.work_date BETWEEN ? AND ? AND d.owner_user_id=?"; $p=[$from,$to,$tenant];
    if($eid){$w.=" AND d.estate_id=?";$p[]=$eid;}
    $st=$db->prepare("SELECT e.id, e.emp_code, e.full_name,
        SUM(CASE WHEN t.is_plucking=1 THEN d.kg*d.rate ELSE 0 END) plucking,
        SUM(CASE WHEN t.is_plucking=0 OR t.is_plucking IS NULL THEN d.rate ELSE 0 END) assignment_pay,
        SUM(d.allowance) allow, SUM(d.deduction) ded, SUM(d.kg) kg, COUNT(*) days
      FROM daily_assignments d
      JOIN employees e ON e.id=d.employee_id
      LEFT JOIN assignment_types t ON t.id=d.assignment_type_id
      WHERE $w GROUP BY e.id ORDER BY e.emp_code");
    $st->execute($p);
    $rows=[];
    foreach($st as $r){
      $gross=$r['plucking']+$r['assignment_pay']+$r['allow'];
      $net=$gross-$r['ded'];
      $rows[]=['employee_id'=>$r['id'],'emp_code'=>$r['emp_code'],'name'=>$r['full_name'],
        'kg'=>(float)$r['kg'],'days'=>(int)$r['days'],
        'plucking'=>(float)$r['plucking'],'assignment_pay'=>(float)$r['assignment_pay'],
        'allowances'=>(float)$r['allow'],'deductions'=>(float)$r['ded'],
        'gross'=>(float)$gross,'net'=>(float)$net];
    }
    ok(['rows'=>$rows,'from'=>$from,'to'=>$to]);
  }

  if (!can_edit()) fail('Permission denied');
  $b=body(); if(!check_csrf($b['csrf']??'')) fail('Invalid session token');

  if ($action==='generate') {
    $rows=$b['rows']??[]; $from=$b['from']; $to=$b['to'];
    $stmt=$db->prepare("INSERT INTO payroll
      (owner_user_id,employee_id,estate_id,period_from,period_to,plucking_pay,assignment_pay,allowances,deductions,gross,net,status)
      VALUES (?,?,?,?,?,?,?,?,?,?,?, 'Calculated')");
    $db->beginTransaction(); $n=0;
    foreach($rows as $r){
      $stmt->execute([$tenant,(int)$r['employee_id'],($b['estate_id']?:null),$from,$to,
        num($r['plucking']),num($r['assignment_pay']),num($r['allowances']),num($r['deductions']),num($r['gross']),num($r['net'])]);
      $n++;
    }
    $db->commit();
    ok(['saved'=>$n]);
  }

  if ($action==='list') {
    $st=$db->prepare("SELECT pr.*, e.emp_code, e.full_name FROM payroll pr JOIN employees e ON e.id=pr.employee_id WHERE pr.owner_user_id=? ORDER BY pr.id DESC LIMIT 500");
    $st->execute([$tenant]);
    ok(['rows'=>$st->fetchAll()]);
  }

  if (in_array($action, ['approve','pay','delete'], true)) {
    $st=$db->prepare('SELECT owner_user_id FROM payroll WHERE id=?'); $st->execute([(int)($b['id']??0)]);
    $owner = $st->fetchColumn();
    if ($owner === false || (int)$owner !== $tenant) fail('Record not found');
  }

  if ($action==='approve') {
    if(!can_approve()) fail('Not authorized to approve');
    $u=current_user();
    $db->prepare("UPDATE payroll SET status='Approved', approved_by=?, approved_date=CURDATE() WHERE id=?")
       ->execute([$u['name'],(int)$b['id']]);
    ok();
  }
  if ($action==='pay') {
    if(!can_approve()) fail('Not authorized');
    $db->prepare("UPDATE payroll SET status='Paid', paid_date=CURDATE(), payment_method=?, reference=? WHERE id=?")
       ->execute([s($b['method']??'Cash'),s($b['reference']??null),(int)$b['id']]);
    ok();
  }
  if ($action==='delete') {
    $db->prepare("DELETE FROM payroll WHERE id=?")->execute([(int)$b['id']]);
    ok();
  }
  fail('Unknown action');
} catch (Throwable $e) { if($db->inTransaction())$db->rollBack(); fail($e->getMessage()); }
