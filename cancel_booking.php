<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'camper') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user_dashboard.php');
    exit;
}

$booking_id = $_POST['booking_id'] ?? 0;
if (!$booking_id) {
    $_SESSION['cancel_error'] = 'Invalid booking ID.';
    header('Location: user_dashboard.php');
    exit;
}

// Fetch the booking and verify it belongs to the logged‑in user
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['cancel_error'] = 'Booking not found or does not belong to you.';
    header('Location: user_dashboard.php');
    exit;
}

// Determine if cancellation is allowed
$can_cancel = false;
$now = new DateTime();
$check_in = new DateTime($booking['check_in']);
$hours_until_checkin = $now->diff($check_in)->h + ($now->diff($check_in)->days * 24);

if ($booking['status'] === 'pending' || $booking['status'] === 'quoted') {
    $can_cancel = true;
} elseif ($booking['status'] === 'confirmed') {
    if ($hours_until_checkin > 24) {
        $can_cancel = true;
    }
}

if (!$can_cancel) {
    $_SESSION['cancel_error'] = 'This booking cannot be cancelled at this time.';
    header('Location: user_dashboard.php');
    exit;
}

// Perform cancellation: set status = 'cancelled', payment_status = 'pending' (admin will refund later)
$update = $pdo->prepare("UPDATE reservations SET status = 'cancelled', payment_status = 'pending' WHERE id = ?");
$update->execute([$booking_id]);

$_SESSION['cancel_success'] = 'Booking cancelled successfully. Your refund request has been submitted. Admin will process it shortly.';
header('Location: user_dashboard.php');
exit;