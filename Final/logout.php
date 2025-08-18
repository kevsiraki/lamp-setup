<?php
// Initialize the session
session_start();

// Unset all of the session variables
$_SESSION = array();
$_COOKIE = array();
setcookie("username", "");
setcookie("id", "");
setcookie("remember", 0);
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("location: login.php");
 
exit;
?>