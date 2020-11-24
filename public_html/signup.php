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
$page_title = "Fitness Tracker &rsaquo; Sign Up";
include '../templates/header.php';
?>
    <div class="jumbotron text-center">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Coming soon!</p>
    </div>
    <div class="container">
        <h2>Lorem ipsum dolar sil imet</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
    </div>
<?php
include '../templates/footer.php';
?>
