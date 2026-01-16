<?php
// process_wallet_payment.php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['current_order'])) {
    $_SESSION['message'] = "No order to process.";
    redirect('products.php');
}

$order = $_SESSION['current_order'];
$user_id = $_SESSION['user_id'];

// Start transaction
$pdo->beginTransaction();

try {
    // Check user balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user['balance'] < $order['total_amount']) {
        throw new Exception("Insufficient wallet balance.");
    }

    // Create transaction record
    $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, product_id, amount, recipient_number, transaction_id, status) 
                          VALUES (?, ?, ?, ?, ?, 'successful')");
    $stmt->execute([
        $user_id,
        $order['product_id'],
        $order['total_amount'],
        $order['recipient_number'],
        $transaction_id
    ]);

    // Update user balance
    $new_balance = $user['balance'] - $order['total_amount'];
    $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->execute([$new_balance, $user_id]);

    // Update session balance
    $_SESSION['user_balance'] = $new_balance;

    // Create order record
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'completed')");
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
    $_SESSION['message'] = "Payment failed: " . $e->getMessage();
    redirect('checkout.php');
}
