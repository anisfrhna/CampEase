<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: manage-reservations.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed', payment_status = 'paid', payment_date = NOW() WHERE id = ?");
$stmt->execute([$id]);

header('Location: manage-reservations.php?msg=payment_confirmed');
exit;