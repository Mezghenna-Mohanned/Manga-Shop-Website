<?php
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$host     = 'localhost';
$dbname   = 'mangashop';
$username = 'root';
$password = 'iammohanned04';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $userId = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT 
          p.product_id, 
          p.name, 
          p.price, 
          p.image_url, 
          ci.quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cartItems);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]);
}
