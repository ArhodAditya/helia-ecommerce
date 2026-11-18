<?php
require_once 'includes/user_auth.php';
require_once 'config/db.php';
require_once 'includes/ui_helpers.php';

$userId = $_SESSION['user_id'];

$statusColors = [
    'Pending' => 'warning',
    'Confirmed' => 'info',
    'Shipped' => 'primary',
    'Delivered' => 'success',
];

$stmt = $pdo->prepare('
    SELECT id, total_amount, coupon_code, status, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
require_once 'includes/header.php';
?>

<div class="row g-4">
<div class="col-lg-3">
    <?php require 'includes/account_sidebar.php'; ?>
</div>
<div class="col-lg-9">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">My Orders</h1>
        <p class="text-body-secondary mb-0">You have placed <?= count($orders) ?> order(s) with Helia.</p>
    </div>
    <a href="order_tracking.php" class="btn btn-outline-primary">
        <i class="bi bi-truck"></i> Track an Order
    </a>
</div>

<?php if (empty($orders)): ?>
    <?= renderEmptyState('No orders yet', 'When you place an order it will show up here.', 'Start Shopping', 'products.php', 'bi-bag-x') ?>
<?php else: ?>
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Coupon</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold">#<?= $order['id'] ?></td>
                                <td class="text-nowrap"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <?php if (!empty($order['coupon_code'])): ?>
                                        <span class="badge text-bg-secondary"><?= e($order['coupon_code']) ?></span>
                                    <?php else: ?>
                                        <span class="text-body-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">BDT <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <a href="order_tracking.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-truck"></i> Track Order
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-md-none">
        <?php foreach ($orders as $order): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="h6 fw-bold mb-1">Order #<?= $order['id'] ?></h2>
                            <div class="small text-body-secondary"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                        </div>
                        <span class="badge text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Coupon</span>
                        <span><?= !empty($order['coupon_code']) ? e($order['coupon_code']) : '-' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-body-secondary small">Total</span>
                        <span class="fw-semibold">BDT <?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <a href="order_tracking.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="bi bi-truck"></i> Track Order
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>
</div>

<?php require_once 'includes/footer.php'; ?>
