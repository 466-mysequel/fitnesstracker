<?php
/**
 * @file convert.php
 * Convert Class definition
 * 
 * This file contains all the ratios and member functions for converting
 * It includes the convert class definition which stores the ratios and member functions
 **/

 /** 
 * Convert member class
 * 
 * This class contains all the conversion ratios 
 * from the selected unit to grams or cubic centimeters.
 * 
 * Recommended use of member functions examples: 
 *  convert::mass_to_g(45, "oz");
 *  convert::mass_from_g(132, "kg");
 *  convert::volume_to_cc(23, "tbsp");
 *  convert::volume_from_cc(55, "pt");
 * 
 * You can also create a convert class object and call the functions but is not recommended 
 * as the object itself is not very useful once conversions are finished
 * 
 * Not Recommended use of member funtions examples: 
 *  $convertClassName = new convert();
 *      $convertClassName -> mass_to_g(45, "oz");
 *      $convertClassName -> mass_from_g(123, "kg");
 */
class convert
{
    //volume ratios
    private static $literRatio = 1000;
    private static $gallonRatio = 3785.41;
    private static $quartRatio = 946.353;
    private static $pintRatio = 473.176;
    private static $cupRatio = 236.588;
    private static $fluidOZRatio = 29.5735;
    private static $tbspRatio = 14.7868;
    private static $tspRatio = 4.92892;

    //mass ratios
    private static $poundRatio = 453.592;
    private static $ounceRatio = 28.3495;
    private static $kilogramRatio = 1000;
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
     * @example - convert::volume_to_cc(56.3, "tsp"); - Converts 56.3 tablespoons into mL/cc
     * @example - convert::volume_to_cc(13, "fl oz"); - Converts 13 fluid ounces into mL/cc
     * @warning - DO NOT USE "oz" for fluid ounces. Mass also uses ounces as a unit, so I'd like to seperate 
     *            "fl oz" for fluid ounces in volume and "oz" for ounces in mass to avoid confusion
     * @see "Project Issue #41"
     * 
     */
    public static function volume_to_cc(float $qty, string $begin_unit)
    {
        switch($begin_unit)
        {
            //since we're converting to milliliters, no math needs to be done if begin unit is mililiters
            case "mL":
                return $qty;
                break;
            //liters to milliliters
            case "L":
                return $qty * self::$literRatio;
                break;
            //gallons to milliliters
            case "gal":       
                return $qty * self::$gallonRatio;   
                break;
            //quart to milliliters
            case "qt":
                return $qty * self::$quartRatio;
                break;
            case "pt":
                return $qty * self::$pintRatio;
                break;
            //cup to milliliters
            case "cup":   
                return $qty * self::$cupRatio;      
                break;
            //ounces to milliliters
            case "fl oz": 
                return $qty * self::$fluidOZRatio;
                break;
            //tablespoon to milliliters
            case "tbsp":      
                return $qty * self::$tbspRatio;     
                break;
            //teaspoon to milliliters
            case "tsp":       
                return $qty * self::$tspRatio;     
                break;
            //invalid entered unit
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
     * @example - convert::mass_to_g(100,"kg"); - Converts 100 kilograms into grams
     * @example - convert::mass_to_g(15, "oz"); - Converts 15 ounces into grams
     * @warning - It doesn't make sense to use "fl oz" for ounces in mass, but just putting this here
     *            in case: DO NOT USE "fl oz" FOR ounces.
     * @see "Project Issue #41"
     */
    public static function mass_to_g(float $qty, string $begin_unit)
    {
        switch($begin_unit)
        {
            //no need to convert if the beginning unit is grams
            case "g":      
                return $qty;  
                break;

            //pounds to grams
            case "lb":     
                return $qty * self::$poundRatio;     
                break;    
        
            //ounces to grams
            case "oz":     
                return $qty * self::$ounceRatio;     
                break;

            //kilogram to grams
            case "kg":    
                return $qty * self::$kilogramRatio;  
                break;

            //unit is not found
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
     * @example - convert::volume_from_cc(56.3, "tsp"); - Converts 56.3 milliliters into teaspoons
     * @example - convert::volume_from_cc(13, "fl oz"); - Converts 13 milliliters into fluid ounces
     * @warning - DO NOT USE "oz" for fluid ounces. Mass also uses ounces as a unit, so I'd like to seperate 
     *            "fl oz" for fluid ounces in volume and "oz" for ounces in mass to avoid confusion
     * @note - extra and not needed for #41
     */
    public static function volume_from_cc(float $qty, string $end_unit)
    {
        //converting from mililliters to ending unit with division
        switch($end_unit)
        {
            //if user wants milliliters, no change needed
            case "mL":   
                return $qty;
                break;

            //milliliters to liters
            case "L":        
                return $qty/self::$literRatio;    
                break;

            //milliliters to gallons
            case "gal":      
                return $qty/self::$gallonRatio;     
                break;

            //milliliters to quart 
            case "qt":       
                return $qty/self::$quartRatio;      
                break;

            // milliliters to pint 
            case "pt":       
                return $qty/self::$pintRatio;     
                break;

            // milliliters to cup  
            case "cup":      
                return $qty/self::$cupRatio;        
                break;

            //milliliters to ounces         
            case "fl oz":   
                return $qty/self::$fluidOZRatio;    
                break;

            //milliliters to tablespoon 
            case "tbsp":     
                return $qty/self::$tbspRatio;       
                break;

            //milliliters to teaspoons  
            case "tsp":
                return $qty/self::$tspRatio;        
                break;

            //invalid ending unit
            default: 
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
     * @example - convert::mass_from_g(100,"kg"); - Converts 100 kilograms into grams
     * @example - convert::mass_from_g(15, "oz"); - Converts 15 ounces into grams
     * @warning - It doesn't make sense to use "fl oz" for ounces in mass, but just putting this here
     *            in case: DO NOT USE "fl oz" FOR ounces.
     * @note - extra function not needed for #41
     */
    public static function mass_from_g(float $qty, string $end_unit)
    {
        //dividing the units into the end unit

        switch($end_unit)
        {
            //no changes needed if already in grams
            case "g":     
                return $qty;   
                break;

            //grams to pounds
            case "lb":       
                return $qty/self::$poundRatio;   
                break;      

            //grams to ounces
            case "oz":      
                return $qty/self::$ounceRatio;      
                break;

            //grams to kilograms
            case "kg":     
                return $qty/self::$kilogramRatio;   

            //ending unit not found
            default:
                echo "ending unit not found";    
                return -1;     
        }
    }
}
?>