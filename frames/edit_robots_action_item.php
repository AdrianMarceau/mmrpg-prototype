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

// Now that we have a new item attached, we should re-evaluate compatibile items
$robot_item_settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_items']) ? $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_items'] : array();
$player_item_rewards = !empty($_SESSION[$session_token]['values']['battle_items']) ? $_SESSION[$session_token]['values']['battle_items']: array('buster-shot' => array('item_token' => 'buster-shot'));
$allowed_item_ids = array();
if (!empty($player_item_rewards)){
    foreach ($player_item_rewards AS $item_token => $item_info){
        if (empty($item_info['item_token'])){ continue; }
        elseif ($item_info['item_token'] == '*'){ continue; }
        elseif ($item_info['item_token'] == 'item'){ continue; }
        elseif (!isset($mmrpg_database_items[$item_info['item_token']])){ continue; }
        elseif (!rpg_robot::has_item_compatibility($temp_robot_info['robot_token'], $item_token, $temp_item)){
            if (isset($robot_item_settings[$item_token])){ unset($robot_item_settings[$item_token]); }
            continue;
        }
        $item_info['item_id'] = $mmrpg_database_items[$item_info['item_token']]['item_id'];
        $allowed_item_ids[] = $item_info['item_id'];
    }
}
$allowed_item_ids = implode(',', $allowed_item_ids);
if (empty($robot_item_settings)){ $robot_item_settings['buster-shot'] = array('buster-shot' => array('item_token' => 'buster-shot')); }
$_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_items'] = $robot_item_settings;

// Regardless of what happened before, update this robot's item in the session and save
rpg_game::save_session($this_save_filepath);
exit('success|item-updated|'.$temp_item.'|'.$allowed_item_ids);
//exit('success|item-updated|'.$temp_item);

?>