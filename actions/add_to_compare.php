<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    if (!isset($_SESSION['compare'])) {
        $_SESSION['compare'] = [];
    }
    
    // Check if max 4 items reached
    if (count($_SESSION['compare']) >= 4 && !in_array($product_id, $_SESSION['compare'])) {
        $_SESSION['error_message'] = "You can only compare up to 4 products at a time.";
    } else {
        // Add if not already present
        if (!in_array($product_id, $_SESSION['compare'])) {
            $_SESSION['compare'][] = $product_id;
            $_SESSION['success_message'] = "Product added to compare list.";
        } else {
            $_SESSION['error_message'] = "Product is already in your compare list.";
        }
    }
}

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/shop.php');
?>
