<?php
require_once 'includes/user_auth.php';
require_once 'config/db.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT name, email, phone, address, password FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['user_id'], $_SESSION['user_name']);
    header('Location: login.php');
    exit;
}

$errors = [];
$form = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'address' => $user['address'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name'] = trim($_POST['name'] ?? '');
    $form['email'] = strtolower(trim($_POST['email'] ?? ''));
    $form['phone'] = trim($_POST['phone'] ?? '');
    $form['address'] = trim($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($form['name'] === '') {
        $errors[] = 'Full name is required.';
    } elseif (mb_strlen($form['name']) > 100) {
        $errors[] = 'Full name must be 100 characters or less.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($form['email']) > 100) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($form['email'] !== strtolower($user['email'])) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$form['email'], $userId]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'This email is already used by another account.';
        }
    }

    if (!preg_match('/^\+?[0-9\s-]{6,20}$/', $form['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if ($form['address'] === '') {
        $errors[] = 'Delivery address is required.';
    }

    $changePassword = $newPassword !== '' || $confirmPassword !== '';

    if ($changePassword) {
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Your current password is not correct.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
        $stmt->execute([$form['name'], $form['email'], $form['phone'], $form['address'], $userId]);

        if ($changePassword) {
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        }

        $_SESSION['user_name'] = $form['name'];
        $_SESSION['message'] = $changePassword ? 'Profile and password updated successfully.' : 'Profile updated successfully.';
        $_SESSION['message_type'] = 'success';

        header('Location: profile.php');
        exit;
    }
}

$pageTitle = 'Edit Profile';
require_once 'includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php require 'includes/account_sidebar.php'; ?>
    </div>
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h5 fw-bold mb-4">Edit Profile</h1>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="edit_profile.php">
                    <h2 class="h6 fw-semibold text-body-secondary mb-3">Personal Details</h2>
                    <div class="row g-3 mb-4">
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
                            <input type="tel" class="form-control" id="phone" name="phone" maxlength="20" value="<?= e($form['phone']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required><?= e($form['address']) ?></textarea>
                        </div>
                    </div>

                    <h2 class="h6 fw-semibold text-body-secondary mb-1">Change Password</h2>
                    <p class="small text-body-secondary mb-3">Leave these empty if you don't want to change your password.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="col-md-4">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                        <a href="profile.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
