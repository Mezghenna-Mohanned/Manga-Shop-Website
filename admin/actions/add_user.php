<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$first_name || !$last_name || !$email || !$password) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (first_name, last_name, email, password, role, created_at)
        VALUES (?, ?, ?, ?, 'customer', NOW())
    ");
    
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Failed to add user: ' . $conn->error]);
    }
    exit;
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}