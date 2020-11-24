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
 * @return void
 * @example draw_table($rows); // prints table with columns in same order as result, with database column names as headings
 * @example draw_table($rows, ['Student ID', 'Student Name', 'Status']); // prints table with columns in same order as result, with pretty names
 * @example draw_table($rows, ['status' => 'Enrollment Status', 'name' => 'Student Name']) // prints table in the order of the associative array keys, and with pretty names matching the associative array values
 */
function draw_table(array &$rows, ?array $headers = NULL) {
    echo "        <table border=1 colspan=1>\n";
    echo "            <tr>\n                ";
    if (is_null($headers)) {
        // If the headers parameter was not specified, use database column names as table headers
        // Example: <th>id</th><th>name</th><th>status</th>
        foreach (array_keys($rows[0]) as $th) {
            echo "<th>$th</th>";
        }
    } elseif(count(array_filter(array_keys($headers), 'is_string')) == 0) {
        // If the headers parameter was specified, and contains only numeric keys, use valuesin the sequential array as table headers
        // Example: </th><th>Student Name</th><th>Status</th>
        foreach ($headers as $th) {
            echo "<th>$th</th>";
        }
    } else {
        // If the headers parameter was specified and contains non-numeric keys, use values specified by the associatvie array as table headers
        // Example: <th>Status</th><th>Student Name</th>
        foreach ($headers as $key => $value) {
            echo "<th>$value</th>";
        }
        echo "\n            </tr>\n";
        foreach ($rows as $row) {
            echo "            <tr>\n                ";
            foreach (array_keys($headers) as $key) {
                echo "<td>{$row[$key]}</td>";
            }
            echo "\n            </tr>\n";
        }
        echo "        </table>\n";
        return;
    }
    echo "\n            </tr>\n";
    foreach ($rows as $row) {
        echo "            <tr>\n                ";
        foreach ($row as $td) {
            echo "<td>$td</td>";
        }
        echo "\n            </tr>\n";
    }
    echo "        </table>\n";
}

/**
 * Check if visitor is authenticated
 * 
 * This function looks at the $_SESSION variables to see if the visitor is authenticated or not.
 * 
 * @return bool
 * @see "Project issue #23"
 */
function is_authenticated(): bool {
    return isset($_SESSION['auth_status']) && $_SESSION['auth_status'];
}

?>
