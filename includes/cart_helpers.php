<?php
function setFlash($message, $type = 'success')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getSafeReturnLink($link, $default)
{
    if (preg_match('/^[a-z_]+\.php(\?[^\s]*)?$/i', (string) $link)) {
        return $link;
    }

    return $default;
}

function addToCart($pdo, $productId, $quantity = 1, $color = '')
{
    $stmt = $pdo->prepare('SELECT id, name, stock, color_variants FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlash('Sorry, that product could not be found.', 'danger');
        return false;
    }

    if ($product['stock'] <= 0) {
        setFlash($product['name'] . ' is out of stock right now.', 'danger');
        return false;
    }

    $colors = getColorList($product['color_variants']);
    $color = strtoupper(trim((string) $color));

    if (!in_array($color, $colors)) {
        $color = $colors[0] ?? '';
    }

    $id = (int) $product['id'];
    $quantity = max(1, (int) $quantity);
    $currentQuantity = $_SESSION['cart'][$id]['quantity'] ?? 0;
    $newQuantity = $currentQuantity + $quantity;

    if ($newQuantity > $product['stock']) {
        $newQuantity = (int) $product['stock'];
        setFlash('Only ' . $product['stock'] . ' of ' . $product['name'] . ' in stock, so your cart now has the maximum.', 'warning');
    } else {
        setFlash($product['name'] . ' was added to your cart.');
    }

    $_SESSION['cart'][$id] = [
        'quantity' => $newQuantity,
        'color' => $color,
    ];

    return true;
}

function findValidCoupon($pdo, $code)
{
    $stmt = $pdo->prepare('
        SELECT code, discount_percent
        FROM coupons
        WHERE code = ? AND is_active = 1 AND expiry_date >= CURDATE()
    ');
    $stmt->execute([strtoupper(trim((string) $code))]);

    return $stmt->fetch();
}

function applyCoupon($pdo, $code)
{
    $code = strtoupper(trim((string) $code));

    if ($code === '') {
        setFlash('Please enter a coupon code.', 'danger');
        return;
    }

    $coupon = findValidCoupon($pdo, $code);

    if ($coupon) {
        $_SESSION['applied_coupon'] = [
            'code' => $coupon['code'],
            'discount_percent' => (int) $coupon['discount_percent'],
        ];
        setFlash('Coupon ' . $coupon['code'] . ' applied. You get ' . $coupon['discount_percent'] . '% off!');
    } else {
        setFlash('Coupon "' . $code . '" is not valid or has expired.', 'danger');
    }
}

function loadCart($pdo)
{
    $cart = $_SESSION['cart'] ?? [];
    $items = [];
    $notices = [];
    $coupon = null;

    if (!empty($cart)) {
        $ids = array_map('intval', array_keys($cart));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare('SELECT id, name, price, stock, image FROM products WHERE id IN (' . $placeholders . ')');
        $stmt->execute($ids);

        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[$row['id']] = $row;
        }

        foreach ($cart as $id => $entry) {
            if (!isset($products[$id])) {
                unset($_SESSION['cart'][$id]);
                $notices[] = 'A product in your cart is no longer sold, so it was removed.';
                continue;
            }

            $product = $products[$id];

            if ($product['stock'] <= 0) {
                unset($_SESSION['cart'][$id]);
                $notices[] = $product['name'] . ' is now out of stock, so it was removed from your cart.';
                continue;
            }

            $quantity = (int) $entry['quantity'];

            if ($quantity > $product['stock']) {
                $quantity = (int) $product['stock'];
                $_SESSION['cart'][$id]['quantity'] = $quantity;
                $notices[] = 'Only ' . $quantity . ' of ' . $product['name'] . ' left, so we changed the quantity.';
            }

            $items[] = [
                'id' => (int) $id,
                'name' => $product['name'],
                'price' => (float) $product['price'],
                'stock' => (int) $product['stock'],
                'image' => $product['image'],
                'color' => $entry['color'] ?? '',
                'quantity' => $quantity,
                'line_total' => $quantity * $product['price'],
            ];
        }
    }

    if (isset($_SESSION['applied_coupon'])) {
        $coupon = findValidCoupon($pdo, $_SESSION['applied_coupon']['code']);

        if ($coupon) {
            $_SESSION['applied_coupon'] = [
                'code' => $coupon['code'],
                'discount_percent' => (int) $coupon['discount_percent'],
            ];
            $coupon = $_SESSION['applied_coupon'];
        } else {
            unset($_SESSION['applied_coupon']);
            $coupon = null;
            $notices[] = 'Your coupon has expired or was turned off, so it was removed.';
        }
    }

    return [
        'items' => $items,
        'notices' => $notices,
        'coupon' => $coupon,
    ];
}

function calculateTotals($items, $coupon)
{
    $subtotal = 0;

    foreach ($items as $item) {
        $subtotal += $item['line_total'];
    }

    $discountPercent = $coupon ? (int) $coupon['discount_percent'] : 0;
    $discount = round($subtotal * $discountPercent / 100, 2);

    return [
        'subtotal' => $subtotal,
        'discount_percent' => $discountPercent,
        'discount' => $discount,
        'grand_total' => $subtotal - $discount,
    ];
}

function renderCheckoutSteps($currentStep)
{
    $steps = ['Cart', 'Checkout', 'Payment', 'Done'];
    $html = '<div class="d-flex justify-content-center gap-2 gap-sm-4 mb-4 small flex-wrap">';

    foreach ($steps as $index => $step) {
        $number = $index + 1;

        if ($number < $currentStep) {
            $html .= '<span class="text-success"><i class="bi bi-check-circle-fill"></i> ' . $step . '</span>';
        } elseif ($number === $currentStep) {
            $html .= '<span class="fw-semibold text-primary"><span class="badge rounded-pill text-bg-primary">' . $number . '</span> ' . $step . '</span>';
        } else {
            $html .= '<span class="text-body-secondary"><span class="badge rounded-pill text-bg-secondary">' . $number . '</span> ' . $step . '</span>';
        }
    }

    $html .= '</div>';

    return $html;
}
