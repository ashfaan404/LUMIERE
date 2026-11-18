<?php
require_once 'functions.php';
// Note: $conn should be available from the page including this header

// Detect if we're being called from customer/ subfolder or pages/ subfolder
// $_SERVER['SCRIPT_NAME'] always uses forward slashes (reliable on Windows too)
$in_customer = (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false);

// Paths relative to the browser URL
$pages_base = $in_customer ? '../pages/' : '';
$customer_base = $in_customer ? '' : '../customer/';
$assets_base = '../assets/'; // same relative depth from both pages/ and customer/

// Load current user's profile pic & name if logged in
$nav_user = null;
$nav_initials = '?';
if (isLoggedIn() && isset($conn)) {
    try {
        $nav_stmt = $conn->prepare("SELECT full_name, email, profile_pic FROM users WHERE user_id = ?");
        $nav_stmt->bind_param("i", $_SESSION['user_id']);
        $nav_stmt->execute();
        $nav_user = $nav_stmt->get_result()->fetch_assoc();
        if ($nav_user) {
            $np = explode(' ', $nav_user['full_name'], 2);
            $nav_initials = strtoupper(substr($np[0] ?? '', 0, 1) . substr($np[1] ?? '', 0, 1));
        }
    }
    catch (Throwable $e) {
        // profile_pic column may not exist yet — run run_migration.php
        try {
            $nav_stmt2 = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
            $nav_stmt2->bind_param("i", $_SESSION['user_id']);
            $nav_stmt2->execute();
            $nav_user = $nav_stmt2->get_result()->fetch_assoc();
            if ($nav_user) {
                $nav_user['profile_pic'] = null;
                $np = explode(' ', $nav_user['full_name'], 2);
                $nav_initials = strtoupper(substr($np[0] ?? '', 0, 1) . substr($np[1] ?? '', 0, 1));
            }
        }
        catch (Throwable $e2) { /* give up gracefully */
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMIÈRE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $assets_base; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo $assets_base; ?>css/customer.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="<?php echo $pages_base; ?>home.php">
                <h1>LUMIÈRE</h1>
            </a>
        </div>
        
        <nav class="main-nav" id="main-nav">
            <ul id="nav-links">
                <li><a href="<?php echo $pages_base; ?>home.php">Home</a></li>
                <li><a href="<?php echo $pages_base; ?>shop.php">Shop</a></li>
                <li><a href="<?php echo $pages_base; ?>promotions.php">Offers</a></li>
                <li><a href="<?php echo $pages_base; ?>support.php">Support</a></li>
            </ul>
        </nav>

        <div class="header-icons">
            <div class="search-box">
                <form action="<?php echo $pages_base; ?>shop.php" method="GET" style="display: flex; position: relative;">
                    <input type="text" name="search" placeholder="Search perfumes..." required value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">🔍</button>
                </form>
            </div>

            <a href="<?php echo $pages_base; ?>cart.php" class="cart-link">
                Cart <?php echo getCartCount() > 0 ? "<span class='badge'>" . getCartCount() . "</span>" : ""; ?>
            </a>

            <a href="<?php echo $pages_base; ?>compare.php" class="icon-link">
                Compare <?php echo getCompareCount() > 0 ? "<span class='badge'>" . getCompareCount() . "</span>" : ""; ?>
            </a>

            <a href="<?php echo $customer_base; ?>../pages/wishlist.php" class="icon-link">
                Wishlist <?php echo(isLoggedIn() && isset($conn)) ? (getWishlistCount($conn) > 0 ? "<span class='badge'>" . getWishlistCount($conn) . "</span>" : "") : ""; ?>
            </a>

            <?php if (isLoggedIn() && $nav_user): ?>
                <!-- Profile avatar with dropdown -->
                <div class="profile-nav-item">
                    <a href="<?php echo $customer_base; ?>profile.php" class="profile-avatar-btn" title="My Account">
                        <?php if (!empty($nav_user['profile_pic']) && file_exists('../' . $nav_user['profile_pic'])): ?>
                            <img src="../<?php echo htmlspecialchars($nav_user['profile_pic']); ?>" alt="Profile">
                        <?php
    else: ?>
                            <?php echo htmlspecialchars($nav_initials); ?>
                        <?php
    endif; ?>
                    </a>
                    <div class="profile-dropdown">
                        <div class="profile-dropdown-header">
                            <div class="profile-dropdown-name"><?php echo htmlspecialchars($nav_user['full_name']); ?></div>
                            <div class="profile-dropdown-email"><?php echo htmlspecialchars($nav_user['email']); ?></div>
                        </div>
                        <a href="<?php echo $customer_base; ?>profile.php">👤 My Profile</a>
                        <a href="<?php echo $pages_base; ?>wishlist.php">❤️ My Wishlist</a>
                        <a href="<?php echo $customer_base; ?>orders.php">📦 My Orders</a>
                        <a href="<?php echo $customer_base; ?>addresses.php">📍 Saved Addresses</a>
                        <a href="<?php echo $pages_base; ?>logout.php" class="dropdown-logout">🚪 Sign Out</a>
                    </div>
                </div>
            <?php
else: ?>
                <a href="<?php echo $pages_base; ?>login.php" class="icon-link">Login</a>
            <?php
endif; ?>
            
            <button id="mobile-menu-toggle" class="mobile-menu-btn">&#9776;</button>
        </div>
    </div>
</header>

<div class="flash-messages">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message'];
    unset($_SESSION['success_message']); ?>
            <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
        </div>
    <?php
endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error_message'];
    unset($_SESSION['error_message']); ?>
            <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
        </div>
    <?php
endif; ?>
</div>

<main>
<script>
// Mobile menu toggle
var mobileBtn = document.getElementById('mobile-menu-toggle');
var mainNav   = document.getElementById('main-nav');
if (mobileBtn && mainNav) {
    mobileBtn.addEventListener('click', function() {
        mainNav.classList.toggle('active');
    });
}
</script>
