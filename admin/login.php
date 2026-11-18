<?php
session_start();
require_once '../includes/db.php';

// If already logged in, redirect to correct portal based on role
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
    if ($_SESSION['admin_role'] === 'super') {
        header("Location: pages/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | LUMIÈRE</title>
    <style>
        :root {
            --admin-navy: #1a237e;
            --admin-gold: #C5A059;
            --admin-bg: #f5f5f5;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--admin-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h2 {
            margin: 0;
            color: var(--admin-navy);
            font-family: 'Georgia', serif;
            font-size: 1.8rem;
        }
        .login-header h2 span { color: var(--admin-gold); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--admin-navy);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-btn:hover { background-color: #0d125a; }
        .error-msg {
            background-color: #ffebee;
            color: #c62828;
            padding: 0.8rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h2>LUMIÈRE <span>Admin</span></h2>
        <p style="color:#888; font-size:0.9rem; margin-top:0.5rem;">Please enter your staff credentials.</p>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-msg">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="actions/admin_login_action.php" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="admin@lumiere.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="login-btn">Secure Login</button>
    </form>
</div>

</body>
</html>
