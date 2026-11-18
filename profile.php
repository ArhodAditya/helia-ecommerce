<?php
require_once 'includes/user_auth.php';
require_once 'config/db.php';

$stmt = $pdo->prepare('SELECT name, email, phone, address, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['user_id'], $_SESSION['user_name']);
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$orderCount = $stmt->fetchColumn();

$pageTitle = 'My Profile';
require_once 'includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php require 'includes/account_sidebar.php'; ?>
    </div>
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4 text-center">
                        <span class="profile-avatar mb-3"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
                        <h1 class="h5 fw-bold mb-1"><?= e($user['name']) ?></h1>
                        <p class="text-body-secondary small mb-3">Member since <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                        <div class="bg-body-tertiary rounded-3 py-3">
                            <div class="display-6 fw-bold text-primary"><?= $orderCount ?></div>
                            <div class="small text-body-secondary">Total Order(s)</div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <h2 class="h6 fw-semibold mb-3">Account Details</h2>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item px-0 d-flex gap-3">
                                <i class="bi bi-person text-primary fs-5"></i>
                                <div>
                                    <div class="small text-body-secondary">Full Name</div>
                                    <div class="fw-semibold"><?= e($user['name']) ?></div>
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex gap-3">
                                <i class="bi bi-envelope text-primary fs-5"></i>
                                <div>
                                    <div class="small text-body-secondary">Email</div>
                                    <div class="fw-semibold text-break"><?= e($user['email']) ?></div>
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex gap-3">
                                <i class="bi bi-telephone text-primary fs-5"></i>
                                <div>
                                    <div class="small text-body-secondary">Phone</div>
                                    <div class="fw-semibold"><?= !empty($user['phone']) ? e($user['phone']) : 'Not added yet' ?></div>
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex gap-3">
                                <i class="bi bi-geo-alt text-primary fs-5"></i>
                                <div>
                                    <div class="small text-body-secondary">Delivery Address</div>
                                    <div class="fw-semibold"><?= !empty($user['address']) ? nl2br(e($user['address'])) : 'Not added yet' ?></div>
                                </div>
                            </li>
                        </ul>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="edit_profile.php" class="btn btn-primary">
                                <i class="bi bi-pencil-square"></i> Edit Profile
                            </a>
                            <a href="my_orders.php" class="btn btn-outline-primary">
                                <i class="bi bi-bag-check"></i> My Orders
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger ms-sm-auto">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
