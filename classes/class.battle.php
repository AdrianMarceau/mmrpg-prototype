<?php
/**
 * Mega Man RPG Battle
 * <p>The object class for all battles in the Mega Man RPG Prototype.</p>
 */
class rpg_battle extends rpg_object {

  // Define the internal database cache
  public static $database_index = array();

  // Define the private battle variables
  private $system = array();
  private $players = array();
  private $robots = array();
  private $abilities = array();
  private $attachments = array();
  private $items = array();

  // Define public battle variables
  public $field = null;
  public $field_id = 0;
  public $field_token = '';
  public $battle_id = 0;
  public $battle_name = '';
  public $battle_token = '';
  public $battle_description = '';
  public $battle_level = 0;
  public $battle_field_info = array();
  public $battle_this_player = array();
  public $battle_target_player = array();
  public $battle_turns_limit = 0;
  public $battle_robots_limit = 0;
  public $battle_rewards_points = 0;
  public $battle_rewards_zenny = 0;
  public $battle_rewards_robots = array();
  public $battle_rewards_abilities = array();
  public $battle_rewards_items = array();
  public $battle_status = '';
  public $battle_result = '';
  public $battle_overkill = 0;
  public $battle_base_name = '';
  public $battle_base_description = '';
  public $battle_base_rewards_points = 0;
  public $battle_base_rewards_zenny = 0;
  public $battle_base_rewards_robots = array();
  public $battle_base_rewards_abilities = array();
  public $battle_base_rewards_items = array();

  // Define private battle variables
  private $events = array();
  private $actions = array();
  private $tooltips = array();

  /**
   * Create a new RPG battle object
   * @param array $battle_info (optional)
   * @return rpg_battle
   */
  public function __construct($battle_info = array()){

    // Update the session keys for this object
    $this->session_key = 'BATTLES';
    $this->session_token = 'battle_token';
    $this->session_id = 'battle_id';
    $this->class = 'battle';

    // Collect any provided arguments
    $args = func_get_args();

    // Collect current battle data from the function if available
    $this_battleinfo = isset($args[0]) ? $args[0] : array();
    if (!isset($this_battleinfo['battle_id'])){ $this_battleinfo['battle_id'] = 0; }
    if (!isset($this_battleinfo['battle_token'])){ $this_battleinfo['battle_token'] = 'battle'; }

    // Now load the battle data from the session or index
    $this->battle_load($this_battleinfo['battle_id'], $this_battleinfo['battle_token'], $this_battleinfo);
    // Load the battle data based on the ID and fallback token
    $this_battleinfo = $this->battle_load($this_battleinfo['battle_id'], $this_battleinfo['battle_token'], $this_battleinfo);

    // Now load the battle data from the session or index
    if (empty($this_battleinfo)){
      // Player data could not be loaded
      die('Player data could not be loaded :<br />$this_battleinfo = <pre>'.print_r($this_battleinfo, true).'</pre>');
      return false;
    }

    // Update the session variable
    $this->update_session();

    // Return true on success
    return true;

  }

  /**
   * Manually (re)load battle data for this object from the session or index
   * @param int $battle_id
   * @param string $battle_token
   * @param array $custom_info (optional)
   * @return bool
   */
  public function battle_load($battle_id, $battle_token, $custom_info = array()){

    // If the battle ID was not provided, return false
    if (!isset($battle_id)){
      die("battle id must be set!\n\$this_battleinfo\n".print_r($this_battleinfo, true));
      return false;
    }
    // If the battle token was not provided, return false
    if (!isset($battle_token)){
      die("battle token must be set!\n\$this_battleinfo\n".print_r($this_battleinfo, true));
      return false;
    }

    // Collect current battle data from the session if available
    if (isset($_SESSION[$this->session_key][$battle_id])){
      $this_battleinfo = $_SESSION[$this->session_key][$battle_id];
      if ($this_battleinfo['battle_token'] != $battle_token){
        die("battle token and ID mismatch {$battle_id}:{$battle_token}!\n");
        return false;
      }
    }
    // Otherwise, collect battle data from the index
    else {
      $this_battleinfo = self::get_index_info($battle_token);
      if (empty($this_battleinfo)){
        die("battle data could not be loaded for {$battle_id}:{$battle_token}!\n");
        return false;
      }
    }

    // If the custom data was not empty, merge now
    if (!empty($custom_info)){ $this_battleinfo = array_merge($this_battleinfo, $custom_info); }

    /*
    $backup_fields = array('flags', 'values', 'counters');
    $backup_values = array('');
    foreach ($backup_fields AS $field){ $backup_values[$field] = isset($this_battleinfo[$field]) ? $this_battleinfo[$field] : array(); }
    $this_battleinfo = array_replace($this_battleinfo, $this_battleinfo_backup);
    foreach ($backup_fields AS $field){ $this_battleinfo[$field] = isset($this_battleinfo[$field]) ? array_replace($this_battleinfo[$field], $backup_values[$field]) : $backup_values[$field]; }
    */

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
    $this->battle_level = isset($this_battleinfo['battle_level']) ? $this_battleinfo['battle_level'] : 0;
    $this->battle_field_info = isset($this_battleinfo['battle_field_info']) ? $this_battleinfo['battle_field_info'] : array();
    $this->battle_this_player = isset($this_battleinfo['battle_this_player']) ? $this_battleinfo['battle_this_player'] : array();
    $this->battle_target_player = isset($this_battleinfo['battle_target_player']) ? $this_battleinfo['battle_target_player'] : array();
    $this->battle_turns_limit = isset($this_battleinfo['battle_turns_limit']) ? $this_battleinfo['battle_turns_limit'] : 1;
    $this->battle_robots_limit = isset($this_battleinfo['battle_robots_limit']) ? $this_battleinfo['battle_robots_limit'] : 1;
    $this->battle_rewards_points = isset($this_battleinfo['battle_rewards_points']) ? $this_battleinfo['battle_rewards_points'] : 0;
    $this->battle_rewards_zenny = isset($this_battleinfo['battle_rewards_zenny']) ? $this_battleinfo['battle_rewards_zenny'] : 0;
    $this->battle_rewards_robots = isset($this_battleinfo['battle_rewards_robots']) ? $this_battleinfo['battle_rewards_robots'] : array();
    $this->battle_rewards_abilities = isset($this_battleinfo['battle_rewards_robots']) ? $this_battleinfo['battle_rewards_robots'] : array();
    $this->battle_rewards_items = isset($this_battleinfo['battle_rewards_robots']) ? $this_battleinfo['battle_rewards_robots'] : array();
    $this->battle_status = isset($this_battleinfo['battle_status']) ? $this_battleinfo['battle_status'] : 'active';
    $this->battle_result = isset($this_battleinfo['battle_result']) ? $this_battleinfo['battle_result'] : 'pending';
    $this->battle_overkill = isset($this_battleinfo['battle_overkill']) ? $this_battleinfo['battle_overkill'] : 0;

    // Define the internal robot base values using the robots index array
    $this->battle_base_name = isset($this_battleinfo['battle_base_name']) ? $this_battleinfo['battle_base_name'] : $this->battle_name;
    $this->battle_base_description = isset($this_battleinfo['battle_base_description']) ? $this_battleinfo['battle_base_description'] : $this->battle_description;
    $this->battle_base_rewards_points = isset($this_battleinfo['battle_base_points']) ? $this_battleinfo['battle_base_points'] : $this->battle_rewards_points;
    $this->battle_base_rewards_zenny = isset($this_battleinfo['battle_base_zenny']) ? $this_battleinfo['battle_base_zenny'] : $this->battle_rewards_zenny;
    $this->battle_base_rewards_robots = isset($this_battleinfo['battle_base_rewards_robots']) ? $this_battleinfo['battle_base_rewards_robots'] : array();
    $this->battle_base_rewards_abilities = isset($this_battleinfo['battle_base_rewards_abilities']) ? $this_battleinfo['battle_base_rewards_abilities'] : array();
    $this->battle_base_rewards_items = isset($this_battleinfo['battle_base_rewards_items']) ? $this_battleinfo['battle_base_rewards_items'] : array();

    // If objects were defined in this battleinfo, extract them
    if (!empty($this_battleinfo['objects'])){

      // Loop through and add players to the index
      if (!empty($this_battleinfo['objects']['players'])){
        foreach ($this_battleinfo['objects']['players'] AS $key => $player_id){
          if ($this->player_exists($player_id)){ continue; }
          $player_info = $this->get_session_player($player_id);
          $this->add_player($player_info);
        }
      }

      // Loop through and add robots to the index
      if (!empty($this_battleinfo['objects']['robots'])){
        foreach ($this_battleinfo['objects']['robots'] AS $key => $robot_id){
          if ($this->robot_exists($robot_id)){ continue; }
          $robot_info = $this->get_session_robot($robot_id);
          echo basename(__FILE__).' on line '.__LINE__."\n";
          $this_player = $this->get_player($robot_info['player_id']);
          echo basename(__FILE__).' on line '.__LINE__."\n";
          $this->add_robot($this_player, $robot_info);
        }
      }

      // Loop through and add abilities to the index
      if (!empty($this_battleinfo['objects']['abilities'])){
        foreach ($this_battleinfo['objects']['abilities'] AS $key => $ability_id){
          if ($this->ability_exists($ability_id)){ continue; }
          $ability_info = $this->get_session_ability($ability_id);
          echo basename(__FILE__).' on line '.__LINE__."\n";
          $this_player = $this->get_player($ability_info['player_id']);
          echo basename(__FILE__).' on line '.__LINE__."\n";
          $this_robot = $this->get_robot($ability_info['robot_id']);
          $this->add_ability($this_player, $this_robot, $ability_info);
        }
      }

      // Loop through and add attachments to the index
      if (!empty($this_battleinfo['objects']['attachments'])){
        foreach ($this_battleinfo['objects']['attachments'] AS $key => $attachment_id){
          if ($this->attachment_exists($attachment_id)){ continue; }
          $attachment_info = $this->get_session_attachment($attachment_id);
          echo basename(__FILE__).' on line '.__LINE__."\n";
          $this_player = $this->get_player($attachment_info['player_id']);
          $this_robot = $this->get_robot($attachment_info['robot_id']);
          $this->add_attachment($this_player, $this_robot, $attachment_info);
        }
      }

      // Loop through and add items to the index
      if (!empty($this_battleinfo['objects']['items'])){
        foreach ($this_battleinfo['objects']['items'] AS $key => $item_id){
          if ($this->item_exists($item_id)){ continue; }
          $item_info = $this->get_session_item($item_id);
          echo basename(__FILE__).' on line '.__LINE__."\n";
          $this_player = $this->get_player($item_info['player_id']);
          $this_robot = $this->get_robot($item_info['robot_id']);
          $this->add_item($this_player, $this_robot, $item_info);
        }
      }


    }

    // Return true on success
    return true;

  }

  /**
   * Return a reference to the global battle object
   * @return rpg_battle
   */
  public static function get_battle(){
    $this_battle = isset($GLOBALS['this_battle']) ? $GLOBALS['this_battle'] : new rpg_battle();
    $this_battle->refresh();
    return $this_battle;
  }


  /**
   * Load new player data into the global battle index
   * @param array $player_info
   * @return bool
   */
  public function add_player($player_info){
    if (!isset($player_info['player_id'])){ return false; }
    elseif (!isset($player_info['player_token'])){ return false; }
    $player_id = $player_info['player_id'];
    if (!isset($this->players[$player_id])){
      $this_player = new rpg_player($player_info);
      $this->players[$player_id] = $this_player;
      $this_player->update_session();
    }
    if (!empty($this->players[$player_id])){ return true; }
    else { return false; }
  }

  /**
   * Return existing player data from the global battle index via player_id
   * @param int $player_id
   * @return rpg_player
   */
  public function get_player($player_id){
    if (!is_numeric($player_id)){ return false; }
    elseif (!isset($this->players[$player_id])){ return false; }
    return $this->players[$player_id];
  }

  /**
   * Return an array of existing player IDs in the global battle index
   * @return array
   */
  public function get_player_ids(){
    $player_ids = array();
    if (!empty($this->players)){ $player_ids = array_keys($this->players); }
    return $player_ids;
  }

  /**
   * Request a reference to existing player data in the battle object via filters
   * @param array $filters
   * @return rpg_player
   */
  public function find_player($filters, $invert = false){
    if (empty($this->players) || empty($filters)){ return false; }
    foreach ($this->players AS $player_id => $this_player){
      $is_match = true;
      foreach ($filters AS $field_name => $field_value){
        if ($invert == false && $this_player->$field_name != $field_value){
          $is_match = false;
          break;
        } elseif ($invert == true && $this_player->$field_name == $field_value){
          $is_match = false;
          break;
        }
      }
      if ($is_match){
        return $this_player;
      }
    }
    return false;
  }

  /**
   * Check for existing player data in the battle object via player_id
   * @param int $player_id
   * @return bool
   */
  public function player_exists($player_id){
    return isset($this->players[$player_id]) ? true : false;
  }



  /**
   * Load new robot data into the battle object and index by robot_id
   * @param rpg_player $this_player
   * @param array $robot_info
   * @return bool
   */
  public function add_robot(rpg_player $this_player, $robot_info){
    if (!isset($robot_info['robot_id'])){ return false; }
    elseif (!isset($robot_info['robot_token'])){ return false; }
    $robot_id = $robot_info['robot_id'];
    if (!isset($this->robots[$robot_id])){
      $this_robot = new rpg_robot($this_player, $robot_info);
      $this->robots[$robot_id] = $this_robot;
      $this_robot->update_session();
    }
    if (!empty($this->robots[$robot_id])){ return true; }
    else { return false; }
  }

  /**
   * Request a reference to existing robot data in the battle object via robot_id
   * @param int $robot_id
   * @return rpg_robot
   */
  public function get_robot($robot_id){
    if (!is_numeric($robot_id)){ return false; }
    elseif (!isset($this->robots[$robot_id])){ return false; }
    return $this->robots[$robot_id];
  }

  /**
   * Request a reference to existing robot data in the battle object via filters
   * @param array $filters
   * @return rpg_robot
   */
  public function find_robot($filters){
    if (empty($this->robots) || empty($filters)){ return false; }
    foreach ($this->robots AS $robot_id => $this_robot){
      $is_match = true;
      foreach ($filters AS $field_name => $field_value){
        if ($this_robot->$field_name != $field_value){
          $is_match = false;
          break;
        }
      }
      if ($is_match){
        return $this_robot;
      }
    }
    return false;
  }

  /**
   * Request a list of references to existing robot data in the battle object via filters
   * @param array $filters
   * @return rpg_robot
   */
  public function find_robots($filters){
    if (empty($this->robots) || empty($filters)){ return false; }
    $robots = array();
    foreach ($this->robots AS $robot_id => $this_robot){
      $is_match = true;
      foreach ($filters AS $field_name => $field_value){
        if ($this_robot->$field_name != $field_value){
          $is_match = false;
          break;
        }
      }
      if ($is_match){
        $robots[] = $this_robot;
      }
    }
    return $robots;
  }

  /**
   * Check for existing robot data in the battle object via robot_id
   * @param int $robot_id
   * @return bool
   */
  public function robot_exists($robot_id){
    return isset($this->robots[$robot_id]) ? true : false;
  }


  /**
   * Load new ability data into the battle object and index by ability_id
   * @param rpg_player $this_player
   * @param rpg_robot $this_robot
   * @param array $ability_info
   * @return bool
   */
  public function add_ability(rpg_player $this_player, rpg_robot $this_robot, $ability_info){
    if (!isset($ability_info['ability_id'])){ return false; }
    elseif (!isset($ability_info['ability_token'])){ return false; }
    $ability_id = $ability_info['ability_id'];
    if (!isset($this->abilities[$ability_id])){
      $this_ability = new rpg_ability($this_player, $this_robot, $ability_info);
      $this->abilities[$ability_id] = $this_ability;
      $this_ability->update_session();
    }
    if (!empty($this->abilities[$ability_id])){ return true; }
    else { return false; }
  }

  /**
   * Update existing ability data into the battle object and index by ability_id
   * @param int $ability_id
   * @param array $ability_info
   */
  public function update_ability($ability_id, $ability_info){
    if (!isset($ability_id) || empty($ability_info)){ return false; }
    elseif (!$this->ability_exists($ability_id)){ return false; }
    $this_ability = $this->abilities[$ability_id];
    foreach ($ability_info AS $key => $value){
      if (preg_match('/(_id|_token)$/', $key)){ continue; }
      elseif (isset($this_ability->$key)){
        $this_ability->set_info($key, $value);
      }
    }
  }

  /**
   * Request a reference to existing ability data in the battle object via filters
   * @param array $filters
   * @return rpg_ability
   */
  public function find_ability($filters, $invert = false){
    if (empty($this->abilities) || empty($filters)){ return false; }
    foreach ($this->abilities AS $ability_id => $this_ability){
      $is_match = true;
      foreach ($filters AS $field_name => $field_value){
        if ($invert == false && $this_ability->$field_name != $field_value){
          $is_match = false;
          break;
        } elseif ($invert == true && $this_ability->$field_name == $field_value){
          $is_match = false;
          break;
        }
      }
      if ($is_match){
        return $this_ability;
      }
    }
    return false;
  }

  /**
   * Request a reference to existing ability data in the battle object via ability_id
   * @param int $ability_id
   * @return rpg_ability
   */
  public function get_ability($ability_id){
    if (!isset($this->abilities[$ability_id])){ return false; }
    return $this->abilities[$ability_id];
  }

  /**
   * Check for existing ability data in the battle object via ability_id
   * @param int $ability_id
   * @return bool
   */
  public function ability_exists($ability_id){
    return isset($this->abilities[$ability_id]) ? true : false;
  }


  /**
   * Load new attachment data into the battle object and index by attachment_id
   * @param rpg_player $this_player
   * @param rpg_robot $this_robot
   * @param array $attachment_info
   * @return bool
   */
  public function add_attachment(rpg_player $this_player, rpg_robot $this_robot, $attachment_info){
    if (!isset($attachment_info['attachment_id'])){ return false; }
    elseif (!isset($attachment_info['attachment_token'])){ return false; }
    $attachment_id = $attachment_info['attachment_id'];
    $attachment_id = $this->robot_id.str_pad($attachment_id, 3, '0', STR_PAD_LEFT);
    if (!isset($this->attachments[$attachment_id])){
      $this_attachment = new rpg_attachment($this_player, $this_robot, $attachment_info);
      $this->attachments[$attachment_id] = $this_attachment;
      $this_attachment->update_session();
    }
    if (!empty($this->attachments[$attachment_id])){ return true; }
    else { return false; }
  }


  /**
   * Remove attachment data from the battle object index by attachment_id
   * @param int $attachment_id
   */
  public function unset_attachment($attachment_id){
    if (!isset($attachment_id) || empty($attachment_info)){ return false; }
    elseif (!$this->attachment_exists($attachment_id)){ return false; }
    unset($this->attachments[$attachment_id]);
  }

  /**
   * Update existing attachment data into the battle object and index by attachment_id
   * @param int $attachment_id
   * @param array $attachment_info
   */
  public function update_attachment($attachment_id, $attachment_info){
    if (!isset($attachment_id) || empty($attachment_info)){ return false; }
    elseif (!$this->attachment_exists($attachment_id)){ return false; }
    $this_attachment = $this->attachments[$attachment_id];
    foreach ($attachment_info AS $key => $value){
      if (preg_match('/(_id|_token)$/', $key)){ continue; }
      elseif (isset($this_attachment->$key)){
        $this_attachment->set_info($key, $value);
      }
    }
  }

  /**
   * Request a reference to existing attachment data in the battle object via attachment_id
   * @param int $attachment_id
   * @return rpg_attachment
   */
  public function get_attachment($attachment_id){
    if (!isset($this->attachments[$attachment_id])){ return false; }
    return $this->attachments[$attachment_id];
  }

  /**
   * Check for existing attachment data in the battle object via attachment_id
   * @param int $attachment_id
   * @return bool
   */
  public function attachment_exists($attachment_id){
    return isset($this->attachments[$attachment_id]) ? true : false;
  }


  /**
   * Load new item data into the battle object and index by item_id
   * @param rpg_player $this_player
   * @param rpg_robot $this_robot
   * @param array $item_info
   * @return bool
   */
  public function add_item(rpg_player $this_player, rpg_robot $this_robot, $item_info){
    if (!isset($item_info['item_id'])){ return false; }
    elseif (!isset($item_info['item_token'])){ return false; }
    $item_id = $item_info['item_id'];
    $item_id = $this->robot_id.str_pad($item_id, 3, '0', STR_PAD_LEFT);
    if (!isset($this->items[$item_id])){
      $this_item = new rpg_item($this_player, $this_robot, $item_info);
      $this->items[$item_id] = $this_item;
      $this_item->update_session();
    }
    if (!empty($this->items[$item_id])){ return true; }
    else { return false; }
  }

  /**
   * Update existing item data into the battle object and index by item_id
   * @param int $item_id
   * @param array $item_info
   */
  public function update_item($item_id, $item_info){
    if (!isset($item_id) || empty($item_info)){ return false; }
    elseif (!$this->item_exists($item_id)){ return false; }
    $this_item = $this->items[$item_id];
    foreach ($item_info AS $key => $value){
      if (preg_match('/(_id|_token)$/', $key)){ continue; }
      elseif (isset($this_item->$key)){
        $this_item->set_info($key, $value);
      }
    }
  }

  /**
   * Request a reference to existing item data in the battle object via item_id
   * @param int $item_id
   * @return rpg_item
   */
  public function get_item($item_id){
    if (!isset($this->items[$item_id])){ return false; }
    return $this->items[$item_id];
  }

  /**
   * Check for existing item data in the battle object via item_id
   * @param int $item_id
   * @return bool
   */
  public function item_exists($item_id){
    return isset($this->items[$item_id]) ? true : false;
  }


  // -- ID FUNCTIONS -- //

  /**
   * Get the ID of this battle object
   * @return integer
   */
  public function get_id(){
    return intval($this->get_info('battle_id'));
  }

  /**
   * Set the ID of this battle object
   * @param int $value
   */
  public function set_id($id){
    $this->set_info('battle_id', intval($id));
  }


  // -- NAME FUNCTIONS -- //

  /**
   * Get the name of this battle object
   * @return string
   */
  public function get_name(){
    return $this->get_info('battle_name');
  }

  /**
   * Set the name of this battle object
   * @param string $value
   */
  public function set_name($name){
    $this->set_info('battle_name', $name);
  }

  /**
   * Get the base name of this battle object
   * @return string value
   */
  public function get_base_name(){
    return $this->get_info('battle_base_name');
  }

  /**
   * Set the base name of this battle object
   * @param string $value
   */
  public function set_base_name($name){
    $this->set_info('battle_base_name', $name);
  }


  // -- TOKEN FUNCTIONS -- //

  /**
   * Get the token of this battle object
   * @return string
   */
  public function get_token(){
    return $this->get_info('battle_token');
  }

  /**
   * Set the token of this battle object
   * @param string $value
   */
  public function set_token($token){
    $this->set_info('battle_token', $token);
  }


  // -- DESCRIPTION FUNCTIONS -- //

  /**
   * Get the description of this battle object
   * @return string
   */
  public function get_description(){
    return $this->get_info('battle_description');
  }

  /**
   * Set the description of this battle object
   * @param string $description
   */
  public function set_description($description){
    $this->set_info('battle_description', $description);
  }

  /**
   * Get the base description of this battle object
   * @return string
   */
  public function get_base_description(){
    return $this->get_info('battle_base_description');
  }

  /**
   * Set the base description of this battle object
   * @param string $description
   */
  public function set_base_description($description){
    $this->set_info('battle_base_description', $description);
  }


  // -- TURN FUNCTIONS -- //

  /**
   * Get the target turns for this battle object
   * @return integer
   */
  public function get_turns(){
    return $this->get_info('battle_turns_limit');
  }

  /**
   * Set the target turns for this battle object
   * @param int $turns
   */
  public function set_turns($turns){
    $this->set_info('battle_turns_limit', $turns);
  }


  // -- STATUS FUNCTIONS -- //

  /**
   * Get the status of this battle object
   * @return string
   */
  public function get_status(){
    return $this->get_info('battle_status');
  }

  /**
   * Set the status of this battle object
   * @param string $status
   */
  public function set_status($status){
    $this->set_info('battle_status', $status);
  }


  // -- RESULT FUNCTIONS -- //

  /**
   * Get the result of this battle object
   * @return string
   */
  public function get_result(){
    return $this->get_info('battle_result');
  }

  /**
   * Set the result of this battle object
   * @param string $result
   */
  public function set_result($result){
    $this->set_info('battle_result', $result);
  }


  // -- ROBOT LIMIT FUNCTIONS -- //

  /**
   * Get the target robot limit for this battle object
   * @return integer
   */
  public function get_robot_limit(){
    return $this->get_info('battle_robots_limit');
  }

  /**
   * Set the target robot limit for this battle object
   * @param int $limit
   */
  public function set_robot_limit($limit){
    $this->set_info('battle_robots_limit', $limit);
  }


  // -- FIELD BASE FUNCTIONS -- /

  /**
   * Get the target field for this battle object
   * @return array
   */
  public function get_field_info(){
    return $this->get_info('battle_field_info');
  }

  /**
   * Set the base field for this battle object
   * @param array $field
   */
  public function set_field_info($field){
    $this->set_info('battle_field_info', $field);
  }


  // -- THIS PLAYER FUNCTIONS -- /

  /**
   * Get the human player for this battle object
   * @return array
   */
  public function get_this_player(){
    return $this->get_info('battle_this_player');
  }

  /**
   * Set the human player for this battle object
   * @param array $player_info
   */
  public function set_this_player($player_info){
    $this->set_info('battle_this_player', $player_info);
  }


  // -- TARGET PLAYER FUNCTIONS -- /

  /**
   * Get the target player for this battle object
   * @return array
   */
  public function get_target_player(){
    return $this->get_info('battle_target_player');
  }

  /**
   * Set the target player for this battle object
   * @param array $player_info
   */
  public function set_target_player($player_info){
    $this->set_info('battle_target_player', $player_info);
  }


  // -- REWARDS FUNCTIONS -- /

  /**
   * Get the robot rewards for this battle object
   * @return array
   */
  public function get_robot_rewards(){
    return $this->get_info('battle_rewards_robots');
  }

  /**
   * Set the robot rewards for this battle object
   * @param array $rewards
   */
  public function set_robot_rewards($rewards){
    $this->set_info('battle_rewards_robots', $rewards);
  }

  /**
   * Get the base robot rewards for this battle object
   * @return array
   */
  public function get_base_robot_rewards(){
    return $this->get_info('battle_base_rewards_robots');
  }

  /**
   * Set the base robot rewards for this battle object
   * @param array $rewards
   */
  public function set_base_robot_rewards($rewards){
    $this->set_info('battle_base_rewards_robots', $rewards);
  }

  /**
   * Reset the robot rewards for this battle object
   */
  public function reset_robot_rewards(){
    $this->set_info('battle_rewards_robots', $this->get_info('battle_base_rewards_robots'));
  }

  /**
   * Get the ability rewards for this battle object
   * @return array
   */
  public function get_ability_rewards(){
    return $this->get_info('battle_rewards_abilities');
  }

  /**
   * Set the ability rewards for this battle object
   * @param array $rewards
   */
  public function set_ability_rewards($rewards){
    $this->set_info('battle_rewards_abilities', $rewards);
  }

  /**
   * Get the base ability rewards for this battle object
   * @return array
   */
  public function get_base_ability_rewards(){
    return $this->get_info('battle_base_rewards_abilities');
  }

  /**
   * Set the base ability rewards for this battle object
   * @param array $rewards
   */
  public function set_base_ability_rewards($rewards){
    $this->set_info('battle_base_rewards_abilities', $rewards);
  }

  /**
   * Reset the ability rewards for this battle object
   */
  public function reset_ability_rewards(){
    $this->set_info('battle_rewards_abilities', $this->get_info('battle_base_rewards_abilities'));
  }

  /**
   * Get the item rewards for this battle object
   * @return array
   */
  public function get_item_rewards(){
    return $this->get_info('battle_rewards_items');
  }

  /**
   * Set the item rewards for this battle object
   * @param array $rewards
   */
  public function set_item_rewards($rewards){
    $this->set_info('battle_rewards_items', $rewards);
  }

  /**
   * Get the base item rewards for this battle object
   * @return array
   */
  public function get_base_item_rewards(){
    return $this->get_info('battle_base_rewards_items');
  }

  /**
   * Set the base item rewards for this battle object
   * @param array $rewards
   */
  public function set_base_item_rewards($rewards){
    $this->set_info('battle_base_rewards_items', $rewards);
  }

  /**
   * Reset the item rewards for this battle object
   */
  public function reset_item_rewards(){
    $this->set_info('battle_rewards_items', $this->get_info('battle_base_rewards_items'));
  }


  // -- POINTS FUNCTIONS -- /

  /**
   * Get the reward points for this battle object
   * @return integer
   */
  public function get_points(){
    return $this->get_info('battle_points');
  }

  /**
   * Set the reward points for this battle object
   * @param int $points
   */
  public function set_points($points){
    $this->set_info('battle_points', $points);
  }

  /**
   * Get the base reward points for this battle object
   * @return integer
   */
  public function get_base_points(){
    return $this->get_info('battle_base_points');
  }

  /**
   * Set the base reward points for this battle object
   * @param int $points
   */
  public function set_base_points($points){
    $this->set_info('battle_base_points', $points);
  }

  /**
   * Reset the reward points for this battle object
   */
  public function reset_points(){
    $this->set_info('battle_points', $this->get_info('battle_base_points'));
  }


  // -- ZENNY FUNCTIONS -- /

  /**
   * Get the reward zenny for this battle object
   * @return integer
   */
  public function get_zenny(){
    return $this->get_info('battle_zenny');
  }

  /**
   * Set the reward zenny for this battle object
   * @param int $zenny
   */
  public function set_zenny($zenny){
    $this->set_info('battle_zenny', $zenny);
  }

  /**
   * Get the base reward zenny for this battle object
   * @return integer
   */
  public function get_base_zenny(){
    return $this->get_info('battle_base_zenny');
  }

  /**
   * Set the base reward zenny for this battle object
   * @param int $zenny
   */
  public function set_base_zenny($zenny){
    $this->set_info('battle_base_zenny', $zenny);
  }

  /**
   * Reset the reward zenny for this battle object
   */
  public function reset_zenny(){
    $this->set_info('battle_zenny', $this->get_info('battle_base_zenny'));
  }


  // -- LEVEL FUNCTIONS -- /

  /**
   * Get the difficulty level of this battle object
   * @return integer
   */
  public function get_level(){
    return $this->get_info('battle_level');
  }

  /**
   * Set the difficulty level of this battle object
   * @param int $level
   */
  public function set_level($level){
    $this->set_info('battle_level', $level);
  }


  // -- OVERKILL FUNCTIONS -- /

  /**
   * Get the overkill total for this battle object
   * @return integer
   */
  public function get_overkill(){
    return $this->get_info('battle_overkill');
  }

  /**
   * Set the overkill total for this battle object
   * @param int $overkill
   */
  public function set_overkill($overkill){
    $this->set_info('battle_overkill', $overkill);
  }

  /**
   * Add to the overkill total of this battle object
   * @param int $overkill
   */
  public function add_overkill($overkill){
    $new_overkill = $this->get_info('battle_overkill') + $overkill;
    $this->set_info('battle_overkill', $new_overkill);
  }


  // -- PRINT FUNCTIONS -- /

  /**
   * Get the formatted name of this battle object
   * @return string
   */
  public function print_name(){
    return '<span class="battle_name battle_type">'.$this->battle_name.'</span>';
  }

  /**
   * Get the formatted token of this battle object
   * @return string
   */
  public function print_token(){ return '<span class="battle_token">'.$this->battle_token.'</span>'; }

  /**
   * Get the formatted description of this battle object
   * @return string
   */
  public function print_description(){ return '<span class="battle_description">'.$this->battle_description.'</span>'; }

  /**
   * Get the formatted reward points for this battle object
   * @return string
   */
  public function print_points(){
    return '<span class="battle_points">'.$this->battle_rewards_points.'</span>';
  }

  /**
   * Get the formatted reward zenny for this battle object
   * @return string
   */
  public function print_zenny(){
    return '<span class="battle_zenny">'.$this->battle_rewards_zenny.'</span>';
  }


  // -- INDEX FUNCTIONS -- //

  /**
   * Get the entire battle index array with parsed info
   * @param bool $session
   * @return array
   */
  public static function get_index($session = true){
    // Load the battle index if not
    self::load_battle_index();
    $this_index = self::$database_index;
    return $this_index;
  }

  /**
   * Update battle info in the global index with custom values via battle token
   * @param string $battle_token
   * @param array $battle_info
   */
  public static function update_index_info($battle_token, $battle_info){

    // Collect references to global objects
    $this_database = cms_database::get_database();

    // Add the updated, customized info to the session index
    $_SESSION['GAME']['values']['battle_index'][$battle_token] = json_encode($battle_info);

  }

  /**
   * Reset battle info in the global index to base values via battle token
   * @param string $battle_token
   */
  public static function reset_index_info($battle_token){

    // Remove the custom data in the session
    if (isset($_SESSION['GAME']['values']['battle_index'][$battle_token])){
      unset($_SESSION['GAME']['values']['battle_index'][$battle_token]);
    }

  }

  /**
   * Request battle info from the global index via battle token
   * @param string $battle_token
   * @return array
   */
  public static function get_index_info($battle_token, $session = true){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_index = self::get_index($session);

    // If the requested battle is in the index, return the entry
    if (!empty($this_index[$battle_token])){
      // Decode the info and return the array
      $battle_info = json_decode($this_index[$battle_token], true);
    }
    // Otherwise if the battle index doesn't exist at all
    else {
      // Return empty array on failure
      $battle_info = array();
    }

    //die('<pre>get_index_info('.$battle_token.') = '.print_r($battle_info, true).'</pre>');

    // Return the battle info
    return $battle_info;

  }

  /**
   * Load the battle index from the session or cache file and return success
   * @param bool include_session
   * @return bool
   */
  public static function load_battle_index($include_session = true){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_index = array();

    // Default the battles index to an empty array
    $battles_index = array();

    // If caching is turned OFF, or a cache has not been created
    if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists(MMRPG_CONFIG_BATTLES_CACHE_PATH)){
      // Start indexing the battle data files
      $battles_cache_markup = self::index_battle_data();
      // Implode the markup into a single string and enclose in PHP tags
      $battles_cache_markup = implode('', $battles_cache_markup);
      $battles_cache_markup = "<?php\n".$battles_cache_markup."\n?>";
      // Write the index to a cache file, if caching is enabled
      $battles_cache_file = @fopen(MMRPG_CONFIG_BATTLES_CACHE_PATH, 'w');
      if (!empty($battles_cache_file)){
        @fwrite($battles_cache_file, $battles_cache_markup);
        @fclose($battles_cache_file);
      }
    }

    // Include the cache file so it can be evaluated
    require_once(MMRPG_CONFIG_BATTLES_CACHE_PATH);

    //die('<pre>$battles_index => '.print_r($battles_index, true).'</pre>');

    // Return false if we got nothing from the index
    if (empty($battles_index)){ return array(); }

    // Loop through the battles and index them after serializing
    foreach ($battles_index AS $token => $array){ $this_index[$token] = json_encode($array); }

    // Additionally, include any dynamic session-based battles
    if (!empty($include_session) && !empty($_SESSION['GAME']['values']['battle_index'])){
      // The session-based battles exist, so merge them with the index
      $this_index = array_merge($this_index, $_SESSION['GAME']['values']['battle_index']);
    }

    //echo('<pre>self::$database_index = $this_index => '.print_r($this_index, true).'</pre>');

    // Update the internal index
    self::$database_index = $this_index;

    // Return the index on success
    return true;

  }

  /**
   * Generate the battle index cache file by scanning the filesystem and return markup
   * @param string $this_path
   * @return string
   */
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
        $append_cache_markup = self::index_battle_data($this_path.$filename.'/');
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
        $this_battle_markup[1] = preg_replace('#\$battle = array\(#i', "\$battles_index['{$this_battle_token}'] = array(\n  'battle_token' => '{$this_battle_token}',\n  'battle_functions' => 'battles/{$this_path}{$filename}',", $this_battle_markup[1]);
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


  // -- ACTION FUNCTIONS -- //

  /**
   * Extact and remove given action(s) from the global battle queue using filters
   * @param array $filters
   * @return array
   */
  public function actions_extract($filters){
    $this_battle = rpg_battle::get_battle();
    $extracted_actions = array();
    foreach($this_battle->actions AS $action_key => $action_array){
      $is_match = true;
      if (!empty($filters['this_player_id']) && $action_array['this_player'] != $filters['this_player_id']){ $is_match = false; }
      if (!empty($filters['this_robot_id']) && $action_array['this_robot'] != $filters['this_robot_id']){ $is_match = false; }
      if (!empty($filters['target_player_id']) && $action_array['target_player'] != $filters['target_player_id']){ $is_match = false; }
      if (!empty($filters['target_robot_id']) && $action_array['target_robot'] != $filters['target_robot_id']){ $is_match = false; }
      if (!empty($filters['action_type']) && $action_array['action_type'] != $filters['action_type']){ $is_match = false; }
      if (!empty($filters['action_token']) && $action_array['action_token'] != $filters['action_token']){ $is_match = false; }
      if ($is_match){ $extracted_actions = array_slice($this_battle->actions, $action_key, 1, false); }
    }
    return $extracted_actions;
  }

  /**
   * Insert an array of actions into the global battle queue
   * @param array $actions
   */
  public function actions_insert($actions){
    $this_battle = rpg_battle::get_battle();
    if (!empty($actions)){
      $this_battle->actions = array_merge($this_battle->actions, $actions);
    }
  }

  /**
   * Prepend an action to the start of the global battle queue
   * @param int $this_player_id
   * @param int $this_robot_id
   * @param int $target_player_id
   * @param int $target_robot_id
   * @param string $action_type
   * @param string $action_token
   * @return array
   */
  public function actions_prepend($this_player_id, $this_robot_id, $target_player_id, $target_robot_id, $action_type, $action_token){
    $this_battle = rpg_battle::get_battle();
    array_unshift($this_battle->actions, array(
      'this_player_id' => $this_player_id,
      'this_robot_id' => $this_robot_id,
      'target_player_id' => $target_player_id,
      'target_robot_id' => $target_robot_id,
      'action_type' => $action_type,
      'action_token' => $action_token
      ));
    return $this_battle->actions;
  }

  /**
   * Append an action to the end of the global battle queue
   * @param int $this_player_id
   * @param int $this_robot_id
   * @param int $target_player_id
   * @param int $target_robot_id
   * @param string $action_type
   * @param string $action_token
   * @return array
   */
  public function actions_append($this_player_id, $this_robot_id, $target_player_id, $target_robot_id, $action_type, $action_token){
    $this_battle = rpg_battle::get_battle();
    array_push($this_battle->actions, array(
      'this_player_id' => $this_player_id,
      'this_robot_id' => $this_robot_id,
      'target_player_id' => $target_player_id,
      'target_robot_id' => $target_robot_id,
      'action_type' => $action_type,
      'action_token' => $action_token
      ));
    return $this_battle->actions;
  }

  /**
   * Empty all actions in the global battle queue
   */
  public function actions_empty(){
    $this_battle = rpg_battle::get_battle();
    $this_battle->actions = array();
    return $this_battle->actions;
  }

  /**
   * Execute all actions in the global battle queue one after the other
   */
  public function actions_execute(){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // Loop through the non-empty action queue and trigger actions
    $key = 0;
    while (!empty($this_battle->actions) && $this_battle->get_status() != 'complete'){

      // Shift and collect the oldest action from the queue
      $current_action = array_shift($this_battle->actions);
      //echo 'actions_execute('.$key.')'.PHP_EOL;
      //echo '$current_action = '.print_r($current_action, true).PHP_EOL;

      // Collect references to relevant player and robot data
      $this_player = !empty($current_action['this_player_id']) ? $this_battle->get_player($current_action['this_player_id']) : false;
      $this_robot = !empty($current_action['this_robot_id']) ? $this_battle->get_robot($current_action['this_robot_id']) : false;
      $target_player = !empty($current_action['target_player_id']) ? $this_battle->get_player($current_action['target_player_id']) : false;
      $target_robot = !empty($current_action['target_robot_id']) ? $this_battle->get_robot($current_action['target_robot_id']) : false;

      if (empty($target_player)){ throw($target_player); }

      // Collect the current action and token
      $action_type = !empty($current_action['action_type']) ? $current_action['action_type'] : '';
      $action_token = !empty($current_action['action_token']) ? $current_action['action_token'] : '';

      // If the robot's player is on autopilot and the action is empty, automate input
      if (!empty($this_player) && $this_player->get_autopilot() != false && empty($action_type)){
        $action_type = 'ability';
      }
      // Else if this is a start action, clear the token
      elseif ($action_type == 'start'){
        $action_token = '';
      }

      // Based on the action type, trigger the appropriate battle function
      $battle_action = $this_battle->trigger_action(
        $this_player,
        $this_robot,
        $target_player,
        $target_robot,
        $action_type,
        $action_token
        );

      // Create a closing event with robots in base frames, if the battle is not over
      if (!empty($action_type) && $this_battle->get_status() != 'complete' && $action_type != 'start'){
        $this_battle->events_create();
      }

      $key++;
    }

    // Return true on loop completion
    return true;

  }

  /**
   * Trigger an action pulled from the global battle queue
   * @param rpg_player this_player
   * @param rpg_robot this_robot
   * @param rpg_player target_player
   * @param rpg_robot target_robot
   * @param string $action_type
   * @param string $action_token
   */
  public function trigger_action($this_player, $this_robot, $target_player, $target_robot, $action_type = '', $action_token = ''){

    // Default the return variable to false
    $this_return = false;

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // If the target player does not have any robots left
    if ($target_player->get_counter('robots_active') == 0){
      // Trigger the battle complete action to update status and result
      $this_battle->trigger_complete($this_player, $this_robot, $target_player, $target_robot);
    }

    // Start the battle loop to allow breaking
    $battle_loop = true;
    while ($battle_loop == true && $this_battle->battle_status != 'complete'){

      // If the battle is just starting
      if ($action_type == 'start'){
        // If the target player is hidden
        if ($this_player->player_token == 'player'){

          // Create the enter event for this robot
          $event_header = $this_robot->robot_name;
          $event_body = "{$this_robot->print_name()} wants to fight!<br />";
          $this_robot->set_frame('defend');
          $this_robot->set_frame_styles('');
          $this_robot->set_detail_styles('');
          $this_robot->set_position('active');
          if (isset($this_robot->robot_quotes['battle_start'])){
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_robot->print_quote('battle_start', $this_find, $this_replace);
          }
          $this_battle->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_target' => false, 'console_show_target' => false));

          // Create an event for this robot teleporting in
          if ($this_player->counters['robots_active'] == 1){
            $this_robot->set_frame('taunt');
            $this_battle->events_create(false, false, '', '');
          }
          $this_robot->set_frame('base');
          $this_robot->set_frame_styles('');
          $this_robot->set_detail_styles('');

        }

        // Show the player's other robots one by one
        $temp_robots_active = $this_player->get_robots_active();
        foreach ($temp_robots_active AS $key => $temp_robot){
          $frame_styles = $temp_robot->get_frame_styles();
          if (!preg_match('/display:\s?none;/i', $frame_styles)){ continue; }
          $temp_robot->set_frame('taunt');
          $temp_robot->set_frame_styles('');
          $temp_robot->set_detail_styles('');
          $this_battle->events_create(false, false, '', '');
          $temp_robot->set_frame('base');
        }

        // Ensure this robot has abilities to loop through
        if (!$this_robot->has_flag('ability_startup') && $this_robot->has_abilities()){
          // Loop through each of this robot's abilities and trigger the start event
          $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
          $temp_robot_abilities = $this_robot->get_abilities();
          foreach ($temp_robot_abilities AS $temp_key => $temp_token){
            // Define the current ability object using the loaded ability data
            $temp_info = array('ability_id' => $temp_key, 'ability_token' => $temp_token);
            $temp_ability = new rpg_ability($this_player, $this_robot, $temp_info);
          }
          // And now update the robot with the flag
          $this_robot->set_flag('ability_startup', true);
        }

        // Set this token to the ID and token of the starting robot
        $action_token = $this_robot->robot_id.'_'.$this_robot->robot_token;

        // Return from the battle function with the start results
        $this_return = true;
        break;

      }
      // Else if the player has chosen to use an ability
      elseif ($action_type == 'ability'){

        // Combine into the actions index
        $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

        // DEFINE ABILITY TOKEN

        // If an ability token was not collected
        if (empty($action_token)){
          // Collect the ability choice from the robot
          $temp_token = $this_robot->robot_choices_abilities($target_player, $target_robot);
          $temp_id = array_search($temp_token, $this_robot->robot_abilities);
          if (empty($temp_id)){ $temp_id = $this_battle->index['abilities'][$temp_token]['ability_id']; }
          $this_info = rpg_ability::parse_index_info($temp_abilities_index[$temp_token]);
          $this_info['ability_id'] = $temp_id;
        }
        // Otherwise, parse the token for data
        else {
          // Define the ability choice data for this robot
          list($temp_id, $temp_token) = explode('_', $action_token);
          $this_info = rpg_ability::parse_index_info($temp_abilities_index[$temp_token]);
          $this_info['ability_id'] = $temp_id;
        }

        // If the current robot has been already disabled
        if ($this_robot->robot_status == 'disabled'){
          // Break from this queued action as the robot cannot fight
          break;
        }

        // Define the current ability object using the loaded ability data
        $this_ability = new rpg_ability($this_player, $this_robot, $this_info);
        // Trigger this robot's ability
        $ability_results = $this_robot->trigger_ability($target_player, $target_robot, $this_ability);
        $this_ability->set_results($ability_results);

        // Ensure the battle has not completed before triggering the taunt event
        if ($this_battle->battle_status != 'complete'){
          // Check to ensure this robot hasn't taunted already
          if (!isset($this_robot->flags['robot_quotes']['battle_taunt'])
            && isset($this_robot->robot_quotes['battle_taunt'])
            && $this_robot->robot_quotes['battle_taunt'] != '...'
            && $this_ability->ability_results['this_amount'] > 0
            && $target_robot->robot_status != 'disabled'
            && $this_battle->critical_chance(3)){
            // Generate this robot's taunt event after dealing damage, which only happens once per battle
            $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_taunt']);
            $event_body = ($this_player->player_token != 'player' ? $this_player->print_name().'&#39;s ' : '').$this_robot->print_name().' taunts the opponent!<br />';
            $event_body .= $this_robot->print_quote('battle_taunt', $this_find, $this_replace);
            //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
            $this_robot->set_frame('taunt');
            $target_robot->set_frame('base');
            $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, array('console_show_target' => false));
            $this_robot->set_frame('base');
            // Create the quote flag to ensure robots don't repeat themselves
            $this_robot->set_flag('robot_quotes', 'battle_taunt', true);
          }

        }

        // Set this token to the ID and token of the triggered ability
        $action_token = $action_token['ability_id'].'_'.$action_token['ability_token'];

        // Return from the battle function with the used ability
        $this_return = &$this_ability;
        break;

      }
      // Else if the player has chosen to switch
      elseif ($action_type == 'switch'){

        // Collect this player's last action if it exists
        if (!empty($this_player->history['actions'])){
          $this_recent_switches = array_slice($this_player->history['actions'], -5, 5, false);
          foreach ($this_recent_switches AS $key => $info){
            if ($info['this_action'] == 'switch' || $info['this_action'] == 'start'){ $this_recent_switches[$key] = $info['this_action_token']; } //$info['this_action_token'];
            else { unset($this_recent_switches[$key]); }
          }
          $this_recent_switches = array_values($this_recent_switches);
          $this_recent_switches_count = count($this_recent_switches);
        }
        // Otherwise define an empty action
        else {
          $this_recent_switches = array();
          $this_recent_switches_count = 0;
        }

        // If the robot token was not collected and this player is NOT on autopilot
        if (empty($action_token) && $this_player->player_side == 'left'){
          // Clear any pending actions
          $this_battle->actions_empty();
          // Return from the battle function
          $this_return = true;
          break;
        }
        // Else If a robot token was not collected and this player IS on autopilot
        elseif (empty($action_token) && $this_player->player_side == 'right'){
          // Decide which robot the target should use (random)
          $active_robot_count = count($this_player->values['robots_active']);
          if ($active_robot_count == 1){
            $this_robotinfo = $this_player->values['robots_active'][0];
          }
          elseif ($active_robot_count > 1) {
            $this_current_token = $this_robot->robot_id.'_'.$this_robot->robot_token;
            do {
              $this_robotinfo = $this_player->values['robots_active'][mt_rand(0, ($active_robot_count - 1))];
              if ($this_robotinfo['robot_id'] == $this_robot->robot_id ){ continue; }
              $this_temp_token = $this_robotinfo['robot_id'].'_'.$this_robotinfo['robot_token'];
            } while(empty($this_temp_token));
          }
          else {
            $this_robotinfo = array('robot_id' => 0, 'robot_token' => 'robot');
          }
          //$this_battle->events_create(false, false, 'DEBUG', 'auto switch picked ['.print_r($this_robotinfo['robot_name'], true).'] | recent : ['.preg_replace('#\s+#', ' ', print_r($this_recent_switches, true)).']');
        }
        // Otherwise, parse the token for data
        else {
          list($temp_id, $temp_token) = explode('_', $action_token);
          $this_robotinfo = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
        }

        //$this_battle->events_create(false, false, 'DEBUG', 'switch picked ['.print_r($this_robotinfo['robot_token'], true).'] | other : []');

        // Update this player and robot's session data before switching
        $this_player->update_session();
        $this_robot->update_session();

        // Define the switch reason based on if this robot is disabled
        $this_switch_reason = $this_robot->robot_status != 'disabled' ? 'withdrawn' : 'removed';

        /*
        $this_battle->events_create(false, false, 'DEBUG',
        	'$this_switch_reason = '.$this_switch_reason.'<br />'.
          '$this_player->values[\'current_robot\'] = '.$this_player->values['current_robot'].'<br />'.
          '$this_player->values[\'current_robot_enter\'] = '.$this_player->values['current_robot_enter'].'<br />'.
          '');
        */

        // If this robot is being withdrawn on the same turn it entered, return false
        if ($this_player->player_side == 'right' && $this_switch_reason == 'withdrawn' && $this_player->values['current_robot_enter'] == $this_battle->counters['battle_turn']){
          // Return false to cancel the switch action
          $this_return = false;
          break;
        }

        // If the switch reason was removal, make sure this robot stays hidden
        if ($this_switch_reason == 'removed' && $this_player->player_side == 'right'){
          $this_robot->flags['hidden'] = true;
          $this_robot->update_session();
        }

        // Withdraw the player's robot and display an event for it
        if ($this_robot->robot_position != 'bench'){
          $this_robot->robot_frame = $this_robot->robot_status != 'disabled' ? 'base' : 'defeat';
          $this_robot->robot_position = 'bench';
          $this_player->set_frame('base');
          $this_player->set_value('current_robot', false);
          $this_player->set_value('current_robot_enter', false);
          $this_robot->update_session();
          $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
          $event_body = $this_robot->print_name().' is '.$this_switch_reason.' from battle!';
          if ($this_robot->robot_status != 'disabled' && isset($this_robot->robot_quotes['battle_retreat'])){
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_robot->print_quote('battle_retreat', $this_find, $this_replace);
          }
          // Only show the removed event or the withdraw event if there's more than one robot
          if ($this_switch_reason == 'removed' || $this_player->counters['robots_active'] > 1){
            $this_battle->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_disabled_bench' => $this_robot->robot_id.'_'.$this_robot->robot_token));
          }
          $this_robot->update_session();
        }

        // If the switch reason was removal, hide the robot from view
        if ($this_switch_reason == 'removed'){
          $this_robot->flags['hidden'] = true;
          $this_robot->update_session();
        }

        // Ensure all robots have been withdrawn to the bench at this point
        if (!empty($this_player->player_robots)){
          foreach ($this_player->player_robots AS $temp_key => $temp_robotinfo){
            $temp_robot = new rpg_robot($this_player, $temp_robotinfo);
            $temp_robot->robot_position = 'bench';
            $temp_robot->update_session();
          }
        }

        // Switch in the player's new robot and display an event for it
        $this_robot->robot_load($this_robotinfo);
        if ($this_robot->robot_position != 'active'){
          $this_robot->robot_position = 'active';
          $this_player->set_frame('command');
          $this_player->set_value('current_robot', $this_robot->robot_string);
          $this_player->set_value('current_robot_enter', $this_battle->counters['battle_turn']);
          $this_robot->update_session();
          $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
          $event_body = "{$this_robot->print_name()} joins the battle!<br />";
          if (isset($this_robot->robot_quotes['battle_start'])){
            $this_robot->robot_frame = 'taunt';
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_robot->print_quote('battle_start', $this_find, $this_replace);
          }
          // Only show the enter event if the switch reason was removed or if there is more then one robot
          if ($this_switch_reason == 'removed' || $this_player->counters['robots_active'] > 1){
            $this_battle->events_create($this_robot, false, $event_header, $event_body);
          }
        }

        // Ensure this robot has abilities to loop through
        if (!isset($this_robot->flags['ability_startup']) && !empty($this_robot->robot_abilities)){
          // Loop through each of this robot's abilities and trigger the start event
          $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
          foreach ($this_robot->robot_abilities AS $key => $token){
            if (!isset($temp_abilities_index[$token])){ continue; }
            // Define the current ability object using the loaded ability data
            $temp_info = array('ability_id' => $key, 'ability_token' => $token);
            $temp_ability = new rpg_ability($this_player, $this_robot, $temp_info);
          }
          // And now update the robot with the flag
          $this_robot->flags['ability_startup'] = true;
          $this_robot->update_session();
        }

        // Now we can update the current robot's frame regardless of what happened
        $this_robot->robot_frame = $this_robot->robot_status != 'disabled' ? 'base' : 'defeat';
        $this_robot->update_session();

        // Set this token to the ID and token of the switched robot
        $action_token = $this_robotinfo['robot_id'].'_'.$this_robotinfo['robot_token'];

        //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint ['.$action_token.'] | other : []');

        // Return from the battle function
        $this_return = true;
        break;
      }
      // Else if the player has chosen to scan the target
      elseif ($action_type == 'scan'){

        // Otherwise, parse the token for data
        if (!empty($action_token)){
          list($temp_id, $temp_token) = explode('_', $action_token);
          $scan_info = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
        }

        // If an ability token was not collected
        if (empty($scan_info)){
          // Decide which robot should be scanned
          foreach ($target_player->player_robots AS $this_key => $this_robotinfo){
            if ($this_robotinfo['robot_position'] == 'active'){ $scan_info = $this_robotinfo;  }
          }
        }

        // Create the temporary target player and robot objects
        $temp_target_player = $this_battle->get_player($temp_target_robot_info['player_id']);
        $temp_target_robot = $this_battle->get_robot($scan_info['robot_id']);

        // Change the target robot's frame is set to taunt
        $temp_target_robot->set_frame('taunt');

        // Generate the event header for this scan action
        $event_header = ($temp_target_player->player_token != 'player' ? $temp_target_player->player_name.'&#39;s ' : '').$temp_target_robot->robot_name;
        if (!rpg_game::robot_scanned($temp_target_robot->robot_token)){ $event_header .= ' (New!)'; }

        // Generate the event body for this scan action
        //$event_body = rpg_markup::robot_scan_markup($this_battle, $this_field, $temp_target_player, $temp_target_robot);
        $event_body = $temp_target_robot->print_scan_markup();

        // Create an event showing the scanned robot's data
        $this_battle->events_create($temp_target_robot, false, $event_header, $event_body, array('console_container_height' => 2, 'canvas_show_this' => false)); //, 'event_flag_autoplay' => false

        // Ensure the target robot's frame is reset to base
        $temp_target_robot->set_frame('base');

        // Add this robot to the global robot database array
        rpg_game::scan_robot($temp_target_robot->robot_token);

        // Set this token to the ID and token of the triggered ability
        $action_token = $action_token['robot_id'].'_'.$action_token['robot_token'];

        // Return from the battle function with the scanned robot
        $this_return = &$this_ability;
        break;

      }

      // Break out of the battle loop by default
      break;
    }

    // Set the hidden flag on this robot if necessary
    if ($this_robot->robot_position == 'bench' && ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1)){
      $this_robot->flags['apply_disabled_state'] = true;
      $this_robot->flags['hidden'] = true;
      $this_robot->update_session();
    }

    // Set the hidden flag on the target robot if necessary
    if ($target_robot->robot_position == 'bench' && ($target_robot->robot_status == 'disabled' || $target_robot->robot_energy < 1)){
      $target_robot->flags['apply_disabled_state'] = true;
      $target_robot->flags['hidden'] = true;
      $target_robot->update_session();
    }

    // If the target player does not have any robots left
    if ($target_player->counters['robots_active'] == 0){

      // Trigger the battle complete action to update status and result
      $this_battle->trigger_complete($this_player, $this_robot, $target_player, $target_robot);

    }

    // Update this player's history object with this action
    $this_player->history['actions'][] = array(
        'action_type' => $action_type,
        'action_token' => $action_token
        );

    // Return the result for this battle function
    return $this_return;
  }

  /**
   * Trigger the battle complete action and end the current mission
   * @param rpg_player this_player
   * @param rpg_robot this_robot
   * @param rpg_player target_player
   * @param rpg_robot target_robot
   */
  public function trigger_complete(rpg_player $this_player, rpg_robot $this_robot, rpg_player $target_player, rpg_robot $target_robot){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // Default the return variable to false
    $this_return = false;

    //$this_battle->events_create(false, false, 'DEBUG', 'Battle complete trigger triggered!');

    // Return false if anything is missing
    if (empty($this_player) || empty($this_robot)){ return false; }
    if (empty($target_player) || empty($target_robot)){ return false; }

    // Return true if the battle status is already complete
    if ($this_battle->battle_status == 'complete'){ return true; }

    // Update the battle status to complete
    $this_battle->set_info('battle_status', 'complete');
    if ($this_battle->battle_result == 'pending'){
      $this_battle->set_info('battle_result', ($target_player->player_side == 'right' ? 'victory' : 'defeat'));
      $event_options = array();
      if ($this_battle->battle_result == 'victory'){
        $event_options['event_flag_victory'] = true;
      }
      elseif ($this_battle->battle_result == 'defeat'){
        $event_options['event_flag_defeat'] = true;
      }
      $this_battle->events_create(false, false, '', '', $event_options);
    }

    // -- CALCULATE REWARDS -- //

    // Define variables for the human's rewards in this scenario
    $temp_human_token = $target_player->player_side == 'left' ? $target_player->player_token : $this_player->player_token;
    $temp_human_info = $target_player->player_side == 'left' ? $target_player->export_array() : $this_player->export_array();
    $temp_human_rewards = array();
    $temp_human_rewards['battle_points'] = 0;
    $temp_human_rewards['battle_zenny'] = 0;
    $temp_human_rewards['battle_complete'] = isset($_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this_battle->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this_battle->battle_token]['battle_count'] : 0;
    $temp_human_rewards['battle_failure'] = isset($_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this_battle->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this_battle->battle_token]['battle_count'] : 0;
    $temp_human_rewards['checkpoint'] = 'start: ';

    // Calculate the base point and zenny rewards for this battle
    $temp_reward_points_base = !empty($this_battle->battle_rewards_points) ? $this_battle->battle_rewards_points : 0;
    $temp_reward_zenny_base = !empty($this_battle->battle_rewards_zenny) ? $this_battle->battle_rewards_zenny : 0;

    // Default the bonus to zero and calulate based on turns
    $temp_turn_bonus = 0;
    if ($this_battle->counters['battle_turn'] < $this_battle->battle_turns_limit){ $temp_turn_bonus = round(($this_battle->battle_turns_limit - $this_battle->counters['battle_turn']) * 10); }
    elseif ($this_battle->counters['battle_turn'] > $this_battle->battle_turns_limit){ $temp_turn_bonus = round(($this_battle->counters['battle_turn'] - $this_battle->battle_turns_limit) * 10) * -1; }

    // Default the bonus to zero and calulate based on turns
    $temp_robot_bonus = 0;
    if ($temp_human_info['counters']['robots_masters_total'] < $this_battle->battle_robots_limit){ $temp_robot_bonus = round(($this_battle->battle_robots_limit - $temp_human_info['counters']['robots_masters_total']) * 10); }
    elseif ($temp_human_info['counters']['robots_masters_total'] > $this_battle->battle_robots_limit){ $temp_robot_bonus = $temp_robot_bonus = round(($temp_human_info['counters']['robots_masters_total'] - $this_battle->battle_robots_limit) * 10) * -1; }

    // Calculate the bonus points and zenny for the turns
    $temp_turn_bonus_points = (int)($temp_reward_points_base * ($temp_turn_bonus / 100));
    $temp_turn_bonus_zenny = (int)($temp_reward_zenny_base * ($temp_turn_bonus / 100));

    // Calculate the bonus points and zenny for the turns
    $temp_robot_bonus_points = (int)($temp_reward_points_base * ($temp_robot_bonus / 100));
    $temp_robot_bonus_zenny = (int)($temp_reward_zenny_base * ($temp_robot_bonus / 100));

    // Calculate the final reward points based on above
    if ($this_battle->battle_result == 'victory'){
      $temp_reward_points_final = $temp_reward_points_base + $temp_turn_bonus_points + $temp_robot_bonus_points;
      $temp_reward_zenny_final = $temp_reward_zenny_base + $temp_turn_bonus_zenny + $temp_robot_bonus_zenny;
      if ($temp_reward_points_final < 0){ $temp_reward_points_final = 0; }
      if ($temp_reward_zenny_final < 0){ $temp_reward_zenny_final = 0; }
    } else {
      $temp_reward_points_final = 0;
      $temp_reward_zenny_final = 0;
    }

    // Define the number of stars to show for this mission
    $temp_rating_stars = 0;
    if ($this_battle->battle_result == 'victory'){
      $temp_rating_stars += 1;
      if ($temp_turn_bonus >= 0){ $temp_rating_stars += 1; }
      if ($temp_robot_bonus >= 0){ $temp_rating_stars += 1; }
      if (empty($temp_human_info['counters']['robots_disabled'])){ $temp_rating_stars += 1; }
      if (empty($temp_human_info['counters']['items_used_this_battle'])){ $temp_rating_stars += 1; }
    }
    // Generate the markup for this stars
    $temp_rating_stars_markup = '';
    for ($i = 1; $i <= 5; $i++){ $temp_rating_stars_markup .= $i <= $temp_rating_stars ? '&#9733;' : '&#9734;'; }


    // (HUMAN) TARGET DEFEATED
    // Check if the target was the human character
    if ($target_player->player_side == 'left'){

      // Increment the main game's points total with the battle points
      $_SESSION['GAME']['counters']['battle_points'] += $temp_reward_points_final;
      $_SESSION['GAME']['counters']['battle_zenny'] += $temp_reward_zenny_final;

      // Increment this player's points total with the battle points
      if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'] = 0; }
      if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_zenny'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_zenny'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'] += $temp_reward_points_final;
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_zenny'] += $temp_reward_zenny_final;

      // Update the global variable with the points reward
      $temp_human_rewards['battle_points'] = $temp_reward_points_final;
      $temp_human_rewards['battle_zenny'] = $temp_reward_zenny_final;

      // Update the GAME session variable with the failed battle token
      $save_records = $this_battle->has_flag('save_records') ? $this_battle->get_flag('save_records') : true;
      if ($save_records){
        $bak_session_array = isset($_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this_battle->battle_token]) ? $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this_battle->battle_token] : array();
        $new_session_array = array('battle_token' => $this_battle->battle_token, 'battle_count' => 0, 'battle_level' => 0);
        if (!empty($bak_session_array['battle_count'])){ $new_session_array['battle_count'] = $bak_session_array['battle_count']; }
        if (!empty($bak_session_array['battle_level'])){ $new_session_array['battle_level'] = $bak_session_array['battle_level']; }
        $new_session_array['battle_level'] = $this_battle->battle_level;
        $new_session_array['battle_count']++;
        $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this_battle->battle_token] = $new_session_array;
        $temp_human_rewards['battle_failure'] = $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this_battle->battle_token]['battle_count'];
      }

    }


    // NON-INVISIBLE PLAYER DEFEATED
    // Display the defeat message for the target character if not default/hidden
    if ($target_player->player_token != 'player'){

      // (HUMAN) TARGET DEFEATED BY (INVISIBLE/COMPUTER)
      // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
      if ($this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left' && $this_robot->robot_class != 'mecha'){

        // Calculate how many points the other player is rewarded for winning
        $target_player_robots = $target_player->values['robots_disabled'];
        $target_player_robots_count = count($target_player_robots);
        $other_player_points = 0;
        $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
        foreach ($target_player_robots AS $disabled_robotinfo){ $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER; }

        // Create the victory event for the target player
        $this_robot->robot_frame = 'victory';
        $this_robot->update_session();
        $event_header = $this_robot->robot_name.' Undefeated';
        $event_body = '';
        $event_body .= $this_robot->print_name().' could not be defeated! ';
        $event_body .= '<br />';
        $event_options = array();
        $event_options['console_show_this_robot'] = true;
        $event_options['console_show_target'] = false;
        $event_options['event_flag_defeat'] = true;
        $event_options['this_header_float'] = $event_options['this_body_float'] = $this_robot->player->player_side;
        if ($this_robot->robot_token != 'robot'
          && isset($this_robot->robot_quotes['battle_victory'])){
          $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
          $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
          $event_body .= $this_robot->print_quote('battle_victory', $this_find, $this_replace);
        }
        $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

      }

      $target_player->set_frame('defeat');
      $target_robot->update_session();
      $event_header = $target_player->player_name.' Defeated';
      $event_body = $target_player->print_name().' was defeated'.($target_player->player_side == 'left' ? '&hellip;' : '!').' ';
      $event_body .= '<br />';
      $event_options = array();
      $event_options['console_show_this_player'] = true;
      $event_options['console_show_target'] = false;
      $event_options['event_flag_defeat'] = true;
      $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
      if ($target_player->player_token != 'player'
        && isset($target_player->player_quotes['battle_defeat'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $target_robot->robot_name);
        $this_quote_text = str_replace($this_find, $this_replace, $target_player->player_quotes['battle_defeat']);
        $event_body .= $target_player->print_quote('battle_defeat', $this_find, $this_replace);
      }
      $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

      // (HUMAN) TARGET DEFEATED BY (GHOST/COMPUTER)
      // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
      if ($this_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left'){

        // Calculate how many points the other player is rewarded for winning
        $target_player_robots = $target_player->values['robots_disabled'];
        $target_player_robots_count = count($target_player_robots);
        $other_player_points = 0;
        $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
        foreach ($target_player_robots AS $disabled_robotinfo){ $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER; }

        // Create the victory event for the target player
        $this_player->set_frame('victory');
        $target_robot->update_session();
        $event_header = $this_player->player_name.' Victorious';
        $event_body = $this_player->print_name().' was victorious! ';
        $event_body .= $this_player->print_name().' could not be defeated!';
        $event_body .= '<br />';
        $event_options = array();
        $event_options['console_show_this_player'] = true;
        $event_options['console_show_target'] = false;
        $event_options['event_flag_defeat'] = true;
        $event_options['this_header_float'] = $event_options['this_body_float'] = $this_player->player_side;
        if ($this_player->player_token != 'player'
          && isset($this_player->player_quotes['battle_victory'])){
          $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
          $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
          $event_body .= $this_player->print_quote('battle_victory', $this_find, $this_replace);
        }
        $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

      }

    }


    // (HUMAN) TARGET DEFEATED BY (COMPUTER)
    // Check if the target was the human character (and they LOST)
    if ($target_player->player_side == 'left'){

        // Collect the robot info array
        $temp_player_info = $target_player->export_array();

        // Collect or define the player points and player rewards variables
        $temp_player_token = $temp_player_info['player_token'];
        $temp_player_points = rpg_game::player_points($temp_player_info['player_token']);
        $temp_player_rewards = rpg_game::player_rewards($temp_player_info['player_token']); //!empty($temp_player_info['player_rewards']) ? $temp_player_info['player_rewards'] : array();

        // -- ABILITY REWARDS for HUMAN PLAYER -- //

        // Loop through the ability rewards for this robot if set
        if (!empty($temp_player_rewards['abilities']) && rpg_game::is_user()){
          $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
          foreach ($temp_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

            // If this ability is already unlocked, continue
            if (rpg_game::ability_unlocked($target_player->player_token, false, $ability_reward_info['token'])){ continue; }

            // Check if the required level has been met by this robot
            if ($temp_player_points >= $ability_reward_info['points'] && rpg_game::is_user()){

              // Collect the ability info from the index
              $ability_info = array('ability_id' => (MMRPG_SETTINGS_BATTLEABILITIES_PERROBOT_MAX + $ability_reward_key), 'ability_token' => $ability_reward_info['token']);
              // Create the temporary ability object for event creation
              $temp_ability = new rpg_ability($target_player, $target_robot, $ability_info);

              // Collect or define the ability variables
              $temp_ability_token = $ability_info['ability_token'];

              // Display the robot reward message markup
              $event_header = $temp_ability->ability_name.' Unlocked';
              $event_body = rpg_functions::get_random_positive_word().' <span class="player_name">'.$temp_player_info['player_name'].'</span> unlocked new ability data!<br />';
              $event_body .= '<span class="ability_name">'.$temp_ability->ability_name.'</span> can now be used in battle!';
              $event_options = array();
              $event_options['console_show_target'] = false;
              $event_options['this_header_float'] = $target_player->player_side;
              $event_options['this_body_float'] = $target_player->player_side;
              $event_options['this_ability'] = $temp_ability;
              $event_options['this_ability_image'] = 'icon';
              $event_options['event_flag_victory'] = true;
              $event_options['console_show_this_player'] = false;
              $event_options['console_show_this_robot'] = false;
              $event_options['console_show_this_ability'] = true;
              $event_options['canvas_show_this_ability'] = false;
              $target_player->set_frame(($ability_reward_key % 2 == 0 ? 'victory' : 'taunt'));
              $temp_ability->ability_frame = 'base';
              $temp_ability->update_session();
              $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

              // Automatically unlock this ability for use in battle
              $this_reward = array('ability_token' => $temp_ability_token);
              $show_event = !rpg_game::ability_unlocked('', '', $temp_ability_token) ? true : false;
              rpg_game::unlock_ability($temp_player_info, false, $this_reward, $show_event);

            }

          }
        }


    }

    // (COMPUTER) TARGET DEFEATED BY (HUMAN)
    // Check if this player was the human player (and they WON)
    if ($this_player->player_side == 'left'){

      // Increment the main game's points total with the battle points
      $_SESSION['GAME']['counters']['battle_points'] += $temp_reward_points_final;
      $_SESSION['GAME']['counters']['battle_zenny'] += $temp_reward_zenny_final;

      // Reference the number of points this player gets
      $this_player_points = $temp_reward_points_final;
      $this_player_zenny = $temp_reward_zenny_final;

      // Increment this player's points total with the battle points
      $player_token = $this_player->player_token;
      $player_info = $this_player->export_array();
      if (!isset($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'])){ $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'] += $this_player_points;
      if (!isset($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_zenny'])){ $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_zenny'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_zenny'] += $this_player_zenny;

      // Update the global variable with the points reward
      $temp_human_rewards['battle_points'] = $this_player_points;
      $temp_human_rewards['battle_zenny'] = $this_player_zenny;

      // Display the win message for this player with battle points
      $this_robot->robot_frame = 'victory';
      $this_player->set_frame('victory');
      $this_robot->update_session();
      $event_header = $this_player->player_name.' Victorious';
      $event_body = $this_player->print_name().' was victorious! ';
      $event_body .= 'The '.($target_player->counters['robots_disabled'] > 1 ? 'targets were' : 'target was').' defeated!';
      $event_body .= '<br />';
      $event_options = array();
      $event_options['console_show_this_player'] = true;
      $event_options['console_show_target'] = false;
      $event_options['event_flag_victory'] = true;
      $event_options['this_header_float'] = $event_options['this_body_float'] = $this_player->player_side;
      if ($this_player->player_token != 'player'
        && isset($this_player->player_quotes['battle_victory'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_player->print_quote('battle_victory', $this_find, $this_replace);
      }
      $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);


      /*
       * PLAYER REWARDS
       */

      // Check if the the player was a human character
      if ($this_player->player_side == 'left'){


        // Collect the robot info array
        $temp_player_info = $this_player->export_array();

        // Collect or define the player points and player rewards variables
        $temp_player_token = $temp_player_info['player_token'];
        $temp_player_points = rpg_game::player_points($temp_player_info['player_token']);
        $temp_player_rewards = !empty($temp_player_info['player_rewards']) ? $temp_player_info['player_rewards'] : array();

        // -- ABILITY REWARDS for HUMAN PLAYER -- //

        // Loop through the ability rewards for this player if set
        if (!empty($temp_player_rewards['abilities']) && rpg_game::is_user()){
          $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
          foreach ($temp_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

            // If this ability is already unlocked, continue
            if (rpg_game::ability_unlocked($this_player->player_token, false, $ability_reward_info['token'])){ continue; }

            // Check if the required level has been met by this robot
            if ($temp_player_points >= $ability_reward_info['points']){

              // Collect the ability info from the index
              $ability_info = rpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
              // Create the temporary ability object for event creation
              $temp_ability = new rpg_ability($this_player, $this_robot, $ability_info);

              // Collect or define the ability variables
              $temp_ability_token = $ability_info['ability_token'];

              // Display the robot reward message markup
              $event_header = $ability_info['ability_name'].' Unlocked';
              $event_body = rpg_functions::get_random_positive_word().' <span class="player_name">'.$temp_player_info['player_name'].'</span> unlocked new ability data!<br />';
              $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
              $event_options = array();
              $event_options['console_show_target'] = false;
              $event_options['this_header_float'] = $this_player->player_side;
              $event_options['this_body_float'] = $this_player->player_side;
              $event_options['this_ability'] = $temp_ability;
              $event_options['this_ability_image'] = 'icon';
              $event_options['event_flag_victory'] = true;
              $event_options['console_show_this_player'] = false;
              $event_options['console_show_this_robot'] = false;
              $event_options['console_show_this_ability'] = true;
              $event_options['canvas_show_this_ability'] = false;
              $this_player->set_frame(($ability_reward_key % 2 == 0 ? 'victory' : 'taunt'));
              $this_robot->robot_frame = $ability_reward_key % 2 == 0 ? 'taunt' : 'base';
              $this_robot->update_session();
              $temp_ability->ability_frame = 'base';
              $temp_ability->update_session();
              $this_battle->events_create($this_robot, $this_robot, $event_header, $event_body, $event_options);

              // Automatically unlock this ability for use in battle
              $this_reward = array('ability_token' => $temp_ability_token);
              $show_event = !rpg_game::ability_unlocked('', '', $temp_ability_token) ? true : false;
              rpg_game::unlock_ability($temp_player_info, false, $this_reward, $show_event);

            }

          }
        }

      }

    }


    /*
     * BATTLE REWARDS
     */

    // Collect or define the player variables
    $this_player_token = $this_player->player_token;
    $this_player_info = $this_player->export_array();

    // Collect or define the target player variables
    $target_player_token = $target_player->player_token;
    $target_player_info = $target_player->export_array();

    // Check if this player was the human player
    if ($this_player->player_side == 'left'){

      // Update the GAME session variable with the completed battle token
      $save_records = $this_battle->has_flag('save_records') ? $this_battle->get_flag('save_records') : true;
      if ($save_records){
        // Back up the current session array for this battle complete counter
        $bak_session_array = isset($_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this_battle->battle_token]) ? $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this_battle->battle_token] : array();
        // Create the new session array from scratch to ensure all values exist
        $new_session_array = array(
          'battle_token' => $this_battle->battle_token,
          'battle_count' => 0,
          'battle_min_level' => 0,
          'battle_max_level' => 0,
          'battle_min_turns' => 0,
          'battle_max_turns' => 0,
          'battle_min_points' => 0,
          'battle_max_points' => 0,
          'battle_min_robots' => 0,
          'battle_max_robots' => 0
          );
        // Recollect applicable battle values from the backup session array
        if (!empty($bak_session_array['battle_count'])){ $new_session_array['battle_count'] = $bak_session_array['battle_count']; }
        if (!empty($bak_session_array['battle_level'])){ $new_session_array['battle_min_level'] = $bak_session_array['battle_level']; } // LEGACY
        if (!empty($bak_session_array['battle_min_level'])){ $new_session_array['battle_min_level'] = $bak_session_array['battle_min_level']; }
        if (!empty($bak_session_array['battle_max_level'])){ $new_session_array['battle_max_level'] = $bak_session_array['battle_max_level']; }
        if (!empty($bak_session_array['battle_min_turns'])){ $new_session_array['battle_min_turns'] = $bak_session_array['battle_min_turns']; }
        if (!empty($bak_session_array['battle_max_turns'])){ $new_session_array['battle_max_turns'] = $bak_session_array['battle_max_turns']; }
        if (!empty($bak_session_array['battle_min_points'])){ $new_session_array['battle_min_points'] = $bak_session_array['battle_min_points']; }
        if (!empty($bak_session_array['battle_max_points'])){ $new_session_array['battle_max_points'] = $bak_session_array['battle_max_points']; }
        if (!empty($bak_session_array['battle_min_robots'])){ $new_session_array['battle_min_robots'] = $bak_session_array['battle_min_robots']; }
        if (!empty($bak_session_array['battle_max_robots'])){ $new_session_array['battle_max_robots'] = $bak_session_array['battle_max_robots']; }
        // Update and/or increment the appropriate battle variables in the new array
        if ($new_session_array['battle_max_level'] == 0 || $this_battle->battle_level > $new_session_array['battle_max_level']){ $new_session_array['battle_max_level'] = $this_battle->battle_level; }
        if ($new_session_array['battle_min_level'] == 0 || $this_battle->battle_level < $new_session_array['battle_min_level']){ $new_session_array['battle_min_level'] = $this_battle->battle_level; }
        if ($new_session_array['battle_max_turns'] == 0 || $this_battle->counters['battle_turn'] > $new_session_array['battle_max_turns']){ $new_session_array['battle_max_turns'] = $this_battle->counters['battle_turn']; }
        if ($new_session_array['battle_min_turns'] == 0 || $this_battle->counters['battle_turn'] < $new_session_array['battle_min_turns']){ $new_session_array['battle_min_turns'] = $this_battle->counters['battle_turn']; }
        if ($new_session_array['battle_max_points'] == 0 || $temp_human_rewards['battle_points'] > $new_session_array['battle_max_points']){ $new_session_array['battle_max_points'] = $temp_human_rewards['battle_points']; }
        if ($new_session_array['battle_min_points'] == 0 || $temp_human_rewards['battle_points'] < $new_session_array['battle_min_points']){ $new_session_array['battle_min_points'] = $temp_human_rewards['battle_points']; }
        if ($new_session_array['battle_max_robots'] == 0 || $this_player->counters['robots_total'] > $new_session_array['battle_max_robots']){ $new_session_array['battle_max_robots'] = $this_player->counters['robots_total']; }
        if ($new_session_array['battle_min_robots'] == 0 || $this_player->counters['robots_total'] < $new_session_array['battle_min_robots']){ $new_session_array['battle_min_robots'] = $this_player->counters['robots_total']; }
        $new_session_array['battle_count']++;
        // Update the session variable for this player with the updated battle values
        $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this_battle->battle_token] = $new_session_array;
        $temp_human_rewards['battle_complete'] = $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this_battle->battle_token]['battle_count'];
      }

      // Refresh the player info array
      $this_player_info = $this_player->export_array();

      // ROBOT REWARDS

      // Loop through any robot rewards for this battle
      $this_robot_rewards = $this_battle->get_robot_rewards();
      if (!empty($this_robot_rewards) && rpg_game::is_user()){
        foreach ($this_robot_rewards AS $robot_reward_key => $robot_reward_info){

          // If this robot has already been unlocked by anyone, continue
          if (rpg_game::robot_unlocked(false, $robot_reward_info['token'])){ continue; }

          // Collect the robot info from the index
          $robot_info = rpg_robot::get_index_info($robot_reward_info['token']);
          // Search this player's base robots for the robot ID
          $robot_info['robot_id'] = 0;
          foreach ($this_player->player_base_robots AS $base_robot){
            if ($robot_info['robot_token'] == $base_robot['robot_token']){
              $robot_info['robot_id'] = $base_robot['robot_id'];
              break;
            }
          }
          // Create the temporary robot object for event creation
          $temp_robot = new rpg_robot($this_player, $robot_info);

          // Collect or define the robot points and robot rewards variables
          $this_robot_token = $robot_reward_info['token'];
          $this_robot_level = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
          $this_robot_experience = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
          $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

          // Automatically unlock this robot for use in battle
          $this_reward = $robot_info;
          $this_reward['robot_level'] = $this_robot_level;
          $this_reward['robot_experience'] = $this_robot_experience;
          rpg_game::unlock_robot($this_player_info, $this_reward, true, true);

        }
      }

      // ABILITY REWARDS

      // Loop through any ability rewards for this battle
      $this_ability_rewards = $this_battle->get_ability_rewards();
      if (!empty($this_ability_rewards) && rpg_game::is_user()){
        $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
        foreach ($this_ability_rewards AS $ability_reward_key => $ability_reward_info){

          // Collect the ability info from the index
          $ability_info = rpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
          // Create the temporary robot object for event creation
          $temp_ability = new rpg_ability($this_player, $this_robot, $ability_info);

          // Collect or define the robot points and robot rewards variables
          $this_ability_token = $ability_info['ability_token'];

          // Now loop through all active robots on this side of the field
          foreach ($this_player_info['values']['robots_active'] AS $temp_key => $temp_info){
            // If this robot is a mecha, skip it!
            if (!empty($temp_info['robot_class']) && $temp_info['robot_class'] == 'mecha'){ continue; }
            // Equip this ability to the robot is there was a match found
            if (rpg_robot::has_ability_compatibility($temp_info['robot_token'], $ability_info['ability_token'])){
              if (!isset( $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'] )){ $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'] = array(); }
              if (count($_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities']) < 8){ $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'][$ability_info['ability_token']] = array('ability_token' => $ability_info['ability_token']); }
            }
          }

          // If this ability has already been unlocked by the player, continue
          if (rpg_game::ability_unlocked($this_player_token, false, $ability_reward_info['token'])){ continue; }

          // Automatically unlock this ability for use in battle
          $this_reward = array('ability_token' => $this_ability_token);
          $show_event = !rpg_game::ability_unlocked('', '', $this_ability_token) ? true : false;
          rpg_game::unlock_ability($this_player_info, false, $this_reward, $show_event);

          // Display the robot reward message markup
          $event_header = $ability_info['ability_name'].' Unlocked';
          $event_body = rpg_functions::get_random_positive_word().' <span class="player_name">'.$this_player_info['player_name'].'</span> unlocked new ability data!<br />';
          $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
          $event_options = array();
          $event_options['console_show_target'] = false;
          $event_options['this_header_float'] = $this_player->player_side;
          $event_options['this_body_float'] = $this_player->player_side;
          $event_options['this_ability'] = $temp_ability;
          $event_options['this_ability_image'] = 'icon';
          $event_options['console_show_this_player'] = false;
          $event_options['console_show_this_robot'] = false;
          $event_options['console_show_this_ability'] = true;
          $event_options['canvas_show_this_ability'] = false;
          $this_player->set_frame('victory');
          $temp_ability->ability_frame = 'base';
          $temp_ability->update_session();
          $this_battle->events_create($this_robot, false, $event_header, $event_body, $event_options);

        }
      }




    } // end of BATTLE REWARDS

    // Check if there is a field star for this stage to collect
    if ($this_battle->battle_result == 'victory' && !empty($this_battle->values['field_star'])){

      // Collect the field star data for this battle
      $temp_field_star = $this_battle->values['field_star'];

      // Print out the event for collecting the new field star
      $temp_name_markup = '<span class="field_name field_type field_type_'.(!empty($temp_field_star['star_type']) ? $temp_field_star['star_type'] : 'none').(!empty($temp_field_star['star_type2']) ? '_'.$temp_field_star['star_type2'] : '').'">'.$temp_field_star['star_name'].' Star</span>';
      $temp_event_header = $this_player->player_name.'&#39;s '.ucfirst($temp_field_star['star_kind']).' Star';
      $temp_event_body = $this_player->print_name().' collected the '.$temp_name_markup.'!<br />';
      $temp_event_body .= 'The new '.ucfirst($temp_field_star['star_kind']).' Star amplifies your Starforce!';
      $temp_event_options = array();
      $temp_event_options['console_show_this_player'] = false;
      $temp_event_options['console_show_target_player'] = false;
      $temp_event_options['console_show_this_robot'] = false;
      $temp_event_options['console_show_target_robot'] = false;
      $temp_event_options['console_show_this_ability'] = false;
      $temp_event_options['console_show_this'] = true;
      $temp_event_options['console_show_this_star'] = true;
      $temp_event_options['this_header_float'] = $temp_event_options['this_body_float'] = $this_player->player_side;
      $temp_event_options['this_star'] = $temp_field_star;
      $temp_event_options['this_ability'] = false;
      $this_battle->events_create(false, false, $temp_event_header, $temp_event_body, $temp_event_options);

      // Update the session with this field star data
      $_SESSION['GAME']['values']['battle_stars'][$temp_field_star['star_token']] = $temp_field_star;

      // DEBUG DEBUG
      //$this_battle->events_create($this_robot, $target_robot, 'DEBUG FIELD STAR', 'You got a field star! The field star names '.implode(' | ', $temp_field_star));

    }

    // If this robot's image has been changed, reveert it back to what it was
    if ($this_robot->robot_core == 'copy'){
      if (isset($this_robot->robot_image_overlay['copy_type1'])){ unset($this_robot->robot_image_overlay['copy_type1']); }
      if (isset($this_robot->robot_image_overlay['copy_type2'])){ unset($this_robot->robot_image_overlay['copy_type2']); }
      $this_robot->update_session();
    }

    // If the target robot's image has been changed, reveert it back to what it was
    if ($target_robot->robot_core == 'copy'){
      if (isset($target_robot->robot_image_overlay['copy_type1'])){ unset($target_robot->robot_image_overlay['copy_type1']); }
      if (isset($target_robot->robot_image_overlay['copy_type2'])){ unset($target_robot->robot_image_overlay['copy_type2']); }
      $target_robot->update_session();
    }

    // Define the first event body markup, regardless of player type
    $first_event_header = $this_battle->battle_name.($this_battle->battle_result == 'victory' ? ' Complete' : ' Failure').' <span class="pipe">|</span> '.$this_battle->battle_field->field_name;
    if ($this_battle->battle_result == 'victory'){ $first_event_body = 'Mission complete! <span class="pipe">|</span> '.($temp_human_rewards['battle_complete'] > 1 ? rpg_functions::get_random_positive_word().' That&#39;s '.$temp_human_rewards['battle_complete'].' times now! ' : '').rpg_functions::get_random_victory_quote(); }
    elseif ($this_battle->battle_result == 'defeat'){ $first_event_body = 'Mission failure. <span class="pipe">|</span> '.($temp_human_rewards['battle_failure'] > 1 ? 'That&#39;s '.$temp_human_rewards['battle_failure'].' times now&hellip; ' : '').rpg_functions::get_random_defeat_quote(); }
    $first_event_body .= ' <span class="pipe">|</span> '.$temp_rating_stars_markup.'<br />';

    // Print out the table and markup for the battle
    $first_event_body .= '<table class="full">';
    $first_event_body .= '<colgroup><col width="30%" /><col width="15%" /><col width="15%" /><col width="20%" /><col width="20%" /></colgroup>';
    $first_event_body .= '<tbody>';

      $first_event_body .= '<tr>';
        $first_event_body .= '<td class="left">Base Values</td>';
        $first_event_body .= '<td class="center"></td>';
        $first_event_body .= '<td class="center"></td>';
        $first_event_body .= '<td class="right">'.($temp_reward_points_base == 1 ? '1 Point' : number_format($temp_reward_points_base, 0, '.', ',').' Points').'</td>';
        $first_event_body .= '<td class="right">'.($temp_reward_zenny_base == 1 ? '1 Zenny' : number_format($temp_reward_zenny_base, 0, '.', ',').' Zenny').'</td>';
      $first_event_body .= '</tr> ';

      // Only grant bonuses if there was a victory
      if ($this_battle->battle_result == 'victory'){

        // Print out the label and target vs actual turn stats
        $first_event_body .= '<tr>';

          $first_event_body .= '<td class="left">Target Turns</td>';
          $first_event_body .= '<td class="center">'.$this_battle->counters['battle_turn'].' &nbsp;/&nbsp; '.$this_battle->battle_turns_limit.'</td>';

          // Print the markup for the bonus/penalty percent
          if ($temp_turn_bonus > 0){ $first_event_body .= '<td class="center positive">+'.$temp_turn_bonus.'%</td>'; }
          elseif ($temp_turn_bonus < 0){ $first_event_body .= '<td class="center negative">'.$temp_turn_bonus.'%</td>'; }
          else { $first_event_body .= '<td class="center">+0%</td>'; }

          // Print out any mods to the points
          $markup = $temp_turn_bonus_points == 1 ? '1 Point' : number_format($temp_turn_bonus_points, 0, '.', ',').' Points';
          if ($temp_turn_bonus_points > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
          elseif ($temp_turn_bonus_points < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
          else { $first_event_body .= '<td class="right">-</td>'; }

          // Print out any mods to the zenny
          $markup = $temp_turn_bonus_zenny == 1 ? '1 Zenny' : number_format($temp_turn_bonus_zenny, 0, '.', ',').' Zenny';
          if ($temp_turn_bonus_zenny > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
          elseif ($temp_turn_bonus_zenny < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
          else { $first_event_body .= '<td class="right">-</td>'; }

        $first_event_body .= '</tr>';

        // Print out the label and target vs actual robot stats
        $first_event_body .= '<tr>';

          $first_event_body .= '<td class="left">Target Robots</td>';
          $first_event_body .= '<td class="center">'.$this_player_info['counters']['robots_masters_total'].' &nbsp;/&nbsp; '.$this_battle->battle_robots_limit.'</td>';

          // Print the markup for the bonus/penalty percent
          if ($temp_robot_bonus > 0){ $first_event_body .= '<td class="center positive">+'.$temp_robot_bonus.'%</td>'; }
          elseif ($temp_robot_bonus < 0){ $first_event_body .= '<td class="center negative">'.$temp_robot_bonus.'%</td>'; }
          else { $first_event_body .= '<td class="center">+0%</td>'; }

          // Print out any mods to the points
          $markup = $temp_robot_bonus_points == 1 ? '1 Point' : number_format($temp_robot_bonus_points, 0, '.', ',').' Points';
          if ($temp_robot_bonus_points > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
          elseif ($temp_robot_bonus_points < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
          else { $first_event_body .= '<td class="right">-</td>'; }

          // Print out any mods to the zenny
          $markup = $temp_robot_bonus_zenny == 1 ? '1 Zenny' : number_format($temp_robot_bonus_zenny, 0, '.', ',').' Zenny';
          if ($temp_robot_bonus_zenny > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
          elseif ($temp_robot_bonus_zenny < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
          else { $first_event_body .= '<td class="right">-</td>'; }

        $first_event_body .= '</tr>';

      }
      // Else if defeated show what they were missing
      elseif ($this_battle->battle_result == 'defeat'){

        // Print out the label and target vs actual turn stats
        $first_event_body .= '<tr>';

          $first_event_body .= '<td class="left">Target Turns</td>';
          $first_event_body .= '<td class="center">'.$this_battle->counters['battle_turn'].' &nbsp;/&nbsp; '.$this_battle->battle_turns_limit.'</td>';

          // Print the markup for the empty fields
          $first_event_body .= '<td class="center">-</td>';
          $first_event_body .= '<td class="right">-</td>';
          $first_event_body .= '<td class="right">-</td>';

        $first_event_body .= '</tr>';

        // Print out the label and target vs actual robot stats
        $first_event_body .= '<tr>';

          $first_event_body .= '<td class="left">Target Robots</td>';
          $first_event_body .= '<td class="center">'.$this_player_info['counters']['robots_masters_total'].' &nbsp;/&nbsp; '.$this_battle->battle_robots_limit.'</td>';

          // Print the markup for the empty fields
          $first_event_body .= '<td class="center">-</td>';
          $first_event_body .= '<td class="right">-</td>';
          $first_event_body .= '<td class="right">-</td>';

        $first_event_body .= '</tr>';

      }

      // Print out the final rewards for this battle
      $first_event_body .= '<tr>';
        $first_event_body .= '<td class="left"><strong>Final Rewards</strong></td>';
        $first_event_body .= '<td class="center"></td>';
        $first_event_body .= '<td class="center"></td>';
        $first_event_body .= '<td class="right"><strong>'.($temp_reward_points_final != 1 ? number_format($temp_reward_points_final, 0, '.', ',').' Points' : '1 Point').'</strong></td>';
        $first_event_body .= '<td class="right"><strong>'.($temp_reward_zenny_final != 1 ? number_format($temp_reward_zenny_final, 0, '.', ',').' Zenny' : '1 Zenny').'</strong></td>';
      $first_event_body .= '</tr>';

      // Finalize the table body for the results
    $first_event_body .= '</tbody>';
    $first_event_body .= '</table>';


    // Print the battle complete message
    $event_options = array();
    $event_options['this_header_float'] = 'center';
    $event_options['this_body_float'] = 'center';
    $event_options['this_event_class'] = false;
    $event_options['console_show_this'] = false;
    $event_options['console_show_target'] = false;
    $event_options['console_container_classes'] = 'field_type field_type_event field_type_'.($this_battle->battle_result == 'victory' ? 'nature' : 'flame');
    $this_battle->events_create($target_robot, $this_robot, $first_event_header, $first_event_body, $event_options);

    // Add the flag to prevent any further messages from appearing
    $this_battle->set_flag('battle_complete_message_created', true);

    // Return the result for this battle function
    return $this_return;
  }


  // -- EVENT FUNCTIONS -- //

  /**
   * Create a new debug entry in the global battle event queue
   * @param string $file_name
   * @param int $line_number
   * @param string $debug_message
   */
  public function events_debug($file_name, $line_number, $debug_message){
    if (MMRPG_CONFIG_DEBUG_MODE){
      $file_name = basename($file_name);
      $line_number = 'Line '.$line_number;
      $this->events_create(false, false, 'DEBUG | '.$file_name.' | '.$line_number, $debug_message);
    }
  }

  /**
   * Create a new entry in the global battle event queue
   * @param rpg_object $this_object
   * @param rpg_object $target_object
   * @param string $event_header (optional)
   * @param string $event_body (optional)
   * @param array $event_options (optional)
   */
  public function events_create($this_object = false, $target_object = false, $event_header = '', $event_body = '', $event_options = array()){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // Collect references to the player and robot objects
    $this_player = false;
    $this_robot = false;
    if (is_a($this_object, 'rpg_robot')){
      $this_player = $this_battle->get_player($this_object->player_id);
      $this_robot = $this_battle->get_robot($this_object->robot_id);
    } elseif (is_a($this_object, 'rpg_player')){
      $this_player = $this_battle->get_player($this_object->player_id);
    }

    // Collect references to the target player and robot objects
    $target_player = false;
    $target_robot = false;
    if (is_a($target_object, 'rpg_robot')){
      $target_player = $this_battle->get_player($target_object->player_id);
      $target_robot = $this_battle->get_robot($target_object->robot_id);
    } elseif (is_a($target_object, 'rpg_player')){
      $target_player = $this_battle->get_player($target_object->player_id);
    }

    // Increment the internal events counter
    $this_battle->inc_counter('events');

    // Generate the event markup and add it to the array
    $this_battle->events[] = $this_battle->generate_markup(array(
      'this_player' => $this_player,
      'this_robot' => $this_robot,
      'target_player' => $target_player,
      'target_robot' => $target_robot,
      'event_header' => $event_header,
      'event_body' => $event_body,
      'event_options' => $event_options
      ));

  }

  /**
   * Generate the console and canvas markup for the current frame of the battle
   * @param array $event_info
   * @return array
   */
  public function generate_markup($event_info){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // Define defaults for event options
    $options = array();
    $options['event_flag_autoplay'] = isset($event_info['event_options']['event_flag_autoplay']) ? $event_info['event_options']['event_flag_autoplay'] : true;
    $options['event_flag_victory'] = isset($event_info['event_options']['event_flag_victory']) ? $event_info['event_options']['event_flag_victory'] : false;
    $options['event_flag_defeat'] = isset($event_info['event_options']['event_flag_defeat']) ? $event_info['event_options']['event_flag_defeat'] : false;
    $options['console_container_height'] = isset($event_info['event_options']['console_container_height']) ? $event_info['event_options']['console_container_height'] : 1;
    $options['console_container_classes'] = isset($event_info['event_options']['console_container_classes']) ? $event_info['event_options']['console_container_classes'] : '';
    $options['console_container_styles'] = isset($event_info['event_options']['console_container_styles']) ? $event_info['event_options']['console_container_styles'] : '';
    $options['console_header_float'] = isset($event_info['event_options']['this_header_float']) ? $event_info['event_options']['this_header_float'] : '';
    $options['console_body_float'] = isset($event_info['event_options']['this_body_float']) ? $event_info['event_options']['this_body_float'] : '';
    $options['console_show_this'] = isset($event_info['event_options']['console_show_this']) ? $event_info['event_options']['console_show_this'] : true;
    $options['console_show_this_player'] = isset($event_info['event_options']['console_show_this_player']) ? $event_info['event_options']['console_show_this_player'] : false;
    $options['console_show_this_robot'] = isset($event_info['event_options']['console_show_this_robot']) ? $event_info['event_options']['console_show_this_robot'] : true;
    $options['console_show_this_ability'] = isset($event_info['event_options']['console_show_this_ability']) ? $event_info['event_options']['console_show_this_ability'] : false;
    $options['console_show_this_star'] = isset($event_info['event_options']['console_show_this_star']) ? $event_info['event_options']['console_show_this_star'] : false;
    $options['console_show_target'] = isset($event_info['event_options']['console_show_target']) ? $event_info['event_options']['console_show_target'] : true;
    $options['console_show_target_player'] = isset($event_info['event_options']['console_show_target_player']) ? $event_info['event_options']['console_show_target_player'] : true;
    $options['console_show_target_robot'] = isset($event_info['event_options']['console_show_target_robot']) ? $event_info['event_options']['console_show_target_robot'] : true;
    $options['console_show_target_ability'] = isset($event_info['event_options']['console_show_target_ability']) ? $event_info['event_options']['console_show_target_ability'] : true;
    $options['canvas_show_this'] = isset($event_info['event_options']['canvas_show_this']) ? $event_info['event_options']['canvas_show_this'] : true;
    $options['canvas_show_this_robots'] = isset($event_info['event_options']['canvas_show_this_robots']) ? $event_info['event_options']['canvas_show_this_robots'] : true;
    $options['canvas_show_this_ability'] = isset($event_info['event_options']['canvas_show_this_ability']) ? $event_info['event_options']['canvas_show_this_ability'] : true;
    $options['canvas_show_this_ability_overlay'] = isset($event_info['event_options']['canvas_show_this_ability_overlay']) ? $event_info['event_options']['canvas_show_this_ability_overlay'] : false;
    $options['canvas_show_target'] = isset($event_info['event_options']['canvas_show_target']) ? $event_info['event_options']['canvas_show_target'] : true;
    $options['canvas_show_target_robots'] = isset($event_info['event_options']['canvas_show_target_robots']) ? $event_info['event_options']['canvas_show_target_robots'] : true;
    $options['canvas_show_target_ability'] = isset($event_info['event_options']['canvas_show_target_ability']) ? $event_info['event_options']['canvas_show_target_ability'] : true;
    $options['canvas_show_ability_stats'] = isset($event_info['event_options']['canvas_show_ability_stats']) ? $event_info['event_options']['canvas_show_ability_stats'] : true;
    $options['this_ability'] = isset($event_info['event_options']['this_ability']) ? $event_info['event_options']['this_ability'] : false;
    $options['this_ability_target'] = isset($event_info['event_options']['this_ability_target']) ? $event_info['event_options']['this_ability_target'] : false;
    $options['this_ability_target_key'] = isset($event_info['event_options']['this_ability_target_key']) ? $event_info['event_options']['this_ability_target_key'] : 0;
    $options['this_ability_target_position'] = isset($event_info['event_options']['this_ability_target_position']) ? $event_info['event_options']['this_ability_target_position'] : 'active';
    $options['this_ability_results'] = isset($event_info['event_options']['this_ability_results']) ? $event_info['event_options']['this_ability_results'] : false;
    $options['this_star'] = isset($event_info['event_options']['this_star']) ? $event_info['event_options']['this_star'] : false;
    $options['this_player_image'] = isset($event_info['event_options']['this_player_image']) ? $event_info['event_options']['this_player_image'] : 'sprite';
    $options['this_robot_image'] = isset($event_info['event_options']['this_robot_image']) ? $event_info['event_options']['this_robot_image'] : 'sprite';
    $options['this_ability_image'] = isset($event_info['event_options']['this_ability_image']) ? $event_info['event_options']['this_ability_image'] : 'sprite';

    // Define the variable to collect markup
    $this_markup = array();

    // Generate the event flags markup
    $event_flags = array();
    $event_flags['autoplay'] = $options['event_flag_autoplay'];
    $event_flags['victory'] = $options['event_flag_victory'];
    $event_flags['defeat'] = $options['event_flag_defeat'];
    $this_markup['flags'] = json_encode($event_flags);

    // Generate the console message markup
    $this_markup['console'] = $this_battle->get_console_markup($event_info, $options);

    // Generate the canvas scene markup
    $this_markup['canvas'] = $this_battle->get_canvas_markup($event_info, $options);

    // Generate the jSON encoded event data markup
    $this_markup['data'] = array();
    $this_markup['data']['this_battle'] = '';
    $this_markup['data']['this_field'] = '';
    $this_markup['data']['this_player'] = '';
    $this_markup['data']['this_robot'] = '';
    $this_markup['data']['target_player'] = '';
    $this_markup['data']['target_robot'] = '';
    $this_markup['data'] = json_encode($this_markup['data']);

    // Increment the battle frames counter
    $this_battle->inc_counter('event_frames');

    // Return the generated event markup
    return $this_markup;

  }

  /**
   * Generate the canvas scene markup for this frame of battle including players, robots, abilities etc.
   * @param array $eventinfo
   * @param array $options (optional)
   * @return string
   */
  public function get_canvas_markup($eventinfo, $options = array()){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // Default the return markup to empty
    $this_markup = '';

    // If this robot was not provided or allowed by the function
    if (empty($eventinfo['this_player']) || empty($eventinfo['this_robot']) || $options['canvas_show_this'] == false){
      // Set both this player and robot to false
      $eventinfo['this_player'] = false;
      $eventinfo['this_robot'] = false;
      // Collect possible player IDs and figure out which is which
      $all_player_ids = $this_battle->get_player_ids();
      $target_player_id = !empty($eventinfo['target_player']) ? $eventinfo['target_player']->get_id() : 0;
      if (!empty($target_player_id)){
        $temp_player_id = array_shift($all_player_ids);
        $this_player_id = $temp_player_id == $target_player_id ? array_shift($all_player_ids) : $temp_player_id;
      } else {
        $target_player_id = array_shift($all_player_ids);
        $this_player_id = array_shift($all_player_ids);
      }
      // Recollect this player and robot info based on above
      if (!empty($this_player_id)){
        $eventinfo['this_player'] = $this_battle->get_player($this_player_id);
        $eventinfo['this_robot'] = $this_battle->find_robot(array('player_id' => $this_player_id, 'robot_position' => 'active', 'robot_status' => 'active'));
      }
    }

    // If this robot was targetting itself, set the target to false
    if (!empty($eventinfo['this_robot']) && !empty($eventinfo['target_robot'])){
      // If this and the target robot are the same, set target to false
      if ($eventinfo['this_robot']->robot_id == $eventinfo['target_robot']->robot_id){ $eventinfo['target_robot'] = false; }
      // If this and the target robot's ID is less than the prefedined computer target ID (same team), set target to false
      if ($eventinfo['this_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID){ $eventinfo['target_robot'] = false; }
      // If this and the target robot's ID is greater than the prefedined computer target ID (same team), set target to false
      if ($eventinfo['this_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID){ $eventinfo['target_robot'] = false; }
    }

    // If the target robot was not provided or allowed by the function
    if (empty($eventinfo['target_player']) || empty($eventinfo['target_robot']) || $options['canvas_show_target'] == false){
      // Set both this player and robot to false
      $eventinfo['target_player'] = false;
      $eventinfo['target_robot'] = false;
      // Collect possible player IDs and figure out which is which
      $all_player_ids = $this_battle->get_player_ids();
      $this_player_id = !empty($eventinfo['this_player']) ? $eventinfo['this_player']->get_id() : 0;
      if (!empty($this_player_id)){
        $temp_player_id = array_shift($all_player_ids);
        $target_player_id = $temp_player_id == $this_player_id ? array_shift($all_player_ids) : $temp_player_id;
      } else {
        $this_player_id = array_shift($all_player_ids);
        $target_player_id = array_shift($all_player_ids);
      }
      // Recollect the target player and robot info based on above
      if (!empty($target_player_id)){
        $eventinfo['target_player'] = $this_battle->get_player($target_player_id);
        $eventinfo['target_robot'] = $this_battle->find_robot(array('player_id' => $target_player_id, 'robot_position' => 'active', 'robot_status' => 'active'));
      }
    }

    // Collect this player's markup data
    $this_player_data = $eventinfo['this_player']->get_canvas_markup($options);
    // Append this player's markup to the main markup array
    $this_markup .= $this_player_data['player_markup'];

    // Loop through and display this player's robots
    if ($options['canvas_show_this_robots'] && !empty($eventinfo['this_player']->player_robots)){
      $num_player_robots = count($eventinfo['this_player']->player_robots);
      foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){
        $this_robot = new rpg_robot($eventinfo['this_player'], $this_robotinfo);
        $this_options = $options;
        //if ($this_robot->robot_status == 'disabled' && $this_robot->robot_position == 'bench'){ continue; }
        if (!empty($this_robot->flags['hidden'])){ continue; }
        elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id != $this_robot->robot_id){ $this_options['this_ability'] = false; }
        elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id == $this_robot->robot_id && $options['canvas_show_this'] != false){ $this_robot->robot_frame =  $eventinfo['this_robot']->robot_frame; }
        $this_robot->robot_key = $this_robot->robot_key !== false ? $this_robot->robot_key : ($this_key > 0 ? $this_key : $num_player_robots);
        $this_robot_data = $this_robot->get_canvas_markup($this_options, $this_player_data);
        $this_robot_id_token = $this_robot_data['robot_id'].'_'.$this_robot_data['robot_token'];

        // ABILITY OVERLAY STUFF
        if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){
          $this_markup .= '<div class="ability_overlay overlay1" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: '.(($this_robot_data['robot_position'] == 'active' ? 5052 : (4900 - ($this_robot_data['robot_key'] * 100)))).';">&nbsp;</div>';
        }
        elseif ($this_robot_data['robot_position'] != 'bench' && !empty($this_options['this_ability']) && !empty($options['canvas_show_this_ability'])){
          $this_markup .= '<div class="ability_overlay overlay2" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: '.(($this_options['this_ability_target_position'] == 'active' ? 5051 : (4900 - ($this_options['this_ability_target_key'] * 100)))).';">&nbsp;</div>';
        }
        elseif ($this_robot_data['robot_position'] != 'bench' && !empty($options['canvas_show_this_ability_overlay'])){
          $this_markup .= '<div class="ability_overlay overlay3" style="z-index: 100;">&nbsp;</div>';
        }

        // RESULTS ANIMATION STUFF
        if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){
          /*
           * ABILITY EFFECT OFFSETS
           * Frame 01 : Energy +
           * Frame 02 : Energy -
           * Frame 03 : Attack +
           * Frame 04 : Attack -
           * Frame 05 : Defense +
           * Frame 06 : Defense -
           * Frame 07 : Speed +
           * Frame 08 : Speed -
           */

          // Define the results data array and populate with basic fields
          $this_results_data = array();
          $this_results_data['results_amount_markup'] = '';
          $this_results_data['results_effect_markup'] = '';

          // Calculate the results effect canvas offsets
          $this_results_data['canvas_offset_x'] = ceil($this_robot_data['canvas_offset_x'] - (4 * $this_options['this_ability_results']['total_actions']));
          $this_results_data['canvas_offset_y'] = ceil($this_robot_data['canvas_offset_y'] + 0);
          $this_results_data['canvas_offset_z'] = ceil($this_robot_data['canvas_offset_z'] - 20);
          $temp_size_diff = $this_robot_data['robot_size'] > 80 ? ceil(($this_robot_data['robot_size'] - 80) * 0.5) : 0;
          $this_results_data['canvas_offset_x'] += $temp_size_diff;
          if ($this_robot_data['robot_position'] == 'bench' && $this_robot_data['robot_size'] >= 80){
            $this_results_data['canvas_offset_x'] += ceil($this_robot_data['robot_size'] / 2);
          }


          // Define the style and class variables for these results
          $base_image_size = 40;
          $this_results_data['ability_size'] = $this_robot_data['robot_position'] == 'active' ? ($base_image_size * 2) : $base_image_size;
          $this_results_data['ability_scale'] = isset($this_robot_data['robot_scale']) ? $this_robot_data['robot_scale'] : ($this_robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_robot_data['robot_key']) / 8) * 0.5));
          $zoom_size = $base_image_size * 2;
          $this_results_data['ability_sprite_size'] = ceil($this_results_data['ability_scale'] * $zoom_size);
          $this_results_data['ability_sprite_width'] = ceil($this_results_data['ability_scale'] * $zoom_size);
          $this_results_data['ability_sprite_height'] = ceil($this_results_data['ability_scale'] * $zoom_size);
          $this_results_data['ability_image_width'] = ceil($this_results_data['ability_scale'] * $zoom_size * 10);
          $this_results_data['ability_image_height'] = ceil($this_results_data['ability_scale'] * $zoom_size);
          $this_results_data['results_amount_class'] = 'sprite ';
          $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 50;
          $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] - 40;
          $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 100;
          if (!empty($this_options['this_ability_results']['total_actions'])){
            $total_actions = $this_options['this_ability_results']['total_actions'];
            if ($this_options['this_ability_results']['trigger_kind'] == 'damage'){
              $this_results_data['results_amount_canvas_offset_y'] -= ceil((1.5 * $total_actions) * $total_actions);
              $this_results_data['results_amount_canvas_offset_x'] -= $total_actions * 4;
            } elseif ($this_options['this_ability_results']['trigger_kind'] == 'recovery'){
              $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 20;
              $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] - 40;
              $this_results_data['results_amount_canvas_offset_y'] += ceil((1.5 * $total_actions) * $total_actions);
              $this_results_data['results_amount_canvas_offset_x'] -= $total_actions * 4;
            }
          }
          $this_results_data['results_amount_canvas_opacity'] = 1.00;
          if ($this_robot_data['robot_position'] == 'bench'){
            $this_results_data['results_amount_canvas_offset_x'] += 105; //$this_results_data['results_amount_canvas_offset_x'] * -1;
            $this_results_data['results_amount_canvas_offset_y'] += 5; //10;
            $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 1000;
            $this_results_data['results_amount_canvas_opacity'] -= 0.10;
          } else {
            $this_results_data['canvas_offset_x'] += mt_rand(0, 10); //jitter
            $this_results_data['canvas_offset_y'] += mt_rand(0, 10); //jitter
          }
          $this_results_data['results_amount_style'] = 'bottom: '.$this_results_data['results_amount_canvas_offset_y'].'px; '.$this_robot_data['robot_float'].': '.$this_results_data['results_amount_canvas_offset_x'].'px; z-index: '.$this_results_data['results_amount_canvas_offset_z'].'; opacity: '.$this_results_data['results_amount_canvas_opacity'].'; ';
          $this_results_data['results_effect_class'] = 'sprite sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].' ability_status_active ability_position_active '; //sprite_'.$this_robot_data['robot_size'].'x'.$this_robot_data['robot_size'].'
          $this_results_data['results_effect_style'] = 'z-index: '.$this_results_data['canvas_offset_z'].'; '.$this_robot_data['robot_float'].': '.$this_results_data['canvas_offset_x'].'px; bottom: '.$this_results_data['canvas_offset_y'].'px; background-image: url(images/abilities/ability-results/sprite_'.$this_robot_data['robot_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';

          // Ensure a damage/recovery trigger has been sent and actual damage/recovery was done
          if (!empty($this_options['this_ability_results']['this_amount'])
            && in_array($this_options['this_ability_results']['trigger_kind'], array('damage', 'recovery'))){
            // Define the results effect index
            $this_results_data['results_effect_index'] = array();
            // Check if the results effect index was already generated
            if (!empty($this_battle->index['results_effects'])){
              // Collect the results effect index from the battle index
              $this_results_data['results_effect_index'] = $this_battle->index['results_effects'];
            }
            // Otherwise, generate the results effect index
            else {
              // Define the results effect index for quick programatic lookups
              $this_results_data['results_effect_index']['recovery']['energy'] = '00';
              $this_results_data['results_effect_index']['damage']['energy'] = '01';
              $this_results_data['results_effect_index']['recovery']['attack'] = '02';
              $this_results_data['results_effect_index']['damage']['attack'] = '03';
              $this_results_data['results_effect_index']['recovery']['defense'] = '04';
              $this_results_data['results_effect_index']['damage']['defense'] = '05';
              $this_results_data['results_effect_index']['recovery']['speed'] = '06';
              $this_results_data['results_effect_index']['damage']['speed'] = '07';
              $this_results_data['results_effect_index']['recovery']['weapons'] = '04';
              $this_results_data['results_effect_index']['damage']['weapons'] = '05';
              $this_results_data['results_effect_index']['recovery']['experience'] = '10';
              $this_results_data['results_effect_index']['damage']['experience'] = '10';
              $this_results_data['results_effect_index']['recovery']['level'] = '10';
              $this_results_data['results_effect_index']['damage']['level'] = '10';
              $this_battle->index['results_effects'] = $this_results_data['results_effect_index'];
            }


            // Check if a damage trigger was sent with the ability results
            if ($this_options['this_ability_results']['trigger_kind'] == 'damage'){
              // Append the ability damage kind to the class
              $temp_smalltext_class = strlen($this_options['this_ability_results']['this_amount']) > 4 ? 'ability_damage_smalltext' : '';
              $this_results_data['results_amount_class'] .= 'ability_damage '.$temp_smalltext_class.' ability_damage_'.$this_options['this_ability_results']['damage_kind'].' ';
              if (!empty($this_options['this_ability_results']['flag_resistance'])){ $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_low '; }
              elseif (!empty($this_options['this_ability_results']['flag_weakness']) || !empty($this_options['this_ability_results']['flag_critical'])){ $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_high '; }
              else { $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_base '; }
              $frame_number = $this_results_data['results_effect_index']['damage'][$this_options['this_ability_results']['damage_kind']];
              $frame_int = (int)$frame_number;
              $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data['ability_sprite_size']) : 0;
              $frame_position = $frame_int;
              if ($frame_position === false){ $frame_position = 0; }
              $frame_background_offset = -1 * ceil(($this_results_data['ability_sprite_size'] * $frame_position));
              $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].'_'.$frame_number.' ';
              $this_results_data['results_effect_style'] .= 'width: '.$this_results_data['ability_sprite_size'].'px; height: '.$this_results_data['ability_sprite_size'].'px; background-size: '.$this_results_data['ability_image_width'].'px '.$this_results_data['ability_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';
              // Append the final damage results markup to the markup array
              $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'">-'.$this_options['this_ability_results']['this_amount'].'</div>';
              $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'">-'.$this_options['this_ability_results']['damage_kind'].'</div>';

            }
            // Check if a recovery trigger was sent with the ability results
            elseif ($this_options['this_ability_results']['trigger_kind'] == 'recovery'){
              // Append the ability recovery kind to the class
              $temp_smalltext_class = strlen($this_options['this_ability_results']['this_amount']) > 4 ? 'ability_recovery_smalltext' : '';
              $this_results_data['results_amount_class'] .= 'ability_recovery '.$temp_smalltext_class.' ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].' ';
              if (!empty($this_options['this_ability_results']['flag_resistance'])){ $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_low '; }
              elseif (!empty($this_options['this_ability_results']['flag_affinity']) || !empty($this_options['this_ability_results']['flag_critical'])){ $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_high '; }
              else { $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_base '; }
              $frame_number = $this_results_data['results_effect_index']['recovery'][$this_options['this_ability_results']['recovery_kind']];
              $frame_int = (int)$frame_number;
              $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data['ability_size']) : 0;
              $frame_position = $frame_int;
              if ($frame_position === false){ $frame_position = 0; }
              $frame_background_offset = -1 * ceil(($this_results_data['ability_sprite_size'] * $frame_position));
              $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].'_'.$frame_number.' ';
              $this_results_data['results_effect_style'] .= 'width: '.$this_results_data['ability_sprite_size'].'px; height: '.$this_results_data['ability_sprite_size'].'px; background-size: '.$this_results_data['ability_image_width'].'px '.$this_results_data['ability_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';
              // Append the final recovery results markup to the markup array
              $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'">+'.$this_options['this_ability_results']['this_amount'].'</div>';
              $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'">+'.$this_options['this_ability_results']['recovery_kind'].'</div>';

            }

          }

          // Append this result's markup to the main markup array
          $this_markup .= $this_results_data['results_amount_markup'];
          $this_markup .= $this_results_data['results_effect_markup'];

        }

        // ATTACHMENT ANIMATION STUFF
        if (empty($this_robot->flags['apply_disabled_state']) && !empty($this_robot->robot_attachments)){

          // Loop through each attachment and process it
          foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){
            // If this is an ability attachment
            if ($attachment_info['class'] == 'ability'){
              // Create the temporary ability object using the provided data and generate its markup data
              $this_ability = new rpg_ability($eventinfo['this_player'], $this_robot, $attachment_info);
              // Define this ability data array and generate the markup data
              $this_attachment_options = $this_options;
              $this_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
              $this_attachment_options['data_sticky'] = $this_attachment_options['sticky'];
              $this_attachment_options['data_type'] = 'attachment';
              $this_attachment_options['data_debug'] = ''; //$attachment_token;
              $this_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $this_ability->ability_image;
              $this_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $this_ability->ability_frame;
              $this_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $this_ability->ability_frame_span;
              $this_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $this_ability->ability_frame_animate;
              $attachment_frame_count = !empty($this_attachment_options['ability_frame_animate']) ? sizeof($this_attachment_options['ability_frame_animate']) : sizeof($this_attachment_options['ability_frame']);
              $temp_event_frame = $this_battle->counters['event_frames'];
              if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
              elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
              elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
              if (isset($this_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $this_attachment_options['ability_frame'] = $this_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
              $this_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $this_ability->ability_frame_offset;
              $this_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $this_ability->ability_frame_styles;
              $this_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $this_ability->ability_frame_classes;
              $this_ability_data = $this_ability->get_canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
              // Append this ability's markup to the main markup array
              if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
                $this_markup .= $this_ability_data['ability_markup'];
              }
            }

          }

        }

        // ABILITY ANIMATION STUFF
        if (/*true //$this_robot_data['robot_id'] == $this_options['this_ability_target']
          && $this_robot_data['robot_position'] != 'bench'
          &&*/ !empty($this_options['this_ability'])
          && !empty($options['canvas_show_this_ability'])){
          // Define the ability data array and generate markup data
          $attachment_options['data_type'] = 'ability';
          $this_ability_data = $this_options['this_ability']->get_canvas_markup($this_options, $this_player_data, $this_robot_data);

          // Display the ability's mugshot sprite
          if (empty($this_options['this_ability_results']['total_actions'])){
            $this_mugshot_markup_left = '<div class="sprite ability_icon ability_icon_left" style="background-image: url(images/abilities/'.(!empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
            $this_mugshot_markup_right = '<div class="sprite ability_icon ability_icon_right" style="background-image: url(images/abilities/'.(!empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
            if (!empty($eventinfo['this_robot']) && !empty($eventinfo['target_robot']) && ($eventinfo['this_robot']->robot_id != $eventinfo['target_robot']->robot_id)){

              // Check to make sure starforce is enabled right now
              $temp_starforce_enabled = true;
              if (!empty($eventinfo['this_player']->counters['dark_elements'])){ $temp_starforce_enabled = false; }
              if (!empty($eventinfo['target_player']->counters['dark_elements'])){ $temp_starforce_enabled = false; }

              // Collect the attack value from this robot
              $temp_attack_value = $eventinfo['this_robot']->robot_attack;
              $temp_attack_markup = $temp_attack_value.' AT';

              // If this player has starforce, increase the attack amount appropriately
              if ($temp_starforce_enabled && !empty($eventinfo['this_player']->player_starforce)){

                // Check to ensure this ability actually has a type before proceeding
                if (!empty($this_options['this_ability']->ability_type)){
                  // Define the boost value and start at zero
                  $temp_boost_value = 0;
                  // If the player has a matching starforce amount, add the value
                  if (!empty($eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type])){
                    // Collect the force value for the first ability type
                    $temp_force_value = $eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type];
                    // Increase the attack with the value times the boost constant
                    $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                    $temp_attack_value += $temp_boost_value;
                  }
                  // And if the ability has a second type, process that too
                  if (!empty($this_options['this_ability']->ability_type2)){
                    // If the player has a matching starforce amount, add the value
                    if (!empty($eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type2])){
                      // Collect the force value for the second ability type
                      $temp_force_value = $eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type2];
                      // Increase the attack with the value times the boost constant
                      $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                      $temp_attack_value += $temp_boost_value;
                    }
                  }
                  // If there was a starforce boost, display it
                  if ($temp_boost_value > 0){
                    // Append a star to the markup so people know it's boosted
                    $temp_attack_markup .= ' +'.$temp_boost_value.'<span class="star">&#9733;</span>';
                  }
                }

              }

              // Collect the defense value for the target robot
              $temp_defense_value = $eventinfo['target_robot']->robot_defense;
              $temp_defense_markup = $temp_defense_value.' DF';

              // If the target player has starforce, increase the defense amount appropriately
              if ($temp_starforce_enabled && !empty($eventinfo['target_player']->player_starforce)){

                // Check to ensure this ability actually has a type before proceeding
                if (!empty($this_options['this_ability']->ability_type)){
                  // Define the boost value and start at zero
                  $temp_boost_value = 0;
                  // If the player has a matching starforce amount, add the value
                  if (!empty($eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type])){
                    // Collect the force value for the first ability type
                    $temp_force_value = $eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type];
                    // Increase the defense with the value times the boost constant
                    $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                    $temp_defense_value += $temp_boost_value;
                  }
                  // And if the ability has a second type, process that too
                  if (!empty($this_options['this_ability']->ability_type2)){
                    // If the player has a matching starforce amount, add the value
                    if (!empty($eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type2])){
                      // Collect the force value for the second ability type
                      $temp_force_value = $eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type2];
                      // Increase the defense with the value times the boost constant
                      $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                      $temp_defense_value += $temp_boost_value;
                    }
                  }
                  // If there was a starforce boost, display it
                  if ($temp_boost_value > 0){
                    // Append a star to the markup so people know it's boosted
                    $temp_defense_markup .= ' +'.$temp_boost_value.'<span class="star">&#9733;</span>';
                  }
                }

              }

              // Position the attack and defense values to right/left depending on player side
              if ($eventinfo['this_player']->player_side == 'left'){
                $this_stat_markup_left = '<span class="robot_stat robot_stat_left type_attack">'.$temp_attack_markup.'</span>';
                $this_stat_markup_right = '<span class="robot_stat robot_stat_right type_defense">'.$temp_defense_markup.'</span>';
              } elseif ($eventinfo['this_player']->player_side == 'right'){
                $this_stat_markup_left = '<span class="robot_stat robot_stat_left type_defense">'.$temp_defense_markup.'</span>';
                $this_stat_markup_right = '<span class="robot_stat robot_stat_right type_attack">'.$temp_attack_markup.'</span>';
              }

              // Always show the attack name and type at this point
              $this_markup .=  '<div class="'.$this_ability_data['ability_markup_class'].' canvas_ability_details ability_type ability_type_'.(!empty($this_options['this_ability']->ability_type) ? $this_options['this_ability']->ability_type : 'none').(!empty($this_options['this_ability']->ability_type2) ? '_'.$this_options['this_ability']->ability_type2 : '').'">'.$this_mugshot_markup_left.'<div class="ability_name" style="">'.$this_ability_data['ability_title'].'</div>'.$this_mugshot_markup_right.'</div>';

              // Only show stat amounts if we're not targetting ourselves
              if ($this_options['canvas_show_ability_stats'] && $eventinfo['this_robot']->robot_id != $this_options['this_ability_results']['trigger_target_id']){
                $this_markup .= '<div class="'.$this_ability_data['ability_markup_class'].' canvas_ability_stats"><div class="wrap">'.$this_stat_markup_left.'<span class="vs">vs</span>'.$this_stat_markup_right.'</div></div>';
              }

            }
          }

          // Append this ability's markup to the main markup array
          $this_markup .= $this_ability_data['ability_markup'];

        }

        // Append this robot's markup to the main markup array
        $this_markup .= $this_robot_data['robot_markup'];

      }
    }

    // Collect the target player's markup data
    $target_player_data = $eventinfo['target_player']->get_canvas_markup($options);
    // Append the target player's markup to the main markup array
    $this_markup .= $target_player_data['player_markup'];

    // Loop through and display the target player's robots
    if ($options['canvas_show_target_robots'] && !empty($eventinfo['target_player']->player_robots)){
      // Count the number of robots on the target's side of the field
      $num_player_robots = count($eventinfo['target_player']->player_robots);

      // Loop through each target robot and generate it's markup
      foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){
        // Create the temporary target robot ovject
        $target_robot = new rpg_robot($eventinfo['target_player'], $target_robotinfo);
        $target_options = $options;
        //if ($target_robot->robot_status == 'disabled' && $target_robot->robot_position == 'bench'){ continue; }
        if (!empty($target_robot->flags['hidden'])){ continue; }
        elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id != $target_robot->robot_id){ $target_options['this_ability'] = false;  }
        elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id == $target_robot->robot_id && $options['canvas_show_target'] != false){ $target_robot->robot_frame =  $eventinfo['target_robot']->robot_frame; }
        $target_robot->robot_key = $target_robot->robot_key !== false ? $target_robot->robot_key : ($target_key > 0 ? $target_key : $num_player_robots);
        $target_robot_data = $target_robot->get_canvas_markup($target_options, $target_player_data);

        // ATTACHMENT ANIMATION STUFF
        if (empty($target_robot->flags['apply_disabled_state']) && !empty($target_robot->robot_attachments)){
          // Loop through each attachment and process it
          foreach ($target_robot->robot_attachments AS $attachment_token => $attachment_info){
            // If this is an ability attachment
            if ($attachment_info['class'] == 'ability'){
              // Create the target's temporary ability object using the provided data
              $target_ability = new rpg_ability($eventinfo['target_player'], $target_robot, $attachment_info);
              // Define this ability data array and generate the markup data
              $target_attachment_options = $target_options;
              $target_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
              $target_attachment_options['data_sticky'] = $target_attachment_options['sticky'];
              $target_attachment_options['data_type'] = 'attachment';
              $target_attachment_options['data_debug'] = ''; //$attachment_token;
              $target_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $target_ability->ability_image;
              $target_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $target_ability->ability_frame;
              $target_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $target_ability->ability_frame_span;
              $target_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $target_ability->ability_frame_animate;
              $attachment_frame_key = 0;
              $attachment_frame_count = sizeof($target_attachment_options['ability_frame_animate']);
              $temp_event_frame = $this_battle->counters['event_frames'];
              if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
              elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
              elseif ($attachment_frame_count > 0 && $temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
              if (isset($target_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $target_attachment_options['ability_frame'] = $target_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
              else { $target_attachment_options['ability_frame'] = 0; }
              $target_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $target_ability->ability_frame_offset;
              $target_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $target_ability->ability_frame_styles;
              $target_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $target_ability->ability_frame_classes;
              $target_ability_data = $target_ability->get_canvas_markup($target_attachment_options, $target_player_data, $target_robot_data);
              // Append this target's ability's markup to the main markup array
              if (!preg_match('/display:\s?none;/i', $target_robot->robot_frame_styles)){
                $this_markup .= $target_ability_data['ability_markup'];
              }
            }

          }

        }

        $this_markup .= $target_robot_data['robot_markup'];

      }

    }

    // Append the field multipliers to the canvas markup
    if (!empty($this_battle->battle_field->field_multipliers)){
      $temp_multipliers = $this_battle->battle_field->field_multipliers;
      asort($temp_multipliers);
      $temp_multipliers = array_reverse($temp_multipliers, true);
      $temp_multipliers_count = count($temp_multipliers);
      $this_special_types = array('experience', 'damage', 'recovery', 'items');
      $multiplier_markup_left = '';
      $multiplier_markup_right = '';
      foreach ($temp_multipliers AS $this_type => $this_multiplier){
        if ($this_type == 'experience' && rpg_game::is_demo()){ continue; }
        if ($this_multiplier == 1){ continue; }
        if ($this_multiplier < MMRPG_SETTINGS_MULTIPLIER_MIN){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MIN; }
        elseif ($this_multiplier > MMRPG_SETTINGS_MULTIPLIER_MAX){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MAX; }
        $temp_name = $this_type != 'none' ? ucfirst($this_type) : 'Neutral';
        $temp_number = number_format($this_multiplier, 1);
        $temp_title = $temp_name.' x '.$temp_number;
        if ($temp_multipliers_count >= 8){ $temp_name = substr($temp_name, 0, 2); }
        $temp_markup = '<span title="'.$temp_title.'" data-tooltip-align="center" class="field_multiplier field_multiplier_'.$this_type.' field_multiplier_count_'.$temp_multipliers_count.' field_type field_type_'.$this_type.'"><span class="text"><span class="name">'.$temp_name.' </span><span class="cross">x</span><span class="number"> '.$temp_number.'</span></span></span>';
        if (in_array($this_type, $this_special_types)){ $multiplier_markup_left .= $temp_markup; }
        else { $multiplier_markup_right .= $temp_markup; }
      }
      if (!empty($multiplier_markup_left) || !empty($multiplier_markup_right)){
        $this_markup .= '<div class="canvas_overlay_footer"><strong class="overlay_label">Field Multipliers</strong><span class="overlay_multiplier_count_'.$temp_multipliers_count.'">'.$multiplier_markup_left.$multiplier_markup_right.'</div></div>';
      }

    }

    // If this battle is over, display the mission complete/failed result
    if ($this_battle->battle_status == 'complete'){
      if ($this_battle->battle_result == 'victory'){
        $result_text = 'Mission Complete!';
        $result_class = 'nature';
      }
      elseif ($this_battle->battle_result == 'defeat') {
        $result_text = 'Mission Failure&hellip;';
        $result_class = 'flame';
      }
      if (!empty($this_markup) && $this_battle->battle_status == 'complete' || $this_battle->battle_result == 'defeat'){
        $this_mugshot_markup_left = '<div class="sprite ability_icon ability_icon_left">&nbsp;</div>';
        $this_mugshot_markup_right = '<div class="sprite ability_icon ability_icon_right">&nbsp;</div>';
        $this_markup =  '<div class="sprite canvas_ability_details ability_type ability_type_'.$result_class.'">'.$this_mugshot_markup_left.'<div class="ability_name">'.$result_text.'</div>'.$this_mugshot_markup_right.'</div>'.$this_markup;
      }
    }

    // Return the generated markup and robot data
    return $this_markup;

  }


  /**
   * Generate the console message markup for this frame of battle including players, robots, abilities etc.
   * @param array $eventinfo
   * @param array $options (optional)
   * @return string
   */
  public function get_console_markup($eventinfo, $options = array()){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_battle = self::get_battle();
    $this_field = rpg_field::get_field();

    // Default the return markup to empty
    $this_markup = '';

    // Ensure this side is allowed to be shown before generating any markup
    if ($options['console_show_this'] != false){

        // Define the necessary text markup for the current player if allowed and exists
      if (!empty($eventinfo['this_player'])){
        // Collect the console data for this player
        $this_player_data = $eventinfo['this_player']->get_console_markup($options);
      } else {
        // Define empty console data for this player
        $this_player_data = array();
        $options['console_show_this_player'] = false;
      }
      // Define the necessary text markup for the current robot if allowed and exists
      if (!empty($eventinfo['this_robot'])){
        // Collect the console data for this robot
        $this_robot_data = $eventinfo['this_robot']->get_console_markup($options, $this_player_data);
      } else {
        // Define empty console data for this robot
        $this_robot_data = array();
        $options['console_show_this_robot'] = false;
      }
      // Define the necessary text markup for the current ability if allowed and exists
      if (!empty($options['this_ability'])){
        // Collect the console data for this ability
        $this_ability_data = $options['this_ability']->get_console_markup($options, $this_player_data, $this_robot_data);
      } else {
        // Define empty console data for this ability
        $this_ability_data = array();
        $options['console_show_this_ability'] = false;
      }
      // Define the necessary text markup for the current star if allowed and exists
      if (!empty($options['this_star'])){
        // Collect the console data for this star
        $this_star_data = rpg_functions::get_star_console_markup($options['this_star'], $this_player_data, $this_robot_data);
      } else {
        // Define empty console data for this star
        $this_star_data = array();
        $options['console_show_this_star'] = false;
      }

      // If no objects would found to display, turn the left side off
      if (empty($options['console_show_this_player'])
        && empty($options['console_show_this_robot'])
        && empty($options['console_show_this_ability'])
        && empty($options['console_show_this_star'])){
        // Automatically set the console option to false
        $options['console_show_this'] = false;
      }

    }
    // Otherwise, if this side is not allowed to be shown at all
    else {

      // Default all of this side's objects to empty arrays
      $this_player_data = array();
      $this_robot_data = array();
      $this_ability_data = array();
      $this_star_data = array();

    }

    // Ensure the target side is allowed to be shown before generating any markup
    if ($options['console_show_target'] != false){

      // Define the necessary text markup for the target player if allowed and exists
      if (!empty($eventinfo['target_player'])){
        // Collect the console data for this player
        $target_player_data = $eventinfo['target_player']->get_console_markup($options);
      } else {
        // Define empty console data for this player
        $target_player_data = array();
        $options['console_show_target_player'] = false;
      }
      // Define the necessary text markup for the target robot if allowed and exists
      if (!empty($eventinfo['target_robot'])){
        // Collect the console data for this robot
        $target_robot_data = $eventinfo['target_robot']->get_console_markup($options, $target_player_data);
      } else {
        // Define empty console data for this robot
        $target_robot_data = array();
        $options['console_show_target_robot'] = false;
      }
      // Define the necessary text markup for the target ability if allowed and exists
      if (!empty($options['target_ability'])){
        // Collect the console data for this ability
        $target_ability_data = $options['target_ability']->get_console_markup($options, $target_player_data, $target_robot_data);
      } else {
        // Define empty console data for this ability
        $target_ability_data = array();
        $options['console_show_target_ability'] = false;
      }

      // If no objects would found to display, turn the right side off
      if (empty($options['console_show_target_player'])
        && empty($options['console_show_target_robot'])
        && empty($options['console_show_target_ability'])){
        // Automatically set the console option to false
        $options['console_show_target'] = false;
      }

    }
    // Otherwise, if the target side is not allowed to be shown at all
    else {

      // Default all of the target side's objects to empty arrays
      $target_player_data = array();
      $target_robot_data = array();
      $target_ability_data = array();

    }

    // Assign player-side based floats for the header and body if not set
    if (empty($options['console_header_float']) && !empty($this_robot_data)){
      $options['console_header_float'] = $this_robot_data['robot_float'];
    }
    if (empty($options['console_body_float']) && !empty($this_robot_data)){
      $options['console_body_float'] = $this_robot_data['robot_float'];
    }

    // Append the generated console markup if not empty
    if (!empty($eventinfo['event_header']) && !empty($eventinfo['event_body'])){

      // Define the container class based on height
      $event_class = 'event ';
      $event_style = '';
      if ($options['console_container_height'] == 1){ $event_class .= 'event_single '; }
      if ($options['console_container_height'] == 2){ $event_class .= 'event_double '; }
      if ($options['console_container_height'] == 3){ $event_class .= 'event_triple '; }
      if (!empty($options['console_container_classes'])){ $event_class .= $options['console_container_classes']; }
      if (!empty($options['console_container_styles'])){ $event_style .= $options['console_container_styles']; }

      // Generate the opening event tag
      $this_markup .= '<div class="'.$event_class.'" style="'.$event_style.'">';

      // Generate this side's markup if allowed
      if ($options['console_show_this'] != false){
        // Append this player's markup if allowed
        if ($options['console_show_this_player'] != false){ $this_markup .= $this_player_data['player_markup']; }
        // Otherwise, append this robot's markup if allowed
        elseif ($options['console_show_this_robot'] != false){ $this_markup .= $this_robot_data['robot_markup']; }
        // Otherwise, append this ability's markup if allowed
        elseif ($options['console_show_this_ability'] != false){ $this_markup .= $this_ability_data['ability_markup']; }
        // Otherwise, append this star's markup if allowed
        elseif ($options['console_show_this_star'] != false){ $this_markup .= $this_star_data['star_markup']; }
      }

      // Generate the target side's markup if allowed
      if ($options['console_show_target'] != false){
        // Append the target player's markup if allowed
        if ($options['console_show_target_player'] != false){ $this_markup .= $target_player_data['player_markup']; }
        // Otherwise, append the target robot's markup if allowed
        elseif ($options['console_show_target_robot'] != false){ $this_markup .= $target_robot_data['robot_markup']; }
        // Otherwise, append the target ability's markup if allowed
        elseif ($options['console_show_target_ability'] != false){ $this_markup .= $target_ability_data['ability_markup']; }
      }

      // Prepend the turn counter to the header if necessary
      if (!empty($this_battle->counters['battle_turn']) && $this_battle->battle_status != 'complete'){ $eventinfo['event_header'] = 'Turn #'.$this_battle->counters['battle_turn'].' : '.$eventinfo['event_header']; }

      // Display the event header and event body
      $this_markup .= '<div class="header header_'.$options['console_header_float'].'">'.$eventinfo['event_header'].'</div>';
      $this_markup .= '<div class="body body_'.$options['console_body_float'].'">'.$eventinfo['event_body'].'</div>';

      // Displat the closing event tag
      $this_markup .= '</div>';

    }

    // Return the generated markup and robot data
    return $this_markup;

  }


  /**
   * Request an array of all event markup generated in the battle so far
   * @return array
   */
  public function get_events_markup(){
    // Return the events markup array
    return $this->events;
  }



  // -- SESSION FUNCTIONS -- //

  /**
   * Update internal variables for this battle object
   * @return bool
   */
  public function update_variables(){

    // Calculate this battle's count variables
    //$this->counters['thing'] = count($this->robot_stuff);

    // Return true on success
    return true;

  }

  /**
   * Update the session data for this battle object
   * @return bool
   */
  public function update_session(){

    // Update any internal counters
    $this->update_variables();

    // Update the session with the export array
    $this_data = $this->export_array();
    $_SESSION[$this->session_key][$this->{$this->session_id}] = $this_data;

    // Return true on success
    return true;

  }

  /**
   * Export an array of the current battle object values
   * @return array
   */
  public function export_array(){

    // Collect IDs for the object index
    $object_index = array();
    $object_index['players'] = array_keys($this->players);
    $object_index['robots'] = array_keys($this->robots);
    $object_index['abilities'] = array_keys($this->abilities);
    $object_index['attachments'] = array_keys($this->attachments);
    $object_index['items'] = array_keys($this->items);

    // Return all internal ability fields in array format
    return array(
      'battle_id' => $this->battle_id,
      'battle_name' => $this->battle_name,
      'battle_token' => $this->battle_token,
      'battle_description' => $this->battle_description,
      'battle_level' => $this->battle_level,
      'battle_field_info' => $this->battle_field_info,
      'battle_this_player' => $this->battle_this_player,
      'battle_target_player' => $this->battle_target_player,
      'battle_turns_limit' => $this->battle_turns_limit,
      'battle_robots_limit' => $this->battle_robots_limit,
      'battle_points' => $this->battle_rewards_points,
      'battle_rewards_zenny' => $this->battle_rewards_zenny,
      'battle_rewards_robots' => $this->battle_rewards_robots,
      'battle_rewards_abilities' => $this->battle_rewards_abilities,
      'battle_rewards_items' => $this->battle_rewards_items,
      'battle_status' => $this->battle_status,
      'battle_result' => $this->battle_result,
      'battle_overkill' => $this->battle_overkill,
      'battle_base_name' => $this->battle_base_name,
      'battle_base_description' => $this->battle_base_description,
      'battle_base_rewards_points' => $this->battle_base_rewards_points,
      'battle_base_rewards_zenny' => $this->battle_base_rewards_zenny,
      'battle_base_rewards_robots' => $this->battle_base_rewards_robots,
      'battle_base_rewards_abilities' => $this->battle_base_rewards_abilities,
      'battle_base_rewards_items' => $this->battle_base_rewards_items,
      'flags' => $this->flags,
      'counters' => $this->counters,
      'values' => $this->values,
      'history' => $this->history,
      'objects' => $object_index
      );

  }

}
?>