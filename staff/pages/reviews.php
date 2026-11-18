<?php
// staff/pages/reviews.php
require_once '../includes/staff_header.php';

$reviews = $conn->query("SELECT r.*, p.product_name, u.full_name 
                        FROM reviews r 
                        JOIN products p ON r.product_id = p.product_id 
                        JOIN users u ON r.user_id = u.user_id 
                        ORDER BY r.created_at DESC");
?>

<div class="card">
    <div class="card-header">
        <h3>Customer Product Reviews</h3>
    </div>
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if($reviews->num_rows > 0): ?>
                    <?php while($r = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['product_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                        <td>
                            <span style="color: #C5A059;">
                                <?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?>
                            </span>
                        </td>
                        <td>
                            <div style="max-width: 400px; font-size: 0.85rem; color: #555;">
                                <?php echo htmlspecialchars($r['review_text']); ?>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 2rem; color: #888;">No reviews yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/staff_footer.php'; ?>
