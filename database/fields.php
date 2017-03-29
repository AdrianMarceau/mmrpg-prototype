<?

// FIELD DATABASE

// Define the index of hidden fields to not appear in the database
$hidden_database_fields = array();
$hidden_database_fields_count = !empty($hidden_database_fields) ? count($hidden_database_fields) : 0;

// Define the hidden field query condition
$temp_condition = '';
$temp_condition .= "AND fields.field_class <> 'system' ";
if (!empty($hidden_database_fields)){
    $temp_tokens = array();
    foreach ($hidden_database_fields AS $token){ $temp_tokens[] = "'".$token."'"; }
    $temp_condition .= 'AND fields.field_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_fields_filter)){
    if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_fields_filter)){ $temp_condition .= 'AND ';  }
    $temp_condition .= $mmrpg_database_fields_filter;
}

// If we're specifically on the fields page, collect records
$temp_joins = '';
$temp_field_fields = '';
if ($this_current_sub == 'fields'){
    //$temp_field_fields .= "";
    //$temp_joins .= "";
}

// Collect the relevant index fields
$field_fields = rpg_field::get_index_fields(true, 'fields');

// Define the query for the global fields index
$temp_fields_index_query = "SELECT
    {$field_fields}
    {$temp_field_fields}
    FROM mmrpg_index_fields AS fields
    {$temp_joins}
    WHERE
    fields.field_flag_published = 1
    AND (fields.field_flag_hidden = 0 OR fields.field_token = '{$this_current_token}')
    {$temp_condition}
    ORDER BY
    fields.field_flag_hidden ASC,
    fields.field_order ASC
    ;";

// Define the query for the global fields count
$temp_fields_count_query = "SELECT
    COUNT(fields.field_id) AS field_count
    FROM mmrpg_index_fields AS fields
    WHERE
    fields.field_flag_published = 1
    AND fields.field_flag_hidden = 0
    {$temp_condition_unfiltered}
    ;";

// Define the query for the global field numbers
$temp_fields_numbers_query = "SELECT
    fields.field_token,
    (@field_row_number:=@field_row_number + 1) AS field_key
    FROM mmrpg_index_fields AS fields
    WHERE
    fields.field_flag_published = 1
    {$temp_condition_unfiltered}
    ORDER BY
    fields.field_flag_hidden ASC,
    fields.field_order ASC
    ;";

// Execute generated queries and collect return value
$db->query("SET @field_row_number = 0;");
$mmrpg_database_fields = $db->get_array_list($temp_fields_index_query, 'field_token');
$mmrpg_database_fields_count = $db->get_value($temp_fields_count_query, 'field_count');
$mmrpg_database_fields_numbers = $db->get_array_list($temp_fields_numbers_query, 'field_token');

// DEBUG
//echo('<pre>$temp_fields_index_query = '.print_r($temp_fields_index_query, true).'</pre>');
//echo('<pre>$temp_fields_count_query = '.print_r($temp_fields_count_query, true).'</pre>');
//echo('<pre>$temp_fields_numbers_query = '.print_r($temp_fields_numbers_query, true).'</pre>');
//echo('<pre>$mmrpg_database_fields = '.print_r($mmrpg_database_fields, true).'</pre>');
//echo('<pre>$mmrpg_database_fields_count = '.print_r($mmrpg_database_fields_count, true).'</pre>');
//echo('<pre>$mmrpg_database_fields_numbers = '.print_r($mmrpg_database_fields_numbers, true).'</pre>');
//exit();

// Remove unallowed fields from the database
foreach ($mmrpg_database_fields AS $temp_token => $temp_info){

    // Define first field token if not set
    if (!isset($first_field_token)){ $first_field_token = $temp_token; }

    // Send this data through the field index parser
    $temp_info = rpg_field::parse_index_info($temp_info);

    // Collect this field's key in the index
    $temp_info['field_key'] = $mmrpg_database_fields_numbers[$temp_token]['field_key'];

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

// Loop through the database and generate the links for these fields
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_fields_links = '';
$mmrpg_database_fields_links_index = array();
$mmrpg_database_fields_links_counter = 0;
$mmrpg_database_fields_count_complete = 0;

// Loop through the results and generate the links for these fields
foreach ($mmrpg_database_fields AS $field_key => $field_info){

    // If a type filter has been applied to the field page
    $temp_field_types = array();
    if (!empty($field_info['field_type'])){ $temp_field_types[] = $field_info['field_type']; }
    if (!empty($field_info['field_type2'])){ $temp_field_types[] = $field_info['field_type2']; }
    if (empty($temp_field_types)){ $temp_field_types[] = 'none'; }
    if (isset($this_current_filter) && !in_array($this_current_filter, $temp_field_types)){ $key_counter++; continue; }

    // If this is the first in a new group
    $game_code = !empty($field_info['field_group']) ? $field_info['field_group'] : (!empty($field_info['field_game']) ? $field_info['field_game'] : 'MMRPG');
    if ($game_code != $last_game_code){
        if ($key_counter != 0){ $mmrpg_database_fields_links .= '</div>'; }
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
        <a class="sprite field link mugshot size40 <?= ($field_key == $first_field_token ? ' current' : '') ?>" href="<?= 'database/fields/'.$field_info['field_token'].'/'?>" rel="<?= $field_image_incomplete ? 'nofollow' : 'follow' ?>">
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
    $mmrpg_database_fields_links .= $temp_markup;
    $mmrpg_database_fields_links_counter++;
    $key_counter++;

}

// End the groups, however many there were
$mmrpg_database_fields_links .= '</div>';

?>