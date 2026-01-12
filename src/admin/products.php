<?php
include '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// CSRF vulnerable admin actions
if (isset($_GET['delete_product'])) {
    // VULNERABLE: No CSRF protection
    $product_id = $_GET['delete_product'];
    $delete_query = "DELETE FROM products WHERE id = " . $product_id;
    if ($conn->query($delete_query)) {
        $message = "Product deleted successfully!";
    } else {
        $error = "Error deleting product: " . $conn->error;
    }
}

// Process product form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    
    // VULNERABLE: SQL Injection and no CSRF protection
    if (isset($_POST['edit_product'])) {
        $product_id = $_POST['product_id'];
        $update_query = "UPDATE products SET name='$name', description='$description', price=$price, category='$category', stock=$stock WHERE id=$product_id";
        
        if ($conn->query($update_query)) {
            $message = "Product updated successfully!";
        } else {
            $error = "Error updating product: " . $conn->error;
        }
    } else {
        $insert_query = "INSERT INTO products (name, description, price, category, stock) 
                         VALUES ('$name', '$description', $price, '$category', $stock)";
        
        if ($conn->query($insert_query)) {
            $message = "Product added successfully!";
        } else {
            $error = "Error adding product: " . $conn->error;
        }
    }
}

// Fetch products
$products_query = "SELECT * FROM products ORDER BY id DESC";
$products_result = $conn->query($products_query);

// Fetch product for editing
$edit_product = null;
if (isset($_GET['edit_product'])) {
    $product_id = $_GET['edit_product'];
    $edit_query = "SELECT * FROM products WHERE id = $product_id";
    $edit_result = $conn->query($edit_query);
    $edit_product = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - SecScope Tech Store</title>
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
                            <a href="index.php" class="nav-link">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="products.php" class="nav-link active">
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
                    <h1 class="h2">Product Management</h1>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Add/Edit Product Form -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Product Name</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo $edit_product ? $edit_product['name'] : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="Laptops" <?php echo ($edit_product && $edit_product['category'] == 'Laptops') ? 'selected' : ''; ?>>Laptops</option>
                                                <option value="Smartphones" <?php echo ($edit_product && $edit_product['category'] == 'Smartphones') ? 'selected' : ''; ?>>Smartphones</option>
                                                <option value="Tablets" <?php echo ($edit_product && $edit_product['category'] == 'Tablets') ? 'selected' : ''; ?>>Tablets</option>
                                                <option value="Accessories" <?php echo ($edit_product && $edit_product['category'] == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                                                <option value="Smart Devices" <?php echo ($edit_product && $edit_product['category'] == 'Smart Devices') ? 'selected' : ''; ?>>Smart Devices</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $edit_product ? $edit_product['description'] : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="price" class="form-label">Price</label>
                                            <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                                   value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="stock" class="form-label">Stock</label>
                                            <input type="number" class="form-control" id="stock" name="stock" 
                                                   value="<?php echo $edit_product ? $edit_product['stock'] : ''; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <?php if ($edit_product): ?>
                                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                                    <button type="submit" name="edit_product" class="btn btn-primary">Update Product</button>
                                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                                    <?php else: ?>
                                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>All Products</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($products_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($product = $products_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td><?php echo $product['name']; ?></td>
                                                <td><?php echo $product['category']; ?></td>
                                                <td>$<?php echo $product['price']; ?></td>
                                                <td><?php echo $product['stock']; ?></td>
                                                <td>
                                                    <a href="?edit_product=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="?delete_product=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center">No products found.</p>
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