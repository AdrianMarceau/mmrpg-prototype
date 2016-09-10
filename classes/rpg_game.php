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

}