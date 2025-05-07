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

    // Get trending products
    $stmt3 = $conn->query("SELECT * FROM products ORDER BY RAND() LIMIT 6");
    $trending_products = $stmt3->fetchAll(PDO::FETCH_ASSOC);

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

    /* Header Styles */
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
    }
    
    .header-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Search Bar */
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
    
    /* Autocomplete */
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
    
    /* Navigation */
    .nav-menu ul {
      display: flex;
      gap: 15px;
      list-style: none;
      margin: 0;
      padding: 0;
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
    
    /* Hero Slider */
    .hero-slider {
      position: relative;
      width: 100%;
      height: 70vh;
      min-height: 500px;
      margin-top: 72px;
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
      margin-bottom: 30px;
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
      margin: 15px auto 0;
    }
    
    /* Carousel Styles */
    .carousel-container {
      position: relative;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .carousel-wrapper {
      position: relative;
      overflow: hidden;
      padding: 20px 0;
    }

    .carousel-track {
      display: flex;
      gap: 20px;
      transition: transform 0.5s ease;
      padding: 10px 0;
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

    /* Product Card Styles */
    .product-card {
      flex: 0 0 auto;
      width: 220px;
      background: var(--bg-card);
      border-radius: 10px;
      overflow: hidden;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(244, 117, 33, 0.3);
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

    .product-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    .product-info {
      padding: 20px;
      text-align: center;
    }

    .product-name {
      font-size: 1.1rem;
      margin-bottom: 5px;
      color: var(--text-main);
      font-weight: bold;
    }

    .product-price {
      color: var(--accent);
      font-weight: bold;
      font-size: 1.2rem;
      margin-bottom: 15px;
    }

    .product-author {
      color: var(--text-sub);
      font-size: 0.9rem;
      margin-bottom: 15px;
    }

    .add-to-cart {
      width: 100%;
      padding: 10px;
      background: transparent;
      border: 1px solid var(--accent);
      color: var(--text-main);
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s;
      font-weight: bold;
    }

    .add-to-cart:hover {
      background: var(--accent);
      color: #000;
    }

    /* Hide scrollbar but keep functionality */
    .carousel-track {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    
    .carousel-track::-webkit-scrollbar {
      display: none;
    }

    /* Footer */
    footer {
      background: #000;
      color: #888;
      text-align: center;
      padding: 30px 0;
      margin-top: 60px;
      font-size: 0.9rem;
    }

    /* Responsive */
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
      
      .hero-slider {
        height: 50vh;
        min-height: 400px;
      }
      
      .product-card {
        width: 180px;
      }
      
      .product-image {
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
      
      .product-image {
        height: 220px;
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

  <main style="margin-top: 72px;">
    <!-- Hero Slider -->
    <section class="hero-slider">
      <div class="hero-slide active" style="background-image: url('assets/images/d.jpg')"></div>
      <div class="hero-slide" style="background-image: url('assets/images/mha.jpg')"></div>
      <div class="hero-slide" style="background-image: url('assets/images/fireforce.jpg')"></div>
      <div class="hero-slide" style="background-image: url('assets/images/kaguya.jpg')"></div>
      
      <div class="hero-dots">
        <span class="active"></span>
        <span></span>
        <span></span>
        <span></span>
      </div>
    </section>

  

    <!-- Manga à prix découverte -->
    <section style="padding: 60px 0; background: rgba(0,0,0,0.1);">
      <h2 class="section-title">Manga à prix découverte</h2>
      <div class="carousel-container">
        <div class="carousel-wrapper">
          <button class="carousel-arrow left">&#10094;</button>
          <div class="carousel-track" id="discount-carousel">
            <?php foreach ($products as $p): ?>
              <div class="product-card">
                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-image">
                <div class="product-info">
                  <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
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

    <!-- Nouvel Arrivage -->
    <section style="padding: 60px 0;">
      <h2 class="section-title">Nouvel Arrivage</h2>
      <div class="carousel-container">
        <div class="carousel-wrapper">
          <button class="carousel-arrow left">&#10094;</button>
          <div class="carousel-track" id="new-carousel">
            <?php foreach ($new_products as $p): ?>
              <div class="product-card">
                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-image">
                <div class="product-info">
                  <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
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

  <footer>
    <p>&copy; 2025 Manga Store | Tous droits réservés</p>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Hero Slider
      const heroSlides = document.querySelectorAll('.hero-slide');
      const heroDots = document.querySelectorAll('.hero-dots span');
      let currentHeroSlide = 0;
      let heroInterval;

      function showHeroSlide(index) {
        heroSlides.forEach((slide, i) => {
          slide.classList.toggle('active', i === index);
          heroDots[i].classList.toggle('active', i === index);
        });
        currentHeroSlide = index;
      }

      function nextHeroSlide() {
        currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
        showHeroSlide(currentHeroSlide);
      }

      function startHeroSlider() {
        heroInterval = setInterval(nextHeroSlide, 5000);
      }

      heroDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          clearInterval(heroInterval);
          showHeroSlide(index);
          startHeroSlider();
        });
      });

      startHeroSlider();

      // Initialize all carousels
      function initCarousel(carouselId) {
        const track = document.getElementById(carouselId);
        const wrapper = track.parentElement;
        const leftBtn = wrapper.querySelector('.carousel-arrow.left');
        const rightBtn = wrapper.querySelector('.carousel-arrow.right');
        
        let position = 0;
        const cardWidth = 220;
        const gap = 20;
        const scrollAmount = (cardWidth + gap) * 3;
        const maxPosition = (track.scrollWidth - wrapper.offsetWidth) * -1;
        
        function updateButtons() {
          leftBtn.classList.toggle('disabled', position >= 0);
          rightBtn.classList.toggle('disabled', position <= maxPosition);
        }
        
        function moveCarousel(amount) {
          position = Math.max(maxPosition, Math.min(0, position + amount));
          track.style.transform = `translateX(${position}px)`;
          updateButtons();
        }
        
        leftBtn.addEventListener('click', () => moveCarousel(scrollAmount));
        rightBtn.addEventListener('click', () => moveCarousel(-scrollAmount));
        
        // Touch support
        let startX, moveX;
        track.addEventListener('touchstart', (e) => {
          startX = e.touches[0].clientX;
        });
        
        track.addEventListener('touchmove', (e) => {
          moveX = e.touches[0].clientX;
          const diff = startX - moveX;
          track.style.transform = `translateX(${position - diff}px)`;
        });
        
        track.addEventListener('touchend', (e) => {
          const diff = startX - moveX;
          if (Math.abs(diff) > 50) {
            moveCarousel(diff > 0 ? -scrollAmount/3 : scrollAmount/3);
          } else {
            track.style.transform = `translateX(${position}px)`;
          }
        });
        
        updateButtons();
      }
      
      // Initialize all carousels
      initCarousel('trending-carousel');
      initCarousel('discount-carousel');
      initCarousel('new-carousel');
      
      // Search functionality
      const searchInput = document.querySelector('.search-input');
      const autocompleteResults = document.getElementById('autocomplete-results');
      let timeoutId;
      
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