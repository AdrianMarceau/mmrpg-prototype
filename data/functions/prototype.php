<?php

/*
 * PROTOTYPE FUNCTIONS
 */


/*
// Define a function for calculating total experience points at a given level
function mmrpg_prototype_calculate_experience($this_level, $max_level = 100, $min_experience = 1000, $max_experience = 1000000){

  $b = log(1.0 * $max_experience / $min_experience) / ($max_level - 1);
  $a = 1.0 * $min_experience / (exp($b) - 1.0);

  $x = (int)($a * exp($b * $this_level));
  $y = 10 * (int)(log($x) / log(10) - 2.2);
  $e = (int)($x / $y) * $y;

  return $e;
}

// Define a function for calculating required experience points to the next level
function mmrpg_prototype_calculate_experience_required($this_level, $max_level = 100, $min_experience = 1000, $max_experience = 1000000){
  $e = mmrpg_prototype_calculate_experience($this_level, $max_level, $min_experience, $max_experience) - mmrpg_prototype_calculate_experience(($this_level - 1), $max_level, $min_experience, $max_experience);
  return $e;
}
*/


// Define a function for calculating required experience points to the next level
function mmrpg_prototype_calculate_experience_required($this_level, $max_level = 100, $min_experience = 1000){

  $last_level = $this_level - 1;
  $level_mod = $this_level / $max_level;
  $this_experience = round($min_experience + ($last_level * $level_mod * $min_experience));

  return $this_experience;
}

// Define a function for calculating required experience points to the next level
function mmrpg_prototype_calculate_level_by_experience($this_experience, $max_level = 100, $min_experience = 1000){
  $temp_total_experience = 0;
  for ($this_level = 1; $this_level < $max_level; $this_level++){
    $temp_experience = mmrpg_prototype_calculate_experience_required($this_level, $max_level, $min_experience);
    $temp_total_experience += $temp_experience;
    if ($temp_total_experience > $this_experience){
      return $this_level - 1;
    }
  }
  return $max_level;
}

// Define a function for checking a player has completed the prototype
function mmrpg_prototype_complete($player_token = ''){
  // Pull in global variables
  //global $mmrpg_index;
  $mmrpg_index_players = $GLOBALS['mmrpg_index']['players'];
  $session_token = mmrpg_game_token();
  // If the player token was provided, do a quick check
  if (!empty($player_token)){
    // Return the prototype complete flag for this player
    if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){ return 1; }
    else { return 0; }
  }
  // Otherwise loop through all players and check each
  else {
    // Loop through unlocked robots and return true if any are found to be completed
    $complete_count = 0;
    foreach ($mmrpg_index_players AS $player_token => $player_info){
      if (mmrpg_prototype_player_unlocked($player_token)){
        if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){
          $complete_count += 1;
        }
      }
    }
    // Otherwise return false by default
    return $complete_count;
  }
}

// Define a function for checking the battle's prototype points total
function mmrpg_prototype_event_complete($event_token){
  // Return the current point total for thisgame
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['flags']['events'][$event_token])){ return 1; }
  else { return 0; }
}

// Define a function for checking the battle's prototype points total
function mmrpg_prototype_battle_points(){
  // Return the current point total for thisgame
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['counters']['battle_points'])){ return $_SESSION[$session_token]['counters']['battle_points']; }
  else { return 0; }
}
// Define a function for checking a player's prototype points total
function mmrpg_prototype_player_points($player_token){
  // Return the current point total for this player
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_points'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_points']; }
  else { return 0; }
}
// Define a function for checking a player's prototype rewards array
function mmrpg_prototype_player_rewards($player_token){
  // Return the current rewards array for this player
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]; }
  else { return array(); }
}
// Define a function for checking a player's prototype settings array
function mmrpg_prototype_player_settings($player_token){
  // Return the current rewards array for this player
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token])){ return $_SESSION[$session_token]['values']['battle_settings'][$player_token]; }
  else { return array(); }
}
// Define a function for checking a player's prototype settings array
function mmrpg_prototype_player_stars_available($player_token){
  // Return the current rewards array for this player
  $session_token = mmrpg_game_token();

  // Collect the omega factors from the session
  $temp_session_key = $player_token.'_target-robot-omega_prototype';
  if (empty($_SESSION[$session_token]['values'][$temp_session_key])){ return array('field' => 0, 'fusion' => 0); }
  $new_target_robot_omega = $_SESSION[$session_token]['values'][$temp_session_key];

  // Define the arrays to hold all available stars
  $temp_field_stars = array();
  $temp_fusion_stars = array();
  // Loop through and collect the field stars
  foreach ($new_target_robot_omega AS $key => $info){
    $temp_field_stars[] = $info['field'];
  }
  // Loop thourgh and collect the fusion stars
  for ($i = 0; $i < 8; $i += 2){
    list($t1a, $t1b) = explode('-', $temp_field_stars[$i]);
    list($t2a, $t2b) = explode('-', $temp_field_stars[$i + 1]);
    $temp_fusion_token = $t1a.'-'.$t2b;
    $temp_fusion_stars[] = $temp_fusion_token;
  }
  // Loop through field stars and remove unlocked
  foreach ($temp_field_stars AS $key => $token){
    if (!empty($_SESSION[$session_token]['values']['battle_stars'][$token])){
      unset($temp_field_stars[$key]);
    }
  }
  // Loop through fusion stars and remove unlocked
  foreach ($temp_fusion_stars AS $key => $token){
    if (!empty($_SESSION[$session_token]['values']['battle_stars'][$token])){
      unset($temp_fusion_stars[$key]);
    }
  }
  // Count the field stars
  $temp_field_stars = array_values($temp_field_stars);
  $temp_field_stars_count = count($temp_field_stars);
  // Count the fusion stars
  $temp_fusion_stars = array_values($temp_fusion_stars);
  $temp_fusion_stars_count = count($temp_fusion_stars);

  /*
  // DEBUG DEBUG
  die(
    '<pre>$temp_field_stars = '.print_r($temp_field_stars, true).'</pre><br />'.
    '<pre>$temp_fusion_stars = '.print_r($temp_fusion_stars, true).'</pre><br />'
    );
  */

  // Return the star counts
  return array('field' => $temp_field_stars_count, 'fusion' => $temp_fusion_stars_count);
}
// Define a function for checking a robot's prototype experience total
function mmrpg_prototype_robot_experience($player_token, $robot_token){
  // Return the current point total for this robot
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_experience'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_experience']; }
  elseif (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_points'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_points']; }
  else { return 0; }
}
// Define a function for checking a robot's prototype current level
function mmrpg_prototype_robot_level($player_token, $robot_token){
  // Return the current level total for this robot
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_level'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_level']; }
  else { return 1; }
}
// Define a function for checking a robot's prototype current level
function mmrpg_prototype_robot_original_player($player_token, $robot_token){
  // Return the current level total for this robot
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['original_player'])){ return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['original_player']; }
  else { return $player_token; }
}
// Define a function for checking a robot's prototype reward array
function mmrpg_prototype_robot_rewards($player_token = '', $robot_token){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Return the current reward array for this robot
  if (!empty($player_token)){
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])){
      return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
    }
  } elseif (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
      if (!empty($player_info['player_robots'][$robot_token])){
        return $player_info['player_robots'][$robot_token];
      }
    }
  }
  return array();
}
// Define a function for checking a robot's prototype settings array
function mmrpg_prototype_robot_settings($player_token = '', $robot_token){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Return the current setting array for this robot
  if (!empty($player_token)){
    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
      return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token];
    }
  } elseif (!empty($_SESSION[$session_token]['values']['battle_settings'])){
    foreach ($_SESSION[$session_token]['values']['battle_settings'] AS $player_token => $player_info){
      if (!empty($player_info['player_robots'][$robot_token])){
        return $player_info['player_robots'][$robot_token];
      }
    }
  }
  return array();
}
// Define a function for checking a player's robot database array
function mmrpg_prototype_robot_database(){
  // Return the current rewards array for this player
  $session_token = mmrpg_game_token();
  //die('<pre style="color: white;">session_values('.$session_token.')! '.print_r($_SESSION[$session_token]['values'], true).'</pre>');
  if (!empty($_SESSION[$session_token]['values']['robot_database'])){ return $_SESSION[$session_token]['values']['robot_database']; }
  else { return array(); }
}
// Define a function for checking a player's robot favourites array
function mmrpg_prototype_robot_favourites(){
  // Return the current rewards array for this player
  $session_token = mmrpg_game_token();
  if (!empty($_SESSION[$session_token]['values']['robot_favourites'])){ return $_SESSION[$session_token]['values']['robot_favourites']; }
  else { return array(); }
}
// Define a function for checking a player's prototype rewards array
function mmrpg_prototype_robot_favourite($robot_token){
  // Return the current rewards array for this player
  $session_token = mmrpg_game_token();
  if (!isset($_SESSION[$session_token]['values']['robot_favourites'])){ $_SESSION[$session_token]['values']['robot_favourites'] = array(); }
  return in_array($robot_token, $_SESSION[$session_token]['values']['robot_favourites']) ? true : false;
}
// Define a function for checking if a prototype battle has been completed
function mmrpg_prototype_battle_complete($player_token, $battle_token){
  // Check if this battle has been completed and return true is it was
  $session_token = mmrpg_game_token();
  if (!empty($player_token)){
    return isset($_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token] : false;
  } elseif (!empty($_SESSION[$session_token]['values']['battle_complete'])){
    foreach ($_SESSION[$session_token]['values']['battle_complete'] AS $player_token => $player_batles){
      if (isset($player_batles[$battle_token])){ return $player_batles[$battle_token]; }
      else { continue; }
    }
    return false;
  } else {
    return false;
  }
}
// Define a function for checking if a prototype battle has been failured
function mmrpg_prototype_battle_failure($player_token, $battle_token){
  // Check if this battle has been failured and return true is it was
  $session_token = mmrpg_game_token();
  return isset($_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token] : false;
}
// Define a function for checking is a prototype player has been unlocked
function mmrpg_prototype_player_unlocked($player_token){
  // Check if this battle has been completed and return true is it was
  $session_token = mmrpg_game_token();
  return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]) ? true : false;
}
// Define a function for checking is a prototype robot has been unlocked
function mmrpg_prototype_robot_unlocked($player_token = '', $robot_token = ''){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // If the player token was not false, check to see if that particular player has unlocked
  if (empty($robot_token)){ return false; }
  if (!empty($player_token)){
    // Check if this battle has been completed and return true is it was
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])
      && !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
      return true;
    } else {
      return false;
    }
  }
  // Otherwise, loop through all robots and make sure no player has unlocked this robot
  else {
    // Loop through all the player tokens in the battle rewards
    $robot_unlocked = false;
    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
      if (isset($player_info['player_robots'][$robot_token])
        && !empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])
        && !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
        $robot_unlocked = true;
        break;
      }
    }
    return $robot_unlocked;
  }
}
// Define a function for collecting all abilities unlocked by player or all
function mmrpg_prototype_ability_tokens_unlocked($player_token = ''){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Define the temp ability and return arrays
  $unlocked_abilities_tokens = array();
  // If the player token was not false, attempt to collect rewards and settings arrays for that player
  if (!empty($player_token)){
    // Loop through and collect the ability settings and rewards for this player
    $battle_values = array('battle_rewards', 'battle_settings');
    foreach ($battle_values AS $value_token){
      if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_abilities'])){
        foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_abilities'] AS $ability_token => $ability_info){
          if (!empty($ability_token) && !empty($ability_info) && !in_array($ability_token, $unlocked_abilities_tokens)){
            $unlocked_abilities_tokens[] = $ability_token;
          }
        }
      }
    }
  }
  // Otherwise, loop through all abilities and make sure no player has unlocked this ability
  else {
    // Loop through and collect the ability settings and rewards for all players
    foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $player_token => $player_info){
      if (!empty($_SESSION[$session_token]['values']['battle_abilities'])){
        foreach ($_SESSION[$session_token]['values']['battle_abilities'] AS $ability_token => $ability_info){
          if (!empty($ability_token) && !empty($ability_info) && !in_array($ability_token, $unlocked_abilities_tokens)){
            $unlocked_abilities_tokens[] = $ability_token;
          }
        }
      }
    }
  }
  // Return the collected ability tokens
  return $unlocked_abilities_tokens;
}
// Define a function for collecting all robots unlocked by player or all
function mmrpg_prototype_robot_tokens_unlocked($player_token = ''){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Define the temp robot and return arrays
  $unlocked_robots_tokens = array();
  // If the player token was not false, attempt to collect rewards and settings arrays for that player
  if (!empty($player_token)){
    // Loop through and collect the robot settings and rewards for this player
    $battle_values = array('battle_rewards', 'battle_settings');
    foreach ($battle_values AS $value_token){
      if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'])){
        foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'] AS $robot_token => $robot_info){
          if (!empty($robot_token) && !empty($robot_info) && !in_array($robot_token, $unlocked_robots_tokens)){
            $unlocked_robots_tokens[] = $robot_token;
          }
        }
      }
    }
  }
  // Otherwise, loop through all robots and make sure no player has unlocked this robot
  else {
    // Loop through and collect the robot settings and rewards for all players
    $battle_values = array('battle_rewards', 'battle_settings');
    foreach ($battle_values AS $value_token){
      foreach ($_SESSION[$session_token]['values'][$value_token] AS $player_token => $player_info){
        if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'])){
          foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'] AS $robot_token => $robot_info){
            if (!empty($robot_token) && !empty($robot_info) && !in_array($robot_token, $unlocked_robots_tokens)){
              $unlocked_robots_tokens[] = $robot_token;
            }
          }
        }
      }
    }
  }
  // Return the collected robot tokens
  return $unlocked_robots_tokens;
}
// Define a function for collecting all robots unlocked by player or all
function mmrpg_prototype_robots_unlocked_info($player_token = '', $merge_arrays = true){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Define the temp robot and return arrays
  $temp_robots = array();
  $temp_return = array();
  // If the player token was not false, attempt to collect rewards and settings arrays for that player
  if (!empty($player_token)){
    // Loop through and collect the robot settings and rewards for this player
    $battle_values = array('battle_rewards', 'battle_settings');
    foreach ($battle_values AS $value_token){
      if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'])){
        foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'] AS $robot_token => $robot_info){
          if (!empty($robot_token) && !empty($robot_info)){
            $robot_info['robot_player'] = $player_token;
            $temp_return[$value_token][$robot_token] = $robot_info;
          }
        }
      }
    }
  }
  // Otherwise, loop through all robots and make sure no player has unlocked this robot
  else {
    // Loop through and collect the robot settings and rewards for all players
    $battle_values = array('battle_rewards', 'battle_settings');
    foreach ($battle_values AS $value_token){
      foreach ($_SESSION[$session_token]['values'][$value_token] AS $player_token => $player_info){
        if (!empty($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'])){
          foreach ($_SESSION[$session_token]['values'][$value_token][$player_token]['player_robots'] AS $robot_token => $robot_info){
            if (!empty($robot_token) && !empty($robot_info)){
              $robot_info['robot_player'] = $player_token;
              $temp_return[$value_token][$robot_token] = $robot_info;
            }
          }
        }
      }
    }
  }
  // Merge arrays if requested, otherwise just return the two results raw
  if ($merge_arrays == true){
    $temp_robots = array_merge($temp_return['battle_rewards'], $temp_return['battle_settings']);
    return $temp_robots;
  } else {
    return $temp_return;
  }
}
// Define a function for checking if a prototype ability has been unlocked
function mmrpg_prototype_ability_unlocked($player_token = '', $robot_token = '', $ability_token = ''){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // If the combined array exists and we're not being specific, check that first
  if (empty($player_token) && empty($robot_token) && isset($_SESSION[$session_token]['values']['battle_abilities'][$ability_token])){
    // Check if this ability exists in the array, and return true if it does
    return !empty($_SESSION[$session_token]['values']['battle_abilities'][$ability_token]) ? $_SESSION[$session_token]['values']['battle_abilities'][$ability_token] : false;
  }
  // Otherwise, check the old way by looking through individual arrays
  else {
    // If a specific robot token was provided
    if (!empty($robot_token)){
      // Check if this ability has been unlocked by the specified robot and return true if it was
      return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token]) ? true : false;
    } elseif (!empty($player_token)){
      // Check if this ability has been unlocked by the player and return true if it was
      return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities'][$ability_token]) ? true : false;
    } else {
      // Check if this ability has been unlocked by any player and return true if it was
      if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
        foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $ptoken => $pinfo){
          if (!empty($pinfo['player_abilities'][$ability_token])){ return $pinfo['player_abilities'][$ability_token]; }
          else { continue; }
        }
      }
      // Return false if nothing found
      return false;
    }
  }
}
// Define a function for checking if a prototype skin has been unlocked
function mmrpg_prototype_skin_unlocked($robot_token = '', $skin_token = 'alt'){
  // Define the game session helper var
  $session_token = mmrpg_game_token();

  // If the robot token or alt token was not provided, return false
  if (empty($robot_token) || empty($skin_token)){ return false; }

  // Loop through all the robot rewards and check for this alt's presence
  if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $ptoken => $pinfo){
      if (!empty($pinfo['player_robots'])){
        foreach ($pinfo['player_robots'] AS $rtoken => $rinfo){
           if ($rtoken == $robot_token){
             if (!isset($rinfo['robot_skins'])){
               // The skin array does not exist, so let's create it
               $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken]['robot_skins'] = $rinfo['robot_skins'] = array();
             }
             if (!empty($rinfo['robot_skins']) && in_array($skin_token, $rinfo['robot_skins'])){
               // This skin has been unlocked, so let's return true
               return true;
             }
           }
        }
      }
    }
  }

  // If we made it this far, return false
  return false;

}

// Define a function for counting the number of completed prototype battles
function mmrpg_prototype_battles_complete($player_token = '', $unique = true){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Collect the battle complete count from the session if set
  if (!empty($player_token)){
    $temp_battles_complete = isset($_SESSION[$session_token]['values']['battle_complete'][$player_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token] : array();
  } else {
    $temp_battles_complete = array();
    if (isset($_SESSION[$session_token]['values']['battle_complete'])){
      foreach ($_SESSION[$session_token]['values']['battle_complete'] AS $player_token => $battle_array){
        $temp_battles_complete = array_merge($temp_battles_complete, $battle_array);
      }
    }
    $player_token = '';
  }
  //if (empty($player_token)){ die('$player_token = '.$player_token.', $unique = '.($unique ? 1 : 0).',  $count = '.count($temp_battles_complete).'<br />'.print_r($temp_battles_complete, true)); }
  // Check if only unique battles were requested or ALL battles
  if ($unique == true){
   $temp_count = count($temp_battles_complete);
   return $temp_count;
  } else {
   $temp_count = 0;
   foreach ($temp_battles_complete AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
   return $temp_count;
  }
}
// Define a function for counting the number of failured prototype battles
function mmrpg_prototype_battles_failure($player_token, $unique = true){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Collect the battle failure count from the session if set
  $temp_battle_failures = isset($_SESSION[$session_token]['values']['battle_failure'][$player_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token] : array();
  // Check if only unique battles were requested or ALL battles
  if (!empty($unique)){
   $temp_count = count($temp_battle_failures);
   return $temp_count;
  } else {
   $temp_count = 0;
   foreach ($temp_battle_failures AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
   return $temp_count;
  }
}
// Define a function for checking is a prototype player has been unlocked
function mmrpg_prototype_players_unlocked(){
  // Check if this battle has been completed and return true is it was
  $session_token = mmrpg_game_token();
  return isset($_SESSION[$session_token]['values']['battle_rewards']) ? count($_SESSION[$session_token]['values']['battle_rewards']) : 0;
}
// Define a function for checking is a prototype robot has been unlocked
function mmrpg_prototype_robots_unlocked($player_token = ''){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  if (!empty($player_token)){
    // Check if this battle has been completed and return true is it was
    return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots']) : 0;
  } else {
    $robot_counter = 0;
    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
      $robot_counter += isset($player_info['player_robots']) ? count($player_info['player_robots']) : 0;
    }
    return $robot_counter;
  }

}
// Define a function for checking how many items have been unlocked by a player
function mmrpg_prototype_items_unlocked(){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  $temp_counter = 0;
  if (!empty($_SESSION[$session_token]['values']['battle_items'])){
    foreach ($_SESSION[$session_token]['values']['battle_items'] AS $token => $quantity){
      $temp_counter += $quantity;
    }
  }
  return $temp_counter;
}
// Define a function for checking how many cores have been unlocked by a player
function mmrpg_prototype_cores_unlocked(){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  $temp_counter = 0;
  if (!empty($_SESSION[$session_token]['values']['battle_items'])){
    foreach ($_SESSION[$session_token]['values']['battle_items'] AS $token => $quantity){
      if (preg_match('/^item-core-/i', $token)){ $temp_counter += $quantity; }
    }
  }
  return $temp_counter;
}
// Define a function for checking how many screws have been unlocked by a player
function mmrpg_prototype_screws_unlocked($size = ''){
  // If neither screw type has ever been created, return a hard false
  $session_token = mmrpg_game_token();
  if (!isset($_SESSION[$session_token]['values']['battle_items']['item-screw-small'])
    && !isset($_SESSION[$session_token]['values']['battle_items']['item-screw-large'])){
    return false;
  }
  // Define the game session helper var
  $temp_counter = 0;
  if (isset($_SESSION[$session_token]['values']['battle_items']['item-screw-small'])){ $temp_counter += $_SESSION[$session_token]['values']['battle_items']['item-screw-small']; }
  if (isset($_SESSION[$session_token]['values']['battle_items']['item-screw-large'])){ $temp_counter += $_SESSION[$session_token]['values']['battle_items']['item-screw-large']; }
  return $temp_counter;
}
// Define a function for checking how much zenny has been unlocked by all players
function mmrpg_prototype_zenny_unlocked(){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Collect the zenny count and return it
  if (!empty($_SESSION[$session_token]['values']['battle_zenny'])){ return $_SESSION[$session_token]['values']['battle_zenny']; }
  else { return 0; }
}
// Define a function for checking how many database pages have been unlocked by all players
function mmrpg_prototype_database_unlocked(){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  // Collect the database count and return it
  if (!empty($_SESSION[$session_token]['values']['robot_database'])){ return count($_SESSION[$session_token]['values']['robot_database']); }
  else { return 0; }
}
// Define a function for checking is a prototype star has been unlocked
function mmrpg_prototype_star_unlocked($star_token){
  $session_token = mmrpg_game_token();
  if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return false; }
  elseif (empty($_SESSION[$session_token]['values']['battle_stars'][$star_token])){ return false; }
  else { return true; }
}
// Define a function for checking is a prototype star has been unlocked
function mmrpg_prototype_stars_unlocked($player_token = '', $star_kind = ''){
  // Define the game session helper var
  $session_token = mmrpg_game_token();
  if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return 0; }
  else {
    $temp_stars_index = $_SESSION[$session_token]['values']['battle_stars'];
    if (empty($player_token) && empty($star_kind)){ return count($temp_stars_index); }
    foreach ($temp_stars_index AS $key => $info){
      if (!empty($player_token) && $info['star_player'] != $player_token){ unset($temp_stars_index[$key]); }
      elseif (!empty($star_kind) && $info['star_kind'] != $star_kind){ unset($temp_stars_index[$key]); }
    }
    return count($temp_stars_index);
  }
}
// Define a function for checking if a prototype ability has been unlocked
function mmrpg_prototype_abilities_unlocked($player_token = '', $robot_token = ''){
  // Pull in global variables
  //global $mmrpg_index;
  $mmrpg_index_players = $GLOBALS['mmrpg_index']['players'];
  $session_token = mmrpg_game_token();
  // If the combined session array exists, use that to check to unlocked
  if (empty($player_token) && empty($robot_token) && isset($_SESSION[$session_token]['values']['battle_abilities'])){
    // Count the number of abilities in the combined array
    return !empty($_SESSION[$session_token]['values']['battle_abilities']) ? count($_SESSION[$session_token]['values']['battle_abilities']) : 0;
  }
  // Otherwise, we check the separate player arrays to see if unlocked
  else {
    // If a specific robot token was provided
    if (!empty($player_token) && !empty($robot_token)){
      // Check if this battle has been completed and return true is it was
      return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) : 0;
    } elseif (!empty($player_token)){
      // Check if this ability has been unlocked by the player and return true if it was
      return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) : 0;
    } else {
      // Define the ability counter and token tracker
      $ability_tokens = array();
      foreach ($mmrpg_index_players AS $temp_player_token => $temp_player_info){
        $temp_player_abilities = isset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_abilities']) ? $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_abilities'] : array();
        foreach ($temp_player_abilities AS $temp_ability_token => $temp_ability_info){
          if (!in_array($temp_ability_token, $ability_tokens)){
            $ability_tokens[] = $temp_ability_token;
          }
        }
      }
      // Return the total amount of ability tokens pulled
      return !empty($ability_tokens) ? count($ability_tokens) : 0;
    }
  }
}


// Define a function for displaying prototype battle option markup
function mmrpg_prototype_options_markup(&$battle_options, $player_token){
  // Refence the global config and index objects for easy access
  global $mmrpg_index, $DB;
  $mmrpg_index_fields = mmrpg_field::get_index();
  // Define the variable to collect option markup
  $this_markup = '';
  // Require the actual code file
  require('prototype_options-markup.php');
  // Return the generated markup
  return $this_markup;
}

// Define a function for generating option message markup
function mmrpg_prototype_option_message_markup($player_token, $subject, $lineone, $linetwo, $sprites = ''){
  $temp_optiontext = '<span class="multi"><span class="maintext">'.$subject.'</span><span class="subtext">'.$lineone.'</span><span class="subtext2">'.$linetwo.'</span></span>';
  return '<a class="option option_1x4 option_this-'.$player_token.'-select option_message "><div class="chrome"><div class="inset"><label class="'.(!empty($sprites) ? 'has_image' : '').'">'.$sprites.$temp_optiontext.'</label></div></div></a>'."\n";
}


/*
 * MISSION GENERATION
 */


// Define a function for generating an ability set for a given robot
require('prototype_generate-abilities.php');

// Define a function for generating the STARTER missions
require('prototype_mission-starter.php');

// Define a function for generating the SINGLES missions
require('prototype_mission-single.php');

// Define a function for generating the DOUBLES missions
require('prototype_mission-double.php');

// Define a function for generating the PLAYER missions
require('prototype_mission-player.php');

// Define a function for generating the BONUS missions
require('prototype_mission-bonus.php');

// Define a function for generating the FORTRESS missions
require('prototype_mission-fortress.php');

// Define a function for generating robot SELECT markup
require('prototype_robot-select-markup.php');

// Define a function for sorting the omega player robots
function mmrpg_prototype_sort_player_robots($info1, $info2){
  $info1_robot_level = $info1['robot_level'];
  $info2_robot_level = $info2['robot_level'];
  $info1_robot_favourite = isset($info1['values']['flag_favourite']) ? $info1['values']['flag_favourite'] : 0;
  $info2_robot_favourite = isset($info2['values']['flag_favourite']) ? $info2['values']['flag_favourite'] : 0;
  if ($info1_robot_favourite < $info2_robot_favourite){ return 1; }
  elseif ($info1_robot_favourite > $info2_robot_favourite){ return -1; }
  elseif ($info1_robot_level < $info2_robot_level){ return 1; }
  elseif ($info1_robot_level > $info2_robot_level){ return -1; }
  else { return 0; }
}

// Define a function to sort prototype robots based on their current level / experience points
function mmrpg_prototype_sort_robots_experience($info1, $info2){
  global $this_prototype_data;
  $info1_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info1['robot_token']);
  $info1_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info1['robot_token']);
  $info2_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info2['robot_token']);
  $info2_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info2['robot_token']);
  if ($info1_robot_level < $info2_robot_level){ return 1; }
  elseif ($info1_robot_level > $info2_robot_level){ return -1; }
  elseif ($info1_robot_experience < $info2_robot_experience){ return 1; }
  elseif ($info1_robot_experience > $info2_robot_experience){ return -1; }
  else { return 0; }
}


// Define a function to sort prototype robots based on their current level / experience points
function mmrpg_prototype_sort_robots_position($info1, $info2){
  global $this_prototype_data;
  static $this_robot_favourites;
  if (empty($this_robot_favourites)){ $this_robot_favourites = mmrpg_prototype_robot_favourites(); }
  $temp_player_settings = mmrpg_prototype_player_settings($this_prototype_data['this_player_token']);
  $info1_robot_position = array_search($info1['robot_token'], array_keys($temp_player_settings['player_robots']));
  $info2_robot_position = array_search($info2['robot_token'], array_keys($temp_player_settings['player_robots']));
  $info1_robot_favourite = in_array($info1['robot_token'], $this_robot_favourites) ? 1 : 0;
  $info2_robot_favourite = in_array($info2['robot_token'], $this_robot_favourites) ? 1 : 0;
  if ($info1_robot_favourite < $info2_robot_favourite){ return 1; }
  elseif ($info1_robot_favourite > $info2_robot_favourite){ return -1; }
  elseif ($info1_robot_position < $info2_robot_position){ return -1; }
  elseif ($info1_robot_position > $info2_robot_position){ return 1; }
  else { return 0; }
}

// Define the field star image function for use in other parts of the game
function mmrpg_prototype_star_image($type){
  static $type_order = array('none', 'copy', 'crystal', 'cutter', 'earth',
    'electric', 'explode', 'flame', 'freeze', 'impact',
    'laser', 'missile', 'nature', 'shadow', 'shield',
    'space', 'swift', 'time', 'water', 'wind');
  $type_sheet = 1;
  $type_frame = array_search($type, $type_order);
  if ($type_frame >= 10){
    $type_sheet = 2;
    $type_frame = $type_frame - 10;
  } elseif ($type_frame < 0){
    $type_sheet = 1;
    $type_frame = 0;
  }
  $temp_array = array('sheet' => $type_sheet, 'frame' => $type_frame);
  return $temp_array;
}

// Define a function for pulling the leaderboard players index
function mmrpg_prototype_leaderboard_index(){
  global $DB;
  // Check to see if the leaderboard index has already been pulled or not
  if (!empty($DB->INDEX['LEADERBOARD']['index'])){
    $this_leaderboard_index = json_decode($DB->INDEX['LEADERBOARD']['index'], true);
  } else {
    // Define the array for pulling all the leaderboard data
    $temp_leaderboard_query = 'SELECT
      mmrpg_users.user_id,
      mmrpg_users.user_name,
      mmrpg_users.user_name_clean,
      mmrpg_users.user_name_public,
      mmrpg_users.user_date_accessed,
      mmrpg_leaderboard.board_points
      FROM mmrpg_users
      LEFT JOIN mmrpg_leaderboard ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
      WHERE mmrpg_leaderboard.board_points > 0 ORDER BY mmrpg_leaderboard.board_points DESC
      ';
    // Query the database and collect the array list of all online players
    $this_leaderboard_index = $DB->get_array_list($temp_leaderboard_query);
    // Update the database index cache
    $DB->INDEX['LEADERBOARD']['index'] = json_encode($this_leaderboard_index);
  }
  // Return the collected leaderboard index
  return $this_leaderboard_index;
}

// Define a function for collecting the requested player's board ranking
function mmrpg_prototype_leaderboard_rank($user_id){
  // Query the database and collect the array list of all non-bogus players
  $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
  $this_leaderboard_points = 0;
  $this_leaderboard_list = array();
  foreach ($this_leaderboard_index AS $array){
    $this_leaderboard_list[] = $array['board_points'];
    if ($array['user_id'] == $user_id){ $this_leaderboard_points = $array['board_points']; }
  }
  $this_leaderboard_list = array_unique($this_leaderboard_list);
  sort($this_leaderboard_list);
  $this_leaderboard_list = array_reverse($this_leaderboard_list);

  // Now collect the leaderboard rank based on position
  if (in_array($this_leaderboard_points, $this_leaderboard_list)){
    $this_leaderboard_rank = array_search($this_leaderboard_points, $this_leaderboard_list);
    $this_leaderboard_rank = $this_leaderboard_rank !== false ? $this_leaderboard_rank + 1 : 0;
  } else {
    $this_leaderboard_rank = 0;
  }
  return $this_leaderboard_rank;

}

// Define a function for pulling the leaderboard online player
function mmrpg_prototype_leaderboard_online(){
  global $DB;
  // Check to see if the leaderboard online has already been pulled or not
  if (!empty($DB->INDEX['LEADERBOARD']['online'])){
    $this_leaderboard_online = json_decode($DB->INDEX['LEADERBOARD']['online'], true);
  } else {
    // Collect the leaderboard index for ranking
    $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
    // Generate the points index and then break it down to unique for ranks
    $this_points_index = array();
    if (!empty($this_leaderboard_index)){
      foreach ($this_leaderboard_index AS $info){
        $this_points_index[] = $info['board_points'];
      }
    }
    $this_points_index = array_unique($this_points_index);
    // Define the vars for finding the online players
    $this_time = time();
    $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
    // Loop through the collected index and pull online players
    $this_leaderboard_online = array();
    if (!empty($this_leaderboard_index)){
      foreach ($this_leaderboard_index AS $key => $board){
        if (!empty($board['user_date_accessed']) && (($this_time - $board['user_date_accessed']) <= $this_online_timeout)){
          $temp_userid = !empty($board['user_id']) ? $board['user_id'] : 0;
          $temp_usertoken = $board['user_name_clean'];
          $temp_username = !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'];
          $temp_username = htmlentities($temp_username, ENT_QUOTES, 'UTF-8', true);
          $temp_points = !empty($board['board_points']) ? $board['board_points'] : 0;
          $temp_place = array_search($board['board_points'], $this_points_index) + 1;
          $this_leaderboard_online[] = array('id' => $temp_userid, 'name' => $temp_username, 'token' => $temp_usertoken, 'points' => $temp_points, 'place' => $temp_place);
        }
      }
    }
    // Update the database index cache
    $DB->INDEX['LEADERBOARD']['online'] = json_encode($this_leaderboard_online);
  }
  // Return the collected online players if any
  return $this_leaderboard_online;
}

// Define a function for pulling the leaderboard custom player options
function mmrpg_prototype_leaderboard_custom($player_token = '', $this_userid = 0){
  global $DB;
  // Check to see if the leaderboard online has already been pulled or not
  if (!empty($DB->INDEX['LEADERBOARD']['custom'])){
    $this_leaderboard_custom = json_decode($DB->INDEX['LEADERBOARD']['custom'], true);
  } else {
    // Collect the leaderboard index for ranking
    $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
    if (!empty($player_token)){
      $this_custom_array = !empty($_SESSION['GAME']['values']['battle_targets'][$player_token]) ? $_SESSION['GAME']['values']['battle_targets'][$player_token] : array();
    } else {
      $this_custom_array = array();
      if (!empty($_SESSION['GAME']['values']['battle_targets'])){
        foreach ($_SESSION['GAME']['values']['battle_targets'] AS $player_token => $player_custom_array){
          $this_custom_array = array_merge($this_custom_array, $player_custom_array);
        }
      }
    }

    // Generate the points index and then break it down to unique for ranks
    $this_points_index = array();
    foreach ($this_leaderboard_index AS $info){ $this_points_index[] = $info['board_points']; }
    $this_points_index = array_unique($this_points_index);

    // Loop through the collected index and pull online players
    $this_leaderboard_custom = array();
    if (!empty($this_leaderboard_index)){
      foreach ($this_leaderboard_index AS $key => $board){
        if ($board['user_id'] != $this_userid && !empty($board['user_name_clean']) && in_array($board['user_name_clean'], $this_custom_array)){
          $temp_userid = !empty($board['user_id']) ? $board['user_id'] : 0;
          $temp_usertoken = $board['user_name_clean'];
          $temp_username = !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'];
          $temp_username = htmlentities($temp_username, ENT_QUOTES, 'UTF-8', true);
          $temp_points = !empty($board['board_points']) ? $board['board_points'] : 0;
          $temp_place = array_search($board['board_points'], $this_points_index) + 1;
          $this_leaderboard_custom[] = array('id' => $temp_userid, 'name' => $temp_username, 'token' => $temp_usertoken, 'points' => $temp_points, 'place' => $temp_place);
        }
      }
    }
    // Update the database index cache
    $DB->INDEX['LEADERBOARD']['custom'] = json_encode($this_leaderboard_custom);
  }
  // Return the collected online players if any
  return $this_leaderboard_custom;
}

// Define a function for pulling the leaderboard rival targets
function mmrpg_prototype_leaderboard_rivals($this_leaderboard_index, $this_userid, $offset = 10){
  global $DB;

  // Collect the position of the current player in the leaderboard list
  $this_leaderboard_index_position = 0;
  foreach ($this_leaderboard_index AS $key => $array){
    if ($array['user_id'] == $this_userid){
      $this_leaderboard_index_position = $key;
      break;
    }
  }

  // Collect the players before and after the current user for matchmaking
  $max_player_key = $this_leaderboard_index_position - $offset;
  $min_player_key = $this_leaderboard_index_position + $offset;
  if ($max_player_key < 0){ $min_player_key -= $max_player_key; $max_player_key = 0; }
  if ($min_player_key > count($this_leaderboard_index)){ $max_player_key -= $min_player_key - count($this_leaderboard_index); }
  $this_leaderboard_targets = $this_leaderboard_index;
  unset($this_leaderboard_targets[$this_leaderboard_index_position]);
  $this_leaderboard_targets = array_slice($this_leaderboard_targets, $max_player_key, $min_player_key);

  // Return the collected rival players
  return $this_leaderboard_targets;

}

// Define a function for pulling the leaderboard targets
function mmrpg_prototype_leaderboard_targets($this_userid, $player_limit = 12, $player_sort = '', $player_campaign = ''){
  global $DB;

  // Check to see if the leaderboard targets have already been pulled or not
  if (!empty($DB->INDEX['LEADERBOARD']['targets'])){

    $this_leaderboard_target_players = $DB->INDEX['LEADERBOARD']['targets'];

  } else {

    // Collect the leaderboard index and online players for ranking
    $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
    $this_leaderboard_targets = array();
    $this_leaderboard_targets_ids = array();
    $this_leaderboard_targets['custom'] = mmrpg_prototype_leaderboard_custom($player_campaign, $this_userid);
    $this_leaderboard_targets['online'] = mmrpg_prototype_leaderboard_online();
    $this_leaderboard_targets['rival'] = mmrpg_prototype_leaderboard_rivals($this_leaderboard_index, $this_userid, 10);
    $this_leaderboard_targets_ids['custom'] = array();
    $this_leaderboard_targets_ids['online'] = array();
    $this_leaderboard_targets_ids['rival'] = array();
    if (!empty($this_leaderboard_targets['custom'])){ shuffle($this_leaderboard_targets['custom']); }
    if (!empty($this_leaderboard_targets['online'])){ shuffle($this_leaderboard_targets['online']); }
    if (!empty($this_leaderboard_targets['rival'])){ shuffle($this_leaderboard_targets['rival']); }

    //die('<pre>$this_leaderboard_targets(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_targets, true).'</pre>');

    //$this_leaderboard_include_players = array_merge($this_leaderboard_targets['online'], $this_leaderboard_targets['rival']);
    //$this_leaderboard_include_players = array_slice($this_leaderboard_include_players, 0, $player_limit);
    //shuffle($this_leaderboard_include_players);

    // Generate the custom username tokens for adding to the condition list
    $temp_include_raw = array();
    $temp_include_userids = array();
    $temp_include_usernames = array();
    $temp_include_usernames_count = 0;
    $temp_include_usernames_string = array();

    // Add the include data to the raw array
    if (!empty($this_leaderboard_targets)){
      foreach ($this_leaderboard_targets AS $kind => $players){
        if (!empty($players)){
          if (!isset($this_leaderboard_targets_ids[$kind])){ $this_leaderboard_targets_ids[$kind] = array(); }
          foreach ($players AS $key => $info){
            $id = isset($info['user_id']) ? $info['user_id'] : $info['id'];
            $this_leaderboard_targets_ids[$kind][] = $id;
            if (!isset($temp_include_raw[$id])){
              $temp_include_raw[$id] = $info;
            }
            else { continue; }
          }
        }

      }
    }

    //die('<pre>$this_leaderboard_targets_ids(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_targets_ids, true).'</pre>');

    // Re-key the array to prevent looping errors
    $temp_include_raw = array_values($temp_include_raw);

    // Loop thrugh the raw array and collect filter variables
    if (!empty($temp_include_raw)){
      foreach ($temp_include_raw AS $info){
        if (isset($info['id']) && $info['id'] != $this_userid){
          $temp_include_usernames[] = $info['token'];
          $temp_include_userids[] = $info['id'];
        } elseif (isset($info['user_id']) && $info['user_id'] != $this_userid){
          $temp_include_usernames[] = $info['user_name_clean'];
          $temp_include_userids[] = $info['user_id'];
        }
      }
      $temp_include_usernames_count = count($temp_include_usernames);
      if (!empty($temp_include_usernames)){
        foreach ($temp_include_usernames AS $token){ $temp_include_usernames_string[] = "'{$token}'"; }
        $temp_include_usernames_string = implode(',', $temp_include_usernames_string);
      } else {
        $temp_include_usernames_string = '';
      }
    } else {
      $temp_include_usernames_string = '';
    }

    //die('<pre>$temp_include_raw(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($temp_include_raw, true).'</pre>');

    // Define the array for pulling all the leaderboard data
    $temp_leaderboard_query = 'SELECT
        mmrpg_leaderboard.user_id,
        mmrpg_leaderboard.board_points,
        mmrpg_users.user_name,
        mmrpg_users.user_name_clean,
        mmrpg_users.user_name_public,
        mmrpg_users.user_gender,
        mmrpg_saves.save_values_battle_rewards AS player_rewards,
        mmrpg_saves.save_values_battle_settings AS player_settings,
        mmrpg_saves.save_values AS player_values,
        mmrpg_saves.save_counters AS player_counters
        FROM mmrpg_leaderboard
        LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
        LEFT JOIN mmrpg_saves ON mmrpg_users.user_id = mmrpg_saves.user_id
        WHERE board_points > 0
        AND mmrpg_leaderboard.user_id != '.$this_userid.'
        '.(!empty($temp_include_usernames_string) ? 'AND mmrpg_users.user_name_clean IN ('.$temp_include_usernames_string.') ' : '').'
        ORDER BY board_points DESC
      ';
    //AND board_points >= '.$this_player_points_min.' AND board_points <= '.$this_player_points_max.'
    //'.(!empty($temp_online_usernames_string) ? ' FIELD(user_name_clean, '.$temp_online_usernames_string.') DESC, ' : '').'
    //LIMIT '.$player_limit.'

    // Query the database and collect the array list of all online players
    //die('<pre>$temp_leaderboard_query(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($temp_leaderboard_query, true).'</pre>');
    $this_leaderboard_target_players = $DB->get_array_list($temp_leaderboard_query);

    //die('<pre>$this_leaderboard_target_players(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_target_players, true).'</pre>');

    // Sort the target players based on position in userid array
    usort($this_leaderboard_target_players, function($u1, $u2) use ($temp_include_userids) {
      $id1 = isset($u1['user_id']) ? $u1['user_id'] : $u1['id'];
      $id2 = isset($u2['user_id']) ? $u2['user_id'] : $u2['id'];
      $pos1 = array_search($id1, $temp_include_userids);
      $pos2 = array_search($id2, $temp_include_userids);
      if ($pos1 > $pos2){ return 1; }
      elseif ($pos1 < $pos2){ return -1; }
      else { return 0; }
      });

    //die('<pre>$this_leaderboard_target_players(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_target_players, true).'</pre>');

    //die('<pre>(this:'.$player_campaign.'/target:'.$player_sort.')'."\n\n".'$temp_leaderboard_query = '.print_r($temp_leaderboard_query, true).''."\n\n".'$this_leaderboard_target_players = '.print_r($this_leaderboard_target_players, true).'</pre>');

    // Loop through and decode any fields that require it
    if (!empty($this_leaderboard_target_players)){
      foreach ($this_leaderboard_target_players AS $key => $player){
        $player['player_rewards'] = !empty($player['player_rewards']) ? json_decode($player['player_rewards'], true) : array();
        $player['player_settings'] = !empty($player['player_settings']) ? json_decode($player['player_settings'], true) : array();
        $player['values'] = !empty($player['player_values']) ? json_decode($player['player_values'], true) : array();
        $player['counters'] = !empty($player['player_counters']) ? json_decode($player['player_counters'], true) : array();
        unset($player['player_values']);
        unset($player['player_counters']);
        $player['player_favourites'] = !empty($player['values']['robot_favourites']) ? $player['values']['robot_favourites'] : array();
        $player['player_starforce'] = !empty($player['values']['star_force']) ? $player['values']['star_force'] : array();
        if (!empty($player_sort)){ $player['counters']['player_robots_count'] = !empty($player['player_rewards'][$player_sort]['player_robots']) ? count($player['player_rewards'][$player_sort]['player_robots']) : 0; }
        $player['values']['flag_custom'] = in_array($player['user_id'], $this_leaderboard_targets_ids['custom']) ? 1 : 0;
        $player['values']['flag_online'] = in_array($player['user_id'], $this_leaderboard_targets_ids['online']) ? 1 : 0;
        $player['values']['flag_rival'] = in_array($player['user_id'], $this_leaderboard_targets_ids['rival']) ? 1 : 0;
        $this_leaderboard_target_players[$key] = $player;
      }
    }

    // Update the database index cache
    //if (!empty($player_sort)){ uasort($this_leaderboard_target_players, 'mmrpg_prototype_leaderboard_targets_sort'); }
    $DB->INDEX['LEADERBOARD']['targets'] = $this_leaderboard_target_players;
    //die($temp_leaderboard_query);

  }
  // Return the collected online players if any
  return $this_leaderboard_target_players;
}


// Define a function for sorting the target leaderboard players
function mmrpg_prototype_leaderboard_targets_sort($player1, $player2){
  if ($player1['values']['flag_online'] < $player2['values']['flag_online']){ return 1; }
  elseif ($player1['values']['flag_online'] > $player2['values']['flag_online']){ return -1; }
  elseif ($player1['counters']['battle_points'] < $player2['counters']['battle_points']){ return -1; }
  elseif ($player1['counters']['battle_points'] > $player2['counters']['battle_points']){ return 1; }
  elseif ($player1['counters']['player_robots_count'] < $player2['counters']['player_robots_count']){ return -1; }
  elseif ($player1['counters']['player_robots_count'] > $player2['counters']['player_robots_count']){ return 1; }
  else { return 0; }
}
// Define a function for sorting the target leaderboard players
function mmrpg_prototype_leaderboard_targets_sort_online($player1, $player2){
  if ($player1['values']['flag_online'] < $player2['values']['flag_online']){ return 1; }
  elseif ($player1['values']['flag_online'] > $player2['values']['flag_online']){ return -1; }
  else { return 0; }
}



// Define a function for determining a player's battle music
function mmrpg_prototype_get_player_music($player_token, $session_token = 'GAME'){
  global $mmrpg_index, $DB;

  $temp_session_key = $player_token.'_target-robot-omega_prototype';
  $temp_robot_omega = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
  $temp_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

  // Count the games representaed and order by count
  $temp_game_counters = array();
  foreach ($temp_robot_omega AS $omega){
    if (empty($omega['robot'])){ continue; }
    $index = mmrpg_robot::parse_index_info($temp_robot_index[$omega['robot']]);
    $game = strtolower($index['robot_game']);
    if (!isset($temp_game_counters[$game])){ $temp_game_counters[$game] = 0; }
    $temp_game_counters[$game] += 1;
  }

  //die('<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>');

  if (empty($temp_game_counters)){
    if ($player_token == 'dr-light'){ $temp_game_counters['mm01'] = 1; }
    if ($player_token == 'dr-wily'){ $temp_game_counters['mm02'] = 1; }
    if ($player_token == 'dr-cossack'){ $temp_game_counters['mm04'] = 1; }
  }

  asort($temp_game_counters, SORT_NUMERIC);

  //echo("\n".'-------'.$player_token.'-------'."\n".'<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>'."\n");

  // Get the last element in the array
  end($temp_game_counters);
  $most_key = key($temp_game_counters);
  $most_count = $temp_game_counters[$most_key];

  //echo("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

  $most_options = array($most_key);
  foreach ($temp_game_counters AS $key => $count){ if ($key != $most_key && $count >= $most_count){ $most_options[] = $key; } }
  if (count($most_options) > 1){ $most_key = $most_options[array_rand($most_options, 1)];  }

  //echo("\n".'<pre>$most_options = '.print_r($most_options, true).'</pre>'."\n");

  //echo("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

  return $most_key;

}

// Define a function for determining a player's battle music
function mmrpg_prototype_get_player_mission_music($player_token, $session_token = 'GAME'){
  $most_key = mmrpg_prototype_get_player_music($player_token, $session_token);
  return 'stage-select-'.$most_key;
}


// Define a function for determining a player's boss music
function mmrpg_prototype_get_player_boss_music($player_token, $session_token = 'GAME'){
  $most_key = mmrpg_prototype_get_player_music($player_token, $session_token);
  return 'boss-theme-'.$most_key;
}

// Define a function for checking the battle's prototype points total
function mmrpg_prototype_database_summoned($robot_token = ''){
  // Define static variables amd populate if necessary
  static $this_count_array;
  // Return the current point total for thisgame
  $session_token = mmrpg_game_token();
  // Check if the array is empty and populate if not
  if (empty($this_count_array)){
    // Define the array to hold all the summon counts
    $this_count_array = array();
    // If the robot database array is not empty, loop through it
    if (!empty($_SESSION[$session_token]['values']['robot_database'])){
      foreach ($_SESSION[$session_token]['values']['robot_database'] AS $token => $info){
        if (!empty($info['robot_summoned'])){ $this_count_array[$token] = $info['robot_summoned']; }
      }
    }
  }
  // If the robot token was not empty
  if (!empty($robot_token)){
    // If the array exists, return the count
    if (!empty($this_count_array[$robot_token])){ return $this_count_array[$robot_token]; }
    // Otherwise, return zero
    else { return 0; }
  }
  // Otherwise, return the full array
  else {
    // Return the count array
    return $this_count_array;
  }
}


// Define a function for collecting robot sprite markup
function mmrpg_prototype_get_player_robot_sprites($player_token, $session_token = 'GAME'){
  global $mmrpg_index, $DB;
  $temp_offset_x = 14;
  $temp_offset_z = 50;
  $temp_offset_y = -2;
  $temp_offset_opacity = 0.75;
  $text_sprites_markup = '';
  $temp_player_robots = $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'];
  $temp_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
  foreach ($temp_player_robots AS $token => $info){
    $index = mmrpg_robot::parse_index_info($temp_robot_index[$token]);
    $info = array_merge($index, $info);
    if (mmrpg_prototype_robot_unlocked($player_token, $token)){
      $temp_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
      $temp_size_text = $temp_size.'x'.$temp_size;
      $temp_offset_x += $temp_size > 40 ? 0 : 20;
      $temp_offset_y = $temp_size > 40 ? -42 : -2;
      $temp_offset_z -= 1;
      $temp_offset_opacity -= 0.05;
      if ($temp_offset_opacity <= 0){ $temp_offset_opacity = 0; break; }
      //$text_sprites_markup .= '<span class="sprite sprite_nobanner sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(images/robots/'.(!empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token']).'/sprite_right_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_offset_y.'px; right: '.$temp_offset_x.'px; z-index: '.$temp_offset_z.'; opacity: '.$temp_offset_opacity.'; ">'.$info['robot_name'].'</span>';
      //$text_sprites_markup .= '<span class="sprite sprite_nobanner sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(images/robots/'.(!empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token']).'/sprite_right_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_offset_y.'px; right: '.$temp_offset_x.'px;">'.$info['robot_name'].'</span>';
      $text_sprites_markup .= '<span class="sprite sprite_nobanner sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(i/r/'.(!empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token']).'/sr'.$temp_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_offset_y.'px; right: '.$temp_offset_x.'px;">'.$info['robot_name'].'</span>';
      if ($temp_size > 40){ $temp_offset_x += 20;  }
    }
  }
  return $text_sprites_markup;
}


// Define functions for printing out array data in an easier to read format
function mmrpg_print_r($variable, $base = '/'){
  ob_start();
  echo '<pre>';
  echo mmrpg_print_r_recursive($variable, $base);
  echo '</pre>';
  return ob_get_clean();
}
function mmrpg_print_r_recursive($variable, $base = '/'){
  //if (is_array($variable)){ echo '<br /><br />'.$base;  }
  if (is_array($variable) && count($variable) > 1){ echo '<br /><br />'.$base;  }
  elseif (is_array($variable) && count($variable) == 1){ echo '<br />'.$base;  }
  else { echo $base; }
  if (is_bool($variable)){ echo ($variable == true ? 'true' : 'false').'<br />'; }
  elseif (!is_numeric($variable) && empty($variable)){ echo (is_array($variable) ? '=-' : '-').'<br />'; }
  elseif (!is_array($variable)){ echo $variable.'<br />'; }
  elseif (is_array($variable)){
    echo '=<br />';
    foreach ($variable AS $key => $value){
      echo mmrpg_print_r_recursive($value, $base.$key.'/');
    }
    if (is_array($variable) && count($variable) > 1){ echo '<br />';  }
  }
}

/*
// Define a function for encoding a robot's reward and setting data
function mmrpg_prototype_encode_robot_data($robot_data){
  $robot_data = json_encode($robot_data);
  return $robot_data;
}
// Define a function for decoding a robot's reward and setting data
function mmrpg_prototype_decode_robot_data($robot_data){
  if (!strstr('////', $robot_data)){ return false; }
  list($robot_data, $salt_data) = explode('////', $robot_data);
  $robot_data = base64_decode($robot_data);
  $salt_data = base64_decode($salt_data);
  if ($salt_data != MMRPG_SETTINGS_ROBOT_ENCODING_SALT){ return false; }
  $robot_data = json_decode($robot_data, true);
  return $robot_data;
}
*/

/*
// If possible, attempt to save the game to the session
if (!empty($this_save_filepath)){
  // Save the game session
  mmrpg_save_game_session($this_save_filepath);
}
*/
?>