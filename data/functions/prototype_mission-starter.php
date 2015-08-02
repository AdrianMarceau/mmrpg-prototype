<?
// Define a function for generating the STARTER missions
function mmrpg_prototype_mission_starter($this_prototype_data, $this_robot_token = 'met', $this_start_level = 1, $this_rescue_token = 'roll', $this_field_token = 'intro-field', $this_start_count = 1, $this_target_class = 'mecha'){
  // Pull in global variables for this function
  global $mmrpg_index, $DB;

  // Collect data on this robot and the rescue robot
  $this_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
  $this_robot_data = mmrpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
  $this_robot_name = $this_robot_data['robot_name'];
  // Populate the battle options with the starter battle option
  $temp_start_abilities = $this_start_count;
  $temp_target_count = $this_start_count;
  $temp_max_count = $this_start_count; //($this_start_count * 3);
  $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
  $temp_battle_omega = array();
  $temp_battle_omega['battle_field_base']['field_id'] = 100;
  $temp_battle_omega['battle_field_base']['field_token'] = $this_field_token; //'intro-field';
  $temp_battle_omega['flags']['starter_battle'] = true;
  $temp_battle_omega['battle_complete'] = false;
  $temp_battle_omega['battle_token'] = $temp_battle_token;
  $temp_battle_omega['battle_size'] = '1x4';
  $temp_battle_omega_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
  if (!empty($temp_battle_omega_complete)){ $temp_battle_omega['battle_complete'] = $temp_battle_omega_complete; }
  if (!empty($temp_battle_omega_complete['battle_count'])){ $temp_target_count += $temp_battle_omega_complete['battle_count'] - 1; }
  if (empty($temp_battle_omega_complete['battle_count'])){ $temp_battle_omega['flags']['starter_battle_firstrun'] = true; }
  if ($temp_target_count > $temp_max_count){ $temp_target_count = $temp_max_count; }
  $temp_battle_omega['battle_level'] = $this_start_level;
  $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
  $temp_battle_omega['battle_name'] = 'Chapter One Intro Battle';
  $temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);
  $temp_battle_omega['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
  $temp_battle_omega['battle_target_player']['player_token'] = 'player';
  $temp_battle_omega['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);
  $temp_mook_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];
  $temp_battle_omega['battle_target_player']['player_robots'] = array();
  $temp_name_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
  $temp_mook_tokens = array();

  // Make copied of the robot level, points, and turns
  $temp_omega_robot_level = $this_start_level;
  // If the battle was already complete, collect its details and modify the mission
  $temp_complete_level = 0;
  $temp_complete_count = 0;
  if (!empty($temp_battle_omega['battle_complete'])){
    if (!empty($temp_battle_omega['battle_complete']['battle_min_level'])){ $temp_complete_level = $temp_battle_omega['battle_complete']['battle_min_level']; }
    else { $temp_complete_level = $temp_omega_robot_level; }
    if (!empty($temp_battle_omega['battle_complete']['battle_count'])){ $temp_complete_count = $temp_battle_omega['battle_complete']['battle_count']; }
    else { $temp_complete_count = 1; }
    //$temp_omega_robot_level = $temp_complete_level + $temp_complete_count - 1;
    // DEBUG
    //echo('battle is complete '.$temp_battle_omega['battle_token'].' | omega robot level'.$temp_omega_robot_level.' | battle_level '.$temp_battle_omega['battle_complete']['battle_level'].' | battle_count '.$temp_battle_omega['battle_complete']['battle_count'].'<br />');
  }

  /// Loop through and add other robots to the battle
  for ($i = 0; $i < $temp_target_count; $i++){
    $temp_clone_robot = $temp_mook_robot;
    $temp_clone_robot['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $i;
    //$temp_clone_robot['robot_level'] = $temp_omega_robot_level;
    $temp_clone_robot['robot_token'] = $this_robot_token;
    $temp_robot_name = $this_robot_name;
    $temp_robot_name_token = $temp_clone_robot['robot_name_token'] = str_replace(' ', '-', strtolower($temp_robot_name));
    if (!isset($temp_mook_tokens[$temp_robot_name_token])){ $temp_mook_tokens[$temp_robot_name_token] = 0; }
    else { $temp_mook_tokens[$temp_robot_name_token]++; }
    if ($temp_target_count > 1){ $temp_clone_robot['robot_name'] = $temp_robot_name.' '.$temp_name_index[$temp_mook_tokens[$temp_robot_name_token]]; }
    else { $temp_clone_robot['robot_name'] = $temp_robot_name; }
    $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_clone_robot;
  }

  // Start all the point-based battle vars at zero
  $temp_battle_omega['battle_points'] = 0;
  $temp_battle_omega['battle_zenny'] = 0;
  $temp_battle_omega['battle_turns'] = 0;
  $temp_battle_omega['battle_robot_limit'] = 0;

  // Loop through all the required robots one last time and do final calculations
  foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot){
    // Update the robot level and battle points plus turns
    if (isset($this_robot_index[$robot['robot_token']])){ $robot = mmrpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]); }
    else { continue; }
    $temp_core_backup = !empty($robot['robot_core']) ? $robot['robot_core'] : '';
    $index = mmrpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
    $robot = array_merge($index, $robot);
    if (!empty($temp_core_backup)){ $robot['robot_core'] = $temp_core_backup; }
    if (!isset($robot['robot_item'])){ $robot['robot_item'] = ''; }

    // Increment allowable robots, points, and turns based on who's in the battle
    if ($robot['robot_class'] == 'mecha'){
      $robot['robot_level'] = $temp_omega_robot_level; //mt_rand(ceil($temp_omega_robot_level / 2), $temp_omega_robot_level);
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
      //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = mmrpg_prototype_generate_abilities($robot, $this_start_level, $temp_start_abilities, $robot['robot_item']);
    }
    elseif ($robot['robot_class'] == 'master'){
      $robot['robot_level'] = $temp_omega_robot_level;
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = mmrpg_prototype_generate_abilities($robot, $this_start_level, $temp_start_abilities, $robot['robot_item']);
    }
    elseif ($robot['robot_class'] == 'boss'){
      $robot['robot_level'] = $temp_omega_robot_level; //mt_rand($temp_omega_robot_level, ceil($temp_omega_robot_level * 2));
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
      //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = mmrpg_prototype_generate_abilities($robot, $this_start_level, $temp_start_abilities, $robot['robot_item']);
    }
    // Increment the battle's turn limit based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }
    // Increment the battle's point reward based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }
    // Increment the battle's zenny reward based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }
    // Increment the battle's robot limit based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }
    // Remove any uncessesary A's from the mecha robots' names
    if ($robot['robot_class'] == 'mecha' && isset($robot['robot_name_token'])){
      if (isset($temp_mook_tokens[$robot['robot_name_token']]) && $temp_mook_tokens[$robot['robot_name_token']] == 0){
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_name'] = str_replace(' A', '', $robot['robot_name']);
      }
    }
  }

  // Fix any zero or invalid battle values
  if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
  else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
  if ($temp_battle_omega['battle_turns'] < 1){ $temp_battle_omega['battle_turns'] = 1; }
  else { $temp_battle_omega['battle_turns'] = ceil($temp_battle_omega['battle_turns']); }
  if ($temp_battle_omega['battle_robot_limit'] < 1){ $temp_battle_omega['battle_robot_limit'] = 1; }
  else { $temp_battle_omega['battle_robot_limit'] = ceil($temp_battle_omega['battle_robot_limit']); }

  // Remove background mechas from view for starter fields
  $temp_battle_omega['battle_field_base']['field_background_attachments'] = array();
  $temp_battle_omega['battle_field_base']['field_foreground_attachments'] = array();

  // Clear any items present in the rewards array
  $temp_battle_omega['battle_rewards']['items'] = array(
    // No item drops for the first stage
    );

  // Clear any robots present in the rewards array
  $temp_battle_omega['battle_rewards']['robots'] = array(
    // No unlockable robots for the first stage
    );

  // Process special actions for the first, actual intro field
  if ($this_field_token == 'intro-field'){

    // Update the music to use the boss them from whichever game is most common right now
    $temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);

    // Add the player's lab to the background field
    $temp_doctor_token = str_replace('dr-', '', $this_prototype_data['this_player_token']);
    $temp_battle_omega['battle_field_base']['field_foreground_attachments']['object_intro-field-'.$temp_doctor_token] = array('class' => 'object', 'size' => 160, 'offset_x' => 12, 'offset_y' => 121, 'offset_z' => 1, 'object_token' => 'intro-field-'.$temp_doctor_token, 'object_frame' => array(0), 'object_direction' => 'right');

    // Otherwise if the rescue has not yet been unlocked as a playable character
    if (!empty($this_rescue_token) && !mmrpg_prototype_robot_unlocked(false, $this_rescue_token)){
      // Add the rescue to the background with animation
      $temp_battle_omega['battle_field_base']['field_foreground_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => 40, 'offset_x' => 91, 'offset_y' => 118, 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => array(8,0,8,0,0), 'robot_direction' => 'right');
    }

    // Update the description text for the battle
    $temp_field_locations = array('light' => 'laboratory', 'wily' => 'castle', 'cossack' => 'citadel');
    $temp_location = $temp_field_locations[$temp_doctor_token];
    //$temp_location = preg_replace('/^([a-z0-9]+)-/i', '', $this_field_token);
    $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's that are' : ' that\'s').' attacking the '.$temp_location.'!';

    // Hard code a small screw drop if this is the first time through
    if (empty($temp_battle_omega['battle_complete'])){
      $temp_battle_omega['battle_turns'] = 3;
      $temp_battle_omega['battle_rewards']['items'][] = array('chance' => 100, 'token' => 'item-screw-small');
    } else {
      $temp_battle_omega['battle_turns'] = 1;
    }

  }
  // Otherwise, if this is one of the home field battles
  elseif (in_array($this_field_token, array('light-laboratory', 'wily-castle', 'cossack-citadel'))){

    // Update the music to use the default one for the doctor stage
    $temp_battle_omega['battle_field_base']['field_music'] = $this_field_token;

    // Update the description text for the battle
    $temp_location = preg_replace('/^([a-z0-9]+)-/i', '', $this_field_token);
    $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' still inside the '.$temp_location.'!';

    // Otherwise if the rescue has not yet been unlocked as a playable character
    if (!empty($this_rescue_token) && !mmrpg_prototype_robot_unlocked(false, $this_rescue_token)){
      // Add the rescue to the background with animation
      $temp_battle_omega['battle_field_base']['field_foreground_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => 40, 'offset_x' => 91, 'offset_y' => 118, 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => array(8,0,8,0,0), 'robot_direction' => 'left');
      // Add the rescue character in an unlockable
      //$temp_battle_omega['battle_rewards']['robots'][] = array('token' => $this_rescue_token, 'level' => $this_start_level);
    }

    // Hard code a large screw drop if this is the first time through, else other stuff
    if (empty($temp_battle_omega['battle_complete'])){
      $temp_battle_omega['battle_turns'] = 6;
      $temp_battle_omega['battle_rewards']['items'][] = array('chance' => 100, 'token' => 'item-screw-large');
    } else {
      $temp_battle_omega['battle_turns'] = 3;
    }

  }
  // Otherwise, if this is the prototype subspace mission against a spacebot
  elseif ($this_field_token == 'prototype-subspace'){

    // Update the music to use the prototype subspace theme
    $temp_battle_omega['battle_field_base']['field_music'] = 'prototype-subspace';

    // Define the type and challenger alt for this doctor
    $temp_challenger_alt = '';
    $temp_challenger_ability = '';
    $temp_challenger_ability2 = '';
    $temp_challenger_ability_addons = array();
    $temp_challenger_stat_boost = array();
    $temp_challenger_stat_break = array();
    $temp_challenger_stat_boost_value = 0;
    $temp_challenger_stat_break_value = 0;
    if ($this_prototype_data['this_player_token'] == 'dr-light'){
      $temp_challenger_alt = '_alt';
      $temp_challenger_ability_addons[] = 'space-shot';
      $temp_challenger_ability_addons[] = 'space-buster';
      $temp_challenger_ability_addons[] = 'space-overdrive';
      //$temp_challenger_ability_addons[] = 'bass-buster';
      //$temp_challenger_ability_addons[] = 'bass-baroque';
      $temp_challenger_stat_boost = array('attack');
      $temp_challenger_stat_break = array('defense', 'speed');
      $temp_challenger_stat_boost_value = 120;
      $temp_challenger_stat_break_value = 60;
    }
    elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){
      $temp_challenger_alt = '_alt3';
      $temp_challenger_ability_addons[] = 'space-shot';
      $temp_challenger_ability_addons[] = 'space-buster';
      $temp_challenger_ability_addons[] = 'space-overdrive';
      //$temp_challenger_ability_addons[] = 'proto-buster';
      //$temp_challenger_ability_addons[] = 'proto-strike';
      $temp_challenger_stat_boost = array('speed');
      $temp_challenger_stat_break = array('attack', 'defense');
      $temp_challenger_stat_boost_value = 160;
      $temp_challenger_stat_break_value = 80;

    }
    elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){
      $temp_challenger_alt = '_alt2';
      $temp_challenger_ability_addons[] = 'space-shot';
      $temp_challenger_ability_addons[] = 'space-buster';
      $temp_challenger_ability_addons[] = 'space-overdrive';
      //$temp_challenger_ability_addons[] = 'mega-buster';
      //$temp_challenger_ability_addons[] = 'mega-slide';
      $temp_challenger_stat_boost = array('defense');
      $temp_challenger_stat_break = array('speed', 'attack');
      $temp_challenger_stat_boost_value = 200;
      $temp_challenger_stat_break_value = 100;
    }

    // Collect a reference to the first robot, the target one
    $temp_battle_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];

    // Update the target robots's image with the new alt
    $temp_battle_robot['robot_image'] = $this_robot_token.$temp_challenger_alt;

    // Update the target robot with new stats based on their form
    $stat_overflow = 0;
    foreach ($temp_challenger_stat_boost AS $key => $stat){
      $temp_battle_robot['robot_'.$stat] = $this_robot_data['robot_'.$stat];
      $temp_battle_robot['robot_'.$stat] += $temp_challenger_stat_boost_value;
    }
    foreach ($temp_challenger_stat_break AS $key => $stat){
      $temp_battle_robot['robot_'.$stat] = $this_robot_data['robot_'.$stat];
      $temp_battle_robot['robot_'.$stat] -= $temp_challenger_stat_break_value;
    }

    // Add abilities for this robot based on their element of choice
    //$temp_battle_robot['robot_abilities'] = array('buster-shot', $temp_challenger_ability, 'energy-boost', $temp_challenger_stat_boost[0].'-boost', $temp_challenger_stat_break[0].'-break', $temp_challenger_stat_boost[0].'-mode');
    $temp_battle_robot['robot_abilities'] = array();
    $temp_battle_robot['robot_abilities'][] = $temp_challenger_stat_boost[0].'-boost';
    $temp_battle_robot['robot_abilities'][] = $temp_challenger_stat_break[0].'-break';
    $temp_battle_robot['robot_abilities'][] = $temp_challenger_stat_boost[0].'-mode';
    foreach ($temp_challenger_ability_addons AS $temp_challenger_ability){
      $temp_battle_robot['robot_abilities'][] = $temp_challenger_ability;
    }

    // Update changes in the main robot array
    $temp_battle_omega['battle_target_player']['player_robots'][0] = $temp_battle_robot;

    // Update the description text for the battle
    $temp_battle_omega['battle_description'] = 'Defeat the challenger '.$this_robot_name.' in its '.$temp_challenger_stat_boost[0].' form!';

    /*
    // Add an item drop to the battle based on type
    $temp_battle_omega['battle_rewards']['items'] = array(
      array('chance' => 100, 'token' => 'item-'.$temp_challenger_stat_boost[0].'-capsule')
      );
    */

    // Hard code a large capsule drop if this is the first time through
    if (empty($temp_battle_omega['battle_complete'])){
      $temp_battle_omega['battle_turns'] = 9;
      $temp_battle_omega['battle_rewards']['items'][] = array('chance' => 100, 'token' => 'item-'.$temp_challenger_stat_boost[0].'-capsule');
    } else {
      $temp_battle_omega['battle_turns'] = 6;
    }

  }

  // Return the generated omega battle data
  return $temp_battle_omega;

}
?>