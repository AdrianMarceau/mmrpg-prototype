<?php
/**
 * Mega Man RPG Bonus-Battle Mission
 * <p>The bonus mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_bonus extends rpg_mission {

    // Define a function for generating the BONUS missions
    public static function generate($this_prototype_data, $this_robot_count = 8, $this_robot_class = 'master'){

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

        // Collect the types index for calculation purposes
        $mmrpg_index_robots = rpg_robot::get_index(true);
        $mmrpg_index_types = rpg_type::get_index();

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
            $robot_index_query .= "AND robot_token NOT IN ('auto', 'bond-man', 'pulse-man') ";
        } elseif ($this_robot_class == 'mecha'){
            $robot_index_query .= "AND robot_class = 'mecha' ";
            $robot_index_query .= "AND base_total <= 400 ";
            $robot_index_query .= "AND robot_token NOT IN ('rush', 'beat', 'tango', 'mariachi', 'reggae', 'treble') ";
            $robot_index_query .= "AND robot_token NOT IN ('weapon-archivist') ";
            $robot_index_query .= "AND robot_token NOT IN ('dark-frag') ";
        } elseif ($this_robot_class == 'boss'){
            $robot_index_query .= "AND robot_class = 'boss' ";
            $robot_index_query .= "AND base_total >= 400 ";
            $robot_index_query .= "AND robot_flag_hidden = 0 AND (robot_flag_fightable = 1
                AND robot_number NOT LIKE 'EXN-%'
                AND robot_number NOT LIKE 'SRN-%'
                AND robot_number NOT LIKE 'PCR-%'
                AND robot_number NOT LIKE 'RPG-%'
                ) ";
            $robot_index_query .= "AND robot_token NOT IN ('quint', 'sunstar', 'cache') ";
        }
        $robot_index_query .= "AND robot_flag_published = 1 ";
        $robot_index_query .= "AND robot_flag_exclusive = 0 ";
        $robot_index_query .= "ORDER BY robot_id ASC ";
        //error_log($robot_index_query);
        $cache_token = md5($robot_index_query);
        $cached_index = rpg_object::load_cached_index('mission.bonus.targets', $cache_token);
        if (!empty($cached_index)){
            $bonus_robot_tokens = $cached_index;
            unset($cached_index);
        } else {
            $bonus_robots_raw = $db->get_array_list($robot_index_query, 'robot_token');
            $bonus_robot_tokens = array_keys($bonus_robots_raw);
            rpg_object::save_cached_index('mission.bonus.targets', $cache_token, $bonus_robot_tokens);
        }

        // Populate the battle options with the starter battle option
        $temp_rand_num = $this_robot_count;
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-prototype-bonus-'.$this_robot_class;
        if ($this_robot_class == 'mecha'){
            $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete');
            $temp_battle_omega['battle_field_base']['field_name'] = 'Bonus Field I (Mecha Support)';
            $temp_battle_omega['battle_name'] = 'Bonus Battle vs Random Mechas';
            $temp_battle_omega['battle_description'] = 'Face off against a randomized assortment of support mechas in this special bonus battle!';
            $temp_battle_omega['battle_description2'] = 'This mission is great for grinding EXP or collecting Small Screws and Mecha Shards!';
        }
        elseif ($this_robot_class == 'master'){
            $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-2');
            $temp_battle_omega['battle_field_base']['field_name'] = 'Bonus Field II (Robot Masters)';
            $temp_battle_omega['battle_name'] = 'Bonus Battle vs Random Masters';
            $temp_battle_omega['battle_description'] = 'Face off against a randomized assortment of robot masters in this special bonus battle!';
            $temp_battle_omega['battle_description2'] = 'This mission is great for grinding EXP or collecting Large Screws and Robot Cores!';
        }
        elseif ($this_robot_class == 'boss'){
            $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-2');
            $temp_battle_omega['battle_field_base']['field_name'] = 'Bonus Field III (Fortress Bosses)';
            $temp_battle_omega['battle_name'] = 'Bonus Battle vs Random Bosses';
            $temp_battle_omega['battle_description'] = 'Face off against a randomized assortment of fortress bosses in this special bonus battle!';
            $temp_battle_omega['battle_description2'] = 'This mission is great for grinding EXP or collecting Large Screws and Robot Cores!';
        }

        // Collect an index of which robots have been encountered already
        $session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();

        // Populate the player's target robots with compatible class matches
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_battle_omega['battle_target_player'] = array_merge(array('user_id' => 0, 'player_id' => 0), $temp_battle_omega['battle_target_player']);
        $temp_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
        $temp_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_counter = 0;
        foreach ($bonus_robot_tokens AS $key => $token){
            $info = $mmrpg_index_robots[$token];
            if (empty($info['robot_flag_complete']) || $info['robot_class'] != $this_robot_class){ continue; }
            if (!isset($session_robot_database[$token]) || empty($session_robot_database[$token]['robot_encountered'])){ continue; }
            $temp_counter++;
            $temp_robot_info = array();
            $temp_robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $info['robot_id'], $temp_counter);
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
        $temp_bonus_level_min = 1;
        $temp_bonus_level_max = 10;
        if (!empty($temp_player_rewards['player_robots'])){
            foreach ($temp_player_rewards['player_robots'] AS $token => $info){
                $temp_level = !empty($info['robot_level']) ? $info['robot_level'] : 1;
                if ($temp_level > $temp_bonus_level_max){ $temp_bonus_level_max = $temp_level; }
                $temp_total_robots++;
            }
            if ($temp_bonus_level_max > 10){
                $temp_bonus_level_min = $temp_bonus_level_max - 10;
            }
        }

        // Define a list of items that can be equipped to target robots
        static $hold_item_index;
        static $hold_item_list;
        if (empty($hold_item_index)){
            $hold_item_index = $db->get_array_list("SELECT item_token
                FROM mmrpg_index_items
                WHERE
                item_flag_hidden = 0
                AND item_flag_complete = 1
                AND item_flag_published = 1
                AND item_subclass IN ('consumable', 'holdable')
                ;", 'item_token');
            $hold_item_list = !empty($hold_item_index) ? array_keys($hold_item_index) : array();
        }

        // Loop through each of the bonus robots and update their levels
        $temp_games_counter = array();
        $temp_battle_omega['battle_zenny'] = 0;
        $temp_battle_omega['battle_turns'] = 0;
        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
            $info['robot_level'] = mt_rand($temp_bonus_level_min, $temp_bonus_level_max);
            $index = $mmrpg_index_robots[$info['robot_token']];
            // Keep track of which game this robot/mecha/etc. is from
            if (!isset($temp_games_counter[$index['robot_game']])){ $temp_games_counter[$index['robot_game']] = 0; }
            $temp_games_counter[$index['robot_game']] += 1;
            // Randomly attach a hold or consumable item to this bot
            if (mt_rand(0, 100) >= 75){ $info['robot_item'] = $hold_item_list[mt_rand(0, (count($hold_item_list) - 1))]; }
            // Generate a number of abilities based on robot class
            if ($this_robot_class == 'boss'){ $extra_count = 8; }
            elseif ($this_robot_class == 'master'){ $extra_count = 6; }
            elseif ($this_robot_class == 'mecha'){ $extra_count = 4; }
            $index_plus_info = array_merge($index, $info);
            $info['robot_abilities'] = mmrpg_prototype_generate_abilities($index_plus_info, $info['robot_level'], $extra_count);
            // Use a random alt image for this robot if available (not for bosses though)
            if ($this_robot_class != 'boss'
                && !empty($index['robot_image_alts'])
                && count($index['robot_image_alts']) > 1
                && mt_rand(0, 100) <= 30){
                $images = array($info['robot_token']);
                foreach ($index['robot_image_alts'] AS $alt){
                    if ($alt['token'] == 'alt9'){ continue; }
                    $images[] = $info['robot_token'].'_'.$alt['token'];
                }
                shuffle($images);
                $info['robot_image'] = array_shift($images);
            }
            // Calculate the battle zenny and turns based on class
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
        $temp_types = $mmrpg_index_types;
        $temp_allow_special = array(); //, 'damage', 'recovery', 'experience'
        foreach ($temp_types AS $key => $temp_type){
            if (!empty($temp_type['type_class'])
                && $temp_type['type_class'] == 'special'
                && !in_array($temp_type['type_token'], $temp_allow_special)){
                unset($temp_types[$key]);
            }
        }
        //$temp_battle_omega['battle_field_base']['field_multipliers']['experience'] = round((mt_rand(200, 300) / 100), 1);
        //$temp_battle_omega['battle_field_base']['field_type'] = $temp_types[array_rand($temp_types)]['type_token'];
        //do { $temp_battle_omega['battle_field_base']['field_type2'] = $temp_types[array_rand($temp_types)]['type_token'];
        //} while($temp_battle_omega['battle_field_base']['field_type2'] == $temp_battle_omega['battle_field_base']['field_type']);

        $temp_battle_omega['battle_field_base']['field_multipliers'] = array();
        while (!empty($temp_types) && count($temp_battle_omega['battle_field_base']['field_multipliers']) < 6){
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

        // Sort the games counter by highest-count at the top
        $temp_games_counter = rpg_functions::shuffle_array($temp_games_counter, true);
        $temp_games_counter = rpg_functions::reverse_sort_array($temp_games_counter, true);
        // Update the field music to a random boss theme best-representing combatants
        $temp_music_path = false;
        $temp_music_index = rpg_game::get_music_paths_index();
        foreach ($temp_games_counter AS $game_code => $target_counter){
            $temp_path = 'sega-remix/boss-theme-'.strtolower($game_code);
            if (isset($temp_music_index[$temp_path])){
                $temp_music_path = $temp_path;
                break;
            }
        }
        // If an appropriate track count not be found, just pick a random one from MM1-11
        if (empty($temp_music_path)){ $temp_music_path = 'sega-remix/boss-theme-mm'.mt_rand(1, 11); }
        // Update the battle with the selected music path
        $temp_battle_omega['battle_field_base']['field_music'] = $temp_music_path;

        // Add some random item drops to the starter battle
        $temp_battle_omega['battle_rewards']['items'] = array(

            );

        // Return the generated battle data
        return $temp_battle_omega;

    }

}
?>