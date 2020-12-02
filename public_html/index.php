<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/db.php';
include_once '../src/library.php';


$style = <<<CSS
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

p {
  color: red;
  text-transform: uppercase;
}
CSS;

// Start writing the page
session_start();
$page_title = "Fitness Tracker &rsaquo; Home Page";
include '../templates/header.php';
$db = new DB();



?>
    <!-- Page Content -->

    <?php if(is_authenticated()):
    $user = $db->get_user($_SESSION['user_id']); 

?>
        <main role="main" class="container">
            <h1> Hello <?php echo $user['first_name'] . ' ' . $user['last_name']; ?> </h1>
            <div class="container">
            <div class="row">
                <div class="col">
                <?php $rows=$db->get_latest_meal((int) $_SESSION['user_id']);
                echo "<h1><b>Your Latest meal:</b></h1> <br>";
                echo  "<i><p>" . $rows[0]['date'] . "</p></i>";
                foreach($rows as $row){
                    echo "<i><p>". "<br>" . $row['name']. "<br>" . $row['serving_size_friendly'] . "</p></i>";
                }
                ?>
                </div>
                <div class="col"> 
                <?php $rows=$db->get_latest_workout((int) $_SESSION['user_id']);
                echo "<h1><b>Your Latest Workout:</b></h1> <br>";
                echo  "<i><p>" . $rows[0]['date'] . "</p></i>";
                foreach($rows as $row){
                    echo "<i><p>".  "<br>" . $row['category']. "<br>" . $row['activity'] . "</p></i>";
                }
            ?>
            </div>
            </div>
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
