<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/db.php';
include_once '../src/library.php';
include_once '../src/convert.php';              //convert class functions needed
//make sure user is login   
session_start();
$home = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
if(!is_authenticated()) {
    header("Location: $home/login.php", true, 303);
    die ("<html><body>You must be logged in. <a href=\"login.php\">Click here if your are not redirected automatically.</a></body></html>\n");
}
$db = new DB();
$user = $db->get_user($_SESSION['user_id']);
if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['username']) && isset($_POST['password'])) {
    $valid_username = strlen($_POST['username']) >= 3 || $_POST['username'] == '';
    $valid_password = strlen($_POST['password']) >= 8 || $_POST['password'] == '';
    $valid_name = (strlen($_POST['first_name']) >= 2 || $_POST['first_name'] = '') && (strlen($_POST['last_name']) >= 2 || $_POST['last_name'] == '');
    $update_result = $db->update_user($_SESSION['user_id'], $_POST['username'], $_POST['password'], $_POST['first_name'], $_POST['last_name']);
    if($update_result) $user = $db->get_user($_SESSION['user_id']);
}

if(isset($_POST['weight'])) {
    if(!empty($_POST['weight'])){
        //converting the weight string into an integer
        $a = $_POST['weight'];
        $a = +$a;                       
        //converting in case the unit is in lb                  
        if($_POST['unit'] == "lb" )    
        {
            $grams = convert::mass_to_g($a, "lb");
            $kilograms = convert :: mass_from_g($grams, "kg");
            $db->log_weight($_SESSION['user_id'],$kilograms);
        }
        //execute log_weight if unit is already in kg
        else
        {
            $db->log_weight($_SESSION['user_id'],$a);
        }
        $update_weight = true;
        //echo "<p> Weight Updated Successfully </p>";
    }
    else {
        $update_weight = false;
    }
}



// Start writing the page
$page_title = "Fitness Tracker &rsaquo; My Account";
$style = <<<CSS
#warning {
    color: #aa3333;
}
/* the following css is for the password validation and message implementation
    here is the source: https://www.w3schools.com/howto/howto_js_password_validation.asp */ 

/* The message box is shown when the user clicks on the password field */
#message {
  display:none;
  background: #f1f1f1;
  color: #000;
  position: relative;
  padding: 20px;
  margin-top: 10px;
}

#message p {
  padding: 2px 10px 2px 10px;
  text-align: left;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" icon when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
}
CSS;
include '../templates/header.php';
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <h1>Hello <?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h1>
        
        <div class="row">
            <div class="col-6">
                <p class="lead">Manage your account</p>
                <?php if(isset($update_result)) echo $update_result ? "<p style=\"color: #33aa33\">Update sucessful!</p>\n" : "<p style=\"color: #aa3333\">Update unsucessful!</p>\n"; ?>
                <form method="POST">
                    <label for="first_name" class="sr-only">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First name">
                    <label for="last_name" class="sr-only">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last name">
                    <label for="username" class="sr-only">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username">
                    <label for="password" class="sr-only">Password</label>
                    <!-- password validation form and alert message from: https://www.w3schools.com/howto/howto_js_password_validation.asp -->
                    <input type="password" patter="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" id="password" name="password" class="form-control" placeholder="Password" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters">
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Update account</button>
                    <?php if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['username']) && isset($_POST['password'])): ?> 
                        <div id="warning"> 
                            <?php 
                                if(isset($valid_username) && !$valid_username) echo "<p>Username invalid</p>";
                                if(isset($valid_password) && !$valid_password) echo "<p>Password invalid</p>";
                                if(isset($valid_name) && !$valid_name) echo "<p>Name invalid</p>";    
                            ?>
                        </div>
                    <?php endif; ?>
                    <div id="message">
                        <b>Password must contain the following:</b>
                        <p id="letter" class="invalid">A <b>lowercase</b> letter</p>
                        <p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
                        <p id="number" class="invalid">A <b>number</b></p>
                        <p id="length" class="invalid">Minimum <b>8 characters</b></p>
                    </div>
                </form>
                <!-- from w3 schools, source: https://www.w3schools.com/howto/howto_js_password_validation.asp -->

                <script>
                    var myInput = document.getElementById("password");
                    var letter = document.getElementById("letter");
                    var capital = document.getElementById("capital");
                    var number = document.getElementById("number");
                    var length = document.getElementById("length");

                    // When the user clicks on the password field, show the message box
                    myInput.onfocus = function() {
                    document.getElementById("message").style.display = "block";
                    }

                    // When the user clicks outside of the password field, hide the message box
                    myInput.onblur = function() {
                    document.getElementById("message").style.display = "none";
                    }

                    // When the user starts to type something inside the password field
                    myInput.onkeyup = function() {
                    // Validate lowercase letters
                    var lowerCaseLetters = /[a-z]/g;
                    if(myInput.value.match(lowerCaseLetters)) {
                        letter.classList.remove("invalid");
                        letter.classList.add("valid");
                    } else {
                        letter.classList.remove("valid");
                        letter.classList.add("invalid");
                    }

                    // Validate capital letters
                    var upperCaseLetters = /[A-Z]/g;
                    if(myInput.value.match(upperCaseLetters)) {
                        capital.classList.remove("invalid");
                        capital.classList.add("valid");
                    } else {
                        capital.classList.remove("valid");
                        capital.classList.add("invalid");
                    }

                    // Validate numbers
                    var numbers = /[0-9]/g;
                    if(myInput.value.match(numbers)) {
                        number.classList.remove("invalid");
                        number.classList.add("valid");
                    } else {
                        number.classList.remove("valid");
                        number.classList.add("invalid");
                    }

                    // Validate length
                    if(myInput.value.length >= 8) {
                        length.classList.remove("invalid");
                        length.classList.add("valid");
                    } else {
                        length.classList.remove("valid");
                        length.classList.add("invalid");
                    }
                    }
                </script>
            </div>
            <div class="col-6">
                <p class="lead">Update your weight</p>
                <?php if(isset($update_weight)) echo $update_weight ? "<p style=\"color: #33aa33\">Weight sucessfully updated</p>\n" : "<p style=\"color: #aa3333\">Weight unsuccessfully updated</p>\n"; ?>
                <form method="POST" class="form-inline">
                    <label for="weight" class="sr-only">Weight</label>
                    <input type="text" id="weight" name="weight" class="form-control" placeholder="Weight">
                    <label for="weight" class="sr-only">Unit</label>
                    <select class="form-control" name="unit" id="unit">
                        <option value="kg">kg</option>
                        <option value="lb">lbs</option>
                    </select>
                    <button class="btn btn-lg btn-primary" type="submit">Update weight</button>
                </form>
            </div>
        </div>
    </main>
<?php include '../templates/footer.php'; ?>
