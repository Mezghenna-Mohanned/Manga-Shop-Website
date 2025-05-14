<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

require_once '../config.php';

// Fetch users
$users_query = "SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC";
$users_result = $conn->query($users_query);

// Fetch products
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products_result = $conn->query($products_query);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HB Manga Kissa</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .admin-title {
            font-size: 2rem;
            color: var(--text-main);
        }

        .admin-nav {
            display: flex;
            gap: 20px;
        }

        .admin-nav-item {
            padding: 10px 20px;
            background: var(--bg-card);
            border-radius: 8px;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.3s;
        }

        .admin-nav-item:hover,
        .admin-nav-item.active {
            background: var(--accent);
            color: #000;
        }

        .admin-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .admin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-table th {
            color: var(--accent);
            font-weight: bold;
        }

        .admin-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-weight: bold;
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            padding: 20px;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-sub);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            color: var(--text-main);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header class="sticky-header">
        <div class="header-container">
            <h1 class="admin-title">Admin Dashboard</h1>
            <nav class="admin-nav">
                <a href="#users" class="admin-nav-item active">Users</a>
                <a href="#products" class="admin-nav-item">Products</a>
                <a href="../z_index.php" class="admin-nav-item">
                    <i class="fas fa-store"></i> View Store
                </a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <!-- Users Section -->
        <section id="users" class="admin-card">
            <div class="admin-card-header">
                <h2>Users Management</h2>
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
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
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

        <!-- Products Section -->
        <section id="products" class="admin-card">
            <div class="admin-card-header">
                <h2>Products Management</h2>
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
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['price']) ?> DA</td>
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

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h2>Add New User</h2>
            <form id="addUserForm" action="actions/add_user.php" method="POST">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h2>Add New Product</h2>
            <form id="addProductForm" action="actions/add_product.php" method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Price (DA)</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image_url" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('addProductModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
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

        // Tab navigation
        const navItems = document.querySelectorAll('.admin-nav-item');
        const sections = document.querySelectorAll('section');

        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (item.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const targetId = item.getAttribute('href').slice(1);
                    
                    navItems.forEach(nav => nav.classList.remove('active'));
                    item.classList.add('active');
                    
                    sections.forEach(section => {
                        section.style.display = section.id === targetId ? 'block' : 'none';
                    });
                }
            });
        });

        // Initially hide products section
        document.getElementById('products').style.display = 'none';
    </script>
</body>
</html>