<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'types/index/{token}';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Get an index of all types from the database
$type_data = rpg_type::get_index_info($api_request_token, true, false, true);

// If not empty, go through and remove api-incompatible data
if (!empty($type_data)){
    unset($type_data['_parsed']);
    unset($type_data['type_id']);
    unset($type_data['type_functions']);
}

// Correct the name of the "none" type to "Neutral"
if ($type_data['type_token'] === 'none'){ $type_data['type_name'] = 'Neutral'; }

// Print out the types index as JSON so others can use them
if (!empty($type_data)){ print_success_and_update_api_cache(array('type' => $type_data)); }
else { print_success_and_update_api_cache('Field data for `'.$api_request_token.'` could not be loaded from the database!'); }

?>
