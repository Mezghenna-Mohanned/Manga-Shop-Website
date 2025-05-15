<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $image_url = filter_var($_POST['image_url'] ?? '', FILTER_VALIDATE_URL);
    $category = $_POST['category'] ?? '';

    $valid_categories = ['manga', 'kpop', 'comics_cinema', 'jeux_video', 'dessin', 'jeux_cartes'];
    if (!in_array($category, $valid_categories)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        exit;
    }

    if (empty($name) || $price <= 0 || $stock_quantity < 0 || !$image_url) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Invalid or missing required fields']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO products (name, price, stock_quantity, image_url, category)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param('sdiss', $name, $price, $stock_quantity, $image_url, $category);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added successfully']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $conn->error]);
    }
    exit;
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}