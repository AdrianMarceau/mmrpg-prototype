<?php
/*
 * Project   : [Mega Man RPG Prototype Version 8] <megamanpoweredup.net>
 * Name      : Config Object <config.php>
 * Author    : Adrian Marceau <Ageman20XX>
 * Created   : August 22nd, 2011
 * Modified  : May 10th, 2015
 * Update this config file with your own database credentials
 * and path settings then rename it to config.php for install.
 */

/*
 * SYSTEM/ENVIRONMENT CONFIG
 * These variables pertain to the environment and
 * system settings for the running scripts
 */

// Define the global cache date for... caching
define('MMRPG_CONFIG_CACHE_DATE', '20141201-05'); //Prev : 20140830-08, 20140501-01
define('MMRPG_CONFIG_DEBUG_MODE', true);

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
  define('MMRPG_CONFIG_ROOTDIR', '/var/www/html/rpg.megamanpoweredup.net/'); // Syetem Root DIR
  define('MMRPG_CONFIG_ROOTURL', 'http://local.megamanpoweredup.net/'); // Syetem Root URL
  define('MMRPG_CONFIG_IS_LIVE', false); // System is not LIVE
  define('MMRPG_CONFIG_CACHE_PAGES', false); // Turn OFF page caching
  define('MMRPG_CONFIG_CACHE_INDEXES', false); // Turn OFF index caching
}
elseif (MMRPG_CONFIG_DOMAIN == 'rpg.megamanpoweredup.net'){
  define('MMRPG_CONFIG_ROOTDIR', '/var/www/html/'); // Syetem Root DIR
  define('MMRPG_CONFIG_ROOTURL', 'http://'.MMRPG_CONFIG_DOMAIN.'/'); // Syetem Root URL
  define('MMRPG_CONFIG_IS_LIVE', true); // System is LIVE
  define('MMRPG_CONFIG_CACHE_PAGES', true); // Turn OFF page caching
  define('MMRPG_CONFIG_CACHE_INDEXES', true); // Turn ON index caching
}

// DATABASE CONFIG
if (MMRPG_CONFIG_DOMAIN == 'local.megamanpoweredup.net'){
  //define('MMRPG_CONFIG_DBHOST', 'localhost'); // Database Host
  define('MMRPG_CONFIG_DBHOST', '127.0.0.1'); // Database Host
  define('MMRPG_CONFIG_DBUSERNAME', 'username'); // Database Name
  define('MMRPG_CONFIG_DBPASSWORD', 'password'); // Database Password
  define('MMRPG_CONFIG_DBCHARSET', 'utf8'); // Database Charset
  define('MMRPG_CONFIG_DBNAME', 'mmrpg2k15'); // Database Name
}
elseif (MMRPG_CONFIG_DOMAIN == 'rpg.megamanpoweredup.net'){
  define('MMRPG_CONFIG_DBHOST', 'localhost'); // Database Host
  define('MMRPG_CONFIG_DBUSERNAME', 'username'); // Database Name
  define('MMRPG_CONFIG_DBPASSWORD', 'password'); // Database Password
  define('MMRPG_CONFIG_DBCHARSET', 'utf8'); // Database Charset
  define('MMRPG_CONFIG_DBNAME', 'mmrpg2k15'); // Database Name
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
//define('MMRPG_SETTINGS_ONLINE_TIMEOUT', (60 * 9)); // In seconds (60sec x 9min = 9 Minutes)
define('MMRPG_SETTINGS_ACTIVE_TIMEOUT', (60 * 60 * 24 * 90)); // In seconds (60sec x 60min x 24hr x 90days)
define('MMRPG_SETTINGS_LEGACY_TIMEOUT', (60 * 60 * 24 * 365)); // In seconds (60sec x 60min x 24hr x 365days)
define('MMRPG_SETTINGS_UPDATE_TIMEOUT', (60 * 60 * 24 * 1)); // In seconds (60sec x 60min x 24hr x 1days)

// Define the global difficulty from the session if available
$temp_difficulty = !empty($_SESSION['GAME']['USER']['difficulty']) && in_array($_SESSION['GAME']['USER']['difficulty'], array('easy', 'normal', 'hard')) ? $_SESSION['GAME']['USER']['difficulty'] : 'normal';
define('MMRPG_SETTINGS_GAME_DIFFICULTY', $temp_difficulty); // Define the game's difficulty level for turns/points/robots

// Define the global guest ID to prevent confusion
define('MMRPG_SETTINGS_GUEST_ID', 888888); // Doubt we'll ever get this many users

// Define the global target player ID to prevent overlap
define('MMRPG_SETTINGS_TARGET_PLAYERID', 999999); // Doubt we'll ever get this many users

// Define the global max and min limits for field multipliers
define('MMRPG_SETTINGS_MULTIPLIER_MIN', 0.1); // Prevent the multiplier from reaching absolute zero
define('MMRPG_SETTINGS_MULTIPLIER_MAX', 9.9); // Ensure the multiplier stays under the limit of ten

// Define the global max and min limits for robot stat values
define('MMRPG_SETTINGS_LEVEL_MIN', 1); // Prevent the level from going below base one
define('MMRPG_SETTINGS_LEVEL_MAX', 100); // Prevent the level from going above one hundred
define('MMRPG_SETTINGS_EXPERIENCE_MIN', 1000); // The minimum required experience for a new level
define('MMRPG_SETTINGS_EXPERIENCE_MOD', 0.1); // Slightly more experience is required for each new level
define('MMRPG_SETTINGS_STATS_MIN', 0); // Prevent the stats from going under zero
define('MMRPG_SETTINGS_STATS_MAX', 999999); // Ensure the stats to not break the display limit
//define('MMRPG_SETTINGS_STATS_BASEMAX', 9999); // Ensure the stats to not break the display limit
//define('MMRPG_SETTINGS_STATS_MAX', 9999); // Ensure the stats to not break the display limit
define('MMRPG_SETTINGS_STATS_LEVELBOOST', 0.050); // Each level up boosts all stats by 5% of base value
//define('MMRPG_SETTINGS_STATS_LIMITBREAK', 0.001); // Each maximum stat calculation is multiplied by 1/1000
define('MMRPG_SETTINGS_STATS_ROBOTMAX', 10.00); // Ensure robots grow only to 1000% of max base stat
define('MMRPG_SETTINGS_STATS_PLAYERMAX', 0.25); // Ensure player boosts the last 25% of max base stat

// Calculate the stat level boost, requires base stat and current level
function MMRPG_SETTINGS_STATS_GET_LEVELBOOST($base, $level){
  $temp_level = $level - 1;
  $level_boost = $base * MMRPG_SETTINGS_STATS_LEVELBOOST;
  return ceil($temp_level * $level_boost);
}
// Calculate the minimum boosted value of a given robot stat, requires base stat and current robot level
function MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base, $level){
  $temp_base = $base;
  $level_boost = MMRPG_SETTINGS_STATS_GET_LEVELBOOST($base, $level);
  return round($temp_base + $level_boost);
}
// Calculate the maximum boosted value of a given robot stat, requires base stat and current robot level
function MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base, $level){
  $temp_base = $base;
  $level_boost = MMRPG_SETTINGS_STATS_GET_LEVELBOOST($base, $level);
  $robot_boost = MMRPG_SETTINGS_STATS_ROBOTMAX; //MMRPG_SETTINGS_STATS_BASEMAX * MMRPG_SETTINGS_STATS_LIMITBREAK * MMRPG_SETTINGS_STATS_ROBOTMAX;
  return round(($temp_base + $level_boost) * $robot_boost);
}
// Calculate the minimum player boost for a given robot stat, requires base stat and current robot level
function MMRPG_SETTINGS_STATS_GET_PLAYERMIN($base, $level){
  $temp_base = 0;
  return round($temp_base);
}
// Calculate the maximum player boost for a given robot stat, requires base stat and current robot level
function MMRPG_SETTINGS_STATS_GET_PLAYERMAX($base, $level){
  $temp_base = $base;
  $level_boost = MMRPG_SETTINGS_STATS_GET_LEVELBOOST($base, $level);
  $player_boost = MMRPG_SETTINGS_STATS_BASEMAX * MMRPG_SETTINGS_STATS_LIMITBREAK * MMRPG_SETTINGS_STATS_PLAYERMAX;
  return round(($temp_base + $level_boost) * $player_boost);
}

// Define the global values for robot recharge stat values
define('MMRPG_SETTINGS_RECHARGE_WEAPONS', 1); // Recharge one unit of weapon energy each turn
define('MMRPG_SETTINGS_RECHARGE_ATTACK', 1); // Recharge one unit of attack power each turn
define('MMRPG_SETTINGS_RECHARGE_DEFENSE', 1); // Recharge one unit of defense power each turn
define('MMRPG_SETTINGS_RECHARGE_SPEED', 1); // Recharge one unit of speed power each turn

// Define the global values for item maximums
define('MMRPG_SETTINGS_SHARDS_MAXQUANTITY', 4); // Define the number of shards required to create a new core
define('MMRPG_SETTINGS_ITEMS_MAXQUANTITY', 99); // Define the max quantity allowed for normal held items
define('MMRPG_SETTINGS_CORES_MAXQUANTITY', 999); // Define the max quantity allowed for elemental robot cores

// Define the global values for item maximums
define('MMRPG_SETTINGS_SHARDS_PERCORE', 4); // Define the number of shards required to create a new core

// Define the global variables for the total number of robots allower per player
define('MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MIN', 1); // The minimum number of robots required for battle per side
define('MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX', 8); // The maximum number of robots allowed for battle per side

// Define the global multiplier for battle points per level
define('MMRPG_SETTINGS_BATTLEPOINTS_MINREWARD', 1); // The minimum point reward value for a given mission
define('MMRPG_SETTINGS_BATTLEPOINTS_MAXREWARD', 999999999); // The maximum point reward value for a given mission
define('MMRPG_SETTINGS_BATTLETURNS_MINAMOUNT', 1); // The minimum target turn value for a given mission
define('MMRPG_SETTINGS_BATTLETURNS_MAXAMOUNT', 99); // The maximum target turn value for a given mission
define('MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER', 2.0); // The point rate per robot level multiplier for player battles
define('MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER', 0.5); // The point rate per target robot multiplier for player battles

// -- DIFFICULTY SETTINGS -- //
// Define global multipliers that differ based on difficulty level

// Define the global variables that determine target turn values
define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 3); // The point rate per target robot master for normal battles
define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 1); // The point rate per target support mecha for normal battles

// -- EASY MODE -- //
if (MMRPG_SETTINGS_GAME_DIFFICULTY == 'easy'){

  // Define the global variables that determine the player robot limit
  define('MMRPG_SETTINGS_BATTLEROBOTS_SELECT_MAX', 8); // The max number of robots the player can bring into battle for any mission

  // Define the global variables that determine mission point values
  define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL', 500); // The point rate per robot master level for normal battles
  define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2', 50); // The point rate per support mecha level for normal battles

  // Define the global variables that determine target turn values
  //define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 6); // The point rate per target robot master for normal battles
  //define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 2); // The point rate per target support mecha for normal battles

  // Define the global variables that determine opponent stat boosts
  define('MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST', 100); // The bonus stat seed for target robot masters in battle

}
// -- NORMAL MODE -- //
elseif (MMRPG_SETTINGS_GAME_DIFFICULTY == 'normal'){

  // Define the global variables that determine the player robot limit
  define('MMRPG_SETTINGS_BATTLEROBOTS_SELECT_MAX', 4); // The max number of robots the player can bring into battle for any mission

  // Define the global variables that determine mission points and turns
  define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL', 1000); // The point rate per robot master level for normal battles
  define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2', 100); // The point rate per support mecha level for normal battles

  // Define the global variables that determine target turn values
  //define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 3); // The point rate per target robot master for normal battles
  //define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 1); // The point rate per target support mecha for normal battles

  // Define the global variables that determine opponent stat boosts
  define('MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST', 300); // The bonus stat seed for target robot masters in battle

}
// -- HARD MODE -- //
elseif (MMRPG_SETTINGS_GAME_DIFFICULTY == 'hard'){

  // Define the global variables that determine the player robot limit
  define('MMRPG_SETTINGS_BATTLEROBOTS_SELECT_MAX', 2); // The max number of robots the player can bring into battle for any mission

  // Define the global variables that determine mission points and turns
  define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL', 2000); // The point rate per robot master level for normal battles
  define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2', 200); // The point rate per support mecha level for normal battles

  // Define the global variables that determine target turn values
  //define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 1); // The point rate per target robot master for normal battles
  //define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 0); // The point rate per target support mecha for normal battles

  // Define the global variables that determine opponent stat boosts
  define('MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST', 900); // The bonus stat seed for target robot masters in battle

}

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
define('MMRPG_SETTINGS_THREADS_RECENT', 10); // Only display 10 discussion threads per category
define('MMRPG_SETTINGS_POSTS_PERPAGE', 15); // Only display 15 comment posts per page
define('MMRPG_SETTINGS_SEARCH_PERPAGE', 30); // Only display 50 results per page

// Define the global variables that control field and fusion star exchange rates relative to cores
define('MMRPG_SETTINGS_STARS_SELLPRICE', 3000); // Base price for field stars sold in shop (fusion are double)
define('MMRPG_SETTINGS_STARS_ATTACKBOOST', 10); // Increase damage inflicted by all abilities of the same type (+10 Attack)
define('MMRPG_SETTINGS_STARS_DEFENSEBOOST', 10); // Decrease damage received from all abilities of the same type (+10 Defense)
define('MMRPG_SETTINGS_STARS_ATTACKBOOST_MAX', 9999); // Maximum increase to damage inflicted by abilities with matching types (999.0%)
define('MMRPG_SETTINGS_STARS_DEFENSEBOOST_MAX', 9999); // Minimum decrease to damage received from abilities with matching types (0.999%)
define('MMRPG_SETTINGS_STARS_COREPRICE_BOOST', 0.01); // Increased sell price for cores in Reggae's shop (+1%)
define('MMRPG_SETTINGS_STARS_STARPRICE_BREAK', 0.01); // Decreased sell price for stars in Kalinka's shop (-1%)

// Define the global variables that control robot core exchange rates relative to stars
define('MMRPG_SETTINGS_CORES_SELLPRICE', 1000); // Base price for cores sold in shop (of any type)
define('MMRPG_SETTINGS_CORES_STARPRICE_BOOST', 0.01); // Increased sell price for stars in Kalinka's shop (+1%)
define('MMRPG_SETTINGS_CORES_COREPRICE_BREAK', 0.01); // Decreased sell price for cores in Reggae's shop (-1%)

// Define the global encoding and decoding variables
define('MMRPG_SETTINGS_ROBOT_ENCODING_SALT', 'Bulbasaur Used Vine Whip!'); // Define the encoding salt for robot data

// Define the username and email address combinations that should be allowed to bypass age restriction
define('MMRPG_COPPA_COMPLIANCE_PERMISSIONS', '
AdrianMarceau/adrian.marceau@gmail.com,
Ageman20XX/adrian.marceau@gmail.com,
test/test@test.com
'); // Manually list all the username/password combos that



?>