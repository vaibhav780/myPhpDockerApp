<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    header("Location: index.php");
    exit;
}

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

// Get product details
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header("Location: products.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching product: " . $e->getMessage());
}

// Handle add to cart
$message = "";
$message_type = "";

if (isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['ekart_user_id'];
    
    try {
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product_id]);
        }
        
        $message = "Product added to cart successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error adding to cart: " . $e->getMessage();
        $message_type = "error";
    }
}

// Get cart count
$user_id = $_SESSION['ekart_user_id'];
$cart_count_stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
$cart_count_stmt->execute([$user_id]);
$cart_count = $cart_count_stmt->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - eKart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            max-width: 1200px;
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

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-info {
            color: #666;
            font-size: 14px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .cart-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }

        .cart-badge {
            background: white;
            color: #28a745;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .profile-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .logout-btn {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
            text-decoration: underline;
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

        .product-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 20px;
        }

        .product-image-container {
            position: relative;
        }

        .product-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .no-image-large {
            width: 100%;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            border-radius: 15px;
            color: #999;
            font-size: 120px;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .product-info h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .product-price {
            color: #667eea;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .product-description {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .product-meta {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .product-meta-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .product-meta-item:last-child {
            border-bottom: none;
        }

        .product-meta-label {
            color: #666;
            font-weight: 600;
        }

        .product-meta-value {
            color: #333;
        }

        .add-to-cart-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .add-to-cart-btn:active {
            transform: translateY(0);
        }

        .continue-shopping {
            width: 100%;
            padding: 18px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            display: block;
        }

        .continue-shopping:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .product-details {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .product-info h1 {
                font-size: 2rem;
            }

            .product-price {
                font-size: 2rem;
            }

            .container {
                padding: 20px;
            }

            .header {
                padding: 15px 20px;
            }

            .header-left,
            .header-right {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo">🛒 eKart</div>
            <div class="user-info">Welcome, <strong><?= htmlspecialchars($_SESSION['ekart_user']) ?></strong></div>
        </div>
        <div class="header-right">
            <a href="cart.php" class="cart-link">
                🛒 Cart
                <span class="cart-badge"><?= $cart_count ?></span>
            </a>
            <a href="user_profile.php" class="profile-link">
                👤 Profile
            </a>
            <a href="logout.php" class="logout-btn" id="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <a href="products.php" class="back-link">← Back to Products</a>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="product-details">
            <div class="product-image-container">
                <?php if ($product['image'] && file_exists($product['image'])): ?>
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                <?php else: ?>
                    <div class="no-image-large">📦</div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-price">$<?= number_format($product['price'], 2) ?></div>

                <div style="margin-bottom: 20px;">
                    <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-block;">
                        📁 <?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?>
                    </span>
                </div>

                <?php if ($product['description']): ?>
                    <div class="product-description">
                        <strong>Description:</strong><br>
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>
                <?php endif; ?>

                <div class="product-meta">
                    <div class="product-meta-item">
                        <span class="product-meta-label">Product ID:</span>
                        <span class="product-meta-value">#<?= $product['id'] ?></span>
                    </div>
                    <div class="product-meta-item">
                        <span class="product-meta-label">Price:</span>
                        <span class="product-meta-value">$<?= number_format($product['price'], 2) ?></span>
                    </div>
                    <div class="product-meta-item">
                        <span class="product-meta-label">Category:</span>
                        <span class="product-meta-value"><?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></span>
                    </div>
                    <div class="product-meta-item">
                        <span class="product-meta-label">Availability:</span>
                        <span class="product-meta-value" style="color: #28a745; font-weight: 600;">In Stock</span>
                    </div>
                </div>

                <form method="POST" class="add-to-cart-form">
                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                        🛒 Add to Cart
                    </button>
                </form>

                <a href="products.php" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
    </div>
</body>
</html>
