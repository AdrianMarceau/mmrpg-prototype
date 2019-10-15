<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'types/index';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Get an index of all types from the database
$types_index = rpg_type::get_index(true, false, true);

// If not empty, loop through and remove api-incompatible data
if (!empty($types_index)){
    foreach ($types_index AS $type_token => $type_data){
        unset($type_data['_parsed']);
        unset($type_data['type_id']);
        unset($type_data['type_functions']);
        $types_index[$type_token] = $type_data;
    }
}

// Correct the name of the "none" type to "Neutral"
if (isset($types_index['none'])){ $types_index['none']['type_name'] = 'Neutral'; }

// Print out the types index as JSON so others can use them
if (!empty($types_index)){ print_success_and_update_api_cache(array('types' => $types_index, 'total' => count($types_index))); }
else { print_success_and_update_api_cache('The field index array was empty'); }

?>
