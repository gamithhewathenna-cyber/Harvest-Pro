<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();
$me = current_user();
$action = $_GET['action'] ?? '';

try {
  if ($action === 'change_password') {
    $b = body();
    if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');
    $current = (string)($b['current_password'] ?? '');
    $new = (string)($b['new_password'] ?? '');
    if (strlen($new) < 6) fail('New password must be at least 6 characters');

    $st = $db->prepare('SELECT password_hash FROM users WHERE id=?');
    $st->execute([$me['id']]);
    $row = $st->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) fail('Current password is incorrect');

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $db->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash, $me['id']]);
    ok();
  }

  if ($action === 'change_email') {
    $b = body();
    if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');
    $current = (string)($b['current_password'] ?? '');
    $newEmail = trim((string)($b['new_email'] ?? ''));
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) fail('Enter a valid email address');

    $st = $db->prepare('SELECT password_hash FROM users WHERE id=?');
    $st->execute([$me['id']]);
    $row = $st->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) fail('Current password is incorrect');

    $st = $db->prepare('SELECT 1 FROM users WHERE email=? AND id<>?');
    $st->execute([$newEmail, $me['id']]);
    if ($st->fetchColumn()) fail('That email is already in use by another account');

    $db->prepare('UPDATE users SET email=? WHERE id=?')->execute([$newEmail, $me['id']]);
    $_SESSION['user']['email'] = $newEmail;
    ok(['email'=>$newEmail]);
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
