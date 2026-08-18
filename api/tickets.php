<?php
require_once __DIR__ . '/../includes/api_helper.php';
api_require_login();
$db = db();
$tenant = tenant_id();
$action = $_GET['action'] ?? 'list';

try {
  if ($action === 'list') {
    $st = $db->prepare("SELECT * FROM support_tickets WHERE owner_user_id=? ORDER BY updated_at DESC");
    $st->execute([$tenant]);
    ok(['rows'=>$st->fetchAll()]);
  }

  if ($action === 'thread') {
    $tid = (int)($_GET['id'] ?? 0);
    $st = $db->prepare('SELECT * FROM support_tickets WHERE id=? AND owner_user_id=?'); $st->execute([$tid,$tenant]);
    $ticket = $st->fetch();
    if (!$ticket) fail('Ticket not found');
    $st = $db->prepare("SELECT r.*, u.name user_name FROM support_ticket_replies r JOIN users u ON u.id=r.user_id WHERE ticket_id=? ORDER BY r.id ASC");
    $st->execute([$tid]);
    ok(['ticket'=>$ticket, 'replies'=>$st->fetchAll()]);
  }

  $b = body();
  if (!check_csrf($b['csrf'] ?? '')) fail('Invalid session token');
  $me = current_user();

  if ($action === 'create') {
    $subject = trim($b['subject'] ?? ''); $message = trim($b['message'] ?? '');
    if ($subject === '' || $message === '') fail('Subject and message are required');
    $db->beginTransaction();
    try {
      $db->prepare("INSERT INTO support_tickets (owner_user_id,subject,status) VALUES (?,?, 'Open')")->execute([$tenant,$subject]);
      $tid = (int)$db->lastInsertId();
      $db->prepare("INSERT INTO support_ticket_replies (ticket_id,user_id,is_admin_reply,message) VALUES (?,?,0,?)")->execute([$tid,$me['id'],$message]);
      $db->commit();
      ok(['id'=>$tid]);
    } catch (Throwable $e) { $db->rollBack(); fail($e->getMessage()); }
  }

  if ($action === 'reply') {
    $tid = (int)($b['ticket_id'] ?? 0);
    $message = trim($b['message'] ?? '');
    if ($message === '') fail('Message is required');
    $st = $db->prepare('SELECT id FROM support_tickets WHERE id=? AND owner_user_id=?'); $st->execute([$tid,$tenant]);
    if (!$st->fetchColumn()) fail('Ticket not found');
    $db->prepare("INSERT INTO support_ticket_replies (ticket_id,user_id,is_admin_reply,message) VALUES (?,?,0,?)")->execute([$tid,$me['id'],$message]);
    $db->prepare("UPDATE support_tickets SET status='Open' WHERE id=?")->execute([$tid]);
    ok();
  }

  fail('Unknown action');
} catch (Throwable $e) { fail($e->getMessage()); }
