<?php
// admin/includes/admin_header.php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate correct path to includes
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/fastdata';
require_once $root_path . '/includes/config.php';

// Admin check function (define here since we're not including functions.php)
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function redirect($url)
{
    header("Location: " . $url);
    exit();
}

function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

// Format currency
function formatCurrency($amount)
{
    return 'GHS ' . number_format($amount, 2);
}

// Get user balance
function getUserBalance($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    return $user['balance'] ?? 0;
}

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
            <li><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
            <li><a href="products.php"><i class='bx bxs-package'></i> Products</a></li>
            <li><a href="users.php"><i class='bx bxs-user'></i> Users</a></li>
            <li><a href="orders.php"><i class='bx bxs-cart'></i> Orders</a></li>
            <li><a href="transactions.php"><i class='bx bxs-receipt'></i> Transactions</a></li>
            <li><a href="<?php echo SITE_URL; ?>/dashboard.php"><i class='bx bxs-user-circle'></i> My Account</a></li>
            <li><a href="<?php echo SITE_URL; ?>/logout.php"><i class='bx bx-log-out'></i> Logout</a></li>
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

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const adminNav = document.getElementById('adminNav');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                adminNav.classList.toggle('active');
                menuToggle.classList.toggle('active');
            });
        }
        
        // Close menu when a link is clicked
        const navLinks = document.querySelectorAll('.admin-nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                adminNav.classList.remove('active');
                menuToggle.classList.remove('active');
            });
        });
    });
</script>