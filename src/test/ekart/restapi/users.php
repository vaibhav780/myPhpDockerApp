<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$userData = verifyToken(); // Verify JWT token
$userId = $_GET['id'] ?? null;

// GET /users.php - Get all users OR /users.php?id=1 - Get specific user
if ($method === 'GET') {
    try {
        if ($userId) {
            $stmt = $pdo->prepare("SELECT id, username, email, phone, address, city, state, zipcode, gender, date_of_birth, interests, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                sendError('User not found', 404);
            }
            
            sendResponse($user);
        } else {
            $stmt = $pdo->query("SELECT id, username, email, phone, address, city, state, zipcode, gender, date_of_birth, interests, created_at FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll();
            sendResponse(['users' => $users]);
        }
    } catch (PDOException $e) {
        sendError('Failed to fetch users: ' . $e->getMessage(), 500);
    }
}

// PUT /users.php?id=1 - Update user profile
if ($method === 'PUT') {
    if (!$userId) {
        sendError('User ID is required');
    }
    
    // Users can only update their own profile (unless admin)
    if ($userData['user_id'] != $userId) {
        sendError('Unauthorized to update this profile', 403);
    }
    
    $data = getRequestData();
    
    try {
        $allowedFields = ['username', 'email', 'phone', 'address', 'city', 'state', 'zipcode', 'gender', 'date_of_birth', 'interests'];
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
        
        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        sendResponse([
            'success' => true,
            'message' => 'User profile updated successfully'
        ]);
        
    } catch (PDOException $e) {
        sendError('Failed to update user: ' . $e->getMessage(), 500);
    }
}

// DELETE /users.php?id=1 - Delete user
if ($method === 'DELETE') {
    if (!$userId) {
        sendError('User ID is required');
    }
    
    // Users can only delete their own profile
    if ($userData['user_id'] != $userId) {
        sendError('Unauthorized to delete this profile', 403);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() === 0) {
            sendError('User not found', 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        sendError('Failed to delete user: ' . $e->getMessage(), 500);
    }
}

sendError('Invalid request method', 405);
?>
