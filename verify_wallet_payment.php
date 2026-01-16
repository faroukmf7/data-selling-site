<?php
// verify_wallet_payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    $_SESSION['message'] = "No payment reference provided.";
    redirect('add_funds.php');
}

// Verify payment with Paystack
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($err) {
    error_log("Paystack curl error: " . $err);
    $_SESSION['message'] = "Payment verification failed: Network error (" . $err . ")";
    redirect('add_funds.php');
}

if ($http_code !== 200) {
    error_log("Paystack HTTP error: " . $http_code . " Response: " . $response);
    $_SESSION['message'] = "Payment verification failed: Server error (HTTP " . $http_code . ")";
    redirect('add_funds.php');
}

$result = json_decode($response, true);

if (!$result) {
    $_SESSION['message'] = "Invalid payment response.";
    redirect('add_funds.php');
}

if (!isset($result['status']) || $result['status'] !== true) {
    $_SESSION['message'] = "Payment verification failed. Please try again.";
    redirect('add_funds.php');
}

if (!isset($result['data']['status']) || $result['data']['status'] !== 'success') {
    $_SESSION['message'] = "Payment not completed. Status: " . ($result['data']['status'] ?? 'unknown');
    redirect('add_funds.php');
}

// Payment successful - add funds to wallet
$user_id = $_SESSION['user_id'];
$amount = $result['data']['amount'] / 100; // Convert from kobo to GHS

error_log("verify_wallet_payment.php - Payment verified. User: " . $user_id . ", Amount: " . $amount . ", Ref: " . $reference);

// Start transaction
$pdo->beginTransaction();

try {
    // Update user balance
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$amount, $user_id]);

    // Record wallet transaction
    $transaction_id = 'WALLET_' . time() . '_' . rand(1000, 9999);
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, product_id, amount, recipient_number, transaction_id, status, provider_reference, created_at) 
                          VALUES (?, ?, ?, ?, ?, 'successful', ?, NOW())");
    $stmt->execute([
        $user_id,
        null,  // No product for wallet topup
        $amount,
        null,  // No recipient
        $transaction_id,
        $reference
    ]);

    // Commit transaction
    $pdo->commit();

    // Update session with new balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
    if ($user_data) {
        $_SESSION['user_balance'] = $user_data['balance'];
    }

    // Clear session data
    unset($_SESSION['current_order']);
    unset($_SESSION['wallet_reference']);
    unset($_SESSION['wallet_amount']);

    $_SESSION['message'] = "Wallet topped up successfully! GHS " . number_format($amount, 2) . " has been added to your account.";
    redirect('dashboard.php');

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Wallet payment error: " . $e->getMessage());
    $_SESSION['message'] = "Failed to process wallet topup. Please contact support.";
    redirect('add_funds.php');
}
?>
