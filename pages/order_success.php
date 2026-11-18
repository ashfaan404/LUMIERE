<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Get order ID from URL (set by place_order.php / stripe_success.php)
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fallback: check session (legacy)
if (!$order_id && isset($_SESSION['last_order_id'])) {
    $order_id = $_SESSION['last_order_id'];
}

if (!$order_id) {
    redirect('shop.php');
}

// Clear legacy session key if set
unset($_SESSION['last_order_id']);

// Fetch order details — must belong to logged-in user
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ? AND o.user_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->bind_param("i", $order_id);
}
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('shop.php');
}

$order_num     = 'ORD-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
$is_card       = ($order['payment_method'] === 'Card Payment' || $order['payment_method'] === 'Stripe');
$payment_label = $is_card ? 'Card Payment' : 'Cash on Delivery';
$payment_icon  = $is_card ? '💳' : '💵';
?>

<div style="background: linear-gradient(135deg, #fdfbf7 0%, #f8f4ee 100%); min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 3rem 1rem;">
    <div style="max-width: 620px; width: 100%;">

        <!-- Success Card -->
        <div style="background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #f0ebe2;">

            <!-- Animated Top Banner -->
            <div style="background: linear-gradient(135deg, #2E7D32 0%, #43A047 100%); padding: 2.5rem 2rem; text-align: center; position: relative; overflow: hidden;">
                <div style="position: absolute; inset:0; background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2220%22 cy=%2220%22 r=%2216%22 fill=%22rgba(255,255,255,0.05)%22/><circle cx=%2280%22 cy=%2280%22 r=%2224%22 fill=%22rgba(255,255,255,0.04)%22/></svg>');"></div>
                <div style="position: relative; z-index:1;">
                    <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem; animation: popIn 0.6s cubic-bezier(0.175,0.885,0.32,1.275);">
                        <span style="font-size: 2.5rem;">✓</span>
                    </div>
                    <h2 style="color: #fff; font-size: 1.8rem; margin: 0 0 0.5rem; font-weight: 700;">
                        <?php echo $is_card ? 'Payment Successful!' : 'Order Placed Successfully!'; ?>
                    </h2>
                    <p style="color: rgba(255,255,255,0.85); font-size: 0.95rem; margin: 0;">
                        <?php echo $is_card ? 'Your payment has been confirmed and your order is being processed.' : 'Your order has been received and will be processed soon.'; ?>
                    </p>
                </div>
            </div>

            <!-- Order Details Grid -->
            <div style="padding: 2rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: #fafaf8; border-radius: 12px; padding: 1.2rem; border: 1px solid #f0ebe2;">
                        <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.4rem;">Order Number</div>
                        <div style="font-weight: 800; font-size: 1.05rem; color: #1a1a1a; font-family: monospace;"><?php echo $order_num; ?></div>
                    </div>
                    <div style="background: #fafaf8; border-radius: 12px; padding: 1.2rem; border: 1px solid #f0ebe2;">
                        <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.4rem;">Order Date</div>
                        <div style="font-weight: 600; font-size: 0.95rem; color: #1a1a1a;"><?php echo date('d M Y', strtotime($order['order_date'])); ?></div>
                    </div>
                    <div style="background: #fafaf8; border-radius: 12px; padding: 1.2rem; border: 1px solid #f0ebe2;">
                        <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.4rem;">Total Amount</div>
                        <div style="font-weight: 800; font-size: 1.1rem; color: #C5A059;"><?php echo formatPrice($order['total_amount']); ?></div>
                    </div>
                    <div style="background: #fafaf8; border-radius: 12px; padding: 1.2rem; border: 1px solid #f0ebe2;">
                        <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.4rem;">Payment</div>
                        <div style="font-weight: 600; font-size: 0.9rem; color: #1a1a1a;"><?php echo $payment_icon . ' ' . $payment_label; ?></div>
                    </div>
                </div>

                <!-- Payment-specific notice -->
                <?php if ($is_card): ?>
                <div style="background: #E8F5E9; border: 1px solid #A5D6A7; border-radius: 10px; padding: 1rem 1.2rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.8rem;">
                    <span style="font-size: 1.3rem; flex-shrink:0;">💳</span>
                    <div>
                        <div style="font-weight: 700; color: #2E7D32; margin-bottom: 0.2rem;">Payment Confirmed</div>
                        <div style="font-size: 0.85rem; color: #388E3C;">Your card payment has been successfully processed. A confirmation email has been sent to <strong><?php echo htmlspecialchars($order['email']); ?></strong>.</div>
                    </div>
                </div>
                <?php else: ?>
                <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 10px; padding: 1rem 1.2rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.8rem;">
                    <span style="font-size: 1.3rem; flex-shrink:0;">💵</span>
                    <div>
                        <div style="font-weight: 700; color: #F57F17; margin-bottom: 0.2rem;">Cash on Delivery</div>
                        <div style="font-size: 0.85rem; color: #795548;">Please keep the exact amount ready at the time of delivery. A confirmation email has been sent to <strong><?php echo htmlspecialchars($order['email']); ?></strong>.</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Delivery Estimate -->
                <div style="background: #f0f7ff; border-radius: 10px; padding: 1rem 1.2rem; margin-bottom: 1.8rem; display: flex; align-items: center; gap: 0.8rem;">
                    <span style="font-size: 1.5rem;">📦</span>
                    <div>
                        <div style="font-weight: 600; color: #1565C0; font-size: 0.9rem;">Estimated Delivery</div>
                        <div style="font-size: 0.85rem; color: #555;">3–5 Business Days — You'll receive email updates as your order progresses.</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="../customer/order-details.php?id=<?php echo $order_id; ?>"
                       style="flex:1; min-width:150px; padding: 0.9rem 1.5rem; background: linear-gradient(135deg, #3E2723, #6D4C41); color: #fff; text-align: center; border-radius: 10px; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        📦 View Order Details
                    </a>
                    <a href="shop.php"
                       style="flex:1; min-width:150px; padding: 0.9rem 1.5rem; background: #fff; color: #555; text-align: center; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        🛍️ Continue Shopping
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes popIn {
    0%   { transform: scale(0); opacity: 0; }
    70%  { transform: scale(1.15); }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
