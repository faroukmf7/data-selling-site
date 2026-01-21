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

// Sanitize input function
function sanitizeInput($input)
{
    return htmlspecialchars(strip_tags(trim($input ?? '')));
}

// Check if complaints table exists and create if needed
function ensureComplaintsTableExists($pdo)
{
    try {
        $pdo->query("SELECT 1 FROM complaints LIMIT 1");
    } catch (PDOException $e) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE `complaints` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `order_id` INT,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `status` ENUM('pending', 'in_review', 'resolved', 'rejected') DEFAULT 'pending',
            `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
            `category` VARCHAR(100),
            `resolution_note` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_order_id (order_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_user_created (user_id, created_at)
        )";
        $pdo->exec($sql);
    }
}

