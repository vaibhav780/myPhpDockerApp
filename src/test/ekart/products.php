<?php
session_start();
include 'db.php'; // Assume this contains your PDO connection

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    header("Location: index.php");
    exit;
}

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
    // Table might already exist or foreign key might fail
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

// Add interests column to users table if it doesn't exist
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'interests'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN interests TEXT NULL");
    }
} catch (PDOException $e) {
    // Column might already exist
}

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';
$category_filter = $_GET['category'] ?? '';
$likes_filter = isset($_GET['likes']) && $_GET['likes'] === '1';

// Get user's interests/likes
$user_id = $_SESSION['ekart_user_id'];
$user_stmt = $pdo->prepare("SELECT interests FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_interests = $user_stmt->fetchColumn();
$user_interests = $user_interests ?: ''; // Ensure it's not null

// Validate sort parameter to prevent SQL injection
$allowed_sorts = [
    'name_asc' => 'name ASC',
    'name_desc' => 'name DESC',
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC'
];

if (!isset($allowed_sorts[$sort])) {
    $sort = 'name_asc';
}

$order_by = $allowed_sorts[$sort];

// Build query with category filter and search in name and description
$query = "SELECT * FROM products WHERE (name LIKE :search OR description LIKE :search)";
$params = ['search' => "%$search%"];

// Add likes filter if enabled and user has interests
if ($likes_filter && !empty($user_interests)) {
    // Parse interests (comma or comma-space separated)
    $interests = array_map('trim', preg_split('/[,;]+/', $user_interests));
    $interests = array_filter($interests); // Remove empty values
    
    if (!empty($interests)) {
        $like_conditions = [];
        foreach ($interests as $index => $interest) {
            $param_name = "interest_$index";
            $like_conditions[] = "(name LIKE :$param_name OR description LIKE :$param_name OR category LIKE :$param_name)";
            $params[$param_name] = "%$interest%";
        }
        if (!empty($like_conditions)) {
            $query .= " AND (" . implode(' OR ', $like_conditions) . ")";
        }
    }
}

if ($category_filter && $category_filter !== 'all') {
    $query .= " AND category = :category";
    $params['category'] = $category_filter;
}
$query .= " ORDER BY $order_by";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all unique categories for filter
$categories_stmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get cart count
$cart_count_stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
$cart_count_stmt->execute([$user_id]);
$cart_count = $cart_count_stmt->fetchColumn() ?? 0;

// Check if user has interests
$has_interests = !empty($user_interests);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKart - Products</title>
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

        h2 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        form {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
        }

        input[type="text"],
        select {
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }

        input[type="text"] {
            flex: 1;
            min-width: 300px;
            padding-left: 40px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%23667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>');
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 18px;
        }

        .search-wrapper {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #667eea;
            border-top: none;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .search-suggestions.active {
            display: block;
        }

        .suggestion-item {
            padding: 12px 40px;
            cursor: pointer;
            transition: background 0.2s ease;
            font-size: 15px;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover,
        .suggestion-item.selected {
            background: linear-gradient(135deg, #f0f0ff 0%, #f8f8ff 100%);
            color: #667eea;
        }

        .suggestion-item mark {
            background: #fff4b3;
            font-weight: 600;
            color: #667eea;
            padding: 2px 0;
        }

        .no-suggestions {
            padding: 12px 40px;
            color: #999;
            font-size: 14px;
            text-align: center;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        select {
            cursor: pointer;
            background: white;
        }

        button[type="submit"] {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .clear-btn {
            padding: 12px 24px;
            background: #f0f0f0;
            color: #666;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .clear-btn:hover {
            background: #e0e0e0;
            border-color: #d0d0d0;
        }

        .likes-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .likes-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.5);
        }

        .likes-btn.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .likes-btn.active:hover {
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.5);
        }

        .likes-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .product-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid #f0f0f0;
            display: flex;
            flex-direction: column;
        }

        .product-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border-color: #667eea;
        }

        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            background: #f0f0f0;
        }

        .no-image {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            border-radius: 10px;
            margin-bottom: 15px;
            color: #999;
            font-size: 48px;
        }

        .product-item h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 10px;
            min-height: 50px;
        }

        .product-item p {
            color: #667eea;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .view-details {
            display: inline-block;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .view-details:hover {
            background: #667eea;
            color: white;
        }

        .product-item form {
            margin: 0;
            margin-top: auto;
        }

        .product-item form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .product-item form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #666;
        }

        .empty-state p {
            font-size: 16px;
            color: #999;
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }

            .header-left,
            .header-right {
                width: 100%;
                justify-content: space-between;
            }

            .container {
                padding: 20px;
            }

            h2 {
                font-size: 2rem;
            }

            form {
                flex-direction: column;
                width: 100%;
            }

            input[type="text"],
            select,
            button[type="submit"] {
                width: 100%;
            }

            .product-grid {
                grid-template-columns: 1fr;
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
        <h2>🔍 Find Your Products</h2>
        <form method="GET">
            <div class="search-wrapper">
                <input type="text" name="search" id="search_input" value="<?= htmlspecialchars($search) ?>" placeholder="🔎 Search by product name or description..." autocomplete="off">
                <div class="search-suggestions" id="suggestions"></div>
            </div>
            <select name="category" id="category_select" onchange="this.form.submit()">
                <option value="all" <?= $category_filter === '' || $category_filter === 'all' ? 'selected' : '' ?>>All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" id="sort_select" onchange="this.form.submit()">
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
            </select>
            <input type="hidden" name="likes" value="<?= $likes_filter ? '1' : '0' ?>" id="likes_input">
            <button type="submit">🔍 Search</button>
            <?php if ($has_interests): ?>
                <a href="#" onclick="event.preventDefault(); toggleLikes();" class="likes-btn <?= $likes_filter ? 'active' : '' ?>" id="likes_btn" title="<?= $likes_filter ? 'Showing products matching your interests' : 'Show products matching your interests' ?>">
                    ❤️ <?= $likes_filter ? 'My Likes (ON)' : 'My Likes' ?>
                </a>
            <?php else: ?>
                <a href="user_profile.php" class="likes-btn" style="opacity: 0.6;" title="Add interests in your profile to use this feature">
                    ❤️ My Likes
                </a>
            <?php endif; ?>
            <?php if ($search || $category_filter || $likes_filter): ?>
                <a href="products.php" class="clear-btn">✕ Clear All</a>
            <?php endif; ?>
        </form>

        <?php if ($likes_filter && !empty($user_interests)): ?>
            <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">
                <span style="font-size: 24px;">❤️</span>
                <div>
                    <strong style="font-size: 16px; display: block; margin-bottom: 4px;">Showing products matching your interests:</strong>
                    <span style="opacity: 0.95; font-size: 14px;"><?= htmlspecialchars($user_interests) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="product-grid">
            <?php if (empty($products)): ?>
                <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <div style="font-size: 80px; margin-bottom: 20px;">🔍</div>
                    <h3 style="color: #333; font-size: 24px; margin-bottom: 10px;">No Products Found</h3>
                    <?php if ($likes_filter): ?>
                        <p style="color: #666; font-size: 16px;">No products match your interests: "<strong><?= htmlspecialchars($user_interests) ?></strong>"</p>
                        <p style="color: #999; margin-top: 10px;">Try updating your <a href="user_profile.php" style="color: #667eea; text-decoration: underline;">interests in your profile</a> or <a href="products.php" style="color: #667eea; text-decoration: underline;">browse all products</a></p>
                    <?php elseif ($search): ?>
                        <p style="color: #666; font-size: 16px;">No products match "<strong><?= htmlspecialchars($search) ?></strong>"</p>
                        <p style="color: #999; margin-top: 10px;">Try different keywords or <a href="products.php" style="color: #667eea; text-decoration: underline;">browse all products</a></p>
                    <?php else: ?>
                        <p style="color: #666; font-size: 16px;">No products match your search criteria.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-item">
                        <a href="product_info.php?id=<?= $p['id'] ?>" style="text-decoration: none; color: inherit;">
                            <?php if ($p['image'] && file_exists($p['image'])): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="cursor: pointer;">
                            <?php else: ?>
                                <div class="no-image" style="cursor: pointer;">📦</div>
                            <?php endif; ?>
                        </a>
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <p class="category-badge" style="font-size: 12px; color: #667eea; background: #f0f0ff; padding: 4px 12px; border-radius: 12px; display: inline-block; margin-bottom: 8px; font-weight: 600;"><?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?></p>
                        <p style="font-size: 1.5rem; color: #667eea; font-weight: bold;">$<?= number_format($p['price'], 2) ?></p>
                        <a href="product_info.php?id=<?= $p['id'] ?>" class="view-details">View Details</a>
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search_input');
        const suggestionsDiv = document.getElementById('suggestions');
        let currentFocus = -1;
        let debounceTimer;

        // Debounce function to limit API calls
        function debounce(func, delay) {
            return function(...args) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Fetch suggestions from server
        async function fetchSuggestions(query) {
            if (query.length < 1) {
                hideSuggestions();
                return;
            }

            try {
                const response = await fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`);
                const suggestions = await response.json();
                displaySuggestions(suggestions, query);
            } catch (error) {
                console.error('Error fetching suggestions:', error);
                hideSuggestions();
            }
        }

        // Display suggestions in dropdown
        function displaySuggestions(suggestions, query) {
            if (suggestions.length === 0) {
                suggestionsDiv.innerHTML = '<div class="no-suggestions">No matching products found</div>';
                suggestionsDiv.classList.add('active');
                return;
            }

            // Highlight matching text
            const regex = new RegExp(`(${query})`, 'gi');
            const html = suggestions.map(suggestion => {
                const highlighted = suggestion.replace(regex, '<mark>$1</mark>');
                return `<div class="suggestion-item" data-value="${suggestion}">${highlighted}</div>`;
            }).join('');

            suggestionsDiv.innerHTML = html;
            suggestionsDiv.classList.add('active');

            // Add click handlers
            document.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', function() {
                    searchInput.value = this.getAttribute('data-value');
                    hideSuggestions();
                    searchInput.form.submit();
                });
            });
        }

        // Hide suggestions
        function hideSuggestions() {
            suggestionsDiv.classList.remove('active');
            suggestionsDiv.innerHTML = '';
            currentFocus = -1;
        }

        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            const items = suggestionsDiv.querySelectorAll('.suggestion-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocus++;
                if (currentFocus >= items.length) currentFocus = 0;
                setActive(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocus--;
                if (currentFocus < 0) currentFocus = items.length - 1;
                setActive(items);
            } else if (e.key === 'Enter') {
                if (currentFocus > -1 && items[currentFocus]) {
                    e.preventDefault();
                    items[currentFocus].click();
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        // Set active suggestion
        function setActive(items) {
            items.forEach((item, index) => {
                item.classList.toggle('selected', index === currentFocus);
            });
            if (items[currentFocus]) {
                items[currentFocus].scrollIntoView({ block: 'nearest' });
            }
        }

        // Handle input with debounce
        searchInput.addEventListener('input', debounce(function() {
            currentFocus = -1;
            fetchSuggestions(this.value.trim());
        }, 300));

        // Handle focus
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                fetchSuggestions(this.value.trim());
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput && !suggestionsDiv.contains(e.target)) {
                hideSuggestions();
            }
        });

        // Toggle likes filter
        function toggleLikes() {
            const likesInput = document.getElementById('likes_input');
            const currentValue = likesInput.value;
            likesInput.value = currentValue === '1' ? '0' : '1';
            
            // Submit the form
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>