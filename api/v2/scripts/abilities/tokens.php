<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'abilities';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for abilities and then parse necessary data
$mmrpg_database_abilities_filter = "AND ability_flag_published = 1 ";
if (!$api_include_hidden){ $mmrpg_database_abilities_filter = "AND ability_flag_hidden = 0 "; }
if (!$api_include_incomplete){ $mmrpg_database_abilities_filter = "AND ability_flag_complete = 1 "; }
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');
if (empty($mmrpg_database_abilities)){ print_error_and_quit('The ability database could not be loaded'); }
$ability_tokens = !empty($mmrpg_database_abilities) ? array_keys($mmrpg_database_abilities) : array();

// Print out the ability tokens as JSON so others can use them
if (!empty($ability_tokens)){ print_success_and_update_api_cache(array('abilities' => $ability_tokens, 'total' => count($ability_tokens))); }
else { print_error_and_quit('The ability token array was empty'); }

?>
