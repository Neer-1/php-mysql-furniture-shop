<?php
require_once 'auth.php';
require_once 'input.php';
require_admin();
require_once 'db.php';
$categories = ['fullrooms', 'livingroom', 'kitchen', 'diningroom', 'bedroom', 'office', 'bathroom'];

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

    $stmt = $conn->prepare('INSERT INTO products (name, description, price, quantity, image, category) VALUES (:name, :description, :price, :quantity, :image, :category)');
    $stmt->execute(['name' => $name, 'description' => $description, 'price' => $price, 'quantity' => $quantity, 'image' => $image ?: null, 'category' => $category]);
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <h1>Add New Product</h1>
    <a href="admin.php">Back to Product List</a>
</nav>

<section class="form-section">
    <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>
        
        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea>
        
        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" required>
        
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" required>
        
        <label for="image">Image Path:</label>
        <input type="text" name="image" id="image" required>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <?php foreach ($categories as $categoryOption): ?>
                <option value="<?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoryOption, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">Add Product</button>
    </form>
</section>

</body>
</html>
