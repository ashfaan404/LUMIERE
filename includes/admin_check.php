<?php
// includes/admin_check.php
// Middleware to ensure only administrators can access admin pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to access the admin area.";
    header("Location: ../pages/login.php");
    exit();
}

// Check if user has admin privileges
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_check = $stmt->get_result()->fetch_assoc();

if (!$user_check || $user_check['is_admin'] != 1) {
    $_SESSION['error_message'] = "Access denied. Administrator privileges required.";
    header("Location: ../pages/home.php");
    exit();
}
?>
