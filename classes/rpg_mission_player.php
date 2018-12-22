<?php
/**
 * Mega Man RPG Player-Battle Mission
 * <p>The player mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_player extends rpg_mission {

    // Define a function for generating the PLAYER missions
    public static function generate($this_prototype_data, $this_user_info, $this_max_robots, &$field_factors_one, &$field_factors_two, &$field_factors_three){

        // Pull in global variables for this function
        global $mmrpg_index, $db;
        global $this_omega_factors_one;
        global $this_omega_factors_two;
        global $this_omega_factors_three;
        global $this_omega_factors_four;
        global $this_omega_factors_five;
        global $this_omega_factors_six;
        global $this_omega_factors_seven;
        global $this_omega_factors_eight;
        global $this_omega_factors_eight_two;
        global $this_omega_factors_nine;
        global $this_omega_factors_ten;
        global $this_omega_factors_eleven;

        $this_field_index = rpg_field::get_index();

        // Define the omega battle and default to empty
        $temp_battle_omega = array();

        // Define the local scope current player
        $this_player_token = $this_prototype_data['this_player_token'];
        $target_player_token = $this_prototype_data['target_player_token'];
        $target_player_token_backup = $target_player_token;

        // Pull and random player from the list and collect their full data
        $temp_player_array = $this_user_info;

        // Add this player data to the omage array
        $temp_battle_omega_player = $temp_player_array;

        // Collect the player values and decode the rewards and settings arrays
        $temp_player_rewards = $temp_player_array['player_rewards'];
        $temp_player_settings = $temp_player_array['player_settings'];
        $temp_player_starforce = $temp_player_array['player_starforce'];
        $temp_player_favourites = $temp_player_array['player_favourites'];

        // Create the empty array for the target player's battle robots
        $temp_player_robots = array();
        $temp_player_robots_rewards = !empty($temp_player_rewards[$target_player_token]['player_robots']) ? $temp_player_rewards[$target_player_token]['player_robots'] : array();
        $temp_player_robots_settings = !empty($temp_player_settings[$target_player_token]['player_robots']) ? $temp_player_settings[$target_player_token]['player_robots'] : array();
        $temp_player_field_settings = !empty($temp_player_settings[$target_player_token]['player_fields']) ? $temp_player_settings[$target_player_token]['player_fields'] : array();
        if (empty($temp_player_robots_rewards)){
            foreach ($temp_player_rewards AS $ptoken => $pinfo){
                if (!empty($temp_player_rewards[$ptoken]['player_robots'])){
                    $target_player_token = $ptoken;
                    $temp_player_robots_rewards = !empty($temp_player_rewards[$target_player_token]['player_robots']) ? $temp_player_rewards[$target_player_token]['player_robots'] : array();
                    $temp_player_robots_settings = !empty($temp_player_settings[$target_player_token]['player_robots']) ? $temp_player_settings[$target_player_token]['player_robots'] : array();
                    $temp_player_field_settings = !empty($temp_player_settings[$target_player_token]['player_fields']) ? $temp_player_settings[$target_player_token]['player_fields'] : array();
                    break;
                }
            }
        }

        // If the player fields setting is empty, define manually
        if (empty($temp_player_field_settings)){
            $temp_omega_fields = array();
            if ($target_player_token == 'dr-light'){ $temp_omega_fields = $this_omega_factors_one; }
            elseif ($target_player_token == 'dr-wily'){ $temp_omega_fields = $this_omega_factors_two; }
            elseif ($target_player_token == 'dr-cossack'){ $temp_omega_fields = $this_omega_factors_three; }
            foreach ($temp_omega_fields AS $omega){ $temp_player_field_settings[$omega['field']] = array('field_token' => $omega['field']); }
        }

        // Ensure this player has been unlocked by the target before continuing
        if (!empty($temp_player_robots_rewards)){

            // Collect the target player's robot rewards from the array
            $temp_player_robots = $temp_player_robots_rewards;

            // Define the array to hold the omega battle robots
            $temp_battle_omega_robots = array();

            // Loop through the reward robots and append their info
            $temp_counter = 1;
            foreach ($temp_player_robots AS $key => $temp_robotinfo){
                // Skip if does not exist
                if (empty($temp_robotinfo['robot_token'])){ continue; }
                // Collect this robot's settings if they exist
                if (!empty($temp_player_robots_settings[$temp_robotinfo['robot_token']])){ $temp_settings_array = $temp_player_robots_settings[$temp_robotinfo['robot_token']]; }
                else { $temp_settings_array = $temp_robotinfo; }
                // Collect this robot's rewards if they exist
                if (!empty($temp_player_robots_rewards[$temp_robotinfo['robot_token']])){ $temp_rewards_array = $temp_player_robots_rewards[$temp_robotinfo['robot_token']]; }
                else { $temp_rewards_array = $temp_robotinfo; }
                // Collect the basic details of this robot like ID, token, and level
                $temp_robot_id = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_counter;
                $temp_robot_token = $temp_robotinfo['robot_token'];
                $temp_robot_level = !empty($temp_robotinfo['robot_level']) ? $temp_robotinfo['robot_level'] : 1;
                $temp_robot_favourite = in_array($temp_robot_token, $temp_player_favourites) ? 1 : 0;
                $temp_robot_image = !empty($temp_settings_array['robot_image']) ? $temp_settings_array['robot_image'] : $temp_robotinfo['robot_token'];
                $temp_robot_item = !empty($temp_settings_array['robot_item']) ? $temp_settings_array['robot_item'] : '';
                //$temp_robot_rewards = $temp_player_rewards[$target_player_token];
                $temp_robot_rewards = $temp_rewards_array;
                // Collect this robot's abilities, format them, and crop if necessary
                $temp_robot_abilities = array();
                foreach ($temp_settings_array['robot_abilities'] AS $key2 => $temp_abilityinfo){ $temp_robot_abilities[] = $temp_abilityinfo['ability_token']; }
                $temp_robot_abilities = count($temp_robot_abilities) > 8 ? array_slice($temp_robot_abilities, 0, 8) : $temp_robot_abilities;
                // Create the new robot info array to be added to the omega battle options
                $temp_new_array = array(
                    'values' => array(
                        'flag_favourite' => $temp_robot_favourite,
                        'robot_rewards' => $temp_robot_rewards
                        ),
                    'robot_id' => $temp_robot_id,
                    'robot_token' => $temp_robot_token,
                    'robot_level' => $temp_robot_level,
                    'robot_image' => $temp_robot_image,
                    'robot_item' => $temp_robot_item,
                    'robot_abilities' => $temp_robot_abilities
                    );
                // Add this robot to the omega array and increment the counter
                $temp_battle_omega_robots[] = $temp_new_array;
                $temp_counter++;

            }

            // Sort the player's robots according to their level
            usort($temp_battle_omega_robots, 'mmrpg_prototype_sort_player_robots');

            // Slice the robot array based on the max num requested
            $temp_max_robots = $this_max_robots;
            $temp_omega_robots_count = count($temp_battle_omega_robots);
            if ($temp_omega_robots_count > $temp_max_robots){
                $temp_battle_omega_robots = array_slice($temp_battle_omega_robots, 0, $temp_max_robots);
                shuffle($temp_battle_omega_robots);
            } elseif ($temp_omega_robots_count < $temp_max_robots){
                $temp_max_robots = $temp_omega_robots_count;
            }
            $temp_omega_robots_count = count($temp_battle_omega_robots);

            // Populate the battle options with the player battle option
            $temp_battle_userid = $temp_battle_omega_player['user_id'];
            $temp_battle_usertoken = $temp_battle_omega_player['user_name_clean'];
            $temp_battle_username = !empty($temp_battle_omega_player['user_name_public']) ? $temp_battle_omega_player['user_name_public'] : $temp_battle_omega_player['user_name'];
            $temp_battle_userpronoun = ($temp_battle_omega_player['user_gender'] == 'male' ? 'his' : ($temp_battle_omega_player['user_gender'] == 'female' ? 'her' : ('their')));
            $temp_robots_num = count($temp_battle_omega_robots);
            $temp_battle_token = $this_prototype_data['phase_battle_token'].'-vs-player-'.$temp_battle_usertoken;
            $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-3');
            $temp_battle_omega['flags']['player_battle'] = true;
            $temp_challenge_type = $temp_max_robots.'-on-'.$temp_max_robots;
            $temp_battle_omega['battle_token'] = $temp_battle_token;
            $temp_battle_omega['battle_size'] = '1x2';
            $temp_battle_omega['battle_counts'] = false;
            $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_battle_omega['battle_name'] = 'Player Battle vs '.$temp_battle_username;
            $temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;'.(!preg_match('/s$/i', $temp_battle_username) ? 's' : '').' player data in a '.$temp_challenge_type.' battle!';
            $temp_battle_omega['battle_description2'] = '';
            $temp_battle_omega['battle_turns'] = ceil(MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $temp_robots_num * MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER);
            $temp_battle_omega['battle_robot_limit'] = $this_max_robots;
            $temp_battle_omega['battle_points'] = 0;
            foreach ($temp_battle_omega_robots AS $info){
                $temp_stat_counter = 0;
                $temp_robot_rewards = !empty($info['values']['robot_rewards']) ? $info['values']['robot_rewards'] : array();
                if (!empty($temp_robot_rewards['robot_energy'])){ $temp_stat_counter += $temp_robot_rewards['robot_energy']; }
                if (!empty($temp_robot_rewards['robot_attack'])){ $temp_stat_counter += $temp_robot_rewards['robot_attack']; }
                if (!empty($temp_robot_rewards['robot_defense'])){ $temp_stat_counter += $temp_robot_rewards['robot_defense']; }
                if (!empty($temp_robot_rewards['robot_speed'])){ $temp_stat_counter += $temp_robot_rewards['robot_speed']; }
                $temp_battle_omega['battle_points'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * $info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER) + $temp_stat_counter;
            }

            // Define the fusion field properties
            $temp_battle_omega['battle_button'] = ucfirst($temp_battle_username);
            $temp_field_info_options = array_keys($temp_player_field_settings);
            $temp_rand_int = mt_rand(1, 4);
            $temp_rand_start = ($temp_rand_int - 1) * 2;
            $temp_field_info_options = array_slice($temp_field_info_options, $temp_rand_start, 2);
            $temp_field_token_one = $temp_field_info_options[0];
            $temp_field_token_two = $temp_field_info_options[1];
            $temp_field_info_one = rpg_field::parse_index_info($this_field_index[$temp_field_token_one]);
            $temp_field_info_two = rpg_field::parse_index_info($this_field_index[$temp_field_token_two]);
            $temp_option_multipliers = array();
            $temp_option_field_list = array($temp_field_info_one, $temp_field_info_two);
            $temp_battle_omega['battle_field_base']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_field_info_one['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_field_info_two['field_name']);
            foreach ($temp_option_field_list AS $temp_field){
                if (!empty($temp_field['field_multipliers'])){
                    foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                        if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                        else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                    }
                }
            }

            $temp_battle_omega['battle_field_base']['field_type'] = !empty($temp_field_info_one['field_type']) ? $temp_field_info_one['field_type'] : '';
            $temp_battle_omega['battle_field_base']['field_type2'] = !empty($temp_field_info_two['field_type']) ? $temp_field_info_two['field_type'] : '';
            $temp_battle_omega['battle_field_base']['field_music'] = $temp_field_token_two;
            $temp_battle_omega['battle_field_base']['field_background'] = $temp_field_token_one;
            $temp_battle_omega['battle_field_base']['field_foreground'] = $temp_field_token_two;
            $temp_battle_omega['battle_field_base']['field_multipliers'] = $temp_option_multipliers;
            $temp_battle_omega['battle_field_base']['field_mechas'] = array();
            if (!empty($temp_field_info_one['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'] = array_merge($temp_battle_omega['battle_field_base']['field_mechas'], $temp_field_info_one['field_mechas']); }
            if (!empty($temp_field_info_two['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'] = array_merge($temp_battle_omega['battle_field_base']['field_mechas'], $temp_field_info_two['field_mechas']); }
            if (empty($temp_battle_omega['battle_field_base']['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'][] = 'met'; }
            $temp_battle_omega['battle_field_base']['field_background_frame'] = $temp_field_info_one['field_background_frame'];
            $temp_battle_omega['battle_field_base']['field_foreground_frame'] = $temp_field_info_two['field_foreground_frame'];
            $temp_battle_omega['battle_field_base']['field_background_attachments'] = $temp_field_info_one['field_background_attachments'];
            $temp_battle_omega['battle_field_base']['field_foreground_attachments'] = $temp_field_info_two['field_foreground_attachments'];

            // Define the final details for the player
            $temp_battle_omega['battle_target_player']['player_id'] = $temp_battle_userid;
            $temp_battle_omega['battle_target_player']['player_token'] = $target_player_token_backup;
            $temp_battle_omega['battle_target_player']['player_name'] = ucfirst($temp_battle_username);
            $temp_battle_omega['battle_target_player']['player_robots'] = $temp_battle_omega_robots;
            $temp_battle_omega['battle_target_player']['player_starforce'] = $temp_player_starforce;
            $temp_battle_omega['battle_robot_limit'] = count($temp_battle_omega_robots);

            // This battle doesn't count, so let's modify the point value
            $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER);

        } else {

            return false;

        }

        // Return the generated battle data
        return $temp_battle_omega;

    }

}
?>