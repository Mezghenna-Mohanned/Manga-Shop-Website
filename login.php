<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter a valid email and password.';
    } else {
        if ($action === 'login') {
            $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($userId, $hashedPassword, $role);
                $stmt->fetch();
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['is_admin'] = ($role === 'admin');
                    header('Location: ' . ($_SESSION['is_admin'] ? 'admin/dashboard.php' : 'z_index.php'));
                    exit;
                } else {
                    $error = 'Incorrect password.';
                }
            } else {
                $error = 'No account found with this email.';
            }
        } elseif ($action === 'register') {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if ($first_name && $last_name) {
                $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $chk->bind_param('s', $email);
                $chk->execute();
                $chk->store_result();

                if ($chk->num_rows > 0) {
                    $error = 'This email is already registered.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'customer'; // Default role for new users
                    $ins = $conn->prepare("
                        INSERT INTO users 
                        (first_name, last_name, email, password, role, phone_number, address)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $ins->bind_param('sssssss',
                        $first_name,
                        $last_name,
                        $email,
                        $hashed,
                        $role,
                        $phone_number,
                        $address
                    );
                    if ($ins->execute()) {
                        $_SESSION['user_id'] = $ins->insert_id;
                        $_SESSION['is_admin'] = false;
                        header('Location: z_index.php');
                        exit;
                    } else {
                        $error = 'Registration failed.';
                    }
                }
            } else {
                $error = 'Please enter your first and last name.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - HB Manga Kissa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --accent: #f47521;
            --bg-dark: #0e0e10;
            --bg-card: #1b1b1e;
            --text-main: #fff;
            --text-sub: #bbb;
        }

        body {
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(rgba(14, 14, 16, 0.8), rgba(14, 14, 16, 0.9)),
                        url('assets/images/image032.png') center/cover fixed;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
        }

        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .auth-box {
            width: 100%;
            max-width: 400px;
            background: rgba(27, 27, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, var(--accent), #ff9f5a);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-subtitle {
            color: var(--text-sub);
            font-size: 0.9rem;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group {
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-sub);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.08);
        }

        .auth-btn {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 0.875rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 117, 33, 0.3);
        }

        .auth-switch {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-sub);
        }

        .auth-switch button {
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
            font-weight: 500;
            padding: 0.25rem;
        }

        .auth-switch button:hover {
            text-decoration: underline;
        }

        .error-message {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .register-fields {
            display: none;
        }

        .register-fields.active {
            display: block;
        }

        @media (max-width: 480px) {
            .auth-box {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1 class="auth-title">HB Manga Kissa</h1>
                <p class="auth-subtitle">Welcome back to your manga universe</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form" id="authForm">
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email address" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <div class="register-fields">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="first_name" class="form-control" placeholder="First name">
                    </div>

                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="last_name" class="form-control" placeholder="Last name">
                    </div>

                    <div class="form-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone_number" class="form-control" placeholder="Phone number">
                    </div>

                    <div class="form-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="address" class="form-control" placeholder="Address">
                    </div>
                </div>

                <input type="hidden" name="action" value="login" id="formAction">
                <button type="submit" class="auth-btn" id="submitBtn">Sign In</button>

                <div class="auth-switch">
                    <span id="switchText">Don't have an account?</span>
                    <button type="button" id="switchBtn">Sign Up</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('authForm');
        const registerFields = document.querySelector('.register-fields');
        const switchBtn = document.getElementById('switchBtn');
        const switchText = document.getElementById('switchText');
        const submitBtn = document.getElementById('submitBtn');
        const formAction = document.getElementById('formAction');
        let isLogin = true;

        switchBtn.addEventListener('click', () => {
            isLogin = !isLogin;
            registerFields.classList.toggle('active');
            formAction.value = isLogin ? 'login' : 'register';
            submitBtn.textContent = isLogin ? 'Sign In' : 'Sign Up';
            switchBtn.textContent = isLogin ? 'Sign Up' : 'Sign In';

            switchText.textContent = isLogin ? 'Dont have an account?' : 'Already have an account?';

            switchText.textContent = isLogin ? 'Dont have an account?' : 'Already have an account?';
        });
    </script>
</body>
</html>