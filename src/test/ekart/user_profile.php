<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['ekart_user_id'];

// Add additional user profile columns if they don't exist
try {
    $columns = ['phone', 'address', 'gender', 'city', 'state', 'zipcode', 'date_of_birth', 'interests'];
    foreach ($columns as $col) {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE '$col'");
        if ($stmt->rowCount() == 0) {
            $type = ($col === 'interests') ? 'TEXT' : (($col === 'date_of_birth') ? 'DATE' : 'VARCHAR(255)');
            $pdo->exec("ALTER TABLE users ADD COLUMN $col $type NULL");
        }
    }
} catch (PDOException $e) {
    // Columns might already exist
}

$message = "";
$message_type = "";

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zipcode = trim($_POST['zipcode']);
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $interests = trim($_POST['interests']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zipcode = ?, gender = ?, date_of_birth = ?, interests = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $address, $city, $state, $zipcode, $gender, $date_of_birth, $interests, $user_id]);
        
        $_SESSION['ekart_user'] = $username;
        $message = "Profile updated successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error updating profile: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = "New passwords do not match!";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (password_verify($current_password, $user['password'])) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user_id]);
                
                $message = "Password changed successfully!";
                $message_type = "success";
            } else {
                $message = "Current password is incorrect!";
                $message_type = "error";
            }
        } catch (PDOException $e) {
            $message = "Error changing password: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get cart count
$cart_count_stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
$cart_count_stmt->execute([$user_id]);
$cart_count = $cart_count_stmt->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - eKart</title>
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

        .profile-link,
        .cart-link,
        .products-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .profile-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .cart-link {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .products-link {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }

        .profile-link:hover,
        .cart-link:hover,
        .products-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .cart-badge {
            background: white;
            color: #28a745;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
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
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
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

        .profile-sections {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .section {
            background: #f8f9ff;
            border-radius: 15px;
            padding: 30px;
            border: 2px solid #e0e0ff;
        }

        .section h2 {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
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
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select,
        textarea {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.6);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
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
            <div class="user-info">Welcome, <strong><?= htmlspecialchars($user['username']) ?></strong></div>
        </div>
        <div class="header-right">
            <a href="products.php" class="products-link">
                🏪 Products
            </a>
            <a href="cart.php" class="cart-link">
                🛒 Cart
                <span class="cart-badge"><?= $cart_count ?></span>
            </a>
            <a href="user_profile.php" class="profile-link">
                👤 Profile
            </a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>👤 My Profile</h1>
        <p class="subtitle">Manage your account information and preferences</p>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="profile-sections">
            <!-- Personal Information -->
            <div class="section">
                <h2>📋 Personal Information</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Full Name *</label>
                            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($user['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                <option value="Prefer not to say" <?= ($user['gender'] ?? '') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    
                    <h2 style="margin-top: 30px; margin-bottom: 20px; color: #667eea; font-size: 1.3rem;">📍 Address Information</h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="address">Street Address</label>
                            <input type="text" name="address" id="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="123 Main Street, Apt 4B">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="New York">
                        </div>
                        <div class="form-group">
                            <label for="state">State/Province</label>
                            <input type="text" name="state" id="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>" placeholder="NY">
                        </div>
                        <div class="form-group">
                            <label for="zipcode">Zip/Postal Code</label>
                            <input type="text" name="zipcode" id="zipcode" value="<?= htmlspecialchars($user['zipcode'] ?? '') ?>" placeholder="10001">
                        </div>
                    </div>

                    <h2 style="margin-top: 30px; margin-bottom: 20px; color: #667eea; font-size: 1.3rem;">❤️ Interests & Preferences</h2>
                    <div class="form-group full-width">
                        <label for="interests">What are you interested in? (Used for personalized product recommendations)</label>
                        <textarea name="interests" id="interests" placeholder="e.g., Electronics, Books, Fashion, Sports Equipment, Gaming, Home Decor, Fitness..."><?= htmlspecialchars($user['interests'] ?? '') ?></textarea>
                        <small style="color: #666; font-size: 13px; margin-top: 5px; display: block;">
                            💡 Tip: Add keywords separated by commas. Use the "My Likes" button on the products page to see items matching your interests!
                        </small>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top: 20px;">💾 Update Profile</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="section">
                <h2>🔒 Change Password</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="current_password">Current Password *</label>
                            <input type="password" name="current_password" id="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" name="new_password" id="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" name="confirm_password" id="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-secondary" style="margin-top: 20px;">🔑 Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
