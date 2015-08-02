<?
/*
 * PLAYER MISSION SELECT
 * Only re-generate missions if it is approriate to do
 * so at this time (the player is requesting missions)
 */

// Only generate out mission markup data if conditions allow or do not exist
if (!defined('MMRPG_SCRIPT_REQUEST') ||
  ($this_data_select == 'this_battle_token' && in_array('this_player_token='.$this_prototype_data['this_player_token'], $this_data_condition))){
  // -- STARTER BATTLE : CHAPTER ONE -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'one';

  // If the player has completed at least zero battles, display the starter battle
  if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['one'])){

    // EVENT MESSAGE : CHAPTER ONE
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_chapter' => $this_prototype_data['this_current_chapter'],
      'option_maintext' => 'Chapter One : An Unexpected Attack'
      );

    // Generate the battle option with the starter data
    $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'];
    if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
      $temp_battle_omega = mmrpg_prototype_mission_starter($this_prototype_data, 'met', $chapters_levels_common['one'], $this_prototype_data['this_support_robot'], 'intro-field', 1, 'mecha');
      $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
      mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
      $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
    } else {
      $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
      $temp_battle_omega = mmrpg_battle::get_index_info($temp_battle_token);
    }

    // Add the omega battle to the options, index, and session
    $this_prototype_data['battle_options'][] = $temp_battle_omega;

  }


  // If the player has completed at least one battles, display the home laboratory/castle/citadel battle
  if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['one-2'])){
    // Generate the battle option with the starter data
    $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'-2';
    if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
      $temp_battle_omega = mmrpg_prototype_mission_starter($this_prototype_data, 'sniper-joe', ($chapters_levels_common['one-2']), $this_prototype_data['this_support_robot'], $this_prototype_data['this_player_field'], 1, 'mecha');
      $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
      mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
      $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
    } else {
      $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
      $temp_battle_omega = mmrpg_battle::get_index_info($temp_battle_token);
    }

    // Add the omega battle to the options, index, and session
    $this_prototype_data['battle_options'][] = $temp_battle_omega;

  }


  // If the player has completed at least one battles, display the trill in attack/defense/speed form battle
  if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['one-3'])){
    // Generate the battle option with the starter data
    $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'-3';
    if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
      $temp_battle_omega = mmrpg_prototype_mission_starter($this_prototype_data, 'trill', ($chapters_levels_common['one-3']), '', 'prototype-subspace', 1, 'boss');
      $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
      mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
      $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
    } else {
      $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
      $temp_battle_omega = mmrpg_battle::get_index_info($temp_battle_token);
    }

    // Add the omega battle to the options, index, and session
    $this_prototype_data['battle_options'][] = $temp_battle_omega;

  }


  // -- ROBOT MASTER BATTLES : CHAPTER TWO -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'two';

  // Only continue if the player has unlocked the required chapters
  if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['two'])){
    // EVENT MESSAGE : CHAPTER TWO
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_chapter' => $this_prototype_data['this_current_chapter'],
      'option_maintext' => 'Chapter Two : Robot Master Revival'
      );

    // Increment the phase counter
    $this_prototype_data['battle_phase'] += 1;
    $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
    $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];

    // Populate the battle options with the initial eight robots
    if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
    foreach ($this_prototype_data['target_robot_omega'] AS $key => $info){
      // Generate the battle option with the starter data
      $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'_'.$key;
      if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
        $temp_battle_omega = mmrpg_prototype_mission_single($this_prototype_data, $info['robot'], $info['field'], $chapters_levels_common['two']);
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
        $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
      } else {
        $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
        $temp_battle_omega = mmrpg_battle::get_index_info($temp_battle_token);
      }

      // Add the omega battle to the options, index, and session
      $this_prototype_data['battle_options'][] = $temp_battle_omega;

    }

  }


  // -- NEW CHALLENGER BATTLE : CHAPTER THREE -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'three';

  // If the first 1 + 8 battles are complete, unlock the ninth and recollect markup
  if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['three'])){
    // EVENT MESSAGE : CHAPTER THREE
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_chapter' => $this_prototype_data['this_current_chapter'],
      'option_maintext' => 'Chapter Three : The Rival Challengers'
      );

    // Unlock the rival fortress battle (fortress-i)
    $temp_index_token = $this_prototype_data['this_player_token'].'-fortress-i';
    $temp_battle_token = $this_prototype_data['this_player_token'].'-phase'.$this_prototype_data['battle_phase'].'-fortress-i';
    $temp_battle_omega = mmrpg_prototype_mission_fortress($this_prototype_data, $chapters_levels_common['three'], $temp_index_token, $temp_battle_token);

    // Add the omega battle to the battle options
    $this_prototype_data['battle_options'][] = $temp_battle_omega;
    mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
    //exit('$temp_battle_omega = <pre>'.print_r($temp_battle_omega, true).'</pre>');

  }


  // -- FUSION FIELD BATTLES : CHAPTER FOUR -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'four';

  // Only continue if the player has unlocked the required chapters
  if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['four'])){
    // EVENT MESSAGE : CHAPTER FOUR
    $this_prototype_data['battle_options'][] = array(
    'option_type' => 'message',
    'option_chapter' => $this_prototype_data['this_current_chapter'],
    'option_maintext' => 'Chapter Four : Battle Field Fusions'
    );

    // Increment the phase counter
    $this_prototype_data['battle_phase'] += 1;
    $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
    $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];

    // Populate the battle options with the initial eight robots combined
    if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
    foreach ($this_prototype_data['target_robot_omega'] AS $key => $info){
      // Generate the second info option and skip if already used
      if ($key > 0 && ($key + 1) % 2 == 0){ continue; }
      // Generate the battle option with the starter data
      $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'_'.$key;
      if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
        $info2 = $this_prototype_data['target_robot_omega'][$key + 1];
        $temp_battle_omega = mmrpg_prototype_mission_double($this_prototype_data, array($info['robot'], $info2['robot']), array($info['field'], $info2['field']), $chapters_levels_common['four'], true, true);
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
        $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
      } else {
        $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
        $temp_battle_omega = mmrpg_battle::get_index_info($temp_battle_token);
      }

      // Add the omega battle to the options, index, and session
      $this_prototype_data['battle_options'][] = $temp_battle_omega;

    }

  }


  // -- THE FINAL BATTLES : CHAPTER FIVE -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'five';

  // Only continue if the player has unlocked the required chapters
  if ($this_prototype_data['prototype_complete']
    || !empty($this_prototype_data['this_chapter_unlocked']['five'])
    || !empty($this_prototype_data['this_chapter_unlocked']['five-2'])
    || !empty($this_prototype_data['this_chapter_unlocked']['five-3'])){
    // EVENT MESSAGE : CHAPTER FOUR
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_chapter' => $this_prototype_data['this_current_chapter'],
      'option_maintext' => 'Chapter Five : The Final Battles'
      );

    // Final Destination I
    // Only continue if the player has unlocked the required chapters
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['five'])){

      // Unlock the first of the final destination battles (fortress-ii)
      $temp_index_token = $this_prototype_data['this_player_token'].'-fortress-ii';
      $temp_battle_token = $this_prototype_data['this_player_token'].'-phase'.$this_prototype_data['battle_phase'].'-fortress-ii';
      $temp_battle_omega = mmrpg_prototype_mission_fortress($this_prototype_data, $chapters_levels_common['five'], $temp_index_token, $temp_battle_token);

      // Add the omega battle to the battle options
      $this_prototype_data['battle_options'][] = $temp_battle_omega;
      mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    }

    // Final Destination II
    // Only continue if the player has unlocked the required chapters
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['five-2'])){

      // Unlock the second of the final destination battles (fortress-iii)
      $temp_index_token = $this_prototype_data['this_player_token'].'-fortress-iii';
      $temp_battle_token = $this_prototype_data['this_player_token'].'-phase'.$this_prototype_data['battle_phase'].'-fortress-iii';
      $temp_battle_omega = mmrpg_prototype_mission_fortress($this_prototype_data, $chapters_levels_common['five-2'], $temp_index_token, $temp_battle_token);

      // Add the omega battle to the battle options
      $this_prototype_data['battle_options'][] = $temp_battle_omega;
      mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    }

    // Final Destination III
    // Only continue if the player has unlocked the required chapters
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['five-3'])){
      // Collect the robot index for quick use
      $temp_robots_index = mmrpg_robot::get_index(); //$DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
      $temp_fields_index = mmrpg_field::get_index();

      // Collect and define the robot masters and support mechas to appear on this field
      $temp_robot_masters = array();
      $temp_support_mechas = array();
      if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
      foreach ($this_prototype_data['target_robot_omega'] AS $key => $info){
        $temp_field_info = mmrpg_field::parse_index_info($temp_fields_index[$info['field']]);
        if (!empty($temp_field_info['field_master'])){ $temp_robot_masters[] = $temp_field_info['field_master']; }
        if (!empty($temp_field_info['field_mechas'])){ $temp_support_mechas[] = array_pop($temp_field_info['field_mechas']); }
      }

      // Add the masters info into the omega battle
      $possible_items = array('item-energy-upgrade', 'item-weapon-upgrade', 'item-target-module', 'item-charge-module', 'item-fortune-module', 'item-field-booster', 'item-attack-booster', 'item-defense-booster', 'item-speed-booster');
      foreach ($mmrpg_index['types'] AS $token => $info){
        if (!empty($info['type_class']) && $info['type_class'] == 'special'){ continue; }
        elseif (in_array($token, array('copy', 'empty'))){ continue; }
        $possible_items[] = 'item-core-'.$token;
      }
      $possible_items_last_key = count($possible_items) - 1;
      $temp_robot_masters_tokens = $temp_robot_masters;
      $temp_robot_masters = array();
      foreach ($temp_robot_masters_tokens AS $key => $token){
        $index = mmrpg_robot::parse_index_info($temp_robots_index[$token]);
        $info = array();
        $info['robot_id'] = (MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1);
        $info['robot_token'] = $token;
        $info['robot_level'] = $chapters_levels_common['five-3'] + mt_rand(-5, 5);
        $info['robot_item'] = $possible_items[mt_rand(0, $possible_items_last_key)];
        $info['robot_abilities'] = array();
        $info['robot_abilities'] = mmrpg_prototype_generate_abilities($index, $info['robot_level'], 8, $info['robot_item']);
        $temp_robot_masters[] = $info;
      }
      shuffle($temp_robot_masters);

      // Unlock the first of the final destination battles (fortress-iv)
      $temp_index_token = $this_prototype_data['this_player_token'].'-fortress-iv';
      $temp_battle_token = $this_prototype_data['this_player_token'].'-phase'.$this_prototype_data['battle_phase'].'-fortress-iv';
      $temp_battle_omega = mmrpg_prototype_mission_fortress($this_prototype_data, $chapters_levels_common['five-3'], $temp_index_token, $temp_battle_token, $temp_robot_masters, $temp_support_mechas);

      // Add the omega battle to the battle options
      $this_prototype_data['battle_options'][] = $temp_battle_omega;
      mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    }

  }


  // -- PROTOTYPE COMPLETE BATTLE : BONUS BATTLES -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'bonus';

  // Only continue if the player has unlocked the required chapters
  if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['bonus']){
    // EVENT MESSAGE : BONUS BATTLES
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_chapter' => $this_prototype_data['this_current_chapter'],
      'option_maintext' => 'Bonus Battles : Prototype Complete!'
      );

    // Generate the bonus battle and using the prototype data
    $temp_battle_omega = mmrpg_prototype_mission_bonus($this_prototype_data, 3, 'mecha');
    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
    // Add the omega battle to the options, index, and session
    $this_prototype_data['battle_options'][] = $temp_battle_omega;
    mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    // Generate the bonus battle and using the prototype data
    $temp_battle_omega = mmrpg_prototype_mission_bonus($this_prototype_data, 6, 'master');
    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
    // Add the omega battle to the options, index, and session
    $this_prototype_data['battle_options'][] = $temp_battle_omega;
    mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

  }

  // -- SPECIAL PLAYER BATTLE : PLAYER BATTLES -- //

  // Update the prototype data's global current chapter variable
  $this_prototype_data['this_current_chapter'] = 'player';

  // Unlock a battle with a randomized player from the leaderboards if the game is done
  //$temp_flags = !empty($_SESSION['GAME']['flags']) ? $_SESSION['GAME']['flags'] : array();
  $temp_ptoken = str_replace('-', '', $this_prototype_data['this_player_token']);
  if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['player']){
    //die('checkpoint1');
    if (true){
      //die('checkpoint2');

      // EVENT MESSAGE : PLAYER BATTLES
      $this_prototype_data['battle_options'][] = array(
        'option_type' => 'message',
        'option_chapter' => $this_prototype_data['this_current_chapter'],
        'option_maintext' => 'Player Battles : Leaderboard Challengers'
        );

      // Include the leaderboard data for pruning
      $this_leaderboard_online_players = mmrpg_prototype_leaderboard_online();
      $temp_include_usernames = array();
      if (!empty($this_leaderboard_online_players)){
        foreach ($this_leaderboard_online_players AS $info){ $temp_include_usernames[] = $info['token']; }
      }

      // Pull a random set of players from the database with similar point levels
      $temp_player_list = mmrpg_prototype_leaderboard_targets($this_userid, 9, $this_prototype_data['target_player_token'], $this_prototype_data['this_player_token']);
      if (empty($temp_player_list)){ $temp_player_list = mmrpg_prototype_leaderboard_targets($this_userid, 9, $this_prototype_data['this_player_token'], $this_prototype_data['this_player_token']); }

      // If player data was actuall pulled, continue
      if (!empty($temp_player_list)){
        // Shuffle the player list
        $max_battle_count = 2;
        if ($temp_player_list >= 4){ $max_battle_count = 4; }
        if ($temp_player_list >= 6){ $max_battle_count = 6; }
        $temp_player_list = array_slice($temp_player_list, 0, 6);
        shuffle($temp_player_list);

        // Loop through the list up for two to four times, creating new battles
        if (empty($_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_player_battle_factors'])){
          $temp_field_factors_one = $this_omega_factors_two;
          $temp_field_factors_two = $this_omega_factors_one;
          $temp_field_factors_three = $this_omega_factors_three;
          shuffle($temp_field_factors_one);
          shuffle($temp_field_factors_two);
          shuffle($temp_field_factors_three);
          $temp_one = array_merge($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
          $temp_two = array_merge($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
          $temp_three = array_merge($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
          $temp_field_factors_one = $temp_one;
          $temp_field_factors_two = $temp_two;
          $temp_field_factors_three = $temp_three;
          shuffle($temp_field_factors_one);
          shuffle($temp_field_factors_two);
          shuffle($temp_field_factors_three);
          $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_player_battle_factors'] = array($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
        } else {
          list($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three) = $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_player_battle_factors'];
        }

        for ($i = 0; $i < $max_battle_count; $i++){

          // If there are no more players, break
          if (empty($temp_player_list)){ break; }

          // Pull and random player from the list and collect their full data
          $temp_max_robots = 2;
          if ($i >= 2 && $this_prototype_data['robots_unlocked'] >= 4){ $temp_max_robots = 4; }
          if ($i >= 4 && $this_prototype_data['robots_unlocked'] >= 8){ $temp_max_robots = 8; }
          $temp_player_array = array_shift($temp_player_list);
          $temp_battle_omega = mmrpg_prototype_mission_player($this_prototype_data, $temp_player_array, $temp_max_robots, $temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);

          // If the collected omega battle was empty, continue gracefully
          if (empty($temp_battle_omega) || empty($temp_battle_omega['battle_token'])){
            continue;
          }
          // Update the option chapter to the current
          $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

          // Define the button name if not set already
          $temp_battle_omega['battle_button'] = !empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name'];

          // If this user is online, update the battle button with details
          if (!empty($temp_player_array['values']['flag_online'])){
            $temp_battle_omega['option_style'] = 'border-color: green !important; ';
            $temp_battle_omega['battle_button'] .= ' <sup class="online_type player_type player_type_nature">Online</sup>';
          }

          // If this user is custom, update the battle button with details
          if (!empty($temp_player_array['values']['flag_custom'])){
            $temp_battle_omega['battle_button'] .= ' <sup class="custom_type player_type player_type_flame">&hearts;</sup>';
          }

          // Add the omega battle to the options, index, and session
          $this_prototype_data['battle_options'][] = $temp_battle_omega;
          mmrpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
          unset($temp_battle_omega);

        }

      }

      // Unset the temp player array
      unset($temp_player_list);

    }

  }


}

?>