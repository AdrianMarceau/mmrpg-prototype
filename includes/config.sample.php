<?

// Define approved domains this project can run on
$approved_domains = array('localhost');

// Require the config helper file to pre-collect data for later
$config_dir = rtrim(str_replace('\\', '/', dirname(__FILE__)), '/');
require_once($config_dir.'/config.helper.php');

// Define the global cache date setting (for when DB connection not exists yet)
define('MMRPG_CONFIG_CACHE_DATE_FALLBACK', '20140830-02');

// Define the debug mode flag (useful for printing extra info, but slow)
define('MMRPG_CONFIG_DEBUG_MODE', false);

// Define the mainenance mode flag and message (for when you wanna take the site offline)
define('MMRPG_CONFIG_MAINTENANCE_MODE', false);
define('MMRPG_CONFIG_MAINTENANCE_MODE_MESSAGE', 'SERVER MAINTENANCE IN PROGRESS!');

// Define whether or not we're current in HTTPS mode
define('MMRPG_IS_HTTPS', $is_https);

// Define the global path variables for this installation
define('MMRPG_CONFIG_ROOTDIR', '/var/www/html/');
define('MMRPG_CONFIG_ROOTURL', (MMRPG_IS_HTTPS ? 'https' : 'http').'://'.$current_domain.'/');
define('MMRPG_CONFIG_CACHE_INDEXES', false);
define('MMRPG_CONFIG_IS_LIVE', false);

// Define which server environment we're on (local|dev|stage|prod)
define('MMRPG_CONFIG_SERVER_ENV', 'local');

// Define which servers we should pull dev and/or live data from
define('MMRPG_CONFIG_PULL_DEV_DATA_FROM', false);
define('MMRPG_CONFIG_PULL_LIVE_DATA_FROM', 'prod');

// Define the global database credentials for this installation
define('MMRPG_CONFIG_DBHOST', 'localhost');
define('MMRPG_CONFIG_DBUSERNAME', 'username');
define('MMRPG_CONFIG_DBPASSWORD', 'password');
define('MMRPG_CONFIG_DBCHARSET', 'utf8');
define('MMRPG_CONFIG_DBNAME', 'mmrpg2k11');

// Define the global variables for the CDN (currently used for audio files only)
define('MMRPG_CONFIG_CDN_ENABLED', true);
define('MMRPG_CONFIG_CDN_ROOTURL', 'https://cdn.mmrpg-world.net/');
define('MMRPG_CONFIG_CDN_PROJECT', 'prototype');

// Define the analytics account ID
define('LEGACY_MMRPG_GA_ACCOUNTID', 'UA-00000000-0');

// Define the PASSWORD SALT and OMEGA SEED string values
define('MMRPG_SETTINGS_PASSWORD_SALT', 'password-salt');
define('MMRPG_SETTINGS_OMEGA_SEED', 'omega-seed-salt');

// Define some SALT for the IMAGEPROXY hash values
define('MMRPG_SETTINGS_IMAGEPROXY_SALT', 'image-proxy-salt');

// Define the COPPA email exceptions based on written permission from guardians
$temp_coppa_list = array();
define('MMRPG_CONFIG_COPPA_PERMISSIONS', implode(',', $temp_coppa_list));

// Define the list of user IDs that can log into back-end
$temp_admin_list = array();
define('MMRPG_CONFIG_ADMIN_LIST', implode(',', $temp_admin_list));

// Define the list of back-end permissions given user IDs
$temp_admin_perms_list = array(
    1, // mmrpg_developer
    2, // mmrpg_admin
    3, // mmrpg_contributor
    4  // mmrpg_moderator
    );
define('MMRPG_CONFIG_ADMIN_PERMS_LIST', json_encode($temp_admin_perms_list));

// Define the list of BANNED remote addresses that cannot access site
$temp_banned_list = array(
    // mmrpg_developer
    1 => array('*'),
    // mmrpg_admin
    2 => array('edit-pages', 'edit-users', 'edit-players', 'edit-robots', 'edit-fields', 'edit-challenges', 'edit-stars', 'delete-cached-files'),
    // mmrpg_contributor
    3 => array('edit-players', 'edit-robots', 'edit-fields'),
    // mmrpg_moderator
    4 => array('edit-users')
    );
define('MMRPG_CONFIG_BANNED_LIST', implode(',', $temp_banned_list));

?>