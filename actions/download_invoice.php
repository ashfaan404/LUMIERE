<?php
// actions/download_invoice.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    header("Location: ../customer/orders.php");
    exit();
}

// Load order (ownership check)
$stmt_ord = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt_ord->bind_param("ii", $order_id, $user_id);
$stmt_ord->execute();
$order = $stmt_ord->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = "Order not found.";
    header("Location: ../customer/orders.php");
    exit();
}

// Load user
$stmt_user = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

// Load order items
$stmt_items = $conn->prepare(
    "SELECT oi.*, p.product_name, p.brand FROM order_items oi
     JOIN products p ON oi.product_id = p.product_id
     WHERE oi.order_id = ?"
);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();

$order_num = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);

// Output as HTML invoice (browser can print/save)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice - Order #<?php echo $order_num; ?></title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size:13px; color:#222; padding:2rem; background:#fff; }
    .inv-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; padding-bottom:1.5rem; border-bottom:2px solid #3E2723; }
    .inv-brand { font-size:1.8rem; font-weight:bold; color:#3E2723; }
    .inv-brand span { color:#C5A059; }
    .inv-title { font-size:1.2rem; font-weight:bold; color:#3E2723; }
    .inv-num { color:#888; font-size:0.9rem; margin-top:0.3rem; }
    .info-section { display:flex; gap:3rem; margin-bottom:1.5rem; }
    .info-box h4 { font-size:0.78rem; text-transform:uppercase; letter-spacing:1px; color:#aaa; margin-bottom:0.5rem; }
    .info-box p { line-height:1.6; color:#444; }
    table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
    th { background:#3E2723; color:#fff; padding:0.7rem 0.9rem; text-align:left; font-size:0.82rem; }
    td { padding:0.7rem 0.9rem; border-bottom:1px solid #f0ebe2; }
    .total-section { margin-left:auto; max-width:250px; }
    .total-row { display:flex; justify-content:space-between; padding:0.4rem 0; font-size:0.9rem; }
    .total-row.grand { font-weight:bold; font-size:1rem; padding-top:0.7rem; border-top:1px solid #ccc; }
    .footer-note { margin-top:2rem; text-align:center; color:#aaa; font-size:0.82rem; }
    @media print { body { padding:0; } }
</style>
</head>
<body>
<div class="inv-header">
    <div>
        <div class="inv-brand">LUMIÈRE</div>
        <div style="color:#888; font-size:0.85rem; margin-top:0.3rem;">Luxury Fragrances</div>
    </div>
    <div style="text-align:right;">
        <div class="inv-title">INVOICE</div>
        <div class="inv-num">Order #<?php echo $order_num; ?></div>
        <div class="inv-num">Date: <?php echo date('d M Y', strtotime($order['order_date'])); ?></div>
    </div>
</div>

<div class="info-section">
    <div class="info-box">
        <h4>Bill To</h4>
        <p>
            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
            <?php echo htmlspecialchars($user['email']); ?><br>
            <?php echo htmlspecialchars($user['phone']); ?>
        </p>
    </div>
    <div class="info-box">
        <h4>Ship To</h4>
        <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
    </div>
    <div class="info-box">
        <h4>Payment</h4>
        <p>
            Method: <?php echo htmlspecialchars($order['payment_method']); ?><br>
            Status: <?php echo htmlspecialchars($order['payment_status']); ?>
        </p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Brand</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; while ($item = $items->fetch_assoc()): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
            <td><?php echo htmlspecialchars($item['brand']); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo formatPrice($item['unit_price']); ?></td>
            <td><?php echo formatPrice($item['subtotal']); ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<div class="total-section">
    <div class="total-row"><span>Subtotal</span><span><?php echo formatPrice($order['total_amount'] - 5); ?></span></div>
    <div class="total-row"><span>Shipping</span><span><?php echo formatPrice(5); ?></span></div>
    <div class="total-row grand"><span>Total</span><span><?php echo formatPrice($order['total_amount']); ?></span></div>
</div>

<div class="footer-note">
    Thank you for shopping with LUMIÈRE! &nbsp;|&nbsp; For queries: support@lumiere.com
</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>
