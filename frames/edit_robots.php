<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Include the DATABASE file
//require_once('../database/include.php');
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/players.php');

//require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
$mmrpg_database_robots = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1 ORDER BY robot_game ASC, robot_number ASC;", 'robot_token');
//require(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');
$mmrpg_database_abilities = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 ORDER BY ability_order ASC;", 'ability_token');
//require(MMRPG_CONFIG_ROOTDIR.'database/items.php');
//
// Collect the editor flag if set
$global_allow_editing = isset($_GET['edit']) && $_GET['edit'] == 'false' ? false : true;


// -- COLLECT SETTINGS DATA -- //

// Define the index of allowable robots to appear in the edit
$allowed_edit_players = array();
$allowed_edit_robots = array();
$allowed_edit_data = array();

// Collect the player's robot favourites
$player_robot_favourites = mmrpg_prototype_robot_favourites();
if (empty($player_robot_favourites)){ $player_robot_favourites = array(); }

// Collect the player's robot database
$player_robot_database = mmrpg_prototype_robot_database();
if (empty($player_robot_database)){ $player_robot_database = array(); }

// Now to actually loop through and update the allowed players, robots, and abilities arrays
foreach ($_SESSION[$session_token]['values']['battle_settings'] AS $player_token => $player_info){
    if (empty($player_token) || empty($player_info['player_robots'])){ continue; }
    $player_info = array_merge($mmrpg_index['players'][$player_token], $player_info);
    $allowed_edit_players[] = $player_info;
    $allowed_edit_data[$player_token] = $player_info;
    foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
        $robot_index = rpg_robot::parse_index_info($mmrpg_database_robots[$robot_token]);
        $robot_index['robot_index_abilities'] = $robot_index['robot_abilities'];
        $robot_info = array_merge($robot_index, $robot_info);
        $allowed_edit_data[$player_token]['player_robots'][$robot_token] = $robot_info;
        $allowed_edit_robots[] = $robot_info;
        foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){
            if (!isset($mmrpg_database_abilities[$ability_token])){ continue; }
            $ability_index = rpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
            if (empty($ability_index)){ continue; }
            $ability_info = array_merge($ability_index, $ability_info);
            $allowed_edit_data[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_info;
        }
    }
}
$allowed_edit_data_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;
$allowed_edit_player_count = !empty($allowed_edit_players) ? count($allowed_edit_players) : 0;
$allowed_edit_robot_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;


// -- PROCESS ROBOT ACTIONS -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'player'){
    require('edit_robots_action_player.php');
}

// Check if an action request has been sent with an ability type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'ability'){
    require('edit_robots_action_ability.php');
}

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'favourite'){
    require('edit_robots_action_favourite.php');
}

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'sort'){
    require('edit_robots_action_sort.php');
}

// Check if an action request has been sent with an altimage type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'altimage'){
    require('edit_robots_action_altimage.php');
}



// -- RECOLLECT SETTINGS DATA -- //

// Define the index of allowable robots to appear in the edit
$allowed_edit_players = array();
$allowed_edit_robots = array();
$allowed_edit_data = array();
$battle_settings = $_SESSION[$session_token]['values']['battle_settings'];
foreach ($battle_settings AS $player_token => $player_info_raw){
    if (empty($player_token) || !isset($mmrpg_index['players'][$player_token])){ continue; }
    $player_index = $mmrpg_index['players'][$player_token];
    unset($player_index['player_robots']);
    $player_info = array_merge($player_index, $player_info_raw);
    if (empty($player_info['player_robots'])){ continue; }
    $allowed_edit_players[] = $player_info;
    $allowed_edit_data[$player_token] = $player_info;
    foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
        $robot_index = rpg_robot::parse_index_info($mmrpg_database_robots[$robot_token]);
        $robot_index['robot_index_abilities'] = $robot_index['robot_abilities'];
        $robot_info = array_merge($robot_index, $robot_info);
        $allowed_edit_data[$player_token]['player_robots'][$robot_token] = $robot_info;
        $allowed_edit_robots[] = $robot_info;
        foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){
            if (!isset($mmrpg_database_abilities[$ability_token])){ continue; }
            $ability_index = rpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
            if (empty($ability_index)){ continue; }
            $ability_info = array_merge($ability_index, $ability_info);
            $allowed_edit_data[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_info;
        }
    }
}
$allowed_edit_data_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;
$allowed_edit_player_count = !empty($allowed_edit_players) ? count($allowed_edit_players) : 0;
$allowed_edit_robot_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;
//$allowed_edit_data = array_reverse($allowed_edit_data, true);


// -- GENERATE EDITOR MARKUP

// CANVAS MARKUP

// Generate the canvas markup for this page
if (true){

 // Start the output buffer
 ob_start();

    // Loop through the allowed edit data for all players
    $key_counter = 0;
    $player_counter = 0;
    $player_keys = array_keys($allowed_edit_data);
    foreach($allowed_edit_data AS $player_token => $player_info){
        $player_counter++;
        $player_colour = 'energy';
        if (!empty($player_info['player_attack'])){ $player_colour = 'attack'; }
        elseif (!empty($player_info['player_defense'])){ $player_colour = 'defense'; }
        elseif (!empty($player_info['player_speed'])){ $player_colour = 'speed'; }
        echo '<td style="width: '.floor(100 / $allowed_edit_player_count).'%;">'."\n";
            echo '<div class="wrapper wrapper_'.($player_counter % 2 != 0 ? 'left' : 'right').' wrapper_'.$player_token.'" data-select="robots" data-player="'.$player_info['player_token'].'">'."\n";
            echo '<div class="wrapper_header player_type player_type_'.$player_colour.'">'.$player_info['player_name'].'</div>';
            foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
                $robot_key = $key_counter;
                $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                $temp_robot_rewards = array();

                if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])){
                    $temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
                }

                foreach ($player_keys AS $this_player_key){
                    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$this_player_key]['player_robots'][$robot_token])){
                        $temp_array = $_SESSION[$session_token]['values']['battle_rewards'][$this_player_key]['player_robots'][$robot_token];
                        $temp_robot_rewards = array_merge($temp_robot_rewards, $temp_array);
                    }
                }

                if (!empty($temp_robot_rewards) && $global_allow_editing){
                    $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token] = $temp_robot_rewards;
                }

                //$temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
                $robot_info['robot_level'] = !empty($temp_robot_rewards['robot_level']) ? $temp_robot_rewards['robot_level'] : 1;
                $robot_info['robot_experience'] = !empty($temp_robot_rewards['robot_experience']) ? $temp_robot_rewards['robot_experience'] : 0;
                if ($robot_info['robot_level'] >= 100){ $robot_info['robot_experience'] = '&#8734;'; }
                $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
                $robot_image_offset_x = -6 - $robot_image_offset;
                $robot_image_offset_y = -10 - $robot_image_offset;
                echo '<a data-number="'.$robot_info['robot_number'].'" data-level="'.$robot_info['robot_level'].'" data-token="'.$player_info['player_token'].'_'.$robot_info['robot_token'].'" data-robot="'.$robot_info['robot_token'].'" data-player="'.$player_info['player_token'].'" title="'.$robot_info['robot_name'].'" data-tooltip="'.$robot_info['robot_name'].' ('.(!empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']).' Core' : 'Neutral Core').') &lt;br /&gt;Lv '.$robot_info['robot_level'].' | '.$robot_info['robot_experience'].' Exp" style="background-image: url(images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: '.$robot_image_offset_x.'px '.$robot_image_offset_y.'px;" class="sprite sprite_robot sprite_robot_'.$player_token.' sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == 0 ? 'sprite_robot_current sprite_robot_'.$player_token.'_current ' : '').' robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').'">'.$robot_info['robot_name'].'</a>'."\n";
                $key_counter++;
            }
            if ($global_allow_editing){
                ?>
                <div class="sort_wrapper">
                    <label class="label">sort</label>
                    <a class="sort sort_number" data-sort="number" data-order="asc" data-player="<?= $player_info['player_token'] ?>">number</a>
                    <a class="sort sort_level" data-sort="level" data-order="asc" data-player="<?= $player_info['player_token'] ?>">level</a>
                </div>
                <?
            }
            echo '</div>'."\n";
        echo '</td>'."\n";
    }

 // Collect the contents of the buffer
 $edit_canvas_markup = ob_get_clean();
 $edit_canvas_markup = preg_replace('/\s+/', ' ', trim($edit_canvas_markup));

}

// CONSOLE MARKUP

// Generate the console markup for this page
if (true){

    // Start the output buffer
    ob_start();

    // Predefine the player options markup
    $player_options_markup = '';
    foreach($allowed_edit_data AS $player_token => $player_info){
        $temp_player_battles = mmrpg_prototype_battles_complete($player_token);
        $temp_player_transfer = $temp_player_battles >= 1 ? true : false;
        $player_options_markup .= '<option value="'.$player_info['player_token'].'" data-label="'.$player_info['player_token'].'" title="'.$player_info['player_name'].'" '.(!$temp_player_transfer ? 'disabled="disabled"' : '').'>'.$player_info['player_name'].'</option>';
    }

    // Loop through the allowed edit data for all players
    $key_counter = 0;

    // Loop through and count each player's robot totals
    $temp_robot_totals = array();
    foreach($allowed_edit_data AS $player_token => $player_info){
        $temp_robot_totals[$player_token] = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : 0;
    }

    // Loop through the players in the ability edit data
    foreach($allowed_edit_data AS $player_token => $player_info){

        // Collect the rewards for this player
        $player_rewards = mmrpg_prototype_player_rewards($player_token);

        // Check how many robots this player has and see if they should be able to transfer
        $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
        $counter_player_missions = mmrpg_prototype_battles_complete($player_info['player_token']);
        $allow_player_selector = $player_counter > 1 && $counter_player_missions > 0 ? true : false; //$counter_player_robots > 1 && $player_counter > 1 ? true : false;

        // If this player has fewer robots than any other player
        //$temp_flag_most_robots = true;
        //foreach ($temp_robot_totals AS $temp_player => $temp_total){
            //if ($temp_player == $player_token){ continue; }
            //elseif ($temp_total > $counter_player_robots){ $allow_player_selector = false; }
        //}
        //
        // Collect global abilities as player abilities
        $player_ability_rewards = array();
        foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $ability_key => $ability_token){
            $player_ability_rewards[$ability_token] = array('ability_token' => $ability_token);
        }
        if (!empty($player_ability_rewards)){ asort($player_ability_rewards); }

        // Loop through the player robots and display their edit boxes
        foreach ($player_info['player_robots'] AS $robot_token => $robot_info){

            // Update the robot key to the current counter
            $robot_key = $key_counter;
            // Make a backup of the player selector
            $allow_player_selector_backup = $allow_player_selector;

            // Collect and print the editor markup for this robot
            $temp_editor_markup = rpg_robot::print_editor_markup($player_info, $robot_info);
            echo $temp_editor_markup;

            $key_counter++;

            // Return the backup of the player selector
            $allow_player_selector = $allow_player_selector_backup;

        }

    }

 // Collect the contents of the buffer
 $edit_console_markup = ob_get_clean();
 $edit_console_markup = preg_replace('/\s+/', ' ', trim($edit_console_markup));

}

// Generate the edit markup using the battles settings and rewards
$this_edit_markup = '';
if (true){

    // Prepare the output buffer
    ob_start();

    // Determine the token for the very first robot in the edit
    $temp_robot_tokens = array_values($allowed_edit_robots);
    $first_robot_token = array_shift($temp_robot_tokens);
    $first_robot_token = $first_robot_token['robot_token'];
    unset($temp_robot_tokens);

    // Start generating the edit markup
    ?>

    <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="count">
            Robot <?= $global_allow_editing ? 'Editor' : 'Viewer' ?> (<?= $allowed_edit_robot_count == 1 ? '1 Robot' : $allowed_edit_robot_count.' Robots' ?>)
        </span>
    </span>

    <div style="float: left; width: 100%;">
    <table class="formatter" style="width: 100%;">
        <colgroup>
            <col width="220" />
            <col width="" />
        </colgroup>
        <tbody>
            <tr>
                <td class="console">

                    <div id="console" class="noresize" style="height: auto;">
                        <div id="robots" class="wrapper"><?/*= $edit_console_markup */?></div>
                    </div>

                </td>
            </tr>
            <tr>
                <td class="canvas" style="vertical-align: top;">

                    <div id="canvas" class="player_counter_<?= $allowed_edit_player_count ?>" style="">
                        <table id="links" style="width: 100%;"><tr><?/*= $edit_canvas_markup */?></tr></table>
                    </div>

                </td>
            </tr>
        </tbody>
    </table>
    </div>

    <?

    // Collect the output buffer content
    $this_edit_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));
}

// DEBUG DEBUG DEBUG
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Data Library | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/edit_robots.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
    <div id="prototype" class="hidden" style="opacity: 0; <?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">
        <div id="edit" class="menu" style="position: relative;">
            <div id="edit_overlay" style="">&nbsp;</div>

            <?= $this_edit_markup ?>

        </div>

    </div>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/edit_robots.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'true' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.autoScrollTop = false;
gameSettings.allowEditing = <?= $global_allow_editing ? 'true' : 'false' ?>;
// Wait until the document is ready
$(document).ready(function(){
    // Append the markup after load to prevent halting display and waiting players
    $('#console #robots').append('<?= str_replace("'", "\'", $edit_console_markup) ?>');
    $('#canvas #links').append('<?= str_replace("'", "\'", $edit_canvas_markup) ?>');
    // Update the player and robot count by counting elements
    thisEditorData.playerTotal = $('#canvas .wrapper[data-player]', thisEditor).length;
    thisEditorData.robotTotal = $('#canvas .sprite[data-robot]', thisEditor).length;
    resizePlayerWrapper();
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