<?php
// dashboard.php
$page_title = "Dashboard";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to access dashboard.";
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get recent transactions
$stmt = $pdo->prepare("SELECT t.*, p.name as product_name, p.network, o.id as order_id
                      FROM transactions t 
                      LEFT JOIN products p ON t.product_id = p.id 
                      LEFT JOIN orders o ON o.user_id = t.user_id AND o.product_id = t.product_id AND DATE(o.created_at) = DATE(t.created_at)
                      WHERE t.user_id = ? 
                      ORDER BY t.created_at DESC 
                      LIMIT 10");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>

<div class="content">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo $user['username']; ?>!</h1>
        <div class="user-balance">
            Account Balance: <span class="balance-amount"><?php echo formatCurrency($user['balance']); ?></span>
            <a href="add_funds.php" class="btn-small">Add Funds</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Quick Stats -->
        <div class="dashboard-card">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="products.php" class="btn-action">
                    <i class='bx bx-wifi'></i>
                    <span>Buy Data</span>
                </a>
                <a href="products.php" class="btn-action">
                    <i class='bx bx-phone'></i>
                    <span>Buy Airtime</span>
                </a>
                <a href="add_funds.php" class="btn-action">
                    <i class='bx bx-credit-card'></i>
                    <span>Add Funds</span>
                </a>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="dashboard-card">
            <h3>Recent Transactions</h3>
            <?php if (empty($transactions)): ?>
                <p class="no-data">No transactions yet.</p>
            <?php else: ?>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr onclick="<?php if ($transaction['order_id']) { ?>window.location='order_details.php?id=<?php echo $transaction['order_id']; ?>';<?php } ?>" style="<?php if ($transaction['order_id']) { ?>cursor: pointer;<?php } ?>">
                                <td><?php echo date('M d, H:i', strtotime($transaction['created_at'])); ?></td>
                                <td>
                                    <?php echo $transaction['product_name'] ?? 'Wallet Top-up'; ?><br>
                                    <small><?php echo $transaction['network'] ?? 'N/A'; ?></small>
                                </td>
                                <td><?php echo formatCurrency($transaction['amount']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="dashboard.php" class="view-all" style="display: block; text-align: center; margin-top: 15px;">All transactions shown above</a>
            <?php endif; ?>
        </div>

        <!-- Account Info -->
        <div class="dashboard-card">
            <h3>Account Information</h3>
            <div class="account-info">
                <div class="info-row">
                    <span class="label">Username:</span>
                    <span class="value"><?php echo $user['username']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo $user['email']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Phone:</span>
                    <span class="value"><?php echo $user['phone']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Member Since:</span>
                    <span class="value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
            <div class="account-actions">
                <a href="edit_profile.php" class="btn-small">Edit Profile</a>
                <a href="change_password.php" class="btn-small">Change Password</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>