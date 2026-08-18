<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
if (!platform_admin()) fail('Permission denied');
$db = db();
$action = $_GET['action'] ?? 'get';
$KEYS = ['site_name','support_email'];

try {
  if ($action === 'get') {
    $st = $db->query('SELECT skey,svalue FROM platform_settings');
    $vals = [];
    foreach ($st as $r) $vals[$r['skey']] = $r['svalue'];
    $out = [];
    foreach ($KEYS as $k) $out[$k] = $vals[$k] ?? '';
    ok(['settings'=>$out]);
  }

  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'save') {
    $stmt = $db->prepare('INSERT INTO platform_settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)');
    foreach ($KEYS as $k) {
      if (array_key_exists($k, $b)) $stmt->execute([$k, trim((string)$b[$k])]);
    }
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
