<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$userData = verifyToken(); // All order operations require authentication
$orderId = $_GET['id'] ?? null;

// GET /orders.php - Get all orders for authenticated user OR /orders.php?id=1 - Get specific order
if ($method === 'GET') {
    try {
        if ($orderId) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $userData['user_id']]);
            $order = $stmt->fetch();
            
            if (!$order) {
                sendError('Order not found', 404);
            }
            
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name as product_name, p.price as product_price 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll();
            
            sendResponse($order);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userData['user_id']]);
            $orders = $stmt->fetchAll();
            
            sendResponse(['orders' => $orders]);
        }
    } catch (PDOException $e) {
        sendError('Failed to fetch orders: ' . $e->getMessage(), 500);
    }
}

// POST /orders.php - Create new order
if ($method === 'POST') {
    $data = getRequestData();
    
    if (empty($data['full_name']) || empty($data['address']) || empty($data['items'])) {
        sendError('Full name, address, and items are required');
    }
    
    if (!is_array($data['items']) || empty($data['items'])) {
        sendError('Items must be a non-empty array');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Calculate total
        $total = 0;
        foreach ($data['items'] as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                $pdo->rollBack();
                sendError('Each item must have product_id and quantity');
            }
            
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch();
            
            if (!$product) {
                $pdo->rollBack();
                sendError('Product not found: ' . $item['product_id'], 404);
            }
            
            $total += $product['price'] * $item['quantity'];
        }
        
        // Create order
        $orderNumber = 'ORD-' . time() . '-' . rand(1000, 9999);
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, full_name, address, payment_method, total, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $userData['user_id'],
            $orderNumber,
            $data['full_name'],
            $data['address'],
            $data['payment_method'] ?? 'Cash on Delivery',
            $total
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Create order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($data['items'] as $item) {
            $productStmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $productStmt->execute([$item['product_id']]);
            $product = $productStmt->fetch();
            
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $product['price']
            ]);
        }
        
        $pdo->commit();
        
        sendResponse([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total' => $total
        ], 201);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendError('Failed to create order: ' . $e->getMessage(), 500);
    }
}

// PUT /orders.php?id=1 - Update order (only status can be updated)
if ($method === 'PUT') {
    if (!$orderId) {
        sendError('Order ID is required');
    }
    
    $data = getRequestData();
    
    try {
        // Check if order belongs to user
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userData['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendError('Order not found', 404);
        }
        
        // Only allow updating specific fields
        $allowedFields = ['full_name', 'address', 'payment_method', 'status'];
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
        
        $values[] = $orderId;
        $values[] = $userData['user_id'];
        
        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        sendResponse([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
        
    } catch (PDOException $e) {
        sendError('Failed to update order: ' . $e->getMessage(), 500);
    }
}

// DELETE /orders.php?id=1 - Delete/Cancel order
if ($method === 'DELETE') {
    if (!$orderId) {
        sendError('Order ID is required');
    }
    
    try {
        // Check if order belongs to user and can be cancelled
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userData['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendError('Order not found', 404);
        }
        
        if ($order['status'] === 'completed' || $order['status'] === 'shipped') {
            sendError('Cannot cancel completed or shipped orders', 400);
        }
        
        $pdo->beginTransaction();
        
        // Delete order items
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        // Delete order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userData['user_id']]);
        
        $pdo->commit();
        
        sendResponse([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendError('Failed to cancel order: ' . $e->getMessage(), 500);
    }
}

sendError('Invalid request method', 405);
?>
