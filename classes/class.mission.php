<?php
/**
 * Mega Man RPG Mission
 * <p>The global mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission {

  /**
   * Create a new RPG mission game object.
   * This is a wrapper class for static functions,
   * so object initialization is not necessary.
   */
  public function rpg_mission(){ }

  /*
   * MISSION GENERATION
   */

  // Define a function for generating the STARTER missions
  public static function generate_starter($this_prototype_data, $this_robot_token = 'met', $this_start_level = 1, $this_rescue_token = 'roll', $this_field_token = 'intro-field', $this_start_count = 1, $this_target_class = 'mecha'){
    // Pull in global variables for this function
    global $mmrpg_index, $this_database;

    // Collect data on this robot and the rescue robot
    $this_robot_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    $this_robot_data = rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
    $this_robot_name = $this_robot_data['robot_name'];
    // Populate the battle options with the starter battle option
    $temp_start_abilities = $this_start_count;
    $temp_target_count = $this_start_count;
    $temp_max_count = $this_start_count; //($this_start_count * 3);
    $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
    $temp_battle_omega = array();
    $temp_battle_omega['battle_field_info']['field_id'] = 100;
    $temp_battle_omega['battle_field_info']['field_token'] = $this_field_token; //'intro-field';
    $temp_battle_omega['flags']['starter_battle'] = true;
    $temp_battle_omega['battle_complete'] = false;
    $temp_battle_omega['battle_token'] = $temp_battle_token;
    $temp_battle_omega['battle_size'] = '1x4';
    $temp_battle_omega_complete = rpg_prototype::battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
    if (!empty($temp_battle_omega_complete)){ $temp_battle_omega['battle_complete'] = $temp_battle_omega_complete; }
    if (!empty($temp_battle_omega_complete['battle_count'])){ $temp_target_count += $temp_battle_omega_complete['battle_count'] - 1; }
    if (empty($temp_battle_omega_complete['battle_count'])){ $temp_battle_omega['flags']['starter_battle_firstrun'] = true; }
    if ($temp_target_count > $temp_max_count){ $temp_target_count = $temp_max_count; }
    $temp_battle_omega['battle_level'] = $this_start_level;
    $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
    $temp_battle_omega['battle_name'] = 'Chapter One Intro Battle';
    $temp_battle_omega['battle_field_info']['field_music'] = rpg_prototype::get_player_boss_music($this_prototype_data['this_player_token']);
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
    $temp_battle_omega['battle_turns_limit'] = 0;
    $temp_battle_omega['battle_robots_limit'] = 0;


    // Process special actions for the first, actual intro field
    if ($this_field_token == 'intro-field'){
      $temp_chapter_phase_token = 'intro';
    }
    // Otherwise, if this is one of the doctor field battles
    elseif (in_array($this_field_token, array('light-laboratory', 'wily-castle', 'cossack-citadel'))){
      $temp_chapter_phase_token = 'doctor';
    }
    // Otherwise, if this is the prototype subspace mission against a spacebot
    elseif ($this_field_token == 'prototype-subspace'){
      $temp_chapter_phase_token = 'subspace';
    }

    // Loop through all the required robots one last time and do final calculations
    foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot){
      // Update the robot level and battle points plus turns
      if (isset($this_robot_index[$robot['robot_token']])){ $robot = rpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]); }
      else { continue; }
      $temp_core_backup = !empty($robot['robot_core']) ? $robot['robot_core'] : '';
      $index = rpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
      $robot = array_merge($index, $robot);
      if (!empty($temp_core_backup)){ $robot['robot_core'] = $temp_core_backup; }
      if (!isset($robot['robot_item'])){ $robot['robot_item'] = ''; }

      // Increment allowable robots, points, and turns based on who's in the battle
      if ($robot['robot_class'] == 'mecha'){
        $robot['robot_level'] = $temp_omega_robot_level; //mt_rand(ceil($temp_omega_robot_level / 2), $temp_omega_robot_level);
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $this_start_level, $temp_start_abilities, $robot['robot_item']);
      }
      elseif ($robot['robot_class'] == 'master'){
        $robot['robot_level'] = $temp_omega_robot_level;
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $this_start_level, $temp_start_abilities, $robot['robot_item']);
      }
      elseif ($robot['robot_class'] == 'boss'){
        $robot['robot_level'] = $temp_omega_robot_level; //mt_rand($temp_omega_robot_level, ceil($temp_omega_robot_level * 2));
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $this_start_level, $temp_start_abilities, $robot['robot_item']);
      }
      // Increment the battle's turn limit based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }
      // Increment the battle's point reward based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }
      // Increment the battle's zenny reward based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }
      // Increment the battle's robot limit based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }
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
    if ($temp_battle_omega['battle_turns_limit'] < 1){ $temp_battle_omega['battle_turns_limit'] = 1; }
    else { $temp_battle_omega['battle_turns_limit'] = ceil($temp_battle_omega['battle_turns_limit']); }
    if ($temp_battle_omega['battle_robots_limit'] < 1){ $temp_battle_omega['battle_robots_limit'] = 1; }
    else { $temp_battle_omega['battle_robots_limit'] = ceil($temp_battle_omega['battle_robots_limit']); }

    // Remove background mechas from view for starter fields
    $temp_battle_omega['battle_field_info']['field_background_attachments'] = array();
    $temp_battle_omega['battle_field_info']['field_foreground_attachments'] = array();

    // Clear any items present in the rewards array
    $temp_battle_omega['battle_rewards_items'] = array(
      // No item drops for the first stage
      );

    // Clear any robots present in the rewards array
    $temp_battle_omega['battle_rewards_robots'] = array(
      // No unlockable robots for the first stage
      );

    // Process special actions for the first, actual intro field
    if ($temp_chapter_phase_token == 'intro'){

      // Update the battle name with the current phase
      $temp_battle_omega['battle_name'] = 'Chapter One Intro Battle';

      // Update the music to use the boss them from whichever game is most common right now
      $temp_battle_omega['battle_field_info']['field_music'] = rpg_prototype::get_player_boss_music($this_prototype_data['this_player_token']);

      // Add the player's lab to the background field
      $temp_doctor_token = str_replace('dr-', '', $this_prototype_data['this_player_token']);
      $temp_battle_omega['battle_field_info']['field_foreground_attachments']['object_intro-field-'.$temp_doctor_token] = array('class' => 'object', 'size' => 160, 'offset_x' => 12, 'offset_y' => 121, 'offset_z' => 1, 'object_token' => 'intro-field-'.$temp_doctor_token, 'object_frame' => array(0), 'object_direction' => 'right');

      // Otherwise if the rescue has not yet been unlocked as a playable character
      if (!empty($this_rescue_token) && !rpg_game::robot_unlocked(false, $this_rescue_token)){
        // Add the rescue to the background with animation
        $temp_battle_omega['battle_field_info']['field_foreground_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => 40, 'offset_x' => 91, 'offset_y' => 118, 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => array(8,0,8,0,0), 'robot_direction' => 'right');
      }

      // Update the description text for the battle
      $temp_field_locations = array('light' => 'laboratory', 'wily' => 'castle', 'cossack' => 'citadel');
      $temp_location = $temp_field_locations[$temp_doctor_token];
      //$temp_location = preg_replace('/^([a-z0-9]+)-/i', '', $this_field_token);
      $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's that are' : ' that\'s').' attacking the '.$temp_location.'!';

      // Collect a reference to the first robot, the target one
      $temp_battle_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];
      // Add abilities for this robot that are easy to deal with
      $temp_battle_robot['robot_abilities'] = array('met-shot');
      // Update changes in the main robot array
      $temp_battle_omega['battle_target_player']['player_robots'][0] = $temp_battle_robot;

      // Hard code a small screw drop if this is the first time through
      if (empty($temp_battle_omega['battle_complete'])){
        $temp_battle_omega['battle_turns_limit'] = 3;
        $temp_battle_omega['battle_rewards_items'][] = array('chance' => 100, 'token' => 'item-screw-small');
      } else {
        $temp_battle_omega['battle_turns_limit'] = 1;
      }

    }
    // Otherwise, if this is one of the home field battles
    elseif ($temp_chapter_phase_token == 'doctor'){

      // Update the battle name with the current phase
      $temp_battle_omega['battle_name'] = 'Chapter One Mecha Battle';

      // Update the music to use the default one for the doctor stage
      $temp_battle_omega['battle_field_info']['field_music'] = $this_field_token;
      $temp_battle_omega['battle_field_info']['field_multipliers'] = array('experience' => 2.0);

      // Update the description text for the battle
      $temp_location = preg_replace('/^([a-z0-9]+)-/i', '', $this_field_token);
      $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' still inside the '.$temp_location.'!';

      // Otherwise if the rescue has not yet been unlocked as a playable character
      if (!empty($this_rescue_token) && !rpg_game::robot_unlocked(false, $this_rescue_token)){
        // Add the rescue to the background with animation
        $temp_battle_omega['battle_field_info']['field_foreground_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => 40, 'offset_x' => 91, 'offset_y' => 118, 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => array(8,0,8,0,0), 'robot_direction' => 'left');
        // Add the rescue character in an unlockable
        //$temp_battle_omega['battle_rewards_robots'][] = array('token' => $this_rescue_token, 'level' => $this_start_level);
      }

      // Collect a reference to the first robot, the target one
      $temp_battle_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];
      // Add abilities for this robot based on their rival player
      $temp_buster_ability = str_replace('dr-', '', $this_prototype_data['target_player_token']).'-buster';
      $temp_battle_robot['robot_abilities'] = array($temp_buster_ability, 'buster-shot');
      // Update changes in the main robot array
      $temp_battle_omega['battle_target_player']['player_robots'][0] = $temp_battle_robot;

      // Hard code a large screw drop if this is the first time through, else other stuff
      if (empty($temp_battle_omega['battle_complete'])){
        $temp_battle_omega['battle_turns_limit'] = 6;
        $temp_battle_omega['battle_rewards_items'][] = array('chance' => 100, 'token' => 'item-screw-large');
      } else {
        $temp_battle_omega['battle_turns_limit'] = 3;
      }

      // Add the opposing buster as an unlockable ability
      if (!rpg_game::ability_unlocked('', '', $temp_buster_ability)){
        $temp_battle_omega['battle_rewards_abilities'][] = array('token' => $temp_buster_ability);
      }

    }
    // Otherwise, if this is the prototype subspace mission against a spacebot
    elseif ($temp_chapter_phase_token == 'subspace'){

      // Update the battle name with the current phase
      $temp_battle_omega['battle_name'] = 'Chapter One Boss Battle';

      // Update the music to use the prototype subspace theme
      $temp_battle_omega['battle_field_info']['field_music'] = 'prototype-subspace';

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
        $temp_challenger_stat_boost_value = 90;
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
        $temp_challenger_stat_boost_value = 90;
        $temp_challenger_stat_break_value = 60;

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
        $temp_challenger_stat_boost_value = 90;
        $temp_challenger_stat_break_value = 60;
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
      $temp_battle_omega['battle_rewards_items'] = array(
        array('chance' => 100, 'token' => 'item-'.$temp_challenger_stat_boost[0].'-capsule')
        );
      */

      // Hard code a large capsule drop if this is the first time through
      if (empty($temp_battle_omega['battle_complete'])){
        $temp_battle_omega['battle_turns_limit'] = 9;
        $temp_battle_omega['battle_rewards_items'][] = array('chance' => 100, 'token' => 'item-'.$temp_challenger_stat_boost[0].'-capsule');
      } else {
        $temp_battle_omega['battle_turns_limit'] = 6;
      }

    }

    // Return the generated omega battle data
    return $temp_battle_omega;

  }

  // Define a function for generating the SINGLES missions
  public static function generate_single($this_prototype_data, $this_robot_token, $this_field_token, $this_start_level = 1, $this_unlock_robots = true, $this_unlock_abilities = true, $this_addon_abilities = 0){
    // Pull in global variables for this function
    global $mmrpg_index, $this_database, $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine;

    // Collect the robot index for calculation purposes
    $this_robot_index = rpg_robot::get_index();
    $this_field_index = rpg_field::get_index();

    // Define the array to hold this omega battle and populate with base varaibles
    $temp_option_robot = is_array($this_robot_token) ? $this_robot_token : rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
    $temp_option_field = rpg_field::parse_index_info($this_field_index[$this_field_token]);

    $temp_battle_omega = array();

    $temp_battle_omega['flags']['single_battle'] = true;
    $temp_battle_omega['values']['single_battle_masters'] = array($this_robot_token);

    $temp_battle_omega['battle_size'] = '1x1';
    $temp_battle_omega['battle_name'] = 'Chapter Two Master Battle';
    $temp_battle_omega['battle_token'] = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
    $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];

    $temp_battle_omega['battle_field_info']['field_id'] = 100;
    $temp_battle_omega['battle_field_info']['field_token'] = $temp_option_field['field_token'];

    $temp_battle_omega['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
    $temp_battle_omega['battle_target_player']['player_token'] = 'player';
    $temp_battle_omega['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);

    $temp_battle_omega['battle_complete'] = false;
    $temp_option_completed = rpg_prototype::battle_complete(false, $temp_battle_omega['battle_token']);
    if (!empty($temp_option_completed)){ $temp_battle_omega['battle_complete'] = $temp_option_completed; }

    $temp_target_count = 2;
    $temp_ability_count = 2;
    $temp_battle_count = 0;
    if (!empty($temp_battle_omega['battle_complete']['battle_count'])){
      $temp_battle_count = $temp_battle_omega['battle_complete']['battle_count'];
      $temp_target_count += $temp_battle_count;
      $temp_ability_count += $temp_battle_count;
    }
    if ($temp_target_count > 4){ $temp_target_count = 4; }
    if ($temp_ability_count > 6){ $temp_ability_count = 6; }

    // Create the quick flag to check if battle is complete
    $temp_battle_complete = !empty($temp_battle_omega['battle_complete']) ? true : false;
    // Check to see if this robot master has been unlocked already
    $temp_master_unlocked = rpg_game::robot_unlocked('', $this_robot_token) ? true : false;
    // Create a quick flah to check if the prototype is complete
    $temp_prototype_complete = !empty($this_prototype_data['prototype_complete']) ? true : false;

    // Define the fusion star token in case we need to test for it
    $temp_field_star_token = $temp_option_field['field_token'];
    $temp_field_star_present = $this_prototype_data['prototype_complete'] && empty($_SESSION['GAME']['values']['battle_stars'][$temp_field_star_token]) ? true : false;

    // Define both the starforce and darkness battles to false
    $temp_darkness_battle = false;
    $temp_starforce_battle = false;

    // If the prototype is complete, this can be a starforce battle
    if ($temp_prototype_complete && $temp_field_star_present){
      $temp_starforce_battle = true;
    }

    // If the prototype and battle are complete plus robot unlocked, this can be a darkness battle
    if ($temp_prototype_complete && $temp_battle_complete && $temp_master_unlocked){
      // Only make this a darkness battle on a random chance and only if there isn't a star present
      if (!$temp_field_star_present && mt_rand(0, 7) == 0){
        $temp_darkness_battle = true;
        $temp_starforce_battle = false;
      }
    }

    // If this is a starforce battle, fill the empty spots with like-typed robots
    if ($temp_starforce_battle){

      // Increase the frequency of switching for this battle
      $temp_battle_omega['battle_target_player']['player_switch'] = 1.5;
      // Create an array to hold this battle's robots and populate with defaults
      $temp_robot_tokens = array();
      $temp_robot_tokens[] = $temp_battle_omega['battle_target_player']['player_robots'][0]['robot_token'];
      // Collect omega robot factors based on player and merge them all together
      if ($this_prototype_data['this_player_token'] == 'dr-light'){
        $temp_factors_list = array($this_omega_factors_one, array_merge($this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine));
      } elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){
        $temp_factors_list = array($this_omega_factors_two, array_merge($this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine, $this_omega_factors_one));
      } elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){
        $temp_factors_list = array($this_omega_factors_three, array_merge($this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine, $this_omega_factors_one, $this_omega_factors_two));
      }
      // Shuffle the bonus robots section of the list
      shuffle($temp_factors_list[1]);
      //$debug_backup = 'initial:count = '.count($temp_battle_omega['battle_target_player']['player_robots']).' // ';
      // Loop through and add the robots
      $temp_counter = 0;
      foreach ($temp_factors_list AS $this_list){
        shuffle($this_list);
        foreach ($this_list AS $this_factor){
          if (empty($this_factor['robot'])){ continue; }
          $bonus_robot_index = $this_robot_index[$this_factor['robot']];
          if (!isset($bonus_robot_index['robot_core'])){ $bonus_robot_index['robot_core'] = ''; }
          if ($bonus_robot_index['robot_core'] == $temp_option_field['field_type']){
            if (!in_array($bonus_robot_index['robot_token'], $temp_robot_tokens)){
              $bonus_robot_info = array();
              $bonus_robot_info['flags']['hide_from_mission_select'] = true;
              $bonus_robot_info['robot_token'] = $bonus_robot_index['robot_token'];
              $bonus_robot_info['robot_class'] = $bonus_robot_index['robot_class'];
              $temp_battle_omega['battle_target_player']['player_robots'][] = $bonus_robot_info;
              $temp_robot_tokens[] = $bonus_robot_info['robot_token'];
            }
          }
        }
      }
      //$debug_backup .= 'before:count = '.count($temp_battle_omega['battle_target_player']['player_robots']).' // ';
      $temp_slice_limit = 2; //1 + $temp_battle_omega['battle_complete']['battle_count'];
      //if ($temp_slice_limit >= (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 4)){ $temp_slice_limit = (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 4); }
      //elseif ($temp_slice_limit >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ $temp_slice_limit = 8; }
      $temp_battle_omega['battle_target_player']['player_robots'] = array_slice($temp_battle_omega['battle_target_player']['player_robots'], 0, $temp_slice_limit);
      shuffle($temp_battle_omega['battle_target_player']['player_robots']);
      //$debug_backup .= 'after:count = '.count($temp_battle_omega['battle_target_player']['player_robots']).' // ';

    }


    // Define the omega variables for level, points, turns, and random encounter rate
    $omega_robot_level_max = $this_start_level + 7;
    $omega_robot_level = $this_start_level + (!empty($this_prototype_data['battles_complete']) ? $this_prototype_data['battles_complete'] - 1 : 0);
    if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
    $omega_random_encounter = false;

    // Define the battle rewards based on above data
    $temp_battle_omega['battle_rewards_robots'] = array();
    $temp_battle_omega['battle_rewards_abilities'] = array();
    if (!$temp_master_unlocked){ $temp_battle_omega['battle_rewards_robots'][] = array('token' => $this_robot_token); }
    if (!empty($temp_option_robot['robot_rewards']['abilities'])){
      foreach ($temp_option_robot['robot_rewards']['abilities'] AS $key => $info){
        if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
        if (!rpg_game::ability_unlocked('', '', $info['token'])){ $temp_battle_omega['battle_rewards_abilities'][] = $info; }
      }
    }

    // Fill the empty spots with minor enemy robots
    if (true){
      $temp_battle_omega['battle_target_player']['player_switch'] = 1.5;
      $bonus_robot_count = $temp_target_count;
      $temp_mook_options = array();
      $temp_mook_letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
      $temp_mook_counts = array();
      if (!isset($temp_option_field['field_mechas'])){ $temp_option_field['field_mechas'] = array(); }
      for ($key = 0; $key < count($temp_option_field['field_mechas']); $key++){
        if (!empty($temp_option_field['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field['field_mechas'][$key]; }
      }
      if (empty($temp_mook_options)){ $temp_mook_options[] = 'met'; }
      $temp_mook_weights = array();
      $temp_mook_maxchance = count($temp_mook_options) * 30;
      foreach ($temp_mook_options AS $key => $token){ $temp_mook_weights[] = $temp_mook_maxchance - ($key * 15); }
      $temp_mook_options = array_slice($temp_mook_options, 0, ($temp_battle_count + 1));
      $temp_battle_omega['battle_field_info']['field_mechas'] = $temp_mook_options;
      if ($temp_darkness_battle){ $temp_battle_omega['battle_field_info']['field_mechas'] = array('dark-frag'); }
      $mt_rand = mt_rand(1, 4);

      // Allow mets to randomly show up in other battles at a low rate
      if (!empty($temp_battle_omega['battle_complete']) && !in_array('met', $temp_mook_options)){ $temp_mook_options[] = 'met'; $temp_mook_weights[] = 5; }

      // Loop through the allowed bonus robot count placing random mooks
      $temp_base_robot_count = count($temp_battle_omega['battle_target_player']['player_robots']);
      for ($i = 0; $i < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX; $i++){

        $temp_count_target_robots = count($temp_battle_omega['battle_target_player']['player_robots']);
        if ($temp_count_target_robots >= $bonus_robot_count){ break; }
        elseif ($temp_count_target_robots >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ break; }

        if ($temp_darkness_battle){ $temp_mook_token = 'dark-frag'; }
        else { $temp_mook_token = rpg_functions::weighted_chance($temp_mook_options, $temp_mook_weights); }

        $temp_mook_info = rpg_robot::parse_index_info($this_robot_index[$temp_mook_token]);
        $temp_mook_id = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_count_target_robots;
        $bonus_robot_info = array('robot_token' => $temp_mook_token, 'robot_id' => $temp_mook_id, 'robot_level' => 1);

        $bonus_robot_info['robot_abilities'] = $temp_mook_info['robot_abilities'];
        $bonus_robot_info['robot_class'] = !empty($temp_mook_info['robot_class']) ? $temp_mook_info['robot_class'] : 'master';
        $bonus_robot_info['robot_name'] = $temp_mook_info['robot_name'];

        $temp_mook_name_token = str_replace(' ', '-', strtolower($bonus_robot_info['robot_name']));
        $bonus_robot_info['robot_name_token'] = $temp_mook_name_token;
        if (!isset($temp_mook_counts[$temp_mook_name_token])){ $temp_mook_counts[$temp_mook_name_token] = 0; }
        else { $temp_mook_counts[$temp_mook_name_token]++; }

        $temp_battle_omega['battle_target_player']['player_robots'][] = $bonus_robot_info;
        $temp_robot_tokens[] = $bonus_robot_info['robot_token'];

      }

      // Remove the "A" from any mooks that are one of their kind
      foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
        if (!isset($info['robot_class']) || $info['robot_class'] != 'mecha'){ continue; }
        elseif (!isset($temp_mook_counts[$info['robot_token']]) || $temp_mook_counts[$info['robot_token']] == 0){
          $temp_battle_omega['battle_target_player']['player_robots'][$key]['robot_name'] = str_replace(' A', '', $info['robot_name']);
        }
      }

      // Loop through the target player robots to make final changes
      foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
        // Collect an index of this robot
        $index = rpg_robot::parse_index_info($this_robot_index[$info['robot_token']]);

        // Default the stat reward boost to zero to prevent early difficulities
        $temp_stat_reward_boost = 0;
        // Update the robot ID to prevent collisions
        $info['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1;
        // Append the appropriate letters to all the robot name tokens
        $info['robot_class'] = !empty($info['robot_class']) ? $info['robot_class'] : 'master';

        // Check to see if this is a support mecha and process changes accordingly
        if ($info['robot_class'] == 'mecha'){
          /*
          // Update this mecha's name with a letter if necessary
          $temp_name_token = isset($info['robot_name_token']) ? $info['robot_name_token'] : $info['robot_token'];
          if ($temp_mook_counts[$temp_name_token] > 0){
            if (!isset($temp_mook_counts[$temp_name_token])){ $temp_mook_counts[$temp_name_token] = 0; }
            else { $temp_mook_counts[$temp_name_token]++; }
            $info['robot_name'] .= ' '.$temp_mook_letters[$temp_mook_counts[$temp_name_token]];
          }
          */

          // If this is a starforce battle, boost the mechas stats appropriately
          if ($temp_starforce_battle){
            $temp_stat_reward_boost = 0.5;
          }
          // Otherwise if this is a darkness battle, boost the dark frags appropriately
          elseif ($temp_darkness_battle){
            $temp_stat_reward_boost = 1.0;
          }

        }
        // Otherwise, if this is a master robot, tweak their alt and stats
        else {

          // If this is s starforce battle, the robot master should be boosted in an alt colour
          if ($temp_starforce_battle){
            // Update the robot name, adding a delta symbol at the end
            $info['robot_name'] = $index['robot_name'].' Δ';
            $info['robot_number'] = $index['robot_number'].'Δ';
            // Update the robot image for this battle to be the alt version
            $info['robot_image'] = $info['robot_token'].'_alt';
            // This robot should also have more stats than usual, so let's give 'em some
            $temp_stat_reward_boost = 1.0;
          }
          // Otherwise, if a darkness battle, the robot master should be boosted further and dark in colour
          elseif ($temp_darkness_battle){
            // Update the robot name, adding a lower-case sigma symbol at the end
            $info['robot_name'] = $index['robot_name'].' Σ';
            $info['robot_number'] = $index['robot_number'].'Σ';
            // Update the robot image for this battle to be the alt version
            $info['robot_image'] = $info['robot_token'].'_alt9';
            // Update the robot's core type to empty
            $info['robot_core'] = 'empty';
            $info['robot_core2'] = '';
            // Update the robot's weaknesses/resistances/etc.
            $info['robot_weaknesses'] = array();
            $info['robot_resistances'] = array();
            $info['robot_affinities'] = array();
            $info['robot_immunities'] = array(); //array('copy', 'crystal', 'cutter', 'earth', 'electric', 'explode', 'flame', 'freeze', 'impact', 'laser', 'missile', 'nature', 'shadow', 'shield', 'space', 'swift', 'time', 'water', 'wind');
            // Remove all quotes so this robot doesn't speak
            $info['robot_quotes'] = array();
            // This robot should also have more stats than usual, so let's give 'em more
            $temp_stat_reward_boost = 2.0;
          }
          // If the defined robot image does not actually exist, replace with placeholder base sprite
          if (isset($info['robot_image']) && !file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$info['robot_image'].'/')){
            $info['robot_image'] = $info['robot_token'];
          }

        }

        // If there is a reward booster in place, generate values
        if ($temp_stat_reward_boost > 0){

          // This robot should also have more stats than usual, so let's give 'em some more
          $temp_robot_rewards = array();
          //$temp_stat_total = $index['robot_energy'] + $index['robot_attack'] + $index['robot_defense'] + $index['robot_speed'];
          //$temp_robot_rewards['robot_energy'] = ceil(($index['robot_energy'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $temp_stat_total = $index['robot_attack'] + $index['robot_defense'] + $index['robot_speed'];
          $temp_robot_rewards['robot_attack'] = ceil(($index['robot_attack'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $temp_robot_rewards['robot_defense'] = ceil(($index['robot_defense'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $temp_robot_rewards['robot_speed'] = ceil(($index['robot_speed'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $info['values']['robot_rewards'] = $temp_robot_rewards;

        }

        // Update the player robots array with recent changes
        $temp_battle_omega['battle_target_player']['player_robots'][$key] = $info;
      }

    }

    // Skip the empty battle button or a different phase
    if (empty($temp_battle_omega['battle_token']) || $temp_battle_omega['battle_token'] == 'battle' || $temp_battle_omega['battle_phase'] != $this_prototype_data['battle_phase']){ return false; }

    // Collect the battle token and create an omega clone from the index base
    $temp_battle_token = $temp_battle_omega['battle_token'];
    // If the battle was already complete, collect its details and modify the mission
    $temp_complete_level = 0;
    $temp_complete_count = 0;
    if (!empty($temp_battle_omega['battle_complete'])){
      if (!empty($temp_battle_omega['battle_complete']['battle_min_level'])){ $temp_complete_level = $temp_battle_omega['battle_complete']['battle_min_level']; }
      else { $temp_complete_level = $omega_robot_level; }
      if (!empty($temp_battle_omega['battle_complete']['battle_count'])){ $temp_complete_count = $temp_battle_omega['battle_complete']['battle_count']; }
      else { $temp_complete_count = 1; }
      $omega_robot_level = $temp_complete_level + $temp_complete_count - 1;
      $omega_robot_level = $omega_robot_level >= 100 ? 100 : $omega_robot_level;
    }

    // If this is a starforce battle, we should double the level max
    if ($temp_starforce_battle){
      $omega_robot_level_max = $omega_robot_level_max * 2;
      $omega_robot_level = $omega_robot_level * 2;
    }
    // Otherwise if this is a darkness battle, we should triple the level max
    elseif ($temp_darkness_battle){
      $omega_robot_level_max = $omega_robot_level_max * 3;
      $omega_robot_level = $omega_robot_level * 3;
    }

    // Define the battle difficulty level (0 - 8) based on level and completed count
    $temp_battle_difficulty = ceil(8 * ($omega_robot_level / 100));
    $temp_battle_difficulty += $temp_complete_count;
    if ($temp_battle_difficulty >= 10){ $temp_battle_difficulty = 10; }
    $temp_battle_omega['battle_difficulty'] = $temp_battle_difficulty;
    // Update the robot level for this battle
    $temp_battle_omega['battle_level'] = $omega_robot_level;

    // Start all the point-based battle vars at zero
    $temp_battle_omega['battle_points'] = 0;
    $temp_battle_omega['battle_zenny'] = 0;
    $temp_battle_omega['battle_turns_limit'] = 0;
    $temp_battle_omega['battle_robots_limit'] = 0;

    // Loop through the target robots again update with omega values
    foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot){

      // Update the robot level and battle points plus turns
      if (!isset($this_robot_index[$robot['robot_token']])){ continue; }
      $temp_core_backup = !empty($robot['robot_core']) ? $robot['robot_core'] : '';
      $index = rpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
      $robot = array_merge($index, $robot);
      if (!empty($temp_core_backup)){ $robot['robot_core'] = $temp_core_backup; }
      if (!isset($robot['robot_item'])){ $robot['robot_item'] = ''; }

      if (!empty($temp_core_backup)){ $robot['robot_core'] = $temp_core_backup; }
      // Increment allowable robots, points, and turns based on who's in the battle
      if ($robot['robot_class'] == 'mecha'){
        $robot['robot_level'] = mt_rand(ceil($omega_robot_level / 2), $omega_robot_level);
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $omega_robot_level, ceil($temp_ability_count / 2), $robot['robot_item']);
      }
      elseif ($robot['robot_class'] == 'master'){
        $robot['robot_level'] = $omega_robot_level;
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $omega_robot_level, $temp_ability_count, $robot['robot_item']);
      }
      elseif ($robot['robot_class'] == 'boss'){
        $robot['robot_level'] = mt_rand($omega_robot_level, ceil($omega_robot_level * 2));
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $omega_robot_level, floor($temp_ability_count * 2), $robot['robot_item']);
      }

      // Increment the battle's turn limit based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }

      // Increment the battle's point reward based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }

      // Increment the battle's zenny reward based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }

      // Increment the battle's robot limit based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }

    }

    // Increase expected turns if a starforce or darkness battle
    if ($temp_starforce_battle){ $temp_battle_omega['battle_turns_limit'] += round($temp_battle_omega['battle_turns_limit'] * 0.25); }
    elseif ($temp_darkness_battle){ $temp_battle_omega['battle_turns_limit'] += round($temp_battle_omega['battle_turns_limit'] * 0.50); }

    // Fix any zero or invalid battle values
    if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
    else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
    if ($temp_battle_omega['battle_turns_limit'] < 1){ $temp_battle_omega['battle_turns_limit'] = 1; }
    else { $temp_battle_omega['battle_turns_limit'] = ceil($temp_battle_omega['battle_turns_limit']); }
    if ($temp_battle_omega['battle_robots_limit'] < 1){ $temp_battle_omega['battle_robots_limit'] = 1; }
    else { $temp_battle_omega['battle_robots_limit'] = ceil($temp_battle_omega['battle_robots_limit']); }

    // Recollect the option robots
    $temp_option_robot = $this_robot_index[$temp_option_robot['robot_token']];

    // Reverse the order of the robots in battle
    $temp_battle_omega['battle_target_player']['player_robots'] = array_reverse($temp_battle_omega['battle_target_player']['player_robots']);
    $temp_first_robot = array_shift($temp_battle_omega['battle_target_player']['player_robots']);
    shuffle($temp_battle_omega['battle_target_player']['player_robots']);
    array_unshift($temp_battle_omega['battle_target_player']['player_robots'], $temp_first_robot);

    // Empty the robot rewards array if not allowed
    if (!$this_unlock_robots){ $temp_battle_omega['battle_rewards_robots'] = array(); }
    // Empty the ability rewards array if not allowed
    if (!$this_unlock_abilities){ $temp_battle_omega['battle_rewards_abilities'] = array(); }

    // Define the number of abilities and robots left to unlock and start at zero
    $this_unlock_robots_count = count($temp_battle_omega['battle_rewards_robots']);
    $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards_abilities']);

    // Loop through the omega battle robot rewards and update the robot levels there too
    if (!empty($temp_battle_omega['battle_rewards_robots'])){
      foreach ($temp_battle_omega['battle_rewards_robots'] AS $key2 => $robot){
        // Update the robot level and battle points plus turns
        $temp_battle_omega['battle_rewards_robots'][$key2]['level'] = $omega_robot_level;
        // Remove if this robot is already unlocked
        if (rpg_game::robot_unlocked(false, $robot['token'])){ $this_unlock_robots_count -= 1; }
      }
    }

    // Loop through the omega battle ability rewards and update the ability levels there too
    if (!empty($temp_battle_omega['battle_rewards_abilities'])){
      foreach ($temp_battle_omega['battle_rewards_abilities'] AS $key2 => $ability){
        // Remove if this ability is already unlocked
        if (rpg_game::ability_unlocked($this_prototype_data['this_player_token'], false, $ability['token'])){ $this_unlock_abilities_count -= 1; }
      }
    }

    // Check to see if we should be adding starforce to this battle
    if ($temp_starforce_battle){
      // Generate the necessary field star variables and add them to the battle data
      $temp_field_star = array();
      $temp_field_star['star_name'] = $temp_option_field['field_name'];
      $temp_field_star['star_token'] = $temp_field_star_token;
      $temp_field_star['star_kind'] = 'field';
      $temp_field_star['star_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : 'none';
      $temp_field_star['star_type2'] = !empty($temp_option_field['field_type2']) ? $temp_option_field['field_type2'] : '';
      $temp_field_star['star_field'] = $temp_option_field['field_token'];
      $temp_field_star['star_field2'] = '';
      $temp_field_star['star_player'] = $this_prototype_data['this_player_token'];
      $temp_field_star['star_date'] = time();
      $temp_battle_omega['values']['field_star'] = $temp_field_star;
      $temp_battle_omega['battle_target_player']['player_starforce'] = array();
      $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type']] = 1;

    }

    // Check to see if we should be adding darkness to this battle
    if ($temp_darkness_battle){
      // Generate the necessary dark tower variables and add them to the battle data
      $temp_battle_omega['flags']['dark_tower'] = true;
    }

    // Update the battle description based on what we've calculated
    if (!empty($this_unlock_abilities_count)){
      $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].' and download '.get_gendered_possessive($temp_option_robot['robot_gender']).' special weapon!';
      $temp_battle_omega['battle_description2'] = 'Once we\'ve acquired it, we may be able to equip the ability to other robots...';
    } elseif (!empty($this_unlock_robots_count)){
      $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].' and download '.get_gendered_possessive($temp_option_robot['robot_gender']).' robot data!';
      $temp_battle_omega['battle_description2'] = 'If we use only Neutral type abilities on the target we may be able to save it...';
    } elseif ($temp_starforce_battle){
      $temp_battle_omega['battle_description'] = 'Defeat the starforce boosted '.$temp_option_robot['robot_name'].' and collect '.get_gendered_possessive($temp_option_robot['robot_gender']).' Field Star! ';
      $temp_battle_omega['battle_description2'] = 'The '.ucfirst($temp_option_field['field_type']).' type energy appears to have attracted another robot master to the field...';
    } elseif ($temp_darkness_battle){
      $temp_battle_omega['battle_description'] = 'Defeat the darkness boosted '.$temp_option_robot['robot_name'].' and destroy '.get_gendered_possessive($temp_option_robot['robot_gender']).' Dark Tower! ';
      $temp_battle_omega['battle_description2'] = 'The powerful negative energy appears to have attracted an army of dark elements to the field...';
    } else {
      $temp_battle_omega['battle_description'] = 'Defeat '.$temp_option_robot['robot_name'].'!'; //.' and download its data!';
    }

    // If this battle has been completed already, decrease the points
    //if ($temp_battle_omega['battle_complete']){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] * 0.10); }
    // Add some random item drops to the starter battle
    $temp_battle_omega['battle_rewards_items'] = array(
      //array('chance' => 1, 'token' => 'item-energy-tank')
      );

    // Return the generated battle data
    return $temp_battle_omega;

  }


  // Define a function for generating the DOUBLES missions
  public static function generate_double($this_prototype_data, $this_robot_tokens, $this_field_tokens, $this_start_level = 1, $this_unlock_robots = true, $this_unlock_abilities = true){
    // Pull in global variables for this function
    global $mmrpg_index, $this_database, $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine;

    // Collect the robot index for calculation purposes
    $this_robot_index = rpg_robot::get_index();
    $this_field_index = rpg_field::get_index();

    // Define the array to hold this omega battle and populate with base varaibles
    $this_robot_token = $this_robot_tokens[0];
    $this_robot_token2 = $this_robot_tokens[1];

    $temp_option_battle = array();
    $temp_option_battle2 = array();
    $temp_option_robot = is_array($this_robot_tokens[0]) ? $this_robot_tokens[0] : array('robot_token' => $this_robot_tokens[0]);
    $temp_option_robot2 = is_array($this_robot_tokens[1]) ? $this_robot_tokens[1] : array('robot_token' => $this_robot_tokens[1]);
    $temp_option_field = rpg_field::parse_index_info($this_field_index[$this_field_tokens[0]]);
    $temp_option_field2 = rpg_field::parse_index_info($this_field_index[$this_field_tokens[1]]);

    $temp_battle_omega = array();

    $temp_battle_omega['flags']['double_battle'] = true;
    $temp_battle_omega['values']['double_battle_masters'] = $this_robot_tokens;

    $temp_option_battle['battle_size'] = '1x2';
    $temp_option_battle['battle_name'] = 'Chapter Four Fusion Battle';
    $temp_option_battle['battle_token'] = $this_prototype_data['phase_battle_token'].'-'.str_replace('-man', '', $this_robot_tokens[0]).'-'.str_replace('-man', '', $this_robot_tokens[1]);
    $temp_option_battle['battle_phase'] = $this_prototype_data['battle_phase'];

    $temp_option_battle['battle_field_info']['field_token'] = $temp_option_field['field_token'];
    $temp_option_battle['battle_field_info']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_name']);
    $temp_option_battle['battle_field_info']['field_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
    $temp_option_battle['battle_field_info']['field_type2'] = !empty($temp_option_field2['field_type']) ? $temp_option_field2['field_type'] : '';
    $temp_option_battle['battle_field_info']['field_music'] = $temp_option_field2['field_token'];
    $temp_option_battle['battle_field_info']['field_foreground'] = $temp_option_field2['field_foreground'];
    $temp_option_battle['battle_field_info']['field_foreground_attachments'] = $temp_option_field2['field_foreground_attachments'];
    $temp_option_battle['battle_field_info']['field_background'] = $temp_option_field['field_background'];
    $temp_option_battle['battle_field_info']['field_background_attachments'] = $temp_option_field['field_background_attachments'];

    $temp_option_multipliers = array();
    $temp_option_field_list = array($temp_option_field, $temp_option_field2);
    foreach ($temp_option_field_list AS $temp_field){
      if (!empty($temp_field['field_multipliers'])){
        foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
          if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
          else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
        }
      }
    }
    $temp_option_battle['battle_field_info']['field_multipliers'] = $temp_option_multipliers;

    $temp_option_battle['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
    $temp_option_battle['battle_target_player']['player_token'] = 'player';
    $temp_option_battle['battle_target_player']['player_robots'] = array(); //array_merge($temp_option_battle['battle_target_player']['player_robots'], $temp_option_battle2['battle_target_player']['player_robots']);
    $temp_option_battle['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);
    $temp_option_battle['battle_target_player']['player_robots'][1] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => $this_robot_token2);
    //shuffle($temp_option_battle['battle_target_player']['player_robots']);

    $temp_option_battle['battle_complete'] = false;
    $temp_option_completed = rpg_prototype::battle_complete($this_prototype_data['this_player_token'], $temp_option_battle['battle_token']);
    if ($temp_option_completed){ $temp_option_battle['battle_complete'] = $temp_option_completed; }

    $temp_target_count = 4;
    $temp_ability_count = 4;
    $temp_battle_count = 0;
    if (!empty($temp_option_battle['battle_complete']['battle_count'])){
      $temp_battle_count = $temp_option_battle['battle_complete']['battle_count'];
      $temp_target_count += $temp_battle_count;
      $temp_ability_count += $temp_battle_count;
    }
    if ($temp_target_count > 6){ $temp_target_count = 6; }
    if ($temp_ability_count > 8){ $temp_ability_count = 8; }

    // Create the quick flag to check if battle is complete
    $temp_battle_complete = !empty($temp_option_battle['battle_complete']) ? true : false;
    // Check to see if this robot master has been unlocked already
    $temp_master_unlocked = rpg_game::robot_unlocked('', $this_robot_token) ? true : false;
    $temp_master_unlocked2 = rpg_game::robot_unlocked('', $this_robot_token2) ? true : false;
    // Create a quick flah to check if the prototype is complete
    $temp_prototype_complete = !empty($this_prototype_data['prototype_complete']) ? true : false;

    // Define the fusion star token in case we need to test for it
    $temp_field_star_token = preg_replace('/^([-_a-z0-9\s]+)-([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_token']).'-'.preg_replace('/^([-_a-z0-9\s]+)-([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_token']);
    $temp_field_star_present = $this_prototype_data['prototype_complete'] && empty($_SESSION['GAME']['values']['battle_stars'][$temp_field_star_token]) ? true : false;

    // Define both the starforce and darkness battles to false
    $temp_darkness_battle = false;
    $temp_starforce_battle = false;

    // If the prototype is complete, this can be a starforce battle
    if ($temp_prototype_complete && $temp_field_star_present){
      $temp_starforce_battle = true;
    }

    // If the prototype and battle are complete plus robot unlocked, this can be a darkness battle
    if ($temp_prototype_complete && $temp_battle_complete && $temp_master_unlocked && $temp_master_unlocked2){
      // Only make this a darkness battle on a random chance and only if there isn't a star present
      if (!$temp_field_star_present && mt_rand(0, 3) == 0){
        $temp_darkness_battle = true;
        $temp_starforce_battle = false;
      }
    }

    // If a fusion star is preent on the field, fill the empty spots with like-typed robots
    if ($temp_starforce_battle){

      $temp_option_battle['battle_target_player']['player_switch'] = 2;
      $temp_robot_tokens = array();
      $temp_robot_tokens[] = $temp_option_battle['battle_target_player']['player_robots'][0]['robot_token'];
      $temp_robot_tokens[] = $temp_option_battle['battle_target_player']['player_robots'][1]['robot_token'];
      // Collect factors based on player
      if ($this_prototype_data['this_player_token'] == 'dr-light'){
        $temp_factors_list = array($this_omega_factors_one, array_merge($this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine));
      } elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){
        $temp_factors_list = array($this_omega_factors_two, array_merge($this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine, $this_omega_factors_one));
      } elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){
        $temp_factors_list = array($this_omega_factors_three, array_merge($this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine, $this_omega_factors_one, $this_omega_factors_two));
      }
      // Shuffle the bonus robots section of the list
      shuffle($temp_factors_list[1]);
      //$debug_backup = 'initial:count = '.count($temp_option_battle['battle_target_player']['player_robots']).' // ';
      // Loop through and add the robots
      $temp_counter = 0;
      foreach ($temp_factors_list AS $this_list){
        shuffle($this_list);
        foreach ($this_list AS $this_factor){
          if (empty($this_factor['robot'])){ continue; }
          $bonus_robot_index = $this_robot_index[$this_factor['robot']];
          if (!isset($bonus_robot_index['robot_core'])){ $bonus_robot_index['robot_core'] = ''; }
          if ($bonus_robot_index['robot_core'] == $temp_option_field['field_type'] || $bonus_robot_index['robot_core'] == $temp_option_field2['field_type']){
            if (!in_array($bonus_robot_index['robot_token'], $temp_robot_tokens)){
              $bonus_robot_info = array();
              $bonus_robot_info['flags']['hide_from_mission_select'] = true;
              $bonus_robot_info['robot_token'] = $bonus_robot_index['robot_token'];
              $bonus_robot_info['robot_class'] = $bonus_robot_index['robot_class'];
              $temp_option_battle['battle_target_player']['player_robots'][] = $bonus_robot_info;
              $temp_robot_tokens[] = $bonus_robot_info['robot_token'];
            }
          }
        }
      }
      //$debug_backup .= 'before:count = '.count($temp_option_battle['battle_target_player']['player_robots']).' // ';
      $temp_slice_limit = 4; //2 + $temp_option_battle['battle_complete']['battle_count'];
      //if ($temp_slice_limit >= (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 2)){ $temp_slice_limit = (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 2); }
      //elseif ($temp_slice_limit >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ $temp_slice_limit = 8; }
      $temp_option_battle['battle_target_player']['player_robots'] = array_slice($temp_option_battle['battle_target_player']['player_robots'], 0, $temp_slice_limit);
      shuffle($temp_option_battle['battle_target_player']['player_robots']);
      //$debug_backup .= 'after:count = '.count($temp_option_battle['battle_target_player']['player_robots']).' // ';

    }

    // Define the omega variables for level, points, turns, and random encounter rate
    $omega_robot_level_max = $this_start_level + 3;
    $omega_robot_level = $this_start_level + (!empty($this_prototype_data['battles_complete']) ? ($this_prototype_data['battles_complete'] - 10) * 1 : 0);
    if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
    $omega_random_encounter = false;

    // Define the battle rewards based on above data
    $temp_battle_omega['battle_rewards_robots'] = array();
    $temp_battle_omega['battle_rewards_abilities'] = array();
    if (!$temp_master_unlocked){ $temp_battle_omega['battle_rewards_robots'][] = array('token' => $this_robot_token); }
    if (!empty($temp_option_robot['robot_rewards']['abilities'])){
      foreach ($temp_option_robot['robot_rewards']['abilities'] AS $key => $info){
        if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
        if (!rpg_game::ability_unlocked('', '', $info['token'])){ $temp_battle_omega['battle_rewards_abilities'][] = $info; }
      }
    }
    if (!$temp_master_unlocked2){ $temp_battle_omega['battle_rewards_robots'][] = array('token' => $this_robot_token2); }
    if (!empty($temp_option_robot2['robot_rewards']['abilities'])){
      foreach ($temp_option_robot2['robot_rewards']['abilities'] AS $key => $info){
        if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
        if (!rpg_game::ability_unlocked('', '', $info['token'])){ $temp_battle_omega['battle_rewards_abilities'][] = $info; }
      }
    }

    // Fill the empty spots with minor enemy robots
    if (true){
      $temp_option_battle['battle_target_player']['player_switch'] = 2.0;
      $bonus_robot_count = $temp_target_count;
      $temp_mook_options = array();
      $temp_mook_letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
      $temp_mook_counts = array();
      if (!isset($temp_option_field['field_mechas'])){ $temp_option_field['field_mechas'] = array(); }
      if (!isset($temp_option_field2['field_mechas'])){ $temp_option_field2['field_mechas'] = array(); }
      for ($key = 0; $key < count($temp_option_field['field_mechas']); $key++){
        if (!empty($temp_option_field['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field['field_mechas'][$key]; }
        if (!empty($temp_option_field2['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field2['field_mechas'][$key]; }
      }
      $temp_mook_weights = array();
      $temp_mook_maxchance = count($temp_mook_options) * 30;
      foreach ($temp_mook_options AS $key => $token){ $temp_mook_weights[] = $temp_mook_maxchance - ($key * 15); }
      $temp_mook_options = array_slice($temp_mook_options, 0, ($temp_battle_count + 1));
      $temp_battle_omega['battle_field_info']['field_mechas'] = $temp_mook_options;
      if ($temp_darkness_battle){ $temp_battle_omega['battle_field_info']['field_mechas'] = array('dark-frag'); }
      $mt_rand = mt_rand(1, 4);

      // Allow mets to randomly show up in other battles at a low rate
      if (!empty($temp_battle_omega['battle_complete']) && !in_array('met', $temp_mook_options)){ $temp_mook_options[] = 'met'; $temp_mook_weights[] = 5; }

      // Loop through the allowed bonus robot count placing random mooks
      $temp_base_robot_count = count($temp_option_battle['battle_target_player']['player_robots']);
      for ($i = 0; $i < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX; $i++){

        $temp_count_target_robots = count($temp_option_battle['battle_target_player']['player_robots']);
        if ($temp_count_target_robots >= $bonus_robot_count){ break; }
        elseif ($temp_count_target_robots >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ break; }

        if ($temp_darkness_battle){ $temp_mook_token = 'dark-frag'; }
        else { $temp_mook_token = rpg_functions::weighted_chance($temp_mook_options, $temp_mook_weights); }

        $temp_mook_info = rpg_robot::parse_index_info($this_robot_index[$temp_mook_token]);
        $temp_mook_id = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_count_target_robots;
        $bonus_robot_info = array('robot_token' => $temp_mook_token, 'robot_id' => $temp_mook_id, 'robot_level' => 1);

        $bonus_robot_info['robot_abilities'] = $temp_mook_info['robot_abilities'];
        $bonus_robot_info['robot_class'] = !empty($temp_mook_info['robot_class']) ? $temp_mook_info['robot_class'] : 'master';
        $bonus_robot_info['robot_name'] = $temp_mook_info['robot_name'];

        $temp_mook_name_token = str_replace(' ', '-', strtolower($bonus_robot_info['robot_name']));
        $bonus_robot_info['robot_name_token']  = $temp_mook_name_token;
        if (!isset($temp_mook_counts[$temp_mook_name_token])){ $temp_mook_counts[$temp_mook_name_token] = 0; }
        else { $temp_mook_counts[$temp_mook_name_token]++; }

        $temp_option_battle['battle_target_player']['player_robots'][] = $bonus_robot_info;
        $temp_robot_tokens[] = $bonus_robot_info['robot_token'];

      }

      // Remove the "A" from any mooks that are one of their kind
      foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
        if (!isset($info['robot_class']) || $info['robot_class'] != 'mecha'){ continue; }
        elseif (!isset($temp_mook_counts[$info['robot_token']]) || $temp_mook_counts[$info['robot_token']] == 0){
          $temp_option_battle['battle_target_player']['player_robots'][$key]['robot_name'] = str_replace(' A', '', $info['robot_name']);
        }
      }

      // Loop through the target player robots to make final changes
      foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
        // Collect an index of this robot
        $index = rpg_robot::parse_index_info($this_robot_index[$info['robot_token']]);

        // Default the stat reward boost to zero to prevent early difficulities
        $temp_stat_reward_boost = 0;
        // Update the robot ID to prevent collisions
        $info['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1;
        // Append the appropriate letters to all the robot name tokens
        $info['robot_class'] = !empty($info['robot_class']) ? $info['robot_class'] : 'master';

        // Check to see if this is a support mecha and process changes accordingly
        if ($info['robot_class'] == 'mecha'){
          /*
          // Update this mecha's name with a letter if necessary
          $temp_name_token = isset($info['robot_name_token']) ? $info['robot_name_token'] : $info['robot_token'];
          if ($temp_mook_counts[$temp_name_token] > 0){
            if (!isset($temp_mook_counts[$temp_name_token])){ $temp_mook_counts[$temp_name_token] = 0; }
            else { $temp_mook_counts[$temp_name_token]++; }
            $info['robot_name'] .= ' '.$temp_mook_letters[$temp_mook_counts[$temp_name_token]];
          }
          */

          // If this is a starforce battle, boost the mechas stats appropriately
          if ($temp_starforce_battle){
            $temp_stat_reward_boost = 0.5;
          }
          // Otherwise if this is a darkness battle, boost the dark frags appropriately
          elseif ($temp_darkness_battle){
            $temp_stat_reward_boost = 1.0;
          }

        }
        // Otherwise, if this is a master robot, tweak their alt and stats
        else {

          // If this is s starforce battle, the robot master should be boosted in an alt colour
          if ($temp_starforce_battle){
            // Update the robot name, adding a delta symbol at the end
            $info['robot_name'] = $index['robot_name'].' Δ';
            $info['robot_number'] = $index['robot_number'].'Δ';
            // Update the robot image for this battle to be the alt version
            $info['robot_image'] = $info['robot_token'].'_alt2';
            // This robot should also have more stats than usual, so let's give 'em some
            $temp_stat_reward_boost = 1.0;
          }
          // Otherwise, if a darkness battle, the robot master should be boosted further and dark in colour
          elseif ($temp_darkness_battle){
            // Update the robot name, adding a lower-case sigma symbol at the end
            $info['robot_name'] = $index['robot_name'].' Σ';
            $info['robot_number'] = $index['robot_number'].'Σ';
            // Update the robot image for this battle to be the alt version
            $info['robot_image'] = $info['robot_token'].'_alt9';
            // Update the robot's core type to empty
            $info['robot_core'] = 'empty';
            $info['robot_core2'] = '';
            // Update the robot's weaknesses/resistances/etc.
            $info['robot_weaknesses'] = array();
            $info['robot_resistances'] = array();
            $info['robot_affinities'] = array();
            $info['robot_immunities'] = array(); //array('copy', 'crystal', 'cutter', 'earth', 'electric', 'explode', 'flame', 'freeze', 'impact', 'laser', 'missile', 'nature', 'shadow', 'shield', 'space', 'swift', 'time', 'water', 'wind');
            // Remove all quotes so this robot doesn't speak
            $info['robot_quotes'] = array();
            // This robot should also have more stats than usual, so let's give 'em more
            $temp_stat_reward_boost = 2.0;
          }
          // If the defined robot image does not actually exist, replace with placeholder base sprite
          if (isset($info['robot_image']) && !file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$info['robot_image'].'/')){
            $info['robot_image'] = $info['robot_token'];
          }

        }

        // If there is a reward booster in place, generate values
        if ($temp_stat_reward_boost > 0){

          // This robot should also have more stats than usual, so let's give 'em some more
          $temp_robot_rewards = array();
          //$temp_stat_total = $index['robot_energy'] + $index['robot_attack'] + $index['robot_defense'] + $index['robot_speed'];
          //$temp_robot_rewards['robot_energy'] = ceil(($index['robot_energy'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $temp_stat_total = $index['robot_attack'] + $index['robot_defense'] + $index['robot_speed'];
          $temp_robot_rewards['robot_attack'] = ceil(($index['robot_attack'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $temp_robot_rewards['robot_defense'] = ceil(($index['robot_defense'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $temp_robot_rewards['robot_speed'] = ceil(($index['robot_speed'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
          $info['values']['robot_rewards'] = $temp_robot_rewards;

        }

        // Update the player robots array with recent changes
        $temp_option_battle['battle_target_player']['player_robots'][$key] = $info;

      }

    }

    // Reassign robot IDs to prevent errors
    foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
      $temp_option_battle['battle_target_player']['player_robots'][$key]['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1;
    }
    $temp_option_battle['battle_rewards_robots'] = array();
    $temp_option_battle['battle_rewards_abilities'] = array();
    $temp_battle_flags = !empty($temp_battle_omega['flags']) ? $temp_battle_omega['flags'] : array();
    $temp_battle_values = !empty($temp_battle_omega['values']) ? $temp_battle_omega['values'] : array();
    $temp_battle_counters = !empty($temp_battle_omega['counters']) ? $temp_battle_omega['counters'] : array();
    $temp_battle_omega = $temp_option_battle;
    $temp_battle_omega['flags'] = $temp_battle_flags;
    $temp_battle_omega['values'] = $temp_battle_values;
    $temp_battle_omega['counters'] = $temp_battle_counters;

    // Skip the empty battle button or a different phase
    if (empty($temp_battle_omega['battle_token']) || $temp_battle_omega['battle_token'] == 'battle' || $temp_battle_omega['battle_phase'] != $this_prototype_data['battle_phase']){ return false; }

    // Collect the battle token and create an omega clone from the index base
    $temp_battle_token = $temp_battle_omega['battle_token'];
    // If the battle was already complete, collect its details and modify the mission
    $temp_complete_level = 0;
    $temp_complete_count = 0;
    if (!empty($temp_battle_omega['battle_complete'])){
      if (!empty($temp_battle_omega['battle_complete']['battle_min_level'])){ $temp_complete_level = $temp_battle_omega['battle_complete']['battle_min_level']; }
      else { $temp_complete_level = $omega_robot_level; }
      if (!empty($temp_battle_omega['battle_complete']['battle_count'])){ $temp_complete_count = $temp_battle_omega['battle_complete']['battle_count']; }
      else { $temp_complete_count = 1; }
      $omega_robot_level = $temp_complete_level + $temp_complete_count - 1;
      $omega_robot_level = $omega_robot_level >= 100 ? 100 : $omega_robot_level;
    }

    // If this is a starforce battle, we should double the level max
    if ($temp_starforce_battle){
      $omega_robot_level_max = $omega_robot_level_max * 2;
      $omega_robot_level = $omega_robot_level * 2;
    }
    // Otherwise if this is a darkness battle, we should triple the level max
    elseif ($temp_darkness_battle){
      $omega_robot_level_max = $omega_robot_level_max * 3;
      $omega_robot_level = $omega_robot_level * 3;
    }

    // Define the battle difficulty level (0 - 8) based on level and completed count
    $temp_battle_difficulty = ceil(8 * ($omega_robot_level / 100));
    $temp_battle_difficulty += $temp_complete_count;
    if ($temp_battle_difficulty >= 10){ $temp_battle_difficulty = 10; }
    $temp_battle_omega['battle_difficulty'] = $temp_battle_difficulty;
    // Update the robot level for this battle
    $temp_battle_omega['battle_level'] = $omega_robot_level;

    // Start all the point-based battle vars at zero
    $temp_battle_omega['battle_points'] = 0;
    $temp_battle_omega['battle_zenny'] = 0;
    $temp_battle_omega['battle_turns_limit'] = 0;
    $temp_battle_omega['battle_robots_limit'] = 0;

    // Loop through the target robots again update with omega values
    foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot){

      // Update the robot level and battle points plus turns
      if (!isset($this_robot_index[$robot['robot_token']])){ continue; }
      $temp_core_backup = !empty($robot['robot_core']) ? $robot['robot_core'] : '';
      $index = rpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
      $robot = array_merge($index, $robot);
      if (!empty($temp_core_backup)){ $robot['robot_core'] = $temp_core_backup; }
      if (!isset($robot['robot_item'])){ $robot['robot_item'] = ''; }
      $temp_opposite_type = $robot['robot_core'] != $temp_option_field['field_type'] ? $temp_option_field['field_type'] : $temp_option_field2['field_type'];

      // Increment allowable robots, points, and turns based on who's in the battle
      if ($robot['robot_class'] == 'mecha'){
        if (mt_rand(0, 6) == 0){ $robot['robot_item'] = 'item-shard-'.$temp_opposite_type; }
        $robot['robot_level'] = mt_rand(ceil($omega_robot_level / 2), $omega_robot_level);
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $robot['robot_item'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $omega_robot_level, ceil($temp_ability_count / 2), $robot['robot_item']);
      }
      elseif ($robot['robot_class'] == 'master'){
        if (mt_rand(0, 3) == 0){ $robot['robot_item'] = 'item-core-'.$temp_opposite_type; }
        $robot['robot_level'] = $omega_robot_level;
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $robot['robot_item'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $omega_robot_level, $temp_ability_count, $robot['robot_item']);
      }
      elseif ($robot['robot_class'] == 'boss'){
        $robot['robot_level'] = mt_rand($omega_robot_level, ceil($omega_robot_level * 2));
        $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
        //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = rpg_prototype::generate_abilities($robot, $omega_robot_level, floor($temp_ability_count * 2), $robot['robot_item']);
      }

      // Increment the battle's turn limit based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }

      // Increment the battle's point reward based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }

      // Increment the battle's zenny reward based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }

      // Increment the battle's robot limit based on the class of target robot
      if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
      elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
      elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }

    }

    // Increase expected turns if a starforce or darkness battle
    if ($temp_starforce_battle){ $temp_battle_omega['battle_turns_limit'] += round($temp_battle_omega['battle_turns_limit'] * 0.25); }
    elseif ($temp_darkness_battle){ $temp_battle_omega['battle_turns_limit'] += round($temp_battle_omega['battle_turns_limit'] * 0.50); }

    // Fix any zero or invalid battle values
    if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
    else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
    if ($temp_battle_omega['battle_turns_limit'] < 1){ $temp_battle_omega['battle_turns_limit'] = 1; }
    else { $temp_battle_omega['battle_turns_limit'] = ceil($temp_battle_omega['battle_turns_limit']); }
    if ($temp_battle_omega['battle_robots_limit'] < 1){ $temp_battle_omega['battle_robots_limit'] = 1; }
    else { $temp_battle_omega['battle_robots_limit'] = ceil($temp_battle_omega['battle_robots_limit']); }

    // Recollect the option robots
    $temp_option_robot = $this_robot_index[$temp_option_robot['robot_token']];
    $temp_option_robot2 = $this_robot_index[$temp_option_robot2['robot_token']];

    // Reverse the order of the robots in battle
    $temp_battle_omega['battle_target_player']['player_robots'] = array_reverse($temp_battle_omega['battle_target_player']['player_robots']);
    $temp_first_robot = array_shift($temp_battle_omega['battle_target_player']['player_robots']);
    shuffle($temp_battle_omega['battle_target_player']['player_robots']);
    array_unshift($temp_battle_omega['battle_target_player']['player_robots'], $temp_first_robot);

    // Empty the robot rewards array if not allowed
    if (!$this_unlock_robots){ $temp_battle_omega['battle_rewards_robots'] = array(); }
    // Empty the ability rewards array if not allowed
    if (!$this_unlock_abilities){ $temp_battle_omega['battle_rewards_abilities'] = array(); }

    // Define the number of abilities and robots left to unlock and start at zero
    $this_unlock_robots_count = count($temp_battle_omega['battle_rewards_robots']);
    $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards_abilities']);

    // Loop through the omega battle robot rewards and update the robot levels there too
    if (!empty($temp_battle_omega['battle_rewards_robots'])){
      foreach ($temp_battle_omega['battle_rewards_robots'] AS $key2 => $robot){
        // Update the robot level and battle points plus turns
        $temp_battle_omega['battle_rewards_robots'][$key2]['level'] = $omega_robot_level;
        // Remove if this robot is already unlocked
        if (rpg_game::robot_unlocked(false, $robot['token'])){ $this_unlock_robots_count -= 1; }
      }
    }

    // Loop through the omega battle ability rewards and update the ability levels there too
    if (!empty($temp_battle_omega['battle_rewards_abilities'])){
      foreach ($temp_battle_omega['battle_rewards_abilities'] AS $key2 => $ability){
        // Remove if this ability is already unlocked
        if (rpg_game::ability_unlocked($this_prototype_data['this_player_token'], false, $ability['token'])){ $this_unlock_abilities_count -= 1; }
      }
    }

    // Check to see if we should be adding a field star to this battle
    if ($temp_starforce_battle){
      // Generate the necessary field star variables and add them to the battle data
      $temp_field_star = array();
      $temp_field_star['star_name'] = $temp_battle_omega['battle_field_info']['field_name'];
      $temp_field_star['star_token'] = $temp_field_star_token;
      $temp_field_star['star_kind'] = 'fusion';
      $temp_field_star['star_type'] = !empty($temp_battle_omega['battle_field_info']['field_type']) ? $temp_battle_omega['battle_field_info']['field_type'] : 'none';
      $temp_field_star['star_type2'] = !empty($temp_battle_omega['battle_field_info']['field_type2']) ? $temp_battle_omega['battle_field_info']['field_type2'] : 'none';
      $temp_field_star['star_field'] = $temp_option_field['field_token'];
      $temp_field_star['star_field2'] = $temp_option_field2['field_token'];
      $temp_field_star['star_player'] = $this_prototype_data['this_player_token'];
      $temp_field_star['star_date'] = time();
      $temp_battle_omega['values']['field_star'] = $temp_field_star;
      $temp_battle_omega['battle_target_player']['player_starforce'] = array();
      if ($temp_field_star['star_type'] == $temp_field_star['star_type2']){
        $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type']] = 2;
      } else {
        $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type']] = 1;
        $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type2']] = 1;
      }

    }

    // Check to see if we should be adding darkness to this battle
    if ($temp_darkness_battle){
      // Generate the necessary dark tower variables and add them to the battle data
      $temp_battle_omega['flags']['dark_tower'] = true;
    }

    // Update the battle description based on what we've calculated
    $temp_description_target_robots = $temp_option_robot['robot_name'].' and '.$temp_option_robot2['robot_name'];
    if (!empty($this_unlock_abilities_count)){
      $temp_battle_omega['battle_description'] = 'Defeat '.$temp_description_target_robots.' and download their special weapons!';
      $temp_battle_omega['battle_description2'] = 'Once we\'ve acquired them, we may be able to equip the abilities to other robots...';
    } elseif (!empty($this_unlock_robots_count)){
      $temp_battle_omega['battle_description'] = 'Defeat '.$temp_description_target_robots.' and download their robot data!';
      $temp_battle_omega['battle_description2'] = 'If we use only Neutral type abilities on the targets we may be able to save them...';
    } elseif ($temp_starforce_battle){
      $temp_battle_omega['battle_description'] = 'Defeat the starforce boosted '.$temp_description_target_robots.' and collect their Fusion Star! ';
      $temp_battle_omega['battle_description2'] = 'The '.ucfirst($temp_option_field['field_type']).' type energy appears to have attracted more robots to the field...';
    } elseif ($temp_darkness_battle){
      $temp_battle_omega['battle_description'] = 'Defeat the darkness boosted '.$temp_description_target_robots.' and destroy their Dark Tower! ';
      $temp_battle_omega['battle_description2'] = 'The negative energy appears to have attracted an army of dark elements to the field...';
    } else {
      $temp_battle_omega['battle_description'] = 'Defeat '.$temp_description_target_robots.'!';
    }

    // If this battle has been completed already, decrease the points
    //if ($temp_battle_omega['battle_complete']){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] * 0.10); }
    // Add some random item drops to the starter battle
    $temp_battle_omega['battle_rewards_items'] = array(
      //array('chance' => 1, 'token' => 'item-energy-tank'),
      //array('chance' => 1, 'token' => 'item-weapon-tank')
      );

    // Return the generated battle data
    return $temp_battle_omega;

  }


  // Define a function for generating the PLAYER missions
  function generate_player($this_prototype_data, $this_user_info, $this_max_robots, &$field_factors_one, &$field_factors_two, &$field_factors_three){
    // Pull in global variables for this function
    global $mmrpg_index, $this_database, $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine;

    $this_field_index = rpg_field::get_index();
    // Define the omega battle and default to empty
    $temp_battle_omega = array();
    $temp_battle_omega['flags']['save_records'] = false;
    $temp_battle_omega['flags']['player_battle'] = true;
    $temp_battle_omega['values']['player_battle_masters'] = array();
    $temp_battle_omega['values']['player_battle_level'] = 1;

    // Define the local scope current player
    $this_player_token = $this_prototype_data['this_player_token'];
    $target_player_token = $this_prototype_data['target_player_token'];
    $target_player_token_backup = $target_player_token;

    // DEBUG
    //die('<pre>'.print_r($temp_player_array, true).'</pre>');

    // Pull and random player from the list and collect their full data
    $temp_player_array = $this_user_info; /* $temp_player_array = $this_database->get_array("SELECT users.*, saves.*, boards.* FROM mmrpg_users AS users
      LEFT JOIN mmrpg_saves AS saves ON saves.user_id = users.user_id
      LEFT JOIN mmrpg_leaderboard AS boards ON boards.user_id = users.user_id
    	WHERE users.user_id = {$this_user_id}
    	"); */

    // Add this player data to the omage array
    $temp_battle_omega_player = $temp_player_array;

    // DEBUG
    //echo('<pre>'.print_r($temp_player_array, true).'</pre>');

    // Collect the player values and decode the rewards and settings arrays
    $temp_player_rewards = $temp_player_array['player_rewards'];
    $temp_player_settings = $temp_player_array['player_settings'];
    $temp_player_starforce = $temp_player_array['player_starforce'];
    $temp_player_favourites = $temp_player_array['player_favourites'];
    // Calculate what level these bonus robots should be in the range of
    $temp_player_rewards2 = rpg_game::player_rewards($this_prototype_data['this_player_token']);
    $temp_total_level = 0;
    $temp_total_robots = 0;
    $temp_bonus_level_min = 100;
    $temp_bonus_level_max = 1;
    if (!empty($temp_player_rewards2['player_robots'])){
      foreach ($temp_player_rewards2['player_robots'] AS $token => $info){
        $temp_level = !empty($info['robot_level']) ? $info['robot_level'] : 1;
        if ($temp_level > $temp_bonus_level_max){ $temp_bonus_level_max = $temp_level; }
        if ($temp_level < $temp_bonus_level_min){ $temp_bonus_level_min = $temp_level; }
        $temp_total_robots++;
      }
      //$temp_bonus_level_max = ceil($temp_total_level / $temp_total_robots);
      //$temp_bonus_level_min = ceil($temp_bonus_level_max / 3);
    }

    // Round the number to the nearst multiple of ten so it looks nicer
    $temp_player_battle_level = $temp_bonus_level_max;
    $temp_player_battle_level = floor($temp_player_battle_level * 0.10) * 10;
    if ($temp_player_battle_level < 10){ $temp_player_battle_level = 10; }

    // Update the player battle level to match that of this player's highest
    $temp_battle_omega['values']['player_battle_level'] = $temp_player_battle_level;

    // Create the empty array for the target player's battle robots
    $temp_player_robots = array();
    $temp_player_robots_rewards = !empty($temp_player_rewards[$target_player_token]['player_robots']) ? $temp_player_rewards[$target_player_token]['player_robots'] : array();
    $temp_player_robots_settings = !empty($temp_player_settings[$target_player_token]['player_robots']) ? $temp_player_settings[$target_player_token]['player_robots'] : array();
    $temp_player_field_settings = !empty($temp_player_settings[$target_player_token]['player_fields']) ? $temp_player_settings[$target_player_token]['player_fields'] : array();
    if (empty($temp_player_robots_rewards)){
      foreach ($temp_player_rewards AS $ptoken => $pinfo){
        if (!empty($temp_player_rewards[$ptoken]['player_robots'])){
          $target_player_token = $ptoken;
          $temp_player_robots_rewards = !empty($temp_player_rewards[$target_player_token]['player_robots']) ? $temp_player_rewards[$target_player_token]['player_robots'] : array();
          $temp_player_robots_settings = !empty($temp_player_settings[$target_player_token]['player_robots']) ? $temp_player_settings[$target_player_token]['player_robots'] : array();
          $temp_player_field_settings = !empty($temp_player_settings[$target_player_token]['player_fields']) ? $temp_player_settings[$target_player_token]['player_fields'] : array();
          break;
        }
      }
    }
    //echo('<pre>'.__FILE__.' on line '.__LINE__.' : $temp_player_robots_rewards = '.preg_replace('/\s+/', ' ', print_r($temp_player_robots_rewards, true)).'</pre>');
    //echo('<pre>'.__FILE__.' on line '.__LINE__.' : $temp_player_robots_settings = '.preg_replace('/\s+/', ' ', print_r($temp_player_robots_settings, true)).'</pre>');
    // If the player fields setting is empty, define manually
    if (empty($temp_player_field_settings)){
      $temp_omega_fields = array();
      if ($target_player_token == 'dr-light'){ $temp_omega_fields = $this_omega_factors_one; }
      elseif ($target_player_token == 'dr-wily'){ $temp_omega_fields = $this_omega_factors_two; }
      elseif ($target_player_token == 'dr-cossack'){ $temp_omega_fields = $this_omega_factors_three; }
      foreach ($temp_omega_fields AS $omega){ $temp_player_field_settings[$omega['field']] = array('field_token' => $omega['field']); }
    }

    // Ensure this player has been unlocked by the target before continuing
    if (!empty($temp_player_robots_rewards)){
      //echo('<pre>'.__FILE__.' on line '.__LINE__.' : '.preg_replace('/\s+/', ' ', print_r($temp_player_rewards[$target_player_token], true)).'</pre>');

      // Collect the target player's robot rewards from the array
      $temp_player_robots = $temp_player_robots_rewards;

      // Define the array to hold the omega battle robots
      $temp_battle_omega_robots = array();

      // Loop through the reward robots and append their info
      $temp_counter = 1;
      foreach ($temp_player_robots AS $key => $temp_robotinfo){
        // Skip if does not exist
        if (empty($temp_robotinfo['robot_token'])){ continue; }
        // Collect this robot's settings if they exist
        if (!empty($temp_player_robots_settings[$temp_robotinfo['robot_token']])){ $temp_settings_array = $temp_player_robots_settings[$temp_robotinfo['robot_token']]; }
        else { $temp_settings_array = $temp_robotinfo; }
        // Collect this robot's rewards if they exist
        if (!empty($temp_player_robots_rewards[$temp_robotinfo['robot_token']])){ $temp_rewards_array = $temp_player_robots_rewards[$temp_robotinfo['robot_token']]; }
        else { $temp_rewards_array = $temp_robotinfo; }
        // Collect the basic details of this robot like ID, token, and level
        $temp_robot_id = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_counter;
        $temp_robot_token = $temp_robotinfo['robot_token'];
        $temp_robot_level = $temp_battle_omega['values']['player_battle_level']; //!empty($temp_robotinfo['robot_level']) ? $temp_robotinfo['robot_level'] : 1;
        $temp_robot_favourite = in_array($temp_robot_token, $temp_player_favourites) ? 1 : 0;
        $temp_robot_image = !empty($temp_settings_array['robot_image']) ? $temp_settings_array['robot_image'] : $temp_robotinfo['robot_token'];
        //$temp_robot_rewards = $temp_player_rewards[$target_player_token];
        $temp_robot_rewards = $temp_rewards_array;
        $temp_robot_settings = $temp_settings_array;
        // Collect this robot's abilities, format them, and crop if necessary
        $temp_robot_abilities = array();
        foreach ($temp_settings_array['robot_abilities'] AS $key2 => $temp_abilityinfo){ $temp_robot_abilities[] = $temp_abilityinfo['ability_token'] != 'copy-shot' ? $temp_abilityinfo['ability_token'] : 'buster-shot'; }
        $temp_robot_abilities = count($temp_robot_abilities) > 8 ? array_slice($temp_robot_abilities, 0, 8) : $temp_robot_abilities;
        // Create the new robot info array to be added to the omega battle options
        $temp_new_array = array('values' => array('flag_favourite' => $temp_robot_favourite, 'robot_rewards' => $temp_robot_rewards), 'robot_id' => $temp_robot_id, 'robot_token' => $temp_robot_token, 'robot_level' => $temp_robot_level, 'robot_image' => $temp_robot_image, 'robot_abilities' => $temp_robot_abilities);
        // Add this robot to the omega array and increment the counter
        $temp_battle_omega_robots[] = $temp_new_array;
        $temp_counter++;

      }

      // Sort the player's robots according to their level
      usort($temp_battle_omega_robots, 'mmrpg_prototype_sort_player_robots');

      // Slice the robot array based on the max num requested
      $temp_max_robots = $this_max_robots;
      $temp_omega_robots_count = count($temp_battle_omega_robots);
      if ($temp_omega_robots_count > $temp_max_robots){
        $temp_battle_omega_robots = array_slice($temp_battle_omega_robots, 0, $temp_max_robots);
        shuffle($temp_battle_omega_robots);
      } elseif ($temp_omega_robots_count < $temp_max_robots){
        $temp_max_robots = $temp_omega_robots_count;
      }
      $temp_omega_robots_count = count($temp_battle_omega_robots);


      // DEBUG
      //die('<pre><strong>$temp_battle_omega_robots</strong><br />'.print_r($temp_battle_omega_robots, true).'</pre>');

      // Populate the battle options with the player battle option
      $temp_battle_userid = $temp_battle_omega_player['user_id'];
      $temp_battle_usertoken = $temp_battle_omega_player['user_name_clean'];
      $temp_battle_username = !empty($temp_battle_omega_player['user_name_public']) ? $temp_battle_omega_player['user_name_public'] : $temp_battle_omega_player['user_name'];
      $temp_battle_userpronoun = ($temp_battle_omega_player['user_gender'] == 'male' ? 'his' : ($temp_battle_omega_player['user_gender'] == 'female' ? 'her' : ('their')));
      //$temp_battle_userimage = !empty($temp_battle_omega_player['user_image_path']) ? $temp_battle_omega_player['user_image_path'] : 'robots/mega-man';
      $temp_robots_num = count($temp_battle_omega_robots);
      $temp_battle_token = $this_prototype_data['phase_battle_token'].'-vs-player-'.$temp_battle_usertoken;
      $backup_fields = array('flags', 'values', 'counters');
      $backup_values = array();
      foreach ($backup_fields AS $field){ $backup_values[$field] = isset($temp_battle_omega[$field]) ? $temp_battle_omega[$field] : array(); }
      $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-3');
      foreach ($backup_fields AS $field){ $temp_battle_omega[$field] = isset($temp_battle_omega[$field]) ? array_replace($temp_battle_omega[$field], $backup_values[$field]) : $backup_values[$field]; }
      $temp_challenge_type = ($temp_max_robots == 8 ? 'an ' : 'a ').$temp_max_robots.'-on-'.$temp_max_robots;
      $temp_star_boost = !empty($temp_player_starforce) ? array_sum($temp_player_starforce) : 0;
      $temp_battle_omega['battle_token'] = $temp_battle_token;
      $temp_battle_omega['battle_size'] = '1x2';
      $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
      $temp_battle_omega['battle_name'] = 'Player Battle vs '.$temp_battle_username;
      if (!empty($temp_star_boost)){
        reset($temp_player_starforce);
        $temp_most_powerful = key($temp_player_starforce);
        $temp_most_powerful_value = $temp_player_starforce[$temp_most_powerful];
        //$temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;s starforce boosted player data in a '.$temp_challenge_type.' battle! The '.ucfirst($temp_most_powerful).' type appears to be '.$temp_battle_userpronoun.' most powerful element, with '.($temp_most_powerful_value * 10).'&nbsp;/&nbsp;'.($temp_star_boost * 10).'% of the total boost!';
        $temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;'.(!preg_match('/s$/i', $temp_battle_username) ? 's' : '').' starforce boosted player data in '.$temp_challenge_type.' battle!';
        $temp_battle_omega['battle_description2'] = 'The '.ucfirst($temp_most_powerful).' type appears to be '.$temp_battle_userpronoun.' most powerful element, with nearly '.ceil(($temp_most_powerful_value / $temp_star_boost) * 100).'% of the total boost!';
      } else {
        $temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;'.(!preg_match('/s$/i', $temp_battle_username) ? 's' : '').' player data in '.$temp_challenge_type.' battle!';
        $temp_battle_omega['battle_description2'] = '';
      }
      $temp_battle_omega['battle_turns_limit'] = ceil(MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $temp_robots_num * MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER);
      $temp_battle_omega['battle_robots_limit'] = $this_max_robots;
      $temp_battle_omega['battle_points'] = 0;
      foreach ($temp_battle_omega_robots AS $info){
        $temp_stat_counter = 0;
        $temp_robot_rewards = !empty($info['values']['robot_rewards']) ? $info['values']['robot_rewards'] : array();
        if (!empty($temp_robot_rewards['robot_energy'])){ $temp_stat_counter += $temp_robot_rewards['robot_energy']; }
        if (!empty($temp_robot_rewards['robot_attack'])){ $temp_stat_counter += $temp_robot_rewards['robot_attack']; }
        if (!empty($temp_robot_rewards['robot_defense'])){ $temp_stat_counter += $temp_robot_rewards['robot_defense']; }
        if (!empty($temp_robot_rewards['robot_speed'])){ $temp_stat_counter += $temp_robot_rewards['robot_speed']; }
        $temp_battle_omega['battle_points'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT * $info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER) + $temp_stat_counter;
        $temp_battle_omega['battle_points'] += !empty($temp_star_boost) ? ceil(($temp_star_boost * $temp_stat_counter) / MMRPG_SETTINGS_STARS_ATTACKBOOST) : 0;
        $temp_battle_omega['values']['player_battle_masters'][] = $info['robot_token'];
      }
      //if (!empty($temp_star_boost)){ $temp_battle_omega['battle_points'] += ceil($temp_star_boost * 1000);  }
      // Define the fusion field properties
      //$temp_battle_omega['battle_field_info']['field_name'] = ucfirst($temp_battle_username); //'Player Battle : '.$temp_battle_username;
      $temp_battle_omega['battle_button'] = ucfirst($temp_battle_username);
      $temp_field_info_options = array_keys($temp_player_field_settings);
      $temp_rand_int = mt_rand(1, 4);
      $temp_rand_start = ($temp_rand_int - 1) * 2;
      $temp_field_info_options = array_slice($temp_field_info_options, $temp_rand_start, 2);
      //shuffle($temp_field_info_options);
      $temp_field_token_one = $temp_field_info_options[0];
      $temp_field_token_two = $temp_field_info_options[1];
      $temp_field_info_one = rpg_field::parse_index_info($this_field_index[$temp_field_token_one]);
      $temp_field_info_two = rpg_field::parse_index_info($this_field_index[$temp_field_token_two]);
      $temp_option_multipliers = array();
      $temp_option_field_list = array($temp_field_info_one, $temp_field_info_two);
      $temp_battle_omega['battle_field_info']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_field_info_one['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_field_info_two['field_name']);
      foreach ($temp_option_field_list AS $temp_field){
        if (!empty($temp_field['field_multipliers'])){
          foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
            if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
            else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
          }
        }
      }
      //$temp_battle_omega['battle_field_info']['field_music'] = $temp_field_token_three['field'];
      $temp_battle_omega['battle_field_info']['field_type'] = !empty($temp_field_info_one['field_type']) ? $temp_field_info_one['field_type'] : '';
      $temp_battle_omega['battle_field_info']['field_type2'] = !empty($temp_field_info_two['field_type']) ? $temp_field_info_two['field_type'] : '';
      $temp_battle_omega['battle_field_info']['field_music'] = $temp_field_token_two;
      $temp_battle_omega['battle_field_info']['field_background'] = $temp_field_token_one;
      $temp_battle_omega['battle_field_info']['field_foreground'] = $temp_field_token_two;
      // Update the battle robot limit once more in case target had fewer robots than anticipated
      $temp_battle_omega['battle_robots_limit'] = count($temp_battle_omega_robots);
      //$temp_battle_omega['battle_description'] .= ' // starforce:+'.($temp_star_boost * 10).'% // background:'.$temp_battle_omega['battle_field_info']['field_background'].' / foreground:'.$temp_battle_omega['battle_field_info']['field_foreground'];
      $temp_battle_omega['battle_field_info']['field_multipliers'] = $temp_option_multipliers;
      $temp_battle_omega['battle_field_info']['field_mechas'] = array();
      if (!empty($temp_field_info_one['field_mechas'])){ $temp_battle_omega['battle_field_info']['field_mechas'] = array_merge($temp_battle_omega['battle_field_info']['field_mechas'], $temp_field_info_one['field_mechas']); }
      if (!empty($temp_field_info_two['field_mechas'])){ $temp_battle_omega['battle_field_info']['field_mechas'] = array_merge($temp_battle_omega['battle_field_info']['field_mechas'], $temp_field_info_two['field_mechas']); }
      //if (!empty($temp_option_field_list[2]['field_mechas'])){ $temp_battle_omega['battle_field_info']['field_mechas'] = array_merge($temp_battle_omega['battle_field_info']['field_mechas'], $temp_option_field_list[2]['field_mechas']); }
      if (empty($temp_battle_omega['battle_field_info']['field_mechas'])){ $temp_battle_omega['battle_field_info']['field_mechas'][] = 'met'; }
      $temp_battle_omega['battle_field_info']['field_background_frame'] = $temp_field_info_one['field_background_frame'];
      $temp_battle_omega['battle_field_info']['field_foreground_frame'] = $temp_field_info_two['field_foreground_frame'];
      $temp_battle_omega['battle_field_info']['field_background_attachments'] = $temp_field_info_one['field_background_attachments'];
      $temp_battle_omega['battle_field_info']['field_foreground_attachments'] = $temp_field_info_two['field_foreground_attachments'];
      // Define the final details for the player
      $temp_battle_omega['battle_target_player']['player_id'] = $temp_battle_userid;
      $temp_battle_omega['battle_target_player']['player_token'] = $target_player_token_backup;
      $temp_battle_omega['battle_target_player']['player_name'] = ucfirst($temp_battle_username);
      $temp_battle_omega['battle_target_player']['player_robots'] = $temp_battle_omega_robots;
      $temp_battle_omega['battle_target_player']['player_starforce'] = $temp_player_starforce;

    } else {

      return false;

    }

    // Return the generated battle data
    return $temp_battle_omega;

  }


  // Define a function for generating the BONUS missions
  public static function generate_bonus($this_prototype_data, $this_robot_count = 8, $this_robot_class = 'master'){
    // Pull in global variables for this function
    global $mmrpg_index, $this_database;

    // Collect the robot index for calculation purposes
    $this_robot_index = $this_database->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    // Populate the battle options with the starter battle option
    $temp_rand_num = $this_robot_count;
    $temp_battle_token = $this_prototype_data['phase_battle_token'].'-prototype-bonus-'.$this_robot_class;
    if ($this_robot_class == 'mecha'){
      $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete');
      $temp_battle_omega['battle_field_info']['field_name'] = 'Bonus Field';
    }
    elseif ($this_robot_class == 'master'){
      $temp_battle_omega = rpg_battle::get_index_info('bonus-prototype-complete-2');
      $temp_battle_omega['battle_field_info']['field_name'] = 'Bonus Field II';
    }
    // Populate the player's target robots with compatible class matches
    $temp_battle_omega['battle_target_player']['player_robots'] = array();
    $temp_counter = 0;
    foreach ($this_robot_index AS $token => $info){
      if (empty($info['robot_flag_complete']) || $info['robot_class'] != $this_robot_class){ continue; }
      $temp_counter++;
      $temp_robot_info = array();
      $temp_robot_info['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_counter;
      $temp_robot_info['robot_token'] = $info['robot_token'];
      $temp_robot_info['robot_core'] = $info['robot_core'];
      $temp_robot_info['robot_core2'] = $info['robot_core2'];
      $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_robot_info;
    }
    //die('<pre>player_robots '.print_r($temp_battle_omega['battle_target_player']['player_robots'], true).'</pre>');
    // Continue defining battle variables for this mission
    $temp_battle_omega['flags']['bonus_battle'] = true;
    $temp_battle_omega['battle_token'] = $temp_battle_token;
    $temp_battle_omega['battle_size'] = '1x4';
    $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
    //if ($this_robot_class == 'mecha'){ $temp_battle_omega['battle_turns_limit'] = MMRPG_SETTINGS_BATTLETURNS_PERMECHA * $this_robot_count; }
    //elseif ($this_robot_class == 'master'){ $temp_battle_omega['battle_turns_limit'] = MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $this_robot_count; }
    //$temp_battle_omega['battle_points'] = ceil(($this_prototype_data['battles_complete'] > 1 ? 100 : 1000) * $temp_rand_num);
    //shuffle($temp_battle_omega['battle_target_player']['player_robots']);


    // Create the randomized field multupliers
    $temp_types = $mmrpg_index['types'];
    $temp_allow_special = array(); //, 'damage', 'recovery', 'experience'
    foreach ($temp_types AS $key => $temp_type){ if (!empty($temp_type['type_class']) && $temp_type['type_class'] == 'special' && !in_array($temp_type['type_token'], $temp_allow_special)){ unset($temp_types[$key]); } }
    //$temp_battle_omega['battle_field_info']['field_multipliers']['experience'] = round((mt_rand(200, 300) / 100), 1);
    //$temp_battle_omega['battle_field_info']['field_type'] = $temp_types[array_rand($temp_types)]['type_token'];
    //do { $temp_battle_omega['battle_field_info']['field_type2'] = $temp_types[array_rand($temp_types)]['type_token'];
    //} while($temp_battle_omega['battle_field_info']['field_type2'] == $temp_battle_omega['battle_field_info']['field_type']);
    $temp_battle_omega['battle_field_info']['field_multipliers'] = array();
    while (count($temp_battle_omega['battle_field_info']['field_multipliers']) < 6){
      $temp_type = $temp_types[array_rand($temp_types)];
      $temp_multiplier = 1;
      while ($temp_multiplier == 1){ $temp_multiplier = round((mt_rand(10, 990) / 100), 1); }
      $temp_battle_omega['battle_field_info']['field_multipliers'][$temp_type['type_token']] = $temp_multiplier;
      //if (count($temp_battle_omega['battle_field_info']['field_multipliers']) >= 6){ break; }
    }


    // Update the field type based on multipliers
    $temp_multipliers = $temp_battle_omega['battle_field_info']['field_multipliers'];
    asort($temp_multipliers);
    $temp_multipliers = array_keys($temp_multipliers);
    $temp_battle_omega['battle_field_info']['field_type'] = array_pop($temp_multipliers);
    $temp_battle_omega['battle_field_info']['field_type2'] = array_pop($temp_multipliers);

    // Collect the field types into a simple array
    $temp_field_types = array($temp_battle_omega['battle_field_info']['field_type'], $temp_battle_omega['battle_field_info']['field_type2']);

    // Give the robots a quick shuffle before sorting by core
    shuffle($temp_battle_omega['battle_target_player']['player_robots']);
    //die('<pre>player_robots '.print_r($temp_battle_omega['battle_target_player']['player_robots'], true).'</pre>');

    // Sort the robots by their relevance to the field type
    usort($temp_battle_omega['battle_target_player']['player_robots'], function($r1, $r2) use ($temp_field_types, $this_robot_index){
      //global $temp_field_types, $this_robot_index;
      $r1_core = !empty($r1['robot_core']) ? $r1['robot_core'] : '';
      $r2_core = !empty($r2['robot_core']) ? $r2['robot_core'] : '';
      if (in_array($r1_core, $temp_field_types) && !in_array($r2_core, $temp_field_types)){ return -1; }
      elseif (!in_array($r1_core, $temp_field_types) && in_array($r2_core, $temp_field_types)){ return 1; }
      else { return 0; }
    });

    //die('<pre>field_types = '.implode(', ', $temp_field_types).' | player_robots '.print_r($temp_battle_omega['battle_target_player']['player_robots'], true).'</pre>');

    $temp_battle_omega['battle_target_player']['player_robots'] = array_slice($temp_battle_omega['battle_target_player']['player_robots'], 0, $this_robot_count);

    // Calculate what level these bonus robots should be in the range of
    $temp_player_rewards = rpg_game::player_rewards($this_prototype_data['this_player_token']);
    $temp_total_level = 0;
    $temp_total_robots = 0;
    $temp_bonus_level_min = 100;
    $temp_bonus_level_max = 1;
    if (!empty($temp_player_rewards['player_robots'])){
      foreach ($temp_player_rewards['player_robots'] AS $token => $info){
        $temp_level = !empty($info['robot_level']) ? $info['robot_level'] : 1;
        if ($temp_level > $temp_bonus_level_max){ $temp_bonus_level_max = $temp_level; }
        if ($temp_level < $temp_bonus_level_min){ $temp_bonus_level_min = $temp_level; }
        $temp_total_robots++;
      }
      //$temp_bonus_level_max = ceil($temp_total_level / $temp_total_robots);
      //$temp_bonus_level_min = ceil($temp_bonus_level_max / 3);
    }

    // Start all the point-based battle vars at zero
    $temp_battle_omega['battle_points'] = 0;
    $temp_battle_omega['battle_zenny'] = 0;
    $temp_battle_omega['battle_turns_limit'] = 0;
    $temp_battle_omega['battle_robots_limit'] = 0;

    // Define the possible items for bonus mission robot masters
    $possible_master_items = array('item-energy-upgrade', 'item-weapon-upgrade', 'item-target-module', 'item-charge-module', 'item-fortune-module', 'item-field-booster', 'item-attack-booster', 'item-defense-booster', 'item-speed-booster');
    foreach ($mmrpg_index['types'] AS $token => $info){
      if (!empty($info['type_class']) && $info['type_class'] == 'special'){ continue; }
      elseif (in_array($token, array('copy', 'empty'))){ continue; }
      $possible_master_items[] = 'item-core-'.$token;
    }
    $possible_master_items_last_key = count($possible_master_items) - 1;

    // Loop through each of the bonus robots and update their levels
    foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot){
      $robot['robot_level'] = mt_rand($temp_bonus_level_min, $temp_bonus_level_max);
      $index = rpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
      if ($this_robot_class != 'mecha'){ $robot['robot_item'] = $possible_master_items[mt_rand(0, $possible_master_items_last_key)]; }
      else { $robot['robot_item'] = ''; }
      $robot['robot_abilities'] = rpg_prototype::generate_abilities($index, $robot['robot_level'], 8, $robot['robot_item']);


      // Increment the battle's turn limit based on the class of target robot
      if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
      elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
      elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }

      // Increment the battle's point reward based on the class of target robot
      if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
      elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
      elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }

      // Increment the battle's zenny reward based on the class of target robot
      if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
      elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
      elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }

      // Increment the battle's robot limit based on the class of target robot
      if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
      elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
      elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }

      $temp_battle_omega['battle_target_player']['player_robots'][$key] = $robot;
    }

    // Fix any zero or invalid battle values
    if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
    else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
    if ($temp_battle_omega['battle_turns_limit'] < 1){ $temp_battle_omega['battle_turns_limit'] = 1; }
    else { $temp_battle_omega['battle_turns_limit'] = ceil($temp_battle_omega['battle_turns_limit']); }
    if ($temp_battle_omega['battle_robots_limit'] < 1){ $temp_battle_omega['battle_robots_limit'] = 1; }
    else { $temp_battle_omega['battle_robots_limit'] = ceil($temp_battle_omega['battle_robots_limit']); }

    // Multiply battle points and zenny by ten for bonus amount (basically a cheating stage)
    $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 10);
    $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] / 10);


    //if ($this_robot_class == 'mecha'){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 100); }
    //elseif ($this_robot_class == 'master'){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 10); }
    //elseif ($this_robot_class == 'master'){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 10); }
    //if ($this_robot_class == 'mecha'){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 100); }
    //elseif ($this_robot_class == 'master'){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 10); }
    //elseif ($this_robot_class == 'master'){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] / 10); }

    // types used to be here

    // Update the field music to a random boss theme from MM1-10 + MM&B
    $temp_music_number = mt_rand(1, 11);
    $temp_music_name = 'boss-theme-mm'.str_pad($temp_music_number, 2, '0', STR_PAD_LEFT);
    $temp_battle_omega['battle_field_info']['field_music'] = $temp_music_name;

    // Add some random item drops to the starter battle
    $temp_battle_omega['battle_rewards_items'] = array(
      array('chance' => 2, 'token' => 'item-energy-tank'),
      array('chance' => 2, 'token' => 'item-weapon-tank'),
      array('chance' => 1, 'token' => 'item-yashichi'),
      array('chance' => 1, 'token' => 'item-extra-life')
      );

    // Return the generated battle data
    return $temp_battle_omega;

  }


  // Define a function for generating the FORTRESS missions
  public static function generate_fortress($this_prototype_data, $temp_battle_level, $temp_index_token, $temp_battle_token, $temp_robot_masters = array(), $temp_support_mechas = array(), $temp_player_info = array()){
    // Pull in global variables for this function
    global $mmrpg_index, $this_database;

    // Collect the battle index for this foress battle
    $temp_battle_index = rpg_battle::get_index_info($temp_index_token);
    $temp_robot_index = rpg_robot::get_index();

    //ksort($_SESSION['GAME']['values']['battle_index']);
    //exit('$session_values_battle_index = <pre>'.print_r($_SESSION['GAME']['values']['battle_index'], true).'</pre>');
    //unset($_SESSION['GAME']['values']['battle_index'][$temp_battle_token]);
    //die('$temp_battle_index = <pre>'.print_r($temp_battle_index, true).'</pre>');

    // Copy over any completion records from the old, index-based name
    if (!empty($_SESSION['GAME']['values']['battle_complete'][$this_prototype_data['this_player_token']][$temp_index_token])){
        $_SESSION['GAME']['values']['battle_complete'][$this_prototype_data['this_player_token']][$temp_battle_token] = $_SESSION['GAME']['values']['battle_complete'][$this_prototype_data['this_player_token']][$temp_index_token];
        unset($_SESSION['GAME']['values']['battle_complete'][$this_prototype_data['this_player_token']][$temp_index_token]);
    }

    // Copy over any completion records from the old, index-based name
    if (!empty($_SESSION['GAME']['values']['battle_failure'][$this_prototype_data['this_player_token']][$temp_index_token])){
        $_SESSION['GAME']['values']['battle_failure'][$this_prototype_data['this_player_token']][$temp_battle_token] = $_SESSION['GAME']['values']['battle_failure'][$this_prototype_data['this_player_token']][$temp_index_token];
        unset($_SESSION['GAME']['values']['battle_failure'][$this_prototype_data['this_player_token']][$temp_index_token]);
    }

    // Collect and define the rest of the details
    $temp_battle_omega = $temp_battle_index;
    $temp_battle_complete = rpg_prototype::battle_complete($this_prototype_data['this_player_token'], $temp_battle_token);
    $temp_battle_targets = !empty($temp_battle_omega['battle_target_player']['player_robots']) ? count($temp_battle_omega['battle_target_player']['player_robots']) : 0;
    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
    $temp_battle_omega['battle_token'] = $temp_battle_token;
    $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
    $temp_battle_omega['battle_level'] = $temp_battle_level; //$this_prototype_data['this_chapter_levels'][5];

    // If the battle is complete, remove the player from the description and increase the level
    if (!empty($temp_battle_complete)){
      $temp_base_level = $temp_battle_omega['battle_level'];
      $temp_battle_omega['battle_level'] += !empty($temp_battle_complete['battle_count']) ? $temp_battle_complete['battle_count'] : 0;
      if ($temp_battle_omega['battle_level'] > ($temp_base_level * 2)){ $temp_battle_omega['battle_level'] = $temp_base_level* 2; }
      $temp_battle_omega['battle_target_player']['player_token'] = 'player';
      $temp_battle_omega['battle_description'] = preg_replace('/^Defeat (Dr. (Wily|Light|Cossack)\'s)/i', 'Defeat', $temp_battle_omega['battle_description']);
    }

    // If robot masters were provided to the function, update them in the battle array
    if (!empty($temp_robot_masters)){
      $temp_battle_omega['battle_target_player']['player_robots'] = $temp_robot_masters;
    }

    // If support mechas were provided to the function, update them in the battle array
    if (!empty($temp_support_mechas)){
      $temp_battle_omega['battle_field_info']['field_mechas'] = $temp_support_mechas;
    }

    // Start all the point-based battle vars at zero
    $temp_battle_omega['battle_points'] = 0;
    $temp_battle_omega['battle_zenny'] = 0;
    $temp_battle_omega['battle_turns_limit'] = 0;
    $temp_battle_omega['battle_robots_limit'] = 0;

    // If the player info array was provided, merge into current
    if (!empty($temp_player_info)){  $temp_battle_omega['battle_target_player'] = array_merge($temp_battle_omega['battle_target_player'], $temp_player_info); }

    // Loop through and adjust the levels of robots
    if (!empty($temp_battle_omega['battle_target_player']['player_robots'])){
      $stat_boost_amount = !empty($temp_battle_complete['battle_count']) ? $temp_battle_complete['battle_count'] * $temp_battle_level : 0;
      if ($stat_boost_amount >= 1000){ $stat_boost_amount = 1000; }
      foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $robot){
        $index = $temp_robot_index[$robot['robot_token']];
        $robot['robot_level'] = $temp_battle_omega['battle_level'];
        if (!empty($stat_boost_amount)){
          $robot['values'] = array();
          $robot['values']['robot_rewards']  = array();
          $robot['values']['robot_rewards']['robot_energy'] = $stat_boost_amount;
          $robot['values']['robot_rewards']['robot_attack'] = $stat_boost_amount;
          $robot['values']['robot_rewards']['robot_defense'] = $stat_boost_amount;
          $robot['values']['robot_rewards']['robot_speed'] = $stat_boost_amount;
        }
        // Increment the battle's turn limit based on the class of target robot
        if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
        elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
        elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns_limit'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }

        // Increment the battle's point reward based on the class of target robot
        if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
        elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
        elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }

        // Increment the battle's zenny reward based on the class of target robot
        if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
        elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
        elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }

        // Increment the battle's robot limit based on the class of target robot
        if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
        elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
        elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_robots_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }
        $temp_battle_omega['battle_target_player']['player_robots'][$key] = $robot;
      }
    }
    //$temp_battle_omega['battle_field_info']['field_name'] = 'stat boost '.$stat_boost_amount.' '.$temp_battle_omega['battle_target_player']['player_robots'][$key]['values']['robot_rewards']['robot_energy'].' | ';

    // Fix any zero or invalid battle values
    if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
    else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
    if ($temp_battle_omega['battle_turns_limit'] < 1){ $temp_battle_omega['battle_turns_limit'] = 1; }
    else { $temp_battle_omega['battle_turns_limit'] = ceil($temp_battle_omega['battle_turns_limit']); }
    if ($temp_battle_omega['battle_robots_limit'] < 1){ $temp_battle_omega['battle_robots_limit'] = 1; }
    else { $temp_battle_omega['battle_robots_limit'] = ceil($temp_battle_omega['battle_robots_limit']); }

    // Return the generated omega battle data
    return $temp_battle_omega;

  }

}
?>