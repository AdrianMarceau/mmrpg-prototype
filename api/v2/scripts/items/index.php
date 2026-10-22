<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'items/index';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for items and then parse necessary data
define('FORCE_INCLUDE_TEMPLATE_ITEM', $api_include_templates);
define('FORCE_INCLUDE_HIDDEN_ITEMS', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_ITEMS', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/items.php');
if (empty($mmrpg_database_items)){ print_error_and_quit('The item database could not be loaded'); }
$items_index = !empty($mmrpg_database_items) ? $mmrpg_database_items : array();

// If not empty, loop through and remove api-incompatible data
if (!empty($items_index)){
    foreach ($items_index AS $item_token => $item_data){
        unset($item_data['_parsed']);
        unset($item_data['item_id']);
        unset($item_data['item_functions']);
        $backup_item_data = $item_data; $item_data = array();
        foreach ($backup_item_data AS $k => $v){ $item_data[str_replace('item_', '', $k)] = $v; }
        $items_index[$item_token] = $item_data;
    }
}

// Print out the items index as JSON so others can use them
if (!empty($items_index)){ print_success_and_update_api_cache(array('items' => $items_index, 'total' => count($items_index))); }
else { print_error_and_quit('The item index array was empty'); }

?>
