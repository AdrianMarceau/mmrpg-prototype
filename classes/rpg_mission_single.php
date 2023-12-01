<?php
/**
 * Mega Man RPG Single-Battle Mission
 * <p>The single mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_single extends rpg_mission {

    // Define a function for generating the SINGLES missions
    public static function generate($this_prototype_data, $this_robot_token, $this_field_token, $this_start_level = 1, $this_unlock_robots = true, $this_unlock_abilities = true){
        //error_log("rpg_mission_single::generate(\$this_prototype_data, '{$this_robot_token}', '{$this_field_token}', {$this_start_level}, {$this_unlock_robots}, {$this_unlock_abilities})");

        // Collect the session token
        $session_token = mmrpg_game_token();

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

        // Collect a list of completed battles for this player so we can see our phase1 level
        $phase_level_boost = 0;
        $battles_complete_list = array();
        $battles_complete_count = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token'], true, $battles_complete_list);
        $battles_complete_tokens = !empty($battles_complete_list) ? array_keys($battles_complete_list) : array();
        $battles_complete_targets = !empty($this_prototype_data['target_robot_omega']) ? array_map(function($r){ return $r['robot']; }, $this_prototype_data['target_robot_omega']) : array();
        //error_log('$this_prototype_data[\'target_robot_omega\'] = '.print_r($this_prototype_data['target_robot_omega'], true));
        //error_log('$battles_complete_targets = '.print_r($battles_complete_targets, true));
        //error_log('$battles_complete_count = '.print_r($battles_complete_count, true));
        //error_log('$battles_complete_tokens = '.print_r($battles_complete_tokens, true));
        //error_log('$battles_complete_list = '.print_r($battles_complete_list, true));
        if (!empty($battles_complete_tokens)
            && !empty($battles_complete_targets)){
            foreach ($battles_complete_targets AS $key => $rtoken){
                $btoken = $this_prototype_data['this_player_token'].'-phase1-'.$rtoken;
                if (in_array($btoken, $battles_complete_tokens)){ $phase_level_boost += 1; }
            }
        }
        //error_log('$phase_level_boost = '.print_r($phase_level_boost, true));

        // Collect the robot index for calculation purposes
        $this_robot_index = rpg_robot::get_index(true);
        $this_field_index = rpg_field::get_index();

        // Define the array to hold this omega battle and populate with base varaibles
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_option_robot = is_array($this_robot_token) ? $this_robot_token : $this_robot_index[$this_robot_token];
        $temp_option_field = $this_field_index[$this_field_token];
        $temp_battle_omega = array();
        $temp_battle_omega['flags']['single_battle'] = true;
        $temp_battle_omega['values']['single_battle_masters'] = array($this_robot_token);
        $temp_battle_omega['battle_complete'] = false;
        $temp_battle_omega['battle_counts'] = true;
        $temp_battle_omega['battle_size'] = '1x1';
        $temp_battle_omega['battle_name'] = 'Chapter Two Master Battle';
        $temp_battle_omega['battle_token'] = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_field_base']['field_id'] = 100;
        $temp_battle_omega['battle_field_base']['field_token'] = $temp_option_field['field_token'];
        $temp_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
        $temp_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_robot_master_tokens = array();
        $temp_battle_omega['battle_target_player']['player_robots'][0]['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_option_robot['robot_id'], 1);
        $temp_battle_omega['battle_target_player']['player_robots'][0]['robot_token'] = $this_robot_token;
        $temp_robot_master_tokens[] = $this_robot_token;
        $temp_option_completed = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        if (!empty($temp_option_completed)){ $temp_battle_omega['battle_complete'] = $temp_option_completed; }

        // Determine the amount of targets and their ability counts
        $temp_target_count = 1;
        $temp_ability_count = !empty($this_prototype_data['this_player_number']) ? $this_prototype_data['this_player_number'] : 1;
        $temp_limit_count = 4;
        $temp_battle_count = 0;
        if (!empty($temp_battle_omega['battle_complete'])){
            $temp_battle_count = $temp_battle_omega['battle_complete'];
            if ($temp_battle_count <= $temp_limit_count){
                $temp_target_count += $temp_battle_count;
                $temp_ability_count += $temp_battle_count;
            } else {
                $temp_target_count += $temp_limit_count;
                $temp_ability_count += $temp_limit_count;
            }
        }

        // Define the omega variables for level, zenny, turns, and random encounter rate
        $omega_robot_level_max = $this_start_level + 8;
        if ($omega_robot_level_max >= 100){ $omega_robot_level_max = 100; }
        $omega_robot_level = $this_start_level;
        if (!empty($temp_option_completed) && !empty($temp_option_completed)){ $omega_robot_level += $temp_option_completed - 1; }
        if (!empty($phase_level_boost)){ $omega_robot_level += $phase_level_boost; }
        if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
        if ($omega_robot_level >= 100){ $omega_robot_level = 100; }

        // Define the battle rewards based on above data
        $temp_battle_omega['battle_rewards']['abilities'] = array();
        if (!empty($temp_option_robot['robot_rewards']['abilities'])){
            foreach ($temp_option_robot['robot_rewards']['abilities'] AS $key => $info){
                if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
                $temp_battle_omega['battle_rewards']['abilities'][] = $info;
                break; // only unlock first ability (T1) if simply clearing the stage
            }
        }

        // Fill the empty spots with minor enemy robots
        if (true){
            $temp_battle_omega['battle_target_player']['player_switch'] = 1.5;
            $bonus_robot_count = 0;
            //if ($this_prototype_data['this_player_token'] == 'dr-light'){ $bonus_robot_count += 1; }
            //elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){ $bonus_robot_count += 2; }
            //elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){ $bonus_robot_count += 3; }
            $temp_mook_options = array();
            $temp_mook_letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
            $temp_mook_counts = array();
            $temp_mook_counts2 = array();
            if (!isset($temp_option_field['field_mechas'])){ $temp_option_field['field_mechas'] = array(); }
            $temp_mook_options = array_merge($temp_mook_options, $temp_option_field['field_mechas']);
            //if (empty($temp_mook_options) || mt_rand(1, 10) == 1){ $temp_mook_options[] = 'met'; }
            if (empty($temp_mook_options)){ $temp_mook_options[] = 'met'; }
            $temp_mook_options = array_slice($temp_mook_options, 0, ($temp_battle_count + 1));
            $temp_battle_omega['battle_field_base']['field_mechas'] = $temp_mook_options;
            // Loop through the allowed bonus robot count placing random mooks
            $temp_robot_count = count($temp_battle_omega['battle_target_player']['player_robots']);
            for ($i = 0; $i < $bonus_robot_count; $i++){
                if (count($temp_battle_omega['battle_target_player']['player_robots']) >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ break; }
                shuffle($temp_mook_options);
                $temp_mook_token = $temp_mook_options[array_rand($temp_mook_options)];
                $temp_mook_info = $this_robot_index[$temp_mook_token];
                $bonus_robot_info = array('robot_token' => $temp_mook_token, 'robot_id' => 1, 'robot_level' => 1);
                $bonus_robot_info['robot_abilities'] = $temp_mook_info['robot_abilities'];
                $bonus_robot_info['robot_class'] = !empty($temp_mook_info['robot_class']) ? $temp_mook_info['robot_class'] : 'master';
                $bonus_robot_info['robot_name'] = $temp_mook_info['robot_name'];
                $temp_mook_name_token = $bonus_robot_info['robot_name_token'] = str_replace(' ', '-', strtolower($bonus_robot_info['robot_name']));
                if (!isset($temp_mook_counts[$temp_mook_name_token])){ $temp_mook_counts[$temp_mook_name_token] = 0; }
                else { $temp_mook_counts[$temp_mook_name_token]++; }
                //$bonus_robot_info['robot_name'] .= ' '.$temp_mook_letters[$temp_mook_counts[$temp_mook_name_token]];
                $temp_battle_omega['battle_target_player']['player_robots'][] = $bonus_robot_info;
                $temp_robot_master_tokens[] = $bonus_robot_info['robot_token'];
            }
            // Shuffle all the target player robots and then loop through them to make final changes
            //shuffle($temp_battle_omega['battle_target_player']['player_robots']);
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
                // Update the robot ID to prevent collisions
                $info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $this_robot_index[$info['robot_token']]['robot_id'], ($key + 1));
                // Append the appropriate letters to all the robot name tokens
                if (isset($info['robot_class']) && $info['robot_class'] == 'mecha'){
                    $temp_name_token = isset($info['robot_name_token']) ? $info['robot_name_token'] : $info['robot_token'];
                    if ($temp_mook_counts[$temp_name_token] > 0){
                        if (!isset($temp_mook_counts2[$temp_name_token])){ $temp_mook_counts2[$temp_name_token] = 0; }
                        else { $temp_mook_counts2[$temp_name_token]++; }
                        $info['robot_name'] .= ' '.$temp_mook_letters[$temp_mook_counts2[$temp_name_token]];
                        $temp_battle_omega['battle_target_player']['player_robots'][$key] = $info;
                    }
                }
                // Otherwise, if this is a master robot
                else {
                    // Do nothing for now
                }
                // Update the player robots array with recent changes
                $temp_battle_omega['battle_target_player']['player_robots'][$key] = $info;
            }
        }

        //if (!empty($this_prototype_data['battles_complete'])){ die('<pre style="height: 600px; overflow: auto;">'.print_r($this_prototype_data['battles_complete'], true).'</pre>'); }
        //if (!empty($temp_battle_omega['battle_complete'])){ die('<pre style="height: 600px; overflow: auto;">'.print_r($temp_battle_omega['battle_complete'], true).'</pre>'); }

        // Add an appropriate bonus robot IF this mission has not been completed yet
        if ($temp_option_completed){
            //error_log('VISITOR needed for = '.print_r($temp_battle_omega['battle_token'], true));

            // Decide which robot should appear for this player
            static $temp_possible_visitors;
            if (empty($temp_possible_visitors)){
                //error_log('$temp_possible_visitors = '.print_r($temp_possible_visitors, true));
                $temp_possible_visitors = array();
                $temp_possible_visitors_query = "SELECT
                    `robots`.`robot_token`,
                    `robots`.`robot_core`
                    FROM `mmrpg_index_robots` AS `robots`
                    LEFT JOIN `mmrpg_index_robots_groups_tokens` AS `tokens` ON `tokens`.`robot_token` = `robots`.`robot_token`
                    LEFT JOIN `mmrpg_index_robots_groups` AS `groups` ON `groups`.`group_class` = `tokens`.`group_class` AND `groups`.`group_token` = `tokens`.`group_token`
                    WHERE
                    `robots`.`robot_flag_complete` = 1
                    AND `robots`.`robot_flag_published` = 1
                    AND `robots`.`robot_flag_hidden` = 0
                    AND `robots`.`robot_flag_fightable` = 1
                    -- AND `robots`.`robot_flag_unlockable` = 1
                    AND `robots`.`robot_flag_exclusive` = 0
                    AND `robots`.`robot_class` = 'mecha'
                    -- AND `robots`.`robot_core` NOT IN ('', 'copy')
                    AND `robots`.`robot_token` NOT IN ('heal-bot', 'heel-bot', 'trille-bot')
                    AND `robots`.`robot_token` NOT IN ('weapons-archive')
                    -- AND `robots`.`robot_game` NOT IN ('MM1', 'MMPU', 'MM2', 'MM4')
                    ORDER BY
                    `groups`.`group_order` ASC,
                    `tokens`.`token_order` ASC
                    ;";
                $cache_token = md5($temp_possible_visitors_query);
                $cached_index = rpg_object::load_cached_index('missions.single.visitors', $cache_token);
                if (!empty($cached_index)){
                    $temp_possible_visitors_raw = $cached_index;
                    unset($cached_index);
                } else {
                    $temp_possible_visitors_raw = $db->get_array_list($temp_possible_visitors_query);
                    rpg_object::save_cached_index('missions.single.visitors', $cache_token, $temp_possible_visitors_raw);
                }
                //error_log('$temp_possible_visitors_raw = '.print_r($temp_possible_visitors_raw, true));
                if (!empty($temp_possible_visitors_raw)){
                    $temp_robot_records = mmrpg_prototype_robot_database();
                    $temp_robot_encounters = array_map(function($r){ return isset($r['robot_encountered']) ? $r['robot_encountered'] : 0; }, $temp_robot_records);
                    asort($temp_robot_encounters);
                    //error_log('$temp_robot_encounters = '.print_r($temp_robot_encounters, true));
                    //error_log('$temp_robot_records = '.print_r($temp_robot_records, true));
                    usort($temp_possible_visitors_raw, function($r1, $r2) use ($temp_robot_encounters){
                        $r1token = $r1['robot_token'];
                        $r2token = $r2['robot_token'];
                        $r1encounters = isset($temp_robot_encounters[$r1token]) ? $temp_robot_encounters[$r1token] : 0;
                        $r2encounters = isset($temp_robot_encounters[$r2token]) ? $temp_robot_encounters[$r2token] : 0;
                        if ($r1encounters == $r2encounters){ return 0; }
                        return ($r1encounters < $r2encounters) ? -1 : 1;
                        });
                    //error_log('$temp_possible_visitors_raw = '.print_r($temp_possible_visitors_raw, true));
                    foreach($temp_possible_visitors_raw AS $key => $info){
                        list($token, $core) = array_values($info);
                        if (empty($core)){ $core = 'none'; }
                        if (!isset($temp_possible_visitors[$core])){ $temp_possible_visitors[$core] = array(); }
                        $temp_possible_visitors[$core][] = $token;
                    }
                    //error_log('$temp_possible_visitors_raw (tokens) = '.print_r(array_map(function($r){ return $r['robot_token']; }, $temp_possible_visitors_raw), true));
                }
            }
            //error_log('$temp_possible_visitors = '.print_r($temp_possible_visitors, true));
            $temp_visitor_token = '';
            $temp_visitor_core = $temp_option_field['field_type'];
            if (!empty($temp_possible_visitors['none'])){
                if (empty($temp_possible_visitors[$temp_visitor_core])){ $temp_visitor_core = 'none'; }
                elseif ($temp_battle_count % 7 === 0){ $temp_visitor_core = 'none'; }
            }
            //error_log($temp_battle_omega['battle_token'].' // $temp_visitor_core = '.$temp_visitor_core);
            $temp_visitor_queue = array();
            if (!empty($temp_possible_visitors[$temp_visitor_core])){
                $temp_visitor_queue = array_merge($temp_visitor_queue, $temp_possible_visitors[$temp_visitor_core]);
            }
            if (!empty($temp_visitor_queue)){
                //error_log('$temp_visitor_queue = '.print_r($temp_visitor_queue, true));
                foreach ($temp_visitor_queue AS $key => $token){
                    if (in_array($token, $temp_option_field['field_mechas'])){ continue; }
                    $temp_visitor_token = $token;
                    break;
                }
            }
            //error_log('$temp_visitor_token = '.print_r($temp_visitor_token, true));
            if (!empty($temp_visitor_token)){
                $temp_robot_key = count($temp_battle_omega['battle_target_player']['player_robots']);
                $temp_robot_token = $temp_visitor_token;
                $temp_robot_index_info = $this_robot_index[$temp_robot_token];
                $temp_robot_info = array();
                $temp_robot_info['flags'] = array();
                $temp_robot_info['flags']['robot_is_visitor'] = true;
                $temp_robot_info['flags']['hide_from_mission_select'] = true;
                $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_option_robot['robot_id'], $temp_robot_key);
                $temp_robot_info['robot_token'] = $temp_robot_token;
                $temp_robot_info['robot_item'] = 'super-pellet';
                $temp_robot_master_tokens[] = $temp_robot_token;
                $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_robot_info;
                //error_log('adding '.$temp_robot_token.' to a '.$this_prototype_data['this_player_token'].' base field battle $temp_robot_info = '.print_r($temp_robot_info, true));
            }

        }

        //error_log('$temp_battle_omega[\'battle_target_player\'][\'player_robots\'] = '.print_r($temp_battle_omega['battle_target_player']['player_robots'], true));

        // Skip the empty battle button or a different phase
        if (empty($temp_battle_omega['battle_token']) || $temp_battle_omega['battle_token'] == 'battle' || $temp_battle_omega['battle_phase'] != $this_prototype_data['battle_phase']){ return false; }
        // Collect the battle token and create an omega clone from the index base
        $temp_battle_token = $temp_battle_omega['battle_token'];
        // Make copied of the robot level var and adjust
        $temp_omega_robot_level = $omega_robot_level;
        // If the battle was already complete, collect its details and modify the mission
        $temp_complete_level = 0;
        $temp_complete_count = 0;
        if (!empty($temp_battle_omega['battle_complete'])){
            $temp_complete_level = $temp_omega_robot_level;
            if (!empty($temp_battle_omega['battle_complete'])){ $temp_complete_count = $temp_battle_omega['battle_complete']; }
            else { $temp_complete_count = 1; }
        }

        // Define the battle difficulty level (0 - 8) based on level and completed count
        $temp_battle_difficulty = ceil(8 * ($temp_omega_robot_level / 100));
        $temp_battle_difficulty += $temp_complete_count;
        if ($temp_battle_difficulty >= 10){ $temp_battle_difficulty = 10; }
        $temp_battle_omega['battle_difficulty'] = $temp_battle_difficulty;

        // Update the robot level for this battle
        $temp_battle_omega['battle_level'] = $temp_omega_robot_level;

        // Update the battle zenny and turns with the omega values
        $temp_battle_omega['battle_zenny'] = 0;
        $temp_battle_omega['battle_turns'] = 0;

        // Loop through the target robots again update with omega values
        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot_data){

            // Ensure this robot's token exists in the index, else continue
            if (!isset($this_robot_index[$robot_data['robot_token']])){ continue; }
            $robot_token = $robot_data['robot_token'];
            $robot_info = $this_robot_index[$robot_token];

            // Update the robot level and battle zenny plus turns
            $temp_robot_level = $robot_info['robot_class'] != 'mecha' ? $temp_omega_robot_level : mt_rand(1, ceil($temp_omega_robot_level / 3));
            $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $temp_robot_level;
            if ($robot_info['robot_class'] == 'boss'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL0 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $temp_robot_level);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS;
            } elseif ($robot_info['robot_class'] == 'master'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $temp_robot_level);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
            } elseif ($robot_info['robot_class'] == 'mecha'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $temp_robot_level);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA;
            }

            // If this is a mecha, only allow limited extra abilities
            $ability_count = $temp_ability_count;
            if ($robot_info['robot_class'] == 'mecha'){
                $ability_count = ceil($ability_count / 2);
                if ($ability_count > 2){ $ability_count = 2; }
            }

            // Randomly assign this robot a hold item if applicable
            $temp_item = '';
            if ($robot_info['robot_class'] == 'master'){
                if (false){
                    $rand = mt_rand(1, 3);
                    if ($rand == 1
                        || $rand == 2){
                        $stats = array('energy', 'weapon', 'attack', 'defense', 'speed');
                        $items = array('pellet', 'capsule');
                        $temp_item = $stats[mt_rand(0, (count($stats) - 1))].'-'.$items[mt_rand(0, (count($items) - 1))];
                    }
                }
                $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $temp_item;
            }

            // Generate abilities and update the omega robot array
            $robot_info_plus_data = array_merge($robot_info, $robot_data);
            $temp_abilities = mmrpg_prototype_generate_abilities($robot_info_plus_data, $omega_robot_level, $ability_count, $temp_item);
            $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = $temp_abilities;

            // If this is a mecha with alt images, randomly assign one
            if ($robot_info['robot_class'] == 'mecha' && !empty($robot_info['robot_image_alts'])){
                $images = array($robot_info['robot_token']);
                foreach ($robot_info['robot_image_alts'] AS $alt){
                    if (count($images) > $temp_complete_count){ break; }
                    $images[] = $robot_info['robot_token'].'_'.$alt['token'];
                }
                shuffle($images);
                $temp_image = array_shift($images);
                $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_image'] = $temp_image;
            }

        }

        // Reduce the zenny earned from this mission each time it is completed
        if ($temp_complete_count > 0){ $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * (2 / (2 + $temp_complete_count))); }

        // Reverse the order of the robots in battle
        $temp_battle_omega['battle_target_player']['player_robots'] = array_reverse($temp_battle_omega['battle_target_player']['player_robots']);
        $temp_first_robot = array_shift($temp_battle_omega['battle_target_player']['player_robots']);
        shuffle($temp_battle_omega['battle_target_player']['player_robots']);
        array_unshift($temp_battle_omega['battle_target_player']['player_robots'], $temp_first_robot);

        // Empty the robot rewards array if not allowed
        $temp_battle_omega['battle_rewards']['robots'] = array();
        if ($this_unlock_robots){
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot_info){
                $index_info = $this_robot_index[$robot_info['robot_token']];
                if ($index_info['robot_class'] == 'master'
                    && $index_info['robot_flag_unlockable']
                    && !$index_info['robot_flag_exclusive']
                    && !mmrpg_game_robot_unlocked('', $robot_info['robot_token'])){
                    $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $robot_info['robot_token'], 'level' => $omega_robot_level, 'experience' => 999);
                }
            }
        }

        // Empty the ability rewards array if not allowed
        if (!$this_unlock_abilities){ $temp_battle_omega['battle_rewards']['abilities'] = array(); }

        // Define the number of abilities and robots left to unlock and start at zero
        $this_unlock_robots_count = count($temp_battle_omega['battle_rewards']['robots']);
        $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards']['abilities']);

        // Review the unlockable RMs for this field and grey-out any that are already unlocked
        if (!empty($temp_battle_omega['values']['single_battle_masters'])
            && $temp_battle_omega['battle_complete']){
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot){
                if (!in_array($robot['robot_token'], $temp_battle_omega['values']['single_battle_masters'])){ continue; }
                if (mmrpg_prototype_robot_unlocked(false, $robot['robot_token'])){
                    $robot['flags']['shadow_on_mission_select'] = true;
                    $temp_battle_omega['battle_target_player']['player_robots'][$key] = $robot;
                    $this_unlock_robots_count -= 1;
                }
            }
        }

        // Loop through the omega battle ability rewards and update the ability levels there too
        if (!empty($temp_battle_omega['battle_rewards']['abilities'])){
            foreach ($temp_battle_omega['battle_rewards']['abilities'] AS $key2 => $ability){
                // Remove if this ability is already unlocked
                if (mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, $ability['token'])){ $this_unlock_abilities_count -= 1; }
            }
        }

        // Update the battle description based on what we've calculated
        $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].' and liberate the '.$temp_option_field['field_name'].'!';
        $temp_battle_omega['battle_description2'] = '';
        if ($this_unlock_abilities_count > 0){
            $temp_robot_pronoun_possessive = rpg_robot::get_robot_pronoun($temp_option_robot['robot_class'], $temp_option_robot['robot_gender'], 'possessive');
            $temp_battle_omega['battle_description2'] = 'Once we\'ve acquired '.$temp_robot_pronoun_possessive.' special weapon, we may be able to equip it to other robots!';
        } elseif ($this_unlock_robots_count > 0){
            $temp_robot_pronoun_object = rpg_robot::get_robot_pronoun($temp_option_robot['robot_class'], $temp_option_robot['robot_gender'], 'object');
            $temp_robot_pronoun_possessive = rpg_robot::get_robot_pronoun($temp_option_robot['robot_class'], $temp_option_robot['robot_gender'], 'possessive');
            $temp_battle_omega['battle_description2'] = 'If we use only Neutral-type abilities on '.$temp_robot_pronoun_object.', we may be able to save '.$temp_robot_pronoun_possessive.' data!';
        } elseif ($temp_option_completed){
            $temp_battle_omega['battle_description'] = str_replace('liberate', 'secure', $temp_battle_omega['battle_description']);
        }

        // Return the generated battle data
        return $temp_battle_omega;

    }

}
?>