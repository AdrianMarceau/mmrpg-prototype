<?
// -- DR. COSSACK MENU OPTIONS -- //

// Define the robot options and counter for Dr. Cossack mode
$this_prototype_data['this_player_token'] = 'dr-cossack';
$this_prototype_data['this_player_field'] = 'cossack-citadel';
$this_prototype_data['this_support_robot'] = 'rhythm';
$this_prototype_data['this_chapter_levels'] = array(0 => 21, 1 => 22, 2 => 30, 3 => 41, 4 => 45, 5 => 50, 6 => 55);
$this_prototype_data['this_chapter_unlocked'] = $chapters_unlocked_cossack;
$this_prototype_data['target_player_token'] = 'dr-light';
$this_prototype_data['battle_phase'] = 0;
$this_prototype_data['battle_options'] = array();
$this_prototype_data['missions_markup'] = '';
$this_prototype_data['battles_complete'] = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token']);
$this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
$this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];
$this_prototype_data['robots_unlocked'] = mmrpg_prototype_robots_unlocked($this_prototype_data['this_player_token']);
$this_prototype_data['points_unlocked'] = mmrpg_prototype_player_points($this_prototype_data['this_player_token']);
$this_prototype_data['prototype_complete'] = $prototype_complete_flag_cossack;

// Define the stage select music based on progression
$this_music_token = $this_prototype_data['battles_complete'] >= 10 ? $this_prototype_data['target_player_token'] : $this_prototype_data['this_player_token'];
$this_prototype_data['missions_music'] = 'misc/stage-select-'.$this_music_token;

// Define the robot omega array for dynamic battle options
$temp_session_key = $this_prototype_data['this_player_token'].'_target-robot-omega_prototype';
$this_prototype_data['target_robot_omega'] = !empty($_SESSION['GAME']['values'][$temp_session_key]) ? $_SESSION['GAME']['values'][$temp_session_key] : array();
$this_prototype_data['this_player_fields'] = !empty($_SESSION['GAME']['values']['battle_settings'][$this_prototype_data['this_player_token']]['player_fields']) ? $_SESSION['GAME']['values']['battle_settings'][$this_prototype_data['this_player_token']]['player_fields'] : array();
// If the options have not already been defined, generate them
$temp_save = false;
if (empty($this_prototype_data['target_robot_omega'])){
  // Define the phase one omega factors, then shuffle
  $this_prototype_data['target_robot_omega'] = $this_omega_factors_three;
  shuffle($this_prototype_data['target_robot_omega']);
  $temp_save = true;
} elseif (count($this_prototype_data['target_robot_omega']) == 2){
  // Pull the omega tokens from the old array format
  $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1];
  $temp_save = true;
}
// If the player fields have not been defined
if (empty($this_prototype_data['this_player_fields'])){
  // Update the player fields array in the settings
  $temp_player_fields = array();
  foreach ($this_prototype_data['target_robot_omega'] AS $omega){ if (!empty($omega['field'])){ $temp_player_fields[$omega['field']] = array('field_token' => $omega['field']); } }
  $this_prototype_data['this_player_fields'] = $temp_player_fields;
  $_SESSION['GAME']['values']['battle_settings'][$this_prototype_data['this_player_token']]['player_fields'] = $temp_player_fields;
}
// Update the session with the omega options
$_SESSION['GAME']['values'][$temp_session_key] = $this_prototype_data['target_robot_omega'];

// If possible, attempt to save the game to the session
if ($temp_save && !empty($this_save_filepath)){
  // Save the game session
  mmrpg_save_game_session($this_save_filepath);
}

// If there were save file corruptions, reset
if (!defined('MMRPG_SCRIPT_REQUEST') && empty($this_prototype_data['robots_unlocked'])){
  mmrpg_reset_game_session($this_save_filepath);
  header('Location: prototype.php');
  exit();
}

// Require the PASSWORDS file for this player
if (!defined('MMRPG_SCRIPT_REQUEST')){
  require('prototype_dr-xxx_passwords.php');
}

// Require the MISSIONS file for this player
require('prototype_dr-xxx_missions.php');

// Define the robot options and counter for this mode
if (empty($_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_robot_options'])){
  $this_prototype_data['robot_options'] = !empty($mmrpg_index['players'][$this_prototype_data['this_player_token']]['player_robots']) ? $mmrpg_index['players'][$this_prototype_data['this_player_token']]['player_robots'] : array();
  foreach ($this_prototype_data['robot_options'] AS $key => $info){
    if (!mmrpg_prototype_robot_unlocked($this_prototype_data['this_player_token'], $info['robot_token'])){
      unset($this_prototype_data['robot_options'][$key]);
    } else {
      $temp_settings = mmrpg_prototype_robot_settings($this_prototype_data['this_player_token'], $info['robot_token']);
      $this_prototype_data['robot_options'][$key]['original_player'] = !empty($temp_settings['original_player']) ? $temp_settings['original_player'] : $this_prototype_data['this_player_token'];
      $this_prototype_data['robot_options'][$key]['robot_abilities'] = !empty($temp_settings['robot_abilities']) ? $temp_settings['robot_abilities'] : array();
      $this_prototype_data['robot_options'][$key]['robot_item'] = !empty($temp_settings['robot_item']) ? $temp_settings['robot_item'] : '';
    }
  }
  $this_prototype_data['robot_options'] = array_values($this_prototype_data['robot_options']);
  usort($this_prototype_data['robot_options'], 'mmrpg_prototype_sort_robots_position');
  $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_robot_options'] = $this_prototype_data['robot_options'];
} else {
  $this_prototype_data['robot_options'] = $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_robot_options'];
}

// Generate the markup for this player's robot select screen
$this_prototype_data['robots_markup'] = mmrpg_prototype_robot_select_markup($this_prototype_data);

// Generate the markup for any leftover player missions
$this_prototype_data['missions_markup'] .= mmrpg_prototype_options_markup($this_prototype_data['battle_options'], $this_prototype_data['this_player_token']);

// Add all these options to the global prototype data variable
$prototype_data[$this_prototype_data['this_player_token']] = $this_prototype_data;
unset($this_prototype_data);

?>