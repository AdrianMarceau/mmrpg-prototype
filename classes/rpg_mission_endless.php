<?php
/**
 * Mega Man RPG Endless Mission
 * <p>The endless mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_endless extends rpg_mission {

    // Define a function for generating an endless mission seed given a count
    public static function generate_endless_mission_seed($mission_number, $return_json = false){

        // Define the list of static indexes so we don't waste time regenerating on repeat triggers
        static $indexes_preloaded = false;
        static $mmrpg_types_index, $mmrpg_robots_index, $mmrpg_items_index, $mmrpg_fields_index;
        static $mmrpg_robots_index_bycore, $mmrpg_items_index_bykind, $mmrpg_fields_index_bytype;
        static $mmrpg_robots_cores, $mmrpg_items_kinds, $mmrpg_fields_types;
        static $mmrpg_robots_cores_count, $mmrpg_items_kinds_count, $mmrpg_fields_types_count;
        static $mmrpg_endless_loop_size;
        if (!$indexes_preloaded){

            // Collect a list of unlocked RMs, types, etc. from the database
            $mmrpg_types_index = rpg_type::get_index();
            $mmrpg_robots_index = rpg_robot::get_index(false, false, 'master');
            $mmrpg_items_index = rpg_item::get_index(false, false, array('consumable', 'holdable'));
            $mmrpg_fields_index = rpg_field::get_index();

            // Sort the robot masters into lists of their core types
            $mmrpg_robots_index_bycore = array();
            foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                if (empty($robot_info['robot_flag_complete'])){ unset($mmrpg_robots_index[$robot_token]); continue; }
                $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
                if (!isset($mmrpg_robots_index_bycore[$robot_core])){ $mmrpg_robots_index_bycore[$robot_core] = array(); }
                $mmrpg_robots_index_bycore[$robot_core][] = $robot_token;
            }

            // Sort the robot masters into lists of their core types
            $mmrpg_items_index_bykind = array();
            foreach ($mmrpg_items_index AS $item_token => $item_info){
                if ($item_token == 'extra-life' || $item_token == 'yashichi'){ continue; } // we don't want cheap items
                list($item_subkind1, $item_subkind2) = explode('-', $item_token);
                $item_subkind2 .= substr($item_subkind2, -1, 1) == 's' ? 'es' : 's';
                if (!isset($mmrpg_items_index_bykind[$item_subkind2])){ $mmrpg_items_index_bykind[$item_subkind2] = array(); }
                $mmrpg_items_index_bykind[$item_subkind2][] = $item_token;
            }

            // Sort the robot masters into lists of their core types
            $mmrpg_fields_index_bytype = array();
            foreach ($mmrpg_fields_index AS $field_token => $field_info){
                if ($field_token == 'prototype-complete'){ continue; } // we don't want prototype complete
                if (empty($field_info['field_flag_complete'])){ unset($mmrpg_fields_index[$field_token]); continue; }
                $field_type = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
                if ($field_token == 'intro-field'){ $field_type = 'copy'; }
                elseif (strstr($field_token, 'final-destination')){ $field_type = 'copy'; }
                if (!isset($mmrpg_fields_index_bytype[$field_type])){ $mmrpg_fields_index_bytype[$field_type] = array(); }
                $mmrpg_fields_index_bytype[$field_type][] = $field_token;
            }

            //echo('<pre>$mmrpg_items_index_bykind = '.print_r($mmrpg_items_index_bykind, true).'</pre>');
            //echo('<pre>$mmrpg_fields_index_bytype = '.print_r($mmrpg_fields_index_bytype, true).'</pre>');

            // Collect and count the number of core types represented
            $mmrpg_robots_cores = array_keys($mmrpg_robots_index_bycore);
            $mmrpg_robots_cores_count = count($mmrpg_robots_cores);

            // Collect and count the number of item kinds represented
            $mmrpg_items_kinds = array_keys($mmrpg_items_index_bykind);
            $mmrpg_items_kinds_count = count($mmrpg_items_kinds);

            // Collect and count the number of type types represented
            $mmrpg_fields_types = array_keys($mmrpg_fields_index_bytype);
            $mmrpg_fields_types_count = count($mmrpg_fields_types);

            // Define loop size for the missions given num cores available
            $mmrpg_endless_loop_size = $mmrpg_robots_cores_count;

            // Set the preload flag to true now
            $indexes_preloaded = true;

        }


        // Predefine the mission details array
        $mission_data = array();
        //$mission_data['num'] = $mission_number;

        // Determine phase according to where we are in the core loop
        $mission_phase = $mission_number > $mmrpg_endless_loop_size ? ceil($mission_number / $mmrpg_endless_loop_size) : 1;
        $mission_is_double = $mission_phase >= 4 ? true : false;
        $mission_data['phase'] = $mission_phase;

        // Decide which core type or types will be represented
        $mission_coretypes = array();
        $mission_coretypes[] = select_from_array_with_rollover($mmrpg_robots_cores, ($mission_number));
        if ($mission_is_double){ $mission_coretypes[] = select_from_array_with_rollover($mmrpg_robots_cores, ($mission_number + 1 + ($mission_phase - 4))); }

        $mission_coretypes = array_unique($mission_coretypes);
        $mission_data['types'] = $mission_coretypes;

        // Determine the number of robots that should appear
        $num_targets = 3;
        if ($mission_is_double){ $num_targets *= 2; }
        $mission_data['size'] = $num_targets;

        // Decide which field this will be played on
        $field_type = $mission_coretypes[0];
        $field_options = $mmrpg_fields_index_bytype[$field_type];
        $field_token = select_from_array_with_rollover($field_options, $mission_phase);
        $mission_data['field'] = $field_token;
        if ($mission_is_double
            && isset($mission_coretypes[1])){
            $field_type2 = $mission_coretypes[1];
            $field_options2 = $mmrpg_fields_index_bytype[$field_type2];
            $field_token2 = select_from_array_with_rollover($field_options2, ($mission_phase + 1));
            $mission_data['field2'] = $field_token2;
        }

        // Define which items may appear on this robot
        $possible_item_kinds = array();
        if ($mission_phase >= 1){ $possible_item_kinds[] = 'capsules'; $possible_item_kinds[] = 'tanks'; }
        if ($mission_phase >= 2){ $possible_item_kinds[] = 'upgrades'; $possible_item_kinds[] = 'modules'; }
        if ($mission_phase >= 3){ $possible_item_kinds[] = 'boosters'; $possible_item_kinds[] = 'circuits'; }
        if ($mission_phase >= 4){ $possible_item_kinds[] = 'cores'; }

        // Combine all possible items into a single list and then shuffle it
        $possible_item_tokens = array();
        foreach ($possible_item_kinds AS $kind){ $possible_item_tokens = array_merge($possible_item_tokens, $mmrpg_items_index_bykind[$kind]); }
        shuffle($possible_item_tokens);

        // Define which robots will appear based on typelist and phasearray_unique($mission_data['targets'])
        $mission_targets = array();
        for ($target_num = 1; $target_num <= $num_targets; $target_num++){

            // Define the target string empty to start
            $target_string = '';

            // Select the core type for this robot from the list of available
            $target_type = select_from_array_with_rollover($mission_coretypes, $target_num);

            // Select robot targets from the array by type, rotating for variety and exposure
            $target_robot_options = $mmrpg_robots_index_bycore[$target_type];
            if ($mission_phase > 1){
                $shifts_required = ($mission_phase - 1) * $num_targets;
                for ($i = 0; $i < $shifts_required; $i++){
                    array_push($target_robot_options, array_shift($target_robot_options));
                }
            }
            if ($mission_is_double){
                $target_token = select_from_array_with_rollover($target_robot_options, (ceil($target_num / 2) + ($target_num % 2 == 0 ? 1 : $mission_phase)));
            } else {
                $target_token = select_from_array_with_rollover($target_robot_options, $target_num);
            }
            $target_string .= $target_token;

            // Select a random item kind and then select a random item from that list
            //$rand_item_kind = $possible_item_kinds[mt_rand(0, (count($possible_item_kinds) - 1))];
            //$rand_item_token = $mmrpg_items_index_bykind[$rand_item_kind][mt_rand(0, (count($mmrpg_items_index_bykind[$rand_item_kind]) - 1))];
            $rand_item_token = array_shift($possible_item_tokens);
            $target_string .= '@'.$rand_item_token;

            // Append this robot string to the mission
            if (!empty($target_string)){ $mission_targets[] = $target_string; }

        }
        $mission_data['targets'] = $mission_targets;

        if ($mission_data['targets'] != array_unique($mission_data['targets'])){
            //$mission_data['targets'] = array_values(array_unique($mission_data['targets']));
            $mission_data['DUPLICATES'] = true;
        }

        if ($return_json){ return json_encode($mission_data); }
        else { return $mission_data; }

    }

    // Define a function for generating an endless mission given a count
    public static function generate_endless_mission($this_prototype_data, $mission_number){

        // Collect a list of possible stars
        static $possible_star_list = false;
        static $max_star_force = false;
        if ($possible_star_list === false){
            $possible_star_list = mmrpg_prototype_possible_stars(true);
            $max_star_force = array();
            if (!empty($possible_star_list)){
                foreach ($possible_star_list AS $star_token => $star_info){
                    if (!isset($max_star_force[$star_info['info1']['type']])){ $max_star_force[$star_info['info1']['type']] = 0; }
                    if (!empty($star_info['info2']) && !isset($max_star_force[$star_info['info2']['type']])){ $max_star_force[$star_info['info2']['type']] = 0; }
                    if ($star_info['kind'] == 'fusion'){
                        if ($star_info['info1']['type'] == $star_info['info2']['type']){
                            $max_star_force[$star_info['info1']['type']] += 2;
                        } else {
                            $max_star_force[$star_info['info1']['type']] += 1;
                            $max_star_force[$star_info['info2']['type']] += 1;
                        }
                    } else {
                        $max_star_force[$star_info['info1']['type']] += 1;
                    }
                }
            }
        }

        // Precollect a static field list for reference
        static $mmrpg_fields_index = false;
        static $mmrpg_robots_index = false;
        if ($mmrpg_fields_index === false){ $mmrpg_fields_index = rpg_field::get_index(); }
        if ($mmrpg_robots_index === false){ $mmrpg_robots_index = rpg_robot::get_index(false, false, 'master'); }

        // Collect the endless mission seed based on the mission number
        $temp_battle_seed = self::generate_endless_mission_seed($mission_number);

        // Precollect data about the requested fields
        $temp_option_field = $mmrpg_fields_index[(!empty($temp_battle_seed['field']) ? $temp_battle_seed['field'] : 'intro-field')];
        $temp_option_field2 = $mmrpg_fields_index[(!empty($temp_battle_seed['field2']) ? $temp_battle_seed['field2'] : $temp_option_field['field_token'])];
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

        // Do not allow experience mods for these multipliers
        unset($temp_option_multipliers['experience']);

        // Define the target field data with seed data
        $target_field = array();
        //$target_field['field_background'] = $temp_option_field['field_token'];
        //$target_field['field_foreground'] = $temp_option_field2['field_token'];
        //$target_field['field_music'] = $temp_option_field['field_music'];
        $target_field['field_token'] = $temp_option_field['field_token'];
        $target_field['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_name']);
        $target_field['field_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
        $target_field['field_type2'] = !empty($temp_option_field2['field_type']) ? $temp_option_field2['field_type'] : '';
        $target_field['field_music'] = !empty($temp_option_field2['field_music']) ? $temp_option_field2['field_music'] : $temp_option_field2['field_token'];
        $target_field['field_foreground'] = $temp_option_field2['field_foreground'];
        $target_field['field_foreground_attachments'] = $temp_option_field2['field_foreground_attachments'];
        $target_field['field_background'] = $temp_option_field['field_background'];
        $target_field['field_background_attachments'] = $temp_option_field['field_background_attachments'];
        $target_field['field_multipliers'] = $temp_option_multipliers;
        //$target_field['field_music'] = 'sega-remix/boss-theme-mm10';
        //$target_field['values'] = array('hazards' => array('super_blocks' => 'right'));

        // Decide what level the battle should be at after overflow
        $challenge_battle_level = 100;
        if ($temp_battle_seed['phase'] > 1){
            $level_max_boost = (10 * ($temp_battle_seed['phase'] - 1));
            $challenge_battle_level += $level_max_boost;
        } if ($challenge_battle_level > 999){
            $challenge_battle_level = 999;
        }

        // Define the target player with seed data
        $target_player = array(
            'player_token' => 'player',
            'player_starforce' => $max_star_force
            );

        // Generate the list of target robots given the seed data
        $target_robots = array();
        //$statmods = min(5, ($temp_battle_seed['phase'] - 1));
        //$statmods = $mission_number > 1 ? mt_rand(0, min(5, $mission_number)) : 0;
        //$statmodmax = $mission_number > 1 ? min(5, $mission_number) : 0;
        $statmodmax = $temp_battle_seed['phase'] > 1 ? min(5, ($temp_battle_seed['phase'] - 1)) : 0;
        $statrewards = 9999;
        foreach ($temp_battle_seed['targets'] AS $key => $target){
            list($robot, $item) = explode('@', $target);
            $target_robots[] = array(
                'robot_token' => $robot,
                'robot_item' => $item,
                'counters' => array(
                    'attack_mods' => mt_rand(0, $statmodmax),
                    'defense_mods' => mt_rand(0, $statmodmax),
                    'speed_mods' => mt_rand(0, $statmodmax)
                    ),
                'values' => array(
                    'robot_level_max' => $challenge_battle_level,
                    'robot_rewards' => array(
                        'robot_attack' => $statrewards,
                        'robot_defense' => $statrewards,
                        'robot_speed' => $statrewards
                        )
                    )
                );
        }

        // Now that robot data has been parsed, let's try to customize music
        $music_is_customized = false;
        if ($temp_battle_seed['types'][0] == 'copy'
            || $temp_battle_seed['types'][0] == 'none'){

            $atoken = 'sega-remix';
            if ($temp_battle_seed['types'][0] == 'copy'){ $moptions = array('wily-fortress-1-mm08', 'wily-fortress-2-mm08', 'wily-fortress-3-mm08', 'wily-fortress-4-mm08'); }
            elseif ($temp_battle_seed['types'][0] == 'none'){ $moptions = array('wily-fortress-1-mm07', 'wily-fortress-2-mm07', 'wily-fortress-3-mm07', 'wily-fortress-4-mm07'); }
            $mtoken = select_from_array_with_rollover($moptions, $mission_number);
            $music_path = $atoken.'/'.$mtoken.'/';
            if (file_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path)){
                $target_field['field_music'] = $music_path;
                $music_is_customized = true;
            } else {

            }

        } elseif (!empty($target_robots)){

            // For all other types, collect first robot and theme after them
            $atoken = 'sega-remix';
            $rtoken = $target_robots[0]['robot_token'];
            $gtoken = strtolower($mmrpg_robots_index[$rtoken]['robot_game']);
            $music_path = $atoken.'/'.$rtoken.'-'.$gtoken.'/';
            //$temp_battle_omega['battle_description2'] .= '| maybe music:'.$music_path.' ';
            if (file_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path)){
                $target_field['field_music'] = $music_path;
                $music_is_customized = true;
            } else {
                $atoken = 'fallbacks';
                $music_path2 = $atoken.'/'.$rtoken.'-'.$gtoken.'/';
                //$temp_battle_omega['battle_description2'] .= '| maybe music2:'.$music_path2.' ';
                if (file_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path2)){
                    $target_field['field_music'] = $music_path2;
                    $music_is_customized = true;
                }
            }

        }

        // If custom music was not assigned, we should at least check the default exists
        if (!$music_is_customized){
            $boss_theme_num = str_pad(($mission_number > 10 ? ($mission_number % 10) : $mission_number), 2, '0', STR_PAD_LEFT);
            $target_field['field_music'] = 'sega-remix/boss-theme-mm'.$boss_theme_num;
            $music_is_customized = true;
        }

        // Define and battle flag, values, or counters we need to
        $challenge_flags = array();
        $challenge_values = array();
        $challenge_counters = array();
        $challenge_flags['challenge_battle'] = true;
        $challenge_flags['endless_battle'] = true;
        if (false){ $challenge_flags['is_hidden'] = true; }
        if (false){ $challenge_flags['is_cleared'] = true; }
        $challenge_values['challenge_battle_id'] = -1;
        $challenge_values['challenge_battle_kind'] = 'event';
        $challenge_values['challenge_battle_by'] = '';
        $challenge_values['challenge_marker'] = 'base';
        if ($temp_battle_seed['phase'] >= 2){ $challenge_values['challenge_marker'] = 'bronze'; }
        if ($temp_battle_seed['phase'] >= 3){ $challenge_values['challenge_marker'] = 'silver'; }
        if ($temp_battle_seed['phase'] >= 4){ $challenge_values['challenge_marker'] = 'gold'; }
        $challenge_values['challenge_records'] = array();
        $challenge_values['colour_token'] = 'copy';

        // Generate the first ENDLESS MISSION and append it to the list
        $temp_battle_token = $this_prototype_data['this_player_token'].'-endless-mission';
        $temp_battle_sigma = mmrpg_prototype_generate_mission($this_prototype_data, $temp_battle_token, array(
                'battle_name' => 'Special All-Star Challenge Mission',
                'battle_button' => 'Endless Attack Mode',
                'battle_level' => $challenge_battle_level,
                'battle_robot_limit' => 6,
                'battle_description' => 'Select a team of up to six robots and fight your way through as many waves of enemies as you can in this special challenge mission! ',
                'battle_description2' => 'The targets you face will grow stronger as you progress and your team will NOT heal between battles.  Good luck!',
                'battle_counts' => false,
                'battle_zenny' => 1000,
                'battle_complete_redirect_token' => $temp_battle_token,
                'flags' => $challenge_flags,
                'values' => $challenge_values,
                'counters' => $challenge_counters
                ), $target_field, $target_player, $target_robots, true);

        // Return the generated endless mission
        return $temp_battle_sigma;

    }

}
?>