<?php
include 'includes/config.php';
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch order details (VULNERABLE: SQL Injection)
$order_query = "SELECT o.*, u.username 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = $conn->query($order_query);

if ($order_result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Order not found or you don't have permission to view it!</div>";
    include 'includes/footer.php';
    exit();
}

$order = $order_result->fetch_assoc();

// Fetch order items (VULNERABLE: SQL Injection)
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_query);
?>

<div class="row">
    <div class="col-md-8">
        <h2>Order Details #<?php echo $order['id']; ?></h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php 
                                switch($order['status']) {
                                    case 'pending': echo 'warning'; break;
                                    case 'processing': echo 'info'; break;
                                    case 'shipped': echo 'primary'; break;
                                    case 'delivered': echo 'success'; break;
                                    case 'cancelled': echo 'danger'; break;
                                    default: echo 'secondary';
                                }
                            ?>"><?php echo ucfirst($order['status']); ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Order Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
                        <p><strong>Customer:</strong> <?php echo $order['username']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Order Items</h5>
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
                        $subtotal = 0;
                        while($item = $items_result->fetch_assoc()): 
                            $item_total = $item['price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/products/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" 
                                         alt="<?php echo $item['name']; ?>" width="50" class="me-3">
                                    <div><?php echo $item['name']; ?></div>
                                </div>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                            <td><strong>$10.00</strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                            <td><strong>$<?php echo number_format($subtotal * 0.08, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Order Actions</h5>
            </div>
            <div class="card-body">
                <?php if ($order['status'] == 'pending' || $order['status'] == 'processing'): ?>
                <a href="orders.php?cancel_order=<?php echo $order['id']; ?>" class="btn btn-danger w-100 mb-2" 
                   onclick="return confirm('Are you sure you want to cancel this order?')">
                    <i class="fas fa-times"></i> Cancel Order
                </a>
                <?php endif; ?>
                
                <a href="products.php" class="btn btn-primary-custom w-100">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>