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

/** Builds the session user array from a `users` row and logs them in */
function start_user_session($row) {
    $_SESSION['user'] = [
        'id'=>$row['id'],'name'=>$row['name'],'email'=>$row['email'],
        'role'=>$row['role'],'estates'=>array_filter(explode(',', $row['assigned_estate_ids'] ?? '')),
        'owner_user_id'=>$row['owner_user_id'] ?? null,
        'is_platform_admin'=>(bool)($row['is_platform_admin'] ?? false),
    ];
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

/** The id that owns/scopes all of this user's data (self for tenant roots, else their Owner's id) */
function tenant_id() {
    $u = current_user();
    return $u ? (int)($u['owner_user_id'] ?? $u['id']) : 0;
}
/** Platform operator (manages coupons) - distinct from per-tenant Owner/Administrator roles */
function platform_admin() {
    $u = current_user();
    return (bool)($u['is_platform_admin'] ?? false);
}

/** Where to send someone right after login/registration */
function post_login_redirect() {
    return platform_admin() ? 'admin_dashboard.php' : 'dashboard.php';
}

/** Site name shown on the landing page and sidebar - DB override if set, else the APP_NAME constant */
function site_name() {
    static $name = null;
    if ($name === null) {
        try {
            $v = db()->query("SELECT svalue FROM platform_settings WHERE skey='site_name'")->fetchColumn();
        } catch (Throwable $e) { $v = false; }
        $name = ($v !== false && $v !== '') ? $v : APP_NAME;
    }
    return $name;
}

/** Cache-busting asset URL: appends the file's last-modified time as ?v= */
function av($relPath) {
    $full = __DIR__ . '/../' . $relPath;
    $v = @filemtime($full);
    return $relPath . '?v=' . ($v ?: '1');
}

/** Shared sidebar icon set (customer + admin shells) */
function icon($n) {
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
     'tag'=>'M20.5 12.8 12.7 20.6a2 2 0 0 1-2.83 0l-6.5-6.5a2 2 0 0 1 0-2.83L11.2 3.5H19a1.5 1.5 0 0 1 1.5 1.5v7.8zM8 8h.01',
     'support'=>'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM9.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2 1.8-2 3.5M12 17h.01',
     'settings'=>'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19 12a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7 7 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2L10 21h4l.5-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.07-.4.1-.8.1-1.2z',
     'mail'=>'M3 5h18v14H3zM3 5l9 8 9-8',
    ];
    return '<svg viewBox="0 0 24 24" class="ic"><path d="'.$p[$n].'"/></svg>';
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n) { return CURRENCY.' '.number_format((float)$n, 2); }
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function check_csrf($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t); }
