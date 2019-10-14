<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'fields/index';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Get an index of all fields from the database
$fields_index = rpg_field::get_index(true, false, false);

// If not empty, loop through and remove api-incompatible data
if (!empty($fields_index)){
    foreach ($fields_index AS $field_token => $field_data){
        unset($field_data['_parsed']);
        unset($field_data['field_id']);
        unset($field_data['field_functions']);
        $fields_index[$field_token] = $field_data;
    }
}

// Print out the fields index as JSON so others can use them
if (!empty($fields_index)){ print_success_and_update_api_cache(array('fields' => $fields_index, 'total' => count($fields_index))); }
else { print_success_and_update_api_cache('Field index could not be loaded from the database!'); }

?>
