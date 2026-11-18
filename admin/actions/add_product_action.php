<?php
// admin/actions/add_product_action.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['product_name']);
    $brand = sanitize($conn, $_POST['brand']);
    $cat_id = intval($_POST['category_id']);
    $desc = sanitize($conn, $_POST['description']);
    $top = sanitize($conn, $_POST['top_notes']);
    $middle = sanitize($conn, $_POST['middle_notes']);
    $base = sanitize($conn, $_POST['base_notes']);

    // Get variants array
    $variants = $_POST['variants'] ?? [];
    if (empty($variants)) {
        $_SESSION['error_message'] = "At least one size variant is required.";
        header("Location: ../pages/add_product.php");
        exit();
    }

    // Default primary values from first variant for backwards compatibility in product table
    $price = floatval($variants[0]['price']);
    $stock = intval($variants[0]['stock']);
    $size = intval($variants[0]['size']);

    // Handle Image Upload
    $image_path = "assets/images/placeholder.jpg";
    if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['product_img']['name'], PATHINFO_EXTENSION);
        $filename = 'perfume_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['product_img']['tmp_name'], $target)) {
            $image_path = 'assets/images/products/' . $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (category_id, product_name, brand, price, stock, size_ml, description, top_notes, middle_notes, base_notes, main_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdiisssss", $cat_id, $name, $brand, $price, $stock, $size, $desc, $top, $middle, $base, $image_path);
    
    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;

        // Insert Variants
        $v_stmt = $conn->prepare("INSERT INTO product_variants (product_id, size_ml, price, stock) VALUES (?, ?, ?, ?)");
        foreach ($variants as $v) {
            $v_size = intval($v['size']);
            $v_price = floatval($v['price']);
            $v_stock = intval($v['stock']);
            $v_stmt->bind_param("iidi", $product_id, $v_size, $v_price, $v_stock);
            $v_stmt->execute();
        }

        $_SESSION['success_message'] = "Product and variants added successfully!";
        header("Location: ../pages/products.php");
    } else {
        $_SESSION['error_message'] = "Error adding product and variants.";
        header("Location: ../pages/add_product.php");
    }
    exit();
}
?>
