<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die('Please log in first');
}

$conn->query("
    ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT FALSE
");

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