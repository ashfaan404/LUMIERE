<?php
// admin/actions/add_staff_action.php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only super admins
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
        $_SESSION['error_message'] = "Access denied.";
        header("Location: ../pages/dashboard.php");
        exit();
    }

    $name = sanitize($conn, $_POST['admin_name']);
    $email = sanitize($conn, $_POST['admin_email']);
    $password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
    $role = sanitize($conn, $_POST['role']);

    if ($role !== 'super' && $role !== 'staff') {
        $role = 'staff';
    }

    // Check if email exists in either table
    $check_admin = $conn->prepare("SELECT admin_id FROM admins WHERE admin_email = ?");
    $check_admin->bind_param("s", $email);
    $check_admin->execute();
    
    $check_staff = $conn->prepare("SELECT staff_id FROM staffs WHERE staff_email = ?");
    $check_staff->bind_param("s", $email);
    $check_staff->execute();
    
    if ($check_admin->get_result()->num_rows > 0 || $check_staff->get_result()->num_rows > 0) {
        $_SESSION['error_message'] = "An account with this email already exists.";
        header("Location: ../pages/add_staff.php");
        exit();
    }

    if ($role === 'staff') {
        $stmt = $conn->prepare("INSERT INTO staffs (staff_name, staff_email, staff_password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (admin_name, admin_email, admin_password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Staff account created successfully.";
        header("Location: ../pages/staff.php");
    } else {
        $_SESSION['error_message'] = "Error creating staff account.";
        header("Location: ../pages/add_staff.php");
    }
    exit();
}
?>
