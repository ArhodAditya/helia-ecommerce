<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/brand.php';

$pageTitle = $pageTitle ?? 'Helia';
$currentPage = basename($_SERVER['PHP_SELF']);
$activePage = $activePage ?? $currentPage;
$isLoggedIn = isset($_SESSION['user_id']);
$searchValue = $currentPage === 'search.php' ? trim($_GET['q'] ?? '') : '';
$wishlistCount = count($_SESSION['wishlist'] ?? []);
$cartCount = count($_SESSION['cart'] ?? []);

$navItems = [
    'index.php' => ['Home', 'bi-house'],
    'products.php' => ['Products', 'bi-grid'],
    'categories.php' => ['Categories', 'bi-tags'],
    'order_tracking.php' => ['Track Order', 'bi-truck'],
    'product_reviews.php' => ['Reviews', 'bi-star'],
];

if ($isLoggedIn) {
    $accountItems = [
        'profile.php' => ['Profile', 'bi-person-circle'],
        'my_orders.php' => ['My Orders', 'bi-bag-check'],
        'logout.php' => ['Logout', 'bi-box-arrow-right'],
    ];
    $firstName = explode(' ', trim($_SESSION['user_name'] ?? 'Account'))[0];
    $accountLabel = $firstName !== '' ? $firstName : 'Account';
} else {
    $accountItems = [
        'login.php' => ['Login', 'bi-box-arrow-in-right'],
        'register.php' => ['Register', 'bi-person-plus'],
    ];
    $accountLabel = 'Account';
}

$parentPages = [
    'product_details.php' => 'products.php',
    'search.php' => 'products.php',
    'order_details.php' => 'my_orders.php',
    'edit_profile.php' => 'profile.php',
    'forgot_password.php' => 'login.php',
];

if (isset($parentPages[$activePage])) {
    $activePage = $parentPages[$activePage];
}

$accountActive = isset($accountItems[$activePage]);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (isset($baseHref)): ?>
        <base href="<?= e($baseHref) ?>">
    <?php endif; ?>
    <title><?= e($pageTitle) ?> | Helia</title>
    <script>
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wdth,wght@75..125,400..800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/theme.css?v=3" rel="stylesheet">
    <link href="assets/css/style.css?v=3" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body border-bottom sticky-top py-2 site-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php" aria-label="Helia home">
            <?= heliaLogo() ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <div class="d-flex flex-column-reverse flex-lg-column w-100">
                <div class="d-lg-flex align-items-center gap-1 pb-2 pb-lg-0">
                    <form class="site-search my-2 my-lg-0 me-lg-auto" role="search" method="GET" action="search.php">
                        <div class="input-group input-group-sm">
                            <input type="search" class="form-control" name="q" placeholder="Search products..." value="<?= e($searchValue) ?>" aria-label="Search products">
                            <button class="btn btn-primary" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                    <ul class="navbar-nav align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'wishlist.php' ? 'active fw-semibold' : '' ?>" href="wishlist.php" aria-label="Wishlist, <?= $wishlistCount ?> item(s)">
                                <i class="bi bi-heart me-1"></i><span class="d-lg-none d-xl-inline">Wishlist </span><span class="badge rounded-pill text-bg-danger nav-count"><?= $wishlistCount ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'cart.php' ? 'active fw-semibold' : '' ?>" href="cart.php" aria-label="Cart, <?= $cartCount ?> item(s)">
                                <i class="bi bi-cart3 me-1"></i><span class="d-lg-none d-xl-inline">Cart </span><span class="badge rounded-pill text-bg-primary nav-count"><?= $cartCount ?></span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= $accountActive ? 'active fw-semibold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu: <?= e($accountLabel) ?>">
                                <i class="bi bi-person-circle me-1"></i><span class="account-name text-truncate"><?= e($accountLabel) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-lg-end shadow-sm">
                                <?php if ($isLoggedIn): ?>
                                    <li><h6 class="dropdown-header">Signed in as <?= e($_SESSION['user_name'] ?? '') ?></h6></li>
                                <?php endif; ?>
                                <?php foreach ($accountItems as $file => $item): ?>
                                    <?php if ($file === 'logout.php'): ?>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item <?= $activePage === $file ? 'active' : '' ?> <?= $file === 'logout.php' ? 'text-danger' : '' ?>" href="<?= $file ?>" <?= $activePage === $file ? 'aria-current="page"' : '' ?>>
                                            <i class="bi <?= $item[1] ?> me-2"></i><?= $item[0] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/login.php">
                                <i class="bi bi-shield-lock me-1"></i>Admin
                            </a>
                        </li>
                    </ul>
                    <button id="themeToggle" class="btn btn-outline-secondary btn-sm ms-lg-2 mt-2 mt-lg-0" type="button">
                        <i class="bi bi-moon-stars"></i> Dark
                    </button>
                </div>
                <ul class="navbar-nav store-links border-bottom border-lg-0 pb-2 pb-lg-0">
                    <?php foreach ($navItems as $file => $item): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === $file ? 'active fw-semibold' : '' ?>" href="<?= $file ?>" <?= $activePage === $file ? 'aria-current="page"' : '' ?>>
                                <i class="bi <?= $item[1] ?> me-1"></i><?= $item[0] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main class="site-main container py-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= e($_SESSION['message_type'] ?? 'success') ?> alert-dismissible fade show" role="alert">
            <?= e($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
