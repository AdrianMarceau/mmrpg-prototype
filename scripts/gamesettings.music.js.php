<?php

// Define the root paths for the music and sound files
$mmrpg_music_path = 'prototype/sounds/';
$mmrpg_music_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_music_path;
$mmrpg_music_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_music_path;

// Collect the music index from the database and then output to the JS
$this_music_track_index = rpg_music_track::get_index(true, false, 'music_token', 'music_album+/+music_token');
$this_music_track_index = array_map(function($info){ return array(
    'token' => $info['music_token'],
    'album' => $info['music_album'],
    'game' => $info['music_game'],
    'name' => $info['music_name'],
    'link' => $info['music_link'],
    'loop' => $info['music_loop'],
    'order' => $info['music_order']
    ); }, $this_music_track_index);
//error_log('$this_music_track_index ='.print_r($this_music_track_index, true));
echo 'gameSettings.customIndex.musicIndex = '.json_encode($this_music_track_index).';'.PHP_EOL;

?>