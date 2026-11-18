<?php
// includes/customer_check.php
// Ensure user is logged in. Redirect to login if not.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to access this page.";
    
    // Detect how to get back to login.php
    $pages_path = (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false) ? '../pages/login.php' : 'login.php';
    header("Location: " . $pages_path);
    exit();
}

