<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
if (!platform_admin()) fail('Permission denied');
$db = db();
$action = $_GET['action'] ?? 'list';

try {
  if ($action === 'list') {
    $status = $_GET['status'] ?? '';
    $w = $status !== '' ? ' WHERE t.status=?' : '';
    $st = $db->prepare("SELECT t.*, u.name customer_name, u.email customer_email
      FROM support_tickets t JOIN users u ON u.id=t.owner_user_id
      $w ORDER BY t.updated_at DESC LIMIT 1000");
    $st->execute($status !== '' ? [$status] : []);
    ok(['rows'=>$st->fetchAll()]);
  }

  if ($action === 'thread') {
    $tid = (int)($_GET['id'] ?? 0);
    $st = $db->prepare("SELECT t.*, u.name customer_name, u.email customer_email
      FROM support_tickets t JOIN users u ON u.id=t.owner_user_id WHERE t.id=?");
    $st->execute([$tid]);
    $ticket = $st->fetch();
    if (!$ticket) fail('Ticket not found');
    $st = $db->prepare("SELECT r.*, u.name user_name FROM support_ticket_replies r JOIN users u ON u.id=r.user_id WHERE ticket_id=? ORDER BY r.id ASC");
    $st->execute([$tid]);
    ok(['ticket'=>$ticket, 'replies'=>$st->fetchAll()]);
  }

  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');
  $me = current_user();

  if ($action === 'reply') {
    $tid = (int)($b['ticket_id'] ?? 0);
    $message = trim($b['message'] ?? '');
    if ($message === '') fail('Message is required');
    $st = $db->prepare('SELECT id FROM support_tickets WHERE id=?'); $st->execute([$tid]);
    if (!$st->fetchColumn()) fail('Ticket not found');
    $db->prepare("INSERT INTO support_ticket_replies (ticket_id,user_id,is_admin_reply,message) VALUES (?,?,1,?)")->execute([$tid,$me['id'],$message]);
    $db->prepare("UPDATE support_tickets SET status='Answered' WHERE id=?")->execute([$tid]);
    ok();
  }

  if ($action === 'set_status') {
    $tid = (int)($b['ticket_id'] ?? 0);
    $status = $b['status'] ?? '';
    if (!in_array($status, ['Open','Answered','Closed'], true)) fail('Invalid status');
    $db->prepare("UPDATE support_tickets SET status=? WHERE id=?")->execute([$status,$tid]);
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
