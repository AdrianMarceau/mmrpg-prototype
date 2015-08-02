<?
// -- DR. COSSACK PASSWORDS -- //

// Collect the temp battle flags
$temp_flags = !empty($_SESSION['GAME']['flags']) ? $_SESSION['GAME']['flags'] : array();

// If the UNLOCK RHYTHM password was created
if (!empty($temp_flags['drcossack_password_robotgetrhythmandblues'])){
  if (!mmrpg_prototype_robot_unlocked(false, 'rhythm')){
    // Unlock Roll as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
    $unlock_robot_info = mmrpg_robot::get_index_info('rhythm');
    $unlock_robot_info['robot_level'] = 1;
    $unlock_robot_info['robot_experience'] = mmrpg_prototype_calculate_experience(1) - 1;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true);
    header('Location: prototype.php');
    exit();
  }
}

// If the UNLOCK BUBBLE BOMB password was created
if (!empty($temp_flags['drcossack_password_abilitygetbubblebombsaway'])){
  if (!mmrpg_prototype_ability_unlocked('dr-cossack', false, 'bubble-bomb')){
    // Unlock Bubble Bomb as an equippable ability
    mmrpg_game_unlock_ability($mmrpg_index['players']['dr-cossack'], false, array('ability_token' => 'bubble-bomb'), true);
    header('Location: prototype.php');
    exit();
  }
}

// If the UNLOCK SHADOW BLADE password was created
if (!empty($temp_flags['drcossack_password_abilitygetcutterofdarkness'])){
  if (!mmrpg_prototype_ability_unlocked('dr-cossack', false, 'shadow-blade')){
    // Unlock Bubble Bomb as an equippable ability
    mmrpg_game_unlock_ability($mmrpg_index['players']['dr-cossack'], false, array('ability_token' => 'shadow-blade'), true);
    header('Location: prototype.php');
    exit();
  }
}

?>