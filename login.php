<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: profile.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
        <div class="text-center mb-4">
            <span class="brand-logo brand-logo-lg mb-3"><i class="bi bi-bag-heart-fill"></i></span>
            <h1 class="h4 fw-bold mb-1">Welcome Back</h1>
            <p class="text-body-secondary mb-0">Login to your Helia account</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if (isset($_GET['logged_out'])): ?>
                    <div class="alert alert-success py-2">You have been logged out.</div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required autofocus>
                    </div>
                    <div class="mb-2">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="text-end mb-4">
                        <a href="forgot_password.php" class="small">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>
                </form>

                <p class="text-center text-body-secondary small mt-3 mb-0">
                    New to Helia? <a href="register.php">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
