<?php
// customer/orders.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Load user info for sidebar
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name_parts = explode(' ', $user['full_name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';
$initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));

// Load all orders
$stmt_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders = $stmt_orders->get_result();

// Status badge helper
function statusBadge($status)
{
    $map = [
        'Placed' => 'status-placed',
        'Confirmed' => 'status-confirmed',
        'Packed' => 'status-packed',
        'Shipped' => 'status-shipped',
        'Delivered' => 'status-delivered',
        'Cancelled' => 'status-cancelled',
    ];
    $class = $map[$status] ?? 'status-placed';
    return '<span class="order-status-badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}
?>
<link rel="stylesheet" href="../assets/css/customer.css">

<div class="account-wrapper">

    <!-- Sidebar -->
    <aside class="account-sidebar">
        <div class="account-sidebar-card">
            <div class="sidebar-avatar-section">
                <div class="avatar-circle">
                    <?php if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile">
                    <?php
else: ?>
                        <?php echo htmlspecialchars($initials); ?>
                    <?php
endif; ?>
                </div>
                <div class="sidebar-user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="sidebar-user-email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <nav class="sidebar-nav">
                <a href="profile.php"><span class="nav-icon">👤</span> My Profile</a>
                <a href="wishlist.php"><span class="nav-icon">❤️</span>My Wishlist</a>
                <a href="orders.php" class="active"><span class="nav-icon">📦</span> Order History</a>
                <a href="addresses.php"><span class="nav-icon">📍</span> Saved Addresses</a>
                <a href="../pages/logout.php" class="logout-link"><span class="nav-icon">🚪</span> Sign Out</a>
            </nav>
        </div>
    </aside>

    <!-- Main -->
    <div class="account-main">
        <div class="account-page-header">
            <h2>Order History</h2>
            <p>Track and manage all your past orders</p>
        </div>

        <div class="account-card">
            <div class="account-card-title">All Orders</div>

            <?php if ($orders->num_rows > 0): ?>
                <div class="order-list">
                    <?php while ($ord = $orders->fetch_assoc()):
        $order_num = str_pad($ord['order_id'], 6, '0', STR_PAD_LEFT);
?>
                    <div class="order-list-item">
                        <div class="order-list-meta">
                            <div class="order-list-num">Order #<?php echo $order_num; ?></div>
                            <div class="order-list-date"><?php echo date('d M Y, h:i A', strtotime($ord['order_date'])); ?></div>
                        </div>
                        <div>
                            <?php echo statusBadge($ord['order_status']); ?>
                        </div>
                        <div class="order-list-amount">
                            Rs. <?php echo number_format($ord['total_amount'], 2); ?>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="order-details.php?id=<?php echo $ord['order_id']; ?>" class="account-btn" style="white-space:nowrap; font-size:0.82rem; padding:0.55rem 1.2rem;">View Details</a>
                            <?php 
                                // Check if order is eligible for cancellation (correct status AND within 1 day)
                                $is_cancellable_status = in_array($ord['order_status'], ['Placed', 'Pending', 'Confirmed']);
                                $is_within_time = strtotime($ord['order_date']) >= strtotime('-1 day');
                                if ($is_cancellable_status && $is_within_time): 
                            ?>
                                <a href="actions/cancel_order.php?id=<?php echo $ord['order_id']; ?>" class="account-btn" style="white-space:nowrap; font-size:0.82rem; padding:0.55rem 1.2rem; background: #ffebee; color: #c62828; border: 1px solid #ffcdd2;" onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">Cancel Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
    endwhile; ?>
                </div>
            <?php
else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p>You haven't placed any orders yet.</p>
                    <a href="../pages/shop.php" class="account-btn" style="display:inline-block; text-decoration:none;">Start Shopping</a>
                </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js/customer.js"></script>
<?php require_once '../includes/footer.php'; ?>
