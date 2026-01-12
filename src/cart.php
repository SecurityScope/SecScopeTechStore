<?php
// Start output buffering at the very beginning
ob_start();

include 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Clear buffer before redirect
    ob_end_clean();
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Process remove item from cart
if (isset($_GET['remove_item'])) {
    $cart_id = $_GET['remove_item'];
    
    // Remove item from cart (VULNERABLE: SQL Injection)
    $delete_query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
    if ($conn->query($delete_query)) {
        $_SESSION['success'] = "Item removed from cart successfully!";
    } else {
        $_SESSION['error'] = "Error removing item: " . $conn->error;
    }
    
    // Clear buffer before redirect
    ob_end_clean();
    header("Location: cart.php");
    exit();
}

// Process cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    // Update cart quantities (VULNERABLE: SQL Injection)
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        if ($quantity <= 0) {
            $delete_query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
            $conn->query($delete_query);
        } else {
            $update_query = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id";
            $conn->query($update_query);
        }
    }
    
    $_SESSION['success'] = "Cart updated successfully!";
    
    // Clear buffer before redirect
    ob_end_clean();
    header("Location: cart.php");
    exit();
}

// If we reach here, we're displaying the page, so include header
include 'includes/header.php';

// Display messages
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch cart items (VULNERABLE: SQL Injection)
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_query);

$total = 0;
?>

<div class="row">
    <div class="col-md-8">
        <h2>Your Shopping Cart</h2>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($cart_result->num_rows > 0): ?>
        <form method="POST" action="">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $cart_result->fetch_assoc()): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                     alt="<?php echo $item['name']; ?>" width="60" class="me-3">
                                <div>
                                    <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                    <small class="text-muted">Stock: <?php echo $item['stock']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>$<?php echo $item['price']; ?></td>
                        <td>
                            <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="<?php echo $item['stock']; ?>" class="form-control" style="width: 80px;">
                        </td>
                        <td>$<?php echo number_format($item_total, 2); ?></td>
                        <td>
                            <a href="cart.php?remove_item=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to remove this item from your cart?')">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="d-flex justify-content-between">
                <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
                <button type="submit" name="update_quantity" class="btn btn-primary-custom">Update Cart</button>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-info">
            <h4>Your cart is empty</h4>
            <p>Browse our <a href="products.php">products</a> and add items to your cart.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span>$10.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($total * 0.08, 2); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong>$<?php echo number_format($total + 10 + ($total * 0.08), 2); ?></strong>
                </div>
                
                <?php if ($cart_result->num_rows > 0): ?>
                <a href="checkout.php" class="btn btn-primary-custom w-100">Proceed to Checkout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>