<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_DATABASE', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Include the DATABASE file
//require_once('../data/database.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_players.php');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_robots.php');
$mmrpg_database_robots = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_abilities.php');
$mmrpg_database_abilities = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');
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

// Now to actually loop through and update the allowed players, robots, and abilities arrays
foreach ($_SESSION[$session_token]['values']['battle_settings'] AS $player_token => $player_info){
    if (empty($player_token) || empty($player_info['player_robots'])){ continue; }
    $player_info = array_merge($mmrpg_index['players'][$player_token], $player_info);
    $allowed_edit_players[] = $player_info;
    $allowed_edit_data[$player_token] = $player_info;
    foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
        $robot_index = mmrpg_robot::parse_index_info($mmrpg_database_robots[$robot_token]);
        $robot_index['robot_index_abilities'] = $robot_index['robot_abilities'];
        $robot_info = array_merge($robot_index, $robot_info);
        $allowed_edit_data[$player_token]['player_robots'][$robot_token] = $robot_info;
        $allowed_edit_robots[] = $robot_info;
        foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){
            if (!isset($mmrpg_database_abilities[$ability_token])){ continue; }
            $ability_index = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
            if (empty($ability_index)){ continue; }
            $ability_info = array_merge($ability_index, $ability_info);
            $allowed_edit_data[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_info;
        }
    }
}
$allowed_edit_data_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;
$allowed_edit_player_count = !empty($allowed_edit_players) ? count($allowed_edit_players) : 0;
$allowed_edit_robot_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;


// -- PROCESS PLAYER ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'player'){

    // Collect the player variables from the request header, if they exist
    $temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
    $temp_current_player = !empty($_REQUEST['player1']) ? $_REQUEST['player1'] : '';
    $temp_new_player = !empty($_REQUEST['player2']) ? $_REQUEST['player2'] : '';
    // If key variables are not provided, kill the script in error
    if (empty($temp_robot) || empty($temp_current_player) || empty($temp_new_player)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }
    // If either of the keys are not strings, kill the script in error
    if (!is_string($temp_robot) || !is_string($temp_current_player) || !is_string($temp_new_player)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

    //die(print_r($_REQUEST, true));

    // Unset the prototype robot order variables
    unset($_SESSION['PROTOTYPE_TEMP'][$temp_current_player.'_robot_options']);
    unset($_SESSION['PROTOTYPE_TEMP'][$temp_new_player.'_robot_options']);

    // Ensure this robot exists in the current game session
    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$temp_current_player]['player_robots'][$temp_robot])
        && !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_current_player]['player_robots'][$temp_robot])){

        // Count the number of robots each player has on their team before doing anything
        $temp_current_player_robot_count = !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_current_player]['player_robots']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$temp_current_player]['player_robots']) : 0;
        $temp_new_player_robot_count = !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_new_player]['player_robots']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$temp_new_player]['player_robots']) : 0;

        // Produce the error message if the current player only has one robot
        if ($temp_current_player_robot_count < 2){ exit('error|last-robot|false'); }

        // Collect the current robot settings and rewards from the game session
        $temp_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_current_player]['player_robots'][$temp_robot];
        $temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_current_player]['player_robots'][$temp_robot];

        // Copy the robot settings and rewards to the new player's game session
        $_SESSION[$session_token]['values']['battle_settings'][$temp_new_player]['player_robots'][$temp_robot] = $temp_robot_settings;
        $_SESSION[$session_token]['values']['battle_rewards'][$temp_new_player]['player_robots'][$temp_robot] = $temp_robot_rewards;

        // Update the edit date with the new robot info, then loop through and retcon details
        $allowed_edit_data[$temp_new_player]['player_robots'][$temp_robot] = $allowed_edit_data[$temp_current_player]['player_robots'][$temp_robot];

        // Unset the robot settings and rewards in the old player's game session
        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$temp_new_player]['player_robots'][$temp_robot])
            && !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_new_player]['player_robots'][$temp_robot])){

            $_SESSION[$session_token]['values']['battle_settings'][$temp_current_player]['player_robots'][$temp_robot] = false;
            $_SESSION[$session_token]['values']['battle_rewards'][$temp_current_player]['player_robots'][$temp_robot] = false;
            $allowed_edit_data[$temp_current_player]['player_robots'][$temp_robot] = false;
            unset($_SESSION[$session_token]['values']['battle_settings'][$temp_current_player]['player_robots'][$temp_robot]);
            unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_current_player]['player_robots'][$temp_robot]);
            unset($allowed_edit_data[$temp_current_player]['player_robots'][$temp_robot]);

        }

        // Save, produce the success message with the new ability order
        mmrpg_save_game_session($this_save_filepath);
        //exit('success|player-swapped|true');

        // Collect global abilities as player abilities
        $player_ability_rewards = array();
        if (!isset($_SESSION[$session_token]['values']['battle_abilities'])){ $_SESSION[$session_token]['values']['battle_abilities'] = array(); }
        foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $ability_key => $ability_token){ $player_ability_rewards[$ability_token] = array('ability_token' => $ability_token); }
        if (!empty($player_ability_rewards)){ asort($player_ability_rewards); }

        $key_counter = 0;
        $player_counter = 1;
        $temp_robot_totals = array();
        $player_options_markup = '';

        foreach($allowed_edit_data AS $ptoken => $pinfo){
            $temp_robot_totals[$ptoken] = !empty($pinfo['player_robots']) ? count($pinfo['player_robots']) : 0;
            $temp_player_battles = mmrpg_prototype_battles_complete($ptoken);
            $temp_player_transfer = $temp_player_battles >= 1 ? true : false;
            $player_options_markup .= '<option value="'.$pinfo['player_token'].'" data-label="'.$pinfo['player_token'].'" title="'.$pinfo['player_name'].'" '.(!$temp_player_transfer ? 'disabled="disabled"' : '').'>'.$pinfo['player_name'].'</option>';
            $player_counter++;
        }

        foreach($allowed_edit_data AS $temp_player_token => $temp_player_info){
            if ($temp_player_token == $temp_new_player){
                // Collect player rewards and settings then print editor markup
                $player_rewards = mmrpg_prototype_player_rewards($temp_player_token);
                $temp_robot_info = $temp_player_info['player_robots'][$temp_robot];
                $temp_robot_info['robot_settings'] = $temp_robot_settings;
                $temp_robot_info['robot_rewards'] = $temp_robot_rewards;
                $first_robot_token = $temp_robot_info['robot_token'];
                exit('success|player-swapped|'.mmrpg_robot::print_editor_markup($temp_player_info, $temp_robot_info));
            }
        }

    }
    // Otherwise, produce an error
    else {

        // Produce the error message
        exit('error|robot-undefined|false');

    }



}

// -- PROCESS ABILITY ACTION -- //

// Check if an action request has been sent with an ability type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'ability'){

    // Collect the ability variables from the request header, if they exist
    $temp_key = !empty($_REQUEST['key']) ? $_REQUEST['key'] : 0;
    $temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
    $temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
    $temp_ability = !empty($_REQUEST['ability']) ? $_REQUEST['ability']: '';
    // If key variables are not provided, kill the script in error
    if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

    //die(print_r($_REQUEST, true));

    // Collect the current settings for the requested robot
    $temp_settings = mmrpg_prototype_robot_settings($temp_player, $temp_robot);
    // Create a key-based array to hold the ability settings in and populate it
    $temp_abilities = array();
    foreach ($temp_settings['robot_abilities'] AS $temp_info){ $temp_abilities[] = $temp_info['ability_token']; }
    // Crop the ability settings if they've somehow exceeded the eight limit
    if (count($temp_abilities) > 8){ $temp_abilities = array_slice($temp_abilities, 0, 8, true); }

    // If requested new ability was an empty string, remove the previous value
    if (empty($temp_ability)){
        // If this was the last ability, do nothing with this request
        if (count($temp_abilities) <= 1){ die('success|remove-last|'.implode(',', $temp_abilities)); }
        // Unset the requested key in the array
        unset($temp_abilities[$temp_key]);
        // Create a new array to hold the full ability settings and populate
        $temp_abilities_new = array();
        foreach ($temp_abilities AS $temp_token){ $temp_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
        // Update the new ability settings in the session variable
        $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $temp_abilities_new;
        // Save, produce the success message with the new ability order
        mmrpg_save_game_session($this_save_filepath);
        exit('success|ability-removed|'.implode(',', $temp_abilities));
    }
    // Otherwise, if there was a new ability provided, update it in the array
    elseif (!in_array($temp_ability, $temp_abilities)){
        // Update this position in the array with the new ability
        $temp_abilities[$temp_key] = $temp_ability;
        // Create a new array to hold the full ability settings and populate
        $temp_abilities_new = array();
        foreach ($temp_abilities AS $temp_token){ $temp_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
        // Update the new ability settings in the session variable
        $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $temp_abilities_new;
        // Save, produce the success message with the new ability order
        mmrpg_save_game_session($this_save_filepath);
        exit('success|ability-updated|'.implode(',', $temp_abilities));
    }
    // Otherwise, if ability is already equipped, swap positions in the array
    elseif (in_array($temp_ability, $temp_abilities)){
        // Update this position in the array with the new ability
        $this_slot_key = $temp_key;
        $this_slot_value = $temp_abilities[$temp_key];
        $copy_slot_value = $temp_ability;
        $copy_slot_key = array_search($temp_ability, $temp_abilities);
        // Update this slot with new value
        $temp_abilities[$this_slot_key] = $copy_slot_value;
        // Update copy slot with new value
        $temp_abilities[$copy_slot_key] = $this_slot_value;
        // Create a new array to hold the full ability settings and populate
        $temp_abilities_new = array();
        foreach ($temp_abilities AS $temp_token){ $temp_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
        // Update the new ability settings in the session variable
        $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $temp_abilities_new;
        // Save, produce the success message with the new ability order
        mmrpg_save_game_session($this_save_filepath);
        exit('success|ability-updated|'.implode(',', $temp_abilities));
    } else {
        // Produce an error show this ability has already been selected
        exit('error|ability-exists|'.implode(',', $temp_abilities));
    }

}




// -- PROCESS FAVOURITE ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'favourite'){

    // Collect the ability variables from the request header, if they exist
    $temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
    $temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
    // If key variables are not provided, kill the script in error
    if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

    //die(print_r($_REQUEST, true));

    // Collect the current robot favourites for this user
    $current_robot_favourites = !empty($_SESSION[$session_token]['values']['robot_favourites']) ? $_SESSION[$session_token]['values']['robot_favourites'] : array();
    $temp_player_info = $allowed_edit_data[$temp_player];
    $temp_robot_info = $allowed_edit_data[$temp_player]['player_robots'][$temp_robot];

    // If this robot is not already a favourite, add it
    if (!in_array($temp_robot, $current_robot_favourites)){
        $current_robot_favourites[] = $temp_robot;
        $_SESSION[$session_token]['values']['robot_favourites'] = $current_robot_favourites;
        mmrpg_save_game_session($this_save_filepath);
        exit('success|favourite-added|added');
    }
    // If this robot is not already a favourite, add it
    elseif (in_array($temp_robot, $current_robot_favourites)){
        $temp_remove_key = array_search($temp_robot, $current_robot_favourites);
        unset($current_robot_favourites[$temp_remove_key]);
        $current_robot_favourites = array_values($current_robot_favourites);
        $_SESSION[$session_token]['values']['robot_favourites'] = $current_robot_favourites;
        mmrpg_save_game_session($this_save_filepath);
        exit('success|favourite-removed|removed');
    }

    exit('error|request-error|unknown');

}


// -- PROCESS SORT ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'sort'){

    // Collect the ability variables from the request header, if they exist
    $temp_token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
    $temp_order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
    $temp_token_order = $temp_token.'_'.$temp_order;
    // If key variables are not provided, kill the script in error
    if (empty($temp_token) || empty($temp_order) || empty($temp_player)){
        die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true)));
    }

    //die(print_r($_REQUEST, true));

    // Ensure this player's robots exist in the current game session
    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'])
        && !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'])){

            // Make a copy of the player robots array
            $temp_player_robots = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'];
            $temp_player_robots_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'];
            if (!empty($temp_player_robots)){
                foreach ($temp_player_robots AS $token => $info){
                    // Update the current and session arrays to make absolutely sure the robot token is in the right place
                    if (empty($info['robot_token'])){ $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$token]['robot_token'] = $temp_player_robots[$token]['robot_token'] = $token; }
                    //$temp_player_robots[$token]['robot_level'] = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$token]['robot_level'];
                    //$temp_player_robots[$token]['robot_experience'] = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$token]['robot_experience'];
                }
            }
            //die('<pre>'.print_r($temp_player_robots, true).'</pre>');

            // Define a temporarily function for sorting the robots
            $mmrpg_database_robots_keys = array_keys($mmrpg_database_robots);

            // If the sort token was by number and asc
            if ($temp_token_order == 'number_asc'){
                // Define the sort function that uses these keys
                function temp_player_robots_sort($r1, $r2){
                    global $mmrpg_index, $mmrpg_database_robots_keys;
                    if (empty($r1) || empty($r2)){ return 0; }
                    $robot1_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                    $robot2_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                    if ($robot1_position === false){ return 1; }
                    elseif ($robot2_position === false){ return -1; }
                    elseif ($robot1_position < $robot2_position){ return -1; }
                    elseif ($robot1_position > $robot2_position){ return 1; }
                    else { return 0; }
                }
            }
            // Else if the sort token was by number and desc
            elseif ($temp_token_order == 'number_desc'){
                // Define the sort function that uses these keys
                function temp_player_robots_sort($r1, $r2){
                    global $mmrpg_index, $mmrpg_database_robots_keys;
                    if (empty($r1) || empty($r2)){ return 0; }
                    $robot1_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                    $robot2_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                    if ($robot1_position === false){ return -1; }
                    elseif ($robot2_position === false){ return 1; }
                    elseif ($robot1_position < $robot2_position){ return 1; }
                    elseif ($robot1_position > $robot2_position){ return -1; }
                    else { return 0; }
                }
            }
            // Else if the sort token was by level and asc
            elseif ($temp_token_order == 'level_asc'){
                // Define the sort function that uses these keys
                function temp_player_robots_sort($r1, $r2){
                    global $mmrpg_index, $mmrpg_database_robots_keys, $temp_player_robots_rewards;
                    if (empty($r1) || empty($r2)){ return 0; }
                    $robot1_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                    $robot2_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                    $r1['robot_level'] = $temp_player_robots_rewards[$r1['robot_token']]['robot_level'];
                    $r1['robot_experience'] = $temp_player_robots_rewards[$r1['robot_token']]['robot_experience'];
                    $r2['robot_level'] = $temp_player_robots_rewards[$r2['robot_token']]['robot_level'];
                    $r2['robot_experience'] = $temp_player_robots_rewards[$r2['robot_token']]['robot_experience'];
                    if ($robot1_position === false){ return -1; }
                    elseif ($robot2_position === false){ return 1; }
                    elseif ($r1['robot_level'] < $r2['robot_level']){ return -1; }
                    elseif ($r1['robot_level'] > $r2['robot_level']){ return 1; }
                    elseif ($r1['robot_experience'] < $r2['robot_experience']){ return -1; }
                    elseif ($r1['robot_experience'] > $r2['robot_experience']){ return 1; }
                    elseif ($robot1_position < $robot2_position){ return -1; }
                    elseif ($robot1_position > $robot2_position){ return 1; }
                    else { return 0; }
                }
            }
            // Else if the sort token was by level and desc
            elseif ($temp_token_order == 'level_desc'){
                // Define the sort function that uses these keys
                function temp_player_robots_sort($r1, $r2){
                    global $mmrpg_index, $mmrpg_database_robots_keys, $temp_player_robots_rewards;
                    if (empty($r1) || empty($r2)){ return 0; }
                    $robot1_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                    $robot2_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                    $r1['robot_level'] = $temp_player_robots_rewards[$r1['robot_token']]['robot_level'];
                    $r1['robot_experience'] = $temp_player_robots_rewards[$r1['robot_token']]['robot_experience'];
                    $r2['robot_level'] = $temp_player_robots_rewards[$r2['robot_token']]['robot_level'];
                    $r2['robot_experience'] = $temp_player_robots_rewards[$r2['robot_token']]['robot_experience'];
                    if ($robot1_position === false){ return 1; }
                    elseif ($robot2_position === false){ return -1; }
                    elseif ($r1['robot_level'] < $r2['robot_level']){ return 1; }
                    elseif ($r1['robot_level'] > $r2['robot_level']){ return -1; }
                    elseif ($r1['robot_experience'] < $r2['robot_experience']){ return 1; }
                    elseif ($r1['robot_experience'] > $r2['robot_experience']){ return -1; }
                    elseif ($robot1_position < $robot2_position){ return 1; }
                    elseif ($robot1_position > $robot2_position){ return -1; }
                    else { return 0; }
                }
            }

            // Sort the robots and maintain index association
            uasort($temp_player_robots, 'temp_player_robots_sort');

            // Ensure nothing went wrong with the array before copying
            if (!empty($temp_player_robots)){
                $temp_robot_tokens = implode(',', array_keys($temp_player_robots));
                $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'] = $temp_player_robots;
                exit('success|array-sorted|'.$temp_robot_tokens);
            }
            // Otherwise produce an error
            else {
                // Produce the error message
                exit('error|array-corrupted|false');
            }


        }
    // Otherwise, produce an error
    else {

        // Produce the error message
        exit('error|robots-undefined|false');

    }

}



// -- RECOLLECT SETTINGS DATA -- //

// Define the index of allowable robots to appear in the edit
$allowed_edit_players = array();
$allowed_edit_robots = array();
$allowed_edit_data = array();
foreach ($_SESSION[$session_token]['values']['battle_settings'] AS $player_token => $player_info){
    if (empty($player_token) || !isset($mmrpg_index['players'][$player_token])){ continue; }
    $player_info = array_merge($mmrpg_index['players'][$player_token], $player_info);
    $allowed_edit_players[] = $player_info;
    $allowed_edit_data[$player_token] = $player_info;
    foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
        $robot_index = mmrpg_robot::parse_index_info($mmrpg_database_robots[$robot_token]);
        $robot_index['robot_index_abilities'] = $robot_index['robot_abilities'];
        $robot_info = array_merge($robot_index, $robot_info);
        $allowed_edit_data[$player_token]['player_robots'][$robot_token] = $robot_info;
        $allowed_edit_robots[] = $robot_info;
        foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){
            if (!isset($mmrpg_database_abilities[$ability_token])){ continue; }
            $ability_index = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
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
            $temp_editor_markup = mmrpg_robot::print_editor_markup($player_info, $robot_info);
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

    <span class="header block_1">Robot <?= $global_allow_editing ? 'Editor' : 'Viewer' ?> (<?= $allowed_edit_robot_count == 1 ? '1 Robot' : $allowed_edit_robot_count.' Robots' ?>)</span>

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
    //console.log(thisEditorData);
    resizePlayerWrapper();
<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'mmrpg-event-01_robot-editor-intro';
if (empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
    $temp_game_flags[$temp_event_flag] = true;
    ?>
    // Generate a first-time event canvas that explains how the editor works
    gameSettings.windowEventsCanvas = [
        '<div class="sprite sprite_80x80" style="background-image: url(images/fields/field/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
        '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/ice-slasher/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 20px; left: 0px; width: 80px; height: 80px;">&nbsp;</div>'+
        '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/fire-storm/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 35px; left: 100px; width: 80px; height: 80px;">&nbsp;</div>'+
        '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/mega-buster/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 50px; left: 200px; width: 80px; height: 80px;">&nbsp;</div>'+
        '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/rolling-cutter/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 35px; left: 300px; width: 80px; height: 80px;">&nbsp;</div>'+
        '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/hyper-bomb/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 20px; left: 400px; width: 80px; height: 80px;">&nbsp;</div>'+
        ''
        ];
    // Generate a first-time event message that explains how the editor works
    gameSettings.windowEventsMessages = [
        '<p>The <strong>Robot Editor</strong> contains detailed information on all of your unlocked robot masters and allows you to edit their attributes.  Detailed stat breakdowns track the growth of your robtos while the weaknesses and resistances provide helpful reference before battle. The most powerful feature of the editor, however, comes in the form of ability customization.</p>'+
        '<p>Click on any of any of the eight weapon slots for a robot and you can equip it with any ability it\'s compatible with - based on their core type - even if the ability was originally learned by another robot. Some abilities can be used by all robots and some by only a select few, so don\'t be afraid to experiment when a new one is unlocked.</p>'+
        ''
        ];
    // Push this event to the parent window and display to the user
    top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
    <?
}
?>
});
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($DB);
?>