<?php
require_once 'auth.php';
start_app_session();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}
require_valid_csrf();
$userId = require_user();
require_once 'db.php';
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
    exit();
}

try {
    $conn->beginTransaction();
    $productQuery = $conn->prepare('SELECT quantity FROM products WHERE id = :id FOR UPDATE');
    $productQuery->execute(['id' => $productId]);
    $product = $productQuery->fetch();
    if (!$product) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        exit();
    }

    $cartQuery = $conn->prepare('SELECT quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id FOR UPDATE');
    $cartQuery->execute(['user_id' => $userId, 'product_id' => $productId]);
    $cartItem = $cartQuery->fetch();
    $newQuantity = (int)($cartItem['quantity'] ?? 0) + 1;
    if ($newQuantity > (int)$product['quantity']) {
        $conn->rollBack();
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Requested quantity exceeds available stock']);
        exit();
    }

    if ($cartItem) {
        $update = $conn->prepare('UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id');
        $update->execute(['quantity' => $newQuantity, 'user_id' => $userId, 'product_id' => $productId]);
    } else {
        $insert = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)');
        $insert->execute(['user_id' => $userId, 'product_id' => $productId]);
    }
    $conn->commit();
    echo json_encode(['status' => 'success']);
} catch (PDOException $error) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Cart update failed.');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Cart update failed']);
}
