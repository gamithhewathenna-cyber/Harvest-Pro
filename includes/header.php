<?php
require_once __DIR__ . '/auth.php';
require_login();
$u = current_user();
$page = $page ?? '';
$nav = [
  ['dashboard.php','Dashboard','grid'],
  ['assignments.php','Daily Assignment','clipboard'],
  ['estates.php','Estate Management','map'],
  ['users.php','User Management','shield'],
  ['employees.php','Employee','users'],
  ['service.php','Service Management','tool'],
  ['expenses.php','Expenses','receipt'],
  ['reminders.php','Reminders','bell'],
  ['reports.php','Reports','chart'],
  ['payroll.php','Payroll','cash'],
];
function icon($n){
  $p = [
   'grid'=>'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z',
   'clipboard'=>'M9 4h6v2H9zM7 6h10v14H7z',
   'map'=>'M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2z',
   'shield'=>'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z',
   'users'=>'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0zM3 21a7 7 0 0 1 18 0z',
   'tool'=>'M14 6a4 4 0 0 1 5 5l-9 9-4 1 1-4 9-9z',
   'receipt'=>'M6 3h12v18l-3-2-3 2-3-2-3 2z',
   'bell'=>'M12 3a5 5 0 0 0-5 5v4l-2 3h14l-2-3V8a5 5 0 0 0-5-5zM10 20a2 2 0 0 0 4 0',
   'chart'=>'M4 20V10M10 20V4M16 20v-7M22 20H2',
   'cash'=>'M2 6h20v12H2zM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6z',
  ];
  return '<svg viewBox="0 0 24 24" class="ic"><path d="'.$p[$n].'"/></svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e($title ?? APP_NAME)?></title>
<link rel="stylesheet" href="<?=av('assets/css/app.css')?>">
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-logo">🍃</div>
      <div class="brand-txt"><span>Tea Estate</span><small>Management</small></div>
    </div>
    <nav class="nav">
      <?php foreach($nav as $n): ?>
        <a href="<?=$n[0]?>" class="nav-item <?=$page===$n[0]?'active':''?>">
          <?=icon($n[2])?><span><?=e($n[1])?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-user">
      <div class="avatar"><?=e(strtoupper(substr($u['name'],0,1)))?></div>
      <div class="side-user-info">
        <strong><?=e($u['name'])?></strong>
        <small><?=e($u['role'])?></small>
      </div>
      <a href="logout.php" class="logout" title="Logout">⏻</a>
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <h1><?=e($title ?? '')?></h1>
      <div class="top-right"><?=e($u['name'])?> · <span class="role-tag"><?=e($u['role'])?></span></div>
    </header>
    <div class="content">
