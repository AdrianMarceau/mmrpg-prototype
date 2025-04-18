<?php

// ROBOT ACTIONS : CHANGE ALTIMAGE

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

// Collect the newly regenerated unlocked robots index
$this_unlocked_robots_index = mmrpg_prototype_robots_unlocked_index_json();
$this_unlocked_robot_info = rpg_robot::get_index_info($temp_robot);
if (!empty($this_unlocked_robots_index[$temp_robot])){
    $this_unlocked_robot_data = $this_unlocked_robots_index[$temp_robot];
} else {
    $this_unlocked_robot_data = array(
        'token' => $temp_robot,
        'originalPlayer' => $temp_player,
        'currentPlayer' => $temp_player,
        'image' => $this_unlocked_robot_info['robot_image'],
        'imageSize' => $this_unlocked_robot_info['robot_image_size'],
        'energyBase' => $this_unlocked_robot_info['robot_energy'],
        'weaponsBase' => $this_unlocked_robot_info['robot_weapons'],
        'attackBase' => $this_unlocked_robot_info['robot_attack'],
        'defenseBase' => $this_unlocked_robot_info['robot_defense'],
        'speedBase' => $this_unlocked_robot_info['robot_speed']
        );
}

// Regardless of what happened before, update this robot's image in the session and save
$temp_image_full = $temp_robot.($temp_image != 'base' ? '_'.$temp_image : '');
$_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_image'] = $temp_image_full;
rpg_game::save_session();
exit('success|image-updated|'.$temp_image_full.PHP_EOL.json_encode($this_unlocked_robot_data));

?>