<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$dbname = 'mangashop';
$username = 'root';
$password = 'iammohanned04';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get user's orders
    $stmt = $conn->prepare("
        SELECT o.order_id, o.total_price, o.status, o.shipping_address,
               GROUP_CONCAT(oi.product_id) as product_ids,
               GROUP_CONCAT(oi.quantity) as quantities
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.order_id DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get product details for each order
    foreach ($orders as &$order) {
        $productIds = explode(',', $order['product_ids']);
        $quantities = explode(',', $order['quantities']);
        
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $conn->prepare("
            SELECT product_id, name, price, image_url 
            FROM products 
            WHERE product_id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $order['items'] = [];
        foreach ($products as $index => $product) {
            $order['items'][] = [
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'image_url' => $product['image_url'],
                'quantity' => $quantities[$index],
                'price' => $product['price']
            ];
        }
    }
    unset($order);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historique des Commandes - Shonen Station</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --accent: #f47521;
      --bg-dark: #0e0e10;
      --bg-card: #1b1b1e;
      --text-main: #fff;
      --text-sub: #bbb;
    }

    header {
      background-color: var(--bg-dark);
      color: white;
      padding: 1rem 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .header-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: bold;
      color: var(--accent);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo i {
      font-size: 2rem;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
    }

    .nav-links a {
      color: var(--text-main);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }

    .nav-links a:hover {
      color: var(--accent);
    }

    .user-actions {
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }

    .user-actions a {
      color: var(--text-main);
      text-decoration: none;
      transition: color 0.3s;
    }

    .user-actions a:hover {
      color: var(--accent);
    }

    .cart-icon {
      position: relative;
    }

    .cart-count {
      position: absolute;
      top: -10px;
      right: -10px;
      background-color: var(--accent);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: bold;
    }

    body {
      font-family: 'Arial', sans-serif;
      background: var(--bg-dark);
      color: var(--text-main);
      line-height: 1.6;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      flex: 1;
    }

    :root {
      --accent: #f47521;
      --bg-dark: #0e0e10;
      --bg-card: #1b1b1e;
      --text-main: #fff;
      --text-sub: #bbb;
    }

    body {
      font-family: 'Arial', sans-serif;
      background: var(--bg-dark);
      color: var(--text-main);
      line-height: 1.6;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      flex: 1;
    }

    .history-header {
      text-align: center;
      margin-bottom: 40px;
      padding-top: 20px;
    }

    .history-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: var(--accent);
    }

    .history-header p {
      color: var(--text-sub);
      font-size: 1.1rem;
    }

    .order-card {
      background: var(--bg-card);
      border-radius: 12px;
      margin-bottom: 30px;
      overflow: hidden;
      box-shadow: 0 10px 20px rgba(0,0,0,0.3);
      transition: transform 0.3s ease;
    }

    .order-card:hover {
      transform: translateY(-5px);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      background: rgba(244, 117, 33, 0.1);
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .order-info {
      display: flex;
      flex-direction: column;
    }

    .order-id {
      font-size: 1.2rem;
      font-weight: bold;
      color: var(--accent);
      margin-bottom: 5px;
    }

    .order-address {
      color: var(--text-sub);
      font-size: 0.9rem;
      margin-top: 5px;
    }

    .order-status {
      padding: 8px 15px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 0.9rem;
    }

    .status-completed {
      background: rgba(46, 204, 113, 0.2);
      color: #2ecc71;
    }

    .status-pending {
      background: rgba(241, 196, 15, 0.2);
      color: #f1c40f;
    }

    .status-cancelled {
      background: rgba(231, 76, 60, 0.2);
      color: #e74c3c;
    }

    .order-details {
      padding: 20px;
    }

    .order-summary {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .order-total {
      font-size: 1.2rem;
      font-weight: bold;
    }

    .order-total span {
      color: var(--accent);
    }

    .order-items {
      border-top: 1px solid rgba(255,255,255,0.1);
      padding-top: 20px;
    }

    .order-items h3 {
      margin-bottom: 15px;
      font-size: 1.1rem;
      color: var(--accent);
    }

    .item-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }

    .item-card {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background: rgba(255,255,255,0.05);
      border-radius: 8px;
      transition: background 0.3s ease;
    }

    .item-card:hover {
      background: rgba(255,255,255,0.1);
    }

    .item-image {
      width: 60px;
      height: 90px;
      object-fit: cover;
      border-radius: 4px;
    }

    .item-info {
      flex: 1;
    }

    .item-name {
      font-weight: bold;
      margin-bottom: 5px;
    }

    .item-price {
      color: var(--accent);
      font-weight: bold;
      margin-bottom: 5px;
    }

    .item-quantity {
      color: var(--text-sub);
      font-size: 0.9rem;
    }

    .no-orders {
      text-align: center;
      padding: 50px;
      color: var(--text-sub);
    }

    .no-orders i {
      font-size: 3rem;
      margin-bottom: 20px;
      color: var(--accent);
    }

    .no-orders h2 {
      margin-bottom: 10px;
      color: var(--text-main);
    }

    .no-orders a {
      color: var(--accent);
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }

    .no-orders a:hover {
      color: #ff6a00;
    }

    footer {
      background: #000;
      color: #888;
      text-align: center;
      padding: 30px 0;
      margin-top: 60px;
      font-size: 0.9rem;
      width: 100%;
    }

    footer p {
      margin: 0;
    }

    @media (max-width: 768px) {
      .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      
      .order-summary {
        flex-direction: column;
        gap: 10px;
      }
      
      .item-list {
        grid-template-columns: 1fr;
      }
      
      footer {
        padding: 20px 0;
      }
    }

  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <a href="index.php" class="logo">
        <i class="fas fa-book"></i>
        <span>Shonen Station</span>
      </a>
      
      <nav class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="products.php">Produits</a>
        <a href="about.php">À propos</a>
        <a href="contact.php">Contact</a>
      </nav>
      
      <div class="user-actions">
        <a href="profile.php" class="user-icon">
          <i class="fas fa-user"></i>
        </a>
        <a href="cart.php" class="cart-icon">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-count">0</span>
        </a>
      </div>
    </div>
  </header>

  <div class="container">
    <div class="history-header">
      <h1>Votre Historique de Commandes</h1>
      <p>Retrouvez ici toutes vos commandes passées sur Shonen Station</p>
    </div>

    <?php if (empty($orders)): ?>
      <div class="no-orders">
        <i class="fas fa-box-open"></i>
        <h2>Aucune commande trouvée</h2>
        <p>Vous n'avez pas encore passé de commande sur notre site.</p>
        <p><a href="index.php">Découvrez nos produits</a></p>
      </div>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-header">
            <div class="order-info">
              <span class="order-id">Commande #<?= $order['order_id'] ?></span>
              <span class="order-address">Livraison: <?= htmlspecialchars($order['shipping_address']) ?></span>
            </div>
            <div class="order-status <?= 'status-' . strtolower($order['status']) ?>">
              <?= $order['status'] ?>
            </div>
          </div>
          
          <div class="order-details">
            <div class="order-summary">
              <div class="order-total">
                Total: <span><?= number_format($order['total_price'], 2) ?> DA</span>
              </div>
            </div>
            
            <div class="order-items">
              <h3>Articles commandés</h3>
              <div class="item-list">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="item-card">
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                    <div class="item-info">
                      <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                      <div class="item-price"><?= number_format($item['price'], 2) ?> DA</div>
                      <div class="item-quantity">Quantité: <?= $item['quantity'] ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <footer>
    <p>&copy; <?= date('Y') ?> Shonen Station | Tous droits réservés</p>
  </footer>
</body>
</html>