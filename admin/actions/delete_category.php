<?php
// admin/actions/delete_category.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    // Check if category has products first (secondary check)
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $count = $stmt_check->get_result()->fetch_row()[0];

    if ($count > 0) {
        $_SESSION['error_message'] = "Cannot delete category that still has products.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category deleted successfully.";
        }
    }
}

header("Location: ../pages/categories.php");
exit();
?>
