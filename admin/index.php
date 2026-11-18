<?php
require_once 'auth_check.php';
require_once '../config/db.php';

$totalSales = $pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$lowStockCount = $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn();

$salesRows = $pdo->query('
    SELECT DATE(created_at) AS order_date, SUM(total_amount) AS revenue
    FROM orders
    GROUP BY DATE(created_at)
    ORDER BY order_date
')->fetchAll();

$salesLabels = [];
$salesData = [];
foreach ($salesRows as $row) {
    $salesLabels[] = date('d M Y', strtotime($row['order_date']));
    $salesData[] = (float) $row['revenue'];
}

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
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success-emphasis"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="text-body-secondary small">Total Sales</div>
                    <div class="fs-5 fw-bold"><span class="fs-6 text-body-secondary">BDT</span> <?= number_format($totalSales, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary-emphasis"><i class="bi bi-bag-check"></i></div>
                <div>
                    <div class="text-body-secondary small">Total Orders</div>
                    <div class="fs-5 fw-bold"><?= $totalOrders ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info-emphasis"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="text-body-secondary small">Total Products</div>
                    <div class="fs-5 fw-bold"><?= $totalProducts ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger-subtle text-danger-emphasis"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="text-body-secondary small">Low / Out of Stock</div>
                    <div class="fs-5 fw-bold"><?= $lowStockCount ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Sales by Date (BDT)</h2>
                <?php if (empty($salesData)): ?>
                    <p class="text-body-secondary mb-0">No sales yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="salesChart"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Orders by Status</h2>
                <?php if ($totalOrders == 0): ?>
                    <p class="text-body-secondary mb-0">No orders yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="ordersChart"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Top Selling Products (Units Sold)</h2>
                <?php if (empty($topData)): ?>
                    <p class="text-body-secondary mb-0">No products sold yet.</p>
                <?php else: ?>
                    <div class="chart-box"><canvas id="topProductsChart"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const salesLabels = <?= json_encode($salesLabels, JSON_HEX_TAG) ?>;
    const salesData = <?= json_encode($salesData) ?>;
    const statusLabels = <?= json_encode($statusLabels) ?>;
    const statusData = <?= json_encode(array_values($statusCounts)) ?>;
    const topLabels = <?= json_encode($topLabels, JSON_HEX_TAG) ?>;
    const topData = <?= json_encode($topData) ?>;

    const chartColors = {
        light: {
            main: '#2a78d6',
            status: ['#2a78d6', '#eb6834', '#1baf7a', '#eda100']
        },
        dark: {
            main: '#3987e5',
            status: ['#3987e5', '#d95926', '#199e70', '#c98500']
        }
    };

    let charts = [];

    function drawCharts() {
        charts.forEach(function (chart) {
            chart.destroy();
        });
        charts = [];

        const theme = document.documentElement.getAttribute('data-bs-theme');
        const colors = chartColors[theme] || chartColors.light;
        const styles = getComputedStyle(document.documentElement);

        Chart.defaults.color = styles.getPropertyValue('--bs-secondary-color').trim();
        Chart.defaults.borderColor = styles.getPropertyValue('--bs-border-color-translucent').trim();
        Chart.defaults.font.family = styles.getPropertyValue('--bs-body-font-family').trim();

        const salesCanvas = document.getElementById('salesChart');
        if (salesCanvas) {
            charts.push(new Chart(salesCanvas, {
                type: 'bar',
                data: {
                    labels: salesLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: salesData,
                        backgroundColor: colors.main,
                        borderRadius: 4,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return 'BDT ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true }
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
                        backgroundColor: colors.status,
                        borderColor: styles.getPropertyValue('--bs-body-bg').trim(),
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom' }
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
                        backgroundColor: colors.main,
                        borderRadius: 4,
                        maxBarThickness: 32
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
                        y: { grid: { display: false } }
                    }
                }
            }));
        }
    }

    drawCharts();
    document.addEventListener('themechange', drawCharts);
</script>

<?php require_once 'includes/footer.php'; ?>
