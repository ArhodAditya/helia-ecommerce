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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (in_array($status, $statuses)) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);

        $_SESSION['message'] = 'Order #' . $orderId . ' status updated to ' . $status . '.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Invalid order status.';
        $_SESSION['message_type'] = 'danger';
    }

    header('Location: orders.php');
    exit;
}

$orders = $pdo->query('
    SELECT id, customer_name, phone, shipping_address, total_amount, coupon_code, payment_method, status, created_at
    FROM orders
    ORDER BY created_at DESC, id DESC
')->fetchAll();

$itemRows = $pdo->query('
    SELECT order_items.order_id, order_items.quantity, order_items.price, order_items.color, products.name
    FROM order_items
    LEFT JOIN products ON products.id = order_items.product_id
    ORDER BY order_items.order_id, order_items.id
')->fetchAll();

$itemsByOrder = [];

foreach ($itemRows as $item) {
    $itemsByOrder[$item['order_id']][] = $item;
}

foreach ($orders as $index => $order) {
    $items = $itemsByOrder[$order['id']] ?? [];
    $itemsTotal = 0;

    foreach ($items as $item) {
        $itemsTotal += $item['quantity'] * $item['price'];
    }

    $paymentMethod = $order['payment_method'] ?: 'Cash on Delivery';

    $orders[$index]['items'] = $items;
    $orders[$index]['items_total'] = $itemsTotal;
    $orders[$index]['discount'] = $itemsTotal - $order['total_amount'];
    $orders[$index]['payment_method'] = $paymentMethod;
    $orders[$index]['payment_short'] = str_replace(['Cash on Delivery', 'Demo Online Payment'], ['COD', 'Online'], $paymentMethod);
    $orders[$index]['is_cod'] = $paymentMethod === 'Cash on Delivery';
}

$pageTitle = 'Orders';
require_once 'includes/header.php';
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h6 fw-semibold mb-3"><?= count($orders) ?> order(s)</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-nowrap">Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Coupon</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-nowrap">Change Status</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-body-secondary py-4">No orders yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="fw-semibold">#<?= $order['id'] ?></td>
                            <td>
                                <div class="text-nowrap"><?= e($order['customer_name']) ?></div>
                                <div class="small text-body-secondary text-nowrap">
                                    <i class="bi bi-telephone"></i> <?= !empty($order['phone']) ? e($order['phone']) : 'Not provided' ?>
                                </div>
                            </td>
                            <td class="text-nowrap">BDT <?= number_format($order['total_amount'], 2) ?></td>
                            <td class="small text-nowrap" title="<?= e($order['payment_method']) ?>">
                                <i class="bi <?= $order['is_cod'] ? 'bi-cash-coin text-success' : 'bi-credit-card text-primary' ?>"></i>
                                <?= e($order['payment_short']) ?>
                            </td>
                            <td>
                                <?php if (!empty($order['coupon_code'])): ?>
                                    <span class="badge text-bg-secondary"><?= e($order['coupon_code']) ?></span>
                                <?php else: ?>
                                    <span class="text-body-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
                            </td>
                            <td class="text-nowrap">
                                <div><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                                <div class="small text-body-secondary"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="form-select form-select-sm status-select" aria-label="Status for order <?= $order['id'] ?>">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#orderModal<?= $order['id'] ?>">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($orders as $order): ?>
    <div class="modal fade" id="orderModal<?= $order['id'] ?>" tabindex="-1" aria-labelledby="orderModalLabel<?= $order['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h5" id="orderModalLabel<?= $order['id'] ?>">
                        Order #<?= $order['id'] ?>
                        <span class="badge text-bg-<?= $statusColors[$order['status']] ?? 'secondary' ?> ms-1 fs-6 align-middle"><?= e($order['status']) ?></span>
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <h4 class="h6 fw-semibold mb-2"><i class="bi bi-geo-alt text-primary"></i> Ship To</h4>
                                <div class="fw-semibold"><?= e($order['customer_name']) ?></div>
                                <div class="small"><i class="bi bi-telephone"></i> <?= !empty($order['phone']) ? e($order['phone']) : 'Not provided' ?></div>
                                <div class="small text-body-secondary mt-1"><?= !empty($order['shipping_address']) ? nl2br(e($order['shipping_address'])) : 'Address not provided' ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <h4 class="h6 fw-semibold mb-2"><i class="bi bi-receipt text-primary"></i> Order Info</h4>
                                <div class="small d-flex justify-content-between gap-2"><span class="text-body-secondary">Placed</span><span><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span></div>
                                <div class="small d-flex justify-content-between gap-2"><span class="text-body-secondary">Payment</span><span class="text-end"><?= e($order['payment_method']) ?></span></div>
                                <div class="small d-flex justify-content-between gap-2"><span class="text-body-secondary">Coupon</span><span><?= !empty($order['coupon_code']) ? e($order['coupon_code']) : 'None' ?></span></div>
                            </div>
                        </div>
                    </div>

                    <h4 class="h6 fw-semibold mb-2"><i class="bi bi-box-seam text-primary"></i> Items (<?= count($order['items']) ?>)</h4>
                    <?php if (empty($order['items'])): ?>
                        <p class="small text-body-secondary mb-0">No items found for this order.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div><?= e($item['name'] ?? 'Product no longer available') ?></div>
                                                <?php if (!empty($item['color'])): ?>
                                                    <div class="small text-body-secondary">
                                                        <span class="color-dot" style="width: 12px; height: 12px; background-color: <?= e($item['color']) ?>;"></span> <?= e($item['color']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= $item['quantity'] ?></td>
                                            <td class="text-end text-nowrap">BDT <?= number_format($item['price'], 2) ?></td>
                                            <td class="text-end text-nowrap">BDT <?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end small text-body-secondary">Items Total</td>
                                        <td class="text-end small text-nowrap">BDT <?= number_format($order['items_total'], 2) ?></td>
                                    </tr>
                                    <?php if ($order['discount'] > 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-end small text-success">Discount<?= !empty($order['coupon_code']) ? ' (' . e($order['coupon_code']) . ')' : '' ?></td>
                                            <td class="text-end small text-success text-nowrap">- BDT <?= number_format($order['discount'], 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="3" class="text-end fw-semibold">Grand Total</td>
                                        <td class="text-end fw-semibold text-nowrap">BDT <?= number_format($order['total_amount'], 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
