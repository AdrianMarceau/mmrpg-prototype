<?php
/**
 * Mega Man RPG Robot
 * <p>The object class for all robots in the Mega Man RPG Prototype.</p>
 */
class rpg_robot extends rpg_object {

    // Define public robot variables
    public $battle;
    public $battle_id;
    public $battle_token;
    public $field;
    public $field_id;
    public $field_token;
    public $robot_id = 0;
    public $robot_key = 0;
    public $robot_name = '';
    public $robot_token = '';
    public $robot_description = '';
    public $robot_number = '';
    public $robot_field = '';
    public $robot_class = '';
    public $robot_gender = '';
    public $robot_core = '';
    public $robot_core2 = '';
    public $robot_experience = 0;
    public $robot_level = 0;
    public $robot_energy = 0;
    public $robot_weapons = 0;
    public $robot_attack = 0;
    public $robot_defense = 0;
    public $robot_speed = 0;
    public $robot_total = 0;
    public $robot_weaknesses = array();
    public $robot_resistances = array();
    public $robot_affinities = array();
    public $robot_immunities = array();
    public $robot_item = '';
    public $robot_abilities = array();
    public $robot_attachments = array();
    public $robot_quotes = array();
    public $robot_status = '';
    public $robot_side = '';
    public $robot_direction = '';
    public $robot_position = '';
    public $robot_stance = '';
    public $robot_rewards = array();
    public $robot_functions = '';
    public $robot_image = '';
    public $robot_image_size = 0;
    public $robot_image_overlay = '';
    public $robot_image_alts = array();
    public $robot_frame = '';
    public $robot_frame_offset = array();
    public $robot_frame_classes = '';
    public $robot_frame_styles = '';
    public $robot_detail_styles = '';
    public $robot_original_player = '';
    public $robot_string = '';
    public $robot_function = null;
    public $robot_function_onload = null;
    public $robot_function_choices_abilities = null;
    public $robot_base_key = 0;
    public $robot_base_name = '';
    public $robot_base_description = '';
    public $robot_base_number = '';
    public $robot_base_core = '';
    public $robot_base_core2 = '';
    public $robot_base_experience = 0;
    public $robot_base_level = 0;
    public $robot_base_energy = 0;
    public $robot_base_weapons = 0;
    public $robot_base_attack = 0;
    public $robot_base_defense = 0;
    public $robot_base_speed = 0;
    public $robot_base_total = 0;
    public $robot_base_weaknesses = array();
    public $robot_base_resistances = array();
    public $robot_base_affinities = array();
    public $robot_base_immunities = array();
    public $robot_base_item = '';
    public $robot_base_abilities = array();
    public $robot_base_attachments = array();
    public $robot_base_quotes = array();
    public $robot_base_image = '';
    public $robot_base_image_size = 0;
    public $robot_base_image_overlay = array();

    // Define the constructor class
    public function __construct(rpg_player $this_player, $robot_info = array()){

        // Update the session keys for this object
        $this->session_key = 'ROBOTS';
        $this->session_token = 'robot_token';
        $this->session_id = 'robot_id';
        $this->class = 'robot';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal class identifier
        $this->class = 'robot';

        // Define the internal battle pointer
        $this->battle = rpg_battle::get_battle();
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Define the internal battle pointer
        $this->field = rpg_field::get_field();
        $this->field_id = $this->field->field_id;
        $this->field_token = $this->field->field_token;

        // Define the internal player values using the provided array
        $this->player = $this_player;
        $this->player_id = $this_player->player_id;
        $this->player_token = $this_player->player_token;

        // Collect current robot data from the function if available
        $robot_info = !empty($robot_info) ? $robot_info : array('robot_id' => 0, 'robot_token' => 'robot');
        // Load the robot data based on the ID and fallback token
        $robot_info = $this->robot_load($robot_info['robot_id'], $robot_info['robot_token'], $robot_info);

        // Now load the robot data from the session or index
        if (empty($robot_info)){
            // Robot data could not be loaded
            die('Robot data could not be loaded :<br />$robot_info = <pre>'.print_r($robot_info, true).'</pre>');
            return false;
        }

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a public function for manually loading data
    public function robot_load($robot_id = 0, $robot_token = 'robot', $custom_info = array()){

        // If the robot ID was not provided, return false
        if (!isset($robot_id)){
            die("robot id must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true));
            return false;
        }
        // If the robot token was not provided, return false
        if (!isset($robot_token)){
            die("robot token must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true));
            return false;
        }

        // Collect current robot data from the session if available
        if (isset($_SESSION['ROBOTS'][$robot_id])){
            $this_robotinfo = $_SESSION['ROBOTS'][$robot_id];
            if ($this_robotinfo['robot_token'] != $robot_token){
                die("robot token and ID mismatch {$robot_id}:{$robot_token}!\n");
                return false;
            }
        }
        // Otherwise, collect robot data from the index
        else {
            $this_robotinfo = self::get_index_info($robot_token);
            if (empty($this_robotinfo)){
                die("robot data could not be loaded for {$robot_id}:{$robot_token}!\n");
                return false;
            }
        }

        // If the custom data was not empty, merge now
        if (!empty($custom_info)){ $this_robotinfo = array_merge($this_robotinfo, $custom_info); }

        // Define the internal robot values using the provided array
        $this->flags = isset($this_robotinfo['flags']) ? $this_robotinfo['flags'] : array();
        $this->counters = isset($this_robotinfo['counters']) ? $this_robotinfo['counters'] : array();
        $this->values = isset($this_robotinfo['values']) ? $this_robotinfo['values'] : array();
        $this->history = isset($this_robotinfo['history']) ? $this_robotinfo['history'] : array();
        $this->robot_id = isset($this_robotinfo['robot_id']) ? $this_robotinfo['robot_id'] : $robot_id;
        $this->robot_key = isset($this_robotinfo['robot_key']) ? $this_robotinfo['robot_key'] : 0;
        $this->robot_name = isset($this_robotinfo['robot_name']) ? $this_robotinfo['robot_name'] : 'Robot';
        $this->robot_token = isset($this_robotinfo['robot_token']) ? $this_robotinfo['robot_token'] : 'robot';
        $this->robot_description = isset($this_robotinfo['robot_description']) ? $this_robotinfo['robot_description'] : '';
        $this->robot_number = isset($this_robotinfo['robot_number']) ? $this_robotinfo['robot_number'] : 'RPG000';
        $this->robot_field = isset($this_robotinfo['robot_field']) ? $this_robotinfo['robot_field'] : 'field';
        $this->robot_class = isset($this_robotinfo['robot_class']) ? $this_robotinfo['robot_class'] : 'master';
        $this->robot_gender = isset($this_robotinfo['robot_gender']) ? $this_robotinfo['robot_gender'] : 'none';
        $this->robot_core = isset($this_robotinfo['robot_core']) ? $this_robotinfo['robot_core'] : false;
        $this->robot_core2 = isset($this_robotinfo['robot_core2']) ? $this_robotinfo['robot_core2'] : false;
        $this->robot_experience = isset($this_robotinfo['robot_experience']) ? $this_robotinfo['robot_experience'] : (isset($this_robotinfo['robot_points']) ? $this_robotinfo['robot_points'] : 0);
        $this->robot_level = isset($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : (!empty($this->robot_experience) ? $this->robot_experience / 1000 : 0) + 1;
        $this->robot_energy = isset($this_robotinfo['robot_energy']) ? $this_robotinfo['robot_energy'] : 1;
        $this->robot_weapons = isset($this_robotinfo['robot_weapons']) ? $this_robotinfo['robot_weapons'] : 10;
        $this->robot_attack = isset($this_robotinfo['robot_attack']) ? $this_robotinfo['robot_attack'] : 1;
        $this->robot_defense = isset($this_robotinfo['robot_defense']) ? $this_robotinfo['robot_defense'] : 1;
        $this->robot_speed = isset($this_robotinfo['robot_speed']) ? $this_robotinfo['robot_speed'] : 1;
        $this->robot_total = isset($this_robotinfo['robot_total']) ? $this_robotinfo['robot_total'] : ($this->robot_energy + $this->robot_attack + $this->robot_defense + $this->robot_speed);
        $this->robot_weaknesses = isset($this_robotinfo['robot_weaknesses']) && is_array($this_robotinfo['robot_weaknesses']) ? $this_robotinfo['robot_weaknesses'] : array();
        $this->robot_resistances = isset($this_robotinfo['robot_resistances']) && is_array($this_robotinfo['robot_resistances']) ? $this_robotinfo['robot_resistances'] : array();
        $this->robot_affinities = isset($this_robotinfo['robot_affinities']) && is_array($this_robotinfo['robot_affinities']) ? $this_robotinfo['robot_affinities'] : array();
        $this->robot_immunities = isset($this_robotinfo['robot_immunities']) && is_array($this_robotinfo['robot_immunities']) ? $this_robotinfo['robot_immunities'] : array();
        $this->robot_item = isset($this_robotinfo['robot_item']) ? $this_robotinfo['robot_item'] : '';
        $this->robot_abilities = isset($this_robotinfo['robot_abilities']) && is_array($this_robotinfo['robot_abilities']) ? $this_robotinfo['robot_abilities'] : array();
        $this->robot_attachments = isset($this_robotinfo['robot_attachments']) && is_array($this_robotinfo['robot_attachments']) ? $this_robotinfo['robot_attachments'] : array();
        $this->robot_quotes = isset($this_robotinfo['robot_quotes']) && is_array($this_robotinfo['robot_quotes']) ? $this_robotinfo['robot_quotes'] : array();
        $this->robot_status = isset($this_robotinfo['robot_status']) ? $this_robotinfo['robot_status'] : 'active';
        $this->robot_side = isset($this_robotinfo['robot_side']) ? $this_robotinfo['robot_side'] : $this->player->player_side;
        $this->robot_direction = isset($this_robotinfo['robot_direction']) ? $this_robotinfo['robot_direction'] : $this->player->player_direction;
        $this->robot_position = isset($this_robotinfo['robot_position']) ? $this_robotinfo['robot_position'] : 'bench';
        $this->robot_stance = isset($this_robotinfo['robot_stance']) ? $this_robotinfo['robot_stance'] : 'base';
        $this->robot_rewards = isset($this_robotinfo['robot_rewards']) ? $this_robotinfo['robot_rewards'] : array();
        $this->robot_functions = isset($this_robotinfo['robot_functions']) ? $this_robotinfo['robot_functions'] : 'robots/robot.php';
        $this->robot_image = isset($this_robotinfo['robot_image']) ? $this_robotinfo['robot_image'] : $this->robot_token;
        $this->robot_image_size = isset($this_robotinfo['robot_image_size']) ? $this_robotinfo['robot_image_size'] : 40;
        $this->robot_image_overlay = isset($this_robotinfo['robot_image_overlay']) ? $this_robotinfo['robot_image_overlay'] : array();
        $this->robot_image_alts = isset($this_robotinfo['robot_image_alts']) ? $this_robotinfo['robot_image_alts'] : array();
        $this->robot_frame = isset($this_robotinfo['robot_frame']) ? $this_robotinfo['robot_frame'] : 'base';
        $this->robot_frame_offset = isset($this_robotinfo['robot_frame_offset']) ? $this_robotinfo['robot_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->robot_frame_classes = isset($this_robotinfo['robot_frame_classes']) ? $this_robotinfo['robot_frame_classes'] : '';
        $this->robot_frame_styles = isset($this_robotinfo['robot_frame_styles']) ? $this_robotinfo['robot_frame_styles'] : '';
        $this->robot_detail_styles = isset($this_robotinfo['robot_detail_styles']) ? $this_robotinfo['robot_detail_styles'] : '';
        $this->robot_original_player = isset($this_robotinfo['robot_original_player']) ? $this_robotinfo['robot_original_player'] : $this->player_token;
        $this->robot_string = isset($this_robotinfo['robot_string']) ? $this_robotinfo['robot_string'] : $this->robot_id.'_'.$this->robot_token;

        // Collect any functions associated with this ability
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->robot_functions) ? $this->robot_functions : 'robots/robot.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->robot_function = isset($robot['robot_function']) ? $robot['robot_function'] : function(){};
        $this->robot_function_onload = isset($robot['robot_function_onload']) ? $robot['robot_function_onload'] : function(){};
        $this->robot_function_choices_abilities = isset($robot['robot_function_choices_abilities']) ? $robot['robot_function_choices_abilities'] : function(){};
        unset($robot);

        // Define the internal robot base values using the robots index array
        $this->robot_base_key = isset($this_robotinfo['robot_base_key']) ? $this_robotinfo['robot_base_key'] : $this->robot_key;
        $this->robot_base_name = isset($this_robotinfo['robot_base_name']) ? $this_robotinfo['robot_base_name'] : $this->robot_name;
        $this->robot_base_description = isset($this_robotinfo['robot_base_description']) ? $this_robotinfo['robot_base_description'] : $this->robot_description;
        $this->robot_base_number = isset($this_robotinfo['robot_base_number']) ? $this_robotinfo['robot_base_number'] : $this->robot_number;
        $this->robot_base_core = isset($this_robotinfo['robot_base_core']) ? $this_robotinfo['robot_base_core'] : $this->robot_core;
        $this->robot_base_core2 = isset($this_robotinfo['robot_base_core2']) ? $this_robotinfo['robot_base_core2'] : $this->robot_core2;
        $this->robot_base_experience = isset($this_robotinfo['robot_base_experience']) ? $this_robotinfo['robot_base_experience'] : $this->robot_experience;
        $this->robot_base_level = isset($this_robotinfo['robot_base_level']) ? $this_robotinfo['robot_base_level'] : $this->robot_level;
        $this->robot_base_energy = isset($this_robotinfo['robot_base_energy']) ? $this_robotinfo['robot_base_energy'] : $this->robot_energy;
        $this->robot_base_weapons = isset($this_robotinfo['robot_base_weapons']) ? $this_robotinfo['robot_base_weapons'] : $this->robot_weapons;
        $this->robot_base_attack = isset($this_robotinfo['robot_base_attack']) ? $this_robotinfo['robot_base_attack'] : $this->robot_attack;
        $this->robot_base_defense = isset($this_robotinfo['robot_base_defense']) ? $this_robotinfo['robot_base_defense'] : $this->robot_defense;
        $this->robot_base_speed = isset($this_robotinfo['robot_base_speed']) ? $this_robotinfo['robot_base_speed'] : $this->robot_speed;
        $this->robot_base_total = isset($this_robotinfo['robot_base_total']) ? $this_robotinfo['robot_base_total'] : ($this->robot_base_energy + $this->robot_base_attack + $this->robot_base_defense + $this->robot_base_speed);
        $this->robot_base_weaknesses = isset($this_robotinfo['robot_base_weaknesses']) ? $this_robotinfo['robot_base_weaknesses'] : $this->robot_weaknesses;
        $this->robot_base_resistances = isset($this_robotinfo['robot_base_resistances']) ? $this_robotinfo['robot_base_resistances'] : $this->robot_resistances;
        $this->robot_base_affinities = isset($this_robotinfo['robot_base_affinities']) ? $this_robotinfo['robot_base_affinities'] : $this->robot_affinities;
        $this->robot_base_immunities = isset($this_robotinfo['robot_base_immunities']) ? $this_robotinfo['robot_base_immunities'] : $this->robot_immunities;
        $this->robot_base_item = isset($this_robotinfo['robot_base_item']) ? $this_robotinfo['robot_base_item'] : $this->robot_item;
        $this->robot_base_abilities = isset($this_robotinfo['robot_base_abilities']) ? $this_robotinfo['robot_base_abilities'] : $this->robot_abilities;
        $this->robot_base_attachments = isset($this_robotinfo['robot_base_attachments']) ? $this_robotinfo['robot_base_attachments'] : $this->robot_attachments;
        $this->robot_base_quotes = isset($this_robotinfo['robot_base_quotes']) ? $this_robotinfo['robot_base_quotes'] : $this->robot_quotes;
        $this->robot_base_image = isset($this_robotinfo['robot_base_image']) ? $this_robotinfo['robot_base_image'] : $this->robot_token;
        $this->robot_base_image_size = isset($this_robotinfo['robot_base_image_size']) ? $this_robotinfo['robot_base_image_size'] : $this->robot_image_size;
        $this->robot_base_image_overlay = isset($this_robotinfo['robot_base_image_overlay']) ? $this_robotinfo['robot_base_image_overlay'] : $this->robot_image_overlay;

        // Limit all stats to 999999 for display purposes (and balance I guess)
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

        // If this is a player-controlled robot, load abilities and image from session
        if ($this->player->player_side == 'left' && empty($this->flags['apply_session_abilities'])){

            // Collect the Settings for this robot from the session
            $temp_robot_settings = rpg_game::robot_settings($this->player_token, $this->robot_token);

            // Parse the abilities for this robot from the session
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

            // Set the session ability flag to true
            $this->flags['apply_session_abilities'] = true;

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
        $temp_function = $this->robot_function_onload;
        $temp_result = $temp_function(array(
            'this_field' => isset($this->battle->battle_field) ? $this->battle->battle_field : false,
            'this_battle' => $this->battle,
            'this_player' => $this->player,
            'this_robot' => $this
            ));

        // Return true on success
        return true;

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
    public function reset_energy($value){
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
    public function get_base_stat($stat){ return $this->get_info('robot_base_'.$stat); }
    public function set_base_stat($stat, $value){ $this->set_info('robot_base_'.$stat, $value); }

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

    public function get_attachments(){ return $this->get_info('robot_attachments'); }
    public function set_attachments($value){ $this->set_info('robot_attachments', $value); }
    public function has_attachments(){ return $this->get_info('robot_attachments') ? true : false; }
    public function has_attachment($token){ return $this->get_info('robot_attachments') ? true : false; }
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

    public function get_frame_offset(){ return $this->get_info('robot_frame_offset'); }
    public function set_frame_offset($value){ $this->set_info('robot_frame_offset', $value); }

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

    /**
     * Generate an ability ID for this robot based on the ability slot key
     * @param int $ability_key (optional)
     * @return int
     */
    public function get_ability_id($ability_key = 0){
        return rpg_ability::generate_id($this->get_id(), $ability_key);
    }

    /**
     * Generate an attachment ID for this robot based on the attachment token
     * @param string $attachment_token (optional)
     * @return int
     */
    public function get_attachment_id($attachment_token = 'attachment'){
        return rpg_attachment::generate_id($this->get_id(), $attachment_token);
    }

    /**
     * Generate an item ID for this robot based on the item slot
     * @param int $item_slot (optional)
     * @return int
     */
    public function get_item_id($item_slot = 0){
        return rpg_item::generate_id($this->get_id(), $item_slot);
    }


    // -- STARTUP FUNCTIONS -- //

    /**
     * Add a new ability to this robot's object data and apply startup actions
     * @param array $ability_info
     * @param int $ability_key
     * @param bool $apply_bonuses (optional)
     * @return bool
     */
    public function add_ability($ability_info, $ability_key = 0, $apply_bonuses = false){
        if (empty($ability_info['ability_id']) || empty($ability_info['ability_token'])){ return false; }
        $this_battle = rpg_battle::get_battle();
        $ability_id = $ability_info['ability_id'];
        $ability_token = $ability_info['ability_token'];
        $ability_key = !empty($ability_key) ? $ability_key : $this->get_counter('abilities_total');
        $ability_info['player_id'] = $this->player->get_id();
        $ability_info['player_token'] = $this->player->get_token();
        $ability_info['robot_id'] = $this->get_id();
        $ability_info['robot_token'] = $this->get_token();
        $this_battle->add_ability($this->player, $this, $ability_info);
        $this_ability = $this_battle->get_ability($ability_id);
        //if ($apply_bonuses){ $this_ability->apply_stat_bonuses(); }
        $robot_abilities = $this->get_abilities();
        $robot_abilities[$ability_key] = array('ability_id' => $this_ability->ability_id, 'ability_token' => $this_ability->ability_token);
        $this->set_abilities($robot_abilities);
        $this->update_variables();
        return true;
    }


    /**
     * Request a reference to one of this robot's abilities, either by id, token, string, or lookup
     * @param  mixed $ability
     * @return rpg_ability
     */
    public function get_ability($ability){

        // Automatically return false on empty
        if (empty($ability)){ return false; }

        // Collect a reference to the global battle object
        $this_battle = rpg_battle::get_battle();

        // Create the base ability filter with this robot's id
        $ability_filter = array('robot_id' => $this->get_id());

        // If this was a preformatted filter array
        if (is_array($ability)){
            $ability_filter = array_merge($ability_filter, $ability);
        }
        // Else if this was a lookup string in id_token format
        elseif (preg_match('/^[0-9]+_[-a-z0-9]+$/', $ability)){
            list($id, $token) = explode('_', $ability);
            $ability_filter['ability_id'] = $id;
            $ability_filter['ability_token'] = $token;
        }
        // Else if this was a lookup id in numeric format
        elseif (preg_match('/^[0-9]+$/', $ability)){
            $ability_filter['ability_id'] = $ability;
        }
        // Else if this was a lookup token in string format
        elseif (preg_match('/^[-a-z0-9]+$/', $ability)){
            $ability_filter['ability_token'] = $ability;
        }

        // Return false if neither the id nor token were provided
        if (empty($ability_filter['ability_id']) && empty($ability_filter['ability_token'])){ return false; }

        // Otherwise, try to collect a reference to the ability via filter
        $this_ability = $this_battle->find_ability($ability_filter);

        // Return the ability value whatever it was
        return $this_ability;

    }


    /**
     * Apply startup stat bonuses to this robot object, usually at the start of the battle
     * @return bool
     */
    public function apply_stat_bonuses(){
        // Pull in the global index
        global $mmrpg_index;

        // Collect references to global objects
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();
        $this_player = $this->player;
        $this_player_token = $this_player->get_token();
        $this_robot = $this;
        $this_robot_token = $this_robot->get_token();

        // Only continue if this hasn't been done already
        if ($this_robot->has_flag('apply_stat_bonuses')){ return false; }


        // -- COLLECT BASE VALUES -- //

        // If this is robot's player is human controlled
        if (!$this_player->is_autopilot() && !$this_robot->is_class('mecha')){

            // Collect this robot's rewards and settings
            $this_settings = rpg_game::robot_settings($this_player_token, $this_robot_token);
            $this_rewards = rpg_game::robot_rewards($this_player_token, $this_robot_token);

            // Update this robot's original player with any session settings
            $original_player = rpg_game::robot_original_player($this_player_token, $this_robot_token);
            $this_robot->set_original_player($original_player);

            // Update this robot's experience with any session rewards
            $this_experience = rpg_game::robot_experience($this_player_token, $this_robot_token);
            $this_robot->set_experience($this_experience);
            $this_robot->set_base_experience($this_experience);

            // Update this robot's level with any session rewards
            $this_level = rpg_game::robot_level($this_player_token, $this_robot_token);
            $this_robot->set_level($this_level);
            $this_robot->set_base_level($this_level);

        }
        // Otherwise, if this player is on autopilot
        else {

            // Create an empty settings and reward array to prevent errors
            $this_settings = !empty($this->values['robot_settings']) ? $this->values['robot_settings'] : array();
            $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

            // Collect this robot's other current values into variables
            $original_player = $this_robot->get_original_player();
            $this_experience = $this_robot->get_experience();
            $this_level = $this_robot->get_level();

        }

        // Calculate required experience for this robot
        $required_experience = rpg_prototype::calculate_experience_required($this_level);

        // If this is a player battle, automatically set all robot levels to the same value
        $player_battle_level = $this_battle->get_value('player_battle_level');
        if (!empty($player_battle_level)){
            $this_experience = $required_experience;
            $this_level = $player_battle_level;
        }

        // If the robot experience is over the required points, level up and reset
        if ($this_experience > $required_experience){
            $level_boost = floor($this_experience / $required_experience);
            $this_experience -= $level_boost * $required_experience;
            $this_level += $level_boost;
        }

        // Fix the level if it's over 100
        if ($this_level > 100){ $this_level = 100;  }

        // Update this robot's experience and level any changes
        $this_robot->set_experience($this_experience);
        $this_robot->set_base_experience($this_experience);
        $this_robot->set_level($this_level);
        $this_robot->set_base_level($this_level);

        // Collect this robot's stats for manipulation
        $this_base_energy = $this_energy = $this_robot->get_energy();
        $this_base_weapons = $this_weapons = $this_robot->get_weapons();
        $this_base_attack = $this_attack = $this_robot->get_attack();
        $this_base_defense = $this_defense = $this_robot->get_defense();
        $this_base_speed = $this_speed = $this_robot->get_speed();

        // Collect this player's stat rewards if any so we can apply
        $this_player_energy = $this_player->get_energy();
        $this_player_weapons = $this_player->get_weapons();
        $this_player_attack = $this_player->get_attack();
        $this_player_defense = $this_player->get_defense();
        $this_player_speed = $this_player->get_speed();

        // Collect the maximum values for each of these stats for later
        $this_max_energy = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($this_base_energy, $this_level);
        $this_max_attack = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($this_base_attack, $this_level);
        $this_max_defense = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($this_base_defense, $this_level);
        $this_max_speed = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($this_base_speed, $this_level);


        // -- LEVEL UP REWARDS -- //

        // If the robot's level is greater than one, increase stats
        if ($this_level > 1){

            // If this robot's level is at the max value or greater, set a flag for later
            if ($this_level >= 100){ $this_robot->set_flag('robot_stat_max_level', true);  }

            // Update the robot stats with a small boost based on experience level
            $this_energy += MMRPG_SETTINGS_STATS_GET_LEVELBOOST($this_base_energy, $this_level);
            $this_attack += MMRPG_SETTINGS_STATS_GET_LEVELBOOST($this_base_attack, $this_level);
            $this_defense += MMRPG_SETTINGS_STATS_GET_LEVELBOOST($this_base_defense, $this_level);
            $this_speed += MMRPG_SETTINGS_STATS_GET_LEVELBOOST($this_base_speed, $this_level);

        }


        // -- BONUS STAT REWARDS -- //

        // Increase this robot's stats by any reward values
        if (!empty($this_rewards['robot_energy'])){ $this_energy += $this_rewards['robot_energy']; }
        if (!empty($this_rewards['robot_attack'])){ $this_attack += $this_rewards['robot_attack']; }
        if (!empty($this_rewards['robot_defense'])){ $this_defense += $this_rewards['robot_defense']; }
        if (!empty($this_rewards['robot_speed'])){ $this_speed += $this_rewards['robot_speed']; }


        // -- BASE STAT OVERFLOW -- //

        // If this robot's energy rating is at the max value or greater, set a flag for later
        if ($this_energy > $this_max_energy){
            $this_robot->set_flag('robot_stat_max_energy', true);
            $max_stat_overflow += $this_energy - $this_max_energy;
            $this_energy = $this_max_energy;
        }

        // If this robot's attack rating is at the max value or greater, set a flag for later
        if ($this_attack > $this_max_attack){
            $this_robot->set_flag('robot_stat_max_attack', true);
            $max_stat_overflow += $this_attack - $this_max_attack;
            $this_attack = $this_max_attack;
        }

        // If this robot's defense rating is at the max value or greater, set a flag for later
        if ($this_defense > $this_max_defense){
            $this_robot->set_flag('robot_stat_max_defense', true);
            $max_stat_overflow += $this_defense - $this_max_defense;
            $this_defense = $this_max_defense;
        }

        // If this robot's speed rating is at the max value or greater, set a flag for later
        if ($this_speed > $this_max_speed){
            $this_robot->set_flag('robot_stat_max_speed', true);
            $max_stat_overflow += $this_speed - $this_max_speed;
            $this_speed = $this_max_speed;
        }


        // -- PLAYER STAT REWARDS -- //

        // If this player has bonuses, apply them on top of the robot stats
        if (!empty($this_player_energy)){ $this_energy += ceil($this_energy * ($this_player_energy / 100)); }
        if (!empty($this_player_attack)){ $this_attack += ceil($this_attack * ($this_player_attack / 100)); }
        if (!empty($this_player_defense)){ $this_defense += ceil($this_defense * ($this_player_defense / 100)); }
        if (!empty($this_player_speed)){ $this_speed += ceil($this_speed * ($this_player_speed / 100)); }


        // -- ROBOT ITEM REWARDS -- //

        // If this robot is holder a relavant item, apply stat upgrades
        $this_robot_item = $this_robot->get_item();
        switch ($this_robot_item){
            // If this robot is holding an Energy Upgrade, double the life energy stat
            case 'item-energy-upgrade' : {
                $this_energy = $this_energy * 2;
                break;
            }
            // Else if this robot is holding a Weapon Upgrade, double the life energy stat
            case 'item-weapon-upgrade' : {
                $this_weapons = $this_weapons * 2;
                break;
            }
        }


        // -- FINAL STAT LIMITS -- //

        // Limit all stats to maximums for display purposes
        if ($this_energy > MMRPG_SETTINGS_STATS_MAX){ $this_energy = MMRPG_SETTINGS_STATS_MAX; }
        if ($this_attack > MMRPG_SETTINGS_STATS_MAX){ $this_attack = MMRPG_SETTINGS_STATS_MAX; }
        if ($this_defense > MMRPG_SETTINGS_STATS_MAX){ $this_defense = MMRPG_SETTINGS_STATS_MAX; }
        if ($this_speed > MMRPG_SETTINGS_STATS_MAX){ $this_speed = MMRPG_SETTINGS_STATS_MAX; }


        // -- UPDATE ROBOT OBJECT -- //

        // Update this robot's stats with any changes
        $this_robot->set_energy($this_energy);
        $this_robot->set_base_energy($this_energy);
        $this_robot->set_attack($this_attack);
        $this_robot->set_base_attack($this_attack);
        $this_robot->set_defense($this_defense);
        $this_robot->set_base_defense($this_defense);
        $this_robot->set_speed($this_speed);
        $this_robot->set_base_speed($this_speed);


        // Create the stat boost flag
        $this_robot->set_flag('apply_stat_bonuses', true);

        // Return true on success
        return true;

    }


    // -- PRINT FUNCTIONS -- //

    // Define public print functions for markup generation
    public function print_number(){ return '<span class="robot_number">'.$this->robot_number.'</span>'; }
    public function print_name(){ return '<span class="robot_name robot_type">'.$this->robot_name.'</span>'; }
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

    public function print_weaknesses($print_empty = false){
        $this_markup = array();
        $short_names = count($this->robot_weaknesses) > 4 ? true : false;
        if (count($this->robot_weaknesses) >= 19){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_none">All Types</span>';
        } elseif (!empty($this->robot_weaknesses)) {
            foreach ($this->robot_weaknesses AS $key => $this_type){
                $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'"'.($short_names ? ' title="'.ucfirst($this_type).'"' : '').'>'.ucfirst($short_names ? substr($this_type, 0, 2) : $this_type).'</span>';
            }
        } elseif ($print_empty){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_none">None</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }

    public function print_resistances($print_empty = false){
        $this_markup = array();
        $short_names = count($this->robot_resistances) > 4 ? true : false;
        if (count($this->robot_resistances) >= 19){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_none">All Types</span>';
        } elseif (!empty($this->robot_resistances)) {
            foreach ($this->robot_resistances AS $key => $this_type){
                $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'"'.($short_names ? ' title="'.ucfirst($this_type).'"' : '').'>'.ucfirst($short_names ? substr($this_type, 0, 2) : $this_type).'</span>';
            }
        } elseif ($print_empty){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_none">None</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }

    public function print_affinities($print_empty = false){
        $this_markup = array();
        $short_names = count($this->robot_affinities) > 4 ? true : false;
        if (count($this->robot_affinities) >= 19){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_none">All Types</span>';
        } elseif (!empty($this->robot_affinities)) {
            foreach ($this->robot_affinities AS $key => $this_type){
                $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'"'.($short_names ? ' title="'.ucfirst($this_type).'"' : '').'>'.ucfirst($short_names ? substr($this_type, 0, 2) : $this_type).'</span>';
            }
        } elseif ($print_empty){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_none">None</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }

    public function print_immunities($print_empty = false){
        $this_markup = array();
        $short_names = count($this->robot_immunities) > 4 ? true : false;
        if (count($this->robot_immunities) >= 19){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_none">All Types</span>';
        } elseif (!empty($this->robot_immunities)) {
            foreach ($this->robot_immunities AS $key => $this_type){
                $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'"'.($short_names ? ' title="'.ucfirst($this_type).'"' : '').'>'.ucfirst($short_names ? substr($this_type, 0, 2) : $this_type).'</span>';
            }
        } elseif ($print_empty){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_none">None</span>';
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
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $this->robot_quotes[$quote_type]);
            // Collect the text colour for this robot
            $this_type_token = !empty($this->robot_core) ? $this->robot_core : 'none';
            $this_text_colour = !empty($mmrpg_database_types[$this_type_token]) ? $mmrpg_database_types[$this_type_token]['type_colour_light'] : array(200, 200, 200);
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

    /**
     * Generate the robot scan markup for this frame of battle including stats, weaknesses, resistances, etc.
     * @return string
     */
    public function print_scan_markup(){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // Collect references to current objects
        $this_player = $this->player;
        $this_robot = $this;

        // Collect object tokens for easy reference
        $this_player_token = $this_player->get_token();
        $this_robot_token = $this_robot->get_token();

        // Collect the stat values for display and calculations
        $this_robot_energy = $this_robot->get_energy();
        $this_robot_base_energy = $this_robot->get_base_energy();
        $this_robot_weapons = $this_robot->get_weapons();
        $this_robot_base_weapons = $this_robot->get_base_weapons();
        $this_robot_attack = $this_robot->get_attack();
        $this_robot_base_attack = $this_robot->get_base_attack();
        $this_robot_defense = $this_robot->get_defense();
        $this_robot_base_defense = $this_robot->get_base_defense();
        $this_robot_speed = $this_robot->get_speed();
        $this_robot_base_speed = $this_robot->get_base_speed();

        // Collect the weakness, resistsance, affinity, and immunity text
        $this_robot_weaknesses = $this_robot->print_weaknesses(true);
        $this_robot_resistances = $this_robot->print_resistances(true);
        $this_robot_affinities = $this_robot->print_affinities(true);
        $this_robot_immunities = $this_robot->print_immunities(true);

        // Collect the csv list of abilities for this robot for debug
        $this_robot_abilities = implode(', ', $this_robot->get_abilities());

        // Define the base stat totals and padding for calculations
        $temp_stat_padding_total = 300;
        $temp_stat_counter_total = $this_robot_energy + $this_robot_attack + $this_robot_defense + $this_robot_speed;
        $temp_stat_counter_base_total = $this_robot_base_energy + $this_robot_base_attack + $this_robot_base_defense + $this_robot_base_speed;

        // Define the energy values and padding for display
        $temp_energy_padding = ceil(($this_robot_energy / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_energy_base_padding = ceil(($this_robot_base_energy / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_energy_base_padding = $temp_energy_base_padding - $temp_energy_padding;

        // Define the attack values and padding for display
        $temp_attack_padding = ceil(($this_robot_attack / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_attack_base_padding = ceil(($this_robot_base_attack / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_attack_base_padding = $temp_attack_base_padding - $temp_attack_padding;
        if ($temp_attack_padding < 1){ $temp_attack_padding = 0; }
        if ($temp_attack_base_padding < 1){ $temp_attack_base_padding = 0; }

        // Define the defense values and padding for display
        $temp_defense_padding = ceil(($this_robot_defense / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_defense_base_padding = ceil(($this_robot_base_defense / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_defense_base_padding = $temp_defense_base_padding - $temp_defense_padding;
        if ($temp_defense_padding < 1){ $temp_defense_padding = 0; }
        if ($temp_defense_base_padding < 1){ $temp_defense_base_padding = 0; }

        // Define the speed values and padding for display
        $temp_speed_padding = ceil(($this_robot_speed / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_speed_base_padding = ceil(($this_robot_base_speed / $temp_stat_counter_base_total) * $temp_stat_padding_total);
        $temp_speed_base_padding = $temp_speed_base_padding - $temp_speed_padding;
        if ($temp_speed_padding < 1){ $temp_speed_padding = 0; }
        if ($temp_speed_base_padding < 1){ $temp_speed_base_padding = 0; }

        // Generate the event markup showing the scanned robot's data
        ob_start();
        ?>
            <table class="full">
                <colgroup>
                    <col width="20%" />
                    <col width="43%" />
                    <col width="4%" />
                    <col width="13%" />
                    <col width="20%" />
                </colgroup>
                <tbody>
                    <tr>
                        <td class="left">Name  : </td>
                        <td  class="right"><?= !$this_robot->has_darkness() ? $this_robot->print_number() : '' ?> <?= $this_robot->print_name() ?></td>
                        <td class="center">&nbsp;</td>
                        <td class="left">Core : </td>
                        <td  class="right"><?= $this_robot->print_core() ?></td>
                    </tr>
                    <tr>
                        <td class="left">Weaknesses : </td>
                        <td  class="right"><?= !empty($this_robot_weaknesses) ? $this_robot_weaknesses : '<span class="robot_weakness">None</span>' ?></td>
                        <td class="center">&nbsp;</td>
                        <td class="left">Energy : </td>
                        <td  class="right"><span title="<?= ceil(($this_robot_energy / $this_robot_base_energy) * 100).'% | '.$this_robot_energy.' / '.$this_robot_base_energy ?>"data-tooltip-type="robot_type robot_type_energy" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_energy_base_padding ?>px;"><span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= $temp_energy_padding ?>px;"><?= $this_robot_energy ?></span></span></td>
                    </tr>
                    <tr>
                        <td class="left">Resistances : </td>
                        <td  class="right"><?= !empty($this_robot_resistances) ? $this_robot_resistances : '<span class="robot_resistance">None</span>' ?></td>
                        <td class="center">&nbsp;</td>
                        <td class="left">Attack : </td>
                        <td  class="right"><span title="<?= ceil(($this_robot_attack / $this_robot_base_attack) * 100).'% | '.$this_robot_attack.' / '.$this_robot_base_attack ?>"data-tooltip-type="robot_type robot_type_attack" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_attack_base_padding ?>px;"><span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= $temp_attack_padding ?>px;"><?= $this_robot_attack ?></span></span></td>
                    </tr>
                    <tr>
                        <td class="left">Affinities : </td>
                        <td  class="right"><?= !empty($this_robot_affinities) ? $this_robot_affinities : '<span class="robot_affinity">None</span>' ?></td>
                        <td class="center">&nbsp;</td>
                        <td class="left">Defense : </td>
                        <td  class="right"><span title="<?= ceil(($this_robot_defense / $this_robot_base_defense) * 100).'% | '.$this_robot_defense.' / '.$this_robot_base_defense ?>"data-tooltip-type="robot_type robot_type_defense" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_defense_base_padding ?>px;"><span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= $temp_defense_padding ?>px;"><?= $this_robot_defense ?></span></span></td>
                    </tr>
                    <tr>
                        <td class="left">Immunities : </td>
                        <td  class="right"><?= !empty($this_robot_immunities) ? $this_robot_immunities : '<span class="robot_immunity">None</span>' ?></td>
                        <td class="center">&nbsp;</td>
                        <td class="left">Speed : </td>
                        <td  class="right"><span title="<?= ceil(($this_robot_speed / $this_robot_base_speed) * 100).'% | '.$this_robot_speed.' / '.$this_robot_base_speed ?>"data-tooltip-type="robot_type robot_type_speed" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_speed_base_padding ?>px;"><span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= $temp_speed_padding ?>px;"><?= $this_robot_speed ?></span></span></td>
                    </tr>
                    <?php if (MMRPG_CONFIG_DEBUG_MODE){ ?>
                    <tr>
                        <td class="left" colspan="5">
                            Abilities :
                            <?= !empty($this_robot_abilities) ? $this_robot_abilities : '<span class="ability_name">None</span>' ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php
        $this_markup .= preg_replace('#\s+#', ' ', trim(ob_get_clean()));

        // Return the generated markup and robot data
        return $this_markup;

    }

    // Define a static function for printing out the robot's database markup
    public static function print_database_markup($robot_info, $print_options = array(), $cache_markup = false){

        // Define the markup variable
        $this_markup = '';

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $mmrpg_database_players, $mmrpg_database_items, $mmrpg_database_fields, $mmrpg_database_types;
        global $mmrpg_stat_base_max_value;

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
            if (!isset($print_options['show_stats'])){ $print_options['show_stats'] = true; }
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
            if (!isset($print_options['show_stats'])){ $print_options['show_stats'] = false; }
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
            if (!isset($print_options['show_stats'])){ $print_options['show_stats'] = false; }
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
        $temp_robot_fields = array();
        if (!empty($robot_info['robot_field']) && $robot_info['robot_field'] != 'field'){ $temp_robot_fields[] = $robot_info['robot_field']; }
        if (!empty($robot_info['robot_field2'])){ $temp_robot_fields = array_merge($temp_robot_fields, $robot_info['robot_field2']); }
        if ($temp_robot_fields){
            foreach ($temp_robot_fields AS $key => $token){
                if (!empty($mmrpg_database_fields[$token])){
                    $field_info_array[] = rpg_field::parse_index_info($mmrpg_database_fields[$token]);
                }
            }
        }

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

            global $db;
            $temp_robot_records = array('robot_encountered' => 0, 'robot_defeated' => 0, 'robot_unlocked' => 0, 'robot_summoned' => 0, 'robot_scanned' => 0);
            //$temp_robot_records['player_count'] = $db->get_value("SELECT COUNT(board_id) AS player_count  FROM mmrpg_leaderboard WHERE board_robots LIKE '%[".$robot_info['robot_token'].":%' AND board_points > 0", 'player_count');
            $temp_player_query = "SELECT
                mmrpg_saves.user_id,
                mmrpg_saves.save_values_robot_database,
                mmrpg_leaderboard.board_points
                FROM mmrpg_saves
                LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
                WHERE mmrpg_saves.save_values_robot_database LIKE '%\"{$robot_info['robot_token']}\"%' AND mmrpg_leaderboard.board_points > 0;";
            $temp_player_list = $db->get_array_list($temp_player_query);
            if (!empty($temp_player_list)){
                foreach ($temp_player_list AS $temp_data){
                    $temp_values = !empty($temp_data['save_values_robot_database']) ? json_decode($temp_data['save_values_robot_database'], true) : array();
                    $temp_entry = !empty($temp_values[$robot_info['robot_token']]) ? $temp_values[$robot_info['robot_token']] : array();
                    foreach ($temp_robot_records AS $temp_record => $temp_count){
                        if (!empty($temp_entry[$temp_record])){ $temp_robot_records[$temp_record] += $temp_entry[$temp_record]; }
                    }
                }
            }
            $temp_values = array();
            //echo '<pre>'.print_r($temp_robot_records, true).'</pre>';

        }

        // Define the common stat container variables
        $stat_container_percent = 74;
        $stat_base_max_value = 2000;
        $stat_padding_area = 76;
        if (!empty($mmrpg_stat_base_max_value[$robot_info['robot_class']])){ $stat_base_max_value = $mmrpg_stat_base_max_value[$robot_info['robot_class']]; }
        elseif ($robot_info['robot_class'] == 'master'){ $stat_base_max_value = 400; }
        elseif ($robot_info['robot_class'] == 'mecha'){ $stat_base_max_value = 400; }
        elseif ($robot_info['robot_class'] == 'boss'){ $stat_base_max_value = 2000; }

        // If this is a mecha class, do not show potential stat totals... for now
        //if ($robot_info['robot_class'] != 'master'){
        //  $print_options['show_stats'] = false;
        //}

        // Define the variable to hold compact footer link markup
        $compact_footer_link_markup = array();
        //$compact_footer_link_markup[] = '<a class="link link_permalink" href="'.$database_category_robot_url.'">+ Huh?</a>';

        // Add a link to the sprites in the compact footer markup
        if (!in_array($robot_image_token, $default_robot_class_tokens)){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#sprites">#Sprites</a>'; }
        if (!empty($robot_info['robot_quotes']['battle_start'])){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#quotes">#Quotes</a>'; }
        if (!empty($robot_info['robot_description2'])){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#description">#Description</a>'; }
        if (!empty($robot_info['robot_abilities'])){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#abilities">#Abilities</a>'; }
        $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#stats">#Stats</a>';
        $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#records">#Records</a>';

        /*
        $compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'">View More</a>';
        */

        // Start the output buffer
        ob_start();
        /*<div class="database_container database_<?= $robot_class_token ?>_container database_<?= $print_options['layout_style'] ?>_container" data-token="<?= $robot_info['robot_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">*/
        ?>
        <div class="database_container layout_<?= str_replace('website_', '', $print_options['layout_style']) ?>" data-token="<?= $robot_info['robot_token']?>">

            <?php if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?= $robot_info['robot_token'] ?>"></a>
            <?php endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $robot_info['robot_token']?>">

                <?php if($print_options['show_mugshot']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <?php if($print_options['show_mugshot']): ?>
                            <?php if($print_options['show_key'] !== false): ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.$robot_info['robot_key'] ?></div>
                            <?php endif; ?>
                            <?php if (!in_array($robot_image_token, $default_robot_class_tokens)){ ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: url(i/r/<?= $robot_image_token ?>/mr<?= $robot_image_size ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?>'s Mugshot</div></div>
                            <?php } else { ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active">No Image</div></div>
                            <?php } ?>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $robot_header_types ?> <?= (!$print_options['show_mugshot']) ? 'nomug' : '' ?>" style="<?= (!$print_options['show_mugshot']) ? 'margin-left: 0;' : '' ?>">
                        <?php if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="<?= $database_category_robot_url ?>"><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></a>
                        <?php else: ?>
                            <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Data
                        <?php endif; ?>
                        <div class="header_core robot_type"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'mecha' ? ' Type' : ' Core' ?></div>
                    </h2>
                    <div class="body body_left <?= !$print_options['show_mugshot'] ? 'fullsize' : '' ?>">
                        <table class="full">
                            <colgroup>
                                <?php if($print_options['layout_style'] == 'website'): ?>
                                    <col width="48%" />
                                    <col width="1%" />
                                    <col width="48%" />
                                <?php else: ?>
                                    <col width="40%" />
                                    <col width="1%" />
                                    <col width="59%" />
                                <?php endif; ?>
                            </colgroup>
                            <tbody>
                                <?php if($print_options['layout_style'] != 'event'): ?>
                                    <tr>
                                        <td  class="right">
                                            <label>Name :</label>
                                            <span class="robot_type" style="width: auto;"><?= $robot_info['robot_name']?></span>
                                            <?php if (!empty($robot_info['robot_generation'])){ ?><span class="robot_type" style="width: auto;"><?= $robot_info['robot_generation']?> Gen</span><?php } ?>
                                        </td>
                                        <td></td>
                                        <td class="right">
                                            <?php
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
                                <?php endif; ?>
                                <tr>
                                    <td  class="right">
                                        <label>Model :</label>
                                        <span class="robot_type"><?= $robot_info['robot_number']?></span>
                                    </td>
                                    <td></td>
                                    <td  class="right">
                                        <label>Class :</label>
                                        <span class="robot_type"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Type :</label>
                                        <?php if($print_options['layout_style'] != 'event'): ?>
                                            <?php if(!empty($robot_info['robot_core2'])): ?>
                                                <span class="robot_type type_<?= $robot_info['robot_core'].'_'.$robot_info['robot_core2'] ?>">
                                                    <a href="<?= $database_category_url ?><?= $robot_info['robot_core'] ?>/"><?= ucfirst($robot_info['robot_core']) ?></a> /
                                                    <a href="<?= $database_category_url ?><?= $robot_info['robot_core2'] ?>/"><?= ucfirst($robot_info['robot_core2']) ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                                                </span>
                                            <?php else: ?>
                                                <a href="<?= $database_category_url ?><?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/" class="robot_type type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="robot_type type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td></td>
                                    <td  class="right">
                                        <label><?= empty($field_info_array) || count($field_info_array) == 1 ? 'Field' : 'Fields' ?> :</label>
                                        <?php
                                        // Loop through the robots fields if available
                                        if (!empty($field_info_array)){
                                            foreach ($field_info_array AS $key => $field_info){
                                                ?>
                                                    <?php if($print_options['layout_style'] != 'event'): ?>
                                                        <a href="<?= $database_url ?>fields/<?= $field_info['field_token'] ?>/" class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></a>
                                                    <?php else: ?>
                                                        <span class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></span>
                                                    <?php endif; ?>
                                                <?php
                                            }
                                        }
                                        // Otherwise, print an empty field
                                        else {
                                            ?>
                                                <span class="field_type">&hellip;</span>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Energy :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <?php if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_energy" style="padding-left: <?= round( ( ($robot_info['robot_energy'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_energy'] ?></span></span>
                                            <?php else: ?>
                                                <span class="robot_stat type_energy" style="padding-left: <?= round( ( ($robot_info['robot_energy'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_energy'] ?></span></span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td></td>
                                    <td class="right">
                                        <label>Weaknesses :</label>
                                        <?php
                                        if (!empty($robot_info['robot_weaknesses'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_weakness.'/" class="robot_weakness robot_type type_'.$robot_weakness.'">'.$mmrpg_database_types[$robot_weakness]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_weakness robot_type type_'.$robot_weakness.'">'.$mmrpg_database_types[$robot_weakness]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_weakness robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Attack :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <?php if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_attack" style="padding-left: <?= round( ( ($robot_info['robot_attack'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_attack'] ?></span></span>
                                            <?php else: ?>
                                                <span class="robot_stat type_attack" style="padding-left: <?= round( ( ($robot_info['robot_attack'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_attack'] ?></span></span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td></td>
                                    <td class="right">
                                        <label>Resistances :</label>
                                        <?php
                                        if (!empty($robot_info['robot_resistances'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_resistance.'/" class="robot_resistance robot_type type_'.$robot_resistance.'">'.$mmrpg_database_types[$robot_resistance]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_resistance robot_type type_'.$robot_resistance.'">'.$mmrpg_database_types[$robot_resistance]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_resistance robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label>Defense :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <?php if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_defense" style="padding-left: <?= round( ( ($robot_info['robot_defense'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_defense'] ?></span></span>
                                            <?php else: ?>
                                                <span class="robot_stat type_defense" style="padding-left: <?= round( ( ($robot_info['robot_defense'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_defense'] ?></span></span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td></td>
                                    <td class="right">
                                        <label>Affinities :</label>
                                        <?php
                                        if (!empty($robot_info['robot_affinities'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_affinity.'/" class="robot_affinity robot_type type_'.$robot_affinity.'">'.$mmrpg_database_types[$robot_affinity]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_affinity robot_type type_'.$robot_affinity.'">'.$mmrpg_database_types[$robot_affinity]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_affinity robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Speed :</label>
                                        <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                            <?php if(false && $print_options['layout_style'] == 'website_compact'): ?>
                                                <span class="robot_stat type_speed" style="padding-left: <?= round( ( ($robot_info['robot_speed'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_speed'] ?></span></span>
                                            <?php else: ?>
                                                <span class="robot_stat type_speed" style="padding-left: <?= round( ( ($robot_info['robot_speed'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_speed'] ?></span></span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td></td>
                                    <td class="right">
                                        <label>Immunities :</label>
                                        <?php
                                        if (!empty($robot_info['robot_immunities'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_immunity.'/" class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_database_types[$robot_immunity]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_database_types[$robot_immunity]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_immunity robot_type type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <?php if(false && ($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact')): ?>

                                    <tr>
                                        <td class="right">
                                            <label>Total :</label>
                                            <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                                                <?php if($print_options['layout_style'] == 'website_compact' && $robot_info['robot_total'] < $stat_base_max_value): ?>
                                                    <span class="robot_stat type_empty">
                                                        <span class="robot_stat type_none" style="padding-left: <?= round( ( ($robot_info['robot_total'] / $stat_base_max_value) * $stat_padding_area ), 4) ?>%;"><span><?= $robot_info['robot_total'] ?></span></span>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="robot_stat type_none" style="padding-left: <?= $stat_padding_area ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_total'] ?></span></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td></td>
                                        <td class="right"><?/*
                                            <label>Immunities :</label>
                                            <?php
                                            if (!empty($robot_info['robot_immunities'])){
                                                $temp_string = array();
                                                foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_immunity.'/" class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_database_types[$robot_immunity]['type_name'].'</a>'; }
                                                    else { $temp_string[] = '<span class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_database_types[$robot_immunity]['type_name'].'</span>'; }
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<span class="robot_immunity robot_type type_none">None</span>';
                                            }
                                            ?>*/?>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                <?php if($print_options['layout_style'] == 'event'): ?>

                                    <?php
                                    // Define the search and replace arrays for the robot quotes
                                    $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
                                    $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
                                    ?>
                                    <tr>
                                        <td colspan="3" class="center" style="font-size: 13px; padding: 5px 0; ">
                                            <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

                <?php if($print_options['layout_style'] == 'website'): ?>

                    <?php
                    // Define the various tabs we are able to scroll to
                    $section_tabs = array();
                    if ($print_options['show_sprites']){ $section_tabs[] = array('sprites', 'Sprites', false); }
                    if ($print_options['show_quotes']){ $section_tabs[] = array('quotes', 'Quotes', false); }
                    if ($print_options['show_description']){ $section_tabs[] = array('description', 'Description', false); }
                    if ($print_options['show_abilities']){ $section_tabs[] = array('abilities', 'Abilities', false); }
                    if ($print_options['show_stats']){ $section_tabs[] = array('stats', 'Stats', false); }
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

                    <div class="section_tabs">
                        <?php
                        foreach($section_tabs AS $tab){
                            echo '<a class="link_inline link_'.$tab[0].' '.($tab[2] ? 'active' : '').'" href="'.$temp_url.'#'.$tab[0].'" data-anchor="#'.$tab[0].'"><span class="wrap">'.$tab[1].'</span></a>';
                        }
                        ?>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_sprites']): ?>

                    <?php

                    // Start the output buffer and prepare to collect sprites
                    ob_start();

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
                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_robot_image_token.'" data-frame="mugshot" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
                                echo '<img style="margin-left: 0;" data-tooltip="'.$temp_title.'" src="i/r/'.$temp_robot_image_token.'/m'.$temp_direction2.$robot_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                            echo '</div>';
                        }


                        // Loop through the different frames and print out the sprite sheets
                        foreach ($robot_sprite_frames AS $this_key => $this_frame){
                            $margin_left = ceil((0 - $this_key) * $robot_sprite_size);
                            $frame_relative = $this_frame;
                            //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($robot_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                            $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_direction2 = substr($temp_direction, 0, 1);
                                $temp_embed = '[robot:'.$temp_direction.':'.$frame_relative.']{'.$temp_robot_image_token.'}';
                                $temp_title = $temp_robot_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                //$image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
                                //if ($temp_sheet > 1){ $temp_robot_image_token .= '-'.$temp_sheet; }
                                echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_robot_image_token.'" data-frame="'.$frame_relative.'" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="i/r/'.$temp_robot_image_token.'/s'.$temp_direction2.$robot_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                        }

                    }

                    // Collect the sprite markup from the output buffer for later
                    $this_sprite_markup = ob_get_clean();

                    ?>

                    <h2 id="sprites" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left; overflow: hidden; height: auto;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Sprites
                        <span class="header_links image_link_container">
                            <span class="images" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>"><?php
                                // Loop though and print links for the alts
                                $alt_type_base = 'robot_type type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').' ';
                                foreach ($temp_alts_array AS $alt_key => $alt_info){
                                    $alt_type = '';
                                    $alt_style = '';
                                    $alt_title = $alt_info['name'];
                                    $alt_type2 = $alt_type_base;
                                    if (preg_match('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', $alt_info['name'])){
                                        $alt_type = strtolower(preg_replace('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', '$1', $alt_info['name']));
                                        $alt_name = '&bull;'; //ucfirst($alt_type); //substr(ucfirst($alt_type), 0, 2);
                                        $alt_type = 'robot_type type_'.$alt_type.' core_type ';
                                        $alt_type2 = 'robot_type type_'.$alt_type.' ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key == 0 ? $robot_info['robot_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : ''); //$alt_key == 0 ? $robot_info['robot_name'] : $robot_info['robot_name'].' Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'robot_type type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($robot_info['robot_core'] == 'copy' && $alt_key == 0){ $alt_type = 'robot_type type_empty '; }
                                    }

                                    echo '<a href="#" data-tooltip="'.$alt_title.'" data-tooltip-type="'.$alt_type2.'" class="link link_image '.($alt_key == 0 ? 'link_active ' : '').'" data-image="'.$alt_info['image'].'">';
                                    echo '<span class="'.$alt_type.'" style="'.$alt_style.'">'.$alt_name.'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                            <span class="pipe" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>">|</span>
                            <span class="directions"><?php
                                // Loop though and print links for the alts
                                foreach (array('right', 'left') AS $temp_key => $temp_direction){
                                    echo '<a href="#" data-tooltip="'.ucfirst($temp_direction).' Facing Sprites" data-tooltip-type="'.$alt_type_base.'" class="link link_direction '.($temp_key == 0 ? 'link_active' : '').'" data-direction="'.$temp_direction.'">';
                                    echo '<span class="ability_type ability_type_empty" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ">'.ucfirst($temp_direction).'</span>';
                                    echo '</a>';
                                }
                                ?></span>
                        </span>
                    </h2>
                    <div id="sprites_body" class="body body_full" style="margin: 0; padding: 10px; min-height: 10px;">
                        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                            <?= $this_sprite_markup ?>
                        </div>
                        <?php
                        // Define the editor title based on ID
                        $temp_editor_title = 'Undefined';
                        $temp_final_divider = '<span style="color: #565656;"> | </span>';
                        if (!empty($robot_info['robot_image_editor'])){
                            $temp_break = false;
                            if ($robot_info['robot_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 110){ $temp_break = true; $temp_editor_title = 'MetalMarioX100 / EliteP1</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 18){ $temp_break = true; $temp_editor_title = 'Sean Adamson / MetalMan</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 4117){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 3842){ $temp_break = true; $temp_editor_title = 'Miki Bossman / MegaBossMan</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 5161){ $temp_break = true; $temp_editor_title = 'The Zion / maistir1234</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            if ($temp_break){ $temp_final_divider = '<br />'; }
                        }
                        $temp_is_capcom = true;
                        $temp_is_original = array('disco', 'rhythm', 'flutter-fly', 'flutter-fly-2', 'flutter-fly-3');
                        if (in_array($robot_info['robot_token'], $temp_is_original)){ $temp_is_capcom = false; }
                        if ($temp_is_capcom){
                            echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Artwork by <strong>Capcom</strong></p>'."\n";
                        } else {
                            echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Character by <strong>Adrian Marceau</strong></p>'."\n";
                        }
                        ?>
                    </div>

                    <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                            <a class="link link_permalink" href="<?= $database_category_robot_url ?>#sprites" rel="permalink">#Sprites</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if($print_options['show_quotes']): ?>

                    <h2 id="quotes" class="header header_left <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Quotes
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <?php
                        // Define the search and replace arrays for the robot quotes
                        $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
                        $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
                        ?>
                        <table class="full" style="margin: 5px auto 10px;">
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

                    <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                            <a class="link link_permalink" href="<?= $database_category_robot_url ?>#quotes" rel="permalink">#Quotes</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if($print_options['show_description'] && !empty($robot_info['robot_description2'])): ?>

                    <h2 id="description" class="header header_left <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left; ">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Description
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="robot_description" style="text-align: left; padding: 0 4px;"><?= $robot_info['robot_description2'] ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                            <a class="link link_permalink" href="<?= $database_category_robot_url ?>#description" rel="permalink">#Description</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if($print_options['show_abilities']): ?>

                    <h2 id="abilities" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Abilities
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 2px 3px; min-height: 10px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="ability_container">
                                        <?php

                                        // Define the robot ability class and collect the cores for testing
                                        $robot_ability_class = !empty($robot_info['robot_class']) ? $robot_info['robot_class'] : 'master';
                                        $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
                                        $robot_ability_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : false;
                                        $robot_ability_list = !empty($robot_info['robot_abilities']) ? $robot_info['robot_abilities'] : array();
                                        $robot_ability_rewards = !empty($robot_info['robot_rewards']['abilities']) ? $robot_info['robot_rewards']['abilities'] : array();

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
                                                if (in_array($this_token, $robot_ability_core_list)){ $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }
                                                else { $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }

                                            }
                                        }
                                        $robot_ability_rewards = $new_ability_rewards;

                                        //die('<pre>'.print_r($robot_ability_rewards, true).'</pre>');

                                        if (!empty($robot_ability_rewards)){
                                            $temp_string = array();
                                            $ability_key = 0;
                                            $ability_method_key = 0;
                                            $ability_method = '';
                                            foreach ($robot_ability_rewards AS $this_info){
                                                if (!isset($temp_abilities_index[$this_info['token']])){ continue; }
                                                $this_level = $this_info['level'];
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
                                                $this_ability_method = 'level';
                                                $this_ability_method_text = 'Level Up';
                                                $this_ability_title_html = '<strong class="name">'.$this_ability_name.'</strong>';
                                                if (is_numeric($this_level)){
                                                    if ($this_level > 1){ $this_ability_title_html .= '<span class="level">Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).'</span>'; }
                                                    else { $this_ability_title_html .= '<span class="level">Start</span>'; }
                                                } else {
                                                    $this_ability_method = 'player';
                                                    $this_ability_method_text = 'Player Only';
                                                    if (!in_array($this_ability_token, $robot_info['robot_abilities'])){
                                                        $this_ability_method = 'core';
                                                        $this_ability_method_text = 'Core Match';
                                                    }
                                                    $this_ability_title_html .= '<span class="level">&nbsp;</span>';
                                                }

                                                // If this is a boss, don't bother showing player or core match abilities
                                                if ($this_ability_method != 'level' && $robot_info['robot_class'] == 'boss'){ continue; }

                                                if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                                                if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                                                if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                                                if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                                                $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_image = 'ability'; $this_ability_sprite_path = 'i/a/ability/il40.png'; }
                                                else { $this_ability_sprite_path = 'i/a/'.$this_ability_image.'/il40.png'; }
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

                    <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                            <a class="link link_permalink" href="<?= $database_category_robot_url ?>#abilities" rel="permalink">#Abilities</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if($print_options['show_stats']): ?>

                    <h2 id="stats" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Stats
                    </h2>
                    <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
                        <?php
                        // Define the various levels we'll display in this chart
                        $display_levels = array(1, 5, 10, 50, 100); //range(1, 100, 1);
                        ?>
                        <table class="full stat_container" style="">
                            <colgroup>
                                <col width="20%" />
                                <col width="10%" />
                                <col width="10%" />
                                <col width="10%" />
                                <col width="10%" />
                                <col width="10%" />
                                <col width="10%" />
                                <col width="10%" />
                                <col width="10%" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="top left level">Level</th>
                                    <th class="top center energy" colspan="1">Energy</th>
                                    <th class="top center weapons" colspan="1">Weapons</th>
                                    <th class="top center attack" colspan="2">Attack</th>
                                    <th class="top center defense" colspan="2">Defense</th>
                                    <th class="top center speed" colspan="2">Speed</th>
                                </tr>
                                <tr>
                                    <th class="sub left level" >&nbsp;</th>
                                    <th class="sub center energy max">-</th>
                                    <th class="sub center weapons max">-</th>
                                    <th class="sub center attack min">Min</th>
                                    <th class="sub center attack max">Max</th>
                                    <th class="sub center defense min">Min</th>
                                    <th class="sub center defense max">Max</th>
                                    <th class="sub center speed min">Min</th>
                                    <th class="sub center speed max">Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Define or collect the base stats for this robot, ready to be modified
                                $base_stats = array();
                                $base_stats['energy'] = $robot_info['robot_energy'];
                                $base_stats['weapons'] = $robot_info['robot_weapons'];
                                $base_stats['attack'] = $robot_info['robot_attack'];
                                $base_stats['defense'] = $robot_info['robot_defense'];
                                $base_stats['speed'] = $robot_info['robot_speed'];
                                // Loop through the display levels and calculate stat adjustments
                                foreach ($display_levels AS $level){
                                    // Calculate the minimum stat values for this robot with only level-based stat boosts
                                    $min_stats = array();
                                    $min_stats['energy'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['energy'], $level);
                                    $min_stats['attack'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['attack'], $level);
                                    $min_stats['defense'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['defense'], $level);
                                    $min_stats['speed'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['speed'], $level);
                                    // Calculate the maximum stat values for this robot considering both level and overkill-based stat boosts
                                    $max_stats = array();
                                    //$max_stats['energy'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['energy'], $level);
                                    $max_stats['attack'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['attack'], $level);
                                    $max_stats['defense'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['defense'], $level);
                                    $max_stats['speed'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['speed'], $level);
                                    ?>
                                    <tr>
                                        <td class="left level">Lv <?= $level ?></td>
                                        <td class="center energy max"><?= number_format($min_stats['energy'], 0, '.', ',') ?></td>
                                        <td class="center weapons max"><?= number_format($base_stats['weapons'], 0, '.', ',') ?></td>
                                        <td class="center attack min"><?= number_format($min_stats['attack'], 0, '.', ',') ?></td>
                                        <td class="center attack max"><?= number_format($max_stats['attack'], 0, '.', ',') ?></td>
                                        <td class="center defense min"><?= number_format($min_stats['defense'], 0, '.', ',') ?></td>
                                        <td class="center defense max"><?= number_format($max_stats['defense'], 0, '.', ',') ?></td>
                                        <td class="center speed min"><?= number_format($min_stats['speed'], 0, '.', ',') ?></td>
                                        <td class="center speed max"><?= number_format($max_stats['speed'], 0, '.', ',') ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td class="left help" colspan="9">
                                        <?php if ($robot_info['robot_class'] == 'master'): ?>
                                            * Min stats represent a robot's base values without any knockout bonuses applied.<br />
                                            ** Max stats represent a robot's potential values with maximum knockout bonuses applied.
                                        <?php elseif ($robot_info['robot_class'] == 'mecha'): ?>
                                            * Min stats represent a mecha's base values without any difficulty mods applied.<br />
                                            ** Max stats represent a mecha's potential values with maximum difficulty mods applied.
                                        <?php elseif ($robot_info['robot_class'] == 'boss'): ?>
                                            * Min stats represent a boss's base values without any difficulty mods applied.<br />
                                            ** Max stats represent a boss's potential values with maximum difficulty mods applied.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                            <a class="link link_permalink" href="<?= $database_category_robot_url ?>#stats" rel="permalink">#Stats</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if($print_options['show_records']): ?>

                    <h2 id="records" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Records
                    </h2>
                    <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <?php if($robot_info['robot_class'] == 'master'): ?>
                                    <tr>
                                        <td class="right">
                                            <label>Unlocked By : </label>
                                            <span class="robot_quote"><?= $temp_robot_records['robot_unlocked'] == 1 ? '1 Player' : number_format($temp_robot_records['robot_unlocked'], 0, '.', ',').' Players' ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="right">
                                        <label>Encountered : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_encountered'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_encountered'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Summoned : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_summoned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_summoned'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Defeated : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_defeated'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_defeated'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label>Scanned : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_scanned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_scanned'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                            <a class="link link_permalink" href="<?= $database_category_robot_url ?>#records" rel="permalink">#Records</a>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <div class="link_wrapper">
                        <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        <a class="link link_permalink" href="<?= $database_category_robot_url ?>" rel="permalink">+ View More</a>
                    </div>
                    <span class="link_container">
                        <?= !empty($compact_footer_link_markup) ? implode("\n", $compact_footer_link_markup) : ''  ?>
                    </span>

                <?php endif; ?>

            </div>
        </div>
        <?php
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }



    // Define a static function for printing out the robot's editor markup
    public static function print_editor_markup($player_info, $robot_info){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
        global $mmrpg_database_players, $mmrpg_database_abilities, $mmrpg_database_items, $mmrpg_database_fields, $mmrpg_database_types;
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
            $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
            // Check if this robot has the copy shot ability
            $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

            // Make backups of the robot's original stats before rewards
            $robot_info['robot_energy_index'] = $robot_info['robot_energy'];
            $robot_info['robot_weapons_index'] = $robot_info['robot_weapons'];
            $robot_info['robot_attack_index'] = $robot_info['robot_attack'];
            $robot_info['robot_defense_index'] = $robot_info['robot_defense'];
            $robot_info['robot_speed_index'] = $robot_info['robot_speed'];

            // Collect this robot's ability rewards and add them to the dropdown
            $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
            $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
            foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }

            // If the robot's level is greater than one, apply stat boosts
            if ($robot_info['robot_level'] > 1){
                // Create the temp level by subtracting one (so we don't have level 1 boosts)
                $temp_level = $robot_info['robot_level'] - 1;
                // Update the robot energy with a small boost based on experience level
                $robot_info['robot_energy'] = $robot_info['robot_energy'] + ceil($temp_level * (0.05 * $robot_info['robot_energy']));
                // Update the robot attack with a small boost based on experience level
                $robot_info['robot_attack'] = $robot_info['robot_attack'] + ceil($temp_level * (0.05 * $robot_info['robot_attack']));
                // Update the robot defense with a small boost based on experience level
                $robot_info['robot_defense'] = $robot_info['robot_defense'] + ceil($temp_level * (0.05 * $robot_info['robot_defense']));
                // Update the robot speed with a small boost based on experience level
                $robot_info['robot_speed'] = $robot_info['robot_speed'] + ceil($temp_level * (0.05 * $robot_info['robot_speed']));
            }

            // Make backups of the robot's original stats before rewards
            $robot_info['robot_energy_base'] = $robot_info['robot_energy'];
            $robot_info['robot_attack_base'] = $robot_info['robot_attack'];
            $robot_info['robot_defense_base'] = $robot_info['robot_defense'];
            $robot_info['robot_speed_base'] = $robot_info['robot_speed'];

            // Apply any stat rewards for the robot's attack
            if (!empty($robot_rewards['robot_attack'])){
                $robot_info['robot_attack'] += $robot_rewards['robot_attack'];
            }
            // Apply any stat rewards for the robot's defense
            if (!empty($robot_rewards['robot_defense'])){
                $robot_info['robot_defense'] += $robot_rewards['robot_defense'];
            }
            // Apply any stat rewards for the robot's speed
            if (!empty($robot_rewards['robot_speed'])){
                $robot_info['robot_speed'] += $robot_rewards['robot_speed'];
            }

            // Make backups of the robot's original stats before rewards
            $robot_info['robot_attack_rewards'] = $robot_info['robot_attack'] - $robot_info['robot_attack_base'];
            $robot_info['robot_defense_rewards'] = $robot_info['robot_defense'] - $robot_info['robot_defense_base'];
            $robot_info['robot_speed_rewards'] = $robot_info['robot_speed'] - $robot_info['robot_speed_base'];

            // Only apply player bonuses if the robot is with it's original player
            //if (!empty($robot_info['original_player']) && $robot_info['original_player'] == $player_info['player_token']){}

            // Apply stat bonuses to this robot based on its current player's own stats
            if (true){

                // Apply any player special for the robot's attack
                if (!empty($player_info['player_attack'])){
                    $robot_info['robot_attack'] += ceil($robot_info['robot_attack'] * ($player_info['player_attack'] / 100));
                }
                // Apply any player special for the robot's defense
                if (!empty($player_info['player_defense'])){
                    $robot_info['robot_defense'] += ceil($robot_info['robot_defense'] * ($player_info['player_defense'] / 100));
                }
                // Apply any player special for the robot's speed
                if (!empty($player_info['player_speed'])){
                    $robot_info['robot_speed'] += ceil($robot_info['robot_speed'] * ($player_info['player_speed'] / 100));
                }

            }

            // Make backups of the robot's original stats before rewards
            $robot_info['robot_attack_player'] = $robot_info['robot_attack'] - $robot_info['robot_attack_rewards'] - $robot_info['robot_attack_base'];
            $robot_info['robot_defense_player'] = $robot_info['robot_defense'] - $robot_info['robot_defense_rewards'] - $robot_info['robot_defense_base'];
            $robot_info['robot_speed_player'] = $robot_info['robot_speed'] - $robot_info['robot_speed_rewards'] - $robot_info['robot_speed_base'];

            // Limit stat digits for display purposes
            if ($robot_info['robot_energy'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_energy'] = MMRPG_SETTINGS_STATS_MAX; }
            if ($robot_info['robot_attack'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_attack'] = MMRPG_SETTINGS_STATS_MAX; }
            if ($robot_info['robot_defense'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_defense'] = MMRPG_SETTINGS_STATS_MAX; }
            if ($robot_info['robot_speed'] > MMRPG_SETTINGS_STATS_MAX){ $robot_info['robot_speed'] = MMRPG_SETTINGS_STATS_MAX; }

            // Collect the summon count from the session if it exists
            $robot_info['robot_summoned'] = !empty($robot_database['robot_summoned']) ? $robot_database['robot_summoned'] : 0;

            // Collect the alt images if there are any that are unlocked
            $robot_alt_count = 1 + (!empty($robot_info['robot_image_alts']) ? count($robot_info['robot_image_alts']) : 0);
            $robot_alt_options = array();
            if (!empty($robot_info['robot_image_alts'])){
                foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                    if ($robot_info['robot_summoned'] < $alt_info['summons']){ continue; }
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
            for ($i = 0; $i < 6; $i++){
                $temp_enabled = true;
                $temp_active = false;
                if ($i + 1 > $robot_alt_count){ break; }
                if ($i > 0 && !isset($robot_alt_options[$i - 1])){ $temp_enabled = false; }
                if ($temp_enabled && $i == 0 && $robot_image_unlock_current == 'base'){ $temp_active = true; }
                elseif ($temp_enabled && $i >= 1 && $robot_image_unlock_current == $robot_alt_options[$i - 1]){ $temp_active = true; }
                $robot_image_unlock_tokens .= '<span class="token token_'.($temp_enabled ? 'enabled' : 'disabled').' '.($temp_active ? 'token_active' : '').'" style="left: '.($token_first_offset + ($i * $token_other_offset)).'px;">&bull;</span>';
                $temp_total_alts_count += 1;
            }
            $temp_unlocked_alts_count = count($robot_alt_options) + 1;
            $temp_image_alt_title = '';
            if ($temp_total_alts_count > 1){
                $temp_image_alt_title = '<strong>'.$temp_unlocked_alts_count.' / '.$temp_total_alts_count.' Outfits Unlocked</strong><br />';
                //$temp_image_alt_title .= '<span style="font-size: 90%;">';
                    $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$robot_info['robot_name'].'</span><br />';
                    foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                        if ($robot_info['robot_summoned'] >= $alt_info['summons']){
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
                    <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                    <div class="sprite_wrapper robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="width: 33px;">
                        <div class="sprite_wrapper robot_type robot_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px;"></div>
                        <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(i/r/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mr<?= $robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                    </div>
                </div>

                <?php if(false && $robot_info['robot_level'] >= 100 && $robot_info['robot_core'] != 'copy'): ?>
                    <div class="this_sprite sprite_left event_robot_core2 ability_type ability_type_<?= !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : 'none' ?>" style="" >
                        <div class="sprite_wrapper" style="">
                            <?php if($global_allow_editing): ?>
                                <a class="robot_core2 <?= in_array($robot_token, $player_robot_favourites) ? 'robot_core_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Equip Subcore?">
                                    <?php if(!empty($robot_info['robot_core2'])): ?>
                                        <span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(i/a/item-core-<?= !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : 'none' ?>/il40.png);"></span>
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                <span class="robot_core2 <?= in_array($robot_token, $player_robot_favourites) ? 'robot_core_active ' : '' ?>">
                                    <span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(i/a/item-core-<?= !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : 'none' ?>/il40.png);"></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="this_sprite sprite_left event_robot_images" style="">
                    <?php if($global_allow_editing && !empty($robot_alt_options)): ?>
                        <a class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(i/r/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sr<?= $robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </a>
                    <?php else: ?>
                        <span class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(i/r/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sr<?= $robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="this_sprite sprite_left event_robot_summons" style="">
                    <div class="robot_summons">
                        <span class="summons_count"><?= $robot_info['robot_summoned'] ?></span>
                        <span class="summons_label"><?= $robot_info['robot_summoned'] == 1 ? 'Summon' : 'Summons' ?></span>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_favourite" style="" >
                    <?php if($global_allow_editing): ?>
                        <a class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Toggle Favourite?">&hearts;</a>
                    <?php else: ?>
                        <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</span>
                    <?php endif; ?>
                </div>

                <?php

                // Define the placehodler cells for the empty column in case it's needed
                ob_start();
                ?>
                <td class="right">
                    <label style="display: block; float: left; color: #696969;">??? :</label>
                    <span class="robot_stat" style="color: #696969; font-weight: normal;">???</span>
                </td>
                <?php
                $empty_column_placeholder = ob_get_clean();

                // Define an array to hold all the data in the left and right columns
                $left_column_markup = array();
                $right_column_markup = array();

                // Check to see if the player has unlocked the ability to swap players
                $temp_player_swap_unlocked = rpg_game::player_unlocked('dr-wily'); // && rpg_prototype::event_unlocked('dr-wily', 'chapter_one_complete');
                // If this player has unlocked the ability to let robots swap players
                if ($temp_player_swap_unlocked){
                    ob_start();
                    ?>
                    <td class="player_select_block right">
                        <?php
                        $player_style = '';
                        $robot_info['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : $player_info['player_token'];
                        if ($player_info['player_token'] != $robot_info['original_player']){
                            if ($counter_player_robots > 1){ $allow_player_selector = true; }
                        }
                        ?>
                        <?php if($robot_info['original_player'] != $player_info['player_token']): ?>
                            <label title="<?= 'Transferred from Dr. '.ucfirst(str_replace('dr-', '', $robot_info['original_player'])) ?>"  class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                        <?php else: ?>
                            <label class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                        <?php endif; ?>

                        <?if($global_allow_editing && $allow_player_selector):?>
                            <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>"><label style="background-image: url(i/p/<?= $player_info['player_token']?>/ml40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $player_info['player_name']?><span class="arrow">&#8711;</span></label></a>
                        <?elseif(!$global_allow_editing && $allow_player_selector):?>
                            <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="cursor: default; "><label style="background-image: url(i/p/<?= $player_info['player_token']?>/ml40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); cursor: default; "><?= $player_info['player_name']?></label></a>
                        <?else:?>
                            <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(i/p/<?= $player_info['player_token']?>/ml40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $player_info['player_name']?></label></a>
                        <?endif;?>
                    </td>
                    <?php
                    $left_column_markup[] = ob_get_clean();
                }

                // Check to see if the player has unlocked the ability to hold items
                $temp_item_hold_unlocked = rpg_prototype::event_complete('completed-chapter_dr-cossack_one');
                $current_item_token = '';
                // If this player has unlocked the ability to let robots hold items
                if ($temp_item_hold_unlocked){
                    // Collect the currently held item and token, if available
                    $current_item_token = !empty($robot_info['robot_item']) ? $robot_info['robot_item'] : '';
                    $current_item_info = !empty($mmrpg_database_items[$current_item_token]) ? $mmrpg_database_items[$current_item_token] : array();
                    $current_item_name = !empty($current_item_info['ability_name']) ? $current_item_info['ability_name'] : 'No Item';
                    $current_item_image = !empty($current_item_info['ability_image']) ? $current_item_info['ability_image'] : $current_item_token;
                    $current_item_type = !empty($current_item_info['ability_type']) ? $current_item_info['ability_type'] : 'none';
                    if (!empty($current_item_info['ability_type2'])){ $current_item_type = $current_item_type != 'none' ?  $current_item_type.'_'.$current_item_info['ability_type2'] : $current_item_info['ability_type2']; }
                    if (empty($current_item_info)){ $current_item_token = ''; $current_item_image = 'ability'; }
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Item:</label>
                        <?php if($global_allow_editing): ?>
                            <a title="Change Item?" class="item_name type <?= $current_item_type ?>"><label style="background-image: url(i/a/<?= $current_item_image ?>/il40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $current_item_name ?><span class="arrow">&#8711;</span></label></a>
                        <?php else: ?>
                            <a class="item_name type <?= $current_item_type ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(i/a/<?= $current_item_image ?>/il40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?= $current_item_name ?></label></a>
                        <?php endif; ?>
                    </td>
                    <?php
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the weakness
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Weaknesses :</label>
                        <?php
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
                    <?php
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the resistance
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Resistances :</label>
                        <?php
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
                    <?php
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the affinity
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Affinities :</label>
                        <?php
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
                    <?php
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the immunity
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label style="display: block; float: left;">Immunities :</label>
                        <?php
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
                    <?php
                    $left_column_markup[] = ob_get_clean();
                }

                // Define the markup for the level
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Level :</label>
                        <?php if($robot_info['robot_level'] >= 100){ ?>
                            <a data-tooltip-align="center" data-tooltip="<?= htmlentities(('Congratulations! '.$robot_info['robot_name'].' has reached Level 100!<br /> <span style="font-size: 90%;">Stat bonuses will now be awarded immediately when this robot lands the finishing blow on a target! Try to max out each stat to its full potential!</span>'), ENT_QUOTES, 'UTF-8') ?>" class="robot_stat robot_type_electric"><?= $robot_info['robot_level'] ?> <span>&#9733;</span></a>
                        <?php } else { ?>
                            <span class="robot_stat robot_level_reset robot_type_<?= !empty($robot_rewards['flags']['reached_max_level']) ? 'electric' : 'none' ?>"><?= !empty($robot_rewards['flags']['reached_max_level']) ? '<span>&#9733;</span>' : '' ?> <?= $robot_info['robot_level'] ?></span>
                        <?php } ?>
                    </td>
                    <?php
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the experience
                if (true){
                    ob_start();
                    ?>
                    <td  class="right">
                        <label style="display: block; float: left;">Experience :</label>
                        <?php if ($robot_info['robot_level'] >= MMRPG_SETTINGS_LEVEL_MAX): ?>
                            <span class="robot_stat robot_type_cutter">&#8734; / &#8734;</span>
                        <?php else: ?>
                            <span class="robot_stat"><?= $robot_info['robot_experience'] ?> / <?= rpg_prototype::calculate_experience_required($robot_info['robot_level']) ?></span>
                        <?php endif; ?>
                    </td>
                    <?php
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the energy
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <label style="display: block; float: left;">Energy :</label>

                        <span class="robot_stat robot_type robot_type_energy" style="padding: 0 6px; margin-right: 3px;"><?php
                            echo MMRPG_SETTINGS_STATS_GET_ROBOTMIN($robot_info['robot_energy_index'], $robot_info['robot_level'])
                        ?><span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> LE</span></span>

                        <span class="robot_stat robot_type robot_type_weapons" style="padding: 0 6px;"><?php
                            echo $robot_info['robot_weapons_index']
                        ?><span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> WE</span></span>

                    </td>
                    <?php
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the attack
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <?php
                        // Print out the ATTACK stat
                        $temp_stat = 'attack';
                        $temp_stat_max = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($robot_info['robot_'.$temp_stat.'_index'], $robot_info['robot_level']);
                        $temp_stat_maxed = $robot_info['robot_'.$temp_stat] >= $temp_stat_max ? true : false;
                        $temp_title = $robot_info['robot_level'] >= 100 ? $robot_info['robot_'.$temp_stat].' / '.$temp_stat_max.' Max'.($temp_stat_maxed ? ' &#9733;' : '') : '';
                        $temp_data_type = $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '';
                        ?>
                        <label class="<?= !empty($player_info['player_'.$temp_stat]) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;"><?= ucfirst($temp_stat) ?> :</label>
                        <span class="robot_stat <?= $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '' ?>"><?php
                            echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
                                echo '<span title="Base '.ucfirst($temp_stat).'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_base'].'</span> ';
                                echo !empty($robot_info['robot_'.$temp_stat.'_rewards']) ? '+ <span title="Knockout Bonuses" class="statboost_robot"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_rewards'].'</span> ' : '';
                                echo !empty($robot_info['robot_'.$temp_stat.'_player']) ? '+ <span title="Player Bonuses" class="statboost_player_'.$player_info['player_token'].($temp_stat_maxed ? '2' : '').'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_player'].'</span> ' : '';
                            echo ' = </span>';
                            echo '<span'.(!empty($temp_title) ? ' title="'.$temp_title.'"' : '').(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>';
                            echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$temp_stat], 4, '0', STR_PAD_LEFT));
                            if ($temp_stat_maxed){ echo '<span>&nbsp;&#9733;</span>'; }
                            echo '</span>';
                        ?></span>
                    </td>
                    <?php
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the defense
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <?php
                        // Print out the DEFENSE stat
                        $temp_stat = 'defense';
                        $temp_stat_max = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($robot_info['robot_'.$temp_stat.'_index'], $robot_info['robot_level']);
                        $temp_stat_maxed = $robot_info['robot_'.$temp_stat] >= $temp_stat_max ? true : false;
                        $temp_title = $robot_info['robot_level'] >= 100 ? $robot_info['robot_'.$temp_stat].' / '.$temp_stat_max.' Max'.($temp_stat_maxed ? ' &#9733;' : '') : '';
                        $temp_data_type = $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '';
                        ?>
                        <label class="<?= !empty($player_info['player_'.$temp_stat]) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;"><?= ucfirst($temp_stat) ?> :</label>
                        <span class="robot_stat <?= $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '' ?>"><?php
                            echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
                                echo '<span title="Base '.ucfirst($temp_stat).'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_base'].'</span> ';
                                echo !empty($robot_info['robot_'.$temp_stat.'_rewards']) ? '+ <span title="Knockout Bonuses" class="statboost_robot"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_rewards'].'</span> ' : '';
                                echo !empty($robot_info['robot_'.$temp_stat.'_player']) ? '+ <span title="Player Bonuses" class="statboost_player_'.$player_info['player_token'].($temp_stat_maxed ? '2' : '').'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_player'].'</span> ' : '';
                            echo ' = </span>';
                            echo '<span'.(!empty($temp_title) ? ' title="'.$temp_title.'"' : '').(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>';
                            echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$temp_stat], 4, '0', STR_PAD_LEFT));
                            if ($temp_stat_maxed){ echo '<span>&nbsp;&#9733;</span>'; }
                            echo '</span>';
                        ?></span>
                    </td>
                    <?php
                    $right_column_markup[] = ob_get_clean();
                }

                // Define the markup for the speed
                if (true){
                    ob_start();
                    ?>
                    <td class="right">
                        <?php
                        // Print out the SPEED stat
                        $temp_stat = 'speed';
                        $temp_stat_max = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($robot_info['robot_'.$temp_stat.'_index'], $robot_info['robot_level']);
                        $temp_stat_maxed = $robot_info['robot_'.$temp_stat] >= $temp_stat_max ? true : false;
                        $temp_title = $robot_info['robot_level'] >= 100 ? $robot_info['robot_'.$temp_stat].' / '.$temp_stat_max.' Max'.($temp_stat_maxed ? ' &#9733;' : '') : '';
                        $temp_data_type = $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '';
                        ?>
                        <label class="<?= !empty($player_info['player_'.$temp_stat]) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;"><?= ucfirst($temp_stat) ?> :</label>
                        <span class="robot_stat <?= $temp_stat_maxed ? 'robot_type robot_type_'.$temp_stat : '' ?>"><?php
                            echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
                                echo '<span title="Base '.ucfirst($temp_stat).'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_base'].'</span> ';
                                echo !empty($robot_info['robot_'.$temp_stat.'_rewards']) ? '+ <span title="Knockout Bonuses" class="statboost_robot"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_rewards'].'</span> ' : '';
                                echo !empty($robot_info['robot_'.$temp_stat.'_player']) ? '+ <span title="Player Bonuses" class="statboost_player_'.$player_info['player_token'].($temp_stat_maxed ? '2' : '').'"'.(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>'.$robot_info['robot_'.$temp_stat.'_player'].'</span> ' : '';
                            echo ' = </span>';
                            echo '<span'.(!empty($temp_title) ? ' title="'.$temp_title.'"' : '').(!empty($temp_data_type) ? ' data-tooltip-type="'.$temp_data_type.'"' : '').'>';
                            echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$temp_stat], 4, '0', STR_PAD_LEFT));
                            if ($temp_stat_maxed){ echo '<span>&nbsp;&#9733;</span>'; }
                            echo '</span>';
                        ?></span>
                    </td>
                    <?php
                    $right_column_markup[] = ob_get_clean();
                }

                ?>

                <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
                    <span class="title robot_type"><?= $robot_info['robot_name']?></span>
                    <span class="core robot_type">
                        <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(i/a/item-core-<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/il40.png);"></span></span>
                        <span class="text"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?> Core</span>
                    </span>
                </div>
                <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
                    <table class="full" style="margin-bottom: 5px;">
                        <colgroup>
                            <col width="64%" />
                            <col width="1%" />
                            <col width="35%" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <?php
                                if (!empty($left_column_markup[0])){ echo $left_column_markup[0]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?php
                                if (!empty($right_column_markup[0])){ echo $right_column_markup[0]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                if (!empty($left_column_markup[1])){ echo $left_column_markup[1]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?php
                                if (!empty($right_column_markup[1])){ echo $right_column_markup[1]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>

                            <tr>
                                <?php
                                if (!empty($left_column_markup[2])){ echo $left_column_markup[2]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?php
                                if (!empty($right_column_markup[2])){ echo $right_column_markup[2]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                if (!empty($left_column_markup[3])){ echo $left_column_markup[3]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?php
                                if (!empty($right_column_markup[3])){ echo $right_column_markup[3]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                if (!empty($left_column_markup[4])){ echo $left_column_markup[4]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?php
                                if (!empty($right_column_markup[4])){ echo $right_column_markup[4]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                if (!empty($left_column_markup[5])){ echo $left_column_markup[5]; }
                                else { echo $empty_column_placeholder; }
                                ?>
                                <td class="center">&nbsp;</td>
                                <?php
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
                                    <?/*<label style="display: block; float: left; font-size: 12px;">Abilities :</label>*/?>
                                    <?php
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
                                        <?php

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
            <?php
            $key_counter++;

            // Return the backup of the player selector
            $allow_player_selector = $allow_player_selector_backup;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;
    }


    /**
     * Get the formatted editor option markup for this field object given player and field info
     * @param array $player_info
     * @param array $field_info
     * @return string
     */
    public static function print_editor_option_markup($field_info){

        // Collect references to global objects
        $db = cms_database::get_database();

        // Collect references to global indexes
        $mmrpg_types = rpg_type::get_index();

        // Expand the field index info
        $field_token = $field_info['field_token'];
        $field_info = rpg_field::get_index_info($field_token);
        if (empty($field_info) || empty($field_info)){ return false; }

        // Collect the field type info
        $temp_field_type = !empty($field_info['field_type']) ? $mmrpg_types[$field_info['field_type']] : false;
        $temp_field_type2 = !empty($field_info['field_type2']) ? $mmrpg_types[$field_info['field_type2']] : false;

        // Generate the field option markup
        $temp_field_label = $field_info['field_name'];
        $temp_field_title = rpg_markup::field_editor_title_markup($field_info);
        $temp_field_title_plain = strip_tags(str_replace('<br />', '&#10;', $temp_field_title));
        $temp_field_title_tooltip = htmlentities($temp_field_title, ENT_QUOTES, 'UTF-8');
        $temp_field_option = $field_info['field_name'];
        if (!empty($temp_field_type)){ $temp_field_option .= ' | '.$temp_field_type['type_name']; }
        if (!empty($temp_field_type2)){ $temp_field_option .= ' / '.$temp_field_type2['type_name']; }
        if (!empty($temp_field_energy)){ $temp_field_option .= ' | E:'.$temp_field_energy; }
        $temp_field_option_markup = '<option value="'.$temp_field_token.'" data-label="'.$temp_field_label.'" data-type="'.(!empty($temp_field_type) ? $temp_field_type['type_token'] : 'none').'" data-type2="'.(!empty($temp_field_type2) ? $temp_field_type2['type_token'] : '').'" title="'.$temp_field_title_plain.'" data-tooltip="'.$temp_field_title_tooltip.'">'.$temp_field_option.'</option>';

        // Return the generated markup
        return $temp_field_option_markup;

    }


    /**
     * Get the formatted editor title markup for this field object given field info
     * @param array $field_info
     * @return string
     */
    public static function print_editor_title_markup($field_info){

        // Collect references to global objects
        $db = cms_database::get_database();

        // Collect references to global indexes
        $mmrpg_types = rpg_type::get_index();
        $mmrpg_players = rpg_player::get_index();
        $mmrpg_robots = self::get_index();

        // Expand the field index info
        $field_token = $field_info['field_token'];
        $field_info = rpg_field::get_index_info($field_token);
        if (empty($field_info) || empty($field_info)){ return false; }

        // Collect the field type info and expand master/mecha data
        $temp_field_type = !empty($field_info['field_type']) ? $mmrpg_types[$field_info['field_type']] : false;
        $temp_field_type2 = !empty($field_info['field_type2']) ? $mmrpg_types[$field_info['field_type2']] : false;
        $temp_field_master = !empty($field_info['field_master']) ? self::parse_index_info($mmrpg_robots[$field_info['field_master']]) : false;
        $temp_field_mechas = !empty($field_info['field_mechas']) ? $field_info['field_mechas'] : array();
        foreach ($temp_field_mechas AS $key => $token){
            $temp_mecha = self::parse_index_info($mmrpg_robots[$token]);
            if (!empty($temp_mecha)){ $temp_field_mechas[$key] = $temp_mecha['robot_name'];  }
            else { unset($temp_field_mechas[$key]); }
        }

        // Generate the field title markup
        $temp_field_title = $field_info['field_name'];
        if (!empty($temp_field_type)){ $temp_field_title .= ' ('.$temp_field_type['type_name'].' Type)'; }
        if (!empty($temp_field_type2)){ $temp_field_title = str_replace('Type', '/ '.$temp_field_type2['type_name'].' Type', $temp_field_title); }
        $temp_field_title .= '  // ';
        if (!empty($temp_field_master)){ $temp_field_title .= 'Robot : '.$temp_field_master['robot_name'].' // '; }
        if (!empty($temp_field_mechas)){ $temp_field_title .= 'Mecha : '.implode(', ', array_unique($temp_field_mechas)).' // '; }

        // Return the generated markup
        return $temp_field_title;

    }





    // Define a function for checking if this robot is compatible with a specific ability
    static public function has_ability_compatibility($robot_token, $ability_token, $item_token = ''){
        global $mmrpg_index;
        if (empty($robot_token) || empty($ability_token)){ return false; }
        $robot_info = is_array($robot_token) ? $robot_token : self::get_index_info($robot_token);
        $ability_info = is_array($ability_token) ? $ability_token : rpg_ability::get_index_info($ability_token);
        $item_info = is_array($item_token) ? $item_token : rpg_ability::get_index_info($item_token);
        if (empty($robot_info) || empty($ability_info)){ return false; }
        $ability_token = !empty($ability_info) ? $ability_info['ability_token'] : '';
        $item_token = !empty($item_info) ? $item_info['ability_token'] : '';
        $robot_token = $robot_info['robot_token'];
        $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_core2 = !empty($item_token) && preg_match('/^item-core-/i', $item_token) ? preg_replace('/^item-core-/i', '', $item_token) : '';
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
            if (!empty($robot_core) || !empty($robot_core2)){
            //$debug_fragment .= 'has-core '; // DEBUG
                if ($robot_core == 'copy'){
                    //$debug_fragment .= 'copy-core '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type'])
                    && ($ability_info['ability_type'] == $robot_core || $ability_info['ability_type'] == $robot_core2)){
                    //$debug_fragment .= 'core-match1 '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type2'])
                    && ($ability_info['ability_type2'] == $robot_core || $ability_info['ability_type2'] == $robot_core2)){
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
        elseif (is_array($this->robot_weaknesses) && in_array($weakness_token, $this->robot_weaknesses)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific resistance
    public function has_resistance($resistance_token){
        if (empty($this->robot_resistances) || empty($resistance_token)){ return false; }
        elseif (is_array($this->robot_resistances) && in_array($resistance_token, $this->robot_resistances)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific affinity
    public function has_affinity($affinity_token){
        if (empty($this->robot_affinities) || empty($affinity_token)){ return false; }
        elseif (is_array($this->robot_affinities) && in_array($affinity_token, $this->robot_affinities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific immunity
    public function has_immunity($immunity_token){
        if (empty($this->robot_immunities) || empty($immunity_token)){ return false; }
        elseif (is_array($this->robot_immunities) && in_array($immunity_token, $this->robot_immunities)){ return true; }
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

    // Define a function for checking if this robot afflicted with the darkness
    public function has_darkness(){
        // Define the darkness flag and default to false
        $this_has_darkness = false;
        // Collect darkness influencing variables values
        $this_robot_token = $this->get_token();
        $this_robot_core = $this->get_core();
        $this_robot_core2 = $this->get_core();
        // If this robot is a dark elemental it's afflicted with the darkness
        if (preg_match('/^dark-(frag|spire|tower)$/i', $this_robot_token)){ $this_has_darkness = true; }
        // Else if this robot has an empty core it's afflicted with the darkness
        elseif (in_array('empty', array($this_robot_core, $this_robot_core2))){ $this_has_darkness = true; }
        // Return the darkness flag
        return $this_has_darkness;
    }


    // Define a function for returning a random robot frame, allowing inclusions and exclusions
    public static function random_frame($filter = 'exclude', $frames = array()){
        // Define all the frames a robot can have
        $robot_frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        // Define the filter if not set or invalid
        $filter = !empty($filter) && $filter == 'include' ? 'include' : 'exclude';
        // If this is an exclusion filter, include all but the requested
        if ($filter == 'exclude'){
            // If the frame array is equal to false, auto-populate
            if (empty($frames) && $frames !== false){ $exclude = array('defeat', 'damage'); }
            elseif (empty($frames) && $frames === false) { $exclude = array(); }
            // Loop through index and include any that are relevant
            $frames = array();
            foreach ($robot_frame_index AS $token){ if (!in_array($token, $exclude)){ $frames[] = $token;  } }
            // Return a random element from the array
            return rpg_functions::weighted_chance($frames);
        }
        // Else if this is an inclusion filter, include only the requested
        elseif ($filter == 'include'){
            // If the frame array was empty in any way, populate
            if (empty($frames)){ $include = $robot_frame_index; }
            elseif (!empty($frames)){ $include = $frames; }
            // Return a random element from the array
            return rpg_functions::weighted_chance($frames);
        }

    }

    // Define a function for checking if this robot is in speed break status
    public function robot_choices_abilities($target_player, $target_robot){

        // Extract all objects into the current scope
        global $db;
        $this_battle = $this->battle;
        $this_field = $this->field;
        $this_player = $this->player;
        $this_robot = $this;
        $this_battle->refresh();
        $this_field->refresh();
        $this_player->refresh();
        $this_robot->refresh();
        $target_player->refresh();
        $target_robot->refresh();

        // If the given robot has their own choices function
        if (isset($this_robot->robot_function_choices_abilities)){
            // Simply return the result of the robot's personal choices function
            $temp_function = $this_robot->robot_function_choices_abilities;
            $return_token = $temp_function(array(
                'this_battle' => $this_battle,
                'this_field' => $this_field,
                'this_player' => $this_player,
                'this_robot' => $this_robot,
                'target_player' => $target_player,
                'target_robot' => $target_robot
                ));
            if (!empty($return_token)){ return $return_token; }
        }

        // Define the base frequency of the various boost and break abilities
        $temp_energy_boost_rate = $temp_attack_boost_rate = $temp_defense_boost_rate = $temp_speed_boost_rate = 0;
        $temp_energy_break_rate = $temp_attack_break_rate = $temp_defense_break_rate = $temp_speed_break_rate = 0;
        $temp_energy_swap_rate = $temp_attack_swap_rate = $temp_defense_swap_rate = $temp_speed_swap_rate = 0;
        $temp_energy_mode_rate = $temp_attack_mode_rate = $temp_defense_mode_rate = $temp_speed_mode_rate = 0;

        // Apply energy boost mods based on current life energy of this robot
        if ($this_robot->robot_energy < ($this_robot->robot_base_energy / 4)){ $temp_energy_boost_rate += 20; }
        elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 3)){ $temp_energy_boost_rate += 15; }
        elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 2)){ $temp_energy_boost_rate += 10; }
        // Apply energy break mods based on current life energy of the target robot
        if ($target_robot->robot_energy < ($target_robot->robot_base_energy / 4)){ $temp_energy_break_rate += 10; }
        elseif ($target_robot->robot_energy < ($target_robot->robot_base_energy / 3)){ $temp_energy_break_rate += 15; }
        elseif ($target_robot->robot_energy < ($target_robot->robot_base_energy / 2)){ $temp_energy_break_rate += 20; }
        // Apply attack/defense boost/break mods based on the current stats of both robots
        if ($this_robot->robot_attack < $target_robot->robot_defense){ $temp_attack_boost_rate += 20; $temp_defense_break_rate += 20; }
        if ($this_robot->robot_defense < $target_robot->robot_attack){ $temp_defense_boost_rate += 20; $temp_attack_break_rate += 20; }
        // Apply speed boost/break mods based on the current stats of both robots
        if ($this_robot->robot_speed < $target_robot->robot_speed){ $temp_speed_boost_rate += 20; $temp_speed_break_rate += 20; }
        // Apply energy swap mods based on current life energy of both robots
        if ($this_robot->robot_energy < $target_robot->robot_energy){ $temp_energy_swap_rate += 20; }
        // Apply attack/defense swap mods based on the current stats of both robots
        if ($this_robot->robot_attack < $target_robot->robot_attack){ $temp_attack_swap_rate += 20; }
        if ($this_robot->robot_defense < $target_robot->robot_defense){ $temp_defense_swap_rate += 20; }
        // Apply speed swap mods based on the current stats of both robots
        if ($this_robot->robot_speed < $target_robot->robot_speed){ $temp_speed_swap_rate += 20; }
        // Apply the various mode mods based on the robot's current stats vs base
        if ($this_robot->robot_energy < ($this_robot->robot_base_energy / 2)){ $temp_energy_mode_rate += 15; }
        if ($this_robot->robot_attack < ($this_robot->robot_base_attack / 2)){ $temp_attack_mode_rate += 15; }
        if ($this_robot->robot_defense < ($this_robot->robot_base_defense / 2)){ $temp_defense_mode_rate += 15; }
        if ($this_robot->robot_speed < ($this_robot->robot_base_speed / 2)){ $temp_speed_mode_rate += 15; }

        // Create the ability options and weights variables
        $options = array();
        $weights = array();

        // Define the support multiplier for this robot
        $support_multiplier = 1;
        if (in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm')) || $this_robot->robot_class == 'mecha'){ $support_multiplier += 1; }

        // Define the frequency of the default buster ability if set
        if ($this_robot->has_ability('buster-shot')){ $options[] = 'buster-shot'; $weights[] = $this_robot->robot_token == 'met' ? 90 : 1;  }
        if ($this_robot->has_ability('super-throw')){ $options[] = 'super-throw'; $weights[] = 1;  }

        // Loop through any leftover abilities and add them to the weighted ability options
        $temp_ability_index = $db->get_array_list("SELECT ability_id, ability_token, ability_name, ability_type, ability_type2 FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
        $temp_boost_pattern = 'boost|hone|temper|cool|blast|blaze|charge|haste|harden|breeze|douse|surge|growth|rocket|polish|charm|cosmos|guard|glow';
        $temp_break_pattern = 'break|blunt|hammer|chill|burst|burn|shock|slow|crumble|squall|drench|stall|decay|torpedo|tarnish|curse|chaos|block|fade';
        foreach ($this_robot->robot_abilities AS $key => $token){
            if (!in_array($token, $options)){
                $info = $temp_ability_index[$token];
                $value = 3;
                if (preg_match('/^(energy|repair)-('.$temp_boost_pattern.')$/i', $token)){ $value = $temp_energy_boost_rate * $support_multiplier; }
                elseif (preg_match('/^(attack|weapon)-('.$temp_boost_pattern.')$/i', $token)){ $value = $temp_attack_boost_rate * $support_multiplier; }
                elseif (preg_match('/^(defense|shield)-('.$temp_boost_pattern.')$/i', $token)){ $value = $temp_defense_boost_rate * $support_multiplier; }
                elseif (preg_match('/^(speed|mobility)-('.$temp_boost_pattern.')$/i', $token)){ $value = $temp_speed_boost_rate * $support_multiplier; }
                elseif (preg_match('/^(energy|repair)-('.$temp_break_pattern.')$/i', $token)){ $value = $temp_energy_break_rate * $support_multiplier; }
                elseif (preg_match('/^(attack|weapon)-('.$temp_break_pattern.')$/i', $token)){ $value = $temp_attack_break_rate * $support_multiplier; }
                elseif (preg_match('/^(defense|shield)-('.$temp_break_pattern.')$/i', $token)){ $value = $temp_defense_break_rate * $support_multiplier; }
                elseif (preg_match('/^(speed|mobility)-('.$temp_break_pattern.')$/i', $token)){ $value = $temp_speed_break_rate * $support_multiplier; }
                elseif (preg_match('/^(energy|repair)-(swap)$/i', $token)){ $value = $temp_energy_swap_rate; }
                elseif (preg_match('/^(attack|weapon)-(swap)$/i', $token)){ $value = $temp_attack_swap_rate; }
                elseif (preg_match('/^(defense|shield)-(swap)$/i', $token)){ $value = $temp_defense_swap_rate; }
                elseif (preg_match('/^(speed|mobility)-(swap)$/i', $token)){ $value = $temp_speed_swap_rate; }
                elseif (preg_match('/^(energy|repair)-(mode)$/i', $token)){ $value = $temp_energy_mode_rate; }
                elseif (preg_match('/^(attack|weapon)-(mode)$/i', $token)){ $value = $temp_attack_mode_rate; }
                elseif (preg_match('/^(defense|shield)-(mode)$/i', $token)){ $value = $temp_defense_mode_rate; }
                elseif (preg_match('/^(speed|mobility)-(mode)$/i', $token)){ $value = $temp_speed_mode_rate; }
                elseif (!empty($this_robot->robot_core) && !empty($info['ability_type'])){
                    if ($this_robot->robot_core == $info['ability_type']){ $value = 50; }
                    elseif ($this_robot->robot_core == $info['ability_type2']){ $value = 25; }
                    elseif ($this_robot->robot_core == 'copy'){ $value = 40; }
                    elseif ($this_robot->robot_core != $info['ability_type']){ $value = 30; }
                } elseif (empty($this_robot->robot_core)){
                    $value = 30;
                } else {
                    $value = 3;
                }
                $options[] = $token;
                $weights[] = $value;
            }
        }
        // Return an ability based on a weighted chance
        return rpg_functions::weighted_chance($options, $weights);

    }

    // Define a trigger for using one of this robot's abilities
    public function trigger_ability(rpg_player $target_player, rpg_robot $target_robot, rpg_ability $this_ability){

        // Extract all objects into the current scope
        global $db;
        $this_battle = $this->battle;
        $this_field = $this->field;
        $this_player = $this->player;
        $this_robot = $this;
        $this_battle->refresh();
        $this_field->refresh();
        $this_player->refresh();
        $this_robot->refresh();
        $target_player->refresh();
        $target_robot->refresh();
        $this_ability->refresh();

        // Update this robot's history with the triggered ability
        $this_robot->add_history('triggered_abilities', $this_ability->ability_token);

        // Set the ability active flag in the battle
        $this_battle->set_flag('robot_ability_in_progress', 1);

        // Define a variable to hold the ability results
        $ability_results = array();
        $ability_results['total_result'] = '';
        $ability_results['total_actions'] = 0;
        $ability_results['total_strikes'] = 0;
        $ability_results['total_misses'] = 0;
        $ability_results['total_amount'] = 0;
        $ability_results['total_overkill'] = 0;
        $ability_results['this_result'] = '';
        $ability_results['this_amount'] = 0;
        $ability_results['this_overkill'] = 0;
        $ability_results['this_text'] = '';
        $ability_results['counter_criticals'] = 0;
        $ability_results['counter_affinities'] = 0;
        $ability_results['counter_weaknesses'] = 0;
        $ability_results['counter_resistances'] = 0;
        $ability_results['counter_immunities'] = 0;
        $ability_results['counter_coreboosts'] = 0;
        $ability_results['flag_critical'] = false;
        $ability_results['flag_affinity'] = false;
        $ability_results['flag_weakness'] = false;
        $ability_results['flag_resistance'] = false;
        $ability_results['flag_immunity'] = false;
        $this_ability->set_results($ability_results);

        // Reset the ability options to default
        $this_ability->target_options_reset();
        $this_ability->damage_options_reset();
        $this_ability->recovery_options_reset();

        // Determine how much weapon energy this should take
        $temp_ability_energy = $this_robot->calculate_weapon_energy($this_ability);

        // Decrease this robot's weapon energy
        $this_robot->set_weapons($this_robot->robot_weapons - $temp_ability_energy);

        // Default this and the target robot's frames to their base
        $this_robot->set_frame('base');
        $target_robot->set_frame('base');

        // Default the robot's stances to attack/defend
        $this_robot->set_stance('attack');
        $target_robot->set_stance('defend');

        // If this is a copy core robot and the ability type does not match its core
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) ? $this_ability->ability_type : '';
        $temp_ability_type2 = !empty($this_ability->ability_type2) ? $this_ability->ability_type2 : $temp_ability_type;
        if (!preg_match('/^item-/', $this_ability->ability_token) && !empty($temp_ability_type) && $this->robot_base_core == 'copy'){
            $this_robot->set_image_overlay('copy_type1', $this->robot_base_image.'_'.$temp_ability_type.'2');
            $this_robot->set_image_overlay('copy_type2', $this->robot_base_image.'_'.$temp_ability_type2.'3');
            $temp_image_changed = true;
        }

        // Copy the ability function to local scope and execute it
        $this_ability_function = $this_ability->ability_function;
        $this_ability_function($this_battle, $this_field, $this_player, $this_robot, $target_player, $target_robot, $this_ability);

        // If this robot's image has been changed, reveert it back to what it was
        if ($temp_image_changed){
            $this_robot->unset_image_overlay('copy_type1');
            $this_robot->unset_image_overlay('copy_type2');
        }

        // Update this ability's history with the triggered ability data and results
        $this_ability->add_history('ability_results', $this_ability->ability_results);
        // Update this ability's history with the triggered ability damage options
        $this_ability->add_history('ability_options', $this_ability->ability_options);

        // Reset the robot's stances to the base
        $this_robot->set_stance('base');
        $target_robot->set_stance('base');


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        if ($this_robot->has_attachments()){
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $attachment_key = 0;
            foreach ($this->robot_attachments AS $attachment_token => $attachment_info){
                // Ensure this ability has a type before checking weaknesses, resistances, etc.
                if (!empty($this_ability->ability_type)){
                    // If this attachment has weaknesses defined and this ability is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses']) || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))){
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        $this->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            $temp_info = array('ability_id' => (MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX + $attachment_key + 100), 'ability_token' => $attachment_info['ability_token']);
                            $temp_attachment = new rpg_ability($this->player, $this, $temp_info);
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
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            break;
                        }
                    }

                }
                $attachment_key++;
            }
        }

        // Unset the ability active flag in the battle
        unset($this->battle->flags['robot_ability_in_progress']);

        // Update internal variables
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;
    }

    // Define a trigger for using one of this robot's abilities
    public function trigger_target($target_robot, $this_ability, $trigger_options = array()){

        // Pull in the global objects
        global $db;
        // Collect references to assumed variables
        $this_battle = $this->battle;
        $this_field = $this->field;
        $this_player = $this->player;
        $this_robot = $this;
        $target_player = $target_robot->player;
        //$target_robot = $target_robot;
        //$this_ability = $this_ability;
        // Refresh all object variable values
        $this_battle->refresh();
        $this_field->refresh();
        $this_player->refresh();
        $this_robot->refresh();
        $target_player->refresh();
        $target_robot->refresh();
        $this_ability->refresh();

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
        $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
        $event_options['this_ability_target_key'] = $target_robot->robot_key;
        $event_options['this_ability_target_position'] = $target_robot->robot_position;
        $event_options['this_ability_results'] = array();
        $event_options['console_show_target'] = false;
        if (!empty($trigger_options['prevent_stats_text'])){
            $event_options['canvas_show_ability_stats'] = false;
        }

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update this robot's history with the triggered ability
        $this->history['triggered_targets'][] = $target_robot->robot_token;

        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Update this robot's frames using the target options
        $this->robot_frame = $this_ability->target_options['target_frame'];
        if ($this->robot_id != $target_robot->robot_id && $target_robot->robot_frame == 'base'){ $target_robot->robot_frame = 'defend'; }
        $this->player->set_frame('command');
        $this_ability->ability_frame = $this_ability->target_options['ability_success_frame'];
        $this_ability->ability_frame_span = $this_ability->target_options['ability_success_frame_span'];
        $this_ability->ability_frame_offset = $this_ability->target_options['ability_success_frame_offset'];

        // If the target player is on the bench, alter the ability scale
        $temp_ability_styles_backup = $this_ability->ability_frame_styles;
        if ($target_robot->robot_position == 'bench' && $event_options['this_ability_target'] != $this->robot_id.'_'.$this->robot_token){
            $temp_scale = 1 - ($target_robot->robot_key * 0.06);
            $temp_translate = 20 + ($target_robot->robot_key * 20);
            $temp_translate2 = ceil($temp_translate / 10) * -1;
            $temp_translate = $temp_translate * ($target_robot->player->player_side == 'left' ? -1 : 1);
            //$this_ability->ability_frame_styles .= 'border: 1px solid red !important; ';
            $this_ability->ability_frame_styles .= 'transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -webkit-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -moz-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); ';
        }

        // Create a message to show the initial targeting action
        if ($this->robot_id != $target_robot->robot_id && empty($trigger_options['prevent_default_text'])){
            $this_ability->ability_results['this_text'] .= "{$this->print_name()} targets {$target_robot->print_name()}!<br />";
        }

        // Append the targetting text to the event body
        $this_ability->ability_results['this_text'] .= $this_ability->target_options['target_text'];

        // Update the ability results with the the trigger kind
        $this_ability->ability_results['trigger_kind'] = 'target';
        $this_ability->ability_results['trigger_target_id'] = $target_robot->robot_id;
        $this_ability->ability_results['trigger_target_key'] = $target_robot->robot_key;
        $this_ability->ability_results['this_result'] = 'success';

        // Update the event options with the ability results
        $event_options['this_ability_results'] = $this_ability->ability_results;
        if (isset($trigger_options['canvas_show_this_ability'])){ $event_options['canvas_show_this_ability'] = $trigger_options['canvas_show_this_ability'];  }

        // Create a new entry in the event log for the targeting event
        $this->battle->events_create($this, $target_robot, $this_ability->target_options['target_header'], $this_ability->ability_results['this_text'], $event_options);

        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;

        // Refresh the ability styles from any changes
        $this_ability->ability_frame_styles = ''; //$temp_ability_styles_backup;

        // restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->set_frame($this_player_backup_frame);
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->set_frame($target_player_backup_frame);
        $this_ability->ability_frame = $this_ability_backup_frame;
        $this_ability->target_options_reset();

        // Update internal variables
        $this->update_session();
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;

    }

    // Define a trigger for inflicting all types of damage on this robot
    public function trigger_damage($target_robot, $this_ability, $damage_amount, $trigger_disabled = true, $trigger_options = array()){

        // Import global variables
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // If the battle has already ended, return false
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Generate default trigger options if not set
        if (!isset($trigger_options['apply_modifiers'])){ $trigger_options['apply_modifiers'] = $this_ability->damage_options['damage_modifiers'] == false ? false : true; }
        if (!isset($trigger_options['apply_type_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_type_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_weakness_modifiers']) || $trigger_options['apply_weakness_modifiers'] == false){ $trigger_options['apply_weakness_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_resistance_modifiers']) || $trigger_options['apply_resistance_modifiers'] == false){ $trigger_options['apply_resistance_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_affinity_modifiers']) || $trigger_options['apply_affinity_modifiers'] == false){ $trigger_options['apply_affinity_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_immunity_modifiers']) || $trigger_options['apply_immunity_modifiers'] == false){ $trigger_options['apply_immunity_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_core_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_core_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_position_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_position_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_field_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_field_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_stat_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_stat_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['referred_damage'])){ $trigger_options['referred_damage'] = false; }
        if (!isset($trigger_options['referred_player'])){ $trigger_options['referred_player'] = false; }
        if (!isset($trigger_options['referred_robot'])){ $trigger_options['referred_robot'] = false; }
        if (!isset($trigger_options['referred_energy'])){ $trigger_options['referred_energy'] = false; }
        if (!isset($trigger_options['referred_attack'])){ $trigger_options['referred_attack'] = false; }
        if (!isset($trigger_options['referred_defense'])){ $trigger_options['referred_defense'] = false; }
        if (!isset($trigger_options['referred_speed'])){ $trigger_options['referred_speed'] = false; }


        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Check if this robot is at full health before triggering
        $this_robot_energy_start = $this->robot_energy;
        $this_robot_energy_start_max = $this_robot_energy_start >= $this->robot_base_energy ? true : false;

        // If this damage has been referred, update target variables
        if (!empty($trigger_options['referred_damage'])){

            // If a referred player was provided, replace the target player object
            if (!empty($trigger_options['referred_player'])){
                $target_player = new rpg_player($trigger_options['referred_player']);
            }

            // If a referred player and robot were provided, replace the target robot object
            if (!empty($trigger_options['referred_player']) && !empty($trigger_options['referred_robot'])){
                $target_robot = new rpg_robot($target_player, $trigger_options['referred_robot']);
            }

            // Collect references to referred damage stats if they exist
            $target_robot_energy_start = !empty($trigger_options['referred_energy']) ? $trigger_options['referred_energy'] : $target_robot->robot_energy;
            $target_robot_attack_start = !empty($trigger_options['referred_attack']) ? $trigger_options['referred_attack'] : $target_robot->robot_attack;
            $target_robot_defense_start = !empty($trigger_options['referred_defense']) ? $trigger_options['referred_defense'] : $target_robot->robot_defense;
            $target_robot_speed_start = !empty($trigger_options['referred_speed']) ? $trigger_options['referred_speed'] : $target_robot->robot_speed;

        } else {

            // Collect references to the target robots stats
            $target_robot_energy_start = $target_robot->robot_energy;
            $target_robot_attack_start = $target_robot->robot_attack;
            $target_robot_defense_start = $target_robot->robot_defense;
            $target_robot_speed_start = $target_robot->robot_speed;

        }

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_results'] = array();

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update the damage to whatever was supplied in the argument
        //if ($this_ability->damage_options['damage_percent'] && $damage_amount > 100){ $damage_amount = 100; }
        $this_ability->damage_options['damage_amount'] = $damage_amount;

        // Collect the damage amount argument from the function
        $this_ability->ability_results['this_amount'] = $damage_amount;
        // DEBUG
        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | trigger_damage |  this('.$this->robot_id.':'.$this->robot_token.') vs target('.$target_robot->robot_id.':'.$target_robot->robot_token.') <br /> damage_start_amount:'.$this_ability->ability_results['this_amount'].' | '.'percent:'.($this_ability->damage_options['damage_percent'] ? 'true' : 'false').' | '.'kind:'.$this_ability->damage_options['damage_kind'].' | type1:'.(!empty($this_ability->damage_options['damage_type']) ? $this_ability->damage_options['damage_type'] : 'none').' | type2:'.(!empty($this_ability->damage_options['damage_type2']) ? $this_ability->damage_options['damage_type2'] : 'none').'');


        // DEBUG
        $debug = array();
        foreach ($trigger_options AS $key => $value){ $debug[] = (!$value ? '<del>' : '<span>').preg_replace('/^apply_(.*)_modifiers$/i', '$1_modifiers', $key).(!$value ? '</del>' : '</span>'); }
        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | damage_trigger_options | '.implode(', ', $debug));

        // Only apply modifiers if they have not been disabled
        if ($trigger_options['apply_modifiers'] != false){

            // Skip all weakness, resistance, etc. calculations if robot is targetting self
            if ($trigger_options['apply_type_modifiers'] != false
                && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_damage'])){

                // If target robot has affinity to the ability (based on type)
                if ($trigger_options['apply_affinity_modifiers'] != false && $this->has_affinity($this_ability->damage_options['damage_type']) && !$this->has_weakness($this_ability->damage_options['damage_type2'])){
                    //$this_ability->ability_results['counter_affinities'] += 1;
                    //$this_ability->ability_results['flag_affinity'] = true;
                    return $this->trigger_recovery($target_robot, $this_ability, $damage_amount);
                } else {
                    $this_ability->ability_results['flag_affinity'] = false;
                }

                // If target robot has affinity to the ability (based on type2)
                if ($trigger_options['apply_affinity_modifiers'] != false && $this->has_affinity($this_ability->damage_options['damage_type2']) && !$this->has_weakness($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_affinities'] += 1;
                    $this_ability->ability_results['flag_affinity'] = true;
                    return $this->trigger_recovery($target_robot, $this_ability, $damage_amount);
                }

                // If this robot has weakness to the ability (based on type)
                if ($trigger_options['apply_weakness_modifiers'] != false && $this->has_weakness($this_ability->damage_options['damage_type']) && !$this->has_affinity($this_ability->damage_options['damage_type2'])){
                    $this_ability->ability_results['counter_weaknesses'] += 1;
                    $this_ability->ability_results['flag_weakness'] = true;
                } else {
                    $this_ability->ability_results['flag_weakness'] = false;
                }

                // If this robot has weakness to the ability (based on type2)
                if ($trigger_options['apply_weakness_modifiers'] != false && $this->has_weakness($this_ability->damage_options['damage_type2']) && !$this->has_affinity($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_weaknesses'] += 1;
                    $this_ability->ability_results['flag_weakness'] = true;
                }

                // If target robot has resistance tp the ability (based on type)
                if ($trigger_options['apply_resistance_modifiers'] != false && $this->has_resistance($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                } else {
                    $this_ability->ability_results['flag_resistance'] = false;
                }

                // If target robot has resistance tp the ability (based on type2)
                if ($trigger_options['apply_resistance_modifiers'] != false && $this->has_resistance($this_ability->damage_options['damage_type2'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                }

                // If target robot has immunity to the ability (based on type)
                if ($trigger_options['apply_immunity_modifiers'] != false && $this->has_immunity($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                } else {
                    $this_ability->ability_results['flag_immunity'] = false;
                }

                // If target robot has immunity to the ability (based on type2)
                if ($trigger_options['apply_immunity_modifiers'] != false && $this->has_immunity($this_ability->damage_options['damage_type2'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                }

            }

            // Apply position boosts if allowed to
            if ($trigger_options['apply_position_modifiers'] != false){

                // If this robot is not in the active position
                if ($this->robot_position != 'active'){
                    // Collect the current key of the robot and apply damage mods
                    $temp_damage_key = $this->robot_key + 1;
                    $temp_damage_resistor = (10 - $temp_damage_key) / 10;
                    $new_damage_amount = round($damage_amount * $temp_damage_resistor);
                    // DEBUG
                    $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | position_modifier_damage | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_damage_resistor.') = '.$new_damage_amount.'');
                    $damage_amount = $new_damage_amount;
                }

            }

        }

        // Collect the first and second ability type else "none" for multipliers
        $temp_ability_damage_type = !empty($this_ability->damage_options['damage_type']) ? $this_ability->damage_options['damage_type'] : 'none';
        $temp_ability_damage_type2 = !empty($this_ability->damage_options['damage_type2']) ? $this_ability->damage_options['damage_type2'] : '';

        // Apply field multipliers preemtively if there are any
        if ($trigger_options['apply_field_modifiers'] != false && $this_ability->damage_options['damage_modifiers'] && !empty($this->field->field_multipliers)){
            // Collect the multipliters for easier
            $field_multipliers = $this->field->field_multipliers;
            // If there's a damage booster, apply that first
            if (isset($field_multipliers['damage'])){
                $new_damage_amount = round($damage_amount * $field_multipliers['damage']);
                // DEBUG
                $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | field_multiplier_damage | '.$damage_amount.' = round('.$damage_amount.' * '.$field_multipliers['damage'].') = '.$new_damage_amount.'');
                $damage_amount = $new_damage_amount;
            }
            // Loop through all the other type multipliers one by one if this ability has a type
            $skip_types = array('damage', 'recovery', 'experience');
            if (true){ //!empty($this_ability->damage_options['damage_type'])
                // Loop through all the other type multipliers one by one if this ability has a type
                foreach ($field_multipliers AS $temp_type => $temp_multiplier){
                    // Skip non-type and special fields for this calculation
                    if (in_array($temp_type, $skip_types)){ continue; }
                    // If this ability's first type matches the multiplier, apply it
                    if ($temp_ability_damage_type == $temp_type || $temp_ability_damage_type2 == $temp_type){
                        $new_damage_amount = round($damage_amount * $temp_multiplier);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_multiplier.') = '.$new_damage_amount.'');
                        $damage_amount = $new_damage_amount;
                    }
                }
            }
            if (!empty($this_ability->damage_options['damage_type2'])){
                foreach ($field_multipliers AS $temp_type => $temp_multiplier){
                    // Skip non-type and special fields for this calculation
                    if (in_array($temp_type, $skip_types)){ continue; }
                    // If this ability's type matches the multiplier, apply it
                    if ($this_ability->damage_options['damage_type2'] == $temp_type){
                        $new_damage_amount = round($damage_amount * $temp_multiplier);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_multiplier.') = '.$new_damage_amount.'');
                        $damage_amount = $new_damage_amount;
                    }
                }
            }
        }

        // Update the ability results with the the trigger kind and damage details
        $this_ability->ability_results['trigger_kind'] = 'damage';
        $this_ability->ability_results['damage_kind'] = $this_ability->damage_options['damage_kind'];
        $this_ability->ability_results['damage_type'] = $this_ability->damage_options['damage_type'];
        $this_ability->ability_results['damage_type2'] = $this_ability->damage_options['damage_type2'];

        // If the success rate was not provided, auto-calculate
        if ($this_ability->damage_options['success_rate'] == 'auto'){
            // If this robot is targetting itself, default to ability accuracy
            if ($this->robot_id == $target_robot->robot_id){
                // Update the success rate to the ability accuracy value
                $this_ability->damage_options['success_rate'] = $this_ability->ability_accuracy;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($target_robot_speed_start <= 0 && $this->robot_speed > 0){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->damage_options['success_rate'] = 0;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($this->robot_speed <= 0 || $this_ability->ability_accuracy == 100){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->damage_options['success_rate'] = 100;
            }
            // Otherwise, calculate the success rate based on relative speeds
            else {
                // Collect this ability's accuracy stat for modification
                $this_ability_accuracy = $this_ability->ability_accuracy;
                // If the target was faster/slower, boost/lower the ability accuracy
                if ($target_robot_speed_start > $this->robot_speed
                    || $target_robot_speed_start < $this->robot_speed){
                    $this_modifier = $target_robot_speed_start / $this->robot_speed;
                    //$this_ability_accuracy = ceil($this_ability_accuracy * $this_modifier);
                    $this_ability_accuracy = ceil($this_ability_accuracy * 0.95) + ceil(($this_ability_accuracy * 0.05) * $this_modifier);
                    if ($this_ability_accuracy > 100){ $this_ability_accuracy = 100; }
                    elseif ($this_ability_accuracy < 0){ $this_ability_accuracy = 0; }
                }
                // Update the success rate to the ability accuracy value
                $this_ability->damage_options['success_rate'] = $this_ability_accuracy;
                //$this_ability->ability_results['this_text'] .= '';
            }
        }

        // If the failure rate was not provided, auto-calculate
        if ($this_ability->damage_options['failure_rate'] == 'auto'){
            // Set the failure rate to the difference of success vs failure (100% base)
            $this_ability->damage_options['failure_rate'] = 100 - $this_ability->damage_options['success_rate'];
            if ($this_ability->damage_options['failure_rate'] < 0){
                $this_ability->damage_options['failure_rate'] = 0;
            }
        }

        // If this robot is in speed break, increase success rate, reduce failure
        if ($this->robot_speed == 0 && $this_ability->damage_options['success_rate'] > 0){
            $this_ability->damage_options['success_rate'] = ceil($this_ability->damage_options['success_rate'] * 2);
            $this_ability->damage_options['failure_rate'] = ceil($this_ability->damage_options['failure_rate'] / 2);
        }
        // If the target robot is in speed break, decease the success rate, increase failure
        elseif ($target_robot_speed_start == 0 && $this_ability->damage_options['success_rate'] > 0){
            $this_ability->damage_options['success_rate'] = ceil($this_ability->damage_options['success_rate'] / 2);
            $this_ability->damage_options['failure_rate'] = ceil($this_ability->damage_options['failure_rate'] * 2);
        }

        // If success rate is at 100%, auto-set the result to success
        if ($this_ability->damage_options['success_rate'] == 100){
            // Set this ability result as a success
            $this_ability->damage_options['failure_rate'] = 0;
            $this_ability->ability_results['this_result'] = 'success';
        }
        // Else if the success rate is at 0%, auto-set the result to failure
        elseif ($this_ability->damage_options['success_rate'] == 0){
            // Set this ability result as a failure
            $this_ability->damage_options['failure_rate'] = 100;
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise, use a weighted random generation to get the result
        else {
            // Calculate whether this attack was a success, based on the success vs. failure rate
            $this_ability->ability_results['this_result'] = rpg_functions::weighted_chance(
                array('success','failure'),
                array($this_ability->damage_options['success_rate'], $this_ability->damage_options['failure_rate'])
                );
        }

        // If this is ENERGY damage and this robot is already disabled
        if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->robot_energy <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // If this is WEAPONS recovery and this robot is already at empty ammo
        elseif ($this_ability->damage_options['damage_kind'] == 'weapons' && $this->robot_weapons <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if ATTACK damage but attack is already zero
        elseif ($this_ability->damage_options['damage_kind'] == 'attack' && $this->robot_attack <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if DEFENSE damage but defense is already zero
        elseif ($this_ability->damage_options['damage_kind'] == 'defense' && $this->robot_defense <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if SPEED damage but speed is already zero
        elseif ($this_ability->damage_options['damage_kind'] == 'speed' && $this->robot_speed <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }

        // If this robot has immunity to the ability, hard-code a failure result
        if ($this_ability->ability_results['flag_immunity']){
            $this_ability->ability_results['this_result'] = 'failure';
            $this->flags['triggered_immunity'] = true;
            // Generate the status text based on flags
            $this_flag_name = 'immunity_text';
            if (isset($this_ability->damage_options[$this_flag_name])){
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->damage_options[$this_flag_name].'<br /> ';
            }
        }

        // If the attack was a success, proceed normally
        if ($this_ability->ability_results['this_result'] == 'success'){

            // Create the experience multiplier if not already set
            if (!isset($this->field->field_multipliers['experience'])){ $this->field->field_multipliers['experience'] = 1; }
            elseif ($this->field->field_multipliers['experience'] < 0.1){ $this->field->field_multipliers['experience'] = 0.1; }
            elseif ($this->field->field_multipliers['experience'] > 9.9){ $this->field->field_multipliers['experience'] = 9.9; }

            // If modifiers are not turned off
            if ($trigger_options['apply_modifiers'] != false){

                // Update this robot's internal flags based on ability effects
                if (!empty($this_ability->ability_results['flag_weakness'])){
                    $this->flags['triggered_weakness'] = true;
                    if (!isset($this->counters['triggered_weakness'])){ $this->counters['triggered_weakness'] = 0; }
                    $this->counters['triggered_weakness'] += 1;
                    if (!isset($this->values['triggered_weaknesses'])){ $this->values['triggered_weaknesses'] = array(); }
                    $this->values['triggered_weaknesses'][] = array(
                        'id' => $this_ability->ability_id,
                        'token' => $this_ability->ability_token,
                        'type' => $this_ability->ability_type,
                        'type2' => $this_ability->ability_type2
                        );
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){
                        $this->field->field_multipliers['experience'] += 0.1;
                        $this_ability->damage_options['damage_kickback']['x'] = ceil($this_ability->damage_options['damage_kickback']['x'] * 2);
                    }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_affinity'])){
                    $this->flags['triggered_affinity'] = true;
                    if (isset($this->counters['triggered_affinity'])){ $this->counters['triggered_affinity'] += 1; }
                    else { $this->counters['triggered_affinity'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_resistance'])){
                    $this->flags['triggered_resistance'] = true;
                    if (isset($this->counters['triggered_resistance'])){ $this->counters['triggered_resistance'] += 1; }
                    else { $this->counters['triggered_resistance'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_critical'])){
                    $this->flags['triggered_critical'] = true;
                    if (isset($this->counters['triggered_critical'])){ $this->counters['triggered_critical'] += 1; }
                    else { $this->counters['triggered_critical'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){
                        $this->field->field_multipliers['experience'] += 0.1;
                        $this_ability->damage_options['damage_kickback']['x'] = ceil($this_ability->damage_options['damage_kickback']['x'] * 2);
                    }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }

            }

            // Update the field session with any changes
            $this->field->update_session();

            // Update this robot's frame based on damage type
            $this->robot_frame = $this_ability->damage_options['damage_frame'];
            $this->player->set_frame((($this->robot_id != $target_robot->robot_id || $trigger_options['referred_damage']) ? 'damage' : 'base'));
            $this_ability->ability_frame = $this_ability->damage_options['ability_success_frame'];
            $this_ability->ability_frame_offset = $this_ability->damage_options['ability_success_frame_offset'];

            // Display the success text, if text has been provided
            if (!empty($this_ability->damage_options['success_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->damage_options['success_text'];
            }

            // Collect the damage amount argument from the function
            $this_ability->ability_results['this_amount'] = $damage_amount;

            // If we're not dealing with a percentage-based amount, apply stat mods
            if ($trigger_options['apply_stat_modifiers'] != false && !$this_ability->damage_options['damage_percent']){

                // Only apply ATTACK/DEFENSE mods if this robot is not targetting itself and it's ENERGY based damage
                if ($this_ability->damage_options['damage_kind'] == 'energy' && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_damage'])){

                    // Backup the current ammount before stat multipliers
                    $temp_amount_backup = $this_ability->ability_results['this_amount'];

                    // If this robot's defense is at absolute zero, and the target's attack isnt, OHKO
                    if ($this->robot_defense <= 0 && $target_robot_attack_start >= 1){
                        // Set the new damage amount to OHKO this robot
                        $temp_new_amount = $this->robot_base_energy;
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | '.$this->robot_token.'_defense_break | D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif the target robot's attack is at absolute zero, and the this's defense isnt, NOKO
                    elseif ($target_robot_attack_start <= 0 && $this->robot_defense >= 1){
                        // Set the new damage amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break | A:'.$target_robot_attack_start.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif this robot's defense is at absolute zero and the target's attack is too, NOKO
                    elseif ($this->robot_defense <= 0 && $target_robot_attack_start <= 0){
                        // Set the new damage amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break and '.$this->robot_token.'_defense_break | A:'.$target_robot_attack_start.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Otherwise if both robots have normal stats, calculate the new amount normally
                    else {

                        // Check to make sure starforce is enabled right now
                        $temp_starforce_enabled = true;
                        if (!empty($this->player->counters['dark_elements'])){ $temp_starforce_enabled = false; }
                        if (!empty($target_robot->player->counters['dark_elements'])){ $temp_starforce_enabled = false; }
                        if (empty($target_robot->player->player_starforce[$temp_ability_damage_type]) && empty($target_robot->player->player_starforce[$temp_ability_damage_type2])){ $temp_starforce_enabled = false; }

                        // Collect the target's attack stat and this robot's defense values
                        $target_robot_attack = $target_robot_attack_start;
                        $this_robot_defense = $this->robot_defense;

                        if (MMRPG_CONFIG_DEBUG_MODE && $temp_starforce_enabled){ $this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | attack_vs_defense | before_starforce | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' '); }

                        // If the target player has any starforce for type1, apply it to the attack
                        if ($temp_starforce_enabled && !empty($target_robot->player->player_starforce[$temp_ability_damage_type])){
                            $temp_attack_boost = $target_robot->player->player_starforce[$temp_ability_damage_type] * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                            $temp_new_attack = $target_robot_attack + $temp_attack_boost;
                            $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | target_starforce | '.$temp_ability_damage_type.'_boost = '.$temp_attack_boost.' | robot_attack = round('.$target_robot_attack.' + '.$temp_attack_boost.') = '.$temp_new_attack.'');
                            $target_robot_attack = $temp_new_attack;
                            // If the target player has any starforce for type2, apply it to the attack
                            if (!empty($target_robot->player->player_starforce[$temp_ability_damage_type2])){
                                $temp_attack_boost = $target_robot->player->player_starforce[$temp_ability_damage_type2] * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                                $temp_new_attack = $target_robot_attack + $temp_attack_boost;
                                $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | target_starforce | '.$temp_ability_damage_type2.'_boost = '.$temp_attack_boost.' | robot_attack = round('.$target_robot_attack.' + '.$temp_attack_boost.') = '.$temp_new_attack.'');
                                $target_robot_attack = $temp_new_attack;
                            }
                        }

                        // If this player has any starforce for type1, apply it to the defense
                        if ($temp_starforce_enabled && !empty($this->player->player_starforce[$temp_ability_damage_type])){
                            $temp_defense_boost = $this->player->player_starforce[$temp_ability_damage_type] * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                            $temp_new_defense = $this_robot_defense + $temp_defense_boost;
                            $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | this_starforce | '.$temp_ability_damage_type.'_boost = '.$temp_defense_boost.' | robot_defense = round('.$this_robot_defense.' + '.$temp_defense_boost.') = '.$temp_new_defense.'');
                            $this_robot_defense = $temp_new_defense;
                            // If the target player has any starforce for type2, apply it to the defense
                            if (!empty($this->player->player_starforce[$temp_ability_damage_type2])){
                                $temp_defense_boost = $this->player->player_starforce[$temp_ability_damage_type2] * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                                $temp_new_defense = $this_robot_defense + $temp_defense_boost;
                                $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | this_starforce | '.$temp_ability_damage_type2.'_boost = '.$temp_defense_boost.' | robot_defense = round('.$this_robot_defense.' + '.$temp_defense_boost.') = '.$temp_new_defense.'');
                                $this_robot_defense = $temp_new_defense;
                            }
                        }

                        if (MMRPG_CONFIG_DEBUG_MODE && $temp_starforce_enabled){ $this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | attack_vs_defense | after_starforce | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' '); }

                        // Set the new damage amount relative to this robot's defense and the target robot's attack
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * ($target_robot_attack / $this_robot_defense));

                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | normal_damage | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * ('.$target_robot_attack.' / '.$this_robot_defense.')) = '.$temp_new_amount.'');

                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;

                    }

                    // If this robot started out above zero but is now absolute zero, round up
                    if ($temp_amount_backup > 0 && $this_ability->ability_results['this_amount'] == 0){ $this_ability->ability_results['this_amount'] = 1; }

                }

                // If this is a critical hit (lucky, based on turn and level)
                $temp_flag_critical = $target_robot->robot_class != 'mecha' ? rpg_functions::critical_turn($this_battle->counters['battle_turn'], $target_robot->robot_level, $target_robot->robot_item) : false;
                if ($temp_flag_critical){
                    $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] * $this_ability->damage_options['critical_multiplier'];
                    $this_ability->ability_results['flag_critical'] = true;
                    // DEBUG
                    $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_critical | x '.$this_ability->damage_options['critical_multiplier'].' = '.$this_ability->ability_results['this_amount'].'');
                } else {
                    $this_ability->ability_results['flag_critical'] = false;
                }

            }

            // Only apply weakness, resistance, etc. if allowed to
            if ($trigger_options['apply_type_modifiers'] != false){

                // If this robot has a weakness to the ability (based on type)
                if ($this_ability->ability_results['flag_weakness']){
                    $loop_count = $this_ability->ability_results['counter_weaknesses'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->damage_options['weakness_multiplier']);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_weakness ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->damage_options['weakness_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot resists the ability (based on type)
                if ($this_ability->ability_results['flag_resistance']){
                    $loop_count = $this_ability->ability_results['counter_resistances'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->damage_options['resistance_multiplier']);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_resistance ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->damage_options['resistance_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot is immune to the ability (based on type)
                if ($this_ability->ability_results['flag_immunity']){
                    $loop_count = $this_ability->ability_results['counter_immunities'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $this_ability->ability_results['this_amount'] = round($this_ability->ability_results['this_amount'] * $this_ability->damage_options['immunity_multiplier']);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_immunity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->damage_options['immunity_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

            }

            // Only apply other modifiers if allowed to
            if ($trigger_options['apply_modifiers'] != false){

                // If this robot has an attachment with a damage multiplier
                if (!empty($this->robot_attachments)){
                    foreach ($this->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage input breaker value set
                            if (isset($temp_info['attachment_damage_input_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage input booster value set
                            if (isset($temp_info['attachment_damage_input_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type.' ='.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_input_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_input_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }

                // If the target robot has an attachment with a damage multiplier
                if (!empty($target_robot->robot_attachments)){
                    foreach ($target_robot->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage output breaker value set
                            if (isset($temp_info['attachment_damage_output_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage output booster value set
                            if (isset($temp_info['attachment_damage_output_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_output_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type2.' ='.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_output_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }


            }

            // Generate the flag string for easier parsing
            $this_flag_string = array();
            if ($this_ability->ability_results['flag_immunity']){ $this_flag_string[] = 'immunity'; }
            elseif ($trigger_options['apply_type_modifiers'] != false){
                if (!empty($this_ability->ability_results['flag_weakness'])){ $this_flag_string[] = 'weakness'; }
                if (!empty($this_ability->ability_results['flag_affinity'])){ $this_flag_string[] = 'affinity'; }
                if (!empty($this_ability->ability_results['flag_resistance'])){ $this_flag_string[] = 'resistance'; }
                if ($trigger_options['apply_modifiers'] != false && !$this_ability->damage_options['damage_percent']){
                    if (!empty($this_ability->ability_results['flag_critical'])){ $this_flag_string[] = 'critical'; }
                }
            }
            $this_flag_name = (!empty($this_flag_string) ? implode('_', $this_flag_string).'_' : '').'text';

            // Generate the status text based on flags
            if (isset($this_ability->damage_options[$this_flag_name])){
                //$event_options['console_container_height'] = 2;
                //$this_ability->ability_results['this_text'] .= '<br />';
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->damage_options[$this_flag_name];
            }

            // Display a break before the damage amount if other text was generated
            if (!empty($this_ability->ability_results['this_text'])){
                $this_ability->ability_results['this_text'] .= '<br />';
            }

            // Ensure the damage amount is always at least one, unless absolute zero
            if ($this_ability->ability_results['this_amount'] < 1 && $this_ability->ability_results['this_amount'] > 0){ $this_ability->ability_results['this_amount'] = 1; }

            // Reference the requested damage kind with a shorter variable
            $this_ability->damage_options['damage_kind'] = strtolower($this_ability->damage_options['damage_kind']);
            $damage_stat_name = 'robot_'.$this_ability->damage_options['damage_kind'];

            // Inflict the approiate damage type based on the damage options
            switch ($damage_stat_name){

                // If this is an ATTACK type damage trigger
                case 'robot_attack': {
                    // Inflict attack damage on the target's internal stat
                    $this->robot_attack = $this->robot_attack - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's attack below zero
                    if ($this->robot_attack < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_attack * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_attack;
                        // Zero out the robots attack
                        $this->robot_attack = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the ATTACK case
                    break;
                }
                // If this is an DEFENSE type damage trigger
                case 'robot_defense': {
                    // Inflict defense damage on the target's internal stat
                    $this->robot_defense = $this->robot_defense - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's defense below zero
                    if ($this->robot_defense < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_defense * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_defense;
                        // Zero out the robots defense
                        $this->robot_defense = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the DEFENSE case
                    break;
                }
                // If this is an SPEED type damage trigger
                case 'robot_speed': {
                    // Inflict attack damage on the target's internal stat
                    $this->robot_speed = $this->robot_speed - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's speed below zero
                    if ($this->robot_speed < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_speed * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_speed;
                        // Zero out the robots speed
                        $this->robot_speed = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the SPEED case
                    break;
                }
                // If this is a WEAPONS type damage trigger
                case 'robot_weapons': {
                    // Inflict weapon damage on the target's internal stat
                    $this->robot_weapons = $this->robot_weapons - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's weapons below zero
                    if ($this->robot_weapons < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_weapons * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_weapons;
                        // Zero out the robots weapons
                        $this->robot_weapons = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the WEAPONS case
                    break;
                }
                // If this is an ENERGY type damage trigger
                case 'robot_energy': default: {
                    // Inflict the actual damage on the robot
                    $this->robot_energy = $this->robot_energy - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot into overkill, recalculate the damage
                    if ($this->robot_energy < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_energy * -1;
                        if ($this_ability->ability_results['this_overkill'] > $this->robot_base_energy){ $this_ability->ability_results['this_overkill'] = $this->robot_base_energy; }
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_energy;
                        // Zero out the robots energy
                        $this->robot_energy = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // If the robot's energy has dropped to zero, disable them
                    if ($this->robot_energy == 0){
                        // Change the status to disabled
                        $this->robot_status = 'disabled';
                        // Remove any attachments this robot has
                        if (!empty($this->robot_attachments)){
                            foreach ($this->robot_attachments AS $token => $info){
                                if (empty($info['sticky'])){ unset($this->robot_attachments[$token]); }
                            }
                        }
                    }
                    // Break from the ENERGY case
                    break;
                }

            }

            // Define the print variables to return
            $this_ability->ability_results['print_strikes'] = '<span class="damage_strikes">'.(!empty($this_ability->ability_results['total_strikes']) ? $this_ability->ability_results['total_strikes'] : 0).'</span>';
            $this_ability->ability_results['print_misses'] = '<span class="damage_misses">'.(!empty($this_ability->ability_results['total_misses']) ? $this_ability->ability_results['total_misses'] : 0).'</span>';
            $this_ability->ability_results['print_result'] = '<span class="damage_result">'.(!empty($this_ability->ability_results['total_result']) ? $this_ability->ability_results['total_result'] : 0).'</span>';
            $this_ability->ability_results['print_amount'] = '<span class="damage_amount">'.(!empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0).'</span>';
            $this_ability->ability_results['print_overkill'] = '<span class="damage_overkill">'.(!empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0).'</span>';

            // Add the final damage text showing the amount based on damage type
            if ($this_ability->damage_options['damage_kind'] == 'energy'){
                $this_ability->ability_results['this_text'] .= "{$this->print_name()} takes {$this_ability->ability_results['print_amount']} life energy damage";
                $this_ability->ability_results['this_text'] .= ($this_ability->ability_results['this_overkill'] > 0 && $this->player->player_side == 'right' ? " and {$this_ability->ability_results['print_overkill']} overkill" : '');
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise add the final damage text showing the amount based on weapon energy damage
            elseif ($this_ability->damage_options['damage_kind'] == 'weapons'){
                $this_ability->ability_results['this_text'] .= "{$this->print_name()} takes {$this_ability->ability_results['print_amount']} weapon energy damage";
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise, if this is one of the robot's other internal stats
            elseif ($this_ability->damage_options['damage_kind'] == 'attack'
                || $this_ability->damage_options['damage_kind'] == 'defense'
                || $this_ability->damage_options['damage_kind'] == 'speed'){
                // Print the result based on if the stat will go any lower
                if ($this_ability->ability_results['this_amount'] > 0){
                    $this_ability->ability_results['this_text'] .= "{$this->print_name()}&#39;s {$this_ability->damage_options['damage_kind']} fell by {$this_ability->ability_results['print_amount']}";
                    $this_ability->ability_results['this_text'] .= '!<br />';
                }
                // Otherwise if the stat wouldn't go any lower
                else {

                    // Update this robot's frame based on damage type
                    $this_ability->ability_frame = $this_ability->damage_options['ability_failure_frame'];
                    $this_ability->ability_frame_span = $this_ability->damage_options['ability_failure_frame_span'];
                    $this_ability->ability_frame_offset = $this_ability->damage_options['ability_failure_frame_offset'];

                    // Display the failure text, if text has been provided
                    if (!empty($this_ability->damage_options['failure_text'])){
                        $this_ability->ability_results['this_text'] .= $this_ability->damage_options['failure_text'].' ';
                    }
                }
            }

        }
        // Otherwise, if the attack was a failure
        else {

            // Update this robot's frame based on damage type
            $this_ability->ability_frame = $this_ability->damage_options['ability_failure_frame'];
            $this_ability->ability_frame_span = $this_ability->damage_options['ability_failure_frame_span'];
            $this_ability->ability_frame_offset = $this_ability->damage_options['ability_failure_frame_offset'];

            // Update the damage and overkilll amounts to reflect zero damage
            $this_ability->ability_results['this_amount'] = 0;
            $this_ability->ability_results['this_overkill'] = 0;

            // Display the failure text, if text has been provided
            if (!$this_ability->ability_results['flag_immunity'] && !empty($this_ability->damage_options['failure_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->damage_options['failure_text'].' ';
            }

        }

        // Update this robot's history with the triggered damage amount
        $this->history['triggered_damage'][] = $this_ability->ability_results['this_amount'];
        // Update the robot's history with the triggered damage types
        if (!empty($this_ability->ability_results['damage_type'])){
            $temp_types = array();
            $temp_types[] = $this_ability->ability_results['damage_type'];
            if (!empty($this_ability->ability_results['damage_type2'])){ $temp_types[] = $this_ability->ability_results['damage_type2']; }
            $this->history['triggered_damage_types'][] = $temp_types;
        } else {
            $this->history['triggered_damage_types'][] = array();
        }
        // Update this robot's history with the overkill if applicable
        if (!empty($this_ability->ability_results['this_overkill'])){
            $this->counters['defeat_overkill'] = isset($this->counters['defeat_overkill']) ? $this->counters['defeat_overkill'] + $this_ability->ability_results['this_overkill'] : $this_ability->ability_results['this_overkill'];
        }

        // Update the damage result total variables
        $this_ability->ability_results['total_amount'] += !empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0;
        $this_ability->ability_results['total_overkill'] += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
        if ($this_ability->ability_results['this_result'] == 'success'){ $this_ability->ability_results['total_strikes']++; }
        else { $this_ability->ability_results['total_misses']++; }
        $this_ability->ability_results['total_actions'] = $this_ability->ability_results['total_strikes'] + $this_ability->ability_results['total_misses'];
        if ($this_ability->ability_results['total_result'] != 'success'){ $this_ability->ability_results['total_result'] = $this_ability->ability_results['this_result']; }
        $event_options['this_ability_results'] = $this_ability->ability_results;

        // Update internal variables
        $target_robot->update_session();
        $target_robot->player->update_session();
        $this->update_session();
        $this->player->update_session();

        // If this robot was at full energy but is now at zero, it's a OHKO
        $this_robot_energy_ohko = false;
        if ($this->robot_energy <= 0 && $this_robot_energy_start_max){
            // DEBUG
            $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | damage_result_OHKO! | Start:'.$this_robot_energy_start.' '.($this_robot_energy_start_max ? '(MAX!)' : '-').' | Finish:'.$this->robot_energy);
            // Ensure the attacking player was a human
            if ($this->player->player_side == 'right'){
                $this_robot_energy_ohko = true;
                // Increment the field multipliers for items
                //if (!isset($this->field->field_multipliers['items'])){ $this->field->field_multipliers['items'] = 1; }
                //$this->field->field_multipliers['items'] += 0.1;
                //$this->field->update_session();
            }
        }

        // Generate an event with the collected damage results based on damage type
        if ($this->robot_id == $target_robot->robot_id){ //$this_ability->damage_options['damage_kind'] == 'energy'
            $event_options['console_show_target'] = false;
            $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
            $this_battle->events_create($target_robot, $this, $this_ability->damage_options['damage_header'], $this_ability->ability_results['this_text'], $event_options);
        } else {
            $event_options['console_show_target'] = false;
            $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
            $this_battle->events_create($this, $target_robot, $this_ability->damage_options['damage_header'], $this_ability->ability_results['this_text'], $event_options);
        }

        // If this robot was an unlockable and hit with an element, remove it
        $temp_robot_rewards = $this_battle->get_robot_rewards();
        if ($this_ability->ability_class != 'item'
            && $this->player->player_id != $target_robot->player->player_id
            && !empty($temp_robot_rewards)
            && (!empty($this_ability->ability_type) || !empty($this_ability->ability_type2))){
            foreach ($temp_robot_rewards AS $key => $reward){
                if ($reward['token'] == $this->robot_token){
                    $event_options['console_show_target'] = false;
                    $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
                    $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
                    $this_battle->events_create($this, $target_robot,
                        'Master Core Data Damaged',
                        $this->print_name().'\'s core was damaged by the attack!<br /> '.
                        'The robot can\'t be unlocked anymore!',
                        $event_options);
                    unset($temp_robot_rewards[$key]);
                    $temp_robot_rewards = array_values($temp_robot_rewards);
                    $this_battle->set_robot_rewards($temp_robot_rewards);
                    break;
                }
            }

        }

        // Restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->set_frame($this_player_backup_frame);
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->set_frame($target_player_backup_frame);
        $this_ability->ability_frame = $this_ability_backup_frame;

        // Update internal variables
        $target_robot->update_session();
        $this->update_session();
        $this_ability->update_session();

        // If this robot has been disabled, add a defeat attachment
        if ($this->robot_status == 'disabled'){
            // Define this ability's attachment token
            $temp_frames = array(0,4,1,5,2,6,3,7,4,8,5,9,0,1,2,3,4,5,6,7,8,9);
            shuffle($temp_frames);
            $this_attachment_token = 'ability_attachment-defeat';
            $this_attachment_info = array(
                'class' => 'ability',
                'ability_token' => 'attachment-defeat',
                'attachment_flag_defeat' => true,
                'ability_frame' => 0,
                'ability_frame_animate' => $temp_frames,
                'ability_frame_offset' => array('x' => 0, 'y' => -10, 'z' => -10)
                );

            // If the attachment doesn't already exists, add it to the robot
            if (!isset($this->robot_attachments[$this_attachment_token])){
                //$this->robot_attachments[$this_attachment_token] =  $this_attachment_info;
                $this->update_session();
            }

        }

        // If this robot was disabled, process experience for the target
        if ($this->robot_status == 'disabled' && $trigger_disabled){
            $trigger_options = array();
            if ($this_robot_energy_ohko){ $trigger_options['item_multiplier'] = 2.0; }
            $this->trigger_disabled($target_robot, $this_ability, $trigger_options);
        }
        // Otherwise, if the target robot was not disabled
        elseif ($this->robot_status != 'disabled'){
            // -- CHECK ATTACHMENTS -- //

            // Ensure the ability was a success before checking attachments
            if ($this_ability->ability_results['this_result'] == 'success'){
                // If this robot has any attachments, loop through them
                if (!empty($this->robot_attachments)){
                    $this_battle->events_debug(__FILE__, __LINE__, $this->robot_token.' | has_attachments | '.implode(', ', array_keys($this->robot_attachments)));
                    $attachment_key = 0;
                    foreach ($this->robot_attachments AS $attachment_token => $attachment_info){
                        // Ensure this ability has a type before checking weaknesses, resistances, etc.
                        if (!empty($this_ability->ability_type)){
                            // If this attachment has weaknesses defined and this ability is a match
                            if (!empty($attachment_info['attachment_weaknesses'])
                                && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses']) || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))){
                                $this_battle->events_debug(__FILE__, __LINE__, 'checkpoint weaknesses');
                                // Remove this attachment and inflict damage on the robot
                                unset($this->robot_attachments[$attachment_token]);
                                $this->update_session();
                                if ($attachment_info['attachment_destroy'] !== false){
                                    $temp_info = array('ability_id' => (MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX + $attachment_key + 100), 'ability_token' => $attachment_info['ability_token']);
                                    $temp_attachment = new rpg_ability($this->player, $this, $temp_info);
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
                                        $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                                    }
                                }
                                // If this robot was disabled, process experience for the target
                                if ($this->robot_status == 'disabled'){ break; }
                            }
                            $attachment_key++;
                        }

                    }
                }

            }

        }


        // Return the final damage results
        return $this_ability->ability_results;
    }


    // Define a trigger for inflicting all types of recovery on this robot
    public function trigger_recovery($target_robot, $this_ability, $recovery_amount, $trigger_disabled = true, $trigger_options = array()){

        // Import global variables
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // Generate default trigger options if not set
        if (!isset($trigger_options['apply_modifiers'])){ $trigger_options['apply_modifiers'] = $this_ability->recovery_options['recovery_modifiers'] == false ? false : true; }
        if (!isset($trigger_options['apply_type_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_type_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_weakness_modifiers']) || $trigger_options['apply_weakness_modifiers'] == false){ $trigger_options['apply_weakness_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_resistance_modifiers']) || $trigger_options['apply_resistance_modifiers'] == false){ $trigger_options['apply_resistance_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_affinity_modifiers']) || $trigger_options['apply_affinity_modifiers'] == false){ $trigger_options['apply_affinity_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_immunity_modifiers']) || $trigger_options['apply_immunity_modifiers'] == false){ $trigger_options['apply_immunity_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_core_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_core_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_field_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_field_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_position_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_position_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_stat_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_stat_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['referred_recovery'])){ $trigger_options['referred_recovery'] = false; }
        if (!isset($trigger_options['referred_player'])){ $trigger_options['referred_player'] = false; }
        if (!isset($trigger_options['referred_robot'])){ $trigger_options['referred_robot'] = false; }
        if (!isset($trigger_options['referred_energy'])){ $trigger_options['referred_energy'] = false; }
        if (!isset($trigger_options['referred_attack'])){ $trigger_options['referred_attack'] = false; }
        if (!isset($trigger_options['referred_defense'])){ $trigger_options['referred_defense'] = false; }
        if (!isset($trigger_options['referred_speed'])){ $trigger_options['referred_speed'] = false; }

        // If the battle has already ended, return false
        if ($this_battle->battle_status == 'complete'){ return false; }

        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Check if this robot is at full health before triggering
        $this_robot_energy_start = $this->robot_energy;
        $this_robot_energy_start_max = $this_robot_energy_start >= $this->robot_base_energy ? true : false;

        // If this recovery has been referred, update target variables
        if (!empty($trigger_options['referred_recovery'])){

            // If a referred player was provided, replace the target player object
            if (!empty($trigger_options['referred_player'])){
                $target_player = new rpg_player($trigger_options['referred_player']);
            }

            // If a referred player and robot were provided, replace the target robot object
            if (!empty($trigger_options['referred_player']) && !empty($trigger_options['referred_robot'])){
                $target_robot = new rpg_robot($target_player, $trigger_options['referred_robot']);
            }

            // Collect references to referred recovery stats if they exist
            $target_robot_energy_start = !empty($trigger_options['referred_energy']) ? $trigger_options['referred_energy'] : $target_robot->robot_energy;
            $target_robot_attack_start = !empty($trigger_options['referred_attack']) ? $trigger_options['referred_attack'] : $target_robot->robot_attack;
            $target_robot_defense_start = !empty($trigger_options['referred_defense']) ? $trigger_options['referred_defense'] : $target_robot->robot_defense;
            $target_robot_speed_start = !empty($trigger_options['referred_speed']) ? $trigger_options['referred_speed'] : $target_robot->robot_speed;

        } else {

            // Collect references to the target robots stats
            $target_robot_energy_start = $target_robot->robot_energy;
            $target_robot_attack_start = $target_robot->robot_attack;
            $target_robot_defense_start = $target_robot->robot_defense;
            $target_robot_speed_start = $target_robot->robot_speed;

        }

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_results'] = array();

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update the recovery to whatever was supplied in the argument
        //if ($this_ability->recovery_options['recovery_percent'] && $recovery_amount > 100){ $recovery_amount = 100; }
        $this_ability->recovery_options['recovery_amount'] = $recovery_amount;

        // Collect the recovery amount argument from the function
        $this_ability->ability_results['this_amount'] = $recovery_amount;
        // DEBUG
        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | trigger_recovery |  this('.$this->robot_id.':'.$this->robot_token.') vs target('.$target_robot->robot_id.':'.$target_robot->robot_token.') <br /> recovery_start_amount:'.$this_ability->ability_results['this_amount'].' | '.'percent:'.($this_ability->recovery_options['recovery_percent'] ? 'true' : 'false').' | '.'kind:'.$this_ability->recovery_options['recovery_kind'].' | type1:'.(!empty($this_ability->recovery_options['recovery_type']) ? $this_ability->recovery_options['recovery_type'] : 'none').' | type2:'.(!empty($this_ability->recovery_options['recovery_type2']) ? $this_ability->recovery_options['recovery_type2'] : 'none').'');

        // DEBUG
        $debug = array();
        foreach ($trigger_options AS $key => $value){ $debug[] = (!$value ? '<del>' : '<span>').preg_replace('/^apply_(.*)_modifiers$/i', '$1_modifiers', $key).(!$value ? '</del>' : '</span>'); }
        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | damage_trigger_options | '.implode(', ', $debug));

        // Only apply modifiers if they have not been disabled
        if ($trigger_options['apply_modifiers'] != false){

            // Skip all weakness, resistance, etc. calculations if robot is targetting self
            if ($trigger_options['apply_type_modifiers'] != false
                && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery'])){

                // If this robot has weakness to the ability (based on type)
                if ($trigger_options['apply_weakness_modifiers'] != false && $this->has_weakness($this_ability->recovery_options['recovery_type']) && !$this->has_affinity($this_ability->recovery_options['recovery_type2'])){
                    //$this_ability->ability_results['counter_weaknesses'] += 1;
                    //$this_ability->ability_results['flag_weakness'] = true;
                    return $this->trigger_damage($target_robot, $this_ability, $recovery_amount);
                } else {
                    $this_ability->ability_results['flag_weakness'] = false;
                }

                // If this robot has weakness to the ability (based on type2)
                if ($trigger_options['apply_weakness_modifiers'] != false && $this->has_weakness($this_ability->recovery_options['recovery_type2']) && !$this->has_affinity($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_weaknesses'] += 1;
                    $this_ability->ability_results['flag_weakness'] = true;
                    return $this->trigger_damage($target_robot, $this_ability, $recovery_amount);
                }

                // If target robot has affinity to the ability (based on type)
                if ($trigger_options['apply_affinity_modifiers'] != false && $this->has_affinity($this_ability->recovery_options['recovery_type']) && !$this->has_weakness($this_ability->recovery_options['recovery_type2'])){
                    $this_ability->ability_results['counter_affinities'] += 1;
                    $this_ability->ability_results['flag_affinity'] = true;
                } else {
                    $this_ability->ability_results['flag_affinity'] = false;
                }

                // If target robot has affinity to the ability (based on type2)
                if ($trigger_options['apply_affinity_modifiers'] != false && $this->has_affinity($this_ability->recovery_options['recovery_type2']) && !$this->has_weakness($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_affinities'] += 1;
                    $this_ability->ability_results['flag_affinity'] = true;
                }

                // If target robot has resistance to the ability (based on type)
                if ($trigger_options['apply_resistance_modifiers'] != false && $this->has_resistance($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                } else {
                    $this_ability->ability_results['flag_resistance'] = false;
                }

                // If target robot has resistance to the ability (based on type2)
                if ($trigger_options['apply_resistance_modifiers'] != false && $this->has_resistance($this_ability->recovery_options['recovery_type2'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                }

                // If target robot has immunity to the ability (based on type)
                if ($trigger_options['apply_immunity_modifiers'] != false && $this->has_immunity($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                } else {
                    $this_ability->ability_results['flag_immunity'] = false;
                }

                // If target robot has immunity to the ability (based on type2)
                if ($trigger_options['apply_immunity_modifiers'] != false && $this->has_immunity($this_ability->recovery_options['recovery_type2'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                }

            }

            // Apply position boosts if allowed to
            if ($trigger_options['apply_position_modifiers'] != false){

                // If this robot is not in the active position
                if ($this->robot_position != 'active'){
                    // Collect the current key of the robot and apply recovery mods
                    $temp_recovery_key = $this->robot_key + 1;
                    $temp_recovery_resistor = (10 - $temp_recovery_key) / 10;
                    $new_recovery_amount = round($recovery_amount * $temp_recovery_resistor);
                    // DEBUG
                    $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | position_modifier_recovery | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_recovery_resistor.') = '.$new_recovery_amount.'');
                    $recovery_amount = $new_recovery_amount;
                }

            }

        }

        // Collect the first and second ability type else "none" for multipliers
        $temp_ability_recovery_type = !empty($this_ability->recovery_options['recovery_type']) ? $this_ability->recovery_options['recovery_type'] : 'none';
        $temp_ability_recovery_type2 = !empty($this_ability->recovery_options['recovery_type2']) ? $this_ability->recovery_options['recovery_type2'] : '';

        // Apply field multipliers preemtively if there are any
        if ($trigger_options['apply_field_modifiers'] != false && $this_ability->recovery_options['recovery_modifiers'] && !empty($this->field->field_multipliers)){
            // Collect the multipliters for easier
            $field_multipliers = $this->field->field_multipliers;
            // If there's a recovery booster, apply that first
            if (isset($field_multipliers['recovery'])){
                $new_recovery_amount = round($recovery_amount * $field_multipliers['recovery']);
                // DEBUG
                $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | field_multiplier_recovery | '.$recovery_amount.' = round('.$recovery_amount.' * '.$field_multipliers['recovery'].') = '.$new_recovery_amount.'');
                $recovery_amount = $new_recovery_amount;
            }
            // Loop through all the other type multipliers one by one if this ability has a type
            $skip_types = array('recovery', 'damage', 'experience');
            if (true){ //!empty($this_ability->recovery_options['recovery_type'])
                // Loop through all the other type multipliers one by one if this ability has a type
                foreach ($field_multipliers AS $temp_type => $temp_multiplier){
                    // Skip non-type and special fields for this calculation
                    if (in_array($temp_type, $skip_types)){ continue; }
                    // If this ability's type matches the multiplier, apply it
                    if ($temp_ability_recovery_type == $temp_type || $temp_ability_recovery_type2 == $temp_type){
                        $new_recovery_amount = round($recovery_amount * $temp_multiplier);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.'');
                        $recovery_amount = $new_recovery_amount;
                    }
                }
            }
            if (!empty($this_ability->recovery_options['recovery_type2'])){
                foreach ($field_multipliers AS $temp_type => $temp_multiplier){
                    // Skip non-type and special fields for this calculation
                    if (in_array($temp_type, $skip_types)){ continue; }
                    // If this ability's type matches the multiplier, apply it
                    if ($this_ability->recovery_options['recovery_type2'] == $temp_type){
                        $new_recovery_amount = round($recovery_amount * $temp_multiplier);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.'');
                        $recovery_amount = $new_recovery_amount;
                    }
                }
            }
        }

        // Update the ability results with the the trigger kind and recovery details
        $this_ability->ability_results['trigger_kind'] = 'recovery';
        $this_ability->ability_results['recovery_kind'] = $this_ability->recovery_options['recovery_kind'];
        $this_ability->ability_results['recovery_type'] = $this_ability->recovery_options['recovery_type'];
        $this_ability->ability_results['recovery_type2'] = $this_ability->recovery_options['recovery_type2'];

        // If the success rate was not provided, auto-calculate
        if ($this_ability->recovery_options['success_rate'] == 'auto'){
            // If this robot is targetting itself, default to ability accuracy
            if ($this->robot_id == $target_robot->robot_id){
                // Update the success rate to the ability accuracy value
                $this_ability->recovery_options['success_rate'] = $this_ability->ability_accuracy;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($target_robot->robot_speed <= 0 && $this->robot_speed > 0){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->recovery_options['success_rate'] = 0;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($this->robot_speed <= 0 || $this_ability->ability_accuracy == 100){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->recovery_options['success_rate'] = 100;
            }
            // Otherwise, calculate the success rate based on relative speeds
            else {
                // Collect this ability's accuracy stat for modification
                $this_ability_accuracy = $this_ability->ability_accuracy;
                // If the target was faster/slower, boost/lower the ability accuracy
                if ($target_robot_speed_start > $this->robot_speed
                    || $target_robot_speed_start < $this->robot_speed){
                    $this_modifier = $target_robot_speed_start / $this->robot_speed;
                    //$this_ability_accuracy = ceil($this_ability_accuracy * $this_modifier);
                    $this_ability_accuracy = ceil($this_ability_accuracy * 0.95) + ceil(($this_ability_accuracy * 0.05) * $this_modifier);
                    if ($this_ability_accuracy > 100){ $this_ability_accuracy = 100; }
                    elseif ($this_ability_accuracy < 0){ $this_ability_accuracy = 0; }
                }
                // Update the success rate to the ability accuracy value
                $this_ability->recovery_options['success_rate'] = $this_ability_accuracy;
                //$this_ability->ability_results['this_text'] .= '';
            }
        }

        // If the failure rate was not provided, auto-calculate
        if ($this_ability->recovery_options['failure_rate'] == 'auto'){
            // Set the failure rate to the difference of success vs failure (100% base)
            $this_ability->recovery_options['failure_rate'] = 100 - $this_ability->recovery_options['success_rate'];
            if ($this_ability->recovery_options['failure_rate'] < 0){
                $this_ability->recovery_options['failure_rate'] = 0;
            }
        }

        // If this robot is in speed break, increase success rate, reduce failure
        if ($this->robot_speed == 0 && $this_ability->recovery_options['success_rate'] > 0){
            $this_ability->recovery_options['success_rate'] = ceil($this_ability->recovery_options['success_rate'] * 2);
            $this_ability->recovery_options['failure_rate'] = ceil($this_ability->recovery_options['failure_rate'] / 2);
        }
        // If the target robot is in speed break, decease the success rate, increase failure
        elseif ($target_robot->robot_speed == 0 && $this_ability->recovery_options['success_rate'] > 0){
            $this_ability->recovery_options['success_rate'] = ceil($this_ability->recovery_options['success_rate'] / 2);
            $this_ability->recovery_options['failure_rate'] = ceil($this_ability->recovery_options['failure_rate'] * 2);
        }

        // If success rate is at 100%, auto-set the result to success
        if ($this_ability->recovery_options['success_rate'] == 100){
            // Set this ability result as a success
            $this_ability->recovery_options['failure_rate'] = 0;
            $this_ability->ability_results['this_result'] = 'success';
        }
        // Else if the success rate is at 0%, auto-set the result to failure
        elseif ($this_ability->recovery_options['success_rate'] == 0){
            // Set this ability result as a failure
            $this_ability->recovery_options['failure_rate'] = 100;
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise, use a weighted random generation to get the result
        else {
            // Calculate whether this attack was a success, based on the success vs. failure rate
            $this_ability->ability_results['this_result'] = rpg_functions::weighted_chance(
                array('success','failure'),
                array($this_ability->recovery_options['success_rate'], $this_ability->recovery_options['failure_rate'])
                );
        }

        // If this is ENERGY recovery and this robot is already at full health
        if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->robot_energy >= $this->robot_base_energy){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // If this is WEAPONS recovery and this robot is already at full ammo
        elseif ($this_ability->recovery_options['recovery_kind'] == 'weapons' && $this->robot_weapons >= $this->robot_base_weapons){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if ATTACK recovery but attack is already at 9999
        elseif ($this_ability->recovery_options['recovery_kind'] == 'attack' && $this->robot_attack >= MMRPG_SETTINGS_STATS_MAX){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if DEFENSE recovery but defense is already at 9999
        elseif ($this_ability->recovery_options['recovery_kind'] == 'defense' && $this->robot_defense >= MMRPG_SETTINGS_STATS_MAX){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if SPEED recovery but speed is already at 9999
        elseif ($this_ability->recovery_options['recovery_kind'] == 'speed' && $this->robot_speed >= MMRPG_SETTINGS_STATS_MAX){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }

        // If this robot has immunity to the ability, hard-code a failure result
        if ($this_ability->ability_results['flag_immunity']){
            $this_ability->ability_results['this_result'] = 'failure';
            $this->flags['triggered_immunity'] = true;
            // Generate the status text based on flags
            $this_flag_name = 'immunity_text';
            if (isset($this_ability->recovery_options[$this_flag_name])){
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->recovery_options[$this_flag_name].'<br /> ';
            }
        }

        // If the attack was a success, proceed normally
        if ($this_ability->ability_results['this_result'] == 'success'){

            // Create the experience multiplier if not already set
            if (!isset($this->field->field_multipliers['experience'])){ $this->field->field_multipliers['experience'] = 1; }
            elseif ($this->field->field_multipliers['experience'] < 0.1){ $this->field->field_multipliers['experience'] = 0.1; }
            elseif ($this->field->field_multipliers['experience'] > 9.9){ $this->field->field_multipliers['experience'] = 9.9; }

            // If modifiers are not turned off
            if ($trigger_options['apply_modifiers'] != false){

                // Update this robot's internal flags based on ability effects
                if (!empty($this_ability->ability_results['flag_weakness'])){
                    $this->flags['triggered_weakness'] = true;
                    if (!isset($this->counters['triggered_weakness'])){ $this->counters['triggered_weakness'] = 0; }
                    $this->counters['triggered_weakness'] += 1;
                    if (!isset($this->values['triggered_weaknesses'])){ $this->values['triggered_weaknesses'] = array(); }
                    $this->values['triggered_weaknesses'][] = array(
                        'id' => $this_ability->ability_id,
                        'token' => $this_ability->ability_token,
                        'type' => $this_ability->ability_type,
                        'type2' => $this_ability->ability_type2
                        );
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){
                        $this->field->field_multipliers['experience'] += 0.1;
                        $this_ability->recovery_options['recovery_kickback']['x'] = ceil($this_ability->recovery_options['recovery_kickback']['x'] * 2);
                    }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_affinity'])){
                    $this->flags['triggered_affinity'] = true;
                    if (isset($this->counters['triggered_affinity'])){ $this->counters['triggered_affinity'] += 1; }
                    else { $this->counters['triggered_affinity'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_resistance'])){
                    $this->flags['triggered_resistance'] = true;
                    if (isset($this->counters['triggered_resistance'])){ $this->counters['triggered_resistance'] += 1; }
                    else { $this->counters['triggered_resistance'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_critical'])){
                    $this->flags['triggered_critical'] = true;
                    if (isset($this->counters['triggered_critical'])){ $this->counters['triggered_critical'] += 1; }
                    else { $this->counters['triggered_critical'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){
                        $this->field->field_multipliers['experience'] += 0.1;
                        $this_ability->recovery_options['recovery_kickback']['x'] = ceil($this_ability->recovery_options['recovery_kickback']['x'] * 2);
                    }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }

            }

            // Update the field session with any changes
            $this->field->update_session();

            // Update this robot's frame based on recovery type
            $this->robot_frame = $this_ability->recovery_options['recovery_frame'];
            $this->player->set_frame((($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery']) ? 'taunt' : 'base'));
            $this_ability->ability_frame = $this_ability->recovery_options['ability_success_frame'];
            $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_success_frame_offset'];

            // Display the success text, if text has been provided
            if (!empty($this_ability->recovery_options['success_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['success_text'];
            }

            // Collect the recovery amount argument from the function
            $this_ability->ability_results['this_amount'] = $recovery_amount;

            // If we're not dealing with a percentage-based amount, apply stat mods
            if ($trigger_options['apply_stat_modifiers'] != false && !$this_ability->recovery_options['recovery_percent']){

                // Only apply ATTACK/DEFENSE mods if this robot is not targetting itself and it's ENERGY based recovery
                if ($this_ability->recovery_options['recovery_kind'] == 'energy' && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery'])){

                    // Backup the current ammount before stat multipliers
                    $temp_amount_backup = $this_ability->ability_results['this_amount'];

                    // If this robot's defense is at absolute zero, and the target's attack isnt, OHKO
                    if ($this->robot_defense <= 0 && $target_robot_attack_start >= 1){
                        // Set the new recovery amount to OHKO this robot
                        $temp_new_amount = $this->robot_base_energy;
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | '.$this->robot_token.'_defense_break | D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif the target robot's attack is at absolute zero, and the this's defense isnt, NOKO
                    elseif ($target_robot_attack_start <= 0 && $this->robot_defense >= 1){
                        // Set the new recovery amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break | A:'.$target_robot->robot_attack.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif this robot's defense is at absolute zero and the target's attack is too, NOKO
                    elseif ($this->robot_defense <= 0 && $target_robot_attack_start <= 0){
                        // Set the new recovery amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break and '.$this->robot_token.'_defense_break | A:'.$target_robot->robot_attack.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Otherwise if both robots have normal stats, calculate the new amount normally
                    else {

                        // Check to make sure starforce is enabled right now
                        $temp_starforce_enabled = true;
                        if (!empty($this->player->counters['dark_elements'])){ $temp_starforce_enabled = false; }
                        if (!empty($target_robot->player->counters['dark_elements'])){ $temp_starforce_enabled = false; }
                        if (empty($target_robot->player->player_starforce[$temp_ability_recovery_type]) && empty($target_robot->player->player_starforce[$temp_ability_recovery_type2])){ $temp_starforce_enabled = false; }

                        // Collect the target's attack stat and this robot's defense values
                        $target_robot_attack = $target_robot_attack_start;
                        $this_robot_defense = $this->robot_defense;

                        if (MMRPG_CONFIG_DEBUG_MODE && $temp_starforce_enabled){ $this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | attack_vs_defense | before_starforce |  A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' '); }

                        // If the target player has any starforce for type1, apply it to the attack
                        if ($temp_starforce_enabled && !empty($target_robot->player->player_starforce[$temp_ability_recovery_type])){
                            $temp_attack_boost = $target_robot->player->player_starforce[$temp_ability_recovery_type] * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                            $temp_new_attack = $target_robot_attack + $temp_attack_boost;
                            $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | target_starforce | '.$temp_ability_recovery_type.'_boost = '.$temp_attack_boost.' | robot_attack = round('.$target_robot_attack.' + '.$temp_attack_boost.') = '.$temp_new_attack.'');
                            $target_robot_attack = $temp_new_attack;
                            // If the target player has any starforce for type2, apply it to the attack
                            if (!empty($target_robot->player->player_starforce[$temp_ability_recovery_type2])){
                                $temp_attack_boost = $target_robot->player->player_starforce[$temp_ability_recovery_type2] * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                                $temp_new_attack = $target_robot_attack + $temp_attack_boost;
                                $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | target_starforce | '.$temp_ability_recovery_type2.'_boost = '.$temp_attack_boost.' | robot_attack = round('.$target_robot_attack.' + '.$temp_attack_boost.') = '.$temp_new_attack.'');
                                $target_robot_attack = $temp_new_attack;
                            }
                        }

                        // If this player has any starforce for type2, apply it to the defense
                        if ($temp_starforce_enabled && !empty($this->player->player_starforce[$temp_ability_recovery_type])){
                            $temp_defense_boost = $this->player->player_starforce[$temp_ability_recovery_type] * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                            $temp_new_defense = $this_robot_defense + $temp_defense_boost;
                            $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | this_starforce | '.$temp_ability_recovery_type.'_boost = '.$temp_defense_boost.' | robot_defense = round('.$this_robot_defense.' + '.$temp_defense_boost.') = '.$temp_new_defense.'');
                            $this_robot_defense = $temp_new_defense;
                            // If the target player has any starforce for type2, apply it to the defense
                            if (!empty($this->player->player_starforce[$temp_ability_recovery_type2])){
                                $temp_defense_boost = $this->player->player_starforce[$temp_ability_recovery_type2] * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                                $temp_new_defense = $this_robot_defense + $temp_defense_boost;
                                $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | this_starforce | '.$temp_ability_recovery_type2.'_boost = '.$temp_defense_boost.' | robot_defense = round('.$this_robot_defense.' + '.$temp_defense_boost.') = '.$temp_new_defense.'');
                                $this_robot_defense = $temp_new_defense;
                            }
                        }

                        if (MMRPG_CONFIG_DEBUG_MODE && $temp_starforce_enabled){ $this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | attack_vs_defense | after_starforce | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' '); }

                        // Set the new recovery amount relative to this robot's defense and the target robot's attack
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * ($target_robot_attack / $this_robot_defense));

                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | normal_recovery | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * ('.$target_robot_attack.' / '.$this_robot_defense.')) = '.$temp_new_amount.'');

                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;

                    }

                    // If this robot started out above zero but is now absolute zero, round up
                    if ($temp_amount_backup > 0 && $this_ability->ability_results['this_amount'] == 0){ $this_ability->ability_results['this_amount'] = 1; }

                }

                // If this is a critical hit (lucky, based on turn and level)
                $temp_flag_critical = $target_robot->robot_class != 'mecha' ? rpg_functions::critical_turn($this_battle->counters['battle_turn'], $target_robot->robot_level, $target_robot->robot_item) : false;
                if ($temp_flag_critical){
                    $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] * $this_ability->recovery_options['critical_multiplier'];
                    $this_ability->ability_results['flag_critical'] = true;
                    // DEBUG
                    $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_critical | x '.$this_ability->recovery_options['critical_multiplier'].' = '.$this_ability->ability_results['this_amount'].'');
                } else {
                    $this_ability->ability_results['flag_critical'] = false;
                }

            }

            // Only apply weakness, resistance, etc. if allowed to
            if ($trigger_options['apply_type_modifiers'] != false){

                // If this robot has an affinity to the ability (based on type)
                if ($this_ability->ability_results['flag_affinity']){
                    $loop_count = $this_ability->ability_results['counter_affinities'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['affinity_multiplier']);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_affinity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['affinity_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot resists the ability (based on type)
                if ($this_ability->ability_results['flag_resistance']){
                    $loop_count = $this_ability->ability_results['counter_resistances'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['resistance_multiplier']);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_resistance ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['resistance_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot is immune to the ability (based on type)
                if ($this_ability->ability_results['flag_immunity']){
                    $loop_count = $this_ability->ability_results['counter_immunities'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $this_ability->ability_results['this_amount'] = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['immunity_multiplier']);
                        // DEBUG
                        $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | flag_immunity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['immunity_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

            }

            // Only apply other modifiers if allowed to
            if ($trigger_options['apply_modifiers'] != false){

                // If this robot has an attachment with a recovery multiplier
                if (!empty($this->robot_attachments)){
                    foreach ($this->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery input breaker value set
                            if (isset($temp_info['attachment_recovery_input_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery input booster value set
                            if (isset($temp_info['attachment_recovery_input_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }

                // If this robot has an attachment with a recovery multiplier
                if (!empty($target_robot->robot_attachments)){
                    foreach ($target_robot->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery output breaker value set
                            if (isset($temp_info['attachment_recovery_output_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery output booster value set
                            if (isset($temp_info['attachment_recovery_output_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster']);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                $this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }


            }

            // Generate the flag string for easier parsing
            $this_flag_string = array();
            if ($this_ability->ability_results['flag_immunity']){ $this_flag_string[] = 'immunity'; }
            elseif ($trigger_options['apply_type_modifiers'] != false){
                if (!empty($this_ability->ability_results['flag_weakness'])){ $this_flag_string[] = 'weakness'; }
                if (!empty($this_ability->ability_results['flag_affinity'])){ $this_flag_string[] = 'affinity'; }
                if (!empty($this_ability->ability_results['flag_resistance'])){ $this_flag_string[] = 'resistance'; }
                if ($trigger_options['apply_modifiers'] != false && !$this_ability->recovery_options['recovery_percent']){
                    if (!empty($this_ability->ability_results['flag_critical'])){ $this_flag_string[] = 'critical'; }
                }
            }
            $this_flag_name = (!empty($this_flag_string) ? implode('_', $this_flag_string).'_' : '').'text';

            // Generate the status text based on flags
            if (isset($this_ability->recovery_options[$this_flag_name])){
                //$event_options['console_container_height'] = 2;
                //$this_ability->ability_results['this_text'] .= '<br />';
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->recovery_options[$this_flag_name];
            }

            // Display a break before the recovery amount if other text was generated
            if (!empty($this_ability->ability_results['this_text'])){
                $this_ability->ability_results['this_text'] .= '<br />';
            }

            // Ensure the recovery amount is always at least one, unless absolute zero
            if ($this_ability->ability_results['this_amount'] < 1 && $this_ability->ability_results['this_amount'] > 0){ $this_ability->ability_results['this_amount'] = 1; }

            // Reference the requested recovery kind with a shorter variable
            $this_ability->recovery_options['recovery_kind'] = strtolower($this_ability->recovery_options['recovery_kind']);
            $recovery_stat_name = 'robot_'.$this_ability->recovery_options['recovery_kind'];

            // Inflict the approiate recovery type based on the recovery options
            switch ($recovery_stat_name){

                // If this is an ATTACK type recovery trigger
                case 'robot_attack': {
                    // Inflict attack recovery on the target's internal stat
                    $this->robot_attack = $this->robot_attack + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's attack above 9999
                    if ($this->robot_attack > MMRPG_SETTINGS_STATS_MAX){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_attack) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots attack
                        $this->robot_attack = MMRPG_SETTINGS_STATS_MAX;
                    }
                    // Break from the ATTACK case
                    break;
                }
                // If this is an DEFENSE type recovery trigger
                case 'robot_defense': {
                    // Inflict defense recovery on the target's internal stat
                    $this->robot_defense = $this->robot_defense + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's defense above 9999
                    if ($this->robot_defense > MMRPG_SETTINGS_STATS_MAX){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_defense) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots defense
                        $this->robot_defense = MMRPG_SETTINGS_STATS_MAX;
                    }
                    // Break from the DEFENSE case
                    break;
                }
                // If this is an SPEED type recovery trigger
                case 'robot_speed': {
                    // Inflict speed recovery on the target's internal stat
                    $this->robot_speed = $this->robot_speed + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's speed above 9999
                    if ($this->robot_speed > MMRPG_SETTINGS_STATS_MAX){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_speed) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots speed
                        $this->robot_speed = MMRPG_SETTINGS_STATS_MAX;
                    }
                    // Break from the SPEED case
                    break;
                }
                // If this is a WEAPONS type recovery trigger
                case 'robot_weapons': {
                    // Inflict weapon recovery on the target's internal stat
                    $this->robot_weapons = $this->robot_weapons + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's weapons above the base
                    if ($this->robot_weapons > $this->robot_base_weapons){
                        // Calculate the overcure amount
                        $this_ability->ability_results['this_overkill'] = ($this->robot_base_weapons - $this->robot_weapons) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots weapons
                        $this->robot_weapons = $this->robot_base_weapons;
                    }
                    // Break from the WEAPONS case
                    break;
                }
                // If this is an ENERGY type recovery trigger
                case 'robot_energy': default: {
                    // Inflict the actual recovery on the robot
                    $this->robot_energy = $this->robot_energy + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot into overboost, recalculate the recovery
                    if ($this->robot_energy > $this->robot_base_energy){
                        // Calculate the overcure amount
                        $this_ability->ability_results['this_overboost'] = ($this->robot_base_energy - $this->robot_energy) * -1;
                        if ($this_ability->ability_results['this_overboost'] > $this->robot_base_energy){ $this_ability->ability_results['this_overboost'] = $this->robot_base_energy; }
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots energy
                        $this->robot_energy = $this->robot_base_energy;
                    }
                    // If the robot's energy has dropped to zero, disable them
                    if ($this->robot_energy == 0){
                        // Change the status to disabled
                        $this->robot_status = 'disabled';
                        // Remove any attachments this robot has
                        if (!empty($this->robot_attachments)){
                            foreach ($this->robot_attachments AS $token => $info){
                                if (empty($info['sticky'])){ unset($this->robot_attachments[$token]); }
                            }
                        }
                    }
                    // Break from the ENERGY case
                    break;
                }

            }

            // Define the print variables to return
            $this_ability->ability_results['print_strikes'] = '<span class="recovery_strikes">'.(!empty($this_ability->ability_results['total_strikes']) ? $this_ability->ability_results['total_strikes'] : 0).'</span>';
            $this_ability->ability_results['print_misses'] = '<span class="recovery_misses">'.(!empty($this_ability->ability_results['total_misses']) ? $this_ability->ability_results['total_misses'] : 0).'</span>';
            $this_ability->ability_results['print_result'] = '<span class="recovery_result">'.(!empty($this_ability->ability_results['total_result']) ? $this_ability->ability_results['total_result'] : 0).'</span>';
            $this_ability->ability_results['print_amount'] = '<span class="recovery_amount">'.(!empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0).'</span>';
            $this_ability->ability_results['print_overkill'] = '<span class="recovery_overkill">'.(!empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0).'</span>';

            // Add the final recovery text showing the amount based on life energy recovery
            if ($this_ability->recovery_options['recovery_kind'] == 'energy'){
                $this_ability->ability_results['this_text'] .= "{$this->print_name()} recovers {$this_ability->ability_results['print_amount']} life energy";
                //$this_ability->ability_results['this_text'] .= ($this_ability->ability_results['this_overkill'] > 0 ? " and {$this_ability->ability_results['print_overkill']} overkill" : '');
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise add the final recovery text showing the amount based on weapon energy recovery
            elseif ($this_ability->recovery_options['recovery_kind'] == 'weapons'){
                $this_ability->ability_results['this_text'] .= "{$this->print_name()} recovers {$this_ability->ability_results['print_amount']} weapon energy";
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise, if this is one of the robot's other internal stats
            elseif ($this_ability->recovery_options['recovery_kind'] == 'attack'
                || $this_ability->recovery_options['recovery_kind'] == 'defense'
                || $this_ability->recovery_options['recovery_kind'] == 'speed'){
                // Print the result based on if the stat will go any lower
                if ($this_ability->ability_results['this_amount'] > 0){
                    $this_ability->ability_results['this_text'] .= "{$this->print_name()}&#39;s {$this_ability->recovery_options['recovery_kind']} rose by {$this_ability->ability_results['print_amount']}";
                    $this_ability->ability_results['this_text'] .= '!<br />';
                }
                // Otherwise if the stat wouldn't go any lower
                else {

                    // Update this robot's frame based on recovery type
                    $this_ability->ability_frame = $this_ability->recovery_options['ability_failure_frame'];
                    $this_ability->ability_frame_span = $this_ability->recovery_options['ability_failure_frame_span'];
                    $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_failure_frame_offset'];

                    // Display the failure text, if text has been provided
                    if (!empty($this_ability->recovery_options['failure_text'])){
                        $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['failure_text'].' ';
                    }
                }
            }

        }
        // Otherwise, if the attack was a failure
        else {

            // Update this robot's frame based on recovery type
            $this_ability->ability_frame = $this_ability->recovery_options['ability_failure_frame'];
            $this_ability->ability_frame_span = $this_ability->recovery_options['ability_failure_frame_span'];
            $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_failure_frame_offset'];

            // Update the recovery and overkilll amounts to reflect zero recovery
            $this_ability->ability_results['this_amount'] = 0;
            $this_ability->ability_results['this_overkill'] = 0;

            // Display the failure text, if text has been provided
            if (!$this_ability->ability_results['flag_immunity'] && !empty($this_ability->recovery_options['failure_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['failure_text'].' ';
            }

        }

        // Update this robot's history with the triggered recovery amount
        $this->history['triggered_recovery'][] = $this_ability->ability_results['this_amount'];
        // Update the robot's history with the triggered recovery types
        if (!empty($this_ability->ability_results['recovery_type'])){
            $temp_types = array();
            $temp_types[] = $this_ability->ability_results['recovery_type'];
            if (!empty($this_ability->ability_results['recovery_type2'])){ $temp_types[] = $this_ability->ability_results['recovery_type2']; }
            $this->history['triggered_recovery_types'][] = $temp_types;
        } else {
            $this->history['triggered_recovery_types'][] = array();
        }
        // Update this robot's history with the overboost if applicable
        if (!empty($this_ability->ability_results['this_overboost'])){
            $this->counters['assist_overboost'] = isset($this->counters['assist_overboost']) ? $this->counters['assist_overboost'] + $this_ability->ability_results['this_overboost'] : $this_ability->ability_results['this_overboost'];
        }

        // Update the recovery result total variables
        $this_ability->ability_results['total_amount'] += !empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0;
        $this_ability->ability_results['total_overkill'] += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
        if ($this_ability->ability_results['this_result'] == 'success'){ $this_ability->ability_results['total_strikes']++; }
        else { $this_ability->ability_results['total_misses']++; }
        $this_ability->ability_results['total_actions'] = $this_ability->ability_results['total_strikes'] + $this_ability->ability_results['total_misses'];
        if ($this_ability->ability_results['total_result'] != 'success'){ $this_ability->ability_results['total_result'] = $this_ability->ability_results['this_result']; }
        $event_options['this_ability_results'] = $this_ability->ability_results;

        // Update internal variables
        $target_robot->update_session();
        $target_robot->player->update_session();
        $this->update_session();
        $this->player->update_session();

        // If this robot was at full energy but is now at zero, it's a OHKO
        $this_robot_energy_ohko = false;
        if ($this->robot_energy <= 0 && $this_robot_energy_start_max){
            // DEBUG
            $this_battle->events_debug(__FILE__, __LINE__, $this_ability->ability_token.' | damage_result_OHKO! | Start:'.$this_robot_energy_start.' '.($this_robot_energy_start_max ? '(MAX!)' : '-').' | Finish:'.$this->robot_energy);
            // Ensure the attacking player was a human
            if ($this->player->player_side == 'right'){
                $this_robot_energy_ohko = true;
                // Increment the field multipliers for items
                //if (!isset($this->field->field_multipliers['items'])){ $this->field->field_multipliers['items'] = 1; }
                //$this->field->field_multipliers['items'] += 0.1;
                //$this->field->update_session();
            }
        }

        // Generate an event with the collected recovery results based on recovery type
        if ($this->robot_id == $target_robot->robot_id){ //$this_ability->recovery_options['recovery_kind'] == 'energy'
            $event_options['console_show_target'] = false;
            $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
            $this_battle->events_create($target_robot, $this, $this_ability->recovery_options['recovery_header'], $this_ability->ability_results['this_text'], $event_options);
        } else {
            $event_options['console_show_target'] = false;
            $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;;
            $this_battle->events_create($this, $target_robot, $this_ability->recovery_options['recovery_header'], $this_ability->ability_results['this_text'], $event_options);
        }

        // If this robot was an unlockable and hit with an element, remove it
        $temp_robot_rewards = $this_battle->get_robot_rewards();
        if ($this_ability->ability_class != 'item'
            && $this->player->player_id != $target_robot->player->player_id
            && !empty($temp_robot_rewards)
            && (!empty($this_ability->ability_type) || !empty($this_ability->ability_type2))){
            foreach ($temp_robot_rewards AS $key => $reward){
                if ($reward['token'] == $this->robot_token){
                    $event_options['console_show_target'] = false;
                    $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
                    $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
                    $this_battle->events_create($this, $target_robot,
                        'Master Core Data Damaged',
                        $this->print_name().'\'s core was corrupted by the repair!<br /> '.
                        'The robot can\'t be unlocked anymore!',
                        $event_options);
                    unset($temp_robot_rewards[$key]);
                    $temp_robot_rewards = array_values($temp_robot_rewards);
                    $this_battle->set_robot_rewards($temp_robot_rewards);
                    break;
                }
            }
        }

        // Restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->set_frame($this_player_backup_frame);
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->set_frame($target_player_backup_frame);
        $this_ability->ability_frame = $this_ability_backup_frame;

        // Update internal variables
        $target_robot->update_session();
        $this->update_session();
        $this_ability->update_session();

        // If this robot has been disabled, add a defeat attachment
        if ($this->robot_status == 'disabled'){
            // Define this ability's attachment token
            $temp_frames = array(0,4,1,5,2,6,3,7,4,8,5,9,0,1,2,3,4,5,6,7,8,9);
            shuffle($temp_frames);
            $this_attachment_token = 'ability_attachment-defeat';
            $this_attachment_info = array(
                'class' => 'ability',
                'ability_token' => 'attachment-defeat',
                'attachment_flag_defeat' => true,
                'ability_frame' => 0,
                'ability_frame_animate' => $temp_frames,
                'ability_frame_offset' => array('x' => 0, 'y' => -10, 'z' => -10)
                );

            // If the attachment doesn't already exists, add it to the robot
            if (!isset($this->robot_attachments[$this_attachment_token])){
                $this->robot_attachments[$this_attachment_token] =  $this_attachment_info;
                $this->update_session();
            }

        }

        // If this robot was disabled, process experience for the target
        if ($this->robot_status == 'disabled' && $trigger_disabled){
            $trigger_options = array();
            if ($this_robot_energy_ohko){ $trigger_options['item_multiplier'] = 2.0; }
            $this->trigger_disabled($target_robot, $this_ability, $trigger_options);
        }
        // Otherwise, if the target robot was not disabled
        elseif ($this->robot_status != 'disabled'){
            // -- CHECK ATTACHMENTS -- //

            // Ensure the ability was a success before checking attachments
            if ($this_ability->ability_results['this_result'] == 'success'){
                // If this robot has any attachments, loop through them
                if (!empty($this->robot_attachments)){
                    $this_battle->events_debug(__FILE__, __LINE__, $this->robot_token.' | has_attachments | '.implode(', ', array_keys($this->robot_attachments)));
                    foreach ($this->robot_attachments AS $attachment_token => $attachment_info){
                        // Ensure this ability has a type before checking weaknesses, resistances, etc.
                        if (!empty($this_ability->ability_type)){
                            // If this attachment has weaknesses defined and this ability is a match
                            if (!empty($attachment_info['attachment_weaknesses'])
                                && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses']) || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))){
                                $this_battle->events_debug(__FILE__, __LINE__, 'checkpoint weaknesses');
                                // Remove this attachment and inflict damage on the robot
                                unset($this->robot_attachments[$attachment_token]);
                                $this->update_session();
                                if ($attachment_info['attachment_destroy'] !== false){
                                    $temp_info = array('ability_id' => (MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX + $attachment_key + 100), 'ability_token' => $attachment_info['ability_token']);
                                    $temp_attachment = new rpg_ability($this->player, $this, $temp_info);
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
                                        $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                                    }
                                }
                                // If this robot was disabled, process experience for the target
                                if ($this->robot_status == 'disabled'){ break; }
                            }

                        }

                    }
                }

            }

        }

        // Return the final recovery results
        return $this_ability->ability_results;

    }

    // Define a trigger for processing disabled events
    public function trigger_disabled($target_robot, $this_ability, $trigger_options = array()){

        // Pull in the global variable
        global $mmrpg_index;

        // Import global variables
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // Generate default trigger options if not set
        if (!isset($trigger_options['item_multiplier'])){ $trigger_options['item_multiplier'] = 1.0; }


        // If the battle has already ended, return false
        if (!empty($this_battle->flags['battle_complete_message_created'])){ return false; }

        // Create references to save time 'cause I'm tired
        // (rather than replace all target references to this references)
        $this_battle = &$this_battle;
        $this_player = &$this->player; // the player of the robot being disabled
        $this_robot = &$this; // the robot being disabled
        $target_player = &$target_robot->player; // the player of the other robot
        $target_robot = &$target_robot; // the other robot that isn't this one

        // If the target player is the same as the current or the target is dead
        if ($this_player->player_id == $target_player->player_id){
            // Collect the actual target player from the battle values
            if (!empty($this_battle->values['players'])){
                foreach ($this_battle->values['players'] AS $id => $info){
                    if ($this_player->player_id != $id){
                        unset($target_player);
                        $target_player = new rpg_player($info);
                    }
                }
            }
            // Collect the actual target robot from the battle values
            if (!empty($target_player->values['robots_active'])){
                foreach ($target_player->values['robots_active'] AS $key => $info){
                    if ($info['robot_position'] == 'active'){
                        $target_robot->robot_load($info);
                    }
                }
            }
        }

        // Update the target player's session
        $this_player->update_session();

        // Create the robot disabled event
        $disabled_text = in_array($this_robot->robot_token, array('dark-frag', 'dark-spire', 'dark-tower')) || $this_robot->robot_core == 'empty' ? 'destroyed' : 'disabled';
        $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
        $event_body = ($this_player->player_token != 'player' ? $this_player->print_name().'&#39;s ' : 'The target ').' '.$this_robot->print_name().' was '.$disabled_text.'!<br />'; //'.($this_robot->robot_position == 'bench' ? ' and removed from battle' : '').'
        if (isset($this_robot->robot_quotes['battle_defeat'])){
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_robot->print_quote('battle_defeat', $this_find, $this_replace);
        }
        if ($target_robot->robot_status != 'disabled'){ $target_robot->robot_frame = 'base'; }
        $this_robot->robot_frame = 'defeat';
        $target_robot->update_session();
        $this_robot->update_session();
        $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, array('console_show_target' => false, 'canvas_show_disabled_bench' => $this_robot->robot_id.'_'.$this_robot->robot_token));


        /*
         * EFFORT VALUES / STAT BOOST BONUSES
         */

        // Define the event options array
        $event_options = array();
        $event_options['this_ability_results']['total_actions'] = 0;

        // Calculate the bonus boosts from defeating the target robot (if NOT player battle)
        if ($target_player->player_side == 'left' && $this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && $target_robot->robot_status != 'disabled'){


            // Boost this robot's attack if a boost is in order
            if (empty($target_robot->flags['robot_stat_max_attack'])){
                $this_attack_boost = $this_robot->robot_base_attack / 100; //ceil($this_robot->robot_base_attack / 100);
                if ($this_robot->robot_class == 'mecha'){ $this_attack_boost = $this_attack_boost / 2; }
                if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_attack_boost = $this_attack_boost * 2; }
                if ($target_robot->robot_attack + $this_attack_boost > MMRPG_SETTINGS_STATS_MAX){
                    $this_attack_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_attack) * -1;
                    $this_attack_boost = $this_attack_boost - $this_attack_overboost;
                }
                $this_attack_boost = round($this_attack_boost);
            } else {
                $this_attack_boost = 0;
            }

            // Boost this robot's defense if a boost is in order
            if (empty($target_robot->flags['robot_stat_max_defense'])){
                $this_defense_boost = $this_robot->robot_base_defense / 100; //ceil($this_robot->robot_base_defense / 100);
                if ($this_robot->robot_class == 'mecha'){ $this_defense_boost = $this_defense_boost / 2; }
                if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_defense_boost = $this_defense_boost * 2; }
                if ($target_robot->robot_defense + $this_defense_boost > MMRPG_SETTINGS_STATS_MAX){
                    $this_defense_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_defense) * -1;
                    $this_defense_boost = $this_defense_boost - $this_defense_overboost;
                }
                $this_defense_boost = round($this_defense_boost);
            } else {
                $this_defense_boost = 0;
            }

            // Boost this robot's speed if a boost is in order
            if (empty($target_robot->flags['robot_stat_max_speed'])){
                $this_speed_boost = $this_robot->robot_base_speed / 100; //ceil($this_robot->robot_base_speed / 100);
                if ($this_robot->robot_class == 'mecha'){ $this_speed_boost = $this_speed_boost / 2; }
                if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_speed_boost = $this_speed_boost * 2; }
                if ($target_robot->robot_speed + $this_speed_boost > MMRPG_SETTINGS_STATS_MAX){
                    $this_speed_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_speed) * -1;
                    $this_speed_boost = $this_speed_boost - $this_speed_overboost;
                }
                $this_speed_boost = round($this_speed_boost);
            } else {
                $this_speed_boost = 0;
            }

            // If the target robot is holding a Growth Module, double the stat bonuses
            if ($target_robot->robot_item == 'item-growth-module'){
                if (!$this_attack_boost){ $this_attack_boost = $this_attack_boost * 2; }
                if (!$this_defense_boost){ $this_defense_boost = $this_defense_boost * 2; }
                if (!$this_speed_boost){ $this_speed_boost = $this_speed_boost * 2; }
            }

            // Define the temporary boost actions counter
            $temp_boost_actions = 1;

            // Increase reward if there are any pending stat boosts and clear session
            if ($target_player->player_side == 'left' && ($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') && $target_robot->robot_base_attack < MMRPG_SETTINGS_STATS_MAX){
                if (!empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'])){
                    $this_attack_boost += $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'];
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'] = 0;
                }
            }

            // Increase reward if there are any pending stat boosts and clear session
            if ($target_player->player_side == 'left' && ($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') && $target_robot->robot_base_defense < MMRPG_SETTINGS_STATS_MAX){
                if (!empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'])){
                    $this_defense_boost += $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'];
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'] = 0;
                }
            }

            // Increase reward if there are any pending stat boosts and clear session
            if ($target_player->player_side == 'left' && ($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') && $target_robot->robot_base_speed < MMRPG_SETTINGS_STATS_MAX){
                if (!empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'])){
                    $this_speed_boost += $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'];
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'] = 0;
                }
            }

            // If the attack boost was not empty, process it
            if ($this_attack_boost > 0){

                // If the robot is under level 100, stat boosts are pending
                if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){

                    // Update the session variables with the pending stat boost
                    if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'] = 0; }
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'] += $this_attack_boost;

                }
                // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
                elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->robot_base_attack < MMRPG_SETTINGS_STATS_MAX){

                    // Define the base attack boost based on robot base stats
                    $temp_attack_boost = ceil($this_attack_boost);

                    // If this action would boost the robot over their stat limits
                    if ($temp_attack_boost + $target_robot->robot_attack > MMRPG_SETTINGS_STATS_MAX){
                        $temp_attack_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_attack;
                    }

                    // Increment this robot's attack by the calculated amount and display an event
                    $target_robot->robot_attack = ceil($target_robot->robot_attack + $temp_attack_boost);
                    $target_robot->robot_base_attack = ceil($target_robot->robot_base_attack + $temp_attack_boost);
                    $event_options = array();
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_kind'] = 'attack';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['flag_affinity'] = true;
                    $event_options['this_ability_results']['flag_critical'] = true;
                    $event_options['this_ability_results']['this_amount'] = $temp_attack_boost;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
                    $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
                    $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
                    $event_options['console_show_target'] = false;
                    $event_body = $target_robot->print_name().' downloads weapons data from the target robot! ';
                    $event_body .= '<br />';
                    $event_body .= $target_robot->print_name().'&#39;s attack grew by <span class="recovery_amount">'.$temp_attack_boost.'</span>! ';
                    $target_robot->robot_frame = 'shoot';
                    $target_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

                    // Update the session variables with the rewarded stat boost if not mecha
                    if ($target_robot->robot_class == 'master'){
                        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'] = 0; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack']);
                        $temp_attack_session_boost = round($this_attack_boost);
                        if ($temp_attack_session_boost < 1){ $temp_attack_session_boost = 1; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'] += $temp_attack_session_boost;
                    }


                }

            }

            // If the defense boost was not empty, process it
            if ($this_defense_boost > 0){
                // If the robot is under level 100, stat boosts are pending
                if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){
                    // Update the session variables with the pending stat boost
                    if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'] = 0; }
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'] += $this_defense_boost;

                }
                // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
                elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->robot_base_defense < MMRPG_SETTINGS_STATS_MAX){

                    // Define the base defense boost based on robot base stats
                    $temp_defense_boost = ceil($this_defense_boost);

                    // If this action would boost the robot over their stat limits
                    if ($temp_defense_boost + $target_robot->robot_defense > MMRPG_SETTINGS_STATS_MAX){
                        $temp_defense_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_defense;
                    }

                    // Increment this robot's defense by the calculated amount and display an event
                    $target_robot->robot_defense = ceil($target_robot->robot_defense + $temp_defense_boost);
                    $target_robot->robot_base_defense = ceil($target_robot->robot_base_defense + $temp_defense_boost);
                    $event_options = array();
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_kind'] = 'defense';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['flag_affinity'] = true;
                    $event_options['this_ability_results']['flag_critical'] = true;
                    $event_options['this_ability_results']['this_amount'] = $temp_defense_boost;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
                    $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
                    $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
                    $event_options['console_show_target'] = false;
                    $event_body = $target_robot->print_name().' downloads shield data from the target robot! ';
                    $event_body .= '<br />';
                    $event_body .= $target_robot->print_name().'&#39;s defense grew by <span class="recovery_amount">'.$temp_defense_boost.'</span>! ';
                    $target_robot->robot_frame = 'defend';
                    $target_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

                    // Update the session variables with the rewarded stat boost if not mecha
                    if ($target_robot->robot_class == 'master'){
                        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'] = 0; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense']);
                        $temp_defense_session_boost = round($this_defense_boost);
                        if ($temp_defense_session_boost < 1){ $temp_defense_session_boost = 1; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'] += $temp_defense_session_boost;
                    }

                }

            }

            // If the speed boost was not empty, process it
            if ($this_speed_boost > 0){
                // If the robot is under level 100, stat boosts are pending
                if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){
                    // Update the session variables with the pending stat boost
                    if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'] = 0; }
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'] += $this_speed_boost;

                }
                // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
                elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->robot_base_speed < MMRPG_SETTINGS_STATS_MAX){

                    // Define the base speed boost based on robot base stats
                    $temp_speed_boost = ceil($this_speed_boost);

                    // If this action would boost the robot over their stat limits
                    if ($temp_speed_boost + $target_robot->robot_speed > MMRPG_SETTINGS_STATS_MAX){
                        $temp_speed_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_speed;
                    }

                    // Increment this robot's speed by the calculated amount and display an event
                    $target_robot->robot_speed = ceil($target_robot->robot_speed + $temp_speed_boost);
                    $target_robot->robot_base_speed = ceil($target_robot->robot_base_speed + $temp_speed_boost);
                    $event_options = array();
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_kind'] = 'speed';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['flag_affinity'] = true;
                    $event_options['this_ability_results']['flag_critical'] = true;
                    $event_options['this_ability_results']['this_amount'] = $temp_speed_boost;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
                    $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
                    $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
                    $event_options['console_show_target'] = false;
                    $event_body = $target_robot->print_name().' downloads mobility data from the target robot! ';
                    $event_body .= '<br />';
                    $event_body .= $target_robot->print_name().'&#39;s speed grew by <span class="recovery_amount">'.$temp_speed_boost.'</span>! ';
                    $target_robot->robot_frame = 'slide';
                    $target_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

                    // Update the session variables with the rewarded stat boost if not mecha
                    if ($target_robot->robot_class == 'master'){
                        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'] = 0; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed']);
                        $temp_speed_session_boost = round($this_speed_boost);
                        if ($temp_speed_session_boost < 1){ $temp_speed_session_boost = 1; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'] += $temp_speed_session_boost;
                    }

                }

            }

            // Update the target robot frame
            $target_robot->robot_frame = 'base';
            $target_robot->update_session();

        }

        // Ensure player and robot variables are updated
        $target_robot->update_session();
        $target_player->update_session();
        $this_robot->update_session();
        $this_player->update_session();

        /*
        // DEBUG
        $this_battle->events_create(false, false, 'DEBUG', 'we made it past the stat boosts... <br />'.
            '$this_robot->robot_token='.$this_robot->robot_token.'; $target_robot->robot_token='.$target_robot->robot_token.';<br />'.
            '$target_player->player_token='.$target_player->player_token.'; $target_player->player_side='.$target_player->player_side.';<br />'
            );
        */

        /*
         * ITEM REWARDS / EXPERIENCE POINTS / LEVEL UP
         * Reward the player and robots with items and experience if not in demo mode
         */

        if ($target_player->player_side == 'left' && $this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && rpg_game::is_user()){
            // -- EXPERIENCE POINTS / LEVEL UP -- //

            // Filter out robots who were active in this battle in at least some way
            $temp_robots_active = $target_player->values['robots_active'];
            usort($temp_robots_active, array('rpg_functions','robot_sort_by_active'));


            // Define the boost multiplier and start out at zero
            $temp_boost_multiplier = 0;

            // DEBUG
            //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $this_robot->counters = <pre>'.print_r($this_robot->counters, true).'</pre>');
            //$this_battle->events_create(false, false, 'DEBUG', $event_body);

            // If the target has had any damage flags triggered, update the multiplier
            //if ($this_robot->flags['triggered_immunity']){ $temp_boost_multiplier += 0; }
            //if (!empty($this_robot->flags['triggered_resistance'])){ $temp_boost_multiplier -= $this_robot->counters['triggered_resistance'] * 0.10; }
            //if (!empty($this_robot->flags['triggered_affinity'])){ $temp_boost_multiplier -= $this_robot->counters['triggered_affinity'] * 0.10; }
            //if (!empty($this_robot->flags['triggered_weakness'])){ $temp_boost_multiplier += $this_robot->counters['triggered_weakness'] * 0.10; }
            //if (!empty($this_robot->flags['triggered_critical'])){ $temp_boost_multiplier += $this_robot->counters['triggered_critical'] * 0.10; }

            // If we're in DEMO mode, give a 100% experience boost
            //if (rpg_game::is_demo()){ $temp_boost_multiplier += 1; }

            // Ensure the multiplier has not gone below 100%
            if ($temp_boost_multiplier < -0.99){ $temp_boost_multiplier = -0.99; }
            elseif ($temp_boost_multiplier > 0.99){ $temp_boost_multiplier = 0.99; }

            // Define the boost text to match the multiplier
            $temp_boost_text = '';
            if ($temp_boost_multiplier < 0){ $temp_boost_text = 'a lowered '; }
            elseif ($temp_boost_multiplier > 0){ $temp_boost_text = 'a boosted '; }

            /*
            $event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.'<pre>'.print_r($this_robot->flags, true).'</pre>');
            //$this_battle->events_create(false, false, 'DEBUG', $event_body);

            $event_body = preg_replace('/\s+/', ' ', $target_robot->robot_token.'<pre>'.print_r($target_robot->flags, true).'</pre>');
            //$this_battle->events_create(false, false, 'DEBUG', $event_body);
            */


            // Define the base experience for the target robot
            $temp_experience = $this_robot->robot_base_energy + $this_robot->robot_base_attack + $this_robot->robot_base_defense + $this_robot->robot_base_speed;

            // DEBUG
            //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_boost_multiplier = '.$temp_boost_multiplier.'; $temp_experience = '.$temp_experience.'; ');
            //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $event_body);

            // Apply any boost multipliers to the experience earned
            if ($temp_boost_multiplier > 0 || $temp_boost_multiplier < 0){ $temp_experience += $temp_experience * $temp_boost_multiplier; }
            if ($temp_experience <= 0){ $temp_experience = 1; }
            $temp_experience = round($temp_experience);
            $temp_target_experience = array('level' => $this_robot->robot_level, 'experience' => $temp_experience);

            // DEBUG
            //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_target_experience = <pre>'.print_r($temp_target_experience, true).'</pre>');
            //$this_battle->events_create(false, false, 'DEBUG', $event_body);

            // Define the robot experience level and start at zero
            $target_robot_experience = 0;

            // Sort the active robots based on active or not
            /*
            function mmrpg_sort_temp_active_robots($info1, $info2){
                if ($info1['robot_position'] == 'active'){ return -1; }
                else { return 1; }
            }
            usort($temp_robots_active, 'mmrpg_sort_temp_active_robots');
            */

            // If the target was defeated with overkill, add it to the battle var
            if (!empty($this_robot->counters['defeat_overkill'])){
                $overkill_bonus = $this_robot->counters['defeat_overkill'];
                //$overkill_bonus = $overkill_bonus - ceil($overkill_bonus * 0.90);
                //$overkill_divider = $target_robot->robot_level >= 100 ? 0.01 : (100 - $target_robot->robot_level) / 100;
                //$overkill_bonus = floor($overkill_bonus * $overkill_divider);
                //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$this_battle->battle_overkill' => $this_battle->battle_overkill, '$this_battle->battle_rewards_zenny' => $this_battle->battle_rewards_zenny), true)).'</pre>', $event_options);
                //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$overkill_bonus' => $overkill_bonus), true)).'</pre>', $event_options);
                //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$this_robot->robot_base_total' => $this_robot->robot_base_total, '$target_robot->robot_base_total' => $target_robot->robot_base_total), true)).'</pre>', $event_options);
                //if ($target_robot->robot_base_total > $this_robot->robot_base_total){ $overkill_bonus = floor($overkill_bonus * ($this_robot->robot_base_total / $target_robot->robot_base_total));   }
                //elseif ($target_robot->robot_base_total < $this_robot->robot_base_total){ $overkill_bonus = floor($overkill_bonus * ($target_robot->robot_base_total / $this_robot->robot_base_total));   }
                //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$overkill_bonus' => $overkill_bonus), true)).'</pre>', $event_options);
                $this_battle->battle_overkill += $this_robot->counters['defeat_overkill'];
                if (empty($this_battle->flags['starter_battle'])){ $this_battle->battle_rewards_zenny += $overkill_bonus; }
                $this_battle->update_session();
                //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$this_battle->battle_overkill' => $this_battle->battle_overkill, '$this_battle->battle_rewards_zenny' => $this_battle->battle_rewards_zenny), true)).'</pre>', $event_options);
            }

            // Increment each of this player's robots
            $temp_robots_active_num = count($temp_robots_active);
            $temp_robots_active_num2 = $temp_robots_active_num; // This will be decremented for each non-experience gaining level 100 robots
            $temp_robots_active = array_reverse($temp_robots_active, true);
            usort($temp_robots_active, array('rpg_functions', 'robot_sort_by_active'));
            $temp_robot_active_position = false;
            foreach ($temp_robots_active AS $temp_id => $temp_info){
                $temp_robot = $target_robot->robot_id == $temp_info['robot_id'] ? $target_robot : new rpg_robot($target_player, $temp_info);
                if ($temp_robot->robot_level >= 100 || $temp_robot->robot_class != 'master'){ $temp_robots_active_num2--; }
                if ($temp_robot->robot_position == 'active'){
                    $temp_robot_active_position = $temp_robots_active[$temp_id];
                    unset($temp_robots_active[$temp_id]);
                }
            }
            $temp_unshift = array_unshift($temp_robots_active, $temp_robot_active_position);

            foreach ($temp_robots_active AS $temp_id => $temp_info){
                // Collect or define the robot points and robot rewards variables
                $temp_robot = $target_robot->robot_id == $temp_info['robot_id'] ? $target_robot : new rpg_robot($target_player, $temp_info);
                //if ($temp_robot->robot_class == 'mecha'){ continue; }
                $temp_robot_token = $temp_info['robot_token'];
                if ($temp_robot_token == 'robot'){ continue; }
                $temp_robot_experience = rpg_game::robot_experience($target_player->player_token, $temp_info['robot_token']);
                $temp_robot_rewards = !empty($temp_info['robot_rewards']) ? $temp_info['robot_rewards'] : array();
                if (empty($temp_robots_active_num2)){ break; }

                // Continue if over already at level 100
                //if ($temp_robot->robot_level >= 100){ continue; }

                // Reset the robot experience points to zero
                $target_robot_experience = 0;

                // Continue with experience mods only if under level 100
                if ($temp_robot->robot_level < 100 && $temp_robot->robot_class == 'master'){
                    // Give a proportionate amount of experience based on this and the target robot's levels
                    if ($temp_robot->robot_level == $temp_target_experience['level']){
                        $temp_experience_boost = $temp_target_experience['experience'];
                    } elseif ($temp_robot->robot_level < $temp_target_experience['level']){
                        $temp_experience_boost = $temp_target_experience['experience'] + round((($temp_target_experience['level'] - $temp_robot->robot_level) / 100)  * $temp_target_experience['experience']);
                        //$temp_experience_boost = $temp_target_experience['experience'] + ((($temp_target_experience['level']) / $temp_robot->robot_level) * $temp_target_experience['experience']);
                    } elseif ($temp_robot->robot_level > $temp_target_experience['level']){
                        $temp_experience_boost = $temp_target_experience['experience'] - round((($temp_robot->robot_level - $temp_target_experience['level']) / 100)  * $temp_target_experience['experience']);
                        //$temp_experience_boost = $temp_target_experience['experience'] - ((($temp_robot->robot_level - $temp_target_experience['level']) / 100) * $temp_target_experience['experience']);
                    }

                    // DEBUG
                    //$event_body = 'START EXPERIENCE | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    //$temp_experience_boost = ceil($temp_experience_boost / 10);
                    $temp_experience_boost = ceil($temp_experience_boost / $temp_robots_active_num);
                    //$temp_experience_boost = ceil($temp_experience_boost / ($temp_robots_active_num * 2));
                    //$temp_experience_boost = ceil($temp_experience_boost / ($temp_robots_active_num2 * 2));
                    //$temp_experience_boost = ceil(($temp_experience_boost / $temp_robots_active_num2) * 1.00);

                    if ($temp_experience_boost > MMRPG_SETTINGS_STATS_MAX){ $temp_experience_boost = MMRPG_SETTINGS_STATS_MAX; }
                    $target_robot_experience += $temp_experience_boost;

                    // DEBUG
                    //$event_body = 'ACTIVE ROBOT DIVISION | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robots_active_num = '.$temp_robots_active_num.'; $temp_robots_active_num2 = '.$temp_robots_active_num2.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If this robot has been traded, give it an additional experience boost
                    $temp_experience_boost = 0;
                    $temp_robot_boost_text = $temp_boost_text;
                    $temp_player_boosted = false;
                    if ($temp_robot->player_token != $temp_robot->robot_original_player){
                        $temp_player_boosted = true;
                        $temp_robot_boost_text = 'a player boosted ';
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience = $target_robot_experience * 2;
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                        // DEBUG
                        //$event_body = 'PLAYER BOOSTED | ';
                        //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robot->player_token('.$temp_robot->player_token.') != $temp_robot->robot_original_player('.$temp_robot->robot_original_player.'); ');
                        //$this_battle->events_create(false, false, 'DEBUG', $event_body);
                    }

                    // If the target robot is holding a Growth Module, double the experience bonus
                    if ($temp_robot->robot_item == 'item-growth-module'){
                        $temp_robot_boost_text = $temp_player_boosted ? 'a player and module boosted ' : 'a module boosted ';
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience = $target_robot_experience * 2;
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                        // DEBUG
                        //$event_body = 'MODULE BOOSTED | ';
                        //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robot->robot_item = '.$temp_robot->robot_item.'; ');
                        //$this_battle->events_create(false, false, 'DEBUG', $event_body);
                    }

                    // If there are field multipliers in place, apply them now
                    $temp_experience_boost = 0;
                    if (isset($this->field->field_multipliers['experience'])){
                        //$temp_robot_boost_text = '(and '.$target_robot_experience.' multiplied by '.number_format($this->field->field_multipliers['experience'], 1).') ';
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience = ceil($target_robot_experience * $this->field->field_multipliers['experience']);
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                    }

                    // DEBUG
                    //$event_body = 'FIELD MULTIPLIERS | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    /*
                    // If this robot has any overkill, add that to the temp experience modifier
                    $temp_experience_boost = 0;
                    if (!empty($this_robot->counters['defeat_overkill'])){
                        if (empty($temp_robot_boost_text)){ $temp_robot_boost_text = 'an overkill boosted '; }
                        else { $temp_robot_boost_text = 'a player and overkill boosted '; }
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience += ceil($this_robot->counters['defeat_overkill'] / $temp_robots_active_num2);
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                        //$this_battle->battle_overkill += $this_robot->counters['defeat_overkill'];
                        //$this_battle->update_session();
                        //$temp_robot_boost_text .= 'umm '.$this_battle->battle_overkill;
                    }
                    */

                    // DEBUG
                    //$event_body = 'OVERKILL BONUS | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    /*
                    // If the target robot's core type has been boosted by starforce
                    if (!empty($temp_robot->robot_core) && !empty($_SESSION['GAME']['values']['star_force'][$temp_robot->robot_core])){
                        if (empty($temp_robot_boost_text)){ $temp_robot_boost_text = 'a starforce boosted '; }
                        elseif ($temp_robot_boost_text == 'an overkill boosted '){ $temp_robot_boost_text = 'an overkill and starforce boosted '; }
                        elseif ($temp_robot_boost_text == 'a player boosted '){ $temp_robot_boost_text = 'a player and starforce boosted '; }
                        else { $temp_robot_boost_text = 'a player, overkill, and starforce boosted '; }
                        $temp_starforce = $_SESSION['GAME']['values']['star_force'][$temp_robot->robot_core];
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience += ceil($target_robot_experience * ($temp_starforce / 10));
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                    }
                    */

                    // DEBUG
                    //$event_body = 'STARFORCE BONUS | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $temp_robot->robot_token.' : '.$temp_robot->robot_core.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If the experience is greater then the max, level it off at the max (sorry guys!)
                    if ($target_robot_experience > MMRPG_SETTINGS_STATS_MAX){ $target_robot_experience = MMRPG_SETTINGS_STATS_MAX; }
                    if ($target_robot_experience < MMRPG_SETTINGS_STATS_MIN){ $target_robot_experience = MMRPG_SETTINGS_STATS_MIN; }

                    // Collect the robot's current experience and level for reference later
                    $temp_start_experience = rpg_game::robot_experience($target_player->player_token, $temp_robot_token);
                    $temp_start_level = rpg_game::robot_level($target_player->player_token, $temp_robot_token);

                    // Increment this robots's points total with the battle points
                    if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] = 1; }
                    if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] = 0; }
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] += $target_robot_experience;

                    // Define the new experience for this robot
                    $temp_required_experience = rpg_prototype::calculate_experience_required($temp_robot->robot_level);
                    $temp_new_experience = rpg_game::robot_experience($target_player->player_token, $temp_info['robot_token']);// If the new experience is over the required, level up the robot
                    $level_boost = 0;
                    if ($temp_new_experience > $temp_required_experience){
                        //$level_boost = floor($temp_new_experience / $temp_required_experience);

                        while ($temp_new_experience > $temp_required_experience){
                            $level_boost += 1;
                            $temp_new_experience -= $temp_required_experience;
                            $temp_required_experience = rpg_prototype::calculate_experience_required($temp_robot->robot_level + $level_boost);
                        }

                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] += $level_boost;
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] = $temp_new_experience; //$level_boost * $temp_required_experience;
                        if ($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] > 100){
                            $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] = 100;
                        }

                        $temp_new_experience = rpg_game::robot_experience($target_player->player_token, $temp_info['robot_token']);
                    }

                    // Define the new level for this robot
                    $temp_new_level = rpg_game::robot_level($target_player->player_token, $temp_robot_token);

                }
                // Otherwise if this is a level 100 robot already
                else {
                    // Collect the robot's current experience and level for reference later
                    $temp_start_experience = rpg_game::robot_experience($target_player->player_token, $temp_robot_token);
                    $temp_start_level = rpg_game::robot_level($target_player->player_token, $temp_robot_token);

                    // Define the new experience for this robot
                    $temp_new_experience = $temp_start_experience;
                    $temp_new_level = $temp_start_level;

                }

                // Define the event options
                $event_options = array();
                $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                $event_options['this_ability_results']['recovery_kind'] = 'experience';
                $event_options['this_ability_results']['recovery_type'] = '';
                $event_options['this_ability_results']['this_amount'] = $target_robot_experience;
                $event_options['this_ability_results']['this_result'] = 'success';
                $event_options['this_ability_results']['flag_affinity'] = true;
                $event_options['this_ability_results']['total_actions'] = 1;
                $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
                $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

                // Update player/robot frames and points for the victory
                $temp_robot->robot_frame = 'victory';
                $temp_robot->robot_level = $temp_new_level;
                $temp_robot->robot_experience = $temp_new_experience;
                $target_player->set_frame('victory');
                $temp_robot->update_session();

                // Only display the event if the player is under level 100
                if ($temp_robot->robot_level < 100 && $temp_robot->robot_class == 'master'){
                    // Display the win message for this robot with battle points
                    $temp_robot->robot_frame = 'taunt';
                    $temp_robot->robot_level = $temp_new_level;
                    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = rpg_prototype::calculate_experience_required($temp_robot->robot_level); }
                    $target_player->set_frame('victory');
                    $event_header = $temp_robot->robot_name.'&#39;s Rewards';
                    $event_multiplier_text = $temp_robot_boost_text;
                    $event_body = $temp_robot->print_name().' collects '.$event_multiplier_text.'<span class="recovery_amount ability_type ability_type_cutter">'.$target_robot_experience.'</span> experience points! ';
                    $event_body .= '<br />';
                    if (isset($temp_robot->robot_quotes['battle_victory'])){
                        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                        $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $temp_robot->robot_name);
                        $event_body .= $temp_robot->print_quote('battle_victory', $this_find, $this_replace);
                    }
                    //$event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
                    $temp_robot->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);
                    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = $temp_new_experience; }
                    if ($temp_robot->robot_core == 'copy'){
                        $temp_robot->robot_image = $temp_robot->robot_base_image;
                        $temp_robot->robot_image_overlay = array();
                     }
                    $temp_robot->update_session();
                    $target_player->update_session();
                }

                // Floor the robot's experience with or without the event
                $target_player->set_frame('victory');
                $temp_robot->robot_frame = 'base';
                if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = 0; }
                $temp_robot->update_session();

                // If the level has been boosted, display the stat increases
                if ($temp_start_level != $temp_new_level){
                    // Define the event options
                    $event_options = array();
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_kind'] = 'level';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['flag_affinity'] = true;
                    $event_options['this_ability_results']['flag_critical'] = true;
                    $event_options['this_ability_results']['this_amount'] = $temp_new_level - $temp_start_level;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = 2;
                    $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
                    $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

                    // Display the win message for this robot with battle points
                    $temp_robot->robot_frame = 'taunt';
                    $temp_robot->robot_level = $temp_new_level;
                    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = rpg_prototype::calculate_experience_required($temp_robot->robot_level); }
                    else { $temp_robot->robot_experience = $temp_new_experience; }
                    $target_player->set_frame('victory');
                    $event_header = $temp_robot->robot_name.'&#39;s Rewards';
                    //$event_body = $temp_robot->print_name().' grew to <span class="recovery_amount'.($temp_new_level >= 100 ? ' ability_type ability_type_electric' : '').'">Level '.$temp_new_level.'</span>!<br /> ';
                    $event_body = $temp_robot->print_name().' grew to <span class="recovery_amount ability_type ability_type_level">Level '.$temp_new_level.($temp_new_level >= 100 ? ' &#9733;' : '').'</span>!<br /> ';
                    $event_body .= $temp_robot->robot_name.'&#39;s energy, weapons, shields, and mobility were upgraded!';
                    //$event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
                    $temp_robot->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);
                    $temp_robot->robot_experience = 0;
                    $temp_robot->update_session();

                    // Collect the base robot template from the index for calculations
                    $temp_index_robot = rpg_robot::get_index_info($temp_robot->robot_token);

                    // Define the event options
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['this_amount'] = $this_defense_boost;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = 0;
                    $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
                    $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

                    // Update the robot rewards array with any recent info
                    $temp_robot_rewards = rpg_game::robot_rewards($target_player->player_token, $temp_robot->robot_token);
                    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r($temp_robot_rewards, true)).'</pre>', $event_options);

                    // Define the base energy boost based on robot base stats
                    $temp_energy_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_energy']));

                    // If this robot has reached level 100, the max level, create the flag in their session
                    if ($temp_new_level >= 100){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['flags']['reached_max_level'] = true; }

                    // Check if there are eny pending energy stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_energy_pending'])){
                        $temp_robot_rewards['robot_energy_pending'] = round($temp_robot_rewards['robot_energy_pending']);
                        $temp_energy_boost += $temp_robot_rewards['robot_energy_pending'];
                        if (!empty($temp_robot_rewards['robot_energy'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy'] += $temp_robot_rewards['robot_energy_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy'] = $temp_robot_rewards['robot_energy_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy_pending'] = 0;
                    }

                    // Increment this robot's energy by the calculated amount and display an event
                    $temp_robot->robot_energy += $temp_energy_boost;
                    $temp_base_energy_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_energy']));
                    $temp_robot->robot_base_energy += $temp_base_energy_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'energy';
                    $event_options['this_ability_results']['this_amount'] = $temp_energy_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $event_body = $temp_robot->print_name().'&#39;s health improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_name().'&#39;s energy grew by <span class="recovery_amount">'.$temp_energy_boost.'</span>! ';
                    $temp_robot->robot_frame = 'summon';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


                    // Define the base attack boost based on robot base stats
                    $temp_attack_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_attack']));

                    // Check if there are eny pending attack stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_attack_pending'])){
                        $temp_robot_rewards['robot_attack_pending'] = round($temp_robot_rewards['robot_attack_pending']);
                        $temp_attack_boost += $temp_robot_rewards['robot_attack_pending'];
                        if (!empty($temp_robot_rewards['robot_attack'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack'] += $temp_robot_rewards['robot_attack_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack'] = $temp_robot_rewards['robot_attack_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack_pending'] = 0;
                    }

                    // Increment this robot's attack by the calculated amount and display an event
                    $temp_robot->robot_attack += $temp_attack_boost;
                    $temp_base_attack_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_attack']));
                    $temp_robot->robot_base_attack += $temp_base_attack_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'attack';
                    $event_options['this_ability_results']['this_amount'] = $temp_attack_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $event_body = $temp_robot->print_name().'&#39;s weapons improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_name().'&#39;s attack grew by <span class="recovery_amount">'.$temp_attack_boost.'</span>! ';
                    $temp_robot->robot_frame = 'shoot';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


                    // Define the base defense boost based on robot base stats
                    $temp_defense_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_defense']));

                    // Check if there are eny pending defense stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_defense_pending'])){
                        $temp_robot_rewards['robot_defense_pending'] = round($temp_robot_rewards['robot_defense_pending']);
                        $temp_defense_boost += $temp_robot_rewards['robot_defense_pending'];
                        if (!empty($temp_robot_rewards['robot_defense'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense'] += $temp_robot_rewards['robot_defense_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense'] = $temp_robot_rewards['robot_defense_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense_pending'] = 0;
                    }

                    // Increment this robot's defense by the calculated amount and display an event
                    $temp_robot->robot_defense += $temp_defense_boost;
                    $temp_base_defense_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_defense']));
                    $temp_robot->robot_base_defense += $temp_base_defense_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'defense';
                    $event_options['this_ability_results']['this_amount'] = $temp_defense_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $event_body = $temp_robot->print_name().'&#39;s shields improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_name().'&#39;s defense grew by <span class="recovery_amount">'.$temp_defense_boost.'</span>! ';
                    $temp_robot->robot_frame = 'defend';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


                    // Define the base speed boost based on robot base stats
                    $temp_speed_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_speed']));

                    // Check if there are eny pending speed stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_speed_pending'])){
                        $temp_robot_rewards['robot_speed_pending'] = round($temp_robot_rewards['robot_speed_pending']);
                        $temp_speed_boost += $temp_robot_rewards['robot_speed_pending'];
                        if (!empty($temp_robot_rewards['robot_speed'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_speed'] += $temp_robot_rewards['robot_speed_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_speed'] = $temp_robot_rewards['robot_speed_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_speed_pending'] = 0;
                    }

                    // Increment this robot's speed by the calculated amount and display an event
                    $temp_robot->robot_speed += $temp_speed_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'speed';
                    $event_options['this_ability_results']['this_amount'] = $temp_speed_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $temp_base_speed_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_speed']));
                    $temp_robot->robot_base_speed += $temp_base_speed_boost;
                    $event_body = $temp_robot->print_name().'&#39;s mobility improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_name().'&#39;s speed grew by <span class="recovery_amount">'.$temp_speed_boost.'</span>! ';
                    $temp_robot->robot_frame = 'slide';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);

                    // Update the robot frame
                    $temp_robot->robot_frame = 'base';
                    $temp_robot->update_session();

                }

                // Update the experience level for real this time
                $temp_robot->robot_experience = $temp_new_experience;
                $temp_robot->update_session();

                // Collect the robot info array
                $temp_robot_info = $temp_robot->export_array();

                // Collect the indexed robot rewards for new abilities
                $index_robot_rewards = $temp_robot_info['robot_rewards'];
                //$event_body = preg_replace('/\s+/', ' ', '<pre>'.print_r($index_robot_rewards, true).'</pre>');
                //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                // Loop through the ability rewards for this robot if set
                if ($temp_robot->robot_class != 'mecha' && ($temp_start_level == 100 || ($temp_start_level != $temp_new_level && !empty($index_robot_rewards['abilities'])))){
                    $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                    foreach ($index_robot_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){
                        // If the ability does not exist or is otherwise incomplete, continue
                        if (!isset($temp_abilities_index[$ability_reward_info['token']])){ continue; }
                        // If this ability is already unlocked, continue
                        if (rpg_game::ability_unlocked($target_player->player_token, $temp_robot_token, $ability_reward_info['token'])){ continue; }
                        // If we're in DEMO mode, continue
                        if (rpg_game::is_demo()){ continue; }

                        // Check if the required level has been met by this robot
                        if ($temp_new_level >= $ability_reward_info['level']){
                            // Create the temporary ability object for event creation
                            $temp_ability_info = array('ability_id' => (MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX + $ability_reward_key), 'ability_token' => $ability_reward_info['token']);
                            $temp_ability = new rpg_ability($target_player, $temp_robot, $temp_ability_info);

                            // Collect or define the ability variables
                            $temp_ability_token = $ability_reward_info['token'];

                            // Display the robot reward message markup
                            $event_header = $temp_ability->ability_name.' Unlocked';
                            $event_body = '<span class="robot_name">'.$temp_info['robot_name'].'</span> unlocked new ability data!<br />';
                            $event_body .= $temp_ability->print_name().' can now be used in battle!';
                            $event_options = array();
                            $event_options['console_show_target'] = false;
                            $event_options['this_header_float'] = $target_player->player_side;
                            $event_options['this_body_float'] = $target_player->player_side;
                            $event_options['this_ability'] = $temp_ability;
                            $event_options['this_ability_image'] = 'icon';
                            $event_options['console_show_this_player'] = false;
                            $event_options['console_show_this_robot'] = false;
                            $event_options['console_show_this_ability'] = true;
                            $event_options['canvas_show_this_ability'] = false;
                            $temp_robot->robot_frame = $ability_reward_key % 2 == 2 ? 'taunt' : 'victory';
                            $temp_robot->update_session();
                            $temp_ability->ability_frame = 'base';
                            $temp_ability->update_session();
                            $this_battle->events_create($temp_robot, false, $event_header, $event_body, $event_options);
                            $temp_robot->robot_frame = 'base';
                            $temp_robot->update_session();

                            // Automatically unlock this ability for use in battle
                            $this_reward = rpg_ability::get_index_info($temp_ability_token); //array('ability_token' => $temp_ability_token);
                            $temp_player_info = $target_player->export_array();
                            $show_event = !rpg_game::ability_unlocked('', '', $temp_ability_token) ? true : false;
                            rpg_game::unlock_ability($temp_player_info, $temp_robot_info, $this_reward, $show_event);
                            if ($temp_robot_info['robot_original_player'] == $temp_player_info['player_token']){ rpg_game::unlock_ability($temp_player_info, false, $this_reward); }
                            else { rpg_game::unlock_ability(array('player_token' => $temp_robot_info['robot_original_player']), false, $this_reward); }
                            //$_SESSION['GAME']['values']['battle_rewards'][$target_player_token]['player_robots'][$temp_robot_token]['robot_abilities'][$temp_ability_token] = $this_reward;

                        }

                    }
                }

            }


            // -- ITEM REWARDS -- //
            // Define the temp player rewards array
            $target_player_rewards = array();

            // Define the chance multiplier and start at one
            $temp_chance_multiplier = $trigger_options['item_multiplier'];

            // Increase the item chance multiplier if one is set for the stage
            if (isset($this_battle->field->field_multipliers['items'])){ $temp_chance_multiplier = ($temp_chance_multiplier * $this_battle->field->field_multipliers['items']); }

            // Define the available item drops for this battle
            $target_player_rewards['items'] = $this_battle->get_item_rewards();

            // Increase the multipliers if starter battle
            if (!empty($this_battle->flags['starter_battle'])){

                $temp_chance_multiplier = 4;

            }
            // Otherwise, define auto items
            else {

                // If the target holds a Fortune Module, increase the chance of dropps
                $temp_fortune_module = false;
                if ($target_robot->robot_item == 'item-fortune-module'){ $temp_fortune_module = true; }

                // If this robot was a MECHA class, it may drop SMALL SCREWS
                if ($this_robot->robot_class == 'mecha'){
                    $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-screw-small', 'quantity' => mt_rand(1, ($temp_fortune_module ? 9 : 6)));
                    // If this robot was an empty core, it drops other items too
                    if (!empty($this_robot->robot_core) && $this_robot->robot_core == 'empty'){
                        $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-super-pellet');
                    }
                }

                // If this robot was a MASTER class, it may drop LARGE SCREWS
                if ($this_robot->robot_class == 'master'){
                    $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-screw-large', 'quantity' => mt_rand(1, ($temp_fortune_module ? 6 : 3)));
                    // If this robot was an empty core, it drops other items too
                    if (!empty($this_robot->robot_core) && $this_robot->robot_core == 'empty'){
                        $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-super-capsule');
                    }
                }

                // If this robot was a BOSS class, it may drop EXTRA LIFE
                if ($this_robot->robot_class == 'boss'){
                    $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-extra-life', 'quantity' => mt_rand(1, ($temp_fortune_module ? 3 : 1)));
                }

                // If this robot was holding an ITEM, it should also drop that at a high rate
                if (!empty($this_robot->robot_item)){
                    $target_player_rewards['items'][] =  array('chance' => 100, 'token' => $this_robot->robot_item);
                }

            }



            // Precount the item values for later use
            $temp_value_total = 0;
            $temp_count_total = 0;
            foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_value_total += $item_reward_info['chance']; $temp_count_total += 1; }
            //$this_battle->events_create(false, false, 'DEBUG', '$temp_count_total = '.$temp_count_total.';<br /> $temp_value_total = '.$temp_value_total.'; ');

            // If this robot was a MECHA class and destroyed by WEAKNESS, it may drop a SHARD
            if ($this_robot->robot_class == 'mecha' && !empty($this_robot->flags['triggered_weakness'])){
                $temp_shard_type = !empty($this->robot_core) ? $this->robot_core : 'none';
                $target_player_rewards['items'] = array();
                $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-shard-'.$temp_shard_type);
                }
            // If this robot was a MASTER OR BOSS class and destroyed by WEAKNESS, it may drop a CORE
            elseif (in_array($this_robot->robot_class, array('master', 'boss')) && !empty($this_robot->flags['triggered_weakness'])){
                $temp_core_type = !empty($this->robot_core) ? $this->robot_core : 'none';
                $target_player_rewards['items'] = array();
                $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-core-'.$temp_core_type);
                }

            // Recount the item values for later use
            $temp_value_total = 0;
            $temp_count_total = 0;
            foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_value_total += $item_reward_info['chance']; $temp_count_total += 1; }
            // Adjust item values for easier to understand percentages
            foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $target_player_rewards['items'][$item_reward_key]['chance'] = ceil(($item_reward_info['chance'] / $temp_value_total) * 100); }

            // Shuffle the rewards so it doesn't look to formulaic
            shuffle($target_player_rewards['items']);

            // DEBUG
            //$temp_string = '';
            //foreach ($target_player_rewards['items'] AS $info){ $temp_string .= $info['token'].' = '.$info['chance'].'%, '; }
            //$this_battle->events_create(false, false, 'DEBUG', '$target_player_rewards[\'items\'] = '.count($target_player_rewards['items']).'<br /> '.$temp_string);

            // Define a function for dealing with item drops
            if (!function_exists('temp_player_rewards_items')){
                function temp_player_rewards_items($this_battle, $target_player, $target_robot, $this_robot, $item_reward_key, $item_reward_info, $item_drop_count = 1){
                    global $mmrpg_index;
                    // Create the temporary ability object for event creation
                    $temp_info = array('ability_id' => (MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX + $item_reward_key + 300), 'ability_token' => $item_reward_info['ability_token']);
                    $temp_ability = new rpg_ability($target_player, $target_robot, $item_reward_info);
                    $temp_ability->ability_name = $item_reward_info['ability_name'];
                    $temp_ability->ability_image = $item_reward_info['ability_token'];
                    $temp_ability->update_session();

                    // Collect or define the ability variables
                    $temp_item_token = $item_reward_info['ability_token'];
                    $temp_item_name = $item_reward_info['ability_name'];
                    $temp_item_colour = !empty($item_reward_info['ability_type']) ? $item_reward_info['ability_type'] : 'none';
                    if (!empty($item_reward_info['ability_type2'])){ $temp_item_colour .= '_'.$item_reward_info['ability_type2']; }
                    $temp_type_name = !empty($item_reward_info['ability_type']) ? ucfirst($item_reward_info['ability_type']) : 'Neutral';
                    $allow_over_max = false;
                    $temp_is_shard = preg_match('/^item-shard-/i', $temp_item_token) ? true : false;
                    $temp_is_core = preg_match('/^item-core-/i', $temp_item_token) ? true : false;
                    // Define the max quantity limit for this particular item
                    if ($temp_is_shard){ $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; $allow_over_max = true; }
                    elseif ($temp_is_core){ $temp_item_quantity_max = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
                    else { $temp_item_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }
                    // Create the session variable for this item if it does not exist and collect its value
                    if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
                    $temp_item_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
                    // If this item is already at the quantity limit, skip it entirely
                    if ($temp_item_quantity >= $temp_item_quantity_max){
                        //$this_battle->events_create(false, false, 'DEBUG', 'max count for '.$temp_item_token.' of '.$temp_item_quantity_max.' has been reached ('.($allow_over_max ? 'allow' : 'disallow').')');
                        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_item_quantity_max;
                        $temp_item_quantity = $temp_item_quantity_max;
                        if (!$allow_over_max){ return true; }
                    }

                    // Define the new item quantity after increment
                    $temp_item_quantity_new = $temp_item_quantity + $item_drop_count;
                    $shards_remaining = false;
                    // If this is a shard piece
                    if ($temp_is_shard){
                        // Define the number of shards remaining for a new core
                        $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY;
                        $shards_remaining = $temp_item_quantity_max - $temp_item_quantity_new;
                        // If this player has collected enough shards to create a new core
                        if ($shards_remaining == 0){ $temp_body_addon = 'The other '.$temp_type_name.' Shards from the inventory started glowing&hellip;'; }
                        // Otherwise, if more shards are required to create a new core
                        else { $temp_body_addon = 'Collect '.$shards_remaining.' more shard'.($shards_remaining > 1 ? 's' : '').' to create a new '.$temp_type_name.' Core!'; }
                    }
                    // Else if this is a core
                    elseif (preg_match('/^item-core-/i', $temp_item_token)){
                        // Define the robot core drop text for displau
                        $temp_body_addon = $target_player->print_name().' added the new core to the inventory.';
                    }
                    // Otherwise, if a normal item
                    else {
                        // Define the normal item drop text for display
                        $temp_body_addon = $target_player->print_name().' added the dropped item'.($item_drop_count > 1 ? 's' : '').' to the inventory.';
                    }

                    // Display the robot reward message markup
                    $event_header = $temp_item_name.' Item Drop';
                    $event_body = rpg_functions::get_random_positive_word();
                    $event_body .= ' The disabled '.$this_robot->print_name().' dropped ';
                    if ($item_drop_count == 1){ $event_body .= (preg_match('/^(a|e|i|o|u)/i', $temp_item_name) ? 'an' : 'a').' <span class="ability_name ability_type ability_type_'.$temp_item_colour.'">'.$temp_item_name.'</span>!<br />'; }
                    else { $event_body .= 'x'.$item_drop_count.' <span class="ability_name ability_type ability_type_'.$temp_item_colour.'">'.($temp_item_name == 'Extra Life' ? 'Extra Lives' : $temp_item_name.'s').'</span>!<br />'; }
                    $event_body .= $temp_body_addon;
                    $event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $target_player->player_side;
                    $event_options['this_body_float'] = $target_player->player_side;
                    $event_options['this_ability'] = $temp_ability;
                    $event_options['this_ability_image'] = 'icon';
                    $event_options['event_flag_victory'] = true;
                    $event_options['console_show_this_player'] = false;
                    $event_options['console_show_this_robot'] = false;
                    $event_options['console_show_this_ability'] = true;
                    $event_options['canvas_show_this_ability'] = true;
                    $target_player->set_frame(($item_reward_key % 3 == 0 ? 'victory' : 'taunt'));
                    $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'taunt' : 'base';
                    $target_robot->update_session();
                    $temp_ability->ability_frame = 'base';
                    $temp_ability->ability_frame_offset = array('x' => 220, 'y' => 0, 'z' => 10);
                    $temp_ability->update_session();
                    $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

                    // Create and/or increment the session variable for this item increasing its quantity
                    if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
                    if ($temp_item_quantity < $temp_item_quantity_max){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] += $item_drop_count; }

                    // If this was a shard, and it was the LAST shard
                    if ($shards_remaining !== false && $shards_remaining < 1){

                        // Define the new core token and increment value in session
                        $temp_core_token = str_replace('shard', 'core', $temp_item_token);
                        $temp_core_name = str_replace('Shard', 'Core', $temp_item_name);
                        $item_core_info = array('ability_token' => $temp_core_token, 'ability_name' => $temp_core_name, 'ability_type' => $item_reward_info['ability_type']);

                        // Create the temporary ability object for event creation
                        $temp_info['ability_id'] += 1;
                        $temp_info['ability_token'] = $temp_core_token;
                        $temp_core = new rpg_ability($target_player, $target_robot, $temp_info);
                        $temp_core->ability_name = $item_core_info['ability_name'];
                        $temp_core->ability_image = $item_core_info['ability_token'];
                        $temp_core->update_session();

                        // Collect or define the ability variables
                        //$temp_core_token = $item_core_info['ability_token'];
                        //$temp_core_name = $item_core_info['ability_name'];
                        $temp_type_name = !empty($temp_core->ability_type) ? ucfirst($temp_core->ability_type) : 'Neutral';
                        $temp_core_colour = !empty($temp_core->ability_type) ? $temp_core->ability_type : 'none';
                        // Define the max quantity limit for this particular item
                        $temp_core_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
                        // Create the session variable for this item if it does not exist and collect its value
                        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
                        $temp_core_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_core_token];
                        // If this item is already at the quantity limit, skip it entirely
                        if ($temp_core_quantity >= $temp_core_quantity_max){
                            //$this_battle->events_create(false, false, 'DEBUG', 'max count for '.$temp_core_token.' of '.$temp_core_quantity_max.' has been reached');
                            $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = $temp_core_quantity_max;
                            $temp_core_quantity = $temp_core_quantity_max;
                            return true;
                        }

                        // Display the robot reward message markup
                        $event_header = $temp_core_name.' Item Fusion';
                        $event_body = rpg_functions::get_random_positive_word().' The glowing shards fused to create a new '.$temp_core->print_name().'!<br />';
                        $event_body .= $target_player->print_name().' added the new core to the inventory.';
                        $event_options = array();
                        $event_options['console_show_target'] = false;
                        $event_options['this_header_float'] = $target_player->player_side;
                        $event_options['this_body_float'] = $target_player->player_side;
                        $event_options['this_ability'] = $temp_core;
                        $event_options['this_ability_image'] = 'icon';
                        $event_options['event_flag_victory'] = true;
                        $event_options['console_show_this_player'] = false;
                        $event_options['console_show_this_robot'] = false;
                        $event_options['console_show_this_ability'] = true;
                        $event_options['canvas_show_this_ability'] = true;
                        $target_player->set_frame(($item_reward_key + 1 % 3 == 0 ? 'taunt' : 'victory'));
                        $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'base' : 'taunt';
                        $target_robot->update_session();
                        $temp_core->ability_frame = 'base';
                        $temp_core->ability_frame_offset = array('x' => 220, 'y' => 0, 'z' => 10);
                        $temp_core->update_session();
                        $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

                        // Create and/or increment the session variable for this item increasing its quantity
                        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
                        if ($temp_core_quantity < $temp_core_quantity_max){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] += 1; }

                        // Set the old shard counter back to zero now that they've fused
                        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0;
                        $temp_item_quantity = 0;

                    }

                    // Return true on success
                    return true;

                }
            }

            // Loop through the ability rewards for this robot if set and NOT demo mode
            if (rpg_game::is_user() && !empty($target_player_rewards['items']) && $this->player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID){
                $temp_items_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                // Define the default success rate and multiply by the modifier
                $temp_success_value = $this_robot->robot_class == 'master' ? 50 : 25;
                $temp_success_value = ceil($temp_success_value * $temp_chance_multiplier);
                // Empty cores always have item drops
                if (!empty($this_robot->robot_core) && $this_robot->robot_core == 'empty'){ $temp_success_value = 100; }
                // If the target holds a Fortune Module, increase the chance of dropps
                if ($target_robot->robot_item == 'item-fortune-module'){ $temp_success_value = $temp_success_value * 2; }
                // Fix success values over 100
                if ($temp_success_value > 100){ $temp_success_value = 100; }
                // Define the failure based on success rate
                $temp_failure_value = 100 - $temp_success_value;
                // Define the dropping result based on rates
                $temp_dropping_result = $temp_success_value == 100 ? 'success' : rpg_functions::weighted_chance(array('success', 'failure'), array($temp_success_value, $temp_failure_value));
                //$this_battle->events_create(false, false, 'DEBUG', '..and the result of the drop ('.$temp_success_value.' / '.$temp_failure_value.') is '.$temp_dropping_result);
                if ($temp_dropping_result == 'success'){

                    $temp_value_total = 0;
                    $temp_count_total = 0;
                    foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){
                        $temp_value_total += $item_reward_info['chance'];
                        $temp_count_total += 1;
                    }

                    $temp_item_counts = array();
                    $temp_item_tokens = array();
                    $temp_item_weights = array();
                    if ($temp_value_total > 0){
                        foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){
                            $temp_item_tokens[] = $item_reward_info['token'];
                            $temp_item_weights[] = ceil(($item_reward_info['chance'] / $temp_value_total) * 100);
                            $temp_item_counts[$item_reward_info['token']] = isset($item_reward_info['quantity']) ? $item_reward_info['quantity'] : 1;
                        }
                    }

                    $temp_random_item = rpg_functions::weighted_chance($temp_item_tokens, $temp_item_weights);

                    $item_index_info = rpg_ability::parse_index_info($temp_items_index[$temp_random_item]);
                    $item_drop_count = $temp_item_counts[$temp_random_item];

                    temp_player_rewards_items($this_battle, $target_player, $target_robot, $this, $item_reward_key, $item_index_info, $item_drop_count);
                }
            }

        }

        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG', 'we made it past the experience boosts');

        // If the player has replacement robots and the knocked-out one was active
        if ($this_player->counters['robots_active'] > 0){
            // Try to find at least one active POSITION robot before requiring a switch
            $has_active_positon_robot = false;
            foreach ($this_player->values['robots_active'] AS $key => $robot){
                //if ($robot['robot_position'] == 'active'){ $has_active_positon_robot = true; }
            }

            // If the player does NOT have an active position robot, trigger a switch
            if (!$has_active_positon_robot){
                // If the target player is not on autopilot, require input
                if ($this_player->player_autopilot == false){
                    // Empty the action queue to allow the player switch time
                    $this_battle->actions = array();
                }
                // Otherwise, if the target player is on autopilot, automate input
                elseif ($this_player->player_autopilot == true){  // && $this_player->player_next_action != 'switch'
                    // Empty the action queue to allow the player switch time
                    $this_battle->actions = array();

                    // Remove any previous switch actions for this player
                    $backup_switch_actions = $this_battle->actions_extract(array(
                        'this_player_id' => $this_player->player_id,
                        'this_action' => 'switch'
                        ));

                    //$this_battle->events_create(false, false, 'DEBUG DEBUG', 'This is a test from inside the dead trigger ['.count($backup_switch_actions).'].');

                    // If there were any previous switches removed
                    if (!empty($backup_switch_actions)){
                        // If the target robot was faster, it should attack first
                        if ($this_robot->robot_speed > $target_robot->robot_speed){
                            // Prepend an ability action for this robot
                            $this_battle->actions_prepend(
                                $this_player,
                                $this_robot,
                                $target_player,
                                $target_robot,
                                'ability',
                                ''
                                );
                        }
                        // Otherwise, if the target was slower, if should attack second
                        else {
                            // Prepend an ability action for this robot
                            $this_battle->actions_append(
                                $this_player,
                                $this_robot,
                                $target_player,
                                $target_robot,
                                'ability',
                                ''
                                );
                        }
                    }

                    // Prepend a switch action for the target robot
                    $this_battle->actions_prepend(
                        $this_player,
                        $this_robot,
                        $target_player,
                        $target_robot,
                        'switch',
                        ''
                        );

                }

            }

        }
        // Otherwise, if the target is out of robots...
        else {
            // Trigger a battle complete action
            $this_battle->trigger_complete($target_player, $target_robot, $this_player, $this_robot);

        }

        // Either way, set the hidden flag on the robot
        //if (($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1) && $this_robot->robot_position == 'bench'){
        if ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1){
            //$this_robot->robot_status == 'disabled';
            $this_robot->flags['apply_disabled_state'] = true;
            if ($this_robot->robot_position == 'bench'){ $this_robot->flags['hidden'] = true; }
            $this_robot->update_session();
        }

        // -- ROBOT UNLOCKING STUFF!!! -- //

        // Check if this target winner was a HUMAN player and update the robot database counter for defeats
        if ($target_player->player_side == 'left'){
            // Add this robot to the global robot database array
            if (!isset($_SESSION['GAME']['values']['robot_database'][$this->robot_token])){ $_SESSION['GAME']['values']['robot_database'][$this->robot_token] = array('robot_token' => $this->robot_token); }
            if (!isset($_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated'])){ $_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated'] = 0; }
            $_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated']++;
        }

        // Check if this battle has any robot rewards to unlock and the winner was a HUMAN player
        $temp_robot_rewards = $this_battle->get_robot_rewards();
        if ($target_player->player_side == 'left' && !empty($temp_robot_rewards)){
            // DEBUG
            //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | battle_rewards_robots = '.count($temp_robot_rewards).'');
            foreach ($temp_robot_rewards AS $temp_reward_key => $temp_reward_info){
                // DEBUG
                //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | checking '.$this->robot_token.' == '.preg_replace('/\s+/', ' ', print_r($temp_reward_info, true)).'...');
                // Check if this robot was part of the rewards for this battle
                if (!rpg_game::robot_unlocked(false, $temp_reward_info['token']) && $this->robot_token == $temp_reward_info['token']){
                    // DEBUG
                    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | '.$this->robot_token.' == '.$temp_reward_info['token'].' is a match!');

                    // Check if this robot has been attacked with any elemental moves
                    if (!empty($this->history['triggered_damage_types'])){
                        // Loop through all the damage types and check if they're not empty
                        foreach ($this->history['triggered_damage_types'] AS $key => $types){
                            if (!empty($types)){
                                // DEBUG
                                //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | '.$this->robot_token.' was attacked with a '.implode(', ', $types).' type ability!<br />Removing from the battle rewards!');

                                // Generate the robot removed event showing the destruction
                                /*
                                $event_header = $this->robot_name.'&#39;s Data Destroyed';
                                $event_body = $this->print_name().'&#39;s battle data was damaged beyond repair!<br />';
                                $event_body .= $this->print_name().' could not be unlocked for use in battle&hellip;';
                                $event_options = array();
                                $event_options['console_show_target'] = false;
                                $event_options['this_header_float'] = $this_player->player_side;
                                $event_options['this_body_float'] = $this_player->player_side;
                                $event_options['console_show_this_player'] = false;
                                $event_options['console_show_this_robot'] = true;
                                $this_robot->robot_frame = 'defeat';
                                $this_robot->update_session();
                                $this_battle->events_create($this, false, $event_header, $event_body, $event_options);
                                */

                                // Remove this robot from the battle rewards array
                                unset($temp_robot_rewards[$temp_reward_key]);

                                // Break, we know all we need to
                                break;
                            }
                        }
                    }

                    // If this robot is somehow still a reward, print a message showing a good job
                    if (!empty($temp_robot_rewards[$temp_reward_key])){
                        // Collect this reward's information
                        $robot_reward_info = $temp_robot_rewards[$temp_reward_key];

                        // Collect or define the robot points and robot rewards variables
                        //$this_robot_token = $robot_reward_info['token'];
                        $this_robot_level = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
                        $this_robot_experience = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
                        $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

                        // Create the temp new robot for the player
                        //$temp_index_robot = rpg_robot::get_index_info($this_robot_token);
                        $temp_index_robot['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID * 2;
                        $temp_index_robot['robot_level'] = $this_robot_level;
                        $temp_index_robot['robot_experience'] = $this_robot_experience;
                        $temp_unlocked_robot = new rpg_robot($target_player, $temp_index_robot);

                        // Automatically unlock this robot for use in battle
                        //$temp_unlocked_player = $mmrpg_index['players'][$target_player->player_token];
                        rpg_game::unlock_robot($temp_unlocked_player, $temp_index_robot, true, true);

                        // Display the robot reward message markup
                        //$event_header = $temp_unlocked_robot->robot_name.' Unlocked';
                        $event_body = rpg_functions::get_random_positive_word().' '.$target_player->print_name().' unlocked new robot data!<br />';
                        $event_body .= $temp_unlocked_robot->print_name().' can now be used in battle!';
                        $event_options = array();
                        $event_options['console_show_target'] = false;
                        $event_options['this_header_float'] = $target_player->player_side;
                        $event_options['this_body_float'] = $target_player->player_side;
                        $event_options['this_robot_image'] = 'mug';
                        $temp_unlocked_robot->robot_frame = 'base';
                        $temp_unlocked_robot->update_session();
                        $this_battle->events_create($temp_unlocked_robot, false, $event_header, $event_body, $event_options);

                    }

                    // Update the battle with robot reward changes
                    $temp_robot_rewards = array_values($temp_robot_rewards);
                    $this_battle->set_robot_rewards($temp_robot_rewards);

                }

            }
        }

        // Return true on success
        return true;

    }

    // Define a function for calculating required weapon energy
    public function calculate_weapon_energy($this_ability, &$energy_base = 0, &$energy_mods = 0){
        // Determine how much weapon energy this should take
        $energy_new = $this_ability->ability_energy;
        $energy_base = $energy_new;
        $energy_mods = 0;
        // Only calculate energy if this is not the struggle action
        if ($this_ability->ability_token != 'action-noweapons'){
            // If this robot's core is Elemental, apply a 50% WE reduction to abilities with matching type1 or type2
            if (!empty($this->robot_core) && ($this->robot_core == $this_ability->ability_type || $this->robot_core == $this_ability->ability_type2)){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            // Otherwise, if this robot's core is Neutral, apply a 50% WE reduction to abilities with matching type1 only
            elseif (empty($this->robot_core) && (empty($this_ability->ability_type) && empty($this_ability->ability_type2))){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            // Check to make sure this robot has level-up abilities before looping
            if (!empty($this->robot_rewards['abilities'])){
                // Loop through level up abilities and apply a 50% WE reduction to a matching ability
                foreach ($this->robot_rewards['abilities'] AS $key => $info){
                    if ($info['token'] == $this_ability->ability_token){
                        $energy_mods++;
                        $energy_new = ceil($energy_new * 0.5);
                        break;
                    }
                }
            }
        }
        // Otherwise, if this is the struggle action, energy is zero
        else {
            $this_ability->ability_energy = 0;
        }
        // Return the resulting weapon energy
        return $energy_new;
    }

    // Define a function for calculating required weapon energy without using objects
    static function calculate_weapon_energy_static($this_robot, $this_ability, &$energy_base = 0, &$energy_mods = 0){
        // Determine how much weapon energy this should take
        $energy_new = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 0;
        $energy_base = $energy_new;
        $energy_mods = 0;
        // Generate required variables if not already provided
        if (!isset($this_robot['robot_core'])){ $this_robot['robot_core'] = ''; }
        if (!isset($this_ability['ability_type'])){ $this_ability['ability_type'] = ''; }
        if (!isset($this_ability['ability_type2'])){ $this_ability['ability_type2'] = ''; }
        // Only calculate energy if this is not the struggle action
        if ($this_ability['ability_token'] != 'action-noweapons'){
            // If this robot's core is Elemental, apply a 50% WE reduction to abilities with matching type1 or type2
            if (!empty($this_robot['robot_core']) && ($this_robot['robot_core'] == $this_ability['ability_type'] || $this_robot['robot_core'] == $this_ability['ability_type2'])){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            // Otherwise, if this robot's core is Neutral, apply a 50% WE reduction to abilities with matching type1 only
            elseif (empty($this_robot['robot_core']) && (empty($this_ability['ability_type']) && empty($this_ability['ability_type2']))){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            // Check to make sure this robot has level-up abilities before looping
            if (!empty($this_robot['robot_rewards']['abilities'])){
                // Loop through level up abilities and apply a 50% WE reduction to a matching ability
                foreach ($this_robot['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == $this_ability['ability_token']){
                        $energy_mods++;
                        $energy_new = ceil($energy_new * 0.5);
                        break;
                    }
                }
            }
        }
        // Otherwise, if this is the struggle action, energy is zero
        else {
            $this_ability['ability_energy'] = 0;
        }
        // Return the resulting weapon energy
        return $energy_new;
    }

    // Define a function for generating robot canvas variables
    public function get_canvas_markup($options, $player_data){

        // Define the variable to hold the console robot data
        $this_data = array();
        $this_target_options = !empty($options['this_ability']->target_options) ? $options['this_ability']->target_options : array();
        $this_damage_options = !empty($options['this_ability']->damage_options) ? $options['this_ability']->damage_options : array();
        $this_recovery_options = !empty($options['this_ability']->recovery_options) ? $options['this_ability']->recovery_options : array();
        $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

        // Define and calculate the simpler markup and positioning variables for this robot
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'robot';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['flags'] = $this->flags;
        $this_data['counters'] = $this->counters;
        $this_data['values'] = $this->values;
        $this_data['robot_id'] = $this->robot_id;
        $this_data['robot_token'] = $this->robot_token;
        $this_data['robot_id_token'] = $this->robot_id.'_'.$this->robot_token;
        $this_data['robot_key'] = !empty($this->robot_key) ? $this->robot_key : 0;
        $this_data['robot_core'] = !empty($this->robot_core) ? $this->robot_core : 'none';
        $this_data['robot_class'] = !empty($this->robot_class) ? $this->robot_class : 'master';
        $this_data['robot_stance'] = !empty($this->robot_stance) ? $this->robot_stance : 'base';
        $this_data['robot_frame'] = !empty($this->robot_frame) ? $this->robot_frame : 'base';
        $this_data['robot_frame_index'] = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        $this_data['robot_frame_classes'] = !empty($this->robot_frame_classes) ? $this->robot_frame_classes : '';
        $this_data['robot_frame_styles'] = !empty($this->robot_frame_styles) ? $this->robot_frame_styles : '';
        $this_data['robot_detail_styles'] = !empty($this->robot_detail_styles) ? $this->robot_detail_styles : '';
        $this_data['robot_image'] = $this->robot_image;
        $this_data['robot_image_overlay'] = !empty($this->robot_image_overlay) ? $this->robot_image_overlay : array();
        $this_data['robot_float'] = $this->player->player_side;
        $this_data['robot_direction'] = $this->player->player_side == 'left' ? 'right' : 'left';
        $this_data['robot_status'] = $this->robot_status;
        $this_data['robot_position'] = !empty($this->robot_position) ? $this->robot_position : 'bench';
        $this_data['robot_action'] = 'scan_'.$this->robot_id.'_'.$this->robot_token;
        $this_data['robot_size'] = $this_data['robot_position'] == 'active' ? ($this->robot_image_size * 2) : $this->robot_image_size;
        $this_data['robot_size_base'] = $this->robot_image_size;
        $this_data['robot_size_path'] = ($this->robot_image_size * 2).'x'.($this->robot_image_size * 2);
        //$this_data['robot_scale'] = $this_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_data['robot_key']) / 8) * 0.5);
        //$this_data['robot_title'] = $this->robot_number.' '.$this->robot_name.' (Lv. '.$this->robot_level.')';
        $this_data['robot_title'] = $this->robot_name.' (Lv. '.$this->robot_level.')';
        $this_data['robot_title'] .= ' <br />'.(!empty($this_data['robot_core']) && $this_data['robot_core'] != 'none' ? ucfirst($this_data['robot_core']).' Core' : 'Neutral Core');
        $this_data['robot_title'] .= ' | '.ucfirst($this_data['robot_position']).' Position';
        $temp_energy_max = !empty($this->flags['robot_stat_max_energy']) ? ' &#9733;' : '';
        $temp_attack_max = !empty($this->flags['robot_stat_max_attack']) ? ' &#9733;' : '';
        $temp_defense_max = !empty($this->flags['robot_stat_max_defense']) ? ' &#9733;' : '';
        $temp_speed_max = !empty($this->flags['robot_stat_max_speed']) ? ' &#9733;' : '';

        // Calculate the canvas offset variables for this robot
        $temp_data = rpg_functions::canvas_sprite_offset($this_data['robot_key'], $this_data['robot_position'], $this_data['robot_size']);
        $this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'] + round($this->robot_frame_offset['x'] * $temp_data['canvas_scale']);
        $this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'] + round($this->robot_frame_offset['y'] * $temp_data['canvas_scale']);
        $this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'] + round($this->robot_frame_offset['z'] * $temp_data['canvas_scale']);
        $this_data['canvas_offset_rotate'] = 0;
        $this_data['robot_scale'] = $temp_data['canvas_scale'];

        // Calculate the zoom properties for the robot sprite
        $zoom_size = $this->robot_image_size * 2;
        $frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        $this_data['robot_sprite_size'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_sprite_width'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_sprite_height'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_file_width'] = ceil($this_data['robot_scale'] * $zoom_size * count($frame_index));
        $this_data['robot_file_height'] = ceil($this_data['robot_scale'] * $zoom_size);

        /* DEBUG
        $this_data['robot_title'] = $this->robot_name
            .' | ID '.str_pad($this->robot_id, 3, '0', STR_PAD_LEFT).''
            //.' | '.strtoupper($this->robot_position)
            .' | '.$this->robot_energy.' LE'
            .' | '.$this->robot_attack.' AT'
            .' | '.$this->robot_defense.' DF'
            .' | '.$this->robot_speed.' SP';
            */

        // If this robot is on the bench and inactive, override default sprite frames
        if ($this_data['robot_position'] == 'bench' && $this_data['robot_frame'] == 'base' && $this_data['robot_status'] != 'disabled'){

            // Define a randomly generated integer value
            $random_int = mt_rand(1, 10);
            // If the random number was one, show an attack frame
            if ($random_int == 1){ $this_data['robot_frame'] = 'taunt'; }
            // Else if the random number was two, show a defense frame
            elseif ($random_int == 2){ $this_data['robot_frame'] = 'defend'; }
            // Else if the random number was anything else, show the base frame
            else { $this_data['robot_frame'] = 'base'; }

        }

        // If the robot is defeated, move its sprite across the field
        if ($this_data['robot_frame'] == 'defeat'){
            //$this_data['canvas_offset_x'] -= ceil($this_data['robot_size'] * 0.10);
        }

        // Fix the robot x position if it's size if greater than 80
        //$this_data['canvas_offset_x'] -= ceil(($this_data['robot_size'] - 80) * 0.10);

        // If this robot is being damaged of is defending
        if ($this_data['robot_status'] == 'disabled' && $this_data['robot_frame'] != 'damage'){

            //$this_data['robot_frame'] = 'defeat';
            $this_data['canvas_offset_x'] -= 10;

        } elseif ($this_data['robot_frame'] == 'damage' || $this_data['robot_stance'] == 'defend'){

            if (!empty($this_results['total_strikes']) || (!empty($this_results['this_result']) && $this_results['this_result'] == 'success')){ //checkpoint
                if ($this_results['trigger_kind'] == 'damage' && !empty($this_damage_options['damage_kickback']['x'])){
                    $this_data['canvas_offset_rotate'] += ceil(($this_damage_options['damage_kickback']['x'] / 100) * 45);
                    $this_data['canvas_offset_x'] -= ceil($this_damage_options['damage_kickback']['x'] * 1.5); //isset($this_results['total_strikes']) ? $this_damage_options['damage_kickback']['x'] + ($this_damage_options['damage_kickback']['x'] * $this_results['total_strikes']) : $this_damage_options['damage_kickback']['x'];
                }
                elseif ($this_results['trigger_kind'] == 'recovery' && !empty($this_recovery_options['recovery_kickback']['x'])){
                    $this_data['canvas_offset_rotate'] += ceil(($this_recovery_options['recovery_kickback']['x'] / 100) * 50);
                    $this_data['canvas_offset_x'] -= ceil($this_recovery_options['recovery_kickback']['x'] * 1.5); //isset($this_results['total_strikes']) ? $this_recovery_options['recovery_kickback']['x'] + ($this_recovery_options['recovery_kickback']['x'] * $this_results['total_strikes']) : $this_recovery_options['recovery_kickback']['x'];
                }
                $this_data['canvas_offset_rotate'] += ceil($this_results['total_strikes'] * 10);
            }

            if (!empty($this_results['this_result']) && $this_results['this_result'] == 'success'){
                if ($this_results['trigger_kind'] == 'damage' && !empty($this_damage_options['damage_kickback']['y'])){
                    $this_data['canvas_offset_y'] += $this_damage_options['damage_kickback']['y']; //isset($this_results['total_strikes']) ? ($this_damage_options['damage_kickback']['y'] * $this_results['total_strikes']) : $this_damage_options['damage_kickback']['y'];
                }
                elseif ($this_results['trigger_kind'] == 'recovery' && !empty($this_recovery_options['recovery_kickback']['y'])){
                    $this_data['canvas_offset_y'] += $this_recovery_options['recovery_kickback']['y']; //isset($this_results['total_strikes']) ? ($this_recovery_options['recovery_kickback']['y'] * $this_results['total_strikes']) : $this_recovery_options['recovery_kickback']['y'];
                }
            }

        }

        // Either way, apply target offsets if they exist
        if (isset($options['this_ability_target']) && $options['this_ability_target'] != $this_data['robot_id_token']){
            if (!empty($this_target_options['target_kickback']['x'])
                || !empty($this_target_options['target_kickback']['y'])
                || !empty($this_target_options['target_kickback']['z'])){
                $this_data['canvas_offset_x'] += $this_target_options['target_kickback']['x'];
                $this_data['canvas_offset_y'] += $this_target_options['target_kickback']['y'];
                $this_data['canvas_offset_z'] += $this_target_options['target_kickback']['z'];
            }
        }

        // Calculate the energy bar amount and display properties
        $this_data['energy_fraction'] = $this->robot_energy.' / '.$this->robot_base_energy;
        $this_data['energy_percent'] = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        if ($this_data['energy_percent'] == 100 && $this->robot_energy < $this->robot_base_energy){ $this_data['energy_percent'] = 99; }
        // Calculate the energy bar positioning variables based on float
        if ($this_data['robot_float'] == 'left'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -3;  }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -111 + floor(111 * ($this_data['energy_percent'] / 100)) - 2;  }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -111; }
            else { $this_data['energy_x_position'] = -112; }
            if ($this_data['energy_percent'] > 0 && $this_data['energy_percent'] < 100 && $this_data['energy_x_position'] % 2 == 0){ $this_data['energy_x_position']--; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; $this_data['energy_tooltip_type'] = 'nature'; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -12; $this_data['energy_tooltip_type'] = 'electric'; }
            else { $this_data['energy_y_position'] = -24; $this_data['energy_tooltip_type'] = 'flame'; }
        }
        elseif ($this_data['robot_float'] == 'right'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -112; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(111 * ($this_data['energy_percent'] / 100)) + 2; }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
            else { $this_data['energy_x_position'] = -2; }
            if ($this_data['energy_percent'] > 0 && $this_data['energy_percent'] < 100 && $this_data['energy_x_position'] % 2 != 0){ $this_data['energy_x_position']--; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = -36; $this_data['energy_tooltip_type'] = 'nature'; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -48; $this_data['energy_tooltip_type'] = 'electric'; }
            else { $this_data['energy_y_position'] = -60; $this_data['energy_tooltip_type'] = 'flame'; }
        }

        // Calculate the weapons bar amount and display properties for both robots
        if (true){
            // Define the fraction and percent text for the weapons
            $this_data['weapons_fraction'] = $this->robot_weapons.' / '.$this->robot_base_weapons;
            $this_data['weapons_percent'] = floor(($this->robot_weapons / $this->robot_base_weapons) * 100);
            $this_data['weapons_percent_used'] = 100 - $this_data['weapons_percent'];
            // Calculate the energy bar positioning variables based on float
            if ($this_data['robot_float'] == 'left'){
                // Define the x and y position of the weapons bar background
                if ($this_data['weapons_percent'] == 100){ $this_data['weapons_x_position'] = 0; }
                elseif ($this_data['weapons_percent'] > 1){ $this_data['weapons_x_position'] = 0 - ceil(60 * ($this_data['weapons_percent_used'] / 100));  }
                elseif ($this_data['weapons_percent'] == 1){ $this_data['weapons_x_position'] = -54; }
                else { $this_data['weapons_x_position'] = -60; }
                //if ($this_data['weapons_percent'] > 0 && $this_data['weapons_percent'] < 100 && $this_data['weapons_x_position'] % 2 != 0){ $this_data['weapons_x_position']++; }
                $this_data['weapons_y_position'] = 0;
            }
            elseif ($this_data['robot_float'] == 'right'){
                // Define the x and y position of the weapons bar background
                if ($this_data['weapons_percent'] == 100){ $this_data['weapons_x_position'] = -61; }
                elseif ($this_data['weapons_percent'] > 1){ $this_data['weapons_x_position'] = -61 + ceil(60 * ($this_data['weapons_percent_used'] / 100));  }
                elseif ($this_data['weapons_percent'] == 1){ $this_data['weapons_x_position'] = -7; }
                else { $this_data['weapons_x_position'] = -1; }
                //if ($this_data['weapons_percent'] > 0 && $this_data['weapons_percent'] < 100 && $this_data['weapons_x_position'] % 2 != 0){ $this_data['weapons_x_position']++; }
                $this_data['weapons_y_position'] = -6;
            }

        }


        // Calculate the experience bar amount and display properties if a player robot
        if ($this_data['robot_float'] == 'left'){
            // Define the fraction and percent text for the experience
            if ($this->robot_level < 100){
                $required_experience = rpg_prototype::calculate_experience_required($this->robot_level);
                $this_data['experience_fraction'] = $this->robot_experience.' / '.$required_experience;
                $this_data['experience_percent'] = floor(($this->robot_experience / $required_experience) * 100);
                $this_data['experience_percent_remaining'] = 100 - $this_data['experience_percent'];
            } else {
                $this_data['experience_fraction'] = '&#8734;';
                $this_data['experience_percent'] = 100;
                $this_data['experience_percent_remaining'] = 0;
            }
            // Define the x and y position of the experience bar background
            if ($this_data['experience_percent'] == 100){ $this_data['experience_x_position'] = 0; }
            elseif ($this_data['experience_percent'] > 1){ $this_data['experience_x_position'] = 0 - ceil(60 * ($this_data['experience_percent_remaining'] / 100));  }
            elseif ($this_data['experience_percent'] == 1){ $this_data['experience_x_position'] = -54; }
            else { $this_data['experience_x_position'] = -60; }
            if ($this_data['experience_percent'] > 0 && $this_data['experience_percent'] < 100 && $this_data['experience_x_position'] % 2 != 0){ $this_data['experience_x_position']++; }
            $this_data['experience_y_position'] = 0;
        }



        // Generate the final markup for the canvas robot
        ob_start();

            // Only generate a sprite if the robot is not disabled
            if (empty($this_data['flags']['apply_disabled_state'])){

                // Define the rest of the display variables
                //$this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size_path'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $this_data['robot_markup_class'] = 'sprite ';
                //$this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
                $this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].' sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].'_'.$this_data['robot_frame'].' ';
                $this_data['robot_markup_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
                $frame_position = is_numeric($this_data['robot_frame']) ? (int)($this_data['robot_frame']) : array_search($this_data['robot_frame'], $this_data['robot_frame_index']);
                if ($frame_position === false){ $frame_position = 0; }
                $this_data['robot_markup_class'] .= $this_data['robot_frame_classes'];
                $frame_background_offset = -1 * ceil(($this_data['robot_sprite_size'] * $frame_position));
                $this_data['robot_markup_style'] = 'background-position: '.(!empty($frame_background_offset) ? $frame_background_offset.'px' : '0').' 0; ';
                $this_data['robot_markup_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['robot_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
                if ($this_data['robot_frame'] == 'damage'){
                    $temp_rotate_amount = $this_data['canvas_offset_rotate'];
                    if ($this_data['robot_direction'] == 'right'){ $temp_rotate_amount = $temp_rotate_amount * -1; }
                    $this_data['robot_markup_style'] .= 'transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); ';
                }
                //$this_data['robot_markup_style'] .= 'background-image: url('.$this_data['robot_file'].'); ';
                $this_data['robot_markup_style'] .= 'background-image: url('.$this_data['robot_file'].'); width: '.$this_data['robot_sprite_size'].'px; height: '.$this_data['robot_sprite_size'].'px; background-size: '.$this_data['robot_file_width'].'px '.$this_data['robot_file_height'].'px; ';
                $this_data['robot_markup_style'] .= $this_data['robot_frame_styles'];
                $this_data['energy_class'] = 'energy';
                $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';
                $this_data['weapons_class'] = 'weapons';
                $this_data['weapons_style'] = 'background-position: '.$this_data['weapons_x_position'].'px '.$this_data['weapons_y_position'].'px;';

                if ($this_data['robot_float'] == 'left'){

                    $this_data['experience_class'] = 'experience';
                    $this_data['experience_style'] = 'background-position: '.$this_data['experience_x_position'].'px '.$this_data['experience_y_position'].'px;';

                    $this_data['energy_title'] = $this_data['energy_fraction'].' LE'.$temp_energy_max.' | '.$this_data['energy_percent'].'%';
                    $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE'.$temp_energy_max.'';

                    $this_data['weapons_title'] = $this_data['weapons_fraction'].' WE | '.$this_data['weapons_percent'].'%';
                    $this_data['robot_title'] .= ' | '.$this_data['weapons_fraction'].' WE';

                    if ($this_data['robot_class'] == 'master'){
                        $this_data['experience_title'] = $this_data['experience_fraction'].' EXP | '.$this_data['experience_percent'].'%';
                        $this_data['robot_title'] .= ' | '.$this_data['experience_fraction'].' EXP';
                    } elseif ($this_data['robot_class'] == 'mecha'){
                        $temp_generation = '1st';
                        if (preg_match('/-2$/', $this_data['robot_token'])){ $temp_generation = '2nd'; }
                        elseif (preg_match('/-3$/', $this_data['robot_token'])){ $temp_generation = '3rd'; }
                        $this_data['experience_title'] = $temp_generation.' Gen';
                        $this_data['robot_title'] .= ' | '.$temp_generation.' Gen';
                    }

                    $this_data['robot_title'] .= ' <br />'.$this->robot_attack.' / '.$this->robot_base_attack.' AT';
                    $this_data['robot_title'] .= ' | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF';
                    $this_data['robot_title'] .= ' | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP';

                }
                elseif ($this_data['robot_float'] == 'right'){

                    $this_data['energy_title'] = $this_data['energy_percent'].'% | '.$this_data['energy_fraction'].' LE';
                    $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE';

                    $this_data['weapons_title'] = $this_data['weapons_percent'].'% | '.$this_data['weapons_fraction'].' WE';
                    $this_data['robot_title'] .= ' | '.$this_data['weapons_fraction'].' WE';

                    if ($this_data['robot_class'] == 'mecha'){
                        $temp_generation = '1st';
                        if (preg_match('/-2$/', $this_data['robot_token'])){ $temp_generation = '2nd'; }
                        elseif (preg_match('/-3$/', $this_data['robot_token'])){ $temp_generation = '3rd'; }
                        $this_data['experience_title'] = $temp_generation.' Gen';
                        $this_data['robot_title'] .= ' | '.$temp_generation.' Gen';
                    }

                    $this_data['robot_title'] .= ' <br />'.$this->robot_attack.' / '.$this->robot_base_attack.' AT';
                    $this_data['robot_title'] .= ' | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF';
                    $this_data['robot_title'] .= ' | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP';

                }

                $this_data['robot_title_plain'] = strip_tags(str_replace('<br />', '&#10;', $this_data['robot_title']));
                $this_data['robot_title_tooltip'] = htmlentities($this_data['robot_title'], ENT_QUOTES, 'UTF-8');

                // Display the robot's shadow sprite if allowed sprite
                global $flag_wap, $flag_ipad, $flag_iphone;
                if (!$flag_wap && !$flag_ipad && !$flag_iphone){
                    $shadow_offset_z = $this_data['canvas_offset_z'] - 4;
                    $shadow_scale = array(1.5, 0.25);
                    $shadow_skew = $this_data['robot_direction'] == 'right' ? 30 : -30;
                    $shadow_translate = array(
                        ceil($this_data['robot_sprite_width'] + ($this_data['robot_sprite_width'] * $shadow_scale[1]) + ($shadow_skew * $shadow_scale[1]) - (($this_data['robot_direction'] == 'right' ? 15 : 5) * $this_data['robot_scale'])),
                        ceil(($this_data['robot_sprite_height'] * $shadow_scale[0]) - (5 * $this_data['robot_scale'])),
                        );
                    //if ($this_data['robot_size_base'] >= 80 && $this_data['robot_position'] == 'active'){ $shadow_translate[0] += ceil(10 * $this_data['robot_scale']); $shadow_translate[1] += ceil(120 * $this_data['robot_scale']); }
                    $shadow_translate[0] = $shadow_translate[0] * ($this_data['robot_direction'] == 'right' ? -1 : 1);
                    $shadow_styles = 'z-index: '.$shadow_offset_z.'; transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); -webkit-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); -moz-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); ';
                    $shadow_token = 'shadow-'.$this->robot_class;
                    if ($this->robot_class == 'mecha'){ $shadow_image_token = preg_replace('/(-2|-3)$/', '', $this_data['robot_image']); }
                    elseif (strstr($this_data['robot_image'], '_')){ list($shadow_image_token) = explode('_', $this_data['robot_image']); }
                    else { $shadow_image_token = $this_data['robot_image']; }
                    //$shadow_image_token = $this->robot_class == 'mecha' ? preg_replace('/(-2|-3)$/', '', $this_data['robot_image']) : $this_data['robot_image'];
                    echo '<div data-shadowid="'.$this_data['robot_id'].
                        '" class="'.str_replace($this_data['robot_token'], $shadow_token, $this_data['robot_markup_class']).
                        '" style="'.str_replace('robots/'.$this_data['robot_image'], 'robots_shadows/'.$shadow_image_token, $this_data['robot_markup_style']).$shadow_styles.
                        '" data-key="'.$this_data['robot_key'].
                        '" data-type="'.$this_data['data_type'].'_shadow'.
                        '" data-size="'.$this_data['robot_sprite_size'].
                        '" data-direction="'.$this_data['robot_direction'].
                        '" data-frame="'.$this_data['robot_frame'].
                        '" data-position="'.$this_data['robot_position'].
                        '" data-status="'.$this_data['robot_status'].
                        '" data-scale="'.$this_data['robot_scale'].
                        '"></div>';
                }
                // Display this robot's battle sprite
                //echo '<div data-robotid="'.$this_data['robot_id'].'" class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" title="'.$this_data['robot_title_plain'].'" data-tooltip="'.$this_data['robot_title_tooltip'].'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'">'.$this_data['robot_token'].'</div>';
                echo '<div data-robotid="'.$this_data['robot_id'].'" class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'">'.$this_data['robot_token'].'</div>';
                //echo '<a class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" title="'.$this_data['robot_title'].'" data-type="robot" data-size="'.$this_data['robot_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-action="'.$this_data['robot_action'].'" data-status="'.$this_data['robot_status'].'">'.$this_data['robot_title'].'</a>';
                // If this robot has any overlays, display them too
                if (!empty($this_data['robot_image_overlay'])){
                    foreach ($this_data['robot_image_overlay'] AS $key => $overlay_token){
                        if (empty($overlay_token)){ continue; }
                        $overlay_offset_z = $this_data['canvas_offset_z'] + 2;
                        $overlay_styles = ' z-index: '.$overlay_offset_z.'; ';
                        echo '<div data-overlayid="'.$this_data['robot_id'].
                            '" class="'.str_replace($this_data['robot_token'], $overlay_token, $this_data['robot_markup_class']).
                            '" style="'.str_replace('robots/'.$this_data['robot_image'], 'robots/'.$overlay_token, $this_data['robot_markup_style']).$overlay_styles.
                            '" data-key="'.$this_data['robot_key'].
                            '" data-type="'.$this_data['data_type'].'_overlay'.
                            '" data-size="'.$this_data['robot_sprite_size'].
                            '" data-direction="'.$this_data['robot_direction'].
                            '" data-frame="'.$this_data['robot_frame'].
                            '" data-position="'.$this_data['robot_position'].
                            '" data-status="'.$this_data['robot_status'].
                            '" data-scale="'.$this_data['robot_scale'].
                            '"></div>';
                    }
                }

                // Check if his player has any other active robots
                $temp_player_active_robots = false;
                foreach ($this->player->values['robots_active'] AS $info){
                    if ($info['robot_position'] == 'active'){ $temp_player_active_robots = true; }
                }

                // Only show the robot details if active or the target of an attack
                $show_details = false;
                if (isset($options['this_ability_target']) && $options['this_ability_target'] == $this_data['robot_id_token']){ $show_details = true; }
                elseif (!isset($options['this_ability_target']) || $this_data['robot_position'] == 'active'){ $show_details = true; }
                elseif ($temp_player_active_robots == false && $this_data['robot_frame'] == 'damage'){ $show_details = true; }
                if ($show_details){

                    // Define the mugshot and detail variables for the GUI
                    $details_data = $this_data;
                    $details_data['robot_file'] = 'images/robots/'.$details_data['robot_image'].'/sprite_'.$details_data['robot_direction'].'_'.$details_data['robot_size'].'x'.$details_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                    $details_data['robot_details'] = '<div class="robot_name">'.$this->robot_name.'</div>';
                    $details_data['robot_details'] .= '<div class="robot_level robot_type robot_type_'.($this->robot_level >= 100 ? 'electric' : 'none').'">Lv. '.$this->robot_level.'</div>';
                    $details_data['robot_details'] .= '<div class="'.$details_data['energy_class'].'" style="'.$details_data['energy_style'].'" title="'.$details_data['energy_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$this_data['energy_tooltip_type'].'">'.$details_data['energy_title'].'</div>';
                    $details_data['robot_details'] .= '<div class="'.$details_data['weapons_class'].'" style="'.$details_data['weapons_style'].'" title="'.$details_data['weapons_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_weapons">'.$details_data['weapons_title'].'</div>';
                    if ($this_data['robot_float'] == 'left'){ $details_data['robot_details'] .= '<div class="'.$details_data['experience_class'].'" style="'.$details_data['experience_style'].'" title="'.$details_data['experience_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_experience">'.$details_data['experience_title'].'</div>'; }
                    $details_data['robot_details_extended'] = '';

                    /*
                    $robot_attack_markup = '<div class="robot_attack'.($this->robot_attack < 1 ? ' robot_attack_break' : ($this->robot_attack < ($this->robot_base_attack / 2) ? ' robot_attack_break_chance' : '')).'">'.str_pad($this->robot_attack, 3, '0', STR_PAD_LEFT).'</div>';
                    $robot_defense_markup = '<div class="robot_defense'.($this->robot_defense < 1 ? ' robot_defense_break' : ($this->robot_defense < ($this->robot_base_defense / 2) ? ' robot_defense_break_chance' : '')).'">'.str_pad($this->robot_defense, 3, '0', STR_PAD_LEFT).'</div>';
                    $robot_speed_markup = '<div class="robot_speed'.($this->robot_speed < 1 ? ' robot_speed_break' : ($this->robot_speed < ($this->robot_base_speed / 2) ? ' robot_speed_break_chance' : '')).'">'.str_pad($this->robot_speed, 3, '0', STR_PAD_LEFT).'</div>';
                    */

                    // Define whether or not this robot should display smalltext
                    $temp_display_smalltext = false;
                    if (strlen($this->robot_attack) > 4){ $temp_display_smalltext = true;  }
                    elseif (strlen($this->robot_defense) > 4){ $temp_display_smalltext = true;  }
                    elseif (strlen($this->robot_speed) > 4){ $temp_display_smalltext = true;  }

                    // Define attack variables and markup
                    $temp_attack_break = $this->robot_attack < 1 ? true : false;
                    $temp_attack_break_chance = $this->robot_attack < ($this->robot_base_attack / 2) ? true : false;
                    $temp_attack_percent = round(($this->robot_attack / $this->robot_base_attack) * 100);
                    if ($this_data['robot_float'] == 'left'){ $temp_attack_title = $this->robot_attack.' / '.$this->robot_base_attack.' AT'.$temp_attack_max.' | '.$temp_attack_percent.'%'.($temp_attack_break ? ' | BREAK!' : ''); }
                    elseif ($this_data['robot_float'] == 'right'){ $temp_attack_title = ($temp_attack_break ? 'BREAK! | ' : '').$temp_attack_percent.'% | '.$this->robot_attack.' / '.$this->robot_base_attack.' AT'.$temp_attack_max.''; }
                    $robot_attack_markup = '<div class="robot_attack'.($temp_attack_break ? ' robot_attack_break' : ($temp_attack_break_chance ? ' robot_attack_break_chance' : '')).($temp_display_smalltext ? ' robot_attack_smalltext' : '').'" title="'.$temp_attack_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_attack">'.$this->robot_attack.'</div>';

                    // Define attack variables and markup
                    $temp_defense_break = $this->robot_defense < 1 ? true : false;
                    $temp_defense_break_chance = $this->robot_defense < ($this->robot_base_defense / 2) ? true : false;
                    $temp_defense_percent = round(($this->robot_defense / $this->robot_base_defense) * 100);
                    if ($this_data['robot_float'] == 'left'){ $temp_defense_title = $this->robot_defense.' / '.$this->robot_base_defense.' DF'.$temp_defense_max.' | '.$temp_defense_percent.'%'.($temp_defense_break ? ' | BREAK!' : ''); }
                    elseif ($this_data['robot_float'] == 'right'){ $temp_defense_title = ($temp_defense_break ? 'BREAK! | ' : '').$temp_defense_percent.'% | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF'.$temp_defense_max.''; }
                    $robot_defense_markup = '<div class="robot_defense'.($temp_defense_break ? ' robot_defense_break' : ($temp_defense_break_chance ? ' robot_defense_break_chance' : '')).($temp_display_smalltext ? ' robot_defense_smalltext' : '').'" title="'.$temp_defense_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_defense">'.$this->robot_defense.'</div>';

                    // Define attack variables and markup
                    $temp_speed_break = $this->robot_speed < 1 ? true : false;
                    $temp_speed_break_chance = $this->robot_speed < ($this->robot_base_speed / 2) ? true : false;
                    $temp_speed_percent = round(($this->robot_speed / $this->robot_base_speed) * 100);
                    if ($this_data['robot_float'] == 'left'){ $temp_speed_title = $this->robot_speed.' / '.$this->robot_base_speed.' SP'.$temp_speed_max.' | '.$temp_speed_percent.'%'.($temp_speed_break ? ' | BREAK!' : ''); }
                    elseif ($this_data['robot_float'] == 'right'){ $temp_speed_title = ($temp_speed_break ? 'BREAK! | ' : '').$temp_speed_percent.'% | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP'.$temp_speed_max.''; }
                    $robot_speed_markup = '<div class="robot_speed'.($temp_speed_break ? ' robot_speed_break' : ($temp_speed_break_chance ? ' robot_speed_break_chance' : '')).($temp_display_smalltext ? ' robot_speed_smalltext' : '').'" title="'.$temp_speed_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_speed">'.$this->robot_speed.'</div>';

                    // Add these markup variables to the details string
                    if ($details_data['robot_float'] == 'left'){
                        $details_data['robot_details'] .= $robot_attack_markup;
                        $details_data['robot_details'] .= $robot_defense_markup;
                        $details_data['robot_details'] .= $robot_speed_markup;
                    } else {
                        $details_data['robot_details'] .= $robot_speed_markup;
                        $details_data['robot_details'] .= $robot_defense_markup;
                        $details_data['robot_details'] .= $robot_attack_markup;
                    }

                    // If this robot is holding an item, add it to the display
                    if (!empty($this->robot_item)){
                        $temp_item_info = rpg_ability::get_index_info($this->robot_item);
                        $details_data['item_title'] = $temp_item_info['ability_name'];
                        $details_data['item_type'] = !empty($temp_item_info['ability_type']) ? $temp_item_info['ability_type'] : 'none';
                        $details_data['item_type2'] = !empty($temp_item_info['ability_type2']) ? $temp_item_info['ability_type2'] : '';
                        $details_data['item_title_type'] = $details_data['item_type'];
                        $details_data['item_file'] = 'images/abilities/'.$this->robot_item.'/icon_'.$details_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                        $details_data['item_class'] = 'sprite size40 mugshot '.$details_data['robot_float'].' ';
                        $details_data['item_style'] = 'background-image: url('.$details_data['item_file'].'); ';
                        if (!empty($details_data['item_type2'])){
                            if ($details_data['item_title_type'] != 'none'){ $details_data['item_title_type'] .= ' '.$details_data['item_type2']; }
                            else { $details_data['item_title_type'] = $details_data['item_type2']; }
                        }
                        $item_markup = '<div class="robot_item">';
                            $item_markup .= '<div class="wrap type '.$details_data['item_title_type'].'">';
                                $item_markup .= '<div class="'.$details_data['item_class'].'" style="'.$details_data['item_style'].'" title="'.$details_data['item_title'].'" data-tooltip-type="type '.$details_data['item_title_type'].'">&nbsp;</div>';
                            $item_markup .= '</div>';
                        $item_markup .= '</div>';
                        $details_data['robot_details'] .= $item_markup;
                    }

                    $details_data['mugshot_file'] = 'images/robots/'.$details_data['robot_image'].'/mug_'.$details_data['robot_direction'].'_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                    $details_data['mugshot_class'] = 'sprite details robot_mugshot ';
                    $details_data['mugshot_class'] .= 'sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot sprite_mugshot_'.$details_data['robot_float'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot_'.$details_data['robot_float'].' ';
                    $details_data['mugshot_class'] .= 'robot_status_'.$details_data['robot_status'].' robot_position_'.$details_data['robot_position'].' ';
                    $details_data['mugshot_style'] = 'z-index: 9100; ';
                    $details_data['mugshot_style'] .= 'background-image: url('.$details_data['mugshot_file'].'); ';

                    // Display the robot's mugshot sprite and detail fields
                    echo '<div data-detailsid="'.$this_data['robot_id'].'" class="sprite details robot_details robot_details_'.$details_data['robot_float'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').'><div class="container">'.$details_data['robot_details'].'</div></div>';
                    if (!empty($details_data['robot_details_extended'])){ echo '<div data-detailsid="'.$this_data['robot_id'].'" class="sprite details robot_details_extended robot_details_extended_'.$details_data['robot_float'].' '.$extended_class.'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').'><div class="container">'.$details_data['robot_details_extended'].'</div></div>'; }
                    echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.str_replace('80x80', '40x40', $details_data['mugshot_class']).' robot_mugshot_type robot_type robot_type_'.$this_data['robot_core'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').' data-tooltip="'.$details_data['robot_title_tooltip'].'"><div class="sprite">&nbsp;</div></div>';
                    //echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'" title="'.$details_data['robot_title_plain'].'" data-tooltip="'.$details_data['robot_title_tooltip'].'">'.$details_data['robot_token'].'</div>';
                    echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'">'.$details_data['robot_token'].'</div>';

                    // Update the main data array with this markup
                    $this_data['details'] = $details_data;
                }


            }

        // Collect the generated robot markup
        $this_data['robot_markup'] = trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;
    }

    // Define a function for generating robot console variables
    public function get_console_markup($options, $player_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this robot
        $this_data['robot_frame'] = !empty($this->robot_frame) ? $this->robot_frame : 'base';
        $this_data['robot_key'] = !empty($this->robot_key) ? $this->robot_key : 0;
        $this_data['robot_title'] = $this->robot_name;
        $this_data['robot_token'] = $this->robot_token;
        $this_data['robot_image'] = $this->robot_image;
        $this_data['robot_float'] = $this->player->player_side;
        $this_data['robot_direction'] = $this->player->player_side == 'left' ? 'right' : 'left';
        $this_data['robot_status'] = $this->robot_status;
        $this_data['robot_position'] = !empty($this->robot_position) ? $this->robot_position : 'bench';
        $this_data['image_type'] = !empty($options['this_robot_image']) ? $options['this_robot_image'] : 'sprite';

        // Calculate the energy bar amount and display properties
        $this_data['energy_fraction'] = $this->robot_energy.' / '.$this->robot_base_energy;
        $this_data['energy_percent'] = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        // Calculate the energy bar positioning variables based on float
        if ($this_data['robot_float'] == 'left'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -82; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -119 + floor(37 * ($this_data['energy_percent'] / 100));  }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -119; }
            else { $this_data['energy_x_position'] = -120; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5;}
            else { $this_data['energy_y_position'] = -10; }
        }
        elseif ($this_data['robot_float'] == 'right'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -40; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(37 * ($this_data['energy_percent'] / 100)); }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
            else { $this_data['energy_x_position'] = -2; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5; }
            else { $this_data['energy_y_position'] = -10; }
        }

        // Calculate the weapons bar amount and display properties for both robots
        if (true){
            // Define the fraction and percent text for the weapons
            $this_data['weapons_fraction'] = $this->robot_weapons.' / '.$this->robot_base_weapons;
            $this_data['weapons_percent'] = floor(($this->robot_weapons / $this->robot_base_weapons) * 100);
        }

        // Calculate the experience bar amount and display properties if a player robot
        if ($this_data['robot_float'] == 'left'){
            // Define the fraction and percent text for the experience
            if ($this->robot_level < 100){
                $required_experience = rpg_prototype::calculate_experience_required($this->robot_level);
                $this_data['experience_fraction'] = $this->robot_experience.' / '.$required_experience;
                $this_data['experience_percent'] = floor(($this->robot_experience / $required_experience) * 100);
            } else {
                $this_data['experience_fraction'] = '&#8734;';
                $this_data['experience_percent'] = 100;
            }
        }

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['robot_float'];
        $this_data['container_style'] = '';
        //$this_data['robot_class'] = 'sprite sprite_robot_'.$this_data['robot_status'];
        $this_data['robot_class'] = 'sprite sprite_robot sprite_robot_'.$this_data['image_type'].' ';
        $this_data['robot_style'] = '';
        $this_data['robot_size'] = $this->robot_image_size;
        $this_data['robot_image'] = 'images/robots/'.$this_data['robot_image'].'/'.$this_data['image_type'].'_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
        $this_data['robot_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
        if ($this_data['image_type'] == 'mug'){ $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_mugshot '; }
        $this_data['robot_style'] .= 'background-image: url('.$this_data['robot_image'].'); ';
        $this_data['energy_title'] = $this_data['energy_fraction'].' LE ('.$this_data['energy_percent'].'%)';
        $this_data['robot_title'] .= ' <br />'.$this_data['energy_title'];
        $this_data['weapons_title'] = $this_data['weapons_fraction'].' WE ('.$this_data['weapons_percent'].'%)';
        $this_data['robot_title'] .= ' <br />'.$this_data['weapons_title'];
        if ($this_data['robot_float'] == 'left'){
            $this_data['experience_title'] = $this_data['experience_fraction'].' EXP ('.$this_data['experience_percent'].'%)';
            $this_data['robot_title'] .= ' <br />'.$this_data['experience_title'];
        }
        $this_data['energy_class'] = 'energy';
        $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';

        // Generate the final markup for the console robot
        $this_data['robot_markup'] = '';
        $this_data['robot_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['robot_markup'] .= '<div class="'.$this_data['robot_class'].'" style="'.$this_data['robot_style'].'" title="'.$this_data['robot_title'].'">'.$this_data['robot_title'].'</div>';
        if ($this_data['image_type'] != 'mug'){ $this_data['robot_markup'] .= '<div class="'.$this_data['energy_class'].'" style="'.$this_data['energy_style'].'" title="'.$this_data['energy_title'].'">'.$this_data['energy_title'].'</div>'; }
        $this_data['robot_markup'] .= '</div>';

        // Return the robot console data
        return $this_data;
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
        $temp_field_names = array('robot_field2', 'robot_weaknesses', 'robot_resistances', 'robot_affinities', 'robot_immunities', 'robot_image_alts');
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

        // Calculate this robot's count variables
        $this->counters['abilities_total'] = count($this->robot_abilities);

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

        // Calculate this robot's stat totals
        $this->robot_total = ($this->robot_energy + $this->robot_attack + $this->robot_defense + $this->robot_speed);
        $this->robot_base_total = ($this->robot_base_energy + $this->robot_base_attack + $this->robot_base_defense + $this->robot_base_speed);

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
        $this_data['robot_abilities'] = $this_data['robot_base_abilities'];
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
            'robot_base_item' => $this->robot_base_item,
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
            'robot_base_weaknesses' => $this->robot_base_weaknesses,
            'robot_base_resistances' => $this->robot_base_resistances,
            'robot_base_affinities' => $this->robot_base_affinities,
            'robot_base_immunities' => $this->robot_base_immunities,
            'robot_base_abilities' => $this->robot_base_abilities,
            'robot_base_attachments' => $this->robot_base_attachments,
            'robot_base_quotes' => $this->robot_base_quotes,
            'robot_status' => $this->robot_status,
            'robot_side' => $this->robot_side,
            'robot_direction' => $this->robot_direction,
            'robot_position' => $this->robot_position,
            'robot_stance' => $this->robot_stance,
            'robot_frame' => $this->robot_frame,
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


    // -- END-OF-TURN CHECK FUNCTIONS -- //

    // Define a function for checking attachment status
    public function check_attachments(rpg_player $target_player, rpg_robot $target_robot){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

        // Collect references to relative player and robot objects
        $this_player = $this->player;
        $this_robot = $this;

        // Hide any disabled robots and return
        if ($this_robot->get_status() == 'disabled'){
            $this_robot->set_flag('apply_disabled_state', true);
            $this_battle->events_create();
            return;
        }

        // If this robot has any attachments, loop through them
        if ($this_robot->has_attachments()){
            $attachment_action_flag = false;
            $attachment_key = 0;
            $robot_attachments = $this_robot->get_attachments();
            foreach ($robot_attachments AS $attachment_token => $attachment_info){

                $attachment_debug_token = str_replace('ability_', '', $attachment_token);
                $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' checkpoint has attachment '.$attachment_debug_token);

                // Load the attachment if it doesn't exist yet then collect a reference
                if (!$this_battle->attachment_exists($attachment_info['ability_id'])){ $this_battle->add_attachment($this_player, $this_robot, $attachment_info); }
                else { $this_battle->update_attachment($attachment_info['ability_id'], $attachment_info); }
                $this_attachment = $this_battle->get_attachment($attachment_info['ability_id']);

                // ATTACHMENT DURATION
                // If this attachment has DURATION counter
                if (isset($attachment_info['attachment_duration'])){
                    $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' has duration '.$attachment_info['attachment_duration']);

                    // DURATION COUNT -1
                    // If the duration is not empty, decrement it and continue
                    if ($attachment_info['attachment_duration'] > 0){

                        $attachment_info['attachment_duration'] = $attachment_info['attachment_duration'] - 1;
                        $this_robot->set_attachment($attachment_token, $attachment_info);
                        $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' attachment '.$attachment_debug_token.' duration decreased to '.$attachment_info['attachment_duration']);

                    }
                    // DURATION EXPIRED
                    // Otherwise, trigger the destroy action for this attachment
                    else {

                        // Remove this attachment and inflict damage on the robot
                        $this_robot->unset_attachment($attachment_token);

                        // ATTACHMENT DESTROY
                        if ($attachment_info['attachment_destroy'] !== false){

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
                                $this_robot->trigger_disabled($target_robot, $this_attachment);
                                // If this the player's last robot
                                $active_robots = $this_player->get_robots_active();
                                if (empty($active_robots)){
                                    // Trigger the battle complete event
                                    $this_battle->trigger_complete($target_player, $target_robot, $this_player, $this_robot);
                                    $attachment_action_flag = true;
                                }
                            }

                        }
                    }

                }

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
                            $attachment_info['attachment_repeat']['success'] = array(9, -10, -10, -10, 'The '.$this_attachment->print_name().' '.$temp_damage_words['action'].' '.$this_robot->print_name().'&#39;s '.$temp_damage_words['object'].' systems!');
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
                            $attachment_info['attachment_repeat']['success'] = array(9, -10, -10, -10, 'The '.$this_attachment->print_name().' '.$temp_recovery_words['action'].' '.$this_robot->print_name().'&#39;s '.$temp_recovery_words['object'].' systems!');
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
                            $this_robot->trigger_damage($this_robot, $this_attachment, 0, false, $temp_trigger_options);
                            $attachment_action_flag = true;

                        }

                        // If the temp robot was disabled, trigger the event
                        if ($temp_stat_amount < 1){
                            $this_robot->trigger_disabled($target_robot, $this_attachment);
                            // If this the player's last robot
                            if ($this_player->counters['robots_active'] < 1){
                                // Trigger the battle complete event
                                $this_battle->trigger_complete($target_player, $target_robot, $this_player, $this_robot);
                                $attachment_action_flag = true;
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
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' checkpoint has item '.str_replace('item-', '', $this_robot->get_item()));

            // Define the item info based on token
            $item_id = $this_robot->get_item_id();
            $item_token = $this_robot->get_item();
            $item_info = array('ability_id' => $item_id, 'ability_token' => $item_token);

            // Load the item if it doesn't exist yet then collect a reference
            if (!$this_battle->item_exists($item_info['ability_id'])){ $this_battle->add_item($this_player, $this_robot, $item_info); }
            else { $this_battle->update_item($item_info['ability_id'], $item_info); }
            $this_item = $this_battle->get_item($item_info['ability_id']);

            // If the robot is holding a Field Booster item, increase multiplier
            if ($item_token == 'item-field-booster'){

                // Define the item object and trigger info
                $temp_core_type = $this_robot->get_core();
                $temp_field_type = $this_field->get_type();
                if (empty($temp_core_type)){ $temp_boost_type = 'recovery'; }
                elseif ($temp_core_type == 'empty'){ $temp_boost_type = 'damage'; }
                else { $temp_boost_type = $temp_core_type; }
                if (!isset($this_field->field_multipliers[$temp_boost_type]) || $this_field->field_multipliers[$temp_boost_type] < MMRPG_SETTINGS_MULTIPLIER_MAX){

                    // Define this ability's attachment token
                    $this_star_index = rpg_prototype::star_image(!empty($temp_boost_type) ? $temp_boost_type : 'none');
                    $this_sprite_sheet = 'field-support';
                    $this_attachment_token = 'item_field-booster';
                    $this_attachment_info = array(
                        'class' => 'ability',
                        'ability_id' => $item_id,
                        'ability_token' => $item_token,
                        'ability_image' => $this_sprite_sheet.($this_star_index['sheet'] > 1 ? '-'.$this_star_index['sheet'] : ''),
                        'ability_frame' => $this_star_index['frame'],
                        'ability_frame_animate' => array($this_star_index['frame']),
                        'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
                        );

                    // Attach this ability attachment to this robot temporarily
                    $this_robot->set_frame('taunt');
                    $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

                    // Create or increase the elemental booster for this field
                    $temp_change_percent = round($this_item->get_recovery2() / 100, 1);
                    $new_multiplier_value = $this_field->get_multiplier($temp_boost_type) + $temp_change_percent;
                    if ($new_multiplier_value >= MMRPG_SETTINGS_MULTIPLIER_MAX){
                        $temp_change_percent = $new_multiplier_value - MMRPG_SETTINGS_MULTIPLIER_MAX;
                        $new_multiplier_value = MMRPG_SETTINGS_MULTIPLIER_MAX;
                    }
                    $this_field->set_multiplier($temp_boost_type, $new_multiplier_value);

                    // Create the event to show this element boost
                    if ($temp_change_percent > 0){
                        $this_battle->events_create($this_robot, false, $this_field->field_name.' Multipliers',
                            rpg_functions::get_random_positive_word().' <span class="ability_name ability_type ability_type_'.$temp_boost_type.'">'.ucfirst($temp_boost_type).' Effects</span> were boosted by '.ceil($temp_change_percent * 100).'%!<br />'.
                            'The multiplier is now at <span class="ability_name ability_type ability_type_'.$temp_boost_type.'">'.ucfirst($temp_boost_type).' x '.number_format($new_multiplier_value, 1).'</span>!',
                            array('canvas_show_this_ability_overlay' => true)
                            );
                    }

                    // Remove this ability attachment from this robot
                    $this_robot->unset_attachment($this_attachment_token);

                }

            }
            // Else the robot is holding an Attack Booster item, apply boosts
            elseif ($item_token == 'item-attack-booster'){

                // Define the item object and trigger info
                $temp_recovery_amount = round($this_robot->get_base_attack() * ($this_item->get_recovery2() / 100));
                $this_item->recovery_options_update(array(
                    'kind' => 'attack',
                    'frame' => 'taunt',
                    'percent' => true,
                    'modifiers' => false,
                    'kickback' => array(0, 0, 0),
                    'success' => array(9, -10, -10, -10, 'The '.$this_item->print_name().' improved '.$this_robot->print_name().'&#39;s weapon systems!'),
                    'failure' => array(9, -10, -10, -10, '')
                    ));

                // Trigger stat recovery for the holding robot
                if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

            }
            // Else if the robot is holding an Defense Booster item, apply boosts
            elseif ($item_token == 'item-defense-booster'){

                // Define the item object and trigger info
                $temp_recovery_amount = round($this_robot->get_base_defense() * ($this_item->get_recovery2() / 100));
                $this_item->recovery_options_update(array(
                    'kind' => 'defense',
                    'frame' => 'taunt',
                    'percent' => true,
                    'modifiers' => false,
                    'kickback' => array(0, 0, 0),
                    'success' => array(9, -10, -10, -10, 'The '.$this_item->print_name().' improved '.$this_robot->print_name().'&#39;s shield systems!'),
                    'failure' => array(9, -10, -10, -10, '')
                    ));

                // Trigger stat recovery for the holding robot
                if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

            }
            // Else if the robot is holding an Defense Booster item, apply boosts
            elseif ($item_token == 'item-speed-booster'){

                // Define the item object and trigger info
                $temp_recovery_amount = round($this_robot->get_base_speed() * ($this_item->get_recovery2() / 100));
                $this_item->recovery_options_update(array(
                    'kind' => 'speed',
                    'frame' => 'taunt',
                    'percent' => true,
                    'modifiers' => false,
                    'kickback' => array(0, 0, 0),
                    'success' => array(9, -10, -10, -10, 'The '.$this_item->print_name().' improved '.$this_robot->print_name().'&#39;s mobility systems!'),
                    'failure' => array(9, -10, -10, -10, '')
                    ));

                // Trigger stat recovery for the holding robot
                if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); }

            }

        }

    }

    // Define a function for checking weapons status
    public function check_weapons(rpg_player $target_player, rpg_robot $target_robot, $regen_weapons = true){

        // Collect references to global objects
        $db = cms_database::get_database();
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();

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

    }

}
?>
