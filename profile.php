<?php
$title='My Profile'; $page='profile.php'; $pageScript='assets/js/profile.js';
require_once __DIR__.'/includes/auth.php';
require_login();
$me = current_user();
$st = db()->prepare('SELECT name,email,phone,address,role FROM users WHERE id=?');
$st->execute([$me['id']]);
$row = $st->fetch();
require __DIR__.'/includes/header.php';
?>
<div class="page-head"><h2>My Profile</h2></div>

<div class="card"><div class="card-pad">
  <div class="form-row">
    <div class="field"><label>Full Name</label><input value="<?=e($row['name'])?>" disabled></div>
    <div class="field"><label>Email</label><input value="<?=e($row['email'])?>" disabled></div>
    <div class="field"><label>Phone Number</label><input value="<?=e($row['phone'])?>" disabled></div>
    <div class="field"><label>Role</label><input value="<?=e($row['role'])?>" disabled></div>
    <div class="field" style="grid-column:1 / -1"><label>Address</label><input value="<?=e($row['address'])?>" disabled></div>
  </div>
</div></div>

<div class="card mb" style="margin-top:16px"><div class="card-pad">
  <h3 style="margin-top:0">Change Password</h3>
  <div class="form-row3">
    <div class="field"><label>Current Password</label><input id="curPw" type="password"></div>
    <div class="field"><label>New Password</label><input id="newPw" type="password" placeholder="min 6 chars"></div>
    <div class="field"><label>Confirm New Password</label><input id="confPw" type="password"></div>
  </div>
  <button class="btn" style="margin-top:12px" onclick="changePw()">Update Password</button>
</div></div>

<div class="card mb" style="margin-top:16px"><div class="card-pad" style="text-align:center">
  <a href="logout.php" class="btn red">Sign Out</a>
</div></div>

<?php require __DIR__.'/includes/footer.php'; ?>
