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
define('MMRPG_WORLD_DEFAULT_BASEPATH', 'prototype/worldmaps/'); // TODO: move this to the main config file

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
    static $static_encounter_types = array(
        'mecha' => 'mechas',
        'master' => 'masters',
        'boss' => 'bosses',
        'rescue' => 'rescues',
        );

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
        //error_log('self::$mmrpg_indexes = '.print_r(array_keys(self::$mmrpg_indexes), true));
        if (empty($kind) || !isset(self::$mmrpg_indexes[$kind])){ return false; }
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
        if (!isset($WORLD_SESSION['world_blocks'])){ $WORLD_SESSION['world_blocks'] = array(); }
        if (!isset($WORLD_SESSION['world_hazards'])){ $WORLD_SESSION['world_hazards'] = array(); }
        if (!isset($WORLD_SESSION['world_items'])){ $WORLD_SESSION['world_items'] = array(); }
        if (!isset($WORLD_SESSION['world_abilities'])){ $WORLD_SESSION['world_abilities'] = array(); }
        if (!isset($WORLD_SESSION['world_encounters'])){ $WORLD_SESSION['world_encounters'] = array(); }
        if (!isset($WORLD_SESSION['world_pickups'])){ $WORLD_SESSION['world_pickups'] = array(); }
        if (!isset($WORLD_SESSION['world_symbols'])){ $WORLD_SESSION['world_symbols'] = array(); }
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
        if (!is_array($value)) { return false; }
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
        $json_session_keys = array(
            'player_sessions',
            'world_maps',
            'world_encounters',
            'world_pickups',
            'world_buttons',
            'world_switches',
            'world_blocks',
            'world_hazards',
            );
        foreach ($WORLD_SESSION AS $key => $values){
            if (in_array($key, $json_session_keys)){
                $json_session_file = 'WORLD__'.$key.'.json';
                $json_session_file_path = $json_session_path.$json_session_file;
                if (!is_dir(dirname($json_session_file_path))){ mkdir(dirname($json_session_file_path), 0755, true); }
                file_put_contents($json_session_file_path, json_encode($values, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
            }
        }
        // return true on success
        return true;
    }

    // Define a function for processing save-world-data post action
    public static function save_world_data_to_session($worldData, $allowed = array()){
        //error_log('rpg_world::save_world_data_to_session() called!');
        $allowed_world_tokens = isset($allowed['world_tokens']) ? $allowed['world_tokens'] : array();
        $allowed_world_map_tokens = isset($allowed['world_map_tokens']) ? $allowed['world_map_tokens'] : array();
        $allowed_world_sheet_tokens = isset($allowed['world_sheet_tokens']) ? $allowed['world_sheet_tokens'] : array();
        $allowed_player_tokens = isset($allowed['player_tokens']) ? $allowed['player_tokens'] : array();
        $allowed_robot_tokens = isset($allowed['robot_tokens']) ? $allowed['robot_tokens'] : array();
        $world_session_key = self::session_token();
        $game_session_key = rpg_game::session_token();
        $WORLD_SESSION = &$_SESSION[$world_session_key];
        $GAME_SESSION = &$_SESSION[$game_session_key];
        $playerSessions = &$WORLD_SESSION['player_sessions'];
        $robotSessions = &$WORLD_SESSION['robot_sessions'];
        $battleSettings = &$GAME_SESSION['values']['battle_settings'];
        $battleRewards = &$GAME_SESSION['values']['battle_rewards'];
        // Loop through all the world data fields and parse any json fields provided
        //error_log('$worldData(before) = '.print_r($worldData, true));
        foreach ($worldData AS $key => $data){
            if (!is_string($data)){ continue; }
            elseif (substr($data, 0, 1) !== '{' && substr($data, 0, 1) !== '['){ continue; }
            $decoded = json_decode($data, true);
            if (empty($decoded) && !is_array($decoded)){ continue; }
            $worldData[$key] = $decoded;
        }
        //error_log('$worldData(after) = '.print_r($worldData, true));
        //$world_session_hash = md5(serialize($WORLD_SESSION));
        //error_log('$world_session_hash = '.$world_session_hash);
        if (!empty($worldData['lastPlayer'])
            && in_array($worldData['lastPlayer'], $allowed_player_tokens)){
            $playerSessions['last_player'] = $worldData['lastPlayer'];
            // Collect the last player session data so we can update
            $lastPlayer = $worldData['lastPlayer'];
            if (!isset($playerSessions[$lastPlayer])){ $playerSessions[$lastPlayer] = array(); }
            $lastPlayerSession = &$playerSessions[$lastPlayer];
            $lastPlayerSettings = &$battleSettings[$lastPlayer];
            $lastPlayerRewards = &$battleRewards[$lastPlayer];
            // Collect the cursor player session data so we can update too
            $cursorPlayer = 'player';
            if (!isset($playerSessions[$cursorPlayer])){ $playerSessions[$cursorPlayer] = array(); }
            $cursorPlayerSession = &$playerSessions[$cursorPlayer];
            // If last world was provided, save it to the sessions
            if (!empty($worldData['lastPlayerWorld'])
                && in_array($worldData['lastPlayerWorld'], $allowed_world_tokens)){
                $world_token = $worldData['lastPlayerWorld'];
                $lastPlayerSession['last_world'] = $world_token;
                $cursorPlayerSession['last_world'] = $world_token;
                // If last world-map was provided, save it to the sessions
                if (!empty($worldData['lastPlayerWorldMap'])
                    && in_array($worldData['lastPlayerWorldMap'], $allowed_world_map_tokens)){
                    $map_token = explode('__', $worldData['lastPlayerWorldMap'])[1];
                    $lastPlayerSession['last_map'] = $map_token;
                    $cursorPlayerSession['last_map'] = $map_token;
                }
            }
            // If last position was provided, save it to the sessions
            if (!empty($worldData['lastPlayerPosition']) && preg_match('/^([-0-9]+)$/i', $worldData['lastPlayerPosition'])){
                $lastPlayerSession['last_position'] = $worldData['lastPlayerPosition'];
                $cursorPlayerSession['last_position'] = $worldData['lastPlayerPosition'];
            }
            // If last direction was provided, save it to the sessions
            if (!empty($worldData['lastPlayerDirection']) && preg_match('/^([-a-z0-9]+)$/i', $worldData['lastPlayerDirection'])){
                $lastPlayerSession['last_direction'] = $worldData['lastPlayerDirection'];
                $cursorPlayerSession['last_direction'] = $worldData['lastPlayerDirection'];
            }
            // If the last player team was provided, update it to the world session
            if (!empty($worldData['lastPlayerTeam'])){
                // collect the new list of team robots and save to session if valid
                $lastPlayerTeam = $worldData['lastPlayerTeam'];
                //error_log('$lastPlayerTeam = '. print_r($lastPlayerTeam, true));
                if (!empty($lastPlayerTeam)){
                    $old_last_robots = !empty($lastPlayerSession['last_robots']) ? explode(',', $lastPlayerSession['last_robots']) : array();
                    $new_last_robots = !empty($lastPlayerTeam) ? array_values($lastPlayerTeam) : array();
                    //error_log('$old_last_robots = '. print_r($old_last_robots, true));
                    //error_log('$new_last_robots = '. print_r($new_last_robots, true));
                    $lastPlayerSession['last_robots'] = implode(',', $new_last_robots);
                }
            }
            // If the last player robots were provided, update their world sessions
            if (!empty($worldData['lastPlayerRobots'])){
                // scan the player robots for changes in: energy, weapons, attack, defense, speed
                $lastPlayerRobots = $worldData['lastPlayerRobots'];
                if (!empty($lastPlayerRobots)){
                    /*
                    $old_last_robots = !empty($lastPlayerSession['last_robots']) ? explode(',', $lastPlayerSession['last_robots']) : array();
                    $new_last_robots = !empty($lastPlayerRobots) ? array_keys($lastPlayerRobots) : array();
                    //error_log('$old_last_robots = '. print_r($old_last_robots, true));
                    //error_log('$new_last_robots = '. print_r($new_last_robots, true));
                    $lastPlayerSession['last_robots'] = implode(',', $new_last_robots);
                    */
                    // update the player's last robots string and then any relevant session values per-robot
                    foreach ($lastPlayerRobots AS $key => $data){
                        //error_log('-> checking robot key "'.$key.'"');
                        if (!strstr($key, '_')){ continue; }
                        if (empty($data) || !is_array($data)){ continue; }
                        list($id, $token) = explode('_', $key, 2);
                        if (!in_array($token, $allowed_robot_tokens)){ continue; }
                        if (!isset($robotSessions[$token])){ $robotSessions[$token] = array(); }
                        //error_log('-> w/ $data = '. print_r($data, true));
                        // Okay, now we know it exists, we can update values as we find them
                        $robotSession = &$robotSessions[$token];
                        $robotSettings = &$lastPlayerSettings['player_robots'][$token];
                        $robotRewards = &$lastPlayerRewards['player_robots'][$token];
                        //error_log('-> $robotSession(before) = '. print_r($robotSession, true));
                        //error_log('-> $robotSettings(before) = '. print_r($robotSettings, true));
                        //error_log('-> $robotRewards(before) = '. print_r($robotRewards, true));
                        // Check for stat changes and update session values accordingly
                        if (isset($data['energy']) && isset($data['energyMax'])){ $robotSession['energy'] = intval($data['energy']) - intval($data['energyMax']); }
                        if (isset($data['weapons']) && isset($data['weaponsMax'])){ $robotSession['weapons'] = intval($data['weapons']) - intval($data['weaponsMax']); }
                        if (isset($data['attackMods'])){ $robotSession['attack'] = intval($data['attackMods']); }
                        if (isset($data['defenseMods'])){ $robotSession['defense'] = intval($data['defenseMods']); }
                        if (isset($data['speedMods'])){ $robotSession['speed'] = intval($data['speedMods']); }
                        // Check for settings changes and update session values accordingly
                        if (isset($data['item'])){ $robotSettings['robot_item'] = trim($data['item']); }
                        if (isset($data['abilities'])){
                            //error_log('-> $data[\'abilities\'] = '. print_r($data['abilities'], true));
                            $newAbilityIDs = $data['abilities'];
                            $indexedAbilityIDs = rpg_ability::get_indexed_ids();
                            $newAbilitySettings = array();
                            foreach ($newAbilityIDs AS $newID){
                                if (!isset($indexedAbilityIDs[$newID])){ continue; }
                                $newToken = $indexedAbilityIDs[$newID];
                                $newAbilitySettings[$newToken] = array('ability_token' => $newToken);
                                }
                            //error_log('-> $newAbilitySettings = '. print_r($newAbilitySettings, true));
                            if (!empty($newAbilitySettings)){ $robotSettings['robot_abilities'] = $newAbilitySettings; }
                            //error_log('-> NOT saving yet...');
                        }
                        // DEBUG DEBUG DEBUG
                        //error_log('-> $robotSession(after) = '. print_r($robotSession, true));
                        //error_log('-> $robotSettings(after) = '. print_r($robotSettings, true));
                        //error_log('-> $robotRewards(after) = '. print_r($robotRewards, true));
                        // DEBUG DEBUG DEBUG
                    }
                    //error_log('$lastPlayerSession = '. print_r($lastPlayerSession, true));
                    //error_log('$robotSessions = '. print_r($robotSessions, true));
                }
            }
            // If the last player items were provided, update their game session quantities w/ any changes
            if (!empty($worldData['lastPlayerItems'])){
                //error_log('scanning last player items for changes...');
                $mmrpgPlayersIndex = rpg_world::get_index('players');
                $mmrpgItemsIndex = rpg_world::get_index('items');
                if (empty($mmrpgPlayersIndex)){ $mmrpgPlayersIndex = rpg_player::get_index(true); }
                if (empty($mmrpgItemsIndex)){ $mmrpgItemsIndex = rpg_item::get_index(true); }
                $game_session_token = rpg_game::session_token();
                $GAME_SESSION = &$_SESSION[$game_session_token];
                if (!isset($GAME_SESSION['values'])){ $GAME_SESSION['values'] = array(); }
                if (!isset($GAME_SESSION['values']['battle_items'])){ $GAME_SESSION['values']['battle_items'] = array(); }
                $sessionBattleItems = &$GAME_SESSION['values']['battle_items'];
                $sessionBattleItemsCSV = ','.implode(',', array_keys($sessionBattleItems)).',';
                $lastPlayerInfo = $mmrpgPlayersIndex[$lastPlayer];
                $lastPlayerItems = $worldData['lastPlayerItems'];
                $lastPlayerPronoun = 'They';
                if (!empty($lastPlayerInfo['player_gender']) && $lastPlayerInfo['player_gender'] === 'male'){ $lastPlayerPronoun = 'He'; }
                elseif (!empty($lastPlayerInfo['player_gender']) && $lastPlayerInfo['player_gender'] === 'female'){ $lastPlayerPronoun = 'She'; }
                //error_log('$mmrpgItemsIndex = '. print_r($mmrpgItemsIndex, true));
                //error_log('$sessionBattleItems = '. print_r($sessionBattleItems, true));
                //error_log('$sessionBattleItemsCSV = '. print_r($sessionBattleItemsCSV, true));
                //error_log('$lastPlayerItems = '. print_r($lastPlayerItems, true));
                if (!empty($lastPlayerItems)){
                    foreach ($lastPlayerItems AS $item_token => $item_quantity){
                        //error_log('-> checking item token "'.$item_token.'"');
                        if (empty($item_token) || !is_string($item_token)){ continue; }
                        if (strstr($item_token, '__equipped')){ continue; }
                        $num_in_set = false; $set_token = false;
                        if (strstr($item_token, '__')){ $set_token = $item_token; list($item_token, $num_in_set) = explode('__', $set_token, 2); }
                        if (empty($mmrpgItemsIndex[$item_token])){ continue; }
                        $unlock_item_token = !empty($set_token) ? $set_token : $item_token;
                        $item_info = $mmrpgItemsIndex[$item_token];
                        $item_subclass = !empty($item_info['item_subclass']) ? $item_info['item_subclass'] : '';
                        $currentQuantity = isset($sessionBattleItems[$unlock_item_token]) ? intval($sessionBattleItems[$unlock_item_token]) : 0;
                        $realCurrentQuantity = substr_count($sessionBattleItemsCSV, ','.$item_token.',') + substr_count($sessionBattleItemsCSV, ','.$item_token.'__');
                        $newQuantity = intval($item_quantity);
                        $realNewQuantity = $realCurrentQuantity + 1;
                        //error_log('-> $unlock_item_token = '. print_r($unlock_item_token, true));
                        //error_log('-> item_info = '. print_r($item_info, true));
                        //error_log('-> $item_subclass = '. print_r($item_subclass, true));
                        //error_log('-> $currentQuantity = '. print_r($currentQuantity, true));
                        //error_log('-> $realCurrentQuantity = '. print_r($realCurrentQuantity, true));
                        //error_log('-> $newQuantity = '. print_r($newQuantity, true));
                        //error_log('-> $realNewQuantity = '. print_r($realNewQuantity, true));
                        //error_log('-> comparing '.$unlock_item_token.' ('.$currentQuantity.' ?? '.$newQuantity.')');
                        if ($currentQuantity === $newQuantity){ continue; } // no change, skip
                        //error_log('-> '.$unlock_item_token.' quantity was updated! ('.$currentQuantity.' => '.$newQuantity.')');
                        // If this was an event item, make sure we trigger a popup for it
                        //error_log('-> checking if '.$unlock_item_token.' is an event item...');
                        if ($item_subclass === 'event'){
                            //error_log('-> triggering unlock-item popup for '.$unlock_item_token.' ');
                            $is_heart = strstr($item_token, '-heart') ? true : false;
                            $is_own_heart = $is_heart && explode('-', $item_token)[0] === explode('-', $lastPlayer)[1];
                            $is_unique = !$is_heart ? true : false;
                            $is_repeat = $realNewQuantity > 1 ? true : false;
                            $action_text = '{player} found '.($is_repeat ? 'another' : ($is_unique ? 'the' : 'a')).' {item}!';
                            $action_text2 = '';
                            if (!$is_heart || !$is_own_heart){
                                $action_text2 = 'The'.(!$is_repeat ? ' new ' : ' ').'item was added to the inventory!';
                                if ($is_repeat){ $action_text2 .= ' That makes '.$realNewQuantity.' total!'; }
                                }
                            $homebase_field = rpg_player::get_homebase_field($lastPlayer);
                            mmrpg_game_unlock_item($unlock_item_token, array(
                                'event_text' => $action_text.(!empty($action_text2) ? ' <br /> '.$action_text2 : ''),
                                'player_token' => $lastPlayer,
                                'show_images' => array('player'),
                                'field_background' => $homebase_field,
                                'field_foreground' => $homebase_field
                                ));
                        }
                        // Otherwise we can just increment the session value directly
                        else {
                            //error_log('-> saving new '.$unlock_item_token.' quantity to session');
                            $sessionBattleItems[$unlock_item_token] = intval($item_quantity);
                        }
                    }
                }
            }
            // If the last player abilities were provided, update their game session quantities w/ any changes
            if (!empty($worldData['lastPlayerAbilities'])){
                //error_log('scanning last player abilities for changes...');
                $mmrpg_index_players = rpg_world::get_index('players');
                $mmrpg_index_robots = rpg_world::get_index('robots');
                $mmrpg_index_abilities = rpg_world::get_index('abilities');
                if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }
                if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(true); }
                if (empty($mmrpg_index_abilities)){ $mmrpg_index_abilities = rpg_ability::get_index(true); }
                //error_log('$mmrpg_index_players = '. print_r($mmrpg_index_players, true));
                //error_log('$mmrpg_index_abilities = '. print_r($mmrpg_index_abilities, true));
                $game_session_token = rpg_game::session_token();
                $GAME_SESSION = &$_SESSION[$game_session_token];
                if (!isset($GAME_SESSION['values'])){ $GAME_SESSION['values'] = array(); }
                if (!isset($GAME_SESSION['values']['battle_abilities'])){ $GAME_SESSION['values']['battle_abilities'] = array(); }
                $sessionBattleAbilities = &$GAME_SESSION['values']['battle_abilities'];
                $lastPlayerInfo = $mmrpg_index_players[$lastPlayer];
                $lastPlayerRobots = !empty($lastPlayerSession['last_robots']) ? explode(',', $lastPlayerSession['last_robots']) : array();
                $lastPlayerAbilities = $worldData['lastPlayerAbilities'];
                //error_log('$lastPlayer = '. print_r($lastPlayer, true));
                //error_log('$lastPlayerInfo = '. print_r($lastPlayerInfo, true));
                //error_log('$lastPlayerRobots = '. print_r($lastPlayerRobots, true));
                //error_log('$sessionBattleAbilities = '. print_r($sessionBattleAbilities, true));
                //error_log('$lastPlayerAbilities = '. print_r($lastPlayerAbilities, true));
                if (!empty($lastPlayerAbilities)){
                    foreach ($lastPlayerAbilities AS $ability_key => $ability_token){
                        //error_log('-> checking ability token "'.$ability_token.'"');
                        if (empty($ability_token) || !is_string($ability_token)){ continue; }
                        if (empty($mmrpg_index_abilities[$ability_token])){ continue; }
                        $ability_info = $mmrpg_index_abilities[$ability_token];
                        $alreadyUnlocked = in_array($ability_token, $sessionBattleAbilities);
                        //error_log('-> checking '.$ability_token.' $alreadyUnlocked ('.($alreadyUnlocked ? 'true' : 'false').')');
                        if ($alreadyUnlocked){ continue; } // no change, skip
                        //error_log('-> '.$ability_token.' will be unlocked!');
                        mmrpg_game_unlock_ability($lastPlayerInfo, '', array('ability_token' => $ability_token), true);
                        if (!empty($lastPlayerRobots)){
                            foreach ($lastPlayerRobots AS $robot_key => $robot_string){
                                list($robot_id, $robot_token) = explode('_', $robot_string, 2);
                                if (empty($robot_token) || !is_string($robot_token)){ continue; }
                                if (empty($mmrpg_index_robots[$robot_token])){ continue; }
                                $robot_info = $mmrpg_index_robots[$robot_token];
                                $robot_settings = mmrpg_game_robot_settings($lastPlayer, $robot_token);
                                $robot_item = !empty($robot_settings['robot_item']) ? $robot_settings['robot_item'] : '';
                                //error_log('-> checking if robot "'.$robot_token.'" can also unlock '.$ability_token.' (while holding item "'.$robot_item.'")');
                                if (!rpg_robot::has_ability_compatibility($robot_token, $ability_token, $robot_item)){ continue; }
                                //error_log('-> '.$robot_token.' will also unlock '.$ability_token.'!');
                                $lastRobotInfo = array_merge($robot_info, $robot_settings);
                                mmrpg_game_unlock_ability($lastPlayerInfo, $lastRobotInfo, array('ability_token' => $ability_token), false);
                            }
                        }
                    }
                }
            }
            // If world item claim-times were provided, save them to the session
            if (!empty($worldData['lastWorldItems'])){
                if (!isset($WORLD_SESSION['world_items'])){ $WORLD_SESSION['world_items'] = array(); }
                $worldItemStates = &$WORLD_SESSION['world_items'];
                foreach ($worldData['lastWorldItems'] AS $map_token => $item_states){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldItemStates[$map_token])){ $worldItemStates[$map_token] = array(); }
                    $worldItemStates[$map_token] = array_merge($worldItemStates[$map_token], $item_states);
                }
            }
            // If world ability claim-times were provided, save them to the session
            if (!empty($worldData['lastWorldAbilities'])){
                if (!isset($WORLD_SESSION['world_abilities'])){ $WORLD_SESSION['world_abilities'] = array(); }
                $worldAbilityStates = &$WORLD_SESSION['world_abilities'];
                foreach ($worldData['lastWorldAbilities'] AS $map_token => $ability_states){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldAbilityStates[$map_token])){ $worldAbilityStates[$map_token] = array(); }
                    $worldAbilityStates[$map_token] = array_merge($worldAbilityStates[$map_token], $ability_states);
                }
            }
            // If world button states were provided, save them to the session
            if (!empty($worldData['lastWorldButtons'])){
                if (!isset($WORLD_SESSION['world_buttons'])){ $WORLD_SESSION['world_buttons'] = array(); }
                $worldButtonStates = &$WORLD_SESSION['world_buttons'];
                foreach ($worldData['lastWorldButtons'] AS $map_token => $button_states){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldButtonStates[$map_token])){ $worldButtonStates[$map_token] = array(); }
                    $worldButtonStates[$map_token] = array_merge($worldButtonStates[$map_token], $button_states);
                }
            }
            // If world switch states were provided, save them to the session
            if (!empty($worldData['lastWorldSwitches'])){
                if (!isset($WORLD_SESSION['world_switches'])){ $WORLD_SESSION['world_switches'] = array(); }
                $worldSwitchStates = &$WORLD_SESSION['world_switches'];
                foreach ($worldData['lastWorldSwitches'] AS $map_token => $switch_states){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldSwitchStates[$map_token])){ $worldSwitchStates[$map_token] = array(); }
                    $worldSwitchStates[$map_token] = array_merge($worldSwitchStates[$map_token], $switch_states);
                }
            }
            // If world block states were provided, save them to the session
            if (!empty($worldData['lastWorldBlocks'])){
                if (!isset($WORLD_SESSION['world_blocks'])){ $WORLD_SESSION['world_blocks'] = array(); }
                $worldBlockStates = &$WORLD_SESSION['world_blocks'];
                foreach ($worldData['lastWorldBlocks'] AS $map_token => $block_states){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldBlockStates[$map_token])){ $worldBlockStates[$map_token] = array(); }
                    $worldBlockStates[$map_token] = array_merge($worldBlockStates[$map_token], $block_states);
                }
            }
            // If world hazard states were provided, save them to the session
            if (!empty($worldData['lastWorldHazards'])){
                if (!isset($WORLD_SESSION['world_hazards'])){ $WORLD_SESSION['world_hazards'] = array(); }
                $worldHazardStates = &$WORLD_SESSION['world_hazards'];
                foreach ($worldData['lastWorldHazards'] AS $map_token => $hazard_states){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldHazardStates[$map_token])){ $worldHazardStates[$map_token] = array(); }
                    $worldHazardStates[$map_token] = array_merge($worldHazardStates[$map_token], $hazard_states);
                }
            }
            // If world symbol position changes were provided, save them to the session
            if (!empty($worldData['lastWorldSymbols'])){
                $allowed_world_symbol_kinds = array('items', 'abilities', 'encounters', 'pickups', 'blocks', 'hazards'); // for the moment, 'buttons' and 'switches' cannot be moved
                if (!isset($WORLD_SESSION['world_symbols'])){ $WORLD_SESSION['world_symbols'] = array(); }
                $worldSymbolStates = &$WORLD_SESSION['world_symbols'];
                foreach ($worldData['lastWorldSymbols'] AS $map_token => $symbol_kinds){
                    if (!in_array($map_token, $allowed_world_map_tokens)){ continue; }
                    if (!isset($worldSymbolStates[$map_token])){ $worldSymbolStates[$map_token] = array(); }
                    foreach ($symbol_kinds AS $symbol_kind => $symbol_states){
                        if (!in_array($symbol_kind, $allowed_world_symbol_kinds)){ continue; }
                        if (!isset($worldSymbolStates[$map_token][$symbol_kind])){ $worldSymbolStates[$map_token][$symbol_kind] = array(); }
                        $worldSymbolStates[$map_token][$symbol_kind] = array_merge($worldSymbolStates[$map_token][$symbol_kind], $symbol_states);
                    }
                }
            }
            // Check to see if anything has actually changed about the game session
            //$new_world_session_hash = md5(serialize($WORLD_SESSION));
            //$game_session_changed = $world_session_hash !== $new_world_session_hash ? true : false;
            //error_log('$new_world_session_hash = '.$new_world_session_hash);
            //error_log('rpg_world::save_world_data_to_session() $game_session_changed = '.($game_session_changed ? 'true' : 'false'));
            //error_log('World data saved successfully for player "'.$lastPlayer.'"!');
            //error_log('World data saved successfully for player "'.$lastPlayer.'" with world "'.$lastPlayerSession['last_world'].'" and position "'.$lastPlayerSession['last_position'].'"');
            //error_log('$WORLD_SESSION = '.print_r($WORLD_SESSION, true));
            /*
            // DEBUG DEBUG DEBUG
            // Show a debug-popup event to prove the game was saved
            $show_debug_popup = false;
            if ($show_debug_popup
                && !empty($game_session_changed)){
                $game_session_token = rpg_game::session_token();
                $temp_canvas_markup = '';
                $temp_canvas_markup .= '<div class="sprite" style="background-image: url(images/fields/field/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; filter: blur(2px) brightness(0.7);"></div>';
                $temp_console_markup = '';
                $temp_console_markup .= '<p class="headline type nature"><strong>Game Saved!</strong></p>';
                $temp_console_markup .= '<div class="inset_panel compact"><p style="text-align: center; margin: 5px auto;">Your progress has been saved.</p></div>';
                $_SESSION[$game_session_token]['EVENTS'][] = array(
                    'canvas_markup' => $temp_canvas_markup,
                    'console_markup' => $temp_console_markup,
                    'player_token' => $lastPlayer,
                    'event_type' => 'debug'
                    );
                }
            */
        }
        // Return true on success
        return true;
    }

    // -- WORLD MAP METHODS -- //

    // Define a function for loading a given map's data from the filesystem
    public static function load_map_data($world_map_token, &$world_data_parsed = array(), &$map_data_parsed = array()){
        //error_log('load_map_data() called!');
        if (empty($world_map_token)){ error_log('rpg_world::load_map_data() error - missing world-map token!'); return false; }
        if (!strstr($world_map_token, '__')){ error_log('rpg_world::load_map_data() error - invalid world-map token "'.$world_map_token.'"!'); return false; }
        list($world_token, $map_token) = explode('__', $world_map_token);
        // first we collect data for the parent world itself
        $world_basedir = self::$worldmap_basedir.self::$worldmap_basepath;
        $world_filename = $world_token.'.world';
        $world_filedir = $world_basedir.$world_filename;
        if (!file_exists($world_filedir)){
            //error_log('load_map_data() world file not found "'.$world_filedir.'"!');
            return false;
            }
        $world_data_raw = file_get_contents($world_filedir);
        if (empty($world_data_raw)){
            //error_log('load_map_data() world file empty "'.$world_filedir.'"!');
            return false;
            }
        $world_data_array = explode("\n", trim($world_data_raw));
        $world_data_vars = array();
        foreach ($world_data_array AS $line){
            $line = trim($line);
            // Ignore empty lines and comments
            if (empty(trim($line))){ continue; }
            else if (strpos($line, '#') === 0){ continue; } // Ignore comments
            else if (strpos($line, '//') === 0){ continue; } // Ignore comments
            // If this is a variable line, pull it (ie. @foo = bar)
            if (strpos($line, '@') === 0){
                if (!strstr($line, '=')){ continue; }
                $line = preg_replace('/\s+\=\s+/i', '=', trim($line, '@ '));
                list($name, $value) = explode('=', $line, 2);
                if (strstr($name, '[') && strstr($name, ']')){
                    $key = substr($name, strpos($name, '[') + 1, -1);
                    $name = substr($name, 0, strpos($name, '['));
                    if (!isset($world_data_vars[$name])){ $world_data_vars[$name] = array(); }
                    if ($key === ''){ $world_data_vars[$name][] = $value; }
                    else { $world_data_vars[$name][$key] = $value; }
                    } else {
                    $world_data_vars[$name] = $value;
                    }
                continue;
                }
        }
        //error_log('load_map_data() loaded world data vars: '.print_r($world_data_vars, true));
        // then we collect data for the actual map within the world
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
        $map_tiles_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+),(-?[.0-9]+)(,\s?[-_a-z0-9,]+)?\)$/i'; // syntax: name(key,x,y) ie. void(0,20,20) => name:void, key:0, x:20, y:20
        $map_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)(,\s?[-_a-z0-9,]+)?\)$/i'; // syntax: name(x,y[,flag1,flag2,etc.]) ie. spawn(4,4) or spawn(4,4,other-area-2) => name:spawn, x:4, y:4
        $map_listval_custval_regex = '/^([.a-z0-9-_\+]+)\(([\+\:\.\,a-z0-9-_]+)\)/i'; // syntax: name(token1,token2,token3) ie. spawn(token1,token2,token3) => name:spawn, tokens:token1,token2,token3
        //$map_other_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+)\)$/i'; // syntax: name(x,y) ie. spawn(4,4) => name:spawn, x:4, y:4
        static $map_custval_parser;
        if (!$map_custval_parser){
            $map_custval_parser = function($custval_kind, $raw_strings, $include_keys = false) use ($map_tiles_custval_regex, $map_other_custval_regex, $map_listval_custval_regex){
                if (empty($raw_strings)){ return array(); }
                $parsed_keys = array();
                $parsed_tiles = array();
                foreach ($raw_strings AS $line){
                    $line = trim(str_replace(' ', '', $line));
                    if (empty($line)){ continue; }
                    //error_log('parsing custval line: '.$line);
                    $is_tile_custval = preg_match($map_tiles_custval_regex, $line);
                    $is_other_custval = preg_match($map_other_custval_regex, $line);
                    $is_listval_custval = preg_match($map_listval_custval_regex, $line);
                    if (!$is_tile_custval && !$is_other_custval && !$is_listval_custval){ continue; }
                    if ($is_tile_custval){
                        $exploded = explode('/', preg_replace($map_tiles_custval_regex, '$1/$2/$3/$4', $line), 4);
                        //error_log($custval_kind.' tile $exploded ='.print_r($exploded, true));
                        list($name, $k, $x, $y) = $exploded;
                        $parsed_tiles[$name] = array($x, $y);
                        $parsed_keys[intval($k)] = $name;
                        continue;
                        }
                    if ($is_other_custval){
                        $exploded = explode('/', preg_replace($map_other_custval_regex, '$1/$2/$3/$4', $line), 4);
                        //error_log($custval_kind.' other $exploded ='.print_r($exploded, true));
                        list($name, $x, $y) = $exploded;
                        $parsed_tiles[$name] = array($x, $y);
                        if (!empty($exploded[3])){ $parsed_tiles[$name] = array_merge($parsed_tiles[$name], explode(',', trim($exploded[3], ','))); }
                        continue;
                        }
                    if ($is_listval_custval){
                        $exploded = explode('/', preg_replace($map_listval_custval_regex, '$1/$2', $line), 2);
                        //error_log($custval_kind.' list $exploded ='.print_r($exploded, true));
                        list($name, $tokens) = $exploded;
                        $tokens = explode(',', $tokens);
                        if (empty($tokens) || count($tokens) < 1){ continue; }
                        foreach ($tokens AS $token){
                            $token = trim($token, ',');
                            if (empty($token)){ continue; }
                            if (strstr($token, '...')){
                                $range = self::parse_position_range($token);
                                foreach ($range AS $pos){ $parsed_tiles[$name][] = $pos; }
                                continue;
                                }
                            $parsed_tiles[$name][] = $token;
                            }
                        continue;
                        }
                    }
                if ($include_keys){ $parsed_tiles['keys'] = $parsed_keys; }
                return $parsed_tiles;
                };
            }
        //error_log('raw $world_data_vars(before) = '.print_r($world_data_vars, true));
        $world_data_vars['areas'] = $map_custval_parser('areas', $world_data_vars['areas']);
        //error_log('$world_data_vars(after) = '.print_r($world_data_vars, true));
        //error_log('raw $map_data_vars(before) = '.print_r($map_data_vars, true));
        $map_data_vars['world'] = isset($world_data_vars['token']) ? $world_data_vars['token'] : '';
        $map_data_vars['world_name'] = isset($world_data_vars['name']) ? $world_data_vars['name'] : '';
        $map_data_vars['token'] = isset($map_data_vars['token']) ? $map_data_vars['token'] : '';
        $map_data_vars['name'] = isset($map_data_vars['name']) ? $map_data_vars['name'] : '';
        $map_data_vars['type'] = isset($map_data_vars['type']) ? $map_data_vars['type'] : '';
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
        $map_data_vars['blocks'] = isset($map_data_vars['blocks']) ? $map_data_vars['blocks'] : array();
        $map_data_vars['hazards'] = isset($map_data_vars['hazards']) ? $map_data_vars['hazards'] : array();
        $map_data_vars['field'] = isset($map_data_vars['field']) ? $map_data_vars['field'] : '';
        $map_data_vars['music'] = isset($map_data_vars['music']) ? $map_data_vars['music'] : array();
        $map_data_vars['terrain'] = isset($map_data_vars['terrain']) ? $map_data_vars['terrain'] : array();
        $map_data_vars['encounters'] = isset($map_data_vars['encounters']) ? $map_data_vars['encounters'] : '';
        $map_data_vars['pickups'] = isset($map_data_vars['pickups']) ? $map_data_vars['pickups'] : '';
        $map_data_vars['habitats'] = isset($map_data_vars['habitats']) ? $map_data_vars['habitats'] : array();
        foreach (self::$static_encounter_types AS $type){ $map_data_vars[$type] = isset($map_data_vars[$type]) ? $map_data_vars[$type] : array(); }
        $map_data_vars['items'] = isset($map_data_vars['items']) ? $map_data_vars['items'] : array();
        $map_data_vars['abilities'] = isset($map_data_vars['abilities']) ? $map_data_vars['abilities'] : array();
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
        if (empty($map_data_vars['music'])){ $map_data_vars['music'] = array(); }
        if (!empty($map_data_vars['encounters'])){ $map_data_vars['encounters'] = explode(',', str_replace(' ', '', $map_data_vars['encounters'])); }
        if (!empty($map_data_vars['pickups'])){ $map_data_vars['pickups'] = explode(',', str_replace(' ', '', $map_data_vars['pickups'])); }
        $map_data_vars['tiles'] = $map_custval_parser('tiles', $map_data_vars['tiles'], true);
        $map_data_vars['groups'] = $map_custval_parser('groups', $map_data_vars['groups']);
        $map_data_vars['sprites'] = $map_custval_parser('sprites', $map_data_vars['sprites']);
        $map_data_vars['events'] = $map_custval_parser('events', $map_data_vars['events']);
        $map_data_vars['portals'] = $map_custval_parser('portals', $map_data_vars['portals']);
        $map_data_vars['buttons'] = $map_custval_parser('buttons', $map_data_vars['buttons']);
        $map_data_vars['switches'] = $map_custval_parser('switches', $map_data_vars['switches']);
        $map_data_vars['blocks'] = $map_custval_parser('blocks', $map_data_vars['blocks']);
        $map_data_vars['hazards'] = $map_custval_parser('hazards', $map_data_vars['hazards']);
        $map_data_vars['terrain'] = $map_custval_parser('terrain', $map_data_vars['terrain']);
        $map_data_vars['music'] = $map_custval_parser('music', $map_data_vars['music']);
        $map_data_vars['habitats'] = $map_custval_parser('habitats', $map_data_vars['habitats']);
        foreach (self::$static_encounter_types AS $type){ $map_data_vars[$type] = $map_custval_parser($type, $map_data_vars[$type]); }
        $map_data_vars['items'] = $map_custval_parser('items', $map_data_vars['items']);
        $map_data_vars['abilities'] = $map_custval_parser('abilities', $map_data_vars['abilities']);
        // Add collected world data to its parsed data array
        $world_data_parsed = array();
        $world_data_parsed['token'] = $world_data_vars['token']; unset($world_data_vars['token']);
        $world_data_parsed['name'] = $world_data_vars['name']; unset($world_data_vars['name']);
        $world_data_parsed['size'] = $world_data_vars['size']; unset($world_data_vars['size']);
        $world_data_parsed['sheet'] = $world_data_vars['sheet']; unset($world_data_vars['sheet']);
        $world_data_parsed['image'] = $world_data_vars['image']; unset($world_data_vars['image']);
        $world_data_parsed['areas'] = $world_data_vars['areas']; unset($world_data_vars['areas']);
        if (!empty($world_data_vars)){ $world_data_parsed['vars'] = $world_data_vars; }
        //error_log('$world_data_parsed = '.print_r($world_data_parsed, true));
        // Add collected map data to its parsed map data
        $map_data_parsed = array();
        $map_data_parsed['world'] = $map_data_vars['world']; unset($map_data_vars['world']);
        $map_data_parsed['world_name'] = $map_data_vars['world_name']; unset($map_data_vars['world_name']);
        $map_data_parsed['token'] = $map_data_vars['token']; unset($map_data_vars['token']);
        $map_data_parsed['name'] = $map_data_vars['name']; unset($map_data_vars['name']);
        $map_data_parsed['type'] = $map_data_vars['type']; unset($map_data_vars['type']);
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
        $map_data_parsed['blocks'] = $map_data_vars['blocks']; unset($map_data_vars['blocks']);
        $map_data_parsed['hazards'] = $map_data_vars['hazards']; unset($map_data_vars['hazards']);
        $map_data_parsed['field'] = $map_data_vars['field']; unset($map_data_vars['field']);
        $map_data_parsed['music'] = $map_data_vars['music']; unset($map_data_vars['music']);
        $map_data_parsed['terrain'] = $map_data_vars['terrain']; unset($map_data_vars['terrain']);
        $map_data_parsed['encounters'] = $map_data_vars['encounters']; unset($map_data_vars['encounters']);
        $map_data_parsed['pickups'] = $map_data_vars['pickups']; unset($map_data_vars['pickups']);
        $map_data_parsed['habitats'] = $map_data_vars['habitats']; unset($map_data_vars['habitats']);
        foreach (self::$static_encounter_types AS $type){ $map_data_parsed[$type] = $map_data_vars[$type]; unset($map_data_vars[$type]); }
        $map_data_parsed['items'] = $map_data_vars['items']; unset($map_data_vars['items']);
        $map_data_parsed['abilities'] = $map_data_vars['abilities']; unset($map_data_vars['abilities']);
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
        $map_offset_styles = 'top: 0px; left: 0px; z-index: 1; ';
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
        // Let's pre-parsed the groups array in case any shorthand was used so we don't have to do it later
        if (!empty($map_data_parsed['groups'])){
            foreach ($map_data_parsed['groups'] AS $group_key => $group_tiles){
                if (empty($group_tiles)){ continue; }
                //error_log('-> $group_key = '.print_r($group_key, true));
                //error_log('-> $group_tiles = '.print_r($group_tiles, true));
                foreach ($group_tiles AS $tile_key => $tile_string){
                    //error_log('--> $tile_key = '.print_r($tile_key, true));
                    //error_log('--> $tile_string = '.print_r($tile_string, true));
                    // If this value is using range syntax, parse it into a range
                    if (strstr($tile_string, '...')){
                        //error_log('---> parsing range syntax for tile string "'.$tile_string.'"');
                        // Parse the range tiles out of the tile string
                        $range_tiles = self::parse_position_range($tile_string);
                        // Now we can replace the tile string with the range tiles
                        $group_tiles[$tile_key] = '';
                        $group_tiles = array_merge($group_tiles, $range_tiles); // merge the range tiles into the group tiles
                    }
                }
                $group_tiles = array_values(array_filter($group_tiles));
                //error_log('---> '.$group_key.' $group_tiles = '. print_r($group_tiles, true));
                $map_data_parsed['groups'][$group_key] = $group_tiles; // update the group tiles
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
        $sheet_tiles_custval_regex = '/^([.a-z0-9-_]+)\((-?[.0-9]+),(-?[.0-9]+),(-?[.0-9]+)(,\s?[-_a-z0-9,!]+)?\)$/i'; // syntax: name(key,x,y) ie. void(0,20,20) => name:void, key:0, x:20, y:20
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
                        $exploded = explode(',', preg_replace($sheet_tiles_custval_regex, '$1,$2,$3,$4'.'$5', $line), 5);
                        list($name, $k) = $exploded;
                        $values = array_slice($exploded, 2);
                        //error_log('raw $line ='.print_r($line, true));
                        //error_log('tile $exploded ='.print_r($exploded, true));
                        //error_log('$name ='.print_r($name, true).' $k ='.print_r($k, true).' $values ='.print_r($values, true));
                        $parsed_tiles[$name] = $values;
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

    // Define a function for returning all the available cells on a given map that don't have anything on them yet
    public static function get_available_cells($map_data, $exclude_existing = true, $ignore_existing = array()){
        //error_log('rpg_world::get_available_cells() called w/ $exclude_existing = '.print_r($exclude_existing, true).' & $ignore_existing = '.print_r($ignore_existing, true));
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
        // Now let's loop through blocks and remove spaces that have active blocks on them
        if (!empty($map_data['blocks']) && is_array($map_data['blocks'])){
            foreach ($map_data['blocks'] AS $block_name => $block_data){
                if (empty($block_data) || !is_array($block_data)){ continue; }
                if (in_array('removed', $block_data)){ continue; } // don't block if it was removed
                $pos = $block_data[0];
                //error_log('-> removing block position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // Now let's loop through hazards and remove spaces that have active hazards on them
        if (!empty($map_data['hazards']) && is_array($map_data['hazards'])){
            foreach ($map_data['hazards'] AS $hazard_name => $hazard_data){
                if (empty($hazard_data) || !is_array($hazard_data)){ continue; }
                if (in_array('removed', $hazard_data)){ continue; } // don't block if it was removed
                $pos = $hazard_data[0];
                //error_log('-> removing hazard position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // Now let's loop through static mechas, masters, bosses, etc. to make sure those aren't used either
        $static_encounter_types = self::$static_encounter_types;
        foreach ($static_encounter_types AS $type => $map_key){
            if (!empty($map_data[$map_key]) && is_array($map_data[$map_key])){
                foreach ($map_data[$map_key] AS $encounter_name => $encounter_data){
                    if (empty($encounter_data) || !is_array($encounter_data)){ continue; }
                    $pos = $encounter_data[0];
                    //error_log('-> removing '.$type.' position "'.$pos.'" from available cells');
                    unset($available_cells[$pos]);
                }
            }
        }
        // Now let's loop through any items and remove spaces that have item pickups on them
        if (!empty($map_data['items']) && is_array($map_data['items'])){
            foreach ($map_data['items'] AS $item_name => $item_data){
                if (empty($item_data) || !is_array($item_data)){ continue; }
                $pos = $item_data[0];
                //error_log('-> removing item position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // Now let's loop through any abilities and remove spaces that have ability pickups on them
        if (!empty($map_data['abilities']) && is_array($map_data['abilities'])){
            foreach ($map_data['abilities'] AS $ability_name => $ability_data){
                if (empty($ability_data) || !is_array($ability_data)){ continue; }
                $pos = $ability_data[0];
                //error_log('-> removing ability position "'.$pos.'" from available cells');
                unset($available_cells[$pos]);
            }
        }
        // If there are any groups named or prefixed with "no-encounters", loop through the cells and add them too
        //if (!empty($map_data['groups']) && is_array($map_data['groups']) && isset($map_data['groups']['no-encounters'])){
        if (!empty($map_data['groups']) && is_array($map_data['groups'])){
            $no_encounters = array();
            $map_groups = $map_data['groups'];
            $map_groups_keys = array_keys($map_groups);
            $no_encounters_keys = array_filter($map_groups_keys, function($v){ return (strtolower($v) === 'no-encounters' || strtolower(substr($v, 0, 14)) === 'no-encounters_'); });
            foreach ($no_encounters_keys AS $group_key){ $no_encounters = array_merge($no_encounters, $map_data['groups'][$group_key]); }
            $no_encounters = array_unique($no_encounters);
            //$no_encounters = $map_data['groups']['no-encounters'];
            //error_log('-> $no_encounters_keys = '.print_r($no_encounters_keys, true));
            //error_log('-> $no_encounters (list) = '.print_r($no_encounters, true));
            if (!empty($no_encounters) && is_array($no_encounters)){
                $no_encounters_groups = array();
                foreach ($no_encounters AS $key => $pos){
                    if (preg_match('/^([0-9]+)\-([0-9]+)$/i', $pos)){ continue; }
                    //error_log('-> no-encounters position "'.$pos.'" might be group...');
                    if (!isset($map_data['groups'][$pos])){ continue; }
                    elseif (empty($map_data['groups'][$pos])){ continue; }
                    //error_log('-> ... no-encounters position "'.$pos.'" IS a group w/ '.count($map_data['groups'][$pos]).' items!');
                    //error_log('-> $map_data[groups]['.$pos.'] = '.print_r($map_data['groups'][$pos], true));
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
        // If we're allowed to check existing, let's exclude any encounters or pickups already-spawned and using any of these
        if ($exclude_existing){
            //error_log('-> checking existing encounters and pickups to exclude from available cells...');
            //error_log('-> $exclude_existing = '.($exclude_existing ? 'true' : 'false'));
            //error_log('-> $ignore_existing = '.print_r($ignore_existing, true));
            $WORLD_SESSION = self::get_session();
            $world_token = $map_data['world'];
            $map_token = $map_data['token'];
            $world_map_token = $world_token.'__'.$map_token;
            if (!in_array('encounters', $ignore_existing)){
                $world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
                $world_map_encounters = !empty($world_encounters[$world_map_token]) ? $world_encounters[$world_map_token] : array();
                $world_encounter_cells = array();
                if (!empty($world_map_encounters)){
                    foreach ($world_map_encounters AS $key => $encounter){
                        list($kind, $token, $alt, $position, $namekey, $label) = $encounter;
                        if (!rpg_battle::has_index_info($namekey)){ continue; }
                        $world_encounter_cells[] = $position;
                        }
                    }
                //error_log('-> $world_map_encounters('.count($world_map_encounters).') = '.print_r($world_map_encounters, true));
                //error_log('-> $world_encounter_cells('.count($world_encounter_cells).') = '.print_r($world_encounter_cells, true));
                foreach ($world_encounter_cells AS $position){ unset($available_cells[$position]); }
            }
            if (!in_array('pickups', $ignore_existing)){
                $world_pickups = !empty($WORLD_SESSION['world_pickups']) ? $WORLD_SESSION['world_pickups'] : array();
                $world_map_pickups = !empty($world_pickups[$world_map_token]) ? $world_pickups[$world_map_token] : array();
                $world_pickup_cells = array();
                if (!empty($world_map_pickups)){
                    foreach ($world_map_pickups AS $key => $pickup){
                        list($kind, $token, $position, $namekey, $label) = $pickup;
                        $world_pickup_cells[] = $position;
                        }
                    }
                //error_log('-> $world_map_pickups('.count($world_map_pickups).') = '.print_r($world_map_pickups, true));
                //error_log('-> $world_pickup_cells('.count($world_pickup_cells).') = '.print_r($world_pickup_cells, true));
                foreach ($world_pickup_cells AS $position){ unset($available_cells[$position]); }
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
        $mmrpg_index_abilities = self::get_index('abilities');
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

        // Pull the list of robots unlocked already overall, so that we do not need to keep checking the session
        $all_unlocked_robots = array();
        $all_unlocked_robots = mmrpg_prototype_robots_unlocked('', true);

        // Pull the list of abilities unlocked already overall, so that we do not need to keep checking the session
        $all_unlocked_abilities = array();
        mmrpg_prototype_abilities_unlocked('', '', $all_unlocked_abilities);

        // Calculate the available encounter cells based on the map data and define a var to hold used encounter cells later
        $world_map_encounters = array();
        $available_encounter_cells = self::get_available_cells($map_data_parsed, true, array('encounters'));
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
            $robot_name = $robot_info['robot_name'];
            $robot_class = $robot_info['robot_class'];
            $robot_level = mt_rand($levels_matrix[$robot_class]['min'], $levels_matrix[$robot_class]['max']);
            $robot_item = mt_rand(1, 100) <= 50 ? $get_random_allowed_item() : '';
            $robot_label = $robot_info['robot_name'].' (Lv. '.$robot_level.')';
            $battle_token = $world_battle_token.'_random-robot-'.($robot_key + 1);
            $battle_name = $map_name.' '.ucfirst($robot_class).' Battle';
            $battle_description = 'Defeat '.$robot_name.' in battle!';
            if ($robot_class === 'mecha'){ $battle_description = 'Defeat the '.$robot_name.' support mecha in battle!'; }
            elseif ($robot_class === 'master'){ $battle_description = 'Defeat the robot master '.$robot_name.' in battle!'; }
            elseif ($robot_class === 'boss'){ $battle_description = 'Defeat '.$robot_name.' the fortress boss in battle!'; }
            $battle_background = $map_field_token;
            $battle_foreground = !empty($available_encounter_terrain[$robot_pos_terrain]) ? $available_encounter_terrain[$robot_pos_terrain][0] : $map_field_token;
            $battle_field = $battle_background !== $battle_foreground ? $battle_background.'/'.$battle_foreground : $battle_background;
            $battle_music = $map_field_music;
            if (!empty($map_data_parsed['music'])){
                $possible_music = $map_data_parsed['music'];
                if (!empty($possible_music[$robot_class.'-battle'])){ $possible_music = $possible_music[$robot_class.'-battle'][0]; }
                elseif (!empty($possible_music['battle'])){ $possible_music = $possible_music['battle'][0]; }
                }
            $battle_turns = $rewards_matrix[$robot_class]['turns'];
            $battle_zenny = $rewards_matrix[$robot_class]['zenny'];
            $world_map_encounters[] = array('robot/'.$robot_class, $robot_token, '', $robot_pos, $battle_token, $robot_label);
            $battle_omega = rpg_mission::generate_mission($this_prototype_data, $battle_token, array(
                'token' => $battle_token,
                'name' => $battle_name,
                'description' => $battle_description,
                'turns' => $battle_turns,
                'zenny' => $battle_zenny,
                'field' => $battle_field,
                'music' => $battle_music,
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

        // STATIC ENCOUNTERS (w/ Mechas, Masters, Bosses, etc.)
        $static_encounter_key = array();
        $static_encounter_types = self::$static_encounter_types;
        foreach ($static_encounter_types AS $encounter_class => $encounter_xclass){
            if (!empty($map_data_parsed[$encounter_xclass])){
                $static_encounters = $map_data_parsed[$encounter_xclass];
                if (!isset($static_encounter_key[$encounter_class])){ $static_encounter_key[$encounter_class] = 0; }
                //error_log('$static_encounters = '.print_r($static_encounters, true));
                foreach ($static_encounters AS $key => $robot_data){
                    //error_log('-> next $robot_key = '.print_r($robot_key, true));
                    //error_log('-> next $robot_data = '.print_r($robot_data, true));
                    //error_log('-> next $robot_key = '.$robot_key.PHP_EOL.'---> w/ $robot_data = '.print_r($robot_data, true));
                    $robot_key = $static_encounter_key[$encounter_class]++;
                    $robot_pos = $robot_data[0]; unset($robot_data[0]);
                    $robot_token = !empty($robot_data[1]) ? $robot_data[1] : 'robot'; unset($robot_data[1]);
                    $level = !empty($robot_data[2]) ? $robot_data[2] : ''; unset($robot_data[2]);
                    $flags = !empty($robot_data[3]) ? $robot_data[3] : ''; unset($robot_data[3]);
                    $effect = !empty($robot_data[4]) ? $robot_data[4] : ''; unset($robot_data[4]);
                    $target = !empty($robot_data[5]) ? $robot_data[5] : ''; unset($robot_data[5]);
                    $value = !empty($robot_data[6]) ? $robot_data[6] : ''; unset($robot_data[6]);
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
                    $robot_gender = $robot_info['robot_gender'];
                    //error_log('-> $robot_info = '.print_r($robot_info, true));
                    if (!empty($level) && is_numeric($level) && intval($level) > 0){ $robot_level = $level; }
                    else { $robot_level = mt_rand($levels_matrix[$robot_class]['min'], $levels_matrix[$robot_class]['max']); }
                    $robot_item = mt_rand(1, 100) <= 50 ? $get_random_allowed_item() : '';
                    $robot_name = $robot_info['robot_name'];
                    $robot_label = $robot_name.' (Lv. '.$robot_level.')';
                    $robot_flags = array();
                    $battle_token = $world_battle_token.'_static-'.$encounter_class.'-'.($robot_key + 1);
                    if ($encounter_class === 'rescue'){
                        $battle_name = $map_name.' '.ucfirst($robot_class).' Rescue';
                        $rescue_pronoun = rpg_robot::get_robot_pronoun($robot_class, $robot_gender, 'object');
                        $battle_description = 'Protect '.$robot_name.' from falling in battle to rescue '.$rescue_pronoun.'!';
                        } else {
                        $battle_name = $map_name.' '.ucfirst($robot_class).' Battle';
                        $battle_description = 'Defeat '.$robot_name.' in battle!';
                        if ($encounter_class === 'mecha'){ $battle_description = 'Defeat the '.$robot_name.' support mecha in battle!'; }
                        elseif ($encounter_class === 'master'){ $battle_description = 'Defeat the robot master '.$robot_name.' in battle!'; }
                        elseif ($encounter_class === 'boss'){ $battle_description = 'Defeat '.$robot_name.' the fortress boss in battle!'; }
                        }
                    $battle_background = $map_field_token;
                    $battle_foreground = !empty($available_encounter_terrain[$robot_pos_terrain]) ? $available_encounter_terrain[$robot_pos_terrain][0] : $map_field_token;
                    $battle_field = $battle_background !== $battle_foreground ? $battle_background.'/'.$battle_foreground : $battle_background;
                    $battle_music = $map_field_music;
                    if (!empty($map_data_parsed['music'])){
                        $possible_music = $map_data_parsed['music'];
                        if (!empty($possible_music[$encounter_class.'-battle'])){ $possible_music = $possible_music[$encounter_class.'-battle'][0]; }
                        elseif (!empty($possible_music['battle'])){ $possible_music = $possible_music['battle'][0]; }
                        }
                    $battle_turns = $rewards_matrix[$robot_class]['turns'];
                    $battle_zenny = $rewards_matrix[$robot_class]['zenny'];
                    $battle_rewards = array();
                    $battle_flags = array();
                    $battle_flags['world_battle'] = true;
                    $battle_flags['remove_on_complete'] = true;
                    // If this is a master battle, make sure we add the necessary robot and ability rewards to this battle
                    if ($robot_class === 'master'
                        && $encounter_class !== 'rescue'){
                        if (!mmrpg_prototype_robot_unlocked('', $robot_token)
                            && !empty($robot_info['robot_flag_published'])
                            && !empty($robot_info['robot_flag_complete'])
                            && !empty($robot_info['robot_flag_unlockable'])){
                            //error_log('-> '.$robot_token.' is a master that is not unlocked yet!');
                            if (!isset($battle_rewards['robots'])){ $battle_rewards['robots'] = array(); }
                            //$battle_rewards['robots'][] = array('token' => $robot_token, 'level' => $robot_level, 'experience' => 999);
                            $battle_rewards['robots'][] = array('token' => $robot_token, 'level' => 'auto', 'experience' => 'auto');
                            //error_log('-> ... adding '.$robot_token.' to the battle rewards!');
                        }
                        $master_abilities = !empty($robot_info['robot_rewards']['abilities']) ? $robot_info['robot_rewards']['abilities'] : array();
                        if (!empty($master_abilities)){
                            //error_log('-> '.$robot_token.' is a master that has abilities to unlock!');
                            $master_abilities = array_map(function($value){
                                return $value['token'];
                                }, $master_abilities);
                            $master_abilities = array_filter($master_abilities, function($token) use ($mmrpg_index_abilities){
                                $info = $mmrpg_index_abilities[$token];
                                if (empty($info)){ return false; }
                                elseif (empty($info['ability_flag_published'])){ return false; }
                                elseif (empty($info['ability_flag_complete'])){ return false; }
                                elseif (empty($info['ability_flag_unlockable'])){ return false; }
                                return true;
                                });
                            //error_log('-> $master_abilities = '.print_r($master_abilities, true));
                            foreach ($master_abilities AS $ability){
                                if (in_array($ability, $all_unlocked_abilities)){ continue; }
                                if (!isset($battle_rewards['abilities'])){ $battle_rewards['abilities'] = array(); }
                                $battle_rewards['abilities'][] = array('token' => $ability);
                                //error_log('-> ... adding '.$ability.' to the battle rewards!');
                            }
                        }
                    }
                    // If this is a rescue battle, the robot should be added to the rewards too
                    if ($encounter_class === 'rescue'){
                        $battle_turns = 1; // one turn goal for rescue battles
                        $battle_zenny *= 2; // double the zenny reward for rescue battles
                        $battle_flags['rescue_battle'] = true;
                        $robot_flags['robot_is_rescue'] = true;
                        $robot_flags['is_friendly'] = true;
                        $robot_label = $robot_name.' (Help!)';
                        if (!mmrpg_prototype_robot_unlocked('', $robot_token)){
                            //error_log('-> '.$robot_token.' is a rescue that is not unlocked yet!');
                            if (!isset($battle_rewards['robots'])){ $battle_rewards['robots'] = array(); }
                            //$battle_rewards['robots'][] = array('token' => $robot_token, 'level' => 'auto', 'experience' => 'auto');
                            $battle_rewards['robots'][] = array('token' => $robot_token, 'level' => $robot_level, 'experience' => 999);
                            //error_log('-> ... adding '.$robot_token.' to the battle rewards!');
                        }
                    }
                    //error_log('-> generating '.$robot_class.' battle "'.$battle_token.'" ('.$battle_name.')');
                    $world_map_encounters[] = array('robot/'.$encounter_class, $robot_token, '', $robot_pos, $battle_token, $robot_label);
                    $battle_omega = rpg_mission::generate_mission($this_prototype_data, $battle_token, array(
                        'token' => $battle_token,
                        'name' => $battle_name,
                        'description' => $battle_description,
                        'field' => $battle_field,
                        'music' => $battle_music,
                        'turns' => $battle_turns,
                        'zenny' => $battle_zenny,
                        'target' => array('robots' => array(array(
                            'token' => $robot_token,
                            'level' => $robot_level,
                            'item' => $robot_item,
                            'flags' => $robot_flags,
                            ))),
                        'rewards' => $battle_rewards,
                        'flags' => $battle_flags,
                        ), true);
                    //error_log('-> $battle_omega = '.print_r($battle_omega, true));
                }
            }
        }

        // Return the generated encounters array
        return $world_map_encounters;
    }

    // Define a function for generating a bunch of world map item pickups given parsed map data and some config
    public static function generate_worldmap_pickups($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::generate_worldmap_pickups() called!');

        // Collect any indexes we're gonna need for this part
        $mmrpg_index_items = self::get_index('items');
        $mmrpg_index_fields = self::get_index('fields');

        // Collect the map's field token and mecha pickups
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $world_pickup_token = 'world-pickup_'.str_replace('__', '-', $world_map_token);
        $map_name = !empty($map_data_parsed['name']) ? $map_data_parsed['name'] : 'Undefined';
        $map_level = !empty($map_data_parsed['level']) ? $map_data_parsed['level'] : 1;
        $map_pickups = !empty($map_data_parsed['pickups']) ? $map_data_parsed['pickups'] : array();
        //$map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
        //$map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
        //$map_field_background = !empty($map_field_info['field_background']) ? $map_field_info['field_background'] : 'field';
        //$map_field_foreground = !empty($map_field_info['field_foreground']) ? $map_field_info['field_foreground'] : 'field';
        //error_log('$world_map_token = '.print_r($world_map_token, true));
        //error_log('$map_pickups = '.print_r($map_pickups, true));
        //error_log('$map_field_token = '.print_r($map_field_token, true));
        //error_log('$map_field_info = '.print_r($map_field_info, true));
        //error_log('$map_field_background = '.print_r($map_field_background, true));
        //error_log('$map_field_foreground = '.print_r($map_field_foreground, true));
        if (empty($map_pickups)){ return array(); }

        // Calculate the available pickup cells based on the map data and define a var to hold used pickup cells later
        $world_map_pickups = array();
        $available_pickup_cells = self::get_available_cells($map_data_parsed, true, array('pickups'));
        $available_pickup_terrain = !empty($map_data_parsed['terrain']) ? $map_data_parsed['terrain'] : array();
        $used_pickup_cells = array();

        // RANDOM PICKUPS (within defined limits)
        $allowed_random_pickups = $map_pickups;
        $max_random_pickups = ceil($available_pickup_cells['total'] * 0.10); // TODO: make this configurable in the map file
        //error_log('$allowed_random_pickups = '.print_r($allowed_random_pickups, true));
        //error_log('$available_pickup_terrain = '.print_r($available_pickup_terrain, true));
        //error_log('$available_pickup_cells = '.print_r($available_pickup_cells, true));
        //error_log('$max_random_pickups = '.print_r($max_random_pickups, true));
        $ratios = array();
        foreach ($allowed_random_pickups AS $key => $item){
            $ratio = strstr($item, '(') && strstr($item, ')') ? explode('(', str_replace(')', '', $item)) : array($item, 1);
            $item = $ratio[0]; $value = intval($ratio[1]);
            $ratios[$item] = $value;
            }
        $ratios_sum = array_sum($ratios);
        $distributed_pickups = array_map(function($value) use ($ratios_sum, $max_random_pickups){
            return ceil(($value / $ratios_sum) * $max_random_pickups);
            }, $ratios);
        asort($distributed_pickups);
        $options = array_keys($distributed_pickups);
        //error_log('$map_data_parsed = '.print_r($map_data_parsed, true).PHP_EOL);
        //error_log('$ratios = '.print_r($ratios, true).PHP_EOL);
        //error_log('$options = '.print_r($options, true).PHP_EOL);
        //error_log('$ratios_sum = '.print_r($ratios_sum, true).PHP_EOL);
        //error_log('$max_random_pickups = '.print_r($max_random_pickups, true).PHP_EOL);
        //error_log('$distributed_pickups = '.print_r($distributed_pickups, true).PHP_EOL);
        $item_token = '';
        for ($item_key = 0; $item_key < $max_random_pickups; $item_key++){
            if (empty($options)){ $options = array_keys($distributed_pickups); }
            if (empty($item_token)){ $item_token = array_shift($options); }
            if (!isset($generated_pickups[$item_token])){ $generated_pickups[$item_token] = 0; }
            //error_log(PHP_EOL.'-> next item = "'.$item_token.'"');
            //error_log('-> getting random position for item "'.$item_token.'"');
            $all_available = !empty($available_pickup_cells['all']) ? $available_pickup_cells['all'] : array();
            $water_tiles = !empty($available_pickup_cells['by_terrain']['water']) ? $available_pickup_cells['by_terrain']['water'] : array();
            $available = array_diff($all_available, $water_tiles);
            //error_log('$available = '.print_r($available, true));
            //error_log('$available(count) = '.count($available).' vs. $used_pickup_cells(count) = '.count($used_pickup_cells));
            $item_pos = self::get_rand_pos($available, $used_pickup_cells);
            if (!$item_pos){ continue; } // means there's no more room for this type!!!
            $item_pos_terrain = self::get_map_position_terrain($item_pos, $map_data_parsed);
            //error_log('$item_pos = '.print_r($item_pos, true));
            //error_log('$item_pos_terrain = '.print_r($item_pos_terrain, true));
            $item_info = $mmrpg_index_items[$item_token];
            $item_class = $item_info['item_class'];
            $pickup_token = 'random-pickup-item-'.($item_key + 1); //$world_pickup_token.'_random-item-'.($item_key + 1);
            $pickup_label = $item_info['item_name'];
            $world_map_pickups[] = array('item', $item_token, $item_pos, $pickup_token, $pickup_label);
            $generated_pickups[$item_token]++;
            $distributed_pickups[$item_token]--;
            if (empty($distributed_pickups[$item_token])){ $item_token = ''; }
        }
        //error_log('$generated_pickups = '.print_r($generated_pickups, true).PHP_EOL);

        // Return the generated pickups array
        return $world_map_pickups;
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
    public static function get_sprite($kind, $token, $alt = '', $dir = 'left', $class = '', $styles = '', $attrs = '', $prefix = ''){
        //error_log('rpg_world::get_sprite() called for "'.$kind.'" with token "'.$token.'"');
        $mmrpg_indexes = self::$mmrpg_indexes;
        $xkind = self::get_xkind($kind);
        if (strstr($token, '_')){ list($token, $alt) = explode('_', $token, 2); }
        if (empty($mmrpg_indexes)){ error_log('rpg_world::get_sprite() error: $mmrpg_indexes does not exist!'); return false; }
        elseif (empty($mmrpg_indexes[$xkind])){ error_log('rpg_world::get_sprite() error: $mmrpg_indexes['.$xkind.'] does not exist!'); return false; }
        elseif (empty($mmrpg_indexes[$xkind][$token])){ error_log('rpg_world::get_sprite() error: $mmrpg_indexes['.$xkind.']['.$token.'] does not exist!'); return false; }
        // Collect index info and initialize vars for generating the sprite markup
        $info = $mmrpg_indexes[$xkind][$token];
        $sprite_classes = !empty($class) ? $class : '';
        $sprite_styles = !empty($styles) ? $styles : '';
        $sprite_attrs = !empty($attrs) ? $attrs : '';
        $anim_classes = '';
        $anim_styles = '';
        $anim_attrs = '';
        // Generate the delay to make each sprite's animation slightly offset from each other
        $animDelay = -1 * (mt_rand(1, 100) / 100);
        if (!empty($animDelay)){ $anim_styles .= ' animation-delay: '.$animDelay.'s;'; }
        //error_log('$info = '.print_r($info, true));
        //error_log('$anim = '.print_r($anim, true));
        // Collect other basic details about the sprite image, size, etc.
        $dir = $dir;
        $img = $info[$kind.'_image'];
        $img_size = $info[$kind.'_image_size'];
        $img_dir = 'both';
        $img_prefix = !empty($prefix) ? $prefix : '';
        if (empty($img_prefix) && ($kind === 'player' || $kind === 'robot')){ $img_prefix = 'sprite'; }
        if (empty($img_prefix) && ($kind === 'ability' || $kind === 'item')){ $img_prefix = 'icon'; }
        $img_xsize = $img_size. 'x'.$img_size;
        $img_sprite = '';
        if ($kind === 'ability'){ $img_sprite .= '<i class="back"></i>'; }
        $img_sprite .= '<i class="sprite"></i>';
        $combined_classes = trim(implode(' ', array($sprite_classes, $anim_classes)));
        $combined_styles = trim(implode(' ', array($sprite_styles, $anim_styles)));
        $combined_attrs = trim(implode(' ', array($sprite_attrs, $anim_attrs)));
        $sprite_class = 'sprite '.$kind.($combined_classes ? ' '.$combined_classes : '');
        $sprite_styles = ($combined_styles ? ' style="'.$combined_styles.'"' : '');
        $sprite_attrs = ' data-sprite="'.$kind.'" data-token="'.$token.'" data-alt="'.$alt.'" data-size="'.$img_size.'" data-dir="'.$dir.'" data-frame="00" '.($combined_attrs ? ' '.$combined_attrs : '');
        $sprite_markup = '';
        $sprite_markup .= '<span class="'.$sprite_class.'"'.$sprite_attrs.$sprite_styles.'>';
            $sprite_markup .= '<span class="wrap">'.$img_sprite.'</span>';
        $sprite_markup .= '</span>';
        return($sprite_markup);
    }

    // Define a reusable function for grabbing the markup for a given character sprite (player or robot)
    public static function get_sprite_meta($kind, $token, $alt = '', $dir = 'left'){
        //error_log('rpg_world::get_sprite_meta(kind:'.$kind.', token:'.$token.') called!');
        $mmrpg_indexes = self::$mmrpg_indexes;
        $xkind = self::get_xkind($kind);
        if (strstr($token, '_')){ list($token, $alt) = explode('_', $token, 2); }
        if (empty($mmrpg_indexes)){ error_log('rpg_world::get_sprite() error: $mmrpg_indexes does not exist!'); return false; }
        if (empty($mmrpg_indexes[$xkind])){ error_log('rpg_world::get_sprite() error: $mmrpg_indexes['.$xkind.'] does not exist!'); return false; }
        if (empty($mmrpg_indexes[$xkind][$token])){ error_log('rpg_world::get_sprite() error: $mmrpg_indexes['.$xkind.']['.$token.'] does not exist!'); return false; }
        $sprite_meta = array();
        $sprite_meta['kind'] = $kind;
        $sprite_meta['token'] = $token;
        $info = $mmrpg_indexes[$xkind][$token];
        if ($info[$kind.'_class'] === 'system'){ error_log('rpg_world::get_sprite() error: cannot get sprite for system '.$kind.'!'); return false; }
        if (empty($info[$kind.'_flag_published'])){ error_log('rpg_world::get_sprite() error: cannot get sprite for unpublished '.$kind.'!'); return false; }
        if (empty($info[$kind.'_flag_complete'])){ error_log('rpg_world::get_sprite() error: cannot get sprite for incomplete '.$kind.'!'); return false; }
        $spriteSpeed = 0;
        if ($kind === 'player'){ $spriteSpeed = rpg_player::get_css_animation_duration($info); }
        elseif ($kind === 'robot'){ $spriteSpeed = rpg_robot::get_css_animation_duration($info); }
        elseif ($kind === 'item'){ $spriteSpeed = 0.5; }
        elseif ($kind === 'ability'){ $spriteSpeed = 1; }
        if (!empty($spriteSpeed)){ $sprite_meta['spriteSpeed'] = $spriteSpeed; }
        else { $sprite_meta['spriteSpeed'] = 1; }
        //error_log('$info = '.print_r($info, true));
        //error_log('$spriteSpeed = '.print_r($spriteSpeed, true));
        $dir = $dir;
        $img = $info[$kind.'_image'];
        $img_dir = 'both';
        $img_size = $info[$kind.'_image_size'];
        $img_prefix = '';
        if (empty($img_prefix) && ($kind === 'player' || $kind === 'robot')){ $img_prefix = 'sprite'; }
        if (empty($img_prefix) && ($kind === 'ability' || $kind === 'item')){ $img_prefix = 'icon'; }
        $img_xsize = $img_size. 'x'.$img_size;
        $sprite_meta['img'] = $img;
        $sprite_meta['dir'] = $dir;
        $sprite_meta['imgSize'] = array($img_size, $img_size);
        $sprite_meta['imgDir'] = $img_dir;
        $sprite_meta['imgPrefix'] = $img_prefix;
        $img_sprite = '';
        if ($kind === 'player' || $kind === 'robot'){
            $base_img = !empty($img) ? $img : $token;
            $alt_num = !empty($alt) ? str_replace('alt', '', $alt) : 0;
            if (empty($alt_num)){ $alt_num = $alt_num === '' ? 1 : 0; }
            $sprite_path = 'images/'.$xkind.'/all/token:'.$base_img.($alt_num ? '+alt:'.$alt_num : '').'+dir:'.$img_dir.'+zoom:true+crop:false/'.$img_prefix.'_left_'.$img_xsize.'.png';
            $sprite_meta['sheetPath'] = $sprite_path;
            $sprite_meta['sheetSize'] = array($img_size, $img_size);
            $sprite_meta['sheetOffset'] = array(0, 0);
        } elseif ($kind === 'ability' || $kind === 'item'){
            if ($kind === 'ability'){
                static $composite_ability_sprite_zoom = 80;
                static $composite_ability_sprite_config, $composite_ability_sprite_image, $composite_ability_sprite_index, $composite_ability_sprite_size;
                if (empty($composite_ability_sprite_config)
                    || empty($composite_ability_sprite_image)
                    || empty($composite_ability_sprite_index)
                    || empty($composite_ability_sprite_size)){
                    $composite_ability_sprite_config = array('kind' => 'abilities', 'image' => 'icon_right_40x40', 'size' => $composite_ability_sprite_zoom, 'frame' => 'icon', 'zoom' => 'true');
                    //error_log('$composite_ability_sprite_config = '.print_r($composite_ability_sprite_config, true));
                    $composite_ability_sprite_image = rpg_game::get_sprite_composite_path($composite_ability_sprite_config);
                    $composite_ability_sprite_index = rpg_game::get_sprite_composite_index($composite_ability_sprite_config);
                    $composite_ability_sprite_size = array(0, 0);
                    if (!empty($composite_ability_sprite_index)){
                        foreach ($composite_ability_sprite_index AS $key => $ability){
                            //error_log('$composite_ability_sprite_index['.$key.'] = '.print_r($ability, true));
                            if (!isset($ability['position'])){ continue; }
                            $col = $ability['position']['col'] + 1;
                            $row = $ability['position']['row'] + 1;
                            if ($col > $composite_ability_sprite_size[0]){ $composite_ability_sprite_size[0] = $col; }
                            if ($row > $composite_ability_sprite_size[1]){ $composite_ability_sprite_size[1] = $row; }
                            }
                        $composite_ability_sprite_size[0] *= $composite_ability_sprite_zoom;
                        $composite_ability_sprite_size[1] *= $composite_ability_sprite_zoom;
                        }
                    //error_log('$composite_ability_sprite_image = '.print_r($composite_ability_sprite_image, true));
                    //error_log('$composite_ability_sprite_index = '.print_r($composite_ability_sprite_index, true));
                    //error_log('$composite_ability_sprite_size = '.print_r($composite_ability_sprite_size, true));
                }
                $sprite_path = $composite_ability_sprite_image;
                $sprite_composite = !empty($composite_ability_sprite_index[$token]) ? $composite_ability_sprite_index[$token] : array();
                $sprite_offset = !empty($sprite_composite['offset']) ? array_values($sprite_composite['offset']) : array(0,0);
                $sprite_bgsize = array($composite_ability_sprite_size[0], $composite_ability_sprite_size[1]);
                $sprite_offset = array_map(function($i){ return -1 * ($i / 2); }, $sprite_offset);
                $sprite_bgsize = array_map(function($i){ return $i / 2; }, $sprite_bgsize);
                //error_log('$sprite_path('.$token.') = '.print_r($sprite_path, true));
                //error_log('$sprite_composite('.$token.') = '.print_r($sprite_composite, true));
                //error_log('$sprite_offset('.$token.') = '.print_r($sprite_offset, true));
                $sprite_meta['sheetPath'] = $sprite_path;
                $sprite_meta['sheetSize'] = $sprite_bgsize;
                $sprite_meta['sheetOffset'] = $sprite_offset;
            } elseif ($kind === 'item'){
                static $composite_item_sprite_zoom = 80;
                static $composite_item_sprite_config, $composite_item_sprite_image, $composite_item_sprite_index, $composite_item_sprite_size;
                if (empty($composite_item_sprite_config)
                    || empty($composite_item_sprite_image)
                    || empty($composite_item_sprite_index)
                    || empty($composite_item_sprite_size)){
                    $composite_item_sprite_config = array('kind' => 'items', 'image' => 'icon_right_40x40', 'size' => $composite_item_sprite_zoom, 'frame' => 'icon', 'zoom' => 'true');
                    //error_log('$composite_item_sprite_config = '.print_r($composite_item_sprite_config, true));
                    $composite_item_sprite_image = rpg_game::get_sprite_composite_path($composite_item_sprite_config);
                    $composite_item_sprite_index = rpg_game::get_sprite_composite_index($composite_item_sprite_config);
                    $composite_item_sprite_size = array(0, 0);
                    if (!empty($composite_item_sprite_index)){
                        foreach ($composite_item_sprite_index AS $key => $item){
                            //error_log('$composite_item_sprite_index['.$key.'] = '.print_r($item, true));
                            if (!isset($item['position'])){ continue; }
                            $col = $item['position']['col'] + 1;
                            $row = $item['position']['row'] + 1;
                            if ($col > $composite_item_sprite_size[0]){ $composite_item_sprite_size[0] = $col; }
                            if ($row > $composite_item_sprite_size[1]){ $composite_item_sprite_size[1] = $row; }
                            }
                        $composite_item_sprite_size[0] *= $composite_item_sprite_zoom;
                        $composite_item_sprite_size[1] *= $composite_item_sprite_zoom;
                        }
                    //error_log('$composite_item_sprite_image = '.print_r($composite_item_sprite_image, true));
                    //error_log('$composite_item_sprite_index = '.print_r($composite_item_sprite_index, true));
                    //error_log('$composite_item_sprite_size = '.print_r($composite_item_sprite_size, true));
                }
                $sprite_path = $composite_item_sprite_image;
                $sprite_composite = !empty($composite_item_sprite_index[$token]) ? $composite_item_sprite_index[$token] : array();
                $sprite_offset = !empty($sprite_composite['offset']) ? array_values($sprite_composite['offset']) : array(0,0);
                $sprite_bgsize = array($composite_item_sprite_size[0], $composite_item_sprite_size[1]);
                $sprite_offset = array_map(function($i){ return -1 * ($i / 2); }, $sprite_offset);
                $sprite_bgsize = array_map(function($i){ return $i / 2; }, $sprite_bgsize);
                //error_log('$sprite_path('.$token.') = '.PHP_EOL.print_r($sprite_path, true));
                //error_log('$sprite_meta'.$token.') = '.print_r($sprite_meta, true));
                //error_log('$sprite_offset'.$token.') = '.print_r($sprite_offset, true));
                $sprite_meta['sheetPath'] = $sprite_path;
                $sprite_meta['sheetSize'] = $sprite_bgsize;
                $sprite_meta['sheetOffset'] = $sprite_offset;
            }
        }
        // Return the generated sprite metadata
        return $sprite_meta;
    }

    // Define a function for getting the cursor sprite specifically (which has it's own rules)
    public static function get_cursor_sprite($dir = '', $class = '', $styles = '', $attrs = ''){
        //error_log('rpg_world::get_cursor_sprite() called for dir "'.$dir.'"');
        $cursor_sprite = self::get_sprite('robot', 'pointan', '', $dir, $class, $styles, $attrs);
        $cursor_background = 'background-image: url(images/assets/cursor_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.');';
        $cursor_sprite = str_replace('pointan', 'cursor', $cursor_sprite);
        $cursor_sprite = str_replace('<i class="sprite">', '<i class="sprite" style="'.$cursor_background.'">', $cursor_sprite);
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

    // Define a function for getting the battle stars collected so far
    public static function get_battle_stars(){
        //error_log('rpg_world::get_battle_stars()');
        $session_token = rpg_game::session_token();
        $this_battle_stars = !empty($_SESSION[$session_token]['values']['battle_stars']) ? $_SESSION[$session_token]['values']['battle_stars'] : array();
        return $this_battle_stars;
    }

    // Define a function for getting the player switcher markup given current conditions
    public static function get_player_switcher_markup($this_prototype_data, $player_tokens){
        //error_log('rpg_world::get_player_switcher_markup() called!');
        $return_markup = '';
        $get_label_span = function($name, $kind){ return ('<span class="label">'.$name.' ('.ucfirst($kind).')</span>'); };
        $cursor_token = 'player';
        $cursor_active = $this_prototype_data['this_player_token'] === $cursor_token ? true : false;
        $cursor_sprite = self::get_cursor_sprite('right', 'cursor');
        $cursor_label = $get_label_span('Prε', 'cursor');
        $cursor_types = 'type explode';
        //$return_markup .= ('<a class="option'.($cursor_active ? ' active' : '').'" data-player="'.$cursor_token.'">'.$cursor_sprite.$cursor_label.'</a>');
        $mmrpg_index_players = self::get_index('players');
        $return_markup .= ('<a class="team-player '.$cursor_types.($cursor_active ? ' active' : '').'" data-player="'.$cursor_token.'">'.$cursor_sprite.$cursor_label.'</a>');
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
        return $return_markup;
    }

    // Define a function for getting the cursor pallet markup given current conditions
    public static function get_cursor_palette_markup($this_prototype_data){
        //error_log('rpg_world::get_cursor_palette_markup() called!');
        if (empty($this_prototype_data)){ return ''; }
        if ($this_prototype_data['this_player_token'] !== 'player'){ return ''; }
        $return_markup = '';
        // Define the markup for the permanent quest-like items that show progress
        //$perm_item_slots = 4; // TODO: add this to the settings file as a constant (after deciding what these represent)
        //$return_markup .= '<div class="slots perm" data-slots="'.$perm_item_slots.'">';
        //    for ($i = 0; $i < $perm_item_slots; $i++){ $return_markup .= '<div class="slot" data-slot="'.($i + 1).'"></div>'; }
        //$return_markup .= '</div>';
        // Define the markup for the temporary item slot for stuff that can be picked-up/put-down
        $return_markup .= '<div class="slots temp" data-slots="1">';
            $return_markup .= '<div class="slot" data-slot="1"></div>';
        $return_markup .= '</div>';
        return $return_markup;
    }

    // Define a function for getting the robot switcher markup given current conditions
    public static function get_robots_overview_markup($this_prototype_data, $current_robot_tokens){
        //error_log('rpg_world::get_robot_overview_markup() called!');
        if (empty($this_prototype_data) || empty($current_robot_tokens)){ return ''; }
        if ($this_prototype_data['this_player_token'] === 'player'){ return ''; }
        $return_markup = '';
        $WORLD_SESSION = self::get_session();
        $WORLD_ROBOT_SESSIONS = &$WORLD_SESSION['robot_sessions'];
        $mmrpg_index_types = self::get_index('types');
        $mmrpg_index_robots = self::get_index('robots');
        $mmrpg_index_items = self::get_index('items');
        $mmrpg_index_abilities = self::get_index('abilities');
        $current_player_token = $this_prototype_data['this_player_token'];
        $player_starforce = rpg_game::starforce_unlocked();
        $limit_hearts = mmrpg_prototype_limit_hearts_earned($current_player_token);
        $num_robot_unlocked = mmrpg_prototype_robots_unlocked($current_player_token);
        $storage_robot_tokens = mmrpg_prototype_robots_unlocked($current_player_token, true);
        //$storage_robot_tokens = array_diff($storage_robot_tokens, $current_robot_tokens);
        $storage_item_tokens = array(); mmrpg_prototype_items_unlocked(true, $storage_item_tokens);
        $storage_ability_tokens = array(); mmrpg_prototype_abilities_unlocked('', '', $storage_ability_tokens);
        $equipped_player_items = array(); mmrpg_prototype_items_equipped('', $equipped_player_items);
        //error_log('$storage_item_tokens('.count($storage_item_tokens).') = '.print_r($storage_item_tokens, true));
        //error_log('$storage_ability_tokens('.count($storage_ability_tokens).') = '.print_r($storage_ability_tokens, true));
        $battle_robot_history = rpg_world::get_battle_history($current_player_token);
        $battle_robot_tokens = !empty($battle_robot_history['robots_summoned']) ? $battle_robot_history['robots_summoned'] : array();
        $battle_robot_tokens = array_filter($battle_robot_tokens, function($token) use ($storage_robot_tokens){ return in_array($token, $storage_robot_tokens) ? true : false; });
        //error_log('$battle_robot_history('.count($battle_robot_history).') = '.print_r($battle_robot_history, true));
        //error_log('$battle_robot_tokens('.count($battle_robot_tokens).') = '.print_r($battle_robot_tokens, true));
        $current_team_size = $limit_hearts;
        if ($current_team_size > $num_robot_unlocked){ $current_team_size = $num_robot_unlocked; }
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
        $get_sort_options = function($sort_options, $active_option = '', $active_direction = ''){
            if (empty($sort_options)){ return false; }
            if (empty($active_option)){ $active_option = array_keys($sort_options)[0]; }
            if (empty($active_direction)){ $active_direction = 'down'; }
            $markup = '';
            $markup .= '<div class="sorts" data-dir="'.$active_direction.'">';
                $markup .= '<strong class="label">sort <i class="fas fa-sort"></i></strong>';
                foreach ($sort_options AS $option_token => $option_name){
                    $active = $active_option === $option_token ? true : false;
                    $markup .= '<button type="button" '.
                        'class="button sort'.($active ? ' active' : '').'" '.
                        'data-sort="'.$option_token.'">'.
                            '<span>'.$option_name.'</span>'.
                        '</button>';
                    }
            $markup .= '</div>';
            return $markup;
            };
        $get_toggle_options = function($toggle_options){
            if (empty($toggle_options)){ return false; }
            $markup = '';
            $markup .= '<div class="toggles">';
            foreach ($toggle_options AS $option_token => $option_data){
                if (empty($option_data) || !is_array($option_data)){ continue; }
                $option_states = $option_data; unset($option_states['default']);
                $default_state = isset($option_data['default']) ? $option_data['default'] : array_keys($option_data)[0];
                $class = 'class="button toggle"';
                $attrs = 'data-toggle="'.$option_token.'" data-state="'.$default_state.'"';
                $attrs .= ' title="show/hide '.$option_token.'"';
                $markup .= '<button type="button" '.$class.' '.$attrs.'>';
                    foreach ($option_states AS $state => $state_icon){
                        $markup .= '<i class="icon fas fa-'.$state_icon.'" data-state="'.$state.'"></i>';
                        }
                $markup .= '</button>';
            }
            $markup .= '</div>';
            return $markup;
            };
        // Pre-parse any items that are part of sets so we can display their quantities as normal items
        $get_storage_item_sets = function($storage_item_tokens) use ($mmrpg_index_items){
            //error_log('$parse_storage_item_sets() called!');
            if (empty($storage_item_tokens)){ return false; }
            $storage_item_sets = array();
            foreach ($storage_item_tokens AS $item_token => $item_quantity){
                if (empty($item_token) || !is_string($item_token)){ continue; }
                elseif ($item_token === 'item' || strstr($item_token, '__equipped')){ continue; }
                elseif (!strstr($item_token, '__')){ continue; }
                //error_log('-> storage-item '.$item_token.' w/ quantity '.$item_quantity);
                $set_token = $item_token;
                list($item_token, $num_in_set) = explode('__', $set_token, 2);
                //error_log('--> $set_token = '.$set_token);
                //error_log('--> $item_token = '.$item_token);
                //error_log('--> $num_in_set = '.$num_in_set);
                if (empty($mmrpg_index_items[$item_token]) || empty($set_token) || empty($num_in_set)){ continue; }
                if (!isset($storage_item_sets[$item_token])){ $storage_item_sets[$item_token] = array(); }
                $storage_item_sets[$item_token][] = $set_token;
                //error_log('----> $storage_item_sets['.$item_token.'][] = '.$set_token);
                }
            return $storage_item_sets;
            };
        $storage_item_sets = $get_storage_item_sets($storage_item_tokens);
        //error_log('-> $storage_item_tokens(raw) = '.print_r($storage_item_tokens, true));
        //error_log('-> $storage_item_sets = '.print_r($storage_item_sets, true));
        if (!empty($storage_item_sets)){
            foreach ($storage_item_sets AS $item_token => $set_tokens){
                unset($storage_item_tokens[$item_token]);
                $item_quantity = count($set_tokens);
                $insert_array = array($item_token => $item_quantity);
                $insert_after = array_search($set_tokens[0], array_keys($storage_item_tokens));
                $before = array_slice($storage_item_tokens, 0, $insert_after, true);
                $after = array_slice($storage_item_tokens, $insert_after + 1, null, true);
                $storage_item_tokens = array_merge($before, $insert_array, $after);
                foreach ($set_tokens AS $set_token){ unset($storage_item_tokens[$set_token]); }
            }
            //error_log('-> $storage_item_tokens(modded) = '.print_r($storage_item_tokens, true));
        }
        // [robots-overview][team-size]
        //if ($num_robot_unlocked > $current_team_size){ $return_markup .= '<div class="team-size"><strong>'.$current_team_size.' of '.$num_robot_unlocked.'</strong></div>'; }
        //elseif ($num_robot_unlocked === 1){ $return_markup .= '<div class="team-size"><strong>1 robot</strong></div>'; }
        //elseif ($num_robot_unlocked === $current_team_size){ $return_markup .= '<div class="team-size"><strong>'.$current_team_size.' robots</strong></div>'; }
        //else { $return_markup .= '<div class="team-size"><strong>'.$current_team_size.' of '.$num_robot_unlocked.'</strong></div>'; }
        // [robots-overview][team-rotate]
        if ($num_robot_unlocked > 1){ $return_markup .= '<a class="team-rotate"><i class="fa fas fa-sync"></i></a>'; }
        // [robots-overview][team-switch]
        if ($num_robot_unlocked > 0){ $return_markup .= '<a class="storage-button team-switch" data-view="robots"><i class="fa fas fa-robot"></i><b>robots</b></a>'; }
        //if ($num_robot_unlocked > $current_team_size){ $return_markup .= '<a class="storage-button team-switch" data-view="robots"><i class="fa fas fa-robot"></i><b>robots</b></a>'; }
        //else { $return_markup .= '<span class="storage-button team-switch" data-view="robots"><i class="fa fas fa-robot"></i><b>robots</b></span>'; }
        // [robots-overview][team-items]
        $return_markup .= '<a class="storage-button team-items" data-view="items"><i class="fa fas fa-briefcase"></i><b>items</b></a>';
        // [robots-overview][team-abilities]
        $return_markup .= '<a class="storage-button team-abilities" data-view="abilities"><i class="fa fas fa-fire-alt"></i><b>abilities</b></a>';
        // [robots-overview][limit-hearts]
        $return_markup .= '<div class="limit-hearts">';
            $return_markup .= '<i class="player '.$current_player_token.'"></i>';
            $return_markup .= str_repeat('<i class="heart fa fas fa-heart"></i>', $limit_hearts);
        $return_markup .= '</div>';
        // [robots-overview][team-robots]
        $return_markup .= '<div class="team-robots listing" data-team-size="'.count($current_robot_tokens).'">';
            $return_markup .= '<div class="wrapper">';
            foreach ($current_robot_tokens AS $robot_key => $robot_token){
                if ($robot_token === 'robot' || empty($mmrpg_index_robots[$robot_token])){ continue; }
                // collect all the info we need about this robot
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
                $item_sprite = !empty($robot_item) ? self::get_sprite('item', $robot_item, '', 'right', 'holding', '', '', 'icon') : '';
                $robot_disabled = empty($robot_overview['energy']) ? true : false;
                // generate markup for energy and weapons guages
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
                // generate the markup for the attack/defense/speed mods
                $has_statmods = false;
                $robot_stats_markup = '';
                $stat_tokens = array('attack', 'defense', 'speed');
                foreach ($stat_tokens AS $stat_token){
                    $mod_token = $stat_token.'Mods';
                    $stat_mods = !empty($robot_overview[$mod_token]) ? $robot_overview[$mod_token] : 0;
                    if (empty($stat_mods)){ continue; }
                    $num_arrows = abs($stat_mods);
                    if ($stat_mods < 1){ $stat_dir = 'down'; }
                    else { $stat_dir = 'up'; }
                    $stat_markup = str_repeat('<i class="fa fas fa-caret-'.$stat_dir.'"></i>', $num_arrows);
                    $robot_stats_markup .= '<div class="mod color '.$stat_token.' '.$stat_dir.'" title="'.ucfirst($stat_token).' Mods">'.$stat_markup.'</div>';
                    }
                if (!empty($robot_stats_markup)){
                    $has_statmods = true;
                    $robot_stats_markup = '<div class="statmods">'.$robot_stats_markup.'</div>';
                    }
                // collect the robot's frame based on its current health
                $robot_frame = $get_robot_energy_frame($robot_energy_rating);
                $robot_sprite = str_replace('data-frame="00"', 'data-frame="'.$robot_frame.'"', $robot_sprite);
                // put it all together to generate the robot markup
                $markup_class = 'team-robot'.($has_statmods ? ' hasmods' : '').($robot_disabled ? ' disabled' : '');
                $markup_attrs = 'data-robot="'.$robot_id.'_'.$robot_token.'" data-status="'.$robot_energy_rating.'-energy"';
                $robot_markup = '';
                $robot_markup .= '<div class="'.$markup_class.'" '.$markup_attrs.'>';
                    $robot_markup .= '<div class="icon '.$robot_core_types.'">'.$robot_sprite.$item_sprite.'</div>';
                    $robot_markup .= '<div class="label">';
                        $robot_markup .= '<strong class="name">'.$robot_name.'</strong>';
                        $robot_markup .= '<span class="lvl type '.($robot_level >= 100 ? 'level' : 'none').'">Lv. '.$robot_level.'</span>';
                    $robot_markup .= '</div>';
                    $robot_markup .= $robot_energy_markup;
                    $robot_markup .= $robot_weapons_markup;
                    $robot_markup .= $robot_stats_markup;
                $robot_markup .= '</div>';
                // add this robot's markup to the return markup
                $return_markup .= $robot_markup;
            }
            $return_markup .= '</div>';
        $return_markup .= '</div>';
        // [robots-overview][storage-robots]
        //error_log('$current_robot_tokens = '.print_r($current_robot_tokens, true));
        //error_log('$battle_robot_tokens = '.print_r($battle_robot_tokens, true));
        //error_log('$storage_robot_tokens = '.print_r($storage_robot_tokens, true));
        $return_markup .= '<div class="storage-robots storage-box listing" data-storage="robots">';
            $recent_storage_robot_tokens = array();
            $recent_storage_robot_tokens = array_merge($recent_storage_robot_tokens, $current_robot_tokens);
            $recent_storage_robot_tokens = array_merge($recent_storage_robot_tokens, $battle_robot_tokens);
            $recent_storage_robot_tokens = array_merge($recent_storage_robot_tokens, $storage_robot_tokens);
            //$recent_storage_robot_tokens = array_diff($recent_storage_robot_tokens, $current_robot_tokens);
            //$recent_storage_robot_tokens = array_merge($recent_storage_robot_tokens, $current_robot_tokens);
            $recent_storage_robot_tokens = array_unique($recent_storage_robot_tokens);
            //error_log('$recent_storage_robot_tokens = '.print_r($recent_storage_robot_tokens, true));
            $return_markup .= '<div class="wrapper">';
            foreach ($storage_robot_tokens AS $robot_key => $robot_token){
                if ($robot_token === 'robot' || empty($mmrpg_index_robots[$robot_token])){ continue; }
                // collect all the info we need about this robot
                $robot_index_info = $mmrpg_index_robots[$robot_token];
                $robot_info = $robot_index_info;
                $robot_id = $robot_info['robot_id'];
                $robot_overview = self::get_player_robot_overview($current_player_token, $robot_token, $robot_id);
                $robot_name = $robot_overview['name'];
                $robot_level = $robot_overview['level'];
                $robot_experience = $robot_overview['experience'];
                $robot_level_exp = $robot_level + ($robot_experience / 1000);
                $robot_core_types = $robot_overview['coreTypes'];
                $robot_core_or_none = $robot_overview['coreElse'];
                $robot_item = $robot_overview['item'];
                $robot_image = $robot_overview['image'];
                $robot_core1 = $robot_overview['core'];
                $robot_core2 = $robot_overview['core2'];
                $robot_index_key = array_search($robot_token, array_keys($mmrpg_index_robots));
                $robot_core_key = array_search($robot_core1, array_keys($mmrpg_index_types));
                $robot_storage_key = array_search($robot_token, $recent_storage_robot_tokens);
                if (!empty($robot_core2)){ $robot_core_key += (array_search($robot_core2, array_keys($mmrpg_index_types)) / 100); }
                $robot_sprite = self::get_sprite('robot', $robot_image, '', 'left', 'character', '');
                $item_sprite = !empty($robot_item) ? self::get_sprite('item', $robot_item, '', 'right', 'holding', '', '', 'icon') : '';
                $robot_current = in_array($robot_token, $current_robot_tokens) ? true : false;
                $robot_disabled = empty($robot_overview['energy']) ? true : false;
                // generate markup for energy and weapons guages
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
                // generate the markup for the attack/defense/speed mods
                $robot_stats_markup = '';
                $stat_tokens = array('attack', 'defense', 'speed');
                foreach ($stat_tokens AS $stat_token){
                    $mod_token = $stat_token.'Mods';
                    $stat_mods = !empty($robot_overview[$mod_token]) ? $robot_overview[$mod_token] : 0;
                    if (empty($stat_mods)){ continue; }
                    $num_arrows = abs($stat_mods);
                    if ($stat_mods < 1){ $stat_dir = 'down'; }
                    else { $stat_dir = 'up'; }
                    $stat_markup = str_repeat('<i class="fa fas fa-caret-'.$stat_dir.'"></i>', $num_arrows);
                    $robot_stats_markup .= '<div class="mod color '.$stat_token.' '.$stat_dir.'" title="'.ucfirst($stat_token).' Mods">'.$stat_markup.'</div>';
                }
                if (!empty($robot_stats_markup)){ $robot_stats_markup = '<div class="statmods">'.$robot_stats_markup.'</div>'; }
                // collect the robot's frame based on its current health
                $robot_frame = $get_robot_energy_frame($robot_energy_rating);
                $robot_sprite = str_replace('data-frame="00"', 'data-frame="'.$robot_frame.'"', $robot_sprite);
                // put it all together to generate the robot markup
                $markup_class = 'team-robot'.($robot_current ? ' current' : '').($robot_disabled ? ' disabled' : '');
                $markup_attrs = '';
                $markup_attrs .= 'data-robot="'.$robot_id.'_'.$robot_token.'" ';
                $markup_attrs .= 'data-robot-id="'.$robot_id.'" ';
                $markup_attrs .= 'data-robot-token="'.$robot_token.'" ';
                $markup_attrs .= 'data-status="'.$robot_energy_rating.'-energy" ';
                $markup_attrs .= 'data-level-exp="'.$robot_level_exp.'" ';
                $markup_attrs .= 'data-index-key="'.$robot_index_key.'" ';
                $markup_attrs .= 'data-storage-key="'.$robot_storage_key.'" ';
                $markup_attrs .= 'data-core-key="'.$robot_core_key.'" ';
                $robot_markup = '';
                $robot_markup .= '<div class="'.$markup_class.'" '.trim($markup_attrs).'>';
                    $robot_markup .= '<div class="icon '.$robot_core_types.'">'.$robot_sprite.$item_sprite.'</div>';
                    $robot_markup .= '<div class="label">';
                        $robot_markup .= '<strong class="name">'.$robot_name.'</strong>';
                        $robot_markup .= '<span class="lvl type '.($robot_level >= 100 ? 'level' : 'none').'">Lv. '.$robot_level.'</span>';
                    $robot_markup .= '</div>';
                    $robot_markup .= $robot_energy_markup;
                    $robot_markup .= $robot_weapons_markup;
                    $robot_markup .= $robot_stats_markup;
                $robot_markup .= '</div>';
                // add this robot's markup to the return markup
                $return_markup .= $robot_markup;
            }
            $return_markup .= '</div>';
            $return_markup .= $get_sort_options(array(
                'storage-key' => 'recent',
                'index-key' => 'id',
                'core-key' => 'core',
                'level-exp' => 'level',
                ));
            /* $return_markup .= $get_toggle_options(array(
                'disabled' => array(
                    'visible' => 'eye-slash',
                    'hidden' => 'eye',
                    'default' => 'hidden',
                    ),
                )); */
        $return_markup .= '</div>';
        // [robots-overview][storage-items]
        $return_markup .= '<div class="storage-items storage-box listing" data-storage="items">';
            $storage_item_types_revised = array('none', 'copy', 'energy', 'weapons', 'attack', 'defense', 'speed');
            $storage_item_types_revised = array_unique(array_merge($storage_item_types_revised, array_keys($mmrpg_index_types)));
            $storage_item_tokens_reversed = array_reverse(array_keys($storage_item_tokens));
            $return_markup .= '<div class="wrapper">';
            foreach ($storage_item_tokens AS $item_token => $item_quantity){
                if (empty($item_token) || !is_string($item_token)){ continue; }
                if ($item_token === 'item' || strstr($item_token, '__equipped')){ continue; }
                //error_log('-> storage-item '.$item_token.' w/ quantity '.$item_quantity);
                $num_in_set = false; $set_token = false;
                if (strstr($item_token, '__')){ $set_token = $item_token; list($item_token, $num_in_set) = explode('__', $set_token, 2); }
                if (!empty($set_token) && $storage_item_sets[$item_token]){ $item_quantity = count($storage_item_sets[$item_token]); }
                //error_log('--> set-token: '.$set_token.' | num-in-set: '.$num_in_set.' | new-quantity: '.$item_quantity);
                if (empty($mmrpg_index_items[$item_token])){ continue; }
                $item_quantity_absolute = $item_quantity;
                $item_quantity_equipped = !empty($equipped_player_items[$item_token]) ? $equipped_player_items[$item_token] : 0;
                if (!empty($item_quantity_equipped)){ $item_quantity -= $equipped_player_items[$item_token]; }
                if (strstr($item_token, '-shard') && $item_quantity > MMRPG_SETTINGS_SHARDS_MAXQUANTITY){ $item_quantity = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; }
                elseif (strstr($item_token, '-core') && $item_quantity > MMRPG_SETTINGS_CORES_MAXQUANTITY){ $item_quantity = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
                elseif ($item_quantity > MMRPG_SETTINGS_ITEMS_MAXQUANTITY){ $item_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }
                if (empty($item_quantity) || $item_quantity < 1){ $item_quantity = 0; }
                $item_info = $mmrpg_index_items[$item_token];
                $item_id = $item_info['item_id'];
                $item_type1 = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                $item_type2 = !empty($item_info['item_type2']) ? $item_info['item_type2'] : '';
                $item_index_key = array_search($item_token, array_keys($mmrpg_index_items));
                $item_type_key = array_search($item_type1, $storage_item_types_revised);
                $item_storage_key = array_search($item_token, $storage_item_tokens_reversed);
                if (!empty($item_type2) && $item_type1 !== 'none'){ $item_type_key += (array_search($item_type2, array_keys($storage_item_types_revised)) / 100); }
                elseif (!empty($item_type2) && $item_type1 === 'none'){ $item_type_key += count($storage_item_types_revised); }
                elseif (strstr($item_token, 'omega-')){ $item_type_key += count($storage_item_types_revised) + 1; }
                $item_name = $item_info['item_name'];
                $item_class = $item_info['item_class'];
                $item_subclass = $item_info['item_subclass'];
                $item_types = 'type '.(empty($item_info['item_type']) ? 'none' : $item_info['item_type'].(!empty($item_info['item_type2']) ? '_'.$item_info['item_type2'] : ''));
                $item_display_types = 'type ';
                if (empty($item_info['item_type']) && empty($item_info['item_type2'])){ $item_display_types .= 'none'; }
                elseif (empty($item_info['item_type']) && !empty($item_info['item_type2'])){ $item_display_types .= $item_info['item_type2']; }
                else { $item_display_types .= $item_info['item_type'].(!empty($item_info['item_type2']) ? '_'.$item_info['item_type2'] : ''); }
                $item_sprite = self::get_sprite('item', $item_token, '', 'right', 'icon', '', '', 'icon');
                $markup_attrs = '';
                $markup_attrs .= 'data-item="'.$item_token.'" ';
                $markup_attrs .= 'data-item-id="'.$item_id.'" ';
                $markup_attrs .= 'data-quantity="'.$item_quantity.'" ';
                $markup_attrs .= 'data-index-key="'.$item_index_key.'" ';
                $markup_attrs .= 'data-storage-key="'.$item_storage_key.'" ';
                $markup_attrs .= 'data-type-key="'.$item_type_key.'" ';
                $item_markup = '';
                $item_markup .= '<div class="team-item" '.trim($markup_attrs).'>';
                    $item_markup .= '<div class="image '.$item_display_types.'">';
                        $item_markup .= $item_sprite;
                    $item_markup .= '</div>';
                    $item_markup .= '<strong class="name'.(!strstr($item_name, ' ') ? ' oneline' : '').'">'.str_replace(' ', '<br />', $item_name).'</strong>';
                    if (true){ $item_markup .= '<span class="quantity">&times; '.$item_quantity.'</span>'; }
                    if (!empty($item_quantity_equipped)){ $item_markup .= '<span class="qty-equipped">&minus; '.$item_quantity_equipped.'</span>'; }
                $item_markup .= '</div>';
                $return_markup .= $item_markup;
            }
            $return_markup .= '</div>';
            $return_markup .= $get_sort_options(array(
                'index-key' => 'id',
                'type-key' => 'type',
                'quantity' => 'owned',
                'storage-key' => 'new',
                ));
            $return_markup .= $get_toggle_options(array(
                'outofstock' => array(
                    'visible' => 'eye-slash',
                    'hidden' => 'eye',
                    'default' => 'hidden',
                    ),
                ));
        $return_markup .= '</div>';
        // [robots-overview][storage-abilities]
        $return_markup .= '<div class="storage-abilities storage-box listing" data-storage="abilities">';
            $elemental_type_tokens = rpg_type::get_index_tokens(false, false, false, false);
            $storage_ability_types_revised = array_unique(array_merge($elemental_type_tokens, array('copy', 'none'), array_keys($mmrpg_index_types)));
            //error_log('$elemental_type_tokens = '.print_r($elemental_type_tokens, true));
            //error_log('$storage_ability_types_revised = '.print_r($storage_ability_types_revised, true));
            $storage_ability_tokens_reversed = array_reverse(array_values($storage_ability_tokens));
            $return_markup .= '<div class="wrapper">';
            foreach ($storage_ability_tokens AS $ability_key => $ability_token){
                if ($ability_token === 'ability' || empty($mmrpg_index_abilities[$ability_token])){ continue; }
                $ability_info = $mmrpg_index_abilities[$ability_token];
                $ability_id = $ability_info['ability_id'];
                $ability_type1 = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : '';
                $ability_index_key = array_search($ability_token, array_keys($mmrpg_index_abilities));
                $ability_type_key = array_search($ability_type1, $storage_ability_types_revised);
                $ability_storage_key = array_search($ability_token, $storage_ability_tokens_reversed);
                if (!empty($ability_type2)){ $ability_type_key += (array_search($ability_type2, $storage_ability_types_revised) / 100); }
                $ability_name = $ability_info['ability_name'];
                $ability_cost = !empty($ability_info['ability_energy']) ? $ability_info['ability_energy'] : 0;
                $ability_types = 'type '.(empty($ability_info['ability_type']) ? 'none' : $ability_info['ability_type'].(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : ''));
                $ability_display_types = 'type ';
                if (empty($ability_info['ability_type']) && empty($ability_info['ability_type2'])){ $ability_display_types .= 'none'; }
                elseif (empty($ability_info['ability_type']) && !empty($ability_info['ability_type2'])){ $ability_display_types .= $ability_info['ability_type2']; }
                else { $ability_display_types .= $ability_info['ability_type'].(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : ''); }
                $ability_sprite = self::get_sprite('ability', $ability_token, '', 'right', 'icon', '', '', 'icon');
                $markup_attrs = '';
                $markup_attrs .= 'data-ability="'.$ability_token.'" ';
                $markup_attrs .= 'data-ability-id="'.$ability_id.'" ';
                $markup_attrs .= 'data-energy-cost="'.$ability_cost.'" ';
                $markup_attrs .= 'data-index-key="'.$ability_index_key.'" ';
                $markup_attrs .= 'data-storage-key="'.$ability_storage_key.'" ';
                $markup_attrs .= 'data-type-key="'.$ability_type_key.'" ';
                $ability_markup = '';
                $ability_markup .= '<div class="team-ability" '.trim($markup_attrs).'>';
                    $ability_markup .= '<div class="image">';
                        $ability_markup .= str_replace('class="back"', 'class="back '.$ability_display_types.'"', $ability_sprite);
                    $ability_markup .= '</div>';
                    $ability_markup .= '<span class="tint '.$ability_display_types.'"></span>';
                    $ability_markup .= '<strong class="name'.(!strstr($ability_name, ' ') ? ' oneline' : '').'">'.str_replace(' ', '<br />', $ability_name).'</strong>';
                    $ability_markup .= '<span class="cost"><sup>'.$ability_cost.'</sup><sub>WE</sub></span>';
                $ability_markup .= '</div>';
                $return_markup .= $ability_markup;
            }
            $return_markup .= '</div>';
            $return_markup .= $get_sort_options(array(
                'index-key' => 'id',
                'type-key' => 'type',
                'energy-cost' => 'cost',
                'storage-key' => 'new',
                ));
            $return_markup .= $get_toggle_options(array(
                'incompatible' => array(
                    'visible' => 'eye-slash',
                    'hidden' => 'eye',
                    'default' => 'hidden',
                    ),
                ));
        $return_markup .= '</div>';
        // [robots-overview][close-button]
        $return_markup .= '<a class="team-close"><i class="fa fas fa-times"></i></a>';
        return $return_markup;
    }

    // Define a function for getting the robot switcher markup given current conditions
    public static function get_minimap_overview_markup($this_prototype_data, $world_data_parsed, $map_data_parsed){
        //error_log('rpg_world::get_minimap_overview_markup() ended!');
        $return_markup = '';
        // Collect world session data to reference during minimap generation
        $WORLD_SESSION = self::get_session();
        $world_maps = !empty($WORLD_SESSION['world_maps']) ? $WORLD_SESSION['world_maps'] : array();
        $world_areas = !empty($world_data_parsed['areas']) ? $world_data_parsed['areas'] : array();
        // Also collect information about the current area'a field (if possible) to determine minimap attributes
        $mmrpg_index_fields = self::get_index('fields');
        $map_field_token = !empty($map_data_parsed['field']) ? $map_data_parsed['field'] : 'field';
        $map_field_info = !empty($mmrpg_index_fields[$map_field_token]) ? $mmrpg_index_fields[$map_field_token] : array();
        //error_log('$map_field_token = '.$map_field_token);
        //error_log('$map_field_info = '.print_r($map_field_info, true));
        // Pull or define some basic details about the minimap from the size/image data
        if (!empty($map_data_parsed['type'])){ $minimap_type = $map_data_parsed['type']; }
        elseif (!empty($map_field_info['field_type'])){ $minimap_type = $map_field_info['field_type']; }
        elseif (!empty($map_field_info['field_type2'])){ $minimap_type = $map_field_info['field_type2']; }
        else { $minimap_type = 'none'; }
        $minimap_size = !empty($world_data_parsed['size']) ? $world_data_parsed['size'] : '1 x 1 @ 1 x 1 ^ 1';
        $minimap_padding = ''; if (strstr($minimap_size, ' ^ ')){ list($minimap_size, $minimap_padding) = explode(' ^ ', $minimap_size); }
        $minimap_tilesize = ''; if (strstr($minimap_size, ' @ ')){ list($minimap_size, $minimap_tilesize) = explode(' @ ', $minimap_size); }
        $minimap_padding = explode(' x ', $minimap_padding);
        $minimap_tilesize = explode(' x ', $minimap_tilesize);
        $minimap_size = explode(' x ', $minimap_size);
        $minimap_cols = $minimap_size[0];
        $minimap_rows = $minimap_size[1];
        $minimap_image = $world_data_parsed['image']; //'mmrpg-minimap-2025_debug-world.png';
        $minimap_image_path = 'images/maps/'.$minimap_image;
        $minimap_image_dir_path = MMRPG_CONFIG_ROOTDIR.$minimap_image_path;
        $minimap_image_url_path = MMRPG_CONFIG_ROOTURL.$minimap_image_path;
        $minimap_image_size = file_exists($minimap_image_dir_path) ? getimagesize($minimap_image_dir_path) : array();
        $minimap_image_width = !empty($minimap_image_size[0]) ? $minimap_image_size[0] : 0;
        $minimap_image_height = !empty($minimap_image_size[1]) ? $minimap_image_size[1] : 0;
        $minimap_size_styles = 'width:'.$minimap_image_width.'px; height:'.$minimap_image_height.'px;';
        $minimap_size_attrs = 'width="'.$minimap_image_width.'" height="'.$minimap_image_height.'"';
        $minimap_size_string = implode(' x ', $minimap_size).(!empty($minimap_tilesize) ? ' @ '.implode(' x ', $minimap_tilesize) : '').(!empty($minimap_padding) ? ' ^ '.implode(' x ', $minimap_padding) : '');
        // Review the world areas to see which minimap areas have already been seen or not
        $world_areas_visible = array();
        $world_areas_visible_positions = array();
        $current_world = $this_prototype_data['this_current_world'];
        $current_world_area = $this_prototype_data['this_current_map'];
        $current_world_area_position = !empty($world_areas[$current_world_area]) ? $world_areas[$current_world_area][0] : '';
        foreach ($world_areas AS $area_token => $area_positions){
            $is_multi_area = strstr($area_token, '+') ? true : false;
            if (!$is_multi_area){
                // Check if this area has been visited yet
                $tmp_world_map_token = $current_world.'__'.$area_token;
                $area_visited = !empty($world_maps[$tmp_world_map_token]) ? true : false;
                if (!$area_visited){ continue; }
                $world_areas_visible[] = $area_token;
                foreach ($area_positions AS $area_position){ $world_areas_visible_positions[] = $area_position; }
            } else {
                // Explode list of areas and ensure all have been visited before showing
                $area_tokens = explode('+', $area_token);
                $areas_visited = 0;
                foreach ($area_tokens AS $sub_area_token){
                    $tmp_world_map_token = $current_world.'__'.$sub_area_token;
                    $area_visited = !empty($world_maps[$tmp_world_map_token]) ? true : false;
                    if ($area_visited){ $areas_visited++; }
                }
                if ($areas_visited < count($area_tokens)){ continue; }
                $world_areas_visible[] = $area_token;
                foreach ($area_positions AS $area_position){ $world_areas_visible_positions[] = $area_position; }
            }
        }
        //error_log('$world_areas = '.print_r($world_areas, true));
        //error_log('$world_areas_visible = '.print_r($world_areas_visible, true));
        //error_log('$world_areas_visible_positions = '.print_r($world_areas_visible_positions, true));
        //error_log('$current_world_area = '.print_r($current_world_area, true));
        //error_log('$current_world_area_position = '.print_r($current_world_area_position, true));
        // Use the above to generate the minimap image attributes and styles
        $minimap_image_attrs = '';
        $minimap_image_attrs .= 'data-image="'.$minimap_image_path.'" ';
        $minimap_image_attrs .= 'data-size="'.$minimap_size_string.'" ';
        $minimap_image_attrs .= 'data-cols="'.$minimap_cols.'" ';
        $minimap_image_attrs .= 'data-rows="'.$minimap_rows.'" ';
        $minimap_image_attrs .= 'data-show="'.implode(',', $world_areas_visible_positions).'" ';
        $minimap_image_attrs .= 'data-focus="'.$current_world_area_position.'" ';
        $minimap_image_attrs .= 'data-zoom="1" ';
        $minimap_image_styles = '';
        $minimap_image_styles .= $minimap_size_styles.' ';
        // Put it all together into the final return markup
        $return_markup .= '<div class="viewport world">';
            $return_markup .= '<div class="grid type '.$minimap_type.'"></div>';
            $return_markup .= '<div class="image" '.$minimap_image_attrs.' style="'.$minimap_size_styles.'"></div>';
            $return_markup .= '<div class="marker"><i class="arrow fas fa-caret-down"></i></div>';
        $return_markup .= '</div>';
        $return_markup .= '<div class="viewport area">';
            $return_markup .= '<div class="grid type '.$minimap_type.'"></div>';
            $return_markup .= '<div class="image"></div>';
            $return_markup .= '<div class="marker"><i class="arrow fas fa-angle-down"></i></div>';
        $return_markup .= '</div>';
        $return_markup .= '<div class="buttons">';
            $return_markup .= '<button class="button" data-view="world"><i class="fas fa-globe"></i></button>';
            $return_markup .= '<button class="button" data-view="area"><i class="fas fa-map"></i></button>';
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
        $field_background_styles = 'top: 0; left: 0; z-index: 1; background-image: url('.$field_background_image_url.');';
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
                $z_index = $top + 1;
                $hidden = in_array('hidden', $event_data) ? true : false; unset($event_data[array_search('hidden', $event_data)]);
                $locked = in_array('locked', $event_data) ? true : false; unset($event_data[array_search('locked', $event_data)]);
                $active = in_array('active', $event_data) ? true : false; unset($event_data[array_search('active', $event_data)]);
                $data = array_values($event_data); // remaining vaules if any
                $label = 'World '.ucwords(str_replace('-', ' ', $event_name));
                $attrs = 'data-event="'.$event_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $classes = 'sprite tile event'.($sprite ? ' '.$sprite : '').(!$hidden && !$locked  ? ' animate' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '').($active ? ' active' : '');
                $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.'; ';
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
                    'active' => $active,
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
                $z_index = $top + 1;
                //list($mod_top, $mod_left) = self::transform_point_with_matrix(array($left, $top), $map_perspective_matrix);
                //error_log('-> portal "'.$portal_name.'" at pos "'.$pos.'" (col: '.$col.', row: '.$row.')');
                //error_log('-> $top = '.$top.', $left = '.$left.' | $mod_top = '.$mod_top.', $mod_left = '.$mod_left);
                //$top = $mod_top; $left = $mod_left;
                $hidden = in_array('hidden', $portal_data) ? true : false;
                $locked = in_array('locked', $portal_data) ? true : false;
                $colour = false;
                $direction = false;
                if (in_array('black-alt', $portal_data)){ $colour = 'black'; }
                else if (in_array('red-alt', $portal_data)){ $colour = 'red'; }
                else if (in_array('blue-alt', $portal_data)){ $colour = 'blue'; }
                else if (in_array('yellow-alt', $portal_data)){ $colour = 'yellow'; }
                else if (in_array('green-alt', $portal_data)){ $colour = 'green'; }
                else if (in_array('purple-alt', $portal_data)){ $colour = 'purple'; }
                else if (in_array('orange-alt', $portal_data)){ $colour = 'orange'; }
                if (in_array('left-only', $portal_data)){ $direction = 'left'; }
                elseif (in_array('right-only', $portal_data)){ $direction = 'right'; }
                elseif (in_array('up-only', $portal_data)){ $direction = 'up'; }
                elseif (in_array('down-only', $portal_data)){ $direction = 'down'; }
                if ($hidden && $portal_name === 'spawn'){ continue; }
                //if ($this_is_cursor && !$locked && $portal_name !== 'spawn'){ $locked = true; }
                $sprite = 'portal';
                if (!empty($direction)){ $sprite .= '-'.$direction; }
                if (!empty($colour)){ $sprite .= ' portal-'.$colour; }
                $label = preg_match('/^goto__/i', $portal_name) ? strtoupper(preg_replace('/^goto__/i', '', $portal_name)) : ('World '.ucwords(str_replace('-', ' ', $portal_name)));
                $attrs = 'data-portal="'.$portal_name.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $classes = 'sprite tile '.$sprite.' '.($portal_name !== 'spawn' && !$hidden && !$locked  ? ' pulse' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
                $style .= ' animation-delay: '.(rand(0, 100) / 100).'s;';
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
                    'direction' => $direction,
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
            foreach ($button_sprites AS $button_namekey => $button_data){
                if (empty($button_data) || !is_array($button_data) || count($button_data) < 2){ continue; }
                $pos = $button_data[0]; list($col, $row) = explode('-', $pos); unset($button_data[0]);
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                $z_index = $top + 1;
                $colour = !empty($button_data[1]) ? $button_data[1] : 'black'; unset($button_data[1]);
                $state = !empty($button_data[2]) ? $button_data[2] : 'up'; unset($button_data[2]);
                $action = !empty($button_data[3]) ? $button_data[3] : ''; unset($button_data[3]);
                $hidden = false; if (in_array('hidden', $button_data)){ $hidden = true; unset($button_data[array_search('hidden', $button_data)]); }
                $locked = false; if (in_array('locked', $button_data)){ $locked = true; unset($button_data[array_search('locked', $button_data)]); }
                if (!empty($world_map_buttons[$button_namekey])){ $state = $world_map_buttons[$button_namekey]; }
                $data = array_values($button_data);
                $is_glowing = $state !== 'down' && !$hidden && !$locked ? true : false;
                $base_classes = 'sprite tile button';
                $kind_classes = $colour.' '.$state;
                $sprite = '<span class="'.$base_classes.' '.$kind_classes.'"></span>';
                $attrs = 'data-button="'.$button_namekey.'" data-colour="'.$colour.'" data-state="'.$state.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $styles = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
                $classes = $base_classes.($is_glowing ? ' glow' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $buttons_markup[] = '<span data-sprite="button" class="'.$classes.'" '.$attrs.' style="'.$styles.'">'.$sprite.'</span>';
                $button_symbols[$pos] = $button_namekey;
                $buttons_index[$button_namekey] = array(
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

    // Define a function for getting the SWITCHES LAYER sprite markup for the world map
    public static function get_switches_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_switches_layer_markup() called!');
        // BUTTONS LAYER
        $WORLD_SESSION = self::get_session();
        $world_switches = !empty($WORLD_SESSION['world_switches']) ? $WORLD_SESSION['world_switches'] : array();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_tilesize_offset = $map_config['tilesize_offset'];
        $switches_markup = array();
        $switch_symbols = array();
        $switches_index = array();
        if (!empty($map_data_parsed['switches'])){
            $switch_sprites = $map_data_parsed['switches'];
            $world_map_switches = !empty($world_switches[$world_map_token]) ? $world_switches[$world_map_token] : array();
            foreach ($switch_sprites AS $switch_namekey => $switch_data){
                if (empty($switch_data) || !is_array($switch_data) || count($switch_data) < 2){ continue; }
                $pos = $switch_data[0]; list($col, $row) = explode('-', $pos); unset($switch_data[0]);
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                $z_index = $top + 1;
                $colour = !empty($switch_data[1]) ? $switch_data[1] : 'black'; unset($switch_data[1]);
                $state = !empty($switch_data[2]) ? $switch_data[2] : 'up'; unset($switch_data[2]);
                $action = !empty($switch_data[3]) ? $switch_data[3] : ''; unset($switch_data[3]);
                $hidden = false; if (in_array('hidden', $switch_data)){ $hidden = true; unset($switch_data[array_search('hidden', $switch_data)]); }
                $locked = false; if (in_array('locked', $switch_data)){ $locked = true; unset($switch_data[array_search('locked', $switch_data)]); }
                if (!empty($world_map_switches[$switch_namekey])){ $state = $world_map_switches[$switch_namekey]; }
                $data = array_values($switch_data);
                $is_glowing = $state !== 'down' && !$hidden && !$locked ? true : false;
                $base_classes = 'sprite tile switch';
                $kind_classes = $colour.' '.$state;
                $sprite = '<span class="'.$base_classes.' '.$kind_classes.'"></span>';
                $attrs = 'data-switch="'.$switch_namekey.'" data-colour="'.$colour.'" data-state="'.$state.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $styles = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
                $classes = $base_classes.($is_glowing ? ' glow' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $switches_markup[] = '<span data-sprite="switch" class="'.$classes.'" '.$attrs.' style="'.$styles.'">'.$sprite.'</span>';
                $switch_symbols[$pos] = $switch_namekey;
                $switches_index[$switch_namekey] = array(
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
        $switch_symbols_json = json_encode($switch_symbols, JSON_NUMERIC_CHECK);
        $switches_index_json = json_encode($switches_index, JSON_NUMERIC_CHECK);
        $switches_markup[] = '<script data-json="switchSymbols" type="application/json">'.$switch_symbols_json.'</script>';
        $switches_markup[] = '<script data-json="switchesIndex" type="application/json">'.$switches_index_json.'</script>';
        return implode(PHP_EOL, $switches_markup);
    }

    // Define a function that returns the BLOCKS INDEX with details for usage on the world map
    public static function get_static_blocks_index(){
        $mmrpg_blocks_index = array(
            'super-block' => array(
                'name' => 'Super Block',
                'type' => 'earth',
                'weaknesses' => array('impact', 'explode')
                ),
            'fence-block' => array(
                'name' => 'Fence Block',
                'type' => 'nature',
                'weaknesses' => array('flame', 'cutter'),
                ),
            'cyber-block' => array(
                'name' => 'Virtual Block',
                'type' => 'electric',
                'weaknesses' => array('electric'),
                ),
            'phantom-block' => array(
                'name' => 'Phantom Block',
                'type' => 'shadow',
                'weaknesses' => array('shadow'),
                ),
            );
        return $mmrpg_blocks_index;
    }

    // Define a function for getting the BLOCKS LAYER sprite markup for the world map
    public static function get_blocks_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_blocks_layer_markup() called!');
        // BLOCKS LAYER
        $WORLD_SESSION = self::get_session();
        $world_blocks = !empty($WORLD_SESSION['world_blocks']) ? $WORLD_SESSION['world_blocks'] : array();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_tilesize_offset = $map_config['tilesize_offset'];
        $this_player_token = $this_prototype_data['this_player_token'];
        $this_is_cursor = $this_player_token === 'player' ? true : false;
        $mmrpg_index_blocks = self::get_index('blocks');
        $blocks_markup = array();
        $block_symbols = array();
        $blocks_index = array();
        if (!empty($map_data_parsed['blocks'])){
            $block_sprites = $map_data_parsed['blocks'];
            $world_map_blocks = !empty($world_blocks[$world_map_token]) ? $world_blocks[$world_map_token] : array();
            foreach ($block_sprites AS $block_namekey => $block_data){
                if (empty($block_data) || !is_array($block_data) || count($block_data) < 2){ continue; }
                $hidden = in_array('hidden', $block_data) ? true : false; if ($hidden){ unset($block_data[array_search('hidden', $block_data)]); }
                $locked = in_array('locked', $block_data) ? true : false; if ($locked){ unset($block_data[array_search('locked', $block_data)]); }
                $removed = in_array('removed', $block_data) ? true : false; if ($removed){ unset($block_data[array_search('removed', $block_data)]); }
                if (!empty($world_map_blocks[$block_namekey])){ $removed = true; }
                if ($removed){ continue; }
                $pos = $block_data[0]; list($col, $row) = explode('-', $pos); unset($block_data[0]);
                $sprite = !empty($block_data[1]) ? $block_data[1] : 'super-block'; unset($block_data[1]);
                $image = !empty($block_data[2]) ? $block_data[2] : ''; unset($block_data[2]);
                if (empty($image) && $sprite === 'super-block'){ $image = $map_data_parsed['field']; }
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                $z_index = $top + 1;
                $data = array_values($block_data);
                $info = !empty($mmrpg_index_blocks[$sprite]) ? $mmrpg_index_blocks[$sprite] : array();
                $type = !empty($info['type']) ? $info['type'] : '';
                $weaknesses = !empty($info['weaknesses']) ? $info['weaknesses'] : array();
                $colour = !empty($type) ? $type : 'none';
                $base_classes = 'sprite tile block';
                $kind_classes = $sprite.($image ? ' '.$image : '');
                $inner_sprite = '<span class="'.$base_classes.' '.$kind_classes.'"></span>';
                $attrs = 'data-block="'.$block_namekey.'" data-type="'.$type.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $styles = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
                $classes = $base_classes.($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $blocks_markup[] = '<span data-sprite="block" class="'.$classes.'" '.$attrs.' style="'.$styles.'">'.$inner_sprite.'</span>';
                $block_symbols[$pos] = $block_namekey;
                $blocks_index[$block_namekey] = array(
                    'pos' => $pos,
                    'sprite' => $sprite,
                    'image' => $image,
                    'colour' => $colour,
                    'col' => $col,
                    'row' => $row,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    'removed' => $removed,
                    'data' => $data,
                    'type' => $type,
                    'weaknesses' => $weaknesses,
                    );
            }
        }
        $block_symbols_json = json_encode($block_symbols, JSON_NUMERIC_CHECK);
        $blocks_index_json = json_encode($blocks_index, JSON_NUMERIC_CHECK);
        $blocks_markup[] = '<script data-json="blockSymbols" type="application/json">'.$block_symbols_json.'</script>';
        $blocks_markup[] = '<script data-json="blocksIndex" type="application/json">'.$blocks_index_json.'</script>';
        return implode(PHP_EOL, $blocks_markup);
    }

    // Define a function that returns the HAZARDS INDEX with details for usage on the world map
    public static function get_static_hazards_index(){
        $mmrpg_hazards_index = array(
            // Early-Game
            'crude-oil' => array(
                'name' => 'Crude Oil',
                'type' => 'earth',
                'weaknesses' => array('flame', 'water', 'earth'),
                'effects' => array('lower-team-attack', 1)
                ),
            'foamy-bubbles' => array(
                'name' => 'Foamy Bubbles',
                'type' => 'water',
                'weaknesses' => array('wind', 'cutter', 'missle'),
                'effects' => array('lower-team-defense', 1)
                ),
            'frozen-foothold' => array(
                'name' => 'Frozen Foothold',
                'type' => 'freeze',
                'weaknesses' => array('flame', 'impact', 'explode'),
                'effects' => array('lower-team-speed', 1)
                ),
            'flame-pillar' => array(
                'name' => 'Flame Pillar',
                'type' => 'flame',
                'weaknesses' => array('water', 'earth', 'freeze'),
                'effects' => array('lower-team-energy', 10)
                ),
            // Mid-Game
            'toxic-sludge' => array(
                'name' => 'Toxic Sludge',
                'type' => 'shadow',
                'weaknesses' => array('water', 'crystal'),
                'effects' => array('lower-team-attack', 2)
                ),
            'bramble-patch' => array(
                'name' => 'Bramble Patch',
                'type' => 'nature',
                'weaknesses' => array('flame', 'cutter'),
                'effects' => array('lower-team-defense', 2)
                ),
            'shifting-sands' => array(
                'name' => 'Shifting Sands',
                'type' => 'earth',
                'weaknesses' => array('nature', 'wind'),
                'effects' => array('lower-team-speed', 2)
                ),
            'woolly-clouds' => array(
                'name' => 'Woolly Clouds',
                'type' => 'electric',
                'weaknesses' => array('wind', 'swift'),
                'effects' => array('lower-team-energy', 25)
                ),
            // Late-Game
            'jagged-crystals' => array(
                'name' => 'Jagged Crystals',
                'type' => 'crystal',
                'weaknesses' => array('laser'),
                'effects' => array('lower-team-attack', 5)
                ),
            'laser-trap' => array(
                'name' => 'Laser Trap',
                'type' => 'laser',
                'weaknesses' => array('shield'),
                'effects' => array('lower-team-defense', 5)
                ),
            'gravity-well' => array(
                'name' => 'Gravity Well',
                'type' => 'space',
                'weaknesses' => array('space'),
                'effects' => array('lower-team-speed', 5)
                ),
            'tachyon-rift' => array(
                'name' => 'Tachyon Rift',
                'type' => 'time',
                'weaknesses' => array('time'),
                'effects' => array('lower-team-energy', 50)
                ),
            );
        return $mmrpg_hazards_index;
    }

    // Define a function for getting the HAZARDS LAYER sprite markup for the world map
    public static function get_hazards_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_hazards_layer_sprites() called!');
        // HAZARDS LAYER
        $WORLD_SESSION = self::get_session();
        $world_hazards = !empty($WORLD_SESSION['world_hazards']) ? $WORLD_SESSION['world_hazards'] : array();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_width = $map_config['pixel_width'];
        $map_height = $map_config['pixel_height'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_tilesize_offset = $map_config['tilesize_offset'];
        $this_player_token = $this_prototype_data['this_player_token'];
        $this_is_cursor = $this_player_token === 'player' ? true : false;
        $mmrpg_index_hazards = self::get_index('hazards');
        $hazards_markup = array();
        $hazard_symbols = array();
        $hazards_index = array();
        if (!empty($map_data_parsed['hazards'])){
            $hazard_sprites = $map_data_parsed['hazards'];
            $world_map_hazards = !empty($world_hazards[$world_map_token]) ? $world_hazards[$world_map_token] : array();
            foreach ($hazard_sprites AS $hazard_namekey => $hazard_data){
                if (empty($hazard_data) || !is_array($hazard_data)){ continue; }
                $hidden = in_array('hidden', $hazard_data) ? true : false; if ($hidden){ unset($hazard_data[array_search('hidden', $hazard_data)]); }
                $locked = in_array('locked', $hazard_data) ? true : false; if ($locked){ unset($hazard_data[array_search('locked', $hazard_data)]); }
                $removed = in_array('removed', $hazard_data) ? true : false; if ($removed){ unset($hazard_data[array_search('removed', $hazard_data)]); }
                if (!empty($world_map_hazards[$hazard_namekey])){ $removed = true; }
                if ($removed){ continue; }
                $pos = $hazard_data[0]; unset($hazard_data[0]);
                $sprite = !empty($hazard_data[1]) ? ($hazard_data[1] !== '-' ? $hazard_data[1] : '') : ''; unset($hazard_data[1]);
                $colour = !empty($hazard_data[2]) ? ($hazard_data[2] !== '-' ? $hazard_data[2] : '') : ''; unset($hazard_data[2]);
                $filter = !empty($hazard_data[3]) ? ($hazard_data[3] !== '-' ? $hazard_data[3] : '') : ''; unset($hazard_data[3]);
                $action = !empty($hazard_data[4]) ? ($hazard_data[4] !== '-' ? $hazard_data[4] : '') : ''; unset($hazard_data[4]);
                //error_log('processing hazard "'.$hazard_namekey.'" with pos "'.$pos.'"'.PHP_EOL.'-> $sprite = "'.$sprite.'"'.PHP_EOL.'-> filter = "'.$filter.'"'.PHP_EOL.'-> $action = "'.$action.'"');
                list($col, $row) = explode('-', $pos);
                $top = ($row - 1) * $map_tile_height + $map_tilesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_tilesize_offset[1];
                $z_index = $top + 1;
                $data = array_values($hazard_data); // remaining vaules if any
                $info = !empty($mmrpg_index_hazards[$sprite]) ? $mmrpg_index_hazards[$sprite] : array();
                $type = !empty($info['type']) ? $info['type'] : '';
                $weaknesses = !empty($info['weaknesses']) ? $info['weaknesses'] : array();
                $effects = !empty($info['effects']) ? $info['effects'] : array();
                if (empty($colour) && !empty($type)){ $colour = $type; }
                if (empty($filter) && !empty($effects)){ $filter = 'any'; }
                if (empty($action) && !empty($effects)){ $action = 'trigger-effects'; }
                if (empty($data) && !empty($effects)){ $data = $effects; }
                $label = ucwords(str_replace('-', ' ', $sprite)); //ucwords(str_replace('-', ' ', $hazard_namekey));
                $base_classes = 'sprite object hazard';
                $kind_classes = $colour.' '.$sprite;
                $inner_sprite = '<span class="wrap"><i class="'.$base_classes.' '.$kind_classes.'"></i></span>';
                $attrs = 'data-hazard="'.$hazard_namekey.'" data-colour="'.$colour.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
                $classes = $base_classes.' '.$kind_classes.(!$hidden && !$locked  ? ' animate' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.'; ';
                $hazards_markup[] = '<span data-sprite="hazard" class="'.$classes.'" '.$attrs.' style="'.$style.'">'.$inner_sprite.'</span>';
                $hazard_symbols[$pos] = $hazard_namekey;
                $hazards_index[$hazard_namekey] = array(
                    'pos' => $pos,
                    'sprite' => $sprite,
                    'colour' => $colour,
                    'filter' => $filter,
                    'action' => $action,
                    'col' => $col,
                    'row' => $row,
                    'label' => $label,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    'removed' => $removed,
                    'data' => $data,
                    'type' => $type,
                    'weaknesses' => $weaknesses,
                    'effects' => $effects,
                    );
            }
        }
        $hazard_symbols_json = json_encode($hazard_symbols, JSON_NUMERIC_CHECK);
        $hazards_index_json = json_encode($hazards_index, JSON_NUMERIC_CHECK);
        $hazards_markup[] = '<script data-json="hazardSymbols" type="application/json">'.$hazard_symbols_json.'</script>';
        $hazards_markup[] = '<script data-json="hazardsIndex" type="application/json">'.$hazards_index_json.'</script>';
        return implode(PHP_EOL, $hazards_markup);
    }

    // Define a function for getting the BATTLES LAYER sprite markup for the world map
    public static function get_battles_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_battles_layer_markup() called!');
        // BATTLES LAYER
        $WORLD_SESSION = self::get_session();
        $world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
        $world_symbols = !empty($WORLD_SESSION['world_symbols']) ? $WORLD_SESSION['world_symbols'] : array();
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
        $world_map_encounter_symbols = !empty($world_symbols[$world_map_token]['encounters']) ? $world_symbols[$world_map_token]['encounters'] : array();
        if (empty($world_map_encounters)){
            $world_map_encounters = rpg_world::generate_worldmap_encounters($this_prototype_data, $map_data_parsed);
            self::update_session('world_encounters', $world_map_token, $world_map_encounters);
        }
        //error_log('$world_map_encounters = '.print_r($world_map_encounters, true));
        $battles_markup = array();
        $battle_symbols = array();
        $battles_index = array();
        foreach ($world_map_encounters AS $encounter_namekey => $encounter_data){
            $kind = $encounter_data[0]; $subkind = '';
            if (strstr($kind, '/')){ list($kind, $subkind) = explode('/', $kind, 2); }
            //$xkind = rpg_world::get_xkind($kind);
            $token = $encounter_data[1];
            $alt = $encounter_data[2];
            $pos = $encounter_data[3];
            $battle = $encounter_data[4];
            $name = $encounter_data[5];
            //error_log('-> processing battle w/'.PHP_EOL.'-> $token ='.' '.$token.PHP_EOL.'-> $kind = '.$kind.PHP_EOL.'-> $subkind = '.$subkind.PHP_EOL.'-> $alt = '.$alt.PHP_EOL.'-> $pos = '.$pos.PHP_EOL.'-> $battle = '.$battle);
            if (!rpg_battle::has_index_info($battle)){ continue; }
            if ($subkind === 'rescue' && mmrpg_prototype_robot_unlocked('', $token)){ continue; }
            if (!empty($world_map_encounter_symbols[$encounter_namekey])){ $pos = $world_map_encounter_symbols[$encounter_namekey]; }
            list($col, $row) = explode('-', $pos);
            $maxcols = $map_col_size;
            $maxrows = $map_row_size;
            $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
            $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
            $z_index = $top + 1;
            $dir = ($col > ($map_col_size / 2)) ? 'left' : 'right';
            if (mt_rand(1, 2) === 1){ $dir = $dir !== 'left' ? 'left' : 'right'; }
            $class = 'battle vs-'.$subkind.' bounce';
            if ($subkind === 'master'){ $class .= ' always-zoom'; }
            elseif ($subkind === 'boss'){ $class .= ' always-zoom'; }
            elseif ($subkind === 'rescue'){ $class .= ' always-zoom frame-lock'; }
            $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
            $attrs = 'data-battle="'.$battle.'" data-label="'.$name.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"';
            $markup = self::get_sprite($kind, $token, $alt, $dir, $class, $style, $attrs);
            $markup = str_replace('data-sprite="'.$kind.'"', 'data-sprite="battle-'.$kind.'"', $markup);
            if ($subkind === 'rescue'){ $markup = str_replace('data-frame="00"', 'data-frame="08"', $markup); }
            $battles_markup[] = $markup;
            $battle_symbols[$pos] = $battle;
            $battles_index[$battle] = array(
                'kind' => $kind,
                'kind2' => $subkind,
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
    public static function get_team_sprites_markup($map_data_parsed, $team_sprites, $target_position = '1-1', $team_class = 'team', $team_dir = 'down-right'){
        //error_log('rpg_world::get_team_sprites_markup() called!');
        $map_config = $map_data_parsed['config'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];
        $sprites = array();
        list($col, $row) = explode('-', $target_position);
        $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
        $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
        $z_index = $top + 1;
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
                else { $top -= 2; }
                $z_index = $top + 1;
                }
            $class = $team_class.' bounce'.($disabled ? ' disabled' : '');
            $styles = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
            $attrs = 'data-key="'.$key.'"';
            if (!empty($id)){ $attrs .= ' data-id="'.$id.'"'; }
            $markup = self::get_sprite($kind, $img, $alt, $dir, $class, $styles, $attrs);
            $markup = str_replace('data-sprite="'.$kind.'"', 'data-sprite="'.$team_class.'-'.$kind.'" data-'.$kind.'="'.$id.'_'.$token.'"', $markup);
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
        $z_index = $top + 1;
        $class = 'cursor bounce';
        $styles = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.';';
        $attrs = 'data-pos="'.$team_position.'" data-col="'.$col.'" data-row="'.$row.'"';
        $cursor_markup = self::get_cursor_sprite($team_direction, $class, $styles, $attrs);
        $cursor_markup = str_replace('data-sprite="robot"', 'data-sprite="team-cursor"', $cursor_markup);
        $team_markup[] = $cursor_markup;
        // Now we can generate the markup for the actual team sprites if any were defined
        $team_markup[] = self::get_team_sprites_markup($map_data_parsed, $team_sprites, $team_position, 'team', $team_direction);
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
        $world_spawn_portal = !empty($map_data_parsed['portals']['spawn']) ? $map_data_parsed['portals']['spawn'] : false;
        $world_spawn_position = $world_spawn_portal ? $world_spawn_portal[0] : '0-0';
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
            elseif ($tmp_world_position === $world_spawn_position){ continue; } // skip if still on spawn point
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
                    if (!empty($robot_image) && $robot_image !== $robot_token){ $robot[] = $robot_image; }
                    $tmp_team_sprites[] = $robot;
                }
            }
            $rivals_markup[] = self::get_team_sprites_markup($map_data_parsed, $tmp_team_sprites, $tmp_world_position, 'rival', $tmp_world_direction);
        }
        $rival_symbols_json = json_encode($rival_symbols, JSON_NUMERIC_CHECK);
        $rivals_markup[] = '<script data-json="rivalSymbols" type="application/json">'.$rival_symbols_json.'</script>';
        // Return the gernated rivals markup
        return implode(PHP_EOL, $rivals_markup);
    }

    // Define a function for getting the ITEMS LAYER sprite markup for the world map
    public static function get_items_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_items_layer_sprites() called!');
        // ITEMS LAYER
        $WORLD_SESSION = self::get_session();
        $world_items = !empty($WORLD_SESSION['world_items']) ? $WORLD_SESSION['world_items'] : array();
        $world_symbols = !empty($WORLD_SESSION['world_symbols']) ? $WORLD_SESSION['world_symbols'] : array();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_width = $map_config['pixel_width'];
        $map_height = $map_config['pixel_height'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];
        $this_player_token = $this_prototype_data['this_player_token'];
        $this_player_items = $this_prototype_data['this_player_items_index'];
        $this_is_cursor = $this_player_token === 'player' ? true : false;
        $non_hovering_items = array('small-screw', 'large-screw');
        $mmrpg_index_items = self::get_index('items');
        $world_pickups = !empty($WORLD_SESSION['world_pickups']) ? $WORLD_SESSION['world_pickups'] : array();
        $world_map_pickups = !empty($world_pickups[$world_map_token]) ? $world_pickups[$world_map_token] : array();
        $world_map_pickup_symbols = !empty($world_symbols[$world_map_token]['pickups']) ? $world_symbols[$world_map_token]['pickups'] : array();
        if (empty($world_map_pickups)){
            $world_map_pickups = rpg_world::generate_worldmap_pickups($this_prototype_data, $map_data_parsed);
            self::update_session('world_pickups', $world_map_token, $world_map_pickups);
        }
        $items_markup = array();
        $item_symbols = array();
        $items_index = array();
        //error_log('checking for items in $map_data_parsed[items]: '.print_r($map_data_parsed['items'], true));
        //error_log('and combining with any $world_map_pickups: '.print_r($world_map_pickups, true));
        $world_map_items = array();
        if (!empty($map_data_parsed['items'])){
            foreach ($map_data_parsed['items'] AS $item_namekey => $item_info){
                $world_map_items[$item_namekey] = $item_info;
            }
        }
        if (!empty($world_map_pickups)){
            foreach ($world_map_pickups AS $pickup_key => $pickup_data){
                list($kind, $token, $position, $namekey, $label) = $pickup_data;
                $world_map_items[$namekey] = array($position, $token);
            }
        }
        //error_log('-> combined $world_map_items = '.print_r($world_map_items, true));
        if (!empty($world_map_items)){
            $item_sprites = $world_map_items;
            $world_map_items = !empty($world_items[$world_map_token]) ? $world_items[$world_map_token] : array();
            $world_map_item_symbols = !empty($world_symbols[$world_map_token]['items']) ? $world_symbols[$world_map_token]['items'] : array();
            //error_log('$item_sprites = '.print_r($item_sprites, true));
            //error_log('$world_map_items = '.print_r($world_map_items, true));
            //error_log('$world_map_item_symbols = '.print_r($world_map_item_symbols, true));
            foreach ($item_sprites AS $item_namekey => $item_data){
                //error_log('----------');
                //error_log('Processing item "'.$item_namekey.'" with data: '.print_r($item_data, true));
                if (empty($item_data) || !is_array($item_data)){ continue; }
                if (empty($item_data[0]) || !is_string($item_data[0]) || !preg_match('/^\d+-\d+$/', $item_data[0])){ continue; } // skip if no position
                if (empty($item_data[1]) || !is_string($item_data[1])){ continue; } // skip if no token
                $claimed = !empty($world_map_items[$item_namekey]) ? $world_map_items[$item_namekey] : 0; // unix-timestamp
                //error_log('-> '.$item_namekey.' already claimed, skipping...');
                $kind = 'item';
                $hidden = in_array('hidden', $item_data) ? true : false; if ($hidden){ unset($item_data[array_search('hidden', $item_data)]); }
                $locked = in_array('locked', $item_data) ? true : false; if ($locked){ unset($item_data[array_search('locked', $item_data)]); }
                $anchored = in_array('anchored', $item_data) ? true : false; if ($anchored){ unset($item_data[array_search('anchored', $item_data)]); }
                $data = array_values($item_data); // remaining values if any
                if ($claimed && !$anchored){
                    //error_log('-> '.$item_namekey.' already claimed and not anchored, skipping...');
                    continue;
                    } // skip if already claimed but not anchored
                $pos = $item_data[0]; unset($item_data[0]);
                $token = !empty($item_data[1]) ? $item_data[1] : ''; unset($item_data[1]);
                $quantity = !empty($item_data[2]) ? $item_data[2] : ''; unset($item_data[2]);
                $repeat = !empty($item_data[3]) ? $item_data[3] : ''; unset($item_data[3]);
                $num_in_set = false; $set_token = false;
                if (strstr($token, '__')){  $set_token = $token; list($token, $num_in_set) = explode('__', $set_token, 2); }
                $unlock_token = !empty($set_token) ? $set_token : $token;
                $quantity = intval(trim($quantity, 'x')); if (!$quantity){ $quantity = 1; }
                if (!empty($world_map_item_symbols[$item_namekey])){ $pos = $world_map_item_symbols[$item_namekey]; }
                list($col, $row) = explode('-', $pos);
                //error_log('-> $item_namekey = '.print_r($item_namekey, true));
                //error_log('-> $kind = '.print_r($kind, true));
                //error_log('-> $kind = '.print_r($kind, true));
                //error_log('-> $pos = '.print_r($pos, true));
                //error_log('-> $token = '.print_r($token, true));
                //error_log('-> $quantity = '.print_r($quantity, true));
                //error_log('-> $repeat = '.print_r($repeat, true));
                //error_log('-> $hidden = '.print_r($hidden, true));
                //error_log('-> $locked = '.print_r($locked, true));
                //error_log('-> $anchored = '.print_r($anchored, true));
                //error_log('-> $num_in_set = '.print_r($num_in_set, true));
                //error_log('-> $set_token = '.print_r($set_token, true));
                //error_log('-> $unlock_token = '.print_r($unlock_token, true));
                if (!isset($mmrpg_index_items[$token])){
                    //error_log('-> '.$item_namekey.' has invalid token "'.$token.'", skipping...');
                    continue;
                    } // skip if not valid item
                $info = $mmrpg_index_items[$token];
                $subclass = !empty($info['item_subclass']) ? $info['item_subclass'] : '';
                $subtypes = array((!empty($info['item_type']) ? $info['item_type'] : ''), (!empty($info['item_type2']) ? $info['item_type2'] : ''));
                $is_unique = $subclass === 'event' && !strstr($token, '-heart') ? true : false;
                $is_already_owned = !empty($this_player_items[$unlock_token]) ? true : false;
                //error_log('-> $info = '.print_r(json_encode($info), true));
                //error_log('-> $subclass = '.print_r($subclass, true));
                //error_log('-> $is_unique = '.print_r($is_unique, true));
                //error_log('-> $is_already_owned = '.print_r($is_already_owned, true));
                if ($is_unique && $is_already_owned && !$anchored){
                    //error_log('-> '.$item_namekey.' is unique and already owned, skipping...');
                    continue;
                    } // skip if unique and already owned
                elseif ($is_unique){
                    $quantity = 1;
                    $repeat = 'once';
                    } // else set quantity to 1 and repeat to once
                //error_log('-> generating item "'.$item_namekey.'" with pos "'.$pos.'"'.PHP_EOL.'-> $token = "'.$token.'"'.PHP_EOL.'-> $quantity = "'.$quantity.'"'.PHP_EOL.'-> $info = '.print_r($info, true));
                $animated = !$hidden && !in_array($token, $non_hovering_items) ? true : false;
                $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                $z_index = $top + 1;
                $label = $info['item_name'];
                $class = $token.($animated  ? ' animate' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                if ($subclass === 'event'){ $class .= ' always-zoom'; }
                elseif (strstr($token, '-star')){ $class .= ' always-zoom'; $z_index -= 2; }
                elseif (strstr($token, '-core') && $anchored){ $class .= ' always-zoom'; }
                $colour = !empty($subtypes) ? implode(' ', array_filter($subtypes)) : '';
                $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.'; ';
                $attrs = 'data-item="'.$item_namekey.'" data-colour="'.$colour.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"'; //data-key="'.$item_namekey.'"
                $markup = self::get_sprite($kind, $token, '', 'right', $class, $style, $attrs, 'icon');
                $markup = str_replace('data-sprite="'.$kind.'"', 'data-sprite="'.$kind.'-pickup"', $markup);
                $markup = str_replace('data-token="'.$token.'"', 'data-token="'.$unlock_token.'"', $markup);
                $items_markup[] = $markup;
                $item_symbols[$pos] = $item_namekey;
                $items_index[$item_namekey] = array(
                    'pos' => $pos,
                    'token' => $unlock_token,
                    'quantity' => $quantity,
                    'repeat' => $repeat,
                    'col' => $col,
                    'row' => $row,
                    'label' => $label,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    'anchored' => $anchored,
                    'claimed' => $claimed,
                    'data' => $data,
                    );
            }
        }
        $item_symbols_json = json_encode($item_symbols, JSON_NUMERIC_CHECK);
        $items_index_json = json_encode($items_index, JSON_NUMERIC_CHECK);
        $items_markup[] = '<script data-json="itemSymbols" type="application/json">'.$item_symbols_json.'</script>';
        $items_markup[] = '<script data-json="itemsIndex" type="application/json">'.$items_index_json.'</script>';
        return implode(PHP_EOL, $items_markup);
    }

    // Define a function for getting the ABILITIES LAYER sprite markup for the world map
    public static function get_abilities_layer_markup($this_prototype_data, $map_data_parsed){
        //error_log('rpg_world::get_abilities_layer_sprites() called!');
        // ABILITIES LAYER
        $WORLD_SESSION = self::get_session();
        $world_abilities = !empty($WORLD_SESSION['world_abilities']) ? $WORLD_SESSION['world_abilities'] : array();
        $world_symbols = !empty($WORLD_SESSION['world_symbols']) ? $WORLD_SESSION['world_symbols'] : array();
        $map_config = $map_data_parsed['config'];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $map_width = $map_config['pixel_width'];
        $map_height = $map_config['pixel_height'];
        $map_tile_height = $map_config['tile_height'];
        $map_tile_width = $map_config['tile_width'];
        $map_spritesize_offset = $map_config['spritesize_offset'];
        $this_player_token = $this_prototype_data['this_player_token'];
        $this_player_abilities = $this_prototype_data['this_player_abilities'];
        $this_is_cursor = $this_player_token === 'player' ? true : false;
        $mmrpg_index_abilities = self::get_index('abilities');
        $abilities_markup = array();
        $ability_symbols = array();
        $abilities_index = array();
        //error_log('checking for abilities in $map_data_parsed[abilities]: '.print_r($map_data_parsed['abilities'], true));
        if (!empty($map_data_parsed['abilities'])){
            $ability_sprites = $map_data_parsed['abilities'];
            $world_map_abilities = !empty($world_abilities[$world_map_token]) ? $world_abilities[$world_map_token] : array();
            $world_map_ability_symbols = !empty($world_symbols[$world_map_token]['abilities']) ? $world_symbols[$world_map_token]['abilities'] : array();
            //error_log('$ability_sprites = '.print_r($ability_sprites, true));
            //error_log('$world_map_abilities = '.print_r($world_map_abilities, true));
            foreach ($ability_sprites AS $ability_namekey => $ability_data){
                //error_log('Processing ability "'.$ability_namekey.'" with data: '.print_r($ability_data, true));
                if (empty($ability_data) || !is_array($ability_data)){ continue; }
                $kind = 'ability';
                $hidden = in_array('hidden', $ability_data) ? true : false; if ($hidden){ unset($ability_data[array_search('hidden', $ability_data)]); }
                $locked = in_array('locked', $ability_data) ? true : false; if ($locked){ unset($ability_data[array_search('locked', $ability_data)]); }
                $anchored = in_array('anchored', $ability_data) ? true : false; if ($anchored){ unset($ability_data[array_search('anchored', $ability_data)]); }
                $data = array_values($ability_data); // remaining values if any
                $pos = $ability_data[0]; unset($ability_data[0]);
                $token = !empty($ability_data[1]) ? $ability_data[1] : ''; unset($ability_data[1]);
                $claimed = !empty($world_map_abilities[$ability_namekey]) ? $world_map_abilities[$ability_namekey] : 0; // unix-timestamp
                //error_log('-> $kind = '.print_r($kind, true));
                //error_log('-> $pos = '.print_r($pos, true));
                //error_log('-> $token = '.print_r($token, true));
                //error_log('-> $claimed = '.print_r($claimed, true));
                if (empty($pos) || !is_string($pos) || !preg_match('/^\d+-\d+$/', $pos)){ continue; } // skip if no position
                if (empty($token) || !is_string($token) || !isset($mmrpg_index_abilities[$token])){ continue; } // skip if no token
                if ($claimed && !$anchored){ continue; } // skip if already claimed but not anchored
                if (!empty($world_map_ability_symbols[$ability_namekey])){ $pos = $world_map_ability_symbols[$ability_namekey]; }
                list($col, $row) = explode('-', $pos);
                $info = $mmrpg_index_abilities[$token];
                $subclass = !empty($info['ability_subclass']) ? $info['ability_subclass'] : '';
                $subtypes = array((!empty($info['ability_type']) ? $info['ability_type'] : ''), (!empty($info['ability_type2']) ? $info['ability_type2'] : ''));
                //error_log('-> $info = '.print_r(json_encode($info), true));
                $is_already_owned = in_array($token, $this_player_abilities) ? true : false;
                //error_log('-> $is_already_owned = '.print_r($is_already_owned, true));
                if ($is_already_owned && !$anchored){ continue; } // skip if already owned
                //if ($is_already_owned){ error_log('should-skip-owned-abilities'); } // skip if already owned
                // Otherwise we can actually show this ability on the map
                //error_log('processing ability "'.$ability_namekey.'" with pos "'.$pos.'"'.PHP_EOL.'-> $token = "'.$token.'"'.PHP_EOL.'-> $info = '.print_r($info, true));
                $top = ($row - 1) * $map_tile_height + $map_spritesize_offset[0];
                $left = ($col - 1) * $map_tile_width + $map_spritesize_offset[1];
                $z_index = $top + 1;
                $label = $info['ability_name'];
                $class = $token.(!$hidden  ? ' animate' : '').($hidden ? ' hidden' : '').($locked ? ' locked' : '');
                $types = 'type '.(!empty($info['ability_type']) ? $info['ability_type'] : 'none').(!empty($info['ability_type2']) ? ' '.$info['ability_type2'] : '');
                $colour = !empty($subtypes) ? implode(' ', array_filter($subtypes)) : '';
                $style = 'top: '.$top.'px; left: '.$left.'px; z-index: '.$z_index.'; ';
                $attrs = 'data-ability="'.$ability_namekey.'" data-colour="'.$colour.'" data-token="'.$token.'" data-label="'.$label.'" data-pos="'.$pos.'" data-col="'.$col.'" data-row="'.$row.'"'; //data-key="'.$ability_namekey.'"
                $markup = self::get_sprite($kind, $token, '', 'right', $class, $style, $attrs, 'icon');
                $markup = str_replace('data-sprite="'.$kind.'"', 'data-sprite="'.$kind.'-pickup"', $markup);
                $markup = str_replace('class="wrap"', 'class="wrap"', $markup);
                $markup = str_replace('class="back"', 'class="back '.$types.'"', $markup);
                $abilities_markup[] = $markup;
                $ability_symbols[$pos] = $ability_namekey;
                $abilities_index[$ability_namekey] = array(
                    'pos' => $pos,
                    'token' => $token,
                    'col' => $col,
                    'row' => $row,
                    'label' => $label,
                    'hidden' => $hidden,
                    'locked' => $locked,
                    'claimed' => $claimed,
                    'data' => $data,
                    );
            }
        }
        $ability_symbols_json = json_encode($ability_symbols, JSON_NUMERIC_CHECK);
        $abilities_index_json = json_encode($abilities_index, JSON_NUMERIC_CHECK);
        $abilities_markup[] = '<script data-json="abilitySymbols" type="application/json">'.$ability_symbols_json.'</script>';
        $abilities_markup[] = '<script data-json="abilitiesIndex" type="application/json">'.$abilities_index_json.'</script>';
        return implode(PHP_EOL, $abilities_markup);
    }


    // -- WORLDMAP REFRESH METHODS -- //

    // Define a function for checking and refreshing world map portals, butons, etc. with persistent data
    public static function refresh_world_map($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map() called!');

        // If there are any portals define, check to see if any are being covered by battles or obstacles
        self::refresh_map_portals($this_prototype_data, $map_data_parsed);

        // If there are any events defined, check to see if any of them have been interacted with already
        self::refresh_map_events($this_prototype_data, $map_data_parsed);

        // If there are any buttons defined, check to see if any of them have been interacted with already
        self::refresh_map_buttons($this_prototype_data, $map_data_parsed);

        // If there are any switches defined, check to see if any of them have been interacted with already
        self::refresh_map_switches($this_prototype_data, $map_data_parsed);

        // If there are any blocks defined, check to see if any of them have been interacted with already
        self::refresh_map_blocks($this_prototype_data, $map_data_parsed);

        // If there are any hazards defined, check to see if any of them have been interacted with already
        self::refresh_map_hazards($this_prototype_data, $map_data_parsed);

        // If there are any platforms defined, check to see if any of them have been interacted with already
        self::refresh_map_platforms($this_prototype_data, $map_data_parsed);

        // Return true on success
        return true;
    }

    // If there are any portals define, check to see if any are being covered by battles or obstacles
    public static function refresh_map_portals($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_portals() called!');
        if (empty($map_data_parsed['portals'])){ return; }
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
        $world_map_encounters = !empty($world_encounters[$world_map_token]) ? $world_encounters[$world_map_token] : array();
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
        // Return true on success
        return true;
    }

    // If there are any events defined, check to see if any of them have been interacted with already
    public static function refresh_map_events($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_events() called!');
        if (empty($map_data_parsed['events'])){ return; }
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $event_sprites = $map_data_parsed['events'];
        $world_events = !empty($WORLD_SESSION['world_events'][$world_map_token]) ? $WORLD_SESSION['world_events'][$world_map_token] : array();
        $dynamic_event_actions = array('drop-zone'); // these event actions change based on persistent/dynamic data
        foreach ($event_sprites AS $event_name => $event_data){
            if (empty($event_data) || !is_array($event_data)){ continue; }
            $pos = $event_data[0]; unset($event_data[0]);
            $sprite = !empty($event_data[1]) ? $event_data[1] : ''; unset($event_data[1]);
            $filter = !empty($event_data[2]) ? $event_data[2] : ''; unset($event_data[2]);
            $action = !empty($event_data[3]) ? $event_data[3] : ''; unset($event_data[3]);
            $hidden = false; if (in_array('hidden', $event_data)){ $hidden = true; unset($event_data[array_search('hidden', $event_data)]); }
            $locked = false; if (in_array('locked', $event_data)){ $locked = true; unset($event_data[array_search('locked', $event_data)]); }
            $data = array_values($event_data);
            if (!empty($world_events[$event_name])){ $action = $world_events[$event_name]; }
            if (empty($action) || !in_array($action, $dynamic_event_actions)){ continue; }
            //error_log('[event-check] world map event "'.$event_name.'" has action "'.$action.'"');
            // ...
            // event action for DROP-ZONES to check if their action should be preset or not
            if ($action === 'drop-zone'){
                //error_log('world map event "'.$event_name.'" ('.$pos.') is a drop-zone, checking for items here');
                $position = $pos;
                $world_item_symbols = !empty($WORLD_SESSION['world_symbols'][$world_map_token]['items']) ? $WORLD_SESSION['world_symbols'][$world_map_token]['items'] : array();
                $world_ability_symbols = !empty($WORLD_SESSION['world_symbols'][$world_map_token]['abilities']) ? $WORLD_SESSION['world_symbols'][$world_map_token]['abilities'] : array();
                $world_item_symbols_by_pos = array_flip($world_item_symbols);
                $world_ability_symbols_by_pos = array_flip($world_ability_symbols);
                //error_log('-> $position = '.print_r($position, true));
                //error_log('-> $world_item_symbols = '.print_r($world_item_symbols, true));
                //error_log('-> $world_ability_symbols = '.print_r($world_ability_symbols, true));
                //error_log('-> $world_item_symbols_by_pos = '.print_r($world_item_symbols_by_pos, true));
                //error_log('-> $world_ability_symbols_by_pos = '.print_r($world_ability_symbols_by_pos, true));
                $item_dropped_here = !empty($world_item_symbols_by_pos[$position]) ? $world_item_symbols_by_pos[$position] : '';
                $ability_dropped_here = !empty($world_ability_symbols_by_pos[$position]) ? $world_ability_symbols_by_pos[$position] : '';
                //error_log('-> $item_dropped_here = '.print_r($item_dropped_here, true));
                //error_log('-> $ability_dropped_here = '.print_r($ability_dropped_here, true));
                if (!$item_dropped_here && !$ability_dropped_here){ continue; }
                list($drop_filter, $drop_action) = $data;
                $drop_filter = strstr($drop_filter, ':') ? explode(':', $drop_filter) : array($drop_filter);
                $drop_filter_kind = $drop_filter[0];
                $drop_filter_values = strstr($drop_filter[1], ',') ? explode(',', $drop_filter[1]) : array($drop_filter[1]);
                //error_log('-> $drop_filter = '.print_r(json_encode($drop_filter), true));
                //error_log('-> $drop_filter_kind = '.print_r($drop_filter_kind, true));
                //error_log('-> $drop_filter_values = '.print_r(json_encode($drop_filter_values), true));
                if (!empty($item_dropped_here)){
                    //error_log('-----------------------------------');
                    //error_log('-> item detected in drop-zone "'.$event_name.'" ('.$pos.')! let us check if it\'s valid...');
                    $item_namekey = $item_dropped_here;
                    $item_eventinfo = !empty($map_data_parsed['items'][$item_namekey]) ? $map_data_parsed['items'][$item_namekey] : array();
                    $item_position = !empty($item_eventinfo[0]) ? $item_eventinfo[0] : '';
                    $item_token = !empty($item_eventinfo[1]) ? $item_eventinfo[1] : '';
                    if (strstr($item_token, '__')){ list($item_token) = explode('__', $item_token, 2); }
                    //error_log('-> $item_namekey = '.print_r($item_namekey, true));
                    //error_log('-> $item_eventinfo = '.print_r(json_encode($item_eventinfo), true));
                    //error_log('-> $item_position = '.print_r($item_position, true));
                    //error_log('-> $item_token = '.print_r($item_token, true));
                    if ($drop_filter_kind === 'item'){
                        if (in_array($item_token, $drop_filter_values) || in_array('any', $drop_filter_values)){
                            //error_log('-> item "'.$item_token.'" is valid for this drop-zone!');
                            //error_log('-> activating drop-zone "'.$event_name.'" ('.$pos.') since it has a valid item in it');
                            $event_data = $map_data_parsed['events'][$event_name];
                            $event_data[] = 'active';
                            $map_data_parsed['events'][$event_name] = $event_data;
                            $item_data = $map_data_parsed['items'][$item_namekey];
                            $item_data[] = 'locked';
                            $map_data_parsed['items'][$item_namekey] = $item_data;
                            //error_log('-> $map_data_parsed[\'events\']['.$event_name.'] = '.print_r($map_data_parsed['events'][$event_name], true));
                            //error_log('-> $map_data_parsed[\'items\']['.$item_namekey.'] = '.print_r($map_data_parsed['items'][$item_namekey], true));

                        } else {
                            //error_log('-> item "'.$item_token.'" is NOT valid for this drop-zone, leaving it deactivated');
                        }
                    } else {
                        //error_log('-> drop-zone "'.$event_name.'" is not configured to accept items, leaving it deactivated');
                    }
                } elseif (!empty($ability_dropped_here)){
                    //error_log('-----------------------------------');
                    //error_log('-> ability detected in drop-zone "'.$event_name.'" ('.$pos.')! let us check if it\'s valid...');
                    $ability_namekey = $ability_dropped_here;
                    $ability_eventinfo = !empty($map_data_parsed['abilities'][$ability_namekey]) ? $map_data_parsed['abilities'][$ability_namekey] : array();
                    $ability_position = !empty($ability_eventinfo[0]) ? $ability_eventinfo[0] : '';
                    $ability_token = !empty($ability_eventinfo[1]) ? $ability_eventinfo[1] : '';
                    //error_log('-> $ability_namekey = '.print_r($ability_namekey, true));
                    //error_log('-> $ability_eventinfo = '.print_r(json_encode($ability_eventinfo), true));
                    //error_log('-> $ability_position = '.print_r($ability_position, true));
                    //error_log('-> $ability_token = '.print_r($ability_token, true));
                    if ($drop_filter_kind === 'ability'){
                        if (in_array($ability_token, $drop_filter_values) || in_array('any', $drop_filter_values)){
                            //error_log('-> ability "'.$ability_token.'" is valid for this drop-zone!');
                            //error_log('-> activating drop-zone "'.$event_name.'" ('.$pos.') since it has a valid ability in it');
                            $event_data = $map_data_parsed['events'][$event_name];
                            $event_data[] = 'active';
                            $map_data_parsed['events'][$event_name] = $event_data;
                            $ability_data = $map_data_parsed['abilities'][$ability_namekey];
                            $ability_data[] = 'locked';
                            $map_data_parsed['abilities'][$ability_namekey] = $ability_data;
                        } else {
                            //error_log('-> ability "'.$ability_token.'" is NOT valid for this drop-zone, leaving it deactivated');
                        }
                    } else {
                        //error_log('-> drop-zone "'.$event_name.'" is not configured to accept abilities, leaving it deactivated');
                    }
                } else {
                    //error_log('-> no item/ability dropped here yet, leaving this drop-zone unlocked');
                }
            }
            // ...
        }
        // Return true on success
        return true;
    }

    // If there are any buttons defined, check to see if any of them have been interacted with already
    public static function refresh_map_buttons($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_buttons() called!');
        if (empty($map_data_parsed['buttons'])){ return; }
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
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
        // Return true on success
        return true;
    }

    // If there are any switches defined, check to see if any of them have been interacted with already
    public static function refresh_map_switches($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_switches() called!');
        if (empty($map_data_parsed['switches'])){ return; }
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;

        // TODO: implement switch refresh functionality

        // Return true on success
        return true;
    }

    // If there are any blocks defined, check to see if any of them have been interacted with already
    public static function refresh_map_blocks($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_blocks() called!');
        if (empty($map_data_parsed['blocks'])){ return; }
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;

        // TODO: implement block refresh functionality

        // Return true on success
        return true;
    }

    // If there are any hazards defined, check to see if any of them have been interacted with already
    public static function refresh_map_hazards($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_hazards() called!');
        if (empty($map_data_parsed['hazards'])){ return; }
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $hazard_sprites = $map_data_parsed['hazards'];
        $world_hazards = !empty($WORLD_SESSION['world_hazards'][$world_map_token]) ? $WORLD_SESSION['world_hazards'][$world_map_token] : array();
        foreach ($hazard_sprites AS $hazard_name => $hazard_data){
            if (empty($hazard_data) || !is_array($hazard_data)){ continue; }
            // If this hazard has a timestamp in the session, it means the player removed it
            if (!empty($world_hazards[$hazard_name])){
                // Append the 'removed' flag to the hazard's data array so the rest of the engine knows
                if (!in_array('removed', $hazard_data)){
                    $hazard_data[] = 'removed';
                    $map_data_parsed['hazards'][$hazard_name] = $hazard_data;
                }
            }
        }
        // Return true on success
        return true;
    }

    // If there are any platforms defined, check to see if any of them have been interacted with already
    public static function refresh_map_platforms($this_prototype_data, &$map_data_parsed){
        //error_log('rpg_world::refresh_map_platforms() called!');
        if (empty($map_data_parsed['events'])){ return; } // platforms are just a specific kind of event fwiw
        $game_session_token = rpg_game::session_token();
        $world_session_token = self::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $WORLD_SESSION = &$_SESSION[$world_session_token];
        $this_player_token = $this_prototype_data['this_current_player'];
        $WORLD_PLAYER_SESSION = &$WORLD_SESSION['player_sessions'][$this_player_token];
        $world_token = $map_data_parsed['world'];
        $map_token = $map_data_parsed['token'];
        $world_map_token = $world_token.'__'.$map_token;
        $mmrpg_index_players = self::get_index('players');
        $mmrpg_index_robots = self::get_index('robots');
        // Check to see if this map has any player platforms on it and review each group as a whole
        $map_player_platforms = array();
        $map_player_platforms_index = array();
        $map_has_player_platforms = false;
        if (!empty($map_data_parsed['events'])){
            $event_sprites = $map_data_parsed['events'];
            foreach ($event_sprites AS $event_name => $event_data){
                if (empty($event_data) || !is_array($event_data)){ continue; }
                $raw_event_data = $event_data;
                //error_log('Checking event "'.$event_name.'" for player platform data...');
                //error_log('-> $event_data = '.print_r($event_data, true));
                $hidden = false; if (in_array('hidden', $event_data)){ $hidden = true; unset($event_data[array_search('hidden', $event_data)]); }
                $locked = false; if (in_array('locked', $event_data)){ $locked = true; unset($event_data[array_search('locked', $event_data)]); }
                $active = false; if (in_array('active', $event_data)){ $active = true; unset($event_data[array_search('active', $event_data)]); }
                $data = array_values($event_data);
                $pos = $event_data[0]; unset($event_data[0]);
                $sprite = !empty($event_data[1]) ? $event_data[1] : ''; unset($event_data[1]);
                $filter = !empty($event_data[2]) ? $event_data[2] : ''; unset($event_data[2]);
                $action = !empty($event_data[3]) ? $event_data[3] : ''; unset($event_data[3]);
                if ($action !== 'drop-zone'){ continue; }
                elseif (!preg_match('/^(light|wily|cossack|lalinde)pad-/i', $sprite)){ continue; }
                // This is a PLAYER PLATFORM event, so collect its data for later processing
                $map_has_player_platforms = true;
                $platform_kind = explode('-', $sprite, 2)[0];
                $player_token = 'dr-'.substr($platform_kind, 0, -3);
                $player_unlocked = mmrpg_prototype_player_unlocked($player_token);
                // Add this platform to the index in case we need access to this later
                //error_log('-> '.$event_name.' $raw_event_data = '.print_r($raw_event_data, true));
                if (!isset($map_player_platforms_index[$player_token])){ $map_player_platforms_index[$player_token] = array(); }
                $map_player_platforms_index[$player_token][$event_name] = $raw_event_data;
                //error_log('Map "'.$world_map_token.'" has player platform event "'.$event_name.'" with sprite "'.$sprite.'"' );
                //error_log('-> $platform_kind = '.print_r($platform_kind, true));
                //error_log('-> $player_token = '.print_r($player_token, true));
                //error_log('-> $active = '.print_r($active, true));
                if (!isset($map_player_platforms[$player_token])){ $map_player_platforms[$player_token] = array(); }
                $map_player_platforms[$player_token][$pos] = $active ? 1 : 0;
            }
        }
        // Unlock new player-characters if their platforms exist here and are fully active (all objects placed) not not unlocked yet
        // OR Automatically activate platforms (by placing all objects) for players already unlocked
        //error_log('Map "'.$world_map_token.'" has player platforms? '.($map_has_player_platforms ? 'YES' : 'no'));
        //error_log('-> $map_player_platforms = '.print_r($map_player_platforms, true));
        //error_log('-> $map_player_platforms_index = '.print_r($map_player_platforms_index, true));
        if ($map_has_player_platforms && !empty($map_player_platforms)){
            // Define a quick inline function for looking up drop objects by kind and token when needed
            $find_drop_object = function($kind, $token) use ($map_data_parsed){
                if (empty($kind) || empty($token)){ return false; }
                //error_log('$find_drop_object($kind: '.$kind.', $token: '.$token.')');
                if ($kind === 'item'){
                    static $parsed_items = null;
                    if (!is_array($parsed_items)){ $parsed_items = !empty($map_data_parsed['items']) ? $map_data_parsed['items'] : array(); }
                    //error_log('-> $parsed_items = '.print_r($parsed_items, true));
                    $parsed_objects = $parsed_items;
                    } elseif ($kind === 'ability'){
                    static $parsed_abilities = null;
                    if (!is_array($parsed_abilities)){ $parsed_abilities = !empty($map_data_parsed['abilities']) ? $map_data_parsed['abilities'] : array(); }
                    //error_log('-> $parsed_abilities = '.print_r($parsed_abilities, true));
                    $parsed_objects = $parsed_abilities;
                    } else {
                    return false;
                    }
                foreach ($parsed_objects AS $namekey => $data){
                    if (empty($data) || !is_array($data)){ continue; }
                    $position = !empty($data[0]) ? $data[0] : '';
                    $object_token = !empty($data[1]) ? $data[1] : '';
                    if ($kind === 'item' && strstr($object_token, '__')){ list($object_token) = explode('__', $object_token, 2); }
                    if ($object_token !== $token){ continue; }
                    //error_log('-> found '.$kind.' "'.$token.'" at position "'.$position.'"');
                    //return array($namekey, $data);
                    return $namekey;
                    }
                return false;
                };
            // Loop through each player and their platforms to see if they need unlocking or activating
            foreach ($map_player_platforms AS $player_token => $platforms_active){
                //error_log('-> '.$player_token.' $platforms_active = '.print_r($platforms_active, true));
                $platform_data = $map_player_platforms_index[$player_token];
                //error_log('-> '.$player_token.' $platform_data = '.print_r($platform_data, true));
                $player_unlocked = mmrpg_prototype_player_unlocked($player_token);
                $all_active = array_sum($platforms_active) === count($platforms_active) ? true : false;
                //error_log('-> player "'.$player_token.'" is already unlocked? '.($player_unlocked ? 'YES' : 'NO'));
                //error_log('-> player "'.$player_token.'" has all platforms active? '.($all_active ? 'YES' : 'NO'));
                // If the player is NOT UNLOCKED YET but SHOULD BE given all active platforms, do so now
                if (!$player_unlocked && $all_active){
                    //error_log('-> auto-unlocking player "'.$player_token.'" since all of their platforms are active!');
                    //error_log('-> '.$player_token.' $platforms_active = '.print_r($platforms_active, true));
                    //error_log('-> '.$player_token.' $platform_data = '.print_r($platform_data, true));
                    $player_info = $mmrpg_index_players[$player_token];
                    $player_size = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;
                    $player_xsize = $player_size.'x'.$player_size;
                    $player_zoom_size = $player_size * 2;
                    $player_zoom_xsize = $player_zoom_size.'x'.$player_zoom_size;
                    $player_type = !empty($player_info['player_type']) ? $player_info['player_type'] : 'none';
                    $player_name = !empty($player_info['player_name']) ? $player_info['player_name'] : ucwords(str_replace('-', ' ', $player_token));
                    $player_story = ucfirst(explode('-', $player_token)[1]).' Story';
                    $player_pronoun = rpg_player::get_player_pronoun($player_info['player_gender'], 'possessive2');
                    $player_intro_field = rpg_player::get_intro_field($player_token);
                    $player_starter_robot = rpg_player::get_starter_robot($player_token);
                    $player_unlock_subtext = 'This campaign is undefined and this text should not appear. Do no engage.';
                    $player_heart_token = str_replace('dr-', '', $player_token).'-heart__1'; // player's first limit heart
                    if ($player_token === 'dr-light'){ $player_unlock_subtext = 'This beginner-level campaign teaches you the basics while you fight through an army of powered-up opponents!'; }
                    elseif ($player_token === 'dr-wily'){ $player_unlock_subtext = 'This campaign offers a bit more challenge than the normal one and expects you to already-know the basics of battle!'; }
                    elseif ($player_token === 'dr-cossack'){ $player_unlock_subtext = 'This veteran-level campaign acts as the conclusion to the doctors\' individual stories and packs the hardest punch of all!'; }
                    elseif ($player_token === 'dr-lalinde'){ $player_unlock_subtext = 'This campaign used to look a lot different but system damage has left it nearly unrecognizable - can it be saved?'; }
                    mmrpg_game_unlock_player(array('player_token' => $player_token), true, true);
                    mmrpg_game_unlock_item($player_heart_token, false);
                    $player_robots_unlocked = mmrpg_prototype_robots_unlocked($player_token, true);
                    $first_robot = !empty($player_robots_unlocked[0]) ? $player_robots_unlocked[0] : 'robot';
                    $first_robot_info = !empty($mmrpg_index_robots[$first_robot]) ? $mmrpg_index_robots[$first_robot] : array();
                    $first_robot_size = !empty($first_robot_info['robot_image_size']) ? $first_robot_info['robot_image_size'] : 40;
                    $first_robot_xsize = $first_robot_size.'x'.$first_robot_size;
                    $first_robot_zoom_size = $first_robot_size * 2;
                    $first_robot_zoom_xsize = $first_robot_zoom_size.'x'.$first_robot_zoom_size;
                    $first_robot_name = !empty($first_robot_info['robot_name']) ? $first_robot_info['robot_name'] : ucwords(str_replace('-', ' ', $first_robot));
                    $first_robot_core = !empty($first_robot_info['robot_core']) ? $first_robot_info['robot_core'] : 'none';
                    $temp_event_flag = $player_token.'-event-00_player-unlocked';
                    $temp_game_flags = &$GAME_SESSION['flags'];
                    if (empty($temp_game_flags['events'][$temp_event_flag])){
                        $temp_game_flags['events'][$temp_event_flag] = true;
                        // canvas
                        $temp_canvas_markup = '';
                        $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$player_intro_field.'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; filter: blur(1px) brightness(0.8);"></div>';
                            $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$player_intro_field.'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
                        $temp_canvas_markup .= '<div class="sprite_wrapper breathing_animation" style="bottom: 20px; left: calc(50% + 100px);"><div class="wrap">';
                            $temp_canvas_markup .= '<div class="sprite sprite_player sprite_shadow sprite_'.$player_zoom_xsize.' sprite_'.$player_zoom_xsize.'_victory" style="background-image: url(images/players/'.$player_token.'/sprite_left_'.$player_zoom_xsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 0; left: 0; transform: scale(1.5, 0.5) translate('.(-50 + ($player_zoom_size > 80 ? 5 : 0)).'%, 0) skew(26deg, 0); transform-origin: bottom left; filter: brightness(0); opacity: 0.1;"></div>';
                            $temp_canvas_markup .= '<div class="sprite sprite_player sprite_'.$player_zoom_xsize.' sprite_'.$player_zoom_xsize.'_victory" style="background-image: url(images/players/'.$player_token.'/sprite_left_'.$player_zoom_xsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 0; left: 0; transform: scale(1.5) translate('.(-50 + ($player_zoom_size > 80 ? 5 : 0)).'%, 0); transform-origin: bottom center; image-rendering: pixelated;"></div>';
                        $temp_canvas_markup .= '</div></div>';
                        $temp_canvas_markup .= '<div class="sprite_wrapper breathing_animation" style="bottom: 20px; left: calc(50% - 50px);"><div class="wrap">';
                            $temp_canvas_markup .= '<div class="sprite sprite_robot sprite_shadow sprite_'.$first_robot_zoom_xsize.' sprite_'.$first_robot_zoom_xsize.'_victory" style="background-image: url(images/robots/'.$first_robot.'/sprite_right_'.$first_robot_zoom_xsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 0; left: 0; transform: scale(1.5, 0.5) translate('.(-50 + ($first_robot_zoom_size > 80 ? 5 : 0)).'%, 0) skew(26deg, 0); transform-origin: bottom right; filter: brightness(0); opacity: 0.1;"></div>';
                            $temp_canvas_markup .= '<div class="sprite sprite_robot sprite_'.$first_robot_zoom_xsize.' sprite_'.$first_robot_zoom_xsize.'_victory" style="background-image: url(images/robots/'.$first_robot.'/sprite_right_'.$first_robot_zoom_xsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 0; left: 0; transform: scale(1.5) translate('.(-50 + ($first_robot_zoom_size > 80 ? 5 : 0)).'%, 0); transform-origin: bottom center; image-rendering: pixelated;"></div>';
                        $temp_canvas_markup .= '</div></div>';
                        // console
                        $temp_console_markup = '';
                        $temp_console_markup .= '<p class="ability_type ability_type_defense" style="margin: 5px auto 10px; text-align: center;">Congratulations!</p>';
                        $temp_console_markup .= '<p style="margin: 5px auto 10px; text-align: center;">'.rpg_type::print_span($player_type, $player_name).' has been unlocked as a playable character in Free Roam and <br /> the '.rpg_type::print_span($player_type, $player_story).' campaign has been unlocked on the Main Menu!</p>';
                        $temp_console_markup .= '<p style="margin: 5px auto 10px; text-align: center;">Play through the game as <strong>'.$player_name.'</strong> and <strong>'.$first_robot_name.'</strong> to experience events from their perspective, unlocking new robots, items, and abilities using their unique skills! '.$player_unlock_subtext.'</p>';
                        $temp_console_markup .= '<p style="margin: 5px auto 10px; text-align: center; font-size: 90%; line-height: 1.6; color: #d6d6d6;">Select <strong class="player_type type '.$player_type.'">'.$player_name.'</strong> from the player select menu to play through '.$player_pronoun.' story missions at any time.</p>';
                        if (!isset($GAME_SESSION['EVENTS'])){ $GAME_SESSION['EVENTS'] = array(); }
                        array_push($GAME_SESSION['EVENTS'], array(
                            'canvas_markup' => $temp_canvas_markup,
                            'console_markup' => $temp_console_markup,
                            'player_token' => $player_token,
                            'event_type' => 'new-player'
                            ));
                        $clear_seen_frame_token = 'edit_players';
                        rpg_prototype::mark_menu_frame_as_unseen($clear_seen_frame_token);
                    }
                    $middle_platform_position = count($platforms_active) > 2 ? array_keys($platforms_active)[floor(count($platforms_active) / 2)] : '';
                    $below_middle_platform_position = !empty($middle_platform_position) ? (explode('-', $middle_platform_position)[0]).'-'.((explode('-', $middle_platform_position)[1] + 1)) : false;
                    //error_log('-> $middle_platform_position = '.print_r($middle_platform_position, true));
                    //error_log('-> $below_middle_platform_position = '.print_r($below_middle_platform_position, true));
                    $redirect_to_player = $player_token;
                    $redirect_to_world = $WORLD_PLAYER_SESSION['last_world'];
                    $redirect_to_map = $WORLD_PLAYER_SESSION['last_map'];
                    $redirect_to_position = !empty($below_middle_platform_position) ? $below_middle_platform_position : $WORLD_PLAYER_SESSION['last_position'];
                    $redirect_to_url = 'world.php?'.implode('&', array(
                        'player='.$redirect_to_player,
                        'world='.$redirect_to_world,
                        'map='.$redirect_to_map,
                        'position='.$redirect_to_position,
                        'return=prototype'
                        ));
                    //error_log('-> redirecting to new world URL: '.$redirect_to_url);
                    header('Location: '.$redirect_to_url);
                    exit();
                }
                // Else if the player IS UNLOCKED but platforms are NOT ACTIVE YET, auto-move objects into place
                elseif ($player_unlocked && !$all_active){
                    //error_log('-> auto-moving init-items for "'.$player_token.'" as they\'re already unlocked!');
                    //error_log('-> '.$player_token.' $platforms_active = '.print_r($platforms_active, true));
                    //error_log('-> '.$player_token.' $platform_data = '.print_r($platform_data, true));
                    // We need to move the items into place and save it as if the player already did-so themselves
                    $world_objects_required = array_map(function($data) use ($find_drop_object){
                        if (empty($data[0]) || !strstr($data[0], '-')){ return false; }
                        if (empty($data[4]) || !strstr($data[4], ':')){ return false; }
                        $position = $data[0]; $filter = trim($data[4]);
                        list($filter_kind, $filter_token) = explode(':', $filter);
                        $object_namekey = $find_drop_object($filter_kind, $filter_token);
                        return array($filter_kind, $object_namekey, $position);
                        }, $platform_data);
                    //error_log('-> $world_objects_required = '.print_r($world_objects_required, true));
                    if (!empty($world_objects_required)){
                        foreach ($world_objects_required AS $event_namekey => $object_data){
                            //error_log('-> processing object #'.$event_namekey.' = '.print_r($object_data, true));
                            if (empty($object_data) || !is_array($object_data) || count($object_data) < 3){ continue; }
                            list($object_kind, $object_namekey, $object_position) = $object_data;
                            if (empty($object_namekey)){ continue; }
                            if ($object_kind === 'item'){ $object_xkind = 'items'; }
                            elseif ($object_kind === 'ability'){ $object_xkind = 'abilities'; }
                            else { $object_xkind = false; }
                            if (!$object_xkind){ /*error_log('-> unrecognized object kind "'.$object_kind.'", skipping it');*/ continue; }
                            if (!isset($WORLD_SESSION['world_symbols'][$world_map_token][$object_xkind])){ $WORLD_SESSION['world_symbols'][$world_map_token][$object_xkind] = array(); }
                            $world_object_symbols = &$WORLD_SESSION['world_symbols'][$world_map_token][$object_xkind];
                            $world_object_symbols[$object_namekey] = $object_position;
                            $map_data_parsed['events'][$event_namekey][] = 'active';
                            //error_log('-> auto-placed '.$object_kind.' "'.$object_namekey.'" into position "'.$object_position.'"' );
                        }
                    }
                    //error_log('-> theoretically, all objects should be placed and this should not require a reload...');
                }
            }
        }
        // Return true on success
        return true;
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
        static $mmrpg_index_abilities; if (empty($mmrpg_index_abilities)){ $mmrpg_index_abilities = self::get_index('abilities'); }
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
        $robot_experience = !empty($robot_rewards['robot_experience']) ? $robot_rewards['robot_experience'] : 0;
        $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : '';
        $robot_core_types = 'type '.(!empty($robot_info['robot_core']) ? ($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' '.$robot_info['robot_core2'] : '')) : 'none');
        $robot_core_or_none = !empty($robot_core) ? $robot_core : 'none';
        $robot_item = !empty($robot_settings['robot_item']) ? $robot_settings['robot_item'] : '';
        $robot_support = !empty($robot_settings['robot_support']) ? $robot_settings['robot_support'] : '';
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
        //error_log($robot_token.' | $robot_session = '.print_r($robot_session, true));
        $robot_disabled = false;
        $robot_image = $robot_token;
        if (!empty($robot_settings['robot_persona_image'])){ $robot_image = $robot_settings['robot_persona_image']; }
        elseif (!empty($robot_settings['robot_image'])){ $robot_image = $robot_settings['robot_image']; }
        $robot_info['robot_image'] = $robot_image;
        $robot_energy = $robot_energy_max = $robot_energy_percent = 0; $robot_energy_rating = 'no';
        if (!empty($robot_info['robot_energy'])){
            $robot_energy = $robot_stats['energy']['current'];
            $robot_energy_max = $robot_stats['energy']['current'];
            if (!empty($robot_session['energy']) && is_numeric($robot_session['energy'])){ $robot_energy += $robot_session['energy']; }
            $robot_energy_percent = round(($robot_energy / $robot_energy_max) * 100);
            if ($robot_energy_percent === 100 && $robot_energy < $robot_energy_max){ $robot_energy_percent -= 1; }
            $robot_energy_rating = $get_rating_token($robot_energy_percent);
            if (empty($robot_energy)){ $robot_disabled = true; }
            }
        $robot_weapons = $robot_weapons_max = $robot_weapons_percent = 0; $robot_weapons_rating = 'no';
        if (!empty($robot_info['robot_weapons'])){
            $robot_weapons = $robot_stats['weapons']['current'];
            $robot_weapons_max = $robot_stats['weapons']['current'];
            if (!empty($robot_session['weapons']) && is_numeric($robot_session['weapons'])){ $robot_weapons += $robot_session['weapons']; }
            $robot_weapons_percent = ceil(($robot_weapons / $robot_weapons_max) * 100);
            $robot_weapons_rating = $get_rating_token($robot_weapons_percent);
            }
        $temp_ability_token_to_id = function($token) use($mmrpg_index_abilities) {
            if (!isset($mmrpg_index_abilities[$token])){ return 0; }
            return intval($mmrpg_index_abilities[$token]['ability_id']);
            };
        $robot_abilities = !empty($robot_settings['robot_abilities']) ? array_map(function($a){ return is_array($a) ? array_values($a)[0] : $a; }, array_values($robot_settings['robot_abilities'])) : array();
        $robot_abilities_compatible = rpg_robot::get_ability_compatibility($robot_token, ''); // omitting $robot_item so we get a base-list instead
        $robot_abilities_compatible_via_item = rpg_robot::get_ability_compatibility($robot_token, $robot_item); // now we grab a copy w/ item included to get a diff
        $robot_abilities_via_item = array_values(array_diff($robot_abilities_compatible_via_item, $robot_abilities_compatible));
        //error_log('['.$robot_token.'] -> $robot_abilities(before) = '.print_r($robot_abilities, true));
        //error_log('['.$robot_token.'] -> $robot_abilities_compatible(before) = '.print_r($robot_abilities_compatible, true));
        //error_log('['.$robot_token.'] -> $robot_abilities_compatible_via_item(before) = '.print_r($robot_abilities_compatible_via_item, true));
        //error_log('['.$robot_token.'] -> $robot_abilities_via_item(before) = '.print_r($robot_abilities_via_item, true));
        $robot_abilities = array_map($temp_ability_token_to_id, $robot_abilities);
        $robot_abilities_compatible = array_map($temp_ability_token_to_id, $robot_abilities_compatible);
        $robot_abilities_via_item = array_map($temp_ability_token_to_id, $robot_abilities_via_item);
        //$robot_abilities_compatible_base = array_map($temp_ability_token_to_id, $robot_abilities_compatible_base);
        //error_log('-> $robot_abilities(after) = '.print_r($robot_abilities, true));
        //error_log('['.$robot_token.'] -> $robot_abilities_compatible(after) = '.print_r($robot_abilities_compatible, true));
        //error_log('['.$robot_token.'] -> $robot_abilities_via_item(after) = '.print_r($robot_abilities_via_item, true));
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
            'experience' => $robot_experience,
            'item' => $robot_item,
            'support' => $robot_support,
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
            'abilities' => $robot_abilities,
            'abilitiesCompatible' => $robot_abilities_compatible,
            'abilitiesViaItem' => $robot_abilities_via_item,
            'disabled' => $robot_disabled,
            );
        if (!$has_persona_applied){ unset($robot_overview['persona']); }
        return $robot_overview;
    }

    // Define a method for quickly parsing a string representing a position range ("1-2...5-2") into an array and returning it
    public static function parse_position_range($range_string){
        //error_log('rpg_world::parse_position_range() called for range string "'.$range_string.'"');
        $range_positions = array();
        if (preg_match('/^([0-9]{1,}-[0-9]{1,})\.\.\.([0-9]{1,}-[0-9]{1,})$/', $range_string, $matches)){
            //error_log('---> parsing range syntax for tile string "'.$range_string.'"');
            //error_log('---> $matches = '. print_r($matches, true));
            $range_start = intval($matches[1]); // will be in format x-y
            list($range_start_x, $range_start_y) = explode('-', $matches[1]);
            $range_end = intval($matches[2]); // will also be in format x-y
            list($range_end_x, $range_end_y) = explode('-', $matches[2]);
            for ($x = $range_start_x; $x <= $range_end_x; $x++){
                for ($y = $range_start_y; $y <= $range_end_y; $y++){
                    $range_positions[] = $x.'-'.$y; // add the tile to the range
                }
            }
        } elseif (preg_match('/^([0-9]{1,}-[0-9]{1,})$/', $range_string, $matches)){
            //error_log('---> parsing single position syntax for tile string "'.$range_string.'"');
            //error_log('---> $matches = '. print_r($matches, true));
            $range_positions[] = $matches[1];
        }
        //error_log('---> $range_positions = '. print_r($range_positions, true));
        return $range_positions;
    }

    // Define a method for grabbing a battle star's data from the session given a token
    public static function get_star_info($star_token){
        //error_log('rpg_world::get_star_info() called for star token "'.$star_token.'"');
        if (empty($star_token) || !is_string($star_token)){ return false; }
        $star_token_clean = str_replace('world-star_', '', $star_token);
        list($world_token, $map_token, $real_star_token) = explode('_', $star_token_clean);
        $star_world_map = $world_token.'__'.$map_token;
        //error_log('$star_token_clean = '.print_r($star_token_clean, true));
        //error_log('$world_token = '.print_r($world_token, true));
        //error_log('$map_token = '.print_r($map_token, true));
        //error_log('$star_world_map = '.print_r($star_world_map, true));
        //error_log('$real_star_token = '.print_r($real_star_token, true));
        $world_data_parsed = array();
        $map_data_parsed = array();
        rpg_world::parse_map_data($star_world_map, $world_data_parsed, $map_data_parsed);
        //error_log('$world_data_parsed = '.print_r($world_data_parsed, true));
        //error_log('$map_data_parsed = '.print_r($map_data_parsed, true));
        //error_log('$world_data_parsed = '.(!empty($world_data_parsed) ? gettype($world_data_parsed) : '-'));
        //error_log('$map_data_parsed = '.(!empty($map_data_parsed) ? gettype($map_data_parsed) : '-'));
        $this_star_data = array();
        if (!empty($map_data_parsed['items'])
            && !empty($map_data_parsed['items'][$real_star_token])){
            $raw_star_data = $map_data_parsed['items'][$real_star_token];
            $this_star_data['position'] = $raw_star_data[0];
            $this_star_data['kind'] = $raw_star_data[1];
            $this_star_data['type'] = str_replace('-star', '', $raw_star_data[1]);
            $this_star_data['owner'] = $raw_star_data[2];
        }
        return $this_star_data;
    }

    // Define a method for generating a star force index with all star tokens and info together
    public static function get_stars_index(){
        //error_log('rpg_world::get_stars_index() called!');
        $mmrpg_index_robots = rpg_robot::get_index(true);
        $mmrpg_index_fields = rpg_field::get_index(true);
        $mmrpg_index_stars = array();
        if (!empty($mmrpg_index_robots)){
            $mmrpg_robots_tokens = array_keys($mmrpg_index_robots);
            foreach ($mmrpg_robots_tokens AS $robot_key => $robot_token){
                $robot_info = $mmrpg_index_robots[$robot_token];
                if (empty($robot_info['robot_core'])){ continue; }
                if ($robot_info['robot_core'] === 'copy'){ continue; }
                if ($robot_info['robot_class'] !== 'master'){ continue; }
                if (!empty($robot_info['robot_flag_hidden'])){ continue; }
                $new_star_data = array();
                $new_star_data['star_token'] = $robot_token;
                $new_star_data['star_name'] = $robot_info['robot_name'];
                $new_star_data['star_kind'] = 'boss';
                $new_star_data['star_type'] = $robot_info['robot_core'];
                $new_star_data['star_type2'] = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : '';
                $new_star_data['star_robot'] = $robot_token;
                $new_star_data['star_robot2'] = '';
                $new_star_data['star_order'] = count($mmrpg_index_stars) + 1;
                $mmrpg_index_stars[$new_star_data['star_token']] = array_filter($new_star_data);
            }
        }
        if (!empty($mmrpg_index_fields)){
            $mmrpg_fields_tokens = array_keys($mmrpg_index_fields);
            $allowed_field_games = array('MM1', 'MM2', 'MM3', 'MM4'); // TEMP!
            foreach ($mmrpg_fields_tokens AS $field1_key => $field1_token){
                $field1_info = $mmrpg_index_fields[$field1_token];
                if (empty($field1_info['field_type'])){ continue; }
                if ($field1_info['field_type'] === 'copy'){ continue; }
                if ($field1_info['field_class'] !== 'master'){ continue; }
                if (!empty($field1_info['field_flag_hidden'])){ continue; }
                //if (!in_array($field1_info['field_game'], $allowed_field_games)){ continue; }
                $new_star_data = array();
                $new_star_data['star_token'] = $field1_token;
                $new_star_data['star_name'] = $field1_info['field_name'];
                $new_star_data['star_kind'] = 'field';
                $new_star_data['star_type'] = $field1_info['field_type'];
                $new_star_data['star_type2'] = !empty($field1_info['field_type2']) ? $field1_info['field_type2'] : '';
                $new_star_data['star_field'] = $field1_token;
                $new_star_data['star_field2'] = '';
                $new_star_data['star_order'] = count($mmrpg_index_stars) + 1;
                $mmrpg_index_stars[$new_star_data['star_token']] = array_filter($new_star_data);
                foreach ($mmrpg_fields_tokens AS $field2_key => $field2_token){
                    $field2_info = $mmrpg_index_fields[$field2_token];
                    if (empty($field2_info['field_type'])){ continue; }
                    if ($field2_info['field_type'] === 'copy'){ continue; }
                    if ($field2_info['field_class'] !== 'master'){ continue; }
                    if (!empty($field2_info['field_flag_hidden'])){ continue; }
                    //if (!in_array($field2_info['field_game'], $allowed_field_games)){ continue; }
                    $fusion_token = explode('-', $field1_token)[0].'-'.explode('-', $field2_token)[1];
                    $fusion_name = explode(' ', $field1_info['field_name'])[0].'-'.explode(' ', $field2_info['field_name'])[1];
                    $new_star_data = array();
                    $new_star_data['star_token'] = $fusion_token;
                    $new_star_data['star_name'] = $fusion_name;
                    $new_star_data['star_kind'] = 'fusion';
                    $new_star_data['star_type'] = $field1_info['field_type'];
                    $new_star_data['star_type2'] = $field2_info['field_type'];
                    $new_star_data['star_field'] = $field1_token;
                    $new_star_data['star_field2'] = $field2_token;
                    $new_star_data['star_order'] = count($mmrpg_index_stars) + 1;
                    $mmrpg_index_stars[$new_star_data['star_token']] = array_filter($new_star_data);
                }
            }
        }
        return $mmrpg_index_stars;
    }


}
?>
