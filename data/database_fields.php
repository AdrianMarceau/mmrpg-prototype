<?
// FIELD DATABASE

// Define the index of hidden fields to not appear in the database
$hidden_database_fields = array();
$hidden_database_fields = array_merge($hidden_database_fields, array('field', 'prototype-subspace')); //'prototype-complete'
$hidden_database_fields_count = !empty($hidden_database_fields) ? count($hidden_database_fields) : 0;

// Define the hidden field query condition
$temp_condition = '';
$temp_condition .= "AND field_class <> 'system' ";
if (!empty($hidden_database_fields)){
  $temp_tokens = array();
  foreach ($hidden_database_fields AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND field_token NOT IN ('.implode(',', $temp_tokens).') ';
}

// Collect the database fields
$mmrpg_database_fields = $DB->get_array_list("SELECT * FROM mmrpg_index_fields WHERE field_flag_published = 1 {$temp_condition} ORDER BY field_order ASC;", 'field_token');

// Remove unallowed fields from the database
foreach ($mmrpg_database_fields AS $temp_token => $temp_info){

  // Send this data through the field index parser
  $temp_info = mmrpg_field::parse_index_info($temp_info);

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

// Determine the token for the very first field in the database
$temp_field_tokens = array_values($mmrpg_database_fields);
$first_field_token = array_shift($temp_field_tokens);
$first_field_token = $first_field_token['field_token'];
unset($temp_field_tokens);

// Count the number of fields collected and filtered
$mmrpg_database_fields_count = count($mmrpg_database_fields);
$mmrpg_database_fields_count_complete = 0;

// Loop through the database and generate the links for these fields
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_fields_links = '';
$mmrpg_database_fields_links_counter = 0;
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
  //$field_image_path = 'images/fields/'.$field_image_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
  $field_image_path = 'i/f/'.$field_image_token.'/bfa.png?'.MMRPG_CONFIG_CACHE_DATE;
  $field_type_token = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
  if (!empty($field_info['field_type2'])){ $field_type_token .= '_'.$field_info['field_type2']; }
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $field_title_text ?>" data-token="<?= $field_info['field_token'] ?>" class="float left link type <?= ($field_image_incomplete  ? 'inactive ' : '').($field_type_token) ?>">
    <a class="sprite field link mugshot size40 <?= ($field_key == $first_field_token ? ' current' : '') ?>" href="<?='database/fields/'.$field_info['field_token'].'/'?>" rel="<?= $field_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($field_image_token != 'field'): ?>
        <img src="<?= $field_image_path ?>" width="<?= $field_image_size ?>" height="<?= $field_image_size ?>" alt="<?= $field_title_text ?>" />
      <? else: ?>
        <span><?= $field_info['field_name'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  if ($field_flag_complete){ $mmrpg_database_fields_count_complete++; }
  $mmrpg_database_fields_links .= ob_get_clean();
  $mmrpg_database_fields_links_counter++;
  $key_counter++;
}

// End the groups, however many there were
$mmrpg_database_fields_links .= '</div>';

?>