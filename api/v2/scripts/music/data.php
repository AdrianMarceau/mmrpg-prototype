<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'music/index/{token}';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get an index of all music from the database
$music_data = rpg_music_track::get_index_info($api_request_token);

// If not empty, go through and remove api-incompatible data
if (!empty($music_data)){
    unset($music_data['_parsed']);
    unset($music_data['music_id']);
    unset($music_data['music_functions']);
    $backup_music_data = $music_data; $music_data = array();
    foreach ($backup_music_data AS $k => $v){ $music_data[str_replace('music_', '', $k)] = $v; }
}

// Correct the name of the "none" music to "Neutral"
if (!empty($music_data) && $music_data['token'] === 'none'){ $music_data['name'] = 'Neutral'; }

// Print out the music index as JSON so others can use them
if (!empty($music_data)){ print_success_and_update_api_cache(array('music' => $music_data)); }
else { print_error_and_quit('Type data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
