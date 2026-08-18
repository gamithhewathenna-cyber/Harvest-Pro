<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();
$tenant = tenant_id();
$type = $_GET['type'] ?? '';
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-t');
$eid  = (int)($_GET['estate_id'] ?? 0);
$eW = " AND d.owner_user_id=$tenant".($eid ? " AND d.estate_id=$eid" : '');
$eWe = " AND owner_user_id=$tenant".($eid ? " AND estate_id=$eid" : '');
$export = $_GET['export'] ?? '';

try {
  $cols=[]; $rows=[]; $title='';
  switch ($type) {
    case 'harvest':
      $title='Harvest Report (KG by Section)';
      $cols=['Estate','Section','Total KG','Assignments'];
      $st=$db->prepare("SELECT es.name estate, s.name section, SUM(d.kg) kg, COUNT(*) c
        FROM daily_assignments d JOIN sections s ON s.id=d.section_id JOIN estates es ON es.id=d.estate_id
        WHERE d.work_date BETWEEN ? AND ?$eW GROUP BY s.id ORDER BY kg DESC");
      $st->execute([$from,$to]);
      foreach($st as $r) $rows[]=[$r['estate'],$r['section'],number_format($r['kg'],2),$r['c']];
      break;
    case 'worker':
      $title='Worker Performance';
      $cols=['Emp ID','Worker','Days','Assignments','Total KG','Avg KG/Day','Labour Cost'];
      $st=$db->prepare("SELECT e.emp_code, e.full_name, COUNT(DISTINCT d.work_date) days, COUNT(*) asg,
        SUM(d.kg) kg, SUM(d.cost) cost FROM daily_assignments d JOIN employees e ON e.id=d.employee_id
        WHERE d.work_date BETWEEN ? AND ?$eW GROUP BY e.id ORDER BY kg DESC");
      $st->execute([$from,$to]);
      foreach($st as $r){$avg=$r['days']?$r['kg']/$r['days']:0;
        $rows[]=[$r['emp_code'],$r['full_name'],$r['days'],$r['asg'],number_format($r['kg'],2),number_format($avg,2),number_format($r['cost'],2)];}
      break;
    case 'section':
      $title='Section Performance';
      $cols=['Section','Total KG','Labour Cost','Cost per KG'];
      $st=$db->prepare("SELECT s.name, SUM(d.kg) kg, SUM(d.cost) cost FROM sections s
        LEFT JOIN daily_assignments d ON d.section_id=s.id AND d.work_date BETWEEN ? AND ? AND d.owner_user_id=$tenant
        WHERE s.owner_user_id=$tenant".($eid?" AND s.estate_id=$eid":'')." GROUP BY s.id ORDER BY kg DESC");
      $st->execute([$from,$to]);
      foreach($st as $r){$cpk=$r['kg']>0?$r['cost']/$r['kg']:0;
        $rows[]=[$r['name'],number_format($r['kg'],2),number_format($r['cost'],2),number_format($cpk,2)];}
      break;
    case 'expense':
      $title='Expense Report';
      $cols=['Date','Category','Estate','Supplier','Amount'];
      $st=$db->prepare("SELECT expense_date, category, (SELECT name FROM estates WHERE id=expenses.estate_id) est, supplier, amount
        FROM expenses WHERE expense_date BETWEEN ? AND ?$eWe ORDER BY expense_date DESC");
      $st->execute([$from,$to]);
      foreach($st as $r) $rows[]=[$r['expense_date'],$r['category'],$r['est'],$r['supplier'],number_format($r['amount'],2)];
      break;
    case 'payroll':
      $title='Payroll Report';
      $cols=['Worker','Period','Gross','Deductions','Net','Status'];
      $st=$db->query("SELECT e.full_name, pr.period_from, pr.period_to, pr.gross, pr.deductions, pr.net, pr.status
        FROM payroll pr JOIN employees e ON e.id=pr.employee_id WHERE pr.owner_user_id=$tenant ORDER BY pr.id DESC");
      foreach($st as $r) $rows[]=[$r['full_name'],$r['period_from'].' - '.$r['period_to'],
        number_format($r['gross'],2),number_format($r['deductions'],2),number_format($r['net'],2),$r['status']];
      break;
    case 'profit':
      $title='Profitability Report';
      $price=(float)($db->query("SELECT svalue FROM settings WHERE skey='tea_price_per_kg' AND owner_user_id=$tenant")->fetchColumn() ?: 0);
      if(isset($_GET['price']) && $_GET['price']!=='') $price=(float)$_GET['price'];
      $kg=$db->prepare("SELECT COALESCE(SUM(kg),0) FROM daily_assignments d WHERE work_date BETWEEN ? AND ?$eW"); $kg->execute([$from,$to]); $kg=(float)$kg->fetchColumn();
      $pay=$db->prepare("SELECT COALESCE(SUM(cost),0) FROM daily_assignments d WHERE work_date BETWEEN ? AND ?$eW"); $pay->execute([$from,$to]); $pay=(float)$pay->fetchColumn();
      $exp=$db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?$eWe"); $exp->execute([$from,$to]); $exp=(float)$exp->fetchColumn();
      $rev=$kg*$price; $profit=$rev-$pay-$exp;
      $cols=['Metric','Value'];
      $rows=[['Total KG Harvested',number_format($kg,2)],['Tea Price / KG',number_format($price,2)],
        ['Estimated Revenue',number_format($rev,2)],['Payroll (Labour)',number_format($pay,2)],
        ['Other Expenses',number_format($exp,2)],['Estimated Operating Profit',number_format($profit,2)]];
      break;
    default: fail('Unknown report');
  }

  if ($export === 'csv') {
    $u=current_user();
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="'.$type.'_report.csv"');
    $out=fopen('php://output','w');
    fputcsv($out,[$title]); fputcsv($out,['Range: '.$from.' to '.$to,'Generated: '.date('Y-m-d H:i'),'By: '.$u['name']]);
    fputcsv($out,$cols);
    foreach($rows as $r) fputcsv($out,$r);
    fclose($out); exit;
  }
  ok(['title'=>$title,'cols'=>$cols,'rows'=>$rows,'from'=>$from,'to'=>$to]);
} catch (Throwable $e) { fail($e->getMessage()); }
