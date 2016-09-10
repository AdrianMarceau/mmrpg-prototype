<?
/**
 * Mega Man RPG Robot Object
 * <p>The base class for all robot objects in the Mega Man RPG Prototype.</p>
 */
class rpg_robot extends rpg_object {

    // Define the constructor class
    public function rpg_robot(){

        // Update the session keys for this object
        $this->session_key = 'ROBOTS';
        $this->session_token = 'robot_token';
        $this->session_id = 'robot_id';
        $this->class = 'robot';
        $this->multi = 'robots';

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal battle pointer
        $this->battle = isset($args[0]) ? $args[0] : $GLOBALS['this_battle'];
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Define the internal battle pointer
        $this->field = isset($this->battle->field) ? $this->battle->field : $GLOBALS['this_field'];
        $this->field_id = $this->battle->battle_id;
        $this->field_token = $this->battle->battle_token;

        // Define the internal player values using the provided array
        $this->player = isset($args[1]) ? $args[1] : $GLOBALS['this_player'];
        $this->player_id = $this->player->player_id;
        $this->player_token = $this->player->player_token;

        // Collect current robot data from the function if available
        $this_robotinfo = isset($args[2]) && !empty($args[2]) ? $args[2] : array('robot_id' => 0, 'robot_token' => 'robot');

        // Now load the robot data from the session or index
        if (!$this->robot_load($this_robotinfo)){
            // Robot data could not be loaded
            die('Robot data could not be loaded :<br />$this_robotinfo = <pre>'.print_r($this_robotinfo, true).'</pre>');
        }

        // Return true on success
        return true;

    }

    // Define a function for getting the session info
    public static function get_session_field($robot_id, $field_token){
        if (empty($robot_id) || empty($field_token)){ return false; }
        elseif (!empty($_SESSION['ROBOTS'][$robot_id][$field_token])){ return $_SESSION['ROBOTS'][$robot_id][$field_token]; }
        else { return false; }
    }

    // Define a function for setting the session info
    public static function set_session_field($robot_id, $field_token, $field_value){
        if (empty($robot_id) || empty($field_token)){ return false; }
        else { $_SESSION['ROBOTS'][$robot_id][$field_token] = $field_value; }
        return true;
    }

    // Define a public function for manually loading data
    public function robot_load($this_robotinfo){

        // If the robot info was not an array, return false
        if (!is_array($this_robotinfo)){ die("robot info must be an array!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }
        // If the robot ID was not provided, return false
        if (!isset($this_robotinfo['robot_id'])){ die("robot id must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }
        // If the robot token was not provided, return false
        if (!isset($this_robotinfo['robot_token'])){ die("robot token must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }

        // Collect current robot data from the session if available
        $this_robotinfo_backup = $this_robotinfo;
        if (isset($_SESSION['ROBOTS'][$this_robotinfo['robot_id']])){
            $this_robotinfo = $_SESSION['ROBOTS'][$this_robotinfo['robot_id']];
        }
        // Otherwise, collect robot data from the index
        else {
            if (empty($this_robotinfo_backup['_parsed'])){
                if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "\$this_robotinfo = rpg_robot::get_index_info({$this_robotinfo['robot_token']}); on line ".__LINE__;  }
                $this_robotinfo = rpg_robot::get_index_info($this_robotinfo['robot_token']);
                $this_robotinfo = array_replace($this_robotinfo, $this_robotinfo_backup);
            }
        }

        // DEBUG
        /*
        if (false && $this_robotinfo['robot_token'] == 'mega-man'){
            die(__LINE__.
                ':: <pre>$this_robotinfo_backup:'.print_r($this_robotinfo_backup, true).'</pre>'.
                ':: <pre>$this_robotinfo:'.print_r($this_robotinfo, true).'</pre>');
        }
        */


        // Define the internal robot values using the provided array
        $this->flags = isset($this_robotinfo['flags']) ? $this_robotinfo['flags'] : array();
        $this->counters = isset($this_robotinfo['counters']) ? $this_robotinfo['counters'] : array();
        $this->values = isset($this_robotinfo['values']) ? $this_robotinfo['values'] : array();
        $this->history = isset($this_robotinfo['history']) ? $this_robotinfo['history'] : array();
        $this->robot_key = isset($this_robotinfo['robot_key']) ? $this_robotinfo['robot_key'] : 0;
        $this->robot_id = isset($this_robotinfo['robot_id']) ? $this_robotinfo['robot_id'] : false;
        $this->robot_number = isset($this_robotinfo['robot_number']) ? $this_robotinfo['robot_number'] : 'RPG000';
        $this->robot_name = isset($this_robotinfo['robot_name']) ? $this_robotinfo['robot_name'] : 'Robot';
        $this->robot_token = isset($this_robotinfo['robot_token']) ? $this_robotinfo['robot_token'] : 'robot';
        $this->robot_field = isset($this_robotinfo['robot_field']) ? $this_robotinfo['robot_field'] : 'field';
        $this->robot_class = isset($this_robotinfo['robot_class']) ? $this_robotinfo['robot_class'] : 'master';
        $this->robot_image = isset($this_robotinfo['robot_image']) ? $this_robotinfo['robot_image'] : $this->robot_token;
        $this->robot_image_size = isset($this_robotinfo['robot_image_size']) ? $this_robotinfo['robot_image_size'] : 40;
        $this->robot_image_overlay = isset($this_robotinfo['robot_image_overlay']) ? $this_robotinfo['robot_image_overlay'] : array();
        $this->robot_image_alts = isset($this_robotinfo['robot_image_alts']) ? $this_robotinfo['robot_image_alts'] : array();
        $this->robot_core = isset($this_robotinfo['robot_core']) ? $this_robotinfo['robot_core'] : false;
        $this->robot_core2 = isset($this_robotinfo['robot_core2']) ? $this_robotinfo['robot_core2'] : false;
        $this->robot_description = isset($this_robotinfo['robot_description']) ? $this_robotinfo['robot_description'] : '';
        $this->robot_experience = isset($this_robotinfo['robot_experience']) ? $this_robotinfo['robot_experience'] : (isset($this_robotinfo['robot_points']) ? $this_robotinfo['robot_points'] : 0);
        $this->robot_level = isset($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : (!empty($this->robot_experience) ? $this->robot_experience / 1000 : 0) + 1;
        $this->robot_energy = isset($this_robotinfo['robot_energy']) ? $this_robotinfo['robot_energy'] : 1;
        $this->robot_weapons = isset($this_robotinfo['robot_weapons']) ? $this_robotinfo['robot_weapons'] : 10;
        $this->robot_attack = isset($this_robotinfo['robot_attack']) ? $this_robotinfo['robot_attack'] : 1;
        $this->robot_defense = isset($this_robotinfo['robot_defense']) ? $this_robotinfo['robot_defense'] : 1;
        $this->robot_speed = isset($this_robotinfo['robot_speed']) ? $this_robotinfo['robot_speed'] : 1;
        $this->robot_weaknesses = isset($this_robotinfo['robot_weaknesses']) ? $this_robotinfo['robot_weaknesses'] : array();
        $this->robot_resistances = isset($this_robotinfo['robot_resistances']) ? $this_robotinfo['robot_resistances'] : array();
        $this->robot_affinities = isset($this_robotinfo['robot_affinities']) ? $this_robotinfo['robot_affinities'] : array();
        $this->robot_immunities = isset($this_robotinfo['robot_immunities']) ? $this_robotinfo['robot_immunities'] : array();
        $this->robot_abilities = isset($this_robotinfo['robot_abilities']) ? $this_robotinfo['robot_abilities'] : array();
        $this->robot_attachments = isset($this_robotinfo['robot_attachments']) ? $this_robotinfo['robot_attachments'] : array();
        $this->robot_quotes = isset($this_robotinfo['robot_quotes']) ? $this_robotinfo['robot_quotes'] : array();
        $this->robot_status = isset($this_robotinfo['robot_status']) ? $this_robotinfo['robot_status'] : 'active';
        $this->robot_position = isset($this_robotinfo['robot_position']) ? $this_robotinfo['robot_position'] : 'bench';
        $this->robot_stance = isset($this_robotinfo['robot_stance']) ? $this_robotinfo['robot_stance'] : 'base';
        $this->robot_rewards = isset($this_robotinfo['robot_rewards']) ? $this_robotinfo['robot_rewards'] : array();
        $this->robot_functions = isset($this_robotinfo['robot_functions']) ? $this_robotinfo['robot_functions'] : 'robots/robot.php';
        $this->robot_frame = isset($this_robotinfo['robot_frame']) ? $this_robotinfo['robot_frame'] : 'base';
        //$this->robot_frame_index = isset($this_robotinfo['robot_frame_index']) ? $this_robotinfo['robot_frame_index'] : array('base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage','base2');
        $this->robot_frame_offset = !empty($this_robotinfo['robot_frame_offset']) ? $this_robotinfo['robot_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->robot_frame_classes = isset($this_robotinfo['robot_frame_classes']) ? $this_robotinfo['robot_frame_classes'] : '';
        $this->robot_frame_styles = isset($this_robotinfo['robot_frame_styles']) ? $this_robotinfo['robot_frame_styles'] : '';
        $this->robot_detail_styles = isset($this_robotinfo['robot_detail_styles']) ? $this_robotinfo['robot_detail_styles'] : '';
        $this->robot_original_player = isset($this_robotinfo['robot_original_player']) ? $this_robotinfo['robot_original_player'] : $this->player_token;
        $this->robot_string = isset($this_robotinfo['robot_string']) ? $this_robotinfo['robot_string'] : $this->robot_id.'_'.$this->robot_token;

        // Collect any functions associated with this ability
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->robot_functions) ? $this->robot_functions : 'robots/functions.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->robot_function = isset($ability['robot_function']) ? $ability['robot_function'] : function(){};
        $this->robot_function_onload = isset($ability['robot_function_onload']) ? $ability['robot_function_onload'] : function(){};
        unset($ability);

        // Define the internal robot base values using the robots index array
        $this->robot_base_name = isset($this_robotinfo['robot_base_name']) ? $this_robotinfo['robot_base_name'] : $this->robot_name;
        $this->robot_base_token = isset($this_robotinfo['robot_base_token']) ? $this_robotinfo['robot_base_token'] : $this->robot_token;

        $this->robot_base_image = isset($this_robotinfo['robot_base_image']) ? $this_robotinfo['robot_base_image'] : $this->robot_base_token;
        $this->robot_base_image_size = isset($this_robotinfo['robot_base_image_size']) ? $this_robotinfo['robot_base_image_size'] : $this->robot_image_size;
        $this->robot_base_image_overlay = isset($this_robotinfo['robot_base_image_overlay']) ? $this_robotinfo['robot_base_image_overlay'] : $this->robot_image_overlay;

        $this->robot_base_core = isset($this_robotinfo['robot_base_core']) ? $this_robotinfo['robot_base_core'] : $this->robot_core;
        $this->robot_base_core2 = isset($this_robotinfo['robot_base_core2']) ? $this_robotinfo['robot_base_core2'] : $this->robot_core2;

        $this->robot_base_description = isset($this_robotinfo['robot_base_description']) ? $this_robotinfo['robot_base_description'] : $this->robot_description;

        $this->robot_base_experience = isset($this_robotinfo['robot_base_experience']) ? $this_robotinfo['robot_base_experience'] : $this->robot_experience;
        $this->robot_base_level = isset($this_robotinfo['robot_base_level']) ? $this_robotinfo['robot_base_level'] : $this->robot_level;

        $this->robot_base_energy = isset($this_robotinfo['robot_base_energy']) ? $this_robotinfo['robot_base_energy'] : $this->robot_energy;
        $this->robot_base_weapons = isset($this_robotinfo['robot_base_weapons']) ? $this_robotinfo['robot_base_weapons'] : $this->robot_weapons;
        $this->robot_base_attack = isset($this_robotinfo['robot_base_attack']) ? $this_robotinfo['robot_base_attack'] : $this->robot_attack;
        $this->robot_base_defense = isset($this_robotinfo['robot_base_defense']) ? $this_robotinfo['robot_base_defense'] : $this->robot_defense;
        $this->robot_base_speed = isset($this_robotinfo['robot_base_speed']) ? $this_robotinfo['robot_base_speed'] : $this->robot_speed;

        $this->robot_max_energy = isset($this_robotinfo['robot_max_energy']) ? $this_robotinfo['robot_max_energy'] : $this->robot_base_energy;
        $this->robot_max_weapons = isset($this_robotinfo['robot_max_weapons']) ? $this_robotinfo['robot_max_weapons'] : $this->robot_base_weapons;
        $this->robot_max_attack = isset($this_robotinfo['robot_max_attack']) ? $this_robotinfo['robot_max_attack'] : $this->robot_base_attack;
        $this->robot_max_defense = isset($this_robotinfo['robot_max_defense']) ? $this_robotinfo['robot_max_defense'] : $this->robot_base_defense;
        $this->robot_max_speed = isset($this_robotinfo['robot_max_speed']) ? $this_robotinfo['robot_max_speed'] : $this->robot_base_speed;

        $this->robot_base_weaknesses = isset($this_robotinfo['robot_base_weaknesses']) ? $this_robotinfo['robot_base_weaknesses'] : $this->robot_weaknesses;
        $this->robot_base_resistances = isset($this_robotinfo['robot_base_resistances']) ? $this_robotinfo['robot_base_resistances'] : $this->robot_resistances;
        $this->robot_base_affinities = isset($this_robotinfo['robot_base_affinities']) ? $this_robotinfo['robot_base_affinities'] : $this->robot_affinities;
        $this->robot_base_immunities = isset($this_robotinfo['robot_base_immunities']) ? $this_robotinfo['robot_base_immunities'] : $this->robot_immunities;

        //$this->robot_base_abilities = isset($this_robotinfo['robot_base_abilities']) ? $this_robotinfo['robot_base_abilities'] : $this->robot_abilities;
        $this->robot_base_attachments = isset($this_robotinfo['robot_base_attachments']) ? $this_robotinfo['robot_base_attachments'] : $this->robot_attachments;

        $this->robot_base_quotes = isset($this_robotinfo['robot_base_quotes']) ? $this_robotinfo['robot_base_quotes'] : $this->robot_quotes;

        // Limit all stats to 9999 for display purposes (and balance I guess)
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

        // If this is a player-controlled robot, load settings from session
        if ($this->player->player_side == 'left' && empty($this->flags['apply_session_settings'])){

            // Collect the abilities for this robot from the session
            $temp_robot_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);

            // If this is a player-controlled robot, load abilities from session
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

            /*
            // If there is a held item set, apply it
            if (!empty($temp_robot_settings['robot_item'])){
                $this->robot_item = $temp_robot_settings['robot_item'];
                $this->robot_base_item = $this->robot_item;
            }
            */

            // Set the session settings flag to true
            $this->flags['apply_session_settings'] = true;

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

        // Update the session variable
        $this->update_session();

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
    public function get_stats(){
        $stats = array();
        $stats['robot_energy'] = $this->get_energy();
        $stats['robot_weapons'] = $this->get_weapons();
        $stats['robot_attack'] = $this->get_attack();
        $stats['robot_defense'] = $this->get_defense();
        $stats['robot_speed'] = $this->get_speed();
        return $stats;
    }
    public function get_base_stat($stat){ return $this->get_info('robot_base_'.$stat); }
    public function set_base_stat($stat, $value){ $this->set_info('robot_base_'.$stat, $value); }
    public function get_base_stats(){
        $stats = array();
        $stats['robot_base_energy'] = $this->get_base_energy();
        $stats['robot_base_weapons'] = $this->get_base_weapons();
        $stats['robot_base_attack'] = $this->get_base_attack();
        $stats['robot_base_defense'] = $this->get_base_defense();
        $stats['robot_base_speed'] = $this->get_base_speed();
        return $stats;
    }

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

    public function get_attachment($token){ return $this->get_info('robot_attachments', $token); }
    public function set_attachment($token, $value){ $this->set_info('robot_attachments', $token, $value); }
    public function unset_attachment($token){ return $this->unset_info('robot_attachments', $token); }

    public function get_attachments(){ return $this->get_info('robot_attachments'); }
    public function set_attachments($value){ $this->set_info('robot_attachments', $value); }
    public function has_attachments(){ return $this->get_info('robot_attachments') ? true : false; }
    public function has_attachment($token){ return $this->get_info('robot_attachments', $token) ? true : false; }
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

    public function get_frame_offset(){
        $args = func_get_args();
        if (isset($args[0])){ return $this->get_info('robot_frame_offset', $args[0]); }
        else { return $this->get_info('robot_frame_offset'); }
    }
    public function set_frame_offset($value){
        $args = func_get_args();
        if (isset($args[1])){ $this->set_info('robot_frame_offset', $args[0], $args[1]); }
        else { $this->set_info('robot_frame_offset', $value); }
    }

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

    // Define a public function for applying robot stat bonuses
    public function apply_stat_bonuses(){

        // Pull in the global index
        global $mmrpg_index;

        // Only continue if this hasn't been done already
        if (!empty($this->flags['apply_stat_bonuses'])){ return false; }
        /*
         * ROBOT CLASS FUNCTION APPLY STAT BONUSES
         * public function apply_stat_bonuses(){}
         */

        // If this is robot's player is human controlled
        if ($this->player->player_autopilot != true && $this->robot_class == 'master'){

            // Collect this robot's rewards and settings
            $this_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);
            $this_rewards = mmrpg_prototype_robot_rewards($this->player_token, $this->robot_token);

            // Update this robot's original player with any session settings
            $this->robot_original_player = mmrpg_prototype_robot_original_player($this->player_token, $this->robot_token);

            // Update this robot's level with any session rewards
            $this->robot_base_experience = $this->robot_experience = mmrpg_prototype_robot_experience($this->player_token, $this->robot_token);
            $this->robot_base_level = $this->robot_level = mmrpg_prototype_robot_level($this->player_token, $this->robot_token);

        }
        // Otherwise, if this player is on autopilot
        else {

            // Create an empty reward array to prevent errors
            $this_settings = !empty($this->values['robot_settings']) ? $this->values['robot_settings'] : array();
            $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

        }

        // If the robot experience is over 1000 points, level up and reset
        if ($this->robot_experience > 1000){
            $level_boost = floor($this->robot_experience / 1000);
            $this->robot_level += $level_boost;
            $this->robot_base_level = $this->robot_level;
            $this->robot_experience -= $level_boost * 1000;
            $this->robot_base_experience = $this->robot_experience;
        }

        // Fix the level if it's over 100
        if ($this->robot_level > 100){ $this->robot_level = 100;  }
        if ($this->robot_base_level > 100){ $this->robot_base_level = 100;  }

        // Collect this robot's stat values for later reference
        $this_index_info = self::get_index_info($this->robot_token);
        $this_robot_stats = self::calculate_stat_values($this->robot_level, $this_index_info, $this_rewards, true);

        // Update the robot's stat values with calculated totals
        $stat_tokens = array('energy', 'attack', 'defense', 'speed');
        foreach ($stat_tokens AS $stat){
            // Collect and apply this robot's current stats and max
            $prop_stat = 'robot_'.$stat;
            $prop_stat_base = 'robot_base_'.$stat;
            $prop_stat_max = 'robot_max_'.$stat;
            $this->$prop_stat = $this_robot_stats[$stat]['current'];
            $this->$prop_stat_base = $this_robot_stats[$stat]['current'];
            $this->$prop_stat_max = $this_robot_stats[$stat]['max'];
            // If this robot's player has any stat bonuses, apply them as well
            $prop_player_stat = 'player_'.$stat;
            if (!empty($this->player->$prop_player_stat)){
                $temp_boost = ceil($this->$prop_stat * ($this->player->$prop_player_stat / 100));
                $this->$prop_stat += $temp_boost;
                $this->$prop_stat_base += $temp_boost;
            }

        }

        // Create the stat boost flag
        $this->flags['apply_stat_bonuses'] = true;

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define public print functions for markup generation
    public function print_number(){ return '<span class="robot_number">'.$this->robot_number.'</span>'; }
    public function print_name(){ return '<span class="robot_name robot_type">'.$this->robot_name.'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
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
    public function print_weaknesses(){
        $this_markup = array();
        foreach ($this->robot_weaknesses AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_resistances(){
        $this_markup = array();
        foreach ($this->robot_resistances AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_affinities(){
        $this_markup = array();
        foreach ($this->robot_affinities AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_immunities(){
        $this_markup = array();
        foreach ($this->robot_immunities AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
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
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
            $this_text_colour_bak = $this_text_colour;
            $temp_saturator = 1.25;
            if (in_array($this_type_token, array('water','wind'))){ $temp_saturator = 1.5; }
            elseif (in_array($this_type_token, array('earth', 'time', 'impact'))){ $temp_saturator = 1.75; }
            elseif (in_array($this_type_token, array('space', 'shadow'))){ $temp_saturator = 2.0; }
            elseif (in_array($this_type_token, array('empty'))){ $this_text_colour = array(172, 45, 27); }
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




    // Define public print functions for markup generation
    public static function print_robot_info_number($robot_info){ return '<span class="robot_number">'.$robot_info['robot_number'].'</span>'; }
    public static function print_robot_info_name($robot_info){ return '<span class="robot_name robot_type">'.$robot_info['robot_name'].'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
    public static function print_robot_info_token($robot_info){ return '<span class="robot_token">'.$robot_info['robot_token'].'</span>'; }
    public static function print_robot_info_core($robot_info){ return '<span class="robot_core '.(!empty($robot_info['robot_core']) ? 'robot_type_'.$robot_info['robot_core'] : '').'">'.(!empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral').'</span>'; }
    public static function print_robot_info_description($robot_info){ return '<span class="robot_description">'.$robot_info['robot_description'].'</span>'; }
    public static function print_robot_info_energy($robot_info){ return '<span class="robot_stat robot_stat_energy">'.$robot_info['robot_energy'].'</span>'; }
    public static function print_robot_info_base_energy($robot_info){ return '<span class="robot_stat robot_stat_base_energy">'.$robot_info['robot_base_energy'].'</span>'; }
    public static function print_robot_info_attack($robot_info){ return '<span class="robot_stat robot_stat_attack">'.$robot_info['robot_attack'].'</span>'; }
    public static function print_robot_info_base_attack($robot_info){ return '<span class="robot_stat robot_stat_base_attack">'.$robot_info['robot_base_attack'].'</span>'; }
    public static function print_robot_info_defense($robot_info){ return '<span class="robot_stat robot_stat_defense">'.$robot_info['robot_defense'].'</span>'; }
    public static function print_robot_info_base_defense($robot_info){ return '<span class="robot_stat robot_stat_base_defense">'.$robot_info['robot_base_defense'].'</span>'; }
    public static function print_robot_info_speed($robot_info){ return '<span class="robot_stat robot_stat_speed">'.$robot_info['robot_speed'].'</span>'; }
    public static function print_robot_info_base_speed($robot_info){ return '<span class="robot_stat robot_stat_base_speed">'.$robot_info['robot_base_speed'].'</span>'; }
    public static function print_robot_info_weaknesses($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_weaknesses'] AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_resistances($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_resistances'] AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_affinities($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_affinities'] AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_immunities($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_immunities'] AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_quote($robot_info, $quote_type, $this_find = array(), $this_replace = array()){
        global $mmrpg_index;
        // Define the quote text variable
        $quote_text = '';
        // If the robot is visible and has the requested quote text
        if ($robot_info['robot_token'] != 'robot' && isset($robot_info['robot_quotes'][$quote_type])){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $robot_info['robot_quotes'][$quote_type]);
            // Collect the text colour for this robot
            $this_type_token = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
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

    // Define a function for checking if this robot is compatible with a specific ability
    static public function has_ability_compatibility($robot_token, $ability_token){
        global $mmrpg_index;
        if (empty($robot_token) || empty($ability_token)){ return false; }
        $robot_info = is_array($robot_token) ? $robot_token : rpg_robot::get_index_info($robot_token);
        $ability_info = is_array($ability_token) ? $ability_token : rpg_ability::get_index_info($ability_token);
        if (empty($robot_info) || empty($ability_info)){ return false; }
        $robot_token = $robot_info['robot_token'];
        $ability_token = $ability_info['ability_token'];
        // Define the compatibility flag and default to false
        $temp_compatible = false;
        // If this ability has a type, check it against this robot
        if (!empty($ability_info['ability_type']) || !empty($ability_info['ability_type2'])){
            //$debug_fragment .= 'has-type '; // DEBUG
            if (!empty($robot_info['robot_core'])){
            //$debug_fragment .= 'has-core '; // DEBUG
                if ($robot_info['robot_core'] == 'copy' && !empty($ability_info['ability_type'])){
                    //$debug_fragment .= 'copy-core '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type'])
                    && $ability_info['ability_type'] == $robot_info['robot_core']){
                    //$debug_fragment .= 'core-match1 '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type2'])
                    && $ability_info['ability_type2'] == $robot_info['robot_core']){
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
        // Otherwise, see if this is a globally compatible ability
        if (!$temp_compatible && preg_match('/^(energy|attack|defense|speed)-(boost|break|mode|swap)$/i', $ability_info['ability_token'])){
            //$debug_fragment .= 'has-global '; // DEBUG
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
        elseif (in_array($weakness_token, $this->robot_weaknesses)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific resistance
    public function has_resistance($resistance_token){
        if (empty($this->robot_resistances) || empty($resistance_token)){ return false; }
        elseif (in_array($resistance_token, $this->robot_resistances)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific affinity
    public function has_affinity($affinity_token){
        if (empty($this->robot_affinities) || empty($affinity_token)){ return false; }
        elseif (in_array($affinity_token, $this->robot_affinities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific immunity
    public function has_immunity($immunity_token){
        if (empty($this->robot_immunities) || empty($immunity_token)){ return false; }
        elseif (in_array($immunity_token, $this->robot_immunities)){ return true; }
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

    // Define a function for checking if this robot is in speed break status
    public static function robot_choices_abilities($objects){

        // Extract all objects into the current scope
        extract($objects);
        global $db;

        // Create the ability options and weights variables
        $options = array();
        $weights = array();

        // Define the support multiplier for this robot
        $support_multiplier = 1;
        if (in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm'))){ $support_multiplier += 1; }

        // Define the freency of the default buster ability if set
        if ($this_robot->has_ability('buster-shot')){
            $options[] = 'buster-shot';
            $weights[] = $this_robot->robot_token == 'met' ? 90 : 1;
        }

        // Define the frequency of the energy boost ability if set
        if ($this_robot->has_ability('energy-boost')){
            $options[] = 'energy-boost';
            if ($this_robot->robot_energy >= $this_robot->robot_base_energy){ $weights[] = 0;  }
            elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 4)){ $weights[] = 14 * $support_multiplier;  }
            elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 3)){ $weights[] = 12 * $support_multiplier;  }
            elseif ($this_robot->robot_energy < ($this_robot->robot_base_energy / 2)){ $weights[] = 10 * $support_multiplier;  }
        }

        // Define the frequency of the energy break ability if set
        if ($this_robot->has_ability('energy-break')){
            $options[] = 'energy-break';
            if ($target_robot->robot_energy >= $target_robot->robot_base_energy){ $weights[] = 28 * $support_multiplier;  }
            elseif ($target_robot->robot_energy < ($target_robot->robot_base_energy / 4)){ $weights[] = 10 * $support_multiplier;  }
            elseif ($target_robot->robot_energy < ($target_robot->robot_base_energy / 3)){ $weights[] = 12 * $support_multiplier;  }
            elseif ($target_robot->robot_energy < ($target_robot->robot_base_energy / 2)){ $weights[] = 14 * $support_multiplier;  }
        }

        // Define the frequency of the energy swap ability if set
        if ($this_robot->has_ability('energy-swap')){
            $options[] = 'energy-swap';
            if ($target_robot->robot_energy > $this_robot->robot_energy){ $weights[] = 28 * $support_multiplier;  }
            elseif ($target_robot->robot_energy <= $this_robot->robot_energy){ $weights[] = 0;  }
        }

        // Define the frequency of the attack, defense, and speed boost abiliies if set
        if ($this_robot->has_ability('attack-boost')){
            $options[] = 'attack-boost';
            if ($this_robot->robot_attack < ($this_robot->robot_base_attack * 0.5)){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-boost')){
            $options[] = 'defense-boost';
            if ($this_robot->robot_defense < ($this_robot->robot_base_defense * 0.5)){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-boost')){
            $options[] = 'speed-boost';
            if ($this_robot->robot_speed < ($this_robot->robot_base_speed * 0.5)){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the attack, defense, and speed break abilities if set
        if ($this_robot->has_ability('attack-break')){
            $options[] = 'attack-break';
            if ($target_robot->robot_attack > $this_robot->robot_defense){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-break')){
            $options[] = 'defense-break';
            if ($target_robot->robot_defense > $this_robot->robot_attack){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-break')){
            $options[] = 'speed-break';
            if ($this_robot->robot_speed < $target_robot->robot_speed){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the attack, defense, and speed swap abilities if set
        if ($this_robot->has_ability('attack-swap')){
            $options[] = 'attack-swap';
            if ($target_robot->robot_attack > $this_robot->robot_attack){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-swap')){
            $options[] = 'defense-swap';
            if ($target_robot->robot_defense > $this_robot->robot_defense){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-swap')){
            $options[] = 'speed-swap';
            if ($target_robot->robot_speed > $this_robot->robot_speed){ $weights[] = 3 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the energy/repair mode ability if set
        if ($this_robot->has_ability('energy-mode')){
            $options[] = 'energy-mode';
            if ($this_robot->robot_energy < ($this_robot->robot_base_energy * 0.5)){ $weights[] = 9 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('repair-mode')){
            $options[] = 'repair-mode';
            if ($this_robot->robot_energy < ($this_robot->robot_base_energy * 0.5)){ $weights[] = 9 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the attack, defense, and speed mode abilities if set
        if ($this_robot->has_ability('attack-mode')){
            $options[] = 'attack-mode';
            if ($this_robot->robot_attack < ($this_robot->robot_base_attack * 0.10)){ $weights[] = 6 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('defense-mode')){
            $options[] = 'defense-mode';
            if ($this_robot->robot_defense < ($this_robot->robot_base_defense * 0.10)){ $weights[] = 6 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }
        if ($this_robot->has_ability('speed-mode')){
            $options[] = 'speed-mode';
            if ($this_robot->robot_speed < ($this_robot->robot_base_speed * 0.10)){ $weights[] = 6 * $support_multiplier;  }
            else { $weights[] = 1;  }
        }

        // Define the frequency of the super throw ability based on benched targets
        if ($this_robot->has_ability('super-throw')){
            $options[] = 'super-throw';
            if ($target_player->counters['robots_active'] > 1){ $weights[] = 10; }
            else { $weights[] = 1; }
        }

        // Define the frequency of the mecha support ability based benched robot count
        if ($this_robot->has_ability('mecha-support')){
            $options[] = 'mecha-support';
            if ($target_player->counters['robots_total'] == 1){ $weights[] = 50; }
            elseif ($target_player->counters['robots_total'] < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ $weights[] = 10; }
            else { $weights[] = 0; }
        }

        // Loop through any leftover abilities and add them to the weighted ability options
        $temp_ability_tokens = "'".implode("','", array_values($this_robot->robot_abilities))."'";
        $temp_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_token IN ({$temp_ability_tokens});", 'ability_token');
        foreach ($this_robot->robot_abilities AS $key => $token){
            if (!in_array($token, $options)){
                $info = rpg_ability::parse_index_info($temp_ability_index[$token]);
                $value = 3;
                if (!empty($this_robot->robot_core) && !empty($info['ability_type'])){
                    if ($this_robot->robot_core == $info['ability_type']){ $value = 50; }
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

        // Remove any options that have absolute zero values
        foreach ($weights AS $key => $value){
            if (empty($value)){
                unset($weights[$key]);
                unset($options[$key]);
                continue;
            }
        }

        // Re-key both arrays just in case
        $weights = array_values($weights);
        $options = array_values($options);

        // This robot doesn't have ANY abilities, return buster shot
        if (empty($options) || empty($weights)){ return 'action-noweapons'; }

        // Return an ability based on a weighted chance
        $ability_token = $this_battle->weighted_chance($options, $weights);
        return $ability_token;

    }

    // Define a trigger for using one of this robot's abilities
    public function trigger_ability($target_robot, $this_ability){
        global $db;

        // Update this robot's history with the triggered ability
        $this->history['triggered_abilities'][] = $this_ability->ability_token;

        // Define a variable to hold the ability results
        $this_ability->ability_results = array();
        $this_ability->ability_results['total_result'] = '';
        $this_ability->ability_results['total_actions'] = 0;
        $this_ability->ability_results['total_strikes'] = 0;
        $this_ability->ability_results['total_misses'] = 0;
        $this_ability->ability_results['total_amount'] = 0;
        $this_ability->ability_results['total_overkill'] = 0;
        $this_ability->ability_results['this_result'] = '';
        $this_ability->ability_results['this_amount'] = 0;
        $this_ability->ability_results['this_overkill'] = 0;
        $this_ability->ability_results['this_text'] = '';
        $this_ability->ability_results['counter_criticals'] = 0;
        $this_ability->ability_results['counter_affinities'] = 0;
        $this_ability->ability_results['counter_weaknesses'] = 0;
        $this_ability->ability_results['counter_resistances'] = 0;
        $this_ability->ability_results['counter_immunities'] = 0;
        $this_ability->ability_results['counter_coreboosts'] = 0;
        $this_ability->ability_results['flag_critical'] = false;
        $this_ability->ability_results['flag_affinity'] = false;
        $this_ability->ability_results['flag_weakness'] = false;
        $this_ability->ability_results['flag_resistance'] = false;
        $this_ability->ability_results['flag_immunity'] = false;

        // Reset the ability options to default
        $this_ability->target_options_reset();
        $this_ability->damage_options_reset();
        $this_ability->recovery_options_reset();

        // Determine how much weapon energy this should take
        $temp_ability_energy = $this->calculate_weapon_energy($this_ability);

        // Decrease this robot's weapon energy
        $this->robot_weapons = $this->robot_weapons - $temp_ability_energy;
        if ($this->robot_weapons < 0){ $this->robot_weapons = 0; }
        $this->update_session();

        // Default this and the target robot's frames to their base
        $this->robot_frame = 'base';
        $target_robot->robot_frame = 'base';

        // Default the robot's stances to attack/defend
        $this->robot_stance = 'attack';
        $target_robot->robot_stance = 'defend';

        // If this is a copy core robot and the ability type does not match its core
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) ? $this_ability->ability_type : '';
        $temp_ability_type2 = !empty($this_ability->ability_type2) ? $this_ability->ability_type2 : $temp_ability_type;
        if (!empty($temp_ability_type) && $this->robot_base_core == 'copy'){
            $this->robot_image_overlay['copy_type1'] = $this->robot_base_image.'_'.$temp_ability_type.'2';
            $this->robot_image_overlay['copy_type2'] = $this->robot_base_image.'_'.$temp_ability_type2.'3';
            $this->update_session();
            $temp_image_changed = true;
        }

        // Copy the ability function to local scope and execute it
        $this_ability_function = $this_ability->ability_function;
        $this_ability_function(array(
            'this_battle' => $this->battle,
            'this_field' => $this->field,
            'this_player' => $this->player,
            'this_robot' => $this,
            'target_player' => $target_robot->player,
            'target_robot' => $target_robot,
            'this_ability' => $this_ability
            ));


        // If this robot's image has been changed, reveert it back to what it was
        if ($temp_image_changed){
            unset($this->robot_image_overlay['copy_type1']);
            unset($this->robot_image_overlay['copy_type2']);
            $this->update_session();
        }

        // DEBUG DEBUG DEBUG
        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;
        // Update this ability's history with the triggered ability damage options
        $this_ability->history['ability_options'][] = $this_ability->ability_options;

        // Reset the robot's stances to the base
        $this->robot_stance = 'base';
        $target_robot->robot_stance = 'base';

        // Update internal variables
        $target_robot->update_session();
        $this_ability->update_session();


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        if (!empty($this->robot_attachments)){
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $temp_attachments_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            foreach ($this->robot_attachments AS $attachment_token => $attachment_info){

                // Ensure this ability has a type before checking weaknesses, resistances, etc.
                if (!empty($this_ability->ability_type)){

                    // If this attachment has weaknesses defined and this ability is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses'])
                            || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))
                            ){
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        $this->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            $temp_ability = rpg_ability::parse_index_info($temp_attachments_index[$attachment_info['ability_token']]);
                            $attachment_info = array_merge($temp_ability, $attachment_info);
                            $temp_attachment = rpg_game::get_ability($this->battle, $this->player, $this, $attachment_info);
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
                                //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            break;
                        }
                    }

                }

            }
        }

        // Update internal variables
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;
    }

    // Define a trigger for using one of this robot's items
    public function trigger_item($target_robot, $this_item){
        global $db;

        // Update this robot's history with the triggered item
        $this->history['triggered_items'][] = $this_item->item_token;

        // Define a variable to hold the item results
        $this_item->item_results = array();
        $this_item->item_results['total_result'] = '';
        $this_item->item_results['total_actions'] = 0;
        $this_item->item_results['total_strikes'] = 0;
        $this_item->item_results['total_misses'] = 0;
        $this_item->item_results['total_amount'] = 0;
        $this_item->item_results['total_overkill'] = 0;
        $this_item->item_results['this_result'] = '';
        $this_item->item_results['this_amount'] = 0;
        $this_item->item_results['this_overkill'] = 0;
        $this_item->item_results['this_text'] = '';
        $this_item->item_results['counter_criticals'] = 0;
        $this_item->item_results['counter_affinities'] = 0;
        $this_item->item_results['counter_weaknesses'] = 0;
        $this_item->item_results['counter_resistances'] = 0;
        $this_item->item_results['counter_immunities'] = 0;
        $this_item->item_results['counter_coreboosts'] = 0;
        $this_item->item_results['flag_critical'] = false;
        $this_item->item_results['flag_affinity'] = false;
        $this_item->item_results['flag_weakness'] = false;
        $this_item->item_results['flag_resistance'] = false;
        $this_item->item_results['flag_immunity'] = false;

        // Reset the item options to default
        $this_item->target_options_reset();
        $this_item->damage_options_reset();
        $this_item->recovery_options_reset();

        // Determine how much weapon energy this should take
        $temp_item_energy = $this->calculate_weapon_energy($this_item);

        // Decrease this robot's weapon energy
        $this->robot_weapons = $this->robot_weapons - $temp_item_energy;
        if ($this->robot_weapons < 0){ $this->robot_weapons = 0; }
        $this->update_session();

        // Default this and the target robot's frames to their base
        $this->robot_frame = 'base';
        $target_robot->robot_frame = 'base';

        // Default the robot's stances to attack/defend
        $this->robot_stance = 'attack';
        $target_robot->robot_stance = 'defend';

        // If this is a copy core robot and the item type does not match its core
        $temp_image_changed = false;
        $temp_item_type = !empty($this_item->item_type) ? $this_item->item_type : '';
        $temp_item_type2 = !empty($this_item->item_type2) ? $this_item->item_type2 : $temp_item_type;
        if (preg_match('/^([a-z0-9]+)-(shard|core|star)/', $this_item->item_token) && !empty($temp_item_type) && $this->robot_base_core == 'copy'){
            $this->robot_image_overlay['copy_type1'] = $this->robot_base_image.'_'.$temp_item_type.'2';
            $this->robot_image_overlay['copy_type2'] = $this->robot_base_image.'_'.$temp_item_type2.'3';
            $this->update_session();
            $temp_image_changed = true;
        }

        // Copy the item function to local scope and execute it
        $this_item_function = $this_item->item_function;
        $this_item_function(array(
            'this_battle' => $this->battle,
            'this_field' => $this->field,
            'this_player' => $this->player,
            'this_robot' => $this,
            'target_player' => $target_robot->player,
            'target_robot' => $target_robot,
            'this_item' => $this_item
            ));


        // If this robot's image has been changed, reveert it back to what it was
        if ($temp_image_changed){
            unset($this->robot_image_overlay['copy_type1']);
            unset($this->robot_image_overlay['copy_type2']);
            $this->update_session();
        }

        // DEBUG DEBUG DEBUG
        // Update this item's history with the triggered item data and results
        $this_item->history['item_results'][] = $this_item->item_results;
        // Update this item's history with the triggered item damage options
        $this_item->history['item_options'][] = $this_item->item_options;

        // Reset the robot's stances to the base
        $this->robot_stance = 'base';
        $target_robot->robot_stance = 'base';

        // Update internal variables
        $target_robot->update_session();
        $this_item->update_session();


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        if (!empty($this->robot_attachments)){
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $temp_attachments_index = $db->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_flag_complete = 1;", 'item_token');
            foreach ($this->robot_attachments AS $attachment_token => $attachment_info){

                // Ensure this item has a type before checking weaknesses, resistances, etc.
                if (!empty($this_item->item_type)){

                    // If this attachment has weaknesses defined and this item is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_item->item_type, $attachment_info['attachment_weaknesses'])
                            || in_array($this_item->item_type2, $attachment_info['attachment_weaknesses']))
                            ){
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        $this->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            $temp_item = rpg_item::parse_index_info($temp_attachments_index[$attachment_info['item_token']]);
                            $attachment_info = array_merge($temp_item, $attachment_info);
                            $temp_attachment = rpg_game::get_item($this->battle, $this->player, $this, $attachment_info);
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
                                //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_item' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            break;
                        }
                    }

                }

            }
        }

        // Update internal variables
        $target_robot->update_session();
        $this_item->update_session();

        // Return the item results
        return $this_item->item_results;
    }

    // Define a trigger for using one of this robot's attachments
    public function trigger_attachment($attachment_info){
        global $db;

        // If this is an ABILITY attachment
        if ($attachment_info['class'] == 'ability'){

            // Create the temporary ability object
            $attachment_info['flags']['is_attachment'] = true;
            if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_info['ability_token']; }
            $this_ability = rpg_game::get_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));

            // Update this robot's history with the triggered attachment
            $this->history['triggered_attachments'][] = 'ability_'.$this_ability->ability_token;

            // Define a variable to hold the ability results
            $this_ability->attachment_results = array();
            $this_ability->attachment_results['total_result'] = '';
            $this_ability->attachment_results['total_actions'] = 0;
            $this_ability->attachment_results['total_strikes'] = 0;
            $this_ability->attachment_results['total_misses'] = 0;
            $this_ability->attachment_results['total_amount'] = 0;
            $this_ability->attachment_results['total_overkill'] = 0;
            $this_ability->attachment_results['this_result'] = '';
            $this_ability->attachment_results['this_amount'] = 0;
            $this_ability->attachment_results['this_overkill'] = 0;
            $this_ability->attachment_results['this_text'] = '';
            $this_ability->attachment_results['counter_critical'] = 0;
            $this_ability->attachment_results['counter_affinity'] = 0;
            $this_ability->attachment_results['counter_weakness'] = 0;
            $this_ability->attachment_results['counter_resistance'] = 0;
            $this_ability->attachment_results['counter_immunity'] = 0;
            $this_ability->attachment_results['counter_coreboosts'] = 0;
            $this_ability->attachment_results['flag_critical'] = false;
            $this_ability->attachment_results['flag_affinity'] = false;
            $this_ability->attachment_results['flag_weakness'] = false;
            $this_ability->attachment_results['flag_resistance'] = false;
            $this_ability->attachment_results['flag_immunity'] = false;

            // Reset the ability options to default
            $this_ability->attachment_options_reset();

            // Default this and the target robot's frames to their base
            $this->robot_frame = 'base';
            //$target_robot->robot_frame = 'base';

            // Collect the target robot and player objects
            //$target_robot_info = $this->battle->values['robots'][];

            // Copy the attachment function to local scope and execute it
            $this_attachment_function = $this_ability->ability_function_attachment;
            $this_attachment_function(array(
                'this_battle' => $this->battle,
                'this_field' => $this->field,
                'this_player' => $this->player,
                'this_robot' => $this,
                //'target_player' => $target_robot->player,
                //'target_robot' => $target_robot,
                'this_ability' => $this_ability
                ));

            // Update this ability's attachment history with the triggered attachment data and results
            $this_ability->history['attachment_results'][] = $this_ability->attachment_results;
            // Update this ability's attachment history with the triggered attachment damage options
            $this_ability->history['attachment_options'][] = $this_ability->attachment_options;

            // Reset the robot's stances to the base
            $this->robot_stance = 'base';
            //$target_robot->robot_stance = 'base';

            // Update internal variables
            $this->update_session();
            $this_ability->update_session();

            // Return the ability results
            return $this_ability->attachment_results;

        }
        // If this is an ITEM attachment
        elseif ($attachment_info['class'] == 'item'){

            // Create the temporary item object
            $this_item = rpg_game::get_item($this->battle, $this->player, $this, array('item_token' => $attachment_info['item_token']));

            // Update this robot's history with the triggered attachment
            $this->history['triggered_attachments'][] = 'item_'.$this_item->item_token;

            // Define a variable to hold the item results
            $this_item->attachment_results = array();
            $this_item->attachment_results['total_result'] = '';
            $this_item->attachment_results['total_actions'] = 0;
            $this_item->attachment_results['total_strikes'] = 0;
            $this_item->attachment_results['total_misses'] = 0;
            $this_item->attachment_results['total_amount'] = 0;
            $this_item->attachment_results['total_overkill'] = 0;
            $this_item->attachment_results['this_result'] = '';
            $this_item->attachment_results['this_amount'] = 0;
            $this_item->attachment_results['this_overkill'] = 0;
            $this_item->attachment_results['this_text'] = '';
            $this_item->attachment_results['counter_critical'] = 0;
            $this_item->attachment_results['counter_affinity'] = 0;
            $this_item->attachment_results['counter_weakness'] = 0;
            $this_item->attachment_results['counter_resistance'] = 0;
            $this_item->attachment_results['counter_immunity'] = 0;
            $this_item->attachment_results['counter_coreboosts'] = 0;
            $this_item->attachment_results['flag_critical'] = false;
            $this_item->attachment_results['flag_affinity'] = false;
            $this_item->attachment_results['flag_weakness'] = false;
            $this_item->attachment_results['flag_resistance'] = false;
            $this_item->attachment_results['flag_immunity'] = false;

            // Reset the item options to default
            $this_item->attachment_options_reset();

            // Default this and the target robot's frames to their base
            $this->robot_frame = 'base';
            //$target_robot->robot_frame = 'base';

            // Collect the target robot and player objects
            //$target_robot_info = $this->battle->values['robots'][];

            // Copy the attachment function to local scope and execute it
            $this_attachment_function = $this_item->item_function_attachment;
            $this_attachment_function(array(
                'this_battle' => $this->battle,
                'this_field' => $this->field,
                'this_player' => $this->player,
                'this_robot' => $this,
                //'target_player' => $target_robot->player,
                //'target_robot' => $target_robot,
                'this_item' => $this_item
                ));

            // Update this item's attachment history with the triggered attachment data and results
            $this_item->history['attachment_results'][] = $this_item->attachment_results;
            // Update this item's attachment history with the triggered attachment damage options
            $this_item->history['attachment_options'][] = $this_item->attachment_options;

            // Reset the robot's stances to the base
            $this->robot_stance = 'base';
            //$target_robot->robot_stance = 'base';

            // Update internal variables
            $this->update_session();
            $this_item->update_session();

            // Return the item results
            return $this_item->attachment_results;

        }

    }

    // Define a trigger for using one of this robot's abilities or items in battle
    public function trigger_target($target_robot, $this_object, $trigger_options = array()){

        // Check to see which object type has been provided
        if (isset($this_object->ability_token)){
            // This was an ability so delegate to the ability function
            return rpg_target::trigger_ability_target($this, $target_robot, $this_object, $trigger_options);

        } elseif (isset($this_object->item_token)){
            // This was an item so delegate to the item function
            return rpg_target::trigger_item_target($this, $target_robot, $this_object, $trigger_options);
        }

    }

    // Define a trigger for inflicting all types of ability or item damage on this robot
    public function trigger_damage($target_robot, $this_object, $damage_amount, $trigger_disabled = true, $trigger_options = array()){

        // Check to see which object type has been provided
        if (isset($this_object->ability_token)){
            // This was an ability so delegate to the ability class function
            return rpg_ability_damage::trigger_robot_damage($this, $target_robot, $this_object, $damage_amount, $trigger_disabled, $trigger_options);

        } elseif (isset($this_object->item_token)){
            // This was an item so delegate to the item class function
            //return $this->trigger_item_damage($target_robot, $this_object, $damage_amount, $trigger_disabled, $trigger_options);
            return rpg_item_damage::trigger_robot_damage($this, $target_robot, $this_object, $damage_amount, $trigger_disabled, $trigger_options);
        }

    }

    // Define a trigger for inflicting all types of ability or item recovery on this robot
    public function trigger_recovery($target_robot, $this_object, $recovery_amount, $trigger_disabled = true, $trigger_options = array()){

        // Check to see which object type has been provided
        if (isset($this_object->ability_token)){
            // This was an ability so delegate to the ability class function
            return rpg_ability_recovery::trigger_robot_recovery($this, $target_robot, $this_object, $recovery_amount, $trigger_disabled, $trigger_options);

        } elseif (isset($this_object->item_token)){
            // This was an item so delegate to the item class function
            return rpg_item_recovery::trigger_robot_recovery($this, $target_robot, $this_object, $recovery_amount, $trigger_disabled, $trigger_options);
        }

    }

    // Define a trigger for processing disabled events from abilities
    public function trigger_disabled($target_robot, $trigger_options = array()){

        // This was an ability so delegate to the ability class function
        return rpg_disabled::trigger_robot_disabled($this, $target_robot, $trigger_options);
    }

    // Define a function for calculating required weapon energy
    public function calculate_weapon_energy($this_object, &$energy_base = 0, &$energy_mods = 0){

        // If this is an item the weapon energy is zero
        if (isset($this_object->item_token)){

            // Define the return to the item variable
            $this_item = $this_object;

            // Return zero as items are free
            return 0;

        }
        // Otherwise if ability then we have to calculate
        elseif (isset($this_object->ability_token)){

            // Define the return to the ability variable
            $this_ability = $this_object;

            // Determine how much weapon energy this should take
            $energy_new = $this_ability->ability_energy;
            $energy_base = $energy_new;
            $energy_mods = 0;
            if ($this_ability->ability_token != 'action-noweapons'){
                if (!empty($this->robot_core) && ($this->robot_core == $this_ability->ability_type || $this->robot_core == $this_ability->ability_type2)){
                    $energy_mods++;
                    $energy_new = ceil($energy_new * 0.5);
                } elseif (empty($this->robot_core) && empty($this_ability->ability_type) && empty($this_ability->ability_type2)){
                    $energy_mods++;
                    $energy_new = ceil($energy_new * 0.5);
                }
                if (!empty($this->robot_rewards['abilities'])){
                    foreach ($this->robot_rewards['abilities'] AS $key => $info){
                        if ($info['token'] == $this_ability->ability_token){
                            $energy_mods++;
                            $energy_new = ceil($energy_new * 0.5);
                            break;
                        }
                    }
                }
            } else {
                $this_ability->ability_energy = 0;
            }

            // Return the resulting weapon energy
            return $energy_new;

        }
    }

    // Define a function for calculating required weapon energy without using objects
    static function calculate_weapon_energy_static($this_robot, $this_ability, &$energy_base = 0, &$energy_mods = 0){
        // Determine how much weapon energy this should take
        $energy_new = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 0;
        $energy_base = $energy_new;
        $energy_mods = 0;
        if (!isset($this_robot['robot_core'])){ $this_robot['robot_core'] = ''; }
        if (!isset($this_ability['ability_type'])){ $this_ability['ability_type'] = ''; }
        if (!isset($this_ability['ability_type2'])){ $this_ability['ability_type2'] = ''; }
        if ($this_ability['ability_token'] != 'action-noweapons'){
            if (!empty($this_robot['robot_core']) && ($this_robot['robot_core'] == $this_ability['ability_type'] || $this_robot['robot_core'] == $this_ability['ability_type2'])){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            if (!empty($this_robot['robot_rewards']['abilities'])){
                foreach ($this_robot['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == $this_ability['ability_token']){
                        $energy_mods++;
                        $energy_new = ceil($energy_new * 0.5);
                        break;
                    }
                }
            }
        } else {
            $this_ability['ability_energy'] = 0;
        }
        // Return the resulting weapon energy
        return $energy_new;
    }

    // Define a function for generating robot canvas variables
    public function canvas_markup($options, $player_data){

        // Delegate markup generation to the canvas class
        return rpg_canvas::robot_markup($this, $options, $player_data);

    }

    // Define a function for generating robot console variables
    public function console_markup($options, $player_data){

        // Delegate markup generation to the console class
        return rpg_console::robot_markup($this, $options, $player_data);

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

        // Update parent objects first
        //$this->player->update_variables();

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
        $this_data['robot_token'] = $this_data['robot_base_token'];
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
        //$this_data['robot_abilities'] = $this_data['robot_base_abilities'];
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
        //$this->player->values['robots'][$this->robot_id] = $this_data;

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
            'robot_base_token' => $this->robot_base_token,
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
            'robot_max_energy' => $this->robot_max_energy,
            'robot_max_weapons' => $this->robot_max_weapons,
            'robot_max_attack' => $this->robot_max_attack,
            'robot_max_defense' => $this->robot_max_defense,
            'robot_max_speed' => $this->robot_max_speed,
            'robot_base_weaknesses' => $this->robot_base_weaknesses,
            'robot_base_resistances' => $this->robot_base_resistances,
            'robot_base_affinities' => $this->robot_base_affinities,
            'robot_base_immunities' => $this->robot_base_immunities,
            //'robot_base_abilities' => $this->robot_base_abilities,
            'robot_base_attachments' => $this->robot_base_attachments,
            'robot_base_quotes' => $this->robot_base_quotes,
            //'robot_base_rewards' => $this->robot_base_rewards,
            'robot_status' => $this->robot_status,
            'robot_position' => $this->robot_position,
            'robot_stance' => $this->robot_stance,
            'robot_frame' => $this->robot_frame,
            //'robot_frame_index' => $this->robot_frame_index,
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

    // Define a static function for printing out the robot's database markup
    public static function print_database_markup($robot_info, $print_options = array()){

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


        // Define the variable to hold compact footer link markup
        $compact_footer_link_markup = array();
        //$compact_footer_link_markup[] = '<a class="link link_permalink" href="'.$database_category_robot_url.'">+ Huh?</a>';

        /*
        // Add a link to the sprites in the compact footer markup
        if (!in_array($robot_image_token, $default_robot_class_tokens)){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#sprites">#Sprites</a>'; }
        if (!empty($robot_info['robot_quotes']['battle_start'])){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#quotes">#Quotes</a>'; }
        if (!empty($robot_info['robot_description2'])){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#description">#Description</a>'; }
        if (!empty($robot_info['robot_abilities'])){ $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#abilities">#Abilities</a>'; }
        $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#stats">#Stats</a>';
        $compact_footer_link_markup[] = '<a class="link" href="'.$database_category_robot_url.'#records">#Records</a>';
        */

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
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: url(images/robots/<?= $robot_image_token ?>/mug_right_<?= $robot_image_size_text ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?>'s Mugshot</div></div>
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
                                echo '<img style="margin-left: 0;" data-tooltip="'.$temp_title.'" src="images/robots/'.$temp_robot_image_token.'/mug_'.$temp_direction.'_'.$robot_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
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
                                    echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/robots/'.$temp_robot_image_token.'/sprite_'.$temp_direction.'_'.$robot_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
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
                                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_image = 'ability'; $this_ability_sprite_path = 'images/abilities/ability/icon_left_40x40.png'; }
                                                else { $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png'; }
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
    public static function print_editor_markup($player_info, $robot_info, $mmrpg_database_abilities = array()){

        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup;
        global $mmrpg_database_abilities;
        $session_token = mmrpg_game_token();

        // If either fo empty, return error
        if (empty($player_info)){ return 'error:player-empty'; }
        if (empty($robot_info)){ return 'error:robot-empty'; }

        // Collect the approriate database indexes
        if (empty($mmrpg_database_abilities)){ $mmrpg_database_abilities = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token'); }

        // Define the quick-access variables for later use
        $player_token = $player_info['player_token'];
        $robot_token = $robot_info['robot_token'];
        if (!isset($first_robot_token)){ $first_robot_token = $robot_token; }

        // Start the output buffer
        ob_start();

            // Check how many robots this player has and see if they should be able to transfer
            $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
            $counter_player_missions = mmrpg_prototype_battles_complete($player_info['player_token']);
            $allow_player_selector = $player_counter > 1 && $counter_player_missions > 0 ? true : false;

            // If this player has fewer robots than any other player
            //$temp_flag_most_robots = true;
            foreach ($temp_robot_totals AS $temp_player => $temp_total){
                //if ($temp_player == $player_token){ continue; }
                //elseif ($temp_total > $counter_player_robots){ $allow_player_selector = false; }
            }

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
            $robot_info['robot_level'] = mmrpg_prototype_robot_level($player_info['player_token'], $robot_info['robot_token']);
            $robot_info['robot_experience'] = mmrpg_prototype_robot_experience($player_info['player_token'], $robot_info['robot_token']);
            // Collect the rewards for this robot
            $robot_rewards = mmrpg_prototype_robot_rewards($player_token, $robot_token);
            // Collect the settings for this robot
            $robot_settings = mmrpg_prototype_robot_settings($player_token, $robot_token);
            // Collect the database for this robot
            $robot_database = !empty($player_robot_database[$robot_token]) ? $player_robot_database[$robot_token] : array();
            // Collect the stat details for this robot
            $robot_stats = rpg_robot::calculate_stat_values($robot_info['robot_level'], $robot_info, $robot_rewards, true);
            // Collect the robot ability core if it exists
            $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
            // Check if this robot has the copy shot ability
            $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

            // Loop through and update this robot's stats with calculated values
            $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
            foreach ($stat_tokens As $stat_token){
                // Update this robot's stat with the calculated current totals
                $robot_info['robot_'.$stat_token] = $robot_stats[$stat_token]['current'];
                $robot_info['robot_'.$stat_token.'_base'] = $robot_stats[$stat_token]['current_noboost'];
                $robot_info['robot_'.$stat_token.'_rewards'] = $robot_stats[$stat_token]['bonus'];
                if (!empty($player_info['player_'.$stat_token])){
                    $robot_stats[$stat_token]['player'] = ceil($robot_info['robot_'.$stat_token] * ($player_info['player_'.$stat_token] / 100));
                    $robot_info['robot_'.$stat_token.'_player'] = $robot_stats[$stat_token]['player'];
                    $robot_info['robot_'.$stat_token] += $robot_stats[$stat_token]['player'];
                }
            }

            // Define a temp function for printing out robot stat blocks
            $print_robot_stat_function = function($stat_token) use($robot_info, $robot_stats, $player_info){

                $level_max = $robot_stats['level'] >= 100 ? true : false;
                $is_maxed = $robot_stats[$stat_token]['bonus'] >= $robot_stats[$stat_token]['bonus_max'] ? true : false;

                if ($stat_token == 'energy' || $stat_token == 'weapons'){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
                elseif ($level_max && $is_maxed){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
                else { echo '<span class="robot_stat"> '; }

                    if ($stat_token != 'energy' && $stat_token != 'weapons'){
                        echo $is_maxed ? ($level_max ? '<span>&#9733;</span> ' : '<span>&bull;</span> ') : '';
                        echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
                            $base_text = 'Base '.ucfirst($stat_token).' <br /> <span style="font-size: 90%">'.number_format($robot_stats[$stat_token]['base'], 0, '.', ',').' <span style="font-size: 90%">@</span>  Lv.'.$robot_stats['level'].' = '.number_format($robot_stats[$stat_token]['current_noboost'], 0, '.', ',').'</span>';
                            echo '<span data-tooltip="'.htmlentities($base_text, ENT_QUOTES, 'UTF-8', true).'" data-tooltip-type="robot_type robot_type_none">'.$robot_stats[$stat_token]['current_noboost'].'</span> ';
                            if (!empty($robot_stats[$stat_token]['bonus'])){
                                $robot_bonus_text = 'Robot Bonuses <br /> <span style="font-size: 90%">'.number_format($robot_stats[$stat_token]['bonus'], 0, '.', ',').' / '.number_format($robot_stats[$stat_token]['bonus_max'], 0, '.', ',').' Max</span>';
                                echo '+ <span data-tooltip="'.htmlentities($robot_bonus_text, ENT_QUOTES, 'UTF-8', true).'" class="statboost_robot" data-tooltip-type="robot_stat robot_type_shield">'.$robot_stats[$stat_token]['bonus'].'</span> ';
                            }
                            if (!empty($robot_stats[$stat_token]['player'])){
                                $player_bonus_text = 'Player Bonuses <br /> <span style="font-size: 90%">'.number_format(($robot_stats[$stat_token]['current']), 0, '.', ',').' x '.$player_info['player_'.$stat_token].'% = '.number_format($robot_stats[$stat_token]['player'], 0, '.', ',').'</span>';
                                echo '+ <span data-tooltip="'.htmlentities($player_bonus_text, ENT_QUOTES, 'UTF-8', true).'" class="statboost_player_'.$player_info['player_token'].'" data-tooltip-type="robot_stat robot_type_'.$stat_token.'">'.$robot_stats[$stat_token]['player'].'</span> ';
                            }
                        echo ' = </span>';
                        echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$stat_token], 4, '0', STR_PAD_LEFT));
                    } else {
                        echo $robot_info['robot_'.$stat_token];
                    }

                    if ($stat_token == 'energy'){ echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> LE</span>'; }
                    elseif ($stat_token == 'weapons'){ echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> WE</span>'; }

                echo '</span>'."\n";
                };

            // Collect this robot's ability rewards and add them to the dropdown
            $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
            $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
            foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }

            // Collect the summon count from the session if it exists
            $robot_info['robot_summoned'] = !empty($robot_database['robot_summoned']) ? $robot_database['robot_summoned'] : 0;

            // Collect any manually unlocked alts from the session if exists
            $robot_info['robot_altimages'] = mmrpg_prototype_altimage_unlocked($robot_token);

            // Collect the alt images if there are any that are unlocked
            $robot_alt_count = 1 + (!empty($robot_info['robot_image_alts']) ? count($robot_info['robot_image_alts']) : 0);
            $robot_alt_options = array();
            if (!empty($robot_info['robot_image_alts'])){
                foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                    $is_unlocked = false;
                    if (in_array($alt_info['token'], $robot_info['robot_altimages'])){ $is_unlocked = true; }
                    elseif ($robot_info['robot_summoned'] >= $alt_info['summons']){ $is_unlocked = true; $robot_info['robot_altimages'][] = $alt_info['token']; }
                    if (!$is_unlocked){ continue; }
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
                        if (
                            ($robot_info['robot_summoned'] >= $alt_info['summons']) ||
                            (in_array($alt_info['token'], $robot_info['robot_altimages']))
                            ){
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
            <div class="event event_double event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token'].'_'.$robot_info['robot_token']?>">

                <div class="this_sprite sprite_left event_robot_mugshot" style="">
                    <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                    <div class="sprite_wrapper robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="width: 33px;">
                        <div class="sprite_wrapper robot_type robot_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px;"></div>
                        <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mug_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_images" style="">
                    <? if($global_allow_editing && !empty($robot_alt_options)): ?>
                        <a class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </a>
                    <? else: ?>
                        <span class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <? $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </span>
                    <? endif; ?>
                </div>

                <div class="this_sprite sprite_left event_robot_summons" style="">
                    <div class="robot_summons">
                        <span class="summons_count"><?= $robot_info['robot_summoned'] ?></span>
                        <span class="summons_label"><?= $robot_info['robot_summoned'] == 1 ? 'Summon' : 'Summons' ?></span>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_favourite" style="" >
                    <? if($global_allow_editing): ?>
                        <a class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Toggle Favourite?">&hearts;</a>
                    <? else: ?>
                        <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</span>
                    <? endif; ?>
                </div>

                <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
                    <span class="title robot_type"><?= $robot_info['robot_name']?></span>
                    <span class="core robot_type">
                        <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/abilities/item-core-<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/icon_left_40x40.png);"></span></span>
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
                                <td  class="right">
                                    <label style="display: block; float: left;">Name :</label>
                                    <span class="robot_name robot_type robot_type_none"><?=$robot_info['robot_name']?></span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Level :</label>
                                    <? if($robot_info['robot_level'] >= 100): ?>
                                        <a data-tooltip-align="center" data-tooltip="<?= htmlentities(('Congratulations! '.$robot_info['robot_name'].' has reached Level 100!<br /> <span style="font-size: 90%;">Stat bonuses will now be awarded immediately when this robot lands the finishing blow on a target! Try to max out your other stats!</span>'), ENT_QUOTES, 'UTF-8') ?>" class="robot_stat robot_type_electric"><span>&#9733;</span> <?= $robot_info['robot_level'] ?></a>
                                    <? else: ?>
                                        <span class="robot_stat robot_level_reset robot_type_<?= !empty($robot_rewards['flags']['reached_max_level']) ? 'electric' : 'none' ?>"><?= !empty($robot_rewards['flags']['reached_max_level']) ? '<span>&#9733;</span>' : '' ?> <?= $robot_info['robot_level'] ?></span>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="player_select_block right">
                                    <?
                                    $player_style = '';
                                    $robot_info['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : $player_info['player_token'];
                                    if ($player_info['player_token'] != $robot_info['original_player']){
                                        if ($counter_player_robots > 1){ $allow_player_selector = true; }
                                    }
                                    ?>
                                    <? if($robot_info['original_player'] != $player_info['player_token']): ?>
                                        <label title="<?= 'Transferred from Dr. '.ucfirst(str_replace('dr-', '', $robot_info['original_player'])) ?>"  class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                                    <? else: ?>
                                        <label class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                                    <? endif; ?>

                                    <?if($global_allow_editing && $allow_player_selector):?>
                                        <a title="Transfer Robot?" class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>"><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?><span class="arrow">&#8711;</span></label><select class="player_name" <?= !$allow_player_selector ? 'disabled="disabled"' : '' ?> data-player="<?=$player_info['player_token']?>" data-robot="<?=$robot_info['robot_token']?>"><?= str_replace('value="'.$player_info['player_token'].'"', 'value="'.$player_info['player_token'].'" selected="selected"', $player_options_markup) ?></select></a>
                                    <?elseif(!$global_allow_editing && $allow_player_selector):?>
                                        <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="cursor: default; "><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); cursor: default; "><?=$player_info['player_name']?></label></a>
                                    <?else:?>
                                        <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?></label><select class="player_name" disabled="disabled" data-player="<?=$player_info['player_token']?>" data-robot="<?=$robot_info['robot_token']?>"><?= str_replace('value="'.$player_info['player_token'].'"', 'value="'.$player_info['player_token'].'" selected="selected"', $player_options_markup) ?></select></a>
                                    <?endif;?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Exp :</label>
                                    <? if($robot_info['robot_level'] >= 100): ?>
                                        <span class="robot_stat robot_type_experience" title="Max Experience!"><span>&#8734;</span> / 1000</span>
                                    <? else: ?>
                                        <span class="robot_stat"><?= $robot_info['robot_experience'] ?> / 1000</span>
                                    <? endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Weaknesses :</label>
                                    <?
                                    if (!empty($robot_info['robot_weaknesses'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                            $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.(!empty($robot_weakness) ? $robot_weakness : 'none').'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_weakness">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_energy']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Energy :</label>
                                    <?
                                    // Print out the energy stat breakdown
                                    $print_robot_stat_function('energy');
                                    $print_robot_stat_function('weapons');
                                    ?>
                                </td>

                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Resistances :</label>
                                    <?
                                    if (!empty($robot_info['robot_resistances'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                            $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.(!empty($robot_resistance) ? $robot_resistance : 'none').'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_resistance">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_attack']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Attack :</label>
                                    <?
                                    // Print out the attack stat breakdown
                                    $print_robot_stat_function('attack');
                                    ?>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Affinities :</label>
                                    <?
                                    if (!empty($robot_info['robot_affinities'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                            $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.(!empty($robot_affinity) ? $robot_affinity : 'none').'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_affinity">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_defense']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Defense :</label>
                                    <?
                                    // Print out the defense stat breakdown
                                    $print_robot_stat_function('defense');
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="right">
                                    <label style="display: block; float: left;">Immunities :</label>
                                    <?
                                    if (!empty($robot_info['robot_immunities'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                            $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.(!empty($robot_immunity) ? $robot_immunity : 'none').'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_immunity">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_speed']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Speed :</label>
                                    <?
                                    // Print out the speed stat breakdown
                                    $print_robot_stat_function('speed');
                                    ?>
                                </td>
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
                                    <label style="display: block; float: left; font-size: 12px;">Abilities :</label>
                                    <div class="ability_container" style="height: auto;">
                                    <?

                                    // Define the array to hold ALL the reward option markup
                                    $ability_rewards_options = '';

                                    // Sort the ability index based on ability number
                                    uasort($player_ability_rewards, array('rpg_player', 'abilities_sort_for_editor'));

                                    // Dont' bother generating option dropdowns if editing is disabled
                                    if ($global_allow_editing){

                                        $player_ability_rewards_options = array();
                                        foreach ($player_ability_rewards AS $temp_ability_key => $temp_ability_info){
                                            if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                            $temp_token = $temp_ability_info['ability_token'];
                                            $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                            $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                            if (!empty($temp_option_markup)){ $player_ability_rewards_options[] = $temp_option_markup; }
                                        }
                                        $player_ability_rewards_options = '<optgroup label="Player Abilities">'.implode('', $player_ability_rewards_options).'</optgroup>';
                                        $ability_rewards_options .= $player_ability_rewards_options;

                                        // Collect this robot's ability rewards and add them to the dropdown
                                        $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
                                        $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
                                        foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }
                                        if (!empty($robot_ability_rewards)){ sort($robot_ability_rewards); }
                                        $robot_ability_rewards_options = array();
                                        foreach ($robot_ability_rewards AS $temp_ability_info){
                                            if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                            $temp_token = $temp_ability_info['ability_token'];
                                            $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                            $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                            if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
                                        }
                                        $robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
                                        $ability_rewards_options .= $robot_ability_rewards_options;

                                        // Add an option at the bottom to remove the ability
                                        $ability_rewards_options .= '<optgroup label="Ability Actions">';
                                        $ability_rewards_options .= '<option value="" title="">- Remove Ability -</option>';
                                        $ability_rewards_options .= '</optgroup>';

                                    }

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
                                            $this_ability = rpg_ability::parse_index_info($mmrpg_database_abilities[$robot_ability['ability_token']]);
                                            if (empty($this_ability)){ continue; }
                                            $this_ability_token = $this_ability['ability_token'];
                                            $this_ability_name = $this_ability['ability_name'];
                                            $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                            $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                                            if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){
                                                $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type';
                                                if (!empty($this_ability_type2) && !empty($mmrpg_index['types'][$this_ability_type2])){
                                                    $this_ability_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_ability_type2]['type_name'].' Type', $this_ability_type);
                                                }
                                            } else {
                                                $this_ability_type = '';
                                            }
                                            $this_ability_energy = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 4;
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
                                            $this_ability_title = rpg_ability::print_editor_title_markup($robot_info, $this_ability);
                                            $this_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_ability_title));
                                            $this_ability_title_tooltip = htmlentities($this_ability_title, ENT_QUOTES, 'UTF-8');
                                            $this_ability_title_html = str_replace(' ', '&nbsp;', $this_ability_name);
                                            $temp_select_options = str_replace('value="'.$this_ability_token.'"', 'value="'.$this_ability_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);
                                            $this_ability_title_html = '<label style="background-image: url(images/abilities/'.$this_ability_token.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_ability_title_html.'</label>';
                                            if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
                                            $temp_string[] = '<a class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'" style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="'.$this_ability_token.'" title="'.$this_ability_title_plain.'" data-tooltip="'.$this_ability_title_tooltip.'">'.$this_ability_title_html.'</a>';
                                            $ability_key++;
                                        }

                                        if ($ability_key <= 7){
                                            for ($ability_key; $ability_key <= 7; $ability_key++){
                                                $empty_ability_counter++;
                                                if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                                else { $empty_ability_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $ability_rewards_options);
                                                $this_ability_title_html = '<label>-</label>';
                                                if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="ability_name " style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="" title="" data-tooltip="">'.$this_ability_title_html.'</a>';
                                            }
                                        }


                                    } else {

                                        for ($ability_key = 0; $ability_key <= 7; $ability_key++){
                                            $empty_ability_counter++;
                                            if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                            else { $empty_ability_disable = false; }
                                            $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $ability_rewards_options);
                                            $this_ability_title_html = '<label>-</label>';
                                            if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                            $temp_string[] = '<a class="ability_name " style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_ability_title_html.'</a>';
                                        }

                                    }

                                    echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                    echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';

                                    ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?
            $key_counter++;

            // Return the backup of the player selector
            $allow_player_selector = $allow_player_selector_backup;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;
    }

    // Define a function for calculating robot stat details
    public static function calculate_stat_values($level, $base_stats, $bonus_stats = array(), $limit = false){
        // Define the four basic stat tokens
        $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
        // Define the robot stats array to return
        $robot_stats = array();
        // Collect the robot's current level
        $robot_stats['level'] = $level;
        $robot_stats['level_max'] = 100;
        // Loop through each stat and calculate values
        foreach ($stat_tokens AS $key => $stat){
            $robot_stats[$stat]['base'] = $base_stats['robot_'.$stat];
            if ($stat != 'weapons'){
                $robot_stats[$stat]['base_max'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level_max']);
                $robot_stats[$stat]['bonus'] = isset($bonus_stats['robot_'.$stat]) ? $bonus_stats['robot_'.$stat] : 0;
                $robot_stats[$stat]['bonus_max'] = $stat != 'energy' ? round($robot_stats[$stat]['base_max'] * MMRPG_SETTINGS_STATS_BONUS_MAX) : 0;
                if ($limit && $robot_stats[$stat]['bonus'] > $robot_stats[$stat]['bonus_max']){ $robot_stats[$stat]['bonus'] = $robot_stats[$stat]['bonus_max']; }
                $robot_stats[$stat]['current'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level']) + $robot_stats[$stat]['bonus'];
                $robot_stats[$stat]['current_noboost'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $level);
                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base_max'] + $robot_stats[$stat]['bonus_max'];
                if ($robot_stats[$stat]['current'] > $robot_stats[$stat]['max']){
                    $robot_stats[$stat]['over'] = $robot_stats[$stat]['current'] - $robot_stats[$stat]['max'];
                }
            } else {
                $robot_stats[$stat]['base_max'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['bonus'] = 0;
                $robot_stats[$stat]['bonus_max'] = 0;
                $robot_stats[$stat]['current'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['current_noboost'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base'];

            }
        }
        return $robot_stats;
    }

    // Define a function for calculating a robot stat level boost
    public static function calculate_level_boosted_stat($base, $level){
        $stat_boost = round( $base + ($base * 0.05 * ($level - 1)) );
        return $stat_boost;
    }


}
?>