# Functions
\! echo "Creating functions..."
DELIMITER $$
# Function: WEIGHT_AT_TIME - get a users weight at any given time in the past
CREATE FUNCTION WEIGHT_AT_TIME(uid INT, time TIMESTAMP)
RETURNS DOUBLE
BEGIN
  DECLARE weight DOUBLE DEFAULT 83.57; # average weight USA
  SELECT weight_kg INTO weight
    FROM weight_log
	WHERE user_id = uid AND date < time
	ORDER BY date DESC
	LIMIT 1;
  RETURN weight;
END$$
\! echo " * WEIGHT_AT_TIME"
DELIMITER ;
# Views
\! echo "Creating views..."
# View: Calories in per day
CREATE VIEW calories_in_per_day AS
SELECT DATE(date) AS date, user_id, SUM(calories_per_serving * servings) AS total_calories_in
FROM food_log
INNER JOIN food ON food.id = food_id
GROUP BY DATE(food_log.date), user_id;
\! echo " * calories_in_per_day"
# View: Current weight
CREATE VIEW current_weight AS
SELECT user_id, weight_kg
FROM weight_log
WHERE date IN (
  SELECT MAX(date) FROM weight_log GROUP BY user_id
);
\! echo " * current_weight"
# View: Calories burned during activity
CREATE VIEW workout_calories_burned AS
SELECT
  id,
  date,
  user_id,
  CAST(ROUND(mets_value * duration_seconds/3600 * WEIGHT_AT_TIME(user_id,date)) AS INT) AS calories_burned
FROM workout_log
INNER JOIN workout_type ON workout_type.id = workout_type_id;
\! echo " * workout_calories_burned"
# View: Calories out per day
CREATE VIEW calories_out_per_day AS
SELECT DATE(date) AS date, user_id, CAST(ROUND(SUM(mets_value * duration_seconds/3600 * WEIGHT_AT_TIME(user_id,date))) AS INT) AS total_calories_out
FROM workout_log
INNER JOIN workout_type ON workout_type.id = workout_type_id
GROUP BY DATE(workout_log.date), user_id;
\! echo " * calories_out_per_day"
# View: Net calories per day
CREATE VIEW net_calories_per_day AS
SELECT `date`,user_id,IFNULL(total_calories_in,0) AS total_calories_in,IFNULL(total_calories_out,0) AS total_calories_out,IFNULL(total_calories_in,0)-IFNULL(total_calories_out,0) AS net_calories FROM calories_in_per_day
LEFT JOIN calories_out_per_day USING (`date`, user_id)
UNION
SELECT `date`,user_id,IFNULL(total_calories_in,0) AS total_calories_in,IFNULL(total_calories_out,0) AS total_calories_out,IFNULL(total_calories_in,0)-IFNULL(total_calories_out,0) AS net_calories FROM calories_in_per_day
RIGHT JOIN calories_out_per_day USING (`date`, user_id)
ORDER BY `date`;
\! echo " * net_calories_per_day"
# View: Macro totals monthly
CREATE VIEW macro_totals_monthly AS
SELECT user_id,nutrient_id,sum(servings*amount) AS macro_total_g
FROM food_log
INNER JOIN macronutrient_content USING (food_id)
WHERE nutrient_id IN(1,4,6,7)
AND DATE(date) BETWEEN DATE_SUB(NOW(),INTERVAL 31 DAY) AND NOW()
GROUP BY user_id,nutrient_id;
\! echo " * macro_totals_monthly"
# View: Macro totals weekly
CREATE VIEW macro_totals_weekly AS
SELECT user_id,nutrient_id,sum(servings*amount) AS macro_total_g
FROM food_log
INNER JOIN macronutrient_content USING (food_id)
WHERE nutrient_id IN(1,4,6,7)
AND DATE(date) BETWEEN DATE_SUB(NOW(),INTERVAL 7 DAY) AND NOW()
GROUP BY user_id,nutrient_id;
\! echo " * macro_totals_weekly"
# View: Macro totals today
CREATE VIEW macro_totals_today AS
SELECT user_id,nutrient_id,sum(servings*amount) AS macro_total_g
FROM food_log
INNER JOIN macronutrient_content USING (food_id)
WHERE nutrient_id IN(1,4,6,7)
AND DATE(date) = CURDATE()
GROUP BY user_id,nutrient_id;
\! echo " * macro_totals_today"
# View: Food Totals
CREATE VIEW total_food_logs AS
SELECT user_id,food_id,nutrient_id,servings,date FROM food_log
INNER JOIN macronutrient_content USING (food_id)
GROUP BY user_id,food_id ORDER BY date ASC;
\! echo " * total_food_logs"
# View: Macronutrients
CREATE VIEW macronutrients AS
SELECT * FROM nutrient WHERE id IN (1,4,6,7);
\! echo " * macronutrients"
# View: Micronutrients
CREATE VIEW micronutrients AS
SELECT * FROM nutrient WHERE rdv_unit != 'g';
\! echo " * micronutrients"
# View: Micro totals monthly
CREATE VIEW micro_totals_monthly AS
SELECT user_id,nutrient_id,sum(servings*percent_dv) AS micro_total_percent
FROM food_log
INNER JOIN micronutrient_content USING (food_id)
WHERE nutrient_id IN(SELECT nutrient_id FROM micronutrients)
AND date BETWEEN DATE_SUB(NOW(),INTERVAL 31 DAY) AND NOW()
GROUP BY user_id,nutrient_id;
\! echo " * micro_totals_monthly"
# View: Micro totals weekly
CREATE VIEW micro_totals_weekly AS
SELECT user_id,nutrient_id,sum(servings*percent_dv) AS micro_total_percent
FROM food_log
INNER JOIN micronutrient_content USING (food_id)
WHERE nutrient_id IN(SELECT nutrient_id FROM micronutrients)
AND date BETWEEN DATE_SUB(NOW(),INTERVAL 7 DAY) AND NOW()
GROUP BY user_id,nutrient_id;
\! echo " * micro_totals_weekly"
# View: Micro totals today
CREATE VIEW micro_totals_today AS
SELECT user_id,nutrient_id,sum(servings*percent_dv) AS micro_total_percent
FROM food_log
INNER JOIN micronutrient_content USING (food_id)
WHERE nutrient_id IN(SELECT nutrient_id FROM micronutrients)
AND DATE(date) = CURDATE()
GROUP BY user_id,nutrient_id;
\! echo " * micro_totals_today"