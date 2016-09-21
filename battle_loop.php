<?php
// Include the TOP file
require_once('top.php');

//die('<pre>'.print_r($_REQUEST, true).'</pre>');

// Start the output buffer to capture any errors
ob_start();

/*
 * DEFINE & COLLECT BATTLE VARIABLES
 */

// Collect the global battle variables
$this_battle_id = isset($_REQUEST['this_battle_id']) ? $_REQUEST['this_battle_id'] : 1;
$this_battle_token = isset($_REQUEST['this_battle_token']) ? $_REQUEST['this_battle_token'] : 'battle';
$this_field_id = isset($_REQUEST['this_field_id']) ? $_REQUEST['this_field_id'] : 1;
$this_field_token = isset($_REQUEST['this_field_token']) ? $_REQUEST['this_field_token'] : 'field';
$this_user_id = isset($_REQUEST['this_user_id']) ? $_REQUEST['this_user_id'] : 1;
$this_player_id = isset($_REQUEST['this_player_id']) ? $_REQUEST['this_player_id'] : $this_user_id;
$this_player_token = isset($_REQUEST['this_player_token']) ? $_REQUEST['this_player_token'] : 'player';
$this_player_robots = !empty($_REQUEST['this_player_robots']) ? $_REQUEST['this_player_robots'] : '00_robot';
$this_robot_id = isset($_REQUEST['this_robot_id']) ? $_REQUEST['this_robot_id'] : 1;
$this_robot_token = isset($_REQUEST['this_robot_token']) ? $_REQUEST['this_robot_token'] : 'robot';
$target_user_id = isset($_REQUEST['target_user_id']) ? $_REQUEST['target_user_id'] : 2;
$target_player_id = isset($_REQUEST['target_player_id']) ? $_REQUEST['target_player_id'] : 2;
$target_player_token = isset($_REQUEST['target_player_token']) ? $_REQUEST['target_player_token'] : 'player';
$target_player_robots = !empty($_REQUEST['target_player_robots']) ? $_REQUEST['target_player_robots'] : '00_robot';
$target_robot_id = isset($_REQUEST['target_robot_id']) ? $_REQUEST['target_robot_id'] : 2;
$target_robot_token = isset($_REQUEST['target_robot_token']) ? $_REQUEST['target_robot_token'] : 'robot';

// Define the current action request variables
$this_action = isset($_REQUEST['this_action']) ? $_REQUEST['this_action'] : 'start';
$this_action_token = isset($_REQUEST['this_action_token']) ? $_REQUEST['this_action_token'] : '';
$target_action = isset($_REQUEST['target_action']) ? $_REQUEST['target_action'] : 'start';
$target_action_token = isset($_REQUEST['target_action_token']) ? $_REQUEST['target_action_token'] : '';

// Define a variable to track the verified state and any errors in data processing
$this_verified = true;
$this_errors = array();

// Ensure all madatory variables were set, and create errors for missing fields
if (empty($this_battle_id) || empty($this_battle_token)){
    $this_verified = false;
    $this_errors[] = 'This battle token was not received! '.
        '$this_battle_id = '.$this_battle_id.'; $this_battle_token = '.$this_battle_token.';';
}
if (empty($this_field_id) || empty($this_field_token)){
    $this_verified = false;
    $this_errors[] = 'This field token was not received! '.
        '$this_field_id = '.$this_field_id.'; $this_field_token = '.$this_field_token.';';
}
if (empty($this_player_id) || empty($this_player_token)){
    $this_verified = false;
    $this_errors[] = 'This player token was not received! '.
        '$this_field_id = '.$this_field_id.'; $this_field_token = '.$this_field_token.';';
}
if (empty($target_player_id) || empty($target_player_token)){
    $this_verified = false;
    $this_errors[] = 'Target player token was not received! '.
        '$target_player_id = '.$target_player_id.'; $target_player_token = '.$target_player_token.';';
}
if (empty($this_robot_id) || empty($this_robot_token)){
    $this_verified = false;
    $this_errors[] = 'This robot token was not received! '.
        '$this_robot_id = '.$this_robot_id.'; $this_robot_token = '.$this_robot_token.';';
}
if (empty($target_robot_id) || empty($target_robot_token)){
    $this_verified = false;
    $this_errors[] = 'Target robot token was not received! '.
        '$target_robot_id = '.$target_robot_id.'; $target_robot_token = '.$target_robot_token.';';
}

// If there were any critcal errors, exit the battle script
if ($this_verified == false || !empty($this_errors)){
    trigger_error('Critical Battle Error<br /><pre>$this_errors : '.print_r($this_errors, true).'</pre>', E_USER_ERROR);
}

// If the player is has requested the start action
if ($this_action == 'start'){

    // Automatically empty all temporary battle variables
    $_SESSION['BATTLES'] = array();
    $_SESSION['FIELDS'] = array();
    $_SESSION['PLAYERS'] = array();
    $_SESSION['ROBOTS'] = array();
    $_SESSION['ABILITIES'] = array();
    $_SESSION['ITEMS'] = array();

}

/*
 * DEFINE & INITALIZE BATTLE OBJECTS
 */

// Define the battle object using the loaded battle data
$this_battleinfo = array('battle_id' => $this_battle_id, 'battle_token' => $this_battle_token);

// Define the current field object using the loaded field data
$this_fieldinfo = array('field_id' => $this_field_id, 'field_token' => $this_field_token);

// Define the battle object using the loaded battle data and update session
$this_battle = rpg_game::get_battle($this_battleinfo);
$this_battle->flags['wap'] = $flag_wap ? true : false;
$this_battle->update_session();

// Define the current field object using the loaded field data and update session
$this_field = new rpg_field($this_battle, $this_fieldinfo);
$this_field->update_session();

// Define the current player object using the loaded player data
$this_playerinfo = array('user_id' => $this_user_id, 'player_id' => $this_player_id, 'player_token' => $this_player_token, 'player_autopilot' => false);
$this_playerinfo['player_autopilot'] = false;
$this_playerinfo['player_side'] = 'left';
// Define the target player object using the loaded player data
$target_playerinfo = array('user_id' => $target_user_id, 'player_id' => $target_player_id, 'player_token' => $target_player_token);
$target_playerinfo['player_autopilot'] = true;
$target_playerinfo['player_side'] = 'right';

// Only create the arrays with minimal info if NOT start
if ($this_action != 'start'){
    // Define the current player object using the loaded player data and update session
    $this_player = rpg_game::get_player($this_battle, $this_playerinfo);
    // Define the target player object using the loaded player data and update session
    $target_player = rpg_game::get_player($this_battle, $target_playerinfo);
}
// Otherwise, prepopulate their robot arrays
elseif ($this_action == 'start'){
    // Break apart and filter this player's robots
    $this_playerinfo['player_robots'] = array();
    $temp_this_player_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
    foreach ($temp_this_player_robots AS $temp_string){
        list($temp_id, $temp_token) = explode('_', $temp_string);
        $temp_rewards = mmrpg_prototype_robot_settings($this_playerinfo['player_token'], $temp_token);
        $temp_abilities = !empty($temp_rewards['robot_abilities']) ? array_keys($temp_rewards['robot_abilities']) : array();
        $this_playerinfo['player_robots'][] = array('robot_id' => $temp_id, 'robot_token' => $temp_token, 'robot_abilities' => $temp_abilities);
        unset($temp_rewards, $temp_abilities);
    }
    // Break apart and filter the target player's robots
    $target_playerinfo['player_robots'] = array();
    if (!empty($this_battle->battle_target_player['player_robots'])){
        $target_playerinfo['player_robots'] = $this_battle->battle_target_player['player_robots'];
    }
    // Break apart and filter the target player's starforce
    $target_playerinfo['player_starforce'] = array();
    if (!empty($this_battle->battle_target_player['player_starforce'])){
        $target_playerinfo['player_starforce'] = $this_battle->battle_target_player['player_starforce'];
    }
}

//echo 'memory_limit() = '.ini_get('memory_limit')."\n";
//echo 'memory_get_usage() = '.round(((memory_get_usage() / 1024) / 1024), 2).'M'."\n";
//echo 'memory_get_peak_usage() = '.round((memory_get_peak_usage() / 1024) / 1024, 2).'M'."\n";
//exit();

// If this is the START action, update objects with preset battle data fields
if ($this_action == 'start'){

    // Start the battle turn off at zero
    $this_battle->counters['battle_turn'] = 0;

    // Update applicable fieldinfo fields with preset battle data
    if (!empty($this_battle->battle_field_base)){
        $this_fieldinfo = array_replace($this_fieldinfo, $this_battle->battle_field_base);
        $this_field = new rpg_field($this_battle, $this_fieldinfo);
        $this_field->update_session();
    }

    // Ensure the player's robot string was provided
    if (!empty($this_player_robots)){

        // Precreate the player object using the newly defined details
        $backup_this_playerinfo = $this_playerinfo;
        $backup_this_playerinfo['player_robots'] = array();
        $this_player = rpg_game::get_player($this_battle, $backup_this_playerinfo);
        unset($backup_this_playerinfo);
        // Break apart the allowed robots string and unset undefined robots
        $temp_robots_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        //$this_playerinfo = $this_player->export_array();
        //$this_playerinfo_robots = array();
        //die('<pre>after:'.print_r($debug['player_robots'], true).'</pre>');
        $this_key_counter = 0;
        $temp_this_player_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
        foreach ($this_playerinfo['player_robots'] AS $this_key => $this_data){
            if (!mmrpg_prototype_robot_unlocked($this_player_token, $this_data['robot_token'])){ continue; }
            $this_info = rpg_robot::parse_index_info($temp_robots_index[$this_data['robot_token']]);
            $this_token = $this_data['robot_id'].'_'.$this_data['robot_token'];
            $this_position = array_search($this_token, $temp_this_player_robots);
            $this_data['robot_key'] = $this_key_counter;
            $this_data['robot_experience'] = mmrpg_prototype_robot_experience($this_playerinfo['player_token'], $this_data['robot_token']);
            $this_data['robot_level'] = mmrpg_prototype_robot_level($this_playerinfo['player_token'], $this_data['robot_token']);
            // Only allow this robot if it exists in the robot string
            if ($this_position !== false){
                // Create the temporary robot object to load data
                $this_data = array_merge($this_info, $this_data);
                $this_player->load_robot($this_data, $this_key_counter, true);
                $this_player->update_session();
                $this_key_counter++;
            }
        }
        // Update the player session with changes
        $this_player->update_session();

    }

    // Ensure this player's items were provided
    $temp_session_token = $this_player->player_token.'_this-item-omega_prototype';
    /*
    if (!empty($_SESSION['GAME']['values'][$temp_session_token])){
        $this_player_items = $_SESSION['GAME']['values'][$temp_session_token];
    } else
    */
    if (!empty($_SESSION['GAME']['values']['battle_items'])){
        $this_player_items = $_SESSION['GAME']['values']['battle_items'];
        unset($this_player_items['small-screw'], $this_player_items['large-screw']);
        $this_player_items = array_keys($this_player_items);
        //$this_player_items = array_slice($this_player_items, 0, 8);
        $_SESSION['GAME']['values'][$temp_session_token] = $this_player_items;
    } else {
        $this_player_items = array();
    }

    // Update this player's items in the object and session
    if (!empty($this_player_items)){
        // Update this player's items with the requested tokens
        $this_player->player_items = $this_player_items;
        $this_player->player_base_items = $this_player->player_items;
        // Update the session with the item changes
        $this_player->update_session();
    }

    // Ensure there are target robots to loop through
    if (!empty($target_playerinfo['player_robots'])){

        // Precreate the target player object using the newly defined details
        $backup_target_playerinfo = $target_playerinfo;
        $backup_target_playerinfo['player_robots'] = array();
        $target_player = rpg_game::get_player($this_battle, $backup_target_playerinfo);
        unset($backup_target_playerinfo);
        // Break apart the allowed robots string and unset undefined robots
        $temp_robots_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        //strstr($target_player_robots, ',') ? explode(',', $target_player_robots) : array($target_player_robots);
        //$target_playerinfo = $target_player->export_array();
        $target_playerinfo_robots = array();
        $this_key_counter = 0;
        foreach ($target_playerinfo['player_robots'] AS $this_key => $this_data){
            $this_info = rpg_robot::parse_index_info($temp_robots_index[$this_data['robot_token']]);
            $this_token = $this_data['robot_id'].'_'.$this_data['robot_token'];
            $this_position = $this_key; //array_search($this_token, $target_player_robots);
            $this_data['robot_key'] = $this_key_counter;
            // Only allow this robot if it exists in the robot string
            if ($this_position !== false){
                // Create the temporary robot object to load data
                $this_data = array_merge($this_info, $this_data);
                $target_player->player_robots[$this_key] = $this_data;
                $target_player->load_robot($this_data, $this_key_counter, true);
                $this_key_counter++;
            }
        }
        // Update the player session with changes
        $target_player->update_session();

        // DEBUG
        //die('<hr />'.__LINE__.':: <pre>'.print_r($this_data, true).'</pre>');

    }

    // Ensure the target player's items were provided
    $target_playerinfo['player_items'] = array();
    if (!empty($target_playerinfo['player_items'])){
        // Update this player's items with the requested tokens
        $target_player->player_items = $target_playerinfo['player_items'];
        $target_player->player_base_items = $target_player->player_items;
        // Update the session with the item changes
        $target_player->update_session();
    }

}

// Ensure this player has robots to start with
if (!empty($this_player->player_robots)){
    // Check if the player robot was set to auto
    if ($this_robot_id == 'auto' || $this_robot_token == 'auto'){
        // Collect the first robot in this player's party
        reset($this_player->player_robots);
        $this_robotinfo = current($this_player->player_robots);
    }
    // Otherwise define the robotinfo array manually
    else {
        // Create the robotinfo array with engine data
        $this_robotinfo = array('robot_id' => $this_robot_id, 'robot_token' => $this_robot_token);
    }
}
// Otherwise, if this player has no robots
else {
    // Trigger a critical error, this shit is not gonna work
    trigger_error('Critical Battle Error<br /><pre>This player has no robots!</pre>', E_USER_ERROR);
}

//echo '<script type="text/javascript">alert("$this_robotinfo : '.preg_replace('#\s+#', ' ', print_r($this_robotinfo, true)).'");</script>';

// Ensure the target player has robots to start with
if (!empty($target_player->player_robots)){
    // Check if the target player robot was set to auto
    if ($target_robot_id == 'auto' || $target_robot_token == 'auto'){
        // Collect the first robot in the target player's party
        reset($target_player->player_robots);
        $target_robotinfo = current($target_player->player_robots);
    }
    // Otherwise define the robotinfo array manually
    else {
        // Create the robotinfo array with engine data
        $target_robotinfo = array('robot_id' => $target_robot_id, 'robot_token' => $target_robot_token);
    }
}
// Otherwise, if the target player has no robots
else {
    // Trigger a critical error, this shit is not gonna work
    trigger_error('Critical Battle Error<br /><pre>The target player has no robots!</pre>', E_USER_ERROR);
}

// Define the current robot object using the loaded robot data
$this_robot = rpg_game::get_robot($this_battle, $this_player, $this_robotinfo);
// Define the target robot object using the loaded robot data
if ($target_robotinfo['robot_id'] >= MMRPG_SETTINGS_TARGET_PLAYERID){
    $target_robot = rpg_game::get_robot($this_battle, $target_player, $target_robotinfo);
} else {
    $target_robot = rpg_game::get_robot($this_battle, $this_player, $target_robotinfo);
}


/*
 * BATTLE START!
 */

// Define the redirect variable in case we need to change screens
$this_redirect = '';

// Define the action queue variable and populate it with this/target pairs
$action_queue = array();

// If the player is has requested the prototype menu
if ($this_action == 'prototype'){

    // Require the prototype action file
    require_once('battle/actions/prototype.php');

}
// Else if the player is has requested to restart the battle
elseif ($this_action == 'restart'){

    // Require the restart action file
    require_once('battle/actions/restart.php');

}
// Else if the player is just starting the battle, queue start actions
elseif ($this_action == 'start'){

    // Require the start action file
    require_once('battle/actions/start.php');

}
// Else if the player is switching robots, they go first
elseif ($this_action == 'switch'){

    // Require the switch action file
    require_once('battle/actions/switch.php');

}
// Else if the player's robot is using a scan
elseif ($this_action == 'scan'){

    // Require the scan action file
    require_once('battle/actions/scan.php');

}
// Else if the player's robot is using an ability
elseif ($this_action == 'ability'){

    // Require the ability action file
    require_once('battle/actions/ability.php');

}
// Else if the player's robot is using an item
elseif ($this_action == 'item'){

    // Require the ability-item action file
    require_once('battle/actions/ability_item.php');

}
/*
// Else if the player's robot is using an ability-item
elseif ($this_action == 'item' && strstr($this_action_token, '-core')){

    // Require the ability-item action file
    require_once('battle/actions/ability_item.php');

}
// Else if the player's robot is using an item
elseif ($this_action == 'item'){

    // Require the item action file
    require_once('battle/actions/item.php');

}
*/

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();

// Set the hidden flag on this robot if necessary
if ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1){
    $this_robot->flags['apply_disabled_state'] = true;
    $this_robot->flags['hidden'] = true;
    $this_robot->update_session();
}

// Set the hidden flag on the target robot if necessary
if ($target_robot->robot_status == 'disabled' || $target_robot->robot_energy < 1){
    $target_robot->flags['apply_disabled_state'] = true;
    $target_robot->flags['hidden'] = true;
    $target_robot->update_session();
}


/*
 * ACTION PROCESSING
 */

if (!isset($_SESSION['GAME']['values']['battle_items'])){
    // Loop through player index if not empty
    $temp_items_array = array();
    foreach ($mmrpg_index['players'] AS $player_token => $player_info){
        if (!empty($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_items'])){
            foreach ($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_items'] AS $token => $count){
                if (!isset($temp_items_array[$token])){ $temp_items_array[$token] = 0; }
                $temp_items_array[$token] += $count;
            }
        }
    }
    // Create the new unified items array in the session
    if (!isset($_SESSION['GAME']['values']['battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = array(); }
    $_SESSION['GAME']['values']['battle_items'] = $temp_items_array;
}

// Define the array to hold action panel markup
$actions_markup = array();


// Define the default this and target ids and tokens to reload
$temp_this_reload_robot = array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token);
$temp_target_reload_robot = array('robot_id' => $target_robot->robot_id, 'robot_token' => $target_robot->robot_token);

// If this robot is not active, check to see if there's one that is
if ($this_robot->robot_position != 'active'){
    // Define the variable to hold active robot info and search for it
    $temp_active_info = array();
    foreach ($this_player->player_robots AS $token => $info){
        $temp_robot = rpg_game::get_robot($this_battle, $this_player, $info);
        if ($info['robot_status'] == 'active'){ $temp_robot->robot_frame = 'base'; }
        if ($info['robot_position'] == 'active'){ $temp_active_info = $info;  }
        $temp_robot->update_session();
    }
    // If something was found, upload the reload for this robot
    if (!empty($temp_active_info)){
        $temp_this_reload_robot['robot_id'] = $temp_active_info['robot_id'];
        $temp_this_reload_robot['robot_token'] = $temp_active_info['robot_token'];
    }
}

// If target robot is not active, check to see if there's one that is
if ($target_robot->robot_position != 'active'){
    // Define the variable to hold active robot info and search for it
    $temp_active_info = array();
    foreach ($target_player->player_robots AS $token => $info){
        $temp_robot = rpg_game::get_robot($this_battle, $target_player, $info);
        if ($info['robot_status'] == 'active'){ $temp_robot->robot_frame = 'base'; }
        if ($info['robot_position'] == 'active'){ $temp_active_info = $info;  }
        $temp_robot->update_session();
    }
    // If something was found, upload the reload for this robot
    if (!empty($temp_active_info)){
        $temp_target_reload_robot['robot_id'] = $temp_active_info['robot_id'];
        $temp_target_reload_robot['robot_token'] = $temp_active_info['robot_token'];
    }
    // Update the player session
    $target_player->update_session();
}

// Refresh the settings for both robots with recent changes
$this_robot = rpg_game::get_robot($this_battle, $this_player, $temp_this_reload_robot);
$target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_target_reload_robot);

// Ensure the battle is still in progress
if (empty($this_redirect) && $this_battle->battle_status != 'complete'){

    // Require the option menu markup
    require_once('battle/menus/option.php');

    // Require the ability menu markup
    $temp_player_ability_actions = array();
    require_once('battle/menus/ability.php');

    // Require the item menu markup
    $temp_player_item_actions = array();
    require_once('battle/menus/item.php');

    // Require the switch actions
    require_once('battle/menus/switch.php');

    // Require the target menu markup
    require_once('battle/menus/target_this.php');
    require_once('battle/menus/target_this_disabled.php');
    require_once('battle/menus/target_target.php');

    // Require the scan menu markup
    require_once('battle/menus/scan.php');

    // Require the battle menu markup
    require_once('battle/menus/battle.php');

}
// Otherwise, if the battle has ended
elseif (empty($this_redirect) && $this_battle->battle_status == 'complete'){

    // Require the option menu markup
    require_once('battle/menus/option.php');

    // Require the complete menu markup
    require_once('battle/menus/complete.php');

}

// If possible, attempt to save the game to the session with recent changes
//if (!empty($this_save_filepath)){  // Why were we writing to the database/file soooo often?
if (!empty($this_save_filepath) && $this_battle->battle_status == 'complete'){
    // Save the game session
    mmrpg_save_game_session($this_save_filepath);
}

// Determine the next action based on everything that's happened
if (empty($this_redirect)){
    $this_next_action = 'battle';
    if (($this_robot->robot_status == 'disabled' || $this_robot->robot_position != 'active')
        && $this_battle->battle_status != 'complete'){
        $this_next_action = 'switch';
    }
}

// Define the canvas refresh flag
$canvas_refresh = false;

// Search for an active target robot to update the engine with
$active_target_robot = false;
foreach ($target_player->player_robots AS $temp_robotinfo){
    if (empty($active_target_robot) && $temp_robotinfo['robot_position'] == 'active'){
        $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robotinfo);
        if ($active_target_robot->robot_energy < 1){
            $active_target_robot->flags['apply_disabled_state'] = true;
            $active_target_robot->flags['hidden'] = true;
            $active_target_robot->robot_status = 'disabled';
            $active_target_robot->update_session();
            $canvas_refresh = true;
        }
    } elseif (!empty($active_target_robot) && $temp_robotinfo['robot_position'] == 'active'){
        $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robotinfo);
        $temp_target_robot->robot_position = 'bench';
        $temp_target_robot->update_session();
        $canvas_refresh = true;
        if ($temp_target_robot->robot_energy < 1){
            $temp_target_robot->flags['apply_disabled_state'] = true;
            $temp_target_robot->flags['hidden'] = true;
            $temp_target_robot->robot_status = 'disabled';
            $temp_target_robot->update_session();
            $canvas_refresh = true;
        }
    }

}
if (empty($active_target_robot)){
    $temp_robots_active_array = $target_player->values['robots_active'];
    $temp_robots_disabled_array = $target_player->values['robots_disabled'];
    if (!empty($temp_robots_active_array)){
        $temp_robots_active_info = array_shift($temp_robots_active_array);
        $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robots_active_info);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
    } elseif (!empty($temp_robots_disabled_array)){
        $temp_robots_active_info = array_shift($temp_robots_disabled_array);
        $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robots_active_info);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
    } else {
        $active_target_robot = $target_robot;
    }
}

// If the active robot was not the same as the target, update
if (!empty($active_target_robot) && $active_target_robot->robot_id != $target_robot->robot_id){
    $target_robot = $active_target_robot;
    $canvas_refresh = true;
}

// If canvas refresh is needed, create an empty event
if ($canvas_refresh && $this_battle->battle_status != 'complete'){ $this_battle->events_create(false, false, '', ''); }

// Stop the output buffer and collect contents
$output_buffer_contents = trim(ob_get_clean());
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Data API | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<style type="text/css">
</style>
<script type="text/javascript">
// Ensure this script is loaded via iframe
if (window != window.top){

    // Ensure the parent window knows we've returned
    parent.gameEngineSubmitReturn = true;

    // Redirect the parent window if necessary
    <?if(!empty($this_redirect)):?>
    parent.window.location.href = '<?=$this_redirect?>';
    <?else:?>

    // Update the global battle engine variables
    parent.mmrpg_engine_update({
        this_battle_id : '<?= $this_battle->battle_id ?>',
        this_battle_token : '<?= $this_battle->battle_token ?>',
        this_field_id : '<?= $this_field->field_id ?>',
        this_field_token : '<?= $this_field->field_token ?>',
        this_player_id : '<?= $this_player->player_id ?>',
        this_player_token : '<?= $this_player->player_token ?>',
        this_robot_id : '<?= $this_robot->robot_id ?>',
        this_robot_token : '<?= $this_robot->robot_token ?>',
        target_player_id : '<?= $target_player->player_id ?>',
        target_player_token : '<?= $target_player->player_token ?>',
        target_robot_id : '<?= $target_robot->robot_id ?>',
        target_robot_token : '<?= $target_robot->robot_token ?>',
        this_battle_status : '<?= $this_battle->battle_status ?>',
        this_battle_result : '<?= $this_battle->battle_result ?>',
        next_action : '<?= $this_next_action ?>'
        });
    <?

        // If action markup exists, loop through it
        if (!empty($actions_markup)){
            // Update any action panel markup changed by the battle
            foreach($actions_markup AS $action_token => $action_markup){
                $action_markup = str_replace("'", "\\'", $action_markup);
                echo "parent.mmrpg_action_panel_update('{$action_token}', '{$action_markup}');\n";
            }
        }

        // Collect event markup from the battle object
        $events_markup = $this_battle->events_markup_collect();

        // If event markup exists, loop through it
        if (!empty($events_markup)){
            //Print out any event markup generated by the battle
            foreach($events_markup AS $markup){
                $flags_markup = str_replace("'", "\\'", $markup['flags']);
                $data_markup = str_replace("'", "\\'", $markup['data']);
                $canvas_markup = str_replace("'", "\\'", $markup['canvas']);
                $console_markup = str_replace("'", "\\'", $markup['console']);
                echo "parent.mmrpg_event('{$flags_markup}', '{$data_markup}', '{$canvas_markup}', '{$console_markup}');\n";
            }
        }

        // Attempt to print out the events either way
        echo "parent.setTimeout(function(){ return parent.mmrpg_events(); }, parent.gameSettings.eventTimeout);\n";

    ?>
    <?endif;?>
}
<?
// DEBUG
// If output buffer content was created, alert the webmaster of its content
if (!empty($output_buffer_contents) && (!MMRPG_CONFIG_IS_LIVE && MMRPG_CONFIG_ADMIN_MODE)){
    $output_buffer_contents = str_replace("\\", '\\', $output_buffer_contents);
    $output_buffer_contents = str_replace("\n", '\n', $output_buffer_contents);
    $output_buffer_contents = preg_replace('/\s+/', ' ', $output_buffer_contents);
    $output_buffer_contents = str_replace("'", "\'", $output_buffer_contents);
    $output_buffer_contents = strip_tags($output_buffer_contents);
    echo "alert('".$output_buffer_contents."');";
}
?>
<?
/*
// TEMP DEBUG
if (!MMRPG_CONFIG_IS_LIVE || MMRPG_CONFIG_ADMIN_MODE){
    echo "console.log('memory_limit() = ".ini_get('memory_limit')."');\n";
    echo "console.log('memory_get_usage() = ".round(((memory_get_usage() / 1024) / 1024), 2)."M');\n";
    echo "console.log('memory_get_peak_usage() = ".round((memory_get_peak_usage() / 1024) / 1024, 2)."M');\n";
    //echo 'memory_get_peak_usage_peak() = '.memory_get_peak_usage_peak().'<br />';
}
*/
?>
</script>
</head>
<body>
<?
// Unset the database variable
unset($db);
?>
</body>
</html>