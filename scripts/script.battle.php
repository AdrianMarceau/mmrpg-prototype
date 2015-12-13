<?php

// Include the application TOP file
$temp_path = str_replace('\\', '/', dirname(dirname(__FILE__)));
require($temp_path.'/_top.php');

header('Content-type: text/plain;');

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
$this_user_id = isset($_REQUEST['this_user_id']) ? $_REQUEST['this_user_id'] : $this_userid;
$this_player_id = isset($_REQUEST['this_player_id']) ? $_REQUEST['this_player_id'] : $this_user_id;
$this_player_token = isset($_REQUEST['this_player_token']) ? $_REQUEST['this_player_token'] : 'player';
$this_player_robots = !empty($_REQUEST['this_player_robots']) ? $_REQUEST['this_player_robots'] : '00_robot';
$this_robot_id = isset($_REQUEST['this_robot_id']) ? $_REQUEST['this_robot_id'] : 1;
$this_robot_token = isset($_REQUEST['this_robot_token']) ? $_REQUEST['this_robot_token'] : 'robot';
$target_user_id = isset($_REQUEST['target_user_id']) ? $_REQUEST['target_user_id'] : MMRPG_SETTINGS_TARGET_PLAYERID;
$target_player_id = isset($_REQUEST['target_player_id']) ? $_REQUEST['target_player_id'] : $target_user_id;
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
    'this_battle_id = '.$this_battle_id.'; $this_battle_token = '.$this_battle_token.';';
}
if (empty($this_field_id) || empty($this_field_token)){
  $this_verified = false;
  $this_errors[] = 'This field token was not received! '.
    'this_field_id = '.$this_field_id.'; $this_field_token = '.$this_field_token.';';
}
if (empty($this_player_id) || empty($this_player_token)){
  $this_verified = false;
  $this_errors[] = 'This player token was not received! '.
    'this_field_id = '.$this_field_id.'; $this_field_token = '.$this_field_token.';';
}
if (empty($target_player_id) || empty($target_player_token)){
  $this_verified = false;
  $this_errors[] = 'Target player token was not received! '.
    'target_player_id = '.$target_player_id.'; $target_player_token = '.$target_player_token.';';
}
if (empty($this_robot_id) || empty($this_robot_token)){
  $this_verified = false;
  $this_errors[] = 'This robot token was not received! '.
    'this_robot_id = '.$this_robot_id.'; $this_robot_token = '.$this_robot_token.';';
}
if (empty($target_robot_id) || empty($target_robot_token)){
  $this_verified = false;
  $this_errors[] = 'Target robot token was not received! '.
    'target_robot_id = '.$target_robot_id.'; $target_robot_token = '.$target_robot_token.';';
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

// Define the current player object using the loaded player data
$this_playerinfo = array('user_id' => $this_user_id, 'player_id' => $this_player_id, 'player_token' => $this_player_token, 'player_autopilot' => false);
$this_playerinfo['player_autopilot'] = 0;
$this_playerinfo['player_side'] = 'left';
// Define the target player object using the loaded player data
$target_playerinfo = array('user_id' => $target_user_id, 'player_id' => $target_player_id, 'player_token' => $target_player_token);
$target_playerinfo['player_autopilot'] = 1;
$target_playerinfo['player_side'] = 'right';

// Define this player's current robot ID and token if either are set to auto
if ($this_robot_id == 'auto' || $this_robot_token == 'auto'){
  $temp_robots_list = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
  $temp_robot_string = array_shift($temp_robots_list);
  list($temp_id, $temp_token) = explode('_', $temp_robot_string);
  $this_robot_id = $temp_id;
  $this_robot_token = $temp_token;
}

// Define the target player's current robot ID and token if either are set to auto
if ($target_robot_id == 'auto' || $target_robot_token == 'auto'){
  $temp_robots_list = strstr($target_player_robots, ',') ? explode(',', $target_player_robots) : array($target_player_robots);
  $temp_robot_string = array_shift($temp_robots_list);
  list($temp_id, $temp_token) = explode('_', $temp_robot_string);
  $target_robot_id = $temp_id;
  $target_robot_token = $temp_token;
}

// Only create the arrays with minimal info if NOT start
if ($this_action != 'start'){

  // Define the battle object using the loaded battle data and update session
  $this_battle = new rpg_battle($this_battleinfo);

  // Load the object data for this field from the session
  $this_field = new rpg_field($this_fieldinfo);

  // Load the object data for this and the target player from the battle
  echo basename(__FILE__).' on line '.__LINE__."\n";
  $this_player = $this_battle->get_player($this_player_id);
  echo basename(__FILE__).' on line '.__LINE__."\n";
  $target_player = $this_battle->get_player($target_player_id);

  // Load the object data for this and the target robot from the battle
  $this_robot = $this_battle->get_robot($this_robot_id);
  $target_robot = $this_battle->get_robot($target_robot_id);

}
// Otherwise, prepopulate their robot arrays
elseif ($this_action == 'start'){

  // Collect the preset battle info from the index
  $this_preset_battle = rpg_battle::get_index_info($this_battleinfo['battle_token']);
  if (!empty($this_preset_battle)){ $this_battleinfo = array_replace($this_preset_battle, $this_battleinfo); }

  // Define the battle object using the loaded battle data and update session
  $this_battle = new rpg_battle($this_battleinfo);

  // Start the battle turn off at zero
  $this_battle->set_counter('battle_turn', 0);

  // Collect preset field info from the battle
  $this_preset_field = $this_battle->get_field_info();
  if (!empty($this_preset_field)){ $this_fieldinfo = array_replace($this_preset_field, $this_fieldinfo); }

  // Collect this player's preset info from the battle
  $this_preset_player = $this_battle->get_this_player();
  if (!empty($this_preset_player)){ $this_playerinfo = array_merge($this_preset_player, $this_playerinfo); }

  // Collect this player's preset robots from the battle
  $this_preset_robots = array();
  if (isset($this_playerinfo['player_robots'])){ $this_preset_robots = $this_playerinfo['player_robots']; }
  $this_playerinfo['player_robots'] = array();

  // Update this player's name if this is a player battle
  if ($this_battle->get_flag('player_battle')){
    $temp_name = !empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
    $this_playerinfo['player_name'] = $temp_name;
  }

  // Update this player's items if they exist in the session
  if (isset($_SESSION['GAME']['values']['battle_items'])){
    $temp_items = $_SESSION['GAME']['values']['battle_items'];
    $this_playerinfo['player_items'] = $temp_items;
  }

  // Collect the target player's preset info from the battle
  $target_preset_player = $this_battle->get_target_player();
  if (!empty($target_preset_player)){ $target_playerinfo = array_merge($target_preset_player, $target_playerinfo); }

  // Collect the target player's preset robots from the battle
  $target_preset_robots = array();
  if (isset($target_playerinfo['player_robots'])){ $target_preset_robots = $target_playerinfo['player_robots']; }
  $target_playerinfo['player_robots'] = array();

  //echo '<pre>$this_battleinfo = '."\n".print_r($this_battleinfo, true).'</pre>'."\n";
  //echo '<pre>$this_playerinfo = '."\n".print_r($this_playerinfo, true).'</pre>'."\n";
  //echo '<pre>$this_preset_robots = '."\n".print_r($this_preset_robots, true).'</pre>'."\n";
  //echo '<pre>$target_playerinfo = '."\n".print_r($target_playerinfo, true).'</pre>'."\n";
  //echo '<pre>$target_preset_robots = '."\n".print_r($target_preset_robots, true).'</pre>'."\n";

  // Load the object data for this field from the session
  $this_field = new rpg_field($this_fieldinfo);

  //echo '<pre>$this_fieldinfo = '."\n".print_r($this_fieldinfo, true).'</pre>'."\n";
  //echo '<pre>$this_field->export_array() = '."\n".print_r($this_field->export_array(), true).'</pre>'."\n";

  // Add the player info to the battle
  $this_battle->add_player($this_playerinfo);
  $this_battle->add_player($target_playerinfo);

  //echo '<pre>$this_playerinfo('.$this_player_id.') = '."\n".print_r($this_playerinfo, true).'</pre>'."\n";
  //echo '<pre>$target_playerinfo('.$target_player_id.') = '."\n".print_r($target_playerinfo, true).'</pre>'."\n";

  // Load the player objects from the battle
  $this_player = $this_battle->get_player($this_player_id);
  $target_player = $this_battle->get_player($target_player_id);

  //echo '<pre>$this_player->export_array() = '."\n".print_r($this_player->export_array(), true).'</pre>'."\n";
  //echo '<pre>$target_player->export_array() = '."\n".print_r($target_player->export_array(), true).'</pre>'."\n";

  // Break apart and filter this player's robots, adding to player and battle
  $this_player_robots_strings = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
  foreach ($this_player_robots_strings AS $temp_key => $temp_string){
    // Break apart the string into robot ID and token
    list($temp_id, $temp_token) = explode('_', $temp_string);
    // Define the basic lookup array for this robot
    $temp_robotinfo = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
    // Collect rewards and settings for human-controller robots
    $temp_robotinfo['robot_level'] = rpg_game::robot_level($this_player_token, $temp_token);
    $temp_robotinfo['robot_experience'] = rpg_game::robot_experience($this_player_token, $temp_token);
    $temp_robotinfo['robot_abilities'] = rpg_game::robot_settings_abilities($this_player_token, $temp_token);
    // If preset info exists, merge it with the lookup array
    if (isset($this_preset_robots[$temp_key])){ $temp_robotinfo = array_merge($this_preset_robots[$temp_key], $temp_robotinfo); }
    // Add this robot to the player to index and apply stat bonuses
    $this_player->add_robot($temp_robotinfo, $temp_key, true);
  }
  // Update this player's count and other stat variables
  $this_player->update_session();

  // Break apart and filter the target player's robots, adding to player and battle
  $target_player_robots_strings = strstr($target_player_robots, ',') ? explode(',', $target_player_robots) : array($target_player_robots);
  foreach ($target_player_robots_strings AS $temp_key => $temp_string){
    // Break apart the string into robot ID and token
    list($temp_id, $temp_token) = explode('_', $temp_string);
    // Define the basic lookup array for this robot
    $temp_robotinfo = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
    // If preset info exists, merge it with the lookup array
    if (isset($target_preset_robots[$temp_key])){ $temp_robotinfo = array_merge($target_preset_robots[$temp_key], $temp_robotinfo); }
    // Add this robot to the player to index and apply stat bonuses
    $target_player->add_robot($temp_robotinfo, $temp_key, true);
  }
  // Update this player's count and other stat variables
  $target_player->update_session();

  //echo '<pre>$this_player->export_array() = '."\n".print_r($this_player->export_array(), true).'</pre>'."\n";
  //echo '<pre>$target_player->export_array() = '."\n".print_r($target_player->export_array(), true).'</pre>'."\n";

  // Load the robot objects from the battle
  $this_robot = $this_battle->get_robot($this_robot_id);
  $target_robot = $this_battle->get_robot($target_robot_id);

  //echo '<pre>$this_robot->export_array() = '."\n".print_r($this_robot->export_array(), true).'</pre>'."\n";
  //echo '<pre>$target_robot->export_array() = '."\n".print_r($target_robot->export_array(), true).'</pre>'."\n";

  //exit('test me');

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

  // Redirect the user back to the prototype screen
  $this_redirect = 'battle.php?'.
    ($flag_wap ? 'wap=true' : 'wap=false').
    '&this_battle_id='.$this_battle_id.
    '&this_battle_token='.$this_battle_token.
    '&this_field_id='.$this_field_id.
    '&this_field_token='.$this_field_token.
    '&this_player_id='.$this_player_id.
    '&this_player_token='.$this_player_token.
    '&this_player_robots='.$this_player_robots.
    '&target_player_id='.$target_player_id.
    '&target_player_token='.$target_player_token.
    '&target_player_robots='.$target_player_robots.
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

  //header('Content-type: text/plain');
  //echo('action start'.PHP_EOL);

  // Define the battle's turn counter and start at 0
  $this_battle->set_counter('battle_turn', 0);

  //echo 'battle_turn = '.$this_battle->get_counter('battle_turn').PHP_EOL;

  $this_battle->increase_counter('battle_turn', 1);

  //echo 'battle_turn = '.$this_battle->get_counter('battle_turn').PHP_EOL;

  //exit('action end'.PHP_EOL);

  // Collect the base point and zenny rewards for this battle
  $target_turns_base = !empty($this_battle->battle_turns_limit) ? $this_battle->battle_turns_limit : 0;
  $target_robots_base = !empty($this_battle->battle_robots_limit) ? $this_battle->battle_robots_limit : 0;
  $reward_points_base = !empty($this_battle->battle_rewards_points) ? number_format($this_battle->battle_rewards_points, 0, '.', ',') : 0;
  $reward_zenny_base = !empty($this_battle->battle_rewards_zenny) ? number_format($this_battle->battle_rewards_zenny, 0, '.', ',') : 0;

  // Define the first event body markup, regardless of player type
  $first_event_header = $this_battle->get_name().' <span class="pipe">|</span> '.$this_field->get_name();
  $first_event_body = $this_battle->get_description().'<br />';
  $first_event_body .= 'Target : '.($this_battle->battle_turns_limit == 1 ? '1 Turn' : $target_turns_base.' Turns').' with '.($this_battle->battle_robots_limit == 1 ? '1 Robot' : $target_robots_base.' Robots');
  $first_event_body .= ' <span class="pipe">|</span> ';
  $first_event_body .= 'Reward : '.($this_battle->battle_rewards_points == 1 ? '1 Point' : $reward_points_base.' Points').' and '.($this_battle->battle_rewards_zenny == 1 ? '1 Zenny' : $reward_zenny_base.' Zenny');
  $first_event_body .= ' <br /> ';

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
  $temp_robots_active = $this_player->get_robots_active();
  foreach ($temp_robots_active AS $key => $temp_robot){
    $temp_robot->set_frame_styles('display:none;');
    $temp_robot->set_detail_styles('display:none;');
  }

  // Hide all the target player's robots by default
  $temp_robots_active = $target_player->get_robots_active();
  foreach ($temp_robots_active AS $key => $temp_robot){
    $temp_robot->set_frame_styles('display:none;');
    $temp_robot->set_detail_styles('display:none;');
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
    $this_battle->events_create();
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Create the enter event for the target player's robots
    $event_header = $target_player->player_name.'&#39;s '.($target_player->counters['robots_active'] > 1 ? 'Robots' : 'Robot');
    $event_body = $target_player->print_name().'&#39;s '.($target_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
    //if (isset($target_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$target_player->player_quotes['battle_start'].'</em>&quot;'; }
    if ($target_player->player_token != 'player'
      && isset($target_player->player_quotes['battle_start'])){
      $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
      $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $target_robot->robot_name);
      $event_body .= $target_player->print_quote('battle_start', $this_find, $this_replace);
    }
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'right';
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target'] = false;
    $event_options['console_show_target_player'] = false;
    $target_player->set_frame('taunt');
    $target_robot->robot_frame = 'taunt';
    $target_robot->robot_frame_styles = '';
    $target_robot->robot_detail_styles = '';
    $target_robot->robot_position = 'active';
    $target_robot->update_session();
    $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);
    $target_player->set_frame('base');
    $target_robot->robot_frame = 'base';
    $target_robot->update_session();
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Then queue up an the target robot's startup action
    $this_battle->actions_append($target_player->get_id(), $target_robot->get_id(), $this_player->get_id(), $this_robot->get_id(), 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    // Create the enter event for this player's robots
    $event_header = "{$this_player->player_name}&#39;s ".($this_player->counters['robots_active'] > 1 ? 'Robots' : 'Robot');
    $event_body = $this_player->print_name().'&#39;s '.($this_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
    //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
    if ($this_player->player_token != 'player'
      && isset($this_player->player_quotes['battle_start'])){
      $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
      $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
      $event_body .= $this_player->print_quote('battle_start', $this_find, $this_replace);
    }
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
    $event_options['canvas_show_this'] = true;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target_player'] = false;
    $event_options['canvas_show_target_robots'] = true;
    $this_player->set_frame('taunt');
    $this_robot->robot_frame = 'taunt';
    $this_robot->robot_frame_styles = '';
    $this_robot->robot_detail_styles = '';
    $this_robot->robot_position = 'active';
    $this_robot->update_session();
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_player->set_frame('base');
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Queue up this robot's startup action first
    $this_battle->actions_append($this_player->get_id(), $this_robot->get_id(), $target_player->get_id(), $target_robot->get_id(), 'start', '');
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
    $this_battle->events_create();

    // Queue up an the target robot's startup action
    $this_battle->actions_append($target_player->get_id(), $target_robot->get_id(), $this_player->get_id(), $this_robot->get_id(), 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    // Check to see if this player has more than one robot
    if ($this_player->counters['robots_active'] > 1){

      // Create the enter event for this player's robots
      $event_header = "{$this_player->player_name}&#39;s Robots";
      $event_body = $this_player->print_name().'&#39;s '.($this_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
      //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
      if ($this_player->player_token != 'player'
        && isset($this_player->player_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_player->print_quote('battle_start', $this_find, $this_replace);
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
      //$event_body = $this_player->print_name().'&#39;s '.$this_robot->print_name().' appears the battle field!<br />';
      $event_body = $this_robot->print_name().' enters the battle!<br />';
      //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
      if ($this_robot->robot_token != 'robot'
        && isset($this_robot->robot_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_robot->print_quote('battle_start', $this_find, $this_replace);
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
    $this_player->set_frame('taunt');
    $this_robot->robot_frame = 'taunt';
    $this_robot->robot_frame_styles = '';
    $this_robot->robot_detail_styles = '';
    $this_robot->robot_position = 'active';
    $this_robot->update_session();
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_player->set_frame('base');
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();

  }

  // Execute the battle actions
  $this_battle->actions_execute();

  // Show the player's other robots one by one
  $temp_robots_active = $this_player->get_robots_active();
  foreach ($temp_robots_active AS $key => $temp_robot){
    $temp_frame_styles = $temp_robot->get_frame_styles();
    if (!preg_match('/display:\s?none;/i', $temp_frame_styles)){ continue; }
    $temp_robot->set_frame_styles('');
    $temp_robot->set_detail_styles('');
    $temp_robot->set_frame('taunt');
    $this_battle->events_create();
    $temp_robot->set_frame('base');
  }

  // Create a final frame before giving control to the user
  $this_battle->events_create();

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
  $this_battle->actions_append($this_player->get_id(), $this_robot->get_id(), $target_player->get_id(), $target_robot->get_id(), 'switch', $this_action_token);

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
    $this_battle->actions_append($target_player->get_id(), $target_robot->get_id(), $this_player->get_id(), $this_robot->get_id(), 'switch', '');
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
  $this_battle->actions_append($this_player->get_id(), $this_robot->get_id(), $target_player->get_id(), $target_robot->get_id(), $this_action, $this_action_token);

}
// Else if the player's robot is using an item (MAYBE NOT?!)
elseif ($this_action == 'ability' && preg_match('/^([0-9]+)_item-/i', $this_action_token)){
  // Create the temporary ability object for this player's robot
  $temp_abilityinfo = array();
  list($temp_abilityinfo['ability_id'], $temp_abilityinfo['ability_token']) = explode('_', $this_action_token); //array('ability_token' => $this_action_token);
  $temp_thisability = new rpg_ability($this_player, $this_robot, $temp_abilityinfo);

  // Queue up an this robot's action first, because it's faster
  $this_battle->actions_append($this_player->get_id(), $this_robot->get_id(), $target_player->get_id(), $target_robot->get_id(), $this_action, $this_action_token);

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
        $active_target_robot = new rpg_robot($target_player, $temp_robotinfo);
        $active_target_robot->update_session();
        break;
      }
    }
    if (empty($active_target_robot)){
      $temp_robotinfo = array_shift(array_values($target_player->values['robots_active']));
      $temp_robotinfo = array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']);
      $active_target_robot = new rpg_robot($target_player, $target_player->player_robots[0]);
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
    && rpg_functions::critical_chance($temp_critical_chance)){
    // Set the target action to the switch type
    $target_action = 'switch';
  }
  // Otherwise default to ability
  else {
    // Set the target action to the ability type
    $target_action = 'ability';
  }

  // DEBUG
  //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'temp_critical_chance = '.$temp_critical_chance.'; $target_action = '.$target_action.'; </pre>');

  // Then queue up an the target robot's defined action
  //$this_battle->actions_append($target_player->get_id(), $target_robot->get_id(), $this_player->get_id(), $this_robot->get_id(), $target_action, $target_action_token);

  // Collect the abilities index for the current robot
  $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

  // Create the temporary ability object for this player's robot
  list($temp_id, $temp_token) = explode('_', $this_action_token); //array('ability_token' => $this_action_token);
  $temp_info = array('ability_id' => $temp_id, 'ability_token' => $temp_token);
  $temp_thisability = new rpg_ability($this_player, $this_robot, $temp_info);

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
  $temp_abilities_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
  $temp_abilities_backup = $active_target_robot->robot_abilities;
  foreach ($active_target_robot->robot_abilities AS $key => $token){
    // Collect the data for this ability from the index
    $temp_info = array('ability_id' => $key, 'ability_token' => $token);
    $temp_ability = new rpg_ability($target_player, $active_target_robot, $temp_info);
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
  $temp_token = $active_target_robot->robot_choices_abilities($this_player, $this_robot);
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
  $temp_targetability = new rpg_ability($target_player, $active_target_robot, $temp_abilityinfo);

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
      $temp_targetability_targetrobot = new rpg_robot($this_player, $temp_targetability_targetinfo);
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
      $temp_targetability_targetrobot = new rpg_robot($target_player, $temp_targetability_targetinfo);
    }
  } else {
    $temp_targetability_targetplayer = &$this_player;
    $temp_targetability_targetrobot = &$this_robot;
  }

  /*
  // DEBUG
  $this_battle->events_create(false, false, 'DEBUG',
  	'temp_thisability->ability_speed >= $temp_targetability->ability_speed | '.$temp_thisability->ability_speed.' >= '.$temp_targetability->ability_speed.'<br />'.
    'this_robot->robot_speed >= $active_target_robot->robot_speed | '.$this_robot->robot_speed.' >= '.$active_target_robot->robot_speed
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
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' $this_battle->actions_append($this_player->get_id(), $this_robot->get_id(), $target_player->get_id(), $target_robot->get_id(), $this_action, $this_action_token);');
      $this_battle->actions_append($this_player->get_id(), $this_robot->get_id(), $target_player->get_id(), $target_robot->get_id(), $this_action, $this_action_token);
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
      $temp_ability_object = new rpg_ability($this_player, $this_robot, $temp_ability_info);
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
        $new_target_robot = new rpg_robot($this_player, array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token));;
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);
      } else {
        // Define the new target robot which is actually a team mate
        $new_target_robot = new rpg_robot($this_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
        // Update the target robot's session
        $new_target_robot->update_session();
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);
      }
    }
    // If this is a special SELECT TARGET ability
    elseif ($temp_ability_info['ability_target'] == 'select_target'){
      // Define the new target robot which is actually a team mate
      $new_target_robot = new rpg_robot($target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
      // Update the target robot's session
      $new_target_robot->update_session();
      // Queue up an this robot's action second, because its slower
      $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);
    }
    // Else if the target was originally active or the ability is set to auto
    elseif ($backup_target_robot_position == 'active' || (!empty($temp_ability_info) && $temp_ability_info['ability_target'] == 'auto')){
      // Define the new target robot which is the current active target robot
      $new_target_robot = new rpg_robot($target_player, array('robot_id' => $active_target_robot->robot_id, 'robot_token' => $active_target_robot->robot_token));
      // Update the target robot's session
      $new_target_robot->update_session();
      // Queue up an this robot's action second, because its slower
      $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);
    }
    // Otherwise, if a normal case of targetting
    else {
      // Define the new target robot which is the original request
      $new_target_robot = new rpg_robot($target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
      // Update the target robot's session
      $new_target_robot->update_session();
      // Queue up an this robot's action second, because its slower
      $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);
    }

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
      $temp_robot = new rpg_robot($target_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']));
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
        $temp_robot = new rpg_robot($target_player, $temp_robotinfo);
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
      $temp_token = $active_target_robot->robot_choices_abilities($this_player, $this_robot);
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
        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__.'_DATA', ' (queue new for switched target) $this_battle->actions_append($target_player->get_id(), $target_robot->get_id(), $this_player->get_id(), $this_robot->get_id(), \'ability\', $target_action_token);');
        $this_battle->actions_append($target_player->get_id(), $target_robot->get_id(), $this_player->get_id(), $this_robot->get_id(), 'ability', $target_action_token);
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
  //$this_robot = new rpg_robot($this_player, $temp_this_robot_backup);
  //$target_robot = new rpg_robot($target_player, $temp_target_robot_backup);

  // -- END OF TURN ACTIONS -- //

  // If the battle has not concluded, check the robot attachments
  if ($this_battle->battle_status != 'complete'){

    // DEBUG
    if (empty($this_robot)){
      die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
    } elseif (empty($target_robot)){
      die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
    }

    // Collect both player's active pokemon
    $this_robots_active = $this_player->get_robots_active();
    $target_robots_active = $this_player->get_robots_active();

    // Loop through this player's robots and apply end-turn checks
    foreach ($this_robots_active AS $key => $active_robot){
      if ($active_robot->get_id() == $this_robot->get_id()){ $active_robot = $this_robot; }
      $active_robot->check_items($target_player, $target_robot);
      $active_robot->check_attachments($target_player, $target_robot);
      $active_robot->check_weapons($target_player, $target_robot);
    }

    // Loop through the target player's robots and apply end-turn checks
    foreach ($target_robots_active AS $key => $active_robot){
      if ($active_robot->get_id() == $target_robot->get_id()){ $active_robot = $target_robot; }
      $active_robot->check_items($this_player, $this_robot);
      $active_robot->check_attachments($this_player, $this_robot);
      $active_robot->check_weapons($this_player, $this_robot);
    }

    // Re-collect both player's active pokemon
    $this_robots_active = $this_player->get_robots_active();
    $target_robots_active = $this_player->get_robots_active();

    // Create an empty field to remove any leftover frames
    $this_battle->events_create();

    // If this the player's last robot
    if (empty($this_robots_active)){
      // Trigger the battle complete event
      $this_battle->trigger_complete($target_player, $target_robot, $this_player, $this_robot);
    }
    // Else if the target player's on their last robot
    elseif (empty($target_robots_active)){
      // Trigger the battle complete event
      $this_battle->trigger_complete($this_player, $this_robot, $target_player, $target_robot);
    }

  }

  // Unset any item use flags for this player, so they can use one again next turn
  if ($this_player->has_flag('item_used_this_turn')){ $this_player->unset_flag('item_used_this_turn'); }
  if ($target_player->has_flag('item_used_this_turn')){ $target_player->unset_flag('item_used_this_turn'); }

  // Unset any switch use flags for this player, so they can use one again next turn
  if ($this_player->has_flag('switch_used_this_turn')){ $this_player->unset_flag('switch_used_this_turn'); }
  if ($target_player->has_flag('switch_used_this_turn')){ $target_player->unset_flag('switch_used_this_turn'); }

}

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();

// Collect both player's active pokemon once again
$this_robots_active = $this_player->get_robots_active();
$target_robots_active = $this_player->get_robots_active();

// Loop through this player's still-active robots and apply disabled checks
foreach ($this_robots_active AS $key => $active_robot){
  if ($active_robot->get_id() == $this_robot->get_id()){ $active_robot = $this_robot; }
  if ($active_robot->get_status() == 'disabled' || $active_robot->get_energy() < 1){
    $active_robot->set_flag('apply_disabled_state', true);
    $active_robot->set_flag('hidden', true);
  }
}

// Loop through the target player's still-active robots and apply disabled checks
foreach ($target_robots_active AS $key => $active_robot){
  if ($active_robot->get_id() == $target_robot->get_id()){ $active_robot = $target_robot; }
  if ($active_robot->get_status() == 'disabled' || $active_robot->get_energy() < 1){
    $active_robot->set_flag('apply_disabled_state', true);
    $active_robot->set_flag('hidden', true);
  }
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

// Collect the resulting battle status
$this_battle_status = $this_battle->get_status();

// Refresh the active robot on this and the target's side of the field
$this_robot = $this_player->get_active_robot();
$target_robot = $target_player->get_active_robot();

// Ensure the battle is still in progress
if (empty($this_redirect) && $this_battle_status != 'complete'){

  // Require the option actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.option.php');

  // Require the ability actions
  $temp_player_ability_actions = array();
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.ability.php');

  // Require the item actions
  $temp_player_item_actions = array();
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.item.php');

  // Require the switch actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.switch.php');

  // Require the target actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.target_this.php');
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.target_this_disabled.php');
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.target_target.php');

  // Require the scan actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.scan.php');

  // Require the battle actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.battle.php');

}
// Otherwise, if the battle has ended
elseif (empty($this_redirect) && $this_battle_status == 'complete'){

  // Require the option actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.option.php');

  // Require the complete actions
  require_once(MMRPG_CONFIG_ROOTDIR.'actions/action.complete.php');

}

// If possible, attempt to save the game to the session with recent changes
if (!empty($this_save_filepath) && $this_battle_status == 'complete'){
  // Save the game session
  rpg_game::save_session($this_save_filepath);
}

// Determine the next action based on everything that's happened
if (empty($this_redirect)){
  $this_next_action = 'battle';
  $this_robot_status = $this_robot->get_status();
  $this_robot_position = $this_robot->get_position();
  if (($this_robot_status == 'disabled' || $this_robot_position != 'active') && $this_battle_status != 'complete'){
    $this_next_action = 'switch';
  }
}

// Stop the output buffer and collect contents
$output_buffer_contents = trim(ob_get_clean());

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Data API | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?= MMRPG_CONFIG_ROOTURL?>" />
<style type="text/css">
</style>
<script type="text/javascript">
// Ensure this script is loaded via iframe
if (window != window.top){
  // Ensure the parent window knows we've returned
  parent.gameEngineSubmitReturn = true;
  // Redirect the parent window if necessary
  <?if(!empty($this_redirect)):?>
  parent.window.location.href = '<?= $this_redirect?>';
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
  <?php
    // If action markup exists, loop through it
    if (!empty($actions_markup)){
      // Update any action panel markup changed by the battle
      foreach($actions_markup AS $action_token => $action_markup){
        $action_markup = $action_markup;
        $action_markup = str_replace("'", "\\'", $action_markup);
        echo "parent.mmrpg_action_panel_update('{$action_token}', '{$action_markup}');\n";
      }
    }
    // Collect event markup from the battle object
    $events_markup = $this_battle->get_events_markup();
    // If event markup exists, loop through it
    if (!empty($events_markup)){
      //Print out any event markup generated by the battle
      foreach($events_markup AS $markup){
        $flags_markup = str_replace("'", "\\'", $markup['flags']);
        $data_markup = str_replace("'", "\\'", $markup['data']);
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
<?php
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
<?php
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
<?php
// Unset the database variable
unset($this_database);
?>
</body>
</html>