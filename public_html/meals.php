<?php
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include_once '../src/db.php';
include_once '../src/library.php'; 
$page_title = "Fitness Tracker &rsaquo; Meals Page";
include_once '../templates/header.php';
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <div class="row">
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="meals.php?action=log" role="button" title="Record a meal">Log a meal</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="meals.php?action=history" role="button" title="See your meal history">Meal history</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="meals.php?action=browse" role="button" title="Browse a database full of food">Browse foods</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="meals.php?action=new" role="button" title="Create a new food">Create food</a>
            </div>
        </div>
    <?php
if(isset($_GET['action'])):
    switch($_GET['action']):
        case 'log': ?>
        <h2>Log a meal</h2>
        <div class="row">
        </div>
<?php   break;
        case 'history': ?>
        <h2>Your meal history</h2>
        <div class="row">
        </div>
<?php   break;
        case 'browse': ?>
        <?php if(isset($_GET['id'])): ?>
            <h2>Meal <?php echo $_GET['id'] ?></h2>
        <?php else: ?>
            <h2>Directory of foods</h2>
        <?php endif; ?>
        <div class="row">
        </div>
<?php   break;
        case 'new': ?>
        <h2>Create a new food</h2>
<?php   break;
        endswitch; ?>
<?php else: ?>
        <div class="row">
            <div class="col-6">
                <div style="text-align:center;line-height:4em;width:100%;height:8em;margin:12px 12px 12px 12px;font-size:2em;background-color:#666666">Your most recent meal</div>

            </div>
            <div class="col-6">
                <h2>Lorem ipsum dolar sil imet</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
        </div>
        </div>

<?php endif; ?>
    </main>
<?php include_once '../templates/footer.php';
