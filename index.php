<?php
// index.php
$page_title = "Home";
require_once 'includes/header.php';
?>

<div class="content">
    <div class="title-section">
        <div class="title-main">Affordable Data Solutions</div>
        <div class="title-des">FastData provides high-speed data services at competitive prices.</div>

        <?php if (isLoggedIn()): ?>
            <div class="user-welcome">
                Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>!
                Your balance: <span class="balance"><?php echo formatCurrency(getUserBalance($pdo, $_SESSION['user_id'])); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Purchase Section -->
    <div class="quick-purchase">
        <h2>Quick Purchase</h2>
        <p class="quick-subtitle" style="text-align: center;
    margin-bottom: 10px;">Select a product to get started</p>
        <div class="quick-grid">
            <a href="products.php?network=MTN&category=data" class="quick-item">
                <img src="images/MTN.png" alt="MTN">
                <span>MTN Data</span>
                <small>From GHS 0.50/GB</small>
            </a>

            <a href="products.php?network=AIRTEL-TIGO&category=data" class="quick-item">
                <img src="images/airteltigo.png" alt="Airtel-Tigo">
                <span>Airtel-Tigo Data</span>
                <small>From GHS 0.45/GB</small>
            </a>

            <a href="products.php?network=TELECEL&category=data" class="quick-item">
                <img src="images/telecel.png" alt="Telecel">
                <span>Telecel Data</span>
                <small>From GHS 0.55/GB</small>
            </a>

            <a href="products.php?category=airtime" class="quick-item">
                <i class='bx bx-phone'></i>
                <span>Airtime</span>
                <small>All networks</small>
            </a>

            <a href="products.php?category=exam_pin" class="quick-item">
                <i class='bx bx-book'></i>
                <span>Exam PINs</span>
                <small>WAEC, NECO, BECE</small>
            </a>
        </div>

        <?php if (!isLoggedIn()): ?>
            <div class="guest-option">
                <p>Don't have an account?</p>
                <a href="products.php" class="btn-guest-option">
                    <i class='bx bx-user'></i> Continue as Guest
                </a>
                <span class="divider">or</span>
                <a href="login.php" class="btn-login-option">
                    <i class='bx bx-log-in'></i> Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>