<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'abilities/index';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for abilities and then parse necessary data
$mmrpg_database_abilities_filter = "AND ability_flag_published = 1 ";
if (!$api_include_hidden){ $mmrpg_database_abilities_filter = "AND ability_flag_hidden = 0 "; }
if (!$api_include_incomplete){ $mmrpg_database_abilities_filter = "AND ability_flag_complete = 1 "; }
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');
if (empty($mmrpg_database_abilities)){ print_error_and_quit('The ability database could not be loaded'); }
$abilities_index = !empty($mmrpg_database_abilities) ? $mmrpg_database_abilities : array();

// If not empty, loop through and remove api-incompatible data
if (!empty($abilities_index)){
    foreach ($abilities_index AS $ability_token => $ability_data){
        unset($ability_data['_parsed']);
        unset($ability_data['ability_id']);
        unset($ability_data['ability_functions']);
        $backup_ability_data = $ability_data; $ability_data = array();
        foreach ($backup_ability_data AS $k => $v){ $ability_data[str_replace('ability_', '', $k)] = $v; }
        $abilities_index[$ability_token] = $ability_data;
    }
}

// Print out the abilities index as JSON so others can use them
if (!empty($abilities_index)){ print_success_and_update_api_cache(array('abilities' => $abilities_index, 'total' => count($abilities_index))); }
else { print_error_and_quit('The ability index array was empty'); }

?>
