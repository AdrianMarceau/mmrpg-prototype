<?
/**
 * Mega Man RPG Game
 * <p>The top-level container class for Mega Man RPG Prototype objects and settings.</p>
 */
class rpg_game {

    // Define global class variables
    public static $index = array();

    /**
     * Create a new RPG object
     * @return  rpg_game
     */
    public function rpg_game(){

    }

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

}