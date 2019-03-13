<?
/**
 * Mega Man RPG Ability Object
 * <p>The base class for all ability objects in the Mega Man RPG Prototype.</p>
 */
class rpg_ability extends rpg_object {

    // Define the constructor class
    public function rpg_ability(){

        // Update the session keys for this object
        $this->session_key = 'ABILITIES';
        $this->session_token = 'ability_token';
        $this->session_id = 'ability_id';
        $this->class = 'ability';
        $this->multi = 'abilities';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal battle pointer
        $this->battle = isset($args[0]) ? $args[0] : $GLOBALS['this_battle'];
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Define the internal player values using the provided array
        $this->player = isset($args[1]) ? $args[1] : $GLOBALS['this_player'];
        $this->player_id = $this->player->player_id;
        $this->player_token = $this->player->player_token;

        // Define the internal player values using the provided array
        $this->robot = isset($args[2]) ? $args[2] : $GLOBALS['this_robot'];
        $this->robot_id = $this->robot->robot_id;
        $this->robot_token = $this->robot->robot_token;

        // Collect current ability data from the function if available
        $this_abilityinfo = isset($args[3]) ? $args[3] : array('ability_id' => 0, 'ability_token' => 'ability');

        if (!is_array($this_abilityinfo)){
            die('!is_array($this_abilityinfo){ '.print_r($this_abilityinfo, true)).' }';
        }

        // Now load the ability data from the session or index
        $this->ability_load($this_abilityinfo);

        // Update the session by default
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a public function for manually loading data
    public function ability_load($this_abilityinfo){

        // Collect ability index info in case we need it
        $this_indexinfo = self::get_index_info($this_abilityinfo['ability_token']);

        // If the ability info was not an array, return false
        if (!is_array($this_abilityinfo)){ return false; }
        // If the ability ID was not provided, return false
        //if (!isset($this_abilityinfo['ability_id'])){ return false; }
        if (!isset($this_abilityinfo['ability_id'])){ $this_abilityinfo['ability_id'] = 0; }
        // If the ability token was not provided, return false
        if (!isset($this_abilityinfo['ability_token'])){ return false; }

        // If this is a special system ability, hard-code its ID, otherwise base off robot
        $temp_system_abilities = array('attachment-defeat');
        if (in_array($this_abilityinfo['ability_token'], $temp_system_abilities)){
            $this_abilityinfo['ability_id'] = $this->player_id.'000';
        }
        // Otherwise base the ID off of the robot
        else {
            $ability_id = $this->robot_id.str_pad($this_indexinfo['ability_id'], 3, '0', STR_PAD_LEFT);
            if (!empty($this_abilityinfo['flags']['is_attachment'])){
                if (isset($this_abilityinfo['attachment_token'])){ $ability_id .= 'x'.strtoupper(substr(md5($this_abilityinfo['attachment_token']), 0, 3)); }
                else { $ability_id .= substr(md5($this_abilityinfo['ability_token']), 0, 3); }
            }
            $this_abilityinfo['ability_id'] = $ability_id;
        }

        // Collect current ability data from the session if available
        $this_abilityinfo_backup = $this_abilityinfo;
        if (isset($_SESSION['ABILITIES'][$this_abilityinfo['ability_id']])){
            $this_abilityinfo = $_SESSION['ABILITIES'][$this_abilityinfo['ability_id']];
        }
        // Otherwise, collect ability data from the index if not already
        elseif (!in_array($this_abilityinfo['ability_token'], $temp_system_abilities)){
            $temp_backup_id = $this_abilityinfo['ability_id'];
            if (empty($this_abilityinfo_backup['_parsed'])){
                $this_abilityinfo = array_replace($this_indexinfo, $this_abilityinfo_backup);
            }
        }

        // Define the internal ability values using the provided array
        $this->flags = isset($this_abilityinfo['flags']) ? $this_abilityinfo['flags'] : array();
        $this->counters = isset($this_abilityinfo['counters']) ? $this_abilityinfo['counters'] : array();
        $this->values = isset($this_abilityinfo['values']) ? $this_abilityinfo['values'] : array();
        $this->history = isset($this_abilityinfo['history']) ? $this_abilityinfo['history'] : array();
        $this->ability_id = isset($this_abilityinfo['ability_id']) ? $this_abilityinfo['ability_id'] : 0;
        $this->ability_name = isset($this_abilityinfo['ability_name']) ? $this_abilityinfo['ability_name'] : 'Ability';
        $this->ability_token = isset($this_abilityinfo['ability_token']) ? $this_abilityinfo['ability_token'] : 'ability';
        $this->ability_class = isset($this_abilityinfo['ability_class']) ? $this_abilityinfo['ability_class'] : 'master';
        $this->ability_image = isset($this_abilityinfo['ability_image']) ? $this_abilityinfo['ability_image'] : $this->ability_token;
        $this->ability_image2 = isset($this_abilityinfo['ability_image2']) ? $this_abilityinfo['ability_image2'] : '';
        $this->ability_image_size = isset($this_abilityinfo['ability_image_size']) ? $this_abilityinfo['ability_image_size'] : 40;
        $this->ability_description = isset($this_abilityinfo['ability_description']) ? $this_abilityinfo['ability_description'] : '';
        $this->ability_type = isset($this_abilityinfo['ability_type']) ? $this_abilityinfo['ability_type'] : '';
        $this->ability_type2 = isset($this_abilityinfo['ability_type2']) ? $this_abilityinfo['ability_type2'] : '';
        $this->ability_speed = isset($this_abilityinfo['ability_speed']) ? $this_abilityinfo['ability_speed'] : 1;
        $this->ability_energy = isset($this_abilityinfo['ability_energy']) ? $this_abilityinfo['ability_energy'] : 4;
        $this->ability_damage = isset($this_abilityinfo['ability_damage']) ? $this_abilityinfo['ability_damage'] : 0;
        $this->ability_damage2 = isset($this_abilityinfo['ability_damage2']) ? $this_abilityinfo['ability_damage2'] : 0;
        $this->ability_damage_percent = isset($this_abilityinfo['ability_damage_percent']) ? $this_abilityinfo['ability_damage_percent'] : false;
        $this->ability_damage2_percent = isset($this_abilityinfo['ability_damage2_percent']) ? $this_abilityinfo['ability_damage2_percent'] : false;
        //$this->ability_damage_modifiers = isset($this_abilityinfo['ability_damage_modifiers']) ? $this_abilityinfo['ability_damage_modifiers'] : true;
        $this->ability_recovery = isset($this_abilityinfo['ability_recovery']) ? $this_abilityinfo['ability_recovery'] : 0;
        $this->ability_recovery2 = isset($this_abilityinfo['ability_recovery2']) ? $this_abilityinfo['ability_recovery2'] : 0;
        $this->ability_recovery_percent = isset($this_abilityinfo['ability_recovery_percent']) ? $this_abilityinfo['ability_recovery_percent'] : false;
        $this->ability_recovery2_percent = isset($this_abilityinfo['ability_recovery2_percent']) ? $this_abilityinfo['ability_recovery2_percent'] : false;
        //$this->ability_recovery_modifiers = isset($this_abilityinfo['ability_recovery_modifiers']) ? $this_abilityinfo['ability_recovery_modifiers'] : true;
        $this->ability_accuracy = isset($this_abilityinfo['ability_accuracy']) ? $this_abilityinfo['ability_accuracy'] : 0;
        $this->ability_target = isset($this_abilityinfo['ability_target']) ? $this_abilityinfo['ability_target'] : 'auto';
        $this->ability_frame = isset($this_abilityinfo['ability_frame']) ? $this_abilityinfo['ability_frame'] : 'base';
        $this->ability_frame_span = isset($this_abilityinfo['ability_frame_span']) ? $this_abilityinfo['ability_frame_span'] : 1;
        $this->ability_frame_animate = isset($this_abilityinfo['ability_frame_animate']) ? $this_abilityinfo['ability_frame_animate'] : array($this->ability_frame);
        $this->ability_frame_index = isset($this_abilityinfo['ability_frame_index']) ? $this_abilityinfo['ability_frame_index'] : array('base');
        $this->ability_frame_offset = isset($this_abilityinfo['ability_frame_offset']) ? $this_abilityinfo['ability_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 1);
        $this->ability_frame_styles = isset($this_abilityinfo['ability_frame_styles']) ? $this_abilityinfo['ability_frame_styles'] : '';
        $this->ability_frame_classes = isset($this_abilityinfo['ability_frame_classes']) ? $this_abilityinfo['ability_frame_classes'] : '';
        $this->attachment_frame = isset($this_abilityinfo['attachment_frame']) ? $this_abilityinfo['attachment_frame'] : 'base';
        $this->attachment_frame_animate = isset($this_abilityinfo['attachment_frame_animate']) ? $this_abilityinfo['attachment_frame_animate'] : array($this->attachment_frame);
        $this->attachment_frame_index = isset($this_abilityinfo['attachment_frame_index']) ? $this_abilityinfo['attachment_frame_index'] : array('base');
        $this->attachment_frame_offset = isset($this_abilityinfo['attachment_frame_offset']) ? $this_abilityinfo['attachment_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 1);
        $this->ability_results = array();
        $this->attachment_results = array();
        $this->ability_options = array();
        $this->target_options = array();
        $this->damage_options = array();
        $this->recovery_options = array();
        $this->attachment_options = array();

        // Define the internal robot base values using the robots index array
        $this->ability_base_name = isset($this_abilityinfo['ability_base_name']) ? $this_abilityinfo['ability_base_name'] : $this->ability_name;
        $this->ability_base_token = isset($this_abilityinfo['ability_base_token']) ? $this_abilityinfo['ability_base_token'] : $this->ability_token;
        $this->ability_base_image = isset($this_abilityinfo['ability_base_image']) ? $this_abilityinfo['ability_base_image'] : $this->ability_image;
        $this->ability_base_image2 = isset($this_abilityinfo['ability_base_image2']) ? $this_abilityinfo['ability_base_image2'] : $this->ability_image2;
        $this->ability_base_image_size = isset($this_abilityinfo['ability_base_image_size']) ? $this_abilityinfo['ability_base_image_size'] : $this->ability_image_size;
        $this->ability_base_description = isset($this_abilityinfo['ability_base_description']) ? $this_abilityinfo['ability_base_description'] : $this->ability_description;
        $this->ability_base_type = isset($this_abilityinfo['ability_base_type']) ? $this_abilityinfo['ability_base_type'] : $this->ability_type;
        $this->ability_base_type2 = isset($this_abilityinfo['ability_base_type2']) ? $this_abilityinfo['ability_base_type2'] : $this->ability_type2;
        $this->ability_base_energy = isset($this_abilityinfo['ability_base_energy']) ? $this_abilityinfo['ability_base_energy'] : $this->ability_energy;
        $this->ability_base_speed = isset($this_abilityinfo['ability_base_speed']) ? $this_abilityinfo['ability_base_speed'] : $this->ability_speed;
        $this->ability_base_damage = isset($this_abilityinfo['ability_base_damage']) ? $this_abilityinfo['ability_base_damage'] : $this->ability_damage;
        $this->ability_base_damage2 = isset($this_abilityinfo['ability_base_damage2']) ? $this_abilityinfo['ability_base_damage2'] : $this->ability_damage2;
        $this->ability_base_recovery = isset($this_abilityinfo['ability_base_recovery']) ? $this_abilityinfo['ability_base_recovery'] : $this->ability_recovery;
        $this->ability_base_recovery2 = isset($this_abilityinfo['ability_base_recovery2']) ? $this_abilityinfo['ability_base_recovery2'] : $this->ability_recovery2;
        $this->ability_base_accuracy = isset($this_abilityinfo['ability_base_accuracy']) ? $this_abilityinfo['ability_base_accuracy'] : $this->ability_accuracy;
        $this->ability_base_target = isset($this_abilityinfo['ability_base_target']) ? $this_abilityinfo['ability_base_target'] : $this->ability_target;

        // Collect any functions associated with this ability
        $this->ability_functions = isset($this_abilityinfo['ability_functions']) ? $this_abilityinfo['ability_functions'] : 'abilities/ability.php';
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->ability_functions) ? $this->ability_functions : 'abilities/ability.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->ability_function = isset($ability['ability_function']) ? $ability['ability_function'] : function(){};
        $this->ability_function_onload = isset($ability['ability_function_onload']) ? $ability['ability_function_onload'] : function(){};
        $this->ability_function_attachment = isset($ability['ability_function_attachment']) ? $ability['ability_function_attachment'] : function(){};
        unset($ability);

        // Define a the default ability results
        $this->ability_results_reset();

        // Reset the ability options to default
        $this->target_options_reset();
        $this->damage_options_reset();
        $this->recovery_options_reset();

        // Trigger the onload function if it exists
        $this->trigger_onload();

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a function for refreshing this ability and running onload actions
    public function trigger_onload(){

        // Trigger the onload function if it exists
        $temp_target_player = $this->battle->find_target_player($this->player->player_side != 'right' ? 'right' : 'left');
        $temp_target_robot = $this->battle->find_target_robot($this->player->player_side != 'right' ? 'right' : 'left');
        $temp_function = $this->ability_function_onload;
        $temp_result = $temp_function(array(
            'this_field' => $this->battle->battle_field,
            'this_battle' => $this->battle,
            'this_player' => $this->player,
            'this_robot' => $this->robot,
            'target_player' => $temp_target_player,
            'target_robot' => $temp_target_robot,
            'this_ability' => $this
            ));

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
    public function reset_type(){ $this->set_info('ability_type', $this->get_info('ability_base_type')); }

    public function get_type2(){ return $this->get_info('ability_type2'); }
    public function set_type2($value){ $this->set_info('ability_type2', $value); }
    public function get_base_type2(){ return $this->get_info('ability_base_type2'); }
    public function set_base_type2($value){ $this->set_info('ability_base_type2', $value); }
    public function reset_type2(){ $this->set_info('ability_type2', $this->get_info('ability_base_type2')); }

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
    public function get_image2(){ return $this->get_info('ability_image2'); }
    public function set_image($value){ $this->set_info('ability_image', $value); }
    public function set_image2($value){ $this->set_info('ability_image2', $value); }
    public function get_base_image(){ return $this->get_info('ability_base_image'); }
    public function get_base_image2(){ return $this->get_info('ability_base_image2'); }
    public function set_base_image($value){ $this->set_info('ability_base_image', $value); }
    public function set_base_image2($value){ $this->set_info('ability_base_image2', $value); }
    public function reset_image(){ $this->set_info('ability_image', $this->get_info('ability_base_image')); }
    public function reset_image2(){ $this->set_info('ability_image2', $this->get_info('ability_base_image2')); }

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

    public function print_token(){ return '<span class="ability_token">'.$this->ability_token.'</span>'; }
    public function print_description(){ return '<span class="ability_description">'.$this->ability_description.'</span>'; }
    public function print_type(){ return '<span class="ability_type">'.$this->ability_type.'</span>'; }
    public function print_type2(){ return '<span class="ability_type2">'.$this->ability_type2.'</span>'; }
    public function print_speed(){ return '<span class="ability_speed">'.$this->ability_speed.'</span>'; }
    public function print_damage(){ return '<span class="ability_damage">'.$this->ability_damage.'</span>'; }
    public function print_recovery(){ return '<span class="ability_recovery">'.$this->ability_recovery.'</span>'; }
    public function print_accuracy(){ return '<span class="ability_accuracy">'.$this->ability_accuracy.'%</span>'; }

    // Define a trigger for using one of this robot's abilities
    public function reset_all(){

        // Reset this ability's results and options
        $this->ability_results_reset();
        $this->target_options_reset();
        $this->damage_options_reset();
        $this->recovery_options_reset();
        return true;
    }

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
        $this->ability_results = array();
        // Populate the array with defaults
        $this->ability_results['total_result'] = '';
        $this->ability_results['total_actions'] = 0;
        $this->ability_results['total_strikes'] = 0;
        $this->ability_results['total_misses'] = 0;
        $this->ability_results['total_amount'] = 0;
        $this->ability_results['total_overkill'] = 0;
        $this->ability_results['this_result'] = '';
        $this->ability_results['this_amount'] = 0;
        $this->ability_results['this_overkill'] = 0;
        $this->ability_results['this_text'] = '';
        $this->ability_results['counter_criticals'] = 0;
        $this->ability_results['counter_weaknesses'] = 0;
        $this->ability_results['counter_resistances'] = 0;
        $this->ability_results['counter_affinities'] = 0;
        $this->ability_results['counter_immunities'] = 0;
        $this->ability_results['counter_coreboosts'] = 0;
        $this->ability_results['flag_critical'] = false;
        $this->ability_results['flag_affinity'] = false;
        $this->ability_results['flag_weakness'] = false;
        $this->ability_results['flag_resistance'] = false;
        $this->ability_results['flag_immunity'] = false;
        $this->ability_results['flag_coreboost'] = false;
        // Update this ability's data
        $this->update_session();
        // Return the resuling array
        return $this->ability_results;
    }

    // Define a public function for easily resetting target options
    public function target_options_reset(){
        // Redfine the options variables as an empty array
        $this->target_options = array();
        // Populate the array with defaults
        $this->target_options['target_kind'] = 'energy';
        $this->target_options['target_frame'] = 'shoot';
        $this->target_options['ability_success_frame'] = 1;
        $this->target_options['ability_success_frame_span'] = 1;
        $this->target_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->target_options['ability_failure_frame'] = 1;
        $this->target_options['ability_failure_frame_span'] = 1;
        $this->target_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->target_options['target_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $this->target_options['target_header'] = $this->robot->robot_name.'&#39;s '.$this->ability_name;
        $this->target_options['target_text'] = "{$this->robot->print_name()} uses {$this->print_name()}!";
        // Update this ability's data
        $this->update_session();
        // Return the resuling array
        return $this->target_options;
    }


    // Define a public function for easily updating target options
    public function target_options_update($target_options = array()){
        // Update internal variables with basic target options, if set
        if (isset($target_options['header'])){ $this->target_options['target_header'] = $target_options['header'];  }
        if (isset($target_options['text'])){ $this->target_options['target_text'] = $target_options['text'];  }
        if (isset($target_options['frame'])){ $this->target_options['target_frame'] = $target_options['frame'];  }
        if (isset($target_options['kind'])){ $this->target_options['target_kind'] = $target_options['kind'];  }
        // Update internal variables with kickback options, if set
        if (isset($target_options['kickback'])){
            $this->target_options['target_kickback']['x'] = $target_options['kickback'][0];
            $this->target_options['target_kickback']['y'] = $target_options['kickback'][1];
            $this->target_options['target_kickback']['z'] = $target_options['kickback'][2];
        }
        // Update internal variabels with success options, if set
        if (isset($target_options['success'])){
            $this->target_options['ability_success_frame'] = $target_options['success'][0];
            $this->target_options['ability_success_frame_offset']['x'] = $target_options['success'][1];
            $this->target_options['ability_success_frame_offset']['y'] = $target_options['success'][2];
            $this->target_options['ability_success_frame_offset']['z'] = $target_options['success'][3];
            $this->target_options['target_text'] = $target_options['success'][4];
            $this->target_options['ability_success_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($target_options['failure'])){
            $this->target_options['ability_failure_frame'] = $target_options['failure'][0];
            $this->target_options['ability_failure_frame_offset']['x'] = $target_options['failure'][1];
            $this->target_options['ability_failure_frame_offset']['y'] = $target_options['failure'][2];
            $this->target_options['ability_failure_frame_offset']['z'] = $target_options['failure'][3];
            $this->target_options['target_text'] = $target_options['failure'][4];
            $this->target_options['ability_failure_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Return the new array
        return $this->target_options;
    }

    // Define a public function for easily resetting damage options
    public function damage_options_reset(){
        // Redfine the options variables as an empty array
        $this->damage_options = array();
        // Populate the array with defaults
        $this->damage_options = array();
        $this->damage_options['damage_header'] = $this->robot->robot_name.'&#39;s '.$this->ability_name;
        $this->damage_options['damage_frame'] = 'damage';
        $this->damage_options['ability_success_frame'] = 1;
        $this->damage_options['ability_success_frame_span'] = 1;
        $this->damage_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['ability_failure_frame'] = 1;
        $this->damage_options['ability_failure_frame_span'] = 1;
        $this->damage_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['damage_kind'] = 'energy';
        $this->damage_options['damage_type'] = $this->ability_type;
        $this->damage_options['damage_type2'] = $this->ability_type2;
        $this->damage_options['damage_amount'] = $this->ability_damage;
        $this->damage_options['damage_amount2'] = $this->ability_damage2;
        $this->damage_options['damage_kickback'] = array('x' => 5, 'y' => 0, 'z' => 0);
        $this->damage_options['damage_percent'] = false;
        $this->damage_options['damage_percent2'] = false;
        $this->damage_options['damage_modifiers'] = true;
        $this->damage_options['success_rate'] = 'auto';
        $this->damage_options['failure_rate'] = 'auto';
        $this->damage_options['critical_rate'] = 10;
        $this->damage_options['critical_multiplier'] = 2;
        $this->damage_options['weakness_multiplier'] = 2;
        $this->damage_options['resistance_multiplier'] = 0.5;
        $this->damage_options['immunity_multiplier'] = 0;
        $this->damage_options['success_text'] = 'The ability hit!';
        $this->damage_options['failure_text'] = 'The ability missed&hellip;';
        $this->damage_options['immunity_text'] = 'The ability had no effect&hellip;';
        $this->damage_options['critical_text'] = 'It&#39;s a critical hit!';
        $this->damage_options['weakness_text'] = 'It&#39;s super effective!';
        $this->damage_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $this->damage_options['weakness_resistance_text'] = ''; //"It's a super effective resisted hit!';
        $this->damage_options['weakness_critical_text'] = 'It&#39;s a super effective critical hit!';
        $this->damage_options['resistance_critical_text'] = 'It&#39;s a critical hit, but not very effective&hellip;';
        // Update this ability's data
        $this->update_session();
        // Return the resuling array
        return $this->damage_options;
    }

    // Define a public function for easily updating damage options
    public function damage_options_update($damage_options = array(), $update_session = false){
        // Update internal variables with basic damage options, if set
        if (isset($damage_options['header'])){ $this->damage_options['damage_header'] = $damage_options['header'];  }
        if (isset($damage_options['frame'])){ $this->damage_options['damage_frame'] = $damage_options['frame'];  }
        if (isset($damage_options['kind'])){ $this->damage_options['damage_kind'] = $damage_options['kind'];  }
        if (isset($damage_options['type'])){ $this->damage_options['damage_type'] = $damage_options['type'];  }
        if (isset($damage_options['type2'])){ $this->damage_options['damage_type2'] = $damage_options['type2'];  }
        if (isset($damage_options['amount'])){ $this->damage_options['damage_amount'] = $damage_options['amount'];  }
        if (isset($damage_options['percent'])){ $this->damage_options['damage_percent'] = $damage_options['percent'];  }
        if (isset($damage_options['modifiers'])){ $this->damage_options['damage_modifiers'] = $damage_options['modifiers'];  }
        // Update internal variables with rate options, if set
        if (isset($damage_options['rates'])){
            $this->damage_options['success_rate'] = $damage_options['rates'][0];
            $this->damage_options['failure_rate'] = $damage_options['rates'][1];
            $this->damage_options['critical_rate'] = $damage_options['rates'][2];
        }
        // Update internal variables with multipier options, if set
        if (isset($damage_options['multipliers'])){
            $this->damage_options['critical_multiplier'] = $damage_options['multipliers'][0];
            $this->damage_options['weakness_multiplier'] = $damage_options['multipliers'][1];
            $this->damage_options['resistance_multiplier'] = $damage_options['multipliers'][2];
            $this->damage_options['immunity_multiplier'] = $damage_options['multipliers'][3];
        }
        // Update internal variables with kickback options, if set
        if (isset($damage_options['kickback'])){
            $this->damage_options['damage_kickback']['x'] = $damage_options['kickback'][0];
            $this->damage_options['damage_kickback']['y'] = $damage_options['kickback'][1];
            $this->damage_options['damage_kickback']['z'] = $damage_options['kickback'][2];
        }
        // Update internal variables with success options, if set
        if (isset($damage_options['success'])){
            $this->damage_options['ability_success_frame'] = $damage_options['success'][0];
            $this->damage_options['ability_success_frame_offset']['x'] = $damage_options['success'][1];
            $this->damage_options['ability_success_frame_offset']['y'] = $damage_options['success'][2];
            $this->damage_options['ability_success_frame_offset']['z'] = $damage_options['success'][3];
            $this->damage_options['success_text'] = $damage_options['success'][4];
            $this->damage_options['ability_success_frame_span'] = isset($damage_options['success'][5]) ? $damage_options['success'][5] : 1;
        }
        // Update internal variables with failure options, if set
        if (isset($damage_options['failure'])){
            $this->damage_options['ability_failure_frame'] = $damage_options['failure'][0];
            $this->damage_options['ability_failure_frame_offset']['x'] = $damage_options['failure'][1];
            $this->damage_options['ability_failure_frame_offset']['y'] = $damage_options['failure'][2];
            $this->damage_options['ability_failure_frame_offset']['z'] = $damage_options['failure'][3];
            $this->damage_options['failure_text'] = $damage_options['failure'][4];
            $this->damage_options['ability_failure_frame_span'] = isset($damage_options['failure'][5]) ? $damage_options['failure'][5] : 1;
        }
        // If session update was requested, do it
        if ($update_session){ $this->update_session(); }
        // Return the new array
        return $this->damage_options;
    }

    // Define a public function for easily resetting recovery options
    public function recovery_options_reset(){
        // Redfine the options variables as an empty array
        $this->recovery_options = array();
        // Populate the array with defaults
        $this->recovery_options = array();
        $this->recovery_options['recovery_header'] = $this->robot->robot_name.'&#39;s '.$this->ability_name;
        $this->recovery_options['recovery_frame'] = 'defend';
        $this->recovery_options['ability_success_frame'] = 1;
        $this->recovery_options['ability_success_frame_span'] = 1;
        $this->recovery_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['ability_failure_frame'] = 1;
        $this->recovery_options['ability_failure_frame_span'] = 1;
        $this->recovery_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['recovery_kind'] = 'energy';
        $this->recovery_options['recovery_type'] = $this->ability_type;
        $this->recovery_options['recovery_type2'] = $this->ability_type2;
        $this->recovery_options['recovery_amount'] = $this->ability_recovery;
        $this->recovery_options['recovery_amount2'] = $this->ability_recovery2;
        $this->recovery_options['recovery_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $this->recovery_options['recovery_percent'] = false;
        $this->recovery_options['recovery_percent2'] = false;
        $this->recovery_options['recovery_modifiers'] = true;
        $this->recovery_options['success_rate'] = 'auto';
        $this->recovery_options['failure_rate'] = 'auto';
        $this->recovery_options['critical_rate'] = 10;
        $this->recovery_options['critical_multiplier'] = 2;
        $this->recovery_options['affinity_multiplier'] = 2;
        $this->recovery_options['resistance_multiplier'] = 0.5;
        $this->recovery_options['immunity_multiplier'] = 0;
        $this->recovery_options['recovery_type'] = $this->ability_type;
        $this->recovery_options['recovery_type2'] = $this->ability_type2;
        $this->recovery_options['success_text'] = 'The ability worked!';
        $this->recovery_options['failure_text'] = 'The ability failed&hellip;';
        $this->recovery_options['immunity_text'] = 'The ability had no effect&hellip;';
        $this->recovery_options['critical_text'] = 'It&#39;s a lucky boost!';
        $this->recovery_options['affinity_text'] = 'It&#39;s super effective!';
        $this->recovery_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $this->recovery_options['affinity_resistance_text'] = ''; //'It&#39;s a super effective resisted hit!';
        $this->recovery_options['affinity_critical_text'] = 'It&#39;s a super effective lucky boost!';
        $this->recovery_options['resistance_critical_text'] = 'It&#39;s a lucky boost, but not very effective&hellip;';
        // Update this ability's data
        $this->update_session();
        // Return the resuling array
        return $this->recovery_options;
    }

    // Define a public function for easily updating recovery options
    public function recovery_options_update($recovery_options = array(), $update_session = false){
        // Update internal variables with basic recovery options, if set
        if (isset($recovery_options['header'])){ $this->recovery_options['recovery_header'] = $recovery_options['header'];  }
        if (isset($recovery_options['frame'])){ $this->recovery_options['recovery_frame'] = $recovery_options['frame'];  }
        if (isset($recovery_options['kind'])){ $this->recovery_options['recovery_kind'] = $recovery_options['kind'];  }
        if (isset($recovery_options['type'])){ $this->recovery_options['recovery_type'] = $recovery_options['type'];  }
        if (isset($recovery_options['type2'])){ $this->recovery_options['recovery_type2'] = $recovery_options['type2'];  }
        if (isset($recovery_options['amount'])){ $this->recovery_options['recovery_amount'] = $recovery_options['amount'];  }
        if (isset($recovery_options['percent'])){ $this->recovery_options['recovery_percent'] = $recovery_options['percent'];  }
        if (isset($recovery_options['modifiers'])){ $this->recovery_options['recovery_modifiers'] = $recovery_options['modifiers'];  }
        // Update internal variables with rate options, if set
        if (isset($recovery_options['rates'])){
            $this->recovery_options['success_rate'] = $recovery_options['rates'][0];
            $this->recovery_options['failure_rate'] = $recovery_options['rates'][1];
            $this->recovery_options['critical_rate'] = $recovery_options['rates'][2];
        }
        // Update internal variables with multipier options, if set
        if (isset($recovery_options['multipliers'])){
            $this->recovery_options['critical_multiplier'] = $recovery_options['multipliers'][0];
            $this->recovery_options['weakness_multiplier'] = $recovery_options['multipliers'][1];
            $this->recovery_options['resistance_multiplier'] = $recovery_options['multipliers'][2];
            $this->recovery_options['immunity_multiplier'] = $recovery_options['multipliers'][3];
        }
        // Update internal variables with kickback options, if set
        if (isset($recovery_options['kickback'])){
            $this->recovery_options['recovery_kickback']['x'] = $recovery_options['kickback'][0];
            $this->recovery_options['recovery_kickback']['y'] = $recovery_options['kickback'][1];
            $this->recovery_options['recovery_kickback']['z'] = $recovery_options['kickback'][2];
        }
        // Update internal variabels with success options, if set
        if (isset($recovery_options['success'])){
            $this->recovery_options['ability_success_frame'] = $recovery_options['success'][0];
            $this->recovery_options['ability_success_frame_offset']['x'] = $recovery_options['success'][1];
            $this->recovery_options['ability_success_frame_offset']['y'] = $recovery_options['success'][2];
            $this->recovery_options['ability_success_frame_offset']['z'] = $recovery_options['success'][3];
            $this->recovery_options['success_text'] = $recovery_options['success'][4];
            $this->recovery_options['ability_success_frame_span'] = isset($recovery_options['success'][5]) ? $recovery_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($recovery_options['failure'])){
            $this->recovery_options['ability_failure_frame'] = $recovery_options['failure'][0];
            $this->recovery_options['ability_failure_frame_offset']['x'] = $recovery_options['failure'][1];
            $this->recovery_options['ability_failure_frame_offset']['y'] = $recovery_options['failure'][2];
            $this->recovery_options['ability_failure_frame_offset']['z'] = $recovery_options['failure'][3];
            $this->recovery_options['failure_text'] = $recovery_options['failure'][4];
            $this->recovery_options['ability_failure_frame_span'] = isset($recovery_options['failure'][5]) ? $recovery_options['failure'][5] : 1;
        }
        // If session update was requested, do it
        if ($update_session){ $this->update_session(); }
        // Return the new array
        return $this->recovery_options;
    }

    // Define a public function for easily resetting attachment options
    public function attachment_options_reset(){
        // Redfine the options variables as an empty array
        $this->attachment_options = array();
        // Update this ability's data
        $this->update_session();
        // Return the resuling array
        return $this->attachment_options;
    }


    // Define a public function for easily updating attachment options
    public function attachment_options_update($attachment_options = array()){
        // Update this ability's data
        $this->update_session();
        // Return the new array
        return $this->attachment_options;
    }

    // Define a function for generating ability canvas variables
    public function canvas_markup($options, $player_data, $robot_data){

        // Delegate markup generation to the canvas class
        return rpg_canvas::ability_markup($this, $options, $player_data, $robot_data);

    }

    // Define a function for generating ability console variables
    public function console_markup($options, $player_data, $robot_data){

        // Delegate markup generation to the console class
        return rpg_console::ability_markup($this, $options, $player_data, $robot_data);

    }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all ability index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

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
     * Get the entire ability index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $filter_class = '', $include_tokens = array()){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND ability_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND ability_flag_published = 1 '; }
        if (!empty($filter_class)){ $temp_where .= "AND ability_class = '{$filter_class}' "; }
        if (!empty($include_tokens)){
            $include_string = $include_tokens;
            array_walk($include_string, function(&$s){ $s = "'{$s}'"; });
            $include_tokens = implode(', ', $include_string);
            $temp_where .= 'OR ability_token IN ('.$include_tokens.') ';
        }

        // Collect every type's info from the database index
        $ability_fields = self::get_index_fields(true);
        $ability_index = $db->get_array_list("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_id <> 0 {$temp_where};", 'ability_token');

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
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND ability_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND ability_flag_published = 1 '; }

        // Collect an array of ability tokens from the database
        $ability_index = $db->get_array_list("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_id <> 0 {$temp_where};", 'ability_token');

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
        $db = cms_database::get_database();

        // Generate a token string for the database query
        $ability_tokens_string = array();
        foreach ($ability_tokens AS $ability_token){ $ability_tokens_string[] = "'{$ability_token}'"; }
        $ability_tokens_string = implode(', ', $ability_tokens_string);

        // Collect the requested ability's info from the database index
        $ability_fields = self::get_index_fields(true);
        $ability_index = $db->get_array_list("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_token IN ({$ability_tokens_string});", 'ability_token');

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
        $db = cms_database::get_database();

        // Collect this ability's info from the database index
        $lookup = !is_numeric($ability_token) ? "ability_token = '{$ability_token}'" : "ability_id = {$ability_token}";
        $ability_fields = self::get_index_fields(true);
        $ability_index = $db->get_array("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE {$lookup};", 'ability_token');

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

        if (!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  // '; }
        elseif (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  // '; }
        if (!empty($ability_info['ability_damage']) && !empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage | '.$ability_info['ability_recovery'].' Recovery'; }
        elseif (!empty($ability_info['ability_damage'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage'; }
        elseif (!empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_recovery'].' Recovery '; }
        if (!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  | '; }

        // If show accuracy or quantity
        if ($print_options['show_accuracy']){

            if (preg_match('/(damage|recovery)/i', $temp_ability_title)){ $temp_ability_title .= '  | '; }
            if (!empty($ability_info['ability_accuracy'])){ $temp_ability_title .= ' '.$ability_info['ability_accuracy'].'% Accuracy'; }

        }

        if (!empty($temp_ability_energy)){ $temp_ability_title .= ' | '.$temp_ability_energy.' Energy'; }
        if ($temp_ability_target != 'auto'){ $temp_ability_title .= ' | Select Target'; }

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
        if (!empty($ability_info['ability_accuracy'])){ $temp_ability_option .= ' | A:'.$ability_info['ability_accuracy']; }
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
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
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
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
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

        $ability_power = 0;
        if (!empty($ability_info_damage) && $ability_info_damage > $ability_power){ $ability_power = $ability_info_damage; }
        if (!empty($ability_info_recovery) && $ability_info_recovery > $ability_power){ $ability_power = $ability_info_recovery; }

        $ability_info_accuracy = !empty($ability_info['ability_accuracy']) ? $ability_info['ability_accuracy'] : 0;
        $ability_info_description = !empty($ability_info['ability_description']) ? $ability_info['ability_description'] : '';
        $ability_info_description = str_replace('{DAMAGE}', $ability_info_damage, $ability_info_description);
        $ability_info_description = str_replace('{RECOVERY}', $ability_info_recovery, $ability_info_description);
        $ability_info_description = str_replace('{DAMAGE2}', $ability_info_damage2, $ability_info_description);
        $ability_info_description = str_replace('{RECOVERY2}', $ability_info_recovery2, $ability_info_description);
        $ability_info_class_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
        if (!empty($ability_info['ability_type2'])){ $ability_info_class_type = $ability_info_class_type != 'none' ? $ability_info_class_type.'_'.$ability_info['ability_type2'] : $ability_info['ability_type2']; }
        $ability_info_title = rpg_ability::print_editor_title_markup($robot_info, $ability_info);
        //$ability_info_title_plain = strip_tags(str_replace('<br />', '//', $ability_info_title));
        $ability_info_title_tooltip = htmlentities($ability_info_title, ENT_QUOTES, 'UTF-8');
        $ability_info_title_html = str_replace(' ', '&nbsp;', $ability_info_name);
        $temp_select_options = str_replace('value="'.$ability_info_token.'"', 'value="'.$ability_info_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);
        $ability_info_title_html = '<label style="background-image: url(images/abilities/'.$ability_info_token.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$ability_info_title_html.'<span class="arrow">&#8711;</span></label>';
        $this_select_markup = '<a '.
            'class="ability_name type type_'.$ability_info_class_type.'" '.
            'data-id="'.$ability_info_id.'" '.
            'data-key="'.$ability_key.'" '.
            'data-player="'.$player_info['player_token'].'" '.
            'data-robot="'.$robot_info['robot_token'].'" '.
            'data-ability="'.$ability_info_token.'" '.
            'data-type="'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').'" '.
            'data-type2="'.(!empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : '').'" '.
            'data-power="'.$ability_power.'" '.
            //'title="'.$ability_info_title_plain.'" '.
            'data-tooltip="'.$ability_info_title_tooltip.'"'.
            '>'.$ability_info_title_html.'</a>';

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
        $_SESSION['ABILITIES'][$this->ability_id] = $this_data;
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
            'ability_name' => $this->ability_name,
            'ability_token' => $this->ability_token,
            'ability_class' => $this->ability_class,
            'ability_image' => $this->ability_image,
            'ability_image2' => $this->ability_image2,
            'ability_image_size' => $this->ability_image_size,
            'ability_description' => $this->ability_description,
            'ability_type' => $this->ability_type,
            'ability_type2' => $this->ability_type2,
            'ability_energy' => $this->ability_energy,
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
            'attachment_results' => $this->attachment_results,
            'ability_options' => array(), //$this->ability_options,
            'target_options' => array(), //$this->target_options,
            'damage_options' => array(), //$this->damage_options,
            'recovery_options' => array(), //$this->recovery_options,
            'attachment_options' => array(), //$this->attachment_options,
            'ability_base_name' => $this->ability_base_name,
            'ability_base_token' => $this->ability_base_token,
            'ability_base_image' => $this->ability_base_image,
            'ability_base_image2' => $this->ability_base_image2,
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
            'ability_frame' => $this->ability_frame,
            'ability_frame_span' => $this->ability_frame_span,
            //'ability_frame_index' => $this->ability_frame_index,
            'ability_frame_animate' => $this->ability_frame_animate,
            'ability_frame_offset' => $this->ability_frame_offset,
            'ability_frame_classes' => $this->ability_frame_classes,
            'ability_frame_styles' => $this->ability_frame_styles,
            'attachment_frame' => $this->attachment_frame,
            'attachment_frame_animate' => $this->attachment_frame_animate,
            'attachment_frame_offset' => $this->attachment_frame_offset,
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
        global $mmrpg_database_abilities, $mmrpg_database_abilities, $mmrpg_database_types;
        global $db;

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
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = true; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'event'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
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

            <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?= $ability_info['ability_token']?>">&nbsp;</a>
            <? endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $ability_info['ability_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

                <? if($print_options['show_icon']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_icon']): ?>
                            <? if($print_options['show_key'] !== false): ?>
                                <div class="icon ability_type <?= $ability_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.$ability_info['ability_key'] ?></div>
                            <? endif; ?>
                            <? if ($ability_image_token != 'ability'){ ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: url(images/abilities/<?= $ability_image_token ?>/icon_right_<?= $ability_image_size_text ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon"><?= $ability_info['ability_name']?>'s Icon</div></div>
                            <? } else { ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon">No Image</div></div>
                            <? } ?>
                        <? endif; ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $ability_header_types ?> <?= (!$print_options['show_icon']) ? 'noicon' : '' ?>">
                        <? if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="<?= 'database/abilities/'.$ability_info['ability_token'].'/' ?>"><?= $ability_info['ability_name'] ?></a>
                        <? else: ?>
                            <?= $ability_info['ability_name'] ?>
                        <? endif; ?>
                        <? if ($print_options['layout_style'] != 'event'){ ?>
                            <? if (!empty($ability_info['ability_type_special'])){ ?>
                                <div class="header_core ability_type"><?= ucfirst($ability_info['ability_type_special']) ?> Type</div>
                            <? } elseif (!empty($ability_info['ability_type']) && !empty($ability_info['ability_type2'])){ ?>
                                <div class="header_core ability_type"><?= ucfirst($ability_info['ability_type']).' / '.ucfirst($ability_info['ability_type2']) ?> Type</div>
                            <? } elseif (!empty($ability_info['ability_type'])){ ?>
                                <div class="header_core ability_type"><?= ucfirst($ability_info['ability_type']) ?> Type</div>
                            <? } else { ?>
                                <div class="header_core ability_type">Neutral Type</div>
                            <? } ?>
                        <? } ?>
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 0 0 2px; min-height: 10px; <?= (!$print_options['show_icon']) ? 'margin-left: 0; ' : '' ?><?= $print_options['layout_style'] == 'event' ? 'font-size: 10px; min-height: 150px; ' : '' ?>">

                        <table class="full basic">
                            <tbody>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Name :</label>
                                        <span class="ability_type ability_type_"><?= $ability_info['ability_name']?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Type :</label>
                                        <? if($print_options['layout_style'] != 'event'): ?>
                                            <?
                                            if (!empty($ability_info['ability_type_special'])){
                                                echo '<a href="database/abilities/'.$ability_info['ability_type_special'].'/" class="ability_type '.$ability_header_types.'">'.ucfirst($ability_info['ability_type_special']).'</a>';
                                            }
                                            elseif (!empty($ability_info['ability_type'])){
                                                $temp_string = array();
                                                $ability_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                                                $temp_string[] = '<a href="database/abilities/'.$ability_type.'/" class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_types[$ability_type]['type_name'].'</a>';
                                                if (!empty($ability_info['ability_type2'])){
                                                    $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                                    $temp_string[] = '<a href="'.(('database/abilities/').$ability_type2.'/').'" class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_types[$ability_type2]['type_name'].'</a>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<a href="'.(('database/abilities/').'none/').'" class="ability_type ability_type_none">Neutral</a>';
                                            }
                                            ?>
                                        <? else: ?>
                                            <?
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
                                        <? endif; ?>
                                    </td>
                                </tr>
                                <? if($ability_image_token != 'ability'): ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Energy :</label>
                                            <span class="ability_stat"><?= !empty($ability_info['ability_energy']) ? $ability_info['ability_energy'] : '-' ?></span>
                                        </td>
                                    </tr>
                                <? else: ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Energy :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                            </tbody>
                        </table>

                        <table class="full extras">
                            <tbody>
                                <? if($ability_image_token != 'ability'): ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Power :</label>
                                            <? if(!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])): ?>
                                                <? if(!empty($ability_info['ability_damage'])){ ?><span class="ability_stat"><?= $ability_info['ability_damage'].(!empty($ability_info['ability_damage_percent']) ? '%' : '') ?> Damage</span><? } ?>
                                                <? if(!empty($ability_info['ability_recovery'])){ ?><span class="ability_stat"><?= $ability_info['ability_recovery'].(!empty($ability_info['ability_recovery_percent']) ? '%' : '') ?> Recovery</span><? } ?>
                                            <? else: ?>
                                                <span class="ability_stat">-</span>
                                            <? endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Speed :</label>
                                            <? if (empty($ability_info['ability_speed']) || $ability_info['ability_speed'] === 1){ ?>
                                                <span class="ability_stat">Normal</span>
                                            <? } elseif ($ability_info['ability_speed'] > 1){ ?>
                                                <span class="ability_stat">Fast <sup>(+<?= $ability_info['ability_speed'] - 1 ?>)</sup></span>
                                            <? } elseif ($ability_info['ability_speed'] < 1){ ?>
                                                <span class="ability_stat">Slow <sup>(<?= $ability_info['ability_speed'] + 1 ?>)</sup></span>
                                            <? } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Accuracy :</label>
                                            <span class="ability_stat"><?= $ability_info['ability_accuracy'].'%' ?></span>
                                        </td>
                                    </tr>
                                <? else: ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Power :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Accuracy :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Speed :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                            </tbody>
                        </table>

                        <? if (!empty($ability_info['ability_description'])): ?>
                            <table class="full description">
                                <tbody>
                                    <tr>
                                        <td class="right">
                                            <div class="ability_description" style="white-space: normal; text-align: left; <?= $print_options['layout_style'] == 'event' ? 'font-size: 12px; ' : '' ?> "><?
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
                        <? endif; ?>

                    </div>

                <? endif; ?>

                <? if ($print_options['layout_style'] == 'website'): ?>

                    <?
                    // Define the various tabs we are able to scroll to
                    $section_tabs = array();
                    //if ($print_options['show_description']){ $section_tabs[] = array('description', 'Description', false); }
                    if ($print_options['show_sprites']){ $section_tabs[] = array('sprites', 'Sprites', false); }
                    if ($print_options['show_robots']){ $section_tabs[] = array('robots', 'Robots', false); }
                    //if ($print_options['show_records']){ $section_tabs[] = array('records', 'Records', false); }
                    // Automatically mark the first element as true or active
                    $section_tabs[0][2] = true;
                    // Define the current URL for this ability page
                    $temp_url = 'database/abilities/';
                    $temp_url .= $ability_info['ability_token'].'/';
                    ?>

                    <div id="tabs" class="section_tabs">
                        <?
                        foreach($section_tabs AS $tab){
                            echo '<a class="link_inline link_'.$tab[0].'" href="'.$temp_url.'#'.$tab[0].'" data-anchor="#'.$tab[0].'"><span class="wrap">'.$tab[1].'</span></a>';
                        }
                        ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_sprites'] && (!isset($ability_info['ability_image_sheets']) || $ability_info['ability_image_sheets'] !== 0) && $ability_image_token != 'ability' ): ?>

                    <?

                    // Start the output buffer and prepare to collect sprites
                    $this_sprite_markup = '';
                    if (true){

                        // Define the alts we'll be looping through for this ability
                        $temp_alts_array = array();
                        $temp_alts_array[] = array('token' => '', 'name' => $ability_info['ability_name'], 'summons' => 0);
                        // Append predefined alts automatically, based on the ability image alt array
                        if (!empty($ability_info['ability_image_alts'])){
                            $temp_alts_array = array_merge($temp_alts_array, $ability_info['ability_image_alts']);
                        }
                        // Otherwise, if this is a copy ability, append based on all the types in the index
                        elseif ($ability_info['ability_type'] == 'copy' && preg_match('/^(mega-man|proto-man|bass|doc-ability)$/i', $ability_info['ability_token'])){
                            foreach ($mmrpg_database_types AS $type_token => $type_info){
                                if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
                                $temp_alts_array[] = array('token' => $type_token, 'name' => $ability_info['ability_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
                            }
                        }
                        // Otherwise, if this ability has multiple sheets, add them as alt options
                        elseif (!empty($ability_info['ability_image_sheets'])){
                            for ($i = 2; $i <= $ability_info['ability_image_sheets']; $i++){
                                $temp_alts_array[] = array('sheet' => $i, 'name' => $ability_info['ability_name'].' (Sheet #'.$i.')', 'summons' => 0);
                            }
                        }

                        // Loop through sizes to show and generate markup
                        $show_sizes = array();
                        $base_size = $ability_image_size;
                        $zoom_size = $ability_image_size * 2;
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
                                        echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_ability_image_token.'" data-frame="icon" style="'.($size_is_final ? 'padding-top: 20px;' : 'padding: 0;').' float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$size_value.'px; height: '.$size_value.'px; overflow: hidden;">';
                                            echo '<img class="has_pixels" style="margin-left: 0; height: '.$size_value.'px;" data-tooltip="'.$temp_title.'" src="images/abilities/'.$temp_ability_image_token.'/icon_'.$temp_direction.'_'.$show_sizes[$base_size].'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                            if ($size_is_final){ echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>'; }
                                        echo '</div>';
                                    }


                                    // Loop through the different frames and print out the sprite sheets
                                    foreach ($ability_sprite_frames AS $this_key => $this_frame){
                                        $margin_left = ceil((0 - $this_key) * $size_value);
                                        $frame_relative = $this_frame;
                                        //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($ability_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                                        $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                                        foreach (array('right', 'left') AS $temp_direction){
                                            $temp_direction2 = substr($temp_direction, 0, 1);
                                            $temp_embed = '[ability:'.$temp_direction.':'.$frame_relative.']{'.$temp_ability_image_token.'}';
                                            $temp_title = $temp_ability_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                            $temp_imgalt = $temp_title;
                                            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                            $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                            //$image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
                                            //if ($temp_sheet > 1){ $temp_ability_image_token .= '-'.$temp_sheet; }
                                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_ability_image_token.'" data-frame="'.$frame_relative.'" style="'.($size_is_final ? 'padding-top: 20px;' : 'padding: 0;').' float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$size_value.'px; height: '.$size_value.'px; overflow: hidden;">';
                                                echo '<img class="has_pixels" style="margin-left: '.$margin_left.'px; height: '.$size_value.'px;" data-tooltip="'.$temp_title.'" alt="'.$temp_imgalt.'" src="images/abilities/'.$temp_ability_image_token.'/sprite_'.$temp_direction.'_'.$sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
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

                    <h2 id="sprites" class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left; overflow: hidden; height: auto;">
                        Sprite Sheets
                        <span class="header_links image_link_container">
                            <span class="images" style="<?= count($temp_alts_array) == 1 ? 'display: none;' : '' ?>"><?
                                // Loop though and print links for the alts
                                $alt_type_base = 'ability_type type_'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').' ';
                                foreach ($temp_alts_array AS $alt_key => $alt_info){
                                    $alt_type = '';
                                    $alt_style = '';
                                    $alt_title = $alt_info['name'];
                                    $alt_title_type = $alt_type_base;
                                    if (preg_match('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', $alt_info['name'])){
                                        $alt_type = strtolower(preg_replace('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', '$1', $alt_info['name']));
                                        $alt_name = '&bull;'; //ucfirst($alt_type); //substr(ucfirst($alt_type), 0, 2);
                                        $alt_title_type = 'ability_type type_'.$alt_type.' ';
                                        $alt_type = 'ability_type type_'.$alt_type.' type_type ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key + 1; //$alt_key == 0 ? $ability_info['ability_name'] : ($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'ability_type type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($ability_info['ability_type'] == 'copy' && $alt_key == 0){ $alt_type = 'ability_type type_empty '; }
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
                        if (!empty($ability_info['ability_image_editor'])){ $editor_ids[] = $ability_info['ability_image_editor']; }
                        if (!empty($ability_info['ability_image_editor2'])){ $editor_ids[] = $ability_info['ability_image_editor2']; }
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
                        $temp_is_original = array('xxxxxxx');
                        if (in_array($ability_info['ability_token'], $temp_is_original)){ $temp_is_capcom = false; }
                        if ($temp_is_capcom){
                            echo '<p class="text text_editor">Sprite Editing by '.$temp_editor_title.' '.$temp_final_divider.' Original Artwork by <strong>Capcom</strong></p>'."\n";
                        } else {
                            echo '<p class="text text_editor">Sprite Editing by '.$temp_editor_title.' '.$temp_final_divider.' Original Item by <strong>Adrian Marceau</strong></p>'."\n";
                        }
                        ?>
                    </div>

                    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        </div>
                    <? endif; ?>

                <? endif; ?>

                <? if($print_options['show_robots']): ?>

                    <h2 id="robots" class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        Robot Compatibility
                    </h2>
                    <div class="body body_full solid" style="margin: 0 auto 4px; padding: 2px 3px; min-height: 10px;">
                        <table class="full robots" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="robot_container">
                                        <?

                                        // Collect the full robot index to loop through
                                        $ability_type_one = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : false;
                                        $ability_type_two = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : false;
                                        $ability_robot_rewards = array();
                                        $ability_robot_rewards_level = array();
                                        $ability_robot_rewards_core = array();
                                        $ability_robot_rewards_player = array();

                                        // Collect a FULL list of abilities for display
                                        $temp_required = array();
                                        if (!empty($ability_info['ability_master'])){ $temp_required[] = $ability_info['ability_master']; }
                                        $temp_robots_index = rpg_robot::get_index(false, false, 'master', $temp_required);

                                        // Loop through and remove any robots that do not learn the ability
                                        foreach ($temp_robots_index AS $robot_token => $robot_info){

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
                                                $this_robot = $temp_robots_index[$this_info['token']];
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
                                                if (!$this_robot['robot_flag_complete']){ $this_robot_image = 'robot'; $this_robot_sprite_path = 'images/robots/robot/mug_left_40x40.png'; }
                                                else { $this_robot_sprite_path = 'images/robots/'.$this_robot_image.'/mug_left_'.$this_robot_sprite_size.'x'.$this_robot_sprite_size.'.png'; }
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

    // Define a static function to use as a common action for all stat boosting
    public static function ability_function_stat_boost($target_robot, $stat_type, $boost_amount, $trigger_ability = false, $success_frame = 0, $failure_frame = 9, $extra_text = '', $item_redirect = false){

        // Do not boost stats if the battle is over
        if ($target_robot->battle->battle_status === 'complete'){ return false; }
        elseif ($target_robot->robot_status === 'disabled' || $target_robot->robot_energy <= 0){ return false; }

        // If the target robot is holding a Revser Module, redirect to a break
        if (!$item_redirect
            && !empty($target_robot->robot_item)
            && $target_robot->robot_item == 'reverse-module'){
            $target_robot->battle->events_debug(__FILE__, __LINE__, $target_robot->robot_token.' '.$target_robot->get_item().' changes reverses stat changes!');
            return rpg_ability::ability_function_stat_break($target_robot, $stat_type, $boost_amount, $trigger_ability, $success_frame, $failure_frame, $extra_text, true);
        }

        // Compensate for malformed arguments
        if (empty($trigger_ability)){ $trigger_ability = false; }
        if (!is_numeric($success_frame)){ $success_frame = 0; }
        if (!is_numeric($failure_frame)){ $failure_frame = 9; }
        if (!is_string($extra_text)){ $extra_text = ''; }

        // Add a break to the extra text if not empty
        if (!empty($extra_text) && !strstr($extra_text, '<br />')){ $extra_text .= ' <br /> '; }

        // Define the counter name we'll be working with here
        $mods_token = $stat_type.'_mods';

        // If ability not provided then generate a new one
        $hide_ability_header = false;
        if (!$trigger_ability){
            $hide_ability_header = true;
            $trigger_ability = rpg_game::get_ability($target_robot->battle, $target_robot->player, $target_robot, array('ability_token' => $stat_type.'-boost'));
        }

        // Increase the target's stat modifier only if it's not already at max
        if ($target_robot->counters[$mods_token] < MMRPG_SETTINGS_STATS_MOD_MAX){

            // Increase the stat by X stages and then floor if too high
            $rel_boost_amount = $boost_amount;
            $target_robot->counters[$mods_token] += $rel_boost_amount;
            if ($target_robot->counters[$mods_token] > MMRPG_SETTINGS_STATS_MOD_MAX){
                $rel_boost_amount -= ($target_robot->counters[$mods_token] - MMRPG_SETTINGS_STATS_MOD_MAX);
                $target_robot->counters[$mods_token] = MMRPG_SETTINGS_STATS_MOD_MAX;
            }
            $target_robot->update_session();

            // Define the boost text based on how much was applied
            if ($rel_boost_amount >= 3){ $boost_text = 'rose drastically'; }
            elseif ($rel_boost_amount >= 2){ $boost_text = 'sharply rose'; }
            else { $boost_text = 'rose'; }

            // Target this robot's self to show the success message
            $trigger_ability->set_flag('skip_canvas_header', true);
            $trigger_ability->target_options_update(array('frame' => 'taunt', 'success' => array($success_frame, -2, 0, -10, $extra_text.$target_robot->print_name().'&#39;s '.$stat_type.' '.$boost_text.'!')));
            $target_robot->trigger_target($target_robot, $trigger_ability);

        } else {

            // Target this robot's self to show the failure message
            $trigger_ability->set_flag('skip_canvas_header', true);
            $trigger_ability->target_options_update(array('frame' => 'defend', 'success' => array($failure_frame, -2, 0, -10, $extra_text.$target_robot->print_name().'&#39;s '.$stat_type.' won\'t go any higher&hellip;')));
            $target_robot->trigger_target($target_robot, $trigger_ability);

        }

    }

    // Define a static function to use as a common action for all stat breaking
    public static function ability_function_stat_break($target_robot, $stat_type, $break_amount, $trigger_ability = false, $success_frame = 0, $failure_frame = 9, $extra_text = '', $item_redirect = false){

        // Do not boost stats if the battle is over
        if ($target_robot->battle->battle_status === 'complete'){ return false; }
        elseif ($target_robot->robot_status === 'disabled' || $target_robot->robot_energy <= 0){ return false; }

        // If the target robot is holding a Revser Module, redirect to a break
        if (!$item_redirect
            && !empty($target_robot->robot_item)
            && $target_robot->robot_item == 'reverse-module'){
            $target_robot->battle->events_debug(__FILE__, __LINE__, $target_robot->robot_token.' '.$target_robot->get_item().' changes reverses stat changes!');
            return rpg_ability::ability_function_stat_boost($target_robot, $stat_type, $break_amount, $trigger_ability, $success_frame, $failure_frame, $extra_text, true);
        }

        // Compensate for malformed arguments
        if (empty($trigger_ability)){ $trigger_ability = false; }
        if (!is_numeric($success_frame)){ $success_frame = 0; }
        if (!is_numeric($failure_frame)){ $failure_frame = 9; }
        if (!is_string($extra_text)){ $extra_text = ''; }

        // Add a break to the extra text if not empty
        if (!empty($extra_text) && !strstr($extra_text, '<br />')){ $extra_text .= ' <br /> '; }

        // Define the counter name we'll be working with here
        $mods_token = $stat_type.'_mods';

        // If ability not provided then generate a new one
        $hide_ability_header = false;
        if (!$trigger_ability){
            $hide_ability_header = true;
            $trigger_ability = rpg_game::get_ability($target_robot->battle, $target_robot->player, $target_robot, array('ability_token' => $stat_type.'-break'));
        }

        // Increase the target's stat modifier only if it's not already at min
        if ($target_robot->counters[$mods_token] > MMRPG_SETTINGS_STATS_MOD_MIN){

            // Decrease the stat by X stages and then ceil if too low
            $rel_break_amount = $break_amount;
            $target_robot->counters[$mods_token] -= $rel_break_amount;
            if ($target_robot->counters[$mods_token] < MMRPG_SETTINGS_STATS_MOD_MIN){
                $rel_break_amount += ($target_robot->counters[$mods_token] + MMRPG_SETTINGS_STATS_MOD_MIN);
                $target_robot->counters[$mods_token] = MMRPG_SETTINGS_STATS_MOD_MIN;
            }
            $target_robot->update_session();

            // Define the break text based on how much was applied
            if ($rel_break_amount >= 3){ $break_text = 'severely fell'; }
            elseif ($rel_break_amount >= 2){ $break_text = 'harshly fell'; }
            else { $break_text = 'fell'; }

            // Target this robot's self to show the success message
            $trigger_ability->set_flag('skip_canvas_header', true);
            $trigger_ability->target_options_update(array('frame' => 'defend', 'success' => array($success_frame, -2, 0, -10, $extra_text.$target_robot->print_name().'&#39;s '.$stat_type.' '.$break_text.'!')));
            $target_robot->trigger_target($target_robot, $trigger_ability);

        } else {

            // Target this robot's self to show the failure message
            $trigger_ability->set_flag('skip_canvas_header', true);
            $trigger_ability->target_options_update(array('frame' => 'base', 'success' => array($failure_frame, -2, 0, -10, $extra_text.$target_robot->print_name().'&#39;s '.$stat_type.' won\'t go any lower&hellip;')));
            $target_robot->trigger_target($target_robot, $trigger_ability);

        }

    }


    // Define a static function to use as the common action for all forward attack type abilities
    public static function ability_function_forward_attack($objects, $target_options, $damage_options, $recovery_options, $effect_options = array()){

        // Define defaults for undefined target options
        if (!isset($target_options['stat_kind'])){ $target_options['stat_kind'] = 'energy'; }
        if (!isset($target_options['robot_frame'])){ $target_options['robot_frame'] = 'shoot'; }
        if (!isset($target_options['robot_kickback'])){ $target_options['robot_kickback'] = array(0, 0, 0); }
        if (!isset($target_options['ability_frame'])){ $target_options['ability_frame'] = 0; }
        if (!isset($target_options['ability_offset'])){ $target_options['ability_offset'] = array(110, 0, 10); }
        if (!isset($target_options['ability_text'])){ $target_options['ability_text'] = '{this_robot_name} uses the {this_ability_name}!'; }

        // Define defaults for undefined damage options
        if (!isset($damage_options['robot_frame'])){ $damage_options['robot_frame'] = 'damage'; }
        if (!isset($damage_options['robot_kickback'])){ $damage_options['robot_kickback'] = array(10, 0, 0); }
        if (!isset($damage_options['ability_sucess_frame'])){ $damage_options['ability_sucess_frame'] = 4; }
        if (!isset($damage_options['ability_success_offset'])){ $damage_options['ability_success_offset'] = array(-90, 0, 10); }
        if (!isset($damage_options['ability_success_text'])){ $damage_options['ability_success_text'] = 'The {this_ability_name} hit the target!'; }
        if (!isset($damage_options['ability_failure_frame'])){ $damage_options['ability_failure_frame'] = 4; }
        if (!isset($damage_options['ability_failure_offset'])){ $damage_options['ability_failure_offset'] = array(-100, 0, -10); }
        if (!isset($damage_options['ability_failure_text'])){ $damage_options['ability_failure_text'] = 'The {this_ability_name} missed...'; }

        // Define defaults for undefined recovery options
        if (!isset($recovery_options['robot_frame'])){ $recovery_options['robot_frame'] = 'taunt'; }
        if (!isset($recovery_options['robot_kickback'])){ $recovery_options['robot_kickback'] = array(0, 0, 0); }
        if (!isset($recovery_options['ability_sucess_frame'])){ $recovery_options['ability_sucess_frame'] = 4; }
        if (!isset($recovery_options['ability_success_offset'])){ $recovery_options['ability_success_offset'] = array(-45, 0, 10); }
        if (!isset($recovery_options['ability_success_text'])){ $recovery_options['ability_success_text'] = 'The {this_ability_name} was absorbed by the target!'; }
        if (!isset($recovery_options['ability_failure_frame'])){ $recovery_options['ability_failure_frame'] = 4; }
        if (!isset($recovery_options['ability_failure_offset'])){ $recovery_options['ability_failure_offset'] = array(-100, 0, -10); }
        if (!isset($recovery_options['ability_failure_text'])){ $recovery_options['ability_failure_text'] = 'The {this_ability_name} had no effect on the target...'; }

        // Define defaults for undefined effect options
        if (!isset($effect_options['stat_kind'])){ $effect_options = false; }
        else {
            if (!isset($effect_options['damage_text'])){ $effect_options['damage_text'] = '{this_robot_name}\'s stats were damaged!'; }
            if (!isset($effect_options['recovery_text'])){ $effect_options['recovery_text'] = '{this_robot_name}\'s stats improved!'; }
            if (!isset($effect_options['effect_chance'])){ $effect_options['effect_chance'] = 50; }
            if (!isset($effect_options['effect_target'])){ $effect_options['effect_target'] = 'target'; }
            if (!isset($effect_options['effect_value'])){ $effect_options['effect_value'] = 0; }
        }

        // Extract all objects into the current scope
        extract($objects);

        // Define Search and replace object strings for replacing
        $search_replace = array();
        $search_replace['this_player_name'] = $this_player->print_name();
        $search_replace['this_robot_name'] = $this_robot->print_name();
        $search_replace['target_player_name'] = $target_player->print_name();
        $search_replace['target_robot_name'] = $target_robot->print_name();
        $search_replace['this_ability_name'] = $this_ability->print_name();

        // Run the obtion arrays through the parsing function
        $target_options = self::parse_string_variables($search_replace, $target_options);
        $damage_options = self::parse_string_variables($search_replace, $damage_options);
        $recovery_options = self::parse_string_variables($search_replace, $recovery_options);
        if (!empty($effect_options)){
            $effect_options = self::parse_string_variables($search_replace, $effect_options);
        }

        // Update target options for this ability
        $this_ability->target_options_update(array(
            'frame' => $target_options['robot_frame'],
            'kickback' => $target_options['robot_kickback'],
            'success' => array(
                $target_options['ability_frame'],
                $target_options['ability_offset'][0],
                $target_options['ability_offset'][1],
                $target_options['ability_offset'][2],
                $target_options['ability_text']
                )
            ));

        // Update damage options for this ability
        $this_ability->damage_options_update(array(
            'kind' => $target_options['stat_kind'],
            'frame' => $damage_options['robot_frame'],
            'kickback' => $damage_options['robot_kickback'],
            'success' => array(
                $damage_options['ability_sucess_frame'],
                $damage_options['ability_success_offset'][0],
                $damage_options['ability_success_offset'][1],
                $damage_options['ability_success_offset'][2],
                $damage_options['ability_success_text']
                ),
            'failure' => array(
                $damage_options['ability_failure_frame'],
                $damage_options['ability_failure_offset'][0],
                $damage_options['ability_failure_offset'][1],
                $damage_options['ability_failure_offset'][2],
                $damage_options['ability_failure_text']
                )
            ));

        // Update recovery options for this ability
        $this_ability->recovery_options_update(array(
            'kind' => $target_options['stat_kind'],
            'frame' => $recovery_options['robot_frame'],
            'kickback' => $recovery_options['robot_kickback'],
            'success' => array(
                $recovery_options['ability_sucess_frame'],
                $recovery_options['ability_success_offset'][0],
                $recovery_options['ability_success_offset'][1],
                $recovery_options['ability_success_offset'][2],
                $recovery_options['ability_success_text']
                ),
            'failure' => array(
                $damage_options['ability_failure_frame'],
                $damage_options['ability_failure_offset'][0],
                $damage_options['ability_failure_offset'][1],
                $damage_options['ability_failure_offset'][2],
                $damage_options['ability_failure_text']
                )
            ));


        // Target the opposing robot with this ability
        $this_robot->trigger_target($target_robot, $this_ability);

        // Attempt to inflict damage on the opposing robot
        $stat_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $stat_damage_amount);

        // Only apply a secondary affect if one was defined
        if (!empty($effect_options)){

            // Define the stat property strings
            $robot_stat_prop = 'robot_'.$effect_options['stat_kind'];

            // Check to make sure the target of this effect is the target of the ability
            if ($effect_options['effect_target'] == 'target'){

                // Trigger effect if target isn't disabled and ability was successful and chance
                if (!empty($effect_options['effect_value']) &&
                    $target_robot->robot_status != 'disabled' &&
                    $this_ability->ability_results['this_result'] != 'failure' &&
                    ($effect_options['effect_chance'] == 100 || $this_battle->critical_chance($effect_options['effect_chance']))
                    ){

                    // Call the global stat boost or break function with customized options
                    if ($effect_options['effect_value'] > 0){
                        rpg_ability::ability_function_stat_boost($target_robot, $effect_options['stat_kind'], $effect_options['effect_value']);
                    } elseif ($effect_options['effect_value'] < 0){
                        rpg_ability::ability_function_stat_break($target_robot, $effect_options['stat_kind'], ($effect_options['effect_value'] * -1));
                    }

                }

            }
            // Otherwise, if the target of this effect is the user of the ability
            elseif ($effect_options['effect_target'] == 'user'){

                // Trigger effect if target isn't disabled and ability was successful and chance
                if (!empty($effect_options['effect_value']) &&
                    $this_robot->robot_status != 'disabled' &&
                    ($effect_options['effect_chance'] == 100 || $this_battle->critical_chance($effect_options['effect_chance']))
                    ){

                    // Call the global stat boost or break function with customized options
                    if ($effect_options['effect_value'] > 0){
                        rpg_ability::ability_function_stat_boost($this_robot, $effect_options['stat_kind'], $effect_options['effect_value']);
                    } elseif ($effect_options['effect_value'] < 0){
                        rpg_ability::ability_function_stat_break($this_robot, $effect_options['stat_kind'], ($effect_options['effect_value'] * -1));
                    }

                }

            }

        }

        // Return true on success
        return true;

    }


    // Define a static function to use as the common action for all ranged attack type abilities
    public static function ability_function_ranged_attack($objects, $options = array()){


    }


    // Define a static function to use as the common action for all multi-hit attack type abilities
    public static function ability_function_repeat_attack($objects, $options = array()){


    }


    // Define a static function to use as the common action for all multi-target attack type abilities
    public static function ability_function_spread_attack($objects, $options = array()){


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
        $this_robot_attachments = $this_robot->get_current_attachments();
        foreach ($this_robot_attachments AS $this_attachment_token => $this_attachment_info){
            if ($this_attachment_token == 'ability_'.$this_ability->ability_type.'-buster'){
                $temp_new_damage += 2;
            }
        }
        // Update the ability's damage with the new amount
        $this_ability->set_damage($temp_new_damage);

        // If this robot is holding a Target Module, allow target selection
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

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
        $this_robot_attachments = $this_robot->get_current_attachments();
        foreach ($this_robot_attachments AS $key => $info){
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
        $this_charge_required = !isset($this_robot_attachments[$this_attachment_token]) ? true : false;
        // If this robot is holding a charge module, bypass changing and set to false
        if ($this_robot->has_item('charge-module')){ $this_charge_required = false; }

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
        $this_robot_attachments = $this_robot->get_current_attachments();
        $this_charge_required = !isset($this_robot_attachments[$this_attachment_token]) ? true : false;
        // If this robot is holding a Charge Module, bypass changing and set to false
        if ($this_robot->has_item('charge-module')){ $this_charge_required = false; }

        // If the ability flag had already been set, reduce the weapon energy to zero
        if (!$this_charge_required){ $this_ability->set_energy(0); }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->reset_energy(); }

        // If this robot is holding a Target Module, allow target selection
        if ($this_robot->has_item('target-module')){
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
            $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
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

        // Loop through all robots on the target side and disable any that need it
        $target_robots_active = $target_player->get_robots();
        foreach ($target_robots_active AS $key => $robot){
            if ($robot->robot_id == $target_robot->robot_id){ $temp_target_robot = $target_robot; }
            else { $temp_target_robot = $robot; }
            if (($temp_target_robot->robot_energy < 1 || $temp_target_robot->robot_status == 'disabled')
                && empty($temp_target_robot->flags['apply_disabled_state'])){
                $temp_target_robot->trigger_disabled($this_robot);
            }
        }

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-overdrive abilities
    public static function ability_function_onload_overdrive($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update this abilities weapon energy to whatever the user's max is
        $this_ability->set_energy($this_robot->robot_base_weapons);

        // Calculate the user's current life damage percent for calculations
        $robot_energy_damage = $this_robot->robot_base_energy - $this_robot->robot_energy;
        $robot_energy_damage_percent = !empty($robot_energy_damage) ? ceil(($robot_energy_damage / $this_robot->robot_base_energy) * 100) : 0;

        // Multiply the user's damage by the remaining weapon energy for damage total
        $ability_damage_amount = $robot_energy_damage_percent + 1;
        $this_ability->set_damage($ability_damage_amount);

        // Return true on success
        return true;

    }

    // Define a static function for replacing string variables with their values
    public static function parse_string_variables($search_replace, $field_values){

        // Collect all the find and replace strings
        $find_strings = array_keys($search_replace);
        $replace_strings = array_values($search_replace);
        foreach ($find_strings AS $k => $v){ $find_strings[$k] = '{'.$v.'}'; }

        // Loop through field values and replace string variables
        foreach ($field_values AS $field => $value){
            if (strstr($field, '_text')){
                $value = str_replace($find_strings, $replace_strings, $value);
                $field_values[$field] = $value;
            }
        }

        // Return the parsed field values
        return $field_values;

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
            'energy-mode', 'attack-mode', 'defense-mode', 'speed-mode',
            'field-support', 'mecha-support',
            'core-shield', 'core-laser', 'omega-pulse', 'omega-wave'
            );
        // Return the list of global abilities
        return $temp_global_abilities;
    }

    // Define a static function for getting a preset core shield for the challenge
    public static function get_static_core_shield($shield_type, $shield_duration = 99, $existing_shields = 0){
        $this_ability_token = 'core-shield';
        $this_attachment_token = 'ability_'.$this_ability_token.'_'.$shield_type;
        $this_attachment_image = $this_ability_token.'_'.$shield_type;
        $this_attachment_destroy_text = 'The <span class="ability_name ability_type ability_type_'.$shield_type.'">'.ucfirst($shield_type).'</span> type <span class="ability_name ability_type ability_type_'.$shield_type.'">Core Shield</span> faded away!<br /> ';
        $this_attachment_destroy_text .= 'This robot is no longer protected from the <span class="ability_name ability_type ability_type_'.$shield_type.'">'.ucfirst($shield_type).'</span> element...';
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability_token,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $shield_duration,
            'attachment_damage_input_breaker_'.$shield_type => 0.0000000001,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_destroy_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_destroy_text)
                ),
            'ability_frame' => 2,
            'ability_frame_animate' => array(2, 3, 4, 3),
            'ability_frame_offset' => array(
                'x' => (10 + ($existing_shields * 8)),
                'y' => (0),
                'z' => (10 + $existing_shields)
                )
            );
        return $this_attachment_info;
    }

    // Define a static function for generating a static field attachment of "crude oil" (from the Oil Shooter ability)
    public static function get_static_crude_oil($static_attachment_key, $this_attachment_duration = 99, $existing_attachments = 0){
        $this_battle = rpg_battle::get_battle();
        $this_attachment_json = json_encode($this_battle->battle_attachments);
        $this_ability_token = 'oil-shooter';
        $this_attachment_token = 'ability_'.$this_ability_token.'_'.$static_attachment_key;
        $this_attachment_image = $this_ability_token;
        $this_existing = substr_count($this_attachment_json, 'ability_'.$this_ability_token);
        $this_text_prefix = $this_existing > 0 ? 'One of the puddles of' : 'The puddle of';
        $this_attachment_destroy_text = $this_text_prefix.' <span class="ability_name ability_type ability_type_earth">crude oil</span> faded away...<br /> ';
        $this_attachment_destroy_text .= 'That position on the field isn\'t vulnerable to '.rpg_type::print_span('flame').' or '.rpg_type::print_span('explode').' types any more! ';
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability_token,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $this_attachment_duration,
            'attachment_sticky' => true,
            'attachment_damage_input_booster_flame' => 2.0,
            'attachment_damage_input_booster_explode' => 2.0,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_destroy_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_destroy_text)
                ),
            'ability_frame' => 1,
            'ability_frame_animate' => array(1, 2),
            'ability_frame_offset' => array(
                'x' => (0 + ($existing_attachments * 8)),
                'y' => (-10 + ($existing_attachments * 2)),
                'z' => (-8 - $existing_attachments)
                )
            );
        return $this_attachment_info;
    }

    // Define a static function for generating a static field attachment of "foamy bubbles" (from the Bubble Spray ability)
    public static function get_static_foamy_bubbles($static_attachment_key, $this_attachment_duration = 99, $existing_attachments = 0){
        $this_battle = rpg_battle::get_battle();
        $this_attachment_json = json_encode($this_battle->battle_attachments);
        $this_ability_token = 'bubble-spray';
        $this_attachment_token = 'ability_'.$this_ability_token.'_'.$static_attachment_key;
        $this_attachment_image = $this_ability_token;
        $this_existing = substr_count($this_attachment_json, 'ability_'.$this_ability_token);
        $this_text_prefix = $this_existing > 0 ? 'One of the mounds of' : 'The mound of';
        $this_attachment_destroy_text = $this_text_prefix.' <span class="ability_name ability_type ability_type_water">foamy bubbles</span> faded away...<br /> ';
        $this_attachment_destroy_text .= 'That position on the field isn\'t vulnerable to '.rpg_type::print_span('electric').' or '.rpg_type::print_span('freeze').' types any more! ';
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability_token,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $this_attachment_duration,
            'attachment_sticky' => true,
            'attachment_damage_input_booster_electric' => 2.0,
            'attachment_damage_input_booster_freeze' => 2.0,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_destroy_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_destroy_text)
                ),
            'ability_frame' => 2,
            'ability_frame_animate' => array(2, 3),
            'ability_frame_offset' => array(
                'x' => (0 + ($existing_attachments * 8)),
                'y' => (-5 + $existing_attachments),
                'z' => (6 + $existing_attachments)
                )
            );
        return $this_attachment_info;
    }

    // Define a static function for generating a static field attachment of a "black hole" (from the Galaxy Bomb ability)
    public static function get_static_black_hole($static_attachment_key, $this_attachment_duration = 99, $existing_attachments = 0){
        $this_battle = rpg_battle::get_battle();
        $this_attachment_json = json_encode($this_battle->battle_attachments);
        $this_ability_token = 'galaxy-bomb';
        $this_attachment_token = 'ability_'.$this_ability_token.'_'.$static_attachment_key;
        $this_attachment_image = $this_ability_token;
        $this_existing = substr_count($this_attachment_json, 'ability_'.$this_ability_token);
        $this_text_prefix = $this_existing > 0 ? 'One of the' : 'The';
        $this_text_noun = 'black hole'.($this_existing > 0 ? 's' : '');
        $this_attachment_destroy_text = $this_text_prefix.' crushing <span class="ability_name ability_type ability_type_space_explode">'.$this_text_noun.'</span> faded away...<br /> ';
        $this_attachment_destroy_text .= 'That position on the field won\'t take end-of-turn damage any more! ';
        $this_attachment_repeat_text = $this_text_prefix.' <span class="ability_name ability_type ability_type_space_explode">'.$this_text_noun.'</span> exherted its crushing power!';
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability_token,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $this_attachment_duration,
            'attachment_energy' => 0,
            'attachment_energy_base_percent' => 10,
            'attachment_sticky' => true,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_destroy_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_destroy_text)
                ),
            'attachment_repeat' => array(
                'kind' => 'energy',
                'trigger' => 'damage',
                'type' => 'space',
                'type2' => 'explode',
                'energy' => 10,
                'percent' => true,
                'modifiers' => true,
                'frame' => 'damage',
                'rates' => array(100, 0, 0),
                'success' => array(1, -5, 5, -10, $this_attachment_repeat_text),
                'failure' => array(1, -5, 5, -99, $this_attachment_repeat_text),
                'options' => array(
                    'apply_modifiers' => true,
                    'apply_type_modifiers' => true,
                    'apply_core_modifiers' => true,
                    'apply_field_modifiers' => true,
                    'apply_stat_modifiers' => false,
                    'apply_position_modifiers' => false,
                    'referred_damage' => true,
                    'referred_damage_id' => 0,
                    'referred_damage_stats' => array()
                    )
                ),
            'ability_frame' => 1,
            'ability_frame_animate' => array(1, 2, 3, 4, 5, 6, 7, 8, 9),
            'ability_frame_offset' => array(
                'x' => (-5 + ($existing_attachments * 6)),
                'y' => (5 + $existing_attachments),
                'z' => (-10 - $existing_attachments)
                )
            );
        return $this_attachment_info;
    }

    // Define a static function for generating a static field attachment of a "frozen foothold" (from the Ice Breath ability)
    public static function get_static_frozen_foothold($static_attachment_key, $this_attachment_duration = 99, $existing_attachments = 0){
        $this_battle = rpg_battle::get_battle();
        $this_attachment_json = json_encode($this_battle->battle_attachments);
        $this_ability_token = 'ice-breath';
        $this_attachment_token = 'ability_'.$this_ability_token.'_'.$static_attachment_key;
        $this_attachment_image = $this_ability_token;
        $this_existing = substr_count($this_attachment_json, 'ability_'.$this_ability_token);
        $this_text_prefix = $this_existing > 0 ? 'One of the' : 'The';
        $this_text_noun = 'frozen foothold'.($this_existing > 0 ? 's' : '');
        $this_attachment_destroy_text = $this_text_prefix.' <span class="ability_name ability_type ability_type_freeze">'.$this_text_noun.'</span> faded away...<br /> ';
        $this_attachment_destroy_text .= 'That position on the field isn\'t prevented from switching any more! ';
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability_token,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $this_attachment_duration,
            'attachment_sticky' => true,
            'attachment_switch_disabled' => true,
            'attachment_weaknesses' => array('flame', 'laser'),
            'attachment_weaknesses_trigger' => 'either',
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_destroy_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_destroy_text)
                ),
            'ability_frame' => 2,
            'ability_frame_animate' => array(2, 3),
            'ability_frame_offset' => array(
                'x' => (0 + ($existing_attachments * 8)),
                'y' => (-5 + $existing_attachments),
                'z' => (8 + $existing_attachments)
                )
            );
        return $this_attachment_info;
    }

}
?>