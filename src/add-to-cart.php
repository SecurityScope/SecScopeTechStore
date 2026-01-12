<?php
include 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to add items to cart";
    header("Location: login.php");
    exit();
}

// Process add to cart request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    // Validate product exists and has stock
    $product_query = "SELECT * FROM products WHERE id = $product_id AND stock > 0";
    $product_result = $conn->query($product_query);
    
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        
        // Check if product already in cart
        $check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            // Update quantity
            $update_query = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
            $conn->query($update_query);
        } else {
            // Add to cart
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
            $conn->query($insert_query);
        }
        
        $_SESSION['success'] = "Product added to cart successfully!";
    } else {
        $_SESSION['error'] = "Product not available or out of stock!";
    }
}

// Redirect back to previous page or products page
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'products.php';
header("Location: $redirect_url");
exit();
?>