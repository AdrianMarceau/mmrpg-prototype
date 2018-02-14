<?

// -- DR. LIGHT PASSWORDS -- //

// Collect the temp battle flags
$temp_flags = !empty($_SESSION['GAME']['flags']) ? $_SESSION['GAME']['flags'] : array();

// If the UNLOCK ROLL password was created
if (!empty($temp_flags['drlight_password_robotgetletsrocknroll'])){
    if (!mmrpg_prototype_robot_unlocked(false, 'roll')){
        // Unlock Roll as a playable character
        $unlock_player_info = $mmrpg_index['players']['dr-light'];
        $unlock_robot_info = rpg_robot::get_index_info('roll');
        $unlock_robot_info['robot_level'] = 1;
        $unlock_robot_info['robot_experience'] = 999;
        mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK DEMO COMPLETE BONUS password was created
if (!empty($temp_flags['drlight_password_abilitygetdemocompletebonus'])){
    // Only process if the Mega Buster has not yet been unlocked
    if ($_SESSION['GAME']['values']['battle_rewards']['dr-light']['player_points'] <= 0){
        // Increase this player's zenny by 10,000
        $temp_bonus_zenny = 10000;
        $_SESSION['GAME']['counters']['battle_zenny'] += $temp_bonus_zenny;
        // Unlock the Copy Shot for use in battle early
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-light'], false, array('ability_token' => 'copy-shot'), true);
        // Reset and return to the main menu
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK BUBBLE BOMB password was created
if (!empty($temp_flags['drlight_password_abilitygetbubblebombsaway'])){
    if (!mmrpg_prototype_ability_unlocked('dr-light', false, 'bubble-bomb')){
        // Unlock Bubble Bomb as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-light'], false, array('ability_token' => 'bubble-bomb'), true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK SHADOW BLADE password was created
if (!empty($temp_flags['drlight_password_abilitygetcutterofdarkness'])){
    if (!mmrpg_prototype_ability_unlocked('dr-light', false, 'shadow-blade')){
        // Unlock Shadow Blade as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-light'], false, array('ability_token' => 'shadow-blade'), true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK COPY SHOT password was created
if (!empty($temp_flags['drlight_password_abilitygetnowivegotyourpower'])){
    if (!mmrpg_prototype_ability_unlocked('dr-light', false, 'copy-shot')){
        // Unlock Copy Shot as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-light'], false, array('ability_token' => 'copy-shot'), true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK MEGA SLIDE password was created
if (!empty($temp_flags['drlight_password_abilitygetslideintofirst'])){
    if (!mmrpg_prototype_ability_unlocked('dr-light', false, 'mega-slide')){
        // Unlock Copy Shot as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-light'], false, array('ability_token' => 'mega-slide'), true);
        header('Location: prototype.php');
        exit();
    }
}

?>