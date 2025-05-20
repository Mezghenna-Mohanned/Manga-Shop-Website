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


function displayStars($rating) {
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    $html = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($hasHalfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    
    return $html;
}

try {
    $conn = new PDO(
      "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
      $username,
      $password,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if (isset($_GET['search']) && (!empty($_GET['search']) || !empty($_GET['category']))) {
      $searchTerm = '%' . $_GET['search'] . '%';
      $category = $_GET['category'] ?? '';
      
      $sql = "SELECT name, product_id, image_url FROM products WHERE 1=1";
      $params = [];
      
      if (!empty($_GET['search'])) {
          $sql .= " AND name LIKE :term";
          $params[':term'] = $searchTerm;
      }
      
      if (!empty($category)) {
          $sql .= " AND category = :category";
          $params[':category'] = $category;
      }
      
      $sql .= " LIMIT 5";
      
      $stmt = $conn->prepare($sql);
      foreach ($params as $key => $value) {
          $stmt->bindValue($key, $value);
      }
      $stmt->execute();
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      header('Content-Type: application/json');
      echo json_encode($results);
      exit;
  }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['product_id'])) {
        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) $_POST['product_id'];

        $sel = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = :uid AND product_id = :pid");
        $sel->execute([':uid' => $userId, ':pid' => $productId]);

        if ($row = $sel->fetch(PDO::FETCH_ASSOC)) {
            $newQty = $row['quantity'] + 1;
            $upd = $conn->prepare("UPDATE cart_items SET quantity = :q WHERE user_id = :uid AND product_id = :pid");
            $upd->execute([':q' => $newQty, ':uid' => $userId, ':pid' => $productId]);
        } else {
            $ins = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:uid, :pid, 1)");
            $ins->execute([':uid' => $userId, ':pid' => $productId]);
        }

        header('Location: z_index.php?added=1');
        exit;
    }

    $stmt = $conn->query("
        SELECT p.*, 
              COALESCE(AVG(pr.rating), 0) as avg_rating,
              COUNT(pr.rating) as rating_count
        FROM products p
        LEFT JOIN product_ratings pr ON p.product_id = pr.product_id
        GROUP BY p.product_id
        ORDER BY p.product_id ASC 
        LIMIT 13
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $conn->query("
        SELECT p.*, 
              COALESCE(AVG(pr.rating), 0) as avg_rating,
              COUNT(pr.rating) as rating_count
        FROM products p
        LEFT JOIN product_ratings pr ON p.product_id = pr.product_id
        GROUP BY p.product_id
        ORDER BY p.product_id DESC 
        LIMIT 13
    ");
    $new_products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $stmt3 = $conn->query("
        SELECT p.*, 
              COALESCE(AVG(pr.rating), 0) as avg_rating,
              COUNT(pr.rating) as rating_count
        FROM products p
        LEFT JOIN product_ratings pr ON p.product_id = pr.product_id
        GROUP BY p.product_id
        ORDER BY RAND() 
        LIMIT 6
    ");
    $trending_products = $stmt3->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur BDD : " . htmlspecialchars($e->getMessage());
    exit;
}



session_start();
$cookieConsent = $_COOKIE['cookie_consent'] ?? false;

if (isset($_POST['cookie_consent'])) {
    if ($_POST['cookie_consent'] === 'accept') {
        setcookie('cookie_consent', 'accepted', time() + (86400 * 365), "/");
        $cookieConsent = 'accepted';
    } else {
        setcookie('cookie_consent', 'declined', time() + (86400 * 30), "/");
        $cookieConsent = 'declined';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Shonen Station</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    :root {
      --accent: #f47521;
      --bg-dark: #0e0e10;
      --bg-card: #1b1b1e;
      --text-main: #fff;
      --text-sub: #bbb;
    }

    .nav-menu a.active {
    color: var(--accent) !important;
    font-weight: bold;
    }
    .cart-popup {
      position: absolute;
      top: 120%;
      right: 0;
      width: 320px;
      max-height: 400px;
      background: var(--bg-card);
      border-radius: 8px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.3);
      overflow-y: auto;
      z-index: 1500;
      color: var(--text-main);
      font-size: 0.9rem;
    }

    .cart-popup .cart-item {
      display: flex;
      gap: 10px;
      padding: 10px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      align-items: center;
    }

    .cart-popup .cart-item img {
      width: 50px;
      height: 75px;
      object-fit: cover;
      border-radius: 4px;
    }

    .cart-popup .cart-item .item-info {
      flex: 1;
    }

    .cart-popup .cart-item .item-info .item-name {
      font-weight: bold;
      color: var(--accent);
    }

    .cart-popup .cart-item .item-info .item-qty {
      margin-top: 5px;
      color: var(--text-sub);
    }

    .cart-popup .cart-item .item-price {
      font-weight: bold;
      white-space: nowrap;
    }

    body {
      font-family: 'Arial', sans-serif;
      background: var(--bg-dark);
      color: var(--text-main);
      line-height: 1.6;
      overflow-x: hidden;
    }

    .toast {
      position: fixed; 
      top: 20px; 
      right: 20px;
      background: #4CAF50; 
      color: #fff;
      padding: 10px 20px; 
      border-radius: 4px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      z-index: 1000;
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .sticky-header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: rgba(14, 14, 16, .95);
      backdrop-filter: blur(6px);
      border-bottom: 1px solid rgba(255, 255, 255, .06);
      z-index: 1000;
      padding: 15px 0;
      height: 72px;
      box-sizing: border-box;
    }
    
    .header-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .category-filter {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      padding: 8px 12px;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 20px;
      background: rgba(27, 27, 30, 0.9);
      color: var(--text-main);
      font-size: 0.9rem;
      cursor: pointer;
      appearance: none;
      z-index: 10;
  }


    .search-container {
      position: relative;
      max-width: 700px;
      width: 100%;
    }

    .search-input-group {
      display: flex;
      align-items: center;
      background: rgba(27, 27, 30, 0.9);
      border-radius: 30px;
      border: 1px solid rgba(255,255,255,0.2);
      transition: all 0.3s ease;
      overflow: hidden;
    }

    .search-input-group:focus-within {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(244, 117, 33, 0.2);
    }

    .search-icon {
      padding: 0 15px;
      color: var(--text-sub);
      font-size: 1rem;
    }

    .search-input {
      flex: 1;
      padding: 14px 0;
      border: none;
      background: transparent;
      color: var(--text-main);
      font-size: 1rem;
      outline: none;
    }

    .category-select-wrapper {
      position: relative;
      margin-left: auto;
      padding-right: 15px;
    }

    .category-filter {
      appearance: none;
      padding: 14px 35px 14px 15px;
      background: transparent;
      border: none;
      color: var(--text-main);
      font-size: 0.95rem;
      cursor: pointer;
      outline: none;
      border-left: 1px solid rgba(255,255,255,0.1);
      transition: all 0.2s;
    }

    .category-filter:hover {
      color: var(--accent);
    }

    .category-filter option {
      background: var(--bg-card);
      color: var(--text-main);
    }

    .category-arrow {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--text-sub);
      font-size: 0.8rem;
      transition: all 0.2s;
    }

    .category-filter:focus + .category-arrow {
      transform: translateY(-50%) rotate(180deg);
      color: var(--accent);
    }
    
    .autocomplete-items {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 999;
      background: var(--bg-card);
      border-radius: 0 0 10px 10px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.3);
      max-height: 400px;
      overflow-y: auto;
      display: none;
    }
    
    .autocomplete-item {
      padding: 12px 20px;
      cursor: pointer;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .autocomplete-item:hover {
      background: rgba(244, 117, 33, 0.1);
    }
    
    .autocomplete-item img {
      width: 40px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
    }
    
    .autocomplete-item div {
      flex: 1;
    }
    
    .autocomplete-item strong {
      color: var(--accent);
    }
    
    .nav-menu ul {
  display: flex;
  justify-content: space-between;
  list-style: none;
  margin: 0;
  padding: 0;
  width: 100%;
}

  .nav-menu li {
    flex: 1;
    text-align: center;
  }

    
    .nav-menu a {
      color: var(--text-main);
      text-decoration: none;
      font-size: 0.95rem;
      letter-spacing: 0.8px;
      transition: color 0.2s;
      white-space: nowrap;
    }
    
    .nav-menu a:hover {
      color: var(--accent);
    }
    
    .hero-slider {
      position: relative;
      width: 100%;
      height: 70vh;
      min-height: 500px;
      margin-top: 0;
      overflow: hidden;
    }
    
    .hero-slide {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      opacity: 0;
      transition: opacity 1s ease-in-out;
      z-index: 0;
    }
    
    .hero-slide.active {
      opacity: 1;
      z-index: 1;
    }
    
    .hero-slide::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, var(--bg-dark) 0%, transparent 50%);
    }
    
    .hero-dots {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      z-index: 2;
    }
    
    .hero-dots span {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255,255,255,0.5);
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .hero-dots span.active {
      background: var(--accent);
      transform: scale(1.2);
    }
    
    /* Section Titles */
    .section-title {
      font-size: 2rem;
      margin-bottom: 20px; /* Reduced from 30px */
      position: relative;
      text-align: center;
      padding-top: 20px;
    }
    
    .section-title::after {
      content: '';
      display: block;
      width: 80px;
      height: 3px;
      background: var(--accent);
      margin: 10px auto 0;
    }
    
    .carousel-container {
      position: relative;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .carousel-wrapper {
      position: relative;
      overflow: hidden;
      padding: 10px 0;
    }

    .carousel-track {
      display: flex;
      gap: 20px;
      transition: transform 0.5s ease;
      padding: 5px 0;
    }

    .carousel-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 50px;
      height: 50px;
      background: rgba(27, 27, 30, 0.8);
      border: 2px solid var(--accent);
      border-radius: 50%;
      color: var(--accent);
      font-size: 1.5rem;
      cursor: pointer;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
    }

    .carousel-arrow:hover {
      background: var(--accent);
      color: #000;
    }

    .carousel-arrow.left {
      left: 0;
    }

    .carousel-arrow.right {
      right: 0;
    }

    .carousel-arrow.disabled {
      opacity: 0.3;
      cursor: not-allowed;
      border-color: var(--text-sub);
      color: var(--text-sub);
    }

    /* Modern Product Card Styles */
    .product-card {
      flex: 0 0 auto;
      width: 220px;
      background: var(--bg-card);
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      box-shadow: 0 8px 16px rgba(0,0,0,0.3);
      position: relative;
      margin-bottom: 10px; /* Added for better spacing */
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(244, 117, 33, 0.3);
    }

    .product-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      background: var(--accent);
      color: #000;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
      z-index: 2;
    }

    .product-image-container {
      width: 100%;
      height: 300px;
      overflow: hidden;
      position: relative;
    }

    .product-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    .product-info {
      padding: 15px; /* Reduced from 20px */
      text-align: center;
    }

    .product-name {
      font-size: 1rem;
      margin-bottom: 8px; /* Reduced from 15px */
      color: var(--text-main);
      font-weight: bold;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 2.4em;
      line-height: 1.2;
    }

    .product-price {
      color: var(--accent);
      font-weight: bold;
      font-size: 1.1rem;
      margin-bottom: 10px; /* Reduced from 15px */
    }

    .product-author {
      color: var(--text-sub);
      font-size: 0.85rem;
      margin-bottom: 12px; /* Reduced from 15px */
      display: -webkit-box;
      -webkit-line-clamp: 1;
      line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .add-to-cart {
      width: 100%;
      padding: 8px; /* Reduced from 10px */
      background: transparent;
      border: 1px solid var(--accent);
      color: var(--text-main);
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s;
      font-weight: bold;
      font-size: 0.9rem;
    }

    .add-to-cart:hover {
      background: var(--accent);
      color: #000;
    }

    .product-rating {
        display: flex;
        justify-content: center;
        gap: 2px;
        margin-bottom: 10px;
        align-items: center;
    }

    .product-rating i {
        color: #f47521;
        font-size: 0.9rem;
    }

    .product-rating span {
        font-size: 0.8rem;
        color: #888;
        margin-left: 5px;
    }

    .carousel-track {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    
    .carousel-track::-webkit-scrollbar {
      display: none;
    }

    footer {
      background: #000;
      color: #888;
      text-align: center;
      padding: 30px 0;
      margin-top: 60px;
      font-size: 0.9rem;
    }

    @media (max-width: 992px) {
      .header-container {
        flex-direction: column;
        padding: 10px;
      }
      
      .search-container {
        width: 100%;
        margin: 10px 0;
      }
      
      .nav-menu ul {
        flex-wrap: wrap;
        justify-content: center;
      }
      
      .hero-slider {
        height: 50vh;
        min-height: 400px;
      }
      
      .product-card {
        width: 180px;
      }
      
      .product-image-container {
        height: 250px;
      }
    }
    
    @media (max-width: 768px) {
      .carousel-arrow {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
      
      .product-card {
        width: 160px;
      }
      
      .product-image-container {
        height: 220px;
      }
      
      .section-title {
        font-size: 1.5rem;
        margin-bottom: 15px;
      }
    }
    #productModal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
        max-width: 90%;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        font-family: Arial, sans-serif;
      }

      #productModal.active {
        display: flex;
      }

      .modal-banner {
        height: 120px;
        background-size: cover;
        background-position: center;
      }

      .modal-image {
        width: 100%;
        height: 200px;
        object-fit: contain;
        margin: 10px 0;
      }

      /* Title and price */
      .modal-title {
        margin: 0 10px;
        font-size: 1.3em;
        font-weight: bold;
        color: #333;
      }

      .modal-price {
        margin: 5px 10px 15px 10px;
        color: #f47521;
        font-weight: bold;
        font-size: 1.1em;
      }

      .btn-add-cart {
        margin: 0 10px 15px 10px;
        padding: 12px;
        background-color: #f47521;
        border: none;
        color: white;
        font-weight: 600;
        cursor: pointer;
        border-radius: 5px;
        width: calc(100% - 20px);
      }

      .modal-close-btn {
        position: absolute;
        top: 8px;
        right: 12px;
        background: transparent;
        border: none;
        font-size: 2rem;
        color: #666;
        cursor: pointer;
      }

      @media (max-width: 600px) {
        #productModal {
          width: 90%;
        }

        .modal-image {
          height: 150px;
        }

        .modal-banner {
          height: 90px;
        }
      }

      .cookie-consent {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--bg-card);
        color: var(--text-main);
        padding: 20px;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.3);
        z-index: 9999;
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
      }

      .cookie-consent.show {
        transform: translateY(0);
      }

      .cookie-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
      }

      .cookie-content p {
        margin: 0;
        flex: 1;
      }

      .cookie-buttons {
        display: flex;
        gap: 10px;
      }

      .cookie-btn {
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.2s;
      }

      .accept-btn {
        background: var(--accent);
        color: #000;
        border: 1px solid var(--accent);
      }

      .decline-btn {
        background: transparent;
        color: var(--text-main);
        border: 1px solid var(--text-sub);
      }

      .accept-btn:hover {
        background: #f36b0b;
        border-color: #f36b0b;
      }

      .decline-btn:hover {
        border-color: var(--text-main);
      }

      @media (max-width: 768px) {
        .cookie-content {
          flex-direction: column;
          text-align: center;
        }
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
      <div class="search-container">
        <div class="search-input-group">
          <i class="fas fa-search search-icon"></i>
          <input type="text" class="search-input" placeholder="Rechercher des mangas, figurines, jeux vidéo..." autocomplete="off" id="search-input">
          <div class="category-select-wrapper">
            <select id="category-filter" class="category-filter">
              <option value="">Toutes catégories</option>
              <option value="manga">Manga</option>
              <option value="kpop">K-Pop</option>
              <option value="jeux_video">Jeux Vidéo</option>
            </select>
            <i class="fas fa-chevron-down category-arrow"></i>
          </div>
        </div>
        <div class="autocomplete-items" id="autocomplete-results"></div>
      </div>

      <nav class="nav-menu">
        <ul>
            <li><a href="#manga-section">MANGA</a></li>
            <li><a href="#kpop-section">K‑POP</a></li>
            <li><a href="#jeux_video-section">JEUX VIDÉO</a></li>
            <li><a href="history.php">HISTORIQUE</a></li>
            <li style="position: relative;">
              <a href="#" id="cart-button">PANIER <i class="fas fa-shopping-cart"></i></a>
              <div id="cart-popup" class="cart-popup" style="display:none;">
                <div id="cart-items"></div>
                <div id="cart-total" style="font-weight:bold; padding:10px; border-top:1px solid #444;"></div>
                <button id="finalize-order-btn" style="
                  margin: 10px;
                  width: calc(100% - 20px);
                  padding: 12px;
                  background-color: #f47521;
                  border: none;
                  color: black;
                  font-weight: bold;
                  border-radius: 6px;
                  cursor: pointer;
                  font-size: 1rem;
                ">
                  Finaliser commande
                </button>
              </div>

            </li>

        </ul>
    </nav>


    </div>
  </header>

  <main>
    <section class="hero-slider">
      <div class="hero-slide active" style="background-image: url('assets/images/d.jpg')"></div>
      <div class="hero-slide" style="background-image: url('assets/images/mha.jpg')"></div>
      <div class="hero-slide" style="background-image: url('assets/images/fireforce.jpg')"></div>
      <div class="hero-slide" style="background-image: url('assets/images/kaguya.jpg')"></div>
      
      <div class="hero-slide" style="background-image: url('assets/images/onepiece.jpg')"></div>
      
      <div class="hero-dots">
        <span class="active"></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
      </div>
    </section>

    <?php
    $categories = [
        'manga' => 'Manga',
        'kpop' => 'K-Pop',
        'jeux_video' => 'Jeux Vidéo'
    ];

    foreach ($categories as $category_id => $category_name) {
        $stmt = $conn->prepare("
            SELECT p.*, 
                  COALESCE(AVG(pr.rating), 0) as avg_rating,
                  COUNT(pr.rating) as rating_count
            FROM products p
            LEFT JOIN product_ratings pr ON p.product_id = pr.product_id
            WHERE p.category = ?
            GROUP BY p.product_id
            ORDER BY p.product_id DESC 
            LIMIT 8
        ");
        $stmt->execute([$category_id]);
        $category_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($category_products)) {
            echo '<section id="'.$category_id.'-section" style="padding: 40px 0;">';
            echo '<h2 class="section-title">'.$category_name.'</h2>';
            echo '<div class="carousel-container">';
            echo '<div class="carousel-wrapper">';
            echo '<button class="carousel-arrow left" onclick="scrollCarousel(\''.$category_id.'\', -1)">&#10094;</button>';
            echo '<div class="carousel-track" id="'.$category_id.'-carousel">';
            
            foreach ($category_products as $p) {
                echo '<div class="product-card">';
                echo '<div class="product-image-container">';
                echo '<a href="product.php?id='.(int)$p['product_id'].'">';
                echo '<img src="'.htmlspecialchars($p['image_url']).'" alt="'.htmlspecialchars($p['name']).'" class="product-image">';
                echo '</a>';
                echo '</div>';
                echo '<div class="product-info">';
                echo '<h3 class="product-name"><a href="product.php?id='.(int)$p['product_id'].'" style="color:inherit; text-decoration:none;">'.htmlspecialchars($p['name']).'</a></h3>';
                echo '<div class="product-rating">';
                echo displayStars($p['avg_rating']);
                echo '<span style="font-size:0.8em; margin-left:5px; color:#888;">('.(int)$p['rating_count'].')</span>';
                echo '</div>';
                echo '<p class="product-price">'.htmlspecialchars($p['price']).' DA</p>';
                echo '<form method="post">';
                echo '<input type="hidden" name="product_id" value="'.(int)$p['product_id'].'">';
                echo '<button type="submit" class="add-to-cart">Ajouter au panier</button>';
                echo '</form>';
                echo '</div></div>';
            }
            
            echo '</div>';
            echo '<button class="carousel-arrow right" onclick="scrollCarousel(\''.$category_id.'\', 1)">&#10095;</button>';
            echo '</div></div></section>';
        }
    }
    ?>
>
    <section style="padding: 40px 0; background: rgba(0,0,0,0.1);">
      <h2 class="section-title">Nouvel Arrivage</h2>
      <div class="carousel-container">
        <div class="carousel-wrapper">
          <button class="carousel-arrow left">&#10094;</button>
          <div class="carousel-track" id="discount-carousel">
            <?php foreach ($products as $p): ?>
              <div class="product-card">
                <div class="product-image-container">
                  <a href="product.php?id=<?= (int)$p['product_id'] ?>">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-image">
                  </a>

                </div>
                <div class="product-info">
                  <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                  <div class="product-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </div>
                  <p class="product-price"><?= htmlspecialchars($p['price']) ?> DA</p>
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
      </div>
    </section>


  </main>

  <div id="productModal" class="modal">
    <div class="modal-content">
      <div id="productModalBanner" class="modal-banner"></div>
      <img id="productModalImage" alt="Product Image" class="modal-image" />
      <h2 id="productModalTitle" class="modal-title"></h2>
      <p id="productModalPrice" class="modal-price"></p>
      <form id="productModalForm">
        <input type="hidden" id="productModalId" name="product_id" value="">
        <button type="submit" id="addToCartFromModal" class="btn-add-cart">Ajouter au panier</button>
      </form>
      <button id="modalCloseBtn" class="modal-close-btn">&times;</button>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 Manga Store | Tous droits réservés</p>
  </footer>

  <script src="js/main.js"></script>


  <?php if (empty($_COOKIE['cookie_consent'])) { ?>
<div id="cookie-consent-banner" class="cookie-consent">
  <div class="cookie-content">
    <p>Nous utilisons des cookies pour améliorer votre expérience. En continuant, vous acceptez notre utilisation des cookies.</p>
    <div class="cookie-buttons">
      <button id="accept-cookies" class="cookie-btn accept-btn">Accepter</button>
      <button id="decline-cookies" class="cookie-btn decline-btn">Refuser</button>
    </div>
  </div>
</div>
<?php } ?>
  

</body>
</html>
