<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$what = $_GET['what'] ?? '';
$db = db();
$tenant = tenant_id();
try {
  switch ($what) {
    case 'estates':
      $st=$db->prepare('SELECT id,name,code FROM estates WHERE owner_user_id=? ORDER BY name'); $st->execute([$tenant]);
      ok(['rows'=>$st->fetchAll()]);
    case 'sections':
      $eid = (int)($_GET['estate_id'] ?? 0);
      if ($eid) { $st=$db->prepare('SELECT id,name,code FROM sections WHERE estate_id=? AND owner_user_id=? ORDER BY name'); $st->execute([$eid,$tenant]); }
      else { $st=$db->prepare('SELECT id,name,code,estate_id FROM sections WHERE owner_user_id=? ORDER BY name'); $st->execute([$tenant]); }
      ok(['rows'=>$st->fetchAll()]);
    case 'employees':
      $eid=(int)($_GET['estate_id']??0);
      if($eid){$st=$db->prepare('SELECT id,emp_code,full_name,kg_rate,daily_rate FROM employees WHERE status="Active" AND estate_id=? AND owner_user_id=? ORDER BY emp_code');$st->execute([$eid,$tenant]);}
      else {$st=$db->prepare('SELECT id,emp_code,full_name,kg_rate,daily_rate FROM employees WHERE status="Active" AND owner_user_id=? ORDER BY emp_code');$st->execute([$tenant]);}
      ok(['rows'=>$st->fetchAll()]);
    case 'assignment_types':
      ok(['rows'=>$db->query('SELECT id,name,is_plucking FROM assignment_types ORDER BY id')->fetchAll()]);
    case 'expense_categories':
      ok(['rows'=>$db->query('SELECT id,name FROM expense_categories ORDER BY name')->fetchAll()]);
    case 'clones':
      ok(['rows'=>$db->query('SELECT id,name,code FROM tea_clones ORDER BY name')->fetchAll()]);
    default: fail('Unknown lookup');
  }
} catch (Throwable $e) { fail($e->getMessage()); }
