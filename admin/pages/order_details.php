<?php
// admin/pages/order_details.php
require_once '../includes/admin_header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email, u.phone 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        WHERE o.order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    require_once '../includes/admin_footer.php';
    exit();
}

// Fetch items
$items = $conn->query("SELECT oi.*, p.product_name, p.brand, p.main_image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.product_id 
                       WHERE oi.order_id = $id");
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
    
    <div class="admin-left">
        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h3>Order #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?> Items</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap: 1rem;">
                                <img src="<?php echo strpos($item['main_image'], 'http') === 0 ? $item['main_image'] : '../../' . $item['main_image']; ?>" width="40" height="40" style="object-fit:cover; border-radius:4px;">
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div style="font-size: 0.7rem; color: #888;"><?php echo htmlspecialchars($item['brand']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatPrice($item['unit_price']); ?></td>
                        <td style="font-weight: 700;"><?php echo formatPrice($item['subtotal']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding: 1.5rem; font-weight: 700;">Order Total:</td>
                        <td style="padding: 1.5rem; font-size: 1.2rem; font-weight: 700; color: var(--admin-navy);"><?php echo formatPrice($order['total_amount']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Shipping Address -->
        <div class="card">
            <div class="card-header">
                <h3>Delivery Address</h3>
            </div>
            <div style="padding: 2rem; line-height: 1.6; color: #555;">
                <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
            </div>
        </div>
    </div>

    <div class="admin-right">
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h3>Customer Information</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; font-size:0.7rem; font-weight:700; color:#888; text-transform:uppercase;">Name</label>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; font-size:0.7rem; font-weight:700; color:#888; text-transform:uppercase;">Email</label>
                    <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; font-size:0.7rem; font-weight:700; color:#888; text-transform:uppercase;">Phone</label>
                    <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($order['phone']); ?></div>
                </div>
                <div style="margin-bottom: 0;">
                    <label style="display:block; font-size:0.7rem; font-weight:700; color:#888; text-transform:uppercase;">Payment Method</label>
                    <div style="font-size: 0.9rem; font-weight: 600;">
                        <?php 
                        $pm = $order['payment_method'];
                        echo ($pm === 'Stripe') ? 'Card Payment' : htmlspecialchars($pm);
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Update -->
        <div class="card" style="background: #fdfbf7; border: 1px solid var(--admin-gold);">
            <div class="card-header" style="background: transparent;">
                <h3>Update Order Status</h3>
            </div>
            <div style="padding: 1.5rem;">
                <form action="../actions/update_order_status.php" method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $id; ?>">
                    <div class="form-group" style="margin-bottom: 1.2rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Order Status</label>
                        <select name="status" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; background:#fff;">
                            <option value="Placed" <?php echo $order['order_status'] == 'Placed' ? 'selected' : ''; ?>>Placed (New)</option>
                            <option value="Confirmed" <?php echo $order['order_status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="Packed" <?php echo $order['order_status'] == 'Packed' ? 'selected' : ''; ?>>Packed</option>
                            <option value="Shipped" <?php echo $order['order_status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Cancelled" <?php echo $order['order_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Payment Status</label>
                        <select name="pay_status" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; background:#fff;">
                            <option value="Pending" <?php echo $order['payment_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Paid" <?php echo $order['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid (Complete)</option>
                            <option value="Failed" <?php echo $order['payment_status'] == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Update Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
