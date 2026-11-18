<?php
// admin/pages/edit_product.php
require_once '../includes/admin_header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Fetch variants
$variants_res = $conn->query("SELECT * FROM product_variants WHERE product_id = $id ORDER BY size_ml ASC");
$variants = [];
while($v = $variants_res->fetch_assoc()) {
    $variants[] = $v;
}

if (!$product) {
    echo "Product not found.";
    require_once '../includes/admin_footer.php';
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
?>

<div style="max-width: 1000px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="background: #fff; padding: 2rem 2.5rem; border-bottom: 2px solid var(--admin-bg);">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h3 style="margin:0; font-size: 1.4rem; color: var(--admin-navy);">Edit Perfume Details</h3>
                    <p style="color: #888; margin-top: 5px;">Update inventory and product specifications.</p>
                </div>
                <a href="products.php" class="btn btn-secondary" style="background: #f8f9fa; color: #555; padding: 0.6rem 1.2rem;">Back to Catalog</a>
            </div>
        </div>
        
        <div style="padding: 3rem;">
            <form action="../actions/edit_product_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 3rem;">
                    <!-- Basic Information -->
                    <div>
                        <div class="form-group" style="margin-bottom: 1.8rem;">
                            <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Product Title</label>
                            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required style="width:100%; padding:0.9rem; border:1px solid #ddd; border-radius:8px; font-size: 1.1rem; font-weight: 600;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.8rem;">
                            <div class="form-group">
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Brand / House</label>
                                <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required style="width:100%; padding:0.9rem; border:1px solid #ddd; border-radius:8px;">
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Family / Category</label>
                                <select name="category_id" required style="width:100%; padding:0.9rem; border:1px solid #ddd; border-radius:8px; background:#fff;">
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $product['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div style="background:#f9f9f9; padding:1.5rem; border-radius:12px; border:1px solid #eee; margin-bottom:1.8rem;">
                            <h4 style="font-size:0.75rem; color:#999; text-transform:uppercase; margin-bottom:1.2rem; border-bottom:1px solid #eee; padding-bottom:0.8rem; letter-spacing:1px;">Size & Price Variants</h4>
                            <div id="variant-rows">
                                <?php if (empty($variants)): ?>
                                    <div class="variant-row" style="display:grid; grid-template-columns: 1fr 1.5fr 1fr; gap:0.5rem; margin-bottom:0.8rem;">
                                        <input type="number" name="variants[0][size]" value="<?php echo $product['size_ml']; ?>" placeholder="ML" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px;">
                                        <input type="number" step="0.01" name="variants[0][price]" value="<?php echo $product['price']; ?>" placeholder="Price ($)" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px;">
                                        <input type="number" name="variants[0][stock]" value="<?php echo $product['stock']; ?>" placeholder="Stock" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px;">
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($variants as $index => $v): ?>
                                        <div class="variant-row" style="display:grid; grid-template-columns: 1fr 1.5fr 1fr <?php echo $index > 0 ? '0.3fr' : ''; ?>; gap:0.5rem; margin-bottom:0.8rem;">
                                            <input type="number" name="variants[<?php echo $index; ?>][size]" value="<?php echo $v['size_ml']; ?>" placeholder="ML" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px;">
                                            <input type="number" step="0.01" name="variants[<?php echo $index; ?>][price]" value="<?php echo $v['price']; ?>" placeholder="Price ($)" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px;">
                                            <input type="number" name="variants[<?php echo $index; ?>][stock]" value="<?php echo $v['stock']; ?>" placeholder="Stock" required style="padding:0.7rem; border:1px solid #ddd; border-radius:6px;">
                                            <?php if ($index > 0): ?>
                                                <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ff4d4d; cursor:pointer; font-size:1.4rem; padding:0; line-height:1;">&times;</button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" onclick="addVariant()" style="background:#555; color:#fff; border:none; padding:0.5rem 1.2rem; border-radius:8px; cursor:pointer; font-size:0.8rem; font-weight:600; margin-top:0.5rem;">+ Add Size Option</button>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.8rem;">
                            <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Description</label>
                            <textarea name="description" rows="4" style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-family:inherit; line-height:1.6;"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 1.8rem;">
                            <div class="form-group">
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Top Notes</label>
                                <input type="text" name="top_notes" value="<?php echo htmlspecialchars($product['top_notes']); ?>" style="width:100%; padding:0.9rem; border:1px solid #ddd; border-radius:8px;">
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Heart Notes</label>
                                <input type="text" name="middle_notes" value="<?php echo htmlspecialchars($product['middle_notes']); ?>" style="width:100%; padding:0.9rem; border:1px solid #ddd; border-radius:8px;">
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:1px;">Base Notes</label>
                                <input type="text" name="base_notes" value="<?php echo htmlspecialchars($product['base_notes']); ?>" style="width:100%; padding:0.9rem; border:1px solid #ddd; border-radius:8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Info: Notes & Media -->
                    <div>

                        <div style="background: #fff; padding: 2rem; border-radius: 12px; border: 1px solid #eee; text-align: center;">
                            <label style="display:block; font-size:0.75rem; font-weight:800; color:#999; margin-bottom:1.5rem; text-transform:uppercase; letter-spacing:1px; text-align: left;">Product Visual</label>
                            
                            <div style="margin-bottom: 1.5rem; position: relative; display: inline-block;">
                                <img src="<?php echo strpos($product['main_image'], 'http') === 0 ? $product['main_image'] : '../../' . $product['main_image']; ?>" 
                                         alt="Current" width="180" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
                            </div>

                            <div class="form-group">
                                <input type="file" name="product_img" accept="image/*" id="img-upload" style="display: none;">
                                <label for="img-upload" class="btn" style="background:#f1f3f4; color:#555; width:100%; padding: 0.8rem;">Change Photo</label>
                                <p style="font-size: 0.75rem; color: #888; margin-top: 10px;">Max size 2MB. Format: JPG, PNG.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 4rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 1.2rem 4rem; font-size: 1rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(26, 35, 126, 0.2);">Save Product Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let variantCount = <?php echo count($variants) ?: 1; ?>;
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
