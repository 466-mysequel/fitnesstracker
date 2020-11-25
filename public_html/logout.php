<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Destroy the session
setcookie(session_name(), "", time() -3600, "/"); // remove cookie
$_SESSION = array(); // set session to empty array
session_destroy(); // destroy session
session_write_close(); // close session
$home = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); // get current location
header("Location: $home/index.php", true, 303);
die ("<html><body>You have been logged out. <a href=\"index.php\">Click here if your are not redirected automatically.</a></body></html>\n");
?>