<?
// Collect the ability variables from the request header, if they exist
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the current robot favourites for this user
$current_robot_favourites = !empty($_SESSION[$session_token]['values']['robot_favourites']) ? $_SESSION[$session_token]['values']['robot_favourites'] : array();
$temp_player_info = $allowed_edit_data[$temp_player];
$temp_robot_info = $allowed_edit_data[$temp_player]['player_robots'][$temp_robot];

// If this robot is not already a favourite, add it
if (!in_array($temp_robot, $current_robot_favourites)){
  $current_robot_favourites[] = $temp_robot;
  $_SESSION[$session_token]['values']['robot_favourites'] = $current_robot_favourites;
  mmrpg_save_game_session($this_save_filepath);
  exit('success|favourite-added|added');
}
// If this robot is not already a favourite, add it
elseif (in_array($temp_robot, $current_robot_favourites)){
  $temp_remove_key = array_search($temp_robot, $current_robot_favourites);
  unset($current_robot_favourites[$temp_remove_key]);
  $current_robot_favourites = array_values($current_robot_favourites);
  $_SESSION[$session_token]['values']['robot_favourites'] = $current_robot_favourites;
  mmrpg_save_game_session($this_save_filepath);
  exit('success|favourite-removed|removed');
}

exit('error|request-error|unknown');



?>