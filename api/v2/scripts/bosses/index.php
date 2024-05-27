<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'bosses/index';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for bosses and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_BOSSES', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_BOSSES', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/bosses.php');
if (empty($mmrpg_database_bosses)){ print_error_and_quit('The boss database could not be loaded'); }
$bosses_index = !empty($mmrpg_database_bosses) ? $mmrpg_database_bosses : array();

// If not empty, go through and remove or reformat api-incompatible data
if (!empty($bosses_index)){
    foreach ($bosses_index AS $boss_token => $boss_data){
        unset($boss_data['_parsed']);
        unset($boss_data['robot_id']);
        unset($boss_data['robot_functions']);
        $boss_data['robot_abilities_rewards'] = !empty($boss_data['robot_rewards']['abilities']) ? $boss_data['robot_rewards']['abilities'] : array();
        $boss_data['robot_abilities_compatible'] = !empty($boss_data['robot_abilities']) ? $boss_data['robot_abilities'] : array();
        unset($boss_data['robot_abilities'], $boss_data['robot_rewards']);
        $backup_boss_data = $boss_data; $boss_data = array();
        foreach ($backup_boss_data AS $k => $v){ $boss_data[str_replace('robot_', '', $k)] = $v; }
        $bosses_index[$boss_token] = $boss_data;
    }
}

// Print out the bosses index as JSON so others can use them
if (!empty($bosses_index)){ print_success_and_update_api_cache(array('bosses' => $bosses_index, 'total' => count($bosses_index))); }
else { print_error_and_quit('The boss index array was empty'); }

?>
