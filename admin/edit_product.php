<?php
// admin/edit_product.php
$page_title = "Edit Product";
require_once 'includes/admin_header.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = (int)$_GET['id'];

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['message'] = "Product not found.";
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $network = sanitize($_POST['network']);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $data_value = sanitize($_POST['data_value']);
    $validity_days = (int)$_POST['validity_days'];
    $category = sanitize($_POST['category']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Update product
    $stmt = $pdo->prepare("UPDATE products SET network = ?, name = ?, description = ?, price = ?, 
                          data_value = ?, validity_days = ?, category = ?, is_active = ? WHERE id = ?");

    if ($stmt->execute([$network, $name, $description, $price, $data_value, $validity_days, $category, $is_active, $product_id])) {
        $_SESSION['message'] = "Product updated successfully!";
        redirect('products.php');
    } else {
        $error = "Failed to update product. Please try again.";
    }
}

// Get distinct networks
$networks_stmt = $pdo->query("SELECT DISTINCT network FROM products ORDER BY network");
?>

<div class="admin-content">
    <h1 class="admin-dashboard-header">Edit Product</h1>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="admin-form">
        <form method="POST" action="">
            <div class="form-group">
                <label for="network">Network</label>
                <select id="network" name="network" required>
                    <?php while ($network = $networks_stmt->fetch()): ?>
                        <option value="<?php echo $network['network']; ?>" <?php echo ($product['network'] == $network['network']) ? 'selected' : ''; ?>>
                            <?php echo $network['network']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="data" <?php echo ($product['category'] == 'data') ? 'selected' : ''; ?>>Data Bundle</option>
                    <option value="airtime" <?php echo ($product['category'] == 'airtime') ? 'selected' : ''; ?>>Airtime</option>
                    <option value="exam_pin" <?php echo ($product['category'] == 'exam_pin') ? 'selected' : ''; ?>>Exam PIN</option>
                    <option value="other" <?php echo ($product['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (GHS)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="form-group">
                <label for="data_value">Data Value</label>
                <input type="text" id="data_value" name="data_value" value="<?php echo htmlspecialchars($product['data_value']); ?>">
            </div>

            <div class="form-group">
                <label for="validity_days">Validity (Days)</label>
                <input type="number" id="validity_days" name="validity_days" value="<?php echo $product['validity_days']; ?>" min="0">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                    Active Product
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-admin btn-primary">
                    <i class='bx bx-save'></i> Update Product
                </button>
                <a href="products.php" class="btn-admin btn-secondary">
                    <i class='bx bx-x'></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'includes/admin_footer.php';
?>