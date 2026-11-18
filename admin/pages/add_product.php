<?php
// admin/pages/add_product.php
require_once '../includes/admin_header.php';

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h3>Add New Perfume</h3>
        <a href="products.php" style="font-size: 0.9rem; color: var(--admin-navy); text-decoration: none;">← Back to List</a>
    </div>
    <div style="padding: 2.5rem;">
        <form action="../actions/add_product_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Left Side -->
                <div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Product Name</label>
                        <input type="text" name="product_name" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Brand</label>
                        <input type="text" name="brand" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Category</label>
                        <select name="category_id" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                            <option value="">Select Category</option>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div style="background:#f9f9f9; padding:1.2rem; border-radius:10px; border:1px solid #eee; margin-bottom:1.5rem;">
                        <h4 style="font-size:0.7rem; color:#888; text-transform:uppercase; margin-bottom:1.2rem; border-bottom:1px solid #eee; padding-bottom:0.5rem; letter-spacing:1px;">Size Variants (ML, Price, Stock)</h4>
                        <div id="variant-rows">
                            <div class="variant-row" style="display:grid; grid-template-columns: 1fr 1.5fr 1fr; gap:0.5rem; margin-bottom:0.8rem;">
                                <input type="number" name="variants[0][size]" placeholder="ML" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem;">
                                <input type="number" step="0.01" name="variants[0][price]" placeholder="Price ($)" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem;">
                                <input type="number" name="variants[0][stock]" placeholder="Stock" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem;">
                            </div>
                        </div>
                        <button type="button" onclick="addVariant()" style="background:#444; color:#fff; border:none; padding:0.5rem 1rem; border-radius:6px; cursor:pointer; font-size:0.75rem; font-weight:600; transition: 0.2s;">+ Add Another Size</button>
                    </div>
                </div>

                <!-- Right Side -->
                <div>
                    <!-- Single Size replaced by variants above -->
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Top Notes</label>
                        <input type="text" name="top_notes" placeholder="e.g. Citrus, Bergamot" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Heart Notes</label>
                        <input type="text" name="middle_notes" placeholder="e.g. Jasmine, Rose" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Base Notes</label>
                        <input type="text" name="base_notes" placeholder="e.g. Musk, Amber" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Description</label>
                        <textarea name="description" rows="4" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Product Image</label>
                <input type="file" name="product_img" accept="image/*" required style="font-size:0.9rem;">
            </div>

            <div style="margin-top: 2.5rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Create Product</button>
            </div>
        </form>
    </div>
</div>

<script>
let variantCount = 1;
function addVariant() {
    const container = document.getElementById('variant-rows');
    const div = document.createElement('div');
    div.className = 'variant-row';
    div.style.display = 'grid';
    div.style.gridTemplateColumns = '1fr 1.5fr 1fr 0.3fr';
    div.style.gap = '0.5rem';
    div.style.marginBottom = '0.8rem';
    
    div.innerHTML = `
        <input type="number" name="variants[${variantCount}][size]" placeholder="ML" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem;">
        <input type="number" step="0.01" name="variants[${variantCount}][price]" placeholder="Price ($)" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem;">
        <input type="number" name="variants[${variantCount}][stock]" placeholder="Stock" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem;">
        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ff4d4d; cursor:pointer; font-size:1.4rem; padding:0; line-height:1;">&times;</button>
    `;
    container.appendChild(div);
    variantCount++;
}
</script>

<?php require_once '../includes/admin_footer.php'; ?>
