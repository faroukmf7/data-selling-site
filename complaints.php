<?php
// complaints.php
$page_title = "My Complaints";
require_once 'includes/header.php';

// Ensure complaints table exists
ensureComplaintsTableExists($pdo);

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = "Please login to view complaints.";
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle form submission for new complaint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_complaint') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $order_id = isset($_POST['order_id']) && $_POST['order_id'] !== '' ? (int)$_POST['order_id'] : null;
    $category = sanitizeInput($_POST['category'] ?? '');
    $priority = sanitizeInput($_POST['priority'] ?? 'medium');

    if (!empty($title) && !empty($description) && !empty($category)) {
        $stmt = $pdo->prepare("INSERT INTO complaints (user_id, order_id, title, description, category, priority, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        if ($stmt->execute([$user_id, $order_id, $title, $description, $category, $priority])) {
            $_SESSION['message'] = "Complaint submitted successfully!";
        } else {
            $_SESSION['error'] = "Failed to submit complaint. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
    }
}

// Get complaint statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'in_review' => 0,
    'resolved' => 0,
    'rejected' => 0
];

$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM complaints WHERE user_id = ? GROUP BY status");
$stmt->execute([$user_id]);
$status_counts = $stmt->fetchAll();

foreach ($status_counts as $row) {
    $stats[$row['status']] = $row['count'];
    $stats['total'] += $row['count'];
}

// Get all complaints with filtering and search
$search = sanitizeInput($_GET['search'] ?? '');
$filter_status = sanitizeInput($_GET['status'] ?? '');

$query = "SELECT c.*, o.total_amount, p.name as product_name 
          FROM complaints c 
          LEFT JOIN orders o ON c.order_id = o.id 
          LEFT JOIN products p ON o.product_id = p.id 
          WHERE c.user_id = ?";
$params = [$user_id];

if (!empty($search)) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.category LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term);
}

if (!empty($filter_status)) {
    $query .= " AND c.status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Get user orders for complaint creation
$stmt = $pdo->prepare("SELECT o.id, o.id as order_number, p.name as product_name, o.created_at 
                      FROM orders o 
                      LEFT JOIN products p ON o.product_id = p.id 
                      WHERE o.user_id = ? 
                      ORDER BY o.created_at DESC 
                      LIMIT 20");
$stmt->execute([$user_id]);
$user_orders = $stmt->fetchAll();
?>

<div class="content">
    <div class="complaints-header">
        <h1>Complaints & Support</h1>
        <p>Track and manage your package complaints</p>
    </div>

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

    <!-- Statistics Cards -->
    <div class="complaints-stats-grid">
        <div class="complaint-stat-card">
            <div class="stat-icon total">
                <i class='bx bx-list-check'></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Complaints</p>
                <p class="stat-number"><?php echo $stats['total']; ?></p>
            </div>
        </div>

        <div class="complaint-stat-card">
            <div class="stat-icon pending">
                <i class='bx bx-time-five'></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Pending</p>
                <p class="stat-number"><?php echo $stats['pending']; ?></p>
            </div>
        </div>

        <div class="complaint-stat-card">
            <div class="stat-icon in-review">
                <i class='bx bx-search'></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">In Review</p>
                <p class="stat-number"><?php echo $stats['in_review']; ?></p>
            </div>
        </div>

        <div class="complaint-stat-card">
            <div class="stat-icon resolved">
                <i class='bx bx-check-double'></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Resolved</p>
                <p class="stat-number"><?php echo $stats['resolved']; ?></p>
            </div>
        </div>

        <div class="complaint-stat-card">
            <div class="stat-icon rejected">
                <i class='bx bx-x-circle'></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Rejected</p>
                <p class="stat-number"><?php echo $stats['rejected']; ?></p>
            </div>
        </div>
    </div>

    <!-- New Complaint Form -->
    <div class="complaints-section">
        <div class="section-header">
            <h2>Submit New Complaint</h2>
            <button class="btn-toggle-form" id="toggleFormBtn">
                <i class='bx bx-chevron-down'></i>
            </button>
        </div>

        <div class="complaint-form-container" id="complaintForm" style="display: none;">
            <form method="POST" class="complaint-form">
                <input type="hidden" name="action" value="submit_complaint">

                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Complaint Title *</label>
                        <input type="text" id="title" name="title" placeholder="Brief title of your complaint" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="not_received">Package Not Received</option>
                            <option value="partial_delivery">Partial Delivery</option>
                            <option value="quality_issue">Quality Issue</option>
                            <option value="wrong_product">Wrong Product Delivered</option>
                            <option value="damaged_package">Damaged Package</option>
                            <option value="service_issue">Service Issue</option>
                            <option value="billing_issue">Billing Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="order_id">Related Order (Optional)</label>
                        <select id="order_id" name="order_id">
                            <option value="">No specific order</option>
                            <?php foreach ($user_orders as $order): ?>
                                <option value="<?php echo $order['id']; ?>">
                                    Order #<?php echo $order['order_number']; ?> - <?php echo $order['product_name']; ?> (<?php echo date('M d, Y', strtotime($order['created_at'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" placeholder="Describe your complaint in detail..." rows="5" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-send'></i>
                        Submit Complaint
                    </button>
                    <button type="button" class="btn-secondary" id="cancelFormBtn">
                        <i class='bx bx-x'></i>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="complaints-section">
        <div class="section-header">
            <h2>All Complaints</h2>
        </div>

        <div class="complaints-controls">
            <div class="search-box">
                <i class='bx bx-search'></i>
                <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Search by title, description, or category..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_review" <?php echo $filter_status === 'in_review' ? 'selected' : ''; ?>>In Review</option>
                        <option value="resolved" <?php echo $filter_status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit" class="btn-filter">
                        <i class='bx bx-filter'></i>
                        Filter
                    </button>
                    <?php if (!empty($search) || !empty($filter_status)): ?>
                        <a href="complaints.php" class="btn-filter-clear">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="complaints-table-container">
            <?php if (!empty($complaints)): ?>
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr class="complaint-row status-<?php echo $complaint['status']; ?>">
                                <td>
                                    <div class="complaint-title">
                                        <strong><?php echo htmlspecialchars($complaint['title']); ?></strong>
                                        <?php if (!empty($complaint['description'])): ?>
                                            <p class="complaint-desc-preview"><?php echo substr(htmlspecialchars($complaint['description']), 0, 60) . (strlen($complaint['description']) > 60 ? '...' : ''); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-category"><?php echo ucwords(str_replace('_', ' ', $complaint['category'])); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-status status-<?php echo $complaint['status']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-priority priority-<?php echo $complaint['priority']; ?>">
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
                                    <small><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="#" class="btn-view-detail" data-id="<?php echo $complaint['id']; ?>" data-title="<?php echo htmlspecialchars($complaint['title']); ?>" data-description="<?php echo htmlspecialchars($complaint['description']); ?>" data-resolution="<?php echo htmlspecialchars($complaint['resolution_note'] ?? ''); ?>" data-status="<?php echo $complaint['status']; ?>">
                                        <i class='bx bx-show'></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class='bx bx-inbox'></i>
                    <h3>No complaints found</h3>
                    <p><?php echo empty($search) && empty($filter_status) ? "You haven't submitted any complaints yet." : "No complaints match your search criteria."; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal" id="complaintDetailModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="modalTitle">Complaint Details</h2>
            <button class="modal-close" id="closeDetailModal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalDescription"></div>
            <div class="detail-section" id="resolutionSection" style="display: none;">
                <h4>Resolution Note</h4>
                <p id="modalResolution"></p>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle complaint form
document.getElementById('toggleFormBtn').addEventListener('click', function() {
    const form = document.getElementById('complaintForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    this.classList.toggle('active');
});

document.getElementById('cancelFormBtn').addEventListener('click', function() {
    document.getElementById('complaintForm').style.display = 'none';
    document.getElementById('toggleFormBtn').classList.remove('active');
});

// View complaint details
document.querySelectorAll('.btn-view-detail').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const title = this.dataset.title;
        const description = this.dataset.description;
        const resolution = this.dataset.resolution;
        const status = this.dataset.status;

        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalDescription').innerHTML = `<strong>Description:</strong><br><p>${description}</p><strong>Status:</strong><br><p>${status.replace('_', ' ').toUpperCase()}</p>`;

        const resolutionSection = document.getElementById('resolutionSection');
        if (resolution) {
            document.getElementById('modalResolution').textContent = resolution;
            resolutionSection.style.display = 'block';
        } else {
            resolutionSection.style.display = 'none';
        }

        document.getElementById('complaintDetailModal').style.display = 'block';
    });
});

// Close modal
document.getElementById('closeDetailModal').addEventListener('click', function() {
    document.getElementById('complaintDetailModal').style.display = 'none';
});

window.addEventListener('click', function(e) {
    const modal = document.getElementById('complaintDetailModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
