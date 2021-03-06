<?

/*
 * DEV TESTS / ENDLESS MODE
 */

// Define the constant that puts the front-end in compact mode
define('MMRPG_INDEX_COMPACT_MODE', true);

// Define the SEO variables for this page
$this_seo_title = 'Endless Mode Generator V3 | '.$this_seo_title;
$this_seo_description = 'An experimental endless mode generator for the MMRPG.';
$this_seo_robots = 'noindex,nofollow';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Endless Mode Generator V3';
$this_graph_data['description'] = 'An experimental endless mode mission/path generator for the MMRPG.';

?>
<div class="header">
    <div class="header_wrapper">
        <h1 class="title"><span class="brand">Mega Man RPG Endless Mode V3</span></h1>
    </div>
</div>
<h2 class="subheader field_type_<?= !empty($this_field_info['field_type']) ? $this_field_info['field_type'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    Endless Mode Path Generator
</h2>

<div class="subbody">

    <?

    // Collect a list of unlocked RMs, types, etc. from the database
    $mmrpg_types_index = rpg_type::get_index();
    $mmrpg_robots_index = rpg_robot::get_index(false, false, 'master');
    $mmrpg_items_index = rpg_item::get_index(false, false, array('consumable', 'holdable'));

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
        if ($item_token == 'extra-life' || $item_token == 'yashichi'){ continue; } // we don't want none
        $item_kind = !empty($item_info['item_subclass']) ? $item_info['item_subclass'] : 'item';
        if (substr($item_token, -5, 5) === '-core'){ $item_kind = 'cores'; }
        elseif (substr($item_token, -7, 7) === '-pellet'){ $item_kind = 'pellets'; }
        elseif (substr($item_token, -8, 8) === '-capsule'){ $item_kind = 'capsules'; }
        elseif (substr($item_token, -5, 5) === '-tank'){ $item_kind = 'tanks'; }
        elseif ($item_token == 'extra-life' || $item_token == 'yashichi'){ $item_kind = 'extra'; }
        if (!isset($mmrpg_items_index_bykind[$item_kind])){ $mmrpg_items_index_bykind[$item_kind] = array(); }
        $mmrpg_items_index_bykind[$item_kind][] = $item_token;
    }

    // Collect and count the number of core types represented
    $mmrpg_robots_cores = array_keys($mmrpg_robots_index_bycore);
    $mmrpg_robots_cores_count = count($mmrpg_robots_cores);

    // Collect and count the number of item kinds represented
    $mmrpg_items_kinds = array_keys($mmrpg_items_index_bykind);
    $mmrpg_items_kinds_count = count($mmrpg_items_kinds);

    /*
    if (isset($mmrpg_items_index_bykind['consumable'])){
        usort($mmrpg_items_index_bykind['consumable'], function($a, $b){
            if (strstr($a, '-pellet') && !strstr($b, '-pellet')){ return -1; }
            elseif (!strstr($a, '-pellet') && strstr($b, '-pellet')){ return 1; }
            elseif (strstr($a, '-capsule') && !strstr($b, '-capsule')){ return -1; }
            elseif (!strstr($a, '-capsule') && strstr($b, '-capsule')){ return 1; }
            elseif (strstr($a, '-tank') && !strstr($b, '-tank')){ return -1; }
            elseif (!strstr($a, '-tank') && strstr($b, '-tank')){ return 1; }
            elseif (strstr($a, 'defense-') && !strstr($b, 'defense-')){ return -1; }
            elseif (!strstr($a, 'defense-') && strstr($b, 'defense-')){ return 1; }
            elseif (strstr($a, 'attack-') && !strstr($b, 'attack-')){ return -1; }
            elseif (!strstr($a, 'attack-') && strstr($b, 'attack-')){ return 1; }
            elseif (strstr($a, 'speed-') && !strstr($b, 'speed-')){ return -1; }
            elseif (!strstr($a, 'speed-') && strstr($b, 'speed-')){ return 1; }
            elseif (strstr($a, 'energy-') && !strstr($b, 'energy-')){ return -1; }
            elseif (!strstr($a, 'energy-') && strstr($b, 'energy-')){ return 1; }
            elseif (strstr($a, 'weapon-') && !strstr($b, 'weapon-')){ return -1; }
            elseif (!strstr($a, 'weapon-') && strstr($b, 'weapon-')){ return 1; }
            elseif (strstr($a, 'super-') && !strstr($b, 'super-')){ return -1; }
            elseif (!strstr($a, 'super-') && strstr($b, 'super-')){ return 1; }
            else { return 0; }
            });
    }
    */

    // Define a function for generating new missions for the playlist
    $mmrpg_endless_loop_size = $mmrpg_robots_cores_count;
    $mmrpg_generate_endless_mission = function($mission_number)
        use($mmrpg_robots_index, $mmrpg_robots_index_bycore,
            $mmrpg_robots_cores, $mmrpg_robots_cores_count,
            $mmrpg_items_index, $mmrpg_items_index_bykind,
            $mmrpg_items_kinds, $mmrpg_items_kinds_count,
            $mmrpg_endless_loop_size){

        $mission = array();
        //$mission['num'] = $mission_number;

        // Determine phase according to where we are in the core loop
        $mission_phase = $mission_number > $mmrpg_endless_loop_size ? ceil($mission_number / $mmrpg_endless_loop_size) : 1;
        $mission_is_double = $mission_phase >= 4 ? true : false;
        $mission['phase'] = $mission_phase;

        // Decide which core type or types will be represented
        $mission_coretypes = array();
        $mission_coretypes[] = select_from_array_with_rollover($mmrpg_robots_cores, ($mission_number));
        if ($mission_is_double){ $mission_coretypes[] = select_from_array_with_rollover($mmrpg_robots_cores, ($mission_number + 1 + ($mission_phase - 4))); }

        $mission_coretypes = array_unique($mission_coretypes);
        $mission['types'] = $mission_coretypes;

        // Determine the number of robots that should appear
        $num_targets = 3;
        if ($mission_is_double){ $num_targets *= 2; }
        $mission['size'] = $num_targets;

        // Define which robots will appear based on typelist and phasearray_unique($mission['targets'])
        $mission_targets = array();
        $mission_targets_types = array();
        $mission_items_kinds = array();
        for ($target_num = 1; $target_num <= $num_targets; $target_num++){

            // Define the target string empty to start
            $target_string = '';

            $target_type = select_from_array_with_rollover($mission_coretypes, $target_num);

            $target_robot_options = $mmrpg_robots_index_bycore[$target_type];
            if ($mission_phase > 1){
                $shifts_required = ($mission_phase - 1) * $num_targets;
                for ($i = 0; $i < $shifts_required; $i++){ array_push($target_robot_options, array_shift($target_robot_options)); }
            }
            if ($mission_is_double){
                $target_token = select_from_array_with_rollover($target_robot_options, (ceil($target_num / 2) + ($target_num % 2 == 0 ? 1 : $mission_phase)));
            } else {
                $target_token = select_from_array_with_rollover($target_robot_options, $target_num);
            }

            $target_string .= $target_token;

            /*
            $target_item_kind = select_from_array_with_rollover($mmrpg_items_kinds, min($mission_phase, $target_num));
            if (!isset($mission_items_kinds[$target_item_kind])){ $mission_items_kinds[$target_item_kind] = 0; }
            $mission_items_kinds[$target_item_kind] += 1;
            $target_item_offset_num = $mission_items_kinds[$target_item_kind] + ($mission_phase - 1) + (($mission_number - 1) * $num_targets);
            $target_item_token = select_from_array_with_rollover($mmrpg_items_index_bykind[$target_item_kind], $target_item_offset_num);

            $target_string .= '@'.$target_item_token;
            */

            // Append this robot string to the mission
            if (!empty($target_string)){ $mission_targets[] = $target_string; }

        }
        $mission['targets'] = $mission_targets;

        if ($mission['targets'] != array_unique($mission['targets'])){
            //$mission['targets'] = array_values(array_unique($mission['targets']));
            $mission['DUPLICATES'] = true;
        }


        return json_encode($mission);

        };

    // Generate a mission index using the collected robot and hazard data
    $mmrpg_endless_playlist = array();
    for ($mission_number = 1; $mission_number <= 400; $mission_number++){
        $mmrpg_endless_playlist[$mission_number] = $mmrpg_generate_endless_mission($mission_number);
    }
    for ($mission_number = 400; $mission_number <= 1000; $mission_number += 50){
        $mmrpg_endless_playlist[$mission_number] = $mmrpg_generate_endless_mission($mission_number);
    }

    echo('<pre style="font-size: 11px;">$mmrpg_endless_playlist = '.print_r($mmrpg_endless_playlist, true).'</pre>');
    //echo('<pre>$mmrpg_robots_cores = '.print_r($mmrpg_robots_cores, true).'</pre>');
    echo('<pre>$mmrpg_robots_index_bycore = '.print_r($mmrpg_robots_index_bycore, true).'</pre>');
    //echo('<pre>$mmrpg_types_index = '.print_r($mmrpg_types_index, true).'</pre>');
    //echo('<pre>$mmrpg_items_index = '.print_r(array_keys($mmrpg_items_index), true).'</pre>');
    echo('<pre>$mmrpg_items_index_bykind = '.print_r($mmrpg_items_index_bykind, true).'</pre>');
    //echo('<pre>$mmrpg_items_index_bykind = '.print_r(array_keys($mmrpg_items_index_bykind), true).'</pre>');
    //echo('<pre>$mmrpg_items_index = '.print_r($mmrpg_items_index, true).'</pre>');
    //echo('<pre>$mmrpg_robots_index = '.print_r(array_keys($mmrpg_robots_index), true).'</pre>');
    //echo('<pre style="max-height: none;">$mmrpg_robots_index = '.print_r($mmrpg_robots_index, true).'</pre>');

    ?>

</div>