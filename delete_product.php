<?php
require_once 'auth.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') app_fail(405, 'Method not allowed.');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        http_response_code(400);
        exit('Invalid product id.');
    }

    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: admin.php"); // Redirect to the product list after deletion
    exit();
}
