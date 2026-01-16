<?php
// order_details.php
$page_title = "Order Details";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to view orders.";
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    $_SESSION['message'] = "Invalid order ID.";
    redirect('dashboard.php');
}

// Get order details
$stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.network, u.username 
                      FROM orders o 
                      JOIN products p ON o.product_id = p.id 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['message'] = "Order not found.";
    redirect('dashboard.php');
}

// Get related transaction if exists
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND product_id = ? AND DATE(created_at) = DATE(?) LIMIT 1");
$stmt->execute([$user_id, $order['product_id'], $order['created_at']]);
$transaction = $stmt->fetch();
?>

<div class="content">
    <div class="order-details-container">
        <div class="order-header">
            <h1>Order Details</h1>
            <div class="order-status">
                <span class="status-badge status-<?php echo $order['status']; ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
        </div>

        <div class="order-grid">
            <!-- Main Order Information -->
            <div class="order-card">
                <h3>Order Information</h3>
                <div class="info-row">
                    <span class="label">Order ID:</span>
                    <span class="value">#<?php echo $order['id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('F d, Y H:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?php echo ucfirst($order['payment_method']); ?></span>
                </div>
            </div>

            <!-- Product Information -->
            <div class="order-card">
                <h3>Product Details</h3>
                <div class="info-row">
                    <span class="label">Product:</span>
                    <span class="value"><?php echo htmlspecialchars($order['product_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Network:</span>
                    <span class="value"><?php echo $order['network']; ?></span>
                </div>
                <?php if ($order['category']): ?>
                <div class="info-row">
                    <span class="label">Category:</span>
                    <span class="value"><?php echo ucfirst($order['category']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['data_amount']): ?>
                <div class="info-row">
                    <span class="label">Data Amount:</span>
                    <span class="value"><?php echo $order['data_amount']; ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['exam_type']): ?>
                <div class="info-row">
                    <span class="label">Exam Type:</span>
                    <span class="value"><?php echo htmlspecialchars($order['exam_type']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recipient Information -->
            <div class="order-card">
                <h3>Recipient Information</h3>
                <div class="info-row">
                    <span class="label">Phone Number:</span>
                    <span class="value"><?php echo htmlspecialchars($order['recipient_number']); ?></span>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="order-card">
                <h3>Payment Summary</h3>
                <div class="info-row">
                    <span class="label">Unit Price:</span>
                    <span class="value"><?php echo formatCurrency($order['unit_price']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Quantity:</span>
                    <span class="value"><?php echo $order['quantity']; ?></span>
                </div>
                <div class="info-row total">
                    <span class="label">Total Amount:</span>
                    <span class="value"><?php echo formatCurrency($order['total_amount']); ?></span>
                </div>
            </div>

            <!-- Transaction Information (if exists) -->
            <?php if ($transaction): ?>
            <div class="order-card">
                <h3>Transaction Information</h3>
                <div class="info-row">
                    <span class="label">Transaction ID:</span>
                    <span class="value"><?php echo htmlspecialchars($transaction['transaction_id']); ?></span>
                </div>
                <?php if ($transaction['provider_reference']): ?>
                <div class="info-row">
                    <span class="label">Provider Reference:</span>
                    <span class="value"><?php echo htmlspecialchars($transaction['provider_reference']); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="label">Transaction Status:</span>
                    <span class="value"><?php echo ucfirst($transaction['status']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Transaction Date:</span>
                    <span class="value"><?php echo date('F d, Y H:i A', strtotime($transaction['created_at'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="order-actions">
            <a href="javascript:window.print()" class="btn-primary">
                <i class='bx bx-printer'></i> Print Receipt
            </a>
            <a href="dashboard.php" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.order-details-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.order-header h1 {
    margin: 0;
}

.order-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.order-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-card h3 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    color: #2b2d42;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f5f5f5;
}

.info-row.total {
    font-weight: bold;
    font-size: 18px;
    border-bottom: 2px solid #2b2d42;
    padding: 10px 0;
}

.info-row .label {
    color: #666;
    font-weight: 500;
}

.info-row .value {
    text-align: right;
    font-weight: 500;
    color: #333;
}

.order-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.order-actions a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-grid {
        grid-template-columns: 1fr;
    }

    .info-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-row .value {
        text-align: left;
        margin-top: 5px;
    }

    .order-actions {
        flex-direction: column;
    }

    .order-actions a {
        width: 100%;
        justify-content: center;
    }
}

@media print {
    .order-actions {
        display: none;
    }

    .header, .footer {
        display: none;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
