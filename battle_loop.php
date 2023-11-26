<?php
// Include the TOP file
require_once('top.php');

// For this script, because it's high priority, we increase the memory limit
ini_set('memory_limit', '256M');

// Never try to display errors during the battle loop, always log to file instead
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('ignore_repeated_errors', 1);
ini_set('log_errors', 1);

// If the user is not logged in, don't allow them here
if (!rpg_game::is_user()){
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8" />
    <title>Login Error! | Mega Man RPG Prototype</title>
    <base href="<?=MMRPG_CONFIG_ROOTURL?>" />
    </head>
    <body>
        <script type="text/javascript">
            alert('You have been logged out due to inactivity or an action \n in another window! Redirecting to login page...');
            if (window != window.top){ window.top.location.href = 'prototype/'; }
            else { window.location.href = 'prototype/'; }
        </script>
    </body>
    </html>
    <?
    exit();
}

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
$this_user_id = isset($_REQUEST['this_user_id']) ? $_REQUEST['this_user_id'] : rpg_user::get_current_userid();
$this_player_id = isset($_REQUEST['this_player_id']) ? $_REQUEST['this_player_id'] : $this_user_id;
$this_player_token = isset($_REQUEST['this_player_token']) ? $_REQUEST['this_player_token'] : 'player';
$this_player_robots = !empty($_REQUEST['this_player_robots']) ? $_REQUEST['this_player_robots'] : '00_robot';
$this_robot_id = isset($_REQUEST['this_robot_id']) ? $_REQUEST['this_robot_id'] : 1;
$this_robot_token = isset($_REQUEST['this_robot_token']) ? $_REQUEST['this_robot_token'] : 'robot';
$target_user_id = isset($_REQUEST['target_user_id']) ? $_REQUEST['target_user_id'] : MMRPG_SETTINGS_TARGET_PLAYERID;
$target_player_id = isset($_REQUEST['target_player_id']) ? $_REQUEST['target_player_id'] : $target_user_id;
$target_player_token = isset($_REQUEST['target_player_token']) ? $_REQUEST['target_player_token'] : 'player';
//$target_player_robots = !empty($_REQUEST['target_player_robots']) ? $_REQUEST['target_player_robots'] : '00_robot';
$target_robot_id = isset($_REQUEST['target_robot_id']) ? $_REQUEST['target_robot_id'] : 2;
$target_robot_token = isset($_REQUEST['target_robot_token']) ? $_REQUEST['target_robot_token'] : 'robot';

// Define the current action request variables
$this_action = isset($_REQUEST['this_action']) ? $_REQUEST['this_action'] : 'start';
$this_action_token = isset($_REQUEST['this_action_token']) ? $_REQUEST['this_action_token'] : '';
$target_action = isset($_REQUEST['target_action']) ? $_REQUEST['target_action'] : 'start';
$target_action_token = isset($_REQUEST['target_action_token']) ? $_REQUEST['target_action_token'] : '';

// If the player is not starting, the target shouldn't either
if ($target_action == 'start' && $this_action != 'start'){ $target_action = 'ability'; }

// If this is a "next" action w/ a substring, we need to break it apart
if (substr($this_action, 0, 5) == 'next_'){ list($this_action, $this_action_token) = explode('_', $this_action); }

// Define a variable to track the verified state and any errors in data processing
$this_verified = true;
$this_errors = array();

// Ensure all madatory variables were set, and create errors for missing parameters
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
        '$this_player_id = '.$this_player_id.'; $this_player_token = '.$this_player_token.';';
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
    $_SESSION['SKILLS'] = array();

    // Restore any dropped items to their owners if able to
    mmrpg_prototype_restore_dropped_items(array(
        'this_battle_token' => $this_battle_token
        ));

}

/*
 * DEFINE & INITALIZE BATTLE OBJECTS
 */

// Define the battle object using the loaded battle data
$this_battleinfo = array('battle_id' => $this_battle_id, 'battle_token' => $this_battle_token);

// Define the current field object using the loaded field data
if (!strstr($this_field_id, 'x')){ $this_field_id = rpg_game::unique_field_id($this_battle_id, $this_field_id); }
$this_fieldinfo = array('field_id' => $this_field_id, 'field_token' => $this_field_token);

// Define the battle object using the loaded battle data and update session
$this_battle = rpg_game::get_battle($this_battleinfo);
$this_battle->flags['wap'] = $flag_wap ? true : false;
$this_battle->update_session();

// Define the current field object using the loaded field data and update session
if ($this_action !== 'start'){
    $this_field = rpg_game::get_field($this_battle, $this_fieldinfo);
    $this_field->update_session();
}

// Define the current player object using the loaded player data
$this_user_id = rpg_user::get_current_userid();
$this_playerinfo = array(
    'user_id' => $this_user_id,
    'player_id' => $this_player_id,
    'player_token' => $this_player_token,
    'player_autopilot' => false
    );
$this_playerinfo['player_autopilot'] = false;
$this_playerinfo['player_side'] = 'left';
// Define the target player object using the loaded player data
$target_playerinfo = array(
    'user_id' => $target_user_id,
    'player_id' => $target_player_id,
    'player_token' => $target_player_token
    );
$target_playerinfo['player_autopilot'] = true;
$target_playerinfo['player_side'] = 'right';

// Make sure the current and target player info are always available in the battle
if (!isset($this_battle->values['battle_players'])
    || empty($this_battle->values['battle_players'])){

    // Check to see if there are already any ENDLESS ATTACK MODE sessions in progress
    $is_endless_battle = !empty($this_battle->flags['challenge_battle']) && !empty($this_battle->flags['endless_battle']) ? true : false;
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions($this_playerinfo['player_token']);
    //error_log('we are in battle with player '.$this_playerinfo['player_token'].' and have '.print_r(array_keys($endless_attack_savedata), true));
    if (!isset($this_playerinfo['flags'])){ $this_playerinfo['flags'] = array(); }
    if (!$is_endless_battle
        && !empty($endless_attack_savedata)){
        //error_log($this_playerinfo['player_token'].' is currently in endless attack mode');
        $this_playerinfo['flags']['player_disabled'] = true;
        $this_playerinfo['player_image'] = 'player';
    }

    // Add the start data for this and the target player into the battle itself
    $temp_battle_players = array(
        'this_player' => $this_playerinfo,
        'target_player' => $target_playerinfo
        );

    // Make sure we leave a reference to the start robots on each side for later
    $temp_player_robot_string = $this_player_robots;
    $temp_target_robot_string = implode(',', array_map(function($r){ return $r['robot_id'].'_'.$r['robot_token']; }, $this_battle->battle_target_player['player_robots']));
    $temp_battle_players['this_player']['player_robots'] = $this_player_robots;
    $temp_battle_players['target_player']['player_robots'] = $temp_target_robot_string;

    // And finally, it's important to check if the player(s) are visible or not
    $temp_battle_players['this_player']['player_visible'] = true;
    $temp_battle_players['target_player']['player_visible'] = false;
    if (!empty($this_playerinfo['flags']['player_disabled'])){ $temp_battle_players['this_player']['player_visible'] = false; }
    if ($temp_battle_players['target_player']['player_token'] !== 'player'){ $temp_battle_players['target_player']['player_visible'] = true; }

    // Update the battle values with the calculated cached player info
    $this_battle->values['battle_players'] = $temp_battle_players;

}

// Only create the arrays with minimal info if NOT start
if ($this_action != 'start'){

    // Define the current player object using the loaded player data and update session
    $this_player = rpg_game::get_player($this_battle, $this_playerinfo);

    // Define the target player object using the loaded player data and update session
    $target_player = rpg_game::get_player($this_battle, $target_playerinfo);

    // Make sure the two players reference each other internally
    $this_player->other_player = $target_player;
    $target_player->other_player = $this_player;

    // Remove temporary flags that might prevent actions
    unset($this_player->flags['switched_this_turn']);
    unset($target_player->flags['switched_this_turn']);

}
// Otherwise, prepopulate their robot arrays
elseif ($this_action == 'start'){

    // Break apart and filter this player's robots
    $this_playerinfo['player_robots'] = array();
    $temp_this_player_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
    foreach ($temp_this_player_robots AS $temp_string){
        list($temp_id, $temp_token) = explode('_', $temp_string);
        $temp_settings = mmrpg_prototype_robot_settings($this_playerinfo['player_token'], $temp_token);
        $temp_abilities = !empty($temp_settings['robot_abilities']) ? array_keys($temp_settings['robot_abilities']) : array();
        $this_playerinfo['player_robots'][] = array('robot_id' => $temp_id, 'robot_token' => $temp_token, 'robot_abilities' => $temp_abilities);
        unset($temp_settings, $temp_abilities);
    }

    // Collect any important player details over
    if (!empty($this_battle->battle_target_player['user_id'])){
        $target_playerinfo['user_id'] = $this_battle->battle_target_player['user_id'];
    }
    if (!empty($this_battle->battle_target_player['player_controller'])){
        $target_playerinfo['player_controller'] = $this_battle->battle_target_player['player_controller'];
    }
    if (!empty($this_battle->battle_target_player['player_switch'])){
        $target_playerinfo['player_switch'] = $this_battle->battle_target_player['player_switch'];
    }
    if (!empty($this_battle->battle_target_player['player_name'])){
        $target_playerinfo['player_name'] = $this_battle->battle_target_player['player_name'];
    }
    if (!empty($this_battle->battle_target_player['player_type'])){
        $target_playerinfo['player_type'] = $this_battle->battle_target_player['player_type'];
    }
    if (!empty($this_battle->battle_target_player['player_image'])){
        $target_playerinfo['player_image'] = $this_battle->battle_target_player['player_image'];
    }
    if (!empty($this_battle->battle_target_player['player_quotes'])){
        $target_playerinfo['player_quotes'] = $this_battle->battle_target_player['player_quotes'];
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

// Collect an index of all players so we can use later
$battle_players_index = rpg_player::get_index(true);
$mmrpg_index_players = &$battle_players_index;

// Collect an index of all types so we can user later
$battle_types_index = rpg_type::get_index(true, false, true);
$mmrpg_index_types = &$battle_types_index;

// Collect an index of all complete robots so we can use later
$battle_robots_index = rpg_robot::get_index(true);
$mmrpg_index_robots = &$battle_robots_index;

// If this is the START action, update objects with preset battle data fields
if ($this_action == 'start'){

    // Start the battle turn off at zero
    $this_battle->counters['battle_turn'] = 0;

    // Update applicable fieldinfo fields with preset battle data
    if (!empty($this_battle->battle_field_base)){
        $backup_field_id = $this_fieldinfo['field_id'];
        $this_fieldinfo = array_replace($this_fieldinfo, $this_battle->battle_field_base);
        $this_fieldinfo['field_id'] = $backup_field_id;
        $this_field = rpg_game::get_field($this_battle, $this_fieldinfo);
        $this_field->update_session();
    }

    // Check to see if experience points are relevant for this battle
    $allow_experience_multipliers = true;
    if (!empty($this_battle->flags['challenge_battle'])
        || !empty($this_battle->flags['endless_battle'])
        || !empty($this_battle->flags['player_battle'])){
        $allow_experience_multipliers = false;
    }
    //error_log('$allow_experience_multipliers = '.($allow_experience_multipliers ? 'true' : 'false'));

    // If this battle has chain power, make sure we apply it to the field multipliers
    if (!empty($this_battle->battle_chain_power)){
        //error_log('starting a battle with CHAIN POWER!');

        // Give an upfront experience boost based on chain power (if allowed)
        if ($allow_experience_multipliers){
            $base_experience_multiplier = $this_field->get_multiplier('experience');
            //error_log('$base_experience_multiplier = '.$base_experience_multiplier);
            $new_experience_multiplier = $base_experience_multiplier + ($this_battle->battle_chain_power / 10);
            if ($new_experience_multiplier > MMRPG_SETTINGS_MULTIPLIER_MAX){ $new_experience_multiplier = MMRPG_SETTINGS_MULTIPLIER_MAX; }
            //error_log('$new_experience_multiplier = '.$new_experience_multiplier);
            $this_field->set_multiplier('experience', $new_experience_multiplier);
            $this_field->set_base_multiplier('experience', $new_experience_multiplier);
        }

    }

    // Ensure the player's robot string was provided
    if (!empty($this_player_robots)){

        // Pre-collect any preload data we have access to
        $temp_robots_preload = array();
        if (!empty($_SESSION['ROBOTS_PRELOAD'][$this_battle->battle_token])){ $temp_robots_preload = $_SESSION['ROBOTS_PRELOAD'][$this_battle->battle_token]; }
        //error_log('$temp_robots_preload = '.print_r($temp_robots_preload, true));

        // Precreate the player object using the newly defined details
        $backup_this_playerinfo = $this_playerinfo;
        $backup_this_playerinfo['player_robots'] = array();
        $this_player = rpg_game::get_player($this_battle, $backup_this_playerinfo);
        unset($backup_this_playerinfo);
        // Break apart the allowed robots string and unset undefined robots
        //$this_playerinfo = $this_player->export_array();
        //$this_playerinfo_robots = array();
        //die('<pre>after:'.print_r($debug['player_robots'], true).'</pre>');
        $this_key_counter = 0;
        $this_team_string = array();
        $temp_this_player_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
        $temp_this_player_robots_ids = array();
        foreach ($this_playerinfo['player_robots'] AS $this_key => $this_data){
            if (!mmrpg_prototype_robot_unlocked($this_player_token, $this_data['robot_token'])){ continue; }
            $this_info = rpg_robot::parse_index_info($battle_robots_index[$this_data['robot_token']]);
            if (in_array($this_data['robot_id'], $temp_this_player_robots_ids)){ continue; }
            else { $temp_this_player_robots_ids[] = $this_data['robot_id']; }
            $this_token = $this_data['robot_id'].'_'.$this_data['robot_token'];
            $this_position = array_search($this_token, $temp_this_player_robots);
            $this_preload = !empty($temp_robots_preload[$this_token]) ? $temp_robots_preload[$this_token] : array();
            $this_data['robot_key'] = $this_key_counter;
            $this_data['robot_experience'] = mmrpg_prototype_robot_experience($this_playerinfo['player_token'], $this_data['robot_token']);
            $this_data['robot_level'] = mmrpg_prototype_robot_level($this_playerinfo['player_token'], $this_data['robot_token']);
            if (isset($this_preload['robot_persona'])){
                $this_data['robot_persona'] = $this_preload['robot_persona'];
                $this_data['robot_persona_image'] = !empty($this_preload['robot_persona_image']) ? $this_preload['robot_persona_image'] : $this_preload['robot_persona'];
            }
            // Only allow this robot if it exists in the robot string
            if ($this_position !== false){
                // Create the temporary robot object to load data
                $this_data = array_merge($this_info, $this_data);
                $temp_robot = $this_player->load_robot($this_data, $this_key_counter, true, true);
                $this_player->update_session();
                $this_key_counter++;
                $this_string = !empty($temp_robot->robot_image) ? $temp_robot->robot_image : $temp_robot->robot_token;
                if (!empty($temp_robot->robot_item)){ $this_string .= '@'.$temp_robot->robot_item; }
                $this_team_string[] = $this_string;
                unset($temp_robot);
            }
        }
        // Update the player session with changes
        $this_player->counters['robots_start_total'] = $this_key_counter;
        $this_player->values['robots_start_team'] = $this_team_string;
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
        //strstr($target_player_robots, ',') ? explode(',', $target_player_robots) : array($target_player_robots);
        //$target_playerinfo = $target_player->export_array();
        $target_key_counter = 0;
        $target_team_string = array();
        $target_playerinfo_robots = array();
        $temp_target_player_robots_ids = array();
        foreach ($target_playerinfo['player_robots'] AS $this_key => $this_data){
            $this_info = rpg_robot::parse_index_info($battle_robots_index[$this_data['robot_token']]);
            if (in_array($this_data['robot_id'], $temp_target_player_robots_ids)){ continue; }
            else { $temp_target_player_robots_ids[] = $this_data['robot_id']; }
            $this_token = $this_data['robot_id'].'_'.$this_data['robot_token'];
            $this_position = $this_key; //array_search($this_token, $target_player_robots);
            $this_data['robot_key'] = $target_key_counter;
            // Only allow this robot if it exists in the robot string
            if ($this_position !== false){
                // Create the temporary robot object to load data
                $this_data = array_merge($this_info, $this_data);
                $target_player->player_robots[$this_key] = $this_data;
                $temp_robot = $target_player->load_robot($this_data, $target_key_counter, true, true);
                $target_key_counter++;
                $target_string = !empty($temp_robot->robot_image) ? $temp_robot->robot_image : $temp_robot->robot_token;
                if (!empty($temp_robot->robot_item)){ $target_string .= '@'.$temp_robot->robot_item; }
                $target_team_string[] = $target_string;
                unset($temp_robot);
            }
        }
        // Update the player session with changes
        $target_player->counters['robots_start_total'] = $target_key_counter;
        $target_player->values['robots_start_team'] = $target_team_string;
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

    // Make sure the two players reference each other internally
    $this_player->other_player = isset($target_player) ? $target_player : false;
    $target_player->other_player = isset($this_player) ? $this_player : false;

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
// Else if the player is has requested to withdraw from the battle (endless mode)
elseif ($this_action == 'withdraw'){

    // Require the giveup action file
    require_once('battle/actions/withdraw.php');

}
// Else if the player is has requested to restart the battle
elseif ($this_action == 'restart'
    || $this_action == 'restart_with-rotate'
    || $this_action == 'restart_whole-mission'){

    // Require the restart action file
    require_once('battle/actions/restart.php');

}
// Else if the player is has requested the next battle in sequence
elseif ($this_action == 'next'){

    // Require the next action file
    require_once('battle/actions/next.php');

}
// Else if the player is just starting the battle, queue start actions
elseif ($this_action == 'start'){

    // Require the start action file
    require_once('battle/actions/start.php');

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

    // Require the ability-item action file (player uses item, target uses ability)
    require_once('battle/actions/ability_item.php');

}
// Else if the player is switching robots, they go first
elseif ($this_action == 'switch'){

    // Require the ability-switch action file (player uses switch, target uses ability)
    require_once('battle/actions/ability_switch.php');

}

// Re-collect this robot if different from the "current" one in this player's values
$this_player->player_reload();
if (!empty($this_player->values['current_robot'])){
    //$this_battle->events_create(false, false, 'debug', 'battle-loop on line '.__LINE__.' our $this_player->values[\'current_robot\'] = '.$this_player->values['current_robot']);
    list($id, $token) = explode('_', $this_player->values['current_robot']);
    if ($this_robot->robot_id !== $id){
        unset($this_robot);
        $this_robot = rpg_game::get_robot($this_battle, $this_player, array(
            'robot_id' => $id,
            'robot_token' => $token
            ));
    }
}

// Re-collect target robot if different from the "current" one in target player's values
$target_player->player_reload();
if (!empty($target_player->values['current_robot'])){
    //$this_battle->events_create(false, false, 'debug', 'battle-loop on line '.__LINE__.' our $target_player->values[\'current_robot\'] = '.$target_player->values['current_robot']);
    list($id, $token) = explode('_', $target_player->values['current_robot']);
    if ($target_robot->robot_id !== $id){
        unset($target_robot);
        $target_robot = rpg_game::get_robot($this_battle, $target_player, array(
            'robot_id' => $id,
            'robot_token' => $token
            ));
    }
}

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
    foreach ($battle_players_index AS $player_token => $player_info){
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
        $temp_this_reload_robot = array();
        $temp_this_reload_robot['robot_id'] = $temp_active_info['robot_id'];
        $temp_this_reload_robot['robot_token'] = $temp_active_info['robot_token'];
    }
    // Refresh the settings for this robot with recent changes
    $this_robot = rpg_game::get_robot($this_battle, $this_player, $temp_this_reload_robot);
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
        $temp_target_reload_robot = array();
        $temp_target_reload_robot['robot_id'] = $temp_active_info['robot_id'];
        $temp_target_reload_robot['robot_token'] = $temp_active_info['robot_token'];
    }
    // Update the player session
    $target_player->update_session();
    // Refresh the settings for target robot with recent changes
    $target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_target_reload_robot);
}

// Ensure the battle is still in progress
if (empty($this_redirect) && $this_battle->battle_status != 'complete'){

    // Remove any temp flags from the players
    if ($this_action != 'scan'
        && $this_action != 'switch'){
        unset($this_player->flags['item_used_this_turn']);
        unset($target_player->flags['item_used_this_turn']);
    }

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
    require_once('battle/menus/target_this_ally.php');
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
if ($this_battle->battle_status == 'complete'){
    // Save the game session
    mmrpg_save_game_session();
}

// Determine the next action based on everything that's happened
if (empty($this_redirect)){
    $this_next_action = 'battle';
    $has_active_robot = rpg_game::find_robot(array(
        'player_id' => $this_player->player_id,
        'robot_position' => 'active',
        'robot_status' => 'active'
        ));
    if ((!$has_active_robot || $this_robot->robot_status == 'disabled' || $this_robot->robot_position != 'active')
        && $this_battle->battle_status != 'complete'){
        $this_next_action = 'switch';
    }
}

// Define the canvas refresh flag
$canvas_refresh = false;

// Search for an active target robot to update the engine with
$active_target_robot = false;
if ($target_robot->robot_position !== 'active'){
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
} else {
    $active_target_robot = $target_robot;
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

// -- END OF TURN ACTIONS -- //

// If this is a challenge battle, turns must be respected
if (!empty($this_battle->counters['battle_turn']) && $this_battle->battle_status != 'complete'){
    if (!empty($this_battle->flags['challenge_battle'])
        && empty($this_battle->flags['endless_battle'])){
        if ($this_battle->counters['battle_turn'] >= $this_battle->battle_turns){

            // Trigger the battle complete event
            $this_battle->battle_result = 'defeat';
            $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');
            $this_next_action = 'battle';

            // Now execute the stored actions (and any created in the process of executing them!)
            $this_battle->actions_execute();
            $canvas_refresh = true;

            // Otherwise, if the battle has ended due to the above
            if ($this_battle->battle_status == 'complete'){

                // Require the option menu markup
                require_once('battle/menus/option.php');

                // Require the complete menu markup
                require_once('battle/menus/complete.php');

                // Save the game session
                mmrpg_save_game_session();

            }

        }
    }
}

// If this is a normal CHALLENGE BATTLE, we might need to update records
if (!empty($this_battle->flags['challenge_battle'])
    && !empty($this_battle->values['challenge_battle_id'])
    && empty($this_battle->flags['endless_battle'])){

    // Ensure we have the correct user ID before proceeding
    $this_user_id = rpg_user::get_current_userid();

    // Collect the challenge battle ID for reference
    $challenge_kind = $this_battle->values['challenge_battle_kind'];
    if ($challenge_kind === 'user'){
        $challenge_xid = (int)(substr($this_battle->values['challenge_battle_id'], 1));
        $challenge_table = 'mmrpg_users_challenges';
        $challenge_leaderboard_table = 'mmrpg_users_challenges_leaderboard';
    } else {
        $challenge_xid = (int)($this_battle->values['challenge_battle_id']);
        $challenge_table = 'mmrpg_challenges';
        $challenge_leaderboard_table = 'mmrpg_challenges_leaderboard';
    }


    //$this_battle->events_create(false, false, 'debug', '$challenge_xid = '.preg_replace('/\s+/', ' ', print_r($challenge_xid, true)).'<br />');
    //$this_battle->events_create(false, false, 'debug', '$this_battle->battle_status = '.preg_replace('/\s+/', ' ', print_r($this_battle->battle_status, true)).'<br />');
    //$this_battle->events_create(false, false, 'debug', '$this_battle->battle_result = '.preg_replace('/\s+/', ' ', print_r($this_battle->battle_result, true)).'<br />');

    // Update the "accessed" count if we're at the battle start action
    if ($this_action === 'start'
        && empty($this_battle->flags['challenge_battle_accessed'])){
        $db->query("UPDATE {$challenge_table} SET challenge_times_accessed = (challenge_times_accessed + 1) WHERE challenge_id = {$challenge_xid};");
        $this_battle->flags['challenge_battle_accessed'] = true;
    }

    // Update the "concluded" count if we're at the end of the mission (regardless of result)
    if (empty($this_redirect)
        && $this_action !== 'start'
        && $this_action !== 'restart'
        && $this_action !== 'prototype'
        && $this_action !== 'next'
        && $this_battle->battle_status == 'complete'
        && empty($this_battle->flags['challenge_battle_concluded'])){

        // Update the concluded flag regardless and count of the actual result
        $db->query("UPDATE {$challenge_table} SET challenge_times_concluded = (challenge_times_concluded + 1) WHERE challenge_id = {$challenge_xid};");
        $this_battle->flags['challenge_battle_concluded'] = true;

        // Now update the victory or defeat counters based on the result of the battle
        if ($this_battle->battle_result == 'defeat'){

            // Update the defeat counter for this challenge and be done with it
            $db->query("UPDATE {$challenge_table} SET challenge_user_defeats = (challenge_user_defeats + 1) WHERE challenge_id = {$challenge_xid};");
        } elseif ($this_battle->battle_result == 'victory'){

            // Update the victory counter for this challenge before we update leaderboard
            $db->query("UPDATE {$challenge_table} SET challenge_user_victories = (challenge_user_victories + 1) WHERE challenge_id = {$challenge_xid};");

            // Define the base variables for this particular victory
            $temp_challenge_kind = $this_battle->values['challenge_battle_kind'];
            $temp_player_turns_used = $this_battle->counters['battle_turn'];
            $temp_player_robots_used = !empty($this_player->counters['robots_start_total']) ? $this_player->counters['robots_start_total'] : 0;
            $temp_target_turn_limit = !empty($this_battle->battle_turns) ? $this_battle->battle_turns : 99;
            $temp_target_robot_limit = !empty($this_battle->battle_robot_limit) ? $this_battle->battle_robot_limit : $target_player->counters['robots_start_total'];
            $temp_zenny_earned = !empty($this_battle->counters['final_zenny_reward']) ? $this_battle->counters['final_zenny_reward'] : 0;

            // Check to see if this user already has a record on the challenge leaderboard
            $existing_db_row = $db->get_array("SELECT
                board_id,
                challenge_turns_used,
                challenge_robots_used,
                challenge_zenny_earned
                FROM {$challenge_leaderboard_table}
                WHERE
                challenge_id = {$challenge_xid}
                AND user_id = {$this_user_id}
                ;");
            $db_common_fields = array();
            $db_common_fields['challenge_turns_used'] = $temp_player_turns_used;
            $db_common_fields['challenge_robots_used'] = $temp_player_robots_used;
            $db_common_fields['challenge_zenny_earned'] = $temp_zenny_earned;

            // Calculate the earned points and rank for this run based on used turns and robots vs goal
            $new_points = rpg_mission_challenge::calculate_challenge_reward_points($temp_challenge_kind, array(
                'challenge_turns_used' => $temp_player_turns_used,
                'challenge_turn_limit' => $temp_target_turn_limit,
                'challenge_robots_used' => $temp_player_robots_used,
                'challenge_robot_limit' => $temp_target_robot_limit
                ), $new_percent, $new_rank);

            /*
            error_log('NEW results = '.print_r(array(
                'challenge_turns_used' => $temp_player_turns_used,
                'challenge_turn_limit' => $temp_target_turn_limit,
                'challenge_robots_used' => $temp_player_robots_used,
                'challenge_robot_limit' => $temp_target_robot_limit
                ), true).' '.PHP_EOL.
                ' $new_points = '.$new_points.
                ' | $new_percent = '.$new_percent.
                ' | $new_rank = '.$new_rank
                );
            */

            // Check to see if an existing row exists to update before inserting
            if (!empty($existing_db_row)){

                // Calculate the earned points and rank for the previous run so we can compare to current
                $old_points = rpg_mission_challenge::calculate_challenge_reward_points($temp_challenge_kind, array(
                    'challenge_turns_used' => $existing_db_row['challenge_turns_used'],
                    'challenge_turn_limit' => $temp_target_turn_limit,
                    'challenge_robots_used' => $existing_db_row['challenge_robots_used'],
                    'challenge_robot_limit' => $temp_target_robot_limit
                    ), $old_percent, $old_rank);

                /*
                error_log('OLD results = '.print_r(array(
                    'challenge_turns_used' => $existing_db_row['challenge_turns_used'],
                    'challenge_turn_limit' => $temp_target_turn_limit,
                    'challenge_robots_used' => $existing_db_row['challenge_robots_used'],
                    'challenge_robot_limit' => $temp_target_robot_limit
                    ), true).' '.PHP_EOL.
                    ' $old_points = '.$old_points.
                    ' | $old_percent = '.$old_percent.
                    ' | $old_rank = '.$old_rank
                    );
                */

                // If new points are higher, we can update record, else just update access time
                if ($new_points >= $old_points){ $update_fields = $db_common_fields; }
                else { $update_fields = array(); }
                $update_fields['challenge_date_lastclear'] = time();
                $condition_data = array();
                $condition_data['user_id'] = $this_user_id;
                $condition_data['challenge_id'] = $challenge_xid;
                $db->update($challenge_leaderboard_table, $update_fields, $condition_data);
                //error_log('$update_fields = '.print_r($update_fields, true).'<br />'.'$condition_data = '.print_r($condition_data, true));

            } else {

                // There are no records yet so we can just insert freely into the database
                $insert_fields = $db_common_fields;
                $insert_fields['user_id'] = $this_user_id;
                $insert_fields['challenge_id'] = $challenge_xid;
                $insert_fields['challenge_result'] = 'victory';
                $insert_fields['challenge_date_firstclear'] = time();
                $db->insert($challenge_leaderboard_table, $insert_fields);
                //error_log('$insert_fields = '.print_r($insert_fields, true));

            }

        }

    }

}
// Else If this is an ENDLESS ATTACK MODE battle, we might need to update records
if (!empty($this_battle->flags['challenge_battle'])
    && !empty($this_battle->flags['endless_battle'])){

    // Update the "concluded" count if we're at the end of the mission (regardless of result)
    if ($this_battle->battle_status == 'complete'
        && empty($this_battle->flags['challenge_battle_concluded'])
        && $this_action != 'next'){

        // Update the concluded flag regardless
        $this_battle->flags['challenge_battle_concluded'] = true;

        // Only update the waveboard with the results of victories
        if ($this_battle->battle_result == 'victory'){

            // Collect the current mission number so we now where we are
            $this_battle_chain = isset($_SESSION['BATTLES_CHAIN'][$this_battle->battle_chain_token]) ? $_SESSION['BATTLES_CHAIN'][$this_battle->battle_chain_token] : array();
            $this_loop_size = 18;
            $wave_value = MMRPG_SETTINGS_BATTLEPOINTS_PERWAVE;
            $this_wave_number = (!empty($this_battle_chain) ? count($this_battle_chain) : 0) + 1;
            $this_phase_number = floor($this_wave_number / $this_loop_size) + 1;
            $this_battle_number = $this_wave_number > $this_loop_size ? ($this_wave_number % $this_loop_size) : $this_wave_number;

            // Collect the start robot count and then tally the total turns taken
            $this_robot_used = 0;
            $this_turns_used = 0;
            $this_team_config = '';
            if (!empty($this_battle_chain)){
                $this_robot_used = $this_battle_chain[0]['battle_robots_used'];
                $this_team_config = $this_battle_chain[0]['battle_team_config'];
            } elseif (!empty($this_player->counters['robots_start_total'])){
                $this_robot_used = $this_player->counters['robots_start_total'];
                $this_team_config = $this_player->player_token.'::'.implode(',', $this_player->values['robots_start_team']);
            }
            if (!empty($this_battle_chain)){ foreach ($this_battle_chain AS $key => $record){ $this_turns_used += $record['battle_turns_used']; } }
            if (!empty($this_battle->counters['battle_turn'])){ $this_turns_used += $this_battle->counters['battle_turn']; }

            // Update the session with the new battle chain results
            $_SESSION['BATTLES_CHAIN'][$this_battle->battle_chain_token] = $this_battle_chain;

            // Check to see if there's an existing record for this user and this challenge
            $current_user_id = rpg_user::get_current_userid();
            $old_wave_record = $db->get_array("SELECT
                board_id,
                user_id,
                challenge_result,
                challenge_waves_completed,
                challenge_robots_used,
                challenge_turns_used,
                challenge_team_config,
                challenge_date_firstclear,
                challenge_date_lastclear
                FROM mmrpg_challenges_waveboard
                WHERE
                user_id = {$current_user_id}
                AND challenge_waves_completed > 0
                AND challenge_robots_used > 0
                AND challenge_turns_used > 0
                ;");

            // Define a variable to hold required action
            $this_required_action = false;

            // Only bother updating the db if this run is better in some way or new
            //error_log('Check if old wave exists...');
            if (empty($old_wave_record)){
                //error_log('Old wave NOT exists so we can insert new!');
                $this_required_action = 'insert';
            } elseif (!empty($old_wave_record)){
                //error_log('Old wave EXISTS so we might need to...');
                //$this_battle->events_create(false, false, 'DEBUG', '$old_wave_record = '.print_r($old_wave_record, true).'');
                // Collect the meta info for the old wave record
                $old_wave_number = (int)($old_wave_record['challenge_waves_completed']);
                $old_robots_used = (int)($old_wave_record['challenge_robots_used']);
                $old_turns_used = (int)($old_wave_record['challenge_turns_used']);
                // Calculate points given the old wave record
                $old_wave_record = array();
                $old_wave_record['challenge_points_base'] = $old_wave_number * $wave_value;
                $old_wave_record['challenge_points_robot_bonus'] = ceil($old_wave_record['challenge_points_base'] / $old_robots_used);
                $old_wave_record['challenge_points_turn_bonus'] = ceil($old_wave_record['challenge_points_base'] / ($old_turns_used / $old_wave_number));
                $old_wave_record['challenge_points_total'] = ceil($old_wave_record['challenge_points_base'] + $old_wave_record['challenge_points_robot_bonus'] + $old_wave_record['challenge_points_turn_bonus']);
                // Calculate points given the new wave record
                $new_wave_record = array();
                $new_wave_record['challenge_points_base'] = $this_wave_number * $wave_value;
                $new_wave_record['challenge_points_robot_bonus'] = ceil($new_wave_record['challenge_points_base'] / $this_robot_used);
                $new_wave_record['challenge_points_turn_bonus'] = ceil($new_wave_record['challenge_points_base'] / ($this_turns_used / $this_wave_number));
                $new_wave_record['challenge_points_total'] = ceil($new_wave_record['challenge_points_base'] + $new_wave_record['challenge_points_robot_bonus'] + $new_wave_record['challenge_points_turn_bonus']);
                //error_log('...$old_wave_record = '.print_r(array_map(function($n){ return number_format($n, 0, '.', ','); }, $old_wave_record), true));
                //error_log('...$new_wave_record = '.print_r(array_map(function($n){ return number_format($n, 0, '.', ','); }, $new_wave_record), true));
                // If the new points are higher than the old ones, we update
                if ($new_wave_record['challenge_points_total'] > $old_wave_record['challenge_points_total']){
                    $this_required_action = 'update';
                    //error_log('Update!');
                } else {
                    //error_log('Nevermind!');
                }
            }

            // Predefine the comments fields for insert or update
            $db_common_fields = array();
            $db_common_fields['challenge_waves_completed'] = $this_wave_number;
            $db_common_fields['challenge_robots_used'] = $this_robot_used;
            $db_common_fields['challenge_turns_used'] = $this_turns_used;
            $db_common_fields['challenge_team_config'] = $this_team_config;

            // Insert a new record if this is the first time clearing
            if ($this_required_action == 'insert'){
                //error_log('Insert the new record');

                // Define the insert fields for this row
                $db_insert_fields = $db_common_fields;
                $db_insert_fields['user_id'] = $current_user_id;
                $db_insert_fields['challenge_result'] = 'victory';
                $db_insert_fields['challenge_date_firstclear'] = time();
                $db_insert_fields['challenge_date_lastclear'] = time();
                $db->insert('mmrpg_challenges_waveboard', $db_insert_fields);
                //$this_battle->events_create(false, false, 'DEBUG', 'NEW $db_insert_fields = '.print_r($db_insert_fields, true).'');

            }
            // Otherwise update the existing record with the new score
            elseif ($this_required_action == 'update'){
                //error_log('Update the existing record');

                // Define the update fields for this row
                $db_update_fields = $db_common_fields;
                $db_update_fields['challenge_date_lastclear'] = time();
                $db->update('mmrpg_challenges_waveboard', $db_update_fields, array('user_id' => $current_user_id));
                //$this_battle->events_create(false, false, 'DEBUG', 'NEW $db_update_fields = '.print_r($db_update_fields, true).'');

            }
            // Otherwise do nothing
            else {
                //error_log('Do nothing!');

                // ...

            }

        }

    }

}

// If canvas refresh is needed, create an empty event
if ($canvas_refresh && $this_battle->battle_status != 'complete'){
    $this_battle->events_create(false, false, '', '', array(
        'event_flag_camera_reset' => true
        ));
}

// Stop the output buffer and collect contents
$output_buffer_contents = trim(ob_get_clean());
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Battle Loop | Prototype | Mega Man RPG Prototype</title>
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
        this_user_id : '<?= rpg_user::get_current_userid() ?>',
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

// Unset the database variable
unset($db);

// DEBUG
// If output buffer content was created, alert the webmaster of its content
if (!empty($output_buffer_contents) && (!MMRPG_CONFIG_IS_LIVE && MMRPG_CONFIG_ADMIN_MODE)){
    $output_buffer_contents = str_replace("\\", '\\', $output_buffer_contents);
    $output_buffer_contents = str_replace("\n", '\n', $output_buffer_contents);
    $output_buffer_contents = preg_replace('/\s+/', ' ', $output_buffer_contents);
    $output_buffer_contents = str_replace("'", "\'", $output_buffer_contents);
    $output_buffer_contents = strip_tags($output_buffer_contents);
    $output_buffer_contents = addslashes($output_buffer_contents);
    echo "alert('".$output_buffer_contents."');";
}

// TEMP DEBUG
if (!MMRPG_CONFIG_IS_LIVE || MMRPG_CONFIG_ADMIN_MODE){
    //error_log("memory_limit() = ".ini_get('memory_limit')."");
    //error_log("memory_get_usage() = ".round(((memory_get_usage() / 1024) / 1024), 2)."M");
    //error_log("memory_get_peak_usage() = ".round((memory_get_peak_usage() / 1024) / 1024, 2)."M");
    //error_log('memory_get_peak_usage_peak() = '.memory_get_peak_usage_peak());
}

?>
</script>
</head>
<body>
    <!-- This page intentionally left blank -->
</body>
</html>
