<?php
// admin/products.php
$page_title = "Manage Products";
require_once 'includes/admin_header.php';

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->execute([$product_id]);
    $_SESSION['message'] = "Product deleted successfully.";
    redirect('products.php');
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $product_id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$product_id]);
    $_SESSION['message'] = "Product status updated.";
    redirect('products.php');
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<div class="admin-content">
    <h1>Manage Products</h1>

    <div class="quick-actions">
        <a href="add_product.php" class="btn-admin btn-primary">
            <i class='bx bx-plus'></i> Add New Product
        </a>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Network</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Data Value</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No products found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['network']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo ucfirst($product['category']); ?></td>
                            <td>GHS <?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['data_value'] ?: 'N/A'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-admin btn-secondary btn-small">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="?toggle=<?php echo $product['id']; ?>" class="btn-admin btn-<?php echo $product['is_active'] ? 'warning' : 'success'; ?> btn-small">
                                    <i class='bx bx-<?php echo $product['is_active'] ? 'x-circle' : 'check-circle'; ?>'></i>
                                </a>
                                <a href="?delete=<?php echo $product['id']; ?>"
                                    class="btn-admin btn-danger btn-small"
                                    onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
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