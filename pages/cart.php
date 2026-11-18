<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;

?>

<div class="mb-4 text-center">
    <h1 class="section-title">Your Shopping Collection</h1>
    <p style="color: var(--light-text);">Review your selections before proceeding to checkout.</p>
</div>

<div class="cart-layout">
    <?php if (empty($cart)): ?>
        <div class="cart-empty-state">
            <div class="alert alert-error mb-2">Your collection is currently empty.</div>
            <p><a href="shop.php" class="btn btn-primary" style="width: auto;">Explore Fragrances</a></p>
        </div>
    <?php else: ?>
        <div class="cart-items-container">
            <?php foreach($cart as $key => $item): 
                if (!is_array($item)) {
                    unset($_SESSION['cart'][$key]);
                    continue;
                }
                $item_total = $item['price'] * $item['quantity'];
                $subtotal += $item_total;
            ?>
                <div class="cart-item-card">
                    <div class="cart-item-img">
                        <?php echo renderImage($item['image'], $item['product_name']); ?>
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                        <h4 class="cart-item-title">
                            <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </a>
                        </h4>
                        <div class="cart-item-price"><?php echo formatPrice($item['price']); ?> | <span style="font-size:0.8rem; color:#888;"><?php echo $item['size']; ?></span></div>
                    </div>
                    <div class="cart-item-actions">
                        <form action="../actions/update_cart.php" method="POST" class="cart-qty-form">
                            <input type="hidden" name="cart_key" value="<?php echo $key; ?>">
                            <div class="qty-input-group">
                                <label>QTY</label>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                            </div>
                            <button type="submit" class="btn-text">Update</button>
                        </form>
                        <div class="cart-item-total">
                            <span>TOTAL</span>
                            <strong><?php echo formatPrice($item_total); ?></strong>
                        </div>
                        <a href="../actions/remove_from_cart.php?key=<?php echo urlencode($key); ?>" class="cart-remove-link">&times; Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="mt-2">
                <a href="shop.php" class="btn-text">&larr; Continue Shopping</a>
            </div>
        </div>

        <aside class="cart-summary-sidebar">
            <div class="summary-card">
                <h3 class="summary-title">Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                
                <?php
                $discount_percent = 0;
                $discount_amount = 0;
                $promo_product_id = null;

                if (isset($_SESSION['active_promotion'])) {
                    $promo_id = $_SESSION['active_promotion'];
                    $promo_stmt = $conn->prepare("SELECT discount_percent, product_id FROM promotions WHERE promotion_id = ? AND end_date >= CURRENT_DATE() AND start_date <= CURRENT_DATE()");
                    $promo_stmt->bind_param("i", $promo_id);
                    $promo_stmt->execute();
                    $promo_res = $promo_stmt->get_result();
                    if ($promo_res->num_rows > 0) {
                        $promo_row = $promo_res->fetch_assoc();
                        $discount_percent = $promo_row['discount_percent'];
                        $promo_product_id = $promo_row['product_id'];
                    } else {
                        // Expired or invalid
                        unset($_SESSION['active_promotion']);
                    }
                }

                if ($discount_percent > 0) {
                    if ($promo_product_id) {
                        // Targeted promotion
                        $targeted_subtotal = 0;
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                if (isset($item['product_id']) && $item['product_id'] == $promo_product_id) {
                                     $targeted_subtotal += ($item['price'] * $item['quantity']);
                                }
                            }
                        }
                        $discount_amount = ($targeted_subtotal * $discount_percent) / 100;
                        
                        if ($targeted_subtotal == 0) {
                            $discount_percent = 0; // Item not in cart
                        }
                    } else {
                        // Global promotion
                        $discount_amount = ($subtotal * $discount_percent) / 100;
                    }
                }
                
                $estimated_total = $subtotal - $discount_amount;
                ?>

                <?php if ($discount_percent > 0): ?>
                <div class="summary-row" style="color: #d32f2f; align-items: center;">
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span>Promotion (<?php echo $discount_percent; ?>% OFF)</span>
                        <a href="../actions/remove_offer.php" style="font-size: 0.75rem; color: #888; text-decoration: underline;">Remove offer</a>
                    </div>
                    <span>-<?php echo formatPrice($discount_amount); ?></span>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color: var(--success); font-weight: 600;">Complimentary</span>
                </div>
                <div class="summary-row total">
                    <span>Estimated Total</span>
                    <span><?php echo formatPrice($estimated_total); ?></span>
                </div>
                <p class="tax-note">Taxes and duties included where applicable.</p>
                <a href="checkout.php" class="btn btn-primary mt-2">Proceed to Checkout</a>
                
                <div class="cart-trust-badges">
                    <div class="badge-item"><span>Authenticity Guaranteed</span></div>
                    <div class="badge-item"><span>Secure Encryption</span></div>
                </div>
            </div>
        </aside>
    <?php endif; ?>
</div>


<?php require_once '../includes/footer.php'; ?>
