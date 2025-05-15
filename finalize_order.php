<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
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

    // Fetch cart items for this user
    $stmt = $conn->prepare("
        SELECT p.product_id, p.name, p.price, p.image_url, ci.quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $totalPrice = 0;
    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    $success = false;
    $error = '';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        if ($shippingAddress === '') {
            $error = "Veuillez saisir une adresse de livraison.";
        } else if (empty($cartItems)) {
            $error = "Votre panier est vide.";
        } else {
            // Insert order
            $insertOrder = $conn->prepare("
                INSERT INTO orders (user_id, total_price, shipping_address, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $insertOrder->execute([$userId, $totalPrice, $shippingAddress]);

            $orderId = $conn->lastInsertId();

            // Insert order items
            $insertItem = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cartItems as $item) {
                $insertItem->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // Clear user's cart
            $deleteCart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $deleteCart->execute([$userId]);

            $success = true;
            // Empty cart items to hide the list
            $cartItems = [];
            $totalPrice = 0;
        }
    }

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Finaliser la commande</title>
<style>
  :root {
    --primary: #f47521;
    --dark-bg: #0a0a0f;
    --card-bg: #1a1a24;
    --text: #e5e5e5;
    --success: #4CAF50;
    --error: #e74c3c;
  }

  body {
    background: var(--dark-bg);
    color: var(--text);
    font-family: 'Inter', 'Segoe UI', sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
    min-height: 100vh;
  }

  .container {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  }

  h1 {
    font-size: 2.2rem;
    margin-bottom: 1.5rem;
    color: var(--primary);
    position: relative;
    display: inline-block;
  }

  h1::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 60px;
    height: 4px;
    background: var(--primary);
    border-radius: 2px;
  }

  .cart-items {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin: 2rem 0;
  }

  .cart-item {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    transition: transform 0.3s ease;
  }

  .cart-item:hover {
    transform: translateY(-3px);
  }

  .cart-item img {
    width: 100px;
    height: 140px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid var(--primary);
  }

  .cart-item-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .cart-item-name {
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
  }

  .cart-item-meta {
    color: #aaa;
    font-size: 0.95rem;
    display: flex;
    gap: 1rem;
  }

  .total {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: right;
    margin: 2rem 0;
    padding-top: 1rem;
    border-top: 2px solid rgba(255,255,255,0.1);
  }

  .total-amount {
    color: var(--primary);
    font-size: 1.8rem;
  }

  .form-group {
    margin-top: 2rem;
  }

  label {
  display: inline-block;
  margin-top: 1rem;
  margin-bottom: 0.3rem;
  font-weight: 600;
  color: var(--text);
  font-size: 0.9rem;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  }


  textarea {
  width: 100%;
  height: 100px;
  padding: 0.6rem 0.8rem;
  background: #1a1a24;
  border: 1.5px solid var(--primary);
  border-radius: 8px;
  color: var(--text);
  font-size: 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

  textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 8px var(--primary);
  }


  button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    margin-top: 2rem;
    padding: 1rem 2.5rem;
    background: var(--primary);
    color: black;
    font-weight: 700;
    font-size: 1.1rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
  }

  button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(244, 117, 33, 0.3);
  }

  .error {
    margin: 1.5rem 0;
    padding: 1rem;
    background: rgba(231, 76, 60, 0.2);
    border-left: 4px solid var(--error);
    border-radius: 4px;
    font-weight: 600;
  }

  .success-message {
    position: fixed;
    top: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: var(--success);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(76, 175, 80, 0.3);
    display: flex;
    align-items: center;
    gap: 1rem;
    z-index: 1000;
    animation: slideIn 0.5s ease, fadeOut 0.5s ease 3.5s forwards;
  }

  .success-message svg {
    width: 24px;
    height: 24px;
  }

  @keyframes slideIn {
    from { top: -100px; opacity: 0; }
    to { top: 2rem; opacity: 1; }
  }

  @keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
  }

  .empty-cart {
    text-align: center;
    padding: 3rem;
    color: #aaa;
    font-size: 1.2rem;
  }

  @media (max-width: 768px) {
    body {
      padding: 1rem;
    }
    
    .container {
      padding: 1.5rem;
    }
    
    .cart-item {
      flex-direction: column;
      gap: 1rem;
    }
    
    .cart-item img {
      width: 100%;
      height: auto;
      aspect-ratio: 2/3;
    }
  }
</style>
</head>
<body>

<div class="container">
  <h1>Finaliser la commande</h1>

  <?php if ($success): ?>
    <div class="success-message">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <span>Merci ! Votre commande a bien été enregistrée.</span>
    </div>
    <script>
      setTimeout(() => {
        window.location.href = 'z_index.php';
      }, 4000);
    </script>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!$success): ?>
    <?php if (!empty($cartItems)): ?>
      <div class="cart-items">
        <?php foreach ($cartItems as $item): ?>
          <div class="cart-item">
            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="cart-item-info">
              <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="cart-item-meta">
                <span>Quantité : <?= $item['quantity'] ?></span>
                <span>Prix unitaire : <?= number_format($item['price'], 2) ?> DZD</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="total">
        Total : <span class="total-amount"><?= number_format($totalPrice, 2) ?> DZD</span>
      </div>

      <form method="POST" action="">
        <div class="form-group">
          <label for="shipping_address">Adresse de livraison :</label>
          <textarea name="shipping_address" id="shipping_address" required></textarea>
        </div>
        <button type="submit">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
          </svg>
          Valider la commande
        </button>
      </form>
    <?php else: ?>
      <div class="empty-cart">
        <p>Votre panier est vide.</p>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

</body>
</html>