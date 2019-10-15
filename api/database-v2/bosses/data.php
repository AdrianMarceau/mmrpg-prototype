<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'bosses/index/{token}';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for bosses and then parse necessary data
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/bosses.php');
if (empty($mmrpg_database_bosses)){ print_error_and_quit('The boss database could not be loaded'); }
$boss_data = !empty($mmrpg_database_bosses[$api_request_token]) ? $mmrpg_database_bosses[$api_request_token] : false;

// If not empty, go through and remove or reformat api-incompatible data
if (!empty($boss_data)){
    unset($boss_data['_parsed']);
    unset($boss_data['robot_id']);
    unset($boss_data['robot_functions']);
    $boss_data['robot_abilities_rewards'] = !empty($boss_data['robot_rewards']['abilities']) ? $boss_data['robot_rewards']['abilities'] : array();
    $boss_data['robot_abilities_compatible'] = !empty($boss_data['robot_abilities']) ? $boss_data['robot_abilities'] : array();
    unset($boss_data['robot_abilities'], $boss_data['robot_rewards']);
    $backup_boss_data = $boss_data; $boss_data = array();
    foreach ($backup_boss_data AS $k => $v){ $boss_data[str_replace('robot_', '', $k)] = $v; }
}

// Print out the bosses index as JSON so others can use them
if (!empty($boss_data)){ print_success_and_update_api_cache(array('boss' => $boss_data)); }
else { print_error_and_quit('The boss token `'.$api_request_token.'` has no data', MMRPG_API_ERROR_NOTFOUND); }

?>
