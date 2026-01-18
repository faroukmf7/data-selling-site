<?php
// checkout.php
$page_title = "Checkout";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to checkout.";
    redirect('login.php');
}

// Handle direct product purchase (from products.php form)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $recipient_number = sanitize($_POST['recipient_number']);
    $data_amount = isset($_POST['data_amount']) ? (float)$_POST['data_amount'] : null;
    $fixed_price = isset($_POST['fixed_price']) ? (float)$_POST['fixed_price'] : null;
    $exam_type = isset($_POST['exam_type']) ? sanitize($_POST['exam_type']) : null;

    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['message'] = "Product not found.";
        redirect('products.php');
    }

    // Calculate total
    if ($product['is_flexible'] && $data_amount) {
        // Flexible data plan
        if (
            $data_amount < $product['min_value'] ||
            ($product['max_value'] && $data_amount > $product['max_value'])
        ) {
            $_SESSION['message'] = "Invalid data amount. Please enter between " .
                $product['min_value'] . " and " . $product['max_value'] .
                $product['unit'] . ".";
            redirect('products.php');
        }

        $total = $data_amount * $product['price_per_unit'];
        $description = $data_amount . $product['unit'] . " " . $product['name'];
    } else {
        // Fixed price product
        $total = $fixed_price ?: $product['price'];
        $description = $product['name'];

        if ($exam_type) {
            $description = $exam_type . " " . $description;
        }
    }

    // Store order in session for payment processing
    $_SESSION['current_order'] = [
        'product_id' => $product_id,
        'product_name' => $description,
        'recipient_number' => $recipient_number,
        'total_amount' => $total,
        'data_amount' => $data_amount,
        'exam_type' => $exam_type,
        'network' => $product['network'],
        'category' => $product['category']
    ];

    // Validate that total_amount is greater than 0
    if ($total <= 0) {
        $_SESSION['message'] = "Invalid order amount.";
        redirect('products.php');
    }

    // Stay on checkout page to show payment options
    // No redirect - payment options are shown below
}

// If no current order, redirect to products
if (!isset($_SESSION['current_order'])) {
    redirect('products.php');
}

$order = $_SESSION['current_order'];
?>

<div class="content">
    <h1>Checkout</h1>

    <div class="checkout-direct">
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="summary-item">
                <span>Product:</span>
                <span class="value"><?php echo $order['product_name']; ?></span>
            </div>

            <?php if ($order['recipient_number']): ?>
                <div class="summary-item">
                    <span>Recipient:</span>
                    <span class="value"><?php echo $order['recipient_number']; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($order['data_amount']): ?>
                <div class="summary-item">
                    <span>Data Amount:</span>
                    <span class="value"><?php echo $order['data_amount']; ?>GB</span>
                </div>
            <?php endif; ?>

            <?php if ($order['exam_type']): ?>
                <div class="summary-item">
                    <span>Exam Type:</span>
                    <span class="value"><?php echo $order['exam_type']; ?></span>
                </div>
            <?php endif; ?>

            <div class="summary-item total">
                <span>Total Amount:</span>
                <span class="value">GHS <?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>

        <div class="payment-options">
            <h3>Select Payment Method</h3>

            <div class="payment-method">
                <button id="paystack-button" class="payment-option-btn paystack-btn">
                    <i class='bx bx-credit-card'></i>
                    <span>Pay with Card/Mobile Money</span>
                    <small>Secure payment via Paystack</small>
                </button>
            </div>

            <?php if (getUserBalance($pdo, $_SESSION['user_id']) >= $order['total_amount']): ?>
                <div class="payment-method">
                    <a href="process_wallet_payment.php" class="payment-option-btn wallet">
                        <i class='bx bx-wallet'></i>
                        <span>Pay with Wallet Balance</span>
                        <small>Available: GHS <?php echo number_format(getUserBalance($pdo, $_SESSION['user_id']), 2); ?></small>
                    </a>
                </div>
            <?php else: ?>
                <div class="payment-method disabled">
                    <div class="payment-option-btn">
                        <i class='bx bx-wallet'></i>
                        <span>Pay with Wallet Balance</span>
                        <small class="insufficient">Insufficient balance. Need GHS <?php
                                                                                    $needed = $order['total_amount'] - getUserBalance($pdo, $_SESSION['user_id']);
                                                                                    echo number_format($needed, 2);
                                                                                    ?> more.</small>
                    </div>
                </div>
            <?php endif; ?>

            <div class="back-to-products">
                <a href="products.php" class="btn-secondary">
                    <i class='bx bx-arrow-back'></i> Back to Products
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Generate payment reference if needed for Paystack
$payment_reference = 'PAY_' . time() . '_' . $_SESSION['user_id'] . '_' . rand(1000, 9999);
$_SESSION['payment_reference'] = $payment_reference;
?>

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
            email: '<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>',
            amount: <?php echo (int)($order['total_amount'] * 100); ?>,
            currency: 'GHS',
            ref: '<?php echo htmlspecialchars($payment_reference); ?>',
            channels: ['mobile_money'],
            callback: function(response) {
                // Redirect to verification page
                window.location.href = 'verify_payment.php?reference=' + encodeURIComponent(response.reference);
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

<?php require_once 'includes/footer.php'; ?>