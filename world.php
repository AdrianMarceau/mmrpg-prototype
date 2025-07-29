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

// Collect the game session token in case we need it later
$session_token = rpg_game::session_token();

//unset($_SESSION[$session_token]['battle_history']);
//unset($_SESSION[$session_token]['values']['battle_history']);

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
    if (!empty($worldData['lastPlayer']) && in_array($worldData['lastPlayer'], $allowed_player_tokens)){
        $lastPlayer = $worldData['lastPlayer'];
        $WORLD_SESSION['last_player_token'] = $lastPlayer;
        if (!empty($worldData['lastWorld']) && in_array($worldData['lastWorld'], $allowed_world_tokens)){
            $last_world_token_key = 'last_'.$lastPlayer.'_world_token';
            $WORLD_SESSION[$last_world_token_key] = $worldData['lastWorld'];
        }
        if (!empty($worldData['lastPosition']) && preg_match('/^([-0-9]+)$/i', $worldData['lastPosition'])){
            $last_world_position_key = 'last_'.$lastPlayer.'_world_position';
            $WORLD_SESSION[$last_world_position_key] = $worldData['lastPosition'];
        }
        if (!empty($worldData['lastDirection']) && preg_match('/^([-a-z0-9]+)$/i', $worldData['lastDirection'])){
            $last_world_direction_key = 'last_'.$lastPlayer.'_world_direction';
            $WORLD_SESSION[$last_world_direction_key] = $worldData['lastDirection'];
        }
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
    $available = array_values(array_diff($available_encounter_cells, $used));
    if (empty($available)){ return false; }
    $pos = $available[mt_rand(0, count($available) - 1)];
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
    $map_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)(,[-_a-z0-9,]+)?\)$/i'; // syntax: name(x,y[,flag1,flag2,etc.]) ie. spawn(4,4) or spawn(4,4,other-area-2) => name:spawn, x:4, y:4
    $map_listval_custval_regex = '/^([.a-z0-9-_]+)\(([,a-z0-9-_]+)\)/i'; // syntax: name(token1,token2,token3) ie. spawn(token1,token2,token3) => name:spawn, tokens:token1,token2,token3
    //$map_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntax: name(x,y) ie. spawn(4,4) => name:spawn, x:4, y:4
    static $map_custval_parser;
    if (!$map_custval_parser){
        $map_custval_parser = function($raw_tiles, $include_keys = false) use ($map_tiles_custval_regex, $map_other_custval_regex, $map_listval_custval_regex){
            if (empty($raw_tiles)){ return array(); }
            $parsed_keys = array();
            $parsed_tiles = array();
            foreach ($raw_tiles AS $line){
                $line = trim(str_replace(' ', '', $line));
                if (empty($line)){ continue; }
                $is_tile_custval = preg_match($map_tiles_custval_regex, $line);
                $is_other_custval = preg_match($map_other_custval_regex, $line);
                $is_listval_custval = preg_match($map_listval_custval_regex, $line);
                if (!$is_tile_custval && !$is_other_custval && !$is_listval_custval){ continue; }
                if ($is_tile_custval){
                    $exploded = explode('/', preg_replace($map_tiles_custval_regex, '$1/$2/$3/$4', $line), 4);
                    //error_log('tile $exploded ='.print_r($exploded, true));
                    list($name, $k, $x, $y) = $exploded;
                    $parsed_tiles[$name] = array($x, $y);
                    $parsed_keys[intval($k)] = $name;
                    continue;
                    }
                if ($is_other_custval){
                    $exploded = explode('/', preg_replace($map_other_custval_regex, '$1/$2/$3/$4', $line), 4);
                    //error_log('other $exploded ='.print_r($exploded, true));
                    list($name, $x, $y) = $exploded;
                    $parsed_tiles[$name] = array($x, $y);
                    if (!empty($exploded[3])){ $parsed_tiles[$name] = array_merge($parsed_tiles[$name], explode(',', trim($exploded[3], ','))); }
                    continue;
                    }
                if ($is_listval_custval){
                    $exploded = explode('/', preg_replace($map_listval_custval_regex, '$1/$2', $line), 2);
                    //error_log('list $exploded ='.print_r($exploded, true));
                    list($name, $tokens) = $exploded;
                    $tokens = explode(',', $tokens);
                    if (empty($tokens) || count($tokens) < 1){ continue; }
                    foreach ($tokens AS $token){ if (empty($token)){ continue; } $parsed_tiles[$name][] = trim($token, ','); }
                    continue;
                    }
                }
            if ($include_keys){ $parsed_tiles['keys'] = $parsed_keys; }
            return $parsed_tiles;
            };
        }
    //error_log('raw $map_data_vars(before) = '.print_r($map_data_vars, true));
    $map_data_vars['token'] = isset($map_data_vars['token']) ? $map_data_vars['token'] : '';
    $map_data_vars['name'] = isset($map_data_vars['name']) ? $map_data_vars['name'] : '';
    $map_data_vars['size'] = isset($map_data_vars['size']) ? $map_data_vars['size'] : '';
    $map_data_vars['sheet'] = isset($map_data_vars['sheet']) ? $map_data_vars['sheet'] : '';
    $map_data_vars['field'] = isset($map_data_vars['field']) ? $map_data_vars['field'] : '';
    $map_data_vars['mechas'] = isset($map_data_vars['mechas']) ? $map_data_vars['mechas'] : array();
    $map_data_vars['habitats'] = isset($map_data_vars['habitats']) ? $map_data_vars['habitats'] : array();
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
    if (empty($map_data_vars['habitats'])){ $map_data_vars['habitats'] = ''; }
    if (empty($map_data_vars['tiles'])){ $map_data_vars['tiles'][] = ''; }
    if (empty($map_data_vars['sprites'])){ $map_data_vars['sprites'][] = ''; }
    if (empty($map_data_vars['portals'])){ $map_data_vars['portals'][] = ''; }
    $map_data_vars['tiles'] = $map_custval_parser($map_data_vars['tiles'], true);
    $map_data_vars['sprites'] = $map_custval_parser($map_data_vars['sprites']);
    $map_data_vars['portals'] = $map_custval_parser($map_data_vars['portals']);
    $map_data_vars['habitats'] = $map_custval_parser($map_data_vars['habitats']);
    // Add collected data to the parsed map data
    $map_data_parsed = array();
    $map_data_parsed['token'] = $map_data_vars['token']; unset($map_data_vars['token']);
    $map_data_parsed['name'] = $map_data_vars['name']; unset($map_data_vars['name']);
    $map_data_parsed['size'] = $map_data_vars['size']; unset($map_data_vars['size']);
    $map_data_parsed['sheet'] = $map_data_vars['sheet']; unset($map_data_vars['sheet']);
    $map_data_parsed['field'] = $map_data_vars['field']; unset($map_data_vars['field']);
    $map_data_parsed['mechas'] = $map_data_vars['mechas']; unset($map_data_vars['mechas']);
    $map_data_parsed['habitats'] = $map_data_vars['habitats']; unset($map_data_vars['habitats']);
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
            $available_cells[$pos] = true;
        }
    }
    //error_log('$map_col_size = '.print_r($map_col_size, true));
    //error_log('$map_row_size = '.print_r($map_row_size, true));
    //error_log('$available_cells('.count($available_cells).') = '.print_r($available_cells, true));
    // Now let's loop through portals and remove spaces that have portals on them
    if (!empty($map_data['portals']) && is_array($map_data['portals'])){
        foreach ($map_data['portals'] AS $portal_name => $portal_data){
            if (empty($portal_data) || !is_array($portal_data) || count($portal_data) < 2){ continue; }
            list($x, $y) = $portal_data;
            $pos = $x.'-'.$y;
            //error_log('-> removing portal position "'.$pos.'" from available cells');
            unset($available_cells[$pos]);
        }
    }
    // Then we through all the tiles and remove any that are unwalkable "void" type
    $by_terrain = array();
    if (!empty($map_data['tiles']) && !empty($map_data['tiles']['keys']) && !empty($map_data['layers'])){
        $tilesIndex = $map_data['tiles']['keys'];
        $tileLayers = $map_data['layers'];
        foreach ($tileLayers AS $layer_key => $layer_tiles){
            foreach ($layer_tiles AS $row_key => $row_tiles){
                $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
                $row_tiles = array_map('intval', $row_tiles);
                foreach ($row_tiles AS $col_key => $tile_key){
                    $pos = ($col_key + 1).'-'.($row_key + 1);
                    if (!isset($available_cells[$pos])){ continue; }
                    if (!isset($tilesIndex[$tile_key])){ continue; }
                    $tile_token = $tilesIndex[$tile_key];
                    // If this tile is a "void" type, remove it from the available cells
                    if ($tile_token === 'void' || strstr($tile_token, 'void')){
                        //error_log('-> removing tile position "'.$pos.'" from available cells (tile: '.$tile_token.')');
                        unset($available_cells[$pos]);
                    }
                    // Otherwise we should add it to the appropriate array in the by_terrain list
                    else {
                        list($tile_token_clean) = strstr($tile_token, '-') ? explode('-', $tile_token) : array($tile_token);
                        //error_log('-> adding tile position "'.$pos.'" to by_terrain["'.$tile_token_clean.'"] (tile: '.$tile_token.')');
                        if (!isset($by_terrain[$tile_token_clean])){ $by_terrain[$tile_token_clean] = array(); }
                        $by_terrain[$tile_token_clean][] = $pos;
                        //error_log('-> adding tile position "'.$pos.'" to by_terrain["'.$tile_token.'"]');
                    }
                }
            }
        }
    }
    // Return the available cells as an array of positions
    $available_cells = array_keys($available_cells);
    $total = count($available_cells);
    //error_log('$available_cells('.count($available_cells).') = '.print_r($available_cells, true));
    //error_log('$by_terrain('.count($by_terrain).') = '.print_r($by_terrain, true));
    return array(
        'all' => $available_cells,
        'by_terrain' => $by_terrain,
        'total' => $total
        );
}

// Define the default prototype data fields and values so we don't get errors
$this_prototype_data = array();
$this_prototype_data['this_current_chapter'] = -1; // required
$this_prototype_data['this_current_player'] = ''; // required
$this_prototype_data['this_current_world'] = ''; // required
$this_prototype_data['this_current_position'] = ''; // required
$this_prototype_data['battle_phase'] = 1; // required
$this_prototype_data['battle_round'] = 1; // required

// Collect of define the current player character we'll be using
$request_player_token = isset($_REQUEST['player']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['player']) ? trim($_REQUEST['player']) : '';
if (empty($request_player_token) && !empty($WORLD_SESSION['last_player_token'])){ $request_player_token = $WORLD_SESSION['last_player_token']; }
if (!empty($request_player_token) && in_array($request_player_token, $allowed_player_tokens)){ $this_prototype_data['this_current_player'] = $request_player_token; }
if (empty($this_prototype_data['this_current_player'])){ $this_prototype_data['this_current_player'] = $default_player_token; }
$WORLD_SESSION['last_player_token'] = $this_prototype_data['this_current_player'];

// Now that we have the player, we should collect the player's robots and other data
$this_player_id = 1;
$this_player_token = 'player';
$this_player_info = array();
$this_player_robots = array();
$this_prototype_data['this_player_id'] = $this_player_id; // required
$this_prototype_data['this_player_token'] = $this_player_token; // required
$this_prototype_data['this_player_robots'] = $this_player_robots; // required
if (!empty($this_prototype_data['this_current_player'])){
    $this_player_token = $this_prototype_data['this_current_player'];
    $this_player_info = !empty($mmrpg_index_players[$this_player_token]) ? $mmrpg_index_players[$this_player_token] : array();
    $this_battle_history = !empty($_SESSION[$session_token]['values']['battle_history']) && !empty($_SESSION[$session_token]['values']['battle_history'][$this_player_token]) ? $_SESSION[$session_token]['values']['battle_history'][$this_player_token] : array();
    $this_prototype_data['this_player_id'] = $this_player_info['player_id'];
    $this_prototype_data['this_player_token'] = $this_player_info['player_token'];
    $max_player_robots = MMRPG_WORLD_DEFAULT_TEAMSIZE; // TODO: make this dynamic based on limit hearts
    $allowed_player_robots = mmrpg_prototype_robots_unlocked($this_player_token, true);
    //error_log('$allowed_player_robots = '.print_r($allowed_player_robots, true));
    $current_player_robots = !empty($allowed_player_robots) ? array_slice($allowed_player_robots, 0, $max_player_robots) : array(); // TODO: make this customizable
    //error_log('$current_player_robots = '.print_r($current_player_robots, true));
    $summoned_player_robots = !empty($this_battle_history['robots_summoned']) ? $this_battle_history['robots_summoned'] : array();
    //error_log('$summoned_player_robots = '.print_r($summoned_player_robots, true));
    if (!empty($summoned_player_robots)){
        //error_log('$summoned_player_robots = '.print_r($summoned_player_robots, true));
        usort($current_player_robots, function($a, $b) use ($summoned_player_robots){
            $a_summoned = array_search($a, $summoned_player_robots);
            $b_summoned = array_search($b, $summoned_player_robots);
            if ($a_summoned !== false && $b_summoned !== false){ return $a_summoned - $b_summoned; }
            elseif ($a_summoned !== false){ return 1; } elseif ($b_summoned !== false){ return -1; }
            else { return 0; }
            });
        //error_log('$current_player_robots (sorted) = '.print_r($current_player_robots, true));
    }
    if (!empty($current_player_robots)){
        $this_player_robots = array();
        foreach ($current_player_robots AS $robot_token){
            if (empty($mmrpg_index_robots[$robot_token])){ continue; }
            $robot_info = $mmrpg_index_robots[$robot_token];
            $robot_id = $robot_info['robot_id'];
            $robot_string = $robot_id . '_' . $robot_token;
            $this_player_robots[] = $robot_string;
        }
        $this_prototype_data['this_player_robots'] = $this_player_robots;
    }
} else {
    die('MMRPG World Fatal Error - No player token defined!');
}

// Define the session keys we'll be using to store player-specific world settings
$last_world_token_key = 'last_'.$this_player_token.'_world_token';
$last_world_position_key = 'last_'.$this_player_token.'_world_position';
$last_world_direction_key = 'last_'.$this_player_token.'_world_direction';

// Collect or define the current map token we'll be loading from
$request_world_token = isset($_REQUEST['world']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['world']) ? trim($_REQUEST['world']) : '';
if (empty($request_world_token) && !empty($WORLD_SESSION[$last_world_token_key])){ $request_world_token = $WORLD_SESSION[$last_world_token_key]; }
if (!empty($request_world_token) && !empty($WORLD_SESSION[$last_world_token_key]) && $request_world_token !== $WORLD_SESSION[$last_world_token_key]){ unset($WORLD_SESSION[$last_world_position_key]); }
if (!empty($request_world_token) && in_array($request_world_token, $allowed_world_tokens)){
    $this_prototype_data['this_current_world'] = $request_world_token;
}
if (empty($this_prototype_data['this_current_world'])){ $this_prototype_data['this_current_world'] = $default_world_token; }
$WORLD_SESSION[$last_world_token_key] = $this_prototype_data['this_current_world'];

// Collect or define the current map position we'll be spawning into
$request_world_position = isset($_REQUEST['position']) && preg_match('/^([-0-9]+)$/i', $_REQUEST['position']) ? trim($_REQUEST['position']) : '';
if (empty($request_world_position) && !empty($WORLD_SESSION[$last_world_position_key])){ $request_world_position = $WORLD_SESSION[$last_world_position_key]; }
if (!empty($request_world_position)){ $this_prototype_data['this_current_position'] = $request_world_position; }
else { $this_prototype_data['this_current_position'] = $default_world_position; }
$WORLD_SESSION[$last_world_position_key] = $this_prototype_data['this_current_position'];

// Load map data from the appropriate map file
$map_token = $this_prototype_data['this_current_world'];
$map_data_parsed = loadMapData($map_token);
//error_log('$map_data_parsed = '.print_r($map_data_parsed, true));

// Collect the map's field token and mecha encounters
$map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
$map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
$map_field_background = !empty($map_field_info['field_background']) ? $map_field_info['field_background'] : 'field';
$map_field_foreground = !empty($map_field_info['field_foreground']) ? $map_field_info['field_foreground'] : 'field';
$map_field_music = !empty($map_field_info['field_music']) ? $map_field_info['field_music'] : 'misc/star-force'; // TODO: find a better default for this
$map_mecha_support = !empty($map_data_parsed['mechas']) ? $map_data_parsed['mechas'] : array();
$map_mecha_habitats = !empty($map_data_parsed['habitats']) ? $map_data_parsed['habitats'] : array();
//error_log('$map_field_token = '.print_r($map_field_token, true));
//error_log('$map_field_info = '.print_r($map_field_info, true));
//error_log('$map_field_background = '.print_r($map_field_background, true));
//error_log('$map_field_foreground = '.print_r($map_field_foreground, true));
//error_log('$map_field_music = '.print_r($map_field_music, true));
//error_log('$map_mecha_support = '.print_r($map_mecha_support, true));
//error_log('$map_mecha_habitats = '.print_r($map_mecha_habitats, true));

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
$max_random_encounters = ceil($available_encounter_cells['total'] * 0.25);
$map_random_encounters = !empty($WORLD_SESSION[$map_token.'_random_encounters']) ? $WORLD_SESSION[$map_token.'_random_encounters'] : array();
//error_log('$allowed_random_encounters = '.print_r($allowed_random_encounters, true));
//error_log('$available_encounter_cells = '.print_r($available_encounter_cells, true));
//error_log('$max_random_encounters = '.print_r($max_random_encounters, true));
//error_log('$map_random_encounters = '.print_r($map_random_encounters, true));
if (empty($map_random_encounters)){
    $ratios = array();
    foreach ($allowed_random_encounters AS $key => $robot){
        $ratio = strstr($robot, '(') && strstr($robot, ')') ? explode('(', str_replace(')', '', $robot)) : array($robot, 1);
        $robot = $ratio[0]; $value = intval($ratio[1]);
        $ratios[$robot] = $value;
        }
    $ratios_sum = array_sum($ratios);
    $distributed_encounters = array_map(function($value) use ($ratios_sum, $max_random_encounters){
        return ceil(($value / $ratios_sum) * $max_random_encounters);
        }, $ratios);
    asort($distributed_encounters);
    $options = array_keys($distributed_encounters);
    //echo('<pre>'.PHP_EOL);
    //error_log('$map_data_parsed = '.print_r($map_data_parsed, true).PHP_EOL);
    //error_log('$ratios = '.print_r($ratios, true).PHP_EOL);
    //error_log('$options = '.print_r($options, true).PHP_EOL);
    //error_log('$ratios_sum = '.print_r($ratios_sum, true).PHP_EOL);
    //error_log('$max_random_encounters = '.print_r($max_random_encounters, true).PHP_EOL);
    //error_log('$distributed_encounters = '.print_r($distributed_encounters, true).PHP_EOL);
    $robot = '';
    for ($i = 0; $i < $max_random_encounters; $i++){
        if (empty($options)){ $options = array_keys($distributed_encounters); }
        if (empty($robot)){ $robot = array_shift($options); }
        if (!isset($generated_encounters[$robot])){ $generated_encounters[$robot] = 0; }
        //error_log('-> next robot = "'.$robot.'"'.PHP_EOL);
        $habitats = !empty($map_mecha_habitats[$robot]) ? $map_mecha_habitats[$robot] : '';
        //error_log('-> getting random position for robot "'.$robot.'" (habitats: '.print_r(implode(',', $habitats), true).')');
        $available = array();
        if (!empty($habitats)){
            $by_terrain = $available_encounter_cells['by_terrain'];
            foreach ($by_terrain AS $terrain => $cells){
                if (!in_array($terrain, $habitats)){ continue; }
                $available = array_merge($available, $cells);
                }
            }
        if (empty($available)){ $available = $available_encounter_cells['all']; }
        //error_log('$available = '.print_r($available, true).PHP_EOL);
        //exit();
        $randpos = $get_randpos($available);
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
        $generated_encounters[$robot]++;
        $distributed_encounters[$robot]--;
        if (empty($distributed_encounters[$robot])){ $robot = ''; }
        }
    //error_log'$generated_encounters = '.print_r($generated_encounters, true).PHP_EOL);
    //echo('</pre>'.PHP_EOL);
    //exit();
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
                $data['start_position'] = $this_prototype_data['this_current_position'];
                $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                echo('<script data-json="mapData" type="application/json">'.$data_json.'</script>'.PHP_EOL);

                // BACKGROUND LAYER
                $map_layer_styles = $map_base_styles; // TODO: support custom styles per layer maybe?
                $map_layer_attrs = $map_base_attrs; // TODO: support custom attributes per layer maybe?
                $field_background_image = 'images/fields/'.$map_field_token.'/battle-field_background_base.gif';
                $field_background_styles = 'top: 0; left: 0; background-image: url('.$field_background_image.');';
                ?>
                <div class="layer layer-0 background" data-layer="background" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <span class="sprite field background" style="<?= $field_background_styles ?>"></span>
                </div>
                <?

                // TERRAIN TILES
                foreach ($map_data_parsed['layers'] AS $map_layer_key => $map_layer_data){
                    $map_layer_styles = $map_base_styles; // TODO: support custom styles per layer maybe?
                    $map_layer_attrs = $map_base_attrs; // TODO: support custom attributes per layer maybe?
                    ?>
                    <div class="layer layer-1 tiles terrain has-canvas" data-layer="terrain" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                        <?

                        // Generate the json data for the map tiles and then print them out for the canvas
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
                            $hidden = in_array('hidden', $portal_data) ? true : false;
                            if ($hidden){ continue; }
                            $label = preg_match('/^goto__/i', $portal_name) ? strtoupper(preg_replace('/^goto__/i', '', $portal_name)) : ('World '.ucfirst($portal_name));
                            $attrs = 'data-portal="'.$portal_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                            $classes = 'sprite tile portal'.($portal_name !== 'spawn' ? ' pulse' : '').($hidden ? ' hidden' : '');
                            $style = 'top: '.$top.'px; left: '.$left.'px;';
                            echo('<span class="'.$classes.'" '.$attrs.' style="'.$style.'"></span>'.PHP_EOL);
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
            <div id="back-button" class="chrome chrome-button"><a class="wrapper"><i class="fa fas fa-sign-out"></i></a></div>
            <div id="home-button" class="chrome chrome-button"><a class="wrapper"><i class="fa fas fa-home"></i></a></div>
            <div id="reset-button" class="chrome chrome-button"><a class="wrapper"><i class="fa fas fa-recycle"></i></a></div>
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
            <div id="side-buttons" class="chrome"><div class="wrapper">&hellip;</div></div>
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
_worldConfig.backButtonURL = 'prototype.php';
_worldConfig.homeButtonURL = 'world.php?world=<?= $default_world_token ?>&position=<?= $default_world_position ?>';
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