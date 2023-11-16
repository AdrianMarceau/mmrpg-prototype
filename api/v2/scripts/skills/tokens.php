<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'skills';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get a list of all skill tokens from the database
$skill_tokens = rpg_skill::get_index_tokens(true, false, $api_include_hidden, true);

// Print out the skill tokens as JSON so others can use them
if (!empty($skill_tokens)){ print_success_and_update_api_cache(array('skills' => $skill_tokens, 'total' => count($skill_tokens))); }
else { print_error_and_quit('The skill token array was empty'); }

?>
