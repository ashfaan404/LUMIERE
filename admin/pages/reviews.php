<?php
// admin/pages/reviews.php
require_once '../includes/admin_header.php';

$reviews = $conn->query("SELECT r.*, p.product_name, u.full_name 
                        FROM reviews r 
                        JOIN products p ON r.product_id = p.product_id 
                        JOIN users u ON r.user_id = u.user_id 
                        ORDER BY r.created_at DESC");
?>

<div class="card">
    <div class="card-header">
        <h3>Product Reviews</h3>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $reviews->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['product_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                    <td>
                        <span style="color: var(--admin-gold);">
                            <?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?>
                        </span>
                    </td>
                    <td>
                        <div style="max-width: 300px; font-size: 0.85rem; color: #555;">
                            <?php echo htmlspecialchars($r['review_text']); ?>
                        </div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                    <td>
                        <a href="../actions/delete_review.php?id=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-delete" style="color: #d32f2f;">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
