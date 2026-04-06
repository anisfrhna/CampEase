<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}