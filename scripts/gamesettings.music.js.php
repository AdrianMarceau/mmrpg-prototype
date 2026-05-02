<?php

// Require the top file if not already included
require_once(dirname(dirname(__FILE__)).'/top.php');

// If the return value was set explicitly as javascript, update headers
$return_type = !empty($_REQUEST['return']) && is_string($_REQUEST['return']) ? $_REQUEST['return'] : 'default';
if ($return_type === 'javascript'){
    // Explicitly set the content type as javascript
    header('Content-type: text/javascript;');
    // Ensure this settings file is never cached as it changes
    $seconds_in_a_day = 86400; // 24 hours
    header("Cache-Control: public, max-age={$seconds_in_a_day}");
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + $seconds_in_a_day) . ' GMT');
    header("Pragma: cache");
}

// If there's already a cached version of this data, return that instead
$cached_files_enabled = true;
$cached_files_dir = MMRPG_CONFIG_ROOTDIR.'.cache/indexes/';
$cached_date_cutoff = substr(MMRPG_CONFIG_CACHE_DATE, 0, 8);
$cached_music_tracks_index = 'cache.game-music-tracks_index.json';
if ($cached_files_enabled
    && file_exists($cached_files_dir.$cached_music_tracks_index)
    && date('Ymd', filemtime($cached_files_dir.$cached_music_tracks_index)) >= $cached_date_cutoff){
    //error_log(basename(__FILE__).' is pulling music tracks index from cache !');
    $this_music_track_index = json_decode(file_get_contents($cached_files_dir.$cached_music_tracks_index));
}

// Define the root paths for the music and sound files
$mmrpg_music_path = 'prototype/sounds/';
$mmrpg_music_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_music_path;
$mmrpg_music_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_music_path;

// Collect the music index from the database and then output to the JS
if (empty($this_music_track_index)){
    //error_log(basename(__FILE__).' is generated music tracks index from scratch ...');
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
    if (!empty($this_music_track_index)){
        if (file_exists($cached_files_dir.$cached_music_tracks_index)){ @unlink($cached_files_dir.$cached_music_tracks_index); }
        $f = fopen($cached_files_dir.$cached_music_tracks_index, 'w');
        fwrite($f, json_encode($this_music_track_index, JSON_NUMERIC_CHECK || JSON_PRETTY_PRINT));
        fclose($f);
    }
}
//error_log('$this_music_track_index ='.print_r($this_music_track_index, true));
echo 'gameSettings.customIndex.musicIndex = '.json_encode($this_music_track_index).';'.PHP_EOL;

?>