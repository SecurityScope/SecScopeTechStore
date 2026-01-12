<?php
session_start();
include 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="text-center">Your Profile</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="password-reset.php" class="btn btn-primary-custom">Change Password</a>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="card">
            <div class="card-header">
                <h5>Order History</h5>
            </div>
            <div class="card-body">
                <?php
                $orders_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($orders_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $orders_result = $stmt->get_result();

                if ($orders_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
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
                                                default: echo 'secondary';
                                            }
                                        ?>"><?php echo ucfirst($order['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">You haven't placed any orders yet.</p>
                <?php endif; ?>
                <?php $stmt->close(); ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
