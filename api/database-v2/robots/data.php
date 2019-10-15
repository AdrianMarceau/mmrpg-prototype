<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'robots/index/{token}';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for robots and then parse necessary data
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
if (empty($mmrpg_database_robots)){ print_error_and_quit('The robot database could not be loaded'); }
$robot_data = !empty($mmrpg_database_robots[$api_request_token]) ? $mmrpg_database_robots[$api_request_token] : false;

// If not empty, go through and remove or reformat api-incompatible data
if (!empty($robot_data)){
    unset($robot_data['_parsed']);
    unset($robot_data['robot_id']);
    unset($robot_data['robot_functions']);
    $robot_data['robot_abilities_rewards'] = !empty($robot_data['robot_rewards']['abilities']) ? $robot_data['robot_rewards']['abilities'] : array();
    $robot_data['robot_abilities_compatible'] = !empty($robot_data['robot_abilities']) ? $robot_data['robot_abilities'] : array();
    unset($robot_data['robot_abilities'], $robot_data['robot_rewards']);
    $backup_robot_data = $robot_data; $robot_data = array();
    foreach ($backup_robot_data AS $k => $v){ $robot_data[str_replace('robot_', '', $k)] = $v; }
}

// Print out the robots index as JSON so others can use them
if (!empty($robot_data)){ print_success_and_update_api_cache(array('robot' => $robot_data)); }
else { print_error_and_quit('Robot data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
