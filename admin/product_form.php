<?php
require_once 'auth_check.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? '';
$isEdit = $id !== '';

$product = [
    'name' => '',
    'category_id' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'color_variants' => '',
    'image' => '',
    'is_trending' => 0,
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['message'] = 'Product not found.';
        $_SESSION['message_type'] = 'danger';
        header('Location: products.php');
        exit;
    }
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['name'] = trim($_POST['name'] ?? '');
    $product['category_id'] = $_POST['category_id'] ?? '';
    $product['description'] = trim($_POST['description'] ?? '');
    $product['price'] = trim($_POST['price'] ?? '');
    $product['stock'] = trim($_POST['stock'] ?? '');
    $product['color_variants'] = trim(str_replace(' ', '', $_POST['color_variants'] ?? ''), ',');
    $product['is_trending'] = isset($_POST['is_trending']) ? 1 : 0;

    if ($product['name'] === '') {
        $errors[] = 'Product name is required.';
    }

    if (!in_array($product['category_id'], array_column($categories, 'id'))) {
        $errors[] = 'Please select a category.';
    }

    if (!is_numeric($product['price']) || $product['price'] < 0) {
        $errors[] = 'Price must be a valid number (0 or more).';
    }

    if (!ctype_digit($product['stock'])) {
        $errors[] = 'Stock quantity must be a whole number (0 or more).';
    }

    if ($product['color_variants'] !== '') {
        foreach (explode(',', $product['color_variants']) as $color) {
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                $errors[] = 'Color variants must be hex codes separated by commas, like #000000,#0d6efd.';
                break;
            }
        }
    }

    $hasNewImage = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($hasNewImage) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed. Please try again.';
        } elseif (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Only JPG, JPEG, PNG and WEBP images are allowed.';
        } elseif (!in_array(mime_content_type($_FILES['image']['tmp_name']), $allowedTypes)) {
            $errors[] = 'The selected file is not a real image.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be 2 MB or smaller.';
        }
    }

    if (empty($errors)) {
        $oldImage = $product['image'];

        if ($hasNewImage) {
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0777, true);
            }

            $fileName = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $extension;

            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $fileName)) {
                $product['image'] = 'uploads/' . $fileName;
            } else {
                $errors[] = 'Could not save the uploaded image.';
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = $pdo->prepare('
                UPDATE products
                SET category_id = ?, name = ?, description = ?, price = ?, stock = ?,
                    color_variants = ?, image = ?, is_trending = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $product['category_id'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock'],
                $product['color_variants'],
                $product['image'],
                $product['is_trending'],
                $id,
            ]);

            if ($hasNewImage && !empty($oldImage) && file_exists('../' . $oldImage)) {
                unlink('../' . $oldImage);
            }

            $_SESSION['message'] = 'Product updated successfully.';
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO products (category_id, name, description, price, stock, color_variants, image, is_trending)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $product['category_id'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock'],
                $product['color_variants'],
                $product['image'],
                $product['is_trending'],
            ]);

            $_SESSION['message'] = 'Product added successfully.';
        }

        $_SESSION['message_type'] = 'success';
        header('Location: products.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Edit Product' : 'Add Product';
$activePage = $isEdit ? 'products.php' : 'product_form.php';
require_once 'includes/header.php';
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="150" value="<?= e($product['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($categories)): ?>
                            <div class="form-text text-danger">No categories yet. <a href="categories.php">Add a category first.</a></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?= e($product['description']) ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="price" class="form-label">Price (BDT)</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?= e($product['price']) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="stock" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" step="1" value="<?= e($product['stock']) ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <label for="image" class="form-label">Product Image</label>
                    <?php if (!empty($product['image'])): ?>
                        <img id="imagePreview" src="../<?= e($product['image']) ?>" alt="Current product image" class="image-preview mb-2">
                    <?php else: ?>
                        <img id="imagePreview" alt="Image preview" class="image-preview mb-2 d-none">
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">JPG, JPEG, PNG or WEBP, max 2 MB.<?= $isEdit ? ' Leave empty to keep the current image.' : '' ?></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <label for="color_variants" class="form-label">Color Variants</label>
                    <input type="text" class="form-control mb-2" id="color_variants" name="color_variants" placeholder="#000000,#0d6efd" value="<?= e($product['color_variants']) ?>">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <input type="color" class="form-control form-control-color" id="colorPicker" value="#0d6efd" title="Pick a color">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addColor">Add color</button>
                    </div>
                    <div id="colorPreview" class="d-flex flex-wrap gap-1"></div>
                    <div class="form-text">Comma-separated hex codes.</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_trending" name="is_trending" value="1" <?= $product['is_trending'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_trending">Mark as Trending</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Update Product' : 'Save Product' ?>
                </button>
                <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
    const colorInput = document.getElementById('color_variants');
    const colorPreview = document.getElementById('colorPreview');

    function showColors() {
        colorPreview.innerHTML = '';

        colorInput.value.split(',').forEach(function (color) {
            color = color.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(color)) {
                const dot = document.createElement('span');
                dot.className = 'color-dot';
                dot.style.backgroundColor = color;
                dot.title = color;
                colorPreview.appendChild(dot);
            }
        });
    }

    document.getElementById('addColor').addEventListener('click', function () {
        const newColor = document.getElementById('colorPicker').value;
        colorInput.value = colorInput.value.trim() === '' ? newColor : colorInput.value.trim() + ',' + newColor;
        showColors();
    });

    colorInput.addEventListener('input', showColors);
    showColors();

    document.getElementById('image').addEventListener('change', function () {
        const preview = document.getElementById('imagePreview');

        if (this.files.length > 0) {
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove('d-none');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
