<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['reset_user_id'])) {
    redirect('forgot_password.php');
}
?>

<div class="form-container">
    <h2>Reset Password</h2>
    <p class="text-center mb-2" style="color:var(--light-text); font-size: 0.9rem;">
        Please enter your new password below.
    </p>
    <p class="text-center mb-2" style="color:red; font-size: 0.9rem;">
        First letter Must be Capital letter and must contain at least one number and special character.
    </p>

    <form action="../actions/reset_password_action.php" method="POST">
        <div class="form-group">
            <label for="new_password">New Password *</label>
            <input type="password" id="new_password" name="new_password" required placeholder="Min. 6 characters">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Save New Password</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>