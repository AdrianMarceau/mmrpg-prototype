<?

/*
 * DEV TESTS / MAP GENERATOR
 */

// Define the constant that puts the front-end in compact mode
define('MMRPG_INDEX_COMPACT_MODE', true);

// Define the SEO variables for this page
$this_seo_title = 'Map Generator | Dev Tests | '.$this_seo_title;
$this_seo_description = 'An experimental map generator for the MMRPG.';
$this_seo_robots = 'noindex,nofollow';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Map Generator';
$this_graph_data['description'] = 'An experimental map generator for the MMRPG.';

// Empty cached session playlist if reset requested
if (!empty($_REQUEST['reset'])){
    unset($_SESSION['mmrpg_conqest_playlist']);
    header('Location: '.MMRPG_CONFIG_ROOTURL.'dev/map-test/');
    exit();
}

// Define array column function if not exists
if (!function_exists('array_column')){
    function array_column($array, $column){
        return array_map(function($array)use($column){
            return isset($array[$column]) ? $array[$column] : false;
            }, $array);
    }
}

// If this is NOT a debug request, automatically set a key value
if (!isset($_REQUEST['debug'])
    && !isset($_REQUEST['key'])){
    $_REQUEST['key'] = 0;
    $_SESSION['mmrpg_conqest_playlist'] = array();
    $_SESSION['mmrpg_conqest_playlist_progress'] = array();
}

// Define a playlist of map configurations to go through
$this_map_playlist = array();
if (!empty($_SESSION['mmrpg_conqest_playlist'])){

    // Collect existing playlist from session
    $this_map_playlist = $_SESSION['mmrpg_conqest_playlist'];

} else {

    // Generate the map playlist
    function generate_conquest_playlist(){
        global $db;

        $this_map_playlist = array();

        // Tutorial Mission

        $this_map_playlist[] = array('scale' => 1, 'field' => 'intro-field', 'boss' => 'trill');

        // Doctor Missions

        $this_map_playlist[] = array('scale' => 2, 'field' => 'light-laboratory', 'boss' => 'enker');
        $this_map_playlist[] = array('scale' => 2, 'field' => 'wily-castle', 'boss' => 'punk');
        $this_map_playlist[] = array('scale' => 2, 'field' => 'cossack-citadel', 'boss' => 'ballade');

        // Elemental Missions

        $elemental_fields = $db->get_array_list("SELECT field_token, field_type FROM mmrpg_index_fields WHERE field_class = 'master' AND field_type <> '' AND field_flag_hidden = 0 AND field_flag_published = 1 AND field_flag_complete = 1 ORDER BY field_order ASC;");
        $elemental_fields_bytype = array();
        foreach ($elemental_fields AS $field_key => $field_info){
            $field_token = $field_info['field_token'];
            $field_type = $field_info['field_type'];
            if (!isset($elemental_fields_bytype[$field_type])){ $elemental_fields_bytype[$field_type] = array(); }
            $elemental_fields_bytype[$field_type][] = $field_token;
        }

        $elemental_types = $db->get_array_list("SELECT type_token, type_name FROM mmrpg_index_types WHERE type_class = 'normal' AND type_flag_hidden = 0 AND type_flag_published = 1 ORDER BY type_order ASC;");
        $elemental_types_excluded = array('none', 'copy', 'laser', 'shield');

        foreach ($elemental_types AS $type_key => $type_info){
            $type_token = $type_info['type_token'];
            if (in_array($type_token, $elemental_types_excluded)){ continue; }
            elseif (empty($elemental_fields_bytype[$type_token])){ continue; }
            $scale_value = 3;
            if ($type_key >= 8){ $scale_value++; }
            if ($type_key >= 16){ $scale_value++; }
            $field_token = $elemental_fields_bytype[$type_token][mt_rand(0, (count($elemental_fields_bytype[$type_token]) - 1))];
            $this_map_playlist[] = array('scale' => $scale_value, 'field' => $field_token, 'boss' => 'doc-robot');
        }

        // Final Missions

        $temp_robot_tokens = array('mega-man-ds', 'bass-ds', 'proto-man-ds');
        $this_map_playlist[] = array('scale' => 6, 'field' => 'final-destination', 'boss' => $temp_robot_tokens[mt_rand(0, (count($temp_robot_tokens) - 1))]);

        $temp_robot_tokens = array('dark-man', 'dark-man-2', 'dark-man-3', 'dark-man-4');
        $this_map_playlist[] = array('scale' => 7, 'field' => 'final-destination-2', 'boss' => $temp_robot_tokens[mt_rand(0, (count($temp_robot_tokens) - 1))]);

        $this_map_playlist[] = array('scale' => 8, 'field' => 'final-destination-3', 'boss' => 'slur');

        //$this_map_playlist[] = array('scale' => 8, 'field' => 'prototype-complete', 'boss' => 'quint', 'bonus' => true, 'difficulty' => 1);

        $this_map_playlist[] = array('results' => true);

        //echo('<pre>$elemental_fields = '.print_r($elemental_fields, true).'</pre>');
        //echo('<pre>$elemental_fields_bytype = '.print_r($elemental_fields_bytype, true).'</pre>');

        //echo('<pre>$elemental_types = '.print_r($elemental_types, true).'</pre>');
        //echo('<pre>$elemental_types_excluded = '.print_r($elemental_types_excluded, true).'</pre>');

        //echo('<pre>$this_map_playlist = '.print_r($this_map_playlist, true).'</pre>');
        //exit();

        // Return completed playlist
        return $this_map_playlist;

    }

    // Generate a new playlist geing the conquest funtion
    $this_map_playlist = generate_conquest_playlist();

    // Update the session with the newly generated playlist
    if (!empty($this_map_playlist)){ $_SESSION['mmrpg_conqest_playlist'] =  $this_map_playlist; }

}

// Count the entries in the playlist they are NOT results
$this_map_playlist_count = 0;
foreach ($this_map_playlist AS $entry){
    if (!isset($entry['results'])){ $this_map_playlist_count += 1; }
}

// If a completion request was specifically posted, save it as progress
if (!isset($_SESSION['mmrpg_conqest_playlist_progress'])){
    $_SESSION['mmrpg_conqest_playlist_progress'] = array();
}
if (!empty($_POST['mission_complete'])
    && is_array($_POST['mission_complete'])
    && isset($_POST['mission_complete']['mission_key'])){
    $mission_complete = $_POST['mission_complete'];
    $mission_key = $mission_complete['mission_key'];
    $_SESSION['mmrpg_conqest_playlist_progress'][$mission_key] = $mission_complete;
}
/*
if (isset($_POST['mission_complete_key'])
    && is_numeric($_POST['mission_complete_key'])
    && !empty($_POST['mission_complete_score'])
    && is_numeric($_POST['mission_complete_score'])
    && !empty($_POST['mission_complete_possible'])
    && is_numeric($_POST['mission_complete_possible'])){
    $_SESSION['mmrpg_conqest_playlist_progress'][] = array(
        'key' => $_POST['mission_complete_key'],
        'score' => $_POST['mission_complete_score'],
        'possible' => $_POST['mission_complete_possible']
        );
}
*/

// Collect the progress array from the session
$this_map_progress = $_SESSION['mmrpg_conqest_playlist_progress'];

// If a specific playlist key was requested, load it now
$current_playlist_key = false;
$current_playlist_info = false;
$current_playlist_key_semifinal = false;
$current_playlist_key_final = false;
$current_playlist_key_results = false;
if (isset($_REQUEST['key'])
    && is_numeric($_REQUEST['key'])
    && isset($this_map_playlist[$_REQUEST['key']])){
    $current_playlist_key = $_REQUEST['key'];
    foreach ($this_map_playlist[$current_playlist_key] AS $name => $value){
        $_REQUEST[$name] = $value;
    }
    $current_playlist_info = $this_map_playlist[$current_playlist_key];
    if (!empty($current_playlist_info['results'])){
        $current_playlist_key_results = true;
    }
    if ($current_playlist_key == ($this_map_playlist_count - 1)){
        $current_playlist_key_final = true;
    } elseif ($current_playlist_key == ($this_map_playlist_count - 2)){
        $current_playlist_key_semifinal = true;
    }
}


/* -- MAP GENERATION -- */

ob_start();

if (!$current_playlist_key_results){

    // Collect a list of battle fields from the database
    $index_field_tokens = $db->get_array_list("SELECT field_token FROM mmrpg_index_fields WHERE field_flag_complete = 1 ORDER BY field_order ASC;");
    $index_field_tokens = !empty($index_field_tokens) ? array_column($index_field_tokens, 'field_token') : array('intro-field');

    // Collect the field token from the request if set
    $request_field_token = false;
    if (!empty($_REQUEST['field'])
        && is_string($_REQUEST['field'])
        && in_array($_REQUEST['field'], $index_field_tokens)){
        $request_field_token = $_REQUEST['field'];
    }

    // Collect a random field for this test to use as a base
    $this_field_token = !empty($request_field_token) ? $request_field_token : $index_field_tokens[mt_rand(0, (count($index_field_tokens) - 1))];
    $this_field_info = rpg_field::get_index_info($this_field_token);

    //echo('<hr />');
    //echo('<pre>$index_field_tokens = '.print_r($index_field_tokens, true).'</pre>');
    //echo('<pre>$this_field_token = '.print_r($this_field_token, true).'</pre>');
    //echo('<pre>$this_field_info = '.print_r($this_field_info, true).'</pre>');

    // Collect a list of player characters from the database
    $index_player_tokens = $db->get_array_list("SELECT player_token FROM mmrpg_index_players WHERE player_flag_complete = 1 ORDER BY player_order ASC;");
    $index_player_tokens = !empty($index_player_tokens) ? array_column($index_player_tokens, 'player_token') : array('dr-light');

    // Collect the player token from the request if set
    $request_player_token = false;
    if (!empty($_REQUEST['player'])
        && is_string($_REQUEST['player'])
        && in_array($_REQUEST['player'], $index_player_tokens)){
        $request_player_token = $_REQUEST['player'];
    }

    // Collect a random player for this test to use as a base
    $this_player_token = !empty($request_player_token) ? $request_player_token : $index_player_tokens[mt_rand(0, (count($index_player_tokens) - 1))];
    $this_player_info = rpg_player::get_index_info($this_player_token);

    //echo('<hr />');
    //echo('<pre>$index_player_tokens = '.print_r($index_player_tokens, true).'</pre>');
    //echo('<pre>$this_player_token = '.print_r($this_player_token, true).'</pre>');
    //echo('<pre>$this_player_info = '.print_r($this_player_info, true).'</pre>');

    // Collect a list of boss targets based on field type
    $index_boss_tokens = $db->get_array_list("SELECT robot_token FROM mmrpg_index_robots WHERE robot_flag_complete = 1 AND robot_class = 'boss' ORDER BY robot_order ASC;");
    $index_boss_tokens = !empty($index_boss_tokens) ? array_column($index_boss_tokens, 'robot_token') : array('trill');

    // Collect a list of boss targets based on field type
    $index_field_boss_tokens = $db->get_array_list("SELECT robot_token FROM mmrpg_index_robots WHERE robot_flag_complete = 1 AND robot_core = '{$this_field_info['field_type']}' AND robot_class = 'boss' ORDER BY robot_order ASC;");
    $index_field_boss_tokens = !empty($index_field_boss_tokens) ? array_column($index_field_boss_tokens, 'robot_token') : array('trill');

    // Collect the boss token from the request if set
    $request_boss_token = false;
    if (!empty($_REQUEST['boss'])
        && is_string($_REQUEST['boss'])
        && in_array($_REQUEST['boss'], $index_boss_tokens)){
        $request_boss_token = $_REQUEST['boss'];
    }

    // Collect a random boss for this test to use as a base
    $this_boss_token = !empty($request_boss_token) ? $request_boss_token : $index_field_boss_tokens[mt_rand(0, (count($index_field_boss_tokens) - 1))];
    $this_boss_info = rpg_robot::get_index_info($this_boss_token);

    //echo('<hr />');
    //echo('<pre>$index_field_boss_tokens = '.print_r($index_field_boss_tokens, true).'</pre>');
    //echo('<pre>$this_boss_token = '.print_r($this_boss_token, true).'</pre>');
    //echo('<pre>$this_boss_info = '.print_r($this_boss_info, true).'</pre>');

    // Collect a list of robots targets based on field type
    $index_robot_filter = '';
    if ($request_field_token != 'prototype-complete'){ $index_robot_filter = "AND robot_core = '{$this_field_info['field_type']}' "; }
    $index_robot_tokens = $db->get_array_list("SELECT robot_token FROM mmrpg_index_robots WHERE robot_flag_complete = 1 {$index_robot_filter} AND robot_class = 'master' ORDER BY robot_order ASC;");
    $index_robot_tokens = !empty($index_robot_tokens) ? array_column($index_robot_tokens, 'robot_token') : array('mega-man');
    shuffle($index_robot_tokens);
    if (!empty($this_field_info['field_master'])){
        $token = $this_field_info['field_master'];
        array_unshift($index_robot_tokens, $token);
        $index_robot_tokens = array_unique($index_robot_tokens);
    }
    $index_robot_info = rpg_robot::get_index_custom($index_robot_tokens);

    //echo('<hr />');
    //echo('<pre>$index_robot_tokens = '.print_r($index_robot_tokens, true).'</pre>');
    //echo('<pre>$index_robot_info = '.print_r($index_robot_info, true).'</pre>');

    // Collect a list of mecha targets based on field type
    $index_mecha_filter = '';
    if ($request_field_token != 'prototype-complete'){ $index_mecha_filter = "AND robot_core = '{$this_field_info['field_type']}' "; }
    $index_mecha_tokens = $db->get_array_list("SELECT robot_token FROM mmrpg_index_robots WHERE robot_flag_complete = 1 {$index_mecha_filter} AND robot_class = 'mecha' ORDER BY robot_order ASC;");
    $index_mecha_tokens = !empty($index_mecha_tokens) ? array_column($index_mecha_tokens, 'robot_token') : array('met');
    shuffle($index_mecha_tokens);
    if (!empty($this_field_info['field_mechas'])){
        foreach ($this_field_info['field_mechas'] AS $token){ array_unshift($index_mecha_tokens, $token); }
        $index_mecha_tokens = array_unique($index_mecha_tokens);
    }
    $index_mecha_info = rpg_robot::get_index_custom($index_mecha_tokens);

    //echo('<hr />');
    //echo('<pre>$index_mecha_tokens = '.print_r($index_mecha_tokens, true).'</pre>');
    //echo('<pre>$index_mecha_info = '.print_r($index_mecha_info, true).'</pre>');

    // Define the base col/row ratio for map size
    $base_num_cols = 4;
    $base_num_rows = 1;
    $min_map_scale = 1;
    $max_map_scale = 8;

    //echo('<hr />');
    //echo('<pre>$base_num_cols = '.print_r($base_num_cols, true).'</pre>');
    //echo('<pre>$base_num_rows = '.print_r($base_num_rows, true).'</pre>');
    //echo('<pre>$min_map_scale = '.print_r($min_map_scale, true).'</pre>');
    //echo('<pre>$max_map_scale = '.print_r($max_map_scale, true).'</pre>');

    // Collect the map scale from the request if set
    $request_map_scale = false;
    if (!empty($_REQUEST['scale'])
        && is_numeric($_REQUEST['scale'])
        && $_REQUEST['scale'] >= $min_map_scale
        && $_REQUEST['scale'] <= $max_map_scale){
        $request_map_scale = $_REQUEST['scale'];
    }

    // Generate the scale value and rows/cols for this map (1-8)
    $this_map_scale = !empty($request_map_scale) ? $request_map_scale : mt_rand($min_map_scale, $max_map_scale);
    $this_map_cols = $base_num_cols * $this_map_scale;
    $this_map_rows = $base_num_rows * $this_map_scale;

    //echo('<hr />');
    //echo('<pre>$this_map_scale = '.print_r($this_map_scale, true).'</pre>');
    //echo('<pre>$this_map_cols = '.print_r($this_map_cols, true).'</pre>');
    //echo('<pre>$this_map_rows = '.print_r($this_map_rows, true).'</pre>');

    // Create an array to hold all the event details for this map
    $this_map_events = array();

    // Generate a list of possible cells to keep track when generating
    $this_map_cells = array();
    $this_map_cells_total_count = 0;
    $this_map_cells_used_count = 0;
    $this_map_cells_remaning_count = 0;
    for ($row = 1; $row <= $this_map_rows; $row++){
        for ($col = 1; $col <= $this_map_cols; $col++){
            $this_map_cells[] = $row.'-'.$col;
            $this_map_cells_total_count++;
        }
    }

    if ($this_map_scale < 4){
        //echo('<hr />');
        //echo('<pre>$this_map_cells = '.print_r($this_map_cells, true).'</pre>');
        //echo('<pre>$this_map_cells_total_count = '.print_r($this_map_cells_total_count, true).'</pre>');
        //echo('<pre>$this_map_cells_used_count = '.print_r($this_map_cells_used_count, true).'</pre>');
        //echo('<pre>$this_map_cells_remaning_count = '.print_r($this_map_cells_remaning_count, true).'</pre>');
    }

    // Define a function to re-count used vs remaining map cells
    function temp_count_map_cells(){
        global $this_map_events, $this_map_cells_total_count, $this_map_cells_used_count, $this_map_cells_remaning_count;
        $this_map_cells_used_count = count($this_map_events);
        $this_map_cells_remaning_count = $this_map_cells_total_count - $this_map_cells_used_count;
    }

    // Define a function for generating a list of remaining cell positions
    function temp_remaining_cell_positions(){
        global $this_map_cells, $this_map_events;
        $event_keys = array_keys($this_map_events);
        $remaining_cells = array_diff($this_map_cells, $event_keys);
        $remaining_cells = array_values($remaining_cells);
        return $remaining_cells;
    }

    // Define a range diff for map origin/destination rand
    $quad_rang_diff = ($this_map_cols / 4) - 1;

    //echo('<hr />');
    //echo('<pre>$quad_rang_diff = '.print_r($quad_rang_diff, true).'</pre>');

    // Generate an origin point for this map on the far left
    $this_origin_col = 1;
    $this_orgin_row = $this_map_rows;
    $this_map_origin = $this_orgin_row.'-'.$this_origin_col;

    //echo('<hr />');
    //echo('<pre>$this_origin_col = '.print_r($this_origin_col, true).'</pre>');
    //echo('<pre>$this_orgin_row = '.print_r($this_orgin_row, true).'</pre>');
    //echo('<pre>$this_map_origin = '.print_r($this_map_origin, true).'</pre>');

    // Generate a destination point for this map on the far right
    $this_destination_col = $this_map_cols;
    $this_destination_row = 1;
    $this_map_destination = $this_destination_row.'-'.$this_destination_col;

    //echo('<hr />');
    //echo('<pre>$this_destination_col = '.print_r($this_destination_col, true).'</pre>');
    //echo('<pre>$this_destination_row = '.print_r($this_destination_row, true).'</pre>');
    //echo('<pre>$this_map_destination = '.print_r($this_map_destination, true).'</pre>');

    // Add the origin and destination points to the map first
    $this_map_events[$this_map_origin] = array('kind' => 'origin', 'player' => $this_player_token, 'size' => $this_player_info['player_image_size']);
    $this_map_events[$this_map_destination] = array('kind' => 'destination', 'boss' => $this_boss_token, 'size' => $this_boss_info['robot_image_size']);
    temp_count_map_cells();

    //echo('<hr />');
    //echo('<pre>$this_map_events['.$this_map_origin.'] = '.print_r($this_map_events[$this_map_origin], true).'</pre>');
    //echo('<pre>$this_map_events['.$this_map_destination.'] = '.print_r($this_map_events[$this_map_destination], true).'</pre>');

    //echo('<hr />');
    //echo('<pre>$this_map_cells_total_count = '.print_r($this_map_cells_total_count, true).'</pre>');
    //echo('<pre>$this_map_cells_used_count = '.print_r($this_map_cells_used_count, true).'</pre>');
    //echo('<pre>$this_map_cells_remaning_count = '.print_r($this_map_cells_remaning_count, true).'</pre>');

    // If there are cells remaining, populate with collected robots
    if ($this_map_cells_remaning_count > 0){

        // Define the limit for how many robots we can add events for
        $this_map_robot_limit = floor($this_map_scale * 1.50);

        //echo('<hr />');
        //echo('<pre>$this_map_robot_limit = floor('.$this_map_scale.' * 1.5) = '.print_r($this_map_robot_limit, true).'</pre>');

        // Loop through and generate robots events for cells
        if (!empty($this_map_robot_limit)){
            $temp_robot_tokens = array();
            for ($i = 0; $i < $this_map_robot_limit; $i++){

                // Collect available robot tokens and shuffle if necessary
                if (empty($temp_robot_tokens)){ $temp_robot_tokens = $index_robot_tokens; }

                //echo('<hr />');
                //echo('<pre>$temp_robot_tokens = '.print_r($temp_robot_tokens, true).'</pre>');

                // Pop an element off the robot array and use it
                $temp_robot_token = array_shift($temp_robot_tokens);
                $temp_robot_info = $index_robot_info[$temp_robot_token];

                // Create an event somewhere on the map with this robot
                $remaining = temp_remaining_cell_positions();
                if ($this_map_scale == 1){
                    $temp_max = count($remaining) - 1;
                    $temp_min = round(count($remaining) / 2);
                    $temp_pos = $remaining[mt_rand($temp_min, $temp_max)];
                } else {
                    $temp_pos = $remaining[mt_rand(0, count($remaining) - 1)];
                }
                $this_map_events[$temp_pos] = array('kind' => 'robot', 'token' => $temp_robot_token, 'size' => $temp_robot_info['robot_image_size']);

                //echo('<hr />');
                //echo('<pre>$temp_robot_token = '.print_r($temp_robot_token, true).'</pre>');
                //echo('<pre>$temp_robot_info = '.print_r($temp_robot_info, true).'</pre>');
                //echo('<pre>$remaining = '.implode(', ', $remaining).'</pre>');
                //echo('<pre>$temp_pos = '.print_r($temp_pos, true).'</pre>');
                //echo('<pre>$this_map_events['.$temp_pos.'] = '.print_r($this_map_events[$temp_pos], true).'</pre>');

            }
        }

        // Recount the map cells after recent changes
        temp_count_map_cells();

        //echo('<hr />');
        //echo('<pre>$this_map_cells_total_count = '.print_r($this_map_cells_total_count, true).'</pre>');
        //echo('<pre>$this_map_cells_used_count = '.print_r($this_map_cells_used_count, true).'</pre>');
        //echo('<pre>$this_map_cells_remaning_count = '.print_r($this_map_cells_remaning_count, true).'</pre>');

        // Define the limit for how many mechas we can add events for
        $this_map_mecha_limit = floor($this_map_scale * $this_map_scale * 1.10);

        //echo('<hr />');
        //echo('<pre>$this_map_mecha_limit = '.print_r($this_map_mecha_limit, true).'</pre>');

        // Loop through and generate mecgas events for cells
        if (!empty($this_map_mecha_limit)){
            $temp_mecha_tokens = array();
            for ($i = 0; $i < $this_map_mecha_limit; $i++){

                // Collect available mecha tokens and shuffle if necessary
                if (empty($temp_mecha_tokens)){ $temp_mecha_tokens = $index_mecha_tokens; }
                //echo('<hr />');
                //echo('<pre>$temp_mecha_tokens = '.print_r($temp_mecha_tokens, true).'</pre>');

                // Pop an element off the mecha array and use it
                $temp_mecha_token = array_shift($temp_mecha_tokens);
                $temp_mecha_info = $index_mecha_info[$temp_mecha_token];

                // Create an event somewhere on the map with this mecha
                $remaining = temp_remaining_cell_positions();
                $temp_pos = $remaining[mt_rand(0, (count($remaining) - 1))];
                $this_map_events[$temp_pos] = array('kind' => 'mecha', 'token' => $temp_mecha_token, 'size' => $temp_mecha_info['robot_image_size']);

                //echo('<hr />');
                //echo('<pre>$temp_mecha_token = '.print_r($temp_mecha_token, true).'</pre>');
                //echo('<pre>$temp_mecha_info = '.print_r($temp_mecha_info, true).'</pre>');
                //echo('<pre>$remaining = '.implode(', ', $remaining).'</pre>');
                //echo('<pre>$temp_pos = '.print_r($temp_pos, true).'</pre>');
                //echo('<pre>$this_map_events['.$temp_pos.'] = '.print_r($this_map_events[$temp_pos], true).'</pre>');

            }
        }

        // Recount the map cells after recent changes
        temp_count_map_cells();

        //echo('<hr />');
        //echo('<pre>$this_map_cells_total_count = '.print_r($this_map_cells_total_count, true).'</pre>');
        //echo('<pre>$this_map_cells_used_count = '.print_r($this_map_cells_used_count, true).'</pre>');
        //echo('<pre>$this_map_cells_remaning_count = '.print_r($this_map_cells_remaning_count, true).'</pre>');

    }

    /*
    //echo('<hr />');
    //echo('<pre>$a = '.print_r($a, true).'</pre>');
    //echo('<pre>$a = '.print_r($a, true).'</pre>');
    //echo('<pre>$a = '.print_r($a, true).'</pre>');
    //echo('<pre>$a = '.print_r($a, true).'</pre>');
    //echo('<pre>$a = '.print_r($a, true).'</pre>');
    */


}

$debug_variable_text = ob_get_clean();

?>

<div class="header">
    <div class="header_wrapper">
        <h1 class="title"><span class="brand">Mega Man RPG Conquest</span></h1>
    </div>
</div>

<h2 class="subheader field_type_<?= !empty($this_field_info['field_type']) ? $this_field_info['field_type'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <? if ($current_playlist_key_results){ ?>
        Conquest Complete!
        <span style="float: right;">
            Final Score
        </span>
    <? } elseif ($current_playlist_key !== false){ ?>
        <?= $this_field_info['field_name'] ?>
        <span style="float: right;">
            <?= 'Mission '.($current_playlist_key + 1).' of '.(count($this_map_playlist) - 1) ?>
        </span>
    <? } else { ?>
        <?= $this_field_info['field_name'] ?>
        <span style="float: right;">
            Debug Mode
        </span>
    <? } ?>
</h2>

<div class="subbody">

    <div class="test_area">

        <? if (!$current_playlist_key_results){ ?>

            <div class="field_counters">
                <div class="results">
                    <div class="result success type type_nature">Mission Complete!</div>
                    <div class="result failure type type_flame">Mission Failure&hellip;</div>
                </div>
                <div class="counter moves">
                    <span class="value remaining">0</span>
                    <strong class="label">Moves</strong>
                </div>
                <div class="counter points">
                    <span class="value current">0</span>
                    <span class="slash">/</span>
                    <span class="value total">0</span>
                    <strong class="label">Points</strong>
                </div>
                <div class="counter complete">
                    <span class="value percent">0%</span>
                    <strong class="label">Complete</strong>
                </div>
            </div>

            <div class="field_map" data-scale="<?= $this_map_scale ?>" data-rows="<?= $this_map_rows ?>" data-cols="<?= $this_map_cols ?>">
                <div class="wrapper">
                    <div class="field_background" style="background-image: url(images/fields/<?= $this_field_token ?>/<?= !$flag_wap ? 'battle-field_background_base.gif' : 'battle-field_preview.png' ?>);"></div>
                    <div class="field_overlay"></div>
                    <div class="event_grid">
                        <?

                        /*
                        $complete = array();
                        $complete = array(
                            $this_map_origin,
                            ($this_orgin_row ).'-'.($this_origin_col + 1),
                            ($this_orgin_row ).'-'.($this_origin_col + 2)
                            );

                        $current_position = $complete[count($complete) - 1];
                        */

                        $complete = array();
                        $current_position = $this_map_origin;

                        // Loop through rows, and then through columns
                        for ($row = 1; $row <= $this_map_rows; $row++){
                            for ($col = 1; $col <= $this_map_cols; $col++){
                                $pos = $row.'-'.$col;

                                $is_complete = false;

                                $class = 'cell';
                                $inside = '';

                                // Add a special class if this is the origin or destination
                                if ($pos == $this_map_origin){
                                    $class .= ' origin';
                                } elseif ($pos == $this_map_destination){
                                    $class .= ' destination';
                                }

                                if (in_array($pos, $complete)){
                                    $is_complete = true;
                                }

                                // Add events markers and sprites based on event kind
                                if (isset($this_map_events[$pos])){

                                    $event = $this_map_events[$pos];
                                    $direction = isset($event['direction']) ? $event['direction'] : 'left';
                                    $size = isset($event['size']) ? $event['size'] : 40;
                                    $xsize = $size.'x'.$size;

                                    // Add a special class if this is the origin or destination
                                    $eclass = '';
                                    if ($pos == $this_map_origin){
                                        $eclass .= ' origin player';
                                        $eclass .= ' type type_nature';
                                    } elseif ($pos == $this_map_destination){
                                        $eclass .= ' destination boss';
                                        $eclass .= ' type type_flame';
                                    } elseif ($event['kind'] == 'robot'){
                                        $eclass .= ' robot';
                                        $eclass .= ' type type_explode';
                                    } elseif ($event['kind'] == 'mecha'){
                                        $eclass .= ' mecha';
                                        $eclass .= ' type type_electric';
                                    }

                                    $inside .= '<span class="event '.$eclass.'"></span>';

                                    if ($event['kind'] == 'origin'){

                                        $is_complete = true;

                                    } elseif ($event['kind'] == 'destination'){

                                        //$inside .= '<span class="sprite sprite_'.$xsize.'" style="background-image: url(images/robots/'.$event['boss'].'/sprite_'.$direction.'_'.$xsize.'.png);"></span>';
                                        $inside .= '<span class="sprite sprite_left sprite_'.$xsize.' sprite_'.$event['boss'].' sprite_shadow"></span>';

                                    } elseif (in_array($event['kind'], array('mecha', 'robot', 'boss'))){

                                        //$inside .= '<span class="sprite sprite_'.$xsize.'" style="background-image: url(images/robots/'.$event['token'].'/sprite_'.$direction.'_'.$xsize.'.png);"></span>';
                                        $inside .= '<span class="sprite sprite_left sprite_'.$xsize.' sprite_'.$event['token'].' sprite_shadow"></span>';

                                    }
                                }

                                // Add the player sprite to their current position on the map
                                if ($pos == $current_position){

                                    $player = $this_map_events[$this_map_origin]['player'];
                                    $inside .= '<span class="sprite sprite_'.$xsize.'" style="background-image: url(images/players/'.$player.'/sprite_right_'.$xsize.'.png);"></span>';
                                    //$inside .= '<span class="sprite sprite_right sprite_'.$xsize.' sprite_'.$player.'"></span>';
                                }

                                // If this cell has already been completed add the class
                                if ($is_complete){
                                    $class .= ' complete';
                                }

                                // Print out the generated markup for this cell and its contents
                                echo '<div '.
                                    'class="'.$class.'" '.
                                    'data-col="'.$col.'" '.
                                    'data-row="'.$row.'" '.
                                    //'title="'.$pos.'" '.
                                    '>'.$inside.
                                    '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="field_options <?= $current_playlist_key !== false ? 'playlist_active' : '' ?>">
                <form class="options_form" method="get" action="dev/map-test/">

                    <? if ($current_playlist_key !== false && $current_playlist_key_results != true){ ?>

                        <?
                        // Print hidden inputs for the playlist variables
                        echo('<input class="hidden" type="hidden" name="key" value="'.$current_playlist_key.'" />'.PHP_EOL);
                        echo('<input class="hidden" type="hidden" name="maxkey" value="'.count($this_map_playlist).'" />'.PHP_EOL);
                        foreach ($this_map_playlist[$current_playlist_key] AS $name => $value){
                            echo('<input class="hidden" type="hidden" name="'.$name.'" value="'.$value.'" />'.PHP_EOL);
                        }
                        ?>

                        <div class="buttons">

                            <a class="button retry type type_water"><span>Retry Mission</span></a>
                            <a class="button regenerate type type_time"><span>Regenerate Field</span></a>
                            <?
                            if ($current_playlist_key_final){ $next_button_text = 'Continue to Results'; }
                            elseif ($current_playlist_key_semifinal){ $next_button_text = 'Final Mission'; }
                            else { $next_button_text = 'Next Mission'; }
                            ?>
                            <a class="button continue type type_nature disabled"><span><?= $next_button_text ?> &raquo;</span></a>
                            <a class="button reset type type_flame" href="dev/map-test/"><span>Reset Game</span></a>

                        </div>

                    <? } else { ?>

                        <div class="option">
                            <label>Map Size</label>
                            <select name="scale">
                                <?
                                // Loop through and display scale options
                                for ($scale = 0; $scale <= 8; $scale++){

                                    $cols = $scale * 4;
                                    $rows = $scale * 1;
                                    $label = !empty($scale) ? $rows.' x '.$cols : 'Random';
                                    //$label = !empty($scale) ? 'Level '.$scale : 'Random';

                                    $value = !empty($scale) ? $scale : '';

                                    if (empty($_REQUEST['scale']) && empty($scale)){ $selected = 'selected="selected"'; }
                                    elseif (!empty($_REQUEST['scale']) && $_REQUEST['scale'] == $scale){ $selected = 'selected="selected"'; }
                                    else { $selected = ''; }

                                    echo('<option value="'.$value.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);

                                }
                                ?>
                            </select>
                        </div>

                        <div class="option">
                            <label>Player Character</label>
                            <select name="player">
                                <?
                                // Loop through and display player character options
                                $player_tokens = array_merge(array(''), $index_player_tokens);
                                foreach ($player_tokens AS $player){

                                    $label = !empty($player) ? ucwords(str_replace('-', '. ', $player)) : 'Random';

                                    $value = !empty($player) ? $player : '';

                                    if (empty($_REQUEST['player']) && empty($player)){ $selected = 'selected="selected"'; }
                                    elseif (!empty($_REQUEST['player']) && $_REQUEST['player'] == $player){ $selected = 'selected="selected"'; }
                                    else { $selected = ''; }

                                    echo('<option value="'.$value.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);

                                }
                                ?>
                            </select>
                        </div>

                        <div class="option">
                            <label>Battle Field</label>
                            <select name="field">
                                <?
                                // Loop through and display battle field options
                                $field_tokens = array_merge(array(''), $index_field_tokens);
                                foreach ($field_tokens AS $field){

                                    $label = !empty($field) ? ucwords(str_replace('-', ' ', $field)) : 'Random';

                                    $value = !empty($field) ? $field : '';

                                    if (empty($_REQUEST['field']) && empty($field)){ $selected = 'selected="selected"'; }
                                    elseif (!empty($_REQUEST['field']) && $_REQUEST['field'] == $field){ $selected = 'selected="selected"'; }
                                    else { $selected = ''; }

                                    echo('<option value="'.$value.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);

                                }
                                ?>
                            </select>
                        </div>

                        <div class="option">
                            <label>Fortress Boss</label>
                            <select name="boss">
                                <?
                                // Loop through and display boss character options
                                $boss_tokens = array_merge(array(''), $index_boss_tokens);
                                foreach ($boss_tokens AS $boss){

                                    $label = !empty($boss) ? ucwords(str_replace('-', ' ', str_replace('-ds', ' DS', $boss))) : 'Random';

                                    $value = !empty($boss) ? $boss : '';

                                    if (empty($_REQUEST['boss']) && empty($boss)){ $selected = 'selected="selected"'; }
                                    elseif (!empty($_REQUEST['boss']) && $_REQUEST['boss'] == $boss){ $selected = 'selected="selected"'; }
                                    else { $selected = ''; }

                                    echo('<option value="'.$value.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);

                                }
                                ?>
                            </select>
                        </div>

                        <div class="buttons debug">

                            <a class="button retry type type_water"><span>Retry Mission</span></a>
                            <a class="button regenerate type type_time"><span>Regenerate Field</span></a>

                        </div>

                    <? } ?>

                </form>
            </div>

        <? } ?>

        <? if ($current_playlist_key !== false){ ?>

            <?
            // Calculate the current score for this user
            $current_score_total = 0;
            $current_mechas_defeated = 0;
            $current_mechas_total = 0;
            $current_masters_defeated = 0;
            $current_masters_total = 0;
            $current_bosses_defeated = 0;
            $current_bosses_total = 0;
            foreach ($this_map_progress AS $key => $complete_info){
                $current_score_total += $complete_info['points_earned'];
                if (isset($complete_info['mechas_defeated'])){ $current_mechas_defeated += $complete_info['mechas_defeated']; }
                if (isset($complete_info['mechas_total'])){ $current_mechas_total += $complete_info['mechas_total']; }
                if (isset($complete_info['robots_defeated'])){ $current_masters_defeated += $complete_info['robots_defeated']; }
                if (isset($complete_info['robots_total'])){ $current_masters_total += $complete_info['robots_total']; }
                if (isset($complete_info['bosses_defeated'])){ $current_bosses_defeated += $complete_info['bosses_defeated']; }
                if (isset($complete_info['bosses_total'])){ $current_bosses_total += $complete_info['bosses_total']; }
            }
            $defeated_sum = ($current_mechas_defeated + $current_masters_defeated + $current_bosses_defeated);
            $total_sum = ($current_mechas_total + $current_masters_total + $current_bosses_total);
            $current_percent_total = !empty($total_sum) ? (($defeated_sum / $total_sum) * 100) : 0;
            ?>
            <div class="field_progress">
                <div class="score <?= $current_playlist_key_results ? 'final type type_electric' : '' ?>" data-total="<?= $current_score_total ?>">
                    <span class="label"><?= $current_playlist_key_results ? 'Final Score' : 'Current Score' ?>:</span>
                    <span class="value"><?= number_format($current_score_total, 0, '.', ',') ?> Points</span>
                </div>
            </div>

            <div class="field_playlist">
                <?

                // Collect a custom field index for display purposes
                $print_field_tokens = array_filter(array_column($this_map_playlist, 'field'));
                $print_field_index = rpg_field::get_index_custom($print_field_tokens);

                // Collect a custom boss index for display purposes
                $print_boss_tokens = array_filter(array_column($this_map_playlist, 'boss'));
                $print_boss_index = rpg_robot::get_index_custom($print_boss_tokens);

                // Loop through the playlist and print all entries
                echo('<table style="width: 100%">'.PHP_EOL);

                    echo('<thead>'.PHP_EOL);
                        echo('<tr>');
                            echo('<th class="number">#</th>');
                            echo('<th class="name">Field Name</th>');
                            echo('<th class="size">Size</th>');
                            echo('<th class="difficulty">Difficulty</th>');
                            echo('<th class="mechas">Mechas</th>');
                            echo('<th class="masters">Masters</th>');
                            echo('<th class="bosses">Bosses</th>');
                            echo('<th class="score">Points</th>');
                            echo('<th class="percent">%</th>');
                        echo('</tr>'.PHP_EOL);
                    echo('</thead>'.PHP_EOL);

                    echo('<tfoot>'.PHP_EOL);
                        echo('<tr>');

                            echo('<td class="number"></td>');
                            echo('<td class="name">Overall Totals</td>');
                            echo('<td class="size"></td>');
                            echo('<td class="difficulty"></td>');

                            echo('<td class="mechas">'.$current_mechas_defeated.' / <sub>'.$current_mechas_total.'</sub></td>');
                            echo('<td class="masters">'.$current_masters_defeated.' / <sub>'.$current_masters_total.'</sub></sub></td>');
                            echo('<td class="bosses">'.$current_bosses_defeated.' / <sub>'.$current_bosses_total.'</sub></sub></td>');

                            echo('<td class="score">'.number_format($current_score_total, 0, '.', ',').'</td>');

                            $percent_text = number_format($current_percent_total, 2, '.', ',');
                            $percent_class = '';
                            if ($current_percent_total == 100){ $percent_class = 'perfect'; }
                            elseif ($current_percent_total >= 75){ $percent_class = 'high'; }
                            elseif ($current_percent_total >= 50){ $percent_class = 'medium'; }
                            elseif ($current_percent_total >= 25){ $percent_class = 'low'; }
                            elseif ($current_percent_total > 0){ $percent_class = 'bad'; }
                            echo('<td class="percent '.$percent_class.'">'.$percent_text.'%</td>');

                        echo('</tr>'.PHP_EOL);
                    echo('</tfoot>'.PHP_EOL);

                    echo('<tbody>'.PHP_EOL);
                        foreach ($this_map_playlist AS $key => $entry){
                            if (!empty($entry['results'])){ continue; }

                            $complete_info = isset($this_map_progress[$key]) ? $this_map_progress[$key] : false;

                            echo('<tr>');

                                $number_text = $key + 1;
                                echo('<td class="number">'.$number_text.'</td>');

                                $field_info = !empty($entry['field']) ? $print_field_index[$entry['field']] : false;
                                $field_text = isset($field_info['field_name']) ? $field_info['field_name'] : '-';
                                if (empty($complete_info) && $key != $current_playlist_key){ $field_text = '-'; }
                                echo('<td class="name">'.$field_text.'</td>');

                                $scale_text = !empty($entry['scale']) ? $entry['scale'].' x '.($entry['scale'] * 4) : '-';
                                if (empty($complete_info) && $key != $current_playlist_key){ $scale_text = '-'; }
                                echo('<td class="size">'.$scale_text.'</td>');

                                $difficulty_value = !empty($entry['difficulty']) ? $entry['difficulty'] : $entry['scale'];
                                $difficulty_text = str_repeat('&#9733;', $difficulty_value);
                                if (empty($complete_info) && $key != $current_playlist_key){ $difficulty_text = '-'; }
                                echo('<td class="difficulty">'.$difficulty_text.'</td>');

                                if (!empty($complete_info)){

                                    $mechas_defeated = $complete_info['mechas_defeated'];
                                    $mechas_total = $complete_info['mechas_total'];
                                    $mechas_text = $mechas_defeated.'<sub> / '.$mechas_total.'</sub>';
                                    echo('<td class="mechas">'.$mechas_text.'</td>');

                                    $masters_defeated = $complete_info['robots_defeated'];
                                    $masters_total = $complete_info['robots_total'];
                                    $masters_text = $masters_defeated.'<sub> / '.$masters_total.'</sub>';
                                    echo('<td class="masters">'.$masters_text.'</td>');

                                    $bosses_defeated = $complete_info['bosses_defeated'];
                                    $bosses_total = $complete_info['bosses_total'];
                                    $bosses_text = $bosses_defeated.'<sub> / '.$bosses_total.'</sub>';
                                    echo('<td class="bosses">'.$bosses_text.'</td>');

                                    $score_text = isset($complete_info['points_earned']) ? number_format($complete_info['points_earned'], 0, '.', ',') : '-';
                                    echo('<td class="score">'.$score_text.'</td>');

                                    $percent_text = isset($complete_info['percent_complete']) ? $complete_info['percent_complete'].'%' : '-';
                                    $percent_class = '';
                                    if ($complete_info['percent_complete'] == 100){ $percent_class = 'perfect'; }
                                    elseif ($complete_info['percent_complete'] >= 75){ $percent_class = 'high'; }
                                    elseif ($complete_info['percent_complete'] >= 50){ $percent_class = 'medium'; }
                                    elseif ($complete_info['percent_complete'] >= 25){ $percent_class = 'low'; }
                                    elseif ($complete_info['percent_complete'] > 0){ $percent_class = 'bad'; }
                                    echo('<td class="percent '.$percent_class.'">'.$percent_text.'</td>');


                                } else {

                                    echo('<td class="mechas">-</td>');
                                    echo('<td class="masters">-</td>');
                                    echo('<td class="bosses">-</td>');
                                    echo('<td class="score">-</td>');
                                    echo('<td class="percent">-</td>');

                                }

                            echo('</tr>'.PHP_EOL);
                        }
                    echo('</tbody>'.PHP_EOL);

                echo('</table>'.PHP_EOL);

                //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>'.PHP_EOL);
                //echo('<pre>$this_map_playlist = '.print_r($this_map_playlist, true).'</pre>'.PHP_EOL);
                //echo('<pre>$this_map_progress = '.print_r($this_map_progress, true).'</pre>'.PHP_EOL);

                ?>
            </div>

        <? } ?>

        <? if ($current_playlist_key_results){  ?>
            <div class="game_complete">
                <div class="thank_you type type_nature">
                    Thank you for playing!
                </div>
                <a class="link link_inline play_again" href="dev/map-test/">Play again?</a>
            </div>
        <?} ?>

        <div>
            <?

            // DEBUG DEBUG DEBUG
            echo $debug_variable_text;

            ?>
        </div>

    </div>

</div>
