<?php
// NO whitespace before this line!
// NO content before this line!

// Start output buffering at the very beginning
if (!ob_get_status()) {
    ob_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecScope Tech Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FFD700;
            --secondary: #000000;
            --light: #FFFFFF;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .bg-primary-custom {
            background-color: var(--primary) !important;
        }
        
        .text-primary-custom {
            color: var(--primary) !important;
        }
        
        .btn-primary-custom {
            background-color: var(--primary);
            color: var(--secondary);
            border: none;
        }
        
        .btn-primary-custom:hover {
            background-color: #e6c200;
            color: var(--secondary);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 30px;
        }
        
        .product-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="../assets/images/secscopecorp-logo.png" width=200px alt="" srcset="">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact</a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">Admin</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <form class="d-flex me-2" action="products.php" method="GET">
                        <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
                        <button class="btn btn-outline-warning" type="submit">Search</button>
                    </form>
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                                <li><a  class="dropdown-item" href="password-reset.php">Reset Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <li class="nav-item position-relative">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                            <?php
                                $cart_count = getCartCount();
                                if ($cart_count > 0) {
                                    echo '<span class="cart-badge">'.$cart_count.'</span>';
                                }
                            ?>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-4">