<?
/**
 * Mega Man RPG Robot Object
 * <p>The base class for all robot objects in the Mega Man RPG Prototype.</p>
 */
class rpg_robot extends rpg_object {

    // Define the constructor class
    public function rpg_robot(){

        // Update the session keys for this object
        $this->session_key = 'ROBOTS';
        $this->session_token = 'robot_token';
        $this->session_id = 'robot_id';
        $this->class = 'robot';
        $this->multi = 'robots';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal battle pointer
        $this->battle = isset($args[0]) ? $args[0] : $GLOBALS['this_battle'];
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Define the internal battle pointer
        $this->field = isset($this->battle->field) ? $this->battle->field : $GLOBALS['this_field'];
        $this->field_id = $this->battle->battle_id;
        $this->field_token = $this->battle->battle_token;

        // Define the internal player values using the provided array
        $this->player = isset($args[1]) ? $args[1] : $GLOBALS['this_player'];
        $this->player_id = $this->player->player_id;
        $this->player_token = $this->player->player_token;

        // Collect current robot data from the function if available
        $this_robotinfo = isset($args[2]) && !empty($args[2]) ? $args[2] : array('robot_id' => 0, 'robot_token' => 'robot');

        // Now load the robot data from the session or index
        if (!$this->robot_load($this_robotinfo)){
            // Robot data could not be loaded
            die('Robot data could not be loaded :<br />$this_robotinfo = <pre>'.print_r($this_robotinfo, true).'</pre>');
        }

        // Return true on success
        return true;

    }

    // Define a function for getting the session info
    public static function get_session_field($robot_id, $field_token){
        if (empty($robot_id) || empty($field_token)){ return false; }
        elseif (!empty($_SESSION['ROBOTS'][$robot_id][$field_token])){ return $_SESSION['ROBOTS'][$robot_id][$field_token]; }
        else { return false; }
    }

    // Define a function for setting the session info
    public static function set_session_field($robot_id, $field_token, $field_value){
        if (empty($robot_id) || empty($field_token)){ return false; }
        else { $_SESSION['ROBOTS'][$robot_id][$field_token] = $field_value; }
        return true;
    }

    // Define a public function for manually loading data
    public function robot_load($this_robotinfo){

        // If the robot info was not an array, return false
        if (!is_array($this_robotinfo)){ die("robot info must be an array!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }
        // If the robot ID was not provided, return false
        if (!isset($this_robotinfo['robot_id'])){ die("robot id must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }
        // If the robot token was not provided, return false
        if (!isset($this_robotinfo['robot_token'])){ die("robot token must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }

        // Collect current robot data from the session if available
        $this_robotinfo_backup = $this_robotinfo;
        if (isset($_SESSION['ROBOTS'][$this_robotinfo['robot_id']])){
            $this_robotinfo = $_SESSION['ROBOTS'][$this_robotinfo['robot_id']];
        }
        // Otherwise, collect robot data from the index
        else {
            if (empty($this_robotinfo_backup['_parsed'])){
                if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "\$this_robotinfo = rpg_robot::get_index_info({$this_robotinfo['robot_token']}); on line ".__LINE__;  }
                $this_robotinfo = rpg_robot::get_index_info($this_robotinfo['robot_token']);
                $this_robotinfo = array_replace($this_robotinfo, $this_robotinfo_backup);
            }
        }

        // DEBUG
        /*
        if (false && $this_robotinfo['robot_token'] == 'mega-man'){
            die(__LINE__.
                ':: <pre>$this_robotinfo_backup:'.print_r($this_robotinfo_backup, true).'</pre>'.
                ':: <pre>$this_robotinfo:'.print_r($this_robotinfo, true).'</pre>');
        }
        */


        // Define the internal robot values using the provided array
        $this->flags = isset($this_robotinfo['flags']) ? $this_robotinfo['flags'] : array();
        $this->counters = isset($this_robotinfo['counters']) ? $this_robotinfo['counters'] : array();
        $this->values = isset($this_robotinfo['values']) ? $this_robotinfo['values'] : array();
        $this->history = isset($this_robotinfo['history']) ? $this_robotinfo['history'] : array();
        $this->robot_key = isset($this_robotinfo['robot_key']) ? $this_robotinfo['robot_key'] : 0;
        $this->robot_id = isset($this_robotinfo['robot_id']) ? $this_robotinfo['robot_id'] : false;
        $this->robot_number = isset($this_robotinfo['robot_number']) ? $this_robotinfo['robot_number'] : 'RPG000';
        $this->robot_name = isset($this_robotinfo['robot_name']) ? $this_robotinfo['robot_name'] : 'Robot';
        $this->robot_token = isset($this_robotinfo['robot_token']) ? $this_robotinfo['robot_token'] : 'robot';
        $this->robot_field = isset($this_robotinfo['robot_field']) ? $this_robotinfo['robot_field'] : 'field';
        $this->robot_field2 = isset($this_robotinfo['robot_field2']) ? $this_robotinfo['robot_field2'] : 'field';
        $this->robot_class = isset($this_robotinfo['robot_class']) ? $this_robotinfo['robot_class'] : 'master';
        $this->robot_gender = isset($this_robotinfo['robot_gender']) ? $this_robotinfo['robot_gender'] : 'none';
        $this->robot_image = isset($this_robotinfo['robot_image']) ? $this_robotinfo['robot_image'] : $this->robot_token;
        $this->robot_image_size = isset($this_robotinfo['robot_image_size']) ? $this_robotinfo['robot_image_size'] : 40;
        $this->robot_image_overlay = isset($this_robotinfo['robot_image_overlay']) ? $this_robotinfo['robot_image_overlay'] : array();
        $this->robot_image_alts = isset($this_robotinfo['robot_image_alts']) ? $this_robotinfo['robot_image_alts'] : array();
        $this->robot_core = isset($this_robotinfo['robot_core']) ? $this_robotinfo['robot_core'] : false;
        $this->robot_core2 = isset($this_robotinfo['robot_core2']) ? $this_robotinfo['robot_core2'] : false;
        $this->robot_description = isset($this_robotinfo['robot_description']) ? $this_robotinfo['robot_description'] : '';
        $this->robot_experience = isset($this_robotinfo['robot_experience']) ? $this_robotinfo['robot_experience'] : (isset($this_robotinfo['robot_points']) ? $this_robotinfo['robot_points'] : 0);
        $this->robot_level = isset($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : (!empty($this->robot_experience) ? $this->robot_experience / 1000 : 0) + 1;
        $this->robot_energy = isset($this_robotinfo['robot_energy']) ? $this_robotinfo['robot_energy'] : 1;
        $this->robot_weapons = isset($this_robotinfo['robot_weapons']) ? $this_robotinfo['robot_weapons'] : 10;
        $this->robot_attack = isset($this_robotinfo['robot_attack']) ? $this_robotinfo['robot_attack'] : 1;
        $this->robot_defense = isset($this_robotinfo['robot_defense']) ? $this_robotinfo['robot_defense'] : 1;
        $this->robot_speed = isset($this_robotinfo['robot_speed']) ? $this_robotinfo['robot_speed'] : 1;
        $this->robot_weaknesses = isset($this_robotinfo['robot_weaknesses']) ? $this_robotinfo['robot_weaknesses'] : array();
        $this->robot_resistances = isset($this_robotinfo['robot_resistances']) ? $this_robotinfo['robot_resistances'] : array();
        $this->robot_affinities = isset($this_robotinfo['robot_affinities']) ? $this_robotinfo['robot_affinities'] : array();
        $this->robot_immunities = isset($this_robotinfo['robot_immunities']) ? $this_robotinfo['robot_immunities'] : array();
        $this->robot_item = isset($this_robotinfo['robot_item']) ? $this_robotinfo['robot_item'] : '';
        $this->robot_abilities = isset($this_robotinfo['robot_abilities']) ? $this_robotinfo['robot_abilities'] : array();
        $this->robot_attachments = isset($this_robotinfo['robot_attachments']) ? $this_robotinfo['robot_attachments'] : array();
        $this->robot_quotes = isset($this_robotinfo['robot_quotes']) ? $this_robotinfo['robot_quotes'] : array();
        $this->robot_status = isset($this_robotinfo['robot_status']) ? $this_robotinfo['robot_status'] : 'active';
        $this->robot_position = isset($this_robotinfo['robot_position']) ? $this_robotinfo['robot_position'] : 'bench';
        $this->robot_stance = isset($this_robotinfo['robot_stance']) ? $this_robotinfo['robot_stance'] : 'base';
        $this->robot_rewards = isset($this_robotinfo['robot_rewards']) ? $this_robotinfo['robot_rewards'] : array();
        $this->robot_frame = isset($this_robotinfo['robot_frame']) ? $this_robotinfo['robot_frame'] : 'base';
        $this->robot_frame_offset = !empty($this_robotinfo['robot_frame_offset']) ? $this_robotinfo['robot_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->robot_frame_classes = isset($this_robotinfo['robot_frame_classes']) ? $this_robotinfo['robot_frame_classes'] : '';
        $this->robot_frame_styles = isset($this_robotinfo['robot_frame_styles']) ? $this_robotinfo['robot_frame_styles'] : '';
        $this->robot_detail_styles = isset($this_robotinfo['robot_detail_styles']) ? $this_robotinfo['robot_detail_styles'] : '';
        $this->robot_original_player = isset($this_robotinfo['robot_original_player']) ? $this_robotinfo['robot_original_player'] : $this->player_token;
        $this->robot_string = isset($this_robotinfo['robot_string']) ? $this_robotinfo['robot_string'] : $this->robot_id.'_'.$this->robot_token;

        // Define the internal robot base values using the robots index array
        $this->robot_base_name = isset($this_robotinfo['robot_base_name']) ? $this_robotinfo['robot_base_name'] : $this->robot_name;
        $this->robot_base_token = isset($this_robotinfo['robot_base_token']) ? $this_robotinfo['robot_base_token'] : $this->robot_token;

        $this->robot_base_image = isset($this_robotinfo['robot_base_image']) ? $this_robotinfo['robot_base_image'] : $this->robot_base_token;
        $this->robot_base_image_size = isset($this_robotinfo['robot_base_image_size']) ? $this_robotinfo['robot_base_image_size'] : $this->robot_image_size;
        $this->robot_base_image_overlay = isset($this_robotinfo['robot_base_image_overlay']) ? $this_robotinfo['robot_base_image_overlay'] : $this->robot_image_overlay;

        $this->robot_base_core = isset($this_robotinfo['robot_base_core']) ? $this_robotinfo['robot_base_core'] : $this->robot_core;
        $this->robot_base_core2 = isset($this_robotinfo['robot_base_core2']) ? $this_robotinfo['robot_base_core2'] : $this->robot_core2;

        $this->robot_base_description = isset($this_robotinfo['robot_base_description']) ? $this_robotinfo['robot_base_description'] : $this->robot_description;

        $this->robot_base_experience = isset($this_robotinfo['robot_base_experience']) ? $this_robotinfo['robot_base_experience'] : $this->robot_experience;
        $this->robot_base_level = isset($this_robotinfo['robot_base_level']) ? $this_robotinfo['robot_base_level'] : $this->robot_level;

        $this->robot_base_energy = isset($this_robotinfo['robot_base_energy']) ? $this_robotinfo['robot_base_energy'] : $this->robot_energy;
        $this->robot_base_weapons = isset($this_robotinfo['robot_base_weapons']) ? $this_robotinfo['robot_base_weapons'] : $this->robot_weapons;
        $this->robot_base_attack = isset($this_robotinfo['robot_base_attack']) ? $this_robotinfo['robot_base_attack'] : $this->robot_attack;
        $this->robot_base_defense = isset($this_robotinfo['robot_base_defense']) ? $this_robotinfo['robot_base_defense'] : $this->robot_defense;
        $this->robot_base_speed = isset($this_robotinfo['robot_base_speed']) ? $this_robotinfo['robot_base_speed'] : $this->robot_speed;

        $this->robot_max_energy = isset($this_robotinfo['robot_max_energy']) ? $this_robotinfo['robot_max_energy'] : $this->robot_base_energy;
        $this->robot_max_weapons = isset($this_robotinfo['robot_max_weapons']) ? $this_robotinfo['robot_max_weapons'] : $this->robot_base_weapons;
        $this->robot_max_attack = isset($this_robotinfo['robot_max_attack']) ? $this_robotinfo['robot_max_attack'] : $this->robot_base_attack;
        $this->robot_max_defense = isset($this_robotinfo['robot_max_defense']) ? $this_robotinfo['robot_max_defense'] : $this->robot_base_defense;
        $this->robot_max_speed = isset($this_robotinfo['robot_max_speed']) ? $this_robotinfo['robot_max_speed'] : $this->robot_base_speed;

        $this->robot_base_weaknesses = isset($this_robotinfo['robot_base_weaknesses']) ? $this_robotinfo['robot_base_weaknesses'] : $this->robot_weaknesses;
        $this->robot_base_resistances = isset($this_robotinfo['robot_base_resistances']) ? $this_robotinfo['robot_base_resistances'] : $this->robot_resistances;
        $this->robot_base_affinities = isset($this_robotinfo['robot_base_affinities']) ? $this_robotinfo['robot_base_affinities'] : $this->robot_affinities;
        $this->robot_base_immunities = isset($this_robotinfo['robot_base_immunities']) ? $this_robotinfo['robot_base_immunities'] : $this->robot_immunities;

        //$this->robot_base_abilities = isset($this_robotinfo['robot_base_abilities']) ? $this_robotinfo['robot_base_abilities'] : $this->robot_abilities;
        $this->robot_base_attachments = isset($this_robotinfo['robot_base_attachments']) ? $this_robotinfo['robot_base_attachments'] : $this->robot_attachments;

        $this->robot_base_quotes = isset($this_robotinfo['robot_base_quotes']) ? $this_robotinfo['robot_base_quotes'] : $this->robot_quotes;

        // Limit all stats to 9999 for display purposes (and balance I guess)
        if ($this->robot_energy > MMRPG_SETTINGS_STATS_MAX){ $this->robot_energy = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_energy > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_energy = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_weapons > MMRPG_SETTINGS_STATS_MAX){ $this->robot_weapons = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_weapons > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_weapons = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_attack > MMRPG_SETTINGS_STATS_MAX){ $this->robot_attack = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_attack > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_attack = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_defense > MMRPG_SETTINGS_STATS_MAX){ $this->robot_defense = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_defense > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_defense = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_speed > MMRPG_SETTINGS_STATS_MAX){ $this->robot_speed = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_speed > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_speed = MMRPG_SETTINGS_STATS_MAX; }

        // Collect any functions associated with this robot
        $this->robot_functions = isset($this_robotinfo['robot_functions']) ? $this_robotinfo['robot_functions'] : 'robots/robot.php';
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->robot_functions) ? $this->robot_functions : 'robots/robot.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->robot_function = isset($robot['robot_function']) ? $robot['robot_function'] : function(){};
        $this->robot_function_onload = isset($robot['robot_function_onload']) ? $robot['robot_function_onload'] : function(){};
        $this->robot_function_ondamage = isset($robot['robot_function_ondamage']) ? $robot['robot_function_ondamage'] : function(){};
        $this->robot_function_onrecovery = isset($robot['robot_function_onrecovery']) ? $robot['robot_function_onrecovery'] : function(){};
        $this->robot_function_ondisabled = isset($robot['robot_function_ondisabled']) ? $robot['robot_function_ondisabled'] : function(){};
        unset($robot);

        // If this is a player-controlled robot, load settings from session
        if ($this->player->player_side == 'left' && empty($this->flags['apply_session_settings'])){

            // Collect the abilities for this robot from the session
            $temp_robot_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);

            // If this is a player-controlled robot, load abilities from session
            if (!empty($temp_robot_settings['robot_abilities'])){
                $temp_robot_abilities = $temp_robot_settings['robot_abilities'];
                $this->robot_abilities = array();
                foreach ($temp_robot_abilities AS $token => $info){ $this->robot_abilities[] = $token; }
            }

            // If there is an alternate image set, apply it
            if (!empty($temp_robot_settings['robot_image'])){
                $this->robot_image = $temp_robot_settings['robot_image'];
                $this->robot_base_image = $this->robot_image;
            }

            // If there is a held item set, apply it
            if (!empty($temp_robot_settings['robot_item'])){
                $this->robot_item = $temp_robot_settings['robot_item'];
                $this->robot_base_item = $this->robot_item;
            }

            // Set the session settings flag to true
            $this->flags['apply_session_settings'] = true;

        }

        // If this is an AI-controlled robot, check to see if unlockable
        if ($this->player->player_side == 'right' && empty($this->flags['check_robot_unlockable'])){

            // Calculate whether or not this robot is currently unlockable
            $is_unlockable = false;
            if ($this->player->player_side == 'right'){
                if (!empty($this->battle->battle_rewards['robots'])){
                    foreach ($this->battle->battle_rewards['robots'] AS $reward){
                        if ($this->robot_token == $reward['token']){
                            $is_unlockable = true;
                            if (mmrpg_prototype_robot_unlocked(false, $reward['token'])){
                                $is_unlockable = false;
                                break;
                            }
                            break;
                        }
                    }
                }
            }

            // Update this robot's flag's to reflect unlockability
            $this->flags['robot_is_unlockable'] = $is_unlockable;

            // Set the session settings flag to true
            $this->flags['check_robot_unlockable'] = true;

        }

        // Remove any abilities that do not exist in the index
        if (!empty($this->robot_abilities)){
            foreach ($this->robot_abilities AS $key => $token){
                if ($token == 'ability' || empty($token)){ unset($this->robot_abilities[$key]); }
            }
            $this->robot_abilities = array_values($this->robot_abilities);
        }

        // If this robot is already disabled, make sure their status reflects it
        if (!empty($this->flags['hidden'])){
            $this->flags['apply_disabled_state'] = true;
            $this->robot_status = 'disabled';
            $this->robot_energy = 0;
        }

        // Trigger the onload function if it exists
        $this->trigger_onload();

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a function for refreshing this robot and running onload actions
    public function trigger_onload(){

        // Trigger the onload function if it exists
        $temp_function = $this->robot_function_onload;
        $temp_result = $temp_function(array(
            'this_field' => isset($this->battle->battle_field) ? $this->battle->battle_field : false,
            'this_battle' => $this->battle,
            'this_player' => $this->player,
            'this_robot' => $this
            ));

    }


    // Define alias functions for updating specific fields quickly

    public function get_id(){ return intval($this->get_info('robot_id')); }
    public function set_id($value){ $this->set_info('robot_id', intval($value)); }

    public function get_key(){ return intval($this->get_info('robot_key')); }
    public function set_key($value){ $this->set_info('robot_key', intval($value)); }
    public function get_base_key(){ return intval($this->get_info('robot_base_key')); }
    public function set_base_key($value){ $this->set_info('robot_base_key', intval($value)); }

    public function get_name(){ return $this->get_info('robot_name'); }
    public function set_name($value){ $this->set_info('robot_name', $value); }
    public function get_base_name(){ return $this->get_info('robot_base_name'); }
    public function set_base_name($value){ $this->set_info('robot_base_name', $value); }
    public function reset_name(){ $this->set_info('robot_name', $this->get_info('robot_base_name')); }

    public function get_token(){ return $this->get_info('robot_token'); }
    public function set_token($value){ $this->set_info('robot_token', $value); }

    public function get_description(){ return $this->get_info('robot_description'); }
    public function set_description($value){ $this->set_info('robot_description', $value); }
    public function get_base_description(){ return $this->get_info('robot_base_description'); }
    public function set_base_description($value){ $this->set_info('robot_base_description', $value); }

    public function get_number(){ return $this->get_info('robot_number'); }
    public function set_number($value){ $this->set_info('robot_number', $value); }
    public function get_base_number(){ return $this->get_info('robot_base_number'); }
    public function set_base_number($value){ $this->set_info('robot_base_number', $value); }

    public function get_field(){ return $this->get_info('robot_field'); }
    public function set_field($value){ $this->set_info('robot_field', $value); }
    public function get_base_field(){ return $this->get_info('robot_base_field'); }
    public function set_base_field($value){ $this->set_info('robot_base_field', $value); }

    public function get_class(){ return $this->get_info('robot_class'); }
    public function set_class($value){ $this->set_info('robot_class', $value); }
    public function is_class($class){ return $this->get_class() == $class ? true : false; }
    public function get_base_class(){ return $this->get_info('robot_base_class'); }
    public function set_base_class($value){ $this->set_info('robot_base_class', $value); }
    public function is_base_class($class){ return $this->get_base_class() == $class ? true : false; }

    public function get_gender(){ return $this->get_info('robot_gender'); }
    public function set_gender($value){ $this->set_info('robot_gender', $value); }

    public function get_core(){ return $this->get_info('robot_core'); }
    public function set_core($value){ $this->set_info('robot_core', $value); }
    public function get_base_core(){ return $this->get_info('robot_base_core'); }
    public function set_base_core($value){ $this->set_info('robot_base_core', $value); }

    public function get_core2(){ return $this->get_info('robot_core2'); }
    public function set_core2($value){ $this->set_info('robot_core2', $value); }
    public function get_base_core2(){ return $this->get_info('robot_base_core2'); }
    public function set_base_core2($value){ $this->set_info('robot_base_core2', $value); }

    public function get_experience(){ return $this->get_info('robot_experience'); }
    public function set_experience($value){ $this->set_info('robot_experience', $value); }
    public function get_base_experience(){ return $this->get_info('robot_base_experience'); }
    public function set_base_experience($value){ $this->set_info('robot_base_experience', $value); }

    public function get_level(){ return $this->get_info('robot_level'); }
    public function set_level($value){ $this->set_info('robot_level', $value); }
    public function get_base_level(){ return $this->get_info('robot_base_level'); }
    public function set_base_level($value){ $this->set_info('robot_base_level', $value); }

    public function get_energy(){
        return $this->get_info('robot_energy');
    }
    public function set_energy($value){
        $energy = $value;
        $max_energy = $this->get_base_energy();
        $min_energy = 0;
        if ($energy > $max_energy){ $energy = $max_energy; }
        elseif ($energy < $min_energy){ $energy = $min_energy; }
        $this->set_info('robot_energy', $energy);
    }
    public function get_base_energy(){
        return $this->get_info('robot_base_energy');
    }
    public function set_base_energy($value){
        $energy = $value;
        if ($energy < 0){ $energy = 0; }
        $this->set_info('robot_base_energy', $energy);
    }
    public function reset_energy(){
        $this->set_info('robot_energy', $this->get_info('robot_base_energy'));
    }

    public function get_weapons(){
        return $this->get_info('robot_weapons');
    }
    public function set_weapons($value){
        $weapons = $value;
        $max_weapons = $this->get_base_weapons();
        $min_weapons = 0;
        if ($weapons > $max_weapons){ $weapons = $max_weapons; }
        elseif ($weapons < $min_weapons){ $weapons = $min_weapons; }
        $this->set_info('robot_weapons', $weapons);
    }
    public function get_base_weapons(){
        return $this->get_info('robot_base_weapons');
    }
    public function set_base_weapons($value){
        $weapons = $value;
        if ($weapons < 0){ $weapons = 0; }
        $this->set_info('robot_base_weapons', $weapons);
    }
    public function reset_weapons($value){
        $this->set_info('robot_weapons', $this->get_info('robot_base_weapons'));
    }

    public function get_attack(){ return $this->get_info('robot_attack'); }
    public function set_attack($value){ $this->set_info('robot_attack', $value); }
    public function get_base_attack(){ return $this->get_info('robot_base_attack'); }
    public function set_base_attack($value){ $this->set_info('robot_base_attack', $value); }

    public function get_defense(){ return $this->get_info('robot_defense'); }
    public function set_defense($value){ $this->set_info('robot_defense', $value); }
    public function get_base_defense(){ return $this->get_info('robot_base_defense'); }
    public function set_base_defense($value){ $this->set_info('robot_base_defense', $value); }

    public function get_speed(){ return $this->get_info('robot_speed'); }
    public function set_speed($value){ $this->set_info('robot_speed', $value); }
    public function get_base_speed(){ return $this->get_info('robot_base_speed'); }
    public function set_base_speed($value){ $this->set_info('robot_base_speed', $value); }

    public function get_total(){ return $this->get_info('robot_total'); }
    public function set_total($value){ $this->set_info('robot_total', $value); }
    public function get_base_total(){ return $this->get_info('robot_base_total'); }
    public function set_base_total($value){ $this->set_info('robot_base_total', $value); }

    public function get_stat($stat){ return $this->get_info('robot_'.$stat); }
    public function set_stat($stat, $value){ $this->set_info('robot_'.$stat, $value); }
    public function get_stats(){
        $stats = array();
        $stats['robot_energy'] = $this->get_energy();
        $stats['robot_weapons'] = $this->get_weapons();
        $stats['robot_attack'] = $this->get_attack();
        $stats['robot_defense'] = $this->get_defense();
        $stats['robot_speed'] = $this->get_speed();
        return $stats;
    }
    public function get_base_stat($stat){ return $this->get_info('robot_base_'.$stat); }
    public function set_base_stat($stat, $value){ $this->set_info('robot_base_'.$stat, $value); }
    public function get_base_stats(){
        $stats = array();
        $stats['robot_base_energy'] = $this->get_base_energy();
        $stats['robot_base_weapons'] = $this->get_base_weapons();
        $stats['robot_base_attack'] = $this->get_base_attack();
        $stats['robot_base_defense'] = $this->get_base_defense();
        $stats['robot_base_speed'] = $this->get_base_speed();
        return $stats;
    }

    public function get_weaknesses(){ return $this->get_info('robot_weaknesses'); }
    public function set_weaknesses($value){ $this->set_info('robot_weaknesses', $value); }
    public function get_base_weaknesses(){ return $this->get_info('robot_base_weaknesses'); }
    public function set_base_weaknesses($value){ $this->set_info('robot_base_weaknesses', $value); }

    public function get_resistances(){ return $this->get_info('robot_resistances'); }
    public function set_resistances($value){ $this->set_info('robot_resistances', $value); }
    public function get_base_resistances(){ return $this->get_info('robot_base_resistances'); }
    public function set_base_resistances($value){ $this->set_info('robot_base_resistances', $value); }

    public function get_affinities(){ return $this->get_info('robot_affinities'); }
    public function set_affinities($value){ $this->set_info('robot_affinities', $value); }
    public function get_base_affinities(){ return $this->get_info('robot_base_affinities'); }
    public function set_base_affinities($value){ $this->set_info('robot_base_affinities', $value); }

    public function get_immunities(){ return $this->get_info('robot_immunities'); }
    public function set_immunities($value){ $this->set_info('robot_immunities', $value); }
    public function get_base_immunities(){ return $this->get_info('robot_base_immunities'); }
    public function set_base_immunities($value){ $this->set_info('robot_base_immunities', $value); }

    public function get_item(){ return $this->get_info('robot_item'); }
    public function set_item($value){ $this->set_info('robot_item', $value); }
    public function unset_item(){ $this->set_info('robot_item', ''); }

    /**
     * Check if this robot is holding an item, optionally checking for a specific one
     * @param string $item_token (optional)
     * @return bool
     */
    public function has_item(){
        $args = func_get_args();
        $item = $this->get_info('robot_item');
        if (!empty($args[0])){ return $item == $args[0] ? true : false; }
        else { return !empty($item) ? true : false; }
    }

    public function get_base_item(){ return $this->get_info('robot_base_item'); }
    public function set_base_item($value){ $this->set_info('robot_base_item', $value); }
    public function unset_base_item(){ $this->set_info('robot_base_item', ''); }

    /**
     * Check if this robot is holding a base item, optionally checking for a specific one
     * @param string $item_token (optional)
     * @return bool
     */
    public function has_base_item(){
        $args = func_get_args();
        $item = $this->get_info('robot_base_item');
        if (!empty($args[0])){ return $item == $args[0] ? true : false; }
        else { return !empty($item) ? true : false; }
    }

    public function reset_item(){ $this->set_info('robot_item', $this->get_info('robot_base_item')); }

    public function get_abilities(){ return $this->get_info('robot_abilities'); }
    public function set_abilities($value){ $this->set_info('robot_abilities', $value); }
    public function has_abilities(){ return $this->get_info('robot_abilities') ? true : false; }
    public function has_ability($token){ return in_array($token, $this->get_info('robot_abilities')) ? true : false; }
    public function get_base_abilities(){ return $this->get_info('robot_base_abilities'); }
    public function set_base_abilities($value){ $this->set_info('robot_base_abilities', $value); }
    public function has_base_abilities(){ return $this->get_info('robot_base_abilities') ? true : false; }
    public function has_base_ability($token){ return in_array($token, $this->get_info('robot_base_abilities')) ? true : false; }

    public function get_attachment($token){ return $this->get_info('robot_attachments', $token); }
    public function set_attachment($token, $value){ $this->set_info('robot_attachments', $token, $value); }
    public function unset_attachment($token){ return $this->unset_info('robot_attachments', $token); }

    public function get_attachments(){ return $this->get_info('robot_attachments'); }
    public function set_attachments($value){ $this->set_info('robot_attachments', $value); }
    public function has_attachments(){ return $this->get_info('robot_attachments') ? true : false; }
    public function has_attachment($token){ return $this->get_info('robot_attachments', $token) ? true : false; }
    public function get_base_attachments(){ return $this->get_info('robot_base_attachments'); }
    public function set_base_attachments($value){ $this->set_info('robot_base_attachments', $value); }
    public function has_base_attachments(){ return $this->get_info('robot_base_attachments') ? true : false; }
    public function has_base_attachment($token){ return in_array($token, $this->get_info('robot_base_attachments')) ? true : false; }

    public function get_quotes(){ return $this->get_info('robot_quotes'); }
    public function set_quotes($value){ $this->set_info('robot_quotes', $value); }
    public function get_base_quotes(){ return $this->get_info('robot_base_quotes'); }
    public function set_base_quotes($value){ $this->set_info('robot_base_quotes', $value); }

    public function get_quote($token){ return $this->get_info('robot_quotes', $token); }
    public function set_quote($token, $value){ $this->set_info('robot_quotes', $token, $value); }
    public function unset_quote($token){ $this->unset_info('robot_quotes', $token); }
    public function has_quote($token){
        $quote = $this->get_info('robot_quotes', $token);
        return !empty($quote) ? true : false;
    }
    public function get_base_quote($token){ return $this->get_info('robot_base_quotes', $token); }
    public function set_base_quote($token, $value){ $this->set_info('robot_base_quotes', $token, $value); }
    public function unset_base_quote($token){ $this->unset_info('robot_base_quotes', $token); }
    public function has_base_quote($token){
        $quote = $this->get_info('robot_base_quotes', $token);
        return !empty($quote) ? true : false;
    }

    public function get_status(){ return $this->get_info('robot_status'); }
    public function set_status($value){ $this->set_info('robot_status', $value); }

    public function get_side(){ return $this->get_info('robot_side'); }
    public function set_side($value){ $this->set_info('robot_side', $value); }

    public function get_direction(){ return $this->get_info('robot_direction'); }
    public function set_direction($value){ $this->set_info('robot_direction', $value); }

    public function get_position(){ return $this->get_info('robot_position'); }
    public function set_position($value){ $this->set_info('robot_position', $value); }

    public function get_stance(){ return $this->get_info('robot_stance'); }
    public function set_stance($value){ $this->set_info('robot_stance', $value); }

    public function get_rewards(){ return $this->get_info('robot_rewards'); }
    public function set_rewards($value){ $this->set_info('robot_rewards', $value); }
    public function get_base_rewards(){ return $this->get_info('robot_base_rewards'); }
    public function set_base_rewards($value){ $this->set_info('robot_base_rewards', $value); }

    public function get_functions(){ return $this->get_info('robot_functions'); }
    public function set_functions($value){ $this->set_info('robot_functions', $value); }

    public function get_image(){ return $this->get_info('robot_image'); }
    public function set_image($value){ $this->set_info('robot_image', $value); }
    public function get_base_image(){ return $this->get_info('robot_base_image'); }
    public function set_base_image($value){ $this->set_info('robot_base_image', $value); }
    public function reset_image(){ $this->set_info('robot_image', $this->get_info('robot_base_image')); }

    public function get_image_size(){ return $this->get_info('robot_image_size'); }
    public function set_image_size($value){ $this->set_info('robot_image_size', $value); }
    public function get_base_image_size(){ return $this->get_info('robot_base_image_size'); }
    public function set_base_image_size($value){ $this->set_info('robot_base_image_size', $value); }
    public function reset_base_image_size(){ $this->set_info('robot_image_size', $this->get_info('robot_base_image_size')); }

    public function get_image_overlay(){
        return $this->get_info('robot_image_overlay');
    }
    public function set_image_overlay($value){
        $args = func_get_args();
        if (count($args) == 2){ $this->set_info('robot_image_overlay', $args[0], $args[1]); }
        else { $this->set_info('robot_image_overlay', $value); }
    }
    public function unset_image_overlay($token){
        $this->unset_info('robot_image_overlay', $token);
    }
    public function get_base_image_overlay(){
        return $this->get_info('robot_base_image_overlay');
    }
    public function set_base_image_overlay($value){
        $args = func_get_args();
        if (count($args) == 2){ $this->set_info('robot_base_image_overlay', $args[0], $args[1]); }
        else { $this->set_info('robot_base_image_overlay', $value); }
    }
    public function unset_base_image_overlay($token){
        $this->unset_info('robot_base_image_overlay', $token);
    }

    public function get_image_alts(){ return $this->get_info('robot_image_alts'); }
    public function set_image_alts($value){ $this->set_info('robot_image_alts', $value); }

    public function get_frame(){ return $this->get_info('robot_frame'); }
    public function set_frame($value){ $this->set_info('robot_frame', $value); }

    public function get_frame_offset(){
        $args = func_get_args();
        if (isset($args[0])){ return $this->get_info('robot_frame_offset', $args[0]); }
        else { return $this->get_info('robot_frame_offset'); }
    }
    public function set_frame_offset($value){
        $args = func_get_args();
        if (isset($args[1])){ $this->set_info('robot_frame_offset', $args[0], $args[1]); }
        else { $this->set_info('robot_frame_offset', $value); }
    }

    public function get_frame_classes(){ return $this->get_info('robot_frame_classes'); }
    public function set_frame_classes($value){ $this->set_info('robot_frame_classes', $value); }

    public function get_frame_styles(){ return $this->get_info('robot_frame_styles'); }
    public function set_frame_styles($value){ $this->set_info('robot_frame_styles', $value); }

    public function get_detail_styles(){ return $this->get_info('robot_detail_styles'); }
    public function set_detail_styles($value){ $this->set_info('robot_detail_styles', $value); }

    public function get_original_player(){ return $this->get_info('robot_original_player'); }
    public function set_original_player($value){ $this->set_info('robot_original_player', $value); }

    public function get_string(){ return $this->get_info('robot_string'); }
    public function set_string($value){ $this->set_info('robot_string', $value); }

    public function get_lookup(){
        $lookup = array();
        $lookup['robot_id'] = $this->get_id();
        $lookup['robot_token'] = $this->get_token();
        return $lookup;
    }

    // Define a function for generating a static attachment key for this position
    public function get_static_attachment_key(){
        $position = $this->get_info('robot_position');
        $static_attachment_key = $this->player->get_info('player_side');
        $static_attachment_key .= '-'.$position;
        if ($position !== 'active'){ $static_attachment_key .= '-'.$this->get_info('robot_key'); }
        return $static_attachment_key;
    }

    // Define a function for collecting all attachments on this robot or affecting their position
    public function get_current_attachments(){
        $current_attachments = array();
        if (!empty($this->robot_attachments)){ foreach ($this->robot_attachments AS $token => $info){ $current_attachments[$token] = $info; }  }
        $static_attachment_key = $this->get_static_attachment_key();
        if (!empty($this->battle->battle_attachments[$static_attachment_key])){ foreach ($this->battle->battle_attachments[$static_attachment_key] AS $token => $info){ $current_attachments[$token] = $info; } }
        return $current_attachments;
    }

    // Define a public function for applying robot stat bonuses
    public function apply_stat_bonuses(){

        // Pull in the global index
        global $mmrpg_index;

        // Only continue if this hasn't been done already
        if (!empty($this->flags['apply_stat_bonuses'])){ return false; }

        /*
         * ROBOT CLASS FUNCTION APPLY STAT BONUSES
         * public function apply_stat_bonuses(){}
         */

        // If this is robot's player is human controlled
        if ($this->player->player_autopilot != true && $this->robot_class == 'master'){

            // Collect this robot's rewards and settings
            $this_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);
            $this_rewards = mmrpg_prototype_robot_rewards($this->player_token, $this->robot_token);

            // Update this robot's original player with any session settings
            $this->robot_original_player = mmrpg_prototype_robot_original_player($this->player_token, $this->robot_token);

            // If we're in a player battle, cast all robots as level 100
            if (!empty($this->battle->flags['player_battle'])
                || !empty($this->battle->flags['challenge_battle'])){
                $this->robot_base_experience = $this->robot_experience = 1000;
                $this->robot_base_level = $this->robot_level = 100;
            }
            // Otherwise collect this robot's level and experience from session
            else {
                $this->robot_base_experience = $this->robot_experience = mmrpg_prototype_robot_experience($this->player_token, $this->robot_token);
                $this->robot_base_level = $this->robot_level = mmrpg_prototype_robot_level($this->player_token, $this->robot_token);
            }


        }
        // Otherwise, if this player is on autopilot
        else {

            // Create an empty reward array to prevent errors
            $this_settings = !empty($this->values['robot_settings']) ? $this->values['robot_settings'] : array();
            $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

        }

        // If the robot experience is over 1000 points, level up and reset
        if ($this->robot_experience > 1000){
            $level_boost = floor($this->robot_experience / 1000);
            $this->robot_level += $level_boost;
            $this->robot_base_level = $this->robot_level;
            $this->robot_experience -= $level_boost * 1000;
            $this->robot_base_experience = $this->robot_experience;
        }

        // Fix the level if it's over 100
        if ($this->robot_level > 100){ $this->robot_level = 100;  }
        if ($this->robot_base_level > 100){ $this->robot_base_level = 100;  }

        // Collect this robot's stat values for later reference
        $this_index_info = self::get_index_info($this->robot_token);
        $this_robot_stats = self::calculate_stat_values($this->robot_level, $this_index_info, $this_rewards, true, $this->robot_core, $this->player->player_starforce);

        // Update the robot's stat values with calculated totals
        $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
        foreach ($stat_tokens AS $stat){
            // Collect and apply this robot's current stats and max
            $prop_stat = 'robot_'.$stat;
            $prop_stat_base = 'robot_base_'.$stat;
            $prop_stat_max = 'robot_max_'.$stat;
            $this->$prop_stat = $this_robot_stats[$stat]['current'];
            $this->$prop_stat_base = $this_robot_stats[$stat]['current'];
            $this->$prop_stat_max = $this_robot_stats[$stat]['max'];
            // If this robot's player has any stat bonuses, apply them as well
            $prop_player_stat = 'player_'.$stat;
            if (!empty($this->player->$prop_player_stat)){
                $temp_boost = ceil($this->$prop_stat * ($this->player->$prop_player_stat / 100));
                $this->$prop_stat += $temp_boost;
                $this->$prop_stat_base += $temp_boost;
            }
            // Also create a variable for mods if applicable
            if ($stat !== 'energy'
                && $stat !== 'weapons'
                && !isset($this->counters[$stat.'_mods'])){
                $this->counters[$stat.'_mods'] = 0;
            }
        }

        // If this robot is holder a relavant item, apply stat upgrades or other effects
        $this_robot_item = $this->get_item();
        if (!empty($this_robot_item)){

            // If this robot is holding an Energy Upgrade, double the life energy stat
            if ($this_robot_item == 'energy-upgrade'){
                $this->robot_energy = $this->robot_energy * 2;
                $this->robot_base_energy = $this->robot_base_energy * 2;
            }

            // Else if this robot is holding a Weapon Upgrade, double the weapon energy stat
            if ($this_robot_item == 'weapon-upgrade'){
                $this->robot_weapons = $this->robot_weapons * 2;
                $this->robot_base_weapons = $this->robot_base_weapons * 2;
            }

            // Else if this robot is holding an Elemental Core, apply a temp core shield
            if (preg_match('/^([a-z]+)-core$/', $this_robot_item)){
                list($item_type, $item_kind) = explode('-', $this_robot_item);
                if ($item_type != 'none' && $item_type != 'empty'){
                    $shield_info = rpg_ability::get_static_core_shield($item_type, 6, 0);
                    $this->robot_attachments[$shield_info['attachment_token']] = $shield_info;
                }
            }

        }

        // Create the stat boost flag
        $this->flags['apply_stat_bonuses'] = true;

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define public print functions for markup generation
    public function print_number(){ return '<span class="robot_number">'.$this->robot_number.'</span>'; }
    public function print_name(){ return '<span class="robot_name robot_type">'.$this->robot_name.'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
    public function print_token(){ return '<span class="robot_token">'.$this->robot_token.'</span>'; }
    public function print_core(){ return '<span class="robot_core '.(!empty($this->robot_core) ? 'robot_type_'.$this->robot_core : '').'">'.(!empty($this->robot_core) ? ucfirst($this->robot_core) : 'Neutral').'</span>'; }
    public function print_description(){ return '<span class="robot_description">'.$this->robot_description.'</span>'; }
    public function print_energy(){ return '<span class="robot_stat robot_stat_energy">'.$this->robot_energy.'</span>'; }
    public function print_robot_base_energy(){ return '<span class="robot_stat robot_stat_base_energy">'.$this->robot_base_energy.'</span>'; }
    public function print_attack(){ return '<span class="robot_stat robot_stat_attack">'.$this->robot_attack.'</span>'; }
    public function print_robot_base_attack(){ return '<span class="robot_stat robot_stat_base_attack">'.$this->robot_base_attack.'</span>'; }
    public function print_defense(){ return '<span class="robot_stat robot_stat_defense">'.$this->robot_defense.'</span>'; }
    public function print_robot_base_defense(){ return '<span class="robot_stat robot_stat_base_defense">'.$this->robot_base_defense.'</span>'; }
    public function print_speed(){ return '<span class="robot_stat robot_stat_speed">'.$this->robot_speed.'</span>'; }
    public function print_robot_base_speed(){ return '<span class="robot_stat robot_stat_base_speed">'.$this->robot_base_speed.'</span>'; }

    /*
    public function get_gender(){
        if ($this->robot_class === 'mecha'){ $gender = 'none'; }
        elseif (preg_match('/(^(roll|disco|rhythm)$|-woman$)/i', $this->robot_token)){ $gender = 'female'; }
        elseif (preg_match('/(^(bass)$|-man)/i', $this->robot_token)){ $gender = 'male'; }
        else { $gender = 'none'; }
        return $gender;
    }
    */

    public function get_pronoun($form = 'subject'){
        $gender = $this->get_gender();
        $is_mecha = $this->robot_class === 'mecha';
        if ($form === 'subject'){
            if ($gender === 'male'){ return 'he'; }
            elseif ($gender === 'female'){ return 'she'; }
            else { return $is_mecha ? 'it' : 'they'; }
        } elseif ($form === 'object'){
            if ($gender === 'male'){ return 'him'; }
            elseif ($gender === 'female'){ return 'her'; }
            else { return $is_mecha ? 'it' : 'them'; }
        } elseif ($form === 'possessive'){
            if ($gender === 'male'){ return 'his'; }
            elseif ($gender === 'female'){ return 'hers'; }
            else { return $is_mecha ? 'its' : 'theirs'; }
        } elseif ($form === 'possessive2'){
            if ($gender === 'male'){ return 'his'; }
            elseif ($gender === 'female'){ return 'her'; }
            else { return $is_mecha ? 'its' : 'their'; }
        } elseif ($form === 'reflexive'){
            if ($gender === 'male'){ return 'himself'; }
            elseif ($gender === 'female'){ return 'herself'; }
            else { return $is_mecha ? 'its' : 'themselves'; }
        } else {
            return false;
        }
    }

    public function print_weaknesses(){
        $this_markup = array();
        foreach ($this->robot_weaknesses AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_resistances(){
        $this_markup = array();
        foreach ($this->robot_resistances AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_affinities(){
        $this_markup = array();
        foreach ($this->robot_affinities AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_immunities(){
        $this_markup = array();
        foreach ($this->robot_immunities AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_abilities(){
        $this_markup = array();
        if (empty($this->robot_abilities)){ return false; }
        $ability_index = rpg_ability::get_index_custom($this->robot_abilities);
        foreach ($this->robot_abilities AS $ability_token){
            $ability_info = $ability_index[$ability_token];
            $ability_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : '';
            if (!empty($ability_type) && !empty($ability_info['ability_type2'])){ $ability_type .= '_'.$ability_info['ability_type2']; }
            if (empty($ability_type)){ $ability_type = 'none'; }
            $this_markup[] = '<span class="ability_name ability_type type_'.$ability_type.'">'.$ability_info['ability_name'].'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_quote($quote_type, $this_find = array(), $this_replace = array()){
        global $mmrpg_index;
        // Define the quote text variable
        $quote_text = '';
        // If the robot is visible and has the requested quote text
        if ($this->robot_token != 'robot' && isset($this->robot_quotes[$quote_type])){
            // Loop through and replace ambiguous "Player" references in quotes
            $num_words = count($this_find);
            for ($key = 0; $key < $num_words; $key++){
                $find = $this_find[$key];
                $replace = $this_replace[$key];
                if ($this->player->player_side === 'right' && $find === '{this_player}' && $replace === 'Player'){ $replace = 'the master'; }
                elseif ($this->player->player_side === 'left' && $find === '{target_player}' && $replace === 'Player'){ $replace = 'your master'; }
                else { continue; }
                $this_replace[$key] = $replace;
            }
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $this->robot_quotes[$quote_type]);
            // Collect the text colour for this robot
            $this_type_token = !empty($this->robot_core) ? $this->robot_core : 'none';
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
            $this_text_colour_bak = $this_text_colour;
            $temp_saturator = 1.25;
            if (in_array($this_type_token, array('water','wind'))){ $temp_saturator = 1.5; }
            elseif (in_array($this_type_token, array('earth', 'time', 'impact'))){ $temp_saturator = 1.75; }
            elseif (in_array($this_type_token, array('space', 'shadow'))){ $temp_saturator = 2.0; }
            elseif (in_array($this_type_token, array('empty'))){ $this_text_colour = array(172, 45, 27); }
            if ($temp_saturator > 1){
                $temp_overflow = 0;
                foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] = ceil($val * $temp_saturator); if ($this_text_colour[$key] > 255){ $temp_overflow = $this_text_colour[$key] - 255; $this_text_colour[$key] = 255; } }
                if ($temp_overflow > 0){ foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += ceil($temp_overflow / 3); if ($this_text_colour[$key] > 255){ $this_text_colour[$key] = 255; } } }
            }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }
        return $quote_text;
    }




    // Define public print functions for markup generation
    public static function print_robot_info_number($robot_info){ return '<span class="robot_number">'.$robot_info['robot_number'].'</span>'; }
    public static function print_robot_info_name($robot_info){ return '<span class="robot_name robot_type">'.$robot_info['robot_name'].'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
    public static function print_robot_info_token($robot_info){ return '<span class="robot_token">'.$robot_info['robot_token'].'</span>'; }
    public static function print_robot_info_core($robot_info){ return '<span class="robot_core '.(!empty($robot_info['robot_core']) ? 'robot_type_'.$robot_info['robot_core'] : '').'">'.(!empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral').'</span>'; }
    public static function print_robot_info_description($robot_info){ return '<span class="robot_description">'.$robot_info['robot_description'].'</span>'; }
    public static function print_robot_info_energy($robot_info){ return '<span class="robot_stat robot_stat_energy">'.$robot_info['robot_energy'].'</span>'; }
    public static function print_robot_info_base_energy($robot_info){ return '<span class="robot_stat robot_stat_base_energy">'.$robot_info['robot_base_energy'].'</span>'; }
    public static function print_robot_info_attack($robot_info){ return '<span class="robot_stat robot_stat_attack">'.$robot_info['robot_attack'].'</span>'; }
    public static function print_robot_info_base_attack($robot_info){ return '<span class="robot_stat robot_stat_base_attack">'.$robot_info['robot_base_attack'].'</span>'; }
    public static function print_robot_info_defense($robot_info){ return '<span class="robot_stat robot_stat_defense">'.$robot_info['robot_defense'].'</span>'; }
    public static function print_robot_info_base_defense($robot_info){ return '<span class="robot_stat robot_stat_base_defense">'.$robot_info['robot_base_defense'].'</span>'; }
    public static function print_robot_info_speed($robot_info){ return '<span class="robot_stat robot_stat_speed">'.$robot_info['robot_speed'].'</span>'; }
    public static function print_robot_info_base_speed($robot_info){ return '<span class="robot_stat robot_stat_base_speed">'.$robot_info['robot_base_speed'].'</span>'; }
    public static function print_robot_info_weaknesses($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_weaknesses'] AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_resistances($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_resistances'] AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_affinities($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_affinities'] AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_immunities($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_immunities'] AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_quote($robot_info, $quote_type, $this_find = array(), $this_replace = array()){
        global $mmrpg_index;
        // Define the quote text variable
        $quote_text = '';
        // If the robot is visible and has the requested quote text
        if ($robot_info['robot_token'] != 'robot' && isset($robot_info['robot_quotes'][$quote_type])){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $robot_info['robot_quotes'][$quote_type]);
            // Collect the text colour for this robot
            $this_type_token = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
            $this_text_colour_bak = $this_text_colour;
            $temp_saturator = 1.25;
            if (in_array($this_type_token, array('water','wind'))){ $temp_saturator = 1.5; }
            elseif (in_array($this_type_token, array('earth', 'time', 'impact'))){ $temp_saturator = 1.75; }
            elseif (in_array($this_type_token, array('space', 'shadow'))){ $temp_saturator = 2.0; }
            if ($temp_saturator > 1){
                $temp_overflow = 0;
                foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] = ceil($val * $temp_saturator); if ($this_text_colour[$key] > 255){ $temp_overflow = $this_text_colour[$key] - 255; $this_text_colour[$key] = 255; } }
                if ($temp_overflow > 0){ foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += ceil($temp_overflow / 3); if ($this_text_colour[$key] > 255){ $this_text_colour[$key] = 255; } } }
            }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }
        return $quote_text;
    }

    // Define a function for checking if this robot is compatible with a specific ability
    static public function has_ability_compatibility($robot_token, $ability_token, $item_token = ''){
        if (empty($robot_token) || empty($ability_token)){ return false; }
        $robot_info = is_array($robot_token) ? $robot_token : self::get_index_info($robot_token);
        $ability_info = is_array($ability_token) ? $ability_token : rpg_ability::get_index_info($ability_token);
        $item_info = is_array($item_token) ? $item_token : rpg_item::get_index_info($item_token);
        if (empty($robot_info) || empty($ability_info)){ return false; }
        $ability_token = !empty($ability_info) ? $ability_info['ability_token'] : '';
        $item_token = !empty($item_info) ? $item_info['item_token'] : '';
        $robot_token = $robot_info['robot_token'];
        $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_base_core = !empty($robot_info['robot_base_core']) ? $robot_info['robot_base_core'] : '';
        $robot_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : '';
        $robot_base_core2 = !empty($robot_info['robot_base_core2']) ? $robot_info['robot_base_core2'] : '';
        $item_core = !empty($item_token) && preg_match('/-core$/i', $item_token) ? preg_replace('/-core$/i', '', $item_token) : '';
        if ($item_core == 'none' || $item_core == 'copy'){ $item_core = ''; }
        //echo 'has_ability_compatibility('.$robot_token.', '.$ability_token.', '.$robot_core.', '.$robot_core2.')'."\n";

        // Define the compatibility flag and default to false
        $temp_compatible = false;

        // Collect the global list and return true if match is found
        $global_abilities = rpg_ability::get_global_abilities();

        // If this ability is in the list of globally compatible
        if (in_array($ability_token, $global_abilities)){ $temp_compatible = true; }
        // Else if this ability has a type, check it against this robot
        elseif (!empty($ability_info['ability_type']) || !empty($ability_info['ability_type2'])){
            //$debug_fragment .= 'has-type '; // DEBUG
            if (!empty($robot_core) || !empty($robot_core2) || !empty($item_core)){
            //$debug_fragment .= 'has-core '; // DEBUG
                if ($robot_core == 'copy' || $robot_base_core == 'copy'){
                    //$debug_fragment .= 'copy-core '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type'])
                    && ($ability_info['ability_type'] == $robot_core
                        || $ability_info['ability_type'] == $robot_core2
                        || $ability_info['ability_type'] == $item_core
                        )){
                    //$debug_fragment .= 'core-match1 '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type2'])
                    && ($ability_info['ability_type2'] == $robot_core
                        || $ability_info['ability_type2'] == $robot_core2
                        || $ability_info['ability_type2'] == $item_core
                        )){
                    //$debug_fragment .= 'core-match2 '; // DEBUG
                    $temp_compatible = true;
                }
            }
        }

        // Otherwise, check to see if this ability is in the robot's level up set
        if (!$temp_compatible && !empty($robot_info['robot_rewards']['abilities'])){
            //$debug_fragment .= 'has-levelup '; // DEBUG
            foreach ($robot_info['robot_rewards']['abilities'] AS $info){
                if ($info['token'] == $ability_info['ability_token']){
                    //$debug_fragment .= ''.$ability_info['ability_token'].'-matched '; // DEBUG
                    $temp_compatible = true;
                    break;
                }
            }
        }

        // Otherwise, see if this robot can be taught vis player only
        if (!$temp_compatible && in_array($ability_info['ability_token'], $robot_info['robot_abilities'])){
            //$debug_fragment .= 'has-playeronly '; // DEBUG
            $temp_compatible = true;
        }

        //$robot_info['robot_abilities']
        // DEBUG
        //die('Found '.$debug_fragment.' - robot '.($temp_compatible ? 'is' : 'is not').' compatible!');
        // Return the temp compatible result
        return $temp_compatible;

    }

    // Define a function for checking if this robot has a specific weakness
    public function has_weakness($weakness_token){
        if (empty($this->robot_weaknesses) || empty($weakness_token)){ return false; }
        elseif (in_array($weakness_token, $this->robot_weaknesses)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific resistance
    public function has_resistance($resistance_token){
        if (empty($this->robot_resistances) || empty($resistance_token)){ return false; }
        elseif (in_array($resistance_token, $this->robot_resistances)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific affinity
    public function has_affinity($affinity_token){
        if (empty($this->robot_affinities) || empty($affinity_token)){ return false; }
        elseif (in_array($affinity_token, $this->robot_affinities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific immunity
    public function has_immunity($immunity_token){
        if (empty($this->robot_immunities) || empty($immunity_token)){ return false; }
        elseif (in_array($immunity_token, $this->robot_immunities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is above a certain energy percent
    public function above_energy_percent($this_energy_percent){
        $actual_energy_percent = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        if ($actual_energy_percent > $this_energy_percent){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is below a certain energy percent
    public function below_energy_percent($this_energy_percent){
        $actual_energy_percent = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        if ($actual_energy_percent < $this_energy_percent){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in attack boost status
    public function has_attack_boost(){
        if ($this->robot_attack >= ($this->robot_base_attack * 2)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in attack break status
    public function has_attack_break(){
        if ($this->robot_attack <= 0){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in defense boost status
    public function has_defense_boost(){
        if ($this->robot_defense >= ($this->robot_base_defense * 2)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in defense break status
    public function has_defense_break(){
        if ($this->robot_defense <= 0){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in speed boost status
    public function has_speed_boost(){
        if ($this->robot_speed >= ($this->robot_base_speed * 2)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in speed break status
    public function has_speed_break(){
        if ($this->robot_speed <= 0){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in speed break status
    public static function robot_choices_abilities($objects){

        // Extract all objects into the current scope
        extract($objects);
        global $db;

        // Create the ability options and weights variables
        $options = array();
        $weights = array();

        // Define the support multiplier for this robot
        $support_multiplier = 1;
        if (in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm'))){ $support_multiplier += 1; }

        // Define the freency of the default buster ability if set
        if ($this_robot->has_ability('buster-shot')){
            $options[] = 'buster-shot';
            $weights[] = $this_robot->robot_token == 'met' ? 90 : 1;
        }

        // Define the freency of the omega pulse ability if set
        if ($this_robot->has_ability('omega-pulse')){
            $options[] = 'omega-pulse';
            $weights[] = 24 * $support_multiplier;
        }

        // Define the freency of the omega wave ability if set
        if ($this_robot->has_ability('omega-wave')){
            $options[] = 'omega-wave';
            $weights[] = 12 * $support_multiplier;
        }

        // Define the frequency of the energy boost ability if set
        if ($this_robot->has_ability('energy-boost')){
            $options[] = 'energy-boost';
            if ($this_robot->robot_energy >= $this_robot->robot_base_energy){ $weights[] = 0;  }
            elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 4)){ $weights[] = 14 * $support_multiplier;  }
            elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 3)){ $weights[] = 12 * $support_multiplier;  }
            elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 2)){ $weights[] = 10 * $support_multiplier;  }
        }

        // Define the frequency of the energy break ability if set
        if ($this_robot->has_ability('energy-break')){
            $options[] = 'energy-break';
            if ($target_robot->robot_energy < ($target_robot->robot_base_energy / 2)){ $weights[] = 28 * $support_multiplier;  }
            else { $weights[] = 12 * $support_multiplier; }
        }

        // Define the frequency of the energy swap ability if set
        if ($this_robot->has_ability('energy-swap')){
            $options[] = 'energy-swap';
            if ($this_robot->robot_energy < $target_robot->robot_energy){ $weights[] = 3 * $support_multiplier;  }
            elseif ($this_robot->robot_energy >= $target_robot->robot_energy){ $weights[] = 0;  }
        }

        // Define the frequency of the attack, defense, and speed boost abiliies if set
        if ($this_robot->has_ability('attack-boost')){
            $options[] = 'attack-boost';
            if ($this_robot->robot_attack < ($this_robot->robot_base_attack * 0.5)){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-boost')){
            $options[] = 'defense-boost';
            if ($this_robot->robot_defense < ($this_robot->robot_base_defense * 0.5)){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-boost')){
            $options[] = 'speed-boost';
            if ($this_robot->robot_speed < ($this_robot->robot_base_speed * 0.5)){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the attack, defense, and speed break abilities if set
        if ($this_robot->has_ability('attack-break')){
            $options[] = 'attack-break';
            if ($target_robot->robot_attack > $this_robot->robot_defense){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-break')){
            $options[] = 'defense-break';
            if ($target_robot->robot_defense > $this_robot->robot_attack){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-break')){
            $options[] = 'speed-break';
            if ($this_robot->robot_speed < $target_robot->robot_speed){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the attack, defense, and speed swap abilities if set
        if ($this_robot->has_ability('attack-swap')){
            $options[] = 'attack-swap';
            if ($this_robot->counters['attack_mods'] < $target_robot->counters['attack_mods']){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 0;  }
        }
        if ($this_robot->has_ability('defense-swap')){
            $options[] = 'defense-swap';
            if ($this_robot->counters['defense_mods'] < $target_robot->counters['defense_mods']){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 0;  }
        }
        if ($this_robot->has_ability('speed-swap')){
            $options[] = 'speed-swap';
            if ($this_robot->counters['speed_mods'] < $target_robot->counters['speed_mods']){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 0;  }
        }

        // Define the frequency of the energy/repair mode ability if set
        if ($this_robot->has_ability('energy-mode')){
            $options[] = 'energy-mode';
            if ($this_robot->robot_energy < ($this_robot->robot_base_energy * 0.5)){ $weights[] = 9 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('repair-mode')){
            $options[] = 'repair-mode';
            if ($this_robot->robot_energy < ($this_robot->robot_base_energy * 0.5)){ $weights[] = 9 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the attack, defense, and speed mode abilities if set
        if ($this_robot->has_ability('attack-mode')){
            $options[] = 'attack-mode';
            if ($this_robot->counters['attack_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $weights[] = 6 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-mode')){
            $options[] = 'defense-mode';
            if ($this_robot->counters['defense_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $weights[] = 6 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-mode')){
            $options[] = 'speed-mode';
            if ($this_robot->counters['speed_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $weights[] = 6 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the super throw ability based on benched targets
        if ($this_robot->has_ability('super-throw')){
            $options[] = 'super-throw';
            if ($target_player->counters['robots_active'] > 1){ $weights[] = 10; }
            else { $weights[] = 1; }
        }

        // Define the frequency of the mecha support ability based benched robot count
        if ($this_robot->has_ability('mecha-support')){
            $options[] = 'mecha-support';
            if ($target_player->counters['robots_total'] == 1){ $weights[] = 50; }
            elseif ($target_player->counters['robots_total'] < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ $weights[] = 10; }
            else { $weights[] = 0; }
        }

        // Define the frequency of the buster charge ability if set
        if ($this_robot->has_ability('buster-charge')){
            $options[] = 'buster-charge';
            if ($this_robot->robot_weapons < ($this_robot->robot_base_weapons / 3)){ $weights[] = 10;  }
            elseif ($this_robot->robot_weapons < ($this_robot->robot_base_weapons / 2)){ $weights[] = 5;  }
            else { $weights[] = 0;  }
        }

        // Check to see if the target has any damage-resistant cores
        $target_core_shields = array();
        if (!empty($target_robot->robot_attachments)){
            $temp_attachment_tokens = array_keys($target_robot->robot_attachments);
            foreach ($temp_attachment_tokens AS $key => $token){
                if (preg_match('/^ability_core-shield_([a-z]+)$/', $token)){
                    $target_core_shields[] = str_replace('ability_core-shield_', '', $token);
                }
            }
        }

        // Loop through any leftover abilities and add them to the weighted ability options
        $db_ability_fields = rpg_ability::get_index_fields(true);
        $temp_ability_tokens = "'".implode("','", array_values($this_robot->robot_abilities))."'";
        $temp_ability_index = $db->get_array_list("SELECT {$db_ability_fields} FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_token IN ({$temp_ability_tokens});", 'ability_token');
        foreach ($this_robot->robot_abilities AS $key => $token){
            if (!in_array($token, $options)){

                // Collect ability info and define base chance
                $info = rpg_ability::parse_index_info($temp_ability_index[$token]);
                $value = 3;

                // If this ability has a type, we can use it to alter chance values
                if (!empty($info['ability_type'])){

                    // Collect ability types into an array
                    $ability_types = array();
                    $ability_types[] = $info['ability_type'];
                    if (!empty($info['ability_type2'])){ $ability_types[] = $info['ability_type2']; }

                    // Increase chance for abilities with type that matches user's core
                    if (empty($this_robot->robot_core)){ $value *= 10; }
                    elseif ($this_robot->robot_core == 'copy'){ $value *= 12; }
                    elseif (in_array($this_robot->robot_core, $ability_types)){ $value *= 16; }

                    // Increase chance for abilities with type that matches user's held core
                    if (!empty($this_robot->robot_item) && in_array(str_replace('-core', '', $this_robot->robot_item), $ability_types)){ $value *= 8; }

                    // Increase chance if the target is weak to this ability's types
                    foreach ($ability_types AS $key2 => $type){
                        if ($target_robot->has_weakness($type)){ $value *= 2; }
                        if ($target_robot->has_resistance($type)){ $value *= 0.5; }
                        if ($target_robot->has_affinity($type)){ $value *= 0; }
                        if ($target_robot->has_immunity($type)){ $value *= 0; }
                        if (in_array($type, $target_core_shields)){ $value *= 0; }
                    }

                }

                // Append this option and its weight to the parent arrays
                if ($value > 0){
                    $options[] = $token;
                    $weights[] = ceil($value);
                }

            }
        }

        // Remove any options that have absolute zero values
        $weights = array_values($weights);
        $options = array_values($options);
        foreach ($weights AS $key => $value){
            if (empty($value)){
                unset($weights[$key]);
                unset($options[$key]);
                continue;
            }
        }

        // Re-key both arrays just in case
        $weights = array_values($weights);
        $options = array_values($options);

        /*
        $debug_text = '----------'.PHP_EOL.PHP_EOL;
        $debug_text .= 'this_robot('.$this_robot->robot_token.') vs target_robot('.$target_robot->robot_token.')'.PHP_EOL.PHP_EOL;
        $debug_text .= 'this_abilities = '.print_r($this_robot->robot_abilities, true);
        $debug_text .= 'target_core_shields = '.print_r($target_core_shields, true);
        $debug_text .= '$weights = '.print_r($weights, true);
        $debug_text .= '$options = '.print_r($options, true);
        $debug_text .= PHP_EOL.PHP_EOL;
        $debug_file = fopen(MMRPG_CONFIG_ROOTDIR.'_cache/aaaa-debug.txt', 'a');
        fwrite($debug_file, $debug_text);
        fclose($debug_file);
        */

        // This robot doesn't have ANY abilities, automatically charge
        if (empty($options) || empty($weights)){
            if ($this_robot->robot_weapons <= 4){ return 'action-chargeweapons';  }
            else { return 'action-noweapons';  }
        }

        // Return an ability based on a weighted chance
        $ability_token = $this_battle->weighted_chance($options, $weights);
        return $ability_token;

    }

    // Define a trigger for using one of this robot's abilities
    public function trigger_ability($target_robot, $this_ability){
        global $db;

        // Update this robot's history with the triggered ability
        $this->history['triggered_abilities'][] = $this_ability->ability_token;

        // Define a variable to hold the ability results
        $this_ability->ability_results = array();
        $this_ability->ability_results['total_result'] = '';
        $this_ability->ability_results['total_actions'] = 0;
        $this_ability->ability_results['total_strikes'] = 0;
        $this_ability->ability_results['total_misses'] = 0;
        $this_ability->ability_results['total_amount'] = 0;
        $this_ability->ability_results['total_overkill'] = 0;
        $this_ability->ability_results['this_result'] = '';
        $this_ability->ability_results['this_amount'] = 0;
        $this_ability->ability_results['this_overkill'] = 0;
        $this_ability->ability_results['this_text'] = '';
        $this_ability->ability_results['counter_criticals'] = 0;
        $this_ability->ability_results['counter_affinities'] = 0;
        $this_ability->ability_results['counter_weaknesses'] = 0;
        $this_ability->ability_results['counter_resistances'] = 0;
        $this_ability->ability_results['counter_immunities'] = 0;
        $this_ability->ability_results['counter_coreboosts'] = 0;
        $this_ability->ability_results['flag_critical'] = false;
        $this_ability->ability_results['flag_affinity'] = false;
        $this_ability->ability_results['flag_weakness'] = false;
        $this_ability->ability_results['flag_resistance'] = false;
        $this_ability->ability_results['flag_immunity'] = false;

        // Reset the ability options to default
        $this_ability->target_options_reset();
        $this_ability->damage_options_reset();
        $this_ability->recovery_options_reset();

        // Determine how much weapon energy this should take
        $temp_ability_energy = $this->calculate_weapon_energy($this_ability);

        // Decrease this robot's weapon energy
        $this->robot_weapons = $this->robot_weapons - $temp_ability_energy;
        if ($this->robot_weapons < 0){ $this->robot_weapons = 0; }
        $this->update_session();

        // Default this and the target robot's frames to their base
        $this->robot_frame = 'base';
        $target_robot->robot_frame = 'base';

        // Default the robot's stances to attack/defend
        $this->robot_stance = 'attack';
        $target_robot->robot_stance = 'defend';

        // If this is a copy core robot and the ability type does not match its core
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) ? $this_ability->ability_type : '';
        $temp_ability_type2 = !empty($this_ability->ability_type2) ? $this_ability->ability_type2 : $temp_ability_type;
        if (!empty($temp_ability_type) && $this->robot_base_core == 'copy' && !strstr($this->robot_base_image, '_')){
            $this->robot_image_overlay['copy_type1'] = $this->robot_base_image.'_'.$temp_ability_type.'2';
            $this->robot_image_overlay['copy_type2'] = $this->robot_base_image.'_'.$temp_ability_type2.'3';
            $this->update_session();
            $temp_image_changed = true;
        }

        // Copy the ability function to local scope and execute it
        $this_ability_function = $this_ability->ability_function;
        $this_ability_function(array(
            'this_battle' => $this->battle,
            'this_field' => $this->field,
            'this_player' => $this->player,
            'this_robot' => $this,
            'target_player' => $target_robot->player,
            'target_robot' => $target_robot,
            'this_ability' => $this_ability
            ));


        // If this robot's image has been changed, reveert it back to what it was
        if ($temp_image_changed){
            unset($this->robot_image_overlay['copy_type1']);
            unset($this->robot_image_overlay['copy_type2']);
            $this->update_session();
        }

        // DEBUG DEBUG DEBUG
        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;
        // Update this ability's history with the triggered ability damage options
        $this_ability->history['ability_options'][] = $this_ability->ability_options;

        // Reset the robot's stances to the base
        $this->robot_stance = 'base';
        $target_robot->robot_stance = 'base';

        // Update internal variables
        $target_robot->update_session();
        $this_ability->update_session();


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        $this_attachments = $this->get_current_attachments();
        $static_attachment_key = $this->get_static_attachment_key();
        if (!empty($this_attachments)){
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $db_ability_fields = rpg_ability::get_index_fields(true);
            $temp_attachments_index = $db->get_array_list("SELECT {$db_ability_fields} FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            foreach ($this_attachments AS $attachment_token => $attachment_info){

                // Ensure this ability has a type before checking weaknesses, resistances, etc.
                if (!empty($this_ability->ability_type)){

                    // If this attachment has weaknesses defined and this ability is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses'])
                            || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))
                        && (!isset($attachment_info['attachment_weaknesses_trigger'])
                            || $attachment_info['attachment_weaknesses_trigger'] === 'either'
                            || $attachment_info['attachment_weaknesses_trigger'] === 'self')
                            ){
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        unset($this->battle->battle_attachments[$static_attachment_key][$attachment_token]);
                        $this->update_session();
                        $this->battle->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            $temp_ability = rpg_ability::parse_index_info($temp_attachments_index[$attachment_info['ability_token']]);
                            $attachment_info = array_merge($temp_ability, $attachment_info);
                            $temp_attachment = rpg_game::get_ability($this->battle, $this->player, $this, $attachment_info);
                            $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                            if ($temp_trigger_type == 'damage'){
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                    $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'recovery'){
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                    $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'special'){
                                $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            break;
                        }
                    }

                }

            }
        }

        // Update internal variables
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;
    }

    // Define a trigger for using one of this robot's items
    public function trigger_item($target_robot, $this_item){
        global $db;

        // Update this robot's history with the triggered item
        $this->history['triggered_items'][] = $this_item->item_token;

        // Define a variable to hold the item results
        $this_item->item_results = array();
        $this_item->item_results['total_result'] = '';
        $this_item->item_results['total_actions'] = 0;
        $this_item->item_results['total_strikes'] = 0;
        $this_item->item_results['total_misses'] = 0;
        $this_item->item_results['total_amount'] = 0;
        $this_item->item_results['total_overkill'] = 0;
        $this_item->item_results['this_result'] = '';
        $this_item->item_results['this_amount'] = 0;
        $this_item->item_results['this_overkill'] = 0;
        $this_item->item_results['this_text'] = '';
        $this_item->item_results['counter_criticals'] = 0;
        $this_item->item_results['counter_affinities'] = 0;
        $this_item->item_results['counter_weaknesses'] = 0;
        $this_item->item_results['counter_resistances'] = 0;
        $this_item->item_results['counter_immunities'] = 0;
        $this_item->item_results['counter_coreboosts'] = 0;
        $this_item->item_results['flag_critical'] = false;
        $this_item->item_results['flag_affinity'] = false;
        $this_item->item_results['flag_weakness'] = false;
        $this_item->item_results['flag_resistance'] = false;
        $this_item->item_results['flag_immunity'] = false;

        // Reset the item options to default
        $this_item->target_options_reset();
        $this_item->damage_options_reset();
        $this_item->recovery_options_reset();

        // Determine how much weapon energy this should take
        $temp_item_energy = $this->calculate_weapon_energy($this_item);

        // Decrease this robot's weapon energy
        $this->robot_weapons = $this->robot_weapons - $temp_item_energy;
        if ($this->robot_weapons < 0){ $this->robot_weapons = 0; }
        $this->update_session();

        // Default this and the target robot's frames to their base
        $this->robot_frame = 'base';
        $target_robot->robot_frame = 'base';

        // Default the robot's stances to attack/defend
        $this->robot_stance = 'attack';
        $target_robot->robot_stance = 'defend';

        // If this is a copy core robot and the item type does not match its core
        $temp_image_changed = false;
        $temp_item_type = !empty($this_item->item_type) ? $this_item->item_type : '';
        $temp_item_type2 = !empty($this_item->item_type2) ? $this_item->item_type2 : $temp_item_type;
        if (preg_match('/^([a-z0-9]+)-(shard|core|star)/', $this_item->item_token) && !empty($temp_item_type) && $this->robot_base_core == 'copy'){
            $this->robot_image_overlay['copy_type1'] = $this->robot_base_image.'_'.$temp_item_type.'2';
            $this->robot_image_overlay['copy_type2'] = $this->robot_base_image.'_'.$temp_item_type2.'3';
            $this->update_session();
            $temp_image_changed = true;
        }

        // Copy the item function to local scope and execute it
        $this_item_function = $this_item->item_function;
        $this_item_function(array(
            'this_battle' => $this->battle,
            'this_field' => $this->field,
            'this_player' => $this->player,
            'this_robot' => $this,
            'target_player' => $target_robot->player,
            'target_robot' => $target_robot,
            'this_item' => $this_item
            ));


        // If this robot's image has been changed, reveert it back to what it was
        if ($temp_image_changed){
            unset($this->robot_image_overlay['copy_type1']);
            unset($this->robot_image_overlay['copy_type2']);
            $this->update_session();
        }

        // DEBUG DEBUG DEBUG
        // Update this item's history with the triggered item data and results
        $this_item->history['item_results'][] = $this_item->item_results;
        // Update this item's history with the triggered item damage options
        $this_item->history['item_options'][] = $this_item->item_options;

        // Reset the robot's stances to the base
        $this->robot_stance = 'base';
        $target_robot->robot_stance = 'base';

        // Update internal variables
        $target_robot->update_session();
        $this_item->update_session();


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        $this_attachments = $this->get_current_attachments();
        $static_attachment_key = $this->get_static_attachment_key();
        if (!empty($this_attachments)){
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $db_item_fields = rpg_item::get_index_fields(true);
            $temp_attachments_index = $db->get_array_list("SELECT {$db_item_fields} FROM mmrpg_index_items WHERE item_flag_complete = 1;", 'item_token');
            foreach ($this_attachments AS $attachment_token => $attachment_info){

                // Ensure this item has a type before checking weaknesses, resistances, etc.
                if (!empty($this_item->item_type)){

                    // If this attachment has weaknesses defined and this item is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_item->item_type, $attachment_info['attachment_weaknesses'])
                            || in_array($this_item->item_type2, $attachment_info['attachment_weaknesses']))
                        && (!isset($attachment_info['attachment_weaknesses_trigger'])
                            || $attachment_info['attachment_weaknesses_trigger'] === 'either'
                            || $attachment_info['attachment_weaknesses_trigger'] === 'self')
                            ){
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        unset($this->battle->battle_attachments[$static_attachment_key][$attachment_token]);
                        $this->update_session();
                        $this->battle->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            $temp_item = rpg_item::parse_index_info($temp_attachments_index[$attachment_info['item_token']]);
                            $attachment_info = array_merge($temp_item, $attachment_info);
                            $temp_attachment = rpg_game::get_item($this->battle, $this->player, $this, $attachment_info);
                            $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                            if ($temp_trigger_type == 'damage'){
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                    $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'recovery'){
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                    $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'special'){
                                $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_item' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            break;
                        }
                    }

                }

            }
        }

        // Update internal variables
        $target_robot->update_session();
        $this_item->update_session();

        // Return the item results
        return $this_item->item_results;
    }

    // Define a trigger for using one of this robot's attachments
    public function trigger_attachment($attachment_info){
        global $db;

        // If this is an ABILITY attachment
        if ($attachment_info['class'] == 'ability'){

            // Create the temporary ability object
            $attachment_info['flags']['is_attachment'] = true;
            if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_info['ability_token']; }
            $this_ability = rpg_game::get_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));

            // Update this robot's history with the triggered attachment
            $this->history['triggered_attachments'][] = 'ability_'.$this_ability->ability_token;

            // Define a variable to hold the ability results
            $this_ability->attachment_results = array();
            $this_ability->attachment_results['total_result'] = '';
            $this_ability->attachment_results['total_actions'] = 0;
            $this_ability->attachment_results['total_strikes'] = 0;
            $this_ability->attachment_results['total_misses'] = 0;
            $this_ability->attachment_results['total_amount'] = 0;
            $this_ability->attachment_results['total_overkill'] = 0;
            $this_ability->attachment_results['this_result'] = '';
            $this_ability->attachment_results['this_amount'] = 0;
            $this_ability->attachment_results['this_overkill'] = 0;
            $this_ability->attachment_results['this_text'] = '';
            $this_ability->attachment_results['counter_critical'] = 0;
            $this_ability->attachment_results['counter_affinity'] = 0;
            $this_ability->attachment_results['counter_weakness'] = 0;
            $this_ability->attachment_results['counter_resistance'] = 0;
            $this_ability->attachment_results['counter_immunity'] = 0;
            $this_ability->attachment_results['counter_coreboosts'] = 0;
            $this_ability->attachment_results['flag_critical'] = false;
            $this_ability->attachment_results['flag_affinity'] = false;
            $this_ability->attachment_results['flag_weakness'] = false;
            $this_ability->attachment_results['flag_resistance'] = false;
            $this_ability->attachment_results['flag_immunity'] = false;

            // Reset the ability options to default
            $this_ability->attachment_options_reset();

            // Default this and the target robot's frames to their base
            $this->robot_frame = 'base';
            //$target_robot->robot_frame = 'base';

            // Collect the target robot and player objects
            //$target_robot_info = $this->battle->values['robots'][];

            // Copy the attachment function to local scope and execute it
            $this_attachment_function = $this_ability->ability_function_attachment;
            $this_attachment_function(array(
                'this_battle' => $this->battle,
                'this_field' => $this->field,
                'this_player' => $this->player,
                'this_robot' => $this,
                //'target_player' => $target_robot->player,
                //'target_robot' => $target_robot,
                'this_ability' => $this_ability
                ));

            // Update this ability's attachment history with the triggered attachment data and results
            $this_ability->history['attachment_results'][] = $this_ability->attachment_results;
            // Update this ability's attachment history with the triggered attachment damage options
            $this_ability->history['attachment_options'][] = $this_ability->attachment_options;

            // Reset the robot's stances to the base
            $this->robot_stance = 'base';
            //$target_robot->robot_stance = 'base';

            // Update internal variables
            $this->update_session();
            $this_ability->update_session();

            // Return the ability results
            return $this_ability->attachment_results;

        }
        // If this is an ITEM attachment
        elseif ($attachment_info['class'] == 'item'){

            // Create the temporary item object
            $this_item = rpg_game::get_item($this->battle, $this->player, $this, array('item_token' => $attachment_info['item_token']));

            // Update this robot's history with the triggered attachment
            $this->history['triggered_attachments'][] = 'item_'.$this_item->item_token;

            // Define a variable to hold the item results
            $this_item->attachment_results = array();
            $this_item->attachment_results['total_result'] = '';
            $this_item->attachment_results['total_actions'] = 0;
            $this_item->attachment_results['total_strikes'] = 0;
            $this_item->attachment_results['total_misses'] = 0;
            $this_item->attachment_results['total_amount'] = 0;
            $this_item->attachment_results['total_overkill'] = 0;
            $this_item->attachment_results['this_result'] = '';
            $this_item->attachment_results['this_amount'] = 0;
            $this_item->attachment_results['this_overkill'] = 0;
            $this_item->attachment_results['this_text'] = '';
            $this_item->attachment_results['counter_critical'] = 0;
            $this_item->attachment_results['counter_affinity'] = 0;
            $this_item->attachment_results['counter_weakness'] = 0;
            $this_item->attachment_results['counter_resistance'] = 0;
            $this_item->attachment_results['counter_immunity'] = 0;
            $this_item->attachment_results['counter_coreboosts'] = 0;
            $this_item->attachment_results['flag_critical'] = false;
            $this_item->attachment_results['flag_affinity'] = false;
            $this_item->attachment_results['flag_weakness'] = false;
            $this_item->attachment_results['flag_resistance'] = false;
            $this_item->attachment_results['flag_immunity'] = false;

            // Reset the item options to default
            $this_item->attachment_options_reset();

            // Default this and the target robot's frames to their base
            $this->robot_frame = 'base';
            //$target_robot->robot_frame = 'base';

            // Collect the target robot and player objects
            //$target_robot_info = $this->battle->values['robots'][];

            // Copy the attachment function to local scope and execute it
            $this_attachment_function = $this_item->item_function_attachment;
            $this_attachment_function(array(
                'this_battle' => $this->battle,
                'this_field' => $this->field,
                'this_player' => $this->player,
                'this_robot' => $this,
                //'target_player' => $target_robot->player,
                //'target_robot' => $target_robot,
                'this_item' => $this_item
                ));

            // Update this item's attachment history with the triggered attachment data and results
            $this_item->history['attachment_results'][] = $this_item->attachment_results;
            // Update this item's attachment history with the triggered attachment damage options
            $this_item->history['attachment_options'][] = $this_item->attachment_options;

            // Reset the robot's stances to the base
            $this->robot_stance = 'base';
            //$target_robot->robot_stance = 'base';

            // Update internal variables
            $this->update_session();
            $this_item->update_session();

            // Return the item results
            return $this_item->attachment_results;

        }

    }

    // Define a trigger for using one of this robot's abilities or items in battle
    public function trigger_target($target_robot, $this_object, $trigger_options = array()){

        // If the battle has ended, trigger no targets
        if ($this->battle->battle_status == 'complete'){ return false; }

        // Check to see which object type has been provided
        if (isset($this_object->ability_token)){
            // This was an ability so delegate to the ability function
            return rpg_target::trigger_ability_target($this, $target_robot, $this_object, $trigger_options);

        } elseif (isset($this_object->item_token)){
            // This was an item so delegate to the item function
            return rpg_target::trigger_item_target($this, $target_robot, $this_object, $trigger_options);
        }

    }

    // Define a trigger for inflicting all types of ability or item damage on this robot
    public function trigger_damage($target_robot, $this_object, $damage_amount, $trigger_disabled = true, $trigger_options = array()){

        // If the battle has ended, trigger no damage
        if ($this->battle->battle_status == 'complete'){ return false; }

        // Define the return variable to pass back later
        $trigger_return = false;

        // Check to see which object type has been provided
        if (isset($this_object->ability_token)){
            // This was an ability so delegate to the ability class function
            $trigger_return = rpg_ability_damage::trigger_robot_damage($this, $target_robot, $this_object, $damage_amount, $trigger_disabled, $trigger_options);

        } elseif (isset($this_object->item_token)){
            // This was an item so delegate to the item class function
            //return $this->trigger_item_damage($target_robot, $this_object, $damage_amount, $trigger_disabled, $trigger_options);
            $trigger_return = rpg_item_damage::trigger_robot_damage($this, $target_robot, $this_object, $damage_amount, $trigger_disabled, $trigger_options);
        }

        // Check if this unlockable robot's data has been corrupted
        if (!empty($this->flags['robot_is_unlockable'])){

            // Calculate whether or not this robot is currently corrupted
            $is_corrupted = false;
            if (!empty($this->history['triggered_damage_types'])){
                foreach ($this->history['triggered_damage_types'] AS $types){
                    if (!empty($types)){
                        $is_corrupted = true;
                        break;
                    }
                }
            }

            // Update this robot's session flags with corrupted status
            $this->set_flag('robot_is_unlockable_corrupted', $is_corrupted);

        }

        // Return the trigger results
        return $trigger_return;

    }

    // Define a trigger for inflicting all types of ability or item recovery on this robot
    public function trigger_recovery($target_robot, $this_object, $recovery_amount, $trigger_disabled = true, $trigger_options = array()){

        // If the battle has ended, trigger no recovery
        $this_battle = rpg_battle::get_battle();
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Check to see which object type has been provided
        if (isset($this_object->ability_token)){
            // This was an ability so delegate to the ability class function
            return rpg_ability_recovery::trigger_robot_recovery($this, $target_robot, $this_object, $recovery_amount, $trigger_disabled, $trigger_options);

        } elseif (isset($this_object->item_token)){
            // This was an item so delegate to the item class function
            return rpg_item_recovery::trigger_robot_recovery($this, $target_robot, $this_object, $recovery_amount, $trigger_disabled, $trigger_options);
        }

    }

    // Define a trigger for processing disabled events from abilities
    public function trigger_disabled($target_robot, $trigger_options = array()){

        // If the battle has ended, trigger no disables
        if ($this->battle->battle_status == 'complete'){ return false; }

        // This was an ability so delegate to the ability class function
        return rpg_disabled::trigger_robot_disabled($this, $target_robot, $trigger_options);
    }

    // Define a function for calculating required weapon energy
    public function calculate_weapon_energy($this_object, &$energy_base = 0, &$energy_mods = 0){

        // If this is an item the weapon energy is zero
        if (isset($this_object->item_token)){

            // Return zero as items are free
            $energy_new = 0;
            $energy_base = 0;
            $energy_mods = 0;
            return 0;

        }
        // Otherwise if ability then we have to calculate
        elseif (isset($this_object->ability_token)){

            // Define the return to the ability variable
            $this_ability = $this_object;
            $ability_info = $this_ability->export_array();

            // Define basic robot info for caluclations
            $robot_info = array();
            $robot_info['robot_token'] = $this->robot_token;
            $robot_info['robot_class'] = $this->robot_class;
            $robot_info['robot_core'] = $this->robot_core;
            $robot_info['robot_core2'] = $this->robot_core2;
            $robot_info['robot_item'] = $this->robot_item;
            $robot_info['robot_rewards'] = $this->robot_rewards;

            // If this was the noweapons/chargeweapons action, everything is zero
            if (in_array($this_ability->ability_token, array('action-noweapons', 'action-chargeweapons', 'action-devpower-clearmission'))){
                $energy_new = 0;
                $energy_base = 0;
                $energy_mods = 0;
                return 0;
            }

            // Pass along variables to the static function and return
            $energy_new = self::calculate_weapon_energy_static($robot_info, $ability_info, $energy_base, $energy_mods);
            return $energy_new;

        }
    }

    // Define a function for calculating required weapon energy without using objects
    static function calculate_weapon_energy_static($this_robot, $this_ability, &$energy_base = 0, &$energy_mods = 0){

        // Determine how much weapon energy this should take
        $energy_new = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 0;
        $energy_base = $energy_new;
        $energy_mods = 0;

        // If this was the noweapons action, everything is zero
        if (in_array($this_ability['ability_token'], array('action-noweapons', 'action-chargeweapons', 'action-devpower-clearmission'))){
            $energy_new = 0;
            $energy_base = 0;
            $energy_mods = 0;
            return 0;
        }

        // Collect this ability's type tokens if they exist
        $ability_type_token = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none';
        $ability_type_token2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : '';

        // Collect this robot's core type tokens if they exist
        $core_type_token = !empty($this_robot['robot_core']) ? $this_robot['robot_core'] : 'none';
        $core_type_token2 = !empty($this_robot['robot_core2']) ? $this_robot['robot_core2'] : '';

        // Collect this robot's held robot core if it exists
        $core_type_token3 = '';
        if (!empty($this_robot['robot_item']) && strstr($this_robot['robot_item'], '-core')){
            $core_type_token3 = str_replace('-core', '', $this_robot['robot_item']);
        }

        // Check this ability's FIRST type for multiplier matches
        if (!empty($ability_type_token)){

            // Apply primary robot core multipliers if they exist
            if ($ability_type_token == $core_type_token){ $energy_new = ceil($energy_new * MMRPG_SETTINGS_COREBONUS_MULTIPLIER); $energy_mods++; }
            // Apply secondary robot core multipliers if they exist
            elseif ($ability_type_token == $core_type_token2){ $energy_new = ceil($energy_new * MMRPG_SETTINGS_COREBONUS_MULTIPLIER); $energy_mods++; }

            // Apply held robot core multipliers if they exist
            if ($ability_type_token == $core_type_token3){ $energy_new = ceil($energy_new * MMRPG_SETTINGS_SUBCOREBONUS_MULTIPLIER); $energy_mods++; }

        }

        // Check this ability's SECOND type for multiplier matches
        if (!empty($ability_type_token2)){

            // Apply primary robot core multipliers if they exist
            if ($ability_type_token2 == $core_type_token){ $energy_new = ceil($energy_new * MMRPG_SETTINGS_COREBONUS_MULTIPLIER); $energy_mods++; }
            // Apply secondary robot core multipliers if they exist
            elseif ($ability_type_token2 == $core_type_token2){ $energy_new = ceil($energy_new * MMRPG_SETTINGS_COREBONUS_MULTIPLIER); $energy_mods++; }

            // Apply held robot core multipliers if they exist
            if ($ability_type_token2 == $core_type_token3){ $energy_new = ceil($energy_new * MMRPG_SETTINGS_SUBCOREBONUS_MULTIPLIER); $energy_mods++; }

        }

        // Check if the user is a MECHA class robot for lower WE requirements
        if (isset($this_robot['robot_class'])
            && $this_robot['robot_class'] === 'mecha'){
            $energy_new = ceil($energy_new * MMRPG_SETTINGS_MECHABONUS_MULTIPLIER);
            $energy_mods++;
        }

        // If this ability is in the list of robot rewards, apply LEVEL UP bonuses
        if (!empty($this_robot['robot_rewards']['abilities'])){
            foreach ($this_robot['robot_rewards']['abilities'] AS $key => $reward){
                if ($reward['token'] == $this_ability['ability_token']){
                    $energy_new = ceil($energy_new * MMRPG_SETTINGS_NATIVEBONUS_MULTIPLIER);
                    $energy_mods++;
                }
            }
        }

        // Return the resulting weapon energy
        return $energy_new;

    }

    // Define a function for generating robot canvas variables
    public function canvas_markup($options, $player_data){

        // Delegate markup generation to the canvas class
        return rpg_canvas::robot_markup($this, $options, $player_data);

    }

    // Define a function for generating robot console variables
    public function console_markup($options, $player_data){

        // Delegate markup generation to the console class
        return rpg_console::robot_markup($this, $options, $player_data);

    }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all robot index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_index_fields($implode = false){

        // Define the various index fields for robot objects
        $index_fields = array(
            'robot_id',
            'robot_token',
            'robot_number',
            'robot_name',
            'robot_game',
            'robot_group',
            'robot_field',
            'robot_field2',
            'robot_class',
            'robot_gender',
            'robot_image',
            'robot_image_size',
            'robot_image_editor',
            'robot_image_editor2',
            'robot_image_alts',
            'robot_core',
            'robot_core2',
            'robot_description',
            'robot_description2',
            'robot_energy',
            'robot_weapons',
            'robot_attack',
            'robot_defense',
            'robot_speed',
            'robot_weaknesses',
            'robot_resistances',
            'robot_affinities',
            'robot_immunities',
            'robot_abilities_rewards',
            'robot_abilities_compatible',
            'robot_quotes_start',
            'robot_quotes_taunt',
            'robot_quotes_victory',
            'robot_quotes_defeat',
            'robot_functions',
            'robot_flag_hidden',
            'robot_flag_complete',
            'robot_flag_unlockable',
            'robot_flag_exclusive',
            'robot_flag_published',
            'robot_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

    /**
     * Get the entire robot index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $filter_class = '', $include_tokens = array()){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND robot_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND robot_flag_published = 1 '; }
        if (!empty($filter_class)){ $temp_where .= "AND robot_class = '{$filter_class}' "; }
        if (!empty($include_tokens)){
            $include_string = $include_tokens;
            array_walk($include_string, function(&$s){ $s = "'{$s}'"; });
            $include_tokens = implode(', ', $include_string);
            $temp_where .= 'OR robot_token IN ('.$include_tokens.') ';
        }

        // Collect every type's info from the database index
        $robot_fields = self::get_index_fields(true);
        $robot_index = $db->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE robot_id <> 0 {$temp_where};", 'robot_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($robot_index)){
            $robot_index = self::parse_index($robot_index);
            return $robot_index;
        } else {
            return array();
        }

    }

    /**
     * Get the tokens for all robots in the global index
     * @return array
     */
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false, $filter_class = ''){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND robot_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND robot_flag_published = 1 '; }
        if (!empty($filter_class)){ $temp_where .= "AND robot_class = '{$filter_class}' "; }

        // Collect an array of robot tokens from the database
        $robot_index = $db->get_array_list("SELECT robot_token FROM mmrpg_index_robots WHERE robot_id <> 0 {$temp_where};", 'robot_token');

        // Return the tokens if not empty, else nothing
        if (!empty($robot_index)){
            $robot_tokens = array_keys($robot_index);
            return $robot_tokens;
        } else {
            return array();
        }

    }

    // Define a function for pulling a custom robot index
    public static function get_index_custom($robot_tokens = array()){

        // If the robot tokens were empty, return nothing
        if (empty($robot_tokens)){ return array(); }

        // Pull in global variables
        $db = cms_database::get_database();

        // Generate a token string for the database query
        $robot_tokens_string = array();
        foreach ($robot_tokens AS $robot_token){ $robot_tokens_string[] = "'{$robot_token}'"; }
        $robot_tokens_string = implode(', ', $robot_tokens_string);

        // Collect the requested robot's info from the database index
        $robot_fields = self::get_index_fields(true);
        $robot_index = $db->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE robot_token IN ({$robot_tokens_string});", 'robot_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($robot_index)){
            $robot_index = self::parse_index($robot_index);
            return $robot_index;
        } else {
            return array();
        }

    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($robot_token){

        // Pull in global variables
        $db = cms_database::get_database();

        // Collect this robot's info from the database index
        $lookup = !is_numeric($robot_token) ? "robot_token = '{$robot_token}'" : "robot_id = {$robot_token}";
        $robot_fields = self::get_index_fields(true);
        $robot_index = $db->get_array("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE {$lookup};", 'robot_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($robot_index)){
            $robot_index = self::parse_index_info($robot_index);
            return $robot_index;
        } else {
            return array();
        }

    }

    // Define a public function for parsing a robot index array in bulk
    public static function parse_index($robot_index){

        // Loop through each entry and parse its data
        foreach ($robot_index AS $token => $info){
            $robot_index[$token] = self::parse_index_info($info);
        }

        // Return the parsed index
        return $robot_index;

    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($robot_info){

        // Return false if empty
        if (empty($robot_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($robot_info['_parsed'])){ return $robot_info; }
        else { $robot_info['_parsed'] = true; }

        // Explode the weaknesses, resistances, affinities, and immunities into an array
        $temp_field_names = array('robot_weaknesses', 'robot_resistances', 'robot_affinities', 'robot_immunities', 'robot_image_alts');
        foreach ($temp_field_names AS $field_name){
            if (!empty($robot_info[$field_name])){ $robot_info[$field_name] = json_decode($robot_info[$field_name], true); }
            else { $robot_info[$field_name] = array(); }
        }

        // Explode the abilities into the appropriate array
        $robot_info['robot_abilities'] = !empty($robot_info['robot_abilities_compatible']) ? json_decode($robot_info['robot_abilities_compatible'], true) : array();
        unset($robot_info['robot_abilities_compatible']);

        // Explode the abilities into the appropriate array
        $robot_info['robot_rewards']['abilities'] = !empty($robot_info['robot_abilities_rewards']) ? json_decode($robot_info['robot_abilities_rewards'], true) : array();
        unset($robot_info['robot_abilities_rewards']);

        // Collect the quotes into the proper arrays
        $quote_types = array('start', 'taunt', 'victory', 'defeat');
        foreach ($quote_types AS $type){
            $robot_info['robot_quotes']['battle_'.$type] = !empty($robot_info['robot_quotes_'.$type]) ? $robot_info['robot_quotes_'.$type]: '';
            unset($robot_info['robot_quotes_'.$type]);
        }

        // Return the parsed robot info
        return $robot_info;
    }

    // Define a public function for recalculating internal counters
    public function update_variables(){

        // Create variables that don't exist if necessary
        if (!isset($this->history['turns_active'])){ $this->history['turns_active'] = array(); }
        if (!isset($this->history['turns_benched'])){ $this->history['turns_benched'] = array(); }

        // Calculate this robot's count variables
        $this->counters['abilities_total'] = count($this->robot_abilities);

        // Recalculate this robot's effective stats based on modifiers
        $stat_tokens = array('attack', 'defense', 'speed');
        $mod_numerator = 2;
        $mod_denominator = 2;
        foreach ($stat_tokens AS $key => $stat){
            // Collect prop names and base value
            $prop_stat = 'robot_'.$stat;
            $prop_stat_base = 'robot_base_'.$stat;
            $base_value = $this->$prop_stat_base;
            // Reset the stat to its base value to start
            $new_value = $base_value;
            // Apply any positive or negative stat mods to the value
            if (!empty($this->counters[$stat.'_mods'])){
                $stage = $this->counters[$stat.'_mods'];
                $numerator = $mod_numerator;
                $denominator = $mod_denominator;
                if ($stage > 0){ $numerator += $stage; }
                elseif ($stage < 0){ $denominator += ($stage * -1); }
                $new_value = ceil($base_value * ($numerator / $denominator));
            }
            // Update the robot with the recalculated value
            $this->$prop_stat = $new_value;
        }

        // Reset this robot's elemental properties back to base
        $element_stats = array('weaknesses', 'resistances', 'affinities', 'immunities');
        foreach ($element_stats AS $key => $stat){
            $prop_stat = 'robot_'.$stat;
            $prop_stat_base = 'robot_base_'.$stat;
            $this->$prop_stat = $this->$prop_stat_base;
        }

        // Apply any affinities granted by hold items
        $elemental_items = array(
            'battery-circuit' => array('electric', 'nature'),
            'sponge-circuit' => array('water', 'electric'),
            'forge-circuit' => array('flame', 'water'),
            'sapling-circuit' => array('nature', 'flame')
            );
        if (!empty($this->robot_item)
            && !empty($elemental_items[$this->robot_item])){
            // Collect the element associated with this item
            $elemental_item = $elemental_items[$this->robot_item];
            // First we process the AFFINITY mods for this robot
            if (!empty($elemental_item[0])){
                $item_affinity = $elemental_item[0];
                // Add the element as an affinity if not already there
                if (!in_array($item_affinity, $this->robot_affinities)){ $this->robot_affinities[] = $item_affinity; }
                // Remove the element from other lists if applicable
                if (in_array($item_affinity, $this->robot_weaknesses)){ unset($this->robot_weaknesses[array_search($item_affinity, $this->robot_weaknesses)]); }
                if (in_array($item_affinity, $this->robot_resistances)){ unset($this->robot_resistances[array_search($item_affinity, $this->robot_resistances)]); }
                if (in_array($item_affinity, $this->robot_immunities)){ unset($this->robot_immunities[array_search($item_affinity, $this->robot_immunities)]); }
            }
            // First we process the WEAKNESS mods for this robot
            if (!empty($elemental_item[1])){
                $item_weakness = $elemental_item[1];
                // Add the element as an weakness if not already there
                if (!in_array($item_weakness, $this->robot_weaknesses)){ $this->robot_weaknesses[] = $item_weakness; }
                // Remove the element from other lists if applicable
                if (in_array($item_weakness, $this->robot_affinities)){ unset($this->robot_affinities[array_search($item_weakness, $this->robot_affinities)]); }
                if (in_array($item_weakness, $this->robot_resistances)){ unset($this->robot_resistances[array_search($item_weakness, $this->robot_resistances)]); }
                if (in_array($item_weakness, $this->robot_immunities)){ unset($this->robot_immunities[array_search($item_weakness, $this->robot_immunities)]); }
            }
        }

        // Now collect an export array for this object
        $this_data = $this->export_array();

        // Update the parent battle variable
        $this->battle->values['robots'][$this->robot_id] = $this_data;

        // Find and update the parent's robot variable
        foreach ($this->player->player_robots AS $this_key => $this_robotinfo){
            if ($this_robotinfo['robot_id'] == $this->robot_id){
                $this->player->player_robots[$this_key] = $this_data;
                break;
            }
        }

        // Return true on success
        return true;

    }

    // Define a public, static function for resetting robot values to base
    public static function reset_variables($this_data){
        $this_data['robot_flags'] = array();
        $this_data['robot_counters'] = array();
        $this_data['robot_values'] = array();
        $this_data['robot_history'] = array();
        $this_data['robot_name'] = $this_data['robot_base_name'];
        $this_data['robot_token'] = $this_data['robot_base_token'];
        $this_data['robot_description'] = $this_data['robot_base_description'];
        $this_data['robot_energy'] = $this_data['robot_base_energy'];
        $this_data['robot_weapons'] = $this_data['robot_base_weapons'];
        $this_data['robot_attack'] = $this_data['robot_base_attack'];
        $this_data['robot_defense'] = $this_data['robot_base_defense'];
        $this_data['robot_speed'] = $this_data['robot_base_speed'];
        $this_data['robot_weaknesses'] = $this_data['robot_base_weaknesses'];
        $this_data['robot_resistances'] = $this_data['robot_base_resistances'];
        $this_data['robot_affinities'] = $this_data['robot_base_affinities'];
        $this_data['robot_immunities'] = $this_data['robot_base_immunities'];
        //$this_data['robot_abilities'] = $this_data['robot_base_abilities'];
        $this_data['robot_attachments'] = $this_data['robot_base_attachments'];
        $this_data['robot_quotes'] = $this_data['robot_base_quotes'];
        return $this_data;

    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Request parent player object to update as well
        //$this->player->update_session();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['ROBOTS'][$this->robot_id] = $this_data;
        $this->battle->values['robots'][$this->robot_id] = $this_data;
        //$this->player->values['robots'][$this->robot_id] = $this_data;

        // Return true on success
        return true;

    }


    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal robot fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_token' => $this->player_token,
            'robot_key' => $this->robot_key,
            'robot_id' => $this->robot_id,
            'robot_number' => $this->robot_number,
            'robot_name' => $this->robot_name,
            'robot_token' => $this->robot_token,
            'robot_field' => $this->robot_field,
            'robot_field2' => $this->robot_field2,
            'robot_class' => $this->robot_class,
            'robot_gender' => $this->robot_gender,
            'robot_item' => $this->robot_item,
            'robot_image' => $this->robot_image,
            'robot_image_size' => $this->robot_image_size,
            'robot_image_overlay' => $this->robot_image_overlay,
            'robot_image_alts' => $this->robot_image_alts,
            'robot_core' => $this->robot_core,
            'robot_description' => $this->robot_description,
            'robot_experience' => $this->robot_experience,
            'robot_level' => $this->robot_level,
            'robot_energy' => $this->robot_energy,
            'robot_weapons' => $this->robot_weapons,
            'robot_attack' => $this->robot_attack,
            'robot_defense' => $this->robot_defense,
            'robot_speed' => $this->robot_speed,
            'robot_weaknesses' => $this->robot_weaknesses,
            'robot_resistances' => $this->robot_resistances,
            'robot_affinities' => $this->robot_affinities,
            'robot_immunities' => $this->robot_immunities,
            'robot_abilities' => $this->robot_abilities,
            'robot_attachments' => $this->robot_attachments,
            'robot_quotes' => $this->robot_quotes,
            'robot_rewards' => $this->robot_rewards,
            'robot_functions' => $this->robot_functions,
            'robot_base_name' => $this->robot_base_name,
            'robot_base_token' => $this->robot_base_token,
            'robot_base_image' => $this->robot_base_image,
            'robot_base_image_size' => $this->robot_base_image_size,
            'robot_base_image_overlay' => $this->robot_base_image_overlay,
            'robot_base_core' => $this->robot_base_core,
            'robot_base_core2' => $this->robot_base_core2,
            'robot_base_description' => $this->robot_base_description,
            'robot_base_experience' => $this->robot_base_experience,
            'robot_base_level' => $this->robot_base_level,
            'robot_base_energy' => $this->robot_base_energy,
            'robot_base_weapons' => $this->robot_base_weapons,
            'robot_base_attack' => $this->robot_base_attack,
            'robot_base_defense' => $this->robot_base_defense,
            'robot_base_speed' => $this->robot_base_speed,
            'robot_max_energy' => $this->robot_max_energy,
            'robot_max_weapons' => $this->robot_max_weapons,
            'robot_max_attack' => $this->robot_max_attack,
            'robot_max_defense' => $this->robot_max_defense,
            'robot_max_speed' => $this->robot_max_speed,
            'robot_base_weaknesses' => $this->robot_base_weaknesses,
            'robot_base_resistances' => $this->robot_base_resistances,
            'robot_base_affinities' => $this->robot_base_affinities,
            'robot_base_immunities' => $this->robot_base_immunities,
            //'robot_base_abilities' => $this->robot_base_abilities,
            'robot_base_attachments' => $this->robot_base_attachments,
            'robot_base_quotes' => $this->robot_base_quotes,
            //'robot_base_rewards' => $this->robot_base_rewards,
            'robot_status' => $this->robot_status,
            'robot_position' => $this->robot_position,
            'robot_stance' => $this->robot_stance,
            'robot_frame' => $this->robot_frame,
            //'robot_frame_index' => $this->robot_frame_index,
            'robot_frame_offset' => $this->robot_frame_offset,
            'robot_frame_classes' => $this->robot_frame_classes,
            'robot_frame_styles' => $this->robot_frame_styles,
            'robot_detail_styles' => $this->robot_detail_styles,
            'robot_original_player' => $this->robot_original_player,
            'robot_string' => $this->robot_string,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

    // Define a static function for printing out the robot's database markup
    public static function print_database_markup($robot_info, $print_options = array()){

        // Define the markup variable
        $this_markup = '';

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $mmrpg_database_players, $mmrpg_database_items, $mmrpg_database_types;
        global $mmrpg_stat_base_max_value;

        // Define any local static variables
        static $mmrpg_database_fields;

        // Collect the approriate database indexes
        if (empty($mmrpg_database_players)){ $mmrpg_database_players = rpg_player::get_index(true); }
        if (empty($mmrpg_database_items)){ $mmrpg_database_items = rpg_item::get_index(true); }
        if (empty($mmrpg_database_fields)){ $mmrpg_database_fields = rpg_field::get_index(true); }
        if (empty($mmrpg_database_types)){ $mmrpg_database_types = rpg_type::get_index(); }

        // Define the print style defaults
        if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
        if ($print_options['layout_style'] == 'website'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = true; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'event'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = false; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = false; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        }

        // Collect the robot sprite dimensions
        $robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
        $robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
        $robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
        //die('<pre>$robot_info = '.print_r($robot_info, true).'</pre>');

        // Collect the robot's type for background display
        $robot_header_types = 'type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none').' ';

        // Define the sprite sheet alt and title text
        $robot_sprite_size = $robot_image_size * 2;
        $robot_sprite_size_text = $robot_sprite_size.'x'.$robot_sprite_size;
        $robot_sprite_title = $robot_info['robot_name'];
        //$robot_sprite_title = $robot_info['robot_number'].' '.$robot_info['robot_name'];
        //$robot_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

        // If this is a mecha, define it's generation for display
        $robot_info['robot_name_append'] = '';
        if (!empty($robot_info['robot_class']) && $robot_info['robot_class'] == 'mecha'){
            $robot_info['robot_generation'] = '1st';
            if (preg_match('/-2$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '2nd'; $robot_info['robot_name_append'] = ' 2'; }
            elseif (preg_match('/-3$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '3rd'; $robot_info['robot_name_append'] = ' 3'; }
        } elseif (preg_match('/^duo/i', $robot_info['robot_token'])){

        }

        // Define the sprite frame index for robot images
        $robot_sprite_frames = array('base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage','base2');

        // Collect the field info if applicable
        $field_info_array = array();
        if (!empty($robot_info['robot_field']) && $robot_info['robot_field'] != 'field'){ $index_info = rpg_field::get_index_info($robot_info['robot_field']); if (!empty($index_info)){ $field_info_array[] = $index_info; } }
        if (!empty($robot_info['robot_field2']) && $robot_info['robot_field2'] != 'field'){ $index_info = rpg_field::get_index_info($robot_info['robot_field2']); if (!empty($index_info)){ $field_info_array[] = $index_info; } }

        // Define the class token for this robot
        $robot_class_token = '';
        $robot_class_token_plural = '';
        if ($robot_info['robot_class'] == 'master'){
            $robot_class_token = 'robot';
            $robot_class_token_plural = 'robots';
        } elseif ($robot_info['robot_class'] == 'mecha'){
            $robot_class_token = 'mecha';
            $robot_class_token_plural = 'mechas';
        } elseif ($robot_info['robot_class'] == 'boss'){
            $robot_class_token = 'boss';
            $robot_class_token_plural = 'bosses';
        }
        // Define the default class tokens for "empty" images
        $default_robot_class_tokens = array('robot', 'mecha', 'boss');

        // Automatically disable sections if content is unavailable
        if (empty($robot_info['robot_description2'])){ $print_options['show_description'] = false;  }
        if (isset($robot_info['robot_image_sheets']) && $robot_info['robot_image_sheets'] === 0){ $print_options['show_sprites'] = false; }
        elseif (in_array($robot_image_token, $default_robot_class_tokens)){ $print_options['show_sprites'] = false; }

        // Define the base URLs for this robot
        $database_url = 'database/';
        $database_category_url = $database_url;
        if ($robot_info['robot_class'] == 'master'){ $database_category_url .= 'robots/'; }
        elseif ($robot_info['robot_class'] == 'mecha'){ $database_category_url .= 'mechas/'; }
        elseif ($robot_info['robot_class'] == 'boss'){ $database_category_url .= 'bosses/'; }
        $database_category_robot_url = $database_category_url.$robot_info['robot_token'].'/';

        // Calculate the robot base stat total
        $robot_info['robot_total'] = 0;
        $robot_info['robot_total'] += $robot_info['robot_energy'];
        $robot_info['robot_total'] += $robot_info['robot_attack'];
        $robot_info['robot_total'] += $robot_info['robot_defense'];
        $robot_info['robot_total'] += $robot_info['robot_speed'];

        // Calculate this robot's maximum base stat for reference
        $robot_info['robot_max_stat_name'] = 'unknown';
        $robot_info['robot_max_stat_value'] = 0;
        $temp_types = array('energy', 'attack', 'defense', 'speed');
        foreach ($temp_types AS $type){
            if ($robot_info['robot_'.$type] > $robot_info['robot_max_stat_value']){
                $robot_info['robot_max_stat_value'] = $robot_info['robot_'.$type];
                $robot_info['robot_max_stat_name'] = $type;
            }
        }


        // Collect the database records for this robot
        if ($print_options['show_records']){

            // Pull in global DB and preset the record array
            global $db;
            $temp_robot_records = array('robot_encountered' => 0, 'robot_defeated' => 0, 'robot_unlocked' => 0, 'robot_summoned' => 0, 'robot_scanned' => 0, 'robot_avatars' => 0);

            // Check to see if a recent record already exists and use it if possible
            $temp_fields = implode(', ', array_keys($temp_robot_records));
            $record_token = $robot_info['robot_token'];
            $existing_record = $db->get_array("SELECT
                record_id,
                record_time,
                robot_token,
                {$temp_fields}
                FROM mmrpg_records_robots
                WHERE robot_token = '{$record_token}'
                ;");

            // If the record exists and isn't too old, loop through and collect
            if (!empty($existing_record)
                && $existing_record['record_time'] >= MMRPG_CONFIG_LAST_SAVE_DATE){
                foreach ($temp_robot_records AS $token => $value){
                    if (!empty($existing_record[$token])){
                        $temp_robot_records[$token] = $existing_record[$token];
                        continue;
                    }
                }
            }
            // Otherwise, if record not exists or too old, generate a new one
            else {

                // Collect relevant save files from the database
                $temp_player_query = "SELECT
                    saves.user_id,
                    saves.save_values_robot_database,
                    board.board_points,
                    users.user_image_path
                    FROM mmrpg_saves AS saves
                    LEFT JOIN mmrpg_leaderboard AS board ON board.user_id = saves.user_id
                    LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
                    WHERE
                    board.board_points > 0
                    AND saves.save_values_robot_database LIKE '%\"{$record_token}\"%'
                    ;";
                $temp_player_list = $db->get_array_list($temp_player_query);

                // Loop through save files and count unlock records etc.
                if (!empty($temp_player_list)){
                    foreach ($temp_player_list AS $temp_data){
                        $temp_values = !empty($temp_data['save_values_robot_database']) ? json_decode($temp_data['save_values_robot_database'], true) : array();
                        $temp_entry = !empty($temp_values[$robot_info['robot_token']]) ? $temp_values[$robot_info['robot_token']] : array();
                        foreach ($temp_robot_records AS $temp_record => $temp_count){
                            if (!empty($temp_entry[$temp_record])){ $temp_robot_records[$temp_record] += $temp_entry[$temp_record]; }
                        }
                        if (strstr($temp_data['user_image_path'], 'robots/'.$robot_info['robot_token'])){
                            $temp_robot_records['robot_avatars'] += 1;
                        }
                    }
                }

                // Now that we have the data, either insert or update the record in the db
                if (empty($existing_record)){
                    $insert_array = array();
                    $insert_array['record_time'] = MMRPG_CONFIG_LAST_SAVE_DATE;
                    $insert_array['robot_token'] = $record_token;
                    foreach ($temp_robot_records AS $token => $value){ $insert_array[$token] = $value; }
                    $db->insert('mmrpg_records_robots', $insert_array);
                } else {
                    $update_array = array();
                    $update_array['record_time'] = MMRPG_CONFIG_LAST_SAVE_DATE;
                    foreach ($temp_robot_records AS $token => $value){ $update_array[$token] = $value; }
                    $db->update('mmrpg_records_robots', $update_array, array('robot_token' => $record_token));
                }


            }


        }

        // Define the common stat container variables
        $stat_container_percent = 74;
        $stat_base_max_value = 2000;
        $stat_padding_area = 76;
        if (!empty($mmrpg_stat_base_max_value[$robot_info['robot_class']])){ $stat_base_max_value = $mmrpg_stat_base_max_value[$robot_info['robot_class']]; }
        elseif ($robot_info['robot_class'] == 'master'){ $stat_base_max_value = 400; }
        elseif ($robot_info['robot_class'] == 'mecha'){ $stat_base_max_value = 400; }
        elseif ($robot_info['robot_class'] == 'boss'){ $stat_base_max_value = 2000; }


        // Define the variable to hold compact footer link markup
        $compact_footer_link_markup = array();

        // Start the output buffer
        ob_start();
        /*<div class="database_container database_<?= $robot_class_token ?>_container database_<?= $print_options['layout_style'] ?>_container" data-token="<?= $robot_info['robot_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">*/
        ?>
        <div class="database_container layout_<?= str_replace('website_', '', $print_options['layout_style']) ?>" data-token="<?= $robot_info['robot_token']?>">

            <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?= $robot_info['robot_token'] ?>"></a>
            <? endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $robot_info['robot_token']?>">

                <? if($print_options['show_mugshot']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_mugshot']): ?>
                            <? if($print_options['show_key'] !== false): ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.$robot_info['robot_key'] ?></div>
                            <? endif; ?>
                            <? if (!in_array($robot_image_token, $default_robot_class_tokens)){ ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: url(images/robots/<?= $robot_image_token ?>/mug_right_<?= $robot_image_size_text ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?>'s Mugshot</div></div>
                            <? } else { ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active">No Image</div></div>
                            <? } ?>
                        <? endif; ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $robot_header_types ?> <?= (!$print_options['show_mugshot']) ? 'nomug' : '' ?>" style="<?= (!$print_options['show_mugshot']) ? 'margin-left: 0;' : '' ?>">
                        <? if ($print_options['layout_style'] == 'website_compact'){ ?>
                            <a href="<?= $database_category_robot_url ?>"><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></a>
                        <? } else { ?>
                            <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>
                        <? } ?>
                        <? if ($robot_info['robot_class'] == 'master' && empty($robot_info['robot_flag_unlockable'])){ ?>
                            <span class="not_unlockable" data-tooltip-type="<?= $robot_header_types ?>" title="* This robot cannot be unlocked right now">*</span>
                        <? } ?>
                        <div class="header_core robot_type"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'mecha' ? ' Type' : ' Core' ?></div>
                    </h2>

                    <div class="body body_left <?= !$print_options['show_mugshot'] ? 'fullsize' : '' ?>">

                        <table class="full basic">
                            <tbody>
                                <? if($print_options['layout_style'] != 'event'): ?>
                                    <tr>
                                        <td  class="right">
                                            <label>Name :</label>
                                            <span class="robot_type" style="width: auto;"><?= $robot_info['robot_name']?></span>
                                            <? if (!empty($robot_info['robot_generation'])){ ?><span class="robot_type" style="width: auto;"><?= $robot_info['robot_generation']?> Gen</span><? } ?>
                                        </td>
                                    </tr>
                                <? endif; ?>
                                <tr>
                                    <td  class="right">
                                        <label>Model :</label>
                                        <span class="robot_type"><?= $robot_info['robot_number']?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Type :</label>
                                        <? if($print_options['layout_style'] != 'event'): ?>
                                            <? if(!empty($robot_info['robot_core2'])): ?>
                                                <span class="robot_type type_<?= $robot_info['robot_core'].'_'.$robot_info['robot_core2'] ?>">
                                                    <a href="<?= $database_category_url ?><?= $robot_info['robot_core'] ?>/"><?= ucfirst($robot_info['robot_core']) ?></a> /
                                                    <a href="<?= $database_category_url ?><?= $robot_info['robot_core2'] ?>/"><?= ucfirst($robot_info['robot_core2']) ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                                                </span>
                                            <? else: ?>
                                                <a href="<?= $database_category_url ?><?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/" class="robot_type type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                                            <? endif; ?>
                                        <? else: ?>
                                            <span class="robot_type type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></span>
                                        <? endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="full extras">
                            <tbody>
                                <? if($print_options['layout_style'] != 'event'): ?>
                                    <tr>
                                        <td class="right">
                                            <?
                                            // Define the source game string
                                            if ($robot_info['robot_token'] == 'mega-man' || $robot_info['robot_token'] == 'roll'){ $temp_source_string = 'Mega Man'; }
                                            elseif ($robot_info['robot_token'] == 'proto-man'){ $temp_source_string = 'Mega Man 3'; }
                                            elseif ($robot_info['robot_token'] == 'bass'){ $temp_source_string = 'Mega Man 7'; }
                                            elseif ($robot_info['robot_token'] == 'disco' || $robot_info['robot_token'] == 'rhythm'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif (preg_match('/^flutter-fly/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif (preg_match('/^beetle-borg/i', $robot_info['robot_token'])){ $temp_source_string = '<span title="Rockman &amp; Forte 2 : Challenger from the Future (JP)">Mega Man &amp; Bass 2</span>'; }
                                            elseif ($robot_info['robot_token'] == 'bond-man'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif ($robot_info['robot_token'] == 'enker'){ $temp_source_string = 'Mega Man : Dr. Wily\'s Revenge'; }
                                            elseif ($robot_info['robot_token'] == 'punk'){ $temp_source_string = 'Mega Man III'; }
                                            elseif ($robot_info['robot_token'] == 'ballade'){ $temp_source_string = 'Mega Man IV'; }
                                            elseif ($robot_info['robot_token'] == 'quint'){ $temp_source_string = 'Mega Man II'; }
                                            elseif ($robot_info['robot_token'] == 'oil-man' || $robot_info['robot_token'] == 'time-man'){ $temp_source_string = 'Mega Man Powered Up'; }
                                            elseif ($robot_info['robot_token'] == 'solo'){ $temp_source_string = 'Mega Man Star Force 3'; }
                                            elseif (preg_match('/^duo-2/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man 8'; }
                                            elseif (preg_match('/^duo/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man Power Battles'; }
                                            elseif (preg_match('/^trio/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif ($robot_info['robot_token'] == 'cosmo-man' || $robot_info['robot_token'] == 'lark-man'){ $temp_source_string = 'Mega Man Battle Network 5'; }
                                            elseif ($robot_info['robot_token'] == 'laser-man'){ $temp_source_string = 'Mega Man Battle Network 4'; }
                                            elseif ($robot_info['robot_token'] == 'desert-man'){ $temp_source_string = 'Mega Man Battle Network 3'; }
                                            elseif ($robot_info['robot_token'] == 'planet-man' || $robot_info['robot_token'] == 'gate-man'){ $temp_source_string = 'Mega Man Battle Network 2'; }
                                            elseif ($robot_info['robot_token'] == 'shark-man' || $robot_info['robot_token'] == 'number-man' || $robot_info['robot_token'] == 'color-man'){ $temp_source_string = 'Mega Man Battle Network'; }
                                            elseif ($robot_info['robot_token'] == 'trill' || $robot_info['robot_token'] == 'slur'){ $temp_source_string = '<span title="Rockman.EXE Stream (JP)">Mega Man NT Warrior</span>'; }
                                            elseif ($robot_info['robot_game'] == 'MM085'){ $temp_source_string = '<span title="Rockman &amp; Forte (JP)">Mega Man &amp; Bass</span>'; }
                                            elseif ($robot_info['robot_game'] == 'MM30'){ $temp_source_string = 'Mega Man V'; }
                                            elseif ($robot_info['robot_game'] == 'MM21'){ $temp_source_string = 'Mega Man : The Wily Wars'; }
                                            elseif ($robot_info['robot_game'] == 'MM19'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif ($robot_info['robot_game'] == 'MMEXE'){ $temp_source_string = 'Mega Man EXE'; }
                                            elseif ($robot_info['robot_game'] == 'MM00' || $robot_info['robot_game'] == 'MM01'){ $temp_source_string = 'Mega Man'; }
                                            elseif (preg_match('/^MM([0-9]{2})$/', $robot_info['robot_game'])){ $temp_source_string = 'Mega Man '.ltrim(str_replace('MM', '', $robot_info['robot_game']), '0'); }
                                            elseif (!empty($robot_info['robot_game'])){ $temp_source_string = $robot_info['robot_game']; }
                                            else { $temp_source_string = '???'; }
                                            ?>
                                            <label>Source :</label>
                                            <span class="robot_type"><?= $temp_source_string ?></span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                                <tr>
                                    <td  class="right">
                                        <label>Class :</label>
                                        <span class="robot_type"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right fields" data-count="<?= count($field_info_array) ?>">
                                        <label><?= empty($field_info_array) || count($field_info_array) == 1 ? 'Field' : 'Fields' ?> :</label>
                                        <?
                                        // Loop through the robots fields if available
                                        if (!empty($field_info_array)){
                                            foreach ($field_info_array AS $key => $field_info){
                                                $name = $field_info['field_name'];
                                                list($name1, $name2) = explode(' ', $name);
                                                $name1 = '<span>'.$name1.'</span>';
                                                $name2 = '<span> '.$name2.'</span>';
                                                ?>
                                                    <? if($print_options['layout_style'] != 'event'): ?>
                                                        <a href="<?= $database_url ?>fields/<?= $field_info['field_token'] ?>/" class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= 'title="'.$field_info['field_name'].'"' ?>><?= $name1.$name2 ?></a>
                                                    <? else: ?>
                                                        <span class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= 'title="'.$field_info['field_name'].'"' ?>><?= $name ?></span>
                                                    <? endif; ?>
                                                <?
                                            }
                                        }
                                        // Otherwise, print an empty field
                                        else {
                                            ?>
                                                <span class="field_type">&hellip;</span>
                                            <?
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="full types">
                            <tbody>
                                <tr>
                                    <td class="right weaknesses" data-count="<?= count($robot_info['robot_weaknesses']) ?>">
                                        <label>Weaknesses :</label>
                                        <?
                                        if (!empty($robot_info['robot_weaknesses'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                                $name = $mmrpg_database_types[$robot_weakness]['type_name'];
                                                $name1 = '<span>'.substr($name, 0, 2).'</span>';
                                                $name2 = '<span>'.substr($name, 2).'</span>';
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_weakness.'/" class="robot_weakness robot_type type_'.$robot_weakness.'">'.$name1.$name2.'</a>'; }
                                                else { $temp_string[] = '<span class="robot_weakness robot_type type_'.$robot_weakness.'">'.$name.'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_weakness robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right resistances" data-count="<?= count($robot_info['robot_resistances']) ?>">
                                        <label>Resistances :</label>
                                        <?
                                        if (!empty($robot_info['robot_resistances'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                                $name = $mmrpg_database_types[$robot_resistance]['type_name'];
                                                $name1 = '<span>'.substr($name, 0, 2).'</span>';
                                                $name2 = '<span>'.substr($name, 2).'</span>';
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_resistance.'/" class="robot_resistance robot_type type_'.$robot_resistance.'">'.$name1.$name2.'</a>'; }
                                                else { $temp_string[] = '<span class="robot_resistance robot_type type_'.$robot_resistance.'">'.$name.'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_resistance robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right affinities" data-count="<?= count($robot_info['robot_affinities']) ?>">
                                        <label>Affinities :</label>
                                        <?
                                        if (!empty($robot_info['robot_affinities'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                                $name = $mmrpg_database_types[$robot_affinity]['type_name'];
                                                $name1 = '<span>'.substr($name, 0, 2).'</span>';
                                                $name2 = '<span>'.substr($name, 2).'</span>';
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_affinity.'/" class="robot_affinity robot_type type_'.$robot_affinity.'">'.$name1.$name2.'</a>'; }
                                                else { $temp_string[] = '<span class="robot_affinity robot_type type_'.$robot_affinity.'">'.$name.'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_affinity robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right immunities" data-count="<?= count($robot_info['robot_immunities']) ?>">
                                        <label>Immunities :</label>
                                        <?
                                        if (!empty($robot_info['robot_immunities'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                                $name = $mmrpg_database_types[$robot_immunity]['type_name'];
                                                $name1 = '<span>'.substr($name, 0, 2).'</span>';
                                                $name2 = '<span>'.substr($name, 2).'</span>';
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_immunity.'/" class="robot_immunity robot_type type_'.$robot_immunity.'">'.$name1.$name2.'</a>'; }
                                                else { $temp_string[] = '<span class="robot_immunity robot_type type_'.$robot_immunity.'">'.$name.'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_immunity robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="full stats">
                            <tbody>
                                <tr>
                                    <td  class="right">
                                        <label>Energy :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_energy" style="padding-left: <?= round( ( ($robot_info['robot_energy'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_energy'] ?></span></span>
                                            <? else: ?>
                                                <span class="robot_stat type_energy" style="padding-left: <?= round( ( ($robot_info['robot_energy'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_energy'] ?></span></span>
                                            <? endif; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Attack :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_attack" style="padding-left: <?= round( ( ($robot_info['robot_attack'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_attack'] ?></span></span>
                                            <? else: ?>
                                                <span class="robot_stat type_attack" style="padding-left: <?= round( ( ($robot_info['robot_attack'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_attack'] ?></span></span>
                                            <? endif; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Defense :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_defense" style="padding-left: <?= round( ( ($robot_info['robot_defense'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_defense'] ?></span></span>
                                            <? else: ?>
                                                <span class="robot_stat type_defense" style="padding-left: <?= round( ( ($robot_info['robot_defense'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_defense'] ?></span></span>
                                            <? endif; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Speed :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_speed" style="padding-left: <?= round( ( ($robot_info['robot_speed'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_speed'] ?></span></span>
                                            <? else: ?>
                                                <span class="robot_stat type_speed" style="padding-left: <?= round( ( ($robot_info['robot_speed'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_speed'] ?></span></span>
                                            <? endif; ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="full quote">
                            <tbody>
                                <? if($print_options['layout_style'] == 'event'): ?>

                                    <?
                                    // Define the search and replace arrays for the robot quotes
                                    $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
                                    $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
                                    ?>
                                    <tr>
                                        <td class="center" style="font-size: 13px; padding: 5px 0; ">
                                            <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>

                                <? endif; ?>
                            </tbody>
                        </table>

                    </div>

                <? endif; ?>

                <? if($print_options['layout_style'] == 'website'): ?>

                    <?
                    // Define the various tabs we are able to scroll to
                    $section_tabs = array();
                    if ($print_options['show_sprites']){ $section_tabs[] = array('sprites', 'Sprites', false); }
                    if ($print_options['show_quotes']){ $section_tabs[] = array('quotes', 'Quotes', false); }
                    if ($print_options['show_description']){ $section_tabs[] = array('description', 'Description', false); }
                    if ($print_options['show_abilities']){ $section_tabs[] = array('abilities', 'Abilities', false); }
                    if ($print_options['show_records']){ $section_tabs[] = array('records', 'Records', false); }
                    // Automatically mark the first element as true or active
                    $section_tabs[0][2] = true;
                    // Define the current URL for this robot or mecha page
                    $temp_url = 'database/';
                    if ($robot_info['robot_class'] == 'mecha'){ $temp_url .= 'mechas/'; }
                    elseif ($robot_info['robot_class'] == 'master'){ $temp_url .= 'robots/'; }
                    elseif ($robot_info['robot_class'] == 'boss'){ $temp_url .= 'bosses/'; }
                    $temp_url .= $robot_info['robot_token'].'/';
                    ?>

                    <div id="tabs" class="section_tabs">
                        <?
                        foreach($section_tabs AS $tab){
                            echo '<a class="link_inline link_'.$tab[0].'" href="'.$temp_url.'#'.$tab[0].'" data-anchor="#'.$tab[0].'"><span class="wrap">'.$tab[1].'</span></a>';
                        }
                        ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_sprites']): ?>

                    <?

                    // Start the output buffer and prepare to collect sprites
                    $this_sprite_markup = '';
                    if (true){

                        // Define the alts we'll be looping through for this robot
                        $temp_alts_array = array();
                        $temp_alts_array[] = array('token' => '', 'name' => $robot_info['robot_name'], 'summons' => 0);
                        // Append predefined alts automatically, based on the robot image alt array
                        if (!empty($robot_info['robot_image_alts'])){
                            $temp_alts_array = array_merge($temp_alts_array, $robot_info['robot_image_alts']);
                        }
                        // Otherwise, if this is a copy robot, append based on all the types in the index
                        elseif ($robot_info['robot_core'] == 'copy' && preg_match('/^(mega-man|proto-man|bass|doc-robot)$/i', $robot_info['robot_token'])){
                            foreach ($mmrpg_database_types AS $type_token => $type_info){
                                if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
                                $temp_alts_array[] = array('token' => $type_token, 'name' => $robot_info['robot_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
                            }
                        }
                        // Otherwise, if this robot has multiple sheets, add them as alt options
                        elseif (!empty($robot_info['robot_image_sheets'])){
                            for ($i = 2; $i <= $robot_info['robot_image_sheets']; $i++){
                                $temp_alts_array[] = array('sheet' => $i, 'name' => $robot_info['robot_name'].' (Sheet #'.$i.')', 'summons' => 0);
                            }
                        }

                        // Loop through sizes to show and generate markup
                        $show_sizes = array();
                        $base_size = $robot_image_size;
                        $zoom_size = $robot_image_size * 2;
                        $show_sizes[$base_size] = $base_size.'x'.$base_size;
                        $show_sizes[$zoom_size] = $zoom_size.'x'.$zoom_size;
                        $size_key = -1;
                        foreach ($show_sizes AS $size_value => $sprite_size_text){
                            $size_key++;
                            $size_is_final = $size_key == (count($show_sizes) - 1);

                            // Start the output buffer and prepare to collect sprites
                            ob_start();

                                // Loop through the alts and display images for them (yay!)
                                foreach ($temp_alts_array AS $alt_key => $alt_info){

                                    // Define the current image token with alt in mind
                                    $temp_robot_image_token = $robot_image_token;
                                    $temp_robot_image_token .= !empty($alt_info['token']) ? '_'.$alt_info['token'] : '';
                                    $temp_robot_image_token .= !empty($alt_info['sheet']) ? '-'.$alt_info['sheet'] : '';
                                    $temp_robot_image_name = $alt_info['name'];
                                    // Update the alt array with this info
                                    $temp_alts_array[$alt_key]['image'] = $temp_robot_image_token;

                                    // Collect the number of sheets
                                    $temp_sheet_number = !empty($robot_info['robot_image_sheets']) ? $robot_info['robot_image_sheets'] : 1;

                                    // Loop through the different frames and print out the sprite sheets
                                    foreach (array('right', 'left') AS $temp_direction){
                                        $temp_direction2 = substr($temp_direction, 0, 1);
                                        $temp_embed = '[robot:'.$temp_direction.']{'.$temp_robot_image_token.'}';
                                        $temp_title = $temp_robot_image_name.' | Mugshot Sprite '.ucfirst($temp_direction);
                                        $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                        $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                        $temp_label = 'Mugshot '.ucfirst(substr($temp_direction, 0, 1));
                                        echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_robot_image_token.'" data-frame="mugshot" style="'.($size_is_final ? 'padding-top: 20px;' : 'padding: 0;').' float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$size_value.'px; height: '.$size_value.'px; overflow: hidden;">';
                                            echo '<img class="has_pixels" style="margin-left: 0; height: '.$size_value.'px;" data-tooltip="'.$temp_title.'" src="images/robots/'.$temp_robot_image_token.'/mug_'.$temp_direction.'_'.$show_sizes[$base_size].'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                            if ($size_is_final){ echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>'; }
                                        echo '</div>';
                                    }


                                    // Loop through the different frames and print out the sprite sheets
                                    foreach ($robot_sprite_frames AS $this_key => $this_frame){
                                        $margin_left = ceil((0 - $this_key) * $size_value);
                                        $frame_relative = $this_frame;
                                        //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($robot_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                                        $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                                        foreach (array('right', 'left') AS $temp_direction){
                                            $temp_direction2 = substr($temp_direction, 0, 1);
                                            $temp_embed = '[robot:'.$temp_direction.':'.$frame_relative.']{'.$temp_robot_image_token.'}';
                                            $temp_title = $temp_robot_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                            $temp_imgalt = $temp_title;
                                            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                            $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                            //$image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
                                            //if ($temp_sheet > 1){ $temp_robot_image_token .= '-'.$temp_sheet; }
                                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_robot_image_token.'" data-frame="'.$frame_relative.'" style="'.($size_is_final ? 'padding-top: 20px;' : 'padding: 0;').' float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$size_value.'px; height: '.$size_value.'px; overflow: hidden;">';
                                                echo '<img class="has_pixels" style="margin-left: '.$margin_left.'px; height: '.$size_value.'px;" data-tooltip="'.$temp_title.'" alt="'.$temp_imgalt.'" src="images/robots/'.$temp_robot_image_token.'/sprite_'.$temp_direction.'_'.$sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                                if ($size_is_final){ echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>'; }
                                            echo '</div>';
                                        }
                                    }

                                }

                            // Collect the sprite markup from the output buffer for later
                            $this_sprite_markup .= '<div class="grid">'.ob_get_clean().'</div>'.PHP_EOL;

                        }


                    }

                    ?>

                    <h2 id="sprites" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left; overflow: hidden; height: auto;">
                        Sprite Sheets
                        <span class="header_links image_link_container">
                            <span class="images" style="<?= count($temp_alts_array) == 1 ? 'display: none;' : '' ?>"><?
                                // Loop though and print links for the alts
                                $alt_type_base = 'robot_type type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').' ';
                                foreach ($temp_alts_array AS $alt_key => $alt_info){
                                    $alt_type = '';
                                    $alt_style = '';
                                    $alt_title = $alt_info['name'];
                                    $alt_title_type = $alt_type_base;
                                    if (preg_match('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', $alt_info['name'])){
                                        $alt_type = strtolower(preg_replace('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', '$1', $alt_info['name']));
                                        $alt_name = '&bull;'; //ucfirst($alt_type); //substr(ucfirst($alt_type), 0, 2);
                                        $alt_title_type = 'robot_type type_'.$alt_type.' ';
                                        $alt_type = 'robot_type type_'.$alt_type.' core_type ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key == 0 ? $robot_info['robot_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'robot_type type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($robot_info['robot_core'] == 'copy' && $alt_key == 0){ $alt_type = 'robot_type type_empty '; }
                                    }

                                    echo '<a href="#" data-tooltip="'.$alt_title.'" data-tooltip-type="'.$alt_title_type.'" class="link link_image '.($alt_key == 0 ? 'link_active ' : '').'" data-image="'.$alt_info['image'].'">';
                                    echo '<span class="'.$alt_type.'" style="'.$alt_style.'">'.$alt_name.'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                            <span class="pipe" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>">|</span>
                            <span class="directions"><?
                                // Loop though and print links for the alts
                                foreach (array('right', 'left') AS $temp_key => $temp_direction){
                                    echo '<a href="#" data-tooltip="'.ucfirst($temp_direction).' Facing Sprites" data-tooltip-type="'.$alt_type_base.'" class="link link_direction '.($temp_key == 0 ? 'link_active' : '').'" data-direction="'.$temp_direction.'">';
                                    echo '<span class="ability_type ability_type_empty" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ">'.ucfirst($temp_direction).'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                        </span>
                    </h2>

                    <div id="sprites_body" class="body body_full sprites_body solid">
                        <?= $this_sprite_markup ?>
                        <?
                        // Define the editor title based on ID
                        $temp_editor_title = 'Undefined';
                        $temp_final_divider = '<span class="pipe"> | </span>';
                        $editor_ids = array();
                        if (!empty($robot_info['robot_image_editor'])){ $editor_ids[] = $robot_info['robot_image_editor']; }
                        if (!empty($robot_info['robot_image_editor2'])){ $editor_ids[] = $robot_info['robot_image_editor2']; }
                        if (!empty($editor_ids)){
                            $editor_ids_string = implode(', ', $editor_ids);
                            $temp_editor_details = $db->get_array_list("SELECT
                                user_name, user_name_public, user_name_clean
                                FROM mmrpg_users
                                WHERE user_id IN ({$editor_ids_string})
                                ORDER BY FIELD(user_id, {$editor_ids_string})
                                ;", 'user_name_clean');
                            $temp_editor_titles = array();
                            foreach ($temp_editor_details AS $editor_url => $editor_info){
                                $editor_name = !empty($editor_info['user_name_public']) ? $editor_info['user_name_public'] : $editor_info['user_name'];
                                if (!empty($editor_info['user_name_public'])
                                    && trim(str_replace(' ', '', $editor_info['user_name_public'])) !== trim(str_replace(' ', '', $editor_info['user_name']))
                                    ){
                                    $editor_name = $editor_info['user_name_public'].' / '.$editor_info['user_name'];
                                }
                                $temp_editor_titles[] = '<strong><a href="leaderboard/'.$editor_url.'/">'.$editor_name.'</a></strong>';
                            }
                            $temp_editor_title = implode(' and ', $temp_editor_titles);
                        }
                        $temp_is_capcom = true;
                        $temp_is_original = array('disco', 'rhythm', 'flutter-fly', 'flutter-fly-2', 'flutter-fly-3');
                        if (in_array($robot_info['robot_token'], $temp_is_original)){ $temp_is_capcom = false; }
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

                <? if($print_options['show_quotes']): ?>

                    <h2 id="quotes" class="header <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        Battle Quotes
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <?
                        // Define the search and replace arrays for the robot quotes
                        $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
                        $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
                        ?>
                        <table class="full quotes">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label>Start Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_start']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_start']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Taunt Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Victory Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_victory']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_victory']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Defeat Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_defeat']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_defeat']) : '&hellip;' ?>&quot;</span>
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

                <? if($print_options['show_description'] && !empty($robot_info['robot_description2'])): ?>

                    <h2 id="description" class="header <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left; ">
                        Description Text
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 0 0 2px; min-height: 10px;">
                        <table class="full description">
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="robot_description" style="text-align: left; padding: 0 4px;"><?= $robot_info['robot_description2'] ?></div>
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

                    <h2 id="abilities" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        Ability Compatibility
                    </h2>
                    <div class="body body_full solid" style="margin: 0 auto 4px; padding: 2px 3px; min-height: 10px;">
                        <table class="full abilities" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="ability_container">
                                        <?

                                        // Define the robot ability class and collect the cores for testing
                                        $robot_ability_class = !empty($robot_info['robot_class']) ? $robot_info['robot_class'] : 'master';
                                        $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
                                        $robot_ability_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : false;
                                        $robot_ability_list = !empty($robot_info['robot_abilities']) ? $robot_info['robot_abilities'] : array();
                                        $robot_ability_rewards = !empty($robot_info['robot_rewards']['abilities']) ? $robot_info['robot_rewards']['abilities'] : array();

                                        // Manually add any global abilities to this robot's list
                                        $global_abilities = rpg_ability::get_global_abilities();
                                        $robot_ability_list = array_diff($robot_ability_list, $global_abilities);
                                        $robot_ability_list = array_merge($robot_ability_list, $global_abilities);

                                        // Manually add any level-up abilities to this robot's list
                                        foreach ($robot_ability_rewards AS $info){ $robot_ability_list[] = $info['token']; }

                                        // Make sure only unique abilities are included
                                        $robot_ability_list = array_unique($robot_ability_list);

                                        //echo('<pre>$global_abilities = '.print_r($global_abilities, true).'</pre>');
                                        //echo('<pre>$robot_ability_rewards = '.print_r($robot_ability_rewards, true).'</pre>');
                                        //echo('<pre>$robot_ability_list = '.print_r($robot_ability_list, true).'</pre>');
                                        //exit();

                                        // Collect a FULL list of abilities for display
                                        $temp_required = array();
                                        foreach ($robot_ability_rewards AS $info){ $temp_required[] = $info['token']; }
                                        $temp_abilities_index = rpg_ability::get_index(false, false, '', $temp_required);

                                        // Clone abilities into new array for filtering
                                        $new_ability_rewards = array();
                                        foreach ($robot_ability_rewards AS $this_info){
                                            $new_ability_rewards[$this_info['token']] = $this_info;
                                        }
                                        $robot_copy_program = $robot_ability_core == 'copy' || $robot_ability_core2 == 'copy' ? true : false;
                                        //if ($robot_copy_program){ $robot_ability_list = $temp_all_ability_tokens; }
                                        $robot_ability_core_list = array();
                                        if ((!empty($robot_ability_core) || !empty($robot_ability_core2))
                                            && $robot_ability_class != 'mecha'){ // only robot masters can core match abilities
                                            foreach ($temp_abilities_index AS $token => $info){
                                                if (
                                                    (!empty($info['ability_type']) && ($robot_copy_program || $info['ability_type'] == $robot_ability_core || $info['ability_type'] == $robot_ability_core2)) ||
                                                    (!empty($info['ability_type2']) && ($info['ability_type2'] == $robot_ability_core || $info['ability_type2'] == $robot_ability_core2))
                                                    ){
                                                    $robot_ability_list[] = $info['ability_token'];
                                                    $robot_ability_core_list[] = $info['ability_token'];
                                                }
                                            }
                                        }
                                        foreach ($robot_ability_list AS $this_token){
                                            if ($this_token == '*'){ continue; }
                                            if (!isset($new_ability_rewards[$this_token])){
                                                if (in_array($this_token, $robot_ability_core_list)){ $new_ability_rewards[$this_token] = array('level' => 'Core', 'token' => $this_token); }
                                                else { $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }

                                            }
                                        }
                                        $robot_ability_rewards = $new_ability_rewards;

                                        //echo('<pre>$global_abilities = '.print_r($global_abilities, true).'</pre>');
                                        //echo('<pre>$robot_ability_rewards = '.print_r($robot_ability_rewards, true).'</pre>');
                                        //exit();

                                        if (!empty($robot_ability_rewards)){
                                            $temp_string = array();
                                            $ability_key = 0;
                                            $ability_method_key = 0;
                                            $ability_method = '';
                                            $method_order = array('level', 'core', 'player');
                                            foreach ($method_order AS $current_method){
                                                foreach ($robot_ability_rewards AS $this_info){
                                                    if (!isset($temp_abilities_index[$this_info['token']])){ continue; }
                                                    $this_level = $this_info['level'];
                                                    $this_ability_method = !is_numeric($this_level) ? strtolower($this_level) : 'level';
                                                    if ($this_ability_method != $current_method){ continue; }
                                                    $this_ability = $temp_abilities_index[$this_info['token']];
                                                    $this_ability_token = $this_ability['ability_token'];
                                                    $this_ability_name = $this_ability['ability_name'];
                                                    $this_ability_class = !empty($this_ability['ability_class']) ? $this_ability['ability_class'] : 'master';
                                                    $this_ability_image = !empty($this_ability['ability_image']) ? $this_ability['ability_image']: $this_ability['ability_token'];
                                                    $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                                    $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                                                    if (!empty($this_ability_type) && !empty($mmrpg_database_types[$this_ability_type])){ $this_ability_type = $mmrpg_database_types[$this_ability_type]['type_name'].' Type'; }
                                                    else { $this_ability_type = ''; }
                                                    if (!empty($this_ability_type2) && !empty($mmrpg_database_types[$this_ability_type2])){ $this_ability_type = str_replace('Type', '/ '.$mmrpg_database_types[$this_ability_type2]['type_name'], $this_ability_type); }
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
                                                    $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                                                    $this_ability_description = str_replace('{DAMAGE}', $this_ability_damage, $this_ability_description);
                                                    $this_ability_description = str_replace('{RECOVERY}', $this_ability_recovery, $this_ability_description);
                                                    $this_ability_description = str_replace('{DAMAGE2}', $this_ability_damage2, $this_ability_description);
                                                    $this_ability_description = str_replace('{RECOVERY2}', $this_ability_recovery2, $this_ability_description);
                                                    //$this_ability_title_plain = $this_ability_name;
                                                    //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                                                    //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                                                    //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                                                    //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                                                    //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                                                    $this_ability_title_plain = rpg_ability::print_editor_title_markup($robot_info, $this_ability);
                                                    $this_ability_method_text = 'Level Up';
                                                    $this_ability_title_html = '<strong class="name">'.$this_ability_name.'</strong>';
                                                    if ($this_ability_method == 'level'){
                                                        $this_ability_method_text = 'Level Up';
                                                        if ($this_level > 1){ $this_ability_title_html .= '<span class="level">Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).'</span>'; }
                                                        else { $this_ability_title_html .= '<span class="level">Start</span>'; }
                                                    } elseif ($this_ability_method == 'player'){
                                                        $this_ability_method_text = 'Player Only';
                                                        $this_ability_title_html .= '<span class="level">&nbsp;</span>';

                                                    } elseif ($this_ability_method == 'core'){
                                                        $this_ability_method_text = 'Core Match';
                                                        $this_ability_title_html .= '<span class="level">&nbsp;</span>';

                                                    }

                                                    // If this is a boss, don't bother showing player or core match abilities
                                                    if ($this_ability_method != 'level' && $robot_info['robot_class'] == 'boss'){ continue; }

                                                    if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                                                    if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                                                    if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                                                    if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                                                    $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                                                    if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_image = 'ability'; $this_ability_sprite_path = 'images/abilities/ability/icon_left_40x40.png'; }
                                                    else { $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png'; }
                                                    $this_ability_sprite_html = '<span class="icon"><img src="'.$this_ability_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_ability_name.' Icon" /></span>';
                                                    $this_ability_title_html = '<span class="label">'.$this_ability_title_html.'</span>';
                                                    //$this_ability_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_ability_title_html;

                                                    // Show the ability method separator if necessary
                                                    if ($ability_method != $this_ability_method && $robot_info['robot_class'] == 'master'){
                                                        $temp_separator = '<div class="ability_separator">'.$this_ability_method_text.'</div>';
                                                        $temp_string[] = $temp_separator;
                                                        $ability_method = $this_ability_method;
                                                        $ability_method_key++;
                                                        // Print out the disclaimer if a copy-core robot
                                                        if ($this_ability_method != 'level' && $robot_copy_program){
                                                            $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">Copy Core robots can equip <em>any</em> '.($this_ability_method == 'player' ? 'player' : 'type').' ability!</div>';
                                                        }
                                                    }
                                                    // If this is a copy core robot, don't bother showing EVERY core-match ability
                                                    if ($this_ability_method != 'level' && $robot_copy_program){ continue; }
                                                    // Only show if this ability is greater than level 0 OR it's not copy core (?)
                                                    elseif ($this_level >= 0 || !$robot_copy_program){
                                                        $temp_element = $this_ability_class == 'master' ? 'a' : 'span';
                                                        $temp_markup = '<'.$temp_element.' '.($this_ability_class == 'master' ? 'href="'.MMRPG_CONFIG_ROOTURL.'database/abilities/'.$this_ability['ability_token'].'/"' : '').' class="ability_name ability_class_'.$this_ability_class.' ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'" title="'.$this_ability_title_plain.'" style="'.($this_ability_image == 'ability' ? 'opacity: 0.3; ' : '').'">';
                                                        $temp_markup .= '<span class="chrome">'.$this_ability_sprite_html.$this_ability_title_html.'</span>';
                                                        $temp_markup .= '</'.$temp_element.'>';
                                                        $temp_string[] = $temp_markup;
                                                        $ability_key++;
                                                        continue;
                                                    }
                                                }

                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_ability type_none"><span class="chrome">None</span></span>';
                                        }
                                        ?>
                                        </div>
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

                <? if($print_options['show_records']): ?>

                    <h2 id="records" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        Community Records
                    </h2>
                    <div class="body body_full" style="margin: 0 auto 5px; padding: 0 0 5px; min-height: 10px;">
                        <table class="full records">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label>Encountered : </label>
                                        <span class="robot_record"><?= $temp_robot_records['robot_encountered'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_encountered'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Scanned : </label>
                                        <span class="robot_record"><?= $temp_robot_records['robot_scanned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_scanned'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Defeated : </label>
                                        <span class="robot_record"><?= $temp_robot_records['robot_defeated'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_defeated'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <? if (
                                    ($robot_info['robot_class'] == 'mecha')
                                    || ($robot_info['robot_class'] == 'master' && !empty($robot_info['robot_flag_unlockable']))
                                    ){ ?>
                                    <tr>
                                        <td class="right">
                                            <label>Summoned : </label>
                                            <span class="robot_record"><?= $temp_robot_records['robot_summoned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_summoned'], 0, '.', ',').' Times' ?></span>
                                        </td>
                                    </tr>
                                <? } ?>
                                <? if (
                                    $robot_info['robot_class'] == 'master'
                                    && !empty($robot_info['robot_flag_unlockable'])
                                    ){ ?>
                                    <tr>
                                        <td class="right">
                                            <label>Unlocked By : </label>
                                            <span class="robot_record"><?= $temp_robot_records['robot_unlocked'] == 1 ? '1 Player' : number_format($temp_robot_records['robot_unlocked'], 0, '.', ',').' Players' ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label>Avatar Of : </label>
                                            <span class="robot_record"><?= $temp_robot_records['robot_avatars'] == 1 ? '1 Player' : number_format($temp_robot_records['robot_avatars'], 0, '.', ',').' Players' ?></span>
                                        </td>
                                    </tr>
                                <? } ?>
                            </tbody>
                        </table>
                    </div>

                    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        </div>
                    <? endif; ?>

                <? endif; ?>

                <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <div class="link_wrapper">
                        <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    </div>
                    <span class="link_container">
                        <?= !empty($compact_footer_link_markup) ? implode("\n", $compact_footer_link_markup) : ''  ?>
                    </span>

                <? endif; ?>

            </div>
        </div>
        <?
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

    // Define a static function for printing out the robot's editor markup
    public static function print_editor_markup($player_info, $robot_info, $mmrpg_database_abilities = array()){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup;
        global $mmrpg_database_abilities;
        global $session_token;

        // Collect values for potentially missing global variables
        if (!isset($session_token)){ $session_token = rpg_game::session_token(); }

        // If either fo empty, return error
        if (empty($player_info)){ return 'error:player-empty'; }
        if (empty($robot_info)){ return 'error:robot-empty'; }

        // Collect the approriate database indexes
        if (empty($mmrpg_database_players)){ $mmrpg_database_players = rpg_player::get_index(true); }
        if (empty($mmrpg_database_abilities)){ $mmrpg_database_abilities = rpg_ability::get_index(true); }
        if (empty($mmrpg_database_items)){ $mmrpg_database_items = rpg_item::get_index(true); }
        if (empty($mmrpg_database_fields)){ $mmrpg_database_fields = rpg_field::get_index(true); }
        if (empty($mmrpg_database_types)){ $mmrpg_database_types = rpg_type::get_index(); }

        // Define the quick-access variables for later use
        $player_token = $player_info['player_token'];
        $robot_token = $robot_info['robot_token'];
        if (!isset($first_robot_token)){ $first_robot_token = $robot_token; }

        // Start the output buffer
        ob_start();

            // Check how many robots this player has and see if they should be able to transfer
            $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
            $counter_player_missions = rpg_prototype::battles_complete($player_info['player_token']);
            $allow_player_selector = $allowed_edit_player_count > 1 ? true : false;

            // Collect starforce values for the current player
            $player_starforce = rpg_game::starforce_unlocked();

            // Update the robot key to the current counter
            $robot_key = $key_counter;
            // Make a backup of the player selector
            $allow_player_selector_backup = $allow_player_selector;
            // Collect or define the image size
            $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
            $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
            $robot_image_size_text = $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'];
            $robot_image_offset_top = -1 * $robot_image_offset;
            // Collect the robot level and experience
            $robot_info['robot_level'] = rpg_game::robot_level($player_info['player_token'], $robot_info['robot_token']);
            $robot_info['robot_experience'] = rpg_game::robot_experience($player_info['player_token'], $robot_info['robot_token']);
            // Collect the rewards for this robot
            $robot_rewards = rpg_game::robot_rewards($player_token, $robot_token);
            // Collect the settings for this robot
            $robot_settings = rpg_game::robot_settings($player_token, $robot_token);
            // Collect the database for this robot
            $robot_database = !empty($player_robot_database[$robot_token]) ? $player_robot_database[$robot_token] : array(); //rpg_game::robot_database($robot_token);
            // Collect the robot ability core if it exists
            $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
            // Collect the stat details for this robot
            $robot_stats = rpg_robot::calculate_stat_values($robot_info['robot_level'], $robot_info, $robot_rewards, true, $robot_ability_core, $player_starforce);
            // Check if this robot has the copy shot ability
            $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

            // Loop through and update this robot's stats with calculated values
            $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
            foreach ($stat_tokens As $stat_token){
                // Update this robot's stat with the calculated current totals
                $robot_info['robot_'.$stat_token] = $robot_stats[$stat_token]['current'];
                $robot_info['robot_'.$stat_token.'_base'] = $robot_stats[$stat_token]['current_noboost'];
                $robot_info['robot_'.$stat_token.'_rewards'] = $robot_stats[$stat_token]['bonus'];
                if (!empty($player_info['player_'.$stat_token])){
                    $robot_stats[$stat_token]['player'] = ceil($robot_info['robot_'.$stat_token] * ($player_info['player_'.$stat_token] / 100));
                    $robot_info['robot_'.$stat_token.'_player'] = $robot_stats[$stat_token]['player'];
                    $robot_info['robot_'.$stat_token] += $robot_stats[$stat_token]['player'];
                }
            }

            // Define a temp function for printing out robot stat blocks
            $maxed_robot_stats = 0;
            $print_robot_stat_function = function($stat_token) use($robot_info, $robot_stats, $player_info){

                $level_max = $robot_stats['level'] >= 100 ? true : false;
                $is_maxed = $robot_stats[$stat_token]['bonus'] >= $robot_stats[$stat_token]['bonus_max'] ? true : false;

                $base_text = '';
                $base_text .= 'Base '.ucfirst($stat_token).' <br /> <span style="font-size: 10px">'.
                    number_format($robot_stats[$stat_token]['base'], 0, '.', ',').
                    ' <span style="font-size:9px">@</span> '.($stat_token != 'weapons' ? 'Lv. '.$robot_stats['level'] : 'Any Lv.').' = '.
                    number_format($robot_stats[$stat_token]['current_noboost'], 0, '.', ',').
                    '</span>';
                $base_text = htmlentities($base_text, ENT_QUOTES, 'UTF-8', true);

                $robot_bonus_text = '';
                if (!empty($robot_stats[$stat_token]['bonus'])){
                    $robot_bonus_text .= 'Knockout Bonuses <br /> <span style="font-size: 10px">+ '.
                        number_format($robot_stats[$stat_token]['bonus'], 0, '.', ',').' = '.
                        number_format((
                            $robot_stats[$stat_token]['current_noboost'] +
                            $robot_stats[$stat_token]['bonus']
                            ), 0, '.', ',').
                        '</span>';
                    $robot_bonus_text = htmlentities($robot_bonus_text, ENT_QUOTES, 'UTF-8', true);
                }

                $starforce_bonus_text = '';
                if (!empty($robot_stats[$stat_token]['starforce'])){
                    $starforce_bonus_text .= 'Starforce Boost <br /> <span style="font-size: 10px">+ '.
                        number_format($robot_stats[$stat_token]['starforce'], 0, '.', ',').' = '.
                        number_format((
                            $robot_stats[$stat_token]['current_noboost'] +
                            $robot_stats[$stat_token]['bonus'] +
                            $robot_stats[$stat_token]['starforce']
                            ), 0, '.', ',').
                        '</span>';
                    $starforce_bonus_text = htmlentities($starforce_bonus_text, ENT_QUOTES, 'UTF-8', true);
                }

                $player_bonus_text = '';
                if (!empty($robot_stats[$stat_token]['player'])){
                    $player_bonus_text .= 'Player Bonuses <br /> <span style="font-size: 10px">'.
                        ' +'.$player_info['player_'.$stat_token].'% = '.
                        number_format((
                            $robot_stats[$stat_token]['current_noboost'] +
                            $robot_stats[$stat_token]['bonus'] +
                            $robot_stats[$stat_token]['starforce'] +
                            $robot_stats[$stat_token]['player']
                            ), 0, '.', ',').
                        '</span>';
                    $player_bonus_text = htmlentities($player_bonus_text, ENT_QUOTES, 'UTF-8', true);
                }

                //if ($stat_token == 'energy' || $stat_token == 'weapons'){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
                if ($level_max && $is_maxed){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
                else { echo '<span class="robot_stat"> '; }

                    // If the this stat has any boosts, show them
                    if (
                        !empty($robot_stats[$stat_token]['bonus'])
                        || !empty($robot_stats[$stat_token]['starforce'])
                        || !empty($robot_stats[$stat_token]['player'])
                        ){

                        echo '<span class="details">';


                            echo '<span data-tooltip="'.$base_text.'" data-tooltip-type="robot_type robot_type_none">'.$robot_stats[$stat_token]['current_noboost'].'</span> ';


                            if (!empty($robot_stats[$stat_token]['bonus'])){
                                echo '+ <span data-tooltip="'.$robot_bonus_text.'" class="statboost_robot" data-tooltip-type="robot_stat robot_type_none">'.$robot_stats[$stat_token]['bonus'].'</span> ';
                            }

                            if (!empty($robot_stats[$stat_token]['starforce'])){
                                echo '+ <span data-tooltip="'.$starforce_bonus_text.'" class="statboost_force" data-tooltip-type="robot_stat robot_type_none">'.$robot_stats[$stat_token]['starforce'].'</span> ';
                            }

                            if (!empty($robot_stats[$stat_token]['player'])){
                                echo '+ <span data-tooltip="'.$player_bonus_text.'" class="statboost_player_'.$player_info['player_token'].'" data-tooltip-type="robot_stat robot_type_'.$stat_token.'">'.$robot_stats[$stat_token]['player'].'</span> ';
                            }

                        echo ' = </span>';

                        echo '<span class="total">';
                            echo $robot_info['robot_'.$stat_token];
                            /*
                            echo preg_replace('/^(0+)/', '<span class="numpad">$1</span>', str_pad($robot_info['robot_'.$stat_token], 4, '0', STR_PAD_LEFT));
                            if ($stat_token != 'energy' && $stat_token != 'weapons'){
                                echo preg_replace('/^(0+)/', '<span class="numpad">$1</span>', str_pad($robot_info['robot_'.$stat_token], 4, '0', STR_PAD_LEFT));
                            } else {
                                echo $robot_info['robot_'.$stat_token];
                            }
                            */
                        echo '</span>';

                    }
                    // Otherwise display as one block
                    else {

                        echo '<span class="total" data-tooltip="'.$base_text.'">';
                            echo $robot_info['robot_'.$stat_token];
                        echo '</span>';

                    }

                    if ($stat_token == 'energy'){ echo '<span class="unit"> LE</span>'; }
                    elseif ($stat_token == 'weapons'){ echo '<span class="unit"> WE</span>'; }
                    elseif ($stat_token == 'attack'){ echo '<span class="unit"> AT</span>'; }
                    elseif ($stat_token == 'defense'){ echo '<span class="unit"> DF</span>'; }
                    elseif ($stat_token == 'speed'){ echo '<span class="unit"> SP</span>'; }

                echo '</span>'."\n";


                // Return positive increment if maxed
                if ($level_max && $is_maxed && !empty($robot_stats[$stat_token]['bonus'])){ return 1; }
                else { return 0; }

                };

            // Collect this robot's ability rewards and add them to the dropdown
            $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
            $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
            foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }

            // Collect the summon count from the session if it exists
            $robot_info['robot_summoned'] = !empty($robot_database['robot_summoned']) ? $robot_database['robot_summoned'] : 0;

            // Collect any manually unlocked alts from the session if exists
            $robot_info['robot_altimages'] = mmrpg_prototype_altimage_unlocked($robot_token);

            // Collect the alt images if there are any that are unlocked
            $robot_alt_count = 1 + (!empty($robot_info['robot_image_alts']) ? count($robot_info['robot_image_alts']) : 0);
            $robot_alt_options = array();
            if (!empty($robot_info['robot_image_alts'])){
                foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                    $is_unlocked = false;
                    if (in_array($alt_info['token'], $robot_info['robot_altimages'])){ $is_unlocked = true; }
                    elseif ($robot_info['robot_summoned'] >= $alt_info['summons']){ $is_unlocked = true; $robot_info['robot_altimages'][] = $alt_info['token']; }
                    if (!$is_unlocked){ continue; }
                    $robot_alt_options[] = $alt_info['token'];
                }
            }

            // Collect the current unlock image token for this robot
            $robot_image_unlock_current = 'base';
            if (!empty($robot_settings['robot_image']) && strstr($robot_settings['robot_image'], '_')){
                list($token, $robot_image_unlock_current) = explode('_', $robot_settings['robot_image']);
            }

            // Define the offsets for the image tokens based on count
            $token_first_offset = 2;
            $token_other_offset = 6;
            if ($robot_alt_count == 1){ $token_first_offset = 17; }
            elseif ($robot_alt_count == 3){ $token_first_offset = 10; }

            // Loop through and generate the robot image display token markup
            $robot_image_unlock_tokens = '';
            $temp_total_alts_count = 0;
            $max_alt_slots = 12;
            $break_after_slot = 6;
            for ($i = 0; $i < $max_alt_slots; $i++){
                $temp_enabled = true;
                $temp_active = false;
                if ($i + 1 > $robot_alt_count){ break; }
                if ($i > 0 && !isset($robot_alt_options[$i - 1])){ $temp_enabled = false; }
                if ($temp_enabled && $i == 0 && $robot_image_unlock_current == 'base'){ $temp_active = true; }
                elseif ($temp_enabled && $i >= 1 && $robot_image_unlock_current == $robot_alt_options[$i - 1]){ $temp_active = true; }
                $rel_i = ($i >= $break_after_slot) ? ($i - $break_after_slot) : $i;
                $left_offset = ($token_first_offset + ($rel_i * $token_other_offset));
                $bottom_offset = ($i >= $break_after_slot) ? -17 : -12;
                $robot_image_unlock_tokens .= '<span class="token token_'.($temp_enabled ? 'enabled' : 'disabled').' '.($temp_active ? 'token_active' : '').'" style="left: '.$left_offset.'px; bottom: '.$bottom_offset.'px;">&bull;</span>';
                $temp_total_alts_count += 1;
            }
            $temp_unlocked_alts_count = count($robot_alt_options) + 1;
            $temp_image_alt_title = '';
            if ($temp_total_alts_count > 1){
                $temp_image_alt_title = '<strong>'.$temp_unlocked_alts_count.' / '.$robot_alt_count.' Outfits Unlocked</strong><br />';
                //$temp_image_alt_title .= '<span style="font-size: 90%;">';
                    $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$robot_info['robot_name'].'</span><br />';
                    foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                        if (
                            ($robot_info['robot_summoned'] >= $alt_info['summons']) ||
                            (in_array($alt_info['token'], $robot_info['robot_altimages']))
                            ){
                            $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$alt_info['name'].'</span><br />';
                        } else {
                            $temp_image_alt_title .= '&#9702; <span style="font-size: 90%;">???</span><br />';
                        }
                    }
                //$temp_image_alt_title .= '</span>';
                $temp_image_alt_title = htmlentities($temp_image_alt_title, ENT_QUOTES, 'UTF-8', true);
            }

            // Define whether or not this robot has coreswap enabled
            $temp_allow_coreswap = $robot_info['robot_level'] >= 100 ? true : false;

            //echo $robot_info['robot_token'].' robot_image_unlock_current = '.$robot_image_unlock_current.' | robot_alt_options = '.implode(',',array_keys($robot_alt_options)).'<br />';

            ?>
            <div class="event event_double event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?> <?= false && $robot_info['robot_level'] >= 100 && $robot_info['robot_core'] != 'copy' ? 'event_has_subcore' : '' ?>" data-token="<?= $player_info['player_token'].'_'.$robot_info['robot_token']?>" data-player="<?= $player_info['player_token'] ?>" data-robot="<?= $robot_info['robot_token'] ?>" data-types="<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>">

                <div class="this_sprite sprite_left event_robot_mugshot" style="">
                    <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                    <div class="sprite_wrapper robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="width: 33px;">
                        <div class="sprite_wrapper robot_type robot_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px;"></div>
                        <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mug_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_images" style="">
                    <? if($global_allow_editing && !empty($robot_alt_options)): ?>
                        <a class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </a>
                    <? else: ?>
                        <span class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </span>
                    <? endif; ?>
                </div>

                <div class="this_sprite sprite_left event_robot_summons" style="">
                    <div class="robot_summons">
                        <span class="summons_count"><?= $robot_info['robot_summoned'] ?></span>
                        <span class="summons_label"><?= $robot_info['robot_summoned'] == 1 ? 'Summon' : 'Summons' ?></span>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_favourite" style="" >
                    <? if($global_allow_editing): ?>
                        <a class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Toggle Favourite?">&hearts;</a>
                    <? else: ?>
                        <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</span>
                    <? endif; ?>
                </div>

                <?

                // Define the placehodler cells for the empty column in case it's needed
                ob_start();
                ?>
                <td class="right">
                    <label style="display: block; float: left; color: #696969;">??? :</label>
                    <span class="robot_stat" style="color: #696969; font-weight: normal;">???</span>
                </td>
                <?
                $empty_column_placeholder = ob_get_clean();

                // Define an array to hold all the data in the left and right columns
                $left_column_markup = array();
                $right_column_markup = array();

                // Check to see if the player has unlocked the ability to swap players
                $temp_player_swap_unlocked = mmrpg_prototype_item_unlocked('wily-program'); // && rpg_prototype::event_unlocked('dr-wily', 'chapter_one_complete');
                // If this player has unlocked the ability to let robots swap players
                if ($temp_player_swap_unlocked){
                    ob_start();
                    ?>
                    <td class="player_select_block right">
                        <?
                        $player_style = '';
                        $robot_info['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : $player_info['player_token'];
                        if ($player_info['player_token'] != $robot_info['original_player']){
                            if ($counter_player_robots > 1){ $allow_player_selector = true; }
                        }
                        ?>
                        <? if($robot_info['original_player'] != $player_info['player_token']): ?>
                            <label title="<?= 'Transferred from Dr. '.ucfirst(str_replace('dr-', '', $robot_info['original_player'])) ?>"  class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                        <? else: ?>
                            <label class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                        <? endif; ?>

                        <?if($global_allow_editing && $allow_player_selector):?>
                            <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>"><label style="background-image: url(images/players/<?= $player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $player_info['player_name']?><span class="arrow">&#8711;</span></label></a>
                        <?elseif(!$global_allow_editing && $allow_player_selector):?>
                            <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="cursor: default; "><label style="background-image: url(images/players/<?= $player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); cursor: default; "><?= $player_info['player_name']?></label></a>
                        <?else:?>
                            <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(images/players/<?= $player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $player_info['player_name']?></label></a>
                        <?endif;?>
                    </td>
                    <?
                    $left_column_markup[] = ob_get_clean();
                }

                // Check to see if the player has unlocked the item to hold items
                $temp_item_hold_unlocked = mmrpg_prototype_item_unlocked('equip-codes');
                $current_item_token = '';
                // If this player has unlocked the item to let robots hold items
                if ($temp_item_hold_unlocked){
                    // Collect the currently held item and token, if available
                    $current_item_token = !empty($robot_info['robot_item']) ? $robot_info['robot_item'] : '';
                    $current_item_info = !empty($mmrpg_database_items[$current_item_token]) ? $mmrpg_database_items[$current_item_token] : array();
                    $current_item_name = !empty($current_item_info['item_name']) ? $current_item_info['item_name'] : 'No Item';
                    $current_item_image = !empty($current_item_info['item_image']) ? $current_item_info['item_image'] : $current_item_token;
                    $current_item_type = !empty($current_item_info['item_type']) ? $current_item_info['item_type'] : 'none';
                    if (!empty($current_item_info['item_type2'])){ $current_item_type = $current_item_type != 'none' ?  $current_item_type.'_'.$current_item_info['item_type2'] : $current_item_info['item_type2']; }
                    if (empty($current_item_info)){ $current_item_token = ''; $current_item_image = 'item'; }
                    $current_date_attr = '';
                    if (!empty($current_item_info)){
                        $title_markup = rpg_item::print_editor_title_markup($robot_info, $current_item_info, array());
                        $title_markup_encoded = htmlentities($title_markup, ENT_QUOTES, 'UTF-8', true);
                        $current_date_attr .= 'data-id="'.$current_item_info['item_id'].'" ';
                        $current_date_attr .= 'data-item="'.$current_item_info['item_token'].'" ';
                        $current_date_attr .= 'data-type="'.$current_item_info['item_type'].'" ';
                        $current_date_attr .= 'data-type2="'.$current_item_info['item_type2'].'" ';
                        $current_date_attr .= 'data-tooltip="'.$title_markup_encoded.'" ';
                    } else {
                        $current_date_attr .= 'data-id="" ';
                        $current_date_attr .= 'data-item="" ';
                        $current_date_attr .= 'data-type="" ';
                        $current_date_attr .= 'data-type2="" ';
                    }
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Item :</label>
                        <? if($global_allow_editing): ?>
                            <a title="Change Item?" class="item_name type <?= $current_item_type ?>" <?= $current_date_attr ?>><label style="background-image: url(images/items/<?= $current_item_image ?>/icon_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $current_item_name ?><span class="arrow">&#8711;</span></label></a>
                        <? else: ?>
                            <a class="item_name type <?= $current_item_type ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(images/items/<?= $current_item_image ?>/icon_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $current_item_name ?></label></a>
                        <? endif; ?>
                    </td>
                    <?
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the weakness
                if (true){
                    ob_start();
                    ?>
                    <td  class="right weaknesses" data-count="<?= count($robot_info['robot_weaknesses']) ?>">
                        <label style="display: block; float: left;">Weaknesses :</label>
                        <?
                        if (!empty($robot_info['robot_weaknesses'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.(!empty($robot_weakness) ? $robot_weakness : 'none').'">'.$mmrpg_database_types[$robot_weakness]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                        } else {
                            echo '<span class="robot_weakness">None</span>';
                        }
                        ?>
                    </td>
                    <?
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the resistance
                if (true){
                    ob_start();
                    ?>
                    <td class="right resistances" data-count="<?= count($robot_info['robot_resistances']) ?>">
                        <label style="display: block; float: left;">Resistances :</label>
                        <?
                        if (!empty($robot_info['robot_resistances'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.(!empty($robot_resistance) ? $robot_resistance : 'none').'">'.$mmrpg_database_types[$robot_resistance]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                        } else {
                            echo '<span class="robot_resistance">None</span>';
                        }
                        ?>
                    </td>
                    <?
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the affinity
                if (true){
                    ob_start();
                    ?>
                    <td  class="right affinities" data-count="<?= count($robot_info['robot_affinities']) ?>">
                        <label style="display: block; float: left;">Affinities :</label>
                        <?
                        if (!empty($robot_info['robot_affinities'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.(!empty($robot_affinity) ? $robot_affinity : 'none').'">'.$mmrpg_database_types[$robot_affinity]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                        } else {
                            echo '<span class="robot_affinity">None</span>';
                        }
                        ?>
                    </td>
                    <?
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the immunity
                if (true){
                    ob_start();
                    ?>
                    <td class="right immunities" data-count="<?= count($robot_info['robot_immunities']) ?>">
                        <label style="display: block; float: left;">Immunities :</label>
                        <?
                        if (!empty($robot_info['robot_immunities'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.(!empty($robot_immunity) ? $robot_immunity : 'none').'">'.$mmrpg_database_types[$robot_immunity]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                        } else {
                            echo '<span class="robot_immunity">None</span>';
                        }
                        ?>
                    </td>
                    <?
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the level
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Level :</label>
                        <? if($robot_info['robot_level'] >= 100){ ?>
                            <a class="robot_stat robot_type_electric" title="Max Level!">
                                <span class="unit">Lv.</span>
                                <?= $robot_info['robot_level'] ?>
                            </a>
                        <? } else { ?>
                            <span
                                class="robot_stat">
                                <span class="unit">Lv.</span>
                                <?= $robot_info['robot_level'] ?>
                            </span>
                        <? } ?>
                        &nbsp;
                        <? if($robot_info['robot_level'] >= 100): ?>
                            <span class="robot_stat robot_type_experience" title="Max Experience!">
                                <span class="details">
                                    <span>&#8734;</span> / 1000
                                </span>
                                <span class="unit">Exp</span>
                            </span>
                        <? else: ?>
                            <span class="robot_stat">
                                <span class="details">
                                    <?= $robot_info['robot_experience'] ?> / 1000
                                </span>
                                <span class="unit">Exp</span>
                            </span>
                        <? endif; ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                /*

                // Define the markup for the level
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Level :</label>
                        <? if($robot_info['robot_level'] >= 100){ ?>
                            <a data-tooltip-align="center" data-tooltip="<?= htmlentities(('Congratulations! '.$robot_info['robot_name'].' has reached Level 100!<br /> <span style="font-size: 90%;">Stat bonuses will now be awarded immediately when this robot lands the finishing blow on a target! Try to max out each stat to its full potential!</span>'), ENT_QUOTES, 'UTF-8') ?>" class="robot_stat robot_type_electric"><?= $robot_info['robot_level'] ?> <span>&#9733;</span></a>
                        <? } else { ?>
                            <span class="robot_stat robot_level_reset robot_type_<?= !empty($robot_rewards['flags']['reached_max_level']) ? 'electric' : 'none' ?>"><?= !empty($robot_rewards['flags']['reached_max_level']) ? '<span>&#9733;</span>' : '' ?> <?= $robot_info['robot_level'] ?></span>
                        <? } ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the experience
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Exp :</label>
                        <? if($robot_info['robot_level'] >= 100): ?>
                            <span class="robot_stat robot_type_experience" title="Max Experience!"><span>&#8734;</span> / 1000</span>
                        <? else: ?>
                            <span class="robot_stat"><?= $robot_info['robot_experience'] ?> / 1000</span>
                        <? endif; ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the energy
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label class="<?= !empty($player_info['player_energy']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Energy :</label>
                        <?
                        // Print out the energy stat breakdown
                        $print_robot_stat_function('energy');
                        $print_robot_stat_function('weapons');
                        ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                */

                // Define the markup for the energy
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label class="<?= !empty($player_info['player_energy']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Energy :</label>
                        <?
                        // Print out the energy stat breakdown
                        $print_robot_stat_function('energy');
                        ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the weapons
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label class="<?= !empty($player_info['player_energy']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Weapons :</label>
                        <?
                        // Print out the energy stat breakdown
                        $print_robot_stat_function('weapons');
                        ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the attack
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label class="<?= !empty($player_info['player_attack']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Attack :</label>
                        <?
                        // Print out the attack stat breakdown
                        $print_robot_stat_function('attack');
                        ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the defense
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label class="<?= !empty($player_info['player_defense']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Defense :</label>
                        <?
                        // Print out the defense stat breakdown
                        $print_robot_stat_function('defense');
                        ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the speed
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label class="<?= !empty($player_info['player_speed']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Speed :</label>
                        <?
                        // Print out the speed stat breakdown
                        $print_robot_stat_function('speed');
                        ?>
                    </td>
                    <?
                    $right_column_markup[] = ob_get_clean();
                }

                ?>

                <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
                    <span class="title robot_type">
                        <?= $robot_info['robot_name']?>
                        <?= $robot_info['robot_level'] >= 100 ? '<span>&#9733;</span>' : '' ?>
                    </span>
                    <?

                    // Only show omega indicators if the the Omega Seed has been unlocked
                    if (mmrpg_prototype_item_unlocked('omega-seed')){

                        // Collect possible hidden power types
                        $hidden_power_types = rpg_type::get_hidden_powers('elements');

                        // Generate this robot's omega string, collect it's hidden power
                        $robot_omega_string = rpg_game::generate_omega_robot_string($robot_info['robot_token']);
                        $robot_hidden_power = rpg_game::select_omega_value($robot_omega_string, $hidden_power_types);

                        // Print out the omega indicators for the shop
                        echo '<span class="omega robot_type type_'.$robot_hidden_power.'" title="Omega Influence || [['.ucfirst($robot_hidden_power).' Type]]"></span>'.PHP_EOL;
                        //title="Omega Influence || [['.ucfirst($robot_hidden_power).' Type]]"

                    }

                    ?>
                    <span class="core robot_type">
                        <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/items/<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>-core/icon_left_40x40.png);"></span></span>
                        <span class="text"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?> Core</span>
                    </span>
                </div>

                <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
                    <table class="full" style="margin-bottom: 5px;">
                        <colgroup>
                            <col width="49%" />
                            <col width="1%" />
                            <col width="50%" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <?
                                if (!empty($left_column_markup[0])){ echo $left_column_markup[0]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?
                                if (!empty($right_column_markup[0])){ echo $right_column_markup[0]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?
                                if (!empty($left_column_markup[1])){ echo $left_column_markup[1]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?
                                if (!empty($right_column_markup[1])){ echo $right_column_markup[1]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>

                            <tr>
                                <?
                                if (!empty($left_column_markup[2])){ echo $left_column_markup[2]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?
                                if (!empty($right_column_markup[2])){ echo $right_column_markup[2]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?
                                if (!empty($left_column_markup[3])){ echo $left_column_markup[3]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?
                                if (!empty($right_column_markup[3])){ echo $right_column_markup[3]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?
                                if (!empty($left_column_markup[4])){ echo $left_column_markup[4]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?
                                if (!empty($right_column_markup[4])){ echo $right_column_markup[4]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?
                                if (!empty($left_column_markup[5])){ echo $left_column_markup[5]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?
                                if (!empty($right_column_markup[5])){ echo $right_column_markup[5]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                    <table class="full">
                        <colgroup>
                            <col width="100%" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <td class="right" style="padding-top: 4px;">
                                    <?

                                    // Loop through all the abilities collected by the player and collect IDs
                                    $allowed_ability_ids = array();
                                    if (!empty($player_ability_rewards)){
                                        foreach ($player_ability_rewards AS $ability_token => $ability_info){

                                            if (empty($ability_info['ability_token'])){ continue; }
                                            elseif ($ability_info['ability_token'] == '*'){ continue; }
                                            elseif ($ability_info['ability_token'] == 'ability'){ continue; }
                                            elseif (!isset($mmrpg_database_abilities[$ability_info['ability_token']])){ continue; }
                                            elseif (!self::has_ability_compatibility($robot_info['robot_token'], $ability_token, $current_item_token)){ continue; }
                                            $ability_info['ability_id'] = $mmrpg_database_abilities[$ability_info['ability_token']]['ability_id'];

                                            $allowed_ability_ids[] = $ability_info['ability_id'];

                                        }
                                    }

                                    ?>
                                    <div class="ability_container" data-compatible="<?= implode(',', $allowed_ability_ids) ?>">
                                        <?

                                        // Sort the player ability index based on ability number
                                        uasort($player_ability_rewards, array('rpg_functions', 'abilities_sort_for_editor'));

                                        // Sort the robot ability index based on ability number
                                        sort($robot_ability_rewards);

                                        // Collect the ability reward options to be used on all selects
                                        $ability_rewards_options = $global_allow_editing ? rpg_ability::print_editor_options_list_markup($player_ability_rewards, $robot_ability_rewards, $player_info, $robot_info) : '';

                                        // Loop through the robot's current abilities and list them one by one
                                        $empty_ability_counter = 0;
                                        if (!empty($robot_info['robot_abilities'])){
                                            $temp_string = array();
                                            $temp_inputs = array();
                                            $ability_key = 0;

                                            // DEBUG
                                            //echo 'robot-ability:';
                                            foreach ($robot_info['robot_abilities'] AS $robot_ability){

                                                if (empty($robot_ability['ability_token'])){ continue; }
                                                elseif ($robot_ability['ability_token'] == '*'){ continue; }
                                                elseif ($robot_ability['ability_token'] == 'ability'){ continue; }
                                                elseif (!isset($mmrpg_database_abilities[$robot_ability['ability_token']])){ continue; }
                                                elseif ($ability_key > 7){ continue; }

                                                $ability_token = $robot_ability['ability_token'];
                                                $this_ability = rpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
                                                if (empty($ability_token) || empty($this_ability)){ continue; }
                                                elseif (!self::has_ability_compatibility($robot_info['robot_token'], $ability_token, $current_item_token)){ continue; }

                                                $temp_select_markup = rpg_ability::print_editor_select_markup($ability_rewards_options, $player_info, $robot_info, $this_ability, $ability_key);

                                                $temp_string[] = $temp_select_markup;
                                                $ability_key++;

                                            }

                                            if ($ability_key <= 7){
                                                for ($ability_key; $ability_key <= 7; $ability_key++){
                                                    $empty_ability_counter++;
                                                    if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                                    else { $empty_ability_disable = false; }
                                                    //$temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $ability_rewards_options);
                                                    $this_ability_title_html = '<label>-</label>';
                                                    //if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                    $temp_string[] = '<a class="ability_name " style="'.($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-id="0" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="" title="" data-tooltip="">'.$this_ability_title_html.'</a>';
                                                }
                                            }

                                        } else {

                                            for ($ability_key = 0; $ability_key <= 7; $ability_key++){
                                                $empty_ability_counter++;
                                                if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                                else { $empty_ability_disable = false; }
                                                //$temp_select_options = str_replace('value=""', 'value="" selected="selected"', $ability_rewards_options);
                                                $this_ability_title_html = '<label>-</label>';
                                                //if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="ability_name " style="'.($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-id="0" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_ability_title_html.'</a>';
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
                </div>

            </div>
            <?
            $key_counter++;

            // Return the backup of the player selector
            $allow_player_selector = $allow_player_selector_backup;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;
    }

    // Define a function for calculating robot stat details
    public static function calculate_stat_values($level, $base_stats, $bonus_stats = array(), $limit = false, $core = '', $starforce_values = array()){

        // Define the four basic stat tokens
        $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');

        // Check if this is of a special core type
        $is_copy_core = $core == 'copy' ? true : false;
        $is_neutral_core = $core == '' ? true : false;
        $is_elemental_core = !$is_copy_core && !$is_neutral_core ? true : false;

        // Define the defaults for starforce boost and multiplier
        if ($is_elemental_core){ $base_starforce_multiplier   = 10.0; }
        elseif ($is_copy_core){ $base_starforce_multiplier    =  1.0; }
        elseif ($is_neutral_core){ $base_starforce_multiplier =  0.1; }

        // Define the robot stats array to return
        $robot_stats = array();

        // Collect the robot's current level
        $robot_stats['level'] = $level;
        $robot_stats['level_max'] = 100;

        // Loop through each stat and calculate values
        foreach ($stat_tokens AS $key => $stat){
            $robot_stats[$stat]['base'] = $base_stats['robot_'.$stat];

            // Define the defaults for starforce boost and multiplier
            $starforce_multiplier = 0;
            if ($is_elemental_core){ $starforce_multiplier          = 10.00; }
            elseif ($is_copy_core){ $starforce_multiplier           = 01.00; }
            elseif ($is_neutral_core){
                if ($stat == 'energy'){ $starforce_multiplier       = 00.10; }
                elseif ($stat == 'weapons'){ $starforce_multiplier  = 00.01; }
            }

            // If starforce values were not empty, calculate boosts
            $starforce_boost = 0;
            if (!empty($starforce_values)){
                // Use all types if neutral or copy core, otherwise be more selective
                if ($is_copy_core || $is_neutral_core){
                    foreach ($starforce_values AS $boost_value){
                        $starforce_boost += $boost_value * $starforce_multiplier;
                    }
                } elseif ($is_elemental_core && isset($starforce_values[$core])){
                    $boost_value = $starforce_values[$core];
                    $starforce_boost += $boost_value * $starforce_multiplier;
                }
                // Round up the starforce boost to a full number
                $starforce_boost = floor($starforce_boost);
            }

            // Calculate the individual stat values based on their type and multipliers
            if ($stat == 'energy'){

                // If this is the ENERGY stat
                $robot_stats[$stat]['base_max'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level_max'], 1);

                $robot_stats[$stat]['bonus'] = isset($bonus_stats['robot_'.$stat]) ? $bonus_stats['robot_'.$stat] : 0;
                if ($stat != 'energy'){ $robot_stats[$stat]['bonus_max'] = round($robot_stats[$stat]['base_max'] * MMRPG_SETTINGS_STATS_BONUS_MAX); }
                else { $robot_stats[$stat]['bonus_max'] = 0; }
                if ($limit && $robot_stats[$stat]['bonus'] > $robot_stats[$stat]['bonus_max']){ $robot_stats[$stat]['bonus'] = $robot_stats[$stat]['bonus_max']; }

                if ($is_neutral_core){ $robot_stats[$stat]['starforce'] = $starforce_boost; }
                else { $robot_stats[$stat]['starforce'] = 0; }

                $robot_stats[$stat]['current'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level'], 1) + $robot_stats[$stat]['bonus'] + $robot_stats[$stat]['starforce'];
                $robot_stats[$stat]['current_noboost'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $level, 1);

                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base_max'] + $robot_stats[$stat]['bonus_max'] + $robot_stats[$stat]['starforce'];

                if ($robot_stats[$stat]['current'] > $robot_stats[$stat]['max']){
                    $robot_stats[$stat]['over'] = $robot_stats[$stat]['current'] - $robot_stats[$stat]['max'];
                }

            } elseif ($stat == 'weapons'){

                // Else if this is the WEAPONS stat
                $robot_stats[$stat]['base_max'] = $robot_stats[$stat]['base'];

                $robot_stats[$stat]['bonus'] = 0;
                $robot_stats[$stat]['bonus_max'] = 0;

                if ($is_neutral_core){ $robot_stats[$stat]['starforce'] = $starforce_boost; }
                else { $robot_stats[$stat]['starforce'] = 0; }

                $robot_stats[$stat]['current'] = $robot_stats[$stat]['base'] + $robot_stats[$stat]['starforce'];
                $robot_stats[$stat]['current_noboost'] = $robot_stats[$stat]['base'];

                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['over'] = 0;

            } else {

                // If this is ATTACK, DEFENSE, or SPEED stats
                $robot_stats[$stat]['base_max'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level_max']);

                $robot_stats[$stat]['bonus'] = isset($bonus_stats['robot_'.$stat]) ? $bonus_stats['robot_'.$stat] : 0;
                $robot_stats[$stat]['bonus_max'] = round($robot_stats[$stat]['base_max'] * MMRPG_SETTINGS_STATS_BONUS_MAX);
                if ($limit && $robot_stats[$stat]['bonus'] > $robot_stats[$stat]['bonus_max']){ $robot_stats[$stat]['bonus'] = $robot_stats[$stat]['bonus_max']; }

                if (!$is_neutral_core){ $robot_stats[$stat]['starforce'] = $starforce_boost; }
                else { $robot_stats[$stat]['starforce'] = 0; }

                $robot_stats[$stat]['current'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level']) + $robot_stats[$stat]['bonus'] + $robot_stats[$stat]['starforce'];
                $robot_stats[$stat]['current_noboost'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $level);

                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base_max'] + $robot_stats[$stat]['bonus_max'] + $robot_stats[$stat]['starforce'];

                if ($robot_stats[$stat]['current'] > $robot_stats[$stat]['max']){
                    $robot_stats[$stat]['over'] = $robot_stats[$stat]['current'] - $robot_stats[$stat]['max'];
                }

            }

        }

        // Return calculated robot stats
        return $robot_stats;

    }

    // Define a function for calculating a robot stat level boost
    public static function calculate_level_boosted_stat($base, $level, $percent = 5){
        $rel_level = $level < 100 ? ($level - 1) : $level;
        $stat_boost = round( $base + ($base * ($percent / 100) * $rel_level) );
        return $stat_boost;
    }


    // -- END-OF-TURN CHECK FUNCTIONS -- //

    // Define a function for checking the current turn and updating history
    public function check_history(rpg_player $target_player, rpg_robot $target_robot){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // If the battle has ended, don't do this
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Collect references to relative player and robot objects
        $this_player = $this->player;
        $this_robot = $this;

        // Update the history if this robot is active
        $current_battle_turn = $this_battle->counters['battle_turn'];
        if ($current_battle_turn){
            if ($this_robot->robot_position == 'active'){
                if (!in_array($current_battle_turn, $this_robot->history['turns_active'])){ $this_robot->history['turns_active'][] = $current_battle_turn; }
            } elseif ($this_robot->robot_position == 'bench'){
                if (!in_array($current_battle_turn, $this_robot->history['turns_benched'])){ $this_robot->history['turns_benched'][] = $current_battle_turn; }
            }
        }

        // Update this robot's setting data
        $this_robot->update_session();

    }

    // Define a function for checking attachment status
    public function check_attachments(rpg_player $target_player, rpg_robot $target_robot){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // If the battle has ended, don't do this
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Collect references to relative player and robot objects
        $this_player = $this->player;
        $this_robot = $this;

        // Hide any disabled robots and return
        if ($this_robot->get_status() == 'disabled'
            || $this_robot->robot_status == 'disabled'){
            $this_robot->set_flag('apply_disabled_state', true);
            $this_battle->events_create();
            return;
        }

        // If this robot has any attachments, loop through them
        $static_attachment_key = $this_robot->get_static_attachment_key();
        $this_robot_attachments = $this_robot->get_current_attachments();
        if (!empty($this_robot_attachments)){
            $attachment_action_flag = false;
            $attachment_key = 0;
            foreach ($this_robot_attachments AS $attachment_token => $attachment_info){

                $attachment_debug_token = str_replace('ability_', '', $attachment_token);
                $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' checkpoint has attachment '.$attachment_debug_token);

                // Check to see if this is a static or dynamic attachment
                $is_static_attachment = isset($this_battle->battle_attachments[$static_attachment_key][$attachment_token]) ? true : false;

                // Collect or load the attachment into memory
                $this_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $attachment_info);

                // ATTACHMENT REPEAT
                // If this attachment has REPEAT effects
                if (!empty($attachment_info['attachment_repeat'])){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' has repeat!');

                        $temp_trigger_type = !empty($attachment_info['attachment_repeat']['trigger']) ? $attachment_info['attachment_repeat']['trigger'] : 'damage';
                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' has '.$temp_trigger_type.' trigger!');

                        // REPEAT DAMAGE
                        if ($temp_trigger_type == 'damage'){

                            // Define the system word based on the stat kind
                            $temp_damage_kind = $attachment_info['attachment_repeat']['kind'];
                            $temp_damage_words = rpg_functions::get_stat_damage_words($temp_damage_kind);

                            // Update the success message to reflect the current target
                            if (!isset($attachment_info['attachment_repeat']['success'])){ $attachment_info['attachment_repeat']['success'] = array(9, -10, -10, -10, 'The '.$this_attachment->print_name().' '.$temp_damage_words['action'].' '.$this_robot->print_name().'&#39;s '.$temp_damage_words['object'].' systems!'); }
                            $this_attachment->damage_options_update($attachment_info['attachment_repeat']);
                            $this_attachment->recovery_options_update($attachment_info['attachment_repeat']);
                            $temp_trigger_options = isset($attachment_info['attachment_repeat']['options']) ? $attachment_info['attachment_repeat']['options'] : array('apply_modifiers' => false);
                            if (isset($attachment_info['attachment_'.$temp_damage_kind])){

                                // Collect the base damage amount
                                $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                $temp_stat_amount = $this_robot->get_stat($temp_damage_kind);
                                $temp_stat_base_amount = $this_robot->get_base_stat($temp_damage_kind);

                                // If an attachment damage percent was provided, recalculate from current stat
                                if (isset($attachment_info['attachment_'.$temp_damage_kind.'_percent'])){
                                    $temp_damage_amount = ceil($temp_stat_amount * ($attachment_info['attachment_'.$temp_damage_kind.'_percent'] / 100));
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_damage_kind.' damage of '.$attachment_info['attachment_'.$temp_damage_kind.'_percent'].'% of current <br /> ceil('.$temp_stat_amount.' * ('.$attachment_info['attachment_'.$temp_damage_kind.'_percent'].' / 100)) = '.$temp_damage_amount.'');
                                }
                                // Else if an attachment damage base percent was provided, recalculate from base stat
                                elseif (isset($attachment_info['attachment_'.$temp_damage_kind.'_base_percent'])){
                                    $temp_damage_amount = ceil($temp_stat_base_amount * ($attachment_info['attachment_'.$temp_damage_kind.'_base_percent'] / 100));
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_damage_kind.' damage of '.$attachment_info['attachment_'.$temp_damage_kind.'_base_percent'].'% of base <br /> ceil('.$temp_stat_base_amount.' * ('.$attachment_info['attachment_'.$temp_damage_kind.'_base_percent'].' / 100)) = '.$temp_damage_amount.'');
                                }
                                // Otherwise attachment damage should be calculated normally
                                else {
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_damage_kind.' damage of '.$temp_damage_amount.'!');
                                }

                                // If this is energy we're dealing with, we must respect min and max limits
                                if ($temp_damage_kind == 'energy' && ($temp_stat_amount - $temp_damage_amount) < 0){
                                    $temp_damage_amount = $temp_stat_amount;
                                    $attachment_info['attachment_'.$temp_damage_kind.'_base_percent'] = round(($temp_damage_amount / $temp_stat_base_amount) * 100);
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_damage_kind.' damage too high, changed to '.$attachment_info['attachment_'.$temp_damage_kind.'_base_percent'].'% of base or '.$temp_damage_amount.' / '.$temp_stat_base_amount);
                                }

                                // Only deal damage if the amount was greater than zero
                                if ($temp_damage_amount > 0){ $this_robot->trigger_damage($this_robot, $this_attachment, $temp_damage_amount, false, $temp_trigger_options); }
                                $temp_results = $this_attachment->get_results();
                                if ($temp_results['this_result'] != 'failure' && $temp_results['this_amount'] > 0){ $attachment_action_flag = true; }

                            } else {
                                $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_damage_kind.' damage amount not found!');
                            }

                        }
                        // REPEAT RECOVERY
                        elseif ($temp_trigger_type == 'recovery'){

                            // Define the system word based on the stat kind
                            $temp_recovery_kind = $attachment_info['attachment_repeat']['kind'];
                            $temp_recovery_words = rpg_functions::get_stat_recovery_words($temp_recovery_kind);

                            // Update the success message to reflect the current target
                            if (!isset($attachment_info['attachment_repeat']['success'])){ $attachment_info['attachment_repeat']['success'] = array(9, -10, -10, -10, 'The '.$this_attachment->print_name().' '.$temp_recovery_words['action'].' '.$this_robot->print_name().'&#39;s '.$temp_recovery_words['object'].' systems!'); }
                            $this_attachment->recovery_options_update($attachment_info['attachment_repeat']);
                            $this_attachment->damage_options_update($attachment_info['attachment_repeat']);
                            $temp_trigger_options = isset($attachment_info['attachment_repeat']['options']) ? $attachment_info['attachment_repeat']['options'] : array('apply_modifiers' => false);
                            if (isset($attachment_info['attachment_'.$temp_recovery_kind])){

                                // Collect the base recovery amount
                                $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                $temp_stat_amount = $this_robot->get_stat($temp_recovery_kind);
                                $temp_stat_base_amount = $this_robot->get_base_stat($temp_recovery_kind);

                                // If an attachment recovery percent was provided, recalculate from current stat
                                if (isset($attachment_info['attachment_'.$temp_recovery_kind.'_percent'])){
                                    $temp_recovery_amount = ceil($temp_stat_amount * ($attachment_info['attachment_'.$temp_recovery_kind.'_percent'] / 100));
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_recovery_kind.' recovery of '.$attachment_info['attachment_'.$temp_recovery_kind.'_percent'].'% of current <br /> ceil('.$temp_stat_amount.' * ('.$attachment_info['attachment_'.$temp_recovery_kind.'_percent'].' / 100)) = '.$temp_recovery_amount.'');
                                }
                                // Else if an attachment recovery base percent was provided, recalculate from base stat
                                elseif (isset($attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'])){
                                    $temp_recovery_amount = ceil($temp_stat_base_amount * ($attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'] / 100));
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_recovery_kind.' recovery of '.$attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'].'% of base <br /> ceil('.$temp_stat_base_amount.' * ('.$attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'].' / 100)) = '.$temp_recovery_amount.'');
                                }
                                // Otherwise attachment recovery should be calculated normally
                                else {
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_recovery_kind.' recovery of '.$temp_recovery_amount.'!');
                                }

                                // If this is energy we're dealing with, we must respect min and max limits
                                if ($temp_recovery_kind == 'energy' && ($temp_stat_amount + $temp_recovery_amount) > $temp_stat_base_amount){
                                    $temp_recovery_amount = $temp_stat_base_amount - $temp_stat_amount;
                                    $attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'] = round(($temp_recovery_amount / $temp_stat_base_amount) * 100);
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_recovery_kind.' recovery too high, changed to '.$attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'].'% of base or '.$temp_recovery_amount.' / '.$temp_stat_base_amount);
                                }

                                // Only deal recovery if the amount was greater than zero
                                if ($temp_recovery_amount > 0){ $this_robot->trigger_recovery($this_robot, $this_attachment, $temp_recovery_amount, false, $temp_trigger_options); }
                                $temp_results = $this_attachment->get_results();
                                if ($temp_results['this_result'] != 'failure' && $temp_results['this_amount'] > 0){ $attachment_action_flag = true; }

                            } else {
                                $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_recovery_kind.' recovery amount not found!');
                            }

                        }
                        // REPEAT SPECIAL
                        elseif ($temp_trigger_type == 'special'){

                            $this_attachment->target_options_update($attachment_info['attachment_repeat']);
                            $this_attachment->recovery_options_update($attachment_info['attachment_repeat']);
                            $this_attachment->damage_options_update($attachment_info['attachment_repeat']);
                            $this_attachment->update_session();
                            $temp_trigger_options = isset($attachment_info['attachment_repeat']['options']) ? $attachment_info['attachment_repeat']['options'] : array();
                            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers special!');
                            if ($this_robot->get_energy() > 0){
                                $this_robot->trigger_damage($this_robot, $this_attachment, 0, false, $temp_trigger_options);
                            }
                            $attachment_action_flag = true;

                        }

                        // If the temp robot was disabled, trigger the event
                        if ($this_robot->get_energy() < 1){
                            $this_robot->trigger_disabled($target_robot);
                            // If this the player's last robot
                            $active_robots = $this_player->get_robots_active();
                            if (empty($active_robots)){
                                // Trigger the battle complete event
                                $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot);
                                $attachment_action_flag = true;
                            }
                        }

                }

                // ATTACHMENT DURATION
                // If this attachment has DURATION counter
                if (isset($attachment_info['attachment_duration'])){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' has duration '.$attachment_info['attachment_duration']);

                    // DURATION COUNT -1
                    // If the duration is not empty, decrement it and continue
                    if ($attachment_info['attachment_duration'] > 0){

                        $attachment_info['attachment_duration'] = $attachment_info['attachment_duration'] - 1;
                        if ($is_static_attachment){ $this_battle->battle_attachments[$static_attachment_key][$attachment_token] = $attachment_info; }
                        else { $this_robot->set_attachment($attachment_token, $attachment_info); }
                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' duration decreased to '.$attachment_info['attachment_duration']);

                    }

                    // DURATION EXPIRED
                    // Otherwise, trigger the destroy action for this attachment
                    if ($attachment_info['attachment_duration'] <= 0){

                        // Remove this attachment and inflict damage on the robot
                        if ($is_static_attachment){
                            unset($this_battle->battle_attachments[$static_attachment_key][$attachment_token]);
                            $this_battle->update_session();
                        } else {
                            $this_robot->unset_attachment($attachment_token);
                            $this_robot->update_session();
                        }

                        // ATTACHMENT DESTROY
                        if (isset($attachment_info['attachment_destroy']) && $attachment_info['attachment_destroy'] !== false){

                            $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' duration ended and has '.$temp_trigger_type.' trigger!');

                            // DESTORY DAMAGE
                            if ($temp_trigger_type == 'damage'){

                                $this_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $this_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array('apply_modifiers' => false);
                                if (isset($attachment_info['attachment_'.$temp_damage_kind])){

                                    // Collect the base damage amount
                                    $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                    $temp_stat_amount = $this_robot->get_stat($temp_damage_kind);
                                    $temp_stat_base_amount = $this_robot->get_base_stat($temp_damage_kind);

                                    // If an attachment damage percent was provided, recalculate from current stat
                                    if (isset($attachment_info['attachment_'.$temp_damage_kind.'_percent'])){
                                        $temp_damage_amount = ceil($temp_stat_amount * ($attachment_info['attachment_'.$temp_damage_kind.'_percent'] / 100));
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_damage_kind.' damage of '.$attachment_info['attachment_'.$temp_damage_kind.'_percent'].'% of current <br /> ceil('.$temp_stat_amount.' * ('.$attachment_info['attachment_'.$temp_damage_kind.'_percent'].' / 100)) = '.$temp_damage_amount.'');
                                    }
                                    // Else if an attachment damage base percent was provided, recalculate from base stat
                                    elseif (isset($attachment_info['attachment_'.$temp_damage_kind.'_base_percent'])){
                                        $temp_damage_amount = ceil($temp_stat_base_amount * ($attachment_info['attachment_'.$temp_damage_kind.'_base_percent'] / 100));
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_damage_kind.' damage of '.$attachment_info['attachment_'.$temp_damage_kind.'_base_percent'].'% of base <br /> ceil('.$temp_stat_base_amount.' * ('.$attachment_info['attachment_'.$temp_damage_kind.'_base_percent'].' / 100)) = '.$temp_damage_amount.'');
                                    }
                                    // Otherwise attachment damage should be calculated normally
                                    else {
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_damage_kind.' damage of '.$temp_damage_amount.'!');
                                    }

                                    // If this is energy we're dealing with, we must respect min and max limits
                                    if ($temp_damage_kind == 'energy' && ($temp_stat_amount - $temp_damage_amount) < 0){
                                        $temp_damage_amount = $temp_stat_amount;
                                        $attachment_info['attachment_'.$temp_damage_kind.'_base_percent'] = round(($temp_damage_amount / $temp_stat_base_amount) * 100);
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_damage_kind.' damage too high, changed to '.$attachment_info['attachment_'.$temp_damage_kind.'_base_percent'].'% of base or '.$temp_damage_amount.' / '.$temp_stat_base_amount);
                                    }

                                    // Only deal damage if the amount was greater than zero
                                    if ($temp_damage_amount > 0){ $this_robot->trigger_damage($this_robot, $this_attachment, $temp_damage_amount, false, $temp_trigger_options); }
                                    if ($this_attachment->ability_results['this_result'] != 'failure' && $this_attachment->ability_results['this_amount'] > 0){ $attachment_action_flag = true; }

                                } else {
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_damage_kind.' damage amount not found!');
                                }

                            }
                            // DESTROY RECOVERY
                            elseif ($temp_trigger_type == 'recovery'){

                                $this_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $this_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array('apply_modifiers' => false);
                                if (isset($attachment_info['attachment_'.$temp_recovery_kind])){

                                    // Collect the base recovery amount
                                    $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                    $temp_stat_amount = $this_robot->get_stat($temp_recovery_kind);
                                    $temp_stat_base_amount = $this_robot->get_base_stat($temp_recovery_kind);

                                    // If an attachment recovery percent was provided, recalculate from current stat
                                    if (isset($attachment_info['attachment_'.$temp_recovery_kind.'_percent'])){
                                        $temp_recovery_amount = ceil($temp_stat_amount * ($attachment_info['attachment_'.$temp_recovery_kind.'_percent'] / 100));
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_recovery_kind.' recovery of '.$attachment_info['attachment_'.$temp_recovery_kind.'_percent'].'% of current <br /> ceil('.$temp_stat_amount.' * ('.$attachment_info['attachment_'.$temp_recovery_kind.'_percent'].' / 100)) = '.$temp_recovery_amount.'');
                                    }
                                    // Else if an attachment recovery base percent was provided, recalculate from base stat
                                    elseif (isset($attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'])){
                                        $temp_recovery_amount = ceil($temp_stat_base_amount * ($attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'] / 100));
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_recovery_kind.' recovery of '.$attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'].'% of base <br /> ceil('.$temp_stat_base_amount.' * ('.$attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'].' / 100)) = '.$temp_recovery_amount.'');
                                    }
                                    // Otherwise attachment recovery should be calculated normally
                                    else {
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' triggers '.$temp_recovery_kind.' recovery of '.$temp_recovery_amount.'!');
                                    }

                                    // If this is energy we're dealing with, we must respect min and max limits
                                    if ($temp_recovery_kind == 'energy' && ($temp_stat_amount + $temp_recovery_amount) > $temp_stat_base_amount){
                                        $temp_recovery_amount = $temp_stat_base_amount - $temp_stat_amount;
                                        $attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'] = round(($temp_recovery_amount / $temp_stat_base_amount) * 100);
                                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_recovery_kind.' recovery too high, changed to '.$attachment_info['attachment_'.$temp_recovery_kind.'_base_percent'].'% of base or '.$temp_recovery_amount.' / '.$temp_stat_base_amount);
                                    }

                                    // Only deal recovery if the amount was greater than zero
                                    if ($temp_recovery_amount > 0){ $this_robot->trigger_recovery($this_robot, $this_attachment, $temp_recovery_amount, false, $temp_trigger_options); }
                                    $temp_results = $this_attachment->get_results();
                                    if ($temp_results['this_result'] != 'failure' && $temp_results['this_amount'] > 0){ $attachment_action_flag = true; }

                                } else {
                                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' '.$temp_recovery_kind.' recovery amount not found!');
                                }

                            }
                            // DESTROY SPECIAL
                            elseif ($temp_trigger_type == 'special'){

                                $this_attachment->target_options_update($attachment_info['attachment_destroy']);
                                $this_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $this_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array();
                                $this_robot->trigger_damage($this_robot, $this_attachment, 0, false, $temp_trigger_options);
                                $attachment_action_flag = true;

                            }

                            // If the temp robot was disabled, trigger the event
                            if ($this_robot->get_energy() < 1){
                                $this_robot->trigger_disabled($target_robot);
                                // If this the player's last robot
                                $active_robots = $this_player->get_robots_active();
                                if (empty($active_robots)){
                                    // Trigger the battle complete event
                                    $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot);
                                    $attachment_action_flag = true;
                                }
                            }

                        }
                    }

                }

                $attachment_key++;

            }

            // Create an empty field to remove any leftover frames
            if ($attachment_action_flag){ $this_battle->events_create(); }

        }

    }

    // Define a function for checking ttem status
    public function check_items(rpg_player $target_player, rpg_robot $target_robot){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();
        $session_token = rpg_game::session_token();

        // If the battle has ended, don't do this
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Collect references to relative player and robot objects
        $this_player = $this->player;
        $this_robot = $this;

        // Hide any disabled robots and return
        if ($this_robot->get_status() == 'disabled'){
            $this_robot->set_flag('apply_disabled_state', true);
            $this_battle->events_create();
            return;
        }

        // If this robot has an item attached, process actions
        if ($this_robot->has_item()){

            // Define the item info based on token and load into memory
            $item_token = $this_robot->get_item();
            $item_info = array(
                'flags' => array('is_part' => true),
                'part_token' => 'item_'.$item_token,
                'item_token' => $item_token
                );
            $this_item = rpg_game::get_item($this_battle, $this_player, $this_robot, $item_info);
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' checkpoint has item '.$item_token);

            // If the robot is holding a Field Booster item, increase multiplier
            if ($item_token == 'field-booster'){

                // Define the item object and trigger info
                $temp_core_type = $this_robot->get_core();
                $temp_field_type = $this_field->field_type;
                if (empty($temp_core_type)){ $temp_boost_type = 'recovery'; }
                elseif ($temp_core_type == 'copy' || $temp_core_type == 'empty'){ $temp_boost_type = 'damage'; }
                else { $temp_boost_type = $temp_core_type; }
                if (!isset($this_field->field_multipliers[$temp_boost_type]) || $this_field->field_multipliers[$temp_boost_type] < MMRPG_SETTINGS_MULTIPLIER_MAX){

                    // Define this item's attachment token
                    $this_star_index = rpg_prototype::star_image(!empty($temp_boost_type) ? $temp_boost_type : 'none');
                    $this_sprite_sheet = 'field-support';
                    $this_attachment_token = 'item_field-booster';
                    $this_attachment_info = array(
                        'class' => 'item',
                        'attachment_token' => $this_attachment_token,
                        'item_token' => $item_token,
                        'item_image' => $this_sprite_sheet.($this_star_index['sheet'] > 1 ? '-'.$this_star_index['sheet'] : ''),
                        'item_frame' => $this_star_index['frame'],
                        'item_frame_animate' => array($this_star_index['frame']),
                        'item_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
                        );

                    // Attach this item attachment to this robot temporarily
                    $this_robot->set_frame('taunt');
                    $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

                    // Create or increase the elemental booster for this field
                    $temp_change_percent = round($this_item->get_recovery2() / 100, 1);
                    $new_multiplier_value = (isset($this_field->field_multipliers[$temp_boost_type]) ? $this_field->field_multipliers[$temp_boost_type] : 1) + $temp_change_percent;
                    if ($new_multiplier_value >= MMRPG_SETTINGS_MULTIPLIER_MAX){
                        $temp_change_percent = $new_multiplier_value - MMRPG_SETTINGS_MULTIPLIER_MAX;
                        $new_multiplier_value = MMRPG_SETTINGS_MULTIPLIER_MAX;
                    }
                    $this_field->field_multipliers[$temp_boost_type] = $new_multiplier_value;
                    $this_field->update_session();

                    // Create the event to show this element boost
                    if ($temp_change_percent > 0){
                        $this_battle->events_create($this_robot, false, $this_field->field_name.' Multipliers',
                            $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!<br />'.
                            'The <span class="item_name item_type item_type_'.$temp_boost_type.'">'.ucfirst($temp_boost_type).'</span> field multiplier rose to <span class="item_name item_type item_type_none">'.$new_multiplier_value.'</span>!',
                            array('canvas_show_this_item_overlay' => true)
                            );
                    }

                    // Remove this item attachment from this robot
                    $this_robot->unset_attachment($this_attachment_token);

                }

            }
            // Else the robot is holding an Energy Capsule or Energy Pellet item, apply recovery
            elseif ($item_token == 'energy-pellet' || $item_token == 'energy-capsule' || $item_token == 'energy-tank'){

                // Collect the base stat for this robot and the item
                $temp_energy = $this_robot->get_energy();
                $temp_base_energy = $this_robot->get_base_energy();
                $temp_item_recovery = $this_item->get_recovery();

                // Calculate this robot's current damage percent
                $temp_damage_required = ceil($temp_item_recovery - 10);
                $temp_damage_taken = round((($temp_base_energy - $temp_energy) / $temp_base_energy) * 100);
                $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' triggers when energy lowered by '.$temp_damage_required.'% (currently at '.$temp_damage_taken.'%)');

                // If the user has token enough damage, we can trigger the ability
                if ($temp_damage_taken >= $temp_damage_required){

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Define the item object and trigger info
                    $temp_recovery_amount = round($temp_base_energy * ($temp_item_recovery / 100));
                    $this_item->recovery_options_update(array(
                        'kind' => 'energy',
                        'frame' => 'taunt',
                        'percent' => true,
                        'modifiers' => false,
                        'frame' => 'taunt',
                        'success' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!'),
                        'failure' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!')
                        ));

                    // Trigger stat recovery for the holding robot
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores energy by '.$temp_recovery_amount.' ('.$temp_item_recovery.'%)');
                    if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }
            // Else the robot is holding an Weapon Capsule or Weapon Pellet item, apply recovery
            elseif ($item_token == 'weapon-pellet' || $item_token == 'weapon-capsule' || $item_token == 'weapon-tank'){

                // Collect the base stat for this robot and the item
                $temp_weapons = $this_robot->get_weapons();
                $temp_base_weapons = $this_robot->get_base_weapons();
                $temp_item_recovery = $this_item->get_recovery();

                // Calculate this robot's current damage percent
                $temp_damage_required = ceil($temp_item_recovery - 10);
                $temp_damage_taken = round((($temp_base_weapons - $temp_weapons) / $temp_base_weapons) * 100);
                $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' triggers when weapons lowered by '.$temp_damage_required.'% (currently at '.$temp_damage_taken.'%)');

                // If the user has token enough damage, we can trigger the ability
                if ($temp_damage_taken >= $temp_damage_required){

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Define the item object and trigger info
                    $temp_recovery_amount = round($temp_base_weapons * ($temp_item_recovery / 100));
                    $this_item->recovery_options_update(array(
                        'kind' => 'weapons',
                        'frame' => 'taunt',
                        'percent' => true,
                        'modifiers' => false,
                        'frame' => 'taunt',
                        'success' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!'),
                        'failure' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!')
                        ));

                    // Trigger stat recovery for the holding robot
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores weapons by '.$temp_recovery_amount.' ('.$temp_item_recovery.'%)');
                    if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }
            // Else if the robot is holding an Yashichi item, apply boosts
            elseif ($item_token == 'yashichi'){

                // Define the trigger flag as false for now
                $trigger_recovery = false;
                $temp_item_recovery = $this_item->get_recovery();

                // First check if energy recovery is necessary
                if (!$trigger_recovery){

                    // Collect the base stat for this robot and the item
                    $temp_energy = $this_robot->get_energy();
                    $temp_base_energy = $this_robot->get_base_energy();

                    // Calculate this robot's current damage percent
                    $temp_damage_required = ceil($temp_item_recovery - 10);
                    $temp_damage_taken = round((($temp_base_energy - $temp_energy) / $temp_base_energy) * 100);
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' triggers when energy lowered by '.$temp_damage_required.'% (currently at '.$temp_damage_taken.'%)');

                    // If the user has token enough damage, we can trigger the ability
                    if ($temp_damage_taken >= $temp_damage_required){ $trigger_recovery = true; }

                }

                // Last check if weapons recovery is necessary
                if (!$trigger_recovery){

                    // Collect the base stat for this robot and the item
                    $temp_weapons = $this_robot->get_weapons();
                    $temp_base_weapons = $this_robot->get_base_weapons();

                    // Calculate this robot's current damage percent
                    $temp_damage_required = ceil($temp_item_recovery - 10);
                    $temp_damage_taken = round((($temp_base_weapons - $temp_weapons) / $temp_base_weapons) * 100);
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' triggers when weapons lowered by '.$temp_damage_required.'% (currently at '.$temp_damage_taken.'%)');

                    // If the user has token enough damage, we can trigger the ability
                    if ($temp_damage_taken >= $temp_damage_required){ $trigger_recovery = true; }

                }

                // If the user has token enough damage or either kind, we can trigger the ability
                if ($trigger_recovery){

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Define the item object and trigger info
                    $temp_base_energy = $this_robot->get_base_energy();
                    $temp_recovery_amount = round($temp_base_energy * ($temp_item_recovery / 100));
                    $this_item->recovery_options_update(array(
                        'kind' => 'energy',
                        'frame' => 'taunt',
                        'percent' => true,
                        'modifiers' => false,
                        'frame' => 'taunt',
                        'success' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!'),
                        'failure' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!')
                        ));

                    // Trigger stat recovery for the holding robot
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores energy by '.$temp_recovery_amount.' ('.$temp_item_recovery.'%)');
                    if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

                    // Define the item object and trigger info
                    $temp_base_weapons = $this_robot->get_base_weapons();
                    $temp_recovery_amount = round($temp_base_weapons * ($temp_item_recovery / 100));
                    $this_item->recovery_options_update(array(
                        'kind' => 'weapons',
                        'frame' => 'taunt',
                        'percent' => true,
                        'modifiers' => false,
                        'frame' => 'taunt',
                        'success' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!'),
                        'failure' => array(9, 0, 0, -9999, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!')
                        ));

                    // Trigger stat recovery for the holding robot
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores weapons by '.$temp_recovery_amount.' ('.$temp_item_recovery.'%)');
                    if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }
            // Else the robot is holding an Attack Booster item, apply boosts
            elseif ($item_token == 'attack-booster'){

                // Ensure this robot's stat isn't already at max value
                if ($this_robot->counters['attack_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' boosts attack by one stage');
                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, 'attack', 1, false, null, null, $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');
                }

            }
            // Else if the robot is holding an Defense Booster item, apply boosts
            elseif ($item_token == 'defense-booster'){

                // Ensure this robot's stat isn't already at max value
                if ($this_robot->counters['defense_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' boosts defense by one stage');
                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, 'defense', 1, false, null, null, $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');
                }

            }
            // Else if the robot is holding an Defense Booster item, apply boosts
            elseif ($item_token == 'speed-booster'){

                // Ensure this robot's stat isn't already at max value
                if ($this_robot->counters['speed_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' boosts speed by one stage');
                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, 'speed', 1, false, null, null, $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');
                }

            }
            // Else the robot is holding an Attack Capsule or Attack Pellet item, apply recovery
            elseif ($item_token === 'attack-pellet' || $item_token === 'attack-capsule'){

                // Only use this item if the robot is active and turns have passed
                $item_restore_value = strstr($item_token, 'pellet') ? 2 : 3;
                $item_wait_time = 1; // strstr($item_token, 'pellet') ? 1 : 2;
                $robot_turns_active = count($this_robot->history['turns_active']);
                if ($this_robot->robot_position == 'active' && $robot_turns_active >= $item_wait_time){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' increases attack by '.$item_restore_value.' stages');

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, 'attack', $item_restore_value, false, null, null, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }
            // Else the robot is holding an Defense Capsule or Defense Pellet item, apply recovery
            elseif ($item_token == 'defense-pellet' || $item_token == 'defense-capsule'){

                // Only use this item if the robot is active and turns have passed
                $item_restore_value = strstr($item_token, 'pellet') ? 2 : 3;
                $item_wait_time = 1; // strstr($item_token, 'pellet') ? 1 : 2;
                $robot_turns_active = count($this_robot->history['turns_active']);
                if ($this_robot->robot_position == 'active' && $robot_turns_active >= $item_wait_time){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' increases defense by '.$item_restore_value.' stages');

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, 'defense', $item_restore_value, false, null, null, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }
            // Else the robot is holding an Speed Capsule or Speed Pellet item, apply recovery
            elseif ($item_token == 'speed-pellet' || $item_token == 'speed-capsule'){

                // Only use this item if the robot is active and turns have passed
                $item_restore_value = strstr($item_token, 'pellet') ? 2 : 3;
                $item_wait_time = 1; // strstr($item_token, 'pellet') ? 1 : 2;
                $robot_turns_active = count($this_robot->history['turns_active']);
                if ($this_robot->robot_position == 'active' && $robot_turns_active >= $item_wait_time){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' increases speed by '.$item_restore_value.' stages');

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, 'speed', $item_restore_value, false, null, null, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }
            // Else the robot is holding an Super Capsule or Super Pellet item, apply recovery
            elseif ($item_token == 'super-pellet' || $item_token == 'super-capsule'){

                // Define the three stat types that can be increased
                $temp_stat_tokens = array('attack', 'defense', 'speed');

                // Only use this item if the robot is active and turns have passed
                $item_restore_value = strstr($item_token, 'pellet') ? 2 : 3;
                $item_wait_time = 1; // strstr($item_token, 'pellet') ? 1 : 2;
                $robot_turns_active = count($this_robot->history['turns_active']);
                if ($this_robot->robot_position == 'active' && $robot_turns_active >= $item_wait_time){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' increases attack/defense/speed by '.$item_restore_value.' stages');

                    // Remove the robot's current item now that it's used up in battle
                    $this_robot->set_item('');

                    // Call the global stat boost function with customized options
                    rpg_ability::ability_function_stat_boost($this_robot, $temp_stat_tokens[0], $item_restore_value, false, null, null, $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');
                    foreach ($temp_stat_tokens AS $temp_stat){
                        if ($temp_stat === $temp_stat_tokens[0]){ continue; }
                        rpg_ability::ability_function_stat_boost($this_robot, $temp_stat, $item_restore_value);
                    }

                    // Also remove this robot's item from the session, we're done with it
                    if ($this_player->player_side == 'left' && empty($this_battle->flags['player_battle']) && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (isset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                        }
                    }

                }

            }


        }

    }

    // Define a function for checking weapons status
    public function check_weapons(rpg_player $target_player, rpg_robot $target_robot, $regen_weapons = true){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // If the battle has ended, don't do this
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Collect references to relative player and robot objects
        $this_player = $this->player;
        $this_robot = $this;

        // Hide any disabled robots and return
        if ($this_robot->get_status() == 'disabled'){
            $this_robot->set_flag('apply_disabled_state', true);
            $this_battle->events_create();
            return;
        }

        // If this robot is not at full weapon energy, increase it by one
        $temp_weapons = $this_robot->get_weapons();
        $temp_base_weapons = $this_robot->get_base_weapons();
        if ($temp_weapons < $temp_base_weapons){
            // Ensure the regen weapons flag has been set to true
            if ($regen_weapons){
                // Define the multiplier based on position
                $temp_multiplier = $this_robot->get_position() == 'bench' ? 2 : 1;
                // Increment this robot's weapons by one point and update
                $temp_weapons += MMRPG_SETTINGS_RECHARGE_WEAPONS * $temp_multiplier;
                $this_robot->set_weapons($temp_weapons);
            }
        }

        // If this robot is on the bench, slowly recovery any lowered stats
        if ($this_robot->get_position() == 'bench'){

            // If this robot's attack is less than base, recover by 1%
            if ($this_robot->robot_attack < $this_robot->robot_base_attack){
                $recovery = ceil($this_robot->robot_base_attack * 0.01);
                $this_robot->robot_attack += $recovery;
            }

            // If this robot's defense is less than base, recover by 1%
            if ($this_robot->robot_defense < $this_robot->robot_base_defense){
                $recovery = ceil($this_robot->robot_base_defense * 0.01);
                $this_robot->robot_defense += $recovery;
            }

            // If this robot's speed is less than base, recover by 1%
            if ($this_robot->robot_speed < $this_robot->robot_base_speed){
                $recovery = ceil($this_robot->robot_base_speed * 0.01);
                $this_robot->robot_speed += $recovery;
            }

        }

    }

    // Define a function for calculating this robot's best stat and returning it
    public static function get_best_stat($robot_info){
        // Decide which one of this robot's stats is best
        if (is_object($robot_info)){ $stats = array('energy' => $robot_info->robot_energy, 'attack' => $robot_info->robot_attack, 'defense' => $robot_info->robot_defense, 'speed' => $robot_info->robot_speed); }
        elseif (is_array($robot_info)){ $stats = array('energy' => $robot_info['robot_energy'], 'attack' => $robot_info['robot_attack'], 'defense' => $robot_info['robot_defense'], 'speed' => $robot_info['robot_speed']); }
        else { return false; }
        asort($stats); $stats = array_reverse($stats);
        if (count(array_unique($stats)) === 1){ $best = 'all'; }
        else { $best = key($stats); }
        return $best;

    }

    // Define a function for calculating this robot's worst stat and returning it
    public static function get_worst_stat($robot_info){
        // Decide which one of this robot's stats is best
        if (is_object($robot_info)){ $stats = array('energy' => $robot_info->robot_energy, 'attack' => $robot_info->robot_attack, 'defense' => $robot_info->robot_defense, 'speed' => $robot_info->robot_speed); }
        elseif (is_array($robot_info)){ $stats = array('energy' => $robot_info['robot_energy'], 'attack' => $robot_info['robot_attack'], 'defense' => $robot_info['robot_defense'], 'speed' => $robot_info['robot_speed']); }
        else { return false; }
        asort($stats); //$stats = array_reverse($stats);
        if (count(array_unique($stats)) === 1){ $best = 'all'; }
        else { $best = key($stats); }
        return $best;

    }

    // Define a function for calculating this robot's best stat and returning a descriptor
    public static function get_best_stat_desc($robot_info){
        // Decide which word best describes this robot based on stat
        $best = self::get_best_stat($robot_info);
        if ($best == 'all'){ $desc = 'balanced'; }
        elseif ($best == 'energy'){ $desc = 'support'; }
        elseif ($best == 'attack'){ $desc = 'powerful'; }
        elseif ($best == 'defense'){ $desc = 'defensive'; }
        elseif ($best == 'speed'){ $desc = 'speedy'; }
        return $desc;
    }

}
?>