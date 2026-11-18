<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$compare_items = isset($_SESSION['compare']) ? $_SESSION['compare'] : [];
$products = [];

if (!empty($compare_items)) {
    // Sanitize array for query
    $ids = implode(',', array_map('intval', $compare_items));
    $query = "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id IN ($ids)";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $pid = $row['product_id'];
        $v_res = $conn->query("SELECT * FROM product_variants WHERE product_id = $pid ORDER BY size_ml ASC");
        $row['variants'] = [];
        while ($v = $v_res->fetch_assoc()) {
            $row['variants'][] = $v;
        }
        $products[] = $row;
    }
}
?>

<div class="container mt-5">
    <div class="mb-4 text-center">
        <h2 class="section-title">Compare Fragrances</h2>
        <p style="color:var(--light-text); font-size: 0.9rem;">Side-by-side comparison of your favorite perfumes.</p>
    </div>

    <div class="compare-table-wrapper">
        <table class="compare-table">
            <thead>
                <tr class="product-header">
                    <th class="compare-attr-label">Product</th>
                    <?php
for ($i = 0; $i < 5; $i++):
    if (isset($products[$i])):
        $p = $products[$i];
?>
                        <td style="position: relative;">
                            <a href="../actions/remove_from_compare.php?id=<?php echo $p['product_id']; ?>" class="remove-compare-btn" title="Remove">&times;</a>
                            <div class="compare-product-img">
                                <?php echo renderImage($p['main_image'], $p['product_name']); ?>
                            </div>
                            <div class="compare-product-name"><?php echo htmlspecialchars($p['product_name']); ?></div>
                            <div class="compare-product-brand"><?php echo htmlspecialchars($p['brand']); ?></div>
                        </td>
                    <?php
    else: ?>
                        <td>
                            <a href="shop.php" class="placeholder-add">
                                <span class="plus">+</span>
                                <span class="text">Add Product</span>
                            </a>
                        </td>
                    <?php
    endif;
endfor; ?>
                </tr>
            </thead>
            <tbody>
                <!-- Price Row -->
                <tr>
                    <th class="compare-attr-label">Price</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td>
                            <?php if (isset($products[$i])):
        $p = $products[$i];
        if (!empty($p['variants'])) {
            $min_price = min(array_column($p['variants'], 'price'));
            echo '<div class="compare-price">From ' . formatPrice($min_price) . '</div>';
        }
        else {
            echo '<div class="compare-price">' . formatPrice($p['price']) . '</div>';
        }
    endif; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>
                
                <!-- Rating Row -->
                <tr>
                    <th class="compare-attr-label">Rating</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td>
                            <?php if (isset($products[$i])):
        $r_data = getAverageRating($conn, $products[$i]['product_id']);
        $avg = round($r_data['avg_rating'] ?? 0, 1);
?>
                                <div class="compare-stars">
                                    <?php for ($s = 1; $s <= 5; $s++)
            echo($s <= $avg) ? '★' : '<span style="color:#eee">★</span>'; ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #999; margin-top: 4px;">(<?php echo $r_data['total_reviews']; ?> reviews)</div>
                            <?php
    endif; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Category Row -->
                <tr>
                    <th class="compare-attr-label">Category</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td class="compare-value">
                            <?php echo isset($products[$i]) ? htmlspecialchars($products[$i]['category_name']) : ''; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Size Row -->
                <tr>
                    <th class="compare-attr-label">Sizes Available</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td class="compare-value">
                            <?php if (isset($products[$i])):
        $p = $products[$i];
        if (!empty($p['variants'])) {
            $size_list = array_map(function ($v) {
                return $v['size_ml'] . 'ml'; }, $p['variants']);
            echo implode(', ', $size_list);
        }
        else {
            echo htmlspecialchars($p['size_ml']) . 'ml';
        }
    endif; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Top Notes -->
                <tr>
                    <th class="compare-attr-label">Top Notes</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td class="compare-value">
                            <?php echo isset($products[$i]) ? htmlspecialchars($products[$i]['top_notes'] ?: 'Not specified') : ''; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Heart Notes -->
                <tr>
                    <th class="compare-attr-label">Heart Notes</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td class="compare-value">
                            <?php echo isset($products[$i]) ? htmlspecialchars($products[$i]['middle_notes'] ?: 'Not specified') : ''; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Base Notes -->
                <tr>
                    <th class="compare-attr-label">Base Notes</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td class="compare-value">
                            <?php echo isset($products[$i]) ? htmlspecialchars($products[$i]['base_notes'] ?: 'Not specified') : ''; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Availability -->
                <tr>
                    <th class="compare-attr-label">Availability</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td>
                            <?php if (isset($products[$i])): ?>
                                <?php if ($products[$i]['stock'] > 0): ?>
                                    <span class="compare-availability-badge">In Stock</span>
                                <?php
        else: ?>
                                    <span class="compare-availability-badge out-of-stock">Out of Stock</span>
                                <?php
        endif; ?>
                            <?php
    endif; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>

                <!-- Actions Row -->
                <tr>
                    <th class="compare-attr-label">Actions</th>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <td>
                            <?php if (isset($products[$i])):
        $p = $products[$i];
?>
                                <div class="compare-footer-btns">
                                    <a href="../actions/add_to_cart.php?id=<?php echo $p['product_id']; ?>" class="btn-compare-action btn-compare-cart" <?php echo $p['stock'] == 0 ? 'style="pointer-events:none; opacity:0.5;"' : ''; ?>>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4H6zM3 6h18M16 10a4 4 0 01-8 0"/></svg>
                                        Add to Cart
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="../actions/toggle_wishlist.php?id=<?php echo $p['product_id']; ?>" class="btn-compare-action btn-compare-wishlist">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                                            Wishlist
                                        </a>
                                    <?php
        endif; ?>
                                </div>
                            <?php
    endif; ?>
                        </td>
                    <?php
endfor; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
