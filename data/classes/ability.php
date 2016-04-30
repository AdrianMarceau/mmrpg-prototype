<?
// Define a class for the abilities
class mmrpg_ability {

    // Define global class variables
    public $flags;
    public $counters;
    public $values;
    public $history;

    // Define the constructor class
    public function mmrpg_ability(){

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
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'ability_load($this_abilityinfo:'.substr(json_encode($this_abilityinfo), 0, 100).')');  }

        // If the ability info was not an array, return false
        if (!is_array($this_abilityinfo)){ return false; }
        // If the ability ID was not provided, return false
        //if (!isset($this_abilityinfo['ability_id'])){ return false; }
        if (!isset($this_abilityinfo['ability_id'])){ $this_abilityinfo['ability_id'] = 0; }
        // If the ability token was not provided, return false
        if (!isset($this_abilityinfo['ability_token'])){ return false; }

        // If this is a special system ability, hard-code its ID, otherwise base off robot
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $temp_system_abilities = array('attachment-defeat');
        if (in_array($this_abilityinfo['ability_token'], $temp_system_abilities)){
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this_abilityinfo['ability_id'] = $this->player_id.'000';
        }
        // Else if this is an item, tweak it's ID as well
        elseif (in_array($this_abilityinfo['ability_token'], $this->player->player_items)){
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this_abilityinfo['ability_id'] = $this->player_id.str_pad($this_abilityinfo['ability_id'], 3, '0', STR_PAD_LEFT);
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$this_abilityinfo[\'ability_id\'] = '.$this_abilityinfo['ability_id']);  }
        }
        // Otherwise base the ID off of the robot
        elseif (!preg_match('/^'.$this->robot->robot_id.'/', $this_abilityinfo['ability_id'])){
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this_abilityinfo['ability_id'] = $this->robot_id.str_pad($this_abilityinfo['ability_id'], 3, '0', STR_PAD_LEFT);
        }

        // Collect current ability data from the session if available
        $this_abilityinfo_backup = $this_abilityinfo;
        if (isset($_SESSION['ABILITIES'][$this_abilityinfo['ability_id']])){
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this_abilityinfo = $_SESSION['ABILITIES'][$this_abilityinfo['ability_id']];
        }
        // Otherwise, collect ability data from the index if not already
        elseif (!in_array($this_abilityinfo['ability_token'], $temp_system_abilities)){
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $temp_backup_id = $this_abilityinfo['ability_id'];
            if (empty($this_abilityinfo_backup['_parsed'])){
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_abilityinfo = mmrpg_ability::get_index_info($this_abilityinfo_backup['ability_token']);
                if (empty($this_abilityinfo['ability_id'])){
                    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$this_abilityinfo_backup:: '."\n".print_r($this_abilityinfo_backup, true));  }
                    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$this_abilityinfo:: '."\n".print_r($this_abilityinfo, true));  }
                    exit();
                }
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
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
    public function print_ability_name(){
        return '<span class="ability_name ability_type ability_type_'.(!empty($this->ability_type) ? $this->ability_type : 'none').(!empty($this->ability_type2) ? '_'.$this->ability_type2 : '').'">'.$this->ability_name.'</span>';
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

        // DEBUG
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_ability_target\'] = '.$options['this_ability_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this->ability_image_size * 2);
        $this_data['ability_sprite_size'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_width'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_height'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_image_width'] = ceil($this_data['ability_scale'] * $zoom_size * 10);
        $this_data['ability_image_height'] = ceil($this_data['ability_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this robot
        $canvas_offset_data = $this->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size']);
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

        // DEBUG
        //$this_data['ability_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));


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
    public function console_markup($options, $player_data, $robot_data){

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

    // Define a public function for collecting index data from the database
    public static function get_index_info($ability_token){
        global $DB;
        // Collect the data from the index or the database if necessary
        if (!is_string($ability_token)){ return false; }
        $ability_info = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_token IN ('{$ability_token}');", 'ability_token');
        if (!empty($ability_info)){ $ability_info = mmrpg_ability::parse_index_info($ability_info[$ability_token]); }
        else { $ability_info = array(); }
        return $ability_info;
    }
    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($ability_info){
        global $DB;

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
        //$DB->INDEX['ABILITIES'][$ability_info['ability_token']] = $ability_info;

        // Return the parsed ability info
        return $ability_info;
    }




    // Define a static function for printing out the ability's title markup
    public static function print_editor_title_markup($robot_info, $ability_info, $print_options = array()){
        // Require the function file
        $temp_ability_title = '';
        require(MMRPG_CONFIG_ROOTDIR.'data/classes/ability_editor-title-markup.php');
        // Return the generated option markup
        return $temp_ability_title;
    }


    // Define a static function for printing out the ability's title markup
    public static function print_editor_option_markup($robot_info, $ability_info){
        // Require the function file
        $this_option_markup = '';
        require(MMRPG_CONFIG_ROOTDIR.'data/classes/ability_editor-option-markup.php');
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
        // Require the external function for generating database markup
        require('ability_database-markup.php');
        // Return the generated markup
        return $this_markup;
    }

    // Define a static function to use as the common action for all item-core-___ abilities
    public static function item_function_core($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 85, 35, 10, $this_robot->print_robot_name().' thows a '.$this_ability->print_ability_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => true,
            'frame' => 'damage',
            'kickback' => array(10, 5, 0),
            'success' => array(0, 10, 0, 10, 'The '.$this_ability->print_ability_name().' damaged the target!'),
            'failure' => array(0, -30, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => true,
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 10, 0, 10, 'The '.$this_ability->print_ability_name().' recovered the target!'),
            'failure' => array(0, -30, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
        $trigger_options = array('apply_modifiers' => true, 'apply_type_modifiers' => true, 'apply_core_modifiers' => false, 'apply_field_modifiers' => true, 'apply_stat_modifiers' => false, 'apply_position_modifiers' => false, 'apply_starforce_modifiers' => false);
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);

        // Return true on success
        return true;

    }

    // Define a static function to use as the common action for all stat pellet and capsule items
    public static function item_function_stat_booster($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self and print item use text
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 40, -2, 99,
                $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
                $target_robot->print_robot_name().' is given the '.$this_ability->print_ability_name().'!'
                )
            ));
        $target_robot->trigger_target($target_robot, $this_ability);

        // Define the various object words used for each boost type
        $stat_boost_subjects = array('attack' => 'weapons', 'defense' => 'shields', 'speed' => 'mobility');
        $stat_boost_verbs = array('weapons' => 'were', 'shields' => 'were', 'mobility' => 'was');

        // Define the various effect words used for each item size
        if (strstr($this_ability->ability_token, 'pellet')){ $boost_effect_word = 'a bit'; }
        elseif (strstr($this_ability->ability_token, 'capsule')){ $boost_effect_word = 'a lot'; }

        // Define the stat(s) this ability will boost (super items boost all)
        $stat_boost_tokens = array();
        if (strstr($this_ability->ability_token, 'super')){ $stat_boost_tokens = array('attack', 'defense', 'speed'); }
        else { $stat_boost_tokens[] = $this_ability->ability_type; }

        // Loop through each stat boost token and raise it
        foreach ($stat_boost_tokens AS $stat_token){

            // Collect the object word for this stat type
            $stat_subject = $stat_boost_subjects[$stat_token];
            $stat_verb = $stat_boost_verbs[$stat_subject];
            $stat_base_prop = 'robot_base_'.$stat_token;
            $stat_max_prop = 'robot_max_'.$stat_token;

            // Increase this robot's in-battle stat
            $this_ability->recovery_options_update(array(
                'kind' => $stat_token,
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s '.$stat_subject.' powered up '.$boost_effect_word.'! '.mmrpg_battle::random_positive_word()),
                'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s '.$stat_subject.''.$stat_verb.' not affected&hellip; '.mmrpg_battle::random_negative_word())
                ));
            $stat_recovery_amount = ceil($target_robot->$stat_base_prop * ($this_ability->ability_recovery / 100));
            $target_robot->trigger_recovery($target_robot, $this_ability, $stat_recovery_amount);

            // Only update the session of the ability was successful
            if ($this_ability->ability_results['this_result'] == 'success' && $this_ability->ability_results['total_amount'] > 0){

                // If this robot is not already over their stat limit, increment pending boosts
                if ($target_player->player_side == 'left' && $target_robot->$stat_base_prop < $target_robot->$stat_max_prop){

                    // Create the stat boost variable if it doesn't already exist in the session
                    $temp_robot_rewards = &$_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token];
                    if (!isset($temp_robot_rewards['robot_'.$stat_token])){ $temp_robot_rewards['robot_'.$stat_token] = 0; }

                    // Calculate the actual amount to permanently boost in case it goes over max
                    $stat_boost_amount = $this_ability->ability_results['total_amount'];
                    if (($target_robot->$stat_base_prop + $stat_boost_amount) > $target_robot->$stat_max_prop){
                        $stat_boost_amount = $target_robot->$stat_max_prop - $target_robot->$stat_base_prop;
                    }

                    // Update the session variables with the incremented stat boost so long as it's not empty
                    if (!empty($stat_boost_amount)){
                        $temp_robot_rewards['robot_'.$stat_token] += $stat_boost_amount;
                        $target_robot->$stat_base_prop += $stat_boost_amount;
                        $target_robot->update_session();
                    }

                }

            }


        }

        // Return true on success
        return true;

    }

}
?>