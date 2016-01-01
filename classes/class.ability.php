<?php
/**
 * Mega Man RPG Ability
 * <p>The object class for all abilities in the Mega Man RPG Prototype.</p>
 */
class rpg_ability extends rpg_object {

    // Define public robot variables
    public $battle = null;
    public $battle_id = 0;
    public $battle_token = '';
    public $field = null;
    public $field_id = 0;
    public $field_token = '';
    public $ability_id = 0;
    public $ability_key = 0;
    public $ability_name = '';
    public $ability_token = '';
    public $ability_description = '';
    public $ability_class = '';
    public $ability_subclass = '';
    public $ability_master = '';
    public $ability_number = '';
    public $ability_type = '';
    public $ability_type2 = '';
    public $ability_speed = 0;
    public $ability_energy = 0;
    public $ability_energy_percent = false;
    public $ability_damage  = 0;
    public $ability_damage2 = 0;
    public $ability_damage_percent = false;
    public $ability_damage2_percent = false;
    public $ability_recovery = 0;
    public $ability_recovery2 = 0;
    public $ability_recovery_percent = false;
    public $ability_recovery2_percent = false;
    public $ability_accuracy = 0;
    public $ability_target = '';
    public $ability_functions = '';
    public $ability_image = '';
    public $ability_image_size = 0;
    public $ability_frame = '';
    public $ability_frame_span = 0;
    public $ability_frame_animate = array();
    public $ability_frame_index = array();
    public $ability_frame_offset = array();
    public $ability_frame_styles = '';
    public $ability_frame_classes = '';
    public $ability_results = array();
    public $ability_options = array();
    public $target_options = array();
    public $damage_options = array();
    public $recovery_options = array();
    public $attachment_options = array();
    public $ability_function = null;
    public $ability_function_onload = null;
    public $ability_function_attachment = null;
    public $ability_base_key = 0;
    public $ability_base_name = '';
    public $ability_base_token = '';
    public $ability_base_description = '';
    public $ability_base_image = '';
    public $ability_base_image_size = 0;
    public $ability_base_type = '';
    public $ability_base_type2 = '';
    public $ability_base_energy = 0;
    public $ability_base_speed = 0;
    public $ability_base_damage = 0;
    public $ability_base_damage_percent = false;
    public $ability_base_damage2 = 0;
    public $ability_base_damage2_percent = false;
    public $ability_base_recovery = 0;
    public $ability_base_recovery_percent = false;
    public $ability_base_recovery2 = 0;
    public $ability_base_recovery2_percent = false;
    public $ability_base_accuracy = 0;
    public $ability_base_target = '';

    /**
     * Create a new RPG ability object
     * @param rpg_player $this_player
     * @param rpg_robot $this_robot
     * @param array $ability_info (optional)
     * @return rpg_ability
     */
    public function rpg_ability(rpg_player $this_player, rpg_robot $this_robot, $ability_info = array()){

        // Update the session keys for this object
        $this->session_key = 'ABILITIES';
        $this->session_token = 'ability_token';
        $this->session_id = 'ability_id';
        $this->class = 'ability';

        // Collect any provided arguments
        $args = func_get_args();

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

        // Define the internal robot values using the provided array
        $this->robot = $this_robot;
        $this->robot_id = $this_robot->robot_id;
        $this->robot_token = $this_robot->robot_token;

        // Collect current ability data from the function if available
        $ability_info = !empty($ability_info) ? $ability_info : array('ability_id' => 0, 'ability_token' => 'ability');
        // Load the ability data based on the ID and fallback token
        $ability_info = $this->ability_load($ability_info['ability_id'], $ability_info['ability_token'], $ability_info);

        // Now load the ability data from the session or index
        if (empty($ability_info)){
            // Ability data could not be loaded
            die('Ability data could not be loaded :<br />$ability_info = <pre>'.print_r($ability_info, true).'</pre>');
            return false;
        }

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    /**
     * Generate an ability ID based on the robot owner and the ability slot
     * @param int $robot_id
     * @param int $ability_slot (optional)
     * @return int
     */
    public static function generate_id($robot_id, $ability_slot = 0){
        $ability_id = $robot_id.str_pad(($ability_slot + 1), 3, '0', STR_PAD_LEFT);
        return $ability_id;
    }

    // Define a public function for manually loading data
    public function ability_load($ability_id = 0, $ability_token = 'ability', $custom_info = array()){

        /*
        // If this is a special system ability, hard-code its ID, otherwise base off robot
        $temp_system_abilities = array('attachment-defeat');
        if (in_array($ability_token, $temp_system_abilities)){
            $ability_id = $this->player_id.player_id.str_pad(array_search($ability_token, $temp_system_abilities), 3, '0', STR_PAD_LEFT);
        }
        // Else if this is an item, tweak it's ID as well
        elseif (in_array($ability_token, $this->player->player_items)){
            $ability_id = $this->player_id.str_pad(array_search($ability_token, $this->player->player_items), 3, '0', STR_PAD_LEFT);
        }
        // Else if this was any other ability, combine ID with robot owner
        else {
            $ability_id = $this->robot_id.str_pad($ability_id, 3, '0', STR_PAD_LEFT);
        }
        */

        // If the ability token was not provided, return false
        if (!isset($ability_token)){
            die("ability token must be set!\n\$this_abilityinfo\n".print_r($this_abilityinfo, true));
            return false;
        }

        // Collect current ability data from the session if available
        if (isset($_SESSION['ABILITIES'][$ability_id])){
            $this_abilityinfo = $_SESSION['ABILITIES'][$ability_id];
            if ($this_abilityinfo['ability_token'] != $ability_token){
                die("ability token and ID mismatch {$ability_id}:{$ability_token}!\n");
                return false;
            }
        }
        // Otherwise, collect ability data from the index
        else {
            $this_abilityinfo = self::get_index_info($ability_token);
            if (empty($this_abilityinfo)){
                die("ability data could not be loaded for {$ability_id}:{$ability_token}!\n");
                return false;
            }
        }

        // If the custom data was not empty, merge now
        if (!empty($custom_info)){ $this_abilityinfo = array_merge($this_abilityinfo, $custom_info); }

        // Define the internal ability values using the provided array
        $this->flags = isset($this_abilityinfo['flags']) ? $this_abilityinfo['flags'] : array();
        $this->counters = isset($this_abilityinfo['counters']) ? $this_abilityinfo['counters'] : array();
        $this->values = isset($this_abilityinfo['values']) ? $this_abilityinfo['values'] : array();
        $this->history = isset($this_abilityinfo['history']) ? $this_abilityinfo['history'] : array();
        $this->ability_id = isset($this_abilityinfo['ability_id']) ? $this_abilityinfo['ability_id'] : $ability_id;
        $this->ability_key = isset($this_abilityinfo['ability_key']) ? $this_abilityinfo['ability_key'] : 0;
        $this->ability_name = isset($this_abilityinfo['ability_name']) ? $this_abilityinfo['ability_name'] : 'Ability';
        $this->ability_token = isset($this_abilityinfo['ability_token']) ? $this_abilityinfo['ability_token'] : 'ability';
        $this->ability_description = isset($this_abilityinfo['ability_description']) ? $this_abilityinfo['ability_description'] : '';
        $this->ability_class = isset($this_abilityinfo['ability_class']) ? $this_abilityinfo['ability_class'] : 'master';
        $this->ability_subclass = isset($this_abilityinfo['ability_subclass']) ? $this_abilityinfo['ability_subclass'] : '';
        $this->ability_master = isset($this_abilityinfo['ability_master']) ? $this_abilityinfo['ability_master'] : '';
        $this->ability_number = isset($this_abilityinfo['ability_number']) ? $this_abilityinfo['ability_number'] : '';
        $this->ability_type = isset($this_abilityinfo['ability_type']) ? $this_abilityinfo['ability_type'] : '';
        $this->ability_type2 = isset($this_abilityinfo['ability_type2']) ? $this_abilityinfo['ability_type2'] : '';
        $this->ability_speed = isset($this_abilityinfo['ability_speed']) ? $this_abilityinfo['ability_speed'] : 1;
        $this->ability_energy = isset($this_abilityinfo['ability_energy']) ? $this_abilityinfo['ability_energy'] : 4;
        $this->ability_energy_percent = isset($this_abilityinfo['ability_energy_percent']) ? $this_abilityinfo['ability_energy_percent'] : true;
        $this->ability_damage = isset($this_abilityinfo['ability_damage']) ? $this_abilityinfo['ability_damage'] : 0;
        $this->ability_damage2 = isset($this_abilityinfo['ability_damage2']) ? $this_abilityinfo['ability_damage2'] : 0;
        $this->ability_damage_percent = isset($this_abilityinfo['ability_damage_percent']) ? $this_abilityinfo['ability_damage_percent'] : false;
        $this->ability_damage2_percent = isset($this_abilityinfo['ability_damage2_percent']) ? $this_abilityinfo['ability_damage2_percent'] : false;
        $this->ability_recovery = isset($this_abilityinfo['ability_recovery']) ? $this_abilityinfo['ability_recovery'] : 0;
        $this->ability_recovery2 = isset($this_abilityinfo['ability_recovery2']) ? $this_abilityinfo['ability_recovery2'] : 0;
        $this->ability_recovery_percent = isset($this_abilityinfo['ability_recovery_percent']) ? $this_abilityinfo['ability_recovery_percent'] : false;
        $this->ability_recovery2_percent = isset($this_abilityinfo['ability_recovery2_percent']) ? $this_abilityinfo['ability_recovery2_percent'] : false;
        $this->ability_accuracy = isset($this_abilityinfo['ability_accuracy']) ? $this_abilityinfo['ability_accuracy'] : 0;
        $this->ability_target = isset($this_abilityinfo['ability_target']) ? $this_abilityinfo['ability_target'] : 'auto';
        $this->ability_functions = isset($this_abilityinfo['ability_functions']) ? $this_abilityinfo['ability_functions'] : 'abilities/ability.php';
        $this->ability_image = isset($this_abilityinfo['ability_image']) ? $this_abilityinfo['ability_image'] : $this->ability_token;
        $this->ability_image_size = isset($this_abilityinfo['ability_image_size']) ? $this_abilityinfo['ability_image_size'] : 40;
        $this->ability_frame = isset($this_abilityinfo['ability_frame']) ? $this_abilityinfo['ability_frame'] : 'base';
        $this->ability_frame_span = isset($this_abilityinfo['ability_frame_span']) ? $this_abilityinfo['ability_frame_span'] : 1;
        $this->ability_frame_animate = isset($this_abilityinfo['ability_frame_animate']) ? $this_abilityinfo['ability_frame_animate'] : array($this->ability_frame);
        $this->ability_frame_index = isset($this_abilityinfo['ability_frame_index']) ? $this_abilityinfo['ability_frame_index'] : array('base');
        $this->ability_frame_offset = isset($this_abilityinfo['ability_frame_offset']) ? $this_abilityinfo['ability_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 1);
        $this->ability_frame_styles = isset($this_abilityinfo['ability_frame_styles']) ? $this_abilityinfo['ability_frame_styles'] : '';
        $this->ability_frame_classes = isset($this_abilityinfo['ability_frame_classes']) ? $this_abilityinfo['ability_frame_classes'] : '';
        $this->ability_results = array();
        $this->ability_options = array();
        $this->target_options = array();
        $this->damage_options = array();
        $this->recovery_options = array();
        $this->attachment_options = array();

        // Collect any functions associated with this ability
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->ability_functions) ? $this->ability_functions : 'abilities/ability.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->ability_function = isset($ability['ability_function']) ? $ability['ability_function'] : function(){};
        $this->ability_function_onload = isset($ability['ability_function_onload']) ? $ability['ability_function_onload'] : function(){};
        $this->ability_function_attachment = isset($ability['ability_function_attachment']) ? $ability['ability_function_attachment'] : function(){};
        unset($ability);

        // Define the internal robot base values using the robots index array
        $this->ability_base_key = isset($this_abilityinfo['ability_base_key']) ? $this_abilityinfo['ability_base_key'] : $this->ability_key;
        $this->ability_base_name = isset($this_abilityinfo['ability_base_name']) ? $this_abilityinfo['ability_base_name'] : $this->ability_name;
        $this->ability_base_token = isset($this_abilityinfo['ability_base_token']) ? $this_abilityinfo['ability_base_token'] : $this->ability_token;
        $this->ability_base_description = isset($this_abilityinfo['ability_base_description']) ? $this_abilityinfo['ability_base_description'] : $this->ability_description;
        $this->ability_base_image = isset($this_abilityinfo['ability_base_image']) ? $this_abilityinfo['ability_base_image'] : $this->ability_image;
        $this->ability_base_image_size = isset($this_abilityinfo['ability_base_image_size']) ? $this_abilityinfo['ability_base_image_size'] : $this->ability_image_size;
        $this->ability_base_type = isset($this_abilityinfo['ability_base_type']) ? $this_abilityinfo['ability_base_type'] : $this->ability_type;
        $this->ability_base_type2 = isset($this_abilityinfo['ability_base_type2']) ? $this_abilityinfo['ability_base_type2'] : $this->ability_type2;
        $this->ability_base_energy = isset($this_abilityinfo['ability_base_energy']) ? $this_abilityinfo['ability_base_energy'] : $this->ability_energy;
        $this->ability_base_speed = isset($this_abilityinfo['ability_base_speed']) ? $this_abilityinfo['ability_base_speed'] : $this->ability_speed;
        $this->ability_base_damage = isset($this_abilityinfo['ability_base_damage']) ? $this_abilityinfo['ability_base_damage'] : $this->ability_damage;
        $this->ability_base_damage_percent = isset($this_abilityinfo['ability_base_damage_percent']) ? $this_abilityinfo['ability_base_damage_percent'] : $this->ability_damage_percent;
        $this->ability_base_damage2 = isset($this_abilityinfo['ability_base_damage2']) ? $this_abilityinfo['ability_base_damage2'] : $this->ability_damage2;
        $this->ability_base_damage2_percent = isset($this_abilityinfo['ability_base_damage2_percent']) ? $this_abilityinfo['ability_base_damage2_percent'] : $this->ability_damage2_percent;
        $this->ability_base_recovery = isset($this_abilityinfo['ability_base_recovery']) ? $this_abilityinfo['ability_base_recovery'] : $this->ability_recovery;
        $this->ability_base_recovery_percent = isset($this_abilityinfo['ability_base_recovery_percent']) ? $this_abilityinfo['ability_base_recovery_percent'] : $this->ability_recovery_percent;
        $this->ability_base_recovery2 = isset($this_abilityinfo['ability_base_recovery2']) ? $this_abilityinfo['ability_base_recovery2'] : $this->ability_recovery2;
        $this->ability_base_recovery2_percent = isset($this_abilityinfo['ability_base_recovery2_percent']) ? $this_abilityinfo['ability_base_recovery2_percent'] : $this->ability_recovery2_percent;
        $this->ability_base_accuracy = isset($this_abilityinfo['ability_base_accuracy']) ? $this_abilityinfo['ability_base_accuracy'] : $this->ability_accuracy;
        $this->ability_base_target = isset($this_abilityinfo['ability_base_target']) ? $this_abilityinfo['ability_base_target'] : $this->ability_target;

        // Define a the default ability results
        $this->ability_results_reset();

        // Reset the ability options to default
        $this->target_options_reset();
        $this->damage_options_reset();
        $this->recovery_options_reset();

        // Trigger the onload function if it exists
        $this_battle = rpg_battle::get_battle();
        $this_field = rpg_field::get_field();
        $this_player = $this->player;
        $this_robot = $this->robot;
        $target_side = $this_player->player_side != 'right' ? 'right' : 'left';
        $target_player = $this_battle->find_player(array('player_side' => $target_side));
        $target_robot = $this_battle->find_robot(array('robot_side' => $target_side, 'robot_position' => 'active'));
        $temp_function = $this->ability_function_onload;
        $temp_result = $temp_function(array(
            'this_battle' => $this_battle,
            'this_field' => $this_field,
            'this_player' => $this_player,
            'this_robot' => $this_robot,
            'target_player' => $target_player,
            'target_robot' => $target_robot,
            'this_ability' => $this
            ));

        // Return true on success
        return true;

    }

    // Define alias functions for updating specific fields quickly

    public function get_id(){ return intval($this->get_info('ability_id')); }
    public function set_id($value){ $this->set_info('ability_id', intval($value)); }

    public function get_name(){ return $this->get_info('ability_name'); }
    public function set_name($value){ $this->set_info('ability_name', $value); }
    public function get_base_name(){ return $this->get_info('ability_base_name'); }
    public function set_base_name($value){ $this->set_info('ability_base_name', $value); }
    public function reset_name(){ $this->set_info('ability_name', $this->get_info('ability_base_name')); }

    public function get_token(){ return $this->get_info('ability_token'); }
    public function set_token($value){ $this->set_info('ability_token', $value); }

    public function get_description(){ return $this->get_info('ability_description'); }
    public function set_description($value){ $this->set_info('ability_description', $value); }
    public function get_base_description(){ return $this->get_info('ability_base_description'); }
    public function set_base_description($value){ $this->set_info('ability_base_description', $value); }

    public function get_class(){ return $this->get_info('ability_class'); }
    public function set_class($value){ $this->set_info('ability_class', $value); }

    public function get_subclass(){ return $this->get_info('ability_subclass'); }
    public function set_subclass($value){ $this->set_info('ability_subclass', $value); }

    public function get_master(){ return $this->get_info('ability_master'); }
    public function set_master($value){ $this->set_info('ability_master', $value); }
    public function get_base_master(){ return $this->get_info('ability_base_master'); }
    public function set_base_master($value){ $this->set_info('ability_base_master', $value); }

    public function get_number(){ return $this->get_info('ability_number'); }
    public function set_number($value){ $this->set_info('ability_number', $value); }
    public function get_base_number(){ return $this->get_info('ability_base_number'); }
    public function set_base_number($value){ $this->set_info('ability_base_number', $value); }

    public function get_type(){ return $this->get_info('ability_type'); }
    public function set_type($value){ $this->set_info('ability_type', $value); }
    public function get_base_type(){ return $this->get_info('ability_base_type'); }
    public function set_base_type($value){ $this->set_info('ability_base_type', $value); }

    public function get_type2(){ return $this->get_info('ability_type2'); }
    public function set_type2($value){ $this->set_info('ability_type2', $value); }
    public function get_base_type2(){ return $this->get_info('ability_base_type2'); }
    public function set_base_type2($value){ $this->set_info('ability_base_type2', $value); }

    public function get_speed(){ return $this->get_info('ability_speed'); }
    public function set_speed($value){ $this->set_info('ability_speed', $value); }
    public function get_base_speed(){ return $this->get_info('ability_base_speed'); }
    public function set_base_speed($value){ $this->set_info('ability_base_speed', $value); }
    public function reset_speed(){ $this->set_info('ability_speed', $this->get_base_speed()); }

    public function get_energy(){ return $this->get_info('ability_energy'); }
    public function set_energy($value){ $this->set_info('ability_energy', $value); }
    public function get_base_energy(){ return $this->get_info('ability_base_energy'); }
    public function set_base_energy($value){ $this->set_info('ability_base_energy', $value); }
    public function reset_energy(){ $this->set_info('ability_energy', $this->get_base_energy()); }

    public function get_damage(){ return $this->get_info('ability_damage'); }
    public function set_damage($value){ $this->set_info('ability_damage', $value); }
    public function get_base_damage(){ return $this->get_info('ability_base_damage'); }
    public function set_base_damage($value){ $this->set_info('ability_base_damage', $value); }
    public function reset_damage(){ $this->set_info('ability_damage', $this->get_base_damage()); }

    public function get_damage_percent(){ return $this->get_info('ability_damage_percent'); }
    public function set_damage_percent($value){ $this->set_info('ability_damage_percent', $value); }
    public function get_base_damage_percent(){ return $this->get_info('ability_base_damage_percent'); }
    public function set_base_damage_percent($value){ $this->set_info('ability_base_damage_percent', $value); }

    public function get_damage2(){ return $this->get_info('ability_damage2'); }
    public function set_damage2($value){ $this->set_info('ability_damage2', $value); }
    public function get_base_damage2(){ return $this->get_info('ability_base_damage2'); }
    public function set_base_damage2($value){ $this->set_info('ability_base_damage2', $value); }
    public function reset_damage2(){ $this->set_info('ability_damage2', $this->get_base_damage2()); }

    public function get_damage2_percent(){ return $this->get_info('ability_damage2_percent'); }
    public function set_damage2_percent($value){ $this->set_info('ability_damage2_percent', $value); }
    public function get_base_damage2_percent(){ return $this->get_info('ability_base_damage2_percent'); }
    public function set_base_damage2_percent($value){ $this->set_info('ability_base_damage2_percent', $value); }

    public function get_recovery(){ return $this->get_info('ability_recovery'); }
    public function set_recovery($value){ $this->set_info('ability_recovery', $value); }
    public function get_base_recovery(){ return $this->get_info('ability_base_recovery'); }
    public function set_base_recovery($value){ $this->set_info('ability_base_recovery', $value); }
    public function reset_recovery(){ $this->set_info('ability_recovery', $this->get_base_recovery()); }

    public function get_recovery_percent(){ return $this->get_info('ability_recovery_percent'); }
    public function set_recovery_percent($value){ $this->set_info('ability_recovery_percent', $value); }
    public function get_base_recovery_percent(){ return $this->get_info('ability_base_recovery_percent'); }
    public function set_base_recovery_percent($value){ $this->set_info('ability_base_recovery_percent', $value); }

    public function get_recovery2(){ return $this->get_info('ability_recovery2'); }
    public function set_recovery2($value){ $this->set_info('ability_recovery2', $value); }
    public function get_base_recovery2(){ return $this->get_info('ability_base_recovery2'); }
    public function set_base_recovery2($value){ $this->set_info('ability_base_recovery2', $value); }
    public function reset_recovery2(){ $this->set_info('ability_recovery2', $this->get_base_recovery2()); }

    public function get_recovery2_percent(){ return $this->get_info('ability_recovery2_percent'); }
    public function set_recovery2_percent($value){ $this->set_info('ability_recovery2_percent', $value); }
    public function get_base_recovery2_percent(){ return $this->get_info('ability_base_recovery2_percent'); }
    public function set_base_recovery2_percent($value){ $this->set_info('ability_base_recovery2_percent', $value); }

    public function get_accuracy(){ return $this->get_info('ability_accuracy'); }
    public function set_accuracy($value){ $this->set_info('ability_accuracy', $value); }
    public function get_base_accuracy(){ return $this->get_info('ability_base_accuracy'); }
    public function set_base_accuracy($value){ $this->set_info('ability_base_accuracy', $value); }
    public function reset_accuracy(){ $this->set_info('ability_accuracy', $this->get_base_accuracy()); }

    public function get_target(){ return $this->get_info('ability_target'); }
    public function set_target($value){ $this->set_info('ability_target', $value); }
    public function get_base_target(){ return $this->get_info('ability_base_target'); }
    public function set_base_target($value){ $this->set_info('ability_base_target', $value); }
    public function reset_target(){ $this->set_info('ability_target', $this->get_base_target()); }

    public function get_functions(){ return $this->get_info('ability_functions'); }
    public function set_functions($value){ $this->set_info('ability_functions', $value); }

    public function get_image(){ return $this->get_info('ability_image'); }
    public function set_image($value){ $this->set_info('ability_image', $value); }
    public function get_base_image(){ return $this->get_info('ability_base_image'); }
    public function set_base_image($value){ $this->set_info('ability_base_image', $value); }
    public function reset_image(){ $this->set_info('ability_image', $this->get_base_image()); }

    public function get_image_size(){ return $this->get_info('ability_image_size'); }
    public function set_image_size($value){ $this->set_info('ability_image_size', $value); }
    public function get_base_image_size(){ return $this->get_info('ability_base_image_size'); }
    public function set_base_image_size($value){ $this->set_info('ability_base_image_size', $value); }
    public function reset_image_size(){ $this->set_info('ability_image_size', $this->get_base_image_size()); }

    public function get_frame(){ return $this->get_info('ability_frame'); }
    public function set_frame($value){ $this->set_info('ability_frame', $value); }

    public function get_frame_span(){ return $this->get_info('ability_frame_span'); }
    public function set_frame_span($value){ $this->set_info('ability_frame_span', $value); }

    public function get_frame_animate(){ return $this->get_info('ability_frame_animate'); }
    public function set_frame_animate($value){ $this->set_info('ability_frame_animate', $value); }

    public function get_frame_index(){ return $this->get_info('ability_frame_index'); }
    public function set_frame_index($value){ $this->set_info('ability_frame_index', $value); }

    public function get_frame_offset(){
        $args = func_get_args();
        if (isset($args[0])){ return $this->get_info('ability_frame_offset', $args[0]); }
        else { return $this->get_info('ability_frame_offset'); }
    }
    public function set_frame_offset($value){
        $args = func_get_args();
        if (isset($args[1])){ $this->set_info('ability_frame_offset', $args[0], $args[1]); }
        else { $this->set_info('ability_frame_offset', $value); }
    }

    public function get_frame_styles(){ return $this->get_info('ability_frame_styles'); }
    public function set_frame_styles($value){ $this->set_info('ability_frame_styles', $value); }
    public function reset_frame_styles(){ $this->set_info('ability_frame_styles', ''); }

    public function get_frame_classes(){ return $this->get_info('ability_frame_classes'); }
    public function set_frame_classes($value){ $this->set_info('ability_frame_classes', $value); }
    public function reset_frame_classes(){ $this->set_info('ability_frame_classes', ''); }

    public function get_results(){ return $this->get_info('ability_results'); }
    public function set_results($value){ $this->set_info('ability_results', $value); }

    public function get_options(){ return $this->get_info('ability_options'); }
    public function set_options($value){ $this->set_info('ability_options', $value); }

    public function get_target_options(){ return $this->get_info('target_options'); }
    public function set_target_options($value){ $this->set_info('target_options', $value); }

    public function get_damage_options(){ return $this->get_info('ddamage_options'); }
    public function set_damage_options($value){ $this->set_info('damage_options', $value); }

    public function get_recovery_options(){ return $this->get_info('recovery_options'); }
    public function set_recovery_options($value){ $this->set_info('recovery_options', $value); }

    public function get_attachment_options(){ return $this->get_info('attachment_options'); }
    public function set_attachment_options($value){ $this->set_info('attachment_options', $value); }


    // Define public print functions for markup generation
    public function print_name($plural = false){
        $type_class = !empty($this->ability_type) ? $this->ability_type : 'none';
        if ($type_class != 'none' && !empty($this->ability_type2)){ $type_class .= '_'.$this->ability_type2; }
        elseif ($type_class == 'none' && !empty($this->ability_type2)){ $type_class = $this->ability_type2; }
        return '<span class="ability_name ability_type ability_type_'.$type_class.'">'.$this->ability_name.($plural ? 's' : '').'</span>';
    }
    //public function print_name(){ return '<span class="ability_name">'.$this->ability_name.'</span>'; }
    public function print_token(){ return '<span class="ability_token">'.$this->ability_token.'</span>'; }
    public function print_description(){ return '<span class="ability_description">'.$this->ability_description.'</span>'; }
    public function print_type(){ return '<span class="ability_type">'.$this->ability_type.'</span>'; }
    public function print_type2(){ return '<span class="ability_type2">'.$this->ability_type2.'</span>'; }
    public function print_speed(){ return '<span class="ability_speed">'.$this->ability_speed.'</span>'; }
    public function print_damage(){ return '<span class="ability_damage">'.$this->ability_damage.'</span>'; }
    public function print_recovery(){ return '<span class="ability_recovery">'.$this->ability_recovery.'</span>'; }
    public function print_accuracy(){ return '<span class="ability_accuracy">'.$this->ability_accuracy.'%</span>'; }

    // Define a trigger for using one of this robot's abilities
    public function reset_ability($target_robot, $this_ability){

        // Update internal variables
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;
    }

    // Define a public function for easily resetting result options
    public function ability_results_reset(){
        // Redfine the result options as an empty array
        $ability_results = array();
        // Populate the array with defaults
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
        $ability_results['counter_weaknesses'] = 0;
        $ability_results['counter_resistances'] = 0;
        $ability_results['counter_affinities'] = 0;
        $ability_results['counter_immunities'] = 0;
        $ability_results['counter_coreboosts'] = 0;
        $ability_results['flag_critical'] = false;
        $ability_results['flag_affinity'] = false;
        $ability_results['flag_weakness'] = false;
        $ability_results['flag_resistance'] = false;
        $ability_results['flag_immunity'] = false;
        $ability_results['flag_coreboost'] = false;
        // Update this ability's data
        $this->set_results($ability_results);
        // Return the resuling array
        return $this->get_results();
    }

    // Define a public function for easily resetting target options
    public function target_options_reset(){
        // Redfine the options variables as an empty array
        $target_options = array();
        // Populate the array with defaults
        $target_options['target_kind'] = 'energy';
        $target_options['target_frame'] = 'shoot';
        $target_options['ability_success_frame'] = 1;
        $target_options['ability_success_frame_span'] = 1;
        $target_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $target_options['ability_failure_frame'] = 1;
        $target_options['ability_failure_frame_span'] = 1;
        $target_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $target_options['target_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $target_options['target_kickback2'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $target_options['target_header'] = $this->robot->robot_name.'&#39;s '.$this->ability_name;
        $target_options['target_text'] = "{$this->robot->print_name()} uses {$this->print_name()}!";
        // Update this ability's data
        $this->set_target_options($target_options);
        // Return the resuling array
        return $this->get_target_options();
    }


    // Define a public function for easily updating target options
    public function target_options_update($target_options = array()){
        // Update internal variables with basic target options, if set
        $new_target_options = $this->get_target_options();
        if (isset($target_options['header'])){ $new_target_options['target_header'] = $target_options['header'];  }
        if (isset($target_options['text'])){ $new_target_options['target_text'] = $target_options['text'];  }
        if (isset($target_options['frame'])){ $new_target_options['target_frame'] = $target_options['frame'];  }
        if (isset($target_options['kind'])){ $new_target_options['target_kind'] = $target_options['kind'];  }
        // Update internal variables with kickback options, if set
        if (isset($target_options['kickback'])){
            $new_target_options['target_kickback']['x'] = $target_options['kickback'][0];
            $new_target_options['target_kickback']['y'] = $target_options['kickback'][1];
            $new_target_options['target_kickback']['z'] = $target_options['kickback'][2];
        }
        // Update internal variables with kickback2 options, if set
        if (isset($target_options['kickback2'])){
            $new_target_options['target_kickback2']['x'] = $target_options['kickback2'][0];
            $new_target_options['target_kickback2']['y'] = $target_options['kickback2'][1];
            $new_target_options['target_kickback2']['z'] = $target_options['kickback2'][2];
        }
        // Update internal variabels with success options, if set
        if (isset($target_options['success'])){
            $new_target_options['ability_success_frame'] = $target_options['success'][0];
            $new_target_options['ability_success_frame_offset']['x'] = $target_options['success'][1];
            $new_target_options['ability_success_frame_offset']['y'] = $target_options['success'][2];
            $new_target_options['ability_success_frame_offset']['z'] = $target_options['success'][3];
            $new_target_options['target_text'] = $target_options['success'][4];
            $new_target_options['ability_success_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($target_options['failure'])){
            $new_target_options['ability_failure_frame'] = $target_options['failure'][0];
            $new_target_options['ability_failure_frame_offset']['x'] = $target_options['failure'][1];
            $new_target_options['ability_failure_frame_offset']['y'] = $target_options['failure'][2];
            $new_target_options['ability_failure_frame_offset']['z'] = $target_options['failure'][3];
            $new_target_options['target_text'] = $target_options['failure'][4];
            $new_target_options['ability_failure_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update the session with changes
        $this->set_target_options($new_target_options);
        // Return the new array
        return $this->get_target_options();
    }

    // Define a public function for easily resetting damage options
    public function damage_options_reset(){
        // Redfine the options variables as an empty array
        $damage_options = array();
        // Populate the array with defaults
        $damage_options['damage_header'] = $this->robot->robot_name.'&#39;s '.$this->ability_name;
        $damage_options['damage_frame'] = 'damage';
        $damage_options['ability_success_frame'] = 1;
        $damage_options['ability_success_frame_span'] = 1;
        $damage_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $damage_options['ability_failure_frame'] = 1;
        $damage_options['ability_failure_frame_span'] = 1;
        $damage_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $damage_options['damage_kind'] = 'energy';
        $damage_options['damage_type'] = $this->ability_type;
        $damage_options['damage_type2'] = $this->ability_type2;
        $damage_options['damage_amount'] = $this->ability_damage;
        $damage_options['damage_amount2'] = $this->ability_damage2;
        $damage_options['damage_kickback'] = array('x' => 5, 'y' => 0, 'z' => 0);
        $damage_options['damage_kickback2'] = array('x' => 5, 'y' => 0, 'z' => 0);
        $damage_options['damage_percent'] = false;
        $damage_options['damage_percent2'] = false;
        $damage_options['damage_modifiers'] = true;
        $damage_options['success_rate'] = 'auto';
        $damage_options['failure_rate'] = 'auto';
        $damage_options['critical_rate'] = 10;
        $damage_options['critical_multiplier'] = 2;
        $damage_options['weakness_multiplier'] = 2;
        $damage_options['resistance_multiplier'] = 0.5;
        $damage_options['immunity_multiplier'] = 0;
        $damage_options['success_text'] = 'The ability hit!';
        $damage_options['failure_text'] = 'The ability missed&hellip;';
        $damage_options['immunity_text'] = 'The ability had no effect&hellip;';
        $damage_options['critical_text'] = 'It&#39;s a critical hit!';
        $damage_options['weakness_text'] = 'It&#39;s super effective!';
        $damage_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $damage_options['weakness_resistance_text'] = ''; //"It's a super effective resisted hit!';
        $damage_options['weakness_critical_text'] = 'It&#39;s a super effective critical hit!';
        $damage_options['resistance_critical_text'] = 'It&#39;s a critical hit, but not very effective&hellip;';
        // Update this ability's data
        $this->set_damage_options($damage_options);
        // Return the resuling array
        return $this->get_damage_options();
    }

    // Define a public function for easily updating damage options
    public function damage_options_update($damage_options = array()){
        // Update internal variables with basic damage options, if set
        $new_damage_options = $this->get_damage_options();
        if (isset($damage_options['header'])){ $new_damage_options['damage_header'] = $damage_options['header'];  }
        if (isset($damage_options['frame'])){ $new_damage_options['damage_frame'] = $damage_options['frame'];  }
        if (isset($damage_options['kind'])){ $new_damage_options['damage_kind'] = $damage_options['kind'];  }
        if (isset($damage_options['type'])){ $new_damage_options['damage_type'] = $damage_options['type'];  }
        if (isset($damage_options['type2'])){ $new_damage_options['damage_type2'] = $damage_options['type2'];  }
        if (isset($damage_options['amount'])){ $new_damage_options['damage_amount'] = $damage_options['amount'];  }
        if (isset($damage_options['percent'])){ $new_damage_options['damage_percent'] = $damage_options['percent'];  }
        if (isset($damage_options['modifiers'])){ $new_damage_options['damage_modifiers'] = $damage_options['modifiers'];  }
        // Update internal variables with rate options, if set
        if (isset($damage_options['rates'])){
            $new_damage_options['success_rate'] = $damage_options['rates'][0];
            $new_damage_options['failure_rate'] = $damage_options['rates'][1];
            $new_damage_options['critical_rate'] = $damage_options['rates'][2];
        }
        // Update internal variables with multipier options, if set
        if (isset($damage_options['multipliers'])){
            $new_damage_options['critical_multiplier'] = $damage_options['multipliers'][0];
            $new_damage_options['weakness_multiplier'] = $damage_options['multipliers'][1];
            $new_damage_options['resistance_multiplier'] = $damage_options['multipliers'][2];
            $new_damage_options['immunity_multiplier'] = $damage_options['multipliers'][3];
        }
        // Update internal variables with kickback options, if set
        if (isset($damage_options['kickback'])){
            $new_damage_options['damage_kickback']['x'] = $damage_options['kickback'][0];
            $new_damage_options['damage_kickback']['y'] = $damage_options['kickback'][1];
            $new_damage_options['damage_kickback']['z'] = $damage_options['kickback'][2];
        }
        // Update internal variables with kickback2 options, if set
        if (isset($damage_options['kickback2'])){
            $new_damage_options['damage_kickback2']['x'] = $damage_options['kickback2'][0];
            $new_damage_options['damage_kickback2']['y'] = $damage_options['kickback2'][1];
            $new_damage_options['damage_kickback2']['z'] = $damage_options['kickback2'][2];
        }
        // Update internal variables with success options, if set
        if (isset($damage_options['success'])){
            $new_damage_options['ability_success_frame'] = $damage_options['success'][0];
            $new_damage_options['ability_success_frame_offset']['x'] = $damage_options['success'][1];
            $new_damage_options['ability_success_frame_offset']['y'] = $damage_options['success'][2];
            $new_damage_options['ability_success_frame_offset']['z'] = $damage_options['success'][3];
            $new_damage_options['success_text'] = $damage_options['success'][4];
            $new_damage_options['ability_success_frame_span'] = isset($damage_options['success'][5]) ? $damage_options['success'][5] : 1;
        }
        // Update internal variables with failure options, if set
        if (isset($damage_options['failure'])){
            $new_damage_options['ability_failure_frame'] = $damage_options['failure'][0];
            $new_damage_options['ability_failure_frame_offset']['x'] = $damage_options['failure'][1];
            $new_damage_options['ability_failure_frame_offset']['y'] = $damage_options['failure'][2];
            $new_damage_options['ability_failure_frame_offset']['z'] = $damage_options['failure'][3];
            $new_damage_options['failure_text'] = $damage_options['failure'][4];
            $new_damage_options['ability_failure_frame_span'] = isset($damage_options['failure'][5]) ? $damage_options['failure'][5] : 1;
        }
        // If session update was requested, do it
        $this->set_damage_options($new_damage_options);
        // Return the new array
        return $this->get_damage_options();
    }

    // Define a public function for easily resetting recovery options
    public function recovery_options_reset(){
        // Redfine the options variables as an empty array
        $recovery_options = array();
        // Populate the array with defaults
        $recovery_options['recovery_header'] = $this->robot->robot_name.'&#39;s '.$this->ability_name;
        $recovery_options['recovery_frame'] = 'defend';
        $recovery_options['ability_success_frame'] = 1;
        $recovery_options['ability_success_frame_span'] = 1;
        $recovery_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $recovery_options['ability_failure_frame'] = 1;
        $recovery_options['ability_failure_frame_span'] = 1;
        $recovery_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $recovery_options['recovery_kind'] = 'energy';
        $recovery_options['recovery_type'] = $this->ability_type;
        $recovery_options['recovery_type2'] = $this->ability_type2;
        $recovery_options['recovery_amount'] = $this->ability_recovery;
        $recovery_options['recovery_amount2'] = $this->ability_recovery2;
        $recovery_options['recovery_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $recovery_options['recovery_kickback2'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $recovery_options['recovery_percent'] = false;
        $recovery_options['recovery_percent2'] = false;
        $recovery_options['recovery_modifiers'] = true;
        $recovery_options['success_rate'] = 'auto';
        $recovery_options['failure_rate'] = 'auto';
        $recovery_options['critical_rate'] = 10;
        $recovery_options['critical_multiplier'] = 2;
        $recovery_options['affinity_multiplier'] = 2;
        $recovery_options['resistance_multiplier'] = 0.5;
        $recovery_options['immunity_multiplier'] = 0;
        $recovery_options['recovery_type'] = $this->ability_type;
        $recovery_options['recovery_type2'] = $this->ability_type2;
        $recovery_options['success_text'] = 'The ability worked!';
        $recovery_options['failure_text'] = 'The ability failed&hellip;';
        $recovery_options['immunity_text'] = 'The ability had no effect&hellip;';
        $recovery_options['critical_text'] = 'It&#39;s a lucky boost!';
        $recovery_options['affinity_text'] = 'It&#39;s super effective!';
        $recovery_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $recovery_options['affinity_resistance_text'] = ''; //'It&#39;s a super effective resisted hit!';
        $recovery_options['affinity_critical_text'] = 'It&#39;s a super effective lucky boost!';
        $recovery_options['resistance_critical_text'] = 'It&#39;s a lucky boost, but not very effective&hellip;';
        // Update this ability's data
        $this->set_recovery_options($recovery_options);
        // Return the resuling array
        return $this->get_recovery_options();
    }

    // Define a public function for easily updating recovery options
    public function recovery_options_update($recovery_options = array()){
        // Update internal variables with basic recovery options, if set
        $new_recovery_options = $this->get_recovery_options();
        if (isset($recovery_options['header'])){ $new_recovery_options['recovery_header'] = $recovery_options['header'];  }
        if (isset($recovery_options['frame'])){ $new_recovery_options['recovery_frame'] = $recovery_options['frame'];  }
        if (isset($recovery_options['kind'])){ $new_recovery_options['recovery_kind'] = $recovery_options['kind'];  }
        if (isset($recovery_options['type'])){ $new_recovery_options['recovery_type'] = $recovery_options['type'];  }
        if (isset($recovery_options['type2'])){ $new_recovery_options['recovery_type2'] = $recovery_options['type2'];  }
        if (isset($recovery_options['amount'])){ $new_recovery_options['recovery_amount'] = $recovery_options['amount'];  }
        if (isset($recovery_options['percent'])){ $new_recovery_options['recovery_percent'] = $recovery_options['percent'];  }
        if (isset($recovery_options['modifiers'])){ $new_recovery_options['recovery_modifiers'] = $recovery_options['modifiers'];  }
        // Update internal variables with rate options, if set
        if (isset($recovery_options['rates'])){
            $new_recovery_options['success_rate'] = $recovery_options['rates'][0];
            $new_recovery_options['failure_rate'] = $recovery_options['rates'][1];
            $new_recovery_options['critical_rate'] = $recovery_options['rates'][2];
        }
        // Update internal variables with multipier options, if set
        if (isset($recovery_options['multipliers'])){
            $new_recovery_options['critical_multiplier'] = $recovery_options['multipliers'][0];
            $new_recovery_options['weakness_multiplier'] = $recovery_options['multipliers'][1];
            $new_recovery_options['resistance_multiplier'] = $recovery_options['multipliers'][2];
            $new_recovery_options['immunity_multiplier'] = $recovery_options['multipliers'][3];
        }
        // Update internal variables with kickback options, if set
        if (isset($recovery_options['kickback'])){
            $new_recovery_options['recovery_kickback']['x'] = $recovery_options['kickback'][0];
            $new_recovery_options['recovery_kickback']['y'] = $recovery_options['kickback'][1];
            $new_recovery_options['recovery_kickback']['z'] = $recovery_options['kickback'][2];
        }
        // Update internal variables with kickback2 options, if set
        if (isset($recovery_options['kickback2'])){
            $new_recovery_options['recovery_kickback2']['x'] = $recovery_options['kickback2'][0];
            $new_recovery_options['recovery_kickback2']['y'] = $recovery_options['kickback2'][1];
            $new_recovery_options['recovery_kickback2']['z'] = $recovery_options['kickback2'][2];
        }
        // Update internal variabels with success options, if set
        if (isset($recovery_options['success'])){
            $new_recovery_options['ability_success_frame'] = $recovery_options['success'][0];
            $new_recovery_options['ability_success_frame_offset']['x'] = $recovery_options['success'][1];
            $new_recovery_options['ability_success_frame_offset']['y'] = $recovery_options['success'][2];
            $new_recovery_options['ability_success_frame_offset']['z'] = $recovery_options['success'][3];
            $new_recovery_options['success_text'] = $recovery_options['success'][4];
            $new_recovery_options['ability_success_frame_span'] = isset($recovery_options['success'][5]) ? $recovery_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($recovery_options['failure'])){
            $new_recovery_options['ability_failure_frame'] = $recovery_options['failure'][0];
            $new_recovery_options['ability_failure_frame_offset']['x'] = $recovery_options['failure'][1];
            $new_recovery_options['ability_failure_frame_offset']['y'] = $recovery_options['failure'][2];
            $new_recovery_options['ability_failure_frame_offset']['z'] = $recovery_options['failure'][3];
            $new_recovery_options['failure_text'] = $recovery_options['failure'][4];
            $new_recovery_options['ability_failure_frame_span'] = isset($recovery_options['failure'][5]) ? $recovery_options['failure'][5] : 1;
        }
        // If session update was requested, do it
        $this->set_recovery_options($new_recovery_options);
        // Return the new array
        return $this->get_recovery_options();
    }

    // Define a public function for easily resetting attachment options
    public function attachment_options_reset(){
        // Redfine the options variables as an empty array
        $attachment_options = array();
        // Update this ability's data
        $this->set_attachment_options($attachment_options);
        // Return the resuling array
        return $this->get_attachment_options();
    }


    // Define a public function for easily updating attachment options
    public function attachment_options_update($attachment_options = array()){
        // Return the new array
        return $this->get_attachment_options();
    }

    // Define a function for generating ability canvas variables
    public function get_canvas_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the ability data array and populate basic data
        $this_data['ability_markup'] = '';
        $this_data['data_sticky'] = isset($options['sticky']) ? $options['sticky'] : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'ability';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['ability_name'] = isset($options['ability_name']) ? $options['ability_name'] : $this->ability_name;
        $this_data['ability_id'] = $this->ability_id;
        $this_data['ability_title'] = $this->ability_name;
        $this_data['ability_token'] = $this->ability_token;
        $this_data['ability_id_token'] = $this->ability_id.'_'.$this->ability_token;
        $this_data['ability_image'] = isset($options['ability_image']) ? $options['ability_image'] : $this->ability_image;
        $this_data['ability_status'] = $robot_data['robot_status'];
        $this_data['ability_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['ability_direction'] = $this->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['ability_float'] = $robot_data['robot_float'];
        $this_data['ability_size'] = $this_data['ability_position'] == 'active' ? ($this->ability_image_size * 2) : $this->ability_image_size;
        $this_data['ability_frame'] = isset($options['ability_frame']) ? $options['ability_frame'] : $this->ability_frame;
        $this_data['ability_frame_span'] = isset($options['ability_frame_span']) ? $options['ability_frame_span'] : $this->ability_frame_span;
        $this_data['ability_frame_index'] = isset($options['ability_frame_index']) ? $options['ability_frame_index'] : $this->ability_frame_index;
        if (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] >= 0){ $this_data['ability_frame'] = str_pad($this_data['ability_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] < 0){ $this_data['ability_frame'] = ''; }
        //$this_data['ability_image'] = 'images/abilities/'.(!empty($this_data['ability_image']) ? $this_data['ability_image'] : $this_data['ability_token']).'/sprite_'.$this_data['ability_direction'].'_'.$this_data['ability_size'].'x'.$this_data['ability_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['ability_frame_offset'] = isset($options['ability_frame_offset']) ? $options['ability_frame_offset'] : $this->ability_frame_offset;
        $animate_frames_array = isset($options['ability_frame_animate']) ? $options['ability_frame_animate'] : array($this_data['ability_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['ability_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['ability_frame_styles'] = isset($options['ability_frame_styles']) ? $options['ability_frame_styles'] : $this->ability_frame_styles;
        $this_data['ability_frame_classes'] = isset($options['ability_frame_classes']) ? $options['ability_frame_classes'] : $this->ability_frame_classes;

        $this_data['ability_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : ($robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $robot_data['robot_key']) / 8) * 0.5));
        if (strstr($this_data['ability_frame_classes'], 'sprite_fullscreen')){
            $this_data['ability_frame_styles'] = '';
            $this_data['ability_scale'] = 1;
        }

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this->ability_image_size * 2);
        $this_data['ability_sprite_size'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_width'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_height'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_image_width'] = ceil($this_data['ability_scale'] * $zoom_size * 10);
        $this_data['ability_image_height'] = ceil($this_data['ability_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this robot
        $canvas_offset_data = rpg_functions::canvas_sprite_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size']);
        //$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        //$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        //$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];

        // Define the ability's canvas offset variables
        //$temp_size_diff = $robot_data['robot_sprite_size'] != $ability_data['ability_sprite_size'] ? ceil(($robot_data['robot_sprite_size'] - $ability_data['ability_sprite_size']) * 0.5) : ceil($ability_data['ability_sprite_size'] * 0.25);
        //$temp_size_diff = $robot_data['robot_sprite_size'] > 80 ? ceil(($robot_data['robot_sprite_size'] - 80) / 2) : 0;
        //if ($temp_size_diff > 0 && $robot_data['robot_position'] != 'active'){ $temp_size_diff += floor($this_data['ability_scale'] * $this_data['ability_sprite_size'] * 0.5); }
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['ability_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['ability_sprite_size']) / 2) ; }
        //$temp_size_diff = floor(($temp_size_diff * 2) + ($temp_size_diff * $robot_data['robot_scale']));

        // If this is a STICKY attachedment, make sure it doesn't move with the robot
        if ($this_data['data_sticky'] != false){

            //$this_data['data_sticky'] = 'true';

            // Calculate the canvas X offsets using the robot's position as base
            if ($this_data['ability_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['ability_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $canvas_offset_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's position as base
            if ($this_data['ability_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['y']/100))); }
            elseif ($this_data['ability_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $canvas_offset_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's position as base
            if ($this_data['ability_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] + $this_data['ability_frame_offset']['z']); }
            elseif ($this_data['ability_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] - ($this_data['ability_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $canvas_offset_data['canvas_offset_z'];  }

            // Collect the target, damage, and recovery options
            $this_target_options = !empty($options['this_ability']->target_options) ? $options['this_ability']->target_options : array();
            $this_damage_options = !empty($options['this_ability']->damage_options) ? $options['this_ability']->damage_options : array();
            $this_recovery_options = !empty($options['this_ability']->recovery_options) ? $options['this_ability']->recovery_options : array();
            $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

            // Either way, apply target offsets if they exist and it's this robot using the ability
            if (isset($options['this_ability_target']) && $options['this_ability_target'] == $this_data['robot_id_token']){
                // If any of the co-ordinates are provided, update all
                if (!empty($this_target_options['target_kickback']['x'])
                    || !empty($this_target_options['target_kickback']['y'])
                    || !empty($this_target_options['target_kickback']['z'])){
                    $this_data['canvas_offset_x'] -= $this_target_options['target_kickback']['x'];
                    $this_data['canvas_offset_y'] -= $this_target_options['target_kickback']['y'];
                    $this_data['canvas_offset_z'] -= $this_target_options['target_kickback']['z'];
                }
            }

        }
        // Else if this is a normal attachment, it moves with the robot
        else {

            // Calculate the canvas X offsets using the robot's offset as base
            if ($this_data['ability_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['ability_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            if ($this_data['ability_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['y']/100))); }
            elseif ($this_data['ability_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            if ($this_data['ability_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $this_data['ability_frame_offset']['z']); }
            elseif ($this_data['ability_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($this_data['ability_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

        }


        // Define the rest of the display variables
        //$this_data['ability_image'] = 'images/abilities/'.(!empty($this_data['ability_image']) ? $this_data['ability_image'] : $this_data['ability_token']).'/sprite_'.$this_data['ability_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
        if (!preg_match('/^images/i', $this_data['ability_image'])){ $this_data['ability_image'] = 'images/abilities/'.$this_data['ability_image'].'/sprite_'.$this_data['ability_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['ability_markup_class'] = 'sprite sprite_ability ';
        $this_data['ability_markup_class'] .= 'sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].' sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].'_'.$this_data['ability_frame'].' ';
        $this_data['ability_markup_class'] .= 'ability_status_'.$this_data['ability_status'].' ability_position_'.$this_data['ability_position'].' ';
        $frame_position = is_numeric($this_data['ability_frame']) ? (int)($this_data['ability_frame']) : array_search($this_data['ability_frame'], $this_data['ability_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['ability_sprite_size'] * $frame_position));
        $this_data['ability_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['ability_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['ability_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['ability_markup_style'] .= 'background-image: url('.$this_data['ability_image'].'); width: '.($this_data['ability_sprite_size'] * $this_data['ability_frame_span']).'px; height: '.$this_data['ability_sprite_size'].'px; background-size: '.$this_data['ability_image_width'].'px '.$this_data['ability_image_height'].'px; ';

        // Generate the final markup for the canvas ability
        ob_start();

            // Display the ability's battle sprite
            echo '<div data-ability-id="'.$this_data['ability_id_token'].'" data-robot-id="'.$robot_data['robot_id_token'].'" class="'.($this_data['ability_markup_class'].$this_data['ability_frame_classes']).'" style="'.($this_data['ability_markup_style'].$this_data['ability_frame_styles']).'" data-debug="'.$this_data['data_debug'].'" data-sticky="'.($this_data['data_sticky'] === false ? 'false' : $this_data['data_sticky']).'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['ability_sprite_size'].'" data-direction="'.$this_data['ability_direction'].'" data-frame="'.$this_data['ability_frame'].'" data-animate="'.$this_data['ability_frame_animate'].'" data-position="'.$this_data['ability_position'].'" data-status="'.$this_data['ability_status'].'" data-scale="'.$this_data['ability_scale'].'">'.$this_data['ability_token'].'</div>';

        // Collect the generated ability markup
        $this_data['ability_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating ability console variables
    public function get_console_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console ability data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this ability
        $this_data['ability_name'] = isset($options['ability_name']) ? $options['ability_name'] : $this->ability_name;
        $this_data['ability_title'] = $this_data['ability_name'];
        $this_data['ability_token'] = $this->ability_token;
        if (preg_match('/^item-/i', $this->ability_token)){ $this_data['ability_direction'] = 'right'; }
        else { $this_data['ability_direction'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this->robot_id ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left'); }
        $this_data['ability_float'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this->robot_id ? $robot_data['robot_float'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['ability_size'] = $this->ability_image_size;
        $this_data['ability_frame'] = isset($options['ability_frame']) ? $options['ability_frame'] : $this->ability_frame;
        if (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] >= 0){ $this_data['ability_frame'] = str_pad($this_data['ability_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] < 0){ $this_data['ability_frame'] = ''; }
        $this_data['image_type'] = !empty($options['this_ability_image']) ? $options['this_ability_image'] : 'icon';

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['ability_float'];
        $this_data['container_style'] = '';
        $this_data['ability_markup_class'] = 'sprite sprite_ability sprite_ability_'.$this_data['image_type'].' ';
        $this_data['ability_markup_style'] = '';
        if (empty($this_data['ability_image']) || !preg_match('/^images/i', $this_data['ability_image'])){ $this_data['ability_image'] = 'images/abilities/'.(!empty($this_data['ability_image']) ? $this_data['ability_image'] : $this_data['ability_token']).'/'.$this_data['image_type'].'_'.$this_data['ability_direction'].'_'.$this_data['ability_size'].'x'.$this_data['ability_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['ability_markup_class'] .= 'sprite_'.$this_data['ability_size'].'x'.$this_data['ability_size'].' sprite_'.$this_data['ability_size'].'x'.$this_data['ability_size'].'_'.$this_data['ability_frame'].' ';
        $this_data['ability_markup_style'] .= 'background-image: url('.$this_data['ability_image'].'); ';

        // Generate the final markup for the console ability
        $this_data['ability_markup'] = '';
        $this_data['ability_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['ability_markup'] .= '<div class="'.$this_data['ability_markup_class'].'" style="'.$this_data['ability_markup_style'].'" title="'.$this_data['ability_title'].'">'.$this_data['ability_title'].'</div>';
        $this_data['ability_markup'] .= '</div>';

        // Return the ability console data
        return $this_data;

    }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all ability index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @return mixed
     */
    public static function get_index_fields($implode = false){

        // Define the various index fields for ability objects
        $index_fields = array(
            'ability_id',
            'ability_token',
            'ability_name',
            'ability_game',
            'ability_group',
            'ability_class',
            'ability_subclass',
            'ability_master',
            'ability_number',
            'ability_image',
            'ability_image_sheets',
            'ability_image_size',
            'ability_image_editor',
            'ability_type',
            'ability_type2',
            'ability_description',
            'ability_description2',
            'ability_speed',
            'ability_energy',
            'ability_energy_percent',
            'ability_damage',
            'ability_damage_percent',
            'ability_damage2',
            'ability_damage2_percent',
            'ability_recovery',
            'ability_recovery_percent',
            'ability_recovery2',
            'ability_recovery2_percent',
            'ability_accuracy',
            'ability_price',
            'ability_target',
            'ability_frame',
            'ability_frame_animate',
            'ability_frame_index',
            'ability_frame_offset',
            'ability_frame_styles',
            'ability_frame_classes',
            'attachment_frame',
            'attachment_frame_animate',
            'attachment_frame_index',
            'attachment_frame_offset',
            'attachment_frame_styles',
            'attachment_frame_classes',
            'ability_functions',
            'ability_flag_hidden',
            'ability_flag_complete',
            'ability_flag_published',
            'ability_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

    /**
     * Get the entire ability index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND ability_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND ability_flag_published = 1 '; }

        // Collect every type's info from the database index
        $ability_fields = self::get_index_fields(true);
        $ability_index = $this_database->get_array_list("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_id <> 0 {$temp_where};", 'ability_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($ability_index)){
            $ability_index = self::parse_index($ability_index);
            return $ability_index;
        } else {
            return array();
        }

    }

    /**
     * Get the tokens for all abilities in the global index
     * @return array
     */
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND ability_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND ability_flag_published = 1 '; }

        // Collect an array of ability tokens from the database
        $ability_index = $this_database->get_array_list("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_id <> 0 {$temp_where};", 'ability_token');

        // Return the tokens if not empty, else nothing
        if (!empty($ability_index)){
            $ability_tokens = array_keys($ability_index);
            return $ability_tokens;
        } else {
            return array();
        }

    }

    // Define a function for pulling a custom ability index
    public static function get_index_custom($ability_tokens = array()){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Generate a token string for the database query
        $ability_tokens_string = array();
        foreach ($ability_tokens AS $ability_token){ $ability_tokens_string[] = "'{$ability_token}'"; }
        $ability_tokens_string = implode(', ', $ability_tokens_string);

        // Collect the requested ability's info from the database index
        $ability_fields = self::get_index_fields(true);
        $ability_index = $this_database->get_array_list("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_token IN ({$ability_tokens_string});", 'ability_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($ability_index)){
            $ability_index = self::parse_index($ability_index);
            return $ability_index;
        } else {
            return array();
        }

    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($ability_token){

        // Pull in global variables
        $this_database = cms_database::get_database();

        // Collect this ability's info from the database index
        $lookup = !is_numeric($ability_token) ? "ability_token = '{$ability_token}'" : "ability_id = {$ability_token}";
        $ability_fields = self::get_index_fields(true);
        $ability_index = $this_database->get_array("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE {$lookup};", 'ability_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($ability_index)){
            $ability_index = self::parse_index_info($ability_index);
            return $ability_index;
        } else {
            return array();
        }

    }

    // Define a public function for parsing a ability index array in bulk
    public static function parse_index($ability_index){

        // Loop through each entry and parse its data
        foreach ($ability_index AS $token => $info){
            $ability_index[$token] = self::parse_index_info($info);
        }

        // Return the parsed index
        return $ability_index;

    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($ability_info){

        // Return false if empty
        if (empty($ability_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($ability_info['_parsed'])){ return $ability_info; }
        else { $ability_info['_parsed'] = true; }

        // Explode the base and animation indexes into an array
        $temp_field_names = array('ability_frame_animate', 'ability_frame_index', 'ability_frame_offset');
        foreach ($temp_field_names AS $field_name){
            if (!empty($ability_info[$field_name])){ $ability_info[$field_name] = json_decode($ability_info[$field_name], true); }
            else { $ability_info[$field_name] = array(); }
        }

        // Return the parsed ability info
        return $ability_info;
    }


    // -- PRINT FUNCTIONS -- /

    // Define a static function for printing out the ability's title markup
    public static function print_editor_title_markup($robot_info, $ability_info, $print_options = array()){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Collect the types index for reference
        $mmrpg_types = rpg_type::get_index();

        // Require the function file
        $temp_ability_title = '';

        // Collect values for potentially missing global variables
        if (!isset($session_token)){  }

        if (empty($robot_info)){ return false; }
        if (empty($ability_info)){ return false; }

        $print_options['show_accuracy'] = isset($print_options['show_accuracy']) ? $print_options['show_accuracy'] : true;
        $print_options['show_quantity'] = isset($print_options['show_quantity']) ? $print_options['show_quantity'] : true;

        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_ability_token = $ability_info['ability_token'];
        $temp_ability_type = !empty($ability_info['ability_type']) ? $mmrpg_types[$ability_info['ability_type']] : false;
        $temp_ability_type2 = !empty($ability_info['ability_type2']) ? $mmrpg_types[$ability_info['ability_type2']] : false;
        $temp_ability_energy = rpg_robot::calculate_weapon_energy_static($robot_info, $ability_info);
        $temp_ability_damage = !empty($ability_info['ability_damage']) ? $ability_info['ability_damage'] : 0;
        $temp_ability_damage2 = !empty($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : 0;
        $temp_ability_recovery = !empty($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : 0;
        $temp_ability_recovery2 = !empty($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : 0;
        $temp_ability_target = !empty($ability_info['ability_target']) ? $ability_info['ability_target'] : 'auto';
        while (!in_array($ability_info['ability_token'], $robot_info['robot_abilities'])){
            if (!$robot_flag_copycore){
                if (empty($robot_ability_core)){ break; }
                elseif (empty($temp_ability_type) && empty($temp_ability_type2)){ break; }
                else {
                    $temp_type_array = array();
                    if (!empty($temp_ability_type)){ $temp_type_array[] = $temp_ability_type['type_token']; }
                    if (!empty($temp_ability_type2)){ $temp_type_array[] = $temp_ability_type2['type_token']; }
                    if (!in_array($robot_ability_core, $temp_type_array)){ break; }
                }
            }
            break;
        }

        $temp_ability_title = $ability_info['ability_name'];
        if (!empty($temp_ability_type)){ $temp_ability_title .= ' ('.$temp_ability_type['type_name'].' Type)'; }
        if (!empty($temp_ability_type2)){ $temp_ability_title = str_replace('Type', '/ '.$temp_ability_type2['type_name'].' Type', $temp_ability_title); }

        if ($ability_info['ability_class'] != 'item'){
            if (!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  // '; }
            elseif (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  // '; }
            if (!empty($ability_info['ability_damage']) && !empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage | '.$ability_info['ability_recovery'].' Recovery'; }
            elseif (!empty($ability_info['ability_damage'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage'; }
            elseif (!empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_recovery'].' Recovery '; }
            if (!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  | '; }
        }

        //if (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $temp_ability_title .= 'Special'; }

        // If show accuracy or quantity
        if (($ability_info['ability_class'] != 'item' && $print_options['show_accuracy'])
            || ($ability_info['ability_class'] == 'item' && $print_options['show_quantity'])){

            $temp_ability_title .= '  | ';
            if ($ability_info['ability_class'] != 'item' && !empty($ability_info['ability_accuracy'])){ $temp_ability_title .= ' '.$ability_info['ability_accuracy'].'% Accuracy'; }
            elseif ($ability_info['ability_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token])){ $temp_ability_title .= ' '.($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token] == 1 ? '1 Unit' : $_SESSION[$session_token]['values']['battle_items'][$temp_ability_token].' Units'); }
            elseif ($ability_info['ability_class'] == 'item' ){ $temp_ability_title .= ' 0 Units'; }

        }

        if ($ability_info['ability_class'] != 'item' && !empty($temp_ability_energy)){ $temp_ability_title .= ' | '.$temp_ability_energy.' Energy'; }
        if ($ability_info['ability_class'] != 'item' && $temp_ability_target != 'auto'){ $temp_ability_title .= ' | Select Target'; }

        if (!empty($ability_info['ability_description'])){
            $temp_find = array('{RECOVERY}', '{RECOVERY2}', '{DAMAGE}', '{DAMAGE2}');
            $temp_replace = array($temp_ability_recovery, $temp_ability_recovery2, $temp_ability_damage, $temp_ability_damage2);
            $temp_description = str_replace($temp_find, $temp_replace, $ability_info['ability_description']);
            $temp_ability_title .= ' // '.$temp_description;
        }

        // Return the generated option markup
        return $temp_ability_title;

    }


    // Define a static function for printing out the ability's title markup
    public static function print_editor_option_markup($robot_info, $ability_info){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Require the function file
        $this_option_markup = '';

        // Generate the ability option markup
        if (empty($robot_info)){ return false; }
        if (empty($ability_info)){ return false; }
        //$ability_info = rpg_ability::get_index_info($temp_ability_token);
        $temp_robot_token = $robot_info['robot_token'];
        $temp_ability_token = $ability_info['ability_token'];
        $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_ability_type = !empty($ability_info['ability_type']) ? rpg_type::get_index_info($ability_info['ability_type']) : false;
        $temp_ability_type2 = !empty($ability_info['ability_type2']) ? rpg_type::get_index_info($ability_info['ability_type2']) : false;
        $temp_ability_energy = rpg_robot::calculate_weapon_energy_static($robot_info, $ability_info);
        $temp_type_array = array();
        $temp_incompatible = false;
        $temp_global_abilities = self::get_global_abilities();
        $temp_index_abilities = !empty($robot_info['robot_index_abilities']) ? $robot_info['robot_index_abilities'] : array();
        $temp_current_abilities = !empty($robot_info['robot_abilities']) ? array_keys($robot_info['robot_abilities']) : array();
        $temp_compatible_abilities = array_merge($temp_global_abilities, $temp_index_abilities, $temp_current_abilities);
        //while (!in_array($temp_ability_token, $robot_info['robot_abilities'])){
        while (!in_array($temp_ability_token, $temp_compatible_abilities)){
            if (!$robot_flag_copycore){
                if (empty($robot_ability_core)){ $temp_incompatible = true; break; }
                elseif (empty($temp_ability_type) && empty($temp_ability_type2)){ $temp_incompatible = true; break; }
                else {
                    if (!empty($temp_ability_type)){ $temp_type_array[] = $temp_ability_type['type_token']; }
                    if (!empty($temp_ability_type2)){ $temp_type_array[] = $temp_ability_type2['type_token']; }
                    if (!in_array($robot_ability_core, $temp_type_array)){ $temp_incompatible = true; break; }
                }
            }
            break;
        }
        if ($temp_incompatible == true){ return false; }
        $temp_ability_label = $ability_info['ability_name'];
        $temp_ability_title = rpg_ability::print_editor_title_markup($robot_info, $ability_info);
        $temp_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $temp_ability_title));
        $temp_ability_title_tooltip = htmlentities($temp_ability_title, ENT_QUOTES, 'UTF-8');
        $temp_ability_option = $ability_info['ability_name'];
        if (!empty($temp_ability_type)){ $temp_ability_option .= ' | '.$temp_ability_type['type_name']; }
        if (!empty($temp_ability_type2)){ $temp_ability_option .= ' / '.$temp_ability_type2['type_name']; }
        if (!empty($ability_info['ability_damage'])){ $temp_ability_option .= ' | D:'.$ability_info['ability_damage']; }
        if (!empty($ability_info['ability_recovery'])){ $temp_ability_option .= ' | R:'.$ability_info['ability_recovery']; }
        if ($ability_info['ability_class'] != 'item' && !empty($ability_info['ability_accuracy'])){ $temp_ability_option .= ' | A:'.$ability_info['ability_accuracy']; }
        elseif ($ability_info['ability_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token])){ $temp_ability_option .= ' | U:'.$_SESSION[$session_token]['values']['battle_items'][$temp_ability_token]; }
        elseif ($ability_info['ability_class'] == 'item'){ $temp_ability_option .= ' | U:0'; }
        if (!empty($temp_ability_energy)){ $temp_ability_option .= ' | E:'.$temp_ability_energy; }

        // Return the generated option markup
        $this_option_markup = '<option value="'.$temp_ability_token.'" data-label="'.$temp_ability_label.'" data-type="'.(!empty($temp_ability_type) ? $temp_ability_type['type_token'] : 'none').'" data-type2="'.(!empty($temp_ability_type2) ? $temp_ability_type2['type_token'] : '').'" title="'.$temp_ability_title_plain.'" data-tooltip="'.$temp_ability_title_tooltip.'">'.$temp_ability_option.'</option>';

        // Return the generated option markup
        return $this_option_markup;

    }


    // Define a static function for printing out the ability's select options markup
    public static function print_editor_options_list_markup($player_ability_rewards, $robot_ability_rewards, $player_info, $robot_info){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $this_database;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
        global $mmrpg_database_abilities;
        $session_token = rpg_game::session_token();

        if (empty($player_info)){ return false; }
        if (empty($robot_info)){ return false; }

        // Define the options markup variable
        $this_options_markup = '';

        $player_ability_options = array();
        $player_abilities_unlocked = array_keys($player_ability_rewards);
        if (!empty($mmrpg_database_abilities)){
            $temp_category = 'special-weapons';
            foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){
                if ($ability_token == 'energy-boost'){ $temp_category = 'support-abilities'; }
                if (!in_array($ability_token, $player_abilities_unlocked)){ continue; }
                $ability_info = rpg_ability::parse_index_info($ability_info);
                $option_markup = rpg_ability::print_editor_option_markup($robot_info, $ability_info);
                $player_ability_options[$temp_category][] = $option_markup;
            }
        }
        if (!empty($player_ability_options)){
            foreach ($player_ability_options AS $category_token => $ability_options){
                $category_name = ucwords(str_replace('-', ' ', $category_token));
                $this_options_markup .= '<optgroup label="'.$category_name.'">'.implode('', $ability_options).'</optgroup>';
            }
        }

        /*
        $robot_ability_rewards_options = array();
        foreach ($robot_ability_rewards AS $temp_ability_info){
            if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
            $temp_token = $temp_ability_info['ability_token'];
            $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
            $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
            if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
        }
        $robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
        $this_options_markup .= $robot_ability_rewards_options;
        */

        /*
        $player_ability_weapon_options = array();
        $player_ability_support_options = array();
        foreach ($player_ability_rewards AS $temp_ability_key => $temp_ability_info){
            if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
            $temp_token = $temp_ability_info['ability_token'];
            $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
            $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);

            if (!empty($temp_option_markup)){
                if ($temp_category == 'weapon'){ $player_ability_weapon_options[] = $temp_option_markup; }
                elseif ($temp_category == 'support'){ $player_ability_support_options[] = $temp_option_markup; }
            }
        }
        $player_ability_weapon_options = '<optgroup label="Special Weapons">'.implode('', $player_ability_weapon_options).'</optgroup>';
        $player_ability_support_options = '<optgroup label="Support Abilities">'.implode('', $player_ability_support_options).'</optgroup>';
        $this_options_markup .= $player_ability_weapon_options;
        $this_options_markup .= $player_ability_support_options;
        */

        // Add an option at the bottom to remove the ability
        $this_options_markup .= '<optgroup label="Ability Actions">';
        $this_options_markup .= '<option value="" title="">- Remove Ability -</option>';
        $this_options_markup .= '</optgroup>';

        // Return the generated select markup
        return $this_options_markup;

    }


    // Define a static function for printing out the ability's select markup
    public static function print_editor_select_markup($ability_rewards_options, $player_info, $robot_info, $ability_info, $ability_key = 0){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $this_database;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
        global $mmrpg_database_abilities;
        $session_token = rpg_game::session_token();

        if (empty($robot_info)){ return false; }
        if (empty($ability_info)){ return false; }

        // Define the select markup variable
        $this_select_markup = '';

        $ability_info_id = $ability_info['ability_id'];
        $ability_info_token = $ability_info['ability_token'];
        $ability_info_name = $ability_info['ability_name'];
        $ability_info_energy = isset($ability_info['ability_energy']) ? $ability_info['ability_energy'] : 4;
        $ability_info_damage = !empty($ability_info['ability_damage']) ? $ability_info['ability_damage'] : 0;
        $ability_info_damage2 = !empty($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : 0;
        $ability_info_damage_percent = !empty($ability_info['ability_damage_percent']) ? true : false;
        $ability_info_damage2_percent = !empty($ability_info['ability_damage2_percent']) ? true : false;
        if ($ability_info_damage_percent && $ability_info_damage > 100){ $ability_info_damage = 100; }
        if ($ability_info_damage2_percent && $ability_info_damage2 > 100){ $ability_info_damage2 = 100; }
        $ability_info_recovery = !empty($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : 0;
        $ability_info_recovery2 = !empty($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : 0;
        $ability_info_recovery_percent = !empty($ability_info['ability_recovery_percent']) ? true : false;
        $ability_info_recovery2_percent = !empty($ability_info['ability_recovery2_percent']) ? true : false;
        if ($ability_info_recovery_percent && $ability_info_recovery > 100){ $ability_info_recovery = 100; }
        if ($ability_info_recovery2_percent && $ability_info_recovery2 > 100){ $ability_info_recovery2 = 100; }
        $ability_info_accuracy = !empty($ability_info['ability_accuracy']) ? $ability_info['ability_accuracy'] : 0;
        $ability_info_description = !empty($ability_info['ability_description']) ? $ability_info['ability_description'] : '';
        $ability_info_description = str_replace('{DAMAGE}', $ability_info_damage, $ability_info_description);
        $ability_info_description = str_replace('{RECOVERY}', $ability_info_recovery, $ability_info_description);
        $ability_info_description = str_replace('{DAMAGE2}', $ability_info_damage2, $ability_info_description);
        $ability_info_description = str_replace('{RECOVERY2}', $ability_info_recovery2, $ability_info_description);
        $ability_info_title = rpg_ability::print_editor_title_markup($robot_info, $ability_info);
        $ability_info_title_plain = strip_tags(str_replace('<br />', '//', $ability_info_title));
        $ability_info_title_tooltip = htmlentities($ability_info_title, ENT_QUOTES, 'UTF-8');
        $ability_info_title_html = str_replace(' ', '&nbsp;', $ability_info_name);
        $temp_select_options = str_replace('value="'.$ability_info_token.'"', 'value="'.$ability_info_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);
        $ability_info_title_html = '<label style="background-image: url(i/a/'.$ability_info_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$ability_info_title_html.'<span class="arrow">&#8711;</span></label>';
        //if ($global_allow_editing){ $ability_info_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
        $this_select_markup = '<a class="ability_name ability_type ability_type_'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '').'" style="'.(!$global_allow_editing ? 'cursor: default; ' : '').'" data-id="'.$ability_info_id.'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="'.$ability_info_token.'" data-type="'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').'" data-type2="'.(!empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : '').'" title="'.$ability_info_title_plain.'" data-tooltip="'.$ability_info_title_tooltip.'">'.$ability_info_title_html.'</a>';

        // Return the generated select markup
        return $this_select_markup;
    }

    // Define a public function for recalculating internal counters
    public function update_variables(){

        // Update parent objects first
        //$this->robot->update_variables();

        // Calculate this ability's count variables
        //$this->counters['thing'] = count($this->robot_stuff);

        // Return true on success
        return true;

    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Request parent robot object to update as well
        //$this->robot->update_session();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['ABILITIES'][$this->robot->robot_id][$this->ability_id] = $this_data;
        $this->battle->values['abilities'][$this->ability_id] = $this_data;
        //$this->player->values['abilities'][$this->ability_id] = $this_data;
        //$this->robot->values['abilities'][$this->ability_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal ability fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_token' => $this->player_token,
            'robot_id' => $this->robot_id,
            'robot_token' => $this->robot_token,
            'ability_id' => $this->ability_id,
            'ability_key' => $this->ability_key,
            'ability_name' => $this->ability_name,
            'ability_token' => $this->ability_token,
            'ability_class' => $this->ability_class,
            'ability_subclass' => $this->ability_subclass,
            'ability_master' => $this->ability_master,
            'ability_number' => $this->ability_number,
            'ability_image' => $this->ability_image,
            'ability_image_size' => $this->ability_image_size,
            'ability_description' => $this->ability_description,
            'ability_type' => $this->ability_type,
            'ability_type2' => $this->ability_type2,
            'ability_energy' => $this->ability_energy,
            'ability_energy_percent' => $this->ability_energy_percent,
            'ability_speed' => $this->ability_speed,
            'ability_damage' => $this->ability_damage,
            'ability_damage2' => $this->ability_damage2,
            'ability_damage_percent' => $this->ability_damage_percent,
            'ability_damage2_percent' => $this->ability_damage2_percent,
            'ability_recovery' => $this->ability_recovery,
            'ability_recovery2' => $this->ability_recovery2,
            'ability_recovery_percent' => $this->ability_recovery_percent,
            'ability_recovery2_percent' => $this->ability_recovery2_percent,
            'ability_accuracy' => $this->ability_accuracy,
            'ability_target' => $this->ability_target,
            'ability_functions' => $this->ability_functions,
            'ability_results' => $this->ability_results,
            'ability_frame' => $this->ability_frame,
            'ability_frame_span' => $this->ability_frame_span,
            'ability_frame_index' => $this->ability_frame_index,
            'ability_frame_animate' => $this->ability_frame_animate,
            'ability_frame_offset' => $this->ability_frame_offset,
            'ability_frame_classes' => $this->ability_frame_classes,
            'ability_frame_styles' => $this->ability_frame_styles,
            'ability_base_name' => $this->ability_base_name,
            'ability_base_image' => $this->ability_base_image,
            'ability_base_image_size' => $this->ability_base_image_size,
            'ability_base_description' => $this->ability_base_description,
            'ability_base_type' => $this->ability_base_type,
            'ability_base_type2' => $this->ability_base_type2,
            'ability_base_energy' => $this->ability_base_energy,
            'ability_base_speed' => $this->ability_base_speed,
            'ability_base_damage' => $this->ability_base_damage,
            'ability_base_damage2' => $this->ability_base_damage2,
            'ability_base_recovery' => $this->ability_base_recovery,
            'ability_base_recovery2' => $this->ability_base_recovery2,
            'ability_base_accuracy' => $this->ability_base_accuracy,
            'ability_base_target' => $this->ability_base_target,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

    // Define a static function for printing out the ability's database markup
    public static function print_database_markup($ability_info, $print_options = array()){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url;
        global $mmrpg_database_abilities, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;
        global $this_database;

        // Collect global indexes for easier search
        $mmrpg_types = rpg_type::get_index();

        // Define the markup variable
        $this_markup = '';

        // Define the print style defaults
        if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
        if ($print_options['layout_style'] == 'website'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = true; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'event'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = false; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        }

        // Collect the ability sprite dimensions
        $ability_image_size = !empty($ability_info['ability_image_size']) ? $ability_info['ability_image_size'] : 40;
        $ability_image_size_text = $ability_image_size.'x'.$ability_image_size;
        $ability_image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];

        // Collect the ability's type for background display
        $ability_type_class = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
        if ($ability_type_class != 'none' && !empty($ability_info['ability_type2'])){ $ability_type_class .= '_'.$ability_info['ability_type2']; }
        elseif ($ability_type_class == 'none' && !empty($ability_info['ability_type2'])){ $ability_type_class = $ability_info['ability_type2'];  }
        $ability_header_types = 'ability_type_'.$ability_type_class.' ';
        // If this is a special category of item, it's a special type
        if (preg_match('/^item-score-ball-(red|blue|green|purple)$/i', $ability_info['ability_token'])){ $ability_info['ability_type_special'] = 'bonus'; }
        elseif (preg_match('/^item-super-(pellet|capsule)$/i', $ability_info['ability_token'])){ $ability_info['ability_type_special'] = 'multi'; }

        // Define the sprite sheet alt and title text
        $ability_sprite_size = $ability_image_size * 2;
        $ability_sprite_size_text = $ability_sprite_size.'x'.$ability_sprite_size;
        $ability_sprite_title = $ability_info['ability_name'];
        //$ability_sprite_title = $ability_info['ability_number'].' '.$ability_info['ability_name'];
        //$ability_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

        // Define the sprite frame index for robot images
        $ability_sprite_frames = array('frame_01','frame_02','frame_03','frame_04','frame_05','frame_06','frame_07','frame_08','frame_09','frame_10');

        // Limit any damage or recovery percents to 100%
        if (!empty($ability_info['ability_damage_percent']) && $ability_info['ability_damage'] > 100){ $ability_info['ability_damage'] = 100; }
        if (!empty($ability_info['ability_damage2_percent']) && $ability_info['ability_damage2'] > 100){ $ability_info['ability_damage2'] = 100; }
        if (!empty($ability_info['ability_recovery_percent']) && $ability_info['ability_recovery'] > 100){ $ability_info['ability_recovery'] = 100; }
        if (!empty($ability_info['ability_recovery2_percent']) && $ability_info['ability_recovery2'] > 100){ $ability_info['ability_recovery2'] = 100; }

        // Start the output buffer
        ob_start();
        ?>
        <div class="database_container database_<?= $ability_info['ability_class'] == 'item' ? 'item' : 'ability' ?>_container" data-token="<?= $ability_info['ability_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

            <?php if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?= $ability_info['ability_token']?>">&nbsp;</a>
            <?php endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $ability_info['ability_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

                <?php if($print_options['show_icon']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <?php if($print_options['show_icon']): ?>
                            <?php if($print_options['show_key'] !== false): ?>
                                <div class="icon ability_type <?= $ability_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                            <?php endif; ?>
                            <?php if ($ability_image_token != 'ability'){ ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: url(i/a/<?= $ability_image_token ?>/ir<?= $ability_image_size ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon"><?= $ability_info['ability_name']?>'s Mugshot</div></div>
                            <?php } else { ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon">No Image</div></div>
                            <?php } ?>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $ability_header_types ?> <?= (!$print_options['show_icon']) ? 'noicon' : '' ?>">
                        <?php if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="<?= preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $ability_info['ability_token']).'/' : 'database/abilities/'.$ability_info['ability_token'].'/' ?>"><?= $ability_info['ability_name'] ?></a>
                        <?php else: ?>
                            <?= $ability_info['ability_name'] ?>&#39;s Data
                        <?php endif; ?>
                        <?php if (!empty($ability_info['ability_type_special'])){ ?>
                            <div class="header_core ability_type"><?= ucfirst($ability_info['ability_type_special']) ?> Type</div>
                        <?php } elseif (!empty($ability_info['ability_type']) && !empty($ability_info['ability_type2'])){ ?>
                            <div class="header_core ability_type"><?= ucfirst($ability_info['ability_type']).' / '.ucfirst($ability_info['ability_type2']) ?> Type</div>
                        <?php } elseif (!empty($ability_info['ability_type'])){ ?>
                            <div class="header_core ability_type"><?= ucfirst($ability_info['ability_type']) ?> Type</div>
                        <?php } else { ?>
                            <div class="header_core ability_type">Neutral Type</div>
                        <?php } ?>
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px; <?= (!$print_options['show_icon']) ? 'margin-left: 0; ' : '' ?><?= $print_options['layout_style'] == 'event' ? 'font-size: 10px; min-height: 150px; ' : '' ?>">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="48%" />
                                <col width="1%" />
                                <col width="48%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Name :</label>
                                        <span class="ability_type ability_type_"><?= $ability_info['ability_name']?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Type :</label>
                                        <?php if($print_options['layout_style'] != 'event'): ?>
                                            <?php
                                            if (!empty($ability_info['ability_type_special'])){
                                                echo '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').$ability_info['ability_type_special'].'/').'" class="ability_type '.$ability_header_types.'">'.ucfirst($ability_info['ability_type_special']).'</a>';
                                            }
                                            elseif (!empty($ability_info['ability_type'])){
                                                $temp_string = array();
                                                $ability_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                                                $temp_string[] = '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').$ability_type.'/').'" class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_types[$ability_type]['type_name'].'</a>';
                                                if (!empty($ability_info['ability_type2'])){
                                                    $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                                    $temp_string[] = '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').$ability_type2.'/').'" class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_types[$ability_type2]['type_name'].'</a>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').'none/').'" class="ability_type ability_type_none">Neutral</a>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <?php
                                            if (!empty($ability_info['ability_type_special'])){
                                                echo '<span class="ability_type '.$ability_header_types.'">'.ucfirst($ability_info['ability_type_special']).'</span>';
                                            }
                                            elseif (!empty($ability_info['ability_type'])){
                                                $temp_string = array();
                                                $ability_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                                                $temp_string[] = '<span class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_types[$ability_type]['type_name'].'</span>';
                                                if (!empty($ability_info['ability_type2'])){
                                                    $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                                    $temp_string[] = '<span class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_types[$ability_type2]['type_name'].'</span>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<span class="ability_type ability_type_none">Neutral</span>';
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if($ability_info['ability_class'] != 'item'): ?>

                                    <?php if($ability_image_token != 'ability'): ?>

                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Power :</label>
                                                <?php if(!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])): ?>
                                                    <?php if(!empty($ability_info['ability_damage'])){ ?><span class="ability_stat"><?= $ability_info['ability_damage'].(!empty($ability_info['ability_damage_percent']) ? '%' : '') ?> Damage</span><?php } ?>
                                                    <?php if(!empty($ability_info['ability_recovery'])){ ?><span class="ability_stat"><?= $ability_info['ability_recovery'].(!empty($ability_info['ability_recovery_percent']) ? '%' : '') ?> Recovery</span><?php } ?>
                                                <?php else: ?>
                                                    <span class="ability_stat">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Accuracy :</label>
                                                <span class="ability_stat"><?= $ability_info['ability_accuracy'].'%' ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Energy :</label>
                                                <span class="ability_stat"><?= !empty($ability_info['ability_energy']) ? $ability_info['ability_energy'] : '-' ?></span>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Speed :</label>
                                                <span class="ability_stat"><?= !empty($ability_info['ability_speed']) ? $ability_info['ability_speed'] : '1' ?></span>
                                            </td>
                                        </tr>

                                    <?php else: ?>

                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Power :</label>
                                                <span class="ability_stat">-</span>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Accuracy :</label>
                                                <span class="ability_stat">-</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Energy :</label>
                                                <span class="ability_stat">-</span>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Speed :</label>
                                                <span class="ability_stat">-</span>
                                            </td>
                                        </tr>

                                    <?php endif; ?>

                                <?php endif; ?>
                            </tbody>
                        </table>
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <?php if($print_options['layout_style'] != 'event'): ?>
                                            <label style="display: block; float: left;">Description :</label>
                                        <?php endif; ?>
                                        <div class="description_container" style="white-space: normal; text-align: left; <?= $print_options['layout_style'] == 'event' ? 'font-size: 12px; ' : '' ?> "><?php
                                        // Define the search/replace pairs for the description
                                        $temp_find = array('{DAMAGE}', '{RECOVERY}', '{DAMAGE2}', '{RECOVERY2}', '{}');
                                        $temp_replace = array(
                                            (!empty($ability_info['ability_damage']) ? $ability_info['ability_damage'] : 0), // {DAMAGE}
                                            (!empty($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : 0), // {RECOVERY}
                                            (!empty($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : 0), // {DAMAGE2}
                                            (!empty($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : 0), // {RECOVERY2}
                                            '' // {}
                                            );
                                        echo !empty($ability_info['ability_description']) ? str_replace($temp_find, $temp_replace, $ability_info['ability_description']) : '&hellip;'
                                        ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_sprites'] && (!isset($ability_info['ability_image_sheets']) || $ability_info['ability_image_sheets'] !== 0) && $ability_image_token != 'ability' ): ?>

                    <?php
                    // Start the output buffer and prepare to collect sprites
                    ob_start();

                    // Define the alts we'll be looping through for this ability
                    $temp_alts_array = array();
                    $temp_alts_array[] = array('token' => '', 'name' => $ability_info['ability_name'], 'summons' => 0);
                    // Append predefined alts automatically, based on the ability image alt array
                    if (!empty($ability_info['ability_image_alts'])){
                        $temp_alts_array = array_merge($temp_alts_array, $ability_info['ability_image_alts']);
                    }
                    // Otherwise, if this is a copy ability, append based on all the types in the index
                    elseif ($ability_info['ability_type'] == 'copy' && preg_match('/^(mega-man|proto-man|bass)$/i', $ability_info['ability_token'])){
                        foreach ($mmrpg_database_types AS $type_token => $type_info){
                            if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
                            $temp_alts_array[] = array('token' => $type_token, 'name' => $ability_info['ability_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
                        }
                    }
                    // Otherwise, if this robot has multiple sheets, add them as alt options
                    elseif (!empty($ability_info['ability_image_sheets'])){
                        for ($i = 2; $i <= $ability_info['ability_image_sheets']; $i++){
                            $temp_alts_array[] = array('sheet' => $i, 'name' => $ability_info['ability_name'].' (Sheet #'.$i.')', 'summons' => 0);
                        }
                    }

                    // Loop through the alts and display images for them (yay!)
                    foreach ($temp_alts_array AS $alt_key => $alt_info){

                        // Define the current image token with alt in mind
                        $temp_ability_image_token = $ability_image_token;
                        $temp_ability_image_token .= !empty($alt_info['token']) ? '_'.$alt_info['token'] : '';
                        $temp_ability_image_token .= !empty($alt_info['sheet']) ? '-'.$alt_info['sheet'] : '';
                        $temp_ability_image_name = $alt_info['name'];
                        // Update the alt array with this info
                        $temp_alts_array[$alt_key]['image'] = $temp_ability_image_token;

                        // Collect the number of sheets
                        $temp_sheet_number = !empty($ability_info['ability_image_sheets']) ? $ability_info['ability_image_sheets'] : 1;

                        // Loop through the different frames and print out the sprite sheets
                        foreach (array('right', 'left') AS $temp_direction){
                            $temp_direction2 = substr($temp_direction, 0, 1);
                            $temp_embed = '[ability:'.$temp_direction.']{'.$temp_ability_image_token.'}';
                            $temp_title = $temp_ability_image_name.' | Icon Sprite '.ucfirst($temp_direction);
                            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                            $temp_label = 'Icon '.ucfirst(substr($temp_direction, 0, 1));
                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_ability_image_token.'" data-frame="icon" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$ability_sprite_size.'px; height: '.$ability_sprite_size.'px; overflow: hidden;">';
                                echo '<img style="margin-left: 0;" data-tooltip="'.$temp_title.'" src="i/a/'.$temp_ability_image_token.'/i'.$temp_direction2.$ability_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                            echo '</div>';
                        }


                        // Loop through the different frames and print out the sprite sheets
                        foreach ($ability_sprite_frames AS $this_key => $this_frame){
                            $margin_left = ceil((0 - $this_key) * $ability_sprite_size);
                            $frame_relative = $this_frame;
                            //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($ability_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                            $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_direction2 = substr($temp_direction, 0, 1);
                                $temp_embed = '[ability:'.$temp_direction.':'.$frame_relative.']{'.$temp_ability_image_token.'}';
                                $temp_title = $temp_ability_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                //$image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
                                //if ($temp_sheet > 1){ $temp_ability_image_token .= '-'.$temp_sheet; }
                                echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_ability_image_token.'" data-frame="'.$frame_relative.'" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$ability_sprite_size.'px; height: '.$ability_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="i/a/'.$temp_ability_image_token.'/s'.$temp_direction2.$ability_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                        }
                    }

                    // Collect the sprite markup from the output buffer for later
                    $this_sprite_markup = ob_get_clean();

                    ?>

                    <h2 id="sprites" class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $ability_info['ability_name']?>&#39;s Sprites
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
                                        $alt_type = 'ability_type ability_type_'.$alt_type.' core_type ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key + 1; //$alt_key == 0 ? $ability_info['ability_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : ''); //$alt_key == 0 ? $ability_info['ability_name'] : $ability_info['ability_name'].' Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'ability_type ability_type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($ability_info['ability_type'] == 'copy' && $alt_key == 0){ $alt_type = 'ability_type ability_type_empty '; }
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
                        if (!empty($ability_info['ability_image_editor'])){
                            if ($ability_info['ability_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                            elseif ($ability_info['ability_image_editor'] == 110){ $temp_editor_title = 'MetalMarioX100 / EliteP1'; }
                            elseif ($ability_info['ability_image_editor'] == 18){ $temp_editor_title = 'Sean Adamson / MetalMan'; }
                        } elseif ($ability_image_token != 'ability'){
                            $temp_editor_title = 'Adrian Marceau / Ageman20XX';
                        }
                        ?>
                        <p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 10px; margin-top: 6px;">Sprite Editing by <strong><?= $temp_editor_title ?></strong> <span style="color: #565656;"> | </span> Original Artwork by <strong>Capcom</strong></p>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_robots'] && $ability_info['ability_class'] != 'item'): ?>

                    <h2 class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $ability_info['ability_name']?>&#39;s Robots
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 2px 3px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="robot_container">
                                        <?php
                                        $ability_type_one = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : false;
                                        $ability_type_two = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : false;
                                        $ability_robot_rewards = array();
                                        $ability_robot_rewards_level = array();
                                        $ability_robot_rewards_core = array();
                                        $ability_robot_rewards_player = array();

                                        // Loop through and remove any robots that do not learn the ability
                                        foreach ($mmrpg_database_robots AS $robot_token => $robot_info){

                                            // Define the match flah to prevent doubling up
                                            $temp_match_flag = false;

                                            // Loop through this robot's ability rewards one by one
                                            foreach ($robot_info['robot_rewards']['abilities'] AS $temp_info){
                                                // If the temp info's type token matches this ability
                                                if ($temp_info['token'] == $ability_info['ability_token']){
                                                    // Add this ability to the rewards list
                                                    $ability_robot_rewards_level[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => $temp_info['level']));
                                                    $temp_match_flag = true;
                                                    break;
                                                }
                                            }

                                            // If a type match was found, continue
                                            if ($temp_match_flag){ continue; }

                                            // If this ability's type matches the robot's first
                                            if (!empty($robot_info['robot_core']) && ($robot_info['robot_core'] == $ability_type_one || $robot_info['robot_core'] == $ability_type_two)){
                                                // Add this ability to the rewards list
                                                $ability_robot_rewards_core[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'core'));
                                                continue;
                                            }

                                            // If this ability's type matches the robot's second
                                            if (!empty($robot_info['robot_core2']) && ($robot_info['robot_core2'] == $ability_type_one || $robot_info['robot_core2'] == $ability_type_two)){
                                                // Add this ability to the rewards list
                                                $ability_robot_rewards_core[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'core'));
                                                continue;
                                            }

                                            // If a type match was found, continue
                                            if ($temp_match_flag){ continue; }

                                            // If this ability's in the robot's list of player-only abilities
                                            if (
                                                (!empty($robot_info['robot_abilities']) && in_array($ability_info['ability_token'], $robot_info['robot_abilities'])) ||
                                                (!empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy') ||
                                                (!empty($robot_info['robot_core2']) && $robot_info['robot_core2'] == 'copy')
                                                ){
                                                // Add this ability to the rewards list
                                                $ability_robot_rewards_player[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'player'));
                                                continue;
                                            }

                                            // If a type match was found, continue
                                            if ($temp_match_flag){ continue; }

                                        }

                                        // Combine the arrays together into one
                                        $ability_robot_rewards = array_merge($ability_robot_rewards_level, $ability_robot_rewards_core, $ability_robot_rewards_player);

                                        // Loop through the collected robots if there are any
                                        if (!empty($ability_robot_rewards)){
                                            $temp_string = array();
                                            $robot_key = 0;
                                            $robot_method_key = 0;
                                            $robot_method = '';
                                            $temp_global_abilities = self::get_global_abilities();
                                            foreach ($ability_robot_rewards AS $this_info){
                                                $this_level = $this_info['level'];
                                                $this_robot = $mmrpg_database_robots[$this_info['token']];
                                                $this_robot_token = $this_robot['robot_token'];
                                                $this_robot_name = $this_robot['robot_name'];
                                                $this_robot_image = !empty($this_robot['robot_image']) ? $this_robot['robot_image']: $this_robot['robot_token'];
                                                $this_robot_energy = !empty($this_robot['robot_energy']) ? $this_robot['robot_energy'] : 0;
                                                $this_robot_attack = !empty($this_robot['robot_attack']) ? $this_robot['robot_attack'] : 0;
                                                $this_robot_defense = !empty($this_robot['robot_defense']) ? $this_robot['robot_defense'] : 0;
                                                $this_robot_speed = !empty($this_robot['robot_speed']) ? $this_robot['robot_speed'] : 0;
                                                $this_robot_method = 'level';
                                                $this_robot_method_text = 'Level Up';
                                                $this_robot_title_html = '<strong class="name">'.$this_robot_name.'</strong>';
                                                if (is_numeric($this_level)){
                                                    if ($this_level > 1){ $this_robot_title_html .= '<span class="level">Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).'</span>'; }
                                                    else { $this_robot_title_html .= '<span class="level">Start</span>'; }
                                                } else {
                                                    if ($this_level == 'core'){
                                                        $this_robot_method = 'core';
                                                        $this_robot_method_text = 'Core Match';
                                                    } elseif ($this_level == 'player'){
                                                        $this_robot_method = 'player';
                                                        $this_robot_method_text = 'Player Only';
                                                    }
                                                    $this_robot_title_html .= '<span class="level">&nbsp;</span>';
                                                }
                                                $this_stat_base_total = $this_robot_energy + $this_robot_attack + $this_robot_defense + $this_robot_speed;
                                                $this_stat_width_total = 84;
                                                if (!empty($this_robot['robot_core'])){ $this_robot_title_html .= '<span class="robot_core type_'.$this_robot['robot_core'].'">'.ucwords($this_robot['robot_core'].(!empty($this_robot['robot_core2']) ? ' / '.$this_robot['robot_core2'] : '')).' Core</span>'; }
                                                else { $this_robot_title_html .= '<span class="robot_core type_none">Neutral Core</span>'; }
                                                $this_robot_title_html .= '<span class="class">'.(!empty($this_robot['robot_description']) ? $this_robot['robot_description'] : '&hellip;').'</span>';
                                                if (!empty($this_robot_speed)){ $temp_speed_width = floor($this_stat_width_total * ($this_robot_speed / $this_stat_base_total)); }
                                                if (!empty($this_robot_defense)){ $temp_defense_width = floor($this_stat_width_total * ($this_robot_defense / $this_stat_base_total)); }
                                                if (!empty($this_robot_attack)){ $temp_attack_width = floor($this_stat_width_total * ($this_robot_attack / $this_stat_base_total)); }
                                                if (!empty($this_robot_energy)){ $temp_energy_width = $this_stat_width_total - ($temp_speed_width + $temp_defense_width + $temp_attack_width); }
                                                if (!empty($this_robot_energy)){ $this_robot_title_html .= '<span class="energy robot_type robot_type_energy" style="width: '.$temp_energy_width.'%;" title="'.$this_robot_energy.' Energy">'.$this_robot_energy.'</span>'; }
                                                if (!empty($this_robot_attack)){ $this_robot_title_html .= '<span class="attack robot_type robot_type_attack" style="width: '.$temp_attack_width.'%;" title="'.$this_robot_attack.' Attack">'.$this_robot_attack.'</span>'; }
                                                if (!empty($this_robot_defense)){ $this_robot_title_html .= '<span class="defense robot_type robot_type_defense" style="width: '.$temp_defense_width.'%;" title="'.$this_robot_defense.' Defense">'.$this_robot_defense.'</span>'; }
                                                if (!empty($this_robot_speed)){ $this_robot_title_html .= '<span class="speed robot_type robot_type_speed" style="width: '.$temp_speed_width.'%;" title="'.$this_robot_speed.' Speed">'.$this_robot_speed.'</span>'; }
                                                $this_robot_sprite_size = !empty($this_robot['robot_image_size']) ? $this_robot['robot_image_size'] : 40;
                                                $this_robot_sprite_path = 'images/robots/'.$this_robot_image.'/mug_left_'.$this_robot_sprite_size.'x'.$this_robot_sprite_size.'.png';
                                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_robot_sprite_path)){ $this_robot_image = 'robot'; $this_robot_sprite_path = 'i/r/robot/ml40.png'; }
                                                else { $this_robot_sprite_path = 'i/r/'.$this_robot_image.'/ml'.$this_robot_sprite_size.'.png'; }
                                                if ($this_robot_image != 'robot'){ $this_robot_sprite_html = '<span class="mug"><img class="size_'.$this_robot_sprite_size.'x'.$this_robot_sprite_size.'" src="'.$this_robot_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_robot_name.' Mug" /></span>'; }
                                                else { $this_robot_sprite_html = '<span class="mug"></span>'; }
                                                $this_robot_title_html = '<span class="label">'.$this_robot_title_html.'</span>';
                                                //$this_robot_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_robot_title_html;
                                                if ($robot_method != $this_robot_method){
                                                    if ($this_robot_method == 'level' && $ability_info['ability_token'] == 'buster-shot'){ continue; }
                                                    $temp_separator = '<div class="robot_separator">'.$this_robot_method_text.'</div>';
                                                    $temp_string[] = $temp_separator;
                                                    $robot_method = $this_robot_method;
                                                    $robot_method_key++;
                                                    // Print out the disclaimer if a global ability
                                                    if (in_array($ability_info['ability_token'], $temp_global_abilities)){
                                                        $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$ability_info['ability_name'].' can be equipped by <em>any</em> robot master!</div>';
                                                    }
                                                }
                                                // If this is a global ability, don't bother showing EVERY compatible robot
                                                if ($this_robot_method == 'level' && $ability_info['ability_token'] == 'buster-shot' || $this_robot_method != 'level' && in_array($ability_info['ability_token'], $temp_global_abilities)){ continue; }
                                                if ($this_level >= 0){
                                                    //title="'.$this_robot_title_plain.'"
                                                    $temp_markup = '<a href="'.MMRPG_CONFIG_ROOTURL.'database/robots/'.$this_robot['robot_token'].'/"  class="robot_name robot_type robot_type_'.(!empty($this_robot['robot_core']) ? $this_robot['robot_core'].(!empty($this_robot['robot_core2']) ? '_'.$this_robot['robot_core2'] : '') : 'none').'" style="'.($this_robot_image == 'robot' ? 'opacity: 0.3; ' : '').'">';
                                                    $temp_markup .= '<span class="chrome">'.$this_robot_sprite_html.$this_robot_title_html.'</span>';
                                                    $temp_markup .= '</a>';
                                                    $temp_string[] = $temp_markup;
                                                    $robot_key++;
                                                    continue;
                                                }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_ability robot_type_none"><span class="chrome">Neutral</span></span>';
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $ability_info['ability_token']).'/' : 'database/abilities/'.$ability_info['ability_token'].'/' ?>" rel="permalink">+ Permalink</a>

                <?php elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $ability_info['ability_token']).'/' : 'database/abilities/'.$ability_info['ability_token'].'/' ?>" rel="permalink">+ View More</a>

                <?php endif; ?>

            </div>
        </div>
        <?php
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

    // Define a static function to use as the common action for all item-shard-___ abilities
    public static function item_function_shard($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 60, 40, 99,
                $this_player->print_name().' hands over an item from the inventory&hellip; <br />'.
                $this_robot->print_name().' throws a '.$this_ability->print_name().' at the target!'
                )
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -90, 0, 10, $target_robot->print_name().' was hit by the '.$this_ability->print_name().'!'),
            'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(0, -60, 0, 10, $target_robot->print_name().' absorbed the '.$this_ability->print_name().'!'),
            'failure' => array(0, -90, 0, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_energy * 0.25);
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all ____-shot abilities
    public static function ability_function_shot($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

        // Extract all objects into the current scope
        extract($objects);

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 105, 0, 10, $this_robot->print_name().' fires '.(preg_match('/^(a|e|i|o|u)/i', $this_ability->ability_token) ? 'an' : 'a').'  '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(0, -60, 0, 10, 'The '.$shot_text.' shot '.$damage_text.' the target!'),
            'failure' => array(0, -60, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(0, -60, 0, 10, 'The '.$shot_text.' shot '.$recovery_text.' the target!'),
            'failure' => array(0, -60, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-shot abilities
    public static function ability_function_onload_shot($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Loop through any attachments and boost power for each buster charge
        $temp_new_damage = $this_ability->ability_base_damage;
        foreach ($this_robot->robot_attachments AS $this_attachment_token => $this_attachment_info){
            if ($this_attachment_token == 'ability_'.$this_ability->ability_type.'-buster'){
                $temp_new_damage += 2;
            }
        }
        // Update the ability's damage with the new amount
        $this_ability->set_damage($temp_new_damage);

        // If this robot is holding a Target Module, allow target selection
        if ($this_robot->robot_item == 'item-target-module'){
            $this_ability->set_target('select_target');
        } else {
            $this_ability->reset_target();
        }

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all ____-buster abilities
    public static function ability_function_buster($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_offset = array('x' => -10, 'y' => -10, 'z' => -20);
        $this_attachment_boost_modifier = 1 + ($this_ability->ability_recovery2 / 100);
        $this_attachment_break_modifier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(1, 2, 1, 0),
            'ability_frame_offset' => $this_attachment_offset,
            'attachment_damage_output_booster_'.$this_ability->ability_type => $this_attachment_boost_modifier,
            'attachment_damage_input_breaker_'.$this_ability->ability_type => $this_attachment_break_modifier,
            'attachment_recovery_output_booster_'.$this_ability->ability_type => $this_attachment_boost_modifier,
            'attachment_recovery_input_breaker_'.$this_ability->ability_type => $this_attachment_break_modifier
            );

        // Loop through each existing attachment and alter the start frame by one
        foreach ($this_robot->robot_attachments AS $key => $info){
            // Move the start frame to the end of the queue so it doesn't overlay with others
            $temp_first = array_shift($this_attachment_info['ability_frame_animate']);
            array_push($this_attachment_info['ability_frame_animate'], $temp_first);
            // Move this attachment's x, y, and z positionslightly left and up for the same reason
            $this_attachment_offset['x'] -= 2;
            $this_attachment_offset['y'] -= 2;
            $this_attachment_offset['z'] -= 1;
            $this_attachment_info['ability_frame_offset'] = $this_attachment_offset;
        }

        // Define the charge required flag based on existing attachments of this ability
        $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;
        // If this robot is holding a charge module, bypass changing and set to false
        if (!empty($this_robot->robot_item) && $this_robot->robot_item == 'item-charge-module'){ $this_charge_required = false; }

        // If the ability flag was not set, this ability begins charging
        if ($this_charge_required){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

        }
        // Else if the ability flag was set, the ability is released at the target
        else {

            // Update this ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'shoot',
                'kickback' => array(-5, 0, 0),
                'success' => array(3, 100, -15, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Remove this ability attachment to the robot using it
            $this_attachment_info = $this_robot->get_attachment($this_attachment_token);
            $this_attachment_info['ability_frame'] = 0;
            $this_attachment_info['ability_frame_animate'] = array(1, 0);
            $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(3, -110, -15, 10, 'A powerful '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$damage_text.' the target!'),
                'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(3, -110, -15, 10, 'The '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$recovery_text.' the target!'),
                'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Remove this ability attachment to the robot using it
            $this_robot->unset_attachment($this_attachment_token);

        }

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-buster abilities
    public static function ability_function_onload_buster($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // Define the charge required flag based on existing attachments of this ability
        $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;
        // If this robot is holding a Charge Module, bypass changing and set to false
        if ($this_robot->robot_item == 'item-charge-module'){ $this_charge_required = false; }

        // If the ability flag had already been set, reduce the weapon energy to zero
        if (!$this_charge_required){ $this_ability->set_energy(0); }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->reset_energy(); }

        // If this robot is holding a Charge Module, cut power in half
        if ($this_robot->robot_item == 'item-charge-module'){
            $this_ability->set_damage(ceil($this_ability->ability_base_damage / 2));
        } else {
            $this_ability->reset_damage();
        }

        // If this robot is holding a Target Module, allow target selection
        if ($this_robot->robot_item == 'item-target-module'){
            if (!$this_charge_required){ $this_ability->set_target('select_target'); }
            else { $this_ability->reset_target(); }
        } else {
            $this_ability->reset_target();
        }

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all ____-overdrive abilities
    public static function ability_function_overdrive($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

        // Extract all objects into the current scope
        extract($objects);

        // Decrease this robot's weapon energy to zero
        $this_robot->set_weapons(0);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'kickback' => array(-5, 0, 0),
            'frame' => 'defend',
            'success' => array(0, 15, 45, 10, $this_robot->print_name().' uses the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Define this ability's attachment token
        $crest_attachment_token = 'ability_'.$this_ability->ability_token;
        $crest_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(1,2),
            'ability_frame_offset' => array('x' => 20, 'y' => 50, 'z' => 10),
            'ability_frame_classes' => ' ',
            'ability_frame_styles' => ' '
            );

        // Add the ability crest attachment
        $this_robot->set_frame('summon');
        $this_robot->set_attachment($crest_attachment_token, $crest_attachment_info);


        // Define this ability's attachment token
        $overlay_attachment_token = 'system_fullscreen-black';
        $overlay_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => 'fullscreen-black',
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -12),
            'ability_frame_classes' => 'sprite_fullscreen '
            );

        // Add the black overlay attachment
        $target_robot->set_attachment($overlay_attachment_token, $overlay_attachment_info);

        // prepare the ability options
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(3, -60, -15, 10, 'A powerful '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$damage_text.' '.$target_robot->print_name().'!'),
            'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed '.$target_robot->print_name().'&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(20, 0, 0),
            'success' => array(3, -60, -15, 10, 'The '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$recovery_text.' '.$target_robot->print_name().'!'),
            'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed '.$target_robot->print_name().'&hellip;')
            ));
        // Inflict damage on the opposing robot
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
        // Remove the black overlay attachment
        $target_robot->unset_attachment($overlay_attachment_token);

        // Loop through the target's benched robots, inflicting half base damage to each
        $backup_robots_active = $target_player->values['robots_active'];
        foreach ($backup_robots_active AS $key => $info){
            if ($info['robot_id'] == $target_robot->robot_id){ continue; }
            $this_ability->ability_results_reset();
            $temp_target_robot = new rpg_robot($target_player, $info);
            // Add the black overlay attachment
            $overlay_attachment_info['ability_frame_offset']['z'] -= 2;
            $temp_target_robot->set_attachment($overlay_attachment_token, $overlay_attachment_info);
            // Update the ability options
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(3, -60, -15, 10, 'A powerful '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$damage_text.' '.$temp_target_robot->print_name().'!'),
                'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed '.$temp_target_robot->print_name().'&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(20, 0, 0),
                'success' => array(3, -60, -15, 10, 'The '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$recovery_text.' '.$temp_target_robot->print_name().'!'),
                'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed '.$temp_target_robot->print_name().'&hellip;')
                ));
            //$energy_damage_amount = ceil($this_ability->ability_damage / $target_robots_active);
            $energy_damage_amount = $this_ability->ability_damage;
            $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
            $temp_target_robot->unset_attachment($overlay_attachment_token);
        }

        // Remove the black background attachment
        $this_robot->set_frame('base');
        $this_robot->unset_attachment($crest_attachment_token);

        // Trigger the disabled event on the targets now if necessary
        if ($target_robot->robot_energy <= 0 || $target_robot->robot_status == 'disabled'){ $target_robot->trigger_disabled($this_robot, $this_ability); }
        foreach ($backup_robots_active AS $key => $info){
            if ($info['robot_id'] == $target_robot->robot_id){ continue; }
            $temp_target_robot = new rpg_robot($target_player, $info);
            if ($temp_target_robot->robot_energy <= 0 || $temp_target_robot->robot_status == 'disabled'){ $temp_target_robot->trigger_disabled($this_robot, $this_ability); }
        }

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-overdrive abilities
    public static function ability_function_onload_overdrive($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update this abilities weapon energy to whatever the user currently has
        $ability_weapon_energy = $this_robot->robot_weapons;
        if ($ability_weapon_energy < 1){ $ability_weapon_energy = 1; }
        if ($this_ability->ability_type == $this_robot->robot_core){ $ability_weapon_energy = $ability_weapon_energy * 2; }
        $this_ability->set_energy($ability_weapon_energy);

        // Calculate the user's current life damage percent for calculations
        $robot_energy_damage = $this_robot->robot_base_energy - $this_robot->robot_energy;
        $robot_energy_damage_percent = !empty($robot_energy_damage) ? ceil(($robot_energy_damage / $this_robot->robot_base_energy) * 100) : 0;

        // Multiply the user's damage by the remaining weapon energy for damage total
        $ability_damage_amount = ceil($this_robot->robot_weapons * (1 + $robot_energy_damage_percent));
        $this_ability->set_damage($ability_damage_amount);

        // Return true on success
        return true;

    }



    // Define a static function to use as the common action for all elemental stat boost abilities
    public static function ability_function_elemental_stat_boost($objects, $stat_type, $stat_noun, $recovery_text, $damage_text){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(2, 0, -20, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Decrease the target robot's attack stat
        $this_ability->recovery_options_update(array(
            'kind' => $stat_type,
            'frame' => 'taunt',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(3, 0, 5, -10, $this_robot->print_name().'&#39;s '.$stat_noun.' were '.$recovery_text.'!'),
            'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_name().'&hellip;')
            ), true);
        $this_ability->damage_options_update(array(
            'kind' => $stat_type,
            'frame' => 'damage',
            'percent' => true,
            'kickback' => array(0, 0, 0),
            'success' => array(3, 0, 5, -10, $this_robot->print_name().'&#39;s '.$stat_noun.' were '.$damage_text.'!'),
            'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_name().'&hellip;')
            ), true);

        $recovery_base_amount = $this_ability->ability_recovery;
        if ($stat_type == 'attack'){ $stat_base_amount = $this_robot->robot_attack; }
        elseif ($stat_type == 'defense'){ $stat_base_amount = $this_robot->robot_defense; }
        elseif ($stat_type == 'speed'){ $stat_base_amount = $this_robot->robot_speed; }
        $stat_recovery_amount = ceil($stat_base_amount * ($recovery_base_amount / 100));

        $trigger_options = array('apply_stat_modifiers' => false);
        $this_robot->trigger_recovery($this_robot, $this_ability, $stat_recovery_amount, $trigger_options);

        // Return true on success
        return true;

    }
    // Define alias functions for each stat in attack/defense/speed stats for cleaner function calls
    public static function ability_function_elemental_attack_boost($objects, $recovery_text = 'recovered', $damage_text = 'damaged'){
        return self::ability_function_elemental_stat_boost($objects, 'attack', 'weapon systems', $shot_text, $recovery_text, $damage_text);
    }
    public static function ability_function_elemental_defense_boost($objects, $recovery_text = 'recovered', $damage_text = 'damaged'){
        return self::ability_function_elemental_stat_boost($objects, 'defense', 'shield systems', $shot_text, $recovery_text, $damage_text);
    }
    public static function ability_function_elemental_speed_boost($objects, $recovery_text = 'recovered', $damage_text = 'damaged'){
        return self::ability_function_elemental_stat_boost($objects, 'speed', 'mobility systems', $shot_text, $recovery_text, $damage_text);
    }

    // Define a static onload function to use as the common action for all elemental stat boost abilities
    public static function ability_function_onload_elemental_stat_boost($objects){

        // Return true on success
        return true;

    }
    // Define alias functions for each stat in attack/defense/speed stats for cleaner function calls
    public static function ability_function_onload_elemental_attack_boost($objects){
        return self::ability_function_onload_elemental_stat_boost($objects);
    }
    public static function ability_function_onload_elemental_defense_boost($objects){
        return self::ability_function_onload_elemental_stat_boost($objects);
    }
    public static function ability_function_onload_elemental_speed_boost($objects){
        return self::ability_function_onload_elemental_stat_boost($objects);
    }


    // Define a static function to use as the common action for all elemental stat break abilities
    public static function ability_function_elemental_stat_break($objects, $stat_type, $stat_noun, $damage_text = 'damaged', $recovery_text = 'recovered'){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 85, 0, 10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Decrease the target robot's attack stat
        $this_ability->damage_options_update(array(
            'kind' => $stat_type,
            'frame' => 'damage',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(1, -50, 0, 10, $target_robot->print_name().'&#39;s '.$stat_noun.' were '.$damage_text.'!'),
            'failure' => array(0, -75, 0, -10, 'It had no effect on '.$target_robot->print_name().'&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => $stat_type,
            'frame' => 'taunt',
            'percent' => true,
            'kickback' => array(0, 0, 0),
            'success' => array(1, -50, 0, 10, $target_robot->print_name().'&#39;s '.$stat_noun.' were '.$recovery_text.'!'),
            'failure' => array(0, -75, 0, -10, 'It had no effect on '.$target_robot->print_name().'&hellip;')
            ));

        $damage_base_amount = $this_ability->ability_damage;
        if ($stat_type == 'attack'){ $stat_base_amount = $this_robot->robot_attack; }
        elseif ($stat_type == 'defense'){ $stat_base_amount = $this_robot->robot_defense; }
        elseif ($stat_type == 'speed'){ $stat_base_amount = $this_robot->robot_speed; }
        $stat_damage_amount = ceil($stat_base_amount * ($damage_base_amount / 100));

        $trigger_options = array('apply_stat_modifiers' => false);
        $target_robot->trigger_damage($this_robot, $this_ability, $stat_damage_amount, $trigger_options);

        // Return true on success
        return true;

    }
    // Define alias functions for each stat in attack/defense/speed stats for cleaner function calls
    public static function ability_function_elemental_attack_break($objects, $damage_text = 'damaged', $recovery_text = 'recovered'){
        return self::ability_function_elemental_stat_break($objects, 'attack', 'weapon systems', $damage_text, $recovery_text);
    }
    public static function ability_function_elemental_defense_break($objects, $damage_text = 'damaged', $recovery_text = 'recovered'){
        return self::ability_function_elemental_stat_break($objects, 'defense', 'shield systems', $damage_text, $recovery_text);
    }
    public static function ability_function_elemental_speed_break($objects, $damage_text = 'damaged', $recovery_text = 'recovered'){
        return self::ability_function_elemental_stat_break($objects, 'speed', 'mobility systems', $damage_text, $recovery_text);
    }

    // Define a static onload function to use as the common action for all elemental stat break abilities
    public static function ability_function_onload_elemental_stat_break($objects){

        // Return true on success
        return true;

    }
    // Define alias functions for each stat in attack/defense/speed stats for cleaner function calls
    public static function ability_function_onload_elemental_attack_break($objects){
        return self::ability_function_onload_elemental_stat_break($objects);
    }
    public static function ability_function_onload_elemental_defense_break($objects){
        return self::ability_function_onload_elemental_stat_break($objects);
    }
    public static function ability_function_onload_elemental_speed_break($objects){
        return self::ability_function_onload_elemental_stat_break($objects);
    }

    // Define a static function to use as the common action for all item-core-___ abilities
    public static function item_function_core($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 60, 40, 99,
                $this_player->print_name().' hands over an item from the inventory&hellip; <br />'.
                $this_robot->print_name().' throws a '.$this_ability->print_name().' at the target!'
                )
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -90, 0, 10, $target_robot->print_name().' was hit by the '.$this_ability->print_name().'!'),
            'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(0, -60, 0, 10, $target_robot->print_name().' absorbed the '.$this_ability->print_name().'!'),
            'failure' => array(0, -90, 0, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_energy * 0.10);
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

    }

    // Define a static function that returns a list of globally compatible abilities
    public static function get_global_abilities(){
        // Define the list of global abilities
        $temp_global_abilities = array(
            'buster-shot', 'buster-charge', 'buster-relay',
            'light-buster', 'wily-buster', 'cossack-buster',
            'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost',
            'energy-break', 'attack-break', 'defense-break', 'speed-break',
            'energy-swap', 'attack-swap', 'defense-swap', 'speed-swap',
            'repair-mode', 'attack-mode', 'defense-mode', 'speed-mode',
            'field-support', 'mecha-support'
            );
        // Return the list of global abilities
        return $temp_global_abilities;
    }

}
?>