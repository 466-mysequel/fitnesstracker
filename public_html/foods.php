<?php
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include_once '../src/db.php';
$db = new DB();
include_once '../src/library.php'; 
$page_title = "Fitness Tracker &rsaquo; Foods";
$result=NULL;
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
        case 'log': ?>

<?php if(isset($_SESSION['user_id']) && isset($_POST['food_id']) && isset($_POST['servings'])) {
    $result = $db->log_food($_SESSION['user_id'],$_POST['food_id'],$_POST['servings']);
} ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <form method="POST">
            <h3>Keep track of your meals</h3>
            <div class="form-row">
                <div class="form-group col-md-10">
                    <label for="mealtime" class="col-form-label"><h4>Date and time</h4></label>
                    <input class="form-control" type="datetime-local" value="" id="mealtime" name="date">
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
                food_html = food_html.concat(`</select><input type="number" min="1" name="servings[]" style="width:3em"/> Serving(s) <a href="#" class="delete-food"><span style="font-size: 1.5em; color: transparent; text-shadow: 0 0 0 red;">&#x24E7;</span></a></div>`);
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
