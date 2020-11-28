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
$home = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
if(!is_authenticated()) {
    header("Location: $home/login.php", true, 303);
    die ("<html><body>You must be logged in. <a href=\"login.php\">Click here if your are not redirected automatically.</a></body></html>\n");
}
$db = new DB();

if(isset($_POST['nutrient']) && isset($_POST['amount']) && isset($_POST['unit'])) {
    if(!empty($_POST['nutrient']) && !empty($_POST['amount']) && !empty($_POST['unit']))
    {  
        $update_nutrient = (bool) $db->add_nutrient($_POST['nutrient'], (float) $_POST['amount'], $_POST['unit']);
    }
    //if there is an invalid entry will print that the nutrient was unsuccessfully added
    else 
    {
        $update_nutrient = false;
    }
}

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; Add Nutrient";
include '../templates/header.php';
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <h1>Add A New Nutrient</h1>
            <div class="col-6">
                <p class="lead">Enter Nutrient Info</p>
                <?php if(isset($update_nutrient)) echo $update_nutrient ? "<p style=\"color: #33aa33\">Nutrient sucessfully updated</p>\n" : "<p style=\"color: #aa3333\">Nutrient unsuccessfully updated</p>\n"; ?>
                <form method="POST" class="form-inline">
                    <label for="nutrient" class="sr-only">Nutrient</label>
                    <input type="text" id="nutrient" name="nutrient" class="form-control" placeholder="Nutrient Name">
                    <label for="nutrient" class="sr-only">Recommended Daily Values Amount</label>
                    <input type="text" id="amount" name ="amount" class ="form-control" placeholder="Nutrient RDV Amount">
                    <label for="nutrient" class="sr-only">Recommended Daily Values Unit</label>
                    <select class="form-control" name="unit" id="unit">
                        <option value="g">g</option>
                        <option value="mg">mg</option>
                        <option value="mcg">mcg</option>
                    </select>
                    <button class="btn btn-lg btn-primary" type="submit">Add Nutrient</button>
                </form>
            </div>
        </div>
    </main>
<?php include '../templates/footer.php'; ?>
