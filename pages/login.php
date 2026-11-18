<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (isLoggedIn()) {
    redirect('home.php');
}
?>

<div class="form-container">
    <h2>Login to Your Account</h2>
    
    <form action="../actions/login_action.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>
    
    <div style="margin-top: 1.5rem; text-align: center;">
        <p style="margin-bottom: 0.5rem;"><a href="forgot_password.php" class="form-link" style="display:inline; margin:0; color:var(--light-text);">Forgot Password?</a></p>
        <p style="margin-top: 1rem;">Don't have an account? <a href="register.php" class="form-link" style="display:inline; margin:0; font-weight:bold;">Register here</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
