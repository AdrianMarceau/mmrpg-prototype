<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags'])){ $_SESSION[$session_token]['flags'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags'];

/*
 * DEMO MISSION SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){
  // Collect the counters and flags for Dr. Light
  $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
  $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
  $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;
  //$ability_counter_light = $unlock_flag_light ? mmrpg_prototype_abilities_unlocked('dr-light') : 0;
  //$star_counter_light = $unlock_flag_light ? mmrpg_prototype_stars_unlocked('dr-light') : 0;
  //$core_counter_light = $unlock_flag_light ? mmrpg_prototype_cores_unlocked('dr-light') : 0;
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
  // Collect the counters and flags for Dr. Light
  $unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
  $point_counter_light = $unlock_flag_light ? mmrpg_prototype_player_points('dr-light') : 0;
  $robot_counter_light = $unlock_flag_light ? mmrpg_prototype_robots_unlocked('dr-light') : 0;
  if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-light', $this_data_condition)){
    $ability_counter_light = $unlock_flag_light ? mmrpg_prototype_abilities_unlocked('dr-light') : 0;
    $star_counter_light = $unlock_flag_light ? mmrpg_prototype_stars_unlocked('dr-light') : 0;
    $core_counter_light = $unlock_flag_light ? mmrpg_prototype_cores_unlocked('dr-light') : 0;
  }
  $battle_complete_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
  $battle_failure_counter_light = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light') : 0;
  //$battle_complete_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light', false) : 0;
  //$battle_failure_counter_light_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-light', false) : 0;
  $prototype_complete_flag_light = $unlock_flag_light ? mmrpg_prototype_complete('dr-light') : false;
  if ($unlock_flag_light && !$prototype_complete_flag_light && $battle_complete_counter_light >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-light']['prototype_complete'] = $prototype_complete_flag_light = true; }

  // Collect the counters and flags for Dr. Wily
  $unlock_flag_wily = mmrpg_prototype_player_unlocked('dr-wily');
  $point_counter_wily = $unlock_flag_wily ? mmrpg_prototype_player_points('dr-wily') : 0;
  $robot_counter_wily = $unlock_flag_wily ? mmrpg_prototype_robots_unlocked('dr-wily') : 0;
  if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-wily', $this_data_condition)){
    $ability_counter_wily = $unlock_flag_wily ? mmrpg_prototype_abilities_unlocked('dr-wily') : 0;
    $star_counter_wily = $unlock_flag_wily ? mmrpg_prototype_stars_unlocked('dr-wily') : 0;
    $core_counter_wily = $unlock_flag_wily ? mmrpg_prototype_cores_unlocked('dr-wily') : 0;
  }
  $battle_complete_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_complete('dr-wily') : 0;
  $battle_failure_counter_wily = $unlock_flag_wily ? mmrpg_prototype_battles_failure('dr-wily') : 0;
  //$battle_complete_counter_wily_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-wily', false) : 0;
  //$battle_failure_counter_wily_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-wily', false) : 0;
  $prototype_complete_flag_wily = $unlock_flag_wily ? mmrpg_prototype_complete('dr-wily') : false;
  if ($unlock_flag_wily && !$prototype_complete_flag_wily && $battle_complete_counter_wily >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-wily']['prototype_complete'] = $prototype_complete_flag_wily = true; }

  // Collect the counters and flags for Dr. Cossack
  $unlock_flag_cossack = mmrpg_prototype_player_unlocked('dr-cossack');
  $point_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_player_points('dr-cossack') : 0;
  $robot_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_robots_unlocked('dr-cossack') : 0;
  if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-cossack', $this_data_condition)){
    $ability_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_abilities_unlocked('dr-cossack') : 0;
    $star_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_stars_unlocked('dr-cossack') : 0;
    $core_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_cores_unlocked('dr-cossack') : 0;
  }
  $battle_complete_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_complete('dr-cossack') : 0;
  $battle_failure_counter_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_failure('dr-cossack') : 0;
  //$battle_complete_counter_cossack_total = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-cossack', false) : 0;
  //$battle_failure_counter_cossack_total = $unlock_flag_light ? mmrpg_prototype_battles_failure('dr-cossack', false) : 0;
  $prototype_complete_flag_cossack = $unlock_flag_cossack ? mmrpg_prototype_complete('dr-cossack') : false;
  if ($unlock_flag_cossack && !$prototype_complete_flag_cossack && $battle_complete_counter_cossack >= 17){ $_SESSION[$session_token]['flags']['prototype_events']['dr-cossack']['prototype_complete'] = $prototype_complete_flag_cossack = true; }


  // -- Mission Counts -- //

  // Define the missions counts for each chapter
  $chapter_mission_counts = array();
  $chapter_mission_counts['zero'] = 0;
  $chapter_mission_counts['one'] = 3;    // 3 x Starter
  $chapter_mission_counts['two'] = 8;    // 8 x Single
  $chapter_mission_counts['three'] = 1;  // 1 x Rival
  $chapter_mission_counts['four'] = 4;   // 4 x Double
  $chapter_mission_counts['five'] = 3;   // 3 x Fortress

  // Define the missions count totals for each chapter
  $chapter_mission_count_totals = array();
  $chapter_mission_count_totals['zero'] = 0;
  $chapter_mission_count_totals['one'] = $chapter_mission_counts['one'];
  $chapter_mission_count_totals['two'] = $chapter_mission_count_totals['one'] + $chapter_mission_counts['two'];
  $chapter_mission_count_totals['three'] = $chapter_mission_count_totals['two'] + $chapter_mission_counts['three'];
  $chapter_mission_count_totals['four'] = $chapter_mission_count_totals['three'] + $chapter_mission_counts['four'];
  $chapter_mission_count_totals['five'] = $chapter_mission_count_totals['four'] + $chapter_mission_counts['five'];

  // Define the chapter levels for missions to be progressively harder as you move forward
  $chapters_levels_common = array();
  $chapters_levels_common['one'] = 1;          // Met
  $chapters_levels_common['one-2'] = 2;        // Joe
  $chapters_levels_common['one-3'] = 3;        // Trill
  $chapters_levels_common['two'] = 4;          // Master
  $chapters_levels_common['two-2'] = 12;       // DocRobot
  $chapters_levels_common['three'] = 14;       // Rival + Support
  $chapters_levels_common['three-2'] = 16;     // Killer + Quint
  $chapters_levels_common['three-3'] = 18;     // Sunstar + Trill
  $chapters_levels_common['four'] = 20;        // Master + Master
  $chapters_levels_common['four-2'] = 30;      // King + DocRobot
  $chapters_levels_common['five'] = 35;        // HeroDS + DarkMan
  $chapters_levels_common['five-2'] = 40;      // BusterRodG + MegaWaterS + HyperStormH
  $chapters_levels_common['five-3'] = 45;      // Slur + Sunstar + Trill
  $chapters_levels_common['six'] = 50;         // Stardroid + Stardroid
  $chapters_levels_common['six-2'] = 60;       // Stardroid + Stardroid + Stardroid
  $chapters_levels_common['six-3'] = 70;       // Stardroid + Stardroid + Stardroid + Stardroid
  $chapters_levels_common['seven'] = 80;       // DarkMan + KillerSP
  $chapters_levels_common['seven-2'] = 90;     // OmegaSlur + PlanetManW + PlanetManF + PlanetManE
  $chapters_levels_common['seven-3'] = 100;    // CosmoMan + OmegaWeapon + EnergyBattery + AttackBattery + DefenseBattery + SpeedBattery
  $chapters_levels_common['eight'] = 200;      // Master
  $chapters_levels_common['eight-2'] = 1000;   // Cache + LaserMan + ShieldMan
  $chapters_levels_common['bonus'] = 30;       // Mechas
  $chapters_levels_common['bonus-2'] = 60;     // Masters


  // -- Dr. Light Chapters -- //

  // Define which chapters have been completed based on the unlocks above
  $chapters_complete_light = array();
  $chapters_complete_light['one'] = $battle_complete_counter_light >= $chapter_mission_count_totals['one'] ? true : false;
  $chapters_complete_light['two'] = $battle_complete_counter_light >= $chapter_mission_count_totals['two'] ? true : false;
  $chapters_complete_light['three'] = $battle_complete_counter_light >= $chapter_mission_count_totals['three'] ? true : false;
  $chapters_complete_light['four'] = $battle_complete_counter_light >= $chapter_mission_count_totals['four'] ? true : false;
  $chapters_complete_light['five'] = $battle_complete_counter_light >= $chapter_mission_count_totals['five'] ? true : false;

  // Define which chapters should be unlocked for Dr. Light based on missions complete
  $chapters_unlocked_light = array();
  $chapters_unlocked_light['one'] = $battle_complete_counter_light >= $chapter_mission_count_totals['zero'] ? true : false;
  $chapters_unlocked_light['one-2'] = $battle_complete_counter_light >= $chapter_mission_count_totals['zero'] + 1 ? true : false;
  $chapters_unlocked_light['one-3'] = $battle_complete_counter_light >= $chapter_mission_count_totals['zero'] + 2 ? true : false;
  $chapters_unlocked_light['two'] = $battle_complete_counter_light >= $chapter_mission_count_totals['one'] ? true : false;
  $chapters_unlocked_light['three'] = $battle_complete_counter_light >= $chapter_mission_count_totals['two'] ? true : false;
  $chapters_unlocked_light['four'] = $battle_complete_counter_light >= $chapter_mission_count_totals['three'] ? true : false;
  $chapters_unlocked_light['five'] = $battle_complete_counter_light >= $chapter_mission_count_totals['four'] ? true : false;
  $chapters_unlocked_light['five-2'] = $battle_complete_counter_light >= $chapter_mission_count_totals['four'] + 1 ? true : false;
  $chapters_unlocked_light['five-3'] = $battle_complete_counter_light >= $chapter_mission_count_totals['four'] + 2 ? true : false;
  $chapters_unlocked_light['bonus'] = $prototype_complete_flag_light || $chapters_complete_light['five'] ? true : false;
  $chapters_unlocked_light['player'] = false; //$prototype_complete_flag_light ? true : false;

  // If the player has manually unlocked any Dr. Light chapters via password, update their flags
  if (!$chapters_unlocked_light['player']){
    if ($battle_complete_counter_light > 0
      && (!empty($temp_game_flags['drlight_password_playerbattlebonus20130324'])
        || !empty($temp_game_flags['drlight_password_chaptergetplayerbattles']))){
        //$chapters_unlocked_light['player'] = true;
      }
  }

  // Loop through and add/update flags for each of the chapter unlocks
  if ($unlock_flag_light){
    foreach ($chapters_complete_light AS $key => $flag){
      $token = 'completed-chapter_dr-light_'.$key;
      if ($flag == true){ $temp_game_flags['events'][$token] = true; }
      elseif (isset($temp_game_flags['events'][$token])){ unset($temp_game_flags['events'][$token]); }
    }
    foreach ($chapters_unlocked_light AS $key => $flag){
      $token = 'unlocked-chapter_dr-light_'.$key;
      if ($flag == true){ $temp_game_flags['events'][$token] = true; }
      elseif (isset($temp_game_flags['events'][$token])){ unset($temp_game_flags['events'][$token]); }
    }
  }


  // -- Dr. Wily Chapters -- //

  // Define which chapters have been completed based on the unlocks above
  $chapters_complete_wily = array();
  $chapters_complete_wily['one'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['one'] ? true : false;
  $chapters_complete_wily['two'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['two'] ? true : false;
  $chapters_complete_wily['three'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['three'] ? true : false;
  $chapters_complete_wily['four'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['four'] ? true : false;
  $chapters_complete_wily['five'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['five'] ? true : false;

  // Define which chapters should be unlocked for Dr. Wily based on missions complete
  $chapters_unlocked_wily = array();
  $chapters_unlocked_wily['one'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['zero'] ? true : false;
  $chapters_unlocked_wily['one-2'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['zero'] + 1 ? true : false;
  $chapters_unlocked_wily['one-3'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['zero'] + 2 ? true : false;
  $chapters_unlocked_wily['two'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['one'] ? true : false;
  $chapters_unlocked_wily['three'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['two'] ? true : false;
  $chapters_unlocked_wily['four'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['three'] ? true : false;
  $chapters_unlocked_wily['five'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['four'] ? true : false;
  $chapters_unlocked_wily['five-2'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['four'] + 1 ? true : false;
  $chapters_unlocked_wily['five-3'] = $battle_complete_counter_wily >= $chapter_mission_count_totals['four'] + 2 ? true : false;
  $chapters_unlocked_wily['bonus'] = $prototype_complete_flag_wily || $chapters_complete_wily['five'] ? true : false;
  $chapters_unlocked_wily['player'] = false; //$prototype_complete_flag_wily ? true : false;

  // If the player has manually unlocked any Dr. Wily chapters via password, update their flags
  if (!$chapters_unlocked_wily['player']){
    if ($battle_complete_counter_wily > 0
      && (!empty($temp_game_flags['drwily_password_playerbattlebonus20130324'])
        || !empty($temp_game_flags['drwily_password_chaptergetplayerbattles']))){
        //$chapters_unlocked_wily['player'] = true;
      }
  }

  // Loop through and add/update flags for each of the chapter unlocks
  if ($unlock_flag_wily){
    foreach ($chapters_complete_wily AS $key => $flag){
      $token = 'completed-chapter_dr-wily_'.$key;
      if ($flag == true){ $temp_game_flags['events'][$token] = true; }
      elseif (isset($temp_game_flags['events'][$token])){ unset($temp_game_flags['events'][$token]); }
    }
    foreach ($chapters_unlocked_wily AS $key => $flag){
      $token = 'unlocked-chapter_dr-wily_'.$key;
      if ($flag == true){ $temp_game_flags['events'][$token] = true; }
      elseif (isset($temp_game_flags['events'][$token])){ unset($temp_game_flags['events'][$token]); }
    }
  }


  // -- Dr. Cossack Chapters -- //

  // Define which chapters have been completed based on the unlocks above
  $chapters_complete_cossack = array();
  $chapters_complete_cossack['one'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['one'] ? true : false;
  $chapters_complete_cossack['two'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['two'] ? true : false;
  $chapters_complete_cossack['three'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['three'] ? true : false;
  $chapters_complete_cossack['four'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['four'] ? true : false;
  $chapters_complete_cossack['five'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['five'] ? true : false;

  // Define which chapters should be unlocked for Dr. Cossack based on missions complete
  $chapters_unlocked_cossack = array();
  $chapters_unlocked_cossack['one'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['zero'] ? true : false;
  $chapters_unlocked_cossack['one-2'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['zero'] + 1 ? true : false;
  $chapters_unlocked_cossack['one-3'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['zero'] + 2 ? true : false;
  $chapters_unlocked_cossack['two'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['one'] ? true : false;
  $chapters_unlocked_cossack['three'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['two'] ? true : false;
  $chapters_unlocked_cossack['four'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['three'] ? true : false;
  $chapters_unlocked_cossack['five'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['four'] ? true : false;
  $chapters_unlocked_cossack['five-2'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['four'] + 1 ? true : false;
  $chapters_unlocked_cossack['five-3'] = $battle_complete_counter_cossack >= $chapter_mission_count_totals['four'] + 2 ? true : false;
  $chapters_unlocked_cossack['bonus'] = $prototype_complete_flag_cossack || $chapters_complete_cossack['five'] ? true : false;
  $chapters_unlocked_cossack['player'] = false; //$prototype_complete_flag_cossack ? true : false;

  // If the player has manually unlocked any Dr. Cossack chapters via password, update their flags
  if (!$chapters_unlocked_cossack['player']){
    if ($battle_complete_counter_cossack > 0
      && (!empty($temp_game_flags['drcossack_password_playerbattlebonus20130324'])
        || !empty($temp_game_flags['drcossack_password_chaptergetplayerbattles']))){
        //$chapters_unlocked_cossack['player'] = true;
      }
  }

  // Loop through and add/update flags for each of the chapter unlocks
  if ($unlock_flag_cossack){
    foreach ($chapters_complete_cossack AS $key => $flag){
      $token = 'completed-chapter_dr-cossack_'.$key;
      if ($flag == true){ $temp_game_flags['events'][$token] = true; }
      elseif (isset($temp_game_flags['events'][$token])){ unset($temp_game_flags['events'][$token]); }
    }
    foreach ($chapters_unlocked_cossack AS $key => $flag){
      $token = 'unlocked-chapter_dr-cossack_'.$key;
      if ($flag == true){ $temp_game_flags['events'][$token] = true; }
      elseif (isset($temp_game_flags['events'][$token])){ unset($temp_game_flags['events'][$token]); }
    }
  }

  // Now that we're done updating and defining flags, let's sort them
  //ksort($temp_game_flags['events']);

}

// Count the number of players unlocked
$unlock_count_players = 0;
if (!empty($unlock_flag_light)){ $unlock_count_players++; }
if (!empty($unlock_flag_wily)){ $unlock_count_players++; }
if (!empty($unlock_flag_cossack)){ $unlock_count_players++; }

// Count the number of stars collected in total
$unlock_count_stars = 0;
if (!empty($star_counter_light)){ $unlock_count_stars++; }
if (!empty($star_counter_wily)){ $unlock_count_stars++; }
if (!empty($star_counter_cossack)){ $unlock_count_stars++; }

// Count the number of cores collected in total
$unlock_count_cores = 0;
if (!empty($core_counter_light)){ $unlock_count_cores++; }
if (!empty($core_counter_wily)){ $unlock_count_cores++; }
if (!empty($core_counter_cossack)){ $unlock_count_cores++; }

?>