<?php
// admin/transactions.php
$page_title = "Transactions";
require_once 'includes/admin_header.php';

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$network_filter = isset($_GET['network']) ? sanitize($_GET['network']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT t.*, u.username, p.name as product_name, p.network 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        JOIN products p ON t.product_id = p.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (t.transaction_id LIKE ? OR u.username LIKE ? OR t.recipient_number LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $sql .= " AND t.status = ?";
    $params[] = $status_filter;
}

if (!empty($network_filter)) {
    $sql .= " AND p.network = ?";
    $params[] = $network_filter;
}

if (!empty($date_from)) {
    $sql .= " AND DATE(t.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(t.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY t.created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);

// Get transactions
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM transactions t 
              JOIN users u ON t.user_id = u.id 
              JOIN products p ON t.product_id = p.id 
              WHERE 1=1";
$count_params = [];

if (!empty($search)) {
    $count_sql .= " AND (t.transaction_id LIKE ? OR u.username LIKE ? OR t.recipient_number LIKE ?)";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

if (!empty($status_filter)) {
    $count_sql .= " AND t.status = ?";
    $count_params[] = $status_filter;
}

if (!empty($network_filter)) {
    $count_sql .= " AND p.network = ?";
    $count_params[] = $network_filter;
}

if (!empty($date_from)) {
    $count_sql .= " AND DATE(t.created_at) >= ?";
    $count_params[] = $date_from;
}

if (!empty($date_to)) {
    $count_sql .= " AND DATE(t.created_at) <= ?";
    $count_params[] = $date_to;
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_transactions = $count_stmt->fetch()['total'];
$total_pages = ceil($total_transactions / $limit);

// Get distinct networks for filter
$networks_stmt = $pdo->query("SELECT DISTINCT network FROM products ORDER BY network");
$networks = $networks_stmt->fetchAll();
?>

<div class="admin-content">
    <h1>Transactions</h1>

    <!-- Filters -->
    <div class="filters-box">
        <form method="GET" action="">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search transactions..." value="<?php echo htmlspecialchars($search); ?>">

                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="successful" <?php echo ($status_filter == 'successful') ? 'selected' : ''; ?>>Successful</option>
                        <option value="failed" <?php echo ($status_filter == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo ($status_filter == 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                    </select>

                    <select name="network">
                        <option value="">All Networks</option>
                        <?php foreach ($networks as $network): ?>
                            <option value="<?php echo htmlspecialchars($network['network']); ?>" <?php echo ($network_filter == $network['network']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($network['network']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                    <input type="date" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">

                    <button type="submit" class="btn-admin btn-primary">
                        <i class='bx bx-filter'></i> Filter
                    </button>

                    <?php if (!empty($search) || !empty($status_filter) || !empty($network_filter) || !empty($date_from) || !empty($date_to)): ?>
                        <a href="transactions.php" class="btn-admin btn-secondary">
                            <i class='bx bx-x'></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="summary-stats">
        <?php
        // Get today's transactions
        $today = date('Y-m-d');
        $today_stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as revenue 
                                    FROM transactions 
                                    WHERE DATE(created_at) = ? AND status = 'successful'");
        $today_stmt->execute([$today]);
        $today_stats = $today_stmt->fetch();

        // Get total successful transactions
        $total_stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as revenue 
                                  FROM transactions WHERE status = 'successful'");
        $total_stats = $total_stmt->fetch();
        ?>

        <div class="stat-card mini">
            <div class="stat-info">
                <h3>Today's Transactions</h3>
                <p class="stat-value"><?php echo $today_stats['count']; ?></p>
                <p class="stat-amount">GHS <?php echo number_format($today_stats['revenue'], 2); ?></p>
            </div>
        </div>

        <div class="stat-card mini">
            <div class="stat-info">
                <h3>Total Transactions</h3>
                <p class="stat-value"><?php echo $total_stats['count']; ?></p>
                <p class="stat-amount">GHS <?php echo number_format($total_stats['revenue'], 2); ?></p>
            </div>
        </div>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Network</th>
                    <th>Amount</th>
                    <th>Recipient</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No transactions found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($transaction['transaction_id']); ?><br>
                                <small><?php echo htmlspecialchars($transaction['provider_reference'] ?: 'N/A'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['network']); ?></td>
                            <td>GHS <?php echo number_format($transaction['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['recipient_number'] ?: 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&network=<?php echo urlencode($network_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                        class="btn-admin btn-secondary">
                        <i class='bx bx-chevron-left'></i> Previous
                    </a>
                <?php endif; ?>

                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&network=<?php echo urlencode($network_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                        class="btn-admin btn-secondary">
                        Next <i class='bx bx-chevron-right'></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card.mini {
        padding: 15px;
        text-align: center;
    }

    .stat-card.mini .stat-value {
        font-size: 24px;
        margin: 5px 0;
    }

    .stat-card.mini .stat-amount {
        font-size: 16px;
        color: #2ecc71;
        font-weight: bold;
    }

    .filters-box {
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .filter-row {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .filter-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-group input,
    .filter-group select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        min-width: 150px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        padding: 10px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add date validation
        const dateFrom = document.querySelector('input[name="date_from"]');
        const dateTo = document.querySelector('input[name="date_to"]');

        if (dateFrom && dateTo) {
            dateFrom.addEventListener('change', function() {
                dateTo.min = this.value;
            });

            dateTo.addEventListener('change', function() {
                dateFrom.max = this.value;
            });
        }
    });
</script>

<?php
require_once 'includes/admin_footer.php';
?>