<?php
// Start output buffering
ob_start();

include 'includes/config.php';

// VULNERABLE: Direct use of GET parameter without sanitization
$product_id = isset($_GET['id']) ? $_GET['id'] : 0;

// VULNERABLE: SQL Injection - no prepared statements or escaping
$query = "SELECT * FROM products WHERE id = " . $product_id;
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    ob_end_clean();
    include 'includes/header.php';
    echo "<div class='alert alert-danger'>Product not found!</div>";
    include 'includes/footer.php';
    exit();
}

// Process add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        ob_end_clean();
        header("Location: login.php");
        exit();
    }
    
    // VULNERABLE: Direct use of POST parameters
    $quantity = $_POST['quantity'];
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    // Debug: Check what values we're getting
    error_log("Add to Cart - User: $user_id, Product: $product_id, Quantity: $quantity");
    
    // VULNERABLE: SQL Injection in all queries
    $check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // VULNERABLE: Update query with no sanitization
        $update_query = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
        if ($conn->query($update_query)) {
            $_SESSION['success'] = "Product quantity updated in cart!";
        } else {
            $_SESSION['error'] = "Error updating cart: " . $conn->error;
        }
    } else {
        // VULNERABLE: Insert query with no sanitization
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        if ($conn->query($insert_query)) {
            $_SESSION['success'] = "Product added to cart successfully!";
        } else {
            $_SESSION['error'] = "Error adding to cart: " . $conn->error;
        }
    }
    
    ob_end_clean();
    header("Location: cart.php");
    exit();
}

include 'includes/header.php';

// Display messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="row">
    <div class="col-md-6">
        <img src="assets/images/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" 
             class="img-fluid rounded" alt="<?php echo $product['name']; ?>" style="height: 300px; object-fit: cover;">
    </div>
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="text-muted">Category: <?php echo htmlspecialchars($product['category']); ?></p>
        <h3 class="text-primary">$<?php echo number_format($product['price'], 2); ?></h3>
        
        <div class="mb-4">
            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        
        <div class="mb-4">
            <p><strong>Availability:</strong> 
                <?php if ($product['stock'] > 0): ?>
                <span class="text-success">In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php else: ?>
                <span class="text-danger">Out of Stock</span>
                <?php endif; ?>
            </p>
        </div>
        
        <?php if ($product['stock'] > 0): ?>
        <form method="POST" action="product-details.php?id=<?php echo $product['id']; ?>">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="quantity" class="form-label">Quantity:</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" 
                           value="1" min="1" max="<?php echo $product['stock']; ?>">
                </div>
            </div>
            
            <?php if (isLoggedIn()): ?>
            <button type="submit" name="add_to_cart" class="btn btn-primary-custom btn-lg">
                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
            </button>
            <?php else: ?>
            <a href="login.php" class="btn btn-primary-custom btn-lg">Login to Purchase</a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>