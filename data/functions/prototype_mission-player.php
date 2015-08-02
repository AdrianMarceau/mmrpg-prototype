<?
// Define a function for generating the PLAYER missions
function mmrpg_prototype_mission_player($this_prototype_data, $this_user_info, $this_max_robots, &$field_factors_one, &$field_factors_two, &$field_factors_three){
  // Pull in global variables for this function
  global $mmrpg_index, $DB, $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine;

  $this_field_index = mmrpg_field::get_index();
  // Define the omega battle and default to empty
  $temp_battle_omega = array();
  $temp_battle_omega['flags']['player_battle'] = true;
  $temp_battle_omega['values']['player_battle_masters'] = array();
  $temp_battle_omega['values']['player_battle_level'] = 1;

  // Define the local scope current player
  $this_player_token = $this_prototype_data['this_player_token'];
  $target_player_token = $this_prototype_data['target_player_token'];
  $target_player_token_backup = $target_player_token;

  // DEBUG
  //die('<pre>'.print_r($temp_player_array, true).'</pre>');

  // Pull and random player from the list and collect their full data
  $temp_player_array = $this_user_info; /* $temp_player_array = $DB->get_array("SELECT users.*, saves.*, boards.* FROM mmrpg_users AS users
    LEFT JOIN mmrpg_saves AS saves ON saves.user_id = users.user_id
    LEFT JOIN mmrpg_leaderboard AS boards ON boards.user_id = users.user_id
  	WHERE users.user_id = {$this_user_id}
  	"); */

  // Add this player data to the omage array
  $temp_battle_omega_player = $temp_player_array;

  // DEBUG
  //echo('<pre>'.print_r($temp_player_array, true).'</pre>');

  // Collect the player values and decode the rewards and settings arrays
  $temp_player_rewards = $temp_player_array['player_rewards'];
  $temp_player_settings = $temp_player_array['player_settings'];
  $temp_player_starforce = $temp_player_array['player_starforce'];
  $temp_player_favourites = $temp_player_array['player_favourites'];
  // Calculate what level these bonus robots should be in the range of
  $temp_player_rewards2 = mmrpg_prototype_player_rewards($this_prototype_data['this_player_token']);
  $temp_total_level = 0;
  $temp_total_robots = 0;
  $temp_bonus_level_min = 100;
  $temp_bonus_level_max = 1;
  if (!empty($temp_player_rewards2['player_robots'])){
    foreach ($temp_player_rewards2['player_robots'] AS $token => $info){
      $temp_level = !empty($info['robot_level']) ? $info['robot_level'] : 1;
      if ($temp_level > $temp_bonus_level_max){ $temp_bonus_level_max = $temp_level; }
      if ($temp_level < $temp_bonus_level_min){ $temp_bonus_level_min = $temp_level; }
      $temp_total_robots++;
    }
    //$temp_bonus_level_max = ceil($temp_total_level / $temp_total_robots);
    //$temp_bonus_level_min = ceil($temp_bonus_level_max / 3);
  }

  // Round the number to the nearst multiple of ten so it looks nicer
  $temp_player_battle_level = $temp_bonus_level_max;
  $temp_player_battle_level = floor($temp_player_battle_level * 0.10) * 10;
  if ($temp_player_battle_level < 10){ $temp_player_battle_level = 10; }

  // Update the player battle level to match that of this player's highest
  $temp_battle_omega['values']['player_battle_level'] = $temp_player_battle_level;

  // Create the empty array for the target player's battle robots
  $temp_player_robots = array();
  $temp_player_robots_rewards = !empty($temp_player_rewards[$target_player_token]['player_robots']) ? $temp_player_rewards[$target_player_token]['player_robots'] : array();
  $temp_player_robots_settings = !empty($temp_player_settings[$target_player_token]['player_robots']) ? $temp_player_settings[$target_player_token]['player_robots'] : array();
  $temp_player_field_settings = !empty($temp_player_settings[$target_player_token]['player_fields']) ? $temp_player_settings[$target_player_token]['player_fields'] : array();
  if (empty($temp_player_robots_rewards)){
    foreach ($temp_player_rewards AS $ptoken => $pinfo){
      if (!empty($temp_player_rewards[$ptoken]['player_robots'])){
        $target_player_token = $ptoken;
        $temp_player_robots_rewards = !empty($temp_player_rewards[$target_player_token]['player_robots']) ? $temp_player_rewards[$target_player_token]['player_robots'] : array();
        $temp_player_robots_settings = !empty($temp_player_settings[$target_player_token]['player_robots']) ? $temp_player_settings[$target_player_token]['player_robots'] : array();
        $temp_player_field_settings = !empty($temp_player_settings[$target_player_token]['player_fields']) ? $temp_player_settings[$target_player_token]['player_fields'] : array();
        break;
      }
    }
  }
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_player_robots_rewards:'.$temp_battle_omega_player['user_name_clean'].' = '.preg_replace('/\s+/', ' ', print_r($temp_player_robots_rewards, true)).'');  }
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_player_robots_settings:'.$temp_battle_omega_player['user_name_clean'].' = '.preg_replace('/\s+/', ' ', print_r($temp_player_robots_settings, true)).'');  }
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_player_field_settings:'.$temp_battle_omega_player['user_name_clean'].' = '.preg_replace('/\s+/', ' ', print_r($temp_player_field_settings, true)).'');  }
  //echo('<pre>'.__FILE__.' on line '.__LINE__.' : $temp_player_robots_rewards = '.preg_replace('/\s+/', ' ', print_r($temp_player_robots_rewards, true)).'</pre>');
  //echo('<pre>'.__FILE__.' on line '.__LINE__.' : $temp_player_robots_settings = '.preg_replace('/\s+/', ' ', print_r($temp_player_robots_settings, true)).'</pre>');
  // If the player fields setting is empty, define manually
  if (empty($temp_player_field_settings)){
    $temp_omega_fields = array();
    if ($target_player_token == 'dr-light'){ $temp_omega_fields = $this_omega_factors_one; }
    elseif ($target_player_token == 'dr-wily'){ $temp_omega_fields = $this_omega_factors_two; }
    elseif ($target_player_token == 'dr-cossack'){ $temp_omega_fields = $this_omega_factors_three; }
    foreach ($temp_omega_fields AS $omega){ $temp_player_field_settings[$omega['field']] = array('field_token' => $omega['field']); }
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'omega fiels for '.$this_player_token.' vs '.$target_player_token.' are now '.implode(',', array_keys($temp_player_field_settings)));  }
  }

  // Ensure this player has been unlocked by the target before continuing
  if (!empty($temp_player_robots_rewards)){
    //echo('<pre>'.__FILE__.' on line '.__LINE__.' : '.preg_replace('/\s+/', ' ', print_r($temp_player_rewards[$target_player_token], true)).'</pre>');

    // Collect the target player's robot rewards from the array
    $temp_player_robots = $temp_player_robots_rewards;

    // Define the array to hold the omega battle robots
    $temp_battle_omega_robots = array();

    // Loop through the reward robots and append their info
    $temp_counter = 1;
    foreach ($temp_player_robots AS $key => $temp_robotinfo){
      // Skip if does not exist
      if (empty($temp_robotinfo['robot_token'])){ continue; }
      // Collect this robot's settings if they exist
      if (!empty($temp_player_robots_settings[$temp_robotinfo['robot_token']])){ $temp_settings_array = $temp_player_robots_settings[$temp_robotinfo['robot_token']]; }
      else { $temp_settings_array = $temp_robotinfo; }
      // Collect this robot's rewards if they exist
      if (!empty($temp_player_robots_rewards[$temp_robotinfo['robot_token']])){ $temp_rewards_array = $temp_player_robots_rewards[$temp_robotinfo['robot_token']]; }
      else { $temp_rewards_array = $temp_robotinfo; }
      // Collect the basic details of this robot like ID, token, and level
      $temp_robot_id = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_counter;
      $temp_robot_token = $temp_robotinfo['robot_token'];
      $temp_robot_level = $temp_battle_omega['values']['player_battle_level']; //!empty($temp_robotinfo['robot_level']) ? $temp_robotinfo['robot_level'] : 1;
      $temp_robot_favourite = in_array($temp_robot_token, $temp_player_favourites) ? 1 : 0;
      $temp_robot_image = !empty($temp_settings_array['robot_image']) ? $temp_settings_array['robot_image'] : $temp_robotinfo['robot_token'];
      //$temp_robot_rewards = $temp_player_rewards[$target_player_token];
      $temp_robot_rewards = $temp_rewards_array;
      $temp_robot_settings = $temp_settings_array;
      // Collect this robot's abilities, format them, and crop if necessary
      $temp_robot_abilities = array();
      foreach ($temp_settings_array['robot_abilities'] AS $key2 => $temp_abilityinfo){ $temp_robot_abilities[] = $temp_abilityinfo['ability_token'] != 'copy-shot' ? $temp_abilityinfo['ability_token'] : 'buster-shot'; }
      $temp_robot_abilities = count($temp_robot_abilities) > 8 ? array_slice($temp_robot_abilities, 0, 8) : $temp_robot_abilities;
      // Create the new robot info array to be added to the omega battle options
      $temp_new_array = array('values' => array('flag_favourite' => $temp_robot_favourite, 'robot_rewards' => $temp_robot_rewards), 'robot_id' => $temp_robot_id, 'robot_token' => $temp_robot_token, 'robot_level' => $temp_robot_level, 'robot_image' => $temp_robot_image, 'robot_abilities' => $temp_robot_abilities);
      // Add this robot to the omega array and increment the counter
      $temp_battle_omega_robots[] = $temp_new_array;
      $temp_counter++;

    }

    // Sort the player's robots according to their level
    usort($temp_battle_omega_robots, 'mmrpg_prototype_sort_player_robots');

    // Slice the robot array based on the max num requested
    $temp_max_robots = $this_max_robots;
    $temp_omega_robots_count = count($temp_battle_omega_robots);
    if ($temp_omega_robots_count > $temp_max_robots){
      $temp_battle_omega_robots = array_slice($temp_battle_omega_robots, 0, $temp_max_robots);
      shuffle($temp_battle_omega_robots);
    } elseif ($temp_omega_robots_count < $temp_max_robots){
      $temp_max_robots = $temp_omega_robots_count;
    }
    $temp_omega_robots_count = count($temp_battle_omega_robots);


    // DEBUG
    //die('<pre><strong>$temp_battle_omega_robots</strong><br />'.print_r($temp_battle_omega_robots, true).'</pre>');

    // Populate the battle options with the player battle option
    $temp_battle_userid = $temp_battle_omega_player['user_id'];
    $temp_battle_usertoken = $temp_battle_omega_player['user_name_clean'];
    $temp_battle_username = !empty($temp_battle_omega_player['user_name_public']) ? $temp_battle_omega_player['user_name_public'] : $temp_battle_omega_player['user_name'];
    $temp_battle_userpronoun = ($temp_battle_omega_player['user_gender'] == 'male' ? 'his' : ($temp_battle_omega_player['user_gender'] == 'female' ? 'her' : ('their')));
    //$temp_battle_userimage = !empty($temp_battle_omega_player['user_image_path']) ? $temp_battle_omega_player['user_image_path'] : 'robots/mega-man';
    $temp_robots_num = count($temp_battle_omega_robots);
    $temp_battle_token = $this_prototype_data['phase_battle_token'].'-vs-player-'.$temp_battle_usertoken;
    $backup_fields = array('flags', 'values', 'counters');
    $backup_values = array();
    foreach ($backup_fields AS $field){ $backup_values[$field] = isset($temp_battle_omega[$field]) ? $temp_battle_omega[$field] : array(); }
    $temp_battle_omega = mmrpg_battle::get_index_info('bonus-prototype-complete-3');
    foreach ($backup_fields AS $field){ $temp_battle_omega[$field] = isset($temp_battle_omega[$field]) ? array_replace($temp_battle_omega[$field], $backup_values[$field]) : $backup_values[$field]; }
    $temp_challenge_type = ($temp_max_robots == 8 ? 'an ' : 'a ').$temp_max_robots.'-on-'.$temp_max_robots;
    $temp_star_boost = !empty($temp_player_starforce) ? array_sum($temp_player_starforce) : 0;
    $temp_battle_omega['battle_token'] = $temp_battle_token;
    $temp_battle_omega['battle_size'] = '1x2';
    $temp_battle_omega['battle_counts'] = false;
    $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
    $temp_battle_omega['battle_name'] = 'Player Battle vs '.$temp_battle_username;
    if (!empty($temp_star_boost)){
      reset($temp_player_starforce);
      $temp_most_powerful = key($temp_player_starforce);
      $temp_most_powerful_value = $temp_player_starforce[$temp_most_powerful];
      //$temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;s starforce boosted player data in a '.$temp_challenge_type.' battle! The '.ucfirst($temp_most_powerful).' type appears to be '.$temp_battle_userpronoun.' most powerful element, with '.($temp_most_powerful_value * 10).'&nbsp;/&nbsp;'.($temp_star_boost * 10).'% of the total boost!';
      $temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;'.(!preg_match('/s$/i', $temp_battle_username) ? 's' : '').' starforce boosted player data in '.$temp_challenge_type.' battle!';
      $temp_battle_omega['battle_description2'] = 'The '.ucfirst($temp_most_powerful).' type appears to be '.$temp_battle_userpronoun.' most powerful element, with nearly '.ceil(($temp_most_powerful_value / $temp_star_boost) * 100).'% of the total boost!';
    } else {
      $temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($temp_battle_username).'&#39;'.(!preg_match('/s$/i', $temp_battle_username) ? 's' : '').' player data in '.$temp_challenge_type.' battle!';
      $temp_battle_omega['battle_description2'] = '';
    }
    $temp_battle_omega['battle_turns'] = ceil(MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $temp_robots_num * MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER);
    $temp_battle_omega['battle_robot_limit'] = $this_max_robots;
    $temp_battle_omega['battle_points'] = 0;
    foreach ($temp_battle_omega_robots AS $info){
      $temp_stat_counter = 0;
      $temp_robot_rewards = !empty($info['values']['robot_rewards']) ? $info['values']['robot_rewards'] : array();
      if (!empty($temp_robot_rewards['robot_energy'])){ $temp_stat_counter += $temp_robot_rewards['robot_energy']; }
      if (!empty($temp_robot_rewards['robot_attack'])){ $temp_stat_counter += $temp_robot_rewards['robot_attack']; }
      if (!empty($temp_robot_rewards['robot_defense'])){ $temp_stat_counter += $temp_robot_rewards['robot_defense']; }
      if (!empty($temp_robot_rewards['robot_speed'])){ $temp_stat_counter += $temp_robot_rewards['robot_speed']; }
      $temp_battle_omega['battle_points'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT * $info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER) + $temp_stat_counter;
      $temp_battle_omega['battle_points'] += !empty($temp_star_boost) ? ceil(($temp_star_boost * $temp_stat_counter) / MMRPG_SETTINGS_STARS_ATTACKBOOST) : 0;
      $temp_battle_omega['values']['player_battle_masters'][] = $info['robot_token'];
    }
    //if (!empty($temp_star_boost)){ $temp_battle_omega['battle_points'] += ceil($temp_star_boost * 1000);  }
    // Define the fusion field properties
    //$temp_battle_omega['battle_field_base']['field_name'] = ucfirst($temp_battle_username); //'Player Battle : '.$temp_battle_username;
    $temp_battle_omega['battle_button'] = ucfirst($temp_battle_username);
    $temp_field_info_options = array_keys($temp_player_field_settings);
    $temp_rand_int = mt_rand(1, 4);
    $temp_rand_start = ($temp_rand_int - 1) * 2;
    $temp_field_info_options = array_slice($temp_field_info_options, $temp_rand_start, 2);
    //shuffle($temp_field_info_options);
    $temp_field_token_one = $temp_field_info_options[0];
    $temp_field_token_two = $temp_field_info_options[1];
    $temp_field_info_one = mmrpg_field::parse_index_info($this_field_index[$temp_field_token_one]);
    $temp_field_info_two = mmrpg_field::parse_index_info($this_field_index[$temp_field_token_two]);
    $temp_option_multipliers = array();
    $temp_option_field_list = array($temp_field_info_one, $temp_field_info_two);
    $temp_battle_omega['battle_field_base']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_field_info_one['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_field_info_two['field_name']);
    foreach ($temp_option_field_list AS $temp_field){
      if (!empty($temp_field['field_multipliers'])){
        foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
          if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
          else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
        }
      }
    }
    //$temp_battle_omega['battle_field_base']['field_music'] = $temp_field_token_three['field'];
    $temp_battle_omega['battle_field_base']['field_type'] = !empty($temp_field_info_one['field_type']) ? $temp_field_info_one['field_type'] : '';
    $temp_battle_omega['battle_field_base']['field_type2'] = !empty($temp_field_info_two['field_type']) ? $temp_field_info_two['field_type'] : '';
    $temp_battle_omega['battle_field_base']['field_music'] = $temp_field_token_two;
    $temp_battle_omega['battle_field_base']['field_background'] = $temp_field_token_one;
    $temp_battle_omega['battle_field_base']['field_foreground'] = $temp_field_token_two;
    // Update the battle robot limit once more in case target had fewer robots than anticipated
    $temp_battle_omega['battle_robot_limit'] = count($temp_battle_omega_robots);
    //$temp_battle_omega['battle_description'] .= ' // starforce:+'.($temp_star_boost * 10).'% // background:'.$temp_battle_omega['battle_field_base']['field_background'].' / foreground:'.$temp_battle_omega['battle_field_base']['field_foreground'];
    $temp_battle_omega['battle_field_base']['field_multipliers'] = $temp_option_multipliers;
    $temp_battle_omega['battle_field_base']['field_mechas'] = array();
    if (!empty($temp_field_info_one['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'] = array_merge($temp_battle_omega['battle_field_base']['field_mechas'], $temp_field_info_one['field_mechas']); }
    if (!empty($temp_field_info_two['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'] = array_merge($temp_battle_omega['battle_field_base']['field_mechas'], $temp_field_info_two['field_mechas']); }
    //if (!empty($temp_option_field_list[2]['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'] = array_merge($temp_battle_omega['battle_field_base']['field_mechas'], $temp_option_field_list[2]['field_mechas']); }
    if (empty($temp_battle_omega['battle_field_base']['field_mechas'])){ $temp_battle_omega['battle_field_base']['field_mechas'][] = 'met'; }
    $temp_battle_omega['battle_field_base']['field_background_frame'] = $temp_field_info_one['field_background_frame'];
    $temp_battle_omega['battle_field_base']['field_foreground_frame'] = $temp_field_info_two['field_foreground_frame'];
    $temp_battle_omega['battle_field_base']['field_background_attachments'] = $temp_field_info_one['field_background_attachments'];
    $temp_battle_omega['battle_field_base']['field_foreground_attachments'] = $temp_field_info_two['field_foreground_attachments'];
    // Define the final details for the player
    $temp_battle_omega['battle_target_player']['player_id'] = $temp_battle_userid;
    $temp_battle_omega['battle_target_player']['player_token'] = $target_player_token_backup;
    $temp_battle_omega['battle_target_player']['player_name'] = ucfirst($temp_battle_username);
    $temp_battle_omega['battle_target_player']['player_robots'] = $temp_battle_omega_robots;
    $temp_battle_omega['battle_target_player']['player_starforce'] = $temp_player_starforce;

  } else {

    return false;

  }

  // Return the generated battle data
  return $temp_battle_omega;

}
?>