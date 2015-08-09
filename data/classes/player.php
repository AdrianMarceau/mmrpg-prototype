<?
// Define a class for the players
class mmrpg_player {

  // Define global class variables
  public $flags;
  public $counters;
  public $values;
  public $history;

  // Define the constructor class
  public function mmrpg_player(){

    // Collect any provided arguments
    $args = func_get_args();

    // Define the internal battle pointer
    $this->battle = isset($args[0]) ? $args[0] : array();
    $this->battle_id = $this->battle->battle_id;
    $this->battle_token = $this->battle->battle_token;

    // Collect current player data from the function if available
    $this_playerinfo = isset($args[1]) ? $args[1] : array('player_id' => 0, 'player_token' => 'player');

    // Now load the player data from the session or index
    $this->player_load($this_playerinfo);

    // Return true on success
    return true;

  }

  // Define a public function for manually loading data
  public function player_load($this_playerinfo){
    // Pull in the global index
    global $mmrpg_index;

    // Collect current player data from the session if available
    $this_playerinfo_backup = $this_playerinfo;
    if (isset($_SESSION['PLAYERS'][$this_playerinfo['player_id']])){
      $this_playerinfo = $_SESSION['PLAYERS'][$this_playerinfo['player_id']];
    }
    // Otherwise, collect player data from the index
    else {
      // Copy over the base contents from the players index
      $this_playerinfo = $mmrpg_index['players'][$this_playerinfo['player_token']];
    }
    $this_playerinfo = array_replace($this_playerinfo, $this_playerinfo_backup);

    // Define the internal player values using the collected array
    $this->flags = isset($this_playerinfo['flags']) ? $this_playerinfo['flags'] : array();
    $this->counters = isset($this_playerinfo['counters']) ? $this_playerinfo['counters'] : array();
    $this->values = isset($this_playerinfo['values']) ? $this_playerinfo['values'] : array();
    $this->history = isset($this_playerinfo['history']) ? $this_playerinfo['history'] : array();
    $this->player_id = isset($this_playerinfo['player_id']) ? $this_playerinfo['player_id'] : 0;
    $this->player_name = isset($this_playerinfo['player_name']) ? $this_playerinfo['player_name'] : 'Robot';
    $this->player_token = isset($this_playerinfo['player_token']) ? $this_playerinfo['player_token'] : 'player';
    $this->player_image = isset($this_playerinfo['player_image']) ? $this_playerinfo['player_image'] : $this->player_token;
    $this->player_image_size = isset($this_playerinfo['player_image_size']) ? $this_playerinfo['player_image_size'] : 40;
    $this->player_description = isset($this_playerinfo['player_description']) ? $this_playerinfo['player_description'] : '';
    $this->player_energy = isset($this_playerinfo['player_energy']) ? $this_playerinfo['player_energy'] : 0;
    $this->player_attack = isset($this_playerinfo['player_attack']) ? $this_playerinfo['player_attack'] : 0;
    $this->player_defense = isset($this_playerinfo['player_defense']) ? $this_playerinfo['player_defense'] : 0;
    $this->player_speed = isset($this_playerinfo['player_speed']) ? $this_playerinfo['player_speed'] : 0;
    $this->player_base_energy = isset($this_playerinfo['player_base_energy']) ? $this_playerinfo['player_base_energy'] : $this->player_energy;
    $this->player_base_attack = isset($this_playerinfo['player_base_attack']) ? $this_playerinfo['player_base_attack'] : $this->player_attack;
    $this->player_base_defense = isset($this_playerinfo['player_base_defense']) ? $this_playerinfo['player_base_defense'] : $this->player_defense;
    $this->player_base_speed = isset($this_playerinfo['player_base_speed']) ? $this_playerinfo['player_base_speed'] : $this->player_speed;
    $this->player_robots = isset($this_playerinfo['player_robots']) ? $this_playerinfo['player_robots'] : array();
    $this->player_abilities = isset($this_playerinfo['player_abilities']) ? $this_playerinfo['player_abilities'] : array();
    $this->player_items = isset($this_playerinfo['player_items']) ? $this_playerinfo['player_items'] : array();
    $this->player_side = isset($this_playerinfo['player_side']) ? $this_playerinfo['player_side'] : 'left';
    $this->player_controller = isset($this_playerinfo['player_controller']) ? $this_playerinfo['player_controller'] : ($this->player_side == 'left' ? 'human' : 'computer');
    $this->player_autopilot = isset($this_playerinfo['player_autopilot']) ? $this_playerinfo['player_autopilot'] : false;
    $this->player_quotes = isset($this_playerinfo['player_quotes']) ? $this_playerinfo['player_quotes'] : array();
    $this->player_rewards = isset($this_playerinfo['player_rewards']) ? $this_playerinfo['player_rewards'] : array();
    $this->player_starforce = isset($this_playerinfo['player_starforce']) ? $this_playerinfo['player_starforce'] : array();
    $this->player_frame = isset($this_playerinfo['player_frame']) ? $this_playerinfo['player_frame'] : 'base';
    $this->player_frame_index = isset($this_playerinfo['player_frame_index']) ? $this_playerinfo['player_frame_index'] : array('base','taunt','victory','defeat','command','damage');
    $this->player_frame_offset = isset($this_playerinfo['player_frame_offset']) ? $this_playerinfo['player_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
    $this->player_points = isset($this_playerinfo['player_points']) ? $this_playerinfo['player_points'] : 0;
    $this->player_switch = isset($this_playerinfo['player_switch']) ? $this_playerinfo['player_switch'] : 1;
    $this->player_next_action = isset($this_playerinfo['player_next_action']) ? $this_playerinfo['player_next_action'] : 'auto';
//    if (empty($this->player_id)){
//      $this->player_id = md5(substr(md5($this->player_side), 0, 10));
//    }

    // Define the internal player base values using the players index array
    $this->player_base_name = isset($this_playerinfo['player_base_name']) ? $this_playerinfo['player_base_name'] : $this->player_name;
    $this->player_base_token = isset($this_playerinfo['player_base_token']) ? $this_playerinfo['player_base_token'] : $this->player_token;
    $this->player_base_image = isset($this_playerinfo['player_base_image']) ? $this_playerinfo['player_base_image'] : $this->player_image;
    $this->player_base_image_size = isset($this_playerinfo['player_base_image_size']) ? $this_playerinfo['player_base_image_size'] : $this->player_image_size;
    $this->player_base_description = isset($this_playerinfo['player_base_description']) ? $this_playerinfo['player_base_description'] : $this->player_description;
    $this->player_base_robots = isset($this_playerinfo['player_base_robots']) ? $this_playerinfo['player_base_robots'] : $this->player_robots;
    $this->player_base_abilities = isset($this_playerinfo['player_base_abilities']) ? $this_playerinfo['player_base_abilities'] : $this->player_abilities;
    $this->player_base_items = isset($this_playerinfo['player_base_items']) ? $this_playerinfo['player_base_items'] : $this->player_items;
    $this->player_base_quotes = isset($this_playerinfo['player_base_quotes']) ? $this_playerinfo['player_base_quotes'] : $this->player_quotes;
    $this->player_base_rewards = isset($this_playerinfo['player_base_rewards']) ? $this_playerinfo['player_base_rewards'] : $this->player_rewards;
    $this->player_base_starforce = isset($this_playerinfo['player_base_starforce']) ? $this_playerinfo['player_base_starforce'] : $this->player_starforce;
    $this->player_base_points = isset($this_playerinfo['player_base_points']) ? $this_playerinfo['player_base_points'] : $this->player_points;
    $this->player_base_switch = isset($this_playerinfo['player_base_switch']) ? $this_playerinfo['player_base_switch'] : $this->player_switch;

    // Remove any abilities that do not exist in the index
    if (!empty($this->player_abilities)){
      foreach ($this->player_abilities AS $key => $token){
        if (empty($token)){ unset($this->player_abilities[$key]); }
      }
      $this->player_abilities = array_values($this->player_abilities);
    }

    /*
    // Remove any items that do not exist in the index
    if (!empty($this->player_items)){
      foreach ($this->player_items AS $key => $token){
        if (empty($token)){ unset($this->player_items[$key]); }
      }
      $this->player_items = array_values($this->player_items);
    }
    */

    // Pull in session starforce if available for human players
    if (empty($this->player_starforce) && $this->player_side == 'left'){
      if (!empty($_SESSION['GAME']['values']['star_force'])){
        $this->player_starforce = $_SESSION['GAME']['values']['star_force'];
      }
    }

    // Update the session variable
    $this->update_session();

    // Return true on success
    return true;

  }

  // Define a function for adding a new robot to this player's object data
  public function load_robot($this_robotinfo, $this_key, $apply_bonuses = false){
    //$GLOBALS['DEBUG']['checkpoint_line'] = 'class.player.php : line 107 <pre>'.print_r($this->player_robots, true).'</pre>';
    ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
    $this_robot = new mmrpg_robot($this->battle, $this, $this_robotinfo);
    ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
    if ($apply_bonuses){ $this_robot->apply_stat_bonuses(); }
    ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
    $this_export_array = $this_robot->export_array();
    ////if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "CHECKPOINT on line ".__LINE__." in ".__FILE__;  }
    $this->player_robots[$this_key] = $this_export_array;
    unset($this_robot);
    return true;
  }

  // Define public print functions for markup generation
  public function print_player_name(){ return '<span class="player_name player_type">'.$this->player_name.'</span>'; }
  public function print_player_token(){ return '<span class="player_token">'.$this->player_token.'</span>'; }
  public function print_player_description(){ return '<span class="player_description">'.$this->player_description.'</span>'; }
  public function print_player_quote($quote_type, $this_find = array(), $this_replace = array()){
    global $mmrpg_index;
    // Define the quote text variable
    $quote_text = '';
    // If the player is visible and has the requested quote text
    if ($this->player_token != 'player' && isset($this->player_quotes[$quote_type])){
      // Collect the quote text with any search/replace modifications
      $this_quote_text = str_replace($this_find, $this_replace, $this->player_quotes[$quote_type]);
      // Collect the text colour for this player
      $this_type_token = str_replace('dr-', '', $this->player_token);
      $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
      foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += 20; }
      // Generate the quote text markup with the appropriate RGB values
      $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
    }
    return $quote_text;
  }

  // Define a function for checking if this player has a specific ability
  public function has_ability($ability_token){
    if (empty($this->player_abilities) || empty($ability_token)){ return false; }
    elseif (in_array($ability_token, $this->player_abilities)){ return true; }
    else { return false; }
  }

  // Define a function for checking if this player has a specific item
  public function has_item($item_token){
    if (empty($this->player_items) || empty($item_token)){ return false; }
    elseif (in_array($item_token, $this->player_items)){ return true; }
    else { return false; }
  }

  // Define a function for generating player canvas variables
  public function canvas_markup($options){

    // Define the variable to hold the console player data
    $this_data = array();
    $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

    // Only proceed if this is a real player
    if ($this->player_token != 'player'){

      // Define and calculate the simpler markup and positioning variables for this player
      $this_data['data_type'] = 'player';
      $this_data['player_id'] = $this->player_id;
      $this_data['player_frame'] = $this->player_frame !== false ? $this->player_frame : 'base'; // IMPORTANT
      //$this_data['player_frame'] = str_pad(array_search($this_data['player_frame'], $this->player_frame_index), 2, '0', STR_PAD_LEFT);
      $this_data['player_frame_index'] = !empty($this->player_frame_index) ? $this->player_frame_index : array('base');
      $this_data['player_title'] = $this->player_name;
      $this_data['player_token'] = $this->player_token;
      $this_data['player_float'] = $this->player_side;
      $this_data['player_direction'] = $this->player_side == 'left' ? 'right' : 'left';
      $this_data['player_position'] = 'active';
      $this_data['player_size'] = 80;
      $this_data['image_type'] = !empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite';
      /*
      $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/sprite_'.$this_data['player_direction'].'_'.$this_data['player_size'].'x'.$this_data['player_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
      $this_data['player_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_80x80 sprite_80x80_'.$this_data['player_frame'];
      $this_data['player_styles'] = '';
      */
      $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/sprite_'.$this_data['player_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
      $this_data['player_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_75x75 sprite_75x75_'.$this_data['player_frame'];


      $this_data['player_scale'] = 0.5 + ((7 / 8) * 0.5);
      $this_data['player_sprite_size'] = ceil($this_data['player_scale'] * 80);
      $this_data['player_sprite_width'] = ceil($this_data['player_scale'] * 80);
      $this_data['player_sprite_height'] = ceil($this_data['player_scale'] * 80);
      $this_data['player_image_width'] = ceil($this_data['player_scale'] * 800);
      $this_data['player_image_height'] = ceil($this_data['player_scale'] * 80);
      $this_data['canvas_offset_z'] = 4900;
      $this_data['canvas_offset_x'] = 200;
      $this_data['canvas_offset_y'] = 60;

      $frame_position = array_search($this_data['player_frame'], $this_data['player_frame_index']);
      if ($frame_position === false){ $frame_position = 0; }
      $frame_background_offset = -1 * ceil(($this_data['player_sprite_size'] * $frame_position));
      $this_data['player_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
      $this_data['player_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['player_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
      $this_data['player_style'] .= 'background-image: url('.$this_data['player_image'].'); width: '.$this_data['player_sprite_size'].'px; height: '.$this_data['player_sprite_size'].'px; background-size: '.$this_data['player_image_width'].'px '.$this_data['player_image_height'].'px; ';

      // Generate the final markup for the canvas player
      ob_start();

        // Display this player's sprite in the active position
        global $flag_wap, $flag_ipad, $flag_iphone;
        if (!$flag_wap && !$flag_ipad && !$flag_iphone){
          $shadow_offset_z = $this_data['canvas_offset_z'] - 1;
          $shadow_scale = array(1.5, 0.25);
          $shadow_skew = $this_data['player_direction'] == 'right' ? 30 : -30;
          $shadow_translate = array(
            ($this_data['player_direction'] == 'right' ? -1 : 1) * ($this_data['player_sprite_width'] + ceil($this_data['player_sprite_width'] * $shadow_scale[1])) + ceil($shadow_skew * $shadow_scale[1]),
            $this_data['player_position'] == 'active' ? 115 : ceil($this_data['player_sprite_height'] * $shadow_scale[0]),
            );
          $shadow_styles = 'z-index: '.$shadow_offset_z.'; transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);  -webkit-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);  -moz-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);';
          echo '<div data-shadowid="'.$this_data['player_id'].'" class="'.str_replace($this_data['player_token'], 'player', $this_data['player_class']).'" style="'.str_replace('players/', 'players_shadows/', $this_data['player_style']).$shadow_styles.'" data-type="'.$this_data['data_type'].'_shadow" data-size="'.$this_data['player_sprite_size'].'" data-direction="'.$this_data['player_direction'].'" data-frame="'.$this_data['player_frame'].'">'.$this_data['player_token'].'_shadow</div>';
        }
        echo '<div data-playerid="'.$this_data['player_id'].'" class="'.$this_data['player_class'].'" style="'.$this_data['player_style'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['player_sprite_size'].'" data-direction="'.$this_data['player_direction'].'" data-frame="'.$this_data['player_frame'].'" data-position="'.$this_data['player_position'].'">'.$this_data['player_title'].'</div>';

      // Collect the generated player markup
      $this_data['player_markup'] = trim(ob_get_clean());

    } else {

      // Define empty player markup
      $this_data['player_markup'] = '';

    }

    // Return the player canvas data
    return $this_data;

  }

  // Define a function for generating player console variables
  public function console_markup($options){

    // Define the variable to hold the console robot data
    $this_data = array();

    // Define and calculate the simpler markup and positioning variables for this player
    $this_data['player_frame'] = !empty($this->player_frame) ? $this->player_frame : 'base';
    $this_data['player_frame'] = str_pad(array_search($this_data['player_frame'], $this->player_frame_index), 2, '0', STR_PAD_LEFT);
    $this_data['player_title'] = $this->player_name;
    $this_data['player_token'] = $this->player_token;
    $this_data['player_float'] = $this->player_side;
    $this_data['player_direction'] = $this->player_side == 'left' ? 'right' : 'left';
    $this_data['player_position'] = 'active';

    // Define the rest of the display variables
    $this_data['container_class'] = 'this_sprite sprite_'.$this_data['player_float'];
    $this_data['container_style'] = '';
    $this_data['player_class'] = 'sprite ';
    $this_data['player_style'] = '';
    $this_data['player_size'] = $this->player_image_size;
    $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/'.(!empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite').'_'.$this_data['player_direction'].'_'.$this_data['player_size'].'x'.$this_data['player_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
    $this_data['player_class'] .= 'sprite_'.$this_data['player_size'].'x'.$this_data['player_size'].' sprite_'.$this_data['player_size'].'x'.$this_data['player_size'].'_'.$this_data['player_frame'].' ';
    $this_data['player_class'] .= 'player_position_'.$this_data['player_position'].' ';
    $this_data['player_style'] .= 'background-image: url('.$this_data['player_image'].'); ';

    // Generate the final markup for the console player
    $this_data['player_markup'] = '';
    // If this was an undefined player, do not create markup
    if ($this->player_token != 'player'){
      $this_data['player_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
      $this_data['player_markup'] .= '<div class="'.$this_data['player_class'].'" style="'.$this_data['player_style'].'" title="'.$this_data['player_title'].'" data-tooltip-align="'.$this_data['player_float'].'">'.$this_data['player_title'].'</div>';
      $this_data['player_markup'] .= '</div>';
    }

    // Return the player console data
    return $this_data;

  }

  // Define a public function for sorting robots by their active status
  public static function robot_sort_by_active($info1, $info2){
    //$info1['robot_key'] = $info1['robot_key'] < 8 ? $info1['robot_key'] + 1 : 1;
    //$info2['robot_key'] = $info2['robot_key'] < 8 ? $info2['robot_key'] + 1 : 1;
    if ($info1['robot_position'] == 'active'){ return -1; }
    elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
    elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
    else { return 0; }
  }


  // Define a static function for printing out the robot's editor markup
  public static function abilities_sort_for_editor($ability_one, $ability_two){
    $ability_token_one = $ability_one['ability_token'];
    $ability_token_two = $ability_two['ability_token'];
    if ($ability_token_one > $ability_token_two){ return 1; }
    elseif ($ability_token_one < $ability_token_two){ return -1; }
    else { return 0; }
  }


  // Define a static function for printing out the robot's editor markup
  public static function fields_sort_for_editor($field_one, $field_two){
    static $mmrpg_fields_index;
    if (empty($mmrpg_fields_index)){ $mmrpg_fields_index = mmrpg_field::get_index(); }
    $field_token_one = $field_one['field_token'];
    $field_token_two = $field_two['field_token'];
    if (!isset($mmrpg_fields_index[$field_token_one])){ return 0; }
    if (!isset($mmrpg_fields_index[$field_token_two])){ return 0; }
    $field_one = $mmrpg_fields_index[$field_token_one];
    $field_two = $mmrpg_fields_index[$field_token_two];
    //die('<pre>'.print_r($field_one, true).'</pre>');
    if ($field_one['field_game'] > $field_two['field_game']){ return 1; }
    elseif ($field_one['field_game'] < $field_two['field_game']){ return -1; }
    if ($field_one['field_token'] > $field_two['field_token']){ return 1; }
    elseif ($field_one['field_token'] < $field_two['field_token']){ return -1; }
    else { return 0; }
  }

  // Define a static function for printing out the robot's editor markup
  public static function items_sort_for_editor($item_one, $item_two){
    global $mmrpg_index;
    $item_token_one = preg_match('/^item-([a-z0-9]+)-(a-z0-9+)$/i', $item_one['ability_token']) ? $item_one['ability_token'] : $item_one['ability_token'].'-size';
    $item_token_two = preg_match('/^item-([a-z0-9]+)-(a-z0-9+)$/i', $item_two['ability_token']) ? $item_two['ability_token'] : $item_two['ability_token'].'-size';
    list($x, $kind_one, $size_one) = explode('-', $item_token_one);
    list($x, $kind_two, $size_two) = explode('-', $item_token_two);

    if ($kind_one == 'energy' && $kind_two != 'energy'){ return -1; }
    elseif ($kind_one != 'energy' && $kind_two == 'energy'){ return 1; }
    elseif ($kind_one == 'weapon' && $kind_two != 'weapon'){ return -1; }
    elseif ($kind_one != 'weapon' && $kind_two == 'weapon'){ return 1; }
    elseif ($kind_one == 'core' && $kind_two != 'core'){ return -1; }
    elseif ($kind_one != 'core' && $kind_two == 'core'){ return 1; }

    elseif ($size_one == 'pellet' && $size_two != 'pellet'){ return -1; }
    elseif ($size_one != 'pellet' && $size_two == 'pellet'){ return 1; }
    elseif ($size_one == 'capsule' && $size_two != 'capsule'){ return -1; }
    elseif ($size_one != 'capsule' && $size_two == 'capsule'){ return 1; }
    elseif ($size_one == 'tank' && $size_two != 'tank'){ return -1; }
    elseif ($size_one != 'tank' && $size_two == 'tank'){ return 1; }

    elseif ($item_one['ability_token'] > $item_two['ability_token']){ return 1; }
    elseif ($item_one['ability_token'] < $item_two['ability_token']){ return -1; }
    else { return 0; }
  }

  // Define a public function updating internal varibales
  public function update_variables(){

    // Update parent objects first
    //$this->battle->update_variables();

    // Calculate this player's count variables
    $this->counters['abilities_total'] = count($this->player_abilities);
    $this->counters['items_total'] = count($this->player_items);
    $this->counters['dark_elements'] = 0;

    // Create the current robot value for calculations
    $this->values['current_robot'] = false;
    $this->values['current_robot_enter'] = isset($this->values['current_robot_enter']) ? $this->values['current_robot_enter'] : false;

    // Create the flag variables if they don't exist

    // Create the counter variables and defeault to zero
    $this->counters['robots_masters_total'] = 0;
    $this->counters['robots_mechas_total'] = 0;
    $this->counters['robots_bosses_total'] = 0;
    $this->counters['robots_total'] = 0;
    $this->counters['robots_active'] = 0;
    $this->counters['robots_disabled'] = 0;
    $this->counters['robots_positions'] = array(
      'active' => 0,
      'bench' => 0
      );

    // Create the value variables and default to empty
    $this->values['robots_total'] = array();
    $this->values['robots_active'] = array();
    $this->values['robots_disabled'] = array();
    $this->values['robots_positions'] = array(
      'active' => array(),
      'bench' => array()
      );

    // Ensure this player has robots to loop over
    if (!empty($this->player_robots)){

      // Define the dark element tokens
      $dark_element_tokens = array('dark-frag', 'dark-spire', 'dark-tower');

      // Loop through each of the player's robots and check status
      foreach ($this->player_robots AS $this_key => $this_robotinfo){
        // Ensure a token an idea are provided at least
        if (empty($this_robotinfo['robot_id']) || empty($this_robotinfo['robot_token'])){ continue; }
        // Define the current temp robot object using the loaded robot data
        $temp_robot = new mmrpg_robot($this->battle, $this, $this_robotinfo);
        $temp_robot->robot_key = $this_key;
        $temp_robot->update_session();
        // Increment the robot class counter for this player
        if ($temp_robot->robot_class == 'master'){ $this->counters['robots_masters_total']++; }
        elseif ($temp_robot->robot_class == 'mecha'){ $this->counters['robots_mechas_total']++; }
        elseif ($temp_robot->robot_class == 'boss'){ $this->counters['robots_bosses_total']++; }
        // Check if this robot is in the active position
        if ($temp_robot->robot_position == 'active'){
          $this->values['current_robot'] = $temp_robot->robot_string;
        }
        // Check if this robot is in active status
        if ($temp_robot->robot_status == 'active'){
          // Increment the active robot counter
          $this->counters['robots_active']++;
          // Add this robot to the active robots array
          $this->values['robots_active'][] = &$this->player_robots[$this_key]; //$this_info;
          // If this robot is a dark element that absorbs the elements
          if (in_array($temp_robot->robot_token, $dark_element_tokens)){
            $this->counters['dark_elements']++;
          }
          // Check if this robot is in the active position
          if ($temp_robot->robot_position == 'active'){
            // Increment the active robot counter
            $this->counters['robots_positions']['active']++;
            // Add this robot to the active robots array
            $this->values['robots_positions']['active'][] = &$this->player_robots[$this_key]; //$this_info;
          }
          // Otherwise, if this robot is in benched position
          elseif ($temp_robot->robot_position == 'bench'){
            // Increment the bench robot counter
            $this->counters['robots_positions']['bench']++;
            // Add this robot to the bench robots array
            $this->values['robots_positions']['bench'][] = &$this->player_robots[$this_key]; //$this_info;
          }
        }
        // Otherwise, if this robot is in disabled status
        elseif ($temp_robot->robot_status == 'disabled'){
          // Increment the disabled robot counter
          $this->counters['robots_disabled']++;
          // Add this robot to the disabled robots array
          $this->values['robots_disabled'][] = &$this->player_robots[$this_key]; //$this_info;
        }

        // Increment the robot total by default
        $this->counters['robots_total']++;
        // Update or create this robot's session object
        //$temp_robot->update_session();
        unset($temp_robot);
      }

    }

    // If an active robot was not found, reset the turn counter
    if (empty($this->values['current_robot'])){
      $this->values['current_robot_enter'] = false;
    }

    // Now collect an export array for this object
    $this_data = $this->export_array();

    // Update the parent battle variable
    $this->battle->values['players'][$this->player_id] = $this_data;

    // Return true on success
    return true;

  }

  // Define a public, static function for resetting player values to base
  public static function reset_variables($this_data){
    $this_data['player_flags'] = array();
    $this_data['player_counters'] = array();
    $this_data['player_values'] = array();
    $this_data['player_history'] = array();
    $this_data['player_name'] = $this_data['player_base_name'];
    $this_data['player_token'] = $this_data['player_base_token'];
    $this_data['player_description'] = $this_data['player_base_description'];
    $this_data['player_robots'] = $this_data['player_base_robots'];
    $this_data['player_quotes'] = $this_data['player_base_quotes'];
    return $this_data;
  }

  // Define a public function for getting this player's currently active robot
  public function get_active_robot(){

    $active_target_robot = false;

    foreach ($this->player_robots AS $temp_robotinfo){

      if (empty($active_target_robot) && $temp_robotinfo['robot_position'] == 'active'){
        $active_target_robot = new mmrpg_robot($this->battle, $this, $temp_robotinfo);
        if ($active_target_robot->robot_energy < 1){
          $active_target_robot->flags['apply_disabled_state'] = true;
          $active_target_robot->flags['hidden'] = true;
          $active_target_robot->robot_status = 'disabled';
          $active_target_robot->update_session();
          $canvas_refresh = true;
        }
      } elseif (!empty($active_target_robot) && $temp_robotinfo['robot_position'] == 'active'){
        $temp_target_robot = new mmrpg_robot($this->battle, $this, $temp_robotinfo);
        $temp_target_robot->robot_position = 'bench';
        $temp_target_robot->update_session();
        $canvas_refresh = true;
        if ($temp_target_robot->robot_energy < 1){
          $temp_target_robot->flags['apply_disabled_state'] = true;
          $temp_target_robot->flags['hidden'] = true;
          $temp_target_robot->robot_status = 'disabled';
          $temp_target_robot->update_session();
          $canvas_refresh = true;
        }
      }

    }
    if (empty($active_target_robot)){

      $temp_robots_active_array = $this->values['robots_active'];
      $temp_robots_disabled_array = $this->values['robots_disabled'];
      if (!empty($temp_robots_active_array)){
        $temp_robots_active_info = array_shift($temp_robots_active_array);
        $active_target_robot = new mmrpg_robot($this->battle, $this, $temp_robots_active_info);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
      } elseif (!empty($temp_robots_disabled_array)){
        $temp_robots_active_info = array_shift($temp_robots_disabled_array);
        $active_target_robot = new mmrpg_robot($this->battle, $this, $temp_robots_active_info);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
      } else {
        $active_target_robot = $target_robot;
      }

    }

    // Update this player's session with any changes
    $this->update_session();

    // Return the collected active robot
    return $active_target_robot;

  }

  // Define a public function for updating this player's session
  public function update_session(){

    // Update any internal counters
    $this->update_variables();

    // Request parent battle object to update as well
    //$this->battle->update_session();

    // Update the session with the export array
    $this_data = $this->export_array();
    $_SESSION['PLAYERS'][$this->player_id] = $this_data;
    $this->battle->values['players'][$this->player_id] = $this_data;

    // Return true on success
    return true;

  }

  // Define a function for exporting the current data
  public function export_array(){

    // Return all internal player fields in array format
    return array(
      'battle_id' => $this->battle_id,
      'battle_token' => $this->battle_token,
      'player_id' => $this->player_id,
      'player_name' => $this->player_name,
      'player_token' => $this->player_token,
      'player_image' => $this->player_image,
      'player_image_size' => $this->player_image_size,
      'player_description' => $this->player_description,
      'player_energy' => $this->player_energy,
      'player_attack' => $this->player_attack,
      'player_defense' => $this->player_defense,
      'player_base_speed' => $this->player_base_speed,
      'player_base_energy' => $this->player_base_energy,
      'player_base_attack' => $this->player_base_attack,
      'player_base_defense' => $this->player_base_defense,
      'player_base_speed' => $this->player_base_speed,
      'player_robots' => $this->player_robots,
      'player_abilities' => $this->player_abilities,
      'player_items' => $this->player_items,
      'player_quotes' => $this->player_quotes,
      'player_rewards' => $this->player_rewards,
      'player_starforce' => $this->player_starforce,
      'player_points' => $this->player_points,
      'player_switch' => $this->player_switch,
      'player_next_action' => $this->player_next_action,
      'player_base_name' => $this->player_base_name,
      'player_base_token' => $this->player_base_token,
      'player_base_image' => $this->player_base_image,
      'player_base_image_size' => $this->player_base_image_size,
      'player_base_description' => $this->player_base_description,
      'player_base_robots' => $this->player_base_robots,
      'player_base_abilities' => $this->player_base_abilities,
      'player_base_items' => $this->player_base_items,
      'player_base_quotes' => $this->player_base_quotes,
      'player_base_rewards' => $this->player_base_rewards,
      'player_base_starforce' => $this->player_base_starforce,
      'player_base_points' => $this->player_base_points,
      'player_base_switch' => $this->player_base_switch,
      'player_side' => $this->player_side,
      'player_autopilot' => $this->player_autopilot,
      'player_frame' => $this->player_frame,
      'player_frame_index' => $this->player_frame_index,
      'player_frame_offset' => $this->player_frame_offset,
      'flags' => $this->flags,
      'counters' => $this->counters,
      'values' => $this->values,
      'history' => $this->history
      );

  }

  // Define a static function for printing out the player's database markup
  public static function print_database_markup($player_info, $print_options = array()){
    // Define the markup variable
    $this_markup = '';
    // Require the actual data file
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/player_database-markup.php');
    // Return the generated markup
    return $this_markup;

  }

  // Define a static function for printing out the player's editor markup
  public static function print_editor_markup($player_info){
    // Define the markup variable
    $this_markup = '';

    // Require the actual data file
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/player_editor-markup.php');

    // Return the generated markup
    return $this_markup;

  }

}
?>