<?php
/**
 * One-time installer. Visit /install.php in your browser after uploading.
 * Creates all tables and the default admin (admin@estate.local / admin123).
 * DELETE this file after successful install.
 */
require_once __DIR__ . '/config/config.php';

$done = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = file_get_contents(__DIR__ . '/database.sql');
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = str_replace('__HASH__', $hash, $sql);
        // split on ; at line ends, run statements
        $pdo = db();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        foreach (array_map('trim', preg_split('/;\s*[\r\n]/', $sql)) as $stmt) {
            // Strip full-line SQL comments so a leading "-- note" doesn't hide the statement
            $lines = preg_split('/\r?\n/', $stmt);
            $lines = array_filter($lines, fn($l) => !str_starts_with(trim($l), '--'));
            $stmt = trim(implode("\n", $lines));
            if ($stmt === '') continue;
            $pdo->exec($stmt);
        }
        $done = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install</title><link rel="stylesheet" href="assets/css/app.css"></head>
<body><div class="login-wrap"><div class="login-card">
<div class="login-logo">🍃</div><h1>Installer</h1><p>Tea Estate Management System</p>
<?php if($done): ?>
  <div class="badge b-green" style="display:block;padding:12px;text-align:center;margin-bottom:14px">✓ Installed successfully!</div>
  <p style="font-size:13px">Login: <b>admin@estate.local</b> / <b>admin123</b></p>
  <p style="font-size:12px;color:#c0392b">Now DELETE install.php from your server.</p>
  <a class="btn" style="width:100%;justify-content:center" href="login.php">Go to Login</a>
<?php elseif($error): ?>
  <div class="badge b-red" style="display:block;padding:12px;margin-bottom:14px"><?=e($error)?></div>
  <p style="font-size:12px">Check config/config.php database settings, then retry.</p>
  <form method="post"><button class="btn" style="width:100%;justify-content:center">Retry Install</button></form>
<?php else: ?>
  <p style="font-size:13px">This creates all database tables and the default admin account. Make sure you edited <b>config/config.php</b> first.</p>
  <form method="post"><button class="btn" style="width:100%;justify-content:center;padding:12px">Run Installer</button></form>
<?php endif; ?>
</div></div></body></html>
