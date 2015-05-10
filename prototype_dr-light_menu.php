<?
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// -- DR. LIGHT MENU OPTIONS -- //

// Define the robot options and counter for this mode
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_prototype_data['this_player_token'] = 'dr-light';
$this_prototype_data['this_player_field'] = 'light-laboratory';
$this_prototype_data['this_support_robot'] = 'roll';
$this_prototype_data['this_chapter_levels'] = array(0 => 1, 1 => 2, 2 => 10, 3 => 31, 4 => 35, 5 => 40, 6 => 45);
$this_prototype_data['this_chapter_unlocked'] = $chapters_unlocked_light;
//$this_prototype_data['this_chapter_unlocked'] = array();
//$this_prototype_data['this_chapter_unlocked']['0'] = true;
//$this_prototype_data['this_chapter_unlocked']['1'] = $battle_complete_counter_light >= 1 ? true : false;
//$this_prototype_data['this_chapter_unlocked']['2'] = $battle_complete_counter_light >= 9 ? true : false;
//$this_prototype_data['this_chapter_unlocked']['3'] = ($battle_complete_counter_light >= 10 && $battle_complete_counter_wily >= 10 && $battle_complete_counter_cossack >= 10) ? true : false;
//$this_prototype_data['this_chapter_unlocked']['4a'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
//$this_prototype_data['this_chapter_unlocked']['4b'] = ($battle_complete_counter_light >= 15 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
//$this_prototype_data['this_chapter_unlocked']['4c'] = ($battle_complete_counter_light >= 16 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
//$this_prototype_data['this_chapter_unlocked']['5'] = ($battle_complete_counter_light >= 17 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
$this_prototype_data['target_player_token'] = 'dr-wily';
$this_prototype_data['battle_phase'] = 0;
$this_prototype_data['battle_options'] = array();
$this_prototype_data['missions_markup'] = '';
$this_prototype_data['battles_complete'] = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token']);
$this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
$this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];
$this_prototype_data['robots_unlocked'] = mmrpg_prototype_robots_unlocked($this_prototype_data['this_player_token']);
$this_prototype_data['points_unlocked'] = mmrpg_prototype_player_points($this_prototype_data['this_player_token']);
$this_prototype_data['prototype_complete'] = $prototype_complete_flag_light;

// Define the stage select music based on progression
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_music_token = $this_prototype_data['battles_complete'] >= 10 ? $this_prototype_data['target_player_token'] : $this_prototype_data['this_player_token'];
$this_prototype_data['missions_music'] = 'misc/stage-select-'.$this_music_token;

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Define the robot omega array for dynamic battle options
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_session_key = $this_prototype_data['this_player_token'].'_target-robot-omega_prototype';
$this_prototype_data['target_robot_omega'] = !empty($_SESSION['GAME']['values'][$temp_session_key]) ? $_SESSION['GAME']['values'][$temp_session_key] : array();
$this_prototype_data['this_player_fields'] = !empty($_SESSION['GAME']['values']['battle_settings'][$this_prototype_data['this_player_token']]['player_fields']) ? $_SESSION['GAME']['values']['battle_settings'][$this_prototype_data['this_player_token']]['player_fields'] : array();
// If the options have not already been defined, generate them
$temp_save = false;
if (empty($this_prototype_data['target_robot_omega'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Define the phase one omega factors, then shuffle
  $this_prototype_data['target_robot_omega'] = $this_omega_factors_one;
  shuffle($this_prototype_data['target_robot_omega']);
  $temp_save = true;
} elseif (count($this_prototype_data['target_robot_omega']) == 2){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Pull the omega tokens from the old array format
  $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1];
  $temp_save = true;
}
// If the player fields have not been defined
if (empty($this_prototype_data['this_player_fields'])){
  // Update the player fields array in the settings
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $temp_player_fields = array();
  foreach ($this_prototype_data['target_robot_omega'] AS $omega){ if (!empty($omega['field'])){ $temp_player_fields[$omega['field']] = array('field_token' => $omega['field']); } }
  $this_prototype_data['this_player_fields'] = $temp_player_fields;
  $_SESSION['GAME']['values']['battle_settings'][$this_prototype_data['this_player_token']]['player_fields'] = $temp_player_fields;
}
// Update the session with the omega options
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$_SESSION['GAME']['values'][$temp_session_key] = $this_prototype_data['target_robot_omega'];

/*
// Define the item omega array for dynamic item options
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_session_key = $this_prototype_data['this_player_token'].'_this-item-omega_prototype';
$this_prototype_data['this_item_omega'] = !empty($_SESSION['GAME']['values'][$temp_session_key]) ? $_SESSION['GAME']['values'][$temp_session_key] : array();
// If the options have not already been defined, generate them
$temp_save = false;
if (empty($this_prototype_data['this_item_omega'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  // Define the phase one omega item factors, collected if necessary
  if (!empty($_SESSION['GAME']['values']['battle_items'])){
    $this_prototype_data['this_item_omega'] = array_keys($this_prototype_data['this_item_omega']);
    $this_prototype_data['this_item_omega'] = array_slice($this_prototype_data['this_item_omega'], 0, 8);
  } else {
    $this_prototype_data['this_item_omega'] = array();
  }
  $temp_save = true;
}
// Update the session with the omega options
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$_SESSION['GAME']['values'][$temp_session_key] = $this_prototype_data['this_item_omega'];
*/

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// If possible, attempt to save the game to the session
if ($temp_save && !empty($this_save_filepath)){
  // Save the game session
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  mmrpg_save_game_session($this_save_filepath);
}

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

//die('<pre>'.print_r($this_prototype_data['target_robot_omega'], true).'</pre>');

// If there were save file corruptions, reset
if (!defined('MMRPG_SCRIPT_REQUEST') && empty($this_prototype_data['robots_unlocked'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  mmrpg_reset_game_session($this_save_filepath);
  header('Location: prototype.php');
  exit();
}

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Require the PASSWORDS file for this player
if (!defined('MMRPG_SCRIPT_REQUEST')){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  require_once('prototype_'.$this_prototype_data['this_player_token'].'_passwords.php');
}

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Require the MISSIONS file for this player
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
require('prototype_dr-xxx_missions.php');
//die('<pre>checkpoint 8 i guess? : ---</pre>');

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Define the robot options and counter for this mode
if (empty($_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_robot_options'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this_prototype_data['robot_options'] = !empty($mmrpg_index['players'][$this_prototype_data['this_player_token']]['player_robots']) ? $mmrpg_index['players'][$this_prototype_data['this_player_token']]['player_robots'] : array();
  foreach ($this_prototype_data['robot_options'] AS $key => $info){
    if (!mmrpg_prototype_robot_unlocked($this_prototype_data['this_player_token'], $info['robot_token'])){
      unset($this_prototype_data['robot_options'][$key]);
    } else {
      $temp_settings = mmrpg_prototype_robot_settings($this_prototype_data['this_player_token'], $info['robot_token']);
      //die(print_r($temp_settings, true));
      $this_prototype_data['robot_options'][$key]['original_player'] = !empty($temp_settings['original_player']) ? $temp_settings['original_player'] : $this_prototype_data['this_player_token'];
      $this_prototype_data['robot_options'][$key]['robot_abilities'] = !empty($temp_settings['robot_abilities']) ? $temp_settings['robot_abilities'] : array();
    }
  }
  $this_prototype_data['robot_options'] = array_values($this_prototype_data['robot_options']);
  usort($this_prototype_data['robot_options'], 'mmrpg_prototype_sort_robots_position');
  $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_robot_options'] = $this_prototype_data['robot_options'];
} else {
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this_prototype_data['robot_options'] = $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_robot_options'];
}

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Generate the markup for this player's robot select screen
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_prototype_data['robots_markup'] = mmrpg_prototype_robot_select_markup($this_prototype_data);

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Generate the markup for any leftover player missions
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_prototype_data['missions_markup'] .= mmrpg_prototype_options_markup($this_prototype_data['battle_options'], $this_prototype_data['this_player_token']);

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Add all these options to the global prototype data variable
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$prototype_data[$this_prototype_data['this_player_token']] = $this_prototype_data;
unset($this_prototype_data);

// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
//die('OMG THIS IS GAY LIGHT '.time());
?>