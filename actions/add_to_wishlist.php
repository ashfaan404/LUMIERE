<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please log in to add items to your wishlist.";
    redirect('../pages/login.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if product exists
    $stmt = $conn->prepare("SELECT product_id FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        
        // Check if already in wishlist
        $stmt_check = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows == 0) {
            $stmt_insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt_insert->bind_param("ii", $user_id, $product_id);
            if($stmt_insert->execute()) {
                $_SESSION['success_message'] = "Added to wishlist!";
            } else {
                $_SESSION['error_message'] = "Failed to add to wishlist.";
            }
        } else {
            $_SESSION['error_message'] = "Item is already in your wishlist.";
        }
    } else {
        $_SESSION['error_message'] = "Product not found.";
    }
}

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/shop.php');
?>
