<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';
require_once 'includes/cart_helpers.php';

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $redirect = getSafeReturnLink($_POST['return_to'] ?? '', 'wishlist.php');

    $stmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($action === 'add') {
        if (!$product) {
            setFlash('Sorry, that product could not be found.', 'danger');
        } elseif (in_array($productId, $_SESSION['wishlist'])) {
            setFlash($product['name'] . ' is already in your wishlist.', 'info');
        } else {
            $_SESSION['wishlist'][] = $productId;
            setFlash($product['name'] . ' was added to your wishlist.');
        }
    } elseif ($action === 'remove') {
        $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$productId]));
        setFlash(($product ? $product['name'] : 'The product') . ' was removed from your wishlist.');
    } elseif ($action === 'move_to_cart') {
        if (addToCart($pdo, $productId, 1)) {
            $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$productId]));
            setFlash($product['name'] . ' was moved to your cart.');
        }
    }

    header('Location: ' . $redirect);
    exit;
}

$products = [];

if (!empty($_SESSION['wishlist'])) {
    $ids = array_map('intval', $_SESSION['wishlist']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare('
        SELECT products.*, categories.name AS category_name
        FROM products
        LEFT JOIN categories ON categories.id = products.category_id
        WHERE products.id IN (' . $placeholders . ')
        ORDER BY products.name
    ');
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $_SESSION['wishlist'] = array_map('intval', array_column($products, 'id'));
}

$pageTitle = 'My Wishlist';
require_once 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">My Wishlist</h1>
        <p class="text-body-secondary mb-0"><?= count($products) ?> saved product(s)</p>
    </div>
    <a href="cart.php" class="btn btn-outline-primary"><i class="bi bi-cart3"></i> View Cart</a>
</div>

<?php if (empty($products)): ?>
    <?= renderEmptyState('Your wishlist is empty', 'Tap the heart on any product to save it here for later.', 'Browse Products', 'products.php', 'bi-heart') ?>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <a href="product_details.php?id=<?= $product['id'] ?>" class="product-card-image">
                        <img src="<?= e(getProductImage($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <div class="small text-body-secondary mb-1"><?= e($product['category_name'] ?? 'Uncategorized') ?></div>
                        <h2 class="h6 fw-semibold mb-2">
                            <a href="product_details.php?id=<?= $product['id'] ?>" class="text-body text-decoration-none"><?= e($product['name']) ?></a>
                        </h2>
                        <div class="fw-bold fs-5 mb-2">BDT <?= number_format($product['price'], 2) ?></div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <?= renderStockBadge($product['stock']) ?>
                            <span class="d-inline-flex gap-1"><?= renderColorSwatches($product['color_variants']) ?></span>
                        </div>
                        <div class="mt-auto d-flex gap-2">
                            <form method="post" action="wishlist.php" class="flex-grow-1">
                                <input type="hidden" name="action" value="move_to_cart">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm w-100" <?= $product['stock'] > 0 ? '' : 'disabled' ?>>
                                    <i class="bi bi-cart-plus"></i> <?= $product['stock'] > 0 ? 'Move to Cart' : 'Out of Stock' ?>
                                </button>
                            </form>
                            <form method="post" action="wishlist.php">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove from wishlist" aria-label="Remove <?= e($product['name']) ?> from wishlist">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
