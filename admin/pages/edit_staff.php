<?php
// admin/pages/edit_staff.php
require_once '../includes/admin_header.php';

// Only allow super admins
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
    $_SESSION['error_message'] = "Access denied.";
    header("Location: dashboard.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$role = isset($_GET['role']) ? $_GET['role'] : 'staff';

if ($role === 'staff') {
    $stmt = $conn->prepare("SELECT staff_id as admin_id, staff_name as admin_name, staff_email as admin_email, 'staff' as role FROM staffs WHERE staff_id = ?");
} else {
    $stmt = $conn->prepare("SELECT admin_id, admin_name, admin_email, role FROM admins WHERE admin_id = ?");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();

if (!$staff) {
    $_SESSION['error_message'] = "Account not found.";
    header("Location: staff.php");
    exit();
}
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3>Edit Staff Profile</h3>
        <a href="staff.php" style="font-size: 0.9rem; color: var(--admin-navy); text-decoration: none;">← Back to Directory</a>
    </div>
    <div style="padding: 2.5rem;">
        <form action="../actions/edit_staff_action.php" method="POST" class="admin-form">
            <input type="hidden" name="admin_id" value="<?php echo $staff['admin_id']; ?>">
            <input type="hidden" name="current_role" value="<?php echo $role; ?>">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Full Name</label>
                <input type="text" name="admin_name" value="<?php echo htmlspecialchars($staff['admin_name']); ?>" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Email Address</label>
                <input type="email" name="admin_email" value="<?php echo htmlspecialchars($staff['admin_email']); ?>" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">New Password</label>
                <input type="password" name="admin_password" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;" placeholder="Leave blank to keep current">
            </div>

            <div class="form-group" style="margin-bottom: 2.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Access Role</label>
                <input type="text" value="<?php echo ucfirst($staff['role']); ?>" disabled style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; background:#f5f5f5; font-family:inherit; color: #888;">
                <input type="hidden" name="role" value="<?php echo $staff['role']; ?>">
                <p style="font-size:0.8rem; color:#888; margin-top: 0.5rem;">Role changes are not allowed between tables. Delete and recreate if necessary.</p>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
