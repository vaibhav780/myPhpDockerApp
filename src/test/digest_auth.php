<?php
$realm = 'Restricted area';
$users = ['admin' => 'admin'];

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
    die('Cancelled by user');
}

// Analyze the PHP_AUTH_DIGEST variable
$data = array();
preg_match_all('@(\w+)=(?:(?:")([^"]+)"|([^\s,]+))@', $_SERVER['PHP_AUTH_DIGEST'], $matches, PREG_SET_ORDER);
foreach ($matches as $m) { $data[$m[1]] = $m[2] ? $m[2] : $m[3]; }

if (!isset($users[$data['username']])) {
    die('Wrong Credentials!');
}
?>
<!DOCTYPE html>
<html>
<head><title>Digest Auth Success</title></head>
<body>
    <div style="border: 2px solid #673ab7; padding: 20px; background: #f3e5f5;">
        <h3>Digest Auth</h3>
        <p>Congratulations! You authenticated using Digest Auth.</p>
    </div>
</body>
</html>