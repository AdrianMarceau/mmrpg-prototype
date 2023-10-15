<?php
/**
 * Mega Man RPG Player-Battle Mission
 * <p>The player mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_player extends rpg_mission {

    // Define a function for generating the PLAYER missions
    public static function generate($this_prototype_data, $this_user_info, $this_max_robots, $this_flat_level, &$field_factors_one, &$field_factors_two, &$field_factors_three){

        // Pull in global variables for this function
        global $db;
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

        static $this_field_index, $this_player_index, $this_robot_index;
        if (empty($this_field_index)){ $this_field_index = rpg_field::get_index(true); }
        if (empty($this_player_index)){ $this_player_index = rpg_player::get_index(true); }
        if (empty($this_robot_index)){ $this_robot_index = rpg_robot::get_index(true); }

        // Define the omega battle and default to empty
        $this_battle_omega = array();

        // Define the local scope current player
        $this_player_token = $this_prototype_data['this_player_token'];
        $target_player_token = $this_prototype_data['target_player_token'];
        $target_player_token_backup = $target_player_token;

        // Pull and random player from the list and collect their full data
        $this_user_info = $this_user_info;
        //error_log('$this_user_info = '.print_r($this_user_info, true));

        // Add this player data to the omage array
        $this_battle_omega_player = $this_user_info;

        // Collect the user and player ID from the data array
        $temp_user_id = $this_battle_omega_player['user_id'];
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, $this_player_index[$target_player_token]['player_id']);

        // Collect the player values and decode the rewards and settings arrays
        $temp_player_rewards = $this_user_info['player_rewards'];
        $temp_player_settings = $this_user_info['player_settings'];
        $temp_player_starforce = $this_user_info['player_starforce'];
        $temp_player_favourites = $this_user_info['player_favourites'];
        $temp_player_items = $this_user_info['player_items'];

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

        // Loop through relevant sessions keys and collect plus merge any robot data
        $all_player_robot_rosters = array();
        $all_player_robots_rewards = array();
        $all_player_robot_settings = array();
        if (!empty($temp_player_rewards)){
            foreach ($temp_player_rewards AS $ptoken => $pinfo){
                if (!empty($pinfo['player_robots'])){
                    $all_player_robots_rewards = array_merge($all_player_robots_rewards, $pinfo['player_robots']);
                }
            }
        }
        if (!empty($temp_player_settings)){
            foreach ($temp_player_settings AS $ptoken => $pinfo){
                if (!empty($pinfo['player_robots'])){
                    $all_player_robot_settings = array_merge($all_player_robot_settings, $pinfo['player_robots']);
                    $all_player_robot_rosters[$ptoken] = array_keys($pinfo['player_robots']);
                }
            }
        }
        $all_player_robots_tokens = array();
        $all_player_robots_tokens = array_merge($all_player_robots_tokens, array_keys($all_player_robots_rewards));
        $all_player_robots_tokens = array_merge($all_player_robots_tokens, array_keys($all_player_robot_settings));
        $all_player_robots_tokens = array_unique($all_player_robots_tokens);

        // Check to see if we need to maintain a static robot position
        $robot_formation_is_static = false;
        if (!empty($this_user_info['proxy_robots'])){ $robot_formation_is_static = true; }

        //error_log('$all_player_robot_rosters: '.print_r($all_player_robot_rosters, true));
        //error_log('$all_player_robots_rewards: '.print_r($all_player_robots_rewards, true));
        //error_log('$all_player_robot_settings: '.print_r($all_player_robot_settings, true));

        // If the player fields setting is empty, define manually
        if (empty($temp_player_field_settings)){
            $temp_omega_fields = array();
            if ($target_player_token == 'dr-light'){ $temp_omega_fields = $this_omega_factors_one; }
            elseif ($target_player_token == 'dr-wily'){ $temp_omega_fields = $this_omega_factors_two; }
            elseif ($target_player_token == 'dr-cossack'){ $temp_omega_fields = $this_omega_factors_three; }
            foreach ($temp_omega_fields AS $omega){ $temp_player_field_settings[$omega['field']] = array('field_token' => $omega['field']); }
        }

        // Ensure this player has been unlocked by the target before continuing
        if (!empty($all_player_robots_rewards)){

            // Collect the target player's robot rewards from the array
            $temp_player_robot_tokens = $all_player_robot_rosters[$target_player_token];
            if (!empty($this_user_info['proxy_robots'])){
                $temp_player_robot_tokens = $this_user_info['proxy_robots'];
            }

            // Define the array to hold the omega battle robots
            $this_battle_omega_robots = array();

            // Loop through the reward robots and append their info
            $temp_counter = 1;
            foreach ($temp_player_robot_tokens AS $key => $token){
                $temp_robotinfo = array('robot_token' => $token);
                // Skip if does not exist
                if (empty($temp_robotinfo['robot_token'])){ continue; }
                // Collect this robot's settings if they exist
                if (!empty($all_player_robot_settings[$temp_robotinfo['robot_token']])){ $temp_settings_array = $all_player_robot_settings[$temp_robotinfo['robot_token']]; }
                else { $temp_settings_array = $temp_robotinfo; }
                // Collect this robot's rewards if they exist
                if (!empty($all_player_robots_rewards[$temp_robotinfo['robot_token']])){ $temp_rewards_array = $all_player_robots_rewards[$temp_robotinfo['robot_token']]; }
                else { $temp_rewards_array = $temp_robotinfo; }
                // Collect the basic details of this robot like ID, token, and level
                $temp_robot_id = rpg_game::unique_robot_id($temp_player_id, $this_robot_index[$temp_robotinfo['robot_token']]['robot_id'], $temp_counter);
                $temp_robot_token = $temp_robotinfo['robot_token'];
                $temp_robot_level = $this_flat_level; //!empty($temp_robotinfo['robot_level']) ? $temp_robotinfo['robot_level'] : 1;
                $temp_robot_favourite = in_array($temp_robot_token, $temp_player_favourites) ? 1 : 0;
                $temp_robot_image = !empty($temp_settings_array['robot_image']) ? $temp_settings_array['robot_image'] : $temp_robotinfo['robot_token'];
                $temp_robot_item = !empty($temp_settings_array['robot_item']) ? $temp_settings_array['robot_item'] : '';
                $temp_robot_support = !empty($temp_settings_array['robot_support']) ? $temp_settings_array['robot_support'] : '';
                $temp_robot_support_image = !empty($temp_settings_array['robot_support_image']) ? $temp_settings_array['robot_support_image'] : '';
                //$temp_robot_rewards = $temp_player_rewards[$target_player_token];
                $temp_robot_rewards = $temp_rewards_array;
                // Collect this robot's abilities, format them, and crop if necessary
                $temp_robot_abilities = array();
                if (!isset($temp_settings_array['robot_abilities'])){ $temp_settings_array['robot_abilities'] = array('buster-shot'); }
                foreach ($temp_settings_array['robot_abilities'] AS $key2 => $temp_abilityinfo){
                    if (!empty($temp_abilityinfo['ability_token'])){ $temp_robot_abilities[] = $temp_abilityinfo['ability_token']; }
                    elseif (!empty($key2) && !is_numeric($key2)){ $temp_robot_abilities[] = $key2; }
                }
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
                    'robot_support' => $temp_robot_support,
                    'robot_support_image' => $temp_robot_support_image,
                    'robot_abilities' => $temp_robot_abilities
                    );
                // Add this robot to the omega array and increment the counter
                $this_battle_omega_robots[] = $temp_new_array;
                $temp_counter++;

            }

            // Sort the player's robots according to their level
            if (!$robot_formation_is_static){ usort($this_battle_omega_robots, 'mmrpg_prototype_sort_player_robots'); }

            // Slice the robot array based on the max num requested
            $temp_max_robots = $this_max_robots;
            $temp_omega_robots_count = count($this_battle_omega_robots);
            if ($temp_omega_robots_count > $temp_max_robots){
                $this_battle_omega_robots = array_slice($this_battle_omega_robots, 0, $temp_max_robots);
                if (!$robot_formation_is_static){ shuffle($this_battle_omega_robots); }
            } elseif ($temp_omega_robots_count < $temp_max_robots){
                $temp_max_robots = $temp_omega_robots_count;
            }
            $temp_omega_robots_count = count($this_battle_omega_robots);

            // Populate the battle options with the player battle option
            $temp_battle_userid = $this_battle_omega_player['user_id'];
            $temp_battle_usertoken = $this_battle_omega_player['user_name_clean'];
            $temp_battle_username = !empty($this_battle_omega_player['user_name_public']) ? $this_battle_omega_player['user_name_public'] : $this_battle_omega_player['user_name'];
            $temp_battle_userpronoun = ($this_battle_omega_player['user_gender'] == 'male' ? 'his' : ($this_battle_omega_player['user_gender'] == 'female' ? 'her' : ('their')));
            $temp_robots_num = count($this_battle_omega_robots);
            $temp_battle_token = $this_prototype_data['phase_battle_token'].'-vs-player-'.$temp_battle_usertoken;
            $this_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-3');
            $this_battle_omega['flags']['player_battle'] = true;
            $this_battle_omega['flags']['player_battle_with_omega'] = !empty($temp_player_items['omega-seed']) ? true : false;
            $this_battle_omega['values']['player_battle_vs'] = $temp_battle_username;
            $temp_challenge_type = $temp_max_robots.'-on-'.$temp_max_robots;
            $this_battle_omega['battle_token'] = $temp_battle_token;
            $this_battle_omega['battle_size'] = '1x2';
            $this_battle_omega['battle_counts'] = false;
            $this_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
            $this_battle_omega['battle_name'] = 'Player Battle vs '.$temp_battle_username;
            $this_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;'.(!preg_match('/s$/i', $temp_battle_username) ? 's' : '').' player data in a '.$temp_challenge_type.' battle!';
            $this_battle_omega['battle_description2'] = '';
            $this_battle_omega['battle_robot_limit'] = $this_max_robots;
            $this_battle_omega['battle_zenny'] = 0;
            $this_battle_omega['battle_turns'] = 0;
            foreach ($this_battle_omega_robots AS $info){
                $temp_stat_counter = 0;
                $temp_robot_rewards = !empty($info['values']['robot_rewards']) ? $info['values']['robot_rewards'] : array();
                if (!empty($temp_robot_rewards['robot_energy'])){ $temp_stat_counter += $temp_robot_rewards['robot_energy']; }
                if (!empty($temp_robot_rewards['robot_attack'])){ $temp_stat_counter += $temp_robot_rewards['robot_attack']; }
                if (!empty($temp_robot_rewards['robot_defense'])){ $temp_stat_counter += $temp_robot_rewards['robot_defense']; }
                if (!empty($temp_robot_rewards['robot_speed'])){ $temp_stat_counter += $temp_robot_rewards['robot_speed']; }
                $this_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER * $info['robot_level']) + $temp_stat_counter;
                $this_battle_omega['battle_turns'] += ceil(MMRPG_SETTINGS_BATTLETURNS_PERROBOT * MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER);
            }

            // Define the fusion field properties
            $this_battle_omega['battle_button'] = 'Vs. '.ucfirst($temp_battle_username);
            $temp_field_info_options = array_keys($temp_player_field_settings);
            $temp_rand_int = mt_rand(1, 4);
            $temp_rand_start = ($temp_rand_int - 1) * 2;
            $temp_field_info_options = array_slice($temp_field_info_options, $temp_rand_start, 2);
            $temp_default_field = rpg_player::get_intro_field($target_player_token);
            $temp_field_token_one = isset($temp_field_info_options[0]) ? $temp_field_info_options[0] : $temp_default_field;
            $temp_field_token_two = isset($temp_field_info_options[1]) ? $temp_field_info_options[1] : $temp_default_field;
            if (!empty($this_user_info['proxy_fields'])){
                $fields = $this_user_info['proxy_fields'];
                $temp_field_token_one = $fields[0];
                $temp_field_token_two = !empty($fields[1]) && $fields[1] !== $temp_field_token_one ? $fields[1] : $temp_field_token_one;
            }
            $temp_field_info_one = $this_field_index[$temp_field_token_one];
            $temp_field_info_two = $this_field_index[$temp_field_token_two];
            $temp_option_multipliers = array();
            $temp_option_field_list = array($temp_field_info_one, $temp_field_info_two);
            $this_battle_omega['battle_field_base']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_field_info_one['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_field_info_two['field_name']);
            foreach ($temp_option_field_list AS $temp_field){
                if (!empty($temp_field['field_multipliers'])){
                    foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                        if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                        else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                    }
                }
            }

            $this_battle_omega['battle_field_base']['field_type'] = !empty($temp_field_info_one['field_type']) ? $temp_field_info_one['field_type'] : '';
            $this_battle_omega['battle_field_base']['field_type2'] = !empty($temp_field_info_two['field_type']) ? $temp_field_info_two['field_type'] : '';
            $this_battle_omega['battle_field_base']['field_music'] = !empty($temp_field_info_two['field_music']) ? $temp_field_info_two['field_music'] : $temp_field_token_two;
            $this_battle_omega['battle_field_base']['field_background'] = $temp_field_token_one;
            $this_battle_omega['battle_field_base']['field_foreground'] = $temp_field_token_two;
            $this_battle_omega['battle_field_base']['field_multipliers'] = $temp_option_multipliers;
            $this_battle_omega['battle_field_base']['field_mechas'] = array();
            if (!empty($temp_field_info_one['field_mechas'])){ $this_battle_omega['battle_field_base']['field_mechas'] = array_merge($this_battle_omega['battle_field_base']['field_mechas'], $temp_field_info_one['field_mechas']); }
            if (!empty($temp_field_info_two['field_mechas'])){ $this_battle_omega['battle_field_base']['field_mechas'] = array_merge($this_battle_omega['battle_field_base']['field_mechas'], $temp_field_info_two['field_mechas']); }
            if (empty($this_battle_omega['battle_field_base']['field_mechas'])){ $this_battle_omega['battle_field_base']['field_mechas'][] = 'met'; }
            $this_battle_omega['battle_field_base']['field_background_frame'] = $temp_field_info_one['field_background_frame'];
            $this_battle_omega['battle_field_base']['field_foreground_frame'] = $temp_field_info_two['field_foreground_frame'];
            $this_battle_omega['battle_field_base']['field_background_attachments'] = $temp_field_info_one['field_background_attachments'];
            $this_battle_omega['battle_field_base']['field_foreground_attachments'] = $temp_field_info_two['field_foreground_attachments'];

            // Define the final details for the player
            $this_battle_omega['battle_target_player'] = array_merge(array('user_id' => 0, 'player_id' => 0), $this_battle_omega['battle_target_player']);
            $this_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
            $this_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
            $this_battle_omega['battle_target_player']['player_token'] = $target_player_token_backup;
            $this_battle_omega['battle_target_player']['player_name'] = ucfirst($temp_battle_username);
            //$this_battle_omega['battle_target_player']['player_image'] = 'custom_***';
            $this_battle_omega['battle_target_player']['player_robots'] = $this_battle_omega_robots;
            $this_battle_omega['battle_target_player']['player_starforce'] = $temp_player_starforce;
            $this_battle_omega['battle_robot_limit'] = count($this_battle_omega_robots);

            // Collect proxy information for this play, if any has been set, so that we can overwrite
            $proxy_player_info = self::extract_proxy_player_info(
                $this_battle_omega['battle_target_player'],
                $this_user_info,
                $this_battle_omega_robots,
                $all_player_robots_tokens
                );
            if (!empty($proxy_player_info)){ $this_battle_omega['battle_target_player'] = $proxy_player_info; }

        } else {

            return false;

        }

        // Return the generated battle data
        return $this_battle_omega;

    }

    // Define a new function for injecting player proxy details into a target player object
    public static function extract_proxy_player_info($base_player_info, $this_user_info, $this_user_robots = array(), $this_user_robots_unlocked = array()){
        //error_log('rpg_mission::extract_proxy_player_info()');
        //error_log('$base_player_info = '.print_r($base_player_info, true));
        //error_log('$this_user_info = '.print_r($this_user_info, true));
        //error_log('$this_user_robots = '.print_r($this_user_robots, true));
        //error_log('$this_user_robots_unlocked = '.print_r($this_user_robots_unlocked, true));

        // Collect indexes for reference
        $this_robot_index = rpg_robot::get_index(true);

        // Define an array to hold player details
        $battle_target_player = $base_player_info;

        // Define PROXY-related image overwrites for player given context and customizations
        $proxy_player_token = 'proxy';
        $proxy_player_image = 'proxy_alt';
        $proxy_player_type = 'energy';
        if ($base_player_info['player_token'] === 'dr-light'){
            $proxy_player_image = 'proxy_alt3';
            $proxy_player_type = 'defense';
        }
        elseif ($base_player_info['player_token'] === 'dr-wily'){
            $proxy_player_image = 'proxy_alt4';
            $proxy_player_type = 'attack';
        }
        elseif ($base_player_info['player_token'] === 'dr-cossack'){
            $proxy_player_image = 'proxy_alt5';
            $proxy_player_type = 'speed';
        }
        if (!empty($this_user_info['proxy_image'])){ $proxy_player_image = $this_user_info['proxy_image']; }
        if (!empty($this_user_info['proxy_bonus'])){ $proxy_player_type = $this_user_info['proxy_bonus']; }
        $battle_target_player['player_token'] = $proxy_player_token;
        $battle_target_player['player_name'] = !empty($this_user_info['user_name_public']) ? $this_user_info['user_name_public'] : $this_user_info['user_name'];
        $battle_target_player['player_image'] = $proxy_player_image;
        $battle_target_player['player_type'] = $proxy_player_type;

        // QUOTES for now, we should just generate some fun filler until later when they're able to be custom
        $human_robots_unlocked = count($this_user_robots_unlocked);
        $human_robots_unlocked_text = $human_robots_unlocked.' '.($human_robots_unlocked === 1 ? 'robot' : 'different robots');
        $first_robot_token = $this_user_robots[0]['robot_token'];
        $first_robot_info = $this_robot_index[$first_robot_token];
        $possible_intro_quotes = array(
            rpg_battle::random_positive_word().' The name\'s {name}! Don\'t wear it out!',
            rpg_battle::random_positive_word().' I\'m ready to go! How about you?',
            rpg_battle::random_positive_word().' I\'ve got {robots} so far, but I\'m not done yet!',
            rpg_battle::random_positive_word().' I\'m ready to battle! Are you ready to lose?',
            rpg_battle::random_positive_word().' Once I beat you, I\'ll get your Player Token!',
            rpg_battle::random_positive_word().' I\'m getting stronger every day! Are you ready?',
            rpg_battle::random_positive_word().' The name\'s {name}... You\'d best remember it!',
            rpg_battle::random_positive_word().' Next up is you! I\'m not afraid of you!',
            );
        $random_intro_quote = $possible_intro_quotes[mt_rand(0, count($possible_intro_quotes) - 1)];
        $random_intro_quote = str_replace('{name}', $battle_target_player['player_name'], $random_intro_quote);
        $random_intro_quote = str_replace('{robots}', $human_robots_unlocked_text, $random_intro_quote);
        $battle_target_player['player_quotes']['battle_start'] = $random_intro_quote;
        $battle_target_player['player_quotes']['battle_taunt'] = 'I\'ve already collected '.$human_robots_unlocked_text.'! How about you?';
        $battle_target_player['player_quotes']['battle_victory'] = rpg_battle::random_positive_word().' I can\'t believe I won! '.$first_robot_info['robot_name'].' and I make a great team!';
        $battle_target_player['player_quotes']['battle_defeat'] = rpg_battle::random_negative_word().' I can\'t believe you beat me... '.$first_robot_info['robot_name'].' and I will get you next time!';
        //error_log('$this_battle_omega[\'battle_target_player\']: '.print_r($battle_target_player, true));

        // Return the generated target player info
        //error_log('$battle_target_player = '.print_r($battle_target_player, true));
        return $battle_target_player;

    }

}
?>