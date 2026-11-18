<?php
require_once 'auth_check.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'Review deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Review not found.';
        $_SESSION['message_type'] = 'danger';
    }

    header('Location: reviews.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$ratingFilter = $_GET['rating'] ?? '';

$sql = 'SELECT reviews.*, products.name AS product_name, products.image AS product_image
        FROM reviews
        JOIN products ON products.id = reviews.product_id
        WHERE 1 = 1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (products.name LIKE ? OR reviews.user_name LIKE ? OR reviews.comment LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (in_array($ratingFilter, ['1', '2', '3', '4', '5'], true)) {
    $sql .= ' AND reviews.rating = ?';
    $params[] = $ratingFilter;
}

$sql .= ' ORDER BY reviews.created_at DESC, reviews.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$stats = $pdo->query('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM reviews')->fetch();

$pageTitle = 'Reviews';
require_once 'includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary-emphasis"><i class="bi bi-chat-square-text"></i></div>
                <div>
                    <div class="small text-body-secondary">Total Reviews</div>
                    <div class="h4 fw-bold mb-0"><?= $stats['total'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-star-half"></i></div>
                <div>
                    <div class="small text-body-secondary">Average Rating</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($stats['average'], 1) ?> / 5</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-7">
                <label for="search" class="form-label small text-body-secondary">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Search by product, customer or comment" value="<?= e($search) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label for="rating" class="form-label small text-body-secondary">Rating</label>
                <select class="form-select" id="rating" name="rating">
                    <option value="">All ratings</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= $ratingFilter === (string) $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i> Filter</button>
                <a href="reviews.php" class="btn btn-outline-secondary" title="Reset filters"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h6 fw-semibold mb-3"><?= count($reviews) ?> review(s) found</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-4">No reviews found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($review['product_image'])): ?>
                                        <img src="../<?= e($review['product_image']) ?>" alt="<?= e($review['product_name']) ?>" class="product-thumb">
                                    <?php else: ?>
                                        <div class="product-thumb thumb-placeholder"><i class="bi bi-image"></i></div>
                                    <?php endif; ?>
                                    <span class="fw-semibold"><?= e($review['product_name']) ?></span>
                                </div>
                            </td>
                            <td class="text-nowrap"><?= e($review['user_name']) ?></td>
                            <td class="text-nowrap text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                                <span class="visually-hidden"><?= $review['rating'] ?> out of 5</span>
                            </td>
                            <td class="review-comment"><?= nl2br(e($review['comment'])) ?></td>
                            <td class="text-nowrap"><?= date('d M Y, h:i A', strtotime($review['created_at'])) ?></td>
                            <td class="text-end">
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this review?');">
                                    <input type="hidden" name="delete_id" value="<?= $review['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
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
