<?php
include 'includes/config.php';
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = $conn->query($orders_query);

// Process order cancellation (VULNERABLE: CSRF - No token validation)
if (isset($_GET['cancel_order'])) {
    $order_id = $_GET['cancel_order'];
    
    // Check if order belongs to user
    $check_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $cancel_query = "UPDATE orders SET status = 'cancelled' WHERE id = $order_id";
        if ($conn->query($cancel_query)) {
            $success = "Order #$order_id has been cancelled successfully!";
        } else {
            $error = "Error cancelling order: " . $conn->error;
        }
    } else {
        $error = "Order not found or you don't have permission to cancel it!";
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2>Your Orders</h2>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($orders_result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                        <td>
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
                        </td>
                        <td>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <?php if ($order['status'] == 'pending' || $order['status'] == 'processing'): ?>
                            <a href="orders.php?cancel_order=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Are you sure you want to cancel order #<?php echo $order['id']; ?>?')">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <h4>You haven't placed any orders yet</h4>
            <p>Browse our <a href="products.php">products</a> and make your first purchase!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>