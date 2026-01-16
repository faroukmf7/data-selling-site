<?php
// includes/header.php

// Start session and load config first
session_start();
require_once 'config.php';
require_once 'functions.php';

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
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="admin/">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
            <li><a href="#">Contact</a></li>
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