<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Veuillez saisir une adresse e-mail valide et un mot de passe.';
    } else {
        if ($action === 'login') {
            $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($userId, $hashedPassword);
                $stmt->fetch();
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $userId;
                    header('Location: z_index.php');
                    exit;
                } else {
                    $error = 'Mot de passe incorrect.';
                }
            } else {
                $error = 'Aucun compte trouvé avec cette adresse e-mail.';
            }

        } elseif ($action === 'register') {
            $first_name   = trim($_POST['first_name']   ?? '');
            $last_name    = trim($_POST['last_name']    ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');
            $address      = trim($_POST['address']      ?? '');

            if ($first_name && $last_name) {
                $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $chk->bind_param('s', $email);
                $chk->execute();
                $chk->store_result();

                if ($chk->num_rows > 0) {
                    $error = 'Cette adresse e-mail est déjà utilisée.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $conn->prepare("
                        INSERT INTO users 
                          (first_name, last_name, email, password, phone_number, address)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $ins->bind_param(
                      'ssssss',
                      $first_name,
                      $last_name,
                      $email,
                      $hashed,
                      $phone_number,
                      $address
                    );
                    if ($ins->execute()) {
                        $_SESSION['user_id'] = $ins->insert_id;
                        header('Location: z_index.php');
                        exit;
                    } else {
                        $error = 'Erreur lors de l\'inscription.';
                    }
                }
            } else {
                $error = 'Veuillez saisir votre prénom et nom.';
            }

        } else {
            $error = 'Action invalide.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Connexion - HB Manga Kissa</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .login-page {
      height: 100vh;
      background: 
        linear-gradient(rgba(14, 14, 16, 0.7), rgba(14, 14, 16, 0.9)),
        url('assets/images/image032.png') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .login-header {
      width: 100%;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .login-logo img {
      height: 40px;
    }

    .login-container {
      background: rgba(27, 27, 30, 0.9);
      width: 450px;
      padding: 60px 40px;
      border-radius: 8px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
      margin: auto;
    }

    .login-title {
      color: var(--text-main);
      font-size: 2rem;
      margin-bottom: 10px;
      text-align: center;
    }

    .login-subtitle {
      color: var(--text-sub);
      text-align: center;
      margin-bottom: 30px;
      font-size: 1.1rem;
    }

    .login-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      color: var(--text-sub);
      font-size: 0.9rem;
    }

    .form-control {
      padding: 12px 15px;
      background: #333;
      border: 1px solid #444;
      border-radius: 4px;
      color: var(--text-main);
      font-size: 1rem;
    }

    .form-control:focus {
      outline: 2px solid var(--accent);
      background: #444;
    }

    .login-actions {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .btn {
      padding: 12px 20px;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.2s;
      flex: 1;
      text-align: center;
      font-size: 1rem;
    }

    .btn-primary {
      background: var(--accent);
      color: #000;
      border: none;
    }

    .btn-primary:hover {
      background: #ff8c42;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: transparent;
      color: var(--text-main);
      border: 1px solid #444;
    }

    .btn-secondary:hover {
      background: rgba(255,255,255,0.1);
      transform: translateY(-2px);
    }

    .error-message {
      color: #ff6b6b;
      text-align: center;
      margin-bottom: 15px;
      padding: 10px;
      background: rgba(255,0,0,0.1);
      border-radius: 4px;
    }

    .trending-section {
      background: var(--bg-dark);
      padding: 60px 20px;
      text-align: center;
    }

    .trending-title {
      color: var(--text-main);
      font-size: 1.8rem;
      margin-bottom: 40px;
      position: relative;
    }

    .trending-title::after {
      content: '';
      display: block;
      width: 80px;
      height: 3px;
      background: var(--accent);
      margin: 15px auto 0;
    }

    .trending-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .trending-item {
      border-radius: 8px;
      overflow: hidden;
      transition: transform 0.3s;
      position: relative;
    }

    .trending-item:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(244, 117, 33, 0.3);
    }

    .trending-item img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      display: block;
    }

    @media (max-width: 768px) {
      .login-container {
        width: 90%;
        padding: 40px 20px;
      }
      
      .login-actions {
        flex-direction: column;
      }
      
      .trending-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
</head>
<body>
  <div class="login-page">
    <header class="login-header">
      <div class="login-logo">
        <img src="assets/images/logo.png" alt="HB Manga Kissa">
      </div>
    </header>

    <div class="login-container">
      <h1 class="login-title">Manga, K-Pop et bien plus</h1>
      <p class="login-subtitle">À partir de 999 DA. Annulable à tout moment.</p>
      
      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" class="login-form">
        <div class="form-group">
          <label for="email">Adresse e-mail</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group" style="display: none;">
          <input type="text" name="first_name" placeholder="Prénom">
          <input type="text" name="last_name" placeholder="Nom de famille">
          <input type="text" name="phone_number" placeholder="Téléphone">
          <textarea name="address" placeholder="Adresse"></textarea>
        </div>
        
        <div class="login-actions">
          <button type="submit" name="action" value="login" class="btn btn-primary">Se connecter</button>
          <button type="submit" name="action" value="register" class="btn btn-secondary">S'inscrire</button>
        </div>
      </form>
    </div>
  </div>

  <section class="trending-section">
    <h2 class="trending-title">Tendances actuelles</h2>
    <div class="trending-grid">
      <div class="trending-item">
        <img src="assets/images/rez.jpeg" alt="Tendance 1">
      </div>
      <div class="trending-item">
        <img src="assets/images/bleach.jpeg" alt="Tendance 2">
      </div>
      <div class="trending-item">
        <img src="assets/images/kaguyaa.jpg" alt="Tendance 3">
      </div>
      <div class="trending-item">
        <img src="assets/images/goblin.jpeg" alt="Tendance 4">
      </div>
      <div class="trending-item">
        <img src="assets/images/chain.jpeg" alt="Tendance 5">
      </div>
      <div class="trending-item">
        <img src="assets/images/clover.jpg" alt="Tendance 6">
      </div>
    </div>
  </section>
</body>
</html>