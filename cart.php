<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';
require_once 'includes/cart_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $redirect = 'cart.php';

    if ($action === 'add') {
        addToCart($pdo, $productId, $_POST['quantity'] ?? 1, $_POST['color'] ?? '');
        $redirect = getSafeReturnLink($_POST['return_to'] ?? '', 'cart.php');
    } elseif ($action === 'update' && isset($_SESSION['cart'][$productId])) {
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
            setFlash('The item was removed from your cart.');
        } else {
            $stmt = $pdo->prepare('SELECT name, stock FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if ($product && $quantity > $product['stock']) {
                $quantity = (int) $product['stock'];
                setFlash('Only ' . $product['stock'] . ' of ' . $product['name'] . ' in stock, so the quantity was set to ' . $quantity . '.', 'warning');
            } else {
                setFlash('Your cart was updated.');
            }

            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$productId]);
        setFlash('The item was removed from your cart.');
    } elseif ($action === 'apply_coupon') {
        applyCoupon($pdo, $_POST['code'] ?? '');
    } elseif ($action === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        setFlash('The coupon was removed.');
    }

    header('Location: ' . $redirect);
    exit;
}

$cart = loadCart($pdo);
$items = $cart['items'];
$coupon = $cart['coupon'];
$totals = calculateTotals($items, $coupon);

$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';
?>

<?= renderCheckoutSteps(1) ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">Shopping Cart</h1>
        <p class="text-body-secondary mb-0"><?= count($items) ?> product(s) in your cart</p>
    </div>
    <a href="products.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
</div>

<?php foreach ($cart['notices'] as $notice): ?>
    <div class="alert alert-warning py-2"><i class="bi bi-info-circle me-1"></i><?= e($notice) ?></div>
<?php endforeach; ?>

<?php if (empty($items)): ?>
    <?= renderEmptyState('Your cart is empty', 'Looks like you have not added anything yet. Find something you like and add it here.', 'Start Shopping', 'products.php', 'bi-cart-x') ?>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush">
                    <?php foreach ($items as $item): ?>
                        <div class="list-group-item p-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="product_details.php?id=<?= $item['id'] ?>">
                                            <img src="<?= e(getProductImage($item['image'])) ?>" alt="<?= e($item['name']) ?>" class="product-thumb product-thumb-lg">
                                        </a>
                                        <div>
                                            <a href="product_details.php?id=<?= $item['id'] ?>" class="fw-semibold text-body text-decoration-none"><?= e($item['name']) ?></a>
                                            <?php if ($item['color'] !== ''): ?>
                                                <div class="small text-body-secondary mt-1">
                                                    Color: <span class="color-dot" style="background-color: <?= e($item['color']) ?>;" title="<?= e($item['color']) ?>"></span> <?= e($item['color']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="small text-body-secondary">BDT <?= number_format($item['price'], 2) ?> each</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-7 col-md-3">
                                    <form method="post" action="cart.php" class="d-flex gap-1">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <label for="qty<?= $item['id'] ?>" class="visually-hidden">Quantity for <?= e($item['name']) ?></label>
                                        <input type="number" class="form-control form-control-sm" id="qty<?= $item['id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock'] ?>" style="max-width: 80px;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update quantity" aria-label="Update quantity"><i class="bi bi-arrow-repeat"></i></button>
                                    </form>
                                    <div class="small text-body-secondary mt-1"><?= $item['stock'] ?> in stock</div>
                                </div>
                                <div class="col-5 col-md-3 text-end">
                                    <div class="fw-bold">BDT <?= number_format($item['line_total'], 2) ?></div>
                                    <form method="post" action="cart.php">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-link btn-sm text-danger px-0"><i class="bi bi-trash"></i> Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Order Summary</h2>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-body-secondary">Subtotal</span>
                        <span>BDT <?= number_format($totals['subtotal'], 2) ?></span>
                    </div>

                    <?php if ($coupon): ?>
                        <div class="d-flex justify-content-between align-items-center bg-success-subtle rounded px-2 py-2 mb-3">
                            <span class="small"><i class="bi bi-ticket-perforated"></i> <strong><?= e($coupon['code']) ?></strong> applied</span>
                            <form method="post" action="cart.php">
                                <input type="hidden" name="action" value="remove_coupon">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0">Remove</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="post" action="cart.php" class="mb-3">
                            <input type="hidden" name="action" value="apply_coupon">
                            <label for="code" class="form-label small text-body-secondary">Have a coupon?</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control text-uppercase" id="code" name="code" placeholder="Coupon code" maxlength="50" required>
                                <button type="submit" class="btn btn-outline-primary">Apply</button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-body-secondary">Discount (<?= $totals['discount_percent'] ?>%)</span>
                        <span class="<?= $totals['discount'] > 0 ? 'text-success' : '' ?>">- BDT <?= number_format($totals['discount'], 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                        <span>Grand Total</span>
                        <span>BDT <?= number_format($totals['grand_total'], 2) ?></span>
                    </div>

                    <a href="checkout.php" class="btn btn-primary w-100">
                        Proceed to Checkout <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
