<?php
// admin/users.php
$page_title = "Manage Users";
require_once 'includes/admin_header.php';

// Handle user actions
if (isset($_GET['action'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'];

    switch ($action) {
        case 'delete':
            // Don't delete self
            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['message'] = "You cannot delete your own account.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $_SESSION['message'] = "User deleted successfully.";
                } else {
                    $_SESSION['message'] = "Failed to delete user.";
                }
            }
            break;

        case 'toggle_admin':
            // Don't remove admin from self
            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['message'] = "You cannot remove admin privileges from yourself.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $_SESSION['message'] = "User admin status updated.";
                } else {
                    $_SESSION['message'] = "Failed to update user admin status.";
                }
            }
            break;

        case 'add_funds':
            if (isset($_GET['amount'])) {
                $amount = (float)$_GET['amount'];
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                if ($stmt->execute([$amount, $user_id])) {
                    $_SESSION['message'] = "GHS " . number_format($amount, 2) . " added to user's balance.";
                }
            }
            break;
    }

    redirect('users.php');
}

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);

// Get users
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM users";
if (!empty($search)) {
    $count_sql .= " WHERE (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
}
$count_stmt = $pdo->prepare($count_sql);
if (!empty($search)) {
    $count_stmt->execute([$search_term, $search_term, $search_term]);
} else {
    $count_stmt->execute();
}
$total_users = $count_stmt->fetch()['total'];
$total_pages = ceil($total_users / $limit);
?>

<div class="admin-content">
    <h1>Manage Users</h1>

    <!-- Search Form -->
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-admin btn-primary">
                <i class='bx bx-search'></i> Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="users.php" class="btn-admin btn-secondary">
                    <i class='bx bx-x'></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Admin</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No users found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                            <td>GHS <?php echo number_format($user['balance'], 2); ?></td>
                            <td>
                                <?php if ($user['is_admin']): ?>
                                    <span class="status-badge status-active">Admin</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">User</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="actions">
                                <!-- Add Funds Button -->
                                <button type="button" class="btn-admin btn-success btn-small"
                                    onclick="showAddFundsModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    <i class='bx bx-money'></i> Add Funds
                                </button>

                                <!-- Toggle Admin Button -->
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?action=toggle_admin&id=<?php echo $user['id']; ?>"
                                        class="btn-admin <?php echo $user['is_admin'] ? 'btn-warning' : 'btn-secondary'; ?> btn-small">
                                        <i class='bx bx-<?php echo $user['is_admin'] ? 'user-x' : 'user-check'; ?>'></i>
                                        <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                    </a>
                                <?php endif; ?>

                                <!-- Delete Button -->
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?action=delete&id=<?php echo $user['id']; ?>"
                                        class="btn-admin btn-danger btn-small"
                                        onclick="return confirm('Are you sure you want to delete this user? This will also delete all their orders and transactions.')">
                                        <i class='bx bx-trash'></i> Delete
                                    </a>
                                <?php endif; ?>
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
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn-admin btn-secondary">
                        <i class='bx bx-chevron-left'></i> Previous
                    </a>
                <?php endif; ?>

                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn-admin btn-secondary">
                        Next <i class='bx bx-chevron-right'></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Funds Modal -->
<div id="addFundsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Funds to User</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Adding funds to: <strong id="userName"></strong></p>
            <form id="addFundsForm" method="GET" action="">
                <input type="hidden" name="action" value="add_funds">
                <input type="hidden" id="userId" name="id" value="">

                <div class="form-group">
                    <label for="amount">Amount (GHS)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-admin btn-primary">
                        <i class='bx bx-money'></i> Add Funds
                    </button>
                    <button type="button" class="btn-admin btn-secondary" onclick="closeModal()">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showAddFundsModal(userId, userName) {
        document.getElementById('userId').value = userId;
        document.getElementById('userName').textContent = userName;
        document.getElementById('addFundsModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('addFundsModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('addFundsModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // Form validation
    document.getElementById('addFundsForm').addEventListener('submit', function(e) {
        const amount = document.getElementById('amount').value;
        if (parseFloat(amount) <= 0) {
            e.preventDefault();
            alert('Amount must be greater than 0.');
            return false;
        }
        return true;
    });
</script>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .close-modal {
        font-size: 28px;
        cursor: pointer;
        color: #666;
    }

    .close-modal:hover {
        color: #000;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        padding: 10px;
    }

    .search-box {
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .search-box form {
        display: flex;
        gap: 10px;
    }

    .search-box input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>

<?php
require_once 'includes/admin_footer.php';
?>