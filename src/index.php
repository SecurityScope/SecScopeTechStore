<?php
include 'includes/config.php';
include 'includes/header.php';

// Get featured products (vulnerable to SQL injection)
$featured_query = "SELECT * FROM products WHERE stock > 0 ORDER BY RAND() LIMIT 6";
$featured_result = $conn->query($featured_query);

// Get categories
$categories_query = "SELECT DISTINCT category FROM products";
$categories_result = $conn->query($categories_query);
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to SecScope Tech Store</h1>
        <p class="lead">Discover the latest technology and gadgets at amazing prices</p>
        <a href="products.php" class="btn btn-warning btn-lg mt-3">Shop Now</a>
    </div>
</section>

<!-- Categories Section -->
<section class="categories mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="row justify-content-center">
            <?php while($category = $categories_result->fetch_assoc()): ?>
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <a href="products.php?category=<?php echo urlencode($category['category']); ?>" class="text-decoration-none">
                    <div class="card text-center product-card">
                        <div class="card-body">
                            <i class="fas fa-<?php 
                                switch($category['category']) {
                                    case 'Laptops': echo 'laptop'; break;
                                    case 'Smartphones': echo 'mobile-alt'; break;
                                    case 'Tablets': echo 'tablet-alt'; break;
                                    case 'Accessories': echo 'headphones'; break;
                                    case 'Smart Devices': echo 'house-signal'; break;
                                    default: echo 'box';
                                }
                            ?> fa-3x mb-2 text-primary-custom"></i>
                            <h6 class="card-title"><?php echo $category['category']; ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php while($product = $featured_result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <img src="assets/images/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                        <p class="card-text"><?php echo substr($product['description'], 0, 100); ?>...</p>
                        <p class="card-text"><strong>$<?php echo $product['price']; ?></strong></p>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                        <?php if (isLoggedIn()): ?>
                        <form action="add-to-cart.php" method="POST" class="d-inline">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary-custom btn-sm">Add to Cart</button>
                        </form>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-primary-custom btn-sm">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline-dark">View All Products</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>