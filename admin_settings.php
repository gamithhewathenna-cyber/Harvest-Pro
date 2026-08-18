<?php
$title='Settings'; $page='admin_settings.php'; $pageScript='assets/js/admin_settings.js';
require __DIR__.'/includes/admin_header.php';
$curEmail = db()->prepare('SELECT email FROM users WHERE id=?');
$curEmail->execute([current_user()['id']]);
$curEmail = $curEmail->fetchColumn();
?>
<div class="page-head"><h2>Settings</h2></div>

<div class="card mb"><div class="card-pad">
  <h3 style="margin-top:0">My Admin Account</h3>
  <p style="font-size:12px;color:var(--muted);margin-top:-6px">The login used for this platform admin account (<b id="curEmail"><?=e($curEmail)?></b>).</p>
  <div class="form-row3">
    <div class="field"><label>New Email</label><input id="newEmail" type="email"></div>
    <div class="field"><label>Current Password</label><input id="emailCurPw" type="password"></div>
    <div class="field" style="display:flex;align-items:flex-end"><button class="btn" onclick="changeEmail()">Update Email</button></div>
  </div>
  <div class="form-row3" style="margin-top:16px">
    <div class="field"><label>Current Password</label><input id="pwCurPw" type="password"></div>
    <div class="field"><label>New Password</label><input id="pwNewPw" type="password" placeholder="min 6 chars"></div>
    <div class="field"><label>Confirm New Password</label><input id="pwConfPw" type="password"></div>
  </div>
  <button class="btn" style="margin-top:12px" onclick="changeAdminPw()">Update Password</button>
</div></div>

<div class="card mb"><div class="card-pad">
  <h3 style="margin-top:0">Site</h3>
  <div class="form-row">
    <div class="field"><label>Site Name</label><input id="siteName" type="text" placeholder="<?=e(APP_NAME)?>"></div>
    <div class="field"><label>Support Contact Email</label><input id="supportEmail" type="email"></div>
  </div>
  <button class="btn" style="margin-top:14px" onclick="saveSettings()">Save Site Settings</button>

  <h3 style="margin:24px 0 0">Logo</h3>
  <div style="display:flex;align-items:center;gap:16px;margin-top:10px">
    <div id="logoPreviewWrap" style="display:none;width:64px;height:64px;border-radius:12px;border:1px solid var(--line);align-items:center;justify-content:center;overflow:hidden">
      <img id="logoPreview" style="width:100%;height:100%;object-fit:contain">
    </div>
    <div class="field" style="flex:1;margin:0"><input id="logoFile" type="file" accept=".png,.jpg,.jpeg,.webp,.svg"></div>
    <button class="btn" onclick="uploadLogo()">Upload Logo</button>
    <button class="btn gray" id="removeLogoBtn" style="display:none" onclick="removeLogo()">Remove</button>
  </div>
  <p style="font-size:12px;color:var(--muted);margin-top:8px">PNG, JPG, WEBP or SVG, up to 2MB. Replaces the leaf icon across the app.</p>
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
