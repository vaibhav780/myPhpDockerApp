<?php
$host = 'db';
$db   = 'my_db';
$user = 'root';
$pass = 'root_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Auto-create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS todos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task VARCHAR(255) NOT NULL,
        completed TINYINT(1) DEFAULT 0
    )");

    // Handle Adding a Task
    if (isset($_POST['add_task']) && !empty($_POST['task'])) {
        $stmt = $pdo->prepare("INSERT INTO todos (task) VALUES (?)");
        $stmt->execute([$_POST['task']]);
    }

    // Handle Deleting a Task
    if (isset($_GET['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    }

    // Fetch all tasks
    $tasks = $pdo->query("SELECT * FROM todos ORDER BY id DESC")->fetchAll();

} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Docker PHP To-Do List</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; padding: 50px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; }
        form { display: flex; gap: 10px; margin-bottom: 20px; }
        input[type="text"] { flex: 1; padding: 8px; }
        button { cursor: pointer; background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; }
        ul { list-style: none; padding: 0; }
        li { background: #eee; margin-bottom: 5px; padding: 10px; display: flex; justify-content: space-between; align-items: center; border-radius: 4px; }
        .delete-btn { color: #dc3545; text-decoration: none; font-weight: bold; font-size: 18px; }
    </style>
</head>
<body>

<div class="container">
    <h2>My Docker To-Do List</h2>
    
    <form method="POST">
        <input type="text" name="task" placeholder="Enter a new task..." required>
        <button type="submit" name="add_task">Add</button>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <?= htmlspecialchars($task['task']) ?>
                <a href="?delete=<?= $task['id'] ?>" class="delete-btn">&times;</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>