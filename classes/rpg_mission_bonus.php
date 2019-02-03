<?php
/**
 * Mega Man RPG Bonus-Battle Mission
 * <p>The bonus mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_bonus extends rpg_mission {

    // Define a function for generating the BONUS missions
    public static function generate($this_prototype_data, $this_robot_count = 8, $this_robot_class = 'master'){

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
        $robot_index_query = "SELECT {$db_robot_fields},
            (robot_energy + robot_attack + robot_defense + robot_speed) AS base_total
            FROM mmrpg_index_robots
            HAVING robot_flag_complete = 1 ";
        if ($this_robot_class == 'master'){
            $robot_index_query .= "AND robot_class = 'master' ";
            $robot_index_query .= "AND base_total = 400 ";
            $robot_index_query .= "AND (robot_flag_hidden = 0 OR (robot_flag_unlockable = 1 AND robot_number NOT LIKE 'RPG-%' AND robot_number NOT LIKE 'PCR-%')) ";
            $robot_index_query .= "AND robot_token NOT LIKE '%-copy' ";
            $robot_index_query .= "AND robot_flag_published = 1 ";
            if (true){ $robot_index_query .= "OR robot_token = 'quint' "; }
        } elseif ($this_robot_class == 'mecha'){
            $robot_index_query .= "AND robot_class = 'mecha' ";
            $robot_index_query .= "AND base_total <= 400 ";
            $robot_index_query .= "AND robot_flag_published = 1 ";
        }
        $robot_index_query .= "ORDER BY robot_order ASC ";
        $this_robot_index = $db->get_array_list($robot_index_query, 'robot_token');

        // Populate the battle options with the starter battle option
        $temp_rand_num = $this_robot_count;
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-prototype-bonus-'.$this_robot_class;
        if ($this_robot_class == 'mecha'){
            $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete');
            $temp_battle_omega['battle_field_base']['field_name'] = 'Mecha Field';
        }
        elseif ($this_robot_class == 'master'){
            $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-2');
            $temp_battle_omega['battle_field_base']['field_name'] = 'Master Field';
        }
        // Populate the player's target robots with compatible class matches
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_counter = 0;
        foreach ($this_robot_index AS $token => $info){
            if (empty($info['robot_flag_complete']) || $info['robot_class'] != $this_robot_class){ continue; }
            $temp_counter++;
            $temp_robot_info = array();
            $temp_robot_info['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_counter;
            $temp_robot_info['robot_token'] = $info['robot_token'];
            $temp_robot_info['robot_core'] = $info['robot_core'];
            $temp_robot_info['robot_core2'] = $info['robot_core2'];
            $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_robot_info;
        }
        $temp_battle_omega['flags']['bonus_battle'] = true;
        $temp_battle_omega['battle_token'] = $temp_battle_token;
        $temp_battle_omega['battle_size'] = '1x4';
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        shuffle($temp_battle_omega['battle_target_player']['player_robots']);
        $temp_battle_omega['battle_target_player']['player_robots'] = array_slice($temp_battle_omega['battle_target_player']['player_robots'], 0, $this_robot_count);

        // Calculate what level these bonus robots should be in the range of
        $temp_player_rewards = mmrpg_prototype_player_rewards($this_prototype_data['this_player_token']);
        $temp_total_level = 0;
        $temp_total_robots = 0;
        $temp_bonus_level_min = 100;
        $temp_bonus_level_max = 1;
        if (!empty($temp_player_rewards['player_robots'])){
            foreach ($temp_player_rewards['player_robots'] AS $token => $info){
                $temp_level = !empty($info['robot_level']) ? $info['robot_level'] : 1;
                if ($temp_level > $temp_bonus_level_max){ $temp_bonus_level_max = $temp_level; }
                if ($temp_level < $temp_bonus_level_min){ $temp_bonus_level_min = $temp_level; }
                $temp_total_robots++;
            }
            //$temp_bonus_level_max = ceil($temp_total_level / $temp_total_robots);
            //$temp_bonus_level_min = ceil($temp_bonus_level_max / 3);
        }

        // Loop through each of the bonus robots and update their levels
        $temp_battle_omega['battle_zenny'] = 0;
        $temp_battle_omega['battle_turns'] = 0;
        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
            $info['robot_level'] = mt_rand($temp_bonus_level_min, $temp_bonus_level_max);
            $index = rpg_robot::parse_index_info($this_robot_index[$info['robot_token']]);
            // Generate a number of abilities based on robot class
            if ($this_robot_class == 'boss'){ $extra_count = 8; }
            elseif ($this_robot_class == 'master'){ $extra_count = 6; }
            elseif ($this_robot_class == 'mecha'){ $extra_count = 4; }
            $info['robot_abilities'] = mmrpg_prototype_generate_abilities($index, $info['robot_level'], $extra_count);
            // Use a random alt image for this robot if available
            if (!empty($index['robot_image_alts'])){
                $images = array($info['robot_token']);
                foreach ($index['robot_image_alts'] AS $alt){
                    if ($alt['token'] == 'alt9'){ continue; }
                    $images[] = $info['robot_token'].'_'.$alt['token'];
                }
                shuffle($images);
                $info['robot_image'] = array_shift($images);
            }
            if ($index['robot_class'] == 'master' || $index['robot_class'] == 'boss'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $info['robot_level']);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
            } elseif ($index['robot_class'] == 'mecha'){
                $temp_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $info['robot_level']);
                $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA;
            }
            $temp_battle_omega['battle_target_player']['player_robots'][$key] = $info;
        }

        // Multiply battle zenny by ten for bonus amount, this is NOT a zenny-grinding area
        $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] / 100);

        // Create the randomized field multupliers
        $temp_types = $mmrpg_index['types'];
        $temp_allow_special = array(); //, 'damage', 'recovery', 'experience'
        foreach ($temp_types AS $key => $temp_type){ if (!empty($temp_type['type_class']) && $temp_type['type_class'] == 'special' && !in_array($temp_type['type_token'], $temp_allow_special)){ unset($temp_types[$key]); } }
        //$temp_battle_omega['battle_field_base']['field_multipliers']['experience'] = round((mt_rand(200, 300) / 100), 1);
        //$temp_battle_omega['battle_field_base']['field_type'] = $temp_types[array_rand($temp_types)]['type_token'];
        //do { $temp_battle_omega['battle_field_base']['field_type2'] = $temp_types[array_rand($temp_types)]['type_token'];
        //} while($temp_battle_omega['battle_field_base']['field_type2'] == $temp_battle_omega['battle_field_base']['field_type']);

        $temp_battle_omega['battle_field_base']['field_multipliers'] = array();
        while (count($temp_battle_omega['battle_field_base']['field_multipliers']) < 6){
            $temp_type = $temp_types[array_rand($temp_types)];
            $temp_multiplier = 1;
            while ($temp_multiplier == 1){ $temp_multiplier = round((mt_rand(10, 990) / 100), 1); }
            $temp_battle_omega['battle_field_base']['field_multipliers'][$temp_type['type_token']] = $temp_multiplier;
            //if (count($temp_battle_omega['battle_field_base']['field_multipliers']) >= 6){ break; }
        }

        // Update the field type based on multipliers
        $temp_multipliers = $temp_battle_omega['battle_field_base']['field_multipliers'];
        asort($temp_multipliers);
        $temp_multipliers = array_keys($temp_multipliers);
        $temp_battle_omega['battle_field_base']['field_type'] = array_pop($temp_multipliers);
        $temp_battle_omega['battle_field_base']['field_type2'] = array_pop($temp_multipliers);

        // Update the field music to a random boss theme from MM1-10 + MM&B
        $temp_music_number = mt_rand(1, 11);
        $temp_music_name = 'boss-theme-mm'.str_pad($temp_music_number, 2, '0', STR_PAD_LEFT);
        $temp_battle_omega['battle_field_base']['field_music'] = $temp_music_name;

        // Add some random item drops to the starter battle
        $temp_battle_omega['battle_rewards']['items'] = array(

            );

        // Return the generated battle data
        return $temp_battle_omega;

    }

}
?>