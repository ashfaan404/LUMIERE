<?php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    $_SESSION['error_message'] = "Your cart is empty. Please add items to checkout.";
    redirect('shop.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch default address
$default_address = null;
try {
    $stmt_addr = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 limit 1");
    $stmt_addr->bind_param("i", $user_id);
    $stmt_addr->execute();
    $addr_result = $stmt_addr->get_result();
    if ($addr_result->num_rows > 0) {
        $default_address = $addr_result->fetch_assoc();
    } else {
        // Fallback to latest address if no default
        $stmt_addr = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt_addr->bind_param("i", $user_id);
        $stmt_addr->execute();
        $addr_result = $stmt_addr->get_result();
        if ($addr_result->num_rows > 0) {
            $default_address = $addr_result->fetch_assoc();
        }
    }
} catch (Throwable $e) {}

$first_name = explode(' ', $user['full_name'])[0];
$last_name = explode(' ', $user['full_name'], 2)[1] ?? '';
$phone = $user['phone'];
$delivery_address = $user['address'];
$city = '';
$postal_code = '';

if ($default_address) {
    $names = explode(' ', $default_address['full_name'], 2);
    $first_name = $names[0] ?? '';
    $last_name = $names[1] ?? '';
    $phone = $default_address['phone'];
    $delivery_address = $default_address['street_address'];
    $city = $default_address['city'];
    $postal_code = $default_address['zip_code'];
}

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
?>

<div class="mb-3 text-center">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 500; margin-bottom: 0.5rem;">Checkout</h2>
    <div style="width: 30px; height: 2px; background: #D4B483; margin: 0.8rem auto;"></div>
</div>

<div class="checkout-container">
    
    <!-- Left Column: Shipping & Payment -->
    <div class="checkout-left">
        <div class="checkout-card">
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <div style="width: 45px; height: 45px; background: #FFF5F5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FF5252;">
                    <span style="font-size: 1.4rem;">📍</span>
                </div>
                <h3 style="margin: 0; font-family: 'Montserrat', sans-serif; font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px;">Shipping Address</h3>
            </div>
            
            <form action="../actions/place_order.php" method="POST" id="checkoutForm">
                <div class="checkout-grid">
                    <div>
                        <label class="checkout-label">First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" class="checkout-input">
                    </div>
                    <div>
                        <label class="checkout-label">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" class="checkout-input">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label class="checkout-label">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="checkout-input">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label class="checkout-label">Street Address</label>
                    <textarea name="delivery_address" required class="checkout-textarea"><?php echo htmlspecialchars($delivery_address); ?></textarea>
                </div>

                <div class="checkout-grid">
                    <div>
                        <label class="checkout-label">City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" placeholder="New York" class="checkout-input">
                    </div>
                    <div>
                        <label class="checkout-label">Postal Code</label>
                        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>" placeholder="10001" class="checkout-input">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label class="checkout-label">Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="checkout-input">
                </div>

                <h3 style="font-family: 'Montserrat', sans-serif; font-size: 1rem; font-weight: 600; margin-bottom: 1.5rem;">Payment Method</h3>

                <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2.5rem;">
                    <label style="display: block; cursor: pointer; border: 1px solid #000; border-radius: 15px; padding: 1.5rem; background: #fafafa; transition: background 0.3s;" id="option-cod">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <input type="radio" name="payment_method" value="Cash on Delivery" checked style="margin-top: 0.3rem;">
                            <div style="text-align: center; flex: 1;">
                                <div style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #000;">Cash on Delivery (COD)</div>
                                <div style="font-size: 0.75rem; color: #888; margin-top: 0.4rem; line-height: 1.4;">PAY WITH CASH UPON DELIVERY.</div>
                            </div>
                        </div>
                    </label>
                    <label style="display: block; cursor: pointer; border: 1px solid #f0f0f0; border-radius: 15px; padding: 1.5rem; transition: background 0.3s;" id="option-stripe">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <input type="radio" name="payment_method" value="Card Payment" style="margin-top: 0.3rem;">
                            <div style="text-align: center; flex: 1;">
                                <div style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #000;">Secure Card Payment</div>
                                <div style="font-size: 0.75rem; color: #888; margin-top: 0.4rem; line-height: 1.4;">PAY SECURELY USING YOUR CREDIT OR DEBIT CARD.</div>
                                <div id="card-warning" style="display: none; font-size: 0.75rem; color: #c62828; margin-top: 0.8rem; background: #ffebee; padding: 0.5rem; border-radius: 8px;">
                                    ⚠️ Note: If you cancel your order after placing it, the payment will be returned within 15 working days.
                                </div>
                            </div>
                        </div>
                    </label>

                </div>

                <input type="hidden" name="total_amount" value="<?php echo $estimated_total; ?>">
            </form>
        </div>
    </div>

    <!-- Right Column: Order Summary -->
    <div class="checkout-right">
        <div class="checkout-card">
            <h3 style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem; font-weight: 600; margin-bottom: 2rem;">Order Summary</h3>

            <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
                <?php foreach ($cart as $item): ?>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div style="width: 70px; height: 70px; background: #fafafa; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #f5f5f5;">
                            <?php echo renderImage($item['image'], $item['product_name'], '', 'width: 100%; height: 100%; object-fit: contain;'); ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.9rem; font-weight: 500; color: #333; line-height: 1.3;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div style="font-size: 0.8rem; color: #999; margin-top: 0.2rem;">Qty: <?php echo $item['quantity']; ?> | 50ml</div>
                            <div style="font-size: 0.95rem; font-weight: 700; color: #000; margin-top: 0.3rem;"><?php echo formatPrice($item['price']); ?></div>
                        </div>
                    </div>
                <?php
endforeach; ?>
            </div>

            <div style="border-top: 1px dashed #eee; padding-top: 1.5rem; margin-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #888; margin-bottom: 0.8rem;">
                    <span style="letter-spacing: 0.5px;">Subtotal</span>
                    <span style="font-weight: 600; color: #333;"><?php echo formatPrice($subtotal); ?></span>
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
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #d32f2f; margin-bottom: 0.8rem; align-items: center;">
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span style="font-weight: 600;">Promotion (<?php echo $discount_percent; ?>% OFF)</span>
                        <a href="../actions/remove_offer.php" style="font-size: 0.75rem; color: #888; text-decoration: underline;">Remove offer</a>
                    </div>
                    <span style="font-weight: 600;">-<?php echo formatPrice($discount_amount); ?></span>
                </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #888; margin-bottom: 1.5rem;">
                    <span style="letter-spacing: 0.5px;">Shipping</span>
                    <span style="color: #4CAF50; font-weight: 700;">Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.3rem; color: #000;">
                    <span style="letter-spacing: 0.5px;">Total</span>
                    <span><?php echo formatPrice($estimated_total); ?></span>
                </div>
            </div>
            
            <button type="button" onclick="document.getElementById('checkoutForm').submit();" style="width: 100%; padding: 1.2rem; background: #1a1a1a; color: #fff; border: none; border-radius: 12px; cursor: pointer; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; font-weight: 600; letter-spacing: 1px; margin-top: 2rem; transition: transform 0.2s; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">CONFIRM ORDER</button>

        </div>
    </div>
</div>
<style>
.checkout-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    gap: 3rem;
    align-items: flex-start;
}
.checkout-left {
    flex: 1.6;
}
.checkout-right {
    flex: 1;
}
.checkout-card {
    background: #fff;
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.03);
    border: 1px solid #f0f0f0;
}
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.checkout-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.6rem;
}
.checkout-input {
    width: 100%;
    padding: 0.9rem;
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    background: #fafafa;
    font-size: 0.9rem;
}
.checkout-textarea {
    width: 100%;
    padding: 0.9rem;
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    background: #fafafa;
    font-size: 0.9rem;
    min-height: 100px;
    resize: none;
}
#checkoutForm input:focus, #checkoutForm textarea:focus { outline: none; border-color: #D4B483 !important; }
.payment-option-active { background: #fafafa; border-color: #000 !important; }

@media (max-width: 900px) {
    .checkout-container {
        flex-direction: column;
        gap: 1.5rem;
    }
    .checkout-left, .checkout-right {
        width: 100%;
        flex: auto;
    }
    .checkout-card {
        padding: 1.5rem;
    }
    .checkout-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('label[id^="option-"]').forEach(lbl => {
            lbl.style.borderColor = '#f0f0f0';
            lbl.style.background = '#fff';
        });
        if(this.checked) {
            this.closest('label').style.borderColor = '#000';
            this.closest('label').style.background = '#fafafa';
        }
        
        const warning = document.getElementById('card-warning');
        if (warning) {
            warning.style.display = (this.value === 'Card Payment') ? 'block' : 'none';
        }
        
        // Change button text based on selection
        const btn = document.querySelector('button[onclick*="checkoutForm"]');
        if(this.value === 'Card Payment') {
            btn.innerText = 'PROCEED TO PAYMENT';
        } else {
            btn.innerText = 'CONFIRM ORDER';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
