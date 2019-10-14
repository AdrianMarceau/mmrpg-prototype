<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'fields';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Get a list of all field tokens from the database
$field_tokens = rpg_field::get_index_tokens(true, false, false);

// Print out the field tokens as JSON so others can use them
if (!empty($field_tokens)){ print_success_and_update_api_cache(array('fields' => $field_tokens)); }
else { print_success_and_update_api_cache('Field tokens could not be loaded from the database!'); }

?>
