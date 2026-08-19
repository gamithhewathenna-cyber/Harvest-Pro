<?php
/**
 * Tea Estate Management System - Configuration
 * Copy this file to config.php and edit the DB_* values below with your cPanel MySQL details.
 */

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    http_response_code(500);
    die('This application requires PHP 8.0 or newer (this server has PHP '.PHP_VERSION.'). '
        .'Switch the PHP version for this domain in cPanel &gt; MultiPHP Manager (PHP 8.2 recommended).');
}

// ---- EDIT THESE FOR YOUR cPANEL HOSTING ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'tea_estate');      // your cPanel database name (often prefixed e.g. cpuser_tea_estate)
define('DB_USER', 'root');            // your cPanel database user
define('DB_PASS', '');                // your cPanel database password
// --------------------------------------------

define('APP_NAME', 'Tea Estate Management');
define('APP_DOMAIN', 'localhost');    // your production domain, e.g. app.example.com (used for the session cookie)
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
