<?php
require_once 'includes/user_auth.php';
require_once 'config/db.php';

$userId = $_SESSION['user_id'];
$orderId = $_GET['id'] ?? '';

$statusColors = [
    'Pending' => 'warning',
    'Confirmed' => 'info',
    'Shipped' => 'primary',
    'Delivered' => 'success',
];

$order = false;
$items = [];
$itemsTotal = 0;

if (ctype_digit((string) $orderId)) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();
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

$pageTitle = $order ? 'Order #' . $order['id'] : 'Order Not Found';
require_once 'includes/header.php';
?>

<a href="my_orders.php" class="btn btn-link text-decoration-none px-0 mb-3">
    <i class="bi bi-arrow-left"></i> Back to My Orders
</a>

<?php if (!$order): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-search display-5 text-body-secondary"></i>
            <h1 class="h5 mt-3">Order not found</h1>
            <p class="text-body-secondary mb-0">We could not find this order in your account.</p>
        </div>
    </div>
<?php else: ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Order #<?= $order['id'] ?></h1>
            <p class="text-body-secondary mb-0">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge fs-6 text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
            <a href="order_tracking.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-truck"></i> Track Order
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><?= count($items) ?> item(s) in this order</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-body-secondary py-4">No items found for this order.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="product-thumb">
                                                <?php else: ?>
                                                    <div class="product-thumb thumb-placeholder"><i class="bi bi-image"></i></div>
                                                <?php endif; ?>
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
                                        <td class="text-end text-nowrap fw-semibold">BDT <?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Order Summary</h2>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-body-secondary">Customer</span>
                        <span><?= e($order['customer_name']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-body-secondary">Items Total</span>
                        <span>BDT <?= number_format($itemsTotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-body-secondary">Coupon Applied</span>
                        <?php if (!empty($order['coupon_code'])): ?>
                            <span class="badge text-bg-secondary align-self-center"><?= e($order['coupon_code']) ?></span>
                        <?php else: ?>
                            <span>None</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>- BDT <?= number_format($discount, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span>Grand Total</span>
                        <span>BDT <?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Delivery &amp; Payment</h2>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-telephone text-primary"></i>
                        <div>
                            <div class="small text-body-secondary">Phone</div>
                            <div><?= !empty($order['phone']) ? e($order['phone']) : 'Not provided' ?></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-geo-alt text-primary"></i>
                        <div>
                            <div class="small text-body-secondary">Shipping Address</div>
                            <div><?= !empty($order['shipping_address']) ? nl2br(e($order['shipping_address'])) : 'Not provided' ?></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <i class="bi bi-credit-card text-primary"></i>
                        <div>
                            <div class="small text-body-secondary">Payment Method</div>
                            <div><?= e($order['payment_method'] ?: 'Cash on Delivery') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
