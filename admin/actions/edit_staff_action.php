<?php
// admin/actions/edit_staff_action.php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
        $_SESSION['error_message'] = "Access denied.";
        header("Location: ../pages/dashboard.php");
        exit();
    }

    $id = intval($_POST['admin_id']);
    $name = sanitize($conn, $_POST['admin_name']);
    $email = sanitize($conn, $_POST['admin_email']);
    $password = $_POST['admin_password'];
    $current_role = sanitize($conn, $_POST['current_role']);

    // Check if email belongs to someone else in the same table
    if ($current_role === 'staff') {
        $check = $conn->prepare("SELECT staff_id FROM staffs WHERE staff_email = ? AND staff_id != ?");
    } else {
        $check = $conn->prepare("SELECT admin_id FROM admins WHERE admin_email = ? AND admin_id != ?");
    }
    $check->bind_param("si", $email, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['error_message'] = "Email already in use by another account.";
        header("Location: ../pages/edit_staff.php?id=$id&role=$current_role");
        exit();
    }

    if ($current_role === 'staff') {
        $sql = "UPDATE staffs SET staff_name=?, staff_email=? WHERE staff_id=?";
        $types = "ssi";
        $params = [$name, $email, $id];

        if (!empty($password)) {
            $sql = "UPDATE staffs SET staff_name=?, staff_email=?, staff_password=? WHERE staff_id=?";
            $types = "sssi";
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $params = [$name, $email, $hashed, $id];
        }
    } else {
        $sql = "UPDATE admins SET admin_name=?, admin_email=? WHERE admin_id=?";
        $types = "ssi";
        $params = [$name, $email, $id];

        if (!empty($password)) {
            $sql = "UPDATE admins SET admin_name=?, admin_email=?, admin_password=? WHERE admin_id=?";
            $types = "sssi";
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $params = [$name, $email, $hashed, $id];
        }
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully.";
        // Update session if editing self
        if ($id == $_SESSION['admin_id']) {
            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;
        }
        header("Location: ../pages/staff.php");
    } else {
        $_SESSION['error_message'] = "Failed to update profile.";
        header("Location: ../pages/edit_staff.php?id=$id");
    }
    exit();
}
?>
