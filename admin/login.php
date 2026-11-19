<?php
session_start();
require_once '../config/db.php';
require_once '../includes/brand.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            header('Location: index.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Helia</title>
    <script>
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wdth,wght@75..125,400..800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/theme.css?v=3" rel="stylesheet">
    <link href="assets/style.css?v=3" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-backdrop" aria-hidden="true"></div>

    <div class="container position-relative">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
                <div class="login-brand text-center mb-4">
                    <?= heliaLogo('Admin', 'helia-logo-lg', 56) ?>
                    <p class="mb-0 mt-3">Sign in to manage the Helia store</p>
                </div>

                <div class="card border-0 shadow login-card">
                    <div class="card-body p-4 p-sm-5">
                        <h1 class="h4 fw-bold mb-1">Welcome back</h1>
                        <p class="text-body-secondary small mb-4">Use your admin email and password.</p>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger py-2"><?= e($error) ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" placeholder="name@example.com" required autofocus>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-center small mt-4 mb-0">
                    <a href="../index.php" class="text-decoration-none fw-semibold"><i class="bi bi-arrow-left"></i> Back to Helia store</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
