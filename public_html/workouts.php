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
<script>
var intensityrow = `

                        <div class="form-row">
                            <div class="form-group col-md-1 text-right">
                            	<a href="#" class="removeintensity" onclick="$(this).closest('div.form-row').remove();"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red; font-weight: bold;">&#x24E7;</span></a>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="mets_value[]" class="sr-only">Mets Value</label>
                                <input type="number" min="1.0" max="14.0" step="0.1" id="mets_value[]" name="mets_value[]" class="form-control" placeholder="Mets Value">
                            </div>
                            <div class="form-group col-md-8">
                                <label for="intensity[]" class="sr-only">Intensity</label>
                                <input type="text" id="intensity[]" name="intensity[]" class="form-control" placeholder="Intensity Description">
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
        <div class="row">
        </div>
<?php   break;
        case 'history': ?>
        <h1>Your workout history</h1>
        <div class="row">
        </div>
<?php   break;
        case 'browse': ?>
        <?php if(isset($_GET['id'])): ?>
            <h1>Workout <?php echo $_GET['id'] ?></h1>
        <?php else: ?>
            <h1>Directory of workouts</h1>
            <ul><?php foreach($db->get_workout_types() as $id => $name): ?>
                <li><a href="?action=browse&id=<?php echo $id ?>"><?php echo htmlspecialchars($name) ?></li>
            <?php endforeach; ?></ul>
        <?php endif; ?>
        <div class="row">
        </div>
<?php   break;
        case 'new': ?>
        <h1>Create a new type of workout</h1>
        <?php
        $new_workouts = []; 
        if(isset($_POST['mets_value']) && isset($_POST['category']) && isset($_POST['activity']) && isset($_POST['intensity'])){
        if(!empty($_POST['mets_value']) && !empty($_POST['category']) && !empty($_POST['activity']) && !empty($_POST['intensity']))
        {  
            for($i = 0; $i < count($_POST['mets_value']); $i++){
                $mets_value = (float)$_POST['mets_value'][$i];
                $category = htmlspecialchars($_POST['category']);
                $activity = htmlspecialchars($_POST['activity']);
                $intensity = htmlspecialchars($_POST['intensity'][$i]);
                $update_workout =  $db->add_workout_type($mets_value, $category, $activity, $intensity);
                if($update_workout > 0){
                    $new_workouts[$update_workout] = $intensity;
                }
            }
            
        }
        //if there is an invalid entry will print that the nutrient was unsuccessfully added
        else 
        {
         $update_workout= false;
        }
}?>
        <div class="row">
        <div class="col-8">
                <p class="lead">Enter Workout info</p>
                <?php 
                    if(isset($update_workout)) { 
                        if (count($new_workouts) > 0)
                        {
                            echo "<p style=\"color: #33AA33\">Successfully added new activity</p>";
                            echo "<p><a href=\"workouts.php?action=browse&category=$category\">$category</a> &gt; <a href=\"workouts.php?action=browse&activity=$activity\">$activity</a> (";
                            $links = [];
                            foreach($new_workouts as $id=>$intensity){
                                $links[] = "<a href=\"workouts.php?action=browse&id=$id\">$intensity</a>";
                            }
                            echo implode(", ", $links). ")</p>";   
                        }
                    }
                ?>
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
                            <select class="form-control" name="category" id="category">
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
                            <input type="text" id="activity" name="activity" class="form-control" placeholder="Activity Name">
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
                                <input type="number" min="1.0" max="14.0" step="0.1" id="mets_value[]" name="mets_value[]" class="form-control" placeholder="1">
                            </div>
                            <div class="form-group col-md-8">
                                <label for="intensity[]" class="sr-only">Intensity</label>
                                <input type="text" id="intensity[]" name="intensity[]" class="form-control" placeholder="eg. low, medium, high, casual, or competitive">
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
        </div><?php
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
