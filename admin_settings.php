<?php
$title='Settings'; $page='admin_settings.php'; $pageScript='assets/js/admin_settings.js';
require __DIR__.'/includes/admin_header.php';
?>
<div class="page-head"><h2>Settings</h2></div>

<div class="card"><div class="card-pad">
  <div class="form-row">
    <div class="field"><label>Site Name</label><input id="siteName" type="text" placeholder="<?=e(APP_NAME)?>"></div>
    <div class="field"><label>Support Contact Email</label><input id="supportEmail" type="email"></div>
  </div>
  <button class="btn" style="margin-top:14px" onclick="saveSettings()">Save Settings</button>
</div></div>

<?php require __DIR__.'/includes/footer.php'; ?>
