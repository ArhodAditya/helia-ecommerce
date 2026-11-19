<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';
require_once 'includes/brand.php';

$categories = $pdo->query('
    SELECT categories.id, categories.name, COUNT(products.id) AS product_count
    FROM categories
    LEFT JOIN products ON products.category_id = categories.id
    GROUP BY categories.id, categories.name
    ORDER BY categories.name
')->fetchAll();

$trendingProducts = $pdo->query('
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON categories.id = products.category_id
    WHERE products.is_trending = 1
    ORDER BY products.id DESC
    LIMIT 8
')->fetchAll();

$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalReviews = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();

$pageTitle = 'Home';
require_once 'includes/header.php';
?>

<section class="hero mb-4 mb-md-5">
    <div class="row align-items-center g-4 g-xl-5">
        <div class="col-lg-7">
            <span class="hero-eyebrow"><?= heliaMark('', 22) ?>Helia &middot; Smart Online Shopping Hub</span>
            <h1 class="hero-title">From cricket bats to Jamdani, <span>all in one cart.</span></h1>
            <p class="hero-lead">Gadgets, sportswear, fashion, home essentials, books and personal care for shoppers across Bangladesh. Check live stock, pay your way and track every parcel to your door.</p>

            <form method="GET" action="search.php" class="hero-search" role="search">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" name="q" placeholder="Search earbuds, panjabi, kettle..." aria-label="Search products" required>
                    <button class="btn btn-sun" type="submit">Search</button>
                </div>
            </form>

            <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3">
                <a href="products.php" class="btn btn-ghost-light">
                    <i class="bi bi-bag me-1"></i> Shop All Products
                </a>
                <a href="order_tracking.php" class="hero-link">
                    <i class="bi bi-truck"></i> Track an order
                </a>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="hero-panel">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="hero-stat">
                            <div class="hero-stat-value"><?= $totalProducts ?></div>
                            <div class="hero-stat-label">Products</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="hero-stat">
                            <div class="hero-stat-value"><?= count($categories) ?></div>
                            <div class="hero-stat-label">Categories</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="hero-stat">
                            <div class="hero-stat-value"><?= $totalReviews ?></div>
                            <div class="hero-stat-label">Reviews</div>
                        </div>
                    </div>
                </div>

                <div class="hero-perks">
                    <div class="d-flex align-items-center gap-3">
                        <span class="hero-perk-icon"><i class="bi bi-wallet2"></i></span>
                        <div>
                            <div class="hero-perk-title">Pay your way</div>
                            <div class="hero-perk-text">Cash on Delivery, bKash or Nagad</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="hero-perk-icon"><i class="bi bi-box-seam"></i></span>
                        <div>
                            <div class="hero-perk-title">Live stock</div>
                            <div class="hero-perk-text">See what is available before you order</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="hero-perk-icon"><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <div class="hero-perk-title">Track every order</div>
                            <div class="hero-perk-text">From Pending to Delivered, step by step</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="section-head">
        <div>
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-sub">Pick a department to see everything in it.</p>
        </div>
        <a href="categories.php" class="section-link">All categories <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (empty($categories)): ?>
        <p class="text-body-secondary">No categories yet.</p>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3">
            <?php foreach ($categories as $category): ?>
                <div class="col">
                    <a href="categories.php?id=<?= $category['id'] ?>" class="card category-card category-tile h-100 text-decoration-none">
                        <div class="card-body">
                            <?= categoryIcon($category['name'], null, 28) ?>
                            <i class="bi bi-arrow-up-right category-go" aria-hidden="true"></i>
                            <div>
                                <div class="category-name"><?= e($category['name']) ?></div>
                                <div class="category-count"><?= $category['product_count'] ?> product(s)</div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <div class="section-head">
        <div>
            <h2 class="section-title">Trending Products</h2>
            <p class="section-sub">Popular picks shoppers keep coming back for.</p>
        </div>
        <a href="products.php" class="section-link">View all <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (empty($trendingProducts)): ?>
        <?= renderEmptyState('No trending products right now', 'Check back soon, or browse everything we have.', 'Shop All Products', 'products.php', 'bi-fire') ?>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 product-grid">
            <?php foreach ($trendingProducts as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
