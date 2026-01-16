<?php
// admin/index.php
$page_title = "Admin Dashboard";
require_once 'includes/admin_header.php';
?>

<div class="admin-content">
    <h1>Dashboard</h1>

    <!-- Quick Stats -->
    <div class="admin-stats">
        <?php
        // Get total users
        $users_stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $users_count = $users_stmt->fetch()['count'];

        // Get total products
        $products_stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
        $products_count = $products_stmt->fetch()['count'];

        // Get total orders today
        $today = date('Y-m-d');
        $orders_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = ?");
        $orders_stmt->execute([$today]);
        $orders_today = $orders_stmt->fetch()['count'];

        // Get total revenue today
        $revenue_stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) = ? AND status = 'completed'");
        $revenue_stmt->execute([$today]);
        $revenue_today = $revenue_stmt->fetch()['revenue'];
        ?>

        <div class="stat-card">
            <div class="stat-icon users">
                <i class='bx bxs-user'></i>
            </div>
            <div class="stat-info">
                <h3>Total Users</h3>
                <p class="stat-value"><?php echo $users_count; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon products">
                <i class='bx bxs-package'></i>
            </div>
            <div class="stat-info">
                <h3>Active Products</h3>
                <p class="stat-value"><?php echo $products_count; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orders">
                <i class='bx bxs-cart'></i>
            </div>
            <div class="stat-info">
                <h3>Orders Today</h3>
                <p class="stat-value"><?php echo $orders_today; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class='bx bxs-wallet'></i>
            </div>
            <div class="stat-info">
                <h3>Revenue Today</h3>
                <p class="stat-value"><?php echo formatCurrency($revenue_today); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="add_product.php" class="btn-admin btn-primary">
            <i class='bx bx-plus'></i> Add New Product
        </a>
        <a href="products.php" class="btn-admin btn-secondary">
            <i class='bx bxs-package'></i> Manage Products
        </a>
        <a href="users.php" class="btn-admin btn-secondary">
            <i class='bx bxs-user'></i> Manage Users
        </a>
    </div>

    <!-- Recent Orders -->
    <div class="admin-table-container">
        <h2>Recent Orders</h2>
        <?php
        $stmt = $pdo->query("SELECT o.*, u.username FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            ORDER BY o.created_at DESC LIMIT 10");
        $orders = $stmt->fetchAll();
        ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No orders found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo $order['username']; ?></td>
                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="actions">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-admin btn-secondary btn-small">
                                    <i class='bx bx-show'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Transactions -->
    <div class="admin-table-container">
        <h2>Recent Transactions</h2>
        <?php
        $stmt = $pdo->query("SELECT t.*, u.username, p.name as product_name 
                            FROM transactions t 
                            JOIN users u ON t.user_id = u.id 
                            JOIN products p ON t.product_id = p.id 
                            ORDER BY t.created_at DESC LIMIT 10");
        $transactions = $stmt->fetchAll();
        ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No transactions found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo $transaction['transaction_id']; ?></td>
                            <td><?php echo $transaction['username']; ?></td>
                            <td><?php echo $transaction['product_name']; ?></td>
                            <td><?php echo formatCurrency($transaction['amount']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, H:i', strtotime($transaction['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'includes/admin_footer.php';
?>