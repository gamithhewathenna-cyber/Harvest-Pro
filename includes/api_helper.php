<?php
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function api_require_login() {
    if (!current_user()) {
        echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
        exit;
    }
}
function api_require_edit() {
    api_require_login();
    if (!can_edit()) {
        echo json_encode(['ok'=>false,'error'=>'Permission denied']);
        exit;
    }
}
function body() {
    $raw = file_get_contents('php://input');
    $j = json_decode($raw, true);
    return is_array($j) ? $j : $_POST;
}
function ok($data = []) { echo json_encode(['ok'=>true] + $data); exit; }
function fail($msg) { echo json_encode(['ok'=>false,'error'=>$msg]); exit; }
function num($v){ return $v === '' || $v === null ? 0 : (float)$v; }
function s($v){ return $v === null ? null : trim((string)$v); }
