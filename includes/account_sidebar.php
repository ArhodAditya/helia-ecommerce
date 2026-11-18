<?php
$sidebarPage = basename($_SERVER['PHP_SELF']);

if ($sidebarPage === 'order_details.php') {
    $sidebarPage = 'my_orders.php';
}

$sidebarItems = [
    'profile.php' => ['My Profile', 'bi-person-circle'],
    'edit_profile.php' => ['Edit Profile', 'bi-pencil-square'],
    'my_orders.php' => ['My Orders', 'bi-bag-check'],
    'order_tracking.php' => ['Track Order', 'bi-truck'],
];

$sidebarName = $_SESSION['user_name'] ?? 'Customer';
?>
<div class="card border-0 shadow-sm account-sidebar">
    <div class="card-body d-flex align-items-center gap-3 border-bottom">
        <span class="account-avatar"><?= e(mb_strtoupper(mb_substr($sidebarName, 0, 1))) ?></span>
        <div class="text-truncate">
            <div class="small text-body-secondary">Hello,</div>
            <div class="fw-semibold text-truncate"><?= e($sidebarName) ?></div>
        </div>
    </div>
    <nav class="list-group list-group-flush" aria-label="Account menu">
        <?php foreach ($sidebarItems as $file => $item): ?>
            <a href="<?= $file ?>" class="list-group-item list-group-item-action <?= $sidebarPage === $file ? 'active' : '' ?>" <?= $sidebarPage === $file ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= $item[1] ?>"></i><?= $item[0] ?>
            </a>
        <?php endforeach; ?>
        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="bi bi-box-arrow-right"></i>Logout
        </a>
    </nav>
</div>
