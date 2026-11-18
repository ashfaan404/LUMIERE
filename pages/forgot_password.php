<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>

<div class="form-container">
    <h2>Forgot Password</h2>
    <p class="text-center mb-2" style="color:var(--light-text); font-size: 0.9rem;">
        Enter your registered email address to receive an OTP to reset your password.
    </p>

    <form action="../actions/forgot_password_action.php" method="POST">
        <div class="form-group">
            <label for="contact">Email Address</label>
            <input type="text" id="contact" name="contact" required placeholder="Enter your registered email">
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Get Reset OTP</button>
    </form>
    
    <div style="margin-top: 1.5rem; text-align: center;">
        <p style="margin-top: 1rem;"><a href="login.php" class="form-link" style="display:inline; margin:0; font-weight:bold;">Back to Login</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
