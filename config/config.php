<?php
/**
 * Tea Estate Management System - Configuration
 * Edit the DB_* values below with your cPanel MySQL details.
 */

// ---- EDIT THESE FOR YOUR cPANEL HOSTING ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'dispdrvg_harvestapp');
define('DB_USER', 'dispdrvg_harvestappadmin');
define('DB_PASS', 'CzH-!x!7*qrS)9YM');
// --------------------------------------------

define('APP_NAME', 'Tea Estate Management');
define('APP_DOMAIN', 'app.harvestpro.lk');
define('CURRENCY', 'LKR');

date_default_timezone_set('Asia/Colombo');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Check config/config.php. ('.$e->getMessage().')');
        }
    }
    return $pdo;
}
