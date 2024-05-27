<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'items';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for items and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_ITEMS', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_ITEMS', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/items.php');
if (empty($mmrpg_database_items)){ print_error_and_quit('The item database could not be loaded'); }
$item_tokens = !empty($mmrpg_database_items) ? array_keys($mmrpg_database_items) : array();

// Print out the item tokens as JSON so others can use them
if (!empty($item_tokens)){ print_success_and_update_api_cache(array('items' => $item_tokens, 'total' => count($item_tokens))); }
else { print_error_and_quit('The item token array was empty'); }

?>
