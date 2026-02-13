<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// POST /auth.php - Register new user
// POST /auth.php?action=login - Login user
$action = $_GET['action'] ?? 'register';

if ($method === 'POST' && $action === 'register') {
    $data = getRequestData();
    
    if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
        sendError('Username, email, and password are required');
    }
    
    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);
        
        if ($stmt->fetch()) {
            sendError('Username or email already exists', 409);
        }
        
        // Create user
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$data['username'], $data['email'], $hashedPassword]);
        
        $userId = $pdo->lastInsertId();
        $token = generateToken($userId, $data['username']);
        
        sendResponse([
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $userId,
            'token' => $token
        ], 201);
        
    } catch (PDOException $e) {
        sendError('Registration failed: ' . $e->getMessage(), 500);
    }
}

if ($method === 'POST' && $action === 'login') {
    $data = getRequestData();
    
    if (empty($data['username']) || empty($data['password'])) {
        sendError('Username and password are required');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['username']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            sendError('Invalid credentials', 401);
        }
        
        $token = generateToken($user['id'], $user['username']);
        
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'user_id' => $user['id'],
            'username' => $user['username'],
            'token' => $token
        ]);
        
    } catch (PDOException $e) {
        sendError('Login failed: ' . $e->getMessage(), 500);
    }
}

sendError('Invalid request', 400);
?>
