<?php
// forgot_password.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, email, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Don't reveal if email exists or not (security best practice)
            $message = "If an account exists with this email, a password reset link has been sent.";
        } else {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $token_hash = password_hash($reset_token, PASSWORD_DEFAULT);
            $token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $stmt->execute([$token_hash, $token_expires, $user['id']]);

            // Send email with reset link
            $reset_link = SITE_URL . "/password_reset.php?token=" . $reset_token . "&email=" . urlencode($email);
            $subject = "Password Reset Request - FastData";
            $body = "Hi " . htmlspecialchars($user['username']) . ",\n\n";
            $body .= "You requested a password reset. Click the link below to reset your password:\n\n";
            $body .= $reset_link . "\n\n";
            $body .= "This link will expire in 1 hour.\n\n";
            $body .= "If you did not request this, please ignore this email.\n\n";
            $body .= "Best regards,\nFastData Support";

            if (sendEmail($email, $user['username'], $subject, $body)) {
                $message = "If an account exists with this email, a password reset link has been sent.";
            } else {
                // Even if email fails, show success message for security
                $message = "If an account exists with this email, a password reset link has been sent.";
                error_log("Failed to send password reset email to: " . $email);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FastData</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
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

    <div class="content">
        <div class="auth-container">
            <h1>Forgot Password</h1>
            <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</p>

            <?php if ($error): ?>
                <div class="message error-message">
                    <i class='bx bx-error-circle'></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="message success-message">
                    <i class='bx bx-check-circle'></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">
                    Send Reset Link
                </button>
            </form>

            <p style="text-align: center; margin-top: 20px;">
                Remember your password? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <div class="footer">
        <div class="footer-text">© 2026 FastData Inc.</div>
    </div>

    <!-- Fixed button for request-callback -->
    <div class="fixed-button">
        <a href="login.php"><i class='bx bxs-phone'></i> Request-callback</a>
    </div>

    <script src="js/script.js"></script>
</body>

</html>
