<?
// Collect the battle index for this foress battle
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_battle_index = mmrpg_battle::get_index_info($temp_index_token);
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
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
$temp_battle_omega['battle_turns'] = $temp_battle_targets * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
$temp_battle_omega['battle_points'] = $temp_battle_targets * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * $temp_battle_omega['battle_level'];

// If the battle is complete, remove the player from the description and increase the level
if (!empty($temp_battle_complete)){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
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

// Define the battle robot limit based on difficulty
$temp_battle_omega['battle_robot_limit'] = MMRPG_SETTINGS_BATTLEROBOTS_SELECT_MAX;

// Loop through and adjust the levels of robots
if (!empty($temp_battle_omega['battle_target_player']['player_robots'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $stat_boost_amount = !empty($temp_battle_complete['battle_count']) ? $temp_battle_complete['battle_count'] * $temp_battle_level : 0;
  if ($stat_boost_amount >= 1000){ $stat_boost_amount = 1000; }
  foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $info['robot_level'] = $temp_battle_omega['battle_level'];
    if (!empty($stat_boost_amount)){
      $info['values'] = array();
      $info['values']['robot_rewards']  = array();
      $info['values']['robot_rewards']['robot_energy'] = $stat_boost_amount;
      $info['values']['robot_rewards']['robot_attack'] = $stat_boost_amount;
      $info['values']['robot_rewards']['robot_defense'] = $stat_boost_amount;
      $info['values']['robot_rewards']['robot_speed'] = $stat_boost_amount;
    }
    $temp_class = $temp_robot_index[$info['robot_token']]['robot_class'];
    $temp_battle_omega['battle_target_player']['player_robots'][$key] = $info;
  }
}
//$temp_battle_omega['battle_field_base']['field_name'] = 'stat boost '.$stat_boost_amount.' '.$temp_battle_omega['battle_target_player']['player_robots'][$key]['values']['robot_rewards']['robot_energy'].' | ';

// Return the generated fortress battle
return $temp_battle_omega;
?>