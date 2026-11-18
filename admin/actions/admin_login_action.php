<?php
// admin/actions/admin_login_action.php
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

    $stmt = $conn->prepare("SELECT * FROM admins WHERE admin_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['admin_password'])) {
            // Successful login
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            $_SESSION['admin_email'] = $admin['admin_email'];
            $_SESSION['admin_role'] = 'super';
            
            $_SESSION['success_message'] = "Welcome back, " . $admin['admin_name'] . "!";
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid password. Access denied.";
        }
    } else {
        $_SESSION['error_message'] = "Admin account not found.";
    }

    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>
