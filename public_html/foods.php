<?php
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include_once '../src/db.php';
$db = new DB();
include_once '../src/library.php';
include_once '../src/convert.php';
require_signed_in();

$page_title = "Fitness Tracker &rsaquo; Foods";
$result=NULL;
$stylesheets = ['css/nutrition-facts.css'];
$style = <<<CSS
.food {
    float: left; margin: 5px;
}
.solid {
    width:108px; height:108px; background:url(img/fooddrink.png) 0px 0px;
}
.liquid {
    width:108px; height:108px; background:url(img/fooddrink.png) -108px 0px;
}
CSS;
include_once '../templates/header.php';
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <div class="row">
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="foods.php?action=log" role="button" title="Record a meal">Log a meal</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="foods.php?action=history" role="button" title="See your meal history">Meal history</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="foods.php?action=browse" role="button" title="Browse a database full of food">Browse foods</a>
            </div>
            <div class="col-3">
                <a class="btn btn-secondary btn-lg btn-block" href="foods.php?action=new" role="button" title="Create a new food">Create food</a>
            </div>
        </div>
<?php
if(isset($_GET['action'])):
    switch($_GET['action']):
        case 'log':

    if(isset($_GET['action']) && $_GET['action'] == 'log' && isset($_POST['food_id']) && isset($_POST['servings']) && isset($_POST['unit'])) {
        $servings = $_POST['servings'];
        $foods = $_POST['food_id'];
        $conv_unit = []; // used to store conversion unit
        $serving = []; // used to store serving after serving has been calculated using

        // check food entries and convert entries as needed
        foreach($_POST['unit'] as $key => $value){ 
            if($value == "serving"){
                $serving[] = $servings[$key];
            } elseif ($value == "g" || $value == "lb"  || $value == "oz" || $value == "kg"){
                $food = $db->get_food((int)$foods[$key]);
                $conv_unit[$key] = convert::mass_to_g((float)$servings[$key],$value);
                $serving[] = $conv_unit[$key] / $food['serving_size_grams'];
            } elseif(
                $value == "mL"    ||
                $value == "L"     ||
                $value == "gal"   ||
                $value == "qt"    ||
                $value == "pt"    ||
                $value == "cup"   ||
                $value == "fl oz" ||
                $value == "tbsp"  ||
                $value == "tsp"
            ){
                $food = $db->get_food((int)$foods[$key]);
                $conv_unit[$key] = convert::volume_to_cc((float)$servings[$key],$value);
                $serving[] = $conv_unit[$key] / $food['serving_size_cc'];
            } else {
                continue;
            }
        }
        
        $log_food_timestamp = $db->log_food($_SESSION['user_id'], $_POST['food_id'], (array)$serving, $_POST['date'] . ' ' . $_POST['time']);
        if($log_food_timestamp > 0) {
            redirect("foods.php?action=history&timestamp=" . $log_food_timestamp, "Your food was logged successfully");
        }
    }
?>
        <form method="POST">
            <h3 class="mt-3">Log a meal</h3>
            <p class="lead"><?php echo (isset($log_food_timestamp)  ? "There was a problem logging your meal." : "Please enter the information about your meal, including one or more foods."); ?></p>
            <div class="form-row">
                <div class="form-group col-md-10 form-inline">
                    <div class="input-group mb-3 input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="date">Date:</span>
                        </div>
                        <input class="form-control" type="date" id="date" name="date">
                    </div>
                    <div class="input-group mb-3 input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="date">Time</span>
                        </div>
                        <input class="form-control" type="time" id="time" name="time">
                    </div>
                </div>
                <div class="form-group col-md-10">
                    <div class="foods">
                        <label for="food-item" class="col-form-label"><h4>Food Item</h4></label><br>
                        <button class="btn btn-lg btn-primary" id="add-foods">Add Item</button>
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-10">
                    <button class="btn btn-lg btn-primary" type="submit">Log Meal</button>
                </div>
            </div>
        </form>
<?php   break;
        case 'history':
            if(isset($_GET['timestamp'])):
                echo "        <h3 class=\"mt-3\"><a href=\"?action=history\">Your meal history</a> &rsaquo; Your meal on " . date('h:i:s A l, jS \of F Y', (int)$_GET['timestamp']) . "</h3>\n";
                $meals = $db->get_meals((int)$_SESSION['user_id'], (int)$_GET['timestamp']);
                foreach($meals as $meal):
                    draw_table($meal['foods'], ['name' => 'Food', 'calories' => 'Calories']);
                endforeach;
            endif;

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
            <?php $rows = $db->query("select date,food,servings,fat,carbs,protein,fiber from total_food_logs where user_id = ?" . $orderBy, [$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);?>
            <h2>Your meal history <?php echo $user['username']; ?></h2>
            <div class="row">                
                <?php
                //start of table
                echo "        <table class=\"table\">\n";
                echo "            <thead class=\"thead-light\">";
                echo "              <tr>\n                ";
                //printing headers
                ?>
                <th><a href="foods.php?action=history<?php echo sortorder('date'); ?>" class="sort">Date</a></th>
                <th><a href="foods.php?action=history<?php echo sortorder('food'); ?>" class="sort">Food</a></th>
                <th><a href="foods.php?action=history<?php echo sortorder('servings'); ?>" class="sort">Servings Total</a></th>
                <th><a href="foods.php?action=history<?php echo sortorder('fat'); ?>" class="sort">Total Fat(g)</a></th>
                <th><a href="foods.php?action=history<?php echo sortorder('carbs'); ?>" class="sort">Total Carbs(g)</a></th>
                <th><a href="foods.php?action=history<?php echo sortorder('protein'); ?>" class="sort">Total Protein(g)</a></th>
                <th><a href="foods.php?action=history<?php echo sortorder('fiber'); ?>" class="sort">Total Fiber(g)</a></th>
                <?php
                echo "            </thead>";
                echo "\n            </tr>\n";
                //printing data
                foreach ($rows as $row) {
                    echo "            <tr>\n                ";
                    foreach ($row as $td) {
                        echo "<td>$td</td>";
                    }
                    echo "\n            </tr>\n";
                }
                echo "        </table>\n";
                ?>
            </div>   
<?php
            echo "        <h3 class=\"mt-3\">Your logged meals</h3>\n";
            $meals = $db->get_meals((int)$_SESSION['user_id']);
            foreach($meals as $meal):
                echo "\n            <h5 class=\"mt-3\"><a href=\"?action=history&timestamp=" . $meal['unixtime'] . "\">". date('h:i:s A l, jS \of F Y', $meal['unixtime']) . "</a></h3>\n";
                draw_table($meal['foods'], ['name' => 'Food', 'calories' => 'Calories']);
            endforeach;

break;
        case 'browse':
            if(isset($_GET['id'])):
                $food = $db->get_food((int) $_GET['id']);
                $macros = $db->get_food_macronutrients((int) $_GET['id']);
                $micros = $db->get_food_micronutrients((int) $_GET['id']);
?>
        <h1>Foods &rsaquo; <?php echo $food['name']; ?> &rsaquo; Details</h1>
        <div><h3><div class="<?php echo $food['type']; ?> food"></div></h2></div>
        <div>
            <!-- HTML/CSS originally based on snippet from https://codemyui.com/nutrition-facts-table-using-html-css/ and modified to suit our needs -->
            <section class="nutrition-facts">
            <header class="nutrition-facts-header">
                <h1 class="nutrition-facts-title">Nutrition Facts</h1>
                <p>Serving Size <?php echo $food['serving_size_friendly']; ?> (about <?php echo $food['serving_size_grams']; ?>g)</p>
            </header>
            <table class="nutrition-facts-table">
                <thead>
                    <tr>
                        <th colspan="3" class="small-info">Amount Per Serving</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th colspan="2">
                            <b>Calories</b> 
                            <?php echo $food['calories_per_serving']; ?>
                        </th>
                        <td>
                            Calories from Fat
                            <?php echo (isset($macros['Fat']) ? (int)$macros['Fat']['amount'] * 4 : 0 ); ?>
                        </td>
                    </tr>
                    <tr class="thick-row">
                        <td colspan="3" class="small-info">
                        <b>% Daily Value*</b>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <b>Total Fat</b>
                            <?php echo (isset($macros['Fat']) ? $macros['Fat']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                            <b><?php echo (isset($macros['Fat']) ? $macros['Fat']['percent_dv'] : 0 ); ?>%</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="blank-cell">
                        </td>
                        <th>
                            Saturated Fat
                            <?php echo (isset($macros['Saturated fat']) ? $macros['Saturated fat']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                            <b><?php echo (isset($macros['Saturated fat']) ? $macros['Saturated fat']['percent_dv'] : 0 ); ?>%</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="blank-cell">
                        </td>
                        <th>
                            Trans Fat
                            <?php echo (isset($macros['Trans fat']) ? $macros['Trans fat']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <b>Cholesterol</b>
                            <?php echo (isset($macros['Cholesterol']) ? $macros['Cholesterol']['amount'] : 0 ); ?>mg
                        </th>
                        <td>
                            <b><?php echo (isset($macros['Cholesterol']) ? $macros['Cholesterol']['percent_dv'] : 0 ); ?>%</b>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <b>Sodium</b>
                            <?php echo (isset($macros['Sodium']) ? $macros['Sodium']['amount'] : 0 ); ?>mg
                        </th>
                        <td>
                            <b><?php echo (isset($macros['Sodium']) ? $macros['Sodium']['percent_dv'] : 0 ); ?>%</b>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <b>Total Carbohydrate</b>
                            <?php echo (isset($macros['Total carbohydrates']) ? $macros['Total carbohydrates']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                            <b><?php echo (isset($macros['Total carbohydrates']) ? $macros['Total carbohydrates']['percent_dv'] : 0 ); ?>%</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="blank-cell">
                        </td>
                        <th>
                            Dietary Fiber
                            <?php echo (isset($macros['Dietary fiber']) ? $macros['Dietary fiber']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                            <b><?php echo (isset($macros['Dietary fiber']) ? $macros['Dietary fiber']['percent_dv'] : 0 ); ?>%</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="blank-cell">
                        </td>
                        <th>
                            Sugar
                            <?php echo (isset($macros['Sugar']) ? $macros['Sugar']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                        </td>
                    </tr>
                    <tr class="thick-end">
                        <th colspan="2">
                            <b>Protein</b>
                            <?php echo (isset($macros['Protein']) ? $macros['Protein']['amount'] : 0 ); ?>g
                        </th>
                        <td>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="nutrition-facts-table--grid">
                <tbody>
                <tr>
                    <td colspan="2">
                    Vitamin A
                    <?php echo (isset($micros['Vitamin A']) ? $micros['Vitamin A']['percent_dv'] : 0 ); ?>%
                    </td>
                    <td>
                    Vitamin C
                    <?php echo (isset($micros['Vitamin C']) ? $micros['Vitamin C']['percent_dv'] : 0 ); ?>%
                    </td>
                </tr>
                <tr class="thin-end">
                    <td colspan="2">
                    Calcium
                    <?php echo (isset($micros['Calcium']) ? $micros['Calcium']['percent_dv'] : 0 ); ?>%
                    </td>
                    <td>
                    Iron
                    <?php echo (isset($micros['Iron']) ? $micros['Iron']['percent_dv'] : 0 ); ?>%
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="small-info">* Percent Daily Values are based on a 2,000 calorie diet. Your daily values may be higher or lower depending on your calorie needs:</p>

            <table class="nutrition-facts-table--small small-info">
                <thead>
                <tr>
                    <td colspan="2"></td>
                    <th>Calories:</th>
                    <th>2,000</th>
                    <th>2,500</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th colspan="2">Total Fat</th>
                    <td>Less than</td>
                    <td>65g</td>
                    <td>80g</td>
                </tr>
                <tr>
                    <td class="blank-cell"></td>
                    <th>Saturated Fat</th>
                    <td>Less than</td>
                    <td>20g</td>
                    <td>25g</td>
                </tr>
                <tr>
                    <th colspan="2">Cholesterol</th>
                    <td>Less than</td>
                    <td>300mg</td>
                    <td>300 mg</td>
                </tr>
                <tr>
                    <th colspan="2">Sodium</th>
                    <td>Less than</td>
                    <td>2,400mg</td>
                    <td>2,400mg</td>
                </tr>
                <tr>
                    <th colspan="3">Total Carbohydrate</th>
                    <td>300g</td>
                    <td>375g</td>
                </tr>
                <tr>
                    <td class="blank-cell"></td>
                    <th colspan="2">Dietary Fiber</th>
                    <td>25g</td>
                    <td>30g</td>
                </tr>
                </tbody>
            </table>

            <p class="small-info">
                Calories per gram:
            </p>
            <p class="small-info text-center">
                Fat 9
                &bull;
                Carbohydrate 4
                &bull;
                Protein 4
            </p>

            </section>
        </div>
        <?php else: ?>
            <h1>Directory of foods</h2>
            <div class="row">
                <div class="col-4">
                    <h3>Foods</h3>
                    <ul>
            <?php $foods = $db->query("SELECT id,name FROM food WHERE type = 'solid'")->fetchAll(PDO::FETCH_ASSOC); foreach ($foods as $food): ?>
                <li>
                    <a href="?action=browse&id=<?php echo $food['id']; ?>"><?php echo $food['name']; ?></a>
                </li>
            <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-4">
                    <h3>Beverages</h3>
                    <ul>
            <?php $foods = $db->query("SELECT id,name FROM food WHERE type = 'liquid'")->fetchAll(PDO::FETCH_ASSOC); foreach ($foods as $food): ?>
                <li>
                    <a href="?action=browse&id=<?php echo $food['id']; ?>"><?php echo $food['name']; ?></a>
                </li>
            <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
        </div>
<?php   break;
        case 'new': ?>

<?php if(isset($_POST['name']) && isset($_POST['type']) && isset($_POST['serving_size_friendly']) && isset($_POST['calories_per_serving']) && isset($_POST['serving_size_grams'])) {
    $return = $db->add_food($_POST['name'],$_POST['type'],$_POST['serving_size_friendly'],(int)$_POST['calories_per_serving'],(int)$_POST['serving_size_grams'],(int)$_POST['serving_size_cc'],$_POST['macro_id'],$_POST['macro_g'],$_POST['micro_id'],$_POST['micro_dv']);
    if($return == 0){
        echo "<h1>Unable to add food.</h1>";
    }
} ?>
<form method="POST">
<br>
    <div class="row">
        <div class="col">
            <h2>Create a new food</h2>
            <label for="name" >Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Name of the food">
            <label for="type" >Type</label>
            <select id="type" name="type" class="form-control">
                <option value="solid">Solid</option>
                <option value="liquid">Liquid</option>
            </select>
            <label for="serving_size_friendly" >Serving Size</label>
            <input type="text" id="serving_size_friendly" name="serving_size_friendly" class="form-control" placeholder="i.e., One cup">
            <label for="calories" >Calories Per Serving</label>
            <input type="number" step="0.01"min="0.01" id="calories" name="calories_per_serving" class="form-control" placeholder="Calories per serving">
            <label for="serving_size_grams" >Serving Size (g)</label>
            <input type="number" step="0.01"min="0.01" id="serving_size_grams" name="serving_size_grams" class="form-control" placeholder="Serving size (in grams)">
            <label for="serving_size_cc" >Serving Size (cc)</label>
            <input type="number" step="0.01"min="0.01" id="serving_size_cc" name="serving_size_cc" class="form-control" placeholder="Volume in (cc or mL)">
        </div>
        <div class="col">    
            <div class="macros">
                <h3>Macronutrients</h3>
                <button class="add-macros">Add Macro &nbsp; 
                <span style="font-size:16px; font-weight:bold;">+ </span>
                </button>
            </div>
            <div class="micros">
                <h3>Micronutrients</h3>
                <button class="add-micros">Add Micro &nbsp; 
                <span style="font-size:16px; font-weight:bold;">+ </span>
                </button>
            </div>
            <button class="btn btn-lg btn-primary" type="submit">Add Food</button>
            <p>Don't see your nutrient? <a href="foods.php?action=nutrient">add</a> it!
        </div>        
    </div>
</form>

<?php   break;
        case 'nutrient': ?>
        <?php 
            $result=NULL;
            if(isset($_POST['name']) && isset($_POST['rdv_amount']) && isset($_POST['rdv_unit'])){
                  $result = $db->add_nutrient($_POST['name'], (float)$_POST['rdv_amount'],$_POST['rdv_unit']);
              }
        ?>
        <div class="row">
            <div class="col">
            </div>
            <div class="col" style="text-align:center;">
                <form method="POST">
                    <label for="nutrient_name">Name</label>
                    <input type="text" id="nutrient_name" name="name" class="form-control">
                    <label for="rdv_amount">Recommended Daily Value</label>
                    <input type="number" step="0.01"min="0.01" id="rdv_amount" name="rdv_amount" class="form-control" placeholder="0">
                    <label for="rdv_unit">Unit</label>
                    <select id="rdv_unit" name="rdv_unit" class="form-control">
                        <option value="g">g</option>
                        <option value="mg">mg</option>
                        <option value="mcg">mcg</option>
                    </select>
                    <br>
                    <button class="btn btn-lg btn-primary" type="submit">Add Nutrient</button>
                </form>
                <?php if(isset($result) && ($result > 0 && $result != NULL)) {
                        echo '<div class="alert alert-success" role="alert">';
                        echo '    Nutrient added! ';
                        echo $result;
                        echo '</div>';
                    }
                    else if(isset($result) && $result != NULL && $result == -1){ 
                        echo '<div class="alert alert-danger" role="alert">';
                        echo '    Name was not entered or was left blank! ';
                        echo $result;
                        echo '</div>';
                    }
                    else if(isset($result) && $result != NULL && $result == -3){ 
                        echo '<div class="alert alert-danger" role="alert">';
                        echo '    RDV unit was not entered or was left blank! ';
                        echo $result;
                        echo '</div>';
                    }
                    else if(isset($result) && $result != NULL && $result == -4){ 
                        echo '<div class="alert alert-danger" role="alert">';
                        echo '    Some or all items were not entered or left blank! ';
                        echo $result;
                        echo '</div>';
                    }
                    else{}
                ?>
            </div>
            <div class="col">
            </div>
        </div>
<?php   break;
        endswitch; ?>
<?php else: 
        $summaryperiod = (isset($_GET['summaryperiod']) ? $_GET['summaryperiod'] : "monthly" );
        $start_date = '';
        $stop_date = '';
        if(isset($_GET['start_date']) && isset($_GET['stop_date']) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_GET['start_date']) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_GET['stop_date'])) {
            $start_date = $_GET['start_date'];
            $stop_date = $_GET['stop_date'];
        } else {
            switch($summaryperiod) {
                case 'today':
                    $start_date = date('Y-m-d');
                    $stop_date = date('Y-m-d');
                break;
                case 'weekly':
                    $start_date = date('Y-m-d', strtotime('7 days ago'));
                    $stop_date = date('Y-m-d');
                break;
                case 'monthly':
                default:
                    $summaryperiod = 'monthly';
                    $start_date = date('Y-m-d', strtotime('30 days ago'));
                    $stop_date = date('Y-m-d');
                break;
            }
        }    
?>
        <h2 class="mt-3">Your meals summary</h2>
        <div class="row">
            <div class="col-6">
                <h4>Macro percents</h4>
                <div id="piechart"></div>
                <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                <script type="text/javascript">
// Load google charts
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

// Draw the chart and set the chart values
function drawChart() {
  var data = google.visualization.arrayToDataTable(<?php
                $macro_calories_period = 'monthly';
                if(in_array($summaryperiod, ['today', 'weekly']))
                    $macro_calories_period = $summaryperiod;
                $macro_calories = $db->get_macro_calories($_SESSION['user_id'],$macro_calories_period);
                echo json_encode($macro_calories);
?>);

  // Optional; add a title and set the width and height of the chart
  var options = {'title':'Macros calories percents (<?php echo $macro_calories_period ?>)', 'width':500, 'height':300};

  // Display the chart inside the <div> element with id="piechart"
  var chart = new google.visualization.PieChart(document.getElementById('piechart'));
  chart.draw(data, options);
}
                </script>

                <pre>
                </pre>
            </div>
            <div class="col-6">
                <h4>Estimated calories consumed per day</h4>
<?php
        $net_cals = $db->query("SELECT * FROM calories_in_per_day WHERE user_id = ? ORDER BY date DESC LIMIT 10", [$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);
        $headers = [
            'date' => 'Date',
            'total_calories_in' => 'Calories consumed',
        ];
        draw_table($net_cals, $headers, true, 'netCals', 'table table-striped table-sm');
?>
            </div>
            <div class="col-4">
            </div>
        </div>
<?php
            switch($summaryperiod) {
                case 'monthly':
                    echo "            <h4>Meals totals (monthly) <a href=\"?summaryperiod=weekly\">weekly</a> <a href=\"?summaryperiod=today\">today</a></h4>";
                break;
                
                case 'weekly':
                    echo "            <h4>Meals totals <a href=\"?summaryperiod=monthly\">monthly</a> (weekly) <a href=\"?summaryperiod=today\">today</a></h4>";
                break;
                    echo "            <h4>Meals totals <a href=\"?summaryperiod=monthly\">monthly</a> (weekly) <a href=\"?summaryperiod=today\">today</a></h4>";
                
                case 'today':
                    echo "            <h4>Meals totals <a href=\"?summaryperiod=monthly\">monthly</a> <a href=\"?summaryperiod=weekly\">weekly</a> (today)</h4>";
                break;

                case 'range':
                    echo "            <h4>Meals totals <a href=\"?summaryperiod=monthly\">monthly</a> <a href=\"?summaryperiod=weekly\">weekly</a> <a href=\"?summaryperiod=today\">today</a></h4>";
                break;
                
            }
            $rows = $db->get_food_totals($_SESSION['user_id'], $start_date, $stop_date);
            $headers = [
                'food' => 'Food name',
                'meals' => 'Total Meals',
                'servings' => 'Total Servings',
                'calories_per_serving' => 'Cals / Serving',
                'calories' => 'Total Cals',
                'fat' => 'Total Fat',
                'carbs' => 'Total Carbs',
                'protein' => 'Total Protein',
                'fiber' => 'Total Fiber',
            ];
            draw_table($rows, $headers, true, 'foods-summary', 'table table-striped table-sm sortable');
?>
        <script src="https://www.w3schools.com/lib/w3.js"></script>
        <form method="GET">
            <div class="form-row form-inline">
                <div class="input-group mb-3 input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="start_date">Start date:</span>
                    </div>
                    <input class="form-control" type="date" id="start_date" name="start_date">
                </div>
                <div class="input-group mb-3 input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="stop_date">End date:</span>
                    </div>
                    <input class="form-control" type="date" id="stop_date" name="stop_date">
                </div>
                <div class="input-group mb-3 input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="summaryperiod">Period</span>
                    </div>
                    <input type="submit" class="btn-sm" id="summaryperiod" name="summaryperiod" value="range">
                </div>
            </div>
        </form>
<?php

        switch($summaryperiod) {
            case 'monthly':
                echo "                <h4>Monthly micronutrient consumption vs recommended amount</h4>\n";
                $sql = <<<SQL
                SELECT
                    name,
                    CONCAT(ROUND(micro_total_percent/100*rdv_amount/30,2), ' ', rdv_unit) AS avg_micro_mass_per_day,
                    CONCAT(rdv_amount, ' ', rdv_unit) AS recommended_amount,
                    ROUND(micro_total_percent/30) AS percent
                FROM micro_totals_monthly
                INNER JOIN nutrient ON nutrient.id = nutrient_id
                WHERE user_id = ?
                SQL;
                $headers = [
                    'name' => 'Micronutrient Name',
                    'avg_micro_mass_per_day' => 'Average daily intake',
                    'recommended_amount' => 'Recommended daily intake',
                    'percent' => '%'
                ];
                $rows = $db->query($sql,[$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);
                draw_table($rows, $headers, true, 'micros', 'table table-striped table-sm');
                break;
            case 'weekly':
                echo "                <h4>Weekly micronutrient consumption vs recommended amount</h4>\n";
                $sql = <<<SQL
                SELECT
                    name,
                    CONCAT(ROUND(micro_total_percent/100*rdv_amount/7,2), ' ', rdv_unit) AS avg_micro_mass_per_day,
                    CONCAT(rdv_amount, ' ', rdv_unit) AS recommended_amount,
                    ROUND(micro_total_percent/7) AS percent
                FROM micro_totals_weekly
                INNER JOIN nutrient ON nutrient.id = nutrient_id
                WHERE user_id = ?
                SQL;
                $headers = [
                    'name' => 'Micronutrient Name',
                    'avg_micro_mass_per_day' => 'Average daily intake',
                    'recommended_amount' => 'Recommended daily intake',
                    'percent' => '%'
                ];
                $rows = $db->query($sql,[$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);
                draw_table($rows, $headers, true, 'micros', 'table table-striped table-sm');
                break;
            case 'today':
                echo "                <h4>Today's micronutrient consumption vs recommended amount</h4>\n";
                $sql = <<<SQL
                SELECT
                    name,
                    CONCAT(ROUND(micro_total_percent/100*rdv_amount,2) , ' ', rdv_unit) AS mass_today,
                    CONCAT(rdv_amount, ' ', rdv_unit) AS recommended_amount,
                    micro_total_percent AS percent
                FROM micro_totals_today
                INNER JOIN nutrient ON nutrient.id = nutrient_id
                WHERE user_id = ?
                SQL;
                $headers = [
                    'name' => 'Micronutrient Name',
                    'mass_today' => 'Today\'s daily intake',
                    'recommended_amount' => 'Recommended daily intake',
                    'percent' => '%'
                ];
                $rows = $db->query($sql,[$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);
                draw_table($rows, $headers, true, 'micros', 'table table-striped table-sm');
                break;
        }
?>

<?php endif; ?>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type='text/javascript'>
    $(document).ready(function() {
        <?php 
            $macronutrients = $db->get_macronutrients();
            $micronutrients = $db->get_micronutrients();
            $sql = "SELECT id,name FROM food";
            $foods = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            // $foods = $db->get_foods();
        ?>
        // wrapper divs
        var macro_wrapper = $(".macros");
        var micro_wrapper = $(".micros");
        var food_wrapper = $(".foods");

        // content divs
        var add_macros = $(".add-macros");
        var add_micros = $(".add-micros");
        var add_foods = $("#add-foods");

        // used to store PHP arrays
        var macro_option_ids = new Array();
        var macro_option_names = new Array();
        <?php foreach($macronutrients as $key => $value) { ?>;
            macro_option_ids.push(`<?php echo $value['id']; ?>`);
            macro_option_names.push(`<?php echo $value['name']; ?>`);
        <?php } ?>;
        var micro_option_ids = new Array();
        var micro_option_names = new Array();
        // create array of options
        <?php foreach($micronutrients as $key => $value) { ?>;
            micro_option_ids.push(`<?php echo $value['id']; ?>`);
            micro_option_names.push(`<?php echo $value['name']; ?>`);
        <?php } ?>;
        var food_option_ids = new Array();
        var food_option_names = new Array();
        // create array of options
        <?php foreach($foods as $key => $value) { ?>;
            food_option_ids.push(`<?php echo $value['id']; ?>`);
            food_option_names.push(`<?php echo $value['name']; ?>`);
        <?php } ?>;

        // counters
        var num_macros = 0;
        var num_micros = 0;
        var num_foods = 0;

        // function to add form fields for macronutrients
        $(add_macros).click(function(e) {
            e.preventDefault();
            // while number of macros is less than the total amount macros available, we can execute this block
            if (num_macros < macro_option_ids.length) {

                // used to create/append select options dynamically
                var macro_html = `<div><select name="macro_id[]">`;
                
                for(var i = 0; i < macro_option_ids.length; i++){
                    macro_html = macro_html.concat(`<option value=${macro_option_ids[i]}>${macro_option_names[i]}</option>`);
                }

                // add the rest to macro_html to close it up
                macro_html = macro_html.concat(`</select><input type="number" step="0.01" min="0.01" name="macro_g[]" style="width:5em"/>grams <a href="#" class="delete-macro"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red;">&#x24E7;</span></a></div>`);
                $(macro_wrapper).append(macro_html); //add input box
                num_macros++; // add to macro counter
            } else {
                alert('No more macronutrients available')
            }
        });
        // function to remove form fields for macronutrients
        $(macro_wrapper).on("click", ".delete-macro", function(e) {
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        })

        // function to add form fields for micronutrients
        $(add_micros).click(function(e) {
            e.preventDefault();
            // allow append to execute as long as x is less than the number of macronutrients
            if (num_micros < micro_option_ids.length) {
                
                var micro_html = `<div><select name="micro_id[]">`;
                for(var i = 0; i < micro_option_ids.length; i++){
                    micro_html = micro_html.concat(`<option value=${micro_option_ids[i]}>${micro_option_names[i]}</option>`);
                }   
                micro_html = micro_html.concat(`</select><input type="number" step="0.01"min="0.01" name="micro_dv[]" style="width:5em"/>% <a href="#" class="delete-micro"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red;">&#x24E7;</span></a></div>`);
                $(micro_wrapper).append(micro_html); //add input box
                num_micros++;
            } else {
                alert('No more micronutrients available')
            }
        });
        // function to remove form fields for micronutrients
        $(micro_wrapper).on("click", ".delete-micro", function(e) {
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        });

        // function to add form fields for foods
        $(add_foods).click(function(e) {
            e.preventDefault();
            // allow append to execute as long as x is less than the number of macronutrients
            if (num_foods < food_option_ids.length) {      
                var food_html = `<div><select class="form-control" name="food_id[]">`;
                for(var i = 0; i < food_option_ids.length; i++){
                    food_html = food_html.concat(`<option value=${food_option_ids[i]}>${food_option_names[i]}</option>`);
                }   
                food_html = food_html.concat(`</select>
                                                <label for="servings">Amount</label>
                                                <input id="servings" type="number" step="0.01" min="0.01" name="servings[]" style="width:5em"/>
                                                <label for="unit">Unit</label>
                                                <select name="unit[]" id="unit">
                                                    <option value="serving">serving</option>
                                                    <option value="L">L</option>
                                                    <option value="mL">mL</option>
                                                    <option value="gal">gal</option>
                                                    <option value="qt">qt</option>
                                                    <option value="pt">pt</option>
                                                    <option value="cup">cup</option>
                                                    <option value="g">g</option>
                                                    <option value="kg">kg</option>
                                                    <option value="lb">lb</option>
                                                    <option value="tsp">tsp</option>
                                                    <option value="oz">oz</option>
                                                    <option value="fl oz">fl oz</option>
                                                    <option value="tbsp">tbsp</option>
                                                    <option value="tsp">tsp</option>
                                                </select> 
                                                <a href="#" class="delete-food">
                                                <span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red;">&#x24E7;</span></a>
                                                </div>`);
                
                $(food_wrapper).append(food_html); //add input box
                num_foods++;
            } else {
                alert('No more food options available')
            }
        });
        // function to remove form fields for foods
        $(food_wrapper).on("click", ".delete-food", function(e) {
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        });
    });
    </script>
<?php include_once '../templates/footer.php';
