<?php
/**
 * @file library.php
 * General functions
 * 
 * This file contains functions that may be useful in multiple places.
 * It includes the DB class definition which acts as a wrapper for an instance of the PDO class.
 */


/**
 * Draw a result set as an HTML table
 * 
 * This function takes an array of arays such as what you would receive from a call to
 * `$pdo->query()->fetchAll()` and generates HTML for a table, which is written out.
 * 
 * @author @zgjs
 * @param rows An reference to an array of arrays, where each array item is a row to be printed.
 * @param headers An optional array of custom headers that will be printed instead of database column names; can be either sequential array or associative array
 * @param sortable should the table be sortable
 * @param id give the table its own id
 * @param class table class
 * @return void
 * @example draw_table($rows); // prints table with columns in same order as result, with database column names as headings
 * @example draw_table($rows, ['Student ID', 'Student Name', 'Status']); // prints table with columns in same order as result, with pretty names
 * @example draw_table($rows, ['status' => 'Enrollment Status', 'name' => 'Student Name']) // prints table in the order of the associative array keys, and with pretty names matching the associative array values
 */
function draw_table(array &$rows, ?array $headers = NULL, bool $sortable = false, string $id = "myTable", string $class = "table-striped") {
    echo "    <table border=1 colspan=1 class=\"$class\" id=\"$id\">\n";
    echo "        <thead>\n";
    echo "            <tr>\n                ";
    $tdcount = 0;
    if (is_null($headers)) {
        // If the headers parameter was not specified, use database column names as table headers
        // Example: <th>id</th><th>name</th><th>status</th>
        foreach (array_keys($rows[0]) as $th) {
            $tdcount++;
            if($sortable) {
                echo "<th onclick=\"w3.sortHTML('#$id', '.item', 'td:nth-child($tdcount)')\" style=\"cursor:pointer\">$value</th>";
            } else {
                echo "<th>$th</th>";
            }
        }
    } elseif(count(array_filter(array_keys($headers), 'is_string')) == 0) {
        // If the headers parameter was specified, and contains only numeric keys, use valuesin the sequential array as table headers
        // Example: </th><th>Student Name</th><th>Status</th>
        foreach ($headers as $th) {
            $tdcount++;
            if($sortable) {
                echo "<th onclick=\"w3.sortHTML('#$id', '.item', 'td:nth-child($tdcount)')\" style=\"cursor:pointer\">$value</th>";
            } else {
                echo "<th>$th</th>";
            }
        }
    } else {
        // If the headers parameter was specified and contains non-numeric keys, use values specified by the associatvie array as table headers
        // Example: <th>Status</th><th>Student Name</th>
        foreach ($headers as $key => $value) {
            $tdcount++;
            if($sortable) {
                echo "<th onclick=\"w3.sortHTML('#$id', '.item', 'td:nth-child($tdcount)')\" style=\"cursor:pointer\">$value</th>";
            } else {
                echo "<th>$value</th>";
            }
        }
        echo "\n            </tr>\n";
        echo "\n        </thead>\n";
        echo "\n        <tbody>\n";
        foreach ($rows as $row) {
            if($sortable) {
                echo "            <tr class=\"item\">\n                ";
            } else {
                echo "            <tr>\n                ";
            }
            foreach (array_keys($headers) as $key) {
                echo "<td>{$row[$key]}</td>";
            }
            echo "\n            </tr>\n";
        }
        echo "        </tbody>\n";
        echo "     </table>\n";
        return;
    }
    echo "\n            </tr>\n";
    echo "\n         </thead>\n";
    foreach ($rows as $row) {
        if($sortable) {
            echo "            <tr class=\"item\">\n                ";
        } else {
            echo "            <tr>\n                ";
        }
        foreach ($row as $td) {
            echo "<td>$td</td>";
        }
        echo "\n            </tr>\n";
    }
    echo "        </tbody>\n";
    echo "     </table>\n";
}

/**
 * Check if visitor is authenticated
 * 
 * This function looks at the $_SESSION variables to see if the visitor is authenticated or not.
 * 
 * @author @zgjs
 * @return bool
 * @see "Project issue #23"
 */
function is_authenticated(): bool {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['auth_status']) && $_SESSION['auth_status'];
}

/**
 * Redirect to some other page
 * 
 * @author @zgjs
 * @param page The page to redirect to
 * @param message The message to display if the client has redirects disabled
 * @return void
 * @example redirect("login.php", "You must be signed in.");
 */
function redirect(string $page, string $message) {
    $home = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
    header("Location: $home/$page", true, 303);
    die ("<html><body>$message <a href=\"$page\">Click here if your are not redirected automatically.</a></body></html>\n");
}

/**
 * Require a user to be signed in to access this page
 * 
 * @author @zgjs
 * @return void
 */
function require_signed_in() {
    if(!is_authenticated()) {
        redirect("login.php", "You must be signed in.");
    }
}
?>
