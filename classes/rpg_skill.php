<?
/**
 * Mega Man RPG Skill Object
 * <p>The base class for all skill objects in the Mega Man RPG Prototype.</p>
 */
class rpg_skill extends rpg_object {

    // Define the constructor class
    public function __construct(){

        // Update the session keys for this object
        $this->session_key = 'SKILLS';
        $this->session_token = 'skill_token';
        $this->session_id = 'skill_id';
        $this->class = 'skill';
        $this->multi = 'skills';

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

        // Collect current skill data from the function if available
        $this_skillinfo = isset($args[3]) ? $args[3] : array('skill_id' => 0, 'skill_token' => 'skill');

        if (!is_array($this_skillinfo)){
            die('!is_array($this_skillinfo){ '.print_r($this_skillinfo, true)).' }';
        }

        // Now load the skill data from the session or index
        $this->skill_load($this_skillinfo);

        // Update the session by default
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define a public function for manually loading data
    public function skill_load($this_skillinfo){

        // Collect skill index info in case we need it
        if (!isset($this_skillinfo['skill_token'])){ return false; }
        $this_indexinfo = self::get_index_info($this_skillinfo['skill_token']);

        // If the skill info was not an array, return false
        if (!is_array($this_skillinfo)){ return false; }
        // If the skill ID was not provided, return false
        //if (!isset($this_skillinfo['skill_id'])){ return false; }
        if (!isset($this_skillinfo['skill_id'])){ $this_skillinfo['skill_id'] = 0; }
        // If the skill token was not provided, return false
        if (!isset($this_skillinfo['skill_token'])){ return false; }

        // If this is a special system skill, hard-code its ID, otherwise base off robot
        $temp_system_skills = array();
        if (in_array($this_skillinfo['skill_token'], $temp_system_skills)){
            $this_skillinfo['skill_id'] = $this->player_id.'000';
        }
        // Otherwise base the ID off of the robot
        else {
            //$skill_id = $this->robot_id.str_pad($this_indexinfo['skill_id'], 3, '0', STR_PAD_LEFT);
            $skill_id = rpg_game::unique_skill_id($this->robot_id, $this_indexinfo['skill_id']);
            if (!empty($this_skillinfo['flags']['is_attachment']) || isset($this_skillinfo['attachment_token'])){
                if (isset($this_skillinfo['attachment_token'])){ $skill_id .= 'y'.strtoupper(substr(md5($this_skillinfo['attachment_token']), 0, 3)); }
                else { $skill_id .= 'z'.strtoupper(substr(md5($this_skillinfo['skill_token']), 0, 3)); }
            }
            $this_skillinfo['skill_id'] = $skill_id;
        }

        // Collect current skill data from the session if available
        $this_skillinfo_backup = $this_skillinfo;
        if (isset($_SESSION['SKILLS'][$this_skillinfo['skill_id']])){
            $this_skillinfo = $_SESSION['SKILLS'][$this_skillinfo['skill_id']];
        }
        // Otherwise, collect skill data from the index if not already
        elseif (!in_array($this_skillinfo['skill_token'], $temp_system_skills)){
            $temp_backup_id = $this_skillinfo['skill_id'];
            if (empty($this_skillinfo_backup['_parsed']) && !empty($this_indexinfo)){
                $this_skillinfo = array_replace($this_indexinfo, $this_skillinfo_backup);
            }
        }

        // If this skill's robot has customizations, apply them now
        if ($this->robot->robot_skill === $this_skillinfo['skill_token']){
            $customizable_fields = array('skill_name', 'skill_description', 'skill_description2', 'skill_parameters');
            foreach ($customizable_fields AS $field_name){
                $robot_field_name = 'robot_'.$field_name;
                if (!empty($this->robot->$robot_field_name)){
                    $this_skillinfo[$field_name] = $this->robot->$robot_field_name;
                } else {
                    $this_skillinfo[$field_name] = $this_indexinfo[$field_name];
                }

            }
        }

        // Define the internal skill values using the provided array
        $this->flags = isset($this_skillinfo['flags']) ? $this_skillinfo['flags'] : array();
        $this->counters = isset($this_skillinfo['counters']) ? $this_skillinfo['counters'] : array();
        $this->values = isset($this_skillinfo['values']) ? $this_skillinfo['values'] : array();
        $this->history = isset($this_skillinfo['history']) ? $this_skillinfo['history'] : array();
        $this->skill_id = isset($this_skillinfo['skill_id']) ? $this_skillinfo['skill_id'] : 0;
        $this->skill_token = isset($this_skillinfo['skill_token']) ? $this_skillinfo['skill_token'] : 'skill';
        $this->skill_class = isset($this_skillinfo['skill_class']) ? $this_skillinfo['skill_class'] : 'master';
        $this->skill_name = isset($this_skillinfo['skill_name']) ? $this_skillinfo['skill_name'] : 'Skill';
        $this->skill_description = isset($this_skillinfo['skill_description']) ? $this_skillinfo['skill_description'] : '';
        $this->skill_description2 = isset($this_skillinfo['skill_description2']) ? $this_skillinfo['skill_description2'] : '';
        $this->skill_parameters = isset($this_skillinfo['skill_parameters']) ? $this_skillinfo['skill_parameters'] : '';
        $this->skill_results = array();
        $this->attachment_results = array();
        $this->skill_options = array();
        $this->target_options = array();
        $this->damage_options = array();
        $this->recovery_options = array();
        $this->attachment_options = array();

        // Define the internal robot base values using the robots index array
        $this->skill_base_name = isset($this_skillinfo['skill_base_name']) ? $this_skillinfo['skill_base_name'] : $this->skill_name;
        $this->skill_base_token = isset($this_skillinfo['skill_base_token']) ? $this_skillinfo['skill_base_token'] : $this->skill_token;
        $this->skill_base_description = isset($this_skillinfo['skill_base_description']) ? $this_skillinfo['skill_base_description'] : $this->skill_description;
        $this->skill_base_description2 = isset($this_skillinfo['skill_base_description2']) ? $this_skillinfo['skill_base_description2'] : $this->skill_description2;
        $this->skill_base_parameters = isset($this_skillinfo['skill_base_parameters']) ? $this_skillinfo['skill_base_parameters'] : $this->skill_parameters;

        // Collect any functions associated with this skill
        if (!isset($this->skill_function)){
            $temp_functions_dir = preg_replace('/^action-/', '_actions/', $this->skill_token);
            $temp_functions_path = MMRPG_CONFIG_SKILLS_CONTENT_PATH.$temp_functions_dir.'/functions.php';
            if (file_exists($temp_functions_path)){ require($temp_functions_path); }
            else { $functions = array(); }
            $this->skill_function = isset($functions['skill_function']) ? $functions['skill_function'] : function(){};
            $this->skill_function_onload = isset($functions['skill_function_onload']) ? $functions['skill_function_onload'] : function(){};
            $this->skill_function_attachment = isset($functions['skill_function_attachment']) ? $functions['skill_function_attachment'] : function(){};
            $this->skill_functions_custom = array();
            foreach ($functions AS $name => $function){
                if (strpos($name, 'skill_function_') === 0){ continue; }
                elseif (!is_callable($function)){ continue; }
                $this->skill_functions_custom[$name] = $function;
            }
            unset($functions);
        }

        // Define a the default skill results
        $this->skill_results_reset();

        // Reset the skill options to default
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

    // Define a function for re-loreading the current skill from session
    public function skill_reload(){
        $this->skill_load(array(
            'skill_id' => $this->skill_id,
            'skill_token' => $this->skill_token
            ));
    }

    // Define a function for refreshing this skill and running onload actions
    public function trigger_onload($force = false){

        // Trigger the onload function if not already called
        if ($force || !rpg_game::onload_triggered('skill', $this->skill_id)){
            rpg_game::onload_triggered('skill', $this->skill_id, true);
            //error_log('---- trigger_onload() for skill '.$this->skill_id.PHP_EOL);
            $temp_function = $this->skill_function_onload;
            $temp_result = $temp_function(self::get_objects());
        }

    }

    // Define alias functions for updating specific fields quickly

    public function get_id(){ return intval($this->get_info('skill_id')); }
    public function set_id($value){ $this->set_info('skill_id', intval($value)); }

    public function get_name(){ return $this->get_info('skill_name'); }
    public function set_name($value){ $this->set_info('skill_name', $value); }
    public function get_base_name(){ return $this->get_info('skill_base_name'); }
    public function set_base_name($value){ $this->set_info('skill_base_name', $value); }
    public function reset_name(){ $this->set_info('skill_name', $this->get_info('skill_base_name')); }

    public function get_token(){ return $this->get_info('skill_token'); }
    public function set_token($value){ $this->set_info('skill_token', $value); }

    public function get_description(){ return $this->get_info('skill_description'); }
    public function set_description($value){ $this->set_info('skill_description', $value); }
    public function get_base_description(){ return $this->get_info('skill_base_description'); }
    public function set_base_description($value){ $this->set_info('skill_base_description', $value); }

    public function get_class(){ return $this->get_info('skill_class'); }
    public function set_class($value){ $this->set_info('skill_class', $value); }

    public function get_functions(){ return $this->get_info('skill_functions'); }
    public function set_functions($value){ $this->set_info('skill_functions', $value); }

    public function get_results(){ return $this->get_info('skill_results'); }
    public function set_results($value){ $this->set_info('skill_results', $value); }

    public function get_options(){ return $this->get_info('skill_options'); }
    public function set_options($value){ $this->set_info('skill_options', $value); }

    public function get_target_options(){ return $this->get_info('target_options'); }
    public function set_target_options($value){ $this->set_info('target_options', $value); }

    // Define a public function for getting all global objects related to this skill
    private function get_objects($extra_objects = array()){

        // Collect refs to all the known objects for this skill
        $objects = array(
            'this_battle' => $this->battle,
            'this_player' => $this->player,
            'this_robot' => $this->robot,
            'this_skill' => $this
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

    // Define public print functions for markup generation
    public function print_name($pseudo_name = ''){
        $print_name = $this->skill_name;
        $print_type = 'none';
        if (strstr($this->skill_token, '-subcore')){ $print_type = str_replace('-subcore', '', $this->skill_token); }
        if (!empty($pseudo_name)){ $print_name = $pseudo_name; }
        return '<span class="skill_name skill_type type_'.$print_type.'">'.$print_name.'</span>';
    }
    public function print_token(){
        return '<span class="skill_token">'.$this->skill_token.'</span>';
    }
    public function print_description(){
        return '<span class="skill_description">'.$this->skill_description.'</span>';
    }

    // Define a trigger for using one of this robot's skills
    public function reset_skill($target_robot, $this_skill){

        // Update internal variables
        $this_skill->update_session();

        // Return the skill results
        return $this_skill->skill_results;
    }

    // Define a public function for easily resetting result options
    public function skill_results_reset(){
        // Redfine the result options as an empty array
        $this->skill_results = array();
        // Populate the array with defaults
        $this->skill_results['total_result'] = '';
        $this->skill_results['total_actions'] = 0;
        $this->skill_results['total_strikes'] = 0;
        $this->skill_results['total_misses'] = 0;
        $this->skill_results['total_amount'] = 0;
        $this->skill_results['total_overkill'] = 0;
        $this->skill_results['this_result'] = '';
        $this->skill_results['this_amount'] = 0;
        $this->skill_results['this_overkill'] = 0;
        $this->skill_results['this_text'] = '';
        $this->skill_results['counter_criticals'] = 0;
        $this->skill_results['counter_weaknesses'] = 0;
        $this->skill_results['counter_resistances'] = 0;
        $this->skill_results['counter_affinities'] = 0;
        $this->skill_results['counter_immunities'] = 0;
        $this->skill_results['counter_coreboosts'] = 0;
        $this->skill_results['counter_omegaboosts'] = 0;
        $this->skill_results['flag_critical'] = false;
        $this->skill_results['flag_weakness'] = false;
        $this->skill_results['flag_resistance'] = false;
        $this->skill_results['flag_affinity'] = false;
        $this->skill_results['flag_immunity'] = false;
        $this->skill_results['flag_coreboost'] = false;
        $this->skill_results['flag_omegaboost'] = false;
        // Update this skill's data
        $this->update_session();
        // Return the resuling array
        return $this->skill_results;
    }

    // Define a public function for easily resetting target options
    public function target_options_reset(){
        // Redfine the options variables as an empty array
        $this->target_options = array();
        // Populate the array with defaults
        $this->target_options['target_kind'] = 'energy';
        $this->target_options['target_frame'] = 'shoot';
        $this->target_options['skill_success_frame'] = 1;
        $this->target_options['skill_success_frame_span'] = 1;
        $this->target_options['skill_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->target_options['skill_failure_frame'] = 1;
        $this->target_options['skill_failure_frame_span'] = 1;
        $this->target_options['skill_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->target_options['target_kickback'] = array('x' => 0, 'y' => 0, 'z' => 0);
        $this->target_options['target_header'] = $this->robot->robot_name.'&#39;s '.$this->skill_name;
        $this->target_options['target_text'] = "{$this->robot->print_name()} uses {$this->print_name()}!";
        // Update this skill's data
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
            $this->target_options['skill_success_frame'] = $target_options['success'][0];
            $this->target_options['skill_success_frame_offset']['x'] = $target_options['success'][1];
            $this->target_options['skill_success_frame_offset']['y'] = $target_options['success'][2];
            $this->target_options['skill_success_frame_offset']['z'] = $target_options['success'][3];
            $this->target_options['target_text'] = $target_options['success'][4];
            $this->target_options['skill_success_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($target_options['failure'])){
            $this->target_options['skill_failure_frame'] = $target_options['failure'][0];
            $this->target_options['skill_failure_frame_offset']['x'] = $target_options['failure'][1];
            $this->target_options['skill_failure_frame_offset']['y'] = $target_options['failure'][2];
            $this->target_options['skill_failure_frame_offset']['z'] = $target_options['failure'][3];
            $this->target_options['target_text'] = $target_options['failure'][4];
            $this->target_options['skill_failure_frame_span'] = isset($target_options['success'][5]) ? $target_options['success'][5] : 1;
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
        $this->damage_options['damage_header'] = $this->robot->robot_name.'&#39;s '.$this->skill_name;
        $this->damage_options['damage_frame'] = 'damage';
        $this->damage_options['skill_success_frame'] = 1;
        $this->damage_options['skill_success_frame_span'] = 1;
        $this->damage_options['skill_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['skill_failure_frame'] = 1;
        $this->damage_options['skill_failure_frame_span'] = 1;
        $this->damage_options['skill_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->damage_options['damage_kind'] = 'energy';
        $this->damage_options['damage_type'] = '';
        $this->damage_options['damage_type2'] = '';
        $this->damage_options['damage_amount'] = '';
        $this->damage_options['damage_amount2'] = '';
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
        $this->damage_options['success_text'] = 'The skill hit!';
        $this->damage_options['failure_text'] = 'The skill missed&hellip;';
        $this->damage_options['immunity_text'] = 'The skill had no effect&hellip;';
        $this->damage_options['critical_text'] = 'It&#39;s a critical hit!';
        $this->damage_options['weakness_text'] = 'It&#39;s super effective!';
        $this->damage_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $this->damage_options['weakness_resistance_text'] = ''; //"It's a super effective resisted hit!';
        $this->damage_options['weakness_critical_text'] = 'It&#39;s a super effective critical hit!';
        $this->damage_options['resistance_critical_text'] = 'It&#39;s a critical hit, but not very effective&hellip;';
        // Update this skill's data
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
            $this->damage_options['skill_success_frame'] = $damage_options['success'][0];
            $this->damage_options['skill_success_frame_offset']['x'] = $damage_options['success'][1];
            $this->damage_options['skill_success_frame_offset']['y'] = $damage_options['success'][2];
            $this->damage_options['skill_success_frame_offset']['z'] = $damage_options['success'][3];
            $this->damage_options['success_text'] = $damage_options['success'][4];
            $this->damage_options['skill_success_frame_span'] = isset($damage_options['success'][5]) ? $damage_options['success'][5] : 1;
        }
        // Update internal variables with failure options, if set
        if (isset($damage_options['failure'])){
            $this->damage_options['skill_failure_frame'] = $damage_options['failure'][0];
            $this->damage_options['skill_failure_frame_offset']['x'] = $damage_options['failure'][1];
            $this->damage_options['skill_failure_frame_offset']['y'] = $damage_options['failure'][2];
            $this->damage_options['skill_failure_frame_offset']['z'] = $damage_options['failure'][3];
            $this->damage_options['failure_text'] = $damage_options['failure'][4];
            $this->damage_options['skill_failure_frame_span'] = isset($damage_options['failure'][5]) ? $damage_options['failure'][5] : 1;
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
        $this->recovery_options['recovery_header'] = $this->robot->robot_name.'&#39;s '.$this->skill_name;
        $this->recovery_options['recovery_frame'] = 'defend';
        $this->recovery_options['skill_success_frame'] = 1;
        $this->recovery_options['skill_success_frame_span'] = 1;
        $this->recovery_options['skill_success_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['skill_failure_frame'] = 1;
        $this->recovery_options['skill_failure_frame_span'] = 1;
        $this->recovery_options['skill_failure_frame_offset'] = array('x' => 0, 'y' => 0, 'z' => 1);
        $this->recovery_options['recovery_kind'] = 'energy';
        $this->recovery_options['recovery_type'] = '';
        $this->recovery_options['recovery_type2'] = '';
        $this->recovery_options['recovery_amount'] = '';
        $this->recovery_options['recovery_amount2'] = '';
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
        $this->recovery_options['recovery_type'] = '';
        $this->recovery_options['recovery_type2'] = '';
        $this->recovery_options['success_text'] = 'The skill worked!';
        $this->recovery_options['failure_text'] = 'The skill failed&hellip;';
        $this->recovery_options['immunity_text'] = 'The skill had no effect&hellip;';
        $this->recovery_options['critical_text'] = 'It&#39;s a lucky boost!';
        $this->recovery_options['affinity_text'] = 'It&#39;s super effective!';
        $this->recovery_options['resistance_text'] = 'It&#39;s not very effective&hellip;';
        $this->recovery_options['affinity_resistance_text'] = ''; //'It&#39;s a super effective resisted hit!';
        $this->recovery_options['affinity_critical_text'] = 'It&#39;s a super effective lucky boost!';
        $this->recovery_options['resistance_critical_text'] = 'It&#39;s a lucky boost, but not very effective&hellip;';
        // Update this skill's data
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
            $this->recovery_options['skill_success_frame'] = $recovery_options['success'][0];
            $this->recovery_options['skill_success_frame_offset']['x'] = $recovery_options['success'][1];
            $this->recovery_options['skill_success_frame_offset']['y'] = $recovery_options['success'][2];
            $this->recovery_options['skill_success_frame_offset']['z'] = $recovery_options['success'][3];
            $this->recovery_options['success_text'] = $recovery_options['success'][4];
            $this->recovery_options['skill_success_frame_span'] = isset($recovery_options['success'][5]) ? $recovery_options['success'][5] : 1;
        }
        // Update internal variabels with failure options, if set
        if (isset($recovery_options['failure'])){
            $this->recovery_options['skill_failure_frame'] = $recovery_options['failure'][0];
            $this->recovery_options['skill_failure_frame_offset']['x'] = $recovery_options['failure'][1];
            $this->recovery_options['skill_failure_frame_offset']['y'] = $recovery_options['failure'][2];
            $this->recovery_options['skill_failure_frame_offset']['z'] = $recovery_options['failure'][3];
            $this->recovery_options['failure_text'] = $recovery_options['failure'][4];
            $this->recovery_options['skill_failure_frame_span'] = isset($recovery_options['failure'][5]) ? $recovery_options['failure'][5] : 1;
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
        // Update this skill's data
        $this->update_session();
        // Return the resuling array
        return $this->attachment_options;
    }


    // Define a public function for easily updating attachment options
    public function attachment_options_update($attachment_options = array()){
        // Update this skill's data
        $this->update_session();
        // Return the new array
        return $this->attachment_options;
    }

    // Define a function for generating skill canvas variables
    public function canvas_markup($options, $player_data, $robot_data){

        // Delegate markup generation to the canvas class
        return rpg_canvas::skill_markup($this, $options, $player_data, $robot_data);

    }

    // Define a function for generating skill console variables
    public function console_markup($options, $player_data, $robot_data){

        // Delegate markup generation to the console class
        return rpg_console::skill_markup($this, $options, $player_data, $robot_data);

    }


    // -- INDEX FUNCTIONS -- //

    /**
     * Get a list of all skill index fields as an array or, optionally, imploded into a string
     * @param bool $implode
     * @param string $table (optional)
     * @return mixed
     */
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various index fields for skill objects
        $index_fields = array(
            'skill_id',
            'skill_token',
            'skill_name',
            'skill_class',
            'skill_description',
            'skill_description2',
            'skill_parameters',
            'skill_flag_hidden',
            'skill_flag_complete',
            'skill_flag_published',
            'skill_flag_protected'
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
            'skill_parameters'
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
            'skill_group',
            'skill_order'
            );

        // Implode the index fields into a string if requested
        if ($implode){
            $json_index_fields = implode(', ', $json_index_fields);
        }

        // Return the index fields, array or string
        return $json_index_fields;

    }

    /**
     * Get the entire skill index array with parsed info
     * @param bool $parse_data
     * @return array
     */
    public static function get_index($include_hidden = false, $include_unpublished = false, $filter_class = '', $include_tokens = array()){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND skills.skill_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND skills.skill_flag_published = 1 '; }
        if (!empty($filter_class)){ $temp_where .= "AND skills.skill_class = '{$filter_class}' "; }
        if (!empty($include_tokens)){
            $include_string = $include_tokens;
            array_walk($include_string, function(&$s){ $s = "'{$s}'"; });
            $include_tokens = implode(', ', $include_string);
            $temp_where .= 'OR skills.skill_token IN ('.$include_tokens.') ';
        }

        // Define a static array for cached queries
        static $index_cache = array();

        // Define the static token for this query
        $cache_token = md5($temp_where);

        // If already found, return the collected index directly, else collect from DB
        if (!empty($index_cache[$cache_token])){

            // Return the cached index array
            return $index_cache[$cache_token];

        }

        // Collect every type's info from the database index
        $skill_fields = rpg_skill::get_index_fields(true, 'skills');
        $skill_index = $db->get_array_list("SELECT
            {$skill_fields},
            groups.group_token AS skill_group,
            tokens.token_order AS skill_order
            FROM mmrpg_index_skills AS skills
            LEFT JOIN mmrpg_index_skills_groups_tokens AS tokens ON tokens.skill_token = skills.skill_token
            LEFT JOIN mmrpg_index_skills_groups AS groups ON groups.group_class = tokens.group_class AND groups.group_token = tokens.group_token
            WHERE skill_id <> 0 {$temp_where}
            ORDER BY
            groups.group_order ASC,
            tokens.token_order ASC
            ;", 'skill_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($skill_index)){ $skill_index = self::parse_index($skill_index); }
        else { $skill_index = array(); }

        // Return the cached index array
        $index_cache[$cache_token] = $skill_index;
        return $index_cache[$cache_token];

    }


    /**
     * Get the tokens for all skills in the global index
     * @return array
     */
    public static function get_index_tokens($include_hidden = false, $include_unpublished = false){

        // Pull in global variables
        $db = cms_database::get_database();

        // Define the query condition based on args
        $temp_where = '';
        if (!$include_hidden){ $temp_where .= 'AND skill_flag_hidden = 0 '; }
        if (!$include_unpublished){ $temp_where .= 'AND skill_flag_published = 1 '; }

        // Collect an array of skill tokens from the database
        $skill_index = $db->get_array_list("SELECT skill_token FROM mmrpg_index_skills WHERE skill_id <> 0 {$temp_where};", 'skill_token');

        // Return the tokens if not empty, else nothing
        if (!empty($skill_index)){
            $skill_tokens = array_keys($skill_index);
            return $skill_tokens;
        } else {
            return array();
        }

    }

    // Define a function for pulling a custom skill index
    public static function get_index_custom($skill_tokens = array()){

        // Pull in global variables
        $db = cms_database::get_database();

        // Generate a token string for the database query
        $skill_tokens_string = array();
        foreach ($skill_tokens AS $skill_token){ $skill_tokens_string[] = "'{$skill_token}'"; }
        $skill_tokens_string = implode(', ', $skill_tokens_string);

        // Collect the requested skill's info from the database index
        $skill_fields = self::get_index_fields(true);
        $skill_index = $db->get_array_list("SELECT {$skill_fields} FROM mmrpg_index_skills WHERE skill_token IN ({$skill_tokens_string});", 'skill_token');

        // Parse and return the data if not empty, else nothing
        if (!empty($skill_index)){
            $skill_index = self::parse_index($skill_index);
            return $skill_index;
        } else {
            return array();
        }

    }

    // Define a public function for collecting index data from the database
    public static function get_index_info($skill_token){

        // If empty, return nothing
        if (empty($skill_token)){ return false; };

        // Collect a local copy of the skill index
        static $skill_index = false;
        static $skill_index_byid = false;
        if ($skill_index === false){
            $skill_index_byid = array();
            $skill_index = self::get_index(true, true);
            if (empty($skill_index)){ $skill_index = array(); }
            foreach ($skill_index AS $token => $skill){ $skill_index_byid[$skill['skill_id']] = $token; }
        }

        // Return either by token or by ID if number provided
        if (is_numeric($skill_token)){
            // Search by skill ID
            $skill_id = $skill_token;
            if (!empty($skill_index_byid[$skill_id])){ return $skill_index[$skill_index_byid[$skill_id]]; }
            else { return false; }
        } else {
            // Search by skill TOKEN
            if (!empty($skill_index[$skill_token])){ return $skill_index[$skill_token]; }
            else { return false; }
        }

    }

    // Define a public function for parsing a skill index array in bulk
    public static function parse_index($skill_index){

        // Loop through each entry and parse its data
        foreach ($skill_index AS $token => $info){
            $skill_index[$token] = self::parse_index_info($info);
        }

        // Return the parsed index
        return $skill_index;

    }

    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($skill_info){

        // Return false if empty
        if (empty($skill_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($skill_info['_parsed'])){ return $skill_info; }
        else { $skill_info['_parsed'] = true; }

        // Explode the base and animation indexes into an array
        $temp_field_names = self::get_json_index_fields();
        foreach ($temp_field_names AS $field_name){
            if (!empty($skill_info[$field_name])){ $skill_info[$field_name] = json_decode($skill_info[$field_name], true); }
            else { $skill_info[$field_name] = array(); }
        }

        // Return the parsed skill info
        return $skill_info;
    }

    // Define a public function for parsing skill parameters w/ optional robot-based customizations
    public static function parse_skill_details($skill_info, $robot_info = array()){
        $details = array();
        $details['skill_name'] = !empty($robot_info['robot_skill_name']) ? $robot_info['robot_skill_name'] : $skill_info['skill_name'];
        $details['skill_description'] = !empty($robot_info['robot_skill_description']) ? $robot_info['robot_skill_description'] : $skill_info['skill_description'];
        $details['skill_description2'] = !empty($robot_info['robot_skill_description2']) ? $robot_info['robot_skill_description2'] : $skill_info['skill_description2'];
        return $details;
    }

    // Define a public function for parsing skill parameters w/ optional robot-based customizations
    public static function parse_skill_parameters($skill_info, $robot_info = array()){
        $base_parameters = !empty($skill_info['skill_parameters']) ? $skill_info['skill_parameters'] : array();
        $robot_parameters = !empty($robot_info['robot_skill_parameters']) ? $robot_info['robot_skill_parameters'] : array();
        $parameters = array_merge($base_parameters, $robot_parameters);
        return $parameters;
    }

    // Define a public function for updating a given skill info array with customized parameters
    public static function update_skill_with_customizations(&$skill_info, $custom_details, $custom_parameters){
        $param_find = $param_replace = array();
        foreach ($custom_parameters AS $key => $value){
            $param_find[] = '{'.$key.'}'; $param_replace[] = $value;
            $param_find[] = '{^'.$key.'}'; $param_replace[] = ucfirst($value);
            $param_find[] = '{^^'.$key.'}'; $param_replace[] = strtoupper($value);
        }
        foreach ($skill_info AS $key => $value){
            if (!isset($custom_details[$key])){ continue; }
            $value = str_replace($param_find, $param_replace, $custom_details[$key]);
            $skill_info[$key] = $value;
        }
        $skill_info['skill_parameters'] = $custom_parameters;
    }


    // -- PRINT FUNCTIONS -- /

    // Define a static function for printing out the skill's title markup
    public static function print_editor_title_markup($robot_info, $skill_info, $print_options = array()){
        if (empty($robot_info)){ return false; }
        if (empty($skill_info)){ return false; }
        $temp_skill_title = '';
        $temp_skill_title .= $skill_info['skill_name'];
        if (!empty($skill_info['skill_description'])){
            $temp_description = $skill_info['skill_description'];
            $temp_skill_title .= ' // '.$temp_description;
        }
        // Return the generated option markup
        return $temp_skill_title;
    }

    // Define a public function for recalculating internal counters
    public function update_variables(){

        // Return true on success
        return true;

    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['SKILLS'][$this->skill_id] = $this_data;
        $this->battle->values['skills'][$this->skill_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal skill fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_token' => $this->player_token,
            'robot_id' => $this->robot_id,
            'robot_token' => $this->robot_token,
            'skill_id' => $this->skill_id,
            'skill_name' => $this->skill_name,
            'skill_token' => $this->skill_token,
            'skill_class' => $this->skill_class,
            'skill_description' => $this->skill_description,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

}
?>