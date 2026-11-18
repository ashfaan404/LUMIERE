<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// Fetch Featured / Best Sellers (with ratings)
$stmt = $conn->prepare("SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.review_id) as total_reviews FROM products p LEFT JOIN reviews r ON p.product_id = r.product_id GROUP BY p.product_id ORDER BY stock ASC LIMIT 4");
$stmt->execute();
$featured_products = $stmt->get_result();

// Fetch New Arrivals (with ratings)
$stmt = $conn->prepare("SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.review_id) as total_reviews FROM products p LEFT JOIN reviews r ON p.product_id = r.product_id GROUP BY p.product_id ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$new_arrivals = $stmt->get_result();

// Fetch Categories
$cat_stmt = $conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
$cat_stmt->execute();
$categories_res = $cat_stmt->get_result();
?>

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="hero-content">
        <h2>Discover Your Signature Scent</h2>
        <p>Premium fragrances for every occasion</p>
        <a href="shop.php" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<!-- Featured Products -->
<section class="mb-2">
    <h3 class="section-title">Best Sellers</h3>
    <div class="product-grid">
        <?php while($row = $featured_products->fetch_assoc()): ?>
            <div class="product-card">
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

<!-- New Arrivals -->
<section class="mb-2 mt-2">
    <h3 class="section-title">New Arrivals</h3>
    <div class="product-grid">
        <?php while($row = $new_arrivals->fetch_assoc()): ?>
            <div class="product-card">
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

<!-- Categories Section -->
<section class="mb-2 mt-2 text-center" style="padding: 2rem 0;">
    <h3 class="section-title">Shop by Category</h3>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php while($cat = $categories_res->fetch_assoc()): ?>
            <a href="shop.php?category=<?php echo $cat['category_id']; ?>" class="btn btn-secondary" style="width: auto;">
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
