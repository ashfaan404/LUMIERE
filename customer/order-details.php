<?php
// customer/order-details.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    $_SESSION['error_message'] = "Order not found.";
    header("Location: orders.php");
    exit();
}

// Load user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$name_parts = explode(' ', $user['full_name'], 2);
$initials = strtoupper(substr($name_parts[0] ?? '', 0, 1) . substr($name_parts[1] ?? '', 0, 1));

// Load order
$stmt_ord = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt_ord->bind_param("ii", $order_id, $user_id);
$stmt_ord->execute();
$order = $stmt_ord->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = "Order not found or access denied.";
    header("Location: orders.php");
    exit();
}

// Load order items
$stmt_items = $conn->prepare(
    "SELECT oi.*, p.product_name, p.brand, p.size_ml, p.main_image
     FROM order_items oi
     JOIN products p ON oi.product_id = p.product_id
     WHERE oi.order_id = ?"
);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();

// Load status history
$history_rows = [];
try {
    $stmt_hist = $conn->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY status_date ASC");
    $stmt_hist->bind_param("i", $order_id);
    $stmt_hist->execute();
    $history = $stmt_hist->get_result();
    while ($h = $history->fetch_assoc()) {
        $history_rows[] = $h;
    }
} catch (Throwable $e) {}

$status_steps = ['Placed', 'Confirmed', 'Packed', 'Shipped', 'Delivered'];
$current_step = array_search($order['order_status'], $status_steps);
if ($current_step === false) $current_step = 0;

// Check reviewed products
$reviewed_products = [];
$rev_col_check = $conn->query("SHOW COLUMNS FROM reviews LIKE 'order_id'");
if ($rev_col_check && $rev_col_check->num_rows > 0) {
    $stmt_rev = $conn->prepare("SELECT product_id FROM reviews WHERE user_id = ? AND order_id = ?");
    $stmt_rev->bind_param("ii", $user_id, $order_id);
} else {
    $stmt_rev = $conn->prepare("SELECT product_id FROM reviews WHERE user_id = ?");
    $stmt_rev->bind_param("i", $user_id);
}
$stmt_rev->execute();
$rev_result = $stmt_rev->get_result();
while ($rev = $rev_result->fetch_assoc()) {
    $reviewed_products[] = $rev['product_id'];
}

$order_num = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
$shipping = 5.00;
$subtotal = $order['total_amount'] - $shipping;
$is_delivered = ($order['order_status'] === 'Delivered');

function statusBadge($status) {
    $map = ['Placed'=>'status-placed','Confirmed'=>'status-confirmed','Packed'=>'status-packed','Shipped'=>'status-shipped','Delivered'=>'status-delivered','Cancelled'=>'status-cancelled'];
    $class = $map[$status] ?? 'status-placed';
    return '<span class="order-status-badge '.$class.'">'.htmlspecialchars($status).'</span>';
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
                    <?php else: ?>
                        <?php echo htmlspecialchars($initials); ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar-user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="sidebar-user-email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <nav class="sidebar-nav">
                <a href="profile.php"><span class="nav-icon">👤</span> My Profile</a>
                <a href="wishlist.php"><span class="nav-icon">❤️</span> My Wishlist</a>
                <a href="orders.php" class="active"><span class="nav-icon">📦</span> Order History</a>
                <a href="addresses.php"><span class="nav-icon">📍</span> Saved Addresses</a>
                <a href="../pages/logout.php" class="logout-link"><span class="nav-icon">🚪</span> Sign Out</a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="account-main">

        <!-- Breadcrumb -->
        <div class="account-breadcrumb">
            <a href="orders.php">My Orders</a>
            <span>›</span>
            <span>Order #<?php echo $order_num; ?></span>
        </div>

        <!-- Page Header -->
        <div class="account-page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
            <div>
                <h2>Order Details</h2>
                <p>Order #<?php echo $order_num; ?> &nbsp;•&nbsp; <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?> &nbsp; <?php echo statusBadge($order['order_status']); ?></p>
            </div>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                <?php
                $is_cancellable = in_array($order['order_status'], ['Placed', 'Pending', 'Confirmed']);
                $is_within_time = strtotime($order['order_date']) >= strtotime('-1 day');
                $payment_method_for_modal = htmlspecialchars($order['payment_method']);
                if ($is_cancellable && $is_within_time):
                ?>
                    <button class="account-btn-danger"
                            id="cancelOrderBtn"
                            onclick="openCancelModal('<?php echo $order_id; ?>', '<?php echo $payment_method_for_modal; ?>')">
                        ❌ Cancel Order
                    </button>
                <?php endif; ?>
                <a href="../actions/download_invoice.php?id=<?php echo $order_id; ?>" class="account-btn-outline">🧾 Download Invoice</a>
            </div>
        </div>

        <!-- Order Details Layout -->
        <div class="order-details-layout">

            <!-- LEFT: Items + Timeline + Review -->
            <div class="order-details-main">

                <!-- Items Ordered -->
                <div class="account-card">
                    <div class="account-card-title">Items Ordered</div>
                    <?php
                    $items->data_seek(0);
                    while ($item = $items->fetch_assoc()):
                    ?>
                    <div class="order-item-row">
                        <img src="<?php echo htmlspecialchars($item['main_image']); ?>"
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                             class="order-item-img"
                             onerror="this.src='../assets/images/placeholder.jpg'">
                        <div class="order-item-details">
                            <div class="order-item-name">
                                <a href="../pages/product.php?id=<?php echo $item['product_id']; ?>" style="color:inherit; text-decoration:none;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </a>
                            </div>
                            <div class="order-item-meta">
                                <?php echo htmlspecialchars($item['brand']); ?>
                                <?php if ($item['size_ml']): ?> • <?php echo $item['size_ml']; ?>ml<?php endif; ?>
                                • Qty: <?php echo $item['quantity']; ?>
                            </div>
                            <div style="margin-top:0.5rem;">
                                <a href="../actions/buy_again.php?product_id=<?php echo $item['product_id']; ?>"
                                   class="account-btn-outline" style="font-size:0.78rem; padding:0.35rem 0.9rem;">
                                    🔁 Buy Again
                                </a>
                            </div>
                        </div>
                        <div class="order-item-price"><?php echo formatPrice($item['unit_price'] * $item['quantity']); ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Delivery Timeline -->
                <div class="account-card">
                    <div class="account-card-title">Delivery Timeline</div>
                    <div class="timeline">
                        <?php foreach ($status_steps as $idx => $step):
                            $done   = ($idx < $current_step);
                            $active = ($idx === $current_step);
                            $hist_note = '';
                            $hist_date = '';
                            foreach ($history_rows as $hr) {
                                if ($hr['status'] === $step) {
                                    $hist_note = $hr['status_note'];
                                    $hist_date = date('d M Y, h:i A', strtotime($hr['status_date']));
                                }
                            }
                        ?>
                        <div class="timeline-step <?php echo $done ? 'done' : ''; ?>">
                            <div class="timeline-dot <?php echo $done ? 'done' : ($active ? 'active' : ''); ?>">
                                <?php echo $done ? '✓' : ($active ? '●' : ($idx + 1)); ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-status" style="color:<?php echo ($done || $active) ? '#3E2723' : '#aaa'; ?>">
                                    <?php echo htmlspecialchars($step); ?>
                                </div>
                                <?php if ($hist_note): ?>
                                    <div class="timeline-note"><?php echo htmlspecialchars($hist_note); ?></div>
                                <?php endif; ?>
                                <?php if ($hist_date): ?>
                                    <div class="timeline-date"><?php echo $hist_date; ?></div>
                                <?php elseif ($active): ?>
                                    <div class="timeline-date" style="color:#C5A059;">Current status</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Review Form -->
                <?php if ($is_delivered):
                    $items->data_seek(0);
                    $has_unreviewed = false;
                    while ($item = $items->fetch_assoc()) {
                        if (!in_array($item['product_id'], $reviewed_products)) {
                            $has_unreviewed = true; break;
                        }
                    }
                    if ($has_unreviewed):
                ?>
                <div class="account-card">
                    <div class="account-card-title">Leave a Review</div>
                    <form action="../actions/submit_review.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                        <div class="account-form-group">
                            <label>Select Product</label>
                            <select name="product_id" required>
                                <option value="">-- Choose a product --</option>
                                <?php
                                $items->data_seek(0);
                                while ($item = $items->fetch_assoc()):
                                    if (in_array($item['product_id'], $reviewed_products)) continue;
                                ?>
                                <option value="<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="account-form-group">
                            <label>Your Rating</label>
                            <div class="star-rating">
                                <input type="radio" name="rating" id="s5" value="5" required><label for="s5">★</label>
                                <input type="radio" name="rating" id="s4" value="4"><label for="s4">★</label>
                                <input type="radio" name="rating" id="s3" value="3"><label for="s3">★</label>
                                <input type="radio" name="rating" id="s2" value="2"><label for="s2">★</label>
                                <input type="radio" name="rating" id="s1" value="1"><label for="s1">★</label>
                            </div>
                        </div>

                        <div class="account-form-group">
                            <label>Review Title</label>
                            <input type="text" name="review_title" placeholder="Sum up your experience" maxlength="200">
                        </div>

                        <div class="account-form-group">
                            <label>Review *</label>
                            <textarea name="review_text" required placeholder="Tell others what you think..." rows="4"></textarea>
                        </div>

                        <button type="submit" class="account-btn">Submit Review</button>
                    </form>
                </div>
                <?php elseif ($is_delivered): ?>
                <div class="account-card">
                    <div style="text-align:center; padding:1rem; color:#2E7D32; background:#E8F5E9; border-radius:8px;">
                        ✅ Thank you! You have reviewed all items in this order.
                    </div>
                </div>
                <?php endif; endif; ?>

            </div><!-- end order-details-main -->

            <!-- RIGHT: Summary + Address + Payment -->
            <div class="order-details-side">

                <!-- Order Summary -->
                <div class="account-card" style="margin-bottom:1.2rem;">
                    <div class="account-card-title">Order Summary</div>
                    <div class="order-summary-box">
                        <div class="order-summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="order-summary-row">
                            <span>Shipping</span>
                            <span><?php echo formatPrice($shipping); ?></span>
                        </div>
                        <div class="order-summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="account-card" style="margin-bottom:1.2rem;">
                    <div class="account-card-title">Shipping Address</div>
                    <?php if (!empty($order['delivery_address'])): ?>
                        <p style="font-size:0.9rem; color:#555; line-height:1.8; margin:0;">
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                        </p>
                    <?php else: ?>
                        <p style="font-size:0.9rem; color:#aaa; margin:0;">Not available</p>
                    <?php endif; ?>
                </div>

                <!-- Payment Info -->
                <div class="account-card">
                    <div class="account-card-title">Payment & Shipping</div>
                    <div style="font-size:0.9rem; color:#555;">
                        <div style="display:flex; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid #f0ebe2;">
                            <span>Payment Method</span>
                            <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid #f0ebe2;">
                            <span>Payment Status</span>
                            <strong><?php echo htmlspecialchars($order['payment_status']); ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:0.5rem 0;">
                            <span>Shipping Method</span>
                            <strong>Standard Delivery</strong>
                        </div>
                    </div>
                </div>

            </div><!-- end order-details-side -->

        </div><!-- end order-details-layout -->
    </div><!-- end account-main -->

</div><!-- end account-wrapper -->

<!-- ===== Order Cancellation Warning Modal ===== -->
<div id="cancelModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; max-width:480px; width:92%; margin:auto; box-shadow:0 20px 60px rgba(0,0,0,0.25); overflow:hidden; animation: slideUp 0.3s ease;">
        <!-- Modal Header -->
        <div id="cancelModalHeader" style="padding:1.5rem 2rem 1.2rem; border-bottom:1px solid #f0ebe2;">
            <div style="display:flex; align-items:center; gap:0.8rem;">
                <span style="font-size:2rem;" id="cancelModalIcon">⚠️</span>
                <div>
                    <h3 style="margin:0; font-size:1.15rem; color:#3E2723;" id="cancelModalTitle">Cancel Order</h3>
                    <p style="margin:4px 0 0; font-size:0.82rem; color:#888;">This action cannot be undone</p>
                </div>
            </div>
        </div>
        <!-- Modal Body -->
        <div style="padding:1.5rem 2rem;">
            <div id="cancelModalBody" style="font-size:0.95rem; color:#444; line-height:1.7;"></div>
        </div>
        <!-- Modal Footer -->
        <div style="padding:1rem 2rem 1.5rem; display:flex; gap:0.75rem; justify-content:flex-end;">
            <button onclick="closeCancelModal()"
                    style="padding:0.6rem 1.4rem; border:1px solid #ccc; background:#fff; color:#555; border-radius:8px; font-size:0.9rem; cursor:pointer; font-weight:500; transition:all 0.2s;"
                    onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'">
                No, Keep Order
            </button>
            <a id="cancelConfirmLink" href="#"
               style="padding:0.6rem 1.4rem; background:linear-gradient(135deg,#c0392b,#e74c3c); color:#fff; border-radius:8px; font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s;"
               onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                ✅ Yes, Cancel Order
            </a>
        </div>
    </div>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>

<script>
function openCancelModal(orderId, paymentMethod) {
    var modal  = document.getElementById('cancelModal');
    var body   = document.getElementById('cancelModalBody');
    var icon   = document.getElementById('cancelModalIcon');
    var title  = document.getElementById('cancelModalTitle');
    var link   = document.getElementById('cancelConfirmLink');

    link.href  = 'actions/cancel_order.php?id=' + orderId;

    if (paymentMethod === 'Card Payment') {
        icon.textContent  = '💳';
        title.textContent = 'Cancel Card Payment Order';
        body.innerHTML    =
            '<div style="background:#fff3cd; border-left:4px solid #f0ad4e; border-radius:6px; padding:1rem 1.2rem; margin-bottom:1rem;">' +
            '<strong style="color:#856404;">⚠️ Important Refund Notice</strong>' +
            '<p style="margin:0.5rem 0 0; color:#664d03;">If you cancel your order after placing it, the payment will be <strong>returned within 15 working days</strong> to your original payment method.</p>' +
            '</div>' +
            '<p style="margin:0; color:#555;">Are you sure you want to cancel this order?</p>';
    } else {
        icon.textContent  = '📦';
        title.textContent = 'Cancel Cash on Delivery Order';
        body.innerHTML    =
            '<div style="background:#e8f5e9; border-left:4px solid #4caf50; border-radius:6px; padding:1rem 1.2rem; margin-bottom:1rem;">' +
            '<strong style="color:#2e7d32;">Cash on Delivery Order</strong>' +
            '<p style="margin:0.5rem 0 0; color:#1b5e20;">No payment has been charged. Your order will be cancelled immediately.</p>' +
            '</div>' +
            '<p style="margin:0; color:#555;">Do you want to cancel this order?</p>';
    }

    modal.style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Close when clicking the overlay
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});
</script>

<?php require_once '../includes/footer.php'; ?>
