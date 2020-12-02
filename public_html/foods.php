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
                $serving = $value;
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
                $serving[] = $conv_unit[$key] / $food['serving_size_grams'];
            } else {
                continue;
            }
        }
        
        $log_food_timestamp = $db->log_food($_SESSION['user_id'], $_POST['food_id'], $serving, $_POST['date'] . ' ' . $_POST['time']);
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
            else:
                echo "        <h3 class=\"mt-3\">Your meal history</h2>\n";
                $meals = $db->get_meals((int)$_SESSION['user_id']);
                foreach($meals as $meal):
                    echo "\n            <h5 class=\"mt-3\"><a href=\"?action=history&timestamp=" . $meal['unixtime'] . "\">". date('h:i:s A l, jS \of F Y', $meal['unixtime']) . "</a></h3>\n";
                    draw_table($meal['foods'], ['name' => 'Food', 'calories' => 'Calories']);
                endforeach;
            endif;
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
                            Added sugars
                            <?php echo (isset($macros['Added sugars']) ? $macros['Added sugars']['amount'] : 0 ); ?>g
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
            <input type="text" id="name" name="name" class="form-control" placeholder="egg">
            <label for="type" >Type</label>
            <select id="type" name="type" class="form-control">
                <option value="solid">Solid</option>
                <option value="liquid">Liquid</option>
            </select>
            <label for="serving_size_friendly" >Serving Size</label>
            <input type="text" id="serving_size_friendly" name="serving_size_friendly" class="form-control" placeholder="i.e., 1 egg">
            <label for="calories" >Calories Per Serving</label>
            <input type="number" min="0" id="calories" name="calories_per_serving" class="form-control" placeholder="100">
            <label for="serving_size_grams" >Serving Size (g)</label>
            <input type="number" min="0" id="serving_size_grams" name="serving_size_grams" class="form-control" placeholder="50">
            <label for="serving_size_cc" >Serving Size (cc)</label>
            <input type="number" min="0" id="serving_size_cc" name="serving_size_cc" class="form-control" placeholder="optional">
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
                    <input type="number" min="0" id="rdv_amount" name="rdv_amount" class="form-control" placeholder="0">
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
                macro_html = macro_html.concat(`</select><input type="number" min="0" name="macro_g[]" style="width:3em"/>grams <a href="#" class="delete-macro"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red;">&#x24E7;</span></a></div>`);
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
                micro_html = micro_html.concat(`</select><input type="number" min="0" name="micro_dv[]" style="width:3em"/>% <a href="#" class="delete-micro"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red;">&#x24E7;</span></a></div>`);
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
                                                <input id="servings" type="number" min="1" name="servings[]" style="width:3em"/>
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
