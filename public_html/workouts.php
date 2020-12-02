<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/db.php';
include_once '../src/library.php';

require_signed_in();
$db = new DB();
if(isset($_POST['intensity']) && !empty($_POST['intensity'])) {
    $timestamp = $db->log_workout($_SESSION['user_id'], (int) $_POST['intensity'], 60 * $_POST['duration'], $_POST['date'] . ' ' . $_POST['time']);
    if($timestamp > 0) {
        redirect("workouts.php?action=history&timestamp=$timestamp", "Workout activity added successfully.");
    }
}

// Start writing the page
$page_title = "Workouts";
include '../templates/header.php';
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
        <h1>Log a workout activity</h1>
        <?php if(isset($timestamp)) {
            echo "<p class=lead>There was a problem logging the working. Please <a href=\"javascript:history.back();\">[Go Back]</a>.</p>\n";
        } ?>
        <form method="POST" class="form-inline">
            <div class="row">
                <div class="col-md-12">
                    <div class="input-group input-group-lg">
<?php
            if(isset($_POST['intensity']) && !empty($_POST['intensity'])):
                $timestamp = $db->log_workout($_SESSION['user_id'], (int) $_POST['intensity'], 60 * $_POST['duration'], $_POST['date'] . ' ' . $_POST['time']);
                if($timestamp > 0) {
                    redirect("workouts.php?action=history&timestamp=$timestamp", "Workout activity added successfully.");
                }

?>
<?php
            elseif(isset($_POST['activity']) && !empty($_POST['activity'])):
                $activities = $db->query("SELECT id,intensity FROM workout_type WHERE activity = ? GROUP BY id", [$_POST['activity']])->fetchAll(PDO::FETCH_ASSOC);?>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="intensity">intensity</span>
                        </div>
                        <select class="form-control" name="intensity" id="intensity" required>
                            <option value="" disabled selected hidden>Intensity</option>
<?php               foreach($activities as $activity) {
                        echo "                            <option value=\"{$activity['id']}\">".ucwords($activity['intensity'])."</option>\n";
                    }
?>
                        </select>
                    </div>
                    <div class="input-group input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="date">Date</span>
                        </div>
                        <input type="date" id="date" name="date" aria-label="Date" class="form-control" required>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="time">Time</span>
                        </div>
                        <input type="time" id="time" name="time" aria-label="Time" class="form-control" required>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="duration">Duration (minutes)</span>
                        </div>
                        <input type="number" id="duration" name="duration" aria-label="Duration (minutes)" class="form-control" required>
                    </div>
                    <div class="input-group input-group-lg">
                        <input class="form-control btn btn-primary btn-lg" type="submit">
                    </div>
<?php

            elseif(isset($_POST['category']) && !empty($_POST['category'])):
                $activities = $db->query("SELECT DISTINCT activity FROM workout_type WHERE category = ?", [$_POST['category']])->fetchAll(PDO::FETCH_COLUMN);
?>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="activity">Activity</span>
                        </div>
                        <select class="form-control" name="activity" id="activity" required>
                            <option value="" disabled selected hidden>Activity</option>
<?php               foreach($activities as $activity) {
                        echo "                            <option value=\"$activity\">".ucwords($activity)."</option>\n";
                    }
?>
                        </select>
                    </div>
                    <div class="input-group input-group-lg">
                        <button class="form-control btn btn-primary btn-lg" type="submit">Next</button>
                    </div>
<?php
            else:
?>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="category">Category</span>
                        </div>
                        <select class="form-control" name="category" id="category" required>
                            <option value="" disabled selected hidden>Category</option>
                            <option value="bicycling">Bicycling</option>
                            <option value="conditioning excerise">Conditioning Excerise</option>
                            <option value="dancing">Dancing</option>
                            <option value="fishing and hunting">Fishing/Hunting </option>
                            <option value="home activites">Home activites </option>
                            <option value="home repair">Home repair </option>
                            <option value="lawn and garden">Lawn and Garden</option>
                            <option value="inactivity quiet/light">Light activities</option>
                            <option value="miscellaneous">Miscellaneous</option>
                            <option value="occupation">Occupation</option>
                            <option value="music playing">Playing Music </option>
                            <option value="religious activities">Religious Activities </option>
                            <option value="running">Running</option>
                            <option value="self care">Self care</option>
                            <option value="sexual activity">Sexual activity</option>
                            <option value="sports">Sports</option>
                            <option value="transportation">Transportation</option>
                            <option value="walking">Walking</option>
                            <option value="water activities">Water Activites</option>
                            <option value="winter activities">Winter Activities</option>
                            <option value="volunteer activities">Volunteer Work</option>
                        </select>
                    </div>
                    <div class="input-group input-group-lg">
                        <button class="form-control btn btn-primary btn-lg" type="submit">Next</button>
                    </div>
        <?php endif; ?>
                </div>
            </div>
        </form>
     <pre><?php if ($_SERVER['REQUEST_METHOD'] === 'POST') var_dump($_POST); ?></pre>
<?php   break;
        case 'history':
            // If URL has a timestamp, print the confirmation section
            $workouts = [];
            if(isset($_GET['timestamp'])) {
                echo "<h2>Workout added successfully</h2>";
                $workouts = $db->get_workouts($_SESSION['user_id'], (int)$_GET['timestamp']);
                $headers = [
                    'date' => 'Date of workout',
                    'duration_minutes' => 'Duration (minutes)',
                    'category' => 'Category',
                    'activity' => 'Activity',
                    'intensity' => 'Intensity',
                    'calories_burned' => 'Calories burned'
                ];
                draw_table($workouts, $headers, );
            }

        // Diego's table:

        /**
         * Sort order of a table
         * 
         * This function takes a sort variable and returns the url of the sort type.
         * @return url
         * Appends to the end of the url.
         */
        function sortorder($fieldname) {
            $sorturl = "&orderBy=".$fieldname."&sort=";
            $sorttype = "asc";

            if(isset($_GET['orderBy']) && $_GET['orderBy'] == $fieldname){
                if(isset($_GET['sort']) && $_GET['sort'] == "asc"){
                    $sorttype = "desc";
                }
            }
            $sorturl .= $sorttype;
            return $sorturl;
        } 
        $orderBy = " ORDER BY date DESC ";
        if(isset($_GET['orderBy']) && isset($_GET['sort'])){
            $orderBy = ' ORDER BY '.$_GET['orderBy'].' '.$_GET['sort'];
        }
        ?>
                
        <?php
            // HERE WE CHECK USER ID 
            $user = $db->get_user($_SESSION['user_id']);
        ?>    
            <?php $rows = $db->query("select l.date,t.activity,t.intensity,l.duration_seconds as seconds,b.calories_burned as calories from workout_log l join workout_type t ON t.id = l.workout_type_id join workout_calories_burned b on b.date = l.date where l.user_id = ? group by l.date " . $orderBy, [$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);?>
            <h2>Your workout history <?php echo $user['username']; ?></h2>

            <?php $calories_burned_total = $db->query("select sum(calories_burned) as total, avg(calories_burned) as average from workout_calories_burned where user_id = ?",[$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);?>
            <?php foreach ($calories_burned_total as $total) { 
                    $ctr = 0;
                    foreach($total as $number) {
                        if ($ctr == 0) {?>
                            <h4>Total Calories Burned: <?php echo (int)$number;  ?></h4> <?php
                        } else { ?>
                            <h4>Average Calories Burned: <?php echo (int)$number;  ?></h4> <?php    
                        }
                        $ctr++;
                  } 
                   }?>
            <div class="row">                
                <?php
                //start of table
                echo "        <table class=\"table\">\n";
                echo "            <thead class=\"thead-light\">";
                echo "              <tr>\n                ";
                //printing headers
                ?>
                <th><a href="workouts.php?action=history<?php echo sortorder('l.date'); ?>" class="sort">Date</a></th>
                <th><a href="workouts.php?action=history<?php echo sortorder('t.activity'); ?>" class="sort">Workout Activity</a></th>
                <th><a href="workouts.php?action=history<?php echo sortorder('t.intensity'); ?>" class="sort">Intensity</a></th>
                <th><a href="workouts.php?action=history<?php echo sortorder('l.duration_seconds'); ?>" class="sort">Duration</a></th>
                <th><a href="workouts.php?action=history<?php echo sortorder('b.calories_burned'); ?>" class="sort">Calories Burned</a></th>
                <?php
                echo "            </thead>";
                echo "\n            </tr>\n";
                //printing data
                foreach ($rows as $row) {
                    $ctr = 0;
                    echo "            <tr>\n                ";
                    foreach ($row as $td) {
                        if ($ctr == 3) {
                            $td = gmdate("H:i:s", (int)$td);
                            echo "<td>$td</td>";
                        } else {
                            echo "<td>$td</td>";
                        }
                        $ctr = $ctr + 1;
                    }
                    echo "\n            </tr>\n";
                }
                echo "        </table>\n";
                ?>
            </div>   
<?php   break;
        case 'browse':
            if(isset($_GET['id'])):
                $workout_type = $db->query("SELECT * FROM workout_type WHERE id = ?", [$_GET['id']])->fetch(PDO::FETCH_ASSOC);
?>
        <h1>Physical Activities &rsaquo; <?php echo $workout_type['category']; ?> &rsaquo; <?php echo $workout_type['activity']; ?>  &rsaquo; <?php echo $workout_type['intensity']; ?></h1>
        <div class="row">
            <div class="col-md-5">
                <h3 class="text-center">Details</h2>
                <table class="table table-striped">
                    <tr>
                        <th>Category</th><td><a href="?action=browse&category=<?php echo $workout_type['category']; ?>"><?php echo $workout_type['category']; ?></a></td>
                    </tr>
                    <tr>
                        <th>Activity</th><td><a href="?action=browse&activity=<?php echo $workout_type['activity']; ?>"><?php echo $workout_type['activity']; ?></a></td>
                    </tr>
                    <tr>
                        <th>Intensity</th><td><?php echo $workout_type['intensity']; ?></td>
                    </tr>
                    <tr>
                        <th>MET Value</th><td><?php echo $workout_type['mets_value']; ?></td>
                    </tr>
                    <tr>
                        <th>MET Code</th><td><?php echo $workout_type['mets_code']; ?></td>
                    </tr>
                    <tr>
                        <th>Description</th><td><?php echo $workout_type['description']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-7">
                <h3 class="text-center">What do these values mean?</h3>
                <dl class="row">
                    <dt class="col-sm-2">Category</dt>
                    <dd class="col-sm-10">Activities are grouped together in broader categories.</dd>
                    <dt class="col-sm-2">Activity</dt>
                    <dd class="col-sm-10">This is the short description of the activity, broken down a bit more specifically than the category.</dd>
                    <dt class="col-sm-2">Intensity</dt>
                    <dd class="col-sm-10">This is a variation on the activity that has its own MET value.</dd>
                    <dt class="col-sm-2">MET Value</dt>
                    <dd class="col-sm-10">This is the metabolic equivilant ratio used to estimate calories burned.<br>The equation is <code>calories&nbsp;burned</code> &equals; <code>METS&nbsp;code</code> &times; <code>duration&nbsp;in&nbsp;hours</code> &times; <code>weight&nbsp;in&nbsp;kg</code></dd>
                    <dt class="col-sm-2">MET Code</dt>
                    <dd class="col-sm-10">This is the code assigned to the activity on the <a href="https://sites.google.com/site/compendiumofphysicalactivities/" target="_blank">Compendium of Physical Activities</a> website (if known).</dd>
                    <dt class="col-sm-2">Description</dt>
                    <dd class="col-sm-10">This is the detailed description of the activity.</dd>
                </dl>
            </div>
        </div>
<?php       elseif(isset($_GET['activity'])):
                $activities = $db->query("SELECT * FROM workout_type WHERE activity = ?", [$_GET['activity']])->fetchAll(PDO::FETCH_ASSOC); ?>
        <h1>Physical Activities &rsaquo; <?php echo ucwords(htmlspecialchars($activities[0]['category'])); ?> &rsaquo; <?php echo ucwords(htmlspecialchars($_GET['activity'])); ?></h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>MET Code</th><th>MET Value</th><th>Intensity</th>
                </tr>
            </thead>
            <tbody>
<?php           foreach($activities as $activity): ?>
                <tr>
                    <td><?php echo $activity['mets_code']; ?></td><td><?php echo $activity['mets_value']; ?></td><td><a href="?action=browse&id=<?php echo $activity['id']; ?>"><?php echo htmlspecialchars($activity['intensity']); ?></a></td>
                </tr>
<?php           endforeach; ?>
            </tbody>
        </table>
<?php       elseif(isset($_GET['category'])): ?>
        <h1>Physical Activities &rsaquo; <?php echo ucwords(htmlspecialchars($_GET['category'])); ?></h1>
        <ul>
<?php           foreach($db->query("SELECT DISTINCT activity FROM workout_type WHERE category = ?", [$_GET['category']])->fetchAll(PDO::FETCH_COLUMN) as $activity): ?>
            <li>
                <a href="?action=browse&activity=<?php echo $activity; ?>"><?php echo ucwords($activity); ?></a>
            </li>
<?php           endforeach; ?>
        </ul>
<?php       else: ?>
        <h1>Physical Activity Categories</h1>
        <ul>
<?php           foreach($db->query("SELECT DISTINCT category FROM workout_type")->fetchAll(PDO::FETCH_COLUMN) as $category): ?>
            <li>
                <a href="?action=browse&category=<?php echo $category; ?>"><?php echo ucwords($category); ?></a>
            </li>
<?php           endforeach; ?>
        </ul>
<?php       endif;
        break;
        case 'new': ?>
        <h1>Create a new type of workout</h1>
<?php
$new_workouts = [];
if(isset($_POST['mets_value']) && isset($_POST['category']) && isset($_POST['activity']) && isset($_POST['intensity']) && !empty($_POST['mets_value']) && !empty($_POST['category']) && !empty($_POST['activity']) && !empty($_POST['intensity'])) {
    $category = htmlspecialchars($_POST['category']);
    $activity = htmlspecialchars($_POST['activity']);
    for($i = 0; $i < count($_POST['mets_value']); $i++) {
        $mets_value = (float)$_POST['mets_value'][$i];
        $intensity = htmlspecialchars($_POST['intensity'][$i]);
        $update_workout =  $db->add_workout_type($mets_value, $category, $activity, $intensity);
        if($update_workout > 0){
            $new_workouts[$update_workout] = $intensity;
        }
    }
}
if (count($new_workouts) > 0) {
    echo <<<HTML
            <p class="lead">
                <span style="color: #33AA33">Successfully added new activity</span>: <a href="workouts.php?action=browse&category=$category">$category</a> &rsaquo; <b><a href="workouts.php?action=browse&activity=$activity">$activity</a></b> (
    HTML;
    $links = [];
    foreach($new_workouts as $id=>$intensity){
        $links[] = "<a href=\"workouts.php?action=browse&id=$id\">$intensity</a>";
    }
    echo implode(", ", $links). ")\n                </p>\n";
} else {
    echo "        <p class=\"lead\">Enter Workout info</p>\n";
}
?>
        <div class="row">
            <div class="col-8">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <h4>Workout type</h4>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="category"><h5>Category</h5></label>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="activity"><h5>Activity name</h5></label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4 text-right">
                            <select class="form-control" name="category" id="category" required>
                                <option value="" disabled selected hidden>Category</option>
                                <option value="bicycling">Bicycling</option>
                                <option value="conditioning excerise">Conditioning Excerise</option>
                                <option value="dancing">Dancing</option>
                                <option value="fishing and hunting">Fishing/Hunting </option>
                                <option value="home activites">Home activites </option>
                                <option value="home repair">Home repair </option>
                                <option value="lawn and garden">Lawn and Garden</option>
                                <option value="inactivity quiet/light">Light activities</option>
                                <option value="miscellaneous">Miscellaneous</option>
                                <option value="occupation">Occupation</option>
                                <option value="music playing">Playing Music </option>
                                <option value="religious activities">Religious Activities </option>
                                <option value="running">Running</option>
                                <option value="self care">Self care</option>
                                <option value="sexual activity">Sexual activity</option>
                                <option value="sports">Sports</option>
                                <option value="transportation">Transportation</option>
                                <option value="walking">Walking</option>
                                <option value="water activities">Water Activites</option>
                                <option value="winter activities">Winter Activities</option>
                                <option value="volunteer activities">Volunteer Work</option>
                            </select>
                        </div>
                        <div class="form-group col-md-8 text-right">
                            <label for="activity" class="sr-only">Activity Name</label>
                            <input type="text" id="activity" name="activity" class="form-control" placeholder="Activity Name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <h4>Intensities</h4>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-1">
                        </div>
                        <div class="form-group col-md-3 w3tooltip">
                            <h5>MET Value <span data-toggle="tooltip" title="A MET is a ratio of your working metabolic rate relative to your resting metabolic rate." style="color: transparent; text-shadow: 0 0 0 #4f6cbd; font-weight: bold;">&#9432;</span></h5>
                        </div>
                        <div class="form-group col-md-8">
                        <h5>Intensity Description</h5>
                        </div>
                    </div>
                    <div id="intensities">
                        <div class="form-row">
                            <div class="form-group col-md-1">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="mets_value[]" class="sr-only">Mets Value</label>
                                <input type="number" min="1" max="25" step="0.1" id="mets_value[]" name="mets_value[]" class="form-control" placeholder="Mets Value" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="intensity[]" class="sr-only">Intensity</label>
                                <input type="text" id="intensity[]" name="intensity[]" class="form-control" placeholder="eg. low, medium, high, casual, or competitive" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-1 mets text-right">
                            <label for="addintensity"><span style="font-size: 1em; color: transparent; text-shadow: 0 0 0 green; text-align: right">&#x2795;</span></label>
                        </div>
                        <div class="form-group col-md-3 mets">
                            <button type="button" id="addintensity" class="btn btn-secondary">Add intensity</button>
                        </div>
                        <div class="form-group col-md-8">
                            
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <button class="btn btn-lg btn-primary" type="submit">Add Workout</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
var intensityrow = `

                        <div class="form-row">
                            <div class="form-group col-md-1 text-right">
                                <a href="#" class="removeintensity" onclick="$(this).closest('div.form-row').remove();"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red; font-weight: bold;">&#x24E7;</span></a>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="mets_value[]" class="sr-only">Mets Value</label>
                                <input type="number" min="1.0" max="14.0" step="0.1" id="mets_value[]" name="mets_value[]" class="form-control" placeholder="Mets Value" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="intensity[]" class="sr-only">Intensity</label>
                                <input type="text" id="intensity[]" name="intensity[]" class="form-control" placeholder="Intensity Description" required>
                            </div>
                        </div>
`;
$(document).ready(function(){
  $("#addintensity").click(function(){
    $("#intensities").append(intensityrow);
  });
  $(".removeintensity").click(function() {
    $(this).parent('div').parent('div').remove();
  });
  $(function () {
    $('[data-toggle="tooltip"]').tooltip()
  });
});
        </script>
        <?php
        break;
    endswitch; ?>
<?php else: ?>
        <div class="row">
            <div class="col-6">
                <div style="text-align:center;line-height:4em;width:100%;height:8em;margin:12px 12px 12px 12px;font-size:2em;background-color:#666666">Your last workout</div>

            </div>
            <div class="col-6">
                <h1>Lorem ipsum dolar sil imet</h1>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
        </div>
<?php endif; ?>

    </main>
<?php include '../templates/footer.php'; ?>
