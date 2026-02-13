<?php
// db.php
// Use SQLite for SQL injection demo
$dbFile = __DIR__ . '/test.db';
try {
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Create users table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, username TEXT, password TEXT);");
    // Insert demo user if not exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO users (username, password) VALUES ('admin', 'password')");
    }
} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}
