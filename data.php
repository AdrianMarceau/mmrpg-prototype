<?php
// Include the TOP file
require_once('top.php');

// Start the output buffer to capture any errors
ob_start();

/*
 * DEFINE & COLLECT BATTLE VARIABLES
 */

// Collect the global battle variables
$this_battle_id = isset($_REQUEST['this_battle_id']) ? $_REQUEST['this_battle_id'] : 1;
$this_battle_token = isset($_REQUEST['this_battle_token']) ? $_REQUEST['this_battle_token'] : 'battle';
$this_field_id = isset($_REQUEST['this_field_id']) ? $_REQUEST['this_field_id'] : 1;
$this_field_token = isset($_REQUEST['this_field_token']) ? $_REQUEST['this_field_token'] : 'field';
$this_user_id = isset($_REQUEST['this_user_id']) ? $_REQUEST['this_user_id'] : 1;
$this_player_id = isset($_REQUEST['this_player_id']) ? $_REQUEST['this_player_id'] : $this_user_id;
$this_player_token = isset($_REQUEST['this_player_token']) ? $_REQUEST['this_player_token'] : 'player';
$this_player_robots = !empty($_REQUEST['this_player_robots']) ? $_REQUEST['this_player_robots'] : '00_robot';
$this_robot_id = isset($_REQUEST['this_robot_id']) ? $_REQUEST['this_robot_id'] : 1;
$this_robot_token = isset($_REQUEST['this_robot_token']) ? $_REQUEST['this_robot_token'] : 'robot';
$target_user_id = isset($_REQUEST['target_user_id']) ? $_REQUEST['target_user_id'] : 2;
$target_player_id = isset($_REQUEST['target_player_id']) ? $_REQUEST['target_player_id'] : 2;
$target_player_token = isset($_REQUEST['target_player_token']) ? $_REQUEST['target_player_token'] : 'player';
$target_player_robots = !empty($_REQUEST['target_player_robots']) ? $_REQUEST['target_player_robots'] : '00_robot';
$target_robot_id = isset($_REQUEST['target_robot_id']) ? $_REQUEST['target_robot_id'] : 2;
$target_robot_token = isset($_REQUEST['target_robot_token']) ? $_REQUEST['target_robot_token'] : 'robot';

// Define the current action request variables
$this_action = isset($_REQUEST['this_action']) ? $_REQUEST['this_action'] : 'start';
$this_action_token = isset($_REQUEST['this_action_token']) ? $_REQUEST['this_action_token'] : '';
$target_action = isset($_REQUEST['target_action']) ? $_REQUEST['target_action'] : 'start';
$target_action_token = isset($_REQUEST['target_action_token']) ? $_REQUEST['target_action_token'] : '';

// Define a variable to track the verified state and any errors in data processing
$this_verified = true;
$this_errors = array();

// Ensure all madatory variables were set, and create errors for missing fields
if (empty($this_battle_id) || empty($this_battle_token)){
  $this_verified = false;
  $this_errors[] = 'This battle token was not received! '.
    '$this_battle_id = '.$this_battle_id.'; $this_battle_token = '.$this_battle_token.';';
}
if (empty($this_field_id) || empty($this_field_token)){
  $this_verified = false;
  $this_errors[] = 'This field token was not received! '.
    '$this_field_id = '.$this_field_id.'; $this_field_token = '.$this_field_token.';';
}
if (empty($this_player_id) || empty($this_player_token)){
  $this_verified = false;
  $this_errors[] = 'This player token was not received! '.
    '$this_field_id = '.$this_field_id.'; $this_field_token = '.$this_field_token.';';
}
if (empty($target_player_id) || empty($target_player_token)){
  $this_verified = false;
  $this_errors[] = 'Target player token was not received! '.
    '$target_player_id = '.$target_player_id.'; $target_player_token = '.$target_player_token.';';
}
if (empty($this_robot_id) || empty($this_robot_token)){
  $this_verified = false;
  $this_errors[] = 'This robot token was not received! '.
    '$this_robot_id = '.$this_robot_id.'; $this_robot_token = '.$this_robot_token.';';
}
if (empty($target_robot_id) || empty($target_robot_token)){
  $this_verified = false;
  $this_errors[] = 'Target robot token was not received! '.
    '$target_robot_id = '.$target_robot_id.'; $target_robot_token = '.$target_robot_token.';';
}

// If there were any critcal errors, exit the battle script
if ($this_verified == false || !empty($this_errors)){
  trigger_error('Critical Battle Error<br /><pre>$this_errors : '.print_r($this_errors, true).'</pre>', E_USER_ERROR);
}

// If the player is has requested the start action
if ($this_action == 'start'){
  // Automatically empty all temporary battle variables
  $_SESSION['BATTLES'] = array();
  $_SESSION['FIELDS'] = array();
  $_SESSION['PLAYERS'] = array();
  $_SESSION['ROBOTS'] = array();
  $_SESSION['ABILITIES'] = array();

}

/*
 * DEFINE & INITALIZE BATTLE OBJECTS
 */

// Define the battle object using the loaded battle data
$this_battleinfo = array('battle_id' => $this_battle_id, 'battle_token' => $this_battle_token);
$this_battleinfo['flags']['wap'] = $flag_wap ? true : false;

// Define the current field object using the loaded field data
$this_fieldinfo = array('field_id' => $this_field_id, 'field_token' => $this_field_token);

// Define the battle object using the loaded battle data and update session
$this_battle = new mmrpg_battle($this_battleinfo);
$this_battle->update_session();

// Define the current field object using the loaded field data and update session
$this_field = new mmrpg_field($this_battle, $this_fieldinfo);
$this_field->update_session();

// Define the current player object using the loaded player data
$this_playerinfo = array('user_id' => $this_user_id, 'player_id' => $this_player_id, 'player_token' => $this_player_token, 'player_autopilot' => false);
$this_playerinfo['player_autopilot'] = false;
$this_playerinfo['player_side'] = 'left';
// Define the target player object using the loaded player data
$target_playerinfo = array('user_id' => $target_user_id, 'player_id' => $target_player_id, 'player_token' => $target_player_token);
$target_playerinfo['player_autopilot'] = true;
$target_playerinfo['player_side'] = 'right';

// Only create the arrays with minimal info if NOT start
if ($this_action != 'start'){
  // Define the current player object using the loaded player data and update session
  $this_player = new mmrpg_player($this_battle, $this_playerinfo);
  $this_player->update_session();
  // Define the target player object using the loaded player data and update session
  $target_player = new mmrpg_player($this_battle, $target_playerinfo);
  $target_player->update_session();
}
// Otherwise, prepopulate their robot arrays
elseif ($this_action == 'start'){
  // Break apart and filter this player's robots
  $this_playerinfo['player_robots'] = array();
  $temp_this_player_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
  foreach ($temp_this_player_robots AS $temp_string){
    list($temp_id, $temp_token) = explode('_', $temp_string);
    $temp_rewards = mmrpg_prototype_robot_settings($this_playerinfo['player_token'], $temp_token);
    $temp_abilities = !empty($temp_rewards['robot_abilities']) ? array_keys($temp_rewards['robot_abilities']) : array();
    $this_playerinfo['player_robots'][] = array('robot_id' => $temp_id, 'robot_token' => $temp_token, 'robot_abilities' => $temp_abilities);
    unset($temp_rewards, $temp_abilities);
  }

  // Break apart and filter the target player's robots
  $target_playerinfo['player_robots'] = array();
  if (!empty($this_battle->battle_target_player['player_robots'])){
    $target_playerinfo['player_robots'] = $this_battle->battle_target_player['player_robots'];
  }

  // Break apart and filter the target player's starforce
  $target_playerinfo['player_starforce'] = array();
  if (!empty($this_battle->battle_target_player['player_starforce'])){
    $target_playerinfo['player_starforce'] = $this_battle->battle_target_player['player_starforce'];
  }

  // If this is a player battle, update the current player's name
  if (!empty($this_battle->flags['player_battle'])){
    //$this_playerinfo['player_name'] = $this_battle->battle_token . '<br />flags='.(implode(',', array_keys($this_battle->flags))).'|values='.(implode(',', array_keys($this_battle->values))); //!empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
    $this_playerinfo['player_name'] = !empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
  }
  // If the target player's name was provided, be sure to update it
  if (!empty($this_battle->battle_target_player['player_name'])){
    $target_playerinfo['player_name'] = $this_battle->battle_target_player['player_name'];
  }

}

// If this is the START action, update objects with preset battle data fields
if ($this_action == 'start'){
  // Start the battle turn off at zero
  $this_battle->counters['battle_turn'] = 0;

  // Update applicable fieldinfo fields with preset battle data
  if (!empty($this_battle->battle_field_base)){
    $this_fieldinfo = array_replace($this_fieldinfo, $this_battle->battle_field_base);
    $this_field = new mmrpg_field($this_battle, $this_fieldinfo);
    $this_field->update_session();
  }

  // Ensure the player's robot string was provided
  if (!empty($this_player_robots)){
    // Precreate the player object using the newly defined details
    $backup_this_playerinfo = $this_playerinfo;
    $backup_this_playerinfo['player_robots'] = array();
    $this_player = new mmrpg_player($this_battle, $backup_this_playerinfo);
    $this_player->update_session();
    unset($backup_this_playerinfo);
    // Break apart the allowed robots string and unset undefined robots
    $temp_robots_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    $this_key_counter = 0;
    $temp_this_player_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
    foreach ($this_playerinfo['player_robots'] AS $this_key => $this_data){
      if (!mmrpg_prototype_robot_unlocked($this_player_token, $this_data['robot_token'])){ continue; }
      $this_info = mmrpg_robot::parse_index_info($temp_robots_index[$this_data['robot_token']]);
      $this_token = $this_data['robot_id'].'_'.$this_data['robot_token'];
      $this_position = array_search($this_token, $temp_this_player_robots);
      $this_data['robot_key'] = $this_key_counter;
      $this_data['robot_experience'] = mmrpg_prototype_robot_experience($this_playerinfo['player_token'], $this_data['robot_token']);
      $this_data['robot_level'] = mmrpg_prototype_robot_level($this_playerinfo['player_token'], $this_data['robot_token']);
      // Only allow this robot if it exists in the robot string
      if ($this_position !== false){
        // Create the temporary robot object to load data
        $this_data = array_merge($this_info, $this_data);
        $this_player->load_robot($this_data, $this_key_counter, true);
        $this_player->update_session();
        $this_key_counter++;
      }
    }
    // Update the player session with changes
    $this_player->update_session();

  }

  // Ensure this player's items were provided
  if (!empty($_SESSION['GAME']['values']['battle_items'])){
    $this_player_items = $_SESSION['GAME']['values']['battle_items'];
    unset($this_player_items['item-screw-small'], $this_player_items['item-screw-large']);
    $this_player_items = array_keys($this_player_items);
  } else {
    $this_player_items = array();
  }

  // Update this player's items in the object and session
  if (!empty($this_player_items)){
    // Update this player's items with the requested tokens
    $this_player->player_items = $this_player_items;
    $this_player->player_base_items = $this_player->player_items;
    // Update the session with the item changes
    $this_player->update_session();
  }

  // Ensure there are target robots to loop through
  if (!empty($target_playerinfo['player_robots'])){
    // Precreate the target player object using the newly defined details
    $backup_target_playerinfo = $target_playerinfo;
    $backup_target_playerinfo['player_robots'] = array();
    $target_player = new mmrpg_player($this_battle, $backup_target_playerinfo);
    $target_player->update_session();
    unset($backup_target_playerinfo);
    // Break apart the allowed robots string and unset undefined robots
    $temp_robots_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    //strstr($target_player_robots, ',') ? explode(',', $target_player_robots) : array($target_player_robots);
    //$target_playerinfo = $target_player->export_array();
    $target_playerinfo_robots = array();
    $this_key_counter = 0;
    foreach ($target_playerinfo['player_robots'] AS $this_key => $this_data){
      $this_info = mmrpg_robot::parse_index_info($temp_robots_index[$this_data['robot_token']]);
      $this_token = $this_data['robot_id'].'_'.$this_data['robot_token'];
      $this_position = $this_key; //array_search($this_token, $target_player_robots);
      $this_data['robot_key'] = $this_key_counter;
      // Only allow this robot if it exists in the robot string
      if ($this_position !== false){
        // Create the temporary robot object to load data
        $this_data = array_merge($this_info, $this_data);
        $target_player->player_robots[$this_key] = $this_data;
        $target_player->load_robot($this_data, $this_key_counter, true);
        $this_key_counter++;
      }
    }
    // Update the player session with changes
    $target_player->update_session();

    // DEBUG
    //die('<hr />'.__LINE__.':: <pre>'.print_r($this_data, true).'</pre>');

  }

  // Ensure the target player's items were provided
  //$target_playerinfo['player_items'] = array('item-energy-pellet','item-energy-capsule','item-weapon-pellet','item-weapon-capsule', 'item-energy-tank', 'item-weapon-tank', 'item-yashichi', 'item-extra-life');
  $target_playerinfo['player_items'] = array();
  if (!empty($target_playerinfo['player_items'])){
    // Update this player's items with the requested tokens
    $target_player->player_items = $target_playerinfo['player_items'];
    $target_player->player_base_items = $target_player->player_items;
    // Update the session with the item changes
    $target_player->update_session();
  }

}

// Ensure this player has robots to start with
if (!empty($this_player->player_robots)){
  // Check if the player robot was set to auto
  if ($this_robot_id == 'auto' || $this_robot_token == 'auto'){
    // Collect the first robot in this player's party
    reset($this_player->player_robots);
    $this_robotinfo = current($this_player->player_robots);
  }
  // Otherwise define the robotinfo array manually
  else {
    // Create the robotinfo array with engine data
    $this_robotinfo = array('robot_id' => $this_robot_id, 'robot_token' => $this_robot_token);
  }
}
// Otherwise, if this player has no robots
else {
  // Trigger a critical error, this shit is not gonna work
  trigger_error('Critical Battle Error<br /><pre>This player has no robots!</pre>', E_USER_ERROR);
}

//echo '<script type="text/javascript">alert("$this_robotinfo : '.preg_replace('#\s+#', ' ', print_r($this_robotinfo, true)).'");</script>';

// Ensure the target player has robots to start with
if (!empty($target_player->player_robots)){
  // Check if the target player robot was set to auto
  if ($target_robot_id == 'auto' || $target_robot_token == 'auto'){
    // Collect the first robot in the target player's party
    reset($target_player->player_robots);
    $target_robotinfo = current($target_player->player_robots);
  }
  // Otherwise define the robotinfo array manually
  else {
    // Create the robotinfo array with engine data
    $target_robotinfo = array('robot_id' => $target_robot_id, 'robot_token' => $target_robot_token);
  }
}
// Otherwise, if the target player has no robots
else {
  // Trigger a critical error, this shit is not gonna work
  trigger_error('Critical Battle Error<br /><pre>The target player has no robots!</pre>', E_USER_ERROR);
}

// Define the current robot object using the loaded robot data
$this_robot = new mmrpg_robot($this_battle, $this_player, $this_robotinfo);
// Define the target robot object using the loaded robot data
if ($target_robotinfo['robot_id'] >= MMRPG_SETTINGS_TARGET_PLAYERID){
  $target_robot = new mmrpg_robot($this_battle, $target_player, $target_robotinfo);
} else {
  $target_robot = new mmrpg_robot($this_battle, $this_player, $target_robotinfo);
}


/*
 * BATTLE START!
 */

// Define the redirect variable in case we need to change screens
$this_redirect = '';

// Define the action queue variable and populate it with this/target pairs
$action_queue = array();

// If the player is has requested the prototype menu
if ($this_action == 'prototype'){
  // Automatically empty all temporary battle variables
  $_SESSION['BATTLES'] = array();
  $_SESSION['FIELDS'] = array();
  $_SESSION['PLAYERS'] = array();
  $_SESSION['ROBOTS'] = array();
  $_SESSION['ABILITIES'] = array();

  // Redirect the user back to the prototype screen
  $this_redirect = 'prototype.php?'.($flag_wap ? 'wap=true' : '');

}
// Else if the player is has requested to restart the battle
elseif ($this_action == 'restart'){
  // Define the player's robots string
  $this_player_robots = array();
  if (!empty($this_player->player_robots)){
    foreach ($this_player->player_robots AS $key => $temp_robotinfo){
      $this_player_robots[] = $temp_robotinfo['robot_id'].'_'.$temp_robotinfo['robot_token'];
    }
  }
  $this_player_robots = implode(',', $this_player_robots);

  // Redirect the user back to the prototype screen
  $this_redirect = 'battle.php?'.
    ($flag_wap ? 'wap=true' : 'wap=false').
    '&this_battle_id='.$this_battle->battle_id.
    '&this_battle_token='.$this_battle->battle_token.
    //'&this_field_id='.$this_field->field_id.
    //'&this_field_token='.$this_field->field_token.
    '&this_player_id='.$this_player->player_id.
    '&this_player_token='.$this_player->player_token.
    '&this_player_robots='.$this_player_robots.
    //'&target_player_id='.$target_player->player_id.
    //'&target_player_token='.$target_player->player_token.
    '';

  // Automatically empty all temporary battle variables
  $_SESSION['BATTLES'] = array();
  $_SESSION['FIELDS'] = array();
  $_SESSION['PLAYERS'] = array();
  $_SESSION['ROBOTS'] = array();
  $_SESSION['ABILITIES'] = array();

}
// Else if the player is just starting the battle, queue start actions
elseif ($this_action == 'start'){
  // Define the battle's turn counter and start at 0
  $this_battle->counters['battle_turn'] = 0;
  $this_battle->update_session();

  // Collect the base point and zenny rewards for this battle
  $temp_target_turns_base = !empty($this_battle->battle_turns) ? $this_battle->battle_turns : 0;
  $temp_target_robots_base = !empty($this_battle->battle_robot_limit) ? $this_battle->battle_robot_limit : 0;
  $temp_reward_points_base = !empty($this_battle->battle_points) ? number_format($this_battle->battle_points, 0, '.', ',') : 0;
  $temp_reward_zenny_base = !empty($this_battle->battle_zenny) ? number_format($this_battle->battle_zenny, 0, '.', ',') : 0;
  // Collect the mission complete/failure records to display
  //$temp_battle_complete_count = isset($_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this_battle->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this_battle->battle_token]['battle_count'] : 0;
  //$temp_battle_failure_count = isset($_SESSION['GAME']['values']['battle_failure'][$this_player->player_token][$this_battle->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_failure'][$this_player->player_token][$this_battle->battle_token]['battle_count'] : 0;

  // Define the first event body markup, regardless of player type
  $first_event_header = $this_battle->battle_name.' <span class="pipe">|</span> '.$this_battle->battle_field->field_name;
  $first_event_body = $this_battle->battle_description.'<br />';
  $first_event_body .= 'Target : '.
    ($this_battle->battle_turns == 1 ? '1 Turn' : $temp_target_turns_base.' Turns').
    ' with '.
    ($this_battle->battle_robot_limit == 1 ? '1 Robot' : $temp_target_robots_base.' Robots').
    '  <span class="pipe">|</span> '.
    'Reward : '.
    ($this_battle->battle_points == 1 ? '1 Point' : $temp_reward_points_base.' Points').
    ' and '.
    ($this_battle->battle_zenny == 1 ? '1 Zenny' : $temp_reward_zenny_base.' Zenny').
    '<br />';

  // Update the summon counts for all this player's robots
  foreach ($this_player->values['robots_active'] AS $key => $info){
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']] = array('robot_token' => $info['robot_token']); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_summoned'])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_summoned'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_summoned'] += 1;
  }

  // Update the encounter counts for all target player's robots
  foreach ($target_player->values['robots_active'] AS $key => $info){
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']] = array('robot_token' => $info['robot_token']); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_encountered'])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_encountered'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_encountered'] += 1;
  }

  // Hide all this player's robots by default
  foreach ($this_player->values['robots_active'] AS $key => $info){
    if ($this_robot->robot_id == $info['robot_id']){
      $this_robot->robot_frame_styles = 'display: none; ';
      $this_robot->robot_detail_styles = 'display: none; ';
      $this_robot->update_session();
    } else {
      $temp_robot = new mmrpg_robot($this_battle, $this_player, $info);
      $temp_robot->robot_frame_styles = 'display: none; ';
      $temp_robot->robot_detail_styles = 'display: none; ';
      $temp_robot->update_session();
    }
  }

  // Hide all the target player's robots by default
  foreach ($target_player->values['robots_active'] AS $key => $info){
    if ($target_robot->robot_id == $info['robot_id']){
      $target_robot->robot_frame_styles = 'display: none; ';
      $target_robot->robot_detail_styles = 'display: none; ';
      $target_robot->update_session();
    } else {
      $temp_robot = new mmrpg_robot($this_battle, $target_player, $info);
      $temp_robot->robot_frame_styles = 'display: none; ';
      $temp_robot->robot_detail_styles = 'display: none; ';
      $temp_robot->update_session();
    }
  }

  // If there is a target player, have this player's robots display first
  if ($target_player->player_token != 'player'){

    // Create the battle start event, showing the points and amount of turns
    $event_header = $first_event_header;
    $event_body = $first_event_body;
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'center';
    $event_options['canvas_show_this'] = $event_options['console_show_this'] = false;
    $event_options['canvas_show_this_robots'] = false;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['canvas_show_target_robots'] = false;
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_battle->events_create(false, false, '', '');
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Create the enter event for the target player's robots
    $event_header = $target_player->player_name.'&#39;s '.($target_player->counters['robots_active'] > 1 ? 'Robots' : 'Robot');
    $event_body = $target_player->print_player_name().'&#39;s '.($target_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
    //if (isset($target_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$target_player->player_quotes['battle_start'].'</em>&quot;'; }
    if ($target_player->player_token != 'player'
      && isset($target_player->player_quotes['battle_start'])){
      $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
      $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $target_robot->robot_name);
      $event_body .= $target_player->print_player_quote('battle_start', $this_find, $this_replace);
    }
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'right';
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target'] = false;
    $event_options['console_show_target_player'] = false;
    $target_player->player_frame = 'taunt';
    $target_player->update_session();
    $target_robot->robot_frame = 'taunt';
    $target_robot->robot_frame_styles = '';
    $target_robot->robot_detail_styles = '';
    $target_robot->robot_position = 'active';
    $target_robot->update_session();
    $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);
    $target_player->player_frame = 'base';
    $target_player->update_session();
    $target_robot->robot_frame = 'base';
    $target_robot->update_session();
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Then queue up an the target robot's startup action
    $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    // Create the enter event for this player's robots
    $event_header = "{$this_player->player_name}&#39;s ".($this_player->counters['robots_active'] > 1 ? 'Robots' : 'Robot');
    $event_body = $this_player->print_player_name().'&#39;s '.($this_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
    //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
    if ($this_player->player_token != 'player'
      && isset($this_player->player_quotes['battle_start'])){
      $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
      $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
      $event_body .= $this_player->print_player_quote('battle_start', $this_find, $this_replace);
    }
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
    $event_options['canvas_show_this'] = true;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target_player'] = false;
    $event_options['canvas_show_target_robots'] = true;
    $this_player->player_frame = 'taunt';
    $this_player->update_session();
    $this_robot->robot_frame = 'taunt';
    $this_robot->robot_frame_styles = '';
    $this_robot->robot_detail_styles = '';
    $this_robot->robot_position = 'active';
    $this_robot->update_session();
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_player->player_frame = 'base';
    $this_player->update_session();
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Queue up this robot's startup action first
    $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    }
  // Otherwise, if there is no target player, have the target's robots display first
  elseif ($target_player->player_token == 'player'){

    // Create the battle start event, showing the points and amount of turns
    $event_header = $first_event_header;
    $event_body = $first_event_body;
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'center';
    $event_options['canvas_show_this'] = $event_options['console_show_this'] = false;
    $event_options['canvas_show_this_robots'] = false;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['canvas_show_target_robots'] = false;
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_battle->events_create(false, false, '', '');

    // Queue up an the target robot's startup action
    $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    // Check to see if this player has more than one robot
    if ($this_player->counters['robots_active'] > 1){

      // Create the enter event for this player's robots
      $event_header = "{$this_player->player_name}&#39;s Robots";
      $event_body = $this_player->print_player_name().'&#39;s '.($this_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
      //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
      if ($this_player->player_token != 'player'
        && isset($this_player->player_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_player->print_player_quote('battle_start', $this_find, $this_replace);
      }
      $event_options = array();
      $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
      $event_options['canvas_show_this'] = true;
      $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
      $event_options['console_show_this_player'] = true;
      $event_options['console_show_target_player'] = false;
      //$event_options['canvas_show_target_robots'] = false;

    }
    // Otherwise if this player brought a single robot
    else {

      // Create the enter event for this player's robots
      $event_header = "{$this_player->player_name}&#39;s {$this_robot->robot_name}";
      //$event_body = $this_player->print_player_name().'&#39;s '.$this_robot->print_robot_name().' appears the battle field!<br />';
      $event_body = $this_robot->print_robot_name().' enters the battle!<br />';
      //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
      if ($this_robot->robot_token != 'robot'
        && isset($this_robot->robot_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_robot->print_robot_quote('battle_start', $this_find, $this_replace);
      }
      $event_options = array();
      $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
      $event_options['canvas_show_this'] = true;
      $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
      $event_options['console_show_this_robot'] = true;
      $event_options['console_show_target_player'] = false;
      //$event_options['canvas_show_target_robots'] = false;

    }

    // Update player and robot frames then show the event
    $this_player->player_frame = 'taunt';
    $this_player->update_session();
    $this_robot->robot_frame = 'taunt';
    $this_robot->robot_frame_styles = '';
    $this_robot->robot_detail_styles = '';
    $this_robot->robot_position = 'active';
    $this_robot->update_session();
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_player->player_frame = 'base';
    $this_player->update_session();
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();

  }

  // Execute the battle actions
  $this_battle->actions_execute();

  // Show the player's other robots one by one
  foreach ($this_player->values['robots_active'] AS $key => $info){
    if (!preg_match('/display:\s?none;/i', $info['robot_frame_styles'])){ continue; }
    if ($this_robot->robot_id == $info['robot_id']){
      $this_robot->robot_frame = 'taunt';
      $this_robot->robot_frame_styles = '';
      $this_robot->robot_detail_styles = '';
      $this_robot->update_session();
      $this_battle->events_create(false, false, '', '');
      $this_robot->robot_frame = 'base';
      $this_robot->update_session();
    } else {
      $temp_robot = new mmrpg_robot($this_battle, $this_player, $info);
      $temp_robot->robot_frame = 'taunt';
      $temp_robot->robot_frame_styles = '';
      $temp_robot->robot_detail_styles = '';
      $temp_robot->update_session();
      $this_battle->events_create(false, false, '', '');
      $temp_robot->robot_frame = 'base';
      $temp_robot->update_session();
    }
  }

  // Create a final frame before giving control to the user
  $this_battle->events_create(false, false, '', '');

}
// Else if the player is switching robots, they go first
elseif ($this_action == 'switch'){
  // DEBUG
  //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'switch to '.$this_action_token);

  /*
  // Define whether to skip the target's turn based on
  // if this player is replacing a fainted robot
  $skip_target_turn = $this_robot->robot_status == 'disabled' || ($this_robot->robot_status == 'active' && $this_robot->robot_position == 'bench') ? true : false;
  */

  // Increment the battle's turn counter by 1 if zero
  if (empty($this_battle->counters['battle_turn'])){
    $this_battle->counters['battle_turn'] += 1;
    $this_battle->update_session();
  }

  // DEBUG
  //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'now switching to '.$this_action_token.' from '.$this_robot->robot_id.'_'.$this_robot->robot_token.'?');

  // Switching should not take a turn - let's encourage it!
  $skip_target_turn = true;
  // Queue up this robot's switch action first
  $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, 'switch', $this_action_token);

  // Execute the battle actions
  $this_battle->actions_execute();

  // Now loop through the player's active robot to collect the new active robot
  list($temp_robot_id, $temp_robot_token) = explode('_', $this_action_token);
  foreach ($this_player->values['robots_active'] AS $key => $info){
    if ($info['robot_id'] == $temp_robot_id){
      $this_robot->robot_load(array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']));
      //$this_robot->robot_load($info);
      $this_robot->update_session();
      break;
     }
  }


  // Otherwise if the target robot is disabled we have no choice
  if ($target_robot->robot_energy < 1 || $target_robot->robot_status == 'disabled'){
    // Then queue up an the target robot's action first, because it's faster and/or switching
    $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'switch', '');
    // Now execute the stored actions
    $this_battle->actions_execute();
    $this_battle->update_session();
  }

  // Create a flag on this player, preventing multiple switches per turn
  $this_player->flags['switch_used_this_turn'] = true;
  $this_player->update_session();

  // DEBUG
  //$this_battle->events_create($this_robot, $target_robot, 'DEBUG', 'so now i am '.$this_robot->robot_id.'_'.$this_robot->robot_token.'? also '.$this_robot->robot_position.'');


}
// Else if the player's robot is using a scan
elseif ($this_action == 'scan'){
  // Queue up an this robot's scan action and do nothing else, it's a free move
  $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);

}
// Else if the player's robot is using an item (MAYBE NOT?!)
elseif ($this_action == 'ability' && preg_match('/^([0-9]+)_item-/i', $this_action_token)){
  // Create the temporary ability object for this player's robot
  $temp_abilityinfo = array();
  list($temp_abilityinfo['ability_id'], $temp_abilityinfo['ability_token']) = explode('_', $this_action_token); //array('ability_token' => $this_action_token);
  $temp_thisability = new mmrpg_ability($this_battle, $this_player, $this_robot, $temp_abilityinfo);

  // Queue up an this robot's action first, because it's faster
  $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);

  // Now execute the stored actions (and any created in the process of executing them!)
  $this_battle->actions_execute();

  // Update the sesions I guess
  $this_robot->update_session();
  $target_robot->update_session();

  // If this ability was an ITEM, decrease it's quantity in the player's session
  if (preg_match('/^([0-9]+)_item-/i', $this_action_token)){
    // Decrease the quantity of this item from the player's inventory
    list($temp_item_id, $temp_item_token) = explode('_', $this_action_token);
    if (!empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){
      $temp_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
      $temp_quantity -= 1;
      if ($temp_quantity < 0){ $temp_quantity = 0; }
      $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_quantity;
    }
  }

  // Create a flag on this player, preventing multiple items per turn
  $this_player->flags['item_used_this_turn'] = true;
  // Update the counter for items used this battle
  if (!isset($this_player->counters['items_used_this_battle'])){ $this_player->counters['items_used_this_battle'] = 0; }
  $this_player->counters['items_used_this_battle'] += 1;
  // Update the session
  $this_player->update_session();

  // Now execute the stored actions (and any created in the process of executing them!)
  $this_battle->actions_execute();

}
// Else if the player's robot is using an ability
elseif ($this_action == 'ability'){
  // Increment the battle's turn counter by 1
  $this_battle->counters['battle_turn'] += 1;
  $this_battle->update_session();

  // Backup the data for the this robot for later reference
  $backup_this_robot_id = $this_robot->robot_id;
  $backup_this_robot_token = $this_robot->robot_token;
  $backup_this_robot_position = $this_robot->robot_position;

  // Backup the data for the targetted robot for later reference
  $backup_target_robot_id = $target_robot->robot_id;
  $backup_target_robot_token = $target_robot->robot_token;
  $backup_target_robot_position = $target_robot->robot_position;

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // If the current target robot is the active one as well
  if ($this_robot->robot_id != $target_robot->robot_id
    && $target_robot->robot_position == 'active'){
    $active_target_robot = &$target_robot;
  }
  // Otherwise, if the target was a benched robot
  else {
    $active_target_robot = false;
    foreach ($target_player->values['robots_active'] AS $temp_robotinfo){
      if ($temp_robotinfo['robot_position'] == 'active'){
        $temp_robotinfo = array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']);
        $active_target_robot = new mmrpg_robot($this_battle, $target_player, $temp_robotinfo);
        $active_target_robot->update_session();
        break;
      }
    }
    if (empty($active_target_robot)){
      $temp_robotinfo = array_shift(array_values($target_player->values['robots_active']));
      $temp_robotinfo = array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']);
      $active_target_robot = new mmrpg_robot($this_battle, $target_player, $target_player->player_robots[0]);
      $active_target_robot->robot_position = 'active';
      $active_target_robot->update_session();
    }
  }

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // Define the switch change based on remaining energy
  $target_energy_percent = ceil(($active_target_robot->robot_energy / $active_target_robot->robot_base_energy) * 100);
  $target_energy_damage_percent = 100 - $target_energy_percent;

  // Define the switch change based on remaining weapons
  $target_weapons_percent = ceil(($active_target_robot->robot_weapons / $active_target_robot->robot_base_weapons) * 100);
  $target_weapons_damage_percent = 100 - $target_weapons_percent;

  // Collect this player's last action if it exists
  if (!empty($target_player->history['actions'])){
    end($target_player->history['actions']);
    $this_last_action = current($target_player->history['actions']);
    $this_recent_actions = array_slice($target_player->history['actions'], -1, 1, false); //array_slice($target_player->history['actions'], -3, 3, false);
    foreach ($this_recent_actions AS $key => $info){
      $this_recent_actions[$key] = $info['this_action'];
    }
  }
  // Otherwise define an empty action
  else {
    $this_last_action = array('this_action' => '', 'this_action_token' => '');
    $this_recent_actions = array();
  }

  // One in ten chance of switching
  if ($target_energy_damage_percent > 0){ $temp_critical_chance = ceil($target_energy_damage_percent / 3); }
  elseif ($target_weapons_damage_percent > 0){ $temp_critical_chance = ceil($target_weapons_damage_percent / 3); }
  else { $temp_critical_chance = 1; }
  if ($target_player->player_switch != 1){ $temp_critical_chance = ceil($temp_critical_chance * $target_player->player_switch); }
  if ($temp_critical_chance > 100){ $temp_critical_chance = 100; }
  $temp_critical_chance = (int)($temp_critical_chance);

  // Check if the switch should be disabled
  $temp_switch_disabled = false;
  if ($active_target_robot->robot_status != 'disabled' && !empty($active_target_robot->robot_attachments)){
    foreach ($active_target_robot->robot_attachments AS $attachment_token => $attachment_info){
      if (!empty($attachment_info['attachment_switch_disabled'])){ $temp_switch_disabled = true; }
    }
  }

  // Check if switch was successful, else we do ability
  if (!$temp_switch_disabled
    && $target_player->counters['robots_active'] > 1  //true ||
    && $target_energy_damage_percent > 0
    && !in_array('start', $this_recent_actions)
    && !in_array('switch', $this_recent_actions)
    && $this_battle->critical_chance($temp_critical_chance)){
    // Set the target action to the switch type
    $target_action = 'switch';
  }
  // Otherwise default to ability
  else {
    // Set the target action to the ability type
    $target_action = 'ability';
  }

  // DEBUG
  //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, '$temp_critical_chance = '.$temp_critical_chance.'; $target_action = '.$target_action.'; </pre>');

  // Then queue up an the target robot's defined action
  //$this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, $target_action, $target_action_token);

  // Collect the abilities index for the current robot
  $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

  // Create the temporary ability object for this player's robot
  list($temp_id, $temp_token) = explode('_', $this_action_token); //array('ability_token' => $this_action_token);
  $temp_abilityinfo = mmrpg_ability::parse_index_info($temp_abilities_index[$temp_token]);
  $temp_abilityinfo['ability_id'] = $temp_id;
  $temp_thisability = new mmrpg_ability($this_battle, $this_player, $this_robot, $temp_abilityinfo);

  // DEBUG
  //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, '<pre>'.preg_replace('#\s+#', ' ', print_r($temp_abilityinfo, true)).'</pre>');

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // Refresh the backed up target robot
  //$target_robot->robot_load(array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));

  // Back up this temp robot's abilities for later
  $temp_active_target_robot_abilities = $active_target_robot->robot_abilities;

  // Loop through the target robot's current abilities and check weapon energy
  $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
  $temp_abilities_backup = $active_target_robot->robot_abilities;
  foreach ($active_target_robot->robot_abilities AS $key => $token){
    // Collect the data for this ability from the index
    $info = mmrpg_ability::parse_index_info($temp_abilities_index[$token]);
    if (empty($info)){ unset($active_target_robot->robot_abilities[$key]); continue; }
    $temp_ability = new mmrpg_ability($this_battle, $target_player, $active_target_robot, $info);
    // Determine how much weapon energy this should take
    $temp_ability_energy = $active_target_robot->calculate_weapon_energy($temp_ability);
    // If this robot does not have enough energy for the move, remove it
    if ($active_target_robot->robot_weapons < $temp_ability_energy){ unset($active_target_robot->robot_abilities[$key]); continue; }
  }
  // If there are no abilities left to use, the robot will automatically enter a recharge state
  if (empty($active_target_robot->robot_abilities)){ $active_target_robot->robot_abilities[] = 'action-noweapons'; }
  // Update the robot's session with ability changes
  $active_target_robot->update_session();

  // Collect the ability choice from the robot
  $temp_token = mmrpg_robot::robot_choices_abilities(array(
    'this_index' => &$mmrpg_index,
    'this_battle' => &$this_battle,
    'this_field' => &$this_battle->battle_field,
    'this_player' => &$target_player,
    'this_robot' => &$active_target_robot,
    'target_player' => &$this_player,
    'target_robot' => &$this_robot
    ));
  $temp_id = array_search($temp_token, $active_target_robot->robot_abilities);
  if (empty($temp_id)){ $temp_id = $temp_abilities_index[$temp_token]['ability_id']; }
  $target_action_token = $temp_id.'_'.$temp_token;

  // Put the rest of the abilities back into the robot (in case it can use next turn)
  $active_target_robot->robot_abilities = $temp_abilities_backup;
  $active_target_robot->update_session();

  // Now that we're done selecting an ability, reset to normal
  $active_target_robot->robot_abilities = $temp_active_target_robot_abilities;
  $active_target_robot->update_session();

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // Create the temporary ability object for the target player's robot
  $temp_abilityinfo = array();
  list($temp_abilityinfo['ability_id'], $temp_abilityinfo['ability_token']) = explode('_', $target_action_token);
  $temp_indexinfo = mmrpg_ability::parse_index_info($temp_abilities_index[$temp_abilityinfo['ability_token']]);
  $temp_abilityinfo = array_merge($temp_indexinfo, $temp_abilityinfo);
  $temp_targetability = new mmrpg_ability($this_battle, $target_player, $active_target_robot, $temp_abilityinfo);

  // If the target player's temporary ability allows target selection
  //if ($temp_targetability->ability_target == 'select'){
  if ($temp_targetability->ability_target == 'select_target'){
    // DEBUG
    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'Target will use an ability ('.$temp_targetability->ability_name.') with selection!');
    // Select a random active robot on this player's side of the field
    $temp_activerobots = $this_player->values['robots_active'];
    shuffle($temp_activerobots);
    $temp_targetability_targetinfo = array_shift($temp_activerobots);
    if ($temp_targetability_targetinfo['robot_id'] == $this_robot->robot_id){
      $temp_targetability_targetplayer = &$this_player;
      $temp_targetability_targetrobot = &$this_robot;
    } else {
      $temp_targetability_targetplayer = &$this_player;
      $temp_targetability_targetrobot = new mmrpg_robot($this_battle, $this_player, $temp_targetability_targetinfo);
    }
  } elseif ($temp_targetability->ability_target == 'select_this'){
    // DEBUG
    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'Target will use an ability ('.$temp_targetability->ability_name.') with selection!');
    // Select a random active robot on this player's side of the field
    $temp_activerobots = $target_player->values['robots_active'];
    shuffle($temp_activerobots);
    $temp_targetability_targetinfo = array_shift($temp_activerobots);
    if ($temp_targetability_targetinfo['robot_id'] == $active_target_robot->robot_id){
      $temp_targetability_targetplayer = &$target_player;
      $temp_targetability_targetrobot = &$active_target_robot;
    } else {
      $temp_targetability_targetplayer = &$target_player;
      $temp_targetability_targetrobot = new mmrpg_robot($this_battle, $target_player, $temp_targetability_targetinfo);
    }
  } else {
    $temp_targetability_targetplayer = &$this_player;
    $temp_targetability_targetrobot = &$this_robot;
  }

  /*
  // DEBUG
  $this_battle->events_create(false, false, 'DEBUG',
  	'$temp_thisability->ability_speed >= $temp_targetability->ability_speed | '.$temp_thisability->ability_speed.' >= '.$temp_targetability->ability_speed.'<br />'.
    '$this_robot->robot_speed >= $active_target_robot->robot_speed | '.$this_robot->robot_speed.' >= '.$active_target_robot->robot_speed
    );
  */

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // If this robot is faster than the target
  if ($target_action != 'switch' && (
  ($this_robot->robot_speed >= $active_target_robot->robot_speed && $temp_targetability->ability_speed <= $temp_thisability->ability_speed) ||
  ($temp_thisability->ability_speed > $temp_targetability->ability_speed)
  )){

    // Queue up an this robot's action first, because it's faster
    if ($this_robot->robot_id != $target_robot->robot_id && $temp_thisability->ability_target != 'select_this'){
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);');
      $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);
    }
    elseif ($this_robot->robot_id != $target_robot->robot_id && $temp_thisability->ability_target == 'select_this'){
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' $this_battle->actions_append($this_player, $this_robot, $this_player, $target_robot, $this_action, $this_action_token);');
      $this_battle->actions_append($this_player, $this_robot, $this_player, $target_robot, $this_action, $this_action_token);
    }
    else {
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' $this_battle->actions_append($this_player, $this_robot, $this_player, $this_robot, $this_action, $this_action_token);');
      $this_battle->actions_append($this_player, $this_robot, $this_player, $this_robot, $this_action, $this_action_token);
    }

    // Then queue up an the target robot's action second, because it's slower
    // DEBUG
    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' $this_battle->actions_append($target_player, $active_target_robot, $this_player, $temp_targetability_targetrobot, $target_action, $target_action_token);');
    $this_battle->actions_append($target_player, $active_target_robot, $temp_targetability_targetplayer, $temp_targetability_targetrobot, $target_action, $target_action_token);

  }
  // Else if the target robot is faster than this one or it's switching
  else {

    // Then queue up an the target robot's action first, because it's faster and/or switching
    if ($target_action == 'switch'){ $target_action_token = ''; }
    // DEBUG
    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' $this_battle->actions_append($target_player, $active_target_robot, $this_player, $temp_targetability_targetrobot, $target_action, $target_action_token);');
    $this_battle->actions_append($target_player, $active_target_robot, $temp_targetability_targetplayer, $temp_targetability_targetrobot, $target_action, $target_action_token);

    // Now execute the stored actions
    $this_battle->actions_execute();
    $this_battle->update_session();

    // Collect the user ability info if set
    $temp_ability_id = false;
    $temp_ability_token = false;
    $temp_ability_info = array();
    if ($this_action == 'ability'){
      list($temp_ability_id, $temp_ability_token) = explode('_', $this_action_token);
      $temp_ability_info = array('ability_id' => $temp_ability_id, 'ability_token' => $temp_ability_token);
      $temp_ability_object = new mmrpg_ability($this_battle, $this_player, $this_robot, $temp_ability_info);
      $temp_ability_info = $temp_ability_object->export_array();
      //$temp_ability_info['ability_id'] = $temp_ability_id;
      if (!isset($temp_ability_info['ability_target'])){
        //$temp_ability_info['ability_target'] = 'auto';
      }
    }

    // Define the new target robot based on the previous target
    $new_target_robot = false;
    // If this is a special SELECT THIS target ability
    if ($temp_ability_info['ability_target'] == 'select_this'){
      // Check if this robot is targetting itself or a team mate
      if ($this_robot->robot_id == $backup_target_robot_id){
        // Define the new target robot which is actually a team mate
        $new_target_robot = &$this_robot; //new mmrpg_robot($this_battle, $this_player, array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token));;
        // Update the target robot's session
        $new_target_robot->update_session();
        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (1) $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);');
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);
      } else {
        // Define the new target robot which is actually a team mate
        $new_target_robot = new mmrpg_robot($this_battle, $this_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
        // Update the target robot's session
        $new_target_robot->update_session();
        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (2) $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);');
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);
      }
    }
    // If this is a special SELECT TARGET ability
    elseif ($temp_ability_info['ability_target'] == 'select_target'){
      // Define the new target robot which is actually a team mate
      $new_target_robot = new mmrpg_robot($this_battle, $target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
      // Update the target robot's session
      $new_target_robot->update_session();
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (3) $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);');
      // Queue up an this robot's action second, because its slower
      $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);
    }
    // Else if the target was originally active or the ability is set to auto
    elseif ($backup_target_robot_position == 'active' || (!empty($temp_ability_info) && $temp_ability_info['ability_target'] == 'auto')){
      // Define the new target robot which is the current active target robot
      $new_target_robot = new mmrpg_robot($this_battle, $target_player, array('robot_id' => $active_target_robot->robot_id, 'robot_token' => $active_target_robot->robot_token));
      // Update the target robot's session
      $new_target_robot->update_session();
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (4) $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);');
      // Queue up an this robot's action second, because its slower
      $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);
    }
    // Otherwise, if a normal case of targetting
    else {
      // Define the new target robot which is the original request
      $new_target_robot = new mmrpg_robot($this_battle, $target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
      // Update the target robot's session
      $new_target_robot->update_session();
        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (5) $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);');
      // Queue up an this robot's action second, because its slower
      $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);
    }

    // DEBUG
    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'new:'.$new_target_robot->robot_image.'-'.$new_target_robot->robot_id.'-'.$new_target_robot->robot_token.'-'.$new_target_robot->robot_position);

    // Now execute the stored actions
    $this_battle->actions_execute();

  }

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // Refresh the backed up target robot
  $target_robot->robot_load(array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
  if ($target_robot->robot_status == 'disabled'){
    // Reset the target robot to the active one for the sake of auto targetting
    $target_robot->robot_load(array('robot_id' => $active_target_robot->robot_id, 'robot_token' => $active_target_robot->robot_token));
    $target_robot->update_session();
  }

  // Loop through the target robots and hide any disabled robots
  foreach ($target_player->player_robots AS $temp_robotinfo){
    if ($temp_robotinfo['robot_status'] == 'disabled'
      /*&& $temp_robotinfo['robot_position'] == 'bench'*/){
      $temp_robot = new mmrpg_robot($this_battle, $target_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']));
      $temp_robot->flags['apply_disabled_state'] = true;
      //$temp_robot->flags['hidden'] = true;
      $temp_robot->update_session();
    }
  }

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // If the target's was a switch action, also queue up an ability
  if ($target_action == 'switch'){

    // Now execute the stored actions
    $this_battle->actions_execute();

    // Update the active robot reference just in case it has changed
    foreach ($target_player->player_robots AS $temp_robotinfo){
      if ($temp_robotinfo['robot_position'] == 'active'){
        $active_target_robot->robot_load($temp_robotinfo);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
      } else {
        $temp_robot = new mmrpg_robot($this_battle, $target_player, $temp_robotinfo);
        $temp_robot->robot_load($temp_robotinfo);
        $temp_robot->robot_position = 'bench';
        $temp_robot->update_session();
      }

    }
    if (empty($active_target_robot)){
      $active_target_robot->robot_load($target_player->player_robots[0]);
      $active_target_robot->robot_position = 'active';
      $active_target_robot->update_session();
    }

    // Decide which ability this robot will use
    $target_action_token = '';
    // Check if this robot has choice data defined
    if (true){
      // Collect the ability choice from the robot
      $temp_token = mmrpg_robot::robot_choices_abilities(array(
        'this_index' => &$mmrpg_index,
        'this_battle' => &$this_battle,
        'this_field' => &$this_field,
        'this_player' => &$target_player,
        'this_robot' => &$active_target_robot,
        'target_player' => &$this_player,
        'target_robot' => &$this_robot
        ));
      $temp_id = array_search($temp_token, $active_target_robot->robot_abilities);
      if (empty($temp_id)){ $temp_id = $temp_abilities_index[$temp_token]['ability_id']; }
      $target_action_token = $temp_id.'_'.$temp_token;
    }

    // If this robot was targetting itself
    if ($this_robot->robot_id == $target_robot->robot_id){

      // And when the switch is done, queue up an ability for this new target robot to use
      if ($active_target_robot->robot_status != 'disabled' && $active_target_robot->robot_position != 'bench'){
        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (queue new for switched target) $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, \'ability\', $target_action_token);');
        $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, 'ability', $target_action_token);
      }

    }
    // Else if this robot was tartetting a team mate
    elseif ($temp_ability_info['ability_target'] == 'select_this'){

      // And when the switch is done, queue up an ability for this new target robot to use
      if ($active_target_robot->robot_status != 'disabled' && $active_target_robot->robot_position != 'bench'){
        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (queue new for switched target) $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, \'ability\', $target_action_token);');
        $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, 'ability', $target_action_token);
      }

    }
    // Otherwise if this was a normal switch by the target
    else {

      // And when the switch is done, queue up an ability for this new target robot to use
      if ($target_robot->robot_status != 'disabled' && $target_robot->robot_position != 'bench'){
        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (queue new for switched target) $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, \'ability\', $target_action_token);');
        $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'ability', $target_action_token);
      }

    }

  }

  // DEBUG
  if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
  } elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
  }

  // Now execute the stored actions (and any created in the process of executing them!)
  //$temp_this_robot_backup = array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token);
  //$temp_target_robot_backup = array('robot_id' => $target_robot->robot_id, 'robot_token' => $target_robot->robot_token);
  $this_battle->actions_execute();
  //$this_robot = new mmrpg_robot($this_battle, $this_player, $temp_this_robot_backup);
  //$target_robot = new mmrpg_robot($this_battle, $target_player, $temp_target_robot_backup);

  // -- END OF TURN ACTIONS -- //

  // If the battle has not concluded, check the robot attachments
  if ($this_battle->battle_status != 'complete'){

    // DEBUG
    if (empty($this_robot)){
      die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
    } elseif (empty($target_robot)){
      die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
    }


    // Loop through all this player's robots and carry out any end-turn events
    mmrpg_battle::temp_check_robot_attachments($this_battle, $this_player, $this_robot, $target_player, $target_robot);
    mmrpg_battle::temp_check_robot_items($this_battle, $this_player, $this_robot, $target_player, $target_robot);
    mmrpg_battle::temp_check_robot_weapons($this_battle, $this_player, $this_robot, $target_player, $target_robot);

    // Loop through all the target player's robots and carry out any end-turn events
    mmrpg_battle::temp_check_robot_attachments($this_battle, $target_player, $target_robot, $this_player, $this_robot);
    mmrpg_battle::temp_check_robot_items($this_battle, $target_player, $target_robot, $this_player, $this_robot);
    mmrpg_battle::temp_check_robot_weapons($this_battle, $target_player, $target_robot, $this_player, $this_robot);

    // Create an empty field to remove any leftover frames
    $this_battle->events_create(false, false, '', '');

    // If this the player's last robot
    if ($this_player->counters['robots_active'] == 0){
      // Trigger the battle complete event
      $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');
    }
    // Else if the target player's on their last robot
    elseif ($target_player->counters['robots_active'] == 0){
      // Trigger the battle complete event
      $this_battle->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, '', '');
    }

  }

  // Increment the battle's turn counter by 1
  //$this_battle->counters['battle_turn'] += 1;
  //$this_battle->update_session();

  // Unset any item use flags for this player, so they can use one again next turn
  if (isset($this_player->flags['item_used_this_turn'])){
    unset($this_player->flags['item_used_this_turn']);
    $this_player->update_session();
  }

  // Unset any switch use flags for this player, so they can use one again next turn
  if (isset($this_player->flags['switch_used_this_turn'])){
    unset($this_player->flags['switch_used_this_turn']);
    $this_player->update_session();
  }

}

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();

// Set the hidden flag on this robot if necessary
if ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1){
  $this_robot->flags['apply_disabled_state'] = true;
  $this_robot->flags['hidden'] = true;
  $this_robot->update_session();
}

// Set the hidden flag on the target robot if necessary
if ($target_robot->robot_status == 'disabled' || $target_robot->robot_energy < 1){
  $target_robot->flags['apply_disabled_state'] = true;
  $target_robot->flags['hidden'] = true;
  $target_robot->update_session();
}


/*
 * ACTION PROCESSING
 */

if (!isset($_SESSION['GAME']['values']['battle_items'])){
  // Loop through player index if not empty
  $temp_items_array = array();
  foreach ($mmrpg_index['players'] AS $player_token => $player_info){
    if (!empty($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_items'])){
      foreach ($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_items'] AS $token => $count){
        if (!isset($temp_items_array[$token])){ $temp_items_array[$token] = 0; }
        $temp_items_array[$token] += $count;
      }
    }
  }
  // Create the new unified items array in the session
  if (!isset($_SESSION['GAME']['values']['battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = array(); }
  $_SESSION['GAME']['values']['battle_items'] = $temp_items_array;
}

// Define the array to hold action panel markup
$actions_markup = array();


// Define the default this and target ids and tokens to reload
$temp_this_reload_robot = array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token);
$temp_target_reload_robot = array('robot_id' => $target_robot->robot_id, 'robot_token' => $target_robot->robot_token);

// If this robot is not active, check to see if there's one that is
if ($this_robot->robot_position != 'active'){
  // Define the variable to hold active robot info and search for it
  $temp_active_info = array();
  foreach ($this_player->player_robots AS $token => $info){
    $temp_robot = new mmrpg_robot($this_battle, $this_player, $info);
    if ($info['robot_status'] == 'active'){ $temp_robot->robot_frame = 'base'; }
    if ($info['robot_position'] == 'active'){ $temp_active_info = $info;  }
    $temp_robot->update_session();
  }
  // If something was found, upload the reload for this robot
  if (!empty($temp_active_info)){
    $temp_this_reload_robot['robot_id'] = $temp_active_info['robot_id'];
    $temp_this_reload_robot['robot_token'] = $temp_active_info['robot_token'];
  }
}

// If target robot is not active, check to see if there's one that is
if ($target_robot->robot_position != 'active'){
  // Define the variable to hold active robot info and search for it
  $temp_active_info = array();
  foreach ($target_player->player_robots AS $token => $info){
    $temp_robot = new mmrpg_robot($this_battle, $target_player, $info);
    if ($info['robot_status'] == 'active'){ $temp_robot->robot_frame = 'base'; }
    if ($info['robot_position'] == 'active'){ $temp_active_info = $info;  }
    $temp_robot->update_session();
  }
  // If something was found, upload the reload for this robot
  if (!empty($temp_active_info)){
    $temp_target_reload_robot['robot_id'] = $temp_active_info['robot_id'];
    $temp_target_reload_robot['robot_token'] = $temp_active_info['robot_token'];
  }
  // Update the player session
  $target_player->update_session();
}

// Refresh the settings for both robots with recent changes
$this_robot->robot_load($temp_this_reload_robot);
$target_robot->robot_load($temp_target_reload_robot);

// Ensure the battle is still in progress
if (empty($this_redirect) && $this_battle->battle_status != 'complete'){

  // Require the option actions
  require_once('data/actions/option.php');

  // Require the ability actions
  $temp_player_ability_actions = array();
  require_once('data/actions/ability.php');

  // Require the item actions
  $temp_player_item_actions = array();
  require_once('data/actions/item.php');

  // Require the switch actions
  require_once('data/actions/switch.php');

  // Require the target actions
  require_once('data/actions/target_this.php');
  require_once('data/actions/target_this_disabled.php');
  require_once('data/actions/target_target.php');

  // Require the scan actions
  require_once('data/actions/scan.php');

  // Require the battle actions
  require_once('data/actions/battle.php');

}
// Otherwise, if the battle has ended
elseif (empty($this_redirect) && $this_battle->battle_status == 'complete'){

  // Require the option actions
  require_once('data/actions/option.php');

  // Require the complete actions
  require_once('data/actions/complete.php');

}

// If possible, attempt to save the game to the session with recent changes
//if (!empty($this_save_filepath)){  // Why were we writing to the database/file soooo often?
if (!empty($this_save_filepath) && $this_battle->battle_status == 'complete'){
  // Save the game session
  mmrpg_save_game_session($this_save_filepath);
}

// Determine the next action based on everything that's happened
if (empty($this_redirect)){
  $this_next_action = 'battle';
  if (($this_robot->robot_status == 'disabled' || $this_robot->robot_position != 'active')
    && $this_battle->battle_status != 'complete'){
    $this_next_action = 'switch';
  }
}

// Define the canvas refresh flag
$canvas_refresh = false;

// Refresh the active robot on this and the target's side of the field
$this_robot = $this_player->get_active_robot();
$target_robot = $target_player->get_active_robot();

/*
$active_target_robot = false;
foreach ($target_player->player_robots AS $temp_robotinfo){
  if (empty($active_target_robot) && $temp_robotinfo['robot_position'] == 'active'){
    $active_target_robot = new mmrpg_robot($this_battle, $target_player, $temp_robotinfo);
    if ($active_target_robot->robot_energy < 1){
      $active_target_robot->flags['apply_disabled_state'] = true;
      $active_target_robot->flags['hidden'] = true;
      $active_target_robot->robot_status = 'disabled';
      $active_target_robot->update_session();
      $canvas_refresh = true;
    }
  } elseif (!empty($active_target_robot) && $temp_robotinfo['robot_position'] == 'active'){
    $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $temp_robotinfo);
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
  $temp_robots_active_array = $target_player->values['robots_active'];
  $temp_robots_disabled_array = $target_player->values['robots_disabled'];
  if (!empty($temp_robots_active_array)){
    $temp_robots_active_info = array_shift($temp_robots_active_array);
    $active_target_robot = new mmrpg_robot($this_battle, $target_player, $temp_robots_active_info);
    $active_target_robot->robot_position = 'active';
    $active_target_robot->update_session();
  } elseif (!empty($temp_robots_disabled_array)){
    $temp_robots_active_info = array_shift($temp_robots_disabled_array);
    $active_target_robot = new mmrpg_robot($this_battle, $target_player, $temp_robots_active_info);
    $active_target_robot->robot_position = 'active';
    $active_target_robot->update_session();
  } else {
    $active_target_robot = $target_robot;
  }
}
// If the active robot was not the same as the target, update
if (!empty($active_target_robot) && $active_target_robot->robot_id != $target_robot->robot_id){
  $target_robot = $active_target_robot;
  $canvas_refresh = true;
}
*/

// If canvas refresh is needed, create an empty event
//if ($canvas_refresh && $this_battle->battle_status != 'complete'){ $this_battle->events_create(false, false, '', ''); }

// Stop the output buffer and collect contents
$output_buffer_contents = trim(ob_get_clean());

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Data API | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<style type="text/css">
</style>
<script type="text/javascript">
// Ensure this script is loaded via iframe
if (window != window.top){
  // Ensure the parent window knows we've returned
  parent.gameEngineSubmitReturn = true;
  // Redirect the parent window if necessary
  <?if(!empty($this_redirect)):?>
  parent.window.location.href = '<?=$this_redirect?>';
  <?else:?>
  // Update the global battle engine variables
  parent.mmrpg_engine_update({
    this_battle_id : '<?= $this_battle->battle_id ?>',
    this_battle_token : '<?= $this_battle->battle_token ?>',
    this_field_id : '<?= $this_field->field_id ?>',
    this_field_token : '<?= $this_field->field_token ?>',
    this_player_id : '<?= $this_player->player_id ?>',
    this_player_token : '<?= $this_player->player_token ?>',
    this_robot_id : '<?= $this_robot->robot_id ?>',
    this_robot_token : '<?= $this_robot->robot_token ?>',
    target_player_id : '<?= $target_player->player_id ?>',
    target_player_token : '<?= $target_player->player_token ?>',
    target_robot_id : '<?= $target_robot->robot_id ?>',
    target_robot_token : '<?= $target_robot->robot_token ?>',
    this_battle_status : '<?= $this_battle->battle_status ?>',
    this_battle_result : '<?= $this_battle->battle_result ?>',
    next_action : '<?= $this_next_action ?>'
    });
  <?
    // Define a quick function for compressing action markup
    $compress_action_array = array(
      'sprite_right', 'sprite_left', 'player_right', 'player_left', 'ability_right', 'ability_left', 'attachment_right', 'attachment_left',
      'field_multiplier', 'field_type', 'player_type', 'robot_type', 'ability_type', 'attachment_type', 'mugshot_right', 'mugshot_left', 'icon_right', 'icon_left',
      'robot_level', 'robot_experience', 'robot_energy', 'robot_attack', 'robot_defense', 'robot_speed', 'action_ability', 'action_option', 'action_scan', 'action_target', 'action_item',
      'field_name', 'player_name', 'robot_name', 'ability_name', 'player_shadow', 'robot_shadow',
      'class="main_actions main_actions_hastitle', 'class="main_actions', 'class="sub_actions', 'class="canvas_overlay_footer', 'class="overlay_label', 'class="overlay_multiplier',
      'class="button', 'class="text', 'class="subtext', 'class="multi', 'class="type', 'class="number"', 'class="cross"',
      'class="level', 'class="experience', 'class="energy', 'class="attack', 'class="defense', 'class="speed',
      'class="sprite', 'class="field', 'class="player', 'class="robot', 'class="attachment', 'class="mugshot',
      'data-tooltip-align="', 'data-tooltip-type="', 'data-tooltip="', 'data-order="',
      'data-robotid="', 'data-playerid="', 'data-abilityid="', 'data-shadowid="', 'data-mugshotid="', 'data-detailsid="',
      'type="button"', 'data-key="', 'data-type="', 'data-panel="', 'data-action="', 'data-preload="', 'data-size="', 'data-frame="', 'data-scale="', 'data-direction="', 'data-position="', 'data-status="', 'data-target="',
      'images/players_shadows/', 'images/players/', 'images/robots_shadows/', 'images/robots/', 'images/abilities/item-', 'images/abilities/',
      'background-image: url(', 'background-position:', 'background-size:', 'border-color:', '-webkit-transform:', '-moz-transform:', 'transform:', ' translate(', ' rotate(',
      '</div><div ', '</span><span ', '<strong>', '</strong>', '</label>',
      'abilities', 'attachment', 'position', 'disabled', 'prototype', 'maintext',
      'experience', '-support', '-assault', 'buster-shot', 'dr-light', 'dr-cossack', 'light-buster', 'wily-buster', 'cossack-buster', '-support', '-capsule',
      'Active Position', 'Bench Position', 'Abilities', ' Accuracy', ' Weapons', ' Attack', ' Defense', ' Recovery', ' Experience', ' Support', 'Buster Shot', 'Dr. Light', 'Dr. Wily', 'Dr. Cossack', 'Light Buster', 'Wily Buster', 'Cossack Buster', 'Neutral ', ' Capsule',
      ' title="', ' class="', ' style="', 'sprite_40x40_', 'sprite_80x80_', 'sprite_160x160_', '_left_40x40', '_left_80x80', '_left_160x160',  '_right_40x40', '_right_80x80', '_right_160x160',
      '.png?'.MMRPG_CONFIG_CACHE_DATE.');',
      );
    function compress_action_markup($action_markup){
      global $compress_action_array;
      $arrayLength = count($compress_action_array);
      for ($i = 0; $i < $arrayLength; $i++){
        $search = $compress_action_array[$i];
        $replace = '!'.dechex($i).'¡';
        $action_markup = str_replace($search, $replace, $action_markup);
      }
      return $action_markup;
    }
    // If action markup exists, loop through it
    if (!empty($actions_markup)){
      // Update any action panel markup changed by the battle
      foreach($actions_markup AS $action_token => $action_markup){
        //$action_markup = compress_action_markup($action_markup);
        $action_markup = $action_markup;
        $action_markup = str_replace("'", "\\'", $action_markup);
        echo "parent.mmrpg_action_panel_update('{$action_token}', '{$action_markup}');\n";
      }
    }
    // Collect event markup from the battle object
    $events_markup = $this_battle->events_markup_collect();
    // If event markup exists, loop through it
    if (!empty($events_markup)){
      //Print out any event markup generated by the battle
      foreach($events_markup AS $markup){
        $flags_markup = str_replace("'", "\\'", $markup['flags']);
        $data_markup = str_replace("'", "\\'", $markup['data']);
        //$canvas_markup = compress_action_markup($markup['canvas']);
        //$console_markup = compress_action_markup($markup['console']);
        if (!isset($markup['console'])){
          exit(PHP_EOL.'$markup = '.print_r($markup, true));
        }
        $console_markup = $markup['console'];
        $canvas_markup = $markup['canvas'];
        $canvas_markup = str_replace("'", "\\'", $canvas_markup);
        $console_markup = str_replace("'", "\\'", $console_markup);
        echo "parent.mmrpg_event('{$flags_markup}', '{$data_markup}', '{$canvas_markup}', '{$console_markup}');\n";
      }
    }
    // Attempt to print out the events either way
    echo "parent.setTimeout(function(){ return parent.mmrpg_events(); }, parent.gameSettings.eventTimeout);\n";

  ?>
  <?endif;?>
}
<?
// DEBUG
// If output buffer content was created, alert the webmaster of its content
if (!MMRPG_CONFIG_IS_LIVE && !empty($output_buffer_contents)){
  $output_buffer_contents = str_replace("\\", '\\', $output_buffer_contents);
  $output_buffer_contents = str_replace("\n", '\n', $output_buffer_contents);
  $output_buffer_contents = preg_replace('/\s+/', ' ', $output_buffer_contents);
  $output_buffer_contents = str_replace("'", "\'", $output_buffer_contents);
  echo "alert('".$output_buffer_contents."');";
}
?>
<?
/*
// TEMP DEBUG
if (!MMRPG_CONFIG_IS_LIVE || MMRPG_CONFIG_ADMIN_MODE){
  echo "console.log('memory_limit() = ".ini_get('memory_limit')."');\n";
  echo "console.log('memory_get_usage() = ".round(((memory_get_usage() / 1024) / 1024), 2)."M');\n";
  echo "console.log('memory_get_peak_usage() = ".round((memory_get_peak_usage() / 1024) / 1024, 2)."M');\n";
  //echo 'memory_get_peak_usage_peak() = '.memory_get_peak_usage_peak().'<br />';
}
*/
?>
</script>
</head>
<body>
<?
// Unset the database variable
unset($DB);
?>
</body>
</html>