<?php

// Define the core domain, locale, timezone, error reporting and cache settings
@preg_match('#^([-~a-z0-9\.]+)#i', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']), $THIS_DOMAIN);
define('MMRPG_CONFIG_DOMAIN', (isset($THIS_DOMAIN[0]) ? $THIS_DOMAIN[0] : false));
unset($THIS_DOMAIN);

// Define whether or not we're being viewed by an admin
$is_admin = in_array($_SERVER['REMOTE_ADDR'], explode(',', MMRPG_CONFIG_ADMIN_LIST)) ? true : false;
define('MMRPG_CONFIG_ADMIN_MODE', $is_admin);

// Define the global cache date and settings
define('MMRPG_CONFIG_DEBUG_MODE', !empty($_SESSION['GAME']['debug_mode']) ? true : false);

// Define the cache date and path on this system
define('MMRPG_CONFIG_CACHE_DATE', '20150808-01');
define('MMRPG_CONFIG_CACHE_PATH', MMRPG_CONFIG_ROOTDIR.'data/cache/');

// Define the cache and index paths for battles
define('MMRPG_CONFIG_BATTLES_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/battles/');
define('MMRPG_CONFIG_BATTLES_CACHE_PATH', MMRPG_CONFIG_CACHE_PATH.'cache.battles.'.MMRPG_CONFIG_CACHE_DATE.'.php');

// Define the cache and index paths for players
define('MMRPG_CONFIG_PLAYERS_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/players/');
define('MMRPG_CONFIG_PLAYERS_CACHE_PATH', MMRPG_CONFIG_CACHE_PATH.'cache.players.'.MMRPG_CONFIG_CACHE_DATE.'.php');

// Define the cache and index paths for robots
define('MMRPG_CONFIG_ROBOTS_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/robots/');
define('MMRPG_CONFIG_ROBOTS_CACHE_PATH', MMRPG_CONFIG_CACHE_PATH.'cache.robots.'.MMRPG_CONFIG_CACHE_DATE.'.php');

// Define the cache and index paths for abilities
define('MMRPG_CONFIG_ABILITIES_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/abilities/');
define('MMRPG_CONFIG_ABILITIES_CACHE_PATH', MMRPG_CONFIG_CACHE_PATH.'cache.abilities.'.MMRPG_CONFIG_CACHE_DATE.'.php');

// Define the cache and index paths for fields
define('MMRPG_CONFIG_FIELDS_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/fields/');
define('MMRPG_CONFIG_FIELDS_CACHE_PATH', MMRPG_CONFIG_CACHE_PATH.'cache.fields.'.MMRPG_CONFIG_CACHE_DATE.'.php');

// Define the global timeout variables for online and new status
define('MMRPG_SETTINGS_ONLINE_TIMEOUT', (60 * 30)); // In seconds (60sec x 60min = 1/2 Hour)
//define('MMRPG_SETTINGS_ONLINE_TIMEOUT', (60 * 9)); // In seconds (60sec x 9min = 9 Minutes)
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
define('MMRPG_SETTINGS_LEVEL_MIN', 1); // Prevent the level from going below base one
define('MMRPG_SETTINGS_LEVEL_MAX', 100); // Prevent the level from going above one hundred
define('MMRPG_SETTINGS_EXPERIENCE_MIN', 1000); // The minimum required experience for a new level
define('MMRPG_SETTINGS_EXPERIENCE_MOD', 0.1); // Slightly more experience is required for each new level
define('MMRPG_SETTINGS_STATS_MIN', 0); // Prevent the stats from going under zero
define('MMRPG_SETTINGS_STATS_MAX', 999999); // Ensure the stats to not break the display limit
define('MMRPG_SETTINGS_STATS_LEVELBOOST', 0.050); // Each level up boosts all stats by 5% of base value
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
  $robot_boost = MMRPG_SETTINGS_STATS_ROBOTMAX;
  return round(($temp_base + $level_boost) * $robot_boost);
}
// Calculate the minimum player boost for a given robot stat, requires base stat and current robot level
function MMRPG_SETTINGS_STATS_GET_PLAYERMIN($base, $level){
  $temp_base = 0;
  return round($temp_base);
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


// -- CHALLENGE SETTINGS -- //

// Define the global variables that determine target turn values
define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 1); // Target number of battle turns per each opposing support mecha
define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 3); // Target number of battle turns per each opposing robot master
define('MMRPG_SETTINGS_BATTLETURNS_PERBOSS',  6); // Target number of battle turns per each opposing fortress boss

// Define the global variables that determine target robot values
define('MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA', 0.5); // Target number of player robots per each opposing support mecha
define('MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT', 1.0); // Target number of player robots per each opposing robot master
define('MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS',  2.0); // Target number of player robots per each opposing fortress boss

// Define the global variables that determine battle point values
define('MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA',  10); // Battle point reward base per each opposing support mecha
define('MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT', 100); // Battle point reward base per each opposing robot master
define('MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS', 1000); // Battle point reward base per each opposing fortress boss

// Define the global variables that determine battle zenny values
define('MMRPG_SETTINGS_BATTLEZENNY_PERMECHA',  6); // Battle zenny reward base per each opposing support mecha
define('MMRPG_SETTINGS_BATTLEZENNY_PERROBOT', 12); // Battle zenny reward base per each opposing robot master
define('MMRPG_SETTINGS_BATTLEZENNY_PERBOSS', 48); // Battle zenny reward base per each opposing fortress boss

 // Define the global variables that determine opponent stat boosts
define('MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST', 600); // The bonus stat seed for target robot masters in battle

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

?>
