<?php
// customer/actions/cancel_order.php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please log in first.";
    header("Location: ../../pages/login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id) {
    $stmt = $conn->prepare("SELECT order_status, order_date, payment_method, total_amount FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $order = $result->fetch_assoc();

        $is_cancellable = in_array($order['order_status'], ['Placed', 'Pending', 'Confirmed']);
        $is_within_time = strtotime($order['order_date']) >= strtotime('-1 day');

        if ($is_cancellable && $is_within_time) {
            $update = $conn->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?");
            $update->bind_param("i", $order_id);

            if ($update->execute()) {
                // Send admin notification about the cancellation
                $order_num = str_pad($order_id, 6, '0', STR_PAD_LEFT);
                $method    = $order['payment_method'];
                $amount    = formatPrice($order['total_amount']);

                if ($method === 'Card Payment') {
                    $notif_msg = "❌ Order #$order_num CANCELLED (Card Payment - Refund Due: $amount)";
                } else {
                    $notif_msg = "❌ Order #$order_num CANCELLED by customer (COD - $amount)";
                }
                $notif_link = "pages/order_details.php?id=" . $order_id;
                $notif_stmt = $conn->prepare("INSERT INTO admin_notifications (type, message, link) VALUES ('order_cancel', ?, ?)");
                $notif_stmt->bind_param("ss", $notif_msg, $notif_link);
                $notif_stmt->execute();

                $_SESSION['success_message'] = "Order #$order_num has been successfully cancelled." .
                    ($method === 'Card Payment' ? " Your payment will be refunded within 15 working days." : "");
            } else {
                $_SESSION['error_message'] = "Failed to cancel order. Please try again.";
            }
        } else {
            if (!$is_within_time) {
                $_SESSION['error_message'] = "This order cannot be cancelled as it was placed more than 24 hours ago.";
            } else {
                $_SESSION['error_message'] = "This order cannot be cancelled as it is already " . strtolower($order['order_status']) . ".";
            }
        }
    } else {
        $_SESSION['error_message'] = "Order not found or access denied.";
    }
} else {
    $_SESSION['error_message'] = "Invalid order ID.";
}

header("Location: ../order-details.php?id=" . $order_id);
exit();
?>
