<?php
// edit_profile.php
$page_title = "Edit Profile";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to edit profile.";
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);

    // Validate inputs
    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists (but not for current user)
        $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $user_id]);
        if ($check_email->rowCount() > 0) {
            $error = "This email is already in use.";
        } else {
            // Check if username already exists (but not for current user)
            $check_username = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check_username->execute([$username, $user_id]);
            if ($check_username->rowCount() > 0) {
                $error = "This username is already taken.";
            } else {
                // Update user profile
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$username, $email, $phone, $user_id])) {
                    // Update session
                    $_SESSION['username'] = $username;
                    $success = "Profile updated successfully!";
                    // Refresh user data
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                } else {
                    $error = "Failed to update profile. Please try again.";
                }
            }
        }
    }
}
?>

<div class="content">
    <div class="auth-container">
        <div class="auth-box">
            <h1>Edit Profile</h1>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter username" value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn-primary">Update Profile</button>
                <a href="dashboard.php" class="btn-secondary" style="display: inline-block; margin-top: 10px;">Cancel</a>
            </form>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <p>Want to change your password? <a href="change_password.php">Click here</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
