<?php
include 'includes/config.php';
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_query = "SELECT c.*, p.name, p.price, p.stock 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_query);

if ($cart_result->num_rows == 0) {
    header("Location: cart.php");
    exit();
}

// Calculate total
$total = 0;
while($item = $cart_result->fetch_assoc()) {
    $total += $item['price'] * $item['quantity'];
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create order (VULNERABLE: SQL Injection)
    $order_total = $total + 10 + ($total * 0.08); // Shipping + tax
    $order_query = "INSERT INTO orders (user_id, total) VALUES ($user_id, $order_total)";
    
    if ($conn->query($order_query)) {
        $order_id = $conn->insert_id;
        
        // Add order items (VULNERABLE: SQL Injection)
        $cart_result = $conn->query($cart_query); // Re-fetch cart items
        while($item = $cart_result->fetch_assoc()) {
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                           VALUES ($order_id, " . $item['product_id'] . ", " . $item['quantity'] . ", " . $item['price'] . ")";
            $conn->query($item_query);
            
            // Update product stock (VULNERABLE: SQL Injection)
            $update_stock = "UPDATE products SET stock = stock - " . $item['quantity'] . " WHERE id = " . $item['product_id'];
            $conn->query($update_stock);
        }
        
        // Clear cart (VULNERABLE: SQL Injection)
        $clear_cart = "DELETE FROM cart WHERE user_id = $user_id";
        $conn->query($clear_cart);
        
        $success = "Order placed successfully! Your order ID is #$order_id";
        $order_complete = true;
    } else {
        $error = "Error placing order: " . $conn->error;
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <h2>Checkout</h2>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-primary-custom">Continue Shopping</a>
        </div>
        <?php else: ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $cart_result = $conn->query($cart_query); // Re-fetch cart items
                        while($item = $cart_result->fetch_assoc()): 
                            $item_total = $item['price'] * $item['quantity'];
                        ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo $item['price']; ?></td>
                            <td>$<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>$10.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (8%):</span>
                            <span>$<?php echo number_format($total * 0.08, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($total + 10 + ($total * 0.08), 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Shipping Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                        <div class="col-md-2">
                            <label for="zip" class="form-label">ZIP</label>
                            <input type="text" class="form-control" id="zip" name="zip" required>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="exp_date" class="form-label">Expiration Date</label>
                                    <input type="text" class="form-control" id="exp_date" name="exp_date" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3 mt-3">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the terms and conditions
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 btn-lg">Place Order</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>