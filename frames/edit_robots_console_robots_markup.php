<?

// Start the output buffer
ob_start();

// Predefine the player options markup
$player_options_markup = '';
foreach($allowed_edit_data AS $player_token => $player_info){
  $temp_player_battles = mmrpg_prototype_battles_complete($player_token);
  $temp_player_transfer = $temp_player_battles >= 1 ? true : false;
  $player_options_markup .= '<option value="'.$player_info['player_token'].'" data-label="'.$player_info['player_token'].'" title="'.$player_info['player_name'].'" '.(!$temp_player_transfer ? 'disabled="disabled"' : '').'>'.$player_info['player_name'].'</option>';
}

// Predefine the item options markup
$item_options_markup = '';
$item_options_markup .= '<option value="" data-label="No Item" title="No Item">- No Item -</option>';
$item_options_markup .= '<optgroup label="Single-Use Items">';
if (!empty($_SESSION[$session_token]['values']['battle_items'])){
  foreach($mmrpg_database_items AS $item_token => $item_info){
    if (preg_match('/^item-screw-/i', $item_token)){ continue; }
    elseif (preg_match('/^item-shard-/i', $item_token)){ continue; }
    elseif (preg_match('/^item-star-/i', $item_token)){ continue; }
    elseif (preg_match('/^item-score-/i', $item_token)){ continue; }
    $item_quantity = !empty($_SESSION[$session_token]['values']['battle_items'][$item_token]) ? $_SESSION[$session_token]['values']['battle_items'][$item_token] : 0;
    if ($item_quantity < 1){ continue; }
    if (preg_match('/^item-core-/i', $item_token)){
      $item_options_markup .= '</optgroup>';
      $item_options_markup .= '<optgroup label="Multi-Use Items">';
    }
    $item_label = $item_info['ability_name'];
    $item_title = $item_info['ability_name'];
    if (!empty($item_info['ability_type'])){ $item_title .= ' | '.ucfirst($item_info['ability_type']).' Type';  }
    else { $item_title .= ' | Neutral Type'; }
    if (!empty($item_info['ability_description'])){ $item_title .= ' || '.$item_info['ability_description'];  }
    $item_options_markup .= '<option value="'.$item_token.'" data-label="'.$item_label.'" title="'.$item_title.'">'.$item_label.' x '.$item_quantity.'</option>';
  }
}
$item_options_markup .= '</optgroup>';
/*
foreach($allowed_edit_data AS $player_token => $player_info){
  $temp_player_battles = mmrpg_prototype_battles_complete($player_token);
  $temp_player_transfer = $temp_player_battles >= 1 ? true : false;
  $item_options_markup .= '<option value="'.$player_info['player_token'].'" data-label="'.$player_info['player_token'].'" title="'.$player_info['player_name'].'" '.(!$temp_player_transfer ? 'disabled="disabled"' : '').'>'.$player_info['player_name'].'</option>';
}
*/

// Loop through the allowed edit data for all players
$key_counter = 0;

// Loop through and count each player's robot totals
$temp_robot_totals = array();
foreach($allowed_edit_data AS $player_token => $player_info){
  $temp_robot_totals[$player_token] = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : 0;
}

// Loop through the players in the ability edit data
foreach($allowed_edit_data AS $player_token => $player_info){

  // Collect the rewards for this player
  $player_rewards = mmrpg_prototype_player_rewards($player_token);

  // Check how many robots this player has and see if they should be able to transfer
  $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
  $counter_player_missions = mmrpg_prototype_battles_complete($player_info['player_token']);
  $allow_player_selector = $allowed_edit_player_count > 1 && $counter_player_missions > 0 ? true : false;

  // Loop through the player robots and display their edit boxes
  foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
    // Update the robot key to the current counter
    $robot_key = $key_counter;
    // Make a backup of the player selector
    $allow_player_selector_backup = $allow_player_selector;

    // Collect this player's ability rewards and add them to the dropdown
    if (!empty($_SESSION[$session_token]['values']['battle_abilities'])){ $player_ability_rewards = $_SESSION[$session_token]['values']['battle_abilities']; }
    elseif (!empty($player_rewards['player_abilities'])){ $player_ability_rewards = $player_rewards['player_abilities']; }
    else { $player_ability_rewards = array(); }
    //$player_ability_rewards = !empty($player_rewards['player_abilities']) ? $player_rewards['player_abilities'] : array();
    if (!empty($player_ability_rewards)){ asort($player_ability_rewards); }

    // Collect and print the editor markup for this robot
    if (
      !empty($_REQUEST['player']) && $_REQUEST['player'] == $player_info['player_token'] &&
      !empty($_REQUEST['robot']) && $_REQUEST['robot'] == $robot_info['robot_token']
      ){

      $temp_editor_markup = mmrpg_robot::print_editor_markup($player_info, $robot_info);
      echo $temp_editor_markup;

      // Collect the contents of the buffer
      $edit_console_markup = ob_get_clean();
      $edit_console_markup = preg_replace('/\s+/', ' ', trim($edit_console_markup));
      exit($edit_console_markup);

    }

    $key_counter++;

    // Return the backup of the player selector
    $allow_player_selector = $allow_player_selector_backup;

  }

}

// Collect the contents of the buffer
$edit_console_markup = ob_get_clean();
$edit_console_markup = preg_replace('/\s+/', ' ', trim($edit_console_markup));
exit($edit_console_markup);

?>