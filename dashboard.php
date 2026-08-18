<?php $title='Dashboard'; $page='dashboard.php'; $pageScript='assets/js/dashboard.js';
require __DIR__.'/includes/header.php'; ?>

<div class="page-head">
  <div class="filters" style="margin:0">
    <select id="estateSel" class="chip" style="border-radius:20px"><option value="0">All Estates</option></select>
    <span class="chip active" data-range="today">Today</span>
    <span class="chip" data-range="week">This Week</span>
    <span class="chip active" data-range="month">This Month</span>
    <span class="chip" data-range="year">This Year</span>
    <span class="chip" data-range="custom">Custom</span>
    <span id="customBox" style="display:none;gap:8px;align-items:center">
      <input type="date" id="fromD" class="chip" style="padding:6px 10px">
      <input type="date" id="toD" class="chip" style="padding:6px 10px">
      <button class="btn sm" onclick="load()">Apply</button>
    </span>
  </div>
</div>

<!-- Quick actions -->
<div class="grid g4 mb">
  <div class="card qa" onclick="location.href='assignments.php'"><div class="qa-ic">➕</div><div><strong>New Daily Assignment</strong><small>Record today's work</small></div></div>
  <div class="card qa" onclick="location.href='expenses.php'"><div class="qa-ic">🧾</div><div><strong>Add Expense</strong><small>Log an estate expense</small></div></div>
  <div class="card qa" onclick="location.href='employees.php'"><div class="qa-ic">👷</div><div><strong>Add Worker</strong><small>Register an employee</small></div></div>
  <div class="card qa" onclick="location.href='reports.php'"><div class="qa-ic">📊</div><div><strong>View Reports</strong><small>Generate estate reports</small></div></div>
</div>

<!-- KPIs -->
<div class="grid g4 mb">
  <div class="card kpi green"><div class="label">Active Workers</div><div class="val" id="k_workers">–</div><div class="sub" id="k_workers_s"></div></div>
  <div class="card kpi blue"><div class="label">KG Plucked</div><div class="val" id="k_kg">–</div><div class="sub">Leaf plucking total</div></div>
  <div class="card kpi amber"><div class="label">Payroll</div><div class="val" id="k_pay">–</div><div class="sub" id="k_pay_s"></div></div>
  <div class="card kpi red"><div class="label">Expenses</div><div class="val" id="k_exp">–</div><div class="sub" id="k_exp_s"></div></div>
</div>

<div class="grid g3 mb">
  <div class="card kpi"><div class="label">Cost / KG (Labour)</div><div class="val" id="k_cpk" style="font-size:22px">–</div><div class="sub">Total labour ÷ total KG</div></div>
  <div class="card kpi"><div class="label">Avg KG / Worker</div><div class="val" id="k_avg" style="font-size:22px">–</div><div class="sub">Selected period</div></div>
  <div class="card kpi"><div class="label">Tea Acres</div><div class="val" id="k_acres" style="font-size:22px">–</div><div class="sub">Total planted</div></div>
</div>

<!-- Chart + Upcoming -->
<div class="grid g2 mb">
  <div class="card">
    <div class="card-h"><div><h3>Payroll vs Expenses</h3><small>Monthly comparison</small></div>
      <select id="yearSel" class="chip"></select></div>
    <div class="card-pad"><canvas id="peChart" style="height:280px;width:100%"></canvas>
      <div style="display:flex;gap:18px;margin-top:10px;font-size:12px">
        <span><span style="display:inline-block;width:12px;height:12px;background:#c98a1a;border-radius:3px"></span> Payroll</span>
        <span><span style="display:inline-block;width:12px;height:12px;background:#c0392b;border-radius:3px"></span> Expenses</span>
      </div></div>
  </div>
  <div class="card">
    <div class="card-h"><div><h3>Upcoming Events</h3><small>From today · next due dates</small></div>
      <a href="reminders.php" class="btn ghost sm">View All</a></div>
    <div class="card-pad" id="events"></div>
  </div>
</div>

<div class="grid g2 mb">
  <div class="card">
    <div class="card-h"><div><h3>Harvest by Section</h3><small>Leaf plucking KG</small></div>
      <a href="assignments.php" class="btn ghost sm">View Assignments</a></div>
    <div class="card-pad"><canvas id="secChart" style="height:260px;width:100%"></canvas></div>
  </div>
  <div class="card">
    <div class="card-h"><div><h3>Expense Breakdown</h3><small>By category</small></div>
      <a href="expenses.php" class="btn ghost sm">View All</a></div>
    <div class="card-pad" id="expBreak"></div>
  </div>
</div>

<div class="card mb">
  <div class="card-h"><h3>Top Workers by KG</h3></div>
  <div class="card-pad" id="topWorkers"></div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
