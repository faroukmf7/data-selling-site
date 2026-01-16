<?php
// login.php
$page_title = "Login";
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];

        $_SESSION['message'] = "Login successful! Welcome back, " . $user['username'] . "!";
        redirect('dashboard.php');
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login to FastData</h2>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>