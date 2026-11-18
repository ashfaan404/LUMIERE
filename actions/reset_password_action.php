<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['reset_user_id'])) {
        redirect('../pages/forgot_password.php');
    }
    
    $user_id = $_SESSION['reset_user_id'];
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Please fill in all fields.";
        redirect('../pages/reset_password.php');
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        redirect('../pages/reset_password.php');
    }
    
    if (strlen($new_password) < 6) {
        $_SESSION['error_message'] = "Password must be at least 6 characters.";
        redirect('../pages/reset_password.php');
    }

    if (!preg_match('/^[A-Z]/', $new_password)) {
        $_SESSION['error_message'] = "Password must start with a capital letter.";
        redirect('../pages/reset_password.php');
    }

    if (!preg_match('/[0-9]/', $new_password)) {
        $_SESSION['error_message'] = "Password must contain at least one number.";
        redirect('../pages/reset_password.php');
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
        $_SESSION['error_message'] = "Password must contain at least one special character (e.g. @).";
        redirect('../pages/reset_password.php');
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        unset($_SESSION['reset_user_id']);
        $_SESSION['success_message'] = "Password reset successfully. You can now login.";
        redirect('../pages/login.php');
    } else {
        $_SESSION['error_message'] = "Failed to reset password. Please try again.";
        redirect('../pages/reset_password.php');
    }
}
?>
