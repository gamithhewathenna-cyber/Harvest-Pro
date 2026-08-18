<?php
/**
 * One-time admin password reset. Visit /reset_admin_password.php in your browser.
 * Resets admin@estate.local's password to admin123.
 * DELETE this file immediately after use.
 */
require_once __DIR__ . '/config/config.php';

$done = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $st = db()->prepare('UPDATE users SET password_hash=? WHERE email=?');
        $st->execute([$hash, 'admin@estate.local']);
        $done = $st->rowCount() > 0;
        if (!$done) $error = 'No user found with email admin@estate.local.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Admin Password</title><link rel="stylesheet" href="assets/css/app.css"></head>
<body><div class="login-wrap"><div class="login-card">
<div class="login-logo">🍃</div><h1>Reset Admin Password</h1>
<?php if($done): ?>
  <div class="badge b-green" style="display:block;padding:12px;text-align:center;margin-bottom:14px">✓ Password reset!</div>
  <p style="font-size:13px">Login: <b>admin@estate.local</b> / <b>admin123</b></p>
  <p style="font-size:12px;color:#c0392b">Now DELETE reset_admin_password.php from your server.</p>
  <a class="btn" style="width:100%;justify-content:center" href="login.php">Go to Login</a>
<?php elseif($error): ?>
  <div class="badge b-red" style="display:block;padding:12px;margin-bottom:14px"><?=e($error)?></div>
  <form method="post"><button class="btn" style="width:100%;justify-content:center">Retry</button></form>
<?php else: ?>
  <p style="font-size:13px">This resets <b>admin@estate.local</b>'s password back to <b>admin123</b>.</p>
  <form method="post"><button class="btn" style="width:100%;justify-content:center;padding:12px">Reset Password</button></form>
<?php endif; ?>
</div></div></body></html>
