<?php
$title='Coupons'; $page='coupons.php'; $pageScript='assets/js/coupons.js';
require_once __DIR__.'/includes/auth.php';
require_login();
if (!platform_admin()) { echo '<link rel="stylesheet" href="'.av('assets/css/app.css').'"><div style="padding:40px;text-align:center">Access denied. Platform admins only.</div>'; exit; }
require __DIR__.'/includes/header.php';
?>
<div class="page-head"><h2>Coupons</h2></div>

<div class="card mb"><div class="card-pad">
  <h3 style="margin-top:0">Generate Coupons</h3>
  <div class="form-row3">
    <div class="field"><label>How many?</label><input id="genCount" type="number" min="1" max="100" value="10"></div>
    <div class="field" style="display:flex;align-items:flex-end"><button class="btn" onclick="generateCoupons()">Generate</button></div>
  </div>
  <div id="genResult" style="display:none;margin-top:14px">
    <label style="font-size:12px;color:var(--muted)">Newly generated codes (copy these out to customers):</label>
    <textarea id="genCodes" rows="6" readonly style="width:100%;margin-top:6px;font-family:monospace"></textarea>
  </div>
</div></div>

<div class="card" style="margin-top:16px"><div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Code</th><th>Status</th><th>Used By</th><th>Used For Estate</th><th>Used At</th><th class="right">Actions</th></tr></thead>
  <tbody id="couponBody"><tr><td colspan="6" class="empty">Loading…</td></tr></tbody>
</table></div></div>

<?php require __DIR__.'/includes/footer.php'; ?>
