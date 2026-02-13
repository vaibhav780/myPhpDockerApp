<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['ekart_user_id'];

// Create cart_items table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_product (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {
    // Table might already exist
}

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    
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
        
        header("Location: cart.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error adding to cart: " . $e->getMessage();
    }
}

// Handle removing items from cart
if (isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        header("Location: cart.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error removing item: " . $e->getMessage();
    }
}

// Handle updating quantity
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    
    try {
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
        header("Location: cart.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating quantity: " . $e->getMessage();
    }
}

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.description
    FROM cart_items c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .products-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .products-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
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

        .cart-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            overflow: hidden;
            border-radius: 8px;
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8f9ff;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            color: #555;
        }

        .remove-btn {
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9ff;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .cart-summary h3 {
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        .cart-summary span {
            color: #667eea;
            font-size: 28px;
            font-weight: 700;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 18px;
        }

        .empty-cart a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-cart a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .qty-input {
            width: 60px;
            padding: 5px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            text-align: center;
        }

        .update-btn {
            padding: 5px 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-left: 5px;
        }

        .update-btn:hover {
            background: #764ba2;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .cart-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 24px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px 8px;
            }

            .cart-summary {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .product-image {
                width: 40px;
                height: 40px;
            }

            .product-info {
                flex-direction: column;
                gap: 5px;
                align-items: flex-start;
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
            <a href="products.php" class="products-link">
                🏪 Products
            </a>
            <a href="user_profile.php" class="profile-link">
                👤 Profile
            </a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="cart-container">
        <a href="products.php" class="back-link">← Back to Products</a>
        <h2>Your Shopping Cart</h2>
        
        <?php if (isset($error)): ?>
            <div style="background: #fee; color: #c33; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="products.php">Start Shopping</a>
            </div>
        <?php else: ?>
            <table id="cart_table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <?php if ($item['image']): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                        </td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                <button type="submit" name="update_quantity" class="update-btn">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <h3>Total:</h3>
                <h3>$<span id="cart_total"><?= number_format($total, 2) ?></span></h3>
            </div>
            <a href="checkout.php" id="checkout_btn" class="checkout-btn">Proceed to Checkout</a>
        <?php endif; ?>
    </div>
</body>
</html>