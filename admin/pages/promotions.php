<?php
// admin/pages/promotions.php
require_once '../includes/admin_header.php';

$promos = $conn->query("SELECT * FROM promotions ORDER BY end_date DESC");
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Store Promotions</h3>
        <a href="add_promotion.php" class="btn btn-primary" style="width: auto;">+ Add New Promotion</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Promotion</th>
                    <th>Discount</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $promos->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($p['title']); ?></div>
                        <div style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($p['description']); ?></div>
                    </td>
                    <td><strong style="color: #d32f2f;"><?php echo $p['discount_percent']; ?>% OFF</strong></td>
                    <td>
                        <div style="font-size: 0.85rem;"><?php echo date('M d', strtotime($p['start_date'])); ?> - <?php echo date('M d, Y', strtotime($p['end_date'])); ?></div>
                    </td>
                    <td>
                        <?php 
                        $today = date('Y-m-d');
                        if($today < $p['start_date']) echo '<span class="badge badge-info">Upcoming</span>';
                        elseif($today > $p['end_date']) echo '<span class="badge badge-danger">Expired</span>';
                        else echo '<span class="badge badge-success">Active</span>';
                        ?>
                    </td>
                    <td>
                        <a href="edit_promotion.php?id=<?php echo $p['promotion_id']; ?>" class="text-primary" style="margin-right: 1rem;">Edit</a>
                        <a href="../actions/delete_promotion.php?id=<?php echo $p['promotion_id']; ?>" class="text-danger" onclick="return confirm('Archive this promotion?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
