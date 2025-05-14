<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}


$users_result = $conn->query("SELECT * FROM users WHERE role = 'customer' ORDER BY user_id DESC");
$products_result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HB Manga Kissa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --accent: #f47521;
            --bg-dark: #0e0e10;
            --bg-card: #1f1f23;
            --text-main: #ffffff;
            --text-sub: #a0a0a0;
            --border: rgba(255,255,255,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 100px 20px 40px;
        }

        .sticky-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(14, 14, 16, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            padding: 1rem 0;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent);
        }

        .admin-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .admin-nav-item {
            color: var(--text-main);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-nav-item:hover,
        .admin-nav-item.active {
            background: rgba(244, 117, 33, 0.1);
            color: var(--accent);
        }

        .admin-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        }

        .admin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .admin-card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(244, 117, 33, 0.3);
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
        }

        .btn-danger:hover {
            background: #bb2d3b;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .admin-table th {
            background: rgba(255,255,255,0.03);
            color: var(--accent);
            font-weight: 600;
        }

        .admin-table tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .product-image {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            transition: transform 0.2s ease;
        }

        .product-image:hover {
            transform: scale(1.1);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            padding: 2rem;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding-top: 120px;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }
            
            .admin-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header class="sticky-header">
        <div class="header-container">
            <h1 class="admin-title">Admin Dashboard</h1>
            <nav class="admin-nav">
                <a href="#users" class="admin-nav-item active">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="#products" class="admin-nav-item">
                    <i class="fas fa-box-open"></i> Products
                </a>
                <a href="../z_index.php" class="admin-nav-item">
                    <i class="fas fa-store"></i> View Store
                </a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <section id="users" class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="fas fa-users-cog"></i> User Management
                </h2>
                <button class="btn btn-primary" onclick="openModal('addUserModal')">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteUser(<?= $user['user_id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="products" class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="fas fa-cubes"></i> Product Management
                </h2>
                <button class="btn btn-primary" onclick="openModal('addProductModal')">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     class="product-image"
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= number_format($product['price'], 2) ?> DA</td>
                            <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteProduct(<?= $product['product_id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('actions/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting user');
                    }
                });
            }
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('actions/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting product');
                    }
                });
            }
        }

        document.querySelectorAll('.admin-nav-item').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                const targetId = item.getAttribute('href').slice(1);
                
                document.querySelectorAll('.admin-nav-item').forEach(nav => 
                    nav.classList.remove('active'));
                item.classList.add('active');
                
                document.querySelectorAll('section').forEach(section => {
                    section.style.display = section.id === targetId ? 'block' : 'none';
                });
            });
        });

        document.getElementById('products').style.display = 'none';
    </script>
</body>
</html>