<?

// ABILITY DATABASE

// Define the index of counters for ability types
$mmrpg_database_abilities_types = array();
foreach ($mmrpg_database_types AS $token => $info){
    $mmrpg_database_abilities_types[$token] = 0;
}
$mmrpg_database_abilities_types['empty'] = 0;
//error_log('$mmrpg_database_types = '.print_r($mmrpg_database_types, true));
//error_log('$mmrpg_database_abilities_types = '.print_r($mmrpg_database_abilities_types, true));

// Define the index of hidden abilities to not appear in the database
$hidden_database_abilities = array();
$hidden_database_abilities_count = !empty($hidden_database_abilities) ? count($hidden_database_abilities) : 0;

// Collect the ability database files from the cache or manually
$cache_token = md5('database/abilities/website');
$cached_index = rpg_object::load_cached_index('database.abilities', $cache_token);
if (!empty($cached_index)){

    // Collect the cached data for abilities, ability count, and ability numbers
    $mmrpg_database_abilities = $cached_index['mmrpg_database_abilities'];
    $mmrpg_database_abilities_count = $cached_index['mmrpg_database_abilities_count'];
    $mmrpg_database_abilities_numbers = $cached_index['mmrpg_database_abilities_numbers'];
    unset($cached_index);

} else {

    // Collect the database abilities
    $ability_fields = rpg_ability::get_index_fields(true, 'abilities');
    $mmrpg_database_abilities = $db->get_array_list("SELECT
        {$ability_fields},
        groups.group_token AS ability_group,
        tokens.token_order AS ability_order
        FROM mmrpg_index_abilities AS abilities
        LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
        LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = abilities.ability_class
        WHERE abilities.ability_token <> 'ability'
        AND abilities.ability_class <> 'system'
        AND abilities.ability_class = 'master'
        AND abilities.ability_flag_published = 1
        ORDER BY
        FIELD(abilities.ability_class, 'master', 'mecha', 'boss'),
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'ability_token');

    // Count the database abilities in total (without filters)
    $mmrpg_database_abilities_count = $db->get_value("SELECT
        COUNT(abilities.ability_id) AS ability_count
        FROM mmrpg_index_abilities AS abilities
        WHERE abilities.ability_token <> 'ability'
        AND abilities.ability_class <> 'system'
        AND abilities.ability_class = 'master'
        AND abilities.ability_flag_published = 1
        AND abilities.ability_flag_hidden = 0
        ;", 'ability_count');

    // Select an ordered list of all abilities and then assign row numbers to them
    $mmrpg_database_abilities_numbers = $db->get_array_list("SELECT
        abilities.ability_token,
        0 AS ability_key
        FROM mmrpg_index_abilities AS abilities
        LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
        LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = abilities.ability_class
        WHERE abilities.ability_token <> 'ability'
        AND abilities.ability_class <> 'system'
        AND abilities.ability_flag_published = 1
        ORDER BY
        FIELD(abilities.ability_class, 'master', 'mecha', 'boss'),
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'ability_token');
    $ability_key = 1;
    foreach ($mmrpg_database_abilities_numbers AS $token => $info){
        $mmrpg_database_abilities_numbers[$token]['ability_key'] = $ability_key++;
    }

    // Remove unallowed abilities from the database, and increment counters
    if (!empty($mmrpg_database_abilities)){
        foreach ($mmrpg_database_abilities AS $temp_token => $temp_info){

            // Send this data through the ability index parser
            $temp_info = rpg_ability::parse_index_info($temp_info);

            // Collect this ability's key in the index
            $temp_info['ability_key'] = $mmrpg_database_abilities_numbers[$temp_token]['ability_key'];

            // Ensure this ability's image exists, else default to the placeholder
            $temp_image_token = isset($temp_info['ability_image']) ? $temp_info['ability_image'] : $temp_token;
            if ($temp_info['ability_flag_complete']){ $temp_info['ability_image'] = $temp_image_token; }
            else { $temp_info['ability_image'] = 'ability'; }
            $temp_info['ability_speed'] = isset($temp_info['ability_speed']) ? (int)($temp_info['ability_speed']) : 1;
            $temp_info['ability_energy'] = isset($temp_info['ability_energy']) ? (int)($temp_info['ability_energy']) : 0;

            // Increment the corresponding type counter for this ability else the empty counter
            if (!empty($temp_info['ability_type'])){
                if (!isset($mmrpg_database_abilities_types[$temp_info['ability_type']])){ $mmrpg_database_abilities_types[$temp_info['ability_type']] = 0; }
                $mmrpg_database_abilities_types[$temp_info['ability_type']]++;

                // Increment the corresponding type2 counter for this ability if not empty
                if (!empty($temp_info['ability_type2'])){
                    if (!isset($mmrpg_database_abilities_types[$temp_info['ability_type2']])){ $mmrpg_database_abilities_types[$temp_info['ability_type2']] = 0; }
                    $mmrpg_database_abilities_types[$temp_info['ability_type2']]++;
                }

            } else {
                $mmrpg_database_abilities_types['none']++;
            }

            // Update the main database array with the changes
            $mmrpg_database_abilities[$temp_token] = $temp_info;

        }
    }

    // Save the cached data for abilities, ability count, and ability numbers
    rpg_object::save_cached_index('database.abilities', $cache_token, array(
        'mmrpg_database_abilities' => $mmrpg_database_abilities,
        'mmrpg_database_abilities_count' => $mmrpg_database_abilities_count,
        'mmrpg_database_abilities_numbers' => $mmrpg_database_abilities_numbers
        ));
}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_abilities)
    && is_callable($filter_mmrpg_database_abilities)){
    $mmrpg_database_abilities = array_filter($mmrpg_database_abilities, $filter_mmrpg_database_abilities);
}

// If an update function gas been provided for this context, run it now
if (isset($update_mmrpg_database_abilities)
    && is_callable($update_mmrpg_database_abilities)){
    $mmrpg_database_abilities = array_map($update_mmrpg_database_abilities, $mmrpg_database_abilities);
}

// Loop through and remove hidden abilities unless they're being viewed explicitly
if (!empty($mmrpg_database_abilities)){
    foreach ($mmrpg_database_abilities AS $temp_token => $temp_info){
        if (!empty($temp_info['ability_flag_hidden'])
            && $temp_info['ability_token'] !== $this_current_token
            && !defined('FORCE_INCLUDE_HIDDEN_ABILITIES')){
            unset($mmrpg_database_abilities[$temp_token]);
        } elseif (!defined('DATA_DATABASE_SHOW_MECHAS')
            && $temp_info['ability_class'] === 'mecha'){
            unset($mmrpg_database_abilities[$temp_token]);
        } elseif (!defined('DATA_DATABASE_SHOW_BOSSES')
            && $temp_info['ability_class'] === 'boss'){
            unset($mmrpg_database_abilities[$temp_token]);
        }
    }
}

// Define database variables we'll be using to generate links
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_abilities_links = '';
$mmrpg_database_abilities_links_index = array();
$mmrpg_database_abilities_links_counter = 0;
$mmrpg_database_abilities_count_complete = 0;

// Loop through the results and generate the links for these abilities
if (!empty($mmrpg_database_abilities)){
    foreach ($mmrpg_database_abilities AS $ability_key => $ability_info){
        if (!isset($first_ability_key)){ $first_ability_key = $ability_key; }

        // Do not show incomplete abilities in the link list
        $show_in_link_list = true;
        if (!$ability_info['ability_flag_complete'] && $ability_info['ability_token'] !== $this_current_token){ $show_in_link_list = false; }

        // If a type filter has been applied to the ability page
        $temp_ability_types = array();
        if (!empty($ability_info['ability_type'])){ $temp_ability_types[] = $ability_info['ability_type']; }
        if (!empty($ability_info['ability_type2'])){ $temp_ability_types[] = $ability_info['ability_type2']; }
        if (empty($temp_ability_types)){ $temp_ability_types[] = 'none'; }
        if (isset($this_current_filter) && !in_array($this_current_filter, $temp_ability_types)){ $key_counter++; continue; }

        // If this is the first in a new group
        $game_code = !empty($ability_info['ability_group']) ? $ability_info['ability_group'] : (!empty($ability_info['ability_game']) ? $ability_info['ability_game'] : 'MMRPG');
        if (empty($this_current_sub) && preg_match('/^(mega|proto|bass)-/i', $ability_info['ability_token'])){
            $game_code = 'HERO/Weapons/T0';
        }
        if ($show_in_link_list && $game_code != $last_game_code){
            if (!empty($mmrpg_database_abilities_links)){ $mmrpg_database_abilities_links .= '</div>'; }
            $mmrpg_database_abilities_links .= '<div class="float_link float_link_group" data-game="'.$game_code.'">';
            $last_game_code = $game_code;
        }

        // Collect the ability sprite dimensions
        $ability_flag_complete = !empty($ability_info['ability_flag_complete']) ? true : false;
        $ability_image_size = !empty($ability_info['ability_image_size']) ? $ability_info['ability_image_size'] : 40;
        $ability_image_size_text = $ability_image_size.'x'.$ability_image_size;
        $ability_image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
        $ability_image_incomplete = $ability_image_token == 'ability' ? true : false;
        $ability_is_active = !empty($this_current_token) && $this_current_token == $ability_info['ability_token'] ? true : false;
        $ability_title_text = $ability_info['ability_name'].(!empty($ability_info['ability_type']) ? ' | '.ucfirst($ability_info['ability_type']).' Type' : ' | Neutral Type'); //.' | '.$ability_info['ability_game'].' | '.$ability_info['ability_group'];
        if (!empty($ability_info['ability_type2'])){ $ability_title_text = str_replace('Type', '/ '.ucfirst($ability_info['ability_type2']).' Type', $ability_title_text); }
        $ability_title_text .= '|| [[';
        if (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $ability_title_text .= 'Special Effects'; }
        elseif (!empty($ability_info['ability_damage'])){ $ability_title_text .= $ability_info['ability_damage'].(!empty($ability_info['ability_damage_percent']) ? '%' : '').' Damage'; }
        elseif (!empty($ability_info['ability_recovery'])){ $ability_title_text .= $ability_info['ability_recovery'].(!empty($ability_info['ability_recovery_percent']) ? '%' : '').' Recovery'; }
        if (!empty($ability_info['ability_accuracy'])){ $ability_title_text .= ' | '.$ability_info['ability_accuracy'].'% Accuracy'; }
        if (isset($ability_info['ability_energy'])){ $ability_title_text .= ' | '.$ability_info['ability_energy'].(!empty($ability_info['ability_energy_percent']) ? '%' : '').' Energy'; }
        $ability_title_text .= ']]';
        $ability_image_path = 'images/abilities/'.$ability_image_token.'/icon_right_'.$ability_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $ability_title_text ?>" data-token="<?= $ability_info['ability_token'] ?>" class="float left link type <?= ($ability_image_incomplete ? 'inactive ' : '').(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '') ?>">
            <a class="sprite ability link mugshot size<?= $ability_image_size.($ability_key == $first_ability_key ? ' current' : '') ?>" href="<?= 'database/abilities/'.$ability_info['ability_token']?>/" rel="<?= $ability_image_incomplete ? 'nofollow' : 'follow' ?>">
                <?php if($ability_image_token != 'ability'): ?>
                    <img src="<?= $ability_image_path ?>" width="<?= $ability_image_size ?>" height="<?= $ability_image_size ?>" alt="<?= $ability_title_text ?>" />
                <?php else: ?>
                    <span><?= $ability_info['ability_name'] ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php
        if ($ability_flag_complete){ $mmrpg_database_abilities_count_complete++; }
        $temp_markup = ob_get_clean();
        $mmrpg_database_abilities_links_index[$ability_key] = $temp_markup;
        if ($show_in_link_list){ $mmrpg_database_abilities_links .= $temp_markup; }
        $mmrpg_database_abilities_links_counter++;
        $key_counter++;

    }
}

// End the groups, however many there were
$mmrpg_database_abilities_links .= '</div>';

?>