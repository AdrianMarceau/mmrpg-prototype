<?

//die('$_SESSION[\'GAME\'][\'CACHE_DATE\'] = '.$_SESSION['GAME']['CACHE_DATE']);

// Check the loaded game's CACHE DATE to see if it needs to be updated from 2012/12/14
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] <= '20121214-03'){
  
  $battle_rewards = !empty($_SESSION['GAME']['values']['battle_rewards']) ? $_SESSION['GAME']['values']['battle_rewards'] : array();
  if (!empty($_SESSION['GAME']['values']['battle_rewards'])){
    foreach ($_SESSION['GAME']['values']['battle_rewards'] AS $player_token => $player_info){
      if (!empty($player_info['player_robots'])){
        foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
          $robot_info['robot_experience'] = 0;
          $robot_info['robot_level'] = 1;
          if ($robot_info['robot_points'] >= 1000){
            $level_boost = floor($robot_info['robot_points'] / 1000);
            $robot_info['robot_experience'] = $robot_info['robot_points'] - ($level_boost * 1000);
            $robot_info['robot_level'] += $level_boost;
          } else {
            $robot_info['robot_experience'] = $robot_info['robot_points'];
          }
          /*
          echo $robot_info['robot_token'].'<br />
          	points: '.$robot_info['robot_points'].' |
          	experience: '.$robot_info['robot_experience'].' |
          	level: '.$robot_info['robot_level'].'
          	<br /><br />';
          	*/
          unset($robot_info['robot_points']);
          $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_robots'][$robot_token] = $robot_info;
        }
      }
    }
  }
  
  $_SESSION['GAME']['CACHE_DATE'] = '20121214-03'; //MMRPG_CONFIG_CACHE_DATE;

  //die('Your game has been updated!');
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20130106
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20130106-01'){
      
  $battle_rewards = !empty($_SESSION['GAME']['values']['battle_rewards']) ? $_SESSION['GAME']['values']['battle_rewards'] : array();
  if (!empty($_SESSION['GAME']['values']['battle_rewards'])){
    foreach ($_SESSION['GAME']['values']['battle_rewards'] AS $player_token => $player_info){
      $this_setting = array('player_token' => $player_token, 'player_robots' => $player_info['player_robots']);
      $_SESSION['GAME']['values']['battle_settings'][$player_token] = $this_setting;
      mmrpg_game_unlock_ability($player_info, false, array('ability_token' => 'buster-shot'));
      if (!empty($player_info['player_robots'])){
        foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
          $this_setting = array('robot_token' => $robot_token, 'robot_abilities' => $robot_info['robot_abilities']);
          $_SESSION['GAME']['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_token] = $this_setting;
        }
      }
    }
  }
  
  $_SESSION['GAME']['CACHE_DATE'] = '20130106-01'; //MMRPG_CONFIG_CACHE_DATE;
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20130127
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20130127-01'){
  // Empty the completed battle arrays, as we are starting fresh
  $_SESSION['GAME']['values']['battle_complete']['dr-light'] = array();
  $_SESSION['GAME']['values']['battle_complete']['dr-wily'] = array();
  $_SESSION['GAME']['values']['battle_complete']['dr-cossack'] = array();
  $_SESSION['GAME']['values']['battle_failure']['dr-light'] = array();
  $_SESSION['GAME']['values']['battle_failure']['dr-wily'] = array();
  $_SESSION['GAME']['values']['battle_failure']['dr-cossack'] = array();
  // Update the game's cache date
  $_SESSION['GAME']['CACHE_DATE'] = '20130127-01'; //MMRPG_CONFIG_CACHE_DATE;
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20130129
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20130130-01'){
      
  $battle_settings = !empty($_SESSION['GAME']['values']['battle_settings']) ? $_SESSION['GAME']['values']['battle_settings'] : array();
  if (!empty($_SESSION['GAME']['values']['battle_settings'])){
    foreach ($_SESSION['GAME']['values']['battle_settings'] AS $player_token => $player_info){
      if (!empty($player_info['player_robots'])){
        foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
          $_SESSION['GAME']['values']['battle_settings'][$player_info['player_token']]['player_robots'][$robot_token]['original_player'] = $player_token;
        }
      }
    }
  }
  
  $_SESSION['GAME']['CACHE_DATE'] = '20130130-01'; //MMRPG_CONFIG_CACHE_DATE;
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20131801
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20130218-01'){
      
  // Loop through and unset all completed battle tokens
  $temp_battles_complete = $_SESSION['GAME']['values']['battle_complete'];
  foreach ($temp_battles_complete AS $temp_player_token => $temp_battle_tokens){
    foreach ($temp_battle_tokens AS $temp_battle_token => $temp_battle_info){
      unset($_SESSION['GAME']['values']['battle_complete'][$temp_player_token][$temp_battle_token]);
    }
  }
  
  $_SESSION['GAME']['CACHE_DATE'] = '20130218-01'; //MMRPG_CONFIG_CACHE_DATE;
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20130728
// This update converts all completed battle values into arrays with the proper values
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20130728-01'){
      
  //die('checkpoint');
  
  // Loop through and unset all completed battle tokens
  $temp_battles_complete = $_SESSION['GAME']['values']['battle_complete'];
  foreach ($temp_battles_complete AS $temp_player_token => $temp_battle_tokens){
    $temp_battle_key = 0;
    foreach ($temp_battle_tokens AS $temp_battle_token => $temp_battle_info){
      //die('<pre>BEFORE : '.print_r($temp_battle_info, true).'</pre>');
      // Define the starting level for this battle and default to one
      $temp_battle_level = 1 + $temp_battle_key;
      // Define the temp updated flag
      $temp_updated = false;
      // If this battle is not in array format, start fresh
      if (!is_array($temp_battle_info)){
        $temp_updated = true;
        $temp_battle_info = array('battle_token' => $temp_battle_token, 'battle_count' => 1, 'battle_level' => $temp_battle_level);
      }
      // Otherwise, if array format already, check for all fields
      else {
        // Ensure a counter has been created for this battle
        if (empty($temp_battle_info['battle_count'])){
          $temp_updated = true;
          $temp_battle_info['battle_count'] = 1;
        }
        // Ensure a base level value has been set for this battle
        if (empty($temp_battle_info['battle_level'])){
          $temp_updated = true;
          $temp_battle_info['battle_level'] = $temp_battle_level;
        }
      }
      // Only update if changed
      if ($temp_updated && !empty($temp_battle_info)){
        //die('<pre>AFTER : '.print_r($temp_battle_info, true).'</pre>');
        $_SESSION['GAME']['values']['battle_complete'][$temp_player_token][$temp_battle_token] = $temp_battle_info;
        $temp_battle_key++;
      }
    }
  }
  
  //die('COMPLETE');
  $_SESSION['GAME']['CACHE_DATE'] = '20130728-01'; //MMRPG_CONFIG_CACHE_DATE;
  
}


// Check the loaded game's CACHE DATE to see if it needs to be updated from 20131012-01
// This update ensures all unlocked robots have the proper original_player variable set
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20131012-01'){
      
  //die('checkpoint');
  
  // Loop through and unset all completed battle tokens
  if (!isset($_SESSION['GAME']['values']['battle_settings'])){ $_SESSION['GAME']['values']['battle_settings'] = array(); }
  $temp_battle_settings = $_SESSION['GAME']['values']['battle_settings'];
  if (!isset($_SESSION['GAME']['values']['battle_rewards'])){ $_SESSION['GAME']['values']['battle_rewards'] = array(); }
  $temp_battle_rewards = $_SESSION['GAME']['values']['battle_rewards'];
  foreach ($temp_battle_settings AS $temp_player_token => $temp_player_settings){
    // Unset this player's omega factors - we've made some changes
    $temp_session_key = $temp_player_token.'_target-robot-omega_prototype';
    unset($_SESSION['GAME']['values'][$temp_session_key]);
    // Loop through the player's robots
    foreach ($temp_player_settings['player_robots'] AS $temp_robot_token => $temp_robot_info){
      
      // Define the temp updated flag
      $temp_updated = false;
      $temp_robot_settings = $temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token];
      $temp_robot_rewards = $temp_battle_rewards[$temp_player_token]['player_robots'][$temp_robot_token];
      // Ensure this robot exists in the index before continuing
      $temp_robot_index = mmrpg_robot::get_index_info($temp_robot_token);
      if (!empty($temp_robot_index)){
        if (!isset($temp_robot_index['robot_class'])){ $temp_robot_index['robot_class'] = 'master'; }
        
        // DEBUG
        //echo $temp_player_token.':'.$temp_robot_token.'<br /> ';
        //echo __LINE__.' : <pre>BEFORE:'.print_r($temp_robot_settings, true).'</pre><br />';
        
        // DEBUG
        //echo $temp_player_token.':'.$temp_robot_token.'<br /> ';
        //echo __LINE__.' : <pre>ROBOT_CLASS:'.print_r($temp_robot_index['robot_class'], true).'</pre><br />';
      
        // If this robot is a mecha, we have to (unfortunately) remove it from the session
        if (!empty($temp_robot_index['robot_class']) && $temp_robot_index['robot_class'] == 'mecha'){
          // Remove this robot from the settings and rewards array
          unset($temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token]);
          unset($temp_battle_rewards[$temp_player_token]['player_robots'][$temp_robot_token]);
          unset($_SESSION['GAME']['values']['battle_settings'][$temp_player_token]['player_robots'][$temp_robot_token]);
          unset($_SESSION['GAME']['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]);
          // Unlock the mecha support ability early(?) as a consolation for removing their robot
          mmrpg_game_unlock_ability($mmrpg_index['players'][$temp_player_token], false, array('ability_token' => 'mecha-support'));
          // DEBUG
          //echo $temp_player_token.':'.$temp_robot_token.'<br /> ';
          //echo __LINE__.' : <pre>UNSET :'.print_r($temp_robot_index['robot_class'], true).' ROBOT $_SESSION[\'GAME\'][\'values\'][\'battle_settings\']['.$temp_player_token.']['.$temp_robot_token.']</pre><br />';
          continue;
        }
        
        // -- BATTLE SETTINGS -- //
        
        // If the original player for this robot has not been set, find it
        if (true || empty($temp_robot_settings['original_player'])){
          $temp_updated = true;
          $temp_robot_settings['original_player'] = '';
          // Loop through all players and see who unlocks the robot
          foreach ($mmrpg_index['players'] AS $token => $info){
            if ($token == 'player'){ continue; }
            if (in_array($temp_robot_token, $info['player_robots_unlockable'])){
              $temp_robot_settings['original_player'] = $token;
              continue;
            }
          }
        }
        
        // Loop through abilities and remove bogus ones
        if (!empty($temp_robot_settings['robot_abilities'])){
          $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
          foreach ($temp_robot_settings['robot_abilities'] AS $token => $info){
            // Ensure the ability exists in the index, otherwise remove
            $index = mmrpg_ability::parse_index_info($temp_abilities_index[$token]);
            if (empty($index)){
              // Remove this ability from the settings and rewards array
              unset($_SESSION['GAME']['values']['battle_settings'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_abilities'][$token]);
              unset($_SESSION['GAME']['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_abilities'][$token]);
              continue;
            }
          }
        }
        // If the abilities are somehow empty, add a default one
        if (empty($temp_robot_settings['robot_abilities'])){
          // Default this robot to only having the buster shot
          $temp_robot_settings['robot_abilities'] = array('buster-shot' => array('ability_token' => 'buster-shot'));
        }
        
        // -- BATTLE REWARDS -- //

        // Fill in missing fields for robot reward array fields
        if (empty($temp_robot_rewards['flags'])){ $temp_updated = true; $temp_robot_rewards['flags'] = array(); }
        if (empty($temp_robot_rewards['values'])){ $temp_updated = true; $temp_robot_rewards['values'] = array(); }
        if (empty($temp_robot_rewards['counters'])){ $temp_updated = true; $temp_robot_rewards['counters'] = array(); }
        if (empty($temp_robot_rewards['robot_token'])){ $temp_updated = true; $temp_robot_rewards['robot_token'] = $temp_robot_token; }
        if (empty($temp_robot_rewards['robot_level'])){ $temp_updated = true; $temp_robot_rewards['robot_level'] = 1; }
        if (empty($temp_robot_rewards['robot_experience'])){ $temp_updated = true; $temp_robot_rewards['robot_experience'] = 0; }
        if (empty($temp_robot_rewards['robot_abilities'])){ $temp_updated = true; $temp_robot_rewards['robot_abilities'] = array('buster-shot' => array('ability_token' => 'buster-shot')); }
        if (empty($temp_robot_rewards['robot_energy'])){ $temp_updated = true; $temp_robot_rewards['robot_energy'] = 0; }
        if (empty($temp_robot_rewards['robot_attack'])){ $temp_updated = true; $temp_robot_rewards['robot_attack'] = 0; }
        if (empty($temp_robot_rewards['robot_defense'])){ $temp_updated = true; $temp_robot_rewards['robot_defense'] = 0; }
        if (empty($temp_robot_rewards['robot_speed'])){ $temp_updated = true; $temp_robot_rewards['robot_speed'] = 0; }
        if (empty($temp_robot_rewards['robot_energy_pending'])){ $temp_updated = true; $temp_robot_rewards['robot_energy_pending'] = 0; }
        if (empty($temp_robot_rewards['robot_attack_pending'])){ $temp_updated = true; $temp_robot_rewards['robot_attack_pending'] = 0; }
        if (empty($temp_robot_rewards['robot_defense_pending'])){ $temp_updated = true; $temp_robot_rewards['robot_defense_pending'] = 0; }
        if (empty($temp_robot_rewards['robot_speed_pending'])){ $temp_updated = true; $temp_robot_rewards['robot_speed_pending'] = 0; }
        
        // If this robot is at level 100, create the maxlevel flag
        if ($temp_robot_rewards['robot_level'] >= 100){
          // Add the maxlevel flag to this robot to show it's amazing
          $temp_robot_rewards['flags']['reached_max_level'] = true;
        }
        
        // DEBUG
        //echo __LINE__.' : <pre>AFTER:'.print_r($temp_robot_settings, true).'</pre><br />';
        
      }
      // Otherwise if this robot does not exist remove it and continue
      else {
        // Remove this robot from the settings and rewards array
        unset($_SESSION['GAME']['values']['battle_settings'][$temp_player_token]['player_robots'][$temp_robot_token]);
        unset($_SESSION['GAME']['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]);
        continue;
      }
      
      // Only update if changed
      if ($temp_updated && !empty($temp_robot_settings)){
        //echo(__LINE__.' : <pre>AFTER : '.print_r($temp_robot_settings, true).'</pre><br />');
        unset($_SESSION['GAME']['values']['battle_settings'][$temp_player_token]['player_robots'][$temp_robot_token]);
        unset($_SESSION['GAME']['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]);
        $_SESSION['GAME']['values']['battle_settings'][$temp_robot_settings['original_player']]['player_robots'][$temp_robot_token] = $temp_robot_settings;
        $_SESSION['GAME']['values']['battle_rewards'][$temp_robot_settings['original_player']]['player_robots'][$temp_robot_token] = $temp_robot_rewards;
        //echo(__LINE__.' : <pre>AFTERSESSION : '.print_r($_SESSION['GAME']['values']['battle_settings'][$temp_player_token]['player_robots'][$temp_robot_token], true).'</pre><br />');
      }
      
      
    }
  
    // And finally, unset all the battle complete and failure arrays, we're starting fresh
    unset($_SESSION['GAME']['values']['battle_complete'][$temp_player_token]);
    unset($_SESSION['GAME']['values']['battle_failure'][$temp_player_token]);
    unset($_SESSION['GAME']['flags']['prototype_events']);
    
  }
  
  // Unset events to prevent weirdness
  unset($_SESSION['GAME']['flags']['events']);
  
  //die('COMPLETE');
  $_SESSION['GAME']['CACHE_DATE'] = '20131012-01'; //MMRPG_CONFIG_CACHE_DATE;
  
}


// Check the loaded game's CACHE DATE to see if it needs to be updated from 20131026-11
// This update ensures all unlocked robots have the proper original_player variable set
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20131026-11'){
      
  //die('checkpoint');
  
  // Loop through player index if not empty
  foreach ($mmrpg_index['players'] AS $player_token => $player_info){
    $temp_session_key = $player_token.'_target-robot-omega_prototype';
    if (!empty($_SESSION['GAME']['values'][$temp_session_key])){
      foreach ($_SESSION['GAME']['values'][$temp_session_key] AS $key => $group){
        foreach ($group AS $key2 => $factor){
          if (!empty($factor['robot']) && $factor['robot'] == 'wood-man'){
            $_SESSION['GAME']['values'][$temp_session_key][$key][$key2]['field'] = 'preserved-forest';
          }
        }
      }
    }
  }
  
  //die('COMPLETE');
  $_SESSION['GAME']['CACHE_DATE'] = '20131026-11'; //MMRPG_CONFIG_CACHE_DATE;
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20131026-15
// This update ensures all unlocked robots have the proper original_player variable set
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20131026-15'){
      
  //die('checkpoint');
  
  // Loop through player index if not empty
  $temp_items_array = array();
  foreach ($mmrpg_index['players'] AS $player_token => $player_info){
    if (!empty($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_items'])){
      foreach ($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_items'] AS $token => $count){
        if (!isset($temp_items_array[$token])){ $temp_items_array[$token] = 0; }
        $temp_items_array[$token] += $count;
      }
    }
  }
  
  // Create the new unified items array in the session
  if (!isset($_SESSION['GAME']['values']['battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = array(); }
  $_SESSION['GAME']['values']['battle_items'] = $temp_items_array;
  
  //die(print_r($temp_items_array, true));
  
  //die('COMPLETE');
  $_SESSION['GAME']['CACHE_DATE'] = '20131026-15'; //MMRPG_CONFIG_CACHE_DATE;
  
}
  

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20131228-01
// This update ensures all unlocked robots have the proper original_player variable set
if (empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20140104-02'){
      
  //die('checkpoint '.$data['user_id']);
  
  // -- FIX ROBOT DATABASE -- //
  
  // Collect the current robot database and define the array for the new one
  $session_robot_database = !empty($_SESSION['GAME']['values']['robot_database']) ? $_SESSION['GAME']['values']['robot_database'] : array();
  $new_robot_database = array();
  
  // Loop through the current database and re-add all the robots in their new format
  foreach ($session_robot_database AS $robot_token => $robot_info){
    $new_robot_info = array('robot_token' => $robot_token);
    $new_robot_info['robot_encountered'] = 1;
    $new_robot_info['robot_defeated'] = !empty($robot_info['robot_defeated']) ? $robot_info['robot_defeated'] : 0;
    if ($new_robot_info['robot_defeated'] > $new_robot_info['robot_encountered']){ $new_robot_info['robot_encountered'] = $new_robot_info['robot_defeated']; }
    if (mmrpg_prototype_robot_unlocked(false, $robot_token)){
      $temp_rewards = mmrpg_prototype_robot_rewards(false, $robot_token);
      $temp_rewards_level = !empty($temp_rewards['robot_level']) ? round(($temp_rewards['robot_level'] - 1) * 1.5) + 1 : 1;
      $new_robot_info['robot_unlocked'] = 1;
      $new_robot_info['robot_summoned'] = !empty($robot_info['robot_summoned']) ? $robot_info['robot_summoned'] : $temp_rewards_level;
      $new_robot_info['robot_scanned'] = !empty($robot_info['robot_scanned']) ? $robot_info['robot_scanned'] : 1;
    } elseif (!empty($robot_info['robot_summoned'])){
      $new_robot_info['robot_summoned'] = $robot_info['robot_summoned'];
      $new_robot_info['robot_scanned'] = !empty($robot_info['robot_scanned']) ? $robot_info['robot_scanned'] : 1;
    } elseif (!empty($robot_info['robot_scanned'])){
      $new_robot_info['robot_scanned'] = $robot_info['robot_scanned'];
    }
    $new_robot_database[$robot_token] = $new_robot_info;
    
  }
  
  // DEBUG DEBUG DEBUG
  //die('<pre>$new_robot_database : '.print_r($new_robot_database, true).'</pre><hr />');
  
  // Update the session with the new database data
  $_SESSION['GAME']['values']['robot_database'] = $new_robot_database;
  
  
  // -- REMOVED UNLOCKED MECHA ROBOTS FROM GAME -- //
  
  $temp_battle_rewards = !empty($_SESSION['GAME']['values']['battle_rewards']) ? $_SESSION['GAME']['values']['battle_rewards'] : array();
  $temp_battle_settings = !empty($_SESSION['GAME']['values']['battle_settings']) ? $_SESSION['GAME']['values']['battle_settings'] : array();
  $temp_indexed_data = array_merge($temp_battle_rewards, $temp_battle_settings);
  if (!empty($temp_indexed_data)){
    foreach ($temp_indexed_data AS $temp_player => $temp_player_info){
      if (!empty($temp_player_info['player_robots'])){
        foreach ($temp_player_info['player_robots'] AS $temp_robot => $temp_robot_info){
          $temp_index_robot = mmrpg_robot::get_index_info($temp_robot);
          if (empty($temp_index_robot) || (!empty($temp_index_robot['robot_class']) && $temp_index_robot['robot_class'] == 'mecha')){
            unset($_SESSION['GAME']['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot]);
            unset($_SESSION['GAME']['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]);
          }
        }
      }
    }
  }
    
  //die('COMPLETE');
  $_SESSION['GAME']['CACHE_DATE'] = MMRPG_CONFIG_CACHE_DATE;
  
}

// Check the loaded game's CACHE DATE to see if it needs to be updated from 20140104-03
// This update ensures all unlocked robots have the proper original_player variable set
if (false && empty($_SESSION['GAME']['CACHE_DATE']) || $_SESSION['GAME']['CACHE_DATE'] < '20140104-07'){
  
  // Update the leaderboard count with new numbers
  $temp_board_update = array();
  
  $temp_board_update['board_stars'] = 0;
  $temp_board_update['board_stars_dr_light'] = 0;
  $temp_board_update['board_stars_dr_wily'] = 0;
  $temp_board_update['board_stars_dr_cossack'] = 0;
  
  $temp_board_update['board_abilities'] = 0;
  $temp_board_update['board_abilities_dr_light'] = 0;
  $temp_board_update['board_abilities_dr_wily'] = 0;
  $temp_board_update['board_abilities_dr_cossack'] = 0;
  
  $temp_board_update['board_missions'] = 0;
  $temp_board_update['board_missions_dr_light'] = 0;
  $temp_board_update['board_missions_dr_wily'] = 0;
  $temp_board_update['board_missions_dr_cossack'] = 0;
  
  if (!empty($_SESSION['GAME']['values']['battle_stars'])){
    
    foreach ($_SESSION['GAME']['values']['battle_stars'] AS $key => $info){
      $temp_player = str_replace('-', '_', $info['star_player']);
      $temp_board_update['board_stars'] += 1;
      $temp_board_update['board_stars_'.$temp_player] += 1;
    }
    
  }
  
  if (!empty($_SESSION['GAME']['values']['battle_complete'])){
    foreach ($_SESSION['GAME']['values']['battle_complete'] AS $ptoken => $battles){
      $temp_player = str_replace('-', '_', $ptoken);
      $temp_board_update['board_missions'] += count($battles);
      $temp_board_update['board_missions_'.$temp_player] += count($battles);
    }
  }
  
  if (!empty($_SESSION['GAME']['values']['battle_rewards'])){
    foreach ($_SESSION['GAME']['values']['battle_rewards'] AS $ptoken => $pinfo){
      $temp_player = str_replace('-', '_', $ptoken);
      if (!empty($pinfo['player_abilities'])){
        $temp_board_update['board_abilities'] += count($pinfo['player_abilities']);
        $temp_board_update['board_abilities_'.$temp_player] += count($pinfo['player_abilities']);
      }
    }
  }
  
  //$temp_board_update['user_id'] = $_SESSION['TEMP']['temp_update_user_id'];
  
  //die('$temp_board_update = <pre>'.print_r($temp_board_update, true).'</pre>');
  
  

  // Update the leaderboard with the star changes
  $DB->update('mmrpg_leaderboard', $temp_board_update, array('user_id' => $_SESSION['TEMP']['temp_update_user_id']));
  
  
  //die('COMPLETE');
  $_SESSION['GAME']['CACHE_DATE'] = MMRPG_CONFIG_CACHE_DATE;
  
  
}


//session_write_close();
//exit('end');

?>