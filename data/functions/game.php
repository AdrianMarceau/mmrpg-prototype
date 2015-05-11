<?php

if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Define a function for collecting the current GAME token
function mmrpg_game_token(){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  if (defined('MMRPG_REMOTE_GAME')){ return 'REMOTE_GAME_'.MMRPG_REMOTE_GAME; }
  else { return 'GAME'; }
}

// Define a function for checking if we're in demo mode
function mmrpg_game_demo(){
  if (!empty($_SESSION[$session_token]['DEMO'])){ return true; } // Demo flag exists, so true
  elseif ($_SESSION[$session_token]['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID){ return true; } // User ID is guest, so true
  else { return false; }  // Demo flag doesn't exist, must be logged in
}

// Define a function for making a javascript-based alert
function mmrpg_debug_alert($alert_string, $echo = true){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $alert_string = str_replace("\n", '\\n', str_replace('"', '\"', htmlentities($alert_string)));
  $script_string = '<script type="text/javascript">alert("'.$alert_string.'");</script>';
  if ($echo){ echo $script_string;  }
  return $script_string;
}

// Define a function for unlocking a game player for use in battle
function mmrpg_game_unlock_player($player_info, $unlock_robots = true, $unlock_abilities = true){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Reference the global variables
  global $mmrpg_index, $DB;
  //$GAME_SESSION = &$_SESSION[mmrpg_game_token()];
  $session_token = mmrpg_game_token();
  // If the player token does not exist, return false
  if (!isset($player_info['player_token'])){ return false; }
  // If this player does not exist in the global index, return false
  if (!isset($mmrpg_index['players'][$player_info['player_token']])){ return false; }
  // Collect the player info from the index
  $player_info = array_replace($mmrpg_index['players'][$player_info['player_token']], $player_info);
  // Collect or define the player points and player rewards variables
  $this_player_token = $player_info['player_token'];
  $this_player_points = !empty($player_info['player_points']) ? $player_info['player_points'] : 0;
  $this_player_rewards = !empty($player_info['player_rewards']) ? $player_info['player_rewards'] : array();
  // Automatically unlock this player for use in battle then create the settings array
  $this_reward = array('player_token' => $this_player_token, 'player_points' => $this_player_points);
  $_SESSION[$session_token]['values']['battle_rewards'][$this_player_token] = $this_reward;
  if (empty($_SESSION[$session_token]['values']['battle_settings'][$this_player_token])
    || count($_SESSION[$session_token]['values']['battle_settings'][$this_player_token]) < 8){
    $this_setting = array('player_token' => $this_player_token, 'player_robots' => array());
    $_SESSION[$session_token]['values']['battle_settings'][$this_player_token] = $this_setting;
  }
  // Loop through the robot rewards for this player if set
  if ($unlock_robots && !empty($this_player_rewards['robots'])){
    $temp_robots_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    foreach ($this_player_rewards['robots'] AS $robot_reward_key => $robot_reward_info){
      // Check if the required amount of points have been met by this player
      if ($this_player_points >= $robot_reward_info['points']){
        // Unlock this robot and all abilities
        $this_robot_info = mmrpg_robot::parse_index_info($temp_robots_index[$robot_reward_info['token']]);
        $this_robot_info['robot_level'] = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
        $this_robot_info['robot_experience'] = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
        mmrpg_game_unlock_robot($player_info, $this_robot_info, true, false);
      }
    }
  }
  // Loop through the ability rewards for this player if set
  if ($unlock_abilities && !empty($this_player_rewards['abilities'])){
    // Collect the ability index for calculation purposes
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $this_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    foreach ($this_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){
      // Check if the required amount of points have been met by this player
      if ($this_player_points >= $ability_reward_info['points']){
        // Unlock this ability
        $this_ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_reward_info['token']]);
        mmrpg_game_unlock_ability($player_info, false, $this_ability_info);
      }
    }
  }
  // Return true on success
  return true;
}
// Define a function for unlocking a game robot for use in battle
function mmrpg_game_unlock_robot($player_info, $robot_info, $unlock_abilities = true, $create_event = true){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Reference the global variables
  global $mmrpg_index, $DB;
  //$_SESSION[$session_token] = &$_SESSION[mmrpg_game_token()];
  $session_token = mmrpg_game_token();
  // If the robot token does not exist, return false
  if (!isset($robot_info['robot_token'])){ return false; }
  // If this robot does not exist in the global index, return false
  //if (!isset($player_info['player_token'])){ echo 'player_info<pre>'.print_r($player_info, true).'</pre>'; }
  $player_index_info = $mmrpg_index['players'][$player_info['player_token']];
  $robot_index_info = $robot_info;
  if (!isset($player_index_info)){ return false; }
  if (!isset($robot_index_info)){ return false; }
  // Collect the robot info from the inde
  $this_robot_token = $robot_info['robot_token'];
  $this_player_token = $player_info['player_token'];
  $this_robot_level = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
  $this_robot_experience = !empty($robot_info['robot_experience']) ? $robot_info['robot_experience'] : 0;
  $player_info = array_replace($player_index_info, $player_info);
  $robot_info = array_replace($robot_index_info, $robot_info);
  // Collect or define the robot points and robot rewards variables
  $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();
  // DEBUG DEBUG DEBUG
  //if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['unlock_robots'][$this_robot_token] = array();  }
  // Automatically unlock this robot for use in battle and create the settings array
  $this_reward = array(
    'flags' => array(),
    'values' => array(),
    'counters' => array(),
    'robot_token' => $this_robot_token,
    'robot_level' => $this_robot_level,
    'robot_experience' => $this_robot_experience,
    'robot_energy' => 0,
    'robot_attack' => 0,
    'robot_defense' => 0,
    'robot_speed' => 0,
    'robot_energy_pending' => 0,
    'robot_attack_pending' => 0,
    'robot_defense_pending' => 0,
    'robot_speed_pending' => 0
    );
  $_SESSION[$session_token]['values']['battle_rewards'][$player_info['player_token']]['player_robots'][$this_robot_token] = $this_reward;
  if (empty($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'])
    || empty($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$this_robot_token])
    || count($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots']) < 8){
    $this_setting = array(
    	'flags' => array(),
    	'values' => array(),
    	'counters' => array(),
    	'robot_token' => $this_robot_token,
    	'robot_abilities' => array(),
    	'original_player' => $player_info['player_token']
      );
    $_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$this_robot_token] = $this_setting;
  }
  // Loop through the ability rewards for this robot if set
  if ($unlock_abilities && !empty($this_robot_rewards['abilities'])){
    // Collect the ability index for calculation purposes
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $this_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    foreach ($this_robot_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){
      // Check if the required amount of points have been met by this robot
      if ($this_robot_level >= $ability_reward_info['level']){
        // DEBUG DEBUG DEBUG
        //if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['unlock_robots'][$this_robot_token][] = $ability_reward_info['token'];  }
        // Unlock this ability
        //$this_ability_info = array('ability_token' => $ability_reward_info['token'], 'ability_points' => $ability_reward_info['level']);
        $this_ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_reward_info['token']]);
        $this_ability_info['ability_points'] = $ability_reward_info['level'];
        mmrpg_game_unlock_ability($player_info, $robot_info, $this_ability_info);
      }
    }
  }
  // Add this robot to the global robot database array
  $temp_data_existed = !empty($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]) ? true : false;
  if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token] = array('robot_token' => $this_robot_token); }
  if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_unlocked'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_unlocked'] = 1; }
  if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_summoned'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_summoned'] = 0; }
  if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_encountered'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_encountered'] = 0; }
  if (!isset($_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_scanned'])){ $_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_scanned'] = 0; }
  //$_SESSION[$session_token]['values']['robot_database'][$this_robot_token]['robot_unlocked']++;

  // Only show the event if allowed by the function args
  if ($create_event){

    // Generate the attributes and text variables for this robot unlock
    $robot_info_size = isset($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] * 2 : 40 * 2;
    $robot_info_size_token = $robot_info_size.'x'.$robot_info_size;
    $this_name = $robot_info['robot_name'];
    $this_description = !empty($robot_info['robot_description']) && $robot_info['robot_description'] != '...' ? $robot_info['robot_description'] : '';
    $this_number = $robot_info['robot_number'];
    $this_energy_boost = round($robot_info['robot_energy'] * 0.05, 1);
    $this_attack_boost = round($robot_info['robot_attack'] * 0.05, 1);
    $this_defense_boost = round($robot_info['robot_defense'] * 0.05, 1);
    $this_speed_boost = round($robot_info['robot_speed'] * 0.05, 1);
    $this_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
    $this_replace = array($player_info['player_name'], $robot_info['robot_name'], $player_info['player_name'], ($this_player_token == 'dr-light' ? 'Mega Man' : ($this_player_token == 'dr-wily' ? 'Bass' : ($this_player_token == 'dr-cossack' ? 'Proto Man' : 'Robot'))));
    $this_quote = !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($this_find, $this_replace, $robot_info['robot_quotes']['battle_taunt']) : '...';
    $this_field = mmrpg_field::get_index_info(!empty($robot_info['robot_field']) ? $robot_info['robot_field'] : 'intro-field');
    $this_pronoun = 'he'; $this_posessive = 'his';
    if (in_array($robot_info['robot_token'], array('roll', 'disco', 'rhythm', 'splash-woman'))){ $this_pronoun = 'she'; $this_posessive = 'her'; }
    elseif (in_array($robot_info['robot_token'], array('met'))){ $this_pronoun = 'it'; $this_posessive = 'its'; }
    $this_best_stat = $robot_info['robot_energy'];
    $this_best_attribute = 'a support';
    if ($robot_info['robot_attack'] > $this_best_stat){ $this_best_stat = $robot_info['robot_attack']; $this_best_attribute = 'a powerful'; }
    elseif ($robot_info['robot_defense'] > $this_best_stat){ $this_best_stat = $robot_info['robot_defense']; $this_best_attribute = 'a defensive'; }
    elseif ($robot_info['robot_speed'] > $this_best_stat){ $this_best_stat = $robot_info['robot_speed']; $this_best_attribute = 'a speedy'; }
    if ($robot_info['robot_token'] == 'met'){ $this_best_attribute = 'bonus'; }
    $this_first_ability = array('level' => 0, 'token' => 'buster-shot');
    $this_count_abilities = count($robot_info['robot_rewards']['abilities']);
    //die('<pre>'.print_r($robot_info['robot_rewards']['abilities'], true).'</pre>');
    foreach ($robot_info['robot_rewards']['abilities'] AS $temp_key => $temp_reward){ if ($temp_reward['token'] != 'buster-shot' && $temp_reward['level'] > 0){ $this_first_ability = $temp_reward; break; } }
    $temp_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    $this_first_ability_name = $temp_ability_index[$this_first_ability['token']]['ability_name'];
    //die('<pre>'.print_r($this_first_ability, true).'</pre>');
    if ($robot_info['robot_token'] == 'oil-man' || $robot_info['robot_token'] == 'time-man'){ $this_first_appearance = 'that first appeared in <em>Mega Man Powered Up</em> for the Sony PlayStation Portable'; }
    elseif ($robot_info['robot_game'] == 'MM01' || $robot_info['robot_token'] == 'mega-man' || $robot_info['robot_token'] == 'roll'){ $this_first_appearance = 'that first appeared in the original <em>Mega Man</em> on the Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM02'){ $this_first_appearance = 'that first appeared in <em>Mega Man 2</em> for the Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM03' || $robot_info['robot_token'] == 'proto-man'){ $this_first_appearance = 'that first appeared in <em>Mega Man 3</em> for the Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM04'){ $this_first_appearance = 'that first appeared in <em>Mega Man 4</em> for the Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM05'){ $this_first_appearance = 'that first appeared in <em>Mega Man 5</em> for the Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM06'){ $this_first_appearance = 'that first appeared in <em>Mega Man 6</em> for the Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM07' || $robot_info['robot_token'] == 'bass'){ $this_first_appearance = 'that first appeared in <em>Mega Man 7</em> for the Super Nintendo Entertainment System'; }
    elseif ($robot_info['robot_game'] == 'MM08' || $robot_info['robot_token'] == 'duo'){ $this_first_appearance = 'that first appeared in <em>Mega Man 8</em> for the Sega Saturn and Sony PlayStation'; }
    elseif ($robot_info['robot_game'] == 'MM085'){ $this_first_appearance = 'that first appeared in <em title="Rockman &amp; Forte in Japan">Mega Man &amp; Bass</em> for the Super Nintendo Entertainment System and Nintendo Game Boy Advance'; }
    elseif ($robot_info['robot_game'] == 'MM09'){ $this_first_appearance = 'that first appeared in <em>Mega Man 9</em> for Nintendo Wii, Sony PlayStation 3, and Xbox 360'; }
    elseif ($robot_info['robot_game'] == 'MM10'){ $this_first_appearance = 'that first appeared in <em>Mega Man 10</em> for Nintendo Wii, Sony PlayStation 3, and Xbox 360'; }
    elseif ($robot_info['robot_game'] == 'MM21'){ $this_first_appearance = 'that first appeared in <em>Mega Man : The Wily Wars</em> for Sega Mega Drive'; }
    elseif ($robot_info['robot_game'] == 'MM30'){ $this_first_appearance = 'that first appeared in <em>Mega Man V</em> for Nintendo Game Boy'; }
    elseif ($robot_info['robot_token'] == 'disco' || $robot_info['robot_token'] == 'rhythm'){ $this_first_appearance = 'making her debut in the <em>Mega Man RPG Prototype</em>'; }
    elseif ($robot_info['robot_token'] == 'bond-man'){ $this_first_appearance = 'making his first playable debut in the <em>Mega Man RPG Prototype</em>'; }
    elseif ($robot_info['robot_token'] == 'enker'){ $this_first_appearance = 'that first appeared in <em>Mega Man : Dr. Wily\'s Revenge</em> for the Nintendo Game Boy'; }
    elseif ($robot_info['robot_token'] == 'punk'){ $this_first_appearance = 'that first appeared in <em>Mega Man III</em> for the Nintendo Game Boy'; }
    elseif ($robot_info['robot_token'] == 'ballade'){ $this_first_appearance = 'that first appeared in <em>Mega Man IV</em> for the Nintendo Game Boy'; }
    elseif ($robot_info['robot_token'] == 'quint'){ $this_first_appearance = 'that first appeared in <em>Mega Man II</em> for the Nintendo Game Boy'; }
    elseif ($robot_info['robot_token'] == 'solo'){ $this_first_appearance = 'that first appeared in <em>Mega Man Star Force 3</em> for the Nintendo DS'; }
    elseif (preg_match('/^duo/i', $robot_info['robot_token'])){ $this_first_appearance = 'that first appeared in <em>Mega Man 7</em> for the Super Nintendo Entertainment System'; }
    elseif (preg_match('/^trio/i', $robot_info['robot_token'])){ $this_first_appearance = 'making their debut in the <em>Mega Man RPG Prototype</em>'; }
    if ($this_first_ability['level'] == 0){ $this_level = 1; }
    else { $this_level = $this_first_ability['level']; }
    $this_weaknesses = !empty($robot_info['robot_weaknesses']) ? $robot_info['robot_weaknesses'] : array();
    $this_resistances = !empty($robot_info['robot_resistances']) ? $robot_info['robot_resistances'] : array();
    $this_affinities = !empty($robot_info['robot_affinities']) ? $robot_info['robot_affinities'] : array();
    $this_immunities = !empty($robot_info['robot_immunities']) ? $robot_info['robot_immunities'] : array();
    foreach ($this_weaknesses AS $key => $token){ $this_weaknesses[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
    foreach ($this_resistances AS $key => $token){ $this_resistances[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
    foreach ($this_affinities AS $key => $token){ $this_affinities[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
    foreach ($this_immunities AS $key => $token){ $this_immunities[$key] = '<strong class="ability_type ability_type_'.$token.'">'.ucfirst($token).'</strong>'; }
    //$this_weaknesses = implode(', ', $this_weaknesses);
    //$this_resistances = implode(', ', $this_resistances);
    //$this_affinities = implode(', ', $this_affinities);
    //$this_immunities = implode(', ', $this_immunities);
    // Generate the window event's canvas and message markup then append to the global array
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_'.$robot_info_size_token.' sprite_'.$robot_info_size_token.'_victory" style="background-image: url(images/robots/'.$robot_info['robot_token'].'/sprite_right_'.$robot_info_size_token.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: '.(200 - (($robot_info_size - 80) * 0.5)).'px;">'.$robot_info['robot_name'].'</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/'.$player_info['player_token'].'/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">'.$player_info['player_name'].'</div>';
    //$temp_console_markup = '<p>Congratulations!  <strong>'.$player_info['player_name'].'</strong> unlocked <strong>'.$this_name.'</strong> '.(!empty($this_description) ? '- the '.str_replace('Robot', 'robot', $this_description).' -' : '').' ('.$this_number.') as a playable character! &quot;<em>'.$this_quote.'</em>&quot; <strong>'.$this_name.'</strong> is '.$this_best_attribute.' '.(!empty($robot_info['robot_core']) ? '<strong class="robot_type robot_type_'.$robot_info['robot_core'].'">'.ucfirst($robot_info['robot_core']).' Core</strong> ' : '<strong class="robot_type robot_type_none">Neutral Core</strong> ').'robot '.$this_first_appearance.'.</p>';
    $temp_console_markup = '<p>Congratulations!  <strong>'.$player_info['player_name'].'</strong> unlocked <strong>'.$this_name.'</strong> as a playable character! <strong>'.$this_name.'</strong> is '.$this_best_attribute.' '.(!empty($robot_info['robot_core']) ? '<strong data-class="robot_type robot_type_'.$robot_info['robot_core'].'">'.ucfirst($robot_info['robot_core']).' Core</strong> ' : '<strong data-class="robot_type robot_type_none">Neutral Core</strong> ').'robot '.$this_first_appearance.'. <strong>'.$this_name.'</strong>&#39;s data was '.($temp_data_existed ? 'updated in ' : 'added to ' ).' the <strong>Robot Database</strong>.</p>';
    $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', mmrpg_robot::print_database_markup($robot_info, array('layout_style' => 'event'))).'</div></div></div>';
    //die(''.$this_robot_token.': '.$temp_console_markup);

    $_SESSION[$session_token]['EVENTS'][] = array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      );

  }

  // Return true on success
  return true;
}
// Define a function for unlocking a game ability for use in battle
function mmrpg_game_unlock_ability($player_info, $robot_info, $ability_info){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  //$GAME_SESSION = &$_SESSION[mmrpg_game_token()];
  $session_token = mmrpg_game_token();
  // If the ability token does not exist, return false
  if (!isset($ability_info['ability_token'])){ return false; }
  // Attempt to collect info for this ability
  $ability_index = $ability_info;
  // If this ability does not exist in the global index, return false
  if (empty($ability_index)){ return false; }
  // Collect the ability info from the index
  $ability_info = array_replace($ability_index, $ability_info);
  // Collect or define the ability variables
  $this_ability_token = $ability_info['ability_token'];
  // Automatically unlock this ability for use in battle
  $this_reward = array('ability_token' => $this_ability_token);
  // Check if this is being awarded to a specific robot or a player
  if (!empty($robot_info)){
    $_SESSION[$session_token]['values']['battle_rewards'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities'][$this_ability_token] = $this_reward;
    if (empty($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities'])
      || count($_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities']) < 8){
      $this_setting = array('ability_token' => $this_ability_token);
      $_SESSION[$session_token]['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_info['robot_token']]['robot_abilities'][$this_ability_token] = $this_setting;
    }
  } else {
    $_SESSION[$session_token]['values']['battle_rewards'][$player_info['player_token']]['player_abilities'][$this_ability_token] = $this_reward;
  }
  // Return true on success
  return true;
}
// Define a function for updating a player setting for use in battle
function mmrpg_game_player_setting($player_info, $setting_token, $setting_value){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Reference the global variables
  //global $mmrpg_index;
  // Update or create the player setting in the session
  $player_token = $player_info['player_token'];
  $_SESSION[mmrpg_game_token()]['values']['battle_settings'][$player_token][$setting_token] = $setting_value;
  // Return true on success
  return true;
}
// Define a function for updating a player setting for use in battle
function mmrpg_game_robot_setting($player_info, $robot_info, $setting_token, $setting_value){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Reference the global variables
  //global $mmrpg_index;
  // Update or create the player setting in the session
  $player_token = $player_info['player_token'];
  $robot_token = $robot_info['robot_token'];
  $_SESSION[mmrpg_game_token()]['values']['battle_settings'][$player_token]['player_robots'][$robot_token][$setting_token] = $setting_value;
  // Return true on success
  return true;
}

// Define a function for saving the game session
require(MMRPG_CONFIG_ROOTDIR.'data/functions/game_reset-game-session.php');

// Define a function for saving the game session
require(MMRPG_CONFIG_ROOTDIR.'data/functions/game_save-game-session.php');

// Define a function for loading the game session
require(MMRPG_CONFIG_ROOTDIR.'data/functions/game_load-game-session.php');

?>