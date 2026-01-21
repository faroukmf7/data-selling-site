<?php
// admin/includes/admin_header.php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate correct path to includes
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/fastdata';
require_once $root_path . '/includes/config.php';
require_once $root_path . '/includes/functions.php';

// Generate transaction reference
function generateTransactionRef()
{
    return 'TXN_' . time() . '_' . rand(1000, 9999);
}

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['message'] = "Access denied. Admin privileges required.";
    header("Location: " . SITE_URL . "/login.php");
    exit();
}

// Set page title if not set
if (!isset($page_title)) {
    $page_title = "Admin Panel";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - FastData Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-title">
            <i class='bx bxs-dashboard'></i>
            <span>FastData Admin</span>
        </div>
        <button class="menu-toggle" id="menuToggle">
            <i class='bx bx-menu'></i>
        </button>
        <ul class="admin-nav" id="adminNav">
            <li class="nav-brand" style="display: flex; align-items: center; justify-content: space-between; padding: 20px; width: 100%; border: none; margin: 0px 0px 10px 0px; border-radius: 0px; background-color: #2b2d42;">
                <div>
                    <div><i class='bx bxs-dashboard' style="font-size: 1.5em; color: #fff;"></i></div>
                    <div style="font-size: 1.8em; color: #fff;">FastData Admin</div>
                </div>
                <div class="close-menu"><i class='bx bx-x' style="font-size: 1.5em; color: #fff;"></i></div>
            </li>
            <li><a href="index.php"><i class='bx bxs-dashboard nav-icon'></i> Dashboard</a></li>
            <li><a href="products.php"><i class='bx bxs-package nav-icon'></i> Products</a></li>
            <li><a href="users.php"><i class='bx bxs-user nav-icon'></i> Users</a></li>
            <li><a href="orders.php"><i class='bx bxs-cart nav-icon'></i> Orders</a></li>
            <li><a href="transactions.php"><i class='bx bxs-receipt nav-icon'></i> Transactions</a></li>
            <li><a href="complaints.php"><i class='bx bx-message-square-error nav-icon'></i> Complaints</a></li>
            <li><a href="<?php echo SITE_URL; ?>/dashboard.php"><i class='bx bxs-user-circle nav-icon'></i> My Account</a></li>
            <li><a href="<?php echo SITE_URL; ?>/logout.php"><i class='bx bx-log-out nav-icon'></i> Logout</a></li>
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

    <div class="admin-container">
        <div class="admin-content">

<script>
    // Mobile menu toggle with backdrop blur animation
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const adminNav = document.getElementById('adminNav');
        const body = document.body;
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                adminNav.classList.toggle('active');
                body.classList.toggle('menu-open');
            });
            
            // Close button functionality
            const closeButton = adminNav.querySelector('.bx-x');
            if (closeButton) {
                closeButton.parentElement.addEventListener('click', function(e) {
                    e.stopPropagation();
                    adminNav.classList.remove('active');
                    body.classList.remove('menu-open');
                });
            }
        }
        
        // Close menu when a link is clicked
        const navLinks = document.querySelectorAll('.admin-nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                adminNav.classList.remove('active');
                body.classList.remove('menu-open');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.admin-header')) {
                adminNav.classList.remove('active');
                body.classList.remove('menu-open');
            }
        });
        
        // Close menu when clicking on the blur background
        document.addEventListener('click', function(event) {
            if (event.target === document.body && adminNav.classList.contains('active')) {
                adminNav.classList.remove('active');
                body.classList.remove('menu-open');
            }
        });
    });
</script>