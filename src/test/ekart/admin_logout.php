<?php
session_start();

// Clear admin session
unset($_SESSION['ekart_admin']);
unset($_SESSION['admin_username']);

header("Location: admin_login.php");
exit;
?>
