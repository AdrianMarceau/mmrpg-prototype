<?php
/*
 * Project   : [Mega Man RPG Prototype Version 7] <megamanpoweredup.net>
 * Name      : Config Object <config.php>
 * Author    : Adrian Marceau <Ageman20XX>
 * Created   : August 22nd, 2011
 * Modified  : August 30th, 2014
 * Update this config file with your own database credentials
 * and path settings then rename it to config.php for install.
 */

/*
 * SYSTEM/ENVIRONMENT CONFIG
 * These variables pertain to the environment and
 * system settings for the running scripts
 */

// Define the global cache date for... caching
define('MMRPG_CONFIG_CACHE_DATE', '20140830-02'); //Prev : 20140501-01
define('MMRPG_CONFIG_DEBUG_MODE', false);

// Define the debug checkpoint function
function mmrpg_debug_checkpoint($file, $line, $extra = ''){
  global $DB;
  static $last_memory_usage = 0;
  static $last_micro_time = 0;
  static $checkpoint = 0;
  $query = 'CHECKPOINT in '.str_replace(MMRPG_CONFIG_ROOTDIR, '', str_replace('\\', '/', $file)).' on line '.$line.' where memory is ';
  $mem_usage = memory_get_usage();
  $micro_time = microtime(true);
  if ($mem_usage < 1024){ $query .= $mem_usage.' B'; }
  elseif ($mem_usage < 1048576){ $query .= round($mem_usage/1024,2).' KB'; }
  else { $query .= round($mem_usage/1048576,2).' MB'; }
  $query .= ' ';
  $mem_colour = 'grey';
  $mem_diff = $mem_usage - $last_memory_usage;
  $time_diff = $micro_time - $last_micro_time;
  $last_memory_usage = $mem_usage;
  $last_micro_time = $micro_time;
  
  if ($mem_diff < 1){ $mem_diff = $mem_diff * -1; $mem_sign = '-'; $mem_colour = 'green'; }
  elseif ($mem_diff > 1){ $mem_sign = '+'; $mem_colour = 'red'; }
  else { $mem_sign = '+/-'; $mem_colour = 'grey'; }
  $query .= '(<span style="color: '.$mem_colour.';">'.$mem_sign;
  if ($mem_diff < 1024){ $query .= $mem_diff.' B'; }
  elseif ($mem_diff < 1048576){ $query .= round($mem_diff/1024,2).' KB'; }
  else { $query .= round($mem_diff/1048576,2).' MB'; }
  $query .= '</span>)'; //."\r\n";
  
  $query .= ' [<span style="color: grey;">+'.round($time_diff, 6).'s</span>]'."\r\n";
  
  if (!empty($extra)){ $query .= '<div style="font-size: 90%; padding: 5px 0 0 30px; margin: 0; color: #6D6D6D;">'.$extra.'</div>'; }
  $query = '<span style="color: #262626;">'.$query.'</span>';
  $DB->DEBUG['script_queries'][] = $query;
  
  //echo $query;
  $checkpoint++;
  //if ($mem_usage >= (1024 * 1024 * 50)){ unset($DB); exit("\n\n|| -- 50MB MEMORY OVERLOAD --||\n\n"); }
}

// Define the core domain, locale, timezone, error reporting and cache settings
@preg_match('#^([-~a-z0-9\.]+)#i', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']), $THIS_DOMAIN);
define('MMRPG_CONFIG_DOMAIN', (isset($THIS_DOMAIN[0]) ? $THIS_DOMAIN[0] : false));
unset($THIS_DOMAIN);

// FILESYSTEM CONFIG
if (MMRPG_CONFIG_DOMAIN == 'local.megamanpoweredup.net'){
  define('MMRPG_CONFIG_ROOTDIR', '/var/www/html/rpg.megamanpoweredup.net/'); // System Root DIR
  define('MMRPG_CONFIG_ROOTURL', 'http://local.megamanpoweredup.net/'); // System Root URL
  define('MMRPG_CONFIG_IS_LIVE', false); // System is not LIVE
  define('MMRPG_CONFIG_CACHE_INDEXES', false); // Turn OFF index caching
}
elseif (MMRPG_CONFIG_DOMAIN == 'rpg.megamanpoweredup.net'){
  define('MMRPG_CONFIG_ROOTDIR', '/var/www/html/'); // System Root DIR
  define('MMRPG_CONFIG_ROOTURL', 'http://rpg.megamanpoweredup.net/'); // System Root URL
  define('MMRPG_CONFIG_IS_LIVE', true); // System is LIVE
  define('MMRPG_CONFIG_CACHE_INDEXES', true); // Turn ON index caching
}

// DATABASE CONFIG
if (MMRPG_CONFIG_DOMAIN == 'local.megamanpoweredup.net'){
  define('MMRPG_CONFIG_DBHOST', 'localhost'); // Database Host
  define('MMRPG_CONFIG_DBUSERNAME', 'username'); // Database Name
  define('MMRPG_CONFIG_DBPASSWORD', 'password'); // Database Password
  define('MMRPG_CONFIG_DBCHARSET', 'utf8'); // Database Charset
  define('MMRPG_CONFIG_DBNAME', 'mmrpg2k11'); // Database Name
}
elseif (MMRPG_CONFIG_DOMAIN == 'rpg.megamanpoweredup.net'){
  define('MMRPG_CONFIG_DBHOST', 'localhost'); // Database Host
  define('MMRPG_CONFIG_DBUSERNAME', 'username'); // Database Name
  define('MMRPG_CONFIG_DBPASSWORD', 'password'); // Database Password
  define('MMRPG_CONFIG_DBCHARSET', 'utf8'); // Database Charset
  define('MMRPG_CONFIG_DBNAME', 'mmrpg2k11'); // Database Name
}

// Define the cache and index paths for battles
define('MMRPG_CONFIG_BATTLES_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/battles/');
define('MMRPG_CONFIG_BATTLES_CACHE_PATH', MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.battles.'.MMRPG_CONFIG_CACHE_DATE.'.php');




/*
 * GAME SETTINGS
 * These variables pertain to the database
 * table names to be used by the running
 * scripts
 */

// Define whether or not we're being viewed by an admin
if (in_array($_SERVER['REMOTE_ADDR'], array('999.999.999.999'))){
  define('MMRPG_CONFIG_ADMIN_MODE', true);
} else {
  define('MMRPG_CONFIG_ADMIN_MODE', false);
}

// Define the global timeout variables for online and new status
define('MMRPG_SETTINGS_ONLINE_TIMEOUT', (60 * 30)); // In seconds (60sec x 60min = 1/2 Hour)
define('MMRPG_SETTINGS_ACTIVE_TIMEOUT', (60 * 60 * 24 * 90)); // In seconds (60sec x 60min x 24hr x 90days)
define('MMRPG_SETTINGS_LEGACY_TIMEOUT', (60 * 60 * 24 * 365)); // In seconds (60sec x 60min x 24hr x 365days)
define('MMRPG_SETTINGS_UPDATE_TIMEOUT', (60 * 60 * 24 * 1)); // In seconds (60sec x 60min x 24hr x 1days)

// Define the global guest ID to prevent confusion
define('MMRPG_SETTINGS_GUEST_ID', 888888); // Doubt we'll ever get this many users

// Define the global target player ID to prevent overlap
define('MMRPG_SETTINGS_TARGET_PLAYERID', 999999); // Doubt we'll ever get this many users

// Define the global max and min limits for field multipliers
define('MMRPG_SETTINGS_MULTIPLIER_MIN', 0.1); // Prevent the multiplier from reaching absolute zero
define('MMRPG_SETTINGS_MULTIPLIER_MAX', 9.9); // Ensure the multiplier stays under the limit of ten

// Define the global max and min limits for robot stat values
define('MMRPG_SETTINGS_STATS_MIN', 0); // Prevent the multiplier from reaching absolute zero
define('MMRPG_SETTINGS_STATS_MAX', 9999); // Ensure the multiplier stays under the limit of ten

// Define the global values for robot recharge stat values
define('MMRPG_SETTINGS_RECHARGE_WEAPONS', 1); // Recharge one unit of weapon energy each turn
define('MMRPG_SETTINGS_RECHARGE_ATTACK', 1); // Recharge one unit of attack power each turn
define('MMRPG_SETTINGS_RECHARGE_DEFENSE', 1); // Recharge one unit of defense power each turn
define('MMRPG_SETTINGS_RECHARGE_SPEED', 1); // Recharge one unit of speed power each turn

// Define the global values for robot weapon energy values
define('MMRPG_SETTINGS_ITEMS_MAXQUANTITY', 99); // Recharge one unit of weapon energy each turn

// Define the global variables for the total number of robots allower per player
define('MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MIN', 1); // The minimum number of robots required for battle per side
define('MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX', 8); // The maximum number of robots allowed for battle per side

// Define the global multiplier for battle points per level
define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL', 1000); // The point rate per robot master level for normal battles
define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2', 100); // The point rate per support mecha level for normal battles
define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 8); // The point rate per target robot master for normal battles
define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 2); // The point rate per target support mecha for normal battles
define('MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER', 2.0); // The point rate per robot level multiplier for player battles
define('MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER', 0.5); // The point rate per target robot multiplier for player battles

// Define the global default values for game multipliers
define('MMRPG_SETTINGS_WEAKNESS_MULTIPLIER', 2.0); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_RESISTANCE_MULTIPLIER', 0.5); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_AFFINITY_MULTIPLIER', -1.0); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_IMMUNITY_MULTIPLIER', 0.0); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_COREBOOST_MULTIPLIER', 1.5); // Core boosted abilites should recive a 50% boost

// Define the global comment size limit for characters
define('MMRPG_SETTINGS_COMMENT_MINLENGTH', 10); // Prevent spam comments
define('MMRPG_SETTINGS_COMMENT_MAXLENGTH', 5000); // Prevent wordy comments

// Define the global comment size limit for characters
define('MMRPG_SETTINGS_THREADNAME_MINLENGTH', 10); // Prevent spam threadnames
define('MMRPG_SETTINGS_THREADNAME_MAXLENGTH', 60); // Prevent wordy threadnames
define('MMRPG_SETTINGS_DISCUSSION_MINLENGTH', 100); // Prevent spam discussions
define('MMRPG_SETTINGS_DISCUSSION_MAXLENGTH', 20000); // Prevent wordy discussions

// Define the global frame index for robot sprites
define('MMRPG_SETTINGS_ROBOT_FRAMEINDEX', 'base/taunt/victory/defeat/shoot/throw/summon/slide/defend/damage/base2'); // Define the sprite index

// Define the global battle point requirements for posting
define('MMRPG_SETTINGS_THREAD_MINPOINTS', 5000); // Prevent spam comments
define('MMRPG_SETTINGS_POST_MINPOINTS', 1000); // Prevent wordy comments

// Define the global thread display limit for community
define('MMRPG_SETTINGS_THREADS_RECENT', 10); // Only display five per category

// Define the global encoding and decoding variables
define('MMRPG_SETTINGS_ROBOT_ENCODING_SALT', 'Only Yue Can Prevent Forest Fires'); // Define the encoding salt for robot data

// Define the username and email address combinations that should be allowed to bypass age restriction
define('MMRPG_COPPA_COMPLIANCE_PERMISSIONS', '
Developer/developer@domaincom,
test/test@test.com
'); // Manually list all the username/password combos that

?>