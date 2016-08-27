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
        // Else if this is an item, tweak it's ID as well
        elseif (in_array($this_abilityinfo['ability_token'], $this->player->player_items)){
            $this_abilityinfo['ability_id'] = $this->player_id.str_pad($this_abilityinfo['ability_id'], 3, '0', STR_PAD_LEFT);
        }
        // Otherwise base the ID off of the robot
        elseif (!preg_match('/^'.$this->robot->robot_id.'/', $this_abilityinfo['ability_id'])){
            $this_abilityinfo['ability_id'] = $this->robot_id.str_pad($this_abilityinfo['ability_id'], 3, '0', STR_PAD_LEFT);
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
                $this_abilityinfo = self::get_index_info($this_abilityinfo_backup['ability_token']);
                if (empty($this_abilityinfo['ability_id'])){
                    exit();
                }
                $this_abilityinfo = array_replace($this_abilityinfo, $this_abilityinfo_backup);
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
        $this->ability_functions = isset($this_abilityinfo['ability_functions']) ? $this_abilityinfo['ability_functions'] : 'abilities/ability.php';
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

        // Collect any functions associated with this ability
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->ability_functions) ? $this->ability_functions : 'abilities/ability.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->ability_function = isset($ability['ability_function']) ? $ability['ability_function'] : function(){};
        $this->ability_function_onload = isset($ability['ability_function_onload']) ? $ability['ability_function_onload'] : function(){};
        $this->ability_function_attachment = isset($ability['ability_function_attachment']) ? $ability['ability_function_attachment'] : function(){};
        unset($ability);

        // Define the internal robot base values using the robots index array
        $this->ability_base_name = isset($this_abilityinfo['ability_base_name']) ? $this_abilityinfo['ability_base_name'] : $this->ability_name;
        $this->ability_base_token = isset($this_abilityinfo['ability_base_token']) ? $this_abilityinfo['ability_base_token'] : $this->ability_token;
        $this->ability_base_image = isset($this_abilityinfo['ability_base_image']) ? $this_abilityinfo['ability_base_image'] : $this->ability_image;
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

        // Define a the default ability results
        $this->ability_results_reset();

        // Reset the ability options to default
        $this->target_options_reset();
        $this->damage_options_reset();
        $this->recovery_options_reset();

        // Trigger the onload function if it exists
        $temp_target_player = $this->battle->find_target_player($this->player->player_side != 'right' ? 'right' : 'left');
        $temp_target_robot = $this->battle->find_target_robot($this->player->player_side != 'right' ? 'right' : 'left');
        $temp_function = $this->ability_function_onload;
        $temp_result = $temp_function(array(
            'this_field' => &$this->battle->battle_field,
            'this_battle' => &$this->battle,
            'this_player' => &$this->player,
            'this_robot' => &$this->robot,
            'target_player' => &$temp_target_player,
            'target_robot' => &$temp_target_robot,
            'this_ability' => &$this
            ));

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define public print functions for markup generation
    public function print_ability_name($plural = false){
        $type_class = !empty($this->ability_type) ? $this->ability_type : 'none';
        if ($type_class != 'none' && !empty($this->ability_type2)){ $type_class .= '_'.$this->ability_type2; }
        elseif ($type_class == 'none' && !empty($this->ability_type2)){ $type_class = $this->ability_type2; }
        return '<span class="ability_name ability_type ability_type_'.$type_class.'">'.$this->ability_name.($plural ? 's' : '').'</span>';
    }
    //public function print_ability_name(){ return '<span class="ability_name">'.$this->ability_name.'</span>'; }
    public function print_ability_token(){ return '<span class="ability_token">'.$this->ability_token.'</span>'; }
    public function print_ability_description(){ return '<span class="ability_description">'.$this->ability_description.'</span>'; }
    public function print_ability_type(){ return '<span class="ability_type">'.$this->ability_type.'</span>'; }
    public function print_ability_type2(){ return '<span class="ability_type2">'.$this->ability_type2.'</span>'; }
    public function print_ability_speed(){ return '<span class="ability_speed">'.$this->ability_speed.'</span>'; }
    public function print_ability_damage(){ return '<span class="ability_damage">'.$this->ability_damage.'</span>'; }
    public function print_ability_recovery(){ return '<span class="ability_recovery">'.$this->ability_recovery.'</span>'; }
    public function print_ability_accuracy(){ return '<span class="ability_accuracy">'.$this->ability_accuracy.'%</span>'; }

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
        $this->target_options['target_text'] = "{$this->robot->print_robot_name()} uses {$this->print_ability_name()}!";
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

    // Define a public function for collecting index data from the database
    public static function get_index_info($ability_token){
        global $db;
        // Collect the data from the index or the database if necessary
        if (!is_string($ability_token)){ return false; }
        $ability_info = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_token IN ('{$ability_token}');", 'ability_token');
        if (!empty($ability_info)){ $ability_info = self::parse_index_info($ability_info[$ability_token]); }
        else { $ability_info = array(); }
        return $ability_info;
    }
    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($ability_info){
        global $db;

        // Return false if empty
        if (empty($ability_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($ability_info['_parsed'])){ return $ability_info; }
        else { $ability_info['_parsed'] = true; }

        // Explode the base and animation indexes into an array
        $temp_field_names = array('ability_frame_animate', 'attachment_frame_animate', 'ability_frame_index', 'attachment_frame_index', 'ability_frame_offset', 'attachment_frame_offset');
        foreach ($temp_field_names AS $field_name){
            if (!empty($ability_info[$field_name])){ $ability_info[$field_name] = json_decode($ability_info[$field_name], true); }
            else { $ability_info[$field_name] = array(); }
            /*
            if (!empty($ability_info[$field_name])){
                $ability_info[$field_name] = strstr($ability_info[$field_name], ',') ? explode(',', $ability_info[$field_name]) : array($ability_info[$field_name]);
                foreach ($ability_info[$field_name] AS $key => $string){ $ability_info[$field_name][$key] = trim($string, '[]'); }
            } else {
                $ability_info[$field_name] = array();
            }
            */
        }

        // Explode the base and animation frame offsets into an array
        /*
        $temp_field_names = array('ability_frame_offset', 'attachment_frame_offset');
        foreach ($temp_field_names AS $field_name){
            if (!empty($ability_info[$field_name])){
                $ability_info[$field_name] = strstr($ability_info[$field_name], ',') ? explode(',', $ability_info[$field_name]) : array($ability_info[$field_name]);
                $temp_array = array();
                foreach ($ability_info[$field_name] AS $key => $string){
                    list($field, $value) = explode(':', trim($string, '[]'));
                    $temp_array[$field] = (int)($value);
                }
                $ability_info[$field_name][$key] = $temp_array;
            } else {
                $ability_info[$field_name] = array();
            }
        }
        */

        // Update the static index with this ability's index info
        //$db->INDEX['ABILITIES'][$ability_info['ability_token']] = $ability_info;

        // Return the parsed ability info
        return $ability_info;
    }




    // Define a static function for printing out the ability's title markup
    public static function print_editor_title_markup($robot_info, $ability_info, $print_options = array()){

        // Require the function file
        $temp_ability_title = '';
        // Pull in global variables
        global $mmrpg_index;
        $session_token = mmrpg_game_token();

        if (empty($robot_info)){ return false; }
        if (empty($ability_info)){ return false; }

        $print_options['show_accuracy'] = isset($print_options['show_accuracy']) ? $print_options['show_accuracy'] : true;
        $print_options['show_quantity'] = isset($print_options['show_quantity']) ? $print_options['show_quantity'] : true;

        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_ability_token = $ability_info['ability_token'];
        $temp_ability_type = !empty($ability_info['ability_type']) ? $mmrpg_index['types'][$ability_info['ability_type']] : false;
        $temp_ability_type2 = !empty($ability_info['ability_type2']) ? $mmrpg_index['types'][$ability_info['ability_type2']] : false;
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
            if (!empty($ability_info['ability_damage'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage'; }
            if (!empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_recovery'].' Recovery'; }
        }

        //if (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $temp_ability_title .= 'Special'; }

        // If show accuracy or quantity
        if (($ability_info['ability_class'] != 'item' && $print_options['show_accuracy'])
            || ($ability_info['ability_class'] == 'item' && $print_options['show_quantity'])){

            if ($ability_info['ability_class'] != 'item' && !empty($ability_info['ability_accuracy'])){ $temp_ability_title .= ' | '.$ability_info['ability_accuracy'].'% Accuracy'; }
            elseif ($ability_info['ability_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token])){ $temp_ability_title .= ' | '.($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token] == 1 ? '1 Unit' : $_SESSION[$session_token]['values']['battle_items'][$temp_ability_token].' Units'); }
            elseif ($ability_info['ability_class'] == 'item' ){ $temp_ability_title .= ' | 0 Units'; }

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
        global $mmrpg_index;
        $session_token = mmrpg_game_token();

        // Require the function file
        $this_option_markup = '';

        // Generate the ability option markup
        if (empty($robot_info)){ return false; }
        if (empty($ability_info)){ return false; }
        //$ability_info = self::get_index_info($temp_ability_token);
        $temp_robot_token = $robot_info['robot_token'];
        $temp_ability_token = $ability_info['ability_token'];
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_ability_token.'/'."\nrobot_abilities = ".array_keys($robot_info['robot_abilities'])."\nrobot_index_abilities = ".array_keys($robot_info['robot_index_abilities']));  }
        $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
        $robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
        $temp_ability_type = !empty($ability_info['ability_type']) ? $mmrpg_index['types'][$ability_info['ability_type']] : false;
        $temp_ability_type2 = !empty($ability_info['ability_type2']) ? $mmrpg_index['types'][$ability_info['ability_type2']] : false;
        $temp_ability_energy = rpg_robot::calculate_weapon_energy_static($robot_info, $ability_info);
        $temp_type_array = array();
        $temp_incompatible = false;
        $temp_index_abilities = !empty($robot_info['robot_index_abilities']) ? $robot_info['robot_index_abilities'] : array();
        $temp_current_abilities = !empty($robot_info['robot_abilities']) ? array_keys($robot_info['robot_abilities']) : array();
        $temp_compatible_abilities = array_merge($temp_index_abilities, $temp_current_abilities);
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_ability_token.'/'."\nindex_abilities = ".implode(',', $temp_index_abilities)."\ncurrent_abilities = ".implode(',', $temp_current_abilities)."\ncompatible_abilities = ".implode(',', $temp_compatible_abilities));  }
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
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_ability_token.'/'.($temp_incompatible ? 'incompatible' : 'compatible'));  }
        if ($temp_incompatible == true){ return false; }
        $temp_ability_label = $ability_info['ability_name'];
        $temp_ability_title = self::print_editor_title_markup($robot_info, $ability_info);
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
            'ability_name' => $this->ability_name,
            'ability_token' => $this->ability_token,
            'ability_class' => $this->ability_class,
            'ability_image' => $this->ability_image,
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
            'ability_frame_index' => $this->ability_frame_index,
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
        global $mmrpg_database_abilities, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;
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
        <div class="database_container database_<?= $ability_info['ability_class'] == 'item' ? 'item' : 'ability' ?>_container" data-token="<?=$ability_info['ability_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

            <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?=$ability_info['ability_token']?>">&nbsp;</a>
            <? endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?= $ability_info['ability_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

                <? if($print_options['show_icon']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_icon']): ?>
                            <? if($print_options['show_key'] !== false): ?>
                                <div class="icon ability_type <?= $ability_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                            <? endif; ?>
                            <? if ($ability_image_token != 'ability'){ ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: url(images/abilities/<?= $ability_image_token ?>/icon_right_<?= $ability_image_size_text ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon ability_status_active ability_position_active"><?=$ability_info['ability_name']?>'s Mugshot</div></div>
                            <? } else { ?>
                                <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon ability_status_active ability_position_active">No Image</div></div>
                            <? } ?>
                        <? endif; ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $ability_header_types ?> <?= (!$print_options['show_icon']) ? 'noicon' : 'hasicon' ?>">
                        <? if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="<?= 'database/abilities/'.$ability_info['ability_token'].'/' ?>"><?= $ability_info['ability_name'] ?></a>
                        <? else: ?>
                            <?= $ability_info['ability_name'] ?>&#39;s Data
                        <? endif; ?>
                        <? if (!empty($ability_info['ability_type_special'])){ ?>
                            <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($ability_info['ability_type_special']) ?> Type</div>
                        <? } elseif (!empty($ability_info['ability_type']) && !empty($ability_info['ability_type2'])){ ?>
                            <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($ability_info['ability_type']).' / '.ucfirst($ability_info['ability_type2']) ?> Type</div>
                        <? } elseif (!empty($ability_info['ability_type'])){ ?>
                            <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($ability_info['ability_type']) ?> Type</div>
                        <? } else { ?>
                            <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Neutral Type</div>
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
                                        <span class="ability_type ability_type_"><?=$ability_info['ability_name']?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
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
                                                $temp_string[] = '<a href="database/abilities/'.$ability_type.'/" class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_index['types'][$ability_type]['type_name'].'</a>';
                                                if (!empty($ability_info['ability_type2'])){
                                                    $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                                    $temp_string[] = '<a href="database/abilities/'.$ability_type2.'/" class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_index['types'][$ability_type2]['type_name'].'</a>';
                                                }
                                                echo implode(' ', $temp_string);
                                            } else {
                                                echo '<a href="database/abilities/none/" class="ability_type ability_type_none">Neutral</a>';
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
                                                $temp_string[] = '<span class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_index['types'][$ability_type]['type_name'].'</span>';
                                                if (!empty($ability_info['ability_type2'])){
                                                    $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                                    $temp_string[] = '<span class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_index['types'][$ability_type2]['type_name'].'</span>';
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
                                            <label style="display: block; float: left;">Power :</label>
                                            <? if(!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])): ?>
                                                <? if(!empty($ability_info['ability_damage'])){ ?><span class="ability_stat"><?= $ability_info['ability_damage'].(!empty($ability_info['ability_damage_percent']) ? '%' : '') ?> Damage</span><? } ?>
                                                <? if(!empty($ability_info['ability_recovery'])){ ?><span class="ability_stat"><?= $ability_info['ability_recovery'].(!empty($ability_info['ability_recovery_percent']) ? '%' : '') ?> Recovery</span><? } ?>
                                            <? else: ?>
                                                <span class="ability_stat">-</span>
                                            <? endif; ?>
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

                                <? else: ?>

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

                <? endif; ?>

                <? if($print_options['show_sprites'] && (!isset($ability_info['ability_image_sheets']) || $ability_info['ability_image_sheets'] !== 0) && $ability_image_token != 'ability' ): ?>

                    <h2 class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?=$ability_info['ability_name']?>&#39;s Sprites
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 10px; min-height: auto;">
                        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                            <?
                            // Show the ability mugshot sprite
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_title = $ability_sprite_title.' | Icon Sprite '.ucfirst($temp_direction);
                                $temp_label = 'Icon '.ucfirst(substr($temp_direction, 0, 1));
                                echo '<div style="'.($ability_sprite_size <= 80 ? 'padding-top: 20px; ' : '').'float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$ability_sprite_size.'px; height: '.$ability_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: 0;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/abilities/'.$ability_image_token.'/icon_'.$temp_direction.'_'.$ability_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                    echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                echo '</div>';
                            }
                            // Loop through the different frames and print out the sprite sheets
                            $temp_sheet_number = !empty($ability_info['ability_image_sheets']) ? $ability_info['ability_image_sheets'] : 1;
                            for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
                                foreach ($ability_sprite_frames AS $this_key => $this_frame){
                                    $margin_left = ceil((0 - $this_key) * $ability_sprite_size);
                                    $frame_relative = $this_frame;
                                    if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($ability_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                                    $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                                    foreach (array('right', 'left') AS $temp_direction){
                                        $temp_title = $ability_sprite_title.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                        $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                        $image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
                                        if ($temp_sheet > 1){ $image_token .= '-'.$temp_sheet; }
                                        echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$ability_sprite_size.'px; height: '.$ability_sprite_size.'px; overflow: hidden;">';
                                            echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/abilities/'.$image_token.'/sprite_'.$temp_direction.'_'.$ability_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
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

                <? endif; ?>

                <? if($print_options['show_robots'] && $ability_info['ability_class'] != 'item'): ?>

                    <h2 class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?=$ability_info['ability_name']?>&#39;s Robots
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
                                            $temp_global_abilities = array(
                                                'light-buster', 'wily-buster', 'cossack-buster',
                                                'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost',
                                                'energy-break', 'attack-break', 'defense-break', 'speed-break',
                                                'energy-swap', 'attack-swap', 'defense-swap', 'speed-swap',
                                                'energy-mode', 'attack-mode', 'defense-mode', 'speed-mode',
                                                'field-support', 'mecha-support',
                                                'repair-mode' // legacy support
                                                );
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
                                                    // Print out the disclaimer if a global ability
                                                    if ($this_robot_method == 'level' && $ability_info['ability_token'] == 'buster-shot'){
                                                        $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$ability_info['ability_name'].' is known by <em>all</em> robot masters from the start!</div>';
                                                    } elseif ($this_robot_method != 'level' && in_array($ability_info['ability_token'], $temp_global_abilities)){
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

                <? if ($print_options['show_records'] && $ability_info['ability_class'] == 'master'): ?>

                  <h2 id="records" class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                    <?= $ability_info['ability_name'] ?>&#39;s Records
                  </h2>
                  <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
                    <?

                    // Collect the database records for this ability
                    global $db;
                    $temp_ability_records = array('ability_unlocked' => 0, 'ability_equipped');
                    $temp_record_query = "SELECT
                        COUNT(*) AS unlock_count,
                        SUM(ROUND((
                        LENGTH(saves.save_values_battle_settings)
                        - LENGTH(REPLACE(saves.save_values_battle_settings, '\"{$ability_info['ability_token']}\"', ''))
                        ) / LENGTH('\"{$ability_info['ability_token']}\"')
                        )) AS equip_count
                        FROM mmrpg_saves AS saves
                        LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
                        LEFT JOIN mmrpg_leaderboard AS points ON points.user_id = saves.user_id
                        WHERE
                        saves.save_values_battle_abilities LIKE '%\"{$ability_info['ability_token']}\"%'
                        AND points.board_points <> 0
                        AND users.user_id <> 0
                        ;";
                    $temp_record_values = $db->get_array($temp_record_query);
                    if (!empty($temp_record_values)){
                        $temp_ability_records['ability_unlocked'] = $temp_record_values['unlock_count'];
                        $temp_ability_records['ability_equipped'] = $temp_record_values['equip_count'];
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
                              <span class="ability_quote"><?= $temp_ability_records['ability_unlocked'] == 1 ? '1 Player' : number_format($temp_ability_records['ability_unlocked'], 0, '.', ',').' Players' ?></span>
                            </td>
                          </tr>
                          <tr>
                            <td class="right">
                              <label style="display: block; float: left;">Equipped To : </label>
                              <span class="ability_quote"><?= $temp_ability_records['ability_equipped'] == 1 ? '1 Robot' : number_format($temp_ability_records['ability_equipped'], 0, '.', ',').' Robots' ?></span>
                            </td>
                          </tr>
                      </tbody>
                    </table>
                  </div>

                <? endif; ?>

                <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= 'database/abilities/'.$ability_info['ability_token'].'/' ?>" rel="permalink">+ Permalink</a>

                <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="<?= 'database/abilities/'.$ability_info['ability_token'].'/' ?>" rel="permalink">+ View More</a>

                <? endif; ?>

            </div>
        </div>
        <?
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;
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
        }

        // Extract all objects into the current scope
        extract($objects);

        // Define Search and replace object strings for replacing
        $search_replace = array();
        $search_replace['this_player_name'] = $this_player->print_player_name();
        $search_replace['this_robot_name'] = $this_robot->print_robot_name();
        $search_replace['target_player_name'] = $target_player->print_player_name();
        $search_replace['target_robot_name'] = $target_robot->print_robot_name();
        $search_replace['this_ability_name'] = $this_ability->print_ability_name();

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
                if (
                    $target_robot->robot_status != 'disabled' &&
                    $this_ability->ability_results['this_result'] != 'failure' &&
                    $this_ability->ability_results['this_amount'] > 0 &&
                    $target_robot->$robot_stat_prop > 0 &&
                    ($effect_options['effect_chance'] == 100 || $this_battle->critical_chance($effect_options['effect_chance']))
                    ){

                    // Define the default damage options for the stat effect
                    $this_ability->damage_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'defend',
                        'percent' => true,
                        'kickback' => array(10, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['damage_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Define the default recovery options for the stat effect
                    $this_ability->recovery_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'taunt',
                        'percent' => true,
                        'kickback' => array(0, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['recovery_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Calculate the exact damage amount and trigger it on the target
                    $trigger_options = array('apply_modifiers' => false);
                    $stat_damage_amount = ceil($target_robot->$robot_stat_prop * ($this_ability->ability_damage2 / 100));
                    $target_robot->trigger_damage($this_robot, $this_ability, $stat_damage_amount, true, $trigger_options);
                }

            }
            // Otherwise, if the target of this effect is the user of the ability
            elseif ($effect_options['effect_target'] == 'user'){

                // Trigger effect if target isn't disabled and ability was successful and chance
                if (
                    $this_robot->robot_status != 'disabled' &&
                    $this_ability->ability_results['this_result'] != 'failure' &&
                    $this_ability->ability_results['this_amount'] > 0 &&
                    $this_robot->$robot_stat_prop < MMRPG_SETTINGS_STATS_MAX &&
                    ($effect_options['effect_chance'] == 100 || $this_battle->critical_chance($effect_options['effect_chance']))
                    ){

                    // Define the default recovery options for the stat effect
                    $this_ability->recovery_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'taunt',
                        'percent' => true,
                        'kickback' => array(0, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['recovery_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Define the default damage options for the stat effect
                    $this_ability->damage_options_update(array(
                        'kind' => $effect_options['stat_kind'],
                        'frame' => 'defend',
                        'percent' => true,
                        'kickback' => array(10, 0, 0),
                        'success' => array(9, 0, 0, -10, $effect_options['damage_text']),
                        'failure' => array(9, 0, 0, -9999, '')
                        ));

                    // Calculate the exact damage amount and trigger it on the target
                    $trigger_options = array('apply_modifiers' => false);
                    $stat_recovery_amount = ceil($this_robot->$robot_stat_prop * ($this_ability->ability_recovery2 / 100));
                    $this_robot->trigger_recovery($this_robot, $this_ability, $stat_recovery_amount, true, $trigger_options);
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