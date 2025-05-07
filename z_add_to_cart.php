<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['product_id'])) {
    $userId    = (int) $_SESSION['user_id'];
    $productId = (int) $_POST['product_id'];

    $host     = 'localhost';
    $dbname   = 'mangashop';
    $dbUser   = 'root';
    $dbPass   = 'iammohanned04';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sel = $pdo->prepare("
      SELECT quantity 
      FROM cart_items 
      WHERE user_id = ? AND product_id = ?
    ");
    $sel->execute([$userId, $productId]);

    if ($row = $sel->fetch(PDO::FETCH_ASSOC)) {
        $newQty = $row['quantity'] + 1;
        $upd = $pdo->prepare("
          UPDATE cart_items 
          SET quantity = ? 
          WHERE user_id = ? AND product_id = ?
        ");
        $upd->execute([$newQty, $userId, $productId]);
    } else {
        $ins = $pdo->prepare("
          INSERT INTO cart_items (user_id, product_id, quantity) 
          VALUES (?, ?, 1)
        ");
        $ins->execute([$userId, $productId]);
    }

    header('Location: z_index.php?added=1');
    exit;
}

header('HTTP/1.1 400 Bad Request');
echo "Requête invalide.";
