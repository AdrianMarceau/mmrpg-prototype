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
        $this_abilityinfo = mmrpg_ability::get_index_info($this_abilityinfo_backup['ability_token']);
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
    $this->ability_subclass = isset($this_abilityinfo['ability_subclass']) ? $this_abilityinfo['ability_subclass'] : '';
    $this->ability_master = isset($this_abilityinfo['ability_master']) ? $this_abilityinfo['ability_master'] : '';
    $this->ability_number = isset($this_abilityinfo['ability_number']) ? $this_abilityinfo['ability_number'] : '';
    $this->ability_image = isset($this_abilityinfo['ability_image']) ? $this_abilityinfo['ability_image'] : $this->ability_token;
    $this->ability_image_size = isset($this_abilityinfo['ability_image_size']) ? $this_abilityinfo['ability_image_size'] : 40;
    $this->ability_description = isset($this_abilityinfo['ability_description']) ? $this_abilityinfo['ability_description'] : '';
    $this->ability_type = isset($this_abilityinfo['ability_type']) ? $this_abilityinfo['ability_type'] : '';
    $this->ability_type2 = isset($this_abilityinfo['ability_type2']) ? $this_abilityinfo['ability_type2'] : '';
    $this->ability_speed = isset($this_abilityinfo['ability_speed']) ? $this_abilityinfo['ability_speed'] : 1;
    $this->ability_energy = isset($this_abilityinfo['ability_energy']) ? $this_abilityinfo['ability_energy'] : 4;
    $this->ability_energy_percent = isset($this_abilityinfo['ability_energy_percent']) ? $this_abilityinfo['ability_energy_percent'] : true;
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


  // Define a static function for printing out the ability's select options markup
  public static function print_editor_options_list_markup($player_ability_rewards, $robot_ability_rewards, $player_info, $robot_info){
    // Require the function file
    $this_options_markup = '';
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/ability_editor-options-list-markup.php');
    // Return the generated select markup
    return $this_options_markup;
  }


  // Define a static function for printing out the ability's select markup
  public static function print_editor_select_markup($ability_rewards_options, $player_info, $robot_info, $ability_info, $ability_key = 0){
    // Require the function file
    $this_select_markup = '';
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/ability_editor-select-markup.php');
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
  public static function print_database_markup($ability_info, $print_options = array(), $cache_markup = false){

    //die('$temp_path_file = '.$temp_path_file.'<br /> markup was not found...');
    // Define the markup variable
    $this_markup = '';
    // Require the external function for generating database markup
    require('ability_database-markup.php');
    // Return the generated markup
    return $this_markup;

    /*
    // Define the cache and index paths for abilities
    $temp_hash_token = $ability_info['ability_token'].'_'.preg_replace('/[^a-z0-9]/i', '', json_encode($print_options)); //md5();
    $temp_path_file = 'database/ability_'.$temp_hash_token.'_'.MMRPG_CONFIG_CACHE_DATE.'.htm';
    //die('$temp_path_file = '.$temp_path_file);
    // If the appropriate cache file exists, use that
    $this_markup = $cache_markup ? mmrpg_get_cached_markup($temp_path_file) : '';
    //die('$this_markup = '.$this_markup);
    // If markup was found, return it directly
    if (!empty($this_markup)){
      //die('$temp_path_file = '.$temp_path_file.'<br /> markup was found!');
      // Return the database markup
      return $this_markup;
    }
    // Otherwise, generate fresh content
    else {
      //die('$temp_path_file = '.$temp_path_file.'<br /> markup was not found...');
      // Define the markup variable
      $this_markup = '';
      // Require the external function for generating database markup
      require('ability_database-markup.php');
      // Update the cached markup file
      if (!empty($cache_markup) && !empty($this_markup)){
        mmrpg_save_cached_markup($temp_path_file, $this_markup);
      }
      // Return the generated markup
      return $this_markup;
    }
    */

  }

  // Define a static function to use as the common action for all item-shard-___ abilities
  public static function item_function_shard($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 220, 20, 99,
        $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
        $this_robot->print_robot_name().' releases the '.$this_ability->print_ability_name().'&#39;s energy!'
        )
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // If the multiplier is already at the limit of 3x, this ability fails
    if (isset($this_field->field_multipliers[$this_ability->ability_type]) && $this_field->field_multipliers[$this_ability->ability_type] >= MMRPG_SETTINGS_MULTIPLIER_MAX){

      // Target this robot's self and show the ability failing
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(9, 0, 0, -10,
          mmrpg_battle::random_negative_word().' The field\'s '.ucfirst($this_ability->ability_type).' power wont go any higher&hellip;<br />'
          )
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Return true on success (well, failure, but whatever)
      return true;

    }

    // CREATE ANIMATION ATTACHMENTS
    if (true){

      // Define this ability's attachment token
      $this_star_index = mmrpg_prototype_star_image(!empty($this_ability->ability_type) ? $this_ability->ability_type : 'none');
      $this_sprite_sheet = 'field-support';
      $this_attachment_token = 'ability_'.$this_sprite_sheet;
      $this_attachment_info = array(
        'class' => 'ability',
        'ability_token' => $this_sprite_sheet,
        'ability_image' => $this_sprite_sheet.($this_star_index['sheet'] > 1 ? '-'.$this_star_index['sheet'] : ''),
        'ability_frame' => $this_star_index['frame'],
        'ability_frame_animate' => array($this_star_index['frame']),
        'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
        );

      // Attach this ability attachment to this robot temporarily
      $this_robot->robot_frame = 'taunt';
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();

      // Attach this ability to all robots on this player's side of the field
      $backup_robots_active = $this_player->values['robots_active'];
      $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
      if ($backup_robots_active_count > 0){
        $this_key = 0;
        foreach ($backup_robots_active AS $key => $info){
          if ($info['robot_id'] == $this_robot->robot_id){ continue; }
          $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
          // Attach this ability attachment to the this robot temporarily
          $temp_this_robot->robot_frame = 'taunt';
          $temp_this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
          $temp_this_robot->update_session();
          $this_key++;
        }
      }

      // Attach this ability to all robots on the target's side of the field
      $backup_robots_active = $target_player->values['robots_active'];
      $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
      if ($backup_robots_active_count > 0){
        $target_key = 0;
        foreach ($backup_robots_active AS $key => $info){
          $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
          // Attach this ability attachment to the target robot temporarily
          $temp_target_robot->robot_frame = 'taunt';
          $temp_target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
          $temp_target_robot->update_session();
          $target_key++;
        }
      }

    }

    // Create or increase the elemental booster for this field
    $temp_change_percent = round($this_ability->ability_recovery / 100, 1);
    if (!isset($this_field->field_multipliers[$this_ability->ability_type])){ $this_field->field_multipliers[$this_ability->ability_type] = 1.0 + $temp_change_percent; }
    else { $this_field->field_multipliers[$this_ability->ability_type] = $this_field->field_multipliers[$this_ability->ability_type] + $temp_change_percent; }
    if ($this_field->field_multipliers[$this_ability->ability_type] >= MMRPG_SETTINGS_MULTIPLIER_MAX){
      $temp_change_percent = MMRPG_SETTINGS_MULTIPLIER_MAX - $this_field->field_multipliers[$this_ability->ability_type];
      $this_field->field_multipliers[$this_ability->ability_type] = MMRPG_SETTINGS_MULTIPLIER_MAX;
    }
    $this_field->update_session();

    // Create the event to show this element boost
    $this_battle->events_create($this_robot, false, $this_field->field_name.' Multipliers',
    	mmrpg_battle::random_positive_word().' <span class="ability_name ability_type ability_type_'.$this_ability->ability_type.'">'.ucfirst($this_ability->ability_type).' Effects</span> were boosted by '.ceil($temp_change_percent * 100).'%!<br />'.
      'The multiplier is now at <span class="ability_name ability_type ability_type_'.$this_ability->ability_type.'">'.ucfirst($this_ability->ability_type).' x '.number_format($this_field->field_multipliers[$this_ability->ability_type], 1).'</span>!',
      array('canvas_show_this_ability_overlay' => true)
      );


    // DESTROY ANIMATION ATTACHMENTS
    if (true){

      // Remove this ability from all robots on this player's side of the field
      $backup_robots_active = $this_player->values['robots_active'];
      $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
      if ($backup_robots_active_count > 0){
        $this_key = 0;
        foreach ($backup_robots_active AS $key => $info){
          if ($info['robot_id'] == $this_robot->robot_id){ continue; }
          $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
          // Attach this ability attachment to the this robot temporarily
          unset($temp_this_robot->robot_attachments[$this_attachment_token]);
          $temp_this_robot->update_session();
          $this_key++;
        }
      }

      // Remove this ability from all robots on the target's side of the field
      $backup_robots_active = $target_player->values['robots_active'];
      $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
      if ($backup_robots_active_count > 0){
        $target_key = 0;
        foreach ($backup_robots_active AS $key => $info){
          if ($info['robot_id'] == $target_robot->robot_id){ continue; }
          $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
          // Attach this ability attachment to the target robot temporarily
          unset($temp_target_robot->robot_attachments[$this_attachment_token]);
          $temp_target_robot->update_session();
          $target_key++;
        }
      }

      // Remove this ability attachment from the target robot
      unset($target_robot->robot_attachments[$this_attachment_token]);
      $target_robot->update_session();

      // Remove this ability attachment from this robot
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();

      // UPDATE COPY CORE ROBOTS
      if ($this_robot->robot_base_shard == 'copy' && $this_robot->robot_shard != $this_ability->ability_type){

        // Define this ability's second attachment token
        $this_attachment_token_two = 'ability_'.$this_ability->ability_token.'_two';
        $this_attachment_info_two = array(
        	'class' => 'ability',
          'sticky' => true,
        	'ability_token' => $this_ability->ability_token,
          'ability_frame' => 0,
          'ability_frame_animate' => array(0),
          'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
          );

        // Attach this item shard to the robot temporarily
        $this_robot->robot_attachments[$this_attachment_token_two] = $this_attachment_info_two;
        $this_robot->update_session();

        // Remove the immunities added by any previous shard
        //$robot_backup_shard = $this_robot->robot_shard;

        // If this is a copy shard item, reset the robot's immunities to nothing
        if ($this_ability->ability_type == 'copy'){ $this_robot->robot_immunities = array(); }
        // Otherwise, add this shard's type as an immunity for this robot
        else { $this_robot->robot_immunities = array($this_ability->ability_type); }

        // Update this robot's shard and image
        $this_robot->robot_shard = $this_ability->ability_type;
        $this_robot->robot_image = $this_ability->ability_type != 'copy' ? $this_robot->robot_base_image.'_'.$this_ability->ability_type : $this_robot->robot_base_image;

        // Define and print the event message about the shard change
        $this_robot->robot_frame = 'defend';
        $this_robot->update_session();
        $this_event_body = '';
        if ($this_ability->ability_type == 'copy'){
          $this_event_body .= $this_robot->print_robot_name().'&#39;s robot shard returned to normal!<br /> ';
          $this_event_body .= $this_robot->print_robot_name().' turned back into a '.$this_ability->print_ability_name().' robot!';
        } else {
          $this_event_body .= $this_robot->print_robot_name().'&#39;s robot shard reacted to the elemental energy!<br /> ';
          $this_event_body .= $this_robot->print_robot_name().' turned into '.(preg_match('/^(a|e|i|o|u|y)/i', $this_ability->ability_type) ? 'an' : 'a').' '.$this_ability->print_ability_name().' robot!';
        }
        $this_battle->events_create($this_robot, $target_robot,
          $this_robot->robot_name.'&#39;s Robot Core',
          $this_event_body,
          array('canvas_show_this_ability_overlay' => true)
          );

        // Remove this shard attachment from this robot
        $this_robot->robot_frame = 'base';
        unset($this_robot->robot_attachments[$this_attachment_token_two]);
        $this_robot->update_session();

      }

    }

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
      'success' => array(0, 105, 0, 10, $this_robot->print_robot_name().' fires '.(preg_match('/^(a|e|i|o|u)/i', $this_ability->ability_token) ? 'an' : 'a').'  '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(0, -60, 0, 10, 'The '.$shot_text.' shot '.$damage_text.' the target!'),
      'failure' => array(0, -60, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(0, -60, 0, 10, 'The '.$shot_text.' shot '.$recovery_text.' the target!'),
      'failure' => array(0, -60, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
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
    $this_ability->ability_damage = $temp_new_damage;

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      $this_ability->ability_target = 'select_target';
    } else {
      $this_ability->ability_target = $this_ability->ability_base_target;
    }

    // Update the ability session
    $this_ability->update_session();

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
        'success' => array(1, -10, 0, -10, $this_robot->print_robot_name().' charges the '.$this_ability->print_ability_name().'&hellip;')
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Attach this ability attachment to the robot using it
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();

    }
    // Else if the ability flag was set, the ability is released at the target
    else {

      // Update this ability's target options and trigger
      $this_ability->target_options_update(array(
        'frame' => 'shoot',
        'kickback' => array(-5, 0, 0),
        'success' => array(3, 100, -15, 10, $this_robot->print_robot_name().' fires the '.$this_ability->print_ability_name().'!'),
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Remove this ability attachment to the robot using it
      $this_robot->robot_attachments[$this_attachment_token]['ability_frame'] = 0;
      $this_robot->robot_attachments[$this_attachment_token]['ability_frame_animate'] = array(1, 0);
      $this_robot->update_session();

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(20, 0, 0),
        'success' => array(3, -110, -15, 10, 'A powerful '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$damage_text.' the target!'),
        'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_ability_name().' shot missed&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'kickback' => array(20, 0, 0),
        'success' => array(3, -110, -15, 10, 'The '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$recovery_text.' the target!'),
        'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_ability_name().' shot missed&hellip;')
        ));
      $energy_damage_amount = $this_ability->ability_damage;
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

      // Remove this ability attachment to the robot using it
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();

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
    if (!$this_charge_required){ $this_ability->ability_energy = 0; }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->ability_energy = $this_ability->ability_base_energy; }

    // If this robot is holding a Charge Module, cut power in half
    if ($this_robot->robot_item == 'item-charge-module'){
      $this_ability->ability_damage = ceil($this_ability->ability_base_damage / 2);
    } else {
      $this_ability->ability_damage = $this_ability->ability_base_damage;
    }

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      $this_ability->ability_target = !$this_charge_required ? 'select_target' : $this_ability->ability_base_target;
    } else {
      $this_ability->ability_target = $this_ability->ability_base_target;
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

  }

  // Define a static function to use as the common action for all ____-overdrive abilities
  public static function ability_function_overdrive($objects, $shot_text = 'energy', $damage_text = 'damaged', $recovery_text = 'recovered'){

    // Extract all objects into the current scope
    extract($objects);

    // Decrease this robot's weapon energy to zero
    $this_robot->robot_weapons = 0;
    $this_robot->update_session();

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'kickback' => array(-5, 0, 0),
      'frame' => 'defend',
      'success' => array(0, 15, 45, 10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Define this ability's attachment token
    $crest_attachment_token = 'ability_'.$this_ability->ability_token;
    $crest_attachment_info = array(
      'class' => 'ability',
      'sticky' => true,
      'ability_token' => $this_ability->ability_token,
      'ability_image' => $this_ability->ability_token,
      'ability_frame' => 0,
      'ability_frame_animate' => array(1,2),
      'ability_frame_offset' => array('x' => 20, 'y' => 50, 'z' => 10),
      'ability_frame_classes' => ' ',
      'ability_frame_styles' => ' '
      );

    // Add the ability crest attachment
    $this_robot->robot_frame = 'summon';
    $this_robot->robot_attachments[$crest_attachment_token] = $crest_attachment_info;
    $this_robot->update_session();


    // Define this ability's attachment token
    $overlay_attachment_token = 'system_fullscreen-black';
    $overlay_attachment_info = array(
    	'class' => 'ability',
      'sticky' => true,
    	'ability_token' => $this_ability->ability_token,
      'ability_image' => 'fullscreen-black',
      'ability_frame' => 0,
      'ability_frame_animate' => array(0, 1),
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -12),
      'ability_frame_classes' => 'sprite_fullscreen '
      );

    // Add the black overlay attachment
    $target_robot->robot_attachments[$overlay_attachment_token] = $overlay_attachment_info;
    $target_robot->update_session();
    // prepare the ability options
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 0, 0),
      'success' => array(3, -60, -15, 10, 'A powerful '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$damage_text.' '.$target_robot->print_robot_name().'!'),
      'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_ability_name().' shot missed '.$target_robot->print_robot_name().'&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(20, 0, 0),
      'success' => array(3, -60, -15, 10, 'The '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$recovery_text.' '.$target_robot->print_robot_name().'!'),
      'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_ability_name().' shot missed '.$target_robot->print_robot_name().'&hellip;')
      ));
    // Inflict damage on the opposing robot
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
    // Remove the black overlay attachment
    unset($target_robot->robot_attachments[$overlay_attachment_token]);
    $target_robot->update_session();

    // Loop through the target's benched robots, inflicting half base damage to each
    $backup_robots_active = $target_player->values['robots_active'];
    foreach ($backup_robots_active AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){ continue; }
      $this_ability->ability_results_reset();
      $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
      // Add the black overlay attachment
      $overlay_attachment_info['ability_frame_offset']['z'] -= 2;
      $temp_target_robot->robot_attachments[$overlay_attachment_token] = $overlay_attachment_info;
      $temp_target_robot->update_session();
      // Update the ability options
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(20, 0, 0),
        'success' => array(3, -60, -15, 10, 'A powerful '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$damage_text.' '.$temp_target_robot->print_robot_name().'!'),
        'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_ability_name().' shot missed '.$temp_target_robot->print_robot_name().'&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'kickback' => array(20, 0, 0),
        'success' => array(3, -60, -15, 10, 'The '.($shot_text != 'energy' ? $shot_text.' energy' : 'energy').' shot '.$recovery_text.' '.$temp_target_robot->print_robot_name().'!'),
        'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_ability_name().' shot missed '.$temp_target_robot->print_robot_name().'&hellip;')
        ));
      //$energy_damage_amount = ceil($this_ability->ability_damage / $target_robots_active);
      $energy_damage_amount = $this_ability->ability_damage;
      $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
      unset($temp_target_robot->robot_attachments[$overlay_attachment_token]);
      $temp_target_robot->update_session();
    }

    // Add the black background attachment
    $this_robot->robot_frame = '';
    unset($this_robot->robot_attachments[$crest_attachment_token]);
    $this_robot->update_session();

    // Trigger the disabled event on the targets now if necessary
    if ($target_robot->robot_energy <= 0 || $target_robot->robot_status == 'disabled'){ $target_robot->trigger_disabled($this_robot, $this_ability); }
    foreach ($backup_robots_active AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){ continue; }
      $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
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
    $this_ability->ability_energy = $this_robot->robot_weapons;
    if ($this_ability->ability_energy < 1){ $this_ability->ability_energy = 1; }
    if ($this_ability->ability_type == $this_robot->robot_core){ $this_ability->ability_energy = $this_ability->ability_energy * 2; }

    // Calculate the user's current life damage percent for calculations
    $robot_energy_damage = $this_robot->robot_base_energy - $this_robot->robot_energy;
    $robot_energy_damage_percent = !empty($robot_energy_damage) ? ceil(($robot_energy_damage / $this_robot->robot_base_energy) * 100) : 0;

    // Multiply the user's damage by the remaining weapon energy for damage total
    $ability_damage_amount = ceil($this_robot->robot_weapons * (1 + $robot_energy_damage_percent));
    $this_ability->ability_damage = $ability_damage_amount;

    // Ability ability session before returning
    $this_ability->update_session();

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
      'success' => array(2, 0, -20, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Decrease the target robot's attack stat
    $this_ability->recovery_options_update(array(
      'kind' => $stat_type,
      'frame' => 'taunt',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(3, 0, 5, -10, $this_robot->print_robot_name().'&#39;s '.$stat_noun.' were '.$recovery_text.'!'),
      'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ), true);
    $this_ability->damage_options_update(array(
      'kind' => $stat_type,
      'frame' => 'damage',
      'percent' => true,
      'kickback' => array(0, 0, 0),
      'success' => array(3, 0, 5, -10, $this_robot->print_robot_name().'&#39;s '.$stat_noun.' were '.$damage_text.'!'),
      'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_robot_name().'&hellip;')
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
    return mmrpg_ability::ability_function_elemental_stat_boost($objects, 'attack', 'weapon systems', $shot_text, $recovery_text, $damage_text);
  }
  public static function ability_function_elemental_defense_boost($objects, $recovery_text = 'recovered', $damage_text = 'damaged'){
    return mmrpg_ability::ability_function_elemental_stat_boost($objects, 'defense', 'shield systems', $shot_text, $recovery_text, $damage_text);
  }
  public static function ability_function_elemental_speed_boost($objects, $recovery_text = 'recovered', $damage_text = 'damaged'){
    return mmrpg_ability::ability_function_elemental_stat_boost($objects, 'speed', 'mobility systems', $shot_text, $recovery_text, $damage_text);
  }

  // Define a static onload function to use as the common action for all elemental stat boost abilities
  public static function ability_function_onload_elemental_stat_boost($objects){

    // Return true on success
    return true;

  }
  // Define alias functions for each stat in attack/defense/speed stats for cleaner function calls
  public static function ability_function_onload_elemental_attack_boost($objects){
    return mmrpg_ability::ability_function_onload_elemental_stat_boost($objects);
  }
  public static function ability_function_onload_elemental_defense_boost($objects){
    return mmrpg_ability::ability_function_onload_elemental_stat_boost($objects);
  }
  public static function ability_function_onload_elemental_speed_boost($objects){
    return mmrpg_ability::ability_function_onload_elemental_stat_boost($objects);
  }


  // Define a static function to use as the common action for all elemental stat break abilities
  public static function ability_function_elemental_stat_break($objects, $stat_type, $stat_noun, $damage_text = 'damaged', $recovery_text = 'recovered'){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 85, 0, 10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Decrease the target robot's attack stat
    $this_ability->damage_options_update(array(
      'kind' => $stat_type,
      'frame' => 'damage',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(1, -50, 0, 10, $target_robot->print_robot_name().'&#39;s '.$stat_noun.' were '.$damage_text.'!'),
      'failure' => array(0, -75, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => $stat_type,
      'frame' => 'taunt',
      'percent' => true,
      'kickback' => array(0, 0, 0),
      'success' => array(1, -50, 0, 10, $target_robot->print_robot_name().'&#39;s '.$stat_noun.' were '.$recovery_text.'!'),
      'failure' => array(0, -75, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
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
    return mmrpg_ability::ability_function_elemental_stat_break($objects, 'attack', 'weapon systems', $damage_text, $recovery_text);
  }
  public static function ability_function_elemental_defense_break($objects, $damage_text = 'damaged', $recovery_text = 'recovered'){
    return mmrpg_ability::ability_function_elemental_stat_break($objects, 'defense', 'shield systems', $damage_text, $recovery_text);
  }
  public static function ability_function_elemental_speed_break($objects, $damage_text = 'damaged', $recovery_text = 'recovered'){
    return mmrpg_ability::ability_function_elemental_stat_break($objects, 'speed', 'mobility systems', $damage_text, $recovery_text);
  }

  // Define a static onload function to use as the common action for all elemental stat break abilities
  public static function ability_function_onload_elemental_stat_break($objects){

    // Return true on success
    return true;

  }
  // Define alias functions for each stat in attack/defense/speed stats for cleaner function calls
  public static function ability_function_onload_elemental_attack_break($objects){
    return mmrpg_ability::ability_function_onload_elemental_stat_break($objects);
  }
  public static function ability_function_onload_elemental_defense_break($objects){
    return mmrpg_ability::ability_function_onload_elemental_stat_break($objects);
  }
  public static function ability_function_onload_elemental_speed_break($objects){
    return mmrpg_ability::ability_function_onload_elemental_stat_break($objects);
  }

  // Define a static function to use as the common action for all item-core-___ abilities
  public static function item_function_core($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(0, 60, 40, 99,
        $this_player->print_player_name().' hands over an item from the inventory&hellip; <br />'.
        $this_robot->print_robot_name().' throws a '.$this_ability->print_ability_name().' at the target!'
        )
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'multipliers' => false,
      'kickback' => array(10, 0, 0),
      'success' => array(0, -90, 0, 10, $target_robot->print_robot_name().' was hit by the '.$this_ability->print_ability_name().'!'),
      'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'percent' => true,
      'multipliers' => false,
      'kickback' => array(0, 0, 0),
      'success' => array(0, -60, 0, 10, $target_robot->print_robot_name().' absorbed the '.$this_ability->print_ability_name().'!'),
      'failure' => array(0, -90, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
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