<?php
// password_reset.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$message = '';
$error = '';
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$token_valid = false;
$user = null;

// Validate token
if (!empty($token) && !empty($email)) {
    $stmt = $pdo->prepare("SELECT id, username, email, reset_token, reset_token_expires FROM users WHERE email = ? AND reset_token IS NOT NULL");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if token matches and hasn't expired
        if (password_verify($token, $user['reset_token'])) {
            $expires = strtotime($user['reset_token_expires']);
            if (time() < $expires) {
                $token_valid = true;
            } else {
                $error = "Password reset link has expired. Please request a new one.";
            }
        } else {
            $error = "Invalid password reset link.";
        }
    } else {
        $error = "Invalid password reset link.";
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash and update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$password_hash, $user['id']]);

            $message = "Password reset successfully! You can now <a href='login.php'>login with your new password</a>.";
            $token_valid = false; // Hide form after successful reset
        } catch (Exception $e) {
            $error = "Failed to reset password. Please try again.";
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FastData</title>
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
            <h1>Reset Password</h1>

            <?php if ($error): ?>
                <div class="message error-message">
                    <i class='bx bx-error-circle'></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="message success-message">
                    <i class='bx bx-check-circle'></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($token_valid): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter new password" required>
                        <small>Minimum 6 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%;">
                        Reset Password
                    </button>
                </form>
            <?php elseif (!$message): ?>
                <p style="text-align: center; color: #d9534f;">
                    Invalid or expired password reset link. <a href="forgot_password.php">Request a new one</a>
                </p>
            <?php endif; ?>

            <p style="text-align: center; margin-top: 20px;">
                <a href="login.php">Back to Login</a>
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
