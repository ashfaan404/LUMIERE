<?php
// admin/actions/edit_category_action.php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $category_name = sanitize($conn, $_POST['category_name']);

    if (empty($category_name)) {
        $_SESSION['error_message'] = "Category name cannot be empty.";
        header("Location: ../pages/categories.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Category updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating category: " . $conn->error;
    }

    header("Location: ../pages/categories.php");
    exit();
} else {
    header("Location: ../pages/categories.php");
    exit();
}
?>
