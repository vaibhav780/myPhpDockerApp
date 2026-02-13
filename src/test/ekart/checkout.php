<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['ekart_user_id'];

// Check if cart has items
$stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = $stmt->fetchColumn();

if ($cart_count == 0) {
    header("Location: products.php");
    exit;
}

// Get cart items for display
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price
    FROM cart_items c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Basic Validation
    if (!empty($_POST['full_name']) && !empty($_POST['address'])) {
        try {
            // Create orders table if not exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                full_name VARCHAR(100) NOT NULL,
                address TEXT NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            
            // Generate order number
            $order_number = 'ORD-' . time() . '-' . rand(1000, 9999);
            
            // Insert order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, full_name, address, payment_method, total) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $order_number,
                $_POST['full_name'],
                $_POST['address'],
                $_POST['payment'] ?? 'cod',
                $total
            ]);
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['last_order_id'] = $order_number;
            header("Location: order_success.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error processing order: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all shipping details.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
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

        .checkout-container {
            max-width: 600px;
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

        .error {
            background-color: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 14px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .order-summary {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .order-summary h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 18px;
            color: #667eea;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
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

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-size: 14px;
            color: #555;
            font-weight: 600;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        select {
            cursor: pointer;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        @media (max-width: 640px) {
            .checkout-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 24px;
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

    <div class="checkout-container">
        <a href="cart.php" class="back-link">← Back to Cart</a>
        <h2>Checkout</h2>
        
        <?php if (isset($error)): ?>
            <p class="error" id="checkout_error"><?= $error ?></p>
        <?php endif; ?>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                    <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-item">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="billing_name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Shipping Address</label>
                <textarea name="address" id="billing_address" placeholder="Enter your shipping address" required></textarea>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment" id="payment_method">
                    <option value="cod">Cash on Delivery</option>
                    <option value="card">Credit Card (Mock)</option>
                </select>
            </div>
            <button type="submit" id="place_order_btn">Place Order</button>
        </form>
    </div>
</body>
</html>