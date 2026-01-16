<?php
// admin/order_details.php
$page_title = "Order Details";
require_once 'includes/admin_header.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    $_SESSION['message'] = "Invalid order ID.";
    redirect('orders.php');
}

// Get order details
$stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.network, u.username, u.email, u.phone 
                      FROM orders o 
                      JOIN products p ON o.product_id = p.id 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['message'] = "Order not found.";
    redirect('orders.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $_SESSION['message'] = "Order status updated successfully!";
        // Refresh order data
        $stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.network, u.username, u.email, u.phone 
                              FROM orders o 
                              JOIN products p ON o.product_id = p.id 
                              JOIN users u ON o.user_id = u.id 
                              WHERE o.id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
    } else {
        $_SESSION['message'] = "Failed to update order status.";
    }
}

// Get related transaction
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND product_id = ? AND DATE(created_at) = DATE(?) LIMIT 1");
$stmt->execute([$order['user_id'], $order['product_id'], $order['created_at']]);
$transaction = $stmt->fetch();
?>

<div class="admin-content">
    <div class="order-details-header">
        <h1>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
        <a href="orders.php" class="btn-admin btn-secondary">
            <i class='bx bx-arrow-back'></i> Back to Orders
        </a>
    </div>

    <div class="order-details-grid">
        <!-- Order Information -->
        <div class="admin-card">
            <h3>Order Information</h3>
            <div class="info-row">
                <span class="label">Order ID:</span>
                <span class="value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Order Date:</span>
                <span class="value"><?php echo date('F d, Y H:i A', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value">
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Payment Method:</span>
                <span class="value"><?php echo ucfirst($order['payment_method']); ?></span>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="admin-card">
            <h3>Customer Information</h3>
            <div class="info-row">
                <span class="label">Customer:</span>
                <span class="value"><?php echo htmlspecialchars($order['username']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                <span class="value"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Recipient Phone:</span>
                <span class="value"><?php echo htmlspecialchars($order['recipient_number']); ?></span>
            </div>
        </div>

        <!-- Product Information -->
        <div class="admin-card">
            <h3>Product Details</h3>
            <div class="info-row">
                <span class="label">Product:</span>
                <span class="value"><?php echo htmlspecialchars($order['product_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Network:</span>
                <span class="value"><?php echo $order['network']; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Category:</span>
                <span class="value"><?php echo ucfirst($order['category']); ?></span>
            </div>
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

        <!-- Payment Summary -->
        <div class="admin-card">
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

        <!-- Status Update Form -->
        <div class="admin-card">
            <h3>Update Status</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_status">Change Status:</label>
                    <select id="new_status" name="new_status" required>
                        <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn-admin btn-primary">Update Status</button>
            </form>
        </div>

        <!-- Transaction Information (if exists) -->
        <?php if ($transaction): ?>
        <div class="admin-card">
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
</div>

<style>
.order-details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.order-details-header h1 {
    margin: 0;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.admin-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.admin-card h3 {
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
    font-size: 16px;
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

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .order-details-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-details-grid {
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
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>
