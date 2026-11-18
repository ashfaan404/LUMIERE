<?php
// admin/actions/add_category_action.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['category_name']);

    if (empty($name)) {
        $_SESSION['error_message'] = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category '$name' created successfully.";
        } else {
            $_SESSION['error_message'] = "Error creating category (it might already exist).";
        }
    }
}
header("Location: ../pages/categories.php");
exit();
?>
