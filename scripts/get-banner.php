<?php

// Require the global config file
require('../top.php');

// Check to ensure the basic "kind" argument has been provided and is valid
$allowed_kinds = array('event');
$request_kind = !empty($_REQUEST['kind']) && in_array($_REQUEST['kind'], $allowed_kinds) ? $_REQUEST['kind'] : false;

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

// Otherwise, we can process the different kinds of banner requests
if ($request_kind === 'event'){

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
                if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(); }
                return $mmrpg_index_players;
            case 'robot': case 'robots':
                if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(); }
                return $mmrpg_index_robots;
            case 'ability': case 'abilities':
                if (empty($mmrpg_index_abilities)){ $mmrpg_index_abilities = rpg_ability::get_index(); }
                return $mmrpg_index_abilities;
            case 'item': case 'items':
                if (empty($mmrpg_index_items)){ $mmrpg_index_items = rpg_item::get_index(); }
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

    // Collect other arguments specific to event banners
    $allowed_players = array_keys(mmrpgGetIndex('players'));
    $allowed_formats = array('png');
    $request_event = !empty($_REQUEST['event']) && preg_match('/^[-_a-z0-9]+$/i', $_REQUEST['event']) ? $_REQUEST['event'] : false;
    $request_player = !empty($_REQUEST['player']) && in_array($_REQUEST['player'], $allowed_players) ? $_REQUEST['player'] : 'player';
    $request_format = !empty($_REQUEST['format']) && in_array($_REQUEST['format'], $allowed_formats) ? $_REQUEST['format'] : $allowed_formats[0];
    $force_refresh = !empty($_GET['refresh']) && $_GET['refresh'] === 'true' ? true : false;

    // Return a 404 header if either of the required arguments are missing
    if (empty($request_event)
        || empty($request_player)){
        http_response_code(404);
        exit();
    }

    // Define the cache filename and variables for event banners
    $cache_file_name = 'event-banner_'.$request_player.'_'.$request_event.'.'.$request_format;
    $cache_file_path_full = $cache_path_full.$cache_file_name;
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
            readfile($cache_file_path_full);
            exit();
        }
    }

    // A cached image could not be found, so that means we're generating anew
    //error_log('generating a NEW image file for '.$cache_file_name);

    // Define the default banner config
    $banner_config = array(
        'field_background' => 'field',
        'field_foreground' => 'field',
        );

    // If the player has requested the banner for unlocking CHAPTER 1 (Unexpected Attack)
    if ($request_event === 'chapter-1-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = rpg_player::get_starter_robot($player_token);
        $field_token = rpg_player::get_intro_field($player_token);
        $mecha_token = rpg_player::get_support_mecha($player_token);
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(
                array('kind' => 'player', 'image' => $player_token, 'frame' => 5, 'direction' => 'right', 'float' => 'left', 'left' => 200, 'bottom' => 86),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => 'met', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => 'trill', 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 210, 'bottom' => 82),
                )
            );

    }
    // Else if the player has requested the banner for unlocking CHAPTER 2 (Robot Master Revival)
    elseif ($request_event === 'chapter-2-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = rpg_player::get_starter_robot($player_token);
        $support_token = rpg_player::get_support_robot($player_token);
        $target_sprites = array(
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 160, 'bottom' => 90),
            );
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
        if ($player_token === 'dr-light'){ $rival_token = 'dr-wily'; }
        elseif ($player_token === 'dr-wily'){ $rival_token = 'dr-cossack'; }
        elseif ($player_token === 'dr-cossack'){ $rival_token = 'dr-light'; }
        $player_robot_token = rpg_player::get_starter_robot($player_token);
        $player_support_token = rpg_player::get_support_robot($player_token);
        $rival_robot_token = rpg_player::get_starter_robot($rival_token);
        $rival_support_token = rpg_player::get_support_robot($rival_token);
        $field_token = rpg_player::get_homebase_field($rival_token);
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

                array('kind' => 'player', 'image' => $rival_token, 'frame' => 4, 'direction' => 'left', 'float' => 'right', 'right' => 170, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $rival_robot_token, 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $rival_support_token, 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 200, 'bottom' => 84),
                )
            );

    }
    // Else if the player has requested the banner for unlocking CHAPTER 4 (Fusion Fields)
    elseif ($request_event === 'chapter-4-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = rpg_player::get_starter_robot($player_token);
        $support_token = rpg_player::get_support_robot($player_token);
        $target_sprites = array(
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 220, 'bottom' => 70),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 90),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 5, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
            array('kind' => 'robot', 'image' => 'robot', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 160, 'bottom' => 90),
            );
        if ($player_token === 'dr-light'){
            $field_token = 'abandoned-warehouse';
            $field_token2 = 'orb-city';
            $target_tokens = array('cut-man', 'bomb-man', 'flea', 'bombomb');
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'atomic-furnace';
            $field_token2 = 'waterfall-institute';
            $target_tokens = array('heat-man', 'bubble-man', 'telly', 'robo-fishtot');

        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'rusty-scrapheap';
            $field_token2 = 'lighting-control';
            $target_tokens = array('dust-man', 'bright-man', 'lady-blader', 'bulb-blaster');
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
    // Else if the player has requested the banner for unlocking CHAPTER 5 (The Final Battles)
    elseif ($request_event === 'chapter-5-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = rpg_player::get_starter_robot($player_token);
        $support_token = rpg_player::get_support_robot($player_token);
        if ($player_token === 'dr-light'){
            $field_token = 'final-destination';
            $defender_token = 'enker';
            $darksoul_tokens = array('mega-man-ds', 'bass-ds', 'proto-man-ds');
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'final-destination-2';
            $defender_token = 'punk';
            $darksoul_tokens = array('bass-ds', 'proto-man-ds', 'mega-man-ds');
        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'final-destination-3';
            $defender_token = 'ballade';
            $darksoul_tokens = array('proto-man-ds', 'mega-man-ds', 'bass-ds');
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 225, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $support_token, 'frame' => 7, 'direction' => 'right', 'float' => 'left', 'left' => 255, 'bottom' => 90),

                array('kind' => 'robot', 'image' => $defender_token, 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 80),
                array('kind' => 'robot', 'image' => 'slur', 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 200, 'bottom' => 82),
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
        $field_token = 'prototype-complete';
        $robot_token = rpg_player::get_starter_robot($player_token);
        $support_token = rpg_player::get_support_robot($player_token);
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
                array('kind' => 'robot', 'image' => $support_token.'_alt2', 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

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
        $robot_token = rpg_player::get_starter_robot($player_token);
        $support_token = rpg_player::get_support_robot($player_token);
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
                array('kind' => 'robot', 'image' => $support_token.'_alt2', 'frame' => 6, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

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
        if ($player_token === 'dr-light'){ $rival_token = 'dr-wily'; $rival_alt_tokens = array('flame', 'nature', 'water'); }
        elseif ($player_token === 'dr-wily'){ $rival_token = 'dr-cossack'; $rival_alt_tokens = array('water', 'flame', 'nature'); }
        elseif ($player_token === 'dr-cossack'){ $rival_token = 'dr-light'; $rival_alt_tokens = array('nature', 'water', 'flame'); }
        $player_robot_token = rpg_player::get_starter_robot($player_token);
        $player_support_token = rpg_player::get_support_robot($player_token);
        $rival_robot_token = rpg_player::get_starter_robot($rival_token);
        $rival_support_token = rpg_player::get_support_robot($rival_token);
        $field_token = rpg_player::get_homebase_field($rival_token);
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 4, 'direction' => 'right', 'float' => 'left', 'left' => 245, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $player_support_token.'_alt2', 'frame' => 7, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $player_robot_token, 'frame' => 6, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

                array('kind' => 'player', 'image' => $rival_token, 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 82),
                array('kind' => 'player', 'image' => $rival_token, 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
                array('kind' => 'player', 'image' => $rival_token, 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 177, 'bottom' => 92),
                array('kind' => 'robot', 'image' => $rival_robot_token.'_'.$rival_alt_tokens[0], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 225, 'bottom' => 80),
                array('kind' => 'robot', 'image' => $rival_robot_token.'_'.$rival_alt_tokens[1], 'frame' => 0, 'direction' => 'left', 'float' => 'right', 'right' => 100, 'bottom' => 68),
                array('kind' => 'robot', 'image' => $rival_robot_token.'_'.$rival_alt_tokens[2], 'frame' => 6, 'direction' => 'left', 'float' => 'right', 'right' => 145, 'bottom' => 90),
                )
            );

    }
    // Else if the player has requested the banner for unlocking BONUS CHAPTER CHALLENGES (Challenge Missions)
    elseif ($request_event === 'chapter-challenges-unlocked'){

        // Construct the banner config for the given event banner
        $player_token = $request_player;
        $robot_token = rpg_player::get_starter_robot($player_token);
        $support_token = rpg_player::get_support_robot($player_token);
        if ($player_token === 'dr-light'){
            $field_token = 'lighting-control';
            $field_token2 = 'prototype-subspace';
            $rival_robot_tokens = array('sheep-man', 'dynamo-man', 'elec-man');
            $rival_block_sheet = 5;
            $rival_block_frame = 4;
        } elseif ($player_token === 'dr-wily'){
            $field_token = 'serpent-column';
            $field_token2 = 'prototype-subspace';
            $rival_robot_tokens = array('slash-man', 'plant-man', 'wood-man');
            $rival_block_sheet = 8;
            $rival_block_frame = 0;
        } elseif ($player_token === 'dr-cossack'){
            $field_token = 'mineral-quarry';
            $field_token2 = 'prototype-subspace';
            $rival_robot_tokens = array('jewel-man', 'crystal-man', 'gemini-man');
            $rival_block_sheet = 5;
            $rival_block_frame = 2;
        }
        $banner_config = array(
            'field_background' => $field_token,
            'field_foreground' => $field_token2,
            'field_sprites' => array(

                array('kind' => 'player', 'image' => $player_token, 'frame' => 3, 'direction' => 'right', 'float' => 'left', 'left' => 245, 'bottom' => 82),
                array('kind' => 'robot', 'image' => $support_token.'_alt2', 'frame' => 10, 'direction' => 'right', 'float' => 'left', 'left' => 165, 'bottom' => 70),
                array('kind' => 'robot', 'image' => $robot_token, 'frame' => 8, 'direction' => 'right', 'float' => 'left', 'left' => 195, 'bottom' => 90),

                array('kind' => 'object', 'image' => 'challenge-markers/gold', 'frame' => 0, 'direction' => 'right', 'float' => 'left', 'left' => 342, 'bottom' => 90),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[0], 'frame' => 10, 'direction' => 'left', 'float' => 'right', 'right' => 260, 'bottom' => 82),
                array('kind' => 'ability', 'image' => 'super-arm-'.$rival_block_sheet, 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 220, 'bottom' => 84),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[1], 'frame' => 8, 'direction' => 'left', 'float' => 'right', 'right' => 130, 'bottom' => 70),
                array('kind' => 'ability', 'image' => 'super-arm-'.$rival_block_sheet, 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 100, 'bottom' => 72),

                array('kind' => 'robot', 'image' => $rival_robot_tokens[2], 'frame' => 1, 'direction' => 'left', 'float' => 'right', 'right' => 166, 'bottom' => 92),
                array('kind' => 'ability', 'image' => 'super-arm-'.$rival_block_sheet, 'frame' => $rival_block_frame, 'direction' => 'left', 'float' => 'right', 'right' => 136, 'bottom' => 94),
                )
            );

    }
    // Else if the user has requested the DEBUG event banner for testing purposes
    elseif ($request_event === 'debug'){

        // DEBUG DEBUG DEBUG
        $banner_config = array(
            'field_background' => 'wily-castle',
            'field_foreground' => 'prototype-complete',
            'field_sprites' => array(
                array('kind' => 'player', 'image' => 'dr-light', 'frame' => 3, 'direction' => 'right', 'float' => 'left', 'left' => 250, 'bottom' => 82),
                array('kind' => 'robot', 'image' => 'mega-man', 'frame' => 3, 'direction' => 'right', 'float' => 'left', 'left' => 190, 'bottom' => 55),
                array('kind' => 'robot', 'image' => 'roll', 'frame' => 3, 'direction' => 'right', 'float' => 'left', 'left' => 160, 'bottom' => 65),
                array('kind' => 'player', 'image' => 'dr-wily', 'frame' => 4, 'direction' => 'left', 'float' => 'right', 'right' => 250, 'bottom' => 82),
                array('kind' => 'robot', 'image' => 'proto-man', 'frame' => 4, 'direction' => 'left', 'float' => 'right', 'right' => 180, 'bottom' => 62),
                )
            );

    }
    // Otherwise if this is an undefined event banner request
    else {

        // DEBUG DEBUG DEBUG
        $banner_config = array(
            'field_background' => 'prototype-subspace',
            'field_foreground' => 'prototype-subspace',
            'field_sprites' => array(
                array('kind' => 'robot', 'image' => 'robot', 'frame' => 3, 'direction' => 'right', 'float' => 'left', 'left' => 340, 'bottom' => 68),
                )
            );

    }

    // Loop through the banner config and manually added image sizes
    if (!empty($banner_config['field_sprites'])){
        //error_log('BANNER CONFIG (BEFORE): '.print_r($banner_config, true));
        foreach ($banner_config['field_sprites'] AS $key => $sprite){
            if (empty($sprite['image'])){
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
    if (!empty($request_player)){

        // Collect the player colour info from the database
        $types_index = mmrpgGetIndex('types');
        $players_index = mmrpgGetIndex('players');
        $player_token = $request_player;
        $player_info = $players_index[$player_token];
        $player_type = $player_info['player_type'];
        $player_type_info = $types_index[$player_type];
        $banner_config['frame_colour'] = $player_type_info['type_colour_light'];
        $banner_config['frame_colour2'] = $player_type_info['type_colour_dark'];

    }

    // Use the generated banner config array to output the banner
    $banner_config['event_name'] = $request_event;
    rpg_game::generate_event_banner($banner_config, $cache_file_path_full);
    if (file_exists($cache_file_path_full)){
        // Set the image content type and then read out the file
        header('Content-Type: image/'.$request_format);
        readfile($cache_file_path_full);
        exit();
    } else {
        // There was an unknown internal error so return appropriate code
        http_response_code(500);
        exit();
    }

}


// Return a 404 header as this is an undefined request
http_response_code(404);
exit();

?>
