<?

// ROBOT ACTION : CHANGE PLAYER

// Include the necessary database files or arrays
$mmrpg_database_robots = rpg_robot::get_index(true, false);

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
    mmrpg_save_game_session();
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

            // If this has a persona right now, make sure we apply it
            $has_persona_applied = false;
            if (!empty($temp_robot_settings['robot_persona'])
                && !empty($temp_robot_settings['robot_abilities']['copy-style'])){
                //error_log($temp_robot_info['robot_token'].' has a persona: '.$temp_robot_settings['robot_persona']);
                $persona_token = $temp_robot_settings['robot_persona'];
                $persona_image_token = !empty($temp_robot_settings['robot_persona_image']) ? $temp_robot_settings['robot_persona_image'] : $temp_robot_settings['robot_persona'];
                $persona_index_info = $mmrpg_database_robots[$persona_token];
                rpg_robot::apply_persona_info($temp_robot_info, $persona_index_info, $temp_robot_settings);
                //error_log('new $temp_robot_info = '.print_r($temp_robot_info, true));
                $has_persona_applied = true;
            }

            // Collect the robot abilities and print the editor markup
            exit('success|player-swapped|'.rpg_robot::print_editor_markup($temp_player_info, $temp_robot_info, $has_persona_applied));

        }
    }

}
// Otherwise, produce an error
else {

    // Produce the error message
    exit('error|robot-undefined|false');

}




?>