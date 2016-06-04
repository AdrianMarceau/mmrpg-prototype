<?
// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// FIELD DATABASE

// Define the index of hidden fields to not appear in the database
$hidden_database_fields = array();
$hidden_database_fields = array_merge($hidden_database_fields, array('field')); //'prototype-complete'
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
$mmrpg_database_fields = $DB->get_array_list("SELECT * FROM mmrpg_index_fields WHERE field_flag_published = 1 {$temp_condition};", 'field_token');

// Remove unallowed fields from the database
foreach ($mmrpg_database_fields AS $temp_token => $temp_info){

  // Send this data through the field index parser
  $temp_info = rpg_field::parse_index_info($temp_info);

  if (in_array($temp_token, $hidden_database_fields)){
    unset($mmrpg_database_fields[$temp_token]);
  } else {
    // Ensure this field's image exists, else default to the placeholder
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/fields/'.$temp_token.'/')){ $temp_info['field_image'] = $temp_token; }
    else { $temp_info['field_image'] = 'field'; }
  }

  // Update the data in the fields index array
  $mmrpg_database_fields[$temp_token] = $temp_info;

}

// Sort the ability index based on field number
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_pattern_first = array();
$temp_pattern_first[] = '/^(intro-field)$/i';
$temp_pattern_first[] = '/^light-laboratory$/i';
$temp_pattern_first[] = '/^wily-castle$/i';
$temp_pattern_first[] = '/^cossack-citadel$/i';
//$temp_pattern_first = array_reverse($temp_pattern_first);
$temp_pattern_last = array();
$temp_pattern_last[] = '/^final-destination(-2|-3)?$/i';
$temp_pattern_last[] = '/^prototype-complete$/i';
$temp_pattern_last = array_reverse($temp_pattern_last);

// Sort the field index based on field number
function mmrpg_index_sort_fields($field_one, $field_two){
  // Pull in global variables
  global $temp_pattern_first, $temp_pattern_last;
  // Loop through all the temp patterns and compare them one at a time
  foreach ($temp_pattern_first AS $key => $pattern){
    // Check if either of these two fields matches the current pattern
    if (preg_match($pattern, $field_one['field_token']) && !preg_match($pattern, $field_two['field_token'])){ return -1; }
    elseif (!preg_match($pattern, $field_one['field_token']) && preg_match($pattern, $field_two['field_token'])){ return 1; }
  }
  foreach ($temp_pattern_last AS $key => $pattern){
    // Check if either of these two fields matches the current pattern
    if (preg_match($pattern, $field_one['field_token']) && !preg_match($pattern, $field_two['field_token'])){ return 1; }
    elseif (!preg_match($pattern, $field_one['field_token']) && preg_match($pattern, $field_two['field_token'])){ return -1; }
  }
  if ($field_one['field_game'] > $field_two['field_game']){ return 1; }
  elseif ($field_one['field_game'] < $field_two['field_game']){ return -1; }
  elseif ($field_one['field_token'] > $field_two['field_token']){ return 1; }
  elseif ($field_one['field_token'] < $field_two['field_token']){ return -1; }
  elseif ($field_one['field_token'] > $field_two['field_token']){ return 1; }
  elseif ($field_one['field_token'] < $field_two['field_token']){ return -1; }
  else { return 0; }
}
uasort($mmrpg_database_fields, 'mmrpg_index_sort_fields');

// Determine the token for the very first field in the database
$temp_field_tokens = array_values($mmrpg_database_fields);
$first_field_token = array_shift($temp_field_tokens);
$first_field_token = $first_field_token['field_token'];
unset($temp_field_tokens);

// Count the number of fields collected and filtered
$mmrpg_database_fields_count = count($mmrpg_database_fields);

// Loop through the database and generate the links for these fields
$key_counter = 0;
$mmrpg_database_fields_links = '';
$mmrpg_database_fields_links_counter = 0;
$mmrpg_database_fields_links_counter_incomplete = 0;
foreach ($mmrpg_database_fields AS $field_key => $field_info){
  // If a type filter has been applied to the field page
  $temp_field_types = array();
  if (!empty($field_info['field_type'])){ $temp_field_types[] = $field_info['field_type']; }
  if (!empty($field_info['field_type2'])){ $temp_field_types[] = $field_info['field_type2']; }
  if (empty($temp_field_types)){ $temp_field_types[] = 'none'; }
  if (isset($this_current_filter) && !in_array($this_current_filter, $temp_field_types)){ $key_counter++; continue; }
  // Collect the field sprite dimensions
  $field_image_size = 50;
  $field_image_token = !empty($field_info['field_image']) ? $field_info['field_image'] : $field_info['field_token'];
  $field_image_incomplete = $field_image_token == 'field' ? true : false;
  $field_is_active = !empty($this_current_token) && $this_current_token == $field_info['field_token'] ? true : false;
  $field_title_text = $field_info['field_name'].(!empty($temp_field_types) ? ' | '.str_replace('None', 'Neutral', ucwords(implode(' / ', $temp_field_types))).' Type' : '');
  $field_image_path = 'images/fields/'.$field_image_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
  $field_type_token = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
  if (!empty($field_info['field_type2'])){ $field_type_token .= '_'.$field_info['field_type2']; }
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $field_title_text ?>" data-token="<?= $field_info['field_token'] ?>" class="float float_left float_link field_type field_type_<?= $field_type_token ?><?= $field_image_incomplete  ? ' incomplete' : '' ?>">
    <a class="sprite sprite_field_link sprite_field sprite_field_sprite sprite_40x40 sprite_40x40_mugshot sprite_size_40x40 field_status_active field_position_active <?= $field_key == $first_field_token ? 'sprite_field_current ' : '' ?>" href="<?='database/fields/'.$field_info['field_token'].'/'?>" rel="<?= $field_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($field_image_token != 'field'): ?>
        <img src="<?= $field_image_path ?>" width="<?= $field_image_size ?>" height="<?= $field_image_size ?>" alt="<?= $field_title_text ?>" />
      <? else: ?>
        <span><?= $field_info['field_name'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  $mmrpg_database_fields_links .= preg_replace('/\s+/', ' ', trim(ob_get_clean()))."\n";
  $mmrpg_database_fields_links_counter++;
  if ($field_image_incomplete){ $mmrpg_database_fields_links_counter_incomplete++; }
  $key_counter++;
}

?>