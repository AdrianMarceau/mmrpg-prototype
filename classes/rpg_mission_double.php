<?php
/**
 * Mega Man RPG Double-Battle Mission
 * <p>The double mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_double extends rpg_mission {

    // Define a function for generating the DOUBLES missions
    public static function generate($this_prototype_data, $this_robot_tokens, $this_field_tokens, $this_start_level = 1, $this_unlock_robots = true, $this_unlock_abilities = true){
        //error_log("rpg_mission_double::generate(\$this_prototype_data, '".implode(',', $this_robot_tokens)."', '".implode(',', $this_field_tokens)."', {$this_start_level}, {$this_unlock_robots}, {$this_unlock_abilities})");

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
            $battles_complete_fusion_targets = array();
            $fusion = array();
            foreach ($battles_complete_targets AS $key => $rtoken){
                $rtoken_clean = str_replace('-man', '', $rtoken);
                $fusion[] = $rtoken_clean;
                if (count($fusion) === 2){
                    $battles_complete_fusion_targets[] = implode('-', $fusion);
                    $fusion = array();
                }
            }
            //error_log('$battles_complete_fusion_targets = '.print_r($battles_complete_fusion_targets, true));
            foreach ($battles_complete_fusion_targets AS $key => $rtoken_rtoken){
                $btoken = $this_prototype_data['this_player_token'].'-phase2-'.$rtoken_rtoken;
                if (in_array($btoken, $battles_complete_tokens)){ $phase_level_boost += 1; }
            }
        }
        //error_log('$phase_level_boost = '.print_r($phase_level_boost, true));

        // Collect the robot index for calculation purposes
        //$db_robot_fields = rpg_robot::get_index_fields(true);
        //$this_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM `mmrpg_index_robots` WHERE `robot_flag_complete` = 1;", 'robot_token');
        $this_robot_index = rpg_robot::get_index(true);
        $this_field_index = rpg_field::get_index();

        // Define the omega battle option and default to empty
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_battle_omega = array();
        $temp_option_battle = array();
        $temp_option_battle2 = array();
        $temp_option_field = $this_field_index[$this_field_tokens[0]];
        $temp_option_field2 = $this_field_index[$this_field_tokens[1]];
        $temp_option_robot = $this_robot_index[(is_array($this_robot_tokens[0]) ? $this_robot_tokens[0]['robot_token'] : $this_robot_tokens[0])];
        $temp_option_robot2 = $this_robot_index[(is_array($this_robot_tokens[1]) ? $this_robot_tokens[1]['robot_token'] : $this_robot_tokens[1])];
        $temp_battle_omega['flags']['double_battle'] = true;
        $temp_battle_omega['values']['double_battle_masters'] = $this_robot_tokens;
        $temp_option_multipliers = array();
        $temp_option_field_list = array($temp_option_field, $temp_option_field2);
        foreach ($temp_option_field_list AS $temp_field){
            if (!empty($temp_field['field_multipliers'])){
                foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                    if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                    else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                }
            }
        }
        $temp_option_battle['battle_token'] = $this_prototype_data['phase_battle_token'].'-'.str_replace('-man', '', $this_robot_tokens[0]).'-'.str_replace('-man', '', $this_robot_tokens[1]);
        $temp_option_battle['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_option_battle['battle_size'] = '1x2';
        $temp_option_battle['battle_name'] = 'Chapter Four Fusion Battle';
        $temp_option_battle['battle_complete'] = false;
        $temp_option_battle['battle_counts'] = true;
        $temp_option_battle['flags'] = $temp_battle_omega['flags'];
        $temp_option_battle['values'] = $temp_battle_omega['values'];
        $temp_option_completed = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_option_battle['battle_token']);
        if ($temp_option_completed){ $temp_option_battle['battle_complete'] = $temp_option_completed; }

        // Determine the amount of targets and their ability counts
        $temp_target_count = 1;
        $temp_ability_count = 2;
        $temp_limit_count = 8;
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

        $temp_option_battle['battle_target_player']['user_id'] = $temp_user_id;
        $temp_option_battle['battle_target_player']['player_id'] = $temp_player_id;
        $temp_option_battle['battle_target_player']['player_token'] = 'player';
        $temp_option_battle['battle_field_base']['field_token'] = $temp_option_field['field_token'];
        $temp_option_battle['battle_field_base']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_name']);
        $temp_option_battle['battle_field_base']['field_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
        $temp_option_battle['battle_field_base']['field_type2'] = !empty($temp_option_field2['field_type']) ? $temp_option_field2['field_type'] : '';
        $temp_option_battle['battle_field_base']['field_music'] = $temp_option_field2['field_token'];
        $temp_option_battle['battle_field_base']['field_foreground'] = $temp_option_field2['field_foreground'];
        $temp_option_battle['battle_field_base']['field_foreground_attachments'] = $temp_option_field2['field_foreground_attachments'];
        $temp_option_battle['battle_field_base']['field_background'] = $temp_option_field['field_background'];
        $temp_option_battle['battle_field_base']['field_background_attachments'] = $temp_option_field['field_background_attachments'];
        $temp_option_battle['battle_field_base']['field_multipliers'] = $temp_option_multipliers;
        $temp_option_battle['battle_target_player']['player_robots'] = array(); //array_merge($temp_option_battle['battle_target_player']['player_robots'], $temp_option_battle2['battle_target_player']['player_robots']);
        $temp_robot_master_tokens = array();
        if (true){
            //error_log('checkpoint '.basename(__FILE__).' on line '.__LINE__);

            // Preset the key so we can increment later
            $temp_robot_key = -1;

            // Add the threshold guardian for the fusion fields
            $temp_robot_token = 'ra-thor'; // fusion field boss
            $temp_robot_index_info = $this_robot_index[$temp_robot_token];
            $temp_robot_info = array();
            $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_option_robot['robot_id'], $temp_robot_key++);
            $temp_robot_info['robot_token'] = $temp_robot_token;
            $temp_robot_info['robot_name'] = $temp_robot_index_info['robot_name'].' Σ';
            $temp_robot_info['robot_image'] = $temp_robot_token.'_alt9';
            $temp_robot_info['robot_core'] = 'empty';
            $temp_robot_info['robot_item'] = 'super-capsule';
            $temp_robot_info['robot_abilities'] = array('space-shot', 'buster-charge');
            switch ($this_prototype_data['this_player_token']){
                case 'dr-cossack': {
                    array_unshift($temp_robot_info['robot_abilities'], 'shield-eater');
                }
                case 'dr-wily':
                case 'dr-cossack': {
                    array_unshift($temp_robot_info['robot_abilities'], 'lunar-memory');
                }
                case 'dr-light':
                case 'dr-wily':
                case 'dr-cossack': {
                    array_unshift($temp_robot_info['robot_abilities'], 'barrier-drive');
                }
            }
            $temp_robot_master_tokens[] = $temp_robot_token;
            $temp_option_battle['battle_target_player']['player_robots'][] = $temp_robot_info;
            //error_log('adding '.$temp_robot_token.' to a '.$this_prototype_data['this_player_token'].' fusion field battle $temp_robot_info = '.print_r($temp_robot_info, true));

            // Add the first robot as the fusion boss' backup minion
            $temp_robot_token = $this_robot_tokens[0];
            $temp_robot_index_info = $this_robot_index[$temp_robot_token];
            $temp_robot_info = array();
            $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_option_robot['robot_id'], $temp_robot_key++);;
            $temp_robot_info['robot_token'] = $temp_robot_token;
            if (mmrpg_prototype_robot_unlocked('', $temp_robot_token)){
                $temp_robot_info['robot_name'] = $temp_robot_index_info['robot_name'].' Σ';
                $temp_robot_info['robot_image'] = $temp_robot_token.'_alt9';
                $temp_robot_info['robot_core'] = 'empty';
            }
            $temp_robot_master_tokens[] = $temp_robot_token;
            $temp_option_battle['battle_target_player']['player_robots'][] = $temp_robot_info;
            //error_log('adding '.$temp_robot_token.' to a '.$this_prototype_data['this_player_token'].' fusion field battle $temp_robot_info = '.print_r($temp_robot_info, true));

            // Add an appropriate bonus robot IF this mission has not been completed yet
            if (!$temp_option_completed){
                //error_log('VISITOR needed for = '.print_r($temp_option_battle['battle_token'], true));

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
                        AND `robots`.`robot_flag_unlockable` = 1
                        AND `robots`.`robot_flag_exclusive` = 0
                        AND `robots`.`robot_class` = 'master'
                        AND `robots`.`robot_core` NOT IN ('', 'copy')
                        AND `robots`.`robot_game` NOT IN ('MM1', 'MMPU', 'MM2', 'MM4')
                        ORDER BY
                        `groups`.`group_order` ASC,
                        `tokens`.`token_order` ASC
                        ;";
                    $cache_token = md5($temp_possible_visitors_query);
                    $cached_index = rpg_object::load_cached_index('missions.double.visitors', $cache_token);
                    if (!empty($cached_index)){
                        $temp_possible_visitors_raw = $cached_index;
                        unset($cached_index);
                    } else {
                        $temp_possible_visitors_raw = $db->get_array_list($temp_possible_visitors_query);
                        rpg_object::save_cached_index('missions.double.visitors', $cache_token, $temp_possible_visitors_raw);
                    }
                    //error_log('$temp_possible_visitors_raw = '.print_r($temp_possible_visitors_raw, true));
                    if (!empty($temp_possible_visitors_raw)){
                        foreach($temp_possible_visitors_raw AS $key => $info){
                            list($token, $core) = array_values($info);
                            if (!isset($temp_possible_visitors[$core])){ $temp_possible_visitors[$core] = array(); }
                            $temp_possible_visitors[$core][] = $token;
                        }
                    }
                }
                //error_log('$temp_possible_visitors = '.print_r($temp_possible_visitors, true));
                $temp_visitor_token = '';
                $temp_visitor_queue = array();
                if (!empty($temp_possible_visitors[$temp_option_field['field_type']])){
                    $temp_visitor_queue = array_merge($temp_visitor_queue, $temp_possible_visitors[$temp_option_field['field_type']]);
                }
                if (!empty($temp_possible_visitors[$temp_option_field2['field_type']])){
                    $temp_visitor_queue = array_merge($temp_visitor_queue, $temp_possible_visitors[$temp_option_field2['field_type']]);
                }
                if (!empty($temp_visitor_queue)){
                    //error_log('$temp_visitor_queue = '.print_r($temp_visitor_queue, true));
                    foreach ($temp_visitor_queue AS $key => $token){
                        if (mmrpg_prototype_robot_unlocked('', $token)){ continue; }
                        $temp_visitor_token = $token;
                        break;
                    }
                }
                //error_log('$temp_visitor_token = '.print_r($temp_visitor_token, true));
                if (!empty($temp_visitor_token)){
                    $temp_robot_token = $temp_visitor_token;
                    $temp_robot_index_info = $this_robot_index[$temp_robot_token];
                    $temp_robot_info = array();
                    $temp_robot_info['flags'] = array();
                    $temp_robot_info['flags']['robot_is_visitor'] = true;
                    $temp_robot_info['flags']['hide_from_mission_select'] = true;
                    $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_option_robot['robot_id'], $temp_robot_key++);
                    $temp_robot_info['robot_token'] = $temp_robot_token;
                    $temp_robot_info['robot_item'] = 'super-capsule';
                    $temp_robot_master_tokens[] = $temp_robot_token;
                    $temp_option_battle['battle_target_player']['player_robots'][] = $temp_robot_info;
                    //error_log('adding '.$temp_robot_token.' to a '.$this_prototype_data['this_player_token'].' fusion field battle $temp_robot_info = '.print_r($temp_robot_info, true));
                }

            }

            // Add the second robot as the fusion boss' backup minion too
            $temp_robot_token = $this_robot_tokens[1];
            $temp_robot_index_info = $this_robot_index[$temp_robot_token];
            $temp_robot_info = array();
            $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_option_robot2['robot_id'], $temp_robot_key++);;
            $temp_robot_info['robot_token'] = $this_robot_tokens[1];
            if (mmrpg_prototype_robot_unlocked('', $temp_robot_token)){
                $temp_robot_info['robot_name'] = $temp_robot_index_info['robot_name'].' Σ';
                $temp_robot_info['robot_image'] = $temp_robot_token.'_alt9';
                $temp_robot_info['robot_core'] = 'empty';
            }
            $temp_robot_master_tokens[] = $temp_robot_token;
            $temp_option_battle['battle_target_player']['player_robots'][] = $temp_robot_info;
            //error_log('adding '.$temp_robot_token.' to a '.$this_prototype_data['this_player_token'].' fusion field battle $temp_robot_info = '.print_r($temp_robot_info, true));

        }

        // Define the omega variables for level, zenny, turns, and random encounter rate
        $omega_robot_level_max = $this_start_level + 4;
        $omega_robot_level_limit_break = mmrpg_prototype_allow_limit_break() ? true : false;
        if ($omega_robot_level_max >= 100 && !$omega_robot_level_limit_break){ $omega_robot_level_max = 100; }
        $omega_robot_level = $this_start_level;
        if (!empty($temp_option_completed) && !empty($temp_option_completed)){ $omega_robot_level += $temp_option_completed; }
        if (!empty($phase_level_boost)){ $omega_robot_level += $phase_level_boost; }
        if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
        if ($omega_robot_level >= 100 && !$omega_robot_level_limit_break){ $omega_robot_level = 100; }

        // Also, fill the empty spots with minor enemy robots
        if (true){
            $temp_option_battle['battle_target_player']['player_switch'] = 0.5;
            $bonus_robot_count = 0;
            $temp_mook_options = array();
            if (!isset($temp_option_field['field_mechas'])){ $temp_option_field['field_mechas'] = array(); }
            if (!isset($temp_option_field2['field_mechas'])){ $temp_option_field2['field_mechas'] = array(); }
            foreach ($temp_option_field['field_mechas'] AS $key => $token){
                if (!empty($temp_option_field['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field['field_mechas'][$key]; }
                if (!empty($temp_option_field2['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field2['field_mechas'][$key]; }
            }
            if (empty($temp_mook_options)){ $temp_mook_options[] = 'met'; }
            //$temp_mook_options = array_slice($temp_mook_options, 0, 3);
            $temp_option_battle['battle_field_base']['field_mechas'] = $temp_mook_options;
            //error_log('(t1) $temp_option_battle[\'battle_field_base\'][\'field_mechas\'] = '.print_r($temp_option_battle['battle_field_base']['field_mechas'], true));

        }

        // Reassign robot IDs to prevent errors
        foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
            $temp_option_battle['battle_target_player']['player_robots'][$key]['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $this_robot_index[$info['robot_token']]['robot_id'], ($key + 1));
        }
        $temp_option_battle['battle_rewards']['robots'] = array();
        $temp_option_battle['battle_rewards']['abilities'] = array();
        $temp_battle_omega = $temp_option_battle;

        // Skip the empty battle button or a different phase
        if (empty($temp_battle_omega['battle_token']) || $temp_battle_omega['battle_token'] == 'battle' || $temp_battle_omega['battle_phase'] != $this_prototype_data['battle_phase']){ return false; }

        // Collect the battle token and create an omega clone from the index base
        $temp_battle_token = $temp_battle_omega['battle_token'];
        // Make copies of the robot level var and adjust
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
                if ($ability_count > 4){ $ability_count = 4; }
            }

            // Randomly assign this robot a hold item if applicable
            $temp_item = !empty($robot_data['robot_item']) ? $robot_data['robot_item'] : '';
            //error_log('checking item ('.$temp_item.') for $robot '.print_r($robot_data, true));
            if ($robot_info['robot_class'] == 'master'){
                if (false){
                    $rand = mt_rand(1, 3);
                    if ($rand == 1){
                        $stats = array('energy', 'weapon', 'attack', 'defense', 'speed');
                        $items = array('pellet', 'capsule');
                        $temp_item = $stats[mt_rand(0, (count($stats) - 1))].'-'.$items[mt_rand(0, (count($items) - 1))];
                    } elseif ($rand == 2){
                        if ($robot_info['robot_core'] != $temp_option_field['field_type']){ $temp_item = $temp_option_field['field_type'].'-core'; }
                        else { $temp_item = $temp_option_field2['field_type'].'-core'; }
                        $ability_count += 1;
                    }
                } else {
                    if (empty($temp_item)){
                        if ($robot_info['robot_core'] != $temp_option_field['field_type']){ $temp_item = $temp_option_field['field_type'].'-core'; }
                        else { $temp_item = $temp_option_field2['field_type'].'-core'; }
                    }
                    $ability_count += 1;
                }
                $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $temp_item;
            }

            // Generate abilities and update the omega robot array
            if (empty($robot_data['robot_abilities'])){
                $robot_info_data = array_merge($robot_info, $robot_data);
                $temp_abilities = mmrpg_prototype_generate_abilities($robot_info_data, $omega_robot_level, $ability_count, $temp_item);
                $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = $temp_abilities;
            }

        }

        // Reduce the zenny earned from this mission each time it is completed
        if ($temp_complete_count > 0){ $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * (2 / (2 + $temp_complete_count))); }

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
        $temp_battle_omega['battle_rewards']['abilities'] = array();
        if ($this_unlock_abilities){
            if (!empty($temp_option_robot['robot_rewards']['abilities'])){
                foreach ($temp_option_robot['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
                    $temp_battle_omega['battle_rewards']['abilities'][] = $info;
                    break; // only unlock first ability (T1) if simply clearing the stage
                }
            }
            if (!empty($temp_option_robot2['robot_rewards']['abilities'])){
                foreach ($temp_option_robot2['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
                    $temp_battle_omega['battle_rewards']['abilities'][] = $info;
                    break; // only unlock first ability (T1) if simply clearing the stage
                }
            }
        }

        // Define the number of abilities and robots left to unlock and start at zero
        $this_unlock_robots_count = count($temp_battle_omega['battle_rewards']['robots']);
        $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards']['abilities']);

        // Review the unlockable RMs for this field and grey-out any that are already unlocked
        if (!empty($temp_battle_omega['values']['double_battle_masters'])
            && $temp_battle_omega['battle_complete']){
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot_data){
                if (!in_array($robot_data['robot_token'], $temp_battle_omega['values']['double_battle_masters'])){ continue; }
                if (mmrpg_prototype_robot_unlocked(false, $robot_data['robot_token'])){
                    $robot_data['flags']['shadow_on_mission_select'] = true;
                    $temp_battle_omega['battle_target_player']['player_robots'][$key] = $robot_data;
                    $this_unlock_robots_count -= 1;
                }
            }
        }

        // Loop through the omega battle ability rewards and update the ability levels there too
        if (!empty($temp_battle_omega['battle_rewards']['abilities'])){
            foreach ($temp_battle_omega['battle_rewards']['abilities'] AS $key2 => $ability){
                // Remove if this ability is already unlocked
                if (mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, $ability['token'])){
                    $this_unlock_abilities_count -= 1;
                }
            }
        }

        // Update the battle description based on what we've calculated
        $temp_description_target_robots = $temp_option_robot['robot_name'].' and '.$temp_option_robot2['robot_name'];
        $temp_battle_omega['battle_description'] = 'Defeat the powered up copies of '.$temp_description_target_robots.' on their fusion field!';

        // Add some random item drops to the starter battle
        $temp_battle_omega['battle_rewards']['items'] = array(

            );

        //error_log(basename(__FILE__).' on line '.__LINE__.' :: $temp_battle_omega[\'battle_target_player\'][\'player_robots\'] = '.print_r($temp_battle_omega['battle_target_player']['player_robots'], true));

        // Return the generated battle data
        return $temp_battle_omega;

    }

}
?>