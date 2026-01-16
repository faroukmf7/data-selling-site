<?php
// api/add_to_cart.php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.']);
        exit();
    }

    $product_id = (int)$_POST['product_id'];
    $recipient_number = isset($_POST['recipient_number']) ? sanitize($_POST['recipient_number']) : '';

    // Validate product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit();
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update item in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$product_id] = [
            'quantity' => 1,
            'recipient' => $recipient_number
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart successfully!',
        'cart_count' => count($_SESSION['cart'])
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
