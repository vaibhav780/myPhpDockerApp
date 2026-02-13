<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['ekart_admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Add category column to products table if it doesn't exist
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN category VARCHAR(100) DEFAULT 'Uncategorized' AFTER price");
    }
} catch (PDOException $e) {
    // Column might already exist
}

$message = "";
$message_type = "";

// Define product categories
$categories = [
    'Electronics',
    'Clothing',
    'Books',
    'Home & Kitchen',
    'Sports & Outdoors',
    'Toys & Games',
    'Health & Beauty',
    'Automotive',
    'Food & Beverages',
    'Other'
];

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $upload_path;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, category, image, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $category, $image, $description]);
        $message = "Product added successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error adding product: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    
    // Get current image
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current_product = $stmt->fetch();
    $image = $current_product['image'];

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                if ($image && file_exists($image)) {
                    unlink($image);
                }
                $image = $upload_path;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, category = ?, image = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $price, $category, $image, $description, $id]);
        $message = "Product updated successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error updating product: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $id = intval($_POST['product_id']);
    
    try {
        // Get image path before deleting
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file
        if ($product && $product['image'] && file_exists($product['image'])) {
            unlink($product['image']);
        }
        
        $message = "Product deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error deleting product: " . $e->getMessage();
        $message_type = "error";
    }
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - eKart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-header {
            max-width: 1400px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 12px;
            padding: 20px 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .admin-logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-badge {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }

        .admin-logout {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .admin-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 600;
        }

        .message {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .error {
            background-color: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .add-product-section {
            background: #f8f9ff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
        }

        .add-product-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 14px;
            color: #555;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"] {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
            background-color: white;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-edit {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            font-size: 13px;
            margin-right: 8px;
        }

        .btn-edit:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .products-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }

        .products-table table {
            width: 100%;
            min-width: 800px;
        }

        .products-table thead {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }

        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
        }

        .products-table th {
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .products-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.2s ease;
        }

        .products-table tbody tr:hover {
            background-color: #f8f9ff;
        }

        .products-table tbody tr:last-child {
            border-bottom: none;
        }

        .products-table td {
            color: #555;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 40px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 32px;
            font-weight: bold;
            line-height: 20px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover,
        .close:focus {
            color: #000;
        }

        .modal h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .admin-header {
                padding: 15px 20px;
            }

            h1 {
                font-size: 26px;
            }

            .add-product-section {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .products-table {
                font-size: 14px;
            }

            .products-table th,
            .products-table td {
                padding: 10px 8px;
            }

            .product-image {
                width: 60px;
                height: 60px;
            }

            .modal-content {
                width: 95%;
                padding: 25px;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div>
            <span class="admin-logo">🛒 eKart</span>
            <span class="admin-badge">ADMIN PANEL</span>
        </div>
        <a href="admin_logout.php" class="admin-logout">Logout</a>
    </div>

    <div class="container">
        <h1>Product Management</h1>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="add-product-section">
            <h2>Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_name">Product Name *</label>
                        <input type="text" name="name" id="add_name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_price">Price ($) *</label>
                        <input type="number" name="price" id="add_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="add_category">Category *</label>
                        <select name="category" id="add_category" required style="padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_image">Product Image</label>
                        <input type="file" name="image" id="add_image" accept="image/*">
                    </div>
                    <div class="form-group full-width">
                        <label for="add_description">Description</label>
                        <textarea name="description" id="add_description"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </form>
        </div>

        <!-- Products List -->
        <div class="products-section">
            <h2>All Products</h2>
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <h3>No Products Found</h3>
                    <p>Add your first product using the form above.</p>
                </div>
            <?php else: ?>
                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td>
                                    <?php if ($product['image'] && file_exists($product['image'])): ?>
                                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                    <?php else: ?>
                                        <div style="width: 80px; height: 80px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                                <td><span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></span></td>
                                <td><?= htmlspecialchars(substr($product['description'], 0, 50)) ?><?= strlen($product['description']) > 50 ? '...' : '' ?></td>
                                <td>
                                    <button class="btn btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($product), ENT_QUOTES) ?>)">Edit</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Product</h2>
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="product_id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_name">Product Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_price">Price ($) *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_category">Category *</label>
                        <select name="category" id="edit_category" required style="padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_image">Product Image (leave empty to keep current)</label>
                        <input type="file" name="image" id="edit_image" accept="image/*">
                        <div id="current_image_preview" style="margin-top: 10px;"></div>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_description">Description</label>
                        <textarea name="description" id="edit_description"></textarea>
                    </div>
                </div>
                <button type="submit" name="update_product" class="btn btn-success">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category').value = product.category || 'Other';
            document.getElementById('edit_description').value = product.description || '';
            
            const preview = document.getElementById('current_image_preview');
            if (product.image) {
                preview.innerHTML = '<img src="' + product.image + '" alt="Current Image" style="max-width: 200px; border-radius: 8px;">';
            } else {
                preview.innerHTML = '<p style="color: #999;">No current image</p>';
            }
            
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
