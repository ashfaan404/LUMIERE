<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['promotion_id']) || !is_numeric($_POST['promotion_id'])) {
        $_SESSION['error_message'] = "Invalid Request.";
        header("Location: ../pages/promotions.php");
        exit;
    }

    $promotion_id     = intval($_POST['promotion_id']);
    $title            = sanitize($conn, $_POST['title']);
    $description      = sanitize($conn, $_POST['description']);
    $discount_percent = intval($_POST['discount_percent']);
    $start_date       = $_POST['start_date'];
    $end_date         = $_POST['end_date'];
    $product_id       = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;

    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = "Title and Description are required.";
        header("Location: ../pages/edit_promotion.php?id=$promotion_id");
        exit;
    }

    // Get old banner just in case
    $stmt_old = $conn->prepare("SELECT banner_image FROM promotions WHERE promotion_id = ?");
    $stmt_old->bind_param("i", $promotion_id);
    $stmt_old->execute();
    $old_promo = $stmt_old->get_result()->fetch_assoc();
    $banner_path = $old_promo['banner_image'] ?? "";

    // Handle Image Upload
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
        $target_dir = "../../assets/uploads/promotions/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
        $new_filename = "promo_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_file)) {
                $banner_path = "assets/uploads/promotions/" . $new_filename;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE promotions SET title = ?, description = ?, discount_percent = ?, start_date = ?, end_date = ?, banner_image = ?, product_id = ? WHERE promotion_id = ?");
    $stmt->bind_param("ssisssii", $title, $description, $discount_percent, $start_date, $end_date, $banner_path, $product_id, $promotion_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Promotion updated successfully!";
        header("Location: ../pages/promotions.php");
    } else {
        $_SESSION['error_message'] = "Error updating promotion: " . $conn->error;
        header("Location: ../pages/edit_promotion.php?id=$promotion_id");
    }
} else {
    header("Location: ../pages/promotions.php");
}
exit;
