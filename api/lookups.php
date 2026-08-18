<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$what = $_GET['what'] ?? '';
$db = db();
try {
  switch ($what) {
    case 'estates':
      ok(['rows'=>$db->query('SELECT id,name,code FROM estates ORDER BY name')->fetchAll()]);
    case 'sections':
      $eid = (int)($_GET['estate_id'] ?? 0);
      if ($eid) { $st=$db->prepare('SELECT id,name,code FROM sections WHERE estate_id=? ORDER BY name'); $st->execute([$eid]); }
      else $st=$db->query('SELECT id,name,code,estate_id FROM sections ORDER BY name');
      ok(['rows'=>$st->fetchAll()]);
    case 'employees':
      $eid=(int)($_GET['estate_id']??0);
      if($eid){$st=$db->prepare('SELECT id,emp_code,full_name,kg_rate,daily_rate FROM employees WHERE status="Active" AND estate_id=? ORDER BY emp_code');$st->execute([$eid]);}
      else $st=$db->query('SELECT id,emp_code,full_name,kg_rate,daily_rate FROM employees WHERE status="Active" ORDER BY emp_code');
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
