<?
// Collect the ability variables from the request header, if they exist
$temp_key = !empty($_REQUEST['key']) ? $_REQUEST['key'] : 0;
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
$temp_ability = !empty($_REQUEST['ability']) ? $_REQUEST['ability']: '';
// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the current settings for the requested robot
$temp_settings = mmrpg_prototype_robot_settings($temp_player, $temp_robot);
// Create a key-based array to hold the ability settings in and populate it
$temp_abilities = array();
foreach ($temp_settings['robot_abilities'] AS $temp_info){ $temp_abilities[] = $temp_info['ability_token']; }
// Crop the ability settings if they've somehow exceeded the eight limit
if (count($temp_abilities) > 8){ $temp_abilities = array_slice($temp_abilities, 0, 8, true); }

// If requested new ability was an empty string, remove the previous value
if (empty($temp_ability)){
  // If this was the last ability, do nothing with this request
  if (count($temp_abilities) <= 1){ die('success|remove-last|'.implode(',', $temp_abilities)); }
  // Unset the requested key in the array
  unset($temp_abilities[$temp_key]);
  // Create a new array to hold the full ability settings and populate
  $temp_abilities_new = array();
  foreach ($temp_abilities AS $temp_token){ $temp_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
  // Update the new ability settings in the session variable
  $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $temp_abilities_new;
  // Save, produce the success message with the new ability order
  mmrpg_save_game_session($this_save_filepath);
  exit('success|ability-removed|'.implode(',', $temp_abilities));
}
// Otherwise, if there was a new ability provided, update it in the array
elseif (!in_array($temp_ability, $temp_abilities)){
  // Update this position in the array with the new ability
  $temp_abilities[$temp_key] = $temp_ability;
  // Create a new array to hold the full ability settings and populate
  $temp_abilities_new = array();
  foreach ($temp_abilities AS $temp_token){ $temp_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
  // Update the new ability settings in the session variable
  $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $temp_abilities_new;
  // Save, produce the success message with the new ability order
  mmrpg_save_game_session($this_save_filepath);
  exit('success|ability-updated|'.implode(',', $temp_abilities));
}
// Otherwise, if ability is already equipped, swap positions in the array
elseif (in_array($temp_ability, $temp_abilities)){
  // Update this position in the array with the new ability
  $this_slot_key = $temp_key;
  $this_slot_value = $temp_abilities[$temp_key];
  $copy_slot_value = $temp_ability;
  $copy_slot_key = array_search($temp_ability, $temp_abilities);
  // Update this slot with new value
  $temp_abilities[$this_slot_key] = $copy_slot_value;
  // Update copy slot with new value
  $temp_abilities[$copy_slot_key] = $this_slot_value;
  // Create a new array to hold the full ability settings and populate
  $temp_abilities_new = array();
  foreach ($temp_abilities AS $temp_token){ $temp_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
  // Update the new ability settings in the session variable
  $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $temp_abilities_new;
  // Save, produce the success message with the new ability order
  mmrpg_save_game_session($this_save_filepath);
  exit('success|ability-updated|'.implode(',', $temp_abilities));
} else {
  // Produce an error show this ability has already been selected
  exit('error|ability-exists|'.implode(',', $temp_abilities));
}

?>