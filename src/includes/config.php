<?php
// Database configuration with environment variable support
$host = getenv('DB_HOST') ?: 'mysql'; // Use 'mysql' service name from docker-compose
$dbname = getenv('DB_NAME') ?: 'secscope_store';
$username = getenv('DB_USER') ?: 'secscope_user';
$password = getenv('DB_PASSWORD') ?: 'secscope_password_123';

// Connection retry logic for Docker startup
$max_retries = 10;
$retry_delay = 2; // seconds
$conn = null;

for ($i = 0; $i < $max_retries; $i++) {
    try {
        $conn = new mysqli($host, $username, $password, $dbname);
        
        if (!$conn->connect_error) {
            break; // Connection successful
        }
        
        // If not the last retry, wait and try again
        if ($i < $max_retries - 1) {
            error_log("Database connection attempt " . ($i + 1) . " failed. Retrying in {$retry_delay} seconds...");
            sleep($retry_delay);
        }
    } catch (Exception $e) {
        if ($i < $max_retries - 1) {
            error_log("Database connection exception: " . $e->getMessage());
            sleep($retry_delay);
        }
    }
}

// Final connection check
if (!$conn || $conn->connect_error) {
    die("Connection failed after {$max_retries} attempts: " . ($conn ? $conn->connect_error : "Unable to create connection"));
}

// Set charset to prevent SQL injection through encoding
$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to get cart count
function getCartCount() {
    global $conn;
    $cart_count = 0;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']); // Basic sanitization
        $cart_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id";
        $cart_result = $conn->query($cart_query);
        
        if ($cart_result && $cart_result->num_rows > 0) {
            $cart_data = $cart_result->fetch_assoc();
            $cart_count = $cart_data['total'] ? intval($cart_data['total']) : 0;
        }
    }
    
    return $cart_count;
}

// Function to redirect with message
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Function to display flash messages
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        
        $alert_class = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ][$type] ?? 'alert-info';
        
        echo "<div class='alert {$alert_class} alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($message);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Application settings
define('SITE_NAME', 'SecScope Tech Store');
define('SITE_URL', 'http://localhost:8080');
define('ADMIN_EMAIL', 'admin@secscope.local');

// Security settings (intentionally weak for demo purposes)
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 100); // Intentionally high for demo

// Error reporting (visible for educational purposes)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>