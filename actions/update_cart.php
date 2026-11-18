<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_key']) && isset($_POST['quantity'])) {
    $key = $_POST['cart_key'];
    $quantity = intval($_POST['quantity']);
    
    if (isset($_SESSION['cart']) && isset($_SESSION['cart'][$key])) {
        if ($quantity > 0) {
            $item = &$_SESSION['cart'][$key];
            $item['quantity'] = ($quantity > $item['stock']) ? $item['stock'] : $quantity;
            $_SESSION['success_message'] = "Collection updated.";
        } else {
            // Remove if quantity set to 0
            header("Location: remove_from_cart.php?key=" . urlencode($key));
            exit();
        }
    }
}

redirect('../pages/cart.php');
?>
