<?php
require_once 'auth_check.php';
require_once '../config/db.php';

$error = '';
$editId = '';
$formName = '';

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $category = $stmt->fetch();

    if ($category) {
        $editId = $category['id'];
        $formName = $category['name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);

        $_SESSION['message'] = 'Category deleted successfully.';
        $_SESSION['message_type'] = 'success';
        header('Location: categories.php');
        exit;
    }

    $editId = $_POST['id'] ?? '';
    $formName = trim($_POST['name'] ?? '');

    if ($formName === '') {
        $error = 'Category name is required.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?');
        $stmt->execute([$formName, $editId === '' ? 0 : $editId]);

        if ($stmt->fetchColumn() > 0) {
            $error = 'A category with this name already exists.';
        }
    }

    if ($error === '') {
        if ($editId === '') {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$formName]);
            $_SESSION['message'] = 'Category added successfully.';
        } else {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$formName, $editId]);
            $_SESSION['message'] = 'Category updated successfully.';
        }

        $_SESSION['message_type'] = 'success';
        header('Location: categories.php');
        exit;
    }
}

$categories = $pdo->query('
    SELECT categories.id, categories.name, categories.created_at, COUNT(products.id) AS product_count
    FROM categories
    LEFT JOIN products ON products.category_id = categories.id
    GROUP BY categories.id, categories.name, categories.created_at
    ORDER BY categories.name
')->fetchAll();

$pageTitle = 'Categories';
require_once 'includes/header.php';
?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3"><?= $editId === '' ? 'Add Category' : 'Edit Category' ?></h2>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" action="categories.php">
                    <input type="hidden" name="id" value="<?= e($editId) ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="100" value="<?= e($formName) ?>" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-check-lg"></i> <?= $editId === '' ? 'Add Category' : 'Update Category' ?>
                        </button>
                        <?php if ($editId !== ''): ?>
                            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">All Categories</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-body-secondary py-4">No categories yet.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($categories as $category): ?>
                                <tr class="<?= $editId == $category['id'] ? 'table-active' : '' ?>">
                                    <td><?= $category['id'] ?></td>
                                    <td class="fw-semibold"><?= e($category['name']) ?></td>
                                    <td><?= $category['product_count'] ?></td>
                                    <td class="text-nowrap"><?= date('d M Y', strtotime($category['created_at'])) ?></td>
                                    <td class="text-end text-nowrap">
                                        <a href="categories.php?edit=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="post" action="categories.php" class="d-inline" onsubmit="return confirm('Delete this category? Its products will become Uncategorized.');">
                                            <input type="hidden" name="delete_id" value="<?= $category['id'] ?>">
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
