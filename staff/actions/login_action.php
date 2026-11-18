<?php
// staff/actions/login_action.php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all fields.";
        header("Location: ../login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM staffs WHERE staff_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        if (password_verify($password, $staff['staff_password'])) {
            $_SESSION['admin_id'] = $staff['staff_id'];
            $_SESSION['admin_name'] = $staff['staff_name'];
            $_SESSION['admin_email'] = $staff['staff_email'];
            $_SESSION['admin_role'] = 'staff';
            
            $_SESSION['success_message'] = "Welcome to the Staff Portal, " . $staff['staff_name'] . "!";
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid password. Access denied.";
        }
    } else {
        // Also check if super admin is trying to login via staff portal
        $stmt_super = $conn->prepare("SELECT * FROM admins WHERE admin_email = ?");
        $stmt_super->bind_param("s", $email);
        $stmt_super->execute();
        if ($stmt_super->get_result()->num_rows === 1) {
            $_SESSION['error_message'] = "Super Admins must use the main Admin Panel.";
        } else {
            $_SESSION['error_message'] = "Staff account not found.";
        }
    }

    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>
