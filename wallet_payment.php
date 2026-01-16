<?php
// wallet_payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['current_order']) || $_SESSION['current_order']['category'] !== 'wallet') {
    $_SESSION['message'] = "Invalid wallet topup request.";
    redirect('add_funds.php');
}

$user_id = $_SESSION['user_id'];
$amount = $_SESSION['wallet_amount'] / 100; // Convert from kobo back to GHS
$reference = $_SESSION['wallet_reference'];

// Get user email
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !$user['email']) {
    $_SESSION['message'] = "Unable to retrieve user information.";
    redirect('add_funds.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Top-up Payment - FastData</title>
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
        <div class="payment-container">
            <h1>Wallet Top-up Payment</h1>

            <div class="payment-info">
                <div class="info-row">
                    <span>Type:</span>
                    <span>Wallet Top-up</span>
                </div>
                <div class="info-row">
                    <span>Amount:</span>
                    <span class="amount">GHS <?php echo number_format($amount, 2); ?></span>
                </div>
                <div class="info-row">
                    <span>Reference:</span>
                    <span class="reference"><?php echo $reference; ?></span>
                </div>
            </div>

            <button id="paystack-button" class="btn-primary btn-paystack">
                <i class='bx bx-credit-card'></i> Pay with Paystack
            </button>

            <a href="add_funds.php" class="btn-secondary" style="display: block; text-align: center; margin-top: 10px;">
                Cancel
            </a>
        </div>
    </div>

    <div class="footer">
        <div class="footer-text">© 2026 FastData Inc.</div>
    </div>

    <!-- Fixed button for request-callback -->
    <div class="fixed-button">
        <a href="login.php"><i class='bx bxs-phone'></i> Request-callback</a>
    </div>

    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="js/script.js"></script>
    <script>
        document.getElementById("paystack-button").addEventListener("click", function(e) {
            e.preventDefault();

            var handler = PaystackPop.setup({
                key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
                email: '<?php echo $user['email']; ?>',
                amount: <?php echo intval($amount * 100); ?>, // Amount in kobo
                ref: '<?php echo $reference; ?>',
                currency: 'GHS',
                onClose: function() {
                    alert('Payment window closed.');
                },
                onSuccess: function(response) {
                    // Payment successful, verify and redirect
                    if (response.reference) {
                        window.location.href = 'verify_wallet_payment.php?reference=' + response.reference;
                    }
                }
            });
            handler.openIframe();
        });
    </script>
</body>

</html>
