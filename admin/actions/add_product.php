<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $image_url = filter_var($_POST['image_url'] ?? '', FILTER_VALIDATE_URL);

    if (!$name || !$price || !$stock_quantity || !$image_url) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO products (name, price, stock_quantity, image_url)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param('sdis', $name, $price, $stock_quantity, $image_url);

    if ($stmt->execute()) {
        header('Location: ../dashboard.php?success=product_added');
    } else {
        header('Location: ../dashboard.php?error=product_add_failed');
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}