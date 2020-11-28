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
            $sql = "SELECT DISTINCT first_name, last_name FROM user WHERE id = 5";
            $stmt = $db->query($sql);
            $allRows = $stmt ->fetchAll(PDO:: FETCH_ASSOC);
            $fullName = array();
            $count =0;
            foreach($allRows as $row)
            {
                echo "<tr>";
                foreach($row as $item)
                {
                    $fullName[$count] = $item;
                    $count += 1;
                }
            } echo "</tr>";
            echo $_POST['username'];
          ?>
          <h1> Hello
          <?php foreach($fullName as $name) { echo"$name "; } ?>
          </h1>
        </div>
    </main>
<?php include '../templates/footer.php'; ?>
