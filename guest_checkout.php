<?php
// guest_checkout.php
$page_title = "Guest Checkout";
require_once 'includes/header.php';

// Handle guest checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $recipient_number = sanitize($_POST['recipient_number']);
    $guest_phone = sanitize($_POST['guest_phone']);
    $guest_email = sanitize($_POST['guest_email']);
    $data_amount = isset($_POST['data_amount']) ? (float)$_POST['data_amount'] : null;
    $fixed_price = isset($_POST['fixed_price']) ? (float)$_POST['fixed_price'] : null;
    $exam_type = isset($_POST['exam_type']) ? sanitize($_POST['exam_type']) : null;

    // Validate email format
    if (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Please enter a valid email address.";
        redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
    }

    // Validate phone format (basic validation)
    if (empty($guest_phone) || strlen($guest_phone) < 9) {
        $_SESSION['message'] = "Please enter a valid phone number.";
        redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
    }

    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['message'] = "Product not found.";
        redirect('products.php');
    }

    // Calculate total
    if ($product['is_flexible'] && $data_amount) {
        // Flexible data plan
        if (
            $data_amount < $product['min_value'] ||
            ($product['max_value'] && $data_amount > $product['max_value'])
        ) {
            $_SESSION['message'] = "Invalid data amount. Please enter between " .
                $product['min_value'] . " and " . $product['max_value'] .
                $product['unit'] . ".";
            redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
        }

        $total = $data_amount * $product['price_per_unit'];
        $description = $data_amount . $product['unit'] . " " . $product['name'];
    } else {
        // Fixed price product
        $total = $fixed_price ?: $product['price'];
        $description = $product['name'];

        if ($exam_type) {
            $description = $exam_type . " " . $description;
        }
    }

    // Validate that total_amount is greater than 0
    if ($total <= 0) {
        $_SESSION['message'] = "Invalid order amount.";
        redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
    }

    // Store order in session for payment processing
    $_SESSION['current_order'] = [
        'product_id' => $product_id,
        'product_name' => $description,
        'recipient_number' => $recipient_number,
        'total_amount' => $total,
        'data_amount' => $data_amount,
        'exam_type' => $exam_type,
        'network' => $product['network'],
        'category' => $product['category'],
        'is_guest' => true,
        'guest_phone' => $guest_phone,
        'guest_email' => $guest_email
    ];

    // Redirect to guest payment
    redirect('guest_payment.php');
    exit();
}

// Get product if passed via GET
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$product = null;

if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['message'] = "Product not found.";
        redirect('products.php');
    }
}
?>

<div class="content">
    <h1>Guest Checkout</h1>
    <p class="subtitle">Purchase without creating an account</p>

    <div class="guest-checkout-container">
        <div class="checkout-form-section">
            <h2>Your Details</h2>

            <form method="POST" class="guest-checkout-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id'] ?? ''; ?>">

                <!-- Guest Contact Information -->
                <div class="form-group">
                    <label for="guest_phone">Phone Number *</label>
                    <input type="tel" id="guest_phone" name="guest_phone" required 
                           placeholder="e.g., 0201234567" 
                           value="<?php echo isset($_POST['guest_phone']) ? htmlspecialchars($_POST['guest_phone']) : ''; ?>">
                    <small>Your phone number for order confirmation</small>
                </div>

                <div class="form-group">
                    <label for="guest_email">Email Address *</label>
                    <input type="email" id="guest_email" name="guest_email" required 
                           placeholder="your.email@example.com"
                           value="<?php echo isset($_POST['guest_email']) ? htmlspecialchars($_POST['guest_email']) : ''; ?>">
                    <small>We'll send your receipt and order details to this email</small>
                </div>

                <!-- Recipient Information -->
                <div class="form-group">
                    <label for="recipient_number">Recipient Phone Number *</label>
                    <input type="tel" id="recipient_number" name="recipient_number" required 
                           placeholder="Phone number to receive the service"
                           value="<?php echo isset($_POST['recipient_number']) ? htmlspecialchars($_POST['recipient_number']) : ''; ?>">
                    <small>The phone number where the data/airtime will be sent</small>
                </div>

                <!-- Product-specific fields -->
                <?php if ($product): ?>
                    <?php if ($product['is_flexible']): ?>
                        <div class="form-group">
                            <label for="data_amount">Amount (<?php echo $product['unit']; ?>) *</label>
                            <input type="number" id="data_amount" name="data_amount" 
                                   min="<?php echo $product['min_value']; ?>" 
                                   max="<?php echo $product['max_value'] ?: 'any'; ?>"
                                   step="0.1" required 
                                   placeholder="e.g., 1"
                                   value="<?php echo isset($_POST['data_amount']) ? htmlspecialchars($_POST['data_amount']) : ''; ?>">
                            <small>Between <?php echo $product['min_value']; ?> and <?php echo $product['max_value']; ?> <?php echo $product['unit']; ?></small>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="fixed_price" value="<?php echo $product['price']; ?>">
                        <div class="form-group">
                            <label>Product Price</label>
                            <div class="price-display">GHS <?php echo number_format($product['price'], 2); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($product['category'] == 'exam_pin'): ?>
                        <div class="form-group">
                            <label for="exam_type">Exam Type *</label>
                            <select id="exam_type" name="exam_type" required>
                                <option value="">Select exam type</option>
                                <option value="WAEC" <?php echo (isset($_POST['exam_type']) && $_POST['exam_type'] == 'WAEC') ? 'selected' : ''; ?>>WAEC</option>
                                <option value="NECO" <?php echo (isset($_POST['exam_type']) && $_POST['exam_type'] == 'NECO') ? 'selected' : ''; ?>>NECO</option>
                                <option value="BECE" <?php echo (isset($_POST['exam_type']) && $_POST['exam_type'] == 'BECE') ? 'selected' : ''; ?>>BECE</option>
                            </select>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="form-group">
                        <p class="error-message">Please select a product first.</p>
                        <a href="products.php" class="btn-primary">View Products</a>
                    </div>
                <?php endif; ?>

                <?php if ($product): ?>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Continue to Payment</button>
                        <a href="products.php" class="btn-secondary">Back to Products</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Order Summary -->
        <?php if ($product): ?>
            <div class="checkout-summary-section">
                <h2>Order Summary</h2>
                <div class="order-summary">
                    <div class="summary-item">
                        <span>Product:</span>
                        <span class="value"><?php echo $product['name']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Network:</span>
                        <span class="value"><?php echo $product['network']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Category:</span>
                        <span class="value"><?php echo ucfirst($product['category']); ?></span>
                    </div>

                    <?php if (!$product['is_flexible']): ?>
                        <div class="summary-item">
                            <span>Price:</span>
                            <span class="value">GHS <?php echo number_format($product['price'], 2); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="summary-item">
                            <span>Price Per <?php echo $product['unit']; ?>:</span>
                            <span class="value">GHS <?php echo number_format($product['price_per_unit'], 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-divider"></div>
                    <div class="summary-item total">
                        <span>Estimated Total:</span>
                        <span class="value" id="total-amount">
                            <?php 
                            if ($product['is_flexible']) {
                                echo "GHS 0.00";
                            } else {
                                echo "GHS " . number_format($product['price'], 2);
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <div class="info-box">
                    <h4><i class='bx bx-info-circle'></i> Guest Purchase Benefits</h4>
                    <ul>
                        <li>No account creation needed</li>
                        <li>Quick checkout process</li>
                        <li>Instant service delivery</li>
                        <li>Email receipt and confirmation</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($product && $product['is_flexible']): ?>
    <script>
        // Update total amount when data amount changes
        const dataAmountInput = document.getElementById('data_amount');
        const totalDisplay = document.getElementById('total-amount');
        const pricePerUnit = <?php echo $product['price_per_unit']; ?>;

        if (dataAmountInput) {
            dataAmountInput.addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                const total = (amount * pricePerUnit).toFixed(2);
                totalDisplay.textContent = 'GHS ' + parseFloat(total).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            });
        }
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
