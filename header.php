<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampEase - Bagan Lalang Campsite Reservation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-inner container">
        <a href="index.php" class="brand">
            <!-- <img src="campease.png" alt="CampEase Logo" class="brand-logo"> -->
            <div class="brand-text">CampEase</div>
        </a>

        <div class="right">
            <nav class="nav">
                <a href="index.php#about">About</a>
                <a href="index.php#campsite-map">Map</a>
                <a href="index.php#faq">FAQ</a>
                <a href="index.php#contact">Contact</a>
                <a href="sites.php" class="book-btn">Book Now</a>
            </nav>

            <div class="auth-box">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- User greeting -->
                    <span class="user-greeting">
                        <i class="fas fa-user-circle"></i> Hi, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    </span>
                    
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a class="dashboard-btn" href="admin/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a class="dashboard-btn" href="user_dashboard.php">
                            <i class="fas fa-user"></i> My Booking
                        </a>
                    <?php endif; ?>
                    
                    <a class="logout-btn" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a class="dashboard-btn" href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a class="logout-btn" href="register.php">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main>