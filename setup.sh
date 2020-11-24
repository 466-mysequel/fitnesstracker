#!/bin/bash
# This script will prompt for settings, create/overwrite config/config.php, and run SQL scripts.
# You could just edit config/config.php based on config/sample-config.php and run scripts yourself.
read -p "MySQL server name [courses]: " servername
servername=${servername:-courses}
read -p "MySQL database name [$USER]: " dbname
dbname=${dbname:-$USER}
read -p "MySQL user name [$USER]: " username
username=${username:-$USER}
read -s -p "MySQL user password: " password
echo
cat > config/config.php <<PHPCONFIG && echo -e "\033[0;32mWrote config.php\033[0m" || echo -e "\033[0;31mCould not write config.php\033[0m";
<?php
// Database
\$servername = "$servername";
\$username = "$username";
\$password = "$password";
\$dbname = "$dbname";
?>
PHPCONFIG
mysql -h $servername -u $username -p$password -D $dbname < sql/tables.sql \
    && echo -e "\033[0;32mCreated tables\033[0m" \
    || echo -e "\033[0;31mProblem creating tables\033[0m"
mysql -h $servername -u $username -p$password -D $dbname < sql/sampledata.sql \
    && echo -e "\033[0;32mInserted sample data\033[0m" \
    || echo -e "\033[0;31mProblem inserting sample data\033[0m"
