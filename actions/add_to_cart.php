<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('../pages/shop.php');
}

$product_id = intval($_GET['id']);
$variant_id = isset($_GET['variant_id']) ? intval($_GET['variant_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

if ($quantity < 1) $quantity = 1;

if ($variant_id > 0) {
    // Fetch variant details
    $stmt = $conn->prepare("SELECT v.*, p.product_name, p.brand, p.main_image FROM product_variants v JOIN products p ON v.product_id = p.product_id WHERE v.variant_id = ? AND v.product_id = ? AND v.stock > 0");
    $stmt->bind_param("ii", $variant_id, $product_id);
} else {
    // Fallback to main product data
    $stmt = $conn->prepare("SELECT product_id, product_name, brand, price, main_image, stock, size_ml FROM products WHERE product_id = ? AND stock > 0");
    $stmt->bind_param("i", $product_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item_data = $result->fetch_assoc();
    $price = $item_data['price'];
    $stock = $item_data['stock'];
    $size = isset($item_data['size_ml']) ? $item_data['size_ml'] : (isset($item_data['size_ml']) ? $item_data['size_ml'] : '');
    
    // Create a unique key for the cart item (product_id + variant_id)
    $cart_key = $product_id . '_' . $variant_id;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product with this variant already in cart
    if (isset($_SESSION['cart'][$cart_key])) {
        $new_quantity = $_SESSION['cart'][$cart_key]['quantity'] + $quantity;
        $_SESSION['cart'][$cart_key]['quantity'] = ($new_quantity > $stock) ? $stock : $new_quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'product_name' => $item_data['product_name'],
            'brand' => $item_data['brand'],
            'price' => $price,
            'size' => $size . 'ml',
            'image' => $item_data['main_image'],
            'quantity' => ($quantity > $stock) ? $stock : $quantity,
            'stock' => $stock
        ];
    }
    
    $_SESSION['success_message'] = "Added to cart successfully!";
} else {
    $_SESSION['error_message'] = "Product is out of stock or does not exist.";
}

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../pages/shop.php');
?>
