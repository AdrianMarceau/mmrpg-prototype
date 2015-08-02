<?
// Define a class for the fields
class mmrpg_field {
  
  // Define global class variables
  public $flags;
  public $counters;
  public $values;
  public $history;
  
  // Define the constructor class
  public function mmrpg_field(){
    
    // Collect any provided arguments
    $args = func_get_args();
    
    // Define the internal battle pointer
    $this->battle = isset($args[0]) ? $args[0] : array();
    $this->battle_id = $this->battle->battle_id;
    $this->battle_token = $this->battle->battle_token;
    
    // Collect current field data from the function if available
    $this_fieldinfo = isset($args[1]) ? $args[1] : array('field_id' => 0, 'field_token' => 'field');

    // Now load the field data from the session or index
    $this->field_load($this_fieldinfo);
    
    // Return true on success
    return true;
    
  }
  
  // Define a public function for manually loading data
  public function field_load($this_fieldinfo){
    // Pull in the global index
    global $mmrpg_index;
    
    // Collect current field data from the session if available
    $this_fieldinfo_backup = $this_fieldinfo;
    if (isset($_SESSION['FIELDS'][$this->battle->battle_id][$this_fieldinfo['field_id']])){
      $this_fieldinfo = $_SESSION['FIELDS'][$this->battle->battle_id][$this_fieldinfo['field_id']];
    }
    // Otherwise, collect field data from the index
    else {
      $this_fieldinfo = mmrpg_field::get_index_info($this_fieldinfo['field_token']);
    }
    $this_fieldinfo = array_replace($this_fieldinfo, $this_fieldinfo_backup);
    
    // Define the internal field values using the collected array
    $this->flags = isset($this_fieldinfo['flags']) ? $this_fieldinfo['flags'] : array();
    $this->counters = isset($this_fieldinfo['counters']) ? $this_fieldinfo['counters'] : array();
    $this->values = isset($this_fieldinfo['values']) ? $this_fieldinfo['values'] : array();
    $this->history = isset($this_fieldinfo['history']) ? $this_fieldinfo['history'] : array();
    $this->field_id = isset($this_fieldinfo['field_id']) ? $this_fieldinfo['field_id'] : 0;
    $this->field_name = isset($this_fieldinfo['field_name']) ? $this_fieldinfo['field_name'] : 'Field';
    $this->field_token = isset($this_fieldinfo['field_token']) ? $this_fieldinfo['field_token'] : 'field';
    $this->field_type = isset($this_fieldinfo['field_type']) ? $this_fieldinfo['field_type'] : '';
    $this->field_group = isset($this_fieldinfo['field_group']) ? $this_fieldinfo['field_group'] : '';
    $this->field_multipliers = isset($this_fieldinfo['field_multipliers']) ? $this_fieldinfo['field_multipliers'] : array();
    $this->field_overlays = isset($this_fieldinfo['field_overlays']) ? $this_fieldinfo['field_overlays'] : array();
    $this->field_mechas = isset($this_fieldinfo['field_mechas']) ? $this_fieldinfo['field_mechas'] : array();
    $this->field_description = isset($this_fieldinfo['field_description']) ? $this_fieldinfo['field_description'] : '';
    $this->field_background = isset($this_fieldinfo['field_background']) ? $this_fieldinfo['field_background'] : 'field';
    $this->field_foreground = isset($this_fieldinfo['field_foreground']) ? $this_fieldinfo['field_foreground'] : 'field';
    $this->field_background_attachments = isset($this_fieldinfo['field_background_attachments']) ? $this_fieldinfo['field_background_attachments'] : array();
    $this->field_foreground_attachments = isset($this_fieldinfo['field_foreground_attachments']) ? $this_fieldinfo['field_foreground_attachments'] : array();
    $this->field_music = isset($this_fieldinfo['field_music']) ? $this_fieldinfo['field_music'] : 'field';
    
    // Define the internal field base values using the fields index array
    $this->field_base_name = isset($this_fieldinfo['field_base_name']) ? $this_fieldinfo['field_base_name'] : $this->field_name;
    $this->field_base_token = isset($this_fieldinfo['field_base_token']) ? $this_fieldinfo['field_base_token'] : $this->field_token;
    $this->field_base_type = isset($this_fieldinfo['field_base_type']) ? $this_fieldinfo['field_base_type'] : $this->field_type;
    $this->field_base_multipliers = isset($this_fieldinfo['field_base_multipliers']) ? $this_fieldinfo['field_base_multipliers'] : $this->field_multipliers;
    $this->field_base_description = isset($this_fieldinfo['field_base_description']) ? $this_fieldinfo['field_base_description'] : $this->field_description;
    $this->field_base_background = isset($this_fieldinfo['field_base_background']) ? $this_fieldinfo['field_base_background'] : $this->field_background;
    $this->field_base_foreground = isset($this_fieldinfo['field_base_foreground']) ? $this_fieldinfo['field_base_foreground'] : $this->field_foreground;
    $this->field_base_background_attachments = isset($this_fieldinfo['field_base_background_attachments']) ? $this_fieldinfo['field_base_background_attachments'] : $this->field_background_attachments;
    $this->field_base_foreground_attachments = isset($this_fieldinfo['field_base_foreground_attachments']) ? $this_fieldinfo['field_base_foreground_attachments'] : $this->field_foreground_attachments;
    $this->field_base_music = isset($this_fieldinfo['field_base_music']) ? $this_fieldinfo['field_base_music'] : $this->field_music;
        
    // Update the session variable
    $this->update_session();
    
    // Return true on success
    return true;
    
  }
  
  // Define public print functions for markup generation
  public function print_field_name(){ return '<span class="field_name field_type field_type_'.(!empty($this->field_type) ? $this->field_type : 'none').'">'.$this->field_name.'</span>'; }
  //public function print_field_name(){ return '<span class="field_name field_type field_type_'.(!empty($this->field_type) ? $this->field_type : 'none').'">'.$this->field_name.'</span>'; }
  public function print_field_token(){ return '<span class="field_token">'.$this->field_token.'</span>'; }
  public function print_field_type(){ return '<span class="field_type field_type_'.(!empty($this->field_type) ? $this->field_type : 'none').'">'.!empty($this->field_type) ? ucfirst($this->field_type) : 'Neutral'.'</span>'; }
  public function print_field_group(){
    $temp_index = array('MMRPG' => 'Mega Man RPG Fields', 'MM00' => 'Mega Man 0 Fields', 'MM01' => 'Mega Man 1 Fields', 'MM02' => 'Mega Man 2 Fields', 'MM03' => 'Mega Man 3 Fields', 'MM04' => 'Mega Man 4 Fields');
    return '<span class="field_group field_group_'.(!empty($this->field_group) ? $this->field_group : 'MMRPG').'">'.!empty($this->field_group) ? $temp_index[$this->field_group] : 'Unknown'.'</span>';
  }
  public function print_field_description(){ return '<span class="field_description">'.$this->field_description.'</span>'; }
  public function print_field_background(){ return '<span class="field_background">'.$this->field_background.'</span>'; }
  public function print_field_foreground(){ return '<span class="field_foreground">'.$this->field_foreground.'</span>'; }
  
  // Define a function for pulling the full field index
  public static function get_index(){
    global $DB;
    $field_index = $DB->get_array_list("SELECT * FROM mmrpg_index_fields WHERE field_flag_complete = 1;", 'field_token');
    if (!empty($field_index)){ return $field_index; }
    else { return array(); }
  }
  // Define a public function for collecting index data from the database
  public static function get_index_info($field_token){
    global $DB;
    $field_index = mmrpg_field::get_index();
    if (!empty($field_index[$field_token])){ $field_info = mmrpg_field::parse_index_info($field_index[$field_token]); }
    else { $field_info = array(); }
    return $field_info;
  }
  // Define a public function for reformatting database data into proper arrays
  public static function parse_index_info($field_info){
    // Return false if empty
    if (empty($field_info)){ return false; }
    
    // If the information has already been parsed, return as-is
    if (!empty($field_info['_parsed'])){ return $field_info; }
    else { $field_info['_parsed'] = true; }
    
    // Explode the json encoded fields into an array
    $temp_field_names = array('field_master2', 'field_mechas', 'field_multipliers', 'field_music_link', 'field_background_frame', 'field_foreground_frame', 'field_background_attachments', 'field_foreground_attachments');
    foreach ($temp_field_names AS $field_name){
      if (!empty($field_info[$field_name])){ $field_info[$field_name] = json_decode($field_info[$field_name], true); }
      else { $field_info[$field_name] = array(); }
    }
    
    // Return the parsed field info
    return $field_info;
  }
  
  // Define a public function updating internal variables
  public function update_variables(){

    // Update parent objects first
    //$this->battle->update_variables();
    
    // Return true on success
    return true;
    
  }
  
  // Define a public function for updating this field's session
  public function update_session(){

    // Update any internal counters
    $this->update_variables();
    
    // Request parent battle object to update as well
    //$this->battle->update_session();
    
    // Update the session with the export array
    $this_data = $this->export_array();
    $_SESSION['FIELDS'][$this->battle->battle_id][$this->field_id] = $this_data;
    $this->battle->battle_field = &$this;  //new mmrpg_field($this->battle, $this->export_array());
    
    // Return true on success
    return true;
    
  }
  
  // Define a function for exporting the current data
  public function export_array(){
    
    // Return all internal field fields in array format
    return array(
      'battle_id' => $this->battle_id,
      'battle_token' => $this->battle_token,
      'field_id' => $this->field_id,
      'field_name' => $this->field_name,
      'field_token' => $this->field_token,
      'field_type' => $this->field_type,
      'field_group' => $this->field_group,
      'field_multipliers' => $this->field_multipliers,
      'field_mechas' => $this->field_mechas,
      'field_description' => $this->field_description,
      'field_background' => $this->field_background,
      'field_foreground' => $this->field_foreground,
      'field_background_attachments' => $this->field_background_attachments,
      'field_foreground_attachments' => $this->field_foreground_attachments,
      'field_music' => $this->field_music,
      'field_base_name' => $this->field_base_name,
      'field_base_token' => $this->field_base_token,
      'field_base_type' => $this->field_base_type,
      'field_base_multipliers' => $this->field_base_multipliers,
      'field_base_description' => $this->field_base_description,
      'field_base_background' => $this->field_base_background,
      'field_base_foreground' => $this->field_base_foreground,
      'field_base_background_attachments' => $this->field_base_background_attachments,
      'field_base_foreground_attachments' => $this->field_base_foreground_attachments,
      'field_base_music' => $this->field_base_music,
      'flags' => $this->flags,
      'counters' => $this->counters,
      'values' => $this->values,
      'history' => $this->history
      );
    
  }

  // Define a static function for printing out the field's database markup
  public static function print_database_markup($field_info, $print_options = array()){
    // Define the markup variable
    $this_markup = '';
    
    // Require the actual data file
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/field_database-markup.php');

    // Return the generated markup
    return $this_markup;

  }
  
  // Define a static function for printing out the field's title markup
  public static function print_editor_title_markup($player_info, $field_info){
    // Pull in global variables
    global $mmrpg_index, $DB;
    // Collect the approriate database indexes
    $mmrpg_database_robots = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    // Generate the field option markup
    $temp_player_token = $player_info['player_token'];
    if (!isset($mmrpg_index['players'][$temp_player_token])){ return false; }
    $player_info = $mmrpg_index['players'][$temp_player_token];
    $temp_field_token = $field_info['field_token'];
    $field_info = mmrpg_field::get_index_info($temp_field_token);
    if (empty($field_info)){ return false; }
    $player_flag_copycore = !empty($player_info['player_core']) && $player_info['player_core'] == 'copy' ? true : false;
    $temp_field_type = !empty($field_info['field_type']) ? $mmrpg_index['types'][$field_info['field_type']] : false;
    $temp_field_type2 = !empty($field_info['field_type2']) ? $mmrpg_index['types'][$field_info['field_type2']] : false;
    $temp_field_master = !empty($field_info['field_master']) ? mmrpg_robot::parse_index_info($mmrpg_database_robots[$field_info['field_master']]) : false;
    $temp_field_mechas = !empty($field_info['field_mechas']) ? $field_info['field_mechas'] : array();
    foreach ($temp_field_mechas AS $key => $token){
      $temp_mecha = mmrpg_robot::parse_index_info($mmrpg_database_robots[$token]);
      if (!empty($temp_mecha)){ $temp_field_mechas[$key] = $temp_mecha['robot_name'];  }
      else { unset($temp_field_mechas[$key]); }
    }
    $temp_field_title = $field_info['field_name'];
    if (!empty($temp_field_type)){ $temp_field_title .= ' ('.$temp_field_type['type_name'].' Type)'; }
    if (!empty($temp_field_type2)){ $temp_field_title = str_replace('Type', '/ '.$temp_field_type2['type_name'].' Type', $temp_field_title); }
    $temp_field_title .= '  // ';
    if (!empty($temp_field_master)){ $temp_field_title .= 'Robot : '.$temp_field_master['robot_name'].' // '; }
    if (!empty($temp_field_mechas)){ $temp_field_title .= 'Mecha : '.implode(', ', array_unique($temp_field_mechas)).' // '; }
    /*
    if (!empty($field_info['field_description'])){
      //$temp_find = array('{RECOVERY}', '{RECOVERY2}', '{DAMAGE}', '{DAMAGE2}');
      //$temp_replace = array($temp_field_recovery, $temp_field_recovery2, $temp_field_damage, $temp_field_damage2);
      //$temp_description = str_replace($temp_find, $temp_replace, $field_info['field_description']);
      //$temp_field_title .= ' <br />'.$temp_description;
      $temp_field_title .= $field_info['field_description'];
    }
    */
    // Return the generated option markup
    return $temp_field_title;
  }

  
  // Define a static function for printing out the field's title markup
  public static function print_editor_option_markup($player_info, $field_info){
    // Pull in global variables
    global $mmrpg_index;
    // Generate the field option markup
    $temp_player_token = $player_info['player_token'];
    if (!isset($mmrpg_index['players'][$temp_player_token])){ return false; }
    $player_info = $mmrpg_index['players'][$temp_player_token];
    $temp_field_token = $field_info['field_token'];
    $field_info = mmrpg_field::get_index_info($temp_field_token);
    if (empty($field_info)){ return false; }
    
    // DEBUG
    //if ($temp_player_token == 'oil-man' && $temp_field_token == 'oil-shooter'){ die('WHY?!'); }

    $temp_field_type = !empty($field_info['field_type']) ? $mmrpg_index['types'][$field_info['field_type']] : false;
    $temp_field_type2 = !empty($field_info['field_type2']) ? $mmrpg_index['types'][$field_info['field_type2']] : false;
    $temp_field_label = $field_info['field_name'];
    $temp_field_title = mmrpg_field::print_editor_title_markup($player_info, $field_info);
    $temp_field_title_plain = strip_tags(str_replace('<br />', '&#10;', $temp_field_title));
    $temp_field_title_tooltip = htmlentities($temp_field_title, ENT_QUOTES, 'UTF-8');
    $temp_field_option = $field_info['field_name'];
    if (!empty($temp_field_type)){ $temp_field_option .= ' | '.$temp_field_type['type_name']; }
    if (!empty($temp_field_type2)){ $temp_field_option .= ' / '.$temp_field_type2['type_name']; }
    //if (!empty($field_info['field_damage'])){ $temp_field_option .= ' | D:'.$field_info['field_damage']; }
    //if (!empty($field_info['field_recovery'])){ $temp_field_option .= ' | R:'.$field_info['field_recovery']; }
    //if (!empty($field_info['field_accuracy'])){ $temp_field_option .= ' | A:'.$field_info['field_accuracy']; }
    if (!empty($temp_field_energy)){ $temp_field_option .= ' | E:'.$temp_field_energy; }
    // Return the generated option markup
    $this_option_markup = '<option value="'.$temp_field_token.'" data-label="'.$temp_field_label.'" data-type="'.(!empty($temp_field_type) ? $temp_field_type['type_token'] : 'none').'" data-type2="'.(!empty($temp_field_type2) ? $temp_field_type2['type_token'] : '').'" title="'.$temp_field_title_plain.'" data-tooltip="'.$temp_field_title_tooltip.'">'.$temp_field_option.'</option>';
    
    // Return the generated option markup
    return $this_option_markup;
    
  }
  
}
?>