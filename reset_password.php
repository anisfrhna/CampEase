<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('No token provided. Please request a new password reset.');
}

// Fetch token without expiration condition
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    die('Invalid token. Please request a new password reset.');
}

// Check expiration using PHP time
$expires = strtotime($reset['expires_at']);
$now = time();
if ($expires <= $now) {
    die('Token expired. Please request a new password reset.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $update->execute([$hash, $reset['email']]);

        // Delete used token
        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

        header('Location: login.php?reset=success');
        exit;
    }
}

include 'header.php';
?>

<div class="container mt-5" style="max-width: 500px;">
    <h2 class="text-center mb-4">Reset Password</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
</div>

<?php include 'footer.php'; ?>