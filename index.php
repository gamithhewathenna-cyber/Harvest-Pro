<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) { header('Location: '.post_login_redirect()); exit; }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(site_name())?> · Tea Estate Plan</title>
<link rel="stylesheet" href="<?=av('assets/css/app.css')?>">
<style>
  body{background:var(--bg)}
  .lp-nav{display:flex;align-items:center;justify-content:space-between;padding:18px 32px;max-width:1100px;margin:0 auto}
  .lp-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px}
  .lp-brand-logo{width:36px;height:36px;background:var(--green);border-radius:10px;display:grid;place-items:center;font-size:18px}
  .lp-nav-links{display:flex;gap:10px}
  .lp-hero{max-width:1100px;margin:0 auto;padding:64px 32px 48px;text-align:center}
  .lp-hero h1{font-size:38px;margin:0 0 14px;font-weight:800;color:var(--green-d)}
  .lp-hero p{font-size:16px;color:var(--muted);max-width:640px;margin:0 auto 28px}
  .lp-hero-actions{display:flex;gap:12px;justify-content:center}
  .lp-section{max-width:1100px;margin:0 auto;padding:20px 32px 60px}
  .lp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:28px}
  .lp-feature{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px}
  .lp-feature .ico{font-size:22px;margin-bottom:8px}
  .lp-feature h3{margin:0 0 6px;font-size:15px}
  .lp-feature p{margin:0;color:var(--muted);font-size:13px}
  .lp-plan-wrap{display:flex;justify-content:center;margin-top:40px}
  .lp-plan{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:32px;max-width:420px;width:100%;text-align:center}
  .lp-plan h2{margin:0 0 6px;font-size:20px}
  .lp-plan p{color:var(--muted);margin:0 0 18px;font-size:13px}
  .lp-plan ul{list-style:none;padding:0;margin:0 0 24px;text-align:left}
  .lp-plan li{padding:8px 0;border-bottom:1px solid var(--line);font-size:13px;display:flex;gap:8px}
  .lp-plan li:last-child{border-bottom:none}
  .lp-plan li:before{content:'✓';color:var(--green);font-weight:800}
  .lp-plan-note{margin-top:14px;font-size:12px;color:var(--muted)}
  .lp-footer{text-align:center;padding:24px;color:var(--muted);font-size:12px;border-top:1px solid var(--line)}
  @media(max-width:820px){.lp-features{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="lp-nav">
  <div class="lp-brand"><div class="lp-brand-logo"><?=brand_mark('🍃')?></div> <?=e(site_name())?></div>
  <div class="lp-nav-links">
    <a href="login.php" class="btn ghost">Login</a>
    <a href="register.php" class="btn">Register</a>
  </div>
</div>

<div class="lp-hero">
  <h1>Run your tea estate on one simple system</h1>
  <p>Daily plucking assignments, payroll, expenses, and reports for tea estates - built to replace spreadsheets and paper registers.</p>
  <div class="lp-hero-actions">
    <a href="register.php" class="btn" style="padding:12px 22px">Get Started</a>
    <a href="login.php" class="btn ghost" style="padding:12px 22px">Login</a>
  </div>
</div>

<div class="lp-section">
  <div class="lp-features">
    <div class="lp-feature"><div class="ico">📊</div><h3>Live Dashboard</h3><p>KPIs, harvest by section, payroll vs expenses, and top workers at a glance.</p></div>
    <div class="lp-feature"><div class="ico">📋</div><h3>Daily Assignments</h3><p>Mobile-friendly bulk entry that auto-calculates plucking pay per worker.</p></div>
    <div class="lp-feature"><div class="ico">🌱</div><h3>Estates &amp; Employees</h3><p>Manage estates, sections, and your workforce in one place.</p></div>
    <div class="lp-feature"><div class="ico">💰</div><h3>Payroll</h3><p>Generate payroll straight from assignments - draft to paid.</p></div>
    <div class="lp-feature"><div class="ico">🧾</div><h3>Expenses &amp; Reminders</h3><p>Track spending and stay ahead of due dates and service cycles.</p></div>
    <div class="lp-feature"><div class="ico">📈</div><h3>Reports</h3><p>Harvest, worker, section, expense, payroll and profitability reports with CSV export.</p></div>
  </div>

  <div class="lp-plan-wrap">
    <div class="lp-plan">
      <h2>Tea Estate Plan</h2>
      <p>Everything you need to run your estate</p>
      <ul>
        <li>Unlimited employees &amp; daily assignments</li>
        <li>Payroll, expenses &amp; service tracking</li>
        <li>Dashboard &amp; full reporting suite</li>
        <li>Add more estates any time</li>
      </ul>
      <a href="register.php" class="btn" style="width:100%;justify-content:center;padding:12px">Register with a Coupon Code</a>
      <p class="lp-plan-note">Registration requires a valid coupon code. Contact us if you don't have one.</p>
    </div>
  </div>
</div>

<div class="lp-footer">&copy; <?=date('Y')?> <?=e(site_name())?></div>

</body></html>
