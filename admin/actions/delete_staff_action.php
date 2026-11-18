<?php
// admin/actions/delete_staff_action.php
session_start();
require_once '../../includes/db.php';

if (isset($_GET['id'])) {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
        $_SESSION['error_message'] = "Access denied.";
        header("Location: ../pages/dashboard.php");
        exit();
    }

    $id = intval($_GET['id']);
    $role = $_GET['role'] ?? 'staff';
    
    // Prevent deleting self if it's the same table
    if ($id == $_SESSION['admin_id'] && $role == 'super') {
        $_SESSION['error_message'] = "You cannot delete your own account.";
        header("Location: ../pages/staff.php");
        exit();
    }

    if ($role === 'staff') {
        $stmt = $conn->prepare("DELETE FROM staffs WHERE staff_id = ?");
    } else {
        $stmt = $conn->prepare("DELETE FROM admins WHERE admin_id = ?");
    }
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Staff account removed successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to remove staff account.";
    }
}

header("Location: ../pages/staff.php");
exit();
?>
