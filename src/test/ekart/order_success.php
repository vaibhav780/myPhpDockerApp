<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['last_order_id'])) {
    header("Location: products.php");
    exit;
}

$order_id = $_SESSION['last_order_id'];
// Clear the order ID from session so refresh doesn't keep it forever
unset($_SESSION['last_order_id']); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmed</title>
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

        .page-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 160px);
        }

        .success-container {
            background: white;
            padding: 60px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 500px;
            width: 100%;
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

        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 50px;
            color: white;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            color: #333;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .order-number {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 20px;
            font-weight: 600;
            margin: 20px 0;
        }

        .return-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 14px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .return-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .return-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .success-container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 26px;
            }

            .success-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }

            .order-number {
                font-size: 18px;
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
    </div>
            <a href="user_profile.php" class="profile-link">
                👤 Profile
            </a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="page-content">
        <div class="success-container">
        <div class="success-icon">✓</div>
        <h1 id="success_msg">Thank you for your order!</h1>
        <p>Your order has been placed successfully.</p>
        <div class="order-number">
            <span id="order_number">#<?= $order_id ?></span>
        </div>
        <a href="products.php" class="return-btn">Return to Shop</a>
    </div>
</body>
</html>