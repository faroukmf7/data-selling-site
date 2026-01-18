<?php
// products.php
$page_title = "Products";
require_once 'includes/header.php';

// Get all products
$sql = "SELECT * FROM products WHERE is_active = 1 ORDER BY network, category, price_per_unit";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_products = $stmt->fetchAll();

// Get distinct networks and categories for filters
$networks_stmt = $pdo->query("SELECT DISTINCT network FROM products WHERE is_active = 1 ORDER BY network");
$categories_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category");

// Get networks for modal
$networks = $networks_stmt->fetchAll();
?>

<div class="content">
    <div class="products-header">
        <h1>Buy Data & Airtime</h1>
        <p class="subtitle">Select your network and purchase instantly</p>

        <!-- Quick Network Selection -->
        <div class="network-selector">
            <h3>Select Network</h3>
            <div class="network-buttons">
                <button type="button" class="network-btn" onclick="openProductModal('MTN')">
                    <img src="images/MTN.png" alt="MTN">
                    <span>MTN</span>
                </button>
                <button type="button" class="network-btn" onclick="openProductModal('AIRTEL-TIGO')">
                    <img src="images/airteltigo.png" alt="Airtel-Tigo">
                    <span>Airtel-Tigo</span>
                </button>
                <button type="button" class="network-btn" onclick="openProductModal('TELECEL')">
                    <img src="images/telecel.png" alt="Telecel">
                    <span>Telecel</span>
                </button>
                <button type="button" class="network-btn" onclick="openProductModal('airtime')">
                    <i class='bx bx-phone'></i>
                    <span>Airtime</span>
                </button>
                <button type="button" class="network-btn" onclick="openProductModal('exam_pin')">
                    <i class='bx bx-book'></i>
                    <span>Exam PINs</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="product-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Products</h2>
                <button class="modal-close" onclick="closeProductModal()">
                    <i style="font-size: 1.2em;" class='bx bx-x'></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalProductsContainer" class="modal-products-grid">
                    <!-- Products will be loaded here by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden div to store all products data for JavaScript -->
    <div id="allProductsData" style="display: none;">
        <?php foreach ($all_products as $product): ?>
            <div class="product-data" data-network="<?php echo htmlspecialchars($product['network']); ?>" data-category="<?php echo htmlspecialchars($product['category']); ?>" data-id="<?php echo $product['id']; ?>" data-is-flexible="<?php echo $product['is_flexible']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>" data-price-per-unit="<?php echo $product['price_per_unit']; ?>" data-unit="<?php echo htmlspecialchars($product['unit']); ?>" data-min-value="<?php echo $product['min_value']; ?>" data-max-value="<?php echo $product['max_value']; ?>" data-validity-days="<?php echo $product['validity_days']; ?>" data-data-value="<?php echo htmlspecialchars($product['data_value'] ?? ''); ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Original Products Grid (hidden for reference) -->
    <div class="products-grid-direct" style="display: none;">
        <?php if (empty($all_products)): ?>
            <div class="no-products">
                <p>No products available.</p>
                <a href="products.php" class="btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <?php foreach ($all_products as $product): ?>
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

                                <button type="submit" class="btn-buy-now">
                                    <i class='bx bx-cart'></i> Buy Now
                                </button>
                            </form>

                            <?php if (!isLoggedIn()): ?>
                                <a href="guest_checkout.php?product_id=<?php echo $product['id']; ?>" class="btn-guest-checkout">
                                    <i class='bx bx-user'></i> Guest Checkout
                                </a>
                            <?php endif; ?>
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

                                <button type="submit" class="btn-buy-now">
                                    <i class='bx bx-cart'></i> Buy Now
                                </button>
                            </form>

                            <?php if (!isLoggedIn()): ?>
                                <a href="guest_checkout.php?product_id=<?php echo $product['id']; ?>" class="btn-guest-checkout">
                                    <i class='bx bx-user'></i> Guest Checkout
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Product data stored as JSON
    const allProducts = <?php echo json_encode($all_products); ?>;

    function openProductModal(filterKey) {
        // Determine if filter is a network or category
        const isNetwork = ['MTN', 'AIRTEL-TIGO', 'TELECEL'].includes(filterKey);
        const isCategory = ['airtime', 'exam_pin', 'data'].includes(filterKey);

        // Filter products based on selection
        let filtered = allProducts.filter(product => {
            if (isNetwork) {
                return product.network === filterKey;
            } else if (isCategory) {
                return product.category === filterKey;
            }
            return false;
        });

        // Get a display name for the modal title
        let displayName = filterKey;
        if (filterKey === 'exam_pin') displayName = 'Exam PINs';
        else if (filterKey === 'airtime') displayName = 'Airtime';

        document.getElementById('modalTitle').textContent = displayName + ' Products';

        // Build HTML for modal
        const modalBody = document.getElementById('modalProductsContainer');
        modalBody.innerHTML = '';

        if (filtered.length === 0) {
            modalBody.innerHTML = '<div class="no-products"><p>No products available for this selection.</p></div>';
            document.getElementById('productModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            return;
        }

        // Create product cards
        filtered.forEach(product => {
            const card = createProductCard(product);
            modalBody.appendChild(card);
        });

        // Show modal
        document.getElementById('productModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function createProductCard(product) {
        const div = document.createElement('div');
        div.className = 'product-card-direct';

        let productHTML = `
            <div class="product-header">
                <div class="product-network">${product.network}</div>
                <div class="product-category">${product.category.charAt(0).toUpperCase() + product.category.slice(1)}</div>
            </div>
            <div class="product-name">${product.name}</div>
        `;

        if (product.is_flexible) {
            // Flexible Data Plan
            productHTML += `
                <div class="flexible-plan">
                    <div class="price-info">
                        <span class="price-per-unit">GHS ${parseFloat(product.price_per_unit).toFixed(2)}/${product.unit}</span>
                        <span class="validity">Valid for ${product.validity_days} days</span>
                    </div>
                    <form action="checkout.php" method="POST" class="purchase-form">
                        <input type="hidden" name="product_id" value="${product.id}">
                        <div class="form-group">
                            <label for="data_amount_${product.id}">${product.unit.charAt(0).toUpperCase() + product.unit.slice(1)} Amount:</label>
                            <div class="input-with-unit">
                                <input type="number" id="data_amount_${product.id}" name="data_amount" step="0.01" min="${product.min_value}" max="${product.max_value}" value="${product.min_value}" required oninput="calculatePrice(${product.id}, ${product.price_per_unit})">
                                <span class="unit">${product.unit}</span>
                            </div>
                            <div class="range-info">
                                <small>Min: ${product.min_value}${product.unit}</small>
                                ${product.max_value ? `<small>Max: ${product.max_value}${product.unit}</small>` : ''}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="recipient_${product.id}">Recipient Phone Number:</label>
                            <input type="tel" id="recipient_${product.id}" name="recipient_number" placeholder="024XXXXXXX" pattern="[0-9]{10}" required>
                        </div>
                        <div class="price-summary">
                            <div class="price-row">
                                <span>Subtotal:</span>
                                <span id="subtotal_${product.id}">GHS 0.00</span>
                            </div>
                            <div class="price-row total">
                                <span>Total:</span>
                                <span id="total_${product.id}">GHS 0.00</span>
                            </div>
                        </div>
                        <button type="submit" class="btn-buy-now">
                            <i class='bx bx-cart'></i> Buy Now
                        </button>
                    </form>
                    ${!<?php echo json_encode(isLoggedIn()); ?> ? `
                        <a href="guest_checkout.php?product_id=${product.id}" class="btn-guest-checkout">
                            <i class='bx bx-user'></i> Guest Checkout
                        </a>
                    ` : ''}
                </div>
            `;
        } else {
            // Fixed Product (Airtime, Exam PINs)
            productHTML += `
                <div class="fixed-product">
                    <div class="product-price">GHS ${parseFloat(product.price).toFixed(2)}</div>
                    ${product.data_value && product.data_value !== 'N/A' ? `<div class="data-value">${product.data_value}</div>` : ''}
                    ${product.validity_days > 0 ? `<div class="validity">Valid for ${product.validity_days} days</div>` : ''}
                    <form action="checkout.php" method="POST" class="purchase-form">
                        <input type="hidden" name="product_id" value="${product.id}">
                        <input type="hidden" name="fixed_price" value="${product.price}">
                        ${product.category === 'airtime' ? `
                            <div class="form-group">
                                <label for="recipient_${product.id}">Recipient Phone Number:</label>
                                <input type="tel" id="recipient_${product.id}" name="recipient_number" placeholder="024XXXXXXX" pattern="[0-9]{10}" required>
                            </div>
                        ` : ''}
                        ${product.category === 'exam_pin' ? `
                            <div class="form-group">
                                <label for="exam_type_${product.id}">Exam Type:</label>
                                <select id="exam_type_${product.id}" name="exam_type">
                                    <option value="WAEC">WAEC</option>
                                    <option value="NECO">NECO</option>
                                    <option value="BECE">BECE</option>
                                </select>
                            </div>
                        ` : ''}
                        <button type="submit" class="btn-buy-now">
                            <i class='bx bx-cart'></i> Buy Now
                        </button>
                    </form>
                    ${!<?php echo json_encode(isLoggedIn()); ?> ? `
                        <a href="guest_checkout.php?product_id=${product.id}" class="btn-guest-checkout">
                            <i class='bx bx-user'></i> Guest Checkout
                        </a>
                    ` : ''}
                </div>
            `;
        }

        div.innerHTML = productHTML;
        return div;
    }

    function calculatePrice(productId, pricePerUnit) {
        const amountInput = document.getElementById('data_amount_' + productId);
        if (!amountInput) return;
        
        const amount = parseFloat(amountInput.value) || 0;
        const subtotal = amount * pricePerUnit;

        const subtotalEl = document.getElementById('subtotal_' + productId);
        const totalEl = document.getElementById('total_' + productId);
        
        if (subtotalEl) subtotalEl.textContent = 'GHS ' + subtotal.toFixed(2);
        if (totalEl) totalEl.textContent = 'GHS ' + subtotal.toFixed(2);
    }

    // Close modal when clicking outside
    document.getElementById('productModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeProductModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeProductModal();
        }
    });

    // Initialize prices on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        document.addEventListener('submit', function(event) {
            if (event.target.classList.contains('purchase-form')) {
                const amountInput = event.target.querySelector('input[name="data_amount"]');
                if (amountInput) {
                    const min = parseFloat(amountInput.getAttribute('min'));
                    const max = parseFloat(amountInput.getAttribute('max'));
                    const value = parseFloat(amountInput.value);

                    if (value < min || value > max) {
                        event.preventDefault();
                        alert('Please enter a value between ' + min + ' and ' + max + '.');
                        amountInput.focus();
                        return false;
                    }
                }
            }
        }, true);
    });
</script>

<?php require_once 'includes/footer.php'; ?>