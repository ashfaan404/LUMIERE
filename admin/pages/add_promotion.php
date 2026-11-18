<?php
// admin/pages/add_promotion.php
require_once '../includes/admin_header.php';

// Get products for target selection
$products = $conn->query("SELECT product_id, product_name, brand FROM products ORDER BY brand ASC, product_name ASC");
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, var(--admin-navy) 0%, var(--admin-navy-dark) 100%); padding: 2.5rem 3rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🏷️</div>
                <div>
                    <h3 style="color: #fff; margin:0; font-size: 1.5rem;">Create New Promotion</h3>
                    <p style="color: rgba(255,255,255,0.6); margin: 0.3rem 0 0; font-size: 0.9rem;">Launch a marketing campaign to boost sales.</p>
                </div>
            </div>
        </div>
        
        <div style="padding: 3rem;">
            <form action="../actions/add_promotion_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;">
                    <div>
                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Promotion Title</label>
                            <input type="text" name="title" required placeholder="e.g. Black Friday Special" style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-size: 1rem;">
                        </div>

                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Marketing Description</label>
                            <textarea name="description" required placeholder="Describe the offer details..." style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-size: 1rem; min-height: 120px; font-family: inherit;"></textarea>
                        </div>
                    </div>

                    <div style="background: #fdfbf7; padding: 2rem; border-radius: 12px; border: 1px dashed var(--admin-gold);">
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Discount (%)</label>
                            <div style="position: relative;">
                                <input type="number" name="discount_percent" min="1" max="100" required value="10" style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-size: 1.5rem; font-weight: 800; color: #d32f2f;">
                                <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #d32f2f; font-size: 1.2rem;">%</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Targeted Product</label>
                            <select name="product_id" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px; background: #fff;">
                                <option value="">Site-wide (All Products)</option>
                                <?php while($p = $products->fetch_assoc()): ?>
                                    <option value="<?php echo $p['product_id']; ?>">
                                        <?php echo htmlspecialchars($p['brand'] . ' - ' . $p['product_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Start Date</label>
                        <input type="date" name="start_date" required value="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">End Date</label>
                        <input type="date" name="end_date" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 2rem;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Banner Image</label>
                    <input type="file" name="banner_image" accept="image/*" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px; background: #fff;">
                    <p style="font-size: 0.8rem; color: #888; margin-top: 8px;">Pro-tip: Use high-resolution lifestyle images (approx 1200x400px) for better engagement.</p>
                </div>

                <div style="margin-top: 3.5rem; display: flex; gap: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 1.2rem 3rem; font-size: 1rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(26, 35, 126, 0.2);">Deploy Campaign</button>
                    <a href="promotions.php" class="btn btn-secondary" style="background: #f1f3f4; color: #555; padding: 1.2rem 2.5rem; border-radius: 10px;">Discard</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
