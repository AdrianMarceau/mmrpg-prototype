<?
// Define the temporary debug variable
define('EDIT_ROBOT_UPDATES_DEBUG', false);

// Only print out temporary style info if we're in debug mode
if (EDIT_ROBOT_UPDATES_DEBUG){
  ?>
  <style type="text/css">
  table {
    border-collapse: collapse;
    border-spacing: 4px;
    width: 100%;
    table-layout: fixed;
    border: 1px solid rgba(0, 0, 0, 0.2);
    margin: 4px 0 8px;
    padding: 4px;
  }
  table table {
    border-spacing: 0;
    border: 0 none transparent;
    padding: 0;
  }
  table th,
  table td {
    text-align: left;
    border: 0 none transparent;
    padding: 0 6px 3px 0;
  }
  table table td {
    border: 1px dotted rgba(0, 0, 0, 0.1);
    padding: 3px 6px 3px 0;
  }
  .robot_block {
    padding: 0 0 18px;
    border-bottom: 1px dotted rgba(0, 0, 0, 0.1);
    margin: 0 auto 18px;
  }
  .robot_title {
    display: block;
    padding: 2px 6px;
    margin: 0 auto 9px;
    color: #FFFFFF;
    background-color: rgba(0, 0, 0, 0.6);
  }
  </style>
  <?
}

$temp_battle_rewards = &$_SESSION[$session_token]['values']['battle_rewards'];
$temp_battle_settings = &$_SESSION[$session_token]['values']['battle_settings'];
$temp_stat_tokens = array('energy', 'attack', 'defense', 'speed');

//$temp_battle_database = &$_SESSION[$session_token]['values']['robot_database'];
if (!empty($temp_battle_rewards) || !empty($temp_battle_settings)){

  // Define the player colours for debug purposes
  $temp_player_colours = array('dr-light' => array(0, 0, 200), 'dr-wily' => array(200, 0, 0), 'dr-cossack' => array(100, 0, 100));
  // Collect a robot array to loop through, rewards or settings
  $temp_player_array = !empty($temp_battle_rewards) ? $temp_battle_rewards : $temp_battle_settings;
  // Define the array to hold the total overflow for all doctors
  $temp_total_overflow_value = 0;


  // -- UPDATE LOOP FUNCTIONS -- //

  // Define the first loop function for memory saving
  function edit_robot_update_loop_one(
    &$mmrpg_database_robots,
    &$temp_battle_rewards, &$temp_battle_settings,
    &$temp_player_array, &$temp_total_overflow_value,
    $temp_player_colours, $temp_stat_tokens,
    $temp_player_token, $temp_player_info,
    $temp_robot_token, $temp_robot_info,
    $temp_phase_loop
    ){
    // Collect the robot index, rewards, and settings
    $temp_robot_index = !empty($mmrpg_database_robots[$temp_robot_token]) ? $mmrpg_database_robots[$temp_robot_token] : $temp_robot_info;
    $temp_robot_rewards = !empty($temp_battle_rewards[$temp_player_token]['player_robots'][$temp_robot_token]) ? $temp_battle_rewards[$temp_player_token]['player_robots'][$temp_robot_token] : array();
    $temp_robot_settings = !empty($temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token]) ? $temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token] : array();
    // Collect the robot's level and experience
    $temp_robot_info['robot_level'] = !empty($temp_robot_rewards['robot_level']) ? $temp_robot_rewards['robot_level'] : 1;
    $temp_robot_info['robot_experience'] = !empty($temp_robot_rewards['robot_experience']) ? $temp_robot_rewards['robot_experience'] : 0;
    // Collect the original play and prepare the transfer abilities to them
    $temp_robot_info['original_player'] = !empty($temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token]['original_player']) ? $temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token]['original_player'] : $temp_player_token;

    // Check the robots's stats and collect any overflow from the max
    $temp_stat_overflow = 0;
    $temp_stat_total = 0;
    $temp_stat_values = array();
    foreach ($temp_stat_tokens AS $stat_token){
     // Collect the stats for this level
     $temp_stat_values[$stat_token]['base'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($temp_robot_index['robot_'.$stat_token], $temp_robot_info['robot_level']);
     $temp_stat_values[$stat_token]['reward'] = !empty($temp_robot_rewards['robot_'.$stat_token]) ? $temp_robot_rewards['robot_'.$stat_token] : 0;
     $temp_stat_values[$stat_token]['total'] = $temp_stat_values[$stat_token]['base'] + $temp_stat_values[$stat_token]['reward'];
     $temp_stat_values[$stat_token]['max'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($temp_robot_index['robot_'.$stat_token], $temp_robot_info['robot_level']);
     $temp_stat_values[$stat_token]['overflow'] = $temp_stat_values[$stat_token]['total'] > $temp_stat_values[$stat_token]['max'] ? $temp_stat_values[$stat_token]['total'] - $temp_stat_values[$stat_token]['max'] : 0;
     $temp_stat_overflow += $temp_stat_values[$stat_token]['overflow'];
     $temp_stat_total += $temp_stat_values[$stat_token]['total'];
    }
    // Update the array with collected stat values
    $temp_robot_info['robot_stats'] = $temp_stat_values;
    $temp_robot_info['robot_stats_overflow'] = $temp_stat_overflow;
    $temp_robot_info['robot_stats_total'] = $temp_stat_total;

    // Print out the robot array for debug purposes
    if (EDIT_ROBOT_UPDATES_DEBUG){
      echo '<div class="robot_block">';
      echo '<strong class="robot_title">'.$temp_robot_token.' (phase-'.$temp_phase_loop.') ('.$temp_player_token.') '.($temp_player_token != $temp_robot_info['original_player'] ? '[via-'.$temp_robot_info['original_player'].']' : '').'</strong>';
      echo array2table(array('robot_level' => $temp_robot_info['robot_level']), true);
      echo array2table(array('robot_stats' => $temp_robot_info['robot_stats']), true);
      echo array2table(array('robot_stats_total' => $temp_robot_info['robot_stats_total']), true);
      echo array2table(array('robot_stats_overflow' => $temp_robot_info['robot_stats_overflow']), true);
      echo '</div>';
    }

    // Update the parent array with these collected details
    $temp_player_array[$temp_player_token]['player_robots'][$temp_robot_token] = $temp_robot_info;
    $temp_total_overflow_value += $temp_robot_info['robot_stats_overflow'];
    }


  // -- PROCESS PHASE LOOP 1 -- //

  // Define the phase loop for debug purposes
  $temp_phase_loop = 1;

  // Print out the total overflow value
  if (EDIT_ROBOT_UPDATES_DEBUG){
    echo '<div style="padding: 20px 10px; background-color: yellow; border: 1px solid #292929;">';
    echo '<h1>update-phase-'.$temp_phase_loop.'</h1>';
    echo '<p>Collect all the data for each robot, including level, stats, and original player</p>';
    echo '$temp_total_overflow_value = '.$temp_total_overflow_value.'<br />';
    echo '</div>';
  }

  // Do the first loop through this robot, collecting settings and unlocking abilities
  foreach ($temp_player_array AS $temp_player_token => $temp_player_info){
    if (EDIT_ROBOT_UPDATES_DEBUG){
      echo '<div style="padding: 20px 10px; background-color: rgba('.implode(', ', $temp_player_colours[$temp_player_token]).', 0.1);">';
      echo '<strong style="font-size: 120%;">'.$temp_player_token.' phase-'.$temp_phase_loop.'</strong><br /><br />';
    }
    if (!empty($temp_player_info['player_robots'])){

      foreach ($temp_player_info['player_robots'] AS $temp_robot_token => $temp_robot_info){
        if (!isset($mmrpg_database_robots[$temp_robot_token])){ continue; }
        edit_robot_update_loop_one(
          $mmrpg_database_robots,
          $temp_battle_rewards, $temp_battle_settings,
          $temp_player_array, $temp_total_overflow_value,
          $temp_player_colours, $temp_stat_tokens,
          $temp_player_token, $temp_player_info,
          $temp_robot_token, $temp_robot_info,
          $temp_phase_loop
          );
        }

      // Sort the above robots by their stat overflow, then level
      uasort($temp_player_array[$temp_player_token]['player_robots'], function($r1, $r2){
        //die('$r1 = <pre>'.print_r($r1, true).'</pre>');
        if ($r1['robot_stats_overflow'] > $r2['robot_stats_overflow']){ return -1; }
        elseif ($r1['robot_stats_overflow'] < $r2['robot_stats_overflow']){ return 1; }
        else { return 0; }
        });

    }
    if (EDIT_ROBOT_UPDATES_DEBUG){ echo '</div>'; }
  }

  // Increment the phase loop
  $temp_phase_loop++;

  // Print out the total overflow value
  if (EDIT_ROBOT_UPDATES_DEBUG){
    echo '<div style="padding: 20px 10px; background-color: yellow; border: 1px solid #292929;">';
    echo '<h1>update-phase-'.$temp_phase_loop.'</h1>';
    echo '<p>Round out the stats of any robots who had overflow in the previous step</p>';
    echo '$temp_total_overflow_value = '.$temp_total_overflow_value.'<br />';
    echo '</div>';
  }

  // Do the first loop through this robot, collecting settings and unlocking abilities
  foreach ($temp_player_array AS $temp_player_token => $temp_player_info){
    if (EDIT_ROBOT_UPDATES_DEBUG){
      echo '<div style="padding: 20px 10px; background-color: rgba('.implode(', ', $temp_player_colours[$temp_player_token]).', 0.1);">';
      echo '<strong style="font-size: 120%;">'.$temp_player_token.' phase-'.$temp_phase_loop.'</strong><br /><br />';
    }
    if (!empty($temp_player_info['player_robots'])){

      foreach ($temp_player_info['player_robots'] AS $temp_robot_token => $temp_robot_info){

        // If this robot had any overflow, let's remove it from their stats
        if ($temp_robot_info['robot_stats_overflow'] > 0){
          foreach ($temp_robot_info['robot_stats'] AS $stat_token => $stat_values){
            if ($stat_values['overflow'] > 0){
              $temp_robot_info['robot_stats'][$stat_token]['reward'] -= $stat_values['overflow'];
              $temp_robot_info['robot_stats'][$stat_token]['overflow'] -= $stat_values['overflow'];
              $temp_robot_info['robot_stats'][$stat_token]['total'] -= $stat_values['overflow'];
              $temp_robot_info['robot_stats_overflow'] -= $stat_values['overflow'];
              $temp_robot_info['robot_stats_total'] -= $stat_values['overflow'];
              // Update the actual session variable with the decrease in reward value
              if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token])){
                $value = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token];
                $value -= $stat_values['overflow'];
                if ($value < 0){ $value = 0; }
                $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token] = $value;
              }
            }
          }
        }

        // Print out the robot array for debug purposes
        if (EDIT_ROBOT_UPDATES_DEBUG){
          echo '<div class="robot_block">';
          echo '<strong class="robot_title">'.$temp_robot_token.' (phase-'.$temp_phase_loop.') ('.$temp_player_token.') '.($temp_player_token != $temp_robot_info['original_player'] ? '[via-'.$temp_robot_info['original_player'].']' : '').'</strong>';
          echo array2table(array('robot_level' => $temp_robot_info['robot_level']), true);
          echo array2table(array('robot_stats' => $temp_robot_info['robot_stats']), true);
          echo array2table(array('robot_stats_total' => $temp_robot_info['robot_stats_total']), true);
          echo array2table(array('robot_stats_overflow' => $temp_robot_info['robot_stats_overflow']), true);
          echo '</div>';
        }

        // Update the parent array with these collected details
        $temp_player_array[$temp_player_token]['player_robots'][$temp_robot_token] = $temp_robot_info;
        //$temp_total_overflow_value += $temp_robot_info['robot_stats_overflow'];

      }

    }

    if (EDIT_ROBOT_UPDATES_DEBUG){ echo '</div>'; }

  }

  // Increment the phase loop
  $temp_phase_loop++;

  // Print out the total overflow value
  if (EDIT_ROBOT_UPDATES_DEBUG){
    echo '<div style="padding: 20px 10px; background-color: yellow; border: 1px solid #292929;">';
    echo '<h1>update-phase-'.$temp_phase_loop.'</h1>';
    echo '<p>If there are any stats that are not yet at their max, boost them as high as we can with the total overflow</p>';
    echo '$temp_total_overflow_value = '.$temp_total_overflow_value.'<br />';
    echo '</div>';
  }

  // Do the first loop through this robot, collecting settings and unlocking abilities
  foreach ($temp_player_array AS $temp_player_token => $temp_player_info){
    if (EDIT_ROBOT_UPDATES_DEBUG){
      echo '<div style="padding: 20px 10px; background-color: rgba('.implode(', ', $temp_player_colours[$temp_player_token]).', 0.1);">';
      echo '<strong style="font-size: 120%;">'.$temp_player_token.' phase-'.$temp_phase_loop.'</strong><br /><br />';
    }
    if (!empty($temp_player_info['player_robots'])){

      foreach ($temp_player_info['player_robots'] AS $temp_robot_token => $temp_robot_info){

        // Loop through this robot's stats and boost any that are under max
        $temp_stat_maxed_count = 0;
        $temp_stat_maxed_flag = false;
        foreach ($temp_robot_info['robot_stats'] AS $stat_token => $stat_values){
          if ($stat_values['total'] < $stat_values['max']){
            $temp_overflow_boost = 0;
            $temp_max_boost = $stat_values['max'] - $stat_values['total'];
            if ($temp_total_overflow_value >= $temp_max_boost){ $temp_overflow_boost = $temp_max_boost; }
            else { $temp_overflow_boost = $temp_total_overflow_value; }
            $temp_robot_info['robot_stats'][$stat_token]['reward'] += $temp_overflow_boost;
            $temp_robot_info['robot_stats'][$stat_token]['total'] += $temp_overflow_boost;
            $temp_robot_info['robot_stats_total'] += $temp_overflow_boost;
            $temp_total_overflow_value -= $temp_overflow_boost;
            $stat_values =  $temp_robot_info['robot_stats'][$stat_token];
            // Update the actual session variable with the decrease in reward value
            if (!isset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token])){
              $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token] = 0;
            }
            if (!empty($temp_overflow_boost)){
              $value = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token];
              $value += $temp_overflow_boost;
              $_SESSION[$session_token]['values']['battle_rewards'][$temp_player_token]['player_robots'][$temp_robot_token]['robot_'.$stat_token] = $value;
            }
          }
          if ($stat_values['total'] == $stat_values['max']){
            $temp_stat_maxed_count++;
          }
        }
        if ($temp_stat_maxed_count == count($temp_robot_info['robot_stats'])){
          $temp_stat_maxed_flag = true;
        }

        // Print out the robot array for debug purposes
        if (EDIT_ROBOT_UPDATES_DEBUG){
          echo '<div class="robot_block">';
          echo '<strong class="robot_title">'.$temp_robot_token.' (phase-'.$temp_phase_loop.') ('.$temp_player_token.') '.($temp_player_token != $temp_robot_info['original_player'] ? '[via-'.$temp_robot_info['original_player'].']' : '').'</strong>';
          echo array2table(array('robot_level' => $temp_robot_info['robot_level']), true);
          echo array2table(array('robot_stats' => $temp_robot_info['robot_stats']), true);
          echo array2table(array('robot_stats_total' => $temp_robot_info['robot_stats_total']), true);
          echo array2table(array('robot_stats_overflow' => $temp_robot_info['robot_stats_overflow']), true);
          if ($temp_stat_maxed_flag){ echo array2table(array('robot_stats_maxed' => 'true'), true); }
          echo array2table(array('remaining_overflow_value' => $temp_total_overflow_value), true);
          echo '</div>';
        }

        // Update the parent array with these collected details
        $temp_player_array[$temp_player_token]['player_robots'][$temp_robot_token] = $temp_robot_info;
        //$temp_total_overflow_value += $temp_robot_info['robot_stats_overflow'];

      }

    }

    if (EDIT_ROBOT_UPDATES_DEBUG){ echo '</div>'; }

  }

  // Increment the phase loop
  $temp_phase_loop++;

  // Print out the total overflow value
  if (EDIT_ROBOT_UPDATES_DEBUG){
    echo '<div style="padding: 20px 10px; background-color: yellow; border: 1px solid #292929;">';
    echo '<h1>update-phase-'.$temp_phase_loop.'</h1>';
    echo '<p>Unlock all robot abilities for the original player that owned the robot in the first place</p>';
    echo '$temp_total_overflow_value = '.$temp_total_overflow_value.'<br />';
    echo '</div>';
  }

  // Do the first loop through this robot, collecting settings and unlocking abilities
  foreach ($temp_player_array AS $temp_player_token => $temp_player_info){
    if (EDIT_ROBOT_UPDATES_DEBUG){
      echo '<div style="padding: 20px 10px; background-color: rgba('.implode(', ', $temp_player_colours[$temp_player_token]).', 0.1);">';
      echo '<strong>'.$temp_player_token.' phase-'.$temp_phase_loop.'</strong><br /><br />';
    }
    if (!empty($temp_player_info['player_robots'])){
      foreach ($temp_player_info['player_robots'] AS $temp_robot_token => $temp_robot_info){

        // Collect the original play and prepare the transfer abilities to them
        $temp_robot_info['original_player'] = !empty($temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token]['original_player']) ? $temp_battle_settings[$temp_player_token]['player_robots'][$temp_robot_token]['original_player'] : $temp_player_token;

        if (EDIT_ROBOT_UPDATES_DEBUG){ echo '-- '.$temp_robot_token.' ('.$temp_robot_info['robot_level'].')  ('.$temp_robot_info['original_player'].') ('.$temp_phase_loop.')<br /><br />'; }

        if (!empty($temp_robot_info['robot_abilities'])){
          foreach ($temp_robot_info['robot_abilities'] AS $temp_ability_token => $temp_ability_info){
           if (EDIT_ROBOT_UPDATES_DEBUG){ echo '---- '.$temp_ability_token; }

           if (mmrpg_prototype_ability_unlocked($temp_robot_info['original_player'], false, $temp_ability_token)){
             if (EDIT_ROBOT_UPDATES_DEBUG){ echo ' (unlocked!)'; }
           } else {
             if (EDIT_ROBOT_UPDATES_DEBUG){ echo ' (not unlocked!)'; }
             $temp_original_player_info = array('player_token' => $temp_robot_info['original_player']);
             mmrpg_game_unlock_ability($temp_original_player_info, false, $temp_ability_info);
             if (EDIT_ROBOT_UPDATES_DEBUG){ echo ' (but unlocked now!)'; }
           }

           if (EDIT_ROBOT_UPDATES_DEBUG){ echo '<br />'; }
          }
        }

       if (EDIT_ROBOT_UPDATES_DEBUG){ echo '<br /><hr /><br />'; }

      }
    }
    if (EDIT_ROBOT_UPDATES_DEBUG){ echo '<br /></div>'; }
  }

  // Increment the phase loop
  $temp_phase_loop++;

  // Print out the total overflow value
  if (EDIT_ROBOT_UPDATES_DEBUG){
    echo '<div style="padding: 20px 10px; background-color: yellow; border: 1px solid #292929;">';
    echo '<h1>update-phase-'.$temp_phase_loop.'</h1>';
    echo '<p>This is the end, really - if there is any overflow value left, we should convert it to zenny and award the player</p>';
    echo '$temp_total_overflow_value = '.$temp_total_overflow_value.'<br />';
    echo '</div>';
  }

  if ($temp_total_overflow_value > 0){

    // Calculate the zenny reward for each point
    $temp_total_overflow_reward = $temp_total_overflow_value * 1;
    if (!isset($_SESSION[$session_token]['counters']['battle_zenny'])){ $_SESSION[$session_token]['counters']['battle_zenny'] = 0; }
    $_SESSION[$session_token]['counters']['battle_zenny'] += $temp_total_overflow_reward;

    // Print out the total overflow value
    if (EDIT_ROBOT_UPDATES_DEBUG){
      echo '<div style="padding: 20px 10px; background-color: green; border: 1px solid #292929; color: white;">';
      echo '$temp_total_overflow_reward = '.$temp_total_overflow_reward.'z<br />';
      echo '</div>';
    }


  }

}

// If we're in hyper debug mode, simply die at the end so we can view output
if (EDIT_ROBOT_UPDATES_DEBUG){ die('done'); }

?>