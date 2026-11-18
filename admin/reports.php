<?php
require_once 'auth_check.php';
require_once '../config/db.php';

$statuses = ['Pending', 'Confirmed', 'Shipped', 'Delivered'];
$statusColors = [
    'Pending' => 'warning',
    'Confirmed' => 'info',
    'Shipped' => 'primary',
    'Delivered' => 'success',
];

$status = $_GET['status'] ?? '';
$fromDate = $_GET['from'] ?? '';
$toDate = $_GET['to'] ?? '';

$sql = 'SELECT id, customer_name, total_amount, coupon_code, status, created_at FROM orders WHERE 1 = 1';
$params = [];

if (in_array($status, $statuses)) {
    $sql .= ' AND status = ?';
    $params[] = $status;
} else {
    $status = '';
}

if ($fromDate !== '') {
    $sql .= ' AND DATE(created_at) >= ?';
    $params[] = $fromDate;
}

if ($toDate !== '') {
    $sql .= ' AND DATE(created_at) <= ?';
    $params[] = $toDate;
}

$sql .= ' ORDER BY created_at DESC, id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$totalOrders = count($orders);
$totalRevenue = 0;
foreach ($orders as $order) {
    $totalRevenue += $order['total_amount'];
}

$filterText = 'Status: ' . ($status === '' ? 'All' : $status)
    . '  |  From: ' . ($fromDate === '' ? 'Any' : $fromDate)
    . '  |  To: ' . ($toDate === '' ? 'Any' : $toDate);

$reportInfo = [
    'filters' => $filterText,
    'generated' => date('d M Y, h:i A'),
    'totalOrders' => $totalOrders,
    'totalRevenue' => 'BDT ' . number_format($totalRevenue, 2),
];

$pageTitle = 'Reports';
require_once 'includes/header.php';
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label for="status" class="form-label small text-body-secondary">Order Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($statuses as $item): ?>
                        <option value="<?= $item ?>" <?= $status === $item ? 'selected' : '' ?>><?= $item ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label for="from" class="form-label small text-body-secondary">From Date</label>
                <input type="date" class="form-control" id="from" name="from" value="<?= e($fromDate) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label for="to" class="form-label small text-body-secondary">To Date</label>
                <input type="date" class="form-control" id="to" name="to" value="<?= e($toDate) ?>">
            </div>
            <div class="col-sm-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="reports.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary-emphasis"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="text-body-secondary small">Total Orders</div>
                    <div class="fs-4 fw-bold"><?= $totalOrders ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success-emphasis"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="text-body-secondary small">Total Revenue</div>
                    <div class="fs-4 fw-bold">BDT <?= number_format($totalRevenue, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h6 fw-semibold mb-0">Sales Report</h2>
            <button type="button" id="exportPdf" class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Export Report as PDF
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Coupon</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-4">No orders match these filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= !empty($order['coupon_code']) ? e($order['coupon_code']) : '-' ?></td>
                            <td><span class="badge text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span></td>
                            <td class="text-nowrap"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            <td class="text-end text-nowrap">BDT <?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($orders)): ?>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="5">Total (<?= $totalOrders ?> orders)</td>
                            <td class="text-end text-nowrap">BDT <?= number_format($totalRevenue, 2) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    const reportInfo = <?= json_encode($reportInfo, JSON_HEX_TAG) ?>;

    document.getElementById('exportPdf').addEventListener('click', function () {
        const doc = new window.jspdf.jsPDF();

        doc.setFontSize(18);
        doc.text('Helia Sales Report', 14, 20);

        doc.setFontSize(10);
        doc.text(reportInfo.filters, 14, 28);
        doc.text('Generated: ' + reportInfo.generated, 14, 34);
        doc.text('Total Orders: ' + reportInfo.totalOrders + '     Total Revenue: ' + reportInfo.totalRevenue, 14, 40);

        doc.autoTable({
            html: '#reportTable',
            startY: 46,
            headStyles: { fillColor: [13, 110, 253] },
            footStyles: { fillColor: [233, 236, 239], textColor: 20 },
            columnStyles: { 5: { halign: 'right' } }
        });

        doc.save('helia_sales_report.pdf');
    });
</script>

<?php require_once 'includes/footer.php'; ?>
