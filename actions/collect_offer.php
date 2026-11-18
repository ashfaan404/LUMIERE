<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isset($_GET['id'])) {
    $promo_id = intval($_GET['id']);
    
    // Verify it's a valid and active promo (global or targeted)
    $stmt = $conn->prepare("SELECT promotion_id FROM promotions WHERE promotion_id = ? AND end_date >= CURRENT_DATE() AND start_date <= CURRENT_DATE()");
    $stmt->bind_param("i", $promo_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['active_promotion'] = $promo_id;
        $_SESSION['success_message'] = "Offer collected successfully! It will be applied to your cart.";
    } else {
        $_SESSION['error_message'] = "Invalid or expired offer.";
    }
}
redirect('../pages/promotions.php');
