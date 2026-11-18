<?php
// actions/submit_review.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../customer/orders.php");
    exit();
}

$order_id = intval($_POST['order_id'] ?? 0);
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$review_title = trim($_POST['review_title'] ?? '');
$review_text = trim($_POST['review_text'] ?? '');

// Validate
if (!$order_id || !$product_id) {
    $_SESSION['error_message'] = "Invalid review submission.";
    header("Location: ../customer/orders.php");
    exit();
}

if ($rating < 1 || $rating > 5) {
    $_SESSION['error_message'] = "Please select a star rating.";
    header("Location: ../customer/order-details.php?id=" . $order_id);
    exit();
}

if (empty($review_text)) {
    $_SESSION['error_message'] = "Please write a review before submitting.";
    header("Location: ../customer/order-details.php?id=" . $order_id);
    exit();
}

// Make sure the order belongs to this user
$stmt_ord = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?");
$stmt_ord->bind_param("ii", $order_id, $user_id);
$stmt_ord->execute();
if ($stmt_ord->get_result()->num_rows === 0) {
    $_SESSION['error_message'] = "Order not found.";
    header("Location: ../customer/orders.php");
    exit();
}

// Make sure product was part of the order
$stmt_item = $conn->prepare("SELECT order_item_id FROM order_items WHERE order_id = ? AND product_id = ?");
$stmt_item->bind_param("ii", $order_id, $product_id);
$stmt_item->execute();
if ($stmt_item->get_result()->num_rows === 0) {
    $_SESSION['error_message'] = "You can only review products you ordered.";
    header("Location: ../customer/order-details.php?id=" . $order_id);
    exit();
}

// Prevent duplicate review
$stmt_dup = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
$stmt_dup->bind_param("iii", $user_id, $product_id, $order_id);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    $_SESSION['error_message'] = "You have already reviewed this product for this order.";
    header("Location: ../customer/order-details.php?id=" . $order_id);
    exit();
}

// Insert review
$stmt_ins = $conn->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, review_title, review_text) VALUES (?, ?, ?, ?, ?, ?)");
$stmt_ins->bind_param("iiiiss", $product_id, $user_id, $order_id, $rating, $review_title, $review_text);

if ($stmt_ins->execute()) {
    $_SESSION['success_message'] = "Thank you! Your review has been submitted.";
} else {
    $_SESSION['error_message'] = "Failed to submit review. Please try again.";
}

header("Location: ../customer/order-details.php?id=" . $order_id);
exit();
