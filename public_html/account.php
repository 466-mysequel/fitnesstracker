<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/db.php';
include_once '../src/library.php';

//make sure user is login   
session_start();
if(!is_authenticated()) {
    $home = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
    header("Location: $home/login.php", true, 303);
    die ("<html><body>You must be logged in. <a href=\"login.php\">Click here if your are not redirected automatically.</a></body></html>\n");
}

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; My Account";
include '../templates/header.php';
$db = new DB();
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <div class="row">
          <?php
            $sql = "SELECT  first_name, last_name,username FROM user WHERE id = ?";
            $stmt = $db->query($sql,[$_SESSION['user_id']]);
            $user = $stmt ->fetch(PDO:: FETCH_ASSOC);
          ?>
          <h1> Hello <?php  echo "{$user['first_name']}  {$user['last_name']} ";  ?>
          </h1>
        </div>
    </main>
<?php include '../templates/footer.php'; ?>
