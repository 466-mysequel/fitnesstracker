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




/**
 * Convert from one unit to another
 * 
 * This function first converts the beginning unit to Milliliters/Cubic Centimeters
 * by multiplicating the conversion ratio from the beginning unit to Milliliters then divides
 * by the conversion ratio from the milliliters to the end Unit.
 * 
 * @author z1868762
 * @param qty - the amount of the beginning unit that needs to be converted
 * @param beginUnit - the symbol unit before converting
 * @param endUnit - the symbol unit after converting
 * @return float - the quantity of the end unit after conversion. Returns -1 on error
 * @example - convertVolume(56.3, tsp, L); - Converts 56.3 tablespoons into Liters
 * @example - convertVolume(13, oz, pt); - Converts 13 fluid ounces into pints
 * @see "Project Issue #41"
 * 
 */
function convertVolume(float $qty, string $beginUnit, string $endUnit)
{
    $literRatio = 1000;
    $gallonRatio = 3785.41;
    $quartRatio = 946.353;
    $pintRatio = 473.176;
    $cupRatio = 236.588;
    $ounceRatio = 29.5735;
    $tbspRatio = 14.7868;
    $tspRatio = 4.92892;
    //since we're converting to milliliters, no math needs to be done if begin unit is mililiters
    if($beginUnit == "mL")  {   $value = $qty;  }

    //liters to milliliters
    else if($beginUnit == "L")      {   $value = $qty * $literRatio;    }

    //gallons to milliliters
    else if($beginUnit == "gal")    {   $value = $qty * $gallonRatio;   }

    //quart to milliliters
    else if($beginUnit == "qt")     {   $value = $qty * $quartRatio;    }

    //pint to milliliters
    else if($beginUnit == "pt")     {   $value = $qty * $pintRatio;     }

    //cup to milliliters
    else if($beginUnit == "cup")    {   $value = $qty * $cupRatio;      }

    //ounces to milliliters
    else if($beginUnit == "oz")     {   $value = $qty * $ounceRatio;    }

    //tablespoon to milliliters
    else if($beginUnit == "tbsp")   {   $value = $qty * $tbspRatio;     }

    //teaspoon to milliliters
    else if($beginUnit == "tsp")    {   $value = $qty * $tspRatio;      }

    //invalid beginning unit
    else    {   echo "Beginning unit not found";    return -1;  }

    //converting from mililliters to ending unit with division

    //if user wants milliliters, no change needed
    if($endUnit == "mL")   {   return $value;  }

    //milliliters to liters
    else if($endUnit == "L")        {   return $value/$literRatio;      }

    //milliliters to gallons
    else if($endUnit == "gal")      {   return $value/$gallonRatio;     }

    //milliliters to quart 
    else if($endUnit == "qt")       {   return $value/$quartRatio;      }

    // milliliters to pint 
    else if($endUnit == "pt")       {   return $value/$pintRatio;       }

    // milliliters to cup  
    else if($endUnit == "cup")      {   return $value/$cupRatio;        }

    //milliliters to ounces         
    else if($endUnit == "oz")       {   return $value/$ounceRatio;      }

    //milliliters to tablespoon 
    else if($endUnit == "tbsp")     {   return $value/$tbspRatio;       }

    //milliliters to teaspoons  
    else if($endUnit == "tsp")      {   return $value/$tspRatio;         }

    //invalid ending unit
    else    {   echo "ending unit not found";   return -1;  }
}
?>
