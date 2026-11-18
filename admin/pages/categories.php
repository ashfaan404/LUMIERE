<?php
// admin/pages/categories.php
require_once '../includes/admin_header.php';

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) as p_count FROM categories c ORDER BY c.category_name ASC");
?>

<?php
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_cat = null;
if ($edit_id > 0) {
    $res = $conn->query("SELECT * FROM categories WHERE category_id = $edit_id");
    $edit_cat = $res->fetch_assoc();
}
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Add/Edit Category Form -->
    <div class="card">
        <div class="card-header">
            <h3><?php echo $edit_cat ? 'Edit Category' : 'Add New Category'; ?></h3>
        </div>
        <div style="padding: 2rem;">
            <form action="../actions/<?php echo $edit_cat ? 'edit_category_action.php' : 'add_category_action.php'; ?>" method="POST">
                <?php if ($edit_cat): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_cat['category_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Category Name</label>
                    <input type="text" name="category_name" value="<?php echo $edit_cat ? htmlspecialchars($edit_cat['category_name']) : ''; ?>" placeholder="e.g. Woody, Floral" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?php echo $edit_cat ? 'Update Category' : 'Create Category'; ?>
                    </button>
                    <?php if ($edit_cat): ?>
                        <a href="categories.php" class="btn btn-secondary" style="background: #f1f3f4; color: #555;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Table -->
    <div class="card">
        <div class="card-header">
            <h3>Existing Categories</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($c = $categories->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $c['category_id']; ?></td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($c['category_name']); ?></div>
                        </td>
                        <td>
                            <span class="badge <?php echo $c['p_count'] > 0 ? 'badge-info' : 'badge-pending'; ?>">
                                <?php echo $c['p_count']; ?> Products
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <a href="?edit=<?php echo $c['category_id']; ?>" style="color: var(--admin-navy); text-decoration: none; font-size: 0.85rem; font-weight: 600;">Edit</a>
                                
                                <?php if($c['p_count'] == 0): ?>
                                    <a href="../actions/delete_category.php?id=<?php echo $c['category_id']; ?>" class="btn-delete" style="color: #d32f2f; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Delete</a>
                                <?php else: ?>
                                    <span title="Cannot delete categories with products" style="font-size: 0.75rem; color: #ccc; cursor: not-allowed;">Delete</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
