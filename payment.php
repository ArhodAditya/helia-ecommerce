<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/cart_helpers.php';

$cart = loadCart($pdo);
$items = $cart['items'];
$coupon = $cart['coupon'];
$checkout = $_SESSION['checkout_data'] ?? null;

if (empty($items) || empty($checkout) || !empty($cart['notices'])) {
    if (!empty($cart['notices'])) {
        setFlash(implode(' ', $cart['notices']) . ' Please check your cart before paying.', 'warning');
    }

    header('Location: cart.php');
    exit;
}

$totals = calculateTotals($items, $coupon);
$isOnline = $checkout['payment_method'] === 'Demo Online Payment';
$providers = ['bKash', 'Nagad', 'Visa Demo'];
$errors = [];
$selectedProvider = 'bKash';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentLabel = 'Cash on Delivery';

    if ($isOnline) {
        $selectedProvider = $_POST['provider'] ?? '';
        $accountNumber = preg_replace('/[\s-]/', '', $_POST['account_number'] ?? '');
        $pin = trim($_POST['pin'] ?? '');

        if (!in_array($selectedProvider, $providers, true)) {
            $errors[] = 'Please choose bKash, Nagad or Visa Demo.';
            $selectedProvider = 'bKash';
        } elseif ($selectedProvider === 'Visa Demo' && !preg_match('/^[0-9]{16}$/', $accountNumber)) {
            $errors[] = 'Card number must be 16 digits.';
        } elseif ($selectedProvider !== 'Visa Demo' && !preg_match('/^01[0-9]{9}$/', $accountNumber)) {
            $errors[] = 'Account number must be an 11 digit mobile number starting with 01.';
        }

        if (!preg_match('/^[0-9]{4,6}$/', $pin)) {
            $errors[] = 'PIN / OTP must be 4 to 6 digits.';
        }

        $paymentLabel = 'Demo Online Payment (' . $selectedProvider . ')';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $lockedItems = [];
            $subtotal = 0;
            $stockStmt = $pdo->prepare('SELECT name, price, stock FROM products WHERE id = ? FOR UPDATE');

            foreach ($items as $item) {
                $stockStmt->execute([$item['id']]);
                $product = $stockStmt->fetch();

                if (!$product || $product['stock'] < $item['quantity']) {
                    $left = $product ? $product['stock'] : 0;
                    throw new Exception('Sorry, only ' . $left . ' of ' . $item['name'] . ' is left. Please update your cart.');
                }

                $item['price'] = (float) $product['price'];
                $subtotal += $item['price'] * $item['quantity'];
                $lockedItems[] = $item;
            }

            $discountPercent = $coupon ? (int) $coupon['discount_percent'] : 0;
            $grandTotal = $subtotal - round($subtotal * $discountPercent / 100, 2);

            $stmt = $pdo->prepare('
                INSERT INTO orders (user_id, customer_name, phone, shipping_address, total_amount, coupon_code, payment_method, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $checkout['customer_name'],
                $checkout['phone'],
                $checkout['shipping_address'],
                $grandTotal,
                $coupon ? $coupon['code'] : null,
                $paymentLabel,
                'Pending',
            ]);

            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, color) VALUES (?, ?, ?, ?, ?)');
            $updateStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?');

            foreach ($lockedItems as $item) {
                $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price'], $item['color']]);
                $updateStmt->execute([$item['quantity'], $item['id']]);
            }

            $pdo->commit();

            unset($_SESSION['cart'], $_SESSION['applied_coupon'], $_SESSION['checkout_data'], $_SESSION['checkout_form']);
            $_SESSION['last_order_id'] = (int) $orderId;

            header('Location: order_confirmation.php?id=' . $orderId);
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = 'Something went wrong while saving your order. Nothing was charged. Please try again.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            setFlash($e->getMessage(), 'danger');
            header('Location: cart.php');
            exit;
        }
    }
}

$pageTitle = 'Payment';
require_once 'includes/header.php';
?>

<?= renderCheckoutSteps(3) ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <h1 class="h4 fw-bold mb-4">Payment</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="small text-body-secondary">Amount to pay</div>
                            <div class="display-6 fw-bold text-primary">BDT <?= number_format($totals['grand_total'], 2) ?></div>
                            <span class="badge text-bg-secondary"><?= e($checkout['payment_method']) ?></span>
                        </div>

                        <?php if (!$isOnline): ?>
                            <h2 class="h6 fw-semibold"><i class="bi bi-cash-coin text-success"></i> Cash on Delivery</h2>
                            <ul class="small text-body-secondary">
                                <li>Pay the delivery person in cash when your parcel arrives.</li>
                                <li>Please keep the exact amount ready if you can.</li>
                                <li>We may call <?= e($checkout['phone']) ?> to confirm your order before shipping.</li>
                                <li>You can check your parcel any time on the Track Order page.</li>
                            </ul>
                            <form method="post" action="payment.php">
                                <button type="submit" class="btn btn-success w-100 btn-lg">
                                    <i class="bi bi-check2-circle"></i> Confirm COD Order
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning small py-2">
                                <i class="bi bi-shield-exclamation me-1"></i>
                                This is a <strong>demo</strong> payment page for our project. No real money is taken and the numbers you type are <strong>not saved</strong>. Please use test numbers only.
                            </div>

                            <form method="post" action="payment.php" autocomplete="off">
                                <div class="mb-3">
                                    <span class="form-label d-block">Pay with</span>
                                    <div class="btn-group w-100" role="group" aria-label="Payment provider">
                                        <?php foreach ($providers as $index => $provider): ?>
                                            <input type="radio" class="btn-check" name="provider" id="provider<?= $index ?>" value="<?= $provider ?>" <?= $selectedProvider === $provider ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-primary" for="provider<?= $index ?>"><?= $provider ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="account_number" class="form-label" id="accountLabel">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" inputmode="numeric" maxlength="19" placeholder="01XXXXXXXXX" required>
                                    <div class="form-text" id="accountHelp">Your 11 digit mobile account number.</div>
                                </div>
                                <div class="mb-4">
                                    <label for="pin" class="form-label">Demo PIN / OTP</label>
                                    <input type="password" class="form-control" id="pin" name="pin" inputmode="numeric" maxlength="6" placeholder="4 to 6 digits" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="bi bi-lock"></i> Pay &amp; Confirm Order
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h2 class="h6 fw-semibold mb-0">Shipping To</h2>
                            <a href="checkout.php" class="small">Edit</a>
                        </div>
                        <div class="fw-semibold"><?= e($checkout['customer_name']) ?></div>
                        <div class="small text-body-secondary"><?= e($checkout['phone']) ?></div>
                        <div class="small text-body-secondary"><?= nl2br(e($checkout['shipping_address'])) ?></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3">Order Summary</h2>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between gap-2 small mb-2">
                                <span><?= e($item['name']) ?> <span class="text-body-secondary">x <?= $item['quantity'] ?></span></span>
                                <span class="text-nowrap">BDT <?= number_format($item['line_total'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between small border-top pt-2 mt-2">
                            <span class="text-body-secondary">Subtotal</span>
                            <span>BDT <?= number_format($totals['subtotal'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-body-secondary">Discount<?= $coupon ? ' (' . e($coupon['code']) . ', ' . $totals['discount_percent'] . '%)' : '' ?></span>
                            <span class="text-success">- BDT <?= number_format($totals['discount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mt-2">
                            <span>Grand Total</span>
                            <span>BDT <?= number_format($totals['grand_total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isOnline): ?>
    <script>
        const providerInputs = document.querySelectorAll('input[name="provider"]');
        const accountLabel = document.getElementById('accountLabel');
        const accountInput = document.getElementById('account_number');
        const accountHelp = document.getElementById('accountHelp');

        function updateAccountField() {
            const provider = document.querySelector('input[name="provider"]:checked').value;

            if (provider === 'Visa Demo') {
                accountLabel.textContent = 'Card Number';
                accountInput.placeholder = '4111 1111 1111 1111';
                accountHelp.textContent = 'A 16 digit demo card number.';
            } else {
                accountLabel.textContent = provider + ' Account Number';
                accountInput.placeholder = '01XXXXXXXXX';
                accountHelp.textContent = 'Your 11 digit mobile account number.';
            }
        }

        providerInputs.forEach(function (input) {
            input.addEventListener('change', updateAccountField);
        });

        updateAccountField();
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
