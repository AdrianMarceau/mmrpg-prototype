<?
// Collect data on this robot and the rescue robot
$this_robot_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
$this_robot_data = rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
$this_robot_name = $this_robot_data['robot_name'];
// Populate the battle options with the starter battle option
$temp_target_count = 1;
$temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
$temp_battle_omega = array();
$temp_battle_omega['battle_field_base']['field_id'] = 100;
$temp_battle_omega['battle_field_base']['field_token'] = 'intro-field';
$temp_battle_omega['flags']['starter_battle'] = true;
$temp_battle_omega['battle_token'] = $temp_battle_token;
$temp_battle_omega['battle_size'] = '1x4';
$temp_battle_omega_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
if (!empty($temp_battle_omega_complete['battle_count'])){ $temp_target_count = 1 + $temp_battle_omega_complete['battle_count']; }
if ($temp_target_count > 8 ){ $temp_target_count = 8; }
$temp_battle_omega['battle_level'] = $this_start_level;
$temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
$temp_battle_omega['battle_name'] = 'Chapter One Intro Battle';
//$temp_battle_omega['battle_name'] = $this_robot_name.($temp_target_count > 1 ? 's' : '');
//$temp_battle_omega['battle_name'] = $this_robot_name.($temp_target_count > 1 ? 's' : '').' Battle';
$temp_battle_omega['battle_turns'] = (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $temp_target_count);
$temp_battle_omega['battle_points'] = ceil(($this_prototype_data['battles_complete'] > 1 ? 100 : 1000) * $temp_target_count);
$temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);
$temp_battle_omega['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
$temp_battle_omega['battle_target_player']['player_token'] = 'player';
$temp_battle_omega['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);
$temp_mook_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];
$temp_battle_omega['battle_target_player']['player_robots'] = array();
$temp_name_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
$temp_mook_tokens = array();
/// Loop through and add other robots to the battle
for ($i = 0; $i < $temp_target_count; $i++){
  $temp_clone_robot = $temp_mook_robot;
  $temp_clone_robot['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $i;
  $temp_clone_robot['robot_level'] = $this_start_level;
  $temp_clone_robot['robot_token'] = $this_robot_token;
  $temp_robot_name = $this_robot_name;
  $temp_robot_name_token = $temp_clone_robot['robot_name_token'] = str_replace(' ', '-', strtolower($temp_robot_name));
  if (!isset($temp_mook_tokens[$temp_robot_name_token])){ $temp_mook_tokens[$temp_robot_name_token] = 0; }
  else { $temp_mook_tokens[$temp_robot_name_token]++; }
  if ($temp_target_count > 1){ $temp_clone_robot['robot_name'] = $temp_robot_name.' '.$temp_name_index[$temp_mook_tokens[$temp_robot_name_token]]; }
  else { $temp_clone_robot['robot_name'] = $temp_robot_name; }
  $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_clone_robot;
}
// Remove any uncessesary A's from the robots' names
foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
  if (!isset($info['robot_name_token'])){ continue; }
  if (isset($temp_mook_tokens[$info['robot_name_token']]) && $temp_mook_tokens[$info['robot_name_token']] == 0){
    $temp_battle_omega['battle_target_player']['player_robots'][$key]['robot_name'] = str_replace(' A', '', $info['robot_name']);
  }
}
// Add the player's lab to the background field
$temp_doctor_token = str_replace('dr-', '', $this_prototype_data['this_player_token']);
$temp_battle_omega['battle_field_base']['field_foreground_attachments'] = array();
$temp_battle_omega['battle_field_base']['field_foreground_attachments']['object_intro-field-'.$temp_doctor_token] = array('class' => 'object', 'size' => 160, 'offset_x' => 12, 'offset_y' => 121, 'offset_z' => 1, 'object_token' => 'intro-field-'.$temp_doctor_token, 'object_frame' => array(0), 'object_direction' => 'right');

// Otherwise if the rescue has not yet been unlocked as a playable character
if (!mmrpg_prototype_robot_unlocked(false, $this_rescue_token)){
  // Add the rescue to the background with animation
  $temp_battle_omega['battle_field_base']['field_foreground_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => 40, 'offset_x' => 91, 'offset_y' => 118, 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => array(8,0,8,0,0), 'robot_direction' => 'right');
}
// Allow unlocking of the mecha support ability if the player has reached max targets
if ($temp_target_count >= 8){
  // Add the Mecha Support ability as an unlockable move if not already unlocked
  $temp_battle_omega['battle_rewards']['abilities'] = array();
  if (!mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'mecha-support')){
    // Add the Met as a reward for the battle
    $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'mecha-support');
    // Update the description text for the battle
    $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' and download '.($temp_target_count > 1 ? 'their' : 'its').' data! &#10023; ';
  } elseif (!mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'field-support')){
    // Add the Met as a reward for the battle
    $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'field-support');
    // Update the description text for the battle
    $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' and download '.($temp_target_count > 1 ? 'their' : 'its').' data! &#10022; ';
  } else {
    // Update the description text for the battle
    $temp_battle_omega['battle_description'] = 'Defeat the enemy robot'.($temp_target_count > 1 ? 's' : '').' and download '.($temp_target_count > 1 ? 'their' : 'its').' data!';
  }
}
// Otherwise, if the player has already unlocked Roll
else {
  // Update the description text for the battle
  $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's that are' : ' that\'s').' attacking the lab!';
}
// Add some random item drops to the starter battle
if ($temp_target_count > 1){
  $temp_battle_omega['battle_rewards']['items'] = array(
    array('chance' => 1, 'token' => 'extra-life')
    );
} else {
  $temp_battle_omega['battle_rewards']['items'] = array(
    // Nothing special the first time around
    );
}
?>