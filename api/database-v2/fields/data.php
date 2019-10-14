<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'fields/index/{token}';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Get an index of all fields from the database
$field_data = rpg_field::get_index_info($api_request_token, true, false, false);

// If not empty, go through and remove api-incompatible data
if (!empty($field_data)){
    unset($field_data['_parsed']);
    unset($field_data['field_id']);
    unset($field_data['field_functions']);
}

// Print out the fields index as JSON so others can use them
if (!empty($field_data)){ print_success_and_update_api_cache(array('field' => $field_data)); }
else { print_success_and_update_api_cache('Field data for `'.$api_request_token.'` could not be loaded from the database!'); }

?>
