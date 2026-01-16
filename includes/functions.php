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

// Send email function
function sendEmail($recipient_email, $recipient_name, $subject, $body)
{
    // Email configuration - Modify these for your server
    $from_email = 'noreply@fastdata.local';
    $from_name = 'FastData Support';

    // Create email headers
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Compose full message
    $message = "Dear " . htmlspecialchars($recipient_name) . ",\n\n";
    $message .= $body . "\n\n";
    $message .= "---\n";
    $message .= "FastData Support Team\n";
    $message .= SITE_URL . "\n";

    // Send email
    $result = mail($recipient_email, $subject, $message, $headers);

    if (!$result) {
        error_log("Failed to send email to: " . $recipient_email);
    }

    return $result;
}

// Generate transaction reference
// function generateTransactionRef()
// {
//     return 'TXN_' . time() . '_' . rand(1000, 9999);
// }
