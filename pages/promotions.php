<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$stmt = $conn->prepare("SELECT * FROM promotions WHERE end_date >= CURRENT_DATE() ORDER BY start_date DESC");
$stmt->execute();
$promotions = $stmt->get_result();

?>

<div class="mb-3 text-center">
    <h2 class="section-title">Special Offers & Promotions</h2>
   
</div>

<?php if ($promotions->num_rows > 0): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem; margin-bottom: 5rem;">
        <?php while ($promo = $promotions->fetch_assoc()): ?>
            <div style="background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: 0 15px 45px rgba(0,0,0,0.06); display: flex; flex-direction: column; transition: transform 0.3s ease; border: 1px solid rgba(0,0,0,0.03);" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                <?php if ($promo['banner_image']): ?>
                    <div class="promo-banner-wrapper" style="height: 200px; width: 100%; overflow: hidden; position: relative;">
                        <?php
            // Using renderImage but overriding its style for clarity
            $img_html = renderImage($promo['banner_image'], $promo['title'], 'promo-banner-img');
            echo str_replace('<img', '<img style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"', $img_html);
?>
                        <div style="position: absolute; top: 15px; right: 15px; background: rgba(212, 175, 55, 0.95); color: #fff; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <?php echo $promo['discount_percent']; ?>% OFF
                        </div>
                    </div>
                <?php
        endif; ?>
                
                <div style="padding: 1.8rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-family: var(--font-heading); font-size: 1.4rem; margin-bottom: 0.8rem; color: var(--secondary-color); font-weight: 700;">
                            <?php echo htmlspecialchars($promo['title']); ?>
                        </h3>
                        
                        <p style="font-size: 0.95rem; color: var(--light-text); margin-bottom: 1.2rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($promo['description']); ?>
                        </p>
                    </div>
                    
                    <div>
                        <div style="font-size: 0.8rem; color: #888; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>🕒</span> Valid until <?php echo date('M d, Y', strtotime($promo['end_date'])); ?>
                        </div>

                        <?php if (isset($_SESSION['active_promotion']) && $_SESSION['active_promotion'] == $promo['promotion_id']): ?>
                            <div style="display:flex; gap:0.5rem; flex-direction:column;">
                                <?php if ($promo['product_id']): ?>
                                    <a href="product.php?id=<?php echo $promo['product_id']; ?>" class="btn btn-primary" style="width: 100%; padding: 0.8rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Shop Item</a>
                                <?php endif; ?>
                                <a href="../actions/remove_offer.php" class="btn btn-secondary" style="width: 100%; padding: 0.8rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; background-color: #f8f9fa; color: #dc3545; border-color: #dc3545;">Remove Offer</a>
                            </div>
                        <?php else: ?>
                            <div style="display:flex; gap:0.5rem; flex-direction:column;">
                                <?php if ($promo['product_id']): ?>
                                    <a href="product.php?id=<?php echo $promo['product_id']; ?>" class="btn btn-primary" style="width: 100%; padding: 0.8rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; background-color: #333; border-color: #333;">View Item</a>
                                <?php endif; ?>
                                <a href="../actions/collect_offer.php?id=<?php echo $promo['promotion_id']; ?>" class="btn btn-primary" style="width: 100%; padding: 0.8rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Collect Offer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php
    endwhile; ?>
    </div>
<?php
else: ?>
    <div style="text-center; padding: 4rem 0;">
        <div class="alert alert-info text-center mb-2" style="max-width: 600px; margin: 0 auto;">There are no active promotions at the moment. Please check back later!</div>
        <p class="text-center"><a href="shop.php" class="btn btn-primary" style="width: auto;">Browse Shop</a></p>
    </div>
<?php
endif; ?>

<?php require_once '../includes/footer.php'; ?>
