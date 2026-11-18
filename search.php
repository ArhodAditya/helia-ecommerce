<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';

$keyword = trim($_GET['q'] ?? '');
$products = [];

if ($keyword !== '') {
    $stmt = $pdo->prepare('
        SELECT products.*, categories.name AS category_name
        FROM products
        LEFT JOIN categories ON categories.id = products.category_id
        WHERE products.name LIKE ? OR products.description LIKE ?
        ORDER BY products.name
    ');
    $stmt->execute(['%' . $keyword . '%', '%' . $keyword . '%']);
    $products = $stmt->fetchAll();
}

$pageTitle = $keyword !== '' ? 'Search: ' . $keyword : 'Search';
require_once 'includes/header.php';
?>

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Search Results</h1>
    <?php if ($keyword !== ''): ?>
        <p class="text-body-secondary mb-0">
            <?= count($products) ?> result(s) found for <strong class="text-body">"<?= e($keyword) ?>"</strong>
        </p>
    <?php else: ?>
        <p class="text-body-secondary mb-0">Type a product name or a word from its description.</p>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="search.php" role="search" class="needs-loading">
            <div class="input-group">
                <input type="search" class="form-control" name="q" placeholder="Search products..." value="<?= e($keyword) ?>" aria-label="Search products" required>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<?php if ($keyword !== '' && empty($products)): ?>
    <?= renderEmptyState('No products found', 'We couldn\'t find anything for "' . $keyword . '". Try another word or browse everything.', 'Browse All Products', 'products.php', 'bi-emoji-frown') ?>
<?php elseif (!empty($products)): ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($products as $product): ?>
            <?php include 'includes/product_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
