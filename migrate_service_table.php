<?php
/**
 * One-time migration. Visit /migrate_service_table.php in your browser.
 * Converts service_cycles from an asset-maintenance tracker into a
 * service catalog: service_name, description, unit_type, rate_per_unit, status.
 * DROPS old columns (asset, estate_id, section_id, last_service_date,
 * next_service_date, frequency, cost, supplier, notes) and their data.
 * DELETE this file immediately after use.
 */
require_once __DIR__ . '/config/config.php';

function has_column($db,$table,$col){
    $st=$db->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    $st->execute([$table,$col]);
    return (bool)$st->fetchColumn();
}
function fk_name($db,$table,$col){
    $st=$db->prepare("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? AND REFERENCED_TABLE_NAME IS NOT NULL");
    $st->execute([$table,$col]);
    return $st->fetchColumn();
}

$done = false; $error = ''; $log = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = db();
        foreach (['estate_id','section_id'] as $col) {
            if (has_column($db,'service_cycles',$col)) {
                $fk = fk_name($db,'service_cycles',$col);
                if ($fk) { $db->exec("ALTER TABLE service_cycles DROP FOREIGN KEY `$fk`"); $log[]="Dropped FK $fk"; }
            }
        }
        foreach (['asset','estate_id','section_id','last_service_date','next_service_date','frequency','cost','supplier','notes'] as $col) {
            if (has_column($db,'service_cycles',$col)) {
                $db->exec("ALTER TABLE service_cycles DROP COLUMN `$col`");
                $log[]="Dropped column $col";
            }
        }
        if (!has_column($db,'service_cycles','description')) {
            $db->exec("ALTER TABLE service_cycles ADD COLUMN description TEXT AFTER service_name");
            $log[]="Added column description";
        }
        if (!has_column($db,'service_cycles','unit_type')) {
            $db->exec("ALTER TABLE service_cycles ADD COLUMN unit_type VARCHAR(60)");
            $log[]="Added column unit_type";
        }
        if (!has_column($db,'service_cycles','rate_per_unit')) {
            $db->exec("ALTER TABLE service_cycles ADD COLUMN rate_per_unit DECIMAL(12,2) DEFAULT 0");
            $log[]="Added column rate_per_unit";
        }
        $done = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Migrate Service Table</title><link rel="stylesheet" href="assets/css/app.css"></head>
<body><div class="login-wrap"><div class="login-card">
<div class="login-logo">🍃</div><h1>Migrate Service Table</h1>
<?php if($done): ?>
  <div class="badge b-green" style="display:block;padding:12px;text-align:center;margin-bottom:14px">✓ Migrated!</div>
  <p style="font-size:12px"><?=e(implode(', ', $log) ?: 'Already up to date.')?></p>
  <p style="font-size:12px;color:#c0392b">Now DELETE migrate_service_table.php from your server.</p>
  <a class="btn" style="width:100%;justify-content:center" href="service.php">Go to Service Management</a>
<?php elseif($error): ?>
  <div class="badge b-red" style="display:block;padding:12px;margin-bottom:14px"><?=e($error)?></div>
  <form method="post"><button class="btn" style="width:100%;justify-content:center">Retry</button></form>
<?php else: ?>
  <p style="font-size:13px">This converts <b>service_cycles</b> into a service catalog table
  (service_name, description, unit_type, rate_per_unit, status), dropping the old
  asset-maintenance columns and their data.</p>
  <form method="post"><button class="btn" style="width:100%;justify-content:center;padding:12px">Run Migration</button></form>
<?php endif; ?>
</div></div></body></html>
