<?php
// verify_payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    $_SESSION['message'] = "No payment reference provided.";
    redirect('products.php');
}

// Verify payment with Paystack
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    $_SESSION['message'] = "Payment verification failed: Network error.";
    redirect('checkout.php');
}

$result = json_decode($response, true);

// Debug: Check if response is valid
if (!$result) {
    $_SESSION['message'] = "Invalid payment response.";
    redirect('checkout.php');
}

// Verify payment status
if (!isset($result['status']) || $result['status'] !== true) {
    $_SESSION['message'] = "Payment verification failed. Please try again.";
    redirect('checkout.php');
}

// Check if transaction status is success
if (!isset($result['data']['status']) || $result['data']['status'] !== 'success') {
    $_SESSION['message'] = "Payment not completed. Status: " . ($result['data']['status'] ?? 'unknown');
    redirect('checkout.php');
}

// Payment successful
if (!isset($_SESSION['current_order'])) {
    $_SESSION['message'] = "Order data missing.";
    redirect('products.php');
}

$order = $_SESSION['current_order'];
$user_id = $_SESSION['user_id'];

// Start transaction
$pdo->beginTransaction();

try {
    // Create transaction record with timestamp
    $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, product_id, amount, recipient_number, transaction_id, status, provider_reference, created_at) 
                          VALUES (?, ?, ?, ?, ?, 'successful', ?, NOW())");
    $stmt->execute([
        $user_id,
        $order['product_id'],
        $order['total_amount'],
        $order['recipient_number'],
        $transaction_id,
        $reference
    ]);

    // Create order record
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'completed', NOW())");
    $stmt->execute([$user_id, $order['total_amount']]);

    // Commit transaction
    $pdo->commit();

    // Clear current order
    unset($_SESSION['current_order']);

    // Deliver the product (in real system, you'd call telecom API here)
    $_SESSION['message'] = "Payment successful! Your " . $order['product_name'] .
        " has been delivered to " . $order['recipient_number'] . ".";

    redirect('dashboard.php');
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Error processing order: " . $e->getMessage();
    redirect('checkout.php');
}
