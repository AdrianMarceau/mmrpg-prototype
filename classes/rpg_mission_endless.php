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

}
?>