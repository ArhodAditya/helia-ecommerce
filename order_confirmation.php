<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';
require_once 'includes/cart_helpers.php';

$orderId = $_GET['id'] ?? '';
$order = false;
$items = [];
$itemsTotal = 0;

if (ctype_digit((string) $orderId)) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
}

if ($order) {
    $isOwner = isset($_SESSION['user_id']) && $order['user_id'] == $_SESSION['user_id'];
    $justPlaced = isset($_SESSION['last_order_id']) && $_SESSION['last_order_id'] == $order['id'];

    if (!$isOwner && !$justPlaced) {
        $order = false;
    }
}

if ($order) {
    $stmt = $pdo->prepare('
        SELECT order_items.quantity, order_items.price, order_items.color, products.name, products.image
        FROM order_items
        LEFT JOIN products ON products.id = order_items.product_id
        WHERE order_items.order_id = ?
        ORDER BY order_items.id
    ');
    $stmt->execute([$order['id']]);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {
        $itemsTotal += $item['quantity'] * $item['price'];
    }
}

$discount = $order ? $itemsTotal - $order['total_amount'] : 0;

$pageTitle = $order ? 'Order Placed' : 'Order Not Found';
require_once 'includes/header.php';
?>

<?php if (!$order): ?>
    <?= renderEmptyState('Order not found', 'We could not find this order, or it belongs to another account.', 'Continue Shopping', 'products.php', 'bi-receipt') ?>
<?php else: ?>
    <?= renderCheckoutSteps(5) ?>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4 p-md-5">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-3" style="width: 72px; height: 72px; font-size: 2.2rem;">
                        <i class="bi bi-check-lg"></i>
                    </span>
                    <h1 class="h3 fw-bold mb-2">Order Placed Successfully!</h1>
                    <p class="text-body-secondary mb-0">Thank you, <?= e($order['customer_name']) ?>. Your order <strong class="text-body">#<?= $order['id'] ?></strong> has been received and is now <span class="badge text-bg-warning"><?= e($order['status']) ?></span>.</p>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 fw-semibold mb-3"><i class="bi bi-receipt text-primary"></i> Order Info</h2>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-body-secondary">Order ID</span>
                                <span class="fw-semibold">#<?= $order['id'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-body-secondary">Date</span>
                                <span><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-body-secondary">Payment Method</span>
                                <span class="text-end"><?= e($order['payment_method']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-body-secondary">Coupon Used</span>
                                <?php if (!empty($order['coupon_code'])): ?>
                                    <span class="badge text-bg-secondary"><?= e($order['coupon_code']) ?></span>
                                <?php else: ?>
                                    <span>None</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 fw-semibold mb-3"><i class="bi bi-geo-alt text-primary"></i> Shipping To</h2>
                            <div class="fw-semibold"><?= e($order['customer_name']) ?></div>
                            <div class="small text-body-secondary"><i class="bi bi-telephone"></i> <?= e($order['phone']) ?></div>
                            <div class="small text-body-secondary mt-1"><?= nl2br(e($order['shipping_address'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Receipt</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?= renderProductImage($item['image'] ?? '', '', 'product-thumb', 'sm') ?>
                                                <div>
                                                    <div class="fw-semibold"><?= e($item['name'] ?? 'Product no longer available') ?></div>
                                                    <?php if (!empty($item['color'])): ?>
                                                        <div class="small text-body-secondary">
                                                            <span class="color-dot" style="width: 12px; height: 12px; background-color: <?= e($item['color']) ?>;"></span> <?= e($item['color']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                        <td class="text-end text-nowrap">BDT <?= number_format($item['price'], 2) ?></td>
                                        <td class="text-end text-nowrap">BDT <?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end text-body-secondary">Items Total</td>
                                    <td class="text-end text-nowrap">BDT <?= number_format($itemsTotal, 2) ?></td>
                                </tr>
                                <?php if ($discount > 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-end text-success">Discount<?= !empty($order['coupon_code']) ? ' (' . e($order['coupon_code']) . ')' : '' ?></td>
                                        <td class="text-end text-success text-nowrap">- BDT <?= number_format($discount, 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total</td>
                                    <td class="text-end fw-bold text-nowrap">BDT <?= number_format($order['total_amount'], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                <a href="order_tracking.php?id=<?= $order['id'] ?>" class="btn btn-primary"><i class="bi bi-truck"></i> Track Order</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my_orders.php" class="btn btn-outline-primary"><i class="bi bi-bag-check"></i> My Orders</a>
                <?php endif; ?>
                <a href="products.php" class="btn btn-outline-secondary"><i class="bi bi-bag"></i> Continue Shopping</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
