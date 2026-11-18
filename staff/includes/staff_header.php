<?php
// staff/includes/staff_header.php
require_once __DIR__ . '/staff_auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Fetch unread notification count for staff
$staff_unread_query = $conn->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0");
$staff_unread_count = $staff_unread_query ? $staff_unread_query->fetch_row()[0] : 0;

// Fetch last 5 notifications
$staff_recent_notifs = $conn->query("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMIÈRE | Staff Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Georgia&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../admin/assets/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" style="background: linear-gradient(180deg, #424242 0%, #212121 100%);">
        <div class="sidebar-header">
            <a href="dashboard.php" class="admin-logo">LUMIÈRE<span>Staff</span></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="icon-box">📊</span> Dashboard
            </a>

            <a href="orders.php" class="<?php echo in_array($current_page, ['orders.php', 'order_details.php']) ? 'active' : ''; ?>">
                <span class="icon-box">📦</span> Orders
            </a>
            <a href="reviews.php" class="<?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <span class="icon-box">⭐</span> Reviews
            </a>
            <a href="tickets.php" class="<?php echo $current_page == 'tickets.php' ? 'active' : ''; ?>">
                <span class="icon-box">🎫</span> Support Tickets
            </a>
            
            <a href="notifications.php" class="<?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
                <span class="icon-box">🔔</span> Notifications
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

                    <!-- Staff Notification Bell -->
                    <div class="notification-dropdown">
                        <div class="notification-bell">
                            🔔
                            <?php if ($staff_unread_count > 0): ?>
                                <span class="notification-badge"><?php echo $staff_unread_count; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-content">
                            <div class="dropdown-header">
                                <span>Notifications</span>
                                <?php if ($staff_unread_count > 0): ?>
                                    <a href="../../admin/actions/mark_all_read.php">Mark all read</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list">
                                <?php if ($staff_recent_notifs && $staff_recent_notifs->num_rows > 0): ?>
                                    <?php while($sn = $staff_recent_notifs->fetch_assoc()): ?>
                                        <a href="../../admin/<?php echo htmlspecialchars($sn['link']); ?>" class="notification-item <?php echo $sn['is_read'] ? '' : 'unread'; ?>">
                                            <div class="notif-row-inner">
                                                <div class="notif-icon">
                                                    <?php
                                                    $icon_map = ['new_order'=>'📦','order_cancel'=>'❌','new_ticket'=>'🎫'];
                                                    echo $icon_map[$sn['type']] ?? '🔔';
                                                    ?>
                                                </div>
                                                <div class="notif-body">
                                                    <div class="notif-text"><?php echo htmlspecialchars($sn['message']); ?></div>
                                                    <div class="notif-time"><?php echo date('M d, H:i', strtotime($sn['created_at'])); ?></div>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="no-notifs">No notifications yet</div>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown-footer">
                                <a href="notifications.php">View All Notifications</a>
                            </div>
                        </div>
                    </div>

                    <div class="nav-divider"></div>

                    <div class="admin-profile-info">
                        <strong><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Staff'); ?> (Staff)</strong>
                        <div class="user-avatar-mini" style="background: #C5A059;">
                            <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'S', 0, 1)); ?>
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
