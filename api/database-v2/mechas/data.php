<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'mechas/index/{token}';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for mechas and then parse necessary data
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/mechas.php');
if (empty($mmrpg_database_mechas)){ print_error_and_quit('The mecha database could not be loaded'); }
$mecha_data = !empty($mmrpg_database_mechas[$api_request_token]) ? $mmrpg_database_mechas[$api_request_token] : false;

// If not empty, go through and remove or reformat api-incompatible data
if (!empty($mecha_data)){
    unset($mecha_data['_parsed']);
    unset($mecha_data['robot_id']);
    unset($mecha_data['robot_functions']);
    $mecha_data['robot_abilities_rewards'] = !empty($mecha_data['robot_rewards']['abilities']) ? $mecha_data['robot_rewards']['abilities'] : array();
    $mecha_data['robot_abilities_compatible'] = !empty($mecha_data['robot_abilities']) ? $mecha_data['robot_abilities'] : array();
    unset($mecha_data['robot_abilities'], $mecha_data['robot_rewards']);
    $backup_mecha_data = $mecha_data; $mecha_data = array();
    foreach ($backup_mecha_data AS $k => $v){ $mecha_data[str_replace('robot_', '', $k)] = $v; }
}

// Print out the mechas index as JSON so others can use them
if (!empty($mecha_data)){ print_success_and_update_api_cache(array('mecha' => $mecha_data)); }
else { print_error_and_quit('Mecha data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
