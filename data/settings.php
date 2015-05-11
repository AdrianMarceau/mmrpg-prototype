<?php

// Define the core domain, locale, timezone, error reporting and cache settings
@preg_match('#^([-~a-z0-9\.]+)#i', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']), $THIS_DOMAIN);
define('MMRPG_CONFIG_DOMAIN', (isset($THIS_DOMAIN[0]) ? $THIS_DOMAIN[0] : false));
unset($THIS_DOMAIN);

// Define whether or not we're being viewed by an admin
$is_admin = in_array($_SERVER['REMOTE_ADDR'], explode(',', MMRPG_CONFIG_ADMIN_LIST)) ? true : false;
define('MMRPG_CONFIG_ADMIN_MODE', $is_admin);

// Define the cache date and path on this system
define('MMRPG_CONFIG_CACHE_DATE', '20140830-02');
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

?>