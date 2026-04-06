<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

$host = 'localhost';
$dbname = 'campease';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

define('BASE_URL', 'http://localhost/campease');
?>