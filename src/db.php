<?php
/**
 * @file db.php
 * Database functions
 * 
 * This file contains functions useful for connecting to and running queries against the database.
 * It includes the DB class definition which acts as a wrapper for an instance of the PDO class.
 */

/**
 * Database wrapper class
 */
class DB {
    /** @var $pdo is an instance of the PHP Data Objects class */
    protected $pdo;

    /**
     * DB class constructor loads secrets and connects to the database
     */
    public function __construct(){
        if (is_readable('../config/config.php')) {
            include_once '../config/config.php';
        } else {
            include_once '../templates/setup.php';
            include_once '../templates/footer.php';
            die();
        }
        if(!(isset($username))) {
            include_once '../templates/setup.php';
            include_once '../templates/footer.php';
            die();
        }
        if($username == "") {
            die("<p>Database username is blank!</p>\n");
        }
        try {
            $dsn = "mysql:host=$servername;dbname=$dbname;";
            $this->pdo = new PDO($dsn,$username,$password);
        } catch (Exception $e) {
            die("<p>Could not connect to database: {$e->getMessage()}</p>\n");
        }
    }

    /**
     * Query the database and return the result
     * 
     * This function takes a SQL query string and returns the result as a PDOStatement object.
     * It supports supplying optional $args into a prepared statement
     * 
     * @author @zgjs
     * @param string $sql A string containing the SQL query. Placeholders can be used if args is also specified
     * @param string[]|null $args An array of arguments to use for PDOStatement::execute(); can be either sequential array or associative array
     * @return PDOStatement
     * @example get_query_result($pdo, 'SELECT * FROM table1;');
     * @example get_query_result($pdo, 'SELECT * FROM table1 WHERE id = ? AND name = ?;', [5,'smith']);
     * @example get_query_result($pdo, 'SELECT * FROM table1 WHERE id = :id AND name = :name;', ['id' => 5, 'name' => 'smith']);
     */
    function query(string $sql, ?array $args = NULL): PDOStatement {
        if(is_null($args) || empty($args)) {
            // If the $args option is null or empty, run a simple PDOStatement::query()
            return $this->pdo->query($sql);
        } else {
            // Otherwise, use a prepared statement
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        }
    }

    /**
     * Get name array
     * 
     * Get a filtered list of the names in any table foods as array (int id => string name)
     * 
     * @author @zgjs
     * @param search An optional string to filter results by
     * @return string[]
     * @example get_names("foods")
     * @example get_names("foods", "milkshake")
     */
    function get_names(string $table, ?string $search = null) {
        $sql = "SELECT id,name FROM $table";
        if(!is_null($search)) {
            $sql .= " WHERE name LIKE ?";
        }
        $names = array();
        $rows = $this->query($sql, ["%$search%"])->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $row) {
            $names[$row['id']] = $row['name'];
        }
        return $names;
    }

    /**
     * Get user id
     * 
     * Get a user_id from table user
     * 
     * @author @john
     * @param username
     * @return int user id
     * @example get_user_id("eve")
     */
    function get_user_id(string $username) :int {
        $sql = "SELECT id FROM user WHERE username = ?";
        $id = $this->query($sql,[$username])->fetchColumn();
        return (int)$id;
    }

    /**
     * Check password
     * 
     * Connect to the database and run password_verify() against the stored hash
     * 
     * @param username the username to look up
     * @param password the plain text password to check
     * @return bool
     * @example check_password("username", "clear password")
     * @see "Project issue #23"
     */
    function check_password(string $username, string $password): bool {
        $sql = "SELECT password FROM user WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username',$username);
        $stmt->execute();
        /** @var password_hash is the encrypted password from the database */
        $password_hash = $stmt->fetchColumn();
        return password_verify($password, $password_hash);
    }

    /**
     * Add a new nutrient to the database
     * 
     * @param name The name of the nutrient
     * @param rdv_amount The recommended daily intake amount
     * @param rdv_unit The unit for the recommended daily intake amount
     * @return int the ID of the new nutrient
     * @example add_nutrient("caffeine", 0.0, "mg")
     * @see "Project issue #25"
     */
    function add_nutrient(string $name, float $rdv_amount, string $rdv_unit): int {
        // Prepare statement
        $sql = "INSERT INTO nutrient (name, rdv_amount, rdv_unit) VALUES (?, ?, ?)";
        // Execute statement;
        $pdo = $this -> query($sql, [$name, $rdv_amount, $rdv_unit]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Add a new food to the database
     * 
     * @param name The name of the food
     * @param type The type of the food (solid or liquid)
     * @param serving_size_friendly A friendly description of the serving size, eg "one cup"
     * @param calories_per_serving The number of calories contained in one serving of this food
     * @param serving_size_grams The mass of one serving in grams
     * @param serving_size_cc The volume of one serving in cubic centimeters or milliliters if known. Otherwise null.
     * @param macro_id An optional array of macronutrient ids
     * @param macro_g An optional array of macronutrient grams
     * @param macro_id An optional array of micronutrient ids
     * @param macro_dv An optional array of mincronutrient daily values
     * @return int the ID of the new food
     * @example add_food("tuna sandwich", "solid", "one sandwich", 250, null, [2 => 3.5, 5 => 1.0], [20 => 5, 12 => 15])
     * @see "Project issue #26"
     */
    function add_food(
        string $name,
        string $type,
        string $serving_size_friendly,
        int $calories_per_serving,
        int $serving_size_grams,
        ?int $serving_size_cc = null,
        ?array $macro_id = null,
        ?array $macro_g = null,
        ?array $micro_id = null,
        ?array $micro_dv = null
    ) :int
    {
        // check if food already exists
        $sql = "SELECT count(*) FROM food WHERE name = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name]);
        $count = $stmt->fetchColumn();
        
        if($count != 0) {
            // food exists
            return -2;
        }
        // Prepare food insert statement
        if(!is_null($serving_size_cc))
            $sql = "INSERT INTO food(name,type,serving_size_friendly,calories_per_serving,serving_size_grams,serving_size_cc) VALUES (:name,:type,:serving_size_friendly,:calories_per_serving,:serving_size_grams,:serving_size_cc)";
        else
            $sql = "INSERT INTO food(name,type,serving_size_friendly,calories_per_serving,serving_size_grams) VALUES (:name,:type,:serving_size_friendly,:calories_per_serving,:serving_size_grams)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':serving_size_friendly', $serving_size_friendly);
        $stmt->bindParam(':calories_per_serving',$calories_per_serving);
        $stmt->bindParam(':serving_size_grams', $serving_size_grams);
        if(!is_null($serving_size_cc))
            $stmt->bindParam(':serving_size_cc', $serving_size_cc);
        // Execute food insert statement
        $stmt->execute();
        $food_id = $this->pdo->lastInsertId();

        // Prepare macronutrient insert statement (if provided)
        if( (!is_null($macro_id) || !empty($macro_id)) && (!is_null($macro_g) || !empty($macro_g)) ) {
            $sql = "INSERT INTO macronutrient_content(food_id,nutrient_id,amount) VALUES (?,?,?)";
            $stmt = $this->pdo->prepare($sql);   
            // for each macronutrient execute prepared statement
            for( $i = 0; $i < count($macro_id); $i++) {
                $stmt->execute([$food_id, $macro_id[$i], $macro_g[$i]]);
            }
        }
        // Prepare micronutrient insert statement (if provided)
        if( (!is_null($micro_id) || !empty($micro_id)) && (!is_null($micro_dv) || !empty($micro_dv)) ) {
            $sql = "INSERT INTO micronutrient_content(food_id,nutrient_id,percent_dv) VALUES (?,?,?)";
            $stmt = $this->pdo->prepare($sql);   
            // for each micronutrient execute prepared statement
            for( $i = 0; $i < count($micro_id); $i++) {
                $stmt->execute([$food_id, $micro_id[$i], $micro_dv[$i]]);
            }
        }
        return $food_id;
    }

    /**
     * Add a new user to the database
     * 
     * @param username The user's login name
     * @param password The user's login password
     * @param first_name The user's given name
     * @param last_name The user's surname
     * @return int the ID of the new user
     * @example add_user("alice", "plain password", "Alice", "Anderson")
     * @see "Project issue #23"
     */
    function add_user(string $username, string $password, string $first_name, string $last_name): int {
        // validate parameters
        if(strlen($username) < 3) {
            return -1;
        }
        if(strlen($password) < 8) {
            return -1;
        }
        if(strlen($first_name) < 2 || strlen($last_name) < 2) {
            return -1;
        }
        // check input against database contents
        $sql = "SELECT count(*) FROM user WHERE username = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();
        
        if($count != 0) {
            // username exists
            return -2;
        }

        // Prepare insert statement
        $sql = "INSERT INTO user(username,password,first_name,last_name) VALUES(:username,:password,:first_name,:last_name)";
        $stmt = $this->pdo->prepare($sql);

        $encrypted_password = password_hash($password,PASSWORD_DEFAULT);
        // Bind parameters
        $stmt->bindParam(':username',$username);
        $stmt->bindParam(':password',$encrypted_password);
        $stmt->bindParam(':first_name',$first_name);
        $stmt->bindParam(':last_name',$last_name);
        // Execute insert statement
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * Add a new workout_type to the database
     * 
     * @param mets_value the coefficient to calculate calories
     * @param category the category of the activity
     * @param activity the name of the activity
     * @param intensity the intensity of the activity
     * @param description the description of the activity
     * @return int the ID of the new workout_type
     * @example add_workout_type(14, 'bicycling', 'mountain bicycling, uphill', 'vigorous')
     * @example add_workout_type(14, 'bicycling', 'mountain bicycling, uphill', 'vigorous', 'Riding a mountain bike uphill (vigorous intensity)') 
     * @see "Project issue #25"
     */
    function add_workout_type(
        float $mets_value,
        string $category,
        string $activity,
        string $intensity,
        ?string $description = null
    ): int {
        if(is_null($description)) {
            $description = "$activity, $intensity";
        }
        // Prepare insert statement
        $sql = "INSERT INTO workout_type (mets_value, category, activity, intensity, description) VALUES (?, ?, ?, ?, ?)";
        
        // Execute statement
        $pdo = $this -> query($sql, [$mets_value, $category, $activity, $intensity, $description]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Get a user's current weight in kg
     * 
     * @param user_id The user's ID
     * @return double
     * @example get_current_weight(1)
     * @see "Project issue #24"
     */
    function get_current_weight(int $user_id): double {
        // 
        return (double)"0.0";
    }
    
    /**
     * Get foods
     * 
     * Get a filtered list of all foods as array (int key => string value), eg. [1 => 'large egg', 2 => 'swiss cheese']
     * @param search An optional string to filter results by
     * @return string[]
     * @example get_goods()
     * @example get_foods("milkshake")
     * @see "Project issue #24"
     */
    function get_foods(?string $search = null) {
        return $this->get_names('food', $search);
    }

    /**
     * Get nutrients
     * 
     * Get a filtered list of all nutrients as array (int key => string value), eg. [1 => 'Fat', 2 => 'Carbohydrates']
     * @param search An optional string to filter results by
     * @return string[]
     * @example get_nutrients()
     * @example get_nutrients("vitamin")
     * @see "Project issue #24"
     */
    function get_nutrients(?string $search = null) {
        return $this->get_names('nutrient', $search);
    }

    /**
     * Get workout types
     * 
     * Get a filtered list of all nutrients as array (int key => string value), eg. [1 => 'Fat', 2 => 'Carbohydrates']
     * @param search An optional string to filter results by
     * @return string[]
     * @example get_workout_types()
     * @example get_workout_types("bicycling")
     * @see "Project issue #24"
     */
    function get_workout_types(?string $search = null) {
        if(is_null($search)) {
            $sql = "SELECT id,activity FROM workout_type GROUP BY activity";
        } else {
            $sql = "SELECT id,activity FROM workout_type WHERE activity LIKE ? GROUP BY activity";
        }
        $names = array();
        $rows = $this->query($sql, ["%$search%"])->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $row) {
            $names[$row['id']] = $row['name'];
        }
        return $names;
    }
    
    /**
     * Add a meal to the food_log table
     *
     * The database will use the NOW() function for the time
     * 
     * @author z1868762 HR0102
     * @param user_id The user's ID
     * @param int[] foods An array of food_ids
     * @param double[] servings An array of how many searvings for each food_id
     * @return void
     * @example log_food(1, [1], [1])
     * @example log_food(1, [1,2,3], [1,2,1])
     * @see "Project issue #27"
     */
    function log_food(int $user_id, array $foods, array $servings) 
    {
        $sql = "INSERT INTO food_log(`date`,`user_id`,`food_id`,`servings`) VALUES (NOW(),?,?,?)";
        // Prepare statement
        $stmt = $this->pdo->prepare($sql);
        // foreach food as food_id
        foreach($foods as $key=>$food_id)                                          
        {
            //execute statement
            $stmt ->execute(array($user_id, $foods[$key], $servings[$key]));   
        }
        return;
    }

    /**
     * Add a new row to the weight_log table
     *
     * The database will use the NOW() function for the time
     * 
     * @author z1868762 HR0102
     * @param user_id The user's ID
     * @param weight_kg The user's current weight in kg
     * @return void
     * @example log_weight(1, 100.0)
     * @see "Project issue #27"
     */
    function log_weight(int $user_id, float $weight_kg) 
    {
        $sql = "INSERT INTO weight_log(`date`, `user_id`, `weight_kg`) VALUES (NOW(), ?, ?)";
        $pdo = $this -> query($sql, [$user_id, $weight_kg]);
        return;
    }
    
    /**
     * Add a workout to the workout_log table
     *
     * The database will use the NOW() function for the time
     * 
     * @author z1868762 HR0102
     * @param user_id The user's ID
     * @param workout_type_id The id of the workout_type
     * @param duration_secs The duration of the workout
     * @return void
     * @example log_workout(1,1,1)
     * @see "Project issue #27"
     */
    function log_workout(int $user_id, int $workout_type_id, int $duration_secs) {
        $sql = "INSERT INTO workout_log(`date`, `user_id`, `workout_type_id`,`duration_seconds`) VALUES (NOW(), ?, ?, ?)";
        $pdo = $this -> query($sql, [$user_id, $workout_type_id, $duration_secs]);
        return;
    }
}
?>
