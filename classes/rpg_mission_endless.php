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
            $mmrpg_fields_index = rpg_field::get_index();
            $mmrpg_items_index = rpg_item::get_index(false, false, array('consumable', 'holdable'));

            // Make sure the types are sorted how we want them (manually move copy to the front)
            uasort($mmrpg_types_index, function($t1, $t2){ return $t1['type_order'] < $t2['type_order'] ? -1 : 1; });
            $mmrpg_types_index = array_merge(array('copy' => null), $mmrpg_types_index);
            $mmrpg_types_order = array_keys($mmrpg_types_index);

            // Sort the robot masters into lists of their core types
            $mmrpg_robots_index_bycore = array();
            foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                if (empty($robot_info['robot_flag_complete'])){ unset($mmrpg_robots_index[$robot_token]); continue; }
                $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
                if (!isset($mmrpg_robots_index_bycore[$robot_core])){ $mmrpg_robots_index_bycore[$robot_core] = array(); }
                $mmrpg_robots_index_bycore[$robot_core][] = $robot_token;
            }
            $mmrpg_robots_index_bycore = array_filter(array_merge(array_flip($mmrpg_types_order), $mmrpg_robots_index_bycore), function($a){ return is_array($a); });

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
            $temp_intro_fields = array_values(rpg_player::get_intro_fields());
            $mmrpg_fields_index_bytype = array();
            foreach ($mmrpg_fields_index AS $field_token => $field_info){
                if ($field_token == 'prototype-complete'){ continue; } // we don't want prototype complete
                if (empty($field_info['field_flag_complete'])){ unset($mmrpg_fields_index[$field_token]); continue; }
                $field_type = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
                if (in_array($field_token, $temp_intro_fields)){ $field_type = 'copy'; }
                elseif (strstr($field_token, 'final-destination')){ $field_type = 'copy'; }
                if (!isset($mmrpg_fields_index_bytype[$field_type])){ $mmrpg_fields_index_bytype[$field_type] = array(); }
                $mmrpg_fields_index_bytype[$field_type][] = $field_token;
            }
            $mmrpg_fields_index_bytype = array_filter(array_merge(array_flip($mmrpg_types_order), $mmrpg_fields_index_bytype), function($a){ return is_array($a); });

            //error_log('<pre>$mmrpg_types_index = '.print_r($mmrpg_types_index, true).'</pre>');
            //error_log('<pre>$mmrpg_robots_index_bycore = '.print_r($mmrpg_robots_index_bycore, true).'</pre>');
            //error_log('<pre>$mmrpg_items_index_bykind = '.print_r($mmrpg_items_index_bykind, true).'</pre>');
            //error_log('<pre>$mmrpg_fields_index_bytype = '.print_r($mmrpg_fields_index_bytype, true).'</pre>');

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
        if ($mission_phase >= 1){ $possible_item_kinds[] = 'pellets'; }
        if ($mission_phase >= 2){ $possible_item_kinds[] = 'capsules'; $possible_item_kinds[] = 'tanks'; }
        if ($mission_phase >= 3){ $possible_item_kinds[] = 'upgrades'; $possible_item_kinds[] = 'modules'; }
        if ($mission_phase >= 4){ $possible_item_kinds[] = 'boosters'; $possible_item_kinds[] = 'circuits'; }
        if ($mission_phase >= 5){ $possible_item_kinds[] = 'cores'; }

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
            if (!$mission_is_double
                && count($target_robot_options) <= $num_targets){
                $shifts_required = $mission_phase + ($num_targets - 1);
                for ($i = 0; $i < $shifts_required; $i++){
                    array_push($target_robot_options, array_shift($target_robot_options));
                }
            }
            $target_token = select_from_array_with_rollover($target_robot_options, $target_num);
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
        if ($mmrpg_fields_index === false){ $mmrpg_fields_index = rpg_field::get_index(true); }
        if ($mmrpg_robots_index === false){ $mmrpg_robots_index = rpg_robot::get_index(true); }

        // Collect the endless mission seed based on the mission number
        $temp_battle_seed = self::generate_endless_mission_seed($mission_number);

        // Precollect data about the requested fields
        $default_field = rpg_player::get_intro_field($this_prototype_data['this_player_token']);
        $temp_option_field = $mmrpg_fields_index[(!empty($temp_battle_seed['field']) ? $temp_battle_seed['field'] : $default_field)];
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

        // Calculate the relative multiplier for starforce * stats
        $rel_multiplier = $mission_number < 1024 ? ($mission_number / 1024) : 1;

        // Define the target player with seed data
        $rel_star_force = $max_star_force;
        foreach ($rel_star_force AS $type => $val){ $rel_star_force[$type] = round($val * $rel_multiplier); }
        $target_player = array(
            'user_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
            'player_id' => rpg_game::unique_player_id(MMRPG_SETTINGS_TARGET_PLAYERID, 0),
            'player_token' => 'player',
            'player_starforce' => $rel_star_force
            );

        // Generate the list of target robots given the seed data
        $robot_tokens = array();
        $target_robots = array();
        //$statmods = min(5, ($temp_battle_seed['phase'] - 1));
        //$statmods = $mission_number > 1 ? mt_rand(0, min(5, $mission_number)) : 0;
        //$statmodmax = $mission_number > 1 ? min(5, $mission_number) : 0;
        $statmodmax = $temp_battle_seed['phase'] > 1 ? min(5, ($temp_battle_seed['phase'] - 1)) : 0;
        $statrewards = floor((9999) * $rel_multiplier);
        foreach ($temp_battle_seed['targets'] AS $key => $target){
            list($robot, $item) = explode('@', $target);
            $image = $robot;
            $info = $mmrpg_robots_index[$robot];
            //error_log('$info = '.print_r($info, true));
            if (!isset($robot_tokens[$robot])){ $robot_tokens[$robot] = 0; }
            $robot_tokens[$robot]++;
            if ($robot_tokens[$robot] > 1
                && $info['robot_core'] !== 'copy'
                && !empty($info['robot_image_alts'])){
                $alts = $info['robot_image_alts'];
                $alt_num = $robot_tokens[$robot] - 1;
                $alt_key = $alt_num - 1;
                if (!isset($alts[$alt_key])){ $alt_key = count($alts) - 1; }
                $alt_info = $alts[$alt_key];
                $image .= '_'.$alt_info['token'];
            }
            $target_robots[] = array(
                'robot_token' => $robot,
                'robot_image' => $image,
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
        if ($temp_battle_seed['types'][0] == 'copy'){

            // Define the music for the relevant stages based on phase
            $atoken = 'sega-remix';
            $moptions = array('special-stage-3-mm10', 'special-stage-2-mm10', 'special-stage-1-mm10', 'wily-fortress-1-mm8', 'wily-fortress-2-mm8', 'wily-fortress-3-mm8', 'wily-fortress-4-mm8');
            $mtoken = select_from_array_with_rollover($moptions, $temp_battle_seed['phase']);
            $music_path = $atoken.'/'.$mtoken.'/';
            if (rpg_game::sound_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path)){
                $target_field['field_music'] = $music_path;
                $music_is_customized = true;
            }

        } elseif ($temp_battle_seed['types'][0] == 'none'){

            // Define the music for the relevant stages based on phase
            $atoken = 'sega-remix';
            $moptions = array('wily-fortress-1-mm8', 'wily-fortress-2-mm8', 'wily-fortress-3-mm8', 'wily-fortress-4-mm8', 'special-stage-3-mm10', 'special-stage-2-mm10', 'special-stage-1-mm10');
            $mtoken = select_from_array_with_rollover($moptions, $temp_battle_seed['phase']);
            $music_path = $atoken.'/'.$mtoken.'/';
            if (rpg_game::sound_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path)){
                $target_field['field_music'] = $music_path;
                $music_is_customized = true;
            }

        } elseif (!empty($target_robots)){

            // Attempt to collect custom music for the first robot
            $custom_music_path = rpg_robot::get_custom_music_path($target_robots[0]['robot_token']);
            if (!empty($custom_music_path)){
                $target_field['field_music'] = $custom_music_path;
                $music_is_customized = true;
            }

        }

        // If custom music was not assigned, we should at least check the default exists
        if (!$music_is_customized){

            $boss_theme_num = 10 - ($mission_number > 10 ? ($mission_number % 10) : $mission_number);
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
                'battle_zenny' => 100,
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