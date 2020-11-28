# Cleanup:
\! echo "Dropping tables and views if they exist..."
DROP VIEW IF EXISTS net_calories_per_day, calories_in_per_day, calories_out_per_day, current_weight, workout_calories_burned;
DROP FUNCTION IF EXISTS WEIGHT_AT_TIME;
DROP TABLE IF EXISTS food_log,workout_log,weight_log,macronutrient_content,micronutrient_content,food,nutrient,workout_type,user;
# Entities: 
\! echo "Creating entity tables:"
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(191) UNIQUE NOT NULL, # limit on unique columns is floor(767/4)=191
    password VARCHAR(255) NOT NULL, # See also: https://www.php.net/manual/en/function.password-hash.php
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL
);
\! echo " * user"
CREATE TABLE workout_type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mets_code INT UNIQUE NULL,
    mets_value DECIMAL(4,2) NOT NULL,
    category VARCHAR(191) NOT NULL,
    activity VARCHAR(191) NOT NULL,
    intensity VARCHAR(191) NOT NULL,
    description VARCHAR(255) NOT NULL,
    UNIQUE KEY (category,activity,intensity)
);
\! echo " * workout_type"
CREATE TABLE nutrient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) UNIQUE NOT NULL, # limit on unique columns is floor(767/4)=191
    rdv_amount DOUBLE NOT NULL, # https://www.fda.gov/media/99069/download
    rdv_unit ENUM('g', 'mg', 'mcg') DEFAULT 'mg' NOT NULL
);
\! echo " * nutrient"
CREATE TABLE food ( # can store both food and beverages
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('solid', 'liquid') DEFAULT 'solid' NOT NULL,
    name VARCHAR(191) UNIQUE NOT NULL, # limit on unique columns is floor(767/4)=191
    calories_per_serving INT NOT NULL,
    serving_size_grams INT NOT NULL, # In nutrition facts, serving size are mass, rounded to gram, eg the "26" in "26g".
    serving_size_cc INT, # Some servinc size are optionally measured also in volume, eg cups or ml. These should be converted to the metric volume cc.
    serving_size_friendly VARCHAR(255) # eg "half cup", "one egg", "2 cookies", "sleeve of oreos"
);
\! echo " * foot"
# Relationships:
\! echo "Creating relationship tables:"
CREATE TABLE macronutrient_content (
    food_id INT,
    nutrient_id INT,
    amount DOUBLE NOT NULL, # the amount as measured in nutrient.rdv_unit (eg grams, miligrams, micrograms)
    PRIMARY KEY (food_id, nutrient_id),
    FOREIGN KEY (food_id) REFERENCES food(id),
    FOREIGN KEY (nutrient_id) REFERENCES nutrient(id)
);
\! echo " * macronutrient_content"
CREATE TABLE micronutrient_content (
    food_id INT,
    nutrient_id INT,
    percent_dv DOUBLE NOT NULL, # the amount as a percent daily value
    PRIMARY KEY (food_id, nutrient_id),
    FOREIGN KEY (food_id) REFERENCES food(id),
    FOREIGN KEY (nutrient_id) REFERENCES nutrient(id)
);
\! echo " * micronutrient_content"
CREATE TABLE weight_log (
    date TIMESTAMP,
    user_id INT,
    weight_kg DOUBLE NOT NULL,
    PRIMARY KEY (date, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);
\! echo " * weight_log "
CREATE TABLE workout_log (
    date TIMESTAMP,
    user_id INT,
    workout_type_id INT,
    duration_seconds INT NOT NULL,
    PRIMARY KEY (date, user_id, workout_type_id),
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (workout_type_id) REFERENCES workout_type(id)
);
\! echo " * workout_log"
CREATE TABLE food_log (
    date TIMESTAMP,
    user_id INT,
    food_id INT,
    servings DOUBLE NOT NULL, # measured by food.serving_size_grams
    PRIMARY KEY (date, user_id, food_id),
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (food_id) REFERENCES food(id)
);
\! echo " * food_log"
