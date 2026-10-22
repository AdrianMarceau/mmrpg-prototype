<?

// Collect quick references to the current world and map for checking
$this_current_world = $this_prototype_data['this_current_world'];
$this_current_map = $this_prototype_data['this_current_map'];

// If we're in LIGHT Area 1, make sure Auto doesn't appear until unlocked
if ($this_current_map === 'light-area-1'){

    // Check to see if Auto is unlocked yet via the Auto Link
    $auto_is_unlocked = mmrpg_prototype_item_unlocked('auto-link');
    if (!$auto_is_unlocked){ unset($map_data_parsed['actors']['good-guy-auto']); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

    /*
    // If Dr. Light has not been unlocked, don't show Eddie yet
    $light_is_unlocked = mmrpg_prototype_player_unlocked('dr-light');
    if (!$light_is_unlocked){
        foreach ($map_data_parsed['rescues'] AS $key => $data){
            if ($data[1] !== 'eddie'){ continue; }
            unset($map_data_parsed['rescues'][$key]);
            rpg_world::$skip_worldmap_actors[] = $key;
            break;
        }
        foreach ($world_map_encounters AS $key => $data){
            if ($data[1] !== 'eddie'){ continue; }
            unset($world_map_encounters[$key]);
            rpg_world::$skip_worldmap_encounters[] = $data[4];
            break;
        }
    }
    //error_log('$map_data_parsed[\'rescues\'] = '.print_r($map_data_parsed['rescues'], true));
    //error_log('$world_map_encounters = '.print_r($world_map_encounters, true));
    //error_log('rpg_world::$skip_worldmap_encounters = '.print_r(rpg_world::$skip_worldmap_encounters, true));
    */

}

// If we're in WILY Area 1, make sure Reggae doesn't appear until unlocked
if ($this_current_map === 'wily-area-1'){

    // Check to see if Reggae is unlocked yet via the Reggae Link
    $reggae_is_unlocked = mmrpg_prototype_item_unlocked('reggae-link');
    if (!$reggae_is_unlocked){ unset($map_data_parsed['actors']['weird-bird-reggae']); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

}

// If we're in COSSACK Area 1, make sure Kalinka doesn't appear until unlocked
if ($this_current_map === 'cossack-area-1'){

    // Check to see if Kalinka is unlocked yet via the Kalinka Link
    $kalinka_is_unlocked = mmrpg_prototype_item_unlocked('kalinka-link');
    if (!$kalinka_is_unlocked){ unset($map_data_parsed['actors']['star-gazer-kalinka']); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

}

?>