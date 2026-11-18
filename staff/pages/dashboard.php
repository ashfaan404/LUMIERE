<?php
// staff/pages/dashboard.php
require_once '../includes/staff_header.php';

// ── Stats ──
$pending_orders  = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Placed'")->fetch_row()[0] ?? 0);
$processing      = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('Confirmed','Packed','Shipped')")->fetch_row()[0] ?? 0);
$delivered_today = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Delivered' AND DATE(order_date)=CURDATE()")->fetch_row()[0] ?? 0);
$support_tickets = intval($conn->query("SELECT COUNT(*) FROM support_tickets WHERE status='Open'")->fetch_row()[0] ?? 0);
$total_reviews   = intval($conn->query("SELECT COUNT(*) FROM reviews")->fetch_row()[0] ?? 0);
$cancelled_today = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Cancelled' AND DATE(order_date)=CURDATE()")->fetch_row()[0] ?? 0);

// ── Recent Orders ──
$recent_orders = $conn->query("
    SELECT o.*, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
    LIMIT 10
");
$orders_rows = [];
if ($recent_orders) { while ($r = $recent_orders->fetch_assoc()) $orders_rows[] = $r; }

// ── Recent Tickets ──
$recent_tickets = $conn->query("
    SELECT t.*, u.full_name
    FROM support_tickets t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.status = 'Open'
    ORDER BY t.created_at DESC
    LIMIT 5
");
$ticket_rows = [];
if ($recent_tickets) { while ($r = $recent_tickets->fetch_assoc()) $ticket_rows[] = $r; }

// ── Weekly order counts for chart (all orders) ──
$week_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date   = date('Y-m-d', strtotime("-$i days"));
    $day    = date('D', strtotime($date));
    $dt_lbl = date('d M', strtotime($date));
    $cnt    = intval($conn->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date)='$date'")->fetch_row()[0] ?? 0);
    $week_data[] = ['day'=>$day, 'date'=>$dt_lbl, 'count'=>$cnt, 'is_today'=>($i===0)];
}
$max_cnt = max(array_column($week_data,'count') ?: [1]);
if ($max_cnt <= 0) $max_cnt = 1;
?>

<!-- ══ STAT CARDS ══ -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="color:#f9a825;background:rgba(249,168,37,0.1);">⏳</div>
        <div class="stat-info">
            <h4>Pending Orders</h4>
            <div class="value"><?php echo $pending_orders; ?></div>
            <div style="font-size:0.7rem;color:#aaa;margin-top:2px;">Awaiting confirmation</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color:#1e88e5;background:rgba(30,136,229,0.1);">🔄</div>
        <div class="stat-info">
            <h4>In Progress</h4>
            <div class="value"><?php echo $processing; ?></div>
            <div style="font-size:0.7rem;color:#aaa;margin-top:2px;">Confirmed/Packed/Shipped</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color:#43a047;background:rgba(67,160,71,0.1);">✅</div>
        <div class="stat-info">
            <h4>Delivered Today</h4>
            <div class="value"><?php echo $delivered_today; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color:#e53935;background:rgba(229,57,53,0.1);">🎫</div>
        <div class="stat-info">
            <h4>Open Tickets</h4>
            <div class="value"><?php echo $support_tickets; ?></div>
            <div style="font-size:0.7rem;color:#aaa;margin-top:2px;">Needs attention</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color:#C5A059;background:rgba(197,160,89,0.1);">⭐</div>
        <div class="stat-info">
            <h4>Total Reviews</h4>
            <div class="value"><?php echo $total_reviews; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color:#e53935;background:rgba(229,57,53,0.07);">❌</div>
        <div class="stat-info">
            <h4>Cancelled Today</h4>
            <div class="value"><?php echo $cancelled_today; ?></div>
        </div>
    </div>
</div>

<!-- ══ ORDERS + TICKETS ══ -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;margin-bottom:2rem;">

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3>📦 Recent Orders</h3>
            <a href="orders.php" class="btn btn-primary btn-sm" style="background:#424242;">View All</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders_rows)): ?>
                        <?php
                        $sc = ['Placed'=>'badge-pending','Confirmed'=>'badge-info','Packed'=>'badge-info',
                               'Shipped'=>'badge-info','Delivered'=>'badge-success','Cancelled'=>'badge-danger'];
                        foreach ($orders_rows as $row):
                            $badge = $sc[$row['order_status']] ?? 'badge-info';
                        ?>
                        <tr>
                            <td style="font-weight:700;color:#424242;">#<?php echo str_pad($row['order_id'],5,'0',STR_PAD_LEFT); ?></td>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <div style="font-size:0.7rem;color:#aaa;"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td style="font-size:0.82rem;color:#888;"><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td style="font-weight:600;"><?php echo formatPrice($row['total_amount']); ?></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo $row['order_status']; ?></span></td>
                            <td>
                                <a href="order_details.php?id=<?php echo $row['order_id']; ?>"
                                   style="color:#424242;font-size:0.8rem;font-weight:600;text-decoration:none;border:1px solid #ddd;padding:3px 10px;border-radius:6px;">
                                    View →
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;color:#bbb;padding:2rem;">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Open Tickets -->
    <div class="card">
        <div class="card-header">
            <h3>🎫 Open Tickets</h3>
            <a href="tickets.php" class="btn btn-secondary btn-sm" style="background:#f5f5f5;font-size:0.74rem;">All Tickets</a>
        </div>
        <div>
            <?php if (!empty($ticket_rows)): ?>
                <?php foreach ($ticket_rows as $t): ?>
                <div style="padding:0.9rem 1.5rem;border-bottom:1px solid #f5f5f5;">
                    <div style="font-weight:600;font-size:0.88rem;color:#222;margin-bottom:2px;">
                        <?php echo htmlspecialchars($t['subject']); ?>
                    </div>
                    <div style="font-size:0.75rem;color:#aaa;display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                        <span><?php echo htmlspecialchars($t['full_name']); ?></span>
                        <span style="background:#fff3cd;color:#856404;padding:2px 8px;border-radius:50px;font-size:0.68rem;font-weight:700;">
                            <?php echo htmlspecialchars($t['ticket_type']); ?>
                        </span>
                    </div>
                    <div style="font-size:0.7rem;color:#ccc;margin-top:3px;"><?php echo date('d M Y, H:i', strtotime($t['created_at'])); ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center;padding:2.5rem;color:#bbb;">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">✅</div>
                    <div style="font-size:0.85rem;">No open tickets — all clear!</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ WEEKLY ORDER CHART ══ -->
<div class="card">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:0.8rem;">
            <span style="font-size:1.2rem;">📊</span>
            <div>
                <h3 style="margin:0;">Orders This Week</h3>
                <p style="margin:2px 0 0;font-size:0.75rem;color:#aaa;">Daily order count — last 7 days</p>
            </div>
        </div>
        <a href="notifications.php" class="btn btn-secondary btn-sm" style="background:#f5f5f5;font-size:0.75rem;">View Notifications</a>
    </div>
    <div style="padding:2rem 2rem 1.2rem;">
        <?php $total_week = array_sum(array_column($week_data,'count')); ?>
        <?php if ($total_week === 0): ?>
            <div style="text-align:center;padding:3rem;color:#bbb;">
                <div style="font-size:2.5rem;margin-bottom:0.5rem;">📉</div>
                <div>No orders placed in the last 7 days.</div>
            </div>
        <?php else: ?>
        <div style="display:flex;align-items:flex-end;gap:1rem;height:200px;background:#fafafa;padding:30px 20px 16px;border-radius:14px;border:1px solid #f0f0f0;">
            <?php foreach ($week_data as $entry):
                $cnt    = $entry['count'];
                $h      = $max_cnt > 0 ? ($cnt / $max_cnt) * 100 : 0;
                $bar_h  = $cnt > 0 ? max($h, 5) : 2;
                $today  = $entry['is_today'];
            ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;gap:0.5rem;">
                <div style="flex:1;width:100%;display:flex;align-items:flex-end;justify-content:center;position:relative;">
                    <?php if ($cnt > 0): ?>
                    <div style="position:absolute;top:-22px;font-size:0.7rem;font-weight:700;color:<?php echo $today?'#C5A059':'#424242'; ?>;">
                        <?php echo $cnt; ?>
                    </div>
                    <?php endif; ?>
                    <div style="width:42px;height:<?php echo $bar_h; ?>%;
                                background:<?php echo $today?'linear-gradient(180deg,#C5A059,#a68443)':'linear-gradient(180deg,#424242,#212121)'; ?>;
                                border-radius:8px 8px 3px 3px;
                                box-shadow:0 3px 10px rgba(0,0,0,0.1);
                                opacity:<?php echo $cnt>0?'1':'0.12'; ?>;
                                transition:all 0.4s ease;"
                         title="<?php echo $entry['day'].' '.$entry['date'].': '.$cnt.' order'.($cnt!=1?'s':''); ?>">
                    </div>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:<?php echo $today?'#C5A059':'#aaa'; ?>;">
                    <?php echo $entry['day']; ?>
                </div>
                <div style="font-size:0.62rem;color:#ccc;"><?php echo $entry['date']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:1rem;display:flex;justify-content:center;gap:2rem;">
            <span style="font-size:0.78rem;color:#888;display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;background:#424242;border-radius:2px;display:inline-block;"></span>
                Orders / Day
            </span>
            <span style="font-size:0.78rem;color:#888;display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;background:#C5A059;border-radius:2px;display:inline-block;"></span>
                Today
            </span>
            <span style="font-size:0.78rem;color:#888;font-weight:600;">Total this week: <?php echo $total_week; ?> orders</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/staff_footer.php'; ?>
