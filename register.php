<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$success = false;
$form = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name'] = trim($_POST['name'] ?? '');
    $form['email'] = strtolower(trim($_POST['email'] ?? ''));
    $form['phone'] = trim($_POST['phone'] ?? '');
    $form['address'] = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($form['name'] === '') {
        $errors[] = 'Full name is required.';
    } elseif (mb_strlen($form['name']) > 100) {
        $errors[] = 'Full name must be 100 characters or less.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($form['email']) > 100) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!preg_match('/^\+?[0-9\s-]{6,20}$/', $form['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if ($form['address'] === '') {
        $errors[] = 'Delivery address is required.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$form['email']]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $form['name'],
            $form['email'],
            $form['phone'],
            $form['address'],
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        $success = true;
        $form = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-sm-11 col-md-9 col-lg-7 col-xl-6">
        <div class="text-center mb-4">
            <span class="brand-logo brand-logo-lg mb-3"><i class="bi bi-person-plus-fill"></i></span>
            <h1 class="h4 fw-bold mb-1">Create Your Account</h1>
            <p class="text-body-secondary mb-0">Join Helia to track your orders and manage your profile.</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-1"></i>
                        Your account has been created successfully.
                        <a href="login.php" class="alert-link">Click here to login</a>.
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="register.php">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="100" value="<?= e($form['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= e($form['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" maxlength="20" placeholder="01XXXXXXXXX" value="<?= e($form['phone']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="House, road, area, city" required><?= e($form['address']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                            <div class="form-text">At least 6 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        <i class="bi bi-person-check me-1"></i> Register
                    </button>
                </form>

                <p class="text-center text-body-secondary small mt-3 mb-0">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
