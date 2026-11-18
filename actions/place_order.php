<?php
ob_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to place an order.";
    redirect('../pages/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id          = $_SESSION['user_id'];
    $delivery_address = isset($_POST['delivery_address']) ? sanitize($conn, $_POST['delivery_address']) : '';
    $payment_method   = isset($_POST['payment_method'])   ? sanitize($conn, $_POST['payment_method'])   : '';
    $cart             = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

    if (empty($delivery_address) || empty($payment_method) || empty($cart)) {
        $_SESSION['error_message'] = "Please fill all required fields and ensure cart is not empty.";
        redirect('../pages/checkout.php');
    }

    // Calculate total on server side
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }

    $discount_percent = 0;
    $discount_amount  = 0;
    $promo_product_id = null;

    if (isset($_SESSION['active_promotion'])) {
        $promo_id   = $_SESSION['active_promotion'];
        $promo_stmt = $conn->prepare("SELECT discount_percent, product_id FROM promotions WHERE promotion_id = ? AND end_date >= CURRENT_DATE() AND start_date <= CURRENT_DATE()");
        $promo_stmt->bind_param("i", $promo_id);
        $promo_stmt->execute();
        $promo_res = $promo_stmt->get_result();
        if ($promo_res->num_rows > 0) {
            $promo_row        = $promo_res->fetch_assoc();
            $discount_percent = $promo_row['discount_percent'];
            $promo_product_id = $promo_row['product_id'];
        }
    }

    if ($discount_percent > 0) {
        if ($promo_product_id) {
            $targeted_subtotal = 0;
            foreach ($cart as $item) {
                if (isset($item['product_id']) && $item['product_id'] == $promo_product_id) {
                    $targeted_subtotal += ($item['price'] * $item['quantity']);
                }
            }
            $discount_amount = ($targeted_subtotal * $discount_percent) / 100;
        } else {
            $discount_amount = ($subtotal * $discount_percent) / 100;
        }
    }

    $total_amount = $subtotal - $discount_amount;

    // =========================================================
    // CARD PAYMENT: Store in session, go to Stripe FIRST
    // Order is only created AFTER successful payment
    // =========================================================
    if ($payment_method === 'Card Payment') {
        $_SESSION['pending_checkout'] = [
            'user_id'          => $user_id,
            'total_amount'     => $total_amount,
            'payment_method'   => 'Card Payment',
            'delivery_address' => $delivery_address,
            'cart'             => $cart,
            'active_promotion' => $_SESSION['active_promotion'] ?? null,
        ];

        $amount_cents = round($total_amount * 100);
        $post_fields  = http_build_query([
            'payment_method_types[0]'                       => 'card',
            'line_items[0][price_data][currency]'           => strtolower(CURRENCY),
            'line_items[0][price_data][product_data][name]' => 'LUMIÈRE Perfume Shop Order',
            'line_items[0][price_data][unit_amount]'        => $amount_cents,
            'line_items[0][quantity]'                       => 1,
            'mode'                                          => 'payment',
            'success_url' => rtrim(appConfig('APP_BASE_URL', 'http://localhost/LUMIERE'), '/') . '/actions/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => rtrim(appConfig('APP_BASE_URL', 'http://localhost/LUMIERE'), '/') . '/pages/checkout.php',
        ]);

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $_SESSION['error_message'] = "Payment gateway connection error. Please try again.";
            redirect('../pages/checkout.php');
        }

        $result = json_decode($response, true);
        if (isset($result['url'])) {
            redirect($result['url']); // Redirect to Stripe — NO order created yet
        } else {
            $_SESSION['error_message'] = "Payment error: " . ($result['error']['message'] ?? 'Unknown Error');
            redirect('../pages/checkout.php');
        }
    }

    // =========================================================
    // CASH ON DELIVERY: Create order immediately
    // =========================================================
    $conn->begin_transaction();
    try {
        $order_status  = 'Placed';
        $payment_status = 'Pending';

        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, delivery_address, order_status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idssss", $user_id, $total_amount, $payment_method, $payment_status, $delivery_address, $order_status);
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
        $notif_msg  = "New COD order #" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " placed (" . formatPrice($total_amount) . ")";
        $notif_link = "orders.php?id=" . $order_id;
        $notif_stmt = $conn->prepare("INSERT INTO admin_notifications (type, message, link) VALUES ('new_order', ?, ?)");
        $notif_stmt->bind_param("ss", $notif_msg, $notif_link);
        $notif_stmt->execute();

        unset($_SESSION['cart']);
        unset($_SESSION['active_promotion']);
        $_SESSION['success_message'] = "Order placed successfully!";
        redirect('../pages/order_success.php?id=' . $order_id);

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "There was an error processing your order: " . $e->getMessage();
        redirect('../pages/checkout.php');
    }
}
?>
