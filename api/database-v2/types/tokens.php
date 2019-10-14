<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'types';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Get a list of all type tokens from the database
$type_tokens = rpg_type::get_index_tokens(true, false, false, true);

// Print out the type tokens as JSON so others can use them
if (!empty($type_tokens)){ print_success_and_update_api_cache(array('types' => $type_tokens, 'total' => count($type_tokens))); }
else { print_success_and_update_api_cache('Field tokens could not be loaded from the database!'); }

?>
