<?php
// staff/includes/staff_auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Must be logged in as staff
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'staff') {
    $current_file = $_SERVER['SCRIPT_NAME'];
    if (strpos($current_file, '/pages/') !== false || strpos($current_file, '/actions/') !== false) {
        header("Location: ../login.php");
    } else {
        header("Location: login.php");
    }
    exit();
}
?>
