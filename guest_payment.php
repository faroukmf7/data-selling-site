<?php
// guest_payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['current_order']) || !$_SESSION['current_order']['is_guest']) {
    $_SESSION['message'] = "Invalid guest order.";
    redirect('products.php');
}

$order = $_SESSION['current_order'];

// Generate unique reference
$reference = 'GUEST_' . time() . '_' . rand(100000, 999999);

// Store reference in session
$_SESSION['payment_reference'] = $reference;
$_SESSION['payment_amount'] = $order['total_amount'] * 100; // Convert to kobo
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Payment - FastData</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="header">
        <div class="title">FastData</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="payment-container">
            <h1>Complete Payment</h1>
            <p class="subtitle">Guest Purchase - No Account Required</p>

            <div class="payment-info">
                <div class="info-row">
                    <span>Order:</span>
                    <span><?php echo htmlspecialchars($order['product_name']); ?></span>
                </div>
                <div class="info-row">
                    <span>Recipient:</span>
                    <span><?php echo htmlspecialchars($order['recipient_number']); ?></span>
                </div>
                <div class="info-row">
                    <span>Email:</span>
                    <span><?php echo htmlspecialchars($order['guest_email']); ?></span>
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
                <a href="products.php" class="btn-secondary">
                    <i class='bx bx-arrow-back'></i> Cancel
                </a>
            </div>

            <div class="info-box">
                <h4><i class='bx bx-shield'></i> Secure Payment</h4>
                <p>This payment is secured by Paystack. Your card information is never shared with FastData.</p>
            </div>
        </div>
    </div>

    <!-- Paystack Inline JS -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        document.getElementById('paystack-button').addEventListener('click', function(e) {
            e.preventDefault();

            const paystackKey = '<?php echo PAYSTACK_PUBLIC_KEY; ?>';
            
            // Check if key is set
            if (!paystackKey || paystackKey.trim() === '') {
                alert('Payment system not configured. Please contact support.');
                return false;
            }

            const handler = PaystackPop.setup({
                key: paystackKey,
                email: '<?php echo htmlspecialchars($order['guest_email']); ?>',
                amount: <?php echo (int)($order['total_amount'] * 100); ?>,
                currency: 'GHS',
                ref: '<?php echo htmlspecialchars($reference); ?>',
                channels: ['mobile_money'],
                callback: function(response) {
                    // Redirect to guest verification page
                    window.location.href = 'guest_verify_payment.php?reference=' + encodeURIComponent(response.reference);
                },
                onError: function(error) {
                    alert('Payment error: ' + error.message);
                },
                onClose: function() {
                    console.log('Payment popup closed');
                }
            });

            handler.openIframe();
            return false;
        });

        // Fallback for button that might not be found
        window.addEventListener('load', function() {
            const btn = document.getElementById('paystack-button');
            if (!btn) {
                console.error('Payment button not found');
            } else {
                console.log('Payment button ready');
            }
        });
    </script>
</body>

</html>
