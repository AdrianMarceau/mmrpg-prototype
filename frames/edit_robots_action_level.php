<?
// Collect the ability variables from the request header, if they exist
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Ensure this robot exists in the current game session
if (!empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot])
  && !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot])){

  // Count the number of robots each player has on their team before doing anything
  $temp_player_robot_count = !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots']) : 0;

  // Collect the current robot settings and rewards from the game session
  $temp_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot];
  $temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot];

  // Update this robot's level and reset it back to 1
  $temp_backup_level = $temp_robot_rewards['robot_level'];
  $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot]['robot_level'] = 1;
  if ($temp_backup_level >= 100){ $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot]['robot_experience'] = 0; }

  // Save, produce the success message with the new ability order
  mmrpg_save_game_session($this_save_filepath);
  $key_counter = 0;
  $player_counter = 1;
  $temp_robot_totals = array();
  $player_options_markup = '';
  foreach($allowed_edit_data AS $ptoken => $pinfo){
    $temp_robot_totals[$ptoken] = !empty($pinfo['player_robots']) ? count($pinfo['player_robots']) : 0;
    $temp_player_battles = mmrpg_prototype_battles_complete($ptoken);
    $temp_player_transfer = $temp_player_battles >= 1 ? true : false;
    $player_options_markup .= '<option value="'.$pinfo['player_token'].'" data-label="'.$pinfo['player_token'].'" title="'.$pinfo['player_name'].'" '.(!$temp_player_transfer ? 'disabled="disabled"' : '').'>'.$pinfo['player_name'].'</option>';
    $player_counter++;
  }
  foreach($allowed_edit_data AS $temp_player_token => $temp_player_info){
    if ($temp_player_token == $temp_player){

      $debug_robot_tokens = array();
      foreach ($temp_player_info['player_robots'] AS $rtoken => $rinfo){ $debug_robot_tokens[] = $rtoken; }

      $player_rewards = mmrpg_prototype_player_rewards($temp_player_token);
      $player_ability_rewards = !empty($player_rewards['player_abilities']) ? $player_rewards['player_abilities'] : array();
      if (!empty($player_ability_rewards)){ asort($player_ability_rewards); }

      //exit('success|player-swapped|<pre style="text-align: left; width: 300px;">'.implode(',', $debug_robot_tokens).'</pre>');
      $temp_robot_info = $temp_player_info['player_robots'][$temp_robot];
      $temp_robot_info['robot_settings'] = $temp_robot_settings;
      $temp_robot_info['robot_rewards'] = $temp_robot_rewards;
      $first_robot_token = $temp_robot_info['robot_token'];
      exit('success|level-reset|'.mmrpg_robot::print_editor_markup($temp_player_info, $temp_robot_info));
    }
  }

}
// Otherwise, produce an error
else {

  // Produce the error message
  exit('error|robot-undefined|false');

}



?>