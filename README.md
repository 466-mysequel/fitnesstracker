# CSCI 466 Group Project - Fitness Tracker
## The Goal
You are being employed by a company that offers a fitness tracking service. They are working on a phone app that will allow the user to track what they eat, as well as when/how they wor kout. Another employee will be designing the user interface, but you are responsible for designingthe database. Design an ER diagram to fulfill this goal, making sure to meet all of the requirements. All entities must have an appropriate identifier specified. If a surrogate key is used, explain why a natural key was not appropriate. In the interests of saving space, attributes that are not part of an identifier may be omitted from the diagram, but they should be included and explained in that portion of your submission.

## Requirements
* Every user will have an account, and all of their meals and workouts will be linked to this account.
* To track weight loss, the user will update their weight periodically. This data must be retained.
* The serving size will be some number of units (grams, ounces, Tbsp, cups, lbs, etc.). There will be information stored for conversion between different unit types.
* There will need to be a database of foods/beverages. Each of these will have information on serving size, calories per serving, and grams per serving of each of the macronutrients (protein, fat, carbohydrates).
* It should be possible to store information on the quantities of micronutrients or chemicals (i.e. vitamin D, caffeine) that are present in a given food or beverage in significant amounts. Recommended daily values for any of these should be stored, when applicable.
* Each time the user eats, a record of who ate how many servings of what and when is stored.
* The app needs to allow a user to track their workouts. This includes the type of the workout, its intensity, and its duration.
* When a user tracks their workout, a record is created of who did what type of workout, when, and for how long.

## Necessary Views
The data base needs to be able to store its operational data in such a way that the final app will be able to show the following views (at minimum). You don't need to implement the views, but the necessary data should be modeled.

* A graph will be generated of how many calories a user consumed each day of the week.
* A similar graph will be generated that shows how many calories were burnt each day of the week through workout.
* Show a pie graph of the percentage of the diet made up of each macronutrient during a given time period (day, week, month).
* Track the consumption of a given micronutrient/chemical over a specified period of time. If there is a recommended daily value, show a comparison of their consumption with the recommended amount.
* Allow the user to search through the food database to find common foods, in order to plan their diets. These same foods will be used to track their eating.
* The user will search through a list of workouts to find the one closest to the one they're going to do, in order to track the activity.
* A line graph of user weight over time.

## Additional Requirements
* You must implement a page that facilitates the addition of new foods/drinks into the data base. This should allow any of the relevant information (calories, macros, micronutrients, etc.) to be added.
* You must implement a page that allows the user to update their weight.
* You must implement a page that allows the user to enter the foods/drinks they have consumed, and in what quantities (tracking page). This page must allow the amount consumed to be specified in any relevant type of unit, and the rest of your app should be able to handle those conversions.
* You must implement a page that shows a table that lists all of the food consumed over a selected period of time, along with the calories and macros that are contained in the quantity of food specified. You must implement this page in such a way that clicking on the column headings will make your page sort based on that column. First click should sort in ascending order, a second should sort in descending order, and subsequent clicks just toggle that back and forth.
* You must implement a page that allows the user to enter their workouts.
* You must implement a page showing workouts over a given time period, including estimated calories burned, and any other relevant information, for each workout. Statistics such as total calories and average calories per workout should be displayed on this page as well.

## ER Diagram
![ER Diagram](https://github.com/466-mysequel/fitnesstracker/blob/master/docs/erd.png?raw=true)

[ER Diagram PDF](https://github.com/466-mysequel/fitnesstracker/blob/master/docs/erd.pdf?raw=true)

## ER Diagram Description

### Entities

#### User Accounts
Attribute | Domain | Description
-- | -- | --
id | positive integers | surrogate key
username | any utf8 characters | login name
password | any utf8 characters | encrypted password
first_name | any utf8 characters | given name
last_name | any utf8 characters | surname

#### Workout Types
Attribute | Domain | Description
-- | -- | --
id | positive integers | surrogate key
description | any utf8 characters | workout activity

#### Nutrients
Attribute | Domain | Description
-- | -- | --
id | positive integers | surrogate key
name | any utf8 characters | nutrient name
rdv_amount | positive numbers | recommended daily intake
rdv_unit | 'g', 'mg', 'mcg' | unit of measure

#### Foods & Beverages
Attribute | Domain | Description
-- | -- | --
id | positive integers | surrogate key
type | 'solid', 'liquid' | solid food or beverage
name | any utf8 characters | name of food
calories_per_serving | positive integers | kiloCalories per serving
serving_size_units | positive numbers | serving size in preferred unit
serving_size_label | any utf8 characters | non-metric unit label
serving_size_grams | positive integers | serving size mass (grams)
serving_size_cc | positive integers | serving size volume (cc/mL)

### Relationships

#### Nutrition Content
Attribute | Domain | Description
-- | -- | --
amount | positive numbers | how much of the nutrient

#### Records Weight
Attribute | Domain | Description
-- | -- | --
weight | positive numbers | weight

#### Does Workout
Attribute | Domain | Description
-- | -- | --
duration | positive time | how long
intensity | any utf8 characters | what level of intensity

#### Eats
Attribute | Domain | Description
-- | -- | --
servings | positive numbers | how many servings of each food

### Assumptions & Notes
* User account passwords will actually be encrypted hashes, rather than plaintext passwords.
* Macronutrients and Micronutrients can both be stored in the same nutrients table. Recommended daily value percentages can be calculated by dividing the mass of any nutrient in a food by that nutrient's RDV amount.
* Users can specify their preferred unit of any food, eg. "half cup", "one egg", "sleeve of cookies", but they will also have to specify the serving size in mass/volume units that can be converted to metric.
* The number of servings that a user has eaten can optionally be calculated by dividing the mass or volume of the eaten food by the mass or volume of the standard serving size.

[ER Diagram Description PDF](https://github.com/466-mysequel/fitnesstracker/blob/master/docs/erd_description.pdf?raw=true)

## Relational Schema in 3NF

### Entities 
**UserAccounts**(<ins>username</ins>, password, first_name, last_name) 

**WorkoutTypes**(<ins>description</ins>) 

**Nutrients**(<ins>name</ins>, rdv_amount, rdv_unit) 

**FoodsBeverages**(<ins>name</ins>, calories_per_serving, serving_size_grams, serving_size_cc, serving_size_units, serving_size_label) 

### Relationships

**ContainsNutrients**(<ins>food name&dagger;, nutrient name&dagger;</ins>, amount) 

**RecordsWeight**(<ins>date, username&dagger;</ins>, weight_kg) 

**DoesWorkout**(<ins>date, username&dagger;, workout description&dagger;</ins>, duration, intensity) 

**Eats**(<ins>date, username&dagger;, food name&dagger;</ins>, servings) 

## SQL Scripts
* [Create Tables](https://github.com/466-mysequel/fitnesstracker/blob/master/sql/tables.sql?raw=true)
* [Insert Sample Data](https://github.com/466-mysequel/fitnesstracker/blob/master/sql/data.sql?raw=true)

## Download
**From the latest release as a zip:**

[Latest release](https://github.com/466-mysequel/releases/latest)

**From the latest commit using git:**

`git clone https://github.com/466-mysequel/fitnesstracker --depth 1`

## Demo
[Demo](#)

## Sources/Attributions

### Data
1. **Daily recommended intake values for the most common nutrients**  
   Kux, Leslie. “Food Labeling: Revision of the Nutrition and Supplement Facts Labels.” Federal Register, May 27, 2016. [FR Doc. 2016-11867](http://federalregister.gov/a/2016-11867)
2. **MET values for various activities**  
   Ainsworth BE, Haskell WL, Herrmann SD, Meckes N, Bassett Jr DR, Tudor-Locke C, Greer JL, Vezina J, Whitt-Glover MC, Leon AS. The Compendium of Physical Activities Tracking Guide. Healthy Lifestyles Research Center, College of Nursing & Health Innovation, Arizona State University. Retrieved [date] from the World Wide Web. [https://sites.google.com/site/compendiumofphysicalactivities/](https://sites.google.com/site/compendiumofphysicalactivities/)

### Code
1. **Bootstrap framework** - [MIT License](https://github.com/twbs/bootstrap/blob/v4.0.0/LICENSE)
