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
        if(!is_null($name) && !empty($name) && isset($rdv_amount) && (!is_null($rdv_amount) || floatval($rdv_amount) != 0) && !is_null($rdv_unit) && !empty($rdv_unit)){
            $pdo = $this -> query($sql, [$name, $rdv_amount, $rdv_unit]);
            return $this->pdo->lastInsertId();
        }
        else if(is_null($name) || empty($name)){
            return -1; // no name
        }
        else if(is_null($rdv_unit) || empty($rdv_unit)) {
            return -3; // no rdv unit 
        }
        else{
            return -4; //some or all values are null or empty
        }
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

        if($food_id > 0) {
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
     * Get a user's info from the database
     * 
     * @author @zgjs
     * @param id The user's ID
     * @return array
     * @example get_user(1); // returns ['username' => 'alice', 'first_name' => 'Alice', 'last_name' => 'Anderson']
     */
    function get_user(int $id) {
        return $this->query('SELECT username, first_name, last_name FROM user WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update a user in the database
     * 
     * @author @zgjs
     * @param id The user's ID
     * @param username The user's login name
     * @param password The user's login password
     * @param first_name The user's given name
     * @param last_name The user's surname
     * @return bool True on success
     * @example update_user(1, "alice", "plain password", "Alice", "Anderson")
     * @see "Project issue #54"
     */
    function update_user(int $id, ?string $username = null, ?string $password = null, ?string $first_name = null, ?string $last_name = null): ?bool {
        if(is_null($username) && is_null($password) && is_null($first_name) && is_null($last_name))
            return false;
        $sql_ = [];
        $params = [];
        $user = $this->get_user($id);
        if(!is_null($username) && strlen($username) >= 3 && $username != $user['username'] && $this->get_user_id($username) == 0) {
            $sql_[] = 'username = :username';
            $params['username'] = $username;
        }
        if(!is_null($password) && strlen($password) >= 8) {
            $sql_[] = 'password = :password';
            $params['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        if(!is_null($first_name) && strlen($first_name) >= 2) {
            $sql_[] = 'first_name = :first_name';
            $params['first_name'] = $first_name;
        }
        if(!is_null($last_name) && strlen($last_name) >= 2) {
            $sql_[] = 'last_name = :last_name';
            $params['last_name'] = $last_name;
        }
        if(empty($params))
            return false;
        $sql = 'UPDATE user SET ' . implode(', ', $sql_) . ' WHERE id = :id';
        $params['id'] = $id;
        return (bool) $this->query($sql, $params)->rowCount();
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
        // Don't allow empty strings for these
        if(empty($mets_value) || empty($category) || empty($activity) || empty($intensity))
            return -1;
        // Allow empty description
        if(empty($description)) {
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
     * Get food
     * 
     * Get info about an food by ID
     * @param id The ID for the food to get
     * @return string[]
     * @example get_food(1);
     */
    function get_food(int $id) {
        return $this->query("SELECT * FROM food WHERE id = ?", [$id])->fetch(PDO::FETCH_ASSOC);
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
            $names[$row['id']] = $row['activity'];
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
     * @return int
     * @example log_food(1, [1], [1])
     * @example log_food(1, [1,2,3], [1,2,1])
     * @see "Project issue #27"
     */
    function log_food(int $user_id, array $foods, array $servings, $date = null): int {
        $last_result;
        if(is_null($date) || empty($date)){
            $sql = "INSERT INTO food_log(`date`,`user_id`,`food_id`,`servings`) VALUES (NOW(),?,?,?)";
            $stmt = $this->pdo->prepare($sql);
            // execute until you have reached the end of the inputs
            for($i = 0; $i < count($foods); $i++) {
                //execute statement
                $last_result = $stmt->execute([$user_id, $foods[$i], $servings[$i]]);
            }
            if($last_result) {
                return time();
            }
        } else {
            $sql = "INSERT INTO food_log(`date`,`user_id`,`food_id`,`servings`) VALUES (?,?,?,?)";
            $stmt = $this->pdo->prepare($sql);
            // execute until you have reached the end of the inputs
            for($i = 0; $i < count($foods); $i++) {
                //execute statement
                $last_result = $stmt->execute([$date, $user_id, $foods[$i], $servings[$i]]);
            }
            if($last_result) {
                return (int) $this->query("SELECT UNIX_TIMESTAMP(?)", [$date])->fetchColumn();
            }
        }
        return -1;
    }

    /**
     * Add a new row to the weight_log table
     * 
     * @author @z1868762 @HR0102 @zgjs
     * @param user_id The user's ID
     * @param weight_kg The user's current weight in kg
     * @param int|string date either the unix timestamp, a string in the format YYYY-MM-DD hh:mm:ss, or null to use the current time
     * @return void
     * @example log_weight(1, 100.0)
     * @see "Project issue #27"
     */
    function log_weight(int $user_id, float $weight_kg, $date = null) 
    {
        if(is_null($date)) {
            $sql = "INSERT INTO weight_log(`date`, `user_id`, `weight_kg`) VALUES (NOW(), ?, ?)";
            $this->query($sql, [$user_id, $weight_kg]);
        } elseif (is_int($date)) {
            $sql = "INSERT INTO weight_log(`date`, `user_id`, `weight_kg`) VALUES (FROM_UNIXTIME(?), ?, ?)";
            $this->query($sql, [$date, $user_id, $weight_kg]);
        } elseif (is_string($date) && !empty($date)) {
            $sql = "INSERT INTO weight_log(`date`, `user_id`, `weight_kg`) VALUES (?, ?, ?)";
            $this->query($sql, [$date, $user_id, $weight_kg]);
        }
        return;
    }
    
    /**
     * Add a workout to the workout_log table
     * 
     * @author @z1868762 @HR0102 @zgjs
     * @param user_id The user's ID
     * @param workout_type_id The id of the workout_type
     * @param duration_seconds The duration of the workout
     * @param int|string date either the unix timestamp, a string in the format YYYY-MM-DD hh:mm:ss, or null to use the current time
     * @return int
     * @example log_workout(1,1,1)
     * @see "Project issue #27"
     */
    function log_workout(int $user_id, int $workout_type_id, int $duration_seconds, $date = null): int {
        if(is_null($date)) {
            $sql = "INSERT INTO workout_log(`date`, `user_id`, `workout_type_id`,`duration_seconds`) VALUES (NOW(), ?, ?, ?)";
            $stmt = $this->query($sql, [$user_id, $workout_type_id, $duration_secs]);
        } elseif (is_int($date)) {
            $sql = "INSERT INTO workout_log(`date`, `user_id`, `workout_type_id`,`duration_seconds`) VALUES (FROM_UNIXTIME(?), ?, ?, ?)";
            $stmt = $this->query($sql, [$date, $user_id, $workout_type_id, $duration_secs]);
        } elseif (is_string($date) && !empty($date)) {
            $sql = "INSERT INTO workout_log(`date`, `user_id`, `workout_type_id`,`duration_seconds`) VALUES (?, ?, ?, ?)";
            $stmt = $this->query($sql, [$date, $user_id, $workout_type_id, $duration_seconds]);
        }
        if($stmt->rowCount() > 0) {
            return (int) $this->query("SELECT UNIX_TIMESTAMP(?)", [$date])->fetchColumn();
        }
        var_dump($this->pdo->errorInfo());
        return -1;
    }

    /**

     * To get the macro percentages from the macro totals view
     * 
     * @author @z1868762 @HR0102 @zgjs
     * @param user_id - The user's ID 
     * @param period - Today, Weekly, Monthly
     * @return array 
     * @example get_macro_calories(5, "today");
     * @example get_macro_calories(3, "weekly");
     * @example get_macro_calories(2, "monthly");
     * @see "Project Issue #29"
     * @see https://www.nal.usda.gov/fnic/how-many-calories-are-one-gram-fat-carbohydrate-or-protein
     */
    function get_macro_calories(int $user_id, string $period) : array
    {
        $views = ['today' => 'macro_totals_today', 
                  'monthly' => 'macro_totals_monthly', 
                  'weekly' => 'macro_totals_weekly'];
        $macro = ['fat' => '1',
                  'carbs' => '4',
                  'fiber' => '6',
                  'protein' => '7'];
        $sql = 'SELECT nutrient_id, macro_total_g FROM ' . $views[$period] . ' WHERE user_id = ?';
        $rows = $this->query($sql, [$user_id])->fetchAll(PDO::FETCH_ASSOC);
        $macro_calories = [["Macronutrient","Calories"]];
        foreach($rows as $row)
        {
            switch($row['nutrient_id'])
            {
                case $macro['fat']:
                    $macro_calories[] = ['Fat',$row['macro_total_g'] * 9];
                    break;
                case $macro['carbs']:
                    $macro_calories[] = ['Carbohydrates',$row['macro_total_g'] * 4];
                    break;
                case $macro['fiber']:
                    $macro_calories[] = ['Dietary fiber',$row['macro_total_g'] * 2];
                    break;
                case $macro['protein']:
                    $macro_calories[] = ['Protein',$row['macro_total_g'] * 4];
                    break;
            }
        }
        return $macro_calories;
    }

    /**
     * Fetch all macronutrients from the database
     * 
     * @return array an array of macronutrient IDs and Names
     * @example $array_result = get_macronutrients()
     * @see "Project issue #2336"
     */
    function get_macronutrients() :array {
        $sql = "SELECT id,name FROM nutrient WHERE id IN(1,4,6,7)";
        $stmt = $this->query($sql);
        $array_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $array_results;
    }

    /**
     * Fetch all micronutrients from the database
     * 
     * @return array an array of micronutrient IDs and Names
     * @example $array_result = get_micronutrients()
     * @see "Project issue #2336"
     */
    function get_micronutrients() :array {
        $sql = "SELECT id,name FROM nutrient WHERE id NOT IN(1,4,6,7)";
        $stmt = $this->query($sql);
        $array_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $array_results;
    }
    
    /**
     * Fetch macronutrients relevant to a specific food from the database
     * 
     * @param int food_id The ID of the food
     * @return array an array of macronutrient IDs and Names
     * @example $macros = get_food_macronutrients(1);
     */
    function get_food_macronutrients(int $food_id) :array {
        $sql = <<<SQL
        SELECT nutrient.name, amount, rdv_unit, ROUND(amount/rdv_amount*100) AS percent_dv
        FROM macronutrient_content
        INNER JOIN food ON food.id = food_id
        INNER JOIN nutrient ON nutrient.id = nutrient_id
        WHERE food.id = ?;
        SQL;
        $stmt = $this->query($sql, [$food_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $macros = [];
        foreach($rows as $row) {
            $macros[$row['name']] = [
                'amount' => $row['amount'],
                'unit' => $row['rdv_unit'],
                'percent_dv' => $row['percent_dv']
            ];
        }
        return $macros;
    }

    /**
     * Fetch all micronutrients relevant to a specific food from the database
     * 
     * @param int food_id The ID of the food
     * @return array an array of micronutrient IDs and Names
     * @example $macros = get_food_micronutrients(1);
     */
    function get_food_micronutrients(int $food_id) :array {
        $sql = <<<SQL
        SELECT nutrient.name, (rdv_amount*percent_dv/100) AS amount, rdv_unit, percent_dv
        FROM micronutrient_content
        INNER JOIN food ON food.id = food_id
        INNER JOIN nutrient ON nutrient.id = nutrient_id
        WHERE food.id = ?;
        SQL;
        $stmt = $this->query($sql, [$food_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $micros = [];
        foreach($rows as $row) {
            $micros[$row['name']] = [
                'amount' => $row['amount'],
                'unit' => $row['rdv_unit'],
                'percent_dv' => $row['percent_dv']
            ];
        }
        return $micros;
    }

    /**
     * Get a list of meals
     * 
     * The return value is in this format:
     * 
     * [
     *     {
     *         "datetime": "2021-01-08 19:04:00",
     *         "unixtime": "1610154240",
     *         "foods": [
     *             {
     *                 "name": "Chik-Fil-A Chocolate Milkshake (small)",
     *                 "calories": "1220"
     *             }
     *         ]
     *     },
     *     {
     *         "datetime": "2020-11-12 19:26:35",
     *         "unixtime": "1605230795",
     *         "foods": [
     *             {
     *                 "name": "large egg",
     *                 "calories": "249"
     *             },
     *             {
     *                 "name": "swiss cheese",
     *                 "calories": "72"
     *             },
     *             {
     *                 "name": "Lette Caramel Macaron",
     *                 "calories": "90"
     *             }
     *         ]
     *     },
     * 
     * @param int user_id The user ID
     * @param int timestamp If you want to get a single meal
     * @return array
     * @example get_meals(1)
     */
    function get_meals(int $user_id, ?int $timestamp = null): array {
        $times = [];
        if(is_null($timestamp)) {
            $times = $this->query("SELECT date AS datetime, UNIX_TIMESTAMP(date) AS unixtime FROM food_log WHERE user_id = ? GROUP BY date ORDER BY date DESC", [$user_id])->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $times[] = ['datetime' => $this->query("SELECT FROM_UNIXTIME(?)", [$timestamp])->fetchColumn(), 'unixtime' => $timestamp];
        }
        $sql = <<<SQL
        SELECT food.name, calories_per_serving*servings AS calories
        FROM food_log
        INNER JOIN food ON food.id = food_id
        WHERE user_id = ? AND `date` = ?
        SQL;
        $sumsql = <<<SQL
        SELECT 'Total' as name, SUM(calories_per_serving*servings) AS calories
        FROM food_log
        INNER JOIN food ON food.id = food_id
        WHERE user_id = ? AND `date` = ?
        GROUP BY user_id,date
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $sumstmt = $this->pdo->prepare($sumsql);
        $meals = [];
        foreach($times as $time) {
            $stmt->execute([$user_id, $time['datetime']]);
            $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sumstmt->execute([$user_id, $time['datetime']]);
            $foods[] = $sumstmt->fetch(PDO::FETCH_ASSOC);
            $meals[] = [
                'datetime' => $time['datetime'],
                'unixtime' => (int)$time['unixtime'],
                'foods' => $foods
            ];
        }
        //echo "</div></div></div><br><h1>JSON</h1><pre>" . json_encode($meals, JSON_PRETTY_PRINT) . "</pre><div>";
        return $meals;
    }

    /**
     * Get food totals
     */
    function get_food_totals(int $user_id, string $start_date, string $stop_date) {
        $sql = <<<SQL
        SELECT
            f.name AS 'food',
            COUNT(*) AS 'meals',
            SUM(t.servings) AS 'servings',
            f.calories_per_serving AS 'calories_per_serving',
            SUM(t.servings) * f.calories_per_serving AS 'calories',
            CONCAT(SUM(CASE WHEN n.id = 1 THEN mi.amount * t.servings ELSE '0' END), ' g') as 'fat',
            CONCAT(SUM(CASE WHEN n.id = 4 THEN mi.amount * t.servings ELSE '0' END), ' g') as 'carbs',
            CONCAT(SUM(CASE WHEN n.id = 7 THEN mi.amount * t.servings ELSE '0' END), ' g') as 'protein',
            CONCAT(SUM(CASE WHEN n.id = 6 THEN mi.amount * t.servings ELSE '0' END), ' g') as 'fiber'
        FROM food_log t 
        INNER JOIN macronutrient_content mi ON mi.food_id = t.food_id
        INNER JOIN user u ON u.id = t.user_id 
        INNER JOIN food f ON f.id = t.food_id 
        INNER JOIN nutrient n ON n.id = mi.nutrient_id
        WHERE user_id = :user_id AND DATE(t.date) BETWEEN :start_date AND :stop_date
        GROUP BY t.user_id,t.food_id;
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':start_date' => $start_date,
            ':stop_date' => $stop_date
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * Get a list of workouts
     * 
     * @param int user_id The user ID
     * @param int timestamp If you want to get a single workout
     * @return array
     * @example get_workouts(1)
     */
    function get_workouts(int $user_id, ?int $timestamp = null): array {
        $sql = <<<SQL
        SELECT date, duration_seconds/60 AS duration_minutes, category, activity, intensity, WEIGHT_AT_TIME(user_id, date) * mets_value * duration_seconds/3600 AS calories_burned
        FROM workout_log
        INNER JOIN workout_type ON workout_type.id = workout_type_id
        WHERE user_id = ?
        SQL;
        if(is_null($timestamp)) {
            return $this->query($sql, [$user_id])->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $this->query("$sql AND `date` = FROM_UNIXTIME(?)", [$user_id, $timestamp])->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * To get latest workout from the workout_log tables
     * 
     * @author @HR0102
     * @param user_id -The user's id
     * @return array
     * @see "Project Issue #31
     */
    function get_latest_workout(int $user_id){
        $sql = "SELECT date as Date, CONCAT(ROUND(duration_seconds/60), ' minutes') AS Duration, category as 'Activity Category', activity as 'Physical Activity', intensity as 'Workout Intensity', ROUND(mets_value * duration_seconds/3600 * WEIGHT_AT_TIME($user_id,date)) AS 'Estimated Calories Burned'      FROM workout_log
        JOIN user ON user.id = user_id 
        JOIN workout_type ON workout_type.id = workout_type_id 
        WHERE user.id = ?
        ORDER BY date DESC LIMIT 1";

        $rows=$this->query($sql,[$user_id])->fetchAll(PDO::FETCH_ASSOC);
        return $rows;

    }
    /**
     * To get latest meal from food_log tables
     * 
     * @author @HR0102
     * @param user_id - the user's id 
     * @return array
     * @see Project Issue #31
     */
    function get_latest_meal(int $user_id){
        $sql = "SELECT date, name, serving_size_friendly
        FROM food_log
        JOIN food on food.id = food_id
        WHERE date IN (SELECT MAX(date)
        FROM food_log WHERE user_id = ?)";

        $rows=$this->query($sql,[$user_id])->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
}

?>
