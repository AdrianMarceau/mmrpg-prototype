<?
/**
 * Mega Man RPG World
 * <p>The global world (map) class for the Mega Man RPG Prototype.</p>
 */

// Define the constants that will be used in the class and maybe elsewhere
define('MMRPG_WORLD_DEFAULT_MAPSIZE', 10);
define('MMRPG_WORLD_DEFAULT_TILESIZE', 80);
define('MMRPG_WORLD_DEFAULT_SPRITESITE', 40);
define('MMRPG_WORLD_DEFAULT_TEAMSIZE', 8); // TODO: hardcode for now, review later
define('MMRPG_WORLD_DEFAULT_MOBILITY', 1); // TODO: make this dependant on player skill
define('MMRPG_WORLD_DEFAULT_BASEPATH', '/');

// Define the actual RPG_WORLD class that uses the above constants and methods
class rpg_world {

    // -- PREDEFINED CONSTANTS -- //

    // Define the defaults for this class
    static $worldmap_mapsize = MMRPG_WORLD_DEFAULT_MAPSIZE;
    static $worldmap_tilesize = MMRPG_WORLD_DEFAULT_TILESIZE;
    static $worldmap_spritesize = MMRPG_WORLD_DEFAULT_SPRITESITE;
    static $worldmap_basedir = MMRPG_CONFIG_ROOTDIR;
    static $worldmap_baseurl = MMRPG_CONFIG_ROOTURL;
    static $worldmap_basepath = MMRPG_WORLD_DEFAULT_BASEPATH;

    // Define functions for manually setting the map constants for this class
    public static function set_mapsize($mapsize){
        //error_log('rpg_world::set_mapsize() called!');
        if (empty($mapsize) || !is_numeric($mapsize)){ return false; }
        self::$worldmap_mapsize = intval($mapsize);
        //error_log('set map size to "'.self::$worldmap_mapsize.'"');
        return true;
    }
    public static function set_tilesize($tilesize){
        //error_log('rpg_world::set_tilesize() called!');
        if (empty($tilesize) || !is_numeric($tilesize)){ return false; }
        self::$worldmap_tilesize = intval($tilesize);
        //error_log('set map tile size to "'.self::$worldmap_tilesize.'"');
        return true;
    }
    public static function set_spritesize($spritesize){
        //error_log('rpg_world::set_spritesize() called!');
        if (empty($spritesize) || !is_numeric($spritesize)){ return false; }
        self::$worldmap_spritesize = intval($spritesize);
        //error_log('set map sprite size to "'.self::$worldmap_spritesize.'"');
        return true;
    }
    public static function set_basepath($basepath){
        //error_log('rpg_world::set_basepath() called!');
        if (empty($basepath) || !is_string($basepath)){ return false; }
        $basepath = trim($basepath, '/').'/';
        self::$worldmap_basepath = $basepath;
        //error_log('set map base path to "'.self::$worldmap_basepath.'"');
        return true;
    }

    // -- CONTENT INDEXES -- //

    // Define the static variables for this class
    static $mmrpg_indexes = array();

    // Define a function for getting a specific index loaded into this class
    public static function get_index($kind){
        //error_log('rpg_world::get_index() called!');
        if (empty($kind) || !isset(self::$mmrpg_indexes[$kind])){ false; }
        elseif (empty(self::$mmrpg_indexes[$kind])){ return array(); }
        return self::$mmrpg_indexes[$kind];
    }

    // Define a function for getting the indexes loaded into this class
    public static function get_indexes(){
        //error_log('rpg_world::get_indexes() called!');
        if (!isset(self::$mmrpg_indexes)){ return false; }
        elseif (empty(self::$mmrpg_indexes)){ return array(); }
        return self::$mmrpg_indexes;
    }

    // Define a function for preloading indexes into this class
    public static function preload_indexes($indexes = array()){
        //error_log('rpg_world::preload_indexes() called!');
        if (empty($indexes) || !is_array($indexes)){ return false; }
        // Loop through the indexes and add them to the static variable
        foreach ($indexes AS $kind => $index){
            if (empty($kind) || empty($index) || !is_array($index)){ continue; }
            self::$mmrpg_indexes[$kind] = $index;
            //error_log('loaded index for "'.$kind.'" with '.count($index).' items');
        }
        return true;
    }

    // -- WORLD SESSION METHODS -- //

    // Define a function for getting the world session token
    public static function session_token(){
        //error_log('rpg_world::session_token() called!');
        return 'WORLD';
    }

    // Define a function for initializing the world session if not exists yet
    public static function init_session(){
        //error_log('rpg_world::init_session() called!');
        $session_token = self::session_token();
        if (!isset($_SESSION[$session_token])){ $_SESSION[$session_token] = array(); }
        $WORLD_SESSION = &$_SESSION[$session_token];
        // Predefine any missing world session variables so they're available
        if (!isset($WORLD_SESSION['player_sessions'])){ $WORLD_SESSION['player_sessions'] = array(); }
        if (!isset($WORLD_SESSION['robot_sessions'])){ $WORLD_SESSION['robot_sessions'] = array(); }
        if (!isset($WORLD_SESSION['world_maps'])){ $WORLD_SESSION['world_maps'] = array(); }
        if (!isset($WORLD_SESSION['world_buttons'])){ $WORLD_SESSION['world_buttons'] = array(); }
        if (!isset($WORLD_SESSION['world_switches'])){ $WORLD_SESSION['world_switches'] = array(); }
        if (!isset($WORLD_SESSION['world_encounters'])){ $WORLD_SESSION['world_encounters'] = array(); }
        // ...as well as any nested variables inside those parent arrays
        if (!isset($WORLD_SESSION['player_sessions']['last_player'])){ $WORLD_SESSION['player_sessions']['last_player'] = ''; }
        // Return true now that we're done preparing the session
        return true;
    }

    // Define a function for getting the current world session
    public static function get_session(){
        //error_log('rpg_world::get_session() called!');
        $session_token = self::session_token();
        if (!isset($_SESSION[$session_token])){ $_SESSION[$session_token] = array(); }
        return $_SESSION[$session_token];
    }

    // Define a function for resetting the world session
    public static function reset_session(){
        //error_log('rpg_world::reset_session() called!');
        // Clear the top-level WORLD session variable entirely to start fresh
        $world_session_token = self::session_token();
        if (isset($_SESSION[$world_session_token])){ unset($_SESSION[$world_session_token]); }
        $_SESSION[$world_session_token] = array();
        // Also clear any parent-level GAME session variables that are world-specific
        $game_session_token = rpg_game::session_token();
        if (!isset($_SESSION[$game_session_token])){ return true; } // return early if no game session exists
        $GAME_SESSION = &$_SESSION[$game_session_token];
        // If there are any world-specific battles in game battle index, unset them now
        if (!empty($GAME_SESSION['values']['battle_index'])){
            foreach ($GAME_SESSION['values']['battle_index'] AS $battle_token => $battle_data){
                if (strpos($battle_token, 'world-battle_') !== 0){ continue; } // not a world-battle, don't touch it
                //error_log('rpg_world::reset_session() unsetting world-battle token "'.$battle_token.'"');
                unset($GAME_SESSION['values']['battle_index'][$battle_token]); // unset this battle from the game session
            }
        }
        // Return true now that we're done resetting the session
        return true;
    }

    // Define a function for saving the current world session
    public static function update_session(){
        //error_log('rpg_world::update_session() called!');
        $session_token = self::session_token();
        $args = func_get_args();
        if (count($args) < 2) { return false; }
        $value = array_pop($args);
        $keys  = $args;
        foreach ($keys as $k){ if (empty($k) || !is_string($k)) { return false; } }
        if (empty($value) || !is_array($value)) { return false; }
        if (!isset($_SESSION[$session_token])){ $_SESSION[$session_token] = array(); }
        $ref =& $_SESSION[$session_token];
        foreach ($keys as $k){
            if (!isset($ref[$k]) || !is_array($ref[$k])) { $ref[$k] = array(); }
            $ref =& $ref[$k];
        }
        $ref = $value;
        return true;
    }

    // Define a function for saving the current world session to the system
    public static function save_session(){
        //error_log('rpg_world::save_session() called!');
        // loop through and update the json session files with new data
        $WORLD_SESSION = self::get_session();
        $is_member = rpg_user::is_member();
        $user_group = $is_member ? 'members' : 'guests';
        $user_id = $is_member ? rpg_user::get_current_userid() : rpg_user::get_current_userip();
        $json_session_path = MMRPG_CONFIG_ROOTDIR.'.cache/sessions/'.$user_group.'/'.$user_id.'/';
        foreach ($WORLD_SESSION AS $key => $values){
            if (in_array($key, array('player_sessions', 'world_maps', 'world_encounters', 'world_buttons', 'world_switches'))){
                $json_session_file = 'WORLD__'.$key.'.json';
                $json_session_file_path = $json_session_path.$json_session_file;
                if (!is_dir(dirname($json_session_file_path))){ mkdir(dirname($json_session_file_path), 0755, true); }
                file_put_contents($json_session_file_path, json_encode($values, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
            }
        }
        // return true on success
        return true;
    }

    // -- WORLD MAP METHODS -- //

    // Define a function for loading a given map's data from the filesystem
    public static function load_map_data($world_map_token){
        //error_log('load_map_data() called!');
        if (empty($world_map_token)){ error_log('rpg_world::load_map_data() error - missing world-map token!'); return false; }
        if (!strstr($world_map_token, '__')){ error_log('rpg_world::load_map_data() error - invalid world-map token "'.$world_map_token.'"!'); return false; }
        list($world_token, $map_token) = explode('__', $world_map_token);
        $map_basedir = self::$worldmap_basedir.self::$worldmap_basepath;
        $map_filename = $world_token.'/'.$map_token.'.map';
        $map_filedir = $map_basedir.$map_filename;
        if (!file_exists($map_filedir)){
            //error_log('load_map_data() file not found "'.$map_filedir.'"!');
            return false;
            }
        $map_data_raw = file_get_contents($map_filedir);
        if (empty($map_data_raw)){
            //error_log('load_map_data() file empty "'.$map_filedir.'"!');
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
        $map_mapsize = self::$worldmap_mapsize;
        $map_tilesize = self::$worldmap_tilesize;
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
        $map_data_vars['world'] = isset($map_data_vars['world']) ? $map_data_vars['world'] : '';
        $map_data_vars['token'] = isset($map_data_vars['token']) ? $map_data_vars['token'] : '';
        $map_data_vars['name'] = isset($map_data_vars['name']) ? $map_data_vars['name'] : '';
        $map_data_vars['level'] = isset($map_data_vars['level']) ? $map_data_vars['level'] : 1;
        $map_data_vars['size'] = isset($map_data_vars['size']) ? $map_data_vars['size'] : '';
        $map_data_vars['sheet'] = isset($map_data_vars['sheet']) ? $map_data_vars['sheet'] : '';
        $map_data_vars['tiles'] = isset($map_data_vars['tiles']) ? $map_data_vars['tiles'] : array();
        $map_data_vars['groups'] = isset($map_data_vars['groups']) ? $map_data_vars['groups'] : array();
        $map_data_vars['sprites'] = isset($map_data_vars['sprites']) ? $map_data_vars['sprites'] : array();
        $map_data_vars['events'] = isset($map_data_vars['events']) ? $map_data_vars['events'] : array();
        $map_data_vars['portals'] = isset($map_data_vars['portals']) ? $map_data_vars['portals'] : array();
        $map_data_vars['buttons'] = isset($map_data_vars['buttons']) ? $map_data_vars['buttons'] : array();
        $map_data_vars['switches'] = isset($map_data_vars['switches']) ? $map_data_vars['switches'] : array();
        $map_data_vars['field'] = isset($map_data_vars['field']) ? $map_data_vars['field'] : '';
        $map_data_vars['terrain'] = isset($map_data_vars['terrain']) ? $map_data_vars['terrain'] : array();
        $map_data_vars['encounters'] = isset($map_data_vars['encounters']) ? $map_data_vars['encounters'] : '';
        $map_data_vars['habitats'] = isset($map_data_vars['habitats']) ? $map_data_vars['habitats'] : array();
        $map_data_vars['mechas'] = isset($map_data_vars['mechas']) ? $map_data_vars['mechas'] : array();
        $map_data_vars['masters'] = isset($map_data_vars['masters']) ? $map_data_vars['masters'] : array();
        $map_data_vars['bosses'] = isset($map_data_vars['bosses']) ? $map_data_vars['bosses'] : array();
        if (empty($map_data_vars['world'])){ $map_data_vars['world'] = $world_token; }
        if (empty($map_data_vars['token'])){ $map_data_vars['token'] = $map_token; }
        if (empty($map_data_vars['name'])){ $map_data_vars['name'] = 'Undefined'; }
        if (empty($map_data_vars['size'])){ $map_data_vars['size'] = '0 x 0 x 0'; }
        if (empty($map_data_vars['sheet'])){ $map_data_vars['sheet'] = 'undefined.png'; }
        if (!empty($map_data_vars['size'])){ $map_data_vars['size'] = explode('x', str_replace(' ', '', $map_data_vars['size'])); }
        if (!isset($map_data_vars['size'][0])){ $map_data_vars['size'][0] = $map_autocols; }
        if (!isset($map_data_vars['size'][1])){ $map_data_vars['size'][1] = $map_autorows; }
        if (!isset($map_data_vars['size'][2])){ $map_data_vars['size'][2] = $map_tilesize; }
        if (empty($map_data_vars['field'])){ $map_data_vars['field'] = 'field'; }
        if (!empty($map_data_vars['encounters'])){ $map_data_vars['encounters'] = explode(',', str_replace(' ', '', $map_data_vars['encounters'])); }
        $map_data_vars['tiles'] = $map_custval_parser($map_data_vars['tiles'], true);
        $map_data_vars['groups'] = $map_custval_parser($map_data_vars['groups']);
        $map_data_vars['sprites'] = $map_custval_parser($map_data_vars['sprites']);
        $map_data_vars['events'] = $map_custval_parser($map_data_vars['events']);
        $map_data_vars['portals'] = $map_custval_parser($map_data_vars['portals']);
        $map_data_vars['buttons'] = $map_custval_parser($map_data_vars['buttons']);
        $map_data_vars['switches'] = $map_custval_parser($map_data_vars['switches']);
        $map_data_vars['terrain'] = $map_custval_parser($map_data_vars['terrain']);
        $map_data_vars['habitats'] = $map_custval_parser($map_data_vars['habitats']);
        $map_data_vars['mechas'] = $map_custval_parser($map_data_vars['mechas']);
        $map_data_vars['masters'] = $map_custval_parser($map_data_vars['masters']);
        $map_data_vars['bosses'] = $map_custval_parser($map_data_vars['bosses']);
        // Add collected data to the parsed map data
        $map_data_parsed = array();
        $map_data_parsed['world'] = $map_data_vars['world']; unset($map_data_vars['world']);
        $map_data_parsed['token'] = $map_data_vars['token']; unset($map_data_vars['token']);
        $map_data_parsed['name'] = $map_data_vars['name']; unset($map_data_vars['name']);
        $map_data_parsed['level'] = $map_data_vars['level']; unset($map_data_vars['level']);
        $map_data_parsed['size'] = $map_data_vars['size']; unset($map_data_vars['size']);
        $map_data_parsed['sheet'] = $map_data_vars['sheet']; unset($map_data_vars['sheet']);
        $map_data_parsed['tiles'] = $map_data_vars['tiles']; unset($map_data_vars['tiles']);
        $map_data_parsed['groups'] = $map_data_vars['groups']; unset($map_data_vars['groups']);
        $map_data_parsed['sprites'] = $map_data_vars['sprites']; unset($map_data_vars['sprites']);
        $map_data_parsed['events'] = $map_data_vars['events']; unset($map_data_vars['events']);
        $map_data_parsed['portals'] = $map_data_vars['portals']; unset($map_data_vars['portals']);
        $map_data_parsed['buttons'] = $map_data_vars['buttons']; unset($map_data_vars['buttons']);
        $map_data_parsed['switches'] = $map_data_vars['switches']; unset($map_data_vars['switches']);
        $map_data_parsed['field'] = $map_data_vars['field']; unset($map_data_vars['field']);
        $map_data_parsed['terrain'] = $map_data_vars['terrain']; unset($map_data_vars['terrain']);
        $map_data_parsed['encounters'] = $map_data_vars['encounters']; unset($map_data_vars['encounters']);
        $map_data_parsed['habitats'] = $map_data_vars['habitats']; unset($map_data_vars['habitats']);
        $map_data_parsed['mechas'] = $map_data_vars['mechas']; unset($map_data_vars['mechas']);
        $map_data_parsed['masters'] = $map_data_vars['masters']; unset($map_data_vars['masters']);
        $map_data_parsed['bosses'] = $map_data_vars['bosses']; unset($map_data_vars['bosses']);
        //$map_data_parsed['tiles']['keys'] = array_keys($map_data_parsed['tiles']);
        $map_data_parsed['layers'] = $map_data_layers;
        if (!empty($map_data_vars)){ $map_data_parsed['vars'] = $map_data_vars; }
        //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
        // Calculate basic config values (dimensions, etc.) this map for easier sizing and stuff
        $map_base_size = $map_data_parsed['size'];
        if (count($map_base_size) === 4){ list($map_col_size, $map_row_size, $map_tile_width, $map_tile_height) = $map_base_size; }
        elseif (count($map_base_size) === 3){ list($map_col_size, $map_row_size, $map_tile_width) = $map_base_size; }
        elseif (count($map_base_size) === 2){ list($map_col_size, $map_tile_width) = $map_base_size; }
        elseif (count($map_base_size) === 1){ list($map_col_size) = $map_base_size; }
        if (!isset($map_col_size)){ $map_col_size = self::$worldmap_mapsize; }
        if (!isset($map_row_size)){ $map_row_size = $map_col_size; }
        if (!isset($map_tile_width)){ $map_tile_width = self::$worldmap_tilesize; }
        if (!isset($map_tile_height)){ $map_tile_height = $map_tile_width; }
        $map_pixel_width = $map_col_size * $map_tile_width;
        $map_pixel_height = $map_row_size * $map_tile_height;
        $map_tilesize_default = self::$worldmap_tilesize;
        $map_tilesize_offset = array(0, 0);
        if ($map_tile_height > $map_tilesize_default){ $map_tilesize_offset[0] = floor(($map_tile_height - $map_tilesize_default) / 2); }
        if ($map_tile_width > $map_tilesize_default){ $map_tilesize_offset[1] = floor(($map_tile_width - $map_tilesize_default) / 2); }
        $map_spritesize_default = self::$worldmap_spritesize;
        $map_spritesize_offset = array(0, 0);
        if ($map_tile_height > $map_spritesize_default){ $map_spritesize_offset[0] = floor(($map_tile_height - $map_spritesize_default) / 2); }
        elseif ($map_tile_height < $map_spritesize_default){ $map_spritesize_offset[0] = floor(($map_spritesize_default - $map_tile_height) / 2); }
        if ($map_tile_width > $map_spritesize_default){ $map_spritesize_offset[1] = floor(($map_tile_width - $map_spritesize_default) / 2); }
        elseif ($map_tile_width < $map_spritesize_default){ $map_spritesize_offset[1] = floor(($map_spritesize_default - $map_tile_width) / 2); }
        //error_log('$map_spritesize_default = '.print_r($map_spritesize_default, true));
        //error_log('$map_tile_height = '.print_r($map_tile_height, true));
        //error_log('$map_tile_width = '.print_r($map_tile_width, true));
        //error_log('$map_spritesize_offset = '.print_r($map_spritesize_offset, true));
        $map_size_styles = 'width: '.$map_pixel_width.'px; height: '.$map_pixel_height.'px; ';
        $map_offset_styles = 'top: 0px; left: 0px; ';
        $map_base_styles = trim($map_size_styles.$map_offset_styles);
        $map_base_attrs = 'data-cols="'.$map_col_size.'" data-rows="'.$map_row_size.'"';
        $map_base_attrs .= ' data-size="'.$map_col_size.' x '.$map_row_size.' x '. $map_tile_width.' x '.$map_tile_height.'"';
        $map_config = array();
        $map_config['base_size'] = $map_base_size;
        $map_config['col_size'] = $map_col_size;
        $map_config['row_size'] = $map_row_size;
        $map_config['tile_width'] = $map_tile_width;
        $map_config['tile_height'] = $map_tile_height;
        $map_config['pixel_width'] = $map_pixel_width;
        $map_config['pixel_height'] = $map_pixel_height;
        $map_config['tilesize_default'] = $map_tilesize_default;
        $map_config['tilesize_offset'] = $map_tilesize_offset;
        $map_config['spritesize_default'] = $map_spritesize_default;
        $map_config['spritesize_offset'] = $map_spritesize_offset;
        $map_config['size_styles'] = $map_size_styles;
        $map_config['offset_styles'] = $map_offset_styles;
        $map_config['base_styles'] = $map_base_styles;
        $map_config['base_attrs'] = $map_base_attrs;
        $map_data_parsed['config'] = $map_config;
        // If the map has a sprite sheet token defined, rather than just an image, load it and merge the data
        $map_sprite_sheet = !empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : '';
        //error_log('$map_sprite_sheet = '.print_r($map_sprite_sheet, true));
        if (!empty($map_sprite_sheet)){
            if (substr($map_sprite_sheet, -4) !== '.png'){
                $sheet_token = $map_sprite_sheet;
                $sheet_data_parsed = !empty($sheet_token) ? self::load_sheet_data($world_token.'__'.$sheet_token) : array();
                //error_log('$sheet_data_parsed = '.print_r($sheet_data_parsed, true));
                if (!empty($sheet_data_parsed)){
                    $map_sheet = !empty($map_data_parsed['sheet']) ? $map_data_parsed['sheet'] : '';
                    $map_size = !empty($map_data_parsed['size']) ? $map_data_parsed['size'] : array();
                    $map_tiles = !empty($map_data_parsed['tiles']) ? $map_data_parsed['tiles'] : array();
                    $map_tiles_keys = !empty($map_tiles['keys']) ? $map_tiles['keys'] : array();
                    $map_sprites = !empty($map_data_parsed['sprites']) ? $map_data_parsed['sprites'] : array();
                    $map_sprites_keys = !empty($map_sprites['keys']) ? $map_sprites['keys'] : array();
                    $sheet_image = !empty($sheet_data_parsed['image']) ? $sheet_data_parsed['image'] : '';
                    $sheet_size = !empty($sheet_data_parsed['size']) ? $sheet_data_parsed['size'] : array();
                    $sheet_tiles = !empty($sheet_data_parsed['tiles']) ? $sheet_data_parsed['tiles'] : array();
                    $sheet_tiles_keys = !empty($sheet_tiles['keys']) ? $sheet_tiles['keys'] : array();
                    $sheet_sprites = !empty($sheet_data_parsed['sprites']) ? $sheet_data_parsed['sprites'] : array();
                    $sheet_sprites_keys = !empty($sheet_sprites['keys']) ? $sheet_sprites['keys'] : array();
                    if (!empty($sheet_image)){ $map_sheet = $sheet_image; }
                    if (!empty($sheet_size)){ list($x, $y) = $map_size; list($w, $h) = $sheet_size; $map_size = array($x, $y, $w, $h); }
                    if (!empty($sheet_tiles)){ $map_tiles = array_merge($map_tiles, $sheet_tiles); $map_tiles['keys'] = $map_tiles_keys + $sheet_tiles_keys; }
                    if (!empty($sheet_sprites)){ $map_sprites = array_merge($map_sprites, $sheet_sprites); $map_sprites['keys'] = $map_sprites_keys + $sheet_sprites_keys; }
                    $map_data_parsed['sheet'] = $map_sheet;
                    $map_data_parsed['size'] = $map_size;
                    $map_data_parsed['tiles'] = $map_tiles;
                    $map_data_parsed['sprites'] = $map_sprites;
                }
                $map_sprite_sheet = $map_data_parsed['sheet'];
                //error_log('$map_sprite_sheet (parsed) = '.print_r($map_sprite_sheet, true));
                //error_log('$map_data_parsed (merged) = '.print_r($map_data_parsed, true));
                //exit();
            }
        }
        // Given we have all the information we need, let's actually parse the map layers into useable data now
        if (!empty($map_data_parsed['layers'])
            && !empty($map_data_parsed['tiles']['keys'])){
            //error_log('Let\'s actually parse the map tiles now...');
            //error_log('-> $map_data_parsed[\'layers\'](before) = '.print_r($map_data_parsed['layers'], true));
            $parsed_map_layers = array();
            $raw_map_layers = $map_data_parsed['layers'];
            $raw_tile_keys = $map_data_parsed['tiles']['keys'];
            //error_log('-> $raw_map_layers = '.print_r($raw_map_layers, true));
            //error_log('-> $raw_tile_keys = '.print_r($raw_tile_keys, true));
            foreach ($raw_map_layers AS $layer_key => $layer_tiles){
                //error_log('--> checking $layer_key = '.print_r($layer_key, true));
                foreach ($layer_tiles AS $row_key => $row_tiles){
                    //error_log('---> checking $row_key = '.$row_key.' w/ $row_tiles = '.print_r($row_tiles, true));
                    if (empty(trim($row_tiles))){ continue; }
                    $row_tiles = explode(',', str_replace(' ', '', trim($row_tiles)));
                    foreach ($row_tiles AS $col_key => $col_tile){
                        //error_log('----> checking $col_key = '.$col_key.' w/ $col_tile = '.print_r($col_tile, true));
                        if (substr($col_tile, 0, 1) === '[' && substr($col_tile, -1) === ']'){ $col_tile = substr($col_tile, 1, -1); } // remove brackets if present
                        if (!is_numeric($col_tile)){ $col_tile = 0; } // ensure this is a numeric tile key
                        $col_tile = intval($col_tile); // ensure this is an integer tile key
                        $col_tile_key = isset($raw_tile_keys[$col_tile]) ? $raw_tile_keys[$col_tile] : ''; // get the tile key from the raw keys
                        //error_log('----> parsed $col_tile '.print_r($row_tiles[$col_key], true).' => '.print_r($col_tile, true).' => '.print_r($col_tile_key, true));
                        $parsed_map_layers[$layer_key][$row_key][$col_key] = $col_tile_key; // add the tile key to the parsed map layers
                    }
                    $parsed_map_layers[$layer_key][$row_key] = implode(',', $parsed_map_layers[$layer_key][$row_key]); // implode the row tiles back into a string
                }
                $map_data_parsed['layers'][$layer_key] = $parsed_map_layers[$layer_key]; // add the parsed layer to the map data
            }
            //error_log('-> $parsed_map_layers = '.print_r($parsed_map_layers, true));
            //error_log('-> $map_data_parsed[\'layers\'](after) = '.print_r($map_data_parsed['layers'], true));
        }
        // Return the parsed map data
        return $map_data_parsed;
    }

    // Define a function for loading a given sheet's data from the filesystem
    public static function load_sheet_data($world_sheet_token){
        //error_log('load_sheet_data() called!');
        if (empty($world_sheet_token)){ error_log('rpg_world::load_sheet_data() error - missing world-sheet token!'); return false; }
        elseif (!strstr($world_sheet_token, '__')){ error_log('rpg_world::load_sheet_data() error - invalid world-sheet token "'.$world_sheet_token.'"!'); return false; }
        list($world_token, $sheet_token) = explode('__', $world_sheet_token);
        $sheet_basedir = self::$worldmap_basedir.self::$worldmap_basepath;
        $sheet_tilesize = self::$worldmap_tilesize;
        $sheet_filename = $world_token.'/'.$sheet_token.'.sheet';
        $sheet_filedir = $sheet_basedir.$sheet_filename;
        if (!file_exists($sheet_filedir)){ error_log('rpg_world::load_sheet_data() error - file not found "'.$sheet_filedir.'"!'); return false; }
        $sheet_data_raw = file_get_contents($sheet_filedir);
        if (empty($sheet_data_raw)){ error_log('rpg_world::load_sheet_data() error - file empty "'.$sheet_filedir.'"!'); return false; }
        $sheet_data_array = explode("\n", trim($sheet_data_raw));
        $sheet_data_vars = array();
        foreach ($sheet_data_array AS $line){
            $line = trim($line);
            // Ignore empty lines and comments
            if (empty(trim($line))){ continue; }
            else if (strpos($line, '#') === 0){ continue; }
            else if (strpos($line, '//') === 0){ continue; }
            // If this is not a variable line, skip it (ie. @foo = bar)
            // ie. void(0.1,-3.5) => void, 0.1, 13,5
            else if (strpos($line, '@') !== 0){ continue; }
            else if (!strstr($line, '=')){ continue; }
            // Otherwise parse the value of the variable and add it to the data
            $line = preg_replace('/\s+\=\s+/i', '=', trim($line, '@ '));
            list($name, $value) = explode('=', $line, 2);
            if (strstr($name, '[') && strstr($name, ']')){
                $key = substr($name, strpos($name, '[') + 1, -1);
                $name = substr($name, 0, strpos($name, '['));
                if (!isset($sheet_data_vars[$name])){ $sheet_data_vars[$name] = array(); }
                if ($key === ''){ $sheet_data_vars[$name][] = $value; }
                else { $sheet_data_vars[$name][$key] = $value; }
                } else {
                $sheet_data_vars[$name] = $value;
                }
        }
        //error_log('$sheet_data_vars = '.print_r($sheet_data_vars, true));
        // Review and process the sheet layer data
        $sheet_tiles_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntax: name(key,x,y) ie. void(0,20,20) => name:void, key:0, x:20, y:20
        $sheet_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)(,[-_a-z0-9,]+)?\)$/i'; // syntax: name(x,y[,flag1,flag2,etc.]) ie. spawn(4,4) or spawn(4,4,other-area-2) => name:spawn, x:4, y:4
        $sheet_listval_custval_regex = '/^([.a-z0-9-_]+)\(([,a-z0-9-_]+)\)/i'; // syntax: name(token1,token2,token3) ie. spawn(token1,token2,token3) => name:spawn, tokens:token1,token2,token3
        //$sheet_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntax: name(x,y) ie. spawn(4,4) => name:spawn, x:4, y:4
        static $sheet_custval_parser;
        if (!$sheet_custval_parser){
            $sheet_custval_parser = function($raw_tiles, $include_keys = false)
                use ($sheet_tiles_custval_regex, $sheet_other_custval_regex, $sheet_listval_custval_regex){
                if (empty($raw_tiles)){ return array(); }
                $parsed_keys = array();
                $parsed_tiles = array();
                foreach ($raw_tiles AS $line){
                    $line = trim(str_replace(' ', '', $line));
                    if (empty($line)){ continue; }
                    $is_tile_custval = preg_match($sheet_tiles_custval_regex, $line);
                    $is_other_custval = preg_match($sheet_other_custval_regex, $line);
                    $is_listval_custval = preg_match($sheet_listval_custval_regex, $line);
                    if (!$is_tile_custval && !$is_other_custval && !$is_listval_custval){ continue; }
                    if ($is_tile_custval){
                        $exploded = explode('/', preg_replace($sheet_tiles_custval_regex, '$1/$2/$3/$4', $line), 4);
                        //error_log('tile $exploded ='.print_r($exploded, true));
                        list($name, $k, $x, $y) = $exploded;
                        $parsed_tiles[$name] = array($x, $y);
                        $parsed_keys[intval($k)] = $name;
                        continue;
                        }
                    if ($is_other_custval){
                        $exploded = explode('/', preg_replace($sheet_other_custval_regex, '$1/$2/$3/$4', $line), 4);
                        //error_log('other $exploded ='.print_r($exploded, true));
                        list($name, $x, $y) = $exploded;
                        $parsed_tiles[$name] = array($x, $y);
                        if (!empty($exploded[3])){ $parsed_tiles[$name] = array_merge($parsed_tiles[$name], explode(',', trim($exploded[3], ','))); }
                        continue;
                        }
                    if ($is_listval_custval){
                        $exploded = explode('/', preg_replace($sheet_listval_custval_regex, '$1/$2', $line), 2);
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
        //error_log('raw $sheet_data_vars(before) = '.print_r($sheet_data_vars, true));
        $sheet_data_vars['world'] = isset($sheet_data_vars['world']) ? $sheet_data_vars['world'] : '';
        $sheet_data_vars['token'] = isset($sheet_data_vars['token']) ? $sheet_data_vars['token'] : '';
        $sheet_data_vars['name'] = isset($sheet_data_vars['name']) ? $sheet_data_vars['name'] : '';
        $sheet_data_vars['size'] = isset($sheet_data_vars['size']) ? $sheet_data_vars['size'] : '';
        $sheet_data_vars['image'] = isset($sheet_data_vars['image']) ? $sheet_data_vars['image'] : '';
        $sheet_data_vars['tiles'] = isset($sheet_data_vars['tiles']) ? $sheet_data_vars['tiles'] : array();
        $sheet_data_vars['sprites'] = isset($sheet_data_vars['sprites']) ? $sheet_data_vars['sprites'] : array();
        if (empty($sheet_data_vars['world'])){ $sheet_data_vars['world'] = $world_token; }
        if (empty($sheet_data_vars['token'])){ $sheet_data_vars['token'] = $sheet_token; }
        if (empty($sheet_data_vars['name'])){ $sheet_data_vars['name'] = 'Undefined'; }
        if (empty($sheet_data_vars['size'])){ $sheet_data_vars['size'] = '0 x 0'; }
        if (empty($sheet_data_vars['image'])){ $sheet_data_vars['image'] = 'undefined.png'; }
        if (!empty($sheet_data_vars['size'])){ $sheet_data_vars['size'] = explode('x', str_replace(' ', '', $sheet_data_vars['size'])); }
        if (!isset($sheet_data_vars['size'][0])){ $sheet_data_vars['size'][0] = $sheet_tilesize; }
        if (!isset($sheet_data_vars['size'][1])){ $sheet_data_vars['size'][1] = $sheet_tilesize; }
        if (empty($sheet_data_vars['tiles'])){ $sheet_data_vars['tiles'][] = ''; }
        if (empty($sheet_data_vars['sprites'])){ $sheet_data_vars['sprites'][] = ''; }
        $sheet_data_vars['tiles'] = $sheet_custval_parser($sheet_data_vars['tiles'], true);
        $sheet_data_vars['sprites'] = $sheet_custval_parser($sheet_data_vars['sprites']);
        // Add collected data to the parsed sheet data
        $sheet_data_parsed = array();
        $sheet_data_parsed['world'] = $sheet_data_vars['world']; unset($sheet_data_vars['world']);
        $sheet_data_parsed['token'] = $sheet_data_vars['token']; unset($sheet_data_vars['token']);
        $sheet_data_parsed['name'] = $sheet_data_vars['name']; unset($sheet_data_vars['name']);
        $sheet_data_parsed['size'] = $sheet_data_vars['size']; unset($sheet_data_vars['size']);
        $sheet_data_parsed['image'] = $sheet_data_vars['image']; unset($sheet_data_vars['image']);
        $sheet_data_parsed['tiles'] = $sheet_data_vars['tiles']; unset($sheet_data_vars['tiles']);
        $sheet_data_parsed['sprites'] = $sheet_data_vars['sprites']; unset($sheet_data_vars['sprites']);
        if (!empty($sheet_data_vars)){ $sheet_data_parsed['vars'] = $sheet_data_vars; }
        //error_log('$sheet_data_parsed = '.print_r($sheet_data_parsed, true));
        return $sheet_data_parsed;
    }

    // Define a function for returning all the available cells that encounters can appear on for a given map
    public static function get_map_encounter_cells($map_data){
        //error_log('rpg_world::get_map_encounter_cells() called!');
        // First we gather the map col and row size so we can generate all possible positions
        $map_col_size = isset($map_data['size'][0]) ? $map_data['size'][0] : self::$worldmap_mapsize;
        $map_row_size = isset($map_data['size'][1]) ? $map_data['size'][1] : self::$worldmap_mapsize;
        $available_cells = array();
        for ($row = 1; $row <= $map_row_size; $row++){
            for ($col = 1; $col <= $map_col_size; $col++){
                $pos = $col.'-'.$row;
                $available_cells[$pos] = true;
            }
        }
        //error_log('$map_col_size = '.print_r($map_col_size, true));
        //error_log('$map_row_size = '.print_r($map_row_size, true));
        //error_log('$available_cells('.count($available_cells).') = ['.implode(', ', array_keys($available_cells)).']');
        // Let let's loop through events and remove spaces that have events on them
        if (!empty($map_data['events']) && is_array($map_data['events'])){
            foreach ($map_data['events'] AS $event_name => $event_data){
                if (empty($event_data) || !is_array($event_data)){ continue; }
                $pos = $event_data[0];
                //error_log('-> removing event position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // Now let's loop through portals and remove spaces that have portals on them
        if (!empty($map_data['portals']) && is_array($map_data['portals'])){
            foreach ($map_data['portals'] AS $portal_name => $portal_data){
                if (empty($portal_data) || !is_array($portal_data)){ continue; }
                $pos = $portal_data[0];
                //error_log('-> removing portal position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // Now let's loop through buttons and remove spaces that have buttons on them
        if (!empty($map_data['buttons']) && is_array($map_data['buttons'])){
            foreach ($map_data['buttons'] AS $button_name => $button_data){
                if (empty($button_data) || !is_array($button_data)){ continue; }
                $pos = $button_data[0];
                //error_log('-> removing button position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // Now let's loop through static bosses and make sure those aren't used either
        if (!empty($map_data['bosses']) && is_array($map_data['bosses'])){
            foreach ($map_data['bosses'] AS $boss_name => $boss_data){
                if (empty($boss_data) || !is_array($boss_data)){ continue; }
                $pos = $boss_data[0];
                //error_log('-> removing boss position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // If there's a group defined called "no-encounters", loop through the cells and add them too
        if (!empty($map_data['groups']) && is_array($map_data['groups']) && isset($map_data['groups']['no-encounters'])){
            $no_encounters = $map_data['groups']['no-encounters'];
            //error_log('-> no-encounters group was defined! | $no_encounters = '.print_r($no_encounters, true));
            if (!empty($no_encounters) && is_array($no_encounters)){
                $no_encounters_groups = array();
                foreach ($no_encounters AS $key => $pos){
                    if (preg_match('/^([0-9]+)\-([0-9]+)$/i', $pos)){ continue; }
                    //error_log('-> no-encounters position "'.$pos.'" might be group...');
                    if (!isset($map_data['groups'][$pos])){ continue; }
                    elseif (empty($map_data['groups'][$pos])){ continue; }
                    //error_log('-> ... no-encounters position "'.$pos.'" IS a group w/ '.count($map_data['groups'][$pos]).' items!');
                    $no_encounters_groups = array_merge($no_encounters_groups, $map_data['groups'][$pos]);
                    unset($no_encounters[$key]);
                }
                //error_log('-> no-$no_encounters_groups = '.print_r($no_encounters_groups, true));
                $no_encounters = array_merge($no_encounters, $no_encounters_groups);
                foreach ($no_encounters AS $pos){
                    //error_log('-> removing no-encounters position "'.$pos.'" from available cells');
                    unset($available_cells[$pos]);
                }
            }
        }
        // Then we through all the tiles and remove any that are unwalkable "void" type
        $by_terrain = array();
        if (!empty($map_data['layers'])){
            $tileLayers = $map_data['layers'];
            //error_log('-> $tileLayers = '.print_r($tileLayers, true));
            foreach ($tileLayers AS $layer_key => $layer_tiles){
                //error_log('-> checking layer #'.$layer_key.' $tileLayers ...');
                //error_log('-> found '.count($layer_tiles).' rows in $layer_tiles ...');
                foreach ($layer_tiles AS $row_key => $row_tiles){
                    //error_log('-> checking $tileLayers['.$layer_key.']['.$row_key.'] ...');
                    //error_log('-> $tileLayers['.$layer_key.']['.$row_key.'] = '.print_r($row_tiles, true));
                    $row_tiles = explode(',', $row_tiles);
                    foreach ($row_tiles AS $col_key => $tile_token){
                        $pos = ($col_key + 1).'-'.($row_key + 1);
                        if (!isset($available_cells[$pos])){ continue; }
                        // If this tile is a "void" type, remove it from the available cells
                        if ($tile_token === 'void' || strpos($tile_token, 'void') === 0){
                            //error_log('-> removing tile position "'.$pos.'" from available cells (tile: '.$tile_token.')');
                            unset($available_cells[$pos]);
                        }
                        // Otherwise we should add it to the appropriate array in the by_terrain list
                        else {
                            list($tile_token_clean) = strstr($tile_token, '-') ? explode('-', $tile_token) : array($tile_token);
                            //error_log('-> adding tile position "'.$pos.'" to by_terrain["'.$tile_token_clean.'"] (tile: '.$tile_token.')');
                            if (!isset($by_terrain[$tile_token_clean])){ $by_terrain[$tile_token_clean] = array(); }
                            $by_terrain[$tile_token_clean][] = $pos;
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

    // Define a function for generating a bunch of world map encounters given parsed map data and some config
    public static function generate_worldmap_encounters($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::generate_worldmap_encounters() called!');

        // Collect any indexes we're gonna need for this part
        $mmrpg_index_robots = self::get_index('robots');
        $mmrpg_index_fields = self::get_index('fields');

        // Collect the map's field token and mecha encounters
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $world_battle_token = 'world-battle_'.str_replace('__', '-', $world_map_token);
        $map_name = !empty($map_data_parsed['name']) ? $map_data_parsed['name'] : 'Undefined';
        $map_level = !empty($map_data_parsed['level']) ? $map_data_parsed['level'] : 1;
        $map_encounters = !empty($map_data_parsed['encounters']) ? $map_data_parsed['encounters'] : array();
        $map_habitats = !empty($map_data_parsed['habitats']) ? $map_data_parsed['habitats'] : array();
        $map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
        $map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
        $map_field_background = !empty($map_field_info['field_background']) ? $map_field_info['field_background'] : 'field';
        $map_field_foreground = !empty($map_field_info['field_foreground']) ? $map_field_info['field_foreground'] : 'field';
        $map_field_music = !empty($map_field_info['field_music']) ? $map_field_info['field_music'] : 'misc/star-force'; // TODO: find a better default for this
        $map_field_mechas = !empty($map_field_info['field_mechas']) ? $map_field_info['field_mechas'] : array();
        //error_log('$map_encounters = '.print_r($map_encounters, true));
        //error_log('$map_habitats = '.print_r($map_habitats, true));
        //error_log('$map_field_token = '.print_r($map_field_token, true));
        //error_log('$map_field_info = '.print_r($map_field_info, true));
        //error_log('$map_field_background = '.print_r($map_field_background, true));
        //error_log('$map_field_foreground = '.print_r($map_field_foreground, true));
        //error_log('$map_field_music = '.print_r($map_field_music, true));

        // Define a quick matrix for the turns and zenny values per class
        $rewards_matrix = array(
            'mecha' => array('turns' => MMRPG_SETTINGS_BATTLETURNS_PERMECHA, 'zenny' => MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2),
            'master' => array('turns' => MMRPG_SETTINGS_BATTLETURNS_PERROBOT, 'zenny' => MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL),
            'boss' => array('turns' => MMRPG_SETTINGS_BATTLETURNS_PERBOSS, 'zenny' => MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL0)
            );

        // Define a quick matrix for the level-range values per class
        //$robot_level = mt_rand(ceil($map_level * 1.2), ceil($map_level * 1.4));
        $levels_matrix = array(
            'mecha' => array('min' => ceil($map_level * 0.6), 'max' => ceil($map_level * 0.8)),
            'master' => array('min' => ceil($map_level * 0.9), 'max' => ceil($map_level * 1.1)),
            'boss' => array('min' => ceil($map_level * 1.2), 'max' => ceil($map_level * 1.4))
            );

        // Calculate the available encounter cells based on the map data and define a var to hold used encounter cells later
        $world_map_encounters = array();
        $available_encounter_cells = self::get_map_encounter_cells($map_data_parsed);
        $available_encounter_terrain = !empty($map_data_parsed['terrain']) ? $map_data_parsed['terrain'] : array();
        $used_encounter_cells = array();

        // RANDOM ENCOUNTERS (within defined limits)
        $allowed_random_encounters = $map_encounters;
        $max_random_encounters = ceil($available_encounter_cells['total'] * 0.20); // TODO: make this configurable in the map file
        $allowed_held_items = array();
        if ($map_level >= 10){ $allowed_held_items += array('energy-pellet', 'weapon-pellet'); }
        if ($map_level >= 20){ $allowed_held_items += array('attack-pellet', 'defense-pellet', 'speed-pellet'); }
        if ($map_level >= 30){ $allowed_held_items += array('energy-capsule', 'weapon-capsule'); }
        if ($map_level >= 40){ $allowed_held_items += array('attack-capsule', 'defense-capsule', 'speed-capsule'); }
        if ($map_level >= 50){ $allowed_held_items += array('energy-tank', 'weapon-tank'); }
        if ($map_level >= 100){ $allowed_held_items += array('extra-life', 'yashichi'); }
        $get_random_allowed_item = function() use ($allowed_held_items){
            if (empty($allowed_held_items)){ return ''; }
            return $allowed_held_items[mt_rand(0, count($allowed_held_items) - 1)];
            };
        //error_log('$allowed_random_encounters = '.print_r($allowed_random_encounters, true));
        //error_log('$available_encounter_terrain = '.print_r($available_encounter_terrain, true));
        //error_log('$available_encounter_cells = '.print_r($available_encounter_cells, true));
        //error_log('$max_random_encounters = '.print_r($max_random_encounters, true));
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
        $robot_token = '';
        for ($robot_key = 0; $robot_key < $max_random_encounters; $robot_key++){
            if (empty($options)){ $options = array_keys($distributed_encounters); }
            if (empty($robot_token)){ $robot_token = array_shift($options); }
            if (!isset($generated_encounters[$robot_token])){ $generated_encounters[$robot_token] = 0; }
            //error_log(PHP_EOL.'-> next robot = "'.$robot_token.'"');
            $habitats = !empty($map_habitats[$robot_token]) ? $map_habitats[$robot_token] : '';
            //error_log('-> getting random position for robot "'.$robot_token.'" (habitats: '.print_r(implode(',', $habitats), true).')');
            $available = array();
            if (!empty($habitats)){
                $by_terrain = $available_encounter_cells['by_terrain'];
                foreach ($by_terrain AS $terrain => $cells){
                    if (!in_array($terrain, $habitats)){ continue; }
                    $available = array_merge($available, $cells);
                    }
                }
            if (empty($available)){ $available = $available_encounter_cells['all']; }
            //error_log('$available = '.print_r($available, true));
            //error_log('$available(count) = '.count($available).' vs. $used_encounter_cells(count) = '.count($used_encounter_cells));
            $robot_pos = self::get_rand_pos($available, $used_encounter_cells);
            if (!$robot_pos){ continue; } // means there's no more room for this type!!!
            $robot_pos_terrain = self::get_map_position_terrain($robot_pos, $map_data_parsed);
            //error_log('$robot_pos = '.print_r($robot_pos, true));
            //error_log('$robot_pos_terrain = '.print_r($robot_pos_terrain, true));
            //error_log('$battle_background = '.print_r($battle_background, true));
            //error_log('$battle_foreground = '.print_r($battle_foreground, true));
            $robot_info = $mmrpg_index_robots[$robot_token];
            $robot_class = $robot_info['robot_class'];
            $robot_level = mt_rand($levels_matrix[$robot_class]['min'], $levels_matrix[$robot_class]['max']);
            $robot_item = mt_rand(1, 100) <= 50 ? $get_random_allowed_item() : '';
            $robot_label = $robot_info['robot_name'].' (Lv. '.$robot_level.')';
            $battle_token = $world_battle_token.'_random-robot-'.($robot_key + 1);
            $battle_name = $map_name.' '.ucfirst($robot_class).' Battle';
            $battle_description = 'This is a debug '.$robot_class.' battle.  It is casual fun.';
            $battle_background = $map_field_token;
            $battle_foreground = !empty($available_encounter_terrain[$robot_pos_terrain]) ? $available_encounter_terrain[$robot_pos_terrain][0] : $map_field_token;
            $battle_field = $battle_background !== $battle_foreground ? $battle_background.'/'.$battle_foreground : $battle_background;
            $world_map_encounters[] = array('robot/'.$robot_class, $robot_token, '', $robot_pos, $battle_token, $robot_label);
            $battle_omega = rpg_mission::generate_mission($this_prototype_data, $battle_token, array(
                'token' => $battle_token,
                'name' => $battle_name,
                'description' => $battle_description,
                'turns' => $rewards_matrix[$robot_class]['turns'],
                'zenny' => $rewards_matrix[$robot_class]['zenny'],
                'field' => $battle_field,
                'target' => array('robots' => array(array(
                    'token' => $robot_token,
                    'level' => $robot_level,
                    'item' => $robot_item,
                    ))),
                'flags' => array(
                    'world_battle' => true,
                    'remove_on_complete' => true
                    ),
                ), true);
            $generated_encounters[$robot_token]++;
            $distributed_encounters[$robot_token]--;
            if (empty($distributed_encounters[$robot_token])){ $robot_token = ''; }
        }
        //error_log'$generated_encounters = '.print_r($generated_encounters, true).PHP_EOL);
        //echo('</pre>'.PHP_EOL);
        //exit();

        // STATIC ENCOUNTERS (w/ Mechas, Masters, Bosses)
        $static_encounter_key = 0;
        $static_encounter_types = array('mecha' => 'mechas', 'master' => 'masters', 'boss' => 'bosses');
        foreach ($static_encounter_types AS $encounter_class => $encounter_xclass){
            if (!empty($map_data_parsed[$encounter_xclass])){
                $static_encounters = $map_data_parsed[$encounter_xclass];
                //error_log('$static_encounters = '.print_r($static_encounters, true));
                foreach ($static_encounters AS $key => $robot_data){
                    //error_log('-> next $robot_key = '.print_r($robot_key, true));
                    //error_log('-> next $robot_data = '.print_r($robot_data, true));
                    //error_log('-> next $robot_key = '.$robot_key.PHP_EOL.'---> w/ $robot_data = '.print_r($robot_data, true));
                    $robot_key = $static_encounter_key++;
                    $robot_pos = $robot_data[0]; unset($robot_data[0]);
                    $robot_token = !empty($robot_data[1]) ? $robot_data[1] : 'robot'; unset($robot_data[1]);
                    $form = !empty($robot_data[2]) ? $robot_data[2] : ''; unset($robot_data[2]);
                    $effect = !empty($robot_data[3]) ? $robot_data[3] : ''; unset($robot_data[3]);
                    $target = !empty($robot_data[4]) ? $robot_data[4] : ''; unset($robot_data[4]);
                    $value = !empty($robot_data[5]) ? $robot_data[5] : ''; unset($robot_data[5]);
                    //error_log('-> $robot_pos = '.print_r($robot_pos, true));
                    //error_log('-> $robot_token = '.print_r($robot_token, true));
                    //error_log('-> $form = '.print_r($form, true));
                    //error_log('-> $effect = '.print_r($effect, true));
                    //error_log('-> $target = '.print_r($target, true));
                    //error_log('-> $value = '.print_r($value, true));
                    //error_log('-> next '.$encounter_class.' = "'.$robot_token.'" (key: '.$robot_key.')');
                    $robot_pos_terrain = rpg_world::get_map_position_terrain($robot_pos, $map_data_parsed);
                    $robot_info = $mmrpg_index_robots[$robot_token];
                    $robot_class = $robot_info['robot_class'];
                    //error_log('-> $robot_info = '.print_r($robot_info, true));
                    $robot_level = mt_rand($levels_matrix[$robot_class]['min'], $levels_matrix[$robot_class]['max']);
                    $robot_item = mt_rand(1, 100) <= 50 ? $get_random_allowed_item() : '';
                    $robot_label = $robot_info['robot_name'].' (Lv. '.$robot_level.')';
                    $battle_token = $world_battle_token.'_static-'.$robot_class.'-'.($robot_key + 1);
                    $battle_name = $map_name.' '.ucfirst($robot_class).' Battle';
                    $battle_background = $map_field_token;
                    $battle_foreground = !empty($available_encounter_terrain[$robot_pos_terrain]) ? $available_encounter_terrain[$robot_pos_terrain][0] : $map_field_token;
                    $battle_field = $battle_background !== $battle_foreground ? $battle_background.'/'.$battle_foreground : $battle_background;
                    $world_map_encounters[] = array('robot/'.$robot_class, $robot_token, '', $robot_pos, $battle_token, $robot_label);
                    $battle_rewards = array();
                    if ($robot_class === 'master'
                        && !mmrpg_prototype_robot_unlocked(false, $robot_token)){
                        //error_log('-> '.$robot_token.' is a master that is not unlocked yet!');
                        if (!isset($battle_rewards['robots'])){ $battle_rewards['robots'] = array(); }
                        $battle_rewards['robots'][] = array('token' => $robot_token, 'level' => $robot_level, 'experience' => 999);
                        }
                    //error_log('-> generating '.$robot_class.' battle "'.$battle_token.'" ('.$battle_name.')');
                    $battle_omega = rpg_mission::generate_mission($this_prototype_data, $battle_token, array(
                        'token' => $battle_token,
                        'name' => $battle_name,
                        'description' => 'This is a debug '.$robot_class.' battle.  It is very serious.',
                        'field' => $battle_field,
                        'turns' => $rewards_matrix[$robot_class]['turns'],
                        'zenny' => $rewards_matrix[$robot_class]['zenny'],
                        'target' => array('robots' => array(array(
                            'token' => $robot_token,
                            'level' => $robot_level,
                            'item' => $robot_item,
                            ))),
                        'flags' => array('world_battle' => true, 'remove_on_complete' => true),
                        'rewards' => $battle_rewards,
                        ), true);
                    //error_log('-> $battle_omega = '.print_r($battle_omega, true));
                }
            }
        }

        // Return the generated encounters array
        return $world_map_encounters;
    }

    // Define a function for getting the terrain type for a given position on the map, but do not limit only to available tiles
    public static function get_map_position_terrain($position, $map_data_parsed){
        //error_log('rpg_world::get_map_position_terrain() called for position "'.$position.'"');
        if (empty($position) || !is_string($position) || !isset($map_data_parsed['tiles']) || !isset($map_data_parsed['layers'])){ return 'unknown'; }
        // Loop through the tiles and find the one that matches this position
        foreach ($map_data_parsed['layers'] AS $layer_key => $layer_tiles){
            foreach ($layer_tiles AS $row_key => $row_tiles){
                $row_tiles = str_replace(array('[', ']'), '', $row_tiles);
                $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
                foreach ($row_tiles AS $col_key => $tile_token){
                    $pos = ($col_key + 1).'-'.($row_key + 1);
                    if ($pos === $position){ return explode('-', $tile_token)[0]; }
                }
            }
        }
        return 'unknown';
    }

    // Define a quick function for translating singular kinds to plural kinds
    // TODO:  Find the class method that already does this if exists, else create
    public static function get_xkind($kind){
        //error_log('rpg_world::get_xkind() called for "'.$kind.'"');
        if (!$kind){ return false; }
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
    }

    // Define a quick function for getting a random position on a given grid
    public static function get_rand_pos($available_encounter_cells, &$used = array()){
        //error_log('rpg_world::get_rand_pos() called!');
        //static $used;
        //if (!$used){ $used = array(); }
        if (!is_array($used)){ $used = array(); }
        $available = array_values(array_diff($available_encounter_cells, $used));
        if (empty($available)){ return false; }
        $pos = $available[mt_rand(0, count($available) - 1)];
        $used[] = $pos;
        return $pos;
    }

    // Define a reusable function for grabbing the markup for a given character sprite (player or robot)
    public static function get_sprite($kind, $token, $alt = '', $dir = 'left', $class = '', $styles = '', $attrs = ''){
        //error_log('rpg_world::get_sprite() called for "'.$kind.'" with token "'.$token.'"');
        $mmrpg_indexes = self::$mmrpg_indexes;
        $xkind = self::get_xkind($kind);
        if (empty($mmrpg_indexes)){ error_log('error: $mmrpg_indexes does not exist!'); return false; }
        elseif (empty($mmrpg_indexes[$xkind])){ error_log('error: $mmrpg_indexes['.$xkind.'] does not exist!'); return false; }
        elseif (empty($mmrpg_indexes[$xkind][$token])){ error_log('error: $mmrpg_indexes['.$xkind.']['.$token.'] does not exist!'); return false; }
        $info = $mmrpg_indexes[$xkind][$token];
        $anim = 0;
        $anim_styles1 = '';
        $anim_styles2 = '';
        $anim_attrs = '';
        if ($kind === 'player'){ $anim = rpg_player::get_css_animation_duration($info); }
        elseif ($kind === 'robot'){ $anim = rpg_robot::get_css_animation_duration($info); }
        if (!empty($anim)){
            $anim_styles1 .= ' --sprite-speed: '.$anim.';';
            $anim_styles2 .= ' animation-delay: -'.(mt_rand(1, 100) / 100).'s;';
            }
        //error_log('$info = '.print_r($info, true));
        //error_log('$anim = '.print_r($anim, true));
        $dir = $dir;
        $img_dir = $dir; //'right';
        $img = $info[$kind.'_image'];
        $size = $info[$kind.'_image_size'];
        $xsize = $size. 'x'.$size;
        $styles .= $anim_styles1.$anim_styles2;
        $attrs .= $anim_attrs;
        // old format: images/robots/frosty-throwman/sprite_right_40x40.png
        //$sprite_path = 'images/'.$xkind.'/'.$img.($alt ? '_'.$alt : '').'/sprite_'.$img_dir.'_'.$xsize.'.png';
        // new format: images/robots/all/token:frosty-throwman+crop:false+dir:both/sprite_left_40x40.png
        $sprite_path = 'images/'.$xkind.'/all/token:'.$img.($alt ? '+alt:'.$alt : '').'+dir:both+zoom:true+crop:false/sprite_left_'.$xsize.'.png';
        $sprite_class = 'sprite '.$kind.($class ? ' '.$class : '');
        $sprite_styles = ($styles ? ' style="'.$styles.'"' : '');
        $sprite_attrs = ' data-sprite="'.$kind.'" data-token="'.$img.'" data-size="'.$size.'" data-dir="'.$dir.'" data-frame="00" '.($attrs ? ' '.$attrs : '');
        $sprite_markup = '';
        $sprite_markup .= '<span class="'.$sprite_class.'"'.$sprite_attrs.$sprite_styles.'>';
            $sprite_markup .= '<span class="wrap">';
                $sprite_markup .= '<span class="sprite" style="background-image: url('.$sprite_path.');"></span>';
            $sprite_markup .= '</span>';
        $sprite_markup .= '</span>';
        return($sprite_markup);
    }

    // Define a function for getting the cursor sprite specifically (which has it's own rules)
    public static function get_cursor_sprite($dir = '', $class = '', $styles = '', $attrs = ''){
        //error_log('rpg_world::get_cursor_sprite() called for dir "'.$dir.'"');
        $cursor_sprite = self::get_sprite('robot', 'pointan', '', $dir, $class, $styles, $attrs);
        //$cursor_sprite = str_replace('images/robots/pointan/sprite_', 'images/assets/cursor_', $cursor_sprite);
        $cursor_sprite = str_replace('pointan', 'cursor', $cursor_sprite);
        //$cursor_sprite = preg_replace('/background-image: url\(([^\(\)]+)\);/i', 'background-image: url(images/assets/cursor_40x40.png);', $cursor_sprite);
        $cursor_sprite = preg_replace('/background-image: url\(([^\(\)]+)\);/i', 'background-image: url(images/assets/cursor_80x80.png);', $cursor_sprite);
        return $cursor_sprite;
    }

    // Define a function for getting the battle history for a given player token
    public static function get_battle_history($player_token = '', $record_token = ''){
        //error_log('rpg_world::get_battle_history() called for player "'.$player_token.'" and record "'.$record_token.'"');
        $session_token = rpg_game::session_token();
        $this_battle_history = !empty($_SESSION[$session_token]['values']['battle_history']) ? $_SESSION[$session_token]['values']['battle_history'] : array();
        if (empty($player_token)){ return $this_battle_history; }
        $player_battle_history = !empty($this_battle_history[$player_token]) ? $this_battle_history[$player_token] : array();
        if (empty($record_token)){ return $player_battle_history; }
        $battle_history_record = !empty($player_battle_history[$record_token]) ? $player_battle_history[$record_token] : array();
        return $battle_history_record;
    }

    // Define a function for getting the player switcher markup given current conditions
    public static function get_player_switcher_markup($this_prototype_data, $player_tokens){
        //error_log('rpg_world::get_player_switcher_markup() called!');
        $return_markup = '';
        $get_label_span = function($name, $kind){ return ('<span class="label">'.$name.' ('.ucfirst($kind).')</span>'); };
        $cursor_token = 'player';
        $cursor_active = $this_prototype_data['this_player_token'] === $cursor_token ? true : false;
        //$cursor_sprite = self::get_sprite('robot', 'pointan', '', 'right', 'cursor');
        //$cursor_sprite = str_replace('images/robots/pointan/sprite_', 'images/assets/cursor_', $cursor_sprite);
        //$cursor_sprite = preg_replace('/background-image: url\(([^\(\)]+)\);/i', 'background-image: url(images/assets/cursor_40x40.png);', $cursor_sprite);
        $cursor_sprite = self::get_cursor_sprite('right', 'cursor');
        $cursor_label = $get_label_span('Prε', 'cursor');
        $cursor_types = 'type explode';
        //$return_markup .= ('<a class="option'.($cursor_active ? ' active' : '').'" data-player="'.$cursor_token.'">'.$cursor_sprite.$cursor_label.'</a>');
        $mmrpg_index_players = self::get_index('players');
        foreach ($player_tokens AS $player_key => $player_token){
            if ($player_token === 'player' || empty($mmrpg_index_players[$player_token])){ continue; }
            $player_info = $mmrpg_index_players[$player_token];
            $player_active = $player_token === $this_prototype_data['this_player_token'] ? true : false;
            $player_sprite = self::get_sprite('player', $player_token, '', 'right', 'character', '');
            $player_label = $get_label_span($player_info['player_name'], 'player');
            $player_types = 'type '.$player_info['player_type'];
            $markup_class = 'team-player '.$player_types.($player_active ? ' active' : '');
            $markup_attrs = !$player_active ? ' data-player="'.$player_token.'"' : '';
            $return_markup .= ('<a class="'.$markup_class.'"'.$markup_attrs.'>'.$player_sprite.$cursor_sprite.$player_label.'</a>');
        }
        $return_markup .= ('<a class="team-player '.$cursor_types.($cursor_active ? ' active' : '').'" data-player="'.$cursor_token.'">'.$cursor_sprite.$cursor_label.'</a>');
        return $return_markup;
    }

    // Define a function for getting the robot switcher markup given current conditions
    public static function get_robots_overview_markup($this_prototype_data, $robot_tokens){
        //error_log('rpg_world::get_robot_overview_markup() called!');
        if (empty($this_prototype_data) || empty($robot_tokens)){ return ''; }
        if ($this_prototype_data['this_player_token'] === 'player'){ return ''; }
        $return_markup = '';
        $WORLD_SESSION = self::get_session();
        $WORLD_ROBOT_SESSIONS = &$WORLD_SESSION['robot_sessions'];
        $mmrpg_index_robots = self::get_index('robots');
        $current_player_token = $this_prototype_data['this_player_token'];
        $player_starforce = rpg_game::starforce_unlocked();
        $limit_hearts = mmrpg_prototype_limit_hearts_earned($current_player_token);
        $get_rating_token = function($percent){
            if ($percent === 100){ return 'full'; }
            elseif ($percent >= 50){ return 'high'; }
            elseif ($percent >= 20){ return 'med'; }
            elseif ($percent >= 1){ return 'low'; }
            else { return 'no'; }
            };
        $get_robot_energy_frame = function($rating){
            if ($rating === 'full'){ return '10'; } // base2
            elseif ($rating === 'high'){ return '01'; } // taunt
            //elseif ($rating === 'med'){ return '08'; } // defend
            elseif ($rating === 'med'){ return '00'; } // base
            //elseif ($rating === 'low'){ return '09'; } // damage
            elseif ($rating === 'low'){ return '08'; } // defend
            else { return '03'; } // defeat
            };
        //$robot_tokens_reversed = array_reverse($robot_tokens);
        $return_markup .= '<a class="team-rotate" title="Rotate Team"><i class="fa fas fa-sync"></i></a>';
        $return_markup .= '<div class="limit-hearts" title="x'.$limit_hearts.' Limit Hearts">';
            $return_markup .= '<i class="player '.$current_player_token.'"></i>';
            $return_markup .= str_repeat('<i class="heart fa fas fa-heart"></i>', $limit_hearts);
        $return_markup .= '</div>';
        $return_markup .= '<div class="team-robots">';
        foreach ($robot_tokens AS $robot_key => $robot_token){
            if ($robot_token === 'robot' || empty($mmrpg_index_robots[$robot_token])){ continue; }
            $robot_index_info = $mmrpg_index_robots[$robot_token];
            $robot_info = $robot_index_info;
            $robot_id = $robot_info['robot_id'];
            $robot_overview = self::get_player_robot_overview($current_player_token, $robot_token, $robot_id);
            $robot_name = $robot_overview['name'];
            $robot_level = $robot_overview['level'];
            $robot_core = $robot_overview['core'];
            $robot_core2 = $robot_overview['core2'];
            $robot_core_types = $robot_overview['coreTypes'];
            $robot_core_or_none = $robot_overview['coreElse'];
            $robot_item = $robot_overview['item'];
            $robot_image = $robot_overview['image'];
            $robot_sprite = self::get_sprite('robot', $robot_image, '', 'right', 'character', '');
            $robot_disabled = empty($robot_overview['energy']) ? true : false;
            $robot_energy = $robot_overview['energy'];
                $robot_energy_max = $robot_overview['energyMax'];
                $robot_energy_rating = $robot_overview['energyRating'];
                $robot_energy_percent = $robot_overview['energyPercent'];
            $robot_energy_label = $robot_energy.' / '.$robot_energy_max.' LE ('.$robot_energy_percent.'%)';
            $robot_energy_markup = '<div class="guage energy" title="'.$robot_energy_label.'"><i class="'.$robot_energy_rating.'" style="width: '.$robot_energy_percent.'%;"></i></div>';
            $robot_weapons = $robot_overview['weapons'];
                $robot_weapons_max = $robot_overview['weaponsMax'];
                $robot_weapons_percent = $robot_overview['weaponsPercent'];
                $robot_weapons_rating = $robot_overview['weaponsRating'];
            $robot_weapons_label = $robot_weapons.' / '.$robot_weapons_max.' WE ('.$robot_weapons_percent.'%)';
            $robot_weapons_markup = '<div class="guage weapons" title="'.$robot_weapons_label.'"><i class="'.$robot_weapons_rating.'" style="width: '.$robot_weapons_percent.'%;"></i></div>';
            $robot_frame = $get_robot_energy_frame($robot_energy_rating);
            $robot_sprite = str_replace('data-frame="00"', 'data-frame="'.$robot_frame.'"', $robot_sprite);
            $markup_class = 'team-robot'.($robot_disabled ? ' disabled' : '');
            $markup_attrs = 'data-robot="'.$robot_id.'_'.$robot_token.'" data-status="'.$robot_energy_rating.'-energy"';
            $robot_markup = '';
            $robot_markup .= '<div class="'.$markup_class.'" '.$markup_attrs.'>';
                $robot_markup .= '<div class="icon '.$robot_core_types.'">'.$robot_sprite.'</div>';
                $robot_markup .= '<div class="label">';
                    $robot_markup .= '<strong class="name">'.$robot_name.'</strong>';
                    $robot_markup .= '<span class="lvl type '.($robot_level >= 100 ? 'level' : 'none').'">Lv. '.$robot_level.'</span>';
                $robot_markup .= '</div>';
                $robot_markup .= $robot_energy_markup;
                $robot_markup .= $robot_weapons_markup;
            $robot_markup .= '</div>';
            $return_markup .= $robot_markup;
        }
        $return_markup .= '</div>';
        return $return_markup;
    }

    // Define a function for getting the BACKGROUND LAYER sprite markup for the world map
    public static function get_background_layer_sprite($this_prototype_data, $map_data_parsed, $preview_only = false){
        //error_log('rpg_world::get_background_layer_sprite() called!');
        // BACKGROUND LAYER
        $background_sprites = array();
        $mmrpg_index_fields = self::get_index('fields');
        $map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
        $map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
        $map_field_background = !empty($map_field_info['field_background']) ? $map_field_info['field_background'] : 'field';
        $map_field_foreground = !empty($map_field_info['field_foreground']) ? $map_field_info['field_foreground'] : 'field';
        $field_background_image = !$preview_only ? 'battle-field_background_base.gif' : 'battle-field_preview.png';
        $field_background_image_url = 'images/fields/'.$map_field_token.'/'.$field_background_image;
        $field_background_styles = 'top: 0; left: 0; background-image: url('.$field_background_image_url.');';
        $background_sprites[] = '<span data-sprite="background" class="sprite field background" style="'.$field_background_styles.'"></span>';
        return implode(PHP_EOL, $background_sprites);
    }

    // Define a function for getting the TERRAIN LAYER markup for the world map
    public static function get_terrain_layer_markup($this_prototype_data, $map_data_parsed, $map_layer_data){
        //error_log('rpg_world::get_terrain_layer_markup() called!');
        // TERRAIN LAYER
        $terrain_markup = array();
        $map_config = $map_data_parsed['config'];
        $map_row_size = $map_config['row_size'];
        $map_col_size = $map_config['col_size'];
        $map_pixel_width = $map_config['pixel_width'];
        $map_pixel_height = $map_config['pixel_height'];
        $tile_data = array();
        $tile_data['canvas_tiles'] = array();
        for ($row = 1; $row <= $map_row_size; $row++){
            $row_tiles = $map_layer_data[$row - 1];
            $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
            for ($col = 1; $col <= $map_col_size; $col++){
                $pos = $col.'-'.$row;
                $key = isset($row_tiles[$col - 1]) ? $row_tiles[$col - 1] : '';
                if (strstr($key, '[') || strstr($key, ']')){ $key = trim($key, '[]'); }
                $tile_data['canvas_tiles'][$pos] = $key;
            }
        }
        $tile_data_json = json_encode($tile_data, JSON_NUMERIC_CHECK);
        $terrain_markup[] = '<canvas data-canvas="terrain" width="'.$map_pixel_width.'" height="'.$map_pixel_height.'"></canvas>';
        $terrain_markup[] = '<script data-json="tileData" type="application/json">'.$tile_data_json.'</script>';
        return implode(PHP_EOL, $terrain_markup);
    }

    // Define a function for getting the EVENTS LAYER sprite markup for the world map
    public static function get_events_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_events_layer_sprites() called!');
        // EVENTS LAYER
        $map_config = $map_data_parsed['config'];
        $map_width = $map_config['pixel_width'];
        $map_height = $map_config['pixel_height'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_tilesize_offset = $map_config['tilesize_offset'];
        $this_player_token = $this_prototype_data['this_player_token'];
        $this_is_cursor = $this_player_token === 'player' ? true : false;
        $events_markup = array();
        $event_symbols = array();
        $events_index = array();
        if (!empty($map_data_parsed['events'])){
            $event_sprites = $map_data_parsed['events'];
            foreach ($event_sprites AS $event_name => $event_data){
                if (empty($event_data) || !is_array($event_data)){ continue; }
                $pos = $event_data[0]; unset($event_data[0]);
                $sprite = !empty($event_data[1]) ? $event_data[1] : ''; unset($event_data[1]);
                $filter = !empty($event_data[2]) ? $event_data[2] : ''; unset($event_data[2]);
                $action = !empty($event_data[3]) ? $event_data[3] : ''; unset($event_data[3]);
                //error_log('processing event "'.$event_name.'" with pos "'.$pos.'"'.PHP_EOL.'-> $sprite = "'.$sprite.'"'.PHP_EOL.'-> filter = "'.$filter.'"'.PHP_EOL.'-> $action = "'.$action.'"');
                list($col, $row) = explode('-', $pos);
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                $hidden = in_array('hidden', $event_data) ? true : false; unset($event_data[array_search('hidden', $event_data)]);
                $locked = in_array('locked', $event_data) ? true : false; unset($event_data[array_search('locked', $event_data)]);
                $data = array_values($event_data); // remaining vaules if any
                $label = 'World '.ucwords(str_replace('-', ' ', $event_name));
                $attrs = 'data-event="'.$event_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $classes = 'sprite tile event'.($sprite ? ' '.$sprite : '').(!$hidden && !$locked  ? ' animate' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $style = 'top: '.$top.'px; left: '.$left.'px;';
                $events_markup[] = '<span data-sprite="event" class="'.$classes.'" '.$attrs.' style="'.$style.'"></span>';
                $event_symbols[$pos] = $event_name;
                $events_index[$event_name] = array(
                    'pos' => $pos,
                    'sprite' => $sprite,
                    'filter' => $filter,
                    'action' => $action,
                    'col' => $col,
                    'row' => $row,
                    'label' => $label,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    'data' => $data,
                    );
            }
        }
        $event_symbols_json = json_encode($event_symbols, JSON_NUMERIC_CHECK);
        $events_index_json = json_encode($events_index, JSON_NUMERIC_CHECK);
        $events_markup[] = '<script data-json="eventSymbols" type="application/json">'.$event_symbols_json.'</script>';
        $events_markup[] = '<script data-json="eventsIndex" type="application/json">'.$events_index_json.'</script>';
        return implode(PHP_EOL, $events_markup);
    }

    // Define a function for getting the PORTALS LAYER sprite markup for the world map
    public static function get_portals_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_portals_layer_sprites() called!');
        // PORTALS LAYER
        $map_config = $map_data_parsed['config'];
        $map_width = $map_config['pixel_width'];
        $map_height = $map_config['pixel_height'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_tilesize_offset = $map_config['tilesize_offset'];
        //$map_perspective_matrix = self::get_perspective_matrix(array($map_width, $map_height));
        //error_log('$map_perspective_matrix = '.print_r($map_perspective_matrix, true));
        //error_log('$map_perspective_matrix = '.PHP_EOL.print_r(self::print_matrix($map_perspective_matrix), true));
        $this_player_token = $this_prototype_data['this_player_token'];
        $this_is_cursor = $this_player_token === 'player' ? true : false;
        $portals_markup = array();
        $portal_symbols = array();
        $portals_index = array();
        if (!empty($map_data_parsed['portals'])){
            $portal_sprites = $map_data_parsed['portals'];
            foreach ($portal_sprites AS $portal_name => $portal_data){
                if (empty($portal_data) || !is_array($portal_data)){ continue; }
                $pos = $portal_data[0];
                $dst = !empty($portal_data[1]) ? $portal_data[1] : '';
                list($col, $row) = explode('-', $pos);
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                //list($mod_top, $mod_left) = self::transform_point_with_matrix(array($left, $top), $map_perspective_matrix);
                //error_log('-> portal "'.$portal_name.'" at pos "'.$pos.'" (col: '.$col.', row: '.$row.')');
                //error_log('-> $top = '.$top.', $left = '.$left.' | $mod_top = '.$mod_top.', $mod_left = '.$mod_left);
                //$top = $mod_top; $left = $mod_left;
                $hidden = in_array('hidden', $portal_data) ? true : false;
                $locked = in_array('locked', $portal_data) ? true : false;
                if ($hidden && $portal_name === 'spawn'){ continue; }
                //if ($this_is_cursor && !$locked && $portal_name !== 'spawn'){ $locked = true; }
                $label = preg_match('/^goto__/i', $portal_name) ? strtoupper(preg_replace('/^goto__/i', '', $portal_name)) : ('World '.ucfirst($portal_name));
                $attrs = 'data-portal="'.$portal_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $classes = 'sprite tile portal'.($portal_name !== 'spawn' && !$hidden && !$locked  ? ' pulse' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $style = 'top: '.$top.'px; left: '.$left.'px;';
                $portals_markup[] = '<span data-sprite="portal" class="'.$classes.'" '.$attrs.' style="'.$style.'"></span>';
                $portal_symbols[$pos] = $portal_name;
                $portals_index[$portal_name] = array(
                    'pos' => $pos,
                    'dst' => $dst,
                    'col' => $col,
                    'row' => $row,
                    'label' => $label,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    );
            }
        }
        $portal_symbols_json = json_encode($portal_symbols, JSON_NUMERIC_CHECK);
        $portals_index_json = json_encode($portals_index, JSON_NUMERIC_CHECK);
        $portals_markup[] = '<script data-json="portalSymbols" type="application/json">'.$portal_symbols_json.'</script>';
        $portals_markup[] = '<script data-json="portalsIndex" type="application/json">'.$portals_index_json.'</script>';
        return implode(PHP_EOL, $portals_markup);
    }

    // Define a function for getting the BUTTONS LAYER sprite markup for the world map
    public static function get_buttons_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_buttons_layer_markup() called!');
        // BUTTONS LAYER
        $WORLD_SESSION = self::get_session();
        $world_buttons = !empty($WORLD_SESSION['world_buttons']) ? $WORLD_SESSION['world_buttons'] : array();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_tilesize_offset = $map_config['tilesize_offset'];
        $buttons_markup = array();
        $button_symbols = array();
        $buttons_index = array();
        if (!empty($map_data_parsed['buttons'])){
            $button_sprites = $map_data_parsed['buttons'];
            $world_map_buttons = !empty($world_buttons[$world_map_token]) ? $world_buttons[$world_map_token] : array();
            foreach ($button_sprites AS $button_name => $button_data){
                if (empty($button_data) || !is_array($button_data) || count($button_data) < 2){ continue; }
                $pos = $button_data[0]; list($col, $row) = explode('-', $pos); unset($button_data[0]);
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                $colour = !empty($button_data[1]) ? $button_data[1] : 'black'; unset($button_data[1]);
                $state = !empty($button_data[2]) ? $button_data[2] : 'up'; unset($button_data[2]);
                $action = !empty($button_data[3]) ? $button_data[3] : ''; unset($button_data[3]);
                $hidden = false; if (in_array('hidden', $button_data)){ $hidden = true; unset($button_data[array_search('hidden', $button_data)]); }
                $locked = false; if (in_array('locked', $button_data)){ $locked = true; unset($button_data[array_search('locked', $button_data)]); }
                $data = array_values($button_data);
                if (!empty($world_map_buttons[$button_name])){ $state = $world_map_buttons[$button_name]; }
                if ($hidden){ continue; }
                $is_glowing = $state !== 'down' && !$hidden && !$locked ? true : false;
                $base_classes = 'sprite tile button';
                $kind_classes = $colour.' '.$state;
                $sprite = '<span class="'.$base_classes.' '.$kind_classes.'"></span>';
                $attrs = 'data-button="'.$button_name.'" data-colour="'.$colour.'" data-state="'.$state.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $styles = 'top: '.$top.'px; left: '.$left.'px;';
                $classes = $base_classes.($is_glowing ? ' glow' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $buttons_markup[] = '<span data-sprite="button" class="'.$classes.'" '.$attrs.' style="'.$styles.'">'.$sprite.'</span>';
                $button_symbols[$pos] = $button_name;
                $buttons_index[$button_name] = array(
                    'pos' => $pos,
                    'col' => $col,
                    'row' => $row,
                    'colour' => $colour,
                    'state' => $state,
                    'action' => $action,
                    'data' => $data,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    );
            }
        }
        $button_symbols_json = json_encode($button_symbols, JSON_NUMERIC_CHECK);
        $buttons_index_json = json_encode($buttons_index, JSON_NUMERIC_CHECK);
        $buttons_markup[] = '<script data-json="buttonSymbols" type="application/json">'.$button_symbols_json.'</script>';
        $buttons_markup[] = '<script data-json="buttonsIndex" type="application/json">'.$buttons_index_json.'</script>';
        return implode(PHP_EOL, $buttons_markup);
    }

    // Define a function for getting the BATTLES LAYER sprite markup for the world map
    public static function get_battles_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_battles_layer_markup() called!');
        // BATTLES LAYER
        $WORLD_SESSION = self::get_session();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_col_size = $map_config['col_size'];
        $map_row_size = $map_config['row_size'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];
        $world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
        $world_map_encounters = !empty($world_encounters[$world_map_token]) ? $world_encounters[$world_map_token] : array();
        if (empty($world_map_encounters)){
            $world_map_encounters = rpg_world::generate_worldmap_encounters($this_prototype_data, $map_data_parsed);
            self::update_session('world_encounters', $world_map_token, $world_map_encounters);
        }
        //error_log('$world_map_encounters = '.print_r($world_map_encounters, true));
        $battles_markup = array();
        $battle_symbols = array();
        $battles_index = array();
        foreach ($world_map_encounters as $encounter){
            $kind = $encounter[0]; $subkind = '';
            if (strstr($kind, '/')){ list($kind, $subkind) = explode('/', $kind, 2); }
            //$xkind = rpg_world::get_xkind($kind);
            $token = $encounter[1];
            $alt = $encounter[2];
            $pos = $encounter[3];
            $battle = $encounter[4];
            $name = $encounter[5];
            if (!rpg_battle::has_index_info($battle)){ continue; }
            list($col, $row) = explode('-', $pos);
            $maxcols = $map_col_size;
            $maxrows = $map_row_size;
            $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
            $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
            $zindex = ($maxrows + 1) - $row;
            $dir = ($col > ($map_col_size / 2)) ? 'left' : 'right';
            if (mt_rand(1, 2) === 1){ $dir = $dir !== 'left' ? 'left' : 'right'; }
            $class = 'battle vs-'.$subkind.' bounce';
            if ($subkind === 'master'){ $class .= ' always-zoom'; }
            elseif ($subkind === 'boss'){ $class .= ' always-zoom'; }
            $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$zindex.';';
            $attrs = 'data-battle="'.$battle.'" data-label="'.$name.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
            $markup = self::get_sprite($kind, $token, $alt, $dir, $class, $style, $attrs);
            $markup = str_replace('data-sprite="'.$kind.'"', 'data-sprite="battle-'.$kind.'"', $markup);
            $battles_markup[] = $markup;
            $battle_symbols[$pos] = $battle;
            $battles_index[$battle] = array(
                'kind' => $kind,
                'token' => $token,
                'alt' => $alt,
                'col' => $col,
                'row' => $row,
                'pos' => $pos,
                );
            }
        $battle_symbols_json = json_encode($battle_symbols, JSON_NUMERIC_CHECK);
        $battles_index_json = json_encode($battles_index, JSON_NUMERIC_CHECK);
        $battles_markup[] = '<script data-json="battleSymbols" type="application/json">'.$battle_symbols_json.'</script>';
        $battles_markup[] = '<script data-json="battlesIndex" type="application/json">'.$battles_index_json.'</script>';
        return implode(PHP_EOL, $battles_markup);
    }

    // Define a function for getting the TEAM LAYER sprite markup for the world map
    public static function get_team_sprites($map_data_parsed, $team_sprites, $target_position = '1-1', $team_class = 'team', $team_dir = 'down-right'){
        //error_log('rpg_world::get_team_sprites() called!');
        $map_config = $map_data_parsed['config'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];
        $sprites = array();
        list($col, $row) = explode('-', $target_position);
        $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
        $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
        if (strstr($team_dir, 'left')){ $left += count($team_sprites) * 4; }
        elseif (strstr($team_dir, 'right')){ $left -= count($team_sprites) * 4; }
        foreach ($team_sprites as $key => $sprite){
            $id = 0;
            $kind = $sprite[0];
            $token = $sprite[1];
            if (strstr($token, '_')){ list($id, $token) = explode('_', $token, 2); }
            $img = isset($sprite[2]) ? $sprite[2] : $token;
            $alt = strstr($img, '_') ? explode('_', $img, 2)[1] : '';
            $dir = strstr($team_dir, 'left') ? 'left' : 'right';
            $disabled = in_array('disabled', $sprite) ? true : false;
            if ($key > 0){
                if (strstr($team_dir, 'left')){ $left -= 10; }
                elseif (strstr($team_dir, 'right')){ $left += 10; }
                if (strstr($team_dir, 'up')){ $top += 4; }
                elseif (strstr($team_dir, 'down')){ $top -= 4; }
                }
            $class = $team_class.' bounce'.($disabled ? ' disabled' : '');
            $styles = 'top: '.$top.'px; left: '.$left.'px; ';
            $attrs = 'data-key="'.$key.'"';
            if (!empty($id)){ $attrs .= ' data-id="'.$id.'"'; }
            $markup = self::get_sprite($kind, $img, $alt, $dir, $class, $styles, $attrs);
            $markup = str_replace('data-sprite="'.$kind.'"', 'data-sprite="'.$team_class.'-'.$kind.'"', $markup);
            if ($disabled){ $markup = str_replace('data-frame="00"', 'data-frame="03"', $markup); }
            if (!empty($markup)){ $sprites[] = $markup; }
            }
        return implode(PHP_EOL, $sprites);
    }

    // Define a function for getting the TEAM LAYER sprite markup for the world map (team only)
    public static function get_team_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_team_layer_markup() called!');
        // TEAM LAYER
        $team_markup = array();
        $WORLD_SESSION = self::get_session();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_col_size = $map_config['col_size'];
        $map_row_size = $map_config['row_size'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];// Collect the current team members from the prototype data
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
                //$robot = array('robot', $robot_token);
                $robot = array('robot', $robot_string);
                $robot_overview = self::get_player_robot_overview($team_player_token, $robot_token, $robot_id);
                if (!empty($robot_overview['image'])){ $robot[] = $robot_overview['image']; }
                if (!empty($robot_overview['disabled'])){ $robot[] = 'disabled'; }
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
        $cursor_markup = self::get_cursor_sprite($team_direction, $class, $styles, $attrs);
        $cursor_markup = str_replace('data-sprite="robot"', 'data-sprite="team-cursor"', $cursor_markup);
        $team_markup[] = $cursor_markup;
        // Now we can generate the markup for the actual team sprites if any were defined
        $team_markup[] = self::get_team_sprites($map_data_parsed, $team_sprites, $team_position, 'team', $team_direction);
        // Return the generated team markup
        return implode(PHP_EOL, $team_markup);
    }

    // Define a function for getting the RIVALS LAYER sprite markup for the world map (rivals only)
    public static function get_rivals_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_rivals_layer_markup() called!');
        // RIVALS LAYER
        $rivals_markup = array();
        $WORLD_SESSION = self::get_session();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_col_size = $map_config['col_size'];
        $map_row_size = $map_config['row_size'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];
        // Loop through the other allowed players to see if any are also on this map
        $rivals_markup = array();
        $rival_symbols = array();
        $mmrpg_index_players = self::get_index('players');
        $world_player_sessions = !empty($WORLD_SESSION['player_sessions']) ? $WORLD_SESSION['player_sessions'] : array();
        $allowed_player_tokens = !empty($world_player_sessions['allowed']) ? $world_player_sessions['allowed'] : array_keys($world_player_sessions);
        $current_player_token = !empty($this_prototype_data['this_player_token']) ? $this_prototype_data['this_player_token'] : 'player';
        foreach ($allowed_player_tokens AS $pkey => $ptoken){
            if ($ptoken === 'player'){ continue; } // skip the default player
            if ($ptoken === $current_player_token){ continue; } // skip the current player
            if (empty($mmrpg_index_players[$ptoken])){ continue; } // skip if not a valid player
            if (empty($world_player_sessions[$ptoken])){ continue; } // skip if no player session
            //error_log('Checking for player "'.$ptoken.'" on map "'.$world_map_token.'"');
            $pinfo = $mmrpg_index_players[$ptoken];
            $tmp_session = $world_player_sessions[$ptoken];
            $tmp_world_token = !empty($tmp_session['last_world']) ? $tmp_session['last_world'] : '';
            $tmp_map_token = !empty($tmp_session['last_map']) ? $tmp_session['last_map'] : '';
            $tmp_world_position = !empty($tmp_session['last_position']) ? $tmp_session['last_position'] : '';
            $tmp_world_direction = !empty($tmp_session['last_direction']) ? $tmp_session['last_direction'] : '';
            $tmp_world_robots = !empty($tmp_session['last_robots']) ? $tmp_session['last_robots'] : '';
            if (empty($tmp_world_token) || $tmp_world_token !== $world_token){ continue; } // skip if not on this world
            if (empty($tmp_map_token) || $tmp_map_token !== $map_token){ continue; } // skip if not on this map
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
            $rivals_markup[] = self::get_team_sprites($map_data_parsed, $tmp_team_sprites, $tmp_world_position, 'rival', $tmp_world_direction);
        }
        $rival_symbols_json = json_encode($rival_symbols, JSON_NUMERIC_CHECK);
        $rivals_markup[] = '<script data-json="rivalSymbols" type="application/json">'.$rival_symbols_json.'</script>';
        // Return the gernated rivals markup
        return implode(PHP_EOL, $rivals_markup);
    }


    // -- MISC HELPER METHODS -- //

    // Define a method for quickly grabbing an overview of a given player's robot (including name, level, stats, etc.)
    public static function get_player_robot_overview($player_token, $robot_token, $robot_id){
        //error_log('rpg_world::get_player_robot_overview() called for player "'.$player_token.'" and robot "'.$robot_token.'"');
        // First validate the player and robot tokens and pull their index info
        if (empty($player_token) || $player_token === 'player'){ error_log('Invalid player token "'.$player_token.'"'); return false; }
        if (empty($robot_token) || $robot_token === 'robot'){ error_log('Invalid robot token "'.$robot_token.'"'); return false; }
        if (empty($robot_id) || !is_numeric($robot_id)){ error_log('Invalid robot ID "'.$robot_id.'"'); return false; }
        static $mmrpg_index_players; if (empty($mmrpg_index_players)){ $mmrpg_index_players = self::get_index('players'); }
        static $mmrpg_index_robots; if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = self::get_index('robots'); }
        if (empty($mmrpg_index_players[$player_token])){ error_log('Invalid player token "'.$player_token.'"'); return false; }
        if (empty($mmrpg_index_robots[$robot_token])){ error_log('Invalid robot token "'.$robot_token.'"'); return false; }
        static $player_starforce; if (empty($player_starforce)){ $player_starforce = rpg_game::starforce_unlocked(); }
        static $get_rating_token; if (empty($get_rating_token)){ $get_rating_token = function($percent){
            if ($percent === 100){ return 'full'; }
            elseif ($percent >= 50){ return 'high'; }
            elseif ($percent >= 20){ return 'med'; }
            elseif ($percent >= 1){ return 'low'; }
            else { return 'no'; }
            }; }
        $player_index_info = $mmrpg_index_players[$player_token];
        $robot_index_info = $mmrpg_index_robots[$robot_token];
        // Pull any world session states for these robots as well
        $WORLD_SESSION = self::get_session();
        $WORLD_ROBOT_SESSIONS = &$WORLD_SESSION['robot_sessions'];
        // Start grabbing the robot data from their index info and any session states
        $robot_info = $robot_index_info;
        $robot_rewards = rpg_game::robot_rewards($player_token, $robot_token);
        $robot_settings = rpg_game::robot_settings($player_token, $robot_token);
        $robot_session = !empty($WORLD_ROBOT_SESSIONS[$robot_token]) ? $WORLD_ROBOT_SESSIONS[$robot_token] : array();
        $robot_level = !empty($robot_rewards['robot_level']) ? $robot_rewards['robot_level'] : 1;
        $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : '';
        $robot_core_types = 'type '.(!empty($robot_info['robot_core']) ? ($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' '.$robot_info['robot_core2'] : '')) : 'none');
        $robot_core_or_none = !empty($robot_core) ? $robot_core : 'none';
        $robot_item = !empty($robot_settings['robot_item']) ? $robot_settings['robot_item'] : '';
        $has_persona_applied = false;
        if (!empty($robot_settings['robot_persona'])
            && !empty($robot_settings['robot_abilities']['copy-style'])){
            //error_log($robot_info['robot_token'].' has a persona: '.$robot_settings['robot_persona']);
            $persona_token = $robot_settings['robot_persona'];
            $persona_image_token = !empty($robot_settings['robot_persona_image']) ? $robot_settings['robot_persona_image'] : $robot_settings['robot_persona'];
            $persona_index_info = $mmrpg_index_robots[$persona_token];
            rpg_robot::apply_persona_info($robot_info, $persona_index_info, $robot_settings);
            //error_log('new $robot_info = '.print_r($robot_info, true));
            $has_persona_applied = true;
        }
        $robot_persona = !empty($robot_info['robot_persona']) ? $robot_info['robot_persona'] : '';
        $base_core_type = $has_persona_applied ? 'copy' : $robot_core;
        $base_stats_ref = $has_persona_applied ? array_merge($robot_info, array('robot_token' => $robot_settings['robot_persona'])) : $robot_info;
        $robot_stats = rpg_robot::calculate_stat_values($robot_level, $base_stats_ref, $robot_rewards, true, $base_core_type, $player_starforce);
        //error_log($robot_token.' | $robot_stats = '.print_r($robot_stats, true));
        $robot_disabled = false;
        $robot_image = $robot_token;
        if (!empty($robot_settings['robot_persona_image'])){ $robot_image = $robot_settings['robot_persona_image']; }
        elseif (!empty($robot_settings['robot_image'])){ $robot_image = $robot_settings['robot_image']; }
        $robot_info['robot_image'] = $robot_image;
        $robot_energy = $robot_energy_max = $robot_energy_percent = 0; $robot_energy_rating = 'no';
        if (!empty($robot_info['robot_energy'])){
            $robot_energy = $robot_stats['energy']['current'];
            $robot_energy_max = $robot_stats['energy']['current'];
            if (isset($robot_session['energy'])){ $robot_energy += $robot_session['energy']; }
            $robot_energy_percent = ceil(($robot_energy / $robot_energy_max) * 100);
            $robot_energy_rating = $get_rating_token($robot_energy_percent);
            if (empty($robot_energy)){ $robot_disabled = true; }
            }
        $robot_weapons = $robot_weapons_max = $robot_weapons_percent = 0; $robot_weapons_rating = 'no';
        if (!empty($robot_info['robot_weapons'])){
            $robot_weapons = $robot_stats['weapons']['current'];
            $robot_weapons_max = $robot_stats['weapons']['current'];
            if (isset($robot_session['weapons'])){ $robot_weapons += $robot_session['weapons']; }
            $robot_weapons_percent = ceil(($robot_weapons / $robot_weapons_max) * 100);
            $robot_weapons_rating = $get_rating_token($robot_weapons_percent);
            }
        // Collect the above details into a single array and return it
        $robot_overview = array(
            'id' => $robot_id,
            'token' => $robot_token,
            'persona' => $robot_persona,
            'name' => $robot_info['robot_name'],
            'core' => $robot_info['robot_core'],
            'core2' => $robot_info['robot_core2'],
            'coreTypes' => $robot_core_types,
            'coreElse' => $robot_core_or_none,
            'image' => $robot_info['robot_image'],
            'level' => $robot_level,
            'item' => $robot_item,
            'energy' => $robot_energy,
            'energyMax' => $robot_energy_max,
            'energyPercent' => $robot_energy_percent,
            'energyRating' => $robot_energy_rating,
            'weapons' => $robot_weapons,
            'weaponsMax' => $robot_weapons_max,
            'weaponsPercent' => $robot_weapons_percent,
            'weaponsRating' => $robot_weapons_rating,
            'attack' => $robot_stats['attack']['current'],
            'attackMods' => (isset($robot_session['attack']) ? $robot_session['attack'] : 0),
            'defense' => $robot_stats['defense']['current'],
            'defenseMods' => (isset($robot_session['defense']) ? $robot_session['defense'] : 0),
            'speed' => $robot_stats['speed']['current'],
            'speedMods' => (isset($robot_session['speed']) ? $robot_session['speed'] : 0),
            'disabled' => $robot_disabled,
            );
        if (!$has_persona_applied){ unset($robot_overview['persona']); }
        return $robot_overview;
    }

    // Define a function for generating a perspective matrix (given known values) for world map positioning
    public static function get_perspective_matrix($dimensions, $transform = array()){
        //error_log('rpg_world::get_perspective_matrix() called!');
        if (empty($dimensions) || !is_array($dimensions) || count($dimensions) !== 2){ error_log('error: $dimensions must be an array with two numeric values!'); return false; }
        if (empty($transform)){ $transform = array(800, 25, 1.5); } // TODO: hard-coded defaults we should store somewhere
        // Unpack dimensions and transform values
        list($width, $height) = $dimensions;
        list($perspectiveDistance, $rotateX, $scale) = $transform;
        // Perspective matrix (d = perspectiveDistance)
        $p = [
            [1, 0, 0, 0],
            [0, 1, 0, 0],
            [0, 0, 1, -1 / $perspectiveDistance],
            [0, 0, 0, 1]
            ];
        // Rotation matrix around X-axis (rotateX degrees)
        $theta = deg2rad($rotateX);
        $rx = [
            [1, 0, 0, 0],
            [0, cos($theta), -sin($theta), 0],
            [0, sin($theta), cos($theta), 0],
            [0, 0, 0, 1]
            ];
        // Rotation matrix around Y-axis (rotateY is fixed at 0 degrees, so identity matrix)
        $ry = [
            [1, 0, 0, 0],
            [0, 1, 0, 0],
            [0, 0, 1, 0],
            [0, 0, 0, 1]
            ];
        // Scaling matrix (scale factor)
        $s = [
            [$scale, 0, 0, 0],
            [0, $scale, 0, 0],
            [0, 0, $scale, 0],
            [0, 0, 0, 1]
            ];
        // Multiply matrices in order: P * Rx * Ry * S
        $transformMatrix = self::multiply_matrices(self::multiply_matrices(self::multiply_matrices($p, $rx), $ry), $s);
        return $transformMatrix;
    }
    // Define a function for transforming a point using the perspective matrix
    public static function transform_point_with_matrix($xy, $matrix) {
        //error_log('rpg_world::transform_point_with_matrix() called!');
        // Apply the affine transformation
        list($x, $y) = $xy;
        // If the matrix is a 4x4 matrix (affine transformation matrix), apply the transformation accordingly
        if (count($matrix) == 4 && count($matrix[0]) == 4) {
            // Use homogeneous coordinates for affine transformation (add z=0 and w=1)
            $z = 0;
            $w = 1;
            // Apply the matrix to the point (in homogeneous coordinates)
            $newX = $matrix[0][0] * $x + $matrix[0][1] * $y + $matrix[0][2] * $z + $matrix[0][3] * $w;
            $newY = $matrix[1][0] * $x + $matrix[1][1] * $y + $matrix[1][2] * $z + $matrix[1][3] * $w;
            $newZ = $matrix[2][0] * $x + $matrix[2][1] * $y + $matrix[2][2] * $z + $matrix[2][3] * $w;
            $newW = $matrix[3][0] * $x + $matrix[3][1] * $y + $matrix[3][2] * $z + $matrix[3][3] * $w;
            // We return the point (x', y') for 2D space (homogeneous coordinates)
            // Here we ignore the Z and W because they aren't relevant for 2D projections
            return [$newX / $newW, $newY / $newW];
        } else {
            // Fallback if the matrix is not the expected size (shouldn't happen with correct input)
            throw new Exception("Matrix must be a 4x4 matrix.");
        }
    }
    // Matrix multiplication function
    public static function multiply_matrices($A, $B){
        $C = [];
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                $C[$i][$j] = 0;
                for ($k = 0; $k < 4; $k++) {
                    $C[$i][$j] += $A[$i][$k] * $B[$k][$j];
                }
            }
        }
        return $C;
    }
    // Print a patrix in an easy-to-understand way
    public static function print_matrix($matrix) {
        $output = '';
        foreach ($matrix as $row) {
            $output .= '[ ' . implode(' ', array_map(function($value) { return number_format($value, 2); }, $row)) . " ]\n";
        }
        return $output;
    }


}
?>
