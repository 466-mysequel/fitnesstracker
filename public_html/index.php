<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/db.php';
include_once '../src/library.php';

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; Home Page";
include '../templates/header.php';
$db = new DB();

echo<<<HTML
<style> 
.signed-out {
  background-image: url('https://images.unsplash.com/photo-1571902943202-507ec2618e8f');
  background-color: #cccccc;
  height: 500px;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  position: relative;
  background-attachment: fixed;
}
</style>
HTML;

?>
    <!-- Page Content -->

    <?php if(is_authenticated()): ?>
        <div class="jumbotron text-center">
            <h1><?php echo $page_title; ?></h1>
        </div>
        <main role="main" class="container">
            <h1> Signed in</h1>
        </main>
    <?php else: ?>
        <div class="signed-out">
            <div class="jumbotron text-center">
                <h1><?php echo $page_title; ?></h1>
            </div>
            <main role="main" class="container">
            <h1 style="color:#007bff">You need to be <a style="color:#eb3434" href="login.php">signed in</a> to view your stats!</h1>
            </main>
        </div>
    <?php endif; ?>
<?php include '../templates/footer.php'; ?>
