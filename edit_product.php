<?php
require_once 'auth.php';
require_once 'input.php';
require_admin();
require_once 'db.php';
$categories = ['fullrooms', 'livingroom', 'kitchen', 'diningroom', 'bedroom', 'office', 'bathroom'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('Invalid product id.');
}
$stmt = $conn->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $name = trim(post_text('name', 100));
    $description = trim(post_text('description', 65535));
    $price = product_price(post_text('price', 32));
    $quantity = filter_var(post_text('quantity', 10), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
    $image = trim(post_text('image', 255));
    $category = post_text('category', 20);
    $validImage = $image === '' || (preg_match('#^images/[A-Za-z0-9_.-]+$#', $image) === 1 && !str_contains($image, '..') && is_file(__DIR__ . '/' . $image));

    if ($name === '' || strlen($name) > 100 || $description === '' || $price === null
        || $quantity === false || !in_array($category, $categories, true) || !$validImage) {
        http_response_code(422);
        exit('Invalid product details.');
    }

    $update = $conn->prepare('UPDATE products SET name = :name, description = :description, price = :price, quantity = :quantity, image = :image, category = :category WHERE id = :id');
    $update->execute(['name' => $name, 'description' => $description, 'price' => $price, 'quantity' => $quantity, 'image' => $image ?: null, 'category' => $category, 'id' => $id]);
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <h1>Edit Product</h1>
    <a href="admin.php">Back to Product List</a>
</nav>

<section class="form-section">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        
        <label for="description">Description:</label>
        <textarea name="description" id="description" required><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        
        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>" required>
        
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" min="0" step="1" value="<?php echo (int)$product['quantity']; ?>" required>
        
        <label for="image">Image Path:</label>
        <input type="text" name="image" id="image" value="<?php echo htmlspecialchars($product['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <?php foreach ($categories as $categoryOption): ?>
                <option value="<?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $product['category'] === $categoryOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">Update Product</button>
    </form>
</section>

</body>
</html>
