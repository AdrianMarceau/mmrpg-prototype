<?

// FORTRESS BOSS DATABASE

// Define the index of counters for boss types
$mmrpg_database_all_types = rpg_type::get_index(true);
$mmrpg_database_bosses_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_all_types AS $token => $info){
    $mmrpg_database_bosses_types['cores'][$token] = 0;
    $mmrpg_database_bosses_types['weaknesses'][$token] = 0;
    $mmrpg_database_bosses_types['resistances'][$token] = 0;
    $mmrpg_database_bosses_types['affinities'][$token] = 0;
    $mmrpg_database_bosses_types['immunities'][$token] = 0;
}

// Define the index of hidden bosses to not appear in the database
$hidden_database_bosses = array();
$hidden_database_bosses_count = !empty($hidden_database_bosses) ? count($hidden_database_bosses) : 0;

// Collect the robot database files from the cache or manually
$cache_token = md5('database/bosses/website');
$cached_index = rpg_object::load_cached_index('database.bosses', $cache_token);
if (!empty($cached_index)){

    // Collect the cached data for bosses, robot count, and robot numbers
    $mmrpg_database_bosses = $cached_index['mmrpg_database_bosses'];
    $mmrpg_database_bosses_count = $cached_index['mmrpg_database_bosses_count'];
    $mmrpg_database_bosses_numbers = $cached_index['mmrpg_database_bosses_numbers'];
    unset($cached_index);

} else {

    // Collect the database fields
    $mmrpg_database_skills = rpg_skill::get_index(true);
    $mmrpg_database_fields = rpg_field::get_index(true, false);

    // Collect the database bosses
    $mecha_fields = rpg_robot::get_index_fields(true, 'robots');
    $mmrpg_database_bosses = $db->get_array_list("SELECT
        {$mecha_fields},
        groups.group_token AS robot_group,
        tokens.token_order AS robot_order
        FROM mmrpg_index_robots AS robots
        LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
        LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
        LEFT JOIN mmrpg_index_fields AS fields ON fields.field_token = robots.robot_field
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'boss'
        AND robots.robot_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'robot_token');

    // Count the database bosses in total (without filters)
    $mmrpg_database_bosses_count = $db->get_value("SELECT
        COUNT(robots.robot_id) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'boss'
        AND robots.robot_flag_published = 1
        AND robots.robot_flag_hidden = 0
        ;", 'robot_count');

    // Select an ordered list of all bosses and then assign row numbers to them
    $mmrpg_database_bosses_numbers = $db->get_array_list("SELECT
        robots.robot_token,
        0 AS robot_key
        FROM mmrpg_index_robots AS robots
        LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
        LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'boss'
        AND robots.robot_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'robot_token');
    $boss_key = 1;
    foreach ($mmrpg_database_bosses_numbers AS $token => $info){
        $mmrpg_database_bosses_numbers[$token]['robot_key'] = $boss_key++;
    }

    // Remove unallowed bosses from the database, and increment type counters
    if (!empty($mmrpg_database_bosses)){
        foreach ($mmrpg_database_bosses AS $temp_token => $temp_info){

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

            // Loop through the boss weaknesses if there are any to loop through
            if (!empty($temp_info['robot_weaknesses'])){
                foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_bosses_types['weaknesses'][$weakness]++; }
            } else {
                $mmrpg_database_bosses_types['weaknesses']['none']++;
            }
            // Loop through the boss resistances if there are any to loop through
            if (!empty($temp_info['robot_resistances'])){
                foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_bosses_types['resistances'][$weakness]++; }
            } else {
                $mmrpg_database_bosses_types['resistances']['none']++;
            }
            // Loop through the boss affinities if there are any to loop through
            if (!empty($temp_info['robot_affinities'])){
                foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_bosses_types['affinities'][$weakness]++; }
            } else {
                $mmrpg_database_bosses_types['affinities']['none']++;
            }
            // Loop through the boss immunities if there are any to loop through
            if (!empty($temp_info['robot_immunities'])){
                foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_bosses_types['immunities'][$weakness]++; }
            } else {
                $mmrpg_database_bosses_types['immunities']['none']++;
            }

            // Update the main database array with the changes
            $mmrpg_database_bosses[$temp_token] = $temp_info;
        }
    }

    // Save the cached data for bosses, robot count, and robot numbers
    rpg_object::save_cached_index('database.bosses', $cache_token, array(
        'mmrpg_database_bosses' => $mmrpg_database_bosses,
        'mmrpg_database_bosses_count' => $mmrpg_database_bosses_count,
        'mmrpg_database_bosses_numbers' => $mmrpg_database_bosses_numbers
        ));
}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_bosses)
    && is_callable($filter_mmrpg_database_bosses)){
    $mmrpg_database_bosses = array_filter($mmrpg_database_bosses, $filter_mmrpg_database_bosses);
}

// Loop through and remove hidden bosses unless they're being viewed explicitly
if (!empty($mmrpg_database_bosses)){
    foreach ($mmrpg_database_bosses AS $temp_token => $temp_info){
        if (!empty($temp_info['robot_flag_hidden'])
            && $temp_info['robot_token'] !== $this_current_token){
            unset($mmrpg_database_bosses[$temp_token]);
        }
    }
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
$mmrpg_database_bosses_count_fightable = 0;

// Loop through the results and generate the links for these bosses
if (!empty($mmrpg_database_bosses)){
    foreach ($mmrpg_database_bosses AS $boss_key => $boss_info){
        if (!isset($first_boss_key)){ $first_boss_key = $boss_key; }

        // Do not show hidden bosses in the link list
        if (!empty($boss_info['robot_flag_hidden'])){ continue; }

        // Do not show incomplete bosses in the link list
        $show_in_link_list = true;
        if (!$boss_info['robot_flag_complete'] && $boss_info['robot_token'] !== $this_current_token){ $show_in_link_list = false; }

        // If a type filter has been applied to the robot page
        if (isset($this_current_filter) && $this_current_filter == 'none' && $boss_info['robot_core'] != ''){ $key_counter++; continue; }
        elseif (isset($this_current_filter) && $this_current_filter != 'none' && $boss_info['robot_core'] != $this_current_filter && $boss_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

        // If this is the first in a new group
        $game_code = !empty($boss_info['robot_group']) ? $boss_info['robot_group'] : (!empty($boss_info['robot_game']) ? $boss_info['robot_game'] : 'MMRPG');
        if ($show_in_link_list && $game_code != $last_game_code){
            if (!empty($mmrpg_database_bosses_links)){ $mmrpg_database_bosses_links .= '</div>'; }
            $mmrpg_database_bosses_links .= '<div class="float link group" data-game="'.$game_code.'">';
            $last_game_code = $game_code;
        }

        // Check if this is a boss and prepare extra text
        $boss_info['robot_name_append'] = '';

        // Collect skill info for this boss if applicable
        $boss_flag_has_skill = !empty($boss_info['robot_skill']) ? true : false;
        $boss_skill_info = $boss_flag_has_skill ? rpg_robot::get_robot_skill_info($boss_info['robot_skill'], $boss_info) : false;
        $boss_skill_display_type = $boss_flag_has_skill ? (!empty($boss_skill_info['skill_display_type']) ? $boss_skill_info['skill_display_type'] : 'none') : false;

        // Collect the boss sprite dimensions
        $boss_flag_complete = !empty($boss_info['robot_flag_complete']) ? true : false;
        $boss_flag_fightable = !empty($boss_info['robot_flag_fightable']) ? true : false;
        $boss_core_type = !empty($boss_info['robot_core']) ? $boss_info['robot_core'] : 'none';
        $boss_core_type2 = !empty($boss_info['robot_core']) && !empty($boss_info['robot_core2']) ? $boss_info['robot_core2'] : '';
        $boss_core_class = $boss_core_type.(!empty($boss_core_type2) ? '_'.$boss_core_type2 : '');
        $boss_image_size = !empty($boss_info['robot_image_size']) ? $boss_info['robot_image_size'] : 40;
        $boss_image_size_text = $boss_image_size.'x'.$boss_image_size;
        $boss_image_token = !empty($boss_info['robot_image']) ? $boss_info['robot_image'] : $boss_info['robot_token'];
        $boss_image_incomplete = $boss_image_token == 'boss' ? true : false;
        $boss_is_active = !empty($this_current_token) && $this_current_token == $boss_info['robot_token'] ? true : false;
        $boss_title_text = $boss_info['robot_name'].$boss_info['robot_name_append'].' | '.$boss_info['robot_number'].' | '.(!empty($boss_info['robot_core']) ? ucwords($boss_info['robot_core'].(!empty($boss_info['robot_core2']) ? ' / '.$boss_info['robot_core2'] : '')) : 'Neutral').' Type';
        $boss_title_text .= '|| [[E:'.$boss_info['robot_energy'].' | W:'.$boss_info['robot_weapons'].' | A:'.$boss_info['robot_attack'].' | D:'.$boss_info['robot_defense'].' | S:'.$boss_info['robot_speed'].']]';
        if ($boss_flag_has_skill){ $boss_title_text .= '|| [[Passive Skill : '.$boss_skill_info['skill_name'].']] '; }
        $boss_image_path = 'images/robots/'.$boss_image_token.'/mug_right_'.$boss_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $boss_stat_max = $boss_info['robot_energy'] + $boss_info['robot_attack'] + $boss_info['robot_defense'] + $boss_info['robot_speed'];
        if ($boss_stat_max > $mmrpg_stat_base_max_value['boss']){ $mmrpg_stat_base_max_value['boss'] = $boss_stat_max; }

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $boss_title_text ?>" data-token="<?= $boss_info['robot_token'] ?>" class="float left link type <?=
            ($boss_core_class.' ').
            ($boss_image_incomplete  ? 'inactive ' : '').
            ($boss_flag_fightable ? 'fightable ' : '').
            ($boss_flag_has_skill ? 'has-skill ' : '')
            ?>">
            <a class="sprite robot link mugshot size<?= $boss_image_size.($boss_key == $first_boss_key ? ' current' : '') ?>" href="<?= 'database/bosses/'.$boss_info['robot_token']?>/" rel="<?= $boss_image_incomplete ? 'nofollow' : 'follow' ?>">
                <?php if($boss_image_token != 'boss'): ?>
                    <img src="<?= $boss_image_path ?>" width="<?= $boss_image_size ?>" height="<?= $boss_image_size ?>" alt="<?= $boss_title_text ?>" />
                <?php else: ?>
                    <span><?= $boss_info['robot_name'].$boss_info['robot_name_append'] ?></span>
                <?php endif; ?>
            </a>
            <?= $boss_flag_has_skill ? '<i class="skill type '.$boss_skill_display_type.'"></i>' : '' ?>
        </div>
        <?php
        if ($boss_flag_complete){ $mmrpg_database_bosses_count_complete++; }
        if ($boss_flag_fightable){ $mmrpg_database_bosses_count_fightable++; }
        $temp_markup = ob_get_clean();
        $mmrpg_database_bosses_links_index[$boss_key] = $temp_markup;
        if ($show_in_link_list){ $mmrpg_database_bosses_links .= $temp_markup; }
        $mmrpg_database_bosses_links_counter++;
        $key_counter++;

    }
}

// End the groups, however many there were
$mmrpg_database_bosses_links .= '</div>';

?>