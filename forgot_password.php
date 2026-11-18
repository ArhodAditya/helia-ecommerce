<?php
session_start();
require_once 'config/db.php';

if (isset($_GET['restart'])) {
    unset($_SESSION['reset_user_id'], $_SESSION['reset_email']);
    header('Location: forgot_password.php');
    exit;
}

$errors = [];
$email = '';
$passwordChanged = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify_email') {
        $email = strtolower(trim($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_email'] = $user['email'];
            } else {
                $errors[] = 'We could not find an account with this email address.';
            }
        }
    }

    if ($action === 'reset_password' && isset($_SESSION['reset_user_id'])) {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['reset_user_id']]);

            unset($_SESSION['reset_user_id'], $_SESSION['reset_email']);
            $passwordChanged = true;
        }
    }
}

$step = isset($_SESSION['reset_user_id']) ? 2 : 1;

$pageTitle = 'Forgot Password';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
        <div class="text-center mb-4">
            <span class="brand-logo brand-logo-lg mb-3"><i class="bi bi-key-fill"></i></span>
            <h1 class="h4 fw-bold mb-1">Reset Password</h1>
            <p class="text-body-secondary mb-0">Get back into your Helia account</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if ($passwordChanged): ?>
                    <div class="text-center">
                        <i class="bi bi-check-circle-fill text-success display-5"></i>
                        <h2 class="h5 fw-bold mt-3">Password Updated</h2>
                        <p class="text-body-secondary">Your password has been changed. You can now login with your new password.</p>
                        <a href="login.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2 mb-4">
                        <div class="flex-fill">
                            <div class="progress mb-1" role="progressbar" aria-label="Step 1" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                                <div class="progress-bar <?= $step > 1 ? 'bg-success' : '' ?>" style="width: 100%"></div>
                            </div>
                            <div class="small <?= $step === 1 ? 'fw-semibold text-primary' : 'text-success' ?>">1. Verify Email</div>
                        </div>
                        <div class="flex-fill">
                            <div class="progress mb-1" role="progressbar" aria-label="Step 2" aria-valuenow="<?= $step === 2 ? 100 : 0 ?>" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                                <div class="progress-bar" style="width: <?= $step === 2 ? 100 : 0 ?>%"></div>
                            </div>
                            <div class="small <?= $step === 2 ? 'fw-semibold text-primary' : 'text-body-secondary' ?>">2. New Password</div>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($step === 1): ?>
                        <form method="post" action="forgot_password.php">
                            <input type="hidden" name="action" value="verify_email">
                            <div class="mb-4">
                                <label for="email" class="form-label">Registered Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required autofocus>
                                <div class="form-text">Enter the email you used when you registered.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i> Verify Email
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success py-2 small">
                            <i class="bi bi-person-check me-1"></i>
                            Account found for <strong><?= e($_SESSION['reset_email']) ?></strong>
                        </div>
                        <form method="post" action="forgot_password.php">
                            <input type="hidden" name="action" value="reset_password">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" autocomplete="new-password" required autofocus>
                                <div class="form-text">At least 6 characters.</div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" autocomplete="new-password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-1"></i> Update Password
                            </button>
                        </form>
                        <p class="text-center small mt-3 mb-0">
                            <a href="forgot_password.php?restart=1">Use a different email</a>
                        </p>
                    <?php endif; ?>

                    <p class="text-center text-body-secondary small mt-3 mb-0">
                        Remembered it? <a href="login.php">Back to Login</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
