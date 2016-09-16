<?

// ROBOT EDITOR FUNCTIONS

// Define a function for updating editor info
function refresh_editor_arrays( &$allowed_edit_players, &$allowed_edit_robots, &$allowed_edit_data,
    &$allowed_edit_data_count, &$allowed_edit_player_count, &$allowed_edit_robot_count ){

    // Collect the current session token
    $session_token = rpg_game::session_token();

    // Collect the player array and merge in session details
    $temp_player_array = array();
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
        $temp_player_rewards = $_SESSION[$session_token]['values']['battle_rewards'];
        $temp_player_array = array_merge($temp_player_array, $temp_player_rewards);
    }
    if (!empty($_SESSION[$session_token]['values']['battle_settings'])){
        $temp_player_settings = $_SESSION[$session_token]['values']['battle_settings'];
        $temp_player_array = array_merge($temp_player_array, $temp_player_settings);
    }

    // Define the editor indexes and count variables
    $allowed_edit_players = array();
    $allowed_edit_robots = array();
    $allowed_edit_data = array();
    $allowed_edit_data_count = 0;
    $allowed_edit_player_count = 0;
    $allowed_edit_robot_count = 0;

    // Collect a temporary player index
    $temp_player_tokens = array_keys($temp_player_array);
    $temp_player_index = rpg_player::get_index_custom($temp_player_tokens);

    // Now to actually loop through and update the allowed players, robots, and abilities arrays
    foreach ($temp_player_array AS $player_token => $player_info){
        if (empty($player_token) || !isset($temp_player_index[$player_token])){ continue; }
        $player_index_info = $temp_player_index[$player_token];

        // If this player has not yet completed chapter one, no robot editor
        //$intro_complete = rpg_prototype::event_complete('completed-chapter_'.$player_token.'_one');
        $battles_complete = rpg_prototype::battles_complete($player_token, true);
        $intro_complete = !empty($battles_complete) && count($battles_complete) >= 1 ? true : false;
        $prototype_complete = rpg_prototype::campaign_complete($player_token);
        if (!$intro_complete && !$prototype_complete){ continue; }

        // Merge the player and index info then append the token and info
        $player_info = array_merge($player_index_info, $player_info);
        $allowed_edit_players[] = $player_token;
        $allowed_edit_data[$player_token] = $player_info;

        // Collect a temporary robot index
        $temp_robot_tokens = array_keys($player_info['player_robots']);
        $temp_robot_index = rpg_robot::get_index_custom($temp_robot_tokens);

        foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
            if (empty($robot_token) || !isset($temp_robot_index[$robot_token])){ continue; }
            $robot_index_info = $temp_robot_index[$robot_token];

            // Merge the robot and index info then append the token and info
            $robot_info = array_merge($robot_index_info, $robot_info);
            $allowed_edit_robots[] = $robot_token;
            $allowed_edit_data[$player_token]['player_robots'][$robot_token] = $robot_info;

            // Collect a temporary ability index
            $temp_ability_tokens = array_keys($robot_info['robot_abilities']);
            $temp_ability_index = rpg_ability::get_index_custom($temp_ability_tokens);

            foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){
                if (empty($ability_token) || !isset($temp_ability_index[$ability_token])){ continue; }
                $ability_index_info = $temp_ability_index[$ability_token];

                // Merge the ability and index info then append the token and info
                $ability_info = array_merge($ability_index_info, $ability_info);
                $allowed_edit_data[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_info;

            }
        }
    }

    //$allowed_edit_data = array_reverse($allowed_edit_data, true);
    $allowed_edit_player_count = !empty($allowed_edit_players) ? count($allowed_edit_players) : 0;
    $allowed_edit_robot_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;
    $allowed_edit_data_count = 0;
    foreach ($allowed_edit_data AS $pinfo){
        $pcount = !empty($pinfo['player_robots']) ? count($pinfo['player_robots']) : 0;
        $allowed_edit_data_count += $pcount;
    }

}

?>