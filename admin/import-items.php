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
<a href="admin.php?action=import-items&limit=<?=$this_import_limit?>">Update Ability Database</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();


// Require the MMRPG database file
//define('DATA_DATABASE_SHOW_MECHAS', true);
//define('DATA_DATABASE_SHOW_CACHE', true);
//define('DATA_DATABASE_SHOW_HIDDEN', true);
//require_once('data/database.php');

// Require the items index file
require(MMRPG_CONFIG_ROOTDIR.'data/items/_index.php');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_items</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_database_items) ? count($mmrpg_database_items) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_items, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

// Loop through each of the item info arrays
$item_key = 0;
$temp_empty = $mmrpg_index['items']['item'];
unset($mmrpg_index['items']['item']);
array_unshift($mmrpg_index['items'], $temp_empty);
if (!empty($mmrpg_index['items'])){
  foreach ($mmrpg_index['items'] AS $item_token => $item_data){

    // If this item's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/items/'.$item_token.'/')){ $item_data['item_image'] = $item_data['item_token']; }
    else { $item_data['item_image'] = 'item'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['item_id'] = isset($item_data['item_id']) ? $item_data['item_id'] : $item_key;
    $temp_insert_array['item_token'] = $item_data['item_token'];
    $temp_insert_array['item_name'] = !empty($item_data['item_name']) ? $item_data['item_name'] : '';
    $temp_insert_array['item_game'] = !empty($item_data['item_game']) ? $item_data['item_game'] : '';
    $temp_insert_array['item_class'] = !empty($item_data['item_class']) ? $item_data['item_class'] : 'master';
    $temp_insert_array['item_image'] = !empty($item_data['item_image']) ? $item_data['item_image'] : '';
    $temp_insert_array['item_image_sheets'] = isset($item_data['item_image_sheets']) ? $item_data['item_image_sheets'] : 1;
    $temp_insert_array['item_image_size'] = !empty($item_data['item_image_size']) ? $item_data['item_image_size'] : 40;
    $temp_insert_array['item_image_editor'] = !empty($item_data['item_image_editor']) ? $item_data['item_image_editor'] : ($item_data['item_image'] != 'item' ? 412 : 0);
    $temp_insert_array['item_type'] = !empty($item_data['item_type']) ? $item_data['item_type'] : '';
    $temp_insert_array['item_type2'] = !empty($item_data['item_type2']) ? $item_data['item_type2'] : '';
    $temp_insert_array['item_description'] = !empty($item_data['item_description']) && $item_data['item_description'] != '...' ? $item_data['item_description'] : '';
    $temp_insert_array['item_description2'] = !empty($item_data['item_description2']) && $item_data['item_description2'] != '...' ? $item_data['item_description2'] : '';
    $temp_insert_array['item_speed'] = !empty($item_data['item_speed']) ? $item_data['item_speed'] : 1;
    $temp_insert_array['item_energy'] = isset($item_data['item_energy']) ? $item_data['item_energy'] : 1;
    $temp_insert_array['item_energy_percent'] = !empty($item_data['item_energy_percent']) ? 1 : 0;
    $temp_insert_array['item_damage'] = !empty($item_data['item_damage']) ? $item_data['item_damage'] : 0;
    $temp_insert_array['item_damage_percent'] = !empty($item_data['item_damage_percent']) ? 1 : 0;
    $temp_insert_array['item_damage2'] = !empty($item_data['item_damage2']) ? $item_data['item_damage2'] : 0;
    $temp_insert_array['item_damage2_percent'] = !empty($item_data['item_damage2_percent']) ? 1 : 0;
    $temp_insert_array['item_recovery'] = !empty($item_data['item_recovery']) ? $item_data['item_recovery'] : 0;
    $temp_insert_array['item_recovery_percent'] = !empty($item_data['item_recovery_percent']) ? 1 : 0;
    $temp_insert_array['item_recovery2'] = !empty($item_data['item_recovery2']) ? $item_data['item_recovery2'] : 0;
    $temp_insert_array['item_recovery2_percent'] = !empty($item_data['item_recovery2_percent']) ? 1 : 0;
    $temp_insert_array['item_accuracy'] = !empty($item_data['item_accuracy']) ? $item_data['item_accuracy'] : 0;
    $temp_insert_array['item_target'] = !empty($item_data['item_target']) ? $item_data['item_target'] : 'auto';
    $temp_insert_array['item_functions'] = !empty($item_data['item_functions']) ? $item_data['item_functions'] : 'items/item.php';

    // Define the item frame properties
    $temp_insert_array['item_frame'] = !empty($item_data['item_frame']) ? $item_data['item_frame'] : 'base';
    $temp_insert_array['item_frame_animate'] = json_encode(!empty($item_data['item_frame_animate']) ? $item_data['item_frame_animate'] : array());
    $temp_insert_array['item_frame_index'] = json_encode(!empty($item_data['item_frame_index']) ? $item_data['item_frame_index'] : array());
    $temp_insert_array['item_frame_offset'] = json_encode(!empty($item_data['item_frame_offset']) ? $item_data['item_frame_offset'] : array());
    //$temp_insert_array['item_frame_animate'] = array();
    //if (!empty($item_data['item_frame_animate'])){ foreach ($item_data['item_frame_animate'] AS $key => $token){ $temp_insert_array['item_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['item_frame_animate'] = implode(',', $temp_insert_array['item_frame_animate']);
    //$temp_insert_array['item_frame_index'] = array();
    //if (!empty($item_data['item_frame_index'])){ foreach ($item_data['item_frame_index'] AS $key => $token){ $temp_insert_array['item_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['item_frame_index'] = implode(',', $temp_insert_array['item_frame_index']);
    //$temp_insert_array['item_frame_offset'] = array();
    //if (!empty($item_data['item_frame_offset'])){ foreach ($item_data['item_frame_offset'] AS $key => $token){ $temp_insert_array['item_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['item_frame_offset'] = implode(',', $temp_insert_array['item_frame_offset']);
    $temp_insert_array['item_frame_styles'] = !empty($item_data['item_frame_styles']) ? $item_data['item_frame_styles'] : '';
    $temp_insert_array['item_frame_classes'] = !empty($item_data['item_frame_classes']) ? $item_data['item_frame_classes'] : '';

    // Define the item frame properties
    $temp_insert_array['attachment_frame'] = !empty($item_data['attachment_frame']) ? $item_data['attachment_frame'] : 'base';
    $temp_insert_array['attachment_frame_animate'] = json_encode(!empty($item_data['attachment_frame_animate']) ? $item_data['attachment_frame_animate'] : array());
    $temp_insert_array['attachment_frame_index'] = json_encode(!empty($item_data['attachment_frame_index']) ? $item_data['attachment_frame_index'] : array());
    $temp_insert_array['attachment_frame_offset'] = json_encode(!empty($item_data['attachment_frame_offset']) ? $item_data['attachment_frame_offset'] : array());
    //$temp_insert_array['attachment_frame_animate'] = array();
    //if (!empty($item_data['attachment_frame_animate'])){ foreach ($item_data['attachment_frame_animate'] AS $key => $token){ $temp_insert_array['attachment_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_animate'] = implode(',', $temp_insert_array['attachment_frame_animate']);
    //$temp_insert_array['attachment_frame_index'] = array();
    //if (!empty($item_data['attachment_frame_index'])){ foreach ($item_data['attachment_frame_index'] AS $key => $token){ $temp_insert_array['attachment_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_index'] = implode(',', $temp_insert_array['attachment_frame_index']);
    //$temp_insert_array['attachment_frame_offset'] = array();
    //if (!empty($item_data['attachment_frame_offset'])){ foreach ($item_data['attachment_frame_offset'] AS $key => $token){ $temp_insert_array['attachment_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['attachment_frame_offset'] = implode(',', $temp_insert_array['attachment_frame_offset']);
    $temp_insert_array['attachment_frame_styles'] = !empty($item_data['attachment_frame_styles']) ? $item_data['attachment_frame_styles'] : '';
    $temp_insert_array['attachment_frame_classes'] = !empty($item_data['attachment_frame_classes']) ? $item_data['attachment_frame_classes'] : '';

    // Define the flags
    $temp_insert_array['item_flag_hidden'] = $temp_insert_array['item_class'] != 'master' || in_array($temp_insert_array['item_token'], array('item', 'attachment-defeat')) ? 1 : 0;
    $temp_insert_array['item_flag_complete'] = $temp_insert_array['item_class'] == 'system' || $item_data['item_image'] != 'item' ? 1 : 0;
    $temp_insert_array['item_flag_published'] = 1;

    // Check if this item already exists in the database
    $temp_success = true;
    $temp_exists = $db->get_array("SELECT item_token FROM mmrpg_index_items WHERE item_token LIKE '{$temp_insert_array['item_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $db->insert('mmrpg_index_items', $temp_insert_array); }
    else { $temp_success = $db->update('mmrpg_index_items', $temp_insert_array, array('item_token' => $temp_insert_array['item_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_items['.$item_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($item_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(rpg_item::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $item_key++;
  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}



?>