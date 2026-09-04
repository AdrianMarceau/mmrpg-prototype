<?

// FIELD DATABASE

// Define the index of hidden fields to not appear in the database
$hidden_database_fields = array();
$hidden_database_fields_count = !empty($hidden_database_fields) ? count($hidden_database_fields) : 0;

// Collect the field database files from the cache or manually
$cache_token = md5('database/fields/website');
$cached_index = rpg_object::load_cached_index('database.fields', $cache_token);
if (!empty($cached_index) && empty($_GET['refresh'])){

    // Collect the cached data for fields, field count, and field numbers
    $mmrpg_database_fields = $cached_index['mmrpg_database_fields'];
    $mmrpg_database_fields_count = $cached_index['mmrpg_database_fields_count'];
    $mmrpg_database_fields_numbers = $cached_index['mmrpg_database_fields_numbers'];
    unset($cached_index);

} else {

    // Collect the database fields
    $field_where = "fields.field_token <> 'field' AND fields.field_class <> 'system' AND fields.field_flag_published = 1 ";
    if (defined('FORCE_INCLUDE_TEMPLATE_FIELD')){ $field_where = "({$field_where}) OR fields.field_token = 'field' "; }
    $field_fields = rpg_field::get_index_fields(true, 'fields');
    $mmrpg_database_fields = $db->get_array_list("SELECT
        {$field_fields},
        groups.group_token AS field_group,
        tokens.token_order AS field_order
        FROM mmrpg_index_fields AS fields
        LEFT JOIN mmrpg_index_fields_groups_tokens AS tokens ON tokens.field_token = fields.field_token
        LEFT JOIN mmrpg_index_fields_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = 'field'
        WHERE {$field_where}
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'field_token');

    // Count the database fields in total (without filters)
    $mmrpg_database_fields_count = $db->get_value("SELECT
        COUNT(fields.field_id) AS field_count
        FROM mmrpg_index_fields AS fields
        WHERE fields.field_token <> 'field'
        AND fields.field_class <> 'system'
        AND fields.field_flag_published = 1
        AND fields.field_flag_hidden = 0
        ;", 'field_count');

    // Select an ordered list of all fields and then assign row numbers to them
    $mmrpg_database_fields_numbers = $db->get_array_list("SELECT
        fields.field_token,
        0 AS field_key
        FROM mmrpg_index_fields AS fields
        LEFT JOIN mmrpg_index_fields_groups_tokens AS tokens ON tokens.field_token = fields.field_token
        LEFT JOIN mmrpg_index_fields_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = 'field'
        WHERE fields.field_token <> 'field'
        AND fields.field_class <> 'system'
        AND fields.field_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'field_token');
    $field_key = 1;
    foreach ($mmrpg_database_fields_numbers AS $token => $info){
        $mmrpg_database_fields_numbers[$token]['field_key'] = $field_key++;
    }

    // Remove unallowed fields from the database
    if (!empty($mmrpg_database_fields)){
        foreach ($mmrpg_database_fields AS $temp_token => $temp_info){

            // Send this data through the field index parser
            $temp_info = rpg_field::parse_index_info($temp_info);

            // Collect this field's key in the index
            if (!isset($mmrpg_database_fields_numbers[$temp_token])){ $temp_info['field_key'] = -1; }
            else { $temp_info['field_key'] = $mmrpg_database_fields_numbers[$temp_token]['field_key']; }

            if (in_array($temp_token, $hidden_database_fields)){
                unset($mmrpg_database_fields[$temp_token]);
            } else {
                // Ensure this field's image exists, else default to the placeholder
                if ($temp_info['field_flag_complete']){ $temp_info['field_image'] = $temp_token; }
                else { $temp_info['field_image'] = 'field'; }
            }

            // Update the data in the fields index array
            $mmrpg_database_fields[$temp_token] = $temp_info;

        }
    }

    // Save the cached data for fields, field count, and field numbers
    rpg_object::save_cached_index('database.fields', $cache_token, array(
        'mmrpg_database_fields' => $mmrpg_database_fields,
        'mmrpg_database_fields_count' => $mmrpg_database_fields_count,
        'mmrpg_database_fields_numbers' => $mmrpg_database_fields_numbers
        ));
}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_fields)
    && is_callable($filter_mmrpg_database_fields)){
    $mmrpg_database_fields = array_filter($mmrpg_database_fields, $filter_mmrpg_database_fields);
}

// If an update function gas been provided for this context, run it now
if (isset($update_mmrpg_database_fields)
    && is_callable($update_mmrpg_database_fields)){
    $mmrpg_database_fields = array_map($update_mmrpg_database_fields, $mmrpg_database_fields);
}

// Loop through and remove hidden fields unless they're being viewed explicitly
if (!empty($mmrpg_database_fields)){
    foreach ($mmrpg_database_fields AS $temp_token => $temp_info){
        if (!empty($temp_info['field_flag_hidden'])
            && $temp_info['field_token'] !== $this_current_token
            && !defined('FORCE_INCLUDE_HIDDEN_FIELDS')){
            unset($mmrpg_database_fields[$temp_token]);
        }
    }
}

// Loop through the database and generate the links for these fields
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_fields_links = '';
$mmrpg_database_fields_links_index = array();
$mmrpg_database_fields_links_counter = 0;
$mmrpg_database_fields_count_complete = 0;

// Loop through the results and generate the links for these fields
if (!empty($mmrpg_database_fields)){
    foreach ($mmrpg_database_fields AS $field_key => $field_info){
        if (!isset($first_field_key)){ $first_field_key = $field_key; }

        // Do not show incomplete fields in the link list
        $show_in_link_list = true;
        if (!$field_info['field_flag_complete'] && $field_info['field_token'] !== $this_current_token){ $show_in_link_list = false; }

        // If a type filter has been applied to the field page
        $temp_field_types = array();
        if (!empty($field_info['field_type'])){ $temp_field_types[] = $field_info['field_type']; }
        if (!empty($field_info['field_type2'])){ $temp_field_types[] = $field_info['field_type2']; }
        if (empty($temp_field_types)){ $temp_field_types[] = 'none'; }
        if (isset($this_current_filter) && !in_array($this_current_filter, $temp_field_types)){ $key_counter++; continue; }

        // If this is the first in a new group
        $game_code = !empty($field_info['field_group']) ? $field_info['field_group'] : (!empty($field_info['field_game']) ? $field_info['field_game'] : 'MMRPG');
        if ($show_in_link_list && $game_code != $last_game_code){
            if (!empty($mmrpg_database_fields_links)){ $mmrpg_database_fields_links .= '</div>'; }
            $mmrpg_database_fields_links .= '<div class="float link group" data-game="'.$game_code.'">';
            $last_game_code = $game_code;
        }

        // Collect the field sprite dimensions
        $field_flag_complete = !empty($field_info['field_flag_complete']) ? true : false;
        $field_image_size = 50;
        $field_image_token = !empty($field_info['field_image']) ? $field_info['field_image'] : $field_info['field_token'];
        $field_image_incomplete = $field_image_token == 'field' ? true : false;
        $field_is_active = !empty($this_current_token) && $this_current_token == $field_info['field_token'] ? true : false;
        $field_title_text = $field_info['field_name'].(!empty($temp_field_types) ? ' | '.str_replace('None', 'Neutral', ucwords(implode(' / ', $temp_field_types))).' Type' : ''); //.' | '.$field_info['field_game'];
        $field_image_path = 'images/fields/'.$field_image_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
        $field_type_token = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
        if (!empty($field_info['field_type2'])){ $field_type_token .= '_'.$field_info['field_type2']; }

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $field_title_text ?>" data-token="<?= $field_info['field_token'] ?>" class="float left link type <?= ($field_image_incomplete  ? 'inactive ' : '').($field_type_token) ?>">
            <a class="sprite field link mugshot size40 <?= ($field_key == $first_field_key ? ' current' : '') ?>" href="<?= 'database/fields/'.$field_info['field_token'].'/'?>" rel="<?= $field_image_incomplete ? 'nofollow' : 'follow' ?>">
                <?php if($field_image_token != 'field'): ?>
                    <img src="<?= $field_image_path ?>" width="<?= $field_image_size ?>" height="<?= $field_image_size ?>" alt="<?= $field_title_text ?>" />
                <?php else: ?>
                    <span><?= $field_info['field_name'] ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php
        if ($field_flag_complete){ $mmrpg_database_fields_count_complete++; }
        $temp_markup = ob_get_clean();
        $mmrpg_database_fields_links_index[$field_key] = $temp_markup;
        if ($show_in_link_list){ $mmrpg_database_fields_links .= $temp_markup; }
        $mmrpg_database_fields_links_counter++;
        $key_counter++;

    }
}

// End the groups, however many there were
$mmrpg_database_fields_links .= '</div>';

?>