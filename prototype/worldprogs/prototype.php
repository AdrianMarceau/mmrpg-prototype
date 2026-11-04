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

    // If Reggae has not been interacted with yet, make sure we set him to first-encounter
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

// If the mechanic PLUM is currently on this map
$actor_token = 'plum';
$actor_namekey = 'field-reporter-plum';
if (!empty($map_data_parsed['actors'])
    && !empty($map_data_parsed['actors'][$actor_namekey])){

    // Check to see if Plum is unlocked yet via the Plum Link
    $actor_is_unlocked = mmrpg_prototype_item_unlocked($actor_token.'-link');
    if (!$actor_is_unlocked){ unset($map_data_parsed['actors'][$actor_namekey]); }
    //error_log('$map_data_parsed[\'actors\'] = '.print_r($map_data_parsed['actors'], true));

    // If Plum has not been interacted with yet, make sure we set her to first-encounter
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

// -- WORLD STATIC ENCOUNTERS -- //

// Manually define specific encounter parameters for unique bosses
rpg_world::$encounter_overrides['boss']['trill'] = array(
    'item' => 'super-capsule',
    'abilities' => array(
        'space-shot', 'space-overdrive',
        'attack-boost', 'attack-break',
        'defense-boost', 'defense-break',
        'speed-boost', 'speed-break',
        'mecha-support',
        )
    );
rpg_world::$encounter_overrides['boss']['slur'] = array(
    'level' => 250,
    'item' => 'persist-module',
    'abilities' => array(
        'space-shot', 'space-buster', 'space-overdrive',
        'time-buster', 'shadow-buster', 'flame-buster', 'freeze-buster',
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        'mecha-support', 'energy-boost'
        ),
    );

// -- WORLD STATIC RESCUES -- //

// Check to see if anyone has had any of the animation companions at least one
$database_robot_records = mmrpg_prototype_database_records();
$anyone_had_rush = !empty($database_robot_records['rush']['robot_unlocked']) || !empty($database_robot_records['rush']['robot_summoned']);
$anyone_had_treble = !empty($database_robot_records['treble']['robot_unlocked']) || !empty($database_robot_records['treble']['robot_summoned']);
$anyone_had_tango = !empty($database_robot_records['tango']['robot_unlocked']) || !empty($database_robot_records['tango']['robot_summoned']);
$anyone_had_beat = !empty($database_robot_records['beat']['robot_unlocked']) || !empty($database_robot_records['beat']['robot_summoned']);

// Check to see if anyone already has any of the animal companions beforehand
$unlocked_robot_counters = mmrpg_prototype_robots_unlocked_counters();
$anyone_has_rush = !empty($unlocked_robot_counters['rush']);
$anyone_has_treble = !empty($unlocked_robot_counters['treble']);
$anyone_has_tango = !empty($unlocked_robot_counters['tango']);
$anyone_has_beat = !empty($unlocked_robot_counters['beat']);
//error_log('$unlocked_robot_counters = '.print_r($unlocked_robot_counters, true));

// Dynamically set the level for the Rush rescue mission
rpg_world::$encounter_overrides['rescue']['good-boy-rush'] = array(
    'level' => mmrpg_prototype_get_player_robot_level_stats('dr-light')['average'],
    'hidden' => !$anyone_had_rush || $anyone_has_rush
    );

// Dynamically set the level for the Treble rescue mission
rpg_world::$encounter_overrides['rescue']['loyal-wolf-treble'] = array(
    'level' => mmrpg_prototype_get_player_robot_level_stats('dr-wily')['average'],
    'hidden' => !$anyone_had_treble || $anyone_has_treble
    );

// Dynamically set the level for the Tango rescue mission
rpg_world::$encounter_overrides['rescue']['frisky-cat-tango'] = array(
    'level' => mmrpg_prototype_get_player_robot_level_stats('dr-cossack')['average'],
    'hidden' => !$anyone_had_tango || $anyone_has_tango
    );

// Dynamically set the level for the Beat rescue mission
rpg_world::$encounter_overrides['rescue']['barrier-bird-beat'] = array(
    'level' => mmrpg_prototype_get_player_robot_level_stats('dr-lalinde')['average'],
    'hidden' => !$anyone_had_beat || $anyone_has_beat
    );

?>