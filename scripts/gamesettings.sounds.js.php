<?php

// Define the root paths for the music and sound files
$mmrpg_sounds_path = 'prototype/sounds/';
$mmrpg_sounds_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_sounds_path;
$mmrpg_sounds_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_sounds_path;

// Collect the sound effects index from the file and then output to the JS
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
//error_log('$this_sound_effects_index ='.print_r($this_sound_effects_index, true));
echo 'gameSettings.customIndex.soundsIndex = '.json_encode($this_sound_effects_index).';'.PHP_EOL;

// Collect the sound effect aliases index from teh file and then outpyt to the JS
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
//error_log('$sound_effects_aliases_index ='.print_r($sound_effects_aliases_index, true));
echo 'gameSettings.customIndex.soundsAliasesIndex = '.json_encode($sound_effects_aliases_index).';'.PHP_EOL;

?>