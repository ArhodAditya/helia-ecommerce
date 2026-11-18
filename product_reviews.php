<?php
session_start();
require_once 'config/db.php';
require_once 'includes/ui_helpers.php';

function showStars($rating)
{
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }

    return $html;
}

$ratingLabels = [
    5 => 'Excellent',
    4 => 'Very Good',
    3 => 'Good',
    2 => 'Fair',
    1 => 'Poor',
];

$products = $pdo->query('SELECT id, name, price, image FROM products ORDER BY name')->fetchAll();

$selectedId = $_GET['product_id'] ?? '';
$selectedProduct = null;

foreach ($products as $product) {
    if ($product['id'] == $selectedId) {
        $selectedProduct = $product;
    }
}

if (!$selectedProduct) {
    $selectedId = '';
}

$errors = [];
$form = [
    'user_name' => $_SESSION['user_name'] ?? '',
    'product_id' => $selectedId,
    'rating' => '',
    'comment' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['user_name'] = trim($_POST['user_name'] ?? '');
    $form['product_id'] = $_POST['product_id'] ?? '';
    $form['rating'] = $_POST['rating'] ?? '';
    $form['comment'] = trim($_POST['comment'] ?? '');

    $productIds = array_column($products, 'id');

    if ($form['user_name'] === '') {
        $errors[] = 'Please enter your name.';
    } elseif (mb_strlen($form['user_name']) > 100) {
        $errors[] = 'Name must be 100 characters or less.';
    }

    if (!in_array($form['product_id'], $productIds)) {
        $errors[] = 'Please choose a product.';
    }

    if (!in_array($form['rating'], ['1', '2', '3', '4', '5'], true)) {
        $errors[] = 'Please choose a rating from 1 to 5 stars.';
    }

    if ($form['comment'] === '') {
        $errors[] = 'Please write a short comment.';
    } elseif (mb_strlen($form['comment']) > 1000) {
        $errors[] = 'Comment must be 1000 characters or less.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO reviews (product_id, user_name, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$form['product_id'], $form['user_name'], $form['rating'], $form['comment']]);

        $_SESSION['message'] = 'Thank you! Your review has been posted.';
        $_SESSION['message_type'] = 'success';
        header('Location: product_reviews.php?product_id=' . $form['product_id']);
        exit;
    }
}

$where = '';
$params = [];

if ($selectedId !== '') {
    $where = ' WHERE reviews.product_id = ?';
    $params[] = $selectedId;
}

$stmt = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM reviews' . $where);
$stmt->execute($params);
$stats = $stmt->fetch();

$stmt = $pdo->prepare('SELECT rating, COUNT(*) AS total FROM reviews' . $where . ' GROUP BY rating');
$stmt->execute($params);
$breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

foreach ($stmt->fetchAll() as $row) {
    $breakdown[$row['rating']] = $row['total'];
}

$stmt = $pdo->prepare('
    SELECT reviews.*, products.name AS product_name
    FROM reviews
    JOIN products ON products.id = reviews.product_id' . $where . '
    ORDER BY reviews.created_at DESC, reviews.id DESC
');
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$average = round($stats['average'], 1);

$pageTitle = 'Product Reviews';
require_once 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">Ratings &amp; Reviews</h1>
        <p class="text-body-secondary mb-0">See what other customers think and share your own experience.</p>
    </div>
    <form method="get" action="product_reviews.php" class="d-flex gap-2">
        <label for="product_filter" class="visually-hidden">Choose a product</label>
        <select class="form-select" id="product_filter" name="product_id" onchange="this.form.submit()">
            <option value="">All Products</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= $product['id'] ?>" <?= $selectedId == $product['id'] ? 'selected' : '' ?>><?= e($product['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel"></i></button>
    </form>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <?php if ($selectedProduct): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <?php if (!empty($selectedProduct['image'])): ?>
                            <img src="<?= e($selectedProduct['image']) ?>" alt="<?= e($selectedProduct['name']) ?>" class="product-thumb product-thumb-lg">
                        <?php else: ?>
                            <div class="product-thumb product-thumb-lg thumb-placeholder"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                        <div>
                            <h2 class="h6 fw-bold mb-1"><?= e($selectedProduct['name']) ?></h2>
                            <div class="text-body-secondary">BDT <?= number_format($selectedProduct['price'], 2) ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <h2 class="h6 fw-bold mb-3">All Products</h2>
                <?php endif; ?>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="display-5 fw-bold"><?= number_format($average, 1) ?></span>
                    <div>
                        <div class="stars fs-5"><?= showStars($average) ?></div>
                        <div class="small text-body-secondary">Based on <?= $stats['total'] ?> review(s)</div>
                    </div>
                </div>

                <?php foreach ($breakdown as $star => $count): ?>
                    <?php $percent = $stats['total'] > 0 ? $count / $stats['total'] * 100 : 0; ?>
                    <div class="d-flex align-items-center gap-2 small mb-1">
                        <span class="text-nowrap" style="width: 3rem;"><?= $star ?> <i class="bi bi-star-fill stars"></i></span>
                        <div class="progress flex-grow-1" role="progressbar" aria-label="<?= $star ?> star reviews" aria-valuenow="<?= round($percent) ?>" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?= $percent ?>%"></div>
                        </div>
                        <span class="text-body-secondary text-end" style="width: 2rem;"><?= $count ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Write a Review</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($products)): ?>
                    <p class="text-body-secondary mb-0">There are no products to review yet.</p>
                <?php else: ?>
                    <form method="post" action="product_reviews.php<?= $selectedId !== '' ? '?product_id=' . $selectedId : '' ?>">
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="user_name" name="user_name" maxlength="100" value="<?= e($form['user_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">Select a product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>" <?= $form['product_id'] == $product['id'] ? 'selected' : '' ?>><?= e($product['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="">Select a rating</option>
                                <?php foreach ($ratingLabels as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $form['rating'] == $value ? 'selected' : '' ?>><?= $value ?> Star<?= $value > 1 ? 's' : '' ?> - <?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" maxlength="1000" placeholder="What did you like or dislike?" required><?= e($form['comment']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send"></i> Submit Review
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if (empty($reviews)): ?>
            <?= renderEmptyState('No reviews yet', $selectedProduct ? 'Nobody has reviewed ' . $selectedProduct['name'] . ' yet. Be the first to write one!' : 'Be the first to share what you think about a product!', '', '', 'bi-chat-square-text') ?>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">
                    <?= count($reviews) ?> review(s)<?= $selectedProduct ? ' for ' . e($selectedProduct['name']) : '' ?>
                </h2>

                <?php foreach ($reviews as $review): ?>
                    <div class="d-flex gap-3 py-3 border-top">
                        <div class="review-avatar"><?= e(mb_strtoupper(mb_substr($review['user_name'], 0, 1))) ?></div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <span class="fw-semibold"><?= e($review['user_name']) ?></span>
                                    <span class="stars small ms-1"><?= showStars($review['rating']) ?></span>
                                </div>
                                <span class="small text-body-secondary"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                            </div>
                            <?php if (!$selectedProduct): ?>
                                <a href="product_reviews.php?product_id=<?= $review['product_id'] ?>" class="badge bg-body-secondary text-body-emphasis border text-decoration-none mt-1"><?= e($review['product_name']) ?></a>
                            <?php endif; ?>
                            <p class="mb-0 mt-2"><?= nl2br(e($review['comment'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
