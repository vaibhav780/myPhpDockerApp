<?php
session_start();
session_destroy();

// Delete the Remember Me cookie by setting its expiration to the past
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

header("Location: login.php");
exit;