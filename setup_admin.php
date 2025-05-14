<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please log in first');
}

// Add is_admin column if it doesn't exist
$conn->query("
    ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT FALSE
");

// Make the logged-in user an admin
$stmt = $conn->prepare("
    UPDATE users 
    SET is_admin = TRUE 
    WHERE user_id = ?
");
$stmt->bind_param('i', $_SESSION['user_id']);

if ($stmt->execute()) {
    echo "Admin access granted successfully! You can now access the <a href='admin/dashboard.php'>admin dashboard</a>";
} else {
    echo "Error setting up admin access";
}
?>