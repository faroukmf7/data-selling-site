<?php
// admin/add_product.php
$page_title = "Add Product";
require_once 'includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $network = sanitize($_POST['network']);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $data_value = sanitize($_POST['data_value']);
    $validity_days = (int)$_POST['validity_days'];
    $category = sanitize($_POST['category']);

    // Insert product
    $stmt = $pdo->prepare("INSERT INTO products (network, name, description, price, data_value, validity_days, category) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$network, $name, $description, $price, $data_value, $validity_days, $category])) {
        $_SESSION['message'] = "Product added successfully!";
        redirect('products.php');
    } else {
        $error = "Failed to add product. Please try again.";
    }
}

// Get distinct networks for dropdown
$networks_stmt = $pdo->query("SELECT DISTINCT network FROM products ORDER BY network");
?>

<div class="admin-content">
    <h1 class="admin-dashboard-header">Add New Product</h1>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="admin-form">
        <form method="POST" action="">
            <div class="form-group">
                <label for="network">Network</label>
                <select id="network" name="network" required>
                    <option value="">Select Network</option>
                    <?php while ($network = $networks_stmt->fetch()): ?>
                        <option value="<?php echo $network['network']; ?>">
                            <?php echo $network['network']; ?>
                        </option>
                    <?php endwhile; ?>
                    <option value="NEW">Add New Network...</option>
                </select>
                <input type="text" id="new_network" name="new_network" placeholder="Enter new network name" style="display: none; margin-top: 5px;">
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="data">Data Bundle</option>
                    <option value="airtime">Airtime</option>
                    <option value="exam_pin">Exam PIN</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required placeholder="e.g., 1GB Monthly Plan">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Product description"></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (GHS)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required placeholder="0.00">
            </div>
            <!-- Inside the form, add these fields: -->
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_flexible" value="1" id="is_flexible" onchange="toggleFlexibleFields()">
                    Flexible Pricing (for data plans)
                </label>
            </div>

            <div id="flexible-fields" style="display: none;">
                <div class="form-group">
                    <label for="price_per_unit">Price per Unit (GHS)</label>
                    <input type="number" id="price_per_unit" name="price_per_unit" step="0.01" min="0.01" placeholder="Price per GB/MB">
                </div>

                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" name="unit">
                        <option value="GB">GB</option>
                        <option value="MB">MB</option>
                        <option value="Day">Day</option>
                        <option value="Week">Week</option>
                        <option value="Month">Month</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="min_value">Minimum Value</label>
                    <input type="number" id="min_value" name="min_value" step="0.01" min="0.01" value="0.10">
                </div>

                <div class="form-group">
                    <label for="max_value">Maximum Value (optional)</label>
                    <input type="number" id="max_value" name="max_value" step="0.01" min="0">
                    <small>Leave empty for unlimited</small>
                </div>
            </div>

            <div id="fixed-fields">
                <div class="form-group">
                    <label for="price">Price (GHS) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" required placeholder="Fixed price">
                </div>

                <div class="form-group">
                    <label for="data_value">Data Value/Quantity</label>
                    <input type="text" id="data_value" name="data_value" placeholder="e.g., 1GB, GHS 10, or leave empty">
                </div>
            </div>

            <script>
                function toggleFlexibleFields() {
                    const isFlexible = document.getElementById('is_flexible').checked;
                    const flexibleFields = document.getElementById('flexible-fields');
                    const fixedFields = document.getElementById('fixed-fields');

                    if (isFlexible) {
                        flexibleFields.style.display = 'block';
                        fixedFields.style.display = 'none';
                        document.getElementById('price').required = false;
                        document.getElementById('price_per_unit').required = true;
                    } else {
                        flexibleFields.style.display = 'none';
                        fixedFields.style.display = 'block';
                        document.getElementById('price').required = true;
                        document.getElementById('price_per_unit').required = false;
                    }
                }

                // Initialize based on category
                document.getElementById('category').addEventListener('change', function() {
                    const category = this.value;
                    const isFlexibleCheckbox = document.getElementById('is_flexible');

                    if (category === 'data') {
                        isFlexibleCheckbox.checked = true;
                    } else {
                        isFlexibleCheckbox.checked = false;
                    }

                    toggleFlexibleFields();
                });
            </script>


            <div class="form-group">
                <label for="data_value">Data Value (for data bundles)</label>
                <input type="text" id="data_value" name="data_value" placeholder="e.g., 1GB, 500MB, or leave empty for non-data">
            </div>

            <div class="form-group">
                <label for="validity_days">Validity (Days)</label>
                <input type="number" id="validity_days" name="validity_days" value="30" min="0">
                <small>Set to 0 for no expiration (e.g., airtime)</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-admin btn-primary">
                    <i class='bx bx-save'></i> Save Product
                </button>
                <a href="products.php" class="btn-admin btn-secondary">
                    <i class='bx bx-x'></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const networkSelect = document.getElementById('network');
        const newNetworkInput = document.getElementById('new_network');

        networkSelect.addEventListener('change', function() {
            if (this.value === 'NEW') {
                newNetworkInput.style.display = 'block';
                newNetworkInput.required = true;
            } else {
                newNetworkInput.style.display = 'none';
                newNetworkInput.required = false;
            }
        });
    });
</script>

<?php
require_once 'includes/admin_footer.php';
?>