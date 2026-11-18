<?php
$returnTo = basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$inWishlist = in_array((int) $product['id'], $_SESSION['wishlist'] ?? []);
?>
<div class="col">
    <div class="card product-card h-100 border-0 shadow-sm">
        <a href="product_details.php?id=<?= $product['id'] ?>" class="product-card-image">
            <img src="<?= e(getProductImage($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
            <?php if (!empty($product['is_trending'])): ?>
                <span class="badge text-bg-primary position-absolute top-0 start-0 m-2"><i class="bi bi-fire"></i> Trending</span>
            <?php endif; ?>
        </a>
        <div class="card-body d-flex flex-column">
            <div class="small text-body-secondary mb-1"><?= e($product['category_name'] ?? 'Uncategorized') ?></div>
            <h3 class="h6 fw-semibold mb-2">
                <a href="product_details.php?id=<?= $product['id'] ?>" class="text-body text-decoration-none"><?= e($product['name']) ?></a>
            </h3>
            <div class="fw-bold fs-5 mb-2">BDT <?= number_format($product['price'], 2) ?></div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <?= renderStockBadge($product['stock']) ?>
                <span class="d-inline-flex gap-1"><?= renderColorSwatches($product['color_variants']) ?></span>
            </div>
            <div class="mt-auto">
                <div class="d-flex gap-2 mb-2">
                    <form method="post" action="cart.php" class="flex-grow-1">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <button type="submit" class="btn btn-primary btn-sm w-100" <?= $product['stock'] > 0 ? '' : 'disabled' ?>>
                            <i class="bi bi-cart-plus"></i> <?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                        </button>
                    </form>
                    <form method="post" action="wishlist.php">
                        <input type="hidden" name="action" value="<?= $inWishlist ? 'remove' : 'add' ?>">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>" aria-label="<?= $inWishlist ? 'Remove' : 'Add' ?> <?= e($product['name']) ?> <?= $inWishlist ? 'from' : 'to' ?> wishlist">
                            <i class="bi <?= $inWishlist ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                    </form>
                </div>
                <a href="product_details.php?id=<?= $product['id'] ?>" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-eye"></i> View Details
                </a>
            </div>
        </div>
    </div>
</div>
