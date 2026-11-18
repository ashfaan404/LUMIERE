<?php
// admin/pages/settings.php
require_once '../includes/admin_header.php';

// Get current admin data (in case session is stale)
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT admin_name, admin_email FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
?>

<div class="settings-container" style="max-width: 800px; margin: 0 auto;">
    <div class="page-header" style="margin-bottom: 2rem;">
        <h2 style="color: var(--admin-navy); font-weight: 700;">Account Settings</h2>
        <p style="color: #666;">Manage your administrative profile and security preferences.</p>
    </div>

    <form action="../actions/update_admin_settings.php" method="POST" class="settings-form">
        <div class="card">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <span style="font-size: 1.2rem;">👤</span>
                    <h3 style="margin:0;">Profile Information</h3>
                </div>
            </div>
            <div style="padding: 2rem;">
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: #555;">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin_data['admin_name']); ?>" 
                               style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: border-color 0.2s;" required>
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: #555;">Login Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['admin_email']); ?>" 
                               style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: border-color 0.2s;" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <span style="font-size: 1.2rem;">🔒</span>
                    <h3 style="margin:0;">Security & Password</h3>
                </div>
            </div>
            <div style="padding: 2rem;">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: #555;">Current Password <span style="color: #ff5252;">*</span></label>
                    <input type="password" name="current_password" placeholder="Required to save any changes" 
                           style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: border-color 0.2s;" required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: #555;">New Password</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current" 
                               style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: border-color 0.2s;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: #555;">Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-type new password" 
                               style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: border-color 0.2s;">
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 2.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="reset" class="btn btn-secondary" style="background: #f1f3f4; color: #555;">Reset Changes</button>
            <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2.5rem; font-size: 1rem; box-shadow: 0 4px 15px rgba(26, 35, 126, 0.2);">Save All Changes</button>
        </div>
    </form>
</div>

<style>
.form-group input:focus {
    border-color: var(--admin-navy) !important;
    background: #fdfcff;
}
</style>

<?php require_once '../includes/admin_footer.php'; ?>
