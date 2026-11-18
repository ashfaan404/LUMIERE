<?php
// staff/pages/tickets.php
require_once '../includes/staff_header.php';

$tickets = $conn->query("SELECT t.*, u.full_name 
                        FROM support_tickets t 
                        JOIN users u ON t.user_id = u.user_id 
                        ORDER BY t.created_at DESC");
?>

<div class="card">
    <div class="card-header">
        <h3>Support Tickets</h3>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($t = $tickets->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $t['ticket_id']; ?></td>
                    <td><?php echo htmlspecialchars($t['full_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($t['subject']); ?></strong></td>
                    <td><span class="badge badge-info" style="font-size: 0.65rem;"><?php echo $t['ticket_type']; ?></span></td>
                    <td>
                        <span class="badge <?php 
                            echo $t['status'] == 'Open' ? 'badge-danger' : 
                                ($t['status'] == 'Closed' ? 'badge-success' : 'badge-pending'); 
                        ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                    <td>
                        <form action="../actions/update_ticket_status.php" method="POST" style="display:flex; gap: 0.5rem;">
                            <input type="hidden" name="ticket_id" value="<?php echo $t['ticket_id']; ?>">
                            <select name="status" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 2px; border-radius: 4px;">
                                <option value="Open" <?php echo $t['status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                                <option value="In Progress" <?php echo $t['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Resolved" <?php echo $t['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="Closed" <?php echo $t['status'] == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/staff_footer.php'; ?>
