<?php
// products.php
$page_title = "Products";
require_once 'includes/header.php';

// Get filter parameters
$network_filter = isset($_GET['network']) ? sanitize($_GET['network']) : '';
$category_filter = isset($_GET['category']) ? sanitize($_GET['category']) : 'data'; // Default to data

// Build query
$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];

if (!empty($network_filter)) {
    $sql .= " AND network = ?";
    $params[] = $network_filter;
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY network, category, price_per_unit";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get distinct networks and categories for filters
$networks_stmt = $pdo->query("SELECT DISTINCT network FROM products WHERE is_active = 1 ORDER BY network");
$categories_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category");
?>

<div class="content">
    <div class="products-header">
        <h1>Buy Data & Airtime</h1>
        <p class="subtitle">Select your network and purchase instantly</p>

        <!-- Quick Network Selection -->
        <div class="network-selector">
            <h3>Select Network</h3>
            <div class="network-buttons">
                <a href="products.php?network=MTN&category=data" class="network-btn <?php echo ($network_filter == 'MTN') ? 'active' : ''; ?>">
                    <img src="images/MTN.png" alt="MTN">
                    <span>MTN</span>
                </a>
                <a href="products.php?network=AIRTEL-TIGO&category=data" class="network-btn <?php echo ($network_filter == 'AIRTEL-TIGO') ? 'active' : ''; ?>">
                    <img src="images/airteltigo.png" alt="Airtel-Tigo">
                    <span>Airtel-Tigo</span>
                </a>
                <a href="products.php?network=TELECEL&category=data" class="network-btn <?php echo ($network_filter == 'TELECEL') ? 'active' : ''; ?>">
                    <img src="images/telecel.png" alt="Telecel">
                    <span>Telecel</span>
                </a>
                <a href="products.php?category=airtime" class="network-btn <?php echo ($category_filter == 'airtime') ? 'active' : ''; ?>">
                    <i class='bx bx-phone'></i>
                    <span>Airtime</span>
                </a>
                <a href="products.php?category=exam_pin" class="network-btn <?php echo ($category_filter == 'exam_pin') ? 'active' : ''; ?>">
                    <i class='bx bx-book'></i>
                    <span>Exam PINs</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="products-grid-direct">
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>No products available for the selected filter.</p>
                <a href="products.php" class="btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card-direct">
                    <div class="product-header">
                        <div class="product-network"><?php echo $product['network']; ?></div>
                        <div class="product-category"><?php echo ucfirst($product['category']); ?></div>
                    </div>

                    <div class="product-name"><?php echo $product['name']; ?></div>

                    <?php if ($product['is_flexible']): ?>
                        <!-- Flexible Data Plan -->
                        <div class="flexible-plan">
                            <div class="price-info">
                                <span class="price-per-unit">GHS <?php echo number_format($product['price_per_unit'], 2); ?>/<?php echo $product['unit']; ?></span>
                                <span class="validity">Valid for <?php echo $product['validity_days']; ?> days</span>
                            </div>

                            <form action="checkout.php" method="POST" class="purchase-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                                <div class="form-group">
                                    <label for="data_amount_<?php echo $product['id']; ?>">
                                        <?php echo ucfirst($product['unit']); ?> Amount:
                                    </label>
                                    <div class="input-with-unit">
                                        <input type="number"
                                            id="data_amount_<?php echo $product['id']; ?>"
                                            name="data_amount"
                                            step="0.01"
                                            min="<?php echo $product['min_value']; ?>"
                                            max="<?php echo $product['max_value']; ?>"
                                            value="<?php echo $product['min_value']; ?>"
                                            required
                                            oninput="calculatePrice(<?php echo $product['id']; ?>, <?php echo $product['price_per_unit']; ?>)">
                                        <span class="unit"><?php echo $product['unit']; ?></span>
                                    </div>
                                    <div class="range-info">
                                        <small>Min: <?php echo $product['min_value']; ?><?php echo $product['unit']; ?></small>
                                        <?php if ($product['max_value']): ?>
                                            <small>Max: <?php echo $product['max_value']; ?><?php echo $product['unit']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="recipient_<?php echo $product['id']; ?>">Recipient Phone Number:</label>
                                    <input type="tel"
                                        id="recipient_<?php echo $product['id']; ?>"
                                        name="recipient_number"
                                        placeholder="024XXXXXXX"
                                        pattern="[0-9]{10}"
                                        required>
                                </div>

                                <div class="price-summary">
                                    <div class="price-row">
                                        <span>Subtotal:</span>
                                        <span id="subtotal_<?php echo $product['id']; ?>">GHS 0.00</span>
                                    </div>
                                    <div class="price-row total">
                                        <span>Total:</span>
                                        <span id="total_<?php echo $product['id']; ?>">GHS 0.00</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn-buy-now" <?php echo !isLoggedIn() ? 'disabled' : ''; ?>>
                                    <?php if (isLoggedIn()): ?>
                                        <i class='bx bx-cart'></i> Buy Now
                                    <?php else: ?>
                                        <i class='bx bx-lock'></i> Login to Purchase
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>

                    <?php else: ?>
                        <!-- Fixed Product (Airtime, Exam PINs) -->
                        <div class="fixed-product">
                            <div class="product-price">GHS <?php echo number_format($product['price'], 2); ?></div>

                            <?php if ($product['data_value'] && $product['data_value'] != 'N/A'): ?>
                                <div class="data-value"><?php echo $product['data_value']; ?></div>
                            <?php endif; ?>

                            <?php if ($product['validity_days'] > 0): ?>
                                <div class="validity">Valid for <?php echo $product['validity_days']; ?> days</div>
                            <?php endif; ?>

                            <form action="checkout.php" method="POST" class="purchase-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="fixed_price" value="<?php echo $product['price']; ?>">

                                <?php if ($product['category'] == 'airtime'): ?>
                                    <div class="form-group">
                                        <label for="recipient_<?php echo $product['id']; ?>">Recipient Phone Number:</label>
                                        <input type="tel"
                                            id="recipient_<?php echo $product['id']; ?>"
                                            name="recipient_number"
                                            placeholder="024XXXXXXX"
                                            pattern="[0-9]{10}"
                                            required>
                                    </div>
                                <?php elseif ($product['category'] == 'exam_pin'): ?>
                                    <div class="form-group">
                                        <label for="exam_type_<?php echo $product['id']; ?>">Exam Type:</label>
                                        <select id="exam_type_<?php echo $product['id']; ?>" name="exam_type">
                                            <option value="WAEC">WAEC</option>
                                            <option value="NECO">NECO</option>
                                            <option value="BECE">BECE</option>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <button type="submit" class="btn-buy-now" <?php echo !isLoggedIn() ? 'disabled' : ''; ?>>
                                    <?php if (isLoggedIn()): ?>
                                        <i class='bx bx-cart'></i> Buy Now
                                    <?php else: ?>
                                        <i class='bx bx-lock'></i> Login to Purchase
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function calculatePrice(productId, pricePerUnit) {
        const amountInput = document.getElementById('data_amount_' + productId);
        const amount = parseFloat(amountInput.value) || 0;
        const subtotal = amount * pricePerUnit;

        document.getElementById('subtotal_' + productId).textContent = 'GHS ' + subtotal.toFixed(2);
        document.getElementById('total_' + productId).textContent = 'GHS ' + subtotal.toFixed(2);
    }

    // Initialize prices on page load
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($products as $product): ?>
            <?php if ($product['is_flexible']): ?>
                calculatePrice(<?php echo $product['id']; ?>, <?php echo $product['price_per_unit']; ?>);
            <?php endif; ?>
        <?php endforeach; ?>

        // Form validation
        const forms = document.querySelectorAll('.purchase-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                    e.preventDefault();
                    alert('Please login to make a purchase.');
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                    return false;
                }

                const amountInput = this.querySelector('input[name="data_amount"]');
                if (amountInput) {
                    const min = parseFloat(amountInput.getAttribute('min'));
                    const max = parseFloat(amountInput.getAttribute('max'));
                    const value = parseFloat(amountInput.value);

                    if (value < min || value > max) {
                        e.preventDefault();
                        alert('Please enter a value between ' + min + ' and ' + max + '.');
                        amountInput.focus();
                        return false;
                    }
                }

                return true;
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>