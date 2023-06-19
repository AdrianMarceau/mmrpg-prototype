<?php

// Include the TOP file
require_once('top.php');

// If the user is not logged in, don't allow them here
if (!rpg_game::is_user()){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'frames/login.php');
    exit();
}

// Collect the game's session token
$session_token = mmrpg_game_token();

// Pull in necessary indexes in case we need them later
if (!isset($mmrpg_index_players) || empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

// Restore any dropped items to their owners if able to
mmrpg_prototype_restore_dropped_items();

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['BATTLES_CHAIN'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ROBOTS_PRELOAD'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['ITEMS_DROPPED'] = array();
$_SESSION['SKILLS'] = array();
$_SESSION['PROTOTYPE_TEMP'] = array();

// Collect the prototype start link if provided
$prototype_start_link = !empty($_GET['start']) ? $_GET['start'] : 'home';

// Define the arrays for holding potential prototype messages
$prototype_window_event_canvas = array();
$prototype_window_event_messages = array();

// Check if a reset request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset'){

    // Collect a reference to the user object
    $this_user = $_SESSION[$session_token]['USER'];

    // Reset the game session and reload the page
    //$db->log_queries = true;
    if (!empty($_REQUEST['full_reset'])
        && $_REQUEST['full_reset'] == 'true'){
        mmrpg_reset_game_session(true, $this_user['userid']);
    } else {
        mmrpg_reset_game_session();
    }
    //$db->log_queries = false;

    // Update the appropriate session variables
    $_SESSION[$session_token]['USER'] = $this_user;

    // Load the save file into memory and overwrite the session
    mmrpg_save_game_session();

    // DEBUG DEBUG DEBUG

    //header('Location: prototype.php');
    unset($db);
    exit('success');

}
// Check if a reset request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset-missions' && !empty($_REQUEST['player'])){

    // Reset the appropriate session variables
    if (!empty($mmrpg_index_players[$_REQUEST['player']])){
        $temp_session_key = $_REQUEST['player'].'_target-robot-omega_prototype';
        $_SESSION[$session_token]['values']['battle_complete'][$_REQUEST['player']] = array();
        $_SESSION[$session_token]['values']['battle_failure'][$_REQUEST['player']] = array();
        $_SESSION[$session_token]['values'][$temp_session_key] = array();
    }

    // Load the save file into memory and overwrite the session
    mmrpg_save_game_session();

    //header('Location: prototype.php');
    unset($db);
    exit('success');

}

// Check if a exit request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'exit'){

    // Exit the game and enter demo mode
    rpg_game::exit_session();

    // Exit on success
    unset($db);
    exit('success');

}


// Cache the currently online players
if (!isset($_SESSION['LEADERBOARD']['online_timestamp'])
    || (time() - $_SESSION['LEADERBOARD']['online_timestamp']) > 1){ // 600sec = 10min
    $_SESSION['LEADERBOARD']['online_players'] = mmrpg_prototype_leaderboard_online();
    $_SESSION['LEADERBOARD']['online_timestamp'] = time();
}


// Require the prototype data file
require_once('prototype/include.php');



/*
 * PASSWORD PROCESSING
 */

// Collect the game flags for easier password processing
$temp_flags = !empty($_SESSION[$session_token]['flags']) ? $_SESSION[$session_token]['flags'] : array();

// Filter out the password flags for easier looping
$temp_password_flags = array();
if (!empty($temp_flags)){
    foreach ($temp_flags AS $flag_token => $flag_value){
        if (strstr($flag_token, '_password_')){
            $temp_password_flags[$flag_token] = $flag_value;
        }
    }
}

//die('wtf1-'.time());
//die('$temp_flags = <pre>'.print_r($temp_flags, true).'</pre>');

// Only proceed if there are actually flags to check
$is_admin = in_array($_SERVER['REMOTE_ADDR'], $dev_whitelist) ? true : false;
if ($is_admin && !empty($temp_password_flags)){

    // PLAYERS / ABILITIES
    $this_player_index = $mmrpg_index_players;

    // Collect the robot index for calculation purposes
    $robot_fields = rpg_robot::get_index_fields(true);
    $this_robot_index = $db->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

    // Collect the ability index for calculation purposes
    $ability_fields = rpg_ability::get_index_fields(true);
    $this_ability_index = $db->get_array_list("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

    // DEBUG PLAYERS / ABILITIES
    foreach ($this_player_index AS $player_token => $player_info){
        //$player_info = rpg_player::parse_index_info($player_info);
        $player_string = str_replace('-', '', $player_token);
        $player_pass = strlen($player_string);

        // ABILITY UNLOCKS
        foreach ($this_ability_index AS $ability_token => $ability_info){
            $ability_info = rpg_ability::parse_index_info($ability_info);
            $ability_string = str_replace('-', '', $ability_token);
            $flag_token = $player_string.'_password_ability'.$ability_string.$player_pass;
            if ($ability_token != 'ability' && !empty($temp_password_flags[$flag_token])){
                // Unlock the requested ability
                if (!mmrpg_prototype_ability_unlocked($player_token, false, $ability_token)){
                    mmrpg_game_unlock_ability($player_info, false, $ability_info, true);
                }
                // Unset this flag's value from the session
                unset($_SESSION[$session_token]['flags'][$flag_token]);
                // And now redirect to the same page
                header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
                exit();

            }
        }

        // ROBOT UNLOCKS
        foreach ($this_robot_index AS $robot_token => $robot_info){
            $robot_info = rpg_robot::parse_index_info($robot_info);
            $robot_string = str_replace('-', '', $robot_token);
            $flag_token = $player_string.'_password_robot'.$robot_string.$player_pass;
            if ($robot_token != 'robot' && !empty($temp_password_flags[$flag_token])){
                // Unlock the requested robot
                if (!mmrpg_prototype_robot_unlocked(false, $robot_token)){
                    mmrpg_game_unlock_robot($player_info, $robot_info, true, true);
                }
                // Unset this flag's value from the session
                unset($_SESSION[$session_token]['flags'][$flag_token]);
                // And now redirect to the same page
                header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
                exit();
            }

        }

    }

    // Unset temporary indexes
    unset($this_robot_index, $this_ability_index);

}

// Include the prototype awards file to check stuff
require(MMRPG_CONFIG_ROOTDIR.'prototype/awards.php');


// If possible, attempt to save the game to the session
if (rpg_game::is_user()){
    $old_points = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
    mmrpg_prototype_refresh_battle_points();
    mmrpg_save_game_session();
    $new_points = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
    if ($old_points != $new_points){
        header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
        exit();
    }
}


// If there is an ENDLESS ATTACK MODE savestate in the waveboard, load it now
if (mmrpg_prototype_item_unlocked('wily-program')){
    $challenge_mode_savestate = $db->get_value("SELECT
        challenge_wave_savestate
        FROM mmrpg_challenges_waveboard
        WHERE user_id = {$this_userid}
        AND challenge_wave_savestate IS NOT NULL
        AND challenge_wave_savestate <> ''
        ;", 'challenge_wave_savestate');
    if (!empty($challenge_mode_savestate)){
        //echo('<pre>$challenge_mode_savestate = '.print_r($challenge_mode_savestate, true).'</pre>'.PHP_EOL.PHP_EOL);
        $challenge_mode_savestate = json_decode($challenge_mode_savestate, true);
        //echo('<pre>$challenge_mode_savestate = '.print_r($challenge_mode_savestate, true).'</pre>'.PHP_EOL.PHP_EOL);
        if (!empty($challenge_mode_savestate['BATTLES_CHAIN'])
            && !empty($challenge_mode_savestate['ROBOTS_PRELOAD'])
            && !empty($challenge_mode_savestate['NEXT_MISSION'])){
            // Load the saved battle chain and robot preload data into session
            $_SESSION['BATTLES_CHAIN'] = $challenge_mode_savestate['BATTLES_CHAIN'];
            $_SESSION['ROBOTS_PRELOAD'] = $challenge_mode_savestate['ROBOTS_PRELOAD'];
            // Generate the URL for the next mission with saved data and redirect
            $next_mission_data = $challenge_mode_savestate['NEXT_MISSION'];
            $next_mission_href = 'battle.php?wap='.($flag_wap ? 'true' : 'false');
            $next_mission_href .= '&this_battle_id='.$next_mission_data['this_battle_id'];
            $next_mission_href .= '&this_battle_token='.$next_mission_data['this_battle_token'];
            $next_mission_href .= '&this_player_id='.$next_mission_data['this_player_id'];
            $next_mission_href .= '&this_player_token='.$next_mission_data['this_player_token'];
            $next_mission_href .= '&this_player_robots='.$next_mission_data['this_player_robots'];
            $next_mission_href .= '&flag_skip_fadein=true';
            //echo('<pre>$next_mission_href = '.print_r($next_mission_href, true).'</pre>'.PHP_EOL.PHP_EOL);
            // Generate the first ENDLESS ATTACK MODE mission and append it to the list
            $next_mission_number = count($_SESSION['BATTLES_CHAIN']) + 1;
            $this_prototype_data = array();
            $this_prototype_data['this_player_token'] = $next_mission_data['this_player_token'];
            $this_prototype_data['this_current_chapter'] = '8';
            $this_prototype_data['battle_phase'] = 4;
            $temp_battle_sigma = rpg_mission_endless::generate_endless_mission($this_prototype_data, $next_mission_number);
            rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);
            //echo('<pre>$temp_battle_sigma = '.print_r($temp_battle_sigma, true).'</pre>'.PHP_EOL.PHP_EOL);
            // Redirect to the mission URL now that everything is loaded and set up
            header('Location: '.$next_mission_href);
            exit();
        }
    }
}

// Check to see if the prototype is "complete" for display purposes
$temp_prototype_complete = mmrpg_prototype_complete();

// Define tooltips for the game options that appear in the main menu and surrounding UI
$this_menu_tooltips = array();
$this_menu_tooltips['home'] = '&laquo; Home Menu &raquo; &lt;br /&gt;Select your mission from the home menu and prepare for battle! Complete missions in fewer turns to earn more zenny!';
$this_menu_tooltips['shop'] = '&laquo; Item Shop &raquo; &lt;br /&gt;Trade in your extra inventory for zenny in the shop and then put your earnings towards new items, new abilities, and new battle fields.';
$this_menu_tooltips['players'] = '&laquo; Player Editor &raquo; &lt;br /&gt;Review detailed stats about your player characters and reconfigure chapter two battle fields to generate new field and fusion stars.';
$this_menu_tooltips['robots'] = '&laquo; Robot Editor &raquo; &lt;br /&gt;Review detailed stats about your battle robots, equip them with new abilities, and transfer them to other players in your save file.';
$this_menu_tooltips['database'] = '&laquo; Robot Database &raquo; &lt;br /&gt;A comprehensive list of all robots encountered in battle so far including their name and basic details. Scanning robots adds their stats and weaknesses to the database and unlocking them adds a complete list of their level-up abilities.';
$this_menu_tooltips['items'] = '&laquo; Item Inventory &raquo; &lt;br /&gt;View your inventory of collected items thus far, including quantities, descriptions, and images.';
$this_menu_tooltips['abilities'] = '&laquo; Ability Arsenal &raquo; &lt;br /&gt;View your archive of abilities unlocked so far, including types, power, descriptions, and images.';
$this_menu_tooltips['stars'] = '&laquo; Star Collection &raquo; &lt;br /&gt;A detailed list of all the Field and Fusion Stars collected on your journey so far. Collect many different stars to advance in the prototype!';
$this_menu_tooltips['save'] = '&laquo; Game Settings &raquo; &lt;br /&gt;Update your game settings and profile options including username, password, profile colour, and more.';
$this_menu_tooltips['leaderboard'] = '&laquo; Battle Points Leaderboard &raquo; &lt;br /&gt;Live leaderboards ranking all players by their total Battle Point scores from highest to lowest. Keep an eye on your Battle Points by checking the top-right of the main menu and try to work your way up to the first page!';

// Generate a list of data-index values based on predefined menu option order
$temp_data_index = 0;
$this_menu_indexes = array();
foreach ($this_menu_tooltips AS $token => $text){
    $this_menu_indexes[$token] = $temp_data_index++;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Prototype Menu | Last Updated <?= mmrpg_print_cache_date() ?></title>

<base href="<?=MMRPG_CONFIG_ROOTURL?>" />

<meta name="robots" content="noindex,nofollow" />

<meta name="format-detection" content="telephone=no" />

<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">

<link type="text/css" href="styles/reset.css" rel="stylesheet" />

<style type="text/css"> html, body { background-color: #262626; } </style>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/solid.css" integrity="sha384-+0VIRx+yz1WBcCTXBkVQYIBVNEFH1eP6Zknm16roZCyeNg2maWEpk/l/KsyFKs7G" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/fontawesome.css" integrity="sha384-jLuaxTTBR42U2qJ/pm4JRouHkEDHkVqH0T1nyQXn1mZ7Snycpf6Rl25VBNthU4z0" crossorigin="anonymous">

<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />

<meta name="format-detection" content="telephone=no" />
<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon-2k19_72x72.png" />
<? /* <meta name="viewport" content="user-scalable=yes, width=768, height=1004"> */ ?>
<meta name="viewport" content="user-scalable=yes, width=device-width, min-width=768, initial-scale=1">

</head>
<body id="mmrpg" class="prototype <?= 'env_'.MMRPG_CONFIG_SERVER_ENV ?>">

<div id="prototype" class="hidden">

    <div class="banner">
        <div class="sprite background banner_background" style="background-image: url(images/menus/menu-banner_this-battle-select.png);">&nbsp;</div>
        <div class="sprite foreground banner_foreground banner_dynamic" style="background-image: url(images/menus/prototype-banners_title-screen_01.gif?<?=MMRPG_CONFIG_CACHE_DATE?>); background-position: center -10px;">&nbsp;</div>
        <div class="sprite credits banner_credits"><h1>Mega Man RPG Prototype | Browser-Based Battle Simulator</h1></div>
        <div class="sprite overlay overlay_hidden banner_overlay">&nbsp;</div>

        <div class="title">Mega Man RPG Prototype</div>

        <?
        // Check to see if a Rogue Star is currently in orbit
        $this_rogue_star = mmrpg_prototype_get_current_rogue_star();
        if (!empty($this_rogue_star)){
            $star_type = $this_rogue_star['star_type'];
            $star_name = ucfirst($star_type);
            $now_time = time();
            $star_from_time = strtotime($this_rogue_star['star_from_date'].'T'.$this_rogue_star['star_from_date_time']);
            $star_to_time = strtotime($this_rogue_star['star_to_date'].'T'.$this_rogue_star['star_to_date_time']);
            $star_time_duration = $star_to_time - $star_from_time;
            $star_time_elapsed = $now_time - $star_from_time;
            $star_time_elapsed_percent = ($star_time_elapsed / $star_time_duration) * 100;
            $star_time_remaining = $star_time_duration - $star_time_elapsed;
            $star_position_right = (100 - $star_time_elapsed_percent) + 1;
            $star_minutes_left = ($star_time_remaining / 60);
            $star_hours_left = ($star_minutes_left / 60);
            $star_tooltip = '&raquo; Rogue Star Event! &laquo; || A '.$star_name.'-type Rogue Star has appeared! This star grants +'.$this_rogue_star['star_power'].' '.$star_name.'-type Starforce for a limited time. Take advantage of its power before it\'s gone! ';
            if ($star_hours_left >= 1){ $star_tooltip .= 'You have less than '.($star_hours_left > 1 ? ceil($star_hours_left).' hours' : '1 hour').' remaining! '; }
            elseif ($star_hours_left < 1){ $star_tooltip .= 'You have only '.($star_minutes_left > 1 ? ceil($star_minutes_left).' minutes' : '1 minute').' remaining! ';  }
            ?>
            <div class="sprite rogue_star"
                data-star-type="<?= $star_type ?>"
                data-from-date="<?= $this_rogue_star['star_from_date'] ?>"
                data-from-date-time="<?= $this_rogue_star['star_from_date_time'] ?>"
                data-to-date="<?= $this_rogue_star['star_to_date'] ?>"
                data-to-date-time="<?= $this_rogue_star['star_to_date_time'] ?>"
                data-star-power="<?= $this_rogue_star['star_power'] ?>"
                data-tooltip="<?= $star_tooltip ?>"
                data-tooltip-type="type_<?= $star_type ?>">
                <div class="wrap">
                    <div class="label">
                        <div class="name type_empty">Rogue Star!</div>
                        <div class="effect type_<?= $star_type ?>"><?= ucfirst($star_type) ?> +<?= $this_rogue_star['star_power'] ?></div>
                    </div>
                    <div class="sprite track type_<?= $star_type ?>"></div>
                    <div class="sprite trail type_<?= $star_type ?>" style="right: <?= $star_position_right ?>%;"></div>
                    <div class="sprite ruler"></div>
                    <div class="sprite star" style="background-image: url(images/items/fusion-star_<?= $star_type ?>/sprite_right_40x40.png); right: <?= $star_position_right ?>%; right: calc(<?= $star_position_right ?>% - 20px);"></div>
                </div>
            </div>
            <?
        }
        ?>

        <?
        // Collect the current points, zenny, and star counts to determine if overflow is needed
        $battle_points_count = isset($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
        $battle_zenny_count = isset($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
        $battle_stars_count = isset($_SESSION[$session_token]['values']['battle_stars']) ? count($_SESSION[$session_token]['values']['battle_stars']) : 0;
        $battle_points_rank = !empty($this_boardinfo['board_rank']) ? $this_boardinfo['board_rank'] : 0;
        $has_points_overflow = $battle_points_count >= 999999999999 ? true : false;
        $has_zenny_overflow = $battle_zenny_count >= 999999999 ? true : false;
        // If the user has collected any points, turn the points counter into a leaderboard link
        $battle_points_container_attrs = array();
        if ($battle_points_count > 0 && $battle_points_rank > 0){
            $battle_points_container_attrs[] = 'data-step="leaderboard"';
            $battle_points_container_attrs[] = 'data-index="'.$this_menu_indexes['leaderboard'].'"';
            $battle_points_container_attrs[] = 'data-source="frames/leaderboard.php"';
            $battle_points_container_attrs[] = 'data-music="misc/leader-board"';
            $battle_points_container_attrs[] = 'data-tooltip="'.$this_menu_tooltips['leaderboard'].'"';
            $battle_points_container_attrs[] = 'data-tooltip-type="field_type field_type_'.MMRPG_SETTINGS_CURRENT_FIELDTYPE.'"';
        }
        $battle_points_container_attrs = implode(' ', $battle_points_container_attrs);
        ?>
        <div class="points field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?> <?= $has_points_overflow || $has_zenny_overflow ? 'overflow' : '' ?>" <?= $battle_points_container_attrs ?>>
            <div class="wrapper">
                <label class="label">Battle Points</label>
                <span class="amount">
                    <span class="value"><?= number_format($battle_points_count, 0, '.', ',') ?></span>
                    <? if(!empty($this_boardinfo['board_rank'])): ?>
                        <span class="pipe">|</span>
                        <span class="place"><?= mmrpg_number_suffix($this_boardinfo['board_rank']) ?></span>
                    <? endif; ?>
                </span>
            </div>
        </div>
        <div class="subpoints field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?> <?= $has_zenny_overflow ? 'overflow' : '' ?>">
            <div class="wrapper">
                <span class="amount zenny">
                    <?= (!empty($battle_zenny_count) ? number_format($battle_zenny_count, 0, '.', ',') : 0).'&#438;' ?>
                </span>
                <? if (!empty($_SESSION[$session_token]['values']['battle_stars'])){ ?>
                    <span class="pipe">|</span>
                    <span class="amount stars"><?=
                        '<span class="num">'.number_format($battle_stars_count, 0, '.', ',').'</span>'.
                        '<i class="fa fas fa-star"></i>'
                        ?></span>
                <? } ?>
                <? if (mmrpg_prototype_item_unlocked('omega-seed')){ ?>
                    <span class="pipe">|</span>
                    <span class="amount omega">
                        <i class="fa fas fa-greek-omega"></i>
                    </span>
                <? } ?>
            </div>
        </div>

        <div class="options options_userinfo field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" data-step="file_save" data-index="<?= $this_menu_indexes['save'] ?>" data-source="frames/settings.php" data-music="misc/file-menu" data-tooltip="<?= $this_menu_tooltips['save'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <div class="wrapper">
                <? $temp_user_name = !empty($this_userinfo['user_name_public']) && !empty($this_userinfo['user_flag_postpublic']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name']; ?>
                <div class="info info_username">
                    <label><?= $temp_user_name ?></label>
                </div>
                <div class="config">
                    <i class="fa fas fa-cog"></i>
                </div>
            </div>
            <?
            // Define the avatar class and path variables
            $temp_avatar_path = !empty($_SESSION[$session_token]['USER']['imagepath']) ? $_SESSION[$session_token]['USER']['imagepath'] : 'robots/mega-man/40';
            list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
            $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_00';
            $temp_sprite_offset = $temp_avatar_size == 80 ? 'margin-left: -20px; margin-top: -40px; ' : '';
            $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $temp_shadow_path = 'images/'.$temp_avatar_kind.'_shadows/'.preg_replace('/_(.*?)$/i', '', $temp_avatar_token).'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
            ?>
            <div class="sprite_wrapper">
                <span class="sprite base sprite_40x40"><span class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>); <?= $temp_sprite_offset ?>"></span></span>
                <span class="sprite shadow sprite_40x40"><span class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>); <?= $temp_sprite_offset ?>"></span></span>
            </div>
        </div>

        <?
        // Check if the prototype has been completed before continuing
        $temp_prototype_complete = mmrpg_prototype_complete();
        ?>
        <div class="options options_fullmenu field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <div class="wrapper">

                <a class="link link_home link_active" data-step="<?= $unlock_count_players == 1 ? 2 : 1 ?>" data-index="<?= $this_menu_indexes['home'] ?>" data-music="misc/<?= $unlock_count_players == 1 ? 'stage-select-dr-light' : 'player-select' ?>" data-tooltip="<?= $this_menu_tooltips['home'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                    <i class="fa fas fa-home"></i>
                    <label>home</label>
                </a>
                <? if (mmrpg_prototype_item_unlocked('auto-link')
                    || mmrpg_prototype_item_unlocked('reggae-link')
                    || mmrpg_prototype_item_unlocked('kalinka-link')): ?>
                    <span class="pipe">|</span>
                    <a class="link link_shop" data-step="shop" data-index="<?= $this_menu_indexes['shop'] ?>" data-source="frames/shop.php" data-music="misc/shop-music" data-tooltip="<?= $this_menu_tooltips['shop'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-shopping-cart"></i>
                        <label>shop</label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_players_unlocked() > 0): ?>
                    <span class="pipe">|</span>
                    <a class="link link_players" data-step="edit_players" data-index="<?= $this_menu_indexes['players'] ?>" data-source="frames/edit_players.php?action=players" data-music="misc/player-editor" data-tooltip="<?= $this_menu_tooltips['players'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-user"></i>
                        <label><?= mmrpg_prototype_players_unlocked() > 1 ? 'players' : 'player' ?></label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_battles_complete('dr-light') >= 1): ?>
                    <span class="pipe">|</span>
                    <a class="link link_robots" data-step="edit_robots" data-index="<?= $this_menu_indexes['robots'] ?>" data-source="frames/edit_robots.php?action=robots" data-music="misc/robot-editor" data-tooltip="<?= $this_menu_tooltips['robots'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-robot"></i>
                        <label>robots</label>
                    </a>
                <? endif; ?>
                <span class="pipe">|</span>
                <a class="link link_data" data-step="database" data-index="<?= $this_menu_indexes['database'] ?>" data-source="frames/database.php" data-music="misc/data-base" data-tooltip="<?= $this_menu_tooltips['database'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                    <i class="fa fas fa-book"></i>
                    <label>database</label>
                </a>
                <? if (mmrpg_prototype_items_unlocked() > 0): ?>
                    <span class="pipe">|</span>
                    <a class="link link_items" data-step="items" data-index="<?= $this_menu_indexes['items'] ?>" data-source="frames/items.php" data-music="misc/item-viewer" data-tooltip="<?= $this_menu_tooltips['items'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-briefcase"></i>
                        <label>items</label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_abilities_unlocked() > 2): ?>
                    <span class="pipe">|</span>
                    <a class="link link_abilities" data-step="abilities" data-index="<?= $this_menu_indexes['abilities'] ?>" data-source="frames/abilities.php" data-music="misc/item-viewer" data-tooltip="<?= $this_menu_tooltips['abilities'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-fire-alt"></i>
                        <label>abilities</label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_stars_unlocked() > 0): ?>
                    <span class="pipe">|</span>
                    <a class="link link_stars" data-step="stars" data-index="<?= $this_menu_indexes['stars'] ?>" data-source="frames/starforce.php" data-music="misc/star-force" data-tooltip="<?= $this_menu_tooltips['stars'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-star"></i>
                        <label>stars</label>
                    </a>
                <? endif; ?>

            </div>
        </div>

    </div>

    <div class="menu select_this_player" data-step="1" data-title="Player Select (<?= !empty($_SESSION[$session_token]['DEMO']) || $unlock_count_players == 1 ? '1 Player' : $unlock_count_players.' Players' ?>)" data-select="this_player_token">
        <span class="header block_1 header_types type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <span class="count">Player Select (<?= !empty($_SESSION[$session_token]['DEMO']) || $unlock_count_players == 1 ? '1 Player' : $unlock_count_players.' Players' ?>)</span>
            <?/*<span class="reload">&#8634;</span>*/?>
        </span>
        <?
        // Require the prototype players display file
        require_once(MMRPG_CONFIG_ROOTDIR.'prototype/players.php');

        ?>
    </div>

    <div class="menu menu_hide select_this_battle" data-step="2" data-title="Battle Select" data-select="this_battle_token">
        <span class="header block_1 header_types type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <span class="count"><?= !empty($_SESSION[$session_token]['DEMO']) ? 'Mega Man RPG Prototype' : 'Mission Select' ?></span>
        </span>
        <?

        // Require the prototype campaign chapters display file
        require_once(MMRPG_CONFIG_ROOTDIR.'prototype/chapters.php');

        // If we're NOT in demo mode, maybe add a back button
        if (empty($_SESSION[$session_token]['DEMO'])){
            // Print out the back button for going back to player select
            if ($unlock_count_players > 1){
                echo '<a class="option option_back block_1" data-back="1">&#9668; Back</a>'."\n";
            }
        }

        ?>
    </div>

    <?
        /*
         * DEMO ROBOT SELECT
         */
        if (!empty($_SESSION[$session_token]['DEMO'])){

            // Only show robot select if the player has more than two robots
            if (mmrpg_prototype_robots_unlocked('dr-light') > 3){

                // Print out the opening tags for the robot select container
                echo '<div class="menu menu_hide select_this_player_robots" data-step="3" data-limit="" data-title="Robot Select" data-select="this_player_robots">'."\n";
                echo '<span class="header block_1 header_types type_none"><span class="count">Robot Select</span></span>'."\n";

                // Require the prototype robots display file
                require_once(MMRPG_CONFIG_ROOTDIR.'prototype/robots.php');

                // Print out the back button for going back to player select
                echo '<a class="option option_back block_1" data-back="2">&#9668; Back</a>'."\n";

                // Print out the closing tags for the robot select container
                echo '</div>'."\n";

            }

        }
        /*
         * NORMAL ROBOT SELECT
         */
        else {

            // Print out the opening tags for the robot select container
            echo '<div class="menu menu_hide select_this_player_robots" data-step="3" data-limit="" data-title="Robot Select" data-select="this_player_robots">'."\n";
            echo '<span class="header block_1 header_types type_'.MMRPG_SETTINGS_CURRENT_FIELDTYPE.'"><span class="count">Robot Select</span></span>'."\n";

            // Require the prototype robots display file
            require_once(MMRPG_CONFIG_ROOTDIR.'prototype/robots.php');

            // Print out the back button for going back to player select
            echo '<a class="option option_back block_1" data-back="2">&#9668; Back</a>'."\n";

            // Print out the closing tags for the robot select container
            echo '</div>'."\n";

        }
    ?>

    <div class="menu menu_hide menu_file_save" data-step="file_save" data-source="frames/settings.php"></div>

    <div class="menu menu_hide menu_items" data-step="items" data-source="frames/items.php"></div>

    <div class="menu menu_hide menu_abilites" data-step="abilities" data-source="frames/abilities.php"></div>

    <div class="menu menu_hide menu_shop" data-step="shop" data-source="frames/shop.php"></div>

    <div class="menu menu_hide menu_edit_robots" data-step="edit_robots" data-source="frames/edit_robots.php?action=robots"></div>

    <div class="menu menu_hide menu_edit_players" data-step="edit_players" data-source="frames/edit_players.php?action=players"></div>

    <div class="menu menu_hide menu_leaderboard" data-step="leaderboard" data-source="frames/leaderboard.php"></div>

    <div class="menu menu_hide menu_database" data-step="database" data-source="frames/database.php"></div>

    <div class="menu menu_hide menu_stars" data-step="stars" data-source="frames/starforce.php"></div>

    <div class="menu menu_hide menu_help" data-step="help" data-source="frames/help.php"></div>

    <div class="menu menu_hide menu_loading" data-step="loading" style="min-height: 600px;">
        <div class="option_wrapper option_wrapper_noscroll" style="color: white; font-weight: bold; line-height: 150px; letter-spacing: 4px; opacity: 0.75; margin-right: 0; background-color: rgba(0, 0, 0, 0.10); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; overflow: hidden; min-height: 600px; ">
            <div style="line-height: 40px; margin-top: 50px;">
                <span class="sprite sprite_40x40 sprite_40x40_left_00 " style="display: inline-block; position: static; background-image: url(images/assets/robot-loader_mega-man.gif); ">&nbsp;</span><br />
                <span class="label" style="display: inline-block;">loading</span>
            </div>
        </div>
    </div>

</div>

<div id="falloff" class="falloff_bottom"></div>
<?
?>
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">

// Update relevent game settings and flags
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
gameSettings.fadeIn = true;
gameSettings.demo = false;
gameSettings.passwordUnlocked = 0;
gameSettings.startLink = '<?= $prototype_start_link ?>';
gameSettings.windowEventsCanvas = [];
gameSettings.windowEventsMessages = [];
gameSettings.totalPlayerOptions = <?= $unlock_count_players ?>;
gameSettings.prototypeBannerKey = 0;
gameSettings.prototypeBanners = ['prototype-banners_title-screen_01.gif'];

// Define any preset menu selections
battleOptions['this_user_id'] = <?= $this_userid ?>;
<? if (!empty($_SESSION[$session_token]['battle_settings']['this_player_token'])){ ?>
    battleOptions['this_player_id'] = <?= $mmrpg_index_players[$_SESSION[$session_token]['battle_settings']['this_player_token']]['player_id'] ?>;
    battleOptions['this_player_token'] = '<?= $_SESSION[$session_token]['battle_settings']['this_player_token'] ?>';
<? } elseif($unlock_count_players < 2){ ?>
    battleOptions['this_player_id'] = <?= $mmrpg_index_players['dr-light']['player_id'] ?>;
    battleOptions['this_player_token'] = 'dr-light';
<? } ?>
// Create the document ready events
$(document).ready(function(){

    <? if($prototype_start_link == 'home' && empty($_SESSION[$session_token]['battle_settings']['this_player_token'])): ?>
        // Start playing the title screen music
        parent.mmrpg_music_load('misc/player-select', true, false);
    <? elseif($prototype_start_link != 'home'): ?>
        <? if(!empty($_SESSION[$session_token]['battle_settings']['this_player_token'])): ?>
            // Start playing the appropriate stage select music
            parent.mmrpg_music_load('misc/stage-select-<?= $_SESSION[$session_token]['battle_settings']['this_player_token'] ?>', true, false);
        <? else: ?>
            // Start playing the title screen music
            parent.mmrpg_music_load('misc/player-select', true, false);
        <? endif; ?>
    <? endif; ?>

    <?
    // If there were any prototype window events created, display them
    if (!empty($_SESSION[$session_token]['EVENTS'])){
        foreach ($_SESSION[$session_token]['EVENTS'] AS $temp_key => $temp_event){
            $temp_canvas_markup = str_replace('"', '\"', $temp_event['canvas_markup']);
            $temp_messages_markup =  str_replace('"', '\"', $temp_event['console_markup']);
            echo 'gameSettings.windowEventsCanvas.push("'.$temp_canvas_markup.'");'."\n";
            echo 'gameSettings.windowEventsMessages.push("'.$temp_messages_markup.'");'."\n";
        }
    }
    ?>

    <? if (rpg_game::is_user()){ ?>
        // The user is logged-in so let's keep the session alive
        mmrpg_keep_session_alive(<?= rpg_game::get_userid() ?>);
    <? } ?>

});
</script>
<?

// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');

?>
</body>
</html>
<?

// If there were any events in the session, automatically add remove them from the session
if (!empty($_SESSION[$session_token]['EVENTS'])){ $_SESSION[$session_token]['EVENTS'] = array(); }
// Unset the database variable
unset($db);

?>