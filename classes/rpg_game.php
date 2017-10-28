<?
/**
 * Mega Man RPG Game
 * <p>The global game for the Mega Man RPG Prototype.</p>
 */
class rpg_game {

    // Define global class variables
    public static $index = array();


    /**
     * Create a new RPG game object.
     * This is a wrapper class for static functions,
     * so object initialization is not necessary.
     */
    public function rpg_game(){ }



    // -- BATTLE OBJECT FUNCTIONS -- //

    /**
     * Create or retrive a battle object from the session
     * @param array $this_battleinfo
     * @return rpg_battle
     */
    public static function get_battle($this_battleinfo){

        // If the battle index has not been created, do so
        if (!isset(self::$index['battles'])){ self::$index['battles'] = array(); }

        // Check if a battle ID has been defined
        if (isset($this_battleinfo['battle_id'])){
            $battle_id = $this_battleinfo['battle_id'];
        }

        // If this battle has already been created, retrieve it
        if (!empty($battle_id) && !empty(self::$index['battles'][$battle_id])){

            // Collect the battle from the index and return
            $this_battle = self::$index['battles'][$battle_id];
            $this_battle->trigger_onload();

        }
        // Otherwise create a new battle object in the index
        else {

            // Create and return the battle object
            $this_battle = new rpg_battle($this_battleinfo);
            self::$index['battles'][$this_battle->battle_id] = $this_battle;

        }

        // Return the collect battle object
        $this_battle->update_session();
        return $this_battle;

    }

    /**
     * Create or retrive a player object from the session
     * @param array $this_playerinfo
     * @return rpg_player
     */
    public static function get_player($this_battle, $this_playerinfo){

        // If the player index has not been created, do so
        if (!isset(self::$index['players'])){ self::$index['players'] = array(); }

        // Check if a player ID has been defined
        if (isset($this_playerinfo['player_id'])){
            $player_id = $this_playerinfo['player_id'];
        }
        // Otherwise if only a player token was defined
        elseif (isset($this_playerinfo['player_token'])){
            $player_id = 0;
            $player_token = $this_playerinfo['player_token'];
            foreach (self::$index['players'] AS $player){
                if ($player_token == $player->player_token){
                    $player_id = $player->player_id;
                    break;
                }
            }
        }

        // If this player has already been created, retrieve it
        if (!empty($player_id) && !empty(self::$index['players'][$player_id])){

            // Collect the player from the index and return
            $this_player = self::$index['players'][$player_id];
            $this_player->trigger_onload();

        }
        // Otherwise create a new player object in the index
        else {

            // Create and return the player object
            $this_player = new rpg_player($this_battle, $this_playerinfo);
            self::$index['players'][$this_player->player_id] = $this_player;

        }

        // Return the collect player object
        $this_player->update_session();
        return $this_player;

    }

    /**
     * Create or retrive a robot object from the session
     * @param array $this_robotinfo
     * @return rpg_robot
     */
    public static function get_robot($this_battle, $this_player, $this_robotinfo){

        // If the robot index has not been created, do so
        if (!isset(self::$index['robots'])){ self::$index['robots'] = array(); }

        // Check if a robot ID has been defined
        if (isset($this_robotinfo['robot_id'])){
            $robot_id = $this_robotinfo['robot_id'];
        }
        // Otherwise if only a robot token was defined
        elseif (isset($this_robotinfo['robot_token'])){
            $robot_id = 0;
            $robot_token = $this_robotinfo['robot_token'];
            foreach (self::$index['robots'] AS $robot){
                if ($robot_token == $robot->robot_token
                    && $this_player->player_id == $robot->player_id){
                    $robot_id = $robot->robot_id;
                    break;
                }
            }
        }

        // If this robot has already been created, retrieve it
        if (!empty($robot_id) && !empty(self::$index['robots'][$robot_id])){

            // Collect the robot from the index and return
            $this_robot = self::$index['robots'][$robot_id];
            $this_robot->trigger_onload();

        }
        // Otherwise create a new robot object in the index
        else {

            // Create and return the robot object
            $this_robot = new rpg_robot($this_battle, $this_player, $this_robotinfo);
            self::$index['robots'][$this_robot->robot_id] = $this_robot;

        }

        // Return the collect robot object
        $this_robot->update_session();
        return $this_robot;

    }

    /**
     * Create or retrive a ability object from the session
     * @param array $this_abilityinfo
     * @return rpg_ability
     */
    public static function get_ability($this_battle, $this_player, $this_robot, $this_abilityinfo){

        // If the ability index has not been created, do so
        if (!isset(self::$index['abilities'])){ self::$index['abilities'] = array(); }

        // Check if a ability ID has been defined
        if (isset($this_abilityinfo['ability_id'])){
            $ability_id = $this_abilityinfo['ability_id'];
        }
        // Otherwise if only a ability token was defined
        elseif (isset($this_abilityinfo['ability_token'])){
            $ability_id = 0;
            $ability_token = $this_abilityinfo['ability_token'];
            foreach (self::$index['abilities'] AS $ability){
                if ($ability_token == $ability->ability_token
                    && $this_robot->robot_id == $ability->robot_id
                    && $this_player->player_id == $ability->player_id){
                    $ability_id = $ability->ability_id;
                    break;
                }
            }
        }

        // If this ability has already been created, retrieve it
        if (!empty($ability_id) && !empty(self::$index['abilities'][$ability_id])){

            // Collect the ability from the index and return
            $this_ability = self::$index['abilities'][$ability_id];
            $this_ability->trigger_onload();

        }
        // Otherwise create a new ability object in the index
        else {

            // Create and return the ability object
            $this_ability = new rpg_ability($this_battle, $this_player, $this_robot, $this_abilityinfo);
            self::$index['abilities'][$this_ability->ability_id] = $this_ability;

        }

        // Return the collect ability object
        $this_ability->update_session();
        return $this_ability;

    }

    /**
     * Create or retrive a item object from the session
     * @param array $this_iteminfo
     * @return rpg_item
     */
    public static function get_item($this_battle, $this_player, $this_robot, $this_iteminfo){

        // If the item index has not been created, do so
        if (!isset(self::$index['items'])){ self::$index['items'] = array(); }

        // Check if a item ID has been defined
        if (isset($this_iteminfo['item_id'])){
            $item_id = $this_iteminfo['item_id'];
        }
        // Otherwise if only a item token was defined
        elseif (isset($this_iteminfo['item_token'])){
            $item_id = 0;
            $item_token = $this_iteminfo['item_token'];
            foreach (self::$index['items'] AS $item){
                if ($item_token == $item->item_token
                    && $this_robot->robot_id == $item->robot_id
                    && $this_player->player_id == $item->player_id){
                    $item_id = $item->item_id;
                    break;
                }
            }
        }

        // If this item has already been created, retrieve it
        if (!empty($item_id) && !empty(self::$index['items'][$item_id])){

            // Collect the item from the index and return
            $this_item = self::$index['items'][$item_id];
            $this_item->trigger_onload();

        }
        // Otherwise create a new item object in the index
        else {

            // Create and return the item object
            $this_item = new rpg_item($this_battle, $this_player, $this_robot, $this_iteminfo);
            self::$index['items'][$this_item->item_id] = $this_item;

        }

        // Return the collect item object
        $this_item->update_session();
        return $this_item;

    }

    /**
     * Request a reference to existing player data in the battle object via filters
     * @param array $filters
     * @return rpg_player
     */
    public static function find_player($filters, $invert = false){
        if (empty(self::$index['players']) || empty($filters)){ return false; }
        foreach (self::$index['players'] AS $player_id => $this_player){
            $is_match = true;
            foreach ($filters AS $field_name => $field_value){
                if ($invert == false && $this_player->$field_name != $field_value){
                    $is_match = false;
                    break;
                } elseif ($invert == true && $this_player->$field_name == $field_value){
                    $is_match = false;
                    break;
                }
            }
            if ($is_match){
                return $this_player;
            }
        }
        return false;
    }

    /**
     * Request a reference to existing robot data in the battle object via filters
     * @param array $filters
     * @return rpg_robot
     */
    public static function find_robot($filters){
        if (empty(self::$index['robots']) || empty($filters)){ return false; }
        foreach (self::$index['robots'] AS $robot_id => $this_robot){
            $is_match = true;
            foreach ($filters AS $field_name => $field_value){
                if ($this_robot->$field_name != $field_value){
                    $is_match = false;
                    break;
                }
            }
            if ($is_match){
                return $this_robot;
            }
        }
        return false;
    }

    /**
     * Request a list of references to existing robot data in the battle object via filters
     * @param array $filters
     * @return rpg_robot
     */
    public static function find_robots($filters){
        if (empty(self::$index['robots']) || empty($filters)){ return false; }
        $robots = array();
        foreach (self::$index['robots'] AS $robot_id => $this_robot){
            $is_match = true;
            foreach ($filters AS $field_name => $field_value){
                if ($this_robot->$field_name != $field_value){
                    $is_match = false;
                    break;
                }
            }
            if ($is_match){
                $robots[] = $this_robot;
            }
        }
        return $robots;
    }



    // -- MODE FUNCTIONS -- //

    // Define a function for checking if we're in demo mode
    public static function is_demo(){
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['DEMO'])){ return true; } // Demo flag exists, so true
        elseif (!empty($_SESSION[$session_token]['USER']['userid']) && $_SESSION[$session_token]['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID){ return true; } // User ID is guest, so true
        else { return false; }  // Demo flag doesn't exist, must be logged in
    }


    // Define a function for checking if we're in user mode
    public static function is_user(){
        // If we're not in demo mode, we must be user mode
        $session_token = self::session_token();
        return !empty($_SESSION[$session_token]['USER']['userid']) && $_SESSION[$session_token]['USER']['userid'] != MMRPG_SETTINGS_GUEST_ID ? true : false;
    }



    // -- PLAYER FUNCTIONS -- //


    // Define a function for checking is a prototype player has been unlocked
    public static function player_unlocked($player_token){
        // Check if this battle has been completed and return true is it was
        $session_token = self::session_token();
        return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]) ? true : false;
    }


    // Define a function for checking is a prototype player has been unlocked
    public static function players_unlocked(){
        // Check if this battle has been completed and return true is it was
        $session_token = self::session_token();
        return isset($_SESSION[$session_token]['values']['battle_rewards']) ? count($_SESSION[$session_token]['values']['battle_rewards']) : 0;
    }


    // Define a function for unlocking a game player for use in battle
    public static function unlock_player($player_info, $unlock_robots = true, $unlock_abilities = true, $unlock_fields = true){

        // Reference the global variables
        global $mmrpg_index, $db;

        //$GAME_SESSION = &$_SESSION[self::session_token()];
        $session_token = self::session_token();

        // Define a reference to the game's session flag variable
        if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
        $temp_game_flags = &$_SESSION[$session_token]['flags'];

        // If the player token does not exist, return false
        if (!isset($player_info['player_token'])){ return false; }

        // Attempt to collect index info for this player, else return false
        $player_index_info = rpg_player::get_index_info($player_info['player_token']);
        if (empty($player_index_info)){ return false; }

        // Collect the player info from the index
        $player_info = array_replace($player_index_info, $player_info);

        // Collect or define the player points and player rewards variables
        $this_player_token = $player_info['player_token'];
        $this_player_points = !empty($player_info['player_points']) ? $player_info['player_points'] : 0;
        $this_player_rewards = !empty($player_info['player_rewards']) ? $player_info['player_rewards'] : array();

        // Automatically unlock this player for use in battle then create the settings array
        $this_reward = array('player_token' => $this_player_token, 'player_points' => $this_player_points);
        $_SESSION[$session_token]['values']['battle_rewards'][$this_player_token] = $this_reward;
        if (empty($_SESSION[$session_token]['values']['battle_settings'][$this_player_token])
            || count($_SESSION[$session_token]['values']['battle_settings'][$this_player_token]) < 8){
            $this_setting = array('player_token' => $this_player_token, 'player_robots' => array());
            $_SESSION[$session_token]['values']['battle_settings'][$this_player_token] = $this_setting;
        }

        // Loop through the robot rewards for this player if set
        if ($unlock_robots && !empty($this_player_rewards['robots'])){
            $temp_tokens = array_column($this_player_rewards['robots'], 'token');
            $temp_robots_index = rpg_robot::get_index_custom($temp_tokens);
            foreach ($this_player_rewards['robots'] AS $robot_reward_key => $robot_reward_info){
                // Check if the required amount of points have been met by this player
                if ($this_player_points >= $robot_reward_info['points']){
                    // Unlock this robot and all abilities
                    $this_robot_info = rpg_robot::parse_index_info($temp_robots_index[$robot_reward_info['token']]);
                    $this_robot_info['robot_level'] = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
                    $this_robot_info['robot_experience'] = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
                    self::unlock_robot($player_info, $this_robot_info, true, false);
                }
            }
        }

        // Loop through the ability rewards for this player if set
        if ($unlock_abilities && !empty($this_player_rewards['abilities'])){
            $temp_tokens = array_column($this_player_rewards['abilities'], 'token');
            $this_ability_index = rpg_ability::get_index_custom($temp_tokens);
            foreach ($this_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){
                // Check if the required amount of points have been met by this player
                if ($this_player_points >= $ability_reward_info['points']){
                    // Unlock this ability
                    $this_ability_info = rpg_ability::parse_index_info($this_ability_index[$ability_reward_info['token']]);
                    $show_event = !self::ability_unlocked('', '', $ability_reward_info['token']) ? true : false;
                    self::unlock_ability($player_info, false, $this_ability_info);
                }
            }
        }

        // Loop through appropriate fields for this player in the database
        if ($unlock_fields){

            // Create the battle fields array if not set
            if (!isset($_SESSION[$session_token]['values']['battle_fields'])){
                $_SESSION[$session_token]['values']['battle_fields'] = array();
            }

            // Define the appropriate game code for the given doctor and collect fields
            $game_code = $player_index_info['player_game'];
            if (!empty($game_code)){
                $field_tokens = $db->get_array_list("SELECT field_token FROM mmrpg_index_fields WHERE field_game = '{$game_code}' AND field_flag_complete = 1 ORDER BY field_order ASC;", 'field_token');
                $field_tokens = !empty($field_tokens) ? array_keys($field_tokens) : array();
                if (!empty($field_tokens)){
                    $battle_fields = $_SESSION[$session_token]['values']['battle_fields'];
                    $battle_fields = array_merge($battle_fields, $field_tokens);
                    $battle_fields = array_unique($battle_fields);
                    if (!empty($battle_fields)){
                        $_SESSION[$session_token]['values']['battle_fields'] = $battle_fields;
                    }
                }
            }

        }

        // Create the event flag for unlocking this robot
        $temp_game_flags['events']['unlocked-player_'.$this_player_token] = true;

        // Return true on success
        return true;

    }


    // Define a function for updating a player setting for use in battle
    public static function player_setting($player_info, $setting_token, $setting_value){
        // Reference the global variables
        //global $mmrpg_index;
        // Update or create the player setting in the session
        $player_token = $player_info['player_token'];
        $_SESSION[self::session_token()]['values']['battle_settings'][$player_token][$setting_token] = $setting_value;
        // Return true on success
        return true;
    }

    // Define a function for checking a player's prototype points total
    public static function player_points($player_token){
        // Return the current point total for this player
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_points'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_points']; }
        else { return 0; }
    }

    // Define a function for checking a player's prototype rewards array
    public static function player_rewards($player_token){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]; }
        else { return array(); }
    }

    // Define a function for checking a player's prototype settings array
    public static function player_settings($player_token){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token])){ return $_SESSION[$session_token]['values']['battle_settings'][$player_token]; }
        else { return array(); }
    }



    // -- ROBOT FUNCTIONS -- //


    // Define a function for checking is a prototype robot has been unlocked
    public static function robot_unlocked($player_token = '', $robot_token = ''){
        // Define the game session helper var
        $session_token = self::session_token();
        // If the player token was not false, check to see if that particular player has unlocked
        if (empty($robot_token)){ return false; }
        if (!empty($player_token)){
            // Check if this battle has been completed and return true is it was
            if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])
                && !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
                return true;
            } else {
                return false;
            }
        }
        // Otherwise, loop through all robots and make sure no player has unlocked this robot
        else {
            // Loop through all the player tokens in the battle rewards
            $robot_unlocked = false;
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
                if (isset($player_info['player_robots'][$robot_token])
                    && !empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])
                    && !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
                    $robot_unlocked = true;
                    break;
                }
            }
            return $robot_unlocked;
        }
    }


    // Define a function for checking robots have been unlocked
    public static function robots_unlocked($player_token = '', $strict = false){
        // Define the game session helper var
        $session_token = self::session_token();

        // If we're filtering by player and strict mode is on, only count original player status
        if (!empty($player_token) && $strict){
            $robot_counter = 0;
            $battle_rewards = $_SESSION[$session_token]['values']['battle_rewards'];
            $battle_settings = $_SESSION[$session_token]['values']['battle_settings'];
            $ptokens = array_keys($battle_rewards);
            $ptokens = array_merge($ptokens, array_keys($battle_settings));
            $ptokens = array_unique($ptokens);
            foreach ($ptokens AS $pkey => $ptoken){
                $prewards = !empty($battle_rewards[$ptoken]) ? $battle_rewards[$ptoken] : array();
                $psettings = !empty($battle_settings[$ptoken]) ? $battle_settings[$ptoken] : array();
                $rtokens = array();
                if (!empty($prewards['player_robots'])){ $rtokens = array_merge($rtokens, array_keys($prewards['player_robots'])); }
                if (!empty($psettings['player_robots'])){ $rtokens = array_merge($rtokens, array_keys($psettings['player_robots'])); }
                $rtokens = array_unique($rtokens);
                if (!empty($rtokens)){
                    foreach ($rtokens AS $rkey => $rtoken){
                        $rsettings = !empty($psettings['player_robots'][$rtoken]) ? $psettings['player_robots'][$rtoken] : array();
                        if (!empty($rsettings['original_player']) && $rsettings['original_player'] == $player_token){
                            $robot_counter++;
                        }
                    }
                }
            }
            return $robot_counter;
        }
        // Otherwise if filtering player and strict mode is off, robots count toward whichever player they're with
        elseif (!empty($player_token) && !$strict){
            $robot_counter = 0;
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $ptoken => $pinfo){
                if ($ptoken != $player_token){ continue; }
                $robot_counter += isset($pinfo['player_robots']) ? count($pinfo['player_robots']) : 0;
            }
            return $robot_counter;
        }
        // Otherwise we can just return the total number of robots all players have collected
        else {
            $robot_counter = 0;
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $ptoken => $pinfo){
                $robot_counter += isset($pinfo['player_robots']) ? count($pinfo['player_robots']) : 0;
            }
            return $robot_counter;
        }

    }


    // Define a function for collecting all robots unlocked by player or all
    public static function robot_tokens_unlocked($player_token = ''){
        // Define the game session helper var
        $session_token = self::session_token();
        // Define the temp robot and return arrays
        $unlocked_robots_tokens = array();
        // If the player token was not false, attempt to collect rewards and settings arrays for that player
        if (!empty($player_token)){
            // Loop through and collect the robot settings and rewards for this player
            $battle_values = array('battle_rewards', 'battle_settings');
            foreach ($battle_values AS $value_token){
                if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'])){
                    foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'] AS $robot_token => $robot_info){
                        if (!empty($robot_token) && !empty($robot_info) && !in_array($robot_token, $unlocked_robots_tokens)){
                            $unlocked_robots_tokens[] = $robot_token;
                        }
                    }
                }
            }
        }
        // Otherwise, loop through all robots and make sure no player has unlocked this robot
        else {
            // Loop through and collect the robot settings and rewards for all players
            $battle_values = array('battle_rewards', 'battle_settings');
            foreach ($battle_values AS $value_token){
                foreach ($_SESSION[$session_token]['values'][$value_token] AS $player_token => $player_info){
                    if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'])){
                        foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'] AS $robot_token => $robot_info){
                            if (!empty($robot_token) && !empty($robot_info) && !in_array($robot_token, $unlocked_robots_tokens)){
                                $unlocked_robots_tokens[] = $robot_token;
                            }
                        }
                    }
                }
            }
        }
        // Return the collected robot tokens
        return $unlocked_robots_tokens;
    }


    // Define a function for unlocking a game robot for use in battle
    public static function unlock_robot($player_info, $robot_info, $unlock_abilities = true, $events_create = true){
        // Reference the global variables
        global $mmrpg_index, $db;

        //$_SESSION[$session_token] = &$_SESSION[self::session_token()];
        $session_token = self::session_token();

        // If the player info was a string, create the info array
        if (is_string($player_info)){ $player_info = array('player_token' => $player_info); }
        // Else if the player token does not exist, return false
        elseif (is_array($player_info) && !isset($player_info['player_token'])){ return false; }

        // If the robot info was a string, create the info array
        if (is_string($robot_info)){ $robot_info = array('robot_token' => $robot_info); }
        // Else if the robot token does not exist, return false
        elseif (is_array($robot_info) && !isset($robot_info['robot_token'])){ return false; }

        // Define a reference to the game's session flag variable
        if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
        $temp_game_flags = &$_SESSION[$session_token]['flags'];

        // If this robot does not exist in the global index, return false
        //if (!isset($player_info['player_token'])){ echo 'player_info<pre>'.print_r($player_info, true).'</pre>'; }
        $player_index_info = $mmrpg_index['players'][$player_info['player_token']];
        $robot_index_info = $robot_info;
        if (!isset($player_index_info)){ return false; }
        if (!isset($robot_index_info)){ return false; }

        // Collect the robot info from the inde
        $this_robot_token = $robot_info['robot_token'];
        $this_player_token = $player_info['player_token'];
        $this_robot_level = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
        $this_robot_experience = !empty($robot_info['robot_experience']) ? $robot_info['robot_experience'] : 0;
        $player_info = array_replace($player_index_info, $player_info);
        $robot_info = array_replace($robot_index_info, $robot_info);

        // Collect or define the robot points and robot rewards variables
        $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

        // Automatically unlock this robot for use in battle and create the settings array
        $this_reward = array(
            'flags' => array(),
            'values' => array(),
            'counters' => array(),
            'robot_token' => $this_robot_token,
            'robot_level' => $this_robot_level,
            'robot_experience' => $this_robot_experience,
            'robot_energy' => 0,
            'robot_attack' => 0,
            'robot_defense' => 0,
            'robot_speed' => 0,
            'robot_energy_pending' => 0,
            'robot_attack_pending' => 0,
            'robot_defense_pending' => 0,
            'robot_speed_pending' => 0
            );
        $_SESSION[$session_token]['values']['battle_rewards'][$player_info['player_token']]['player_robots'][$this_robot_token] = $this_reward;
        if (empty($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'])
            || empty($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$this_robot_token])
            || count($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots']) < 8){
            $this_setting = array(
                'flags' => array(),
                'values' => array(),
                'counters' => array(),
                'robot_token' => $this_robot_token,
                'robot_abilities' => array(),
                'original_player' => $player_info['player_token']
                );
            $_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$this_robot_token] = $this_setting;
        }

        // Add this robot to the global robot database array
        $temp_data_existed = !empty($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]) ? true : false;
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token] = array('robot_token' => $this_robot_token); }
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_unlocked'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_unlocked'] = 1; }
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_summoned'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_summoned'] = 0; }
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_encountered'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_encountered'] = 0; }
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_scanned'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_scanned'] = 0; }
        //$_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_unlocked']++;

        // Only show the event if allowed by the function args
        if ($events_create){

            // Generate the attributes and text variables for this robot unlock
            $robot_info_size = isset($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] * 2 : 40 * 2;
            $robot_info_size_token = $robot_info_size.'x'.$robot_info_size;
            $this_name = $robot_info['robot_name'];
            $this_description = !empty($robot_info['robot_description']) && $robot_info['robot_description'] != '...' ? $robot_info['robot_description'] : '';
            $this_number = $robot_info['robot_number'];
            $this_energy_boost = round($robot_info['robot_energy'] * 0.05, 1);
            $this_attack_boost = round($robot_info['robot_attack'] * 0.05, 1);
            $this_defense_boost = round($robot_info['robot_defense'] * 0.05, 1);
            $this_speed_boost = round($robot_info['robot_speed'] * 0.05, 1);
            $this_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
            $this_replace = array($player_info['player_name'], $robot_info['robot_name'], $player_info['player_name'], ($this_player_token == 'dr-light' ? 'Mega Man' : ($this_player_token == 'dr-wily' ? 'Bass' : ($this_player_token == 'dr-cossack' ? 'Proto Man' : 'Robot'))));
            $this_quote = !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($this_find, $this_replace, $robot_info['robot_quotes']['battle_taunt']) : '...';
            $this_field = rpg_field::get_index_info(!empty($robot_info['robot_field']) ? $robot_info['robot_field'] : 'intro-field');
            $this_pronoun = 'he'; $this_posessive = 'his';
            $this_congrats = 'Congratulations!';
            if (in_array($robot_info['robot_token'], array('roll', 'disco', 'rhythm'))){ $this_congrats = '<strong>'.$this_name.'</strong> to the rescue!'; }
            if (in_array($robot_info['robot_token'], array('roll', 'disco', 'rhythm', 'splash-woman'))){ $this_pronoun = 'she'; $this_posessive = 'her'; }
            elseif (in_array($robot_info['robot_token'], array('met'))){ $this_pronoun = 'it'; $this_posessive = 'its'; }
            $this_best_stat = $robot_info['robot_energy'];
            $this_best_attribute = 'a support';
            if ($robot_info['robot_attack'] > $this_best_stat){ $this_best_stat = $robot_info['robot_attack']; $this_best_attribute = 'a powerful'; }
            elseif ($robot_info['robot_defense'] > $this_best_stat){ $this_best_stat = $robot_info['robot_defense']; $this_best_attribute = 'a defensive'; }
            elseif ($robot_info['robot_speed'] > $this_best_stat){ $this_best_stat = $robot_info['robot_speed']; $this_best_attribute = 'a speedy'; }
            if ($robot_info['robot_token'] == 'met'){ $this_best_attribute = 'bonus'; }
            $this_first_ability = array('level' => 0, 'token' => 'buster-shot');
            $this_count_abilities = count($robot_info['robot_rewards']['abilities']);
            //die('<pre>'.print_r($robot_info['robot_rewards']['abilities'], true).'</pre>');
            foreach ($robot_info['robot_rewards']['abilities'] AS $temp_key => $temp_reward){ if ($temp_reward['token'] != 'buster-shot' && $temp_reward['level'] > 0){ $this_first_ability = $temp_reward; break; } }
            $temp_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            $this_first_ability_name = $temp_ability_index[$this_first_ability['token']]['ability_name'];
            //die('<pre>'.print_r($this_first_ability, true).'</pre>');
            if ($robot_info['robot_token'] == 'oil-man' || $robot_info['robot_token'] == 'time-man'){ $this_first_appearance = 'that first appeared in <em>Mega Man Powered Up</em> for the Sony PlayStation Portable'; }
            elseif ($robot_info['robot_game'] == 'MM01' || $robot_info['robot_token'] == 'mega-man' || $robot_info['robot_token'] == 'roll'){ $this_first_appearance = 'that first appeared in the original <em>Mega Man</em> on the Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM02'){ $this_first_appearance = 'that first appeared in <em>Mega Man 2</em> for the Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM03' || $robot_info['robot_token'] == 'proto-man'){ $this_first_appearance = 'that first appeared in <em>Mega Man 3</em> for the Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM04'){ $this_first_appearance = 'that first appeared in <em>Mega Man 4</em> for the Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM05'){ $this_first_appearance = 'that first appeared in <em>Mega Man 5</em> for the Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM06'){ $this_first_appearance = 'that first appeared in <em>Mega Man 6</em> for the Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM07' || $robot_info['robot_token'] == 'bass'){ $this_first_appearance = 'that first appeared in <em>Mega Man 7</em> for the Super Nintendo Entertainment System'; }
            elseif ($robot_info['robot_game'] == 'MM08' || $robot_info['robot_token'] == 'duo'){ $this_first_appearance = 'that first appeared in <em>Mega Man 8</em> for the Sega Saturn and Sony PlayStation'; }
            elseif ($robot_info['robot_game'] == 'MM085'){ $this_first_appearance = 'that first appeared in <em title="Rockman &amp; Forte in Japan">Mega Man &amp; Bass</em> for the Super Nintendo Entertainment System and Nintendo Game Boy Advance'; }
            elseif ($robot_info['robot_game'] == 'MM09'){ $this_first_appearance = 'that first appeared in <em>Mega Man 9</em> for Nintendo Wii, Sony PlayStation 3, and Xbox 360'; }
            elseif ($robot_info['robot_game'] == 'MM10'){ $this_first_appearance = 'that first appeared in <em>Mega Man 10</em> for Nintendo Wii, Sony PlayStation 3, and Xbox 360'; }
            elseif ($robot_info['robot_game'] == 'MM21'){ $this_first_appearance = 'that first appeared in <em>Mega Man : The Wily Wars</em> for Sega Mega Drive'; }
            elseif ($robot_info['robot_game'] == 'MM30'){ $this_first_appearance = 'that first appeared in <em>Mega Man V</em> for Nintendo Game Boy'; }
            elseif ($robot_info['robot_token'] == 'disco' || $robot_info['robot_token'] == 'rhythm'){ $this_first_appearance = 'making her debut in the <em>Mega Man RPG Prototype</em>'; }
            elseif ($robot_info['robot_token'] == 'bond-man'){ $this_first_appearance = 'making his first playable debut in the <em>Mega Man RPG Prototype</em>'; }
            elseif ($robot_info['robot_token'] == 'enker'){ $this_first_appearance = 'that first appeared in <em>Mega Man : Dr. Wily\'s Revenge</em> for the Nintendo Game Boy'; }
            elseif ($robot_info['robot_token'] == 'punk'){ $this_first_appearance = 'that first appeared in <em>Mega Man III</em> for the Nintendo Game Boy'; }
            elseif ($robot_info['robot_token'] == 'ballade'){ $this_first_appearance = 'that first appeared in <em>Mega Man IV</em> for the Nintendo Game Boy'; }
            elseif ($robot_info['robot_token'] == 'quint'){ $this_first_appearance = 'that first appeared in <em>Mega Man II</em> for the Nintendo Game Boy'; }
            elseif ($robot_info['robot_token'] == 'solo'){ $this_first_appearance = 'that first appeared in <em>Mega Man Star Force 3</em> for the Nintendo DS'; }
            elseif (preg_match('/^duo/i', $robot_info['robot_token'])){ $this_first_appearance = 'that first appeared in <em>Mega Man 7</em> for the Super Nintendo Entertainment System'; }
            elseif (preg_match('/^trio/i', $robot_info['robot_token'])){ $this_first_appearance = 'making their debut in the <em>Mega Man RPG Prototype</em>'; }
            if ($this_first_ability['level'] == 0){ $this_level = 1; }
            else { $this_level = $this_first_ability['level']; }
            $this_weaknesses = !empty($robot_info['robot_weaknesses']) ? $robot_info['robot_weaknesses'] : array();
            $this_resistances = !empty($robot_info['robot_resistances']) ? $robot_info['robot_resistances'] : array();
            $this_affinities = !empty($robot_info['robot_affinities']) ? $robot_info['robot_affinities'] : array();
            $this_immunities = !empty($robot_info['robot_immunities']) ? $robot_info['robot_immunities'] : array();
            foreach ($this_weaknesses AS $key => $token){ $this_weaknesses[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
            foreach ($this_resistances AS $key => $token){ $this_resistances[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
            foreach ($this_affinities AS $key => $token){ $this_affinities[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
            foreach ($this_immunities AS $key => $token){ $this_immunities[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
            //$this_weaknesses = implode(', ', $this_weaknesses);
            //$this_resistances = implode(', ', $this_resistances);
            //$this_affinities = implode(', ', $this_affinities);
            //$this_immunities = implode(', ', $this_immunities);
            // Generate the window event's canvas and message markup then append to the global array
            $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';
            $temp_canvas_markup .= '<div class="sprite sprite_'.$robot_info_size_token.' sprite_'.$robot_info_size_token.'_victory" style="background-image: url(images/robots/'.$robot_info['robot_token'].'/sprite_right_'.$robot_info_size_token.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: '.(200 - (($robot_info_size - 80) * 0.5)).'px;">'.$robot_info['robot_name'].'</div>';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/'.$player_info['player_token'].'/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">'.$player_info['player_name'].'</div>';
            //$temp_console_markup = '<p>Congratulations!  <strong>'.$player_info['player_name'].'</strong> unlocked <strong>'.$this_name.'</strong> '.(!empty($this_description) ? '- the '.str_replace('Robot', 'robot', $this_description).' -' : '').' ('.$this_number.') as a playable character! &quot;<em>'.$this_quote.'</em>&quot; <strong>'.$this_name.'</strong> is '.$this_best_attribute.' '.(!empty($robot_info['robot_core']) ? '<strong class="robot_type robot_type_'.$robot_info['robot_core'].'">'.ucfirst($robot_info['robot_core']).' Core</strong> ' : '<strong class="robot_type robot_type_none">Neutral Core</strong> ').'robot '.$this_first_appearance.'.</p>';
            $temp_console_markup = '<p>'.$this_congrats.'  <strong>'.$player_info['player_name'].'</strong> unlocked <strong>'.$this_name.'</strong> as a playable character! <strong>'.$this_name.'</strong> is '.$this_best_attribute.' '.(!empty($robot_info['robot_core']) ? '<strong data-class="robot_type robot_type_'.$robot_info['robot_core'].'">'.ucfirst($robot_info['robot_core']).' Core</strong> ' : '<strong data-class="robot_type robot_type_none">Neutral Core</strong> ').'robot '.$this_first_appearance.'. <strong>'.$this_name.'</strong>&#39;s data was '.($temp_data_existed ? 'updated in ' : 'added to ' ).' the <strong>Robot Database</strong>.</p>';
            $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', rpg_robot::print_database_markup($robot_info, array('layout_style' => 'event'))).'</div></div></div>';
            //die(''.$this_robot_token.': '.$temp_console_markup);

            $_SESSION[$session_token]['EVENTS'][] = array(
                'canvas_markup' => $temp_canvas_markup,
                'console_markup' => $temp_console_markup
                );

        }

        // Loop through the ability rewards for this robot if set
        if ($unlock_abilities && !empty($this_robot_rewards['abilities'])){
            // Collect the ability index for calculation purposes
            $this_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            foreach ($this_robot_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){
                // Check if the required amount of points have been met by this robot
                if ($this_robot_level >= $ability_reward_info['level']){
                    // Unlock this ability
                    $this_ability_info = rpg_ability::parse_index_info($this_ability_index[$ability_reward_info['token']]);
                    $this_ability_info['ability_points'] = $ability_reward_info['level'];
                    $show_event = !self::ability_unlocked('', '', $ability_reward_info['token']) ? true : false;
                    self::unlock_ability($player_info, $robot_info, $this_ability_info, $show_event);
                }
            }
        }

        // Create the event flag for unlocking this robot
        $temp_game_flags['events']['unlocked-robot_'.$this_robot_token] = true;
        if (!empty($this_player_token)){ $temp_game_flags['events']['unlocked-robot_'.$this_player_token.'_'.$this_robot_token] = true; }

        // Return true on success
        return true;
    }


    // Define a function for updating a player setting for use in battle
    public static function robot_setting($player_info, $robot_info, $setting_token, $setting_value){
        // Reference the global variables
        //global $mmrpg_index;
        // Update or create the player setting in the session
        $player_token = $player_info['player_token'];
        $robot_token = $robot_info['robot_token'];
        $_SESSION[self::session_token()]['values']['battle_settings'][$player_token]['player_robots'][$robot_token][$setting_token] = $setting_value;
        // Return true on success
        return true;
    }


    // Define a function for checking a robot's prototype experience total
    public static function robot_experience($player_token, $robot_token){
        // Return the current point total for this robot
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_experience'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_experience']; }
        elseif (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_points'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_points']; }
        else { return 0; }
    }


    // Define a function for checking a robot's prototype current level
    public static function robot_level($player_token, $robot_token){
        // Return the current level total for this robot
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_level'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_level']; }
        else { return 1; }
    }


    // Define a function for checking a robot's prototype current level
    public static function robot_original_player($player_token, $robot_token){
        // Return the current level total for this robot
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['original_player'])){ return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['original_player']; }
        else { return $player_token; }
    }


    // Define a function for checking a robot's prototype reward array
    public static function robot_rewards($player_token = '', $robot_token){
        // Define the game session helper var
        $session_token = self::session_token();
        // Return the current reward array for this robot
        if (!empty($player_token)){
            if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])){
                return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
            }
        } elseif (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
                if (!empty($player_info['player_robots'][$robot_token])){
                    return $player_info['player_robots'][$robot_token];
                }
            }
        }
        return array();
    }


    // Define a function for checking a robot's prototype settings array
    public static function robot_settings($player_token = '', $robot_token){
        // Define the game session helper var
        $session_token = self::session_token();
        // Return the current setting array for this robot
        if (!empty($player_token)){
            if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
                return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token];
            }
        } elseif (!empty($_SESSION[$session_token]['values']['battle_settings'])){
            foreach ($_SESSION[$session_token]['values']['battle_settings'] AS $player_token => $player_info){
                if (!empty($player_info['player_robots'][$robot_token])){
                    return $player_info['player_robots'][$robot_token];
                }
            }
        }
        return array();
    }


    // Define a function for checking a robot's prototype settings array
    public static function robot_settings_abilities($player_token = '', $robot_token){
        // Direct collect the settings for this robot
        $this_settings = self::robot_settings($player_token, $robot_token);
        $this_abilities = !empty($this_settings['robot_abilities']) ? array_keys($this_settings['robot_abilities']) : array();
        return $this_abilities;
    }


    // Define a function for checking a player's robot database array
    public static function robot_database(){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        //die('<pre style="color: white;">session_values('.$session_token.')! '.print_r($_SESSION[$session_token]['values'], true).'</pre>');
        if (!empty($_SESSION[$session_token]['values']['robot_database'])){ return $_SESSION[$session_token]['values']['robot_database']; }
        else { return array(); }
    }


    // Define a function for checking a player's robot favourites array
    public static function robot_favourites(){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values']['robot_favourites'])){ return $_SESSION[$session_token]['values']['robot_favourites']; }
        else { return array(); }
    }


    // Define a function for checking a player's prototype rewards array
    public static function robot_favourite($robot_token){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        if (!isset($_SESSION[$session_token]['values']['robot_favourites'])){ $_SESSION[$session_token]['values']['robot_favourites'] = array(); }
        return in_array($robot_token, $_SESSION[$session_token]['values']['robot_favourites']) ? true : false;
    }


    // Define a function for checking if a specific robot has been scanned
    public static function robot_scanned($robot_token){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        if (!isset($_SESSION[$session_token]['values']['robot_database'])){ $_SESSION[$session_token]['values']['robot_database'] = array(); }
        return !empty($_SESSION[$session_token]['values']['robot_database'][$robot_token]['robot_scanned']) ? true : false;
    }


    // Define a function for adding a specific robot scan to the game database
    public static function scan_robot($robot_token){
        // Return the current rewards array for this player
        $session_token = self::session_token();
        // Add this robot to the global robot database array
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$robot_token])){ $_SESSION[$session_token]['values']['robot_database'][$robot_token] = array('robot_token' => $robot_token); }
        if (!isset($_SESSION[$session_token]['values']['robot_database'][$robot_token]['robot_scanned'])){ $_SESSION[$session_token]['values']['robot_database'][$robot_token]['robot_scanned'] = 0; }
        $_SESSION[$session_token]['values']['robot_database'][$robot_token]['robot_scanned']++;
    }


    // -- GAME STATUS FUNCTIONS -- //

    // Define a function for checking the Copy Abilities have been unlocked
    public static function copy_abilities_unlocked(){

        // If Dr. Light has unlocked all of his own robots (1 hero, 1 support, 8 master), unlock the Copy Abilities
        if (self::robots_unlocked('dr-light', true) >= 10){ return true; }
        else { return false; }

    }

    // Define a function for checking the Core Abilities have been unlocked
    public static function core_abilities_unlocked(){

        // If Dr. Wily has unlocked all of his own robots (1 hero, 1 support, 8 master), unlock the Core Abilities
        if (self::robots_unlocked('dr-wily', true) >= 10){ return true; }
        else { return false; }

    }

    // Define a function for checking the Omega Abilities have been unlocked
    public static function omega_abilities_unlocked(){

        // If Dr. Cossack has unlocked all of his own robots (1 hero, 1 support, 8 master), unlock the Omega Abilities
        if (self::robots_unlocked('dr-cossack', true) >= 10){ return true; }
        else { return false; }

    }



    // -- ABILITY FUNCTIONS -- //


    // Define a function for checking if a prototype ability has been unlocked
    public static function ability_unlocked($player_token = '', $robot_token = '', $ability_token = ''){
        // Define the game session helper var
        $session_token = self::session_token();
        // If the combined array exists and we're not being specific, check that first
        if (empty($player_token) && empty($robot_token) && isset($_SESSION[$session_token]['values']['battle_abilities'])){
            // Check if this ability exists in the array, and return true if it does
            return in_array($ability_token, $_SESSION[$session_token]['values']['battle_abilities']) ? true : false;
        }
        // Otherwise, check the old way by looking through individual arrays
        else {
            // If a specific robot token was provided
            if (!empty($robot_token)){
                // Check if this ability has been unlocked by the specified robot and return true if it was
                return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token]) ? true : false;
            } elseif (!empty($player_token)){
                // Check if this ability has been unlocked by the player and return true if it was
                return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities'][$ability_token]) ? true : false;
            } else {
                // Check if this ability has been unlocked by any player and return true if it was
                if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
                    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $ptoken => $pinfo){
                        if (!empty($pinfo['player_abilities'][$ability_token])){ return $pinfo['player_abilities'][$ability_token]; }
                        else { continue; }
                    }
                }
                // Return false if nothing found
                return false;
            }
        }
    }


    // Define a function for checking if a prototype ability has been unlocked
    public static function abilities_unlocked($player_token = '', $robot_token = ''){
        // Pull in global variables
        //global $mmrpg_index;
        $mmrpg_index_players = $GLOBALS['mmrpg_index']['players'];
        $session_token = self::session_token();
        // If the combined session array exists, use that to check to unlocked
        if (empty($player_token) && empty($robot_token) && isset($_SESSION[$session_token]['values']['battle_abilities'])){
            // Count the number of abilities in the combined array
            return !empty($_SESSION[$session_token]['values']['battle_abilities']) ? count($_SESSION[$session_token]['values']['battle_abilities']) : 0;
        }
        // Otherwise, we check the separate player arrays to see if unlocked
        else {
            // If a specific robot token was provided
            if (!empty($player_token) && !empty($robot_token)){
                // Check if this battle has been completed and return true is it was
                return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) : 0;
            } elseif (!empty($player_token)){
                // Check if this ability has been unlocked by the player and return true if it was
                return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) : 0;
            } else {
                // Define the ability counter and token tracker
                $ability_tokens = array();
                foreach ($mmrpg_index_players AS $temp_player_token => $temp_player_info){
                    $temp_player_abilities = isset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_abilities']) ? $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_abilities'] : array();
                    foreach ($temp_player_abilities AS $temp_ability_token => $temp_ability_info){
                        if (!in_array($temp_ability_token, $ability_tokens)){
                            $ability_tokens[] = $temp_ability_token;
                        }
                    }
                }
                // Return the total amount of ability tokens pulled
                return !empty($ability_tokens) ? count($ability_tokens) : 0;
            }
        }
    }


    // Define a function for collecting all abilities unlocked by player or all
    public static function ability_tokens_unlocked($player_token = ''){
        // Define the game session helper var
        $session_token = self::session_token();
        // Define the temp ability and return arrays
        $unlocked_abilities_tokens = array();
        // If the player token was not false, attempt to collect rewards and settings arrays for that player
        if (!empty($player_token)){
            // Loop through and collect the ability settings and rewards for this player
            $battle_values = array('battle_rewards', 'battle_settings');
            foreach ($battle_values AS $value_token){
                if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_abilities'])){
                    foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_abilities'] AS $ability_token => $ability_info){
                        if (!empty($ability_token) && !empty($ability_info) && !in_array($ability_token, $unlocked_abilities_tokens)){
                            $unlocked_abilities_tokens[] = $ability_token;
                        }
                    }
                }
            }
        }
        // Otherwise, loop through all abilities and make sure no player has unlocked this ability
        else {
            // Loop through and collect the ability settings and rewards for all players
            if (!empty($_SESSION[$session_token]['values']['battle_abilities'])){
                foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $ability_key => $ability_token){
                    if (!empty($ability_token) && !in_array($ability_token, $unlocked_abilities_tokens)){
                        $unlocked_abilities_tokens[] = $ability_token;
                    }
                }
            }
        }
        // Return the collected ability tokens
        return $unlocked_abilities_tokens;
    }


    // Define a function for unlocking a game ability for use in battle
    public static function unlock_ability($player_info, $robot_info, $ability_info, $events_create = false){
        //$GAME_SESSION = &$_SESSION[self::session_token()];
        $session_token = self::session_token();

        // Define a reference to the game's session flag variable
        if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
        $temp_game_flags = &$_SESSION[$session_token]['flags'];

        // If the ability token does not exist, return false
        if (!isset($ability_info['ability_token'])){ return false; }
        // Turn off the event if it's been turned on and shouldn't be
        if ($ability_info['ability_token'] == 'buster-shot'){ $events_create = false; }
        if (self::ability_unlocked('', '', $ability_info['ability_token'])){ $events_create = false; }
        if (!empty($_SESSION[$session_token]['DEMO'])){ $events_create = false; }

        // Attempt to collect info for this ability
        $ability_index = rpg_ability::get_index_info($ability_info['ability_token']);
        // If this ability does not exist in the global index, return false
        if (empty($ability_index)){ return false; }
        // Collect the ability info from the index
        $ability_info = array_replace($ability_index, $ability_info);
        // Collect or define the ability variables
        $this_ability_token = $ability_info['ability_token'];
        // Automatically unlock this ability for use in battle
        $this_reward = $this_setting = array('ability_token' => $this_ability_token);

        // Check if player info and robot info has been provided, and unlock for this robot if it has
        if (!empty($player_info) && !empty($robot_info)){
            // This is for a robot, so let's unlock it for that robot
            $_SESSION[$session_token]['values']['battle_rewards'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities'][$this_ability_token] = $this_reward;
            // If this robot has less than eight abilities equipped, automatically attach this one
            if (empty($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities'])
                || count($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities']) < 8){
                // Create the ability reward setting and insert it into the session array
                $_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities'][$this_ability_token] = $this_setting;
            }
        }

        // Check to see if player info has been provided, and unlock for this player if it has
        if (!empty($player_info)){
            // This request is for a player, so let's unlocked
            $_SESSION[$session_token]['values']['battle_rewards'][$player_info['player_token']]['player_abilities'][$this_ability_token] = $this_reward;
        }

        // No matter what, always unlock new abilities in the main array
        if (!isset($_SESSION[$session_token]['values']['battle_abilities'])){ $_SESSION[$session_token]['values']['battle_abilities'] = array(); }
        if (!in_array($this_ability_token, $_SESSION[$session_token]['values']['battle_abilities'])){ $_SESSION[$session_token]['values']['battle_abilities'][] = $this_ability_token; }

        // Only show the event if allowed by the function args
        if ($events_create != false){

            // Generate the attributes and text variables for this ability unlock
            global $db;
            $this_player_token = $player_info['player_token'];
            $ability_info_size = isset($ability_info['ability_image_size']) ? $ability_info['ability_image_size'] * 2 : 40 * 2;
            $ability_info_size_token = $ability_info_size.'x'.$ability_info_size;
            $this_name = $ability_info['ability_name'];
            $this_type_token = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : '';
            if (!empty($ability_info['ability_type2'])){ $this_type_token .= '_'.$ability_info['ability_type2']; }
            if (empty($this_type_token)){ $this_type_token = 'none'; }
            $this_description = !empty($ability_info['ability_description']) && $ability_info['ability_description'] != '...' ? $ability_info['ability_description'] : '';
            $this_find = array('{this_player}', '{this_ability}', '{target_player}', '{target_ability}');
            $this_replace = array($player_info['player_name'], $ability_info['ability_name'], $player_info['player_name'], ($this_player_token == 'dr-light' ? 'Mega Man' : ($this_player_token == 'dr-wily' ? 'Bass' : ($this_player_token == 'dr-cossack' ? 'Proto Man' : 'Robot'))));
            $this_field = array('field_token' => 'intro-field', 'field_name' => 'Intro Field'); //rpg_field::get_index_info('field'); //rpg_field::get_index_info(!empty($ability_info['ability_field']) ? $ability_info['ability_field'] : 'intro-field');
            $temp_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            // Generate the window event's canvas and message markup then append to the global array
            $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';

            $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/'.$player_info['player_token'].'/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 220px;">'.$player_info['player_name'].'</div>';

            $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/abilities/'.str_replace('dr-', '', $player_info['player_token']).'-buster/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">&nbsp;</div>';
            $temp_canvas_markup .= '<div class="ability_type ability_type_'.$this_type_token.' sprite sprite_40x40 sprite_40x40_00" style="
                position: absolute;
                bottom: 52px;
                right: 212px;
                padding: 4px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                border-style: solid;
                border-color: #181818;
                border-width: 4px;
                box-shadow: inset 1px 1px 6px rgba(0, 0, 0, 0.8);
                ">&nbsp;</div>';
            $temp_canvas_markup .= '<div class="sprite" style="
                bottom: 57px;
                right: 217px;
                width: 44px;
                height: 44px;
                overflow: hidden;
                background-color: rgba(13,13,13,0.33);
                -moz-border-radius: 6px;
                -webkit-border-radius: 6px;
                border-radius: 6px;
                border-style: solid;
                border-color: #292929;
                border-width: 1px;
                box-shadow: 0 0 6px rgba(255, 255, 255, 0.6);
                "><div class="sprite sprite_'.$ability_info_size_token.' sprite_'.$ability_info_size_token.'_base" style="
                background-image: url(images/abilities/'.$ability_info['ability_token'].'/icon_right_'.$ability_info_size_token.'.png?'.MMRPG_CONFIG_CACHE_DATE.');
                bottom: -18px;
                right: -18px;
                ">'.$ability_info['ability_name'].'</div></div>';

            $temp_console_markup = '<p>Congratulations!  <strong>'.$player_info['player_name'].'</strong> unlocked the <strong>'.$this_name.'</strong> ability! </p>'; //<strong>'.$this_name.'</strong> is '.(!empty($ability_info['ability_type']) ? (preg_match('/^(a|e|i|o|u|y)/i', $ability_info['ability_type']) ? 'an ' : 'a ').'<strong data-class="ability_type ability_type_'.$ability_info['ability_type'].(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '').'">'.ucfirst($ability_info['ability_type']).(!empty($ability_info['ability_type2']) ? ' and '.ucfirst($ability_info['ability_type2']) : '').' Type</strong> ' : '<strong data-class="ability_type ability_type_none">Neutral Type</strong> ').'ability. <strong>'.$this_name.'</strong>&#39;s data was '.($temp_data_existed ? 'updated in ' : 'added to ' ).' the <strong>Robot Database</strong>.
            $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', rpg_ability::print_database_markup($ability_info, array('layout_style' => 'event'))).'</div></div></div>';
            //die(''.$this_ability_token.': '.$temp_console_markup);

            $_SESSION[$session_token]['EVENTS'][] = array(
                'canvas_markup' => preg_replace('/\s+/', ' ', $temp_canvas_markup),
                'console_markup' => $temp_console_markup
                );

        }

        // Create the event flag for unlocking this robot
        $temp_game_flags['events']['unlocked-ability_'.$this_ability_token] = true;
        if (!empty($this_player_token)){ $temp_game_flags['events']['unlocked-ability_'.$this_player_token.'_'.$this_ability_token] = true; }

        // Return true on success
        return true;
    }



    // -- ITEM FUNCTIONS -- //


    // Define a function for checking how many items have been unlocked by a player
    public static function items_unlocked(){
        // Define the game session helper var
        $session_token = self::session_token();
        $temp_counter = 0;
        if (!empty($_SESSION[$session_token]['values']['battle_items'])){
            foreach ($_SESSION[$session_token]['values']['battle_items'] AS $token => $quantity){
                $temp_counter += $quantity;
            }
        }
        return $temp_counter;
    }

    // Define a function for checking how many cores have been unlocked by a player
    public static function cores_unlocked(){
        // Define the game session helper var
        $session_token = self::session_token();
        $temp_counter = 0;
        if (!empty($_SESSION[$session_token]['values']['battle_items'])){
            foreach ($_SESSION[$session_token]['values']['battle_items'] AS $token => $quantity){
                if (preg_match('/^item-core-/i', $token)){ $temp_counter += $quantity; }
            }
        }
        return $temp_counter;
    }

    // Define a function for checking how many screws have been unlocked by a player
    public static function screws_unlocked($size = ''){
        // If neither screw type has ever been created, return a hard false
        $session_token = self::session_token();
        if (!isset($_SESSION[$session_token]['values']['battle_items']['item-screw-small'])
            && !isset($_SESSION[$session_token]['values']['battle_items']['item-screw-large'])){
            return false;
        }
        // Define the game session helper var
        $temp_counter = 0;
        if (isset($_SESSION[$session_token]['values']['battle_items']['item-screw-small'])){ $temp_counter += $_SESSION[$session_token]['values']['battle_items']['item-screw-small']; }
        if (isset($_SESSION[$session_token]['values']['battle_items']['item-screw-large'])){ $temp_counter += $_SESSION[$session_token]['values']['battle_items']['item-screw-large']; }
        return $temp_counter;
    }



    // -- STAR FUNCTIONS -- //


    // Define a function for checking is a prototype star has been unlocked
    public static function star_unlocked($star_token){
        $session_token = self::session_token();
        if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return false; }
        elseif (empty($_SESSION[$session_token]['values']['battle_stars'][$star_token])){ return false; }
        else { return true; }
    }


    // Define a function for checking is a prototype star has been unlocked
    public static function stars_unlocked($player_token = '', $star_kind = ''){
        // Define the game session helper var
        $session_token = self::session_token();
        if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return 0; }
        else {
            $temp_stars_index = $_SESSION[$session_token]['values']['battle_stars'];
            if (empty($player_token) && empty($star_kind)){ return count($temp_stars_index); }
            foreach ($temp_stars_index AS $key => $info){
                if (!empty($player_token) && $info['star_player'] != $player_token){ unset($temp_stars_index[$key]); }
                elseif (!empty($star_kind) && $info['star_kind'] != $star_kind){ unset($temp_stars_index[$key]); }
            }
            return count($temp_stars_index);
        }
    }

    // Define a function for calculating starforce values given the loaded game
    public static function starforce_unlocked($type_filter = '', $strict = false){
        // Define the game session helper var
        $session_token = self::session_token();
        if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return false; }
        else {

            // Collect the field stars from the session variable
            $this_battle_stars = !empty($_SESSION[$session_token]['values']['battle_stars']) ? $_SESSION[$session_token]['values']['battle_stars'] : array();
            $this_battle_stars_count = !empty($this_battle_stars) ? count($this_battle_stars) : 0;
            $this_battle_stars_field_count = 0;
            $this_battle_stars_fusion_count = 0;

            // Loop through the star index and increment the various type counters
            $this_star_force = array();
            $this_star_force_strict = array();
            $this_star_force_total = 0;
            $this_star_kind_counts = array();
            foreach ($this_battle_stars AS $temp_key => $temp_data){
                $star_kind = $temp_data['star_kind'];
                $star_type = !empty($temp_data['star_type']) ? $temp_data['star_type'] : '';
                $star_type2 = !empty($temp_data['star_type2']) ? $temp_data['star_type2'] : '';
                if ($star_kind == 'field'){ $this_battle_stars_field_count++; }
                elseif ($star_kind == 'fusion'){ $this_battle_stars_fusion_count++; }
                if (!empty($star_type)){
                    if (!isset($this_star_force[$star_type])){ $this_star_force[$star_type] = 0; }
                    if (!isset($this_star_force_strict[$star_type])){ $this_star_force_strict[$star_type] = 0; }
                    if (!isset($this_star_kind_counts[$star_kind][$star_type])){ $this_star_kind_counts[$star_kind][$star_type] = 0; }
                    $this_star_force[$star_type]++;
                    $this_star_force_strict[$star_type]++;
                    $this_star_kind_counts[$star_kind][$star_type]++;
                    $this_star_force_total++;
                }
                if (!empty($star_type2)){
                    if (!isset($this_star_force[$star_type2])){ $this_star_force[$star_type2] = 0; }
                    if (!isset($this_star_force_strict[$star_type2])){ $this_star_force_strict[$star_type2] = 0; }
                    if (!isset($this_star_kind_counts[$star_kind][$star_type2])){ $this_star_kind_counts[$star_kind][$star_type2] = 0; }
                    $this_star_force[$star_type2]++;
                    if ($star_type != $star_type2){
                        $this_star_force_strict[$star_type2]++;
                        $this_star_kind_counts[$star_kind][$star_type2]++;
                    }
                    $this_star_force_total++;
                }
            }
            asort($this_star_force);
            $this_star_force = array_reverse($this_star_force);

            // Return the entire or filtered force array based on request
            $return_array = $strict == true ? $this_star_force_strict : $this_star_force;
            if (empty($type_filter)){
                if (!empty($return_array)){ return $return_array; }
                else { return array(); }
            } else {
                if (isset($return_array[$type_filter])){ return $return_array[$type_filter]; }
                else { return array(); }
            }

        }
    }

    // Define a function for checking a player's prototype settings array
    public static function stars_available($player_token){
        // Return the current rewards array for this player
        $session_token = self::session_token();

        // Collect the omega factors from the session
        $temp_session_key = $player_token.'_target-robot-omega_prototype';
        if (empty($_SESSION[$session_token]['values'][$temp_session_key])){ return array('field' => 0, 'fusion' => 0); }
        $new_target_robot_omega = $_SESSION[$session_token]['values'][$temp_session_key];

        // Define the arrays to hold all available stars
        $temp_field_stars = array();
        $temp_fusion_stars = array();
        // Loop through and collect the field stars
        foreach ($new_target_robot_omega AS $key => $info){
            $temp_field_stars[] = $info['field'];
        }
        // Loop thourgh and collect the fusion stars
        for ($i = 0; $i < 8; $i += 2){
            list($t1a, $t1b) = explode('-', $temp_field_stars[$i]);
            list($t2a, $t2b) = explode('-', $temp_field_stars[$i + 1]);
            $temp_fusion_token = $t1a.'-'.$t2b;
            $temp_fusion_stars[] = $temp_fusion_token;
        }
        // Loop through field stars and remove unlocked
        foreach ($temp_field_stars AS $key => $token){
            if (!empty($_SESSION[$session_token]['values']['battle_stars'][$token])){
                unset($temp_field_stars[$key]);
            }
        }
        // Loop through fusion stars and remove unlocked
        foreach ($temp_fusion_stars AS $key => $token){
            if (!empty($_SESSION[$session_token]['values']['battle_stars'][$token])){
                unset($temp_fusion_stars[$key]);
            }
        }
        // Count the field stars
        $temp_field_stars = array_values($temp_field_stars);
        $temp_field_stars_count = count($temp_field_stars);
        // Count the fusion stars
        $temp_fusion_stars = array_values($temp_fusion_stars);
        $temp_fusion_stars_count = count($temp_fusion_stars);

        /*
        // DEBUG DEBUG
        die(
            '<pre>$temp_field_stars = '.print_r($temp_field_stars, true).'</pre><br />'.
            '<pre>$temp_fusion_stars = '.print_r($temp_fusion_stars, true).'</pre><br />'
            );
        */

        // Return the star counts
        return array('field' => $temp_field_stars_count, 'fusion' => $temp_fusion_stars_count);
    }



    // -- SKIN FUNCTIONS -- //


    // Define a function for checking if a prototype skin has been unlocked
    public static function skin_unlocked($robot_token = '', $skin_token = 'alt'){

        // Define the game session helper var
        $session_token = self::session_token();

        // If the robot token or alt token was not provided, return false
        if (empty($robot_token) || empty($skin_token)){ return false; }

        // Loop through all the robot rewards and check for this alt's presence
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $ptoken => $pinfo){
                if (!empty($pinfo['player_robots'])){
                    foreach ($pinfo['player_robots'] AS $rtoken => $rinfo){
                         if ($rtoken == $robot_token){
                             if (!isset($rinfo['robot_skins'])){
                                 // The skin array does not exist, so let's create it
                                 $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken]['robot_skins'] = $rinfo['robot_skins'] = array();
                             }
                             if (!empty($rinfo['robot_skins']) && in_array($skin_token, $rinfo['robot_skins'])){
                                 // This skin has been unlocked, so let's return true
                                 return true;
                             }
                         }
                    }
                }
            }
        }

        // If we made it this far, return false
        return false;

    }


    // -- OMEGA STRING FUNCTIONS -- //

    // Define a function for getting the current user string from session
    public static function get_user_string(){

        // Define the game session helper var
        $session_token = self::session_token();

        // Attempt to collect the username
        if (!empty($_SESSION[$session_token]['USER']['username_clean'])){
            return $_SESSION[$session_token]['USER']['username_clean'];
        } else {
            return 'mmrpg';
        }

    }

    // Define a function for getting the current omega string from the session
    public static function get_user_omega(){

        // Define the game session helper var
        $session_token = self::session_token();

        // Attempt to collect the user omega
        if (!empty($_SESSION[$session_token]['USER']['omega'])){
            return $_SESSION[$session_token]['USER']['omega'];
        } else {
            return 'mmrpg';
        }

    }

    // Define a function for generating an omega string for a given user or robot
    public static function generate_omega_string($user_string, $string_one = '', $string_two = '', $string_three = ''){

        // Concatenate seed values to form the raw omega string
        $raw_omega_string = 'mmrpg_'.$user_string;
        if (!empty($string_one)){ $raw_omega_string .= '_'.$string_one; }
        if (!empty($string_two)){ $raw_omega_string .= '_'.$string_two; }
        if (!empty($string_three)){ $raw_omega_string .= '_'.$string_three; }
        $raw_omega_string = rtrim($raw_omega_string, '_');

        // Calculate the MD5 hash of the raw omega string and crop
        $complete_omega_string = md5(MMRPG_SETTINGS_OMEGA_SEED.$raw_omega_string);
        $final_omega_string = substr($complete_omega_string, 0, 32);
        $base_rotate_amount = hexdec(substr($final_omega_string, 0, 1));
        for ($i = 1; $i < $base_rotate_amount; $i++){
            $left_char = substr($final_omega_string, 0, 1);
            $final_omega_string = substr($final_omega_string.$left_char, 1, 32);
        }

        // Return the finalized omega string value
        return $final_omega_string;

    }

    // Define a function that selects from a group of values given an omega string
    public static function select_omega_value($omega_string, $omega_values){

        // Re-index the values to be sure of zero-index and then count
        $indexed_omega_values = array_values($omega_values);
        $omega_value_count = count($indexed_omega_values);

        // Calculate the min and max range for the index keys
        $index_range_min = 0;
        $index_range_max = $omega_value_count - 1;

        // Calculate how many characters to crop from the omega string given max value
        $index_range_max_hex = dechex($index_range_max);
        $required_string_length = strlen($index_range_max_hex);

        // Make a copy of the omega string, reversed if value count not even (for variety)
        $base_omega_string = $omega_string;

        // Crop the base omega string to the required length and calculate it's decimal value
        $cropped_omega_string = substr($base_omega_string, 0, $required_string_length);
        $cropped_string_value = hexdec($cropped_omega_string);

        // Calculate the index key using the above and then collect the final value
        $this_index_key = $cropped_string_value % $omega_value_count;
        $this_omega_value = $indexed_omega_values[$this_index_key];

        // Return the finalized omega value given available options
        return $this_omega_value;

    }

    // Define a function for specifically generating a shop keeper omega string
    public static function get_omega_shop_string($shop_token, $user_omega = ''){
        if (empty($user_omega)){ $user_omega = rpg_game::get_user_omega(); }
        $shop_omega_string = self::generate_omega_string($user_omega, 'shop', $shop_token);
        return $shop_omega_string;
    }

    // Define a function for specifically generating a player character omega string
    public static function generate_omega_player_string($player_token, $user_omega = ''){
        if (empty($user_omega)){ $user_omega = rpg_game::get_user_omega(); }
        $player_omega_string = self::generate_omega_string($user_omega, 'player', $player_token);
        return $player_omega_string;
    }

    // Define a function for specifically generating a robot master omega string
    public static function generate_omega_robot_string($robot_token, $user_omega = ''){
        if (empty($user_omega)){ $user_omega = rpg_game::get_user_omega(); }
        $robot_omega_string = self::generate_omega_string($user_omega, 'robot', $robot_token);
        return $robot_omega_string;
    }



    // -- DATABASE FUNCTIONS -- //


    // Define a function for checking how many database pages have been unlocked by all players
    public static function database_unlocked(){
        // Define the game session helper var
        $session_token = self::session_token();
        // Collect the database count and return it
        if (!empty($_SESSION[$session_token]['values']['robot_database'])){ return count($_SESSION[$session_token]['values']['robot_database']); }
        else { return 0; }
    }



    // -- POINT FUNCTIONS -- //


    // Define a function for checking the battle's prototype points total
    public static function battle_points(){
        // Return the current point total for thisgame
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['counters']['battle_points'])){ return $_SESSION[$session_token]['counters']['battle_points']; }
        else { return 0; }
    }



    // -- ZENNY FUNCTIONS -- //


    // Define a function for checking how much zenny has been unlocked by all players
    public static function zenny_unlocked(){
        // Define the game session helper var
        $session_token = self::session_token();
        // Collect the zenny count and return it
        if (!empty($_SESSION[$session_token]['values']['battle_zenny'])){ return $_SESSION[$session_token]['values']['battle_zenny']; }
        else { return 0; }
    }



    // -- SESSION FUNCTIONS -- //


    // Define a function for collecting the current GAME token
    public static function session_token(){
        if (defined('MMRPG_UPDATE_GAME')){ return 'UPDATE_GAME_'.MMRPG_UPDATE_GAME; }
        elseif (defined('MMRPG_REMOTE_GAME')){ return 'REMOTE_GAME_'.MMRPG_REMOTE_GAME; }
        else { return 'GAME'; }
    }

    // Define a function for starting the game session
    public static function start_session(){

        // Initialize the user session
        self::init_user_session();

        // Initialize demo mode vars
        self::init_demo_mode();

        // Reset game vars to default values
        self::reset_session();

    }

    // Define a function for initializing the session user
    public static function init_user_session(){

        // Auto-generate basic user info with guest info
        $this_user = array();
        $this_user['userid'] = MMRPG_SETTINGS_GUEST_ID;
        $this_user['username'] = 'guest';
        $this_user['username_clean'] = 'guest';
        $this_user['imagepath'] = '';
        $this_user['backgroundpath'] = '';
        $this_user['colourtoken'] = '';
        $this_user['gender'] = '';
        $this_user['password'] = '';
        $this_user['password_encoded'] = '';
        $this_user['omega'] = '';

        // Overwrite the session user with that one
        $_SESSION['GAME']['USER'] = $this_user;

    }

    // Define a function for initializing the demo mode variables
    public static function init_demo_mode(){

        // Define the global demo flag value
        $_SESSION['GAME']['DEMO'] = 1;

        // Update the session user with demo values
        $_SESSION['GAME']['USER']['userid'] = MMRPG_SETTINGS_GUEST_ID;
        $_SESSION['GAME']['USER']['username'] = 'demo';
        $_SESSION['GAME']['USER']['username_clean'] = 'demo';
        $_SESSION['GAME']['USER']['password'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'demo';
        $_SESSION['GAME']['USER']['password_encoded'] = md5(MMRPG_SETTINGS_PASSWORD_SALT.$_SESSION['GAME']['USER']['password']);
        $_SESSION['GAME']['USER']['omega'] = md5(MMRPG_SETTINGS_OMEGA_SEED.$_SESSION['GAME']['USER']['username_clean']);

        // Auto-generate ciritcal game values
        $_SESSION['GAME']['counters']['battle_points'] = 0;

    }

    // Define a function for resetting the game session
    public static function reset_session(){
        return mmrpg_reset_game_session();
    }


    // Define a function for saving the game session
    public static function save_session(){
        return mmrpg_save_game_session();
    }


    // Define a function for loading the game session
    public static function load_session(){
        return mmrpg_load_game_session();
    }

    // Define a function for exiting the game session
    public static function exit_session(){

        // Clear the current session objects
        unset($_SESSION['GAME']);

        // Start a new session with default variables
        self::start_session();

    }

    // Define a function for collecting the current GAME session flags
    public static function get_session_flags(){
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['flags'])){ return $_SESSION[$session_token]['flags']; }
        else { return array(); }
    }

    // Define a function for collecting the current GAME session counters
    public static function get_session_counters(){
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['counters'])){ return $_SESSION[$session_token]['counters']; }
        else { return array(); }
    }

    // Define a function for collecting the current GAME session values
    public static function get_session_values(){
        $session_token = self::session_token();
        if (!empty($_SESSION[$session_token]['values'])){ return $_SESSION[$session_token]['values']; }
        else { return array(); }
    }

    // Define a function for saving the current user session to the database
    public static function session_to_database($echo = false){
        global $db;

        // If there is not a logged in user, exit
        if (!self::is_user() && !defined('MMRPG_REMOTE_GAME')){ return false; }
        elseif (defined('MMRPG_REMOTE_GAME')){ $this_userid = MMRPG_REMOTE_GAME; }
        else { global $this_userid; }

        // Collect an index of VALID and UNLOCKABLE player tokens to match against
        $mmrpg_index_players = $db->get_array_list("SELECT player_token, player_flag_unlockable FROM mmrpg_index_players WHERE player_flag_published = 1;");
        $mmrpg_valid_player_tokens = !empty($mmrpg_index_players) ? array_column($mmrpg_index_players, 'player_token') : array();
        $mmrpg_unlockable_player_tokens = !empty($mmrpg_index_players) ? array_column(array_filter($mmrpg_index_players, function($a){ return !empty($a['player_flag_unlockable']); }), 'player_token') : array();

        //echo('<pre>$mmrpg_valid_player_tokens = '.print_r($mmrpg_valid_player_tokens, true).'</pre>');
        //echo('<pre>$mmrpg_unlockable_player_tokens = '.print_r($mmrpg_unlockable_player_tokens, true).'</pre>');

        // Collect an index of VALID and UNLOCKABLE robot tokens to match against
        $mmrpg_index_robots = $db->get_array_list("SELECT robot_token, robot_flag_unlockable FROM mmrpg_index_robots WHERE robot_flag_published = 1;");
        $mmrpg_valid_robot_tokens = !empty($mmrpg_index_robots) ? array_column($mmrpg_index_robots, 'robot_token') : array();
        $mmrpg_unlockable_robot_tokens = !empty($mmrpg_index_robots) ? array_column(array_filter($mmrpg_index_robots, function($a){ return !empty($a['robot_flag_unlockable']); }), 'robot_token') : array();

        //echo('<pre>$mmrpg_valid_robot_tokens = '.print_r($mmrpg_valid_robot_tokens, true).'</pre>');
        //echo('<pre>$mmrpg_unlockable_robot_tokens = '.print_r($mmrpg_unlockable_robot_tokens, true).'</pre>');

        // Collect an index of VALID and UNLOCKABLE ability tokens to match against
        $mmrpg_index_abilities = $db->get_array_list("SELECT ability_token, ability_flag_unlockable FROM mmrpg_index_abilities WHERE ability_flag_published = 1;");
        $mmrpg_valid_ability_tokens = !empty($mmrpg_index_abilities) ? array_column($mmrpg_index_abilities, 'ability_token') : array();
        $mmrpg_unlockable_ability_tokens = !empty($mmrpg_index_abilities) ? array_column(array_filter($mmrpg_index_abilities, function($a){ return !empty($a['ability_flag_unlockable']); }), 'ability_token') : array();

        //echo('<pre>$mmrpg_valid_ability_tokens = '.print_r($mmrpg_valid_ability_tokens, true).'</pre>');
        //echo('<pre>$mmrpg_unlockable_ability_tokens = '.print_r($mmrpg_unlockable_ability_tokens, true).'</pre>');

        // Create index arrays for all players and robots to save
        $mmrpg_users_fields = array();
        $mmrpg_users_abilities = array();
        $mmrpg_users_players = array();
        $mmrpg_users_players_abilities = array();
        $mmrpg_users_players_omega = array();
        $mmrpg_users_robots = array();
        $mmrpg_users_robots_abilities = array();
        $mmrpg_users_robots_movesets = array();
        $mmrpg_users_robots_alts = array();
        $mmrpg_users_robots_records = array();
        $mmrpg_users_items = array();
        $mmrpg_users_stars = array();

        // Collect all the game session flags/counters/values
        $session_flags = self::get_session_flags();
        $session_counters = self::get_session_counters();
        $session_values = self::get_session_values();

        // Collect the global battle settings and rewards arrays
        $battle_settings = !empty($session_values['battle_settings']) ? $session_values['battle_settings'] : array();
        $battle_rewards = !empty($session_values['battle_rewards']) ? $session_values['battle_rewards'] : array();
        $battle_abilities = !empty($session_values['battle_abilities']) ? $session_values['battle_abilities'] : array();
        $battle_fields = !empty($session_values['battle_fields']) ? $session_values['battle_fields'] : array();
        $battle_items = !empty($session_values['battle_items']) ? $session_values['battle_items'] : array();
        $battle_stars = !empty($session_values['battle_stars']) ? $session_values['battle_stars'] : array();
        $robot_alts = !empty($session_values['robot_alts']) ? $session_values['robot_alts'] : array();
        $robot_database = !empty($session_values['robot_database']) ? $session_values['robot_database'] : array();

        // Collect any player omega arrays from the session
        $player_omega = array();
        $player_omega['dr-light'] = !empty($session_values['dr-light_target-robot-omega_prototype']) ? $session_values['dr-light_target-robot-omega_prototype'] : array();
        $player_omega['dr-wily'] = !empty($session_values['dr-wily_target-robot-omega_prototype']) ? $session_values['dr-wily_target-robot-omega_prototype'] : array();
        $player_omega['dr-cossack'] = !empty($session_values['dr-cossack_target-robot-omega_prototype']) ? $session_values['dr-cossack_target-robot-omega_prototype'] : array();


        // -- INDEX PLAYER OBJECTS -- //

        // Collect unique player tokens from the settings and/or rewards
        $player_tokens = array();
        if (!empty($battle_settings)){ $player_tokens = array_merge($player_tokens, array_keys($battle_settings)); }
        if (!empty($battle_rewards)){ $player_tokens = array_merge($player_tokens, array_keys($battle_rewards)); }
        $player_tokens = array_unique($player_tokens);

        // Fix issues with legacy player rewards array
        if (!empty($player_tokens)){
            foreach ($player_tokens AS $player_key => $player_token){
                if (empty($player_token)){ continue; }
                if (!empty($battle_rewards)){
                    foreach ($battle_rewards AS $player_token => $player_info){
                        // If new player robots array is empty but old is not, copy over
                        if (empty($player_info['player_robots']) && !empty($player_info['player_rewards']['robots'])){
                            // Loop through and collect robot data from the legacy rewards array
                            foreach ($player_info['player_rewards']['robots'] AS $key => $robot){
                                if (empty($robot['token'])){ continue; }
                                $robot_info = array();
                                $robot_info['robot_token'] = $robot['token'];
                                $robot_info['robot_level'] = !empty($robot['level']) ? $robot['level'] : 1;
                                $robot_info['robot_experience'] = !empty($robot['points']) ? $robot['points'] : 0;
                                $player_info['player_robots'][$robot['token']] = $robot_info;
                            }
                            // Kill the legacy rewards array to prevent confusion
                            unset($player_info['player_rewards']);
                        }
                        // If player robots are NOT empty, update in the parent array
                        if (!empty($player_info['player_robots'])){
                            $battle_rewards[$player_token] = $player_info;
                        }
                        // Otherwise if no robots found, kill this player's data in both arrays
                        else {
                            unset($battle_rewards[$player_token]);
                            unset($battle_settings[$player_token]);
                            unset($player_tokens[array_search($player_token, $player_tokens)]);
                        }
                    }
                }
            }
        }

        //echo('$player_tokens = '.print_r($player_tokens, true).PHP_EOL);

        // If players not empty, loop through and save each to session
        if (!empty($player_tokens)){
            $player_tokens = array_values($player_tokens);
            foreach ($player_tokens AS $player_token){
                if (empty($player_token) || $player_token == 'player'){ continue; }
                elseif (!in_array($player_token, $mmrpg_unlockable_player_tokens)){ continue; }

                //echo('$player_token = '.print_r($player_token, true).PHP_EOL);

                // Collect settings and rewards for this player
                $player_settings = !empty($battle_settings[$player_token]) ? $battle_settings[$player_token] : array();
                $player_rewards = !empty($battle_rewards[$player_token]) ? $battle_rewards[$player_token] : array();

                // Create an entry in the save index for this player
                if (!isset($mmrpg_users_players[$player_token])){ $mmrpg_users_players[$player_token] = array(); }
                $mmrpg_player = $mmrpg_users_players[$player_token];

                // Collect unique robot tokens from the settings and/or rewards
                $robot_tokens = array();
                if (!empty($player_settings['player_robots'])){ $robot_tokens = array_merge($robot_tokens, array_keys($player_settings['player_robots'])); }
                if (!empty($player_rewards['player_robots'])){ $robot_tokens = array_merge($robot_tokens, array_keys($player_rewards['player_robots'])); }
                $robot_tokens = array_unique($robot_tokens);

                //echo('$robot_tokens = '.print_r($robot_tokens, true).PHP_EOL);

                // Collect unique ability tokens from the settings and/or rewards
                $ability_tokens = array();
                if (!empty($player_settings['player_abilities'])){ $ability_tokens = array_merge($ability_tokens, array_keys($player_settings['player_abilities'])); }
                if (!empty($player_rewards['player_abilities'])){ $ability_tokens = array_merge($ability_tokens, array_keys($player_rewards['player_abilities'])); }
                $ability_tokens = array_unique($ability_tokens);

                //echo('$ability_tokens = '.print_r($ability_tokens, true).PHP_EOL);

                // Create or update player info using settings and rewards
                $mmrpg_player['user_id'] = $this_userid;
                $mmrpg_player['player_token'] = $player_token;

                $mmrpg_player['player_points'] = !empty($player_rewards['player_points']) ? $player_rewards['player_points'] : 0;

                $mmrpg_player['player_abilities_unlocked'] = $ability_tokens;


                // -- INDEX ROBOT OBJECTS -- //

                // If robots not empty, loop through and save each to session
                if (!empty($robot_tokens)){
                    foreach ($robot_tokens AS $robot_token){
                        if (empty($robot_token) || $robot_token == 'robot'){ continue; }
                        elseif (!in_array($robot_token, $mmrpg_unlockable_robot_tokens)){ continue; }

                        //echo('$robot_token = '.print_r($robot_token, true).PHP_EOL);

                        // Collect settings and settings for this player
                        $robot_settings = !empty($player_settings['player_robots'][$robot_token]) ? $player_settings['player_robots'][$robot_token] : array();
                        $robot_rewards = !empty($player_rewards['player_robots'][$robot_token]) ? $player_rewards['player_robots'][$robot_token] : array();

                        // Create an entry in the save index for this robot
                        if (!isset($mmrpg_users_robots[$robot_token])){ $mmrpg_users_robots[$robot_token] = array(); }
                        $mmrpg_robot = $mmrpg_users_robots[$robot_token];

                        // Collect unique ability tokens from the settings and/or rewards
                        $ability_tokens = array();
                        if (!empty($robot_settings['robot_abilities'])){ $ability_tokens = array_merge($ability_tokens, array_keys($robot_settings['robot_abilities'])); }
                        if (!empty($robot_rewards['robot_abilities'])){ $ability_tokens = array_merge($ability_tokens, array_keys($robot_rewards['robot_abilities'])); }
                        $ability_tokens = array_unique($ability_tokens);

                        //echo('$ability_tokens = '.print_r($ability_tokens, true).PHP_EOL);

                        // Create or update player info using settings and rewards
                        $mmrpg_robot['user_id'] = $this_userid;
                        $mmrpg_robot['robot_token'] = $robot_token;

                        $mmrpg_robot['robot_image'] = !empty($robot_settings['robot_image']) ? $robot_settings['robot_image'] : '';
                        $mmrpg_robot['robot_item'] = !empty($robot_settings['robot_item']) ? $robot_settings['robot_item'] : '';
                        $mmrpg_robot['robot_core'] = !empty($robot_settings['robot_core']) ? $robot_settings['robot_core'] : '';

                        $mmrpg_robot['robot_level'] = !empty($robot_rewards['robot_level']) ? $robot_rewards['robot_level'] : 1;
                        $mmrpg_robot['robot_experience'] = !empty($robot_rewards['robot_experience']) ? $robot_rewards['robot_experience'] : 0;
                        $mmrpg_robot['robot_experience_total'] = ($mmrpg_robot['robot_level'] * 1000) + $mmrpg_robot['robot_experience'];

                        $mmrpg_robot['robot_energy_bonuses'] = !empty($robot_rewards['robot_energy']) ? $robot_rewards['robot_energy'] : 0;
                        $mmrpg_robot['robot_energy_bonuses_pending'] = !empty($robot_rewards['robot_energy_pending']) ? $robot_rewards['robot_energy_pending'] : 0;
                        $mmrpg_robot['robot_weapons_bonuses'] = !empty($robot_rewards['robot_weapons']) ? $robot_rewards['robot_weapons'] : 0;
                        $mmrpg_robot['robot_weapons_bonuses_pending'] = !empty($robot_rewards['robot_weapons_pending']) ? $robot_rewards['robot_weapons_pending'] : 0;
                        $mmrpg_robot['robot_attack_bonuses'] = !empty($robot_rewards['robot_attack']) ? $robot_rewards['robot_attack'] : 0;
                        $mmrpg_robot['robot_attack_bonuses_pending'] = !empty($robot_rewards['robot_attack_pending']) ? $robot_rewards['robot_attack_pending'] : 0;
                        $mmrpg_robot['robot_defense_bonuses'] = !empty($robot_rewards['robot_defense']) ? $robot_rewards['robot_defense'] : 0;
                        $mmrpg_robot['robot_defense_bonuses_pending'] = !empty($robot_rewards['robot_defense_pending']) ? $robot_rewards['robot_defense_pending'] : 0;
                        $mmrpg_robot['robot_speed_bonuses'] = !empty($robot_rewards['robot_speed']) ? $robot_rewards['robot_speed'] : 0;
                        $mmrpg_robot['robot_speed_bonuses_pending'] = !empty($robot_rewards['robot_speed_pending']) ? $robot_rewards['robot_speed_pending'] : 0;

                        $mmrpg_robot['robot_player'] = $player_token;
                        $mmrpg_robot['robot_player_original'] = !empty($robot_settings['original_player']) ? $robot_settings['original_player'] : '';

                        $mmrpg_robot['robot_abilities_unlocked'] = !empty($robot_rewards['robot_abilities']) ? array_keys($robot_rewards['robot_abilities']) : array();;
                        $mmrpg_robot['robot_abilities_current'] = !empty($robot_settings['robot_abilities']) ? array_keys($robot_settings['robot_abilities']) : array();

                        $robot_flags = array();
                        if (!empty($robot_settings['flags'])){ $robot_flags = array_merge($robot_flags, $robot_settings['flags']); }
                        if (!empty($robot_rewards['flags'])){ $robot_flags = array_merge($robot_flags, $robot_rewards['flags']); }
                        $mmrpg_robot['robot_flags'] = !empty($robot_flags) ? json_encode($robot_flags) : '';

                        $robot_counters = array();
                        if (!empty($robot_settings['counters'])){ $robot_counters = array_merge($robot_counters, $robot_settings['counters']); }
                        if (!empty($robot_rewards['counters'])){ $robot_counters = array_merge($robot_counters, $robot_rewards['counters']); }
                        $mmrpg_robot['robot_counters'] = !empty($robot_counters) ? json_encode($robot_counters) : '';

                        $robot_values = array();
                        if (!empty($robot_settings['values'])){ $robot_values = array_merge($robot_values, $robot_settings['values']); }
                        if (!empty($robot_rewards['values'])){ $robot_values = array_merge($robot_values, $robot_rewards['values']); }
                        $mmrpg_robot['robot_values'] = !empty($robot_values) ? json_encode($robot_values) : '';

                        // Update save index with new robot info
                        $mmrpg_users_robots[$robot_token] = $mmrpg_robot;

                    }
                }

                // Update save index with new player info
                $mmrpg_users_players[$player_token] = $mmrpg_player;

            }
        }


        // -- INDEX ABILITY OBJECTS -- //

        // If not empty, loop through and index abilities
        if (!empty($battle_abilities)){
            foreach ($battle_abilities AS $key => $ability_token){
                if (empty($ability_token) || $ability_token == 'ability'){ continue; }
                elseif (!in_array($ability_token, $mmrpg_unlockable_ability_tokens)){ continue; }
                // Create an entry for this ability in the global unlock index
                $ability_info = array();
                $ability_info['user_id'] = $this_userid;
                $ability_info['ability_token'] = $ability_token;
                $mmrpg_users_abilities[$ability_token] = $ability_info;
            }
        }

        // If not empty, lop through players and index abilities
        if (!empty($mmrpg_users_players)){
            foreach ($mmrpg_users_players AS $player_token => $player_info){
                // Create a sub-array to hold this player's unlocked abilities
                $mmrpg_users_players_abilities[$player_token] = array();
                if (!empty($player_info['player_abilities_unlocked'])){
                    foreach ($player_info['player_abilities_unlocked'] AS $key => $ability_token){
                        if (empty($ability_token) || $ability_token == 'ability'){ continue; }
                        elseif (!in_array($ability_token, $mmrpg_unlockable_ability_tokens)){ continue; }
                        // Create an entry for this ability in the player unlock index
                        $ability_info = array();
                        $ability_info['user_id'] = $this_userid;
                        $ability_info['player_token'] = $player_token;
                        $ability_info['ability_token'] = $ability_token;
                        $mmrpg_users_players_abilities[$player_token][$ability_token] = $ability_info;
                    }
                }
                // Remove the temporary abilities array from the parent index
                unset($mmrpg_users_players[$player_token]['player_abilities_unlocked']);
            }
        }

        // If not empty, lop through robots and index abilities
        if (!empty($mmrpg_users_robots)){
            foreach ($mmrpg_users_robots AS $robot_token => $robot_info){
                // Create a sub-array to hold this robot's unlocked abilities
                $mmrpg_users_robots_abilities[$robot_token] = array();
                if (!empty($robot_info['robot_abilities_unlocked'])){
                    foreach ($robot_info['robot_abilities_unlocked'] AS $key => $ability_token){
                        if (empty($ability_token) || $ability_token == 'ability'){ continue; }
                        elseif (!in_array($ability_token, $mmrpg_unlockable_ability_tokens)){ continue; }
                        // Create an entry for this ability in the robot unlock index
                        $ability_info = array();
                        $ability_info['user_id'] = $this_userid;
                        $ability_info['robot_token'] = $robot_token;
                        $ability_info['ability_token'] = $ability_token;
                        $mmrpg_users_robots_abilities[$robot_token][$ability_token] = $ability_info;
                    }
                }
                // Create a sub-array to hold this robot's equipped abilities
                $mmrpg_users_robots_movesets[$robot_token] = array();
                if (!empty($robot_info['robot_abilities_current'])){
                    foreach ($robot_info['robot_abilities_current'] AS $key => $ability_token){
                        if (empty($ability_token) || $ability_token == 'ability'){ continue; }
                        elseif (!in_array($ability_token, $mmrpg_unlockable_ability_tokens)){ continue; }
                        // Create an entry for this ability in the robot unlock index
                        $ability_info = array();
                        $ability_info['user_id'] = $this_userid;
                        $ability_info['robot_token'] = $robot_token;
                        $ability_info['ability_token'] = $ability_token;
                        $ability_info['slot_key'] = $key;
                        $mmrpg_users_robots_movesets[$robot_token][$ability_token] = $ability_info;
                    }
                }
                // Remove the temporary abilities arrays from the parent index
                unset($mmrpg_users_robots[$robot_token]['robot_abilities_unlocked']);
                unset($mmrpg_users_robots[$robot_token]['robot_abilities_current']);
            }
        }


        // -- INDEX ITEM OBJECTS -- //

        // If not empty, loop through and index items
        if (!empty($battle_items)){
            foreach ($battle_items AS $item_token => $item_quantity){
                if (empty($item_token)){ continue; }
                // Create an entry for this item in the global unlock index
                $item_info = array();
                $item_info['user_id'] = $this_userid;
                $item_info['item_token'] = $item_token;
                $item_info['item_quantity'] = $item_quantity;
                $mmrpg_users_items[$item_token] = $item_info;
            }
        }


        // -- INDEX FIELD OBJECTS -- //

        // If not empty, loop through and index fields
        if (!empty($battle_fields)){
            foreach ($battle_fields AS $key => $field_token){
                if (empty($field_token)){ continue; }
                // Create an entry for this field in the global unlock index
                $field_info = array();
                $field_info['user_id'] = $this_userid;
                $field_info['field_token'] = $field_token;
                $mmrpg_users_fields[$field_token] = $field_info;
            }
        }

        // If not empty, loop through and index omega factors
        if (!empty($player_omega)){
            foreach ($player_omega AS $player_token => $omega_factors){
                if (empty($omega_factors)){ continue; }
                $mmrpg_users_players_omega[$player_token] = array();
                foreach ($omega_factors AS $omega_key => $omega_factor){
                    // Create an entry for this field in the global omega index
                    $omega_info = array();
                    $omega_info['user_id'] = $this_userid;
                    $omega_info['player_token'] = $player_token;
                    $omega_info['field_token'] = $omega_factor['field'];
                    $omega_info['robot_token'] = $omega_factor['robot'];
                    $omega_info['type_token'] = $omega_factor['type'];
                    $omega_info['slot_key'] = $omega_key;
                    $mmrpg_users_players_omega[$player_token][] = $omega_info;
                }
            }
        }


        // -- INDEX STAR OBJECTS -- //

        // If not empty, loop through and index stars
        if (!empty($battle_stars)){
            foreach ($battle_stars AS $star_token => $star_info){
                if (empty($star_token)){ continue; }
                elseif (empty($star_info['star_kind'])){ continue; }
                // Create an entry for this star in the global unlock index
                $star_info['user_id'] = $this_userid;
                $star_info['star_token'] = $star_token;
                $mmrpg_users_stars[$star_token] = $star_info;
            }
        }


        // -- INDEX ROBOT ALTS -- //

        // If not empty, loop through and index robot alts
        if (!empty($robot_alts)){
            foreach ($robot_alts AS $robot_token => $alts_unlocked){
                if (empty($robot_token)){ continue; }
                // Create a sub-array to hold this robot's unlocked alts
                $mmrpg_users_robots_alts[$robot_token] = array();
                foreach ($alts_unlocked AS $alt_key => $alt_token){
                    if (empty($alt_token)){ continue; }
                    // Create an entry for this alt in the robot unlock index
                    $alt_info = array();
                    $alt_info['user_id'] = $this_userid;
                    $alt_info['robot_token'] = $robot_token;
                    $alt_info['alt_token'] = $alt_token;
                    $mmrpg_users_robots_alts[$robot_token][$alt_token] = $alt_info;
                }
            }
        }


        // -- INDEX ROBOT DATABASE -- //

        // If not empty, loop through and index robots
        if (!empty($robot_database)){
            foreach ($robot_database AS $robot_token => $robot_info){
                if (empty($robot_token) || $robot_token == 'robot'){ continue; }
                elseif (!in_array($robot_token, $mmrpg_valid_robot_tokens)){ continue; }
                // Create an entry for this robot in the global unlock index
                $robot_info = array_merge(array('user_id' => $this_userid, 'robot_token' => $robot_token), $robot_info);
                $mmrpg_users_robots_records[$robot_token] = $robot_info;
            }
        }


        // -- RUN PRE-DATABASE FIELD MODS -- //

        // Collapse any nested player arrays into csv strings
        foreach ($mmrpg_users_players AS $player_token => $player_info){
            foreach ($player_info AS $field_name => $field_value){
                if (is_array($field_value)){
                    $mmrpg_users_players[$player_token][$field_name] = implode(',', $field_value);
                }
            }
        }

        // Collapse any nested robot arrays into csv strings
        foreach ($mmrpg_users_robots AS $robot_token => $robot_info){
            foreach ($robot_info AS $field_name => $field_value){
                if (is_array($field_value)){
                    $mmrpg_users_robots[$robot_token][$field_name] = implode(',', $field_value);
                }
            }
        }


        // -- SAVE OBJECTS TO DATABASE -- //

        /*
        if ($echo){ echo('$mmrpg_users_players = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_players, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_abilities = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_abilities, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_players_abilities = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_players_abilities, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_abilities = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_abilities, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_movesets = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_movesets, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_items = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_items, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_stars = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_stars, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_alts = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_alts, '', true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_records = '.PHP_EOL.PHP_EOL.cms_core::array_to_paths($mmrpg_users_robots_records, '', true).'<hr />'.PHP_EOL); }
        */

        /*
        if ($echo){ echo('$mmrpg_users_players = '.print_r($mmrpg_users_players, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots = '.print_r($mmrpg_users_robots, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_abilities = '.print_r($mmrpg_users_abilities, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_players_abilities = '.print_r($mmrpg_users_players_abilities, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_abilities = '.print_r($mmrpg_users_robots_abilities, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_movesets = '.print_r($mmrpg_users_robots_movesets, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_items = '.print_r($mmrpg_users_items, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_stars = '.print_r($mmrpg_users_stars, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_alts = '.print_r($mmrpg_users_robots_alts, true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_records = '.print_r($mmrpg_users_robots_records, true).'<hr />'.PHP_EOL); }
        */

        if ($echo){ echo('$mmrpg_users_players('.count($mmrpg_users_players).') = '.print_r(array_keys($mmrpg_users_players), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots('.count($mmrpg_users_robots).') = '.print_r(array_keys($mmrpg_users_robots), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_abilities('.count($mmrpg_users_abilities).') = '.print_r(array_keys($mmrpg_users_abilities), true).'<hr />'.PHP_EOL); }
        //if ($echo){ echo('$mmrpg_users_players_abilities('.array_sum(array_map('count', $mmrpg_users_players_abilities)).') = '.print_r(array_keys($mmrpg_users_players_abilities), true).'<hr />'.PHP_EOL); }
        //if ($echo){ echo('$mmrpg_users_robots_abilities('.array_sum(array_map('count', $mmrpg_users_robots_abilities)).') = '.print_r(array_keys($mmrpg_users_robots_abilities), true).'<hr />'.PHP_EOL); }
        //if ($echo){ echo('$mmrpg_users_robots_movesets('.array_sum(array_map('count', $mmrpg_users_robots_movesets)).') = '.print_r(array_keys($mmrpg_users_robots_movesets), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_items('.count($mmrpg_users_items).') = '.print_r(array_values(array_map(function($a){ return implode('/', array_values($a)); }, $mmrpg_users_items)), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_fields('.count($mmrpg_users_fields).') = '.print_r(array_keys($mmrpg_users_fields), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_players_omega('.count($mmrpg_users_players_omega).') = '.print_r(array_map(function($a){ return implode('/', array_column($a, 'field_token')); }, $mmrpg_users_players_omega), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_stars('.count($mmrpg_users_stars).') = '.print_r(array_keys($mmrpg_users_stars), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_alts('.array_sum(array_map('count', $mmrpg_users_robots_alts)).') = '.print_r(array_map(function($a){ return implode('/', array_keys($a)); }, $mmrpg_users_robots_alts), true).'<hr />'.PHP_EOL); }
        if ($echo){ echo('$mmrpg_users_robots_records('.count($mmrpg_users_robots_records).') = '.print_r(array_map(function($a){ return implode('/', array_values($a)); }, $mmrpg_users_robots_records), true).'<hr />'.PHP_EOL); }


        // Loop through players and update/insert them in the database
        $db_existing_players = $db->get_array_list("SELECT player_token FROM mmrpg_users_players WHERE user_id = {$this_userid};", 'player_token');
        $db_existing_players = !empty($db_existing_players) ? array_column($db_existing_players, 'player_token') : array();
        foreach ($mmrpg_users_players AS $player_token => $player_info){
            if (in_array($player_token, $db_existing_players)){
                $db->update('mmrpg_users_players', $player_info, array('user_id' => $this_userid, 'player_token' => $player_token));
            } else {
                $db->insert('mmrpg_users_players', $player_info);
            }
        }

        // Loop through robots and update/insert them in the database
        $db_existing_robots = $db->get_array_list("SELECT robot_token FROM mmrpg_users_robots WHERE user_id = {$this_userid};", 'robot_token');
        $db_existing_robots = !empty($db_existing_robots) ? array_column($db_existing_robots, 'robot_token') : array();
        foreach ($mmrpg_users_robots AS $robot_token => $robot_info){
            if (in_array($robot_token, $db_existing_robots)){
                $db->update('mmrpg_users_robots', $robot_info, array('user_id' => $this_userid, 'robot_token' => $robot_token));
            } else {
                $db->insert('mmrpg_users_robots', $robot_info);
            }
        }

        // Loop through global abilities and update/insert them in the database
        $db_existing_abilities = $db->get_array_list("SELECT ability_token FROM mmrpg_users_abilities WHERE user_id = {$this_userid};", 'ability_token');
        $db_existing_abilities = !empty($db_existing_abilities) ? array_column($db_existing_abilities, 'ability_token') : array();
        foreach ($mmrpg_users_abilities AS $ability_token => $ability_info){
            if (!in_array($ability_token, $db_existing_abilities)){
                $db->insert('mmrpg_users_abilities', $ability_info);
            }
        }

        // Loop through fields and update/insert them in the database
        $db_existing_fields = $db->get_array_list("SELECT field_token FROM mmrpg_users_fields WHERE user_id = {$this_userid};", 'field_token');
        $db_existing_fields = !empty($db_existing_fields) ? array_column($db_existing_fields, 'field_token') : array();
        foreach ($mmrpg_users_fields AS $field_token => $field_info){
            if (in_array($field_token, $db_existing_fields)){
                $db->update('mmrpg_users_fields', $field_info, array('user_id' => $this_userid, 'field_token' => $field_token));
            } else {
                $db->insert('mmrpg_users_fields', $field_info);
            }
        }

        // Loop through player abilities and update/insert them in the database
        foreach ($mmrpg_users_players_abilities AS $player_token => $player_abilities){
            $db_existing_players_abilities = $db->get_array_list("SELECT ability_token FROM mmrpg_users_players_abilities WHERE user_id = {$this_userid} AND player_token = '{$player_token}';", 'ability_token');
            $db_existing_players_abilities = !empty($db_existing_players_abilities) ? array_column($db_existing_players_abilities, 'ability_token') : array();
            foreach ($player_abilities AS $ability_token => $ability_info){
                if (!in_array($ability_token, $db_existing_players_abilities)){
                    $db->insert('mmrpg_users_players_abilities', $ability_info);
                }
            }
        }

        // Loop through player omega and update/insert them in the database
        foreach ($mmrpg_users_players_omega AS $player_token => $player_omega){
            $db->query("DELETE FROM mmrpg_users_players_omega WHERE user_id = {$this_userid} AND player_token = '{$player_token}';");
            foreach ($player_omega AS $omega_key => $omega_factor){
                $db->insert('mmrpg_users_players_omega', $omega_factor);
            }
        }

        // Loop through robot abilities and update/insert them in the database
        foreach ($mmrpg_users_robots_abilities AS $robot_token => $robot_abilities){
            $db_existing_robots_abilities = $db->get_array_list("SELECT ability_token FROM mmrpg_users_robots_abilities WHERE user_id = {$this_userid} AND robot_token = '{$robot_token}';", 'ability_token');
            $db_existing_robots_abilities = !empty($db_existing_robots_abilities) ? array_column($db_existing_robots_abilities, 'ability_token') : array();
            foreach ($robot_abilities AS $ability_token => $ability_info){
                if (!in_array($ability_token, $db_existing_robots_abilities)){
                    $db->insert('mmrpg_users_robots_abilities', $ability_info);
                }
            }
        }

        // Loop through equipped robot abilities and update/insert them in the database
        foreach ($mmrpg_users_robots_movesets AS $robot_token => $robot_abilities){
            $db->query("DELETE FROM mmrpg_users_robots_movesets WHERE user_id = {$this_userid} AND robot_token = '{$robot_token}';");
            foreach ($robot_abilities AS $ability_token => $ability_info){
                $db->insert('mmrpg_users_robots_movesets', $ability_info);
            }
        }

        // Loop through robot alts and update/insert them in the database
        foreach ($mmrpg_users_robots_alts AS $robot_token => $robot_alts){
            $db_existing_robots_alts = $db->get_array_list("SELECT alt_token FROM mmrpg_users_robots_alts WHERE user_id = {$this_userid} AND robot_token = '{$robot_token}';", 'alt_token');
            $db_existing_robots_alts = !empty($db_existing_robots_alts) ? array_column($db_existing_robots_alts, 'alt_token') : array();
            foreach ($robot_alts AS $alt_token => $alt_info){
                if (!in_array($alt_token, $db_existing_robots_alts)){
                    $db->insert('mmrpg_users_robots_alts', $alt_info);
                }
            }
        }

        // Loop through robots and update/insert them in the database
        $db_existing_robots = $db->get_array_list("SELECT robot_token FROM mmrpg_users_robots_records WHERE user_id = {$this_userid};", 'robot_token');
        $db_existing_robots = !empty($db_existing_robots) ? array_column($db_existing_robots, 'robot_token') : array();
        foreach ($mmrpg_users_robots_records AS $robot_token => $robot_info){
            if (in_array($robot_token, $db_existing_robots)){
                $db->update('mmrpg_users_robots_records', $robot_info, array('user_id' => $this_userid, 'robot_token' => $robot_token));
            } else {
                $db->insert('mmrpg_users_robots_records', $robot_info);
            }
        }

        // Loop through items and update/insert them in the database
        $db_existing_items = $db->get_array_list("SELECT item_token FROM mmrpg_users_items WHERE user_id = {$this_userid};", 'item_token');
        $db_existing_items = !empty($db_existing_items) ? array_column($db_existing_items, 'item_token') : array();
        foreach ($mmrpg_users_items AS $item_token => $item_info){
            if (in_array($item_token, $db_existing_items)){
                $db->update('mmrpg_users_items', $item_info, array('user_id' => $this_userid, 'item_token' => $item_token));
            } else {
                $db->insert('mmrpg_users_items', $item_info);
            }
        }

        // Delete existing stars for this user from the database, then loop through and re-insert current ones
        $db->query("DELETE FROM mmrpg_users_stars WHERE user_id = {$this_userid};");
        foreach ($mmrpg_users_stars AS $star_token => $star_info){
            $db->insert('mmrpg_users_stars', $star_info);
        }

        // Create index arrays for all players and robots to save
        if ($echo && defined('MMRPG_ADMIN_AJAX_REQUEST')){
            global $this_ajax_request_feedback;
            $this_ajax_request_feedback .= '$mmrpg_users_abilities('.count($mmrpg_users_abilities).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_players('.count($mmrpg_users_players).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_players_abilities('.count($mmrpg_users_players_abilities).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots('.count($mmrpg_users_robots).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_abilities('.count($mmrpg_users_robots_abilities).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_movesets('.count($mmrpg_users_robots_movesets).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_alts('.count($mmrpg_users_robots_alts).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_robots_records('.count($mmrpg_users_robots_records).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_items('.count($mmrpg_users_items).')'.PHP_EOL;
            $this_ajax_request_feedback .= '$mmrpg_users_stars('.count($mmrpg_users_stars).')'.PHP_EOL;
        }

        //exit();

    }


}