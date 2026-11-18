<?php
// admin/actions/update_order_status.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id   = intval($_POST['order_id']);
    $status     = sanitize($conn, $_POST['status']);
    $pay_status = sanitize($conn, $_POST['pay_status']);

    if ($order_id) {
        // Fetch customer info and current order info before update
        $info_stmt = $conn->prepare("SELECT o.payment_method, u.email, u.full_name 
                                     FROM orders o 
                                     JOIN users u ON o.user_id = u.user_id 
                                     WHERE o.order_id = ?");
        $info_stmt->bind_param("i", $order_id);
        $info_stmt->execute();
        $order_info = $info_stmt->get_result()->fetch_assoc();

        // Start transaction
        $conn->begin_transaction();
        try {
            // Update orders table
            $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
            $stmt->bind_param("ssi", $status, $pay_status, $order_id);
            $stmt->execute();

            // Log to history
            $updater = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'staff' ? 'staff member' : 'administrator';
            $note    = "Status updated by $updater.";
            $stmt_hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, status_note) VALUES (?, ?, ?)");
            $stmt_hist->bind_param("iss", $order_id, $status, $note);
            $stmt_hist->execute();

            $conn->commit();
            $_SESSION['success_message'] = "Order #$order_id updated to \"$status\" successfully.";

            // ── Send email notification to customer ──
            if ($order_info && !empty($order_info['email'])) {
                sendOrderStatusEmail(
                    $order_info['email'],
                    $order_info['full_name'],
                    $order_id,
                    $status,
                    $order_info['payment_method']
                );
            }

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error updating order: " . $e->getMessage();
        }
    }
    header("Location: ../pages/order_details.php?id=$order_id");
    exit();
}
?>
