<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['product_id'])) {
    $userId    = (int) $_SESSION['user_id'];
    $productId = (int) $_POST['product_id'];
    $quantity  = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    if ($quantity < 1) {
        $quantity = 1;
    }

    $host     = 'localhost';
    $dbname   = 'mangashop';
    $dbUser   = 'root';
    $dbPass   = 'iammohanned04';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $sel = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $sel->execute([$userId, $productId]);

        if ($row = $sel->fetch(PDO::FETCH_ASSOC)) {
            $newQty = $row['quantity'] + $quantity;
            $upd = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $upd->execute([$newQty, $userId, $productId]);
        } else {
            $ins = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $ins->execute([$userId, $productId, $quantity]);
        }

        header("Location: product.php?id=$productId&added=1");

        exit;

    } catch (PDOException $e) {
        http_response_code(500);
        echo "Erreur base de données : " . htmlspecialchars($e->getMessage());
        exit;
    }
}

header('HTTP/1.1 400 Bad Request');
echo "Requête invalide.";
exit;
