<?php
/**
 * Mega Man RPG Item
 * <p>The object class for all items in the Mega Man RPG Prototype.</p>
 */
class rpg_item extends rpg_object {

    // Define public robot variables
    public $battle = null;
    public $battle_id = 0;
    public $battle_token = '';
    public $field = null;
    public $field_id = 0;
    public $field_token = '';
    public $item_id = 0;
    public $item_key = 0;
    public $item_name = '';
    public $item_token = '';
    public $item_description = '';
    public $item_class = '';
    public $item_subclass = '';
    public $item_master = '';
    public $item_number = '';
    public $item_type = '';
    public $item_type2 = '';
    public $item_speed = 0;
    public $item_energy = 0;
    public $item_energy_percent = false;
    public $item_damage  = 0;
    public $item_damage2 = 0;
    public $item_damage_percent = false;
    public $item_damage2_percent = false;
    public $item_recovery = 0;
    public $item_recovery2 = 0;
    public $item_recovery_percent = false;
    public $item_recovery2_percent = false;
    public $item_accuracy = 0;
    public $item_target = '';
    public $item_functions = '';
    public $item_image = '';
    public $item_image_size = 0;
    public $item_frame = '';
    public $item_frame_span = 0;
    public $item_frame_animate = array();
    public $item_frame_index = array();
    public $item_frame_offset = array();
    public $item_frame_styles = '';
    public $item_frame_classes = '';
    public $item_results = array();
    public $item_options = array();
    public $target_options = array();
    public $damage_options = array();
    public $recovery_options = array();
    public $attachment_options = array();
    public $item_function = null;
    public $item_function_onload = null;
    public $item_function_attachment = null;
    public $item_base_key = 0;
    public $item_base_name = '';
    public $item_base_token = '';
    public $item_base_description = '';
    public $item_base_image = '';
    public $item_base_image_size = 0;
    public $item_base_type = '';
    public $item_base_type2 = '';
    public $item_base_energy = 0;
    public $item_base_speed = 0;
    public $item_base_damage = 0;
    public $item_base_damage_percent = false;
    public $item_base_damage2 = 0;
    public $item_base_damage2_percent = false;
    public $item_base_recovery = 0;
    public $item_base_recovery_percent = false;
    public $item_base_recovery2 = 0;
    public $item_base_recovery2_percent = false;
    public $item_base_accuracy = 0;
    public $item_base_target = '';

    /**
     * Create a new RPG item object
     * @param rpg_player $this_player
     * @param rpg_robot $this_robot
     * @param array $item_info (optional)
     * @return rpg_item
     */
    public function rpg_item(rpg_player $this_player, rpg_robot $this_robot, $item_info = array()){

        // Update the session keys for this object
        $this->session_key = 'ITEMS';
        $this->session_token = 'item_token';
        $this->session_id = 'item_id';
        $this->class = 'item';

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

        // Collect current item data from the function if available
        $item_info = !empty($item_info) ? $item_info : array('item_id' => 0, 'item_token' => 'item');
        // Load the item data based on the ID and fallback token
        $item_info = $this->item_load($item_info['item_id'], $item_info['item_token'], $item_info);

        // Now load the item data from the session or index
        if (empty($item_info)){
            // Item data could not be loaded
            die('Item data could not be loaded :<br />$item_info = <pre>'.print_r($item_info, true).'</pre>');
            return false;
        }

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    /**
     * Generate an item ID based on the robot owner and the item slot
     * @param int $robot_id
     * @param int $item_slot (optional)
     * @return int
     */
    public static function generate_id($robot_id, $item_slot = 0){
        $item_id = $robot_id.str_pad(($item_slot + 1), 3, '0', STR_PAD_LEFT);
        return $item_id;
    }

    // Define a public function for manually loading data
    public function item_load($item_id = 0, $item_token = 'item', $custom_info = array()){

        /*
        // If this is a special system item, hard-code its ID, otherwise base off robot
        $temp_system_items = array('attachment-defeat');
        if (in_array($item_token, $temp_system_items)){
            $item_id = $this->player_id.player_id.str_pad(array_search($item_token, $temp_system_items), 3, '0', STR_PAD_LEFT);
        }
        // Else if this is an item, tweak it's ID as well
        elseif (in_array($item_token, $this->player->player_items)){
            $item_id = $this->player_id.str_pad(array_search($item_token, $this->player->player_items), 3, '0', STR_PAD_LEFT);
        }
        // Else if this was any other item, combine ID with robot owner
        else {
            $item_id = $this->robot_id.str_pad($item_id, 3, '0', STR_PAD_LEFT);
        }
        */

        // If the item token was not provided, return false
        if (!isset($item_token)){
            die("item token must be set!\n\$this_iteminfo\n".print_r($this_iteminfo, true));
            return false;
        }

        // Collect current item data from the session if available
        if (isset($_SESSION['ITEMS'][$item_id])){
            $this_iteminfo = $_SESSION['ITEMS'][$item_id];
            if ($this_iteminfo['item_token'] != $item_token){
                die("item token and ID mismatch {$item_id}:{$item_token}!\n");
                return false;
            }
        }
        // Otherwise, collect item data from the index
        else {
            $this_iteminfo = self::get_index_info($item_token);
            if (empty($this_iteminfo)){
                die("item data could not be loaded for {$item_id}:{$item_token}!\n");
                return false;
            }
        }

        // If the custom data was not empty, merge now
        if (!empty($custom_info)){ $this_iteminfo = array_merge($this_iteminfo, $custom_info); }

        // Define the internal item values using the provided array
        $this->flags = isset($this_iteminfo['flags']) ? $this_iteminfo['flags'] : array();
        $this->counters = isset($this_iteminfo['counters']) ? $this_iteminfo['counters'] : array();
        $this->values = isset($this_iteminfo['values']) ? $this_iteminfo['values'] : array();
        $this->history = isset($this_iteminfo['history']) ? $this_iteminfo['history'] : array();
        $this->item_id = isset($this_iteminfo['item_id']) ? $this_iteminfo['item_id'] : $item_id;
        $this->item_key = isset($this_iteminfo['item_key']) ? $this_iteminfo['item_key'] : 0;
        $this->item_name = isset($this_iteminfo['item_name']) ? $this_iteminfo['item_name'] : 'Item';
        $this->item_token = isset($this_iteminfo['item_token']) ? $this_iteminfo['item_token'] : 'item';
        $this->item_description = isset($this_iteminfo['item_description']) ? $this_iteminfo['item_description'] : '';
        $this->item_class = isset($this_iteminfo['item_class']) ? $this_iteminfo['item_class'] : 'master';
        $this->item_subclass = isset($this_iteminfo['item_subclass']) ? $this_iteminfo['item_subclass'] : '';
        $this->item_master = isset($this_iteminfo['item_master']) ? $this_iteminfo['item_master'] : '';
        $this->item_number = isset($this_iteminfo['item_number']) ? $this_iteminfo['item_number'] : '';
        $this->item_type = isset($this_iteminfo['item_type']) ? $this_iteminfo['item_type'] : '';
        $this->item_type2 = isset($this_iteminfo['item_type2']) ? $this_iteminfo['item_type2'] : '';
        $this->item_speed = isset($this_iteminfo['item_speed']) ? $this_iteminfo['item_speed'] : 1;
        $this->item_energy = isset($this_iteminfo['item_energy']) ? $this_iteminfo['item_energy'] : 4;
        $this->item_energy_percent = isset($this_iteminfo['item_energy_percent']) ? $this_iteminfo['item_energy_percent'] : true;
        $this->item_damage = isset($this_iteminfo['item_damage']) ? $this_iteminfo['item_damage'] : 0;
        $this->item_damage2 = isset($this_iteminfo['item_damage2']) ? $this_iteminfo['item_damage2'] : 0;
        $this->item_damage_percent = isset($this_iteminfo['item_damage_percent']) ? $this_iteminfo['item_damage_percent'] : false;
        $this->item_damage2_percent = isset($this_iteminfo['item_damage2_percent']) ? $this_iteminfo['item_damage2_percent'] : false;
        $this->item_recovery = isset($this_iteminfo['item_recovery']) ? $this_iteminfo['item_recovery'] : 0;
        $this->item_recovery2 = isset($this_iteminfo['item_recovery2']) ? $this_iteminfo['item_recovery2'] : 0;
        $this->item_recovery_percent = isset($this_iteminfo['item_recovery_percent']) ? $this_iteminfo['item_recovery_percent'] : false;
        $this->item_recovery2_percent = isset($this_iteminfo['item_recovery2_percent']) ? $this_iteminfo['item_recovery2_percent'] : false;
        $this->item_accuracy = isset($this_iteminfo['item_accuracy']) ? $this_iteminfo['item_accuracy'] : 0;
        $this->item_target = isset($this_iteminfo['item_target']) ? $this_iteminfo['item_target'] : 'auto';
        $this->item_functions = isset($this_iteminfo['item_functions']) ? $this_iteminfo['item_functions'] : 'items/item.php';
        $this->item_image = isset($this_iteminfo['item_image']) ? $this_iteminfo['item_image'] : $this->item_token;
        $this->item_image_size = isset($this_iteminfo['item_image_size']) ? $this_iteminfo['item_image_size'] : 40;
        $this->item_frame = isset($this_iteminfo['item_frame']) ? $this_iteminfo['item_frame'] : 'base';
        $this->item_frame_span = isset($this_iteminfo['item_frame_span']) ? $this_iteminfo['item_frame_span'] : 1;
        $this->item_frame_animate = isset($this_iteminfo['item_frame_animate']) ? $this_iteminfo['item_frame_animate'] : array($this->item_frame);
        $this->item_frame_index = isset($this_iteminfo['item_frame_index']) ? $this_iteminfo['item_frame_index'] : array('base');
        $this->item_frame_offset = isset($this_iteminfo['item_frame_offset']) ? $this_iteminfo['item_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 1);
        $this->item_frame_styles = isset($this_iteminfo['item_frame_styles']) ? $this_iteminfo['item_frame_styles'] : '';
        $this->item_frame_classes = isset($this_iteminfo['item_frame_classes']) ? $this_iteminfo['item_frame_classes'] : '';
        $this->item_results = array();
        $this->item_options = array();
        $this->target_options = array();
        $this->damage_options = array();
        $this->recovery_options = array();
        $this->attachment_options = array();

        // Collect any functions associated with this item
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->item_functions) ? $this->item_functions : 'items/item.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->item_function = isset($item['item_function']) ? $item['item_function'] : function(){};
        $this->item_function_onload = isset($item['item_function_onload']) ? $item['item_function_onload'] : function(){};
        $this->item_function_attachment = isset($item['item_function_attachment']) ? $item['item_function_attachment'] : function(){};
        unset($item);

        // Define the internal robot base values using the robots index array
        $this->item_base_key = isset($this_iteminfo['item_base_key']) ? $this_iteminfo['item_base_key'] : $this->item_key;
        $this->item_base_name = isset($this_iteminfo['item_base_name']) ? $this_iteminfo['item_base_name'] : $this->item_name;
        $this->item_base_token = isset($this_iteminfo['item_base_token']) ? $this_iteminfo['item_base_token'] : $this->item_token;
        $this->item_base_description = isset($this_iteminfo['item_base_description']) ? $this_iteminfo['item_base_description'] : $this->item_description;
        $this->item_base_image = isset($this_iteminfo['item_base_image']) ? $this_iteminfo['item_base_image'] : $this->item_image;
        $this->item_base_image_size = isset($this_iteminfo['item_base_image_size']) ? $this_iteminfo['item_base_image_size'] : $this->item_image_size;
        $this->item_base_type = isset($this_iteminfo['item_base_type']) ? $this_iteminfo['item_base_type'] : $this->item_type;
        $this->item_base_type2 = isset($this_iteminfo['item_base_type2']) ? $this_iteminfo['item_base_type2'] : $this->item_type2;
        $this->item_base_energy = isset($this_iteminfo['item_base_energy']) ? $this_iteminfo['item_base_energy'] : $this->item_energy;
        $this->item_base_speed = isset($this_iteminfo['item_base_speed']) ? $this_iteminfo['item_base_speed'] : $this->item_speed;
        $this->item_base_damage = isset($this_iteminfo['item_base_damage']) ? $this_iteminfo['item_base_damage'] : $this->item_damage;
        $this->item_base_damage_percent = isset($this_iteminfo['item_base_damage_percent']) ? $this_iteminfo['item_base_damage_percent'] : $this->item_damage_percent;
        $this->item_base_damage2 = isset($this_iteminfo['item_base_damage2']) ? $this_iteminfo['item_base_damage2'] : $this->item_damage2;
        $this->item_base_damage2_percent = isset($this_iteminfo['item_base_damage2_percent']) ? $this_iteminfo['item_base_damage2_percent'] : $this->item_damage2_percent;
        $this->item_base_recovery = isset($this_iteminfo['item_base_recovery']) ? $this_iteminfo['item_base_recovery'] : $this->item_recovery;
        $this->item_base_recovery_percent = isset($this_iteminfo['item_base_recovery_percent']) ? $this_iteminfo['item_base_recovery_percent'] : $this->item_recovery_percent;
        $this->item_base_recovery2 = isset($this_iteminfo['item_base_recovery2']) ? $this_iteminfo['item_base_recovery2'] : $this->item_recovery2;
        $this->item_base_recovery2_percent = isset($this_iteminfo['item_base_recovery2_percent']) ? $this_iteminfo['item_base_recovery2_percent'] : $this->item_recovery2_percent;
        $this->item_base_accuracy = isset($this_iteminfo['item_base_accuracy']) ? $this_iteminfo['item_base_accuracy'] : $this->item_accuracy;
        $this->item_base_target = isset($this_iteminfo['item_base_target']) ? $this_iteminfo['item_base_target'] : $this->item_target;

        // Define a the default item results
        $this->item_results_reset();

        // Reset the item options to default
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
        $temp_function = $this->item_function_onload;
        $temp_result = $temp_function(array(
            'this_battle' => $this_battle,
            'this_field' => $this_field,
            'this_player' => $this_player,
            'this_robot' => $this_robot,
            'target_player' => $target_player,
            'target_robot' => $target_robot,
            'this_item' => $this
            ));

        // Return true on success
        return true;

    }

    // Define alias functions for updating specific fields quickly

    public function get_id(){ return intval($this->get_info('item_id')); }
    public function set_id($value){ $this->set_info('item_id', intval($value)); }

    public function get_name(){ return $this->get_info('item_name'); }
    public function set_name($value){ $this->set_info('item_name', $value); }
    public function get_base_name(){ return $this->get_info('item_base_name'); }
    public function set_base_name($value){ $this->set_info('item_base_name', $value); }
    public function reset_name(){ $this->set_info('item_name', $this->get_info('item_base_name')); }

    public function get_token(){ return $this->get_info('item_token'); }
    public function set_token($value){ $this->set_info('item_token', $value); }

    public function get_description(){ return $this->get_info('item_description'); }
    public function set_description($value){ $this->set_info('item_description', $value); }
    public function get_base_description(){ return $this->get_info('item_base_description'); }
    public function set_base_description($value){ $this->set_info('item_base_description', $value); }

    public function get_class(){ return $this->get_info('item_class'); }
    public function set_class($value){ $this->set_info('item_class', $value); }

    public function get_subclass(){ return $this->get_info('item_subclass'); }
    public function set_subclass($value){ $this->set_info('item_subclass', $value); }

    public function get_master(){ return $this->get_info('item_master'); }
    public function set_master($value){ $this->set_info('item_master', $value); }
    public function get_base_master(){ return $this->get_info('item_base_master'); }
    public function set_base_master($value){ $this->set_info('item_base_master', $value); }

    public function get_number(){ return $this->get_info('item_number'); }
    public function set_number($value){ $this->set_info('item_number', $value); }
    public function get_base_number(){ return $this->get_info('item_base_number'); }
    public function set_base_number($value){ $this->set_info('item_base_number', $value); }

    public function get_type(){ return $this->get_info('item_type'); }
    public function set_type($value){ $this->set_info('item_type', $value); }
    public function get_base_type(){ return $this->get_info('item_base_type'); }
    public function set_base_type($value){ $this->set_info('item_base_type', $value); }

    public function get_type2(){ return $this->get_info('item_type2'); }
    public function set_type2($value){ $this->set_info('item_type2', $value); }
    public function get_base_type2(){ return $this->get_info('item_base_type2'); }
    public function set_base_type2($value){ $this->set_info('item_base_type2', $value); }

    public function get_speed(){ return $this->get_info('item_speed'); }
    public function set_speed($value){ $this->set_info('item_speed', $value); }
    public function get_base_speed(){ return $this->get_info('item_base_speed'); }
    public function set_base_speed($value){ $this->set_info('item_base_speed', $value); }
    public function reset_speed(){ $this->set_info('item_speed', $this->get_base_speed()); }

    public function get_energy(){ return $this->get_info('item_energy'); }
    public function set_energy($value){ $this->set_info('item_energy', $value); }
    public function get_base_energy(){ return $this->get_info('item_base_energy'); }
    public function set_base_energy($value){ $this->set_info('item_base_energy', $value); }
    public function reset_energy(){ $this->set_info('item_energy', $this->get_base_energy()); }

    public function get_damage(){ return $this->get_info('item_damage'); }
    public function set_damage($value){ $this->set_info('item_damage', $value); }
    public function get_base_damage(){ return $this->get_info('item_base_damage'); }
    public function set_base_damage($value){ $this->set_info('item_base_damage', $value); }
    public function reset_damage(){ $this->set_info('item_damage', $this->get_base_damage()); }

    public function get_damage_percent(){ return $this->get_info('item_damage_percent'); }
    public function set_damage_percent($value){ $this->set_info('item_damage_percent', $value); }
    public function get_base_damage_percent(){ return $this->get_info('item_base_damage_percent'); }
    public function set_base_damage_percent($value){ $this->set_info('item_base_damage_percent', $value); }

    public function get_damage2(){ return $this->get_info('item_damage2'); }
    public function set_damage2($value){ $this->set_info('item_damage2', $value); }
    public function get_base_damage2(){ return $this->get_info('item_base_damage2'); }
    public function set_base_damage2($value){ $this->set_info('item_base_damage2', $value); }
    public function reset_damage2(){ $this->set_info('item_damage2', $this->get_base_damage2()); }

    public function get_damage2_percent(){ return $this->get_info('item_damage2_percent'); }
    public function set_damage2_percent($value){ $this->set_info('item_damage2_percent', $value); }
    public function get_base_damage2_percent(){ return $this->get_info('item_base_damage2_percent'); }
    public function set_base_damage2_percent($value){ $this->set_info('item_base_damage2_percent', $value); }

    public function get_recovery(){ return $this->get_info('item_recovery'); }
    public function set_recovery($value){ $this->set_info('item_recovery', $value); }
    public function get_base_recovery(){ return $this->get_info('item_base_recovery'); }
    public function set_base_recovery($value){ $this->set_info('item_base_recovery', $value); }
    public function reset_recovery(){ $this->set_info('item_recovery', $this->get_base_recovery()); }

    public function get_recovery_percent(){ return $this->get_info('item_recovery_percent'); }
    public function set_recovery_percent($value){ $this->set_info('item_recovery_percent', $value); }
    public function get_base_recovery_percent(){ return $this->get_info('item_base_recovery_percent'); }
    public function set_base_recovery_percent($value){ $this->set_info('item_base_recovery_percent', $value); }

    public function get_recovery2(){ return $this->get_info('item_recovery2'); }
    public function set_recovery2($value){ $this->set_info('item_recovery2', $value); }
    public function get_base_recovery2(){ return $this->get_info('item_base_recovery2'); }
    public function set_base_recovery2($value){ $this->set_info('item_base_recovery2', $value); }
    public function reset_recovery2(){ $this->set_info('item_recovery2', $this->get_base_recovery2()); }

    public function get_recovery2_percent(){ return $this->get_info('item_recovery2_percent'); }
    public function set_recovery2_percent($value){ $this->set_info('item_recovery2_percent', $value); }
    public function get_base_recovery2_percent(){ return $this->get_info('item_base_recovery2_percent'); }
    public function set_base_recovery2_percent($value){ $this->set_info('item_base_recovery2_percent', $value); }

    public function get_accuracy(){ return $this->get_info('item_accuracy'); }
    public function set_accuracy($value){ $this->set_info('item_accuracy', $value); }
    public function get_base_accuracy(){ return $this->get_info('item_base_accuracy'); }
    public function set_base_accuracy($value){ $this->set_info('item_base_accuracy', $value); }
    public function reset_accuracy(){ $this->set_info('item_accuracy', $this->get_base_accuracy()); }

    public function get_target(){ return $this->get_info('item_target'); }
    public function set_target($value){ $this->set_info('item_target', $value); }
    public function get_base_target(){ return $this->get_info('item_base_target'); }
    public function set_base_target($value){ $this->set_info('item_base_target', $value); }
    public function reset_target(){ $this->set_info('item_target', $this->get_base_target()); }

    public function get_functions(){ return $this->get_info('item_functions'); }
    public function set_functions($value){ $this->set_info('item_functions', $value); }

    public function get_image(){ return $this->get_info('item_image'); }
    public function set_image($value){ $this->set_info('item_image', $value); }
    public function get_base_image(){ return $this->get_info('item_base_image'); }
    public function set_base_image($value){ $this->set_info('item_base_image', $value); }
    public function reset_image(){ $this->set_info('item_image', $this->get_base_image()); }

    public function get_image_size(){ return $this->get_info('item_image_size'); }
    public function set_image_size($value){ $this->set_info('item_image_size', $value); }
    public function get_base_image_size(){ return $this->get_info('item_base_image_size'); }
    public function set_base_image_size($value){ $this->set_info('item_base_image_size', $value); }
    public function reset_image_size(){ $this->set_info('item_image_size', $this->get_base_image_size()); }

    public function get_frame(){ return $this->get_info('item_frame'); }
    public function set_frame($value){ $this->set_info('item_frame', $value); }

    public function get_frame_span(){ return $this->get_info('item_frame_span'); }
    public function set_frame_span($value){ $this->set_info('item_frame_span', $value); }

    public function get_frame_animate(){ return $this->get_info('item_frame_animate'); }
    public function set_frame_animate($value){ $this->set_info('item_frame_animate', $value); }

    public function get_frame_index(){ return $this->get_info('item_frame_index'); }
    public function set_frame_index($value){ $this->set_info('item_frame_index', $value); }

    public function get_frame_offset(){
        $args = func_get_args();
        if (isset($args[0])){ return $this->get_info('item_frame_offset', $args[0]); }
        else { return $this->get_info('item_frame_offset'); }
    }
    public function set_frame_offset($value){
        $args = func_get_args();
        if (isset($args[1])){ $this->set_info('item_frame_offset', $args[0], $args[1]); }
        else { $this->set_info('item_frame_offset', $value); }
    }

    public function get_frame_styles(){ return $this->get_info('item_frame_styles'); }
    public function set_frame_styles($value){ $this->set_info('item_frame_styles', $value); }
    public function reset_frame_styles(){ $this->set_info('item_frame_styles', ''); }

    public function get_frame_classes(){ return $this->get_info('item_frame_classes'); }
    public function set_frame_classes($value){ $this->set_info('item_frame_classes', $value); }
    public function reset_frame_classes(){ $this->set_info('item_frame_classes', ''); }

    public function get_results(){ return $this->get_info('item_results'); }
    public function set_results($value){ $this->set_info('item_results', $value); }

    public function get_options(){ return $this->get_info('item_options'); }
    public function set_options($value){ $this->set_info('item_options', $value); }

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
        $type_class = !empty($this->item_type) ? $this->item_type : 'none';
        if ($type_class != 'none' && !empty($this->item_type2)){ $type_class .= '_'.$this->item_type2; }
        elseif ($type_class == 'none' && !empty($this->item_type2)){ $type_class = $this->item_type2; }
        return '<span class="item_name item_type item_type_'.$type_class.'">'.$this->item_name.($plural ? 's' : '').'</span>';
    }
    //public function print_name(){ return '<span class="item_name">'.$this->item_name.'</span>'; }
    public function print_token(){ return '<span class="item_token">'.$this->item_token.'</span>'; }
    public function print_description(){ return '<span class="item_description">'.$this->item_description.'</span>'; }
    public function print_type(){ return '<span class="item_type">'.$this->item_type.'</span>'; }
    public function print_type2(){ return '<span class="item_type2">'.$this->item_type2.'</span>'; }
    public function print_speed(){ return '<span class="item_speed">'.$this->item_speed.'</span>'; }
    public function print_damage(){ return '<span class="item_damage">'.$this->item_damage.'</span>'; }
    public function print_recovery(){ return '<span class="item_recovery">'.$this->item_recovery.'</span>'; }
    public function print_accuracy(){ return '<span class="item_accuracy">'.$this->item_accuracy.'%</span>'; }

    // Define a trigger for using one of this robot's items
    public function reset_item($target_robot, $this_item){

        // Update internal variables
        $this_item->update_session();

        // Return the item results
        return $this_item->item_results;
    }

    // Define a public function for easily resetting result options
    public function item_results_reset(){
        // Redfine the result options as an empty array
        $item_results = array();
        // Populate the array with defaults
        $item_results['total_result'] = '';
        $item_results['total_actions'] = 0;
        $item_results['total_strikes'] = 0;
        $item_results['total_misses'] = 0;
        $item_results['total_amount'] = 0;
        $item_results['total_overkill'] = 0;
        $item_results['this_result'] = '';
        $item_results['this_amount'] = 0;
        $item_results['this_overkill'] = 0;
        $item_results['this_text'] = '';
        $item_results['counter_criticals'] = 0;
        $item_results['counter_weaknesses'] = 0;
        $item_results['counter_resistances'] = 0;
        $item_results['counter_affinities'] = 0;
        $item_results['counter_immunities'] = 0;
        $item_results['counter_coreboosts'] = 0;
        $item_results['flag_critical'] = false;
        $item_results['flag_affinity'] = false;
        $item_results['flag_weakness'] = false;
        $item_results['flag_resistance'] = false;
        $item_results['flag_immunity'] = false;
        $item_results['flag_coreboost'] = false;
        // Update this item's data
        $this->set_results($item_results);
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
        $target_options['item_success_frame'] = 1;
        $target_options['item_success_frame_span'] = 1;
        $target_options['item_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $target_options['item_failure_frame'] = 1;
        $target_options['item_failure_frame_span'] = 1;
        $target_options['item_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $target_options['target_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $target_options['target_kickback2'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $target_options['target_header'] = $this->robot->robot_name.'&#39;s '.$this->item_name;
        $target_options['target_text'] = "{$this->robot->print_name()} uses {$this->print_name()}!";
        // Update this item's data
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
            $new_target_options['item_success_frame'] = $target_options['success'][0];
            $new_target_options['item_success_frame_offset']['x'] = $target_options['success'][1];
            $new_target_options['item_success_frame_offset']['y'] = $target_options['success'][2];
            $new_target_options['item_success_frame_offset']['z'] = $target_options['success'][3];
            $new_target_options['target_text'] = $target_options['success'][4];
            $new_target_options['item_success_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($target_options['failure'])){
            $new_target_options['item_failure_frame'] = $target_options['failure'][0];
            $new_target_options['item_failure_frame_offset']['x'] = $target_options['failure'][1];
            $new_target_options['item_failure_frame_offset']['y'] = $target_options['failure'][2];
            $new_target_options['item_failure_frame_offset']['z'] = $target_options['failure'][3];
            $new_target_options['target_text'] = $target_options['failure'][4];
            $new_target_options['item_failure_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
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
        $damage_options['damage_header'] = $this->robot->robot_name.'&#39;s '.$this->item_name;
        $damage_options['damage_frame'] = 'damage';
        $damage_options['item_success_frame'] = 1;
        $damage_options['item_success_frame_span'] = 1;
        $damage_options['item_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $damage_options['item_failure_frame'] = 1;
        $damage_options['item_failure_frame_span'] = 1;
        $damage_options['item_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $damage_options['damage_kind'] = 'energy';
        $damage_options['damage_type'] = $this->item_type;
        $damage_options['damage_type2'] = $this->item_type2;
        $damage_options['damage_amount'] = $this->item_damage;
        $damage_options['damage_amount2'] = $this->item_damage2;
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
        $damage_options['success_text'] = 'The item hit!';
        $damage_options['failure_text'] = 'The item missed&hellip;';
        $damage_options['immunity_text'] = 'The item had no effect&hellip;';
        $damage_options['critical_text'] = 'It&#39;s a critical hit!';
        $damage_options['weakness_text'] = 'It&#39;s super effective!';
        $damage_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $damage_options['weakness_resistance_text'] = ''; //"It's a super effective resisted hit!';
        $damage_options['weakness_critical_text'] = 'It&#39;s a super effective critical hit!';
        $damage_options['resistance_critical_text'] = 'It&#39;s a critical hit, but not very effective&hellip;';
        // Update this item's data
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
            $new_damage_options['item_success_frame'] = $damage_options['success'][0];
            $new_damage_options['item_success_frame_offset']['x'] = $damage_options['success'][1];
            $new_damage_options['item_success_frame_offset']['y'] = $damage_options['success'][2];
            $new_damage_options['item_success_frame_offset']['z'] = $damage_options['success'][3];
            $new_damage_options['success_text'] = $damage_options['success'][4];
            $new_damage_options['item_success_frame_span'] = isset($damage_options['success'][5]) ? $damage_options['success'][5] : 1;
        }
        // Update internal variables with failure options, if set
        if (isset($damage_options['failure'])){
            $new_damage_options['item_failure_frame'] = $damage_options['failure'][0];
            $new_damage_options['item_failure_frame_offset']['x'] = $damage_options['failure'][1];
            $new_damage_options['item_failure_frame_offset']['y'] = $damage_options['failure'][2];
            $new_damage_options['item_failure_frame_offset']['z'] = $damage_options['failure'][3];
            $new_damage_options['failure_text'] = $damage_options['failure'][4];
            $new_damage_options['item_failure_frame_span'] = isset($damage_options['failure'][5]) ? $damage_options['failure'][5] : 1;
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
        $recovery_options['recovery_header'] = $this->robot->robot_name.'&#39;s '.$this->item_name;
        $recovery_options['recovery_frame'] = 'defend';
        $recovery_options['item_success_frame'] = 1;
        $recovery_options['item_success_frame_span'] = 1;
        $recovery_options['item_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $recovery_options['item_failure_frame'] = 1;
        $recovery_options['item_failure_frame_span'] = 1;
        $recovery_options['item_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $recovery_options['recovery_kind'] = 'energy';
        $recovery_options['recovery_type'] = $this->item_type;
        $recovery_options['recovery_type2'] = $this->item_type2;
        $recovery_options['recovery_amount'] = $this->item_recovery;
        $recovery_options['recovery_amount2'] = $this->item_recovery2;
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
        $recovery_options['recovery_type'] = $this->item_type;
        $recovery_options['recovery_type2'] = $this->item_type2;
        $recovery_options['success_text'] = 'The item worked!';
        $recovery_options['failure_text'] = 'The item failed&hellip;';
        $recovery_options['immunity_text'] = 'The item had no effect&hellip;';
        $recovery_options['critical_text'] = 'It&#39;s a lucky boost!';
        $recovery_options['affinity_text'] = 'It&#39;s super effective!';
        $recovery_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $recovery_options['affinity_resistance_text'] = ''; //'It&#39;s a super effective resisted hit!';
        $recovery_options['affinity_critical_text'] = 'It&#39;s a super effective lucky boost!';
        $recovery_options['resistance_critical_text'] = 'It&#39;s a lucky boost, but not very effective&hellip;';
        // Update this item's data
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
            $new_recovery_options['item_success_frame'] = $recovery_options['success'][0];
            $new_recovery_options['item_success_frame_offset']['x'] = $recovery_options['success'][1];
            $new_recovery_options['item_success_frame_offset']['y'] = $recovery_options['success'][2];
            $new_recovery_options['item_success_frame_offset']['z'] = $recovery_options['success'][3];
            $new_recovery_options['success_text'] = $recovery_options['success'][4];
            $new_recovery_options['item_success_frame_span'] = isset($recovery_options['success'][5]) ? $recovery_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($recovery_options['failure'])){
            $new_recovery_options['item_failure_frame'] = $recovery_options['failure'][0];
            $new_recovery_options['item_failure_frame_offset']['x'] = $recovery_options['failure'][1];
            $new_recovery_options['item_failure_frame_offset']['y'] = $recovery_options['failure'][2];
            $new_recovery_options['item_failure_frame_offset']['z'] = $recovery_options['failure'][3];
            $new_recovery_options['failure_text'] = $recovery_options['failure'][4];
            $new_recovery_options['item_failure_frame_span'] = isset($recovery_options['failure'][5]) ? $recovery_options['failure'][5] : 1;
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
        // Update this item's data
        $this->set_attachment_options($attachment_options);
        // Return the resuling array
        return $this->get_attachment_options();
    }


    // Define a public function for easily updating attachment options
    public function attachment_options_update($attachment_options = array()){
        // Return the new array
        return $this->get_attachment_options();
    }

    // Define a function for generating item canvas variables
    public function get_canvas_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the item data array and populate basic data
        $this_data['item_markup'] = '';
        $this_data['data_sticky'] = isset($options['sticky']) ? $options['sticky'] : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'item';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['item_name'] = isset($options['item_name']) ? $options['item_name'] : $this->item_name;
        $this_data['item_id'] = $this->item_id;
        $this_data['item_title'] = $this->item_name;
        $this_data['item_token'] = $this->item_token;
        $this_data['item_id_token'] = $this->item_id.'_'.$this->item_token;
        $this_data['item_image'] = isset($options['item_image']) ? $options['item_image'] : $this->item_image;
        $this_data['item_status'] = $robot_data['robot_status'];
        $this_data['item_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['item_direction'] = $this->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['item_float'] = $robot_data['robot_float'];
        $this_data['item_size'] = $this_data['item_position'] == 'active' ? ($this->item_image_size * 2) : $this->item_image_size;
        $this_data['item_frame'] = isset($options['item_frame']) ? $options['item_frame'] : $this->item_frame;
        $this_data['item_frame_span'] = isset($options['item_frame_span']) ? $options['item_frame_span'] : $this->item_frame_span;
        $this_data['item_frame_index'] = isset($options['item_frame_index']) ? $options['item_frame_index'] : $this->item_frame_index;
        if (is_numeric($this_data['item_frame']) && $this_data['item_frame'] >= 0){ $this_data['item_frame'] = str_pad($this_data['item_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['item_frame']) && $this_data['item_frame'] < 0){ $this_data['item_frame'] = ''; }
        //$this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/sprite_'.$this_data['item_direction'].'_'.$this_data['item_size'].'x'.$this_data['item_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['item_frame_offset'] = isset($options['item_frame_offset']) ? $options['item_frame_offset'] : $this->item_frame_offset;
        $animate_frames_array = isset($options['item_frame_animate']) ? $options['item_frame_animate'] : array($this_data['item_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['item_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['item_frame_styles'] = isset($options['item_frame_styles']) ? $options['item_frame_styles'] : $this->item_frame_styles;
        $this_data['item_frame_classes'] = isset($options['item_frame_classes']) ? $options['item_frame_classes'] : $this->item_frame_classes;

        $this_data['item_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : ($robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $robot_data['robot_key']) / 8) * 0.5));
        if (strstr($this_data['item_frame_classes'], 'sprite_fullscreen')){
            $this_data['item_frame_styles'] = '';
            $this_data['item_scale'] = 1;
        }

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this->item_image_size * 2);
        $this_data['item_sprite_size'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_width'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_height'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_image_width'] = ceil($this_data['item_scale'] * $zoom_size * 10);
        $this_data['item_image_height'] = ceil($this_data['item_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this robot
        $canvas_offset_data = rpg_functions::canvas_sprite_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size']);
        //$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        //$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        //$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];

        // Define the item's canvas offset variables
        //$temp_size_diff = $robot_data['robot_sprite_size'] != $item_data['item_sprite_size'] ? ceil(($robot_data['robot_sprite_size'] - $item_data['item_sprite_size']) * 0.5) : ceil($item_data['item_sprite_size'] * 0.25);
        //$temp_size_diff = $robot_data['robot_sprite_size'] > 80 ? ceil(($robot_data['robot_sprite_size'] - 80) / 2) : 0;
        //if ($temp_size_diff > 0 && $robot_data['robot_position'] != 'active'){ $temp_size_diff += floor($this_data['item_scale'] * $this_data['item_sprite_size'] * 0.5); }
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['item_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['item_sprite_size']) / 2) ; }
        //$temp_size_diff = floor(($temp_size_diff * 2) + ($temp_size_diff * $robot_data['robot_scale']));

        // If this is a STICKY attachedment, make sure it doesn't move with the robot
        if ($this_data['data_sticky'] != false){

            //$this_data['data_sticky'] = 'true';

            // Calculate the canvas X offsets using the robot's position as base
            if ($this_data['item_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['item_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $canvas_offset_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's position as base
            if ($this_data['item_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['y']/100))); }
            elseif ($this_data['item_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $canvas_offset_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's position as base
            if ($this_data['item_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] + $this_data['item_frame_offset']['z']); }
            elseif ($this_data['item_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] - ($this_data['item_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $canvas_offset_data['canvas_offset_z'];  }

            // Collect the target, damage, and recovery options
            $this_target_options = !empty($options['this_item']->target_options) ? $options['this_item']->target_options : array();
            $this_damage_options = !empty($options['this_item']->damage_options) ? $options['this_item']->damage_options : array();
            $this_recovery_options = !empty($options['this_item']->recovery_options) ? $options['this_item']->recovery_options : array();
            $this_results = !empty($options['this_item']->item_results) ? $options['this_item']->item_results : array();

            // Either way, apply target offsets if they exist and it's this robot using the item
            if (isset($options['this_item_target']) && $options['this_item_target'] == $this_data['robot_id_token']){
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
            if ($this_data['item_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['item_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            if ($this_data['item_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['y']/100))); }
            elseif ($this_data['item_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            if ($this_data['item_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $this_data['item_frame_offset']['z']); }
            elseif ($this_data['item_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($this_data['item_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

        }


        // Define the rest of the display variables
        //$this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
        if (!preg_match('/^images/i', $this_data['item_image'])){ $this_data['item_image'] = 'images/items/'.$this_data['item_image'].'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['item_markup_class'] = 'sprite sprite_item ';
        $this_data['item_markup_class'] .= 'sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].' sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].'_'.$this_data['item_frame'].' ';
        $this_data['item_markup_class'] .= 'item_status_'.$this_data['item_status'].' item_position_'.$this_data['item_position'].' ';
        $frame_position = is_numeric($this_data['item_frame']) ? (int)($this_data['item_frame']) : array_search($this_data['item_frame'], $this_data['item_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['item_sprite_size'] * $frame_position));
        $this_data['item_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['item_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['item_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image'].'); width: '.($this_data['item_sprite_size'] * $this_data['item_frame_span']).'px; height: '.$this_data['item_sprite_size'].'px; background-size: '.$this_data['item_image_width'].'px '.$this_data['item_image_height'].'px; ';

        // Generate the final markup for the canvas item
        ob_start();

            // Display the item's battle sprite
            echo '<div data-item-id="'.$this_data['item_id_token'].'" data-robot-id="'.$robot_data['robot_id_token'].'" class="'.($this_data['item_markup_class'].$this_data['item_frame_classes']).'" style="'.($this_data['item_markup_style'].$this_data['item_frame_styles']).'" data-debug="'.$this_data['data_debug'].'" data-sticky="'.($this_data['data_sticky'] === false ? 'false' : $this_data['data_sticky']).'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['item_sprite_size'].'" data-direction="'.$this_data['item_direction'].'" data-frame="'.$this_data['item_frame'].'" data-animate="'.$this_data['item_frame_animate'].'" data-position="'.$this_data['item_position'].'" data-status="'.$this_data['item_status'].'" data-scale="'.$this_data['item_scale'].'">'.$this_data['item_token'].'</div>';

        // Collect the generated item markup
        $this_data['item_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating item console variables
    public function get_console_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console item data
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this item
        $this_data['item_name'] = isset($options['item_name']) ? $options['item_name'] : $this->item_name;
        $this_data['item_title'] = $this_data['item_name'];
        $this_data['item_token'] = $this->item_token;
        if (preg_match('/^item-/i', $this->item_token)){ $this_data['item_direction'] = 'right'; }
        else { $this_data['item_direction'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this->robot_id ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left'); }
        $this_data['item_float'] = !empty($robot_data['robot_id']) && $robot_data['robot_id'] == $this->robot_id ? $robot_data['robot_float'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['item_size'] = $this->item_image_size;
        $this_data['item_frame'] = isset($options['item_frame']) ? $options['item_frame'] : $this->item_frame;
        if (is_numeric($this_data['item_frame']) && $this_data['item_frame'] >= 0){ $this_data['item_frame'] = str_pad($this_data['item_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['item_frame']) && $this_data['item_frame'] < 0){ $this_data['item_frame'] = ''; }
        $this_data['image_type'] = !empty($options['this_item_image']) ? $options['this_item_image'] : 'icon';

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['item_float'];
        $this_data['container_style'] = '';
        $this_data['item_markup_class'] = 'sprite sprite_item sprite_item_'.$this_data['image_type'].' ';
        $this_data['item_markup_style'] = '';
        if (empty($this_data['item_image']) || !preg_match('/^images/i', $this_data['item_image'])){ $this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/'.$this_data['image_type'].'_'.$this_data['item_direction'].'_'.$this_data['item_size'].'x'.$this_data['item_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['item_markup_class'] .= 'sprite_'.$this_data['item_size'].'x'.$this_data['item_size'].' sprite_'.$this_data['item_size'].'x'.$this_data['item_size'].'_'.$this_data['item_frame'].' ';
        $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image'].'); ';

        // Generate the final markup for the console item
        $this_data['item_markup'] = '';
        $this_data['item_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['item_markup'] .= '<div class="'.$this_data['item_markup_class'].'" style="'.$this_data['item_markup_style'].'" title="'.$this_data['item_title'].'">'.$this_data['item_title'].'</div>';
        $this_data['item_markup'] .= '</div>';

        // Return the item console data
        return $this_data;

    }

    // Define a function for pulling the full item index
    public static function get_index($parse_data = false){
        global $this_database;
        $item_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_flag_complete = 1 OR item_token = 'item';", 'item_token');
        if (!empty($item_index)){
            if ($parse_data){ $item_index = self::parse_index($item_index); }
            return $item_index;
        } else {
            return array();
        }
    }

    // Define a function for pulling a custom item index
    public static function get_index_custom($item_tokens = array(), $parse_data = false){
        global $this_database;
        $item_tokens_string = array();
        foreach ($item_tokens AS $item_token){ $item_tokens_string[] = "'{$item_token}'"; }
        $item_tokens_string = implode(', ', $item_tokens_string);
        $item_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_token IN ({$item_tokens_string});", 'item_token');
        if (!empty($item_index)){
            if ($parse_data){ $item_index = self::parse_index($item_index); }
            return $item_index;
        } else {
            return array();
        }
    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($item_token, $parse_data = true){
        global $this_database;
        $item_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_token LIKE '{$item_token}';", 'item_token');
        if (!empty($item_index)){
            if ($parse_data){ $item_index = self::parse_index_info($item_index); }
            return $item_index;
        } else {
            return array();
        }
    }

    // Define a public function for parsing a item index array in bulk
    public static function parse_index($item_index){
        foreach ($item_index AS $token => $info){ $item_index[$token] = self::parse_index_info($info); }
        return $item_index;
    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($item_info){
        global $this_database;

        // Return false if empty
        if (empty($item_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($item_info['_parsed'])){ return $item_info; }
        else { $item_info['_parsed'] = true; }

        // Explode the base and animation indexes into an array
        $temp_field_names = array('item_frame_animate', 'item_frame_index', 'item_frame_offset');
        foreach ($temp_field_names AS $field_name){
            if (!empty($item_info[$field_name])){ $item_info[$field_name] = json_decode($item_info[$field_name], true); }
            else { $item_info[$field_name] = array(); }
        }

        // Return the parsed item info
        return $item_info;
    }




    // Define a static function for printing out the item's title markup
    public static function print_editor_title_markup($robot_info, $item_info, $print_options = array()){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Collect the types index for reference
        $mmrpg_types = rpg_type::get_index();

        // Require the function file
        $temp_item_title = '';

        // Collect values for potentially missing global variables
        if (!isset($session_token)){  }

        if (empty($robot_info)){ return false; }
        if (empty($item_info)){ return false; }

        $print_options['show_accuracy'] = isset($print_options['show_accuracy']) ? $print_options['show_accuracy'] : true;
        $print_options['show_quantity'] = isset($print_options['show_quantity']) ? $print_options['show_quantity'] : true;

        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_item_token = $item_info['item_token'];
        $temp_item_type = !empty($item_info['item_type']) ? $mmrpg_types[$item_info['item_type']] : false;
        $temp_item_type2 = !empty($item_info['item_type2']) ? $mmrpg_types[$item_info['item_type2']] : false;
        $temp_item_energy = rpg_robot::calculate_weapon_energy_static($robot_info, $item_info);
        $temp_item_damage = !empty($item_info['item_damage']) ? $item_info['item_damage'] : 0;
        $temp_item_damage2 = !empty($item_info['item_damage2']) ? $item_info['item_damage2'] : 0;
        $temp_item_recovery = !empty($item_info['item_recovery']) ? $item_info['item_recovery'] : 0;
        $temp_item_recovery2 = !empty($item_info['item_recovery2']) ? $item_info['item_recovery2'] : 0;
        $temp_item_target = !empty($item_info['item_target']) ? $item_info['item_target'] : 'auto';
        while (!in_array($item_info['item_token'], $robot_info['robot_items'])){
            if (!$robot_flag_copycore){
                if (empty($robot_item_core)){ break; }
                elseif (empty($temp_item_type) && empty($temp_item_type2)){ break; }
                else {
                    $temp_type_array = array();
                    if (!empty($temp_item_type)){ $temp_type_array[] = $temp_item_type['type_token']; }
                    if (!empty($temp_item_type2)){ $temp_type_array[] = $temp_item_type2['type_token']; }
                    if (!in_array($robot_item_core, $temp_type_array)){ break; }
                }
            }
            break;
        }

        $temp_item_title = $item_info['item_name'];
        if (!empty($temp_item_type)){ $temp_item_title .= ' ('.$temp_item_type['type_name'].' Type)'; }
        if (!empty($temp_item_type2)){ $temp_item_title = str_replace('Type', '/ '.$temp_item_type2['type_name'].' Type', $temp_item_title); }

        if ($item_info['item_class'] != 'item'){
            if (!empty($item_info['item_damage']) || !empty($item_info['item_recovery'])){ $temp_item_title .= '  // '; }
            elseif (empty($item_info['item_damage']) && empty($item_info['item_recovery'])){ $temp_item_title .= '  // '; }
            if (!empty($item_info['item_damage']) && !empty($item_info['item_recovery'])){ $temp_item_title .= $item_info['item_damage'].' Damage | '.$item_info['item_recovery'].' Recovery'; }
            elseif (!empty($item_info['item_damage'])){ $temp_item_title .= $item_info['item_damage'].' Damage'; }
            elseif (!empty($item_info['item_recovery'])){ $temp_item_title .= $item_info['item_recovery'].' Recovery '; }
            if (!empty($item_info['item_damage']) || !empty($item_info['item_recovery'])){ $temp_item_title .= '  | '; }
        }

        //if (empty($item_info['item_damage']) && empty($item_info['item_recovery'])){ $temp_item_title .= 'Special'; }

        // If show accuracy or quantity
        if (($item_info['item_class'] != 'item' && $print_options['show_accuracy'])
            || ($item_info['item_class'] == 'item' && $print_options['show_quantity'])){

            $temp_item_title .= '  | ';
            if ($item_info['item_class'] != 'item' && !empty($item_info['item_accuracy'])){ $temp_item_title .= ' '.$item_info['item_accuracy'].'% Accuracy'; }
            elseif ($item_info['item_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_item_token])){ $temp_item_title .= ' '.($_SESSION[$session_token]['values']['battle_items'][$temp_item_token] == 1 ? '1 Unit' : $_SESSION[$session_token]['values']['battle_items'][$temp_item_token].' Units'); }
            elseif ($item_info['item_class'] == 'item' ){ $temp_item_title .= ' 0 Units'; }

        }

        if ($item_info['item_class'] != 'item' && !empty($temp_item_energy)){ $temp_item_title .= ' | '.$temp_item_energy.' Energy'; }
        if ($item_info['item_class'] != 'item' && $temp_item_target != 'auto'){ $temp_item_title .= ' | Select Target'; }

        if (!empty($item_info['item_description'])){
            $temp_find = array('{RECOVERY}', '{RECOVERY2}', '{DAMAGE}', '{DAMAGE2}');
            $temp_replace = array($temp_item_recovery, $temp_item_recovery2, $temp_item_damage, $temp_item_damage2);
            $temp_description = str_replace($temp_find, $temp_replace, $item_info['item_description']);
            $temp_item_title .= ' // '.$temp_description;
        }

        // Return the generated option markup
        return $temp_item_title;

    }


    // Define a static function for printing out the item's title markup
    public static function print_editor_option_markup($robot_info, $item_info){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Require the function file
        $this_option_markup = '';

        // Generate the item option markup
        if (empty($robot_info)){ return false; }
        if (empty($item_info)){ return false; }
        //$item_info = rpg_item::get_index_info($temp_item_token);
        $temp_robot_token = $robot_info['robot_token'];
        $temp_item_token = $item_info['item_token'];
        $robot_item_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_item_type = !empty($item_info['item_type']) ? rpg_type::get_index_info($item_info['item_type']) : false;
        $temp_item_type2 = !empty($item_info['item_type2']) ? rpg_type::get_index_info($item_info['item_type2']) : false;
        $temp_item_energy = rpg_robot::calculate_weapon_energy_static($robot_info, $item_info);
        $temp_type_array = array();
        $temp_incompatible = false;
        $temp_global_items = self::get_global_items();
        $temp_index_items = !empty($robot_info['robot_index_items']) ? $robot_info['robot_index_items'] : array();
        $temp_current_items = !empty($robot_info['robot_items']) ? array_keys($robot_info['robot_items']) : array();
        $temp_compatible_items = array_merge($temp_global_items, $temp_index_items, $temp_current_items);
        //while (!in_array($temp_item_token, $robot_info['robot_items'])){
        while (!in_array($temp_item_token, $temp_compatible_items)){
            if (!$robot_flag_copycore){
                if (empty($robot_item_core)){ $temp_incompatible = true; break; }
                elseif (empty($temp_item_type) && empty($temp_item_type2)){ $temp_incompatible = true; break; }
                else {
                    if (!empty($temp_item_type)){ $temp_type_array[] = $temp_item_type['type_token']; }
                    if (!empty($temp_item_type2)){ $temp_type_array[] = $temp_item_type2['type_token']; }
                    if (!in_array($robot_item_core, $temp_type_array)){ $temp_incompatible = true; break; }
                }
            }
            break;
        }
        if ($temp_incompatible == true){ return false; }
        $temp_item_label = $item_info['item_name'];
        $temp_item_title = rpg_item::print_editor_title_markup($robot_info, $item_info);
        $temp_item_title_plain = strip_tags(str_replace('<br />', '&#10;', $temp_item_title));
        $temp_item_title_tooltip = htmlentities($temp_item_title, ENT_QUOTES, 'UTF-8');
        $temp_item_option = $item_info['item_name'];
        if (!empty($temp_item_type)){ $temp_item_option .= ' | '.$temp_item_type['type_name']; }
        if (!empty($temp_item_type2)){ $temp_item_option .= ' / '.$temp_item_type2['type_name']; }
        if (!empty($item_info['item_damage'])){ $temp_item_option .= ' | D:'.$item_info['item_damage']; }
        if (!empty($item_info['item_recovery'])){ $temp_item_option .= ' | R:'.$item_info['item_recovery']; }
        if ($item_info['item_class'] != 'item' && !empty($item_info['item_accuracy'])){ $temp_item_option .= ' | A:'.$item_info['item_accuracy']; }
        elseif ($item_info['item_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_item_token])){ $temp_item_option .= ' | U:'.$_SESSION[$session_token]['values']['battle_items'][$temp_item_token]; }
        elseif ($item_info['item_class'] == 'item'){ $temp_item_option .= ' | U:0'; }
        if (!empty($temp_item_energy)){ $temp_item_option .= ' | E:'.$temp_item_energy; }

        // Return the generated option markup
        $this_option_markup = '<option value="'.$temp_item_token.'" data-label="'.$temp_item_label.'" data-type="'.(!empty($temp_item_type) ? $temp_item_type['type_token'] : 'none').'" data-type2="'.(!empty($temp_item_type2) ? $temp_item_type2['type_token'] : '').'" title="'.$temp_item_title_plain.'" data-tooltip="'.$temp_item_title_tooltip.'">'.$temp_item_option.'</option>';

        // Return the generated option markup
        return $this_option_markup;

    }


    // Define a static function for printing out the item's select options markup
    public static function print_editor_options_list_markup($player_item_rewards, $robot_item_rewards, $player_info, $robot_info){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $this_database;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_items;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
        global $mmrpg_database_items;
        global $session_token;

        // Require the function file
        $this_options_markup = '';

        // Collect values for potentially missing global variables
        if (!isset($session_token)){ $session_token = rpg_game::session_token(); }

        if (empty($player_info)){ return false; }
        if (empty($robot_info)){ return false; }

        $player_item_options = array();
        $player_items_unlocked = array_keys($player_item_rewards);
        if (!empty($mmrpg_database_items)){
            $temp_category = 'special-weapons';
            foreach ($mmrpg_database_items AS $item_token => $item_info){
                if ($item_token == 'energy-boost'){ $temp_category = 'support-items'; }
                if (!in_array($item_token, $player_items_unlocked)){ continue; }
                $item_info = rpg_item::parse_index_info($item_info);
                $option_markup = rpg_item::print_editor_option_markup($robot_info, $item_info);
                $player_item_options[$temp_category][] = $option_markup;
            }
        }
        if (!empty($player_item_options)){
            foreach ($player_item_options AS $category_token => $item_options){
                $category_name = ucwords(str_replace('-', ' ', $category_token));
                $this_options_markup .= '<optgroup label="'.$category_name.'">'.implode('', $item_options).'</optgroup>';
            }
        }

        /*
        $robot_item_rewards_options = array();
        foreach ($robot_item_rewards AS $temp_item_info){
            if (empty($temp_item_info['item_token']) || !isset($mmrpg_database_items[$temp_item_info['item_token']])){ continue; }
            $temp_token = $temp_item_info['item_token'];
            $temp_item_info = rpg_item::parse_index_info($mmrpg_database_items[$temp_token]);
            $temp_option_markup = rpg_item::print_editor_option_markup($robot_info, $temp_item_info);
            if (!empty($temp_option_markup)){ $robot_item_rewards_options[] = $temp_option_markup; }
        }
        $robot_item_rewards_options = '<optgroup label="Robot Items">'.implode('', $robot_item_rewards_options).'</optgroup>';
        $this_options_markup .= $robot_item_rewards_options;
        */

        /*
        $player_item_weapon_options = array();
        $player_item_support_options = array();
        foreach ($player_item_rewards AS $temp_item_key => $temp_item_info){
            if (empty($temp_item_info['item_token']) || !isset($mmrpg_database_items[$temp_item_info['item_token']])){ continue; }
            $temp_token = $temp_item_info['item_token'];
            $temp_item_info = rpg_item::parse_index_info($mmrpg_database_items[$temp_token]);
            $temp_option_markup = rpg_item::print_editor_option_markup($robot_info, $temp_item_info);

            if (!empty($temp_option_markup)){
                if ($temp_category == 'weapon'){ $player_item_weapon_options[] = $temp_option_markup; }
                elseif ($temp_category == 'support'){ $player_item_support_options[] = $temp_option_markup; }
            }
        }
        $player_item_weapon_options = '<optgroup label="Special Weapons">'.implode('', $player_item_weapon_options).'</optgroup>';
        $player_item_support_options = '<optgroup label="Support Items">'.implode('', $player_item_support_options).'</optgroup>';
        $this_options_markup .= $player_item_weapon_options;
        $this_options_markup .= $player_item_support_options;
        */

        // Add an option at the bottom to remove the item
        $this_options_markup .= '<optgroup label="Item Actions">';
        $this_options_markup .= '<option value="" title="">- Remove Item -</option>';
        $this_options_markup .= '</optgroup>';

        // Return the generated select markup
        return $this_options_markup;

    }


    // Define a static function for printing out the item select markup
    public static function print_editor_select_markup($item_rewards_options, $player_info, $robot_info, $item_info, $item_key = 0){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $this_database;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_items;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_rewards, $player_item_rewards, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
        global $mmrpg_database_items;
        global $session_token;

        // Require the function file
        $this_select_markup = '';

        // Collect values for potentially missing global variables
        if (!isset($session_token)){ $session_token = rpg_game::session_token(); }

        if (empty($robot_info)){ return false; }
        if (empty($item_info)){ return false; }

        $item_info_token = $item_info['item_token'];
        $item_info_count = !empty($_SESSION[$session_token]['values']['battle_items'][$item_info_token]) ? $_SESSION[$session_token]['values']['battle_items'][$item_info_token] : 0;
        $item_info_name = $item_info['item_name'];
        $item_info_type = !empty($item_info['item_type']) ? $item_info['item_type'] : false;
        $item_info_type2 = !empty($item_info['item_type2']) ? $item_info['item_type2'] : false;
        if (!empty($item_info_type) && !empty($mmrpg_index['types'][$item_info_type])){
            $item_info_type = $mmrpg_index['types'][$item_info_type]['type_name'].' Type';
            if (!empty($item_info_type2) && !empty($mmrpg_index['types'][$item_info_type2])){
                $item_info_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$item_info_type2]['type_name'].' Type', $item_info_type);
            }
        } else {
            $item_info_type = '';
        }
        $item_info_energy = isset($item_info['item_energy']) ? $item_info['item_energy'] : 4;
        $item_info_damage = !empty($item_info['item_damage']) ? $item_info['item_damage'] : 0;
        $item_info_damage2 = !empty($item_info['item_damage2']) ? $item_info['item_damage2'] : 0;
        $item_info_damage_percent = !empty($item_info['item_damage_percent']) ? true : false;
        $item_info_damage2_percent = !empty($item_info['item_damage2_percent']) ? true : false;
        if ($item_info_damage_percent && $item_info_damage > 100){ $item_info_damage = 100; }
        if ($item_info_damage2_percent && $item_info_damage2 > 100){ $item_info_damage2 = 100; }
        $item_info_recovery = !empty($item_info['item_recovery']) ? $item_info['item_recovery'] : 0;
        $item_info_recovery2 = !empty($item_info['item_recovery2']) ? $item_info['item_recovery2'] : 0;
        $item_info_recovery_percent = !empty($item_info['item_recovery_percent']) ? true : false;
        $item_info_recovery2_percent = !empty($item_info['item_recovery2_percent']) ? true : false;
        if ($item_info_recovery_percent && $item_info_recovery > 100){ $item_info_recovery = 100; }
        if ($item_info_recovery2_percent && $item_info_recovery2 > 100){ $item_info_recovery2 = 100; }
        $item_info_accuracy = !empty($item_info['item_accuracy']) ? $item_info['item_accuracy'] : 0;
        $item_info_description = !empty($item_info['item_description']) ? $item_info['item_description'] : '';
        $item_info_description = str_replace('{DAMAGE}', $item_info_damage, $item_info_description);
        $item_info_description = str_replace('{RECOVERY}', $item_info_recovery, $item_info_description);
        $item_info_description = str_replace('{DAMAGE2}', $item_info_damage2, $item_info_description);
        $item_info_description = str_replace('{RECOVERY2}', $item_info_recovery2, $item_info_description);
        $item_info_class_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
        if (!empty($item_info['item_type2'])){ $item_info_class_type = $item_info_class_type != 'none' ? $item_info_class_type.'_'.$item_info['item_type2'] : $item_info['item_type2']; }
        $item_info_title = rpg_item::print_editor_title_markup($robot_info, $item_info);
        $item_info_title_plain = strip_tags(str_replace('<br />', '//', $item_info_title));
        $item_info_title_tooltip = htmlentities($item_info_title, ENT_QUOTES, 'UTF-8');
        $item_info_title_html = str_replace(' ', '&nbsp;', $item_info_name);
        $item_info_title_html .= '<span class="count">x '.$item_info_count.'</span>';
        $temp_select_options = str_replace('value="'.$item_info_token.'"', 'value="'.$item_info_token.'" selected="selected" disabled="disabled"', $item_rewards_options);
        $item_info_title_html = '<label style="background-image: url(i/i/'.$item_info_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$item_info_title_html.'</label>';
        //if ($global_allow_editing){ $item_info_title_html .= '<select class="item_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
        $this_select_markup = '<a class="item_name type type_'.$item_info_class_type.'" style="'.(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-item="'.$item_info_token.'" data-count="'.$item_info_count.'" title="'.$item_info_title_plain.'" data-tooltip="'.$item_info_title_tooltip.'">'.$item_info_title_html.'</a>';

        // Return the generated select markup
        return $this_select_markup;

    }

    // Define a public function for recalculating internal counters
    public function update_variables(){

        // Update parent objects first
        //$this->robot->update_variables();

        // Calculate this item's count variables
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
        $_SESSION['ITEMS'][$this->robot->robot_id][$this->item_id] = $this_data;
        $this->battle->values['items'][$this->item_id] = $this_data;
        //$this->player->values['items'][$this->item_id] = $this_data;
        //$this->robot->values['items'][$this->item_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal item fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_token' => $this->player_token,
            'robot_id' => $this->robot_id,
            'robot_token' => $this->robot_token,
            'item_id' => $this->item_id,
            'item_key' => $this->item_key,
            'item_name' => $this->item_name,
            'item_token' => $this->item_token,
            'item_class' => $this->item_class,
            'item_subclass' => $this->item_subclass,
            'item_master' => $this->item_master,
            'item_number' => $this->item_number,
            'item_image' => $this->item_image,
            'item_image_size' => $this->item_image_size,
            'item_description' => $this->item_description,
            'item_type' => $this->item_type,
            'item_type2' => $this->item_type2,
            'item_energy' => $this->item_energy,
            'item_energy_percent' => $this->item_energy_percent,
            'item_speed' => $this->item_speed,
            'item_damage' => $this->item_damage,
            'item_damage2' => $this->item_damage2,
            'item_damage_percent' => $this->item_damage_percent,
            'item_damage2_percent' => $this->item_damage2_percent,
            'item_recovery' => $this->item_recovery,
            'item_recovery2' => $this->item_recovery2,
            'item_recovery_percent' => $this->item_recovery_percent,
            'item_recovery2_percent' => $this->item_recovery2_percent,
            'item_accuracy' => $this->item_accuracy,
            'item_target' => $this->item_target,
            'item_functions' => $this->item_functions,
            'item_results' => $this->item_results,
            'item_frame' => $this->item_frame,
            'item_frame_span' => $this->item_frame_span,
            'item_frame_index' => $this->item_frame_index,
            'item_frame_animate' => $this->item_frame_animate,
            'item_frame_offset' => $this->item_frame_offset,
            'item_frame_classes' => $this->item_frame_classes,
            'item_frame_styles' => $this->item_frame_styles,
            'item_base_name' => $this->item_base_name,
            'item_base_image' => $this->item_base_image,
            'item_base_image_size' => $this->item_base_image_size,
            'item_base_description' => $this->item_base_description,
            'item_base_type' => $this->item_base_type,
            'item_base_type2' => $this->item_base_type2,
            'item_base_energy' => $this->item_base_energy,
            'item_base_speed' => $this->item_base_speed,
            'item_base_damage' => $this->item_base_damage,
            'item_base_damage2' => $this->item_base_damage2,
            'item_base_recovery' => $this->item_base_recovery,
            'item_base_recovery2' => $this->item_base_recovery2,
            'item_base_accuracy' => $this->item_base_accuracy,
            'item_base_target' => $this->item_base_target,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

    // Define a static function for printing out the item's database markup
    public static function print_database_markup($item_info, $print_options = array()){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url;
        global $mmrpg_database_items, $mmrpg_database_robots, $mmrpg_database_items, $mmrpg_database_types;
        global $this_database;

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

        // Collect the item sprite dimensions
        $item_image_size = !empty($item_info['item_image_size']) ? $item_info['item_image_size'] : 40;
        $item_image_size_text = $item_image_size.'x'.$item_image_size;
        $item_image_token = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];

        // Collect the item's type for background display
        $item_type_class = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
        if ($item_type_class != 'none' && !empty($item_info['item_type2'])){ $item_type_class .= '_'.$item_info['item_type2']; }
        elseif ($item_type_class == 'none' && !empty($item_info['item_type2'])){ $item_type_class = $item_info['item_type2'];  }
        $item_header_types = 'item_type_'.$item_type_class.' ';
        // If this is a special category of item, it's a special type
        if (preg_match('/^(red|blue|green|purple)-score-ball$/i', $item_info['item_token'])){ $item_info['item_type_special'] = 'bonus'; }
        elseif (preg_match('/^super-(pellet|capsule)$/i', $item_info['item_token'])){ $item_info['item_type_special'] = 'multi'; }

        // Define the sprite sheet alt and title text
        $item_sprite_size = $item_image_size * 2;
        $item_sprite_size_text = $item_sprite_size.'x'.$item_sprite_size;
        $item_sprite_title = $item_info['item_name'];
        //$item_sprite_title = $item_info['item_number'].' '.$item_info['item_name'];
        //$item_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

        // Define the sprite frame index for robot images
        $item_sprite_frames = array('frame_01','frame_02','frame_03','frame_04','frame_05','frame_06','frame_07','frame_08','frame_09','frame_10');

        // Limit any damage or recovery percents to 100%
        if (!empty($item_info['item_damage_percent']) && $item_info['item_damage'] > 100){ $item_info['item_damage'] = 100; }
        if (!empty($item_info['item_damage2_percent']) && $item_info['item_damage2'] > 100){ $item_info['item_damage2'] = 100; }
        if (!empty($item_info['item_recovery_percent']) && $item_info['item_recovery'] > 100){ $item_info['item_recovery'] = 100; }
        if (!empty($item_info['item_recovery2_percent']) && $item_info['item_recovery2'] > 100){ $item_info['item_recovery2'] = 100; }

        // Start the output buffer
        ob_start();
        ?>
        <div class="database_container database_<?= $item_info['item_class'] == 'item' ? 'item' : 'item' ?>_container" data-token="<?= $item_info['item_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

            <?php if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?= $item_info['item_token']?>">&nbsp;</a>
            <?php endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $item_info['item_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

                <?php if($print_options['show_icon']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <?php if($print_options['show_icon']): ?>
                            <?php if($print_options['show_key'] !== false): ?>
                                <div class="icon item_type <?= $item_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                            <?php endif; ?>
                            <?php if ($item_image_token != 'item'){ ?>
                                <div class="icon item_type <?= $item_header_types ?>"><div style="background-image: url(i/i/<?= $item_image_token ?>/ir<?= $item_image_size ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_item sprite_40x40 sprite_40x40_icon sprite_size_<?= $item_image_size_text ?> sprite_size_<?= $item_image_size_text ?>_icon"><?= $item_info['item_name']?>'s Mugshot</div></div>
                            <?php } else { ?>
                                <div class="icon item_type <?= $item_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_item sprite_40x40 sprite_40x40_icon sprite_size_<?= $item_image_size_text ?> sprite_size_<?= $item_image_size_text ?>_icon">No Image</div></div>
                            <?php } ?>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $item_header_types ?> <?= (!$print_options['show_icon']) ? 'noicon' : '' ?>">
                        <?php if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="<?= preg_match('/^item-/', $item_info['item_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $item_info['item_token']).'/' : 'database/items/'.$item_info['item_token'].'/' ?>"><?= $item_info['item_name'] ?></a>
                        <?php else: ?>
                            <?= $item_info['item_name'] ?>&#39;s Data
                        <?php endif; ?>
                        <?php if (!empty($item_info['item_type_special'])){ ?>
                            <div class="header_core item_type"><?= ucfirst($item_info['item_type_special']) ?> Type</div>
                        <?php } elseif (!empty($item_info['item_type']) && !empty($item_info['item_type2'])){ ?>
                            <div class="header_core item_type"><?= ucfirst($item_info['item_type']).' / '.ucfirst($item_info['item_type2']) ?> Type</div>
                        <?php } elseif (!empty($item_info['item_type'])){ ?>
                            <div class="header_core item_type"><?= ucfirst($item_info['item_type']) ?> Type</div>
                        <?php } else { ?>
                            <div class="header_core item_type">Neutral Type</div>
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
                                        <span class="item_type item_type_"><?= $item_info['item_name']?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Type :</label>
                                        <?php if($print_options['layout_style'] != 'event'): ?>
                                            <?php
                                            if (!empty($item_info['item_type_special'])){
                                                echo '<a href="database/items/'.$item_info['item_type_special'].'/" class="item_type '.$item_header_types.'">'.ucfirst($item_info['item_type_special']).'</a>';
                                            }
                                            elseif (!empty($item_info['item_type'])){
                                                $temp_string = array();
                                                $item_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                                                $temp_string[] = '<a href="database/items/'.$item_type.'/" class="item_type item_type_'.$item_type.'">'.$mmrpg_types[$item_type]['type_name'].'</a>';
                                                if (!empty($item_info['item_type2'])){
                                                    $item_type2 = !empty($item_info['item_type2']) ? $item_info['item_type2'] : 'none';
                                                    $temp_string[] = '<a href="database/items/'.$item_type2.'/" class="item_type item_type_'.$item_type2.'">'.$mmrpg_types[$item_type2]['type_name'].'</a>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<a href="database/items/none/" class="item_type item_type_none">Neutral</a>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <?php
                                            if (!empty($item_info['item_type_special'])){
                                                echo '<span class="item_type '.$item_header_types.'">'.ucfirst($item_info['item_type_special']).'</span>';
                                            }
                                            elseif (!empty($item_info['item_type'])){
                                                $temp_string = array();
                                                $item_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                                                $temp_string[] = '<span class="item_type item_type_'.$item_type.'">'.$mmrpg_types[$item_type]['type_name'].'</span>';
                                                if (!empty($item_info['item_type2'])){
                                                    $item_type2 = !empty($item_info['item_type2']) ? $item_info['item_type2'] : 'none';
                                                    $temp_string[] = '<span class="item_type item_type_'.$item_type2.'">'.$mmrpg_types[$item_type2]['type_name'].'</span>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<span class="item_type item_type_none">Neutral</span>';
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Power :</label>
                                        <?php if(!empty($item_info['item_damage']) || !empty($item_info['item_recovery'])): ?>
                                            <?php if(!empty($item_info['item_damage'])){ ?><span class="item_stat"><?= number_format($item_info['item_damage'], 0, '.', ',').(!empty($item_info['item_damage_percent']) ? '%' : '') ?> Damage</span><?php } ?>
                                            <?php if(!empty($item_info['item_recovery'])){ ?><span class="item_stat"><?= number_format($item_info['item_recovery'], 0, '.', ',').(!empty($item_info['item_recovery_percent']) ? '%' : '') ?> Recovery</span><?php } ?>
                                        <?php elseif(!empty($item_info['item_damage2']) || !empty($item_info['item_recovery2'])): ?>
                                            <?php if(!empty($item_info['item_damage2'])){ ?><span class="item_stat"><?= number_format($item_info['item_damage2'], 0, '.', ',').(!empty($item_info['item_damage2_percent']) ? '%' : '') ?></span><?php } ?>
                                            <?php if(!empty($item_info['item_recovery2'])){ ?><span class="item_stat"><?= number_format($item_info['item_recovery2'], 0, '.', ',').(!empty($item_info['item_recovery2_percent']) ? '%' : '') ?></span><?php } ?>
                                        <?php else: ?>
                                            <span class="item_stat">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Value :</label>
                                        <?php if(!empty($item_info['item_price'])): ?>
                                            <span class="item_stat"><?= number_format($item_info['item_price'], 0, '.', ',').'z' ?></span>
                                        <?php else: ?>
                                            <span class="item_stat">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
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
                                            (!empty($item_info['item_damage']) ? number_format($item_info['item_damage'], 0, '.', ',') : 0), // {DAMAGE}
                                            (!empty($item_info['item_recovery']) ? number_format($item_info['item_recovery'], 0, '.', ',') : 0), // {RECOVERY}
                                            (!empty($item_info['item_damage2']) ? number_format($item_info['item_damage2'], 0, '.', ',') : 0), // {DAMAGE2}
                                            (!empty($item_info['item_recovery2']) ? number_format($item_info['item_recovery2'], 0, '.', ',') : 0), // {RECOVERY2}
                                            '' // {}
                                            );
                                        echo !empty($item_info['item_description']) ? str_replace($temp_find, $temp_replace, $item_info['item_description']) : '&hellip;'
                                        ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_sprites'] && (!isset($item_info['item_image_sheets']) || $item_info['item_image_sheets'] !== 0) && $item_image_token != 'item' ): ?>

                    <?php
                    // Start the output buffer and prepare to collect sprites
                    ob_start();

                    // Define the alts we'll be looping through for this item
                    $temp_alts_array = array();
                    $temp_alts_array[] = array('token' => '', 'name' => $item_info['item_name'], 'summons' => 0);
                    // Append predefined alts automatically, based on the item image alt array
                    if (!empty($item_info['item_image_alts'])){
                        $temp_alts_array = array_merge($temp_alts_array, $item_info['item_image_alts']);
                    }
                    // Otherwise, if this is a copy item, append based on all the types in the index
                    elseif ($item_info['item_type'] == 'copy' && preg_match('/^(mega-man|proto-man|bass)$/i', $item_info['item_token'])){
                        foreach ($mmrpg_database_types AS $type_token => $type_info){
                            if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
                            $temp_alts_array[] = array('token' => $type_token, 'name' => $item_info['item_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
                        }
                    }
                    // Otherwise, if this robot has multiple sheets, add them as alt options
                    elseif (!empty($item_info['item_image_sheets'])){
                        for ($i = 2; $i <= $item_info['item_image_sheets']; $i++){
                            $temp_alts_array[] = array('sheet' => $i, 'name' => $item_info['item_name'].' (Sheet #'.$i.')', 'summons' => 0);
                        }
                    }

                    // Loop through the alts and display images for them (yay!)
                    foreach ($temp_alts_array AS $alt_key => $alt_info){

                        // Define the current image token with alt in mind
                        $temp_item_image_token = $item_image_token;
                        $temp_item_image_token .= !empty($alt_info['token']) ? '_'.$alt_info['token'] : '';
                        $temp_item_image_token .= !empty($alt_info['sheet']) ? '-'.$alt_info['sheet'] : '';
                        $temp_item_image_name = $alt_info['name'];
                        // Update the alt array with this info
                        $temp_alts_array[$alt_key]['image'] = $temp_item_image_token;

                        // Collect the number of sheets
                        $temp_sheet_number = !empty($item_info['item_image_sheets']) ? $item_info['item_image_sheets'] : 1;

                        // Loop through the different frames and print out the sprite sheets
                        foreach (array('right', 'left') AS $temp_direction){
                            $temp_direction2 = substr($temp_direction, 0, 1);
                            $temp_embed = '[item:'.$temp_direction.']{'.$temp_item_image_token.'}';
                            $temp_title = $temp_item_image_name.' | Icon Sprite '.ucfirst($temp_direction);
                            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                            $temp_label = 'Icon '.ucfirst(substr($temp_direction, 0, 1));
                            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_item_image_token.'" data-frame="icon" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$item_sprite_size.'px; height: '.$item_sprite_size.'px; overflow: hidden;">';
                                echo '<img style="margin-left: 0;" data-tooltip="'.$temp_title.'" src="i/i/'.$temp_item_image_token.'/i'.$temp_direction2.$item_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                            echo '</div>';
                        }


                        // Loop through the different frames and print out the sprite sheets
                        foreach ($item_sprite_frames AS $this_key => $this_frame){
                            $margin_left = ceil((0 - $this_key) * $item_sprite_size);
                            $frame_relative = $this_frame;
                            //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($item_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                            $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_direction2 = substr($temp_direction, 0, 1);
                                $temp_embed = '[item:'.$temp_direction.':'.$frame_relative.']{'.$temp_item_image_token.'}';
                                $temp_title = $temp_item_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
                                $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
                                $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                //$image_token = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
                                //if ($temp_sheet > 1){ $temp_item_image_token .= '-'.$temp_sheet; }
                                echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_item_image_token.'" data-frame="'.$frame_relative.'" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$item_sprite_size.'px; height: '.$item_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="i/i/'.$temp_item_image_token.'/s'.$temp_direction2.$item_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                        }
                    }

                    // Collect the sprite markup from the output buffer for later
                    $this_sprite_markup = ob_get_clean();

                    ?>

                    <h2 id="sprites" class="header header_full <?= $item_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $item_info['item_name']?>&#39;s Sprites
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
                                        $alt_type = 'item_type item_type_'.$alt_type.' core_type ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
                                    }
                                    else {
                                        $alt_name = $alt_key + 1; //$alt_key == 0 ? $item_info['item_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : ''); //$alt_key == 0 ? $item_info['item_name'] : $item_info['item_name'].' Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                                        $alt_type = 'item_type item_type_empty ';
                                        $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                                        //if ($item_info['item_type'] == 'copy' && $alt_key == 0){ $alt_type = 'item_type item_type_empty '; }
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
                                    echo '<span class="item_type item_type_empty" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ">'.ucfirst($temp_direction).'</span>';
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
                        if (!empty($item_info['item_image_editor'])){
                            if ($item_info['item_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                            elseif ($item_info['item_image_editor'] == 110){ $temp_editor_title = 'MetalMarioX100 / EliteP1'; }
                            elseif ($item_info['item_image_editor'] == 18){ $temp_editor_title = 'Sean Adamson / MetalMan'; }
                        } elseif ($item_image_token != 'item'){
                            $temp_editor_title = 'Adrian Marceau / Ageman20XX';
                        }
                        ?>
                        <p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 10px; margin-top: 6px;">Sprite Editing by <strong><?= $temp_editor_title ?></strong> <span style="color: #565656;"> | </span> Original Artwork by <strong>Capcom</strong></p>
                    </div>

                <?php endif; ?>

                <?php if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= 'database/items/'.$item_info['item_token'].'/' ?>" rel="permalink">+ Permalink</a>

                <?php elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= 'database/items/'.$item_info['item_token'].'/' ?>" rel="permalink">+ View More</a>

                <?php endif; ?>

            </div>
        </div>
        <?php
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

    // Define a static function to use as the common action for all shard-___ items
    public static function item_function_shard($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_item->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 60, 40, 99,
                $this_player->print_name().' hands over an item from the inventory&hellip; <br />'.
                $this_robot->print_name().' throws a '.$this_item->print_name().' at the target!'
                )
            ));
        $this_robot->trigger_target($this_robot, $this_item);

        // Inflict damage on the opposing robot
        $this_item->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -90, 0, 10, $target_robot->print_name().' was hit by the '.$this_item->print_name().'!'),
            'failure' => array(0, -120, 0, -10, 'The '.$this_item->print_name().' missed&hellip;')
            ));
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(0, -60, 0, 10, $target_robot->print_name().' absorbed the '.$this_item->print_name().'!'),
            'failure' => array(0, -90, 0, -10, 'The '.$this_item->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_energy * 0.25);
        $target_robot->trigger_damage($this_robot, $this_item, $energy_damage_amount);

        // Return true on success
        return true;

    }


    // Define a static function to use as the common action for all item-core-___ items
    public static function item_function_core($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_item->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 60, 40, 99,
                $this_player->print_name().' hands over an item from the inventory&hellip; <br />'.
                $this_robot->print_name().' throws a '.$this_item->print_name().' at the target!'
                )
            ));
        $this_robot->trigger_target($this_robot, $this_item);

        // Inflict damage on the opposing robot
        $this_item->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -90, 0, 10, $target_robot->print_name().' was hit by the '.$this_item->print_name().'!'),
            'failure' => array(0, -120, 0, -10, 'The '.$this_item->print_name().' missed&hellip;')
            ));
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'percent' => true,
            'multipliers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(0, -60, 0, 10, $target_robot->print_name().' absorbed the '.$this_item->print_name().'!'),
            'failure' => array(0, -90, 0, -10, 'The '.$this_item->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_energy * 0.10);
        $target_robot->trigger_damage($this_robot, $this_item, $energy_damage_amount);

        // Return true on success
        return true;

    }

}
?>