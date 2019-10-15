<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'types';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get a list of all type tokens from the database
$type_tokens = rpg_type::get_index_tokens(true, false, $api_include_hidden, true);

// Print out the type tokens as JSON so others can use them
if (!empty($type_tokens)){ print_success_and_update_api_cache(array('types' => $type_tokens, 'total' => count($type_tokens))); }
else { print_error_and_quit('The type token array was empty'); }

?>
