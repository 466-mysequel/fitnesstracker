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
$page_title = "Workouts";
include '../templates/header.php';
$db = new DB();
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <div class="row">
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="workouts.php?action=log" role="button" title="Record a workout activity">Log a workout</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="workouts.php?action=history" role="button" title="See your workout history">Workout history</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="workouts.php?action=browse" role="button" title="Browse a directory of all workout types">Browse workouts</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="workouts.php?action=new" role="button" title="Create a new type of workout">Create workout</a>
            </div>
        </div>
<?php
if(isset($_GET['action'])):
    switch($_GET['action']):
        case 'log': ?>
        <h2>Log a workout activity</h2>
        <div class="row">
        </div>
<?php   break;
        case 'history': ?>
        <h2>Your workout history</h2>
        <div class="row">
        </div>
<?php   break;
        case 'browse': ?>
        <?php if(isset($_GET['id'])): ?>
            <h2>Workout <?php echo $_GET['id'] ?></h2>
        <?php else: ?>
            <h2>Directory of workouts</h2>
            <ul><?php foreach($db->get_workout_types() as $id => $name): ?>
                <li><a href="?action=browse&id=<?php echo $id ?>"><?php echo htmlspecialchars($name) ?></li>
            <?php endforeach; ?></ul>
        <?php endif; ?>
        <div class="row">
        </div>
<?php   break;
        case 'new': ?>
        <h2>Create a new type of workout</h2>
        <div class="row">
        </div><?php
        break;
    endswitch; ?>
<?php else: ?>
        <div class="row">
            <div class="col-6">
                <div style="text-align:center;line-height:4em;width:100%;height:8em;margin:12px 12px 12px 12px;font-size:2em;background-color:#666666">Your last workout</div>

            </div>
            <div class="col-6">
                <h2>Lorem ipsum dolar sil imet</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
        </div>
<?php endif; ?>
    </main>
<?php include '../templates/footer.php'; ?>
