<?php
// admin/pages/dashboard.php
require_once '../includes/admin_header.php';

// ── Stats (safe with null coalescing) ──
$total_products  = intval($conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] ?? 0);
$total_orders    = intval($conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0] ?? 0);
$total_users     = intval($conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0);
$paid_revenue    = floatval($conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='Paid'")->fetch_row()[0] ?? 0);
$all_revenue     = floatval($conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE order_status NOT IN ('Cancelled')")->fetch_row()[0] ?? 0);
$pending_orders  = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Placed'")->fetch_row()[0] ?? 0);
$support_tickets = intval($conn->query("SELECT COUNT(*) FROM support_tickets WHERE status='Open'")->fetch_row()[0] ?? 0);
$delivered_today = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Delivered' AND DATE(order_date)=CURDATE()")->fetch_row()[0] ?? 0);

// ── Recent Data ──
$recent_orders  = $conn->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY o.order_date DESC LIMIT 8");
$recent_users   = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 6");

// ── Weekly Chart: orders per day (ALL statuses, not just Paid) ──
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date     = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime($date));
    $day_date = date('d M', strtotime($date));

    $res_rev  = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(order_date)='$date' AND order_status != 'Cancelled'")->fetch_row()[0] ?? 0;
    $res_cnt  = $conn->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date)='$date'")->fetch_row()[0] ?? 0;

    $chart_data[] = [
        'day'     => $day_name,
        'date'    => $day_date,
        'revenue' => floatval($res_rev),
        'count'   => intval($res_cnt),
        'is_today'=> ($i === 0)
    ];
}
$max_rev = max(array_column($chart_data, 'revenue') ?: [1]);
if ($max_rev <= 0) $max_rev = 100; // prevent division by zero

// ── Order Status Breakdown ──
$statuses_list = ['Placed','Confirmed','Packed','Shipped','Delivered','Cancelled'];
$order_status_counts = [];
foreach ($statuses_list as $s) {
    $order_status_counts[$s] = intval($conn->query("SELECT COUNT(*) FROM orders WHERE order_status='$s'")->fetch_row()[0] ?? 0);
}
?>

<!-- ══ STAT CARDS ══ -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🛍️</div>
        <div class="stat-info"><h4>Products</h4><div class="value"><?php echo $total_products; ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info"><h4>Total Orders</h4><div class="value"><?php echo $total_orders; ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info"><h4>Users</h4><div class="value"><?php echo $total_users; ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <h4>Total Revenue</h4>
            <div class="value"><?php echo formatPrice($all_revenue); ?></div>
            <div style="font-size:0.7rem;color:#aaa;margin-top:2px;">Paid: <?php echo formatPrice($paid_revenue); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info"><h4>Pending</h4><div class="value"><?php echo $pending_orders; ?></div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎫</div>
        <div class="stat-info"><h4>Open Tickets</h4><div class="value"><?php echo $support_tickets; ?></div></div>
    </div>
</div>

<!-- ══ ORDERS TABLE + USERS ══ -->
<div style="display:grid; grid-template-columns:2fr 1fr; gap:2rem; margin-bottom:2rem;">

    <!-- Latest Orders -->
    <div class="card">
        <div class="card-header">
            <h3>Latest Orders</h3>
            <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Pay</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                    <?php while ($row = $recent_orders->fetch_assoc()):
                        $sc = ['Placed'=>'badge-pending','Confirmed'=>'badge-info','Packed'=>'badge-info',
                               'Shipped'=>'badge-info','Delivered'=>'badge-success','Cancelled'=>'badge-danger'];
                        $badge = $sc[$row['order_status']] ?? 'badge-info';
                    ?>
                    <tr onclick="location='order_details.php?id=<?php echo $row['order_id']; ?>'" style="cursor:pointer;">
                        <td style="font-weight:700;color:var(--admin-navy);">#<?php echo str_pad($row['order_id'],5,'0',STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td style="font-size:0.82rem;color:#888;"><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                        <td style="font-weight:600;"><?php echo formatPrice($row['total_amount']); ?></td>
                        <td>
                            <span style="font-size:0.7rem;padding:2px 8px;border-radius:50px;font-weight:700;
                                background:<?php echo $row['payment_status']==='Paid'?'#e8f5e9':($row['payment_status']==='Failed'?'#fce4ec':'#fff3cd'); ?>;
                                color:<?php echo $row['payment_status']==='Paid'?'#2e7d32':($row['payment_status']==='Failed'?'#c62828':'#856404'); ?>;">
                                <?php echo htmlspecialchars($row['payment_status']); ?>
                            </span>
                        </td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo $row['order_status']; ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;color:#bbb;padding:2rem;">No orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Right Column: Recent Users + Status Summary -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        <!-- Recent Users -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Users</h3>
                <a href="users.php" class="btn btn-secondary btn-sm" style="background:#f5f5f5;font-size:0.75rem;">View All</a>
            </div>
            <div style="padding:0.5rem 0;">
                <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                    <?php while ($user = $recent_users->fetch_assoc()): ?>
                    <div style="padding:0.75rem 1.5rem; border-bottom:1px solid #f5f5f5; display:flex; align-items:center; gap:0.8rem;">
                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--admin-navy),#2c3a67);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;flex-shrink:0;">
                            <?php echo strtoupper(substr($user['full_name'],0,1)); ?>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.88rem;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div style="font-size:0.72rem;color:#aaa;"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center;color:#bbb;padding:2rem;font-size:0.85rem;">No users yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Status Summary -->
        <div class="card">
            <div class="card-header"><h3>📋 Order Status</h3></div>
            <div style="padding:1rem 1.5rem;">
                <?php
                $total_all = array_sum($order_status_counts) ?: 1;
                $sc_colors = [
                    'Placed'   =>['bar'=>'#f9a825','txt'=>'#856404'],
                    'Confirmed'=>['bar'=>'#1e88e5','txt'=>'#0d47a1'],
                    'Packed'   =>['bar'=>'#8e24aa','txt'=>'#4a148c'],
                    'Shipped'  =>['bar'=>'#00897b','txt'=>'#004d40'],
                    'Delivered'=>['bar'=>'#43a047','txt'=>'#1b5e20'],
                    'Cancelled'=>['bar'=>'#e53935','txt'=>'#b71c1c'],
                ];
                foreach ($order_status_counts as $s => $cnt):
                    $pct = round(($cnt / $total_all) * 100);
                    $c   = $sc_colors[$s] ?? ['bar'=>'#999','txt'=>'#333'];
                ?>
                <div style="margin-bottom:0.9rem;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span style="font-size:0.8rem;font-weight:600;color:<?php echo $c['txt']; ?>;"><?php echo $s; ?></span>
                        <span style="font-size:0.75rem;color:#aaa;"><?php echo $cnt; ?></span>
                    </div>
                    <div style="background:#f0f0f0;border-radius:50px;height:6px;">
                        <div style="width:<?php echo $pct; ?>%;background:<?php echo $c['bar']; ?>;height:100%;border-radius:50px;transition:width 1s ease;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ REVENUE CHART ══ -->
<div class="card">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:0.8rem;">
            <span style="font-size:1.2rem;">📈</span>
            <div>
                <h3 style="margin:0;">Revenue Trend — Last 7 Days</h3>
                <p style="margin:2px 0 0;font-size:0.75rem;color:#aaa;">Excludes cancelled orders</p>
            </div>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <a href="reports.php" class="btn btn-primary btn-sm">📊 Full Reports</a>
            <a href="payment_history.php" class="btn btn-secondary btn-sm" style="background:#f5f5f5;">Payments</a>
        </div>
    </div>
    <div style="padding:2.5rem 2rem 1.5rem;">
        <?php
        $has_any = array_sum(array_column($chart_data,'revenue')) > 0;
        $has_orders = array_sum(array_column($chart_data,'count')) > 0;
        ?>

        <?php if (!$has_orders): ?>
        <!-- No orders at all this week -->
        <div style="text-align:center;padding:4rem;color:#bbb;">
            <div style="font-size:3rem;margin-bottom:0.5rem;">📉</div>
            <div style="font-size:1rem;font-weight:600;color:#888;">No orders in the last 7 days</div>
            <div style="font-size:0.8rem;margin-top:0.4rem;">Place some test orders to see the chart populate.</div>
        </div>
        <?php else: ?>

        <!-- Chart Bars -->
        <div style="display:flex;align-items:flex-end;gap:1rem;height:280px;background:linear-gradient(180deg,#fff 0%,#fdfbf7 100%);padding:40px 20px 20px;border-radius:16px;border:1px solid #f0f0f0;">
            <?php foreach ($chart_data as $entry):
                $rev    = $entry['revenue'];
                $cnt    = $entry['count'];
                $height = $max_rev > 0 ? ($rev / $max_rev) * 100 : 0;
                $bar_h  = $cnt > 0 ? max($height, 4) : 2; // always show tiny stub if there's an order
                $today  = $entry['is_today'];
            ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;gap:0.6rem;">
                <!-- Bar area -->
                <div style="flex:1;width:100%;display:flex;align-items:flex-end;justify-content:center;position:relative;">

                    <!-- Revenue label -->
                    <?php if ($cnt > 0): ?>
                    <div style="position:absolute;top:-28px;left:50%;transform:translateX(-50%);
                                font-size:0.65rem;font-weight:700;white-space:nowrap;
                                color:<?php echo $today ? '#c5a059' : 'var(--admin-navy)'; ?>;">
                        <?php echo $rev > 0 ? formatPrice($rev) : $cnt.' order'.($cnt>1?'s':''); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Bar -->
                    <div style="width:46px;
                                height:<?php echo $bar_h; ?>%;
                                background:<?php echo $today
                                    ? 'linear-gradient(180deg,#C5A059 0%,#a68443 100%)'
                                    : 'linear-gradient(180deg,var(--admin-navy) 0%,#1a2340 100%)'; ?>;
                                border-radius:10px 10px 4px 4px;
                                box-shadow:0 4px 12px rgba(0,0,0,0.1);
                                transition:all 0.5s ease;
                                opacity:<?php echo $cnt > 0 ? '1' : '0.15'; ?>;"
                         onmouseover="this.style.transform='scaleY(1.04)'"
                         onmouseout="this.style.transform='scaleY(1)'"
                         title="<?php echo $entry['day'].' '.$entry['date'].': '.formatPrice($rev).' ('.$cnt.' orders)'; ?>">
                    </div>
                </div>

                <!-- Day label -->
                <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;
                            color:<?php echo $today ? 'var(--admin-gold)' : '#aaa'; ?>;">
                    <?php echo $entry['day']; ?>
                </div>
                <!-- Date -->
                <div style="font-size:0.65rem;color:#ccc;"><?php echo $entry['date']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Legend -->
        <div style="margin-top:1.2rem;display:flex;justify-content:center;align-items:center;gap:2rem;">
            <span style="font-size:0.78rem;color:#888;display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;background:var(--admin-navy);border-radius:2px;display:inline-block;"></span>
                Past 6 Days Revenue
            </span>
            <span style="font-size:0.78rem;color:#888;display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;background:#C5A059;border-radius:2px;display:inline-block;"></span>
                Today
            </span>
            <?php if (!$has_any && $has_orders): ?>
            <span style="font-size:0.78rem;color:#e65100;font-weight:600;">
                ⚠️ Orders exist but payment status is not "Paid" — mark as Paid to show revenue.
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
