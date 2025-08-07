<?php

// Include the TOP file
require_once('top.php');

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
if (!empty($_REQUEST['reset'])
    && $_REQUEST['reset'] === 'world'){
    rpg_world::reset_session();
    header('Location: world.php');
    exit();
}

// Define some constants for the world map
define('MMRPG_WORLD_DEFAULT_MAPSIZE', 10);
define('MMRPG_WORLD_DEFAULT_TILESIZE', 80);
define('MMRPG_WORLD_DEFAULT_SPRITESITE', 40);
define('MMRPG_WORLD_DEFAULT_TEAMSIZE', 3); // TODO: make this dependant on limit hearts
define('MMRPG_WORLD_DEFAULT_MOBILITY', 1); // TODO: make this dependant on player skill
define('MMRPG_WORLD_MAPFILE_BASEPATH', 'prototype/worldmaps/');

// Collect the game session token in case we need it later
$session_token = rpg_game::session_token();

// Define a reference object for storing temporary world data
rpg_world::init_session();
$WORLD_SESSION = &$_SESSION['WORLD'];

// Predefine any missing world session variables so they're available
//if (!isset($WORLD_SESSION['last_player_token'])){ $WORLD_SESSION['last_player_token'] = ''; }
if (!isset($WORLD_SESSION['player_sessions'])){ $WORLD_SESSION['player_sessions'] = array(); }
if (!isset($WORLD_SESSION['player_sessions']['last_player'])){ $WORLD_SESSION['player_sessions']['last_player'] = ''; }
if (!isset($WORLD_SESSION['world_maps'])){ $WORLD_SESSION['world_maps'] = array(); }
if (!isset($WORLD_SESSION['world_buttons'])){ $WORLD_SESSION['world_buttons'] = array(); }
if (!isset($WORLD_SESSION['world_switches'])){ $WORLD_SESSION['world_switches'] = array(); }
if (!isset($WORLD_SESSION['world_encounters'])){ $WORLD_SESSION['world_encounters'] = array(); }

// Define defaults and allowed values for the prototype world data
//$allowed_map_tokens = array('starter', 'water', 'starter-80x80', 'water-80x80');
$existing_sheet_files = glob(MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH.'*.sheet');
$existing_map_files = glob(MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH.'*.map');
$allowed_sheet_tokens = array_map(function($path){ return preg_replace('/\.sheet$/i', '', basename($path)); }, $existing_sheet_files);
$allowed_map_tokens = array_map(function($path){ return preg_replace('/\.map$/i', '', basename($path)); }, $existing_map_files);
//error_log('$existing_sheet_files = '. print_r($existing_sheet_files, true));
//error_log('$existing_map_files = '. print_r($existing_map_files, true));
//error_log('$allowed_sheet_tokens = '. print_r($allowed_sheet_tokens, true));
//error_log('$allowed_map_tokens = '. print_r($allowed_map_tokens, true));
$allowed_player_tokens = mmrpg_prototype_players_unlocked(true);
array_unshift($allowed_player_tokens, 'player'); // always allow the "player" token
$default_world_token = 'debug-area-1'; //'starter-80x80';
$default_player_token = 'player';
$default_world_position = '';
$default_world_direction = '';

//error_log('$_GET = '. print_r($_GET, true));
//error_log('$_POST = '. print_r($_POST, true));

// If a save action was requested, we should do it here and then return exit
if (!empty($_POST['action']) && $_POST['action'] === 'save'
    && !empty($_POST['world_data']) && is_array($_POST['world_data'])){
    $worldData = $_POST['world_data'];
    $playerSessions = &$WORLD_SESSION['player_sessions'];
    if (!empty($worldData['lastPlayer'])
        && in_array($worldData['lastPlayer'], $allowed_player_tokens)){
        //$WORLD_SESSION['last_player_token'] = $worldData['lastPlayer'];
        $playerSessions['last_player'] = $worldData['lastPlayer'];
        // Collect the last player session data so we can update
        $lastPlayer = $worldData['lastPlayer'];
        if (!isset($playerSessions[$lastPlayer])){ $playerSessions[$lastPlayer] = array(); }
        $lastPlayerSession = &$playerSessions[$lastPlayer];
        // Collect the cursor player session data so we can update too
        $cursorPlayer = 'player';
        if (!isset($playerSessions[$cursorPlayer])){ $playerSessions[$cursorPlayer] = array(); }
        $cursorPlayerSession = &$playerSessions[$cursorPlayer];
        // If last world was provided, save it to the sessions
        if (!empty($worldData['lastPlayerWorld']) && in_array($worldData['lastPlayerWorld'], $allowed_map_tokens)){
            $last_world_token_key = 'last_world';
            $lastPlayerSession[$last_world_token_key] = $worldData['lastPlayerWorld'];
            $cursorPlayerSession[$last_world_token_key] = $worldData['lastPlayerWorld'];
        }
        // If last position was provided, save it to the sessions
        if (!empty($worldData['lastPlayerPosition']) && preg_match('/^([-0-9]+)$/i', $worldData['lastPlayerPosition'])){
            $last_world_position_key = 'last_position';
            $lastPlayerSession[$last_world_position_key] = $worldData['lastPlayerPosition'];
            $cursorPlayerSession[$last_world_position_key] = $worldData['lastPlayerPosition'];
        }
        // If last direction was provided, save it to the sessions
        if (!empty($worldData['lastPlayerDirection']) && preg_match('/^([-a-z0-9]+)$/i', $worldData['lastPlayerDirection'])){
            $last_world_direction_key = 'last_direction';
            $lastPlayerSession[$last_world_direction_key] = $worldData['lastPlayerDirection'];
            $cursorPlayerSession[$last_world_direction_key] = $worldData['lastPlayerDirection'];
        }
        // If world button states were provided, save them to the session
        if (!empty($worldData['lastWorldButtons'])){
            $last_world_buttons_key = 'world_buttons';
            if (!isset($WORLD_SESSION[$last_world_buttons_key])){ $WORLD_SESSION[$last_world_buttons_key] = array(); }
            $worldButtonStates = &$WORLD_SESSION[$last_world_buttons_key];
            foreach ($worldData['lastWorldButtons'] AS $map_token => $button_states){
                if (!in_array($map_token, $allowed_map_tokens)){ continue; }
                if (!isset($worldButtonStates[$map_token])){ $worldButtonStates[$map_token] = array(); }
                $worldButtonStates[$map_token] = array_merge($worldButtonStates[$map_token], $button_states);
            }
        }
        // If world switch states were provided, save them to the session
        if (!empty($worldData['lastWorldSwitches'])){
            $last_world_switches_key = 'world_switches';
            if (!isset($WORLD_SESSION[$last_world_switches_key])){ $WORLD_SESSION[$last_world_switches_key] = array(); }
            $worldSwitchStates = &$WORLD_SESSION[$last_world_switches_key];
            foreach ($worldData['lastWorldSwitches'] AS $map_token => $switch_states){
                if (!in_array($map_token, $allowed_map_tokens)){ continue; }
                if (!isset($worldSwitchStates[$map_token])){ $worldSwitchStates[$map_token] = array(); }
                $worldSwitchStates[$map_token] = array_merge($worldSwitchStates[$map_token], $switch_states);
            }
        }
        //error_log('World data saved successfully for player "'.$lastPlayer.'"!');
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

// Now that we have the player, we should collect the player's robots and other data
$this_player_id = 1;
$this_player_token = 'player';
$this_player_info = array();
$this_player_robots = array();
$this_prototype_data['this_player_id'] = $this_player_id; // required
$this_prototype_data['this_player_token'] = $this_player_token; // required
$this_prototype_data['this_player_robots'] = $this_player_robots; // required
$this_prototype_data['this_player_mobility'] = MMRPG_WORLD_DEFAULT_MOBILITY; // required
if (!empty($this_prototype_data['this_current_player'])){
    $this_player_token = $this_prototype_data['this_current_player'];
    $this_player_info = !empty($mmrpg_index_players[$this_player_token]) ? $mmrpg_index_players[$this_player_token] : array();
} else {
    die('MMRPG World Fatal Error - No player token defined!');
}

// Define the session keys we'll be using to store player-specific world settings
$last_world_token_key = 'last_world';
$last_world_position_key = 'last_position';
$last_world_direction_key = 'last_direction';
$last_world_robots_key = 'last_robots';

// Make sure the appropriate player token is set in the settings array
if (!isset($WORLD_SESSION['player_sessions'][$this_player_token])){ $WORLD_SESSION['player_sessions'][$this_player_token] = array(); }
$WORLD_PLAYER_SESSION = &$WORLD_SESSION['player_sessions'][$this_player_token];

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

// Update the player's mobility with any character-specific bonuses or contextual modifiers
if ($this_prototype_data['this_player_token'] === 'player'){ $this_prototype_data['this_player_mobility'] = -1; }
else { $this_prototype_data['this_player_mobility'] = MMRPG_WORLD_DEFAULT_MOBILITY; }

// Collect or define the current map token we'll be loading from
$request_world_token = isset($_REQUEST['world']) && preg_match('/^([-_a-z0-9]+)$/i', $_REQUEST['world']) ? trim($_REQUEST['world']) : '';
if (empty($request_world_token) && !empty($WORLD_PLAYER_SESSION[$last_world_token_key])){ $request_world_token = $WORLD_PLAYER_SESSION[$last_world_token_key]; }
if (!empty($request_world_token) && !empty($WORLD_PLAYER_SESSION[$last_world_token_key]) && $request_world_token !== $WORLD_PLAYER_SESSION[$last_world_token_key]){ unset($WORLD_PLAYER_SESSION[$last_world_position_key]); }
if (!empty($request_world_token) && in_array($request_world_token, $allowed_map_tokens)){
    $this_prototype_data['this_current_world'] = $request_world_token;
}
if (empty($this_prototype_data['this_current_world'])){ $this_prototype_data['this_current_world'] = $default_world_token; }
$WORLD_PLAYER_SESSION[$last_world_token_key] = $this_prototype_data['this_current_world'];

// Collect or define the current map position we'll be spawning into
$request_world_position = isset($_REQUEST['position']) && preg_match('/^([-0-9]+)$/i', $_REQUEST['position']) ? trim($_REQUEST['position']) : '';
$request_world_direction = isset($_REQUEST['direction']) && preg_match('/^([-a-z0-9]+)$/i', $_REQUEST['direction']) ? trim($_REQUEST['direction']) : '';
if (empty($request_world_position) && !empty($WORLD_PLAYER_SESSION[$last_world_position_key])){ $request_world_position = $WORLD_PLAYER_SESSION[$last_world_position_key]; }
if (empty($request_world_direction) && !empty($WORLD_PLAYER_SESSION[$last_world_direction_key])){ $request_world_direction = $WORLD_PLAYER_SESSION[$last_world_direction_key]; }
$this_prototype_data['this_current_position'] = !empty($request_world_position) ? $request_world_position : $default_world_position;
$this_prototype_data['this_current_direction'] = !empty($request_world_direction) ? $request_world_direction : $default_world_direction;
$WORLD_PLAYER_SESSION[$last_world_position_key] = $this_prototype_data['this_current_position'];

// Load map data from the appropriate map file
$map_token = $this_prototype_data['this_current_world'];
$map_name = str_replace(' AREA ', ' Area ', strtoupper(str_replace('-', ' ', $map_token)));
//error_log('$map_token = '.print_r($map_token, true));
//error_log('$map_name = '.print_r($map_name, true));
$map_data_parsed = rpg_world::load_map_data($map_token);
//error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
$map_sprite_sheet = !empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : '';
//error_log('$map_sprite_sheet = '.print_r($map_sprite_sheet, true));
if (empty($map_sprite_sheet)){ error_log('MMRPG World Fatal Error - No sprite sheet defined for map "'.$map_token.'"!'); die(); }

// Make sure there's room in relevant session arrays for this map's data
if (!isset($WORLD_SESSION['world_maps'][$map_token])){ $WORLD_SESSION['world_maps'][$map_token] = array(); }
if (!isset($WORLD_SESSION['world_encounters'][$map_token])){ $WORLD_SESSION['world_encounters'][$map_token] = array(); }
if (!isset($WORLD_SESSION['world_buttons'][$map_token])){ $WORLD_SESSION['world_buttons'][$map_token] = array(); }
if (!isset($WORLD_SESSION['world_switches'][$map_token])){ $WORLD_SESSION['world_switches'][$map_token] = array(); }

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
$map_spawn_pos = !empty($WORLD_SESSION['world_maps'][$map_token]['spawn_pos']) ? $WORLD_SESSION['world_maps'][$map_token]['spawn_pos'] : '';
$map_exit_pos = !empty($WORLD_SESSION['world_maps'][$map_token]['exit_pos']) ? $WORLD_SESSION['world_maps'][$map_token]['exit_pos'] : '';
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
$WORLD_SESSION['world_maps'][$map_token]['spawn_pos'] = $map_spawn_pos;
$WORLD_SESSION['world_maps'][$map_token]['exit_pos'] = $map_exit_pos;

// If the world position has not been set yet, we can use the spawn position for it
if (empty($this_prototype_data['this_current_position'])){ $this_prototype_data['this_current_position'] = $map_spawn_pos; }

// If there are any buttons defined, check to see if any of them have been pushed already
if (!empty($map_data_parsed['buttons'])){
    $button_sprites = $map_data_parsed['buttons'];
    $world_buttons = !empty($WORLD_SESSION['world_buttons'][$map_token]) ? $WORLD_SESSION['world_buttons'][$map_token] : array();
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
            $tiles_index_keys = !empty($tiles_index['keys']) ? array_flip($tiles_index['keys']) : array();
            $layer_key = 0; // currently, this is the main and only terrain layer
            $layer_tiles = !empty($map_data_parsed['layers'][$layer_key]) ? $map_data_parsed['layers'][$layer_key] : array();
            //error_log('$groups_index = '.print_r($groups_index, true));
            //error_log('$group_tiles = '.print_r($group_tiles, true));
            //error_log('$tiles_index = '.print_r($tiles_index, true));
            //error_log('$tiles_index_keys = '.print_r($tiles_index_keys, true));
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
                    $new_tile_value = $tiles_index_keys[$terrain_name]; // TODO: this should be a number lol
                    //error_log('--> $current_tile_value = '.print_r($current_tile_value, true));
                    //error_log('--> $new_tile_value = '.print_r($new_tile_value, true));
                    $row_tiles[$tx] = $new_tile_value;
                    $row_tiles = implode(',', array_map(function($tile){ return '['.$tile.']'; }, $row_tiles));
                    $layer_tiles[$ty] = $row_tiles;
                    //error_log('--> $layer_tiles['.$ty.'](updated) = '.print_r($layer_tiles[$ty], true));
                }
                $map_data_parsed['layers'][$layer_key] = $layer_tiles;
            }
        }
        // ...
    }
}

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
                $data['groups_index'] = $map_data_parsed['groups'];
                $data['start_position'] = $this_prototype_data['this_current_position'];
                $data['start_direction'] = $this_prototype_data['this_current_direction'];
                $data_json = json_encode($data, JSON_NUMERIC_CHECK);
                echo('<script data-json="mapData" type="application/json">'.$data_json.'</script>'.PHP_EOL);

                // BACKGROUND IMAGE
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                $background_layer_markup = rpg_world::get_background_layer_markup($this_prototype_data, $map_data_parsed);
                echo('<div class="layer layer-0 background" data-layer="background" '.$map_layer_styles.$map_layer_attrs.'>');
                    echo($background_layer_markup);
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

                // PORTAL SPRITES
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                $portals_layer_markup = rpg_world::get_portals_layer_markup($this_prototype_data, $map_data_parsed);
                echo('<div class="layer layer-2 tiles events portals" data-layer="portals" '.$map_layer_styles.$map_layer_attrs.'>');
                    echo($portals_layer_markup);
                echo('</div>'.PHP_EOL);

                // BUTTON SPRITES
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                $buttons_layer_markup = rpg_world::get_buttons_layer_markup($this_prototype_data, $map_data_parsed);
                echo('<div class="layer layer-3 tiles events buttons" data-layer="buttons" '.$map_layer_styles.$map_layer_attrs.'>');
                    echo($buttons_layer_markup);
                echo('</div>'.PHP_EOL);

                // BATTLE SPRITES
                $map_layer_styles = !empty($map_base_styles) ? ' style="'.$map_base_styles.'"' : '';
                $map_layer_attrs = !empty($map_base_attrs) ? ' '.$map_base_attrs : '';
                $battles_layer_markup = rpg_world::get_battles_layer_markup($this_prototype_data, $map_data_parsed);
                echo('<div class="layer layer-4 objects events battles" data-layer="battles" '.$map_layer_styles.$map_layer_attrs.'>');
                    echo($battles_layer_markup);
                echo('</div>'.PHP_EOL);

                // CHARACTER OBJECTS (TEAM)
                $map_layer_styles = $map_base_styles;
                $map_layer_attrs = $map_base_attrs;
                ?>
                <div class="layer layer-5 objects characters team" data-layer="team" style="<?= $map_layer_styles ?>" <?= $map_layer_attrs ?>>
                    <?

                    // Quick function for generation the team sprites for a given player
                    $get_team_sprites = function($team_sprites, $target_position = '1-1', $team_class = 'team', $team_dir = 'down-right')
                        use ($map_tile_height, $map_tile_width, $map_spritesize_offset){
                        $sprites = array();
                        list($col, $row) = explode('-', $target_position);
                        $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                        $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                        foreach ($team_sprites as $key => $sprite){
                            $kind = $sprite[0];
                            $token = $sprite[1];
                            $img = isset($sprite[2]) ? $sprite[2] : $token;
                            $alt = strstr($img, '_') ? explode('_', $img, 2)[1] : '';
                            $dir = strstr($team_dir, 'left') ? 'left' : 'right';
                            if ($key > 0){
                                if (strstr($team_dir, 'right')){ $left -= 4; }
                                elseif (strstr($team_dir, 'right')){ $left += 4; }
                                if (strstr($team_dir, 'up')){ $top += 2; }
                                elseif (strstr($team_dir, 'down')){ $top -= 2; }
                                }
                            $class = $team_class; //'team bounce';
                            $styles = 'top: '.$top.'px; left: '.$left.'px; ';
                            $attrs = 'data-key="'.$key.'"';
                            $markup = rpg_world::get_sprite($kind, $img, $alt, $dir, $class, $styles, $attrs);
                            if (!empty($markup)){ $sprites[] = $markup; }
                            }
                        return implode(PHP_EOL, $sprites);
                        };

                    // Collect the current team members from the prototype data
                    $team_position = $this_prototype_data['this_current_position'];
                    $team_direction = $this_prototype_data['this_current_direction'];
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
                            $robot_image = '';
                            if (!empty($robot_settings['robot_persona_image'])){ $robot_image = $robot_settings['robot_persona_image']; }
                            elseif (!empty($robot_settings['robot_image'])){ $robot_image = $robot_settings['robot_image']; }
                            if (!empty($robot_image)){ $robot[] = $robot_image; }
                            $team_sprites[] = $robot;
                        }
                    }

                    // Generate the markup for the cursor sprite
                    $pos = $team_position;
                    list($col, $row) = explode('-', $pos);
                    $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                    $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                    $class = 'cursor bounce';
                    $styles = 'top: '.$top.'px; left: '.$left.'px; ';
                    $attrs = 'data-pos="'.$team_position.'" data-col="'.$col.'" data-row="'.$row.'"';
                    echo(rpg_world::get_cursor_sprite($team_direction, $class, $styles, $attrs));

                    // Generate the markup for the team sprites if any are defined
                    echo($get_team_sprites($team_sprites, $team_position, 'team bounce', $team_direction));

                    // Loop through the other allowed players to see if any are also on this map
                    $rival_symbols = array();
                    foreach ($allowed_player_tokens AS $pkey => $ptoken){
                        if ($ptoken === 'player'){ continue; } // skip the default player
                        if ($ptoken === $team_player_token){ continue; } // skip the current player
                        if (empty($mmrpg_index_players[$ptoken])){ continue; } // skip if not a valid player
                        if (empty($WORLD_SESSION['player_sessions'][$ptoken])){ continue; } // skip if no player session
                        //error_log('Checking for player "'.$ptoken.'" on map "'.$map_token.'"');
                        $pinfo = $mmrpg_index_players[$ptoken];
                        $tmp_session = $WORLD_SESSION['player_sessions'][$ptoken];
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
_worldConfig.playerMobility = <?= json_encode($this_prototype_data['this_player_mobility']) ?>;
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