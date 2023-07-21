<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'music';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get a list of all music tokens from the database
$music_tokens = rpg_music_track::get_index_tokens(true, false, $api_include_hidden, true);

// Print out the music tokens as JSON so others can use them
if (!empty($music_tokens)){ print_success_and_update_api_cache(array('music' => $music_tokens, 'total' => count($music_tokens))); }
else { print_error_and_quit('The music token array was empty'); }

?>
