<?php
require_once '../includes/functions.php';

if (isset($_GET['key'])) {
    $key = $_GET['key'];
    
    if (isset($_SESSION['cart']) && isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['success_message'] = "Item removed from collection.";
    }
}

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/cart.php');
?>
