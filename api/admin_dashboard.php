<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
if (!platform_admin()) fail('Permission denied');
$db = db();

try {
  $activeUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE status='Active'")->fetchColumn();
  $openTickets = (int)$db->query("SELECT COUNT(*) FROM support_tickets WHERE status<>'Closed'")->fetchColumn();
  ok(['active_users'=>$activeUsers, 'open_tickets'=>$openTickets]);
} catch (Throwable $e) { fail($e->getMessage()); }
