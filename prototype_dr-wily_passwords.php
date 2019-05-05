<?

// -- DR. WILY PASSWORDS -- //

// Collect the temp battle flags
$temp_flags = !empty($_SESSION['GAME']['flags']) ? $_SESSION['GAME']['flags'] : array();

// If the UNLOCK DISCO password was created
if (!empty($temp_flags['drwily_password_robotgetpanicatthedisco'])){
    if (!mmrpg_prototype_robot_unlocked(false, 'disco')){
    // Unlock Roll as a playable character
        $unlock_player_info = $mmrpg_index['players']['dr-wily'];
        $unlock_robot_info = rpg_robot::get_index_info('disco');
        $unlock_robot_info['robot_level'] = 1;
        $unlock_robot_info['robot_experience'] = 999;
        mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK BUBBLE BOMB password was created
if (!empty($temp_flags['drwily_password_abilitygetbubblebombsaway'])){
    if (!mmrpg_prototype_ability_unlocked('dr-wily', false, 'bubble-bomb')){
        // Unlock Bubble Bomb as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-wily'], false, array('ability_token' => 'bubble-bomb'), true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK SHADOW BLADE password was created
if (!empty($temp_flags['drwily_password_abilitygetcutterofdarkness'])){
    if (!mmrpg_prototype_ability_unlocked('dr-wily', false, 'shadow-blade')){
        // Unlock Shadow Blade as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-wily'], false, array('ability_token' => 'shadow-blade'), true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK COPY SHOT password was created
if (!empty($temp_flags['drwily_password_abilitygetnowivegotyourpower'])){
    if (!mmrpg_prototype_ability_unlocked('dr-wily', false, 'copy-shot')){
        // Unlock Copy Shot as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-wily'], false, array('ability_token' => 'copy-shot'), true);
        header('Location: prototype.php');
        exit();
    }
}

// If the UNLOCK COPY SOUL password was created
if (!empty($temp_flags['drlight_password_abilitygetwithallmyheartandsoul'])){
    if (!mmrpg_prototype_ability_unlocked('dr-wily', false, 'copy-soul')){
        // Unlock Copy Shot as an equippable ability
        mmrpg_game_unlock_ability($mmrpg_index['players']['dr-wily'], false, array('ability_token' => 'copy-soul'), true);
        header('Location: prototype.php');
        exit();
    }
}

?>