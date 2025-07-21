<?php

// Include the TOP file
require_once('top.php');

// Define any world session vars that don't exist yet
if (!isset($_SESSION['WORLD'])){ $_SESSION['WORLD'] = array(); }
if (!isset($_SESSION['WORLD_TEMP'])){ $_SESSION['WORLD_TEMP'] = array(); }

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
$get_randpos = function($cols, $rows, $inset = 0, $disallowed = array()){
    static $used;
    if (!$used){ $used = array(); }
    if (!$cols || !$rows){ return false; }
    do {
        $col = mt_rand($inset, ($cols - $inset));
        $row = mt_rand($inset, ($rows - $inset));
        $pos = $col.'-'.$row;
    } while (in_array($pos, $used) || in_array($pos, $disallowed));
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
    //error_log('$info = '.print_r($info, true));
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

// Define some constants for the world map
define('MMRPG_WORLD_DEFAULT_MAPSIZE', 10);
define('MMRPG_WORLD_DEFAULT_TILESIZE', 40);

// Define a function for loading a given map's data from the filesystem
function loadMapData($map_token){
    //error_log('loadMapData() called!');
    static $map_basedir = MMRPG_CONFIG_ROOTDIR.'prototype/worldmaps/';
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
    $map_autocols = strlen($map_data_layers[0][0]); // TODO: first row may not be representative of the entire map
    $map_autorows = count($map_data_layers[0]); // TODO: this may not be representative of the entire map
    $map_tiles_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntaxL name(x,y) ie. void(0.1,-3.5) => void, 0.1, 13,5
    static $map_tiles_parser;
    if (!$map_tiles_parser){
        $map_tiles_parser = function($raw_tiles) use ($map_tiles_regex){
            if (empty($raw_tiles)){ return array(); }
            $parsed_tiles = array();
            foreach ($raw_tiles AS $line){
                $line = trim(str_replace(' ', '', $line));
                if (empty($line) || !preg_match($map_tiles_regex, $line)){ continue; }
                list($name, $x, $y) = explode('/', preg_replace($map_tiles_regex, '$1/$2/$3', $line), 3);
                $parsed_tiles[$name] = array($x, $y);
                }
            return $parsed_tiles;
            };
        }
    $map_data_vars['token'] = isset($map_data_vars['token']) ? $map_data_vars['token'] : '';
    $map_data_vars['name'] = isset($map_data_vars['name']) ? $map_data_vars['name'] : '';
    $map_data_vars['size'] = isset($map_data_vars['size']) ? $map_data_vars['size'] : '';
    $map_data_vars['sheet'] = isset($map_data_vars['sheet']) ? $map_data_vars['sheet'] : '';
    $map_data_vars['field'] = isset($map_data_vars['field']) ? $map_data_vars['field'] : '';
    $map_data_vars['mechas'] = isset($map_data_vars['mechas']) ? $map_data_vars['mechas'] : array();
    $map_data_vars['tiles'] = isset($map_data_vars['tiles']) ? $map_data_vars['tiles'] : array();
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
    $map_data_vars['tiles'] = $map_tiles_parser($map_data_vars['tiles']);
    // Add collected data to the parsed map data
    $map_data_parsed = array();
    $map_data_parsed['token'] = $map_data_vars['token']; unset($map_data_vars['token']);
    $map_data_parsed['name'] = $map_data_vars['name']; unset($map_data_vars['name']);
    $map_data_parsed['size'] = $map_data_vars['size']; unset($map_data_vars['size']);
    $map_data_parsed['sheet'] = $map_data_vars['sheet']; unset($map_data_vars['sheet']);
    $map_data_parsed['field'] = $map_data_vars['field']; unset($map_data_vars['field']);
    $map_data_parsed['mechas'] = $map_data_vars['mechas']; unset($map_data_vars['mechas']);
    $map_data_parsed['tiles'] = $map_data_vars['tiles']; unset($map_data_vars['tiles']);
    $map_data_parsed['tiles']['keys'] = array_keys($map_data_parsed['tiles']);
    $map_data_parsed['layers'] = $map_data_layers;
    if (!empty($map_data_vars)){ $map_data_parsed['vars'] = $map_data_vars; }
    //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
    return $map_data_parsed;
}

// Define or collect the prototype data for the player, their robots, etc.
$this_prototype_data = array();
$this_prototype_data['this_current_chapter'] = -1; // required
$this_prototype_data['this_current_world'] = ''; // required
$this_prototype_data['battle_phase'] = 1; // required
$this_prototype_data['battle_round'] = 1; // required
$this_prototype_data['this_player_id'] = 1; // required
$this_prototype_data['this_player_token'] = 'player'; // required
$this_prototype_data['this_player_robots'] = array(); // required

// Collect or define the current map token we'll be loading from
$default_world_token = 'starter-80x80';
$allowed_world_tokens = array('starter', 'water', 'starter-80x80');
$request_world_token = isset($_REQUEST['world']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['world']) ? trim($_REQUEST['world']) : '';
if (empty($request_world_token) && !empty($WORLD_SESSION['last_world_token'])){ $request_world_token = $WORLD_SESSION['last_world_token']; }
if (!empty($request_world_token) && in_array($request_world_token, $allowed_world_tokens)){
    $this_prototype_data['this_current_world'] = $request_world_token;
}
if (empty($this_prototype_data['this_current_world'])){ $this_prototype_data['this_current_world'] = $default_world_token; }
$WORLD_SESSION['last_world_token'] = $this_prototype_data['this_current_world'];

// Collect of define the current player character we'll be using
$default_player_token = 'player';
$allowed_player_tokens = mmrpg_prototype_players_unlocked(true);
$request_player_token = isset($_REQUEST['player']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['player']) ? trim($_REQUEST['player']) : '';
if (empty($request_player_token) && !empty($WORLD_SESSION['last_player_token'])){ $request_player_token = $WORLD_SESSION['last_player_token']; }
if (!empty($request_player_token) && in_array($request_player_token, $allowed_player_tokens)){
    $this_prototype_data['this_player_token'] = $request_player_token;
    $allowed_player_robots = mmrpg_prototype_robots_unlocked($request_player_token, true);
    $max_player_robots = 8; // TODO: make this dynamic based on limit hearts
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
//$map_tile_size = max($map_tile_width, $map_tile_height);
$map_pixel_width = $map_col_size * $map_tile_width;
$map_pixel_height = $map_row_size * $map_tile_height;
//$map_col_size = isset($map_base_size[0]) ? intval($map_base_size[0]) : 0;
//$map_row_size = isset($map_base_size[1]) ? intval($map_base_size[1]) : 0;
//$map_tile_size = isset($map_base_size[2]) ? intval($map_base_size[2]) : 40;
//list($map_col_size, $map_row_size, $map_tile_size) = $map_base_size;
//$map_pixel_width = $map_col_size * $map_tile_size;
//$map_pixel_height = $map_row_size * $map_tile_size;
//error_log('$map_col_size = '.$map_col_size);
//error_log('$map_row_size = '.$map_row_size);
//error_log('$map_tile_width = '.$map_tile_width);
//error_log('$map_tile_height = '.$map_tile_height);
//error_log('$map_pixel_width = '.$map_pixel_width);
//error_log('$map_pixel_height = '.$map_pixel_height);

// Generate the map spawn points (source and destination)
$map_spawn_inset = 3;
$map_spawn_src_pos = !empty($WORLD_SESSION[$map_token.'_spawn_src']) ? $WORLD_SESSION[$map_token.'_spawn_src'] : '';
if (empty($map_spawn_src_pos)){
    $map_spawn_src_col = mt_rand($map_spawn_inset, floor($map_col_size / 2) - $map_spawn_inset);
    $map_spawn_src_row = mt_rand($map_spawn_inset, floor($map_row_size / 2) - $map_spawn_inset);
    $map_spawn_src_pos = $map_spawn_src_col.'-'.$map_spawn_src_row;
}
$WORLD_SESSION[$map_token.'_spawn_src'] = $map_spawn_src_pos;
$map_spawn_dst_pos = !empty($WORLD_SESSION[$map_token.'_spawn_dst']) ? $WORLD_SESSION[$map_token.'_spawn_dst'] : '';
if (empty($map_spawn_dst_pos)){
    $map_spawn_dst_col = mt_rand(ceil($map_col_size / 2) + $map_spawn_inset, $map_col_size - $map_spawn_inset);
    $map_spawn_dst_row = mt_rand(ceil($map_row_size / 2) + $map_spawn_inset, $map_row_size - $map_spawn_inset);
    $map_spawn_dst_pos = $map_spawn_dst_col.'-'.$map_spawn_dst_row;
}
$WORLD_SESSION[$map_token.'_spawn_dst'] = $map_spawn_dst_pos;

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
            $map_offset = array(0, 0); // TODO: make this dynamic
            $map_offset_x = $map_offset[0] * $map_tile_width;
            $map_offset_y = $map_offset[1] * $map_tile_height;
            $map_tilesize_default = MMRPG_WORLD_DEFAULT_TILESIZE;
            $map_tilesize_offset = array(0, 0);
            if ($map_tile_height > $map_tilesize_default){ $map_tilesize_offset[0] = floor(($map_tile_height - $map_tilesize_default) / 2); }
            if ($map_tile_width > $map_tilesize_default){ $map_tilesize_offset[1] = floor(($map_tile_width - $map_tilesize_default) / 2); }
            $map_size_styles = 'width: '.$map_pixel_width.'px; height: '.$map_pixel_height.'px; ';
            $map_offset_styles = 'top: '. $map_offset_y.'px; left: '.$map_offset_x.'px; ';
            $map_base_styles = trim($map_size_styles.$map_offset_styles);
            $map_base_attrs = 'data-cols="'.$map_col_size.'" data-rows="'.$map_row_size.'"';
            $map_base_attrs .= ' data-size="'.$map_col_size.' x '.$map_row_size.' x '. $map_tile_width.' x '.$map_tile_height.'"';
            ?>
            <div id="map" style="<?= $map_base_styles ?>" <?= $map_base_attrs ?>>
                <?

                // TERRAIN TILES
                foreach ($map_data_parsed['layers'] AS $map_layer_key => $map_layer_data){
                    $map_layer_styles = $map_base_styles; // TODO: support custom styles per layer maybe?
                    $map_layer_attrs = $map_base_attrs; // TODO: support custom attributes per layer maybe?
                    ?>
                    <div class="layer layer-1 tiles terrain has-canvas" data-layer="terrain" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                        <?
                        $data = array();
                        //$data['map_image'] = 'images/maps/overworld-experiment-v2024-tileset.png';
                        $data['map_image'] = 'images/maps/'.(!empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : 'undefined.png');
                        $data['map_size'] = array($map_pixel_width, $map_pixel_height);
                        $data['map_offset'] = array($map_offset_x, $map_offset_y);
                        $data['tile_size'] = array($map_tile_width, $map_tile_height);
                        $data['tile_index'] = $map_data_parsed['tiles'];
                        $data['tile_data'] = array();
                        //error_log('$map layer '.$map_layer_key.' has $data = '.print_r($data, true));
                        for ($row = 1; $row <= $map_row_size; $row++){
                            $row_tiles = $map_layer_data[$row - 1];
                            //error_log('$row_tiles = '.print_r($row_tiles, true));
                            for ($col = 1; $col <= $map_col_size; $col++){
                                $pos = $col.'-'.$row;
                                $key = (int)(substr($row_tiles, ($col - 1), 1));
                                $data['tile_data'][$pos] = $key;
                            }
                        }
                        $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                        echo('<canvas width="'.$map_pixel_width.'" height="'.$map_pixel_height.'"></canvas>');
                        echo('<script data-json="canvasData" type="application/json">'.$data_json.'</script>');
                        ?>
                    </div>
                    <?
                }

                // EVENT TILES
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-2 events tiles" data-layer="spawns" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?

                    $tile = 'src';
                    $pos = $map_spawn_src_pos;
                    list($col, $row) = explode('-', $pos);
                    $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                    $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                    echo('<span class="sprite tile '.$tile.' pulse" data-event="spawn-src" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'" style="top: '.$top.'px; left: '.$left.'px;"></span>');

                    $tile = 'dst';
                    $pos = $map_spawn_dst_pos;
                    list($col, $row) = explode('-', $pos);
                    $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                    $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                    echo('<span class="sprite tile '.$tile.' pulse" data-event="spawn-dst" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'" style="top: '.$top.'px; left: '.$left.'px;"></span>');

                    ?>
                </div>
                <?

                // EVENT OBJECTS
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-3 events objects" data-layer="battles" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?
                    $disallowed_cells = array($map_spawn_src_pos, $map_spawn_dst_pos);
                    $random_encounters = array();
                    $max_random_encounters = 10;
                    $allowed_random_encounters = $map_mecha_support;
                    for ($i = 0; $i < $max_random_encounters; $i++){
                        $robot = $allowed_random_encounters[mt_rand(0, count($allowed_random_encounters) - 1)];
                        $randpos = $get_randpos($map_col_size, $map_row_size, 4, $disallowed_cells);
                        $battle_token = 'some-battle-token-'.($i + 1);
                        $random_encounters[] = array('robot', $robot, '', $randpos, $battle_token);
                        $battle_omega = rpg_mission::generate_mission($this_prototype_data, $battle_token, array(
                            'token' => $battle_token,
                            'name' => ('Debug Battle '.($i + 1).'/'.$max_random_encounters),
                            'description' => 'This is a debug battle.  It is '.($i + 1).'/'.$max_random_encounters.' in a series of debug battles.',
                            'turns' => 1234,
                            'zenny' => 5678,
                            'field' => $map_field_token,
                            'target' => array('robots' => array('token' => $robot)),
                            'flags' => array('world_battle' => true),
                            ), true);
                        //exit('omg $battle_omega = '.print_r($battle_omega, true));
                        }
                    //$random_encounters[] = array('robot', 'met', '', $randpos(), 'some-battle-token-1');
                    //$random_encounters[] = array('robot', 'snapper', '', $randpos(), 'some-battle-token-2');
                    //$random_encounters[] = array('robot', 'batton', '', $randpos(), 'some-battle-token-3');
                    $battle_symbols = array();
                    $battle_index = array();
                    foreach ($random_encounters as $battle){
                        $kind = $battle[0];
                        $xkind = $get_xkind($kind);
                        $token = $battle[1];
                        $alt = $battle[2];
                        $position = $battle[3];
                        $battle = $battle[4];
                        list($col, $row) = explode('-', $position);
                        $maxcols = $map_col_size;
                        $maxrows = $map_row_size;
                        $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                        $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                        $zindex = ($maxrows + 1) - $row;
                        $class = 'battle bounce';
                        $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$zindex.';';
                        $attrs = 'data-battle="'.$battle.'" data-pos="'.$position.'" data-col="'.$col.'" data-row="'.$row.'"';
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
                    //error_log('$random_encounters = '.print_r($random_encounters, true));
                    $battle_symbols_json = json_encode($battle_symbols, JSON_NUMERIC_CHECK);
                    $battle_index_json = json_encode($battle_index, JSON_NUMERIC_CHECK);
                    echo('<script data-json="battleSymbols" type="application/json">'.$battle_symbols_json.'</script>');
                    echo('<script data-json="battleIndex" type="application/json">'.$battle_index_json.'</script>');

                    ?>
                </div>
                <?

                // CHARACTER OBJECTS
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-4 objects characters" data-layer="team" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?
                    // Collect the current team members from the prototype data
                    $team = array();
                    $team_player_token = !empty($this_prototype_data['this_player_token']) ? $this_prototype_data['this_player_token'] : 'player';
                    $team_player_robots = !empty($this_prototype_data['this_player_robots']) ? $this_prototype_data['this_player_robots'] : array();
                    if (!empty($team_player_token) && $team_player_token !== 'player'){
                        //error_log('adding player "'.$team_player_token.'" to team');
                        $player_token = $team_player_token;
                        $player = array('player', $player_token);
                        $team[] = $player;
                    }
                    if (!empty($team_player_robots) && is_array($team_player_robots)){
                        foreach ($team_player_robots AS $robot_string){
                            //error_log('adding robot "'.$robot_token.'" to team');
                            list($robot_id, $robot_token) = explode('_', $robot_string, 2);
                            $robot = array('robot', $robot_token);
                            $robot_settings = rpg_game::robot_settings($team_player_token, $robot_token);
                            $robot_image = !empty($robot_settings['robot_image']) ? $robot_settings['robot_image'] : '';
                            if (!empty($robot_image) && $robot_image !== $robot_token){ $robot[] = explode('_', $robot_image, 2)[1]; }
                            $team[] = $robot;
                        }
                    }
                    //error_log('$team = '.print_r($team, true));
                    // Generate the markup for the cursor and team sprites
                    $obj = 'cursor';
                    //$sprite = 'images/items/empty-shard/icon_right_40x40.png';
                    $sprite = 'images/robots/pointan/sprite_right_40x40.png';
                    $pos = $map_spawn_src_pos;
                    list($col, $row) = explode('-', $pos);
                    $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                    $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                    echo('<span class="sprite '.$obj.' bounce" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"><span class="sprite sprite_40x40" style="background-image: url('.$sprite.');"></span></span>');
                    if (!empty($team)){
                        foreach ($team as $key => $sprite){
                            //error_log('$sprite = '.print_r($sprite, true));
                            $kind = $sprite[0];
                            $token = $sprite[1];
                            $alt = isset($sprite[2]) ? $sprite[2] : '';
                            $dir = 'right';
                            $class = 'team bounce';
                            $styles = '';
                            $attrs = 'data-key="'.$key.'"';
                            $markup = $get_sprite($kind, $token, $alt, $dir, $class, $styles, $attrs);
                            echo($markup);

                        }
                    }
                    ?>
                </div>
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