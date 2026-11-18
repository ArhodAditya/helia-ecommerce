<?php
require_once 'auth_check.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    $product = $stmt->fetch();

    if ($product) {
        if (!empty($product['image']) && file_exists('../' . $product['image'])) {
            unlink('../' . $product['image']);
        }

        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);

        $_SESSION['message'] = 'Product deleted successfully.';
        $_SESSION['message_type'] = 'success';
    }

    header('Location: products.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';
$stockFilter = $_GET['stock'] ?? '';

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

if ($stockFilter === 'in') {
    $sql .= ' AND products.stock > 5';
} elseif ($stockFilter === 'low') {
    $sql .= ' AND products.stock BETWEEN 1 AND 5';
} elseif ($stockFilter === 'out') {
    $sql .= ' AND products.stock <= 0';
}

$sql .= ' ORDER BY products.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Products';
require_once 'includes/header.php';
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label small text-body-secondary">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Search by product name" value="<?= e($search) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label for="category" class="form-label small text-body-secondary">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="stock" class="form-label small text-body-secondary">Stock</label>
                <select class="form-select" id="stock" name="stock">
                    <option value="">All stock</option>
                    <option value="in" <?= $stockFilter === 'in' ? 'selected' : '' ?>>In Stock</option>
                    <option value="low" <?= $stockFilter === 'low' ? 'selected' : '' ?>>Low Stock</option>
                    <option value="out" <?= $stockFilter === 'out' ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i> Filter</button>
                <a href="products.php" class="btn btn-outline-secondary" title="Reset filters"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 fw-semibold mb-0"><?= count($products) ?> product(s) found</h2>
            <a href="product_form.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Colors</th>
                        <th>Trending</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-body-secondary py-4">No products found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($products as $product): ?>
                        <?php
                        $colors = [];
                        foreach (explode(',', (string) $product['color_variants']) as $color) {
                            $color = trim($color);
                            if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                                $colors[] = $color;
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="product-thumb">
                                <?php else: ?>
                                    <div class="product-thumb thumb-placeholder"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= e($product['name']) ?></td>
                            <td><?= e($product['category_name'] ?? 'Uncategorized') ?></td>
                            <td class="text-nowrap">BDT <?= number_format($product['price'], 2) ?></td>
                            <td class="text-nowrap">
                                <span class="me-1"><?= $product['stock'] ?></span>
                                <?php if ($product['stock'] > 5): ?>
                                    <span class="badge text-bg-success">In Stock</span>
                                <?php elseif ($product['stock'] >= 1): ?>
                                    <span class="badge text-bg-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if (empty($colors)): ?>
                                    <span class="text-body-secondary">-</span>
                                <?php endif; ?>
                                <?php foreach ($colors as $color): ?>
                                    <span class="color-dot" style="background-color: <?= $color ?>;" title="<?= $color ?>"></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php if ($product['is_trending']): ?>
                                    <span class="badge text-bg-primary"><i class="bi bi-fire"></i> Trending</span>
                                <?php else: ?>
                                    <span class="text-body-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="product_form.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="delete_id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
