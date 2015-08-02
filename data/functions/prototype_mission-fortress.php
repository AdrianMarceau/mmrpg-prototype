<?
// Define a function for generating the FORTRESS missions
function mmrpg_prototype_mission_fortress($this_prototype_data, $temp_battle_level, $temp_index_token, $temp_battle_token, $temp_robot_masters = array(), $temp_support_mechas = array()){
  // Pull in global variables for this function
  global $mmrpg_index, $DB;

  // Collect the battle index for this foress battle
  $temp_battle_index = mmrpg_battle::get_index_info($temp_index_token);
  $temp_robot_index = mmrpg_robot::get_index();

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
  $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_token);
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
    $temp_battle_omega['battle_field_base']['field_mechas'] = $temp_support_mechas;
  }

  // Start all the point-based battle vars at zero
  $temp_battle_omega['battle_points'] = 0;
  $temp_battle_omega['battle_zenny'] = 0;
  $temp_battle_omega['battle_turns'] = 0;
  $temp_battle_omega['battle_robot_limit'] = 0;

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
  }
  //$temp_battle_omega['battle_field_base']['field_name'] = 'stat boost '.$stat_boost_amount.' '.$temp_battle_omega['battle_target_player']['player_robots'][$key]['values']['robot_rewards']['robot_energy'].' | ';

  // Fix any zero or invalid battle values
  if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
  else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
  if ($temp_battle_omega['battle_turns'] < 1){ $temp_battle_omega['battle_turns'] = 1; }
  else { $temp_battle_omega['battle_turns'] = ceil($temp_battle_omega['battle_turns']); }
  if ($temp_battle_omega['battle_robot_limit'] < 1){ $temp_battle_omega['battle_robot_limit'] = 1; }
  else { $temp_battle_omega['battle_robot_limit'] = ceil($temp_battle_omega['battle_robot_limit']); }

  // Return the generated omega battle data
  return $temp_battle_omega;

}
?>