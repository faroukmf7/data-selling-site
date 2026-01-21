<?php
// includes/config.php

// Database configuration
define('DB_HOST', 'srv2112.hstgr.io'); // Change if your database is hosted elsewhere
define('DB_USER', 'u160082954_fastdata'); // Change to your database username
define('DB_PASS', 'Fast_data@44'); // Change to your database password
define('DB_NAME', 'u160082954_fastdata_db');

// Paystack Configuration
define('PAYSTACK_SECRET_KEY', 'sk_test_87ffb42fab8756b17e93fe1d18ad0641d462ea75'); // Your Paystack secret key
define('PAYSTACK_PUBLIC_KEY', 'pk_test_f32a58ce51e18d02b2aa2f30747b0ff7a0c04a60'); // Your Paystack public key
define('PAYSTACK_CALLBACK_URL', 'https://flashdatagh.com/verify_payment.php'); // Callback URL

// Site configuration
define('SITE_NAME', 'FlastData');
define('SITE_URL', 'https://flashdatagh.com'); // Change to your site URL

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
