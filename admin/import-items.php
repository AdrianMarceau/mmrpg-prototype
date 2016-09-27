<?php

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while importing!');  }

// Collect any extra request variables for the import
$this_import_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=import-items&limit=<?= $this_import_limit?>">Update Ability Database</a> &raquo;
</div>
<?php
$this_page_markup .= ob_get_clean();


// Require the MMRPG database file
//define('DATA_DATABASE_SHOW_MECHAS', true);
//define('DATA_DATABASE_SHOW_CACHE', true);
//define('DATA_DATABASE_SHOW_HIDDEN', true);
//require_once('includes/include.database.php');

// TYPES DATABASE

// Define the index of types for the game
$mmrpg_database_types = rpg_type::get_index(true);
//$temp_remove_types = array('attack', 'defense', 'speed', 'energy', 'weapons', 'empty', 'light', 'wily', 'cossack', 'damage', 'recovery', 'experience', 'level');
//foreach ($temp_remove_types AS $token){ unset($mmrpg_database_types[$token]); }
uasort($mmrpg_database_types, function($t1, $t2){
    if ($t1['type_order'] > $t2['type_order']){ return 1; }
    elseif ($t1['type_order'] < $t2['type_order']){ return -1; }
    else { return 0; }
});

// HIDDEN ITEMS

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
foreach ($mmrpg_database_types AS $type_token => $type_info){
    $hidden_database_items[] = $type_token.'-star';
    if (!empty($type_info['type_class']) && $type_info['type_class'] == 'special'){
        $hidden_database_items[] = $type_token.'-core';
        $hidden_database_items[] = $type_token.'-shard';
    }
}
$hidden_database_items = array_merge($hidden_database_items, array('heart'));
$hidden_database_items_count = !empty($hidden_database_items) ? count($hidden_database_items) : 0;

// Truncate any robots currently in the database
$db->query('TRUNCATE TABLE mmrpg_index_items');

// Require the items index file
//$mmrpg_index = array();
require(MMRPG_CONFIG_ROOTDIR.'data/items/_index.php');
//die('$mmrpg_index[types] = <pre>'.print_r($mmrpg_database_types, true).'</pre>');

// Fill in potentially missing fields with defaults for sorting
if (!empty($mmrpg_index['items'])){
    foreach ($mmrpg_index['items'] AS $token => $item){
        $item['item_class'] = isset($item['item_class']) ? $item['item_class'] : 'item';
        $item['item_subclass'] = isset($item['item_subclass']) ? $item['item_subclass'] : '';
        $item['item_game'] = isset($item['item_game']) ? $item['item_game'] : 'MMRPG';
        $item['item_group'] = isset($item['item_group']) ? $item['item_group'] : 'MMRPG';
        $item['item_master'] = isset($item['item_master']) ? $item['item_master'] : '';
        $item['item_number'] = isset($item['item_number']) ? $item['item_number'] : '';
        $item['item_energy'] = isset($item['item_energy']) ? $item['item_energy'] : 1;
        $item['item_type'] = isset($item['item_type']) ? $item['item_type'] : '';
        $item['item_type2'] = isset($item['item_type2']) ? $item['item_type2'] : '';
        $mmrpg_index['items'][$token] = $item;
    }
}


// -- MMRPG IMPORT ITEMS -- //


// Sort the item index based on item number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^small-screw$/i';
$temp_pattern_first[] = '/^large-screw$/i';
$temp_pattern_first[] = '/^energy-pellet$/i';
$temp_pattern_first[] = '/^energy-capsule$/i';
$temp_pattern_first[] = '/^weapon-pellet$/i';
$temp_pattern_first[] = '/^weapon-capsule$/i';
$temp_pattern_first[] = '/^energy-tank$/i';
$temp_pattern_first[] = '/^weapon-tank$/i';
$temp_pattern_first[] = '/^extra-life$/i';
$temp_pattern_first[] = '/^yashichi$/i';
$temp_pattern_first[] = '/^attack-pellet$/i';
$temp_pattern_first[] = '/^attack-capsule$/i';
$temp_pattern_first[] = '/^defense-pellet$/i';
$temp_pattern_first[] = '/^defense-capsule$/i';
$temp_pattern_first[] = '/^speed-pellet$/i';
$temp_pattern_first[] = '/^speed-capsule$/i';
$temp_pattern_first[] = '/^super-pellet$/i';
$temp_pattern_first[] = '/^super-capsule$/i';
//die('$mmrpg_index[\'types\'] = <pre>'.print_r($mmrpg_database_types, true).'</pre>');
//$temp_element_types = $mmrpg_database_types; //array('none', 'copy', 'crystal', 'cutter', 'earth', 'electric', 'explode', 'flame', 'freeze', 'impact', 'laser', 'missile', 'nature', 'shadow', 'shield', 'space', 'swift', 'time', 'water', 'wind');
foreach ($mmrpg_database_types AS $type_token => $type_info){
    //if ($type_token == 'none' || $type_token == 'copy'){ continue; }
    if (!empty($type_info['type_class']) && $type_info['type_class'] == 'special'){ continue; }
    $temp_pattern_first[] = '/^'.$type_token.'-shard$/i';
    $temp_pattern_first[] = '/^'.$type_token.'-core$/i';
    //$temp_pattern_first[] = '/^'.$type_token.'-star$/i';
}
//die('$temp_pattern_first = <pre>'.print_r($temp_pattern_first, true).'</pre>');
$temp_pattern_last = array();
foreach ($mmrpg_database_types AS $type_token => $type_info){
    //if ($type_token == 'none' || $type_token == 'copy'){ continue; }
    if (!empty($type_info['type_class']) && $type_info['type_class'] != 'special'){ continue; }
    $temp_pattern_last[] = '/^'.$type_token.'-shard$/i';
    $temp_pattern_last[] = '/^'.$type_token.'-core$/i';
}

$temp_pattern_last[] = '/^star$/i';

$temp_pattern_last[] = '/^energy-upgrade$/i';
$temp_pattern_last[] = '/^weapon-upgrade$/i';
$temp_pattern_last[] = '/^attack-booster$/i';
$temp_pattern_last[] = '/^defense-booster$/i';
$temp_pattern_last[] = '/^speed-booster$/i';
$temp_pattern_last[] = '/^field-booster$/i';
$temp_pattern_last[] = '/^target-module$/i';
$temp_pattern_last[] = '/^charge-module$/i';
$temp_pattern_last[] = '/^growth-module$/i';
$temp_pattern_last[] = '/^fortune-module$/i';

$temp_pattern_last[] = '/^light-program$/i';
$temp_pattern_last[] = '/^auto-link$/i';
$temp_pattern_last[] = '/^item-codes$/i';
$temp_pattern_last[] = '/^dress-codes$/i';

$temp_pattern_last[] = '/^wily-program$/i';
$temp_pattern_last[] = '/^reggae-link$/i';
$temp_pattern_last[] = '/^ability-codes$/i';
$temp_pattern_last[] = '/^weapon-codes$/i';

$temp_pattern_last[] = '/^cossack-program$/i';
$temp_pattern_last[] = '/^kalinka-link$/i';
$temp_pattern_last[] = '/^equip-codes$/i';
$temp_pattern_last[] = '/^field-codes$/i';

$temp_pattern_last[] = '/^([a-z]+)-star$/i';
//$temp_pattern_last[] = '/^heart$/i';
$temp_pattern_last = array_reverse($temp_pattern_last);
//die('$temp_pattern_last = <pre>'.print_r($temp_pattern_last, true).'</pre>');
function mmrpg_index_sort_items($item_one, $item_two){
    // Pull in global variables
    global $temp_pattern_first, $temp_pattern_last;
    // Loop through all the temp patterns and compare them one at a time
    foreach ($temp_pattern_first AS $key => $pattern){
        // Check if either of these two items matches the current pattern
        if (preg_match($pattern, $item_one['item_token']) && !preg_match($pattern, $item_two['item_token'])){ return -1; }
        elseif (!preg_match($pattern, $item_one['item_token']) && preg_match($pattern, $item_two['item_token'])){ return 1; }
    }
    foreach ($temp_pattern_last AS $key => $pattern){
        // Check if either of these two items matches the current pattern
        if (preg_match($pattern, $item_one['item_token']) && !preg_match($pattern, $item_two['item_token'])){ return 1; }
        elseif (!preg_match($pattern, $item_one['item_token']) && preg_match($pattern, $item_two['item_token'])){ return -1; }
    }
    // If only one of the two items has a type, the one with goes first
    if (!empty($item_one['item_token']) && empty($item_two['item_token'])){ return 1; }
    elseif (empty($item_one['item_token']) && !empty($item_two['item_token'])){ return -1; }
    else {
        // If only one of the two items has a type, the one with goes first
        if ($item_one['item_token'] > $item_two['item_token']){ return 1; }
        elseif ($item_one['item_token'] < $item_two['item_token']){ return -1; }
        else {
            // Return 0 by default
            return 0;
        }
    }
}
uasort($mmrpg_index['items'], 'mmrpg_index_sort_items');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_items</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_index['items']) ? count($mmrpg_index['items']) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_items, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

// Loop through each of the item info arrays
$item_key = 0;
$item_order = 0;
//$temp_empty = $mmrpg_index['items']['item'];
//unset($mmrpg_index['items']['item']);
//array_unshift($mmrpg_index['items'], $temp_empty);
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
        $temp_insert_array['item_group'] = !empty($item_data['item_group']) ? $item_data['item_group'] : '';
        $temp_insert_array['item_class'] = !empty($item_data['item_class']) ? $item_data['item_class'] : 'item';
        $temp_insert_array['item_subclass'] = !empty($item_data['item_subclass']) ? $item_data['item_subclass'] : '';
        $temp_insert_array['item_image'] = !empty($item_data['item_image']) ? $item_data['item_image'] : '';
        $temp_insert_array['item_image_sheets'] = isset($item_data['item_image_sheets']) ? $item_data['item_image_sheets'] : 1;
        $temp_insert_array['item_image_size'] = !empty($item_data['item_image_size']) ? $item_data['item_image_size'] : 40;
        $temp_insert_array['item_image_editor'] = !empty($item_data['item_image_editor']) ? $item_data['item_image_editor'] : ($item_data['item_image'] != 'item' ? 412 : 0);
        $temp_insert_array['item_type'] = !empty($item_data['item_type']) ? $item_data['item_type'] : '';
        $temp_insert_array['item_type2'] = !empty($item_data['item_type2']) ? $item_data['item_type2'] : '';
        $temp_insert_array['item_description'] = !empty($item_data['item_description']) && $item_data['item_description'] != '...' ? $item_data['item_description'] : '';
        $temp_insert_array['item_description2'] = !empty($item_data['item_description2']) && $item_data['item_description2'] != '...' ? $item_data['item_description2'] : '';
        $temp_insert_array['item_description_use'] = !empty($item_data['item_description_use']) && $item_data['item_description_use'] != '...' ? $item_data['item_description_use'] : '';
        $temp_insert_array['item_description_hold'] = !empty($item_data['item_description_hold']) && $item_data['item_description_hold'] != '...' ? $item_data['item_description_hold'] : '';
        $temp_insert_array['item_description_shop'] = !empty($item_data['item_description_shop']) && $item_data['item_description_shop'] != '...' ? $item_data['item_description_shop'] : '';
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
        $temp_insert_array['item_price'] = !empty($item_data['item_price']) ? $item_data['item_price'] : 0;
        $temp_insert_array['item_target'] = !empty($item_data['item_target']) ? $item_data['item_target'] : 'auto';
        $temp_insert_array['item_functions'] = !empty($item_data['item_functions']) ? $item_data['item_functions'] : 'items/item.php';

        // Define the item frame properties
        $temp_insert_array['item_frame'] = !empty($item_data['item_frame']) ? $item_data['item_frame'] : 'base';
        $temp_insert_array['item_frame_animate'] = json_encode(!empty($item_data['item_frame_animate']) ? $item_data['item_frame_animate'] : array());
        $temp_insert_array['item_frame_index'] = json_encode(!empty($item_data['item_frame_index']) ? $item_data['item_frame_index'] : array());
        $temp_insert_array['item_frame_offset'] = json_encode(!empty($item_data['item_frame_offset']) ? $item_data['item_frame_offset'] : array());
        $temp_insert_array['item_frame_styles'] = !empty($item_data['item_frame_styles']) ? $item_data['item_frame_styles'] : '';
        $temp_insert_array['item_frame_classes'] = !empty($item_data['item_frame_classes']) ? $item_data['item_frame_classes'] : '';

        // Define the flags
        $temp_insert_array['item_flag_hidden'] = in_array($temp_insert_array['item_token'], $hidden_database_items) ? 1 : 0;
        $temp_insert_array['item_flag_complete'] = $temp_insert_array['item_class'] == 'system' || $item_data['item_image'] != 'item' ? 1 : 0;
        $temp_insert_array['item_flag_published'] = 1;

        // Define the order counter
        if ($temp_insert_array['item_class'] != 'system'){
            $temp_insert_array['item_order'] = $item_order;
            $item_order++;
        } else {
            $temp_insert_array['item_order'] = 0;
        }

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
    $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ITEMS HAVE BEEN IMPORTED UPDATED!</strong></p>';
}



?>