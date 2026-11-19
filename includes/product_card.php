<?php
$returnTo = basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$inWishlist = in_array((int) $product['id'], $_SESSION['wishlist'] ?? []);
?>
<div class="col">
    <article class="card product-card h-100">
        <a href="product_details.php?id=<?= $product['id'] ?>" class="product-card-image" tabindex="-1" aria-hidden="true">
            <?= renderProductImage($product['image'], $product['name'], '', 'lg') ?>
            <?php if (!empty($product['is_trending'])): ?>
                <span class="product-flag"><i class="bi bi-fire"></i> Trending</span>
            <?php endif; ?>
        </a>
        <div class="card-body d-flex flex-column">
            <div class="product-card-category"><?= e($product['category_name'] ?? 'Uncategorized') ?></div>
            <h3 class="product-card-title">
                <a href="product_details.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a>
            </h3>
            <div class="product-card-price"><small>BDT</small><?= number_format($product['price'], 2) ?></div>
            <div class="product-card-meta">
                <?= renderStockBadge($product['stock']) ?>
                <span class="d-inline-flex gap-1"><?= renderColorSwatches($product['color_variants']) ?></span>
            </div>
            <div class="mt-auto">
                <div class="d-flex gap-2">
                    <form method="post" action="cart.php" class="flex-grow-1">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <button type="submit" class="btn btn-primary btn-sm w-100 text-nowrap" <?= $product['stock'] > 0 ? '' : 'disabled' ?>>
                            <?php if ($product['stock'] > 0): ?>
                                <i class="bi bi-cart-plus"></i> Add<span class="d-none d-sm-inline"> to Cart</span>
                            <?php else: ?>
                                <span class="d-sm-none">Sold Out</span><span class="d-none d-sm-inline">Out of Stock</span>
                            <?php endif; ?>
                        </button>
                    </form>
                    <form method="post" action="wishlist.php">
                        <input type="hidden" name="action" value="<?= $inWishlist ? 'remove' : 'add' ?>">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>" aria-label="<?= $inWishlist ? 'Remove' : 'Add' ?> <?= e($product['name']) ?> <?= $inWishlist ? 'from' : 'to' ?> wishlist">
                            <i class="bi <?= $inWishlist ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                    </form>
                </div>
                <a href="product_details.php?id=<?= $product['id'] ?>" class="product-card-link">
                    View Details <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </article>
</div>
