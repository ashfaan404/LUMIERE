<?php
// admin/includes/admin_auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Must be logged in as super admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
    $current_file = $_SERVER['SCRIPT_NAME'];
    if (strpos($current_file, '/pages/') !== false || strpos($current_file, '/actions/') !== false) {
        header("Location: ../login.php");
    } else {
        header("Location: login.php");
    }
    exit();
}
?>
