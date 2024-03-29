<?php

// Define the cache timestamp now if not already done so
if (!defined('MMRPG_CONFIG_CACHE_DATE')){
    $cache_info = $db->table_exists('mmrpg_config') ? $db->get_array_list("SELECT config_name, config_value FROM mmrpg_config WHERE config_group = 'global' && config_name IN ('cache_date', 'cache_time');", 'config_name') : false;
    //echo('<pre>$cache_info = '.print_r($cache_info, true).'</pre>');
    if (!empty($cache_info)){ define('MMRPG_CONFIG_CACHE_DATE', $cache_info['cache_date']['config_value'].'-'.$cache_info['cache_time']['config_value']); }
    elseif (defined('MMRPG_CONFIG_CACHE_DATE_FALLBACK')){ define('MMRPG_CONFIG_CACHE_DATE', MMRPG_CONFIG_CACHE_DATE_FALLBACK); }
    else { define('MMRPG_CONFIG_CACHE_DATE', date('Ymd').'-0000'); }
    //echo('MMRPG_CONFIG_CACHE_DATE = '. MMRPG_CONFIG_CACHE_DATE);
    //exit();
    if (empty($_SESSION['CACHE'])
        || empty($_SESSION['CACHE']['DATE'])
        || $_SESSION['CACHE']['DATE'] !== MMRPG_CONFIG_CACHE_DATE){
        $_SESSION['CACHE'] = array();
        $_SESSION['CACHE']['DATE'] = MMRPG_CONFIG_CACHE_DATE;
    }
}

// Define the core domain, locale, timezone, error reporting and cache settings
@preg_match('#^([-~a-z0-9\.]+)#i', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']), $THIS_DOMAIN);
define('MMRPG_CONFIG_DOMAIN', (isset($THIS_DOMAIN[0]) ? $THIS_DOMAIN[0] : false));
unset($THIS_DOMAIN);

// Define the base cache paths for the system
define('MMRPG_CONFIG_CACHE_PATH', MMRPG_CONFIG_ROOTDIR.'.cache/');

// Define the directory where all content functions/images/data are stored
define('MMRPG_CONFIG_CONTENT_PATH', MMRPG_CONFIG_ROOTDIR.'content/');
define('MMRPG_CONFIG_SQL_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'.sql/');
define('MMRPG_CONFIG_TYPES_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'types/');
define('MMRPG_CONFIG_PLAYERS_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'players/');
define('MMRPG_CONFIG_ROBOTS_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'robots/');
define('MMRPG_CONFIG_ABILITIES_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'abilities/');
define('MMRPG_CONFIG_ITEMS_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'items/');
define('MMRPG_CONFIG_SKILLS_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'skills/');
define('MMRPG_CONFIG_FIELDS_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'fields/');
define('MMRPG_CONFIG_BATTLES_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'battles/');
define('MMRPG_CONFIG_PAGES_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'pages/');
define('MMRPG_CONFIG_STARS_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'stars/');
define('MMRPG_CONFIG_CHALLENGES_CONTENT_PATH', MMRPG_CONFIG_CONTENT_PATH.'challenges/');

// Define the global settings for external libraries, scripts, and styles
define('MMRPG_CONFIG_JQUERY_VERSION', '1.6.1');

// Define the global timeout variables for online and new status
define('MMRPG_SETTINGS_ONLINE_TIMEOUT', (60 * 30)); // In seconds (60sec x 30min = 1/2 Hour)
define('MMRPG_SETTINGS_ACTIVE_TIMEOUT', (60 * 60 * 24 * 90)); // In seconds (60sec x 60min x 24hr x 90days)
define('MMRPG_SETTINGS_LEGACY_TIMEOUT', (60 * 60 * 24 * 365)); // In seconds (60sec x 60min x 24hr x 365days)
define('MMRPG_SETTINGS_UPDATE_TIMEOUT', (60 * 60 * 24 * 1)); // In seconds (60sec x 60min x 24hr x 1days)

// Define the global leaderboard values for various sorting methods
define('MMRPG_SETTINGS_DEFAULT_LEADERBOARD_METRIC', 'battle_points');
define('MMRPG_SETTINGS_CURRENT_LEADERBOARD_METRIC', (date('m-d') === '04-01' ? 'battle_zenny' : 'battle_points'));

// Define the global password salt and omega seed strings if not already set
if (!defined('MMRPG_SETTINGS_PASSWORD_SALT')){ define('MMRPG_SETTINGS_PASSWORD_SALT', 'mmrpg'); }
if (!defined('MMRPG_SETTINGS_OMEGA_SEED')){ define('MMRPG_SETTINGS_OMEGA_SEED', 'mmrpg'); }

// Define the global guest ID to prevent confusion
define('MMRPG_SETTINGS_GUEST_ID', -1); // Make it a negative so it doesn't mess with auto-increment

// Define the global target player ID to prevent overlap
define('MMRPG_SETTINGS_TARGET_PLAYERID', 999999); // Doubt we'll ever get this many users

// Define the global max and min limits for field multipliers
define('MMRPG_SETTINGS_MULTIPLIER_MIN', 0.1); // Prevent the multiplier from reaching absolute zero
define('MMRPG_SETTINGS_MULTIPLIER_MAX', 9.9); // Ensure the multiplier stays under the limit of ten

// Define the global max and min limits for robot stat values
define('MMRPG_SETTINGS_STATS_MIN', 0); // Prevent the multiplier from reaching absolute zero
define('MMRPG_SETTINGS_STATS_MAX', 9999); // Ensure the multiplier stays under the limit of ten
define('MMRPG_SETTINGS_STATS_BONUS_MAX', 1); // Ensure bonuses do not go exceed X times the base value
define('MMRPG_SETTINGS_STATS_MOD_MIN', -5); // Prevent the multiplier from reaching too low
define('MMRPG_SETTINGS_STATS_MOD_MAX', 5); // Prevent the multiplier from reaching too high

// Define the global values for robot recharge stat values
define('MMRPG_SETTINGS_RECHARGE_WEAPONS', 1); // Recharge one unit of weapon energy each turn
define('MMRPG_SETTINGS_RECHARGE_ATTACK', 1); // Recharge one unit of attack power each turn
define('MMRPG_SETTINGS_RECHARGE_DEFENSE', 1); // Recharge one unit of defense power each turn
define('MMRPG_SETTINGS_RECHARGE_SPEED', 1); // Recharge one unit of speed power each turn

// Define the global values for item maximums
define('MMRPG_SETTINGS_SHARDS_MAXQUANTITY', 4); // Define the number of shards required to create a new core
define('MMRPG_SETTINGS_ITEMS_MAXQUANTITY', 99); // Define the max quantity allowed for normal held items
define('MMRPG_SETTINGS_CORES_MAXQUANTITY', 99); // Define the max quantity allowed for elemental robot cores

// Define the global values for starforce related values
define('MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT', 10);  // Base starforce boost percentage for each field star or half of fusion star
define('MMRPG_SETTINGS_STARFORCE_FIELDCOUNT', 32);
define('MMRPG_SETTINGS_STARFORCE_FUSIONCOUNT', 992);
define('MMRPG_SETTINGS_STARFORCE_STARTOTAL', 1024);

// Define the global variables for the total number of abilities allowed per robot
define('MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX', 8); // The maximum number of abilities allowed per robot

// Define the global variables for the total number of robots allower per player
define('MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MIN', 1); // The minimum number of robots required for battle per side
define('MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX', 8); // The maximum number of robots allowed for battle per side

// Define the global multiplier for battle points per level
define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL0', 2500); // The point rate per fortress boss level for normal battles
define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL', 1000); // The point rate per robot master level for normal battles
define('MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2', 100); // The point rate per support mecha level for normal battles
define('MMRPG_SETTINGS_BATTLETURNS_PERBOSS', 9); // The point rate per target robot master for normal battles
define('MMRPG_SETTINGS_BATTLETURNS_PERROBOT', 3); // The point rate per target robot master for normal battles
define('MMRPG_SETTINGS_BATTLETURNS_PERMECHA', 1); // The point rate per target support mecha for normal battles
define('MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER', 0.05); // The conversion rate for battle points into zenny rewards
define('MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER', 2.0); // The point rate per robot level multiplier for player battles
define('MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER', 0.5); // The point rate per target robot multiplier for player battles
define('MMRPG_SETTINGS_BATTLEPOINTS_PERPLAYER', 10000); // The point rate per wave completed in endless attack mode
define('MMRPG_SETTINGS_BATTLEPOINTS_PERWAVE', 10000); // The point rate per wave completed in endless attack mode

// Define the global default values for game multipliers
define('MMRPG_SETTINGS_WEAKNESS_MULTIPLIER', 2.0); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_RESISTANCE_MULTIPLIER', 0.5); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_AFFINITY_MULTIPLIER', -1.0); // Core boosted abilites should recive a 50% boost
define('MMRPG_SETTINGS_IMMUNITY_MULTIPLIER', 0.0); // Core boosted abilites should recive a 50% boost

// Define the global values for core boosts and bonuses
define('MMRPG_SETTINGS_COREBOOST_MULTIPLIER', 1.50); // Core matched abilites should recive a 50% damage/recovery boost
define('MMRPG_SETTINGS_SUBCOREBOOST_MULTIPLIER', 1.25); // Sub-Core matched abilites should recive a 25% damage/recovery boost
define('MMRPG_SETTINGS_OMEGACOREBOOST_MULTIPLIER', 1.10); // Omega-Core matched abilites should recive a 10% damage/recovery boost
define('MMRPG_SETTINGS_COREBONUS_MULTIPLIER', 0.50); // Core matched abilites should recive a 50% weapon energy reduction
define('MMRPG_SETTINGS_SUBCOREBONUS_MULTIPLIER', 0.75); // Sub-Core matched abilites should recive a 25% weapon energy reduction
define('MMRPG_SETTINGS_NATIVEBONUS_MULTIPLIER', 0.50); // Level-up abilites should recive a 50% weapon energy reduction
define('MMRPG_SETTINGS_MECHABONUS_MULTIPLIER', 0.50); // support mechas should receive a 50% weapon energy reduction

// Define the global values for shop prices and multipliers
define('MMRPG_SETTINGS_SHOP_ABILITY_PRICE', 1500);  // use as (PRICE * ENERGY)
define('MMRPG_SETTINGS_SHOP_ROBOT_PRICE', 32000);
define('MMRPG_SETTINGS_SHOP_FIELD_PRICE', 48000);

// Define the global comment size limit for characters
define('MMRPG_SETTINGS_COMMENT_MINLENGTH', 10); // Prevent spam comments
define('MMRPG_SETTINGS_COMMENT_MAXLENGTH', 5000); // Prevent wordy comments

// Define the global comment size limit for characters
define('MMRPG_SETTINGS_THREADNAME_MINLENGTH', 10); // Prevent spam threadnames
define('MMRPG_SETTINGS_THREADNAME_MAXLENGTH', 60); // Prevent wordy threadnames
define('MMRPG_SETTINGS_DISCUSSION_MINLENGTH', 100); // Prevent spam discussions
define('MMRPG_SETTINGS_DISCUSSION_MAXLENGTH', 20000); // Prevent wordy discussions

// Define the global frame index for robot / ability / item sprites
define('MMRPG_SETTINGS_PLAYER_FRAMEINDEX', 'base/taunt/victory/defeat/command/damage/base2/*/*/*/*'); // Define the sprite index
define('MMRPG_SETTINGS_ROBOT_FRAMEINDEX', 'base/taunt/victory/defeat/shoot/throw/summon/slide/defend/damage/base2'); // Define the sprite index
define('MMRPG_SETTINGS_ABILITY_FRAMEINDEX', '00/01/02/03/04/05/06/07/08/09'); // Define the sprite index
define('MMRPG_SETTINGS_ITEM_FRAMEINDEX', '00/01/02/03/04/05/06/07/08/09'); // Define the sprite index
define('MMRPG_SETTINGS_SKILL_FRAMEINDEX', '00/01/02/03/04/05/06/07/08/09'); // Define the sprite index
define('MMRPG_SETTINGS_ATTACHMENT_FRAMEINDEX', '00/01/02/03/04/05/06/07/08/09'); // Define the sprite index

// Define the global ability target index for looping through in editors/database
define('MMRPG_SETTINGS_ABILITY_TARGETINDEX', '{
    "auto":"Auto",
    "select_target":"Select Target (Enemy Side)",
    "select_this":"Select Target (Player Side)",
    "select_this_ally":"Select Ally (Player Side)",
    "select_this_disabled":"Select Disabled (Player Side)"
    }');

// Define the global battle point requirements for posting
define('MMRPG_SETTINGS_THREAD_MINPOINTS', 5000); // Prevent spam comments
define('MMRPG_SETTINGS_POST_MINPOINTS', 1000); // Prevent wordy comments

// Define the global thread display limit for community
define('MMRPG_SETTINGS_THREADS_RECENT', 6); // How many threads should be listed on the home page
define('MMRPG_SETTINGS_POSTS_PERPAGE', 20); // How many discussion threads should be displayed per page
define('MMRPG_SETTINGS_THREADS_PERPAGE', 50); // How many comment posts should be displayed per page

// Define the global values for robot unlocks in each campaign
define('MMRPG_SETTINGS_GAMESTORY1_STARTLEVEL', 1); // The level the first player's hero robot starts at
define('MMRPG_SETTINGS_GAMESTORY2_STARTLEVEL', 19); // The level the second player's hero robot starts at
define('MMRPG_SETTINGS_GAMESTORY3_STARTLEVEL', 39); // The level the third player's hero robot starts at

// Define the global counters for missions in each campaign
define('MMRPG_SETTINGS_CHAPTER0_MISSIONS', 0);  // Void
define('MMRPG_SETTINGS_CHAPTER1_MISSIONS', 3);  // Intro (Met + Joe + Trill)
define('MMRPG_SETTINGS_CHAPTER2_MISSIONS', 9);  // Masters (x8 Masters + Doc)
define('MMRPG_SETTINGS_CHAPTER3_MISSIONS', 3);  // Rivals (Hero + Killer + King)
define('MMRPG_SETTINGS_CHAPTER4_MISSIONS', 5);  // Fusions (x4 Fusions + Genesis)
define('MMRPG_SETTINGS_CHAPTER5_MISSIONS', 3);  // Finals (Terra + Dark + Slur)

// Define the global counters for missions in each campaign
define('MMRPG_SETTINGS_CHAPTER1_MISSIONCOUNT', MMRPG_SETTINGS_CHAPTER1_MISSIONS);                                         // 1   (Intro)
define('MMRPG_SETTINGS_CHAPTER2_MISSIONCOUNT', MMRPG_SETTINGS_CHAPTER1_MISSIONCOUNT + MMRPG_SETTINGS_CHAPTER2_MISSIONS);  // 9   (Masters)
define('MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT', MMRPG_SETTINGS_CHAPTER2_MISSIONCOUNT + MMRPG_SETTINGS_CHAPTER3_MISSIONS);  // 10  (Rivals)
define('MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT', MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT + MMRPG_SETTINGS_CHAPTER4_MISSIONS);  // 14  (Fusions)
define('MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT', MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT + MMRPG_SETTINGS_CHAPTER5_MISSIONS);  // 17  (Finals)

// Define the global star counters for missions in each campaign
define('MMRPG_SETTINGS_CHAPTER1_STARLOCK', 0);                                                // 0   (Intro)
define('MMRPG_SETTINGS_CHAPTER2_STARLOCK', MMRPG_SETTINGS_CHAPTER1_STARLOCK + 0);             // 0   (Masters)
define('MMRPG_SETTINGS_CHAPTER3_STARLOCK', MMRPG_SETTINGS_CHAPTER2_STARLOCK + 0);             // 0   (Rivals)
define('MMRPG_SETTINGS_CHAPTER4_STARLOCK', MMRPG_SETTINGS_CHAPTER3_STARLOCK + (3 * 8));       // 24  (Fusions)
define('MMRPG_SETTINGS_CHAPTER5_STARLOCK', MMRPG_SETTINGS_CHAPTER4_STARLOCK + (3 * 4));       // 36  (Finals)

// Define the global constants related to audio defaults
define('MMRPG_SETTINGS_AUDIODEFAULT_MASTERVOLUME', 0.7); // Not quite full blast
define('MMRPG_SETTINGS_AUDIODEFAULT_MUSICVOLUME', 0.4); // Slightly lower than the sfx
define('MMRPG_SETTINGS_AUDIODEFAULT_EFFECTVOLUME', 0.6); // Slightly higher than the music

// Back-up definition in case COPPA is not defined
if (!defined('MMRPG_CONFIG_COPPA_PERMISSIONS')){
    define('MMRPG_CONFIG_COPPA_PERMISSIONS', '');
}

// Back-up definition in case SERVER ENV are not defined
if (!defined('MMRPG_CONFIG_SERVER_ENV')){
    define('MMRPG_CONFIG_SERVER_ENV', 'local');
}
// Back-up definition in case SERVER USER are not defined
if (!defined('MMRPG_CONFIG_SERVER_USER')){
    define('MMRPG_CONFIG_SERVER_USER', 'mmrpg2');
}

// Define other server-related variables given the current SERVER ENV
if (MMRPG_CONFIG_SERVER_ENV === 'local'){
    define('MMRPG_CONFIG_PULL_DEV_DATA_FROM', false);
    define('MMRPG_CONFIG_PULL_LIVE_DATA_FROM', 'prod');
} elseif (MMRPG_CONFIG_SERVER_ENV === 'dev'){
    define('MMRPG_CONFIG_PULL_DEV_DATA_FROM', false);
    define('MMRPG_CONFIG_PULL_LIVE_DATA_FROM', 'prod');
} elseif (MMRPG_CONFIG_SERVER_ENV === 'stage'){
    define('MMRPG_CONFIG_PULL_DEV_DATA_FROM', 'dev');
    define('MMRPG_CONFIG_PULL_LIVE_DATA_FROM', 'prod');
} elseif (MMRPG_CONFIG_SERVER_ENV === 'prod'){
    define('MMRPG_CONFIG_PULL_DEV_DATA_FROM', 'dev');
    define('MMRPG_CONFIG_PULL_LIVE_DATA_FROM', false);
}

// Define the last save timestamp now if not already done so
if (!defined('MMRPG_CONFIG_LAST_SAVE_DATE')){
    $guest_id = MMRPG_SETTINGS_GUEST_ID;
    $last_save_time = $db->table_exists('mmrpg_saves') ? $db->get_value("SELECT MAX(save_date_modified) AS last_save_time FROM mmrpg_saves WHERE user_id <> {$guest_id};", 'last_save_time') : false;
    //echo('<pre>$last_save_time = '.print_r($last_save_time, true).'</pre>');
    if (!empty($last_save_time)){ define('MMRPG_CONFIG_LAST_SAVE_DATE', $last_save_time); }
    else { define('MMRPG_CONFIG_LAST_SAVE_DATE', time()); }
    //echo('MMRPG_CONFIG_LAST_SAVE_DATE = '. MMRPG_CONFIG_LAST_SAVE_DATE);
    //exit();
}

// Define the meaning of image editor IDs (changes from 'user_id' to 'contributor_id' post 2020 migration)
if (!defined('MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD')){
    if (!empty($_SESSION['CACHE']['MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD'])){
        $editor_id_meaning = $_SESSION['CACHE']['MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD'];
    } else {
        $editor_id_meaning = $db->table_exists('mmrpg_config') ? $db->get_value("SELECT config_value FROM mmrpg_config WHERE config_group = 'global' && config_name = 'image_editor_id_field';", 'config_value') : false;
        $_SESSION['CACHE']['MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD'] = $editor_id_meaning;
    }
    if (!empty($editor_id_meaning)){ define('MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD', $editor_id_meaning); }
    else { define('MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD', 'user_id'); }
}

?>