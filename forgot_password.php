<?php
require_once 'config.php';
include 'header.php';

$message = '';
$link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $message = '<div class="alert alert-danger">Please enter your email.</div>';
    } else {
        // Check if email exists in users table
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $message = '<div class="alert alert-danger">Email address not found.</div>';
        } else {
            // Delete old tokens for this email
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            
            // Generate new token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);
            
            $link = "http://" . $_SERVER['HTTP_HOST'] . "/campease/reset_password.php?token=" . $token;
            $message = '<div class="alert alert-success">Click the link below to reset your password (valid 1 hour).</div>';
        }
    }
}
?>

<div class="container mt-5" style="max-width: 500px;">
    <h2 class="text-center mb-4">Forgot Password</h2>
    <?= $message ?>
    <?php if ($link): ?>
        <div class="alert alert-info text-break">
            <a href="<?= htmlspecialchars($link) ?>" target="_blank"><?= htmlspecialchars($link) ?></a>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>
    <p class="mt-3 text-center"><a href="login.php">Back to Login</a></p>
</div>

<?php include 'footer.php'; ?>