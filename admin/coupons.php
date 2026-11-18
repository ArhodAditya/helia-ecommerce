<?php
require_once 'auth_check.php';
require_once '../config/db.php';

$errors = [];
$coupon = [
    'id' => '',
    'code' => '',
    'discount_percent' => '',
    'expiry_date' => '',
    'is_active' => 1,
];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM coupons WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $found = $stmt->fetch();

    if ($found) {
        $coupon = $found;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM coupons WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);

        $_SESSION['message'] = 'Coupon deleted successfully.';
        $_SESSION['message_type'] = 'success';
        header('Location: coupons.php');
        exit;
    }

    if (isset($_POST['toggle_id'])) {
        $stmt = $pdo->prepare('UPDATE coupons SET is_active = NOT is_active WHERE id = ?');
        $stmt->execute([$_POST['toggle_id']]);

        $_SESSION['message'] = 'Coupon status changed.';
        $_SESSION['message_type'] = 'success';
        header('Location: coupons.php');
        exit;
    }

    $coupon['id'] = $_POST['id'] ?? '';
    $coupon['code'] = strtoupper(trim($_POST['code'] ?? ''));
    $coupon['discount_percent'] = trim($_POST['discount_percent'] ?? '');
    $coupon['expiry_date'] = $_POST['expiry_date'] ?? '';
    $coupon['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if ($coupon['code'] === '') {
        $errors[] = 'Coupon code is required.';
    }

    if (!ctype_digit($coupon['discount_percent']) || $coupon['discount_percent'] < 1 || $coupon['discount_percent'] > 100) {
        $errors[] = 'Discount must be a whole number between 1 and 100.';
    }

    if ($coupon['expiry_date'] === '' || strtotime($coupon['expiry_date']) === false) {
        $errors[] = 'Please choose a valid expiry date.';
    }

    if ($coupon['code'] !== '') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM coupons WHERE code = ? AND id != ?');
        $stmt->execute([$coupon['code'], $coupon['id'] === '' ? 0 : $coupon['id']]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'This coupon code already exists.';
        }
    }

    if (empty($errors)) {
        if ($coupon['id'] === '') {
            $stmt = $pdo->prepare('INSERT INTO coupons (code, discount_percent, expiry_date, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$coupon['code'], $coupon['discount_percent'], $coupon['expiry_date'], $coupon['is_active']]);
            $_SESSION['message'] = 'Coupon added successfully.';
        } else {
            $stmt = $pdo->prepare('UPDATE coupons SET code = ?, discount_percent = ?, expiry_date = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$coupon['code'], $coupon['discount_percent'], $coupon['expiry_date'], $coupon['is_active'], $coupon['id']]);
            $_SESSION['message'] = 'Coupon updated successfully.';
        }

        $_SESSION['message_type'] = 'success';
        header('Location: coupons.php');
        exit;
    }
}

$coupons = $pdo->query('SELECT * FROM coupons ORDER BY expiry_date DESC')->fetchAll();
$today = date('Y-m-d');
$isEditing = $coupon['id'] !== '';

$pageTitle = 'Coupons';
require_once 'includes/header.php';
?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3"><?= $isEditing ? 'Edit Coupon' : 'Add Coupon' ?></h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="coupons.php">
                    <input type="hidden" name="id" value="<?= e($coupon['id']) ?>">
                    <div class="mb-3">
                        <label for="code" class="form-label">Coupon Code</label>
                        <input type="text" class="form-control text-uppercase" id="code" name="code" maxlength="50" placeholder="HELIA10" value="<?= e($coupon['code']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="discount_percent" class="form-label">Discount (%)</label>
                        <input type="number" class="form-control" id="discount_percent" name="discount_percent" min="1" max="100" value="<?= e($coupon['discount_percent']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?= e($coupon['expiry_date']) ?>" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= $coupon['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-check-lg"></i> <?= $isEditing ? 'Update Coupon' : 'Add Coupon' ?>
                        </button>
                        <?php if ($isEditing): ?>
                            <a href="coupons.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">All Coupons</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coupons)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-body-secondary py-4">No coupons yet.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($coupons as $item): ?>
                                <tr class="<?= $coupon['id'] == $item['id'] ? 'table-active' : '' ?>">
                                    <td class="fw-semibold"><?= e($item['code']) ?></td>
                                    <td><?= $item['discount_percent'] ?>%</td>
                                    <td class="text-nowrap"><?= date('d M Y', strtotime($item['expiry_date'])) ?></td>
                                    <td>
                                        <?php if ($item['is_active']): ?>
                                            <span class="badge text-bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                        <?php if ($item['expiry_date'] < $today): ?>
                                            <span class="badge text-bg-danger">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="coupons.php?edit=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="post" action="coupons.php" class="d-inline">
                                            <input type="hidden" name="toggle_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <?php if ($item['is_active']): ?>
                                                    <i class="bi bi-pause-circle"></i> Deactivate
                                                <?php else: ?>
                                                    <i class="bi bi-play-circle"></i> Activate
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <form method="post" action="coupons.php" class="d-inline" onsubmit="return confirm('Delete this coupon?');">
                                            <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
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
