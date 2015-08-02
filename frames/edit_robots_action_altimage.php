<?
// Collect the ability variables from the request header, if they exist
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
$temp_image = !empty($_REQUEST['image']) ? $_REQUEST['image'] : '';

// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_robot) || empty($temp_image)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the current robot favourites for this user
$temp_player_info = $allowed_edit_data[$temp_player];
$temp_robot_info = $allowed_edit_data[$temp_player]['player_robots'][$temp_robot];

// If player or robot info was not found, kill the script in error
if (empty($temp_player_info) || empty($temp_robot_info)){ die('error|request-notfound|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Regardless of what happened before, update this robot's image in the session and save
$temp_image_full = $temp_robot.($temp_image != 'base' ? '_'.$temp_image : '');
$_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_image'] = $temp_image_full;
mmrpg_save_game_session($this_save_filepath);
exit('success|image-updated|'.$temp_image_full);

?>