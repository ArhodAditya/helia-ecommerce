<?php
session_start();
require_once 'config/db.php';
require_once 'includes/product_helpers.php';
require_once 'includes/ui_helpers.php';

$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';
$minPrice = trim($_GET['min_price'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');

if ($categoryFilter !== '' && !ctype_digit((string) $categoryFilter)) {
    $categoryFilter = '';
}

if ($minPrice !== '' && (!is_numeric($minPrice) || $minPrice < 0)) {
    $minPrice = '';
}

if ($maxPrice !== '' && (!is_numeric($maxPrice) || $maxPrice < 0)) {
    $maxPrice = '';
}

if ($minPrice !== '' && $maxPrice !== '' && $minPrice > $maxPrice) {
    $temp = $minPrice;
    $minPrice = $maxPrice;
    $maxPrice = $temp;
}

$sql = 'SELECT products.*, categories.name AS category_name
        FROM products
        LEFT JOIN categories ON categories.id = products.category_id
        WHERE 1 = 1';
$params = [];

if ($search !== '') {
    $sql .= ' AND products.name LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($categoryFilter !== '') {
    $sql .= ' AND products.category_id = ?';
    $params[] = $categoryFilter;
}

if ($minPrice !== '') {
    $sql .= ' AND products.price >= ?';
    $params[] = $minPrice;
}

if ($maxPrice !== '') {
    $sql .= ' AND products.price <= ?';
    $params[] = $maxPrice;
}

$sql .= ' ORDER BY products.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$isFiltered = $search !== '' || $categoryFilter !== '' || $minPrice !== '' || $maxPrice !== '';

$pageTitle = 'Products';
require_once 'includes/header.php';
?>

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">All Products</h1>
    <p class="text-body-secondary mb-0">Use the filters to find exactly what you want.</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="products.php" class="row g-2 align-items-end needs-loading">
            <div class="col-md-6 col-xl-3">
                <label for="search" class="form-label small text-body-secondary">Product Name</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="e.g. Watch" value="<?= e($search) ?>">
            </div>
            <div class="col-md-6 col-xl-3">
                <label for="category" class="form-label small text-body-secondary">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <label for="min_price" class="form-label small text-body-secondary">Min Price (BDT)</label>
                <input type="number" class="form-control" id="min_price" name="min_price" min="0" step="0.01" placeholder="0" value="<?= e($minPrice) ?>">
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <label for="max_price" class="form-label small text-body-secondary">Max Price (BDT)</label>
                <input type="number" class="form-control" id="max_price" name="max_price" min="0" step="0.01" placeholder="Any" value="<?= e($maxPrice) ?>">
            </div>
            <div class="col-md-4 col-xl-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 text-nowrap"><i class="bi bi-funnel"></i> Filter</button>
                <a href="products.php" class="btn btn-outline-secondary text-nowrap">Reset</a>
            </div>
            <div class="col-12 pt-1">
                <span class="small text-body-secondary">
                    <?= count($products) ?> product(s) found<?= $isFiltered ? ' with your filters' : '' ?>
                </span>
            </div>
        </form>
    </div>
</div>

<?php if (empty($products)): ?>
    <?php if ($isFiltered): ?>
        <?= renderEmptyState('No products match your filters', 'Try a different name, category or price range.', 'Reset Filters', 'products.php', 'bi-funnel') ?>
    <?php else: ?>
        <?= renderEmptyState('No products yet', 'New products will appear here as soon as they are added.', '', '', 'bi-box-seam') ?>
    <?php endif; ?>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($products as $product): ?>
            <?php include 'includes/product_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
