<?php

// Require the global config file
require('../top.php');

// Check to ensure the basic "kind" argument has been provided and is valid
$allowed_kinds = array('event' => 'event-banner', 'challenge' => 'challenge-banner');
$request_kind = !empty($_REQUEST['kind']) && isset($allowed_kinds[$_REQUEST['kind']]) ? $_REQUEST['kind'] : false;
$request_name = !empty($request_kind) ? $allowed_kinds[$request_kind] : false;
$request_token = !empty($request_kind) && !empty($_REQUEST[$request_kind]) && preg_match('/^[-_a-z0-9]+$/i', $_REQUEST[$request_kind]) ? $_REQUEST[$request_kind] : false;
//error_log('$request_kind = '.$request_kind);
//error_log('$request_name = '.$request_name);
//error_log('$request_token = '.$request_token);

// Immediately return a 404 header if this is an undefined request
if (empty($request_kind)){
    http_response_code(404);
    exit();
}

// Define the cache path and variables for event banners
$cache_path = '.cache/banners/';
$cache_path_full  = MMRPG_CONFIG_ROOTDIR.$cache_path;
if (!file_exists($cache_path_full)){ mkdir($cache_path_full, 0777, true); }

// Collect the global cache time and break it down to an exact time
list($new_cache_date, $new_cache_time) = explode('-', MMRPG_CONFIG_CACHE_DATE);
$yyyy = substr($new_cache_date, 0, 4); $mm = substr($new_cache_date, 4, 2); $dd = substr($new_cache_date, 6, 2);
$hh = substr($new_cache_time, 0, 2); $ii = substr($new_cache_time, 2, 2);
$mmrpg_config_cache_time = mktime($hh, $ii, 0, $mm, $dd, $yyyy);
//error_log('$mmrpg_config_cache_time = '.print_r($mmrpg_config_cache_time, true));

// Collect an index of players and robots for validation purposes
$mmrpg_index_types = array();
$mmrpg_index_players = array();
$mmrpg_index_robots = array();
$mmrpg_index_abilities = array();
$mmrpg_index_items = array();
$mmrpg_index_objects = array();
function mmrpgGetIndex($kind){
    global $mmrpg_index_types, $mmrpg_index_players, $mmrpg_index_robots, $mmrpg_index_abilities, $mmrpg_index_items;
    switch ($kind){
        case 'type': case 'types':
            if (empty($mmrpg_index_types)){ $mmrpg_index_types = rpg_type::get_index(true); }
            return $mmrpg_index_types;
        case 'player': case 'players':
            if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }
            return $mmrpg_index_players;
        case 'robot': case 'robots':
            if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(true); }
            return $mmrpg_index_robots;
        case 'ability': case 'abilities':
            if (empty($mmrpg_index_abilities)){ $mmrpg_index_abilities = rpg_ability::get_index(true); }
            return $mmrpg_index_abilities;
        case 'item': case 'items':
            if (empty($mmrpg_index_items)){ $mmrpg_index_items = rpg_item::get_index(true); }
            return $mmrpg_index_items;
        case 'object': case 'objects':
            if (empty($mmrpg_index_objects)){
                $mmrpg_index_objects = array();
                $mmrpg_index_objects['challenge-markers'] = array(
                    'object_token' => 'challenge-markers',
                    'object_image_size' => 40
                    );
            }
            return $mmrpg_index_objects;
    }
    return array();
}

// Define the default banner config
$banner_config = array(
    'field_background' => 'field',
    'field_foreground' => 'field',
    'field_sprites' => array(),
    );

// Collect request variables specific to the kind argument provided
if ($request_kind === 'event'
    || $request_kind === 'challenge'){

    // Collect other arguments specific to event banners
    $allowed_players = array_keys(mmrpgGetIndex('players'));
    $allowed_formats = array('png');
    $request_player = !empty($_REQUEST['player']) && in_array($_REQUEST['player'], $allowed_players) ? $_REQUEST['player'] : 'player';
    $request_format = !empty($_REQUEST['format']) && in_array($_REQUEST['format'], $allowed_formats) ? $_REQUEST['format'] : $allowed_formats[0];
    $force_refresh = !empty($_GET['refresh']) && $_GET['refresh'] === 'true' ? true : false;

    // Collect event-specific banner variables to process
    $request_event = false;
    $request_challenge = false;
    if ($request_kind === 'event'){ $request_event = $request_token; }
    elseif ($request_kind === 'challenge'){ $request_challenge = $request_token; }

    // Return a 404 header if either of the required arguments are missing
    if (empty($request_player)
        || empty($request_token)){
        http_response_code(404);
        exit();
    }

    // If this is a challenge, break appart the request challenge into ID and, if provided, URL slug
    if ($request_kind === 'challenge'){

        // Pre-collect a list of IDs, names, and tokens for reference
        $published_challenges_index = $db->get_array_list("SELECT
            `challenge_id` AS `id`,
            `challenge_name` AS `name`
            FROM `mmrpg_challenges`
            WHERE
            `challenge_kind` = 'event'
            AND `challenge_flag_published` = 1
            ;", 'id');
        $published_challenges_index = !empty($published_challenges_index) ? array_map(function($info){
            $token = $info['name'];
            $token = strtolower($token);
            $token = preg_replace('/\s+/', '-', $token);
            $token = preg_replace('/[^-a-z0-9]+/i', '', $token);
            $info['token'] = $token;
            return $info;
            }, $published_challenges_index) : array();
        //error_log('$published_challenges_index = '.print_r($published_challenges_index, true));

        // Use the data from above to reconstruct our request variables
        $request_challenge_kind = 'event';
        $request_challenge_id = 0;
        $request_challenge_token = '';
        $request_challenge_regex = '/^([0-9]+)-(.*)$/i';
        if (is_numeric($request_challenge)){
            $request_challenge_id = $request_challenge;
        } elseif (preg_match($request_challenge_regex, $request_challenge)){
            $request_challenge_id = (int)(preg_replace($request_challenge_regex, '$1', $request_challenge));
        }
        if (empty($request_challenge_id)
            || !isset($published_challenges_index[$request_challenge_id])){
            http_response_code(404);
            exit();
        }
        $request_challenge_token = isset($published_challenges_index[$request_challenge_id]) ? $published_challenges_index[$request_challenge_id]['token'] : '';
        $request_token = $request_challenge_id.'-'.$request_challenge_token;
        $request_challenge = $request_token;
        //error_log('$request_challenge_kind = '.print_r($request_challenge_kind, true));
        //error_log('$request_challenge_id = '.print_r($request_challenge_id, true));
        //error_log('$request_challenge_token = '.print_r($request_challenge_token, true));
        //error_log('$request_token = '.print_r($request_token, true));

    }

    // Define the cache filename and variables for event banners
    $cache_file_name = $request_name.'_'.$request_player.'_'.$request_token.'.'.$request_format;
    $cache_file_path_full = $cache_path_full.$cache_file_name;
    //error_log('$cache_file_name: '.$cache_file_name);
    //error_log('cache_file_path_full: '.$cache_file_path_full);

    // Check to see if this image already exists and display or delete based on timestamp
    if (file_exists($cache_file_path_full)){
        $cached_image_ftime = filemtime($cache_file_path_full);
        //error_log('existing $cached_image_ftime = '.print_r($cached_image_ftime, true));
        if ($force_refresh || $cached_image_ftime < $mmrpg_config_cache_time){
            //error_log('deleting older cached image file so we can generate anew');
            unlink($cache_file_path_full);
        } else {
            //error_log('displaying the existing image as it is up-to-date and ready');
            header('Content-Type: image/'.$request_format);
            header('Content-Disposition: inline; filename="'.$cache_file_name.'"');
            readfile($cache_file_path_full);
            exit();
        }
    }

    // A cached image could not be found, so that means we're generating anew
    //error_log('generating a NEW image file for '.$cache_file_name);

}

// If this is an EVENT BANNER request, process them appropriately
if ($request_kind === 'event'){

    // If the player has requested the banner for unlocking CHAPTER 1 (Unexpected Attack)
    if ($request_event === 'chapter-1-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $field_token = $player_token !== 'player' ? rpg_player::get_intro_field($player_token) : 'field';
        $mecha_token = 'met'; //rpg_player::get_support_mecha($player_token);
        $boss_token = $player_token !== 'player' ? 'trill' : 'met';
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(
                array('kind' => 'player', 'image' => $player_token, 'frame' => 5, 'direction' => 'right', 'float' => 'left', 'left' => 200, 'bottom' => 86),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $mecha_token, 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $boss_token, 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 210, 'bottom' => 82),
                )
            );

    }
    // Else if the player has requested the banner for unlocking CHAPTER 2 (Robot Master Revival)
    elseif ($request_event === 'chapter-2-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $target_sprites = array(
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 160, 'bottom' => 90),
            );
        $field_token = 'field';
        $target_tokens = array('met', 'met', 'met');
        if ($player_token === 'dr-light'){
            $field_token = 'mountain-mines';
            $target_tokens = array('guts-man', 'picket-man', 'picket-man');
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'preserved-forest';
            $target_tokens = array('wood-man', 'batton', 'batton');

        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'robosaur-boneyard';
            $target_tokens = array('skull-man', 'skullmet_alt', 'skullmet_alt');
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(
                array('kind' => 'player', 'image' => $player_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 200, 'bottom' => 86),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 8, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $support_token, 'frame' => 8, 'direction' => 'right', 'float' => 'left', 'left' => 175, 'bottom' => 80)
                )
            );
        $robot_index = mmrpgGetIndex('robots');
        foreach ($target_sprites AS $key => $sprite){
            $token = $target_tokens[$key];
            if (strstr($token, '_')){ list($token, $alt) = explode('_', $token); }
            else { $alt = ''; }
            $robot = $robot_index[$token];
            $sprite['image'] = $token.(!empty($alt) ? '_'.$alt : '');
            $sprite['size'] = $robot['robot_image_size'];
            $banner_config['field_sprites'][] = $sprite;
        }

    }
    // Else if the player has requested the banner for unlocking CHAPTER 3 (The Rival Challengers)
    elseif ($request_event === 'chapter-3-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $rival_token = 'player';
        if ($player_token === 'dr-light'){ $rival_token = 'dr-wily'; }
        elseif ($player_token === 'dr-wily'){ $rival_token = 'dr-cossack'; }
        elseif ($player_token === 'dr-cossack'){ $rival_token = 'dr-light'; }
        $player_robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $player_support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $rival_robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($rival_token) : 'met';
        $rival_support_token = $player_token !== 'player' ? rpg_player::get_support_robot($rival_token) : 'met';
        $field_token = $player_token !== 'player' ? rpg_player::get_homebase_field($rival_token) : 'field';
        if ($player_token === 'dr-cossack'){
            $rival_token = false;
            $rival_robot_token .= '_alt9';
            $rival_support_token .= '_alt9';
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(
                array('kind' => 'player', 'image' => $player_token, 'frame' => 0, 'direction' => 'right', 'float' => 'left', 'left' => 200, 'bottom' => 86),
                array('kind' => 'robot', 'image' => $player_robot_token, 'frame' => 1, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $player_support_token, 'frame' => 10, 'direction' => 'right', 'float' => 'left', 'left' => 175, 'bottom' => 80),
                )
            );
        if ($player_token === 'dr-light'
            || $player_token === 'dr-wily'){
            $banner_config['field_sprites'][] = array('kind' => 'player', 'image' => $rival_token, 'frame' => 4, 'direction' => 'left', 'float' => 'right', 'right' => 170, 'bottom' => 80);
            $banner_config['field_sprites'][] = array('kind' => 'robot', 'image' => $rival_robot_token, 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80);
            $banner_config['field_sprites'][] = array('kind' => 'robot', 'image' => $rival_support_token, 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 200, 'bottom' => 84);
        } elseif ($player_token === 'dr-cossack'){
            $banner_config['field_sprites'][] = array('kind' => 'robot', 'image' => $rival_robot_token, 'frame' => 4, 'direction' => 'left', 'float' => 'right', 'right' => 170, 'bottom' => 80);
            $banner_config['field_sprites'][] = array('kind' => 'robot', 'image' => 'dark-frag', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80);
            $banner_config['field_sprites'][] = array('kind' => 'robot', 'image' => $rival_support_token, 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 200, 'bottom' => 84);
        }

    }
    // Else if the player has requested the banner for unlocking CHAPTER 4 (Fusion Fields)
    elseif ($request_event === 'chapter-4-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $target_sprites = array(
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 180, 'bottom' => 70),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 200, 'bottom' => 90),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 5, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 160, 'bottom' => 90),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 82),
            );
        $field_token = 'field';
        $field_token2 = 'field';
        $target_tokens = array('met', 'met', 'met', 'met');
        if ($player_token === 'dr-light'){
            $field_token = 'abandoned-warehouse';
            $field_token2 = 'orb-city';
            $target_tokens = array('cut-man_alt9', 'bomb-man_alt9', 'flea', 'bombomb', 'ra-thor_alt9');
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'atomic-furnace';
            $field_token2 = 'waterfall-institute';
            $target_tokens = array('heat-man_alt9', 'bubble-man_alt9', 'telly', 'robo-fishtot', 'ra-thor_alt9');

        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'rusty-scrapheap';
            $field_token2 = 'lighting-control';
            $target_tokens = array('dust-man_alt9', 'bright-man_alt9', 'lady-blader', 'bulb-blaster', 'ra-thor_alt9');
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token2,
            'field_sprites' => array(
                array('kind' => 'player', 'image' => $player_token, 'frame' => 6, 'direction' => 'right', 'float' => 'left', 'left' => 155, 'bottom' => 86),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 6, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $support_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 190, 'bottom' => 80)
                )
            );
        $robot_index = mmrpgGetIndex('robots');
        foreach ($target_sprites AS $key => $sprite){
            $token = $target_tokens[$key];
            if (strstr($token, '_')){ list($token, $alt) = explode('_', $token); }
            else { $alt = ''; }
            $robot = $robot_index[$token];
            $sprite['image'] = $token.(!empty($alt) ? '_'.$alt : '');
            $sprite['size'] = $robot['robot_image_size'];
            $banner_config['field_sprites'][] = $sprite;
        }

    }
    // Else if the player has requested the banner for unlocking CHAPTER 5 (The Final Destination)
    elseif ($request_event === 'chapter-5-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $field_token = 'field';
        $defender_token = 'met';
        $darksoul_tokens = array('met', 'met', 'met');
        $final_boss_token = $player_token !== 'player' ? 'slur' : 'met';
        $final_boss_image = $final_boss_token;
        if ($player_token === 'dr-light'){
            $field_token = 'final-destination';
            $defender_token = 'terra';
            $darksoul_tokens = array('mega-man-ds', 'bass-ds', 'proto-man-ds');
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'final-destination-2';
            $defender_token = 'terra';
            $darksoul_tokens = array('bass-ds', 'proto-man-ds', 'mega-man-ds');
            $final_boss_image .= '_alt';
        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'final-destination-3';
            $defender_token = 'terra';
            $darksoul_tokens = array('proto-man-ds', 'mega-man-ds', 'bass-ds');
            $final_boss_image .= '_alt2';
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 225, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $support_token, 'frame' => 7, 'direction' => 'right', 'float' => 'left', 'left' => 255, 'bottom' => 90),

                array('kind' => 'robot', 'image' => $defender_token, 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $final_boss_image, 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 200, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $darksoul_tokens[2], 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 150, 'bottom' => 90),
                array('kind' => 'robot', 'image' => $darksoul_tokens[1], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 135, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $darksoul_tokens[0], 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 120, 'bottom' => 70),

                )
            );

    }
    // Else if the player has requested the banner for unlocking BONUS CHAPTER RANDOM (Mission Randomizer)
    elseif ($request_event === 'chapter-random-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $field_token = $player_token !== 'player' ? 'prototype-complete' : 'field';
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $support_alt_token = $player_token !== 'player' ? '_alt2' : '';
        $rival_mecha_tokens = array('met', 'met', 'met');
        $rival_robot_tokens = array('met', 'met', 'met');
        if ($player_token === 'dr-light'){
            $rival_mecha_tokens = array('tackle-fire', 'met', 'pyre-fly');
            $rival_robot_tokens = array('elec-man_alt', 'fire-man_alt3', 'ice-man');
        } elseif ($player_token === 'dr-wily'){
            $rival_mecha_tokens = array('flea', 'met', 'ribbitron');
            $rival_robot_tokens = array('crash-man_alt', 'quick-man_alt', 'flash-man_alt');
        } elseif ($player_token === 'dr-cossack'){
            $rival_mecha_tokens = array('spring-head', 'met', 'crazy-cannon_alt');
            $rival_robot_tokens = array('toad-man', 'pharaoh-man_alt', 'ring-man_alt2');
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 2, 'direction' => 'right', 'float' => 'left', 'left' => 245, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 7, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $support_token.$support_alt_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

                array('kind' => 'robot', 'image' => $rival_mecha_tokens[0], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 216, 'bottom' => 66),
                array('kind' => 'robot', 'image' => $rival_mecha_tokens[1], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 242, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $rival_mecha_tokens[2], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 92),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[0], 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 105, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $rival_robot_tokens[1], 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 145, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $rival_robot_tokens[2], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 166, 'bottom' => 92),

                )
            );

    }
    // Else if the player has requested the banner for unlocking BONUS CHAPTER STARS (Star Fields)
    elseif ($request_event === 'chapter-stars-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $support_alt_token = $player_token !== 'player' ? '_alt2' : '';
        $field_token = 'field';
        $field_token2 = 'field';
        $rival_robot_tokens = array('met', 'met', 'met');
        $rival_star_types = array('none', 'none', 'none');
        if ($player_token === 'dr-light'){
            $field_token = 'reflection-chamber';
            $field_token2 = 'power-plant';
            $rival_robot_tokens = array('hard-man', 'gemini-man', 'spark-man');
            $rival_star_types = array('impact-electric', 'crystal-electric', 'electric-crystal');
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'power-plant';
            $field_token2 = 'serpent-column';
            $rival_robot_tokens = array('shadow-man', 'spark-man', 'snake-man');
            $rival_star_types = array('shadow-nature', 'electric-nature', 'nature-electric');
        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'serpent-column';
            $field_token2 = 'spinning-greenhouse';
            $rival_robot_tokens = array('magnet-man', 'snake-man', 'top-man');
            $rival_star_types = array('missile-swift', 'nature-swift', 'swift-nature');
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token2,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 1, 'direction' => 'right', 'float' => 'left', 'left' => 245, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $support_token.$support_alt_token, 'frame' => 6, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

                array('kind' => 'item', 'image' => 'fusion-star_'.$rival_star_types[0], 'frame' => 0, 'direction' => 'right', 'float' => 'left', 'left' => 344, 'bottom' => 90),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[0], 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 245, 'bottom' => 82),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[1], 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
                array('kind' => 'item', 'image' => 'fusion-star_'.$rival_star_types[1], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 100, 'bottom' => 72),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[2], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 166, 'bottom' => 92),
                array('kind' => 'item', 'image' => 'fusion-star_'.$rival_star_types[2], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 136, 'bottom' => 94),
                )
            );

    }
    // Else if the player has requested the banner for unlocking BONUS CHAPTER PLAYERS (Player Battles)
    elseif ($request_event === 'chapter-players-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $rival_token = 'player';
        $rival_image_tokens = 'player';
        $rival_alt_tokens = array('', '', '');
        if ($player_token === 'dr-light'){
            $rival_token = 'dr-wily';
            $rival_image_tokens = array('proxy_alt4', 'proxy_alt5', 'proxy_alt3');
            $rival_alt_tokens = array('_flame', '_nature', '_water');
        } elseif ($player_token === 'dr-wily'){
            $rival_token = 'dr-cossack';
            $rival_image_tokens = array('proxy_alt5', 'proxy_alt3', 'proxy_alt4');
            $rival_alt_tokens = array('_water', '_flame', '_nature');
        } elseif ($player_token === 'dr-cossack'){
            $rival_token = 'dr-light';
            $rival_image_tokens = array('proxy_alt3', 'proxy_alt4', 'proxy_alt5');
            $rival_alt_tokens = array('_nature', '_water', '_flame');
        }
        $player_robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $player_support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $player_support_alt_token = $player_token !== 'player' ? '_alt2' : '';
        $rival_robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($rival_token) : 'met';
        $rival_support_token = $player_token !== 'player' ? rpg_player::get_support_robot($rival_token) : 'met';
        $field_token = $player_token !== 'player' ? rpg_player::get_homebase_field($rival_token) : 'field';
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 245, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $player_support_token.$player_support_alt_token, 'frame' => 7, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $player_robot_token, 'frame' => 6, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

                array('kind' => 'player', 'image' => $rival_image_tokens[0], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 82),
                array('kind' => 'player', 'image' => $rival_image_tokens[1], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
                array('kind' => 'player', 'image' => $rival_image_tokens[2], 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 177, 'bottom' => 92),
                array('kind' => 'robot', 'image' => $rival_robot_token.$rival_alt_tokens[0], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 225, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $rival_robot_token.$rival_alt_tokens[1], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 100, 'bottom' => 68),
                array('kind' => 'robot', 'image' => $rival_robot_token.$rival_alt_tokens[2], 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 145, 'bottom' => 90),
                )
            );

    }
    // Else if the player has requested the banner for unlocking BONUS CHAPTER CHALLENGES (Challenge Missions)
    elseif ($request_event === 'chapter-challenges-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $support_token = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
        $support_alt_token = $player_token !== 'player' ? '_alt2' : '';
        $field_token = 'field';
        $field_token2 = 'field';
        $rival_robot_tokens = array('met', 'met', 'met', 'met');
        $rival_block_sheet = 1;
        $rival_block_frame = 0;
        if ($player_token === 'dr-light'){
            $field_token = 'power-plant';
            $field_token2 = 'space-simulator';
            $rival_robot_tokens = array('sheep-man', 'elec-man_alt9', 'spark-man_alt9', 'gravity-man_alt9');
            $rival_block_sheet = 5;
            $rival_block_frame = 4;
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'waterfall-institute';
            $field_token2 = 'arctic-jungle';
            $rival_robot_tokens = array('aqua-man', 'ice-man_alt9', 'freeze-man_alt9', 'bubble-man_alt9');
            $rival_block_sheet = 8;
            $rival_block_frame = 0;
        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'atomic-furnace';
            $field_token2 = 'preserved-forest';
            $rival_robot_tokens = array('solar-man', 'guts-man_alt9', 'hard-man_alt9', 'dive-man_alt9', 'quick-man_alt9');
            $rival_block_sheet = 5;
            $rival_block_frame = 2;
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token2,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 3, 'direction' => 'right', 'float' => 'left', 'left' => 245, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $support_token.$support_alt_token, 'frame' => 10, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 8, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

                array('kind' => 'object', 'image' => 'challenge-markers/gold', 'frame' => 0, 'direction' => 'right', 'float' => 'left', 'left' => 342, 'bottom' => 90),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[0], 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 82),
                array('kind' => 'ability', 'image' => 'super-arm'.($rival_block_sheet > 1 ? '-'.$rival_block_sheet : ''), 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 220, 'bottom' => 84),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[1], 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
                array('kind' => 'ability', 'image' => 'super-arm'.($rival_block_sheet > 1 ? '-'.$rival_block_sheet : ''), 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 100, 'bottom' => 72),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[2], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 145, 'bottom' => 82),
                array('kind' => 'ability', 'image' => 'super-arm'.($rival_block_sheet > 1 ? '-'.$rival_block_sheet : ''), 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 115, 'bottom' => 80),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[3], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 166, 'bottom' => 92),
                array('kind' => 'ability', 'image' => 'super-arm'.($rival_block_sheet > 1 ? '-'.$rival_block_sheet : ''), 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 136, 'bottom' => 94),
                )
            );

    }
    // Otherwise if this is an undefined event banner request
    else {

        // Return a not found as this isn't a valid request
        http_response_code(404);
        exit();

    }

}
// Else if this is an CHALLENGE BANNER request, process them appropriately
elseif ($request_kind === 'challenge'){

    // Collect the player and rival info as common variables
    $player_token = $request_player;
    $rival_token = 'player';
    if ($player_token === 'dr-light'){ $rival_token = 'dr-wily'; }
    elseif ($player_token === 'dr-wily'){ $rival_token = 'dr-cossack'; }
    elseif ($player_token === 'dr-cossack'){ $rival_token = 'dr-light'; }

    // Predefine some fake prototype data so we can use it for mission generation
    $this_prototype_data = array();
    $this_prototype_data['this_player_token'] = $player_token;
    $this_prototype_data['this_intro_field'] = $player_token !== 'player' ? rpg_player::get_intro_field($player_token) : 'field';
    $this_prototype_data['this_player_field'] = $player_token !== 'player' ? rpg_player::get_homebase_field($player_token) : 'field';
    $this_prototype_data['this_support_robot'] = $player_token !== 'player' ? rpg_player::get_support_robot($player_token) : 'met';
    $this_prototype_data['target_player_token'] = $rival_token;
    $this_prototype_data['this_current_chapter'] = 8;
    $this_prototype_data['battle_phase'] = 2;
    $this_prototype_data['battles_complete'] = 0;
    $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
    $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];
    $this_prototype_data['robots_unlocked'] = 1;
    $this_prototype_data['points_unlocked'] = 0;
    $this_prototype_data['prototype_complete'] = 0;
    $this_prototype_data['prev_player_token'] = '';
    $this_prototype_data['next_player_token'] = $rival_token;

    // First we need to decide if the provided lookup was a numeric ID or a string token
    $challenge_mission_info = array();
    $challenge_mission_info = rpg_mission_challenge::get_mission($this_prototype_data, $request_challenge_id, $request_challenge_kind);
    //error_log('$request_challenge_id: '.print_r($request_challenge_id, true));
    //error_log('$request_challenge_kind: '.print_r($request_challenge_kind, true));
    //error_log('challenge_mission_info: '.print_r($challenge_mission_info, true));

    // Define a quick inline function for pulling the
    function getStaticAttachmentIndex($kind, $token, $index){
        // Require the functions file if it exists
        $functions = array();
        if ($kind === 'ability'){
            $temp_functions_dir = preg_replace('/^action-/', '_actions/', $token);
            $temp_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$temp_functions_dir.'/functions.php';
            if (file_exists($temp_functions_path)){ require($temp_functions_path); }
            else { $functions = array(); }
        }
        // Return the requested index if it exists in the function array
        return isset($functions['static_index_function_'.$index]) ? $functions['static_index_function_'.$index] : false;
    }

    // Define a quick inline function for pulling the
    function getSuperBlockIndex(){ $function = getStaticAttachmentIndex('ability', 'super-arm', 'super-block_sprite-index'); return $function(array());  }
    function getSuperBlockSettings($field){ $index = getSuperBlockIndex(); return isset($index[$field]) ? $index[$field] : false; }

    // If we were able to pull data for the CHALLENGE MISSION, we can display it now
    if (!empty($challenge_mission_info)){

        // Collect references to key values in the challenge mission array
        $challenge_field_info = !empty($challenge_mission_info['battle_field_base']) ? $challenge_mission_info['battle_field_base'] : false;
        $challenge_player_info = !empty($challenge_mission_info['battle_target_player']) ? $challenge_mission_info['battle_target_player'] : false;
        $challenge_player_robots = !empty($challenge_player_info['player_robots']) ? $challenge_player_info['player_robots'] : false;
        $challenge_player_team_size = count($challenge_player_robots);
        //error_log('$challenge_field_info: '.print_r($challenge_field_info, true));
        //error_log('$challenge_player_info: '.print_r($challenge_player_info, true));
        //error_log('$challenge_player_robots: '.print_r($challenge_player_robots, true));

        // Define any hard-coded positiong variables to make things easier to work with
        $canvas_offset_side_mod = -10; // this is because the banner is wider than a normal battle and needs left-padding
        $canvas_offset_bottom_mod = -8; // this is because the foreground is cropped slightly at the bottom of the banner

        // Construct the banner config for the given event banner
        $player_number = 0;
        $player_token = $request_player;
        $next_player_token = 'player';
        if ($player_token === 'dr-light'){ $player_number = 1; $next_player_token = 'dr-wily'; }
        elseif ($player_token === 'dr-wily'){ $player_number = 2; $next_player_token = 'dr-cossack'; }
        elseif ($player_token === 'dr-cossack'){ $player_number = 3; $next_player_token = 'dr-light'; }
        $player_robot_tokens = array();
        $player_robot_tokens[] = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $player_robot_tokens[] = $player_token !== 'player' ? rpg_player::get_support_robot($player_token).'_alt2' : 'met';
        $player_robot_tokens[] = $next_player_token !== 'player' ? rpg_player::get_starter_robot($next_player_token) : 'met';
        $player_team_size = count($player_robot_tokens);
        $field_token = !empty($challenge_field_info['field_background']) ? $challenge_field_info['field_background'] : 'field';
        $field_token2 = !empty($challenge_field_info['field_foreground']) ? $challenge_field_info['field_foreground'] : 'field';
        $super_block_config = getSuperBlockSettings($field_token);
        $rival_block_sheet = !empty($super_block_config) ? $super_block_config[0] : 1;
        $rival_block_frame = !empty($super_block_config) ? $super_block_config[1] : 0;
        //error_log('$player_robot_tokens: '.print_r($player_robot_tokens, true));
        //error_log('$super_block_config: '.print_r($super_block_config, true));

        // Create an array to hold a list of occupied sprite positions
        $occupied_field_positions = array();

        // Collect the offsets for the player and modify offsets as required
        $player_canvas_offset = rpg_battle::calculate_canvas_markup_offset(0, 'active', 40, $player_team_size);
        $player_canvas_offset['canvas_offset_x'] += ceil($player_canvas_offset['canvas_offset_x'] * 0.20);
        $player_canvas_offset['canvas_offset_y'] += ceil($player_canvas_offset['canvas_offset_y'] * 0.10);
        $player_canvas_offset['canvas_offset_x'] += $canvas_offset_side_mod;
        $player_canvas_offset['canvas_offset_y'] += $canvas_offset_bottom_mod;

        // Define the basic banner config with the right field background and player characters
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token2,
            'field_sprites' => array(
                array('kind' => 'object', 'image' => 'challenge-markers/gold', 'frame' => 0, 'direction' => 'right', 'float' => 'left', 'left' => 342, 'bottom' => 90),
                array('kind' => 'player', 'image' => $player_token, 'frame' => 1, 'direction' => 'right', 'float' => 'left', 'left' => $player_canvas_offset['canvas_offset_x'], 'bottom' => $player_canvas_offset['canvas_offset_y']),
                )
            );

        // Add the player as one of the occupied positions just in case
        $occupied_field_positions[] = 'left_'.$player_canvas_offset['canvas_grid_column'].'_'.$player_canvas_offset['canvas_grid_row'];

        // Loop through the player's robots and add them to the sprite list
        if (!empty($player_robot_tokens)){
            $allowed_frames = array(4, 6, 8);
            for ($i = 1; $i < $player_number; $i++){ $allowed_frames[] = array_shift($allowed_frames); }
            foreach ($player_robot_tokens AS $robot_key => $robot_token){
                $robot_image = $robot_token;
                $robot_float = 'left';
                $robot_direction = 'right';
                $robot_position = $robot_key === 0 ? 'active' : 'bench';
                $robot_frame = $allowed_frames[$robot_key % count($allowed_frames)];
                $robot_canvas_offset = rpg_battle::calculate_canvas_markup_offset($robot_key, $robot_position, 40, $player_team_size);
                $robot_canvas_offset['canvas_position'] = $robot_position;
                $robot_canvas_offset['canvas_offset_x'] += $canvas_offset_side_mod;
                $robot_canvas_offset['canvas_offset_y'] += $canvas_offset_bottom_mod;
                $robot_sprite = array(
                    'kind' => 'robot',
                    'image' => $robot_image,
                    'frame' => $robot_frame,
                    'float' => $robot_float,
                    'direction' => $robot_direction,
                    $robot_float => $robot_canvas_offset['canvas_offset_x'],
                    'bottom' => $robot_canvas_offset['canvas_offset_y'],
                    'depth' => 0
                    );
                $robot_position_token = $robot_float.'_'.$robot_canvas_offset['canvas_grid_column'].'_'.$robot_canvas_offset['canvas_grid_row'];
                $occupied_field_positions[] = $robot_position_token;
                $banner_config['field_sprites'][] = $robot_sprite;

            }
        }

        // If this field has any robots to display (of course it does), we need to add them to the image
        if (!empty($challenge_player_robots)){
            $allowed_active_frames = array(8, 4, 6);
            for ($i = 1; $i < $player_number; $i++){ $allowed_active_frames[] = array_shift($allowed_active_frames); }
            $allowed_bench_frames = array(1, 0, 8, 10);
            for ($i = 0; $i < $player_number; $i++){ $allowed_bench_frames[] = array_shift($allowed_bench_frames); }
            foreach ($challenge_player_robots AS $robot_key => $robot_info){
                $robot_token = $robot_info['robot_token'];
                $robot_image = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_token;
                $robot_float = 'right';
                $robot_direction = 'left';
                $robot_position = $robot_key === 0 ? 'active' : 'bench';
                $robot_frame = $robot_position === 'active' ? $allowed_active_frames[$robot_key % count($allowed_bench_frames)] : $allowed_bench_frames[$robot_key % count($allowed_bench_frames)];
                $robot_canvas_offset = rpg_battle::calculate_canvas_markup_offset($robot_key, $robot_position, 40, $challenge_player_team_size);
                $robot_canvas_offset['canvas_position'] = $robot_position;
                $robot_canvas_offset['canvas_offset_x'] += $canvas_offset_side_mod;
                $robot_canvas_offset['canvas_offset_y'] += $canvas_offset_bottom_mod;
                $robot_sprite = array(
                    'kind' => 'robot',
                    'image' => $robot_image,
                    'frame' => $robot_frame,
                    'float' => $robot_float,
                    'direction' => $robot_direction,
                    $robot_float => $robot_canvas_offset['canvas_offset_x'],
                    'bottom' => $robot_canvas_offset['canvas_offset_y'],
                    'depth' => 0
                    );
                $robot_position_token = $robot_float.'_'.$robot_canvas_offset['canvas_grid_column'].'_'.$robot_canvas_offset['canvas_grid_row'];
                $occupied_field_positions[] = $robot_position_token;
                $banner_config['field_sprites'][] = $robot_sprite;
            }
        }

        // If this field has any hazards to display, we need to add them to the image
        if (!empty($challenge_field_info['values']['hazards'])){
            //error_log('$challenge_field_info[\'values\'][\'hazards\'] = '.print_r($challenge_field_info['values']['hazards'], true));

            $field_hazard_index = array();
            $positive_field_hazard_index = rpg_ability::get_positive_field_hazard_index();
            $negative_field_hazard_index = rpg_ability::get_negative_field_hazard_index();
            foreach ($positive_field_hazard_index AS $key => $info){ $field_hazard_index[$info['token']] = $info; }
            foreach ($negative_field_hazard_index AS $key => $info){ $field_hazard_index[$info['token']] = $info; }
            //error_log('$field_hazard_index = '.print_r($field_hazard_index, true));

            $hazard_template = array('kind' => 'ability', 'image' => 'ability', 'frame' => 0, 'direction' => '', 'float' => '', 'left' => 0, 'right' => 0, 'bottom' => 0, 'offset' => array());

            foreach ($challenge_field_info['values']['hazards'] AS $hazard_token => $hazard_position){

                // Start the hazard template at empty and merge in above only for supported hazards
                $this_hazard_template = array();
                $this_hazard_template = array_merge($this_hazard_template, $hazard_template);
                if ($hazard_token === ''){
                    continue;
                }
                elseif (!empty($field_hazard_index[$hazard_token])){
                    $hazard_info = $field_hazard_index[$hazard_token];
                    if ($hazard_token === 'super_blocks'){
                        $super_block_sheet = !empty($super_block_config) ? $super_block_config[0] : 1;
                        $super_block_frame = !empty($super_block_config) ? $super_block_config[1] : 0;
                        $this_hazard_template['image'] = $hazard_info['source'].($super_block_sheet > 1 ? '-'.$super_block_sheet : '');
                        $this_hazard_template['frame'] = $super_block_frame;
                        $this_hazard_template['offset'] = $hazard_info['offset'];
                    } else {
                        $this_hazard_template['image'] = $hazard_info['source'];
                        $this_hazard_template['frame'] = $hazard_info['frame'];
                        $this_hazard_template['offset'] = $hazard_info['offset'];
                    }
                }
                else {
                    $this_hazard_template = array();
                }

                // Use the harzard's position value to determine where their sprites go
                $hazard_sprite_positions = array();
                $hazard_sprites_required = array();
                if ($hazard_position === 'left'){
                    $hazard_sprites_required[] = 'left-active';
                    $hazard_sprites_required[] = 'left-bench';
                } elseif ($hazard_position === 'right'){
                    $hazard_sprites_required[] = 'right-active';
                    $hazard_sprites_required[] = 'right-bench';
                } elseif ($hazard_position === 'both'){
                    $hazard_sprites_required[] = 'left-active';
                    $hazard_sprites_required[] = 'left-bench';
                    $hazard_sprites_required[] = 'right-active';
                    $hazard_sprites_required[] = 'right-bench';
                } elseif ($hazard_position === 'both-active'){
                    $hazard_sprites_required[] = 'left-active';
                    $hazard_sprites_required[] = 'right-active';
                } elseif ($hazard_position === 'both-bench'){
                    $hazard_sprites_required[] = 'left-bench';
                    $hazard_sprites_required[] = 'right-bench';
                } else {
                    $hazard_sprites_required[] = $hazard_position;
                }

                $float_vs_directions = array('right' => 'left', 'left' => 'right');
                foreach ($float_vs_directions AS $float_token => $direction_token){
                    if (in_array($float_token.'-active', $hazard_sprites_required)){
                        $canvas_position = array('canvas_position' => 'active', 'canvas_float' => $float_token, 'canvas_direction' => $direction_token);
                        $active_position = rpg_battle::calculate_canvas_markup_offset(0, 'active', 40, $challenge_player_team_size);
                        $active_position = array_merge($canvas_position, $active_position);
                        $hazard_sprite_positions[] = $active_position;
                    }
                    if (in_array($float_token.'-bench', $hazard_sprites_required)){
                        $canvas_position = array('canvas_position' => 'bench', 'canvas_float' => $float_token, 'canvas_direction' => $direction_token);
                        for ($i = 0; $i < $challenge_player_team_size; $i++){
                            $benched_position = rpg_battle::calculate_canvas_markup_offset($i, 'bench', 40, $challenge_player_team_size);
                            $benched_position = array_merge($canvas_position, $benched_position);
                            $hazard_sprite_positions[] = $benched_position;
                            }
                    }
                }

                //error_log('$hazard_token: '.print_r($hazard_token, true));
                //error_log('$this_hazard_template: '.print_r($this_hazard_template, true));
                //error_log('$hazard_sprite_positions: '.print_r($hazard_sprite_positions, true));

                // Use the above position and template variables to append sprites to the field
                if (!empty($this_hazard_template)
                    || !empty($hazard_sprite_positions)){
                    foreach ($hazard_sprite_positions AS $sprite_key => $sprite_position){
                        $this_hazard_sprite = $this_hazard_template;
                        $this_position_token = $sprite_position['canvas_float'].'_'.$sprite_position['canvas_grid_column'].'_'.$sprite_position['canvas_grid_row'];
                        if (!in_array($this_position_token, $occupied_field_positions)){ continue; }
                        $this_hazard_sprite['float'] = $sprite_position['canvas_float'];
                        $this_hazard_sprite['direction'] = $sprite_position['canvas_direction'];
                        $this_hazard_sprite[$this_hazard_sprite['float']] = $sprite_position['canvas_offset_x'];
                        $this_hazard_sprite[$this_hazard_sprite['float']] += $canvas_offset_side_mod;
                        $this_hazard_sprite['bottom'] = $sprite_position['canvas_offset_y'];
                        $this_hazard_sprite['bottom'] += $canvas_offset_bottom_mod;
                        if (!empty($this_hazard_sprite['offset']['x'])){ $this_hazard_sprite[$this_hazard_sprite['float']] += $this_hazard_sprite['offset']['x']; }
                        if (!empty($this_hazard_sprite['offset']['y'])){ $this_hazard_sprite['bottom'] += $this_hazard_sprite['offset']['y']; }
                        $this_hazard_sprite['depth'] = isset($this_hazard_sprite['offset']['z']) ? ($this_hazard_sprite['offset']['z'] > 0 ? 1 : -1) : 0;
                        $banner_config['field_sprites'][] = array_merge($this_hazard_sprite);
                    }
                }

            }
        }

        // Update the extra frame colour based on the field type
        if (!empty($challenge_field_info['field_type'])
            || !empty($challenge_field_info['field_type2'])){
            $field_types = array();
            if (!empty($challenge_field_info['field_type'])){ $field_types[] = $challenge_field_info['field_type']; }
            if (!empty($challenge_field_info['field_type2'])){ $field_types[] = $challenge_field_info['field_type2']; }
            $field_types = array_unique($field_types);
            $types_index = mmrpgGetIndex('types');
            //error_log('$field_types: '.print_r($field_types, true));
            //error_log('$types_index: '.print_r($types_index, true));
            if (isset($field_types[0]) && isset($types_index[$field_types[0]])){ $banner_config['frame_colour'] = $types_index[$field_types[0]]['type_colour_dark']; }
            if (isset($field_types[1]) && isset($types_index[$field_types[1]])){ $banner_config['frame_colour2'] = $types_index[$field_types[1]]['type_colour_light']; }
            elseif (isset($field_types[0]) && isset($types_index[$field_types[0]])){ $banner_config['frame_colour2'] = $types_index[$field_types[0]]['type_colour_light']; }
            //error_log('$banner_config: '.print_r($banner_config, true));
        }

    }
    // Else if the player has requested the banner for unlocking CHAPTER 2 (Robot Master Revival)
    elseif ($request_challenge === 'debug'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $player_robot_token = $player_token !== 'player' ? rpg_player::get_starter_robot($player_token) : 'met';
        $field_token = $player_token !== 'player' ? rpg_player::get_intro_field($player_token) : 'field';
        $mecha_token = 'met'; //rpg_player::get_support_mecha($player_token);
        $boss_token = $player_token !== 'player' ? 'trill' : 'met';
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(
                array('kind' => 'player', 'image' => $player_token, 'frame' => 5, 'direction' => 'right', 'float' => 'left', 'left' => 200, 'bottom' => 86),
                array('kind' => 'robot', 'image' => $player_robot_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $mecha_token, 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $boss_token, 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 210, 'bottom' => 82),
                )
            );


    }
    // Otherwise if this is an undefined event banner request
    else {

        // Return a not found as this isn't a valid request
        http_response_code(404);
        exit();

    }

}

// Loop through the banner config and manually added image sizes
if (!empty($banner_config['field_sprites'])){
    //error_log('BANNER CONFIG (BEFORE): '.print_r($banner_config, true));
    foreach ($banner_config['field_sprites'] AS $key => $sprite){
        if (empty($sprite['image'])
            || $sprite['image'] === $sprite['kind']){
            unset($banner_config['field_sprites'][$key]);
            continue;
        }
        $token = $sprite['image'];
        $sheet = $alt = '';
        if (strstr($token, '/')){ list($token, $sheet) = explode('/', $token); }
        if (strstr($token, '_')){ list($token, $alt) = explode('_', $token); }
        elseif (preg_match('/-([0-9]+)$/', $token)){ list($token, $alt) = explode('_', preg_replace('/^([-_a-z0-9]+)-([0-9]+)$/', '$1_$2', $token)); }
        $index = array();
        if ($sprite['kind'] === 'player'){ $index = mmrpgGetIndex('players');  }
        elseif ($sprite['kind'] === 'robot'){ $index = mmrpgGetIndex('robots'); }
        elseif ($sprite['kind'] === 'ability'){ $index = mmrpgGetIndex('abilities'); }
        elseif ($sprite['kind'] === 'item'){ $index = mmrpgGetIndex('items'); }
        elseif ($sprite['kind'] === 'object'){ $index = mmrpgGetIndex('objects'); }
        if (empty($index)
            || empty($index[$token])){
            unset($banner_config['field_sprites'][$key]);
            continue;
        }
        $info = $index[$token];
        if (isset($info[$sprite['kind'].'_image_size'])){ $sprite['size'] = $info[$sprite['kind'].'_image_size']; }
        else { $sprite['size'] = 40; }
        $banner_config['field_sprites'][$key] = $sprite;
    }
    $banner_config['field_sprites'] = array_values($banner_config['field_sprites']);
    //error_log('BANNER CONFIG (AFTER): '.print_r($banner_config, true));
}

// Make sure we apply the proper player frame if applicable
if ($request_kind === 'event'
    || $request_kind === 'challenge'){
    if (!empty($request_player)){

        // Collect the player colour info from the database
        $types_index = mmrpgGetIndex('types');
        $players_index = mmrpgGetIndex('players');
        $player_token = $request_player;
        $player_info = $player_token !== 'player' ? $players_index[$player_token] : false;
        $player_type = $player_token !== 'player' && !empty($player_info) ? $player_info['player_type'] : 'empty';
        $player_type_info = $types_index[$player_type];
        if (!isset($banner_config['frame_colour'])){ $banner_config['frame_colour'] = $player_type_info['type_colour_light']; }
        if (!isset($banner_config['frame_colour2'])){ $banner_config['frame_colour2'] = $player_type_info['type_colour_dark']; }

    }
}

// Use the generated banner config array to output the banner
$banner_config['banner_kind'] = $request_kind;
$banner_config['banner_token'] = $request_token;
rpg_game::generate_event_banner($banner_config, $cache_file_path_full);
if (file_exists($cache_file_path_full)){
    // Set the image content type and then read out the file
    header('Content-Type: image/'.$request_format);
    header('Content-Disposition: inline; filename="'.$cache_file_name.'"');
    readfile($cache_file_path_full);
    exit();
} else {
    // There was an unknown internal error so return appropriate code
    http_response_code(500);
    exit();
}


// Return a 404 header as this is an undefined request
http_response_code(404);
exit();

?>
