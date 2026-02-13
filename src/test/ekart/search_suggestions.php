<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ekart_user'])) {
    echo json_encode([]);
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {
    // Get product suggestions based on name
    $stmt = $pdo->prepare("SELECT DISTINCT name FROM products WHERE name LIKE :query LIMIT 10");
    $stmt->execute(['query' => "%$query%"]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($suggestions);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
