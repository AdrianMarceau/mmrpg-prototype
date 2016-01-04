<?php
/**
 * Mega Man RPG Player
 * <p>The object class for all players in the Mega Man RPG Prototype.</p>
 */
class rpg_player extends rpg_object {

    // Define the internal database cache
    public static $database_index = array();

    // Define public player variables
    public $battle = null;
    public $battle_id = 0;
    public $battle_token = '';
    public $field = null;
    public $field_id = 0;
    public $field_token = '';
    public $player_id = 0;
    public $player_name = '';
    public $player_token = '';
    public $player_image = '';
    public $player_image_size = 0;
    public $player_description = '';
    public $player_energy = 0;
    public $player_attack = 0;
    public $player_defense = 0;
    public $player_speed = 0;
    public $player_robots = array();
    public $player_abilities = array();
    public $player_items = array();
    public $player_quotes = array();
    public $player_rewards = array();
    public $player_starforce = array();
    public $player_frame = '';
    public $player_frame_index = array();
    public $player_frame_offset = array();
    public $player_points = 0;
    public $player_switch = 0;
    public $player_side = '';
    public $player_controller = '';
    public $player_autopilot = 0;
    public $player_next_action = '';
    public $player_base_name = '';
    public $player_base_image = '';
    public $player_base_image_size = 0;
    public $player_base_description = '';
    public $player_base_energy = 0;
    public $player_base_attack = 0;
    public $player_base_defense = 0;
    public $player_base_speed = 0;
    //public $player_base_robots = array();
    //public $player_base_abilities = array();
    //public $player_base_items = array();
    //public $player_base_quotes = array();
    //public $player_base_rewards = array();
    //public $player_base_starforce = array();
    //public $player_base_points = 0;
    //public $player_base_switch = 0;

    /**
     * Create a new RPG player object
     * @param array $player_info (optional)
     * @return rpg_player
     */
    public function __construct($player_info = array()){

        // Update the session keys for this object
        $this->session_key = 'PLAYERS';
        $this->session_token = 'player_token';
        $this->session_id = 'player_id';
        $this->class = 'player';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal class identifier
        $this->class = 'player';

        // Define the internal battle pointer
        $this->battle = rpg_battle::get_battle();
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Define the internal battle pointer
        $this->field = rpg_field::get_field();
        $this->field_id = $this->field->field_id;
        $this->field_token = $this->field->field_token;

        // Collect current player data from the function if available
        $this_playerinfo = !empty($player_info) ? $player_info : array();
        if (!isset($this_playerinfo['player_id'])){ $this_playerinfo['player_id'] = 0; }
        if (!isset($this_playerinfo['player_token'])){ $this_playerinfo['player_token'] = 'player'; }

        // Load the player data based on the ID and fallback token
        $this_playerinfo = $this->player_load($this_playerinfo['player_id'], $this_playerinfo['player_token'], $this_playerinfo);

        // Now load the player data from the session or index
        if (empty($this_playerinfo)){
            // Player data could not be loaded
            die('Player data could not be loaded :<br />$this_playerinfo = <pre>'.print_r($this_playerinfo, true).'</pre>');
            return false;
        }

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    /**
     * Manually (re)load player data for this object from the session or index
     * @param integer $player_id
     * @param string $field_token
     * @param array $custom_info (optional)
     * @return bool
     */
    public function player_load($player_id = 0, $player_token = 'player', $custom_info = array()){

        // If the player ID was not provided, return false
        if (!isset($player_id)){
            die("player id must be set!\n\$this_playerinfo\n".print_r($this_playerinfo, true));
            return false;
        }
        // If the player token was not provided, return false
        if (!isset($player_token)){
            die("player token must be set!\n\$this_playerinfo\n".print_r($this_playerinfo, true));
            return false;
        }

        // Collect current player data from the session if available
        if (isset($_SESSION['PLAYERS'][$player_id])){
            $this_playerinfo = $_SESSION['PLAYERS'][$player_id];
            if ($this_playerinfo['player_token'] != $player_token){
                die("player token and ID mismatch {$player_id}:{$player_token}!\n");
                return false;
            }
        }
        // Otherwise, collect player data from the index
        else {
            $this_playerinfo = self::get_index_info($player_token);
            if (empty($this_playerinfo)){
                die("player data could not be loaded for {$player_id}:{$player_token}!\n");
                return false;
            }
        }

        // If the custom data was not empty, merge now
        if (!empty($custom_info)){ $this_playerinfo = array_merge($this_playerinfo, $custom_info); }

        // Define the internal player values using the collected array
        $this->flags = isset($this_playerinfo['flags']) ? $this_playerinfo['flags'] : array();
        $this->counters = isset($this_playerinfo['counters']) ? $this_playerinfo['counters'] : array();
        $this->values = isset($this_playerinfo['values']) ? $this_playerinfo['values'] : array();
        $this->history = isset($this_playerinfo['history']) ? $this_playerinfo['history'] : array();
        $this->player_id = isset($this_playerinfo['player_id']) ? $this_playerinfo['player_id'] : $player_id;
        $this->player_name = isset($this_playerinfo['player_name']) ? $this_playerinfo['player_name'] : 'Player';
        $this->player_token = isset($this_playerinfo['player_token']) ? $this_playerinfo['player_token'] : 'player';
        $this->player_image = isset($this_playerinfo['player_image']) ? $this_playerinfo['player_image'] : $this->player_token;
        $this->player_image_size = isset($this_playerinfo['player_image_size']) ? $this_playerinfo['player_image_size'] : 40;
        $this->player_description = isset($this_playerinfo['player_description']) ? $this_playerinfo['player_description'] : '';
        $this->player_energy = isset($this_playerinfo['player_energy']) ? $this_playerinfo['player_energy'] : 0;
        $this->player_attack = isset($this_playerinfo['player_attack']) ? $this_playerinfo['player_attack'] : 0;
        $this->player_defense = isset($this_playerinfo['player_defense']) ? $this_playerinfo['player_defense'] : 0;
        $this->player_speed = isset($this_playerinfo['player_speed']) ? $this_playerinfo['player_speed'] : 0;
        $this->player_robots = isset($this_playerinfo['player_robots']) ? $this_playerinfo['player_robots'] : array();
        $this->player_abilities = isset($this_playerinfo['player_abilities']) ? $this_playerinfo['player_abilities'] : array();
        $this->player_items = isset($this_playerinfo['player_items']) ? $this_playerinfo['player_items'] : array();
        $this->player_quotes = isset($this_playerinfo['player_quotes']) ? $this_playerinfo['player_quotes'] : array();
        $this->player_rewards = isset($this_playerinfo['player_rewards']) ? $this_playerinfo['player_rewards'] : array();
        $this->player_starforce = isset($this_playerinfo['player_starforce']) ? $this_playerinfo['player_starforce'] : array();
        $this->player_frame = isset($this_playerinfo['player_frame']) ? $this_playerinfo['player_frame'] : 'base';
        $this->player_frame_index = isset($this_playerinfo['player_frame_index']) ? $this_playerinfo['player_frame_index'] : array('base','taunt','victory','defeat','command','damage');
        $this->player_frame_offset = isset($this_playerinfo['player_frame_offset']) ? $this_playerinfo['player_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->player_points = isset($this_playerinfo['player_points']) ? $this_playerinfo['player_points'] : 0;
        $this->player_switch = isset($this_playerinfo['player_switch']) ? $this_playerinfo['player_switch'] : 1;
        $this->player_side = isset($this_playerinfo['player_side']) ? $this_playerinfo['player_side'] : 'left';
        $this->player_direction = isset($this_playerinfo['player_direction']) ? $this_playerinfo['player_direction'] : ($this->player_side == 'left' ? 'right' : 'left');
        $this->player_controller = isset($this_playerinfo['player_controller']) ? $this_playerinfo['player_controller'] : ($this->player_side == 'left' ? 'human' : 'computer');
        $this->player_autopilot = isset($this_playerinfo['player_autopilot']) ? $this_playerinfo['player_autopilot'] : 0;
        $this->player_next_action = isset($this_playerinfo['player_next_action']) ? $this_playerinfo['player_next_action'] : 'auto';

        // Define the internal player base values using the players index array
        $this->player_base_name = isset($this_playerinfo['player_base_name']) ? $this_playerinfo['player_base_name'] : $this->player_name;
        $this->player_base_image = isset($this_playerinfo['player_base_image']) ? $this_playerinfo['player_base_image'] : $this->player_image;
        $this->player_base_image_size = isset($this_playerinfo['player_base_image_size']) ? $this_playerinfo['player_base_image_size'] : $this->player_image_size;
        $this->player_base_description = isset($this_playerinfo['player_base_description']) ? $this_playerinfo['player_base_description'] : $this->player_description;
        $this->player_base_energy = isset($this_playerinfo['player_base_energy']) ? $this_playerinfo['player_base_energy'] : $this->player_energy;
        $this->player_base_attack = isset($this_playerinfo['player_base_attack']) ? $this_playerinfo['player_base_attack'] : $this->player_attack;
        $this->player_base_defense = isset($this_playerinfo['player_base_defense']) ? $this_playerinfo['player_base_defense'] : $this->player_defense;
        $this->player_base_speed = isset($this_playerinfo['player_base_speed']) ? $this_playerinfo['player_base_speed'] : $this->player_speed;
        //$this->player_base_robots = isset($this_playerinfo['player_base_robots']) ? $this_playerinfo['player_base_robots'] : $this->player_robots;
        //$this->player_base_abilities = isset($this_playerinfo['player_base_abilities']) ? $this_playerinfo['player_base_abilities'] : $this->player_abilities;
        //$this->player_base_items = isset($this_playerinfo['player_base_items']) ? $this_playerinfo['player_base_items'] : $this->player_items;
        //$this->player_base_quotes = isset($this_playerinfo['player_base_quotes']) ? $this_playerinfo['player_base_quotes'] : $this->player_quotes;
        //$this->player_base_rewards = isset($this_playerinfo['player_base_rewards']) ? $this_playerinfo['player_base_rewards'] : $this->player_rewards;
        //$this->player_base_starforce = isset($this_playerinfo['player_base_starforce']) ? $this_playerinfo['player_base_starforce'] : $this->player_starforce;
        //$this->player_base_points = isset($this_playerinfo['player_base_points']) ? $this_playerinfo['player_base_points'] : $this->player_points;
        //$this->player_base_switch = isset($this_playerinfo['player_base_switch']) ? $this_playerinfo['player_base_switch'] : $this->player_switch;

        // Remove any abilities that do not exist in the index
        if (!empty($this->player_abilities)){
            foreach ($this->player_abilities AS $key => $token){
                if (empty($token)){ unset($this->player_abilities[$key]); }
            }
            $this->player_abilities = array_values($this->player_abilities);
        }

        // Pull in session starforce if available for human players
        if (empty($this->player_starforce) && $this->player_side == 'left'){
            if (!empty($_SESSION['GAME']['values']['star_force'])){
                $this->player_starforce = $_SESSION['GAME']['values']['star_force'];
            }
        }

        // Return true on success
        return true;

    }


    // -- ID FUNCTIONS -- //

    /**
     * Get the ID of this player object
     * @return integer
     */
    public function get_id(){
        return intval($this->get_info('player_id'));
    }

    /**
     * Set the ID of this player object
     * @param int $value
     */
    public function set_id($value){
        $this->set_info('player_id', intval($value));
    }

    // -- NAME FUNCTIONS -- //

    /**
     * Get the name of this player object
     * @return string
     */
    public function get_name(){
        return $this->get_info('player_name');
    }

    /**
     * Set the name of this player object
     * @param string $value
     */
    public function set_name($name){
        $this->set_info('player_name', $name);
    }

    /**
     * Get the base name of this player object
     * @return string value
     */
    public function get_base_name(){
        return $this->get_info('player_base_name');
    }

    /**
     * Set the base name of this player object
     * @param string $value
     */
    public function set_base_name($name){
        $this->set_info('player_base_name', $name);
    }


    // -- TOKEN FUNCTIONS -- //

    /**
     * Get the token of this player object
     * @return string
     */
    public function get_token(){
        return $this->get_info('player_token');
    }

    /**
     * Set the token of this player object
     * @param string $value
     */
    public function set_token($token){
        $this->set_info('player_token', $token);
    }


    // -- DESCRIPTION FUNCTIONS -- //

    /**
     * Get the description of this player object
     * @return string
     */
    public function get_description(){
        return $this->get_info('player_description');
    }

    /**
     * Set the description of this player object
     * @param string $description
     */
    public function set_description($description){
        $this->set_info('player_description', $description);
    }

    /**
     * Get the base description of this player object
     * @return string
     */
    public function get_base_description(){
        return $this->get_info('player_base_description');
    }

    /**
     * Set the base description of this player object
     * @param string $description
     */
    public function set_base_description($description){
        $this->set_info('player_base_description', $description);
    }


    // -- ENERGY FUNCTIONS -- //

    /**
     * Get the energy stat for this player object
     * @return int
     */
    public function get_energy(){
        return $this->get_info('player_energy');
    }

    /**
     * Set the energy stat for this player object
     * @param int $energy
     */
    public function set_energy($energy){
        $this->set_info('player_energy', $energy);
    }

    /**
     * Get the base energy stat for this player object
     * @return int
     */
    public function get_base_energy(){
        return $this->get_info('player_base_energy');
    }

    /**
     * Set the base energy stat for this player object
     * @param int $energy
     */
    public function set_base_energy($energy){
        $this->set_info('player_base_energy', $energy);
    }


    // -- WEAPONS FUNCTIONS -- //

    /**
     * Get the weapons stat for this player object
     * @return int
     */
    public function get_weapons(){
        return $this->get_info('player_weapons');
    }

    /**
     * Set the weapons stat for this player object
     * @param int $weapons
     */
    public function set_weapons($weapons){
        $this->set_info('player_weapons', $weapons);
    }

    /**
     * Get the base weapons stat for this player object
     * @return int
     */
    public function get_base_weapons(){
        return $this->get_info('player_base_weapons');
    }

    /**
     * Set the base weapons stat for this player object
     * @param int $weapons
     */
    public function set_base_weapons($weapons){
        $this->set_info('player_base_weapons', $weapons);
    }


    // -- ATTACK FUNCTIONS -- //

    /**
     * Get the attack stat for this player object
     * @return int
     */
    public function get_attack(){
        return $this->get_info('player_attack');
    }

    /**
     * Set the attack stat for this player object
     * @param int $attack
     */
    public function set_attack($attack){
        $this->set_info('player_attack', $attack);
    }

    /**
     * Get the base attack stat for this player object
     * @return int
     */
    public function get_base_attack(){
        return $this->get_info('player_base_attack');
    }

    /**
     * Set the base attack stat for this player object
     * @param int $attack
     */
    public function set_base_attack($attack){
        $this->set_info('player_base_attack', $attack);
    }


    // -- DEFENSE FUNCTIONS -- //

    /**
     * Get the defense stat for this player object
     * @return int
     */
    public function get_defense(){
        return $this->get_info('player_defense');
    }

    /**
     * Set the defense stat for this player object
     * @param int $defense
     */
    public function set_defense($defense){
        $this->set_info('player_defense', $defense);
    }

    /**
     * Get the base defense stat for this player object
     * @return int
     */
    public function get_base_defense(){
        return $this->get_info('player_base_defense');
    }

    /**
     * Set the base defense stat for this player object
     * @param int $defense
     */
    public function set_base_defense($defense){
        $this->set_info('player_base_defense', $defense);
    }


    // -- SPEED FUNCTIONS -- //

    /**
     * Get the speed stat for this player object
     * @return int
     */
    public function get_speed(){
        return $this->get_info('player_speed');
    }

    /**
     * Set the speed stat for this player object
     * @param int $speed
     */
    public function set_speed($speed){
        $this->set_info('player_speed', $speed);
    }

    /**
     * Get the base speed stat for this player object
     * @return int
     */
    public function get_base_speed(){
        return $this->get_info('player_base_speed');
    }

    /**
     * Set the base speed stat for this player object
     * @param int $speed
     */
    public function set_base_speed($speed){
        $this->set_info('player_base_speed', $speed);
    }


    // -- IMAGE FUNCTIONS -- //

    /**
     * Get the image token of this player object
     * @return string
     */
    public function get_image(){
        return $this->get_info('player_image');
    }

    /**
     * Set the image token of this player object
     * @param string $image
     */
    public function set_image($image){
        $this->set_info('player_image', $image);
    }

    /**
     * Get the base image token of this player object
     * @return string
     */
    public function get_base_image(){
        return $this->get_info('player_base_image');
    }

    /**
     * Set the base image token of this player object
     * @param string $image
     */
    public function set_base_image($image){
        $this->set_info('player_base_image', $image);
    }

    /**
     * Get the image size of this player object
     * @return int
     */
    public function get_image_size(){
        return $this->get_info('player_image_size');
    }

    /**
     * Set the image size of this player object
     * @param int $size
     */
    public function set_image_size($size){
        $this->set_info('player_image_size', $size);
    }

    /**
     * Get the base image size of this player object
     * @return int
     */
    public function get_base_image_size(){
        return $this->get_info('player_base_image_size');
    }

    /**
     * Set the base image size of this player object
     * @param int $size
     */
    public function set_base_image_size($size){
        $this->set_info('player_base_image_size', $size);
    }


    // -- ROBOT FUNCTIONS -- //

    /**
     * Get the list of robots owned by this player object
     * @return array
     */
    public function get_robots(){
        return $this->get_info('player_robots');
    }

    /**
     * Set the list of robots owned by this player object
     * @param array $robots
     */
    public function set_robots($robots){
        $this->set_info('player_robots', $robots);
    }

    /*

    /**
     * Get the base list of robots owned by this player object
     * @return array
     * /
    public function get_base_robots(){
        return $this->get_info('player_base_robots');
    }

    /**
     * Set the list of robots owned by this player object
     * @param array $robots
     * /
    public function set_base_robots($robots){
        $this->set_info('player_base_robots', $robots);
    }

    */

    /**
     * Get the list of active status robots owned by this player object
     * @return array
     */
    public function get_robots_active(){
        $filters = array('player_id' => $this->player_id, 'robot_status' => 'active');
        $robots = $this->battle->find_robots($filters);
        return $robots;
    }

    /**
     * Get the list of disabled status robots owned by this player object
     * @return array
     */
    public function get_robots_disabled(){
        $filters = array('player_id' => $this->player_id, 'robot_status' => 'disabled');
        $robots = $this->battle->find_robots($filters);
        return $robots;
    }


    // -- ABILITY FUNCTIONS -- //

    /**
     * Get the list of abilities equipped to this player object
     * @return array
     */
    public function get_abilities(){
        return $this->get_info('player_abilities');
    }

    /**
     * Get the number of abilities equipped to this player object
     * @return int
     */
    public function get_abilities_count(){
        $abilities = $this->get_info('player_abilities');
        return count($abilities);
    }

    /**
     * Set the list of abilities equipped to this player object
     * @param array $abilities
     */
    public function set_abilities($abilities){
        $this->set_info('player_abilities', $abilities);
    }

    /**
     * Check if this player object has any abilities
     * @param array $abilities
     */
    public function has_abilities(){
        $abilities = $this->get_info('player_abilities');
        return !empty($abilities) ? true : false;
    }

    /**
     * Get one of this player object's abilities by its slot key
     * @param int $key
     * @return string
     */
    public function get_ability($key){
        return $this->get_info('player_abilities', $key);
    }

    /**
     * Set one of this player object's abilities by its slot key
     * @param int $key
     * @param string $ability
     */
    public function set_ability($key, $token){
        $this->set_info('player_abilities', $key, $token);
    }

    /**
     * Unset one of this player object's abilities by its slot key
     * @param int $key
     */
    public function unset_ability($key){
        $this->unset_info('player_abilities', $key);
    }

    /**
     * Check if this player object has a specific ability by its slot key or token
     * @param mixed $value
     * @return bool
     */
    public function has_ability($value){
        $abilities = $this->get_info('player_abilities');
        if (is_numeric($value)){ return in_array($value, $abilities) ? true : false; }
        else { return isset($abilities[$value]) ? true : false; }
    }

    /*

    /**
     * Get the base list of abilities equipped to this player object
     * @return array
     * /
    public function get_base_abilities(){
        return $this->get_info('player_base_abilities');
    }

    /**
     * Get the number of base list of abilities equipped to this player object
     * @return array
     * /
    public function get_base_abilities_count(){
        $abilities = $this->get_info('player_base_abilities');
        return count($abilities);
    }

    /**
     * Set the base list of abilities equipped to this player object
     * @param array $abilities
     * /
    public function set_base_abilities($abilities){
        $this->set_info('player_base_abilities', $abilities);
    }

    /**
     * Check if this player object has any base abilities
     * @param array $abilities
     * /
    public function has_base_abilities(){
        $abilities = $this->get_info('player_base_abilities');
        return !empty($abilities) ? true : false;
    }

    /**
     * Get one of this player object's base abilities by its slot key
     * @param int $key
     * @return string
     * /
    public function get_base_ability($key){
        return $this->get_info('player_base_abilities', $key);
    }

    /**
     * Set one of this player object's base abilities by its slot key
     * @param int $key
     * @param string $ability
     * /
    public function set_base_ability($key, $token){
        $this->set_info('player_base_abilities', $key, $token);
    }

    /**
     * Unset one of this player object's abilities by its slot key
     * @param int $key
     * /
    public function unset_base_ability($key){
        $this->unset_info('player_base_abilities', $key);
    }

    /**
     * Check if this player object has a specific base ability by its slot key or token
     * @param mixed $value
     * @return bool
     * /
    public function has_base_ability($value){
        $abilities = $this->get_info('player_base_abilities');
        if (is_numeric($value)){ return in_array($value, $abilities) ? true : false; }
        else { return isset($abilities[$value]) ? true : false; }
    }

    */


    // -- ITEM FUNCTIONS -- //

    /**
     * Get the list of items equipped to this player object
     * @return array
     */
    public function get_items(){
        return $this->get_info('player_items');
    }

    /**
     * Get the number of items equipped to this player object
     * @return array
     */
    public function get_items_count(){
        $items = $this->get_info('player_items');
        return count($items);
    }

    /**
     * Set the list of items equipped to this player object
     * @param array $items
     */
    public function set_items($items){
        $this->set_info('player_items', $items);
    }

    /**
     * Get one of this player object's items by its slot key
     * @param int $key
     * @return string
     */
    public function get_item($key){
        return $this->get_info('player_items', $key);
    }

    /**
     * Set one of this player object's items by its slot key
     * @param int $key
     * @param string $item
     */
    public function set_item($key, $token){
        $this->set_info('player_items', $key, $token);
    }

    /**
     * Unset one of this player object's items by its slot key
     * @param int $key
     */
    public function unset_item($key){
        $this->unset_info('player_items', $key);
    }

    /**
     * Check if this player object has a specific item by its slot key or token
     * @param mixed $value
     * @return bool
     */
    public function has_item($value){
        $items = $this->get_info('player_items');
        if (is_numeric($value)){ return in_array($value, $items) ? true : false; }
        else { return isset($items[$value]) ? true : false; }
    }

    /*

    /**
     * Get the base list of items equipped to this player object
     * @return array
     * /
    public function get_base_items(){
        return $this->get_info('player_base_items');
    }

    /**
     * Get the base list of items equipped to this player object
     * @return array
     * /
    public function get_base_items_count(){
        $items = $this->get_info('player_base_items');
        return count($items);
    }

    /**
     * Set the base list of items equipped to this player object
     * @param array $items
     * /
    public function set_base_items($items){
        $this->set_info('player_base_items', $items);
    }

    /**
     * Get one of this player object's base items by its slot key
     * @param int $key
     * @return string
     * /
    public function get_base_item($key){
        return $this->get_info('player_base_items', $key);
    }

    /**
     * Set one of this player object's base items by its slot key
     * @param int $key
     * @param string $item
     * /
    public function set_base_item($key, $token){
        $this->set_info('player_base_items', $key, $token);
    }

    /**
     * Unset one of this player object's items by its slot key
     * @param int $key
     * /
    public function unset_base_item($key){
        $this->unset_info('player_base_items', $key);
    }

    /**
     * Check if this player object has a specific base item by its slot key or token
     * @param mixed $value
     * @return bool
     * /
    public function has_base_item($value){
        $items = $this->get_info('player_base_items');
        if (is_numeric($value)){ return in_array($value, $items) ? true : false; }
        else { return isset($items[$value]) ? true : false; }
    }

    */


    // -- CONTROLLER FUNCTIONS -- //

    /**
     * Get the side of the field this player object is fighting for
     * @return string
     */
    public function get_side(){
        return $this->get_info('player_side');
    }

    /**
     * Set the side of the field this player object is fighting for
     * @param string $side
     */
    public function set_side($side){
        $this->set_info('player_side', $side);
    }

    /**
     * Get the direction of the field this player object is fighting for
     * @return string
     */
    public function get_direction(){
        return $this->get_info('player_direction');
    }

    /**
     * Set the direction of the field this player object is fighting for
     * @param string $direction
     */
    public function set_direction($direction){
        $this->set_info('player_direction', $direction);
    }

    /**
     * Get the value of the auto-pilot flag for this player object
     * @return bool
     */
    public function is_autopilot(){
        return $this->get_autopilot() == true ? true : false;
    }

    /**
     * Get the value of the auto-pilot flag for this player object
     * @return bool
     */
    public function get_autopilot(){
        return $this->get_info('player_autopilot');
    }

    /**
     * Set the value of the auto-pilot flag for this player object
     * @param bool $flag
     */
    public function set_autopilot($flag){
        $this->set_info('player_autopilot', $flag);
    }

    /**
     * Get the controller value for this player object
     * @return string
     */
    public function get_controller(){
        return $this->get_info('player_controller');
    }

    /**
     * Set the controller value for this player object
     * @param string $controller
     */
    public function set_controller($controller){
        $this->set_info('player_controller', $controller);
    }


    // -- QUOTE FUNCTIONS -- //

    /**
     * Get the list of quotes for this player object
     * @return array
     */
    public function get_quotes(){
        return $this->get_info('player_quotes');
    }

    /**
     * Set the list of quotes for this player object
     * @param array $quotes
     */
    public function set_quotes($quotes){
        $this->set_info('player_quotes', $quotes);
    }

    /**
     * Get a specific quote for this player object by its token
     * @param string $token
     * @return string
     */
    public function get_quote($token){
        $quotes = $this->get_info('player_quotes');
        return isset($quotes[$token]) ? $quotes[$token] : '';
    }

    /**
     * Set a specific quote for this player object by its token
     * @param string $token
     * @param string $quote
     */
    public function set_quote($token, $quote){
        $this->set_info('player_quotes', $token, $quote);
    }

    /**
     * Check if this player object has a specific quote by its token
     * @param string $token
     * @return bool
     */
    public function has_quote($token){
        $quotes = $this->get_info('player_quotes');
        return isset($quotes[$token]) ? true : false;
    }

    /*

    /**
     * Get the base list of quotes for this player object
     * @return array
     * /
    public function get_base_quotes(){
        return $this->get_info('player_base_quotes');
    }

    /**
     * Set the base list of quotes for this player object
     * @param array $quotes
     * /
    public function set_base_quotes($quotes){
        $this->set_info('player_base_quotes', $quotes);
    }

    /**
     * Get a specific base quote for this player object by its token
     * @param string $token
     * @return string
     * /
    public function get_base_quote($token){
        $quotes = $this->get_info('player_base_quotes');
        return isset($quotes[$token]) ? $quotes[$token] : '';
    }

    /**
     * Set a specific base quote for this player object by its token
     * @param string $token
     * @param string $quote
     * /
    public function set_base_quote($token, $quote){
        $this->set_info('player_base_quotes', $token, $quote);
    }

    /**
     * Check if this player object has a specific base quote by its token
     * @param string $token
     * @return bool
     * /
    public function has_base_quote($token){
        $quotes = $this->get_info('player_base_quotes');
        return isset($quotes[$token]) ? true : false;
    }

    */


    // -- REWARD FUNCTIONS -- //

    /**
     * Get the list of rewards for this player object
     * @return array
     */
    public function get_rewards(){
        return $this->get_info('player_rewards');
    }

    /**
     * Set the list of rewards for this player object
     * @param array $rewards
     */
    public function set_rewards($rewards){
        $this->set_info('player_rewards', $rewards);
    }

    /*

    /**
     * Get the base list of rewards for this player object
     * @return array
     * /
    public function get_base_rewards(){
        return $this->get_info('player_base_rewards');
    }

    /**
     * Set the base list of rewards for this player object
     * @param array $rewards
     * /
    public function set_base_rewards($rewards){
        $this->set_info('player_base_rewards', $rewards);
    }

    */


    // -- STARFORCE FUNCTIONS -- //

    /**
     * Get the list of starforce values for this player object
     * @return array
     */
    public function get_starforce(){
        return $this->get_info('player_starforce');
    }

    /**
     * Set the list of starforce values for this player object
     * @param array $starforce
     */
    public function set_starforce($starforce){
        $this->set_info('player_starforce', $starforce);
    }

    /*

    /**
     * Get the base list of starforce values for this player object
     * @return array
     * /
    public function get_base_starforce(){
        return $this->get_info('player_base_starforce');
    }

    /**
     * Set the base list of starforce values for this player object
     * @param array $starforce
     * /
    public function set_base_starforce($value){
        $this->set_info('player_base_starforce', $value);
    }

    */


    // -- FRAME FUNCTIONS -- //

    /**
     * Get the current frame of this player object's sprite
     * @return string
     */
    public function get_frame(){
        $frame = $this->get_info('player_frame');
        return !empty($frame) ? $frame : 'base';
    }

    /**
     * Set the current frame of this player object's sprite
     * @param string $frame
     */
    public function set_frame($frame){
        $this->set_info('player_frame', $frame);
    }

    /**
     * Get the frame index for this player object's sprite
     * @return array
     */
    public function get_frame_index(){
        $index = $this->get_info('player_frame_index');
        return !empty($index) ? $index : array();
    }

    /**
     * Get the frame index key for a specific frame of this player sprite
     * @param string $frame
     * @return string
     */
    public function get_frame_index_key($frame){
        $index = $this->get_info('player_frame_index');
        $key = array_search($frame, $index);
        return $key != false ? str_pad($key, 2, '0', STR_PAD_LEFT) : '00';
    }

    /**
     * Set the frame index for this player object's sprite
     * @param array $index
     */
    public function set_frame_index($index){
        $this->set_info('player_frame_index', $index);
    }

    /**
     * Get the frame offset for this player object's sprite
     * @return array
     */
    public function get_frame_offset(){
        $offset = $this->get_info('player_frame_offset');
        return !empty($offset) ? $offset : array();
    }

    /**
     * Set the frame offset for this player object's sprite
     * @param array $offset
     */
    public function set_frame_offset($offset){
        $this->set_info('player_frame_offset', $offset);
    }


    // -- POINTS FUNCTIONS -- //

    /**
     * Get the battle points for this player object
     * @return int
     */
    public function get_points(){
        return $this->get_info('player_points');
    }

    /**
     * Set the battle points for this player object
     * @param int $points
     */
    public function set_points($points){
        $this->set_info('player_points', $points);
    }

    /*

    /**
     * Get the base battle points for this player object
     * @return int
     * /
    public function get_base_points(){
        return $this->get_info('player_base_points');
    }

    /**
     * Set the base battle points for this player object
     * @param int $points
     * /
    public function set_base_points($value){
        $this->set_info('player_base_points', $value);
    }

    */

    // -- SWITCH FUNCTIONS -- //

    /**
     * Get the switch frequency of this player object
     * @return float
     */
    public function get_switch(){
        return $this->get_info('player_switch');
    }

    /**
     * Set the switch frequency of this player object
     * @param float $switch
     */
    public function set_switch($switch){
        $this->set_info('player_switch', $switch);
    }

    /*

    /**
     * Get the base switch frequency of this player object
     * @return float
     * /
    public function get_base_switch(){
        return $this->get_info('player_base_switch');
    }

    /**
     * Set the switch frequency of this player object
     * @param float $switch
     * /
    public function set_base_switch($switch){
        $this->set_info('player_base_switch', $switch);
    }

    */


    // -- ACTION FUNCTIONS -- //

    /**
     * Get the next action of this player object
     * @return string
     */
    public function get_next_action(){
        return $this->get_info('player_next_action');
    }

    /**
     * Set the next action of this player object
     * @param string $action
     */
    public function set_next_action($action){
        $this->set_info('player_next_action', $action);
    }


    // -- STARTUP FUNCTIONS -- //

    /**
     * Add a new robot to this player's object data and apply startup actions
     * @param array $robot_info
     * @param int $robot_key
     * @param bool $apply_bonuses (optional)
     * @return bool
     */
    public function add_robot($robot_info, $robot_key = 0, $apply_bonuses = false){
        if (empty($robot_info['robot_id']) || empty($robot_info['robot_token'])){ return false; }
        $this_battle = rpg_battle::get_battle();
        $robot_id = $robot_info['robot_id'];
        $robot_token = $robot_info['robot_token'];
        $robot_key = !empty($robot_key) ? $robot_key : $this->get_counter('robots_total');
        $robot_info['player_id'] = $this->get_id();
        $robot_info['player_token'] = $this->get_token();
        $this_battle->add_robot($this, $robot_info);
        $this_robot = $this_battle->get_robot($robot_id);
        if ($apply_bonuses){ $this_robot->apply_stat_bonuses(); }
        $player_robots = $this->get_robots();
        $player_robots[$robot_key] = array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token);
        $this->set_robots($player_robots);
        $this->update_variables();
        return true;
    }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all player index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_index_fields($implode = false){

        // Define the various index fields for player objects
        $index_fields = array(
            'player_id',
            'player_token',
            'player_number',
            'player_name',
            'player_game',
            'player_group',
            'player_class',
            'player_image',
            'player_image_size',
            'player_image_editor',
            'player_image_alts',
            'player_type',
            'player_type2',
            'player_description',
            'player_description2',
            'player_energy',
            'player_weapons',
            'player_attack',
            'player_defense',
            'player_speed',
            'player_abilities_rewards',
            'player_abilities_compatible',
            'player_robots_rewards',
            'player_robots_compatible',
            'player_quotes_start',
            'player_quotes_taunt',
            'player_quotes_victory',
            'player_quotes_defeat',
            'player_functions',
            'player_flag_hidden',
            'player_flag_complete',
            'player_flag_published',
            'player_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

    /**
     * Get the entire player index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND player_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND player_flag_published = 1 '; }

        // Collect every type's info from the database index
        $player_fields = self::get_index_fields(true);
        $player_index = $db->get_array_list("SELECT {$player_fields} FROM mmrpg_index_players WHERE player_id <> 0 {$temp_where};", 'player_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($player_index)){
            $player_index = self::parse_index($player_index);
            return $player_index;
        } else {
            return array();
        }

    }

    /**
     * Get the tokens for all players in the global index
     * @return array
     */
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND player_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND player_flag_published = 1 '; }

        // Collect an array of player tokens from the database
        $player_index = $db->get_array_list("SELECT player_token FROM mmrpg_index_players WHERE player_id <> 0 {$temp_where};", 'player_token');

        // Return the tokens if not empty, else nothing
        if (!empty($player_index)){
            $player_tokens = array_keys($player_index);
            return $player_tokens;
        } else {
            return array();
        }

    }

    // Define a function for pulling a custom player index
    public static function get_index_custom($player_tokens = array()){

        // Pull in global variables
        $db = cms_database::get_database();

        // Generate a token string for the database query
        $player_tokens_string = array();
        foreach ($player_tokens AS $player_token){ $player_tokens_string[] = "'{$player_token}'"; }
        $player_tokens_string = implode(', ', $player_tokens_string);

        // Collect the requested player's info from the database index
        $player_fields = self::get_index_fields(true);
        $player_index = $db->get_array_list("SELECT {$player_fields} FROM mmrpg_index_players WHERE player_token IN ({$player_tokens_string});", 'player_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($player_index)){
            $player_index = self::parse_index($player_index);
            return $player_index;
        } else {
            return array();
        }

    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($player_token){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this player's info from the database index
        $lookup = !is_numeric($player_token) ? "player_token = '{$player_token}'" : "player_id = {$player_token}";
        $player_fields = self::get_index_fields(true);
        $player_index = $db->get_array("SELECT {$player_fields} FROM mmrpg_index_players WHERE {$lookup};", 'player_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($player_index)){
            $player_index = self::parse_index_info($player_index);
            return $player_index;
        } else {
            return array();
        }

    }

    // Define a public function for parsing a player index array in bulk
    public static function parse_index($player_index){

        // Loop through each entry and parse its data
        foreach ($player_index AS $token => $info){
            $player_index[$token] = self::parse_index_info($info);
        }

        // Return the parsed index
        return $player_index;

    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($player_info){

        // Return false if empty
        if (empty($player_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($player_info['_parsed'])){ return $player_info; }
        else { $player_info['_parsed'] = true; }

        // Explode json encoded fields into expanded array objects
        $temp_fields = array('player_abilities_rewards', 'player_abilities_compatible', 'player_robots_rewards', 'player_robots_compatible');
        foreach ($temp_fields AS $field_name){
            if (!empty($player_info[$field_name])){ $player_info[$field_name] = json_decode($player_info[$field_name], true); }
            else { $player_info[$field_name] = array(); }
        }

        // Collect the quotes into the proper arrays
        $quote_types = array('start', 'taunt', 'victory', 'defeat');
        foreach ($quote_types AS $type){
            $player_info['player_quotes']['battle_'.$type] = !empty($player_info['player_quotes_'.$type]) ? $player_info['player_quotes_'.$type]: '';
            unset($player_info['player_quotes_'.$type]);
        }

        // Return the parsed player info
        return $player_info;
    }


    // -- PRINT FUNCTIONS -- //

    /**
     * Get the formatted name of this player object
     * @return string
     */
    public function print_name(){
        return '<span class="player_name player_type">'.$this->player_name.'</span>';
    }

    /**
     * Get the formatted token of this player object
     * @return string
     */
    public function print_token(){
        return '<span class="player_token">'.$this->player_token.'</span>';
    }

    /**
     * Get the formatted description of this player object
     * @return string
     */
    public function print_description(){
        return '<span class="player_description">'.$this->player_description.'</span>';
    }

    /**
     * Get a formatted quote from this player object, optionally providing search and replace values
     * @return string
     */
    public function print_quote($quote_type, $this_search = array(), $this_replace = array()){
        $mmrpg_types = rpg_type::get_index();
        // Define the quote text variable
        $quote_text = '';
        // If the player is visible and has the requested quote text
        if ($this->get_token() != 'player' && $this->has_quote($quote_type)){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = $this->get_quote($quote_type);
            $this_quote_text = str_replace($this_search, $this_replace, $this_quote_text);
            // Collect the text colour for this player
            $this_type_token = str_replace('dr-', '', $this->player_token);
            $this_text_colour = !empty($mmrpg_types[$this_type_token]) ? $mmrpg_types[$this_type_token]['type_colour_light'] : array(200, 200, 200);
            foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += 20; }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }
        return $quote_text;
    }

    /**
     * Generate the canvas sprite markup for this player object and any attachments
     * @param array $options (optional)
     * @return string
     */
    public function get_canvas_markup($options = array()){

        // Define the variable to hold the console player data
        $this_data = array();
        $this_ability = !empty($options['this_ability']) ? $options['this_ability'] : false;
        $this_results = !empty($this_ability) ? $this_ability->get_results() : array();

        // Only proceed if this is a real player
        if ($this->get_token() != 'player'){

            // Define and calculate the simpler markup and positioning variables for this player
            $this_data['data_type'] = 'player';
            $this_data['player_id'] = $this->get_id();
            $this_data['player_frame'] = $this->player_frame !== false ? $this->player_frame : 'base'; // IMPORTANT
            //$this_data['player_frame'] = str_pad(array_search($this_data['player_frame'], $this->player_frame_index), 2, '0', STR_PAD_LEFT);
            $this_data['player_frame_index'] = $this->get_frame_index();
            $this_data['player_title'] = $this->get_name();
            $this_data['player_token'] = $this->get_token();
            $this_data['player_float'] = $this->get_side();
            $this_data['player_direction'] = $this_data['player_float'] == 'left' ? 'right' : 'left';
            $this_data['player_position'] = 'active';
            $this_data['player_size'] = 80;
            $this_data['image_type'] = !empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite';
            /*
            $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/sprite_'.$this_data['player_direction'].'_'.$this_data['player_size'].'x'.$this_data['player_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['player_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_80x80 sprite_80x80_'.$this_data['player_frame'];
            $this_data['player_styles'] = '';
            */
            $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/sprite_'.$this_data['player_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['player_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_75x75 sprite_75x75_'.$this_data['player_frame'];


            $this_data['player_scale'] = 0.5 + ((7 / 8) * 0.5);
            $this_data['player_sprite_size'] = ceil($this_data['player_scale'] * 80);
            $this_data['player_sprite_width'] = ceil($this_data['player_scale'] * 80);
            $this_data['player_sprite_height'] = ceil($this_data['player_scale'] * 80);
            $this_data['player_image_width'] = ceil($this_data['player_scale'] * 800);
            $this_data['player_image_height'] = ceil($this_data['player_scale'] * 80);
            $this_data['canvas_offset_z'] = 4900;
            $this_data['canvas_offset_x'] = 200;
            $this_data['canvas_offset_y'] = 60;

            $frame_position = array_search($this_data['player_frame'], $this_data['player_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $frame_background_offset = -1 * ceil(($this_data['player_sprite_size'] * $frame_position));
            $this_data['player_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
            $this_data['player_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['player_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
            $this_data['player_style'] .= 'background-image: url('.$this_data['player_image'].'); width: '.$this_data['player_sprite_size'].'px; height: '.$this_data['player_sprite_size'].'px; background-size: '.$this_data['player_image_width'].'px '.$this_data['player_image_height'].'px; ';

            // Generate the final markup for the canvas player
            ob_start();

                // Display this player's sprite in the active position
                global $flag_wap, $flag_ipad, $flag_iphone;
                if (!$flag_wap && !$flag_ipad && !$flag_iphone){
                    $shadow_offset_z = $this_data['canvas_offset_z'] - 1;
                    $shadow_scale = array(1.5, 0.25);
                    $shadow_skew = $this_data['player_direction'] == 'right' ? 30 : -30;
                    $shadow_translate = array(
                        ($this_data['player_direction'] == 'right' ? -1 : 1) * ($this_data['player_sprite_width'] + ceil($this_data['player_sprite_width'] * $shadow_scale[1])) + ceil($shadow_skew * $shadow_scale[1]),
                        $this_data['player_position'] == 'active' ? 115 : ceil($this_data['player_sprite_height'] * $shadow_scale[0]),
                        );
                    $shadow_styles = 'z-index: '.$shadow_offset_z.'; transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);  -webkit-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);  -moz-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);';
                    echo '<div data-shadowid="'.$this_data['player_id'].'" class="'.str_replace($this_data['player_token'], 'player', $this_data['player_class']).'" style="'.str_replace('players/', 'players_shadows/', $this_data['player_style']).$shadow_styles.'" data-type="'.$this_data['data_type'].'_shadow" data-size="'.$this_data['player_sprite_size'].'" data-direction="'.$this_data['player_direction'].'" data-frame="'.$this_data['player_frame'].'">'.$this_data['player_token'].'_shadow</div>';
                }
                echo '<div data-playerid="'.$this_data['player_id'].'" class="'.$this_data['player_class'].'" style="'.$this_data['player_style'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['player_sprite_size'].'" data-direction="'.$this_data['player_direction'].'" data-frame="'.$this_data['player_frame'].'" data-position="'.$this_data['player_position'].'">'.$this_data['player_title'].'</div>';

            // Collect the generated player markup
            $this_data['player_markup'] = trim(ob_get_clean());

        } else {

            // Define empty player markup
            $this_data['player_markup'] = '';

        }

        // Return the player canvas data
        return $this_data;

    }

    /**
     * Generate the console sprite markup for this player object
     * @param array $options
     * @return string
     */
    public function get_console_markup($options){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this player
        $this_data['player_frame'] = $this->get_frame();
        $this_data['player_frame'] = $this->get_frame_index_key($this_data['player_frame']);
        $this_data['player_title'] = $this->get_name();
        $this_data['player_token'] = $this->get_token();
        $this_data['player_float'] = $this->get_side();
        $this_data['player_direction'] = $this_data['player_float'] == 'left' ? 'right' : 'left';
        $this_data['player_position'] = 'active';

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['player_float'];
        $this_data['container_style'] = '';
        $this_data['player_class'] = 'sprite ';
        $this_data['player_style'] = '';
        $this_data['player_size'] = $this->player_image_size;
        $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/'.(!empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite').'_'.$this_data['player_direction'].'_'.$this_data['player_size'].'x'.$this_data['player_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['player_class'] .= 'sprite_'.$this_data['player_size'].'x'.$this_data['player_size'].' sprite_'.$this_data['player_size'].'x'.$this_data['player_size'].'_'.$this_data['player_frame'].' ';
        $this_data['player_class'] .= 'player_position_'.$this_data['player_position'].' ';
        $this_data['player_style'] .= 'background-image: url('.$this_data['player_image'].'); ';

        // Generate the final markup for the console player
        $this_data['player_markup'] = '';
        // If this was an undefined player, do not create markup
        if ($this->player_token != 'player'){
            $this_data['player_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
            $this_data['player_markup'] .= '<div class="'.$this_data['player_class'].'" style="'.$this_data['player_style'].'" title="'.$this_data['player_title'].'" data-tooltip-align="'.$this_data['player_float'].'">'.$this_data['player_title'].'</div>';
            $this_data['player_markup'] .= '</div>';
        }

        // Return the player console data
        return $this_data;

    }


    // -- SESSION FUNCTIONS -- //

    /**
     * Update internal variables for this player object
     * @return bool
     */
    public function update_variables(){

        // Import global variables
        $this_battle = rpg_battle::get_battle();

        // Define the dark element tokens
        $dark_element_tokens = array('dark-frag', 'dark-spire', 'dark-tower');

        // Calculate this player's count variables
        $count_abilities_total = $this->get_abilities_count();
        $count_items_total = $this->get_items_count();
        $count_dark_elements = 0;

        // Create the current robot value for calculations
        $value_current_robot = false;
        $value_current_robot_enter = $this->get_value('current_robot_enter');

        // Create the counter variables and defeault to zero
        $count_robots_masters_total = 0;
        $count_robots_mechas_total = 0;
        $count_robots_bosses_total = 0;
        $count_robots_total = 0;
        $count_robots_active = 0;
        $count_robots_disabled = 0;
        $count_robots_positions_active = 0;
        $count_robots_positions_bench = 0;

        // Create the value variables and default to empty
        $value_robots_total = array();
        $value_robots_active = array();
        $value_robots_disabled = array();
        $value_robots_position_active = array();
        $value_robots_position_bench = array();

        // Ensure this player has robots to loop over
        $player_robots = $this->get_robots();
        if (!empty($player_robots)){

            // Loop through each of the player's robots and update counters and values
            foreach ($player_robots AS $this_key => $this_robotinfo){
                if (empty($this_robotinfo['robot_id']) || empty($this_robotinfo['robot_token'])){ continue; }
                $count_robots_total++;

                // Load the robot object from the battle and update date
                $this_robot = $this_battle->get_robot($this_robotinfo['robot_id']);
                if (empty($this_robot)){ continue; }

                // Update the robot key position
                //$this_robot->set_key($this_key);

                // Collect key robot values for testing later
                $this_token = $this_robot->get_token();
                $this_lookup = $this_robot->get_lookup();
                $this_string = $this_robot->get_string();
                $this_position = $this_robot->get_position();
                $this_class = $this_robot->get_class();
                $this_status = $this_robot->get_status();

                // Update current robot var if active position
                if ($this_position == 'active'){ $value_current_robot = $this_string; }

                // Add this robot to applicable class vars
                if ($this_class == 'master'){ $count_robots_masters_total++; }
                elseif ($this_class == 'mecha'){ $count_robots_mechas_total++; }
                elseif ($this_class == 'boss'){ $count_robots_bosses_total++; }

                // Check if this robot is in active status
                if ($this_status == 'active'){

                    // Add robot to active status vars
                    $value_robots_active[] = $this_lookup;
                    $count_robots_active++;

                    // Add robot to active of bench position vars
                    if ($this_position == 'active'){
                        $value_robots_position_active[] = $this_lookup;
                        $count_robots_positions_active++;
                    } elseif ($this_position == 'bench'){
                        $value_robots_position_bench[] = $this_lookup;
                        $count_robots_positions_bench++;
                    }

                    // Add robot to dark element vars
                    if (in_array($this_token, $dark_element_tokens)){
                        $count_dark_elements++;
                    }

                }
                // Otherwise, if this robot is in disabled status
                elseif ($this_robot_status == 'disabled'){

                    // Add robot to disabled status vars
                    $value_robots_disabled[] = $this_lookup;
                    $count_robots_disabled++;

                }

            }

        }

        // If current robot was not found, set the enter to false
        if (!empty($value_current_robot)){ $value_current_robot_enter = false; }

        // Update this player's count variables
        $this->set_counter('abilities_total', $count_abilities_total);
        $this->set_counter('items_total', $count_items_total);
        $this->set_counter('dark_elements', $count_dark_elements);

        // Update the counter variables and defeault to zero
        $this->set_counter('robots_masters_total', $count_robots_masters_total);
        $this->set_counter('robots_mechas_total', $count_robots_mechas_total);
        $this->set_counter('robots_bosses_total', $count_robots_bosses_total);
        $this->set_counter('robots_total', $count_robots_total);
        $this->set_counter('robots_active', $count_robots_active);
        $this->set_counter('robots_disabled', $count_robots_disabled);
        $this->set_counter('robots_positions', 'active', $count_robots_positions_active);
        $this->set_counter('robots_positions', 'bench', $count_robots_positions_bench);

        // Update the current robot value for calculations
        $this->set_value('current_robot', $value_current_robot);
        $this->set_value('current_robot_enter', $value_current_robot_enter);

        // Update the value variables and default to empty
        $this->set_value('robots_total', $value_robots_total);
        $this->set_value('robots_active', $value_robots_active);
        $this->set_value('robots_disabled', $value_robots_disabled);
        $this->set_value('robots_positions', 'active', $value_robots_position_active);
        $this->set_value('robots_positions', 'bench', $value_robots_position_bench);

        // Return true on success
        return true;

    }

    /**
     * Reset internal variables for this battle object
     * @return array
     */
    public static function reset_variables($this_data){
        $this_data['player_flags'] = array();
        $this_data['player_counters'] = array();
        $this_data['player_values'] = array();
        $this_data['player_history'] = array();
        $this_data['player_name'] = $this_data['player_base_name'];
        $this_data['player_token'] = $this_data['player_base_token'];
        $this_data['player_description'] = $this_data['player_base_description'];
        //$this_data['player_robots'] = $this_data['player_base_robots'];
        //$this_data['player_abilities'] = $this_data['player_base_abilities'];
        //$this_data['player_items'] = $this_data['player_base_items'];
        //$this_data['player_quotes'] = $this_data['player_base_quotes'];
        //$this_data['player_rewards'] = $this_data['player_base_rewards'];
        //$this_data['player_starforce'] = $this_data['player_base_starforce'];
        return $this_data;
    }

    /**
     * Reset internal variables of this player object
     * @return rpg_robot
     */
    public function get_active_robot(){
        $this_battle = rpg_battle::get_battle();
        $this_robot = $this_battle->find_robot(array('player_id' => $this->player_id, 'robot_status' => 'active', 'robot_position' => 'active'));
        if (empty($this_robot)){
            $this_robot = $this_battle->find_robot(array('player_id' => $this->player_id, 'robot_status' => 'active'));
            if (empty($this_robot)){
                $this_robot = $this_battle->find_robot(array('player_id' => $this->player_id));
            }
        }
        return $this_robot;
    }

    /**
     * Update the session data for this player object
     * @return bool
     */
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION[$this->session_key][$this->{$this->session_id}] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal player fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_name' => $this->player_name,
            'player_token' => $this->player_token,
            'player_image' => $this->player_image,
            'player_image_size' => $this->player_image_size,
            'player_description' => $this->player_description,
            'player_energy' => $this->player_energy,
            'player_attack' => $this->player_attack,
            'player_defense' => $this->player_defense,
            'player_speed' => $this->player_speed,
            'player_robots' => $this->player_robots,
            'player_abilities' => $this->player_abilities,
            'player_items' => $this->player_items,
            'player_quotes' => $this->player_quotes,
            'player_rewards' => $this->player_rewards,
            'player_starforce' => $this->player_starforce,
            'player_points' => $this->player_points,
            'player_switch' => $this->player_switch,
            'player_frame' => $this->player_frame,
            'player_frame_index' => $this->player_frame_index,
            'player_frame_offset' => $this->player_frame_offset,
            'player_side' => $this->player_side,
            'player_controller' => $this->player_controller,
            'player_autopilot' => $this->player_autopilot,
            'player_next_action' => $this->player_next_action,
            'player_base_name' => $this->player_base_name,
            'player_base_image' => $this->player_base_image,
            'player_base_image_size' => $this->player_base_image_size,
            'player_base_description' => $this->player_base_description,
            'player_base_energy' => $this->player_base_energy,
            'player_base_attack' => $this->player_base_attack,
            'player_base_defense' => $this->player_base_defense,
            'player_base_speed' => $this->player_base_speed,
            //'player_base_robots' => $this->player_base_robots,
            //'player_base_abilities' => $this->player_base_abilities,
            //'player_base_items' => $this->player_base_items,
            //'player_base_quotes' => $this->player_base_quotes,
            //'player_base_rewards' => $this->player_base_rewards,
            //'player_base_starforce' => $this->player_base_starforce,
            //'player_base_points' => $this->player_base_points,
            //'player_base_switch' => $this->player_base_switch,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

    // Define a static function for printing out the player's database markup
    public static function print_database_markup($player_info, $print_options = array()){

        // Define the global variables
        global $db;
        global $mmrpg_index, $this_current_uri, $this_current_url;
        global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;

        // Define the print style defaults
        if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
        if ($print_options['layout_style'] == 'website'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        }

        // Collect the player sprite dimensions
        $player_image_size = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;
        $player_image_size_text = $player_image_size.'x'.$player_image_size;
        $player_image_token = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
        $player_type_token = !empty($player_info['player_type']) ? $player_info['player_type'] : 'none';

        // Define the sprite sheet alt and title text
        $player_sprite_size = $player_image_size * 2;
        $player_sprite_size_text = $player_sprite_size.'x'.$player_sprite_size;
        $player_sprite_title = $player_info['player_name'];
        //$player_sprite_title = $player_info['player_number'].' '.$player_info['player_name'];
        //$player_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

        // Define the sprite frame index for robot images
        $player_sprite_frames = array('base','taunt','victory','defeat','command','damage');

        // Define the markup variable
        $this_markup = '';

        // Start the output buffer
        ob_start();
        ?>
        <div class="database_container database_player_container" data-token="<?= $player_info['player_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">
            <a class="anchor" id="<?= $player_info['player_token']?>">&nbsp;</a>
            <div class="subbody event event_triple event_visible" data-token="<?= $player_info['player_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

                <?php if($print_options['show_mugshot']): ?>
                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <?php if($print_options['show_key'] !== false): ?>
                            <div class="mugshot player_type player_type_<?= !empty($player_info['player_type']) ? $player_info['player_type'] : 'none' ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.$player_info['player_key'] ?></div>
                        <?php endif; ?>
                        <div class="mugshot player_type player_type_<?= !empty($player_info['player_type']) ? $player_info['player_type'] : 'none' ?>"><div style="background-image: url(i/p/<?= $player_image_token ?>/mr<?= $player_image_size ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_player sprite_40x40 sprite_40x40_mug sprite_size_<?= $player_image_size_text ?> sprite_size_<?= $player_image_size_text ?>_mug player_status_active player_position_active"><?= $player_info['player_name']?>'s Mugshot</div></div>
                    </div>
                <?php endif; ?>


                <?php if($print_options['show_basics']): ?>
                    <h2 class="header header_left player_type_<?= $player_type_token ?>" style="margin-right: 0;">
                        <?php if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="database/players/<?= $player_info['player_token'] ?>/"><?= $player_info['player_name'] ?></a>
                        <?php else: ?>
                            <?= $player_info['player_name'] ?>&#39;s Data
                        <?php endif; ?>
                        <?php if (!empty($player_info['player_type'])): ?>
                            <span class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($player_info['player_type']) ?> Type</span>
                        <?php endif; ?>
                    </h2>
                    <div class="body body_left" style="margin-right: 0; padding: 2px 3px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="48%" />
                                <col width="1%" />
                                <col width="48%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Name :</label>
                                        <span class="player_name player_type"><?= $player_info['player_name']?></span>
                                    </td>
                                    <td class="middle">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Bonus :</label>
                                        <?php
                                            // Display any special boosts this player has
                                            if (!empty($player_info['player_energy'])){ echo '<span class="player_name player_type player_type_energy">Robot Energy +'.$player_info['player_energy'].'%</span>'; }
                                            elseif (!empty($player_info['player_attack'])){ echo '<span class="player_name player_type player_type_attack">Robot Attack +'.$player_info['player_attack'].'%</span>'; }
                                            elseif (!empty($player_info['player_defense'])){ echo '<span class="player_name player_type player_type_defense">Robot Defense +'.$player_info['player_defense'].'%</span>'; }
                                            elseif (!empty($player_info['player_speed'])){ echo '<span class="player_name player_type player_type_speed">Robot Speed +'.$player_info['player_speed'].'%</span>'; }
                                            else { echo '<span class="player_name player_type player_type_none">None</span>'; }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php if($print_options['show_quotes']): ?>
                            <table class="full" style="margin: 5px auto 10px;">
                                <colgroup>
                                    <col width="100%" />
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Start Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_start']) ? $player_info['player_quotes']['battle_start'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Taunt Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_taunt']) ? $player_info['player_quotes']['battle_taunt'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Victory Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_victory']) ? $player_info['player_quotes']['battle_victory'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Defeat Quote : </label>
                                            <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_defeat']) ? $player_info['player_quotes']['battle_defeat'] : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($print_options['show_sprites'] && (!isset($player_info['player_image_sheets']) || $player_info['player_image_sheets'] !== 0) && $player_image_token != 'player' ): ?>

                    <?php
                    // Start the output buffer and prepare to collect sprites
                    ob_start();

                    // Define the alts we'll be looping through for this player
                    $temp_alts_array = array();
                    $temp_alts_array[] = array('token' => '', 'name' => $player_info['player_name'], 'summons' => 0);
                    // Append predefined alts automatically, based on the player image alt array
                    if (!empty($player_info['player_image_alts'])){
                        $temp_alts_array = array_merge($temp_alts_array, $player_info['player_image_alts']);
                    }
                    // Otherwise, if this is a copy player, append based on all the types in the index
                    elseif ($player_info['player_type'] == 'copy' && preg_match('/^(mega-man|proto-man|bass)$/i', $player_info['player_token'])){
                        foreach ($mmrpg_database_types AS $type_token => $type_info){
                            if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
                            $temp_alts_array[] = array('token' => $type_token, 'name' => $player_info['player_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
                        }
                    }

                    // Loop through the alts and display images for them (yay!)
                    foreach ($temp_alts_array AS $alt_key => $alt_info){

                        // Define the current image token with alt in mind
                        $temp_player_image_token = $player_image_token;
                        $temp_player_image_token .= !empty($alt_info['token']) ? '_'.$alt_info['token'] : '';
                        $temp_player_image_token .= !empty($alt_info['sheet']) ? '-'.$alt_info['sheet'] : '';
                        $temp_player_image_name = $alt_info['name'];
                        // Update the alt array with this info
                        $temp_alts_array[$alt_key]['image'] = $temp_player_image_token;

                        // Collect the number of sheets
                        $temp_sheet_number = !empty($player_info['player_image_sheets']) ? $player_info['player_image_sheets'] : 1;

                        // Loop through the different frames and print out the sprite sheets
                        foreach (array('right', 'left') AS $temp_direction){
                            $temp_direction2 = substr($temp_direction, 0, 1);
                            $temp_embed = '[player:'.$temp_direction.']{'.$temp_player_image_token.'}';
                            $temp_title = $temp_player_image_name.' | Mugshot Sprite '.ucfirst($temp_direction);
                            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                            $temp_label = 'Mugshot '.ucfirst(substr($temp_direction, 0, 1));
                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_player_image_token.'" data-frame="mugshot" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$player_sprite_size.'px; height: '.$player_sprite_size.'px; overflow: hidden;">';
                                echo '<img style="margin-left: 0;" data-tooltip="'.$temp_title.'" src="i/p/'.$temp_player_image_token.'/m'.$temp_direction2.$player_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                            echo '</div>';
                        }


                        // Loop through the different frames and print out the sprite sheets
                        foreach ($player_sprite_frames AS $this_key => $this_frame){
                            $margin_left = ceil((0 - $this_key) * $player_sprite_size);
                            $frame_relative = $this_frame;
                            //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($player_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                            $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_direction2 = substr($temp_direction, 0, 1);
                                $temp_embed = '[player:'.$temp_direction.':'.$frame_relative.']{'.$temp_player_image_token.'}';
                                $temp_title = $temp_player_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                //$image_token = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
                                //if ($temp_sheet > 1){ $temp_player_image_token .= '-'.$temp_sheet; }
                                echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_player_image_token.'" data-frame="'.$frame_relative.'" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$player_sprite_size.'px; height: '.$player_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="i/p/'.$temp_player_image_token.'/s'.$temp_direction2.$player_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                        }

                    }

                    // Collect the sprite markup from the output buffer for later
                    $this_sprite_markup = ob_get_clean();

                    ?>

                    <h2 id="sprites" class="header header_full player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $player_info['player_name']?>&#39;s Sprites
                        <span class="header_links image_link_container">
                            <span class="images" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>"><?php
                                // Loop though and print links for the alts
                                foreach ($temp_alts_array AS $alt_key => $alt_info){
                                    $alt_type = '';
                                    $alt_style = '';
                                    $alt_title = $alt_info['name'];
                                    if (preg_match('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', $alt_info['name'])){
                                        $alt_type = strtolower(preg_replace('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', '$1', $alt_info['name']));
                                        $alt_name = '&bull;'; //ucfirst($alt_type); //substr(ucfirst($alt_type), 0, 2);
                                        $alt_type = 'player_type player_type_'.$alt_type.' core_type ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key == 0 ? $player_info['player_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : ''); //$alt_key == 0 ? $player_info['player_name'] : $player_info['player_name'].' Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'player_type player_type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($player_info['player_type'] == 'copy' && $alt_key == 0){ $alt_type = 'player_type player_type_empty '; }
                                    }
                                    echo '<a href="#" data-tooltip="'.$alt_title.'" class="link link_image '.($alt_key == 0 ? 'link_active ' : '').'" data-image="'.$alt_info['image'].'">';
                                    echo '<span class="'.$alt_type.'" style="'.$alt_style.'">'.$alt_name.'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                            <span class="pipe" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>">|</span>
                            <span class="directions"><?php
                                // Loop though and print links for the alts
                                foreach (array('right', 'left') AS $temp_key => $temp_direction){
                                    echo '<a href="#" data-tooltip="'.ucfirst($temp_direction).' Facing Sprites" class="link link_direction '.($temp_key == 0 ? 'link_active' : '').'" data-direction="'.$temp_direction.'">';
                                    echo '<span class="ability_type ability_type_empty" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ">'.ucfirst($temp_direction).'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                        </span>
                    </h2>
                    <div id="sprites_body" class="body body_full" style="margin: 0; padding: 10px; min-height: auto;">
                        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                            <?= $this_sprite_markup ?>
                        </div>
                        <?php
                        // Define the editor title based on ID
                        $temp_editor_title = 'Undefined';
                        if (!empty($player_info['player_image_editor'])){
                            if ($player_info['player_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                            elseif ($player_info['player_image_editor'] == 110){ $temp_editor_title = 'MetalMarioX100 / EliteP1'; }
                            elseif ($player_info['player_image_editor'] == 18){ $temp_editor_title = 'Sean Adamson / MetalMan'; }
                        } else {
                            $temp_editor_title = 'Adrian Marceau / Ageman20XX';
                        }
                        ?>
                        <p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 10px; margin-top: 6px;">Sprite Editing by <strong><?= $temp_editor_title ?></strong> <span style="color: #565656;"> | </span> Original Artwork by <strong>Capcom</strong></p>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_description'] && !empty($player_info['player_description2'])): ?>

                    <h2 class="header header_left player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left; ">
                        <?= $player_info['player_name'] ?>&#39;s Description
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="player_description" style="text-align: justify; padding: 0 4px;"><?= $player_info['player_description2'] ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="database/players/<?= $player_info['player_token'] ?>/" rel="permalink">+ Permalink</a>

                <?php elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="database/players/<?= $player_info['player_token'] ?>/" rel="permalink">+ View More</a>

                <?php endif; ?>

            </div>
        </div>
        <?php
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());
        // Return the generated markup
        return $this_markup;

    }

    // Define a static function for printing out the player's editor markup
    public static function print_editor_markup($player_info){
        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_fields, $global_allow_editing;
        global $allowed_edit_data_count, $allowed_edit_player_count, $first_player_token;
        global $key_counter, $player_key, $player_counter, $player_rewards, $player_field_rewards, $player_item_rewards, $temp_player_totals, $player_options_markup;
        global $mmrpg_database_robots, $mmrpg_database_items;
        $session_token = rpg_game::session_token();

        // If either fo empty, return error
        if (empty($player_info)){ return 'error:player-empty'; }

        // Collect the approriate database indexes
        if (empty($mmrpg_database_robots)){ $mmrpg_database_robots = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token'); }
        if (empty($mmrpg_database_items)){ $mmrpg_database_items = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_class = 'item' AND ability_flag_complete = 1;", 'ability_token'); }

        // Define the quick-access variables for later use
        $player_token = $player_info['player_token'];
        if (!isset($first_player_token)){ $first_player_token = $player_token; }

        // Define the player's image and size if not defined
        $player_info['player_image'] = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
        $player_info['player_image_size'] = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;

        // Define the player's battle points total, battles complete, and other details
        $player_info['player_points'] = rpg_game::player_points($player_token);
        $player_info['player_battles_complete'] = rpg_prototype::battles_complete($player_token);
        $player_info['player_battles_complete_total'] = rpg_prototype::battles_complete($player_token, false);
        $player_info['player_battles_failure'] = rpg_prototype::battles_failure($player_token);
        $player_info['player_battles_failure_total'] = rpg_prototype::battles_failure($player_token, false);
        $player_info['player_robots_count'] = 0;
        $player_info['player_abilities_count'] = rpg_game::abilities_unlocked($player_token);
        $player_info['player_field_stars'] = rpg_game::stars_unlocked($player_token, 'field');
        $player_info['player_fusion_stars'] = rpg_game::stars_unlocked($player_token, 'fusion');
        $player_info['player_screw_counter'] = 0;
        $player_info['player_heart_counter'] = 0;
        // Define the player's experience points total
        $player_info['player_experience'] = 0;
        // Collect this player's current defined omega item list
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
            //$debug_experience_sum = $player_token.' : ';
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $temp_player => $temp_player_info){
                if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'])){
                    $temp_player_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'];
                    $temp_player_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'];
                    if (empty($temp_player_robot_rewards) || empty($temp_player_robot_settings)){
                        unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots']);
                        unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots']);
                        continue;
                    }
                    foreach ($temp_player_robot_rewards AS $temp_key => $temp_robot_info){
                        if (empty($temp_robot_info['robot_token'])){
                            unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_key]);
                            unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_key]);
                            continue;
                        }
                        $temp_robot_settings = $temp_player_robot_settings[$temp_robot_info['robot_token']];
                        $temp_robot_rewards = $temp_player_robot_settings[$temp_robot_info['robot_token']];
                        // If this robot is not owned by the player, skip it as it doesn't count towards their totals
                        if (empty($temp_robot_settings['original_player']) && $temp_player != $player_token){ continue; }
                        elseif (empty($temp_robot_settings['original_player'])){ $temp_robot_settings['original_player'] = $temp_player; }
                        if ($temp_robot_settings['original_player'] != $player_token){ continue; }
                        //$debug_experience_sum .= $temp_robot_info['robot_token'].', ';
                        $player_info['player_robots_count']++;
                        if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
                        if (!empty($temp_robot_info['robot_experience'])){ $player_info['player_experience'] += $temp_robot_info['robot_experience']; }
                    }
                }
            }
            //die($debug_experience_sum);
        }

        // Collect this player's current field selection from the omega session
        $temp_session_key = $player_info['player_token'].'_target-robot-omega_prototype';
        $player_info['target_robot_omega'] = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        $player_info['player_fields_current'] = array();
        //die('<pre>$player_info[\'target_robot_omega\'] = '.print_r($player_info['target_robot_omega'], true).'</pre>');
        if (count($player_info['target_robot_omega']) == 2){ $player_info['target_robot_omega'] = array_shift($player_info['target_robot_omega']); }
        foreach ($player_info['target_robot_omega'] AS $key => $info){
            $field = rpg_field::get_index_info($info['field']);
            if (empty($field)){ continue; }
            $player_info['player_fields_current'][] = $field;
        }

        // Define this player's stat type boost for display purposes
        $player_info['player_stat_type'] = '';
        if (!empty($player_info['player_energy'])){ $player_info['player_stat_type'] = 'energy'; }
        elseif (!empty($player_info['player_attack'])){ $player_info['player_stat_type'] = 'attack'; }
        elseif (!empty($player_info['player_defense'])){ $player_info['player_stat_type'] = 'defense'; }
        elseif (!empty($player_info['player_speed'])){ $player_info['player_stat_type'] = 'speed'; }

        // Define whether or not field switching is enabled
        $temp_allow_field_switch = rpg_prototype::campaign_complete($player_info['player_token']) || rpg_prototype::campaign_complete();

        // Collect a temp robot object for printing items
        if ($player_info['player_token'] == 'dr-light'){ $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['mega-man']); }
        elseif ($player_info['player_token'] == 'dr-wily'){ $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['bass']); }
        elseif ($player_info['player_token'] == 'dr-cossack'){ $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['proto-man']); }

        // Define the markup variable
        $this_markup = '';

        // Start the output buffer
        ob_start();

        // DEBUG
        //die(print_r($player_field_rewards, true));

            ?>
            <div class="event event_double event_<?= $player_key == $first_player_token ? 'visible' : 'hidden' ?>" data-token="<?= $player_info['player_token'].'_'.$player_info['player_token']?>">
                <div class="this_sprite sprite_left" style="height: 40px;">
                    <?php $temp_margin = -1 * ceil(($player_info['player_image_size'] - 40) * 0.5); ?>
                    <div style="margin-top: <?= $temp_margin ?>px; margin-bottom: <?= $temp_margin * 3 ?>px; background-image: url(i/p/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/mr<?= $player_info['player_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_mug player_status_active player_position_active"><?= $player_info['player_name']?></div>
                </div>
                <div class="header header_left player_type player_type_<?= !empty($player_info['player_stat_type']) ? $player_info['player_stat_type'] : 'none' ?>" style="margin-right: 0;"><?= $player_info['player_name']?>&#39;s Data <span class="player_type"><?= !empty($player_info['player_stat_type']) ? ucfirst($player_info['player_stat_type']) : 'Neutral' ?> Type</span></div>
                <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
                    <table class="full" style="margin-bottom: 5px;">
                        <colgroup>
                            <col width="48.5%" />
                            <col width="1%" />
                            <col width="48.5%" />
                        </colgroup>
                        <tbody>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Name :</label>
                                    <span class="player_name player_type player_type_none"><?= $player_info['player_name']?></span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label style="display: block; float: left;">Bonus :</label>
                                    <?php
                                        // Display any special boosts this player has
                                        if (!empty($player_info['player_stat_type'])){ echo '<span class="player_name player_type player_type_'.$player_info['player_stat_type'].'">Robot '.ucfirst($player_info['player_stat_type']).' +'.$player_info['player_'.$player_info['player_stat_type']].'%</span>'; }
                                        else { echo '<span class="player_name player_type player_type_none">None</span>'; }
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Exp Points :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_experience']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_experience'], 0, '.', ',') ?> EXP</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Robots :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_robots_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_robots_count'].' '.($player_info['player_robots_count'] == 1 ? 'Robot' : 'Robots') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Battle Points :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_points']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_points'], 0, '.', ',') ?> BP</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Abilities :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_abilities_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_abilities_count'].' '.($player_info['player_abilities_count'] == 1 ? 'Ability' : 'Abilities') ?></span>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Missions Completed :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete'] ?> Missions</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Total Victories :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete_total']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete_total'] ?> Victories</span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Missions Failed :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure'] ?> Missions</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Total Defeats :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure_total']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure_total'] ?> Defeats</span>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <?php if(!empty($player_info['player_field_stars'])): ?>
                                    <label style="display: block; float: left;">Field Stars :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_field_stars']) ? 'electric' : 'empty' ?>"><?= $player_info['player_field_stars'].' '.($player_info['player_field_stars'] == 1 ? 'Star' : 'Stars') ?></span>
                                    <?php else: ?>
                                    <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
                                    <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <?php if(!empty($player_info['player_fusion_stars'])): ?>
                                    <label style="display: block; float: left;">Fusion Stars :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_fusion_stars']) ? 'time' : 'empty' ?>"><?= $player_info['player_fusion_stars'].' '.($player_info['player_fusion_stars'] == 1 ? 'Star' : 'Stars') ?></span>
                                    <?php else: ?>
                                    <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
                                    <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        </tbody>
                    </table>



                    <?php if(false && !empty($player_item_rewards)){ ?>

                        <table class="full">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right" style="padding-top: 4px;">
                                    <label class="item_header">Player Items :</label>
                                        <div class="item_container" style="height: auto;">
                                        <?php

                                        // Define the array to hold ALL the reward option markup
                                        $item_rewards_options = '';
                                        // Collect this player's item rewards and add them to the dropdown
                                        //$player_item_rewards = !empty($player_rewards['player_items']) ? $player_rewards['player_items'] : array();
                                        //if (!empty($player_item_rewards)){ sort($player_item_rewards); }

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_item_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                                        //echo 'before:'.implode(',', array_keys($debug_tokens)).'<br />';

                                        // Sort the item index based on item group
                                        uasort($player_item_rewards, array('rpg_functions', 'items_sort_for_editor'));

                                        // DEBUG
                                        //echo 'after:'.implode(',', array_keys($player_item_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_item_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                                        //echo 'after:'.implode(',', $debug_tokens).'<br />';

                                        // Dont' bother generating option dropdowns if editing is disabled
                                        if ($global_allow_editing){
                                            $player_item_rewards_options = array();
                                            foreach ($player_item_rewards AS $temp_item_key => $temp_item_info){
                                                if (empty($temp_item_info['ability_token'])){ continue; }
                                                $temp_token = $temp_item_info['ability_token'];
                                                $temp_item_info = rpg_ability::parse_index_info($mmrpg_database_items[$temp_token]);
                                                $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_item_info);
                                                if (!empty($temp_option_markup)){ $player_item_rewards_options[] = $temp_option_markup; }
                                            }
                                            $player_item_rewards_options = '<optgroup label="Player Items">'.implode('', $player_item_rewards_options).'</optgroup>';
                                            $item_rewards_options .= $player_item_rewards_options;
                                            /*
                                            // Collect this robot's item rewards and add them to the dropdown
                                            $player_item_rewards = !empty($player_rewards['player_items']) ? $player_rewards['player_items'] : array();
                                            $player_item_settings = !empty($player_settings['player_items']) ? $player_settings['player_items'] : array();
                                            foreach ($player_item_settings AS $token => $info){ if (empty($player_item_rewards[$token])){ $player_item_rewards[$token] = $info; } }
                                            if (!empty($player_item_rewards)){ sort($player_item_rewards); }
                                            $player_item_rewards_options = array();
                                            foreach ($player_item_rewards AS $temp_item_info){
                                                if (empty($temp_item_info['ability_token'])){ continue; }
                                                $temp_token = $temp_item_info['ability_token'];
                                                $temp_item_info = rpg_ability::parse_index_info($mmrpg_database_items[$temp_token]);
                                                $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_item_info);
                                                if (!empty($temp_option_markup)){ $player_item_rewards_options[] = $temp_option_markup; }
                                            }
                                            $player_item_rewards_options = '<optgroup label="Player Items">'.implode('', $player_item_rewards_options).'</optgroup>';
                                            $item_rewards_options .= $player_item_rewards_options;
                                            */

                                            // Add an option at the bottom to remove the ability
                                            $item_rewards_options .= '<optgroup label="Item Actions">';
                                            $item_rewards_options .= '<option value="" title="">- Remove Item -</option>';
                                            $item_rewards_options .= '</optgroup>';
                                            }

                                        // Loop through the robot's current items and list them one by one
                                        $empty_item_counter = 0;
                                        $temp_string = array();
                                        $temp_inputs = array();
                                        $item_key = 0;
                                        if (!empty($player_info['player_items_current'])){
                                            // DEBUG
                                            //echo 'robot-ability:';
                                            foreach ($player_info['player_items_current'] AS $key => $player_item){
                                                if (empty($player_item['ability_token'])){ continue; }
                                                elseif ($player_item['ability_token'] == '*'){ continue; }
                                                elseif ($player_item['ability_token'] == 'ability'){ continue; }
                                                elseif ($item_key > 7){ continue; }
                                                $this_item = rpg_ability::parse_index_info($mmrpg_database_items[$player_item['ability_token']]);
                                                if (empty($this_item)){ continue; }
                                                $this_item_token = $this_item['ability_token'];
                                                $this_item_name = $this_item['ability_name'];
                                                $this_item_type = !empty($this_item['ability_type']) ? $this_item['ability_type'] : false;
                                                $this_item_type2 = !empty($this_item['ability_type2']) ? $this_item['ability_type2'] : false;
                                                if (!empty($this_item_type) && !empty($mmrpg_index['types'][$this_item_type])){
                                                    $this_item_type = $mmrpg_index['types'][$this_item_type]['type_name'].' Type';
                                                    if (!empty($this_item_type2) && !empty($mmrpg_index['types'][$this_item_type2])){
                                                        $this_item_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_item_type2]['type_name'].' Type', $this_item_type);
                                                    }
                                                } else {
                                                    $this_item_type = '';
                                                }
                                                $this_item_energy = isset($this_item['ability_energy']) ? $this_item['ability_energy'] : 4;
                                                $this_item_damage = !empty($this_item['ability_damage']) ? $this_item['ability_damage'] : 0;
                                                $this_item_damage2 = !empty($this_item['ability_damage2']) ? $this_item['ability_damage2'] : 0;
                                                $this_item_damage_percent = !empty($this_item['ability_damage_percent']) ? true : false;
                                                $this_item_damage2_percent = !empty($this_item['ability_damage2_percent']) ? true : false;
                                                if ($this_item_damage_percent && $this_item_damage > 100){ $this_item_damage = 100; }
                                                if ($this_item_damage2_percent && $this_item_damage2 > 100){ $this_item_damage2 = 100; }
                                                $this_item_recovery = !empty($this_item['ability_recovery']) ? $this_item['ability_recovery'] : 0;
                                                $this_item_recovery2 = !empty($this_item['ability_recovery2']) ? $this_item['ability_recovery2'] : 0;
                                                $this_item_recovery_percent = !empty($this_item['ability_recovery_percent']) ? true : false;
                                                $this_item_recovery2_percent = !empty($this_item['ability_recovery2_percent']) ? true : false;
                                                if ($this_item_recovery_percent && $this_item_recovery > 100){ $this_item_recovery = 100; }
                                                if ($this_item_recovery2_percent && $this_item_recovery2 > 100){ $this_item_recovery2 = 100; }
                                                $this_item_accuracy = !empty($this_item['ability_accuracy']) ? $this_item['ability_accuracy'] : 0;
                                                $this_item_description = !empty($this_item['ability_description']) ? $this_item['ability_description'] : '';
                                                $this_item_description = str_replace('{DAMAGE}', $this_item_damage, $this_item_description);
                                                $this_item_description = str_replace('{RECOVERY}', $this_item_recovery, $this_item_description);
                                                $this_item_description = str_replace('{DAMAGE2}', $this_item_damage2, $this_item_description);
                                                $this_item_description = str_replace('{RECOVERY2}', $this_item_recovery2, $this_item_description);
                                                $this_item_title = rpg_ability::print_editor_title_markup($robot_info, $this_item);
                                                $this_item_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_item_title));
                                                $this_item_title_tooltip = htmlentities($this_item_title, ENT_QUOTES, 'UTF-8');
                                                $this_item_title_html = str_replace(' ', '&nbsp;', $this_item_name);
                                                $temp_select_options = str_replace('value="'.$this_item_token.'"', 'value="'.$this_item_token.'" selected="selected" disabled="disabled"', $item_rewards_options);
                                                $this_item_title_html = '<label style="background-image: url(i/a/'.$this_item_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_item_title_html.'</label>';
                                                if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="ability_name ability_type ability_type_'.(!empty($this_item['ability_type']) ? $this_item['ability_type'] : 'none').(!empty($this_item['ability_type2']) ? '_'.$this_item['ability_type2'] : '').'" style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-item="'.$this_item_token.'" title="'.$this_item_title_plain.'" data-tooltip="'.$this_item_title_tooltip.'">'.$this_item_title_html.'</a>';
                                                $item_key++;
                                            }

                                            if ($item_key <= 7){
                                                for ($item_key; $item_key <= 7; $item_key++){
                                                    $empty_item_counter++;
                                                    if ($empty_item_counter >= 2){ $empty_item_disable = true; }
                                                    else { $empty_item_disable = false; }
                                                    $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $item_rewards_options);
                                                    $this_item_title_html = '<label>-</label>';
                                                    if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" '.($empty_item_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                    $temp_string[] = '<a class="ability_name " style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_item_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-item="" title="" data-tooltip="">'.$this_item_title_html.'</a>';
                                                }
                                            }


                                        } else {

                                            for ($item_key = 0; $item_key <= 7; $item_key++){
                                                $empty_item_counter++;
                                                if ($empty_item_counter >= 2){ $empty_item_disable = true; }
                                                else { $empty_item_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $item_rewards_options);
                                                $this_item_title_html = '<label>-</label>';
                                                if ($global_allow_editing){ $this_item_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_item_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="ability_name " style="'.(($item_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_item_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_item_title_html.'</a>';
                                            }

                                        }
                                        // DEBUG
                                        //echo 'temp-string:';
                                        echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                        // DEBUG
                                        //echo '<br />temp-inputs:';
                                        echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                                        // DEBUG
                                        //echo '<br />';



                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <?php } ?>

                    <?php if(!empty($player_field_rewards) && rpg_prototype::campaign_complete($player_info['player_token'])){ ?>

                        <table class="full">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right" style="padding-top: 4px;">
                                        <label class="field_header"><?= $global_allow_editing ? 'Edit ' : '' ?>Player Fields :</label>
                                        <div class="field_container" style="height: auto;">
                                        <?php

                                        // Define the array to hold ALL the reward option markup
                                        $field_rewards_options = '';
                                        // Collect this player's field rewards and add them to the dropdown
                                        //$player_field_rewards = !empty($player_rewards['player_fields']) ? $player_rewards['player_fields'] : array();
                                        //if (!empty($player_field_rewards)){ sort($player_field_rewards); }

                                        // DEBUG
                                        //echo 'start:player_field_rewards:<pre style="font-size: 80%;">'.print_r($player_field_rewards, true).'</pre><br />';

                                        // DEBUG
                                        //echo 'before:player_field_rewards(keys):'.implode(',', array_keys($player_field_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                                        //echo 'before:player_field_rewards(field_tokens):'.implode(',', array_values($debug_tokens)).'<br />';

                                        // Sort the field index based on field number
                                        uasort($player_field_rewards, array('rpg_functions', 'fields_sort_for_editor'));

                                        // DEBUG
                                        //echo 'after:player_field_rewards(keys):'.implode(',', array_keys($player_field_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                                        //echo 'after:player_field_rewards(field_tokens):'.implode(',', array_values($debug_tokens)).'<br />';

                                        // Don't bother generating the option markup if disabled editing
                                        if ($global_allow_editing){
                                            // Define the field group index for displau
                                            $temp_group_index = array('MMRPG' => 'Mega Man RPG Fields', 'MM00' => 'Mega Man Bonus Fields', 'MM01' => 'Mega Man 1 Fields', 'MM02' => 'Mega Man 2 Fields', 'MM03' => 'Mega Man 3 Fields', 'MM04' => 'Mega Man 4 Fields', 'MM05' => 'Mega Man 5 Fields', 'MM06' => 'Mega Man 6 Fields', 'MM07' => 'Mega Man 7 Fields', 'MM08' => 'Mega Man 8 Fields', 'MM09' => 'Mega Man 9 Fields', 'MM10' => 'Mega Man 10 Fields');
                                            // Loop through the group index and display any fields that match
                                            $player_field_rewards_backup = $player_field_rewards;
                                            foreach ($temp_group_index AS $group_key => $group_name){
                                                $player_field_rewards_options = array();
                                                foreach ($player_field_rewards_backup AS $temp_field_key => $temp_field_info){
                                                    if (empty($temp_field_info['field_game']) || $temp_field_info['field_game'] != $group_key){ continue; }
                                                    $temp_option_markup = rpg_field::print_editor_option_markup($temp_field_info);
                                                    if (!empty($temp_option_markup)){ $player_field_rewards_options[] = $temp_option_markup; }
                                                    unset($player_field_rewards_backup[$temp_field_key]);
                                                }
                                                if (empty($player_field_rewards_options)){ continue; }
                                                $player_field_rewards_options = '<optgroup label="'.$group_name.'">'.implode('', $player_field_rewards_options).'</optgroup>';
                                                $field_rewards_options .= $player_field_rewards_options;
                                            }

                                        }



                                        // Add an option at the bottom to remove the field
                                        //$field_rewards_options .= '<optgroup label="Field Actions">';
                                        //$field_rewards_options .= '<option value="" title="">- Remove Field -</option>';
                                        //$field_rewards_options .= '</optgroup>';

                                        // Loop through the player's current fields and list them one by one
                                        $empty_field_counter = 0;
                                        $temp_string = array();
                                        $temp_inputs = array();
                                        $field_key = 0;
                                        if (!empty($player_info['player_fields_current'])){
                                            // DEBUG
                                            //echo 'player-field:';
                                            $rpg_field_index = rpg_field::get_index();
                                            $player_info['player_fields_current'] = $player_info['player_fields_current']; //array_reverse($player_info['player_fields_current']);
                                            foreach ($player_info['player_fields_current'] AS $player_field){
                                                if ($player_field['field_token'] == '*'){ continue; }
                                                elseif (!isset($rpg_field_index[$player_field['field_token']])){ continue; }
                                                elseif ($field_key > 7){ continue; }

                                                $this_field = rpg_field::parse_index_info($rpg_field_index[$player_field['field_token']]);
                                                $this_field_token = $this_field['field_token'];
                                                $this_robot_token = $this_field['field_master'];
                                                $this_robot = rpg_robot::parse_index_info($mmrpg_database_robots[$this_robot_token]);
                                                $this_field_name = $this_field['field_name'];
                                                $this_field_type = !empty($this_field['field_type']) ? $this_field['field_type'] : false;
                                                $this_field_type2 = !empty($this_field['field_type2']) ? $this_field['field_type2'] : false;
                                                if (!empty($this_field_type) && !empty($mmrpg_index['types'][$this_field_type])){
                                                    $this_field_type = $mmrpg_index['types'][$this_field_type]['type_name'].' Type';
                                                    if (!empty($this_field_type2) && !empty($mmrpg_index['types'][$this_field_type2])){
                                                        $this_field_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_field_type2]['type_name'].' Type', $this_field_type);
                                                    }
                                                } else {
                                                    $this_field_type = '';
                                                }
                                                $this_field_description = !empty($this_field['field_description']) ? $this_field['field_description'] : '';
                                                $this_field_title = rpg_field::print_editor_title_markup($this_field);
                                                $this_field_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_field_title));
                                                $this_field_title_tooltip = htmlentities($this_field_title, ENT_QUOTES, 'UTF-8');
                                                $this_field_title_html = str_replace(' ', '&nbsp;', $this_field_name);
                                                $temp_select_options = str_replace('value="'.$this_field_token.'"', 'value="'.$this_field_token.'" selected="selected" disabled="disabled"', $field_rewards_options);
                                                $temp_field_type_class = 'field_type_'.(!empty($this_field['field_type']) ? $this_field['field_type'] : 'none').(!empty($this_field['field_type2']) ? '_'.$this_field['field_type2'] : '');
                                                if ($global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="">'.$this_field_title_html.'</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                                                elseif (!$global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                                                else { $this_field_title_html = '<label class="field_type '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                                                $temp_string[] = '<a class="field_name field_type '.$temp_field_type_class.'" style="background-image: url(i/f/'.$this_field_token.'/bfp.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$temp_allow_field_switch || !$global_allow_editing ? 'cursor: default !important; ' : '').(!$temp_allow_field_switch ? 'opacity: 0.50; filter: alpha(opacity=50); ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="'.$this_field_token.'" data-tooltip="'.$this_field_title_tooltip.'">'.$this_field_title_html.'</a>';

                                                $field_key++;
                                            }

                                            if ($field_key <= 7){
                                                for ($field_key; $field_key <= 7; $field_key++){
                                                    $empty_field_counter++;
                                                    if ($empty_field_counter >= 2){ $empty_field_disable = true; }
                                                    else { $empty_field_disable = false; }
                                                    $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $field_rewards_options);
                                                    $this_field_title_html = '<label>-</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_field_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                                                    $temp_string[] = '<a class="field_name " style="'.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_field_disable ? 'opacity:0.25; ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="" title="">'.$this_field_title_html.'</a>';
                                                }
                                            }


                                        } else {
                                            for ($field_key = 0; $field_key <= 7; $field_key++){
                                                $empty_field_counter++;
                                                if ($empty_field_counter >= 2){ $empty_field_disable = true; }
                                                else { $empty_field_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $field_rewards_options);
                                                $this_field_title_html = '<label>-</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_field_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                                                $temp_string[] = '<a class="field_name " style="'.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_field_disable ? 'opacity:0.25; ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="" title="">'.$this_field_title_html.'</a>';
                                            }

                                        }
                                        // DEBUG
                                        //echo 'temp-string:';
                                        echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                        // DEBUG
                                        //echo '<br />temp-inputs:';
                                        echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                                        // DEBUG
                                        //echo '<br />';
                                        // Collect the available star counts for this player
                                        $temp_star_counts = rpg_game::stars_available($player_token);
                                        ?>
                                        <div class="field_stars">
                                            <label class="label">stars</label>
                                            <span class="star star_field" data-star="field"><?= $temp_star_counts['field'] ?> field</span>
                                            <span class="star star_fusion" data-star="fusion"><?= $temp_star_counts['fusion'] ?> fusion</span>
                                        </div>
                                        <?php
                                        // Print the sort wrapper and options if allowed
                                        if ($global_allow_editing){
                                            ?>
                                            <div class="field_tools">
                                                <label class="label">tools</label>
                                                <a class="tool tool_shuffle" data-tool="shuffle" data-player="<?= $player_token ?>">shuffle</a>
                                                <a class="tool tool_randomize" data-tool="randomize" data-player="<?= $player_token ?>">randomize</a>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <?php }?>


                </div>
            </div>
            <?php
            $key_counter++;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

}
?>