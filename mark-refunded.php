<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: manage-reservations.php');
    exit;
}

// Check that the booking is cancelled and not already refunded
$stmt = $pdo->prepare("SELECT status, payment_status FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found.';
    header('Location: manage-reservations.php');
    exit;
}

if ($booking['status'] !== 'cancelled') {
    $_SESSION['error'] = 'Only cancelled bookings can be marked as refunded.';
    header('Location: manage-reservations.php');
    exit;
}

if ($booking['payment_status'] === 'refunded') {
    $_SESSION['error'] = 'Booking already marked as refunded.';
    header('Location: manage-reservations.php');
    exit;
}

// Update payment status to refunded
$update = $pdo->prepare("UPDATE reservations SET payment_status = 'refunded' WHERE id = ?");
$update->execute([$id]);

$_SESSION['success'] = 'Booking marked as refunded.';
header('Location: manage-reservations.php');
exit;