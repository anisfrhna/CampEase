<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    header('Location: forgot_password.php?error=Please enter your email address.');
    exit;
}

// Check if email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: forgot_password.php?error=Email address not found.');
    exit;
}

// Generate a unique token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Store token in password_resets table
$stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$email, $token, $expires]);

// Create reset link
$resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/campease/reset_password.php?token=" . $token;

// Display link directly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Link - CampEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #faf0e6; font-family: 'Quicksand', sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .card-header { background-color: #6B4F3C; color: white; border-radius: 15px 15px 0 0; }
        .btn-primary { background-color: #6B4F3C; border-color: #6B4F3C; }
    </style>
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card">
        <div class="card-header text-center">
            <h4 class="mb-0">Password Reset Link</h4>
        </div>
        <div class="card-body">
            <p class="mb-3">Click the link below to reset your password (valid for 1 hour):</p>
            <div class="alert alert-info text-break">
                <a href="<?= htmlspecialchars($resetLink) ?>"><?= htmlspecialchars($resetLink) ?></a>
            </div>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
</div>
</body>
</html>