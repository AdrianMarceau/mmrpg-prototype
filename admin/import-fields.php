<?

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while importing!');  }

// Collect any extra request variables for the import
$this_import_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=import-fields&limit=<?=$this_import_limit?>">Update Field Database</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();


// Truncate any robots currently in the database
$DB->query('TRUNCATE TABLE mmrpg_index_fields');

// Require the fields index file
if (empty($mmrpg_index['fields'])){ require(MMRPG_CONFIG_ROOTDIR.'data/fields/_index.php'); }
//die('check 2 <pre>'.print_r($mmrpg_index['fields'], true).'</pre>'); //DEBUG
// Require the spreadsheet functions file
require(MMRPG_CONFIG_ROOTDIR.'admin/spreadsheets.php');

// Fill in potentially missing fields with defaults for sorting
if (!empty($mmrpg_index['fields'])){
  foreach ($mmrpg_index['fields'] AS $token => $field){
    $field['field_class'] = isset($field['field_class']) ? $field['field_class'] : 'master';
    $field['field_game'] = isset($field['field_game']) ? $field['field_game'] : 'MMRPG';
    $field['field_group'] = isset($field['field_group']) ? $field['field_group'] : 'MMRPG';
    $field['field_type'] = isset($field['field_type']) ? $field['field_type'] : '';
    $field['field_type2'] = isset($field['field_type2']) ? $field['field_type2'] : '';
    $mmrpg_index['fields'][$token] = $field;
  }
}

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_fields</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_index['fields']) ? count($mmrpg_index['fields']) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_fields, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

$spreadsheet_field_stats = mmrpg_spreadsheet_field_stats();
$spreadsheet_field_descriptions = mmrpg_spreadsheet_field_descriptions();


/*
header('Content-type: text/plain; charset=UTF-8');
die($this_page_markup."\n\n".
  //'$mmrpg_index[\'fields\'] = <pre>'.print_r($mmrpg_index['fields'], true).'</pre>'."\n\n".
  '$spreadsheet_field_stats = <pre>'.print_r($spreadsheet_field_stats, true).'</pre>'."\n\n".
  '$spreadsheet_field_descriptions = <pre>'.print_r($spreadsheet_field_descriptions, true).'</pre>'."\n\n".
  '---');
*/

// Sort the field index based on field number
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
uasort($mmrpg_index['fields'], 'mmrpg_index_sort_fields');

// Loop through each of the field info arrays
$field_key = 0;
$field_order = 0;
$temp_empty = $mmrpg_index['fields']['field'];
unset($mmrpg_index['fields']['field']);
array_unshift($mmrpg_index['fields'], $temp_empty);
if (!empty($mmrpg_index['fields'])){
  foreach ($mmrpg_index['fields'] AS $field_token => $field_data){

    // If this field's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/fields/'.$field_token.'/')){ $field_data['field_image'] = $field_data['field_token']; }
    else { $field_data['field_image'] = 'field'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['field_id'] = isset($field_data['field_id']) ? $field_data['field_id'] : $field_key;
    $temp_insert_array['field_token'] = $field_data['field_token'];
    $temp_insert_array['field_number'] = !empty($field_data['field_number']) ? $field_data['field_number'] : '';
    $temp_insert_array['field_name'] = !empty($field_data['field_name']) ? $field_data['field_name'] : '';
    $temp_insert_array['field_game'] = !empty($field_data['field_game']) ? $field_data['field_game'] : '';

    $temp_insert_array['field_class'] = !empty($field_data['field_class']) ? $field_data['field_class'] : 'master';

    $temp_insert_array['field_master'] = !empty($field_data['field_master']) ? $field_data['field_master'] : '';
    $temp_insert_array['field_master2'] = json_encode(!empty($field_data['field_master2']) ? $field_data['field_master2'] : array());
    $temp_insert_array['field_mechas'] = json_encode(!empty($field_data['field_mechas']) ? $field_data['field_mechas'] : array());

    $temp_insert_array['field_editor'] = !empty($field_data['field_editor']) ? $field_data['field_editor'] : 412;

    $temp_insert_array['field_type'] = !empty($field_data['field_type']) ? $field_data['field_type'] : '';
    $temp_insert_array['field_type2'] = !empty($field_data['field_type2']) ? $field_data['field_type2'] : '';
    $temp_insert_array['field_multipliers'] = !empty($field_data['field_multipliers']) ? json_encode($field_data['field_multipliers']) : '';

    $temp_insert_array['field_description'] = !empty($field_data['field_description']) ? trim($field_data['field_description']) : '';
    $temp_insert_array['field_description2'] = !empty($field_data['field_description2']) ? trim($field_data['field_description2']) : '';

    $temp_insert_array['field_music'] = !empty($field_data['field_music']) ? $field_data['field_music'] : $field_data['field_token'];
    $temp_insert_array['field_music_name'] = !empty($field_data['field_music_name']) ? $field_data['field_music_name'] : '';
    $temp_insert_array['field_music_link'] = json_encode(!empty($field_data['field_music_link']) ? $field_data['field_music_link'] : '');

    $temp_insert_array['field_background'] = !empty($field_data['field_background']) ? $field_data['field_background'] : $field_data['field_token'];
    $temp_insert_array['field_background_frame'] = json_encode(!empty($field_data['field_background_frame']) ? $field_data['field_background_frame']: array());
    $temp_insert_array['field_background_attachments'] = json_encode(!empty($field_data['field_background_attachments']) ? $field_data['field_background_attachments'] : array());

    $temp_insert_array['field_foreground'] = !empty($field_data['field_foreground']) ? $field_data['field_foreground'] : $field_data['field_token'];
    $temp_insert_array['field_foreground_frame'] = json_encode(!empty($field_data['field_foreground_frame']) ? $field_data['field_foreground_frame']: array());
    $temp_insert_array['field_foreground_attachments'] = json_encode(!empty($field_data['field_foreground_attachments']) ? $field_data['field_foreground_attachments'] : array());

    $temp_insert_array['field_functions'] = !empty($field_data['field_functions']) ? $field_data['field_functions'] : 'fields/field.php';

    // Collect applicable spreadsheets for this field
    $spreadsheet_stats = !empty($spreadsheet_field_stats[$field_data['field_token']]) ? $spreadsheet_field_stats[$field_data['field_token']] : array();
    $spreadsheet_descriptions = !empty($spreadsheet_field_descriptions[$field_data['field_token']]) ? $spreadsheet_field_descriptions[$field_data['field_token']] : array();

    // Collect any user-contributed data for this field
    if (!empty($spreadsheet_stats['field_multipliers'])){ $temp_insert_array['field_multipliers'] = json_encode($spreadsheet_stats['field_multipliers']); }
    if (!empty($spreadsheet_descriptions['field_description'])){ $temp_insert_array['field_description2'] = trim($spreadsheet_descriptions['field_description']); }

    // Define the flags
    $temp_insert_array['field_flag_hidden'] = in_array($temp_insert_array['field_token'], array('field')) ? 1 : 0;
    $temp_insert_array['field_flag_complete'] = $field_data['field_image'] != 'field' ? 1 : 0;
    $temp_insert_array['field_flag_published'] = 1;

    // Define the order counter
    if ($temp_insert_array['field_class'] != 'system'){
      $temp_insert_array['field_order'] = $field_order;
      $field_order++;
    } else {
      $temp_insert_array['field_order'] = 0;
    }


    // Check if this field already exists in the database
    $temp_success = true;
    $temp_exists = $DB->get_array("SELECT field_token FROM mmrpg_index_fields WHERE field_token LIKE '{$temp_insert_array['field_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $DB->insert('mmrpg_index_fields', $temp_insert_array); }
    else { $temp_success = $DB->update('mmrpg_index_fields', $temp_insert_array, array('field_token' => $temp_insert_array['field_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_fields['.$field_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($field_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(mmrpg_field::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $field_key++;

    //die('end');

  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}

?>