<?

// ROBOT ACTIONS : CHANGE ITEM

// Collect the item variables from the request header, if they exist
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
$temp_item = !empty($_REQUEST['item']) ? $_REQUEST['item'] : '';

// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the current robot favourites for this user
$temp_player_info = $allowed_edit_data[$temp_player];
$temp_robot_info = $allowed_edit_data[$temp_player]['player_robots'][$temp_robot];

// If player or robot info was not found, kill the script in error
if (empty($temp_player_info) || empty($temp_robot_info)){ die('error|request-notfound|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// If the robot is already holding an item, remove previous and add back to inventory
$temp_item_current = !empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_item']) ? $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_item'] : '';
if (!empty($temp_item_current)){
    $_SESSION[$session_token]['values']['battle_items'][$temp_item_current] += 1;
    $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_item'] = '';
}

// If the new hold item was not empty, remove from inventory and attach to robot
if (!empty($temp_item)){
    $_SESSION[$session_token]['values']['battle_items'][$temp_item] -= 1;
    $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_item'] = $temp_item;
}

// Regardless of what happened before, update this robot's item in the session and save
rpg_game::save_session($this_save_filepath);
exit('success|item-updated|'.$temp_item);
//exit('success|item-updated|'.$temp_item);

?>