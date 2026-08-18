<?php
$title='Admin Dashboard'; $page='admin_dashboard.php'; $pageScript='assets/js/admin_dashboard.js';
require __DIR__.'/includes/admin_header.php';
?>
<div class="page-head"><h2>Admin Dashboard</h2></div>

<div class="grid g4 mb">
  <div class="card kpi green"><div class="label">Active Users</div><div class="val" id="k_active_users">–</div><div class="sub">Across every customer account</div></div>
</div>

<div class="grid g4 mb">
  <a class="card qa" href="admin_users.php"><div class="qa-ic"><?=icon('shield')?></div><div><strong>User Management</strong><small>All customers &amp; sub-users</small></div></a>
  <a class="card qa" href="admin_tickets.php"><div class="qa-ic"><?=icon('support')?></div><div><strong>Support Tickets</strong><small id="k_open_tickets">–</small></div></a>
  <a class="card qa" href="coupons.php"><div class="qa-ic"><?=icon('tag')?></div><div><strong>Coupons</strong><small>Generate &amp; manage codes</small></div></a>
  <a class="card qa" href="admin_settings.php"><div class="qa-ic"><?=icon('settings')?></div><div><strong>Settings</strong><small>Site, support &amp; email config</small></div></a>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
