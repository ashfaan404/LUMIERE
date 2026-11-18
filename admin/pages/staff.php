<?php
// admin/pages/staff.php
require_once '../includes/admin_header.php';

// Only allow super admins
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
    $_SESSION['error_message'] = "Access denied. Super Admin privileges required.";
    header("Location: dashboard.php");
    exit();
}

// Fetch all admins and staff from their respective disconnected tables
$query = "SELECT staff_id as admin_id, staff_name as admin_name, staff_email as admin_email, 'staff' as role, created_at FROM staffs 
          UNION 
          SELECT admin_id, admin_name, admin_email, 'super' as role, created_at FROM admins 
          ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<div class="card">
    <div class="card-header">
        <h3>Staff Management</h3>
        <a href="add_staff.php" class="btn btn-primary btn-sm">+ Add New Staff</a>
    </div>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($staff = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-avatar-mini" style="width:30px; height:30px; font-size:0.75rem;">
                                <?php echo strtoupper(substr($staff['admin_name'], 0, 1)); ?>
                            </div>
                            <span style="font-weight:600;"><?php echo htmlspecialchars($staff['admin_name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($staff['admin_email']); ?></td>
                    <td>
                        <?php if($staff['role'] === 'super'): ?>
                            <span class="badge badge-info" style="background:#e8eaf6; color:#1a237e;">Super Admin</span>
                        <?php else: ?>
                            <span class="badge" style="background:#f1f3f4; color:#555;">Staff</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#666; font-size:0.85rem;"><?php echo date('M d, Y', strtotime($staff['created_at'])); ?></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="edit_staff.php?id=<?php echo $staff['admin_id']; ?>&role=<?php echo $staff['role']; ?>" class="btn-admin" style="font-size:0.8rem; padding:0.4rem 0.8rem; background:#f8f9fa; color:#333; border:1px solid #ddd;">Edit</a>
                            <?php if($staff['admin_id'] != $_SESSION['admin_id'] || $staff['role'] !== 'super'): ?>
                            <a href="../actions/delete_staff_action.php?id=<?php echo $staff['admin_id']; ?>&role=<?php echo $staff['role']; ?>" 
                               class="btn-admin" style="font-size:0.8rem; padding:0.4rem 0.8rem; background:#fff2f2; color:#d32f2f; border:1px solid #ffcdd2;"
                               onclick="return confirm('Are you sure you want to remove this account? This cannot be undone.');">Remove</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
