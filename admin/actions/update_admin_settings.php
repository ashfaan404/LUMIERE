<?php
// admin/actions/update_admin_settings.php
session_start();
require_once '../../includes/db.php';
require_once '../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id         = $_SESSION['admin_id'];
    $full_name        = sanitize($conn, $_POST['full_name']);
    $email            = sanitize($conn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_passwordArray = $_POST['confirm_password']; // Keep the variable name but ensure it matches below if I change it, or fix here.
    // Actually, I'll fix it to a clean name.
    $confirm_password = $_POST['confirm_password'];

    // 1. Verify Current Password
    $stmt = $conn->prepare("SELECT admin_password FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current_password, $res['admin_password'])) {
        $_SESSION['error_message'] = "Incorrect current password. Changes not saved.";
        header("Location: ../pages/settings.php");
        exit;
    }

    // 2. Prepare Update
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = "New passwords do not match.";
            header("Location: ../pages/settings.php");
            exit;
        }
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE admins SET admin_name = ?, admin_email = ?, admin_password = ? WHERE admin_id = ?");
        $update_stmt->bind_param("sssi", $full_name, $email, $hashed_password, $admin_id);
    } else {
        $update_stmt = $conn->prepare("UPDATE admins SET admin_name = ?, admin_email = ? WHERE admin_id = ?");
        $update_stmt->bind_param("ssi", $full_name, $email, $admin_id);
    }

    if ($update_stmt->execute()) {
        $_SESSION['admin_name'] = $full_name;
        $_SESSION['admin_email'] = $email;
        $_SESSION['success_message'] = "Account settings updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating account: " . $conn->error;
    }

    header("Location: ../pages/settings.php");
    exit;
}
