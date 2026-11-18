<?php
// staff/pages/orders.php
require_once '../includes/staff_header.php';

// Filter by status
$status_filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';
$where_clause = $status_filter ? "WHERE o.order_status = '$status_filter'" : "WHERE o.order_status != 'Pending Payment'";

$query = "SELECT o.*, u.full_name, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.user_id 
          $where_clause
          ORDER BY o.order_date DESC";
$orders = $conn->query($query);
?>

<div class="card">
    <div class="card-header">
        <h3>Customer Orders</h3>
        <div style="display: flex; gap: 0.5rem;">
            <a href="orders.php" class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : ''; ?>" style="background:#eee; color:#333;">All</a>
            <a href="orders.php?status=Placed" class="btn btn-sm <?php echo $status_filter == 'Placed' ? 'btn-primary' : ''; ?>" style="background:#eee; color:#333;">Placed</a>
            <a href="orders.php?status=Shipped" class="btn btn-sm <?php echo $status_filter == 'Shipped' ? 'btn-primary' : ''; ?>" style="background:#eee; color:#333;">Shipped</a>
            <a href="orders.php?status=Delivered" class="btn btn-sm <?php echo $status_filter == 'Delivered' ? 'btn-primary' : ''; ?>" style="background:#eee; color:#333;">Delivered</a>
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($orders->num_rows > 0): ?>
                    <?php while($o = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($o['order_id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($o['full_name']); ?></div>
                            <div style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($o['email']); ?></div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($o['order_date'])); ?></td>
                        <td style="font-weight: 700;"><?php echo formatPrice($o['total_amount']); ?></td>
                        <td>
                            <div style="font-size: 0.8rem;">
                                <?php echo ($o['payment_method'] === 'Stripe') ? 'Card Payment' : htmlspecialchars($o['payment_method']); ?>
                            </div>
                            <span class="badge <?php echo $o['payment_status'] == 'Paid' ? 'badge-success' : 'badge-pending'; ?>" style="font-size: 0.6rem;">
                                <?php echo strtoupper($o['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php 
                                echo $o['order_status'] == 'Delivered' ? 'badge-success' : 
                                    ($o['order_status'] == 'Cancelled' ? 'badge-danger' : 'badge-info'); 
                            ?>">
                                <?php echo $o['order_status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-primary">Process</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding: 2rem; color: #888;">No orders found matching this criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/staff_footer.php'; ?>
