<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'robots';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for robots and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_ROBOTS', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_ROBOTS', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
if (empty($mmrpg_database_robots)){ print_error_and_quit('The robot database could not be loaded'); }
$robot_tokens = !empty($mmrpg_database_robots) ? array_keys($mmrpg_database_robots) : array();

// Print out the robot tokens as JSON so others can use them
if (!empty($robot_tokens)){ print_success_and_update_api_cache(array('robots' => $robot_tokens, 'total' => count($robot_tokens))); }
else { print_error_and_quit('The robot token array was empty'); }

?>
