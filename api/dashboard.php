<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();

$range = $_GET['range'] ?? 'month';
$estate = (int)($_GET['estate'] ?? 0);
$today = date('Y-m-d');
switch ($range) {
  case 'today': $from=$to=$today; break;
  case 'week':  $from=date('Y-m-d', strtotime('monday this week')); $to=$today; break;
  case 'year':  $from=date('Y-01-01'); $to=date('Y-12-31'); break;
  case 'custom': $from=$_GET['from']??$today; $to=$_GET['to']??$today; break;
  default:      $from=date('Y-m-01'); $to=date('Y-m-t'); break; // month
}
$eWhere = $estate ? ' AND estate_id='.$estate : '';

// KPIs
$kg = $db->query("SELECT COALESCE(SUM(kg),0) FROM daily_assignments WHERE work_date BETWEEN '$from' AND '$to'$eWhere")->fetchColumn();
$payrollCost = $db->query("SELECT COALESCE(SUM(cost),0) FROM daily_assignments WHERE work_date BETWEEN '$from' AND '$to'$eWhere")->fetchColumn();
$asgCount = $db->query("SELECT COUNT(*) FROM daily_assignments WHERE work_date BETWEEN '$from' AND '$to'$eWhere")->fetchColumn();
$exp = $db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN '$from' AND '$to'$eWhere")->fetchColumn();
$expCount = $db->query("SELECT COUNT(*) FROM expenses WHERE expense_date BETWEEN '$from' AND '$to'$eWhere")->fetchColumn();
$activeW = $db->query("SELECT COUNT(*) FROM employees WHERE status='Active'".($estate?" AND estate_id=$estate":''))->fetchColumn();
$totalW = $db->query("SELECT COUNT(*) FROM employees".($estate?" WHERE estate_id=$estate":''))->fetchColumn();

// Monthly payroll vs expenses (selected year)
$year = (int)($_GET['year'] ?? date('Y'));
$payM = array_fill(0,12,0); $expM = array_fill(0,12,0);
foreach($db->query("SELECT MONTH(work_date) m, SUM(cost) c FROM daily_assignments WHERE YEAR(work_date)=$year$eWhere GROUP BY m") as $r) $payM[$r['m']-1]=(float)$r['c'];
foreach($db->query("SELECT MONTH(expense_date) m, SUM(amount) c FROM expenses WHERE YEAR(expense_date)=$year$eWhere GROUP BY m") as $r) $expM[$r['m']-1]=(float)$r['c'];

// Harvest by section
$sec = $db->query("SELECT s.name, COALESCE(SUM(d.kg),0) kg
  FROM sections s LEFT JOIN daily_assignments d ON d.section_id=s.id AND d.work_date BETWEEN '$from' AND '$to'
  ".($estate?"WHERE s.estate_id=$estate":'')."
  GROUP BY s.id ORDER BY kg DESC LIMIT 12")->fetchAll();

// Expense breakdown by category
$expCat = $db->query("SELECT COALESCE(category,'Other') cat, SUM(amount) amt FROM expenses
  WHERE expense_date BETWEEN '$from' AND '$to'$eWhere GROUP BY cat ORDER BY amt DESC")->fetchAll();

// Top workers
$topW = $db->query("SELECT e.full_name, COALESCE(SUM(d.kg),0) kg FROM daily_assignments d
  JOIN employees e ON e.id=d.employee_id WHERE d.work_date BETWEEN '$from' AND '$to'$eWhere
  GROUP BY e.id ORDER BY kg DESC LIMIT 5")->fetchAll();

// Upcoming events (reminders + due cycles)
$events=[];
foreach($db->query("SELECT title, due_date, priority, 'Reminder' typ FROM reminders WHERE status='Open' AND due_date>='$today' ORDER BY due_date LIMIT 8") as $r)
  $events[]=['title'=>$r['title'],'due'=>$r['due_date'],'type'=>'General'];
foreach($db->query("SELECT CONCAT('Fertilizer: ',fertilizer_type) title, next_due FROM fertilizer_cycles WHERE next_due>='$today' ORDER BY next_due LIMIT 5") as $r)
  $events[]=['title'=>$r['title'],'due'=>$r['next_due'],'type'=>'Fertilizer'];
usort($events, fn($a,$b)=>strcmp($a['due'],$b['due']));
$events=array_slice($events,0,8);
foreach($events as &$ev){
  $days=(int)((strtotime($ev['due'])-strtotime($today))/86400);
  $ev['days']=$days;
  $ev['status']=$days<0?'Overdue':($days==0?'Due Today':($days<=3?'Due Soon':'Upcoming'));
}

// extra analytics
$acres = $db->query("SELECT COALESCE(SUM(tea_acres),0) FROM estates".($estate?" WHERE id=$estate":''))->fetchColumn();
$costPerKg = $kg>0 ? $payrollCost/$kg : 0;
$avgPerWorker = $activeW>0 ? $kg/$activeW : 0;

ok([
  'range'=>['from'=>$from,'to'=>$to],
  'kpi'=>[
    'active_workers'=>(int)$activeW,'total_workers'=>(int)$totalW,
    'kg'=>(float)$kg,'payroll'=>(float)$payrollCost,'assignments'=>(int)$asgCount,
    'expenses'=>(float)$exp,'expense_count'=>(int)$expCount,
    'cost_per_kg'=>$costPerKg,'avg_per_worker'=>$avgPerWorker,'tea_acres'=>(float)$acres
  ],
  'chart'=>['payroll'=>$payM,'expenses'=>$expM,'year'=>$year],
  'sections'=>$sec,'expense_cat'=>$expCat,'top_workers'=>$topW,'events'=>$events
]);
