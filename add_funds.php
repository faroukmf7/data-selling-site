<?php
// add_funds.php
$page_title = "Add Funds";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to add funds.";
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (float)$_POST['amount'];

    if ($amount <= 0) {
        $error = "Amount must be greater than 0.";
    } elseif ($amount > 10000) {
        $error = "Maximum amount is GHS 10,000 per transaction.";
    } else {
        // Create a wallet topup order
        $_SESSION['current_order'] = [
            'product_id' => 0,  // 0 means wallet topup
            'product_name' => 'Wallet Top-up',
            'total_amount' => $amount,
            'recipient_number' => $user['phone'],
            'data_amount' => null,
            'exam_type' => null,
            'network' => null,
            'category' => 'wallet',
        ];

        // Generate reference for wallet topup
        $reference = 'WALLET_' . time() . '_' . $user_id . '_' . rand(1000, 9999);
        $_SESSION['wallet_reference'] = $reference;
        $_SESSION['wallet_amount'] = $amount * 100; // Convert to kobo for Paystack

        // Redirect to Paystack payment page
        redirect('wallet_payment.php');
    }
}
?>

<div class="content">
    <div class="add-funds-container">
        <div class="funds-card">
            <h1>Add Funds to Wallet</h1>

            <div class="current-balance">
                <h3>Current Balance</h3>
                <div class="balance-display"><?php echo formatCurrency($user['balance']); ?></div>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="amount">Amount to Add (GHS)</label>
                    <div class="amount-input-wrapper">
                        <input type="number" id="amount" name="amount" step="0.01" min="1" max="10000" required placeholder="Enter amount" autofocus>
                        <small>Min: GHS 1 | Max: GHS 10,000</small>
                    </div>
                </div>

                <!-- Quick amount buttons -->
                <div class="quick-amounts">
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 50">GHS 50</button>
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 100">GHS 100</button>
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 200">GHS 200</button>
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 500">GHS 500</button>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px;">
                    <i class='bx bx-credit-card'></i> Add Funds with Paystack
                </button>
            </form>

            <div class="funds-info">
                <h4>Payment Information</h4>
                <ul>
                    <li>Funds will be added immediately after successful payment</li>
                    <li>No additional fees will be charged</li>
                    <li>You can use your balance to purchase data/airtime instantly</li>
                    <li>All transactions are secure and encrypted</li>
                </ul>
            </div>

            <div class="funds-actions">
                <a href="dashboard.php" class="btn-secondary">
                    <i class='bx bx-arrow-back'></i> Back to Dashboard
                </a>
                <a href="products.php" class="btn-secondary">
                    <i class='bx bx-shopping-bag'></i> View Products
                </a>
            </div>
        </div>

        <!-- Recent Wallet Transactions -->
        <div class="funds-transactions">
            <h3>Recent Transactions</h3>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND product_id IS NULL ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$user_id]);
            $transactions = $stmt->fetchAll();

            if (empty($transactions)):
            ?>
                <p class="no-data">No wallet transactions yet.</p>
            <?php else: ?>
                <table class="transactions-list">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn): ?>
                        <tr>
                            <td><?php echo date('M d, H:i', strtotime($txn['created_at'])); ?></td>
                            <td><?php echo formatCurrency($txn['amount']); ?></td>
                            <td><span class="status-badge status-<?php echo $txn['status']; ?>"><?php echo ucfirst($txn['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.add-funds-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.funds-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.funds-card h1 {
    margin-top: 0;
    margin-bottom: 30px;
    text-align: center;
    color: #2b2d42;
}

.current-balance {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.current-balance h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.balance-display {
    font-size: 32px;
    font-weight: bold;
}

.amount-input-wrapper {
    display: flex;
    flex-direction: column;
}

.amount-input-wrapper small {
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.quick-amounts {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin: 20px 0;
}

.quick-btn {
    padding: 10px;
    border: 2px solid #ddd;
    background: white;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.quick-btn:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

.funds-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
}

.funds-info h4 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #2b2d42;
}

.funds-info ul {
    margin: 0;
    padding-left: 20px;
}

.funds-info li {
    margin: 8px 0;
    color: #555;
    font-size: 14px;
}

.funds-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.funds-actions a {
    flex: 1;
    text-align: center;
}

.funds-transactions {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.funds-transactions h3 {
    margin-top: 0;
    margin-bottom: 20px;
}

.transactions-list {
    width: 100%;
    border-collapse: collapse;
}

.transactions-list thead {
    background: #f8f9fa;
}

.transactions-list th {
    padding: 10px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #eee;
}

.transactions-list td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

@media (max-width: 768px) {
    .quick-amounts {
        grid-template-columns: repeat(2, 1fr);
    }

    .funds-actions {
        flex-direction: column;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
