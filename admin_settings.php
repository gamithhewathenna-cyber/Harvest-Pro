<?php
$title='Settings'; $page='admin_settings.php'; $pageScript='assets/js/admin_settings.js';
require __DIR__.'/includes/admin_header.php';
?>
<div class="page-head"><h2>Settings</h2></div>

<div class="card mb"><div class="card-pad">
  <h3 style="margin-top:0">Site</h3>
  <div class="form-row">
    <div class="field"><label>Site Name</label><input id="siteName" type="text" placeholder="<?=e(APP_NAME)?>"></div>
    <div class="field"><label>Support Contact Email</label><input id="supportEmail" type="email"></div>
  </div>
  <button class="btn" style="margin-top:14px" onclick="saveSettings()">Save Site Settings</button>
</div></div>

<div class="card"><div class="card-pad">
  <h3 style="margin-top:0">Email Configuration</h3>
  <div class="form-row">
    <div class="field"><label>SMTP Host</label><input id="smtpHost" type="text" placeholder="smtp.example.com"></div>
    <div class="field"><label>SMTP Port</label><input id="smtpPort" type="number" value="587"></div>
    <div class="field"><label>SMTP Username</label><input id="smtpUser" type="text"></div>
    <div class="field"><label>SMTP Password</label><input id="smtpPass" type="password" placeholder="Leave blank to keep current password"></div>
    <div class="field"><label>From Email</label><input id="fromEmail" type="email"></div>
    <div class="field"><label>From Name</label><input id="fromName" type="text"></div>
    <div class="field"><label>Encryption</label>
      <select id="encryption"><option value="tls">STARTTLS (587)</option><option value="ssl">SSL (465)</option><option value="none">None</option></select>
    </div>
  </div>
  <div style="display:flex;gap:10px;margin-top:14px">
    <button class="btn" onclick="saveEmail()">Save Email Settings</button>
    <button class="btn gray" onclick="testEmail()">Send Test Email</button>
  </div>
  <p id="pwNote" style="font-size:12px;color:var(--muted);margin-top:10px;display:none">A password is already saved. Leave the password field blank to keep it.</p>
</div></div>

<?php require __DIR__.'/includes/footer.php'; ?>
