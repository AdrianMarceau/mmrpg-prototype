<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'bosses';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for bosses and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_BOSSES', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_BOSSES', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/bosses.php');
if (empty($mmrpg_database_bosses)){ print_error_and_quit('The boss database could not be loaded'); }
$boss_tokens = !empty($mmrpg_database_bosses) ? array_keys($mmrpg_database_bosses) : array();

// Print out the boss tokens as JSON so others can use them
if (!empty($boss_tokens)){ print_success_and_update_api_cache(array('bosses' => $boss_tokens, 'total' => count($boss_tokens))); }
else { print_error_and_quit('The boss token array was empty'); }

?>
