<?php
// all_transactions.php
$page_title = "All Transactions";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to view transactions.";
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$search = sanitizeInput($_GET['search'] ?? '');
$filter_status = sanitizeInput($_GET['status'] ?? '');
$sort_by = sanitizeInput($_GET['sort'] ?? 'created_at');
$sort_order = sanitizeInput($_GET['order'] ?? 'DESC');

// Validate sort parameters
$valid_sorts = ['created_at', 'amount', 'status'];
$valid_orders = ['ASC', 'DESC'];
if (!in_array($sort_by, $valid_sorts)) $sort_by = 'created_at';
if (!in_array($sort_order, $valid_orders)) $sort_order = 'DESC';

// Build query
$query = "SELECT t.*, p.name as product_name, p.network, o.id as order_id 
          FROM transactions t 
          LEFT JOIN products p ON t.product_id = p.id 
          LEFT JOIN orders o ON o.user_id = t.user_id AND o.product_id = t.product_id AND DATE(o.created_at) = DATE(t.created_at)
          WHERE t.user_id = ?";

$params = [$user_id];

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.network LIKE ? OR t.reference LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term);
}

if (!empty($filter_status)) {
    $query .= " AND t.status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY t.$sort_by $sort_order";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'successful' THEN amount ELSE 0 END) as total_spent
                FROM transactions 
                WHERE user_id = ?";
$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([$user_id]);
$stats = $stats_stmt->fetch();
?>

<div class="content">
    <div class="transactions-page-header">
        <h1>All Transactions</h1>
        <p class="subtitle">View and manage all your transactions</p>
    </div>

    <!-- Statistics -->
    <div class="transactions-stats">
        <div class="stat-card">
            <div class="stat-icon total-icon">
                <i class='bx bx-list-check'></i>
            </div>
            <div class="stat-info">
                <h3>Total Transactions</h3>
                <p class="stat-value"><?php echo $stats['total'] ?? 0; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon successful-icon">
                <i class='bx bx-check-circle'></i>
            </div>
            <div class="stat-info">
                <h3>Successful</h3>
                <p class="stat-value"><?php echo $stats['successful'] ?? 0; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending-icon">
                <i class='bx bx-time-five'></i>
            </div>
            <div class="stat-info">
                <h3>Pending</h3>
                <p class="stat-value"><?php echo $stats['pending'] ?? 0; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon failed-icon">
                <i class='bx bx-x-circle'></i>
            </div>
            <div class="stat-info">
                <h3>Failed</h3>
                <p class="stat-value"><?php echo $stats['failed'] ?? 0; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amount-icon">
                <i class='bx bx-wallet'></i>
            </div>
            <div class="stat-info">
                <h3>Total Spent</h3>
                <p class="stat-value"><?php echo formatCurrency($stats['total_spent'] ?? 0); ?></p>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="transactions-filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search by product, network, or reference..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-group">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="successful" <?php echo $filter_status === 'successful' ? 'selected' : ''; ?>>Successful</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $filter_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>

            <div class="filter-group">
                <select name="sort">
                    <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date (Newest First)</option>
                    <option value="amount" <?php echo $sort_by === 'amount' ? 'selected' : ''; ?>>Amount (High to Low)</option>
                    <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class='bx bx-filter'></i> Filter
            </button>

            <?php if (!empty($search) || !empty($filter_status)): ?>
                <a href="all_transactions.php" class="btn btn-secondary">
                    <i class='bx bx-x'></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="transactions-container">
        <?php if (!empty($transactions)): ?>
            <div class="table-responsive">
                <table class="transactions-list">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Network</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="transaction-row">
                                <td class="date-col">
                                    <span><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></span>
                                    <small><?php echo date('H:i', strtotime($transaction['created_at'])); ?></small>
                                </td>
                                <td class="product-col">
                                    <strong><?php echo htmlspecialchars($transaction['product_name'] ?? 'Wallet Top-up'); ?></strong>
                                </td>
                                <td class="network-col">
                                    <?php echo htmlspecialchars($transaction['network'] ?? '—'); ?>
                                </td>
                                <td class="amount-col">
                                    <strong><?php echo formatCurrency($transaction['amount']); ?></strong>
                                </td>
                                <td class="status-col">
                                    <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                        <i class='bx <?php echo $transaction['status'] === 'successful' ? 'bx-check-circle' : ($transaction['status'] === 'pending' ? 'bx-time-five' : 'bx-x-circle'); ?>'></i>
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                                <td class="reference-col">
                                    <code><?php echo htmlspecialchars($transaction['reference'] ?? '—'); ?></code>
                                </td>
                                <td class="order-col">
                                    <?php if (!empty($transaction['order_id'])): ?>
                                        <a href="order_details.php?id=<?php echo $transaction['order_id']; ?>" class="order-link">
                                            #<?php echo $transaction['order_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span>—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class='bx bx-inbox'></i>
                <h3>No transactions found</h3>
                <p>Start by purchasing data or airtime from our products page</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
