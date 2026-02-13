<?php
// login.php
session_start();
require_once 'db.php';
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // VULNERABLE SQL (for SQL injection demo)
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    try {
        $query = $db->query($sql);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user'] = $username;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid credentials';
        }
    } catch (PDOException $e) {
        $error = 'SQL Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo '<p style="color:red">'.$error.'</p>'; ?>
    <form method="post">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
