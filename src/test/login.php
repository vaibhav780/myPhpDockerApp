<?php
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'Secret!') {
        header("Location: secure.php");
        exit;
    } else {
        $message = "Your username is invalid!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login Page</title></head>
<body>
    <h2>Login Page</h2>
    <?php if ($message): ?>
        <div id="flash" style="background: #f44336; color: white; padding: 10px;"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" id="username" placeholder="Username"><br>
        <input type="password" name="password" id="password" placeholder="Password"><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>