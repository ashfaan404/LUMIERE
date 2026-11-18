<?php
// admin/actions/add_promotion_action.php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title            = sanitize($conn, $_POST['title']);
    $description      = sanitize($conn, $_POST['description']);
    $discount_percent = intval($_POST['discount_percent']);
    $start_date       = $_POST['start_date'];
    $end_date         = $_POST['end_date'];
    $product_id       = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;

    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = "Title and Description are required.";
        header("Location: ../pages/add_promotion.php");
        exit;
    }

    // Handle Image Upload
    $banner_path = "";
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
        $target_dir = "../../assets/uploads/promotions/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
        $new_filename = "promo_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_file)) {
                $banner_path = "assets/uploads/promotions/" . $new_filename;
            } else {
                $_SESSION['error_message'] = "Failed to upload image.";
                header("Location: ../pages/add_promotion.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and WEBP allowed.";
            header("Location: ../pages/add_promotion.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Banner image is required.";
        header("Location: ../pages/add_promotion.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO promotions (title, description, discount_percent, start_date, end_date, banner_image, product_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssi", $title, $description, $discount_percent, $start_date, $end_date, $banner_path, $product_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Promotion launched successfully!";
        header("Location: ../pages/promotions.php");
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        header("Location: ../pages/add_promotion.php");
    }
} else {
    header("Location: ../pages/promotions.php");
}
exit;
