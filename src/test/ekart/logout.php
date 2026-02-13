<?php
session_start();

// Clear all session variables
session_unset();
session_destroy();

// Delete Remember Me cookie
if (isset($_COOKIE['ekart_remember'])) {
    setcookie('ekart_remember', '', time() - 3600, '/');
}

header("Location: index.php");
exit;
?>
