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

// Fetch the booking to ensure it exists and is not already cancelled
$stmt = $pdo->prepare("SELECT status FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking || $booking['status'] === 'cancelled') {
    $_SESSION['error'] = 'Booking cannot be cancelled.';
    header('Location: manage-reservations.php');
    exit;
}

// If reason is provided via GET (from modal), process it
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : '';
if ($reason === '') {
    // No reason – just cancel without reason (fallback, but we'll use modal)
    $reason = null;
}

// Update booking status to cancelled and store reason
$update = $pdo->prepare("UPDATE reservations SET status = 'cancelled', cancellation_reason = ? WHERE id = ?");
$update->execute([$reason, $id]);

$_SESSION['success'] = 'Booking cancelled successfully.';
header('Location: manage-reservations.php');
exit;