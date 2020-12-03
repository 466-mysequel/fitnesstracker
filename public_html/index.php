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
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

CSS;

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; Home Page";
include '../templates/header.php';
$db = new DB();

?>
    <!-- Page Content -->

    <?php if(is_authenticated()):
    $user = $db->get_user($_SESSION['user_id']); 

?>
        <main role="main">
            <div class="container">
            <h1>Hello <?php echo $user['first_name'] . ' ' . $user['last_name']; ?> </h1>
            <div class="row">
                <div class="col-4">
                    <h3>Your weight over time</h3>
                    <?php
                    $rows=$db->query("SELECT date,weight_kg FROM weight_log WHERE user_id = ?", [$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);
                    if(count($rows) > 0) {
                        draw_table($rows);
                    } else {
                        echo "<p class=\"lead\">You have not logged any weight yet. Please add your weight on the <a href=\"account.php\">Acccount page</a>\n";
                    }
                    
                    ?>
                </div>
                <div class="col-6"> 

                  <h4>Net calories per day</h4>
<?php
                    $net_cals = $db->query("SELECT * FROM net_calories_per_day WHERE user_id = ? ORDER BY date DESC LIMIT 10", [$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);
                    if(count($net_cals) > 0) {
                        $headers = [
                            'date' => 'Date',
                            'total_calories_in' => 'Cals In',
                            'total_calories_out' => 'Cals Out',
                            'net_calories' => 'Net Cals'
                        ];
                        draw_table($net_cals, $headers, true, 'netCals', 'table table-striped table-sm');
                    } else {
                        echo "<p class=\"lead\">You have not logged any activity yet. Please add your <a href=\"foods.php?action=log\">meals</a> and <a href=\"workouts.php?action=log\">workouts.</a>\n";
                    }
?>

                </div>
            </div>
        </main>
    <?php else: ?>
            <div class="jumbotron">
                <div class="container">
                    <h1 class="display-3">Fitness Tracker</h1>
                    <p class="lead"><b>With Fitness tracker, you can keep track of your weight, diet, and exercise. Start tracking your fitness today!</b></p>
                    <p><a class="btn btn-primary btn-lg" href="signup.php" role="button">Sign up &raquo;</a></p>
                </div>
            </div>
            <div class="container">

            <div class="row">
                <div class="col-md-4">
                    <h2>Track your weight</h2>
                    <p>Get started by adding your weight. Keep track of your weight as you go.</p>
                </div>
                <div class="col-md-4">
                    <h2>Track your diet</h2>
                    <p>Log what you eat. Select from foods in our database or add your own.</p>
                </div>
                <div class="col-md-4">
                    <h2>Track your exercise</h2>
                    <p>Log your physical activity. Choose from activities in our database or add your own.</p>
                </div>
            </div>
            <hr>

        </div> <!-- /container -->
    <?php endif; ?>
<?php include '../templates/footer.php'; ?>
