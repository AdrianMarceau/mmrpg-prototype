<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_COMPLETE', true);
define('MMRPG_REMOTE_SKIP_FAILURE', true);
define('MMRPG_REMOTE_SKIP_SETTINGS', true);
define('MMRPG_REMOTE_SKIP_ITEMS', true);
define('MMRPG_REMOTE_SKIP_STARS', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME') ? true : false;
if (isset($_GET['edit']) && $_GET['edit'] == 'false'){ $global_allow_editing = false; }
$global_frame_source = !empty($_GET['source']) ? trim($_GET['source']) : 'prototype';

// Collect the number of completed battles for each player
$unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
$battles_complete_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
$unlock_flag_wily = mmrpg_prototype_player_unlocked('dr-wily');
$battles_complete_wily = $unlock_flag_wily ? mmrpg_prototype_battles_complete('dr-wily') : 0;
$unlock_flag_cossack = mmrpg_prototype_player_unlocked('dr-cossack');
$battles_complete_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_complete('dr-cossack') : 0;
$prototype_complete_flag = mmrpg_prototype_complete();

// Count the number of players unlocked
$unlock_count_players = 0;
if ($unlock_flag_light){ $unlock_count_players++; }
if ($unlock_flag_wily){ $unlock_count_players++; }
if ($unlock_flag_cossack){ $unlock_count_players++; }

// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];

// Require the appropriate database files
define('DATA_DATABASE_SHOW_MECHAS', true);
define('DATA_DATABASE_SHOW_BOSSES', true);
define('DATA_DATABASE_SHOW_CACHE', true);
define('DATA_DATABASE_SHOW_HIDDEN', true);
//require_once('../database/include.php');
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/players.php');
require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
require(MMRPG_CONFIG_ROOTDIR.'database/mechas.php');
require(MMRPG_CONFIG_ROOTDIR.'database/bosses.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/fields.php');
$mmrpg_database_abilities = rpg_ability::get_index(true);
$mmrpg_database_fields = rpg_field::get_index(true);
//require(MMRPG_CONFIG_ROOTDIR.'database/items.php');

// Merge the robots and mechas
$mmrpg_database_robots = array_merge($mmrpg_database_robots, $mmrpg_database_mechas, $mmrpg_database_bosses);

// Filter out any robots that are not yet fightable given flag values
$mmrpg_database_robots = array_filter($mmrpg_database_robots, function($robot_info){
    if (empty($robot_info['robot_flag_published'])){ return false; }
    elseif (empty($robot_info['robot_flag_complete'])){ return false; }
    elseif (empty($robot_info['robot_flag_fightable'])){ return false; }
    return true;
    });

// Define a quick function for getting group token for a given robot/mecha/boss
function temp_get_group_token($robot_info){
    static $first_page_robots = array(
        'mega-man', 'bass', 'proto-man', 'roll', 'disco', 'rhythm',
        'bond-man', 'fake-man', 'pulse-man', 'rock',
        'met', 'sniper-joe', 'skeleton-joe', 'crystal-joe',
        'rush', 'beat', 'tango', 'eddie', 'reggae', 'mariachi'
        );
    static $hard_coded_group_tokens = array(
        'time-man' => 'MM1', 'oil-man' => 'MM1',
        'flutter-fly' => 'MM1', 'beetle-borg' => 'MM1'
        );
    if ($robot_info['robot_class'] === 'boss'){
        if (in_array($robot_info['robot_game'], array('MMV', 'MMEXE'))){ $group_token = 'MMBOSS2'; }
        else { $group_token = 'MMBOSS1'; }
    } elseif (isset($hard_coded_group_tokens[$robot_info['robot_token']])){
        $group_token = $hard_coded_group_tokens[$robot_info['robot_token']];
    } elseif (in_array($robot_info['robot_token'], $first_page_robots)){
        $group_token = 'MM0';
    } else {
        $group_token = $robot_info['robot_game'];
    }
    return $group_token;
}

// Preloop through all of the robots in the database session and count the games
$session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
$database_game_counters = array();
foreach ($session_robot_database AS $temp_token => $temp_info){
    if (!isset($mmrpg_database_robots[$temp_token])){ continue; }
    $temp_info = $mmrpg_database_robots[$temp_token];
    $group_token = temp_get_group_token($temp_info);
    if (!isset($database_game_counters[$group_token])){ $database_game_counters[$group_token] = array($temp_token); }
    elseif (!in_array($temp_token, $database_game_counters[$group_token])){ $database_game_counters[$group_token][] = $temp_token; }
    else { continue; }
}

// Define the index of allowable robots to appear in the database
$allowed_database_robots = array();
$allowed_database_robots[] = 'mega-man';
$temp_skip_games = array();
foreach ($mmrpg_database_robots AS $temp_token => $temp_info){
    $group_token = temp_get_group_token($temp_info);
    if (in_array($group_token, $temp_skip_games)){ continue; }
    $allowed_database_robots[] = $temp_token;
}
$allowed_database_robots_count = !empty($allowed_database_robots) ? count($allowed_database_robots) : 0;

// Define the index of allowable robots to appear in the database
$visible_database_robots = array();
$temp_skip_games = array();
foreach ($mmrpg_database_robots AS $temp_token => $temp_info){
    $group_token = temp_get_group_token($temp_info);
    if (in_array($group_token, $temp_skip_games)){ continue; }
    $visible_database_robots[] = $temp_token;
}
$visible_database_robots_count = !empty($visible_database_robots) ? count($visible_database_robots) : 0;

// Remove unallowed robots from the database
foreach ($mmrpg_database_robots AS $temp_key => $temp_info){
    if (!in_array($temp_key, $allowed_database_robots)){
        unset($mmrpg_database_robots[$temp_key]);
    }
}

// Count the robots groups for each page
$database_page_groups = array();
$database_page_groups[0] = array('MM0');
$database_page_groups[1] = array('MM1');
$database_page_groups[2] = array('MM2');
$database_page_groups[3] = array('MM3');
$database_page_groups[4] = array('MM4');
$database_page_groups[5] = array('MM5');
$database_page_groups[6] = array('MM6');
$database_page_groups[7] = array('MM7');
$database_page_groups[8] = array('MM8', 'RnF');
$database_page_groups[9] = array('MM9');
$database_page_groups[10] = array('MM10');
$database_page_groups[11] = array('MM11');
$database_page_groups[12] = array('MMBOSS1', 'MMI', 'MMII', 'MMIII', 'MMIV', 'MMV', 'MMWW');
$database_page_groups[13] = array('MMBOSS2', 'MMRPG', 'MMRPGP', 'MMRPGPR');

// Count the robots for each page
$database_page_counters = array();
foreach ($database_page_groups AS $page_key => $group_array){
    $database_page_counters[$page_key] = false;
    foreach ($group_array AS $group_token){
        if (!empty($database_game_counters[$group_token])){
            $database_page_counters[$page_key] = true;
            continue;
        }
    }
}

// Collect the database markup from the session if set, otherwise generate it
$this_cache_stamp = MMRPG_CONFIG_CACHE_DATE.'_'.$allowed_database_robots_count;
$this_database_markup = '';
if (true){

    // Prepare the output buffer
    ob_start();

    // Determine the token for the very first robot in the database
    $temp_robot_tokens = array_values($mmrpg_database_robots);
    $first_robot_token = array_shift($temp_robot_tokens);
    $first_robot_token = $first_robot_token['robot_token'];
    unset($temp_robot_tokens);

    // Define the header/base counters for the database
    $global_robots_counters = array();
    $global_robots_counters['total'] = 0;
    $global_robots_counters['registered'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
    $global_robots_counters['encountered'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
    $global_robots_counters['scanned'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
    $global_robots_counters['summoned'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
    $global_robots_counters['unlocked'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
    $global_robots_counters['fightable'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);

    // Define a function for looping through the robots and counting/updating them
    //$temp_process_debug = array();
    function temp_process_robots(&$mmrpg_database_robots, &$database_game_counters, &$database_page_groups, &$global_robots_counters, $session_token){

        // Loop through all of the robots, one by one, formatting their info
        static $confirmed_unlocked_robots;
        if (empty($confirmed_unlocked_robots)){
            $confirmed_unlocked_robots = array_keys(mmrpg_prototype_robots_unlocked_index());
            //error_log('$confirmed_unlocked_robots = '.print_r($confirmed_unlocked_robots, true));
            }
        foreach($mmrpg_database_robots AS $robot_key => &$robot_info){

            // Update the global game counters
            $robot_token = $robot_info['robot_token'];
            if (!isset($database_game_counters[$robot_info['robot_game']])){ $database_game_counters[$robot_info['robot_game']] = array($robot_token); }
            elseif (!in_array($robot_token, $database_game_counters[$robot_info['robot_game']])){ $database_game_counters[$robot_info['robot_game']][] = $robot_token; }

            // Update and/or define the encountered, scanned, summoned, and unlocked flags
            //die('dance <pre>'.print_r($_SESSION[$session_token]['values']['robot_database'], true).'</pre>');
            $robot_is_unlocked = $robot_info['robot_class'] === 'master' && in_array($robot_token, $confirmed_unlocked_robots) ? true : false;
            if ($robot_is_unlocked){ $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_unlocked'] = true; }
            if (!isset($robot_info['robot_visible'])){ $robot_info['robot_visible'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]) ? true : false; }
            if (!isset($robot_info['robot_encountered'])){ $robot_info['robot_encountered'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_encountered']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_encountered'] : 0; }
            if (!isset($robot_info['robot_scanned'])){ $robot_info['robot_scanned'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_scanned']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_scanned'] : 0; }
            if (!isset($robot_info['robot_summoned'])){ $robot_info['robot_summoned'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_summoned']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_summoned'] : 0; }
            if (!isset($robot_info['robot_unlocked'])){ $robot_info['robot_unlocked'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_unlocked']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_unlocked'] : 0; }
            if (!isset($robot_info['robot_defeated'])){ $robot_info['robot_defeated'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_defeated']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_defeated'] : 0; }

            /*
            // If this robot was summoned but not enountered, let's fix that
            if (empty($robot_info['robot_encountered'])
                && !empty($robot_info['robot_summoned'])){
                $robot_info['robot_encountered'] = $robot_info['robot_summoned'];
                $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_encountered'] = $robot_info['robot_encountered'];
            }
            */

            // Define the page token based on this robot's game of origin
            if (!isset($robot_info['robot_page_token'])){
                $temp_this_page_token = '?';
                $robot_group_token = temp_get_group_token($robot_info);
                foreach ($database_page_groups AS $page_key => $group_array){
                    if (in_array($robot_group_token, $group_array)){ $temp_this_page_token = $page_key; break; }
                    if (in_array($robot_token, $group_array)){ $temp_this_page_token = $page_key; break; }
                    else { continue; }
                }
                $robot_info['robot_page_token'] = $temp_this_page_token;
            }

            //global $temp_process_debug;
            //if (!isset($temp_process_debug[$robot_info['robot_page_token']])){ $temp_process_debug[$robot_info['robot_page_token']] = array(); }
            //$temp_process_debug[$robot_info['robot_page_token']][] = $robot_token;

            // Increment the global robots counters
            $global_robots_counters['total']++;
            if ($robot_info['robot_unlocked'] || $robot_info['robot_summoned'] || $robot_info['robot_encountered']){ $global_robots_counters['registered']['total']++; $global_robots_counters['registered'][$robot_info['robot_class']]++; }
            if ($robot_info['robot_encountered']){ $global_robots_counters['encountered']['total']++; $global_robots_counters['encountered'][$robot_info['robot_class']]++; }
            if ($robot_info['robot_scanned']){ $global_robots_counters['scanned']['total']++; $global_robots_counters['scanned'][$robot_info['robot_class']]++; }
            if ($robot_info['robot_unlocked']){ $global_robots_counters['unlocked']['total']++; $global_robots_counters['unlocked'][$robot_info['robot_class']]++; }
            elseif ($robot_info['robot_summoned']){ $global_robots_counters['summoned']['total']++; $global_robots_counters['summoned'][$robot_info['robot_class']]++; }
            if ($robot_info['robot_flag_fightable']){ $global_robots_counters['fightable']['total']++; $global_robots_counters['fightable'][$robot_info['robot_class']]++; }

        }
        // Return true on success
        return true;
    }
    // Now to call upon the temp function, passing in appropriate variables
    temp_process_robots($mmrpg_database_robots, $database_game_counters, $database_page_groups, $global_robots_counters, $session_token);
    //error_log('$temp_process_debug = '.print_r($temp_process_debug, true));

    //error_log('<pre>$global_robots_counters : '.print_r($global_robots_counters, true).'</pre>');

    // Start generating the database markup
    ?>

    <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="count">
            <i class="fa fas fa-compact-disc"></i>
            Robot Database
            <span class="progress">(
                <?= $global_robots_counters['registered']['total'] == 1 ? '<span data-click-tooltip="1 Robot Registered">1</span>' : '<span data-click-tooltip="'.$global_robots_counters['registered']['total'].' Robots Registered">'.$global_robots_counters['registered']['total'].'</span>' ?>
                / <?= $global_robots_counters['total'] == 1 ? '<span data-click-tooltip="1 Robot">1 Robot Total</span>' : '<span data-click-tooltip="'.$global_robots_counters['total'].' Robots Total">'.$global_robots_counters['total'].' Robots</span>' ?>
            )</span>
        </span>
    </span>

    <?
    // DEBUG DEBUG
    //die('<pre>$global_robots_counters : '.print_r($global_robots_counters, true).'</pre>');
    ?>

    <table style="width: 100%;">
        <colgroup><col width="165" /><col /></colgroup>
        <tr>
        <td style="width: 165px; vertical-align: top;">

            <div id="canvas" style="">
                <?
                // START THE DATABASE CANVAS BUFFER
                ob_start();
                ?>
                    <strong class="wrapper_header wrapper_subheader">Pages</strong>
                    <div id="robot_games" class="wrapper_links">
                        <?
                        // Print out page links for all the pages that are enabled and placeholder otherwise
                        $temp_current_page_page_key = !empty($_SESSION[$session_token]['battle_settings']['current_database_page_key']) ? $_SESSION[$session_token]['battle_settings']['current_database_page_key'] : 0;
                        $temp_current_page_robot_key = !empty($_SESSION[$session_token]['battle_settings']['current_database_robot_token']) ? $_SESSION[$session_token]['battle_settings']['current_database_robot_token'] : false;
                        foreach ($database_page_counters AS $page_key => $page_unlocked){
                            $temp_is_current = $page_key == $temp_current_page_page_key ? true : false;
                            if ($page_unlocked){ echo '<a class="game_link '.($temp_is_current ? 'game_link_active ' : '').'" href="#" data-game="'.$page_key.'">'.$page_key.'</a>'."\n"; }
                            else { echo '<a class="game_link game_link_disabled">?</a>'."\n"; }
                        }
                        ?>
                    </div>
                    <strong class="wrapper_header wrapper_header_masters">Robot Masters</strong>
                    <div class="wrapper wrapper_robots wrapper_robots_masters" data-select="robots" data-kind="masters">
                        <?
                        // Loop through all of the robots, one by one, displaying their buttons
                        $key_counter = 0;
                        foreach($mmrpg_database_robots AS $robot_key => $robot_info){
                            // Skip if not the correct robot class
                            if ($robot_info['robot_class'] !== 'master'){ continue; }
                            $temp_robot_name_span = '<span>'.$robot_info['robot_name'].'</span>';
                            $temp_robot_type_class = 'robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none');
                            $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                            $temp_robot_image_path = 'images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                            $temp_robot_mug_sprite = '<span class="sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot" style="background-image: url('.$temp_robot_image_path.');"></span>';
                            // If this robot is visible, display normally
                            if ($robot_info['robot_visible'] && in_array($robot_info['robot_token'], $visible_database_robots)){
                                $temp_robot_link_class = 'sprite sprite_robot sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == $first_robot_token ? 'sprite_robot_current ' : '').' '.$temp_robot_type_class;
                                if (empty($robot_info['robot_flag_unlockable'])){ $temp_robot_link_class .= ' not-unlockable'; }
                                $robot_complete_markup = '';
                                if ($robot_info['robot_unlocked']){ $robot_complete_markup .= '<span class="complete '.$temp_robot_type_class.'">&#10022;</span>'; }
                                echo '<a data-token="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" data-size="'.$robot_info['robot_image_size'].'" data-maybe-title="'.$robot_info['robot_number'].' '.$robot_info['robot_name'].'" class="'.$temp_robot_link_class.'">'.$temp_robot_mug_sprite.$temp_robot_name_span.$robot_complete_markup.'</a>';
                            }
                            // Otherwise, show a placeholder box for later
                            else {
                                $temp_robot_link_class = 'sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active';
                                echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" data-size="'.$robot_info['robot_image_size'].'" data-click-tooltip="'.$robot_info['robot_number'].' ???" class="'.$temp_robot_link_class.'">???</a>';
                            }
                            // Increment the key counter
                            $key_counter++;
                        }
                        ?>
                    </div>
                    <strong class="wrapper_header wrapper_header_bosses">Fortress Bosses</strong>
                    <div class="wrapper wrapper_robots wrapper_robots_bosses" data-select="robots" data-kind="bosses">
                        <?
                        // Loop through all of the robots, one by one, displaying their buttons
                        //$key_counter = 0;
                        foreach($mmrpg_database_robots AS $robot_key => $robot_info){
                            // Skip if not the correct robot class
                            if ($robot_info['robot_class'] != 'boss'){ continue; }
                            $temp_robot_name_span = '<span>'.$robot_info['robot_name'].'</span>';
                            $temp_robot_type_class = 'robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none');
                            $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                            $temp_robot_image_path = 'images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                            $temp_robot_mug_sprite = '<span class="sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot" style="background-image: url('.$temp_robot_image_path.');"></span>';
                            // If this robot is visible, display normally
                            if ($robot_info['robot_visible'] && in_array($robot_info['robot_token'], $visible_database_robots)){
                                $temp_robot_link_class = 'sprite sprite_robot sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == $first_robot_token ? 'sprite_robot_current ' : '').' '.$temp_robot_type_class;
                                if (empty($robot_info['robot_flag_unlockable'])){ $temp_robot_link_class .= ' not-unlockable'; }
                                $robot_complete_markup = '';
                                if ($robot_info['robot_summoned']){ $robot_complete_markup .= '<span class="complete '.$temp_robot_type_class.'">&#10022;</span>'; }
                                echo '<a data-token="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" data-size="'.$robot_info['robot_image_size'].'" data-maybe-title="'.$robot_info['robot_number'].' '.$robot_info['robot_name'].'" class="'.$temp_robot_link_class.'">'.$temp_robot_mug_sprite.$temp_robot_name_span.$robot_complete_markup.'</a>';
                            }
                            // Otherwise, show a placeholder box for later
                            else {
                                echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" data-size="'.$robot_info['robot_image_size'].'" data-click-tooltip="'.$robot_info['robot_number'].' ???" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
                            }
                            // Increment the key counter
                            $key_counter++;
                        }
                        ?>
                    </div>
                    <strong class="wrapper_header wrapper_header_mechas">Mecha Support</strong>
                    <div class="wrapper wrapper_robots wrapper_robots_mechas" data-select="robots" data-kind="mechas">
                        <?
                        // Loop through all of the robots, one by one, displaying their buttons
                        //$key_counter = 0;
                        foreach($mmrpg_database_robots AS $robot_key => $robot_info){
                            // Skip if not the correct robot class
                            if ($robot_info['robot_class'] !== 'mecha'){ continue; }
                            $temp_robot_name_span = '<span>'.$robot_info['robot_name'].'</span>';
                            $temp_robot_type_class = 'robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none');
                            $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                            $temp_robot_image_path = 'images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                            $temp_robot_mug_sprite = '<span class="sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot" style="background-image: url('.$temp_robot_image_path.');"></span>';
                            // If this robot is visible, display normally
                            if ($robot_info['robot_visible'] && in_array($robot_info['robot_token'], $visible_database_robots)){
                                $temp_robot_link_class = 'sprite sprite_robot sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == $first_robot_token ? 'sprite_robot_current ' : '').' '.$temp_robot_type_class;
                                if (empty($robot_info['robot_flag_unlockable'])){ $temp_robot_link_class .= ' not-unlockable'; }
                                $robot_complete_markup = '';
                                if ($robot_info['robot_summoned']){ $robot_complete_markup .= '<span class="complete '.$temp_robot_type_class.'">&#10022;</span>'; }
                                echo '<a data-token="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" data-size="'.$robot_info['robot_image_size'].'" data-maybe-title="'.$robot_info['robot_number'].' '.$robot_info['robot_name'].'" class="'.$temp_robot_link_class.'">'.$temp_robot_mug_sprite.$temp_robot_name_span.$robot_complete_markup.'</a>';
                            }
                            // Otherwise, show a placeholder box for later
                            else {
                                echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" data-size="'.$robot_info['robot_image_size'].'" data-click-tooltip="'.$robot_info['robot_number'].' ???" class="sprite scaled sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
                            }
                            // Increment the key counter
                            $key_counter++;
                        }
                        ?>
                    </div>
                <?
                // COLLECT THE DATABASE CANVAS MARKUP
                $database_canvas_markup = preg_replace('/\s+/', ' ', trim(ob_get_clean()));
                ?>
            </div>

        </td>
        <td style="vertical-align: top;">

            <div id="console" class="noresize" style="height: auto;">
                <?
                // START THE DATABASE CONSOLE BUFFER
                ob_start();
                ?>
                    <div id="robots" class="wrapper">
                        <?$key_counter = 0;?>
                        <?
                        // Loop through all the robots again and display them
                        foreach($mmrpg_database_robots AS $robot_key => $robot_info){

                            // Define the default image size if empty
                            if (empty($robot_info['robot_image_size'])){ $robot_info['robot_image_size'] = 40; }

                            $core_type_class = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
                            if (!empty($robot_info['robot_core2'])){ $core_type_class .= '_'.$robot_info['robot_core2']; }

                            $data_tooltip_type = 'data-tooltip-type="robot_type robot_type_'.$core_type_class.'"';

                            $robot_is_unlockable = $robot_info['robot_class'] == 'master' && $robot_info['robot_flag_unlockable'] ? true : false;
                            $robot_is_unlocked = !empty($robot_info['robot_unlocked']);

                            $robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
                            $robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                            $robot_image_size_token = $robot_image_size.'x'.$robot_image_size;
                            $robot_image_path = 'images/robots/'.$robot_image_token.'/mug_right_'.$robot_image_size_token.'.png?'.MMRPG_CONFIG_CACHE_DATE;

                            $show_sprite_showcase = !empty($robot_info['robot_unlocked']) || !empty($robot_info['robot_encountered']) ? true : false;

                            ?>
                            <div class="event event_triple event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?> <?= $robot_is_unlockable ? 'robot_is_unlockable' : '' ?><?= $show_sprite_showcase ? ' has_sprite_showcase' : '' ?>" data-token="<?=$robot_info['robot_token']?>">
                                <div class="this_sprite sprite_left mugshot sx<?= $robot_info['robot_image_size'] ?> robot_type robot_type_<?= $core_type_class ?>">
                                    <div class="wrap">
                                        <div style="background-image: url(<?= $robot_image_path ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_image_size_token ?> sprite_<?= $robot_image_size_token ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
                                    </div>
                                </div>
                                <div class="header header_left robot_type robot_type_<?= $core_type_class ?>">
                                    <strong class="title"><?=$robot_info['robot_name']?>&#39;s Data</strong>
                                    <?
                                    if ($robot_info['robot_class'] === 'master' && $robot_info['robot_unlocked']){ echo '<span class="this_icon this_complete" '.$data_tooltip_type.' data-click-tooltip="Database Entry Complete!"><i class="fa fas fa-compact-disc"></i></span>'; }
                                    elseif ($robot_info['robot_class'] !== 'master' && $robot_info['robot_summoned']){ echo '<span class="this_icon this_complete" '.$data_tooltip_type.' data-click-tooltip="Database Entry Complete!"><i class="fa fas fa-compact-disc"></i></span>'; }
                                    ?>
                                    <? if ($robot_is_unlockable){ ?>
                                        <span class="this_icon this_unlockable" <?= $data_tooltip_type ?> data-click-tooltip="<?= $robot_is_unlocked ? 'Robot Master Unlocked!' : 'Robot Is Unlockable' ?>">
                                            <i class="unlocked fa fas <?= $robot_is_unlocked ? 'fa-robot' : 'fa-exclamation-circle' ?>"></i>
                                        </span>
                                    <? } elseif (!empty($robot_info['robot_summoned'])){ ?>
                                        <? if ($robot_info['robot_class'] === 'mecha'){ ?>
                                            <span class="this_icon this_summoned" <?= $data_tooltip_type ?> data-click-tooltip="Support Mecha Summoned!">
                                                <i class="summoned fa fas fa-ghost"></i>
                                            </span>
                                        <? } elseif ($robot_info['robot_class'] === 'master'){ ?>
                                            <span class="this_icon this_summoned" <?= $data_tooltip_type ?> data-click-tooltip="Robot Master Summoned!">
                                                <i class="summoned fa fas fa-mask"></i>
                                            </span>
                                        <? } elseif ($robot_info['robot_class'] === 'boss'){ ?>
                                            <span class="this_icon this_summoned" <?= $data_tooltip_type ?> data-click-tooltip="Fortress Boss Summoned!">
                                                <i class="summoned fa fas fa-skull"></i>
                                            </span>
                                        <? } ?>
                                    <? } ?>
                                    <? if(!empty($robot_info['robot_core'])): ?>
                                        <span class="robot_type robot_core"><?= ucfirst($robot_info['robot_core']) ?> Core</span>
                                    <? else: ?>
                                        <span class="robot_type robot_core">Neutral Core</span>
                                    <? endif; ?>
                                </div>
                                <div class="body body_left">
                                    <table class="full" style="margin-bottom: 5px;">
                                        <colgroup>
                                            <col width="185" />
                                            <col width="5" />
                                            <col width="" />
                                        </colgroup>
                                        <tbody>
                                            <tr>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Model :</label>
                                                    <span class="robot_number"><?=$robot_info['robot_number']?></span>
                                                </td>
                                                <td class="center">&nbsp;</td>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Name :</label>
                                                    <span class="robot_name robot_type">
                                                        <?=$robot_info['robot_name']?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Type :</label>
                                                    <? if(!empty($robot_info['robot_core'])): ?>
                                                        <span class="robot_name robot_type robot_type_<?=$robot_info['robot_core']?>"><?=ucfirst($robot_info['robot_core'])?> Core</span>
                                                    <? else: ?>
                                                        <span class="robot_name robot_type robot_type_none">Neutral Core</span>
                                                    <? endif; ?>
                                                </td>
                                                <td class="center">&nbsp;</td>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Class :</label>
                                                    <span class="robot_number robot_description"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Energy :</label>
                                                    <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                                                        <span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= ceil($robot_info['robot_energy'] * 0.4) ?>px;"><?= $robot_info['robot_energy'] ?></span>
                                                    <? else: ?>
                                                        <span class="robot_stat">?</span>
                                                    <? endif; ?>
                                                </td>
                                                <td class="center">&nbsp;</td>
                                                <td class="right<?= !empty($robot_info['robot_weaknesses']) ? ' has'.count($robot_info['robot_weaknesses']) : '' ?>">
                                                    <label style="display: block; float: left;">Weaknesses :</label>
                                                    <?
                                                    if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                                                        if (!empty($robot_info['robot_weaknesses'])){
                                                            $temp_string = array();
                                                            foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                                                $type_info = $mmrpg_database_types[$robot_weakness];
                                                                $type_name = $type_info['type_name'];
                                                                $type_name_responsive = '<span>'.substr($type_info['type_name'], 0, 2).'</span><span>'.substr($type_info['type_name'], 2).'</span>';
                                                                $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.$robot_weakness.'">'.$type_name_responsive.'</span>';
                                                            }
                                                            echo implode(' ', $temp_string);
                                                        } else {
                                                            echo '<span class="robot_weakness robot_type robot_type_none">None</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="robot_weakness">?</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Attack :</label>
                                                    <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                                                        <span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= ceil($robot_info['robot_attack'] * 0.4) ?>px;"><?= $robot_info['robot_attack'] ?></span>
                                                    <? else: ?>
                                                        <span class="robot_stat">?</span>
                                                    <? endif; ?>
                                                </td>
                                                <td class="center">&nbsp;</td>
                                                <td class="right<?= !empty($robot_info['robot_resistances']) ? ' has'.count($robot_info['robot_resistances']) : '' ?>">
                                                    <label style="display: block; float: left;">Resistances :</label>
                                                    <?
                                                    if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                                                        if (!empty($robot_info['robot_resistances'])){
                                                            $temp_string = array();
                                                            foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                                                $type_info = $mmrpg_database_types[$robot_resistance];
                                                                $type_name = $type_info['type_name'];
                                                                $type_name_responsive = '<span>'.substr($type_info['type_name'], 0, 2).'</span><span>'.substr($type_info['type_name'], 2).'</span>';
                                                                $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.$robot_resistance.'">'.$type_name_responsive.'</span>';
                                                            }
                                                            echo implode(' ', $temp_string);
                                                        } else {
                                                            echo '<span class="robot_resistance robot_type robot_type_none">None</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="robot_resistance">?</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td  class="right">
                                                    <label style="display: block; float: left;">Defense :</label>
                                                    <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                                                        <span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= ceil($robot_info['robot_defense'] * 0.4) ?>px;"><?= $robot_info['robot_defense'] ?></span>
                                                    <? else: ?>
                                                        <span class="robot_stat">?</span>
                                                    <? endif; ?>
                                                </td>
                                                <td class="center">&nbsp;</td>
                                                <td class="right<?= !empty($robot_info['robot_affinities']) ? ' has'.count($robot_info['robot_affinities']) : '' ?>">
                                                    <label style="display: block; float: left;">Affinities :</label>
                                                    <?
                                                    if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                                                        if (!empty($robot_info['robot_affinities'])){
                                                            $temp_string = array();
                                                            foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                                                $type_info = $mmrpg_database_types[$robot_affinity];
                                                                $type_name = $type_info['type_name'];
                                                                $type_name_responsive = '<span>'.substr($type_info['type_name'], 0, 2).'</span><span>'.substr($type_info['type_name'], 2).'</span>';
                                                                $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.$robot_affinity.'">'.$type_name_responsive.'</span>';
                                                            }
                                                            echo implode(' ', $temp_string);
                                                        } else {
                                                            echo '<span class="robot_affinity robot_type robot_type_none">None</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="robot_affinity">?</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="right">
                                                    <label style="display: block; float: left;">Speed :</label>
                                                    <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                                                        <span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= ceil($robot_info['robot_speed'] * 0.4) ?>px;"><?= $robot_info['robot_speed'] ?></span>
                                                    <? else: ?>
                                                        <span class="robot_stat">?</span>
                                                    <? endif; ?>
                                                </td>
                                                <td class="center">&nbsp;</td>
                                                <td class="right<?= !empty($robot_info['robot_immunities']) ? ' has'.count($robot_info['robot_immunities']) : '' ?>">
                                                    <label style="display: block; float: left;">Immunities :</label>
                                                    <?
                                                    if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                                                        if (!empty($robot_info['robot_immunities'])){
                                                            $temp_string = array();
                                                            foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                                                $type_info = $mmrpg_database_types[$robot_immunity];
                                                                $type_name = $type_info['type_name'];
                                                                $type_name_responsive = '<span>'.substr($type_info['type_name'], 0, 2).'</span><span>'.substr($type_info['type_name'], 2).'</span>';
                                                                $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.$robot_immunity.'">'.$type_name_responsive.'</span>';
                                                            }
                                                            echo implode(' ', $temp_string);
                                                        } else {
                                                            echo '<span class="robot_immunity robot_type robot_type_none">None</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="robot_immunity">?</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?

                                    // Collect the robot skill info if not empty
                                    $skill_info = !empty($robot_info['robot_skill']) ? rpg_robot::get_robot_skill_info($robot_info['robot_skill'], $robot_info) : array();
                                    $skill_info_type = !empty($skill_info) ? (!empty($skill_info['skill_display_type']) ? $skill_info['skill_display_type'] : 'none') : false;

                                    // Collect the robot field if not empty
                                    $temp_robot_field = '';
                                    $temp_show_robot_field = false;
                                    if (!empty($robot_info['robot_field']) && $robot_info['robot_field'] != 'field'){ $temp_robot_field = $robot_info['robot_field']; $temp_show_robot_field = true; }
                                    elseif (!empty($robot_info['robot_field2']) && $robot_info['robot_field2'] != 'field'){ $temp_robot_field = $robot_info['robot_field2']; $temp_show_robot_field = true; }
                                    if (!empty($temp_robot_field)){
                                        //echo $robot_info['robot_field'];
                                        $temp_robot_field = !empty($mmrpg_database_fields[$temp_robot_field]) ? $mmrpg_database_fields[$temp_robot_field] : array();
                                        $temp_field_title = $temp_robot_field['field_name'];
                                        $temp_field_title .= !empty($temp_robot_field['field_type']) ? ' ('.ucfirst($temp_robot_field['field_type']).' Type)' : ' (Neutral Type)';
                                        if (!empty($temp_robot_field['field_multipliers'])){
                                            $temp_field_title .= '&lt;br /&gt;';
                                            $count = 0;
                                            foreach ($temp_robot_field['field_multipliers'] AS $type => $value){
                                                if ($count > 0){ $temp_field_title .= ' | '; }
                                                $temp_field_title .= $type == 'none' ? 'Neutral' : ucfirst($type).' x '.number_format($value, 1);
                                                $count++;
                                            }
                                        }
                                    }

                                    // Generate table data for the skill and field (if applicable) and display if at least one is visible
                                    $table_data = array();
                                    if (!empty($skill_info)){
                                        ob_start();
                                        ?>
                                        <div class="wrap">
                                            <label style="display: block; float: left;">Skill :</label>
                                            <div class="skill_container">
                                                <? if(($robot_info['robot_unlocked'] || $robot_info['robot_summoned'] || $robot_info['robot_scanned'])): ?>
                                                    <span class="skill_name type type_<?= $skill_info_type ?>" data-click-tooltip="<?= htmlentities($skill_info['skill_description'], ENT_QUOTES, 'UTF-8', true) ?>">
                                                        <?= $skill_info['skill_name'] ?>
                                                    </span>
                                                <? else: ?>
                                                    <span class="skill_name type type_empty field_name">???</span>
                                                <? endif; ?>
                                            </div>
                                        </div>
                                        <?
                                        $table_data[] = ob_get_clean();
                                    }
                                    if (!empty($temp_robot_field)){
                                        ob_start();
                                        ?>
                                        <div class="wrap">
                                            <label style="display: block; float: left;">Field :</label>
                                            <div class="field_container">
                                                <? if($temp_show_robot_field && ($robot_info['robot_unlocked'] || $robot_info['robot_summoned'] || $robot_info['robot_encountered'])): ?>
                                                    <span class="ability_name ability_type ability_type_<?= !empty($temp_robot_field['field_type']) ? $temp_robot_field['field_type'] : 'none' ?> field_name" data-click-tooltip="<?= $temp_field_title ?>"><?= $temp_robot_field['field_name'] ?></span>
                                                <? else: ?>
                                                    <span class="ability_name ability_type ability_type_empty field_name">???</span>
                                                <? endif; ?>
                                            </div>
                                        </div>
                                        <?
                                        $table_data[] = ob_get_clean();
                                    }

                                    // If at least one is visible, we can display them
                                    if (!empty($table_data)){
                                        ?>
                                        <table class="full">
                                            <colgroup>
                                                <col width="100%" />
                                            </colgroup>
                                            <tbody>
                                                <tr>
                                                    <td class="right has2cols">
                                                        <? foreach ($table_data AS $key => $data){
                                                            echo(trim($data).PHP_EOL);
                                                        } ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <?
                                    }

                                    ?>
                                    <table class="full">
                                        <colgroup>
                                            <col width="100%" />
                                        </colgroup>
                                        <tbody>
                                            <tr>
                                                <td class="right">
                                                    <label style="display: block; float: left;">Abilities :</label>
                                                    <div class="ability_container">
                                                    <?
                                                    $robot_ability_rewards = $robot_info['robot_rewards']['abilities'];
                                                    foreach ($robot_ability_rewards AS $key => $this_info){
                                                        if ($this_info['token'] === 'buster-shot'){
                                                            unset($robot_ability_rewards[$key]);
                                                        }
                                                    }
                                                    $robot_database_complete = false;
                                                    if ((($robot_info['robot_class'] === 'master' && $robot_info['robot_unlocked'])
                                                        || ($robot_info['robot_class'] !== 'master' && $robot_info['robot_summoned']))){
                                                        $robot_database_complete = true;
                                                    }
                                                    if (!empty($robot_ability_rewards) && $robot_database_complete){
                                                        $temp_string = array();
                                                        $ability_key = 0;
                                                        foreach ($robot_ability_rewards AS $key => $this_info){
                                                            if (!isset($mmrpg_database_abilities[$this_info['token']])){ continue; }
                                                            $this_level = $this_info['level'];
                                                            $this_ability = $mmrpg_database_abilities[$this_info['token']];
                                                            $this_ability_token = $this_ability['ability_token'];
                                                            $this_ability_name = $this_ability['ability_name'];
                                                            $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                                            $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                                                            if (!empty($this_ability_type) && !empty($mmrpg_database_types[$this_ability_type])){ $this_ability_type = $mmrpg_database_types[$this_ability_type]['type_name'].' Type'; }
                                                            else { $this_ability_type = ''; }
                                                            $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                                                            $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                                                            $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                                                            $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                                                            //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                                                            //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                                                            //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                                                            //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                                                            //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                                                            $this_ability_title_html = str_replace(' ', '&nbsp;', $this_ability_name);
                                                            $this_ability_title_html = '<span class="level">'.($this_level > 1 ? 'Lv '.$this_level : 'Start').'</span> <span class="name">'.$this_ability_title_html.'</span>';
                                                            $this_ability_title = rpg_ability::print_editor_title_markup($robot_info, $this_ability);
                                                            $this_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_ability_title));
                                                            $this_ability_title_tooltip = htmlentities($this_ability_title, ENT_QUOTES, 'UTF-8');
                                                            $temp_string[] = '<span data-click-tooltip="'.$this_ability_title_tooltip.'" class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'">'.$this_ability_title_html.'</span>';  //.(($ability_key + 1) % 3 == 0 ? '<br />' : '');
                                                            $ability_key++;
                                                        }
                                                        echo implode(' ', $temp_string);
                                                    } elseif (!empty($robot_ability_rewards)){
                                                        echo '<span class="ability_name ability_type ability_type_empty">???</span>';
                                                    } else {
                                                        echo '<span class="robot_ability robot_type_none">None</span>';
                                                    }
                                                    ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="full">
                                        <colgroup>
                                            <col width="100%" />
                                        </colgroup>
                                        <tbody>
                                            <tr>
                                                <td class="right">
                                                    <label style="display: block; float: left;">Records :</label>
                                                    <div class="record_container">
                                                        <?
                                                        // Predefine the various record data variables so we can use and re-use them
                                                        $record_data = array();
                                                        $record_data['summoned'] = array(
                                                            'label' => ('Summoned : '.($robot_info['robot_summoned'] == 1 ? '1 Times' : number_format($robot_info['robot_summoned'], 0, '.', ',').' Times')),
                                                            'desc' => 'Number of times this robot has been summoned by the player in battle'
                                                            );
                                                        $record_data['encountered'] = array(
                                                            'label' => ('Encountered : '.($robot_info['robot_encountered'] == 1 ? '1 Times' : number_format($robot_info['robot_encountered'], 0, '.', ',').' Times')),
                                                            'desc' => 'Number of times this robot has been encountered as a target in battle'
                                                            );
                                                        $record_data['defeated'] = array(
                                                            'label' => ('Defeated : '.($robot_info['robot_defeated'] == 1 ? '1 Times' : number_format($robot_info['robot_defeated'], 0, '.', ',').' Times')),
                                                            'desc' => 'Number of times this robot has been defeated as a target in battle'
                                                            );
                                                        // Loop through and print out the record data in spans with appropriate markup
                                                        foreach ($record_data AS $record_key => $record_info){
                                                            echo '<span class="ability_name ability_type ability_empty record_name" data-click-tooltip="'.$record_info['label'].' || '.$record_info['desc'].'">'.$record_info['label'].'</span>';
                                                        }
                                                        ?>
                                                        <? /* <span class="ability_name ability_type ability_empty record_name" data-click-tooltip="Highest overkill damage inflicted on this robot as a target in battle">Overkill : <?= (isset($robot_info['max_overkill_damage']) ? number_format($robot_info['max_overkill_damage'], 0, '.', ',').' Damage' : '---') ? ></span> */ ?>
                                                        <? /*
                                                        <span class="ability_name ability_type ability_empty record_name">&nbsp;</span>
                                                        <span class="ability_name ability_type ability_empty record_name">&nbsp;</span>
                                                        <span class="ability_name ability_type ability_empty record_name">&nbsp;</span>
                                                        */ ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                                <? if ($show_sprite_showcase){ ?>
                                    <?
                                    $showcase_sprite_markup = '';
                                    $showcase_shadow_markup = '';
                                    $sprite_animation_duration = 1;
                                    if (true){
                                        $sprite_base_image = $robot_image_token;
                                        $sprite_base_size = $robot_image_size;
                                        $sprite_showcase_size = $robot_image_size; // * 2;
                                        $sprite_showcase_size_token = $sprite_showcase_size.'x'.$sprite_showcase_size;
                                        $sprite_showcase_image = 'images/robots/'.$robot_image_token.'/sprite_left_'.$sprite_showcase_size_token.'.png';
                                        $sprite_animation_duration = rpg_robot::get_css_animation_duration($robot_info);
                                        $class = 'sprite  ';
                                        $class .= 'sprite_'.$sprite_showcase_size_token.' ';
                                        $class .= 'sprite_'.$sprite_showcase_size_token.'_base ';
                                        $class .= 'sprite_size_'.$sprite_showcase_size_token.' ';
                                        $class .= 'sprite_size_'.$sprite_showcase_size_token.'_base ';
                                        $class .= 'robot_status_active robot_position_active ';
                                        $style = 'background-image: url('.$sprite_showcase_image.'?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                                        $showcase_sprite_markup = '<div class="'.$class.'" style="'.$style.'" data-image="'.$sprite_base_image.'" data-image-size="'.$sprite_showcase_size.'"></div>';
                                        $showcase_shadow_markup = $showcase_sprite_markup;
                                    }
                                    $sprite_animation_styles = 'animation-duration: '.$sprite_animation_duration.'s;';
                                    ?>
                                    <div class="sprite_showcase" data-image="<?= $sprite_base_image ?>" data-image-size="<?= $sprite_showcase_size ?>">
                                        <div class="wrapper">
                                            <div class="sprite sprite_robot sprite_40x40" style="<?= $sprite_animation_styles ?>">
                                                <?= $showcase_sprite_markup ?>
                                            </div>
                                            <? if (!empty($showcase_shadow_markup)){ ?>
                                                <div class="sprite sprite_robot sprite_40x40 is_shadow" style="<?= $sprite_animation_styles ?>">
                                                    <?= $showcase_sprite_markup ?>
                                                </div>
                                            <? } ?>
                                        </div>
                                    </div>
                                    <div class="sprite_showcase_buttons">
                                        <div class="wrapper">
                                            <?
                                            // Collect the frame index for robots then loop through and display buttons
                                            $frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
                                            foreach ($frame_index AS $frame_key => $frame_token){
                                                $frame_title = ucfirst($frame_token).' Sprite';
                                                echo('<a class="frame robot_type type '.$core_type_class.'" data-frame="'.$frame_token.'" data-frame-key="'.$frame_key.'" data-click-title="'.$frame_title.'">'.
                                                    '<span class="wrap">'.$frame_key.'</span>'.
                                                    '</a>'.PHP_EOL);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                            <?$key_counter++;?>
                        <? } ?>
                    </div>
                    <?
                    // COLLECT THE DATABASE CONSOLE MARKUP
                    $database_console_markup = preg_replace('/\s+/', ' ', trim(ob_get_clean()));
                    ?>
            </div>

        </td>
        </tr>
    </table>





    <?

    // Collect the output buffer content
    $this_database_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));

    // Update the session cache
    //$_SESSION['DATABASE'][$this_cache_stamp] = $this_database_markup;
}


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Database | Prototype | Mega Man RPG Prototype</title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/database.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
gameSettings.firstRobot = <?= !empty($temp_current_page_robot_key) ? "'{$temp_current_page_robot_key}'" : 'false' ?>;
gameSettings.autoScrollTop = false;
// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
$(document).ready(function(){

    // Tint background color if not in frame
    if (window.top == window.self){ $('body').css({backgroundColor:'#262626'}); }

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the database menu
    var thisContext = $('#database');
    var playSoundEffect = function(){};
    if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){

        // Define a quick local function for routing sound effect plays to the parent
        playSoundEffect = function(soundName, options){
            if (this instanceof jQuery || this instanceof Element){
                if ($(this).data('silentClick')){ return; }
                if ($(this).is('.disabled')){ return; }
                if ($(this).is('.button_disabled')){ return; }
                }
            top.mmrpg_play_sound_effect(soundName, options);
            };

        // DATABASE PAGE LINKS

        // Add hover and click sounds to the buttons in the game-pages menu
        $('#canvas #robot_games .game_link', thisContext).live('mouseenter', function(){
            //console.log('hovering over database game page');
            if ($(this).is('.game_link_disabled')){ return; }
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#canvas #robot_games .game_link', thisContext).live('click', function(){
            //console.log('clicking database game page');
            if ($(this).is('.game_link_disabled')){ return; }
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });

        // DATABASE ICON LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#canvas .wrapper_robots .sprite_robot', thisContext).live('mouseenter', function(){
            //console.log('hovering over database robot icon');
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#canvas .wrapper_robots .sprite_robot', thisContext).live('click', function(){
            //console.log('clicking database robot icon');
            if ($(this).is('[data-token-locked]')){ return; }
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });

        // DATABASE PAGE SPANS

        // Add hover and click sounds to the buttons in the robot page
        $('#console .event .field_name[data-click-tooltip],'+
            '#console .event .skill_name[data-click-tooltip],'+
            '#console .event .ability_name[data-click-tooltip],'+
            '#console .event .record_name[data-click-tooltip]', thisContext).live('mouseenter', function(){
            //console.log('hovering over database robot icon');
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });

        }

    // -- PRIMARY SCRIPT FUNCTIONALITY -- //

    // Fade in the leaderboard screen slowly
    thisBody.waitForImages(function(){
        var tempTimeout = setTimeout(function(){
            if (gameSettings.fadeIn){ thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
            else { thisBody.removeClass('hidden').css({opacity:1}); }
            // Let the parent window know the menu has loaded
            parent.prototype_menu_loaded();
            }, 1000);
        }, false, true);

    // Append the canvas and console markup to the body now that we're ready
    gameCanvas.append('<?= str_replace("'", "\\'", $database_canvas_markup) ?>');
    gameConsole.append('<?= str_replace("'", "\\'", $database_console_markup) ?>');

    // Create the click event for canvas sprites
    $('.sprite_robot[data-token]', gameCanvas).live('click', function(){

        var dataSprite = $(this);
        var dataParent = dataSprite.closest('.wrapper');

        var dataToken = dataSprite.attr('data-token');
        var dataSelect = dataParent.attr('data-select');
        var dataSelectorCurrent = '#'+dataSelect+' .event_visible';
        var dataSelectorNext = '#'+dataSelect+' .event[data-token='+dataToken+']';

        var isAlreadyCurrent = dataSprite.hasClass('sprite_robot_current') ? true : false;
        $('.sprite_robot_current', gameCanvas).removeClass('sprite_robot_current');
        dataSprite.addClass('sprite_robot_current');
        dataParent.css({display:'block'});

        // Check if there is already robot event data on-screen, and either fade it out or skip to the new one
        if ($(dataSelectorCurrent, gameConsole).length && !isAlreadyCurrent){

            // Fade out the current visible events before manually removing them from view
            $(dataSelectorCurrent, gameConsole).stop().animate({opacity:0},250,'swing',function(){
                // Remove the visible class, add the hidden one, then reset the opacity to 1
                $(this).removeClass('event_visible').addClass('event_hidden').css({opacity:1});
                // Fade the new robot data into view by setting opacity to zero, switching classes, then animating back to 1
                $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
                });

            } else {

                // Fade the new robot data into view by setting opacity to zero, switching classes, then animating back to 1
                $(dataSelectorNext, gameConsole).removeClass('event_hidden').addClass('event_visible').css({opacity:1});

            }

        // Update the session variable with the current page link number
        $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,current_database_robot_token,'+dataToken});

        });
    // Trigger a click on the first robot
    //$('.sprite_robot[data-token]:first-child', gameCanvas).trigger('click');

    // Create the click event for canvas game links
    $('.game_link[data-game]', gameCanvas).live('click', function(e){
        // Collect references to the link object and properties
        e.preventDefault();
        var dataLink = $(this);
        var dataGame = dataLink.attr('data-game');
        // Remove the active link from the other link and add it to this one
        $('.game_link[data-game!='+dataGame+']', gameCanvas).removeClass('game_link_active');
        $('.game_link[data-game='+dataGame+']', gameCanvas).addClass('game_link_active');
        // Hide all robot links that are not from the selected game and show the ones that are
        $('.sprite_robot[data-game!='+dataGame+']', gameCanvas).addClass('sprite_robot_hidden');
        $('.sprite_robot[data-game='+dataGame+']', gameCanvas).removeClass('sprite_robot_hidden');
        // Count the number of master and mecha robots currently visible
        var visibleRobots = $('.sprite', gameCanvas).not('.sprite_robot_hidden');
        var visibleRobotsCount = visibleRobots.length;
        var visibleRobotMasters = visibleRobots.filter('.sprite_robot[data-kind=master]').length;
        var visibleRobotMechas = visibleRobots.filter('.sprite_robot[data-kind=mecha]').length;
        var visibleRobotBosses = visibleRobots.filter('.sprite_robot[data-kind=boss]').length;
        //console.log('Switched to '+dataGame+'! Total = '+visibleRobotsCount+'; Robot Masters = '+visibleRobotMasters+'; Mecha Support = '+visibleRobotMechas);
        // Hide or show the robot master container based on count
        if (visibleRobotMasters > 0){ $('.wrapper_header_masters, .wrapper_robots_masters', gameCanvas).css({display:'block'}); }
        else { $('.wrapper_header_masters, .wrapper_robots_masters', gameCanvas).css({display:'none'}); }
        // Hide or show the robot mecha container based on count
        if (visibleRobotMechas > 0){ $('.wrapper_header_mechas, .wrapper_robots_mechas', gameCanvas).css({display:'block'}); }
        else { $('.wrapper_header_mechas, .wrapper_robots_mechas', gameCanvas).css({display:'none'}); }
        // Hide or show the robot boss container based on count
        if (visibleRobotBosses > 0){ $('.wrapper_header_bosses, .wrapper_robots_bosses', gameCanvas).css({display:'block'}); }
        else { $('.wrapper_header_bosses, .wrapper_robots_bosses', gameCanvas).css({display:'none'}); }
        // Auto-click the first visible robot sprite in the canvas
        if (gameSettings.firstRobot !== false){
            var firstVisibleSprite = $('.sprite_robot[data-token='+gameSettings.firstRobot+']', gameCanvas);
            gameSettings.firstRobot = false;
            } else {
            var firstVisibleSprite = $('.sprite_robot[data-token][data-game='+dataGame+']', gameCanvas).first();
            }
        //console.log(firstVisibleSprite.text());
        firstVisibleSprite.triggerSilentClick();
        // Update the session variable with the current page link number
        $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,current_database_page_key,'+dataGame});
        // Return true on succes
        return true;
        });
    // Click the first game link, whatever it is
    if ($('.game_link_active[data-game]', gameCanvas).length){ var tempFirstLink = $('.game_link_active[data-game]', gameCanvas); }
    else { var tempFirstLink = $('.game_link[data-game]', gameCanvas).first(); }
    tempFirstLink.triggerSilentClick();

    // Create the click event for the back button
    $('a.back', gameCanvas).click(function(e){
        e.preventDefault();
        window.location = 'prototype.php';
        });

    // Attach resize events to the window
    thisWindow.resize(function(){ windowResizeFrame(); });
    setTimeout(function(){ windowResizeFrame(); }, 1000);
    windowResizeFrame();

    var windowHeight = $(window).height();
    var htmlHeight = $('html').height();
    var htmlScroll = $('html').scrollTop();
    //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

    // -- DATABASE SPRITE SHOWCASE -- //

    // Create a reference to all the sprite showcase containers
    var $spriteShowcases = $('.sprite_showcase', gameConsole);
    if ($spriteShowcases.length){
        //console.log('found '+$spriteShowcases.length+' sprite showcases!');

        // Define a function to call when we want to update a sprite showcase's frame (background offset)
        var updateSpriteFrame = function($showcase, frameKey){
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $showcaseButtons = $('.sprite_showcase_buttons', $showcaseParent);
            var $showcaseSprites = $('.sprite .sprite', $showcase);
            var dataToken = $showcaseParent.attr('data-token');
            var dataFrame = $('.frame[data-frame-key='+frameKey+']', $showcaseButtons).attr('data-frame');
            var dataImageSize = parseInt($showcase.attr('data-image-size'));
            var backgroundOffset = (frameKey * dataImageSize) * -1;
            var backgroundPosition = backgroundOffset+'px 0';
            $showcaseSprites.css({backgroundPosition:backgroundPosition});
            $('.frame', $showcaseButtons).removeClass('active');
            $('.frame[data-frame-key='+frameKey+']', $showcaseButtons).addClass('active');
            };

        // Define a function to call when we want to update a sprite showcase's alt (background image)
        var updateSpriteAlt = function($showcase, newImageToken){
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $showcaseButtons = $('.sprite_showcase_buttons', $showcaseParent);
            var $showcaseSprites = $('.sprite .sprite', $showcase);
            var dataToken = $showcaseParent.attr('data-token');
            var dataImage = $showcase.attr('data-image');
            var dataBaseImage = $showcase.attr('data-base-image') || dataImage;
            if (!$showcase.is('[data-base-image]')){ $showcase.attr('data-base-image', dataBaseImage); }
            var dataImageSize = parseInt($showcase.attr('data-image-size'));
            var dataImageSizeToken = dataImageSize+'x'+dataImageSize;
            //var oldBackgroundImage = $showcaseSprites.css('backgroundImage');
            //var newBackgroundImage = oldBackgroundImage.replace(dataImage, newImageToken);
            //console.log({oldBackgroundImage:oldBackgroundImage,newBackgroundImage:newBackgroundImage});
            var newBackgroundImage = 'images/robots/'+newImageToken+'/sprite_left_'+dataImageSizeToken+'.png?'+gameSettings.cacheTime;
            //console.log({newBackgroundImage:newBackgroundImage});
            $showcase.attr('data-image', newImageToken);
            $showcaseSprites.css({backgroundImage:'url('+newBackgroundImage+')'});
            };

        // Loop through each showcase and assign events
        $spriteShowcases.each(function(){
            var $showcase = $(this);
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $showcaseButtons = $('.sprite_showcase_buttons', $showcaseParent);
            var $showcaseSprites = $('.sprite .sprite', $showcase);
            //console.log({$showcase:$showcase,$showcaseParent:$showcaseParent,$showcaseButtons:$showcaseButtons});
            var dataToken = $showcaseParent.attr('data-token');
            //console.log('assigning events for '+dataToken+'!');
            $('.frame', $showcaseButtons).bind('mouseenter click', function(e){
                var dataFrame = $(this).attr('data-frame');
                var dataFrameKey = parseInt($(this).attr('data-frame-key'));
                //console.log('mouseenter/click for '+dataToken+'! dataFrame = '+dataFrame+'; dataFrameKey = '+dataFrameKey+';');
                updateSpriteFrame($showcase, dataFrameKey);
                e.stopPropagation();
                });
            $showcaseButtons.bind('mouseleave', function(e){
                //console.log('mouseleave for '+dataToken+'!');
                var dataFrame = 'base';
                var dataFrameKey = 0;
                updateSpriteFrame($showcase, dataFrameKey);
                e.stopPropagation();
                });
            });

        // Make sure we update the showcase images whenever a new alt is selected
        if ($spriteShowcases.length === 1){
            var $showcase = $spriteShowcases.first();
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $spriteHeader = $('.header#sprites', $showcaseParent);
            var $spriteImageOptions = $spriteHeader.length ? $('.images a[data-image]', $spriteHeader) : [];
            if ($spriteImageOptions.length){
                //console.log('found '+$spriteHeader.length+' sprite headers!');
                //console.log('found '+$spriteImageOptions.length+' sprite header images!');
                $spriteImageOptions.each(function(){
                    var $option = $(this);
                    var dataImage = $option.attr('data-image');
                    $option.click(function(e){
                        e.preventDefault();
                        updateSpriteAlt($showcase, dataImage);
                        });
                    });
                }
            }

        }


});

// Create the windowResize event for this page
function windowResizeFrame(){

    var windowWidth = thisWindow.width();
    var windowHeight = thisWindow.height();
    var headerHeight = $('.header', thisBody).outerHeight(true);

    var newBodyHeight = windowHeight;
    var newFrameHeight = newBodyHeight - headerHeight;

    if (windowWidth > 800){ thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
    else { thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

    thisBody.css({height:newBodyHeight+'px'});
    thisPrototype.css({height:newBodyHeight+'px'});

    //console.log('windowWidth = '+windowWidth+'; parentWidth = '+parentWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}
</script>
</head>
<body id="mmrpg" class="iframe" data-frame="database" data-mode="<?= $global_allow_editing ? 'editor' : 'viewer' ?>" data-source="<?= $global_frame_source ?>" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
    <div id="prototype" class="hidden" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
        <div id="database" class="menu">

            <?= $this_database_markup ?>

        </div>

    </div>
<script type="text/javascript">
$(document).ready(function(){

});
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($db);
?>