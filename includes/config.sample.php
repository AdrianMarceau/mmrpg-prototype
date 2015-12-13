<?php

/*
 * Mega Man RPG Prototype
 * This is the SAMPLE config file!!!
 * 1.  Update this file with your own credentials and settings
 * 2.  Save and rename this file to "config.php" in the same directory
 * 3.  Remove this comment from the code and/or put your own
 * 4.  Enjoy playing with the code of the MMRPG!
 */

// Define the global path variables for this installation
define('MMRPG_CONFIG_ROOTDIR', '/var/www/html/');
define('MMRPG_CONFIG_ROOTURL', 'http://'.$_SERVER['HTTP_HOST'].'/');
define('MMRPG_CONFIG_CACHE_INDEXES', false);
define('MMRPG_CONFIG_IS_LIVE', false);

// Define the global database credentials for this installation
define('MMRPG_CONFIG_DBHOST', 'localhost');
define('MMRPG_CONFIG_DBUSERNAME', 'username');
define('MMRPG_CONFIG_DBPASSWORD', 'password');
define('MMRPG_CONFIG_DBCHARSET', 'utf8');
define('MMRPG_CONFIG_DBNAME', 'mmrpg2k15');

// Define the global credentials for any web analytics accounts
define('MMRPG_ANALYTICS_ACCOUNT', 'UA-00000000-0');
define('MMRPG_ANALYTICS_DOMAIN', 'rpg.megamanpoweredup.net');

// Define the list of administrator-approved remote addresses
define('MMRPG_CONFIG_ADMIN_LIST', '127.0.0.1,999.999.999.999');

?>