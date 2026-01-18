<?php
// guest_verify_payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$reference = isset($_GET['reference']) ? sanitize($_GET['reference']) : '';

if (empty($reference)) {
    $_SESSION['message'] = "Invalid payment reference.";
    redirect('products.php');
}

// Verify payment with Paystack
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification for development
    CURLOPT_SSL_VERIFYHOST => false,  // Disable hostname verification for development
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache",
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($err) {
    error_log("Paystack verification error: " . $err);
    $_SESSION['message'] = "Error verifying payment. Please try again or contact support.";
    redirect('products.php');
}

if ($http_code !== 200) {
    error_log("Paystack HTTP error: " . $http_code . " Response: " . $response);
    $_SESSION['message'] = "Payment verification failed. Please try again or contact support.";
    redirect('products.php');
}

$result = json_decode($response, true);

if (!$result) {
    error_log("Invalid Paystack response: " . $response);
    $_SESSION['message'] = "Invalid payment response. Please contact support.";
    redirect('products.php');
}

$transaction = $result['data'];

// Check if payment was successful
if ($transaction['status'] !== 'success') {
    $_SESSION['message'] = "Payment was not successful. Status: " . $transaction['status'];
    redirect('products.php');
}

// Get order details from session
if (!isset($_SESSION['current_order']) || !$_SESSION['current_order']['is_guest']) {
    $_SESSION['message'] = "Session expired. Please try again.";
    redirect('products.php');
}

$order = $_SESSION['current_order'];

// Verify amount matches (Paystack returns amount in kobo)
$paystack_amount = $transaction['amount'] / 100;
if (abs($paystack_amount - $order['total_amount']) > 0.01) {
    error_log("Amount mismatch - Order: " . $order['total_amount'] . ", Paystack: " . $paystack_amount);
    $_SESSION['message'] = "Payment amount mismatch. Please contact support.";
    redirect('products.php');
}

// Record guest transaction
try {
    $stmt = $pdo->prepare("
        INSERT INTO guest_transactions 
        (reference, guest_email, guest_phone, recipient_number, product_id, 
         amount, product_name, network, category, data_amount, exam_type, 
         status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $reference,
        $order['guest_email'],
        $order['guest_phone'],
        $order['recipient_number'],
        $order['product_id'],
        $order['total_amount'],
        $order['product_name'],
        $order['network'],
        $order['category'],
        $order['data_amount'] ?? null,
        $order['exam_type'] ?? null,
        'completed'
    ]);
    
    $transaction_id = $pdo->lastInsertId();
    
    // Send confirmation email to guest
    $email_subject = "Payment Confirmation - FastData";
    $email_body = "Thank you for your purchase!\n\n";
    $email_body .= "Transaction Reference: " . $reference . "\n";
    $email_body .= "Product: " . $order['product_name'] . "\n";
    $email_body .= "Recipient: " . $order['recipient_number'] . "\n";
    $email_body .= "Amount: GHS " . number_format($order['total_amount'], 2) . "\n\n";
    $email_body .= "Your service will be delivered shortly.\n";
    $email_body .= "If you have any issues, please contact our support team.\n";
    
    sendEmail($order['guest_email'], 'Guest Customer', $email_subject, $email_body);
    
} catch (PDOException $e) {
    error_log("Guest transaction insertion error: " . $e->getMessage());
    // Continue even if database insert fails, payment was verified
}

// Clear session data
unset($_SESSION['current_order']);
unset($_SESSION['payment_reference']);
unset($_SESSION['payment_amount']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - FastData</title>
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
        <div class="success-container">
            <div class="success-icon">
                <i class='bx bx-check-circle'></i>
            </div>
            <h1>Payment Successful!</h1>
            <p class="subtitle">Your order has been confirmed</p>

            <div class="success-details">
                <div class="detail-row">
                    <span>Reference:</span>
                    <span class="highlight"><?php echo htmlspecialchars($reference); ?></span>
                </div>
                <div class="detail-row">
                    <span>Product:</span>
                    <span><?php echo htmlspecialchars($order['product_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Recipient:</span>
                    <span><?php echo htmlspecialchars($order['recipient_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Amount Paid:</span>
                    <span class="amount">GHS <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div class="info-box success-message">
                <h4><i class='bx bx-info-circle'></i> Next Steps</h4>
                <p>A confirmation email has been sent to <strong><?php echo htmlspecialchars($order['guest_email']); ?></strong></p>
                <p>Your service will be delivered within the next few minutes. Please check your phone balance.</p>
            </div>

            <div class="actions">
                <a href="index.php" class="btn-primary">
                    <i class='bx bx-home'></i> Back to Home
                </a>
                <a href="products.php" class="btn-secondary">
                    <i class='bx bx-shopping-bag'></i> Buy More
                </a>
            </div>

            <div class="support-info">
                <p>Need help? <a href="#">Contact our support team</a></p>
            </div>
        </div>
    </div>
</body>

</html>
