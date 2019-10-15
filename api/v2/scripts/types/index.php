<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'types/index';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get an index of all types from the database
$types_index = rpg_type::get_index(true, false, $api_include_hidden, true);

// If not empty, loop through and remove api-incompatible data
if (!empty($types_index)){
    foreach ($types_index AS $type_token => $type_data){
        unset($type_data['_parsed']);
        unset($type_data['type_id']);
        unset($type_data['type_functions']);
        $backup_type_data = $type_data; $type_data = array();
        foreach ($backup_type_data AS $k => $v){ $type_data[str_replace('type_', '', $k)] = $v; }
        $types_index[$type_token] = $type_data;
    }
}

// Correct the name of the "none" type to "Neutral"
if (isset($types_index['none'])){ $types_index['none']['name'] = 'Neutral'; }

// Print out the types index as JSON so others can use them
if (!empty($types_index)){ print_success_and_update_api_cache(array('types' => $types_index, 'total' => count($types_index))); }
else { print_success_and_update_api_cache('The type index array was empty'); }

?>
