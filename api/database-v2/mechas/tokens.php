<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'mechas';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for mechas and then parse necessary data
$mmrpg_database_mechas_filter = "AND robot_flag_hidden = 0 AND robot_flag_complete = 1 AND robot_flag_published = 1 ";
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/mechas.php');
if (empty($mmrpg_database_mechas)){ print_error_and_quit('The mecha database could not be loaded'); }
$mecha_tokens = !empty($mmrpg_database_mechas) ? array_keys($mmrpg_database_mechas) : array();

// Print out the mecha tokens as JSON so others can use them
if (!empty($mecha_tokens)){ print_success_and_update_api_cache(array('mechas' => $mecha_tokens, 'total' => count($mecha_tokens))); }
else { print_error_and_quit('The mecha token array was empty'); }

?>
