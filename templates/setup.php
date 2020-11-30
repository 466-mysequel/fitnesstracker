    <div class="jumbotron text-center">
        <br>
        <h1 class="h2 mb-2">Fitness Tracker &rsaquo; Setup</h1>
    </div>
    <div class="container text-center">
<?php
if(isset($_POST['servername']) && isset($_POST['dbname']) && isset($_POST['username']) && isset($_POST['password'])) {
    try {
        $pdo = new PDO("mysql:host={$_POST['servername']};dbname={$_POST['dbname']};",$_POST['username'],$_POST['password']);
        if(isset($_POST['runsqlscripts']) && $_POST['runsqlscripts'] == 1) {
            // Clean up the SQL files before running them. Remove comments and script echos.
            $sql = "";
            foreach(explode("\n",file_get_contents('../sql/tables.sql') . file_get_contents('../sql/views.sql') . file_get_contents('../sql/sampledata.sql')) as $line) {
                if(!(preg_match('/^[#\\\\]|^DELIMITER/', $line))) {
                    $sql .= preg_replace('/^END\$\$/', 'END;', preg_replace('/#.*$/', '', $line)) . "\n";
                }
            }
            $pdo->exec($sql);
            echo "        <p class=\"lead\">[Re]created tables, views, and sample data.</p>\n";
        }
        $config = <<<PHP
        <?php
        // Database
        \$servername = "{$_POST['servername']}";
        \$username = "{$_POST['username']}";
        \$password = "{$_POST['password']}";
        \$dbname = "{$_POST['dbname']}";
        ?>
        PHP;
        if(file_put_contents('../config/config.php', $config, LOCK_EX)) {
            echo "        <p class=\"lead\">Config file written successfully.<br>\n        <a href=\"index.php\">Home</a></p>\n";
        } else {
            echo "        <p class=\"lead\">There was a problem writing to <code>config.php</code> file. Create it manually or run <code>setup.sh</code>.</p>\n";
        }
    } catch (PDOException $e) {
        echo "        <p class=\"lead\">Could not connect to database:<br>\n{$e->getMessage()}<br>\n<a href=\"javascript:window.history.back();\">Back</a></p>";
    }
} elseif (is_writable("../config/") || is_writable("../config/config.php")) {
    $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
    $dirs = explode("/",$_SERVER['REQUEST_URI']);
    if(array_key_exists (1, $dirs) && $dirs[1][0] == '~') {
        $suggestion = substr($dirs[1],1);
    } else {
        $suggestion = "fitnesstracker";
    }
    echo <<<HTML
            <p class="lead">Enter the database credentials.</p>
            <form method="POST" class="form-signin">
                <div class="form-group row">
                    <div class="col-sm-2">&nbsp;</div>
                    <label for="servername" class="col-sm-3 col-form-label-lg">Database Server:</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control form-control-lg" id="servername" name="servername" aria-describedby="servernameHelp" placeholder="Enter server name" value="$subdomain">
                        <small id="servernameHelp" class="form-text text-muted">Hint: Is the database server name maybe <code>$subdomain</code>?</small>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2">&nbsp;</div>
                    <label for="dbname" class="col-sm-3 col-form-label-lg">Database Name:</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control form-control-lg" id="dbname" name="dbname" aria-describedby="dbnameHelp" placeholder="Enter database name" value="$suggestion">
                        <small id="dbnameHelp" class="form-text text-muted">Hint: Is it maybe <code>$suggestion</code>?</small>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2">&nbsp;</div>
                    <label for="username" class="col-sm-3 col-form-label-lg">Database Username:</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control form-control-lg" id="username" name="username" aria-describedby="usernameHelp" placeholder="Enter database username" value="$suggestion">
                        <small id="usernameHelp" class="form-text text-muted">Hint: Is it maybe <code>$suggestion</code>?</small>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2">&nbsp;</div>
                    <label for="password" class="col-sm-3 col-form-label-lg">Database Password:</label>
                    <div class="col-sm-4">
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password">
                        <small id="passwordHelp" class="form-text text-muted">Hint: Is it maybe your birthday in <code>YYYYMmmDD</code> format?</small>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2">&nbsp;</div>
                    <div class="col-sm-8">
                        <input type="checkbox" class="form-check-input-lg" id="runsqlscripts" name="runsqlscripts" value="1" checked>
                        <label class="form-check-label col-form-label-lg" for="runsqlscripts">[re]create tables and insert sample data</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2">&nbsp;</div>
                    <div class="col-sm-8"><button type="submit" class="btn btn-primary">Submit</button></div>
                </div>
            </form>
    HTML;
} else {
    echo "<p class=\"lead\">The <code>config.php</code> file is not writeable by the webserver process (running as ". getenv('APACHE_RUN_USER') . ':' . getenv('APACHE_RUN_GROUP') . ").<br>\nCreate it manually or run <code>setup.sh</code>.</p>\n";
}
?>
    </div>
