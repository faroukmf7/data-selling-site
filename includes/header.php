<?php
// includes/header.php

// Start session and load config first
session_start();
require_once 'config.php';
require_once 'functions.php';

// Store user email in session if logged in
if (isLoggedIn() && !isset($_SESSION['user_email'])) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_email'] = $user['email'];
    }
}

// Now functions are available
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FastData</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <!-- Header section -->
    <div class="header">
        <div class="title">FastData</div>
        <button class="menu-toggle" id="menuToggle">
            <i class='bx bx-menu' style="font-size: 0.8em;"></i>
        </button>
        <ul class="nav-menu" id="navMenu">
            <?php if (isLoggedIn()): ?>
                <li><i class='bx bxs-dashboard nav-icon'></i><a href="dashboard.php">Dashboard</a></li>
            <?php else: ?>
            <?php endif; ?>
            <li><i class='bx bx-home nav-icon'></i><a href="index.php">Home</a></li>
            <li><i class='bx bx-cabinet nav-icon'></i><a href="products.php">Products</a></li>
            <?php if (!isLoggedIn()): ?>
                <li><i class='bx bx-log-in-circle nav-icon'></i><a href="login.php">Login</a></li>
                <li><i class='bx bx-edit nav-icon'></i><a href="register.php">Register</a></li>
            <?php else: ?>
            <?php endif; ?>
            <li class="nav-contact" style="margin: 20px 20px; padding: 10px 10px"><i class='bx bxl-whatsapp nav-icon' style="background-color: hsla(120, 89%, 45%, 0.60);"></i><a href="#">Join our community</a></li>
            <?php if (isLoggedIn()): ?>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li> <i class='bx bxl-graphql nav-icon'></i><a href="admin/">Admin</a></li>
                <?php endif; ?>
                <li><i class='bx bx-log-out-circle nav-icon'></i> <a href="logout.php">Logout</a></li>
            <?php else: ?>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Display flash messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="flash-message">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>