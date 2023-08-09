<?
/**
 * Mega Man RPG Game
 * <p>The global game for the Mega Man RPG Prototype.</p>
 */
class rpg_game {

    // Define global class variables
    public static $index = array();
    public static $flags = array();


    /**
     * Create a new RPG game object.
     * This is a wrapper class for static functions,
     * so object initialization is not necessary.
     */
    public function __construct(){ }



    // -- COMMON FLAG FUNCTIONS -- //


    // Define some quick static flag functions for optimizing purposes
    public static function has_flag($flag_name){
        if (isset(self::$flags[$flag_name])){ return true; }
        else { return false; }
    }
    public static function get_flag($flag_name){
        if (isset(self::$flags[$flag_name])){ return self::$flags[$flag_name]; }
        else { return null; }
    }
    public static function set_flag($flag_name, $flag_value){
        self::$flags[$flag_name] = $flag_value;
    }
    public static function unset_flag($flag_name){
        unset(self::$flags[$flag_name]);
    }

    // Define some quick functions for testing if onload triggered or setting the value
    public static function onload_triggered($object_type, $object_id, $trigger = false){
        $flag_name = 'onload_triggered/'.$object_type.'/'.$object_id;
        if ($trigger === true){
            self::set_flag($flag_name, true);
        } else {
            return self::has_flag($flag_name);
        }
    }


    // -- COMMON ID FUNCTIONS -- //

    public static function unique_field_id($battle_id, $field_index_id){
        return $battle_id.'x'.$field_index_id;
    }
    public static function unique_player_id($user_id, $player_index_id){
        return $user_id.'x'.$player_index_id;
    }
    public static function unique_robot_id($player_id, $robot_index_id, $key = 0){
        return $player_id.'x'.$robot_index_id.'x'.$key;
    }
    public static function unique_ability_id($player_or_robot_id, $ability_index_id){
        return $player_or_robot_id.'x'.$ability_index_id;
    }
    public static function unique_item_id($player_or_robot_id, $item_index_id){
        return $player_or_robot_id.'x'.$item_index_id;
    }
    public static function unique_skill_id($player_or_robot_id, $skill_index_id){
        return $player_or_robot_id.'x'.$skill_index_id;
    }

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
     * Create or retrive a field object from the session
     * @param array $this_fieldinfo
     * @return rpg_field
     */
    public static function get_field($this_battle, $this_fieldinfo){

        // If the field index has not been created, do so
        if (!isset(self::$index['fields'])){ self::$index['fields'] = array(); }

        // If the field was not provided at all, we can assume a stub
        if (empty($this_fieldinfo)){ $this_fieldinfo = array('field_token' => 'field'); }

        // Check if a field ID has been defined
        if (isset($this_fieldinfo['field_id'])){
            $field_id = $this_fieldinfo['field_id'];
        }
        // Otherwise if only a field token was defined
        elseif (isset($this_fieldinfo['field_token'])){
            $field_id = 0;
            $field_token = $this_fieldinfo['field_token'];
            foreach (self::$index['fields'] AS $field){
                if ($field_token == $field->field_token){
                    $field_id = $field->field_id;
                    break;
                }
            }
        }

        // If this field has already been created, retrieve it
        if (!empty($field_id) && !empty(self::$index['fields'][$field_id])){

            // Collect the field from the index and return
            $this_field = self::$index['fields'][$field_id];
            $this_field->trigger_onload();

        }
        // Otherwise create a new field object in the index
        else {

            // Create and return the field object
            $this_field = new rpg_field($this_battle, $this_fieldinfo);
            self::$index['fields'][$this_field->field_id] = $this_field;

        }

        // Return the collect field object
        $this_field->update_session();
        return $this_field;

    }

    /**
     * Create or retrive a player object from the session
     * @param array $this_playerinfo
     * @return rpg_player
     */
    public static function get_player($this_battle, $this_playerinfo, $trigger_onload = true){

        // If the player index has not been created, do so
        if (!isset(self::$index['players'])){ self::$index['players'] = array(); }

        // If the player was not provided at all, we can assume a stub
        if (empty($this_playerinfo)){ $this_playerinfo = array('player_token' => 'player'); }

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
            if ($trigger_onload){ $this_player->trigger_onload(); }

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
     * Retrieve a player object from the session by a known ID
     * @param integer $player_id
     * @return rpg_player
     */
    public static function get_player_by_id($player_id){
        if (isset(self::$index['players'][$player_id])){ return self::$index['players'][$player_id]; }
        else { return false; }
    }

    /**
     * Create or retrive a robot object from the session
     * @param array $this_robotinfo
     * @return rpg_robot
     */
    public static function get_robot($this_battle, $this_player, $this_robotinfo, $trigger_onload = true){

        // If the robot index has not been created, do so
        if (!isset(self::$index['robots'])){ self::$index['robots'] = array(); }

        // If the robot was not provided at all, we can assume a stub
        if (empty($this_robotinfo)){ $this_robotinfo = array('robot_token' => 'robot'); }

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
            if ($trigger_onload){ $this_robot->trigger_onload(); }

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
     * Retrieve a robot object from the session by a known ID
     * @param integer $robot_id
     * @return rpg_robot
     */
    public static function get_robot_by_id($robot_id){
        if (isset(self::$index['robots'][$robot_id])){ return self::$index['robots'][$robot_id]; }
        else { return false; }
    }

    /**
     * Create or retrive a ability object from the session
     * @param array $this_abilityinfo
     * @return rpg_ability
     */
    public static function get_ability($this_battle, $this_player, $this_robot, $this_abilityinfo, $trigger_onload = true){

        // If the ability index has not been created, do so
        if (!isset(self::$index['abilities'])){ self::$index['abilities'] = array(); }

        // If the ability was not provided at all, we can assume a stub
        if (empty($this_abilityinfo)){ $this_abilityinfo = array('ability_token' => 'ability'); }

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
            if ($trigger_onload){ $this_ability->trigger_onload(); }

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
     * Retrieve an ability object from the session by a known ID
     * @param integer $ability_id
     * @return rpg_ability
     */
    public static function get_ability_by_id($ability_id){
        if (isset(self::$index['abilities'][$ability_id])){ return self::$index['abilities'][$ability_id]; }
        else { return false; }
    }

    /**
     * Create or retrive a item object from the session
     * @param array $this_iteminfo
     * @return rpg_item
     */
    public static function get_item($this_battle, $this_player, $this_robot, $this_iteminfo, $trigger_onload = true){

        // If the item index has not been created, do so
        if (!isset(self::$index['items'])){ self::$index['items'] = array(); }

        // If the item was not provided at all, we can assume a stub
        if (empty($this_iteminfo)){ $this_iteminfo = array('item_token' => 'item'); }

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
            if ($trigger_onload){ $this_item->trigger_onload(); }

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
     * Retrieve an item object from the session by a known ID
     * @param integer $item_id
     * @return rpg_item
     */
    public static function get_item_by_id($item_id){
        if (isset(self::$index['items'][$item_id])){ return self::$index['items'][$item_id]; }
        else { return false; }
    }

    /**
     * Create or retrive a skill object from the session
     * @param array $this_skillinfo
     * @return rpg_skill
     */
    public static function get_skill($this_battle, $this_player, $this_robot, $this_skillinfo, $trigger_onload = true){

        // If the skill index has not been created, do so
        if (!isset(self::$index['skills'])){ self::$index['skills'] = array(); }

        // If the skill was not provided at all, we can assume a stub
        if (empty($this_skillinfo)){ $this_skillinfo = array('skill_token' => 'skill'); }

        // Check if a skill ID has been defined
        if (isset($this_skillinfo['skill_id'])){
            $skill_id = $this_skillinfo['skill_id'];
        }
        // Otherwise if only a skill token was defined
        elseif (isset($this_skillinfo['skill_token'])){
            $skill_id = 0;
            $skill_token = $this_skillinfo['skill_token'];
            foreach (self::$index['skills'] AS $skill){
                if ($skill_token == $skill->skill_token
                    && $this_robot->robot_id == $skill->robot_id
                    && $this_player->player_id == $skill->player_id){
                    $skill_id = $skill->skill_id;
                    break;
                }
            }
        }

        // If this skill has already been created, retrieve it
        if (!empty($skill_id) && !empty(self::$index['skills'][$skill_id])){

            // Collect the skill from the index and return
            $this_skill = self::$index['skills'][$skill_id];
            if ($trigger_onload){ $this_skill->trigger_onload(); }

        }
        // Otherwise create a new skill object in the index
        else {

            // Create and return the skill object
            $this_skill = new rpg_skill($this_battle, $this_player, $this_robot, $this_skillinfo);
            self::$index['skills'][$this_skill->skill_id] = $this_skill;

        }

        // Return the collect skill object
        $this_skill->update_session();
        return $this_skill;

    }

    /**
     * Retrieve an skill object from the session by a known ID
     * @param integer $skill_id
     * @return rpg_skill
     */
    public static function get_skill_by_id($skill_id){
        if (isset(self::$index['skills'][$skill_id])){ return self::$index['skills'][$skill_id]; }
        else { return false; }
    }

    /**
     * Create or retrive a proto object from the session
     * @param array $this_objectinfo
     * @return rpg_object
     */
    public static function get_proto_object($this_battle, $this_player, $this_robot, $this_objectinfo){

        // If the object index has not been created, do so
        if (!isset(self::$index['objects'])){ self::$index['objects'] = array(); }

        // Check if a object ID has been defined
        if (isset($this_objectinfo['object_id'])){
            $object_id = $this_objectinfo['object_id'];
        }
        // Otherwise if only a object token was defined
        elseif (isset($this_objectinfo['object_token'])){
            $object_id = 0;
            $object_token = $this_objectinfo['object_token'];
            foreach (self::$index['objects'] AS $object){
                if ($object_token == $object->object_token
                    && $this_robot->robot_id == $object->robot_id
                    && $this_player->player_id == $object->player_id){
                    $object_id = $object->object_id;
                    break;
                }
            }
        }

        // If this object has already been created, retrieve it
        if (!empty($object_id) && !empty(self::$index['objects'][$object_id])){

            // Collect the object from the index and return
            $this_object = self::$index['objects'][$object_id];

        }
        // Otherwise create a new object object in the index
        else {

            // Create and return the object object
            $this_object = new rpg_object();
            $this_object->proto_construct($this_battle, $this_player, $this_robot, $this_objectinfo);
            self::$index['objects'][$this_object->object_id] = $this_object;

        }

        // Return the collect object object
        $this_object->proto_update_session();
        return $this_object;

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
            if ($this_robot->robot_token == 'robot'){ continue; }
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
            if ($this_robot->robot_token == 'robot'){ continue; }
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

    /**
     * Create a new generic options object to be used as in object functions
     * @return stdClass
     */
    public static function new_options_object(){
        $options = new stdClass;
        self::reset_options_object($options);
        return $options;
    }

    /**
     * Reset known settings in the generic options object used in for object functions
     * @return stdClass
     */
    public static function reset_options_object($options){
        $options->return_early = false;
        $options->return_value = null;
    }




    // -- MODE FUNCTIONS -- //

    // Define a function for checking if we're in demo mode
    public static function is_demo(){
        return rpg_user::is_guest() ? true : false;
    }


    // Define a function for checking if we're in user mode
    public static function is_user(){
        return !rpg_user::is_guest() ? true : false;
    }


    // Define a function for checking if we're in user mode
    public static function get_userid(){
        // If we're not in demo mode, we must be user mode
        $session_token = self::session_token();
        return !empty($_SESSION[$session_token]['USER']['userid']) ? $_SESSION[$session_token]['USER']['userid'] : MMRPG_SETTINGS_GUEST_ID;
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
        global $db;
        global $mmrpg_index_players;
        if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

        //$GAME_SESSION = &$_SESSION[self::session_token()];
        $session_token = self::session_token();

        // Define a reference to the game's session flag variable
        if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
        $temp_game_flags = &$_SESSION[$session_token]['flags'];

        // If the player token does not exist, return false
        if (!isset($player_info['player_token'])){ return false; }
        // If this player does not exist in the global index, return false
        if (!isset($mmrpg_index_players[$player_info['player_token']])){ return false; }
        // Collect the player info from the index
        $player_info = array_replace($mmrpg_index_players[$player_info['player_token']], $player_info);
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
        return mmrpg_game_unlock_robot($player_info, $robot_info, $unlock_abilities, $events_create);
    }


    // Define a function for updating a player setting for use in battle
    public static function robot_setting($player_info, $robot_info, $setting_token, $setting_value){
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
        global $mmrpg_index_players;
        if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }
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
        return mmrpg_game_unlock_ability($player_info, $robot_info, $ability_info, $events_create);
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
                if (preg_match('/-core$/i', $token)){ $temp_counter += $quantity; }
            }
        }
        return $temp_counter;
    }

    // Define a function for checking how many screws have been unlocked by a player
    public static function screws_unlocked($size = ''){
        // If neither screw type has ever been created, return a hard false
        $session_token = self::session_token();
        if (!isset($_SESSION[$session_token]['values']['battle_items']['small-screw'])
            && !isset($_SESSION[$session_token]['values']['battle_items']['large-screw'])){
            return false;
        }
        // Define the game session helper var
        $temp_counter = 0;
        if (isset($_SESSION[$session_token]['values']['battle_items']['small-screw'])){ $temp_counter += $_SESSION[$session_token]['values']['battle_items']['small-screw']; }
        if (isset($_SESSION[$session_token]['values']['battle_items']['large-screw'])){ $temp_counter += $_SESSION[$session_token]['values']['battle_items']['large-screw']; }
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

            // Check to see if a Rogue Star is currently in orbit
            $this_rogue_star = mmrpg_prototype_get_current_rogue_star();
            if (!empty($this_rogue_star)){
                $star_type = $this_rogue_star['star_type'];
                $star_power = $this_rogue_star['star_power'];
                if (!isset($this_star_force[$star_type])){ $this_star_force[$star_type] = 0; }
                $this_star_force[$star_type] += $star_power;
            }

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


    // -- SOURCE/GAME INDEX FUNCTIONS -- //

    // Define a function for getting a game name given a source code for that game
    public static function get_source_index(){
        global $db;
        static $source_index;
        if (empty($source_index)){
            $source_index = $db->get_array_list("SELECT
                source_token,
                source_kind,
                source_name,
                source_name_aka,
                source_series,
                source_subseries,
                source_systems,
                source_year,
                source_flag_published,
                source_flag_hidden,
                source_flag_canon,
                source_flag_fanon,
                source_order
                FROM mmrpg_index_sources
                WHERE source_flag_published = 1
                ORDER BY source_order
                ;", 'source_token');
        }
        return $source_index;
    }

    // Define a function for getting a game name given a source code for that game
    public static function get_source_info($source_token){
        global $db;
        $source_index = self::get_source_index();
        if (!isset($source_index[$source_token])){ return false; }
        else { return $source_index[$source_token]; }
    }

    // Define a function for getting a game name given a source code for that game
    public static function get_source_name($source_token, $allow_html = true){
        global $db;
        $source_index = self::get_source_index();
        if (!isset($source_index[$source_token])){ return 'Unknown'; }
        $source_info = $source_index[$source_token];
        $source_name = !empty($source_info['source_name']) ? $source_info['source_name'] : $source_info['source_name_aka'];
        if ($allow_html){
            $title = $source_name;
            if (!empty($source_info['source_name_aka'])
                && $source_name !== $source_info['source_name_aka']){
                $title .= ' / '.$source_info['source_name_aka'];
            }
            $title .= ' ('.$source_info['source_systems'].')';
            $source_name = '<span data-click-tooltip="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8', true).'">'.$source_name.'</span>';
        }
        return $source_name;
    }

    // Define a function for getting a game name given a source code for that game
    public static function get_source_systems($source_token, $allow_html = true){
        global $db;
        $source_index = self::get_source_index();
        if (!isset($source_index[$source_token])){ return 'Unknown'; }
        $source_info = $source_index[$source_token];
        $source_systems = !empty($source_info['source_systems']) ? $source_info['source_systems'] : '???';
        return $source_systems;
    }


    // -- CDN INDEX FUNCTIONS -- //

    // Define a function for getting (or generating) a CDN file index for a given directory
    public static function get_cdn_index($project, $content){

        // Return false if either argument is invalid
        if (!preg_match('/^[-_a-z0-9]+$/i', $project)){ return false; }
        if (!preg_match('/^[-_a-z0-9\/]+$/i', $content)){ return false; }

        // Define the cache file name and path given everything we've learned
        $cache_file_name = 'cache.cdn_'.$project.'-'.str_replace('/', '-', $content).'.json';
        $cache_file_path = MMRPG_CONFIG_CACHE_PATH.'indexes/'.$cache_file_name;
        // Check to see if a file already exists and collect its last-modified date
        if (file_exists($cache_file_path)){ $cache_file_exists = true; $cache_file_date = date('Ymd-Hi', filemtime($cache_file_path)); }
        else { $cache_file_exists = false; $cache_file_date = '00000000-0000'; }

        // LOAD FROM CACHE if data exists and is current, otherwise continue so script can refresh and replace
        if (MMRPG_CONFIG_CACHE_INDEXES && $cache_file_exists && $cache_file_date >= MMRPG_CONFIG_CACHE_DATE){
            $cache_file_markup = file_get_contents($cache_file_path);
            $cache_file_json = json_decode($cache_file_markup, true);
            return $cache_file_json;
        }

        // Otherwise we need to collect the list and add it to the local cache
        $url = MMRPG_CONFIG_CDN_ROOTURL.$project.'/'.rtrim($content, '/').'/index';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (MMRPG_CONFIG_IS_LIVE ? true : false));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        // If results were empty, exit now
        if (empty($result)){ return false; }

        // Otherwise we can decode the data and extract the index
        $json = json_decode($result, true);
        $index = !empty($json['data']) ? $json['data'] : array();

        // Write the index to a cache file for later usage
        if (!empty($index)){
            if (file_exists($cache_file_path)){ unlink($cache_file_path); }
            $f = fopen($cache_file_path, 'w');
            fwrite($f, json_encode($index, JSON_NUMERIC_CHECK));
            fclose($f);
        }

        // Return the final index
        return $index;

    }


    // -- GAME GALLERY FUNCTIONS -- //

    // Define a function for getting (or generating) the gallery screenshots file index from the defined CDN
    public static function get_gallery_index($folder = ''){

        // Pass the work off to the dedicated CDN index function
        static $gallery_index;
        if (empty($gallery_index)){ $gallery_index = self::get_cdn_index(MMRPG_CONFIG_CDN_PROJECT, 'images/gallery'.(!empty($folder) ? '/'.$folder : '')); }
        return $gallery_index;

    }


    // -- GAME MUSIC FUNCTIONS -- //

    // Define a function for getting (or generating) the music/sound file index from the defined CDN
    public static function get_music_index($subkey = false){

        // Pass the work off to the dedicated CDN index function
        static $raw_music_index;
        if (empty($raw_music_index)){ $raw_music_index = self::get_cdn_index(MMRPG_CONFIG_CDN_PROJECT, 'music'); }
        if (!empty($subkey) && isset($raw_music_index[$subkey])){ return $raw_music_index[$subkey]; }
        return $raw_music_index;

    }

    // Define a function for getting (or generating) an index of music paths and info from the CDN
    public static function get_music_paths_index(){
        static $music_paths_index;
        if (empty($music_paths_index)){
            $music_paths_index = array();
            $raw_index = self::get_music_index('index');
            if (!empty($raw_index)){
                foreach ($raw_index AS $key => $info){
                $music_paths_index[$info['music_path']] = $info;
                }
            }
        }
        return $music_paths_index;
    }

    // Define a function for getting (or generating) an index of music/sound files from the CDN
    public static function get_music_files_index(){
        static $music_files_index;
        if (empty($music_files_index)){
            $music_files_index = self::get_music_index('files');
        }
        return $music_files_index;
    }

    // Define a function for getting (or generating) the music/sound file index from the defined CDN
    public static function get_music_info_by_path($music_path){
        $music_paths_index = self::get_music_paths_index();
        if (isset($music_paths_index[$music_path])){ return $music_paths_index[$music_path]; }
        else { return false; }
    }


    // -- SPRITE FUNCTIONS -- //

    // Define a function for conversating a symbolic sprite path into it's real content path
    public static function get_real_sprite_path($sym_path, $force_full_path = false){
        static $sym_patterns;
        if (empty($sym_patterns)){
            $sym_patterns['^images/(abilities|fields|items|players|robots)/(ability|field|item|player|robot)(_[-_a-z0-9]+)?/(.*)?$'] = 'content/$1/.$2/sprites$3/$4';
            $sym_patterns['^images/(abilities|fields|items|players|robots)_shadows/(ability|field|item|player|robot)(_[-_a-z0-9]+)?/(.*)?$'] = 'content/$1/.$2/shadows$3/$4';
            $sym_patterns['^images/(abilities|fields|items|players|robots)/([-a-z0-9]+)(_[-_a-z0-9]+)?/(.*)?$'] = 'content/$1/$2/sprites$3/$4';
            $sym_patterns['^images/(abilities|fields|items|players|robots)_shadows/([-a-z0-9]+)(_[-_a-z0-9]+)?/(.*)?$'] = 'content/$1/$2/shadows$3/$4';
        }
        $had_full_path = strstr($sym_path, MMRPG_CONFIG_ROOTDIR) ? true : false;
        $real_path = str_replace(MMRPG_CONFIG_ROOTDIR, '', $sym_path);
        foreach ($sym_patterns AS $find => $replace){
            if (preg_match('#'.$find.'#i', $real_path)){
                $real_path = preg_replace('#'.$find.'#i', $replace, $real_path);
                break;
            }
        }
        if ($had_full_path
            || $force_full_path){
            $real_path = MMRPG_CONFIG_ROOTDIR.$real_path;
        }
        return $real_path;
    }

    // Define a function for checking to see if a given sprite exists (at its real location)
    public static function sprite_exists($sym_path){

        // Get the real path given the sym link
        $real_path = self::get_real_sprite_path($sym_path);

        //echo('$sym_path = '.$sym_path.'<br />');
        //echo('$real_path = '.$real_path.'<br />');
        //exit();

        // Now that we have the real path, check to see if it exists
        return file_exists(MMRPG_CONFIG_ROOTDIR.$real_path);

    }


    // -- SOUND FUNCTIONS -- //

    // Define a function for checking to see if a sound file exists (at its real location)
    public static function sound_exists($sym_path){

        // Clean sym path and remove the rootdir (if present) for easier testing
        $sym_path = trim(str_replace(MMRPG_CONFIG_ROOTDIR, '', $sym_path), '/').'/';

        // If we're using the CDN, we need to check the index
        if (defined('MMRPG_CONFIG_CDN_ENABLED') && MMRPG_CONFIG_CDN_ENABLED === true){

            // Collect the sounds index for reference
            static $sound_files_list;
            if (empty($sound_files_list)){
                $cdn_music_index = self::get_music_index();
                $sound_files_list = !empty($cdn_music_index['files']) ? $cdn_music_index['files'] : array();
            }

            // Remove the leading "sounds/" path fragment for testing
            $rel_sym_path = preg_replace('/^sounds\//', '', $sym_path);

            // Check to see if the given path is in the CDN index
            return in_array($rel_sym_path, $sound_files_list) ? true : false;

        }
        // Otherwise we can just check for the file directory
        else {

            // Check to see if the given path is locally available
            return file_exists(MMRPG_CONFIG_ROOTDIR.$sym_path) ? true : false;

        }

    }


    // -- EVENT BANNER FUNCTIONS -- //

    // Define a function for generating an event banner given an array of parameters
    public static function generate_event_banner($banner_config = array(), $output_to_file = false){

        // Define default values for any missing parameters
        if (!isset($banner_config['background'])){ $banner_config['background'] = array(38, 38, 38); }
        if (!isset($banner_config['width'])){ $banner_config['width'] = 765; }
        if (!isset($banner_config['height'])){ $banner_config['height'] = 216; }
        if (!isset($banner_config['banner_token'])){ $banner_config['banner_token'] = ''; }
        if (!isset($banner_config['field_background'])){ $banner_config['field_background'] = ''; }
        if (!isset($banner_config['field_foreground'])){ $banner_config['field_foreground'] = ''; }
        if (!isset($banner_config['field_gridlines'])){ $banner_config['field_gridlines'] = true; }
        if (!isset($banner_config['field_sprites'])){ $banner_config['field_sprites'] = array(); }
        if (!isset($banner_config['frame_colour'])){ $banner_config['frame_colour'] = array(26, 26, 26); }
        //error_log('$banner_config = '.print_r($banner_config, true));

        // If there are field sprites, we should do some pre-processing so they're easier to work with
        if (!empty($banner_config['field_sprites'])) {
            // Loop through all the sprites and generate a "layer" value based on the "bottom" and it's percent between the min and max (only allow 8 layers total)
            $sprite_bottom_min = 30;
            $sprite_bottom_max = 115;
            $sprite_bottom_range = $sprite_bottom_max - $sprite_bottom_min;
            foreach ($banner_config['field_sprites'] AS $sprite_key => $sprite_data){
                // If the sprite doesn't have a bottom value, skip it
                if (!isset($sprite_data['bottom'])){ continue; }
                // Otherwise, calculate the layer value
                $sprite_layer = round(1 + (7 * (($sprite_data['bottom'] - $sprite_bottom_min) / $sprite_bottom_range)));
                //error_log('$sprite_data[\'bottom\'] = '.$sprite_data['bottom'].'; $sprite_layer = '.$sprite_layer);
                // If the layer value is less than 1, set it to 1
                if ($sprite_layer < 1){ $sprite_layer = 1; }
                // If the layer value is greater than 8, set it to 8
                if ($sprite_layer > 8){ $sprite_layer = 8; }
                // Add the layer value to the sprite data
                $banner_config['field_sprites'][$sprite_key]['layer'] = $sprite_layer;
            }
            // Revise the entire field sprites array so layers start at "1" rather than 3, 4, 5, etc.
            $min_layer_value = 99;
            foreach ($banner_config['field_sprites'] AS $key => $data){ if ($data['layer'] < $min_layer_value){ $min_layer_value = $data['layer']; } }
            foreach ($banner_config['field_sprites'] AS $key => $data){ $banner_config['field_sprites'][$key]['layer'] = $data['layer'] - ($min_layer_value - 1); }
            // Pre-sort the sprites by their bottom value which is a more fine-grained layer value
            usort($banner_config['field_sprites'], function($a, $b){
                $a_bottom = isset($a['bottom']) ? $a['bottom'] : 0;
                $b_bottom = isset($b['bottom']) ? $b['bottom'] : 0;
                if ($a_bottom == $b_bottom){ return 0; }
                return ($a_bottom < $b_bottom) ? 1 : -1;
            });
            // Print out some debug info in case we need it
            //error_log('$banner_config[\'field_sprites\'] = '.print_r($banner_config['field_sprites'], true));
        }

        // Create a blank image with the specified dimensions
        $banner_image = imagecreatetruecolor($banner_config['width'], $banner_config['height']);
        //error_log('-> created event banner at '.$banner_config['width'].' by '.$banner_config['height']);

        // Fill the background with the color #262626
        list($bgR, $bgG, $bgB) = $banner_config['background'];
        $bgColor = imagecolorallocate($banner_image, $bgR, $bgG, $bgB);
        imagefill($banner_image, 0, 0, $bgColor);
        //error_log('-> filled event banner with rgb('.implode(', ', $banner_config['background']).')');

        // Add the background image (blurred 1px, darkened)
        if (!empty($banner_config['field_background'])){
            $backgroundImagePath = 'images/fields/'.$banner_config['field_background'].'/battle-field_background_base.gif';
            $backgroundImagePath = rpg_game::get_real_sprite_path($backgroundImagePath, true);
            $backgroundImage = imagecreatefromgif($backgroundImagePath);
            self::convert_sprite_to_true_colour($backgroundImage);
            self::darken_event_banner_sprite($backgroundImage, 15);
            self::blur_event_banner_sprite($backgroundImage, 2);
            $backgroundImageWidth = imagesx($backgroundImage);
            $backgroundImageHeight = imagesy($backgroundImage);
            $backgroundOffsetY = 0;
            $backgroundOffsetX = ceil(($banner_config['width'] - $backgroundImageWidth) / 2);
            imagecopy($banner_image, $backgroundImage, $backgroundOffsetX, $backgroundOffsetY, 0, 0, $backgroundImageWidth, $backgroundImageHeight);
            //error_log('-> layered event banner with background '.$backgroundImagePath);
            imagedestroy($backgroundImage);
        }

        // Add the foreground image (2x zoom)
        if (!empty($banner_config['field_foreground'])){
            $foregroundImagePath = 'images/fields/'.$banner_config['field_foreground'].'/battle-field_foreground_base.png';
            $foregroundImagePath = rpg_game::get_real_sprite_path($foregroundImagePath, true);
            $foregroundImage = imagecreatefrompng($foregroundImagePath);
            $foregroundImageWidth = imagesx($foregroundImage);
            $foregroundImageHeight = imagesy($foregroundImage);
            $transparentColor = imagecolorallocatealpha($foregroundImage, 0, 0, 0, 127);
            imagefill($foregroundImage, 0, 0, $transparentColor);
            imagealphablending($foregroundImage, false);
            imagesavealpha($foregroundImage, true);
            $foregroundOffsetY = -25;
            $foregroundOffsetX = ceil(($banner_config['width'] - $foregroundImageWidth) / 2);
            //$foregroundImage = imagescale($foregroundImage, ($foregroundImageWidth * 2), -1, IMG_NEAREST_NEIGHBOUR);
            imagecopy($banner_image, $foregroundImage, $foregroundOffsetX, $foregroundOffsetY, 0, 0, $foregroundImageWidth, $foregroundImageHeight);
            //error_log('-> layered event banner with foreground '.$foregroundImagePath);
            imagedestroy($foregroundImage);
        }

        // Add the gridline image to the field
        if (!empty($banner_config['field_gridlines'])){
            $gridlinesImagePath = 'images/assets/battle-scene_gridlines-resized_event-banner.png';
            $gridlinesImagePath = rpg_game::get_real_sprite_path($gridlinesImagePath, true);
            $gridlinesImage = imagecreatefrompng($gridlinesImagePath);
            $gridlinesImageWidth = imagesx($gridlinesImage);
            $gridlinesImageHeight = imagesy($gridlinesImage);
            $gridlinesImage = rpg_game::generate_event_banner_sprite($gridlinesImagePath, $gridlinesImageWidth, 0);
            $gridlinesOffsetY = 104;
            $gridlinesOffsetX = ceil(($banner_config['width'] - $gridlinesImageWidth) / 2);
            imagecopy($banner_image, $gridlinesImage, $gridlinesOffsetX, $gridlinesOffsetY, 0, 0, $gridlinesImageWidth, $gridlinesImageHeight);
            //error_log('-> layered event banner with gridlines '.$gridlinesImagePath);
            imagedestroy($gridlinesImage);
        }

        // Loop through the sprites array and render each one
        $kind_to_path_index = array();
        $kind_to_path_index['player'] = 'players';
        $kind_to_path_index['robot'] = 'robots';
        $kind_to_path_index['ability'] = 'abilities';
        $kind_to_path_index['item'] = 'items';
        $kind_to_path_index['object'] = 'objects';
        if (!empty($banner_config['field_sprites'])) {
            // Loop through the actual sprites and add them to the banner image
            foreach ($banner_config['field_sprites'] as $sprite) {
                $spriteKind = $sprite['kind'];
                $spriteImage = $sprite['image'];
                $spriteImageSheet = $spriteImageAlt = '';
                if (strstr($sprite['image'], '_')){ list($spriteImage, $spriteImageAlt) = explode('_', $sprite['image']); }
                elseif (preg_match('/-([0-9]+)$/', $sprite['image'])){ list($spriteImage, $spriteImageAlt) = explode('_', preg_replace('/^([-_a-z0-9]+)-([0-9]+)$/', '$1_$2', $sprite['image'])); }
                if (strstr($sprite['image'], '/')){ list($spriteImage, $spriteImageSheet) = explode('/', $sprite['image']); }
                $spriteSize = $sprite['size'];
                $spriteFrame = $sprite['frame'];
                $spriteDirection = $sprite['direction'];
                $spriteLayer = isset($sprite['layer']) ? $sprite['layer'] : 1;
                $spriteLeft = isset($sprite['left']) ? $sprite['left'] : 0;
                $spriteRight = isset($sprite['right']) ? $sprite['right'] : 0;
                $spriteBottom = isset($sprite['bottom']) ? $sprite['bottom'] : 0;
                $spriteFile = 'sprite_' . $spriteDirection . '_' . $spriteSize . 'x' . $spriteSize . '.png';
                $spritePath = 'images/'.$kind_to_path_index[$spriteKind].'/';
                $spritePath .= $spriteImage;
                $spritePath .= (!empty($spriteImageAlt) ? '_'.$spriteImageAlt : '');
                $spritePath .= '/';
                $spritePath .= (!empty($spriteImageSheet) ? $spriteImageSheet.'/' : '');
                $spritePath .= $spriteFile;
                $spriteObj = rpg_game::generate_event_banner_sprite($spritePath, $spriteSize, $spriteFrame, true);
                if ($spriteLayer > 1){ self::darken_event_banner_sprite($spriteObj, (($spriteLayer - 1) * 5)); }
                $destY = $banner_config['height'] - $spriteBottom - ($spriteSize * 2);
                $destX = $sprite['float'] === 'left' ? $spriteLeft : ($banner_config['width'] - $spriteRight - ($spriteSize * 2));
                if ($spriteSize > 40){ $destX += ($sprite['float'] === 'left' ? -1 : 1) * ($spriteSize - 40); }
                imagecopy($banner_image, $spriteObj, $destX, $destY, 0, 0, ($spriteSize * 2), ($spriteSize * 2));
                imagedestroy($spriteObj);
            }
        }

        // Draw a player-coloured rectangle on the left side of the banner
        if (!empty($banner_config['frame_colour'])){

            // Check if this is a bonus event chapter
            $is_bonus_chapter = !preg_match('/^chapter-([0-9]+)-unlocked$/i', $banner_config['banner_token']) ? true : false;

            // Collect the one or two frame colours to use
            $frame_colour = $banner_config['frame_colour'];
            $frame_colour2 = isset($banner_config['frame_colour2']) ? $banner_config['frame_colour2'] : $banner_config['frame_colour'];
            $frame_colour3 = array(241, 182, 41);

            // Add a frame on the left side of the event banner
            if ($is_bonus_chapter){ self::overlay_frame_on_event_banner($banner_image, $banner_config, array('colour' => $frame_colour3, 'position' => 'left', 'width' => 320)); }
            self::overlay_frame_on_event_banner($banner_image, $banner_config, array('colour' => $frame_colour2, 'position' => 'left', 'width' => 300));
            self::overlay_frame_on_event_banner($banner_image, $banner_config, array('colour' => $frame_colour, 'position' => 'left', 'width' => 200));

            // Add a frame on the right side of the event banner
            if ($is_bonus_chapter){ self::overlay_frame_on_event_banner($banner_image, $banner_config, array('colour' => $frame_colour3, 'position' => 'right', 'width' => 320)); }
            self::overlay_frame_on_event_banner($banner_image, $banner_config, array('colour' => $frame_colour2, 'position' => 'right', 'width' => 300));
            self::overlay_frame_on_event_banner($banner_image, $banner_config, array('colour' => $frame_colour, 'position' => 'right', 'width' => 200));
        }


        // If a file path was provided, let's put it there
        if (!empty($output_to_file)){

            // Output the image to the file path
            $output_path = !strstr($output_to_file, MMRPG_CONFIG_ROOTDIR) ? MMRPG_CONFIG_ROOTDIR.$output_to_file : $output_to_file;
            imagepng($banner_image, $output_path);
            imagedestroy($banner_image);
            return;

        }
        // Otherwise we can output it directly
        else {

            // Output the image to the browser
            header('Content-type: image/png;');
            header('Content-Disposition: inline; filename="event-banner.png"');
            imagepng($banner_image);
            imagedestroy($banner_image);
            exit();

        }

    }

    // Define a function for loading and preparing a sprite image for use in an event banner
    public static function generate_event_banner_sprite($path, $size, $frame, $zoom = false){
        // Load the sprite sheet as an image object to start
        $spritePath = rpg_game::get_real_sprite_path($path, true);
        $spriteSheet = imagecreatefrompng($spritePath);
        // Create the new sprite at the requested size
        $sprite = imagecreatetruecolor($size, $size);
        // Define a transparent color
        $transparentColor = imagecolorallocatealpha($sprite, 0, 0, 0, 127);
        // Fill the new image with the transparent color
        imagefill($sprite, 0, 0, $transparentColor);
        // Turn off blending mode
        imagealphablending($sprite, false);
        // Enable saving of alpha channel
        imagesavealpha($sprite, true);
        // Copy the requested frame from the sprite sheet to the new sprite
        $frameOffset = $frame * $size;
        imagecopy($sprite, $spriteSheet, 0, 0, $frameOffset, 0, $size, $size);
        // If requested, scale the sprite up 2x with the standard zoom factor
        $zoomSize = $size * 2; // 2x zoom
        if ($zoom){ $sprite = imagescale($sprite, $zoomSize, -1, IMG_NEAREST_NEIGHBOUR); }
        // Return the generated sprite
        return $sprite;
    }

    // Define a function for converting a given sprite to true colour if it's not already
    public static function convert_sprite_to_true_colour(&$sprite){
        // Check if the sprite is already true colour
        if (imageistruecolor($sprite)){ return $sprite; }
        // Otherwise, convert it to true colour
        $true_colour_sprite = imagecreatetruecolor(imagesx($sprite), imagesy($sprite));
        imagecopy($true_colour_sprite, $sprite, 0, 0, 0, 0, imagesx($sprite), imagesy($sprite));
        $sprite = $true_colour_sprite;
        return true;
    }

    // Define a function for darkening a sprite image by a given percentage
    public static function darken_event_banner_sprite(&$sprite, $percent = 10){
        if ($percent < 0){ $percent = 0; }
        if ($percent > 100){ $percent = 100; }
        imagefilter($sprite, IMG_FILTER_BRIGHTNESS, (-1 * (255 * $percent / 100)));
        return true;
    }

    // Define a function for blurring a sprite image by a given factor
    public static function blur_event_banner_sprite(&$sprite, $factor = 1){
        for ($i = 0; $i < $factor; $i++){ imagefilter($sprite, IMG_FILTER_GAUSSIAN_BLUR); }
        return true;
    }

    // Define a function for adding a frame to an event banner with the requested config
    public static function overlay_frame_on_event_banner(&$banner_image, $banner_config, $frame_config){

        // Set default values for the frame config array if not provided
        if (!isset($frame_config['colour'])){ $frame_config['colour'] = array(0, 0, 0); }
        if (!isset($frame_config['width'])){ $frame_config['width'] = 100; }
        if (!isset($frame_config['position'])){ $frame_config['position'] = 'left'; }

        // Define the blue color (e.g., 0, 0, 255 for pure blue)
        list($fcR, $fcG, $fcB) = $frame_config['colour'];
        $blue = imagecolorallocate($banner_image, $fcR, $fcG, $fcB);

        // Define the width of the blue rectangles (e.g., 20 pixels)
        $rectangle_width = $frame_config['width'];
        $rectangle_height = $banner_config['height'] * 2;

        // Create a new image resource for the left rectangle
        $left_rectangle = imagecreatetruecolor($rectangle_width, $rectangle_height);

        // Enable transparency and set alpha blending
        imagealphablending($left_rectangle, false);
        imagesavealpha($left_rectangle, true);

        // Allocate a transparent color
        $transparent = imagecolorallocatealpha($left_rectangle, 0, 0, 0, 127);
        imagefilledrectangle($left_rectangle, 0, 0, $rectangle_width, $rectangle_height, $transparent);

        // Fill the coloured rectangle
        imagefilledrectangle($left_rectangle, 0, 0, $rectangle_width, $rectangle_height, $blue);

        // Rotate the rectangle before adding to image
        $left_rotate = 0;
        if ($frame_config['position'] === 'left'){ $left_rotate = -10; }
        elseif ($frame_config['position'] === 'right'){ $left_rotate = -10; }
        elseif (is_array($frame_config['position']) && isset($frame_config['position'][2])){ $left_rotate = $frame_config['position'][2]; }
        $rotated_width = abs(cos(deg2rad($left_rotate)) * $rectangle_width);
        if (is_array($frame_config['position'])){
            $left_offsetX = isset($frame_config['position'][0]) ? $frame_config['position'][0] : 0;
            $left_offsetY = isset($frame_config['position'][1]) ? $frame_config['position'][1] : 0;
        } else {
            $left_offsetX = 0;
            $left_offsetY = 0 - ceil($rectangle_height / 4);
            if ($frame_config['position'] === 'left'){
                $left_offsetX -= ceil($banner_config['width'] * 0.045);
                $left_offsetX -= ceil($rectangle_width * 0.9);
            } elseif ($frame_config['position'] === 'right'){
                $left_offsetX += ceil($banner_config['width'] * 0.95);
                $left_offsetX -= ceil($rectangle_width * 0.10);
            }
        }
        if ($left_rotate > 0 || $left_rotate < 0){
            $left_rectangle = imagerotate($left_rectangle, $left_rotate, $transparent); // Apply transparent color here!!!
        }

        // Finally, add the rotated rectangle to the banner image
        imagecopy($banner_image, $left_rectangle, $left_offsetX, $left_offsetY, 0, 0, imagesx($left_rectangle), imagesy($left_rectangle));
        imagedestroy($left_rectangle);

        // Return true on success
        return true;

    }


    // -- SESSION FUNCTIONS -- //


    // Define a function for collecting the current GAME token
    public static function session_token(){
        if (defined('MMRPG_REMOTE_GAME')){ return 'REMOTE_GAME_'.MMRPG_REMOTE_GAME; }
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
        $this_user['colourtoken2'] = '';
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

    // Define a function for optimizing an export error for the session (aka removing unchanged values)
    public static function optimize_export_array_for_session($export_data, $indexed_data){
        $diff_only = array();
        foreach ($export_data AS $key => $export_value){
            if (!isset($indexed_data[$key])
                || $indexed_data[$key] !== $export_value){
                if (strstr($key, '_base_')){
                    $skey = str_replace('_base_', '_', $key);
                    if (isset($indexed_data[$skey])
                        && $indexed_data[$skey] === $export_value){
                        continue;
                    }
                }
                $diff_only[$key] = $export_value;
            }
        }
        return $diff_only;
    }


    // -- MISC ABILITY/ITEM/SKILL FUNCTIONS -- //

    // Define a function for checking if a given "condition" is valid and return the parsed values if true
    public static function check_battle_condition_is_valid($condition, $this_object = false){

        // Collect the object token for error-logging purposes
        $this_object_token = 'undefined'; $this_object_kind = 'unknown';
        if (isset($this_object->ability_token)){ $this_object_token = $this_object->ability_token; $this_object_kind = 'ability'; }
        elseif (isset($this_object->item_token)){ $this_object_token = $this_object->item_token; $this_object_kind = 'item'; }
        elseif (isset($this_object->skill_token)){ $this_object_token = $this_object->skill_token; $this_object_kind = 'skill'; }

        // First check to ensure it matches the established condition format
        // examples: "attack < 3" or "defense >= 2" or "energy < 50%"
        if (!preg_match('/^([-_a-z]+)\s?([\<\>\=\!\%]+)\s?(\-?[0-9]+\%?|[-_a-z0-9]+)$/i', $condition, $matches)){
            error_log('skill parameter "condition" was set but was invalid ('.$this_object_token.':'.__LINE__.')');
            error_log('$condition = '.print_r($condition, true));
            return false;
        }
        // Now check to make sure the individual parts of the condition are allowed
        $allowed_condition_keywords = array('field-type', 'robot-position');
        $allowed_condition_stats = array('energy', 'weapons', 'attack', 'defense', 'speed');
        $allowed_condition_operators = array('=', '<=', '>=', '<', '>', '<>');
        list($x, $c_stat, $c_operator, $c_value) = $matches;
        if (!in_array($c_stat, $allowed_condition_keywords)
            && !in_array($c_stat, $allowed_condition_stats)
            && !preg_match('/^field-multiplier-([a-z]+)$/', $c_stat)){
            error_log('skill parameter "condition" stat was set but was invalid ('.$this_object_token.':'.__LINE__.')');
            error_log('$c_stat = '.print_r($c_stat, true));
            return false;
        } elseif (!in_array($c_operator, $allowed_condition_operators)){
            error_log('skill parameter "condition" operator was set but was invalid ('.$this_object_token.':'.__LINE__.')');
            error_log('$c_operator = '.print_r($c_operator, true));
            return false;
        } else {
            // Validate the value parameter differently for energy/weapons vs attack/defense/speed stats
            $is_energy_c_stat = $c_stat === 'energy' || $c_stat === 'weapons' ? true : false;
            $is_field_type_c_stat = $c_stat === 'field-type' ? true : false;
            $is_robot_position_c_stat = $c_stat === 'robot-position' ? true : false;
            $is_field_multiplier_c_stat = preg_match('/^field-multiplier-([a-z]+)$/', $c_stat) ? true : false;
            if ($is_energy_c_stat){
                if (!strstr($c_value, '%')){
                    error_log('skill parameter "condition" value must be percent for energy/weapons stat ('.$this_object_token.':'.__LINE__.')');
                    error_log('$c_value = '.print_r($c_value, true));
                    return false;
                } elseif (intval($c_value) <= 0 || intval($c_value) > 100){
                    error_log('skill parameter "condition" value must be > 0% and <= 100% for energy/weapons stat ('.$this_object_token.':'.__LINE__.')');
                    error_log('$c_value = '.print_r($c_value, true));
                    return false;
                }
            } elseif ($is_field_type_c_stat) {
                $allowed_field_types = array_keys(rpg_type::get_index(false, false, false, false));
                if (!in_array($c_value, $allowed_field_types)){
                    error_log('skill parameter "condition" value must be a valid type for field types ('.$this_object_token.':'.__LINE__.')');
                    error_log('$c_value = '.print_r($c_value, true));
                    return false;
                }
            } elseif ($is_robot_position_c_stat) {
                $allowed_position_values = array('active', 'bench');
                if (!in_array($c_value, $allowed_position_values)){
                    error_log('skill parameter "condition" value must be a valid for robot positions ('.$this_object_token.':'.__LINE__.')');
                    error_log('$c_value = '.print_r($c_value, true));
                    return false;
                }
            } elseif ($is_field_multiplier_c_stat) {
                if (floatval($c_value) < MMRPG_SETTINGS_MULTIPLIER_MIN || floatval($c_value) > MMRPG_SETTINGS_MULTIPLIER_MAX){
                    error_log('skill parameter "condition" value must be > '.MMRPG_SETTINGS_MULTIPLIER_MIN.' and < '.MMRPG_SETTINGS_MULTIPLIER_MAX.' for multipliers ('.$this_object_token.':'.__LINE__.')');
                    error_log('$c_value = '.print_r($c_value, true));
                    return false;
                }
            } else {
                if (intval($c_value) < MMRPG_SETTINGS_STATS_MOD_MIN || intval($c_value) > MMRPG_SETTINGS_STATS_MOD_MAX){
                    error_log('skill parameter "condition" value must be > '.MMRPG_SETTINGS_STATS_MOD_MIN.' and < '.MMRPG_SETTINGS_STATS_MOD_MAX.' for attack/defense/speed stat ('.$this_object_token.':'.__LINE__.')');
                    error_log('$c_value = '.print_r($c_value, true));
                    return false;
                }
            }
        }

        // If we made it this far it must be valid, return the broken-up parameter details
        return array(
            'stat' => $c_stat,
            'operator' => $c_operator,
            'value' => $c_value,
            );

    }


}