<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';

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

<section class="hero shadow-sm p-4 p-md-5 mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="badge rounded-pill text-bg-warning mb-3"><i class="bi bi-stars"></i> Welcome to Helia</span>
            <h1 class="display-5 fw-bold mb-3">Helia: Smart Online Shopping Hub</h1>
            <p class="lead text-body-secondary mb-4">Find electronics, fashion and more in one place. Search for what you need, compare prices and check stock before you buy.</p>

            <form method="GET" action="search.php" class="mb-3" role="search">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-body"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" name="q" placeholder="What are you looking for?" aria-label="Search products" required>
                    <button class="btn btn-primary px-4" type="submit">Search</button>
                </div>
            </form>

            <a href="products.php" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-bag"></i> Shop All Products
            </a>
        </div>
        <div class="col-lg-5">
            <div class="row g-3 text-center">
                <div class="col-4 col-lg-12">
                    <div class="bg-body rounded-4 shadow-sm p-3">
                        <div class="h3 fw-bold text-primary mb-0"><?= $totalProducts ?></div>
                        <div class="small text-body-secondary">Products</div>
                    </div>
                </div>
                <div class="col-4 col-lg-6">
                    <div class="bg-body rounded-4 shadow-sm p-3">
                        <div class="h3 fw-bold text-primary mb-0"><?= count($categories) ?></div>
                        <div class="small text-body-secondary">Categories</div>
                    </div>
                </div>
                <div class="col-4 col-lg-6">
                    <div class="bg-body rounded-4 shadow-sm p-3">
                        <div class="h3 fw-bold text-primary mb-0"><?= $totalReviews ?></div>
                        <div class="small text-body-secondary">Reviews</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-bold mb-0">Shop by Category</h2>
        <a href="categories.php" class="text-decoration-none">All categories <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (empty($categories)): ?>
        <p class="text-body-secondary">No categories yet.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
            <?php foreach ($categories as $category): ?>
                <div class="col">
                    <a href="categories.php?id=<?= $category['id'] ?>" class="card category-card h-100 shadow-sm text-decoration-none">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="category-icon"><i class="bi bi-tag"></i></span>
                            <div>
                                <div class="fw-semibold text-body"><?= e($category['name']) ?></div>
                                <div class="small text-body-secondary"><?= $category['product_count'] ?> product(s)</div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-bold mb-0"><i class="bi bi-fire text-danger"></i> Trending Products</h2>
        <a href="products.php" class="text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (empty($trendingProducts)): ?>
        <?= renderEmptyState('No trending products right now', 'Check back soon, or browse everything we have.', 'Shop All Products', 'products.php', 'bi-fire') ?>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($trendingProducts as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
