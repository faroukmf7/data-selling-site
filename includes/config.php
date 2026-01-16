<?php
// includes/config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your database username
define('DB_PASS', ''); // Change to your database password
define('DB_NAME', 'fastdata_db');

// Paystack Configuration
define('PAYSTACK_SECRET_KEY', 'sk_test_87ffb42fab8756b17e93fe1d18ad0641d462ea75'); // Your Paystack secret key
define('PAYSTACK_PUBLIC_KEY', 'pk_test_f32a58ce51e18d02b2aa2f30747b0ff7a0c04a60'); // Your Paystack public key
define('PAYSTACK_CALLBACK_URL', 'http://localhost/fastdata/verify_payment.php'); // Callback URL

// Site configuration
define('SITE_NAME', 'FastData');
define('SITE_URL', 'http://localhost/fastdata'); // Change to your site URL

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
