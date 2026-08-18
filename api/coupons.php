<?php
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/coupons.php';
api_require_login();
if (!platform_admin()) fail('Permission denied');
$db = db();
$action = $_GET['action'] ?? 'list';

try {
  if ($action === 'list') {
    $st = $db->query("SELECT c.*, u.name used_by_name, u.email used_by_email, e.name used_for_estate_name
      FROM coupons c
      LEFT JOIN users u ON u.id=c.used_by_user_id
      LEFT JOIN estates e ON e.id=c.used_for_estate_id
      ORDER BY c.id DESC LIMIT 1000");
    ok(['rows'=>$st->fetchAll()]);
  }

  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'generate') {
    $count = (int)($b['count'] ?? 0);
    if ($count < 1 || $count > 100) fail('Enter a number between 1 and 100');
    $stmt = $db->prepare('INSERT INTO coupons (code) VALUES (?)');
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
      for ($attempt = 0; $attempt < 5; $attempt++) {
        $code = generate_coupon_code();
        try { $stmt->execute([$code]); $codes[] = $code; break; }
        catch (Throwable $e) { if ($attempt === 4) throw $e; } // retry on rare code collision
      }
    }
    ok(['codes'=>$codes]);
  }

  if ($action === 'delete') {
    $st = $db->prepare("DELETE FROM coupons WHERE id=? AND status='Unused'");
    $st->execute([(int)($b['id'] ?? 0)]);
    if ($st->rowCount() === 0) fail('Only unused coupons can be revoked');
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
