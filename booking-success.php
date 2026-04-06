<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sites.php');
    exit;
}

$site_id = $_POST['site_id'] ?? 0;
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$check_in = $_POST['check_in'] ?? '';
$check_out = $_POST['check_out'] ?? '';
$price_per_night = $_POST['price_per_night'] ?? 0;
$total_price = $_POST['total_price'] ?? 0;
$addons_json = $_POST['addons_json'] ?? null;

if (!$site_id || !$full_name || !$email || !$phone || !$check_in || !$check_out || !$price_per_night) {
    die('<div class="container my-5"><div class="alert alert-danger">All fields are required.</div><a href="sites.php" class="btn btn-primary">Back</a></div>');
}

// Re-check availability
$check_sql = "SELECT COUNT(*) FROM reservations WHERE site_id = ? AND status IN ('pending','confirmed') AND (check_in < ? AND check_out > ?)";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([$site_id, $check_out, $check_in]);
if ($check_stmt->fetchColumn() > 0) {
    die('<div class="container my-5"><div class="alert alert-warning">Sorry, this campsite is no longer available.</div><a href="sites.php" class="btn btn-primary">Back</a></div>');
}

try {
    $user_id = $_SESSION['user_id'];

    // Insert reservation with source = 'online'
    $insert = $pdo->prepare("INSERT INTO reservations (user_id, site_id, check_in, check_out, price_per_night, total_price, addons, status, payment_status, source) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'online')");
    $insert->execute([$user_id, $site_id, $check_in, $check_out, $price_per_night, $total_price, $addons_json]);

    $reservation_id = $pdo->lastInsertId();

    // Redirect to payment page
    header("Location: payment.php?id=$reservation_id");
    exit;

} catch (PDOException $e) {
    die('<div class="container my-5"><div class="alert alert-danger">Database error: ' . $e->getMessage() . '</div><a href="sites.php" class="btn btn-primary">Back</a></div>');
}
?>