<?php
require_once 'auth.php';
start_app_session();
require_once 'db.php';
if (isset($_SESSION['user_id'])) require_user();


$username = "Guest"; // Default username
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $username = $stmt->fetchColumn();
}

// Fetch cart details
$totalCost = $numItems = 0;
$lastItem = "";

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT cart.quantity, products.name, products.price 
                            FROM cart 
                            JOIN products ON cart.product_id = products.id 
                            WHERE cart.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $totalCost += $row['quantity'] * $row['price'];
        $lastItem = $row['name'];
        $numItems += $row['quantity'];
    }
}

// Handle search request
$searchTerm = "";
if (isset($_GET['search']) && !is_string($_GET['search'])) app_fail(422, 'Invalid search.');
if (isset($_GET['search']) && is_string($_GET['search'])) {
    if (strlen($_GET['search']) > 100 || str_contains($_GET['search'], "\0") || preg_match('//u', $_GET['search']) !== 1) app_fail(422, 'Invalid search.');
    $searchTerm = trim($_GET['search']);
}

function render_product_card(array $product): string
{
    $productId = (int)$product['id'];
    $name = htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars((string)$product['description'], ENT_QUOTES, 'UTF-8');
    $candidateImage = (string)($product['image'] ?? '');
    $imagePath = preg_match('#^images/[A-Za-z0-9_.-]+$#', $candidateImage) === 1
        && !str_contains($candidateImage, '..')
        && is_file(__DIR__ . DIRECTORY_SEPARATOR . $candidateImage)
        ? $candidateImage
        : 'images/image.png';
    $image = htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8');
    $price = number_format((float)$product['price'], 2, '.', '');
    $quantity = (int)$product['quantity'];

    return "<div class='product'><img src='{$image}' alt='{$name}'><div class='description'>"
        . "<h3>{$name}</h3><p>{$description}</p><p>Price: \${$price}</p>"
        . "<p>Available Quantity: {$quantity}</p><button onclick='addToCart({$productId})'>Add to Cart</button></div></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Chisel</title>
<link rel="icon" href="images/brand-icon.png" sizes="500x500">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="style.css">
<script src="script.js" defer></script>
</head>
<body>
<nav>
    <a href="index.html"><img src="images/brand-mark.png"></a>
    <div class="nav-links" id="navLinks">
        <button type="button" onclick="hideMenu()" aria-label="Close navigation">Close</button>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="index.html#about">About Shop</a></li>
            <li><a href="index.html#facilities">Designs</a></li>
            <li><a href="index.html#gallery">Catalog</a></li>
            <li><a href="index.html#testimonials">Reviews</a></li>
            <li><a href="index.html#contactus">Contact</a></li>
            <?php
            if (isset($_SESSION['user_id'])) { // Check if the user is logged in
                echo '<li><a href="cart.php">Cart</a></li>';
            } else {
                echo '<li><a href="register.html">Register</a></li>';
            }
            ?>
        </ul>
    </div>
    <button type="button" onclick="showMenu()" aria-label="Open navigation">Menu</button>
</nav>

<div class="shop-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <form method="GET" action="shop.php">
            <input type="text" id="searchBar" name="search" placeholder="Search products.." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
        <p id="searchResult" role="status" aria-live="polite"></p>
        <ul id="sidebarLinks">
            <li><a href="#fullrooms">Full Rooms</a></li>
            <li><a href="#livingroom">Living Room</a></li>
            <li><a href="#kitchen">Kitchen</a></li>
            <li><a href="#diningroom">Dining Room</a></li>
            <li><a href="#bedroom">Bedroom</a></li>
            <li><a href="#office">Office</a></li>
            <li><a href="#bathroom">Bathroom</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php
        if ($searchTerm !== '') {
            // Search products based on user input
            $stmt = $conn->prepare("SELECT * FROM products WHERE category LIKE :category_search OR name LIKE :name_search");
            $stmt->execute(['category_search' => "%$searchTerm%", 'name_search' => "%$searchTerm%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                echo "<h2>Search Results for '" . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . "':</h2>";
                foreach ($results as $row) {
                    echo render_product_card($row);
                }
            } else {
                echo "<p>No products found for '" . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . "'.</p>";
            }
        } else {
            // Show default categories and products if no search term is entered
            $categories = [
                'fullrooms' => 'Full Rooms', 'livingroom' => 'Living Room', 
                'kitchen' => 'Kitchen', 'diningroom' => 'Dining Room', 
                'bedroom' => 'Bedroom', 'office' => 'Office', 'bathroom' => 'Bathroom'
            ];

            foreach ($categories as $category => $heading) {
                echo "<section id='$category'><h2>$heading</h2>";
                $stmt = $conn->prepare("SELECT * FROM products WHERE category = :category");
                $stmt->execute(['category' => $category]);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($products) {
                    foreach ($products as $row) {
                        echo render_product_card($row);
                    }
                } else {
                    echo "<p>No products found in this category.</p>";
                }
                echo "</section>";
            }
        }
        ?>
    </div>

    <!-- Wizard (Cart Summary) -->
    <div class="wizard">
        <h3>Cart Summary</h3>
        <p>Username: <?php echo htmlspecialchars($username); ?></p>
        <p>Items in Cart: <?php echo htmlspecialchars($numItems); ?></p>
        <p>Last Listed Item: <?php echo htmlspecialchars($lastItem); ?></p>
        <p>Total Cost: $<?php echo number_format($totalCost, 2); ?></p>

        <div style="margin-top: 20px;">
    <button class="back-button" onclick="window.location.href='cart.php'">Go to Cart</button>
    </div>
    </div> 


</div>
</body>
</html>

<?php
$conn = null; // Close the database connection
?>
