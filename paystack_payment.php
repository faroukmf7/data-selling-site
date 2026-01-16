<?php
// paystack_payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['current_order'])) {
    $_SESSION['message'] = "No order to process.";
    redirect('products.php');
}

$order = $_SESSION['current_order'];
$user_id = $_SESSION['user_id'];

// Generate unique reference
$reference = 'PAY_' . time() . '_' . $user_id . '_' . rand(1000, 9999);

// Store reference in session
$_SESSION['payment_reference'] = $reference;
$_SESSION['payment_amount'] = $order['total_amount'] * 100; // Convert to kobo

// Get user email
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if user exists and has email
if (!$user || !$user['email']) {
    $_SESSION['message'] = "Unable to retrieve user information.";
    redirect('checkout.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - FastData</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="header">
        <div class="title">FastData</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="payment-container">
            <h1>Complete Payment</h1>

            <div class="payment-info">
                <div class="info-row">
                    <span>Order:</span>
                    <span><?php echo $order['product_name']; ?></span>
                </div>
                <div class="info-row">
                    <span>Amount:</span>
                    <span class="amount">GHS <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="info-row">
                    <span>Reference:</span>
                    <span class="reference"><?php echo $reference; ?></span>
                </div>
            </div>

            <button id="paystack-button" class="btn-primary btn-paystack">
                <i class='bx bx-credit-card'></i> Pay with Paystack
            </button>

            <div class="alternative-payment">
                <a href="checkout.php" class="btn-secondary">
                    <i class='bx bx-arrow-back'></i> Back to Checkout
                </a>
            </div>
        </div>
    </div>

    <!-- Paystack Inline JS -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        document.getElementById('paystack-button').addEventListener('click', function(e) {
            e.preventDefault();

            const handler = PaystackPop.setup({
                key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
                email: '<?php echo $user['email']; ?>',
                amount: <?php echo $order['total_amount'] * 100; ?>,
                currency: 'GHS',
                ref: '<?php echo $reference; ?>',
                callback: function(response) {
                    // Redirect to verification page
                    window.location.href = 'verify_payment.php?reference=' + response.reference;
                },
                onClose: function() {
                    alert('Payment cancelled. You can try again.');
                }
            });

            handler.openIframe();
        });
    </script>
</body>

</html>