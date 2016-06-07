<?php

// Include mandatory config files
define('MMRPG_BUILD', 'mmrpg2k11');
define('MMRPG_VERSION', '2.9.2');
require('data/config.php');
require('data/settings.php');
require('data/debug.php');

// Turn ON error reporting if admin
if (MMRPG_CONFIG_ADMIN_MODE){
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
}

// Turn OFF error reporting if live
if (MMRPG_CONFIG_IS_LIVE){
    ini_set('display_errors',0);
    ini_set('display_startup_errors',0);
    error_reporting(0);
}

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

// Create the global database object
if (!defined('MMRPG_INDEX_SESSION') && !defined('MMRPG_INDEX_STYLES')){
    if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG'] = array(); }
    $db = new cms_database();
    // If the database could not be created, critical error mode!
    if ($db->CONNECT === false){
        define('MMRPG_CRITICAL_ERROR', true);
        $_GET = array();
        $_GET['page'] = 'error';
    }
}

// Include mandatory class files
require('classes/rpg_battle.php');
require('classes/rpg_field.php');
require('classes/rpg_player.php');
require('classes/rpg_robot.php');
require('classes/rpg_ability.php');
require('classes/rpg_item.php');

// Include mandatory function files
require('data/functions/website.php');
require('data/functions/game.php');
require('data/functions/prototype.php');

// Load startup file unless explicitly prevented
if (!defined('MMRPG_EXTERNAL_TOP_INCLUDE')){
    require('mmrpg.php');
}


?>