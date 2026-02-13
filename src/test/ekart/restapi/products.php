<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$productId = $_GET['id'] ?? null;

// GET /products.php - Get all products OR /products.php?id=1 - Get specific product
if ($method === 'GET') {
    try {
        if ($productId) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                sendError('Product not found', 404);
            }
            
            sendResponse($product);
        } else {
            // Support filtering
            $category = $_GET['category'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $sql = "SELECT * FROM products WHERE 1=1";
            $params = [];
            
            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            if ($search) {
                $sql .= " AND (name LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            
            sendResponse(['products' => $products]);
        }
    } catch (PDOException $e) {
        sendError('Failed to fetch products: ' . $e->getMessage(), 500);
    }
}

// POST /products.php - Create new product (requires authentication)
if ($method === 'POST') {
    $userData = verifyToken();
    $data = getRequestData();
    
    if (empty($data['name']) || empty($data['price'])) {
        sendError('Product name and price are required');
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, category, image, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['price'],
            $data['category'] ?? 'Uncategorized',
            $data['image'] ?? null,
            $data['description'] ?? null
        ]);
        
        $productId = $pdo->lastInsertId();
        
        sendResponse([
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ], 201);
        
    } catch (PDOException $e) {
        sendError('Failed to create product: ' . $e->getMessage(), 500);
    }
}

// PUT /products.php?id=1 - Update product (requires authentication)
if ($method === 'PUT') {
    $userData = verifyToken();
    
    if (!$productId) {
        sendError('Product ID is required');
    }
    
    $data = getRequestData();
    
    try {
        $allowedFields = ['name', 'price', 'category', 'image', 'description'];
        $updates = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            sendError('No valid fields to update');
        }
        
        $values[] = $productId;
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        if ($stmt->rowCount() === 0) {
            sendError('Product not found', 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
        
    } catch (PDOException $e) {
        sendError('Failed to update product: ' . $e->getMessage(), 500);
    }
}

// DELETE /products.php?id=1 - Delete product (requires authentication)
if ($method === 'DELETE') {
    $userData = verifyToken();
    
    if (!$productId) {
        sendError('Product ID is required');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() === 0) {
            sendError('Product not found', 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        sendError('Failed to delete product: ' . $e->getMessage(), 500);
    }
}

sendError('Invalid request method', 405);
?>
