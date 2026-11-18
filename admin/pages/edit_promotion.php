<?php
require_once '../includes/admin_header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid Promotion ID.";
    header("Location: promotions.php");
    exit;
}

$promo_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM promotions WHERE promotion_id = ?");
$stmt->bind_param("i", $promo_id);
$stmt->execute();
$promo = $stmt->get_result()->fetch_assoc();

if (!$promo) {
    $_SESSION['error_message'] = "Promotion not found.";
    header("Location: promotions.php");
    exit;
}

// Get products for target selection
$products = $conn->query("SELECT product_id, product_name, brand FROM products ORDER BY brand ASC, product_name ASC");
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="admin-card">
        <div class="admin-card-header" style="background: linear-gradient(135deg, var(--admin-navy) 0%, var(--admin-navy-dark) 100%); padding: 2.5rem 3rem; color: #fff;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">✏️</div>
                <div>
                    <h3 style="color: #fff; margin:0; font-size: 1.5rem;">Edit Promotion</h3>
                    <p style="color: rgba(255,255,255,0.6); margin: 0.3rem 0 0; font-size: 0.9rem;">Update the details of your marketing campaign as Super Admin.</p>
                </div>
            </div>
        </div>
        
        <div style="padding: 3rem;">
            <form action="../actions/edit_promotion_action.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="promotion_id" value="<?php echo $promo['promotion_id']; ?>">
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;">
                    <div>
                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Promotion Title</label>
                            <input type="text" name="title" required value="<?php echo htmlspecialchars($promo['title']); ?>" style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-size: 1rem;">
                        </div>

                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Marketing Description</label>
                            <textarea name="description" required style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-size: 1rem; min-height: 120px; font-family: inherit;"><?php echo htmlspecialchars($promo['description']); ?></textarea>
                        </div>
                    </div>

                    <div style="background: #fdfbf7; padding: 2rem; border-radius: 12px; border: 1px dashed var(--admin-gold);">
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Discount (%)</label>
                            <div style="position: relative;">
                                <input type="number" name="discount_percent" min="0" max="100" required value="<?php echo $promo['discount_percent']; ?>" style="width:100%; padding:1rem; border:1px solid #ddd; border-radius:8px; font-size: 1.5rem; font-weight: 800; color: #d32f2f;">
                                <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #d32f2f; font-size: 1.2rem;">%</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Targeted Product</label>
                            <select name="product_id" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px; background: #fff;">
                                <option value="">Site-wide (All Products)</option>
                                <?php while($p = $products->fetch_assoc()): ?>
                                    <option value="<?php echo $p['product_id']; ?>" <?php echo ($p['product_id'] == $promo['product_id']) ? 'selected' : ''; ?>>
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
                        <input type="date" name="start_date" required value="<?php echo $promo['start_date']; ?>" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">End Date</label>
                        <input type="date" name="end_date" required value="<?php echo $promo['end_date']; ?>" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px;">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 2rem;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">Banner Image URL</label>
                    <?php if(!empty($promo['banner_image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="../../<?php echo htmlspecialchars($promo['banner_image']); ?>" alt="Current Banner" style="max-height: 100px; border-radius: 4px; border: 1px solid #ddd;">
                            <p style="font-size: 0.75rem; color: #888;">Current banner displayed above.</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="banner_image" accept="image/*" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:8px; background: #fff;">
                    <p style="font-size: 0.8rem; color: #888; margin-top: 8px;">Leave empty to keep the current banner.</p>
                </div>

                <div style="margin-top: 3.5rem; display: flex; gap: 1.5rem;">
                    <button type="submit" class="btn-admin btn-admin-primary" style="padding: 1.2rem 3rem; font-size: 1rem; border-radius: 10px; border: none; cursor: pointer;">Save Changes</button>
                    <a href="promotions.php" class="btn-admin" style="background: #f1f3f4; color: #555; padding: 1.2rem 2.5rem; border-radius: 10px; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
