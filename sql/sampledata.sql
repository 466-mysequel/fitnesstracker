# Users
INSERT INTO user (id, first_name, last_name, username, password) VALUES
    (1, 'Alice',   'Anderson', 'alice',   '$2y$10$V07jtzDcHw/LOqUyTM6gp.VgZ2Di6N38Arah/2aOZPTkUJFik9FQy'),
    (2, 'Bob',     'Brown',    'bob',     '$2y$10$raqJLycfygrDJYCsGBmgOu.NaBrz6RVfLyZVy9/drfgLC5zEjZR36'),
    (3, 'Charles', 'Clark',    'charlie', '$2y$10$QBAn8OaAqTa6H2e5teFuE.ukkR0XcZtpBcMvmMWYSPz8rP1uaTCtG'),
    (4, 'David',   'Davis',    'dave',    '$2y$10$z4tH5cp.PuqExVjEgW/72.HQW1fAMsaRqlo618k4DIgCpjhgtYrdi'),
    (5, 'Evelyn',  'Evans',    'eve',     '$2y$10$49bpYWA3Fhknb3Ib2bonYegG5TteYKyoXYEtWbQRAVyAhwNmCKR6e');

# Nutrients
INSERT INTO nutrient (name, rdv_amount, rdv_unit) VALUES
# Macros: https://www.fda.gov/media/99059/download
    ('Fat',                  78, 'g'),
    ('Saturated fat',        20, 'g'),
    ('Cholesterol',         300, 'mg'),
    ('Total carbohydrates', 275, 'g'),
    ('Sodium',             2300, 'mg'),
    ('Dietary Fiber',        28, 'g'),
    ('Protien',              50, 'g'),
    ('Added sugars',         50, 'g'),
# Micros: https://www.fda.gov/media/99069/download
    ('Vitamin A',           900, 'mcg'),
    ('Vitamin C',            90, 'mg'),
    ('Calcium',            1300, 'mg'),
    ('Vitamin D',            20, 'mcg'),
    ('Vitamin E',            15, 'mg'),
    ('Vitamin K',           120, 'mcg'),
    ('Thiamin',             1.2, 'mg'),
    ('Riboflavin',          1.3, 'mg'),
    ('Niacin',               16, 'mg'),
    ('Vitamin B6',          1.7, 'mg'),
    ('Folate',              400, 'mcg'),
    ('Vitamin B12',         2.4, 'mcg'),
    ('Biotin',               30, 'mcg'),
    ('Pantothenic acid',      5, 'mg'),
    ('Phosphorus',         1250, 'mg'),
    ('Iodine',              150, 'mcg'),
    ('Magnesium',           420, 'mg'),
    ('Zinc',                 11, 'mg'),
    ('Selenium',             55, 'mcg'),
    ('Copper',              0.9, 'mg'),
    ('Manganese',           2.3, 'mg'),
    ('Chromium',             35, 'mcg'),
    ('Molybdenum',           45, 'mcg'),
    ('Chloride',           2300, 'mg'),
    ('Potassium',          4700, 'mg'),
    ('Choline',             550, 'mg'),
# Others
    ('Iron',                 10, 'mg'),
    ('Trans Fat',             2, 'g'),
    ('Betaine',             500, 'mg');

