<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';

$productId = $_GET['id'] ?? '';

if (!ctype_digit((string) $productId)) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON categories.id = products.category_id
    WHERE products.id = ?
');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$colors = getColorList($product['color_variants']);
$inStock = $product['stock'] > 0;

$stmt = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM reviews WHERE product_id = ?');
$stmt->execute([$product['id']]);
$reviewStats = $stmt->fetch();
$averageRating = round($reviewStats['average'], 1);

$stmt = $pdo->prepare('
    SELECT user_name, rating, comment, created_at
    FROM reviews
    WHERE product_id = ?
    ORDER BY created_at DESC, id DESC
    LIMIT 5
');
$stmt->execute([$product['id']]);
$latestReviews = $stmt->fetchAll();

$pageTitle = $product['name'];
require_once 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
        <?php if (!empty($product['category_id'])): ?>
            <li class="breadcrumb-item"><a href="categories.php?id=<?= $product['category_id'] ?>"><?= e($product['category_name']) ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
    </ol>
</nav>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="row g-4">
            <div class="col-md-6">
                <?= renderProductImage($product['image'], $product['name'], 'product-main-image', 'xl', '', false) ?>
            </div>

            <div class="col-md-6">
                <?php if (!empty($product['category_id'])): ?>
                    <a href="categories.php?id=<?= $product['category_id'] ?>" class="badge text-bg-primary text-decoration-none mb-2"><?= e($product['category_name']) ?></a>
                <?php else: ?>
                    <span class="badge text-bg-secondary mb-2">Uncategorized</span>
                <?php endif; ?>
                <?php if ($product['is_trending']): ?>
                    <span class="badge text-bg-danger mb-2"><i class="bi bi-fire"></i> Trending</span>
                <?php endif; ?>

                <h1 class="h3 fw-bold mb-2"><?= e($product['name']) ?></h1>

                <a href="#reviews" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3">
                    <span class="stars"><?= renderStars($averageRating) ?></span>
                    <span class="small text-body-secondary"><?= number_format($averageRating, 1) ?> (<?= $reviewStats['total'] ?> review<?= $reviewStats['total'] == 1 ? '' : 's' ?>)</span>
                </a>

                <div class="display-6 fw-bold mb-3">BDT <?= number_format($product['price'], 2) ?></div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <?= renderStockBadge($product['stock']) ?>
                    <span class="small text-body-secondary">
                        <?php if ($product['stock'] > 5): ?>
                            <?= $product['stock'] ?> items available
                        <?php elseif ($inStock): ?>
                            Hurry, only <?= $product['stock'] ?> left!
                        <?php else: ?>
                            This product is currently unavailable.
                        <?php endif; ?>
                    </span>
                </div>

                <h2 class="h6 fw-semibold">Description</h2>
                <p class="text-body-secondary"><?= !empty($product['description']) ? nl2br(e($product['description'])) : 'No description added yet.' ?></p>

                <form method="post" action="cart.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <?php if (!empty($colors)): ?>
                        <div class="mb-3">
                            <div class="small fw-semibold mb-2">Color: <span id="selectedColor" class="text-body-secondary"><?= $colors[0] ?></span></div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($colors as $index => $color): ?>
                                    <input type="radio" class="btn-check" name="color" id="color<?= $index ?>" value="<?= $color ?>" autocomplete="off" <?= $index === 0 ? 'checked' : '' ?>>
                                    <label class="color-option <?= $index === 0 ? 'selected' : '' ?>" for="color<?= $index ?>" style="background-color: <?= $color ?>;" title="<?= $color ?>">
                                        <span class="visually-hidden"><?= $color ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <label for="quantity" class="form-label small fw-semibold">Quantity</label>
                    <div class="d-flex flex-wrap gap-2">
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= max(1, $product['stock']) ?>" style="width: 100px;" <?= $inStock ? '' : 'disabled' ?>>
                        <button type="submit" class="btn btn-primary flex-grow-1" <?= $inStock ? '' : 'disabled' ?>>
                            <i class="bi bi-cart-plus"></i> <?= $inStock ? 'Add to Cart' : 'Out of Stock' ?>
                        </button>
                    </div>
                </form>

                <?php $inWishlist = in_array((int) $product['id'], $_SESSION['wishlist'] ?? []); ?>
                <form method="post" action="wishlist.php" class="mt-2">
                    <input type="hidden" name="action" value="<?= $inWishlist ? 'remove' : 'add' ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="return_to" value="product_details.php?id=<?= $product['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi <?= $inWishlist ? 'bi-heart-fill' : 'bi-heart' ?>"></i> <?= $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" id="reviews">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                <span class="display-6 fw-bold"><?= number_format($averageRating, 1) ?></span>
                <div>
                    <div class="stars fs-5"><?= renderStars($averageRating) ?></div>
                    <div class="small text-body-secondary">Based on <?= $reviewStats['total'] ?> review(s)</div>
                </div>
            </div>
            <a href="product_reviews.php?product_id=<?= $product['id'] ?>" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Write a Review
            </a>
        </div>

        <h2 class="h6 fw-semibold mb-0">Latest Reviews</h2>

        <?php if (empty($latestReviews)): ?>
            <p class="text-body-secondary mt-3 mb-0">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>

        <?php foreach ($latestReviews as $review): ?>
            <div class="d-flex gap-3 py-3 border-bottom">
                <div class="review-avatar"><?= e(mb_strtoupper(mb_substr($review['user_name'], 0, 1))) ?></div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <span class="fw-semibold"><?= e($review['user_name']) ?></span>
                            <span class="stars small ms-1"><?= renderStars($review['rating']) ?></span>
                        </div>
                        <span class="small text-body-secondary"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <p class="mb-0 mt-1"><?= nl2br(e($review['comment'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($reviewStats['total'] > count($latestReviews)): ?>
            <a href="product_reviews.php?product_id=<?= $product['id'] ?>" class="btn btn-link px-0 mt-2">
                See all <?= $reviewStats['total'] ?> reviews <i class="bi bi-arrow-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
    const colorInputs = document.querySelectorAll('input[name="color"]');
    const selectedColorText = document.getElementById('selectedColor');

    colorInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('.color-option').forEach(function (label) {
                label.classList.remove('selected');
            });

            document.querySelector('label[for="' + input.id + '"]').classList.add('selected');
            selectedColorText.textContent = input.value;
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
