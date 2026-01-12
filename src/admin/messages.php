<?php
// admin/messages.php
include '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Process message deletion (VULNERABLE: CSRF - No token validation)
if (isset($_GET['delete_message'])) {
    $message_id = $_GET['delete_message'];
    $delete_query = "DELETE FROM messages WHERE id = $message_id";
    if ($conn->query($delete_query)) {
        $success = "Message deleted successfully!";
    } else {
        $error = "Error deleting message: " . $conn->error;
    }
}

// Fetch all messages from contact form (VULNERABLE: XSS - No output encoding)
$messages_query = "SELECT * FROM messages ORDER BY created_at DESC";
$messages_result = $conn->query($messages_query);

// Create messages table if it doesn't exist
if (!$messages_result) {
    $create_table_query = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_table_query);
    
    // Re-fetch messages
    $messages_result = $conn->query($messages_query);
}

// Insert sample messages if table is empty
if ($messages_result->num_rows === 0) {
    $sample_messages = [
        "John Doe", "john@example.com", "Hello, I'm interested in your products!",
        "Jane Smith", "jane@example.com", "When will the new iPhone be in stock?",
        "Bob Wilson", "bob@example.com", "Need help with my order #12345"
    ];
    
    for ($i = 0; $i < count($sample_messages); $i += 3) {
        $insert_query = "INSERT INTO messages (name, email, message) VALUES (
            '" . $sample_messages[$i] . "',
            '" . $sample_messages[$i+1] . "',
            '" . $sample_messages[$i+2] . "'
        )";
        $conn->query($insert_query);
    }
    
    // Re-fetch messages
    $messages_result = $conn->query($messages_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - SecScope Admin</title>
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
        
        .message-content {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
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
                            <a href="orders.php" class="nav-link">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Orders
                            </a>
                        </li>
                        <li>
                            <a href="messages.php" class="nav-link active">
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
                    <h1 class="h2">Contact Messages</h1>
                </div>

                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Messages List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>All Messages</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($messages_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Message</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($message = $messages_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $message['id']; ?></td>
                                                <td><?php echo $message['name']; ?></td>
                                                <td><?php echo $message['email']; ?></td>
                                                <td>
                                                    <div class="message-content">
                                                        <!-- VULNERABLE: XSS - No output encoding -->
                                                        <?php echo $message['message']; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <a href="?delete_message=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this message?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center">No messages found.</p>
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