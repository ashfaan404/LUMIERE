<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>

<div class="form-container" style="max-width: 600px;">
    <h2>Create an Account</h2>
    <p class="text-center mb-2" style="color:var(--light-text); font-size: 0.9rem;">Join us to enjoy a premium shopping experience.</p>
    
    <form action="../actions/register_action.php" method="POST">
        <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" required placeholder="John Doe">
        </div>
        
        <div class="form-group flex gap-1 justify-between">
            <div style="flex: 1;">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required placeholder="john@example.com">
            </div>
            <div style="flex: 1;">
                <label for="phone">Phone Number *</label>
                <input type="text" id="phone" name="phone" required placeholder="1234567890">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Delivery Address</label>
            <textarea id="address" name="address" placeholder="Enter your full address"></textarea>
        </div>
        
        <div class="form-group flex gap-1 justify-between">
            <div style="flex: 1;">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required placeholder="Min. 6 characters">
            </div>
            <div style="flex: 1;">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Register Account</button>
    </form>
    
    <div style="margin-top: 1.5rem; text-align: center;">
        <p style="margin-top: 1rem;">Already have an account? <a href="login.php" class="form-link" style="display:inline; margin:0; font-weight:bold;">Login here</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
