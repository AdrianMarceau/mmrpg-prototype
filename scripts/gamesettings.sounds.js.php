<?php

// Require the top file if not already included
require_once(dirname(dirname(__FILE__)).'/top.php');
session_write_close();

// If the return value was set explicitly as javascript, update headers
$return_type = !empty($_REQUEST['return']) && is_string($_REQUEST['return']) ? $_REQUEST['return'] : 'default';
$force_refresh = !empty($_REQUEST['refresh']) && $_REQUEST['refresh'] === 'true' ? true : false;
if ($return_type === 'javascript'){
    // Explicitly set the content type as javascript
    header('Content-type: text/javascript;');
    // Ensure this settings file is cached for at least a day
    $seconds_in_a_day = 86400; // 24 hours
    header("Cache-Control: public, max-age={$seconds_in_a_day}");
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + $seconds_in_a_day) . ' GMT');
    header("Pragma: cache");
}

// If there's already a cached version of this data, return that instead
$cached_files_enabled = true;
$cached_files_dir = MMRPG_CONFIG_ROOTDIR.'.cache/indexes/';
$cached_date_cutoff = substr(MMRPG_CONFIG_CACHE_DATE, 0, 8);
$cached_sound_effects_index = 'cache.game-sound-effects_index.json';
$cached_sound_effects_aliases_index = 'cache.game-sound-effects-aliases_index.json';
if (!$force_refresh
    && $cached_files_enabled
    && file_exists($cached_files_dir.$cached_sound_effects_index)
    && date('Ymd', filemtime($cached_files_dir.$cached_sound_effects_index)) >= $cached_date_cutoff){
    //error_log(basename(__FILE__).' is pulling sound effects index from cache !');
    $this_sound_effects_index = json_decode(file_get_contents($cached_files_dir.$cached_sound_effects_index));
}
if (!$force_refresh
    && $cached_files_enabled
    && file_exists($cached_files_dir.$cached_sound_effects_aliases_index)
    && date('Ymd', filemtime($cached_files_dir.$cached_sound_effects_aliases_index)) >= $cached_date_cutoff){
    //error_log(basename(__FILE__).' is pulling sound effects aliases index from cache !');
    $sound_effects_aliases_index = json_decode(file_get_contents($cached_files_dir.$cached_sound_effects_aliases_index));
}

// Define the root paths for the music and sound files
$mmrpg_sounds_path = 'prototype/sounds/';
$mmrpg_sounds_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_sounds_path;
$mmrpg_sounds_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_sounds_path;

// Collect the sound effects index from the file and then output to the JS
if (empty($this_sound_effects_index)){
    //error_log(basename(__FILE__).' is generated sound effects index from scratch ...');
    $this_sound_effects_path = $mmrpg_sounds_rootdir.'misc/sound-effects-curated/';
    $this_sound_effects_index = array();
    $this_sound_effects_index_raw = file_exists($this_sound_effects_path.'audio.json') ? json_decode(file_get_contents($this_sound_effects_path.'audio.json'), true) : array();
    if (!empty($this_sound_effects_index_raw['resources'])
        && !empty($this_sound_effects_index_raw['spritemap'])){
        $this_sound_effects_index['src'] = array();
        $this_sound_effects_index['sprite'] = array();
        foreach ($this_sound_effects_index_raw['resources'] AS $key => $resource){
            if (!preg_match('/\.(ogg|mp3)$/i', $resource)){ continue; }
            $source = 'misc/'.$resource.'?'.MMRPG_CONFIG_CACHE_DATE;
            $this_sound_effects_index['src'][] = $source;
        }
        foreach ($this_sound_effects_index_raw['spritemap'] AS $token => $spritemap){
            $sprite = array();
            $sprite['start'] = ceil($spritemap['start'] * 1000);
            $sprite['end'] = ceil($spritemap['end'] * 1000);
            $sprite['duration'] = $sprite['end'] - $sprite['start'];
            $this_sound_effects_index['sprite'][$token] = $sprite;
        }
    }
    if (!empty($this_sound_effects_index)){
        if (file_exists($cached_files_dir.$cached_sound_effects_index)){ @unlink($cached_files_dir.$cached_sound_effects_index); }
        $f = fopen($cached_files_dir.$cached_sound_effects_index, 'w');
        fwrite($f, json_encode($this_sound_effects_index, JSON_NUMERIC_CHECK || JSON_PRETTY_PRINT));
        fclose($f);
    }
}
//error_log('$this_sound_effects_index ='.print_r($this_sound_effects_index, true));
echo 'gameSettings.customIndex.soundsIndex = '.json_encode($this_sound_effects_index).';'.PHP_EOL;

// Collect the sound effect aliases index from teh file and then outpyt to the JS
if (empty($sound_effects_aliases_index)){
    //error_log(basename(__FILE__).' is generated sound effect aliases from scratch ...');
    $raw_json = trim(file_get_contents(MMRPG_CONFIG_ROOTDIR.'includes/sounds.json'));
    $raw_json = !empty($raw_json) ? preg_replace('!//.*$!m', '', $raw_json) : '';
    $raw_json_array = !empty($raw_json) ? json_decode($raw_json, true) : array();
    //error_log('$raw_json_array ='.print_r($raw_json_array, true));
    $sound_effects_aliases_index = array();
    if (!empty($raw_json_array)){
        foreach ($raw_json_array AS $sfx_category => $sfx_category_info){
            if (!empty($sfx_category_info['index'])){
                $sound_effects_aliases_index = array_merge($sound_effects_aliases_index, $sfx_category_info['index']);
            }
        }
    }
    if (!empty($sound_effects_aliases_index)){
        $f = fopen($cached_files_dir.$cached_sound_effects_aliases_index, 'w');
        fwrite($f, json_encode($sound_effects_aliases_index, JSON_NUMERIC_CHECK || JSON_PRETTY_PRINT));
        fclose($f);
    }
}
//error_log('$sound_effects_aliases_index ='.print_r($sound_effects_aliases_index, true));
echo 'gameSettings.customIndex.soundsAliasesIndex = '.json_encode($sound_effects_aliases_index).';'.PHP_EOL;

?>