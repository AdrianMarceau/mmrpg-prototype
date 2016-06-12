<?
// Define a class for the items
class rpg_item {

    // Define global class variables
    public $flags;
    public $counters;
    public $values;
    public $history;

    // Define the constructor class
    public function rpg_item(){

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

        // Collect current item data from the function if available
        $this_iteminfo = isset($args[3]) ? $args[3] : array('item_id' => 0, 'item_token' => 'item');

        if (!is_array($this_iteminfo)){
            die('!is_array($this_iteminfo){ '.print_r($this_iteminfo, true)).' }';
        }

        // Now load the item data from the session or index
        $this->item_load($this_iteminfo);

        // Update the session by default
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a public function for manually loading data
    public function item_load($this_iteminfo){

        // If the item info was not an array, return false
        if (!is_array($this_iteminfo)){ return false; }
        // If the item ID was not provided, return false
        //if (!isset($this_iteminfo['item_id'])){ return false; }
        if (!isset($this_iteminfo['item_id'])){ $this_iteminfo['item_id'] = 0; }
        // If the item token was not provided, return false
        if (!isset($this_iteminfo['item_token'])){ return false; }

        // If this is a special system item, hard-code its ID, otherwise base off robot
        $temp_system_items = array('attachment-defeat');
        if (in_array($this_iteminfo['item_token'], $temp_system_items)){
            $this_iteminfo['item_id'] = $this->player_id.'000';
        }
        // Else if this is an item, tweak it's ID as well
        elseif (in_array($this_iteminfo['item_token'], $this->player->player_items)){
            $this_iteminfo['item_id'] = $this->player_id.str_pad($this_iteminfo['item_id'], 3, '0', STR_PAD_LEFT);
        }
        // Otherwise base the ID off of the robot
        elseif (!preg_match('/^'.$this->robot->robot_id.'/', $this_iteminfo['item_id'])){
            $this_iteminfo['item_id'] = $this->robot_id.str_pad($this_iteminfo['item_id'], 3, '0', STR_PAD_LEFT);
        }

        // Collect current item data from the session if available
        $this_iteminfo_backup = $this_iteminfo;
        if (isset($_SESSION['ABILITIES'][$this_iteminfo['item_id']])){
            $this_iteminfo = $_SESSION['ABILITIES'][$this_iteminfo['item_id']];
        }
        // Otherwise, collect item data from the index if not already
        elseif (!in_array($this_iteminfo['item_token'], $temp_system_items)){
            $temp_backup_id = $this_iteminfo['item_id'];
            if (empty($this_iteminfo_backup['_parsed'])){
                $this_iteminfo = self::get_index_info($this_iteminfo_backup['item_token']);
                if (empty($this_iteminfo['item_id'])){
                    exit();
                }
                $this_iteminfo = array_replace($this_iteminfo, $this_iteminfo_backup);
            }
        }

        // Define the internal item values using the provided array
        $this->flags = isset($this_iteminfo['flags']) ? $this_iteminfo['flags'] : array();
        $this->counters = isset($this_iteminfo['counters']) ? $this_iteminfo['counters'] : array();
        $this->values = isset($this_iteminfo['values']) ? $this_iteminfo['values'] : array();
        $this->history = isset($this_iteminfo['history']) ? $this_iteminfo['history'] : array();
        $this->item_id = isset($this_iteminfo['item_id']) ? $this_iteminfo['item_id'] : 0;
        $this->item_name = isset($this_iteminfo['item_name']) ? $this_iteminfo['item_name'] : 'Ability';
        $this->item_token = isset($this_iteminfo['item_token']) ? $this_iteminfo['item_token'] : 'item';
        $this->item_class = isset($this_iteminfo['item_class']) ? $this_iteminfo['item_class'] : 'master';
        $this->item_image = isset($this_iteminfo['item_image']) ? $this_iteminfo['item_image'] : $this->item_token;
        $this->item_image_size = isset($this_iteminfo['item_image_size']) ? $this_iteminfo['item_image_size'] : 40;
        $this->item_description = isset($this_iteminfo['item_description']) ? $this_iteminfo['item_description'] : '';
        $this->item_type = isset($this_iteminfo['item_type']) ? $this_iteminfo['item_type'] : '';
        $this->item_type2 = isset($this_iteminfo['item_type2']) ? $this_iteminfo['item_type2'] : '';
        $this->item_speed = isset($this_iteminfo['item_speed']) ? $this_iteminfo['item_speed'] : 1;
        $this->item_energy = isset($this_iteminfo['item_energy']) ? $this_iteminfo['item_energy'] : 4;
        $this->item_damage = isset($this_iteminfo['item_damage']) ? $this_iteminfo['item_damage'] : 0;
        $this->item_damage2 = isset($this_iteminfo['item_damage2']) ? $this_iteminfo['item_damage2'] : 0;
        $this->item_damage_percent = isset($this_iteminfo['item_damage_percent']) ? $this_iteminfo['item_damage_percent'] : false;
        $this->item_damage2_percent = isset($this_iteminfo['item_damage2_percent']) ? $this_iteminfo['item_damage2_percent'] : false;
        //$this->item_damage_modifiers = isset($this_iteminfo['item_damage_modifiers']) ? $this_iteminfo['item_damage_modifiers'] : true;
        $this->item_recovery = isset($this_iteminfo['item_recovery']) ? $this_iteminfo['item_recovery'] : 0;
        $this->item_recovery2 = isset($this_iteminfo['item_recovery2']) ? $this_iteminfo['item_recovery2'] : 0;
        $this->item_recovery_percent = isset($this_iteminfo['item_recovery_percent']) ? $this_iteminfo['item_recovery_percent'] : false;
        $this->item_recovery2_percent = isset($this_iteminfo['item_recovery2_percent']) ? $this_iteminfo['item_recovery2_percent'] : false;
        //$this->item_recovery_modifiers = isset($this_iteminfo['item_recovery_modifiers']) ? $this_iteminfo['item_recovery_modifiers'] : true;
        $this->item_accuracy = isset($this_iteminfo['item_accuracy']) ? $this_iteminfo['item_accuracy'] : 0;
        $this->item_target = isset($this_iteminfo['item_target']) ? $this_iteminfo['item_target'] : 'auto';
        $this->item_functions = isset($this_iteminfo['item_functions']) ? $this_iteminfo['item_functions'] : 'items/item.php';
        $this->item_frame = isset($this_iteminfo['item_frame']) ? $this_iteminfo['item_frame'] : 'base';
        $this->item_frame_span = isset($this_iteminfo['item_frame_span']) ? $this_iteminfo['item_frame_span'] : 1;
        $this->item_frame_animate = isset($this_iteminfo['item_frame_animate']) ? $this_iteminfo['item_frame_animate'] : array($this->item_frame);
        $this->item_frame_index = isset($this_iteminfo['item_frame_index']) ? $this_iteminfo['item_frame_index'] : array('base');
        $this->item_frame_offset = isset($this_iteminfo['item_frame_offset']) ? $this_iteminfo['item_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 1);
        $this->item_frame_styles = isset($this_iteminfo['item_frame_styles']) ? $this_iteminfo['item_frame_styles'] : '';
        $this->item_frame_classes = isset($this_iteminfo['item_frame_classes']) ? $this_iteminfo['item_frame_classes'] : '';
        $this->attachment_frame = isset($this_iteminfo['attachment_frame']) ? $this_iteminfo['attachment_frame'] : 'base';
        $this->attachment_frame_animate = isset($this_iteminfo['attachment_frame_animate']) ? $this_iteminfo['attachment_frame_animate'] : array($this->attachment_frame);
        $this->attachment_frame_index = isset($this_iteminfo['attachment_frame_index']) ? $this_iteminfo['attachment_frame_index'] : array('base');
        $this->attachment_frame_offset = isset($this_iteminfo['attachment_frame_offset']) ? $this_iteminfo['attachment_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 1);
        $this->item_results = array();
        $this->attachment_results = array();
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
        $this->item_base_name = isset($this_iteminfo['item_base_name']) ? $this_iteminfo['item_base_name'] : $this->item_name;
        $this->item_base_token = isset($this_iteminfo['item_base_token']) ? $this_iteminfo['item_base_token'] : $this->item_token;
        $this->item_base_image = isset($this_iteminfo['item_base_image']) ? $this_iteminfo['item_base_image'] : $this->item_image;
        $this->item_base_image_size = isset($this_iteminfo['item_base_image_size']) ? $this_iteminfo['item_base_image_size'] : $this->item_image_size;
        $this->item_base_description = isset($this_iteminfo['item_base_description']) ? $this_iteminfo['item_base_description'] : $this->item_description;
        $this->item_base_type = isset($this_iteminfo['item_base_type']) ? $this_iteminfo['item_base_type'] : $this->item_type;
        $this->item_base_type2 = isset($this_iteminfo['item_base_type2']) ? $this_iteminfo['item_base_type2'] : $this->item_type2;
        $this->item_base_energy = isset($this_iteminfo['item_base_energy']) ? $this_iteminfo['item_base_energy'] : $this->item_energy;
        $this->item_base_speed = isset($this_iteminfo['item_base_speed']) ? $this_iteminfo['item_base_speed'] : $this->item_speed;
        $this->item_base_damage = isset($this_iteminfo['item_base_damage']) ? $this_iteminfo['item_base_damage'] : $this->item_damage;
        $this->item_base_damage2 = isset($this_iteminfo['item_base_damage2']) ? $this_iteminfo['item_base_damage2'] : $this->item_damage2;
        $this->item_base_recovery = isset($this_iteminfo['item_base_recovery']) ? $this_iteminfo['item_base_recovery'] : $this->item_recovery;
        $this->item_base_recovery2 = isset($this_iteminfo['item_base_recovery2']) ? $this_iteminfo['item_base_recovery2'] : $this->item_recovery2;
        $this->item_base_accuracy = isset($this_iteminfo['item_base_accuracy']) ? $this_iteminfo['item_base_accuracy'] : $this->item_accuracy;
        $this->item_base_target = isset($this_iteminfo['item_base_target']) ? $this_iteminfo['item_base_target'] : $this->item_target;

        // Define a the default item results
        $this->item_results_reset();

        // Reset the item options to default
        $this->target_options_reset();
        $this->damage_options_reset();
        $this->recovery_options_reset();

        // Trigger the onload function if it exists
        $temp_target_player = $this->battle->find_target_player($this->player->player_side != 'right' ? 'right' : 'left');
        $temp_target_robot = $this->battle->find_target_robot($this->player->player_side != 'right' ? 'right' : 'left');
        $temp_function = $this->item_function_onload;
        $temp_result = $temp_function(array(
            'this_field' => &$this->battle->battle_field,
            'this_battle' => &$this->battle,
            'this_player' => &$this->player,
            'this_robot' => &$this->robot,
            'target_player' => &$temp_target_player,
            'target_robot' => &$temp_target_robot,
            'this_item' => &$this
            ));

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define public print functions for markup generation
    public function print_item_name($plural = false){
        $type_class = !empty($this->item_type) ? $this->item_type : 'none';
        if ($type_class != 'none' && !empty($this->item_type2)){ $type_class .= '_'.$this->item_type2; }
        elseif ($type_class == 'none' && !empty($this->item_type2)){ $type_class = $this->item_type2; }
        return '<span class="item_name item_type item_type_'.$type_class.'">'.$this->item_name.($plural ? 's' : '').'</span>';
    }
    //public function print_item_name(){ return '<span class="item_name">'.$this->item_name.'</span>'; }
    public function print_item_token(){ return '<span class="item_token">'.$this->item_token.'</span>'; }
    public function print_item_description(){ return '<span class="item_description">'.$this->item_description.'</span>'; }
    public function print_item_type(){ return '<span class="item_type">'.$this->item_type.'</span>'; }
    public function print_item_type2(){ return '<span class="item_type2">'.$this->item_type2.'</span>'; }
    public function print_item_speed(){ return '<span class="item_speed">'.$this->item_speed.'</span>'; }
    public function print_item_damage(){ return '<span class="item_damage">'.$this->item_damage.'</span>'; }
    public function print_item_recovery(){ return '<span class="item_recovery">'.$this->item_recovery.'</span>'; }
    public function print_item_accuracy(){ return '<span class="item_accuracy">'.$this->item_accuracy.'%</span>'; }

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
        $this->item_results = array();
        // Populate the array with defaults
        $this->item_results['total_result'] = '';
        $this->item_results['total_actions'] = 0;
        $this->item_results['total_strikes'] = 0;
        $this->item_results['total_misses'] = 0;
        $this->item_results['total_amount'] = 0;
        $this->item_results['total_overkill'] = 0;
        $this->item_results['this_result'] = '';
        $this->item_results['this_amount'] = 0;
        $this->item_results['this_overkill'] = 0;
        $this->item_results['this_text'] = '';
        $this->item_results['counter_criticals'] = 0;
        $this->item_results['counter_weaknesses'] = 0;
        $this->item_results['counter_resistances'] = 0;
        $this->item_results['counter_affinities'] = 0;
        $this->item_results['counter_immunities'] = 0;
        $this->item_results['counter_coreboosts'] = 0;
        $this->item_results['flag_critical'] = false;
        $this->item_results['flag_affinity'] = false;
        $this->item_results['flag_weakness'] = false;
        $this->item_results['flag_resistance'] = false;
        $this->item_results['flag_immunity'] = false;
        $this->item_results['flag_coreboost'] = false;
        // Update this item's data
        $this->update_session();
        // Return the resuling array
        return $this->item_results;
    }

    // Define a public function for easily resetting target options
    public function target_options_reset(){
        // Redfine the options variables as an empty array
        $this->target_options = array();
        // Populate the array with defaults
        $this->target_options['target_kind'] = 'energy';
        $this->target_options['target_frame'] = 'shoot';
        $this->target_options['item_success_frame'] = 1;
        $this->target_options['item_success_frame_span'] = 1;
        $this->target_options['item_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->target_options['item_failure_frame'] = 1;
        $this->target_options['item_failure_frame_span'] = 1;
        $this->target_options['item_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->target_options['target_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $this->target_options['target_header'] = $this->robot->robot_name.'&#39;s '.$this->item_name;
        $this->target_options['target_text'] = "{$this->robot->print_robot_name()} uses {$this->print_item_name()}!";
        // Update this item's data
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
            $this->target_options['item_success_frame'] = $target_options['success'][0];
            $this->target_options['item_success_frame_offset']['x'] = $target_options['success'][1];
            $this->target_options['item_success_frame_offset']['y'] = $target_options['success'][2];
            $this->target_options['item_success_frame_offset']['z'] = $target_options['success'][3];
            $this->target_options['target_text'] = $target_options['success'][4];
            $this->target_options['item_success_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($target_options['failure'])){
            $this->target_options['item_failure_frame'] = $target_options['failure'][0];
            $this->target_options['item_failure_frame_offset']['x'] = $target_options['failure'][1];
            $this->target_options['item_failure_frame_offset']['y'] = $target_options['failure'][2];
            $this->target_options['item_failure_frame_offset']['z'] = $target_options['failure'][3];
            $this->target_options['target_text'] = $target_options['failure'][4];
            $this->target_options['item_failure_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
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
        $this->damage_options['damage_header'] = $this->robot->robot_name.'&#39;s '.$this->item_name;
        $this->damage_options['damage_frame'] = 'damage';
        $this->damage_options['item_success_frame'] = 1;
        $this->damage_options['item_success_frame_span'] = 1;
        $this->damage_options['item_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['item_failure_frame'] = 1;
        $this->damage_options['item_failure_frame_span'] = 1;
        $this->damage_options['item_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['damage_kind'] = 'energy';
        $this->damage_options['damage_type'] = $this->item_type;
        $this->damage_options['damage_type2'] = $this->item_type2;
        $this->damage_options['damage_amount'] = $this->item_damage;
        $this->damage_options['damage_amount2'] = $this->item_damage2;
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
        $this->damage_options['success_text'] = 'The item hit!';
        $this->damage_options['failure_text'] = 'The item missed&hellip;';
        $this->damage_options['immunity_text'] = 'The item had no effect&hellip;';
        $this->damage_options['critical_text'] = 'It&#39;s a critical hit!';
        $this->damage_options['weakness_text'] = 'It&#39;s super effective!';
        $this->damage_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $this->damage_options['weakness_resistance_text'] = ''; //"It's a super effective resisted hit!';
        $this->damage_options['weakness_critical_text'] = 'It&#39;s a super effective critical hit!';
        $this->damage_options['resistance_critical_text'] = 'It&#39;s a critical hit, but not very effective&hellip;';
        // Update this item's data
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
            $this->damage_options['item_success_frame'] = $damage_options['success'][0];
            $this->damage_options['item_success_frame_offset']['x'] = $damage_options['success'][1];
            $this->damage_options['item_success_frame_offset']['y'] = $damage_options['success'][2];
            $this->damage_options['item_success_frame_offset']['z'] = $damage_options['success'][3];
            $this->damage_options['success_text'] = $damage_options['success'][4];
            $this->damage_options['item_success_frame_span'] = isset($damage_options['success'][5]) ? $damage_options['success'][5] : 1;
        }
        // Update internal variables with failure options, if set
        if (isset($damage_options['failure'])){
            $this->damage_options['item_failure_frame'] = $damage_options['failure'][0];
            $this->damage_options['item_failure_frame_offset']['x'] = $damage_options['failure'][1];
            $this->damage_options['item_failure_frame_offset']['y'] = $damage_options['failure'][2];
            $this->damage_options['item_failure_frame_offset']['z'] = $damage_options['failure'][3];
            $this->damage_options['failure_text'] = $damage_options['failure'][4];
            $this->damage_options['item_failure_frame_span'] = isset($damage_options['failure'][5]) ? $damage_options['failure'][5] : 1;
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
        $this->recovery_options['recovery_header'] = $this->robot->robot_name.'&#39;s '.$this->item_name;
        $this->recovery_options['recovery_frame'] = 'defend';
        $this->recovery_options['item_success_frame'] = 1;
        $this->recovery_options['item_success_frame_span'] = 1;
        $this->recovery_options['item_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['item_failure_frame'] = 1;
        $this->recovery_options['item_failure_frame_span'] = 1;
        $this->recovery_options['item_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['recovery_kind'] = 'energy';
        $this->recovery_options['recovery_type'] = $this->item_type;
        $this->recovery_options['recovery_type2'] = $this->item_type2;
        $this->recovery_options['recovery_amount'] = $this->item_recovery;
        $this->recovery_options['recovery_amount2'] = $this->item_recovery2;
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
        $this->recovery_options['recovery_type'] = $this->item_type;
        $this->recovery_options['recovery_type2'] = $this->item_type2;
        $this->recovery_options['success_text'] = 'The item worked!';
        $this->recovery_options['failure_text'] = 'The item failed&hellip;';
        $this->recovery_options['immunity_text'] = 'The item had no effect&hellip;';
        $this->recovery_options['critical_text'] = 'It&#39;s a lucky boost!';
        $this->recovery_options['affinity_text'] = 'It&#39;s super effective!';
        $this->recovery_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $this->recovery_options['affinity_resistance_text'] = ''; //'It&#39;s a super effective resisted hit!';
        $this->recovery_options['affinity_critical_text'] = 'It&#39;s a super effective lucky boost!';
        $this->recovery_options['resistance_critical_text'] = 'It&#39;s a lucky boost, but not very effective&hellip;';
        // Update this item's data
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
            $this->recovery_options['item_success_frame'] = $recovery_options['success'][0];
            $this->recovery_options['item_success_frame_offset']['x'] = $recovery_options['success'][1];
            $this->recovery_options['item_success_frame_offset']['y'] = $recovery_options['success'][2];
            $this->recovery_options['item_success_frame_offset']['z'] = $recovery_options['success'][3];
            $this->recovery_options['success_text'] = $recovery_options['success'][4];
            $this->recovery_options['item_success_frame_span'] = isset($recovery_options['success'][5]) ? $recovery_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($recovery_options['failure'])){
            $this->recovery_options['item_failure_frame'] = $recovery_options['failure'][0];
            $this->recovery_options['item_failure_frame_offset']['x'] = $recovery_options['failure'][1];
            $this->recovery_options['item_failure_frame_offset']['y'] = $recovery_options['failure'][2];
            $this->recovery_options['item_failure_frame_offset']['z'] = $recovery_options['failure'][3];
            $this->recovery_options['failure_text'] = $recovery_options['failure'][4];
            $this->recovery_options['item_failure_frame_span'] = isset($recovery_options['failure'][5]) ? $recovery_options['failure'][5] : 1;
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
        // Update this item's data
        $this->update_session();
        // Return the resuling array
        return $this->attachment_options;
    }


    // Define a public function for easily updating attachment options
    public function attachment_options_update($attachment_options = array()){
        // Update this item's data
        $this->update_session();
        // Return the new array
        return $this->attachment_options;
    }

    // Define a function for generating item canvas variables
    public function canvas_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the item data array and populate basic data
        $this_data['item_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
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

        // DEBUG
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_item_target\'] = '.$options['this_item_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this->item_image_size * 2);
        $this_data['item_sprite_size'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_width'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_height'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_image_width'] = ceil($this_data['item_scale'] * $zoom_size * 10);
        $this_data['item_image_height'] = ceil($this_data['item_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this robot
        $canvas_offset_data = $this->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size']);
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
        if ($this_data['data_sticky'] == true){

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

        // DEBUG
        //$this_data['item_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));


        // Generate the final markup for the canvas item
        ob_start();

            // Display the item's battle sprite
            echo '<div data-item-id="'.$this_data['item_id_token'].'" data-robot-id="'.$robot_data['robot_id_token'].'" class="'.($this_data['item_markup_class'].$this_data['item_frame_classes']).'" style="'.($this_data['item_markup_style'].$this_data['item_frame_styles']).'" '.(!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['item_sprite_size'].'" data-direction="'.$this_data['item_direction'].'" data-frame="'.$this_data['item_frame'].'" data-animate="'.$this_data['item_frame_animate'].'" data-position="'.$this_data['item_position'].'" data-status="'.$this_data['item_status'].'" data-scale="'.$this_data['item_scale'].'">'.$this_data['item_token'].'</div>';

        // Collect the generated item markup
        $this_data['item_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating item console variables
    public function console_markup($options, $player_data, $robot_data){

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

    // Define a public function for collecting index data from the database
    public static function get_index_info($item_token){
        global $db;
        // Collect the data from the index or the database if necessary
        if (!is_string($item_token)){ return false; }
        $item_info = $db->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_token IN ('{$item_token}');", 'item_token');
        if (!empty($item_info)){ $item_info = self::parse_index_info($item_info[$item_token]); }
        else { $item_info = array(); }
        return $item_info;
    }
    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($item_info){
        global $db;

        // Return false if empty
        if (empty($item_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($item_info['_parsed'])){ return $item_info; }
        else { $item_info['_parsed'] = true; }

        // Explode the base and animation indexes into an array
        $temp_field_names = array('item_frame_animate', 'attachment_frame_animate', 'item_frame_index', 'attachment_frame_index', 'item_frame_offset', 'attachment_frame_offset');
        foreach ($temp_field_names AS $field_name){
            if (!empty($item_info[$field_name])){ $item_info[$field_name] = json_decode($item_info[$field_name], true); }
            else { $item_info[$field_name] = array(); }
            /*
            if (!empty($item_info[$field_name])){
                $item_info[$field_name] = strstr($item_info[$field_name], ',') ? explode(',', $item_info[$field_name]) : array($item_info[$field_name]);
                foreach ($item_info[$field_name] AS $key => $string){ $item_info[$field_name][$key] = trim($string, '[]'); }
            } else {
                $item_info[$field_name] = array();
            }
            */
        }

        // Explode the base and animation frame offsets into an array
        /*
        $temp_field_names = array('item_frame_offset', 'attachment_frame_offset');
        foreach ($temp_field_names AS $field_name){
            if (!empty($item_info[$field_name])){
                $item_info[$field_name] = strstr($item_info[$field_name], ',') ? explode(',', $item_info[$field_name]) : array($item_info[$field_name]);
                $temp_array = array();
                foreach ($item_info[$field_name] AS $key => $string){
                    list($field, $value) = explode(':', trim($string, '[]'));
                    $temp_array[$field] = (int)($value);
                }
                $item_info[$field_name][$key] = $temp_array;
            } else {
                $item_info[$field_name] = array();
            }
        }
        */

        // Update the static index with this item's index info
        //$db->INDEX['ABILITIES'][$item_info['item_token']] = $item_info;

        // Return the parsed item info
        return $item_info;
    }




    // Define a static function for printing out the item's title markup
    public static function print_editor_title_markup($robot_info, $item_info, $print_options = array()){

        // Require the function file
        $temp_item_title = '';
        // Pull in global variables
        global $mmrpg_index;
        $session_token = mmrpg_game_token();

        if (empty($robot_info)){ return false; }
        if (empty($item_info)){ return false; }

        $print_options['show_accuracy'] = isset($print_options['show_accuracy']) ? $print_options['show_accuracy'] : true;
        $print_options['show_quantity'] = isset($print_options['show_quantity']) ? $print_options['show_quantity'] : true;

        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_item_token = $item_info['item_token'];
        $temp_item_type = !empty($item_info['item_type']) ? $mmrpg_index['types'][$item_info['item_type']] : false;
        $temp_item_type2 = !empty($item_info['item_type2']) ? $mmrpg_index['types'][$item_info['item_type2']] : false;
        $temp_item_energy = 0;
        $temp_item_damage = !empty($item_info['item_damage']) ? $item_info['item_damage'] : 0;
        $temp_item_damage2 = !empty($item_info['item_damage2']) ? $item_info['item_damage2'] : 0;
        $temp_item_recovery = !empty($item_info['item_recovery']) ? $item_info['item_recovery'] : 0;
        $temp_item_recovery2 = !empty($item_info['item_recovery2']) ? $item_info['item_recovery2'] : 0;
        $temp_item_target = !empty($item_info['item_target']) ? $item_info['item_target'] : 'auto';

        $temp_item_title = $item_info['item_name'];
        if (!empty($temp_item_type)){ $temp_item_title .= ' ('.$temp_item_type['type_name'].' Type)'; }
        if (!empty($temp_item_type2)){ $temp_item_title = str_replace('Type', '/ '.$temp_item_type2['type_name'].' Type', $temp_item_title); }

        if ($item_info['item_class'] != 'item'){
            if (!empty($item_info['item_damage']) || !empty($item_info['item_recovery'])){ $temp_item_title .= '  // '; }
            if (!empty($item_info['item_damage'])){ $temp_item_title .= $item_info['item_damage'].' Damage'; }
            if (!empty($item_info['item_recovery'])){ $temp_item_title .= $item_info['item_recovery'].' Recovery'; }
        }

        //if (empty($item_info['item_damage']) && empty($item_info['item_recovery'])){ $temp_item_title .= 'Special'; }

        // If show accuracy or quantity
        if (($item_info['item_class'] != 'item' && $print_options['show_accuracy'])
            || ($item_info['item_class'] == 'item' && $print_options['show_quantity'])){

            if ($item_info['item_class'] != 'item' && !empty($item_info['item_accuracy'])){ $temp_item_title .= ' | '.$item_info['item_accuracy'].'% Accuracy'; }
            elseif ($item_info['item_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_item_token])){ $temp_item_title .= ' | '.($_SESSION[$session_token]['values']['battle_items'][$temp_item_token] == 1 ? '1 Unit' : $_SESSION[$session_token]['values']['battle_items'][$temp_item_token].' Units'); }
            elseif ($item_info['item_class'] == 'item' ){ $temp_item_title .= ' | 0 Units'; }

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
        global $mmrpg_index;
        $session_token = mmrpg_game_token();

        // Require the function file
        $this_option_markup = '';

        // Generate the item option markup
        if (empty($robot_info)){ return false; }
        if (empty($item_info)){ return false; }
        //$item_info = self::get_index_info($temp_item_token);
        $temp_robot_token = $robot_info['robot_token'];
        $temp_item_token = $item_info['item_token'];
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_item_token.'/'."\nrobot_items = ".array_keys($robot_info['robot_items'])."\nrobot_index_items = ".array_keys($robot_info['robot_index_items']));  }
        $robot_item_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_item_type = !empty($item_info['item_type']) ? $mmrpg_index['types'][$item_info['item_type']] : false;
        $temp_item_type2 = !empty($item_info['item_type2']) ? $mmrpg_index['types'][$item_info['item_type2']] : false;
        $temp_item_energy = 0;
        $temp_type_array = array();
        $temp_incompatible = false;
        $temp_index_items = !empty($robot_info['robot_index_items']) ? $robot_info['robot_index_items'] : array();
        $temp_current_items = !empty($robot_info['robot_items']) ? array_keys($robot_info['robot_items']) : array();
        $temp_compatible_items = array_merge($temp_index_items, $temp_current_items);
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_item_token.'/'."\nindex_items = ".implode(',', $temp_index_items)."\ncurrent_items = ".implode(',', $temp_current_items)."\ncompatible_items = ".implode(',', $temp_compatible_items));  }
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
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_item_token.'/'.($temp_incompatible ? 'incompatible' : 'compatible'));  }
        if ($temp_incompatible == true){ return false; }
        $temp_item_label = $item_info['item_name'];
        $temp_item_title = self::print_editor_title_markup($robot_info, $item_info);
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
        $_SESSION['ABILITIES'][$this->robot->robot_id][$this->item_id] = $this_data;
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
            'item_name' => $this->item_name,
            'item_token' => $this->item_token,
            'item_class' => $this->item_class,
            'item_image' => $this->item_image,
            'item_image_size' => $this->item_image_size,
            'item_description' => $this->item_description,
            'item_type' => $this->item_type,
            'item_type2' => $this->item_type2,
            'item_energy' => $this->item_energy,
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
            'attachment_results' => $this->attachment_results,
            'item_options' => array(), //$this->item_options,
            'target_options' => array(), //$this->target_options,
            'damage_options' => array(), //$this->damage_options,
            'recovery_options' => array(), //$this->recovery_options,
            'attachment_options' => array(), //$this->attachment_options,
            'item_base_name' => $this->item_base_name,
            'item_base_token' => $this->item_base_token,
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
            'item_frame' => $this->item_frame,
            'item_frame_span' => $this->item_frame_span,
            'item_frame_index' => $this->item_frame_index,
            'item_frame_animate' => $this->item_frame_animate,
            'item_frame_offset' => $this->item_frame_offset,
            'item_frame_classes' => $this->item_frame_classes,
            'item_frame_styles' => $this->item_frame_styles,
            'attachment_frame' => $this->attachment_frame,
            'attachment_frame_animate' => $this->attachment_frame_animate,
            'attachment_frame_offset' => $this->attachment_frame_offset,
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
        global $db;

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
        if (preg_match('/^item-score-ball-(red|blue|green|purple)$/i', $item_info['item_token'])){ $item_info['item_type_special'] = 'bonus'; }
        elseif (preg_match('/^item-super-(pellet|capsule)$/i', $item_info['item_token'])){ $item_info['item_type_special'] = 'multi'; }

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
        <div class="database_container database_<?= $item_info['item_class'] == 'item' ? 'item' : 'item' ?>_container" data-token="<?=$item_info['item_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

            <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?=$item_info['item_token']?>">&nbsp;</a>
            <? endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $item_info['item_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

                <? if($print_options['show_icon']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_icon']): ?>
                            <? if($print_options['show_key'] !== false): ?>
                                <div class="icon item_type <?= $item_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                            <? endif; ?>
                            <? if ($item_image_token != 'item'){ ?>
                                <div class="icon item_type <?= $item_header_types ?>"><div style="background-image: url(images/items/<?= $item_image_token ?>/icon_right_<?= $item_image_size_text ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_item sprite_40x40 sprite_40x40_icon sprite_size_<?= $item_image_size_text ?> sprite_size_<?= $item_image_size_text ?>_icon item_status_active item_position_active"><?=$item_info['item_name']?>'s Mugshot</div></div>
                            <? } else { ?>
                                <div class="icon item_type <?= $item_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_item sprite_40x40 sprite_40x40_icon sprite_size_<?= $item_image_size_text ?> sprite_size_<?= $item_image_size_text ?>_icon item_status_active item_position_active">No Image</div></div>
                            <? } ?>
                        <? endif; ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $item_header_types ?> <?= (!$print_options['show_icon']) ? 'noicon' : 'hasicon' ?>">
                        <? if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="<?= preg_match('/^item-/', $item_info['item_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $item_info['item_token']).'/' : 'database/items/'.$item_info['item_token'].'/' ?>"><?= $item_info['item_name'] ?></a>
                        <? else: ?>
                            <?= $item_info['item_name'] ?>&#39;s Data
                        <? endif; ?>
                        <? if (!empty($item_info['item_type_special'])){ ?>
                            <div class="header_core item_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($item_info['item_type_special']) ?> Type</div>
                        <? } elseif (!empty($item_info['item_type']) && !empty($item_info['item_type2'])){ ?>
                            <div class="header_core item_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($item_info['item_type']).' / '.ucfirst($item_info['item_type2']) ?> Type</div>
                        <? } elseif (!empty($item_info['item_type'])){ ?>
                            <div class="header_core item_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($item_info['item_type']) ?> Type</div>
                        <? } else { ?>
                            <div class="header_core item_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Neutral Type</div>
                        <? } ?>
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
                                        <span class="item_type item_type_"><?=$item_info['item_name']?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Type :</label>
                                        <? if($print_options['layout_style'] != 'event'): ?>
                                            <?
                                            if (!empty($item_info['item_type_special'])){
                                                echo '<a href="'.((preg_match('/^item-/', $item_info['item_token']) ? 'database/items/' : 'database/items/').$item_info['item_type_special'].'/').'" class="item_type '.$item_header_types.'">'.ucfirst($item_info['item_type_special']).'</a>';
                                            }
                                            elseif (!empty($item_info['item_type'])){
                                                $temp_string = array();
                                                $item_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                                                $temp_string[] = '<a href="'.((preg_match('/^item-/', $item_info['item_token']) ? 'database/items/' : 'database/items/').$item_type.'/').'" class="item_type item_type_'.$item_type.'">'.$mmrpg_index['types'][$item_type]['type_name'].'</a>';
                                                if (!empty($item_info['item_type2'])){
                                                    $item_type2 = !empty($item_info['item_type2']) ? $item_info['item_type2'] : 'none';
                                                    $temp_string[] = '<a href="'.((preg_match('/^item-/', $item_info['item_token']) ? 'database/items/' : 'database/items/').$item_type2.'/').'" class="item_type item_type_'.$item_type2.'">'.$mmrpg_index['types'][$item_type2]['type_name'].'</a>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<a href="'.((preg_match('/^item-/', $item_info['item_token']) ? 'database/items/' : 'database/items/').'none/').'" class="item_type item_type_none">Neutral</a>';
                                            }
                                            ?>
                                        <? else: ?>
                                            <?
                                            if (!empty($item_info['item_type_special'])){
                                                echo '<span class="item_type '.$item_header_types.'">'.ucfirst($item_info['item_type_special']).'</span>';
                                            }
                                            elseif (!empty($item_info['item_type'])){
                                                $temp_string = array();
                                                $item_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                                                $temp_string[] = '<span class="item_type item_type_'.$item_type.'">'.$mmrpg_index['types'][$item_type]['type_name'].'</span>';
                                                if (!empty($item_info['item_type2'])){
                                                    $item_type2 = !empty($item_info['item_type2']) ? $item_info['item_type2'] : 'none';
                                                    $temp_string[] = '<span class="item_type item_type_'.$item_type2.'">'.$mmrpg_index['types'][$item_type2]['type_name'].'</span>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<span class="item_type item_type_none">Neutral</span>';
                                            }
                                            ?>
                                        <? endif; ?>
                                    </td>
                                </tr>
                                <? if($item_info['item_class'] != 'item'): ?>

                                    <? if($item_image_token != 'item'): ?>

                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Power :</label>
                                                <? if(!empty($item_info['item_damage']) || !empty($item_info['item_recovery'])): ?>
                                                    <? if(!empty($item_info['item_damage'])){ ?><span class="item_stat"><?= $item_info['item_damage'].(!empty($item_info['item_damage_percent']) ? '%' : '') ?> Damage</span><? } ?>
                                                    <? if(!empty($item_info['item_recovery'])){ ?><span class="item_stat"><?= $item_info['item_recovery'].(!empty($item_info['item_recovery_percent']) ? '%' : '') ?> Recovery</span><? } ?>
                                                <? else: ?>
                                                    <span class="item_stat">-</span>
                                                <? endif; ?>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Accuracy :</label>
                                                <span class="item_stat"><?= $item_info['item_accuracy'].'%' ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Energy :</label>
                                                <span class="item_stat"><?= !empty($item_info['item_energy']) ? $item_info['item_energy'] : '-' ?></span>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Speed :</label>
                                                <span class="item_stat"><?= !empty($item_info['item_speed']) ? $item_info['item_speed'] : '1' ?></span>
                                            </td>
                                        </tr>

                                    <? else: ?>

                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Power :</label>
                                                <span class="item_stat">-</span>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Accuracy :</label>
                                                <span class="item_stat">-</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  class="right">
                                                <label style="display: block; float: left;">Energy :</label>
                                                <span class="item_stat">-</span>
                                            </td>
                                            <td class="center">&nbsp;</td>
                                            <td class="right">
                                                <label style="display: block; float: left;">Speed :</label>
                                                <span class="item_stat">-</span>
                                            </td>
                                        </tr>

                                    <? endif; ?>

                                <? endif; ?>
                            </tbody>
                        </table>
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Description :</label>
                                        <div class="description_container"><?
                                        // Define the search/replace pairs for the description
                                        $temp_find = array('{DAMAGE}', '{RECOVERY}', '{DAMAGE2}', '{RECOVERY2}', '{}');
                                        $temp_replace = array(
                                            (!empty($item_info['item_damage']) ? $item_info['item_damage'] : 0), // {DAMAGE}
                                            (!empty($item_info['item_recovery']) ? $item_info['item_recovery'] : 0), // {RECOVERY}
                                            (!empty($item_info['item_damage2']) ? $item_info['item_damage2'] : 0), // {DAMAGE2}
                                            (!empty($item_info['item_recovery2']) ? $item_info['item_recovery2'] : 0), // {RECOVERY2}
                                            '' // {}
                                            );
                                        echo !empty($item_info['item_description']) ? str_replace($temp_find, $temp_replace, $item_info['item_description']) : '&hellip;'
                                        ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_sprites'] && (!isset($item_info['item_image_sheets']) || $item_info['item_image_sheets'] !== 0) && $item_image_token != 'item' ): ?>

                    <h2 class="header header_full <?= $item_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?=$item_info['item_name']?>&#39;s Sprites
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 10px; min-height: auto;">
                        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                            <?
                            // Show the item mugshot sprite
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_title = $item_sprite_title.' | Icon Sprite '.ucfirst($temp_direction);
                                $temp_label = 'Icon '.ucfirst(substr($temp_direction, 0, 1));
                                echo '<div style="'.($item_sprite_size <= 80 ? 'padding-top: 20px; ' : '').'float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$item_sprite_size.'px; height: '.$item_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: 0;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/items/'.$item_image_token.'/icon_'.$temp_direction.'_'.$item_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                            // Loop through the different frames and print out the sprite sheets
                            $temp_sheet_number = !empty($item_info['item_image_sheets']) ? $item_info['item_image_sheets'] : 1;
                            for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
                                foreach ($item_sprite_frames AS $this_key => $this_frame){
                                    $margin_left = ceil((0 - $this_key) * $item_sprite_size);
                                    $frame_relative = $this_frame;
                                    if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($item_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                                    $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                                    foreach (array('right', 'left') AS $temp_direction){
                                        $temp_title = $item_sprite_title.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                        $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                        $image_token = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
                                        if ($temp_sheet > 1){ $image_token .= '-'.$temp_sheet; }
                                        echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$item_sprite_size.'px; height: '.$item_sprite_size.'px; overflow: hidden;">';
                                            echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/items/'.$image_token.'/sprite_'.$temp_direction.'_'.$item_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                            echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                        echo '</div>';
                                    }


                                }
                            }
                            ?>
                        </div>
                        <?
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

                <? endif; ?>

                <? if($print_options['show_robots'] && $item_info['item_class'] != 'item'): ?>

                    <h2 class="header header_full <?= $item_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?=$item_info['item_name']?>&#39;s Robots
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
                                        <?
                                        $item_type_one = !empty($item_info['item_type']) ? $item_info['item_type'] : false;
                                        $item_type_two = !empty($item_info['item_type2']) ? $item_info['item_type2'] : false;
                                        $item_robot_rewards = array();
                                        $item_robot_rewards_level = array();
                                        $item_robot_rewards_core = array();
                                        $item_robot_rewards_player = array();

                                        // Loop through and remove any robots that do not learn the item
                                        foreach ($mmrpg_database_robots AS $robot_token => $robot_info){

                                            // Define the match flah to prevent doubling up
                                            $temp_match_flag = false;

                                            // Loop through this robot's item rewards one by one
                                            foreach ($robot_info['robot_rewards']['items'] AS $temp_info){
                                                // If the temp info's type token matches this item
                                                if ($temp_info['token'] == $item_info['item_token']){
                                                    // Add this item to the rewards list
                                                    $item_robot_rewards_level[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => $temp_info['level']));
                                                    $temp_match_flag = true;
                                                    break;
                                                }
                                            }

                                            // If a type match was found, continue
                                            if ($temp_match_flag){ continue; }

                                            // If this item's type matches the robot's first
                                            if (!empty($robot_info['robot_core']) && ($robot_info['robot_core'] == $item_type_one || $robot_info['robot_core'] == $item_type_two)){
                                                // Add this item to the rewards list
                                                $item_robot_rewards_core[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'core'));
                                                continue;
                                            }

                                            // If this item's type matches the robot's second
                                            if (!empty($robot_info['robot_core2']) && ($robot_info['robot_core2'] == $item_type_one || $robot_info['robot_core2'] == $item_type_two)){
                                                // Add this item to the rewards list
                                                $item_robot_rewards_core[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'core'));
                                                continue;
                                            }

                                            // If a type match was found, continue
                                            if ($temp_match_flag){ continue; }

                                            // If this item's in the robot's list of player-only items
                                            if (
                                                (!empty($robot_info['robot_items']) && in_array($item_info['item_token'], $robot_info['robot_items'])) ||
                                                (!empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy') ||
                                                (!empty($robot_info['robot_core2']) && $robot_info['robot_core2'] == 'copy')
                                                ){
                                                // Add this item to the rewards list
                                                $item_robot_rewards_player[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'player'));
                                                continue;
                                            }

                                            // If a type match was found, continue
                                            if ($temp_match_flag){ continue; }

                                        }

                                        // Combine the arrays together into one
                                        $item_robot_rewards = array_merge($item_robot_rewards_level, $item_robot_rewards_core, $item_robot_rewards_player);

                                        // Loop through the collected robots if there are any
                                        if (!empty($item_robot_rewards)){
                                            $temp_string = array();
                                            $robot_key = 0;
                                            $robot_method_key = 0;
                                            $robot_method = '';
                                            $temp_global_items = array(
                                                'light-buster', 'wily-buster', 'cossack-buster',
                                                'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost',
                                                'energy-break', 'attack-break', 'defense-break', 'speed-break',
                                                'energy-swap', 'attack-swap', 'defense-swap', 'speed-swap',
                                                'repair-mode', 'attack-mode', 'defense-mode', 'speed-mode',
                                                'field-support', 'mecha-support'
                                                );
                                            foreach ($item_robot_rewards AS $this_info){
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
                                                if (!empty($this_robot['robot_core'])){ $this_robot_title_html .= '<span class="type">'.ucwords($this_robot['robot_core'].(!empty($this_robot['robot_core2']) ? ' / '.$this_robot['robot_core2'] : '')).' Core</span>'; }
                                                else { $this_robot_title_html .= '<span class="type">Neutral Core</span>'; }
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
                                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_robot_sprite_path)){ $this_robot_image = 'robot'; $this_robot_sprite_path = 'images/robots/robot/mug_left_40x40.png'; }
                                                $this_robot_sprite_html = '<span class="mug"><img class="size_'.$this_robot_sprite_size.'x'.$this_robot_sprite_size.'" src="'.$this_robot_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_robot_name.' Mug" /></span>';
                                                $this_robot_title_html = '<span class="label">'.$this_robot_title_html.'</span>';
                                                //$this_robot_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_robot_title_html;
                                                if ($robot_method != $this_robot_method){
                                                    $temp_separator = '<div class="robot_separator">'.$this_robot_method_text.'</div>';
                                                    $temp_string[] = $temp_separator;
                                                    $robot_method = $this_robot_method;
                                                    $robot_method_key++;
                                                    // Print out the disclaimer if a global item
                                                    if ($this_robot_method == 'level' && $item_info['item_token'] == 'buster-shot'){
                                                        $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$item_info['item_name'].' is known by <em>all</em> robot masters from the start!</div>';
                                                    } elseif ($this_robot_method != 'level' && in_array($item_info['item_token'], $temp_global_items)){
                                                        $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$item_info['item_name'].' can be equipped by <em>any</em> robot master!</div>';
                                                    }
                                                }
                                                // If this is a global item, don't bother showing EVERY compatible robot
                                                if ($this_robot_method == 'level' && $item_info['item_token'] == 'buster-shot' || $this_robot_method != 'level' && in_array($item_info['item_token'], $temp_global_items)){ continue; }
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
                                            echo '<span class="robot_item robot_type_none"><span class="chrome">Neutral</span></span>';
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if ($print_options['show_records'] && $item_info['item_class'] == 'master'): ?>

                  <h2 id="records" class="header header_full <?= $item_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                    <?= $item_info['item_name'] ?>&#39;s Records
                  </h2>
                  <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
                    <?

                    // Collect the database records for this item
                    global $db;
                    $temp_item_records = array('item_unlocked' => 0, 'item_equipped');
                    $temp_record_query = "SELECT
                        COUNT(*) AS unlock_count,
                        SUM(ROUND((
                        LENGTH(saves.save_values_battle_settings)
                        - LENGTH(REPLACE(saves.save_values_battle_settings, '\"{$item_info['item_token']}\"', ''))
                        ) / LENGTH('\"{$item_info['item_token']}\"')
                        )) AS equip_count
                        FROM mmrpg_saves AS saves
                        LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
                        LEFT JOIN mmrpg_leaderboard AS points ON points.user_id = saves.user_id
                        WHERE
                        saves.save_values_battle_items LIKE '%\"{$item_info['item_token']}\"%'
                        AND points.board_points <> 0
                        AND users.user_id <> 0
                        ;";
                    $temp_record_values = $db->get_array($temp_record_query);
                    if (!empty($temp_record_values)){
                        $temp_item_records['item_unlocked'] = $temp_record_values['unlock_count'];
                        $temp_item_records['item_equipped'] = $temp_record_values['equip_count'];
                    }

                    ?>
                    <table class="full" style="margin: 5px auto 10px;">
                      <colgroup>
                        <col width="100%" />
                      </colgroup>
                      <tbody>
                          <tr>
                            <td class="right">
                              <label style="display: block; float: left;">Unlocked By : </label>
                              <span class="item_quote"><?= $temp_item_records['item_unlocked'] == 1 ? '1 Player' : number_format($temp_item_records['item_unlocked'], 0, '.', ',').' Players' ?></span>
                            </td>
                          </tr>
                          <tr>
                            <td class="right">
                              <label style="display: block; float: left;">Equipped To : </label>
                              <span class="item_quote"><?= $temp_item_records['item_equipped'] == 1 ? '1 Robot' : number_format($temp_item_records['item_equipped'], 0, '.', ',').' Robots' ?></span>
                            </td>
                          </tr>
                      </tbody>
                    </table>
                  </div>

                <? endif; ?>

                <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= preg_match('/^item-/', $item_info['item_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $item_info['item_token']).'/' : 'database/items/'.$item_info['item_token'].'/' ?>" rel="permalink">+ Permalink</a>

                <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= preg_match('/^item-/', $item_info['item_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $item_info['item_token']).'/' : 'database/items/'.$item_info['item_token'].'/' ?>" rel="permalink">+ View More</a>

                <? endif; ?>

            </div>
        </div>
        <?
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;
    }

    // Define a static function to use as the common action for all item-core-___ items
    public static function item_function_core($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_item->target_options_update(array(
            'frame' => 'throw',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 85, 35, 10, $this_robot->print_robot_name().' thows a '.$this_item->print_item_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_item);

        // Inflict damage on the opposing robot
        $this_item->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => true,
            'frame' => 'damage',
            'kickback' => array(10, 5, 0),
            'success' => array(0, 10, 0, 10, 'The '.$this_item->print_item_name().' damaged the target!'),
            'failure' => array(0, -30, 0, -10, 'The '.$this_item->print_item_name().' missed&hellip;')
            ));
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => true,
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 10, 0, 10, 'The '.$this_item->print_item_name().' recovered the target!'),
            'failure' => array(0, -30, 0, -10, 'The '.$this_item->print_item_name().' missed&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_item->item_damage / 100));
        $trigger_options = array('apply_modifiers' => true, 'apply_type_modifiers' => true, 'apply_core_modifiers' => false, 'apply_field_modifiers' => true, 'apply_stat_modifiers' => false, 'apply_position_modifiers' => false, 'apply_starforce_modifiers' => false);
        $target_robot->trigger_damage($this_robot, $this_item, $energy_damage_amount, false, $trigger_options);

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all stat pellet and capsule items
    public static function item_function_stat_booster($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self and print item use text
        $this_item->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 40, -2, 99,
                $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
                $target_robot->print_robot_name().' is given the '.$this_item->print_item_name().'!'
                )
            ));
        $target_robot->trigger_target($target_robot, $this_item);

        // Define the various object words used for each boost type
        $stat_boost_subjects = array('attack' => 'weapons', 'defense' => 'shields', 'speed' => 'mobility');
        $stat_boost_verbs = array('weapons' => 'were', 'shields' => 'were', 'mobility' => 'was');

        // Define the various effect words used for each item size
        if (strstr($this_item->item_token, 'pellet')){ $boost_effect_word = 'a bit'; }
        elseif (strstr($this_item->item_token, 'capsule')){ $boost_effect_word = 'a lot'; }

        // Define the stat(s) this item will boost (super items boost all)
        $stat_boost_tokens = array();
        if (strstr($this_item->item_token, 'super')){ $stat_boost_tokens = array('attack', 'defense', 'speed'); }
        else { $stat_boost_tokens[] = $this_item->item_type; }

        // Loop through each stat boost token and raise it
        foreach ($stat_boost_tokens AS $stat_token){

            // Collect the object word for this stat type
            $stat_name = ucfirst($stat_token);
            $stat_subject = $stat_boost_subjects[$stat_token];
            $stat_verb = $stat_boost_verbs[$stat_subject];
            $stat_base_prop = 'robot_base_'.$stat_token;
            $stat_max_prop = 'robot_max_'.$stat_token;

            // Increase this robot's in-battle stat
            $this_item->recovery_options_update(array(
                'kind' => $stat_token,
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s '.$stat_subject.' powered up '.$boost_effect_word.'! '.rpg_battle::random_positive_word()),
                'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s '.$stat_subject.''.$stat_verb.' not affected&hellip; '.rpg_battle::random_negative_word())
                ));
            $stat_recovery_amount = ceil($target_robot->$stat_base_prop * ($this_item->item_recovery / 100));
            $target_robot->trigger_recovery($target_robot, $this_item, $stat_recovery_amount);

            // Only update the session of the item was successful
            if ($this_item->item_results['this_result'] == 'success' && $this_item->item_results['total_amount'] > 0){

                // Create the stat boost variable if it doesn't already exist in the session
                if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_'.$stat_token])){
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_'.$stat_token] = 0;
                }

                // Collect this robot's stat calculations
                $robot_info = rpg_robot::get_index_info($target_robot->robot_token);
                $robot_stats = rpg_robot::calculate_stat_values(
                    $target_robot->robot_level,
                    $robot_info,
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]
                    );

                // If this robot is not already over their stat limit, increment pending boosts
                if ($target_player->player_side == 'left' && $robot_stats[$stat_token]['bonus'] < $robot_stats[$stat_token]['bonus_max']){

                    // Calculate the actual amount to permanently boost in case it goes over max
                    $stat_boost_amount = $this_item->item_results['total_amount'];
                    if (($robot_stats[$stat_token]['bonus'] + $stat_boost_amount) > $robot_stats[$stat_token]['bonus_max']){
                        $stat_boost_amount = $robot_stats[$stat_token]['bonus_max'] - $robot_stats[$stat_token]['bonus'];
                    }

                    // Only update session variables if the boost is not empty
                    if (!empty($stat_boost_amount)){

                        // Update the session variables with the incremented stat boost
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_'.$stat_token] += $stat_boost_amount;
                        $target_robot->$stat_base_prop += $stat_boost_amount;
                        $target_robot->update_session();

                        // Recalculate robot stats with new values
                        $robot_stats = rpg_robot::calculate_stat_values(
                            $target_robot->robot_level,
                            $robot_info,
                            $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]
                            );

                        // Check if this robot has now reached max stats
                        if ($robot_stats[$stat_token]['bonus'] >= $robot_stats[$stat_token]['bonus_max']){
                            // Print the success message for reaching max stats for this robot
                            $target_robot->robot_frame = 'victory';
                            $target_robot->update_session();
                            $this_battle->events_create($target_robot, false,
                                "{$target_robot->robot_name}'s {$stat_name} Stat",
                                $target_robot->print_robot_name().'\'s '.$stat_token.' stat bonuses have been raised to the max of '.
                                '<span class="robot_type robot_type_'.$stat_token.'">'.$robot_stats[$stat_token]['max'].' &#9733;</span>!<br />'.
                                'Congratulations and '.lcfirst(rpg_battle::random_victory_quote()).' '
                                );
                            $target_robot->robot_frame = 'base';
                            $target_robot->update_session();
                        }

                    }

                }

            }


        }

        // Return true on success
        return true;

    }


    // Define a static function to use as the common action for all forward attack type items
    public static function item_function_forward_attack($objects, $target_options, $damage_options, $recovery_options, $effect_options = array()){

        // Define defaults for undefined target options
        if (!isset($target_options['stat_kind'])){ $target_options['stat_kind'] = 'energy'; }
        if (!isset($target_options['robot_frame'])){ $target_options['robot_frame'] = 'shoot'; }
        if (!isset($target_options['robot_kickback'])){ $target_options['robot_kickback'] = array(0, 0, 0); }
        if (!isset($target_options['item_frame'])){ $target_options['item_frame'] = 0; }
        if (!isset($target_options['item_offset'])){ $target_options['item_offset'] = array(110, 0, 10); }
        if (!isset($target_options['item_text'])){ $target_options['item_text'] = '{this_robot_name} uses the {this_item_name}!'; }

        // Define defaults for undefined damage options
        if (!isset($damage_options['robot_frame'])){ $damage_options['robot_frame'] = 'damage'; }
        if (!isset($damage_options['robot_kickback'])){ $damage_options['robot_kickback'] = array(10, 0, 0); }
        if (!isset($damage_options['item_sucess_frame'])){ $damage_options['item_sucess_frame'] = 4; }
        if (!isset($damage_options['item_success_offset'])){ $damage_options['item_success_offset'] = array(-90, 0, 10); }
        if (!isset($damage_options['item_success_text'])){ $damage_options['item_success_text'] = 'The {this_item_name} hit the target!'; }
        if (!isset($damage_options['item_failure_frame'])){ $damage_options['item_failure_frame'] = 4; }
        if (!isset($damage_options['item_failure_offset'])){ $damage_options['item_failure_offset'] = array(-100, 0, -10); }
        if (!isset($damage_options['item_failure_text'])){ $damage_options['item_failure_text'] = 'The {this_item_name} missed...'; }

        // Define defaults for undefined recovery options
        if (!isset($recovery_options['robot_frame'])){ $recovery_options['robot_frame'] = 'taunt'; }
        if (!isset($recovery_options['robot_kickback'])){ $recovery_options['robot_kickback'] = array(0, 0, 0); }
        if (!isset($recovery_options['item_sucess_frame'])){ $recovery_options['item_sucess_frame'] = 4; }
        if (!isset($recovery_options['item_success_offset'])){ $recovery_options['item_success_offset'] = array(-45, 0, 10); }
        if (!isset($recovery_options['item_success_text'])){ $recovery_options['item_success_text'] = 'The {this_item_name} was absorbed by the target!'; }
        if (!isset($recovery_options['item_failure_frame'])){ $recovery_options['item_failure_frame'] = 4; }
        if (!isset($recovery_options['item_failure_offset'])){ $recovery_options['item_failure_offset'] = array(-100, 0, -10); }
        if (!isset($recovery_options['item_failure_text'])){ $recovery_options['item_failure_text'] = 'The {this_item_name} had no effect on the target...'; }

        // Define defaults for undefined effect options
        if (!isset($effect_options['stat_kind'])){ $effect_options = false; }
        else {
            if (!isset($effect_options['damage_text'])){ $effect_options['damage_text'] = '{this_robot_name}\'s stats were damaged!'; }
            if (!isset($effect_options['recovery_text'])){ $effect_options['recovery_text'] = '{this_robot_name}\'s stats improved!'; }
            if (!isset($effect_options['effect_chance'])){ $effect_options['effect_chance'] = 50; }
            if (!isset($effect_options['effect_target'])){ $effect_options['effect_target'] = 'target'; }
        }

        // Extract all objects into the current scope
        extract($objects);

        // Define Search and replace object strings for replacing
        $search_replace = array();
        $search_replace['this_player_name'] = $this_player->print_player_name();
        $search_replace['this_robot_name'] = $this_robot->print_robot_name();
        $search_replace['target_player_name'] = $target_player->print_player_name();
        $search_replace['target_robot_name'] = $target_robot->print_robot_name();
        $search_replace['this_item_name'] = $this_item->print_item_name();

        // Run the obtion arrays through the parsing function
        $target_options = self::parse_string_variables($search_replace, $target_options);
        $damage_options = self::parse_string_variables($search_replace, $damage_options);
        $recovery_options = self::parse_string_variables($search_replace, $recovery_options);
        if (!empty($effect_options)){
            $effect_options = self::parse_string_variables($search_replace, $effect_options);
        }

        // Update target options for this item
        $this_item->target_options_update(array(
            'frame' => $target_options['robot_frame'],
            'kickback' => $target_options['robot_kickback'],
            'success' => array(
                $target_options['item_frame'],
                $target_options['item_offset'][0],
                $target_options['item_offset'][1],
                $target_options['item_offset'][2],
                $target_options['item_text']
                )
            ));

        // Update damage options for this item
        $this_item->damage_options_update(array(
            'kind' => $target_options['stat_kind'],
            'frame' => $damage_options['robot_frame'],
            'kickback' => $damage_options['robot_kickback'],
            'success' => array(
                $damage_options['item_sucess_frame'],
                $damage_options['item_success_offset'][0],
                $damage_options['item_success_offset'][1],
                $damage_options['item_success_offset'][2],
                $damage_options['item_success_text']
                ),
            'failure' => array(
                $damage_options['item_failure_frame'],
                $damage_options['item_failure_offset'][0],
                $damage_options['item_failure_offset'][1],
                $damage_options['item_failure_offset'][2],
                $damage_options['item_failure_text']
                )
            ));

        // Update recovery options for this item
        $this_item->recovery_options_update(array(
            'kind' => $target_options['stat_kind'],
            'frame' => $recovery_options['robot_frame'],
            'kickback' => $recovery_options['robot_kickback'],
            'success' => array(
                $recovery_options['item_sucess_frame'],
                $recovery_options['item_success_offset'][0],
                $recovery_options['item_success_offset'][1],
                $recovery_options['item_success_offset'][2],
                $recovery_options['item_success_text']
                ),
            'failure' => array(
                $damage_options['item_failure_frame'],
                $damage_options['item_failure_offset'][0],
                $damage_options['item_failure_offset'][1],
                $damage_options['item_failure_offset'][2],
                $damage_options['item_failure_text']
                )
            ));


        // Target the opposing robot with this item
        $this_robot->trigger_target($target_robot, $this_item);

        // Attempt to inflict damage on the opposing robot
        $stat_damage_amount = $this_item->item_damage;
        $target_robot->trigger_damage($this_robot, $this_item, $stat_damage_amount);

        // Only apply a secondary affect if one was defined
        if (!empty($effect_options)){

            // Define the stat property strings
            $robot_stat_prop = 'robot_'.$effect_options['stat_kind'];

            // Check to make sure the target of this effect is the target of the item
            if ($effect_options['effect_target'] == 'target'){

                // Trigger effect if target isn't disabled and item was successful and chance
                if (
                    $target_robot->robot_status != 'disabled' &&
                    $this_item->item_results['this_result'] != 'failure' &&
                    $this_item->item_results['this_amount'] > 0 &&
                    $target_robot->$robot_stat_prop > 0 &&
                    ($effect_options['effect_chance'] == 100 || $this_battle->critical_chance($effect_options['effect_chance']))
                    ){

                    // Define the default damage options for the stat effect
                    $this_item->damage_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'defend',
                        'percent' => true,
                        'kickback' => array(10, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['damage_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Define the default recovery options for the stat effect
                    $this_item->recovery_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'taunt',
                        'percent' => true,
                        'kickback' => array(0, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['recovery_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Calculate the exact damage amount and trigger it on the target
                    $trigger_options = array('apply_modifiers' => false);
                    $stat_damage_amount = ceil($target_robot->$robot_stat_prop * ($this_item->item_damage2 / 100));
                    $target_robot->trigger_damage($this_robot, $this_item, $stat_damage_amount, true, $trigger_options);
                }

            }
            // Otherwise, if the target of this effect is the user of the item
            elseif ($effect_options['effect_target'] == 'user'){

                // Trigger effect if target isn't disabled and item was successful and chance
                if (
                    $this_robot->robot_status != 'disabled' &&
                    $this_item->item_results['this_result'] != 'failure' &&
                    $this_item->item_results['this_amount'] > 0 &&
                    $this_robot->$robot_stat_prop < MMRPG_SETTINGS_STATS_MAX &&
                    ($effect_options['effect_chance'] == 100 || $this_battle->critical_chance($effect_options['effect_chance']))
                    ){

                    // Define the default recovery options for the stat effect
                    $this_item->recovery_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'taunt',
                        'percent' => true,
                        'kickback' => array(0, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['recovery_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Define the default damage options for the stat effect
                    $this_item->damage_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'defend',
                        'percent' => true,
                        'kickback' => array(10, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['damage_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Calculate the exact damage amount and trigger it on the target
                    $trigger_options = array('apply_modifiers' => false);
                    $stat_recovery_amount = ceil($this_robot->$robot_stat_prop * ($this_item->item_recovery2 / 100));
                    $this_robot->trigger_recovery($this_robot, $this_item, $stat_recovery_amount, true, $trigger_options);
                }

            }

        }

        // Return true on success
        return true;

    }


    // Define a static function to use as the common action for all ranged attack type items
    public static function item_function_ranged_attack($objects, $options = array()){


    }


    // Define a static function to use as the common action for all multi-hit attack type items
    public static function item_function_repeat_attack($objects, $options = array()){


    }


    // Define a static function to use as the common action for all multi-target attack type items
    public static function item_function_spread_attack($objects, $options = array()){


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

}
?>