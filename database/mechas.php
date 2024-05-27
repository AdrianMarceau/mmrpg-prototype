<?

// SUPPORT MECHA DATABASE

// Define the index of counters for mecha types
$mmrpg_database_mechas_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
    $mmrpg_database_mechas_types['cores'][$token] = 0;
    $mmrpg_database_mechas_types['weaknesses'][$token] = 0;
    $mmrpg_database_mechas_types['resistances'][$token] = 0;
    $mmrpg_database_mechas_types['affinities'][$token] = 0;
    $mmrpg_database_mechas_types['immunities'][$token] = 0;
}

// Define the index of hidden mechas to not appear in the database
$hidden_database_mechas = array();
$hidden_database_mechas_count = !empty($hidden_database_mechas) ? count($hidden_database_mechas) : 0;

// Collect the robot database files from the cache or manually
$cache_token = md5('database/mechas/website');
$cached_index = rpg_object::load_cached_index('database.mechas', $cache_token);
if (!empty($cached_index)){

    // Collect the cached data for mechas, robot count, and robot numbers
    $mmrpg_database_mechas = $cached_index['mmrpg_database_mechas'];
    $mmrpg_database_mechas_count = $cached_index['mmrpg_database_mechas_count'];
    $mmrpg_database_mechas_numbers = $cached_index['mmrpg_database_mechas_numbers'];
    unset($cached_index);

} else {

    // Collect the database fields
    $mmrpg_database_skills = rpg_skill::get_index(true);
    $mmrpg_database_fields = rpg_field::get_index(true, false);

    // Collect the database mecha
    $mecha_fields = rpg_robot::get_index_fields(true, 'robots');
    $mmrpg_database_mechas = $db->get_array_list("SELECT
        {$mecha_fields},
        groups.group_token AS robot_group,
        tokens.token_order AS robot_order
        FROM mmrpg_index_robots AS robots
        LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
        LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
        LEFT JOIN mmrpg_index_fields AS fields ON fields.field_token = robots.robot_field
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'mecha'
        AND robots.robot_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'robot_token');

    // Count the database mecha in total (without filters)
    $mmrpg_database_mechas_count = $db->get_value("SELECT
        COUNT(robots.robot_id) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'mecha'
        AND robots.robot_flag_published = 1
        AND robots.robot_flag_hidden = 0
        ;", 'robot_count');

    // Select an ordered list of all mechas and then assign row numbers to them
    $mmrpg_database_mechas_numbers = $db->get_array_list("SELECT
        robots.robot_token,
        0 AS robot_key
        FROM mmrpg_index_robots AS robots
        LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
        LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
        WHERE robots.robot_token <> 'robot'
        AND robots.robot_class = 'mecha'
        AND robots.robot_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'robot_token');
    $mecha_key = 1;
    foreach ($mmrpg_database_mechas_numbers AS $token => $info){
        $mmrpg_database_mechas_numbers[$token]['robot_key'] = $mecha_key++;
    }

    // Remove unallowed mechas from the database, and increment type counters
    if (!empty($mmrpg_database_mechas)){
        foreach ($mmrpg_database_mechas AS $temp_token => $temp_info){

            // Send this data through the mecha index parser
            $temp_info = rpg_robot::parse_index_info($temp_info);

            // Collect this mecha's key in the index
            $temp_info['robot_key'] = $mmrpg_database_mechas_numbers[$temp_token]['robot_key'];

            // Ensure this mecha's image exists, else default to the placeholder
            if ($temp_info['robot_flag_complete']){ $temp_info['robot_image'] = $temp_token; }
            else { $temp_info['robot_image'] = 'mecha'; }

            // Modify the name of this mecha if it is of the mecha class
            if ($temp_info['robot_class'] == 'mecha'){
                // Collect this mecha's field token, then mecha master token, then mecha master number
                $temp_field_token = !is_string($temp_info['robot_field']) ? array_shift($temp_info['robot_field']) : $temp_info['robot_field'];
                //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
                $temp_field_info = !empty($mmrpg_database_fields[$temp_field_token]) ? rpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]) : array();
                //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
                $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
                $temp_master_number = !empty($mmrpg_database_robots[$temp_master_token]) ? $mmrpg_database_robots[$temp_master_token]['robot_number'] : $temp_info['robot_number'];
                $temp_info['robot_master_number'] = $temp_master_number;
            }


            // Increment the mecha core counter if not empty
            if (!empty($temp_info['robot_core'])){
                if (!isset($mmrpg_database_mechas_types['cores'][$temp_info['robot_core']])){ $mmrpg_database_mechas_types['cores'][$temp_info['robot_core']] = 0; }
                $mmrpg_database_mechas_types['cores'][$temp_info['robot_core']]++;
            }
            else {
                $mmrpg_database_mechas_types['cores']['none']++;
            }
            if (!empty($temp_info['robot_core2'])){ $mmrpg_database_mechas_types['cores'][$temp_info['robot_core2']]++; }
            //else { $mmrpg_database_mechas_types['cores']['none']++; }

            // Loop through the mecha weaknesses if there are any to loop through
            if (!empty($temp_info['robot_weaknesses'])){
                foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_mechas_types['weaknesses'][$weakness]++; }
            } else {
                $mmrpg_database_mechas_types['weaknesses']['none']++;
            }
            // Loop through the mecha resistances if there are any to loop through
            if (!empty($temp_info['robot_resistances'])){
                foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_mechas_types['resistances'][$weakness]++; }
            } else {
                $mmrpg_database_mechas_types['resistances']['none']++;
            }
            // Loop through the mecha affinities if there are any to loop through
            if (!empty($temp_info['robot_affinities'])){
                foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_mechas_types['affinities'][$weakness]++; }
            } else {
                $mmrpg_database_mechas_types['affinities']['none']++;
            }
            // Loop through the mecha immunities if there are any to loop through
            if (!empty($temp_info['robot_immunities'])){
                foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_mechas_types['immunities'][$weakness]++; }
            } else {
                $mmrpg_database_mechas_types['immunities']['none']++;
            }

            // Update the main database array with the changes
            $mmrpg_database_mechas[$temp_token] = $temp_info;
        }
    }

    // Save the cached data for mechas, robot count, and robot numbers
    rpg_object::save_cached_index('database.mechas', $cache_token, array(
        'mmrpg_database_mechas' => $mmrpg_database_mechas,
        'mmrpg_database_mechas_count' => $mmrpg_database_mechas_count,
        'mmrpg_database_mechas_numbers' => $mmrpg_database_mechas_numbers
        ));
}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_mechas)
    && is_callable($filter_mmrpg_database_mechas)){
    $mmrpg_database_mechas = array_filter($mmrpg_database_mechas, $filter_mmrpg_database_mechas);
}

// Loop through and remove hidden mechas unless they're being viewed explicitly
if (!empty($mmrpg_database_mechas)){
    foreach ($mmrpg_database_mechas AS $temp_token => $temp_info){
        if (!empty($temp_info['robot_flag_hidden'])
            && $temp_info['robot_token'] !== $this_current_token
            && !defined('FORCE_INCLUDE_HIDDEN_MECHAS')){
            unset($mmrpg_database_mechas[$temp_token]);
        }
    }
}

// Define the max stat value before we filter and update
if (!isset($mmrpg_stat_base_max_value)){ $mmrpg_stat_base_max_value = array(); }
$mmrpg_stat_base_max_value['mecha'] = 0;

// Loop through the database and generate the links for these mechas
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_mechas_links = '';
$mmrpg_database_mechas_links_index = array();
$mmrpg_database_mechas_links_counter = 0;
$mmrpg_database_mechas_count_complete = 0;
$mmrpg_database_mechas_count_fightable = 0;

// Loop through the results and generate the links for these mechas
if (!empty($mmrpg_database_mechas)){
    foreach ($mmrpg_database_mechas AS $mecha_key => $mecha_info){
        if (!isset($first_mecha_key)){ $first_mecha_key = $mecha_key; }

        // Do not show hidden mechas in the link list
        if (!empty($mecha_info['robot_flag_hidden'])){ continue; }

        // Do not show incomplete mechas in the link list
        $show_in_link_list = true;
        if (!$mecha_info['robot_flag_complete'] && $mecha_info['robot_token'] !== $this_current_token){ $show_in_link_list = false; }

        // If a type filter has been applied to the robot page
        if (isset($this_current_filter) && $this_current_filter == 'none' && $mecha_info['robot_core'] != ''){ $key_counter++; continue; }
        elseif (isset($this_current_filter) && $this_current_filter != 'none' && $mecha_info['robot_core'] != $this_current_filter && $mecha_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

        // If this is the first in a new group
        $game_code = !empty($mecha_info['robot_group']) ? $mecha_info['robot_group'] : (!empty($mecha_info['robot_game']) ? $mecha_info['robot_game'] : 'MMRPG');
        if ($show_in_link_list && $game_code != $last_game_code){
            if (!empty($mmrpg_database_mechas_links)){ $mmrpg_database_mechas_links .= '</div>'; }
            $mmrpg_database_mechas_links .= '<div class="float link group" data-game="'.$game_code.'">';
            $last_game_code = $game_code;
        }

        // Check if this is a mecha and prepare extra text
        $mecha_info['robot_name_append'] = '';

        // Collect skill info for this mecha if applicable
        $mecha_flag_has_skill = !empty($mecha_info['robot_skill']) ? true : false;
        $mecha_skill_info = $mecha_flag_has_skill ? rpg_robot::get_robot_skill_info($mecha_info['robot_skill'], $mecha_info) : false;
        $mecha_skill_display_type = $mecha_flag_has_skill ? (!empty($mecha_skill_info['skill_display_type']) ? $mecha_skill_info['skill_display_type'] : 'none') : false;

        // Collect the mecha sprite dimensions
        $mecha_flag_complete = !empty($mecha_info['robot_flag_complete']) ? true : false;
        $mecha_flag_fightable = !empty($mecha_info['robot_flag_fightable']) ? true : false;
        $mecha_core_type = !empty($mecha_info['robot_core']) ? $mecha_info['robot_core'] : 'none';
        $mecha_core_type2 = !empty($mecha_info['robot_core']) && !empty($mecha_info['robot_core2']) ? $mecha_info['robot_core2'] : '';
        $mecha_core_class = $mecha_core_type.(!empty($mecha_core_type2) ? '_'.$mecha_core_type2 : '');
        $mecha_image_size = !empty($mecha_info['robot_image_size']) ? $mecha_info['robot_image_size'] : 40;
        $mecha_image_size_text = $mecha_image_size.'x'.$mecha_image_size;
        $mecha_image_token = !empty($mecha_info['robot_image']) ? $mecha_info['robot_image'] : $mecha_info['robot_token'];
        $mecha_image_incomplete = $mecha_image_token == 'mecha' ? true : false;
        $mecha_is_active = !empty($this_current_token) && $this_current_token == $mecha_info['robot_token'] ? true : false;
        $mecha_title_text = $mecha_info['robot_name'].$mecha_info['robot_name_append'].' | '.$mecha_info['robot_number'].' | '.(!empty($mecha_info['robot_core']) ? ucwords($mecha_info['robot_core'].(!empty($mecha_info['robot_core2']) ? ' / '.$mecha_info['robot_core2'] : '')) : 'Neutral').' Type';
        $mecha_title_text .= '|| [[E:'.$mecha_info['robot_energy'].' | W:'.$mecha_info['robot_weapons'].' | A:'.$mecha_info['robot_attack'].' | D:'.$mecha_info['robot_defense'].' | S:'.$mecha_info['robot_speed'].']]';
        $mecha_title_text .= '|| ' . $game_code;
        if ($mecha_flag_has_skill){ $mecha_title_text .= '|| [[Passive Skill : '.$mecha_skill_info['skill_name'].']] '; }
        $mecha_image_path = 'images/robots/'.$mecha_image_token.'/mug_right_'.$mecha_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $mecha_stat_max = $mecha_info['robot_energy'] + $mecha_info['robot_attack'] + $mecha_info['robot_defense'] + $mecha_info['robot_speed'];
        if ($mecha_stat_max > $mmrpg_stat_base_max_value['mecha']){ $mmrpg_stat_base_max_value['mecha'] = $mecha_stat_max; }

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $mecha_title_text ?>" data-token="<?= $mecha_info['robot_token'] ?>" class="float left link type <?=
            ($mecha_core_class.' ').
            ($mecha_image_incomplete  ? 'inactive ' : '').
            ($mecha_flag_fightable ? 'fightable ' : '').
            ($mecha_flag_has_skill ? 'has-skill ' : '')
            ?>">
            <a class="sprite robot link mugshot size<?= $mecha_image_size.($mecha_key == $first_mecha_key ? ' current' : '') ?>" href="<?= 'database/mechas/'.$mecha_info['robot_token']?>/" rel="<?= $mecha_image_incomplete ? 'nofollow' : 'follow' ?>">
                <?php if($mecha_image_token != 'mecha'): ?>
                    <img src="<?= $mecha_image_path ?>" width="<?= $mecha_image_size ?>" height="<?= $mecha_image_size ?>" alt="<?= $mecha_title_text ?>" />
                <?php else: ?>
                    <span><?= $mecha_info['robot_name'].$mecha_info['robot_name_append'] ?></span>
                <?php endif; ?>
            </a>
            <?= $mecha_flag_has_skill ? '<i class="skill type '.$mecha_skill_display_type.'"></i>' : '' ?>
        </div>
        <?php
        if ($mecha_flag_complete){ $mmrpg_database_mechas_count_complete++; }
        if ($mecha_flag_fightable){ $mmrpg_database_mechas_count_fightable++; }
        $temp_markup = ob_get_clean();
        $mmrpg_database_mechas_links_index[$mecha_key] = $temp_markup;
        if ($show_in_link_list){ $mmrpg_database_mechas_links .= $temp_markup; }
        $mmrpg_database_mechas_links_counter++;
        $key_counter++;

    }
}

// End the groups, however many there were
$mmrpg_database_mechas_links .= '</div>';

?>