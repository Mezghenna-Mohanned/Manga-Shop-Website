<?php
session_start();

$dbHost     = 'localhost';
$dbName     = 'mangashop';
$dbUser     = 'root';
$dbPassword = 'iammohanned04';

$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
