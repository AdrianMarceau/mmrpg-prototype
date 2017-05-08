<?php
// If a user ID has been defined, attempt to swap the save session
if (!defined('MMRPG_REMOTE_GAME_ID')){ define('MMRPG_REMOTE_GAME_ID', (!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0)); }
if (MMRPG_REMOTE_GAME_ID != 0 && MMRPG_REMOTE_GAME_ID != $_SESSION['GAME']['USER']['userid']){

    // Attempt to collect data for this player from the database
    $this_playerid = MMRPG_REMOTE_GAME_ID;
    $this_playerinfo = $db->get_array("SELECT
        mmrpg_users.*,
        mmrpg_saves.*
        FROM mmrpg_users
        LEFT JOIN mmrpg_saves ON mmrpg_saves.user_id = mmrpg_users.user_id
        WHERE mmrpg_users.user_id = {$this_playerid};");

    // If the userinfo exists in the database, display it
    if (!empty($this_playerinfo)){

        // Ensure this remote game actually exists
        $temp_session_key = 'REMOTE_GAME_'.MMRPG_REMOTE_GAME_ID;
        // Define the constant that forces remote game checking
        define('MMRPG_REMOTE_GAME', MMRPG_REMOTE_GAME_ID);

        // Collect this player's info from the database... all of it
        $this_playerinfo['counters'] = !empty($this_playerinfo['save_counters']) ? json_decode($this_playerinfo['save_counters'], true) : array();
        $this_playerinfo['values'] = !empty($this_playerinfo['save_values']) ? json_decode($this_playerinfo['save_values'], true) : array();
        //$this_playerinfo['values']['battle_index'] = !defined('MMRPG_REMOTE_SKIP_INDEX') && !empty($this_playerinfo['save_values_battle_index']) ? json_decode($this_playerinfo['save_values_battle_index'], true) : array();
        $this_playerinfo['values']['battle_index'] = !defined('MMRPG_REMOTE_SKIP_INDEX') && !empty($this_playerinfo['save_values_battle_index']) ? $this_playerinfo['save_values_battle_index'] : array();
        $this_playerinfo['values']['battle_complete'] = !defined('MMRPG_REMOTE_SKIP_COMPLETE') && !empty($this_playerinfo['save_values_battle_complete']) ? json_decode($this_playerinfo['save_values_battle_complete'], true) : array();
        $this_playerinfo['values']['battle_failure'] = !defined('MMRPG_REMOTE_SKIP_FAILURE') && !empty($this_playerinfo['save_values_battle_failure']) ? json_decode($this_playerinfo['save_values_battle_failure'], true) : array();
        $this_playerinfo['values']['battle_rewards'] = !defined('MMRPG_REMOTE_SKIP_REWARDS') && !empty($this_playerinfo['save_values_battle_rewards']) ? json_decode($this_playerinfo['save_values_battle_rewards'], true) : array();
        $this_playerinfo['values']['battle_settings'] = !defined('MMRPG_REMOTE_SKIP_SETTINGS') && !empty($this_playerinfo['save_values_battle_settings']) ? json_decode($this_playerinfo['save_values_battle_settings'], true) : array();
        $this_playerinfo['values']['battle_abilities'] = !defined('MMRPG_REMOTE_SKIP_ABILITIES') && !empty($this_playerinfo['save_values_battle_abilities']) ? json_decode($this_playerinfo['save_values_battle_abilities'], true) : array();
        $this_playerinfo['values']['battle_stars'] = !defined('MMRPG_REMOTE_SKIP_STARS') && !empty($this_playerinfo['save_values_battle_stars']) ? json_decode($this_playerinfo['save_values_battle_stars'], true) : array();
        $this_playerinfo['flags'] = !empty($this_playerinfo['save_flags']) ? json_decode($this_playerinfo['save_flags'], true) : array();
        $this_playerinfo['settings'] = !empty($this_playerinfo['save_settings']) ? json_decode($this_playerinfo['save_settings'], true) : array();
        unset($this_playerinfo['save_values'],
            $this_playerinfo['save_values_battle_index'],
            $this_playerinfo['save_values_battle_complete'],
            $this_playerinfo['save_values_battle_failure'],
            $this_playerinfo['save_values_battle_rewards'],
            $this_playerinfo['save_values_battle_settings'],
            $this_playerinfo['save_values_battle_abilities'],
            $this_playerinfo['save_flags'],
            $this_playerinfo['save_settings']
            );

        // Manually load items from another table if requested and they exist
        $this_playerinfo['values']['battle_items'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_ITEMS')){
            $raw_battle_items = $db->get_array_list("SELECT item_token, item_quantity FROM mmrpg_users_items WHERE user_id = {$this_playerid};");
            if (!empty($raw_battle_items)){
                // Loop through database items and format into game-compatible array
                $new_battle_items = array();
                foreach ($raw_battle_items AS $key => $item_info){
                    $item_token = $item_info['item_token'];
                    $item_quantity = $item_info['item_quantity'];
                    $new_battle_items[$item_token] = $item_quantity;
                }
                // Update parent array with formatted battle items
                $this_playerinfo['values']['battle_items'] = $new_battle_items;
            }

        }

        // Manually load robot database records from another table if requested and they exist
        $this_playerinfo['values']['robot_database'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_DATABASE')){
            $raw_robot_database = $db->get_array_list("SELECT robot_token, robot_encountered, robot_summoned, robot_scanned, robot_defeated, robot_unlocked FROM mmrpg_users_robots_database WHERE user_id = {$this_playerid};", 'robot_token');
            if (!empty($raw_robot_database)){
                $this_playerinfo['values']['robot_database'] = $raw_robot_database;
            }
        }

        // Fix issues with legacy player rewards array
        if (!empty($this_playerinfo['values']['battle_rewards'])){
            foreach ($this_playerinfo['values']['battle_rewards'] AS $player_token => $player_info){
                // If new player robots array is empty but old is not, copy over
                if (empty($player_info['player_robots']) && !empty($player_info['player_rewards']['robots'])){
                    // Loop through and collect robot data from the legacy rewards array
                    foreach ($player_info['player_rewards']['robots'] AS $key => $robot){
                        if (empty($robot['token'])){ continue; }
                        $robot_info = array();
                        $robot_info['robot_token'] = $robot['token'];
                        $robot_info['robot_level'] = !empty($robot['level']) ? $robot['level'] : 1;
                        $robot_info['robot_experience'] = !empty($robot['points']) ? $robot['points'] : 0;
                        $player_info['player_robots'][$robot['token']] = $robot_info;
                    }
                    // Kill the legacy rewards array to prevent confusion
                    unset($player_info['player_rewards']);
                }
                // If player robots are NOT empty, update in the parent array
                if (!empty($player_info['player_robots'])){
                    $this_playerinfo['values']['battle_rewards'][$player_token] = $player_info;
                }
                // Otherwise if no robots found, kill this player's data in both arrays
                else {
                    unset($this_playerinfo['values']['battle_rewards'][$player_token]);
                    unset($this_playerinfo['values']['battle_settings'][$player_token]);
                }
            }
        }

        // Add this player's GAME data to the session for iframe scripts
        $temp_remote_session = array();
        $temp_remote_session['CACHE_DATE'] = !empty($_SESSION['GAME']['CACHE_DATE']) ? $_SESSION['GAME']['CACHE_DATE'] : MMRPG_CONFIG_CACHE_DATE;
        $temp_remote_session['DEMO'] = 0;
        $temp_remote_session['USER'] = array(
            'userid' => $this_playerinfo['user_id'],
            'username' => $this_playerinfo['user_name'],
            'username_clean' => $this_playerinfo['user_name_clean'],
            'omega' => $this_playerinfo['user_omega'],
            'imagepath' => $this_playerinfo['user_image_path'],
            'backgroundpath' => $this_playerinfo['user_background_path'],
            'colourtoken' => $this_playerinfo['user_colour_token'],
            'gender' => $this_playerinfo['user_gender']
            );
        $temp_remote_session['counters'] = $this_playerinfo['counters'];
        $temp_remote_session['values'] = $this_playerinfo['values'];
        $temp_remote_session['flags'] = $this_playerinfo['flags'];
        $temp_remote_session['settings'] = $this_playerinfo['settings'];
        $temp_session_key = 'REMOTE_GAME_'.$this_playerinfo['user_id'];
        $_SESSION[$temp_session_key] = $temp_remote_session;

        // If the user had a colour token, define it as a constant
        if (!empty($this_playerinfo['user_colour_token'])){
            define('MMRPG_SETTINGS_REMOTE_FIELDTYPE', $this_playerinfo['user_colour_token']);
        }

    }

}
?>