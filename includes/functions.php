<?php
// includes/functions.php

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Redirect function
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

// Sanitize input
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
// function generateTransactionRef()
// {
//     return 'TXN_' . time() . '_' . rand(1000, 9999);
// }
