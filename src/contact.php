<?php
// contact.php
include 'includes/config.php';
include 'includes/header.php';

// Process contact form (VULNERABLE: XSS - No output sanitization)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // Use mysqli_real_escape_string to prevent SQL injection but KEEP XSS vulnerability
    $name_escaped = $conn->real_escape_string($name);
    $email_escaped = $conn->real_escape_string($email);
    $message_escaped = $conn->real_escape_string($message);
    
    $insert_query = "INSERT INTO messages (name, email, message, created_at) VALUES (
        '$name_escaped',
        '$email_escaped',
        '$message_escaped',
        NOW()
    )";
    
    if ($conn->query($insert_query)) {
        // VULNERABLE: XSS - Direct output without htmlspecialchars()
        $success = "Thank you for your message, " . $name . "! We'll get back to you soon.";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <h2>Contact Us</h2>
        
        <?php if (isset($success)): ?>
        <!-- VULNERABLE: XSS in success message -->
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom">Send Message</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Contact Information</h5>
            </div>
            <div class="card-body">
                <p><i class="fas fa-map-marker-alt me-2 text-warning"></i> 123 Tech Street, Silicon Valley, CA</p>
                <p><i class="fas fa-phone me-2 text-warning"></i> (555) 123-4567</p>
                <p><i class="fas fa-envelope me-2 text-warning"></i> info@secscope.com</p>
                
                <hr>
                
                <h6>Business Hours</h6>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 10:00 AM - 4:00 PM</p>
                <p>Sunday: Closed</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>