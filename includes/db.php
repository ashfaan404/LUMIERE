<?php
require_once __DIR__ . '/app_config.php';

$host   = appConfig('DB_HOST', 'localhost');
$user   = appConfig('DB_USER', 'root');
$pass   = appConfig('DB_PASS', '');
$dbname = appConfig('DB_NAME', 'perfume_shop');

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database connection unavailable. Check local configuration.');
}

$conn->set_charset('utf8mb4');
date_default_timezone_set('Asia/Colombo');
$conn->query("SET time_zone = '+05:30'");

define('CURRENCY', 'LKR');
define('STRIPE_SECRET_KEY', appConfig('STRIPE_SECRET_KEY', ''));
define('STRIPE_PUBLISHABLE_KEY', appConfig('STRIPE_PUBLISHABLE_KEY', ''));
