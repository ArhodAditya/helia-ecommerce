<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/cart_helpers.php';

$paymentMethods = [
    'Cash on Delivery' => ['bi-cash-coin', 'Pay with cash when your parcel arrives.'],
    'Demo Online Payment' => ['bi-credit-card', 'Pay now with bKash, Nagad or Visa (demo only, no real money).'],
];

$cart = loadCart($pdo);
$items = $cart['items'];
$coupon = $cart['coupon'];

if (empty($items)) {
    if (!empty($cart['notices'])) {
        setFlash(implode(' ', $cart['notices']), 'warning');
    }

    header('Location: cart.php');
    exit;
}

$errors = [];
$form = [
    'customer_name' => '',
    'phone' => '',
    'shipping_address' => '',
    'payment_method' => 'Cash on Delivery',
];

if (isset($_SESSION['checkout_form'])) {
    $form = array_merge($form, $_SESSION['checkout_form']);
} elseif (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT name, phone, address FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $form['customer_name'] = $user['name'];
        $form['phone'] = $user['phone'] ?? '';
        $form['shipping_address'] = $user['address'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $form['customer_name'] = trim($_POST['customer_name'] ?? '');
    $form['phone'] = trim($_POST['phone'] ?? '');
    $form['shipping_address'] = trim($_POST['shipping_address'] ?? '');
    $form['payment_method'] = $_POST['payment_method'] ?? 'Cash on Delivery';
    $_SESSION['checkout_form'] = $form;

    if ($action === 'apply_coupon') {
        applyCoupon($pdo, $_POST['code'] ?? '');
        header('Location: checkout.php');
        exit;
    }

    if ($action === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        setFlash('The coupon was removed.');
        header('Location: checkout.php');
        exit;
    }

    if ($action === 'continue') {
        if ($form['customer_name'] === '') {
            $errors[] = 'Please enter your full name.';
        } elseif (mb_strlen($form['customer_name']) > 100) {
            $errors[] = 'Name must be 100 characters or less.';
        }

        if (!preg_match('/^\+?[0-9\s-]{6,20}$/', $form['phone'])) {
            $errors[] = 'Please enter a valid phone number.';
        }

        if ($form['shipping_address'] === '') {
            $errors[] = 'Please enter your shipping address.';
        }

        if (!isset($paymentMethods[$form['payment_method']])) {
            $errors[] = 'Please choose a payment method.';
            $form['payment_method'] = 'Cash on Delivery';
        }

        if (empty($errors)) {
            $_SESSION['checkout_data'] = $form;
            header('Location: payment.php');
            exit;
        }
    }
}

$totals = calculateTotals($items, $coupon);

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<?= renderCheckoutSteps(2) ?>

<h1 class="h4 fw-bold mb-4">Checkout</h1>

<?php foreach ($cart['notices'] as $notice): ?>
    <div class="alert alert-warning py-2"><i class="bi bi-info-circle me-1"></i><?= e($notice) ?></div>
<?php endforeach; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-person-circle me-1"></i>
                        You are checking out as a guest. <a href="login.php" class="alert-link">Login</a> to fill in your details and see this order in My Orders.
                    </div>
                <?php endif; ?>

                <form method="post" action="checkout.php" id="checkoutForm">
                    <h2 class="h6 fw-semibold mb-3">Shipping Details</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" maxlength="100" value="<?= e($form['customer_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" maxlength="20" placeholder="01XXXXXXXXX" value="<?= e($form['phone']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="shipping_address" class="form-label">Shipping Address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" placeholder="House, road, area, city" required><?= e($form['shipping_address']) ?></textarea>
                        </div>
                    </div>

                    <h2 class="h6 fw-semibold mb-3">Payment Method</h2>
                    <div class="d-grid gap-2 mb-4">
                        <?php foreach ($paymentMethods as $method => $info): ?>
                            <?php $methodId = 'pay' . md5($method); ?>
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3" for="<?= $methodId ?>" style="cursor: pointer;">
                                <input class="form-check-input mt-0" type="radio" name="payment_method" id="<?= $methodId ?>" value="<?= e($method) ?>" <?= $form['payment_method'] === $method ? 'checked' : '' ?>>
                                <i class="bi <?= $info[0] ?> fs-4 text-primary"></i>
                                <span>
                                    <span class="fw-semibold d-block"><?= e($method) ?></span>
                                    <span class="small text-body-secondary"><?= e($info[1]) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="cart.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Cart</a>
                        <button type="submit" name="action" value="continue" class="btn btn-primary flex-grow-1">
                            Continue to Payment <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6 fw-semibold mb-3">Order Summary</h2>

                <ul class="list-unstyled mb-3">
                    <?php foreach ($items as $item): ?>
                        <li class="d-flex justify-content-between gap-2 mb-2 small">
                            <span>
                                <?php if ($item['color'] !== ''): ?>
                                    <span class="color-dot" style="width: 12px; height: 12px; background-color: <?= e($item['color']) ?>;"></span>
                                <?php endif; ?>
                                <?= e($item['name']) ?> <span class="text-body-secondary">x <?= $item['quantity'] ?></span>
                            </span>
                            <span class="text-nowrap">BDT <?= number_format($item['line_total'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="d-flex justify-content-between border-top pt-3 mb-3">
                    <span class="text-body-secondary">Subtotal</span>
                    <span>BDT <?= number_format($totals['subtotal'], 2) ?></span>
                </div>

                <?php if ($coupon): ?>
                    <div class="d-flex justify-content-between align-items-center bg-success-subtle rounded px-2 py-2 mb-3">
                        <span class="small"><i class="bi bi-ticket-perforated"></i> <strong><?= e($coupon['code']) ?></strong> applied</span>
                        <button type="submit" form="checkoutForm" name="action" value="remove_coupon" class="btn btn-sm btn-link text-danger p-0" formnovalidate>Remove</button>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <label for="code" class="form-label small text-body-secondary">Have a coupon?</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control text-uppercase" id="code" name="code" form="checkoutForm" placeholder="Coupon code" maxlength="50">
                            <button type="submit" form="checkoutForm" name="action" value="apply_coupon" id="applyCouponButton" class="btn btn-outline-primary" formnovalidate>Apply</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mb-1">
                    <span class="text-body-secondary">Discount (<?= $totals['discount_percent'] ?>%)</span>
                    <span class="<?= $totals['discount'] > 0 ? 'text-success' : '' ?>">- BDT <?= number_format($totals['discount'], 2) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-5 fw-bold">
                    <span>Grand Total</span>
                    <span>BDT <?= number_format($totals['grand_total'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const couponInput = document.getElementById('code');

    if (couponInput) {
        couponInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.getElementById('applyCouponButton').click();
            }
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>
