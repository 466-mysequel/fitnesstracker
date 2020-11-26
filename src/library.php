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
 * Convert from one unit of volume to another
 * 
 * This function first converts the beginning unit to Milliliters/Cubic Centimeters
 * by multiplicating the conversion ratio from the beginning unit to Milliliters 
 * 
 * @author z1868762
 * @param qty - the amount of the beginning unit that needs to be converted
 * @param $begin_unit - the symbol unit before converting
 * @return float - the quantity of the end unit after conversion. Returns -1 on error
 * @example - volume_to_cc(56.3, "tsp"); - Converts 56.3 tablespoons into mL/cc
 * @example - volume_to_cc(13, "fl oz"); - Converts 13 fluid ounces into mL/cc
 * @warning - DO NOT USE "oz" for fluid ounces. Mass also uses ounces as a unit, so I'd like to seperate 
 *            "fl oz" for fluid ounces in volume and "oz" for ounces in mass to avoid confusion
 * @see "Project Issue #41"
 * 
 */
function volume_to_cc(float $qty, string $begin_unit)
{
    $literRatio = 1000;
    $gallonRatio = 3785.41;
    $quartRatio = 946.353;
    $pintRatio = 473.176;
    $cupRatio = 236.588;
    $fluidOZRatio = 29.5735;
    $tbspRatio = 14.7868;
    $tspRatio = 4.92892;
    switch($begin_unit)
    {
        //since we're converting to milliliters, no math needs to be done if begin unit is mililiters
        case "mL":
            return $qty;
            break;
        //liters to milliliters
        case "L":
            return $qty * $literRatio;
            break;
        //gallons to milliliters
        case "gal":       
            return $qty * $gallonRatio;   
            break;
        //quart to milliliters
        case "qt":
            return $qty * $quartRatio;
            break;
        case "pt":
            return $qty * $pintRatio;
            break;
        //cup to milliliters
        case "cup":   
            return $qty * $cupRatio;      
            break;
        //ounces to milliliters
        case "fl oz": 
            return $qty * $fluidOZRatio;
            break;
        //tablespoon to milliliters
        case "tbsp":      
            return $qty * $tbspRatio;     
            break;
        //teaspoon to milliliters
        case: "tsp":       
            return $qty * $tspRatio;     
            break;
        //invalid entered unit
        default:
            echo "unit not found";    
            return -1;  
    }
}



/**
 * Convert from mL/cc into another unit
 * 
 * This function first converts Milliliters/Cubic Centimeters
 * into the ending unit using division
 * 
 * @author z1868762
 * @param qty - the amount of the beginning unit that needs to be converted
 * @param $end_unit - the symbol unit before converting
 * @return float - the quantity of the end unit after conversion. Returns -1 on error
 * @example - volume_from_cc(56.3, "tsp"); - Converts 56.3 milliliters into teaspoons
 * @example - volume_from_cc(13, "fl oz"); - Converts 13 milliliters into fluid ounces
 * @warning - DO NOT USE "oz" for fluid ounces. Mass also uses ounces as a unit, so I'd like to seperate 
 *            "fl oz" for fluid ounces in volume and "oz" for ounces in mass to avoid confusion
 * @note - extra and not needed for #41
 * 
 */
function volume_from_cc(float $qty, string $end_unit)
{
    $literRatio = 1000;
    $gallonRatio = 3785.41;
    $quartRatio = 946.353;
    $pintRatio = 473.176;
    $cupRatio = 236.588;
    $fluidOZRatio = 29.5735;
    $tbspRatio = 14.7868;
    $tspRatio = 4.92892;
    //converting from mililliters to ending unit with division
    switch($end_unit)
    {
        //if user wants milliliters, no change needed
        case "mL":   
            return $qty;
            break;

        //milliliters to liters
        case "L"        
            return $value/$literRatio;    
            break;

        //milliliters to gallons
        case "gal":      
            return $value/$gallonRatio;     
            break;

        //milliliters to quart 
        case "qt":       
            return $value/$quartRatio;      
            break;

        // milliliters to pint 
        case "pt":       
            return $value/$pintRatio;     
            break;

        // milliliters to cup  
        case "cup":      
            return $value/$cupRatio;        
            break;

        //milliliters to ounces         
        case "fl oz":   
            return $value/$fluidOZRatio;    
            break;

        //milliliters to tablespoon 
        case "tbsp":     
            return $value/$tbspRatio;       
            break;

        //milliliters to teaspoons  
        case "tsp":
            return $value/$tspRatio;        
            break;

        //invalid ending unit
        default: 
            echo "unit not found";   
            return -1;  
    }
}


/**
 * Convert from one unit of mass to another
 * 
 * This function converts the beginning unit of mass into grams
 * by multiplicating the conversion ratio from the beginning unit to grams 
 * 
 * @author z1868762
 * @param qty - the amount of the beginning unit that needs to be converted
 * @param $begin_unit - the symbol unit before converting
 * @return float - the quantity of the end unit after conversion. Returns -1 on error
 * @example - mass_to_g(100,"kg"); - Converts 100 kilograms into grams
 * @example - mass_to_g(15, "oz"); - Converts 15 ounces into grams
 * @warning - It doesn't make sense to use "fl oz" for ounces in mass, but just putting this here
 *            in case: DO NOT USE "fl oz" FOR ounces.
 * @see "Project Issue #41"
 */
function mass_to_g(float $qty, string $begin_unit)
{
    $poundRatio = 453.592;
    $ounceRatio = 28.3495;
    $kilogramRatio = 1000;

    switch($begin_unit)
    {
        //no need to convert if the beginning unit is grams
        case "g":      
            return $qty;  
            break;

    //pounds to grams
        case "lb":     
            return $qty * $poundRatio;     
            break;    
    
    //ounces to grams
        case "oz":     
            return $qty * $ounceRatio;     
            break;

    //kilogram to grams
        case "kg":    
            return $qty * $kilogramRatio;  
            break;

    //unit is not found
        default
            echo "unit not found"; 
            return -1;  
    }
}


/**
 * Convert from grams to another unit of measurement
 * 
 * This function converts the unit of grams into another unit by division
 * 
 * @author z1868762
 * @param qty - the amount of the beginning unit that needs to be converted
 * @param $end_unit - the symbol unit before converting
 * @return float - the quantity of the end unit after conversion. Returns -1 on error
 * @example - mass_from_g(100,"kg"); - Converts 100 kilograms into grams
 * @example - mass_from_g(15, "oz"); - Converts 15 ounces into grams
 * @warning - It doesn't make sense to use "fl oz" for ounces in mass, but just putting this here
 *            in case: DO NOT USE "fl oz" FOR ounces.
 * @note - extra function not needed for #41
 */
function mass_from_g(float $qty, string $end_unit)
{
    $poundRatio = 453.592;
    $ounceRatio = 28.3495;
    $kilogramRatio = 1000;
    //dividing the units into the end unit

    switch($end_unit)
    {
    //no changes needed if already in grams
        case "g":     
            return $qty;   
            break;

    //grams to pounds
        case "lb":       
            return $qty/$poundRatio;   
            break;      

    //grams to ounces
        case "oz":      
            return $qty/$ounceRatio;      
            break;

    //grams to kilograms
        case "kg":     
            return $qty/$kilogramRatio;   

    //ending unit not found
        default
            echo "ending unit not found";    
            return -1;     
    }
}

?>
