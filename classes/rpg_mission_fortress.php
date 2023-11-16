<?php
/**
 * Mega Man RPG Fotress-Battle Mission
 * <p>The fortress mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_fortress extends rpg_mission {

    public static function prepare(&$this_fortress_battle, $this_prototype_data){

        // Pull in required object indexes
        $mmrpg_players_index = rpg_player::get_index(true);
        $mmrpg_robots_index = rpg_robot::get_index(true);

        // Update the target user ID, player ID, and robot IDs
        $temp_target_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_target_player_id = rpg_game::unique_player_id($temp_target_user_id, 0);
        if ($this_fortress_battle['battle_target_player']['player_token'] !== 'player'){
            $temp_target_player_info = $mmrpg_players_index[$this_fortress_battle['battle_target_player']['player_token']];
            $temp_target_player_id = rpg_game::unique_player_id($temp_target_user_id, $temp_target_player_info['player_id']);
        }
        $temp_battle_target_player = array('user_id' => 0, 'player_id' => 0);
        $this_fortress_battle['battle_target_player'] = array_merge($temp_battle_target_player, $this_fortress_battle['battle_target_player']);
        $this_fortress_battle['battle_target_player']['user_id'] = $temp_target_user_id;
        $this_fortress_battle['battle_target_player']['player_id'] = $temp_target_player_id;

        // Loop through target robots and re-generate unique IDs for each of them
        foreach ($this_fortress_battle['battle_target_player']['player_robots'] AS $key => $robot){
            $temp_target_robot_info = $mmrpg_robots_index[$robot['robot_token']];
            $temp_target_robot_id = rpg_game::unique_robot_id($temp_target_player_id, $temp_target_robot_info['robot_id'], ($key + 1));
            $this_fortress_battle['battle_target_player']['player_robots'][$key]['robot_id'] = $temp_target_robot_id;
        }

        // Calcuate appropriate zenny prizes and turn limits
        rpg_mission::calculate_mission_zenny_and_turns($this_fortress_battle, $this_prototype_data, $mmrpg_robots_index);

    }

    // Define a function for generating the FORTRESS  missions
    public static function generate($this_prototype_data, $this_battle_config, $this_robot_tokens_or_data, $this_field_tokens_or_data, $this_start_level = 1){

        // Collect the session token
        $session_token = mmrpg_game_token();

        // Pull in global variables for this function
        global $db;

        // Collect the robot index for calculation purposes
        $mmrpg_robots_index = rpg_robot::get_index(true);
        $mmrpg_abilities_index = rpg_ability::get_index(true);
        $mmrpg_fields_index = rpg_field::get_index();

        // Predefine some battle config defaults if not already set
        if (!isset($this_battle_config['auto_hide_mechas'])){ $this_battle_config['auto_hide_mechas'] = true; }
        if (!isset($this_battle_config['auto_unlock_target_robots'])){ $this_battle_config['auto_unlock_target_robots'] = false; }
        if (!isset($this_battle_config['auto_unlock_target_abilities'])){ $this_battle_config['auto_unlock_target_abilities'] = false; }

        // If the provided robot tokens were actually data, extract the tokens
        $this_robot_tokens = array();
        $this_robot_data = array();
        if (isset($this_robot_tokens_or_data[0])
            && is_array($this_robot_tokens_or_data[0])
            && isset($this_robot_tokens_or_data[0]['robot_token'])){
            $this_robot_tokens = array_map(function($info){ return $info['robot_token']; }, $this_robot_tokens_or_data);
            $this_robot_data = $this_robot_tokens_or_data;
        } elseif (is_array($this_robot_tokens_or_data)
            && isset($this_robot_tokens_or_data['robot_token'])){
            $this_robot_tokens = array($this_robot_tokens_or_data['robot_token']);
            $this_robot_data = array($this_robot_tokens_or_data);
        } elseif (is_array($this_robot_tokens_or_data)
            && isset($this_robot_tokens_or_data[0])){
            $this_robot_tokens = $this_robot_tokens_or_data;
        } elseif (is_string($this_robot_tokens_or_data)){
            $this_robot_tokens = array($this_robot_tokens_or_data);
        } else {
            return false;
        }

        // If the provided field tokes were actually data, extract the tokens
        $this_field_tokens = array();
        $this_field_data = array();
        if (isset($this_field_tokens_or_data[0])
            && is_array($this_field_tokens_or_data[0])
            && isset($this_field_tokens_or_data[0]['field_token'])){
            $this_field_tokens = array_map(function($info){ return $info['field_token']; }, $this_field_tokens_or_data);
            $this_field_data = $this_field_tokens_or_data;
        } elseif (is_array($this_field_tokens_or_data)
            && isset($this_field_tokens_or_data['field_token'])){
            $this_field_tokens = array($this_field_tokens_or_data['field_token']);
            $this_field_data = array($this_field_tokens_or_data);
        } elseif (is_array($this_field_tokens_or_data)
            && isset($this_field_tokens_or_data[0])){
            $this_field_tokens = $this_field_tokens_or_data;
        } elseif (is_string($this_field_tokens_or_data)){
            $this_field_tokens = array($this_field_tokens_or_data);
        } else {
            return false;
        }

        // Predefine the chapter name because it's weird like that
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $chapter_num = $this_prototype_data['this_current_chapter'] + 1;
        $chapter_name = 'Chapter '.ucfirst($formatter->format($chapter_num));

        // Define the omega battle option and default to empty
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_battle_omega = array();
        $temp_battle_omega['flags']['fortress_battle'] = true;
        $temp_battle_omega['values']['fortress_battle_masters'] = $this_robot_tokens;
        $temp_battle_omega['battle_token'] = $this_prototype_data['phase_battle_token'].'-fortress-'.trim(str_replace('-man', '', implode('-', $this_robot_tokens)), '-');
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_size'] = isset($this_battle_config['battle_size']) ? $this_battle_config['battle_size'] : '1x4';
        $temp_battle_omega['battle_name'] = $chapter_name.' Fortress Battle';
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        $temp_battle_omega['battle_complete'] = false;
        $temp_battle_omega['battle_counts'] = true;
        $temp_battle_omega['flags'] = $temp_battle_omega['flags'];
        $temp_battle_omega['values'] = $temp_battle_omega['values'];
        $temp_option_completed = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        if ($temp_option_completed){ $temp_battle_omega['battle_complete'] = $temp_option_completed; }
        $temp_auto_assign_items = false;

        // Determine the amount of targets and their ability counts
        $temp_target_count = 1;
        $temp_ability_count = 8;
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

        // Define the user and player ID as well as any details about the player themselves
        $temp_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
        $temp_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';

        // If there's more than one field, we need to combine them into a FUSION FIELD to battle on
        if (count($this_field_tokens) > 1){
            $temp_option_field = $mmrpg_fields_index[$this_field_tokens[0]];
            $temp_option_field2 = isset($this_field_tokens[1]) ? $mmrpg_fields_index[$this_field_tokens[1]] : $temp_option_field;
            $temp_option_multipliers = array();
            foreach ($this_field_tokens AS $this_field_key => $this_field_token){
                $temp_field = $mmrpg_fields_index[$this_field_token];
                if (!empty($temp_field['field_multipliers'])){
                    foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                        if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                        else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                    }
                }
            }
            $temp_battle_field_base = array();
            $temp_battle_field_base['field_token'] = $temp_option_field['field_token'];
            $temp_battle_field_base['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_name']);
            $temp_battle_field_base['field_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
            $temp_battle_field_base['field_type2'] = !empty($temp_option_field2['field_type']) ? $temp_option_field2['field_type'] : '';
            $temp_battle_field_base['field_music'] = $temp_option_field2['field_music'];
            $temp_battle_field_base['field_foreground'] = $temp_option_field2['field_foreground'];
            $temp_battle_field_base['field_foreground_attachments'] = $temp_option_field2['field_foreground_attachments'];
            $temp_battle_field_base['field_background'] = $temp_option_field['field_background'];
            $temp_battle_field_base['field_background_attachments'] = $temp_option_field['field_background_attachments'];
            $temp_battle_field_base['field_multipliers'] = $temp_option_multipliers;
            if (isset($this_field_data[0])){ $temp_battle_field_base = array_merge($temp_battle_field_base, $this_field_data[0]); }
            if (isset($this_field_data[1])){ $temp_battle_field_base = array_merge($temp_battle_field_base, $this_field_data[1]); }
            $temp_battle_omega['battle_field_base'] = $temp_battle_field_base;
        }
        // Otherwise if this is just for a SINGLE FIELD, it's easier to define the parameters
        else {
            $temp_option_field = $mmrpg_fields_index[$this_field_tokens[0]];
            $temp_battle_field_base = array();
            $temp_battle_field_base['field_token'] = $temp_option_field['field_token'];
            $temp_battle_field_base['field_name'] = $temp_option_field['field_name'];
            $temp_battle_field_base['field_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
            $temp_battle_field_base['field_music'] = $temp_option_field['field_music'];
            $temp_battle_field_base['field_foreground'] = $temp_option_field['field_foreground'];
            $temp_battle_field_base['field_foreground_attachments'] = $temp_option_field['field_foreground_attachments'];
            $temp_battle_field_base['field_background'] = $temp_option_field['field_background'];
            $temp_battle_field_base['field_background_attachments'] = $temp_option_field['field_background_attachments'];
            $temp_battle_field_base['field_multipliers'] = $temp_option_field['field_multipliers'];
            if (isset($this_field_data[0])){ $temp_battle_field_base = array_merge($temp_battle_field_base, $this_field_data[0]); }
            $temp_battle_omega['battle_field_base'] = $temp_battle_field_base;
        }

        // Define the omega variables for level, zenny, turns, and random encounter rate
        $omega_robot_level_max = $this_start_level; //$this_start_level + 5;
        if ($omega_robot_level_max >= 100){ $omega_robot_level_max = 100; }
        $omega_robot_level = $this_start_level;
        if (!empty($temp_option_completed) && !empty($temp_option_completed)){ $omega_robot_level += $temp_option_completed; }
        if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
        if ($omega_robot_level >= 100){ $omega_robot_level = 100; }

        // Create an empty array for the robot masters and get ready to populate
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_robot_master_tokens = array();

        // Loop through the provided robot tokens and add them to the battle
        foreach ($this_robot_tokens AS $robot_key => $robot_token){

            // Add the robot as the fusion boss' backup minion
            $temp_robot_token = $robot_token;
            $temp_robot_custom_info = isset($this_robot_data[$robot_key]) ? $this_robot_data[$robot_key] : array();
            $temp_robot_index_info = $mmrpg_robots_index[$temp_robot_token];
            $temp_robot_info = array();
            $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $temp_robot_index_info['robot_id'], $robot_key);
            $temp_robot_info['robot_token'] = $temp_robot_token;
            $temp_robot_info = array_merge($temp_robot_info, $temp_robot_custom_info);
            $temp_robot_master_tokens[] = $temp_robot_token;
            $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_robot_info;
            //error_log('adding '.$temp_robot_token.' to a '.$this_prototype_data['this_player_token'].' fusion field battle $temp_robot_info = '.print_r($temp_robot_info, true));

        }

        // Reassign robot IDs to prevent errors
        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
            $temp_battle_omega['battle_target_player']['player_robots'][$key]['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $mmrpg_robots_index[$info['robot_token']]['robot_id'], ($key + 1));
        }
        $temp_battle_omega['battle_rewards']['robots'] = array();
        $temp_battle_omega['battle_rewards']['abilities'] = array();
        $temp_battle_omega = $temp_battle_omega;

        // Skip the empty battle button or a different phase
        if (empty($temp_battle_omega['battle_token']) || $temp_battle_omega['battle_token'] == 'battle' || $temp_battle_omega['battle_phase'] != $this_prototype_data['battle_phase']){ return false; }

        // Collect the battle token and create an omega clone from the index base
        $temp_battle_token = $temp_battle_omega['battle_token'];
        // Make copies of the robot level var and adjust
        $temp_omega_robot_level = $omega_robot_level;

        // If the battle was already complete, collect its details for later
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
            if (!isset($mmrpg_robots_index[$robot_data['robot_token']])){ continue; }
            $robot_token = $robot_data['robot_token'];
            $robot_info = $mmrpg_robots_index[$robot_token];
            $new_robot_data = $robot_data;

            // Update the robot level and battle zenny plus turns
            //$temp_robot_level = $robot_info['robot_class'] !== 'mecha' ? $temp_omega_robot_level : floor($temp_omega_robot_level / 2);
            //if ($temp_robot_level < 1){ $temp_robot_level = 1; }
            if (!empty($new_robot_data['robot_level'])){
                $temp_robot_level = $new_robot_data['robot_level'];
            } else {
                $temp_robot_level = $temp_omega_robot_level;
                $new_robot_data['robot_level'] = $temp_robot_level;
            }
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
                if ($temp_auto_assign_items
                    && empty($temp_item)){
                    $temp_option_field = $mmrpg_fields_index[$this_field_tokens[0]];
                    $temp_option_field2 = isset($this_field_tokens[1]) ? $mmrpg_fields_index[$this_field_tokens[1]] : $temp_option_field;
                    if ($robot_info['robot_core'] != $temp_option_field['field_type']){ $temp_item = $temp_option_field['field_type'].'-core'; }
                    else { $temp_item = $temp_option_field2['field_type'].'-core'; }
                }
                $ability_count += 1;
                $new_robot_data['robot_item'] = $temp_item;
            }

            // Generate abilities and update the omega robot array
            if (empty($robot_data['robot_abilities'])){
                $robot_info_data = array_merge($robot_info, $robot_data);
                $temp_abilities = mmrpg_prototype_generate_abilities($robot_info_data, $omega_robot_level, $ability_count, $temp_item);
                $new_robot_data['robot_abilities'] = $temp_abilities;
                if ($robot_info['robot_class'] == 'mecha'){
                    $temp_native_abilities = array();
                    if (!empty($robot_index_info['robot_rewards']['abilities'])){
                        foreach ($robot_index_info['robot_rewards']['abilities'] AS $info){
                            if (!isset($mmrpg_abilities_index[$info['token']])){ continue; }
                            elseif (!$mmrpg_abilities_index[$info['token']]['ability_flag_complete']){ continue; }
                            $temp_native_abilities[] = $info['token'];
                        }
                    }
                    if (!empty($temp_native_abilities)){
                        $new_robot_data['robot_abilities'] = array_merge($temp_native_abilities, $temp_abilities);
                        $new_robot_data['robot_abilities'] = array_unique($new_robot_data['robot_abilities']);
                        $new_robot_data['robot_abilities'] = array_slice($new_robot_data['robot_abilities'], 8);
                    }
                }
                //error_log('$new_robot_data[\'robot_abilities\'] = '.print_r($new_robot_data['robot_abilities'], true));
            }

            // This was a mecha and we're supposed to be hiding them, let's do that
            if ($robot_info['robot_class'] == 'mecha' && !empty($this_battle_config['auto_hide_mechas'])){ $new_robot_data['flags']['hide_from_mission_select'] = true; }

            // Update the omega robot with recent changes to the array
            $temp_battle_omega['battle_target_player']['player_robots'][$key2] = $new_robot_data;

        }

        // Reduce the zenny earned from this mission each time it is completed
        if ($temp_complete_count > 0){ $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * (2 / (2 + $temp_complete_count))); }

        // Empty the robot rewards array if not allowed
        $temp_battle_omega['battle_rewards']['robots'] = array();
        if ($this_battle_config['auto_unlock_target_robots']){
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot_info){
                $index_info = $mmrpg_robots_index[$robot_info['robot_token']];
                if ($index_info['robot_class'] == 'master'
                    && $index_info['robot_flag_unlockable']
                    && !$index_info['robot_flag_exclusive']
                    && !mmrpg_game_robot_unlocked('', $robot_info['robot_token'])){
                    $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $robot_info['robot_token'], 'level' => $omega_robot_level, 'experience' => 999);
                }
            }
        } elseif (!empty($this_battle_config['robot_rewards'])){
            $temp_battle_omega['battle_rewards']['robots'] = $this_battle_config['robot_rewards'];
        }

        // Empty the ability rewards array if not allowed
        $temp_battle_omega['battle_rewards']['abilities'] = array();
        if ($this_battle_config['auto_unlock_target_abilities']){
            $temp_option_robot = $mmrpg_robots_index[$this_robot_tokens[0]];
            $temp_option_robot2 = isset($this_robot_tokens[1]) ? $mmrpg_robots_index[$this_robot_tokens[1]] : false;
            if (!empty($temp_option_robot['robot_rewards']['abilities'])){
                foreach ($temp_option_robot['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
                    $temp_battle_omega['battle_rewards']['abilities'][] = $info;
                    break; // only unlock first ability (T1) if simply clearing the stage
                }
            }
            if (!empty($temp_option_robot2)
                && !empty($temp_option_robot2['robot_rewards']['abilities'])){
                foreach ($temp_option_robot2['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
                    $temp_battle_omega['battle_rewards']['abilities'][] = $info;
                    break; // only unlock first ability (T1) if simply clearing the stage
                }
            }
        } elseif (!empty($this_battle_config['ability_rewards'])){
            $temp_battle_omega['battle_rewards']['abilities'] = $this_battle_config['ability_rewards'];
        }

        // Define the number of abilities and robots left to unlock and start at zero
        $this_unlock_robots_count = count($temp_battle_omega['battle_rewards']['robots']);
        $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards']['abilities']);

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
        if (empty($temp_battle_omega['battle_description'])){
            $temp_target_robots_names = array();
            foreach ($this_robot_data AS $robot_key => $robot_data){
                if (!isset($mmrpg_robots_index[$robot_data['robot_token']])){ continue; }
                $robot_token = $robot_data['robot_token'];
                $robot_info = $mmrpg_robots_index[$robot_token];
                if ($robot_info['robot_class'] == 'mecha' && !empty($this_battle_config['auto_hide_mechas'])){ continue; }
                $temp_target_robots_names[] = $robot_info['robot_name'];
            }
            $temp_description_target_subject = count($this_robot_data) === 1 ? 'robot' : 'robots';
            if (count($temp_target_robots_names) === 1){ $temp_description_target_robots = implode('', $temp_target_robots_names); }
            elseif (count($temp_target_robots_names) === 2){ $temp_description_target_robots = implode(' and ', $temp_target_robots_names); }
            elseif (count($temp_target_robots_names) >= 3){ $temp_description_target_robots = implode(', ', array_slice($temp_target_robots_names, 0, -1)).', and '.$temp_target_robots_names[count($temp_target_robots_names) - 1]; }
            $temp_battle_omega['battle_description'] = 'Defeat the target '.$temp_description_target_subject.' '.$temp_description_target_robots.' in battle!';
            //error_log('battle description: '.$temp_battle_omega['battle_description']);
        }

        // Add some random item drops to the starter battle
        $temp_battle_omega['battle_rewards']['items'] = array(

            );

        //error_log('$temp_battle_omega['.$temp_battle_omega['battle_token'].'] = '.print_r($temp_battle_omega, true));

        // Run final (re)caluclations then return the generated battle data
        self::prepare($temp_battle_omega, $this_prototype_data);
        return $temp_battle_omega;

    }


}
?>