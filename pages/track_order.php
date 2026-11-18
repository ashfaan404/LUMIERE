<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// If no ID is provided, try to show the latest order for logged in user
$order_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$order_id && isLoggedIn()) {
    $stmt = $conn->prepare("SELECT order_id FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0) {
        $order_id = $res->fetch_assoc()['order_id'];
    }
}

if(!$order_id) {
    echo "<div class='text-center mt-3 mb-3'><h3>No order specified to track.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order) {
    echo "<div class='text-center mt-3 mb-3'><h3>Order not found.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

// Ensure the logged-in user owns the order, or allow public tracking if desired. We'll enforce login for security
if(isLoggedIn() && $order['user_id'] != $_SESSION['user_id']) {
    echo "<div class='text-center mt-3 mb-3'><h3>You do not have permission to view this order.</h3></div>";
    require_once '../includes/footer.php';
    exit;
} elseif(!isLoggedIn()) {
    $_SESSION['error_message'] = "Please log in to track your order.";
    redirect('login.php');
}

$status_steps = ['Placed', 'Confirmed', 'Packed', 'Shipped', 'Delivered'];
$current_status_index = array_search($order['order_status'], $status_steps);
if($current_status_index === false) $current_status_index = 0; // Default fallback

// Fetch order items
$stmt_items = $conn->prepare("SELECT oi.*, p.product_name, p.main_image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items = $stmt_items->get_result();
?>

<div class="mb-3 text-center">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 500; margin-bottom: 0.5rem;">Track Order #<?php echo str_pad($order_id, 6, "0", STR_PAD_LEFT); ?></h2>
    <div style="width: 30px; height: 2px; background: #D4B483; margin: 0.8rem auto;"></div>
</div>

<div style="max-width: 900px; margin: 0 auto; padding: 2rem 1rem;">
    <div style="background: #fff; padding: 3rem; border-radius: 20px; box-shadow: 0 10px 50px rgba(0,0,0,0.04); border: 1px solid #f0f0f0;">
        
        <!-- Header Info Row -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem;">
            <div>
                <div style="color: #999; font-size: 0.85rem; margin-bottom: 0.5rem;">Order Number</div>
                <div style="font-family: 'Montserrat', sans-serif; font-size: 1.2rem; font-weight: 700; color: #1a1a1a;">ORD-<?php echo str_pad($order_id, 6, "0", STR_PAD_LEFT); ?></div>
            </div>
            <div style="text-align: right;">
                <div style="color: #999; font-size: 0.85rem; margin-bottom: 0.5rem;">Payment Method</div>
                <div style="font-family: 'Montserrat', sans-serif; font-size: 1.2rem; font-weight: 700; color: #1a1a1a;"><?php echo htmlspecialchars($order['payment_method']); ?></div>
            </div>
        </div>

        <div style="height: 1px; background: #f0f0f0; margin-bottom: 3.5rem;"></div>

        <!-- Progress Bar -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative;">
            <!-- Connector Line -->
            <div style="position: absolute; top: 25px; left: 50px; right: 50px; height: 2px; background: #eee; z-index: 1;"></div>
            
            <?php 
            $icons = ['🕒', '✔️', '📦', '🚚', '✅'];
            $labels = ['Order Placed', 'Order Confirmed', 'Packed', 'Shipped', 'Delivered'];
            
            foreach($status_steps as $index => $step): 
                $is_completed = $index <= $current_status_index;
                $is_current = $index == $current_status_index;
            ?>
                <div style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; width: 120px; text-align: center;">
                    <!-- Icon Circle -->
                    <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all 0.3s ease;
                        <?php 
                        if($is_completed) echo "background: " . ($is_current ? "#1a1a1a" : "#D4B483") . "; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);";
                        else echo "background: white; color: #ccc; border: 2px solid #eee;";
                        ?>">
                        <?php echo $icons[$index]; ?>
                    </div>
                    
                    <!-- Label -->
                    <div style="margin-top: 1.2rem; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; font-weight: 700; color: <?php echo $is_completed ? '#1a1a1a' : '#ccc'; ?>;">
                        <?php echo $labels[$index]; ?>
                    </div>
                    
                    <!-- Date (Placeholder if not available) -->
                    <div style="margin-top: 0.5rem; font-size: 0.75rem; color: <?php echo $is_completed ? '#888' : '#eee'; ?>; line-height: 1.4;">
                        <?php 
                        if ($index === 0) {
                            echo date('M j, g:i A', strtotime($order['order_date']));
                        } elseif ($is_completed) {
                            echo date('M j, g:i A', strtotime($order['order_date'] . " +$index days")); // Simulated for mockup
                        } else {
                            echo "Pending";
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Order Items (Simplified list no images) -->
        <div style="margin-top: 5rem;">
            <h4 style="font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 1rem;">Items Summary</h4>
            <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                <?php 
                $order_items->data_seek(0);
                while($item = $order_items->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
                        <div>
                            <div style="font-weight: 600; color: #1a1a1a;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div style="font-size: 0.85rem; color: #888;">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div style="font-weight: 700; color: #1a1a1a;">
                            <?php echo formatPrice($item['unit_price'] * $item['quantity']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #1a1a1a; display: flex; justify-content: space-between; font-weight: 800; font-size: 1.2rem;">
                    <span>Total Amount</span>
                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <div style="margin-top: 4rem; text-align: center;">
            <a href="dashboard.php" style="font-family: 'Montserrat', sans-serif; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #aaa; text-decoration: none; display: inline-flex; align-items: center; gap: 0.8rem; transition: color 0.3s;">
                ← Back to My Account
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
