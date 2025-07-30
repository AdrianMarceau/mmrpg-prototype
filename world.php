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
define('MMRPG_WORLD_DEFAULT_TILESIZE', 80);
define('MMRPG_WORLD_DEFAULT_SPRITESITE', 40);
define('MMRPG_WORLD_DEFAULT_TEAMSIZE', 3); // TODO: make this dependant on limit hearts
define('MMRPG_WORLD_DEFAULT_MOBILITY', 1); // TODO: make this dependant on player skill
define('MMRPG_WORLD_MAPFILE_BASEPATH', 'prototype/worldmaps/');

// Define defaults and allowed values for the prototype world data
//$allowed_world_tokens = array('starter', 'water', 'starter-80x80', 'water-80x80');
$existing_map_files = glob(MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH.'*.map');
$allowed_world_tokens = array_map(function($path){ return preg_replace('/\.map$/i', '', basename($path)); }, $existing_map_files);
//error_log('$existing_map_files = '. print_r($existing_map_files, true));
//error_log('$allowed_world_tokens = '. print_r($allowed_world_tokens, true));
$allowed_player_tokens = mmrpg_prototype_players_unlocked(true);
array_unshift($allowed_player_tokens, 'player'); // always allow the "player" token
$default_world_token = 'debug-area-1'; //'starter-80x80';
$default_player_token = 'player';
$default_world_position = '';

//error_log('$_GET = '. print_r($_GET, true));
//error_log('$_POST = '. print_r($_POST, true));

// If a save action was requested, we should do it here and then return exit
if (!empty($_POST['action']) && $_POST['action'] === 'save'
    && !empty($_POST['world_data']) && is_array($_POST['world_data'])){
    $worldData = $_POST['world_data'];
    if (!empty($worldData['lastPlayer'])
        && in_array($worldData['lastPlayer'], $allowed_player_tokens)){
        $WORLD_SESSION['last_player_token'] = $worldData['lastPlayer'];
        // Collect the last player session data so we can update
        $lastPlayer = $worldData['lastPlayer'];
        if (!isset($WORLD_SESSION['last_player_sessions'][$lastPlayer])){ $WORLD_SESSION['last_player_sessions'][$lastPlayer] = array(); }
        $lastPlayerSession = &$WORLD_SESSION['last_player_sessions'][$lastPlayer];
        // Collect the cursor player session data so we can update too
        $cursorPlayer = 'player';
        if (!isset($WORLD_SESSION['last_player_sessions'][$cursorPlayer])){ $WORLD_SESSION['last_player_sessions'][$cursorPlayer] = array(); }
        $cursorPlayerSession = &$WORLD_SESSION['last_player_sessions'][$cursorPlayer];
        // If last world was provided, save it to the sessions
        if (!empty($worldData['lastWorld']) && in_array($worldData['lastWorld'], $allowed_world_tokens)){
            $last_world_token_key = 'last_world_token';
            $lastPlayerSession[$last_world_token_key] = $worldData['lastWorld'];
            $cursorPlayerSession[$last_world_token_key] = $worldData['lastWorld'];
        }
        // If last position was provided, save it to the sessions
        if (!empty($worldData['lastPosition']) && preg_match('/^([-0-9]+)$/i', $worldData['lastPosition'])){
            $last_world_position_key = 'last_world_position';
            $lastPlayerSession[$last_world_position_key] = $worldData['lastPosition'];
            $cursorPlayerSession[$last_world_position_key] = $worldData['lastPosition'];
        }
        // If last direction was provided, save it to the sessions
        if (!empty($worldData['lastDirection']) && preg_match('/^([-a-z0-9]+)$/i', $worldData['lastDirection'])){
            $last_world_direction_key = 'last_world_direction';
            $lastPlayerSession[$last_world_direction_key] = $worldData['lastDirection'];
            $cursorPlayerSession[$last_world_direction_key] = $worldData['lastDirection'];
        }
        //error_log('World data saved successfully for player "'.$lastPlayer.'" with world "'.$lastPlayerSession[$last_world_token_key].'" and position "'.$lastPlayerSession[$last_world_position_key].'"');
        //error_log('$WORLD_SESSION = '.print_r($WORLD_SESSION, true));
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
    'items' => &$mmrpg_index_items,
    );
rpg_world::load_indexes($mmrpg_indexes);

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
} else {
    die('MMRPG World Fatal Error - No player token defined!');
}

// Define the session keys we'll be using to store player-specific world settings
$last_world_token_key = 'last_world_token';
$last_world_position_key = 'last_world_position';
$last_world_direction_key = 'last_world_direction';
$last_world_robots_key = 'last_world_robots';

// Make sure the appropriate player token is set in the settings array
if (!isset($WORLD_SESSION['last_player_sessions'])){ $WORLD_SESSION['last_player_sessions'] = array(); }
if (!isset($WORLD_SESSION['last_player_sessions'][$this_player_token])){ $WORLD_SESSION['last_player_sessions'][$this_player_token] = array(); }
$WORLD_PLAYER_SESSION = &$WORLD_SESSION['last_player_sessions'][$this_player_token];

// Collect the current player's robots and battle history
$this_prototype_data['this_player_id'] = $this_player_info['player_id'];
$this_prototype_data['this_player_token'] = $this_player_info['player_token'];
$max_player_robots = MMRPG_WORLD_DEFAULT_TEAMSIZE; // TODO: make this dynamic based on limit hearts
$allowed_player_robots = mmrpg_prototype_robots_unlocked($this_player_token, true);
$current_player_robots = !empty($allowed_player_robots) ? array_slice($allowed_player_robots, 0, $max_player_robots) : array(); // TODO: make this customizable
$summoned_player_robots = rpg_world::get_battle_history($this_player_token, 'robots_summoned');
//error_log('$allowed_player_robots = '.print_r($allowed_player_robots, true));
//error_log('$current_player_robots = '.print_r($current_player_robots, true));
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
$WORLD_PLAYER_SESSION[$last_world_robots_key] = implode(',', $this_prototype_data['this_player_robots']);

// Collect or define the current map token we'll be loading from
$request_world_token = isset($_REQUEST['world']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['world']) ? trim($_REQUEST['world']) : '';
if (empty($request_world_token) && !empty($WORLD_PLAYER_SESSION[$last_world_token_key])){ $request_world_token = $WORLD_PLAYER_SESSION[$last_world_token_key]; }
if (!empty($request_world_token) && !empty($WORLD_PLAYER_SESSION[$last_world_token_key]) && $request_world_token !== $WORLD_PLAYER_SESSION[$last_world_token_key]){ unset($WORLD_PLAYER_SESSION[$last_world_position_key]); }
if (!empty($request_world_token) && in_array($request_world_token, $allowed_world_tokens)){
    $this_prototype_data['this_current_world'] = $request_world_token;
}
if (empty($this_prototype_data['this_current_world'])){ $this_prototype_data['this_current_world'] = $default_world_token; }
$WORLD_PLAYER_SESSION[$last_world_token_key] = $this_prototype_data['this_current_world'];

// Collect or define the current map position we'll be spawning into
$request_world_position = isset($_REQUEST['position']) && preg_match('/^([-0-9]+)$/i', $_REQUEST['position']) ? trim($_REQUEST['position']) : '';
if (empty($request_world_position) && !empty($WORLD_PLAYER_SESSION[$last_world_position_key])){ $request_world_position = $WORLD_PLAYER_SESSION[$last_world_position_key]; }
if (!empty($request_world_position)){ $this_prototype_data['this_current_position'] = $request_world_position; }
else { $this_prototype_data['this_current_position'] = $default_world_position; }
$WORLD_PLAYER_SESSION[$last_world_position_key] = $this_prototype_data['this_current_position'];

// Load map data from the appropriate map file
$map_token = $this_prototype_data['this_current_world'];
$map_data_parsed = rpg_world::load_map_data($map_token);
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
$available_encounter_cells = rpg_world::get_map_encounter_cells($map_data_parsed);
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
        $randpos = rpg_world::get_rand_pos($available);
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
            $map_spritesize_default = MMRPG_WORLD_DEFAULT_SPRITESITE;
            $map_spritesize_offset = array(0, 0);
            if ($map_tile_height > $map_spritesize_default){ $map_spritesize_offset[0] = floor(($map_tile_height - $map_spritesize_default) / 2); }
            if ($map_tile_width > $map_spritesize_default){ $map_spritesize_offset[1] = floor(($map_tile_width - $map_spritesize_default) / 2); }
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
                    $portal_symbols = array();
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
                            $locked = in_array('locked', $portal_data) ? true : false;
                            if ($hidden){ continue; }
                            $label = preg_match('/^goto__/i', $portal_name) ? strtoupper(preg_replace('/^goto__/i', '', $portal_name)) : ('World '.ucfirst($portal_name));
                            $attrs = 'data-portal="'.$portal_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                            $classes = 'sprite tile portal'.($portal_name !== 'spawn' && !$hidden && !$locked  ? ' pulse' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                            $style = 'top: '.$top.'px; left: '.$left.'px;';
                            $portal_symbols[$pos] = $portal_name;
                            echo('<span class="'.$classes.'" '.$attrs.' style="'.$style.'"></span>'.PHP_EOL);
                        }
                    }
                    $portal_symbols_json = json_encode($portal_symbols, JSON_NUMERIC_CHECK);
                    echo('<script data-json="portalSymbols" type="application/json">'.$portal_symbols_json.'</script>'.PHP_EOL);

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
                    $battles_index = array();
                    foreach ($map_random_encounters as $encounter){
                        $kind = $encounter[0];
                        $xkind = rpg_world::get_xkind($kind);
                        $token = $encounter[1];
                        $alt = $encounter[2];
                        $position = $encounter[3];
                        $battle = $encounter[4];
                        $name = $encounter[5];
                        if (!rpg_battle::has_index_info($battle)){ continue; }
                        list($col, $row) = explode('-', $position);
                        $maxcols = $map_col_size;
                        $maxrows = $map_row_size;
                        $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                        $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                        $zindex = ($maxrows + 1) - $row;
                        $class = 'battle bounce';
                        $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$zindex.';';
                        $attrs = 'data-battle="'.$battle.'" data-pos="'.$position.'" data-col="'.$col.'" data-row="'.$row.'"';
                        $attrs .= 'data-label="'.$name.'"';
                        $markup = rpg_world::get_sprite($kind, $token, $alt, 'right', $class, $style, $attrs);
                        echo($markup);
                        $battle_symbols[$position] = $battle;
                        $battles_index[$battle] = array(
                            'kind' => $kind,
                            'token' => $token,
                            'alt' => $alt,
                            'col' => $col,
                            'row' => $row,
                            'pos' => $position,
                            );
                        }
                    $battle_symbols_json = json_encode($battle_symbols, JSON_NUMERIC_CHECK);
                    $battles_index_json = json_encode($battles_index, JSON_NUMERIC_CHECK);
                    echo('<script data-json="battleSymbols" type="application/json">'.$battle_symbols_json.'</script>'.PHP_EOL);
                    echo('<script data-json="battlesIndex" type="application/json">'.$battles_index_json.'</script>'.PHP_EOL);

                    ?>
                </div>
                <?

                // CHARACTER OBJECTS (TEAM)
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-4 objects characters team" data-layer="team" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?

                    // Quick function for generation the team sprites for a given player
                    $get_team_sprites = function($team_sprites, $target_position = '1-1', $team_class = 'team')
                        use ($map_tile_height, $map_tile_width, $map_spritesize_offset){
                        $sprites = array();
                        list($col, $row) = explode('-', $target_position);
                        $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                        $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                        foreach ($team_sprites as $key => $sprite){
                            $kind = $sprite[0];
                            $token = $sprite[1];
                            $alt = isset($sprite[2]) ? $sprite[2] : '';
                            $dir = 'right';
                            if ($key > 0){ $top -= 2; $left -= 4; }
                            $class = $team_class; //'team bounce';
                            $styles = 'top: '.$top.'px; left: '.$left.'px; ';
                            $attrs = 'data-key="'.$key.'"';
                            $markup = rpg_world::get_sprite($kind, $token, $alt, $dir, $class, $styles, $attrs);
                            if (!empty($markup)){ $sprites[] = $markup; }
                            }
                        return implode(PHP_EOL, $sprites);
                        };

                    // Collect the current team members from the prototype data
                    $team_position = $this_prototype_data['this_current_position'];
                    $team_sprites = array();
                    $team_player_token = !empty($this_prototype_data['this_player_token']) ? $this_prototype_data['this_player_token'] : 'player';
                    $team_player_robots = !empty($this_prototype_data['this_player_robots']) ? $this_prototype_data['this_player_robots'] : array();
                    if (!empty($team_player_token) && $team_player_token !== 'player'){
                        $player_token = $team_player_token;
                        $player = array('player', $player_token);
                        $team_sprites[] = $player;
                    }
                    if (!empty($team_player_robots) && is_array($team_player_robots)){
                        foreach ($team_player_robots AS $robot_string){
                            list($robot_id, $robot_token) = explode('_', $robot_string, 2);
                            $robot = array('robot', $robot_token);
                            $robot_settings = rpg_game::robot_settings($team_player_token, $robot_token);
                            $robot_image = !empty($robot_settings['robot_image']) ? $robot_settings['robot_image'] : '';
                            if (!empty($robot_image) && $robot_image !== $robot_token){ $robot[] = explode('_', $robot_image, 2)[1]; }
                            $team_sprites[] = $robot;
                        }
                    }

                    // Generate the markup for the cursor sprites
                    $obj = 'cursor';
                    $sprite = 'images/robots/pointan/sprite_right_40x40.png'; // TODO: surely this isn't how we're going to leave this...
                    $pos = $team_position;
                    list($col, $row) = explode('-', $pos);
                    $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                    $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                    echo('<span class="sprite '.$obj.' bounce" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"><span class="sprite sprite_40x40" style="background-image: url('.$sprite.');"></span></span>'.PHP_EOL);

                    // Generate the markup for the team sprites if any are defined
                    echo($get_team_sprites($team_sprites, $team_position, 'team bounce'));

                    // Loop through the other allowed players to see if any are also on this map
                    $rival_symbols = array();
                    foreach ($allowed_player_tokens AS $pkey => $ptoken){
                        if ($ptoken === 'player'){ continue; } // skip the default player
                        if ($ptoken === $team_player_token){ continue; } // skip the current player
                        if (empty($mmrpg_index_players[$ptoken])){ continue; } // skip if not a valid player
                        if (empty($WORLD_SESSION['last_player_sessions'][$ptoken])){ continue; } // skip if no player session
                        //error_log('Checking for player "'.$ptoken.'" on map "'.$map_token.'"');
                        $pinfo = $mmrpg_index_players[$ptoken];
                        $tmp_session = $WORLD_SESSION['last_player_sessions'][$ptoken];
                        $tmp_world_token = !empty($tmp_session[$last_world_token_key]) ? $tmp_session[$last_world_token_key] : '';
                        $tmp_world_position = !empty($tmp_session[$last_world_position_key]) ? $tmp_session[$last_world_position_key] : '';
                        $tmp_world_direction = !empty($tmp_session[$last_world_direction_key]) ? $tmp_session[$last_world_direction_key] : '';
                        $tmp_world_robots = !empty($tmp_session[$last_world_robots_key]) ? $tmp_session[$last_world_robots_key] : '';
                        if (empty($tmp_world_token) || $tmp_world_token !== $map_token){ continue; } // skip if not on this map
                        if (empty($tmp_world_position)){ continue; } // skip if no position
                        $rival_symbols[$tmp_world_position] = $ptoken;
                        // If we made it this far, show this other player on the map at their current location (just non-interactacble)
                        //error_log('Found player "'.$ptoken.'" on map "'.$map_token.'" at position "'.$tmp_world_position.'"');
                        $tmp_team_sprites = array();
                        $tmp_team_sprites[] = array('player', $ptoken);
                        if (!empty($tmp_world_robots)){
                            $tmp_world_robots = explode(',', $tmp_world_robots);
                            foreach ($tmp_world_robots AS $robot_string){
                                list($robot_id, $robot_token) = explode('_', $robot_string, 2);
                                $robot = array('robot', $robot_token);
                                $robot_settings = rpg_game::robot_settings($ptoken, $robot_token);
                                $robot_image = !empty($robot_settings['robot_image']) ? $robot_settings['robot_image'] : '';
                                if (!empty($robot_image) && $robot_image !== $robot_token){ $robot[] = explode('_', $robot_image, 2)[1]; }
                                $tmp_team_sprites[] = $robot;
                            }
                        }
                        echo($get_team_sprites($tmp_team_sprites, $tmp_world_position, 'rival bounce'));
                    }

                    $rival_symbols_json = json_encode($rival_symbols, JSON_NUMERIC_CHECK);
                    echo('<script data-json="rivalSymbols" type="application/json">'.$rival_symbols_json.'</script>'.PHP_EOL);

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
            // Define or generate the markup for the various buttons and UI elements around the world map
            $wrap_markup = function($markup, $tag = 'div'){ return '<'.$tag.' class="wrapper">'.$markup.'</'.$tag.'>'; };
            $back_button_markup = $wrap_markup('<i class="fa fas fa-sign-out"></i>', 'a');
            $home_button_markup = $wrap_markup('<i class="fa fas fa-home"></i>', 'a');
            $reset_button_markup = $wrap_markup('<i class="fa fas fa-bomb"></i>', 'a');
            $player_switcher_markup = $wrap_markup(rpg_world::get_player_switcher_markup($this_prototype_data, $allowed_player_tokens));
            $position_display_markup = $wrap_markup('&hellip;');
            $side_buttons_markup = $wrap_markup('&hellip;');
            ?>
            <div id="back-button" class="chrome chrome-button"><?= $back_button_markup ?></div>
            <div id="home-button" class="chrome chrome-button"><?= $home_button_markup ?></div>
            <div id="reset-button" class="chrome chrome-button"><?= $reset_button_markup ?></div>
            <div id="player-switcher" class="chrome"><?= $player_switcher_markup ?></div>
            <div id="position-display" class="chrome"><?= $position_display_markup ?></div>
            <div id="side-buttons" class="chrome"><?= $side_buttons_markup ?></div>
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
_worldConfig.playerMobility = <?= MMRPG_WORLD_DEFAULT_MOBILITY ?>;
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