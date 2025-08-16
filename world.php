<?php

// Include the TOP file
require_once('top.php');
//error_log('---------------------------------');

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
$mapfile_basepath = 'prototype/worldmaps/';
rpg_world::set_basepath($mapfile_basepath);
$mapfile_basedir = rpg_world::$worldmap_basedir.rpg_world::$worldmap_basepath;
//error_log('$mapfile_basedir = '. print_r($mapfile_basedir, true));

// Collect the game session token in case we need it later
$game_session_token = rpg_game::session_token();
$GAME_SESSION = &$_SESSION[$game_session_token];

// Define a reference object for storing temporary world data
rpg_world::init_session();
$world_session_token = rpg_world::session_token();
$WORLD_SESSION = &$_SESSION[$world_session_token];

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
    //rpg_world::save_session();
    // Now that we're done saving, return a success response
    header('Content-Type: application/json');
    echo(json_encode(array('status' => 'success', 'message' => 'World data saved successfully.')));
    exit();
}

// Pull in a few indexes that we'll need for below
$mmrpg_index_fields = rpg_field::get_index(true);
$mmrpg_index_players = rpg_player::get_index(true);
$mmrpg_index_robots = rpg_robot::get_index(true);
$mmrpg_index_abilities = rpg_ability::get_index(true);
$mmrpg_index_items = rpg_item::get_index(true);
$mmrpg_indexes = array(
    'fields' => &$mmrpg_index_fields,
    'players' => &$mmrpg_index_players,
    'robots' => &$mmrpg_index_robots,
    'abilities' => &$mmrpg_index_abilities,
    'items' => &$mmrpg_index_items,
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
$WORLD_SESSION['player_sessions']['last_player'] = $this_prototype_data['this_current_player'];
$WORLD_SESSION['player_sessions']['allowed'] = $allowed_player_tokens; // store the allowed player tokens in the session

// Now that we have the player, we should collect the player's robots and other data
$this_player_id = 1;
$this_player_token = 'player';
$this_player_info = array();
$this_player_robots = array();
$this_player_robots_index = array();
$this_prototype_data['this_player_id'] = $this_player_id; // required
$this_prototype_data['this_player_token'] = $this_player_token; // required
$this_prototype_data['this_player_robots'] = $this_player_robots; // required
$this_prototype_data['this_player_robots_index'] = $this_player_robots_index; // required
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
if (!empty($allowed_player_robots) && count($current_player_robots) < $max_player_robots){
    //error_log('Because $current_player_robots = '. print_r($current_player_robots, true));
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
    //error_log('$current_player_robots = '.print_r($current_player_robots, true));
    foreach ($current_player_robots AS $robot_token){
        if (empty($mmrpg_index_robots[$robot_token])){ continue; }
        $robot_info = $mmrpg_index_robots[$robot_token];
        $robot_id = $robot_info['robot_id'];
        $robot_string = $robot_id . '_' . $robot_token;
        $robot_info = rpg_world::get_player_robot_overview($this_player_token, $robot_token, $robot_id);
        $this_player_robots[] = $robot_string;
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

// Update the player's mobility with any character-specific bonuses or contextual modifiers
if ($this_prototype_data['this_player_token'] === 'player'){ $this_prototype_data['this_player_mobility'] = -1; }
else { $this_prototype_data['this_player_mobility'] = MMRPG_WORLD_DEFAULT_MOBILITY; }

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
$request_world_position = isset($_REQUEST['position']) && preg_match('/^([-0-9]+)$/i', $_REQUEST['position']) ? trim($_REQUEST['position']) : '';
$request_world_direction = isset($_REQUEST['direction']) && preg_match('/^([-a-z0-9]+)$/i', $_REQUEST['direction']) ? trim($_REQUEST['direction']) : '';
if (empty($request_world_position) && !empty($WORLD_PLAYER_SESSION['last_position'])){ $request_world_position = $WORLD_PLAYER_SESSION['last_position']; }
if (empty($request_world_direction) && !empty($WORLD_PLAYER_SESSION['last_direction'])){ $request_world_direction = $WORLD_PLAYER_SESSION['last_direction']; }
$this_prototype_data['this_current_position'] = !empty($request_world_position) ? $request_world_position : $default_world_position;
$this_prototype_data['this_current_direction'] = !empty($request_world_direction) ? $request_world_direction : $default_world_direction;
$WORLD_PLAYER_SESSION['last_position'] = $this_prototype_data['this_current_position'];
$WORLD_PLAYER_SESSION['last_direction'] = $this_prototype_data['this_current_direction'];
$request_world_position = $this_prototype_data['this_current_position'];
$request_world_direction = $this_prototype_data['this_current_direction'];
//error_log('$allowed_world_tokens = '. print_r($allowed_world_tokens, true));
//error_log('$allowed_world_map_tokens = '. print_r($allowed_world_map_tokens, true));
//error_log('$allowed_world_sheet_tokens = '. print_r($allowed_world_sheet_tokens, true));
//error_log('$this_prototype_data = '. print_r($this_prototype_data, true));
//error_log('$WORLD_PLAYER_SESSION = '. print_r($WORLD_PLAYER_SESSION, true));

// Now that we've collected required args that may have been passed in the URL, reload w/o them to prevent double-entry
if (!empty($_REQUEST['player'])
    || !empty($_REQUEST['robots'])
    || !empty($_REQUEST['world'])
    || !empty($_REQUEST['map'])
    || !empty($_REQUEST['position'])
    || !empty($_REQUEST['direction'])){
    // Redirect to the clean world URL
    header('Location: world.php');
    exit();
}

// Load map data from the appropriate map file
$world_map_token = $this_prototype_data['this_current_world'].'__'.$this_prototype_data['this_current_map'];
$world_token = $this_prototype_data['this_current_world'];
$map_token = $this_prototype_data['this_current_map'];
$map_name = str_replace(' AREA ', ' Area ', strtoupper(str_replace('-', ' ', $map_token)));
$map_data_parsed = rpg_world::load_map_data($world_token.'__'.$map_token);
$map_sprite_sheet = !empty($map_data_parsed) && !empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : '';
//error_log('$world_map_token = '.print_r($world_map_token, true));
//error_log('$world_token = '.print_r($world_token, true));
//error_log('$map_token = '.print_r($map_token, true));
//error_log('$map_name = '.print_r($map_name, true));
//error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
//error_log('$map_sprite_sheet = '.print_r($map_sprite_sheet, true));
if (empty($map_data_parsed)){ error_log('MMRPG World Fatal Error - No world data defined for map "'.$world_map_token.'"!'); die(); }
if (empty($map_sprite_sheet)){ error_log('MMRPG World Fatal Error - No sprite sheet defined for map "'.$world_map_token.'"!'); die(); }

// Make sure there's room in relevant session arrays for this map's data
if (!isset($WORLD_SESSION['world_maps'][$world_map_token])){ $WORLD_SESSION['world_maps'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_encounters'][$world_map_token])){ $WORLD_SESSION['world_encounters'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_buttons'][$world_map_token])){ $WORLD_SESSION['world_buttons'][$world_map_token] = array(); }
if (!isset($WORLD_SESSION['world_switches'][$world_map_token])){ $WORLD_SESSION['world_switches'][$world_map_token] = array(); }

// Collect the map's field token and mecha encounters
$map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
$map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
$map_field_background = !empty($map_field_info['field_background']) ? $map_field_info['field_background'] : 'field';
$map_field_foreground = !empty($map_field_info['field_foreground']) ? $map_field_info['field_foreground'] : 'field';
$map_field_music = !empty($map_field_info['field_music']) ? $map_field_info['field_music'] : 'misc/star-force'; // TODO: find a better default for this
//error_log('$map_field_token = '.print_r($map_field_token, true));
//error_log('$map_field_info = '.print_r($map_field_info, true));
//error_log('$map_field_background = '.print_r($map_field_background, true));
//error_log('$map_field_foreground = '.print_r($map_field_foreground, true));
//error_log('$map_field_music = '.print_r($map_field_music, true));

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
if (empty($this_prototype_data['this_current_position'])){ $this_prototype_data['this_current_position'] = $map_spawn_pos; }

// If the encounters for this map have not been generated yet, we can do so now
$reset_encounters = !empty($_GET['reset']) && $_GET['reset'] === 'encounters' ? true : false;
$world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
$world_map_encounters = !empty($world_encounters[$world_map_token]) ? $world_encounters[$world_map_token] : array();
if (empty($world_map_encounters) || $reset_encounters === true){
    $world_map_encounters = rpg_world::generate_worldmap_encounters($this_prototype_data, $map_data_parsed);
    rpg_world::update_session('world_encounters', $world_map_token, $world_map_encounters);
}

// If there are any portals define, check to see if any are being covered by battles or obstacles
if (!empty($map_data_parsed['portals'])){
    //error_log('[portal-check] checking for world map portals on map "'.$world_map_token.'"');
    //error_log('-> $map_data_parsed[\'portals\'] = '.print_r($map_data_parsed['portals'], true));
    //error_log('-> $world_map_encounters = '.print_r($world_map_encounters, true));
    $active_map_encounters = array_filter($world_map_encounters, function($encounter){
        $battle = $encounter[4]; return rpg_battle::has_index_info($battle);
        });
    $active_encounter_cells = array_map(function($encounter){
        $battle = $encounter[3]; return $battle;
        }, $active_map_encounters);
    //error_log('-> $active_map_encounters = '.print_r($active_map_encounters, true));
    //error_log('-> $active_encounter_cells = '.print_r($active_encounter_cells, true));
    foreach ($map_data_parsed['portals'] AS $portal_name => $portal_data){
        if (empty($portal_data) || !is_array($portal_data)){ continue; }
        //error_log('[portal-check] checking portal w/ name "'.$portal_name.'" & data ['.implode(', ', $portal_data).']');
        if (!in_array($portal_data[0], $active_encounter_cells)){ continue; }
        //error_log('[portal-check] portal "'.$portal_name.'" at position "'.$portal_data[0].'" is occupied by an encounter!');
        // lock this portal as it's currently occupied by an encounter
        $portal_data[] = 'locked';
        $map_data_parsed['portals'][$portal_name] = $portal_data;
    }
    //error_log('-> $map_data_parsed[\'portals\'] (new) = '.print_r($map_data_parsed['portals'], true));
}

// If there are any buttons defined, check to see if any of them have been pushed already
if (!empty($map_data_parsed['buttons'])){
    $button_sprites = $map_data_parsed['buttons'];
    $world_buttons = !empty($WORLD_SESSION['world_buttons'][$world_map_token]) ? $WORLD_SESSION['world_buttons'][$world_map_token] : array();
    foreach ($button_sprites AS $button_name => $button_data){
        if (empty($button_data) || !is_array($button_data)){ continue; }
        $position = $button_data[0]; unset($button_data[0]);
        $colour = !empty($button_data[1]) ? $button_data[1] : 'black'; unset($button_data[1]);
        $state = !empty($button_data[2]) ? $button_data[2] : 'up'; unset($button_data[2]);
        $action = !empty($button_data[3]) ? $button_data[3] : ''; unset($button_data[3]);
        $hidden = false; if (in_array('hidden', $button_data)){ $hidden = true; unset($button_data[array_search('hidden', $button_data)]); }
        $locked = false; if (in_array('locked', $button_data)){ $locked = true; unset($button_data[array_search('locked', $button_data)]); }
        $data = array_values($button_data);
        if (!empty($world_buttons[$button_name])){ $state = $world_buttons[$button_name]; }
        if ($state !== 'down'){ continue; }
        //error_log('[button-check] world map button "'.$button_name.'" is already DOWN!');
        // ...
        // event action SET-GROUP-TERRAIN for buttons, switches, etc. to use
        if ($action === 'set-group-terrain'){
            //error_log('world map button "'.$button_name.'" is setting group terrain');
            //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
            $group_name = !empty($data[0]) ? $data[0] : '';
            $terrain_name = !empty($data[1]) ? $data[1] : '';
            //error_log('$group_name = '.print_r($group_name, true));
            //error_log('$terrain_name = '.print_r($terrain_name, true));
            if (!$group_name){ error_log('-> missing group name for button "'.$button_name.'"'); continue; }
            if (!$terrain_name){ error_log('-> missing terrain name for button "'.$button_name.'"'); continue; }
            $groups_index = !empty($map_data_parsed['groups']) ? $map_data_parsed['groups'] : array();
            $group_tiles = !empty($groups_index[$group_name]) ? $groups_index[$group_name] : array();
            $tiles_index = !empty($map_data_parsed['tiles']) ? $map_data_parsed['tiles'] : array();
            $layer_key = 0; // currently, this is the main and only terrain layer
            $layer_tiles = !empty($map_data_parsed['layers'][$layer_key]) ? $map_data_parsed['layers'][$layer_key] : array();
            //error_log('$groups_index = '.print_r($groups_index, true));
            //error_log('$group_tiles = '.print_r($group_tiles, true));
            //error_log('$tiles_index = '.print_r($tiles_index, true));
            //error_log('$layer_tiles = '.print_r($layer_tiles, true));
            if (!$groups_index){ error_log('-> no groups defined for this map'); continue; }
            if (!$group_tiles){ error_log('-> no group tiles defined for "'.$group_name.'"'); continue; }
            if (!$tiles_index){ error_log('-> no tiles index defined for this map'); continue; }
            if (!$layer_tiles){ error_log('-> no layer tiles defined for this map'); continue; }
            if (!empty($group_tiles)){
                foreach ($group_tiles AS $tile_key){
                    //error_log('-> processing tile key "'.$tile_key.'"');
                    list($col, $row) = explode('-', $tile_key);
                    $tx = $col - 1; $ty = $row - 1;
                    //error_log('--> $col = '.$col.', $row = '.$row);
                    if (!isset($layer_tiles[$ty])){ error_log('-> layer row "'.$ty.'" not found in layer tiles'); continue; }
                    //error_log('--> $layer_tiles['.$ty.'](raw) = '.print_r($layer_tiles[$ty], true));
                    $row_tiles = !empty($layer_tiles[$ty]) ? $layer_tiles[$ty] : '';
                    $row_tiles = !empty($row_tiles) ? str_replace(array('[', ']'), '', $row_tiles) : '';
                    $row_tiles = !empty($row_tiles) ? (strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles)) : array();
                    //error_log('--> $row_tiles(parsed) = '.print_r($row_tiles, true));
                    if (empty($row_tiles[$tx])){ error_log('-> tile key "'.$tile_key.'" not found in row tiles'); continue; }
                    $current_tile_value = $row_tiles[$tx];
                    $new_tile_value = $terrain_name;
                    //error_log('--> $current_tile_value = '.print_r($current_tile_value, true));
                    //error_log('--> $new_tile_value = '.print_r($new_tile_value, true));
                    $row_tiles[$tx] = $new_tile_value;
                    $row_tiles = implode(',', $row_tiles);
                    $layer_tiles[$ty] = $row_tiles;
                    //error_log('--> $layer_tiles['.$ty.'](updated) = '.print_r($layer_tiles[$ty], true));
                }
                $map_data_parsed['layers'][$layer_key] = $layer_tiles;
            }
        }
        // ...
    }
}

// Automatically save the world session w/ any recent changes
//rpg_world::save_session();

// Define some fallback values for compatibility
$debug_flag_animation = true;
$flag_skip_fadein = true;

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
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/world.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
</head>
<body id="mmrpg" class="world <?= 'env_'.MMRPG_CONFIG_SERVER_ENV ?>">
<div id="world" class="hidden <?= $flag_skip_fadein ? 'fastfade' : '' ?>">
    <div id="canvas">
        <div class="wrapper">
            <?

            // Grab the background sprite for this map and place a copy of it here behind everything else (for when the user over-zooms)
            $background_layer_sprite = rpg_world::get_background_layer_sprite($this_prototype_data, $map_data_parsed, true);
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
                $data = array();
                $data['map_world'] = $map_data_parsed['world'];
                $data['map_token'] = $map_data_parsed['token'];
                $data['map_name'] = $map_data_parsed['name'];
                $data['map_image'] = 'images/maps/'.(!empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : 'undefined.png');
                $data['map_size'] = array($map_col_size, $map_row_size);
                $data['tile_size'] = array($map_tile_width, $map_tile_height);
                $data['tiles_index'] = $map_data_parsed['tiles'];
                $data['sprites_index'] = $map_data_parsed['sprites'];
                $data['groups_index'] = $map_data_parsed['groups'];
                $data['start_position'] = $this_prototype_data['this_current_position'];
                $data['start_direction'] = $this_prototype_data['this_current_direction'];
                $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                echo('<script data-json="mapData" type="application/json">'.$data_json.'</script>'.PHP_EOL);

                // BACKGROUND IMAGE
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                $background_layer_sprite = rpg_world::get_background_layer_sprite($this_prototype_data, $map_data_parsed);
                echo('<div class="layer layer-0 background" data-layer="background" '.$map_layer_styles.$map_layer_attrs.'>');
                    echo($background_layer_sprite);
                echo('</div>'.PHP_EOL);

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
                echo('</div>'.PHP_EOL);

                // ALL OBJECT SPRITES
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                echo('<div class="layer layer-3 sprites objects" data-layer="sprites/objects" '.$map_layer_styles.$map_layer_attrs.'>');
                    // BATTLE OBJECT SPRITES
                    $battles_layer_markup = rpg_world::get_battles_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($battles_layer_markup);
                    // TEAM & RIVAL OBJECT SPRITES
                    $team_layer_markup = rpg_world::get_team_layer_markup($this_prototype_data, $map_data_parsed);
                    $rivals_layer_markup = rpg_world::get_rivals_layer_markup($this_prototype_data, $map_data_parsed);
                    echo($team_layer_markup);
                    echo($rivals_layer_markup);
                echo('</div>'.PHP_EOL);


                // END OF LAYERS
                ?>
            </div>
            <?
            // Define or generate the markup for the various buttons and UI elements around the world map
            $wrap_markup = function($markup, $tag = 'div'){ if (empty($markup)){ return ''; } return '<'.$tag.' class="wrapper">'.$markup.'</'.$tag.'>'; };
            $back_button_markup = $wrap_markup('<i class="fa fas fa-sign-out"></i>', 'a');
            $home_button_markup = $wrap_markup('<i class="fa fas fa-home"></i>', 'a');
            $reset_button_markup = $wrap_markup('<i class="fa fas fa-bomb"></i>', 'a');
            $position_display_markup = $wrap_markup('&hellip;');
            $side_buttons_markup = $wrap_markup('&hellip;');
            $player_switcher_players = $allowed_player_tokens;
            $robot_overview_robots = $current_player_robots;
            $player_switcher_markup = $wrap_markup(rpg_world::get_player_switcher_markup($this_prototype_data, $player_switcher_players));
            $robots_overview_markup = $wrap_markup(rpg_world::get_robots_overview_markup($this_prototype_data, $robot_overview_robots));
            ?>
            <div id="back-button" class="chrome chrome-button"><?= $back_button_markup ?></div>
            <div id="home-button" class="chrome chrome-button"><?= $home_button_markup ?></div>
            <div id="reset-button" class="chrome chrome-button"><?= $reset_button_markup ?></div>
            <div id="position-display" class="chrome"><?= $position_display_markup ?></div>
            <div id="side-buttons" class="chrome"><?= $side_buttons_markup ?></div>
            <? if (!empty($player_switcher_markup)){ ?><div id="player-switcher" class="chrome"><?= $player_switcher_markup ?></div><? } ?>
            <? if (!empty($robots_overview_markup)){ ?><div id="robots-overview" class="chrome"><?= $robots_overview_markup ?></div><? } ?>

            <?
            // DEBUG DEBUG DEBUG
            echo('<pre data-var="$map_data_parsed" style="display: none;"><!-- $map_data_parsed = '.print_r($map_data_parsed, true).' --></pre>');
            ?>
        </div>
    </div>
</div>
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/world.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">

// Update relevent game settings and flags
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>

// Update relevant world-specific game settings and flags
let _worldConfig = gameSettings.worldConfig;
_worldConfig.userId = <?= rpg_game::get_userid() ?>;
_worldConfig.playerId = <?= json_encode($this_prototype_data['this_player_id']) ?>;
_worldConfig.playerToken = <?= json_encode($this_prototype_data['this_player_token']) ?>;
_worldConfig.playerRobots = <?= json_encode($this_prototype_data['this_player_robots']) ?>;
_worldConfig.playerRobotsIndex = <?= json_encode($this_prototype_data['this_player_robots_index']) ?>;
_worldConfig.playerMobility = <?= json_encode($this_prototype_data['this_player_mobility']) ?>;
_worldConfig.backButtonURL = 'prototype.php';
//_worldConfig.homeButtonURL = 'world.php?world=<?= $default_world_token ?>&map=<?= $default_map_token ?>&position=<?= $default_world_position ?>';
_worldConfig.homeButtonURL = 'world.php?world=<?= $default_world_token ?>&map=<?= $default_map_token ?>';
_worldConfig.resetButtonURL = 'world.php?reset=world';

// Create the document ready events
$(document).ready(function(){

    // Immediately clear the selected player setting as it shouldn't be this

    // Make sure the music button is in the appropriate place
    top.mmrpg_music_context('world');

    // Start the music playing in the background (default if none for this field)
    parent.mmrpg_music_load('<?= $map_field_music ?>', false, false);

    // Collect a ref to the game div then initialize the world map
    let $mmrpg = $('#mmrpg');
    if ($mmrpg.length){
        //console.log('%c' + 'Creating new mmrpgWorldMap object...', 'color: green;');
        let worldMapObject = new mmrpgWorldMap($mmrpg);
        gameSettings.worldMapObject = worldMapObject;
        window.worldMapObject = worldMapObject;
        }

    <? if (rpg_game::is_user()){ ?>
        // The user is logged-in so let's keep the session alive
        mmrpg_keep_session_alive(<?= rpg_game::get_userid() ?>);
    <? } ?>

});

</script>
<?
// Require the analytics file for tracking purposes
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
// Unset the database variable
unset($db);
?>
</body>
</html>