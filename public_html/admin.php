<?php
// Enable strict typing and display all errors
declare(strict_types = 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Includes
include_once '../src/library.php';

// Require admin login
include '../config/config.php';
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $username || $_SERVER['PHP_AUTH_PW'] != $password) {
    header('WWW-Authenticate: Basic realm="Admin"');
    header('HTTP/1.0 401 Unauthorized');
    die("<html>\n<body>\n<h1>401 Unauthorized</h1>\n<p>You must authenticate to access this page</p>\n</body>\n</html>");
}

// Start writing the page
$page_title = "Fitness Tracker &rsaquo; Admin";
include '../templates/header.php';
$pdo = new PDO("mysql:host=$servername;dbname=$dbname;", $username, $password);
?>
    <!-- Page Content -->
    <main role="main" class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Manage the database.</p>
<?php
if(isset($_POST['action']) && $_POST['action'] == 'reset') {
    // Clean up the SQL files before running them. Remove comments and script echos.
    $sql = "";
    foreach(explode("\n",file_get_contents('../sql/tables.sql') . file_get_contents('../sql/views.sql') . file_get_contents('../sql/sampledata.sql')) as $line) {
        if(!(preg_match('/^[#\\\\]|^DELIMITER/', $line))) {
            $sql .= preg_replace('/^END\$\$/', 'END;', preg_replace('/#.*$/', '', $line)) . "\n";
        }
    }
    $pdo->exec($sql);
    echo "<p class=\"lead\" style=\"color: #33aa33\">The database was reset.</p>\n";
}
?>
        <div class="row">
            <div class="col-3">
                <h2>Tables</h2>
<?php
$tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_ASSOC);
foreach($tables as &$table) {
    $table['rows'] = $pdo->query("SELECT COUNT(*) FROM " . $table["Tables_in_$dbname"])->fetchColumn();
}
draw_table($tables, ["Tables_in_$dbname" => "Table Name", 'rows' => "Rows"]);
?>
            </div>
            <div class="col-3">
                <h2>Views</h2>
                <?php $views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_ASSOC); draw_table($views, ["Tables_in_$dbname" => "View Name"]); ?>
            </div>
            <div class="col-3">
                <h2>Functions</h2>
                <?php $views = $pdo->query("SHOW FUNCTION STATUS")->fetchAll(PDO::FETCH_ASSOC); draw_table($views, ["Name" => "Function Name"]); ?>
            </div>
            <div class="col-3">
                <h2>Procedures</h2>
                <?php $views = $pdo->query("SHOW PROCEDURE STATUS")->fetchAll(PDO::FETCH_ASSOC); draw_table($views, ["Name" => "Procedure Name"]); ?>
            </div>
        </div>
        <p>
            <form method="POST">
                <button type="submit" class="btn btn-primary" name="action" value="reset">Reset the database</button>
            </form>
        </p>
    </main>
<?php include '../templates/footer.php'; ?>
