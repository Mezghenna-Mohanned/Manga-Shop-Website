<?php
session_start();

$host     = 'localhost';
$dbname   = 'mangashop';
$username = 'root';
$password = 'iammohanned04';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header('Location: z_index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h1>Produit non trouvé</h1>";
    exit;
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> - Détails</title>
  <style>
  :root {
    --neon-red: #f47521;
    --dark-bg: #0a0a0f;
    --accent: #1a1a24;
    --text-primary: #e5e5e5;
    --error-red: #e74c3c;
    --success-green: #2ecc71;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Netflix Sans', 'Helvetica Neue', sans-serif;
  }

  body {
    background: var(--dark-bg);
    color: var(--text-primary);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
  }

  .background-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    background: linear-gradient(0deg, rgba(10,10,15,0.95) 20%, rgba(10,10,15,0.5) 100%),
          url('https://i.redd.it/hells-paradise-jigokuraku-wallpaper-v0-7b1v1l6g5zsa1.jpg') center/cover;
    filter: saturate(1.2) brightness(0.7);
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    position: relative;
  }

  .header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .form-group {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 1rem;
  }

  .quantity-input {
    width: 70px;
    padding: 0.6rem 0.8rem;
    font-size: 1.1rem;
    border-radius: 6px;
    border: 2px solid #f47521;
    background-color:rgb(23, 23, 26);
    color: #fff;
    text-align: center;
    transition: border-color 0.3s ease;
  }

  .quantity-input:focus {
    outline: none;
    border-color: #ff6a00;
    box-shadow: 0 0 8px #ff6a00aa;
  }

  .play-button {
    background-color: #f47521;
    color: #0a0a0f;
    padding: 0.8rem 1.8rem;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 4px 6px #f475211a;
  }

  .play-button:hover,
  .play-button:focus {
    background-color: #ff6a00;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px #ff6a001a;
  }

  .play-button:disabled {
    background-color: #666;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }

  .back-button {
    font-size: 1.3rem;
    font-weight: 600;
    color: #f47521;
    text-decoration: none;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    display: inline-block;
    transition: background-color 0.3s ease, color 0.3s ease;
    border: 2px solid transparent;
  }

  .back-button:hover,
  .back-button:focus {
    background-color: #f47521;
    color: #0a0a0f;
    border-color: #f47521;
    cursor: pointer;
    text-decoration: none;
  }

  .media-card {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 3rem;
    background: var(--accent);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  }

  .poster-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 2/3;
  }

  .poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .title-section h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
  }

  .metadata {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    color: #888;
  }

  .rating {
    color: var(--neon-red);
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .episode-section {
    background: #15151e;
    border-radius: 8px;
    padding: 1.5rem;
  }

  .play-button {
    background: var(--neon-red);
    border: none;
    padding: 1rem 2rem;
    color: white;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.8rem;
  }

  .play-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 70, 85, 0.4);
  }

  .details-section {
    margin-top: 1.5rem;
    border-top: 1px solid #252535;
    padding-top: 1.5rem;
  }

  .tech-specs {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
  }

  .spec-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    color: #888;
  }
  
  .price {
    color: var(--neon-red);
    font-size: 1.8rem;
    font-weight: 700;
  }

  .stock {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
  }

  .stock.in-stock {
    background: var(--success-green);
    color: white;
  }

  .stock.out-of-stock {
    background: var(--error-red);
    color: white;
  }

  .stock.low-stock {
    background: #f39c12;
    color: white;
  }

  .category {
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
    color: var(--neon-red);
  }

  .toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    opacity: 1;
    transition: opacity 0.5s ease;
    z-index: 9999;
    user-select: none;
    max-width: 90%;
    text-align: center;
  }

  .toast.hide {
    opacity: 0;
    pointer-events: none;
  }

  .toast-success {
    background-color: var(--success-green);
    color: white;
  }

  .toast-error {
    background-color: var(--error-red);
    color: white;
  }

  .error-message {
    color: var(--error-red);
    margin-top: 1rem;
    font-weight: 600;
    padding: 0.5rem;
    border-radius: 4px;
  }

  .stock-info {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #aaa;
  }

  @media (max-width: 768px) {
    .media-card {
      grid-template-columns: 1fr;
    }
    
    .poster-container {
      max-width: 300px;
      margin: 0 auto;
    }
  }

  </style>
</head>
<body>
  <div class="background-wrapper"></div>

  <div class="container">

  <?php if (isset($_GET['added'])): ?>
    <div id="success-toast" class="toast toast-success">
      Ton produit est au panier ✅
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div id="error-toast" class="toast toast-error">
      <?= htmlspecialchars($_GET['error']) ?>
    </div>
  <?php endif; ?>

  <a href="z_index.php" class="back-button">&larr; Retour</a>

  <div class="media-card">
    <div class="poster-container">
      <img src="<?= htmlspecialchars($product['image_url']) ?>" 
         alt="<?= htmlspecialchars($product['name']) ?>" 
         class="poster">
    </div>

    <div class="content">
      <div class="title-section">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <div class="metadata">
          <span class="price"><?= number_format($product['price'], 2) ?> DA</span>
          <span>•</span>
          <?php
          $stockClass = 'in-stock';
          if ($product['stock_quantity'] <= 0) {
              $stockClass = 'out-of-stock';
          } elseif ($product['stock_quantity'] <= 5) {
              $stockClass = 'low-stock';
          }
          ?>
          <span class="stock <?= $stockClass ?>">
            <?php
            if ($product['stock_quantity'] <= 0) {
                echo 'Rupture';
            } elseif ($product['stock_quantity'] <= 5) {
                echo 'Stock faible';
            } else {
                echo 'En Stock';
            }
            ?>
          </span>
          <span>•</span>
          <span class="category"><?= htmlspecialchars($product['category']) ?></span>
        </div>
      </div>

      <div class="episode-section">
        <h2>Description du Produit</h2>
        <p class="synopsis">
          <?= nl2br(htmlspecialchars($product['description'] ?? 'Description non disponible')) ?>
        </p>
        
        <?php if ($product['stock_quantity'] > 0): ?>
          <form method="POST" action="z_add_to_cart.php">
            <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
            <input type="hidden" name="redirect_url" value="product.php?id=<?= (int)$product['product_id'] ?>">
            <div class="form-group">
              <input type="number" 
                id="quantity" 
                name="quantity" 
                min="1" 
                max="<?= $product['stock_quantity'] ?>"
                value="1"
                class="quantity-input" />
              <button type="submit" class="play-button" <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 3l14 9-14 9V3z"/>
                </svg>
                <?= $product['stock_quantity'] <= 0 ? 'Rupture de stock' : 'Ajouter au panier' ?>
              </button>
            </div>
            <div class="stock-info">
              <?php if ($product['stock_quantity'] > 0): ?>
                Stock disponible: <?= $product['stock_quantity'] ?> unité(s)
              <?php endif; ?>
            </div>
          </form>
        <?php else: ?>
          <div class="error-message">
            Ce produit est actuellement en rupture de stock.
          </div>
        <?php endif; ?>
      </div>

      <div class="details-section">
        <div class="tech-specs">
          <div class="spec-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 14a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm-5-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
            </svg>
            Catégorie : <?= htmlspecialchars($product['category']) ?>
          </div>
          <div class="spec-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 7h16M4 15h16M4 11h16"/>
            </svg>
            Référence : #<?= $product['product_id'] ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  <script>
  window.addEventListener('DOMContentLoaded', () => {
    const successToast = document.getElementById('success-toast');
    const errorToast = document.getElementById('error-toast');
    
    if (successToast) {
      setTimeout(() => {
        successToast.classList.add('hide');
      }, 3000);
    }
    
    if (errorToast) {
      setTimeout(() => {
        errorToast.classList.add('hide');
      }, 5000);
    }

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      form.addEventListener('submit', (e) => {
        const quantityInput = form.querySelector('input[name="quantity"]');
        const max = parseInt(quantityInput.getAttribute('max'));
        const value = parseInt(quantityInput.value);
        
        if (value < 1) {
          e.preventDefault();
          alert('La quantité doit être au moins 1');
          return;
        }
        
        if (value > max) {
          e.preventDefault();
          alert(`Vous ne pouvez pas commander plus que ${max} unités`);
          return;
        }
      });
    });
  });
  </script>
</body>
</html>