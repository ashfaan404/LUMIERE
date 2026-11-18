<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/header.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    redirect('shop.php');
}

if (!hasPurchasedProduct($conn, $user_id, $product_id)) {
    $_SESSION['error_message'] = "You can only review products that you have received.";
    redirect('product.php?id=' . $product_id);
}

// Check if already reviewed
$stmt_rev = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ?");
$stmt_rev->bind_param("ii", $user_id, $product_id);
$stmt_rev->execute();
if ($stmt_rev->get_result()->num_rows > 0) {
    $_SESSION['error_message'] = "You have already reviewed this product.";
    redirect('product.php?id=' . $product_id);
}

// Get product details
$stmt_p = $conn->prepare("SELECT product_name, main_image FROM products WHERE product_id = ?");
$stmt_p->bind_param("i", $product_id);
$stmt_p->execute();
$product = $stmt_p->get_result()->fetch_assoc();

if (!$product) {
    redirect('shop.php');
}

?>

<div class="form-container" style="max-width: 600px; margin: 3rem auto;">
    <h2 class="section-title text-center">Write a Review</h2>
    
    <div style="text-align: center; margin-bottom: 2rem;">
        <img src="<?php echo htmlspecialchars($product['main_image']); ?>" style="width: 100px; height: 100px; object-fit: contain;">
        <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
    </div>

    <form action="../actions/submit_review.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <div class="form-group">
            <label>Rating (1-5)</label>
            <select name="rating" required>
                <option value="5">5 - Excellent (★★★★★)</option>
                <option value="4">4 - Very Good (★★★★☆)</option>
                <option value="3">3 - Average (★★★☆☆)</option>
                <option value="2">2 - Poor (★★☆☆☆)</option>
                <option value="1">1 - Terrible (★☆☆☆☆)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Your Review</label>
            <textarea name="review_text" rows="5" required placeholder="What did you think about this fragrance?"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Review</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
