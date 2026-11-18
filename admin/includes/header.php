<?php
$pageTitle = $pageTitle ?? 'Admin';
$activePage = $activePage ?? basename($_SERVER['PHP_SELF']);

$menuItems = [
    'index.php' => ['Dashboard', 'bi-speedometer2'],
    'products.php' => ['Products', 'bi-box-seam'],
    'product_form.php' => ['Add Product', 'bi-plus-square'],
    'categories.php' => ['Categories', 'bi-tags'],
    'orders.php' => ['Orders', 'bi-receipt'],
    'coupons.php' => ['Coupons', 'bi-ticket-perforated'],
    'reviews.php' => ['Reviews', 'bi-star-half'],
    'reports.php' => ['Reports', 'bi-file-earmark-bar-graph'],
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Helia Admin</title>
    <script>
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <aside class="sidebar offcanvas-lg offcanvas-start border-end" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="sidebarLabel">Helia Admin</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-column p-3">
            <a href="index.php" class="d-none d-lg-flex align-items-center gap-2 mb-4 px-2 text-decoration-none">
                <span class="brand-logo"><i class="bi bi-bag-heart-fill"></i></span>
                <span class="fs-5 fw-bold text-body">Helia Admin</span>
            </a>

            <ul class="nav nav-pills flex-column gap-1">
                <?php foreach ($menuItems as $file => $item): ?>
                    <li class="nav-item">
                        <a href="<?= $file ?>" class="nav-link <?= $activePage === $file ? 'active' : 'text-body' ?>">
                            <i class="bi <?= $item[1] ?> me-2"></i><?= $item[0] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="nav-item mt-3 pt-3 border-top">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <div class="main-content flex-grow-1">
        <header class="topbar d-flex align-items-center gap-3 px-3 px-md-4 py-3 border-bottom">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open menu">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h1 class="h5 fw-semibold mb-0 me-auto"><?= e($pageTitle) ?></h1>
            <a href="../index.php" class="btn btn-outline-primary btn-sm" title="View Store">
                <i class="bi bi-shop"></i><span class="d-none d-md-inline"> View Store</span>
            </a>
            <button id="themeToggle" class="btn btn-outline-secondary btn-sm" type="button">
                <i class="bi bi-moon-stars"></i> Dark
            </button>
            <span class="d-none d-sm-inline text-body-secondary small">
                <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['admin_name']) ?>
            </span>
        </header>

        <main class="p-3 p-md-4">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= e($_SESSION['message_type'] ?? 'success') ?> alert-dismissible fade show" role="alert">
                    <?= e($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
