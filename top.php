<?php

// Include mandatory config files
define('MMRPG_BUILD', 'mmrpg2k11');
define('MMRPG_VERSION', '2.3.4');
require('includes/config.php');

// Update the timezone before starting the session
@date_default_timezone_set('Canada/Eastern');
//@ini_set('session.gc_maxlifetime', 24*60*60);
//@ini_set('session.gc_probability', 1);
//@ini_set('session.gc_divisor', 1);
session_start();

// Turn off magic quotes before it causes and problems
if (get_magic_quotes_gpc()){
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

// Include cms classs first and foremost
require('classes/cms_database.php');
require('classes/cms_website.php');
require('classes/cms_index.php');

// Define the debug mode flag based on session flag if not already set
if (!defined('MMRPG_CONFIG_DEBUG_MODE')){
    define('MMRPG_CONFIG_DEBUG_MODE', !empty($_SESSION['GAME']['debug_mode']) ? true : false);
}

// Define the perspective mode flag based on session flag if not already set
if (!defined('MMRPG_CONFIG_PERSPECTIVE_MODE')){
    define('MMRPG_CONFIG_PERSPECTIVE_MODE', isset($_SESSION['GAME']['perspective_mode']) && empty($_SESSION['GAME']['perspective_mode']) ? false : true);
}

// Create the global database object
if (!defined('MMRPG_INDEX_SESSION')){
    if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG'] = array(); }
    $db = new cms_database();
    // If the database could not be created, critical error mode!
    if ($db->CONNECT === false){
        define('MMRPG_CRITICAL_ERROR', true);
        $_GET = array();
        $_GET['page'] = 'error';
    }
}

// Now we can require the general settings file
require('includes/settings.php');

// Collect or generate the developer whitelist for the admin panel
$dev_whitelist = !empty($_SESSION['dev_whitelist']) ? $_SESSION['dev_whitelist'] : array();
if (!defined('MMRPG_INDEX_SESSION') && !defined('MMRPG_INDEX_STYLES')
    && (defined('MMRPG_ADMIN_PANEL') || empty($_SESSION['dev_whitelist']))){

    // Define some defaults for the whitelist
    $dev_whitelist = array();
    if (MMRPG_CONFIG_IS_LIVE === false){ $dev_whitelist[] = '127.0.0.1'; }

    // Attempt to collect a list of developers from the database
    $dev_userdata = $db->get_array_list("SELECT
        users.user_id,
        users.user_name_clean,
        users.user_ip_addresses,
        roles.role_id,
        roles.role_name,
        roles.role_level
        FROM mmrpg_users AS users
        LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
        WHERE
        users.user_ip_addresses <> ''
        AND roles.role_level >= 5
        ORDER BY
        user_id ASC
        ;", 'user_id');

    // If developers were found, loop through and collect IPs
    if (!empty($dev_userdata)){
        foreach ($dev_userdata AS $uid => $udata){
            $ip_list = $udata['user_ip_addresses'];
            $ip_list = strstr($ip_list, ',') ? explode(',', $ip_list) : array($ip_list);
            $ip_list = array_filter(array_map('trim', $ip_list));
            foreach ($ip_list AS $ip){ $dev_whitelist[] = $ip; }
        }
    }

    // Update the session whitelist with values
    $_SESSION['dev_whitelist'] = $dev_whitelist;

}

// Define whether or not we're being viewed by an admin
$is_admin_mode = !empty($_SESSION['admin_id']) ? true : false;
define('MMRPG_CONFIG_ADMIN_MODE', $is_admin_mode);

// Turn ON error reporting if admin
if (MMRPG_CONFIG_ADMIN_MODE){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('xdebug.max_nesting_level', 32);
    error_reporting(-1);
}

// Turn OFF error reporting if live
if (!MMRPG_CONFIG_ADMIN_MODE && MMRPG_CONFIG_IS_LIVE){
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('log_errors', 1);
    ini_set('ignore_repeated_errors', 1);
    ini_set('ignore_repeated_source', 1);
    ini_set('error_log', rtrim(dirname(MMRPG_CONFIG_ROOTDIR), '/').'/_logs/php_error.log');
}

// Stop BANNED users from accessing the website
if (MMRPG_CONFIG_BANNED_LIST){
    $current_ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $banned_list = explode(',', MMRPG_CONFIG_BANNED_LIST);
    foreach ($banned_list AS $ip_key => $ip_start){
        $ip_start_pattern = '/^'.str_replace('.', '\\.', $ip_start).'/';
        if (preg_match($ip_start_pattern, $current_ip)){
            exit('No thank you ['.$ip_key.']');
        }
    }
}

// Include mandatory class files
require('classes/rpg_user.php');
require('classes/rpg_user_role.php');
require('classes/rpg_functions.php');
require('classes/rpg_game.php');
require('classes/rpg_prototype.php');
require('classes/rpg_mission.php');
require('classes/rpg_mission_starter.php');
require('classes/rpg_mission_single.php');
require('classes/rpg_mission_double.php');
require('classes/rpg_mission_fortress.php');
require('classes/rpg_mission_bonus.php');
require('classes/rpg_mission_player.php');
require('classes/rpg_mission_challenge.php');
require('classes/rpg_type.php');
require('classes/rpg_object.php');
require('classes/rpg_canvas.php');
require('classes/rpg_console.php');
require('classes/rpg_target.php');
require('classes/rpg_damage.php');
require('classes/rpg_recovery.php');
require('classes/rpg_disabled.php');
require('classes/rpg_battle.php');
require('classes/rpg_field.php');
require('classes/rpg_player.php');
require('classes/rpg_robot.php');
require('classes/rpg_ability.php');
require('classes/rpg_ability_damage.php');
require('classes/rpg_ability_recovery.php');
require('classes/rpg_item.php');
require('classes/rpg_item_damage.php');
require('classes/rpg_item_recovery.php');

// Include mandatory function files
require('functions/website.php');
require('functions/game.php');
require('functions/prototype.php');

// Load startup file unless explicitly prevented
if (!defined('MMRPG_EXTERNAL_TOP_INCLUDE')){
    require('mmrpg.php');
}


?>