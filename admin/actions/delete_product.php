<?php
// admin/actions/delete_product.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    // Optionally remove image file
    $stmt_img = $conn->prepare("SELECT main_image FROM products WHERE product_id = ?");
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();
    $res = $stmt_img->get_result()->fetch_assoc();
    
    if ($res && !empty($res['main_image']) && strpos($res['main_image'], 'http') !== 0) {
        $file = '../../' . $res['main_image'];
        if (file_exists($file)) unlink($file);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting product.";
    }
}

header("Location: ../pages/products.php");
exit();
?>
