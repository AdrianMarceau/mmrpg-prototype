<?

// ABILITY DATABASE

// Define the index of counters for robot types
$mmrpg_database_abilities_types = array();
foreach ($mmrpg_database_types AS $token => $info){
    $mmrpg_database_abilities_types[$token] = 0;
}

// Define the index of hidden abilities to not appear in the database
$hidden_database_abilities = array();
$hidden_database_abilities_count = !empty($hidden_database_abilities) ? count($hidden_database_abilities) : 0;

// Define the hidden ability query condition
$temp_condition = '';
$temp_condition .= "AND abilities.ability_class <> 'system' ";
if (!defined('DATA_DATABASE_SHOW_MECHAS')){
    $temp_condition .= "AND abilities.ability_class <> 'mecha' ";
}
if (!defined('DATA_DATABASE_SHOW_BOSSES')){
    $temp_condition .= "AND abilities.ability_class <> 'boss' ";
}
if (!empty($hidden_database_abilities)){
    $temp_tokens = array();
    foreach ($hidden_database_abilities AS $token){ $temp_tokens[] = "'".$token."'"; }
    $temp_condition .= 'AND abilities.ability_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_abilities_filter)){
    if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_abilities_filter)){ $temp_condition .= 'AND ';  }
    $temp_condition .= $mmrpg_database_abilities_filter;
}

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
    AND abilities.ability_flag_published = 1 AND (abilities.ability_flag_hidden = 0 OR abilities.ability_token = '{$this_current_token}')  {$temp_condition}
    ORDER BY
    FIELD(abilities.ability_class, 'master', 'mecha', 'boss'),
    groups.group_order ASC,
    tokens.token_order ASC
    ;", 'ability_token');

// Count the database abilities in total (without filters)
$mmrpg_database_abilities_count = $db->get_value("SELECT
    COUNT(abilities.ability_id) AS ability_count
    FROM mmrpg_index_abilities AS abilities
    WHERE abilities.ability_flag_published = 1 AND abilities.ability_flag_hidden = 0 {$temp_condition_unfiltered}
    ;", 'ability_count');

// Select an ordered list of all abilities and then assign row numbers to them
$mmrpg_database_abilities_numbers = $db->get_array_list("SELECT
    abilities.ability_token,
    0 AS ability_key
    FROM mmrpg_index_abilities AS abilities
    LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
    LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = abilities.ability_class
    WHERE abilities.ability_token <> 'ability'
    AND abilities.ability_flag_published = 1 AND (abilities.ability_flag_hidden = 0 OR abilities.ability_token = '{$this_current_token}')  {$temp_condition}
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

        // Define first ability token if not set
        if (!isset($first_ability_token)){ $first_ability_token = $temp_token; }

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
            if ($key_counter != 0){ $mmrpg_database_abilities_links .= '</div>'; }
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
        if (false && !empty($ability_info['ability_description'])){
            $temp_description = $ability_info['ability_description'];
            $temp_description = str_replace('{DAMAGE}', $ability_info['ability_damage'], $temp_description);
            $temp_description = str_replace('{DAMAGE2}', $ability_info['ability_damage2'], $temp_description);
            $temp_description = str_replace('{RECOVERY}', $ability_info['ability_recovery'], $temp_description);
            $temp_description = str_replace('{RECOVERY2}', $ability_info['ability_recovery2'], $temp_description);
            $ability_title_text .= '|| [['.$temp_description.']]';
        }
        $ability_image_path = 'images/abilities/'.$ability_image_token.'/icon_right_'.$ability_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $ability_title_text ?>" data-token="<?= $ability_info['ability_token'] ?>" class="float left link type <?= ($ability_image_incomplete ? 'inactive ' : '').(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '') ?>">
            <a class="sprite ability link mugshot size<?= $ability_image_size.($ability_key == $first_ability_token ? ' current' : '') ?>" href="<?= 'database/abilities/'.$ability_info['ability_token']?>/" rel="<?= $ability_image_incomplete ? 'nofollow' : 'follow' ?>">
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