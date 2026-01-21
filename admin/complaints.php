<?php
// admin/complaints.php
$page_title = "Manage Complaints";
require_once 'includes/admin_header.php';
require_once '../includes/functions.php';

// Ensure complaints table exists
ensureComplaintsTableExists($pdo);

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['message'] = "Access denied. Admin privileges required.";
    redirect('../login.php');
}

// Handle status update via AJAX or form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $complaint_id = (int)$_POST['complaint_id'];
        $new_status = sanitizeInput($_POST['status']);
        $resolution_note = sanitizeInput($_POST['resolution_note'] ?? '');

        // Validate status
        $valid_statuses = ['pending', 'in_review', 'resolved', 'rejected'];
        if (in_array($new_status, $valid_statuses)) {
            $stmt = $pdo->prepare("UPDATE complaints SET status = ?, resolution_note = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$new_status, $resolution_note, $complaint_id])) {
                $_SESSION['message'] = "Complaint status updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update complaint status.";
            }
        }
    }
}

// Get filter parameters
$search = sanitizeInput($_GET['search'] ?? '');
$filter_status = sanitizeInput($_GET['status'] ?? '');
$filter_user = sanitizeInput($_GET['user'] ?? '');
$sort_by = sanitizeInput($_GET['sort'] ?? 'created_at');
$sort_order = sanitizeInput($_GET['order'] ?? 'DESC');

// Validate sort parameters
$valid_sorts = ['created_at', 'status', 'priority', 'user_id'];
$valid_orders = ['ASC', 'DESC'];
if (!in_array($sort_by, $valid_sorts)) $sort_by = 'created_at';
if (!in_array($sort_order, $valid_orders)) $sort_order = 'DESC';

// Build query
$query = "SELECT c.*, u.username, u.email, o.id as order_id, p.name as product_name,
          (SELECT COUNT(*) FROM complaints WHERE status = 'pending') as pending_count,
          (SELECT COUNT(*) FROM complaints WHERE status = 'in_review') as in_review_count,
          (SELECT COUNT(*) FROM complaints WHERE status = 'resolved') as resolved_count,
          (SELECT COUNT(*) FROM complaints WHERE status = 'rejected') as rejected_count
          FROM complaints c
          LEFT JOIN users u ON c.user_id = u.id
          LEFT JOIN orders o ON c.order_id = o.id
          LEFT JOIN products p ON o.product_id = p.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}

if (!empty($filter_status)) {
    $query .= " AND c.status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_user)) {
    $query .= " AND c.user_id = ?";
    $params[] = (int)$filter_user;
}

$query .= " ORDER BY c.$sort_by $sort_order";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'in_review' => 0,
    'resolved' => 0,
    'rejected' => 0
];

$stats_query = "SELECT status, COUNT(*) as count FROM complaints GROUP BY status";
$stats_stmt = $pdo->query($stats_query);
$status_counts = $stats_stmt->fetchAll();

foreach ($status_counts as $row) {
    $stats[$row['status']] = $row['count'];
    $stats['total'] += $row['count'];
}

// Get list of users for filter dropdown
$users_query = "SELECT DISTINCT u.id, u.username FROM complaints c 
                JOIN users u ON c.user_id = u.id 
                ORDER BY u.username";
$users_stmt = $pdo->query($users_query);
$users = $users_stmt->fetchAll();
?>

<div class="admin-content">
    <h1 class="admin-dashboard-header">Manage User Complaints</h1>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <i class='bx bx-check-circle'></i>
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class='bx bx-x-circle'></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class='bx bx-list-check'></i>
            </div>
            <div class="stat-info">
                <h3>Total Complaints</h3>
                <p class="stat-value"><?php echo $stats['total']; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending">
                <i class='bx bx-time-five'></i>
            </div>
            <div class="stat-info">
                <h3>Pending</h3>
                <p class="stat-value"><?php echo $stats['pending']; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon in-review">
                <i class='bx bx-search'></i>
            </div>
            <div class="stat-info">
                <h3>In Review</h3>
                <p class="stat-value"><?php echo $stats['in_review']; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon resolved">
                <i class='bx bx-check-double'></i>
            </div>
            <div class="stat-info">
                <h3>Resolved</h3>
                <p class="stat-value"><?php echo $stats['resolved']; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon rejected">
                <i class='bx bx-x-circle'></i>
            </div>
            <div class="stat-info">
                <h3>Rejected</h3>
                <p class="stat-value"><?php echo $stats['rejected']; ?></p>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="admin-filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search by title, username, or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-group">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_review" <?php echo $filter_status === 'in_review' ? 'selected' : ''; ?>>In Review</option>
                    <option value="resolved" <?php echo $filter_status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <div class="filter-group">
                <select name="user">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $filter_user == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="sort">
                    <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date (Newest First)</option>
                    <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                    <option value="priority" <?php echo $sort_by === 'priority' ? 'selected' : ''; ?>>Priority</option>
                </select>
            </div>

            <button type="submit" class="btn-admin btn-primary">
                <i class='bx bx-filter'></i> Filter
            </button>

            <?php if (!empty($search) || !empty($filter_status) || !empty($filter_user)): ?>
                <a href="complaints.php" class="btn-admin btn-secondary">
                    <i class='bx bx-x'></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Complaints Table -->
    <div class="admin-table-container">
        <div class="admin-table-responsive">
            <?php if (!empty($complaints)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td>#<?php echo $complaint['id']; ?></td>
                                <td>
                                    <div class="user-info">
                                        <strong><?php echo htmlspecialchars($complaint['username']); ?></strong>
                                        <small><?php echo htmlspecialchars($complaint['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="complaint-title">
                                        <strong><?php echo htmlspecialchars($complaint['title']); ?></strong>
                                        <small><?php echo substr(htmlspecialchars($complaint['description']), 0, 50) . (strlen($complaint['description']) > 50 ? '...' : ''); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-category"><?php echo ucwords(str_replace('_', ' ', $complaint['category'])); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge priority-<?php echo $complaint['priority']; ?>">
                                        <?php echo ucfirst($complaint['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($complaint['order_id'])): ?>
                                        <a href="order_details.php?id=<?php echo $complaint['order_id']; ?>" class="order-link">
                                            #<?php echo $complaint['order_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></small>
                                </td>
                                <td class="actions">
                                    <button class="btn-admin btn-secondary btn-small btn-edit-complaint" 
                                            data-id="<?php echo $complaint['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($complaint['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($complaint['description']); ?>"
                                            data-status="<?php echo $complaint['status']; ?>"
                                            data-resolution="<?php echo htmlspecialchars($complaint['resolution_note'] ?? ''); ?>">
                                        <i class='bx bx-edit'></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class='bx bx-inbox'></i>
                    <p>No complaints found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Complaint Modal -->
<div class="modal" id="editComplaintModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="modalTitle">Edit Complaint</h2>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editComplaintForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="complaint_id" id="complaintId">

                <div class="form-section">
                    <h4>Complaint Details</h4>
                    <div class="form-group">
                        <label>Title</label>
                        <p id="complaintTitle" class="form-static"></p>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <p id="complaintDescription" class="form-static" style="white-space: pre-wrap;"></p>
                    </div>
                </div>

                <div class="form-section">
                    <h4>Update Status</h4>
                    <div class="form-group">
                        <label for="statusSelect">Status *</label>
                        <select id="statusSelect" name="status" required>
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_review">In Review</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="resolutionNote">Resolution Note</label>
                        <textarea id="resolutionNote" name="resolution_note" placeholder="Add a note about the resolution..." rows="5"></textarea>
                        <small>This will be visible to the user</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-admin btn-primary">
                        <i class='bx bx-save'></i> Update Status
                    </button>
                    <button type="button" class="btn-admin btn-secondary" id="cancelEdit">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open edit modal
document.querySelectorAll('.btn-edit-complaint').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const title = this.dataset.title;
        const description = this.dataset.description;
        const status = this.dataset.status;
        const resolution = this.dataset.resolution;

        document.getElementById('modalTitle').textContent = 'Edit Complaint #' + id;
        document.getElementById('complaintId').value = id;
        document.getElementById('complaintTitle').textContent = title;
        document.getElementById('complaintDescription').textContent = description;
        document.getElementById('statusSelect').value = status;
        document.getElementById('resolutionNote').value = resolution;

        document.getElementById('editComplaintModal').style.display = 'block';
    });
});

// Close modal
document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('editComplaintModal').style.display = 'none';
});

document.getElementById('cancelEdit').addEventListener('click', function() {
    document.getElementById('editComplaintModal').style.display = 'none';
});

// Close modal on background click
window.addEventListener('click', function(e) {
    const modal = document.getElementById('editComplaintModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// Submit form
document.getElementById('editComplaintForm').addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
