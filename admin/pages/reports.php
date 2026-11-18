<?php
// admin/pages/reports.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// ═══════════════════════════════════════════
// CSV EXPORT — must happen before any HTML output
// ═══════════════════════════════════════════
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exp_range = isset($_GET['range']) ? $_GET['range'] : '30';
    $exp_from  = isset($_GET['from'])  ? $_GET['from']  : '';
    $exp_to    = isset($_GET['to'])    ? $_GET['to']    : '';
    $exp_type  = isset($_GET['type'])  ? $_GET['type']  : 'orders';

    if ($exp_from && $exp_to) {
        $df = $exp_from . ' 00:00:00';
        $dt = $exp_to   . ' 23:59:59';
    } else {
        $d  = in_array(intval($exp_range),[7,30,90,365]) ? intval($exp_range) : 30;
        $df = date('Y-m-d 00:00:00', strtotime("-$d days"));
        $dt = date('Y-m-d 23:59:59');
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="lumiere_' . $exp_type . '_report_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    $out = fopen('php://output', 'w');
    // UTF-8 BOM so Excel opens correctly
    fwrite($out, "\xEF\xBB\xBF");

    if ($exp_type === 'orders') {
        fputcsv($out, ['Order ID','Customer Name','Email','Phone','Order Date','Payment Method','Payment Status','Order Status','Total Amount (Rs.)','Delivery Address']);
        $rows = $conn->query("
            SELECT o.order_id, u.full_name, u.email, u.phone,
                   o.order_date, o.payment_method, o.payment_status,
                   o.order_status, o.total_amount, o.delivery_address
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            WHERE o.order_date BETWEEN '$df' AND '$dt'
            ORDER BY o.order_date DESC
        ");
        while ($r = $rows->fetch_assoc()) {
            fputcsv($out, [
                '#' . str_pad($r['order_id'],6,'0',STR_PAD_LEFT),
                $r['full_name'], $r['email'], $r['phone'],
                date('Y-m-d H:i', strtotime($r['order_date'])),
                $r['payment_method'], $r['payment_status'], $r['order_status'],
                number_format($r['total_amount'],2), $r['delivery_address']
            ]);
        }
    } elseif ($exp_type === 'products') {
        fputcsv($out, ['Rank','Product Name','Brand','Units Sold','Revenue (Rs.)']);
        $rows = $conn->query("
            SELECT p.product_name, p.brand,
                   SUM(oi.quantity) as total_qty,
                   SUM(oi.subtotal) as total_rev
            FROM order_items oi
            JOIN orders o   ON oi.order_id   = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.order_date BETWEEN '$df' AND '$dt'
            GROUP BY oi.product_id, p.product_name, p.brand
            ORDER BY total_qty DESC
        ");
        $rank = 1;
        while ($r = $rows->fetch_assoc()) {
            fputcsv($out, [$rank++, $r['product_name'], $r['brand'], $r['total_qty'], number_format($r['total_rev'],2)]);
        }
    } elseif ($exp_type === 'customers') {
        fputcsv($out, ['Customer Name','Email','Phone','Total Orders','Total Spent (Rs.)','Registered On']);
        $rows = $conn->query("
            SELECT u.full_name, u.email, u.phone, u.created_at,
                   COUNT(o.order_id) as total_orders,
                   COALESCE(SUM(o.total_amount),0) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.user_id = o.user_id
                               AND o.order_date BETWEEN '$df' AND '$dt'
            GROUP BY u.user_id, u.full_name, u.email, u.phone, u.created_at
            ORDER BY total_spent DESC
        ");
        while ($r = $rows->fetch_assoc()) {
            fputcsv($out, [
                $r['full_name'], $r['email'], $r['phone'],
                $r['total_orders'], number_format($r['total_spent'],2),
                date('Y-m-d', strtotime($r['created_at']))
            ]);
        }
    }
    fclose($out);
    exit();
}

// ═══════════════════════════════════════════
// NORMAL PAGE DISPLAY
// ═══════════════════════════════════════════
require_once '../includes/admin_header.php';

// ═══════════════════════════════════════════
// NORMAL PAGE DISPLAY
// ═══════════════════════════════════════════

// Date Range
$range       = isset($_GET['range']) ? $_GET['range'] : '30';
$custom_from = isset($_GET['from'])  ? $_GET['from']  : '';
$custom_to   = isset($_GET['to'])    ? $_GET['to']    : '';

if ($custom_from && $custom_to) {
    $date_from   = $custom_from . ' 00:00:00';
    $date_to     = $custom_to   . ' 23:59:59';
    $range_label = date('d M Y', strtotime($custom_from)) . ' – ' . date('d M Y', strtotime($custom_to));
} else {
    $days        = in_array(intval($range),[7,30,90,365]) ? intval($range) : 30;
    $date_from   = date('Y-m-d 00:00:00', strtotime("-$days days"));
    $date_to     = date('Y-m-d 23:59:59');
    $range_label = "Last $days Days";
}

// Build base URL for export links
$base_params = http_build_query(array_filter(['range'=>$range,'from'=>$custom_from,'to'=>$custom_to]));

// ── KPI Stats (safe null-coalescing on all) ──
$r1 = $conn->query("SELECT COALESCE(SUM(total_amount),0) as v FROM orders WHERE payment_status='Paid' AND order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$r1b= $conn->query("SELECT COALESCE(SUM(total_amount),0) as v FROM orders WHERE order_status!='Cancelled' AND order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$r2 = $conn->query("SELECT COUNT(*) as v FROM orders WHERE order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$r3 = $conn->query("SELECT COUNT(*) as v FROM orders WHERE payment_status='Paid' AND order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$r4 = $conn->query("SELECT COUNT(*) as v FROM orders WHERE order_status='Cancelled' AND order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$r5 = $conn->query("SELECT COUNT(DISTINCT user_id) as v FROM orders WHERE order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$r6 = $conn->query("SELECT COUNT(*) as v FROM users WHERE created_at BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();

$paid_revenue  = floatval($r1['v']  ?? 0);
$total_revenue = floatval($r1b['v'] ?? 0); // all non-cancelled
$total_orders  = intval($r2['v']    ?? 0);
$paid_orders   = intval($r3['v']    ?? 0);
$cancelled     = intval($r4['v']    ?? 0);
$new_customers = intval($r5['v']    ?? 0);
$new_reg       = intval($r6['v']    ?? 0);
$avg_order_val = $total_orders > 0 ? $total_revenue / max($total_orders,1) : 0;

// ── Revenue + Orders by Day (ALL orders, not just Paid) ──
$daily_data = [];
if ($custom_from && $custom_to) {
    $diff_days = max(1, intval((strtotime($custom_to) - strtotime($custom_from)) / 86400));
    $diff_days = min($diff_days, 90);
    for ($i = 0; $i <= $diff_days; $i++) {
        $d    = date('Y-m-d', strtotime($custom_from . " +$i days"));
        $res  = $conn->query("SELECT COALESCE(SUM(total_amount),0) as v FROM orders WHERE order_status!='Cancelled' AND DATE(order_date)='$d'")->fetch_assoc();
        $res2 = $conn->query("SELECT COUNT(*) as v FROM orders WHERE DATE(order_date)='$d'")->fetch_assoc();
        $daily_data[] = ['date'=>$d,'label'=>date('d M',strtotime($d)),'revenue'=>floatval($res['v']),'count'=>intval($res2['v'])];
    }
} else {
    for ($i = $days - 1; $i >= 0; $i--) {
        $d    = date('Y-m-d', strtotime("-$i days"));
        $lbl  = $days <= 14 ? date('D d', strtotime($d)) : date('d M', strtotime($d));
        $res  = $conn->query("SELECT COALESCE(SUM(total_amount),0) as v FROM orders WHERE order_status!='Cancelled' AND DATE(order_date)='$d'")->fetch_assoc();
        $res2 = $conn->query("SELECT COUNT(*) as v FROM orders WHERE DATE(order_date)='$d'")->fetch_assoc();
        $daily_data[] = ['date'=>$d,'label'=>$lbl,'revenue'=>floatval($res['v']),'count'=>intval($res2['v'])];
    }
}
$rev_vals = array_column($daily_data, 'revenue');
$max_rev  = (!empty($rev_vals) && max($rev_vals) > 0) ? max($rev_vals) : 1;

// ── Orders by Status ──
$statuses = ['Placed','Confirmed','Packed','Shipped','Delivered','Cancelled'];
$status_counts = [];
foreach ($statuses as $s) {
    $res = $conn->query("SELECT COUNT(*) as v FROM orders WHERE order_status='$s' AND order_date BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
    $status_counts[$s] = intval($res['v']);
}

// ── Top-Selling Products (fixed GROUP BY) ──
$top_products_res = $conn->query("
    SELECT p.product_id, p.product_name, p.brand,
           SUM(oi.quantity) as total_qty,
           SUM(oi.subtotal) as total_rev
    FROM order_items oi
    JOIN orders o   ON oi.order_id   = o.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.order_date BETWEEN '$date_from' AND '$date_to'
    GROUP BY oi.product_id, p.product_name, p.brand
    ORDER BY total_qty DESC
    LIMIT 10
");
$top_rows = [];
if ($top_products_res) {
    while ($row = $top_products_res->fetch_assoc()) $top_rows[] = $row;
}
$max_qty = !empty($top_rows) ? max(array_column($top_rows,'total_qty')) : 1;

// ── Payment Methods ──
$pm_res = $conn->query("
    SELECT payment_method, COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as rev
    FROM orders
    WHERE order_date BETWEEN '$date_from' AND '$date_to'
    GROUP BY payment_method
    ORDER BY cnt DESC
");
$pm_rows = [];
if ($pm_res) { while ($row = $pm_res->fetch_assoc()) $pm_rows[] = $row; }
$pm_total = array_sum(array_column($pm_rows,'cnt')) ?: 1;

// ── Recent Orders (for data table) ──
$recent_orders_res = $conn->query("
    SELECT o.order_id, u.full_name, u.email,
           o.order_date, o.total_amount, o.payment_method,
           o.payment_status, o.order_status
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_date BETWEEN '$date_from' AND '$date_to'
    ORDER BY o.order_date DESC
    LIMIT 20
");
$recent_orders = [];
if ($recent_orders_res) { while ($row = $recent_orders_res->fetch_assoc()) $recent_orders[] = $row; }

// ── Top Customer ──
$top_cust_res = $conn->query("
    SELECT u.full_name, COUNT(o.order_id) as cnt, SUM(o.total_amount) as total
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_date BETWEEN '$date_from' AND '$date_to'
    GROUP BY o.user_id, u.full_name
    ORDER BY total DESC
    LIMIT 1
");
$top_cust = $top_cust_res ? $top_cust_res->fetch_assoc() : null;

// ── Returning Customers ──
$ret_res  = $conn->query("SELECT COUNT(*) as v FROM (SELECT user_id FROM orders WHERE order_date BETWEEN '$date_from' AND '$date_to' GROUP BY user_id HAVING COUNT(*) > 1) t")->fetch_assoc();
$returning = intval($ret_res['v']);
?>

<!-- ═══ PAGE HEADER ═══ -->
<div style="margin-bottom:2rem; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 style="margin:0; font-size:1.5rem; color:var(--admin-navy);">📊 Sales Reports &amp; Analytics</h2>
        <p style="margin:4px 0 0; font-size:0.85rem; color:#888;">Business insights — <strong><?php echo htmlspecialchars($range_label); ?></strong></p>
    </div>

    <!-- Export Buttons -->
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
        <a href="reports.php?export=csv&type=orders&<?php echo $base_params; ?>"
           style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; background:#2e7d32; color:#fff; border-radius:8px; font-size:0.82rem; font-weight:600; text-decoration:none;">
            ⬇️ Orders CSV
        </a>
        <a href="reports.php?export=csv&type=products&<?php echo $base_params; ?>"
           style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; background:#1565c0; color:#fff; border-radius:8px; font-size:0.82rem; font-weight:600; text-decoration:none;">
            ⬇️ Products CSV
        </a>
        <a href="reports.php?export=csv&type=customers&<?php echo $base_params; ?>"
           style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; background:#6a1b9a; color:#fff; border-radius:8px; font-size:0.82rem; font-weight:600; text-decoration:none;">
            ⬇️ Customers CSV
        </a>
    </div>
</div>

<!-- ═══ DATE RANGE FILTER ═══ -->
<div style="margin-bottom:2rem; display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center; background:#fdfbf7; padding:1rem 1.4rem; border-radius:12px; border:1px solid #f0ebe2;">
    <span style="font-size:0.8rem; font-weight:700; color:#888; margin-right:0.5rem;">Filter:</span>
    <?php foreach ([7=>'7 Days', 30=>'30 Days', 90=>'90 Days', 365=>'1 Year'] as $d => $lbl): ?>
        <a href="reports.php?range=<?php echo $d; ?>"
           style="padding:0.4rem 0.95rem; border-radius:50px; font-size:0.82rem; font-weight:600; text-decoration:none;
                  background:<?php echo (!$custom_from && $range == $d) ? 'var(--admin-navy)' : '#fff'; ?>;
                  color:<?php echo (!$custom_from && $range == $d) ? '#fff' : '#555'; ?>;
                  border:1px solid <?php echo (!$custom_from && $range == $d) ? 'var(--admin-navy)' : '#ddd'; ?>;">
            <?php echo $lbl; ?>
        </a>
    <?php endforeach; ?>
    <form method="GET" style="display:flex; align-items:center; gap:0.4rem; margin-left:0.5rem;">
        <input type="date" name="from" value="<?php echo htmlspecialchars($custom_from); ?>"
               style="padding:0.35rem 0.6rem; border:1px solid #ddd; border-radius:8px; font-size:0.82rem;">
        <span style="color:#aaa; font-size:0.8rem;">to</span>
        <input type="date" name="to" value="<?php echo htmlspecialchars($custom_to); ?>"
               style="padding:0.35rem 0.6rem; border:1px solid #ddd; border-radius:8px; font-size:0.82rem;">
        <button type="submit"
                style="padding:0.4rem 0.9rem; background:var(--admin-gold); color:#fff; border:none; border-radius:8px; font-size:0.82rem; font-weight:600; cursor:pointer;">
            Apply
        </button>
    </form>
</div>

<!-- ═══ KPI CARDS ═══ -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(155px,1fr)); gap:1rem; margin-bottom:2rem;">
<?php
$kpis = [
    ['icon'=>'💰', 'label'=>'Total Revenue',    'value'=>formatPrice($total_revenue), 'color'=>'#b8860b','bg'=>'#fdf9f0','sub'=>'Excl. cancelled'],
    ['icon'=>'💳', 'label'=>'Paid Revenue',     'value'=>formatPrice($paid_revenue),  'color'=>'#2e7d32','bg'=>'#e8f5e9','sub'=>'Confirmed paid'],
    ['icon'=>'📦', 'label'=>'Total Orders',     'value'=>$total_orders,               'color'=>'#1565c0','bg'=>'#e3f2fd','sub'=>'In period'],
    ['icon'=>'❌', 'label'=>'Cancelled',        'value'=>$cancelled,                  'color'=>'#c62828','bg'=>'#fce4ec','sub'=>''],
    ['icon'=>'👥', 'label'=>'Active Customers', 'value'=>$new_customers,              'color'=>'#6a1b9a','bg'=>'#f3e5f5','sub'=>'Placed orders'],
    ['icon'=>'🆕', 'label'=>'New Sign-ups',     'value'=>$new_reg,                   'color'=>'#00695c','bg'=>'#e0f2f1','sub'=>'Registered'],
    ['icon'=>'🧾', 'label'=>'Avg Order Value',  'value'=>formatPrice($avg_order_val), 'color'=>'#37474f','bg'=>'#f5f5f5','sub'=>'Per order'],
    ['icon'=>'🔄', 'label'=>'Repeat Customers', 'value'=>$returning,                 'color'=>'#e65100','bg'=>'#fff3e0','sub'=>'>1 order'],
];
foreach ($kpis as $k): ?>
<div style="background:<?php echo $k['bg']; ?>; border:1px solid <?php echo $k['color']; ?>33; border-radius:14px; padding:1.3rem 1.1rem;">
    <div style="font-size:1.6rem; margin-bottom:0.5rem;"><?php echo $k['icon']; ?></div>
    <div style="font-size:0.68rem; font-weight:700; color:#aaa; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;"><?php echo $k['label']; ?></div>
    <div style="font-size:1.25rem; font-weight:800; color:<?php echo $k['color']; ?>;"><?php echo $k['value']; ?></div>
    <?php if (!empty($k['sub'])): ?>
    <div style="font-size:0.65rem;color:#bbb;margin-top:3px;"><?php echo $k['sub']; ?></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<!-- ═══ REVENUE CHART ═══ -->
<div class="card" style="margin-bottom:2rem;">
    <div class="card-header">
        <div style="display:flex; align-items:center; gap:0.8rem;">
            <span style="font-size:1.2rem;">📈</span>
            <h3 style="margin:0;">Revenue Trend — <?php echo htmlspecialchars($range_label); ?></h3>
        </div>
        <a href="reports.php?export=csv&type=orders&<?php echo $base_params; ?>"
           style="font-size:0.78rem; color:#2e7d32; text-decoration:none; font-weight:600; border:1px solid #2e7d32; padding:0.3rem 0.8rem; border-radius:6px;">
            ⬇️ Export CSV
        </a>
    </div>
    <div style="padding:2rem 2rem 1rem; overflow-x:auto;">
        <?php
        $total_week_orders  = array_sum(array_column($daily_data,'count'));
        $total_week_revenue = array_sum(array_column($daily_data,'revenue'));
        ?>
        <?php if ($total_week_orders == 0): ?>
            <div style="text-align:center; padding:4rem; color:#bbb;">
                <div style="font-size:3rem; margin-bottom:0.5rem;">📉</div>
                <div style="font-size:1rem;font-weight:600;">No orders in this date range.</div>
                <div style="font-size:0.8rem;margin-top:0.3rem;">Try selecting a wider date range.</div>
            </div>
        <?php else: ?>
        <div style="display:flex; align-items:flex-end; gap:<?php echo count($daily_data)>30?'3px':'10px'; ?>; height:200px;
                    min-width:<?php echo max(500, count($daily_data)*26); ?>px; padding-bottom:4px;">
            <?php foreach ($daily_data as $d):
                $h       = $max_rev > 0 ? ($d['revenue'] / $max_rev) * 100 : 0;
                $cnt     = $d['count'];
                $bar_h   = $cnt > 0 ? max($h, 5) : 0;
                $isToday = ($d['date'] === date('Y-m-d'));
            ?>
            <div title="<?php echo htmlspecialchars($d['label']); ?>: <?php echo formatPrice($d['revenue']); ?> (<?php echo $cnt; ?> orders)"
                 style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; min-width:<?php echo count($daily_data)>30?'8':'20'; ?>px; position:relative;">
                <div style="width:100%; flex:1; position:relative; display:flex; align-items:flex-end; justify-content:center;">
                    <?php if ($cnt > 0): ?>
                    <div style="position:absolute;top:-22px;left:50%;transform:translateX(-50%);font-size:0.58rem;font-weight:700;white-space:nowrap;color:<?php echo $isToday?'#c5a059':'var(--admin-navy)'; ?>;">
                        <?php echo $d['revenue'] > 0 ? formatPrice($d['revenue']) : $cnt.' order'.($cnt!=1?'s':''); ?>
                    </div>
                    <?php endif; ?>
                    <div style="width:100%; height:<?php echo $bar_h; ?>%;
                                background:<?php echo $isToday ? 'linear-gradient(180deg,#c5a059,#a68443)' : 'linear-gradient(180deg,var(--admin-navy),#1a2340)'; ?>;
                                border-radius:6px 6px 2px 2px;
                                opacity:<?php echo $cnt>0?'1':'0.1'; ?>;
                                min-height:<?php echo $cnt>0?'4px':'0'; ?>;
                                transition:opacity 0.2s;"
                         onmouseover="this.style.opacity='0.75'" onmouseout="this.style.opacity='<?php echo $cnt>0?1:0.1; ?>'"></div>
                </div>
                <?php if (count($daily_data) <= 20 || $isToday): ?>
                <div style="font-size:0.55rem; color:<?php echo $isToday?'#c5a059':'#bbb'; ?>; white-space:nowrap; margin-top:5px;">
                    <?php echo htmlspecialchars($d['label']); ?>
                </div>
                <?php else: ?>
                <div style="height:18px;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Legend -->
        <div style="display:flex; justify-content:center; gap:2rem; margin-top:1rem; padding-top:0.5rem; border-top:1px solid #f0f0f0; flex-wrap:wrap;">
            <span style="font-size:0.78rem; color:#888; display:flex; align-items:center; gap:5px;">
                <span style="width:12px;height:12px;background:var(--admin-navy);border-radius:2px;display:inline-block;"></span> Revenue (Excl. Cancelled)
            </span>
            <span style="font-size:0.78rem; color:#888; display:flex; align-items:center; gap:5px;">
                <span style="width:12px;height:12px;background:#c5a059;border-radius:2px;display:inline-block;"></span> Today
            </span>
            <span style="font-size:0.78rem; color:#888; font-weight:600;">
                Total: <?php echo $total_week_orders; ?> orders · <?php echo formatPrice($total_week_revenue); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ STATUS + PAYMENT ═══ -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

    <!-- Orders by Status -->
    <div class="card">
        <div class="card-header"><h3>🏷️ Orders by Status</h3></div>
        <div style="padding:1.2rem 1.5rem;">
            <?php
            $total_for_pct = array_sum($status_counts) ?: 1;
            $sc = [
                'Placed'    => ['bar'=>'#f9a825','txt'=>'#856404'],
                'Confirmed' => ['bar'=>'#43a047','txt'=>'#1b5e20'],
                'Packed'    => ['bar'=>'#1e88e5','txt'=>'#0d47a1'],
                'Shipped'   => ['bar'=>'#8e24aa','txt'=>'#4a148c'],
                'Delivered' => ['bar'=>'#00897b','txt'=>'#004d40'],
                'Cancelled' => ['bar'=>'#e53935','txt'=>'#b71c1c'],
            ];
            foreach ($status_counts as $s => $cnt):
                $pct = $total_for_pct > 0 ? round(($cnt / $total_for_pct) * 100) : 0;
                $c   = $sc[$s] ?? ['bar'=>'#999','txt'=>'#333'];
            ?>
            <div style="margin-bottom:1rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <span style="font-size:0.84rem; font-weight:600; color:<?php echo $c['txt']; ?>;"><?php echo $s; ?></span>
                    <span style="font-size:0.78rem; color:#888;"><?php echo $cnt; ?> &nbsp;(<?php echo $pct; ?>%)</span>
                </div>
                <div style="background:#f0f0f0; border-radius:50px; height:7px;">
                    <div style="width:<?php echo $pct; ?>%; background:<?php echo $c['bar']; ?>; height:100%; border-radius:50px; transition:width 1s ease;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!array_sum($status_counts)): ?>
                <p style="color:#bbb; text-align:center; padding:2rem 0; margin:0;">No orders in this period.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="card">
        <div class="card-header"><h3>💳 Payment Methods</h3></div>
        <div style="padding:1.2rem 1.5rem;">
            <?php $pm_colors = ['#c5a059','#1a2340','#2e7d32','#6a1b9a']; ?>
            <?php foreach ($pm_rows as $i => $pm):
                $pct = round(($pm['cnt'] / $pm_total) * 100);
                $col = $pm_colors[$i % count($pm_colors)];
            ?>
            <div style="margin-bottom:1.2rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <span style="font-size:0.84rem; font-weight:600; color:#333;"><?php echo htmlspecialchars($pm['payment_method']); ?></span>
                    <div>
                        <span style="font-size:0.78rem; color:#888;"><?php echo $pm['cnt']; ?> orders</span>
                        <span style="font-size:0.72rem; color:#bbb; margin-left:6px;"><?php echo formatPrice($pm['rev']); ?></span>
                    </div>
                </div>
                <div style="background:#f0f0f0; border-radius:50px; height:8px;">
                    <div style="width:<?php echo $pct; ?>%; background:<?php echo $col; ?>; height:100%; border-radius:50px; transition:width 1s ease;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($pm_rows)): ?>
                <p style="color:#bbb; text-align:center; padding:2rem 0; margin:0;">No payment data in this period.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══ TOP PRODUCTS ═══ -->
<div class="card" style="margin-bottom:2rem;">
    <div class="card-header">
        <h3>🏆 Top-Selling Products</h3>
        <a href="reports.php?export=csv&type=products&<?php echo $base_params; ?>"
           style="font-size:0.78rem; color:#1565c0; text-decoration:none; font-weight:600; border:1px solid #1565c0; padding:0.3rem 0.8rem; border-radius:6px;">
            ⬇️ Export CSV
        </a>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Brand</th>
                    <th style="text-align:right;">Units Sold</th>
                    <th style="text-align:right;">Revenue</th>
                    <th style="min-width:120px;">Sales Bar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($top_rows)): ?>
                    <?php foreach ($top_rows as $ri => $p):
                        $bw = $max_qty > 0 ? round(($p['total_qty'] / $max_qty) * 100) : 0;
                    ?>
                    <tr>
                        <td style="font-weight:700; color:#c5a059;"><?php echo $ri + 1; ?></td>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($p['product_name']); ?></td>
                        <td style="color:#888; font-size:0.85rem;"><?php echo htmlspecialchars($p['brand']); ?></td>
                        <td style="text-align:right; font-weight:700;"><?php echo intval($p['total_qty']); ?></td>
                        <td style="text-align:right; color:#2e7d32; font-weight:700;"><?php echo formatPrice($p['total_rev']); ?></td>
                        <td>
                            <div style="background:#f0f0f0; border-radius:50px; height:7px;">
                                <div style="width:<?php echo $bw; ?>%; background:linear-gradient(90deg,var(--admin-navy),#2c3a67); height:100%; border-radius:50px;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#bbb; padding:3rem;">
                            📦 No product sales data found for this date range.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ RECENT ORDERS TABLE ═══ -->
<div class="card" style="margin-bottom:2rem;">
    <div class="card-header">
        <h3>📋 Recent Orders in Period</h3>
        <a href="reports.php?export=csv&type=orders&<?php echo $base_params; ?>"
           style="font-size:0.78rem; color:#2e7d32; text-decoration:none; font-weight:600; border:1px solid #2e7d32; padding:0.3rem 0.8rem; border-radius:6px;">
            ⬇️ Export All as CSV
        </a>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th style="text-align:right;">Total</th>
                    <th>Status</th>
                    <th>Pay Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_orders)): ?>
                    <?php
                    $st_colors = ['Placed'=>'badge-pending','Confirmed'=>'badge-info','Packed'=>'badge-info','Shipped'=>'badge-info','Delivered'=>'badge-success','Cancelled'=>'badge-danger'];
                    foreach ($recent_orders as $ro):
                        $sc = $st_colors[$ro['order_status']] ?? 'badge-info';
                    ?>
                    <tr onclick="window.location='order_details.php?id=<?php echo $ro['order_id']; ?>'" style="cursor:pointer;">
                        <td style="font-weight:700; color:var(--admin-navy);">#<?php echo str_pad($ro['order_id'],6,'0',STR_PAD_LEFT); ?></td>
                        <td>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($ro['full_name']); ?></div>
                            <div style="font-size:0.72rem; color:#aaa;"><?php echo htmlspecialchars($ro['email']); ?></div>
                        </td>
                        <td style="font-size:0.85rem; color:#666;"><?php echo date('d M Y, H:i', strtotime($ro['order_date'])); ?></td>
                        <td style="font-size:0.82rem;"><?php echo htmlspecialchars($ro['payment_method']); ?></td>
                        <td style="text-align:right; font-weight:700; color:#2e7d32;"><?php echo formatPrice($ro['total_amount']); ?></td>
                        <td><span class="badge <?php echo $sc; ?>"><?php echo $ro['order_status']; ?></span></td>
                        <td>
                            <span class="badge <?php echo $ro['payment_status']==='Paid'?'badge-success':($ro['payment_status']==='Failed'?'badge-danger':'badge-pending'); ?>">
                                <?php echo $ro['payment_status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#bbb; padding:2.5rem;">No orders found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ CUSTOMER ACTIVITY ═══ -->
<div class="card">
    <div class="card-header">
        <h3>👥 Customer Activity</h3>
        <a href="reports.php?export=csv&type=customers&<?php echo $base_params; ?>"
           style="font-size:0.78rem; color:#6a1b9a; text-decoration:none; font-weight:600; border:1px solid #6a1b9a; padding:0.3rem 0.8rem; border-radius:6px;">
            ⬇️ Export CSV
        </a>
    </div>
    <div style="padding:1.5rem 2rem;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.2rem;">
            <?php
            $act_cards = [
                ['icon'=>'🆕','label'=>'New Sign-ups',      'value'=>$new_reg,      'color'=>'#1565c0'],
                ['icon'=>'🔄','label'=>'Repeat Customers',  'value'=>$returning,    'color'=>'#2e7d32'],
                ['icon'=>'🛒','label'=>'Unique Buyers',     'value'=>$new_customers,'color'=>'#c5a059'],
                ['icon'=>'📦','label'=>'Total Orders',      'value'=>$total_orders, 'color'=>'#6a1b9a'],
            ];
            foreach ($act_cards as $ac): ?>
            <div style="background:#f9f9f9; border-radius:12px; padding:1.2rem 1.4rem; border:1px solid #eee;">
                <div style="font-size:1.4rem; margin-bottom:0.4rem;"><?php echo $ac['icon']; ?></div>
                <div style="font-size:0.68rem; color:#bbb; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;"><?php echo $ac['label']; ?></div>
                <div style="font-size:1.6rem; font-weight:800; color:<?php echo $ac['color']; ?>;"><?php echo $ac['value']; ?></div>
            </div>
            <?php endforeach; ?>

            <?php if ($top_cust): ?>
            <div style="background:linear-gradient(135deg,#fdf9f0,#fef5e0); border-radius:12px; padding:1.2rem 1.4rem; border:1px solid #f0e0b0; grid-column:span 1;">
                <div style="font-size:1.4rem; margin-bottom:0.4rem;">🥇</div>
                <div style="font-size:0.68rem; color:#a68443; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Top Customer</div>
                <div style="font-size:0.95rem; font-weight:700; color:#1a2340;"><?php echo htmlspecialchars($top_cust['full_name']); ?></div>
                <div style="font-size:0.78rem; color:#888; margin-top:3px;"><?php echo $top_cust['cnt']; ?> orders &nbsp;·&nbsp; <?php echo formatPrice($top_cust['total']); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
