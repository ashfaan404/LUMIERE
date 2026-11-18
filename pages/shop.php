<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// Build Query
$query = "SELECT p.*, c.category_name, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.review_id) as total_reviews FROM products p JOIN categories c ON p.category_id = c.category_id LEFT JOIN reviews r ON p.product_id = r.product_id WHERE 1=1";
$params = [];
$types = "";

// Filters
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = "%" . trim($_GET['search']) . "%";
    $query .= " AND (p.product_name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $query .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
    $types .= "i";
}

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $query .= " AND p.brand = ?";
    $params[] = $_GET['brand'];
    $types .= "s";
}

if (isset($_GET['min_price']) && is_numeric($_GET['min_price']) && $_GET['min_price'] >= 0) {
    $query .= " AND p.price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] > 0) {
    $query .= " AND p.price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

if (isset($_GET['in_stock']) && $_GET['in_stock'] == '1') {
    $query .= " AND p.stock > 0";
}

$query .= " GROUP BY p.product_id";

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
switch($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY p.product_name ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.product_name DESC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get Categories for filter
$cat_stmt = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

// Get Brands for filter
$brand_stmt = $conn->query("SELECT DISTINCT brand FROM products ORDER BY brand ASC");
?>

<div class="shop-layout mt-2">
    <!-- Sidebar Filters -->
    <aside class="shop-sidebar">
        <div class="filter-group">
            <h4>Filters</h4>
            <form action="shop.php" method="GET">
                <!-- Preserve Search if set -->
                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php while($c = $cat_stmt->fetch_assoc()): ?>
                            <option value="<?php echo $c['category_id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $c['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Brand</label>
                    <select name="brand">
                        <option value="">All Brands</option>
                        <?php while($b = $brand_stmt->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($b['brand']); ?>" <?php echo (isset($_GET['brand']) && $_GET['brand'] == $b['brand']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($b['brand']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group flex justify-between gap-1">
                    <div style="flex: 1;">
                        <label>Min Rs.</label>
                        <input type="number" name="min_price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>Max Rs.</label>
                        <input type="number" name="max_price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group mt-1">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="in_stock" value="1" <?php echo (isset($_GET['in_stock']) && $_GET['in_stock'] == '1') ? 'checked' : ''; ?>>
                        In Stock Only
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Sort By</label>
                    <select name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name: Z-A</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary mt-1">Apply Filters</button>
            </form>
            <div class="mt-1 text-center">
                <a href="shop.php" style="color:var(--primary-color); font-size: 0.9rem; font-weight: 500;">Clear Filters</a>
            </div>
        </div>
    </aside>

    <!-- Main Product Grid -->
    <div class="shop-main">
        <h2 class="mb-1" style="font-family: var(--font-heading);">
            <?php 
                if(isset($_GET['search']) && !empty($_GET['search'])) echo 'Search Results for "' . htmlspecialchars($_GET['search']) . '"';
                else echo "All Perfumes";
            ?>
        </h2>
        <p class="mb-2" style="color:var(--light-text);">Showing <?php echo $products->num_rows; ?> products</p>
        
        <?php if($products->num_rows > 0): ?>
            <div class="product-grid">
                <?php while($row = $products->fetch_assoc()): ?>
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
        <?php else: ?>
            <div class="alert alert-error">No products found matching your criteria. Try adjusting your filters.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
