<?php
require_once 'auth.php';
start_app_session();
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}
require_valid_csrf();
$userId = require_user();
require_once 'db.php';
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

try {
    $conn->beginTransaction();
    $productLock = $conn->prepare('SELECT id FROM products WHERE id = :id FOR UPDATE');
    $productLock->execute(['id' => $productId]);
    $stmt = $conn->prepare('SELECT quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id FOR UPDATE');
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    $quantity = $stmt->fetchColumn();
    if ($quantity === false) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit();
    }
    $remaining = max(0, (int)$quantity - 1);
    if ($remaining === 0) {
        $change = $conn->prepare('DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id');
        $change->execute(['user_id' => $userId, 'product_id' => $productId]);
    } else {
        $change = $conn->prepare('UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id');
        $change->execute(['quantity' => $remaining, 'user_id' => $userId, 'product_id' => $productId]);
    }
    $conn->commit();
    echo json_encode(['success' => true, 'action' => $remaining ? 'decrement' : 'remove', 'quantity' => $remaining]);
} catch (PDOException $error) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log('Cart decrement failed.');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Cart update failed']);
}
