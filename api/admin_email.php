<?php
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/mailer.php';
api_require_login();
if (!platform_admin()) fail('Permission denied');
$db = db();
$action = $_GET['action'] ?? 'get';

try {
  if ($action === 'get') {
    $cfg = get_email_settings();
    ok(['settings'=>[
      'smtp_host'=>$cfg['smtp_host'] ?? '',
      'smtp_port'=>$cfg['smtp_port'] ?? 587,
      'smtp_user'=>$cfg['smtp_user'] ?? '',
      'has_password'=>!empty($cfg['smtp_pass']),
      'from_email'=>$cfg['from_email'] ?? '',
      'from_name'=>$cfg['from_name'] ?? '',
      'encryption'=>$cfg['encryption'] ?? 'tls',
    ]]);
  }

  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');

  if ($action === 'save') {
    $cfg = get_email_settings();
    $pass = trim((string)($b['smtp_pass'] ?? ''));
    if ($pass === '') $pass = $cfg['smtp_pass'] ?? ''; // blank = keep existing password
    save_email_settings([
      'smtp_host'=>trim($b['smtp_host'] ?? ''),
      'smtp_port'=>(int)($b['smtp_port'] ?? 587),
      'smtp_user'=>trim($b['smtp_user'] ?? ''),
      'smtp_pass'=>$pass,
      'from_email'=>trim($b['from_email'] ?? ''),
      'from_name'=>trim($b['from_name'] ?? ''),
      'encryption'=>in_array($b['encryption'] ?? '', ['none','tls','ssl'], true) ? $b['encryption'] : 'tls',
    ]);
    ok();
  }

  if ($action === 'test') {
    $cfg = get_email_settings();
    if (!$cfg || !$cfg['from_email']) fail('Save your SMTP settings first');
    send_email($cfg['from_email'], 'Test email from '.site_name(), "This is a test email confirming your SMTP settings work.\n\nSent ".date('Y-m-d H:i:s'));
    ok(['sent_to'=>$cfg['from_email']]);
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
