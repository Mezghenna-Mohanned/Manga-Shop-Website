<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
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

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (first_name, last_name, email, password, is_admin)
        VALUES (?, ?, ?, ?, 0)
    ");
    
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);

    if ($stmt->execute()) {
        header('Location: ../dashboard.php?success=user_added');
    } else {
        header('Location: ../dashboard.php?error=user_add_failed');
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}