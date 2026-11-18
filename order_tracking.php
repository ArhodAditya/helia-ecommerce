<?php
session_start();
require_once 'config/db.php';

$orderId = trim($_GET['id'] ?? '');

$steps = [
    'Pending' => ['bi-hourglass-split', 'Order placed and waiting for confirmation.'],
    'Confirmed' => ['bi-check2-circle', 'Your order has been confirmed by Helia.'],
    'Shipped' => ['bi-truck', 'Your order is on the way.'],
    'Delivered' => ['bi-house-check', 'Your order has been delivered.'],
];

$statusColors = [
    'Pending' => 'warning',
    'Confirmed' => 'info',
    'Shipped' => 'primary',
    'Delivered' => 'success',
];

$order = false;
$error = '';
$itemCount = 0;

if ($orderId !== '') {
    $orderId = ltrim($orderId, '#');

    if (!ctype_digit($orderId)) {
        $error = 'Please enter a valid order number.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_id = ?');
            $stmt->execute([$order['id']]);
            $itemCount = $stmt->fetchColumn();
        } else {
            $error = 'No order found with ID #' . $orderId . '.';
        }
    }
}

$stepNames = array_keys($steps);
$currentStep = $order ? array_search($order['status'], $stepNames) : 0;

if ($currentStep === false) {
    $currentStep = 0;
}

$progress = $currentStep / (count($stepNames) - 1) * 100;

$pageTitle = 'Track Order';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="text-center mb-4">
            <h1 class="h4 fw-bold mb-1">Track Your Order</h1>
            <p class="text-body-secondary mb-0">Enter your order ID to see where your order is right now.</p>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="get" action="order_tracking.php" class="row g-2">
                    <div class="col-sm-8 col-md-9">
                        <label for="id" class="visually-hidden">Order ID</label>
                        <div class="input-group">
                            <span class="input-group-text">#</span>
                            <input type="text" class="form-control" id="id" name="id" inputmode="numeric" placeholder="Enter your order ID, e.g. 1" value="<?= e($orderId) ?>" required>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Track
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Order #<?= $order['id'] ?></h2>
                            <p class="text-body-secondary mb-0"><?= e($steps[$order['status']][1] ?? '') ?></p>
                        </div>
                        <span class="badge fs-6 text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
                    </div>

                    <div class="tracker mb-2">
                        <div class="progress tracker-line" role="progressbar" aria-label="Order progress" aria-valuenow="<?= round($progress) ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                        </div>
                        <div class="d-flex">
                            <?php foreach ($stepNames as $index => $stepName): ?>
                                <?php
                                $stepClass = '';
                                $icon = $steps[$stepName][0];

                                if ($index < $currentStep || ($index === $currentStep && $stepName === 'Delivered')) {
                                    $stepClass = 'done';
                                    $icon = 'bi-check-lg';
                                } elseif ($index === $currentStep) {
                                    $stepClass = 'current';
                                }
                                ?>
                                <div class="tracker-step <?= $stepClass ?>">
                                    <span class="tracker-circle"><i class="bi <?= $icon ?>"></i></span>
                                    <div class="tracker-label small mt-2"><?= $stepName ?></div>
                                    <?php if ($index === $currentStep): ?>
                                        <div class="small text-body-secondary d-none d-sm-block">Current stage</div>
                                    <?php elseif ($index < $currentStep): ?>
                                        <div class="small text-success d-none d-sm-block">Completed</div>
                                    <?php else: ?>
                                        <div class="small text-body-secondary d-none d-sm-block">Upcoming</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Order Summary</h2>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="small text-body-secondary">Customer</div>
                            <div class="fw-semibold"><?= e($order['customer_name']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-body-secondary">Order Date</div>
                            <div class="fw-semibold"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="small text-body-secondary">Items</div>
                            <div class="fw-semibold"><?= $itemCount ?></div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="small text-body-secondary">Coupon</div>
                            <div class="fw-semibold"><?= !empty($order['coupon_code']) ? e($order['coupon_code']) : '-' ?></div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="small text-body-secondary">Total</div>
                            <div class="fw-semibold text-nowrap">BDT <?= number_format($order['total_amount'], 2) ?></div>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $order['user_id']): ?>
                        <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary mt-3">
                            <i class="bi bi-eye"></i> View Full Details
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($orderId === ''): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-box-seam display-5 text-body-secondary"></i>
                    <p class="text-body-secondary mt-3 mb-0">Your order status will appear here after you search.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
