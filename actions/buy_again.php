<?php
// actions/buy_again.php
// Adds a product back to cart and redirects to cart

require_once '../includes/db.php';
require_once '../includes/customer_check.php';

$product_id = intval($_GET['product_id'] ?? 0);

if (!$product_id) {
    header("Location: ../pages/shop.php");
    exit();
}

// Verify product exists and has stock
$stmt = $conn->prepare("SELECT product_id, product_name, brand, price, main_image, stock FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    $_SESSION['error_message'] = "Product no longer available.";
    header("Location: ../pages/shop.php");
    exit();
}

if ($product['stock'] <= 0) {
    $_SESSION['error_message'] = "Sorry, this item is currently out of stock.";
    header("Location: ../pages/shop.php");
    exit();
}

// Add to cart (session-based)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if product already in cart
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if (is_array($item) && isset($item['product_id']) && $item['product_id'] == $product_id) {
        $new_quantity = $item['quantity'] + 1;
        $item['quantity'] = ($new_quantity > $product['stock']) ? $product['stock'] : $new_quantity;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $product['product_id'],
        'product_name' => $product['product_name'],
        'brand' => $product['brand'],
        'price' => $product['price'],
        'image' => $product['main_image'],
        'quantity' => 1,
        'stock' => $product['stock']
    ];
}

$_SESSION['success_message'] = "Item added to your cart!";
header("Location: ../pages/cart.php");
exit();
