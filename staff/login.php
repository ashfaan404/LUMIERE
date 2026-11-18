<?php
// staff/login.php
session_start();
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'staff') {
    header("Location: pages/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal Login | LUMIÈRE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Georgia&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #fdfbf7; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background: #fff; padding: 3rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 400px; text-align: center; border: 1px solid #eee; }
        .logo { font-family: 'Georgia', serif; font-size: 2rem; color: #1a237e; text-decoration: none; font-weight: bold; margin-bottom: 2rem; display: block; }
        .logo span { color: #C5A059; font-weight: normal; }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        label { display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 0.5rem; text-transform: uppercase; }
        input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        .btn { background: #C5A059; color: #fff; border: none; padding: 1rem; width: 100%; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #b08d4b; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-error { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>

<div class="login-card">
    <a href="../pages/home.php" class="logo">LUMIÈRE<span style="display:block; font-size:1rem; margin-top:5px; color:#555; text-transform:uppercase; letter-spacing:2px; font-family:'Inter',sans-serif;">Staff Portal</span></a>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="actions/login_action.php" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Access Portal</button>
    </form>
</div>

</body>
</html>
