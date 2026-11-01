<?php
require_once 'auth.php';
require_once 'input.php';
$user_id = require_user();
require_once 'db.php';

// Check if the user is logged in
// Fetch user details from the database
$sql_user = "SELECT id, first_name, last_name, username, email, phone FROM users WHERE id = :user_id";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);
if (!$user) app_fail(401, 'Sign in is required.');

// Fetch items from the user's cart
$sql_cart = "SELECT products.id, products.name, products.description, products.price, products.image, cart.quantity 
             FROM cart 
             JOIN products ON cart.product_id = products.id 
             WHERE cart.user_id = :user_id";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_cart->execute();
$result_cart = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css"> 
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
                if (basename($_SERVER['PHP_SELF']) == 'cart.php') {
                    echo '<li><form action="logout.php" method="POST"><input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '"><button type="submit">Logout</button></form></li>';
                } else {
                    // If logged in and not on cart.php, show Cart link
                    echo '<li><a href="cart.php">Cart</a></li>';
                }
            } else {
                // If not logged in, show Register link
                echo '<li><a href="register.html">Register</a></li>';
            }
            ?>
        </ul>
    </div>
    <button type="button" onclick="showMenu()" aria-label="Open navigation">Menu</button>
</nav>


<!-- User Information Section -->
<section class="user-details">
    <h2>Your Details</h2>
    <div class="user-info">
        <div class="user-item">
            <strong>ID:</strong> <span><?php echo (int)$user['id']; ?></span>
        </div>
        <div class="user-item">
            <strong>First Name:</strong> <span><?php echo htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="user-item">
            <strong>Last Name:</strong> <span><?php echo htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="user-item">
            <strong>Username:</strong> <span><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="user-item">
            <strong>Email:</strong> <span><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="user-item">
            <strong>Phone:</strong> <span><?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
</section>


<!-- Cart Items Section -->


<!-- Cart Items Section -->
<section class="cart-items">
    <h2>Your Cart</h2>

    <?php if (count($result_cart) > 0): ?>
        <div class="product-grid">
            <?php foreach ($result_cart as $item): ?>
                <div class="cart-product" id="cart-product-<?php echo (int)$item['id']; ?>">
                    <img src="<?php echo htmlspecialchars(safe_product_image($item['image']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <p class="product-price">Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <p class="product-quantity">Quantity: <span class="quantity-value"><?php echo htmlspecialchars($item['quantity']); ?></span></p>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart(<?php echo (int)$item['id']; ?>)">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</section>



<script src="script.js"></script>
</body>
</html>

<?php
$stmt_user->closeCursor();
$stmt_cart->closeCursor();
$conn = null; // Close the database connection
?>
