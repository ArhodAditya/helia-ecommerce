</main>

<footer class="site-footer bg-body border-top mt-auto">
    <div class="container py-4 py-md-5">
        <div class="row g-4">
            <div class="col-md-5">
                <a href="index.php" class="footer-brand d-inline-flex mb-3" aria-label="Helia home">
                    <?= heliaLogo() ?>
                </a>
                <p class="small text-body-secondary mb-3">Helia is a smart online shopping hub for shoppers across Bangladesh. Browse gadgets, sportswear, fashion, home essentials, books and personal care, check live stock, track your orders and share reviews, all in one place.</p>
                <div class="footer-pay" aria-label="Payment options">
                    <span><i class="bi bi-cash-coin"></i>Cash on Delivery</span>
                    <span><i class="bi bi-phone"></i>bKash</span>
                    <span><i class="bi bi-phone"></i>Nagad</span>
                    <span><i class="bi bi-credit-card"></i>Visa</span>
                </div>
            </div>
            <div class="col-6 col-md-3 offset-md-1">
                <h2 class="h6 fw-semibold mb-3">Quick Shop</h2>
                <ul class="list-unstyled footer-links small mb-0">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="order_tracking.php">Track Order</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="h6 fw-semibold mb-3">Account</h2>
                <ul class="list-unstyled footer-links small mb-0">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="my_orders.php">My Orders</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="border-top">
        <div class="container py-3 d-flex flex-column flex-sm-row justify-content-between gap-1 small text-body-secondary">
            <span>&copy; <?= date('Y') ?> Helia. All rights reserved.</span>
            <span>Made with PHP, MySQL and Bootstrap 5</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
