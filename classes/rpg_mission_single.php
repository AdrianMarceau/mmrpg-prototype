<?php
/**
 * Mega Man RPG Single-Battle Mission
 * <p>The single mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_single extends rpg_mission {

    // Define a function for generating the SINGLES missions
    public static function generate($this_prototype_data, $this_robot_token, $this_field_token, $this_start_level = 1, $this_unlock_robots = true, $this_unlock_abilities = true, $starfield_mission = false){

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

        // Collect the robot index for calculation purposes
        $db_robot_fields = rpg_robot::get_index_fields(true);
        $this_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        $this_field_index = rpg_field::get_index();

        // Define the array to hold this omega battle and populate with base varaibles
        $temp_option_robot = is_array($this_robot_token) ? $this_robot_token : rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
        $temp_option_field = rpg_field::parse_index_info($this_field_index[$this_field_token]);
        $temp_battle_omega = array();
        $temp_battle_omega['flags']['single_battle'] = true;
        $temp_battle_omega['values']['single_battle_masters'] = array($this_robot_token);
        $temp_battle_omega['battle_complete'] = false;
        $temp_battle_omega['battle_counts'] = true;
        $temp_battle_omega['battle_size'] = '1x1';
        $temp_battle_omega['battle_name'] = 'Chapter Two Master Battle';
        $temp_battle_omega['battle_token'] = ($starfield_mission ? 'starfield' : $this_prototype_data['phase_battle_token']).'-'.$this_robot_token;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_field_base']['field_id'] = 100;
        $temp_battle_omega['battle_field_base']['field_token'] = $temp_option_field['field_token'];
        $temp_battle_omega['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';
        $temp_battle_omega['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);
        $temp_option_completed = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        if (!empty($temp_option_completed)){ $temp_battle_omega['battle_complete'] = $temp_option_completed; }

        // If this is a starfield mission, adjust for the new conditions
        if ($starfield_mission){
            $temp_battle_omega['battle_name'] = 'Bonus Chapter Star Field Battle';
            $temp_battle_omega['battle_counts'] = false;
            $temp_battle_omega['flags']['hide_robots_from_mission_select'] = true;
            $temp_battle_omega['flags']['starfield_mission'] = true;
            $temp_battle_omega['battle_size'] = '1x1';
        }

        // Determine the amount of targets and their ability counts
        $temp_target_count = 1;
        $temp_ability_count = 1;
        $temp_limit_count = 4;
        $temp_battle_count = 0;
        if ($starfield_mission){
            $temp_target_count = 4;
            $temp_ability_count = 4;
        } elseif (!empty($temp_battle_omega['battle_complete']['battle_count'])){
            $temp_battle_count = $temp_battle_omega['battle_complete']['battle_count'];
            if ($temp_battle_count <= $temp_limit_count){
                $temp_target_count += $temp_battle_count;
                $temp_ability_count += $temp_battle_count;
            } else {
                $temp_target_count += $temp_limit_count;
                $temp_ability_count += $temp_limit_count;
            }
        }

        // Define the fusion star token in case we need to test for it
        $temp_field_star_token = $temp_option_field['field_token'];
        $temp_field_star_present = $starfield_mission && empty($_SESSION['GAME']['values']['battle_stars'][$temp_field_star_token]) ? true : false;

        // If a field star is present on the field, fill the empty spots with like-typed robots
        if ($starfield_mission
            || $temp_field_star_present){

            $temp_battle_omega['battle_target_player']['player_switch'] = 1.5;
            $temp_robot_tokens = array();
            $temp_robot_tokens[] = $temp_battle_omega['battle_target_player']['player_robots'][0]['robot_token'];
            // Collect factors based on player
            if ($this_prototype_data['this_player_token'] == 'dr-light'){
                $temp_factors_list = array($this_omega_factors_one, array_merge(
                    $this_omega_factors_two,
                    $this_omega_factors_three,
                    $this_omega_factors_four,
                    $this_omega_factors_five,
                    $this_omega_factors_six,
                    $this_omega_factors_seven,
                    $this_omega_factors_eight,
                    $this_omega_factors_eight_two,
                    $this_omega_factors_nine,
                    $this_omega_factors_ten,
                    $this_omega_factors_eleven
                    ));
            } elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){
                $temp_factors_list = array($this_omega_factors_two, array_merge(
                    $this_omega_factors_three,
                    $this_omega_factors_four,
                    $this_omega_factors_five,
                    $this_omega_factors_six,
                    $this_omega_factors_seven,
                    $this_omega_factors_eight,
                    $this_omega_factors_eight_two,
                    $this_omega_factors_nine,
                    $this_omega_factors_ten,
                    $this_omega_factors_eleven,
                    $this_omega_factors_one
                    ));
            } elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){
                $temp_factors_list = array($this_omega_factors_three, array_merge(
                    $this_omega_factors_four,
                    $this_omega_factors_five,
                    $this_omega_factors_six,
                    $this_omega_factors_seven,
                    $this_omega_factors_eight,
                    $this_omega_factors_eight_two,
                    $this_omega_factors_nine,
                    $this_omega_factors_ten,
                    $this_omega_factors_eleven,
                    $this_omega_factors_one,
                    $this_omega_factors_two
                    ));
            }
            // Shuffle the bonus robots section of the list
            shuffle($temp_factors_list[1]);
            //$debug_backup = 'initial:count = '.count($temp_battle_omega['battle_target_player']['player_robots']).' // ';
            // Loop through and add the robots
            $temp_counter = 0;
            foreach ($temp_factors_list AS $this_list){
                shuffle($this_list);
                foreach ($this_list AS $this_factor){
                    //$debug_backup .= 'factor = '.implode(',', array_values($this_factor)).' // ';
                    if (empty($this_factor['robot'])){ continue; }
                    $bonus_robot_info = rpg_robot::parse_index_info($this_robot_index[$this_factor['robot']]);
                    if (!isset($bonus_robot_info['robot_core'])){ $bonus_robot_info['robot_core'] = ''; }
                    if ($bonus_robot_info['robot_core'] == $temp_option_field['field_type']){
                        if (!in_array($bonus_robot_info['robot_token'], $temp_robot_tokens)){
                            $bonus_robot_info['flags']['hide_from_mission_select'] = true;
                            $temp_battle_omega['battle_target_player']['player_robots'][] = $bonus_robot_info;
                            $temp_robot_tokens[] = $bonus_robot_info['robot_token'];
                        }
                    }
                }
            }
            //$debug_backup .= 'before:count = '.count($temp_battle_omega['battle_target_player']['player_robots']).' // ';
            $temp_slice_limit = 2; //1 + $temp_battle_omega['battle_complete']['battle_count'];
            //if ($temp_slice_limit >= (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 4)){ $temp_slice_limit = (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 4); }
            //elseif ($temp_slice_limit >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ $temp_slice_limit = 8; }
            $temp_battle_omega['battle_target_player']['player_robots'] = array_slice($temp_battle_omega['battle_target_player']['player_robots'], 0, $temp_slice_limit);
            shuffle($temp_battle_omega['battle_target_player']['player_robots']);
            //$debug_backup .= 'after:count = '.count($temp_battle_omega['battle_target_player']['player_robots']).' // ';

        }


        // Define the omega variables for level, zenny, turns, and random encounter rate
        if ($starfield_mission){
            $omega_robot_level_max = $this_start_level;
            $omega_robot_level = $this_start_level;
        } else {
            $omega_robot_level_max = $this_start_level + 7;
            if ($omega_robot_level_max >= 100){ $omega_robot_level_max = 100; }
            $omega_robot_level = $this_start_level + (!empty($this_prototype_data['battles_complete']) ? $this_prototype_data['battles_complete'] - 1 : 0);
            if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
            if ($omega_robot_level >= 100){ $omega_robot_level = 100; }
        }

        // Define the battle rewards based on above data
        $temp_battle_omega['battle_rewards']['robots'] = array();
        $temp_battle_omega['battle_rewards']['abilities'] = array();
        $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $this_robot_token);
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
            if ($starfield_mission){ $bonus_robot_count += 3; }
            elseif ($this_prototype_data['this_player_token'] == 'dr-light'){ $bonus_robot_count += 1; }
            elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){ $bonus_robot_count += 2; }
            elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){ $bonus_robot_count += 3; }
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
                $temp_mook_info = rpg_robot::parse_index_info($this_robot_index[$temp_mook_token]);
                $bonus_robot_info = array('robot_token' => $temp_mook_token, 'robot_id' => 1, 'robot_level' => 1);
                $bonus_robot_info['robot_abilities'] = $temp_mook_info['robot_abilities'];
                $bonus_robot_info['robot_class'] = !empty($temp_mook_info['robot_class']) ? $temp_mook_info['robot_class'] : 'master';
                $bonus_robot_info['robot_name'] = $temp_mook_info['robot_name'];
                $temp_mook_name_token = $bonus_robot_info['robot_name_token'] = str_replace(' ', '-', strtolower($bonus_robot_info['robot_name']));
                if (!isset($temp_mook_counts[$temp_mook_name_token])){ $temp_mook_counts[$temp_mook_name_token] = 0; }
                else { $temp_mook_counts[$temp_mook_name_token]++; }
                //$bonus_robot_info['robot_name'] .= ' '.$temp_mook_letters[$temp_mook_counts[$temp_mook_name_token]];
                $temp_battle_omega['battle_target_player']['player_robots'][] = $bonus_robot_info;
                $temp_robot_tokens[] = $bonus_robot_info['robot_token'];
            }
            // Shuffle all the target player robots and then loop through them to make final changes
            //shuffle($temp_battle_omega['battle_target_player']['player_robots']);
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
                // Update the robot ID to prevent collisions
                $info['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1;
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
            if (!empty($temp_battle_omega['battle_complete']['battle_min_level'])){ $temp_complete_level = $temp_battle_omega['battle_complete']['battle_min_level']; }
            else { $temp_complete_level = $temp_omega_robot_level; }
            if (!empty($temp_battle_omega['battle_complete']['battle_count'])){ $temp_complete_count = $temp_battle_omega['battle_complete']['battle_count']; }
            else { $temp_complete_count = 1; }
            $temp_omega_robot_level = $temp_complete_level + $temp_complete_count - 1;
            // DEBUG
            //echo('battle is complete '.$temp_battle_omega['battle_token'].' | omega robot level'.$temp_omega_robot_level.' | battle_level '.$temp_battle_omega['battle_complete']['battle_level'].' | battle_count '.$temp_battle_omega['battle_complete']['battle_count'].'<br />');
        } else {

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
        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot){

            // Ensure this robot's token exists in the index, else continue
            if (isset($this_robot_index[$robot['robot_token']])){ $robot = rpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]); }
            else { continue; }

            // Update the robot level and battle zenny plus turns
            $temp_robot_level = $robot['robot_class'] != 'mecha' ? $temp_omega_robot_level : mt_rand(1, ceil($temp_omega_robot_level / 3));
            $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $temp_robot_level;
            if ($robot['robot_class'] == 'master'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $temp_robot_level);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
            } elseif ($robot['robot_class'] == 'mecha'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $temp_robot_level);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA;
            }

            // If this is a mecha, only allow limited extra abilities
            $ability_count = $temp_ability_count;
            if ($robot['robot_class'] == 'mecha'){
                $ability_count = ceil($ability_count / 2);
                if ($ability_count > 2){ $ability_count = 2; }
            }

            // Randomly assign this robot a hold item if applicable
            $temp_item = '';
            if ($robot['robot_class'] == 'master'){
                $rand = $starfield_mission || $temp_field_star_present ? mt_rand(1, 4) : mt_rand(1, 10);
                if ($rand == 1){
                    $stats = array('energy', 'weapon', 'attack', 'defense', 'speed');
                    $items = array('pellet', 'capsule');
                    $temp_item = $stats[mt_rand(0, (count($stats) - 1))].'-'.$items[mt_rand(0, (count($items) - 1))];
                }
                $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $temp_item;
            }

            // Generate abilities and update the omega robot array
            $temp_abilities = mmrpg_prototype_generate_abilities($robot, $omega_robot_level, $ability_count, $temp_item);
            $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = $temp_abilities;

            // If this is a mecha with alt images, randomly assign one
            if ($robot['robot_class'] == 'mecha' && !empty($robot['robot_image_alts'])){
                $images = array($robot['robot_token']);
                foreach ($robot['robot_image_alts'] AS $alt){
                    if (count($images) > $temp_complete_count){ break; }
                    $images[] = $robot['robot_token'].'_'.$alt['token'];
                }
                shuffle($images);
                $temp_image = array_shift($images);
                $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_image'] = $temp_image;
            }

        }

        // Reduce the zenny earned from this mission each time it is completed
        if ($temp_complete_count > 0){ $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * (2 / (2 + $temp_complete_count))); }

        // If this is a starfield mission, it will give slightly less zenny than usual
        if ($starfield_mission){ $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * 0.1); }
        if ($starfield_mission && !$temp_field_star_present){ $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * 0.1); }

        // Reverse the order of the robots in battle
        $temp_battle_omega['battle_target_player']['player_robots'] = array_reverse($temp_battle_omega['battle_target_player']['player_robots']);
        $temp_first_robot = array_shift($temp_battle_omega['battle_target_player']['player_robots']);
        shuffle($temp_battle_omega['battle_target_player']['player_robots']);
        array_unshift($temp_battle_omega['battle_target_player']['player_robots'], $temp_first_robot);

        // Empty the robot rewards array if not allowed
        if (!$this_unlock_robots){ $temp_battle_omega['battle_rewards']['robots'] = array(); }
        // Empty the ability rewards array if not allowed
        if (!$this_unlock_abilities){ $temp_battle_omega['battle_rewards']['abilities'] = array(); }

        // Define the number of abilities and robots left to unlock and start at zero
        $this_unlock_robots_count = count($temp_battle_omega['battle_rewards']['robots']);
        $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards']['abilities']);

        // Loop through the omega battle robot rewards and update the robot levels there too
        if (!empty($temp_battle_omega['battle_rewards']['robots'])){
            foreach ($temp_battle_omega['battle_rewards']['robots'] AS $key2 => $robot){
                // Update the robot level and battle or button details
                $temp_battle_omega['battle_rewards']['robots'][$key2]['level'] = $temp_omega_robot_level;  //1;
                // Remove if this robot is already unlocked
                if (mmrpg_prototype_robot_unlocked(false, $robot['token'])){
                    $this_unlock_robots_count -= 1;
                    // If this isn't a starforce/starfield mission, we don't show already-unlocked robots
                    if ($temp_battle_omega['battle_complete']
                        && !$starfield_mission
                        && !$temp_field_star_present){
                        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $rm_key => $rm_robot){
                            if ($rm_robot['robot_token'] === $robot['token']){
                                //$rm_robot['flags']['hide_from_mission_select'] = true;
                                $rm_robot['flags']['shadow_on_mission_select'] = true;
                                $temp_battle_omega['battle_target_player']['player_robots'][$rm_key] = $rm_robot;
                            }
                        }
                    }
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

        // Check to see if we should be adding a field star to this battle
        if ($temp_field_star_present){

            // Generate the necessary field star variables and add them to the battle data
            $temp_field_star = array();
            $temp_field_star['star_name'] = $temp_option_field['field_name'];
            $temp_field_star['star_token'] = $temp_field_star_token;
            $temp_field_star['star_kind'] = 'field';
            $temp_field_star['star_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
            $temp_field_star['star_type2'] = !empty($temp_option_field['field_type2']) ? $temp_option_field['field_type2'] : '';
            $temp_field_star['star_field'] = $temp_option_field['field_token'];
            $temp_field_star['star_field2'] = '';
            $temp_field_star['star_player'] = $this_prototype_data['this_player_token'];
            $temp_field_star['star_date'] = time();
            $temp_battle_omega['values']['field_star'] = $temp_field_star;
            $temp_battle_omega['battle_target_player']['player_starforce'] = array();
            $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type']] = 1;

            // Increase the power of the robot masters by 10% in each field
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot){
                if (!isset($this_robot_index[$robot['robot_token']])){ continue; }
                $rindex = $this_robot_index[$robot['robot_token']];
                // Update the robot rewards array by adding 10% to each of the three main stats
                $temp_battle_omega['battle_target_player']['player_robots'][$key]['values']['robot_rewards'] = array(
                    'robot_attack' => round($rindex['robot_attack'] * 0.10),
                    'robot_defense' => round($rindex['robot_defense'] * 0.10),
                    'robot_speed' => round($rindex['robot_speed'] * 0.10)
                    );
            }

        }

        // Update the battle description based on what we've calculated
        if ($starfield_mission){
            if ($temp_field_star_present){
                $temp_battle_omega['battle_description'] = 'Defeat the robot masters guarding this Field Star to liberate it! ';
                $temp_battle_omega['battle_description2'] = 'Collecting stars increases our Starforce and makes us stronger in battle! ';
            } else {
                $temp_battle_omega['battle_description'] = 'Defeat the regenerated robot masters and support mechas! ';
                $temp_battle_omega['battle_description2'] = 'The Field Star for this area has already been liberated, but we can go back as many times as we need to. ';
            }
        } elseif (!empty($temp_battle_omega['values']['field_star'])){
            $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].' and collect its Field Star! ';
            $temp_battle_omega['battle_description2'] = 'The star\'s energy appears to have attracted another robot master to the field...';
        } else if (!empty($this_unlock_abilities_count)){
            $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].' and download its special weapon!';
            $temp_battle_omega['battle_description2'] = 'Once we\'ve acquired it, we may be able to equip the ability to other robots...';
        } elseif (!empty($this_unlock_robots_count)){
            $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].' and download its robot data!';
            $temp_battle_omega['battle_description2'] = 'If we use only Neutral type abilities on the target we may be able to save it...';
        } else {
            $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].'!';
        }

        // Return the generated battle data
        return $temp_battle_omega;

    }

}
?>