<?
// Define a function for generating the DOUBLES missions
function mmrpg_prototype_mission_double($this_prototype_data, $this_robot_tokens, $this_field_tokens, $this_start_level = 1, $this_unlock_robots = true, $this_unlock_abilities = true){
  // Pull in global variables for this function
  global $mmrpg_index, $DB, $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine;

  // Collect the robot index for calculation purposes
  $this_robot_index = mmrpg_robot::get_index();
  $this_field_index = mmrpg_field::get_index();

  // Define the array to hold this omega battle and populate with base varaibles
  $this_robot_token = $this_robot_tokens[0];
  $this_robot_token2 = $this_robot_tokens[1];

  $temp_option_battle = array();
  $temp_option_battle2 = array();
  $temp_option_robot = is_array($this_robot_tokens[0]) ? $this_robot_tokens[0] : array('robot_token' => $this_robot_tokens[0]);
  $temp_option_robot2 = is_array($this_robot_tokens[1]) ? $this_robot_tokens[1] : array('robot_token' => $this_robot_tokens[1]);
  $temp_option_field = mmrpg_field::parse_index_info($this_field_index[$this_field_tokens[0]]);
  $temp_option_field2 = mmrpg_field::parse_index_info($this_field_index[$this_field_tokens[1]]);

  $temp_battle_omega = array();

  $temp_battle_omega['flags']['double_battle'] = true;
  $temp_battle_omega['values']['double_battle_masters'] = $this_robot_tokens;

  $temp_option_battle['battle_size'] = '1x2';
  $temp_option_battle['battle_name'] = 'Chapter Four Fusion Battle';
  $temp_option_battle['battle_token'] = $this_prototype_data['phase_battle_token'].'-'.str_replace('-man', '', $this_robot_tokens[0]).'-'.str_replace('-man', '', $this_robot_tokens[1]);
  $temp_option_battle['battle_phase'] = $this_prototype_data['battle_phase'];

  $temp_option_battle['battle_field_base']['field_token'] = $temp_option_field['field_token'];
  $temp_option_battle['battle_field_base']['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_name']);
  $temp_option_battle['battle_field_base']['field_type'] = !empty($temp_option_field['field_type']) ? $temp_option_field['field_type'] : '';
  $temp_option_battle['battle_field_base']['field_type2'] = !empty($temp_option_field2['field_type']) ? $temp_option_field2['field_type'] : '';
  $temp_option_battle['battle_field_base']['field_music'] = $temp_option_field2['field_token'];
  $temp_option_battle['battle_field_base']['field_foreground'] = $temp_option_field2['field_foreground'];
  $temp_option_battle['battle_field_base']['field_foreground_attachments'] = $temp_option_field2['field_foreground_attachments'];
  $temp_option_battle['battle_field_base']['field_background'] = $temp_option_field['field_background'];
  $temp_option_battle['battle_field_base']['field_background_attachments'] = $temp_option_field['field_background_attachments'];

  $temp_option_multipliers = array();
  $temp_option_field_list = array($temp_option_field, $temp_option_field2);
  foreach ($temp_option_field_list AS $temp_field){
    if (!empty($temp_field['field_multipliers'])){
      foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
        if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
        else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
      }
    }
  }
  $temp_option_battle['battle_field_base']['field_multipliers'] = $temp_option_multipliers;

  $temp_option_battle['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
  $temp_option_battle['battle_target_player']['player_token'] = 'player';
  $temp_option_battle['battle_target_player']['player_robots'] = array(); //array_merge($temp_option_battle['battle_target_player']['player_robots'], $temp_option_battle2['battle_target_player']['player_robots']);
  $temp_option_battle['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);
  $temp_option_battle['battle_target_player']['player_robots'][1] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => $this_robot_token2);
  //shuffle($temp_option_battle['battle_target_player']['player_robots']);

  $temp_option_battle['battle_complete'] = false;
  $temp_option_completed = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_option_battle['battle_token']);
  if ($temp_option_completed){ $temp_option_battle['battle_complete'] = $temp_option_completed; }

  $temp_target_count = 4;
  $temp_ability_count = 4;
  $temp_battle_count = 0;
  if (!empty($temp_option_battle['battle_complete']['battle_count'])){
    $temp_battle_count = $temp_option_battle['battle_complete']['battle_count'];
    $temp_target_count += $temp_battle_count;
    $temp_ability_count += $temp_battle_count;
  }
  if ($temp_target_count > 6){ $temp_target_count = 6; }
  if ($temp_ability_count > 8){ $temp_ability_count = 8; }

  // Create the quick flag to check if battle is complete
  $temp_battle_complete = !empty($temp_option_battle['battle_complete']) ? true : false;
  // Check to see if this robot master has been unlocked already
  $temp_master_unlocked = mmrpg_prototype_robot_unlocked('', $this_robot_token) ? true : false;
  $temp_master_unlocked2 = mmrpg_prototype_robot_unlocked('', $this_robot_token2) ? true : false;
  // Create a quick flah to check if the prototype is complete
  $temp_prototype_complete = !empty($this_prototype_data['prototype_complete']) ? true : false;

  // Define the fusion star token in case we need to test for it
  $temp_field_star_token = preg_replace('/^([-_a-z0-9\s]+)-([-_a-z0-9]+)$/i', '$1', $temp_option_field['field_token']).'-'.preg_replace('/^([-_a-z0-9\s]+)-([-_a-z0-9]+)$/i', '$2', $temp_option_field2['field_token']);
  $temp_field_star_present = $this_prototype_data['prototype_complete'] && empty($_SESSION['GAME']['values']['battle_stars'][$temp_field_star_token]) ? true : false;

  // Define both the starforce and darkness battles to false
  $temp_darkness_battle = false;
  $temp_starforce_battle = false;

  // If the prototype is complete, this can be a starforce battle
  if ($temp_prototype_complete && $temp_field_star_present){
    $temp_starforce_battle = true;
  }

  // If the prototype and battle are complete plus robot unlocked, this can be a darkness battle
  if ($temp_prototype_complete && $temp_battle_complete && $temp_master_unlocked && $temp_master_unlocked2){
    // Only make this a darkness battle on a random chance and only if there isn't a star present
    if (!$temp_field_star_present && mt_rand(0, 3) == 0){
      $temp_darkness_battle = true;
      $temp_starforce_battle = false;
    }
  }

  // If a fusion star is preent on the field, fill the empty spots with like-typed robots
  if ($temp_starforce_battle){

    $temp_option_battle['battle_target_player']['player_switch'] = 2;
    $temp_robot_tokens = array();
    $temp_robot_tokens[] = $temp_option_battle['battle_target_player']['player_robots'][0]['robot_token'];
    $temp_robot_tokens[] = $temp_option_battle['battle_target_player']['player_robots'][1]['robot_token'];
    // Collect factors based on player
    if ($this_prototype_data['this_player_token'] == 'dr-light'){
      $temp_factors_list = array($this_omega_factors_one, array_merge($this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine));
    } elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){
      $temp_factors_list = array($this_omega_factors_two, array_merge($this_omega_factors_three, $this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine, $this_omega_factors_one));
    } elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){
      $temp_factors_list = array($this_omega_factors_three, array_merge($this_omega_factors_four, $this_omega_factors_five, $this_omega_factors_six, $this_omega_factors_seven, $this_omega_factors_eight, $this_omega_factors_nine, $this_omega_factors_one, $this_omega_factors_two));
    }
    // Shuffle the bonus robots section of the list
    shuffle($temp_factors_list[1]);
    //$debug_backup = 'initial:count = '.count($temp_option_battle['battle_target_player']['player_robots']).' // ';
    // Loop through and add the robots
    $temp_counter = 0;
    foreach ($temp_factors_list AS $this_list){
      shuffle($this_list);
      foreach ($this_list AS $this_factor){
        if (empty($this_factor['robot'])){ continue; }
        $bonus_robot_index = $this_robot_index[$this_factor['robot']];
        if (!isset($bonus_robot_index['robot_core'])){ $bonus_robot_index['robot_core'] = ''; }
        if ($bonus_robot_index['robot_core'] == $temp_option_field['field_type'] || $bonus_robot_index['robot_core'] == $temp_option_field2['field_type']){
          if (!in_array($bonus_robot_index['robot_token'], $temp_robot_tokens)){
            $bonus_robot_info = array();
            $bonus_robot_info['flags']['hide_from_mission_select'] = true;
            $bonus_robot_info['robot_token'] = $bonus_robot_index['robot_token'];
            $bonus_robot_info['robot_class'] = $bonus_robot_index['robot_class'];
            $temp_option_battle['battle_target_player']['player_robots'][] = $bonus_robot_info;
            $temp_robot_tokens[] = $bonus_robot_info['robot_token'];
          }
        }
      }
    }
    //$debug_backup .= 'before:count = '.count($temp_option_battle['battle_target_player']['player_robots']).' // ';
    $temp_slice_limit = 4; //2 + $temp_option_battle['battle_complete']['battle_count'];
    //if ($temp_slice_limit >= (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 2)){ $temp_slice_limit = (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX / 2); }
    //elseif ($temp_slice_limit >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ $temp_slice_limit = 8; }
    $temp_option_battle['battle_target_player']['player_robots'] = array_slice($temp_option_battle['battle_target_player']['player_robots'], 0, $temp_slice_limit);
    shuffle($temp_option_battle['battle_target_player']['player_robots']);
    //$debug_backup .= 'after:count = '.count($temp_option_battle['battle_target_player']['player_robots']).' // ';

  }

  // Define the omega variables for level, points, turns, and random encounter rate
  $omega_robot_level_max = $this_start_level + 3;
  $omega_robot_level = $this_start_level + (!empty($this_prototype_data['battles_complete']) ? ($this_prototype_data['battles_complete'] - 10) * 1 : 0);
  if ($omega_robot_level >= $omega_robot_level_max){ $omega_robot_level = $omega_robot_level_max; }
  $omega_random_encounter = false;

  // Define the battle rewards based on above data
  $temp_battle_omega['battle_rewards']['robots'] = array();
  $temp_battle_omega['battle_rewards']['abilities'] = array();
  if (!$temp_master_unlocked){ $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $this_robot_token); }
  if (!empty($temp_option_robot['robot_rewards']['abilities'])){
    foreach ($temp_option_robot['robot_rewards']['abilities'] AS $key => $info){
      if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
      if (!mmrpg_prototype_ability_unlocked('', '', $info['token'])){ $temp_battle_omega['battle_rewards']['abilities'][] = $info; }
    }
  }
  if (!$temp_master_unlocked2){ $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $this_robot_token2); }
  if (!empty($temp_option_robot2['robot_rewards']['abilities'])){
    foreach ($temp_option_robot2['robot_rewards']['abilities'] AS $key => $info){
      if ($info['token'] == 'buster-shot' || $info['level'] > $omega_robot_level){ continue; }
      if (!mmrpg_prototype_ability_unlocked('', '', $info['token'])){ $temp_battle_omega['battle_rewards']['abilities'][] = $info; }
    }
  }

  // Fill the empty spots with minor enemy robots
  if (true){
    $temp_option_battle['battle_target_player']['player_switch'] = 2.0;
    $bonus_robot_count = $temp_target_count;
    $temp_mook_options = array();
    $temp_mook_letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
    $temp_mook_counts = array();
    if (!isset($temp_option_field['field_mechas'])){ $temp_option_field['field_mechas'] = array(); }
    if (!isset($temp_option_field2['field_mechas'])){ $temp_option_field2['field_mechas'] = array(); }
    for ($key = 0; $key < count($temp_option_field['field_mechas']); $key++){
      if (!empty($temp_option_field['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field['field_mechas'][$key]; }
      if (!empty($temp_option_field2['field_mechas'][$key])){ $temp_mook_options[] = $temp_option_field2['field_mechas'][$key]; }
    }
    $temp_mook_weights = array();
    $temp_mook_maxchance = count($temp_mook_options) * 30;
    foreach ($temp_mook_options AS $key => $token){ $temp_mook_weights[] = $temp_mook_maxchance - ($key * 15); }
    $temp_mook_options = array_slice($temp_mook_options, 0, ($temp_battle_count + 1));
    $temp_battle_omega['battle_field_base']['field_mechas'] = $temp_mook_options;
    if ($temp_darkness_battle){ $temp_battle_omega['battle_field_base']['field_mechas'] = array('dark-frag'); }
    $mt_rand = mt_rand(1, 4);

    // Allow mets to randomly show up in other battles at a low rate
    if (!empty($temp_battle_omega['battle_complete']) && !in_array('met', $temp_mook_options)){ $temp_mook_options[] = 'met'; $temp_mook_weights[] = 5; }

    // Loop through the allowed bonus robot count placing random mooks
    $temp_base_robot_count = count($temp_option_battle['battle_target_player']['player_robots']);
    for ($i = 0; $i < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX; $i++){

      $temp_count_target_robots = count($temp_option_battle['battle_target_player']['player_robots']);
      if ($temp_count_target_robots >= $bonus_robot_count){ break; }
      elseif ($temp_count_target_robots >= MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){ break; }

      if ($temp_darkness_battle){ $temp_mook_token = 'dark-frag'; }
      else { $temp_mook_token = mmrpg_battle::weighted_chance_static($temp_mook_options, $temp_mook_weights); }

      $temp_mook_info = mmrpg_robot::parse_index_info($this_robot_index[$temp_mook_token]);
      $temp_mook_id = MMRPG_SETTINGS_TARGET_PLAYERID + $temp_count_target_robots;
      $bonus_robot_info = array('robot_token' => $temp_mook_token, 'robot_id' => $temp_mook_id, 'robot_level' => 1);

      $bonus_robot_info['robot_abilities'] = $temp_mook_info['robot_abilities'];
      $bonus_robot_info['robot_class'] = !empty($temp_mook_info['robot_class']) ? $temp_mook_info['robot_class'] : 'master';
      $bonus_robot_info['robot_name'] = $temp_mook_info['robot_name'];

      $temp_mook_name_token = str_replace(' ', '-', strtolower($bonus_robot_info['robot_name']));
      $bonus_robot_info['robot_name_token']  = $temp_mook_name_token;
      if (!isset($temp_mook_counts[$temp_mook_name_token])){ $temp_mook_counts[$temp_mook_name_token] = 0; }
      else { $temp_mook_counts[$temp_mook_name_token]++; }

      $temp_option_battle['battle_target_player']['player_robots'][] = $bonus_robot_info;
      $temp_robot_tokens[] = $bonus_robot_info['robot_token'];

    }

    // Remove the "A" from any mooks that are one of their kind
    foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
      if (!isset($info['robot_class']) || $info['robot_class'] != 'mecha'){ continue; }
      elseif (!isset($temp_mook_counts[$info['robot_token']]) || $temp_mook_counts[$info['robot_token']] == 0){
        $temp_option_battle['battle_target_player']['player_robots'][$key]['robot_name'] = str_replace(' A', '', $info['robot_name']);
      }
    }

    // Loop through the target player robots to make final changes
    foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
      // Collect an index of this robot
      $index = mmrpg_robot::parse_index_info($this_robot_index[$info['robot_token']]);

      // Default the stat reward boost to zero to prevent early difficulities
      $temp_stat_reward_boost = 0;
      // Update the robot ID to prevent collisions
      $info['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1;
      // Append the appropriate letters to all the robot name tokens
      $info['robot_class'] = !empty($info['robot_class']) ? $info['robot_class'] : 'master';

      // Check to see if this is a support mecha and process changes accordingly
      if ($info['robot_class'] == 'mecha'){
        /*
        // Update this mecha's name with a letter if necessary
        $temp_name_token = isset($info['robot_name_token']) ? $info['robot_name_token'] : $info['robot_token'];
        if ($temp_mook_counts[$temp_name_token] > 0){
          if (!isset($temp_mook_counts[$temp_name_token])){ $temp_mook_counts[$temp_name_token] = 0; }
          else { $temp_mook_counts[$temp_name_token]++; }
          $info['robot_name'] .= ' '.$temp_mook_letters[$temp_mook_counts[$temp_name_token]];
        }
        */

        // If this is a starforce battle, boost the mechas stats appropriately
        if ($temp_starforce_battle){
          $temp_stat_reward_boost = 0.5;
        }
        // Otherwise if this is a darkness battle, boost the dark frags appropriately
        elseif ($temp_darkness_battle){
          $temp_stat_reward_boost = 1.0;
        }

      }
      // Otherwise, if this is a master robot, tweak their alt and stats
      else {

        // If this is s starforce battle, the robot master should be boosted in an alt colour
        if ($temp_starforce_battle){
          // Update the robot name, adding a delta symbol at the end
          $info['robot_name'] = $index['robot_name'].' Δ';
          $info['robot_number'] = $index['robot_number'].'Δ';
          // Update the robot image for this battle to be the alt version
          $info['robot_image'] = $info['robot_token'].'_alt2';
          // This robot should also have more stats than usual, so let's give 'em some
          $temp_stat_reward_boost = 1.0;
        }
        // Otherwise, if a darkness battle, the robot master should be boosted further and dark in colour
        elseif ($temp_darkness_battle){
          // Update the robot name, adding a lower-case sigma symbol at the end
          $info['robot_name'] = $index['robot_name'].' Σ';
          $info['robot_number'] = $index['robot_number'].'Σ';
          // Update the robot image for this battle to be the alt version
          $info['robot_image'] = $info['robot_token'].'_alt9';
          // Update the robot's core type to empty
          $info['robot_core'] = 'empty';
          $info['robot_core2'] = '';
          // Update the robot's weaknesses/resistances/etc.
          $info['robot_weaknesses'] = array();
          $info['robot_resistances'] = array();
          $info['robot_affinities'] = array();
          $info['robot_immunities'] = array(); //array('copy', 'crystal', 'cutter', 'earth', 'electric', 'explode', 'flame', 'freeze', 'impact', 'laser', 'missile', 'nature', 'shadow', 'shield', 'space', 'swift', 'time', 'water', 'wind');
          // Remove all quotes so this robot doesn't speak
          $info['robot_quotes'] = array();
          // This robot should also have more stats than usual, so let's give 'em more
          $temp_stat_reward_boost = 2.0;
        }
        // If the defined robot image does not actually exist, replace with placeholder base sprite
        if (isset($info['robot_image']) && !file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$info['robot_image'].'/')){
          $info['robot_image'] = $info['robot_token'];
        }

      }

      // If there is a reward booster in place, generate values
      if ($temp_stat_reward_boost > 0){

        // This robot should also have more stats than usual, so let's give 'em some more
        $temp_robot_rewards = array();
        //$temp_stat_total = $index['robot_energy'] + $index['robot_attack'] + $index['robot_defense'] + $index['robot_speed'];
        //$temp_robot_rewards['robot_energy'] = ceil(($index['robot_energy'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
        $temp_stat_total = $index['robot_attack'] + $index['robot_defense'] + $index['robot_speed'];
        $temp_robot_rewards['robot_attack'] = ceil(($index['robot_attack'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
        $temp_robot_rewards['robot_defense'] = ceil(($index['robot_defense'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
        $temp_robot_rewards['robot_speed'] = ceil(($index['robot_speed'] / $temp_stat_total) * MMRPG_SETTINGS_BATTLEROBOTS_TARGET_STATBOOST * $temp_stat_reward_boost);
        $info['values']['robot_rewards'] = $temp_robot_rewards;

      }

      // Update the player robots array with recent changes
      $temp_option_battle['battle_target_player']['player_robots'][$key] = $info;

    }

  }

  // Reassign robot IDs to prevent errors
  foreach ($temp_option_battle['battle_target_player']['player_robots'] AS $key => $info){
    $temp_option_battle['battle_target_player']['player_robots'][$key]['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1;
  }
  $temp_option_battle['battle_rewards']['robots'] = array();
  $temp_option_battle['battle_rewards']['abilities'] = array();
  $temp_battle_flags = !empty($temp_battle_omega['flags']) ? $temp_battle_omega['flags'] : array();
  $temp_battle_values = !empty($temp_battle_omega['values']) ? $temp_battle_omega['values'] : array();
  $temp_battle_counters = !empty($temp_battle_omega['counters']) ? $temp_battle_omega['counters'] : array();
  $temp_battle_omega = $temp_option_battle;
  $temp_battle_omega['flags'] = $temp_battle_flags;
  $temp_battle_omega['values'] = $temp_battle_values;
  $temp_battle_omega['counters'] = $temp_battle_counters;

  // Skip the empty battle button or a different phase
  if (empty($temp_battle_omega['battle_token']) || $temp_battle_omega['battle_token'] == 'battle' || $temp_battle_omega['battle_phase'] != $this_prototype_data['battle_phase']){ return false; }

  // Collect the battle token and create an omega clone from the index base
  $temp_battle_token = $temp_battle_omega['battle_token'];
  // If the battle was already complete, collect its details and modify the mission
  $temp_complete_level = 0;
  $temp_complete_count = 0;
  if (!empty($temp_battle_omega['battle_complete'])){
    if (!empty($temp_battle_omega['battle_complete']['battle_min_level'])){ $temp_complete_level = $temp_battle_omega['battle_complete']['battle_min_level']; }
    else { $temp_complete_level = $omega_robot_level; }
    if (!empty($temp_battle_omega['battle_complete']['battle_count'])){ $temp_complete_count = $temp_battle_omega['battle_complete']['battle_count']; }
    else { $temp_complete_count = 1; }
    $omega_robot_level = $temp_complete_level + $temp_complete_count - 1;
    $omega_robot_level = $omega_robot_level >= 100 ? 100 : $omega_robot_level;
  }

  // If this is a starforce battle, we should double the level max
  if ($temp_starforce_battle){
    $omega_robot_level_max = $omega_robot_level_max * 2;
    $omega_robot_level = $omega_robot_level * 2;
  }
  // Otherwise if this is a darkness battle, we should triple the level max
  elseif ($temp_darkness_battle){
    $omega_robot_level_max = $omega_robot_level_max * 3;
    $omega_robot_level = $omega_robot_level * 3;
  }

  // Define the battle difficulty level (0 - 8) based on level and completed count
  $temp_battle_difficulty = ceil(8 * ($omega_robot_level / 100));
  $temp_battle_difficulty += $temp_complete_count;
  if ($temp_battle_difficulty >= 10){ $temp_battle_difficulty = 10; }
  $temp_battle_omega['battle_difficulty'] = $temp_battle_difficulty;
  // Update the robot level for this battle
  $temp_battle_omega['battle_level'] = $omega_robot_level;

  // Start all the point-based battle vars at zero
  $temp_battle_omega['battle_points'] = 0;
  $temp_battle_omega['battle_zenny'] = 0;
  $temp_battle_omega['battle_turns'] = 0;
  $temp_battle_omega['battle_robot_limit'] = 0;

  // Loop through the target robots again update with omega values
  foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key2 => $robot){

    // Update the robot level and battle points plus turns
    if (!isset($this_robot_index[$robot['robot_token']])){ continue; }
    $temp_core_backup = !empty($robot['robot_core']) ? $robot['robot_core'] : '';
    $index = mmrpg_robot::parse_index_info($this_robot_index[$robot['robot_token']]);
    $robot = array_merge($index, $robot);
    if (!empty($temp_core_backup)){ $robot['robot_core'] = $temp_core_backup; }
    if (!isset($robot['robot_item'])){ $robot['robot_item'] = ''; }
    $temp_opposite_type = $robot['robot_core'] != $temp_option_field['field_type'] ? $temp_option_field['field_type'] : $temp_option_field2['field_type'];

    // Increment allowable robots, points, and turns based on who's in the battle
    if ($robot['robot_class'] == 'mecha'){
      if (mt_rand(0, 6) == 0){ $robot['robot_item'] = 'item-shard-'.$temp_opposite_type; }
      $robot['robot_level'] = mt_rand(ceil($omega_robot_level / 2), $omega_robot_level);
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $robot['robot_item'];
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = mmrpg_prototype_generate_abilities($robot, $omega_robot_level, ceil($temp_ability_count / 2), $robot['robot_item']);
    }
    elseif ($robot['robot_class'] == 'master'){
      if (mt_rand(0, 3) == 0){ $robot['robot_item'] = 'item-core-'.$temp_opposite_type; }
      $robot['robot_level'] = $omega_robot_level;
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_item'] = $robot['robot_item'];
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = mmrpg_prototype_generate_abilities($robot, $omega_robot_level, $temp_ability_count, $robot['robot_item']);
    }
    elseif ($robot['robot_class'] == 'boss'){
      $robot['robot_level'] = mt_rand($omega_robot_level, ceil($omega_robot_level * 2));
      $temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_level'] = $robot['robot_level'];
      //$temp_battle_omega['battle_target_player']['player_robots'][$key2]['robot_abilities'] = mmrpg_prototype_generate_abilities($robot, $omega_robot_level, floor($temp_ability_count * 2), $robot['robot_item']);
    }

    // Increment the battle's turn limit based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS; }

    // Increment the battle's point reward based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_points'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERBOSS; }

    // Increment the battle's zenny reward based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_zenny'] += $robot['robot_level'] * MMRPG_SETTINGS_BATTLEZENNY_PERBOSS; }

    // Increment the battle's robot limit based on the class of target robot
    if ($robot['robot_class'] == 'master'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERROBOT; }
    elseif ($robot['robot_class'] == 'mecha'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERMECHA; }
    elseif ($robot['robot_class'] == 'boss'){ $temp_battle_omega['battle_robot_limit'] += MMRPG_SETTINGS_BATTLEROBOTS_PERBOSS; }

  }

  // Increase expected turns if a starforce or darkness battle
  if ($temp_starforce_battle){ $temp_battle_omega['battle_turns'] += round($temp_battle_omega['battle_turns'] * 0.25); }
  elseif ($temp_darkness_battle){ $temp_battle_omega['battle_turns'] += round($temp_battle_omega['battle_turns'] * 0.50); }

  // Fix any zero or invalid battle values
  if ($temp_battle_omega['battle_points'] < 1){ $temp_battle_omega['battle_points'] = 1; }
  else { $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points']); }
  if ($temp_battle_omega['battle_turns'] < 1){ $temp_battle_omega['battle_turns'] = 1; }
  else { $temp_battle_omega['battle_turns'] = ceil($temp_battle_omega['battle_turns']); }
  if ($temp_battle_omega['battle_robot_limit'] < 1){ $temp_battle_omega['battle_robot_limit'] = 1; }
  else { $temp_battle_omega['battle_robot_limit'] = ceil($temp_battle_omega['battle_robot_limit']); }

  // Recollect the option robots
  $temp_option_robot = $this_robot_index[$temp_option_robot['robot_token']];
  $temp_option_robot2 = $this_robot_index[$temp_option_robot2['robot_token']];

  // Reverse the order of the robots in battle
  $temp_battle_omega['battle_target_player']['player_robots'] = array_reverse($temp_battle_omega['battle_target_player']['player_robots']);
  $temp_first_robot = array_shift($temp_battle_omega['battle_target_player']['player_robots']);
  shuffle($temp_battle_omega['battle_target_player']['player_robots']);
  array_unshift($temp_battle_omega['battle_target_player']['player_robots'], $temp_first_robot);

  // Empty the robot rewards array if not allowed
  if (!$this_unlock_robots){ $temp_battle_omega['battle_rewards']['robots'] = array(); }
  // Empty the ability rewards array if not allowed
  if (!$this_unlock_abilities){ $temp_battle_omega['battle_rewards']['abilities'] = array(); }

  // Define the number of abilities and robots left to unlock and start at zero
  $this_unlock_robots_count = count($temp_battle_omega['battle_rewards']['robots']);
  $this_unlock_abilities_count = count($temp_battle_omega['battle_rewards']['abilities']);

  // Loop through the omega battle robot rewards and update the robot levels there too
  if (!empty($temp_battle_omega['battle_rewards']['robots'])){
    foreach ($temp_battle_omega['battle_rewards']['robots'] AS $key2 => $robot){
      // Update the robot level and battle points plus turns
      $temp_battle_omega['battle_rewards']['robots'][$key2]['level'] = $omega_robot_level;
      // Remove if this robot is already unlocked
      if (mmrpg_prototype_robot_unlocked(false, $robot['token'])){ $this_unlock_robots_count -= 1; }
    }
  }

  // Loop through the omega battle ability rewards and update the ability levels there too
  if (!empty($temp_battle_omega['battle_rewards']['abilities'])){
    foreach ($temp_battle_omega['battle_rewards']['abilities'] AS $key2 => $ability){
      // Remove if this ability is already unlocked
      if (mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, $ability['token'])){ $this_unlock_abilities_count -= 1; }
    }
  }

  // Check to see if we should be adding a field star to this battle
  if ($temp_starforce_battle){
    // Generate the necessary field star variables and add them to the battle data
    $temp_field_star = array();
    $temp_field_star['star_name'] = $temp_battle_omega['battle_field_base']['field_name'];
    $temp_field_star['star_token'] = $temp_field_star_token;
    $temp_field_star['star_kind'] = 'fusion';
    $temp_field_star['star_type'] = !empty($temp_battle_omega['battle_field_base']['field_type']) ? $temp_battle_omega['battle_field_base']['field_type'] : 'none';
    $temp_field_star['star_type2'] = !empty($temp_battle_omega['battle_field_base']['field_type2']) ? $temp_battle_omega['battle_field_base']['field_type2'] : 'none';
    $temp_field_star['star_field'] = $temp_option_field['field_token'];
    $temp_field_star['star_field2'] = $temp_option_field2['field_token'];
    $temp_field_star['star_player'] = $this_prototype_data['this_player_token'];
    $temp_field_star['star_date'] = time();
    $temp_battle_omega['values']['field_star'] = $temp_field_star;
    $temp_battle_omega['battle_target_player']['player_starforce'] = array();
    if ($temp_field_star['star_type'] == $temp_field_star['star_type2']){
      $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type']] = 2;
    } else {
      $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type']] = 1;
      $temp_battle_omega['battle_target_player']['player_starforce'][$temp_field_star['star_type2']] = 1;
    }

  }

  // Check to see if we should be adding darkness to this battle
  if ($temp_darkness_battle){
    // Generate the necessary dark tower variables and add them to the battle data
    $temp_battle_omega['flags']['dark_tower'] = true;
  }

  // Update the battle description based on what we've calculated
  $temp_description_target_robots = $temp_option_robot['robot_name'].' and '.$temp_option_robot2['robot_name'];
  if (!empty($this_unlock_abilities_count)){
    $temp_battle_omega['battle_description'] = 'Defeat '.$temp_description_target_robots.' and download their special weapons!';
    $temp_battle_omega['battle_description2'] = 'Once we\'ve acquired them, we may be able to equip the abilities to other robots...';
  } elseif (!empty($this_unlock_robots_count)){
    $temp_battle_omega['battle_description'] = 'Defeat '.$temp_description_target_robots.' and download their robot data!';
    $temp_battle_omega['battle_description2'] = 'If we use only Neutral type abilities on the targets we may be able to save them...';
  } elseif ($temp_starforce_battle){
    $temp_battle_omega['battle_description'] = 'Defeat the starforce boosted '.$temp_description_target_robots.' and collect their Fusion Star! ';
    $temp_battle_omega['battle_description2'] = 'The '.ucfirst($temp_option_field['field_type']).' type energy appears to have attracted more robots to the field...';
  } elseif ($temp_darkness_battle){
    $temp_battle_omega['battle_description'] = 'Defeat the darkness boosted '.$temp_description_target_robots.' and destroy their Dark Tower! ';
    $temp_battle_omega['battle_description2'] = 'The negative energy appears to have attracted an army of dark elements to the field...';
  } else {
    $temp_battle_omega['battle_description'] = 'Defeat '.$temp_description_target_robots.'!';
  }

  // If this battle has been completed already, decrease the points
  //if ($temp_battle_omega['battle_complete']){ $temp_battle_omega['battle_points'] = ceil($temp_battle_omega['battle_points'] * 0.10); }
  // Add some random item drops to the starter battle
  $temp_battle_omega['battle_rewards']['items'] = array(
    //array('chance' => 1, 'token' => 'item-energy-tank'),
    //array('chance' => 1, 'token' => 'item-weapon-tank')
    );

  // Return the generated battle data
  return $temp_battle_omega;

}
?>