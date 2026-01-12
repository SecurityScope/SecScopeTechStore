<?php

include 'includes/config.php';
include 'includes/header.php';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username already exists (VULNERABLE: SQL Injection)
        $check_query = "SELECT * FROM users WHERE username = '" . $username . "'";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Hash password before storing
            $hashed_password = md5($password);
            
            // Insert new user (VULNERABLE: SQL Injection)
            $insert_query = "INSERT INTO users (username, email, password) VALUES ('" . 
                            $username . "', '" . $email . "', '" . $hashed_password . "')";
            
            if ($conn->query($insert_query)) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Error creating account: " . $conn->error;
            }
        }
    }
}

?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Create Your Account</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">Register</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
                
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>