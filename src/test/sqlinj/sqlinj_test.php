<?php
// sqlinj_test.php
require_once 'db.php';
$error = '';
$result = '';
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // VULNERABLE SQL (for testing SQL injection)
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    try {
        $query = $db->query($sql);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $result = 'Login successful!';
        } else {
            $error = 'Login failed!';
        }
    } catch (PDOException $e) {
        $error = 'SQL Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SQL Injection Test</title>
</head>
<body>
    <h2>SQL Injection Demo Login</h2>
    <?php if ($error) echo '<p style="color:red">'.$error.'</p>'; ?>
    <?php if ($result) echo '<p style="color:green">'.$result.'</p>'; ?>
    <form method="post">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Password: <input type="text" name="password" required></label><br>
        <button type="submit">Test Login</button>
    </form>
    <p>Try SQL Injection: <code>admin' -- </code> as username, any password.</p>
</body>
</html>
