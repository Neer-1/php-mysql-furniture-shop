<?php
require_once 'auth.php';
start_app_session();
header('Content-Type: application/json; charset=utf-8');
$userId = require_user();
require_once 'db.php';

$userQuery = $conn->prepare('SELECT username FROM users WHERE id = :id');
$userQuery->execute(['id' => $userId]);
$username = (string)$userQuery->fetchColumn();
$cartQuery = $conn->prepare(
    'SELECT cart.quantity, products.name, products.price FROM cart '
    . 'JOIN products ON cart.product_id = products.id WHERE cart.user_id = :user_id'
);
$cartQuery->execute(['user_id' => $userId]);
$totalCost = 0.0;
$numItems = 0;
$lastItem = '';
foreach ($cartQuery->fetchAll() as $row) {
    $totalCost += (int)$row['quantity'] * (float)$row['price'];
    $numItems += (int)$row['quantity'];
    $lastItem = (string)$row['name'];
}
echo json_encode(['username' => $username, 'numItems' => $numItems, 'lastItem' => $lastItem, 'totalCost' => $totalCost]);
