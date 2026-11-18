<?php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Load user data for sidebar
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Split name for display
$name_parts = explode(' ', $user['full_name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';

// Get initials for avatar
$initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
if (empty($initials))
    $initials = '?';

// Fetch Wishlist Items
$stmt_wish = $conn->prepare("SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.product_id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$stmt_wish->bind_param("i", $user_id);
$stmt_wish->execute();
$wishlist = $stmt_wish->get_result();
?>

<link rel="stylesheet" href="../assets/css/customer.css">

<div class="account-wrapper">
    <!-- Main Content -->
    <div class="account-main">
        <div class="account-page-header">
            <h2>My Wishlist</h2>
            <p>View and manage the products you've saved</p>
        </div>

        <?php if ($wishlist && $wishlist->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($row = $wishlist->fetch_assoc()): ?>
                    <div class="product-card" style="position: relative;">
                        <!-- Remove Button -->
                        <a href="../actions/toggle_wishlist.php?id=<?php echo $row['product_id']; ?>&redirect=wishlist" 
                           style="position: absolute; top: 1rem; right: 1rem; background: #fff; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff4757; z-index: 10; border: 1px solid #ddd; font-weight: bold; text-decoration: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                           title="Remove from Wishlist">
                           &times;
                        </a>
                        
                        <a href="../pages/product.php?id=<?php echo $row['product_id']; ?>" class="product-image">
                            <?php echo renderImage($row['main_image'], $row['product_name']); ?>
                        </a>
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($row['brand'] ?? ''); ?></div>
                            <h4 class="product-title">
                                <a href="../pages/product.php?id=<?php echo $row['product_id']; ?>"><?php echo htmlspecialchars($row['product_name']); ?></a>
                            </h4>
                            <div class="product-price"><?php echo formatPrice($row['price']); ?></div>
                            <div class="product-actions">
                                <a href="../actions/add_to_cart.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php
    endwhile; ?>
            </div>
        <?php
else: ?>
            <div style="text-align: center; padding: 4rem 0; background: #f9f9f9; border-radius: 8px;">
                <div class="mb-2" style="font-size: 3rem; color: #ddd;">❤️</div>
                <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">Your wishlist is currently empty.</p>
                <a href="../pages/shop.php" class="btn btn-primary" style="width: auto;">Discover Fragrances</a>
            </div>
        <?php
endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
