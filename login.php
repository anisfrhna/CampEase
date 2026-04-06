<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } else {
        header('Location: user_dashboard.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
                exit;
            } else {
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect");
                    exit;
                } else {
                    header('Location: user_dashboard.php');
                    exit;
                }
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container mt-5" style="max-width: 400px;">
    <h2 class="text-center mb-4">Login to CampEase</h2>
    <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
        <div class="alert alert-success">Your password has been reset. You can now log in with your new password.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="showPassword">
            <label class="form-check-label" for="showPassword">Show password</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="mt-3 text-center">
        <a href="forgot_password.php">Forgot Password?</a> | 
        <a href="register.php">Register here</a>
    </p>
</div>

<script>
    document.getElementById('showPassword').addEventListener('change', function() {
        const password = document.getElementById('password');
        password.type = this.checked ? 'text' : 'password';
    });
</script>

<?php include 'footer.php'; ?>