<?php
require_once '../includes/functions.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    if (isset($_SESSION['compare'])) {
        $key = array_search($product_id, $_SESSION['compare']);
        if ($key !== false) {
            unset($_SESSION['compare'][$key]);
            $_SESSION['success_message'] = "Product removed from compare list.";
        }
    }
}

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/compare.php');
?>
