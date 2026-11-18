<?php
require_once 'config/db.php';

http_response_code(404);

$baseHref = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
$requestedPage = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

$pageTitle = 'Page Not Found';
require_once 'includes/header.php';
?>

<div class="row justify-content-center py-md-4">
    <div class="col-md-9 col-lg-7 col-xl-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body p-4 p-md-5">
                <div class="error-code mb-2">404</div>
                <h1 class="h3 fw-bold mb-2">Page Not Found</h1>
                <p class="text-body-secondary mb-1">Sorry, the page you are looking for doesn't exist or has been moved.</p>
                <?php if (!empty($requestedPage) && basename($requestedPage) !== '404.php'): ?>
                    <p class="small text-body-secondary mb-0">
                        We couldn't find <code class="text-break"><?= e($requestedPage) ?></code>
                    </p>
                <?php endif; ?>

                <form method="GET" action="search.php" class="my-4" role="search">
                    <div class="input-group">
                        <input type="search" class="form-control" name="q" placeholder="Search for a product instead..." aria-label="Search products" required>
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                    <a href="products.php" class="btn btn-outline-secondary">
                        <i class="bi bi-grid"></i> Browse Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
