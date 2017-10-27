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
        $this_playerinfo['values']['battle_index'] = !defined('MMRPG_REMOTE_SKIP_INDEX') && !empty($this_playerinfo['save_values_battle_index']) ? $this_playerinfo['save_values_battle_index'] : array();
        $this_playerinfo['values']['battle_complete'] = !defined('MMRPG_REMOTE_SKIP_COMPLETE') && !empty($this_playerinfo['save_values_battle_complete']) ? json_decode($this_playerinfo['save_values_battle_complete'], true) : array();
        $this_playerinfo['values']['battle_failure'] = !defined('MMRPG_REMOTE_SKIP_FAILURE') && !empty($this_playerinfo['save_values_battle_failure']) ? json_decode($this_playerinfo['save_values_battle_failure'], true) : array();
        $this_playerinfo['values']['battle_rewards'] = !defined('MMRPG_REMOTE_SKIP_REWARDS') && !empty($this_playerinfo['save_values_battle_rewards']) ? json_decode($this_playerinfo['save_values_battle_rewards'], true) : array();
        $this_playerinfo['values']['battle_settings'] = !defined('MMRPG_REMOTE_SKIP_SETTINGS') && !empty($this_playerinfo['save_values_battle_settings']) ? json_decode($this_playerinfo['save_values_battle_settings'], true) : array();
        $this_playerinfo['flags'] = !empty($this_playerinfo['save_flags']) ? json_decode($this_playerinfo['save_flags'], true) : array();
        $this_playerinfo['settings'] = !empty($this_playerinfo['save_settings']) ? json_decode($this_playerinfo['save_settings'], true) : array();
        unset($this_playerinfo['save_values'],
            $this_playerinfo['save_values_battle_index'],
            $this_playerinfo['save_values_battle_complete'],
            $this_playerinfo['save_values_battle_failure'],
            $this_playerinfo['save_values_battle_rewards'],
            $this_playerinfo['save_values_battle_settings'],
            $this_playerinfo['save_flags'],
            $this_playerinfo['save_settings']
            );

        // Manually load battle rewards and/or settings from another table if requested and they exist
        if (!defined('MMRPG_REMOTE_SKIP_REWARDS') || !defined('MMRPG_REMOTE_SKIP_SETTINGS')){

            // Create arrays to hold the battle rewards and settings
            $raw_battle_rewards = array();
            $raw_battle_settings = array();

            // Collect battle settings and/or rewards from the functions
            if (!defined('MMRPG_REMOTE_SKIP_REWARDS') && !defined('MMRPG_REMOTE_SKIP_SETTINGS')){
                $raw_battle_vars = rpg_user::get_battle_vars($this_playerid);
                $raw_battle_rewards = $raw_battle_vars['battle_rewards'];
                $raw_battle_settings = $raw_battle_vars['battle_settings'];
            } elseif (!defined('MMRPG_REMOTE_SKIP_REWARDS')){
                $raw_battle_rewards = rpg_user::get_battle_rewards($this_playerid);
            } elseif (!defined('MMRPG_REMOTE_SKIP_SETTINGS')){
                $raw_battle_settings = rpg_user::get_battle_settings($this_playerid);
            }

            /*
            // Collect the players and robots for this user for all requests
            $raw_battle_players = rpg_user::get_players($this_playerid);
            $raw_battle_players_abilities = rpg_user::get_players_abilities($this_playerid);
            $raw_battle_robots = rpg_user::get_robots($this_playerid);
            $raw_battle_robots_abilities = rpg_user::get_robots_abilities($this_playerid);
            $raw_battle_robots_movesets = rpg_user::get_robots_movesets($this_playerid);
            */

            // Define a temporary index to say which robot is with which player
            //$temp_robot_player_index = array();

            //echo('<pre>$raw_battle_players = '.print_r($raw_battle_players, true).'</pre>');
            //echo('<pre>$raw_battle_players_abilities = '.print_r($raw_battle_players_abilities, true).'</pre>');
            //echo('<pre>$raw_battle_robots = '.print_r($raw_battle_robots, true).'</pre>');
            //echo('<pre>$raw_battle_robots_abilities = '.print_r($raw_battle_robots_abilities, true).'</pre>');
            //echo('<pre>$raw_battle_robots_movesets = '.print_r($raw_battle_robots_movesets, true).'</pre>');

            /*
            // If requested, generate and save a battle rewards array
            if (!defined('MMRPG_REMOTE_SKIP_REWARDS')){

                // Loop through players and add them to the rewards array
                if (!empty($raw_battle_players)){
                    foreach ($raw_battle_players AS $player_key => $player_info){

                        // Collect the player token for reference
                        $player_token = $player_info['player_token'];

                        // Construct the player rewards array with required info
                        $player_rewards = array();
                        $player_rewards['player_token'] = $player_info['player_token'];
                        $player_rewards['player_points'] = $player_info['player_points'];

                        // Define player rewards list arrays to be populated later
                        $player_rewards['player_abilities'] = array();
                        $player_rewards['player_robots'] = array();

                        // Add this player's data to the parent rewards array
                        $raw_battle_rewards[$player_token] = $player_rewards;
                    }

                    // Loop through player-unlocked abilities and add them to the rewards array
                    if (!empty($raw_battle_players_abilities)){
                        foreach ($raw_battle_players_abilities AS $ability_key => $ability_info){

                            // Collect the ability and player token for reference
                            $ability_token = $ability_info['ability_token'];
                            $player_token = $ability_info['player_token'];

                            // Construct the ability rewards array with required info
                            $ability_rewards = array();
                            $ability_rewards['ability_token'] = $ability_token;

                            // Add this ability's data to the parent player rewards array
                            $raw_battle_rewards[$player_token]['player_abilities'][$ability_token] = $ability_rewards;

                        }
                    }

                    // Loop through robots and add them to the rewards array
                    if (!empty($raw_battle_robots)){
                        foreach ($raw_battle_robots AS $robot_key => $robot_info){

                            // Collect the robot and player token for reference
                            $robot_token = $robot_info['robot_token'];
                            $player_token = $robot_info['robot_player'];
                            $temp_robot_player_index[$robot_token] = $player_token;

                            // Construct the robot rewards array with required info
                            $robot_rewards = array();
                            $robot_rewards['flags'] = !empty($robot_info['robot_flags']) ? json_decode($robot_info['robot_flags'], true) : array();
                            $robot_rewards['values'] = !empty($robot_info['robot_values']) ? json_decode($robot_info['robot_values'], true) : array();
                            $robot_rewards['counters'] = !empty($robot_info['robot_counters']) ? json_decode($robot_info['robot_counters'], true) : array();
                            $robot_rewards['robot_token'] = $robot_info['robot_token'];
                            $robot_rewards['robot_level'] = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
                            $robot_rewards['robot_experience'] = !empty($robot_info['robot_experience']) ? $robot_info['robot_experience'] : 0;
                            $robot_rewards['robot_energy'] = !empty($robot_info['robot_energy_bonuses']) ? $robot_info['robot_energy_bonuses'] : 0;
                            $robot_rewards['robot_energy_pending'] = !empty($robot_info['robot_energy_bonuses_pending']) ? $robot_info['robot_energy_bonuses_pending'] : 0;
                            $robot_rewards['robot_attack'] = !empty($robot_info['robot_attack_bonuses']) ? $robot_info['robot_attack_bonuses'] : 0;
                            $robot_rewards['robot_attack_pending'] = !empty($robot_info['robot_attack_bonuses_pending']) ? $robot_info['robot_attack_bonuses_pending'] : 0;
                            $robot_rewards['robot_defense'] = !empty($robot_info['robot_defense_bonuses']) ? $robot_info['robot_defense_bonuses'] : 0;
                            $robot_rewards['robot_defense_pending'] = !empty($robot_info['robot_defense_bonuses_pending']) ? $robot_info['robot_defense_bonuses_pending'] : 0;
                            $robot_rewards['robot_speed'] = !empty($robot_info['robot_speed_bonuses']) ? $robot_info['robot_speed_bonuses'] : 0;
                            $robot_rewards['robot_speed_pending'] = !empty($robot_info['robot_speed_bonuses_pending']) ? $robot_info['robot_speed_bonuses_pending'] : 0;
                            $robot_rewards['robot_abilities'] = array();

                            // Add this robot's data to the parent player rewards array
                            $raw_battle_rewards[$player_token]['player_robots'][$robot_token] = $robot_rewards;

                        }
                    }

                    // Loop through robot-unlocked abilities and add them to the rewards array
                    if (!empty($raw_battle_robots_abilities)){
                        foreach ($raw_battle_robots_abilities AS $ability_key => $ability_info){

                            // Collect the ability and player token for reference
                            $ability_token = $ability_info['ability_token'];
                            $robot_token = $ability_info['robot_token'];
                            $player_token = $temp_robot_player_index[$robot_token];

                            // Construct the ability rewards array with required info
                            $ability_rewards = array();
                            $ability_rewards['ability_token'] = $ability_token;

                            // Add this ability's data to the parent player rewards array
                            $raw_battle_rewards[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_rewards;

                        }
                    }

                }

            }
            */

            //echo('<pre>$raw_battle_rewards = '.print_r($raw_battle_rewards, true).'</pre>');

            /*
            // If requested, generate and save a battle settings array
            if (!defined('MMRPG_REMOTE_SKIP_SETTINGS')){

                // Loop through players and add them to the settings array
                if (!empty($raw_battle_players)){
                    foreach ($raw_battle_players AS $player_key => $player_info){

                        // Collect the player token for reference
                        $player_token = $player_info['player_token'];

                        // Construct the player settings with define basic info
                        $player_settings = array();
                        $player_settings['player_token'] = $player_info['player_token'];

                        // Define player settings list arrays to be populated later
                        $player_settings['player_robots'] = array();
                        $player_settings['player_fields'] = array();

                        // Add this player's data to the parent settings array
                        $raw_battle_settings[$player_token] = $player_settings;
                    }

                    // Loop through robots and add them to the settings array
                    if (!empty($raw_battle_robots)){
                        foreach ($raw_battle_robots AS $robot_key => $robot_info){

                            // Collect the robot and player token for reference
                            $robot_token = $robot_info['robot_token'];
                            $player_token = $robot_info['robot_player'];

                            // Construct the robot settings array with required info
                            $robot_settings = array();
                            $robot_settings['flags'] = !empty($robot_info['robot_flags']) ? json_decode($robot_info['robot_flags'], true) : array();
                            $robot_settings['values'] = !empty($robot_info['robot_values']) ? json_decode($robot_info['robot_values'], true) : array();
                            $robot_settings['counters'] = !empty($robot_info['robot_counters']) ? json_decode($robot_info['robot_counters'], true) : array();
                            $robot_settings['robot_token'] = $robot_info['robot_token'];
                            $robot_settings['robot_core'] = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
                            $robot_settings['robot_image'] = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : '';
                            $robot_settings['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : '';
                            $robot_settings['robot_abilities'] = array();

                            // Add this robot's data to the parent player settings array
                            $raw_battle_settings[$player_token]['player_robots'][$robot_token] = $robot_settings;

                        }
                    }

                    // Loop through robot-equipped abilities and add them to the rewards array
                    if (!empty($raw_battle_robots_movesets)){
                        foreach ($raw_battle_robots_movesets AS $ability_key => $ability_info){

                            // Collect the ability and player token for reference
                            $ability_token = $ability_info['ability_token'];
                            $robot_token = $ability_info['robot_token'];
                            $slot_key = $ability_info['slot_key'];
                            $player_token = $temp_robot_player_index[$robot_token];

                            // Construct the ability settings array with required info
                            $ability_settings = array();
                            $ability_settings['ability_token'] = $ability_token;

                            // Add this ability's data to the parent player rewards array
                            $raw_battle_settings[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_settings;

                        }
                    }

                }

            }
            */

            //echo('<pre>$raw_battle_settings = '.print_r($raw_battle_settings, true).'</pre>');

            // Assign battle rewards and settings to the parent values array
            $this_playerinfo['values']['battle_rewards'] = $raw_battle_rewards;
            $this_playerinfo['values']['battle_settings'] = $raw_battle_settings;

        }

        /*
        // Manually load battle rewards from another table if requested and they exist
        $this_playerinfo['values']['battle_rewards'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_REWARDS')){ $this_playerinfo['values']['battle_rewards'] = rpg_user::get_battle_rewards($this_playerid); }

        // Manually load battle settings from another table if requested and they exist
        $this_playerinfo['values']['battle_settings'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_SETTINGS')){ $this_playerinfo['values']['battle_settings'] = rpg_user::get_battle_settings($this_playerid); }
        */

        //echo('<pre>$this_playerinfo[\'values\'][\'battle_rewards\'] = '.print_r($this_playerinfo['values']['battle_rewards'], true).'</pre>');
        //echo('<pre>$this_playerinfo[\'values\'][\'battle_settings\'] = '.print_r($this_playerinfo['values']['battle_settings'], true).'</pre>');

        //exit();

        // Manually load unlocked abilities from another table if requested and they exist
        $this_playerinfo['values']['battle_abilities'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_ABILITIES')){ $this_playerinfo['values']['battle_abilities'] = rpg_user::get_battle_abilities($this_playerid); }

        // Manually load unlocked items from another table if requested and they exist
        $this_playerinfo['values']['battle_items'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_ITEMS')){ $this_playerinfo['values']['battle_items'] = rpg_user::get_battle_items($this_playerid); }

        // Manually load unlocked stars from another table if requested and they exist
        $this_playerinfo['values']['battle_stars'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_STARS')){ $this_playerinfo['values']['battle_stars'] = rpg_user::get_battle_stars($this_playerid); }

        // Manually load encounter records from another table if requested and they exist
        $this_playerinfo['values']['robot_database'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_DATABASE')){ $this_playerinfo['values']['robot_database'] = rpg_user::get_robot_database($this_playerid); }

        //echo('<pre>$this_playerinfo[\'values\'][\'battle_abilities\'] = '.print_r($this_playerinfo['values']['battle_abilities'], true).'</pre>');
        //exit();

        /*
        // vv MIGHT NOT BE NECESSARY NOW, RETESTING REQUIRED vv //
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
        */

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