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

        $checkStock = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $checkStock->execute([$productId]);
        $product = $checkStock->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found");
        }

        $cartItem = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $cartItem->execute([$userId, $productId]);
        $existingQty = $cartItem->fetchColumn();

        $totalRequested = $existingQty + $quantity;

        if ($totalRequested > $product['stock_quantity']) {
            header("Location: product.php?id=$productId&error=Not enough stock available. Only {$product['stock_quantity']} items left.");
            exit;
        }

        if ($existingQty) {
            $update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $update->execute([$totalRequested, $userId, $productId]);
        } else {
            $insert = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->execute([$userId, $productId, $quantity]);
        }

        header("Location: product.php?id=$productId&added=1");
        exit;

    } catch (PDOException $e) {
        header("Location: product.php?id=$productId&error=Database error: " . urlencode($e->getMessage()));
        exit;
    } catch (Exception $e) {
        header("Location: product.php?id=$productId&error=" . urlencode($e->getMessage()));
        exit;
    }
}

header('HTTP/1.1 400 Bad Request');
echo "Invalid request.";
exit;