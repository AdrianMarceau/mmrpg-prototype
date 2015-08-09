<?
// Define a function for generating the BONUS missions
function mmrpg_prototype_mission_bonus($this_prototype_data, $this_robot_count = 8, $this_robot_class = 'master'){
  // Pull in global variables for this function
  global $mmrpg_index, $DB;

  // Collect the robot index for calculation purposes
  $this_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
  // Populate the battle options with the starter battle option
  $temp_rand_num = $this_robot_count;
  $temp_battle_token = $this_prototype_data['phase_battle_token'].'-prototype-bonus-'.$this_robot_class;
  if ($this_robot_class == 'mecha'){
    $temp_battle_omega = mmrpg_battle::get_index_info('bonus-prototype-complete');
    $temp_battle_omega['battle_field_base']['field_name'] = 'Bonus Field';
  }
  elseif ($this_robot_class == 'master'){
    $temp_battle_omega = mmrpg_battle::get_index_info('bonus-prototype-complete-2');
    $temp_battle_omega['battle_field_base']['field_name'] = 'Bonus Field II';
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
  //if ($this_robot_class == 'mecha'){ $temp_battle_omega['battle_turns'] = MMRPG_SETTINGS_BATTLETURNS_PERMECHA * $this_robot_count; }
  //elseif ($this_robot_class == 'master'){ $temp_battle_omega['battle_turns'] = MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $this_robot_count; }
  //$temp_battle_omega['battle_points'] = ceil(($this_prototype_data['battles_complete'] > 1 ? 100 : 1000) * $temp_rand_num);
  //shuffle($temp_battle_omega['battle_target_player']['player_robots']);


  // Create the randomized field multupliers
  $temp_types = $mmrpg_index['types'];
  $temp_allow_special = array(); //, 'damage', 'recovery', 'experience'
  foreach ($temp_types AS $key => $temp_type){ if (!empty($temp_type['type_class']) && $temp_type['type_class'] == 'special' && !in_array($temp_type['type_token'], $temp_allow_special)){ unset($temp_types[$key]); } }
  //$temp_battle_omega['battle_field_base']['field_multipliers']['experience'] = round((mt_rand(200, 300) / 100), 1);
  //$temp_battle_omega['battle_field_base']['field_type'] = $temp_types[array_rand($temp_types)]['type_token'];
  //do { $temp_battle_omega['battle_field_base']['field_type2'] = $temp_types[array_rand($temp_types)]['type_token'];
  //} while($temp_battle_omega['battle_field_base']['field_type2'] == $temp_battle_omega['battle_field_base']['field_type']);
  $temp_battle_omega['battle_field_base']['field_multipliers'] = array();
  while (count($temp_battle_omega['battle_field_base']['field_multipliers']) < 6){
    $temp_type = $temp_types[array_rand($temp_types)];
    $temp_multiplier = 1;
    while ($temp_multiplier == 1){ $temp_multiplier = round((mt_rand(10, 990) / 100), 1); }
    $temp_battle_omega['battle_field_base']['field_multipliers'][$temp_type['type_token']] = $temp_multiplier;
    //if (count($temp_battle_omega['battle_field_base']['field_multipliers']) >= 6){ break; }
  }


  // Update the field type based on multipliers
  $temp_multipliers = $temp_battle_omega['battle_field_base']['field_multipliers'];
  asort($temp_multipliers);
  $temp_multipliers = array_keys($temp_multipliers);
  $temp_battle_omega['battle_field_base']['field_type'] = array_pop($temp_multipliers);
  $temp_battle_omega['battle_field_base']['field_type2'] = array_pop($temp_multipliers);

  // Collect the field types into a simple array
  $temp_field_types = array($temp_battle_omega['battle_field_base']['field_type'], $temp_battle_omega['battle_field_base']['field_type2']);

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
  $temp_player_rewards = mmrpg_prototype_player_rewards($this_prototype_data['this_player_token']);
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
  $temp_battle_omega['battle_turns'] = 0;
  $temp_battle_omega['battle_robot_limit'] = 0;

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
    $index = mmrpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
    if ($this_robot_class != 'mecha'){ $robot['robot_item'] = $possible_master_items[mt_rand(0, $possible_master_items_last_key)]; }
    else { $robot['robot_item'] = ''; }
    $robot['robot_abilities'] = mmrpg_prototype_generate_abilities($index, $robot['robot_level'], 8, $robot['robot_item']);


    // Increment the battle's turn limit based on the class of target robot
    if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
    elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
    elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }

    // Increment the battle's point reward based on the class of target robot
    if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
    elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
    elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }

    // Increment the battle's zenny reward based on the class of target robot
    if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
    elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
    elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }

    // Increment the battle's robot limit based on the class of target robot
    if ($index['robot_class'] == 'master'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
    elseif ($index['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
    elseif ($index['robot_class'] == 'boss'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }

    $temp_battle_omega['battle_target_player']['player_robots'][$key] = $robot;
  }

  // Fix any zero or invalid battle values
  if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
  else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
  if ($temp_battle_omega['battle_turns'] < 1){ $temp_battle_omega['battle_turns'] = 1; }
  else { $temp_battle_omega['battle_turns'] = ceil($temp_battle_omega['battle_turns']); }
  if ($temp_battle_omega['battle_robot_limit'] < 1){ $temp_battle_omega['battle_robot_limit'] = 1; }
  else { $temp_battle_omega['battle_robot_limit'] = ceil($temp_battle_omega['battle_robot_limit']); }

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
  $temp_battle_omega['battle_field_base']['field_music'] = $temp_music_name;

  // Add some random item drops to the starter battle
  $temp_battle_omega['battle_rewards']['items'] = array(
    array('chance' => 2, 'token' => 'item-energy-tank'),
    array('chance' => 2, 'token' => 'item-weapon-tank'),
    array('chance' => 1, 'token' => 'item-yashichi'),
    array('chance' => 1, 'token' => 'item-extra-life')
    );

  // Return the generated battle data
  return $temp_battle_omega;

}

?>