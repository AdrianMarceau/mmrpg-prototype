<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'items/index/{token}';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for items and then parse necessary data
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/items.php');
if (empty($mmrpg_database_items)){ print_error_and_quit('The item database could not be loaded'); }
$item_data = !empty($mmrpg_database_items[$api_request_token]) ? $mmrpg_database_items[$api_request_token] : false;

// If not empty, go through and remove api-incompatible data
if (!empty($item_data)){
    unset($item_data['_parsed']);
    unset($item_data['item_id']);
    unset($item_data['item_functions']);
    $backup_item_data = $item_data; $item_data = array();
    foreach ($backup_item_data AS $k => $v){ $item_data[str_replace('item_', '', $k)] = $v; }
}

// Print out the items index as JSON so others can use them
if (!empty($item_data)){ print_success_and_update_api_cache(array('item' => $item_data)); }
else { print_error_and_quit('Item data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
