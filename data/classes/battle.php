<?
// Define a class for the battles
class mmrpg_battle {

  // Define global class variables
  public $index;
  public $flags;
  public $counters;
  public $values;
  public $events;
  public $actions;
  public $history;

  // Define the constructor class
  public function mmrpg_battle(){
    // Collect any provided arguments
    $args = func_get_args();

    // Collect current battle data from the function if available
    $this_battleinfo = isset($args[0]) ? $args[0] : array('battle_id' => 0, 'battle_token' => 'battle');

    // Now load the battle data from the session or index
    $this->battle_load($this_battleinfo);

    // Return true on success
    return true;

  }

  // Define a public function for updating index info
  public static function update_index_info($battle_token, $battle_info){
    global $DB;

    // If the internal index has not been created yet, load it into memory
    if (!isset($DB->INDEX['BATTLES'])){ mmrpg_battle::load_battle_index(); }

    // Update and/or overwrite the current info in the index
    $DB->INDEX['BATTLES'][$battle_token] = json_encode($battle_info);
    // Update the data in the session as well with provided
    $_SESSION['GAME']['values']['battle_index'][$battle_token] = json_encode($battle_info);
    //ksort($_SESSION['GAME']['values']['battle_index']);

    // Return true on success
    return true;

  }

  // Define a public function for updating index info
  public static function reset_index_info($battle_token){
    global $DB;

    // If the internal index has not been created yet, load it into memory
    if (!isset($DB->INDEX['BATTLES'])){ mmrpg_battle::load_battle_index(false); }

    // Update the data in the session as well with recovered
    $_SESSION['GAME']['values']['battle_index'][$battle_token] = $DB->INDEX['BATTLES'][$battle_token];

    // Return true on success
    return true;

  }

  // Define a public function requesting a battle index entry
  public static function get_index_info($battle_token){
    global $DB;

    // If the internal index has not been created yet, load it into memory
    if (!isset($DB->INDEX['BATTLES'])){ mmrpg_battle::load_battle_index(); }

    // If the requested index is not empty, return the entry
    if (!empty($DB->INDEX['BATTLES'][$battle_token])){
      // Decode the info and return the array
      $battle_info = json_decode($DB->INDEX['BATTLES'][$battle_token], true);
      return $battle_info;
    }
    // Otherwise if the battle index doesn't exist at all
    else {
      // Return false on failure
      return array();
    }

  }

  // Define a function for loading the battle index cache file
  public static function load_battle_index($include_session = true){
    global $DB;
    // Create the index as an empty array
    $DB->INDEX['BATTLES'] = array();
    // Default the battles index to an empty array
    $mmrpg_battles_index = array();
    // If caching is turned OFF, or a cache has not been created
    if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists(MMRPG_CONFIG_BATTLES_CACHE_PATH)){
      // Start indexing the battle data files
      $battles_cache_markup = mmrpg_battle::index_battle_data();
      // Implode the markup into a single string and enclose in PHP tags
      $battles_cache_markup = implode('', $battles_cache_markup);
      $battles_cache_markup = "<?\n".$battles_cache_markup."\n?>";
      // Write the index to a cache file, if caching is enabled
      $battles_cache_file = @fopen(MMRPG_CONFIG_BATTLES_CACHE_PATH, 'w');
      if (!empty($battles_cache_file)){
        @fwrite($battles_cache_file, $battles_cache_markup);
        @fclose($battles_cache_file);
      }
    }
    // Include the cache file so it can be evaluated
    require_once(MMRPG_CONFIG_BATTLES_CACHE_PATH);
    // Return false if we got nothing from the index
    if (empty($mmrpg_battles_index)){ return false; }
    // Loop through the battles and index them after serializing
    foreach ($mmrpg_battles_index AS $token => $array){ $DB->INDEX['BATTLES'][$token] = json_encode($array); }
    // Additionally, include any dynamic session-based battles
    if (!empty($include_session) && !empty($_SESSION['GAME']['values']['battle_index'])){
      // The session-based battles exist, so merge them with the index
      $DB->INDEX['BATTLES'] = array_merge($DB->INDEX['BATTLES'], $_SESSION['GAME']['values']['battle_index']);
    }
    // Return true on success
    return true;
  }

  // Define the function used for scanning the battle directory
  public static function index_battle_data($this_path = ''){

    // Default the battles markup index to an empty array
    $battles_cache_markup = array();

    // Open the type data directory for scanning
    $data_battles  = opendir(MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path);

    // Loop through all the files in the directory
    while (false !== ($filename = readdir($data_battles))) {

      // If this is a directory, initiate a recusive scan
      if (is_dir(MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
        // Collect the markup from the recursive scan
        $append_cache_markup = mmrpg_battle::index_battle_data($this_path.$filename.'/');
        // If markup was found, append if to the main container
        if (!empty($append_cache_markup)){ $battles_cache_markup = array_merge($battles_cache_markup, $append_cache_markup); }
      }
      // Else, ensure the file matches the naming format
      elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
        // Collect the battle token from the filename
        $this_battle_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
        if (!empty($this_path)){ $this_battle_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_battle_token; }

        // Read the file into memory as a string and crop slice out the imporant part
        $this_battle_markup = trim(file_get_contents(MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path.$filename));
        $this_battle_markup = explode("\n", $this_battle_markup);
        $this_battle_markup = array_slice($this_battle_markup, 1, -1);
        // Replace the first line with the appropriate index key
        $this_battle_markup[1] = preg_replace('#\$battle = array\(#i', "\$mmrpg_battles_index['{$this_battle_token}'] = array(\n  'battle_token' => '{$this_battle_token}', 'battle_functions' => 'battles/{$this_path}{$filename}',", $this_battle_markup[1]);
        // Implode the markup into a single string
        $this_battle_markup = implode("\n", $this_battle_markup);
        // Copy this battle's data to the markup cache
        $battles_cache_markup[] = $this_battle_markup;
      }

    }

    // Close the battle data directory
    closedir($data_battles);

    // Return the generated cache markup
    return $battles_cache_markup;

  }

  // Define a public function for manually loading data
  public function battle_load($this_battleinfo){
    // Pull in the mmrpg index
    global $mmrpg_index;

    // Collect current battle data from the session if available
    $this_battleinfo_backup = $this_battleinfo;
    if (isset($_SESSION['BATTLES'][$this_battleinfo['battle_id']])){
      $this_battleinfo = $_SESSION['BATTLES'][$this_battleinfo['battle_id']];
    }
    // Otherwise, collect battle data from the index
    else {
      //die(print_r($this_battleinfo, true));
      $this_battleinfo = mmrpg_battle::get_index_info($this_battleinfo['battle_token']);
    }
    $backup_fields = array('flags', 'values', 'counters');
    $backup_values = array('');
    foreach ($backup_fields AS $field){ $backup_values[$field] = isset($this_battleinfo[$field]) ? $this_battleinfo[$field] : array(); }
    $this_battleinfo = array_replace($this_battleinfo, $this_battleinfo_backup);
    foreach ($backup_fields AS $field){ $this_battleinfo[$field] = isset($this_battleinfo[$field]) ? array_replace($this_battleinfo[$field], $backup_values[$field]) : $backup_values[$field]; }

    // Define the internal ability values using the provided array
    $this->flags = isset($this_battleinfo['flags']) ? $this_battleinfo['flags'] : array();
    $this->counters = isset($this_battleinfo['counters']) ? $this_battleinfo['counters'] : array();
    $this->values = isset($this_battleinfo['values']) ? $this_battleinfo['values'] : array();
    $this->history = isset($this_battleinfo['history']) ? $this_battleinfo['history'] : array();
    $this->events = isset($this_battleinfo['events']) ? $this_battleinfo['events'] : array();
    $this->battle_id = isset($this_battleinfo['battle_id']) ? $this_battleinfo['battle_id'] : 0;
    $this->battle_name = isset($this_battleinfo['battle_name']) ? $this_battleinfo['battle_name'] : 'Default';
    $this->battle_token = isset($this_battleinfo['battle_token']) ? $this_battleinfo['battle_token'] : 'default';
    $this->battle_description = isset($this_battleinfo['battle_description']) ? $this_battleinfo['battle_description'] : '';
    $this->battle_turns = isset($this_battleinfo['battle_turns']) ? $this_battleinfo['battle_turns'] : 1;
    $this->battle_counts = isset($this_battleinfo['battle_counts']) ? $this_battleinfo['battle_counts'] : true;
    $this->battle_status = isset($this_battleinfo['battle_status']) ? $this_battleinfo['battle_status'] : 'active';
    $this->battle_result = isset($this_battleinfo['battle_result']) ? $this_battleinfo['battle_result'] : 'pending';
    $this->battle_robot_limit = isset($this_battleinfo['battle_robot_limit']) ? $this_battleinfo['battle_robot_limit'] : 1;
    $this->battle_field_base = isset($this_battleinfo['battle_field_base']) ? $this_battleinfo['battle_field_base'] : array();
    $this->battle_target_player = isset($this_battleinfo['battle_target_player']) ? $this_battleinfo['battle_target_player'] : array();
    $this->battle_rewards = isset($this_battleinfo['battle_rewards']) ? $this_battleinfo['battle_rewards'] : array();
    $this->battle_points = isset($this_battleinfo['battle_points']) ? $this_battleinfo['battle_points'] : 0;
    $this->battle_zenny = isset($this_battleinfo['battle_zenny']) ? $this_battleinfo['battle_zenny'] : 0;
    $this->battle_level = isset($this_battleinfo['battle_level']) ? $this_battleinfo['battle_level'] : 0;
    $this->battle_overkill = isset($this_battleinfo['battle_overkill']) ? $this_battleinfo['battle_overkill'] : 0;

    // Define the internal robot base values using the robots index array
    $this->battle_base_name = isset($this_battleinfo['battle_base_name']) ? $this_battleinfo['battle_base_name'] : $this->battle_name;
    $this->battle_base_token = isset($this_battleinfo['battle_base_token']) ? $this_battleinfo['battle_base_token'] : $this->battle_token;
    $this->battle_base_description = isset($this_battleinfo['battle_base_description']) ? $this_battleinfo['battle_base_description'] : $this->battle_description;
    $this->battle_base_turns = isset($this_battleinfo['battle_base_turns']) ? $this_battleinfo['battle_base_turns'] : $this->battle_turns;
    $this->battle_base_rewards = isset($this_battleinfo['battle_base_rewards']) ? $this_battleinfo['battle_base_rewards'] : $this->battle_rewards;
    $this->battle_base_points = isset($this_battleinfo['battle_base_points']) ? $this_battleinfo['battle_base_points'] : $this->battle_points;
    $this->battle_base_zenny = isset($this_battleinfo['battle_base_zenny']) ? $this_battleinfo['battle_base_zenny'] : $this->battle_zenny;
    $this->battle_base_level = isset($this_battleinfo['battle_base_level']) ? $this_battleinfo['battle_base_level'] : $this->battle_level;
    $this->battle_base_overkill = isset($this_battleinfo['battle_base_overkill']) ? $this_battleinfo['battle_base_overkill'] : $this->battle_overkill;

    // Update the session variable
    $this->update_session();

    // Return true on success
    return true;

  }

  // Define public print functions for markup generation
  //public function print_battle_name(){ return '<span class="battle_name battle_type battle_type_none">'.$this->battle_name.'</span>'; }
  public function print_battle_name(){ return '<span class="battle_name battle_type">'.$this->battle_name.'</span>'; }
  public function print_battle_token(){ return '<span class="battle_token">'.$this->battle_token.'</span>'; }
  public function print_battle_description(){ return '<span class="battle_description">'.$this->battle_description.'</span>'; }
  public function print_battle_points(){ return '<span class="battle_points">'.$this->battle_points.'</span>'; }

  // Define a static public function for encouraging battle words
  public static function random_positive_word(){
    $temp_text_options = array('Awesome!', 'Nice!', 'Fantastic!', 'Yeah!', 'Yay!', 'Yes!', 'Great!', 'Super!', 'Rock on!', 'Amazing!', 'Fabulous!', 'Wild!', 'Sweet!', 'Wow!');
    $temp_text = $temp_text_options[array_rand($temp_text_options)];
    return $temp_text;
  }

// Define a static public function for encouraging battle victory quotes
  public static function random_victory_quote(){
    $temp_text_options = array('Awesome work!', 'Nice work!', 'Fantastic work!', 'Great work!', 'Super work!', 'Amazing work!', 'Fabulous work!');
    $temp_text = $temp_text_options[array_rand($temp_text_options)];
    return $temp_text;
  }

  // Define a static public function for discouraging battle words
  public static function random_negative_word(){
    $temp_text_options = array('Yikes!', 'Oh no!', 'Ouch&hellip;', 'Awwwww&hellip;', 'Bummer&hellip', 'Boooo&hellip;', 'Harsh!');
    $temp_text = $temp_text_options[array_rand($temp_text_options)];
    return $temp_text;
  }

  // Define a static public function for discouraging battle defeat quotes
  public static function random_defeat_quote(){
    $temp_text_options = array('Maybe try again?', 'Bad luck maybe?', 'Maybe try another stage?', 'Better luck next time?', 'At least you tried&hellip; right?');
    $temp_text = $temp_text_options[array_rand($temp_text_options)];
    return $temp_text;
  }

  // Define a public function for extracting actions from the queue
  public function actions_extract($filters){

    $extracted_actions = array();
    foreach($this->actions AS $action_key => $action_array){
      $is_match = true;
      if (!empty($filters['this_player_id']) && $action_array['this_player']->player_id != $filters['this_player_id']){ $is_match = false; }
      if (!empty($filters['this_robot_id']) && $action_array['this_robot']->robot_id != $filters['this_robot_id']){ $is_match = false; }
      if (!empty($filters['target_player_id']) && $action_array['target_player']->player_id != $filters['target_player_id']){ $is_match = false; }
      if (!empty($filters['target_robot_id']) && $action_array['target_robot']->robot_id != $filters['target_robot_id']){ $is_match = false; }
      if (!empty($filters['this_action']) && $action_array['this_action'] != $filters['this_action']){ $is_match = false; }
      if (!empty($filters['this_action_token']) && $action_array['this_action_token'] != $filters['this_action_token']){ $is_match = false; }
      if ($is_match){ $extracted_actions = array_slice($this->actions, $action_key, 1, false); }
    }
    return $extracted_actions;

  }

  // Define a public function for inserting actions into the queue
  public function actions_insert($inserted_actions){

    if (!empty($inserted_actions)){
      $this->actions = array_merge($this->actions, $inserted_actions);
    }

  }

  // Define a public function for prepending to the action array
  public function actions_prepend(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_action_token){

    // Prepend the new action to the array
    array_unshift($this->actions, array(
      'this_field' => &$this->battle_field,
      'this_player' => &$this_player,
      'this_robot' => &$this_robot,
      'target_player' => &$target_player,
      'target_robot' => &$target_robot,
      'this_action' => $this_action,
      'this_action_token' => $this_action_token
      ));

    // Return the resulting array
    return $this->actions;

  }

  // Define a public function for appending to the action array
  public function actions_append(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_action_token){

    //$this->events_create(false, false, 'DEBUG_'.__LINE__.'_BATTLE', ' $this_battle->actions_append('.$this_player->player_id.'_'.$this_player->player_token.', '.$this_robot->robot_id.'_'.$this_robot->robot_token.', '.$target_player->player_id.'_'.$target_player->player_token.', '.$target_robot->robot_id.'_'.$target_robot->robot_token.', '.$this_action.', '.$this_action_token.');');

    // Append the new action to the array
    $this->actions[] = array(
      'this_field' => &$this->battle_field,
      'this_player' => &$this_player,
      'this_robot' => &$this_robot,
      'target_player' => &$target_player,
      'target_robot' => &$target_robot,
      'this_action' => $this_action,
      'this_action_token' => $this_action_token
      );

    // Return the resulting array
    return $this->actions;

  }

  // Define a public function for emptying the actions array
  public function actions_empty(){

    // Empty the internal actions array
    $this->actions = array();

    // Return the resulting array
    return $this->actions;

  }

  // Define a public function for execution queued items in the actions array
  public function actions_execute(){

    // Back up the IDs of this and the target robot in the global space
    $temp_this_robot_backup = array('robot_id' => $GLOBALS['this_robot']->robot_id, 'robot_token' => $GLOBALS['this_robot']->robot_token);
    $temp_target_robot_backup = array('robot_id' => $GLOBALS['target_robot']->robot_id, 'robot_token' => $GLOBALS['target_robot']->robot_token);

    // Loop through the non-empty action queue and trigger actions
    while (!empty($this->actions) && $this->battle_status != 'complete'){
      // Shift and collect the oldest action from the queue
      $current_action = array_shift($this->actions);

      // Reload each player and robot from session to prevent bugs
      if (!empty($current_action['this_player'])){ $current_action['this_player']->player_load(array('player_id' => $current_action['this_player']->player_id, 'player_token' => $current_action['this_player']->player_token)); }
      if (!empty($current_action['target_player'])){ $current_action['target_player']->player_load(array('player_id' => $current_action['target_player']->player_id, 'player_token' => $current_action['target_player']->player_token)); }
      if (!empty($current_action['this_robot'])){ $current_action['this_robot']->robot_load(array('robot_id' => $current_action['this_robot']->robot_id, 'robot_token' => $current_action['this_robot']->robot_token)); }
      if (!empty($current_action['target_robot'])){ $current_action['target_robot']->robot_load(array('robot_id' => $current_action['target_robot']->robot_id, 'robot_token' => $current_action['target_robot']->robot_token)); }

      // If the robot's player is on autopilot and the action is empty, automate input
      if (empty($current_action['this_action']) && $current_action['this_player']->player_autopilot == true){
        $current_action['this_action'] = 'ability';
      }

      // Based on the action type, trigger the appropriate battle function
      switch ($current_action['this_action']){
        // If the battle start action was called
        case 'start': {
          // Initiate the battle start event for this robot
          $battle_action = $this->actions_trigger(
            $current_action['this_player'],
            $current_action['this_robot'],
            $current_action['target_player'],
            $current_action['target_robot'],
            'start',
            ''
            );
          break;
        }
        // If the robot ability action was called
        case 'ability': {
          // Initiate the ability event for this player's robot
          $battle_action = $this->actions_trigger(
            $current_action['this_player'],
            $current_action['this_robot'],
            $current_action['target_player'],
            $current_action['target_robot'],
            'ability',
            $current_action['this_action_token']
            );
          break;
        }
        // If the robot switch action was called
        case 'switch': {
          // Initiate the switch event for this player's robot
          $battle_action = $this->actions_trigger(
            $current_action['this_player'],
            $current_action['this_robot'],
            $current_action['target_player'],
            $current_action['target_robot'],
            'switch',
            $current_action['this_action_token']
            );
          break;
        }
        // If the robot scan action was called
        case 'scan': {
          // Initiate the scan event for this player's robot
          $battle_action = $this->actions_trigger(
            $current_action['this_player'],
            $current_action['this_robot'],
            $current_action['target_player'],
            $current_action['target_robot'],
            'scan',
            $current_action['this_action_token']
            );
          break;
        }
      }

      // Create a closing event with robots in base frames, if the battle is not over
      if ($this->battle_status != 'complete'){
        if (!empty($battle_action) && $battle_action != 'start'){
          $this->events_create(false, false, '', '');
        }
      }

    }

    // Recreate this and the target robot in the global space with backed up info
    if (empty($GLOBALS['this_robot'])){ $GLOBALS['this_robot'] = new mmrpg_robot($this, $GLOBALS['this_player'], $temp_this_robot_backup); }
    if (empty($GLOBALS['target_robot'])){ $GLOBALS['target_robot'] = new mmrpg_robot($this, $GLOBALS['target_player'], $temp_target_robot_backup); }

    // Return true on loop completion
    return true;
  }

  // Define a public function for triggering battle actions
  public function battle_complete_trigger(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_token = ''){
    global $mmrpg_index, $DB;
    require('battle_battle-complete-trigger.php');
  }

  // Define a public function for triggering battle actions
  public function actions_trigger(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_token = ''){
    global $DB;
    // Default the return variable to false
    $this_return = false;
    // Require the actual code file
    require('battle_actions-trigger.php');
    // Return the result for this battle function
    return $this_return;

  }

  // Define a publicfunction for adding to the event array
  public function events_create($this_robot, $target_robot, $event_header, $event_body, $event_options = array()){

    // Clone or define the event objects
    $this_battle = $this;
    $this->field = $this->battle_field; //array_slice($this->values['fields'];
    $this_player = false;
    $this_robot = !empty($this_robot) ? $this_robot : false;
    if (!empty($this_robot)){ $this_player = new mmrpg_player($this, $this->values['players'][$this_robot->player_id]); }
    $target_player = false;
    $target_robot = !empty($target_robot) ? $target_robot : false;
    if (!empty($target_robot)){ $target_player = new mmrpg_player($this, $this->values['players'][$target_robot->player_id]); }

    // Increment the internal events counter
    if (!isset($this->counters['events'])){ $this->counters['events'] = 1; }
    else { $this->counters['events']++; }

    // Generate the event markup and add it to the array
    $this->events[] = $this->events_markup_generate(array(
      'this_battle' => $this_battle,
      'this_field' => $this->field,
      'this_player' => $this_player,
      'this_robot' => $this_robot,
      'target_player' => $target_player,
      'target_robot' => $target_robot,
      'event_header' => $event_header,
      'event_body' => $event_body,
      'event_options' => $event_options
      ));

    // Return the resulting array
    return $this->events;

  }

  // Define a public function for emptying the events array
  public function events_empty(){

    // Empty the internal events array
    $this->events = array();

    // Return the resulting array
    return $this->events;

  }

  // Define a function for generating console message markup
  public function console_markup($eventinfo, $options){
    // Require the actual markup file
    require('battle_console-markup.php');
    // Return the generated markup and robot data
    return $this_markup;
  }

  // Define a function for generating canvas scene markup
  public function canvas_markup($eventinfo, $options = array()){
    // Require the actual markup file
    require('battle_canvas-markup.php');
    // Return the generated markup and robot data
    return $this_markup;

  }

  // Define a public function for calculating canvas markup offsets
  public function canvas_markup_offset($sprite_key, $sprite_position, $sprite_size){

    // Define the data array to be returned later
    $this_data = array();

    // Define the base canvas offsets for this sprite
    $this_data['canvas_offset_x'] = 165;
    $this_data['canvas_offset_y'] = 55;
    $this_data['canvas_offset_z'] = $sprite_position == 'active' ? 5100 : 4900;
    $this_data['canvas_scale'] = $sprite_position == 'active' ? 1 : 0.5 + (((8 - $sprite_key) / 8) * 0.5);

    // If the robot is on the bench, calculate position offsets based on key
    if ($sprite_position == 'bench'){
      $this_data['canvas_offset_z'] -= 100 * $sprite_key;
      $position_modifier = ($sprite_key + 1) / 8;
      $position_modifier_2 = 1 - $position_modifier;
      $temp_seed_1 = 40; //$sprite_size;
      $temp_seed_2 = 20; //ceil($sprite_size / 2);
      $this_data['canvas_offset_x'] = (-1 * $temp_seed_2) + ceil(($sprite_key + 1) * ($temp_seed_1 + 2)) - ceil(($sprite_key + 1) * $temp_seed_2);
      $temp_seed_1 = $sprite_size;
      $temp_seed_2 = ceil($sprite_size / 2);
      $this_data['canvas_offset_y'] = ($temp_seed_1 + 6) + ceil(($sprite_key + 1) * 14) - ceil(($sprite_key + 1) * 7) - ($sprite_size - 40);
      $temp_seed_3 = 0;
      if ($sprite_key == 0){ $temp_seed_3 = -10; }
      elseif ($sprite_key == 1){ $temp_seed_3 = 0; }
      elseif ($sprite_key == 2){ $temp_seed_3 = 10; }
      elseif ($sprite_key == 3){ $temp_seed_3 = 20; }
      elseif ($sprite_key == 4){ $temp_seed_3 = 30; }
      elseif ($sprite_key == 5){ $temp_seed_3 = 40; }
      elseif ($sprite_key == 6){ $temp_seed_3 = 50; }
      elseif ($sprite_key == 7){ $temp_seed_3 = 60; }
      if ($sprite_size > 40){ $temp_seed_3 -= ceil(40 * $this_data['canvas_scale']); }
      $this_data['canvas_offset_x'] += $temp_seed_3;
      $this_data['canvas_offset_x'] += 20;
    }
    // Otherwise, if the robot is in active position
    elseif ($sprite_position == 'active'){
      if ($sprite_size > 80){
        $this_data['canvas_offset_x'] -= 60;
      }
    }

    // Return the generated canvas data for this robot
    return $this_data;

  }

  // Define a public function for generating event markup
  public function events_markup_generate($eventinfo){
    // Create the frames counter if not exists
    if (!isset($this->counters['event_frames'])){ $this->counters['event_frames'] = 0; }

    // Define defaults for event options
    $options = array();
    $options['event_flag_autoplay'] = isset($eventinfo['event_options']['event_flag_autoplay']) ? $eventinfo['event_options']['event_flag_autoplay'] : true;
    $options['event_flag_victory'] = isset($eventinfo['event_options']['event_flag_victory']) ? $eventinfo['event_options']['event_flag_victory'] : false;
    $options['event_flag_defeat'] = isset($eventinfo['event_options']['event_flag_defeat']) ? $eventinfo['event_options']['event_flag_defeat'] : false;
    $options['console_container_height'] = isset($eventinfo['event_options']['console_container_height']) ? $eventinfo['event_options']['console_container_height'] : 1;
    $options['console_container_classes'] = isset($eventinfo['event_options']['console_container_classes']) ? $eventinfo['event_options']['console_container_classes'] : '';
    $options['console_container_styles'] = isset($eventinfo['event_options']['console_container_styles']) ? $eventinfo['event_options']['console_container_styles'] : '';
    $options['console_header_float'] = isset($eventinfo['event_options']['this_header_float']) ? $eventinfo['event_options']['this_header_float'] : '';
    $options['console_body_float'] = isset($eventinfo['event_options']['this_body_float']) ? $eventinfo['event_options']['this_body_float'] : '';
    $options['console_show_this'] = isset($eventinfo['event_options']['console_show_this']) ? $eventinfo['event_options']['console_show_this'] : true;
    $options['console_show_this_player'] = isset($eventinfo['event_options']['console_show_this_player']) ? $eventinfo['event_options']['console_show_this_player'] : false;
    $options['console_show_this_robot'] = isset($eventinfo['event_options']['console_show_this_robot']) ? $eventinfo['event_options']['console_show_this_robot'] : true;
    $options['console_show_this_ability'] = isset($eventinfo['event_options']['console_show_this_ability']) ? $eventinfo['event_options']['console_show_this_ability'] : false;
    $options['console_show_this_star'] = isset($eventinfo['event_options']['console_show_this_star']) ? $eventinfo['event_options']['console_show_this_star'] : false;
    $options['console_show_target'] = isset($eventinfo['event_options']['console_show_target']) ? $eventinfo['event_options']['console_show_target'] : true;
    $options['console_show_target_player'] = isset($eventinfo['event_options']['console_show_target_player']) ? $eventinfo['event_options']['console_show_target_player'] : true;
    $options['console_show_target_robot'] = isset($eventinfo['event_options']['console_show_target_robot']) ? $eventinfo['event_options']['console_show_target_robot'] : true;
    $options['console_show_target_ability'] = isset($eventinfo['event_options']['console_show_target_ability']) ? $eventinfo['event_options']['console_show_target_ability'] : true;
    $options['canvas_show_this'] = isset($eventinfo['event_options']['canvas_show_this']) ? $eventinfo['event_options']['canvas_show_this'] : true;
    $options['canvas_show_this_robots'] = isset($eventinfo['event_options']['canvas_show_this_robots']) ? $eventinfo['event_options']['canvas_show_this_robots'] : true;
    $options['canvas_show_this_ability'] = isset($eventinfo['event_options']['canvas_show_this_ability']) ? $eventinfo['event_options']['canvas_show_this_ability'] : true;
    $options['canvas_show_this_ability_overlay'] = isset($eventinfo['event_options']['canvas_show_this_ability_overlay']) ? $eventinfo['event_options']['canvas_show_this_ability_overlay'] : false;
    $options['canvas_show_target'] = isset($eventinfo['event_options']['canvas_show_target']) ? $eventinfo['event_options']['canvas_show_target'] : true;
    $options['canvas_show_target_robots'] = isset($eventinfo['event_options']['canvas_show_target_robots']) ? $eventinfo['event_options']['canvas_show_target_robots'] : true;
    $options['canvas_show_target_ability'] = isset($eventinfo['event_options']['canvas_show_target_ability']) ? $eventinfo['event_options']['canvas_show_target_ability'] : true;
    $options['canvas_show_ability_stats'] = isset($eventinfo['event_options']['canvas_show_ability_stats']) ? $eventinfo['event_options']['canvas_show_ability_stats'] : true;
    $options['this_ability'] = isset($eventinfo['event_options']['this_ability']) ? $eventinfo['event_options']['this_ability'] : false;
    $options['this_ability_target'] = isset($eventinfo['event_options']['this_ability_target']) ? $eventinfo['event_options']['this_ability_target'] : false;
    $options['this_ability_target_key'] = isset($eventinfo['event_options']['this_ability_target_key']) ? $eventinfo['event_options']['this_ability_target_key'] : 0;
    $options['this_ability_target_position'] = isset($eventinfo['event_options']['this_ability_target_position']) ? $eventinfo['event_options']['this_ability_target_position'] : 'active';
    $options['this_ability_results'] = isset($eventinfo['event_options']['this_ability_results']) ? $eventinfo['event_options']['this_ability_results'] : false;
    $options['this_star'] = isset($eventinfo['event_options']['this_star']) ? $eventinfo['event_options']['this_star'] : false;
    $options['this_player_image'] = isset($eventinfo['event_options']['this_player_image']) ? $eventinfo['event_options']['this_player_image'] : 'sprite';
    $options['this_robot_image'] = isset($eventinfo['event_options']['this_robot_image']) ? $eventinfo['event_options']['this_robot_image'] : 'sprite';
    $options['this_ability_image'] = isset($eventinfo['event_options']['this_ability_image']) ? $eventinfo['event_options']['this_ability_image'] : 'sprite';

    // Define the variable to collect markup
    $this_markup = array();

    // Generate the event flags markup
    $event_flags = array();
    $event_flags['autoplay'] = $options['event_flag_autoplay'];
    $event_flags['victory'] = $options['event_flag_victory'];
    $event_flags['defeat'] = $options['event_flag_defeat'];
    $this_markup['flags'] = json_encode($event_flags);

    // Generate the console message markup
    $this_markup['console'] = $this->console_markup($eventinfo, $options);

    // Generate the canvas scene markup
    $this_markup['canvas'] = $this->canvas_markup($eventinfo, $options);

    // Generate the jSON encoded event data markup
    $this_markup['data'] = array();
    $this_markup['data']['this_battle'] = ''; //$eventinfo['this_battle']->export_array();
    $this_markup['data']['this_field'] = '';
    $this_markup['data']['this_player'] = ''; //!empty($eventinfo['this_player']) ? $eventinfo['this_player']->export_array() : false;
    $this_markup['data']['this_robot'] = ''; //!empty($eventinfo['this_robot']) ? $eventinfo['this_robot']->export_array() : false;
    $this_markup['data']['target_player'] = ''; //!empty($eventinfo['target_player']) ? $eventinfo['target_player']->export_array() : false;
    $this_markup['data']['target_robot'] = ''; //!empty($eventinfo['target_robot']) ? $eventinfo['target_robot']->export_array() : false;
    $this_markup['data'] = json_encode($this_markup['data']);

    // Increment this battle's frames counter
    $this->counters['event_frames'] += 1;
    $this->update_session();

    // Return the generated event markup
    return $this_markup;

  }

  // Define a public function for collecting event markup
  public function events_markup_collect(){

    // Return the events markup array
    return $this->events;

  }

  // Define a function for calculating the amount of BATTLE POINTS a player gets in battle
  public function calculate_battle_points($this_player, $base_points = 0, $base_turns = 0, $battle_robots = 0){

    // Calculate the number of turn points for this player using the base amounts
    $this_base_points = $base_points;

    $temp_bonus_one = 0;
    if ($this->counters['battle_turn'] < $base_turns){ $temp_bonus_one = round((($base_turns / $this->counters['battle_turn']) - 1) * 100); }
    else { $temp_bonus_one = round((($base_turns / $this->counters['battle_turn']) - 1) * 100); }

    if ($temp_bonus_one != 0){ $this_base_points += ceil($base_points * ($temp_bonus_one / 100)); }

    $temp_bonus_two = 0;
    if ($this_player->counters['robots_masters_total'] < $battle_robots){ $temp_bonus_two = round((($this_player->counters['robots_masters_total'] / $battle_robots) - 1) * 100); }
    else { $temp_bonus_two = round((($battle_robots / $this_player->counters['robots_masters_total']) - 1) * 100); }

    if ($temp_bonus_two != 0){ $this_base_points += ceil($base_points * ($temp_bonus_two / 100)); }

    //$this_battle_points = $this_base_points + $this_turn_points + $this_stat_points;
    $this_battle_points = $this_base_points;

    // Prevent players from loosing points
    if ($this_battle_points == 0){ $this_battle_points = 1; }
    elseif ($this_battle_points < 0){ $this_battle_points = -1 * $this_battle_points; }


    // Return the calculated battle points
    return $this_battle_points;

  }

  // Define a function for returning a weighted random chance
  public function weighted_chance($values, $weights = array(), $debug = ''){

    // Return the value of the static function
    return self::weighted_chance_static($values, $weights, $debug);

  }

  // Define a function for returning a weighted random chance
  public static function weighted_chance_static($values, $weights = array(), $debug = ''){

    // Count the number of values passed
    $value_amount = count($values);

    // If no weights have been defined, auto-generate
    if (empty($weights)){
      $weights = array();
      for ($i = 0; $i < $value_amount; $i++){
        $weights[] = 1;
      }
    }

    // Generate a single weighted values array
    $weighted_values = array();
    foreach ($values AS $key => $option){ $weighted_values[$option] = $weights[$key]; }

    // Collect a random number and check which key it matches
    $random_int = mt_rand(1, array_sum($weighted_values));
    foreach ($weighted_values as $option => $weight) {
      $random_int -= $weight;
      if ($random_int <= 0) {
        return $option;
      }
    }

  }

  // Define a function for returning a random robot frame, allowing inclusions and exclusions
  public static function random_robot_frame($filter = 'exclude', $frames = array()){
    // Define all the frames a robot can have
    $robot_frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
    // Define the filter if not set or invalid
    $filter = !empty($filter) && $filter == 'include' ? 'include' : 'exclude';
    // If this is an exclusion filter, include all but the requested
    if ($filter == 'exclude'){
      // If the frame array is equal to false, auto-populate
      if (empty($frames) && $frames !== false){ $exclude = array('defeat', 'damage'); }
      elseif (empty($frames) && $frames === false) { $exclude = array(); }
      // Loop through index and include any that are relevant
      $frames = array();
      foreach ($robot_frame_index AS $token){ if (!in_array($token, $exclude)){ $frames[] = $token;  } }
      // Return a random element from the array
      return self::weighted_chance_static($frames);
    }
    // Else if this is an inclusion filter, include only the requested
    elseif ($filter == 'include'){
      // If the frame array was empty in any way, populate
      if (empty($frames)){ $include = $robot_frame_index; }
      elseif (!empty($frames)){ $include = $frames; }
      // Return a random element from the array
      return self::weighted_chance_static($frames);
    }

  }

  // Define a function for determining a critical turn
  public function critical_turn($battle_turn, $robot_level, $robot_item = ''){
    // If the robot level and the current turn have the same last digits, it's critical
    $temp_turn_digit = substr($battle_turn, -1, 1);
    $temp_level_digit = substr($robot_level, -1, 1);
    $temp_flag_critical = $temp_level_digit == $temp_turn_digit ? true : false;
    $temp_flag_lucky = false;
    // If the robot is holding a chance module, also look at the first digit of their level
    if ($robot_item == 'item-fortune-module' && $temp_flag_critical == false){
      $temp_level_digit2 = substr($robot_level, 0, 1);
      $temp_flag_critical = $temp_level_digit2 == $temp_turn_digit ? true : false;
      $temp_flag_lucky = true;
    }
    // Return the final critical result
    if (MMRPG_CONFIG_DEBUG_MODE){ $this->events_create(false, false, 'DEBUG_'.__LINE__, ' critical_turn | fortune_module = '.($temp_flag_lucky ? 'true' : 'false').' <br /> turn '.$battle_turn.' vs. level '.$robot_level.' | turn_digit '.$temp_turn_digit.' vs level_digit '.$temp_level_digit.(isset($temp_level_digit2) ? ' | level_digit2 = '.$temp_level_digit2 : '').' | critical = '.($temp_flag_critical ? 'true' : 'false')); }
    return $temp_flag_critical;
  }

  // Define a function for returning a critical chance
  public function critical_chance($chance_percent = 10){

    // Invert if negative for some reason
    if ($chance_percent < 0){ $chance_percent = -1 * $chance_percent; }
    // Round up to a whole number
    $chance_percent = ceil($chance_percent);
    // If zero, automatically return false
    if ($chance_percent == 0){ return false; }
    // Return true of false at random
    $random_int = mt_rand(1, 100);
    return ($random_int <= $chance_percent) ? true : false;

  }

  // Define a function for finding a target player based on field side
  public function find_target_player($target_side){
    // Define the target player variable to start
    $target_player = false;
    // Ensure the player array is not empty
    if (!empty($this->values['players'])){
      // Loop through the battle's player characters one by one
      foreach ($this->values['players'] AS $player_id => $player_info){
        // If the player matches the request side, return the player
        if ($player_info['player_side'] == $target_side){
          $target_player = new mmrpg_player($this, $player_info);
        }
      }
    }
    // Return the final value of the target player
    return $target_player;
  }

  // Define a function for finding a target robot based on field side
  public function find_target_robot($target_side){
    // Define the target robot variable to start
    $target_player = $this->find_target_player($target_side);
    $target_robot = false;
    // Ensure the robot array is not empty
    if (!empty($this->values['robots'])){
      // Loop through the battle's robot characters one by one
      foreach ($this->values['robots'] AS $robot_id => $robot_info){
        // If the robot matches the request side, return the robot
        if ($robot_info['player_id'] == $target_player->player_id && $robot_info['robot_position'] == 'active'){
          $target_robot = new mmrpg_robot($this, $target_player, $robot_info);
        }
      }
    }
    // Return the final value of the target robot
    return $target_robot;
  }


  // -- CHECK ATTACHMENTS FUNCTION -- //

  // Define a function for checking attachment status
  public static function temp_check_robot_attachments(&$this_battle, &$this_player, &$this_robot, &$target_player, &$target_robot){

    // Loop through all the target player's robots and carry out any end-turn events
    $temp_robot = false;
    foreach ($this_player->values['robots_active'] AS $temp_robotinfo){

      // Create the temp robot object
      if (empty($temp_robot)){ $temp_robot = new mmrpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
      else { $temp_robot->robot_load(array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
      //if ($temp_robotinfo['robot_id'] == $this_robot->robot_id){ $temp_robot = &$this_robot; }
      //else { $temp_robot = new mmrpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }

      // Hide any disabled robots that have not been hidden yet
      if ($temp_robotinfo['robot_status'] == 'disabled'){
        // Hide robot and update session
        $temp_robot->flags['apply_disabled_state'] = true;
        $temp_robot->update_session();
        // Create an empty field to remove any leftover frames
        $this_battle->events_create(false, false, '', '');
        // Continue
        continue;
      }

      // If this robot has any attachments, loop through them
      if (!empty($temp_robot->robot_attachments)){
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments');
        foreach ($temp_robot->robot_attachments AS $attachment_token => $attachment_info){
          //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments '.$attachment_token);
          // If this attachment has a duration set
          if (isset($attachment_info['attachment_duration'])){
            //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments '.$attachment_token.' duration '.$attachment_info['attachment_duration']);
            // If the duration is not empty, decrement it and continue
            if ($attachment_info['attachment_duration'] > 0){
              $attachment_info['attachment_duration'] = $attachment_info['attachment_duration'] - 1;
              $temp_robot->robot_attachments[$attachment_token] = $attachment_info;
              $temp_robot->update_session();
              //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments '.$attachment_token.' duration '.$temp_robot->robot_attachments[$attachment_token]['attachment_duration']);
            }
            // Otherwise, trigger the destory action for this attachment
            else {
              // Remove this attachment and inflict damage on the robot
              unset($temp_robot->robot_attachments[$attachment_token]);
              $temp_robot->update_session();
              if ($attachment_info['attachment_destroy'] !== false){
                $temp_attachment = new mmrpg_ability($this_battle, $this_player, $temp_robot, array('ability_token' => $attachment_info['ability_token']));
                $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                if ($temp_trigger_type == 'damage'){
                  $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->update_session();
                  $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                  $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array('apply_modifiers' => false);
                  if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                    $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                    $temp_robot->trigger_damage($temp_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                  }
                } elseif ($temp_trigger_type == 'recovery'){
                  $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->update_session();
                  $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                  $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array('apply_modifiers' => false);
                  if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                    $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                    $temp_robot->trigger_recovery($temp_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                  }
                } elseif ($temp_trigger_type == 'special'){
                  $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                  $temp_attachment->update_session();
                  $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array();
                  $temp_robot->trigger_damage($temp_robot, $temp_attachment, 0, false, $temp_trigger_options);
                }
                // If the temp robot was disabled, trigger the event
                if ($temp_robot->robot_energy < 1){
                  $temp_robot->trigger_disabled($target_robot, $temp_attachment);
                  // If this the player's last robot
                  if ($this_player->counters['robots_active'] < 1){
                    // Trigger the battle complete event
                    $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');
                  }
                }
                // Create an empty field to remove any leftover frames
                $this_battle->events_create(false, false, '', '');
              }
            }
          }

        }
      }

    }

    // Return true on success
    return true;

  }


  // -- CHECK ITEMS FUNCTION -- //

  // Define a function for checking ttem status
  public static function temp_check_robot_items(&$this_battle, &$this_player, &$this_robot, &$target_player, &$target_robot){

    // Loop through all the target player's robots and carry out any end-turn events
    $temp_robot = false;
    foreach ($this_player->values['robots_active'] AS $temp_robotinfo){

      // Create the temp robot object
      if (empty($temp_robot)){ $temp_robot = new mmrpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
      else { $temp_robot->robot_load(array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }

      // Hide any disabled robots that have not been hidden yet
      if ($temp_robotinfo['robot_status'] == 'disabled'){
        // Hide robot and update session
        $temp_robot->flags['apply_disabled_state'] = true;
        //$temp_robot->flags['hidden'] = true;
        $temp_robot->update_session();
        // Create an empty field to remove any leftover frames
        $this_battle->events_create(false, false, '', '');
        // Continue
        continue;
      }

      // If this robot has an item attached, process actions
      if (!empty($temp_robot->robot_item)){
        if (MMRPG_CONFIG_DEBUG_MODE){ $this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has item '.str_replace('item-', '', $temp_robot->robot_item)); }

        // If the robot is holding a Field Booster item, increase multiplier
        if ($temp_robot->robot_item == 'item-field-booster'){

          // Define the item object and trigger info
          if (empty($temp_robot->robot_core)){ $temp_boost_type = 'recovery'; }
          elseif ($temp_robot->robot_core == 'copy'){ $temp_boost_type = 'damage'; }
          elseif ($temp_robot->robot_core == 'empty'){ $temp_boost_type = 'damage'; }
          else { $temp_boost_type = $temp_robot->robot_core; }
          $temp_item_info = array('ability_token' => 'item-field-booster');
          $temp_item_object = new mmrpg_ability($this_battle, $this_player, $temp_robot, $temp_item_info);
          if (!isset($this_battle->field->field_multipliers[$temp_boost_type]) || $this_battle->field->field_multipliers[$temp_boost_type] < MMRPG_SETTINGS_MULTIPLIER_MAX){

            // Define this ability's attachment token
            $this_star_index = mmrpg_prototype_star_image(!empty($temp_boost_type) ? $temp_boost_type : 'none');
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
            $temp_robot->robot_frame = 'taunt';
            $temp_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $temp_robot->update_session();

            // Create or increase the elemental booster for this field
            $temp_change_percent = round($temp_item_object->ability_recovery2 / 100, 1);
            if (!isset($this_battle->field->field_multipliers[$temp_boost_type])){ $this_battle->field->field_multipliers[$temp_boost_type] = 1.0 + $temp_change_percent; }
            else { $this_battle->field->field_multipliers[$temp_boost_type] = $this_battle->field->field_multipliers[$temp_boost_type] + $temp_change_percent; }
            if ($this_battle->field->field_multipliers[$temp_boost_type] >= MMRPG_SETTINGS_MULTIPLIER_MAX){
              $temp_change_percent = $this_battle->field->field_multipliers[$temp_boost_type] - MMRPG_SETTINGS_MULTIPLIER_MAX;
              $this_battle->field->field_multipliers[$temp_boost_type] = MMRPG_SETTINGS_MULTIPLIER_MAX;
            }
            $this_battle->field->update_session();

            // Create the event to show this element boost
            if ($temp_change_percent > 0){
              $this_battle->events_create($temp_robot, false, $this_battle->field->field_name.' Multipliers',
              	mmrpg_battle::random_positive_word().' <span class="ability_name ability_type ability_type_'.$temp_boost_type.'">'.ucfirst($temp_boost_type).' Effects</span> were boosted by '.ceil($temp_change_percent * 100).'%!<br />'.
                'The multiplier is now at <span class="ability_name ability_type ability_type_'.$temp_boost_type.'">'.ucfirst($temp_boost_type).' x '.number_format($this_battle->field->field_multipliers[$temp_boost_type], 1).'</span>!',
                array('canvas_show_this_ability_overlay' => true)
                );
            }

            // Remove this ability attachment from this robot
            unset($temp_robot->robot_attachments[$this_attachment_token]);
            $temp_robot->update_session();

          }

        }
        // Else the robot is holding an Attack Booster item, apply boosts
        elseif ($temp_robot->robot_item == 'item-attack-booster'){

          // Define the item object and trigger info
          $temp_item_info = array('ability_token' => 'item-attack-booster');
          $temp_item_object = new mmrpg_ability($this_battle, $this_player, $temp_robot, $temp_item_info);
          $temp_recovery_amount = ceil($temp_robot->robot_base_attack * ($temp_item_object->ability_recovery2 / 100));
          $temp_item_object->recovery_options_update(array(
            'kind' => 'attack',
            'frame' => 'taunt',
            'percent' => true,
            'modifiers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(9, -10, -10, -10, 'The '.$temp_item_object->print_ability_name().' improved '.$temp_robot->print_robot_name().'&#39;s weapon systems!'),
            'failure' => array(9, -10, -10, -10, '')
            ));

          // Trigger stat recovery for the holding robot
          if (!empty($temp_recovery_amount)){ $temp_robot->trigger_recovery($temp_robot, $temp_item_object, $temp_recovery_amount); }

        }
        // Else if the robot is holding an Defense Booster item, apply boosts
        elseif ($temp_robot->robot_item == 'item-defense-booster'){

          // Define the item object and trigger info
          $temp_item_info = array('ability_token' => 'item-defense-booster');
          $temp_item_object = new mmrpg_ability($this_battle, $this_player, $temp_robot, $temp_item_info);
          $temp_recovery_amount = ceil($temp_robot->robot_base_defense * ($temp_item_object->ability_recovery2 / 100));
          $temp_item_object->recovery_options_update(array(
            'kind' => 'defense',
            'frame' => 'taunt',
            'percent' => true,
            'modifiers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(9, -10, -10, -10, 'The '.$temp_item_object->print_ability_name().' improved '.$temp_robot->print_robot_name().'&#39;s shield systems!'),
            'failure' => array(9, -10, -10, -10, '')
            ));

          // Trigger stat recovery for the holding robot
          if (!empty($temp_recovery_amount)){ $temp_robot->trigger_recovery($temp_robot, $temp_item_object, $temp_recovery_amount); }

        }
        // Else if the robot is holding an Defense Booster item, apply boosts
        elseif ($temp_robot->robot_item == 'item-speed-booster'){

          // Define the item object and trigger info
          $temp_item_info = array('ability_token' => 'item-speed-booster');
          $temp_item_object = new mmrpg_ability($this_battle, $this_player, $temp_robot, $temp_item_info);
          $temp_recovery_amount = ceil($temp_robot->robot_base_speed * ($temp_item_object->ability_recovery2 / 100));
          $temp_item_object->recovery_options_update(array(
            'kind' => 'speed',
            'frame' => 'taunt',
            'percent' => true,
            'modifiers' => false,
            'kickback' => array(0, 0, 0),
            'success' => array(9, -10, -10, -10, 'The '.$temp_item_object->print_ability_name().' improved '.$temp_robot->print_robot_name().'&#39;s mobility systems!'),
            'failure' => array(9, -10, -10, -10, '')
            ));

          // Trigger stat recovery for the holding robot
          if (!empty($temp_recovery_amount)){ $temp_robot->trigger_recovery($temp_robot, $temp_item_object, $temp_recovery_amount); }

        }

      }

      // Update just to be sure
      $temp_robot->update_session();

    }

    // Return true on success
    return true;

  }

  // -- CHECK WEAPONS FUNCTION -- //

  // Define a function for checking weapons status
  public static function temp_check_robot_weapons(&$this_battle, &$this_player, &$this_robot, &$target_player, &$target_robot, $regen_weapons = true){

    // Loop through all the target player's robots and carry out any end-turn events
    $temp_robot = false;
    foreach ($this_player->values['robots_active'] AS $temp_robotinfo){

      // Create the temp robot object
      if (empty($temp_robot)){ $temp_robot = new mmrpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
      else { $temp_robot->robot_load(array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }

      // Ensure this robot has not been disabled already
      if ($temp_robotinfo['robot_status'] == 'disabled'){
        // Hide robot and update session
        $temp_robot->flags['apply_disabled_state'] = true;
        //$temp_robot->flags['hidden'] = true;
        $temp_robot->update_session();
        // Create an empty field to remove any leftover frames
        $this_battle->events_create(false, false, '', '');
        // Continue
        continue;
      }

      // If this robot is not at full weapon energy, increase it by one
      if ($temp_robot->robot_weapons < $temp_robot->robot_base_weapons){
        // Ensure the regen weapons flag has been set to true
        if ($regen_weapons){
          // Define the multiplier based on position
          $temp_multiplier = $temp_robot->robot_position == 'bench' ? 2 : 1;
          // Increment this robot's weapons by one point and update
          $temp_robot->robot_weapons += MMRPG_SETTINGS_RECHARGE_WEAPONS * $temp_multiplier;
          // If this robot is over its base, zero it out
          if ($temp_robot->robot_weapons > $temp_robot->robot_base_weapons){ $temp_robot->robot_weapons = $temp_robot->robot_base_weapons; }
        }
        // Update just to be sure
        $temp_robot->update_session();
      }

    }

    // Return true on success
    return true;

  }


  // Define a function for generating star console variables
  public function star_console_markup($options, $player_data, $robot_data){

    // Define the variable to hold the console star data
    $this_data = array();

    // Collect the star image info from the index based on type
    $temp_star_kind = $options['star_kind'];
    $temp_field_type_1 = !empty($options['star_type']) ? $options['star_type'] : 'none';
    $temp_field_type_2 = !empty($options['star_type2']) ? $options['star_type2'] : $temp_field_type_1;
    $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_2);
    $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);

    // Define and calculate the simpler markup and positioning variables for this star
    $this_data['star_name'] = isset($options['star_name']) ? $options['star_name'] : 'Battle Star';
    $this_data['star_title'] = $this_data['star_name'];
    $this_data['star_token'] = $options['star_token'];
    $this_data['container_class'] = 'this_sprite sprite_left';
    $this_data['container_style'] = '';

    // Define the back star's markup
    $this_data['star_image'] = 'images/abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
    $this_data['star_markup_class'] = 'sprite sprite_star sprite_star_sprite sprite_40x40 sprite_40x40_'.str_pad($temp_star_back_info['frame'], 2, '0', STR_PAD_LEFT).' ';
    $this_data['star_markup_style'] = 'background-image: url('.$this_data['star_image'].'); margin-top: 5px; ';
    $temp_back_markup = '<div class="'.$this_data['star_markup_class'].'" style="'.$this_data['star_markup_style'].'" title="'.$this_data['star_title'].'">'.$this_data['star_title'].'</div>';

    // Define the back star's markup
    $this_data['star_image'] = 'images/abilities/item-star-base-'.$temp_star_front_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
    $this_data['star_markup_class'] = 'sprite sprite_star sprite_star_sprite sprite_40x40 sprite_40x40_'.str_pad($temp_star_front_info['frame'], 2, '0', STR_PAD_LEFT).' ';
    $this_data['star_markup_style'] = 'background-image: url('.$this_data['star_image'].'); margin-top: -42px; ';
    $temp_front_markup = '<div class="'.$this_data['star_markup_class'].'" style="'.$this_data['star_markup_style'].'" title="'.$this_data['star_title'].'">'.$this_data['star_title'].'</div>';

    // Generate the final markup for the console star
    $this_data['star_markup'] = '';
    $this_data['star_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
    $this_data['star_markup'] .= $temp_back_markup;
    $this_data['star_markup'] .= $temp_front_markup;
    $this_data['star_markup'] .= '</div>';

    // Return the star console data
    return $this_data;

  }

  // Define a public function for recalculating internal counters
  public function update_variables(){

    // Calculate this battle's count variables
    //$this->counters['thing'] = count($this->robot_stuff);

    // Return true on success
    return true;

  }

  // Define a public function for updating this player's session
  public function update_session(){

    // Update any internal counters
    $this->update_variables();

    // Update the session with the export array
    $this_data = $this->export_array();
    $_SESSION['BATTLES'][$this->battle_id] = $this_data;

    // Return true on success
    return true;

  }

  // Define a function for exporting the current data
  public function export_array(){

    // Return all internal ability fields in array format
    return array(
      'battle_id' => $this->battle_id,
      'battle_name' => $this->battle_name,
      'battle_token' => $this->battle_token,
      'battle_description' => $this->battle_description,
      'battle_turns' => $this->battle_turns,
      'battle_rewards' => $this->battle_rewards,
      'battle_points' => $this->battle_points,
      'battle_zenny' => $this->battle_zenny,
      'battle_level' => $this->battle_level,
      'battle_overkill' => $this->battle_overkill,
      'battle_base_name' => $this->battle_base_name,
      'battle_base_token' => $this->battle_base_token,
      'battle_base_description' => $this->battle_base_description,
      'battle_base_turns' => $this->battle_base_turns,
      'battle_base_rewards' => $this->battle_base_rewards,
      'battle_base_points' => $this->battle_base_points,
      'battle_base_zenny' => $this->battle_base_zenny,
      'battle_base_level' => $this->battle_base_level,
      'battle_base_overkill' => $this->battle_base_overkill,
      'battle_counts' => $this->battle_counts,
      'battle_status' => $this->battle_status,
      'battle_result' => $this->battle_result,
      'battle_robot_limit' => $this->battle_robot_limit,
      'battle_field_base' => $this->battle_field_base,
      'battle_target_player' => $this->battle_target_player,
      'flags' => $this->flags,
      'counters' => $this->counters,
      'values' => $this->values,
      'history' => $this->history
      );

  }

}
?>