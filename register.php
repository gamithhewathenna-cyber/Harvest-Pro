<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/coupons.php';
if (current_user()) { header('Location: dashboard.php'); exit; }

$err = '';
$name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $coupon = trim($_POST['coupon_code'] ?? '');

    if ($name === '' || $email === '' || $coupon === '') {
        $err = 'Full name, email, and coupon code are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $err = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $err = 'Passwords do not match.';
    } else {
        $db = db();
        $exists = $db->prepare('SELECT 1 FROM users WHERE email=?');
        $exists->execute([$email]);
        if ($exists->fetchColumn()) {
            $err = 'An account with this email already exists.';
        } else {
            $db->beginTransaction();
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare("INSERT INTO users (name,email,password_hash,role,status) VALUES (?,?,?, 'Owner','Active')")
                   ->execute([$name, $email, $hash]);
                $userId = (int)$db->lastInsertId();

                $estateName = $name . "'s Estate";
                $estateCode = 'EST' . $userId;
                $db->prepare("INSERT INTO estates (owner_user_id,name,code,status) VALUES (?,?,?, 'Active')")
                   ->execute([$userId, $estateName, $estateCode]);
                $estateId = (int)$db->lastInsertId();

                consume_coupon($db, $coupon, $userId, $estateId);

                $db->prepare("INSERT INTO settings (owner_user_id,skey,svalue) VALUES (?, 'tea_price_per_kg','300')")
                   ->execute([$userId]);

                $db->commit();

                $st = $db->prepare('SELECT * FROM users WHERE id=?');
                $st->execute([$userId]);
                start_user_session($st->fetch());
                header('Location: dashboard.php'); exit;
            } catch (Throwable $e) {
                $db->rollBack();
                $err = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register · <?=e(APP_NAME)?></title><link rel="stylesheet" href="<?=av('assets/css/app.css')?>"></head>
<body><div class="login-wrap"><div class="login-card" style="max-width:440px">
  <div class="login-logo">🍃</div>
  <h1>Create your account</h1><p>Tea Estate Plan</p>
  <?php if($err): ?><div class="badge b-red" style="display:block;padding:10px;margin-bottom:14px;text-align:center"><?=e($err)?></div><?php endif; ?>
  <form method="post">
    <div class="field"><label>Full Name</label><input type="text" name="name" required autofocus value="<?=e($name)?>"></div>
    <div class="field"><label>Email Address</label><input type="email" name="email" required value="<?=e($email)?>"></div>
    <div class="field"><label>Password</label><input type="password" name="password" required placeholder="min 6 characters"></div>
    <div class="field"><label>Confirm Password</label><input type="password" name="confirm" required></div>
    <div class="field"><label>Coupon Code</label><input type="text" name="coupon_code" required placeholder="e.g. TEA-XXXX-XXXX"></div>
    <button class="btn" style="width:100%;justify-content:center;padding:12px;margin-top:6px">Register</button>
  </form>
  <p style="margin-top:18px;font-size:13px;text-align:center">Already have an account? <a href="login.php">Login</a></p>
</div></div></body></html>
