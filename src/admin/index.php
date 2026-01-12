<?php
include '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// CSRF vulnerable admin actions
if (isset($_GET['delete_user'])) {
    // VULNERABLE: No CSRF protection
    $user_id = $_GET['delete_user'];
    $delete_query = "DELETE FROM users WHERE id = " . $user_id;
    if ($conn->query($delete_query)) {
        $message = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . $conn->error;
    }
}

// Get statistics
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

// Get recent orders
$orders_query = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5";
$orders_result = $conn->query($orders_query);

// Get recent users
$users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SecScope Tech Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
        }
        
        .sidebar .nav-link:hover {
            color: #ffffff;
        }
        
        .sidebar .nav-link.active {
            color: #FFD700;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <a href="../index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <i class="fas fa-laptop-code me-2 text-warning"></i>
                        <span class="fs-4">SecScope Admin</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="products.php" class="nav-link">
                                <i class="fas fa-box me-2"></i>
                                Products
                            </a>
                        </li>
                        <li>
                            <a href="users.php" class="nav-link">
                                <i class="fas fa-users me-2"></i>
                                Users
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="nav-link">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Orders
                            </a>
                        </li>
                        <li>
                            <a href="messages.php" class="nav-link">
                                <i class="fas fa-envelope me-2"></i>
                                Messages
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <strong><?php echo $_SESSION['username']; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Users</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $users_count; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Products</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $products_count; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Orders</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $orders_count; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($orders_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($order = $orders_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo $order['username']; ?></td>
                                                <td>$<?php echo $order['total']; ?></td>
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
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center">No orders found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users with CSRF vulnerability -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Users</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($users_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($user = $users_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo $user['username']; ?></td>
                                                <td><?php echo $user['email']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'secondary'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="?delete_user=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center">No users found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>