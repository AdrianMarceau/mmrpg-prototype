<?

// Collect quick references to the current world and map for checking
$this_current_world = $this_prototype_data['this_current_world'];
$this_current_map = $this_prototype_data['this_current_map'];

// If the reset guy ANTI-EDDIE is currently on this map
$actor_token = 'anti-eddie';
$actor_namekey = 'purple-reset-guy';
if (!empty($map_data_parsed['actors'])
    && !empty($map_data_parsed['actors'][$actor_namekey])){

    // If Auto has not been interacted with yet, make sure we set him to first-encounter
    $actor_interacted_with = !empty($world_map_actors[$actor_namekey]) ? true : false;
    if (!$actor_interacted_with){ $map_data_parsed['actors'][$actor_namekey][4] = 'first-encounter'; }
    //error_log('$world_map_actors = '.print_r($world_map_actors, true));

}

// If the shopkeeper AUTO is currently on this map
$actor_token = 'auto';
$actor_namekey = 'good-guy-auto';
if (!empty($map_data_parsed['actors'])
    && !empty($map_data_parsed['actors'][$actor_namekey])){

    // Check to see if Auto is unlocked yet via the Auto Link
    $actor_is_unlocked = mmrpg_prototype_item_unlocked($actor_token.'-link');
    if (!$actor_is_unlocked){ unset($map_data_parsed['actors'][$actor_namekey]); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

    // If Auto has not been interacted with yet, make sure we set him to first-encounter
    $actor_interacted_with = !empty($world_map_actors[$actor_namekey]) ? true : false;
    if ($actor_is_unlocked && !$actor_interacted_with){ $map_data_parsed['actors'][$actor_namekey][4] = 'first-encounter'; }
    //error_log('$world_map_actors = '.print_r($world_map_actors, true));

}

// If the shopkeeper REGGAE is currently on this map
$actor_token = 'reggae';
$actor_namekey = 'weird-bird-reggae';
if (!empty($map_data_parsed['actors'])
    && !empty($map_data_parsed['actors'][$actor_namekey])){

    // Check to see if Reggae is unlocked yet via the Reggae Link
    $actor_is_unlocked = mmrpg_prototype_item_unlocked($actor_token.'-link');
    if (!$actor_is_unlocked){ unset($map_data_parsed['actors'][$actor_namekey]); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

    // If Reggae has not been interacted with yet, make sure we set it to first-encounter
    $actor_interacted_with = !empty($world_map_actors[$actor_namekey]) ? true : false;
    if ($actor_is_unlocked && !$actor_interacted_with){ $map_data_parsed['actors'][$actor_namekey][4] = 'first-encounter'; }
    //error_log('$world_map_actors = '.print_r($world_map_actors, true));

}

// If the shopkeeper KALINKA is currently on this map
$actor_token = 'kalinka';
$actor_namekey = 'star-gazer-kalinka';
if (!empty($map_data_parsed['actors'])
    && !empty($map_data_parsed['actors'][$actor_namekey])){

    // Check to see if Kalinka is unlocked yet via the Kalinka Link
    $actor_is_unlocked = mmrpg_prototype_item_unlocked($actor_token.'-link');
    if (!$actor_is_unlocked){ unset($map_data_parsed['actors'][$actor_namekey]); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

    // If Kalinka has not been interacted with yet, make sure we set her to first-encounter
    $actor_interacted_with = !empty($world_map_actors[$actor_namekey]) ? true : false;
    if ($actor_is_unlocked && !$actor_interacted_with){ $map_data_parsed['actors'][$actor_namekey][4] = 'first-encounter'; }
    //error_log('$world_map_actors = '.print_r($world_map_actors, true));

}


// If the the final PROXY is currently on this map
$actor_token = 'proxy';
$actor_namekey = 'postgame-area-proxy';
if (!empty($map_data_parsed['actors'])
    && !empty($map_data_parsed['actors'][$actor_namekey])){

    // If Auto has not been interacted with yet, make sure we set him to first-encounter
    $actor_interacted_with = !empty($world_map_actors[$actor_namekey]) ? true : false;
    if (!$actor_interacted_with){ $map_data_parsed['actors'][$actor_namekey][4] = 'first-encounter'; }
    //error_log('$world_map_actors = '.print_r($world_map_actors, true));

}


// If we're in LIGHT Area 1, make sure we make appropriate adjustments
if ($this_current_map === 'light-area-1'){

    // ...

}

// If we're in WILY Area 1, make sure we make appropriate adjustments
if ($this_current_map === 'wily-area-1'){

    // ...

}

// If we're in COSSACK Area 1, make sure we make appropriate adjustments
if ($this_current_map === 'cossack-area-1'){

    // ...

}

// If we are on the main hub map (PROTOTYPE Area 0)
if ($this_current_map === 'prototype-area-0') {

    // Define the unlock tokens required for each portal to appear
    $doctor_unlock_tokens = [
        'light' => 'dr-light',
        'wily' => 'dr-wily',
        'cossack' => 'dr-cossack',
        'lalinde' => 'dr-lalinde'
        ];

    // Loop through and hide portals if the player hasn't unlocked the doctor yet
    foreach ($doctor_unlock_tokens as $prefix => $player_token) {
        $portal_namekey = $prefix.'-home-portal';

        // If this portal exists in the generated map data
        if (isset($map_data_parsed['portals'][$portal_namekey])) {

            // Check if the player has actually unlocked this doctor
            // (Note: If you track area unlocks instead of player unlocks, you can swap this function!)
            $is_unlocked = mmrpg_prototype_player_unlocked($player_token);

            // If they haven't traveled there / unlocked them yet, push 'hidden' into the portal's arg array
            if (!$is_unlocked) {
                $map_data_parsed['portals'][$portal_namekey][] = 'hidden';
            }
        }
    }
}

?>