<?php
/**
 * Validates and atomically consumes a coupon code.
 * Must be called inside a transaction the caller already started
 * ($db->beginTransaction()) so the FOR UPDATE row lock actually
 * prevents two concurrent requests from spending the same code.
 * Throws RuntimeException (with a user-facing message) on failure -
 * callers should catch it, roll back, and surface the message.
 */
function consume_coupon(PDO $db, string $code, int $userId, int $estateId) {
    $code = trim($code);
    if ($code === '') throw new RuntimeException('Coupon code is required');

    $st = $db->prepare('SELECT * FROM coupons WHERE code=? FOR UPDATE');
    $st->execute([$code]);
    $row = $st->fetch();
    if (!$row) throw new RuntimeException('Invalid coupon code');
    if ($row['status'] !== 'Unused') throw new RuntimeException('This coupon code has already been used');

    $db->prepare("UPDATE coupons SET status='Used', used_by_user_id=?, used_for_estate_id=?, used_at=NOW() WHERE id=?")
       ->execute([$userId, $estateId, $row['id']]);
}

function generate_coupon_code() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I to avoid ambiguity
    $part = fn() => substr(str_shuffle(str_repeat($chars, 4)), 0, 4);
    return 'TEA-'.$part().'-'.$part();
}
