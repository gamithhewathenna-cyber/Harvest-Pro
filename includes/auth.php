<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => APP_DOMAIN,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function user_role() {
    $u = current_user();
    return $u['role'] ?? 'Viewer';
}

/** Roles allowed to write/approve */
function can_edit() {
    return in_array(user_role(), ['Owner','Administrator','Estate Manager','Supervisor','Accountant'], true);
}
function can_approve() {
    return in_array(user_role(), ['Owner','Administrator','Estate Manager','Accountant'], true);
}
function can_admin() {
    return in_array(user_role(), ['Owner','Administrator'], true);
}

/** Cache-busting asset URL: appends the file's last-modified time as ?v= */
function av($relPath) {
    $full = __DIR__ . '/../' . $relPath;
    $v = @filemtime($full);
    return $relPath . '?v=' . ($v ?: '1');
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n) { return CURRENCY.' '.number_format((float)$n, 2); }
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function check_csrf($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t); }
