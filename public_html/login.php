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
if(isset($_POST['username']) && isset($_POST['password']) && check_password($_POST['username'], $_POST['password'])) {
    header("Location: $home/index.php", true, 303);
    die ("<html><body>You have successfully logged in. <a href=\"index.php\">Click here if your are not redirected automatically.</a></body></html>\n");
}

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; Sign In";
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
    max-width: 330px;
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
CSS;
include '../templates/header.php';
?>
    <form method="POST" class="form-signin text-center">
        <span style="font-size: 4em;">&#x1F3CB;&#xFE0F;</span>
        <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
        <label for="username" class="sr-only">Username</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required autofocus>
        <label for="password" class="sr-only">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
        <?php if(isset($_POST['username']) || isset($_POST['password'])) echo "<p style=\"color: #aa3333;\">Incorrect credentials.</p>\n"; ?>
        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" id="remember-me" name="remember-me" value="1"> Remember me
            </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>
<?php
include '../templates/footer.php';
?>
