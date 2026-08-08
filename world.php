<?php

// Include the TOP file
require_once('top.php');
//error_log('---------------------------------');

// Adjust some important settings to prevent server issues
set_time_limit(30);

// Automatically empty temporary session vars from this or other pages
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['SKILLS'] = array();
$_SESSION['PROTOTYPE_TEMP'] = array();
$_SESSION['WORLD_TEMP'] = array();

// If a reset action was requested, we should reset the world session and redirect
//error_log('$_GET = '. print_r($_GET, true));
//error_log('$_POST = '. print_r($_POST, true));
if (!empty($_REQUEST['reset'])
    && $_REQUEST['reset'] === 'world'){
    rpg_world::reset_session();
    header('Location: world.php');
    exit();
}

// Preset critical world-map constants before we do anything else
//$mapfile_basepath = 'prototype/worldmaps/';
//rpg_world::set_basepath($mapfile_basepath);
$mapfile_basedir = rpg_world::$worldmap_basedir.rpg_world::$worldmap_basepath;
//error_log('$mapfile_basedir = '. print_r($mapfile_basedir, true));

// Collect the game session token in case we need it later
$game_session_token = rpg_game::session_token();
$GAME_SESSION = &$_SESSION[$game_session_token];

// Define a reference object for storing temporary world data
$world_session_token = rpg_world::session_token();
$WORLD_SESSION = &$_SESSION[$world_session_token];
rpg_world::init_session();

// Scan for allowed world directories and the map + sheet files within
//error_log('scanning for existing worldmap files ...');
$allowed_map_tokens = array();
$existing_world_dirs = glob($mapfile_basedir.'*', GLOB_ONLYDIR);
$existing_world_map_files = array();
$existing_world_sheet_files = array();
if (!empty($existing_world_dirs)){
    $existing_world_dirs = array_map(function($path){ return basename($path); }, $existing_world_dirs);
    foreach ($existing_world_dirs AS $key => $world){
        $basedir = $mapfile_basedir.$world.'/';
        $mapfiles = glob($basedir.'*.map');
        $sheetfiles = glob($basedir.'*.sheet');
        if (!empty($mapfiles)){
            $mapfiles = array_map(function($path)use($basedir){ return str_replace('/', '__', preg_replace('/\.map$/i', '', str_replace($basedir, '', $path))); }, $mapfiles);
            $existing_world_map_files[$world] = $mapfiles;
        }
        if (!empty($sheetfiles)){
            $sheetfiles = array_map(function($path)use($basedir){ return str_replace('/', '__', preg_replace('/\.sheet$/i', '', str_replace($basedir, '', $path))); }, $sheetfiles);
            $existing_world_sheet_files[$world] = $sheetfiles;
        }
    }
}
//error_log('$existing_world_dirs = '. print_r($existing_world_dirs, true));
//error_log('$existing_world_map_files = '. print_r($existing_world_map_files, true));
//error_log('$existing_world_sheet_files = '. print_r($existing_world_sheet_files, true));
$allowed_world_tokens = array();
$allowed_world_map_tokens = array();
$allowed_world_sheet_tokens = array();
if (!empty($existing_world_dirs)){
    foreach ($existing_world_dirs AS $world){
        $mapfiles = !empty($existing_world_map_files[$world]) ? $existing_world_map_files[$world] : array();
        $sheetfiles = !empty($existing_world_sheet_files[$world]) ? $existing_world_sheet_files[$world] : array();
        if (!$mapfiles && !$sheetfiles){ continue; }
        $allowed_world_tokens[] = $world;
        foreach ($mapfiles AS $map){ $allowed_world_map_tokens[] = $world.'__'.$map; }
        foreach ($sheetfiles AS $sheet){ $allowed_world_sheet_tokens[] = $world.'__'.$sheet; }
    }
}
//error_log('$allowed_world_tokens = '. print_r($allowed_world_tokens, true));
//error_log('$allowed_world_map_tokens = '. print_r($allowed_world_map_tokens, true));
//error_log('$allowed_world_sheet_tokens = '. print_r($allowed_world_sheet_tokens, true));
//exit();

// Define which player tokens are allowed to be used in the prototype world
$allowed_player_tokens = mmrpg_prototype_players_unlocked(true);
array_unshift($allowed_player_tokens, 'player'); // always allow the "player" token

// Define which robot tokens are allowed to be used in the prototype world
$allowed_robot_tokens = mmrpg_prototype_robots_unlocked('', true);

// Define defaults for the prototype world data
//$default_world_token = 'debug__debug-area-1';
$default_world_token = 'debug';
$default_map_token = 'debug-area-1';
$default_player_token = 'player';
$default_world_position = '';
$default_world_direction = '';

// If a save action was requested, we should do it here and then return exit
if (!empty($_POST['action']) && $_POST['action'] === 'save'
    && !empty($_POST['world_data']) && is_array($_POST['world_data'])){
    $worldData = $_POST['world_data'];
    // save the provided world data to the session (validation happens there)
    rpg_world::save_world_data_to_session($worldData, array(
        'world_tokens' => $allowed_world_tokens,
        'world_map_tokens' => $allowed_world_map_tokens,
        'world_sheet_tokens' => $allowed_world_sheet_tokens,
        'player_tokens' => $allowed_player_tokens,
        'robot_tokens' => $allowed_robot_tokens,
        ));
    // save the session with any new changes we just made
    mmrpg_save_game_session();
    // Now that we're done saving, return a success response
    header('Content-Type: application/json');
    echo(json_encode(array('status' => 'success', 'message' => 'World data saved successfully.')));
    exit();
}

// Pull in a few indexes that we'll need for below
$mmrpg_index_types = rpg_type::get_index(true);
$mmrpg_index_fields = rpg_field::get_index(true);
$mmrpg_index_players = rpg_player::get_index(true);
$mmrpg_index_robots = rpg_robot::get_index(true);
$mmrpg_index_abilities = rpg_ability::get_index(true);
$mmrpg_index_items = rpg_item::get_index(true);
$mmrpg_index_stars = rpg_world::get_stars_index();
$mmrpg_index_gates = rpg_world::get_static_gates_index();
$mmrpg_index_locks = rpg_world::get_static_locks_index();
$mmrpg_index_blocks = rpg_world::get_static_blocks_index();
$mmrpg_index_hazards = rpg_world::get_static_hazards_index();
$mmrpg_index_actors = rpg_world::get_static_actors_index();
$mmrpg_indexes = array(
    'types' => &$mmrpg_index_types,
    'fields' => &$mmrpg_index_fields,
    'players' => &$mmrpg_index_players,
    'robots' => &$mmrpg_index_robots,
    'abilities' => &$mmrpg_index_abilities,
    'items' => &$mmrpg_index_items,
    'stars' => &$mmrpg_index_stars,
    'gates' => &$mmrpg_index_gates,
    'locks' => &$mmrpg_index_locks,
    'blocks' => &$mmrpg_index_blocks,
    'hazards' => &$mmrpg_index_hazards,
    'actors' => &$mmrpg_index_actors,
    );
rpg_world::preload_indexes($mmrpg_indexes);

// Define the default prototype data fields and values so we don't get errors
$this_prototype_data = array();
$this_prototype_data['this_current_chapter'] = -1; // required
$this_prototype_data['this_current_player'] = ''; // required
$this_prototype_data['this_current_world'] = ''; // required
$this_prototype_data['this_current_map'] = ''; // required
$this_prototype_data['this_current_position'] = ''; // required
$this_prototype_data['this_current_direction'] = ''; // required
$this_prototype_data['battle_phase'] = 1; // required
$this_prototype_data['battle_round'] = 1; // required

// Collect of define the current player character we'll be using
$request_player_token = isset($_REQUEST['player']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['player']) ? trim($_REQUEST['player']) : '';
//if (empty($request_player_token) && !empty($WORLD_SESSION['last_player_token'])){ $request_player_token = $WORLD_SESSION['last_player_token']; }
if (empty($request_player_token) && !empty($WORLD_SESSION['player_sessions']['last_player'])){ $request_player_token = $WORLD_SESSION['player_sessions']['last_player']; }
if (!empty($request_player_token) && in_array($request_player_token, $allowed_player_tokens)){ $this_prototype_data['this_current_player'] = $request_player_token; }
if (empty($this_prototype_data['this_current_player'])){ $this_prototype_data['this_current_player'] = $default_player_token; }
//$WORLD_SESSION['last_player_token'] = $this_prototype_data['this_current_player'];
$WORLD_SESSION['last_player_token'] = $this_prototype_data['this_current_player'];
$WORLD_SESSION['player_sessions']['last_player'] = $this_prototype_data['this_current_player'];
$WORLD_SESSION['player_sessions']['allowed'] = $allowed_player_tokens; // store the allowed player tokens in the session
//error_log('$WORLD_SESSION[\'player_sessions\'][\'allowed\'] = '. print_r($WORLD_SESSION['player_sessions']['allowed'], true));

// Update the player session history in case we need to review it or switch back
if (empty($WORLD_SESSION['player_sessions']['history'])){ $WORLD_SESSION['player_sessions']['history'] = array(); }
//error_log('$WORLD_SESSION[\'player_sessions\'][\'history\'] = '. print_r($WORLD_SESSION['player_sessions']['history'], true));
$world_player_session_history = !empty($WORLD_SESSION['player_sessions']['history']) ? $WORLD_SESSION['player_sessions']['history'] : array();
$world_player_session_history = array_reverse($world_player_session_history);
$world_player_session_history[] = $this_prototype_data['this_current_player'];
$world_player_session_history = array_reverse($world_player_session_history);
$world_player_session_history = array_unique($world_player_session_history);
$world_player_session_history = array_values($world_player_session_history);
//error_log('$world_player_session_history = '. print_r($world_player_session_history, true));
$WORLD_SESSION['player_sessions']['history'] = $world_player_session_history;

// Now that we have the player, we should collect the player's robots and other data
$this_player_id = 1;
$this_player_token = 'player';
$this_player_info = array();
$this_player_robots = array();
$this_player_abilities = array();
$this_player_robots_index = array();
$this_player_items_index = array();
$this_prototype_data['this_player_id'] = $this_player_id; // required
$this_prototype_data['this_player_token'] = $this_player_token; // required
$this_prototype_data['this_player_robots'] = $this_player_robots; // required
$this_prototype_data['this_player_abilities'] = $this_player_abilities; // required
$this_prototype_data['this_player_items_index'] = $this_player_items_index; // required
$this_prototype_data['this_player_robots_index'] = $this_player_robots_index; // required
$this_prototype_data['this_player_robots_limit'] = -1; // required
$this_prototype_data['this_player_mobility'] = MMRPG_WORLD_DEFAULT_MOBILITY; // required
if (!empty($this_prototype_data['this_current_player'])){
    $this_player_token = $this_prototype_data['this_current_player'];
    $this_player_info = !empty($mmrpg_index_players[$this_player_token]) ? $mmrpg_index_players[$this_player_token] : array();
} else {
    die('MMRPG World Fatal Error - No player token defined!');
}

// Make sure the appropriate player token is set in the settings array
if (!isset($WORLD_SESSION['player_sessions'][$this_player_token])){ $WORLD_SESSION['player_sessions'][$this_player_token] = array(); }
$WORLD_PLAYER_SESSION = &$WORLD_SESSION['player_sessions'][$this_player_token];
if (!isset($WORLD_PLAYER_SESSION['last_world'])){ $WORLD_PLAYER_SESSION['last_world'] = ''; }
if (!isset($WORLD_PLAYER_SESSION['last_map'])){ $WORLD_PLAYER_SESSION['last_map'] = ''; }
if (!isset($WORLD_PLAYER_SESSION['last_position'])){ $WORLD_PLAYER_SESSION['last_position'] = ''; }
if (!isset($WORLD_PLAYER_SESSION['last_direction'])){ $WORLD_PLAYER_SESSION['last_direction'] = ''; }
if (!isset($WORLD_PLAYER_SESSION['last_robots'])){ $WORLD_PLAYER_SESSION['last_robots'] = ''; }

// Collect the current player's robots and battle history
$this_prototype_data['this_player_id'] = $this_player_info['player_id'];
$this_prototype_data['this_player_token'] = $this_player_info['player_token'];
$max_player_robots = mmrpg_prototype_limit_hearts_earned($this_player_token);
$allowed_player_robots = mmrpg_prototype_robots_unlocked($this_player_token, true);
//$summoned_player_robots = rpg_world::get_battle_history($this_player_token, 'robots_summoned');
//error_log('$max_player_robots = '.print_r($max_player_robots, true));
//error_log('$allowed_player_robots = '.print_r($allowed_player_robots, true));
////error_log('$summoned_player_robots = '.print_r($summoned_player_robots, true));
//error_log('-> $_REQUEST[robots] = '. print_r((isset($_REQUEST['robots']) ? $_REQUEST['robots'] : null), true));
//error_log('-> $WORLD_PLAYER_SESSION[\'last_robots\'] = '. print_r($WORLD_PLAYER_SESSION['last_robots'], true));
$current_player_robots = array();
//error_log('-> $current_player_robots = '. print_r($current_player_robots, true));
if (!empty($_REQUEST['robots'])){
    //error_log('Adding from $_REQUEST[robots] = '. print_r($_REQUEST['robots'], true));
    $request_robots = explode(',', $_REQUEST['robots']);
    //error_log('-> $request_robots = '. print_r($request_robots, true));
    //error_log('-> $current_player_robots (start) = '. print_r($current_player_robots, true));
    //error_log('-> $WORLD_PLAYER_SESSION[\'last_robots\'] (start) = '. print_r($WORLD_PLAYER_SESSION['last_robots'], true));
    $request_robots_filtered = array_filter($request_robots, function($robot) use ($allowed_player_robots){
        list($id, $token) = explode('_', trim($robot), 2);
        return in_array(trim($token), $allowed_player_robots);
        });
    $request_robots_filtered = array_slice($request_robots_filtered, 0, $max_player_robots);
    $request_robots_tokens = array_map(function($r){ return explode('_', $r, 2)[1]; }, $request_robots_filtered);
    //error_log('-> $request_robots_filtered = '. print_r($request_robots_filtered, true));
    //error_log('-> $request_robots_tokens = '. print_r($request_robots_tokens, true));
    if (!empty($request_robots_filtered)){
        $current_player_robots = $request_robots_tokens;
        $last_robots = $request_robots_filtered;
        $WORLD_PLAYER_SESSION['last_robots'] = implode(',', $last_robots);
        //error_log('-> $current_player_robots (new) = '. print_r($current_player_robots, true));
        //error_log('-> $WORLD_PLAYER_SESSION[\'last_robots\'] (new) = '. print_r($WORLD_PLAYER_SESSION['last_robots'], true));
    }
}
elseif (!empty($WORLD_PLAYER_SESSION['last_robots'])){
    //error_log('Adding from $WORLD_PLAYER_SESSION[last_robots] = '. print_r($WORLD_PLAYER_SESSION['last_robots'], true));
    $last_robots = explode(',', $WORLD_PLAYER_SESSION['last_robots']);
    $current_player_robots += array_map(function($r){ return explode('_', $r, 2)[1]; }, $last_robots);
}
if (!empty($allowed_player_robots)){
    if (empty($current_player_robots)){
        //error_log('Because empty $current_player_robots = '. print_r($current_player_robots, true));
        //error_log('Adding from $allowed_player_robots = '. print_r($allowed_player_robots, true));
        //error_log('-> count($current_player_robots)='.count($current_player_robots).' < $max_player_robots='.$max_player_robots);
        $slots_open = $max_player_robots - count($current_player_robots);
        $robots_not_yet_included = array_diff($allowed_player_robots, $current_player_robots);
        $current_player_robots = array_merge($current_player_robots, array_slice($robots_not_yet_included, 0, $slots_open));
        //error_log('-> $slots_open = '. print_r($slots_open, true));
        //error_log('-> $robots_not_yet_included = '. print_r($robots_not_yet_included, true));
        //error_log('-> new $current_player_robots = '. print_r($current_player_robots, true));
    }
    if (!empty($current_player_robots)){
        $current_player_robots = array_values(array_filter($current_player_robots,
            function($token) use ($allowed_player_robots){
                return in_array($token, $allowed_player_robots);
                }));
    }
    if (count($current_player_robots) > $max_player_robots){
        //error_log('Because too many $current_player_robots = '. print_r($current_player_robots, true));
        $current_player_robots = array_slice(0, $max_player_robots);
        //error_log('-> new $current_player_robots = '. print_r($current_player_robots, true));
    }
}
if (!empty($current_player_robots) && !empty($allowed_player_robots)){
    $index_tokens_required = array_values(array_unique(array_merge($current_player_robots, $allowed_player_robots)));
    //error_log('$current_player_robots = '.print_r($current_player_robots, true));
    //error_log('$allowed_player_robots = '.print_r($allowed_player_robots, true));
    //error_log('$index_tokens_required = '.print_r($index_tokens_required, true));
    foreach ($index_tokens_required AS $robot_token){
        if (empty($mmrpg_index_robots[$robot_token])){ continue; }
        $robot_info = $mmrpg_index_robots[$robot_token];
        $robot_id = $robot_info['robot_id'];
        $robot_string = $robot_id . '_' . $robot_token;
        $robot_info = rpg_world::get_player_robot_overview($this_player_token, $robot_token, $robot_id);
        if (in_array($robot_token, $current_player_robots)){ $this_player_robots[] = $robot_string; }
        $this_player_robots_index[$robot_string] = $robot_info;
    }
    //error_log('$this_player_token = '.print_r($this_player_token, true));
    //error_log('$this_player_robots = '.print_r($this_player_robots, true));
    //error_log('$this_player_robots_index = '.print_r($this_player_robots_index, true));
    $this_prototype_data['this_player_robots'] = $this_player_robots;
    $this_prototype_data['this_player_robots_index'] = $this_player_robots_index;
}
$WORLD_PLAYER_SESSION['last_robots'] = implode(',', $this_prototype_data['this_player_robots']);
//error_log('-> $WORLD_PLAYER_SESSION[\'last_robots\'] (final) = '. print_r($WORLD_PLAYER_SESSION['last_robots'], true));

// Collect the user's list of unlocked abilities so we know what we already have (all players share)
$unlocked_player_abilities = array();
$unlocked_abilities_count = mmrpg_prototype_abilities_unlocked('', '', $unlocked_player_abilities);
//error_log('$unlocked_abilities_count = '. print_r($unlocked_abilities_count, true));
//error_log('$unlocked_player_abilities = '. print_r($unlocked_player_abilities, true));
if (!empty($unlocked_player_abilities)
    && empty($this_prototype_data['this_player_abilities'])){
    $this_prototype_data['this_player_abilities'] = array_values($unlocked_player_abilities);
}

// Collect the user's list of collected items so we know what we already have and how many (all players share)
$unlocked_player_items = array();
$equipped_player_items = array();
$unlocked_items_count = mmrpg_prototype_items_unlocked(true, $unlocked_player_items);
$equipped_items_count = mmrpg_prototype_items_equipped('', $equipped_player_items);
//error_log('$unlocked_items_count = '. print_r($unlocked_items_count, true));
//error_log('$unlocked_player_items = '. print_r($unlocked_player_items, true));
//error_log('$equipped_items_count = '. print_r($equipped_items_count, true));
//error_log('$equipped_player_items = '. print_r($equipped_player_items, true));
if (empty($this_prototype_data['this_player_items_index'])){
    $this_player_items_index = array();
    if (!empty($unlocked_player_items)){
        //error_log('$unlocked_player_items = '. print_r($unlocked_player_items, true));
        $this_player_items_index = array_merge($this_player_items_index, $unlocked_player_items);
    }
    if (!empty($equipped_player_items)){
        //error_log('$equipped_player_items = '. print_r($equipped_player_items, true));
        foreach ($equipped_player_items AS $token => $equipped){
            if (!isset($this_player_items_index[$token])){ $this_player_items_index[$token] = 0; }
            $this_player_items_index[$token.'__equipped'] = $equipped;
        }
    }
    $this_prototype_data['this_player_items_index'] = $this_player_items_index;
    //error_log('$this_player_items_index(new) = '. print_r($this_player_items_index, true));
}

// Collect the user's list of collects stars so we know what we already have and their metadata (all players share)
$unlocked_player_stars = rpg_world::get_battle_stars();
if (empty($this_prototype_data['this_player_stars_index'])){
    $this_player_stars_index = array();
    if (!empty($unlocked_player_stars)){
        //error_log('$unlocked_player_stars = '. print_r($unlocked_player_stars, true));
        $new_stars_index = array();
        foreach ($unlocked_player_stars AS $star_token => $star_data){
            $new_stars_index[$star_token] = !empty($star_data['star_date']) ? $star_data['star_date'] : true;
            }
        $this_player_stars_index = array_merge($this_player_stars_index, $new_stars_index);
    }
    $this_prototype_data['this_player_stars_index'] = $this_player_stars_index;
    //error_log('$this_player_stars_index(new) = '. print_r($this_player_stars_index, true));
}

// Update the player's mobility with any character-specific bonuses or contextual modifiers
if ($this_prototype_data['this_player_token'] === 'player'){ $this_prototype_data['this_player_mobility'] = -1; }
else { $this_prototype_data['this_player_mobility'] = MMRPG_WORLD_DEFAULT_MOBILITY; }
// Update the player's max robots value so we can pass it off to the client-side script
if ($this_prototype_data['this_player_token'] !== 'player'){ $this_prototype_data['this_player_robots_limit'] = $max_player_robots; }


// Collect or define the current map token we'll be loading from
$request_world_token = isset($_REQUEST['world']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['world']) ? trim($_REQUEST['world']) : '';
if (empty($request_world_token) && !empty($WORLD_PLAYER_SESSION['last_world'])){ $request_world_token = $WORLD_PLAYER_SESSION['last_world']; }
if (!empty($request_world_token) && !empty($WORLD_PLAYER_SESSION['last_world']) && $request_world_token !== $WORLD_PLAYER_SESSION['last_world']){ unset($WORLD_PLAYER_SESSION['last_position']); }
if (!empty($request_world_token) && in_array($request_world_token, $allowed_world_tokens)){ $this_prototype_data['this_current_world'] = $request_world_token; }
if (empty($this_prototype_data['this_current_world'])){ $this_prototype_data['this_current_world'] = $default_world_token; }
$WORLD_PLAYER_SESSION['last_world'] = $this_prototype_data['this_current_world'];
$request_world_token = $this_prototype_data['this_current_world'];

// Collect or define the current world-map token we'll be loading from
$request_map_token = isset($_REQUEST['map']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['map']) ? trim($_REQUEST['map']) : '';
if (empty($request_map_token) && !empty($WORLD_PLAYER_SESSION['last_map'])){ $request_map_token = $WORLD_PLAYER_SESSION['last_map']; }
if (!empty($request_map_token) && !empty($WORLD_PLAYER_SESSION['last_map']) && $request_map_token !== $WORLD_PLAYER_SESSION['last_map']){ unset($WORLD_PLAYER_SESSION['last_position']); }
if (!empty($request_map_token) && in_array($request_world_token.'__'.$request_map_token, $allowed_world_map_tokens)){ $this_prototype_data['this_current_map'] = $request_map_token; }
if (empty($this_prototype_data['this_current_map'])){ $this_prototype_data['this_current_map'] = $default_map_token; }
$WORLD_PLAYER_SESSION['last_map'] = $this_prototype_data['this_current_map'];
$request_map_token = $this_prototype_data['this_current_map'];

// Collect or define the current map position we'll be spawning into
$is_spawn_request = !empty($_REQUEST['position']) && $_REQUEST['position'] === 'spawn' ? true : false;
$request_world_position = isset($_REQUEST['position']) && preg_match('/^([-a-z0-9]+)$/i', $_REQUEST['position']) ? trim($_REQUEST['position']) : '';
$request_world_direction = isset($_REQUEST['direction']) && preg_match('/^([-a-z0-9]+)$/i', $_REQUEST['direction']) ? trim($_REQUEST['direction']) : '';
if (empty($request_world_position) && !empty($WORLD_PLAYER_SESSION['last_position'])){ $request_world_position = $WORLD_PLAYER_SESSION['last_position']; }
if (empty($request_world_direction) && !empty($WORLD_PLAYER_SESSION['last_direction'])){ $request_world_direction = $WORLD_PLAYER_SESSION['last_direction']; }
$this_prototype_data['this_current_position'] = $is_spawn_request ? $default_world_position : (!empty($request_world_position) ? $request_world_position : $default_world_position);
$this_prototype_data['this_current_direction'] = $is_spawn_request ? $default_world_direction : (!empty($request_world_direction) ? $request_world_direction : $default_world_direction);
$WORLD_PLAYER_SESSION['last_position'] = $this_prototype_data['this_current_position'];
$WORLD_PLAYER_SESSION['last_direction'] = $this_prototype_data['this_current_direction'];
$request_world_position = $this_prototype_data['this_current_position'];
$request_world_direction = $this_prototype_data['this_current_direction'];
//error_log('$allowed_world_tokens = '. print_r($allowed_world_tokens, true));
//error_log('$allowed_world_map_tokens = '. print_r($allowed_world_map_tokens, true));
//error_log('$allowed_world_sheet_tokens = '. print_r($allowed_world_sheet_tokens, true));
//error_log('$this_prototype_data = '. print_r($this_prototype_data, true));
//error_log('$WORLD_PLAYER_SESSION = '. print_r($WORLD_PLAYER_SESSION, true));

// Create arrays for pending actions and transitions before switching just-in-case they're needed
if (empty($WORLD_PLAYER_SESSION['recent_actions'])){ $WORLD_PLAYER_SESSION['recent_actions'] = array(); }
if (empty($WORLD_PLAYER_SESSION['pending_actions'])){ $WORLD_PLAYER_SESSION['pending_actions'] = array(); }
$recent_world_actions = &$WORLD_PLAYER_SESSION['recent_actions'];
$pending_world_actions = &$WORLD_PLAYER_SESSION['pending_actions'];

// Now that we've collected required args that may have been passed in the URL, reload w/o them to prevent double-entry
if (!empty($_REQUEST['player'])
    || !empty($_REQUEST['robots'])
    || !empty($_REQUEST['world'])
    || !empty($_REQUEST['map'])
    || !empty($_REQUEST['position'])
    || !empty($_REQUEST['direction'])){
    // Check for pending actions or transitions before redirecting
    if (!empty($_REQUEST['player']) && !empty($_REQUEST['switch']) && $_REQUEST['switch'] === 'true'){ array_unshift($recent_world_actions, 'player-switch'); }
    if (!empty($_REQUEST['robots']) && !empty($_REQUEST['switch']) && $_REQUEST['switch'] === 'true'){ array_unshift($recent_world_actions, 'team-switch'); }
    // Redirect to the clean world URL without any arguments to prevent double-entry
    $allowed_returns = array('world' => 'world.php', 'prototype' => 'prototype.php');
    $return_to = !empty($_REQUEST['return']) && in_array($_REQUEST['return'], array_keys($allowed_returns)) ? $_REQUEST['return'] : 'world';
    $return_url = $allowed_returns[$return_to];
    header('Location: '.$return_url);
    exit();
}

// Load map data from the appropriate map file
$world_map_token = $this_prototype_data['this_current_world'].'__'.$this_prototype_data['this_current_map'];
$world_token = $this_prototype_data['this_current_world'];
$world_data_parsed = array();
$map_token = $this_prototype_data['this_current_map'];
$map_name = str_replace(' AREA ', ' Area ', strtoupper(str_replace('-', ' ', $map_token)));
$map_data_parsed = array();
rpg_world::parse_map_data($world_token.'__'.$map_token, $world_data_parsed, $map_data_parsed);
//$map_data_parsed = rpg_world::parse_map_data($world_token.'__'.$map_token);
$map_sprite_sheet = !empty($map_data_parsed) && !empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : '';
//error_log('$world_map_token = '.print_r($world_map_token, true));
//error_log('$world_token = '.print_r($world_token, true));
//error_log('$world_data_parsed = '.print_r($world_data_parsed, true));
//error_log('$map_token = '.print_r($map_token, true));
//error_log('$map_name = '.print_r($map_name, true));
//error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
//error_log('$map_sprite_sheet = '.print_r($map_sprite_sheet, true));
if (empty($map_data_parsed)){ error_log('MMRPG World Fatal Error - No world data defined for map "'.$world_map_token.'"!'); die(); }
if (empty($map_sprite_sheet)){ error_log('MMRPG World Fatal Error - No sprite sheet defined for map "'.$world_map_token.'"!'); die(); }

// Update the saved last-world and last-map tokens in the world session and track if they've changed
$prev_world_token = !empty($WORLD_SESSION['last_world_token']) ? $WORLD_SESSION['last_world_token'] : '';
$prev_map_token = !empty($WORLD_SESSION['last_map_token']) ? $WORLD_SESSION['last_map_token'] : '';
$new_world_token = $this_prototype_data['this_current_world'];
$new_map_token = $this_prototype_data['this_current_map'];
$WORLD_SESSION['last_world_token'] = $new_world_token;
$WORLD_SESSION['last_map_token'] = $new_map_token;
$world_token_changed = $prev_world_token !== $new_world_token ? true : false;
$map_token_changed = $prev_map_token !== $new_map_token ? true : false;

// Check if the location (world map) or player (via switch) have changed since last
$location_has_changed = $world_token_changed || $map_token_changed ? true : false;
$player_has_changed = !empty($recent_world_actions) && $recent_world_actions[0] === 'player-switch' ? true : false;
$robots_have_changed = !empty($recent_world_actions) && $recent_world_actions[0] === 'team-switch' ? true : false;

// Make sure we add a start action otherwise to ensure we don't double-up on things
array_unshift($recent_world_actions, 'start');
$recent_world_actions = array_slice($recent_world_actions, 0, 9);

// Make sure there's room in relevant session arrays for this map's data
if (!isset($WORLD_SESSION['world_maps'][$world_map_token])){ $WORLD_SESSION['world_maps'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_events'][$world_map_token])){ $WORLD_SESSION['world_events'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_buttons'][$world_map_token])){ $WORLD_SESSION['world_buttons'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_switches'][$world_map_token])){ $WORLD_SESSION['world_switches'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_gates'][$world_map_token])){ $WORLD_SESSION['world_gates'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_locks'][$world_map_token])){ $WORLD_SESSION['world_locks'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_blocks'][$world_map_token])){ $WORLD_SESSION['world_blocks'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_hazards'][$world_map_token])){ $WORLD_SESSION['world_hazards'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_items'][$world_map_token])){ $WORLD_SESSION['world_items'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_abilities'][$world_map_token])){ $WORLD_SESSION['world_abilities'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_encounters'][$world_map_token])){ $WORLD_SESSION['world_encounters'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_actors'][$world_map_token])){ $WORLD_SESSION['world_actors'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_symbols'][$world_map_token])){ $WORLD_SESSION['world_symbols'][$world_map_token] = array(); } // represents changes to the other symbols

// Collect the map's field token and mecha encounters
$map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
$map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
//error_log('$map_field_token = '.print_r($map_field_token, true));
$map_field_background = !empty($map_field_info['field_background']) ? $map_field_info['field_background'] : 'field';
$map_field_foreground = !empty($map_field_info['field_foreground']) ? $map_field_info['field_foreground'] : 'field';
$map_field_music = !empty($map_field_info['field_music']) ? $map_field_info['field_music'] : 'misc/star-force'; // TODO: find a better default for this
if (!empty($map_data_parsed['background'])){ $map_field_background = $map_data_parsed['background']; }
if (!empty($map_data_parsed['foreground'])){ $map_field_foreground = $map_data_parsed['foreground']; }
//error_log('$map_field_background = '.print_r($map_field_background, true));
//error_log('$map_field_foreground = '.print_r($map_field_foreground, true));
//error_log('b/c -> $map_field_info = '.print_r($map_field_info, true));
//error_log('b/c -> $map_data_parsed = '.print_r($map_data_parsed, true));

// Collect the overall size variables for this map
$map_base_size = $map_data_parsed['size'];
if (count($map_base_size) === 4){ list($map_col_size, $map_row_size, $map_tile_width, $map_tile_height) = $map_base_size; }
elseif (count($map_base_size) === 3){ list($map_col_size, $map_row_size, $map_tile_width) = $map_base_size; }
elseif (count($map_base_size) === 2){ list($map_col_size, $map_tile_width) = $map_base_size; }
elseif (count($map_base_size) === 1){ list($map_col_size) = $map_base_size; }
if (!isset($map_col_size)){ $map_col_size = rpg_world::$default_mapsize; }
if (!isset($map_row_size)){ $map_row_size = $map_col_size; }
if (!isset($map_tile_width)){ $map_tile_width = rpg_world::$default_tilesize; }
if (!isset($map_tile_height)){ $map_tile_height = $map_tile_width; }
$map_pixel_width = $map_col_size * $map_tile_width;
$map_pixel_height = $map_row_size * $map_tile_height;

// Generate the map spawn points (source and destination)
$map_spawn_pos = !empty($WORLD_SESSION['world_maps'][$world_map_token]['spawn_pos']) ? $WORLD_SESSION['world_maps'][$world_map_token]['spawn_pos'] : '';
$map_exit_pos = !empty($WORLD_SESSION['world_maps'][$world_map_token]['exit_pos']) ? $WORLD_SESSION['world_maps'][$world_map_token]['exit_pos'] : '';
if (empty($map_spawn_pos)){
    $map_spawn_pos = '1-1';
    if (!empty($map_data_parsed['portals']['spawn'])){
        $map_spawn_pos = $map_data_parsed['portals']['spawn'][0];
    }
}
if (empty($map_exit_pos)){
    $map_exit_pos = ($map_col_size + 1).'-'.($map_row_size + 1);
    if (!empty($map_data_parsed['portals']['exit'])){
        $map_exit_pos = $map_data_parsed['portals']['exit'][0];
    }
}
$WORLD_SESSION['world_maps'][$world_map_token]['spawn_pos'] = $map_spawn_pos;
$WORLD_SESSION['world_maps'][$world_map_token]['exit_pos'] = $map_exit_pos;

// If the world position has not been set yet, we can use the spawn position for it as well
if (empty($this_prototype_data['this_current_position'])){
    $this_prototype_data['this_current_position'] = $map_spawn_pos;
} else if (!preg_match('/^([0-9]+)-([0-9]+)$/i', $this_prototype_data['this_current_position'])){
    // if not an x-y position, this must be the name key of a location like a portal, so we need to look it up
    $real_position_value = false;
    $position_key = $this_prototype_data['this_current_position'];
    if (!empty($map_data_parsed['portals']) && !empty($map_data_parsed['portals'][$position_key])){ $real_position_value = $map_data_parsed['portals'][$position_key][0]; }
    // if a real position value was found, use it, otherwise default to spawn
    if (!empty($real_position_value)){ $this_prototype_data['this_current_position'] = $real_position_value; }
    else { $this_prototype_data['this_current_position'] = $map_spawn_pos; }
}

// If the encounters for this map have not been generated yet, we can do so now
$reset_encounters = !empty($_GET['reset']) && $_GET['reset'] === 'encounters' ? true : false;
$world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
$world_map_encounters = !empty($world_encounters[$world_map_token]) ? $world_encounters[$world_map_token] : array();
if (empty($world_map_encounters) || $reset_encounters === true){
    $world_map_encounters = rpg_world::generate_worldmap_encounters($this_prototype_data, $map_data_parsed);
    rpg_world::update_session('world_encounters', $world_map_token, $world_map_encounters);
    if ($reset_encounters === true){
        header('Location: world.php');
        exit();
    }
}

// If the pickups for this map have not been generated yet, we can do so now
$reset_pickups = !empty($_GET['reset']) && $_GET['reset'] === 'pickups' ? true : false;
$world_pickups = !empty($WORLD_SESSION['world_pickups']) ? $WORLD_SESSION['world_pickups'] : array();
$world_map_pickups = !empty($world_pickups[$world_map_token]) ? $world_pickups[$world_map_token] : array();
if (empty($world_map_pickups) || $reset_pickups === true){
    //error_log('regenerating pickups!');
    $world_map_pickups = rpg_world::generate_worldmap_pickups($this_prototype_data, $map_data_parsed);
    rpg_world::update_session('world_pickups', $world_map_token, $world_map_pickups);
    $world_map_items = !empty($WORLD_SESSION['world_items'][$world_map_token]) ? $WORLD_SESSION['world_items'][$world_map_token] : array();
    $world_map_abilities = !empty($WORLD_SESSION['world_abilities'][$world_map_token]) ? $WORLD_SESSION['world_abilities'][$world_map_token] : array();
    foreach ($world_map_pickups AS $key => $pickup){ unset($world_map_items[$pickup[3]]); unset($world_map_abilities[$pickup[3]]); } // clear all "claimed" datestamps
    rpg_world::update_session('world_items', $world_map_token, $world_map_items);
    rpg_world::update_session('world_abilities', $world_map_token, $world_map_abilities);
    if ($reset_pickups === true){
        header('Location: world.php');
        exit();
    }
}

// If requested to do so, make sure we reset the world items
$reset_items = !empty($_GET['reset']) && $_GET['reset'] === 'items' ? true : false;
$world_items = !empty($WORLD_SESSION['world_items']) ? $WORLD_SESSION['world_items'] : array();
$world_map_items = !empty($world_items[$world_map_token]) ? $world_items[$world_map_token] : array();
if ($reset_items === true){
    //error_log('clearing claimed items!');
    rpg_world::update_session('world_items', $world_map_token, array());  // clear all "claimed" datestamps
    header('Location: world.php');
    exit();
}

// If requested to do so, make sure we reset the world abilities
$reset_abilities = !empty($_GET['reset']) && $_GET['reset'] === 'abilities' ? true : false;
$world_abilities = !empty($WORLD_SESSION['world_abilities']) ? $WORLD_SESSION['world_abilities'] : array();
$world_map_abilities = !empty($world_abilities[$world_map_token]) ? $world_abilities[$world_map_token] : array();
if ($reset_abilities === true){
    //error_log('clearing claimed abilities!');
    rpg_world::update_session('world_abilities', $world_map_token, array());  // clear all "claimed" datestamps
    header('Location: world.php');
    exit();
}

// If requested to do so, make sure we reset the world gates
$reset_gates = !empty($_GET['reset']) && $_GET['reset'] === 'gates' ? true : false;
$world_gates = !empty($WORLD_SESSION['world_gates']) ? $WORLD_SESSION['world_gates'] : array();
$world_map_gates = !empty($world_gates[$world_map_token]) ? $world_gates[$world_map_token] : array();
if ($reset_gates === true){
    //error_log('clearing removed gates!');
    rpg_world::update_session('world_gates', $world_map_token, array());  // clear all "removed" datestamps
    header('Location: world.php');
    exit();
}

// If requested to do so, make sure we reset the world actors
$reset_actors = !empty($_GET['reset']) && $_GET['reset'] === 'actors' ? true : false;
$world_actors = !empty($WORLD_SESSION['world_actors']) ? $WORLD_SESSION['world_actors'] : array();
$world_map_actors = !empty($world_actors[$world_map_token]) ? $world_actors[$world_map_token] : array();
if ($reset_actors === true){
    //error_log('clearing removed actors!');
    rpg_world::update_session('world_actors', $world_map_token, array());  // clear all "removed" datestamps
    header('Location: world.php');
    exit();
}

// If requested to do so, make sure we reset the world locks
$reset_locks = !empty($_GET['reset']) && $_GET['reset'] === 'locks' ? true : false;
$world_locks = !empty($WORLD_SESSION['world_locks']) ? $WORLD_SESSION['world_locks'] : array();
$world_map_locks = !empty($world_locks[$world_map_token]) ? $world_locks[$world_map_token] : array();
if ($reset_locks === true){
    //error_log('clearing opened locks!');
    //error_log('-> $world_locks was '.print_r($world_locks, true));
    //error_log('-> $world_map_locks was '.print_r($world_map_locks, true));
    rpg_world::update_session('world_locks', $world_map_token, array());  // clear all "removed" datestamps
    if (!empty($world_locks)){
        foreach ($world_locks AS $temp_maptoken => $temp_maplocks){
            if (empty($temp_maplocks)){ continue; }
            foreach ($temp_maplocks AS $lock_namekey => $timestamp){
                if (!isset($world_map_locks[$lock_namekey])){ continue; }
                elseif ($world_map_locks[$lock_namekey] !== $timestamp){ continue; }
                $WORLD_SESSION['world_locks'][$temp_maptoken][$lock_namekey] = 0;
                $WORLD_SESSION['world_locks'][$temp_maptoken] = array_filter($WORLD_SESSION['world_locks'][$temp_maptoken]);
                //error_log('removed '.$lock_namekey.' from $WORLD_SESSION[\'world_locks\']['.$temp_maptoken.'], now = '.print_r($WORLD_SESSION['world_locks'][$temp_maptoken], true));
            }
        }
    }
    header('Location: world.php');
    exit();
}

// Calculate remaining encounters for this area for later reference
$battles_remaining = array();
foreach ($world_map_encounters AS $namekey => $encounter){
        $kind = $encounter[0]; $subkind = '';
        if (strstr($kind, '/')){ list($kind, $subkind) = explode('/', $kind, 2); }
        $token = $encounter[1]; $alt = $encounter[2]; $pos = $encounter[3]; $battle = $encounter[4]; $name = $encounter[5];
        //error_log('-> processing battle w/'.PHP_EOL.'-> $token ='.' '.$token.PHP_EOL.'-> $kind = '.$kind.PHP_EOL.'-> $subkind = '.$subkind.PHP_EOL.'-> $alt = '.$alt.PHP_EOL.'-> $pos = '.$pos.PHP_EOL.'-> $battle = '.$battle);
        if (!rpg_battle::has_index_info($battle)){ continue; }
        if ($subkind === 'rescue' && mmrpg_prototype_robot_unlocked('', $token)){ continue; }
        if (!isset($battles_remaining['all'])){ $battles_remaining['all'] = 0; }
        if (!isset($battles_remaining[$subkind])){ $battles_remaining[$subkind] = 0; }
        if ($subkind !== 'rescue'){ $battles_remaining['all']++; }
        $battles_remaining[$subkind]++;
    }
//error_log('$battles_remaining = '.print_r($battles_remaining, true));
$map_data_parsed['battles'] = $battles_remaining;
//error_log('$map_data_parsed = '.print_r($map_data_parsed, true));

// Refresh the world map with any persistent changes that have occurred
rpg_world::refresh_world_map($this_prototype_data, $map_data_parsed);

// Now that we have encounter and other data, let's see if we should change the music at all
if (!empty($map_data_parsed['music'])){
    $music_tracks = $map_data_parsed['music'];
    if (!empty($music_tracks['safezone']) && empty($battles_remaining['all'])){ $map_field_music = $music_tracks['safezone'][0]; }
    elseif (!empty($music_tracks['dangerzone']) && !empty($battles_remaining['all'])){ $map_field_music = $music_tracks['dangerzone'][0]; }
    elseif (!empty($music_tracks['boss-nearby']) && !empty($battles_remaining['boss'])){ $map_field_music = $music_tracks['boss-nearby'][0]; }
    elseif (!empty($music_tracks['master-nearby']) && !empty($battles_remaining['master'])){ $map_field_music = $music_tracks['master-nearby'][0]; }
    elseif (!empty($music_tracks['mecha-nearby']) && !empty($battles_remaining['mecha'])){ $map_field_music = $music_tracks['mecha-nearby'][0]; }
    elseif (!empty($music_tracks['default'])){ $map_field_music = $music_tracks['default'][0]; }
    //error_log('$music_tracks = '.print_r($music_tracks, true));
    }
if (!strstr($map_field_music, '/')){ $map_field_music = 'sega-remix/'.$map_field_music; }
//error_log('$map_field_music = '.print_r($map_field_music, true));
//error_log('b/c -> $map_field_info = '.print_r($map_field_info, true));
//error_log('b/c -> $map_data_parsed = '.print_r($map_data_parsed, true));
//error_log('b/c -> $world_map_encounters = '.print_r($world_map_encounters, true));

// Automatically save the world session w/ any recent changes
//rpg_world::save_session();

// Define some fallback values for compatibility
$debug_flag_animation = true;
$flag_skip_fadein = !$location_has_changed ? true : false;

/*
// DEBUG DEBUG DEBUG DEBUG
// Append this event to the global events array
$session_token = rpg_game::session_token();
$_SESSION[$session_token]['EVENTS'][] = array(
    'canvas_markup' => '&hellip;',
    'console_markup' => '<p>Testing 123</p>',
    'player_token' => 'dr-light',
    'event_type' => 'other'
    );
$_SESSION[$session_token]['EVENTS'][] = array(
    'canvas_markup' => '&hellip;',
    'console_markup' => '<p>Testing 456</p>',
    'player_token' => 'dr-wily',
    'event_type' => 'other'
    );
$_SESSION[$session_token]['EVENTS'][] = array(
    'canvas_markup' => '&hellip;',
    'console_markup' => '<p>Testing 789</p>',
    'player_token' => 'dr-cossack',
    'event_type' => 'other'
    );
*/

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>World Map | Prototype | Mega Man RPG Prototype</title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">
<link type="text/css" href="styles/reset.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/world.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/events.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
</head>
<body id="mmrpg" class="world <?= 'env_'.MMRPG_CONFIG_SERVER_ENV ?>">
<!-- (1) preload content indexes -->
<link type="text/css" href="content/all.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<script type="text/javascript" src="content/all.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<!-- (2) generate world markup -->
<div id="world" class="hidden <?= $flag_skip_fadein ? 'fastfade' : '' ?>">
    <div id="canvas">
        <div class="wrapper">
            <?

            // Grab the background sprite for this map and place a copy of it here behind everything else (for when the user over-zooms)
            $background_layer_sprite = rpg_world::get_background_layer_sprite($this_prototype_data, $map_data_parsed, false);
            echo('<div id="bg">'.$background_layer_sprite.'</div>'.PHP_EOL);

            // Collect top-level map data and define preset styles for the layers to use
            $map_config = $map_data_parsed['config'];
            $map_tilesize_default = $map_config['tilesize_default'];
            $map_tilesize_offset = $map_config['tilesize_offset'];
            $map_spritesize_default = $map_config['spritesize_default'];
            $map_spritesize_offset = $map_config['spritesize_offset'];
            $map_size_styles = $map_config['size_styles'];
            $map_offset_styles = $map_config['offset_styles'];
            $map_base_styles = $map_config['base_styles'];
            $map_base_attrs = $map_config['base_attrs'];
            ?>
            <div id="map" data-token="<?= $world_map_token ?>" style="<?= $map_base_styles ?>" <?= $map_base_attrs ?>>
                <?

                // GLOBAL MAP DATA
                //error_log('Generating world map data for map "'.$world_map_token.'"...');
                //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
                $data = array();
                $data['map_world'] = $map_data_parsed['world'];
                $data['map_world_name'] = $map_data_parsed['world_name'];
                $data['map_token'] = $map_data_parsed['token'];
                $data['map_name'] = $map_data_parsed['name'];
                $data['map_image'] = 'images/maps/'.(!empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : 'undefined.png');
                $data['map_field'] = $map_field_token;
                $data['map_size'] = array($map_col_size, $map_row_size);
                $data['tile_size'] = array($map_tile_width, $map_tile_height);
                $data['tiles_index'] = $map_data_parsed['tiles'];
                $data['sprites_index'] = $map_data_parsed['sprites'];
                $data['groups_index'] = $map_data_parsed['groups'];
                $data['start_position'] = $this_prototype_data['this_current_position'];
                $data['start_direction'] = $this_prototype_data['this_current_direction'];
                $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                echo('<script data-json="mapData" type="application/json">'.$data_json.'</script>'.PHP_EOL);

                // TERRAIN TILES
                foreach ($map_data_parsed['layers'] AS $map_layer_key => $map_layer_data){
                    $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                    $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                    $terrain_layer_markup = rpg_world::get_terrain_layer_markup($this_prototype_data, $map_data_parsed, $map_layer_data);
                    echo('<div class="layer layer-1 tiles terrain has-canvas" data-layer="terrain" '.$map_layer_styles.$map_layer_attrs.'>');
                        echo($terrain_layer_markup);
                    echo('</div>'.PHP_EOL);
                }

                // ALL TILE SPRITES
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                echo('<div class="layer layer-2 sprites tiles" data-layer="sprites/tiles" '.$map_layer_styles.$map_layer_attrs.'>');
                    // EVENT TILE SPRITES
                    $events_layer_markup = rpg_world::get_events_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($events_layer_markup);
                    // PORTAL TILE SPRITES
                    $portals_layer_markup = rpg_world::get_portals_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($portals_layer_markup);
                    // BUTTON TILE SPRITES
                    $buttons_layer_markup = rpg_world::get_buttons_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($buttons_layer_markup);
                    // SWITCH TILE SPRITES
                    $switches_layer_markup = rpg_world::get_switches_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($switches_layer_markup);
                    // GATE TILE SPRITES
                    $gates_layer_markup = rpg_world::get_gates_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($gates_layer_markup);
                    // LOCK TILE SPRITES
                    $locks_layer_markup = rpg_world::get_locks_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($locks_layer_markup);
                    // BLOCK TILE SPRITES
                    $blocks_layer_markup = rpg_world::get_blocks_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($blocks_layer_markup);
                echo('</div>'.PHP_EOL);

                // ALL OBJECT SPRITES
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                echo('<div class="layer layer-3 sprites objects" data-layer="sprites/objects" '.$map_layer_styles.$map_layer_attrs.'>');
                    // BATTLE OBJECT SPRITES
                    $battles_layer_markup = rpg_world::get_battles_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($battles_layer_markup);
                    // ACTOR TILE SPRITES
                    $actors_layer_markup = rpg_world::get_actors_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($actors_layer_markup);
                    // ITEM OBJECT SPRITES
                    $items_layer_markup = rpg_world::get_items_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($items_layer_markup);
                    // ABILITY OBJECT SPRITES
                    $abilities_layer_markup = rpg_world::get_abilities_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($abilities_layer_markup);
                    // HAZARD TILE SPRITES
                    $hazards_layer_markup = rpg_world::get_hazards_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($hazards_layer_markup);
                    // TEAM & RIVAL OBJECT SPRITES
                    $team_layer_markup = rpg_world::get_team_layer_markup($this_prototype_data, $map_data_parsed);
                    $rivals_layer_markup = rpg_world::get_rivals_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($team_layer_markup);
                    echo($rivals_layer_markup);
                echo('</div>'.PHP_EOL);

                // LABELS AND OVERLAYS
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                echo('<div class="layer layer-4 sprites overlays" data-layer="sprites/overlays" '.$map_layer_styles.$map_layer_attrs.'>');
                    // (populated at runtime)
                    // DEBUG DEBUG DEBUG
                    // echo('<div class="sprite overlay position-overlay debug">');
                    //    echo('<strong class="label position-label debug">X12-Y34</strong>');
                    // echo('</div>'.PHP_EOL);
                    // DEBUG DEBUG DEBUG
                echo('</div>'.PHP_EOL);

                // END OF LAYERS
                ?>
            </div>
            <?
            // Define or generate the markup for the various buttons and UI elements around the world map
            $wrap_markup = function($markup, $tag = 'div', $cls = 'wrapper'){ if (empty($markup)){ return ''; } return '<'.$tag.' class="'.$cls.'">'.$markup.'</'.$tag.'>'; };
            $back_button_markup = $wrap_markup('<i class="fa fas fa-sign-out"></i>', 'a');
            $home_button_markup = $wrap_markup('<i class="fa fas fa-home"></i>', 'a');
            //$reset_button_markup = $wrap_markup('<i class="fa fas fa-bomb"></i>', 'a');
            $zoom_controls_markup = $wrap_markup('<i class="fa fas fa-search-plus"></i>', 'a', 'zoom-control zoom-in');
            $zoom_controls_markup .= $wrap_markup('<i class="fa fas fa-search-minus"></i>', 'a', 'zoom-control zoom-out');
            $position_display_markup = $wrap_markup('&hellip;');
            $message_display_markup = $wrap_markup('&nbsp;');
            $side_buttons_markup = $wrap_markup('&hellip;');
            $player_switcher_players = $allowed_player_tokens;
            $robot_overview_robots = $current_player_robots;
            $player_switcher_markup = $wrap_markup(rpg_world::get_player_switcher_markup($this_prototype_data, $player_switcher_players));
            $cursor_palette_markup = $wrap_markup(rpg_world::get_cursor_palette_markup($this_prototype_data));
            $robots_overview_markup = $wrap_markup(rpg_world::get_robots_overview_markup($this_prototype_data, $robot_overview_robots));
            $minimap_overview_markup = $wrap_markup(rpg_world::get_minimap_overview_markup($this_prototype_data, $world_data_parsed, $map_data_parsed));
            $progress_tracker_markup = $wrap_markup(rpg_world::get_progress_tracker_markup($this_prototype_data));
            ?>
            <? if (!empty($cursor_palette_markup)){ ?><div id="cursor-palette" class="chrome"><?= $cursor_palette_markup ?></div><? } ?>
            <? if (!empty($player_switcher_markup)){ ?><div id="player-switcher" class="chrome"><?= $player_switcher_markup ?></div><? } ?>
            <? if (!empty($robots_overview_markup)){ ?><div id="robots-overview" class="chrome"><?= $robots_overview_markup ?></div><? } ?>
            <? if (!empty($minimap_overview_markup)){ ?><div id="minimap-overview" class="chrome"><?= $minimap_overview_markup ?></div><? } ?>
            <? if (!empty($progress_tracker_markup)){ ?><div id="progress-tracker" class="chrome"><?= $progress_tracker_markup ?></div><? } ?>
            <div id="zoom-controls" class="chrome"><?= $zoom_controls_markup ?></div>
            <div id="position-display" class="chrome"><?= $position_display_markup ?></div>
            <div id="message-display" class="chrome"><?= $message_display_markup ?></div>
            <div id="back-button" class="chrome chrome-button"><?= $back_button_markup ?></div>
            <div id="home-button" class="chrome chrome-button"><?= $home_button_markup ?></div>
            <? /* <div id="reset-button" class="chrome chrome-button"><?= $reset_button_markup ?></div> */ ?>
            <div id="side-buttons" class="chrome"><?= $side_buttons_markup ?></div>
            <div id="loading-icon" class="chrome"><i class="fa fas fa-spinner"></i></div>
        </div>
    </div>
</div>
<!-- (3) start to load world scripts  -->
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamescripts.world.php'); ?>
<script type="text/javascript" src="scripts/world.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-events.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-bindings.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-messages.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-menus.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-players.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-robots.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-items.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world-abilities.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.all.php'); ?>
<!-- (4) define game settings/config/indexes -->
<script type="text/javascript">
(function(){
    // Define the main configuration settings for the world map
    let _worldConfig = gameSettings.worldConfig;
    _worldConfig.userId = <?= rpg_game::get_userid() ?>;
    _worldConfig.playerId = <?= json_encode($this_prototype_data['this_player_id'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerToken = <?= json_encode($this_prototype_data['this_player_token'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerRobots = <?= json_encode($this_prototype_data['this_player_robots'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerAbilities = <?= json_encode($this_prototype_data['this_player_abilities'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerItemsIndex = <?= json_encode($this_prototype_data['this_player_items_index'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerStarsIndex = <?= json_encode($this_prototype_data['this_player_stars_index'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerRobotsIndex = <?= json_encode($this_prototype_data['this_player_robots_index'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerRobotsLimit = <?= json_encode($this_prototype_data['this_player_robots_limit'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerMobility = <?= json_encode($this_prototype_data['this_player_mobility'], JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerHistory = <?= json_encode($world_player_session_history, JSON_NUMERIC_CHECK) ?>;
    _worldConfig.backButtonURL = 'prototype.php';
    _worldConfig.homeButtonURL = 'world.php?world=<?= $default_world_token ?>&map=<?= $default_map_token ?>&position=spawn';
    _worldConfig.resetButtonURL = 'world.php?reset=world';
    // Load in the main content indexes for the world map if they exist
    if (typeof mmrpgIndex !== 'undefined'){
        let _worldIndexes = gameSettings.worldIndexes;
        _worldIndexes.types = typeof mmrpgIndex.types !== 'undefined' ? mmrpgIndex.types : {};
        _worldIndexes.players = typeof mmrpgIndex.players !== 'undefined' ? mmrpgIndex.players : {};
        _worldIndexes.robots = typeof mmrpgIndex.robots !== 'undefined' ? mmrpgIndex.robots : {};
        _worldIndexes.abilities = typeof mmrpgIndex.abilities !== 'undefined' ? mmrpgIndex.abilities : {};
        _worldIndexes.items = typeof mmrpgIndex.items !== 'undefined' ? mmrpgIndex.items : {};
        _worldIndexes.fields = typeof mmrpgIndex.fields !== 'undefined' ? mmrpgIndex.fields : {};
        _worldIndexes.stars = typeof mmrpgIndex.stars !== 'undefined' ? mmrpgIndex.stars : {};
        gameSettings.worldIndexes = mmrpgIndex;
        }
    // Define any additional map settings or flags that are more contextual
    _worldConfig.locationHasChanged = <?= json_encode($location_has_changed, JSON_NUMERIC_CHECK) ?>;
    _worldConfig.playerHasChanged = <?= json_encode($player_has_changed, JSON_NUMERIC_CHECK) ?>;
})();
</script>
<!-- (5) queue document ready events -->
<script type="text/javascript">
$(document).ready(function(){

    // Make sure the music button is in the appropriate place and then
    // start playing it in the background (use default if none defined)
    top.mmrpg_music_context('world');
    top.mmrpg_music_preload('misc/sound-effects-curated');
    top.mmrpg_music_load('<?= $map_field_music ?>', false, false);

    // Collect a ref to the game div then initialize the world map
    // (make sure to automatically pull events if we have a valid map)
    let $mmrpg = $('#mmrpg');
    let worldMapObject = null;
    if ($mmrpg.length){
        // Define objects for any custom config values or indexes
        let _config = gameSettings.worldConfig;
        let _indexes = gameSettings.worldIndexes;
        let customConfig = {};
        let customIndexes = {};
        // If the player was recently switched, make sure we start the zoom far-out
        let showPlayerSwitchIn = _config.playerHasChanged ? true : false;
        if (showPlayerSwitchIn){ customConfig.onReadyZoomIntoPlayer = true; }
        // If the location was recently changed, make sure we show the title banner
        let showLocationBanner = _config.locationHasChanged ? true : false;
        if (showLocationBanner){ customConfig.onReadyShowLocationBanner = true; }
        // Initialize the world map object with any config/settings predetermined
        worldMapObject = new mmrpgWorldMap($mmrpg, function(){
            //console.log('triggering custom onReady callback!');
            let _self = this;
            //let _config = _self.config;
            //let _indexes = _self.indexes;
            _self.triggerWindowEventsPull(1000);
            if (showPlayerSwitchIn){ _self.resetZoomLevel(true); }
            if (showLocationBanner){
                let _fieldsIndex = _indexes.fields || {};
                let worldName = _config.mapWorldName;
                let mapName = _config.mapName;
                let mapField = _config.mapField;
                let mapFieldInfo = _fieldsIndex[mapField] || {};
                let mapFieldName = mapFieldInfo.name || mapField.replace('-', ' ').toUpperCase();
                //console.log('-> worldName = '+worldName);
                //console.log('-> mapName = '+mapName);
                //console.log('-> mapField = '+mapField);
                //console.log('-> mapFieldInfo = ', mapFieldInfo);
                //console.log('-> mapFieldName = '+mapFieldName);
                //let titleText = worldName + ' &raquo; ' + mapName + ' &raquo; ';
                let titleText = mapName + ' &raquo;';
                let subtitleText = mapFieldName;
                setTimeout(function(){ _self.showTitleBanner(titleText, subtitleText, false, 2000); }, 300);
                }
            }, customConfig, customIndexes);
        }
    gameSettings.worldMapObject = worldMapObject;
    window.worldMapObject = worldMapObject;

    <? if (rpg_game::is_user()){ ?>
    // The user is logged-in so let's keep the session alive
    mmrpg_keep_session_alive(<?= rpg_game::get_userid() ?>);
    <? } ?>

    //console.log('$WORLD_PLAYER_SESSION[\'recent_actions\'] =', <?= json_encode($WORLD_PLAYER_SESSION['recent_actions'], true) ?>);
    //console.log('$WORLD_PLAYER_SESSION[\'pending_actions\'] =', <?= json_encode($WORLD_PLAYER_SESSION['pending_actions'], true) ?>);

});

</script>
<?
// Clear any temp session variables we shouldn't repeat (nope, we have a queue limit and handling for this now)
//unset($WORLD_PLAYER_SESSION['recent_actions']);
//unset($WORLD_PLAYER_SESSION['pending_actions']);
// Require the analytics file for tracking purposes
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
// Unset the database variable
unset($db);
?>
<?
// DEBUG DEBUG DEBUG
echo('<!-- [[debug]] --//'.PHP_EOL);
echo('  -> $world_data_parsed = '.trim(print_r($world_data_parsed, true)).PHP_EOL);
echo('  -> $map_data_parsed = '.trim(print_r($map_data_parsed, true)).PHP_EOL);
echo('//-- [[debug]] -->'.PHP_EOL);
// DEBUG DEBUG DEBUG
?>
</body>
</html>