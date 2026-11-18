<?php
// admin/pages/users.php
require_once '../includes/admin_header.php';

$users = $conn->query("SELECT u.*, 
                       (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) as order_count,
                       (SELECT SUM(total_amount) FROM orders o WHERE o.user_id = u.user_id AND o.payment_status = 'Paid') as total_spend
                       FROM users u 
                       ORDER BY u.created_at DESC");
?>

<div class="card">
    <div class="card-header">
        <h3>User Management</h3>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact Info</th>
                    <th>Joined Date</th>
                    <th>Total Orders</th>
                    <th>Total Spend</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap: 1rem;">
                            <div style="width:40px; height:40px; border-radius:50%; background:#e8eaf6; color:var(--admin-navy); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.2rem;">
                                <?php echo substr($u['full_name'], 0, 1); ?>
                            </div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($u['email']); ?></div>
                        <div style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($u['phone']); ?></div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                    <td style="text-align:center; font-weight:600;"><?php echo $u['order_count']; ?></td>
                    <td style="font-weight: 700; color: var(--admin-gold);">$<?php echo number_format($u['total_spend'] ?? 0, 2); ?></td>
                    <td>
                        <span class="badge badge-success">Active</span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
