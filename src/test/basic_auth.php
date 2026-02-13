<?php
$username = 'admin';
$password = 'admin';

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    ($_SERVER['PHP_AUTH_USER'] != $username) || ($_SERVER['PHP_AUTH_PW'] != $password)) {
    
    header('WWW-Authenticate: Basic realm="My Project"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<h1>401 Unauthorized</h1>';
    echo 'You must enter a valid login and password to access this resource.';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Basic Auth Success</title>
    <style>
        body { font-family: sans-serif; padding: 40px; text-align: center; }
        .success-box { border: 2px solid #4CAF50; padding: 20px; background: #e8f5e9; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>Basic Auth</h1>
        <p>Congratulations! You have entered the correct credentials.</p>
    </div>
</body>
</html>