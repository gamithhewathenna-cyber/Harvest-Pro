<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) { header('Location: dashboard.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT * FROM users WHERE email=? AND status="Active" LIMIT 1');
    $st->execute([$email]);
    $row = $st->fetch();
    if ($row && password_verify($pass, $row['password_hash'])) {
        $_SESSION['user'] = [
            'id'=>$row['id'],'name'=>$row['name'],'email'=>$row['email'],
            'role'=>$row['role'],'estate'=>$row['assigned_estate_id']
        ];
        db()->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$row['id']]);
        header('Location: dashboard.php'); exit;
    }
    $err = 'Invalid email or password.';
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login · <?=APP_NAME?></title><link rel="stylesheet" href="assets/css/app.css"></head>
<body><div class="login-wrap"><div class="login-card">
  <div class="login-logo">🍃</div>
  <h1>Tea Estate</h1><p>Management System</p>
  <?php if($err): ?><div class="badge b-red" style="display:block;padding:10px;margin-bottom:14px;text-align:center"><?=e($err)?></div><?php endif; ?>
  <form method="post">
    <div class="field"><label>Email</label><input type="email" name="email" required autofocus value="admin@estate.local"></div>
    <div class="field"><label>Password</label><input type="password" name="password" required value=""></div>
    <button class="btn" style="width:100%;justify-content:center;padding:12px">Sign In</button>
  </form>
  <p style="margin-top:18px;font-size:12px">Default: admin@estate.local / admin123 (change after login)</p>
</div></div></body></html>
