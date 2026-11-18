<?php
// admin/pages/payment_history.php
require_once '../includes/admin_header.php';

// Fetch all orders with paid status
$payments = $conn->query("SELECT o.*, u.full_name, u.email 
                         FROM orders o 
                         JOIN users u ON o.user_id = u.user_id 
                         WHERE o.payment_status = 'Paid' 
                         ORDER BY o.order_date DESC");
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 1.5rem;">💳</div>
            <div>
                <h3 style="margin:0;">Payment History</h3>
                <p style="margin:5px 0 0; font-size: 0.85rem; color: #888;">Complete log of all successful transactions.</p>
            </div>
        </div>
        <div class="stats-mini" style="display: flex; gap: 2rem;">
            <div style="text-align: right;">
                <div style="font-size: 0.7rem; color: #999; text-transform: uppercase; font-weight: 800;">Total Collected</div>
                <div style="font-size: 1.2rem; font-weight: 800; color: #2e7d32;">
                    <?php 
                    $total = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'")->fetch_row()[0];
                    echo formatPrice($total ?? 0);
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date & Time</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments && $payments->num_rows > 0): ?>
                    <?php while($p = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><strong style="color: var(--admin-navy);">#<?php echo str_pad($p['order_id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($p['full_name']); ?></div>
                            <div style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($p['email']); ?></div>
                        </td>
                        <td>
                            <div style="font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($p['order_date'])); ?></div>
                            <div style="font-size: 0.75rem; color: #999;"><?php echo date('H:i A', strtotime($p['order_date'])); ?></div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; font-weight: 600; color: #555;">
                                <?php echo strtoupper($p['payment_method']); ?>
                            </span>
                        </td>
                        <td><strong style="color: #2e7d32;"><?php echo formatPrice($p['total_amount']); ?></strong></td>
                        <td><span class="badge badge-success">Successful</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 4rem; color: #999;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🕳️</div>
                            No successful payments recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
