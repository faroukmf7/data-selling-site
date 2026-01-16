<?php
// index.php
$page_title = "Home";
require_once 'includes/header.php';
?>

<div class="content">
    <div class="title-section">
        <div class="title-main">Affordable Data Solutions</div>
        <div class="title-des">FastData provides high-speed data services at competitive prices.</div>

        <?php if (isLoggedIn()): ?>
            <div class="user-welcome">
                Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>!
                Your balance: <span class="balance"><?php echo formatCurrency(getUserBalance($pdo, $_SESSION['user_id'])); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- In index.php, replace the cards-holder section with: -->
    <div class="content">
        <div class="title-section">
            <div class="title-main">Buy Data & Airtime Instantly</div>
            <div class="title-des">FastData provides high-speed data and airtime at competitive prices.</div>

            <?php if (isLoggedIn()): ?>
                <div class="user-welcome">
                    Welcome back, <strong><?php echo $_SESSION['username']; ?></strong>!
                    Your balance: <span class="balance"><?php echo formatCurrency(getUserBalance($pdo, $_SESSION['user_id'])); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Purchase Section -->
        <div class="quick-purchase">
            <h2>Quick Purchase</h2>
            <div class="quick-grid">
                <a href="products.php?network=MTN&category=data" class="quick-item">
                    <img src="images/MTN.png" alt="MTN">
                    <span>MTN Data</span>
                    <small>From GHS 0.50/GB</small>
                </a>

                <a href="products.php?network=AIRTEL-TIGO&category=data" class="quick-item">
                    <img src="images/airteltigo.png" alt="Airtel-Tigo">
                    <span>Airtel-Tigo Data</span>
                    <small>From GHS 0.45/GB</small>
                </a>

                <a href="products.php?network=TELECEL&category=data" class="quick-item">
                    <img src="images/telecel.png" alt="Telecel">
                    <span>Telecel Data</span>
                    <small>From GHS 0.55/GB</small>
                </a>

                <a href="products.php?category=airtime" class="quick-item">
                    <i class='bx bx-phone'></i>
                    <span>Airtime</span>
                    <small>All networks</small>
                </a>

                <a href="products.php?category=exam_pin" class="quick-item">
                    <i class='bx bx-book'></i>
                    <span>Exam PINs</span>
                    <small>WAEC, NECO, BECE</small>
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="featured-section">
        <h2>Popular Data Plans</h2>
        <div class="products-grid">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 AND category = 'data' ORDER BY price ASC LIMIT 4");
                $products = $stmt->fetchAll();

                foreach ($products as $product):
            ?>
                    <div class="product-card">
                        <div class="product-network"><?php echo htmlspecialchars($product['network']); ?></div>
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-data"><?php echo htmlspecialchars($product['data_value']); ?></div>
                        <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                        <div class="product-validity">Valid for <?php echo intval($product['validity_days']); ?> days</div>
                        <form action="api/add_to_cart.php" method="POST" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo intval($product['id']); ?>">
                            <button type="submit" class="add-to-cart-btn" <?php echo isLoggedIn() ? '' : 'disabled'; ?>>Add to Cart</button>
                        </form>
                    </div>
            <?php endforeach;
            } catch (PDOException $e) {
                echo '<p>Unable to load products.</p>';
            } ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>