<?
/**
 * Mega Man RPG World
 * <p>The global world (map) class for the Mega Man RPG Prototype.</p>
 */
class rpg_world {

    // Define the static variables for this class
    static $mmrpg_indexes = array();

    // Define a function for loading indexes into this class
    public static function load_indexes($indexes = array()){
        //error_log('rpg_world::load_indexes() called!');
        if (empty($indexes) || !is_array($indexes)){ return false; }
        // Loop through the indexes and add them to the static variable
        foreach ($indexes AS $kind => $index){
            if (empty($kind) || empty($index) || !is_array($index)){ continue; }
            self::$mmrpg_indexes[$kind] = $index;
            //error_log('loaded index for "'.$kind.'" with '.count($index).' items');
        }
        return true;
    }

    // Define a function for getting the indexes loaded into this class
    public static function get_indexes($kind = ''){
        //error_log('rpg_world::get_indexes() called!');
        if (empty(self::$mmrpg_indexes)){ return array(); }
        if (empty($kind)){ return self::$mmrpg_indexes; }
        if (!isset(self::$mmrpg_indexes[$kind])){ return array(); }
        return self::$mmrpg_indexes[$kind];
    }

    // Define a function for loading a given map's data from the filesystem
    public static function load_map_data($map_token){
        //error_log('load_map_data() called!');
        static $map_basedir = MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH;
        static $map_tilesize = MMRPG_WORLD_DEFAULT_TILESIZE;
        if (empty($map_token)){
            //error_log('load_map_data() error: missing map token!');
            return false;
            }
        $map_filename = $map_token.'.map';
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
        $map_data_vars['tiles'] = isset($map_data_vars['tiles']) ? $map_data_vars['tiles'] : array();
        $map_data_vars['groups'] = isset($map_data_vars['groups']) ? $map_data_vars['groups'] : array();
        $map_data_vars['sprites'] = isset($map_data_vars['sprites']) ? $map_data_vars['sprites'] : array();
        $map_data_vars['portals'] = isset($map_data_vars['portals']) ? $map_data_vars['portals'] : array();
        $map_data_vars['buttons'] = isset($map_data_vars['buttons']) ? $map_data_vars['buttons'] : array();
        $map_data_vars['switches'] = isset($map_data_vars['switches']) ? $map_data_vars['switches'] : array();
        $map_data_vars['field'] = isset($map_data_vars['field']) ? $map_data_vars['field'] : '';
        $map_data_vars['terrain'] = isset($map_data_vars['terrain']) ? $map_data_vars['terrain'] : array();
        $map_data_vars['mechas'] = isset($map_data_vars['mechas']) ? $map_data_vars['mechas'] : '';
        $map_data_vars['masters'] = isset($map_data_vars['masters']) ? $map_data_vars['masters'] : '';
        $map_data_vars['habitats'] = isset($map_data_vars['habitats']) ? $map_data_vars['habitats'] : array();
        $map_data_vars['bosses'] = isset($map_data_vars['bosses']) ? $map_data_vars['bosses'] : array();
        if (empty($map_data_vars['token'])){ $map_data_vars['token'] = $map_token; }
        if (empty($map_data_vars['name'])){ $map_data_vars['name'] = 'Undefined'; }
        if (empty($map_data_vars['size'])){ $map_data_vars['size'] = '0 x 0 x 0'; }
        if (empty($map_data_vars['sheet'])){ $map_data_vars['sheet'] = 'undefined.png'; }
        if (!empty($map_data_vars['size'])){ $map_data_vars['size'] = explode('x', str_replace(' ', '', $map_data_vars['size'])); }
        if (!isset($map_data_vars['size'][0])){ $map_data_vars['size'][0] = $map_autocols; }
        if (!isset($map_data_vars['size'][1])){ $map_data_vars['size'][1] = $map_autorows; }
        if (!isset($map_data_vars['size'][2])){ $map_data_vars['size'][2] = $map_tilesize; }
        if (empty($map_data_vars['field'])){ $map_data_vars['field'] = 'field'; }
        if (!empty($map_data_vars['mechas'])){ $map_data_vars['mechas'] = explode(',', str_replace(' ', '', $map_data_vars['mechas'])); }
        if (!empty($map_data_vars['masters'])){ $map_data_vars['masters'] = explode(',', str_replace(' ', '', $map_data_vars['masters'])); }
        $map_data_vars['tiles'] = $map_custval_parser($map_data_vars['tiles'], true);
        $map_data_vars['groups'] = $map_custval_parser($map_data_vars['groups']);
        $map_data_vars['sprites'] = $map_custval_parser($map_data_vars['sprites']);
        $map_data_vars['portals'] = $map_custval_parser($map_data_vars['portals']);
        $map_data_vars['buttons'] = $map_custval_parser($map_data_vars['buttons']);
        $map_data_vars['switches'] = $map_custval_parser($map_data_vars['switches']);
        $map_data_vars['terrain'] = $map_custval_parser($map_data_vars['terrain']);
        $map_data_vars['habitats'] = $map_custval_parser($map_data_vars['habitats']);
        $map_data_vars['bosses'] = $map_custval_parser($map_data_vars['bosses']);
        // Add collected data to the parsed map data
        $map_data_parsed = array();
        $map_data_parsed['token'] = $map_data_vars['token']; unset($map_data_vars['token']);
        $map_data_parsed['name'] = $map_data_vars['name']; unset($map_data_vars['name']);
        $map_data_parsed['size'] = $map_data_vars['size']; unset($map_data_vars['size']);
        $map_data_parsed['sheet'] = $map_data_vars['sheet']; unset($map_data_vars['sheet']);
        $map_data_parsed['tiles'] = $map_data_vars['tiles']; unset($map_data_vars['tiles']);
        $map_data_parsed['groups'] = $map_data_vars['groups']; unset($map_data_vars['groups']);
        $map_data_parsed['sprites'] = $map_data_vars['sprites']; unset($map_data_vars['sprites']);
        $map_data_parsed['portals'] = $map_data_vars['portals']; unset($map_data_vars['portals']);
        $map_data_parsed['buttons'] = $map_data_vars['buttons']; unset($map_data_vars['buttons']);
        $map_data_parsed['switches'] = $map_data_vars['switches']; unset($map_data_vars['switches']);
        $map_data_parsed['field'] = $map_data_vars['field']; unset($map_data_vars['field']);
        $map_data_parsed['terrain'] = $map_data_vars['terrain']; unset($map_data_vars['terrain']);
        $map_data_parsed['mechas'] = $map_data_vars['mechas']; unset($map_data_vars['mechas']);
        $map_data_parsed['masters'] = $map_data_vars['masters']; unset($map_data_vars['masters']);
        $map_data_parsed['habitats'] = $map_data_vars['habitats']; unset($map_data_vars['habitats']);
        $map_data_parsed['bosses'] = $map_data_vars['bosses']; unset($map_data_vars['bosses']);
        //$map_data_parsed['tiles']['keys'] = array_keys($map_data_parsed['tiles']);
        $map_data_parsed['layers'] = $map_data_layers;
        if (!empty($map_data_vars)){ $map_data_parsed['vars'] = $map_data_vars; }
        //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
        return $map_data_parsed;
    }

    // Define a function for loading a given sheet's data from the filesystem
    public static function load_sheet_data($sheet_token){
        //error_log('load_sheet_data() called!');
        static $sheet_basedir = MMRPG_CONFIG_ROOTDIR.MMRPG_WORLD_MAPFILE_BASEPATH;
        static $sheet_tilesize = MMRPG_WORLD_DEFAULT_TILESIZE;
        if (empty($sheet_token)){ error_log('rpg_world::load_sheet_data() error - missing sheet token!'); return false; }
        $sheet_filename = $sheet_token.'.sheet';
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
        $sheet_data_vars['token'] = isset($sheet_data_vars['token']) ? $sheet_data_vars['token'] : '';
        $sheet_data_vars['name'] = isset($sheet_data_vars['name']) ? $sheet_data_vars['name'] : '';
        $sheet_data_vars['size'] = isset($sheet_data_vars['size']) ? $sheet_data_vars['size'] : '';
        $sheet_data_vars['image'] = isset($sheet_data_vars['image']) ? $sheet_data_vars['image'] : '';
        $sheet_data_vars['tiles'] = isset($sheet_data_vars['tiles']) ? $sheet_data_vars['tiles'] : array();
        $sheet_data_vars['sprites'] = isset($sheet_data_vars['sprites']) ? $sheet_data_vars['sprites'] : array();
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
        //error_log('$available_cells('.count($available_cells).') = ['.implode(', ', array_keys($available_cells)).']');
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
        if (!empty($map_data['tiles']) && !empty($map_data['tiles']['keys']) && !empty($map_data['layers'])){
            $tilesIndex = $map_data['tiles']['keys'];
            //error_log('-> $tilesIndex = '.print_r($tilesIndex, true));
            $tileLayers = $map_data['layers'];
            //error_log('-> $tileLayers = '.print_r($tileLayers, true));
            foreach ($tileLayers AS $layer_key => $layer_tiles){
                //error_log('-> checking layer #'.$layer_key.' $tileLayers ...');
                //error_log('-> found '.count($layer_tiles).' rows in $layer_tiles ...');
                foreach ($layer_tiles AS $row_key => $row_tiles){
                    //error_log('-> checking $tileLayers['.$layer_key.']['.$row_key.'] ...');
                    //error_log('-> $tileLayers['.$layer_key.']['.$row_key.'] = '.print_r($row_tiles, true));
                    $row_tiles = str_replace(array('[', ']'), '', $row_tiles);
                    //error_log('-> $row_tiles(1) = '.print_r($row_tiles, true));
                    $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
                    //error_log('-> $row_tiles(2) = '.print_r($row_tiles, true));
                    $row_tiles = array_map('intval', $row_tiles);
                    //error_log('-> $row_tiles(3) = '.print_r($row_tiles, true));
                    foreach ($row_tiles AS $col_key => $tile_key){
                        $pos = ($col_key + 1).'-'.($row_key + 1);
                        if (!isset($available_cells[$pos])){ continue; }
                        if (!isset($tilesIndex[$tile_key])){ continue; }
                        $tile_token = $tilesIndex[$tile_key];
                        //error_log('-> $tilesIndex['.$tile_key.'] = "'.$tile_token.'"');
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

    // Define a function for getting the terrain type for a given position on the map, but do not limit only to available tiles
    public static function get_map_position_terrain($position, $map_data_parsed){
        //error_log('rpg_world::get_map_position_terrain() called for position "'.$position.'"');
        if (empty($position) || !is_string($position) || !isset($map_data_parsed['tiles']) || !isset($map_data_parsed['layers'])){ return 'unknown'; }
        // Loop through the tiles and find the one that matches this position
        $tilesIndex = $map_data_parsed['tiles']['keys'];
        foreach ($map_data_parsed['layers'] AS $layer_key => $layer_tiles){
            foreach ($layer_tiles AS $row_key => $row_tiles){
                $row_tiles = str_replace(array('[', ']'), '', $row_tiles);
                $row_tiles = strstr($row_tiles, ',') ? explode(',', $row_tiles) : str_split($row_tiles);
                foreach ($row_tiles AS $col_key => $tile_key){
                    if (!isset($tilesIndex[$tile_key])){ continue; }
                    $pos = ($col_key + 1).'-'.($row_key + 1);
                    if ($pos === $position){ return explode('-', $tilesIndex[$tile_key])[0]; }
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
    // TODO: Define this as an actual function instead of a variable
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

    // Define a reusable method for the above that takes args and generates markup to return as a string
    // TODO:  Define this as an actual function instead of a variable
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
        $sprite_path = 'images/'.$xkind.'/all/token:'.$img.($alt ? '+alt:'.$alt : '').'+crop:false+dir:both/sprite_left_'.$xsize.'.png';
        $sprite_class = 'sprite '.$kind.($class ? ' '.$class : '');
        $sprite_styles = ($styles ? ' style="'.$styles.'"' : '');
        $sprite_attrs = ' data-token="'.$img.'" data-size="'.$size.'" data-dir="'.$dir.'" data-frame="00" '.($attrs ? ' '.$attrs : '');
        $sprite_markup = '';
        $sprite_markup .= '<span class="'.$sprite_class.'"'.$sprite_attrs.$sprite_styles.'>';
            $sprite_markup .= '<span class="wrap">';
                $sprite_markup .= '<span class="sprite" style="background-image: url('.$sprite_path.');"></span>';
            $sprite_markup .= '</span>';
        $sprite_markup .= '</span>';
        return($sprite_markup);
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
    public static function get_player_switcher_markup($this_prototype_data, $allowed_player_tokens){
        //error_log('rpg_world::get_player_switcher_markup() called!');
        $return_markup = '';
        $get_label_span = function($name, $kind){ return ('<span class="label">'.$name.' ('.ucfirst($kind).')</span>'); };
        $cursor_token = 'player';
        $cursor_active = $this_prototype_data['this_player_token'] === $cursor_token ? true : false;
        $cursor_sprite = self::get_sprite('robot', 'pointan', '', 'right', 'cursor');
        //$cursor_sprite = str_replace('images/robots/pointan/sprite_', 'images/assets/cursor_', $cursor_sprite);
        $cursor_sprite = preg_replace('/background-image: url\(([^\(\)]+)\);/i', 'background-image: url(images/assets/cursor_40x40.png);', $cursor_sprite);
        $cursor_label = $get_label_span('Prε', 'cursor');
        $cursor_types = ' type explode';
        //$return_markup .= ('<a class="option'.($cursor_active ? ' active' : '').'" data-player="'.$cursor_token.'">'.$cursor_sprite.$cursor_label.'</a>');
        $mmrpg_index_players = self::get_indexes('players');
        foreach ($allowed_player_tokens AS $player_key => $player_token){
            if ($player_token === 'player' || empty($mmrpg_index_players[$player_token])){ continue; }
            $player_info = $mmrpg_index_players[$player_token];
            $player_active = $player_token === $this_prototype_data['this_player_token'] ? true : false;
            $player_sprite = self::get_sprite('player', $player_token, '', 'right', 'character', '');
            $player_label = $get_label_span($player_info['player_name'], 'player');
            $player_types = ' type '.$player_info['player_type'];
            $return_markup .= ('<a class="option'.$player_types.($player_active ? ' active' : '').'" data-player="'.$player_token.'">'.$player_sprite.$cursor_sprite.$player_label.'</a>');
        }
        $return_markup .= ('<a class="option'.$cursor_types.($cursor_active ? ' active' : '').'" data-player="'.$cursor_token.'">'.$cursor_sprite.$cursor_label.'</a>');
        return $return_markup;
    }


}
?>
