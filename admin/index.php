<?php
require_once 'auth_check.php';
require_once '../config/db.php';
require_once '../includes/brand.php';

$totalSales = $pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$lowStockCount = $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn();
$outOfStockCount = $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 0')->fetchColumn();
$totalCategories = $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$monthOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();
$averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

$monthRows = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, SUM(total_amount) AS revenue, COUNT(*) AS order_count
    FROM orders
    GROUP BY month_key
    ORDER BY month_key
")->fetchAll();

$monthTotals = [];
foreach ($monthRows as $row) {
    $monthTotals[$row['month_key']] = [(float) $row['revenue'], (int) $row['order_count']];
}

$salesLabels = [];
$salesData = [];
$salesOrders = [];
$currentMonthKey = date('Y-m');

if (!empty($monthTotals)) {
    $firstMonth = new DateTime(array_key_first($monthTotals) . '-01');
    $lastMonth = new DateTime(max(array_key_last($monthTotals), $currentMonthKey) . '-01');
    $windowStart = (clone $lastMonth)->modify('-11 months');

    if ($firstMonth < $windowStart) {
        $firstMonth = $windowStart;
    }

    for ($month = $firstMonth; $month <= $lastMonth; $month->modify('+1 month')) {
        $key = $month->format('Y-m');
        $salesLabels[] = $month->format('M Y');
        $salesData[] = $monthTotals[$key][0] ?? 0;
        $salesOrders[] = $monthTotals[$key][1] ?? 0;
    }
}

$currentMonthLabel = date('M Y');

$statusCounts = ['Pending' => 0, 'Confirmed' => 0, 'Shipped' => 0, 'Delivered' => 0];
$statusRows = $pdo->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status')->fetchAll();
foreach ($statusRows as $row) {
    if (isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']] = (int) $row['total'];
    }
}

$statusLabels = [];
foreach ($statusCounts as $status => $count) {
    $statusLabels[] = $status . ' (' . $count . ')';
}

$categoryRows = $pdo->query("
    SELECT COALESCE(categories.name, 'Uncategorized') AS category_name, SUM(order_items.quantity * order_items.price) AS revenue
    FROM order_items
    LEFT JOIN products ON products.id = order_items.product_id
    LEFT JOIN categories ON categories.id = products.category_id
    GROUP BY category_name
    ORDER BY revenue DESC
")->fetchAll();

$categoryLabels = [];
$categoryData = [];
$categoryKeys = [];
foreach ($categoryRows as $row) {
    $categoryLabels[] = $row['category_name'];
    $categoryData[] = (float) $row['revenue'];
    $categoryKeys[] = categoryIconKey($row['category_name']);
}

$topRows = $pdo->query('
    SELECT products.name, SUM(order_items.quantity) AS total_sold
    FROM order_items
    JOIN products ON products.id = order_items.product_id
    GROUP BY products.id, products.name
    ORDER BY total_sold DESC
    LIMIT 5
')->fetchAll();

$topLabels = [];
$topData = [];
foreach ($topRows as $row) {
    $topLabels[] = $row['name'];
    $topData[] = (int) $row['total_sold'];
}

$pageTitle = 'Dashboard';
require_once 'includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stat-card stat-success">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="stat-label">Total Sales</div>
                        <div class="stat-value"><span class="stat-unit">BDT</span><?= number_format($totalSales, 2) ?></div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success-emphasis"><i class="bi bi-cash-stack"></i></div>
                </div>
                <div class="stat-meta">Average order BDT <?= number_format($averageOrder, 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stat-card stat-primary">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value"><?= $totalOrders ?></div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary-emphasis"><i class="bi bi-bag-check"></i></div>
                </div>
                <div class="stat-meta"><?= $monthOrders ?> this month &middot; <?= $statusCounts['Pending'] ?> pending</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stat-card stat-info">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="stat-label">Total Products</div>
                        <div class="stat-value"><?= $totalProducts ?></div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info-emphasis"><i class="bi bi-box-seam"></i></div>
                </div>
                <div class="stat-meta">Across <?= $totalCategories ?> categories</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 stat-card stat-danger">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="stat-label">Low / Out of Stock</div>
                        <div class="stat-value"><?= $lowStockCount ?></div>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger-emphasis"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
                <div class="stat-meta"><?= $outOfStockCount ?> out of stock &middot; <a href="products.php">Review stock</a></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="chart-head">
                    <h2 class="h6 fw-semibold mb-0">Monthly Revenue (BDT)</h2>
                    <?php if (!empty($salesData)): ?>
                        <span class="chart-legend"><span class="chart-key chart-key-current"></span><?= e($currentMonthLabel) ?> so far</span>
                    <?php endif; ?>
                </div>
                <?php if (empty($salesData)): ?>
                    <p class="text-body-secondary mb-0">No sales yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="salesChart" aria-label="Monthly revenue chart" role="img"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="chart-head">
                    <h2 class="h6 fw-semibold mb-0">Orders by Status</h2>
                </div>
                <?php if ($totalOrders == 0): ?>
                    <p class="text-body-secondary mb-0">No orders yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="ordersChart" aria-label="Orders by status chart" role="img"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="chart-head">
                    <h2 class="h6 fw-semibold mb-0">Sales by Category (BDT)</h2>
                </div>
                <?php if (empty($categoryData)): ?>
                    <p class="text-body-secondary mb-0">No products sold yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="categoryChart" aria-label="Sales by category chart" role="img"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="chart-head">
                    <h2 class="h6 fw-semibold mb-0">Top Selling Products (Units Sold)</h2>
                </div>
                <?php if (empty($topData)): ?>
                    <p class="text-body-secondary mb-0">No products sold yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="topProductsChart" aria-label="Top selling products chart" role="img"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const salesLabels = <?= json_encode($salesLabels, JSON_HEX_TAG) ?>;
    const salesData = <?= json_encode($salesData) ?>;
    const salesOrders = <?= json_encode($salesOrders) ?>;
    const currentMonthLabel = <?= json_encode($currentMonthLabel, JSON_HEX_TAG) ?>;
    const statusLabels = <?= json_encode($statusLabels) ?>;
    const statusData = <?= json_encode(array_values($statusCounts)) ?>;
    const categoryLabels = <?= json_encode($categoryLabels, JSON_HEX_TAG) ?>;
    const categoryData = <?= json_encode($categoryData) ?>;
    const categoryKeys = <?= json_encode($categoryKeys) ?>;
    const topLabels = <?= json_encode($topLabels, JSON_HEX_TAG) ?>;
    const topData = <?= json_encode($topData) ?>;

    const fallbackColors = ['#3b5bdb', '#2b8a3e', '#c2255c', '#d9480f', '#0b7285', '#6741d9', '#5b6080'];

    let charts = [];

    function cssValue(styles, name, fallback) {
        const value = styles.getPropertyValue(name).trim();
        return value !== '' ? value : fallback;
    }

    function formatTaka(value) {
        return 'BDT ' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    }

    function drawCharts() {
        charts.forEach(function (chart) {
            chart.destroy();
        });
        charts = [];

        const styles = getComputedStyle(document.documentElement);
        const main = cssValue(styles, '--helia-neel', '#3038a6');
        const sun = cssValue(styles, '--helia-marigold', '#f6a019');
        const surface = cssValue(styles, '--bs-body-bg', '#ffffff');
        const statusColors = [
            cssValue(styles, '--bs-warning', '#ffc107'),
            cssValue(styles, '--bs-info', '#0dcaf0'),
            main,
            cssValue(styles, '--bs-success', '#198754')
        ];

        Chart.defaults.color = cssValue(styles, '--bs-secondary-color', '#5b6080');
        Chart.defaults.borderColor = cssValue(styles, '--bs-border-color-translucent', 'rgba(0, 0, 0, 0.1)');
        Chart.defaults.font.family = cssValue(styles, '--bs-body-font-family', 'sans-serif');
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;

        const salesCanvas = document.getElementById('salesChart');
        if (salesCanvas) {
            charts.push(new Chart(salesCanvas, {
                type: 'bar',
                data: {
                    labels: salesLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: salesData,
                        backgroundColor: salesLabels.map(function (label) {
                            return label === currentMonthLabel ? sun : main;
                        }),
                        borderRadius: 6,
                        maxBarThickness: 44
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return formatTaka(context.parsed.y);
                                },
                                afterLabel: function (context) {
                                    const count = salesOrders[context.dataIndex] || 0;
                                    return count + ' order' + (count === 1 ? '' : 's');
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return value >= 1000 ? (value / 1000) + 'k' : value;
                                }
                            }
                        }
                    }
                }
            }));
        }

        const ordersCanvas = document.getElementById('ordersChart');
        if (ordersCanvas) {
            charts.push(new Chart(ordersCanvas, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: statusColors,
                        borderColor: surface,
                        borderWidth: 3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            }));
        }

        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            const used = [];
            const categoryColors = categoryKeys.map(function (key, index) {
                let color = cssValue(styles, '--cat-' + key, '');

                if (color === '' || used.indexOf(color) !== -1) {
                    color = fallbackColors[index % fallbackColors.length];
                }

                used.push(color);
                return color;
            });

            charts.push(new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: categoryColors,
                        borderColor: surface,
                        borderWidth: 3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + formatTaka(context.parsed);
                                }
                            }
                        }
                    }
                }
            }));
        }

        const topCanvas = document.getElementById('topProductsChart');
        if (topCanvas) {
            charts.push(new Chart(topCanvas, {
                type: 'bar',
                data: {
                    labels: topLabels,
                    datasets: [{
                        label: 'Units sold',
                        data: topData,
                        backgroundColor: main,
                        borderRadius: 6,
                        maxBarThickness: 28
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } },
                        y: {
                            grid: { display: false },
                            ticks: {
                                callback: function (value) {
                                    const label = this.getLabelForValue(value);
                                    return label.length > 28 ? label.slice(0, 27) + '…' : label;
                                }
                            }
                        }
                    }
                }
            }));
        }
    }

    drawCharts();
    document.addEventListener('themechange', drawCharts);
</script>

<?php require_once 'includes/footer.php'; ?>
