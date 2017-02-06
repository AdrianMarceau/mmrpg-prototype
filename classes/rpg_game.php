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
        elseif ($_SESSION[$session_token]['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID){ return true; } // User ID is guest, so true
        else { return false; }  // Demo flag doesn't exist, must be logged in
    }


    // Define a function for checking if we're in user mode
    public static function is_user(){
        // If we're not in demo mode, we must be user mode
        return !self::is_demo() ? true : false;
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
    public static function unlock_player($player_info, $unlock_robots = true, $unlock_abilities = true){
        // Reference the global variables
        global $mmrpg_index, $db;

        //$GAME_SESSION = &$_SESSION[self::session_token()];
        $session_token = self::session_token();

        // Define a reference to the game's session flag variable
        if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
        $temp_game_flags = &$_SESSION[$session_token]['flags'];

        // If the player token does not exist, return false
        if (!isset($player_info['player_token'])){ return false; }
        // If this player does not exist in the global index, return false
        if (!isset($mmrpg_index['players'][$player_info['player_token']])){ return false; }
        // Collect the player info from the index
        $player_info = array_replace($mmrpg_index['players'][$player_info['player_token']], $player_info);
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
            $temp_robots_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
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
            // Collect the ability index for calculation purposes
            $this_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
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
    public static function robots_unlocked($player_token = ''){
        // Define the game session helper var
        $session_token = self::session_token();
        if (!empty($player_token)){
            // Check if this battle has been completed and return true is it was
            return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots']) : 0;
        } else {
            $robot_counter = 0;
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
                $robot_counter += isset($player_info['player_robots']) ? count($player_info['player_robots']) : 0;
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
            foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $player_token => $player_info){
                if (!empty($_SESSION[$session_token]['values']['battle_abilities'])){
                    foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $ability_token => $ability_info){
                        if (!empty($ability_token) && !empty($ability_info) && !in_array($ability_token, $unlocked_abilities_tokens)){
                            $unlocked_abilities_tokens[] = $ability_token;
                        }
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
        $_SESSION[$session_token]['values']['battle_abilities'][$this_ability_token] = $this_reward;

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
        if (defined('MMRPG_REMOTE_GAME')){ return 'REMOTE_GAME_'.MMRPG_REMOTE_GAME; }
        else { return 'GAME'; }
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
    public static function load_session($this_save_filepath){
        return mmrpg_load_game_session($this_save_filepath);
    }


}