<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/db.php';
include_once '../src/library.php';

$home = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .  $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
if(is_authenticated()) {
    header("Location: $home/index.php", true, 303);
    die ("<html><body>You are already logged in. <a href=\"index.php\">Click here if your are not redirected automatically.</a></body></html>\n");
}
$result = 0;
if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['username']) && isset($_POST['password'])) {
    $valid_username = strlen($_POST['username']) >= 3;
    $valid_password = strlen($_POST['password']) >= 8;
    $valid_name = strlen($_POST['first_name']) >= 2 && strlen($_POST['last_name']) >= 2;

    if($valid_username && $valid_password && $valid_name) {
        $db = new DB();
        $result = $db->add_user($_POST['username'],$_POST['password'],$_POST['first_name'],$_POST['last_name']);
        if($result > 0) {
            header("Location: $home/account.php", true, 303);
            session_start();
            $_SESSION['auth_status'] = true;
            $_SESSION['user_id'] = $result;
            die ("<html><body>Successfully signed up. <a href=\"account.php\">Click here if your are not redirected automatically.</a></body></html>\n");
        }
    }
}

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; Sign Up";
$style = <<<CSS
body {
    background-color: #f5f5f5;
}
.form-signin {
    align-items: center;
    justify-content: center;
    padding-top: 40px;
    padding-bottom: 40px;
    width: 100%;
    max-width: 400px;
    padding: 15px;
    margin: 40px auto;
}
.form-signin .checkbox {
    font-weight: 400;
}
.form-signin .form-control {
    position: relative;
    box-sizing: border-box;
    height: auto;
    padding: 10px;
    font-size: 16px;
}
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
    <form method="POST" class="form-signin text-center">
        <span style="font-size: 4em;">&#x1F3CB;&#xFE0F;</span>
        <h1 class="h3 mb-3 font-weight-normal">Please sign up</h1>
        <label for="first_name" class="sr-only">First Name</label>
        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name" required autofocus>
        <label for="last_name" class="sr-only">Last Name</label>
        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name" required>
        <label for="username" class="sr-only">Username</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
        <label for="password" class="sr-only">Password</label>
        <!-- password validation form and alert message from: https://www.w3schools.com/howto/howto_js_password_validation.asp -->
        <input type="password" patter="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" id="password" name="password" class="form-control" placeholder="Password" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign Up</button>
        <?php if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['username']) && isset($_POST['password'])): ?> 
            <div id="warning"  > 
                <?php 
                    if(!$valid_username) echo "<p>Username invalid</p>";
                    if(!$valid_password) echo "<p>Password invalid</p>";
                    if(!$valid_name) echo "<p>Name invalid</p>";
                    if($result == -2) echo "<p>Username already exists</p>";
                    if($result == 0) echo "<p>Error! Try again later</p>";       
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
<?php
include '../templates/footer.php';
?>

