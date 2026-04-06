<?php
session_start();
if (!isset($_SESSION['reset_link'])) {
    header('Location: forgot_password.php');
    exit;
}
$link = $_SESSION['reset_link'];
unset($_SESSION['reset_link']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Link - CampEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #faf0e6; font-family: 'Quicksand', sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .card-header { background-color: #6B4F3C; color: white; border-radius: 15px 15px 0 0; }
        .btn-primary { background-color: #6B4F3C; border-color: #6B4F3C; }
        .btn-primary:hover { background-color: #5A3E2F; border-color: #5A3E2F; }
    </style>
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card">
        <div class="card-header text-center">
            <h4 class="mb-0">Password Reset Link</h4>
        </div>
        <div class="card-body">
            <p class="mb-3">Use the link below to set a new password (valid for 1 hour):</p>
            <div class="alert alert-info text-break">
                <a href="<?= htmlspecialchars($link) ?>" target="_blank"><?= htmlspecialchars($link) ?></a>
            </div>
            <div class="d-grid gap-2">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>