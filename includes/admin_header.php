<?php
require_once __DIR__ . '/auth.php';
require_login();
if (!platform_admin()) { echo '<link rel="stylesheet" href="'.av('assets/css/app.css').'"><div style="padding:40px;text-align:center">Access denied. Platform admins only.</div>'; exit; }
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
$u = current_user();
$page = $page ?? '';
$nav = [
  ['admin_dashboard.php','Admin Dashboard','grid'],
  ['admin_users.php','User Management','shield'],
  ['admin_tickets.php','Support Tickets','support'],
  ['coupons.php','Coupons','tag'],
  ['admin_settings.php','Settings','settings'],
  ['admin_email.php','Email Config','mail'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e($title ?? site_name())?> · Admin</title>
<link rel="stylesheet" href="<?=av('assets/css/app.css')?>">
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-logo">🛠️</div>
      <div class="brand-txt"><span><?=e(site_name())?></span><small>Platform Admin</small></div>
    </div>
    <nav class="nav">
      <?php foreach($nav as $n): ?>
        <a href="<?=$n[0]?>" class="nav-item <?=$page===$n[0]?'active':''?>">
          <?=icon($n[2])?><span><?=e($n[1])?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-user" style="border-top:1px solid rgba(255,255,255,.08)">
      <a href="dashboard.php" class="btn" style="width:100%;justify-content:center">Customer View →</a>
    </div>
    <div class="side-user">
      <a href="profile.php" style="display:contents;text-decoration:none;color:inherit" title="My Profile">
        <div class="avatar"><?=e(strtoupper(substr($u['name'],0,1)))?></div>
        <div class="side-user-info">
          <strong><?=e($u['name'])?></strong>
          <small>Platform Admin</small>
        </div>
      </a>
      <a href="logout.php" class="logout" title="Logout">⏻</a>
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <h1><?=e($title ?? '')?></h1>
      <a class="top-right" href="profile.php" style="text-decoration:none;color:inherit"><?=e($u['name'])?> · <span class="role-tag">Platform Admin</span></a>
    </header>
    <div class="content">
