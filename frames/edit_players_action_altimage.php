<?php

// PLAYER ACTIONS : CHANGE ALTIMAGE

// Collect the ability variables from the request header, if they exist
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_image = !empty($_REQUEST['image']) ? $_REQUEST['image'] : '';

// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_image)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the current player favourites for this user
$temp_player_info = $allowed_edit_data[$temp_player];

// If player or player info was not found, kill the script in error
if (empty($temp_player_info)){ die('error|request-notfound|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the newly regenerated unlocked players index
$this_unlocked_players_index = mmrpg_prototype_players_unlocked_index_json();
$this_unlocked_player_info = rpg_player::get_index_info($temp_player);
if (!empty($this_unlocked_players_index[$temp_player])){
    $this_unlocked_player_data = $this_unlocked_players_index[$temp_player];
} else {
    $this_unlocked_player_data = array(
        'token' => $temp_player,
        'originalPlayer' => $temp_player,
        'currentPlayer' => $temp_player,
        'image' => $this_unlocked_player_info['player_image'],
        'imageSize' => $this_unlocked_player_info['player_image_size'],
        'energyBase' => $this_unlocked_player_info['player_energy'],
        'weaponsBase' => $this_unlocked_player_info['player_weapons'],
        'attackBase' => $this_unlocked_player_info['player_attack'],
        'defenseBase' => $this_unlocked_player_info['player_defense'],
        'speedBase' => $this_unlocked_player_info['player_speed']
        );
}

// Regardless of what happened before, update this player's image in the session and save
$temp_image_full = $temp_player.($temp_image != 'base' ? '_'.$temp_image : '');
$_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_image'] = $temp_image_full;
rpg_game::save_session();
exit('success|image-updated|'.$temp_image_full.PHP_EOL.json_encode($this_unlocked_player_data));

?>