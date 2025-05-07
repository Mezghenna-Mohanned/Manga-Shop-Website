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
    $conn = new PDO(
      "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
      $username,
      $password,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Handle search AJAX request
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchTerm = '%' . $_GET['search'] . '%';
        $stmt = $conn->prepare("SELECT name, product_id, image_url FROM products WHERE name LIKE :term LIMIT 5");
        $stmt->bindParam(':term', $searchTerm);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    // Handle add to cart
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

    // Get products
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    .toast {
      position: fixed; top:20px; right:20px;
      background:#4CAF50; color:#fff;
      padding:10px 20px; border-radius:4px;
      box-shadow:0 2px 6px rgba(0,0,0,0.2);
      z-index:1000;
    }
    
    /* Updated Header Layout */
    .sticky-header {
      padding: 10px 0;
    }
    
    .header-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Enhanced Search Bar Styles */
    .search-container {
      flex: 1;
      max-width: 600px;
      margin: 0 20px;
      position: relative;
    }
    
    .search-input {
      width: 100%;
      padding: 12px 20px 12px 45px;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 25px;
      background: rgba(27, 27, 30, 0.9);
      color: var(--text-main);
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(244, 117, 33, 0.2);
      background: rgba(27, 27, 30, 1);
    }
    
    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-sub);
    }
    
    /* Autocomplete dropdown */
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
    
    /* Navigation Menu */
    .nav-menu ul {
      display: flex;
      gap: 15px;
      list-style: none;
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
    
    @media (max-width: 992px) {
      .header-container {
        flex-direction: column;
        gap: 15px;
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
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher des mangas, figurines, jeux vidéo..." autocomplete="off">
        <div class="autocomplete-items" id="autocomplete-results"></div>
      </div>

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

  <!-- Rest of your content remains the same -->
  
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


  <!-- ... [keep your existing product sections] ... -->

  <footer>
    <p>&copy; 2025 Manga Store | Tous droits réservés</p>
  </footer>

  <script src="js/script.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.querySelector('.search-input');
      const autocompleteResults = document.getElementById('autocomplete-results');
      let timeoutId;
      
      // Search function with debounce
      function searchProducts(query) {
        if (query.length < 2) {
          autocompleteResults.style.display = 'none';
          return;
        }
        
        fetch(`z_index.php?search=${encodeURIComponent(query)}`)
          .then(response => response.json())
          .then(data => {
            if (data.length > 0) {
              autocompleteResults.innerHTML = '';
              data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.innerHTML = `
                  <img src="${item.image_url}" alt="${item.name}">
                  <div>${item.name.replace(
                    new RegExp(query, 'gi'), 
                    match => `<strong>${match}</strong>`
                  )}</div>
                `;
                div.addEventListener('click', () => {
                  window.location.href = `product.php?id=${item.product_id}`;
                });
                autocompleteResults.appendChild(div);
              });
              autocompleteResults.style.display = 'block';
            } else {
              autocompleteResults.style.display = 'none';
            }
          });
      }
      
      // Event listeners
      searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
          searchProducts(this.value.trim());
        }, 300);
      });
      
      searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && autocompleteResults.innerHTML) {
          autocompleteResults.style.display = 'block';
        }
      });
      
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
          autocompleteResults.style.display = 'none';
        }
      });
      
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          const query = searchInput.value.trim();
          if (query) {
            window.location.href = `search.php?q=${encodeURIComponent(query)}`;
          }
        }
      });
    });
  </script>
</body>
</html>