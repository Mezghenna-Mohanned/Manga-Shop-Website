<?php
require 'config.php';  // Start session and connect to the database

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? ''; 
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($email && $password) {
        // Check if the user already exists
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User exists, check password
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
            // If user doesn't exist, register a new user
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone_number, address) VALUES (?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param('ssssss', $first_name, $last_name, $email, $password_hashed, $phone_number, $address);

            if ($insertStmt->execute()) {
                $userId = $insertStmt->insert_id;
                $_SESSION['user_id'] = $userId;
                header('Location: z_index.php');
                exit;
            } else {
                $error = 'Erreur lors de l\'inscription.';
            }
        }
    } else {
        $error = 'Veuillez saisir une adresse e-mail valide et un mot de passe.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MangaFlix</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles1.css">
  <style>
    /* Flexbox for form layout */
    .input-wrapper {
      display: flex;
      flex-wrap: wrap;
      gap: 15px; /* Space between the input fields */
      max-width: 600px;
      margin: 0 auto;
    }

    .input-wrapper input, .input-wrapper textarea {
      width: 48%; /* Make the inputs take 48% of the width (two inputs per row) */
      padding: 10px;
      font-size: 16px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .input-wrapper textarea {
      width: 100%; /* Textarea should take full width */
    }

    button {
      padding: 15px;
      background-color: #FF7A00; /* Orange button color */
      color: white;
      font-size: 18px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%; /* Full width for the button */
      margin-top: 20px;
    }

    button:hover {
      background-color: #E66E00; /* Darker orange on hover */
    }

    .error-message {
      color: red;
      font-size: 16px;
      text-align: center;
    }

    h1, h2 {
      text-align: center;
    }

    .hero-content {
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="hero-box">
    <header class="hero-header">
      <!-- Removed top buttons for language and sign-in -->
    </header>

    <section class="hero-content">
      <h1>Anime, Mangas et Goodies illimités, et bien plus</h1>
      <h2>À partir de 750 DZD/mois. Annulable à tout moment.</h2>
      <p>Prêt à découvrir MangaFlix ? Saisissez vos informations pour vous abonner ou réactiver votre compte.</p>

      <?php if($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="input-wrapper" method="post" action="login.php">
        <input type="text" name="first_name" placeholder="Prénom" required>
        <input type="text" name="last_name" placeholder="Nom de famille" required>
        <input type="email" name="email" placeholder="Adresse e-mail" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="text" name="phone_number" placeholder="Numéro de téléphone" required>
        <textarea name="address" placeholder="Adresse" required></textarea>
        <button type="submit">Confirmer</button>
      </form>
    </section>
  </div>
</body>
</html>
