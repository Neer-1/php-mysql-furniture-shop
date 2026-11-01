<?php
require_once 'auth.php';
require_admin();
require_once 'db.php';

// Pagination settings
$itemsPerPage = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 214748364]]) ?: 1;
$offset = ($page - 1) * $itemsPerPage;

// Fetch products
$sql = "SELECT * FROM products ORDER BY id LIMIT :offset, :itemsPerPage";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total products for pagination
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPages = ceil($totalProducts / $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <h1>Admin Panel</h1>
    <a href="add_product.php">Add New Product</a>
</nav>

<section class="product-list">
    <h2>Product List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo (int)$product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)$product['quantity']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>">Edit</a>
                        <form action="delete_product.php" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</section>

</body>
</html>

<?php
$stmt->closeCursor();
$conn = null; // Close the database connection
?>
