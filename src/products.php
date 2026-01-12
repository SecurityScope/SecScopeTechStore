<?php
include 'includes/config.php';
include 'includes/header.php';

// Get filter parameters (vulnerable to SQL injection)
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Build query with vulnerabilities
$query = "SELECT * FROM products WHERE stock > 0";

if (!empty($category)) {
    $query .= " AND category = '" . $category . "'";
}

if (!empty($search)) {
    $query .= " AND name LIKE '%" . $search . "%'";
}

$query .= " ORDER BY " . $sort;

$result = $conn->query($query);

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM products";
$categories_result = $conn->query($categories_query);
?>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="products.php">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php while($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['category']; ?>" <?php echo ($category == $cat['category']) ? 'selected' : ''; ?>>
                                <?php echo $cat['category']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sort By</label>
                        <select class="form-select" name="sort">
                            <option value="name" <?php echo ($sort == 'name') ? 'selected' : ''; ?>>Name</option>
                            <option value="price" <?php echo ($sort == 'price') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price DESC" <?php echo ($sort == 'price DESC') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="created_at DESC" <?php echo ($sort == 'created_at DESC') ? 'selected' : ''; ?>>Newest First</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100">Apply Filters</button>
                    <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Reset</a>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Products Listing -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <?php 
                if (!empty($category)) {
                    echo $category . ' Products';
                } else if (!empty($search)) {
                    echo 'Search Results for "' . htmlspecialchars($search) . '"';
                } else {
                    echo 'All Products';
                }
                ?>
            </h2>
            <span class="badge bg-primary"><?php echo $result->num_rows; ?> Products</span>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while($product = $result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <img src="assets/images/products/<?php echo $product['image'] ?: 'placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                        <p class="card-text"><?php echo substr($product['description'], 0, 100); ?>...</p>
                        <p class="card-text"><strong>$<?php echo $product['price']; ?></strong></p>
                        <p class="card-text">
                            <small class="text-muted">Category: <?php echo $product['category']; ?></small>
                        </p>
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
        <?php else: ?>
        <div class="alert alert-info text-center">
            <h4>No products found</h4>
            <p>Try adjusting your search or filter criteria</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>