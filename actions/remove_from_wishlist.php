<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    if($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Item removed from wishlist.";
    } else {
        $_SESSION['error_message'] = "Failed to remove item or item not in wishlist.";
    }
}

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/wishlist.php');
?>
