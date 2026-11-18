<?php
// admin/pages/add_staff.php
require_once '../includes/admin_header.php';

// Only allow super admins
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super') {
    $_SESSION['error_message'] = "Access denied.";
    header("Location: dashboard.php");
    exit();
}
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3>Add New Staff Member</h3>
        <a href="staff.php" style="font-size: 0.9rem; color: var(--admin-navy); text-decoration: none;">← Back to Staff List</a>
    </div>
    <div style="padding: 2.5rem;">
        <form action="../actions/add_staff_action.php" method="POST" class="admin-form">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Full Name</label>
                <input type="text" name="admin_name" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Email Address</label>
                <input type="email" name="admin_email" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Password</label>
                <input type="password" name="admin_password" required minlength="6" style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; font-family:inherit;" placeholder="Min. 6 characters">
            </div>

            <div class="form-group" style="margin-bottom: 2.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#555; margin-bottom:0.5rem; text-transform:uppercase;">Access Role</label>
                <select name="role" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:6px; background:#fff; font-family:inherit;">
                    <option value="staff">Staff (Limited Access System)</option>
                    <option value="super">Super Admin (Full Access)</option>
                </select>
                <p style="font-size:0.75rem; color:#888; margin-top:0.5rem; line-height:1.4;">
                    <strong>Staff</strong> users have their own separate dashboard and cannot manage other admins or change system settings.<br>
                    <strong>Super Admins</strong> have full control over the store.
                </p>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">Create Account</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
