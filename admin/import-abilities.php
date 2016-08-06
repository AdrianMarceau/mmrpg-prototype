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
<a href="admin.php?action=import-abilities&limit=<?=$this_import_limit?>">Update Ability Database</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();


// Require the MMRPG database file
//define('DATA_DATABASE_SHOW_MECHAS', true);
//define('DATA_DATABASE_SHOW_CACHE', true);
//define('DATA_DATABASE_SHOW_HIDDEN', true);
//require_once('database/include.php');

// Require the abilities index file
require(MMRPG_CONFIG_ROOTDIR.'data/abilities/_index.php');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_abilities</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_database_abilities) ? count($mmrpg_database_abilities) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_abilities, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

// Loop through each of the ability info arrays
$ability_key = 0;
$temp_empty = $mmrpg_index['abilities']['ability'];
unset($mmrpg_index['abilities']['ability']);
array_unshift($mmrpg_index['abilities'], $temp_empty);
if (!empty($mmrpg_index['abilities'])){
  foreach ($mmrpg_index['abilities'] AS $ability_token => $ability_data){

    // If this ability's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$ability_token.'/')){ $ability_data['ability_image'] = $ability_data['ability_token']; }
    else { $ability_data['ability_image'] = 'ability'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['ability_id'] = isset($ability_data['ability_id']) ? $ability_data['ability_id'] : $ability_key;
    $temp_insert_array['ability_token'] = $ability_data['ability_token'];
    $temp_insert_array['ability_name'] = !empty($ability_data['ability_name']) ? $ability_data['ability_name'] : '';
    $temp_insert_array['ability_game'] = !empty($ability_data['ability_game']) ? $ability_data['ability_game'] : '';
    $temp_insert_array['ability_class'] = !empty($ability_data['ability_class']) ? $ability_data['ability_class'] : 'master';
    $temp_insert_array['ability_image'] = !empty($ability_data['ability_image']) ? $ability_data['ability_image'] : '';
    $temp_insert_array['ability_image_sheets'] = isset($ability_data['ability_image_sheets']) ? $ability_data['ability_image_sheets'] : 1;
    $temp_insert_array['ability_image_size'] = !empty($ability_data['ability_image_size']) ? $ability_data['ability_image_size'] : 40;
    $temp_insert_array['ability_image_editor'] = !empty($ability_data['ability_image_editor']) ? $ability_data['ability_image_editor'] : ($ability_data['ability_image'] != 'ability' ? 412 : 0);
    $temp_insert_array['ability_type'] = !empty($ability_data['ability_type']) ? $ability_data['ability_type'] : '';
    $temp_insert_array['ability_type2'] = !empty($ability_data['ability_type2']) ? $ability_data['ability_type2'] : '';
    $temp_insert_array['ability_description'] = !empty($ability_data['ability_description']) && $ability_data['ability_description'] != '...' ? $ability_data['ability_description'] : '';
    $temp_insert_array['ability_description2'] = !empty($ability_data['ability_description2']) && $ability_data['ability_description2'] != '...' ? $ability_data['ability_description2'] : '';
    $temp_insert_array['ability_speed'] = !empty($ability_data['ability_speed']) ? $ability_data['ability_speed'] : 1;
    $temp_insert_array['ability_energy'] = isset($ability_data['ability_energy']) ? $ability_data['ability_energy'] : 1;
    $temp_insert_array['ability_energy_percent'] = !empty($ability_data['ability_energy_percent']) ? 1 : 0;
    $temp_insert_array['ability_damage'] = !empty($ability_data['ability_damage']) ? $ability_data['ability_damage'] : 0;
    $temp_insert_array['ability_damage_percent'] = !empty($ability_data['ability_damage_percent']) ? 1 : 0;
    $temp_insert_array['ability_damage2'] = !empty($ability_data['ability_damage2']) ? $ability_data['ability_damage2'] : 0;
    $temp_insert_array['ability_damage2_percent'] = !empty($ability_data['ability_damage2_percent']) ? 1 : 0;
    $temp_insert_array['ability_recovery'] = !empty($ability_data['ability_recovery']) ? $ability_data['ability_recovery'] : 0;
    $temp_insert_array['ability_recovery_percent'] = !empty($ability_data['ability_recovery_percent']) ? 1 : 0;
    $temp_insert_array['ability_recovery2'] = !empty($ability_data['ability_recovery2']) ? $ability_data['ability_recovery2'] : 0;
    $temp_insert_array['ability_recovery2_percent'] = !empty($ability_data['ability_recovery2_percent']) ? 1 : 0;
    $temp_insert_array['ability_accuracy'] = !empty($ability_data['ability_accuracy']) ? $ability_data['ability_accuracy'] : 0;
    $temp_insert_array['ability_target'] = !empty($ability_data['ability_target']) ? $ability_data['ability_target'] : 'auto';
    $temp_insert_array['ability_functions'] = !empty($ability_data['ability_functions']) ? $ability_data['ability_functions'] : 'abilities/ability.php';

    // Define the ability frame properties
    $temp_insert_array['ability_frame'] = !empty($ability_data['ability_frame']) ? $ability_data['ability_frame'] : 'base';
    $temp_insert_array['ability_frame_animate'] = json_encode(!empty($ability_data['ability_frame_animate']) ? $ability_data['ability_frame_animate'] : array());
    $temp_insert_array['ability_frame_index'] = json_encode(!empty($ability_data['ability_frame_index']) ? $ability_data['ability_frame_index'] : array());
    $temp_insert_array['ability_frame_offset'] = json_encode(!empty($ability_data['ability_frame_offset']) ? $ability_data['ability_frame_offset'] : array());
    //$temp_insert_array['ability_frame_animate'] = array();
    //if (!empty($ability_data['ability_frame_animate'])){ foreach ($ability_data['ability_frame_animate'] AS $key => $token){ $temp_insert_array['ability_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['ability_frame_animate'] = implode(',', $temp_insert_array['ability_frame_animate']);
    //$temp_insert_array['ability_frame_index'] = array();
    //if (!empty($ability_data['ability_frame_index'])){ foreach ($ability_data['ability_frame_index'] AS $key => $token){ $temp_insert_array['ability_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['ability_frame_index'] = implode(',', $temp_insert_array['ability_frame_index']);
    //$temp_insert_array['ability_frame_offset'] = array();
    //if (!empty($ability_data['ability_frame_offset'])){ foreach ($ability_data['ability_frame_offset'] AS $key => $token){ $temp_insert_array['ability_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['ability_frame_offset'] = implode(',', $temp_insert_array['ability_frame_offset']);
    $temp_insert_array['ability_frame_styles'] = !empty($ability_data['ability_frame_styles']) ? $ability_data['ability_frame_styles'] : '';
    $temp_insert_array['ability_frame_classes'] = !empty($ability_data['ability_frame_classes']) ? $ability_data['ability_frame_classes'] : '';

    // Define the ability frame properties
    $temp_insert_array['attachment_frame'] = !empty($ability_data['attachment_frame']) ? $ability_data['attachment_frame'] : 'base';
    $temp_insert_array['attachment_frame_animate'] = json_encode(!empty($ability_data['attachment_frame_animate']) ? $ability_data['attachment_frame_animate'] : array());
    $temp_insert_array['attachment_frame_index'] = json_encode(!empty($ability_data['attachment_frame_index']) ? $ability_data['attachment_frame_index'] : array());
    $temp_insert_array['attachment_frame_offset'] = json_encode(!empty($ability_data['attachment_frame_offset']) ? $ability_data['attachment_frame_offset'] : array());
    //$temp_insert_array['attachment_frame_animate'] = array();
    //if (!empty($ability_data['attachment_frame_animate'])){ foreach ($ability_data['attachment_frame_animate'] AS $key => $token){ $temp_insert_array['attachment_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_animate'] = implode(',', $temp_insert_array['attachment_frame_animate']);
    //$temp_insert_array['attachment_frame_index'] = array();
    //if (!empty($ability_data['attachment_frame_index'])){ foreach ($ability_data['attachment_frame_index'] AS $key => $token){ $temp_insert_array['attachment_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_index'] = implode(',', $temp_insert_array['attachment_frame_index']);
    //$temp_insert_array['attachment_frame_offset'] = array();
    //if (!empty($ability_data['attachment_frame_offset'])){ foreach ($ability_data['attachment_frame_offset'] AS $key => $token){ $temp_insert_array['attachment_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['attachment_frame_offset'] = implode(',', $temp_insert_array['attachment_frame_offset']);
    $temp_insert_array['attachment_frame_styles'] = !empty($ability_data['attachment_frame_styles']) ? $ability_data['attachment_frame_styles'] : '';
    $temp_insert_array['attachment_frame_classes'] = !empty($ability_data['attachment_frame_classes']) ? $ability_data['attachment_frame_classes'] : '';

    // Define the flags
    $temp_insert_array['ability_flag_hidden'] = $temp_insert_array['ability_class'] != 'master' || in_array($temp_insert_array['ability_token'], array('ability', 'attachment-defeat')) ? 1 : 0;
    $temp_insert_array['ability_flag_complete'] = $temp_insert_array['ability_class'] == 'system' || $ability_data['ability_image'] != 'ability' ? 1 : 0;
    $temp_insert_array['ability_flag_published'] = 1;

    // Check if this ability already exists in the database
    $temp_success = true;
    $temp_exists = $db->get_array("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_token LIKE '{$temp_insert_array['ability_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $db->insert('mmrpg_index_abilities', $temp_insert_array); }
    else { $temp_success = $db->update('mmrpg_index_abilities', $temp_insert_array, array('ability_token' => $temp_insert_array['ability_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_abilities['.$ability_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($ability_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(rpg_ability::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $ability_key++;
  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}



?>