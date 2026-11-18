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

$selectedId = $_GET['id'] ?? '';
$selectedCategory = null;

foreach ($categories as $category) {
    if ($category['id'] == $selectedId) {
        $selectedCategory = $category;
    }
}

if ($selectedId !== '' && !$selectedCategory) {
    header('Location: categories.php');
    exit;
}

if ($selectedCategory) {
    $stmt = $pdo->prepare('
        SELECT products.*, categories.name AS category_name
        FROM products
        LEFT JOIN categories ON categories.id = products.category_id
        WHERE products.category_id = ?
        ORDER BY products.id DESC
    ');
    $stmt->execute([$selectedCategory['id']]);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo->query('
        SELECT products.*, categories.name AS category_name
        FROM products
        LEFT JOIN categories ON categories.id = products.category_id
        ORDER BY products.id DESC
    ')->fetchAll();
}

$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

$pageTitle = $selectedCategory ? $selectedCategory['name'] : 'Categories';
require_once 'includes/header.php';
?>

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Browse by Category</h1>
    <p class="text-body-secondary mb-0">Pick a category to see its products.</p>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-5">
    <div class="col">
        <a href="categories.php" class="card category-card h-100 shadow-sm text-decoration-none <?= !$selectedCategory ? 'active' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="category-icon"><i class="bi bi-grid"></i></span>
                <div>
                    <div class="fw-semibold text-body">All Products</div>
                    <div class="small text-body-secondary"><?= $totalProducts ?> product(s)</div>
                </div>
            </div>
        </a>
    </div>
    <?php foreach ($categories as $category): ?>
        <div class="col">
            <a href="categories.php?id=<?= $category['id'] ?>" class="card category-card h-100 shadow-sm text-decoration-none <?= $selectedCategory && $selectedCategory['id'] == $category['id'] ? 'active' : '' ?>">
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 fw-bold mb-0"><?= $selectedCategory ? e($selectedCategory['name']) : 'All Products' ?></h2>
    <span class="text-body-secondary small"><?= count($products) ?> product(s)</span>
</div>

<?php if (empty($products)): ?>
    <?= renderEmptyState('No products here yet', 'There are no products in this category yet. Please check back soon.', 'Browse All Products', 'products.php', 'bi-box-seam') ?>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($products as $product): ?>
            <?php include 'includes/product_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
