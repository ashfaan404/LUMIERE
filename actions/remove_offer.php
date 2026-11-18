<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isset($_SESSION['active_promotion'])) {
    unset($_SESSION['active_promotion']);
    $_SESSION['success_message'] = "Offer removed from your cart.";
}

// Redirect back to wherever they came from (cart or checkout)
$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/cart.php';
redirect($ref);
