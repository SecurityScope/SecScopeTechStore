<?php
include '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// CSRF vulnerable admin actions
if (isset($_GET['update_status'])) {
    // VULNERABLE: No CSRF protection
    $order_id = $_GET['order_id'];
    $new_status = $_GET['new_status'];
    
    $update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
    if ($conn->query($update_query)) {
        $message = "Order status updated successfully!";
    } else {
        $error = "Error updating order status: " . $conn->error;
    }
}

if (isset($_GET['delete_order'])) {
    // VULNERABLE: No CSRF protection
    $order_id = $_GET['delete_order'];
    
    // First delete order items (VULNERABLE: SQL Injection)
    $delete_items_query = "DELETE FROM order_items WHERE order_id = $order_id";
    $conn->query($delete_items_query);
    
    // Then delete the order (VULNERABLE: SQL Injection)
    $delete_order_query = "DELETE FROM orders WHERE id = $order_id";
    if ($conn->query($delete_order_query)) {
        $message = "Order deleted successfully!";
    } else {
        $error = "Error deleting order: " . $conn->error;
    }
}

// Fetch orders with user information
$orders_query = "SELECT o.*, u.username, u.email 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY o.created_at DESC";
$orders_result = $conn->query($orders_query);

// Get statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total) as total FROM orders WHERE status = 'delivered'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - SecScope Tech Store</title>
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
        
        .stats-card {
            border-left: 4px solid #FFD700;
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
                            <a href="index.php" class="nav-link">
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
                            <a href="orders.php" class="nav-link active">
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
                    <h1 class="h2">Order Management</h1>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pending Orders</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Completed Orders</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completed_orders; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Revenue</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_revenue, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>All Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($orders_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Email</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($order = $orders_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo $order['username']; ?></td>
                                                <td><?php echo $order['email']; ?></td>
                                                <td>$<?php echo number_format($order['total'], 2); ?></td>
                                                <td>
                                                    <form class="d-inline">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                        <input type="hidden" name="update_status" value="1">
                                                    </form>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <a href="../order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="?delete_order=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </td>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced order management functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add confirmation for status changes
            const statusSelects = document.querySelectorAll('select[name="new_status"]');
            statusSelects.forEach(select => {
                select.addEventListener('change', function(e) {
                    const orderId = this.previousElementSibling.value;
                    const newStatus = this.value;
                    
                    if (!confirm(`Are you sure you want to change order #${orderId} status to ${newStatus}?`)) {
                        e.preventDefault();
                        this.blur();
                    }
                });
            });
            
            // Add filter functionality
            const filterForm = document.createElement('div');
            filterForm.className = 'card mb-3';
            filterForm.innerHTML = `
                <div class="card-header">
                    <h6>Filter Orders</h6>
                </div>
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" onchange="filterOrders()">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" onchange="filterOrders()">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Min Total</label>
                            <input type="number" class="form-control" placeholder="$0.00" onchange="filterOrders()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Total</label>
                            <input type="number" class="form-control" placeholder="$1000.00" onchange="filterOrders()">
                        </div>
                    </form>
                </div>
            `;
            
            document.querySelector('.card-header').after(filterForm);
        });
        
        function filterOrders() {
            // Simple client-side filtering for demonstration
            const statusFilter = document.querySelector('select').value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const status = row.querySelector('select').value;
                const total = parseFloat(row.cells[3].textContent.replace('$', ''));
                const shouldShow = (!statusFilter || status === statusFilter);
                
                row.style.display = shouldShow ? '' : 'none';
            });
        }
    </script>
</body>
</html>