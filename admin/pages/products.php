<?php
// admin/pages/products.php
require_once '../includes/admin_header.php';

// Fetch products with category names
$query = "SELECT p.*, c.category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.category_id 
          ORDER BY p.created_at DESC";
$products = $conn->query($query);
?>

<div class="card">
    <div class="card-header">
        <h3>Product Inventory</h3>
        <a href="add_product.php" class="btn btn-primary">+ Add New Product</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="<?php echo strpos($p['main_image'], 'http') === 0 ? $p['main_image'] : '../../' . $p['main_image']; ?>" 
                             alt="Product" width="50" height="50" style="object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($p['product_name']); ?></div>
                        <div style="font-size: 0.75rem; color: #888;"><?php echo $p['size_ml']; ?>ml</div>
                    </td>
                    <td><?php echo htmlspecialchars($p['brand']); ?></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($p['category_name']); ?></span></td>
                    <td style="font-weight: 700;"><?php echo formatPrice($p['price']); ?></td>
                    <td>
                        <span style="font-weight: 600; color: <?php echo $p['stock'] < 10 ? '#d32f2f' : '#2e7d32'; ?>;">
                            <?php echo $p['stock']; ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm" style="background:#f0f0f0; color:#333;">Edit</a>
                            <a href="../actions/delete_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-delete" style="background:#fff5f5; color:#d32f2f; border:1px solid #ffebeb;">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
