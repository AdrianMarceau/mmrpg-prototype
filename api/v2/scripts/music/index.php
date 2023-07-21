<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'music/index';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get an index of all music from the database
$music_index = rpg_music_track::get_index(true, $api_include_hidden, 'music_token');

// If not empty, loop through and remove api-incompatible data
if (!empty($music_index)){
    $new_music_index = array();
    foreach ($music_index AS $music_token => $music_data){
        unset($music_data['_parsed']);
        unset($music_data['music_id']);
        unset($music_data['music_functions']);
        $backup_music_data = $music_data; $music_data = array();
        foreach ($backup_music_data AS $k => $v){ $music_data[str_replace('music_', '', $k)] = $v; }
        $lookup_token = $music_data['album'].'/'.$music_token;
        $new_music_index[$lookup_token] = $music_data;
    }
    $music_index = $new_music_index;
}

// Print out the music index as JSON so others can use them
if (!empty($music_index)){ print_success_and_update_api_cache(array('music' => $music_index, 'total' => count($music_index))); }
else { print_success_and_update_api_cache('The music index array was empty'); }

?>
