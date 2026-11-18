<?php
// staff/pages/notifications.php
require_once '../includes/staff_header.php';

// Filter by type
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter === 'orders') {
    $all_notifs = $conn->query("SELECT * FROM admin_notifications WHERE type = 'new_order' ORDER BY created_at DESC LIMIT 100");
} elseif ($filter === 'cancelled') {
    $all_notifs = $conn->query("SELECT * FROM admin_notifications WHERE type = 'order_cancel' ORDER BY created_at DESC LIMIT 100");
} elseif ($filter === 'tickets') {
    $all_notifs = $conn->query("SELECT * FROM admin_notifications WHERE type = 'new_ticket' ORDER BY created_at DESC LIMIT 100");
} else {
    $all_notifs = $conn->query("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 100");
}

// Count unread by type
$counts = ['all'=>0,'orders'=>0,'cancelled'=>0,'tickets'=>0];
$cnt_res = $conn->query("SELECT type, COUNT(*) as c FROM admin_notifications WHERE is_read = 0 GROUP BY type");
if ($cnt_res) {
    while ($r = $cnt_res->fetch_assoc()) {
        $counts['all'] += $r['c'];
        if ($r['type'] === 'new_order')    $counts['orders']    += $r['c'];
        if ($r['type'] === 'order_cancel') $counts['cancelled'] += $r['c'];
        if ($r['type'] === 'new_ticket')   $counts['tickets']   += $r['c'];
    }
}

function notifIcon($type) {
    $map = ['new_order'=>'📦','order_cancel'=>'❌','new_ticket'=>'🎫'];
    return $map[$type] ?? '🔔';
}
function notifColor($type) {
    $map = ['new_order'=>'#e3f2fd','order_cancel'=>'#fce4ec','new_ticket'=>'#fff8e1'];
    return $map[$type] ?? '#f9f9f9';
}
function notifBorderColor($type) {
    $map = ['new_order'=>'#1976d2','order_cancel'=>'#c62828','new_ticket'=>'#f9a825'];
    return $map[$type] ?? '#C5A059';
}
?>

<div style="margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 style="margin:0; font-size:1.4rem; color:var(--admin-navy);">🔔 Notifications</h2>
        <p style="margin:4px 0 0; font-size:0.85rem; color:#888;">All alerts — new orders, cancellations, and support tickets.</p>
    </div>
    <?php if ($counts['all'] > 0): ?>
    <a href="../../admin/actions/mark_all_read.php"
       style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; background:#fff; border:1px solid #ddd; border-radius:8px; font-size:0.82rem; color:#555; text-decoration:none; font-weight:500;">
        ✅ Mark All as Read
    </a>
    <?php endif; ?>
</div>

<!-- Filter Tabs -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <?php
    $tabs = [
        'all'       => ['label'=>'All',             'icon'=>'🔔'],
        'orders'    => ['label'=>'New Orders',       'icon'=>'📦'],
        'cancelled' => ['label'=>'Order Cancelled',  'icon'=>'❌'],
        'tickets'   => ['label'=>'Support Tickets',  'icon'=>'🎫'],
    ];
    foreach ($tabs as $key => $tab):
        $active = ($filter === $key);
        $unread = $counts[$key];
    ?>
    <a href="notifications.php?filter=<?php echo $key; ?>"
       style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; border-radius:50px; font-size:0.85rem; font-weight:600; text-decoration:none; transition:all 0.2s;
              background:<?php echo $active ? '#424242' : '#fff'; ?>;
              color:<?php echo $active ? '#fff' : '#555'; ?>;
              border:1px solid <?php echo $active ? '#424242' : '#ddd'; ?>;
              box-shadow:<?php echo $active ? '0 2px 8px rgba(0,0,0,0.2)' : 'none'; ?>;">
        <?php echo $tab['icon']; ?> <?php echo $tab['label']; ?>
        <?php if ($unread > 0): ?>
            <span style="background:<?php echo $active ? 'rgba(255,255,255,0.25)' : '#e74c3c'; ?>; color:#fff; border-radius:50px; font-size:0.7rem; padding:0 6px; min-width:18px; line-height:18px; text-align:center;">
                <?php echo $unread; ?>
            </span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card" style="overflow:hidden;">
    <?php if ($all_notifs && $all_notifs->num_rows > 0): ?>
        <?php while($n = $all_notifs->fetch_assoc()): ?>
            <div class="notif-row <?php echo $n['is_read'] ? '' : 'unread'; ?>"
                 onclick="window.location.href='../../admin/<?php echo htmlspecialchars($n['link']); ?>'"
                 style="cursor:pointer; padding:1.2rem 2rem; border-bottom:1px solid #f0f0f0; transition:background 0.2s; display:flex; align-items:center; gap:1.2rem;">

                <!-- Icon -->
                <div style="width:46px; height:46px; border-radius:50%; background:<?php echo notifColor($n['type']); ?>; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; border:2px solid <?php echo notifBorderColor($n['type']); ?>20;">
                    <?php echo notifIcon($n['type']); ?>
                </div>

                <!-- Message -->
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:<?php echo $n['is_read'] ? '500' : '700'; ?>; font-size:0.95rem; color:var(--admin-navy); margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <?php echo htmlspecialchars($n['message']); ?>
                    </div>
                    <div style="font-size:0.78rem; color:#999;">
                        <?php echo date('F j, Y \a\t H:i', strtotime($n['created_at'])); ?>
                    </div>
                </div>

                <!-- Type Badge -->
                <?php
                $label_map = ['new_order'=>'New Order','order_cancel'=>'Cancelled','new_ticket'=>'Ticket'];
                $label = $label_map[$n['type']] ?? strtoupper($n['type']);
                $bg    = notifBorderColor($n['type']);
                ?>
                <div style="flex-shrink:0;">
                    <span style="font-size:0.7rem; font-weight:700; letter-spacing:0.5px; color:<?php echo $bg; ?>; background:<?php echo notifColor($n['type']); ?>; padding:3px 10px; border-radius:50px; border:1px solid <?php echo $bg; ?>40;">
                        <?php echo $label; ?>
                    </span>
                </div>

                <!-- Unread dot -->
                <?php if (!$n['is_read']): ?>
                    <div style="width:9px; height:9px; background:#e74c3c; border-radius:50%; flex-shrink:0;"></div>
                <?php else: ?>
                    <div style="width:9px; height:9px; flex-shrink:0;"></div>
                <?php endif; ?>

                <div style="color:#ccc; font-size:0.9rem; flex-shrink:0;">→</div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="padding:5rem; text-align:center; color:#999;">
            <div style="font-size:3.5rem; margin-bottom:1rem;">📭</div>
            <div style="font-size:1rem; font-weight:600;">No notifications to show</div>
            <div style="font-size:0.85rem; margin-top:0.4rem; color:#bbb;">New orders, cancellations, and tickets will appear here.</div>
        </div>
    <?php endif; ?>
</div>

<style>
.notif-row.unread { background: rgba(66,66,66,0.03); }
.notif-row:hover  { background: #f9f9f9 !important; }
</style>

<?php require_once '../includes/staff_footer.php'; ?>
