<?
// Define a reference to the game's session flag variable
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags'];

/*
 * DEMO MISSION SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // Collect the counters and flags for Dr. Light
  $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
  $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
  $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;
  //$ability_counter_light = $unlock_flag_light ? mmrpg_prototype_abilities_unlocked('dr-light') : 0;
  //$star_counter_light = $unlock_flag_light ? mmrpg_prototype_stars_unlocked('dr-light') : 0;
  //$heart_counter_light = $unlock_flag_light ? mmrpg_prototype_hearts_unlocked('dr-light') : 0;
  $battle_complete_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
  $battle_failure_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light') : 0;
  //$battle_complete_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light', false) : 0;
  //$battle_failure_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light', false) : 0;
  $prototype_complete_flag_light = $unlock_flag_light ? mmrpg_prototype_complete('dr-light') : false;
  if ($unlock_flag_light && !$prototype_complete_flag_light && $battle_complete_counter_light >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-light']['prototype_complete'] = $prototype_complete_flag_light = true; }
      
}
/*
 * NORMAL MISSION SELECT
 */
else {
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // Collect the counters and flags for Dr. Light
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
  $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
  $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;
  if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-light', $this_data_condition)){
    $ability_counter_light = $unlock_flag_light ? mmrpg_prototype_abilities_unlocked('dr-light') : 0;
    $star_counter_light = $unlock_flag_light ? mmrpg_prototype_stars_unlocked('dr-light') : 0;
    //$heart_counter_light = $unlock_flag_light ? mmrpg_prototype_hearts_unlocked('dr-light') : 0;
  }
  $battle_complete_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
  $battle_failure_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light') : 0;
  //$battle_complete_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light', false) : 0;
  //$battle_failure_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light', false) : 0;
  $prototype_complete_flag_light = $unlock_flag_light ? mmrpg_prototype_complete('dr-light') : false;
  if ($unlock_flag_light && !$prototype_complete_flag_light && $battle_complete_counter_light >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-light']['prototype_complete'] = $prototype_complete_flag_light = true; }

  // Collect the counters and flags for Dr. Wily
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $unlock_flag_wily = mmrpg_prototype_player_unlocked('dr-wily');
  $point_counter_wily = $unlock_flag_wily ? mmrpg_prototype_player_points('dr-wily') : 0;
  $robot_counter_wily = $unlock_flag_wily ? mmrpg_prototype_robots_unlocked('dr-wily') : 0;
  if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-wily', $this_data_condition)){
    $ability_counter_wily = $unlock_flag_wily ? mmrpg_prototype_abilities_unlocked('dr-wily') : 0;
    $star_counter_wily = $unlock_flag_wily ? mmrpg_prototype_stars_unlocked('dr-wily') : 0;
    //$heart_counter_wily = $unlock_flag_wily ? mmrpg_prototype_hearts_unlocked('dr-wily') : 0;
  }
  $battle_complete_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_complete('dr-wily') : 0;
  $battle_failure_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_failure('dr-wily') : 0;
  //$battle_complete_counter_wily_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-wily', false) : 0;
  //$battle_failure_counter_wily_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-wily', false) : 0;
  $prototype_complete_flag_wily = $unlock_flag_wily ? mmrpg_prototype_complete('dr-wily') : false;
  if ($unlock_flag_wily && !$prototype_complete_flag_wily && $battle_complete_counter_wily >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-wily']['prototype_complete'] = $prototype_complete_flag_wily = true; }
    
  // Collect the counters and flags for Dr. Cossack
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $unlock_flag_cossack = mmrpg_prototype_player_unlocked('dr-cossack');
  $point_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_player_points('dr-cossack') : 0;
  $robot_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_robots_unlocked('dr-cossack') : 0;
  if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-cossack', $this_data_condition)){
    $ability_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_abilities_unlocked('dr-cossack') : 0;
    $star_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_stars_unlocked('dr-cossack') : 0;
    //$heart_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_hearts_unlocked('dr-cossack') : 0;
  }
  $battle_complete_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_complete('dr-cossack') : 0;
  $battle_failure_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_failure('dr-cossack') : 0;
  //$battle_complete_counter_cossack_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-cossack', false) : 0;
  //$battle_failure_counter_cossack_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-cossack', false) : 0;
  $prototype_complete_flag_cossack = $unlock_flag_cossack ? mmrpg_prototype_complete('dr-cossack') : false;
  if ($unlock_flag_cossack && !$prototype_complete_flag_cossack && $battle_complete_counter_cossack >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-cossack']['prototype_complete'] = $prototype_complete_flag_cossack = true; }
  


  // Define which chapters should be unlocked for Dr. Light based on missions complete
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $chapters_unlocked_light = array();
  $chapters_unlocked_light['0'] = true;
  $chapters_unlocked_light['1'] = $battle_complete_counter_light >= 1 ? true : false;
  $chapters_unlocked_light['2'] = $battle_complete_counter_light >= 9 ? true : false;
  $chapters_unlocked_light['3'] = $battle_complete_counter_light >= 10 ? true : false;
  $chapters_unlocked_light['4a'] = $battle_complete_counter_light >= 14 ? true : false;
  $chapters_unlocked_light['4b'] = $battle_complete_counter_light >= 15 ? true : false;
  $chapters_unlocked_light['4c'] = $battle_complete_counter_light >= 16 ? true : false;
  $chapters_unlocked_light['5'] = $prototype_complete_flag_light || $battle_complete_counter_light >= 17 ? true : false;
  $chapters_unlocked_light['6'] = $prototype_complete_flag_light ? true : false;
  
  // Define which chapters should be unlocked for Dr. Wily based on missions complete
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $chapters_unlocked_wily = array();
  $chapters_unlocked_wily['0'] = true;
  $chapters_unlocked_wily['1'] = $battle_complete_counter_wily >= 1 ? true : false;
  $chapters_unlocked_wily['2'] = $battle_complete_counter_wily >= 9 ? true : false;
  $chapters_unlocked_wily['3'] = $battle_complete_counter_wily >= 10 ? true : false;
  $chapters_unlocked_wily['4a'] = $battle_complete_counter_wily >= 14 ? true : false;
  $chapters_unlocked_wily['4b'] = $battle_complete_counter_wily >= 15 ? true : false;
  $chapters_unlocked_wily['4c'] = $battle_complete_counter_wily >= 16 ? true : false;
  $chapters_unlocked_wily['5'] = $prototype_complete_flag_wily || $battle_complete_counter_wily >= 17 ? true : false;
  $chapters_unlocked_wily['6'] = $prototype_complete_flag_wily ? true : false;
  
  // Define which chapters should be unlocked for Dr. Cossack based on missions complete
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $chapters_unlocked_cossack = array();
  $chapters_unlocked_cossack['0'] = true;
  $chapters_unlocked_cossack['1'] = $battle_complete_counter_cossack >= 1 ? true : false;
  $chapters_unlocked_cossack['2'] = $battle_complete_counter_cossack >= 9 ? true : false;
  $chapters_unlocked_cossack['3'] = $battle_complete_counter_cossack >= 10 ? true : false;
  $chapters_unlocked_cossack['4a'] = $battle_complete_counter_cossack >= 14 ? true : false;
  $chapters_unlocked_cossack['4b'] = $battle_complete_counter_cossack >= 15 ? true : false;
  $chapters_unlocked_cossack['4c'] = $battle_complete_counter_cossack >= 16 ? true : false;
  $chapters_unlocked_cossack['5'] = $prototype_complete_flag_cossack || $battle_complete_counter_cossack >= 17 ? true : false;
  $chapters_unlocked_cossack['6'] = $prototype_complete_flag_cossack ? true : false;
  
  
  /*
  
  // Define which chapters should be unlocked for Dr. Light based on missions complete
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $chapters_unlocked_light = array();
  $chapters_unlocked_light['0'] = true;
  $chapters_unlocked_light['1'] = $battle_complete_counter_light >= 1 ? true : false;
  $chapters_unlocked_light['2'] = $battle_complete_counter_light >= 9 ? true : false;
  $chapters_unlocked_light['3'] = ($battle_complete_counter_light >= 10 && $battle_complete_counter_wily >= 10 && $battle_complete_counter_cossack >= 10) ? true : false;
  $chapters_unlocked_light['4a'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_light['4b'] = ($battle_complete_counter_light >= 15 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_light['4c'] = ($battle_complete_counter_light >= 16 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_light['5'] = $prototype_complete_flag_light || ($battle_complete_counter_light >= 17 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_light['6'] = $prototype_complete_flag_light ? true : false;
  
  // Define which chapters should be unlocked for Dr. Wily based on missions complete
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $chapters_unlocked_wily = array();
  $chapters_unlocked_wily['0'] = true;
  $chapters_unlocked_wily['1'] = $battle_complete_counter_wily >= 1 ? true : false;
  $chapters_unlocked_wily['2'] = $battle_complete_counter_wily >= 9 ? true : false;
  $chapters_unlocked_wily['3'] = ($battle_complete_counter_light >= 10 && $battle_complete_counter_wily >= 10 && $battle_complete_counter_cossack >= 10) ? true : false;
  $chapters_unlocked_wily['4a'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_wily['4b'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 15 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_wily['4c'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 16 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_wily['5'] = $prototype_complete_flag_wily || ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 17 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_wily['6'] = $prototype_complete_flag_wily ? true : false;
  
  // Define which chapters should be unlocked for Dr. Cossack based on missions complete
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $chapters_unlocked_cossack = array();
  $chapters_unlocked_cossack['0'] = true;
  $chapters_unlocked_cossack['1'] = $battle_complete_counter_cossack >= 1 ? true : false;
  $chapters_unlocked_cossack['2'] = $battle_complete_counter_cossack >= 9 ? true : false;
  $chapters_unlocked_cossack['3'] = ($battle_complete_counter_light >= 10 && $battle_complete_counter_wily >= 10 && $battle_complete_counter_cossack >= 10) ? true : false;
  $chapters_unlocked_cossack['4a'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 14) ? true : false;
  $chapters_unlocked_cossack['4b'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 15) ? true : false;
  $chapters_unlocked_cossack['4c'] = ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 16) ? true : false;
  $chapters_unlocked_cossack['5'] = $prototype_complete_flag_cossack || ($battle_complete_counter_light >= 14 && $battle_complete_counter_wily >= 14 && $battle_complete_counter_cossack >= 17) ? true : false;
  $chapters_unlocked_cossack['6'] = $prototype_complete_flag_cossack ? true : false;
  
  */

  // If the player has manually unlocked any Dr. Light chapters via password, update their flags
  if (!$chapters_unlocked_light['6']){
    if ($battle_complete_counter_light > 0
      && (!empty($temp_game_flags['drlight_password_playerbattlebonus20130324'])
        || !empty($temp_game_flags['drlight_password_chaptergetplayerbattles']))){
        $chapters_unlocked_light['6'] = true;
      }
  }
  // If the player has manually unlocked any Dr. Wily chapters via password, update their flags
  if (!$chapters_unlocked_wily['6']){
    if ($battle_complete_counter_wily > 0
      && (!empty($temp_game_flags['drwily_password_playerbattlebonus20130324'])
        || !empty($temp_game_flags['drwily_password_chaptergetplayerbattles']))){
        $chapters_unlocked_wily['6'] = true;
      }
  }
  // If the player has manually unlocked any Dr. Cossack chapters via password, update their flags
  if (!$chapters_unlocked_cossack['6']){
    if ($battle_complete_counter_cossack > 0
      && (!empty($temp_game_flags['drcossack_password_playerbattlebonus20130324'])
        || !empty($temp_game_flags['drcossack_password_chaptergetplayerbattles']))){
        $chapters_unlocked_cossack['6'] = true;
      }
  }
  
}

// Count the number of players unlocked
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$unlock_count_players = 0;
if (!empty($unlock_flag_light)){ $unlock_count_players++; }
if (!empty($unlock_flag_wily)){ $unlock_count_players++; }
if (!empty($unlock_flag_cossack)){ $unlock_count_players++; }

?>