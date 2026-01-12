<?php
include 'includes/config.php';

// Destroy all session data
session_destroy();

// Redirect to homepage
header("Location: index.php");
exit();