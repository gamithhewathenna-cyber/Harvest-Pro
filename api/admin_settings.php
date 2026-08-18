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
    $out['logo_url'] = !empty($vals['logo_path']) && file_exists(__DIR__.'/../'.$vals['logo_path']) ? av($vals['logo_path']) : '';
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

  if ($action === 'upload_logo') {
    if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) fail('No file uploaded');
    $f = $_FILES['logo'];
    if ($f['size'] > 2*1024*1024) fail('Logo must be under 2MB');
    $allowed = ['png','jpg','jpeg','webp','svg'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) fail('Allowed formats: PNG, JPG, WEBP, SVG');

    if ($ext === 'svg') {
      $content = file_get_contents($f['tmp_name']);
      if ($content === false || stripos($content, '<svg') === false) fail('Invalid SVG file');
    } elseif (!@getimagesize($f['tmp_name'])) {
      fail('Invalid image file');
    }

    $dir = __DIR__ . '/../assets/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) fail('Could not create upload directory');
    foreach ($allowed as $a) @unlink("$dir/logo.$a"); // clear any previous logo in another format
    $destRel = "assets/uploads/logo.$ext";
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/../' . $destRel)) fail('Failed to save the uploaded file');

    $db->prepare('INSERT INTO platform_settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)')
       ->execute(['logo_path', $destRel]);
    ok(['logo_url'=>av($destRel)]);
  }

  if ($action === 'remove_logo') {
    $st = $db->query("SELECT svalue FROM platform_settings WHERE skey='logo_path'");
    $path = $st->fetchColumn();
    if ($path) @unlink(__DIR__ . '/../' . $path);
    $db->prepare("DELETE FROM platform_settings WHERE skey='logo_path'")->execute();
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
