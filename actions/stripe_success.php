<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_GET['session_id'])) {
    redirect('../pages/shop.php');
}

$session_id = $_GET['session_id'];

// Verify payment with Stripe API
$ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . $session_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
$response     = curl_exec($ch);
$session_data = json_decode($response, true);
curl_close($ch);

if (!isset($session_data['payment_status']) || $session_data['payment_status'] !== 'paid') {
    $_SESSION['error_message'] = "Payment was not completed. Please try again.";
    redirect('../pages/checkout.php');
}

// Payment confirmed — now create the order from the pending checkout session
if (!isset($_SESSION['pending_checkout'])) {
    // Already processed or session lost
    $_SESSION['error_message'] = "Checkout session expired. Please place your order again.";
    redirect('../pages/checkout.php');
}

$checkout       = $_SESSION['pending_checkout'];
$user_id        = $checkout['user_id'];
$total_amount   = $checkout['total_amount'];
$payment_method = $checkout['payment_method']; // 'Card Payment'
$delivery_addr  = $checkout['delivery_address'];
$cart           = $checkout['cart'];

$conn->begin_transaction();
try {
    $order_status   = 'Placed';
    $payment_status = 'Paid';

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, delivery_address, order_status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $user_id, $total_amount, $payment_method, $payment_status, $delivery_addr, $order_status);
    $stmt->execute();
    $order_id = $conn->insert_id;

    $stmt_item       = $conn->prepare("INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_stock_main = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    $stmt_stock_var  = $conn->prepare("UPDATE product_variants SET stock = stock - ? WHERE variant_id = ?");

    foreach ($cart as $item) {
        $item_subtotal = $item['price'] * $item['quantity'];
        $v_id          = isset($item['variant_id']) ? intval($item['variant_id']) : 0;

        $stmt_item->bind_param("iiiidd", $order_id, $item['product_id'], $v_id, $item['quantity'], $item['price'], $item_subtotal);
        $stmt_item->execute();

        if ($v_id > 0) {
            $stmt_stock_var->bind_param("ii", $item['quantity'], $v_id);
            $stmt_stock_var->execute();
        }
        $stmt_stock_main->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmt_stock_main->execute();
    }

    $conn->commit();

    // Admin Notification
    $notif_msg  = "Paid Card Order #" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " received (" . formatPrice($total_amount) . ")";
    $notif_link = "orders.php?id=" . $order_id;
    $notif_stmt = $conn->prepare("INSERT INTO admin_notifications (type, message, link) VALUES ('new_order', ?, ?)");
    $notif_stmt->bind_param("ss", $notif_msg, $notif_link);
    $notif_stmt->execute();

    // Clear session
    unset($_SESSION['cart']);
    unset($_SESSION['active_promotion']);
    unset($_SESSION['pending_checkout']);

    $_SESSION['success_message'] = "Payment successful! Your order has been placed.";
    redirect('../pages/order_success.php?id=' . $order_id);

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Payment received but order failed. Please contact support.";
    redirect('../pages/checkout.php');
}
?>
