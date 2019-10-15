<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'abilities/index/{token}';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for abilities and then parse necessary data
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');
if (empty($mmrpg_database_abilities)){ print_error_and_quit('The ability database could not be loaded'); }
$ability_data = !empty($mmrpg_database_abilities[$api_request_token]) ? $mmrpg_database_abilities[$api_request_token] : false;

// If not empty, go through and remove api-incompatible data
if (!empty($ability_data)){
    unset($ability_data['_parsed']);
    unset($ability_data['ability_id']);
    unset($ability_data['ability_functions']);
    $backup_ability_data = $ability_data; $ability_data = array();
    foreach ($backup_ability_data AS $k => $v){ $ability_data[str_replace('ability_', '', $k)] = $v; }
}

// Print out the abilities index as JSON so others can use them
if (!empty($ability_data)){ print_success_and_update_api_cache(array('ability' => $ability_data)); }
else { print_error_and_quit('Ability data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
