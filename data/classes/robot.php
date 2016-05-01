<?
// Define a class for the robots
class mmrpg_robot {

    // Define global class variables
    public $flags;
    public $counters;
    public $values;
    public $history;

    // Define the constructor class
    public function mmrpg_robot(){

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal class identifier
        $this->class = 'robot';

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
                if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "\$this_robotinfo = mmrpg_robot::get_index_info({$this_robotinfo['robot_token']}); on line ".__LINE__;  }
                $this_robotinfo = mmrpg_robot::get_index_info($this_robotinfo['robot_token']);
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
        $this->robot_frame_offset = isset($this_robotinfo['robot_frame_offset']) ? $this_robotinfo['robot_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
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

        // If this is a player-controlled robot, load abilities from session
        if ($this->player->player_side == 'left' && empty($this->flags['apply_session_abilities'])){
            // Collect the abilities for this robot from the session
            $temp_robot_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);
            if (!empty($temp_robot_settings['robot_abilities'])){
                $temp_robot_abilities = $temp_robot_settings['robot_abilities'];
                $this->robot_abilities = array();
                foreach ($temp_robot_abilities AS $token => $info){ $this->robot_abilities[] = $token; }
            }
            // Set the session ability flag to true
            $this->flags['apply_session_abilities'] = true;
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



        // Update the session variable
        $this->update_session();

        // DEBUG
        /*
        if ($this_robotinfo['robot_token'] == 'mega-man'){
            die("\nmmrpg_robot()::".__LINE__.
                "\n".':: <pre>$this_robotinfo_backup:'.print_r($this_robotinfo_backup, true).'</pre>'.
                "\n".':: <pre>$this_robotinfo:'.print_r($this_robotinfo, true).'</pre>');
        }
        */

        // Return true on success
        return true;

    }

    // Define a public function for applying robot stat bonuses
    public function apply_stat_bonuses(){
        // Pull in the global index
        global $mmrpg_index;
        // Only continue if this hasn't been done already
        if (!empty($this->flags['apply_stat_bonuses'])){ return false; }
        // Require the external function for applying stat bonuses
        require('robot_apply-stat-bonuses.php');
        // Return true on success
        return true;
    }

    // Define public print functions for markup generation
    public function print_robot_number(){ return '<span class="robot_number">'.$this->robot_number.'</span>'; }
    public function print_robot_name(){ return '<span class="robot_name robot_type">'.$this->robot_name.'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
    public function print_robot_token(){ return '<span class="robot_token">'.$this->robot_token.'</span>'; }
    public function print_robot_core(){ return '<span class="robot_core '.(!empty($this->robot_core) ? 'robot_type_'.$this->robot_core : '').'">'.(!empty($this->robot_core) ? ucfirst($this->robot_core) : 'Neutral').'</span>'; }
    public function print_robot_description(){ return '<span class="robot_description">'.$this->robot_description.'</span>'; }
    public function print_robot_energy(){ return '<span class="robot_stat robot_stat_energy">'.$this->robot_energy.'</span>'; }
    public function print_robot_base_energy(){ return '<span class="robot_stat robot_stat_base_energy">'.$this->robot_base_energy.'</span>'; }
    public function print_robot_attack(){ return '<span class="robot_stat robot_stat_attack">'.$this->robot_attack.'</span>'; }
    public function print_robot_base_attack(){ return '<span class="robot_stat robot_stat_base_attack">'.$this->robot_base_attack.'</span>'; }
    public function print_robot_defense(){ return '<span class="robot_stat robot_stat_defense">'.$this->robot_defense.'</span>'; }
    public function print_robot_base_defense(){ return '<span class="robot_stat robot_stat_base_defense">'.$this->robot_base_defense.'</span>'; }
    public function print_robot_speed(){ return '<span class="robot_stat robot_stat_speed">'.$this->robot_speed.'</span>'; }
    public function print_robot_base_speed(){ return '<span class="robot_stat robot_stat_base_speed">'.$this->robot_base_speed.'</span>'; }
    public function print_robot_weaknesses(){
        $this_markup = array();
        foreach ($this->robot_weaknesses AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_resistances(){
        $this_markup = array();
        foreach ($this->robot_resistances AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_affinities(){
        $this_markup = array();
        foreach ($this->robot_affinities AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_immunities(){
        $this_markup = array();
        foreach ($this->robot_immunities AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_quote($quote_type, $this_find = array(), $this_replace = array()){
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


    // Define a function for checking if this robot has a specific ability
    public function has_ability($ability_token){
        if (empty($this->robot_abilities) || empty($ability_token)){ return false; }
        elseif (in_array($ability_token, $this->robot_abilities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is compatible with a specific ability
    static public function has_ability_compatibility($robot_token, $ability_token){
        global $mmrpg_index;
        if (empty($robot_token) || empty($ability_token)){ return false; }
        $robot_info = is_array($robot_token) ? $robot_token : mmrpg_robot::get_index_info($robot_token);
        $ability_info = is_array($ability_token) ? $ability_token : mmrpg_ability::get_index_info($ability_token);
        if (empty($robot_info) || empty($ability_info)){ return false; }
        $robot_token = $robot_info['robot_token'];
        $ability_token = $ability_info['ability_token'];
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'has_ability_compatibility('.$robot_token.', '.$ability_token.')');  }
        // Define the compatibility flag and default to false
        $temp_compatible = false;
        // If this ability has a type, check it against this robot
        if (!empty($ability_info['ability_type']) || !empty($ability_info['ability_type2'])){
            //$debug_fragment .= 'has-type '; // DEBUG
            if (!empty($robot_info['robot_core'])){
            //$debug_fragment .= 'has-core '; // DEBUG
                if ($robot_info['robot_core'] == 'copy'){
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
        //$robot_info['robot_abilities']
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'has_ability_compatibility('.$robot_token.', '.$ability_token.') = '.($temp_compatible ? 'true' : 'false').'<br /> <pre>'.print_r($robot_info['robot_abilities'], true).'</pre>');  }
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
        global $DB;
        // Create the ability options and weights variables
        $options = array();
        $weights = array();
        // Define the support multiplier for this robot
        $support_multiplier = 1;
        if (in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm'))){ $support_multiplier += 1; }
        // Define the freency of the default buster ability if set
        if ($this_robot->has_ability('buster-shot')){ $options[] = 'buster-shot'; $weights[] = $this_robot->robot_token == 'met' ? 90 : 1;  }
        if ($this_robot->has_ability('super-throw')){ $options[] = 'super-throw'; $weights[] = 1;  }
        // Define the frequency of the energy boost ability if set
        if ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy >= $this_robot->robot_base_energy){ $options[] = 'energy-boost'; $weights[] = 0;  }
        elseif ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy < ($this_robot->robot_base_energy / 4)){ $options[] = 'energy-boost'; $weights[] = 14 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy < ($this_robot->robot_base_energy / 3)){ $options[] = 'energy-boost'; $weights[] = 12 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy < ($this_robot->robot_base_energy / 2)){ $options[] = 'energy-boost'; $weights[] = 10 * $support_multiplier;  }
        // Define the frequency of the energy break ability if set
        if ($this_robot->has_ability('energy-break') && $target_robot->robot_energy >= $target_robot->robot_base_energy){ $options[] = 'energy-break'; $weights[] = 28 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-break') && $target_robot->robot_energy < ($target_robot->robot_base_energy / 4)){ $options[] = 'energy-break'; $weights[] = 10 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-break') && $target_robot->robot_energy < ($target_robot->robot_base_energy / 3)){ $options[] = 'energy-break'; $weights[] = 12 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-break') && $target_robot->robot_energy < ($target_robot->robot_base_energy / 2)){ $options[] = 'energy-break'; $weights[] = 14 * $support_multiplier;  }
        // Define the frequency of the energy swap ability if set
        if ($this_robot->has_ability('energy-swap') && $target_robot->robot_energy > $this_robot->robot_energy){ $options[] = 'energy-swap'; $weights[] = 28 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-swap') && $target_robot->robot_energy <= $this_robot->robot_energy){ $options[] = 'energy-swap'; $weights[] = 0;  }
        // Define the frequency of the attack, defense, and speed boost abiliies if set
        if ($this_robot->has_ability('attack-boost') && $this_robot->robot_attack < ($this_robot->robot_base_attack * 0.5)){ $options[] = 'attack-boost'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-boost')){ $options[] = 'attack-boost'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-boost') && $this_robot->robot_defense < ($this_robot->robot_base_defense * 0.5)){ $options[] = 'defense-boost'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-boost')){ $options[] = 'defense-boost'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-boost') && $this_robot->robot_speed < ($this_robot->robot_base_speed * 0.5)){ $options[] = 'speed-boost'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-boost')){ $options[] = 'speed-boost'; $weights[] = 1;  }
        // Define the frequency of the attack, defense, and speed break abilities if set
        if ($this_robot->has_ability('attack-break') && $target_robot->robot_attack > $this_robot->robot_defense){ $options[] = 'attack-break'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-break')){ $options[] = 'attack-break'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-break') && $target_robot->robot_defense > $this_robot->robot_attack){ $options[] = 'defense-break'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-break')){ $options[] = 'defense-break'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-break') && $this_robot->robot_speed < $target_robot->robot_speed){ $options[] = 'speed-break'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-break')){ $options[] = 'speed-break'; $weights[] = 1;  }
        // Define the frequency of the attack, defense, and speed swap abilities if set
        if ($this_robot->has_ability('attack-swap') && $target_robot->robot_attack > $this_robot->robot_attack){ $options[] = 'attack-swap'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-swap')){ $options[] = 'attack-swap'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-swap') && $target_robot->robot_defense > $this_robot->robot_defense){ $options[] = 'defense-swap'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-swap')){ $options[] = 'defense-swap'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-swap') && $target_robot->robot_speed > $this_robot->robot_speed){ $options[] = 'speed-swap'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-swap')){ $options[] = 'speed-swap'; $weights[] = 1;  }
        // Define the frequency of the repair mode ability if set
        if ($this_robot->has_ability('repair-mode') && $this_robot->robot_energy < ($this_robot->robot_base_energy * 0.5)){ $options[] = 'repair-mode'; $weights[] = 9 * $support_multiplier;  }
        elseif ($this_robot->has_ability('repair-mode')){ $options[] = 'repair-mode'; $weights[] = 1;  }
        // Define the frequency of the attack, defense, and speed mode abilities if set
        if ($this_robot->has_ability('attack-mode') && $this_robot->robot_attack < ($this_robot->robot_base_attack * 0.10)){ $options[] = 'attack-mode'; $weights[] = 6 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-mode')){ $options[] = 'attack-mode'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-mode') && $this_robot->robot_defense < ($this_robot->robot_base_defense * 0.10)){ $options[] = 'defense-mode'; $weights[] = 6 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-mode')){ $options[] = 'defense-mode'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-mode') && $this_robot->robot_speed < ($this_robot->robot_base_speed * 0.10)){ $options[] = 'speed-mode'; $weights[] = 6 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-mode')){ $options[] = 'speed-mode'; $weights[] = 1;  }
        // Loop through any leftover abilities and add them to the weighted ability options
        $temp_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
        foreach ($this_robot->robot_abilities AS $key => $token){
            if (!in_array($token, $options)){
                $info = mmrpg_ability::parse_index_info($temp_ability_index[$token]);
                $value = 3;
                if (!empty($this_robot->robot_core) && !empty($info['ability_type'])){
                    if ($this_robot->robot_core == $info['ability_type']){ $value = 50; }
                    elseif ($this_robot->robot_core == 'copy'){ $value = 40; }
                    elseif ($this_robot->robot_core != $info['ability_type']){ $value = 30; }
                    if (preg_match('/^(attack|defense|speed)-(burn|blaze)$/i', $token)){ $value = ceil($value * 0.10); }
                } elseif (empty($this_robot->robot_core)){
                    $value = 30;
                } else {
                    $value = 3;
                }
                $options[] = $token;
                $weights[] = $value;
            }
        }
        // Return an ability based on a weighted chance
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'robot_choices_abilities('.$this_robot->robot_token.')<br /> $options = '.implode(',', $options).'<br /> $weights = '.implode(',', $weights).'<br /> $this_robot->robot_abilities = '.implode(',', $this_robot->robot_abilities));  }
        return $this_battle->weighted_chance($options, $weights);
    }

    // Define a trigger for using one of this robot's abilities
    public function trigger_ability($target_robot, $this_ability){
        global $DB;
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

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
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_ability->target_options_reset();
        $this_ability->damage_options_reset();
        $this_ability->recovery_options_reset();

        // Determine how much weapon energy this should take
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $temp_ability_energy = $this->calculate_weapon_energy($this_ability);

        // Decrease this robot's weapon energy
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
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
        if (!preg_match('/^item-/', $this_ability->ability_token) && !empty($temp_ability_type) && $this->robot_base_core == 'copy'){
            $this->robot_image_overlay['copy_type1'] = $this->robot_base_image.'_'.$temp_ability_type.'2';
            $this->robot_image_overlay['copy_type2'] = $this->robot_base_image.'_'.$temp_ability_type2.'3';
            $this->update_session();
            $temp_image_changed = true;
        }

        // Copy the ability function to local scope and execute it
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
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
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;
        // Update this ability's history with the triggered ability damage options
        $this_ability->history['ability_options'][] = $this_ability->ability_options;

        // Reset the robot's stances to the base
        $this->robot_stance = 'base';
        $target_robot->robot_stance = 'base';

        // Update internal variables
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $target_robot->update_session();
        $this_ability->update_session();


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        if (!empty($this->robot_attachments)){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $temp_attachments_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            foreach ($this->robot_attachments AS $attachment_token => $attachment_info){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Ensure this ability has a type before checking weaknesses, resistances, etc.
                if (!empty($this_ability->ability_type)){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // If this attachment has weaknesses defined and this ability is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses']) || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))){
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        $this->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            $temp_ability = mmrpg_ability::parse_index_info($temp_attachments_index[$attachment_info['ability_token']]);
                            $attachment_info = array_merge($temp_ability, $attachment_info);
                            $temp_attachment = new mmrpg_ability($this->battle, $this->player, $this, $attachment_info);
                            $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                            if ($temp_trigger_type == 'damage'){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                    $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'recovery'){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                    $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'special'){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            break;
                        }
                    }

                }

            }
        }

        // Update internal variables
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        return $this_ability->ability_results;
    }

    // Define a trigger for using one of this robot's attachments
    public function trigger_attachment($attachment_info){
        global $DB;

        // If this is an ability attachment
        if ($attachment_info['class'] == 'ability'){

            // Create the temporary ability object
            $this_ability = new mmrpg_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));

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

    }

//  // Define separate trigger functions for each type of damage on this robot
//  public function trigger_energy_damage($target_robot, $this_ability, &$ability_results, $damage_amount, &$damage_options){
//    $this->trigger_damage('energy', $target_robot, $this_ability, &$ability_results, $damage_amount, &$damage_options);
//  }

    // Define a trigger for using one of this robot's abilities
    public function trigger_target($target_robot, $this_ability, $trigger_options = array()){
        global $DB;

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
        $event_options['this_ability_target_key'] = $target_robot->robot_key;
        $event_options['this_ability_target_position'] = $target_robot->robot_position;
        $event_options['this_ability_results'] = array();
        $event_options['console_show_target'] = false;

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update this robot's history with the triggered ability
        $this->history['triggered_targets'][] = $target_robot->robot_token;

        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Update this robot's frames using the target options
        $this->robot_frame = $this_ability->target_options['target_frame'];
        if ($this->robot_id != $target_robot->robot_id){ $target_robot->robot_frame = 'defend'; }
        $this->player->player_frame = 'command';
        $this->player->update_session();
        $this_ability->ability_frame = $this_ability->target_options['ability_success_frame'];
        $this_ability->ability_frame_span = $this_ability->target_options['ability_success_frame_span'];
        $this_ability->ability_frame_offset = $this_ability->target_options['ability_success_frame_offset'];

        // If the target player is on the bench, alter the ability scale
        $temp_ability_styles_backup = $this_ability->ability_frame_styles;
        if ($target_robot->robot_position == 'bench' && $event_options['this_ability_target'] != $this->robot_id.'_'.$this->robot_token){
            $temp_scale = 1 - ($target_robot->robot_key * 0.06);
            $temp_translate = 20 + ($target_robot->robot_key * 20);
            $temp_translate2 = ceil($temp_translate / 10) * -1;
            $temp_translate = $temp_translate * ($target_robot->player->player_side == 'left' ? -1 : 1);
            //$this_ability->ability_frame_styles .= 'border: 1px solid red !important; ';
            $this_ability->ability_frame_styles .= 'transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -webkit-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -moz-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); ';
        }

        // Create a message to show the initial targeting action
        if ($this->robot_id != $target_robot->robot_id && empty($trigger_options['prevent_default_text'])){
            $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} targets {$target_robot->print_robot_name()}!<br />";
        } else {
            //$this_ability->ability_results['this_text'] .= ''; //"{$this->print_robot_name()} targets itself&hellip;<br />";
        }

        // Append the targetting text to the event body
        $this_ability->ability_results['this_text'] .= $this_ability->target_options['target_text'];

        // Update the ability results with the the trigger kind
        $this_ability->ability_results['trigger_kind'] = 'target';
        $this_ability->ability_results['this_result'] = 'success';

        // Update the event options with the ability results
        $event_options['this_ability_results'] = $this_ability->ability_results;
        if (isset($trigger_options['canvas_show_this_ability'])){ $event_options['canvas_show_this_ability'] = $trigger_options['canvas_show_this_ability'];  }

        /*
        // If this is a non-transformed copy robot, change its colour
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) && $this_ability->ability_type != 'copy' ? $this_ability->ability_type : '';
        if ($this->robot_base_core == 'copy' && $this->robot_core != $temp_ability_type){
            $this_backup_image = $this->robot_image;
            $this->robot_image = $this->robot_base_image.'_'.$temp_ability_type;
            $this->update_session();
            $temp_image_changed = true;
        }
        */

        // Create a new entry in the event log for the targeting event
        $this->battle->events_create($this, $target_robot, $this_ability->target_options['target_header'], $this_ability->ability_results['this_text'], $event_options);

        /*
        // If this is a non-transformed copy robot, change its colour
        if ($temp_image_changed){
            $this->robot_image = $this_backup_image;
            $this->update_session();
        }
        */

        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;

        // Refresh the ability styles from any changes
        $this_ability->ability_frame_styles = ''; //$temp_ability_styles_backup;

        // restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->player_frame = $this_player_backup_frame;
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->player_frame = $target_player_backup_frame;
        $this_ability->ability_frame = $this_ability_backup_frame;
        $this_ability->target_options_reset();

        // Update internal variables
        $this->update_session();
        $this->player->update_session();
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;

    }

    // Define a trigger for inflicting all types of damage on this robot
    public function trigger_damage($target_robot, $this_ability, $damage_amount, $trigger_disabled = true, $trigger_options = array()){
        global $DB;
        // Generate default trigger options if not set
        if (!isset($trigger_options['apply_modifiers'])){ $trigger_options['apply_modifiers'] = true; }
        if (!isset($trigger_options['apply_type_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_type_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_core_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_core_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_position_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_position_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_field_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_field_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_starforce_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_starforce_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_stat_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_stat_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['referred_damage'])){ $trigger_options['referred_damage'] = false; }
        // Require the external function for triggering damage
        require('robot_trigger-damage.php');
        // Return the final damage results
        return $this_ability->ability_results;
    }


    // Define a trigger for inflicting all types of recovery on this robot
    public function trigger_recovery($target_robot, $this_ability, $recovery_amount, $trigger_disabled = true, $trigger_options = array()){
        global $DB;
        // Generate default trigger options if not set
        if (!isset($trigger_options['apply_modifiers'])){ $trigger_options['apply_modifiers'] = true; }
        if (!isset($trigger_options['apply_type_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_type_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_core_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_core_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_field_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_field_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_starforce_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_starforce_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_position_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_position_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_stat_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_stat_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['referred_recovery'])){ $trigger_options['referred_recovery'] = false; }
        // Require the external function for triggering recovery
        require('robot_trigger-recovery.php');
        // Return the final recovery results
        return $this_ability->ability_results;
    }

    // Define a trigger for processing disabled events
    public function trigger_disabled($target_robot, $this_ability, $trigger_options = array()){
        // Pull in the global variable
        global $mmrpg_index, $DB;
        // Generate default trigger options if not set
        if (!isset($trigger_options['item_multiplier'])){ $trigger_options['item_multiplier'] = 1.0; }
        // Require the external function for triggering disabled
        require('robot_trigger-disabled.php');
        // Return true on success
        return true;
    }

    // Define a function for calculating required weapon energy
    public function calculate_weapon_energy($this_ability, &$energy_base = 0, &$energy_mods = 0){
        // Determine how much weapon energy this should take
        $energy_new = $this_ability->ability_energy;
        $energy_base = $energy_new;
        $energy_mods = 0;
        if ($this_ability->ability_token != 'action-noweapons'){
            if (!empty($this->robot_core) && ($this->robot_core == $this_ability->ability_type || $this->robot_core == $this_ability->ability_type2)){
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
        // Require the external function for generating canvas markup
        require('robot_canvas-markup.php');
        // Return the robot canvas data
        return $this_data;
    }

    // Define a function for generating robot console variables
    public function console_markup($options, $player_data){
        // Require the external function for generating console markup
        require('robot_console-markup.php');
        // Return the robot console data
        return $this_data;
    }

    // Define a function for pulling the full robot index
    public static function get_index($filter = array()){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "get_index()");  }
        global $DB;

        // If a filter was defined, parse it's values for the query
        if (!empty($filter) && is_array($filter)){
                $where_filter = array();
                $filter_ids = array();
                $filter_tokens = array();
                foreach ($filter AS $key => $value){
                        if (is_numeric($value)){ $filter_ids[] = $value; }
                        else { $filter_tokens[] = "'{$value}'"; }
                }
                if (!empty($filter_ids)){ $where_filter[] = 'robot_id IN ('.implode(', ', $filter_ids).')'; }
                if (!empty($filter_tokens)){ $where_filter[] = 'robot_token IN ('.implode(', ', $filter_tokens).')'; }
                if (!empty($where_filter)){ $where_filter = 'AND ('.implode(' OR ', $where_filter).') '; }
                else { $where_filter = ''; }
        } else {
                $where_filter = '';
        }

        // Collect the robot index from the database using any filters
        $robot_index = $DB->get_array_list("SELECT *
                FROM mmrpg_index_robots
                WHERE robot_flag_complete = 1 {$where_filter}
                ;", 'robot_token');

        // Return the robot index, empty or not
        if (!empty($robot_index)){ return $robot_index; }
        else { return array(); }

    }
    // Define a public function for collecting index data from the database
    public static function get_index_info($robot_token){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "get_index_info('{$robot_token}')");  }
        global $DB;
        $robot_index = mmrpg_robot::get_index(array($robot_token));
        if (!empty($robot_index[$robot_token])){ $robot_info = mmrpg_robot::parse_index_info($robot_index[$robot_token]); }
        else { $robot_info = array(); }
        return $robot_info;
    }
    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($robot_info){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "parse_index_info(\$robot_info:{$robot_info['robot_token']})");  }

        // Return false if empty
        if (empty($robot_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($robot_info['_parsed'])){ return $robot_info; }
        else { $robot_info['_parsed'] = true; }

        // Explode the weaknesses, resistances, affinities, and immunities into an array
        $temp_field_names = array('robot_field2', 'robot_weaknesses', 'robot_resistances', 'robot_affinities', 'robot_immunities');
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
        $robot_info['robot_quotes']['battle_start'] = !empty($robot_info['robot_quotes_start']) ? $robot_info['robot_quotes_start']: '';
        $robot_info['robot_quotes']['battle_taunt'] = !empty($robot_info['robot_quotes_taunt']) ? $robot_info['robot_quotes_taunt']: '';
        $robot_info['robot_quotes']['battle_victory'] = !empty($robot_info['robot_quotes_victory']) ? $robot_info['robot_quotes_victory']: '';
        $robot_info['robot_quotes']['battle_defeat'] = !empty($robot_info['robot_quotes_defeat']) ? $robot_info['robot_quotes_defeat']: '';
        unset($robot_info['robot_quotes_start'], $robot_info['robot_quotes_taunt'], $robot_info['robot_quotes_victory'], $robot_info['robot_quotes_defeat']);

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
        // Require the external function for generating database markup
        require('robot_database-markup.php');
        // Return the generated markup
        return $this_markup;
    }

    // Define a static function for printing out the robot's editor markup
    public static function print_editor_markup($player_info, $robot_info, $mmrpg_database_abilities = array()){
        // Require the external function for generating editor markup
        require('robot_editor-markup.php');
        // Return the generated markup
        return $this_markup;
    }

    // Define a function for calculating robot stat details
    public static function calculate_stat_values($level, $base_stats, $bonus_stats = array(), $limit = false){
        // Define the four basic stat tokens
        $stat_tokens = array('energy', 'attack', 'defense', 'speed');
        // Define the robot stats array to return
        $robot_stats = array();
        // Collect the robot's current level
        $robot_stats['level'] = $level;
        $robot_stats['level_max'] = 100;
        // Loop through each stat and calculate values
        foreach ($stat_tokens AS $key => $stat){
            $robot_stats[$stat]['base'] = $base_stats['robot_'.$stat];
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