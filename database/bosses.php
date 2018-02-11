<?

// FORTRESS BOSS DATABASE

// Define the index of counters for boss types
$mmrpg_database_bosses_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
    $mmrpg_database_bosses_types['cores'][$token] = 0;
    $mmrpg_database_bosses_types['weaknesses'][$token] = 0;
    $mmrpg_database_bosses_types['resistances'][$token] = 0;
    $mmrpg_database_bosses_types['affinities'][$token] = 0;
    $mmrpg_database_bosses_types['immunities'][$token] = 0;
}

// Define the index of hidden bosses to not appear in the database
$hidden_database_bosses = array();
$hidden_database_bosses_count = !empty($hidden_database_bosses) ? count($hidden_database_bosses) : 0;


// Define the hidden boss query condition
$temp_condition = '';
$temp_condition .= "AND robot_class = 'boss' ";
if (!empty($hidden_database_bosses)){
    $temp_tokens = array();
    foreach ($hidden_database_bosses AS $token){ $temp_tokens[] = "'".$token."'"; }
    $temp_condition .= 'AND robot_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_bosses_filter)){
    if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_bosses_filter)){ $temp_condition .= 'AND ';  }
    $temp_condition .= $mmrpg_database_bosses_filter;
}


// Collect the database bosses and fields
$field_fields = rpg_field::get_index_fields(true);
$robot_fields = rpg_robot::get_index_fields(true);
$db->query("SET @robot_row_number = 0;");
$mmrpg_database_fields = $db->get_array_list("SELECT {$field_fields} FROM mmrpg_index_fields WHERE field_flag_published = 1;", 'field_token');
$mmrpg_database_bosses = $db->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE robot_flag_published = 1 AND (robot_flag_hidden = 0 OR robot_token = '{$this_current_token}') {$temp_condition} ORDER BY robot_order ASC;", 'robot_token');
$mmrpg_database_bosses_count = $db->get_value("SELECT COUNT(robot_id) AS robot_count FROM mmrpg_index_robots WHERE robot_flag_published = 1 AND robot_flag_hidden = 0 {$temp_condition_unfiltered};", 'robot_count');
$mmrpg_database_bosses_numbers = $db->get_array_list("SELECT robot_token, (@robot_row_number:=@robot_row_number + 1) AS robot_key FROM mmrpg_index_robots WHERE robot_flag_published = 1 {$temp_condition_unfiltered} ORDER BY robot_flag_hidden ASC, robot_order ASC;", 'robot_token');

// Remove unallowed bosses from the database, and increment type counters
foreach ($mmrpg_database_bosses AS $temp_token => $temp_info){

    // Define first boss token if not set
    if (!isset($first_boss_token)){ $first_boss_token = $temp_token; }

    // Send this data through the boss index parser
    $temp_info = rpg_robot::parse_index_info($temp_info);

    // Collect this boss's key in the index
    $temp_info['robot_key'] = $mmrpg_database_bosses_numbers[$temp_token]['robot_key'];

    // Ensure this boss's image exists, else default to the placeholder
    if ($temp_info['robot_flag_complete']){ $temp_info['robot_image'] = $temp_token; }
    else { $temp_info['robot_image'] = 'boss'; }

    // Modify the name of this boss if it is of the boss class
    if ($temp_info['robot_class'] == 'boss'){
        // Collect this boss's field token, then boss master token, then boss master number
        $temp_field_token = !is_string($temp_info['robot_field']) ? array_shift($temp_info['robot_field']) : $temp_info['robot_field'];
        //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
        $temp_field_info = !empty($mmrpg_database_fields[$temp_field_token]) ? rpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]) : array();
        //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
        $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
        $temp_master_number = !empty($mmrpg_database_robots[$temp_master_token]) ? $mmrpg_database_robots[$temp_master_token]['robot_number'] : $temp_info['robot_number'];
        $temp_info['robot_master_number'] = $temp_master_number;
    }


    // Increment the boss core counter if not empty
    if (!empty($temp_info['robot_core'])){ $mmrpg_database_bosses_types['cores'][$temp_info['robot_core']]++; }
    else { $mmrpg_database_bosses_types['cores']['none']++; }
    if (!empty($temp_info['robot_core2'])){ $mmrpg_database_bosses_types['cores'][$temp_info['robot_core2']]++; }

    // Define the stat attributes to loop through and then do so to count instances
    $stat_attributes = array('weaknesses', 'resistances', 'affinities', 'immunities');
    foreach ($stat_attributes AS $attribute){
        if (!empty($temp_info['robot_'.$attribute])){
            foreach ($temp_info['robot_'.$attribute] AS $type){
                if ($type === '*'){ foreach ($mmrpg_database_bosses_types[$attribute] AS $k => $v){ $mmrpg_database_bosses_types[$attribute][$k]++; } }
                if (!isset($mmrpg_database_bosses_types[$attribute][$type])){ continue; }
                $mmrpg_database_bosses_types[$attribute][$type]++;
            }
        } else {
            $mmrpg_database_bosses_types[$attribute]['none']++;
        }
    }

    // Update the main database array with the changes
    $mmrpg_database_bosses[$temp_token] = $temp_info;
}

// Define the max stat value before we filter and update
if (!isset($mmrpg_stat_base_max_value)){ $mmrpg_stat_base_max_value = array(); }
$mmrpg_stat_base_max_value['boss'] = 0;

// Loop through the database and generate the links for these bosses
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_bosses_links = '';
$mmrpg_database_bosses_links_index = array();
$mmrpg_database_bosses_links_counter = 0;
$mmrpg_database_bosses_count_complete = 0;

// Loop through the results and generate the links for these bosses
foreach ($mmrpg_database_bosses AS $boss_key => $boss_info){

    // Do not show hidden bosses in the link list
    if (!empty($boss_info['robot_flag_hidden'])){ continue; }

    // If a type filter has been applied to the robot page
    if (isset($this_current_filter) && $this_current_filter == 'none' && $boss_info['robot_core'] != ''){ $key_counter++; continue; }
    elseif (isset($this_current_filter) && $this_current_filter != 'none' && $boss_info['robot_core'] != $this_current_filter && $boss_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

    // If this is the first in a new group
    $game_code = !empty($boss_info['robot_group']) ? $boss_info['robot_group'] : (!empty($boss_info['robot_game']) ? $boss_info['robot_game'] : 'MMRPG');
    if ($game_code != $last_game_code){
        if ($key_counter != 0){ $mmrpg_database_bosses_links .= '</div>'; }
        $mmrpg_database_bosses_links .= '<div class="float link group" data-game="'.$game_code.'">';
        $last_game_code = $game_code;
    }

    // Check if this is a boss and prepare extra text
    $boss_info['robot_name_append'] = '';

    // Collect the boss sprite dimensions
    $boss_flag_complete = !empty($boss_info['robot_flag_complete']) ? true : false;
    $boss_image_size = !empty($boss_info['robot_image_size']) ? $boss_info['robot_image_size'] : 40;
    $boss_image_size_text = $boss_image_size.'x'.$boss_image_size;
    $boss_image_token = !empty($boss_info['robot_image']) ? $boss_info['robot_image'] : $boss_info['robot_token'];
    $boss_image_incomplete = $boss_image_token == 'boss' ? true : false;
    $boss_is_active = !empty($this_current_token) && $this_current_token == $boss_info['robot_token'] ? true : false;
    $boss_title_text = $boss_info['robot_name'].$boss_info['robot_name_append'].' | '.$boss_info['robot_number'].' | '.(!empty($boss_info['robot_core']) ? ucwords($boss_info['robot_core'].(!empty($boss_info['robot_core2']) ? ' / '.$boss_info['robot_core2'] : '')) : 'Neutral').' Type';
    $boss_title_text .= '|| [[E:'.$boss_info['robot_energy'].' | W:'.$boss_info['robot_weapons'].' | A:'.$boss_info['robot_attack'].' | D:'.$boss_info['robot_defense'].' | S:'.$boss_info['robot_speed'].']]';
    $boss_image_path = 'images/robots/'.$boss_image_token.'/mug_right_'.$boss_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
    $boss_stat_max = $boss_info['robot_energy'] + $boss_info['robot_attack'] + $boss_info['robot_defense'] + $boss_info['robot_speed'];
    if ($boss_stat_max > $mmrpg_stat_base_max_value['boss']){ $mmrpg_stat_base_max_value['boss'] = $boss_stat_max; }

    // Start the output buffer and collect the generated markup
    ob_start();
    ?>
    <div title="<?= $boss_title_text ?>" data-token="<?= $boss_info['robot_token'] ?>" class="float left link type <?= ($boss_image_incomplete  ? 'inactive ' : '').(!empty($boss_info['robot_core']) ? $boss_info['robot_core'] : 'none').(!empty($boss_info['robot_core2']) ? '_'.$boss_info['robot_core2'] : '') ?>">
        <a class="sprite robot link mugshot size<?= $boss_image_size.($boss_key == $first_boss_token ? ' current' : '') ?>" href="<?= 'database/bosses/'.$boss_info['robot_token']?>/" rel="<?= $boss_image_incomplete ? 'nofollow' : 'follow' ?>">
            <?php if($boss_image_token != 'boss'): ?>
                <img src="<?= $boss_image_path ?>" width="<?= $boss_image_size ?>" height="<?= $boss_image_size ?>" alt="<?= $boss_title_text ?>" />
            <?php else: ?>
                <span><?= $boss_info['robot_name'].$boss_info['robot_name_append'] ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php
    if ($boss_flag_complete){ $mmrpg_database_bosses_count_complete++; }
    $temp_markup = ob_get_clean();
    $mmrpg_database_bosses_links_index[$boss_key] = $temp_markup;
    $mmrpg_database_bosses_links .= $temp_markup;
    $mmrpg_database_bosses_links_counter++;
    $key_counter++;

}

// End the groups, however many there were
$mmrpg_database_bosses_links .= '</div>';

?>