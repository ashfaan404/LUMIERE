<?php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get user's past tickets
$stmt = $conn->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result();

// Get user's past orders for the dropdown
$ord_stmt = $conn->prepare("SELECT order_id, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$ord_stmt->bind_param("i", $user_id);
$ord_stmt->execute();
$orders = $ord_stmt->get_result();
?>

<div class="mb-3 text-center">
    <h2 class="section-title">Support Center</h2>
    <p class="text-center" style="color:var(--light-text);">Need help? Submit a ticket below or review your past inquiries.</p>
</div>

<div class="shop-layout">
    <div class="shop-main" style="flex: 2;">
        <div style="background: var(--white); padding: 2rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Submit a New Ticket</h3>
            
            <form action="../actions/submit_ticket.php" method="POST" enctype="multipart/form-data">
                <div class="form-group flex gap-1 justify-between">
                    <div style="flex: 1;">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" required placeholder="Brief description of your issue">
                    </div>
                </div>
                
                <div class="form-group flex gap-1 justify-between">
                    <div style="flex: 1;">
                        <label for="ticket_type">Ticket Type *</label>
                        <select name="ticket_type" id="ticket_type" required>
                            <option value="">Select Category</option>
                            <option value="inquiry">General Inquiry</option>
                            <option value="complaint">Complaint</option>
                            <option value="return">Return / Refund Request</option>
                            <option value="cancel">Product Cancellation</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label for="order_id">Related Order (Optional)</label>
                        <select name="order_id" id="order_id">
                            <option value="">None</option>
                            <?php while($o = $orders->fetch_assoc()): ?>
                                <option value="<?php echo $o['order_id']; ?>">
                                    Order #<?php echo str_pad($o['order_id'], 6, "0", STR_PAD_LEFT); ?> (<?php echo date('M j, Y', strtotime($o['order_date'])); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="image_upload_group" style="display: none; margin-bottom: 1.5rem;">
                    <label for="return_image">Upload Product Image (Required for Returns)</label>
                    <input type="file" name="return_image" id="return_image" accept="image/*" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>

                <script>
                    document.getElementById('ticket_type').addEventListener('change', function() {
                        const uploadGroup = document.getElementById('image_upload_group');
                        const fileInput = document.getElementById('return_image');
                        if (this.value === 'return') {
                            uploadGroup.style.display = 'block';
                            fileInput.required = true;
                        } else {
                            uploadGroup.style.display = 'none';
                            fileInput.required = false;
                        }
                    });
                </script>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required placeholder="Describe your issue in detail..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: auto;">Submit Ticket</button>
            </form>
        </div>
    </div>
    
    <div class="shop-sidebar" style="flex: 1;">
        <div style="background: var(--white); padding: 2rem; border-radius: 8px; border: 1px solid var(--border-color); position: sticky; top: 100px;">
            <h3 style="margin-bottom: 1.5rem; font-family: var(--font-heading);">Previous Tickets</h3>
            
            <?php if($tickets->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php while($tk = $tickets->fetch_assoc()): ?>
                        <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong>#<?php echo str_pad($tk['ticket_id'], 5, "0", STR_PAD_LEFT); ?></strong>
                                <span style="font-size: 0.8rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; background: var(--white); border: 1px solid var(--border-color); 
                                <?php echo in_array($tk['status'], ['Closed', 'Cancelled']) ? 'color: var(--light-text);' : 'color: var(--success);'; ?>">
                                    <?php echo htmlspecialchars($tk['status']); ?>
                                </span>
                            </div>
                            <div style="font-size: 0.95rem; margin-bottom: 0.5rem; font-weight: 500;">
                                <?php echo htmlspecialchars($tk['subject']); ?>
                                <?php if($tk['status'] !== 'Closed' && $tk['status'] !== 'Cancelled' && $tk['status'] !== 'In Progress'): ?>
                                    <br><a href="../actions/cancel_ticket.php?id=<?php echo $tk['ticket_id']; ?>" style="font-size: 0.8rem; color: #dc3545; text-decoration: underline;" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel Ticket</a>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--light-text);">
                                <span><?php echo ucfirst(htmlspecialchars($tk['ticket_type'])); ?></span>
                                <span><?php echo date('M j, Y', strtotime($tk['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--light-text); font-size: 0.9rem;">You have not submitted any support tickets yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
