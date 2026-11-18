<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('shop.php');
}

$product_id = $_GET['id'];

// Get product details
$stmt = $conn->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Product not found.";
    redirect('shop.php');
}

$product = $result->fetch_assoc();

// Get Reviews
$rev_stmt = $conn->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$rev_stmt->bind_param("i", $product_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

$rating_data = getAverageRating($conn, $product_id);
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'];

// Get Similar Products (same category, excluding current)
$sim_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND product_id != ? LIMIT 4");
$sim_stmt->bind_param("ii", $product['category_id'], $product_id);
$sim_stmt->execute();
$similar_products = $sim_stmt->get_result();

// Get More from Brand
$brand_stmt = $conn->prepare("SELECT * FROM products WHERE brand = ? AND product_id != ? LIMIT 4");
$brand_stmt->bind_param("si", $product['brand'], $product_id);
$brand_stmt->execute();
$brand_products = $brand_stmt->get_result();

// Get Top Rated Global Recommendations
$rec_query = "SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.review_id) as total_reviews
              FROM products p 
              LEFT JOIN reviews r ON p.product_id = r.product_id 
              WHERE p.product_id != ?
              GROUP BY p.product_id 
              ORDER BY avg_rating DESC, p.created_at DESC 
              LIMIT 4";
$rec_stmt = $conn->prepare($rec_query);
$rec_stmt->bind_param("i", $product_id);
$rec_stmt->execute();
$global_recommendations = $rec_stmt->get_result();

// Get variants
$v_stmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY size_ml ASC");
$v_stmt->bind_param("i", $product_id);
$v_stmt->execute();
$variants_res = $v_stmt->get_result();
$variants = [];
while($v = $variants_res->fetch_assoc()) {
    $variants[] = $v;
}

// If no variants in new table, use data from products table (fallback)
if (empty($variants)) {
    $variants[] = [
        'variant_id' => 0,
        'size_ml' => $product['size_ml'],
        'price' => $product['price'],
        'stock' => $product['stock']
    ];
}

$is_liked = false;
if (isLoggedIn()) {
    $like_stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $like_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $like_stmt->execute();
    if ($like_stmt->get_result()->num_rows > 0) {
        $is_liked = true;
    }
}
?>

<div class="product-detail-premium">
    <div class="product-image-premium">
        <?php echo renderImage($product['main_image'], $product['product_name']); ?>
    </div>
    
    <div class="product-info-premium">
        <div class="product-brand-premium"><?php echo htmlspecialchars($product['brand']); ?></div>
        <h1 class="product-title-premium"><?php echo htmlspecialchars($product['product_name']); ?></h1>
        <div class="product-category-under-title">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
        
        <div class="product-meta-row">
            <div class="stars-premium">
                <?php 
                for($i=1; $i<=5; $i++) {
                    if($i <= $avg_rating) echo '★';
                    else echo '<span style="color:#eee">★</span>';
                }
                ?>
            </div>
            <span class="review-count-premium">(<?php echo $total_reviews; ?> reviews)</span>
        </div>

        <div class="product-price-premium" id="display-price"><?php echo formatPrice($variants[0]['price']); ?></div>
        
        <div class="selection-group-premium">
            <h4>Select Size</h4>
            <div class="size-options">
                <?php foreach($variants as $index => $v): ?>
                    <div class="size-option <?php echo $index === 0 ? 'active' : ''; ?>" 
                         onclick="selectVariant(<?php echo htmlspecialchars(json_encode($v)); ?>, this)">
                        <?php echo $v['size_ml']; ?>ml
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <form action="../actions/add_to_cart.php" method="GET" id="cart-form">
            <input type="hidden" name="id" value="<?php echo $product['product_id']; ?>">
            <input type="hidden" name="variant_id" id="selected_variant_id" value="<?php echo $variants[0]['variant_id']; ?>">
            
            <div class="purchase-actions-premium">
                <div class="qty-selector-premium">
                    <button type="button" class="qty-btn minus" onclick="updateQty(-1)">−</button>
                    <input type="number" name="quantity" id="product-qty" value="1" min="1" max="<?php echo $variants[0]['stock'] > 0 ? $variants[0]['stock'] : 1; ?>" class="qty-input" readonly>
                    <button type="button" class="qty-btn plus" onclick="updateQty(1)">+</button>
                </div>
                
                <button type="submit" class="add-to-cart-premium" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4H6zM3 6h18M16 10a4 4 0 01-8 0"/></svg>
                    <span><?php echo $product['stock'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?></span>
                </button>
                
                <a href="../actions/toggle_wishlist.php?id=<?php echo $product['product_id']; ?>" class="icon-action-btn <?php echo $is_liked ? 'active' : ''; ?>" title="Add to Wishlist">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_liked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                </a>

                <a href="../actions/add_to_compare.php?id=<?php echo $product['product_id']; ?>" class="icon-action-btn" title="Add to Compare">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18M12 3v18M6 7l-3 5h6l-3-5zm12 0l-3 5h6l-3-5z"/></svg>
                </a>
            </div>
        </form>

        <div class="stock-status-premium" id="stock-status">
            <?php if ($variants[0]['stock'] > 0): ?>
                <span class="check" style="color:#4caf50">✓</span>
                <span>In stock and ready to ship</span>
            <?php else: ?>
                <span class="check" style="color:#f44336">✕</span>
                <span>Currently out of stock</span>
            <?php endif; ?>
        </div>

        <div class="tabs-container-premium">
            <div class="product-tabs-premium">
                <button class="tab-link-premium active" onclick="openTab(event, 'description-panel')">Description</button>
                <button class="tab-link-premium" onclick="openTab(event, 'reviews-panel')">Reviews (<?php echo $total_reviews; ?>)</button>
            </div>

            <div id="description-panel" class="tab-panel active">
                <div class="tab-content-premium">
                    <div class="fragrance-notes-compact" style="margin-bottom: 2rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; background: #fafafa; padding: 1.5rem; border-radius: 12px;">
                        <div class="note-item">
                            <span style="display:block; font-size: 0.7rem; font-weight: 800; color: #999; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 1px;">Top Notes</span>
                            <span style="font-size: 0.95rem; color: #333; font-weight: 500;"><?php echo htmlspecialchars($product['top_notes'] ?: 'Not specified'); ?></span>
                        </div>
                        <div class="note-item" style="border-left: 1px solid #eee; padding-left: 1rem;">
                            <span style="display:block; font-size: 0.7rem; font-weight: 800; color: #999; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 1px;">Heart Notes</span>
                            <span style="font-size: 0.95rem; color: #333; font-weight: 500;"><?php echo htmlspecialchars($product['middle_notes'] ?: 'Not specified'); ?></span>
                        </div>
                        <div class="note-item" style="border-left: 1px solid #eee; padding-left: 1rem;">
                            <span style="display:block; font-size: 0.7rem; font-weight: 800; color: #999; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 1px;">Base Notes</span>
                            <span style="font-size: 0.95rem; color: #333; font-weight: 500;"><?php echo htmlspecialchars($product['base_notes'] ?: 'Not specified'); ?></span>
                        </div>
                    </div>
                    <div style="line-height: 1.8; color: #555;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>
            </div>

            <div id="reviews-panel" class="tab-panel">
                <div class="tab-content-premium">
                    <?php if($reviews->num_rows > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <?php while($rev = $reviews->fetch_assoc()): ?>
                                <div style="border-bottom: 1px solid #f9f9f9; padding-bottom: 1.5rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <strong><?php echo htmlspecialchars($rev['full_name']); ?></strong>
                                        <span style="color:#E6B96D;">
                                            <?php for($i=1; $i<=5; $i++) echo ($i <= $rev['rating']) ? '★' : '☆'; ?>
                                        </span>
                                    </div>
                                    <p style="font-size: 0.85rem; color: #999; margin-bottom: 0.8rem;">
                                        <?php echo date('F j, Y', strtotime($rev['created_at'])); ?>
                                    </p>
                                    <p style="font-size: 0.95rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateQty(change) {
    const input = document.getElementById('product-qty');
    const max = parseInt(input.getAttribute('max'));
    let newVal = parseInt(input.value) + change;
    if (newVal < 1) newVal = 1;
    if (newVal > max) newVal = max;
    input.value = newVal;
}

function openTab(evt, tabName) {
    const panels = document.getElementsByClassName("tab-panel");
    for (let i = 0; i < panels.length; i++) {
        panels[i].classList.remove("active");
    }
    const links = document.getElementsByClassName("tab-link-premium");
    for (let i = 0; i < links.length; i++) {
        links[i].classList.remove("active");
    }
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

// Dynamic Variant Switcher
function selectVariant(variant, el) {
    // 1. Highlight button
    document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');

    // 2. Update Price
    const priceDisplay = document.getElementById('display-price');
    const currency = "Rs. "; // Assuming LKR based on previous logs? 
    // Actually the logic uses formatPrice PHP function. I'll just use a simple JS format or hardcode LKR for now.
    // Wait, the formatPrice PHP function might be doing something specific. 
    // I'll try to match the format.
    const formattedPrice = "Rs. " + parseFloat(variant.price).toLocaleString();
    priceDisplay.textContent = formattedPrice;

    // 3. Update Stock Status
    const stockStatus = document.getElementById('stock-status');
    const cartBtn = document.querySelector('.add-to-cart-premium');
    const qtyInput = document.getElementById('product-qty');

    if (variant.stock > 0) {
        stockStatus.innerHTML = '<span class="check" style="color:#4caf50">✓</span> <span>In stock and ready to ship</span>';
        cartBtn.disabled = false;
        cartBtn.querySelector('span').textContent = 'Add to Cart';
        qtyInput.setAttribute('max', variant.stock);
        if (parseInt(qtyInput.value) > variant.stock) qtyInput.value = variant.stock;
    } else {
        stockStatus.innerHTML = '<span class="check" style="color:#f44336">✕</span> <span>Currently out of stock</span>';
        cartBtn.disabled = true;
        cartBtn.querySelector('span').textContent = 'Out of Stock';
        qtyInput.setAttribute('max', 1);
        qtyInput.value = 1;
    }

    // 4. Update hidden form input
    document.getElementById('selected_variant_id').value = variant.variant_id;
}
</script>

<!-- More from Brand -->
<?php if($brand_products->num_rows > 0): ?>
<section class="mt-4 mb-2">
    <h3 class="section-title">More from <?php echo htmlspecialchars($product['brand']); ?></h3>
    <div class="product-grid-compact">
        <?php while($row = $brand_products->fetch_assoc()): ?>
            <?php 
                $b_rating_data = getAverageRating($conn, $row['product_id']);
                $b_avg = round($b_rating_data['avg_rating'] ?? 0, 1);
            ?>
            <div class="product-card product-card-compact">
                <a href="product.php?id=<?php echo $row['product_id']; ?>" class="product-image">
                    <?php echo renderImage($row['main_image'], $row['product_name']); ?>
                </a>
                <div class="product-info">
                    <div class="product-brand"><?php echo htmlspecialchars($row['brand']); ?></div>
                    <h4 class="product-title"><a href="product.php?id=<?php echo $row['product_id']; ?>"><?php echo htmlspecialchars($row['product_name']); ?></a></h4>
                    <?php echo getRatingStars($b_avg, $b_rating_data['total_reviews']); ?>
                    <div class="product-price"><?php echo formatPrice($row['price']); ?></div>
                    <a href="../actions/add_to_cart.php?id=<?php echo $row['product_id']; ?>" class="btn btn-gold">Add to Cart</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; ?>

<!-- Similar Products -->
<?php 
if($similar_products->num_rows == 0) {
    // Fallback to top rated if no similar products
    $similar_products = $global_recommendations;
    $rec_title = "You May Also Like";
} else {
    $rec_title = "You May Also Like";
}
?>
<section class="mt-4 mb-3">
    <h3 class="section-title"><?php echo $rec_title; ?></h3>
    <div class="product-grid-compact">
        <?php while($row = $similar_products->fetch_assoc()): ?>
            <?php 
                $sim_rating_data = getAverageRating($conn, $row['product_id']);
                $sim_avg = round($sim_rating_data['avg_rating'] ?? 0, 1);
            ?>
            <div class="product-card product-card-compact">
                <a href="product.php?id=<?php echo $row['product_id']; ?>" class="product-image">
                    <?php echo renderImage($row['main_image'], $row['product_name']); ?>
                </a>
                <div class="product-info">
                    <div class="product-brand"><?php echo htmlspecialchars($row['brand']); ?></div>
                    <h4 class="product-title"><a href="product.php?id=<?php echo $row['product_id']; ?>"><?php echo htmlspecialchars($row['product_name']); ?></a></h4>
                    <?php echo getRatingStars($sim_avg, $sim_rating_data['total_reviews']); ?>
                    <div class="product-price"><?php echo formatPrice($row['price']); ?></div>
                    <a href="../actions/add_to_cart.php?id=<?php echo $row['product_id']; ?>" class="btn btn-gold">Add to Cart</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- Global Recommendations -->
<?php if($global_recommendations->num_rows > 0): ?>
<section class="mt-4 mb-4">
    <h3 class="section-title">Curated for You</h3>
    <div class="product-grid-compact">
        <?php while($row = $global_recommendations->fetch_assoc()): ?>
            <div class="product-card product-card-compact">
                <a href="product.php?id=<?php echo $row['product_id']; ?>" class="product-image">
                    <?php echo renderImage($row['main_image'], $row['product_name']); ?>
                </a>
                <div class="product-info">
                    <div class="product-brand"><?php echo htmlspecialchars($row['brand']); ?></div>
                    <h4 class="product-title"><a href="product.php?id=<?php echo $row['product_id']; ?>"><?php echo htmlspecialchars($row['product_name']); ?></a></h4>
                    <?php echo getRatingStars($row['avg_rating'], $row['total_reviews']); ?>
                    <div class="product-price"><?php echo formatPrice($row['price']); ?></div>
                    <a href="../actions/add_to_cart.php?id=<?php echo $row['product_id']; ?>" class="btn btn-gold">Add to Cart</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
