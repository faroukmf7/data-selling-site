<?php
// admin/orders.php
$page_title = "Manage Orders";
require_once 'includes/admin_header.php';

// Handle order actions
if (isset($_GET['action'])) {
    $order_id = (int)$_GET['id'];
    $action = $_GET['action'];

    switch ($action) {
        case 'update_status':
            if (isset($_GET['status'])) {
                $status = sanitize($_GET['status']);
                $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];

                if (in_array($status, $valid_statuses)) {
                    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    if ($stmt->execute([$status, $order_id])) {
                        $_SESSION['message'] = "Order status updated to " . ucfirst($status);
                    }
                }
            }
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            if ($stmt->execute([$order_id])) {
                $_SESSION['message'] = "Order deleted successfully.";
            }
            break;
    }

    redirect('orders.php');
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT o.*, u.username, u.email FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY o.created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);

// Get orders
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE 1=1";
$count_params = [];

if (!empty($search)) {
    $count_sql .= " AND (o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

if (!empty($status_filter)) {
    $count_sql .= " AND o.status = ?";
    $count_params[] = $status_filter;
}

if (!empty($date_from)) {
    $count_sql .= " AND DATE(o.created_at) >= ?";
    $count_params[] = $date_from;
}

if (!empty($date_to)) {
    $count_sql .= " AND DATE(o.created_at) <= ?";
    $count_params[] = $date_to;
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_orders = $count_stmt->fetch()['total'];
$total_pages = ceil($total_orders / $limit);
?>

<div class="admin-content">
    <h1>Manage Orders</h1>

    <!-- Filters -->
    <div class="filters-box">
        <form method="GET" action="">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search orders or users..." value="<?php echo htmlspecialchars($search); ?>">

                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo ($status_filter == 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>

                    <input type="date" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                    <input type="date" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">

                    <button type="submit" class="btn-admin btn-primary">
                        <i class='bx bx-filter'></i> Filter
                    </button>

                    <?php if (!empty($search) || !empty($status_filter) || !empty($date_from) || !empty($date_to)): ?>
                        <a href="orders.php" class="btn-admin btn-secondary">
                            <i class='bx bx-x'></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="admin-table-container">
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
                            <td>
                                <?php echo htmlspecialchars($order['username']); ?><br>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td>GHS <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="actions">
                                <!-- View Details -->
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-admin btn-secondary btn-small">
                                    <i class='bx bx-show'></i> View
                                </a>

                                <!-- Status Dropdown -->
                                <div class="dropdown">
                                    <button class="btn-admin btn-primary btn-small dropdown-toggle">
                                        <i class='bx bx-edit'></i> Status
                                    </button>
                                    <div class="dropdown-content">
                                        <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=pending">Pending</a>
                                        <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=processing">Processing</a>
                                        <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=completed">Completed</a>
                                        <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=cancelled">Cancelled</a>
                                    </div>
                                </div>

                                <!-- Delete Button -->
                                <a href="?action=delete&id=<?php echo $order['id']; ?>"
                                    class="btn-admin btn-danger btn-small"
                                    onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                                    <i class='bx bx-trash'></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                        class="btn-admin btn-secondary">
                        <i class='bx bx-chevron-left'></i> Previous
                    </a>
                <?php endif; ?>

                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                        class="btn-admin btn-secondary">
                        Next <i class='bx bx-chevron-right'></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
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

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-toggle {
        cursor: pointer;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        z-index: 1;
        border-radius: 5px;
        overflow: hidden;
    }

    .dropdown-content a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        border-bottom: 1px solid #eee;
    }

    .dropdown-content a:hover {
        background-color: #f5f5f5;
    }

    .dropdown:hover .dropdown-content {
        display: block;
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