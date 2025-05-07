<?php
session_start();

// 1) Si l'utilisateur n'est pas connecté, on le redirige
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2) Connexion à la BDD
$host     = 'localhost';
$dbname   = 'mangashop';
$username = 'root';
$password = 'iammohanned04';

try {
    $conn = new PDO(
      "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
      $username,
      $password,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 3) Gestion du POST “Ajouter au panier”
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['product_id'])) {
        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) $_POST['product_id'];

        // Vérifier si déjà dans le panier
        $sel = $conn->prepare("
          SELECT quantity
          FROM cart_items
          WHERE user_id = :uid AND product_id = :pid
        ");
        $sel->execute([
          ':uid' => $userId,
          ':pid' => $productId
        ]);

        if ($row = $sel->fetch(PDO::FETCH_ASSOC)) {
            // Incrémenter quantité
            $newQty = $row['quantity'] + 1;
            $upd = $conn->prepare("
              UPDATE cart_items
              SET quantity = :q
              WHERE user_id = :uid AND product_id = :pid
            ");
            $upd->execute([
              ':q'   => $newQty,
              ':uid' => $userId,
              ':pid' => $productId
            ]);
        } else {
            // Insérer nouvelle ligne
            $ins = $conn->prepare("
              INSERT INTO cart_items (user_id, product_id, quantity)
              VALUES (:uid, :pid, 1)
            ");
            $ins->execute([
              ':uid' => $userId,
              ':pid' => $productId
            ]);
        }

        // Redirection pour éviter le "resubmission"
        header('Location: z_index.php?added=1');
        exit;
    }

    // 4) Récupération des produits
    $stmt = $conn->query("SELECT * FROM products ORDER BY product_id ASC LIMIT 13");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 13");
    $new_products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur BDD : " . htmlspecialchars($e->getMessage());
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>HB Manga Kissa</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    .toast {
      position: fixed; top:20px; right:20px;
      background:#4CAF50; color:#fff;
      padding:10px 20px; border-radius:4px;
      box-shadow:0 2px 6px rgba(0,0,0,0.2);
      z-index:1000;
    }
  </style>
</head>
<body>

  <?php if (isset($_GET['added'])): ?>
    <div class="toast">Produit ajouté au panier ✅</div>
    <script>setTimeout(()=>document.querySelector('.toast').remove(),3000);</script>
  <?php endif; ?>

  <header class="sticky-header">
    <div class="header-container">
      <div class="logo"><img src="assets/images/logo.png" alt="Logo"></div>

      <nav class="nav-menu">
        <ul>
          <li><a href="#">MANGA</a></li>
          <li><a href="#">K‑POP</a></li>
          <li><a href="#">COMICS/CINÉMA</a></li>
          <li><a href="#">JEUX VIDÉO</a></li>
          <li><a href="#">DESSIN</a></li>
          <li><a href="#">JEUX DE CARTES</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <section class="hero-slider">
    <div class="hero-slide active" style="background-image: url('assets/images/d.jpg')">
      <div class="hero-banner-overlay"></div>
      <div class="hero-content"></div>
    </div>

    <div class="hero-slide" style="background-image: url('assets/images/mha.jpg')">
      <div class="hero-banner-overlay"></div>
      <div class="hero-content"></div>
    </div>

    <div class="hero-slide" style="background-image: url('assets/images/fireforce.jpg')">
      <div class="hero-banner-overlay"></div>
      <div class="hero-content"></div>
    </div>

    <div class="hero-slide" style="background-image: url('assets/images/kaguya.jpg')">
      <div class="hero-banner-overlay"></div>
      <div class="hero-content"></div>
    </div>

    <div class="hero-dots"></div>
  </section>


  <!-- Manga à prix découverte -->
  <section class="products-section">
    <h2>Manga à prix découverte</h2>
    <div class="carousel-wrapper">
      <button class="carousel-arrow left">&#10094;</button>
      <div class="carousel-track">
        <?php foreach ($products as $p): ?>
          <div class="product-card">
            <div class="card-image">
              <img src="<?= htmlspecialchars($p['image_url']) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>">
            </div>
            <div class="card-content">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <p class="price"><?= htmlspecialchars($p['price']) ?> DA</p>
              <form method="post">
                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                <button type="submit" class="add-to-cart">Ajouter au panier</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-arrow right">&#10095;</button>
    </div>
  </section>

  <!-- Nouvel Arrivage -->
  <section class="nouvel-arrivage-section">
    <h2>Nouvel Arrivage</h2>
    <div class="carousel-wrapper">
      <button class="carousel-arrow left">&#10094;</button>
      <div class="carousel-track">
        <?php foreach ($new_products as $p): ?>
          <div class="product-card">
            <div class="card-image">
              <img src="<?= htmlspecialchars($p['image_url']) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>">
            </div>
            <div class="card-content">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <p class="price"><?= htmlspecialchars($p['price']) ?> DA</p>
              <form method="post">
                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                <button type="submit" class="add-to-cart">Ajouter au panier</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-arrow right">&#10095;</button>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 Manga Store | Tous droits réservés</p>
  </footer>

  <script src="js/script.js"></script>
</body>
</html>
