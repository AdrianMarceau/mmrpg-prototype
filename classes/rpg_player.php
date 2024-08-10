<?
/**
 * Mega Man RPG Player Object
 * <p>The base class for all player objects in the Mega Man RPG Prototype.</p>
 */
class rpg_player extends rpg_object {

    // Define the constructor class
    public function __construct(){

        // Update the session keys for this object
        $this->session_key = 'PLAYERS';
        $this->session_token = 'player_token';
        $this->session_id = 'player_id';
        $this->class = 'player';
        $this->multi = 'players';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal battle pointer
        $this->battle = isset($args[0]) ? $args[0] : array();
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Collect current player data from the function if available
        $this_playerinfo = isset($args[1]) ? $args[1] : array('player_id' => 0, 'player_token' => 'player');

        // Now load the player data from the session or index
        $this->player_load($this_playerinfo);

        // Return true on success
        return true;

    }

    // Define a public function for manually loading data
    public function player_load($this_playerinfo){

        // Pull in the global index
        static $mmrpg_index_players;
        if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect current player data from the session if available
        $this_playerinfo_backup = $this_playerinfo;
        if (isset($_SESSION['PLAYERS'][$this_playerinfo['player_id']])){
            $this_playerinfo = $_SESSION['PLAYERS'][$this_playerinfo['player_id']];
        }
        // Otherwise, collect player data from the index
        elseif (isset($mmrpg_index_players[$this_playerinfo['player_token']])){
            // Copy over the base contents from the players index
            $this_playerinfo = $mmrpg_index_players[$this_playerinfo['player_token']];
        }
        $this_playerinfo = array_replace($this_playerinfo, $this_playerinfo_backup);

        // Define the internal player values using the collected array
        $this->flags = isset($this_playerinfo['flags']) ? $this_playerinfo['flags'] : array();
        $this->counters = isset($this_playerinfo['counters']) ? $this_playerinfo['counters'] : array();
        $this->values = isset($this_playerinfo['values']) ? $this_playerinfo['values'] : array();
        $this->history = isset($this_playerinfo['history']) ? $this_playerinfo['history'] : array();
        $this->user_id = isset($this_playerinfo['user_id']) ? $this_playerinfo['user_id'] : 0;
        $this->user_token = isset($this_playerinfo['user_token']) ? $this_playerinfo['user_token'] : '';
        $this->user_omega = isset($this_playerinfo['user_omega']) ? $this_playerinfo['user_omega'] : '';
        $this->player_id = isset($this_playerinfo['player_id']) ? $this_playerinfo['player_id'] : 0;
        $this->player_name = isset($this_playerinfo['player_name']) ? $this_playerinfo['player_name'] : 'Player';
        $this->player_token = isset($this_playerinfo['player_token']) ? $this_playerinfo['player_token'] : 'player';
        $this->player_number = isset($this_playerinfo['player_number']) ? $this_playerinfo['player_number'] : 0;
        $this->player_type = isset($this_playerinfo['player_type']) ? $this_playerinfo['player_type'] : '';
        $this->player_type2 = isset($this_playerinfo['player_type2']) ? $this_playerinfo['player_type2'] : '';
        $this->player_image = isset($this_playerinfo['player_image']) ? $this_playerinfo['player_image'] : $this->player_token;
        $this->player_image_size = isset($this_playerinfo['player_image_size']) ? $this_playerinfo['player_image_size'] : 40;
        $this->player_description = isset($this_playerinfo['player_description']) ? $this_playerinfo['player_description'] : '';
        $this->player_energy = isset($this_playerinfo['player_energy']) ? $this_playerinfo['player_energy'] : 0;
        $this->player_weapons = isset($this_playerinfo['player_weapons']) ? $this_playerinfo['player_weapons'] : 0;
        $this->player_attack = isset($this_playerinfo['player_attack']) ? $this_playerinfo['player_attack'] : 0;
        $this->player_defense = isset($this_playerinfo['player_defense']) ? $this_playerinfo['player_defense'] : 0;
        $this->player_speed = isset($this_playerinfo['player_speed']) ? $this_playerinfo['player_speed'] : 0;
        $this->player_base_energy = isset($this_playerinfo['player_base_energy']) ? $this_playerinfo['player_base_energy'] : $this->player_energy;
        $this->player_base_weapons = isset($this_playerinfo['player_base_weapons']) ? $this_playerinfo['player_base_weapons'] : $this->player_weapons;
        $this->player_base_attack = isset($this_playerinfo['player_base_attack']) ? $this_playerinfo['player_base_attack'] : $this->player_attack;
        $this->player_base_defense = isset($this_playerinfo['player_base_defense']) ? $this_playerinfo['player_base_defense'] : $this->player_defense;
        $this->player_base_speed = isset($this_playerinfo['player_base_speed']) ? $this_playerinfo['player_base_speed'] : $this->player_speed;
        $this->player_robots = isset($this_playerinfo['player_robots']) ? $this_playerinfo['player_robots'] : array();
        $this->player_abilities = isset($this_playerinfo['player_abilities']) ? $this_playerinfo['player_abilities'] : array();
        $this->player_items = isset($this_playerinfo['player_items']) ? $this_playerinfo['player_items'] : array();
        $this->player_side = isset($this_playerinfo['player_side']) ? $this_playerinfo['player_side'] : 'left';
        $this->player_autopilot = isset($this_playerinfo['player_autopilot']) ? $this_playerinfo['player_autopilot'] : false;
        $this->player_controller = isset($this_playerinfo['player_controller']) ? $this_playerinfo['player_controller'] : 'system';
        $this->player_quotes = isset($this_playerinfo['player_quotes']) ? $this_playerinfo['player_quotes'] : array();
        $this->player_rewards = isset($this_playerinfo['player_rewards']) ? $this_playerinfo['player_rewards'] : array();
        $this->player_starforce = isset($this_playerinfo['player_starforce']) ? $this_playerinfo['player_starforce'] : array();
        $this->player_frame = isset($this_playerinfo['player_frame']) ? $this_playerinfo['player_frame'] : 'base';
        $this->player_frame_index = isset($this_playerinfo['player_frame_index']) ? $this_playerinfo['player_frame_index'] : array('base','taunt','victory','defeat','command','damage','base2');
        $this->player_frame_offset = isset($this_playerinfo['player_frame_offset']) ? $this_playerinfo['player_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->player_frame_classes = isset($this_playerinfo['player_frame_classes']) ? $this_playerinfo['player_frame_classes'] : '';
        $this->player_frame_styles = isset($this_playerinfo['player_frame_styles']) ? $this_playerinfo['player_frame_styles'] : '';
        $this->player_points = isset($this_playerinfo['player_points']) ? $this_playerinfo['player_points'] : 0;
        $this->player_switch = isset($this_playerinfo['player_switch']) ? $this_playerinfo['player_switch'] : 1;
        $this->player_next_action = isset($this_playerinfo['player_next_action']) ? $this_playerinfo['player_next_action'] : 'auto';
        $this->player_visible = $this->player_token !== 'player' && empty($this->flags['player_disabled']) ? true : false;

        // Define the internal player base values using the players index array
        $this->player_base_name = isset($this_playerinfo['player_base_name']) ? $this_playerinfo['player_base_name'] : $this->player_name;
        $this->player_base_token = isset($this_playerinfo['player_base_token']) ? $this_playerinfo['player_base_token'] : $this->player_token;
        $this->player_base_image = isset($this_playerinfo['player_base_image']) ? $this_playerinfo['player_base_image'] : $this->player_image;
        $this->player_base_image_size = isset($this_playerinfo['player_base_image_size']) ? $this_playerinfo['player_base_image_size'] : $this->player_image_size;
        $this->player_base_description = isset($this_playerinfo['player_base_description']) ? $this_playerinfo['player_base_description'] : $this->player_description;
        $this->player_base_robots = isset($this_playerinfo['player_base_robots']) ? $this_playerinfo['player_base_robots'] : $this->player_robots;
        $this->player_base_abilities = isset($this_playerinfo['player_base_abilities']) ? $this_playerinfo['player_base_abilities'] : $this->player_abilities;
        $this->player_base_items = isset($this_playerinfo['player_base_items']) ? $this_playerinfo['player_base_items'] : $this->player_items;
        $this->player_base_quotes = isset($this_playerinfo['player_base_quotes']) ? $this_playerinfo['player_base_quotes'] : $this->player_quotes;
        $this->player_base_rewards = isset($this_playerinfo['player_base_rewards']) ? $this_playerinfo['player_base_rewards'] : $this->player_rewards;
        $this->player_base_starforce = isset($this_playerinfo['player_base_starforce']) ? $this_playerinfo['player_base_starforce'] : $this->player_starforce;
        $this->player_base_points = isset($this_playerinfo['player_base_points']) ? $this_playerinfo['player_base_points'] : $this->player_points;
        $this->player_base_switch = isset($this_playerinfo['player_base_switch']) ? $this_playerinfo['player_base_switch'] : $this->player_switch;

        // Collect any functions associated with this player
        if (!isset($this->player_function)){
            $temp_functions_path = MMRPG_CONFIG_PLAYERS_CONTENT_PATH.$this->player_token.'/functions.php';
            if (file_exists($temp_functions_path)){ require($temp_functions_path); }
            else { $functions = array(); }
            $this->player_function = isset($functions['player_function']) ? $functions['player_function'] : function(){};
            $this->player_function_onload = isset($functions['player_function_onload']) ? $functions['player_function_onload'] : function(){};
            unset($functions);
        }

        // Remove any abilities that do not exist in the index
        if (!empty($this->player_abilities)){
            foreach ($this->player_abilities AS $key => $token){
                if (empty($token)){ unset($this->player_abilities[$key]); }
            }
            $this->player_abilities = array_values($this->player_abilities);
        }

        /*
        // Remove any items that do not exist in the index
        if (!empty($this->player_items)){
            foreach ($this->player_items AS $key => $token){
                if (empty($token)){ unset($this->player_items[$key]); }
            }
            $this->player_items = array_values($this->player_items);
        }
        */

        // Decide whether we should include Rogue Stars or not
        $include_rogue_star_power = true;
        if (!empty($this->battle->flags['player_battle'])
            || !empty($this->battle->flags['challenge_battle'])){
            $include_rogue_star_power = false;
        }

        // Pull in session starforce if available for human players
        if ($this->player_side == 'left'
            && empty($this->player_starforce)){
            include(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');
            if (!empty($_SESSION['GAME']['values']['star_force'])){
                $this->player_starforce = $_SESSION['GAME']['values']['star_force'];
            }
        }
        // Pull in any external starforce if available for CPU players
        elseif ($this->player_side == 'right'){
            // Apply any Rogue Star boosts if not already done so
            if ($include_rogue_star_power && empty($this->flags['rogue_star_applied'])){
                $this_rogue_star = mmrpg_prototype_get_current_rogue_star();
                if (!empty($this_rogue_star)){
                    if (!isset($this->player_starforce[$this_rogue_star['star_type']])){ $this->player_starforce[$this_rogue_star['star_type']] = 0; }
                    $this->player_starforce[$this_rogue_star['star_type']] += $this_rogue_star['star_power'];
                }
                $this->flags['rogue_star_applied'] = true;
            }
        }

        // If this is a player battle, we should collect user data for both sides
        if (!empty($this->battle->flags['player_battle'])){
            //error_log('this is a player battle');
            if (empty($this->flags['player_userinfo_applied'])){
                $temp_user_fields = rpg_user::get_index_fields(true);
                $this_userinfo = $db->get_array("SELECT {$temp_user_fields} FROM mmrpg_users WHERE user_id = {$this->user_id};");
                if (!empty($this_userinfo)){
                    $this->player_name = !empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
                    $this->player_name_full = $this->player_name;
                    $this->values['colour_token'] = !empty($this_userinfo['user_colour_token']) ? $this_userinfo['user_colour_token'] : 'none';
                    $this->values['colour_token2'] = !empty($this_userinfo['user_colour_token2']) ? $this_userinfo['user_colour_token2'] : '';
                }
                $this->flags['player_userinfo_applied'] = true;
            }
        }

        // If this is a player-controlled player, load settings from session
        if ($this->player_side == 'left' && empty($this->flags['apply_session_settings'])){

            // Collect the abilities for this player from the session
            $temp_player_settings = mmrpg_prototype_player_settings($this->player_token);
            //error_log('$temp_player_settings('.$this->player_token.'/'.$this->player_token.') = '.print_r($temp_player_settings, true));

            // If there is an alternate image set, apply it
            if (!empty($temp_player_settings['player_image'])){
                $this->player_image = $temp_player_settings['player_image'];
                $this->player_base_image = $this->player_image;
            }

            // Set the session settings flag to true
            $this->flags['apply_session_settings'] = true;

        }

        // If the user token is empty, collect from data
        if (empty($this->user_token)){

            // Start off the user as mmrpg unless proven otherwise
            $this->user_token = 'mmrpg';
            $this->user_omega = 'mmrpg';

            // If this is the currently logged-in user, collect their username token
            if ($this->user_id == $_SESSION['GAME']['USER']['userid']){
                $this->user_token = $_SESSION['GAME']['USER']['username_clean'];
                $this->user_omega = $_SESSION['GAME']['USER']['omega'];
                $this->player_controller = 'human';
            }
            // Otherwise if different human player, collect username token from db
            elseif ($this->user_id != MMRPG_SETTINGS_TARGET_PLAYERID){
                $db_info = $db->get_array("SELECT user_name_clean AS username_clean, user_omega AS omega FROM mmrpg_users WHERE user_id = {$this->user_id};");
                $this->user_token = $db_info['username_clean'];
                $this->user_omega = $db_info['omega'];
            }

        }

        // Trigger the onload function if it exists
        $this->trigger_onload();

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a function for re-loreading the current player from session
    public function player_reload(){
        $this->player_load(array(
            'player_id' => $this->player_id,
            'player_token' => $this->player_token
            ));
    }

    // Define a function for refreshing this player and running onload actions
    public function trigger_onload($force = false){

        // Trigger the onload function if not already called
        if ($force || !rpg_game::onload_triggered('player', $this->player_id)){
            rpg_game::onload_triggered('player', $this->player_id, true);
            //error_log('- trigger_onload() for player '.$this->player_id.PHP_EOL);
            $temp_function = $this->player_function_onload;
            $temp_result = $temp_function(self::get_objects());
        }

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
     * Get the currently active robot owned by this player object
     * @return array
     */
    public function get_active_robot(){
        $filters = array('player_id' => $this->player_id, 'robot_position' => 'active');
        $robots = rpg_game::find_robots($filters);
        if (!empty($robots)){ $robot = array_shift($robots); }
        else { $robot = false; }
        return $robot;
    }

    /**
     * Set the list of robots owned by this player object
     * @param array $robots
     */
    public function set_robots($robots){
        $this->set_info('player_robots', $robots);
    }

    /**
     * Get the list of robots owned by this player object
     * @return array
     */
    public function get_robots($sort = true){
        $filters = array('player_id' => $this->player_id);
        $player_robots = rpg_game::find_robots($filters);
        if ($sort){
            usort($player_robots, function($a, $b){
                if ($a->robot_position == 'active'){ return -1; }
                elseif ($b->robot_position == 'active'){ return 1; }
                elseif ($a->robot_key < $b->robot_key){ return -1; }
                elseif ($a->robot_key > $b->robot_key){ return 1; }
                else { return 0; }
                });
        }
        return $player_robots;
    }

    /**
     * Get the list of active status robots owned by this player object
     * @return array
     */
    public function get_robots_active($sort = true){
        $filters = array('player_id' => $this->player_id, 'robot_status' => 'active');
        $player_robots = rpg_game::find_robots($filters);
        if ($sort){
            usort($player_robots, function($a, $b){
                if ($a->robot_position == 'active'){ return -1; }
                elseif ($b->robot_position == 'active'){ return 1; }
                elseif ($a->robot_key < $b->robot_key){ return -1; }
                elseif ($a->robot_key > $b->robot_key){ return 1; }
                else { return 0; }
                });
        }
        return $player_robots;
    }

    /**
     * Get the list of disabled status robots owned by this player object
     * @return array
     */
    public function get_robots_disabled($sort = true){
        $filters = array('player_id' => $this->player_id, 'robot_status' => 'disabled');
        $player_robots = rpg_game::find_robots($filters);
        if ($sort){
            usort($player_robots, function($a, $b){
                if ($a->robot_position == 'active'){ return -1; }
                elseif ($b->robot_position == 'active'){ return 1; }
                elseif ($a->robot_key < $b->robot_key){ return -1; }
                elseif ($a->robot_key > $b->robot_key){ return 1; }
                else { return 0; }
                });
        }
        return $player_robots;
    }

    /**
     * Get the list of IDs representing active status robots owned by this player object
     * @return array
     */
    public function get_active_robot_ids($sort = false){
        $active_robots = $this->get_robots_active($sort);
        $active_ids = array();
        if (!empty($active_robots)){ foreach ($active_robots AS $k => $r){ $active_ids[] = $r->robot_id; } }
        return $active_ids;
    }

    /**
     * Get the list of IDs representing active status robots owned by this player object
     * @return array
     */
    public function get_disabled_robot_ids($sort = false){
        $disabled_robots = $this->get_robots_disabled($sort);
        $disabled_ids = array();
        if (!empty($disabled_robots)){ foreach ($disabled_robots AS $k => $r){ $disabled_ids[] = $r->robot_id; } }
        return $disabled_ids;
    }

    /**
     * Get a robot from this player given a specific key
     * @return array
     */
    public function get_robot_by_key($robot_key, $include_active = false){
        $filters = array('player_id' => $this->player_id);
        $player_robots = rpg_game::find_robots($filters);
        foreach ($player_robots AS $key => $robot){
            if ($robot->robot_position === 'active' && !$include_active){ continue; }
            if ($robot->robot_key === $robot_key){ return $robot; }
        }
        return false;
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
     * Reset the current frame of this player object's sprite
     * @param string $frame
     */
    public function reset_frame(){
        $this->set_info('player_frame', 'base');
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

    // Define functions for getting or setting frame classes
    public function get_frame_classes(){ return $this->get_info('player_frame_classes'); }
    public function set_frame_classes($value){ $this->set_info('player_frame_classes', $value); }

    // Define functions for getting or setting frame styles
    public function get_frame_styles(){ return $this->get_info('player_frame_styles'); }
    public function set_frame_styles($value){ $this->set_info('player_frame_styles', $value); }
    public function reset_frame_styles(){ $this->set_info('player_frame_styles', ''); }


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

    // Define a public function for getting all global objects related to this player
    private function get_objects($extra_objects = array()){

        // Collect refs to all the known objects for this player
        $objects = array(
            'this_battle' => $this->battle,
            'this_player' => $this
            );

        // Merge in any additional object refs into the array
        if (!is_array($extra_objects)){ $extra_objects = array(); }
        if (!empty($extra_objects)){ $objects = array_merge($objects, $extra_objects); }

        // Attempt to collect the battle field if not already set by the calling method
        if (empty($objects['this_field'])){
            if (!empty($this->field)){ $objects['this_field'] = $this->field; }
            elseif (!empty($this->battle->battle_field)){ $objects['this_field'] = $this->battle->battle_field; }
        }

        // Attempt to collect the target player if not already set by the calling method
        if (empty($objects['target_player'])){
            if (!empty($this->other_player)){ $objects['target_player'] = $this->other_player; }
            elseif (!empty($objects['target_robot'])){ $objects['target_player'] = $objects['target_robot']->player; }
        }

        // Attempt to collect the target robot if not already set by the calling method
        if (empty($objects['target_robot'])){
            if (!empty($objects['target_player'])){
                if (!empty($objects['target_player']->values['current_robot'])){
                    $target_by_id = rpg_game::get_robot_by_id($objects['target_player']->values['current_robot']);
                    if (!empty($target_by_id)){ $objects['target_robot'] = $target_by_id; }
                }
            }
        }

        // Return the full object array for later extracting
        return $objects;

    }


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


    // -- LOAD FUNCTIONS -- //

    // Define a function for adding a new robot to this player's object data
    public function load_robot($this_robotinfo, $this_key, $apply_bonuses = false, $return_robot = false){
        //$GLOBALS['DEBUG']['checkpoint_line'] = 'class.player.php : line 107 <pre>'.print_r($this->player_robots, true).'</pre>';
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        $robot_object = rpg_game::get_robot($this->battle, $this, $this_robotinfo);
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        if ($apply_bonuses){ $robot_object->apply_stat_bonuses(); }
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        $this_export_array = $robot_object->export_array();
        ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
        $new_robots_array = $this->player_robots;
        $new_robots_array[] = $this_export_array;
        $this->set_robots($new_robots_array);
        $this->update_session();
        if ($return_robot){ return $robot_object; }
        else { return true; }
    }

    // Define public print functions for markup generation
    public function print_name($use_colour = false){
        $type_class = '';
        if ($use_colour){
            if (!empty($this->values['colour_token'])){ $type_class .= 'type_'.$this->values['colour_token']; }
            if (!empty($type_class) && !empty($this->values['colour_token2'])){ $type_class .= '_'.$this->values['colour_token2']; }
        }
        return '<span class="player_name player_type'.(!empty($type_class) ? ' '.$type_class : '').'">'.$this->player_name.'</span>';
    }
    public function print_name_s(){
        $ends_with_s = substr($this->player_name, -1) === 's' ? true : false;
        return $this->print_name()."'".(!$ends_with_s ? 's' : '');
    }
    public function print_token(){ return '<span class="player_token">'.$this->player_token.'</span>'; }
    public function print_description(){ return '<span class="player_description">'.$this->player_description.'</span>'; }
    public function print_quote($quote_type, $this_find = array(), $this_replace = array(), $quote_text_custom = ''){
        static $mmrpg_index_types;
        if (empty($mmrpg_index_types)){ $mmrpg_index_types = rpg_type::get_index(true); }
        if (!is_array($this_find)){ $this_find = array(); }
        if (!is_array($this_replace)){ $this_replace = array(); }

        // Define the quote text variable
        $quote_text = '';

        // If custom text was provided, include that here
        $this_player_quotes = $this->player_quotes;
        if (!empty($quote_text_custom)){ $this_player_quotes['custom'] = $quote_text_custom; }

        // If the player is visible and has the requested quote text
        if ($this->player_visible
            && isset($this_player_quotes[$quote_type])){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $this_player_quotes[$quote_type]);
            // Collect the text colour for this player
            $this_type_token = str_replace('dr-', '', $this->player_token);
            if (!isset($mmrpg_index_types[$this_type_token])){
                if (!empty($this->player_type)){ $this_type_token = $this->player_type; }
            }
            $this_text_colour = !empty($mmrpg_index_types[$this_type_token]) ? $mmrpg_index_types[$this_type_token]['type_colour_light'] : array(200, 200, 200);
            foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += 20; }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }

        return $quote_text;
    }

    // Define a function for generating player canvas variables
    public function canvas_markup($options){

        // Delegate markup generation to the canvas class
        return rpg_canvas::player_markup($this, $options);

    }

    // Define a function for generating player console variables
    public function console_markup($options){

        // Delegate markup generation to the console class
        return rpg_console::player_markup($this, $options);

    }

    // Define a function to trigger when all attack damage has been dealt and we need to apply disabled status to robots
    public function check_robots_disabled($target_player, $target_robot){

        // Loop through all robots on the target side and check to see if any need to be disabled
        $robots_to_disable = array();
        $robots_still_active = $this->get_robots();
        foreach ($robots_still_active AS $key => $robot){
            if (($robot->robot_energy < 1 || $robot->robot_status == 'disabled')
                && empty($robot->flags['apply_disabled_state'])){
                $robots_to_disable[] = $robot;
            }
        }

        // Sort the robots to ensure we process benched ones first, then active ones last
        usort($robots_to_disable, function($r1, $r2){
            if ($r1->robot_position === 'active' && $r2->robot_position !== 'active'){ return 1; }
            elseif ($r1->robot_position !== 'active' && $r2->robot_position === 'active'){ return -1; }
            elseif ($r1->robot_key < $r2->robot_key){ return -1; }
            elseif ($r1->robot_key > $r2->robot_key){ return 1; }
            else { return 0; }
            });

        // Loop through robots to disable and trigger it, delaying experience gains until the end
        while (!empty($robots_to_disable)){

            // Collect the robot object we'll be working with
            $robot = array_shift($robots_to_disable);

            // And now we can process the actual disabled status and cleanup
            $options = array();
            if (!empty($robots_to_disable)){
                $options['delay_stat_bonuses'] = true;
                $options['delay_experience_points'] = true;
            }
            $robot->trigger_disabled($target_robot, $options);

        }

    }

    // Define a public function for sorting robots by their active status
    public static function robot_sort_by_active($info1, $info2){
        //$info1['robot_key'] = $info1['robot_key'] < 8 ? $info1['robot_key'] + 1 : 1;
        //$info2['robot_key'] = $info2['robot_key'] < 8 ? $info2['robot_key'] + 1 : 1;
        if ($info1['robot_position'] == 'active'){ return -1; }
        elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
        elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
        else { return 0; }
    }


    // Define a static function for printing out the robot's editor markup
    public static function abilities_sort_for_editor($ability_one, $ability_two){
        $ability_token_one = $ability_one['ability_token'];
        $ability_token_two = $ability_two['ability_token'];
        if ($ability_token_one > $ability_token_two){ return 1; }
        elseif ($ability_token_one < $ability_token_two){ return -1; }
        else { return 0; }
    }


    // Define a static function for printing out the robot's editor markup
    public static function fields_sort_for_editor($field_one, $field_two){
        static $mmrpg_fields_index;
        if (empty($mmrpg_fields_index)){ $mmrpg_fields_index = rpg_field::get_index(); }
        $field_token_one = $field_one['field_token'];
        $field_token_two = $field_two['field_token'];
        if (!isset($mmrpg_fields_index[$field_token_one])){ return 0; }
        if (!isset($mmrpg_fields_index[$field_token_two])){ return 0; }
        $field_one = $mmrpg_fields_index[$field_token_one];
        $field_two = $mmrpg_fields_index[$field_token_two];
        //die('<pre>'.print_r($field_one, true).'</pre>');
        if ($field_one['field_game'] > $field_two['field_game']){ return 1; }
        elseif ($field_one['field_game'] < $field_two['field_game']){ return -1; }
        if ($field_one['field_token'] > $field_two['field_token']){ return 1; }
        elseif ($field_one['field_token'] < $field_two['field_token']){ return -1; }
        else { return 0; }
    }

    // Define a static function for printing out the robot's editor markup
    public static function items_sort_for_editor($item_one, $item_two){

        $item_token_one = $item_one['item_token'];
        $item_token_two = $item_two['item_token'];
        list($x, $kind_one, $size_one) = explode('-', $item_token_one);
        list($x, $kind_two, $size_two) = explode('-', $item_token_two);

        if ($kind_one == 'energy' && $kind_two != 'energy'){ return -1; }
        elseif ($kind_one != 'energy' && $kind_two == 'energy'){ return 1; }
        elseif ($kind_one == 'weapon' && $kind_two != 'weapon'){ return -1; }
        elseif ($kind_one != 'weapon' && $kind_two == 'weapon'){ return 1; }
        elseif ($kind_one == 'core' && $kind_two != 'core'){ return -1; }
        elseif ($kind_one != 'core' && $kind_two == 'core'){ return 1; }

        elseif ($size_one == 'pellet' && $size_two != 'pellet'){ return -1; }
        elseif ($size_one != 'pellet' && $size_two == 'pellet'){ return 1; }
        elseif ($size_one == 'capsule' && $size_two != 'capsule'){ return -1; }
        elseif ($size_one != 'capsule' && $size_two == 'capsule'){ return 1; }
        elseif ($size_one == 'tank' && $size_two != 'tank'){ return -1; }
        elseif ($size_one != 'tank' && $size_two == 'tank'){ return 1; }

        elseif ($item_one['item_token'] > $item_two['item_token']){ return 1; }
        elseif ($item_one['item_token'] < $item_two['item_token']){ return -1; }
        else { return 0; }

    }

    // Define a function to trigger when a given robot finds an item (in this context, "target" is the player and robot getting the item)
    public static function trigger_item_find($this_battle, $target_player, $target_robot, $item_reward_key, $item_reward_token, $item_quantity_dropped = 1){

        // Create the temporary item object for event creation
        $item_reward_info = rpg_item::get_index_info($item_reward_token);
        if (empty($item_reward_info)){ return false; }
        $temp_item = rpg_game::get_item($this_battle, $target_player, $target_robot, $item_reward_info);
        $temp_item->item_name = $item_reward_info['item_name'];
        $temp_item->item_image = $item_reward_info['item_token'];
        $temp_item->item_quantity = $item_quantity_dropped;
        $temp_item->update_session();

        // Collect or define the item variables
        $temp_item_token = $item_reward_info['item_token'];
        $temp_item_name = $item_reward_info['item_name'];
        $temp_item_colour = !empty($item_reward_info['item_type']) ? $item_reward_info['item_type'] : 'none';
        if (!empty($item_reward_info['item_type2'])){ $temp_item_colour .= '_'.$item_reward_info['item_type2']; }
        $temp_type_name = !empty($item_reward_info['item_type']) ? ucfirst($item_reward_info['item_type']) : 'Neutral';

        // Check if this is a core or a shard item
        $temp_is_shard = preg_match('/^([a-z]+)-shard$/i', $temp_item_token) ? true : false;
        $temp_is_core = preg_match('/^([a-z]+)-core/i', $temp_item_token) ? true : false;

        // Define the max quantity limit for this particular item
        $allow_over_max = false;
        if ($temp_is_shard){ $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; $allow_over_max = true; }
        elseif ($temp_is_core){ $temp_item_quantity_max = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
        else { $temp_item_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }

        // Create the session variable for this item if it does not exist and collect its value
        $temp_item_is_new = !isset($_SESSION['GAME']['values']['battle_items'][$temp_item_token]) ? true : false;
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
        $temp_item_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
        $temp_item_quantity_old = $temp_item_quantity;

        // If this item is already at the quantity limit, skip it entirely
        if ($temp_item_quantity >= $temp_item_quantity_max){
            //$this_battle->events_create(false, false, 'DEBUG', 'max count for '.$temp_item_token.' of '.$temp_item_quantity_max.' has been reached ('.($allow_over_max ? 'allow' : 'disallow').')');
            $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_item_quantity_max;
            $temp_item_quantity = $temp_item_quantity_max;
            if (!$allow_over_max){ return true; }
        }

        // Remove the shop/item frame from the history array so that it appears with the "new" indicator
        if ($temp_item_is_new){
            $menu_frame_token = 'items';
            $menu_frame_content_token = $temp_item_token;
            rpg_prototype::mark_menu_frame_as_unseen($menu_frame_token);
            rpg_prototype::mark_menu_frame_content_as_unseen($menu_frame_token, $menu_frame_content_token);
        }

        // Update the target player and robot's frames and update
        if (!empty($target_player)){
            $target_player->player_frame = $item_reward_key % 3 == 0 ? 'victory' : 'taunt';
            $target_player->update_session();
        }
        if (!empty($target_robot)){
            $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'taunt' : 'base';
            $target_robot->update_session();
        }

        // Update the item frame and offsets then save
        $temp_item->item_frame = 0;
        $temp_item->item_frame_offset = array('x' => 50, 'y' => 0, 'z' => 10);
        if ($item_quantity_dropped > 1){ $temp_item->item_name = $temp_item->item_base_name.'s'; }
        else { $temp_item->item_name = $temp_item->item_base_name; }
        $temp_item->update_session();

        // Define the new item quantity after increment
        $temp_item_quantity_new = $temp_item_quantity + $item_quantity_dropped;
        $shards_remaining = false;

        // If this is a shard piece, show the reaction message
        if ($temp_is_shard){

            // Define the number of shards remaining for a new core
            $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY;
            $shards_remaining = $temp_item_quantity_max - $temp_item_quantity_new;
            // If this player has collected enough shards to create a new core
            if ($shards_remaining == 0){ $temp_body_addon = 'The other '.$temp_type_name.' Shards from the inventory started glowing&hellip;'; }
            // Otherwise, if more shards are required to create a new core
            else { $temp_body_addon = 'Collect '.$shards_remaining.' more shard'.($shards_remaining > 1 ? 's' : '').' to create a new '.$temp_type_name.' Core!'; }

        }
        // Else if this is a core, show the altered inventory text
        elseif ($temp_is_core){

            // Define the robot core drop text for display

            $temp_body_addon = $target_player->print_name().' added the new core to the inventory.';
            $temp_body_addon .= ' <span class="item_stat item_type item_type_none">'.$temp_item_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_item_quantity_new.'</span>';

        }
        // Otherwise, if a normal item show the standard message
        else {

            // Define the normal item drop text for display
            $temp_body_addon = $target_player->print_name().' added the dropped item'.($item_quantity_dropped > 1 ? 's' : '').' to the inventory.';
            $temp_body_addon .= ' <span class="item_stat item_type item_type_none">'.$temp_item_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_item_quantity_new.'</span>';

        }

        // Given everything, check to see if shards will be fusing this turn
        $shards_fusing_this_turn = false;
        if ($shards_remaining !== false && $shards_remaining < 1){ $shards_fusing_this_turn = true; }

        // Display the robot reward message markup
        $event_header = $temp_item_name.' Item Drop';
        if ($item_quantity_dropped > 1){
            $event_body = rpg_battle::random_positive_word().' '.$target_robot->print_name().' found <strong>x'.$item_quantity_dropped.'</strong> '.' <span class="item_name item_type item_type_'.$temp_item_colour.'">'.$temp_item_name.($item_quantity_dropped > 1 ? 's' : '').'</span>!<br />';
        } else {
            $event_body = rpg_battle::random_positive_word().' '.$target_robot->print_name().' found '.(preg_match('/^(a|e|i|o|u)/i', $temp_item_name) ? 'an' : 'a').' <span class="item_name item_type item_type_'.$temp_item_colour.'">'.$temp_item_name.'</span>!<br />';
        }
        $event_body .= $temp_body_addon;
        //$event_body .= ' ('.$temp_item_quantity_old.' &raquo; '.$temp_item_quantity_new.')';
        $event_options = array();
        $event_options['console_show_target'] = false;
        $event_options['this_header_float'] = $target_player->player_side;
        $event_options['this_body_float'] = $target_player->player_side;
        $event_options['this_item'] = $temp_item;
        $event_options['this_item_image'] = 'icon';
        $event_options['this_item_quantity'] = $item_quantity_dropped;
        $event_options['console_show_this_player'] = false;
        $event_options['console_show_this_robot'] = false;
        $event_options['console_show_this_item'] = true;
        $event_options['canvas_show_this_item'] = true;
        $event_options['event_flag_sound_effects'] = array(
            array('name' => 'get-item', 'volume' => 1.0)
            );
        rpg_canvas::apply_camera_action_flags($event_options, $target_robot, $temp_item);
        $this_battle->events_create($target_robot, false, $event_header, $event_body, $event_options);
        if ($shards_fusing_this_turn){
            $event_options['event_flag_sound_effects'] = array(
                array('name' => 'shards-fusing', 'volume' => 0.8)
                );
            $temp_item->set_frame_styles('filter: brightness(2); ');
            $this_battle->events_create($target_robot, false, '', '', $event_options);
            $event_options['this_item_quantity'] = MMRPG_SETTINGS_SHARDS_MAXQUANTITY;
            $this_battle->events_create($target_robot, false, '', '', $event_options);
            $temp_item->set_frame_styles('');
        } else {
            $temp_item->set_frame_styles('');
            $this_battle->events_create(false, false, '', '', array_filter($event_options, function($k){ return strstr($k, '_camera_'); }, ARRAY_FILTER_USE_KEY));
        }

        // Create and/or increment the session variable for this item increasing its quantity
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] += $item_quantity_dropped;

        // If this item is not on the list of key items (un-equippable), don't add it
        $temp_key_items = array('large-screw', 'small-screw', 'heart', 'star');
        if (!in_array($temp_item_token, $temp_key_items)){
            // If there is room in this player's current item omega, add the new item
            $temp_session_token = $target_player->player_token.'_this-item-omega_prototype';
            if (!empty($_SESSION['GAME']['values'][$temp_session_token])){
                $temp_count = count($_SESSION['GAME']['values'][$temp_session_token]);
                if ($temp_count < 8 && !in_array($temp_item_token, $_SESSION['GAME']['values'][$temp_session_token])){
                    $_SESSION['GAME']['values'][$temp_session_token][] = $temp_item_token;
                    $target_player->player_items[] = $temp_item_token;
                    $target_player->update_session();
                }
            }
        }

        // If this was a shard, and it was the LAST shard
        if ($shards_fusing_this_turn){

            // Define the new core token and increment value in session
            $temp_core_token = str_replace('shard', 'core', $temp_item_token);
            $temp_core_name = str_replace('Shard', 'Core', $temp_item_name);
            $item_core_info = array('item_token' => $temp_core_token, 'item_name' => $temp_core_name, 'item_type' => $item_reward_info['item_type']);

            // Create the temporary item object for event creation
            $item_core_info['item_id'] = $item_reward_info['item_id'] + 1;
            $item_core_info['item_token'] = $temp_core_token;
            $temp_core = rpg_game::get_item($this_battle, $target_player, $target_robot, $item_core_info);
            $temp_core->item_name = $item_core_info['item_name'];
            $temp_core->item_image = $item_core_info['item_token'];
            $temp_core->update_session();

            // Collect or define the item variables
            $temp_type_name = !empty($temp_core->item_type) ? ucfirst($temp_core->item_type) : 'Neutral';
            $temp_core_colour = !empty($temp_core->item_type) ? $temp_core->item_type : 'none';

            // Define the max quantity limit for this particular item
            $temp_core_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;

            // Create the session variable for this item if it does not exist and collect its value
            if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
            $temp_core_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_core_token];
            $temp_core_quantity_old = $temp_core_quantity;

            // If this item is already at the quantity limit, skip it entirely
            if ($temp_core_quantity >= $temp_core_quantity_max){
                $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = $temp_core_quantity_max;
                $temp_core_quantity = $temp_core_quantity_max;
                return true;
            }

            // Create and/or increment the session variable for this item increasing its quantity
            if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
            if ($temp_core_quantity < $temp_core_quantity_max){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] += 1; }
            $temp_core_quantity_new = $_SESSION['GAME']['values']['battle_items'][$temp_core_token];

            // Display the robot reward message markup
            $event_header = $temp_core_name.' Item Fusion';
            $event_body = rpg_battle::random_positive_word().' The glowing shards fused to create a new '.$temp_core->print_name().'!<br />';
            $event_body .= $target_player->print_name().' added the new core to the inventory.';
            $event_body .= ' <span class="item_stat item_type item_type_none">'.$temp_core_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_core_quantity_new.'</span>';
            $event_options = array();
            $event_options['console_show_target'] = false;
            $event_options['this_header_float'] = $target_player->player_side;
            $event_options['this_body_float'] = $target_player->player_side;
            $event_options['this_item'] = $temp_core;
            $event_options['this_item_image'] = 'icon';
            $event_options['console_show_this_player'] = false;
            $event_options['console_show_this_robot'] = false;
            $event_options['console_show_this_item'] = true;
            $event_options['canvas_show_this_item'] = true;
            $event_options['event_flag_sound_effects'] = array(
                array('name' => 'get-big-item', 'volume' => 1.0)
                );
            $target_player->set_frame(($item_reward_key + 1 % 3 == 0 ? 'taunt' : 'victory'));
            $target_robot->set_frame($item_reward_key % 2 == 0 ? 'base' : 'taunt');
            $temp_core->set_frame('base');
            $temp_core->set_frame_offset(array('x' => 50, 'y' => 0, 'z' => 10));
            rpg_canvas::apply_camera_action_flags($event_options, $target_robot, $temp_core);
            $this_battle->events_create($target_robot, false, $event_header, $event_body, $event_options);
            $this_battle->events_create(false, false, '', '', array_filter($event_options, function($k){ return strstr($k, '_camera_'); }, ARRAY_FILTER_USE_KEY));

            // Set the old shard counter back to zero now that they've fused
            $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0;
            $temp_item_quantity = 0;

        }

        // Return true on success
        return true;

    }

    // Define a function to trigger when an enemy robot drops an item (in this context, "target" is the player getting the item, and "this" if the one who dropped it)
    public static function trigger_item_drop($this_battle, $target_player, $target_robot, $this_robot, $item_reward_key, $item_reward_token, $item_quantity_dropped = 1){

        // Collect a reference to the player who owned the robot who dropped the item
        $this_player = $this_robot->player;

        // Create the temporary item object for event creation
        $item_reward_info = rpg_item::get_index_info($item_reward_token);
        if (empty($item_reward_info)){ return false; }
        //$temp_item = rpg_game::get_item($this_battle, $target_player, $target_robot, $item_reward_info);
        $temp_item = rpg_game::get_item($this_battle, $this_player, $this_robot, $item_reward_info);
        $temp_item->item_name = $item_reward_info['item_name'];
        $temp_item->item_image = $item_reward_info['item_token'];
        $temp_item->item_quantity = $item_quantity_dropped;
        $temp_item->update_session();

        // Collect or define the item variables
        $temp_item_token = $item_reward_info['item_token'];
        $temp_item_name = $item_reward_info['item_name'];
        $temp_item_colour = !empty($item_reward_info['item_type']) ? $item_reward_info['item_type'] : 'none';
        if (!empty($item_reward_info['item_type2'])){ $temp_item_colour .= '_'.$item_reward_info['item_type2']; }
        $temp_type_name = !empty($item_reward_info['item_type']) ? ucfirst($item_reward_info['item_type']) : 'Neutral';

        // Check if this is a core or a shard item
        $temp_is_shard = preg_match('/^([a-z]+)-shard$/i', $temp_item_token) ? true : false;
        $temp_is_core = preg_match('/^([a-z]+)-core/i', $temp_item_token) ? true : false;

        // Define the max quantity limit for this particular item
        $allow_over_max = false;
        if ($temp_is_shard){ $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; $allow_over_max = true; }
        elseif ($temp_is_core){ $temp_item_quantity_max = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
        else { $temp_item_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }

        // Create the session variable for this item if it does not exist and collect its value
        $temp_item_is_new = !isset($_SESSION['GAME']['values']['battle_items'][$temp_item_token]) ? true : false;
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
        $temp_item_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
        $temp_item_quantity_old = $temp_item_quantity;

        // If this item is already at the quantity limit, skip it entirely
        if ($temp_item_quantity >= $temp_item_quantity_max){
            //$this_battle->events_create(false, false, 'DEBUG', 'max count for '.$temp_item_token.' of '.$temp_item_quantity_max.' has been reached ('.($allow_over_max ? 'allow' : 'disallow').')');
            $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_item_quantity_max;
            $temp_item_quantity = $temp_item_quantity_max;
            if (!$allow_over_max){ return true; }
        }

        // If the requested drop would put the user over the limit, prevent that
        if (!$temp_is_shard
            && (($temp_item_quantity + $item_quantity_dropped) > $temp_item_quantity_max)){
            $item_quantity_dropped -= (($temp_item_quantity + $item_quantity_dropped) - $temp_item_quantity_max);
            if (empty($item_quantity_dropped)){ return false; }
        }

        // Remove the shop/item frame from the history array so that it appears with the "new" indicator
        if ($temp_item_is_new){
            $menu_frame_token = 'items';
            $menu_frame_content_token = $temp_item_token;
            rpg_prototype::mark_menu_frame_as_unseen($menu_frame_token);
            rpg_prototype::mark_menu_frame_content_as_unseen($menu_frame_token, $menu_frame_content_token);
        }

        // Update the target player and robot's frames and update
        if (!empty($target_player)){
            $target_player->player_frame = $item_reward_key % 3 == 0 ? 'victory' : 'taunt';
            $target_player->update_session();
        }
        if (!empty($target_robot)){
            $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'taunt' : 'base';
            $target_robot->update_session();
        }

        // Update the item frame and offsets then save
        $temp_item->item_frame = 0;
        $temp_item->item_frame_offset = array('x' => 50, 'y' => 0, 'z' => 10);
        if ($item_quantity_dropped > 1){ $temp_item->item_name = $temp_item->item_base_name.'s'; }
        else { $temp_item->item_name = $temp_item->item_base_name; }
        $temp_item->update_session();

        // Define the new item quantity after increment
        $temp_item_quantity_new = $temp_item_quantity + $item_quantity_dropped;
        $shards_remaining = false;

        // If this is a shard piece, show the reaction message
        if ($temp_is_shard){

            // Define the number of shards remaining for a new core
            $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY;
            $shards_remaining = $temp_item_quantity_max - $temp_item_quantity_new;
            // If this player has collected enough shards to create a new core
            if ($shards_remaining == 0){ $temp_body_addon = 'The other '.$temp_type_name.' Shards from the inventory started glowing&hellip;'; }
            // Otherwise, if more shards are required to create a new core
            else { $temp_body_addon = 'Collect '.$shards_remaining.' more shard'.($shards_remaining > 1 ? 's' : '').' to create a new '.$temp_type_name.' Core!'; }

        }
        // Else if this is a core, show the altered inventory text
        elseif ($temp_is_core){

            // Define the robot core drop text for display

            $temp_body_addon = $target_player->print_name().' added the new core to the inventory.';
            $temp_body_addon .= ' <span class="item_stat item_type item_type_none">'.$temp_item_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_item_quantity_new.'</span>';

        }
        // Otherwise, if a normal item show the standard message
        else {

            // Define the normal item drop text for display
            $temp_body_addon = $target_player->print_name().' added the dropped item'.($item_quantity_dropped > 1 ? 's' : '').' to the inventory.';
            $temp_body_addon .= ' <span class="item_stat item_type item_type_none">'.$temp_item_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_item_quantity_new.'</span>';

        }

        // Given everything, check to see if shards will be fusing this turn
        $shards_fusing_this_turn = false;
        if ($shards_remaining !== false && $shards_remaining < 1){ $shards_fusing_this_turn = true; }

        // Display the robot reward message markup
        $event_header = $temp_item_name.' Item Drop';
        $prefix = $this_robot->robot_status === 'disabled' ? 'The disabled ' : 'The ';
        if ($item_quantity_dropped > 1){
            $event_body = rpg_battle::random_positive_word().' '.$prefix.$this_robot->print_name().' dropped <strong>x'.$item_quantity_dropped.'</strong> '.' <span class="item_name item_type item_type_'.$temp_item_colour.'">'.$temp_item_name.($item_quantity_dropped > 1 ? 's' : '').'</span>!<br />';
        } else {
            $event_body = rpg_battle::random_positive_word().' '.$prefix.$this_robot->print_name().' dropped '.(preg_match('/^(a|e|i|o|u)/i', $temp_item_name) ? 'an' : 'a').' <span class="item_name item_type item_type_'.$temp_item_colour.'">'.$temp_item_name.'</span>!<br />';
        }
        $event_body .= $temp_body_addon;
        //$event_body .= ' ('.$temp_item_quantity_old.' &raquo; '.$temp_item_quantity_new.')';
        $event_options = array();
        $event_options['console_show_target'] = false;
        $event_options['this_header_float'] = $this_player->player_side;
        $event_options['this_body_float'] = $this_player->player_side;
        $event_options['this_item'] = $temp_item;
        $event_options['this_item_image'] = 'icon';
        $event_options['this_item_quantity'] = $item_quantity_dropped;
        $event_options['console_show_this_player'] = false;
        $event_options['console_show_this_robot'] = false;
        $event_options['console_show_this_item'] = true;
        $event_options['canvas_show_this_item'] = true;
        $event_options['event_flag_sound_effects'] = array(
            array('name' => 'get-item', 'volume' => 1.0)
            );
        rpg_canvas::apply_camera_action_flags($event_options, $this_robot, $temp_item);
        $this_battle->events_create($this_robot, false, $event_header, $event_body, $event_options);
        if ($shards_fusing_this_turn){
            $event_options['event_flag_sound_effects'] = array(
                array('name' => 'shards-fusing', 'volume' => 0.8)
                );
            $temp_item->set_frame_styles('filter: brightness(2); ');
            $this_battle->events_create($this_robot, false, '', '', $event_options);
            $event_options['this_item_quantity'] = MMRPG_SETTINGS_SHARDS_MAXQUANTITY;
            $this_battle->events_create($this_robot, false, '', '', $event_options);
            $temp_item->set_frame_styles('');
        } else {
            $temp_item->set_frame_styles('');
            $this_battle->events_create(false, false, '', '', array_filter($event_options, function($k){ return strstr($k, '_camera_'); }, ARRAY_FILTER_USE_KEY));
        }

        // Create and/or increment the session variable for this item increasing its quantity
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] += $item_quantity_dropped;

        // If this item is not on the list of key items (un-equippable), don't add it
        $temp_key_items = array('large-screw', 'small-screw', 'heart', 'star');
        if (!in_array($temp_item_token, $temp_key_items)){
            // If there is room in this player's current item omega, add the new item
            $temp_session_token = $target_player->player_token.'_this-item-omega_prototype';
            if (!empty($_SESSION['GAME']['values'][$temp_session_token])){
                $temp_count = count($_SESSION['GAME']['values'][$temp_session_token]);
                if ($temp_count < 8 && !in_array($temp_item_token, $_SESSION['GAME']['values'][$temp_session_token])){
                    $_SESSION['GAME']['values'][$temp_session_token][] = $temp_item_token;
                    $target_player->player_items[] = $temp_item_token;
                    $target_player->update_session();
                }
            }
        }

        // If this was a shard, and it was the LAST shard
        if ($shards_fusing_this_turn){

            // Define the new core token and increment value in session
            $temp_core_token = str_replace('shard', 'core', $temp_item_token);
            $temp_core_name = str_replace('Shard', 'Core', $temp_item_name);
            $item_core_info = array('item_token' => $temp_core_token, 'item_name' => $temp_core_name, 'item_type' => $item_reward_info['item_type']);

            // Create the temporary item object for event creation
            $item_core_info['item_id'] = $item_reward_info['item_id'] + 1;
            $item_core_info['item_token'] = $temp_core_token;
            $temp_core = rpg_game::get_item($this_battle, $this_player, $this_robot, $item_core_info);
            $temp_core->item_name = $item_core_info['item_name'];
            $temp_core->item_image = $item_core_info['item_token'];
            $temp_core->update_session();

            // Collect or define the item variables
            $temp_type_name = !empty($temp_core->item_type) ? ucfirst($temp_core->item_type) : 'Neutral';
            $temp_core_colour = !empty($temp_core->item_type) ? $temp_core->item_type : 'none';

            // Define the max quantity limit for this particular item
            $temp_core_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;

            // Create the session variable for this item if it does not exist and collect its value
            if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
            $temp_core_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_core_token];
            $temp_core_quantity_old = $temp_core_quantity;

            // If this item is already at the quantity limit, skip it entirely
            if ($temp_core_quantity >= $temp_core_quantity_max){
                $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = $temp_core_quantity_max;
                $temp_core_quantity = $temp_core_quantity_max;
                return true;
            }

            // Create and/or increment the session variable for this item increasing its quantity
            if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
            if ($temp_core_quantity < $temp_core_quantity_max){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] += 1; }
            $temp_core_quantity_new = $_SESSION['GAME']['values']['battle_items'][$temp_core_token];

            // Display the robot reward message markup
            $event_header = $temp_core_name.' Item Fusion';
            $event_body = rpg_battle::random_positive_word().' The glowing shards fused to create a new '.$temp_core->print_name().'!<br />';
            $event_body .= $target_player->print_name().' added the new core to the inventory.';
            $event_body .= ' <span class="item_stat item_type item_type_none">'.$temp_core_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_core_quantity_new.'</span>';
            $event_options = array();
            $event_options['console_show_target'] = false;
            $event_options['this_header_float'] = $this_player->player_side;
            $event_options['this_body_float'] = $this_player->player_side;
            $event_options['this_item'] = $temp_core;
            $event_options['this_item_image'] = 'icon';
            $event_options['console_show_this_player'] = false;
            $event_options['console_show_this_robot'] = false;
            $event_options['console_show_this_item'] = true;
            $event_options['canvas_show_this_item'] = true;
            $event_options['event_flag_sound_effects'] = array(
                array('name' => 'get-big-item', 'volume' => 1.0)
                );
            $target_player->set_frame(($item_reward_key + 1 % 3 == 0 ? 'taunt' : 'victory'));
            $target_robot->set_frame($item_reward_key % 2 == 0 ? 'base' : 'taunt');
            $temp_core->set_frame('base');
            $temp_core->set_frame_offset(array('x' => 50, 'y' => 0, 'z' => 10));
            rpg_canvas::apply_camera_action_flags($event_options, $this_robot, $temp_core);
            $this_battle->events_create($this_robot, false, $event_header, $event_body, $event_options);
            $this_battle->events_create(false, false, '', '', array_filter($event_options, function($k){ return strstr($k, '_camera_'); }, ARRAY_FILTER_USE_KEY));

            // Set the old shard counter back to zero now that they've fused
            $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0;
            $temp_item_quantity = 0;

        }

        // Return true on success
        return true;

    }


    // -- END-OF-TURN CHECK FUNCTIONS -- //

    // Define a function for checking the current turn and updating history
    public function check_robots_submodules_disabled(){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // Collect references to relative player and robot objects
        $this_player = $this;

        // If the battle has ended, don't do this
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Define the various submodule groups and their associated submodules and/or items
        static $supported_submodule_groups = array(
            'anti_priority_robots' => array('skill' => 'metronome-submodule', 'item' => 'metronome-module', 'position' => 'any'),
            'anti_recovery_robots' => array('skill' => 'saboteur-submodule', 'item' => 'saboteur-module', 'position' => 'any'),
            'hyperscan_robots' => array('skill' => 'hyperscan-submodule', 'item' => 'hyperscan-module', 'position' => 'any'),
            'bulwark_robots' => array('skill' => 'bulwark-submodule', 'item' => 'bulwark-module', 'position' => 'active'),
            'transport_robots' => array('skill' => 'transport-submodule', 'item' => 'transport-module', 'position' => 'bench'),
            );

        // Loop through the supported submodule groups and remove any that are no longer active
        foreach ($supported_submodule_groups AS $group_token => $skills_and_items){
            //error_log('$parse_player_submodule_groups('.$this_player->player_token.', '.$group_token.', ['.implode(',', $skills_and_items).'])');
            $temp_group_robots = $this_player->get_value($group_token);
            $backup_group_robots = $temp_group_robots;
            if (!empty($temp_group_robots)){
                foreach ($temp_group_robots AS $key => $robot_id){
                    //error_log('Checking robot with ID '.$robot_id);
                    $temp_robot = rpg_game::get_robot($this_battle, $this_player, array('robot_id' => $robot_id));
                    if (empty($temp_robot)){
                        //error_log('Removing '.$robot_id.' from '.$group_token.' because it no longer exists');
                        unset($temp_group_robots[$key]);
                        continue;
                        }
                    $temp_skill = $temp_robot->has_skill() ? $temp_robot->get_skill() : '';
                    $temp_item = $temp_robot->has_item() ? $temp_robot->get_item() : '';
                    $temp_position = $temp_robot->get_position();
                    $required_skill = !empty($skills_and_items['skill']) ? $skills_and_items['skill'] : '';
                    $required_item = !empty($skills_and_items['item']) ? $skills_and_items['item'] : '';
                    $required_position = !empty($skills_and_items['position']) ? $skills_and_items['position'] : 'any';
                    $has_required_skill = !empty($required_skill) && $temp_skill == $required_skill ? true : false;
                    $has_required_item = !empty($required_item) && $temp_item == $required_item ? true : false;
                    $has_required_position = $required_position === 'any' || $temp_position === $required_position ? true : false;
                    //error_log('Reviewing skill: '.$temp_skill.' vs '.$required_skill.' ('.($has_required_skill ? 'true' : 'false').')');
                    //error_log('Reviewing item: '.$temp_item.' vs '.$required_item.' ('.($has_required_item ? 'true' : 'false').')');
                    //error_log('Reviewing position: '.$temp_position.' vs '.$required_position.' ('.($has_required_position ? 'true' : 'false').')');
                    if (!$has_required_position || (!$has_required_skill && !$has_required_item)){
                        //error_log('Removing '.$robot_id.' from '.$group_token);
                        unset($temp_group_robots[$key]);
                        continue;
                        } else {
                        //error_log('Keeping '.$temp_robot->robot_string.' in '.$group_token);
                        }
                }
                $temp_group_robots = array_values($temp_group_robots);
                // check to see if the backup and new group have different elements even if different order
                if (array_diff($backup_group_robots, $temp_group_robots) || array_diff($temp_group_robots, $backup_group_robots)){
                    //error_log('NEW array for '.$this_player->player_token.'\'s '.$group_token.' is ['.implode(',', $temp_group_robots).']');
                    $this_player->set_value($group_token, $temp_group_robots);
                } else {
                    //error_log('OLD array for '.$this_player->player_token.'\'s '.$group_token.' is ['.implode(',', $temp_group_robots).']');
                }
            }
        }

        // Return true on success
        return true;

    }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all player index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various index fields for player objects
        $index_fields = array(
            'player_id',
            'player_token',
            'player_number',
            'player_name',
            'player_name_full',
            'player_game',
            'player_class',
            'player_gender',
            'player_image',
            'player_image_size',
            'player_image_editor',
            'player_image_editor2',
            'player_image_editor3',
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
            'player_robot_hero',
            'player_robot_support',
            'player_field_intro',
            'player_field_home',
            'player_abilities_rewards',
            'player_abilities_compatible',
            'player_robots_rewards',
            'player_robots_compatible',
            'player_quotes_start',
            'player_quotes_taunt',
            'player_quotes_victory',
            'player_quotes_defeat',
            'player_quotes_custom',
            'player_flag_hidden',
            'player_flag_complete',
            'player_flag_published',
            'player_flag_protected'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($index_fields AS $key => $field){
                $index_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

    /**
     * Get a list of all JSON-based player index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_json_index_fields($implode = false){

        // Define the various json index fields for player objects
        $json_index_fields = array(
            'player_abilities_rewards',
            'player_abilities_compatible',
            'player_robots_rewards',
            'player_robots_compatible',
            'player_image_alts',
            'player_quotes_custom'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $json_index_fields = implode(', ', $json_index_fields);
        }

        // Return the index fields, array or string
        return $json_index_fields;

    }

    /**
     * Get a list of all fields that can be ignored by JSON-export functions
     * (aka ones that do not actually need to be saved to the database)
     * @param bool $implode
     * @return mixed
     */
    public static function get_fields_excluded_from_json_export($implode = false){

        // Define the various json index fields for player objects
        $json_index_fields = array(
            'player_group',
            'player_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $json_index_fields = implode(', ', $json_index_fields);
        }

        // Return the index fields, array or string
        return $json_index_fields;

    }

    /**
     * Get the entire player index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $filter_class = '', $include_tokens = array()){
        //error_log('rpg_player::get_index()');

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND players.player_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND players.player_flag_published = 1 '; }
        if (!empty($filter_class)){ $temp_where .= "AND players.player_class = '{$filter_class}' "; }
        if (!empty($include_tokens)){
            $include_string = $include_tokens;
            array_walk($include_string, function(&$s){ $s = "'{$s}'"; });
            $include_tokens = implode(', ', $include_string);
            $temp_where .= 'OR players.player_token IN ('.$include_tokens.') ';
        }

        // Define a static array for cached queries
        static $index_cache = array();

        // Define the static token for this query
        $cache_token = md5($temp_where);

        // If already found, return the collected index directly, else collect from DB
        if (!empty($index_cache[$cache_token])){ return $index_cache[$cache_token]; }

        // Otherwise attempt to collect the index from the cache
        $cached_index = rpg_object::load_cached_index('players', $cache_token);
        if (!empty($cached_index)){
            $index_cache[$cache_token] = $cached_index;
            return $index_cache[$cache_token];
        }

        // Collect every player's info from the database index
        //error_log('(!) generating a new players index array for '.MMRPG_CONFIG_CACHE_DATE);
        $player_fields = rpg_player::get_index_fields(true, 'players');
        $player_index = $db->get_array_list("SELECT
            {$player_fields},
            groups.group_token AS player_group,
            tokens.token_order AS player_order
            FROM mmrpg_index_players AS players
            LEFT JOIN mmrpg_index_players_groups_tokens AS tokens ON tokens.player_token = players.player_token
            LEFT JOIN mmrpg_index_players_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE players.player_id <> 0 {$temp_where}
            ORDER BY
            players.player_class ASC,
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'player_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($player_index)){ $player_index = self::parse_index($player_index); }
        else { $player_index = array(); }

        // Return the cached index array
        rpg_object::save_cached_index('players', $cache_token, $player_index);
        $index_cache[$cache_token] = $player_index;
        return $index_cache[$cache_token];

    }

    // Define a function for pulling only the tokens for a given index request
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false, $filter_class = ''){
        $index = self::get_index($include_hidden, $include_unpublished, $filter_class);
        return array_keys($index);
    }

    // Define a function for pulling a custom index given a list of tokens
    public static function get_index_custom($tokens = array()){
        if (empty($tokens)){ return array(); }
        $index = self::get_index();
        foreach ($index AS $token => $info){
            if (!in_array($token, $tokens)){
                unset($index[$token]);
            }
        }
        return $index;
    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($player_token){

        // If empty, return nothing
        if (empty($player_token)){ return false; };

        // Collect a local copy of the player index
        static $player_index = false;
        static $player_index_byid = false;
        if ($player_index === false){
            $player_index_byid = array();
            $player_index = self::get_index(true, true);
            if (empty($player_index)){ $player_index = array(); }
            foreach ($player_index AS $token => $player){ $player_index_byid[$player['player_id']] = $token; }
        }

        // Return either by token or by ID if number provided
        if (is_numeric($player_token)){
            // Search by player ID
            $player_id = $player_token;
            if (!empty($player_index_byid[$player_id])){ return $player_index[$player_index_byid[$player_id]]; }
            else { return false; }
        } else {
            // Search by player TOKEN
            if (!empty($player_index[$player_token])){ return $player_index[$player_token]; }
            else { return false; }
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
        $temp_fields = self::get_json_index_fields();
        foreach ($temp_fields AS $field_name){
            if (!empty($player_info[$field_name])){ $player_info[$field_name] = json_decode($player_info[$field_name], true); }
            else { $player_info[$field_name] = array(); }
        }

        // Restructure reward and compatibility arrays for the player
        $player_info['player_rewards'] = array();
        $player_info['player_rewards']['robots'] = $player_info['player_robots_rewards'];
        $player_info['player_rewards']['abilities'] = $player_info['player_abilities_rewards'];
        unset($player_info['player_robots_rewards'], $player_info['player_abilities_rewards']);
        $player_info['player_abilities'] = $player_info['player_abilities_compatible'];
        unset($player_info['player_abilities_compatible']);
        $player_info['player_robots_unlockable'] = $player_info['player_robots_compatible'];
        unset($player_info['player_robots_compatible']);

        // Collect the quotes into the proper arrays
        $quote_types = array('start', 'taunt', 'victory', 'defeat');
        foreach ($quote_types AS $type){
            $player_info['player_quotes']['battle_'.$type] = !empty($player_info['player_quotes_'.$type]) ? $player_info['player_quotes_'.$type]: '';
            unset($player_info['player_quotes_'.$type]);
        }

        // Return the parsed player info
        return $player_info;
    }


    // -- SESSION FUNCTIONS -- //

    // Define a public function updating internal varibales
    public function update_variables(){

        // Update parent objects first
        //$this->battle->update_variables();

        // Calculate this robot's count variables
        $this->counters['abilities_total'] = count($this->player_abilities);
        $this->counters['items_total'] = count($this->player_items);

        // Create the current robot value for calculations
        $this->values['current_robot'] = false;
        $this->values['current_robot_enter'] = isset($this->values['current_robot_enter']) ? $this->values['current_robot_enter'] : false;

        // Create the counter variables and defeault to zero
        $this->counters['robots_total'] = 0;
        $this->counters['robots_active'] = 0;
        $this->counters['robots_disabled'] = 0;
        $this->counters['robots_positions'] = array(
            'active' => 0,
            'bench' => 0
            );

        // Create the value variables and default to empty
        $this->values['robots_active'] = array();
        $this->values['robots_disabled'] = array();
        $this->values['robots_positions'] = array(
            'active' => array(),
            'bench' => array()
            );

        // Ensure this player has robots to loop over
        if (!empty($this->player_robots)){

            // Loop through each of the player's robots and check status
            $new_player_robots = array();
            foreach ($this->player_robots AS $this_key => $this_robotinfo){
                // Ensure a token an idea are provided at least
                if (empty($this_robotinfo['robot_id']) || empty($this_robotinfo['robot_token'])){ continue; }
                // Define the current temp robot object using the loaded robot data
                $temp_info = array('robot_id' => $this_robotinfo['robot_id'], 'robot_token' => $this_robotinfo['robot_token']);
                $temp_robot = rpg_game::get_robot($this->battle, $this, $temp_info, false);
                if (!isset($temp_robot->robot_key)){ $temp_robot->set_key($this_key); }
                // Update the player object with the refreshed robot info
                $new_player_robots[$temp_robot->robot_id] = $temp_robot->export_array();
                // Check if this robot is in the active position
                if ($temp_robot->robot_position == 'active'){
                    $this->values['current_robot'] = $temp_robot->robot_string;
                }
                // Check if this robot is in active status
                if ($temp_robot->robot_status == 'active'){
                    // Increment the active robot counter
                    $this->counters['robots_active']++;
                    // Add this robot to the active robots array
                    $this->values['robots_active'][] = $new_player_robots[$temp_robot->robot_id];
                    // Check if this robot is in the active position
                    if ($temp_robot->robot_position == 'active'){
                        // Increment the active robot counter
                        $this->counters['robots_positions']['active']++;
                        // Add this robot to the active robots array
                        $this->values['robots_positions']['active'][] = $new_player_robots[$temp_robot->robot_id];
                    }
                    // Otherwise, if this robot is in benched position
                    elseif ($temp_robot->robot_position == 'bench'){
                        // Increment the bench robot counter
                        $this->counters['robots_positions']['bench']++;
                        // Add this robot to the bench robots array
                        $this->values['robots_positions']['bench'][] = $new_player_robots[$temp_robot->robot_id];
                    }
                }
                // Otherwise, if this robot is in disabled status
                elseif ($temp_robot->robot_status == 'disabled'){
                    // Increment the disabled robot counter
                    $this->counters['robots_disabled']++;
                    // Add this robot to the disabled robots array
                    $this->values['robots_disabled'][] = $new_player_robots[$temp_robot->robot_id];
                }

                // Increment the robot total by default
                $this->counters['robots_total']++;
                // Update or create this robot's session object
                //$temp_robot->update_session();
                unset($temp_robot);
            }

            // Now sort the array by position and then key
            usort($new_player_robots, function($a, $b){
                if ($a['robot_position'] == 'active'){ return -1; }
                elseif ($b['robot_position'] == 'active'){ return 1; }
                elseif ($a['robot_key'] < $b['robot_key']){ return -1; }
                elseif ($a['robot_key'] > $b['robot_key']){ return 1; }
                else { return 0; }
                });

            // Update the position keys for all robots after above
            foreach ($new_player_robots AS $k => $r){ $new_player_robots[$k]['robot_key'] = $k; }

            // Update the internal robots array with new data
            $this->player_robots = array_values($new_player_robots);

        }

        // If an active robot was not found, reset the turn counter
        if (empty($this->values['current_robot'])){
            $this->values['current_robot_enter'] = false;
        }

        // Now collect an export array for this object
        $this_data = $this->export_array();

        // Update the parent battle variable
        $this->battle->values['players'][$this->player_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a public, static function for resetting player values to base
    public static function reset_variables($this_data){
        $this_data['player_flags'] = array();
        $this_data['player_counters'] = array();
        $this_data['player_values'] = array();
        $this_data['player_history'] = array();
        $this_data['player_name'] = $this_data['player_base_name'];
        $this_data['player_token'] = $this_data['player_base_token'];
        $this_data['player_description'] = $this_data['player_base_description'];
        $this_data['player_robots'] = $this_data['player_base_robots'];
        $this_data['player_quotes'] = $this_data['player_base_quotes'];
        return $this_data;
    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Request parent battle object to update as well
        //$this->battle->update_session();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['PLAYERS'][$this->player_id] = $this_data;
        $this->battle->values['players'][$this->player_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal player fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'user_id' => $this->user_id,
            'user_token' => $this->user_token,
            'user_omega' => $this->user_omega,
            'player_id' => $this->player_id,
            'player_name' => $this->player_name,
            'player_token' => $this->player_token,
            'player_number' => $this->player_number,
            'player_type' => $this->player_type,
            'player_type2' => $this->player_type2,
            'player_image' => $this->player_image,
            'player_image_size' => $this->player_image_size,
            'player_description' => $this->player_description,
            'player_energy' => $this->player_energy,
            'player_weapons' => $this->player_weapons,
            'player_attack' => $this->player_attack,
            'player_defense' => $this->player_defense,
            'player_speed' => $this->player_speed,
            'player_base_energy' => $this->player_base_energy,
            'player_base_weapons' => $this->player_base_weapons,
            'player_base_attack' => $this->player_base_attack,
            'player_base_defense' => $this->player_base_defense,
            'player_base_speed' => $this->player_base_speed,
            'player_robots' => $this->player_robots,
            'player_abilities' => $this->player_abilities,
            'player_items' => $this->player_items,
            'player_quotes' => $this->player_quotes,
            'player_rewards' => $this->player_rewards,
            'player_starforce' => $this->player_starforce,
            'player_points' => $this->player_points,
            'player_switch' => $this->player_switch,
            'player_next_action' => $this->player_next_action,
            'player_base_name' => $this->player_base_name,
            'player_base_token' => $this->player_base_token,
            'player_base_image' => $this->player_base_image,
            'player_base_image_size' => $this->player_base_image_size,
            'player_base_description' => $this->player_base_description,
            'player_base_robots' => $this->player_base_robots,
            'player_base_abilities' => $this->player_base_abilities,
            'player_base_items' => $this->player_base_items,
            'player_base_quotes' => $this->player_base_quotes,
            'player_base_rewards' => $this->player_base_rewards,
            'player_base_starforce' => $this->player_base_starforce,
            'player_base_points' => $this->player_base_points,
            'player_base_switch' => $this->player_base_switch,
            'player_side' => $this->player_side,
            'player_autopilot' => $this->player_autopilot,
            'player_controller' => $this->player_controller,
            'player_frame' => $this->player_frame,
            'player_frame_index' => $this->player_frame_index,
            'player_frame_offset' => $this->player_frame_offset,
            'player_frame_classes' => $this->player_frame_classes,
            'player_frame_styles' => $this->player_frame_styles,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }


    // -- PRINT FUNCTIONS -- //

    // Define a static function for printing out the player's database markup
    public static function print_database_markup($player_info, $print_options = array()){

        // Define the global variables
        global $db;
        global $this_current_uri, $this_current_url;
        global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;

        // Define the print style defaults
        if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
        if ($print_options['layout_style'] == 'website'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
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
        $player_sprite_frames = array('base','taunt','victory','defeat','command','damage','base2');

        // Define the player header types
        $player_header_types = 'player_type_'.$player_type_token;

        // Define the markup variable
        $this_markup = '';

        // Start the output buffer
        ob_start();
        ?>
        <div class="database_container database_player_container" data-token="<?= $player_info['player_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">
            <a class="anchor" id="<?= $player_info['player_token']?>">&nbsp;</a>
            <div class="subbody event event_triple event_visible" data-token="<?= $player_info['player_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

                <? if($print_options['show_mugshot']): ?>
                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_key'] !== false): ?>
                            <div class="mugshot player_type player_type_<?= !empty($player_info['player_type']) ? $player_info['player_type'] : 'none' ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.$player_info['player_key'] ?></div>
                        <? endif; ?>
                        <div class="mugshot player_type player_type_<?= !empty($player_info['player_type']) ? $player_info['player_type'] : 'none' ?>"><div style="background-image: url(images/players/<?= $player_image_token ?>/mug_right_<?= $player_image_size_text ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_player sprite_40x40 sprite_40x40_mug sprite_size_<?= $player_image_size_text ?> sprite_size_<?= $player_image_size_text ?>_mug player_status_active player_position_active"><?= $player_info['player_name']?>'s Mugshot</div></div>
                    </div>
                <? endif; ?>


                <? if($print_options['show_basics']): ?>
                    <h2 class="header header_left player_type_<?= $player_type_token ?>" style="margin-right: 0;">
                        <? if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="database/players/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/"><?= $player_info['player_name'] ?></a>
                        <? else: ?>
                            <?= $player_info['player_name'] ?>
                        <? endif; ?>
                    </h2>

                    <div class="body body_left" style="margin-right: 0; padding: 0 0 2px;">

                        <table class="full basic">
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Name :</label>
                                        <span class="player_name player_type"><?= $player_info['player_name']?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="full basic">
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Skill :</label>
                                        <?
                                            // Display any special boosts this player has
                                            if (!empty($player_info['player_energy'])){ echo '<span class="player_name player_type player_type_energy">Energy +'.$player_info['player_energy'].'%</span>'; }
                                            elseif (!empty($player_info['player_attack'])){ echo '<span class="player_name player_type player_type_attack">Attack +'.$player_info['player_attack'].'%</span>'; }
                                            elseif (!empty($player_info['player_defense'])){ echo '<span class="player_name player_type player_type_defense">Defense +'.$player_info['player_defense'].'%</span>'; }
                                            elseif (!empty($player_info['player_speed'])){ echo '<span class="player_name player_type player_type_speed">Speed +'.$player_info['player_speed'].'%</span>'; }
                                            else { echo '<span class="player_name player_type player_type_none">None</span>'; }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                <? endif; ?>

                <? if($print_options['layout_style'] == 'website'): ?>

                    <?
                    // Define the various tabs we are able to scroll to
                    $section_tabs = array();
                    if ($print_options['show_sprites']){ $section_tabs[] = array('sprites', 'Sprites', false); }
                    if ($print_options['show_description']){ $section_tabs[] = array('description', 'Description', false); }
                    if ($print_options['show_quotes']){ $section_tabs[] = array('quotes', 'Quotes', false); }
                    if ($print_options['show_abilities']){ $section_tabs[] = array('abilities', 'Abilities', false); }
                    //if ($print_options['show_records']){ $section_tabs[] = array('records', 'Records', false); }
                    // Automatically mark the first element as true or active
                    $section_tabs[0][2] = true;
                    // Define the current URL for this player page
                    $temp_url = 'database/players/';
                    $temp_url .= $player_info['player_token'].'/';
                    ?>

                    <div id="tabs" class="section_tabs">
                        <?
                        foreach($section_tabs AS $tab){
                            echo '<a class="link_inline link_'.$tab[0].'" href="'.$temp_url.'#'.$tab[0].'" data-anchor="#'.$tab[0].'"><span class="wrap">'.$tab[1].'</span></a>';
                        }
                        ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_sprites'] && (!isset($player_info['player_image_sheets']) || $player_info['player_image_sheets'] !== 0) && $player_image_token != 'player' ): ?>

                    <?

                    // Start the output buffer and prepare to collect sprites
                    $this_sprite_markup = '';
                    if (true){

                        // Define the alts we'll be looping through for this player
                        $temp_alts_array = array();
                        $temp_alts_array[] = array('token' => '', 'name' => $player_info['player_name'], 'summons' => 0);
                        // Append predefined alts automatically, based on the player image alt array
                        if (!empty($player_info['player_image_alts'])){
                            $temp_alts_array = array_merge($temp_alts_array, $player_info['player_image_alts']);
                        }
                        // Otherwise, if this is a copy player, append based on all the types in the index
                        elseif ($player_info['player_type'] == 'copy' && preg_match('/^(mega-man|proto-man|bass|doc-player)$/i', $player_info['player_token'])){
                            foreach ($mmrpg_database_types AS $type_token => $type_info){
                                if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
                                $temp_alts_array[] = array('token' => $type_token, 'name' => $player_info['player_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
                            }
                        }
                        // Otherwise, if this player has multiple sheets, add them as alt options
                        elseif (!empty($player_info['player_image_sheets'])){
                            for ($i = 2; $i <= $player_info['player_image_sheets']; $i++){
                                $temp_alts_array[] = array('sheet' => $i, 'name' => $player_info['player_name'].' (Sheet #'.$i.')', 'summons' => 0);
                            }
                        }

                        // Loop through sizes to show and generate markup
                        $show_sizes = array();
                        $base_size = $player_image_size;
                        $zoom_size = $player_image_size * 2;
                        $base_size_text = $base_size.'x'.$base_size;
                        $zoom_size_text = $zoom_size.'x'.$zoom_size;
                        $show_sizes[$base_size] = $base_size_text;
                        $show_sizes[$zoom_size] = $zoom_size_text;
                        if ($print_options['layout_style'] === 'website_compact'){ unset($show_sizes[$base_size]); }
                        $size_key = -1;
                        foreach ($show_sizes AS $size_value => $sprite_size_text){
                            $size_key++;
                            $size_is_final = $size_key == (count($show_sizes) - 1);
                            $show_sprite_labels = $size_is_final && $print_options['layout_style'] !== 'website_compact' ? true : false;

                            // Start the output buffer and prepare to collect sprites
                            ob_start();

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
                                        echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_player_image_token.'" data-frame="mugshot" style="'.($size_is_final ? 'padding-top: 20px;' : 'padding: 0;').' float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$size_value.'px; height: '.$size_value.'px; overflow: hidden;">';
                                            echo '<img class="has_pixels" style="margin-left: 0; height: '.$size_value.'px;" data-tooltip="'.$temp_title.'" src="images/players/'.$temp_player_image_token.'/mug_'.$temp_direction.'_'.$sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                            if ($show_sprite_labels){ echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>'; }
                                        echo '</div>';
                                    }


                                    // Loop through the different frames and print out the sprite sheets
                                    foreach ($player_sprite_frames AS $this_key => $this_frame){
                                        $margin_left = ceil((0 - $this_key) * $size_value);
                                        $frame_relative = $this_frame;
                                        //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($player_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                                        $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                                        foreach (array('right', 'left') AS $temp_direction){
                                            $temp_direction2 = substr($temp_direction, 0, 1);
                                            $temp_embed = '[player:'.$temp_direction.':'.$frame_relative.']{'.$temp_player_image_token.'}';
                                            $temp_title = $temp_player_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                            $temp_imgalt = $temp_title;
                                            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                            $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                            //$image_token = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
                                            //if ($temp_sheet > 1){ $temp_player_image_token .= '-'.$temp_sheet; }
                                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_player_image_token.'" data-frame="'.$frame_relative.'" style="'.($size_is_final ? 'padding-top: 20px;' : 'padding: 0;').' float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$size_value.'px; height: '.$size_value.'px; overflow: hidden;">';
                                                echo '<img class="has_pixels" style="margin-left: '.$margin_left.'px; height: '.$size_value.'px;" data-tooltip="'.$temp_title.'" alt="'.$temp_imgalt.'" src="images/players/'.$temp_player_image_token.'/sprite_'.$temp_direction.'_'.$sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                                if ($show_sprite_labels){ echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>'; }
                                            echo '</div>';
                                        }
                                    }

                                }

                            // Collect the sprite markup from the output buffer for later
                            $this_sprite_markup .= '<div class="grid">'.ob_get_clean().'</div>'.PHP_EOL;

                        }


                    }

                    ?>

                    <h2 <?= $print_options['layout_style'] == 'website' ? 'id="sprites"' : '' ?> class="header header_full sprites_header <?= $player_header_types ?>" style="margin: 10px 0 0; text-align: left; overflow: hidden; height: auto; <?= $print_options['layout_style'] == 'website_compact' ? 'display: none;' : '' ?>">
                        Sprite Sheets
                        <span class="header_links image_link_container">
                            <span class="images" style="<?= count($temp_alts_array) == 1 ? 'display: none;' : '' ?>"><?
                                // Loop though and print links for the alts
                                $alt_type_base = 'player_type type_'.(!empty($player_info['player_type']) ? $player_info['player_type'] : 'none').' ';
                                foreach ($temp_alts_array AS $alt_key => $alt_info){
                                    $alt_type = '';
                                    $alt_style = '';
                                    $alt_title = $alt_info['name'];
                                    $alt_title_type = $alt_type_base;
                                    if (preg_match('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', $alt_info['name'])){
                                        $alt_type = strtolower(preg_replace('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', '$1', $alt_info['name']));
                                        $alt_name = '&bull;'; //ucfirst($alt_type); //substr(ucfirst($alt_type), 0, 2);
                                        $alt_title_type = 'player_type type_'.$alt_type.' ';
                                        $alt_type = 'player_type type_'.$alt_type.' type_type ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key == 0 ? $player_info['player_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'player_type type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($player_info['player_type'] == 'copy' && $alt_key == 0){ $alt_type = 'player_type type_empty '; }
                                    }

                                    echo '<a href="#" data-tooltip="'.$alt_title.'" data-tooltip-type="'.$alt_title_type.'" class="link link_image '.($alt_key == 0 ? 'link_active ' : '').'" data-image="'.$alt_info['image'].'">';
                                    echo '<span class="'.$alt_type.'" style="'.$alt_style.'">'.$alt_name.'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                            <span class="pipe" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>">|</span>
                            <span class="directions"><?
                                // Loop though and print links for the alts
                                foreach (array('left', 'right') AS $temp_key => $temp_direction){
                                    echo '<a href="#" data-tooltip="'.ucfirst($temp_direction).' Facing Sprites" data-tooltip-type="'.$alt_type_base.'" class="link link_direction '.($temp_key == 0 ? 'link_active' : '').'" data-direction="'.$temp_direction.'">';
                                    echo '<span class="ability_type ability_type_empty" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ">'.ucfirst($temp_direction).'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                        </span>
                    </h2>

                    <div <?= $print_options['layout_style'] == 'website' ? 'id="sprites_body"' : '' ?> class="body body_full sprites_body solid">
                        <?= $this_sprite_markup ?>
                        <?
                        // Define the editor title based on ID
                        $temp_editor_titles = array();
                        $temp_editor_title = 'Undefined';
                        $temp_final_divider = '<span class="pipe"> | </span>';
                        $editor_ids = array();
                        if (!empty($player_info['player_image_editor'])){ $editor_ids[] = $player_info['player_image_editor']; }
                        if (!empty($player_info['player_image_editor2'])){ $editor_ids[] = $player_info['player_image_editor2']; }
                        if (!empty($editor_ids)){
                            $temp_editor_index = mmrpg_prototype_contributor_index();
                            foreach ($temp_editor_index AS $editor_id => $editor_info){
                                $editor_url = $editor_info['user_name_clean'];
                                if (!in_array($editor_info[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD], $editor_ids)){ continue; }
                                $editor_name = !empty($editor_info['user_name_public']) ? $editor_info['user_name_public'] : $editor_info['user_name'];
                                if (!empty($editor_info['user_name_public'])
                                    && trim(str_replace(' ', '', $editor_info['user_name_public'])) !== trim(str_replace(' ', '', $editor_info['user_name']))
                                    ){
                                    $editor_name = $editor_info['user_name_public'].' / '.$editor_info['user_name'];
                                }
                                $temp_editor_titles[] = '<strong><a href="leaderboard/'.$editor_url.'/">'.$editor_name.'</a></strong>';
                            }
                        }
                        if (!empty($player_info['player_image_editor3'])){
                            $extra_editors = strstr($player_info['player_image_editor3'], ',') ? explode(',', $player_info['player_image_editor3']) : array($player_info['player_image_editor3']);
                            foreach ($extra_editors AS $custom_name){ $temp_editor_titles[] = '<strong>'.trim($custom_name).'</strong>'; }
                        }
                        if (!empty($temp_editor_titles)){
                            $temp_editor_title = implode(' and ', $temp_editor_titles);
                        }
                        $temp_is_capcom = true;
                        $temp_is_original = array('disco', 'rhythm', 'flutter-fly', 'flutter-fly-2', 'flutter-fly-3');
                        if (in_array($player_info['player_token'], $temp_is_original)){ $temp_is_capcom = false; }
                        if ($temp_is_capcom){
                            echo '<p class="text text_editor">Sprite Editing by '.$temp_editor_title.' '.$temp_final_divider.' Original Artwork by <strong>Capcom</strong></p>'."\n";
                        } else {
                            echo '<p class="text text_editor">Sprite Editing by '.$temp_editor_title.' '.$temp_final_divider.' Original Character by <strong>Adrian Marceau</strong></p>'."\n";
                        }
                        ?>
                    </div>

                    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        </div>
                    <? endif; ?>

                <? endif; ?>

                <? if($print_options['show_description'] && !empty($player_info['player_description2'])): ?>

                    <h2 id="description" class="header player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left; ">
                        Description Text
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 0 0 2px; min-height: 10px;">
                        <table class="full description">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="player_description" style="text-align: justify; padding: 0 4px;"><?= preg_replace('/[\r\n]+/', '<br />', $player_info['player_description2']) ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        </div>
                    <? endif; ?>

                <? endif; ?>

                <? if ($print_options['show_quotes']): ?>

                    <h2 id="quotes" class="header player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left; ">
                        Battle Quotes
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <?
                        // Define the search and replace arrays for the player quotes
                        $temp_find = array('{this_player}', '{this_player}', '{target_player}', '{target_player}');
                        $temp_replace = array('Doctor', $player_info['player_name'], 'Doctor', 'Robot');
                        ?>
                        <table class="full quotes">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label>Start Quote : </label>
                                        <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_start']) ? str_replace($temp_find, $temp_replace, $player_info['player_quotes']['battle_start']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Taunt Quote : </label>
                                        <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $player_info['player_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Victory Quote : </label>
                                        <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_victory']) ? str_replace($temp_find, $temp_replace, $player_info['player_quotes']['battle_victory']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Defeat Quote : </label>
                                        <span class="player_quote">&quot;<?= !empty($player_info['player_quotes']['battle_defeat']) ? str_replace($temp_find, $temp_replace, $player_info['player_quotes']['battle_defeat']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        </div>
                    <? endif; ?>

                <? endif; ?>

                <? if($print_options['show_abilities']): ?>
                    <h2 id="abilities" class="header header_full player_type_<?= $player_type_token ?>" style="margin: 10px 0 0; text-align: left;">
                        Player Abilities
                    </h2>
                    <div class="body body_full solid" style="margin: 0; padding: 2px 3px; min-height: 10px;">
                        <table class="full abilities" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="ability_container">
                                        <?
                                        $index_player = $mmrpg_database_players[$player_info['player_token']];
                                        $player_ability_core = !empty($index_player['player_type']) ? $index_player['player_type'] : false;
                                        $player_ability_list = !empty($index_player['player_abilities']) ? $index_player['player_abilities'] : array();
                                        $player_ability_rewards = !empty($player_info['player_rewards']['abilities']) ? $player_info['player_rewards']['abilities'] : array();

                                        //die('<pre>'.print_r($player_info, true).'</pre>');

                                        $new_ability_rewards = array();
                                        foreach ($player_ability_rewards AS $this_info){
                                            $new_ability_rewards[$this_info['token']] = $this_info;
                                        }
                                        $player_copy_program = $player_ability_core == 'copy' ? true : false;
                                        //if ($player_copy_program){ $player_ability_list = $temp_all_ability_tokens; }
                                        $player_ability_core_list = array();
                                        if (!empty($player_ability_core)){
                                            foreach ($mmrpg_database_abilities AS $token => $info){
                                                if (!empty($info['ability_type']) && ($player_copy_program || $info['ability_type'] == $player_ability_core)){
                                                    $player_ability_list[] = $info['ability_token'];
                                                    $player_ability_core_list[] = $info['ability_token'];
                                                }
                                            }
                                        }
                                        foreach ($player_ability_list AS $this_token){
                                            if ($this_token == '*'){ continue; }
                                            if (!isset($new_ability_rewards[$this_token])){
                                                if (in_array($this_token, $player_ability_core_list)){ $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }
                                                else { $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }

                                            }
                                        }
                                        $player_ability_rewards = $new_ability_rewards;

                                        //die('<pre>'.print_r($player_ability_rewards, true).'</pre>');

                                        if (!empty($player_ability_rewards)){
                                            $temp_string = array();
                                            $ability_key = 0;
                                            $ability_method_key = 0;
                                            $ability_method = '';
                                            $temp_robot_info = rpg_robot::get_index_info('mega-man');
                                            $temp_abilities_index = rpg_ability::get_index(true);
                                            foreach ($player_ability_rewards AS $this_info){
                                                $this_points = $this_info['points'];
                                                $this_ability = $temp_abilities_index[$this_info['token']];
                                                $this_ability_token = $this_ability['ability_token'];
                                                $this_ability_name = $this_ability['ability_name'];
                                                $this_ability_image = !empty($this_ability['ability_image']) ? $this_ability['ability_image']: $this_ability['ability_token'];
                                                $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                                if (!empty($this_ability_type) && !empty($mmrpg_database_types[$this_ability_type])){ $this_ability_type = $mmrpg_database_types[$this_ability_type]['type_name'].' Type'; }
                                                else { $this_ability_type = ''; }
                                                $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                                                $this_ability_damage2 = !empty($this_ability['ability_damage2']) ? $this_ability['ability_damage2'] : 0;
                                                $this_ability_damage_percent = !empty($this_ability['ability_damage_percent']) ? true : false;
                                                $this_ability_damage2_percent = !empty($this_ability['ability_damage2_percent']) ? true : false;
                                                if ($this_ability_damage_percent && $this_ability_damage > 100){ $this_ability_damage = 100; }
                                                if ($this_ability_damage2_percent && $this_ability_damage2 > 100){ $this_ability_damage2 = 100; }
                                                $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                                                $this_ability_recovery2 = !empty($this_ability['ability_recovery2']) ? $this_ability['ability_recovery2'] : 0;
                                                $this_ability_recovery_percent = !empty($this_ability['ability_recovery_percent']) ? true : false;
                                                $this_ability_recovery2_percent = !empty($this_ability['ability_recovery2_percent']) ? true : false;
                                                if ($this_ability_recovery_percent && $this_ability_recovery > 100){ $this_ability_recovery = 100; }
                                                if ($this_ability_recovery2_percent && $this_ability_recovery2 > 100){ $this_ability_recovery2 = 100; }
                                                $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                                                $this_ability_description = rpg_ability::get_parsed_ability_description($this_ability);
                                                //$this_ability_title_plain = $this_ability_name;
                                                //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                                                //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                                                //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                                                //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                                                //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                                                $this_ability_title_plain = rpg_ability::print_editor_title_markup($temp_robot_info, $this_ability);

                                                $this_ability_method = 'points';
                                                $this_ability_method_text = 'Start';
                                                $this_ability_title_html = '<strong class="name">'.$this_ability_name.'</strong>';
                                                if ($this_points > 1){ $this_ability_title_html .= '<span class="points">'.str_pad($this_points, 2, '0', STR_PAD_LEFT).' BP</span>'; }
                                                else { $this_ability_title_html .= '<span class="points">Start</span>'; }
                                                if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                                                if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                                                if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                                                if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                                                $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                                                if (!rpg_game::sprite_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_sprite_path = 'images/abilities/ability/icon_left_40x40.png'; }
                                                $this_ability_sprite_html = '<span class="icon"><img src="'.$this_ability_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_ability_name.' Icon" /></span>';
                                                $this_ability_title_html = '<span class="label">'.$this_ability_title_html.'</span>';
                                                //$this_ability_title_html = (is_numeric($this_points) && $this_points > 1 ? 'Lv '.str_pad($this_points, 2, '0', STR_PAD_LEFT).' : ' : $this_points.' : ').$this_ability_title_html;
                                                if ($ability_method != $this_ability_method){
                                                    $temp_separator = ''; //'<div class="ability_separator">'.$this_ability_method_text.'</div>';
                                                    $temp_string[] = $temp_separator;
                                                    $ability_method = $this_ability_method;
                                                    $ability_method_key++;
                                                }
                                                if ($this_points >= 0 || !$player_copy_program){
                                                    $temp_markup = '<a href="'.MMRPG_CONFIG_ROOTURL.'database/abilities/'.$this_ability['ability_token'].'/"  class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').'" title="'.$this_ability_title_plain.'" style="">';
                                                    $temp_markup .= '<span class="chrome">'.$this_ability_sprite_html.$this_ability_title_html.'</span>';
                                                    $temp_markup .= '</a>';
                                                    $temp_string[] = $temp_markup;
                                                    $ability_key++;
                                                    continue;
                                                }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="player_ability player_type type_none"><span class="chrome">None</span></span>';
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>

                <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>

                <? endif; ?>

            </div>
        </div>
        <?
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());
        // Return the generated markup
        return $this_markup;

    }

    // Define a static function for printing out the player's editor markup
    public static function print_editor_markup($player_info){

        // Define the markup variable
        $this_markup = '';
        // Define the global variables
        global $this_userid;
        global $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_fields, $global_allow_editing;
        global $allowed_edit_data_count, $allowed_edit_player_count, $first_player_token;
        global $key_counter, $player_key, $player_counter, $player_rewards, $player_field_rewards, $player_item_rewards, $temp_player_totals, $player_options_markup;
        global $mmrpg_database_robots, $mmrpg_database_items;
        $session_token = mmrpg_game_token();

        // If either fo empty, return error
        if (empty($player_info)){ return 'error:player-empty'; }

        // Collect the approriate database indexes
        if (empty($mmrpg_database_robots)){ $mmrpg_database_robots = rpg_robot::get_index(true); }
        if (empty($mmrpg_database_items)){ $mmrpg_database_items = rpg_item::get_index(true); }

        // Define the quick-access variables for later use
        $player_token = $player_info['player_token'];
        if (!isset($first_player_token)){ $first_player_token = $player_token; }

        // Define the player's image and size if not defined
        $player_info['player_image'] = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
        $player_info['player_image_size'] = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;

        // Define the player's battle points total, battles complete, and other details
        $player_info['player_points'] = mmrpg_prototype_player_points($player_token);
        $player_info['player_battles_complete'] = mmrpg_prototype_battles_complete($player_token);
        $player_info['player_battles_complete_total'] = mmrpg_prototype_battles_complete($player_token, false);
        $player_info['player_battles_failure'] = mmrpg_prototype_battles_failure($player_token);
        $player_info['player_battles_failure_total'] = mmrpg_prototype_battles_failure($player_token, false);
        $player_info['player_robots_count'] = 0;
        $player_info['player_abilities_count'] = mmrpg_prototype_abilities_unlocked();
        $player_info['player_field_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'field');
        $player_info['player_fusion_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'fusion');
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
                        if (!empty($temp_robot_settings['original_player']) && $temp_robot_settings['original_player'] != $player_token){ continue; }
                        //$debug_experience_sum .= $temp_robot_info['robot_token'].', ';
                        $player_info['player_robots_count']++;
                        if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL; }
                        if (!empty($temp_robot_info['robot_experience'])){ $player_info['player_experience'] += $temp_robot_info['robot_experience']; }
                    }
                }
            }
            //die($debug_experience_sum);
        }

        // Collect the player's total turns count so far
        $player_info['battle_turns_player_total'] = !empty($_SESSION[$session_token]['counters']['battle_turns_'.$player_info['player_token'].'_total']) ? $_SESSION[$session_token]['counters']['battle_turns_'.$player_info['player_token'].'_total'] : 0;
        $player_info['battle_turns_total'] = !empty($_SESSION[$session_token]['counters']['battle_turns_total']) ? $_SESSION[$session_token]['counters']['battle_turns_total'] : 0;

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

        // Collect this player's current challenge selection from the session
        $temp_session_key = $player_info['player_token'].'_target-challenge-missions';
        $player_info['player_challenges_current'] = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        $player_info['player_challenges_current'] = array_filter($player_info['player_challenges_current']);

        // Collect this player's current item selection from the omega session
        $temp_session_key = $player_info['player_token'].'_this-item-omega_prototype';
        $player_info['this_item_omega'] = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        $player_info['player_items_current'] = array();
        //if (empty($player_info['this_item_omega']) && !empty($_SESSION[$session_token]['values']['battle_items'])){ $player_info['this_item_omega'] = array_slice(array_keys($_SESSION[$session_token]['values']['battle_items']), 0, 8); }
        //foreach ($player_info['this_item_omega'] AS $key => $token){ $player_info['player_items_current'][] = $mmrpg_database_items[$token]; }

        // Define this player's stat type boost for display purposes
        $player_info['player_stat_type'] = '';
        if (!empty($player_info['player_energy'])){ $player_info['player_stat_type'] = 'energy'; }
        elseif (!empty($player_info['player_attack'])){ $player_info['player_stat_type'] = 'attack'; }
        elseif (!empty($player_info['player_defense'])){ $player_info['player_stat_type'] = 'defense'; }
        elseif (!empty($player_info['player_speed'])){ $player_info['player_stat_type'] = 'speed'; }

        // Check to see if anyone has finished the prototype yet
        $temp_prototype_complete = mmrpg_prototype_complete();

        // Define whether or not challenge switching is enabled
        $temp_allow_challenge_switch = $temp_prototype_complete && mmrpg_prototype_item_unlocked('wily-program');

        // Define whether or not field switching is enabled
        $temp_allow_field_switch = $temp_prototype_complete && mmrpg_prototype_item_unlocked('cossack-program');

        // Collect a temp robot object for printing items
        if ($player_info['player_token'] == 'dr-light'){
            $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['mega-man']);
            $player_info['player_field'] = 'light-laboratory';
        } elseif ($player_info['player_token'] == 'dr-wily'){
            $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['bass']);
            $player_info['player_field'] = 'wily-castle';
        } elseif ($player_info['player_token'] == 'dr-cossack'){
            $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots['proto-man']);
            $player_info['player_field'] = 'cossack-citadel';
        }

        // Start the output buffer
        ob_start();

            // DEBUG
            //echo(print_r($player_field_rewards, true));

            // Collect or define the image size
            $player_info['player_image_size'] = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;
            $player_image_offset = $player_info['player_image_size'] > 40 ? ceil(($player_info['player_image_size'] - 40) * 0.5) : 0;
            $player_image_size_text = $player_info['player_image_size'].'x'.$player_info['player_image_size'];
            $player_image_offset_top = -1 * $player_image_offset;

            // Collect the rewards for this player
            $player_rewards = rpg_game::player_rewards($player_token);

            // Collect the settings for this player
            $player_settings = rpg_game::player_settings($player_token);

            // Collect the summon count from the session if it exists
            $player_info['player_summoned'] = 0;
            if (!empty($player_info['player_battles_complete_total'])){ $player_info['player_summoned'] += $player_info['player_battles_complete_total']; }
            if (!empty($player_info['player_battles_failure_total'])){ $player_info['player_summoned'] += $player_info['player_battles_failure_total']; }

            // Collect any manually unlocked alts from the session if exists
            $player_info['player_altimages'] = mmrpg_prototype_player_altimage_unlocked($player_token);

            // Collect the alt images if there are any that are unlocked
            $player_alt_count = 1 + (!empty($player_info['player_image_alts']) ? count($player_info['player_image_alts']) : 0);
            $player_alt_options = array();
            if (!empty($player_info['player_image_alts'])){
                foreach ($player_info['player_image_alts'] AS $alt_key => $alt_info){
                    $is_unlocked = false;
                    if (in_array($alt_info['token'], $player_info['player_altimages'])){ $is_unlocked = true; }
                    elseif ($player_info['player_summoned'] >= $alt_info['summons']){ $is_unlocked = true; $player_info['player_altimages'][] = $alt_info['token']; }
                    if (!$is_unlocked){ continue; }
                    $player_alt_options[] = $alt_info['token'];
                }
            }

            // Collect the current unlock image token for this player
            $player_image_unlock_current = 'base';
            if (!empty($player_settings['player_image']) && strstr($player_settings['player_image'], '_')){
                list($token, $player_image_unlock_current) = explode('_', $player_settings['player_image']);
            }

            // Define the offsets for the image tokens based on count
            $token_first_offset = 2;
            $token_other_offset = 6;
            if ($player_alt_count == 1){ $token_first_offset = 17; }
            elseif ($player_alt_count == 3){ $token_first_offset = 10; }

            // Loop through and generate the player image display token markup
            $player_image_unlock_tokens = '';
            $temp_total_alts_count = 0;
            $max_alt_slots = 12;
            $break_after_slot = 6;
            for ($i = 0; $i < $max_alt_slots; $i++){
                $temp_enabled = true;
                $temp_active = false;
                if ($i + 1 > $player_alt_count){ break; }
                if ($i > 0 && !isset($player_alt_options[$i - 1])){ $temp_enabled = false; }
                if ($temp_enabled && $i == 0 && $player_image_unlock_current == 'base'){ $temp_active = true; }
                elseif ($temp_enabled && $i >= 1 && $player_image_unlock_current == $player_alt_options[$i - 1]){ $temp_active = true; }
                $rel_i = ($i >= $break_after_slot) ? ($i - $break_after_slot) : $i;
                $left_offset = ($token_first_offset + ($rel_i * $token_other_offset));
                $bottom_offset = ($i >= $break_after_slot) ? -17 : -12;
                $player_image_unlock_tokens .= '<span class="token token_'.($temp_enabled ? 'enabled' : 'disabled').' '.($temp_active ? 'token_active' : '').'" style="left: '.$left_offset.'px; bottom: '.$bottom_offset.'px;">&bull;</span>';
                $temp_total_alts_count += 1;
            }
            $temp_unlocked_alts_count = count($player_alt_options) + 1;
            $temp_image_alt_title = '';
            if ($temp_total_alts_count > 1){
                $temp_image_alt_title = '<strong>'.$temp_unlocked_alts_count.' / '.$player_alt_count.' Outfits Unlocked</strong><br />';
                //$temp_image_alt_title .= '<span style="font-size: 90%;">';
                    $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$player_info['player_name'].'</span><br />';
                    foreach ($player_info['player_image_alts'] AS $alt_key => $alt_info){
                        if (
                            ($player_info['player_summoned'] >= $alt_info['summons']) ||
                            (in_array($alt_info['token'], $player_info['player_altimages']))
                            ){
                            $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$alt_info['name'].'</span><br />';
                        } else {
                            $temp_image_alt_title .= '&#9702; <span style="font-size: 90%;">???</span><br />';
                        }
                    }
                //$temp_image_alt_title .= '</span>';
                $temp_image_alt_title = htmlentities($temp_image_alt_title, ENT_QUOTES, 'UTF-8', true);
            }

            // Check to see which size this player container should be based on unlocks
            $event_container_size = 1;
            if (mmrpg_prototype_item_unlocked('wily-program')){ $event_container_size++; }
            if (mmrpg_prototype_item_unlocked('cossack-program')){ $event_container_size++; }

            ?>
            <div class="event event_double event_<?= $player_key == $first_player_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token']?>" data-size="<?= $event_container_size ?>">

                <? /*
                <div class="this_sprite sprite_left" style="top: 4px; left: 4px; width: 36px; height: 36px; background-image: url(images/fields/<?= $player_info['player_field'] ?>/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; border: 1px solid #1A1A1A;">
                    <? $temp_margin = -1 * ceil(($player_info['player_image_size'] - 40) * 0.5); ?>
                    <div class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_00" style="margin-top: -4px; margin-left: -2px; background-image: url(images/players/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/sprite_right_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); "><?= $player_info['player_name']?></div>
                </div>
                */ ?>

                <div class="this_sprite sprite_left event_player_mugshot">
                    <? $temp_offset = $player_info['player_image_size'] == 80 ? '-20px' : '0'; ?>
                    <div class="sprite_wrapper player_type player_type_<?= !empty($player_info['player_stat_type']) ? $player_info['player_stat_type'] : 'none' ?>" style="width: 33px;">
                        <div class="sprite_wrapper player_type player_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px; background-image: url(images/fields/<?= $player_info['player_field'] ?>/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; background-size: 50px 50px;"></div>
                        <? /* <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(images/players/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/mug_right_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_mug player_status_active player_position_active"><?= $player_info['player_name']?></div> */ ?>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_player_images" style="">
                    <? if($global_allow_editing && !empty($player_alt_options)): ?>
                        <a class="player_image_alts" data-player="<?= $player_token ?>" data-player="<?= $player_token ?>" data-alt-index="base<?= !empty($player_alt_options) ? ','.implode(',', $player_alt_options) : '' ?>" data-alt-current="<?= $player_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <? $temp_offset = $player_info['player_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $player_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 14px; background-image: url(images/players/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/sprite_right_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_base player_status_active player_position_active"><?= $player_info['player_name']?></div>
                            </span>
                        </a>
                    <? else: ?>
                        <span class="player_image_alts" data-player="<?= $player_token ?>" data-player="<?= $player_token ?>" data-alt-index="base<?= !empty($player_alt_options) ? ','.implode(',', $player_alt_options) : '' ?>" data-alt-current="<?= $player_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <? $temp_offset = $player_info['player_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $player_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 14px; background-image: url(images/players/<?= !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'] ?>/sprite_right_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_player sprite_player_sprite sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?> sprite_<?= $player_info['player_image_size'].'x'.$player_info['player_image_size'] ?>_base player_status_active player_position_active"><?= $player_info['player_name']?></div>
                            </span>
                        </span>
                    <? endif; ?>
                </div>

                <div class="header header_left player_type player_type_<?= !empty($player_info['player_stat_type']) ? $player_info['player_stat_type'] : 'none' ?>" style="margin-right: 0;">
                    <span class="title player_type">
                        <?=$player_info['player_name']?>
                    </span>
                    <?

                    // Only show omega indicators if the the Omega Seed has been unlocked
                    if (mmrpg_prototype_item_unlocked('omega-seed')){

                        // Collect possible hidden power types
                        $hidden_power_types = rpg_type::get_hidden_powers('elements');

                        // Generate this player's omega string, collect it's hidden power
                        $player_omega_string = rpg_game::generate_omega_player_string($player_info['player_token']);
                        $player_hidden_power = rpg_game::select_omega_value($player_omega_string, $hidden_power_types);

                        // Print out the omega indicators for the shop
                        echo '<span class="omega player_type type_'.$player_hidden_power.'" title="Omega Influence || [['.ucfirst($player_hidden_power).' Type]]"></span>'.PHP_EOL;
                        //title="Omega Influence || [['.ucfirst($player_hidden_power).' Type]]"

                    }

                    ?>
                    <span class="core player_type">
                        <!-- <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/items/<?= !empty($player_info['player_stat_type']) ? $player_info['player_stat_type'].'-pellet' : 'item' ?>/icon_left_40x40.png);"></span></span> -->
                        <span class="text">Skill: <?= ucfirst($player_info['player_stat_type']).' +'.$player_info['player_'.$player_info['player_stat_type']].'%' ?></span>
                    </span>
                </div>

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
                                    <label style="display: block; float: left;">Full Name :</label>
                                    <span class="player_name player_type player_type_none"><?
                                        if ($player_info['player_token'] === 'dr-light'){ echo 'Dr. Thomas X. Light'; } // Xavier? [fanon or confirmed?]
                                        elseif ($player_info['player_token'] === 'dr-wily'){ echo 'Dr. Albert W. Wily'; } // William? [fanon]
                                        elseif ($player_info['player_token'] === 'dr-cossack'){ echo 'Dr. Mikhail S. Cossack'; } // Sergeyevich [confirmed]
                                        else { echo $player_info['player_name']; }
                                    ?></span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label style="display: block; float: left;">Exp Points :</label>
                                    <span class="player_stat player_type player_type_none"><?= number_format($player_info['player_experience'], 0, '.', ',') ?> EXP</span>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Robots :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_robots_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_robots_count'].' '.($player_info['player_robots_count'] == 1 ? 'Robot' : 'Robots') ?></span>
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
                                    <label style="display: block; float: left;">Total Turns :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['battle_turns_player_total']) ? 'cutter' : 'none' ?>" title="<?= $player_info['battle_turns_player_total'].' of '.$player_info['battle_turns_total'].' Turns Overall' ?>"><?= $player_info['battle_turns_player_total'] == 1 ? '1 Turn' : $player_info['battle_turns_player_total'].' Turns'  ?></span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <? if(!empty($player_info['player_field_stars'])
                                        || !empty($player_info['player_fusion_stars'])): ?>
                                        <label style="display: block; float: left;">Stars Collected :</label>
                                        <? $total_stars_collected = $player_info['player_field_stars'] + $player_info['player_fusion_stars']; ?>
                                        <span class="player_stat player_type player_type_cutter" title="<?= 'Field x'.$player_info['player_field_stars'].' | Fusion x'.$player_info['player_fusion_stars'] ?>"><?= $total_stars_collected.' '.($total_stars_collected == 1 ? 'Star' : 'Stars') ?></span>
                                    <? else: ?>
                                        <label style="display: block; float: left; opacity: 0.5; filter: alpha(opacity=50); ">??? :</label>
                                        <span class="player_stat player_type player_type_empty" style=" opacity: 0.5; filter: alpha(opacity=50); ">0</span>
                                    <? endif; ?>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <?

                    // Collect a field index in case we need it later
                    global $mmrpg_index_types, $mmrpg_index_fields, $mmrpg_index_robots;
                    if (empty($mmrpg_index_types)){ $mmrpg_index_types = rpg_type::get_index(); }
                    if (empty($mmrpg_index_fields)){ $mmrpg_index_fields = rpg_field::get_index(); }
                    if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(); }

                    ?>

                    <? if(mmrpg_prototype_item_unlocked('wily-program')){ ?>

                        <?
                        // Collect a list of applicable challenges from the database
                        $temp_prototype_data = array();
                        $temp_prototype_data['this_current_chapter'] = '0';
                        $temp_prototype_data['phase_battle_token'] = '0';

                        // Decide which types of missions we can show here
                        $include_event_challenges = true;
                        $include_user_challenges = false;
                        $include_hidden_challenges = false;
                        if (rpg_user::current_user_has_permission('edit-user-challenges')){
                            $include_user_challenges = true;
                        }
                        if (rpg_user::current_user_has_permission('edit-challenges')){
                            $include_hidden_challenges = true;
                        }

                        // Define an array to hold all available challenge missions
                        $available_challenge_missions = array();
                        $available_challenge_missions['event'] = array();
                        $available_challenge_missions['user'] = array();

                        // Pull EVENT CHALLENGE data from the database given filters and ordering
                        if ($include_event_challenges){
                            $query_conditions = '';
                            $query_conditions .= 'AND challenges.challenge_flag_published = 1 ';
                            $query_conditions .= 'AND challenges.challenge_kind = \'event\' ';
                            if (!$include_hidden_challenges){
                                if (!rpg_user::is_guest()){ $query_conditions .= 'AND (challenges.challenge_flag_hidden = 0 OR challenges.challenge_creator = '.$this_userid.')'; }
                                else { $query_conditions .= 'AND challenges.challenge_flag_hidden = 0'; }
                            }
                            $challenge_table = 'mmrpg_challenges';
                            $challenge_fields = rpg_mission_challenge::get_index_fields(true, 'challenges');
                            $available_challenge_missions['event'] = $db->get_array_list("SELECT
                                {$challenge_fields},
                                (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name
                                FROM {$challenge_table} AS challenges
                                LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator
                                WHERE 1 = 1 {$query_conditions}
                                ORDER BY
                                challenges.challenge_creator ASC,
                                challenges.challenge_id ASC
                                LIMIT 100
                                ;", 'challenge_id');
                        }

                        // Pull USER CHALLENGE data from the database given filters and ordering
                        if ($include_user_challenges){
                            $query_conditions = '';
                            $query_conditions .= 'AND challenges.challenge_flag_published = 1 ';
                            $query_conditions .= 'AND challenges.challenge_kind = \'user\' ';
                            if (!$include_hidden_challenges){
                                if (!rpg_user::is_guest()){ $query_conditions .= 'AND (challenges.challenge_flag_hidden = 0 OR challenges.challenge_creator = '.$this_userid.')'; }
                                else { $query_conditions .= 'AND challenges.challenge_flag_hidden = 0'; }
                            }
                            $challenge_table = 'mmrpg_users_challenges';
                            $challenge_fields = rpg_mission_challenge::get_index_fields(true, 'challenges');
                            $available_challenge_missions['user'] = $db->get_array_list("SELECT
                                {$challenge_fields},
                                (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name
                                FROM {$challenge_table} AS challenges
                                LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator
                                WHERE 1 = 1 {$query_conditions}
                                ORDER BY
                                challenges.challenge_creator ASC,
                                challenges.challenge_id ASC
                                LIMIT 100
                                ;", 'challenge_id');
                        }

                        // Pull any challenge records this player has from the leaderboard
                        $challenge_mission_victories = array();
                        $challenge_mission_victories['event'] = array();
                        $challenge_mission_victories['user'] = array();
                        if ($global_allow_editing
                            && !empty($this_userid)
                            && !rpg_user::is_guest()){
                            if ($include_event_challenges){
                                $challenge_table = 'mmrpg_challenges';
                                $challenge_leaderboard_table = 'mmrpg_challenges_leaderboard';
                                $challenge_mission_victories['event'] = $db->get_array_list("SELECT
                                    board.challenge_id,
                                    board.challenge_turns_used,
                                    challenges.challenge_turn_limit,
                                    board.challenge_robots_used,
                                    challenges.challenge_robot_limit,
                                    board.challenge_result
                                    FROM {$challenge_leaderboard_table} AS board
                                    LEFT JOIN {$challenge_table} AS challenges ON challenges.challenge_id = board.challenge_id
                                    WHERE
                                    board.user_id = {$this_userid}
                                    AND board.challenge_result = 'victory'
                                    ;", 'challenge_id');
                            }
                            if ($include_user_challenges){
                                $challenge_table = 'mmrpg_users_challenges';
                                $challenge_leaderboard_table = 'mmrpg_users_challenges_leaderboard';
                                $challenge_mission_victories['user'] = $db->get_array_list("SELECT
                                    board.challenge_id,
                                    board.challenge_turns_used,
                                    challenges.challenge_turn_limit,
                                    board.challenge_robots_used,
                                    challenges.challenge_robot_limit,
                                    board.challenge_result
                                    FROM {$challenge_leaderboard_table} AS board
                                    LEFT JOIN {$challenge_table} AS challenges ON challenges.challenge_id = board.challenge_id
                                    WHERE
                                    board.user_id = {$this_userid}
                                    AND board.challenge_result = 'victory'
                                    ;", 'challenge_id');
                            }
                        }

                        // If challenge missions have not been selected yet, padd-out with recent items
                        if (empty($player_info['player_challenges_current'])
                            || (count($player_info['player_challenges_current']) < 3
                                && !empty($available_challenge_missions['event']))){
                            foreach ($available_challenge_missions['event'] AS $challenge_id => $challenge_info){
                                $player_info['player_challenges_current'][] = $challenge_id;
                                if (count($player_info['player_challenges_current']) >= 3){ break; }
                            }
                            $temp_session_key = $player_info['player_token'].'_target-challenge-missions';
                            $_SESSION[$session_token]['values'][$temp_session_key] = $player_info['player_challenges_current'];
                        }

                        // Collect
                        //echo('<pre>$challenge_mission_victories['event'] = '.print_r($challenge_mission_victories['event'], true).'</pre>');
                        //exit;
                        //echo('<pre>$available_challenge_missions['event'] = '.print_r($available_challenge_missions['event'], true).'</pre>');
                        //exit;
                        //echo('<pre>$player_info[\'player_challenges_current\'] = '.print_r($player_info['player_challenges_current'], true).'</pre>');
                        //exit;

                        ?>

                        <table class="full">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right" style="padding-top: 4px;">
                                        <label class="challenge_header">Challenge Board :
                                            <span style="font-size: 80%; color: #969696; position: relative; bottom: 1px;">
                                            (Special challenge missions created by the dev team. Select any three at once.)
                                            <? /* (Special challenge missions created by other players. Select any three from your playlist.) */ ?>
                                            </span></label>
                                        <div class="challenge_container" style="height: auto;">
                                        <?

                                        // Define the array to hold ALL the reward option markup
                                        $challenge_rewards_options = '';
                                        //$challenge_rewards_options .= '<option value="0">- Select Mission -</option>';

                                        // Don't bother generating the option markup if disabled editing
                                        if ($global_allow_editing
                                            && !empty($available_challenge_missions['event'])){

                                            // Loop through all the challenges and generate options, group by type
                                            $group_kind = '';
                                            foreach ($available_challenge_missions AS $challenge_kind => $challenge_missions){
                                                foreach ($challenge_missions AS $challenge_id => $challenge_info){
                                                    if (empty($group_kind) || $group_kind != $challenge_kind){
                                                        $group_kind = $challenge_kind;
                                                        $group_name = $challenge_kind == 'event' ? 'Event Challenges' : 'User Challenges';
                                                        if (!empty($group_kind)){ $challenge_rewards_options .= '</optgroup>'; }
                                                        $challenge_rewards_options .= '<optgroup label="'.$group_name.'">';
                                                    }
                                                    $option_markup = rpg_mission_challenge::print_editor_option_markup($challenge_info, $challenge_mission_victories[$challenge_kind], $mmrpg_index_fields, $mmrpg_index_robots);
                                                    $challenge_rewards_options .= $option_markup;
                                                }
                                            }
                                            if (!empty($group_kind)){ $challenge_rewards_options .= '</optgroup>'; }

                                        }

                                        // Loop through the player's current challenges and list them one by one
                                        $empty_challenge_counter = 0;
                                        $temp_string = array();
                                        $temp_inputs = array();
                                        $challenge_key = 0;
                                        $allowed_event_challenge_ids = array_keys($available_challenge_missions['event']);
                                        $allowed_user_challenge_ids = array_keys($available_challenge_missions['user']);
                                        if (!empty($player_info['player_challenges_current'])){
                                            foreach ($player_info['player_challenges_current'] AS $challenge_id){
                                                if ($challenge_key > 2){ break; }
                                                if (substr($challenge_id, 0, 1) === 'u'){ $challenge_kind = 'user'; $challenge_id = (int)(substr($challenge_id, 1)); }
                                                else { $challenge_kind = 'event'; $challenge_id = (int)($challenge_id); }
                                                if ($challenge_kind === 'event' && !in_array($challenge_id, $allowed_event_challenge_ids)){ continue; }
                                                elseif ($challenge_kind === 'user' && !in_array($challenge_id, $allowed_user_challenge_ids)){ continue; }
                                                $challenge_info = $available_challenge_missions[$challenge_kind][$challenge_id];
                                                $this_challenge_id = $challenge_info['challenge_id'];
                                                $this_challenge_name = $challenge_info['challenge_name'];
                                                if (!empty($challenge_mission_victories[$challenge_kind][$this_challenge_id])){ $this_challenge_name = '&#9733; '.$this_challenge_name; }
                                                $this_field_data = json_decode($challenge_info['challenge_field_data'], true);
                                                $this_field_info1 = $mmrpg_index_fields[$this_field_data['field_background']];
                                                $this_field_info2 = $mmrpg_index_fields[$this_field_data['field_foreground']];
                                                $this_challenge_title = rpg_mission_challenge::print_editor_title_markup($challenge_info, $challenge_mission_victories[$challenge_kind], $mmrpg_index_fields, $mmrpg_index_robots);
                                                $this_challenge_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_challenge_title));
                                                $this_challenge_title_tooltip = htmlentities($this_challenge_title, ENT_QUOTES, 'UTF-8');
                                                $this_challenge_title_html = str_replace(' ', '&nbsp;', $this_challenge_name);
                                                $temp_select_options = str_replace('value="'.$this_challenge_id.'"', 'value="'.$this_challenge_id.'" selected="selected" disabled="disabled"', $challenge_rewards_options);
                                                $temp_challenge_type_class1 = 'field_type_'.(!empty($this_field_info1['field_type']) ? $this_field_info1['field_type'] : 'none');
                                                //$temp_challenge_type_class2 = 'field_type_'.(!empty($this_field_info2['field_type']) ? $this_field_info2['field_type'] : 'none');
                                                $temp_challenge_type_class3 = 'field_type_'.(!empty($this_field_info1['field_type']) ? $this_field_info1['field_type'] : 'none').((!empty($this_field_info2['field_type']) && $this_field_info2['field_type'] != 'none' && $this_field_info2['field_type'] != $this_field_info1['field_type']) ? '_'.$this_field_info2['field_type'] : '');
                                                if ($global_allow_editing && $temp_allow_challenge_switch){ $this_challenge_title_html = '<label class="field_type  '.$temp_challenge_type_class3.'" style="">'.$this_challenge_title_html.'</label><select class="challenge_name" data-key="'.$challenge_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                                                elseif (!$global_allow_editing && $temp_allow_challenge_switch){ $this_challenge_title_html = '<label class="field_type  '.$temp_challenge_type_class3.'" style="cursor: default !important;">'.$this_challenge_title_html.'</label>'; }
                                                else { $this_challenge_title_html = '<label class="field_type '.$temp_challenge_type_class3.'" style="cursor: default !important;">'.$this_challenge_title_html.'</label>'; }
                                                $temp_string[] = '<a class="challenge_name challenge_battle field_type '.$temp_challenge_type_class1.'" style="background-image: url(images/fields/'.$this_field_info1['field_token'].'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.') !important; '.(($challenge_key + 1) % 3 == 0 ? 'margin-right: 0; ' : '').(!$temp_allow_challenge_switch || !$global_allow_editing ? 'cursor: default !important; ' : '').(!$temp_allow_challenge_switch ? 'opacity: 0.50; filter: alpha(opacity=50); ' : '').'" data-key="'.$challenge_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-challenge="'.$this_challenge_id.'" data-tooltip="'.$this_challenge_title_tooltip.'" data-tooltip-type="field_type '.$temp_challenge_type_class3.'">'.$this_challenge_title_html.'</a>';
                                                $challenge_key++;
                                            }

                                            if ($challenge_key <= 2){
                                                for ($challenge_key; $challenge_key <= 2; $challenge_key++){
                                                    $empty_challenge_counter++;
                                                    if ($empty_challenge_counter >= 2){ $empty_challenge_disable = true; }
                                                    else { $empty_challenge_disable = false; }
                                                    $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $challenge_rewards_options);
                                                    $this_challenge_title_html = '<label>-</label><select class="challenge_name" data-key="'.$challenge_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_challenge_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                                                    $temp_string[] = '<a class="challenge_name challenge_battle " style="'.(($challenge_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_challenge_disable ? 'opacity:0.25; ' : '').'" data-key="'.$challenge_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-challenge="" title="">'.$this_challenge_title_html.'</a>';
                                                }
                                            }


                                        } else {

                                            for ($challenge_key = 0; $challenge_key <= 2; $challenge_key++){
                                                $empty_challenge_counter++;
                                                if ($empty_challenge_counter >= 2){ $empty_challenge_disable = true; }
                                                else { $empty_challenge_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $challenge_rewards_options);
                                                $this_challenge_title_html = '<label>-</label><select class="challenge_name" data-key="'.$challenge_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" '.($empty_challenge_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>';
                                                $temp_string[] = '<a class="challenge_name challenge_battle " style="'.(($challenge_key + 1) % 3 == 0 ? 'margin-right: 0; ' : '').($empty_challenge_disable ? 'opacity:0.25; ' : '').'" data-key="'.$challenge_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-challenge="" title="">'.$this_challenge_title_html.'</a>';
                                            }

                                        }

                                        echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                        echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';

                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <? } ?>

                    <? if(mmrpg_prototype_item_unlocked('cossack-program')){ ?>

                        <table class="full">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right" style="padding-top: 4px;">
                                        <label class="field_header">Mission Customizer :
                                            <span style="font-size: 80%; color: #969696; position: relative; bottom: 1px;">
                                            (Used in Chapter 2 + 4 of the player's campaign. Also influences Player Battles.)
                                            </span></label>
                                        <div class="field_container" style="height: auto;">
                                        <?

                                        // Define the array to hold ALL the reward option markup
                                        $field_rewards_options = '';

                                        // Collect this player's field rewards and add them to the dropdown
                                        //$player_field_rewards = !empty($player_rewards['player_fields']) ? $player_rewards['player_fields'] : array();
                                        //if (!empty($player_field_rewards)){ sort($player_field_rewards); }

                                        // DEBUG
                                        //echo 'before:'.implode(',', array_keys($player_field_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                                        //echo 'before:'.implode(',', array_keys($debug_tokens)).'<br />';

                                        // Sort the field index based on field number
                                        uasort($player_field_rewards, array('rpg_player', 'fields_sort_for_editor'));

                                        // DEBUG
                                        //echo 'after:'.implode(',', array_keys($player_field_rewards)).'<br />';

                                        // DEBUG
                                        //$debug_tokens = array();
                                        //foreach ($player_field_rewards AS $info){ $debug_tokens[] = $info['field_token']; }
                                        //echo 'after:'.implode(',', $debug_tokens).'<br />';

                                        // Don't bother generating the option markup if disabled editing
                                        if ($global_allow_editing){

                                            // Loop through and create an index of unlocked fields
                                            $temp_unlocked_field_tokens = array();
                                            if (!empty($player_field_rewards)){ foreach ($player_field_rewards AS $key => $info){ $temp_unlocked_field_tokens[] = $info['field_token']; } }

                                            // Loop through index fields and print out any that have been unlocked
                                            $last_field_group = false;
                                            foreach ($mmrpg_index_fields AS $field_token => $field_info){
                                                if (!in_array($field_token, $temp_unlocked_field_tokens)){ continue; }
                                                $field_group = $field_info['field_game'];
                                                if ($field_group !== $last_field_group){
                                                    if (!empty($last_field_group)){ $field_rewards_options .= '</optgroup>'; }
                                                    $field_group_name = rpg_game::get_source_name($field_group, false).' Fields';
                                                    $field_rewards_options .= '<optgroup label="'.$field_group_name.'">';
                                                    $last_field_group = $field_group;
                                                }
                                                $field_rewards_options .= rpg_field::print_editor_option_markup($player_info, $field_info);
                                            }
                                            if (!empty($last_field_group)){ $field_rewards_options .= '</optgroup>'; }

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
                                            $player_info['player_fields_current'] = $player_info['player_fields_current']; //array_reverse($player_info['player_fields_current']);
                                            foreach ($player_info['player_fields_current'] AS $player_field){

                                                if ($player_field['field_token'] == '*'){ continue; }
                                                elseif (!isset($mmrpg_index_fields[$player_field['field_token']])){ continue; }
                                                elseif ($field_key > 7){ continue; }
                                                $this_field = rpg_field::parse_index_info($mmrpg_index_fields[$player_field['field_token']]);
                                                $this_field_token = $this_field['field_token'];
                                                $this_robot_token = $this_field['field_master'];
                                                $this_robot = rpg_robot::parse_index_info($mmrpg_database_robots[$this_robot_token]);
                                                $this_field_name = $this_field['field_name'];
                                                $this_field_type = !empty($this_field['field_type']) ? $this_field['field_type'] : false;
                                                $this_field_type2 = !empty($this_field['field_type2']) ? $this_field['field_type2'] : false;
                                                if (!empty($this_field_type) && !empty($mmrpg_index_types[$this_field_type])){
                                                    $this_field_type = $mmrpg_index_types[$this_field_type]['type_name'].' Type';
                                                    if (!empty($this_field_type2) && !empty($mmrpg_index_types[$this_field_type2])){
                                                        $this_field_type = str_replace(' Type', ' / '.$mmrpg_index_types[$this_field_type2]['type_name'].' Type', $this_field_type);
                                                    }
                                                } else {
                                                    $this_field_type = '';
                                                }
                                                $this_field_description = !empty($this_field['field_description']) ? $this_field['field_description'] : '';
                                                $this_field_title = rpg_field::print_editor_title_markup($player_info, $this_field);
                                                $this_field_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_field_title));
                                                $this_field_title_tooltip = htmlentities($this_field_title, ENT_QUOTES, 'UTF-8');
                                                $this_field_title_html = str_replace(' ', '&nbsp;', $this_field_name);
                                                $temp_select_options = str_replace('value="'.$this_field_token.'"', 'value="'.$this_field_token.'" selected="selected" disabled="disabled"', $field_rewards_options);
                                                $temp_field_type_class = 'field_type_'.(!empty($this_field['field_type']) ? $this_field['field_type'] : 'none').(!empty($this_field['field_type2']) ? '_'.$this_field['field_type2'] : '');
                                                if ($global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="">'.$this_field_title_html.'</label><select class="field_name" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'">'.$temp_select_options.'</select>'; }
                                                elseif (!$global_allow_editing && $temp_allow_field_switch){ $this_field_title_html = '<label class="field_type  '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                                                else { $this_field_title_html = '<label class="field_type '.$temp_field_type_class.'" style="cursor: default !important;">'.$this_field_title_html.'</label>'; }
                                                $temp_string[] = '<a class="field_name field_type '.$temp_field_type_class.'" style="background-image: url(images/fields/'.$this_field_token.'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.(($field_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$temp_allow_field_switch || !$global_allow_editing ? 'cursor: default !important; ' : '').(!$temp_allow_field_switch ? 'opacity: 0.50; filter: alpha(opacity=50); ' : '').'" data-key="'.$field_key.'" data-player="'.$player_info['player_token'].'" data-player="'.$player_info['player_token'].'" data-field="'.$this_field_token.'" data-tooltip="'.$this_field_title_tooltip.'">'.$this_field_title_html.'</a>';
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
                                        $temp_star_counts = mmrpg_prototype_player_stars_available($player_token);
                                        ?>
                                        <div class="field_stars">
                                            <label class="label">stars</label>
                                            <span class="star star_field" data-star="field"><?= $temp_star_counts['field'] ?> field</span>
                                            <span class="star star_fusion" data-star="fusion"><?= $temp_star_counts['fusion'] ?> fusion</span>
                                        </div>
                                        <?
                                        // Print the sort wrapper and options if allowed
                                        if ($global_allow_editing){
                                            ?>
                                            <div class="field_tools">
                                                <label class="label">tools</label>
                                                <a class="tool tool_shuffle" data-tool="shuffle" data-player="<?= $player_token ?>">shuffle</a>
                                                <a class="tool tool_randomize" data-tool="randomize" data-player="<?= $player_token ?>">randomize</a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    <? }?>

                </div>
            </div>
            <?
            $key_counter++;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

    // Define a static function for printing out the player's title markup
    public static function print_editor_title_markup($player_info, $print_options = array()){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Collect the types index for reference
        $mmrpg_types = rpg_type::get_index(true);

        // Generta the basic player fields for display
        $temp_player_title = '';
        $temp_player_token = $player_info['player_token'];
        $temp_player_name = $player_info['player_name'];
        $temp_player_type = !empty($player_info['player_type']) ? $mmrpg_types[$player_info['player_type']] : false;

        // Generate the player title based on available info
        $temp_player_title = $player_info['player_name'];

        // Generate the player description based on stat boosts
        $temp_description = '';
        if (!empty($temp_player_type)){
            $bonus_stat_token = $temp_player_type['type_token'];
            $bonus_stat_name = $temp_player_type['type_name'];
            $bonus_stat_value = !empty($player_info['player_'.$bonus_stat_token]) ? $player_info['player_'.$bonus_stat_token] : 0;
            $temp_description .= 'Skill : +'.$bonus_stat_value.'% '.$bonus_stat_name.'';
        }

        $temp_player_title .= ' // '.$temp_description;

        // Return the generated option markup
        return $temp_player_title;

    }


    // Define a static function for printing out the item select markup
    public static function print_editor_select_markup($player_info, $player_key = 0, $player_rewards = array(), $player_settings = array()){

        // Define the global variables
        global $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players;
        global $allowed_edit_data_count, $allowed_edit_player_count, $first_player_token, $global_allow_editing;
        global $key_counter;

        // Collect the current session token
        $session_token = rpg_game::session_token();

        // Collect the types index for reference
        $mmrpg_types = rpg_type::get_index(true);

        // Require the function file
        $this_select_markup = '';

        // Collect basic info about this player
        $player_info_token = $player_info['player_token'];
        $player_image_token = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
        $player_info_name = $player_info['player_name'];
        $player_info_type = !empty($player_info['player_type']) ? $mmrpg_types[$player_info['player_type']] : false;
        $player_info_class_type = !empty($player_info['player_type']) ? $player_info['player_type'] : 'none';

        // Generate the player title and tooltip markup
        $player_info_title = rpg_player::print_editor_title_markup($player_info);
        $player_info_title_plain = strip_tags(str_replace('<br />', '//', $player_info_title));
        $player_info_title_tooltip = htmlentities($player_info_title, ENT_QUOTES, 'UTF-8');
        $player_info_title_html = str_replace(' ', '&nbsp;', $player_info_name);
        $player_info_title_html = '<label style="background-image: url(images/players/'.$player_image_token.'/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$player_info_title_html.'</label>';
        $this_select_markup = '<a class="player_name type type_'.$player_info_class_type.'" style="'.(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$player_key.'" data-player="'.$player_info['player_token'].'" title="'.$player_info_title_plain.'" data-tooltip="'.$player_info_title_tooltip.'">'.$player_info_title_html.'</a>';

        // Return the generated select markup
        return $this_select_markup;


    }


    /* -- MISC PLAYER FUNCTIONS -- */

    // Define a function for getting a list of intro fields for players
    public static function get_intro_fields(){
        return array(
            'default' => 'intro-field',
            'dr-light' => 'gentle-countryside',
            'dr-wily' => 'maniacal-hideaway',
            'dr-cossack' => 'wintry-forefront'
            );
    }

    // Define a function for calculating the intro field for a given player
    public static function get_intro_field($player_token = '', $return_info_array = false){
        $intro_field_index = self::get_intro_fields();
        if (isset($intro_field_index[$player_token])){ $intro_field = $intro_field_index[$player_token]; }
        else { $intro_field = $intro_field_index['default']; }
        if ($return_info_array){
            return array(
                'field_token' => $intro_field,
                'field_name' => ucwords(str_replace('-', ' ', $intro_field))
                );
        } else {
            return $intro_field;
        }
    }

    // Define a function for getting a list of homebase fields for players
    public static function get_homebase_fields(){
        return array(
            'default' => 'intro-field',
            'dr-light' => 'light-laboratory',
            'dr-wily' => 'wily-castle',
            'dr-cossack' => 'cossack-citadel'
            );
    }


    // Define a function for calculating the homebase field for a given player
    public static function get_homebase_field($player_token = '', $return_info_array = false){
        $homebase_field_index = self::get_homebase_fields();
        if (isset($homebase_field_index[$player_token])){ $homebase_field = $homebase_field_index[$player_token]; }
        else { $homebase_field = $homebase_field_index['default']; }
        if ($return_info_array){
            return array(
                'field_token' => $homebase_field,
                'field_name' => ucwords(str_replace('-', ' ', $homebase_field))
                );
        } else {
            return $homebase_field;
        }
    }

    // Define a function for getting a list of starter robots for players
    public static function get_starter_robots(){
        return array(
            'default' => 'robot',
            'dr-light' => 'mega-man',
            'dr-wily' => 'bass',
            'dr-cossack' => 'proto-man'
            );
    }

    // Define a function for calculating the starter robot for a given player
    public static function get_starter_robot($player_token = '', $return_info_array = false){
        $starter_robot_index = self::get_starter_robots();
        if (isset($starter_robot_index[$player_token])){ $starter_robot = $starter_robot_index[$player_token]; }
        else { $starter_robot = $starter_robot_index['default']; }
        if ($return_info_array){
            return array(
                'robot_token' => $starter_robot,
                'robot_name' => ucwords(str_replace('-', ' ', $starter_robot))
                );
        } else {
            return $starter_robot;
        }
    }

    // Define a function for getting a list of support robots for players
    public static function get_support_robots(){
        return array(
            'default' => 'robot',
            'dr-light' => 'roll',
            'dr-wily' => 'disco',
            'dr-cossack' => 'rhythm'
            );
    }

    // Define a function for calculating the support robot for a given player
    public static function get_support_robot($player_token = '', $return_info_array = false){
        $support_robot_index = self::get_support_robots();
        if (isset($support_robot_index[$player_token])){ $support_robot = $support_robot_index[$player_token]; }
        else { $support_robot = $support_robot_index['default']; }
        if ($return_info_array){
            return array(
                'robot_token' => $support_robot,
                'robot_name' => ucwords(str_replace('-', ' ', $support_robot))
                );
        } else {
            return $support_robot;
        }
    }

    // Define a function for getting a list of support robots for players
    public static function get_support_mechas(){
        return array(
            'default' => 'met',
            'dr-light' => 'sniper-joe',
            'dr-wily' => 'skeleton-joe',
            'dr-cossack' => 'crystal-joe'
            );
    }

    // Define a function for calculating the support robot for a given player
    public static function get_support_mecha($player_token = '', $return_info_array = false){
        $support_mecha_index = self::get_support_mechas();
        if (isset($support_mecha_index[$player_token])){ $support_mecha = $support_mecha_index[$player_token]; }
        else { $support_mecha = $support_mecha_index['default']; }
        if ($return_info_array){
            return array(
                'robot_token' => $support_mecha,
                'robot_name' => ucwords(str_replace('-', ' ', $support_mecha))
                );
        } else {
            return $support_mecha;
        }
    }

    // Define a function for getting a list of positive and negative relationships among main characters
    public static function get_character_relationships(){
        return array(
            'dr-light' => array(
                'rival' => 'dr-wily',
                'positive' => array('dr-cossack', 'mega-man', 'roll', 'proto-man', 'rhythm', 'auto'),
                'negative' => array('dr-wily', 'bass'),
                ),
            'dr-wily' => array(
                'rival' => 'dr-cossack',
                'positive' => array('bass', 'disco', 'proto-man'),
                'negative' => array('dr-light', 'dr-cossack', 'mega-man'),
                ),
            'dr-cossack' => array(
                'rival' => 'dr-light',
                'positive' => array('dr-light', 'mega-man', 'rhythm', 'kalinka'),
                'negative' => array('dr-wily'),
                ),
            'mega-man' => array(
                'rival' => 'bass',
                'positive' => array('dr-light', 'roll', 'proto-man', 'auto'),
                'negative' => array('dr-wily', 'bass', 'disco'),
                ),
            'bass' => array(
                'rival' => 'mega-man',
                'positive' => array('splash-woman', 'reggae'),
                'negative' => array('dr-wily', 'dr-light', 'dr-cossack', 'mega-man', 'proto-man', 'roll', 'disco'),
                ),
            'proto-man' => array(
                'rival' => 'mega-man',
                'positive' => array('mega-man', 'roll', 'rhythm'),
                'negative' => array('dr-wily'),
                ),
            'roll' => array(
                'rival' => 'auto',
                'positive' => array('dr-light', 'mega-man', 'proto-man', 'auto'),
                'negative' => array('dr-wily', 'bass', 'disco', 'reggae'),
                ),
            'disco' => array(
                'rival' => 'roll',
                'positive' => array('dr-wily', 'proto-man'),
                'negative' => array('dr-light', 'dr-cossack', 'roll', 'rhythm'),
                ),
            'rhythm' => array(
                'rival' => 'bright-man',
                'positive' => array('dr-cossack', 'dr-light', 'mega-man', 'proto-man', 'roll', 'kalinka'),
                'negative' => array('disco'),
                ),
            );
    }

}
?>