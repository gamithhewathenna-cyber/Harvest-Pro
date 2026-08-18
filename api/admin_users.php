<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
if (!platform_admin()) fail('Permission denied');
$db = db();
$action = $_GET['action'] ?? 'list';

try {
  if ($action === 'list') {
    $st = $db->query("SELECT u.id,u.name,u.email,u.role,u.status,u.owner_user_id,u.last_login,u.created_at,
        o.name owner_name, o.email owner_email
      FROM users u LEFT JOIN users o ON o.id=u.owner_user_id
      ORDER BY u.id DESC LIMIT 2000");
    ok(['rows'=>$st->fetchAll()]);
  }

  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'set_password') {
    $pw = (string)($b['password'] ?? '');
    if (strlen($pw) < 6) fail('Password must be at least 6 characters');
    $st = $db->prepare('SELECT id FROM users WHERE id=?'); $st->execute([(int)$b['id']]);
    if (!$st->fetchColumn()) fail('User not found');
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $db->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash, (int)$b['id']]);
    ok();
  }

  if ($action === 'delete') {
    $id = (int)($b['id'] ?? 0);
    $st = $db->prepare('SELECT id,email,owner_user_id FROM users WHERE id=?'); $st->execute([$id]);
    $target = $st->fetch();
    if (!$target) fail('User not found');
    if ($id === (int)current_user()['id']) fail("You can't delete your own account");

    if ($target['owner_user_id'] !== null) {
      // Sub-user: simple delete, no owned data of their own
      $db->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
      ok();
    }

    // Tenant root: cascades their entire tenant. Require the email typed back as a safety gate.
    $confirm = trim((string)($b['confirm_email'] ?? ''));
    if ($confirm === '' || strcasecmp($confirm, $target['email']) !== 0) {
      fail('Type the customer\'s email exactly to confirm this deletion');
    }

    $db->beginTransaction();
    try {
      $db->prepare('DELETE FROM users WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM payroll WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM employees WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM reminders WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM service_cycles WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM settings WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM support_tickets WHERE owner_user_id=?')->execute([$id]);
      $db->prepare('DELETE FROM estates WHERE owner_user_id=?')->execute([$id]); // cascades sections/assignments/expenses/fertilizer/clearing
      $db->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
      $db->commit();
      ok();
    } catch (Throwable $e) {
      $db->rollBack();
      fail($e->getMessage());
    }
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
