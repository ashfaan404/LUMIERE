<?php
// admin/actions/delete_promotion.php
session_start();
require_once '../../includes/db.php';
require_once '../includes/admin_auth.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $promo_id = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM promotions WHERE promotion_id = ?");
    $stmt->bind_param("i", $promo_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Promotion removed successfully.";
    } else {
        $_SESSION['error_message'] = "Error removing promotion.";
    }
}

header("Location: ../pages/promotions.php");
exit;
