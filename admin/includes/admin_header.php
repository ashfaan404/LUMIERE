<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// current page name for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch unread notifications count
$unread_count_query = $conn->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0");
$unread_count = $unread_count_query ? $unread_count_query->fetch_row()[0] : 0;

// Fetch last 5 notifications
$recent_notifications = $conn->query("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMIÈRE | Admin Panel</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Georgia&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="admin-logo">LUMIÈRE<span>Admin</span></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="icon-box">📊</span> Dashboard
            </a>
            <a href="products.php" class="<?php echo in_array($current_page, ['products.php', 'add_product.php', 'edit_product.php']) ? 'active' : ''; ?>">
                <span class="icon-box">🧴</span> Products
            </a>
            <a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <span class="icon-box">📁</span> Categories
            </a>
            <a href="promotions.php" class="<?php echo $current_page == 'promotions.php' ? 'active' : ''; ?>">
                <span class="icon-box">🏷️</span> Promotions
            </a>
            <a href="orders.php" class="<?php echo in_array($current_page, ['orders.php', 'order_details.php']) ? 'active' : ''; ?>">
                <span class="icon-box">📦</span> Orders
            </a>
            <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <span class="icon-box">👥</span> Users
            </a>
            <a href="reviews.php" class="<?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <span class="icon-box">⭐</span> Reviews
            </a>
            <a href="tickets.php" class="<?php echo $current_page == 'tickets.php' ? 'active' : ''; ?>">
                <span class="icon-box">🎫</span> Support
            </a>
            <a href="reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <span class="icon-box">📊</span> Reports
            </a>
            <a href="notifications.php" class="<?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
                <span class="icon-box">🔔</span> Notifications
                <?php if ($unread_count > 0): ?>
                    <span style="background:#e74c3c; color:#fff; border-radius:50px; font-size:0.65rem; padding:1px 7px; margin-left:auto; font-weight:700;"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            
            <div style="margin-top: 2rem; padding: 0 1.5rem; opacity: 0.3; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color:#fff;">System</div>
            <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super'): ?>
            <a href="staff.php" class="<?php echo in_array($current_page, ['staff.php', 'add_staff.php', 'edit_staff.php']) ? 'active' : ''; ?>">
                <span class="icon-box">🛡️</span> Staff
            </a>
            <?php endif; ?>
            <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <span class="icon-box">⚙️</span> Settings
            </a>
            <a href="../logout.php" style="margin-top: auto;">
                <span class="icon-box">🚪</span> Sign Out
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <div class="header-search">
                    <span>🔍</span> <span>Search for orders, products...</span>
                </div>
            </div>
            
            <div class="header-right">
                <div class="user-profile-nav">
                    <!-- Notifications -->
                    <div class="notification-dropdown">
                        <div class="notification-bell">
                            🔔
                            <?php if ($unread_count > 0): ?>
                                <span class="notification-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-content">
                            <div class="dropdown-header">
                                <span>Recent Notifications</span>
                                <?php if ($unread_count > 0): ?>
                                    <a href="../actions/mark_all_read.php">Mark all read</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list">
                                <?php if ($recent_notifications && $recent_notifications->num_rows > 0): ?>
                                    <?php while($n = $recent_notifications->fetch_assoc()): ?>
                                        <a href="<?php echo $n['link']; ?>" class="notification-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                                            <div class="notif-row-inner">
                                                <div class="notif-icon">
                                                    <?php echo ($n['type'] == 'new_order') ? '📦' : (($n['type'] == 'new_ticket') ? '🎫' : '🔔'); ?>
                                                </div>
                                                <div class="notif-body">
                                                    <div class="notif-text"><?php echo htmlspecialchars($n['message']); ?></div>
                                                    <div class="notif-time"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></div>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="no-notifs">No notifications yet</div>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown-footer">
                                <a href="notifications.php">Notifications</a>
                            </div>
                        </div>
                    </div>

                    <div class="nav-divider"></div>
                    
                    <!-- Admin Profile -->
                    <div class="admin-profile-info">
                        <strong><?php echo $_SESSION['admin_name']; ?></strong>
                        <div class="user-avatar-mini">
                            <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>


        <main class="admin-page-content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert badge-success" style="padding: 1rem; margin-bottom: 2rem; border-radius: 8px;">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert badge-danger" style="padding: 1rem; margin-bottom: 2rem; border-radius: 8px;">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
