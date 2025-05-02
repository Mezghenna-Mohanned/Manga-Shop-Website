<?php
$host = 'localhost';
$dbname = 'mangashop';
$username = 'root';
$password = 'iammohanned04';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM products ORDER BY product_id ASC LIMIT 13";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll();

    $sql_new_arrivals = "SELECT * FROM products ORDER BY product_id DESC LIMIT 13";
    $stmt_new_arrivals = $conn->prepare($sql_new_arrivals);
    $stmt_new_arrivals->execute();
    $new_products = $stmt_new_arrivals->fetchAll();
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HB Manga Kissa</title>

  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>

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

  <!-- Manga à prix découverte Section -->
  <section class="products-section">
    <h2>Manga à prix découverte</h2>

    <div class="carousel-wrapper">
      <button class="carousel-arrow left">&#10094;</button>

      <div class="carousel-track">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
          <div class="card-image">
            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" />
          </div>
          <div class="card-content">
            <h3><?php echo $product['name']; ?></h3>
            <p class="price"><?php echo $product['price']; ?> DA</p>
            <button class="add-to-cart">Ajouter au panier</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <button class="carousel-arrow right">&#10095;</button>
    </div>
  </section>

  <!-- Nouvel Arrivage Section -->
  <section class="nouvel-arrivage-section">
    <h2>Nouvel Arrivage</h2>

    <div class="carousel-wrapper">
      <button class="carousel-arrow left">&#10094;</button>

      <div class="carousel-track">
        <?php foreach ($new_products as $product): ?>
        <div class="product-card">
          <div class="card-image">
            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" />
          </div>
          <div class="card-content">
            <h3><?php echo $product['name']; ?></h3>
            <p class="price"><?php echo $product['price']; ?> DA</p>
            <button class="add-to-cart">Ajouter au panier</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <button class="carousel-arrow right">&#10095;</button>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 Manga Store | All Rights Reserved</p>
  </footer>

  <script src="js/script.js"></script>
</body>

</html>

<?php
$conn = null;
?>
