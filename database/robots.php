<?

// Compensate for missing arg
if (!isset($is_preview_mode)){ $is_preview_mode = false; }

// ROBOT MASTER DATABASE

// Define the index of counters for robot types
$mmrpg_database_robots_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
    $mmrpg_database_robots_types['cores'][$token] = 0;
    $mmrpg_database_robots_types['weaknesses'][$token] = 0;
    $mmrpg_database_robots_types['resistances'][$token] = 0;
    $mmrpg_database_robots_types['affinities'][$token] = 0;
    $mmrpg_database_robots_types['immunities'][$token] = 0;
}

// Define the index of hidden robots to not appear in the database
$hidden_database_robots = array();
$hidden_database_robots_count = !empty($hidden_database_robots) ? count($hidden_database_robots) : 0;

// Collect the robot database files from the cache or manually
$cache_token = md5('database/robots/website');
$cached_index = rpg_object::load_cached_index('database.robots', $cache_token);
if (!empty($cached_index) && empty($_GET['refresh'])){

    // Collect the cached data for robots, robot count, and robot numbers
    $mmrpg_database_robots = $cached_index['mmrpg_database_robots'];
    $mmrpg_database_robots_count = $cached_index['mmrpg_database_robots_count'];
    $mmrpg_database_robots_numbers = $cached_index['mmrpg_database_robots_numbers'];
    unset($cached_index);

} else {

    // Collect the database fields
    $mmrpg_database_skills = rpg_skill::get_index(true);
    $mmrpg_database_fields = rpg_field::get_index(true, false);

    // Collect the database robots
    $robot_where = "robots.robot_token <> 'robot' AND robots.robot_class = 'master' AND robots.robot_flag_published = 1";
    if (defined('FORCE_INCLUDE_TEMPLATE_ROBOT')){ $robot_where = "({$robot_where}) OR robots.robot_token = 'robot' "; }
    $robot_fields = rpg_robot::get_index_fields(true, 'robots');
    $mmrpg_database_robots = $db->get_array_list("SELECT
        {$robot_fields},
        groups.group_token AS robot_group,
        tokens.token_order AS robot_order
        FROM mmrpg_index_robots AS robots
        LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
        LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
        LEFT JOIN mmrpg_index_fields AS fields ON fields.field_token = robots.robot_field
        WHERE {$robot_where}
        ORDER BY
        robots.robot_class DESC,
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'robot_token');

    // Count the database robots in total (without filters)
    $mmrpg_database_robots_count = $db->get_value("SELECT
        COUNT(robots.robot_id) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'master'
        AND robots.robot_flag_published = 1
        AND robots.robot_flag_hidden = 0
        ;", 'robot_count');

    // Select an ordered list of all robots and then assign row numbers to them
    $mmrpg_database_robots_numbers = $db->get_array_list("SELECT
        robots.robot_token,
        0 AS robot_key
        FROM mmrpg_index_robots AS robots
        LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
        LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'master'
        AND robots.robot_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'robot_token');
    $robot_key = 1;
    foreach ($mmrpg_database_robots_numbers AS $token => $info){
        $mmrpg_database_robots_numbers[$token]['robot_key'] = $robot_key++;
    }

    // Remove unallowed robots from the database, and increment type counters
    if (!empty($mmrpg_database_robots)){
        foreach ($mmrpg_database_robots AS $temp_token => $temp_info){

            // Send this data through the robot index parser
            $temp_info = rpg_robot::parse_index_info($temp_info);

            // Collect this robot's key in the index
            if (!isset($mmrpg_database_robots_numbers[$temp_token])){ $temp_info['robot_key'] = -1; }
            else { $temp_info['robot_key'] = $mmrpg_database_robots_numbers[$temp_token]['robot_key']; }

            // Ensure this robot's image exists, else default to the placeholder
            if ($temp_info['robot_flag_complete'] || ($is_preview_mode === true && $temp_token === $this_current_token)){ $temp_info['robot_image'] = $temp_token; }
            else { $temp_info['robot_image'] = 'robot'; }

            // Modify the name of this robot if it is of the mecha class
            if ($temp_info['robot_class'] == 'mecha' && defined('DATA_DATABASE_SHOW_MECHAS')){
                // Collect this mecha's field token, then robot master token, then robot master number
                $temp_field_token = $temp_info['robot_field'];
                $temp_field_info = !empty($mmrpg_database_fields[$temp_field_token]) ? rpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]) : array();
                $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
                $temp_master_number = $mmrpg_database_robots[$temp_master_token]['robot_number'];
                $temp_info['robot_master_number'] = $temp_master_number;
            } elseif ($temp_info['robot_class'] == 'master'){
                $temp_info['robot_master_number'] = $temp_info['robot_number'];
            } elseif ($temp_info['robot_class'] == 'boss'){
                $temp_info['robot_master_number'] = $temp_info['robot_number'];
            }

            // Increment the robot core counter if not empty
            if (!empty($temp_info['robot_core'])){ $mmrpg_database_robots_types['cores'][$temp_info['robot_core']]++; }
            else { $mmrpg_database_robots_types['cores']['none']++; }
            if (!empty($temp_info['robot_core2'])){ $mmrpg_database_robots_types['cores'][$temp_info['robot_core2']]++; }

            // Loop through the robot weaknesses if there are any to loop through
            if (!empty($temp_info['robot_weaknesses'])){
                foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_robots_types['weaknesses'][$weakness]++; }
            } else {
                $mmrpg_database_robots_types['weaknesses']['none']++;
            }
            // Loop through the robot resistances if there are any to loop through
            if (!empty($temp_info['robot_resistances'])){
                foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_robots_types['resistances'][$weakness]++; }
            } else {
                $mmrpg_database_robots_types['resistances']['none']++;
            }
            // Loop through the robot affinities if there are any to loop through
            if (!empty($temp_info['robot_affinities'])){
                foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_robots_types['affinities'][$weakness]++; }
            } else {
                $mmrpg_database_robots_types['affinities']['none']++;
            }
            // Loop through the robot immunities if there are any to loop through
            if (!empty($temp_info['robot_immunities'])){
                foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_robots_types['immunities'][$weakness]++; }
            } else {
                $mmrpg_database_robots_types['immunities']['none']++;
            }

            // Update the main database array with the changes
            $mmrpg_database_robots[$temp_token] = $temp_info;
        }
    }

    // Save the cached data for robots, robot count, and robot numbers
    rpg_object::save_cached_index('database.robots', $cache_token, array(
        'mmrpg_database_robots' => $mmrpg_database_robots,
        'mmrpg_database_robots_count' => $mmrpg_database_robots_count,
        'mmrpg_database_robots_numbers' => $mmrpg_database_robots_numbers
        ));
}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_robots)
    && is_callable($filter_mmrpg_database_robots)){
    $mmrpg_database_robots = array_filter($mmrpg_database_robots, $filter_mmrpg_database_robots);
}

// Loop through and remove hidden robots unless they're being viewed explicitly
if (!empty($mmrpg_database_robots)){
    foreach ($mmrpg_database_robots AS $temp_token => $temp_info){
        if (!empty($temp_info['robot_flag_hidden'])
            && $temp_info['robot_token'] !== $this_current_token
            && !defined('FORCE_INCLUDE_HIDDEN_ROBOTS')){
            unset($mmrpg_database_robots[$temp_token]);
        }
    }
}

// Define the max stat value before we filter and update
if (!isset($mmrpg_stat_base_max_value)){ $mmrpg_stat_base_max_value = array(); }
$mmrpg_stat_base_max_value['master'] = 0;

// Define database variables we'll be using to generate links
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_robots_links = '';
$mmrpg_database_robots_links_index = array();
$mmrpg_database_robots_links_counter = 0;
$mmrpg_database_robots_count_complete = 0;
$mmrpg_database_robots_count_fightable = 0;
$mmrpg_database_robots_count_unlockable = 0;

// Loop through the results and generate the links for these robots
if (!empty($mmrpg_database_robots)){
    foreach ($mmrpg_database_robots AS $robot_key => $robot_info){
        if (!isset($first_robot_key)){ $first_robot_key = $robot_key; }

        // Do not show hidden robots in the link list
        if (!empty($robot_info['robot_flag_hidden'])){ continue; }

        // Do not show incomplete robots in the link list
        $show_in_link_list = true;
        if (!$robot_info['robot_flag_complete'] && $robot_info['robot_token'] !== $this_current_token){ $show_in_link_list = false; }

        // If a type filter has been applied to the robot page
        if (isset($this_current_filter) && $this_current_filter == 'none' && $robot_info['robot_core'] != ''){ $key_counter++; continue; }
        elseif (isset($this_current_filter) && $this_current_filter != 'none' && $robot_info['robot_core'] != $this_current_filter && $robot_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

        // If this is the first in a new group
        $game_code = !empty($robot_info['robot_group']) ? $robot_info['robot_group'] : (!empty($robot_info['robot_game']) ? $robot_info['robot_game'] : 'MMRPG');
        if ($show_in_link_list && $game_code != $last_game_code){
            if (!empty($mmrpg_database_robots_links)){ $mmrpg_database_robots_links .= '</div>'; }
            $mmrpg_database_robots_links .= '<div class="float link group" data-game="'.$game_code.'">';
            $last_game_code = $game_code;
        }

        // Check if this is a mecha and prepare extra text
        $robot_info['robot_name_append'] = '';

        // Collect skill info for this robot if applicable
        $robot_flag_has_skill = !empty($robot_info['robot_skill']) ? true : false;
        $robot_skill_info = $robot_flag_has_skill ? rpg_robot::get_robot_skill_info($robot_info['robot_skill'], $robot_info) : false;
        $robot_skill_display_type = $robot_flag_has_skill ? (!empty($robot_skill_info['skill_display_type']) ? $robot_skill_info['skill_display_type'] : 'none') : false;

        // Collect the robot sprite dimensions
        $robot_flag_complete = !empty($robot_info['robot_flag_complete']) ? true : false;
        $robot_flag_fightable = !empty($robot_info['robot_flag_fightable']) ? true : false;
        $robot_flag_unlockable = !empty($robot_info['robot_flag_unlockable']) ? true : false;
        $robot_core_type = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
        $robot_core_type2 = !empty($robot_info['robot_core']) && !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : '';
        $robot_core_class = $robot_core_type.(!empty($robot_core_type2) ? '_'.$robot_core_type2 : '');
        $robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
        $robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
        $robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
        $robot_image_incomplete = $robot_image_token == 'robot' ? true : false;
        $robot_is_active = !empty($this_current_token) && $this_current_token == $robot_info['robot_token'] ? true : false;
        $robot_title_text = $robot_info['robot_name'].$robot_info['robot_name_append'].' | '.$robot_info['robot_number'].' | '.(!empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral').' Core';
        $robot_title_text .= '|| [[E:'.$robot_info['robot_energy'].' | W:'.$robot_info['robot_weapons'].' | A:'.$robot_info['robot_attack'].' | D:'.$robot_info['robot_defense'].' | S:'.$robot_info['robot_speed'].']] ';
        if ($robot_flag_has_skill){ $robot_title_text .= '|| [[Passive Skill : '.$robot_skill_info['skill_name'].']] '; }
        //$robot_title_text .= ' | game:'.$robot_info['robot_game'].' | group:'.$robot_info['robot_group'];
        $robot_image_path = 'images/robots/'.$robot_image_token.'/mug_right_'.$robot_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $robot_stat_max = $robot_info['robot_energy'] + $robot_info['robot_attack'] + $robot_info['robot_defense'] + $robot_info['robot_speed'];
        if ($robot_stat_max > $mmrpg_stat_base_max_value['master']){ $mmrpg_stat_base_max_value['master'] = $robot_stat_max; }

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $robot_title_text ?>" data-token="<?= $robot_info['robot_token'] ?>" class="float left link type <?=
            ($robot_core_class.' ').
            ($robot_image_incomplete ? 'inactive ' : '').
            ($robot_flag_fightable ? 'fightable ' : '').
            ($robot_flag_unlockable ? 'unlockable ' : '').
            ($robot_flag_has_skill ? 'has-skill ' : '')
            ?>">
            <a class="sprite robot link mugshot size<?= $robot_image_size.($robot_key == $first_robot_key ? ' current' : '') ?>" href="<?= 'database/robots/'.$robot_info['robot_token']?>/" rel="<?= $robot_image_incomplete ? 'nofollow' : 'follow' ?>">
                <?php if($robot_image_token != 'robot'): ?>
                    <img src="<?= $robot_image_path ?>" width="<?= $robot_image_size ?>" height="<?= $robot_image_size ?>" alt="<?= $robot_title_text ?>" />
                <?php else: ?>
                    <span><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></span>
                <?php endif; ?>
            </a>
            <?= $robot_flag_has_skill ? '<i class="skill type '.$robot_skill_display_type.'"></i>' : '' ?>
        </div>
        <?php
        if ($robot_flag_complete){ $mmrpg_database_robots_count_complete++; }
        if ($robot_flag_fightable){ $mmrpg_database_robots_count_fightable++; }
        if ($robot_flag_unlockable){ $mmrpg_database_robots_count_unlockable++; }
        $temp_markup = ob_get_clean();
        $mmrpg_database_robots_links_index[$robot_key] = $temp_markup;
        if ($show_in_link_list){ $mmrpg_database_robots_links .= $temp_markup; }
        $mmrpg_database_robots_links_counter++;
        $key_counter++;

    }
}

// End the groups, however many there were
$mmrpg_database_robots_links .= '</div>';

?>