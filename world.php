<?php

// Include the TOP file
require_once('top.php');

// Define any world session vars that don't exist yet
$reset = !empty($_REQUEST['reset']) && $_REQUEST['reset'] === 'world' ? true : false;
if (!isset($_SESSION['WORLD']) || $reset){ $_SESSION['WORLD'] = array(); }
if (!isset($_SESSION['WORLD_TEMP']) || $reset){ $_SESSION['WORLD_TEMP'] = array(); }
if ($reset){ header('Location: world.php'); exit(); }

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

// Define a reference object for storing temporary world data
$WORLD_SESSION = &$_SESSION['WORLD'];

// Define some constants for the world map
define('MMRPG_WORLD_DEFAULT_MAPSIZE', 10);
define('MMRPG_WORLD_DEFAULT_TILESIZE', 40);
define('MMRPG_WORLD_DEFAULT_TEAMSIZE', 3); // TODO: make this dependant on limit hearts
define('MMRPG_WORLD_MAPFILE_BASEPATH', 'prototype/worldmaps/');

// Define defaults and allowed values for the prototype world data
//$allowed_world_tokens = array('starter', 'water', 'starter-80x80', 'water-80x80');
$existing_map_files = glob(MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH.'*.map');
$allowed_world_tokens = array_map(function($path){ return preg_replace('/\.map$/i', '', basename($path)); }, $existing_map_files);
//error_log('$existing_map_files = '. print_r($existing_map_files, true));
//error_log('$allowed_world_tokens = '. print_r($allowed_world_tokens, true));
$allowed_player_tokens = mmrpg_prototype_players_unlocked(true);
$default_world_token = 'debug-area-1'; //'starter-80x80';
$default_player_token = 'player';
$default_world_position = '';

//error_log('$_GET = '. print_r($_GET, true));
//error_log('$_POST = '. print_r($_POST, true));

// If a save action was requested, we should do it here and then return exit
if (!empty($_POST['action']) && $_POST['action'] === 'save'
    && !empty($_POST['world_data']) && is_array($_POST['world_data'])){
    $worldData = $_POST['world_data'];
    if (!empty($worldData['lastWorld']) && in_array($worldData['lastWorld'], $allowed_world_tokens)){
        $WORLD_SESSION['last_world_token'] = $worldData['lastWorld'];
    }
    if (!empty($worldData['lastPlayer']) && in_array($worldData['lastPlayer'], $allowed_player_tokens)){
        $WORLD_SESSION['last_player_token'] = $worldData['lastPlayer'];
    }
    if (!empty($worldData['lastPosition']) && preg_match('/^([-0-9]+)$/i', $worldData['lastPosition'])){
        $WORLD_SESSION['last_world_position'] = $worldData['lastPosition'];
    }
    if (!empty($worldData['lastDirection']) && preg_match('/^([-a-z0-9]+)$/i', $worldData['lastDirection'])){
        $WORLD_SESSION['last_world_direction'] = $worldData['lastDirection'];
    }
    // that's all we support for now, return a success response
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
    'items' => &$mmrpg_index_items
    );

// Define a quick function for translating singular kinds to plural kinds
// TODO:  Find the class method that already does this if exists, else create
$get_xkind = function($kind){
    switch($kind){
        case 'object': return 'objects';
        case 'player': return 'players';
        case 'robot': return 'robots';
        case 'ability': return 'abilities';
        case 'item': return 'items';
        case 'skill': return 'skills';
        case 'field': return 'fields';
        default: return false;
        }
    };

// Define a quick function for getting a random position on a given grid
// TODO: Define this as an actual function instead of a variable
$get_randpos = function($available_encounter_cells){
    static $used;
    if (!$used){ $used = array(); }
    if (empty($available_encounter_cells)){ return false; }
    do { $pos = $available_encounter_cells[mt_rand(0, count($available_encounter_cells) - 1)]; } while (in_array($pos, $used));
    $used[] = $pos;
    return $pos;
    };

// create a reusable method for the above that takes args and generates markup to return as a string
// TODO:  Define this as an actual function instead of a variable
$get_sprite = function($kind, $token, $alt = '', $dir = 'right', $class = '', $styles = '', $attrs = ''){
    global $mmrpg_indexes, $get_xkind;
    $xkind = $get_xkind($kind);
    if (!isset($mmrpg_indexes[$xkind][$token])){
        //error_log('error: $mmrpg_indexes['.$xkind.']['.$token.'] does not exist!');
        return false;
        }
    $info = $mmrpg_indexes[$xkind][$token];
    $anim = 0;
    if ($kind === 'player'){ $anim = rpg_player::get_css_animation_duration($info); }
    elseif ($kind === 'robot'){ $anim = rpg_robot::get_css_animation_duration($info); }
    if (!empty($anim)){ $styles .= ' animation-duration: '.$anim.'s;'; }
    //error_log('$info = '.print_r($info, true));
    //error_log('$anim = '.print_r($anim, true));
    $dir = 'right';
    $img = $info[$kind.'_image'];
    $size = $info[$kind.'_image_size'];
    $xsize = $size. 'x'.$size;
    $sprite_path = 'images/'.$xkind.'/'.$img.($alt ? '_'.$alt : '').'/sprite_'.$dir.'_'.$xsize.'.png';
    $sprite_class = 'sprite '.$kind.($class ? ' '.$class : '');
    $sprite_styles = ($styles ? ' style="'.$styles.'"' : '');
    $sprite_attrs = ' data-size="'.$size.'"'.($attrs ? ' '.$attrs : '');
    return('<span class="'.$sprite_class.'"'.$sprite_styles.$sprite_attrs.'><span class="sprite sprite_'.$xsize.'" style="background-image: url('.$sprite_path.');"></span></span>');
    };


// Define a function for loading a given map's data from the filesystem
function loadMapData($map_token){
    //error_log('loadMapData() called!');
    static $map_basedir = MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH;
    static $map_tilesize = MMRPG_WORLD_DEFAULT_TILESIZE;
    if (empty($map_token)){
        //error_log('loadMapData() error: missing map token!');
        return false;
        }
    $map_filename = $map_token.'.map';
    $map_filedir = $map_basedir.$map_filename;
    if (!file_exists($map_filedir)){
        //error_log('loadMapData() file not found "'.$map_filedir.'"!');
        return false;
        }
    $map_data_raw = file_get_contents($map_filedir);
    if (empty($map_data_raw)){
        //error_log('loadMapData() file empty "'.$map_filedir.'"!');
        return false;
        }
    $map_data_array = explode("\n", trim($map_data_raw));
    $map_data_vars = array();
    $map_layer_key = 0;
    $map_data_layers = array();
    foreach ($map_data_array AS $line){
        $line = trim($line);
        // Ignore empty lines and comments
        if (empty(trim($line))){ continue; }
        else if (strpos($line, '#') === 0){ continue; } // Ignore comments
        else if (strpos($line, '//') === 0){ continue; } // Ignore comments
        // If this is a variable line, pull it (ie. @foo = bar)
        // ie. void(0.1,-3.5) => void, 0.1, 13,5
        if (strpos($line, '@') === 0){
            if (!strstr($line, '=')){ continue; }
            $line = preg_replace('/\s+\=\s+/i', '=', trim($line, '@ '));
            list($name, $value) = explode('=', $line, 2);
            if (strstr($name, '[') && strstr($name, ']')){
                $key = substr($name, strpos($name, '[') + 1, -1);
                $name = substr($name, 0, strpos($name, '['));
                if (!isset($map_data_vars[$name])){ $map_data_vars[$name] = array(); }
                if ($key === ''){ $map_data_vars[$name][] = $value; }
                else { $map_data_vars[$name][$key] = $value; }
                } else {
                $map_data_vars[$name] = $value;
                }
            continue;
            }
        // Otherwise, this is a valid line so add it to the map data
        if (!isset($map_data_layers[$map_layer_key])){ $map_data_layers[$map_layer_key] = array(); }
        $map_data_layers[$map_layer_key][] = $line;
    }
    //error_log('$map_data_vars = '.print_r($map_data_vars, true));
    // Review and process the map layer data
    $map_autocols = strlen($map_data_layers[0][0]);
    $map_autorows = count($map_data_layers[0]);
    $map_tiles_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntax: name(key,x,y) ie. void(0,20,20) => name:void, key:0, x:20, y:20
    $map_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntax: name(x,y) ie. spawn(4,4) => name:spawn, x:4, y:4
    static $map_custval_parser;
    if (!$map_custval_parser){
        $map_custval_parser = function($raw_tiles, $include_keys = false) use ($map_tiles_custval_regex, $map_other_custval_regex){
            if (empty($raw_tiles)){ return array(); }
            $parsed_keys = array();
            $parsed_tiles = array();
            foreach ($raw_tiles AS $line){
                $line = trim(str_replace(' ', '', $line));
                if (empty($line)){ continue; }
                $is_tile_custval = preg_match($map_tiles_custval_regex, $line);
                $is_other_custval = preg_match($map_other_custval_regex, $line);
                if (!$is_tile_custval && !$is_other_custval){ continue; }
                if ($is_tile_custval){
                    list($name, $k, $x, $y) = explode('/', preg_replace($map_tiles_custval_regex, '$1/$2/$3/$4', $line), 4);
                    $parsed_tiles[$name] = array($x, $y);
                    $parsed_keys[intval($k)] = $name;
                    continue;
                    }
                if ($is_other_custval){
                    list($name, $x, $y) = explode('/', preg_replace($map_other_custval_regex, '$1/$2/$3', $line), 3);
                    $parsed_tiles[$name] = array($x, $y);
                    continue;
                    }
                }
            if ($include_keys){ $parsed_tiles['keys'] = $parsed_keys; }
            return $parsed_tiles;
            };
        }
    //error_log('raw $map_data_vars = '.print_r($map_data_vars, true));
    $map_data_vars['token'] = isset($map_data_vars['token']) ? $map_data_vars['token'] : '';
    $map_data_vars['name'] = isset($map_data_vars['name']) ? $map_data_vars['name'] : '';
    $map_data_vars['size'] = isset($map_data_vars['size']) ? $map_data_vars['size'] : '';
    $map_data_vars['sheet'] = isset($map_data_vars['sheet']) ? $map_data_vars['sheet'] : '';
    $map_data_vars['field'] = isset($map_data_vars['field']) ? $map_data_vars['field'] : '';
    $map_data_vars['mechas'] = isset($map_data_vars['mechas']) ? $map_data_vars['mechas'] : array();
    $map_data_vars['tiles'] = isset($map_data_vars['tiles']) ? $map_data_vars['tiles'] : array();
    $map_data_vars['sprites'] = isset($map_data_vars['sprites']) ? $map_data_vars['sprites'] : array();
    $map_data_vars['portals'] = isset($map_data_vars['portals']) ? $map_data_vars['portals'] : array();
    if (empty($map_data_vars['token'])){ $map_data_vars['token'] = $map_token; }
    if (empty($map_data_vars['name'])){ $map_data_vars['name'] = 'Undefined'; }
    if (empty($map_data_vars['size'])){ $map_data_vars['size'] = '0 x 0 x 0'; }
    if (empty($map_data_vars['sheet'])){ $map_data_vars['sheet'] = 'undefined.png'; }
    if (empty($map_data_vars['field'])){ $map_data_vars['field'] = 'field'; }
    if (!empty($map_data_vars['size'])){ $map_data_vars['size'] = explode('x', str_replace(' ', '', $map_data_vars['size'])); }
    if (!isset($map_data_vars['size'][0])){ $map_data_vars['size'][0] = $map_autocols; }
    if (!isset($map_data_vars['size'][1])){ $map_data_vars['size'][1] = $map_autorows; }
    if (!isset($map_data_vars['size'][2])){ $map_data_vars['size'][2] = $map_tilesize; }
    if (!empty($map_data_vars['mechas'])){ $map_data_vars['mechas'] = explode(',', str_replace(' ', '', $map_data_vars['mechas'])); }
    if (empty($map_data_vars['tiles'])){ $map_data_vars['tiles'][] = 'undefined(0,0)'; }
    if (empty($map_data_vars['sprites'])){ $map_data_vars['sprites'][] = 'undefined(0,0)'; }
    if (empty($map_data_vars['portals'])){ $map_data_vars['portals'][] = 'undefined(0,0)'; }
    $map_data_vars['tiles'] = $map_custval_parser($map_data_vars['tiles'], true);
    $map_data_vars['sprites'] = $map_custval_parser($map_data_vars['sprites']);
    $map_data_vars['portals'] = $map_custval_parser($map_data_vars['portals']);
    // Add collected data to the parsed map data
    $map_data_parsed = array();
    $map_data_parsed['token'] = $map_data_vars['token']; unset($map_data_vars['token']);
    $map_data_parsed['name'] = $map_data_vars['name']; unset($map_data_vars['name']);
    $map_data_parsed['size'] = $map_data_vars['size']; unset($map_data_vars['size']);
    $map_data_parsed['sheet'] = $map_data_vars['sheet']; unset($map_data_vars['sheet']);
    $map_data_parsed['field'] = $map_data_vars['field']; unset($map_data_vars['field']);
    $map_data_parsed['mechas'] = $map_data_vars['mechas']; unset($map_data_vars['mechas']);
    $map_data_parsed['tiles'] = $map_data_vars['tiles']; unset($map_data_vars['tiles']);
    $map_data_parsed['sprites'] = $map_data_vars['sprites']; unset($map_data_vars['sprites']);
    $map_data_parsed['portals'] = $map_data_vars['portals']; unset($map_data_vars['portals']);
    //$map_data_parsed['tiles']['keys'] = array_keys($map_data_parsed['tiles']);
    $map_data_parsed['layers'] = $map_data_layers;
    if (!empty($map_data_vars)){ $map_data_parsed['vars'] = $map_data_vars; }
    //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
    return $map_data_parsed;
}

// Define a function for returning all the available cells that encounters can appear on for a given map
function getMapEncounterCells($map_data){
    // First we gather the map col and row size so we can generate all possible positions
    $map_col_size = isset($map_data['size'][0]) ? $map_data['size'][0] : MMRPG_WORLD_DEFAULT_MAPSIZE;
    $map_row_size = isset($map_data['size'][1]) ? $map_data['size'][1] : MMRPG_WORLD_DEFAULT_MAPSIZE;
    $available_cells = array();
    for ($row = 1; $row <= $map_row_size; $row++){
        for ($col = 1; $col <= $map_col_size; $col++){
            $pos = $col.'-'.$row;
            if (isset($available_cells[$pos])){ continue; }
            $available_cells[$pos] = true;
        }
    }
    // Now let's loop through portals and remove spaces that have portals on them
    if (!empty($map_data['portals']) && is_array($map_data['portals'])){
        foreach ($map_data['portals'] AS $portal_name => $portal_data){
            if (empty($portal_data) || !is_array($portal_data) || count($portal_data) < 2){ continue; }
            list($x, $y) = $portal_data;
            $pos = $x.'-'.$y;
            unset($available_cells[$pos]);
        }
    }
    // Then we through all the tiles and remove any that are unwalkable "void" type
    if (!empty($map_data['tiles']) && !empty($map_data['tiles']['keys']) && !empty($map_data['layers'])){
        $tilesIndex = $map_data['tiles']['keys'];
        $tileLayers = $map_data['layers'];
        foreach ($tileLayers AS $layer_key => $layer_tiles){
            foreach ($layer_tiles AS $row_key => $row_tiles){
                $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
                $row_tiles = array_map('intval', $row_tiles);
                foreach ($row_tiles AS $col_key => $tile_key){
                    if (!isset($tilesIndex[$tile_key])){ continue; }
                    $tile_token = $tilesIndex[$tile_key];
                    // If this tile is a "void" type, remove it from the available cells
                    if ($tile_token === 'void' || strpos($tile_token, 'void') === 0){
                        $pos = ($col_key + 1).'-'.($row_key + 1);
                        unset($available_cells[$pos]);
                    }
                }
            }
        }
    }
    // Return the available cells as an array of positions
    $available_cells = array_keys($available_cells);
    return $available_cells;
}

// Define or collect the prototype data for the player, their robots, etc.
$this_prototype_data = array();
$this_prototype_data['this_current_chapter'] = -1; // required
$this_prototype_data['this_current_world'] = ''; // required
$this_prototype_data['this_current_position'] = ''; // required
$this_prototype_data['battle_phase'] = 1; // required
$this_prototype_data['battle_round'] = 1; // required
$this_prototype_data['this_player_id'] = 1; // required
$this_prototype_data['this_player_token'] = 'player'; // required
$this_prototype_data['this_player_robots'] = array(); // required

// Collect or define the current map token we'll be loading from
$request_world_token = isset($_REQUEST['world']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['world']) ? trim($_REQUEST['world']) : '';
if (empty($request_world_token) && !empty($WORLD_SESSION['last_world_token'])){ $request_world_token = $WORLD_SESSION['last_world_token']; }
if (!empty($request_world_token) && !empty($WORLD_SESSION['last_world_token']) && $request_world_token !== $WORLD_SESSION['last_world_token']){ unset($WORLD_SESSION['last_world_position']); }
if (!empty($request_world_token) && in_array($request_world_token, $allowed_world_tokens)){
    $this_prototype_data['this_current_world'] = $request_world_token;
}
if (empty($this_prototype_data['this_current_world'])){ $this_prototype_data['this_current_world'] = $default_world_token; }
$WORLD_SESSION['last_world_token'] = $this_prototype_data['this_current_world'];

// Collect or define the current map position we'll be spawning into
$request_world_position = isset($_REQUEST['position']) && preg_match('/^([-0-9]+)$/i', $_REQUEST['position']) ? trim($_REQUEST['position']) : '';
if (empty($request_world_position) && !empty($WORLD_SESSION['last_world_position'])){ $request_world_position = $WORLD_SESSION['last_world_position']; }
if (!empty($request_world_position)){ $this_prototype_data['this_current_position'] = $request_world_position; }
else { $this_prototype_data['this_current_position'] = $default_world_position; }
$WORLD_SESSION['last_world_position'] = $this_prototype_data['this_current_position'];

// Collect of define the current player character we'll be using
$request_player_token = isset($_REQUEST['player']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['player']) ? trim($_REQUEST['player']) : '';
if (empty($request_player_token) && !empty($WORLD_SESSION['last_player_token'])){ $request_player_token = $WORLD_SESSION['last_player_token']; }
if (!empty($request_player_token) && in_array($request_player_token, $allowed_player_tokens)){
    $this_prototype_data['this_player_token'] = $request_player_token;
    $allowed_player_robots = mmrpg_prototype_robots_unlocked($request_player_token, true);
    $max_player_robots = MMRPG_WORLD_DEFAULT_TEAMSIZE; // TODO: make this dynamic based on limit hearts
    if (!empty($allowed_player_robots)){
        $request_player_robots = array();
        foreach ($allowed_player_robots AS $robot_token){
            if (empty($mmrpg_index_robots[$robot_token])){ continue; }
            $robot_info = $mmrpg_index_robots[$robot_token];
            $robot_id = $robot_info['robot_id'];
            $robot_string = $robot_id . '_' . $robot_token;
            $request_player_robots[] = $robot_string;
        }
        $this_prototype_data['this_player_robots'] = array_slice($request_player_robots, 0, $max_player_robots);
    }
}
if (empty($this_prototype_data['this_player_token'])){ $this_prototype_data['this_player_token'] = $default_player_token; }
$WORLD_SESSION['last_player_token'] = $this_prototype_data['this_player_token'];

// Load map data from the appropriate map file
$map_token = $this_prototype_data['this_current_world'];
$map_data_parsed = loadMapData($map_token);
//error_log('$map_data_parsed = '.print_r($map_data_parsed, true));

// Collect the map's field token and mecha encounters
$map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
$map_mecha_support = !empty($map_data_parsed['mechas']) ? $map_data_parsed['mechas'] : array();

// Collect the overall size variables for this map
$map_base_size = $map_data_parsed['size'];
if (count($map_base_size) === 4){ list($map_col_size, $map_row_size, $map_tile_width, $map_tile_height) = $map_base_size; }
elseif (count($map_base_size) === 3){ list($map_col_size, $map_row_size, $map_tile_width) = $map_base_size; }
elseif (count($map_base_size) === 2){ list($map_col_size, $map_tile_width) = $map_base_size; }
elseif (count($map_base_size) === 1){ list($map_col_size) = $map_base_size; }
if (!isset($map_col_size)){ $map_col_size = MMRPG_WORLD_DEFAULT_MAPSIZE; }
if (!isset($map_row_size)){ $map_row_size = $map_col_size; }
if (!isset($map_tile_width)){ $map_tile_width = MMRPG_WORLD_DEFAULT_TILESIZE; }
if (!isset($map_tile_height)){ $map_tile_height = $map_tile_width; }
$map_pixel_width = $map_col_size * $map_tile_width;
$map_pixel_height = $map_row_size * $map_tile_height;

// Generate the map spawn points (source and destination)
$map_spawn_pos = !empty($WORLD_SESSION[$map_token.'_spawn_pos']) ? $WORLD_SESSION[$map_token.'_spawn_pos'] : '';
$map_exit_pos = !empty($WORLD_SESSION[$map_token.'_exit_pos']) ? $WORLD_SESSION[$map_token.'_exit_pos'] : '';
if (empty($map_spawn_pos)){
    $map_spawn_pos = '1-1';
    if (!empty($map_data_parsed['portals']['spawn'])){
        $spawn = $map_data_parsed['portals']['spawn'];
        $map_spawn_pos = $spawn[0].'-'.$spawn[1];
    }
}
if (empty($map_exit_pos)){
    $map_exit_pos = ($map_col_size + 1).'-'.($map_row_size + 1);
    if (!empty($map_data_parsed['portals']['exit'])){
        $exit = $map_data_parsed['portals']['exit'];
        $map_exit_pos = $exit[0].'-'.$exit[1];
    }
}
$WORLD_SESSION[$map_token.'_spawn_pos'] = $map_spawn_pos;
$WORLD_SESSION[$map_token.'_exit_pos'] = $map_exit_pos;

// If the world position has not been set yet, we can use the spawn position for it
if (empty($this_prototype_data['this_current_position'])){ $this_prototype_data['this_current_position'] = $map_spawn_pos; }

// Generate the random encounters for this map location if not already spawned
//$max_random_encounters = 20;
$allowed_random_encounters = $map_mecha_support;
$available_encounter_cells = getMapEncounterCells($map_data_parsed);
$max_random_encounters = ceil(count($available_encounter_cells) * 0.25);
$map_random_encounters = !empty($WORLD_SESSION[$map_token.'_random_encounters']) ? $WORLD_SESSION[$map_token.'_random_encounters'] : array();
if (empty($map_random_encounters)){
    for ($i = 0; $i < $max_random_encounters; $i++){
        $robot = $allowed_random_encounters[mt_rand(0, count($allowed_random_encounters) - 1)];
        $randpos = $get_randpos($available_encounter_cells);
        $robot_info = $mmrpg_index_robots[$robot];
        $robot_level = mt_rand(1, 10);
        $battle_token = 'world-battle_'.$map_token.'_debug-'.($i + 1);
        $battle_name = $robot_info['robot_name'].' (Lv. '.$robot_level.')';
        $map_random_encounters[] = array('robot', $robot, '', $randpos, $battle_token, $battle_name);
        $battle_omega = rpg_mission::generate_mission($this_prototype_data, $battle_token, array(
            'token' => $battle_token,
            'name' => ('Debug Battle '.($i + 1).'/'.$max_random_encounters),
            'description' => 'This is a debug battle.  It is '.($i + 1).'/'.$max_random_encounters.' in a series of debug battles.',
            'turns' => 1234,
            'zenny' => 5678,
            'field' => $map_field_token,
            'target' => array('robots' => array(array('token' => $robot, 'level' => $robot_level))),
            'flags' => array('world_battle' => true, 'remove_on_complete' => true),
            ), true);
        }
}
$WORLD_SESSION[$map_token.'_random_encounters'] = $map_random_encounters;

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

            // Generate overall the map styles and markup
            $map_tilesize_default = MMRPG_WORLD_DEFAULT_TILESIZE;
            $map_tilesize_offset = array(0, 0);
            if ($map_tile_height > $map_tilesize_default){ $map_tilesize_offset[0] = floor(($map_tile_height - $map_tilesize_default) / 2); }
            if ($map_tile_width > $map_tilesize_default){ $map_tilesize_offset[1] = floor(($map_tile_width - $map_tilesize_default) / 2); }
            $map_size_styles = 'width: '.$map_pixel_width.'px; height: '.$map_pixel_height.'px; ';
            $map_offset_styles = 'top: 0px; left: 0px; ';
            $map_base_styles = trim($map_size_styles.$map_offset_styles);
            $map_base_attrs = 'data-cols="'.$map_col_size.'" data-rows="'.$map_row_size.'"';
            $map_base_attrs .= ' data-size="'.$map_col_size.' x '.$map_row_size.' x '. $map_tile_width.' x '.$map_tile_height.'"';
            ?>
            <div id="map" data-token="<?= $map_token ?>" style="<?= $map_base_styles ?>" <?= $map_base_attrs ?>>
                <?

                // GLOBAL MAP DATA
                $data = array();
                $data['map_token'] = $map_data_parsed['token'];
                $data['map_image'] = 'images/maps/'.(!empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : 'undefined.png');
                $data['map_size'] = array($map_col_size, $map_row_size);
                $data['tile_size'] = array($map_tile_width, $map_tile_height);
                $data['tiles_index'] = $map_data_parsed['tiles'];
                $data['sprites_index'] = $map_data_parsed['sprites'];
                $data['portals_index'] = $map_data_parsed['portals'];
                $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                echo('<script data-json="mapData" type="application/json">'.$data_json.'</script>'.PHP_EOL);

                // TERRAIN TILES
                foreach ($map_data_parsed['layers'] AS $map_layer_key => $map_layer_data){
                    $map_layer_styles = $map_base_styles; // TODO: support custom styles per layer maybe?
                    $map_layer_attrs = $map_base_attrs; // TODO: support custom attributes per layer maybe?
                    ?>
                    <div class="layer layer-1 tiles terrain has-canvas" data-layer="terrain" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                        <?
                        $data = array();
                        $data['canvas_tiles'] = array();
                        for ($row = 1; $row <= $map_row_size; $row++){
                            $row_tiles = $map_layer_data[$row - 1];
                            $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
                            for ($col = 1; $col <= $map_col_size; $col++){
                                $pos = $col.'-'.$row;
                                $key = isset($row_tiles[$col - 1]) ? $row_tiles[$col - 1] : '';
                                $data['canvas_tiles'][$pos] = $key;
                            }
                        }
                        $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                        echo('<canvas width="'.$map_pixel_width.'" height="'.$map_pixel_height.'"></canvas>'.PHP_EOL);
                        echo('<script data-json="tileData" type="application/json">'.$data_json.'</script>'.PHP_EOL);
                        ?>
                    </div>
                    <?
                }

                // EVENT TILES
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-2 tiles events portals" data-layer="portals" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?

                    // If there are any portals defined, make sure we look through and display them
                    if (!empty($map_data_parsed['portals'])){
                        $portal_sprites = $map_data_parsed['portals'];
                        foreach ($portal_sprites AS $portal_name => $portal_data){
                            if (empty($portal_data) || !is_array($portal_data) || count($portal_data) < 2){ continue; }
                            list($x, $y) = $portal_data;
                            $pos = $x.'-'.$y;
                            list($col, $row) = explode('-', $pos);
                            $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                            $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                            $label = preg_match('/^goto__/i', $portal_name) ? strtoupper(preg_replace('/^goto__/i', '', $portal_name)) : ('World '.ucfirst($portal_name));
                            $attrs = 'data-portal="'.$portal_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                            $style = 'top: '.$top.'px; left: '.$left.'px;';
                            echo('<span class="sprite tile portal pulse" '.$attrs.' style="'.$style.'"></span>'.PHP_EOL);
                        }
                    }

                    ?>
                </div>
                <?

                // EVENT OBJECTS (UNDER)
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-3 objects events battles" data-layer="battles" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?
                    $battle_symbols = array();
                    $battle_index = array();
                    foreach ($map_random_encounters as $encounter){
                        $kind = $encounter[0];
                        $xkind = $get_xkind($kind);
                        $token = $encounter[1];
                        $alt = $encounter[2];
                        $position = $encounter[3];
                        $battle = $encounter[4];
                        $name = $encounter[5];
                        if (!rpg_battle::has_index_info($battle)){ continue; }
                        list($col, $row) = explode('-', $position);
                        $maxcols = $map_col_size;
                        $maxrows = $map_row_size;
                        $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                        $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                        $zindex = ($maxrows + 1) - $row;
                        $class = 'battle bounce';
                        $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$zindex.';';
                        $attrs = 'data-battle="'.$battle.'" data-pos="'.$position.'" data-col="'.$col.'" data-row="'.$row.'"';
                        $attrs .= 'data-label="'.$name.'"';
                        $markup = $get_sprite($kind, $token, $alt, 'right', $class, $style, $attrs);
                        echo($markup);
                        $battle_symbols[$position] = $battle;
                        $battle_index[$battle] = array(
                            'kind' => $kind,
                            'token' => $token,
                            'alt' => $alt,
                            'col' => $col,
                            'row' => $row,
                            'pos' => $position,
                            );
                        }
                    $battle_symbols_json = json_encode($battle_symbols, JSON_NUMERIC_CHECK);
                    $battle_index_json = json_encode($battle_index, JSON_NUMERIC_CHECK);
                    echo('<script data-json="battleSymbols" type="application/json">'.$battle_symbols_json.'</script>'.PHP_EOL);
                    echo('<script data-json="battleIndex" type="application/json">'.$battle_index_json.'</script>'.PHP_EOL);

                    ?>
                </div>
                <?

                // CHARACTER OBJECTS
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-4 objects characters team" data-layer="team" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?
                    // Collect the current team members from the prototype data
                    $team = array();
                    $team_player_token = !empty($this_prototype_data['this_player_token']) ? $this_prototype_data['this_player_token'] : 'player';
                    $team_player_robots = !empty($this_prototype_data['this_player_robots']) ? $this_prototype_data['this_player_robots'] : array();
                    if (!empty($team_player_token) && $team_player_token !== 'player'){
                        $player_token = $team_player_token;
                        $player = array('player', $player_token);
                        $team[] = $player;
                    }
                    if (!empty($team_player_robots) && is_array($team_player_robots)){
                        foreach ($team_player_robots AS $robot_string){
                            list($robot_id, $robot_token) = explode('_', $robot_string, 2);
                            $robot = array('robot', $robot_token);
                            $robot_settings = rpg_game::robot_settings($team_player_token, $robot_token);
                            $robot_image = !empty($robot_settings['robot_image']) ? $robot_settings['robot_image'] : '';
                            if (!empty($robot_image) && $robot_image !== $robot_token){ $robot[] = explode('_', $robot_image, 2)[1]; }
                            $team[] = $robot;
                        }
                    }
                    // Generate the markup for the cursor and team sprites
                    $obj = 'cursor';
                    $sprite = 'images/robots/pointan/sprite_right_40x40.png'; // TODO: surely this isn't how we're going to leave this...
                    $pos = $this_prototype_data['this_current_position'];
                    list($col, $row) = explode('-', $pos);
                    $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                    $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                    echo('<span class="sprite '.$obj.' bounce" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"><span class="sprite sprite_40x40" style="background-image: url('.$sprite.');"></span></span>'.PHP_EOL);
                    if (!empty($team)){
                        foreach ($team as $key => $sprite){
                            $kind = $sprite[0];
                            $token = $sprite[1];
                            $alt = isset($sprite[2]) ? $sprite[2] : '';
                            $dir = 'right';
                            $class = 'team bounce';
                            $styles = '';
                            $attrs = 'data-key="'.$key.'"';
                            $markup = $get_sprite($kind, $token, $alt, $dir, $class, $styles, $attrs);
                            echo($markup.PHP_EOL);

                        }
                    }
                    ?>
                </div>
                <?

                // EVENT OBJECTS (OVER)
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-5 objects zoom" data-layer="zoom" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>> <!-- dynamic layer for temporarily zoomed-sprites --> </div>
                <?

                // END OF LAYERS
                ?>
                <div id="click-overlay" class="active"><div class="wrapper"></div></div>
                <div id="action-dropdown" class="active"><div class="wrapper"></div></div>
            </div>
            <?
            // DEBUG DEBUG DEBUG
            echo('<!-- $map_data_parsed = '.print_r($map_data_parsed, true).' -->');
            ?>
            <div id="home-button" class="chrome"><a class="wrapper"><i class="fa fas fa-home"></i></a></div>
            <div id="reset-button" class="chrome"><a class="wrapper"><i class="fa fas fa-trash"></i></a></div>
            <div id="position-display" class="chrome"><div class="wrapper">&hellip;</div></div>
            <div id="player-switcher" class="chrome"><div class="wrapper"><?
                $sprite = $get_sprite('robot', 'pointan', '', 'right', 'option');
                $active = ($this_prototype_data['this_player_token'] === 'player') ? ' active' : '';
                echo('<a class="option'.$active.'" data-player="player">'.$sprite.'</a>');
                foreach ($allowed_player_tokens AS $pkey => $ptoken){
                    if (!empty($mmrpg_index_players[$ptoken])){ $pinfo = $mmrpg_index_players[$ptoken]; } else { continue; }
                    $sprite = $get_sprite('player', $ptoken, '', 'right', 'option', '');
                    $active = ($ptoken === $this_prototype_data['this_player_token']) ? ' active' : '';
                    echo('<a class="option'.$active.'" data-player="'.$ptoken.'">'.$sprite.'</a>');
                } ?></div></div>
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

// Create the document ready events
$(document).ready(function(){

    // Immediately clear the selected player setting as it shouldn't be this

    // Make sure the music button is in the appropriate place
    top.mmrpg_music_context('world');

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