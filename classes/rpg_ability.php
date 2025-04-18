<?
/**
 * Mega Man RPG Ability Object
 * <p>The base class for all ability objects in the Mega Man RPG Prototype.</p>
 */
class rpg_ability extends rpg_object {

    // Define the constructor class
    public function __construct(){

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
        if (!isset($this_abilityinfo['ability_token'])){ return false; }
        $this_indexinfo = self::get_index_info($this_abilityinfo['ability_token']);

        // If the ability info was not an array, return false
        if (!is_array($this_abilityinfo)){ return false; }
        // If the ability ID was not provided, return false
        //if (!isset($this_abilityinfo['ability_id'])){ return false; }
        if (!isset($this_abilityinfo['ability_id'])){ $this_abilityinfo['ability_id'] = 0; }
        // If the ability token was not provided, return false
        if (!isset($this_abilityinfo['ability_token'])){ return false; }

        // If this is a special system ability, hard-code its ID, otherwise base off robot
        $temp_system_abilities = array();
        if (in_array($this_abilityinfo['ability_token'], $temp_system_abilities)){
            $this_abilityinfo['ability_id'] = $this->player_id.'000';
        }
        // Otherwise if the ID appears to have already been set
        elseif (!empty($this_abilityinfo['ability_id'])
            && strstr($this_abilityinfo['ability_id'], $this->robot_id)){
            $ability_id = $this_abilityinfo['ability_id'];
        }
        // Otherwise base the ID off of the robot
        else {
            //$ability_id = $this->robot_id.str_pad($this_indexinfo['ability_id'], 3, '0', STR_PAD_LEFT);
            $ability_id = rpg_game::unique_ability_id($this->robot_id, $this_indexinfo['ability_id']);
            if (!empty($this_abilityinfo['flags']['is_attachment']) || isset($this_abilityinfo['attachment_token'])){
                if (isset($this_abilityinfo['attachment_token'])){ $ability_id .= 'y'.strtoupper(substr(md5($this_abilityinfo['attachment_token']), 0, 3)); }
                else { $ability_id .= 'z'.strtoupper(substr(md5($this_abilityinfo['ability_token']), 0, 3)); }
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
            if (empty($this_abilityinfo_backup['_parsed']) && !empty($this_indexinfo)){
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
        $this->ability_speed2 = isset($this_abilityinfo['ability_speed2']) ? $this_abilityinfo['ability_speed2'] : $this->ability_speed;
        $this->ability_energy = isset($this_abilityinfo['ability_energy']) ? $this_abilityinfo['ability_energy'] : 4;
        $this->ability_energy_percent = isset($this_abilityinfo['ability_energy_percent']) ? $this_abilityinfo['ability_energy_percent'] : false;
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
        $this->ability_base_speed2 = isset($this_abilityinfo['ability_base_speed2']) ? $this_abilityinfo['ability_base_speed2'] : $this->ability_speed2;
        $this->ability_base_damage = isset($this_abilityinfo['ability_base_damage']) ? $this_abilityinfo['ability_base_damage'] : $this->ability_damage;
        $this->ability_base_damage2 = isset($this_abilityinfo['ability_base_damage2']) ? $this_abilityinfo['ability_base_damage2'] : $this->ability_damage2;
        $this->ability_base_recovery = isset($this_abilityinfo['ability_base_recovery']) ? $this_abilityinfo['ability_base_recovery'] : $this->ability_recovery;
        $this->ability_base_recovery2 = isset($this_abilityinfo['ability_base_recovery2']) ? $this_abilityinfo['ability_base_recovery2'] : $this->ability_recovery2;
        $this->ability_base_accuracy = isset($this_abilityinfo['ability_base_accuracy']) ? $this_abilityinfo['ability_base_accuracy'] : $this->ability_accuracy;
        $this->ability_base_target = isset($this_abilityinfo['ability_base_target']) ? $this_abilityinfo['ability_base_target'] : $this->ability_target;

        // Collect any functions associated with this ability
        if (!isset($this->ability_function)){
            $temp_functions_dir = preg_replace('/^action-/', '_actions/', $this->ability_token);
            $temp_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$temp_functions_dir.'/functions.php';
            if (file_exists($temp_functions_path)){ require($temp_functions_path); }
            else { $functions = array(); }
            $this->ability_function = isset($functions['ability_function']) ? $functions['ability_function'] : function(){};
            $this->ability_function_onload = isset($functions['ability_function_onload']) ? $functions['ability_function_onload'] : function(){};
            $this->ability_function_attachment = isset($functions['ability_function_attachment']) ? $functions['ability_function_attachment'] : function(){};
            unset($functions);
        }

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

    // Define a function for re-loreading the current ability from session
    public function ability_reload(){
        $this->ability_load(array(
            'ability_id' => $this->ability_id,
            'ability_token' => $this->ability_token
            ));
    }

    // Define a function for refreshing this ability and running onload actions
    public function trigger_onload($force = false){

        // Trigger the onload function if not already called
        if ($force || !rpg_game::onload_triggered('ability', $this->ability_id)){
            rpg_game::onload_triggered('ability', $this->ability_id, true);
            //error_log('---- trigger_onload() for ability '.$this->ability_id.PHP_EOL);
            $temp_function = $this->ability_function_onload;
            $temp_result = $temp_function(self::get_objects());
        }

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

    public function get_speed2(){ return $this->get_info('ability_speed2'); }
    public function set_speed2($value){ $this->set_info('ability_speed2', $value); }
    public function get_base_speed2(){ return $this->get_info('ability_base_speed2'); }
    public function set_base_speed2($value){ $this->set_info('ability_base_speed2', $value); }
    public function reset_speed2(){ $this->set_info('ability_speed2', $this->get_base_speed()); }

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

    // Define a public function for getting all global objects related to this ability
    private function get_objects($extra_objects = array()){

        // Collect refs to all the known objects for this ability
        $objects = array(
            'this_battle' => $this->battle,
            'this_player' => $this->player,
            'this_robot' => $this->robot,
            'this_ability' => $this
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
            if (!empty($this->player->other_player)){ $objects['target_player'] = $this->player->other_player; }
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

    // Define a function for getting the parsed version of an ability's description
    public function get_parsed_description($options = array()){
        $ability = $this;
        $objects = array(
            'this_battle' => $this->battle,
            'this_player' => $this->player,
            'this_robot' => $this->robot,
            'this_ability' => $this
            );
        return self::get_parsed_ability_description($ability, $objects, $options);
    }

    // Define a static function for getting the parsed version of an ability's description
    public static function get_parsed_ability_description($ability, $objects = array(), $options = array()){

        // Validate or clean provided optional arguments
        if (empty($objects) || !is_array($objects)){ $objects = array(); }
        if (empty($options) || !is_array($options)){ $options = array(); }

        // Extract the objects array into the current scope
        extract($objects);

        // Define the placeholder text in case we need it
        $placeholder_text = '...';

        // Initialize ability info depending on ability type
        if (is_array($ability)){
            $ability_info = $ability;
        } elseif (is_object($ability) && method_exists($ability, 'export_array')){
            $ability_info = $ability->export_array();
        } else {
            throw new Exception('Invalid ability format. Expected an array or an object with an export_array method.');
            return $placeholder_text;
        }

        // Ensure there is an ability description
        if (!isset($ability_info['ability_description'])) {
            throw new Exception('No ability description found.');
        } elseif (empty($ability_info['ability_description'])){
            return $placeholder_text;
        }

        // Define the tags and their corresponding replacements
        $tags = array('{}', '{DAMAGE}', '{DAMAGE2}', '{RECOVERY}', '{RECOVERY2}', '{ACCURACY}');
        $replacements = array($placeholder_text,
            isset($ability_info['ability_damage']) ? $ability_info['ability_damage'] : '',
            isset($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : '',
            isset($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : '',
            isset($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : '',
            isset($ability_info['ability_accuracy']) ? $ability_info['ability_accuracy'] : ''
            );

        // Collect the base description string and apply any options provided
        $ability_description = $ability_info['ability_description'];
        //if ($options['show_x_desc']){ $ability_description .= ' '.trim($ability_info['ability_description_x']); }

        // Replace the tags in the description and return the result
        $parsed_description = str_replace($tags, $replacements, $ability_description);
        return $parsed_description;
    }

    // Define public print functions for markup generation
    public function print_name($plural = false, $pseudo_name = ''){
        $print_name = $this->ability_name;
        if (!empty($pseudo_name)){ $print_name = $pseudo_name; }
        $type_class = !empty($this->ability_type) ? $this->ability_type : 'none';
        if ($type_class != 'none' && !empty($this->ability_type2)){ $type_class .= '_'.$this->ability_type2; }
        elseif ($type_class == 'none' && !empty($this->ability_type2)){ $type_class = $this->ability_type2; }
        return '<span class="ability_name ability_type ability_type_'.$type_class.'">'.$print_name.($plural ? (substr($print_name, -1, 1) == 's' ? 'es' : 's') : '').'</span>';
    }
    public function print_name_s(){
        $ends_with_s = substr($this->ability_name, -1) === 's' ? true : false;
        return $this->print_name()."'".(!$ends_with_s ? 's' : '');
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
    public function ability_results_init(){
        // Redfine the result options as an empty array
        if (!isset($this->ability_results)){ $this->ability_results = array(); }
        // Populate the array with defaults
        if (!isset($this->ability_results['total_result'])){ $this->ability_results['total_result'] = ''; }
        if (!isset($this->ability_results['total_actions'])){ $this->ability_results['total_actions'] = 0; }
        if (!isset($this->ability_results['total_strikes'])){ $this->ability_results['total_strikes'] = 0; }
        if (!isset($this->ability_results['total_misses'])){ $this->ability_results['total_misses'] = 0; }
        if (!isset($this->ability_results['total_amount'])){ $this->ability_results['total_amount'] = 0; }
        if (!isset($this->ability_results['total_overkill'])){ $this->ability_results['total_overkill'] = 0; }
        if (!isset($this->ability_results['this_result'])){ $this->ability_results['this_result'] = ''; }
        if (!isset($this->ability_results['this_amount'])){ $this->ability_results['this_amount'] = 0; }
        if (!isset($this->ability_results['this_overkill'])){ $this->ability_results['this_overkill'] = 0; }
        if (!isset($this->ability_results['this_text'])){ $this->ability_results['this_text'] = ''; }
        if (!isset($this->ability_results['counter_criticals'])){ $this->ability_results['counter_criticals'] = 0; }
        if (!isset($this->ability_results['counter_weaknesses'])){ $this->ability_results['counter_weaknesses'] = 0; }
        if (!isset($this->ability_results['counter_resistances'])){ $this->ability_results['counter_resistances'] = 0; }
        if (!isset($this->ability_results['counter_affinities'])){ $this->ability_results['counter_affinities'] = 0; }
        if (!isset($this->ability_results['counter_immunities'])){ $this->ability_results['counter_immunities'] = 0; }
        if (!isset($this->ability_results['counter_coreboosts'])){ $this->ability_results['counter_coreboosts'] = 0; }
        if (!isset($this->ability_results['counter_omegaboosts'])){ $this->ability_results['counter_omegaboosts'] = 0; }
        if (!isset($this->ability_results['flag_critical'])){ $this->ability_results['flag_critical'] = false; }
        if (!isset($this->ability_results['flag_weakness'])){ $this->ability_results['flag_weakness'] = false; }
        if (!isset($this->ability_results['flag_resistance'])){ $this->ability_results['flag_resistance'] = false; }
        if (!isset($this->ability_results['flag_affinity'])){ $this->ability_results['flag_affinity'] = false; }
        if (!isset($this->ability_results['flag_immunity'])){ $this->ability_results['flag_immunity'] = false; }
        if (!isset($this->ability_results['flag_coreboost'])){ $this->ability_results['flag_coreboost'] = false; }
        if (!isset($this->ability_results['flag_omegaboost'])){ $this->ability_results['flag_omegaboost'] = false; }
        // Update this ability's data
        $this->update_session();
        // Return the resuling array
        return $this->ability_results;
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
        $this->ability_results['counter_omegaboosts'] = 0;
        $this->ability_results['flag_critical'] = false;
        $this->ability_results['flag_weakness'] = false;
        $this->ability_results['flag_resistance'] = false;
        $this->ability_results['flag_affinity'] = false;
        $this->ability_results['flag_immunity'] = false;
        $this->ability_results['flag_coreboost'] = false;
        $this->ability_results['flag_omegaboost'] = false;
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
        $this->target_options['target_sticky'] = false;
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
        if (isset($target_options['kind'])){ $this->target_options['target_kind'] = $target_options['kind'];  }
        if (isset($target_options['frame'])){ $this->target_options['target_frame'] = $target_options['frame'];  }
        if (isset($target_options['sticky'])){ $this->target_options['target_sticky'] = $target_options['sticky'];  }
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
            $this->target_options['target_success_text'] = $target_options['success'][4];
            $this->target_options['ability_success_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($target_options['failure'])){
            $this->target_options['ability_failure_frame'] = $target_options['failure'][0];
            $this->target_options['ability_failure_frame_offset']['x'] = $target_options['failure'][1];
            $this->target_options['ability_failure_frame_offset']['y'] = $target_options['failure'][2];
            $this->target_options['ability_failure_frame_offset']['z'] = $target_options['failure'][3];
            $this->target_options['target_failure_text'] = $target_options['failure'][4];
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
        $this->damage_options['damage_kind'] = 'energy';
        $this->damage_options['damage_frame'] = 'damage';
        $this->damage_options['damage_sticky'] = false;
        $this->damage_options['ability_success_frame'] = 1;
        $this->damage_options['ability_success_frame_span'] = 1;
        $this->damage_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['ability_failure_frame'] = 1;
        $this->damage_options['ability_failure_frame_span'] = 1;
        $this->damage_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
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
        if (isset($damage_options['kind'])){ $this->damage_options['damage_kind'] = $damage_options['kind'];  }
        if (isset($damage_options['frame'])){ $this->damage_options['damage_frame'] = $damage_options['frame'];  }
        if (isset($damage_options['sticky'])){ $this->damage_options['damage_sticky'] = $damage_options['sticky'];  }
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
        $this->recovery_options['recovery_kind'] = 'energy';
        $this->recovery_options['recovery_frame'] = 'defend';
        $this->recovery_options['recovery_sticky'] = false;
        $this->recovery_options['ability_success_frame'] = 1;
        $this->recovery_options['ability_success_frame_span'] = 1;
        $this->recovery_options['ability_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['ability_failure_frame'] = 1;
        $this->recovery_options['ability_failure_frame_span'] = 1;
        $this->recovery_options['ability_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
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
        if (isset($recovery_options['kind'])){ $this->recovery_options['recovery_kind'] = $recovery_options['kind'];  }
        if (isset($recovery_options['frame'])){ $this->recovery_options['recovery_frame'] = $recovery_options['frame'];  }
        if (isset($recovery_options['sticky'])){ $this->recovery_options['recovery_sticky'] = $recovery_options['sticky'];  }
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
            'ability_class',
            'ability_subclass',
            'ability_master',
            'ability_number',
            'ability_image',
            'ability_image_sheets',
            'ability_image_size',
            'ability_image_editor',
            'ability_image_editor2',
            'ability_image_editor3',
            'ability_type',
            'ability_type2',
            'ability_description',
            'ability_description2',
            'ability_speed',
            'ability_speed2',
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
            'ability_value',
            'ability_shop_tab',
            'ability_shop_level',
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
            'ability_flag_unlockable',
            'ability_flag_protected'
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
            'ability_frame_animate',
            'ability_frame_index',
            'ability_frame_offset'
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
            'ability_group',
            'ability_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $json_index_fields = implode(', ', $json_index_fields);
        }

        // Return the index fields, array or string
        return $json_index_fields;

    }

    /**
     * Get the entire ability index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $filter_class = '', $include_tokens = array()){
        //error_log('rpg_ability::get_index()');

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND abilities.ability_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND abilities.ability_flag_published = 1 '; }
        if (!empty($filter_class)){ $temp_where .= "AND abilities.ability_class = '{$filter_class}' "; }
        if (!empty($include_tokens)){
            $include_string = $include_tokens;
            array_walk($include_string, function(&$s){ $s = "'{$s}'"; });
            $include_tokens = implode(', ', $include_string);
            $temp_where .= 'OR abilities.ability_token IN ('.$include_tokens.') ';
        }

        // Define a static array for cached queries
        static $index_cache = array();

        // Define the static token for this query
        $cache_token = md5($temp_where);

        // If already found, return the collected index directly, else collect from DB
        if (!empty($index_cache[$cache_token])){ return $index_cache[$cache_token]; }

        // Otherwise attempt to collect the index from the cache
        $cached_index = rpg_object::load_cached_index('abilities', $cache_token);
        if (!empty($cached_index)){
            $index_cache[$cache_token] = $cached_index;
            return $index_cache[$cache_token];
        }

        // Collect every type's info from the database index
        //error_log('(!) generating a new abilities index array for '.MMRPG_CONFIG_CACHE_DATE);
        $ability_fields = rpg_ability::get_index_fields(true, 'abilities');
        $ability_index = $db->get_array_list("
            SELECT
            {$ability_fields},
            groups.group_token AS ability_group,
            tokens.token_order AS ability_order
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
            LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE ability_id <> 0 {$temp_where}
            ORDER BY
            FIELD(abilities.ability_class, 'master', 'mecha', 'boss'),
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'ability_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($ability_index)){ $ability_index = self::parse_index($ability_index); }
        else { $ability_index = array(); }

        // Return the cached index array
        rpg_object::save_cached_index('abilities', $cache_token, $ability_index);
        $index_cache[$cache_token] = $ability_index;
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
    public static function get_index_info($ability_token){

        // If empty, return nothing
        if (empty($ability_token)){ return false; };

        // Collect a local copy of the ability index
        static $ability_index = false;
        static $ability_index_byid = false;
        if ($ability_index === false){
            $ability_index_byid = array();
            $ability_index = self::get_index(true, true);
            if (empty($ability_index)){ $ability_index = array(); }
            foreach ($ability_index AS $token => $ability){ $ability_index_byid[$ability['ability_id']] = $token; }
        }

        // Return either by token or by ID if number provided
        if (is_numeric($ability_token)){
            // Search by ability ID
            $ability_id = $ability_token;
            if (!empty($ability_index_byid[$ability_id])){ return $ability_index[$ability_index_byid[$ability_id]]; }
            else { return false; }
        } else {
            // Search by ability TOKEN
            if (!empty($ability_index[$ability_token])){ return $ability_index[$ability_token]; }
            else { return false; }
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
        $temp_field_names = self::get_json_index_fields();
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
        $mmrpg_types = rpg_type::get_index(true, false, true);

        // Require the function file
        $temp_ability_title = '';

        // Collect values for potentially missing global variables
        if (!isset($session_token)){  }

        if (empty($robot_info)){ return false; }
        if (empty($ability_info)){ return false; }

        $print_options['show_accuracy'] = isset($print_options['show_accuracy']) ? $print_options['show_accuracy'] : true;

        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_ability_token = $ability_info['ability_token'];
        $temp_ability_type = !empty($ability_info['ability_type']) ? $mmrpg_types[$ability_info['ability_type']] : false;
        $temp_ability_type2 = !empty($ability_info['ability_type2']) ? $mmrpg_types[$ability_info['ability_type2']] : false;
        $temp_ability_energy = rpg_robot::calculate_weapon_energy_static($robot_info, $ability_info);
        //$temp_ability_speed = !empty($ability_info['ability_speed']) ? $ability_info['ability_speed'] : 1;
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

        $temp_ability_title1 = $ability_info['ability_name'];
        if (!empty($temp_ability_type)){ $temp_ability_title1 .= ' ('.$temp_ability_type['type_name'].' Type)'; }
        if (!empty($temp_ability_type2)){ $temp_ability_title1 = str_replace('Type', '/ '.$temp_ability_type2['type_name'].' Type', $temp_ability_title1); }

        $temp_ability_title2 = array();
        if (!empty($ability_info['ability_damage'])){ $temp_ability_title2[] = $ability_info['ability_damage'].' Damage'; }
        if (!empty($ability_info['ability_recovery'])){ $temp_ability_title2[] = $ability_info['ability_recovery'].' Recovery '; }
        if ($print_options['show_accuracy'] && !empty($ability_info['ability_accuracy'])){ $temp_ability_title2[] = $ability_info['ability_accuracy'].'% Accuracy'; }
        if (!empty($temp_ability_energy)){ $temp_ability_title2[] = $temp_ability_energy.' Energy'; }
        if ($temp_ability_target != 'auto'){ $temp_ability_title2[] = 'Select Target'; }
        $temp_ability_title2 = implode(' | ', $temp_ability_title2);

        $temp_ability_title3 = '';
        if (!empty($ability_info['ability_description'])){
            $temp_description = self::get_parsed_ability_description($ability_info);
            $temp_ability_title3 = $temp_description;
        }

        $temp_ability_title = implode(' // ', array_filter(array(
            $temp_ability_title1,
            $temp_ability_title2,
            $temp_ability_title3
            )));

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
        global $this_current_uri, $this_current_url, $db;
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
        global $this_current_uri, $this_current_url, $db;
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
        $ability_info_energy_percent = !empty($ability_info['ability_energy_percent']) ? true : false;

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
        $ability_info_description =  self::get_parsed_ability_description($ability_info);
        $ability_info_class_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
        if (!empty($ability_info['ability_type2'])){ $ability_info_class_type = $ability_info_class_type != 'none' ? $ability_info_class_type.'_'.$ability_info['ability_type2'] : $ability_info['ability_type2']; }
        $ability_info_title = rpg_ability::print_editor_title_markup($robot_info, $ability_info);
        $ability_info_title_tooltip = htmlentities($ability_info_title, ENT_QUOTES, 'UTF-8');
        $temp_select_options = str_replace('value="'.$ability_info_token.'"', 'value="'.$ability_info_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);

        $type_or_none = $ability_info['ability_type'] ? $ability_info['ability_type'] : 'none';
        $type2_or_false = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : false;

        $btn_type = 'ability_type ability_type_'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '');
        $btn_info_circle = '<span class="info color" data-click-tooltip="'.$ability_info_title_tooltip.'" data-tooltip-type="'.$btn_type.'">';
            $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$type_or_none.'"></i>';
            //if (!empty($type2_or_false)){ $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$type2_or_false.'"></i>'; }
        $btn_info_circle .= '</span>';

        $cost_info_pips = '';
        if (!empty($ability_info_energy)){
            $cost_info_pips .= '<span class="cost">';
                $cost_info_pips .= str_repeat('&bull;', ceil($ability_info_energy / 4));
            $cost_info_pips .= '</span>';
        }

        //'style="border-left-width: '.(1 + $ability_info_energy).'px;" '.

        $ability_info_title_html = '';
        $ability_info_title_html .= '<label style="background-image: url(images/abilities/'.$ability_info_token.'/icon_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">';
            $ability_info_title_html .= str_replace(' ', '&nbsp;', $ability_info_name);
            $ability_info_title_html .= '<span class="arrow"><i class="fa fas fa-angle-double-down"></i></span>';
        $ability_info_title_html .= '</label>';

        $ability_info_title_html .= $btn_info_circle;
        $ability_info_title_html .= $cost_info_pips;

        $this_select_markup = '<a '.
            'class="ability_name type type_'.$ability_info_class_type.'" '.
            'data-id="'.$ability_info_id.'" '.
            'data-key="'.$ability_key.'" '.
            'data-player="'.$player_info['player_token'].'" '.
            'data-robot="'.$robot_info['robot_token'].'" '.
            'data-ability="'.$ability_info_token.'" '.
            'data-type="'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').'" '.
            'data-type2="'.(!empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : '').'" '.
            'data-cost="'.$ability_info_energy.'" '.
            'data-power="'.$ability_power.'" '.
            //'title="'.$ability_info_title_plain.'" '.
            //'data-tooltip="'.$ability_info_title_tooltip.'"'.
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
            'ability_energy_percent' => $this->ability_energy_percent,
            'ability_speed' => $this->ability_speed,
            'ability_speed2' => $this->ability_speed2,
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
            'ability_base_speed2' => $this->ability_base_speed2,
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
        global $this_current_uri, $this_current_url;
        global $mmrpg_database_abilities, $mmrpg_database_abilities, $mmrpg_database_types;
        global $db;

        // Collect global indexes for easier search
        $mmrpg_types = rpg_type::get_index(true);

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

        // If this ability is not complete, we can't show sprites or records
        if (!$ability_info['ability_flag_complete']){
            $print_options['show_sprites'] = false;
            $print_options['show_records'] = false;
        }
        // If this ability is a mecha weapon, don't show records
        if ($ability_info['ability_class'] != 'master'){
            $print_options['show_records'] = false;
        }
        // If this is an empty type ability, don't show the robot's it can equip to
        if ($ability_info['ability_type'] === 'empty'){
            $print_options['show_robots'] = false;
        }


        // Collect the database records for this ability
        if ($print_options['show_records']){

            // Pull in global DB and preset the record array
            global $db;
            $temp_ability_records = array('ability_unlocked' => 0, 'ability_equipped' => 0);

            // Check to see if a recent record already exists and use it if possible
            $temp_fields = implode(', ', array_keys($temp_ability_records));
            $record_token = $ability_info['ability_token'];
            $existing_record = $db->get_array("SELECT
                record_id,
                record_time,
                ability_token,
                {$temp_fields}
                FROM mmrpg_records_abilities
                WHERE ability_token = '{$record_token}'
                ;");

            // If the record exists and isn't too old, loop through and collect
            if (!empty($existing_record)
                && $existing_record['record_time'] >= MMRPG_CONFIG_LAST_SAVE_DATE){
                foreach ($temp_ability_records AS $token => $value){
                    if (!empty($existing_record[$token])){
                        $temp_ability_records[$token] = $existing_record[$token];
                        continue;
                    }
                }
            }
            // Otherwise, if record not exists or too old, generate a new one
            else {

                // Collect temp ability records directly from the database
                $temp_ability_records = $db->get_array("SELECT
                    COUNT(DISTINCT(unlocked.user_id)) AS ability_unlocked,
                    0 AS ability_equipped
                    FROM mmrpg_users_abilities_unlocked AS unlocked
                    LEFT JOIN mmrpg_index_abilities AS abilities ON abilities.ability_token = unlocked.ability_token
                    LEFT JOIN mmrpg_leaderboard AS leaderboard ON leaderboard.user_id = unlocked.user_id
                    WHERE
                    unlocked.ability_token = '{$record_token}'
                    AND abilities.ability_flag_complete = 1
                    AND abilities.ability_flag_unlockable = 1
                    AND leaderboard.board_points > 0
                    ;");

                // Now that we have the data, either insert or update the record in the db
                if (empty($existing_record)){
                    $insert_array = array();
                    $insert_array['record_time'] = MMRPG_CONFIG_LAST_SAVE_DATE;
                    $insert_array['ability_token'] = $record_token;
                    foreach ($temp_ability_records AS $token => $value){ $insert_array[$token] = $value; }
                    $db->insert('mmrpg_records_abilities', $insert_array);
                } else {
                    $update_array = array();
                    $update_array['record_time'] = MMRPG_CONFIG_LAST_SAVE_DATE;
                    foreach ($temp_ability_records AS $token => $value){ $update_array[$token] = $value; }
                    $db->update('mmrpg_records_abilities', $update_array, array('ability_token' => $record_token));
                }


            }


        }

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
                            <? if ($ability_info['ability_flag_complete']){ ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: url(images/abilities/<?= $ability_image_token ?>/icon_right_<?= $ability_image_size_text ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>);" class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon"><?= $ability_info['ability_name']?>'s Icon</div></div>
                            <? } else { ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon">No Image</div></div>
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
                                <? if($ability_info['ability_flag_complete']): ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Cost :</label>
                                            <span class="ability_stat"><?= !empty($ability_info['ability_energy']) ? $ability_info['ability_energy'].(!empty($ability_info['ability_energy_percent']) ? '%' : '').' WE' : '-' ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  class="right">
                                            <? $temp_target_index = json_decode(MMRPG_SETTINGS_ABILITY_TARGETINDEX, true); ?>
                                            <label style="display: block; float: left;">Target :</label>
                                            <span class="ability_stat"><?= !empty($ability_info['ability_target']) && isset($temp_target_index[$ability_info['ability_target']]) ? $temp_target_index[$ability_info['ability_target']] : 'Auto' ?></span>
                                        </td>
                                    </tr>
                                <? else: ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Cost :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Target :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                            </tbody>
                        </table>

                        <table class="full extras">
                            <tbody>
                                <? if($ability_info['ability_flag_complete']): ?>
                                    <tr>
                                        <td class="right">
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
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Value :</label>
                                            <?
                                            // Collect this ability's price and/or BP value where applicable
                                            $value_rows = array();
                                            if (true){
                                                if (!empty($ability_info['ability_price'])){
                                                    $value_rows[] = '<span class="ability_stat">'.number_format($ability_info['ability_price'], 0, '.', ',').' z</span>';
                                                    $value_rows[] = '<span class="ability_stat">'.number_format(ceil($ability_info['ability_price'] / 2), 0, '.', ',').' BP</span>';
                                                } elseif (!empty($ability_info['ability_value'])){
                                                    $value_rows[] = '<span class="ability_stat">'.number_format($ability_info['ability_value'], 0, '.', ',').' BP</span>';
                                                }
                                            }
                                            if (empty($value_rows)){ $value_rows = '<span class="ability_stat">-</span>'; }
                                            else { $value_rows = implode(' / ', $value_rows); }
                                            echo $value_rows.PHP_EOL;
                                            ?>
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
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Value :</label>
                                            <span class="ability_stat">-</span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                            </tbody>
                        </table>

                        <? if ($ability_info['ability_flag_complete']
                            && !empty($ability_info['ability_description'])): ?>
                            <table class="full description">
                                <tbody>
                                    <tr>
                                        <td class="right">
                                            <div class="ability_description" style="white-space: normal; text-align: left; <?= $print_options['layout_style'] == 'event' ? 'font-size: 12px; ' : '' ?> ">
                                                <?= self::get_parsed_ability_description($ability_info); ?>
                                            </div>
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
                    if ($print_options['show_records']){ $section_tabs[] = array('records', 'Records', false); }
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

                    <h2 <?= $print_options['layout_style'] == 'website' ? 'id="sprites"' : '' ?> class="header header_full sprites_header <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left; overflow: hidden; height: auto;">
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
                        if (!empty($ability_info['ability_image_editor'])){ $editor_ids[] = $ability_info['ability_image_editor']; }
                        if (!empty($ability_info['ability_image_editor2'])){ $editor_ids[] = $ability_info['ability_image_editor2']; }
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
                        if (!empty($ability_info['ability_image_editor3'])){
                            $extra_editors = strstr($ability_info['ability_image_editor3'], ',') ? explode(',', $ability_info['ability_image_editor3']) : array($ability_info['ability_image_editor3']);
                            foreach ($extra_editors AS $custom_name){ $temp_editor_titles[] = '<strong>'.trim($custom_name).'</strong>'; }
                        }
                        if (!empty($temp_editor_titles)){
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

                <? if ($print_options['show_robots']): ?>

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
                                                if (!empty($this_robot['robot_flag_hidden'])){ continue; }
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
                                                    if ($this_robot['robot_class'] == 'boss'){ $temp_db_url = 'database/bosses/'; }
                                                    elseif ($this_robot['robot_class'] == 'mecha'){ $temp_db_url = 'database/mechas/'; }
                                                    else { $temp_db_url = 'database/robots/'; }
                                                    $temp_markup = '<a href="'.MMRPG_CONFIG_ROOTURL.$temp_db_url.$this_robot['robot_token'].'/"  class="robot_name robot_type robot_type_'.(!empty($this_robot['robot_core']) ? $this_robot['robot_core'].(!empty($this_robot['robot_core2']) ? '_'.$this_robot['robot_core2'] : '') : 'none').'" style="'.($this_robot_image == 'robot' ? 'opacity: 0.3; ' : '').'">';
                                                    $temp_markup .= '<span class="chrome">'.$this_robot_sprite_html.$this_robot_title_html.'</span>';
                                                    $temp_markup .= '</a>';
                                                    $temp_string[] = $temp_markup;
                                                    $robot_key++;
                                                    continue;
                                                }
                                            }
                                            if (empty($temp_string) && in_array($ability_info['ability_token'], $temp_global_abilities)){
                                                $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$ability_info['ability_name'].' can be equipped by <em>any</em> robot master!</div>';
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

                    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
                        <div class="link_wrapper">
                            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                        </div>
                    <? endif; ?>

                <? endif; ?>

                <? if($print_options['show_records']): ?>

                    <h2 id="records" class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
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
                                        <label>Unlocked By : </label>
                                        <span class="ability_record"><?= $temp_ability_records['ability_unlocked'] == 1 ? '1 Player' : number_format($temp_ability_records['ability_unlocked'], 0, '.', ',').' Players' ?></span>
                                    </td>
                                </tr>
                                <? if (!empty($temp_ability_records['ability_equipped'])){ ?>
                                    <tr>
                                        <td class="right">
                                            <label>Equipped To : </label>
                                            <span class="ability_record"><?= $temp_ability_records['ability_equipped'] == 1 ? '1 Robot' : number_format($temp_ability_records['ability_equipped'], 0, '.', ',').' Robots' ?></span>
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

    // Define a static function to use as a common action for all stat boosting without any mods
    public static function ability_function_fixed_stat_boost($target_robot, $stat_type, $boost_amount, $trigger_object = false, $trigger_options = array()){
        $trigger_options['is_fixed_amount'] = true;
        self::ability_function_stat_boost($target_robot, $stat_type, $boost_amount, $trigger_object, $trigger_options);
        return true;
    }

    // Define a static function to use as a common action for all stat breaking without any mods
    public static function ability_function_fixed_stat_break($target_robot, $stat_type, $break_amount, $trigger_object = false, $trigger_options = array()){
        $trigger_options['is_fixed_amount'] = true;
        self::ability_function_stat_break($target_robot, $stat_type, $break_amount, $trigger_object, $trigger_options);
        return true;
    }

    // Define a static function to use as a common action for all stat resetting
    public static function ability_function_stat_reset($target_robot, $stat_type, $trigger_object = false, $trigger_options = array()){
        $trigger_options['is_fixed_amount'] = true;
        if ($target_robot->counters[$stat_type.'_mods'] < 0){
            $boost_amount = $target_robot->counters[$stat_type.'_mods'] * -1;
            self::ability_function_stat_boost($target_robot, $stat_type, $boost_amount, $trigger_object, $trigger_options);
        } elseif ($target_robot->counters[$stat_type.'_mods'] > 0){
            $break_amount = $target_robot->counters[$stat_type.'_mods'];
            self::ability_function_stat_break($target_robot, $stat_type, $break_amount, $trigger_object, $trigger_options);
        } else {
            return false;
        }
    }

    // Define a static function to use as a common action for all stat boosting
    public static function ability_function_stat_boost($target_robot, $stat_type, $boost_amount, $trigger_object = false, $trigger_options = array()){
        //error_log('ability_function_stat_boost('.$target_robot->robot_token.', '.$stat_type.', '.$boost_amount.', '.gettype($trigger_object).', '.print_r($trigger_options, true).')');

        // Collect or defined required variables from the trigger options
        $initiator_robot = isset($trigger_options['initiator_robot']) ? $trigger_options['initiator_robot'] : false;
        $success_frame = isset($trigger_options['success_frame']) ? $trigger_options['success_frame'] : 9;
        $failure_frame = isset($trigger_options['failure_frame']) ? $trigger_options['failure_frame'] : 9;
        $extra_text = isset($trigger_options['extra_text']) ? $trigger_options['extra_text'] : '';
        $is_redirect = isset($trigger_options['is_redirect']) ? $trigger_options['is_redirect'] : false;
        $allow_custom_effects = isset($trigger_options['allow_custom_effects']) ? $trigger_options['allow_custom_effects'] : true;
        $is_fixed_amount = isset($trigger_options['is_fixed_amount']) ? $trigger_options['is_fixed_amount'] : false;
        $skip_canvas_header = isset($trigger_options['skip_canvas_header']) ? $trigger_options['skip_canvas_header'] : false;
        $skip_failure_events = isset($trigger_options['skip_failure_events']) ? $trigger_options['skip_failure_events'] : false;

        // Exit or redirect if amount doesn't make sense here
        if (empty($boost_amount)){
            return false;
        } elseif ($boost_amount < 0){
            return self::ability_function_stat_break(
                $target_robot,
                $stat_type,
                ($boost_amount * -1),
                $trigger_object,
                $trigger_options
                );
        }

        // Do not boost stats if the battle is over
        if ($target_robot->battle->battle_status === 'complete'){ return false; }
        elseif ($target_robot->robot_status === 'disabled' || $target_robot->robot_energy <= 0){ return false; }

        // Collect the trigger ability or object from the args
        $trigger_ability = !empty($trigger_object) && isset($trigger_object->ability_token) ? $trigger_object : false;
        $trigger_item = !empty($trigger_object) && isset($trigger_object->item_token) ? $trigger_object : false;
        $trigger_skill = !empty($trigger_object) && isset($trigger_object->skill_token) ? $trigger_object : false;

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $options->stat_type = $stat_type;
        $options->boost_amount = $boost_amount;
        $options->success_frame = $success_frame;
        $options->failure_frame = $failure_frame;
        $options->extra_text = $extra_text;
        $options->is_redirect = $is_redirect;
        $options->allow_custom_effects = $allow_custom_effects;
        $options->is_fixed_amount = $is_fixed_amount;
        $extra_objects = array('options' => $options);
        $extra_objects['this_ability'] = $trigger_ability;
        $extra_objects['this_item'] = $trigger_item;
        $extra_objects['this_skill'] = $trigger_skill;
        $extra_objects['initiator_robot'] = !empty($initiator_robot) ? $initiator_robot : $target_robot;
        $extra_objects['recipient_robot'] = $target_robot;

        // Trigger this robot's custom function if one has been defined for this context
        if ($options->allow_custom_effects && !$options->is_redirect){
            if (!empty($initiator_robot) && $initiator_robot !== $target_robot){
                $initiator_robot->trigger_custom_function('rpg-ability_stat-boost_before', $extra_objects);
                if ($options->return_early){ return $options->return_value; }
            }
            $target_robot->trigger_custom_function('rpg-ability_stat-boost_before', $extra_objects);
            if ($options->return_early){ return $options->return_value; }
        }

        // Compensate for malformed arguments
        if (!is_numeric($options->success_frame)){ $options->success_frame = 0; }
        if (!is_numeric($options->failure_frame)){ $options->failure_frame = 9; }
        if (!is_string($options->extra_text)){ $options->extra_text = ''; }

        // Add a break to the extra text if not empty
        if (!empty($options->extra_text) && !strstr($options->extra_text, '<br />')){ $options->extra_text .= ' <br /> '; }

        // Define the counter name we'll be working with here
        $mods_token = $options->stat_type.'_mods';

        // If ability not provided then generate a new one
        $hide_ability_header = false;
        if (!$trigger_ability){
            $hide_ability_header = true;
            $trigger_ability = rpg_game::get_ability($target_robot->battle, $target_robot->player, $target_robot, array('ability_token' => $options->stat_type.'-boost'));
        }

        // Trigger this robot's custom function if one has been defined for this context
        if ($options->allow_custom_effects && !$options->is_redirect){
            if (!empty($initiator_robot) && $initiator_robot !== $target_robot){
                $initiator_robot->trigger_custom_function('rpg-ability_stat-boost_middle', $extra_objects);
                if ($options->return_early){ return $options->return_value; }
            }
            $target_robot->trigger_custom_function('rpg-ability_stat-boost_middle', $extra_objects);
            if ($options->return_early){ return $options->return_value; }
        }

        // Increase the target's stat modifier only if it's not already at max
        if (($options->boost_amount > 0 && $target_robot->counters[$mods_token] < MMRPG_SETTINGS_STATS_MOD_MAX)
            || ($options->boost_amount < 0 && $target_robot->counters[$mods_token] > MMRPG_SETTINGS_STATS_MOD_MIN)){

            // Increase the stat by X stages and then floor if too high
            $old_mod_value = $target_robot->counters[$mods_token];
            $rel_boost_amount = $options->boost_amount;
            $target_robot->counters[$mods_token] += $rel_boost_amount;
            if ($target_robot->counters[$mods_token] > MMRPG_SETTINGS_STATS_MOD_MAX){
                $rel_boost_amount -= ($target_robot->counters[$mods_token] - MMRPG_SETTINGS_STATS_MOD_MAX);
                $target_robot->counters[$mods_token] = MMRPG_SETTINGS_STATS_MOD_MAX;
            }
            $target_robot->update_session();

            // Define the boost text based on how much was applied
            if (empty($target_robot->counters[$mods_token])){ $boost_text = 'returned to normal'; }
            elseif ($rel_boost_amount >= 3){ $boost_text = 'rose drastically'; }
            elseif ($rel_boost_amount >= 2){ $boost_text = 'sharply rose'; }
            else { $boost_text = 'rose'; }

            // Define the sound effect variables for this stat boost
            $boost_sounds = array();
            for ($i = 0; $i < $rel_boost_amount; $i++){
                $boost_sounds[] = array(
                    'name' => 'recovery-stats',
                    'volume' => 1.0 - ($i * 0.1),
                    'delay' => 0 + ($i * 80)
                    );
            }

            // Target this robot's self to show the success message
            $amount_text = ''; //' (old:'.$old_mod_value.', amount:'.$options->boost_amount.', rel-amount:'.$rel_boost_amount.', result:'.$target_robot->counters[$mods_token].')';
            $target_options = array('frame' => 'taunt', 'success' => array($options->success_frame, -2, 0, -10, $options->extra_text.$target_robot->print_name().'&#39;s '.$options->stat_type.' '.$boost_text.$amount_text.'!'));
            $target_results = array('total_actions' => 1, 'total_strikes' => 1, 'recovery_kind' => $options->stat_type, 'this_amount' => $rel_boost_amount);
            $trigger_options = array('override_trigger_kind' => 'recovery');
            if (!empty($boost_sounds)){ $trigger_options['event_flag_sound_effects'] = $boost_sounds; }
            if ($trigger_item || $trigger_skill){
                if (!$skip_canvas_header){ $trigger_object->set_flag('force_canvas_header', true); }
                else { $trigger_ability->set_flag('skip_canvas_header', true); }
                if ($trigger_item){ $trigger_object->item_results = $target_results; }
                elseif ($trigger_skill){ $trigger_object->skill_results = $target_results; }
                $trigger_object->target_options_update($target_options);
                $target_robot->trigger_target($target_robot, $trigger_object, $trigger_options);
                $trigger_object->unset_flag('force_canvas_header');
                $trigger_object->unset_flag('skip_canvas_header');
            } else {
                $trigger_ability->set_flag('skip_canvas_header', true);
                $trigger_ability->ability_results = $target_results;
                $trigger_ability->target_options_update($target_options);
                $target_robot->trigger_target($target_robot, $trigger_ability, $trigger_options);
                $trigger_ability->unset_flag('skip_canvas_header');
            }

            // Update the robot's counter for applied mods
            if (!isset($target_robot->counters[$options->stat_type.'_boosts_applied'])){ $target_robot->counters[$options->stat_type.'_boosts_applied'] = 0; }
            $target_robot->counters[$options->stat_type.'_boosts_applied'] += $rel_boost_amount;

            // Only update triggered boost history if boost was actually dealt
            if ($rel_boost_amount > 0){

                // Update this robot's history with the triggered boost amount
                $target_robot->history['triggered_boosts'][] = $rel_boost_amount;
                $target_robot->history['triggered_boosts_by'][] = $trigger_ability->ability_token;

                // Update the robot's history with the triggered boost types
                if (!empty($trigger_ability->ability_type)){
                    $temp_types = array();
                    $temp_types[] = $trigger_ability->ability_type;
                    if (!empty($trigger_ability->ability_type2)){ $temp_types[] = $trigger_ability->ability_type2; }
                    $target_robot->history['triggered_boosts_types'][] = $temp_types;
                } else {
                    $target_robot->history['triggered_boosts_types'][] = null; //array();
                }

            }

        } else {

            // Target this robot's self to show the failure message
            if (!$skip_failure_events){
                $boost_sounds = array();
                $boost_sounds[] = array('name' => 'no-effect', 'volume' => 1.0);
                $amount_text = ''; //' ('.($target_robot->counters[$mods_token] > 0 ? '+'.$target_robot->counters[$mods_token] : $target_robot->counters[$mods_token]).')';
                $target_options = array('frame' => 'defend', 'success' => array($options->failure_frame, -2, 0, -10, $options->extra_text.$target_robot->print_name().'&#39;s '.$options->stat_type.' won\'t go any higher'.$amount_text.'&hellip;'));
                $trigger_options = array();
                if (!empty($boost_sounds)){ $trigger_options['event_flag_sound_effects'] = $boost_sounds; }
                if ($trigger_item || $trigger_skill){
                    $trigger_object->set_flag('skip_canvas_header', true);
                    $trigger_object->target_options_update($target_options);
                    $target_robot->trigger_target($target_robot, $trigger_object, $trigger_options);
                    $trigger_object->unset_flag('skip_canvas_header');
                } else {
                    $trigger_ability->set_flag('skip_canvas_header', true);
                    $trigger_ability->target_options_update($target_options);
                    $target_robot->trigger_target($target_robot, $trigger_ability, $trigger_options);
                    $trigger_ability->unset_flag('skip_canvas_header');
                }
            }
            $target_robot->counters[$mods_token] = MMRPG_SETTINGS_STATS_MOD_MAX;
            $target_robot->update_session();

        }

        // Trigger this robot's custom function if one has been defined for this context
        if ($options->allow_custom_effects && !$options->is_redirect){
            if (!empty($initiator_robot) && $initiator_robot !== $target_robot){ $initiator_robot->trigger_custom_function('rpg-ability_stat-boost_after', $extra_objects); }
            $target_robot->trigger_custom_function('rpg-ability_stat-boost_after', $extra_objects);
        }

    }

    // Define a static function to use as a common action for all stat breaking
    public static function ability_function_stat_break($target_robot, $stat_type, $break_amount, $trigger_object = false, $trigger_options = array()){
        //error_log('ability_function_stat_break('.$target_robot->robot_token.', '.$stat_type.', '.$break_amount.', '.gettype($trigger_object).', '.print_r($trigger_options, true).')');

        // Collect or defined required variables from the trigger options
        $initiator_robot = isset($trigger_options['initiator_robot']) ? $trigger_options['initiator_robot'] : false;
        $success_frame = isset($trigger_options['success_frame']) ? $trigger_options['success_frame'] : 9;
        $failure_frame = isset($trigger_options['failure_frame']) ? $trigger_options['failure_frame'] : 9;
        $extra_text = isset($trigger_options['extra_text']) ? $trigger_options['extra_text'] : '';
        $is_redirect = isset($trigger_options['is_redirect']) ? $trigger_options['is_redirect'] : false;
        $allow_custom_effects = isset($trigger_options['allow_custom_effects']) ? $trigger_options['allow_custom_effects'] : true;
        $is_fixed_amount = isset($trigger_options['is_fixed_amount']) ? $trigger_options['is_fixed_amount'] : false;
        $skip_canvas_header = isset($trigger_options['skip_canvas_header']) ? $trigger_options['skip_canvas_header'] : false;
        $skip_failure_events = isset($trigger_options['skip_failure_events']) ? $trigger_options['skip_failure_events'] : false;

        // Exit or redirect if amount doesn't make sense here
        if (empty($break_amount)){
            return false;
        } elseif ($break_amount < 0){
            return self::ability_function_stat_boost(
                $target_robot,
                $stat_type,
                ($break_amount * -1),
                $trigger_object,
                $trigger_options
                );
            }

        // Do not boost stats if the battle is over
        if ($target_robot->battle->battle_status === 'complete'){ return false; }
        elseif ($target_robot->robot_status === 'disabled' || $target_robot->robot_energy <= 0){ return false; }

        // Collect the trigger ability or object from the args
        $trigger_ability = !empty($trigger_object) && isset($trigger_object->ability_token) ? $trigger_object : false;
        $trigger_item = !empty($trigger_object) && isset($trigger_object->item_token) ? $trigger_object : false;
        $trigger_skill = !empty($trigger_object) && isset($trigger_object->skill_token) ? $trigger_object : false;

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $options->stat_type = $stat_type;
        $options->break_amount = $break_amount;
        $options->success_frame = $success_frame;
        $options->failure_frame = $failure_frame;
        $options->extra_text = $extra_text;
        $options->is_redirect = $is_redirect;
        $options->allow_custom_effects = $allow_custom_effects;
        $options->is_fixed_amount = $is_fixed_amount;
        $extra_objects = array('options' => $options);
        $extra_objects['this_ability'] = $trigger_ability;
        $extra_objects['this_item'] = $trigger_item;
        $extra_objects['this_skill'] = $trigger_skill;
        $extra_objects['initiator_robot'] = !empty($initiator_robot) ? $initiator_robot : $target_robot;
        $extra_objects['recipient_robot'] = $target_robot;

        // Trigger this robot's custom function if one has been defined for this context
        if ($options->allow_custom_effects && !$options->is_redirect){
            if (!empty($initiator_robot) && $initiator_robot !== $target_robot){
                $initiator_robot->trigger_custom_function('rpg-ability_stat-break_before', $extra_objects);
                if ($options->return_early){ return $options->return_value; }
            }
            $target_robot->trigger_custom_function('rpg-ability_stat-break_before', $extra_objects);
            if ($options->return_early){ return $options->return_value; }
        }

        // Compensate for malformed arguments
        if (!is_numeric($options->success_frame)){ $options->success_frame = 0; }
        if (!is_numeric($options->failure_frame)){ $options->failure_frame = 9; }
        if (!is_string($options->extra_text)){ $options->extra_text = ''; }

        // Add a break to the extra text if not empty
        if (!empty($options->extra_text) && !strstr($options->extra_text, '<br />')){ $options->extra_text .= ' <br /> '; }

        // Define the counter name we'll be working with here
        $mods_token = $options->stat_type.'_mods';

        // If ability not provided then generate a new one
        $hide_ability_header = false;
        if (!$trigger_ability){
            $hide_ability_header = true;
            $trigger_ability = rpg_game::get_ability($target_robot->battle, $target_robot->player, $target_robot, array('ability_token' => $options->stat_type.'-break'));
        }

        // Trigger this robot's custom function if one has been defined for this context
        if ($options->allow_custom_effects && !$options->is_redirect){
            if (!empty($initiator_robot) && $initiator_robot !== $target_robot){
                $initiator_robot->trigger_custom_function('rpg-ability_stat-break_middle', $extra_objects);
                if ($options->return_early){ return $options->return_value; }
            }
            $target_robot->trigger_custom_function('rpg-ability_stat-break_middle', $extra_objects);
            if ($options->return_early){ return $options->return_value; }
        }

        // Increase the target's stat modifier only if it's not already at min
        if (($options->break_amount > 0 && $target_robot->counters[$mods_token] > MMRPG_SETTINGS_STATS_MOD_MIN)
            || ($options->break_amount < 0 && $target_robot->counters[$mods_token] < MMRPG_SETTINGS_STATS_MOD_MAX)){

            // Decrease the stat by X stages and then ceil if too low
            $old_mod_value = $target_robot->counters[$mods_token];
            $rel_break_amount = $options->break_amount;
            $target_robot->counters[$mods_token] -= $rel_break_amount;
            if ($target_robot->counters[$mods_token] < MMRPG_SETTINGS_STATS_MOD_MIN){
                $rel_break_amount += ($target_robot->counters[$mods_token] + (MMRPG_SETTINGS_STATS_MOD_MIN * -1));
                $target_robot->counters[$mods_token] = MMRPG_SETTINGS_STATS_MOD_MIN;
            }
            $target_robot->update_session();

            // Define the break text based on how much was applied
            if (empty($target_robot->counters[$mods_token])){ $break_text = 'returned to normal'; }
            elseif ($rel_break_amount >= 3){ $break_text = 'severely fell'; }
            elseif ($rel_break_amount >= 2){ $break_text = 'harshly fell'; }
            else { $break_text = 'fell'; }

            // Define the sound effect variables for this stat boost
            $break_sounds = array();
            for ($i = 0; $i < $rel_break_amount; $i++){
                $break_sounds[] = array(
                    'name' => 'damage-stats',
                    'volume' => 1.0 - ($i * 0.1),
                    'delay' => 0 + ($i * 100)
                    );
            }

            // Target this robot's self to show the success message
            $amount_text = ''; //' (old:'.$old_mod_value.', amount:'.$options->break_amount.', rel-amount:'.$rel_break_amount.', result:'.$target_robot->counters[$mods_token].')';
            $target_options = array('frame' => 'defend', 'success' => array($options->success_frame, -2, 0, -10, $options->extra_text.$target_robot->print_name().'&#39;s '.$options->stat_type.' '.$break_text.$amount_text.'!'));
            $target_results = array('total_actions' => 1, 'total_strikes' => 1, 'damage_kind' => $options->stat_type, 'this_amount' => $rel_break_amount);
            $trigger_options = array('override_trigger_kind' => 'damage');
            if (!empty($break_sounds)){ $trigger_options['event_flag_sound_effects'] = $break_sounds; }
            if ($trigger_item || $trigger_skill){
                if (!$skip_canvas_header){ $trigger_object->set_flag('force_canvas_header', true); }
                else { $trigger_ability->set_flag('skip_canvas_header', true); }
                if ($trigger_item){ $trigger_object->item_results = $target_results; }
                elseif ($trigger_skill){ $trigger_object->skill_results = $target_results; }
                $trigger_object->target_options_update($target_options);
                $target_robot->trigger_target($target_robot, $trigger_object, $trigger_options);
                $trigger_object->unset_flag('force_canvas_header');
                $trigger_object->unset_flag('skip_canvas_header');
            } else {
                $trigger_ability->set_flag('skip_canvas_header', true);
                $trigger_ability->ability_results = $target_results;
                $trigger_ability->target_options_update($target_options);
                $target_robot->trigger_target($target_robot, $trigger_ability, $trigger_options);
                $trigger_ability->unset_flag('skip_canvas_header');
            }

            // Update the robot's counter for applied mods
            if (!isset($target_robot->counters[$options->stat_type.'_breaks_applied'])){ $target_robot->counters[$options->stat_type.'_breaks_applied'] = 0; }
            $target_robot->counters[$options->stat_type.'_breaks_applied'] += $rel_break_amount;
            $target_robot->update_session();

            // Only update triggered break history if break was actually dealt
            if ($rel_break_amount > 0){

                // Update this robot's history with the triggered break amount
                $target_robot->history['triggered_breaks'][] = $rel_break_amount;
                $target_robot->history['triggered_breaks_by'][] = $trigger_ability->ability_token;

                // Update the robot's history with the triggered break types
                if (!empty($trigger_ability->ability_type)){
                    $temp_types = array();
                    $temp_types[] = $trigger_ability->ability_type;
                    if (!empty($trigger_ability->ability_type2)){ $temp_types[] = $trigger_ability->ability_type2; }
                    $target_robot->history['triggered_breaks_types'][] = $temp_types;
                } else {
                    $target_robot->history['triggered_breaks_types'][] = null; //array();
                }

            }

        } else {

            // Target this robot's self to show the failure message
            if (!$skip_failure_events){
                $break_sounds = array();
                $break_sounds[] = array('name' => 'no-effect', 'volume' => 1.0);
                $amount_text = ''; //' ('.($target_robot->counters[$mods_token] > 0 ? '+'.$target_robot->counters[$mods_token] : $target_robot->counters[$mods_token]).')';
                $target_options = array('frame' => 'base', 'success' => array($options->failure_frame, -2, 0, -10, $options->extra_text.$target_robot->print_name().'&#39;s '.$options->stat_type.' won\'t go any lower'.$amount_text.'&hellip;'));
                $trigger_options = array();
                if (!empty($boost_sounds)){ $trigger_options['event_flag_sound_effects'] = $boost_sounds; }
                if ($trigger_item || $trigger_skill){
                    $trigger_object->set_flag('skip_canvas_header', true);
                    $trigger_object->target_options_update($target_options);
                    $target_robot->trigger_target($target_robot, $trigger_object, $trigger_options);
                    $trigger_object->unset_flag('skip_canvas_header');
                } else {
                    $trigger_ability->set_flag('skip_canvas_header', true);
                    $trigger_ability->target_options_update($target_options);
                    $target_robot->trigger_target($target_robot, $trigger_ability, $trigger_options);
                    $trigger_ability->unset_flag('skip_canvas_header');
                }
            }
            $target_robot->counters[$mods_token] = MMRPG_SETTINGS_STATS_MOD_MIN;
            $target_robot->update_session();

        }

        // Trigger this robot's custom function if one has been defined for this context
        if ($options->allow_custom_effects && !$options->is_redirect){
            if (!empty($initiator_robot) && $initiator_robot !== $target_robot){ $initiator_robot->trigger_custom_function('rpg-ability_stat-break_after', $extra_objects); }
            $target_robot->trigger_custom_function('rpg-ability_stat-break_after', $extra_objects);
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
    public static function ability_function_elemental_shot($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

        // Extract all objects into the current scope
        extract($objects);

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $extra_objects = array('this_ability' => $this_ability, 'options' => $options);

        // Check speed to see how many times buster shot can hit
        $options->num_buster_shots = 1;
        $options->max_buster_shots = $this_robot->robot_level >= 100 ? $this_robot->robot_level : 99;
        if ($this_robot->robot_speed > $target_robot->robot_speed){
            $options->num_buster_shots = floor($this_robot->robot_speed / $target_robot->robot_speed);
            if ($options->num_buster_shots > $options->max_buster_shots){ $options->num_buster_shots = $options->max_buster_shots; }
        }

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-shot_before', $extra_objects);
        if ($options->return_early){ return $options->return_value; }

        // Predefine the damage amount so we can reduce with subsequent shots
        $energy_damage_amount = $this_ability->ability_damage;

        // Loop through the allowed number of shots and fire that many times
        $ineffective_shots = 0;
        $num_target_attachments = count($target_robot->get_attachments());
        for ($num_shot = 1; $num_shot <= $options->num_buster_shots; $num_shot++){

            // Update the ability's target options and trigger
            $target_text = '';
            $target_options = array();
            if ($num_shot === 1){
                $target_text = $this_robot->print_name().' fires '.(preg_match('/^(a|e|i|o|u)/i', $this_ability->ability_token) ? 'an' : 'a').'  '.$this_ability->print_name().'!';
            } else {
                $target_text = $this_robot->print_name().' fires another '.$this_ability->print_name().'!';
                $target_options['prevent_default_text'] = true;
            }
            $target_options['event_flag_sound_effects'] = array(
                array('name' => 'shot-sound-alt', 'volume' => 1.0)
                );
            $this_ability->target_options_update(array(
                'frame' => 'shoot',
                'success' => array(0, 95, 0, 10, $target_text)
                ));
            $this_robot->trigger_target($target_robot, $this_ability, $target_options);

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
            if ($num_shot > 1){ $energy_damage_amount -= ($energy_damage_amount * 0.10); }
            $target_robot->trigger_damage($this_robot, $this_ability, ceil($energy_damage_amount));

            // Break early if the target has been disabled
            if ($target_robot->robot_energy < 1 || $target_robot->robot_status === 'disabled'){ break; }

            // Break early if the move did virtually ineffective damage (one or less)
            $new_num_attachments = count($target_robot->get_attachments());
            if ($this_ability->ability_results['this_amount'] <= 1){ $ineffective_shots++; }
            if ($ineffective_shots >= 3 && $new_num_attachments === $num_target_attachments){ break; }
            $num_target_attachments = $new_num_attachments;

        }

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-shot_after', $extra_objects);

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-shot abilities
    public static function ability_function_elemental_shot_onload($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Reset the ability target (unless otherwise stated later)
        $this_ability->reset_target();

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $options->buster_charge_boost = 2;
        $extra_objects = array('this_ability' => $this_ability, 'options' => $options);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-shot_onload_before', $extra_objects);
        if ($options->return_early){ return $options->return_value; }

        // Loop through any attachments and boost power for each buster charge
        $temp_new_damage = $this_ability->ability_base_damage;
        $this_robot_attachments = $this_robot->get_current_attachments();
        foreach ($this_robot_attachments AS $this_attachment_token => $this_attachment_info){
            if ($this_attachment_token == 'ability_'.$this_ability->ability_type.'-buster'){
                $temp_new_damage += $options->buster_charge_boost;
            }
        }
        // Update the ability's damage with the new amount
        $this_ability->set_damage($temp_new_damage);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-shot_onload_after', $extra_objects);

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all ____-buster abilities
    public static function ability_function_elemental_buster($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

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

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $options->buster_charge_required = !isset($this_robot_attachments[$this_attachment_token]) ? true : false;
        $extra_objects = array('this_ability' => $this_ability, 'options' => $options);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-buster_before', $extra_objects);
        if ($options->return_early){ return $options->return_value; }

        // If the ability flag was not set, this ability begins charging
        if ($options->buster_charge_required){

            // Target this robot's self
            $target_options = array();
            $target_options['event_flag_sound_effects'] = array(
                array('name' => 'charge-sound', 'volume' => 0.6),
                array('name' => 'charge-sound', 'volume' => 0.8, 'delay' => 100),
                array('name' => 'charge-sound', 'volume' => 1.0, 'delay' => 300)
                );
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability, $target_options);

            // Attach this ability attachment to the robot using it
            $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

        }
        // Else if the ability flag was set, the ability is released at the target
        else {

            // Remove this ability attachment to the robot using it
            $existing_attachment_info = $this_robot->get_attachment($this_attachment_token);
            $new_attachment_info = !empty($existing_attachment_info) ? $existing_attachment_info : $this_attachment_info;
            $new_attachment_info['ability_frame'] = 0;
            $new_attachment_info['ability_frame_animate'] = array(1, 0);
            $this_robot->set_attachment($this_attachment_token, $new_attachment_info);

            // Update this ability's target options and trigger
            $target_options = array();
            $target_options['event_flag_sound_effects'] = array(
                array('name' => 'blast-sound', 'volume' => 1.0)
                );
            $this_ability->target_options_update(array(
                'frame' => 'shoot',
                'kickback' => array(-5, 0, 0),
                'success' => array(3, 100, -15, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability, $target_options);

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

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-buster_after', $extra_objects);

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-buster abilities
    public static function ability_function_elemental_buster_onload($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // Define the charge required flag based on existing attachments of this ability
        $this_robot_attachments = $this_robot->get_current_attachments();

        // Reset this ability's energy cost and target select unless otherwise stated
        $this_ability->reset_energy();
        $this_ability->reset_target();

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $options->buster_charge_required = !isset($this_robot_attachments[$this_attachment_token]) ? true : false;
        $options->weapon_energy_required =  $options->buster_charge_required ? true : false;
        $extra_objects = array('this_ability' => $this_ability, 'options' => $options);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-buster_onload_before', $extra_objects);
        if ($options->return_early){ return $options->return_value; }

        // If the ability flag had already been set, reduce the weapon energy to zero
        if (!$options->weapon_energy_required){ $this_ability->set_energy(0); }

        // If this ability is being already charged, we should put an indicator
        $is_charged = !$options->buster_charge_required ? true : false;
        if ($is_charged){
            $new_name = $this_ability->ability_base_name;
            if ($is_charged){ $new_name .= ' ✦'; }
            $this_ability->set_name($new_name);
        } else {
            $this_ability->reset_name();
        }

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-buster_onload_after', $extra_objects);

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all ____-overdrive abilities
    public static function ability_function_elemental_overdrive($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

        // Extract all objects into the current scope
        extract($objects);

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $extra_objects = array('this_ability' => $this_ability, 'options' => $options);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-overdrive_before', $extra_objects);
        if ($options->return_early){ return $options->return_value; }

        // Decrease this robot's weapon energy to zero
        $this_robot->set_weapons(0);

        // Target the opposing robot
        $target_options = array();
        $target_options['event_flag_sound_effects'] = array(
            array('name' => 'hyper-summon-sound', 'volume' => 1.0)
            );
        $this_ability->target_options_update(array(
            'kickback' => array(-5, 0, 0),
            'frame' => 'defend',
            'success' => array(0, 15, 45, 10, $this_robot->print_name().' uses the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, $target_options);

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
        $overlay_attachment_token = 'effect_'.$this_ability->ability_token;
        $overlay_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => '_effects/black-overlay',
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -12),
            'ability_frame_classes' => 'sprite_fullscreen '
            );

        // Add the black overlay attachment
        $target_robot->set_attachment($overlay_attachment_token, $overlay_attachment_info);

        // prepare the ability options
        $trigger_options = array();
        $trigger_options['event_flag_sound_effects'] = array(
            array('name' => 'blast-sound', 'volume' => 1.0)
            );
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
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
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
            $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
            $temp_target_robot->unset_attachment($overlay_attachment_token);
        }

        // Remove the black background attachment
        $this_robot->set_frame('base');
        $this_robot->unset_attachment($crest_attachment_token);

        // Now that all the damage has been dealt, allow the player to check for disabled
        $target_player->check_robots_disabled($this_player, $this_robot);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-overdrive_after', $extra_objects);

        // Return true on success
        return true;

    }

    // Define a static onload function to use as the common action for all ____-overdrive abilities
    public static function ability_function_elemental_overdrive_onload($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Create an options object for this function and populate
        $options = rpg_game::new_options_object();
        $extra_objects = array('this_ability' => $this_ability, 'options' => $options);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-overdrive_onload_before', $extra_objects);
        if ($options->return_early){ return $options->return_value; }

        // Update this abilities weapon energy to whatever the user's max is
        $this_ability->set_energy($this_robot->robot_base_weapons);

        // Calculate the user's current life damage percent for calculations
        $robot_energy_damage = $this_robot->robot_base_energy - $this_robot->robot_energy;
        $robot_energy_damage_percent = !empty($robot_energy_damage) ? ceil(($robot_energy_damage / $this_robot->robot_base_energy) * 100) : 0;

        // Multiply the user's damage by the remaining weapon energy for damage total
        $ability_damage_amount = $robot_energy_damage_percent + 1;
        $this_ability->set_damage($ability_damage_amount);

        // Trigger this robot's custom function if one has been defined for this context
        $this_robot->trigger_custom_function('rpg-ability_elemental-overdrive_onload_after', $extra_objects);

        // Return true on success
        return true;

    }

    // Define alias functions for the elemental shots until we can remove all legacy references
    public static function ability_function_shot($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){
        return self::ability_function_elemental_shot($objects, $shot_text, $damage_text, $recovery_text);
    }
    public static function ability_function_onload_shot($objects){
        return self::ability_function_elemental_shot_onload($objects);
    }
    public static function ability_function_buster($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){
        return self::ability_function_elemental_buster($objects, $shot_text, $damage_text, $recovery_text);
    }
    public static function ability_function_onload_buster($objects){
        return self::ability_function_elemental_buster_onload($objects);
    }
    public static function ability_function_overdrive($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){
        return self::ability_function_elemental_overdrive($objects, $shot_text, $damage_text, $recovery_text);
    }
    public static function ability_function_onload_overdrive($objects){
        return self::ability_function_elemental_overdrive_onload($objects);

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
            'mecha-support', 'mecha-assault', 'mecha-party', 'friend-share',
            'core-shield', 'core-laser', 'omega-pulse', 'omega-wave',
            'field-support',
            );
        // Return the list of global abilities
        return $temp_global_abilities;
    }

    // Define a static function that returns a list of globally compatible support abilities
    public static function get_global_support_abilities(){
        // Define the list of global support abilities
        $temp_global_support_abilities = array(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost',
            'energy-break', 'attack-break', 'defense-break', 'speed-break',
            'energy-swap', 'attack-swap', 'defense-swap', 'speed-swap',
            'energy-mode', 'attack-mode', 'defense-mode', 'speed-mode',
            'field-support', 'mecha-support',
            'core-shield',
            );
        // Return the list of global support abilities
        return $temp_global_support_abilities;
    }

    // Define a static function that returns a list of globally deprecated abilities we begrudgingly support
    public static function get_global_deprecated_abilities(){
        // Define the list of global deprecated abilities
        $temp_global_deprecated_abilities = array(
            'energy-shuffle', 'attack-shuffle', 'defense-shuffle', 'speed-shuffle',
            'repair-mode',
            );
        // Return the list of global deprecated abilities
        return $temp_global_deprecated_abilities;
    }

    // Define a static function that returns a list of globally compatible buster abilities
    public static function get_global_buster_abilities(){
        // Define the list of global buster abilities
        static $temp_types = array();
        if (empty($temp_types)){ $temp_types = rpg_type::get_index(false, false, false, false); }
        $temp_abilities = array('shot', 'buster', 'overdrive');
        $temp_global_buster_abilities = array();
        foreach ($temp_types AS $type_token => $type_info){
            foreach ($temp_abilities AS $ability_type){
                $temp_global_buster_abilities[] = $type_token.'-'.$ability_type;
            }
        }
        // Return the list of global buster abilities
        return $temp_global_buster_abilities;
    }

    // Define a static function that returns a list of all T1 abilities (for the purposes of auto-generation)
    public static function get_tier_one_abilities(){
        static $tier_one_abilities;
        if (!empty($tier_one_abilities)){ return $tier_one_abilities; }

        // Collect a list of relevant abilities from the database
        $tier_one_abilities_query = "SELECT
            abilities.ability_token
            -- , abilities.ability_energy
            -- , abilities.*
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_robots AS robots ON robots.robot_token = abilities.ability_master
            WHERE
            abilities.ability_flag_published = 1
            AND abilities.ability_flag_complete = 1
            AND (
                -- elemental T1 abilities
                (abilities.ability_type <> ''
                AND abilities.ability_type <> 'empty'
                AND (robots.robot_token IS NULL OR robots.robot_core NOT IN ('copy', ''))
                AND (
                    (abilities.ability_energy = 4 AND abilities.ability_token NOT LIKE '%-buster')
                    OR (abilities.ability_energy = 0 AND abilities.ability_token LIKE '%-shot')
                ))
                OR
                -- neutral T1 abilities
                abilities.ability_token IN ('buster-shot')
            )
            AND abilities.ability_class = 'master'
            ORDER BY
            abilities.ability_token ASC
            ;";
        $cache_token = md5($tier_one_abilities_query);
        $cached_index = rpg_object::load_cached_index('abilities.t1', $cache_token);
        if (!empty($cached_index)){
            $tier_one_abilities = $cached_index;
            unset($cached_index);
        } else {
            global $db;
            $temp_tier_one_abilities = $db->get_array_list($tier_one_abilities_query, 'ability_token');
            $tier_one_abilities = !empty($temp_tier_one_abilities) ? array_keys($temp_tier_one_abilities) : array();
            rpg_object::save_cached_index('abilities.t1', $cache_token, $tier_one_abilities);
        }

        // Return the keys for the requested abilities
        return $tier_one_abilities;

    }

    // Define a static function that returns a list of all T2 abilities (for the purposes of auto-generation)
    public static function get_tier_two_abilities(){
        static $tier_two_abilities;
        if (!empty($tier_two_abilities)){ return $tier_two_abilities; }

        // Collect a list of relevant abilities from the database
        $tier_two_abilities_query = "SELECT
            abilities.ability_token
            -- , abilities.ability_energy
            -- , abilities.*
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_robots AS robots ON robots.robot_token = abilities.ability_master
            WHERE
            abilities.ability_flag_published = 1
            AND abilities.ability_flag_complete = 1
            AND (
                -- elemental T2 abilities
                (abilities.ability_type <> ''
                AND abilities.ability_type <> 'empty'
                AND (
                    (abilities.ability_energy = 8)
                    OR (abilities.ability_energy = 4 AND robots.robot_core IN ('copy', ''))
                    OR (abilities.ability_energy = 4 AND abilities.ability_token LIKE '%-buster')
                ))
            )
            AND abilities.ability_class = 'master'
            ORDER BY
            abilities.ability_token ASC
            ;";
        $cache_token = md5($tier_two_abilities_query);
        $cached_index = rpg_object::load_cached_index('abilities.t2', $cache_token);
        if (!empty($cached_index)){
            $tier_two_abilities = $cached_index;
            unset($cached_index);
        } else {
            global $db;
            $temp_tier_two_abilities = $db->get_array_list($tier_two_abilities_query, 'ability_token');
            $tier_two_abilities = !empty($temp_tier_two_abilities) ? array_keys($temp_tier_two_abilities) : array();
            rpg_object::save_cached_index('abilities.t2', $cache_token, $tier_two_abilities);
        }

        // Return the keys for the requested abilities
        return $tier_two_abilities;

    }

    // Define a static function that returns a list of all T3 abilities (for the purposes of auto-generation)
    public static function get_tier_three_abilities(){
        static $tier_three_abilities;
        if (!empty($tier_three_abilities)){ return $tier_three_abilities; }

        // Collect a list of relevant abilities from the database
        $tier_three_abilities_query = "SELECT
            abilities.ability_token
            -- , abilities.ability_energy
            -- , abilities.*
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_robots AS robots ON robots.robot_token = abilities.ability_master
            WHERE
            abilities.ability_flag_published = 1
            AND abilities.ability_flag_complete = 1
            AND (
                -- elemental T3 abilities
                (abilities.ability_type <> ''
                AND abilities.ability_type <> 'empty'
                AND abilities.ability_energy > 10)
            )
            AND abilities.ability_class = 'master'
            ORDER BY
            abilities.ability_token ASC
            ;";
        $cache_token = md5($tier_three_abilities_query);
        $cached_index = rpg_object::load_cached_index('abilities.t3', $cache_token);
        if (!empty($cached_index)){
            $tier_three_abilities = $cached_index;
            unset($cached_index);
        } else {
            global $db;
            $temp_tier_three_abilities = $db->get_array_list($tier_three_abilities_query, 'ability_token');
            $tier_three_abilities = !empty($temp_tier_three_abilities) ? array_keys($temp_tier_three_abilities) : array();
            rpg_object::save_cached_index('abilities.t3', $cache_token, $tier_three_abilities);
        }

        // Return the keys for the requested abilities
        return $tier_three_abilities;

    }


    /* -- ABILITY-SPECIFIC FUNCTIONS THAT DON'T FIT ANYWHERE ELSE RIGHT NOW -- */

    // Define a function for getting the CSS filter styles for a Gemini Clone sprite
    public static function get_css_filter_styles_for_gemini_clone(){
        $filters = 'grayscale(100%) sepia(1) hue-rotate(145deg)';
        $styles = '-moz-filter: '.$filters.'; -webkit-filter: '.$filters.'; filter: '.$filters.'; ';
        return $styles;
    }

    // Define a function for checking if a given ability is complatible with the Gemini Clone mechanic
    public static function is_compatible_with_gemini_clone($ability_token){
        return
            // user cannot double-up on system actions
            substr($ability_token, 0, 7) != 'action-'
            // user has no energy left after an overdrive so prevent
            && substr($ability_token, -10, 10) != '-overdrive'
            // ensure specific incompatible abilities are also blocked
            && !in_array($ability_token, array(
                // causes user or the target to switch which could be messy
                'mecha-support', 'mecha-assault', 'mecha-party', 'flash-pulse', 'super-throw',
                // self-attached charge/shield/booster with no repeat-use benefit
                'proto-shield', 'rhythm-satellite', 'acid-barrier', 'core-shield',
                // target attachment/breaker with no repeat-use benefit for user
                'bass-crush', 'disco-fever', 'galaxy-bomb', 'thunder-wool',
                // already uses itself multiple times
                'water-balloon',
                // swap moves would be pretty lame if used twice
                'energy-swap', 'attack-swap', 'defense-swap', 'speed-swap',
                // mode abilities already max things so repeat-use not needed
                'energy-mode', 'attack-mode', 'defense-mode', 'speed-mode',
                // just doesn't make sense to use twice for one reason or another
                'buster-charge', 'buster-relay', 'copy-shot', 'copy-soul', 'copy-style',
                'friend-share', 'core-shield',
                'jewel-polish',
                // [event] abilities should not double-up
                'star-support',
                // [deprecated] abilities should not double-up
                'repair-mode', 'energy-shuffle', 'attack-shuffle', 'defense-shuffle', 'speed-shuffle',
                // [action/system] abilities should never be doubled-up
                'action-chargeweapons', 'action-noweapons', 'action-unequipitem',
                // prevent from using itself over again
                'gemini-clone',
                // dark moves just don't make sense using twice in a row
                'dark-boost', 'dark-break', 'dark-drain',
                ))
            ? true
            : false;
    }

    // Define a function for checking if a given ability is complatible with the Gemini Clone mechanic
    public static function allow_auto_trigger_with_gemini_clone($ability_token){
        return
            self::is_compatible_with_gemini_clone($ability_token)
            // ensure specific incompatible abilities are also blocked
            && !in_array($ability_token, array(
                // uses end-of-turn functionality but can be compensated for manually
                'mega-ball', 'metal-press',
                'solar-blaze', 'air-twister',
                'crystal-mind',
                ))
            ? true
            : false;
    }

    // Define a function for getting the static list of negative robot attachments with source and other info
    public static function get_negative_robot_attachment_index(){
        static $robot_attachment_index = false;
        if ($robot_attachment_index === false){
            $robot_attachment_index = array(
                // negative
                array('token' => 'bass_crush', 'source' => 'bass-crush', 'object' => 'bass-crush', 'noun' => 'Bass Crush', 'where' => 'behind'),
                array('token' => 'crash_bomb', 'source' => 'crash-bomber', 'object' => 'crash-bomb', 'noun' => 'Crash Bomb', 'where' => 'attached to'),
                );
            }
        return $robot_attachment_index;
    }

    // Define a function for getting the static list of positive field hazards with source and other info
    public static function get_positive_field_hazard_index(){
        static $field_hazard_index = false;
        if ($field_hazard_index === false){
            $field_hazard_index = array(
                // positive
                array('token' => 'super_blocks', 'source' => 'super-arm', 'object' => 'super-block', 'noun' => 'super block', 'where' => 'in front of', 'frame' => 0, 'offset' => array('x' => 55, 'y' => 2, 'z' => -20)),
                array('token' => 'ice_walls', 'source' => 'ice-wall', 'object' => 'ice-wall', 'noun' => 'ice wall', 'where' => 'in front of', 'frame' => 0, 'offset' => array('x' => 55, 'y' => 2, 'z' => -20)),
                );
            }
        return $field_hazard_index;
    }

    // Define a function for getting the static list of negative field hazards with source and other info
    public static function get_negative_field_hazard_index(){
        static $field_hazard_index = false;
        if ($field_hazard_index === false){
            $field_hazard_index = array(
                // negative
                array('token' => 'crude_oil', 'source' => 'oil-shooter', 'object' => 'crude-oil', 'noun' => 'crude oil', 'where' => 'below', 'frame' => 1, 'offset' => array('x' => 0, 'y' => -10, 'z' => -8)),
                array('token' => 'foamy_bubbles', 'source' => 'bubble-spray', 'object' => 'foamy-bubbles', 'noun' => 'foamy bubbles', 'where' => 'below', 'frame' => 2, 'offset' => array('x' => 0, 'y' => -5, 'z' => 6)),
                array('token' => 'frozen_footholds', 'source' => 'ice-breath', 'object' => 'frozen-foothold', 'noun' => 'frozen foothold', 'where' => 'below', 'frame' => 2, 'offset' => array('x' => 0, 'y' => -5, 'z' => 8)),
                array('token' => 'black_holes', 'source' => 'galaxy-bomb', 'object' => 'black-hole', 'noun' => 'black hole', 'where' => 'behind', 'frame' => 1, 'offset' => array('x' => -5, 'y' => 5, 'z' => -10)),
                array('token' => 'disco_balls', 'source' => 'disco-fever', 'object' => 'disco-ball', 'noun' => 'disco ball', 'where' => 'above', 'frame' => 0, 'offset' => array('x' => 70, 'y' => 10, 'z' => 20)),
                array('token' => 'woolly_cloud', 'source' => 'thunder-wool', 'object' => 'woolly-cloud', 'noun' => 'woolly cloud', 'where' => 'above', 'frame' => 0, 'offset' => array('x' => -5, 'y' => 40, 'z' => -8)),
                array('token' => 'acid_globs', 'source' => 'acid-glob', 'object' => 'acid-glob', 'noun' => 'acid glob', 'where' => 'below', 'frame' => 2, 'offset' => array('x' => 5, 'y' => 0, 'z' => 10)),
                array('token' => 'gravity_well', 'source' => 'gravity-hold', 'object' => 'gravity-well', 'noun' => 'gravity well', 'where' => 'below', 'frame' => 2, 'offset' => array('x' => 0, 'y' => 0, 'z' => -10)),
                array('token' => 'remote_mine', 'source' => 'remote-mine', 'object' => 'remote-mine', 'noun' => 'remote mine', 'where' => 'in front of', 'frame' => 6, 'offset' => array('x' => 30, 'y' => -5, 'z' => 6)),
                array('token' => 'frozen_spikes', 'source' => 'chill-spike', 'object' => 'frozen-spikes', 'noun' => 'frozen spikes', 'where' => 'below', 'frame' => 2, 'offset' => array('x' => 0, 'y' => -5, 'z' => 8)),
                );
            }
        return $field_hazard_index;
    }

    // Define a static function for getting a preset core shield for the challenge
    public static function get_static_attachment_token($ability_token, $attachment_token, $attachment_key){

        // If an ability object or array was provided instead
        if (is_object($ability_token) && isset($ability_token->ability_token)){ $ability_token = $ability_token->ability_token; }
        elseif (is_array($ability_token) && isset($ability_token['ability_token'])){ $ability_token = $ability_token['ability_token']; }
        elseif (empty($ability_token) || !is_string($ability_token)){ $ability_token = 'ability'; }

        // If an attachment object or array was provided instead
        if (is_object($attachment_token) && isset($attachment_token->attachment_token)){ $attachment_token = $attachment_token->attachment_token; }
        elseif (is_array($attachment_token) && isset($attachment_token['attachment_token'])){ $attachment_token = $attachment_token['attachment_token']; }
        elseif (empty($attachment_token) || !is_string($attachment_token)){ $ability_token = 'attachment'; }

        // If the attachment key was not provided, just set it to zero
        if (empty($attachment_key)){ $attachment_key = 0; }

        // Generate and return the attachment token
        $static_attachment_token = 'ability_'.$ability_token.'_'.$attachment_token.'_'.$attachment_key;
        return $static_attachment_token;

    }

    // Define a static function for getting an ability's custom attachment to be attached to the field or a robot in battle
    public static function get_static_attachment($ability, $attachment_token){

        // Collect a quick ref to the current battle
        $this_battle = rpg_battle::get_battle();

        // Collect this ability's token and object, however it was provided
        if (!empty($ability)){
            if (is_string($ability)){
                $ability_token = $ability;
                $ability_info = rpg_ability::get_index_info($ability_token);
                $this_ability = (object)($ability_info);
            } elseif (is_array($ability)){
                $ability_info = $ability;
                $ability_token = $ability_info['ability_token'];
                $this_ability = (object)($ability_info);
            } elseif (is_object($ability)){
                $ability_token = $ability->ability_token;
                $ability_info = $ability->export_array();
                $this_ability = $ability;
            }
        }

        // Compensate for missing or invalid ability details
        if (empty($ability_token) || empty($this_ability)){
            $ability_token = 'ability';
            $ability_info = array(
                'ability_token' => $ability_token,
                'ability_image' => $ability_token,
                'ability_damage' => 0,
                'ability_recovery' => 0
                );
            $this_ability = (object)($ability_info);
        }

        // Define an empty attachment object to start with at least the token
        $this_attachment = (object)(array('attachment_token' => $attachment_token));

        // Require the functions file if it exists
        $temp_functions_dir = preg_replace('/^action-/', '_actions/', $ability_token);
        $temp_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$temp_functions_dir.'/functions.php';
        if (file_exists($temp_functions_path)){ require($temp_functions_path); }
        else { $functions = array(); }

        // Collect refs to all the known objects for this ability
        $objects = array(
            'this_battle' => $this_battle,
            'this_field' => $this_battle->battle_field,
            'this_ability' => $this_ability,
            'this_attachment' => $this_attachment
            );

        // Generate very basic attachment info without knowing much else
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'attachment_token' => 'ability_'.$this_ability->ability_token.'_'.$this_attachment->attachment_token
            );

        // If the required attachment function exists (it better!) we can use it to generate actual attachment info
        if (isset($functions['static_attachment_function_'.$attachment_token])){
            $static_attachment_function = $functions['static_attachment_function_'.$attachment_token];
            $static_attachment_function_args = func_get_args();
            $static_attachment_function_args = array_slice($static_attachment_function_args, 2);
            array_unshift($static_attachment_function_args, $objects);
            $new_attachment_info = call_user_func_array($static_attachment_function, $static_attachment_function_args);
            if (!empty($new_attachment_info)){ $this_attachment_info = array_merge($this_attachment_info, $new_attachment_info); }
        } else {
            //error_log('Unable to get `static_attachment_function_'.$attachment_token.'` from ability `'.$ability_token.'`');
            //error_log('Available functions in  '.$temp_functions_path.' : '.implode(', ', array_keys($functions)));
            //error_log('$functions = '.print_r($functions, true));
        }

        // Return generated attachment info, whatever it is
        return $this_attachment_info;

    }

    // Define a static function for getting an ability's custom index to be used in some context during battle calc
    public static function get_static_index($ability, $index_token, $index_subtoken = ''){

        // Collect a quick ref to the current battle
        $this_battle = rpg_battle::get_battle();

        // Collect this ability's token and object, however it was provided
        if (!empty($ability)){
            if (is_string($ability)){
                $ability_token = $ability;
                $ability_info = rpg_ability::get_index_info($ability_token);
                $this_ability = (object)($ability_info);
            } elseif (is_array($ability)){
                $ability_info = $ability;
                $ability_token = $ability_info['ability_token'];
                $this_ability = (object)($ability_info);
            } elseif (is_object($ability)){
                $ability_token = $ability->ability_token;
                $ability_info = method_exists($ability, 'export_array') ? $ability->export_array() : rpg_ability::get_index_info($ability_token);
                $this_ability = $ability;
            }
        }

        // Compensate for missing or invalid ability details
        if (empty($ability_token) || empty($this_ability)){
            $ability_token = 'ability';
            $ability_info = array(
                'ability_token' => $ability_token,
                'ability_image' => $ability_token,
                'ability_damage' => 0,
                'ability_recovery' => 0
                );
            $this_ability = (object)($ability_info);
        }

        // For now, we're just going to combine the index token and subtoken into one
        $backup_index_token = $index_token;
        $backup_index_subtoken = $index_subtoken;
        $index_token = $index_token.(!empty($index_subtoken) ? '_'.$index_subtoken : '');

        // Define an empty index object to start with at least the token
        $this_index = (object)(array('index_token' => $index_token));

        // Require the functions file if it exists
        $temp_functions_dir = preg_replace('/^action-/', '_actions/', $ability_token);
        $temp_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$temp_functions_dir.'/functions.php';
        if (file_exists($temp_functions_path)){ require($temp_functions_path); }
        else { $functions = array(); }

        // Collect refs to all the known objects for this ability
        $objects = array(
            'this_battle' => $this_battle,
            'this_field' => $this_battle->battle_field,
            'this_ability' => $this_ability,
            'this_index' => $this_index
            );

        // Generate very basic index info without knowing much else
        $this_index_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'index_token' => 'ability_'.$this_ability->ability_token.'_'.$this_index->index_token
            );

        // If the required index function exists (it better!) we can use it to generate actual index info
        if (isset($functions['static_index_function_'.$index_token])){
            $static_index_function = $functions['static_index_function_'.$index_token];
            $static_index_function_args = func_get_args();
            $static_index_function_args = array_slice($static_index_function_args, 1);
            array_unshift($static_index_function_args, $objects);
            $new_index_info = call_user_func_array($static_index_function, $static_index_function_args);
            if (!empty($new_index_info)){ $this_index_info = array_merge($this_index_info, $new_index_info); }
        } else {
            //error_log('Unable to get `static_index_function_'.$index_token.'` from ability `'.$ability_token.'`');
            //error_log('Available functions in  '.$temp_functions_path.' : '.implode(', ', array_keys($functions)));
            //error_log('$functions = '.print_r($functions, true));
        }

        // Return generated index info, whatever it is
        return $this_index_info;

    }

    // Define a static function for generating/returning the Super Arm sprite index w/ sheet & frame refs for each field
    public static function get_super_block_sprite_index(){
        return self::get_static_index('super-arm', 'super-block', 'sprite-index');
    }

    // Define a static function for getting a preset core shield for the challenge
    public static function get_static_core_shield($shield_type, $shield_duration = 99, $existing_shields = 0, $effect_percent = 99){
        $this_ability_token = 'core-shield';
        $this_attachment_token = 'ability_'.$this_ability_token.'_'.$shield_type;
        $this_attachment_image = $this_ability_token.'_'.$shield_type;
        $shield_animation_sequence = array(2, 3, 4, 3);
        //for ($i = 0; $i < $existing_shields; $i++){ array_push($shield_animation_sequence, array_shift($shield_animation_sequence)); }
        $shield_effect_multiplier = 1 - (($effect_percent + 0.9999999999) / 100);
        $this_attachment_create_text = '{this_robot}\'s new <span class="ability_name ability_type ability_type_'.$shield_type.'">Core Shield</span> resists damage!<br /> {this_robot} is virtually immune to the <span class="ability_name ability_type ability_type_'.$shield_type.'">'.ucfirst($shield_type).'</span> type now! ';
        $this_attachment_destroy_text = '{this_robot}\'s <span class="ability_name ability_type ability_type_'.$shield_type.'">'.ucfirst($shield_type).'</span> type <span class="ability_name ability_type ability_type_'.$shield_type.'">Core Shield</span> faded away... ';
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability_token,
            'ability_type' => $shield_type,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $shield_duration + 1, // we do +1 so the summon turn doesn't count
            'attachment_damage_input_breaker_'.$shield_type => $shield_effect_multiplier,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_create_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_create_text)
                ),
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
            'ability_frame_animate' => $shield_animation_sequence,
            'ability_frame_offset' => array(
                'x' => (10 + ($existing_shields * 10)),
                'y' => (0),
                'z' => -1 * (10 + $existing_shields)
                )
            );
        return $this_attachment_info;
    }

    // Define a static function for generating a static field attachment of "crude oil" (from the Oil Shooter ability)
    public static function get_static_crude_oil($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('oil-shooter', 'crude-oil', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of "foamy bubbles" (from the Bubble Spray ability)
    public static function get_static_foamy_bubbles($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('bubble-spray', 'foamy-bubbles', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of a "black hole" (from the Galaxy Bomb ability)
    public static function get_static_black_hole($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('galaxy-bomb', 'black-hole', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of a "frozen foothold" (from the Ice Breath ability)
    public static function get_static_frozen_foothold($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('ice-breath', 'frozen-foothold', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of a "super block" (from the Super Arm ability)
    public static function get_static_super_block($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('super-arm', 'super-block', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of a "disco ball" (from the Disco Fever ability)
    public static function get_static_disco_ball($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('disco-fever', 'disco-ball', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of a "woolly cloud" (from the Thunder Wool ability)
    public static function get_static_woolly_cloud($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('thunder-wool', 'woolly-cloud', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of an "acid glob" (from the Acid Glob ability)
    public static function get_static_acid_glob($static_attachment_key, $this_attachment_duration = 99){
        return self::get_static_attachment('acid-glob', 'acid-glob', $static_attachment_key, $this_attachment_duration);
    }

    // Define a static function for generating a static field attachment of "remote mine" (from the Remote Mine ability)
    public static function get_static_remote_mine($static_attachment_key, $this_attachment_duration = 99, $this_attachment_created = 0){
        return self::get_static_attachment('remote-mine', 'remote-mine', $static_attachment_key, $this_attachment_duration, $this_attachment_created);
    }

}
?>
