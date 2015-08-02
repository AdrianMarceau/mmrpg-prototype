<?

// Define a function for parsing editor arrays
function parse_editor_indexes(
  &$mmrpg_database_players, &$mmrpg_database_robots, &$mmrpg_database_abilities,
  &$allowed_edit_players, &$allowed_edit_robots, &$allowed_edit_data
  ){
  global $session_token;
  $temp_player_array = !empty($_SESSION[$session_token]['values']['battle_rewards']) ? $_SESSION[$session_token]['values']['battle_rewards'] : $_SESSION[$session_token]['values']['battle_settings'];

  // Reset all editor variables and counters to their default values
  foreach ($temp_player_array AS $player_token => $player_info){

    if (empty($player_token) || !isset($mmrpg_database_players[$player_token])){ continue; }
    if (false || !isset($mmrpg_database_players[$player_token]['_parsed'])){
      $mmrpg_database_players[$player_token] = $mmrpg_database_players[$player_token];
    }
    $player_info = array_merge($mmrpg_database_players[$player_token], $player_info);

    if (empty($player_info['player_robots'])){ continue; }
    foreach ($player_info['player_robots'] AS $robot_token => $robot_info){

      if (empty($robot_token) || !isset($mmrpg_database_robots[$robot_token])){ continue; }
      if (!isset($mmrpg_database_robots[$robot_token]['_parsed'])){
        $mmrpg_database_robots[$robot_token] = mmrpg_robot::parse_index_info($mmrpg_database_robots[$robot_token]);
        $mmrpg_database_robots[$robot_token]['robot_index_abilities'] = $mmrpg_database_robots[$robot_token]['robot_abilities'];
      }
      $robot_info = array_merge($mmrpg_database_robots[$robot_token], $robot_info);

      if (empty($robot_info['robot_abilities'])){ continue; }
      foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){

        if (empty($ability_token) || !isset($mmrpg_database_abilities[$ability_token])){ continue; }
        if (!isset($mmrpg_database_abilities[$ability_token]['_parsed'])){
          $mmrpg_database_abilities[$ability_token] = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$ability_token]);
        }
        $ability_info = array_merge($mmrpg_database_abilities[$ability_token], $ability_info);

      }
    }
  }

}


// Define a function for updating editor info
function refresh_editor_arrays(
  &$mmrpg_database_players, &$mmrpg_database_robots, &$mmrpg_database_abilities,
  &$allowed_edit_players, &$allowed_edit_robots, &$allowed_edit_data,
  &$allowed_edit_data_count, &$allowed_edit_player_count, &$allowed_edit_robot_count
  ){
  global $session_token;
  $temp_player_array = array();
  if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){ $temp_player_array = array_merge($temp_player_array, $_SESSION[$session_token]['values']['battle_rewards']); }
  if (!empty($_SESSION[$session_token]['values']['battle_settings'])){ $temp_player_array = array_merge($temp_player_array, $_SESSION[$session_token]['values']['battle_settings']); }

  // Define the editor indexes and count variables
  $allowed_edit_players = array();
  $allowed_edit_robots = array();
  $allowed_edit_data = array();
  $allowed_edit_data_count = 0;
  $allowed_edit_player_count = 0;
  $allowed_edit_robot_count = 0;

  // Now to actually loop through and update the allowed players, robots, and abilities arrays
  foreach ($temp_player_array AS $player_token => $player_info){
    if (empty($player_token) || !isset($mmrpg_database_players[$player_token])){ continue; }

    $player_info = array_merge($mmrpg_database_players[$player_token], $player_info);
    $allowed_edit_players[] = $player_token;
    $allowed_edit_data[$player_token] = $player_info;

    foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
      if (empty($robot_token) || !isset($mmrpg_database_robots[$robot_token])){ continue; }

      $robot_info = array_merge($mmrpg_database_robots[$robot_token], $robot_info);
      $allowed_edit_robots[] = $robot_token;
      $allowed_edit_data[$player_token]['player_robots'][$robot_token] = $robot_info;

      foreach ($robot_info['robot_abilities'] AS $ability_token => $ability_info){
        if (empty($ability_token) || !isset($mmrpg_database_abilities[$ability_token])){ continue; }

        //$ability_info = array_merge($mmrpg_database_abilities[$ability_token], $ability_info);
        //$allowed_edit_data[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = $ability_info;
        $allowed_edit_data[$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token] = &$mmrpg_database_abilities[$ability_token];

      }
    }
  }

  //$allowed_edit_data = array_reverse($allowed_edit_data, true);
  $allowed_edit_player_count = !empty($allowed_edit_players) ? count($allowed_edit_players) : 0;
  $allowed_edit_robot_count = !empty($allowed_edit_robots) ? count($allowed_edit_robots) : 0;
  $allowed_edit_data_count = 0;
  foreach ($allowed_edit_data AS $pinfo){ $allowed_edit_data_count += !empty($pinfo['player_robots']) ? count($pinfo['player_robots']) : 0; }
  }

?>