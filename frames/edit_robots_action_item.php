<?
// Collect the ability variables from the request header, if they exist
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

// Now that we have a new item attached, we should re-evaluate compatibile abilities
$robot_ability_settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities']) ? $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] : array();
$player_ability_rewards = !empty($_SESSION[$session_token]['values']['battle_abilities']) ? $_SESSION[$session_token]['values']['battle_abilities']: array('buster-shot' => array('ability_token' => 'buster-shot'));
$allowed_ability_ids = array();
if (!empty($player_ability_rewards)){
  foreach ($player_ability_rewards AS $ability_token => $ability_info){
    if (empty($ability_info['ability_token'])){ continue; }
    elseif ($ability_info['ability_token'] == '*'){ continue; }
    elseif ($ability_info['ability_token'] == 'ability'){ continue; }
    elseif (!isset($mmrpg_database_abilities[$ability_info['ability_token']])){ continue; }
    elseif (!mmrpg_robot::has_ability_compatibility($temp_robot_info['robot_token'], $ability_token, $temp_item)){
      if (isset($robot_ability_settings[$ability_token])){ unset($robot_ability_settings[$ability_token]); }
      continue;
    }
    $ability_info['ability_id'] = $mmrpg_database_abilities[$ability_info['ability_token']]['ability_id'];
    $allowed_ability_ids[] = $ability_info['ability_id'];
  }
}
$allowed_ability_ids = implode(',', $allowed_ability_ids);
if (empty($robot_ability_settings)){ $robot_ability_settings['buster-shot'] = array('buster-shot' => array('ability_token' => 'buster-shot')); }
$_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $robot_ability_settings;

// Regardless of what happened before, update this robot's item in the session and save
mmrpg_save_game_session($this_save_filepath);
exit('success|item-updated|'.$temp_item.'|'.$allowed_ability_ids);
//exit('success|item-updated|'.$temp_item);

?>