<?
//$this->events_create(false, false, 'DEBUG', 'Battle complete trigger triggered!');

// Return false if anything is missing
if (empty($this_player) || empty($this_robot)){ return false; }
if (empty($target_player) || empty($target_robot)){ return false; }

// Return true if the battle status is already complete
if ($this->battle_status == 'complete'){ return true; }

// Update the battle status to complete
$this->battle_status = 'complete';
if ($this->battle_result == 'pending'){
  $this->battle_result = $target_player->player_side == 'right' ? 'victory' : 'defeat';
  $this->update_session();
  $event_options = array();
  if ($this->battle_result == 'victory'){
    $event_options['event_flag_victory'] = true;
  }
  elseif ($this->battle_result == 'defeat'){
    $event_options['event_flag_defeat'] = true;
  }
  $this->events_create(false, false, '', '', $event_options);
}

// -- CALCULATE REWARDS -- //

// Define variables for the human's rewards in this scenario
$temp_human_token = $target_player->player_side == 'left' ? $target_player->player_token : $this_player->player_token;
$temp_human_info = $target_player->player_side == 'left' ? $target_player->export_array() : $this_player->export_array();
$temp_human_rewards = array();
$temp_human_rewards['battle_points'] = 0;
$temp_human_rewards['battle_zenny'] = 0;
$temp_human_rewards['battle_complete'] = isset($_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this->battle_token]['battle_count'] : 0;
$temp_human_rewards['battle_failure'] = isset($_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this->battle_token]['battle_count'] : 0;
$temp_human_rewards['checkpoint'] = 'start: ';

// Calculate the base point and zenny rewards for this battle
$temp_reward_points_base = !empty($this->battle_points) ? $this->battle_points : 0;
$temp_reward_zenny_base = !empty($this->battle_zenny) ? $this->battle_zenny : 0;

// Default the bonus to zero and calulate based on turns
$temp_turn_bonus = 0;
if ($this->counters['battle_turn'] < $this->battle_turns){ $temp_turn_bonus = round(($this->battle_turns - $this->counters['battle_turn']) * 10); }
elseif ($this->counters['battle_turn'] > $this->battle_turns){ $temp_turn_bonus = round(($this->counters['battle_turn'] - $this->battle_turns) * 10) * -1; }

// Default the bonus to zero and calulate based on turns
$temp_robot_bonus = 0;
if ($temp_human_info['counters']['robots_masters_total'] < $this->battle_robot_limit){ $temp_robot_bonus = round(($this->battle_robot_limit - $temp_human_info['counters']['robots_masters_total']) * 10); }
elseif ($temp_human_info['counters']['robots_masters_total'] > $this->battle_robot_limit){ $temp_robot_bonus = $temp_robot_bonus = round(($temp_human_info['counters']['robots_masters_total'] - $this->battle_robot_limit) * 10) * -1; }

// Calculate the bonus points and zenny for the turns
$temp_turn_bonus_points = (int)($temp_reward_points_base * ($temp_turn_bonus / 100));
$temp_turn_bonus_zenny = (int)($temp_reward_zenny_base * ($temp_turn_bonus / 100));

// Calculate the bonus points and zenny for the turns
$temp_robot_bonus_points = (int)($temp_reward_points_base * ($temp_robot_bonus / 100));
$temp_robot_bonus_zenny = (int)($temp_reward_zenny_base * ($temp_robot_bonus / 100));

// Calculate the final reward points based on above
if ($this->battle_result == 'victory'){
  $temp_reward_points_final = $temp_reward_points_base + $temp_turn_bonus_points + $temp_robot_bonus_points;
  $temp_reward_zenny_final = $temp_reward_zenny_base + $temp_turn_bonus_zenny + $temp_robot_bonus_zenny;
  if ($temp_reward_points_final < 0){ $temp_reward_points_final = 0; }
  if ($temp_reward_zenny_final < 0){ $temp_reward_zenny_final = 0; }
} else {
  $temp_reward_points_final = 0;
  $temp_reward_zenny_final = 0;
}

// Define the number of stars to show for this mission
$temp_rating_stars = 0;
if ($this->battle_result == 'victory'){
  $temp_rating_stars += 1;
  if ($temp_turn_bonus >= 0){ $temp_rating_stars += 1; }
  if ($temp_robot_bonus >= 0){ $temp_rating_stars += 1; }
  if (empty($temp_human_info['counters']['robots_disabled'])){ $temp_rating_stars += 1; }
  if (empty($temp_human_info['counters']['items_used_this_battle'])){ $temp_rating_stars += 1; }
}
// Generate the markup for this stars
$temp_rating_stars_markup = '';
for ($i = 1; $i <= 5; $i++){ $temp_rating_stars_markup .= $i <= $temp_rating_stars ? '&#9733;' : '&#9734;'; }


// (HUMAN) TARGET DEFEATED
// Check if the target was the human character
if ($target_player->player_side == 'left'){

  // Increment the main game's points total with the battle points
  $_SESSION['GAME']['counters']['battle_points'] += $temp_reward_points_final;
  $_SESSION['GAME']['counters']['battle_zenny'] += $temp_reward_zenny_final;

  // Increment this player's points total with the battle points
  if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'] = 0; }
  if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_zenny'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_zenny'] = 0; }
  $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'] += $temp_reward_points_final;
  $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_zenny'] += $temp_reward_zenny_final;

  // Update the global variable with the points reward
  $temp_human_rewards['battle_points'] = $temp_reward_points_final;
  $temp_human_rewards['battle_zenny'] = $temp_reward_zenny_final;

  // Update the GAME session variable with the failed battle token
  if ($this->battle_counts){
    $bak_session_array = isset($_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token]) ? $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token] : array();
    $new_session_array = array('battle_token' => $this->battle_token, 'battle_count' => 0, 'battle_level' => 0);
    if (!empty($bak_session_array['battle_count'])){ $new_session_array['battle_count'] = $bak_session_array['battle_count']; }
    if (!empty($bak_session_array['battle_level'])){ $new_session_array['battle_level'] = $bak_session_array['battle_level']; }
    $new_session_array['battle_level'] = $this->battle_level;
    $new_session_array['battle_count']++;
    $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token] = $new_session_array;
    $temp_human_rewards['battle_failure'] = $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token]['battle_count'];
  }

}


// NON-INVISIBLE PLAYER DEFEATED
// Display the defeat message for the target character if not default/hidden
if ($target_player->player_token != 'player'){

  // (HUMAN) TARGET DEFEATED BY (INVISIBLE/COMPUTER)
  // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
  if ($this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left' && $this_robot->robot_class != 'mecha'){

    // Calculate how many points the other player is rewarded for winning
    $target_player_robots = $target_player->values['robots_disabled'];
    $target_player_robots_count = count($target_player_robots);
    $other_player_points = 0;
    $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
    foreach ($target_player_robots AS $disabled_robotinfo){ $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER; }

    // Collect the battle points from the function
    $other_battle_points_modded = $this->calculate_battle_points($target_player, $other_player_points, $other_player_turns, $this->battle_robot_limit);

    // Create the victory event for the target player
    $this_robot->robot_frame = 'victory';
    $this_robot->update_session();
    $event_header = $this_robot->robot_name.' Undefeated';
    $event_body = '';
    $event_body .= $this_robot->print_robot_name().' could not be defeated! ';
    $event_body .= '<br />';
    $event_options = array();
    $event_options['console_show_this_robot'] = true;
    $event_options['console_show_target'] = false;
    $event_options['event_flag_defeat'] = true;
    $event_options['this_header_float'] = $event_options['this_body_float'] = $this_robot->player->player_side;
    if ($this_robot->robot_token != 'robot'
      && isset($this_robot->robot_quotes['battle_victory'])){
      $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
      $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
      $event_body .= $this_robot->print_robot_quote('battle_victory', $this_find, $this_replace);
    }
    $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

  }

  $target_player->player_frame = 'defeat';
  $target_robot->update_session();
  $target_player->update_session();
  $event_header = $target_player->player_name.' Defeated';
  $event_body = $target_player->print_player_name().' was defeated'.($target_player->player_side == 'left' ? '&hellip;' : '!').' ';
  $event_body .= '<br />';
  $event_options = array();
  $event_options['console_show_this_player'] = true;
  $event_options['console_show_target'] = false;
  $event_options['event_flag_defeat'] = true;
  $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
  if ($target_player->player_token != 'player'
    && isset($target_player->player_quotes['battle_defeat'])){
    $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
    $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $target_robot->robot_name);
    $this_quote_text = str_replace($this_find, $this_replace, $target_player->player_quotes['battle_defeat']);
    $event_body .= $target_player->print_player_quote('battle_defeat', $this_find, $this_replace);
  }
  $this->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

  // (HUMAN) TARGET DEFEATED BY (GHOST/COMPUTER)
  // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
  if ($this_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left'){

    // Calculate how many points the other player is rewarded for winning
    $target_player_robots = $target_player->values['robots_disabled'];
    $target_player_robots_count = count($target_player_robots);
    $other_player_points = 0;
    $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
    foreach ($target_player_robots AS $disabled_robotinfo){ $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERROBOT * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER; }

    // Collect the battle points from the function
    $other_battle_points_modded = $this->calculate_battle_points($target_player, $other_player_points, $other_player_turns, $this->battle_robot_limit);

    // Create the victory event for the target player
    $this_player->player_frame = 'victory';
    $target_robot->update_session();
    $this_player->update_session();
    $event_header = $this_player->player_name.' Victorious';
    $event_body = $this_player->print_player_name().' was victorious! ';
    $event_body .= $this_player->print_player_name().' collects <span class="recovery_amount">'.number_format($other_battle_points_modded, 0, '.', ',').'</span> battle points!';
    $event_body .= '<br />';
    $event_options = array();
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target'] = false;
    $event_options['event_flag_defeat'] = true;
    $event_options['this_header_float'] = $event_options['this_body_float'] = $this_player->player_side;
    if ($this_player->player_token != 'player'
      && isset($this_player->player_quotes['battle_victory'])){
      $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
      $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
      $event_body .= $this_player->print_player_quote('battle_victory', $this_find, $this_replace);
    }
    $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

    // Create the temp robot sprites for the database
    $temp_this_player_robots = array();
    $temp_target_player_robots = array();
    foreach ($target_player->player_robots AS $key => $info){ $temp_this_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
    foreach ($this_player->player_robots AS $key => $info){ $temp_target_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
    $temp_this_player_robots = !empty($temp_this_player_robots) ? implode(',', $temp_this_player_robots) : '';
    $temp_target_player_robots = !empty($temp_target_player_robots) ? implode(',', $temp_target_player_robots) : '';
    // Update the database with these pending rewards for each player
    global $DB;
    $DB->insert('mmrpg_battles', array(
      'battle_field_name' => $this->battle_field->field_name,
      'battle_field_background' => $this->battle_field->field_background,
      'battle_field_foreground' => $this->battle_field->field_foreground,
      'battle_turns' => $this->counters['battle_turn'],
      'this_user_id' => $target_player->player_id,
      'this_player_token' => $target_player->player_token,
      'this_player_robots' => $temp_this_player_robots,
      'this_player_points' => $temp_reward_points_base,
      'this_player_result' => 'defeat',
      'this_reward_pending' => 0,
      'target_user_id' => $this_player->player_id,
      'target_player_token' => $this_player->player_token,
      'target_player_robots' => $temp_target_player_robots,
      'target_player_points' => $other_battle_points_modded,
      'target_player_result' => 'victory',
      'target_reward_pending' => 1
      ));

  }

}


// (HUMAN) TARGET DEFEATED BY (COMPUTER)
// Check if the target was the human character (and they LOST)
if ($target_player->player_side == 'left'){

    // Collect the robot info array
    $temp_player_info = $target_player->export_array();

    // Collect or define the player points and player rewards variables
    $temp_player_token = $temp_player_info['player_token'];
    $temp_player_points = mmrpg_prototype_player_points($temp_player_info['player_token']);
    $temp_player_rewards = mmrpg_prototype_player_rewards($temp_player_info['player_token']); //!empty($temp_player_info['player_rewards']) ? $temp_player_info['player_rewards'] : array();

    // -- ABILITY REWARDS for HUMAN PLAYER -- //

    // Loop through the ability rewards for this robot if set
    if (!empty($temp_player_rewards['abilities']) && empty($_SESSION['GAME']['DEMO'])){
      $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
      foreach ($temp_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

        // If this ability is already unlocked, continue
        if (mmrpg_prototype_ability_unlocked($target_player->player_token, false, $ability_reward_info['token'])){ continue; }

        // Check if the required level has been met by this robot
        if ($temp_player_points >= $ability_reward_info['points'] && empty($_SESSION['GAME']['DEMO'])){

          // Collect the ability info from the index
          $ability_info = mmrpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
          // Create the temporary ability object for event creation
          $temp_ability = new mmrpg_ability($this, $target_player, $target_robot, $ability_info);

          // Collect or define the ability variables
          $temp_ability_token = $ability_info['ability_token'];

          // Display the robot reward message markup
          $event_header = $ability_info['ability_name'].' Unlocked';
          $event_body = mmrpg_battle::random_positive_word().' <span class="player_name">'.$temp_player_info['player_name'].'</span> unlocked new ability data!<br />';
          $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
          $event_options = array();
          $event_options['console_show_target'] = false;
          $event_options['this_header_float'] = $target_player->player_side;
          $event_options['this_body_float'] = $target_player->player_side;
          $event_options['this_ability'] = $temp_ability;
          $event_options['this_ability_image'] = 'icon';
          $event_options['event_flag_victory'] = true;
          $event_options['console_show_this_player'] = false;
          $event_options['console_show_this_robot'] = false;
          $event_options['console_show_this_ability'] = true;
          $event_options['canvas_show_this_ability'] = false;
          $target_player->player_frame = $ability_reward_key % 2 == 0 ? 'victory' : 'taunt';
          $target_player->update_session();
          $temp_ability->ability_frame = 'base';
          $temp_ability->update_session();
          $this->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

          // Automatically unlock this ability for use in battle
          $this_reward = array('ability_token' => $temp_ability_token);
          $show_event = !mmrpg_prototype_ability_unlocked('', '', $temp_ability_token) ? true : false;
          mmrpg_game_unlock_ability($temp_player_info, false, $this_reward, $show_event);

        }

      }
    }


}

// (COMPUTER) TARGET DEFEATED BY (HUMAN)
// Check if this player was the human player (and they WON)
if ($this_player->player_side == 'left'){

  // Increment the main game's points total with the battle points
  $_SESSION['GAME']['counters']['battle_points'] += $temp_reward_points_final;
  $_SESSION['GAME']['counters']['battle_zenny'] += $temp_reward_zenny_final;

  // Reference the number of points this player gets
  $this_player_points = $temp_reward_points_final;
  $this_player_zenny = $temp_reward_zenny_final;

  // Increment this player's points total with the battle points
  $player_token = $this_player->player_token;
  $player_info = $this_player->export_array();
  if (!isset($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'])){ $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'] = 0; }
  $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'] += $this_player_points;
  if (!isset($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_zenny'])){ $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_zenny'] = 0; }
  $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_zenny'] += $this_player_zenny;

  // Update the global variable with the points reward
  $temp_human_rewards['battle_points'] = $this_player_points;
  $temp_human_rewards['battle_zenny'] = $this_player_zenny;

  // Display the win message for this player with battle points
  $this_robot->robot_frame = 'victory';
  $this_player->player_frame = 'victory';
  $this_robot->update_session();
  $this_player->update_session();
  $event_header = $this_player->player_name.' Victorious';
  $event_body = $this_player->print_player_name().' was victorious! ';
  $event_body .= 'The '.($target_player->counters['robots_disabled'] > 1 ? 'targets were' : 'target was').' defeated!';
  $event_body .= '<br />';
  $event_options = array();
  $event_options['console_show_this_player'] = true;
  $event_options['console_show_target'] = false;
  $event_options['event_flag_victory'] = true;
  $event_options['this_header_float'] = $event_options['this_body_float'] = $this_player->player_side;
  if ($this_player->player_token != 'player'
    && isset($this_player->player_quotes['battle_victory'])){
    $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
    $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
    $event_body .= $this_player->print_player_quote('battle_victory', $this_find, $this_replace);
  }
  $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

  // If this was a PLAYER BATTLE and the human user won against them (this/human/victory | target/computer/defeat)
  if ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID && $this_player->player_side == 'left'){

    // Create the temp robot sprites for the database
    $temp_this_player_robots = array();
    $temp_target_player_robots = array();
    foreach ($this_player->player_robots AS $key => $info){ $temp_this_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
    foreach ($target_player->player_robots AS $key => $info){ $temp_target_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
    $temp_this_player_robots = !empty($temp_this_player_robots) ? implode(',', $temp_this_player_robots) : '';
    $temp_target_player_robots = !empty($temp_target_player_robots) ? implode(',', $temp_target_player_robots) : '';
    // Collect the userinfo for the target player
    $target_player_userinfo = $DB->get_array("SELECT user_name, user_name_clean, user_name_public FROM mmrpg_users WHERE user_id = {$target_player->player_id};");
    if (!isset($_SESSION['LEADERBOARD']['player_targets_defeated'])){ $_SESSION['LEADERBOARD']['player_targets_defeated'] = array(); }
    $_SESSION['LEADERBOARD']['player_targets_defeated'][] = $target_player_userinfo['user_name_clean'];
    // Update the database with these pending rewards for each player
    global $DB;
    $DB->insert('mmrpg_battles', array(
      'battle_field_name' => $this->battle_field->field_name,
      'battle_field_background' => $this->battle_field->field_background,
      'battle_field_foreground' => $this->battle_field->field_foreground,
      'battle_turns' => $this->counters['battle_turn'],
      'this_user_id' => $this_player->player_id,
      'this_player_token' => $this_player->player_token,
      'this_player_robots' => $temp_this_player_robots,
      'this_player_points' => $this_player_points,
      'this_player_result' => 'victory',
      'this_reward_pending' => 0,
      'target_user_id' => $target_player->player_id,
      'target_player_token' => $target_player->player_token,
      'target_player_robots' => $temp_target_player_robots,
      'target_player_points' => $temp_reward_points_base,
      'target_player_result' => 'defeat',
      'target_reward_pending' => 1
      ));

  }


  /*
   * PLAYER REWARDS
   */

  // Check if the the player was a human character
  if ($this_player->player_side == 'left'){


    // Collect the robot info array
    $temp_player_info = $this_player->export_array();

    // Collect or define the player points and player rewards variables
    $temp_player_token = $temp_player_info['player_token'];
    $temp_player_points = mmrpg_prototype_player_points($temp_player_info['player_token']);
    $temp_player_rewards = !empty($temp_player_info['player_rewards']) ? $temp_player_info['player_rewards'] : array();

    // -- ABILITY REWARDS for HUMAN PLAYER -- //

    // Loop through the ability rewards for this player if set
    if (!empty($temp_player_rewards['abilities']) && empty($_SESSION['GAME']['DEMO'])){
      $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
      foreach ($temp_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

        // If this ability is already unlocked, continue
        if (mmrpg_prototype_ability_unlocked($this_player->player_token, false, $ability_reward_info['token'])){ continue; }

        // Check if the required level has been met by this robot
        if ($temp_player_points >= $ability_reward_info['points']){

          // Collect the ability info from the index
          $ability_info = mmrpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
          // Create the temporary ability object for event creation
          $temp_ability = new mmrpg_ability($this, $this_player, $this_robot, $ability_info);

          // Collect or define the ability variables
          $temp_ability_token = $ability_info['ability_token'];

          // Display the robot reward message markup
          $event_header = $ability_info['ability_name'].' Unlocked';
          $event_body = mmrpg_battle::random_positive_word().' <span class="player_name">'.$temp_player_info['player_name'].'</span> unlocked new ability data!<br />';
          $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
          $event_options = array();
          $event_options['console_show_target'] = false;
          $event_options['this_header_float'] = $this_player->player_side;
          $event_options['this_body_float'] = $this_player->player_side;
          $event_options['this_ability'] = $temp_ability;
          $event_options['this_ability_image'] = 'icon';
          $event_options['event_flag_victory'] = true;
          $event_options['console_show_this_player'] = false;
          $event_options['console_show_this_robot'] = false;
          $event_options['console_show_this_ability'] = true;
          $event_options['canvas_show_this_ability'] = false;
          $this_player->player_frame = $ability_reward_key % 2 == 0 ? 'victory' : 'taunt';
          $this_player->update_session();
          $this_robot->robot_frame = $ability_reward_key % 2 == 0 ? 'taunt' : 'base';
          $this_robot->update_session();
          $temp_ability->ability_frame = 'base';
          $temp_ability->update_session();
          $this->events_create($this_robot, $this_robot, $event_header, $event_body, $event_options);

          // Automatically unlock this ability for use in battle
          $this_reward = array('ability_token' => $temp_ability_token);
          $show_event = !mmrpg_prototype_ability_unlocked('', '', $temp_ability_token) ? true : false;
          mmrpg_game_unlock_ability($temp_player_info, false, $this_reward, $show_event);

        }

      }
    }

  }

}


/*
 * BATTLE REWARDS
 */

// Collect or define the player variables
$this_player_token = $this_player->player_token;
$this_player_info = $this_player->export_array();

// Collect or define the target player variables
$target_player_token = $target_player->player_token;
$target_player_info = $target_player->export_array();

// Check if this player was the human player
if ($this_player->player_side == 'left'){

  // Update the GAME session variable with the completed battle token
  if ($this->battle_counts){
    // Back up the current session array for this battle complete counter
    $bak_session_array = isset($_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token]) ? $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token] : array();
    // Create the new session array from scratch to ensure all values exist
    $new_session_array = array(
      'battle_token' => $this->battle_token,
      'battle_count' => 0,
      'battle_min_level' => 0,
      'battle_max_level' => 0,
      'battle_min_turns' => 0,
      'battle_max_turns' => 0,
      'battle_min_points' => 0,
      'battle_max_points' => 0,
      'battle_min_robots' => 0,
      'battle_max_robots' => 0
      );
    // Recollect applicable battle values from the backup session array
    if (!empty($bak_session_array['battle_count'])){ $new_session_array['battle_count'] = $bak_session_array['battle_count']; }
    if (!empty($bak_session_array['battle_level'])){ $new_session_array['battle_min_level'] = $bak_session_array['battle_level']; } // LEGACY
    if (!empty($bak_session_array['battle_min_level'])){ $new_session_array['battle_min_level'] = $bak_session_array['battle_min_level']; }
    if (!empty($bak_session_array['battle_max_level'])){ $new_session_array['battle_max_level'] = $bak_session_array['battle_max_level']; }
    if (!empty($bak_session_array['battle_min_turns'])){ $new_session_array['battle_min_turns'] = $bak_session_array['battle_min_turns']; }
    if (!empty($bak_session_array['battle_max_turns'])){ $new_session_array['battle_max_turns'] = $bak_session_array['battle_max_turns']; }
    if (!empty($bak_session_array['battle_min_points'])){ $new_session_array['battle_min_points'] = $bak_session_array['battle_min_points']; }
    if (!empty($bak_session_array['battle_max_points'])){ $new_session_array['battle_max_points'] = $bak_session_array['battle_max_points']; }
    if (!empty($bak_session_array['battle_min_robots'])){ $new_session_array['battle_min_robots'] = $bak_session_array['battle_min_robots']; }
    if (!empty($bak_session_array['battle_max_robots'])){ $new_session_array['battle_max_robots'] = $bak_session_array['battle_max_robots']; }
    // Update and/or increment the appropriate battle variables in the new array
    if ($new_session_array['battle_max_level'] == 0 || $this->battle_level > $new_session_array['battle_max_level']){ $new_session_array['battle_max_level'] = $this->battle_level; }
    if ($new_session_array['battle_min_level'] == 0 || $this->battle_level < $new_session_array['battle_min_level']){ $new_session_array['battle_min_level'] = $this->battle_level; }
    if ($new_session_array['battle_max_turns'] == 0 || $this->counters['battle_turn'] > $new_session_array['battle_max_turns']){ $new_session_array['battle_max_turns'] = $this->counters['battle_turn']; }
    if ($new_session_array['battle_min_turns'] == 0 || $this->counters['battle_turn'] < $new_session_array['battle_min_turns']){ $new_session_array['battle_min_turns'] = $this->counters['battle_turn']; }
    if ($new_session_array['battle_max_points'] == 0 || $temp_human_rewards['battle_points'] > $new_session_array['battle_max_points']){ $new_session_array['battle_max_points'] = $temp_human_rewards['battle_points']; }
    if ($new_session_array['battle_min_points'] == 0 || $temp_human_rewards['battle_points'] < $new_session_array['battle_min_points']){ $new_session_array['battle_min_points'] = $temp_human_rewards['battle_points']; }
    if ($new_session_array['battle_max_robots'] == 0 || $this_player->counters['robots_total'] > $new_session_array['battle_max_robots']){ $new_session_array['battle_max_robots'] = $this_player->counters['robots_total']; }
    if ($new_session_array['battle_min_robots'] == 0 || $this_player->counters['robots_total'] < $new_session_array['battle_min_robots']){ $new_session_array['battle_min_robots'] = $this_player->counters['robots_total']; }
    $new_session_array['battle_count']++;
    // Update the session variable for this player with the updated battle values
    $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token] = $new_session_array;
    $temp_human_rewards['battle_complete'] = $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token]['battle_count'];
  }

  // Refresh the player info array
  $this_player_info = $this_player->export_array();

  // ROBOT REWARDS

  // Loop through any robot rewards for this battle
  $this_robot_rewards = !empty($this->battle_rewards['robots']) ? $this->battle_rewards['robots'] : array();
  if (!empty($this_robot_rewards) && empty($_SESSION['GAME']['DEMO'])){
    foreach ($this_robot_rewards AS $robot_reward_key => $robot_reward_info){

      // If this robot has already been unlocked by anyone, continue
      if (mmrpg_prototype_robot_unlocked(false, $robot_reward_info['token'])){ continue; }

      // Collect the robot info from the index
      $robot_info = mmrpg_robot::get_index_info($robot_reward_info['token']);
      // Search this player's base robots for the robot ID
      $robot_info['robot_id'] = 0;
      foreach ($this_player->player_base_robots AS $base_robot){
        if ($robot_info['robot_token'] == $base_robot['robot_token']){
          $robot_info['robot_id'] = $base_robot['robot_id'];
          break;
        }
      }
      // Create the temporary robot object for event creation
      $temp_robot = new mmrpg_robot($this, $this_player, $robot_info);

      // Collect or define the robot points and robot rewards variables
      $this_robot_token = $robot_reward_info['token'];
      $this_robot_level = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
      $this_robot_experience = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
      $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

      // Automatically unlock this robot for use in battle
      $this_reward = $robot_info;
      $this_reward['robot_level'] = $this_robot_level;
      $this_reward['robot_experience'] = $this_robot_experience;
      mmrpg_game_unlock_robot($this_player_info, $this_reward, true, true);

    }
  }

  // ABILITY REWARDS

  // Loop through any ability rewards for this battle
  $this_ability_rewards = !empty($this->battle_rewards['abilities']) ? $this->battle_rewards['abilities'] : array();
  if (!empty($this_ability_rewards) && empty($_SESSION['GAME']['DEMO'])){
    $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    foreach ($this_ability_rewards AS $ability_reward_key => $ability_reward_info){

      // Collect the ability info from the index
      $ability_info = mmrpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
      // Create the temporary robot object for event creation
      $temp_ability = new mmrpg_ability($this, $this_player, $this_robot, $ability_info);

      // Collect or define the robot points and robot rewards variables
      $this_ability_token = $ability_info['ability_token'];

      // Now loop through all active robots on this side of the field
      foreach ($this_player_info['values']['robots_active'] AS $temp_key => $temp_info){
        // If this robot is a mecha, skip it!
        if (!empty($temp_info['robot_class']) && $temp_info['robot_class'] == 'mecha'){ continue; }
        // Equip this ability to the robot is there was a match found
        if (mmrpg_robot::has_ability_compatibility($temp_info['robot_token'], $ability_info['ability_token'])){
          if (!isset( $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'] )){ $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'] = array(); }
          if (count($_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities']) < 8){ $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'][$ability_info['ability_token']] = array('ability_token' => $ability_info['ability_token']); }
        }
      }

      // If this ability has already been unlocked by the player, continue
      if (mmrpg_prototype_ability_unlocked($this_player_token, false, $ability_reward_info['token'])){ continue; }

      // Automatically unlock this ability for use in battle
      $this_reward = array('ability_token' => $this_ability_token);
      $show_event = !mmrpg_prototype_ability_unlocked('', '', $this_ability_token) ? true : false;
      mmrpg_game_unlock_ability($this_player_info, false, $this_reward, $show_event);

      // Display the robot reward message markup
      $event_header = $ability_info['ability_name'].' Unlocked';
      $event_body = mmrpg_battle::random_positive_word().' <span class="player_name">'.$this_player_info['player_name'].'</span> unlocked new ability data!<br />';
      $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
      $event_options = array();
      $event_options['console_show_target'] = false;
      $event_options['this_header_float'] = $this_player->player_side;
      $event_options['this_body_float'] = $this_player->player_side;
      $event_options['this_ability'] = $temp_ability;
      $event_options['this_ability_image'] = 'icon';
      $event_options['console_show_this_player'] = false;
      $event_options['console_show_this_robot'] = false;
      $event_options['console_show_this_ability'] = true;
      $event_options['canvas_show_this_ability'] = false;
      $this_player->player_frame = 'victory';
      $this_player->update_session();
      $temp_ability->ability_frame = 'base';
      $temp_ability->update_session();
      $this->events_create($this_robot, false, $event_header, $event_body, $event_options);

    }
  }




} // end of BATTLE REWARDS

// Check if there is a field star for this stage to collect
if ($this->battle_result == 'victory' && !empty($this->values['field_star'])){

  // Collect the field star data for this battle
  $temp_field_star = $this->values['field_star'];

  // Print out the event for collecting the new field star
  $temp_name_markup = '<span class="field_name field_type field_type_'.(!empty($temp_field_star['star_type']) ? $temp_field_star['star_type'] : 'none').(!empty($temp_field_star['star_type2']) ? '_'.$temp_field_star['star_type2'] : '').'">'.$temp_field_star['star_name'].' Star</span>';
  $temp_event_header = $this_player->player_name.'&#39;s '.ucfirst($temp_field_star['star_kind']).' Star';
  $temp_event_body = $this_player->print_player_name().' collected the '.$temp_name_markup.'!<br />';
  $temp_event_body .= 'The new '.ucfirst($temp_field_star['star_kind']).' Star amplifies your Star Force!';
  $temp_event_options = array();
  $temp_event_options['console_show_this_player'] = false;
  $temp_event_options['console_show_target_player'] = false;
  $temp_event_options['console_show_this_robot'] = false;
  $temp_event_options['console_show_target_robot'] = false;
  $temp_event_options['console_show_this_ability'] = false;
  $temp_event_options['console_show_this'] = true;
  $temp_event_options['console_show_this_star'] = true;
  $temp_event_options['this_header_float'] = $temp_event_options['this_body_float'] = $this_player->player_side;
  $temp_event_options['this_star'] = $temp_field_star;
  $temp_event_options['this_ability'] = false;
  $this->events_create(false, false, $temp_event_header, $temp_event_body, $temp_event_options);

  // Update the session with this field star data
  $_SESSION['GAME']['values']['battle_stars'][$temp_field_star['star_token']] = $temp_field_star;

  // DEBUG DEBUG
  //$this->events_create($this_robot, $target_robot, 'DEBUG FIELD STAR', 'You got a field star! The field star names '.implode(' | ', $temp_field_star));

}

// If this robot's image has been changed, reveert it back to what it was
if ($this_robot->robot_core == 'copy'){
  unset($this_robot->robot_image_overlay['copy_type1']);
  unset($this_robot->robot_image_overlay['copy_type2']);
  $this_robot->update_session();
}

// If the target robot's image has been changed, reveert it back to what it was
if ($target_robot->robot_core == 'copy'){
  unset($target_robot->robot_image_overlay['copy_type1']);
  unset($target_robot->robot_image_overlay['copy_type2']);
  $target_robot->update_session();
}

// Define the first event body markup, regardless of player type
$first_event_header = $this->battle_name.($this->battle_result == 'victory' ? ' Complete' : ' Failure').' <span class="pipe">|</span> '.$this->battle_field->field_name;
if ($this->battle_result == 'victory'){ $first_event_body = 'Mission complete! <span class="pipe">|</span> '.($temp_human_rewards['battle_complete'] > 1 ? mmrpg_battle::random_positive_word().' That&#39;s '.$temp_human_rewards['battle_complete'].' times now! ' : '').mmrpg_battle::random_victory_quote(); }
elseif ($this->battle_result == 'defeat'){ $first_event_body = 'Mission failure. <span class="pipe">|</span> '.($temp_human_rewards['battle_failure'] > 1 ? 'That&#39;s '.$temp_human_rewards['battle_failure'].' times now&hellip; ' : '').mmrpg_battle::random_defeat_quote(); }
$first_event_body .= ' <span class="pipe">|</span> '.$temp_rating_stars_markup.'<br />';

// Print out the table and markup for the battle
$first_event_body .= '<table class="full">';
$first_event_body .= '<colgroup><col width="30%" /><col width="15%" /><col width="15%" /><col width="20%" /><col width="20%" /></colgroup>';
$first_event_body .= '<tbody>';

  $first_event_body .= '<tr>';
    $first_event_body .= '<td class="left">Base Values</td>';
    $first_event_body .= '<td class="center"></td>';
    $first_event_body .= '<td class="center"></td>';
    $first_event_body .= '<td class="right">'.($temp_reward_points_base == 1 ? '1 Point' : number_format($temp_reward_points_base, 0, '.', ',').' Points').'</td>';
    $first_event_body .= '<td class="right">'.($temp_reward_zenny_base == 1 ? '1 Zenny' : number_format($temp_reward_zenny_base, 0, '.', ',').' Zenny').'</td>';
  $first_event_body .= '</tr> ';

  // Only grant bonuses if there was a victory
  if ($this->battle_result == 'victory'){

    // Print out the label and target vs actual turn stats
    $first_event_body .= '<tr>';

      $first_event_body .= '<td class="left">Target Turns</td>';
      $first_event_body .= '<td class="center">'.$this->counters['battle_turn'].' &nbsp;/&nbsp; '.$this->battle_turns.'</td>';

      // Print the markup for the bonus/penalty percent
      if ($temp_turn_bonus > 0){ $first_event_body .= '<td class="center positive">+'.$temp_turn_bonus.'%</td>'; }
      elseif ($temp_turn_bonus < 0){ $first_event_body .= '<td class="center negative">'.$temp_turn_bonus.'%</td>'; }
      else { $first_event_body .= '<td class="center">+0%</td>'; }

      // Print out any mods to the points
      $markup = $temp_turn_bonus_points == 1 ? '1 Point' : number_format($temp_turn_bonus_points, 0, '.', ',').' Points';
      if ($temp_turn_bonus_points > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
      elseif ($temp_turn_bonus_points < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
      else { $first_event_body .= '<td class="right">-</td>'; }

      // Print out any mods to the zenny
      $markup = $temp_turn_bonus_zenny == 1 ? '1 Zenny' : number_format($temp_turn_bonus_zenny, 0, '.', ',').' Zenny';
      if ($temp_turn_bonus_zenny > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
      elseif ($temp_turn_bonus_zenny < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
      else { $first_event_body .= '<td class="right">-</td>'; }

    $first_event_body .= '</tr>';

    // Print out the label and target vs actual robot stats
    $first_event_body .= '<tr>';

      $first_event_body .= '<td class="left">Target Robots</td>';
      $first_event_body .= '<td class="center">'.$this_player_info['counters']['robots_masters_total'].' &nbsp;/&nbsp; '.$this->battle_robot_limit.'</td>';

      // Print the markup for the bonus/penalty percent
      if ($temp_robot_bonus > 0){ $first_event_body .= '<td class="center positive">+'.$temp_robot_bonus.'%</td>'; }
      elseif ($temp_robot_bonus < 0){ $first_event_body .= '<td class="center negative">'.$temp_robot_bonus.'%</td>'; }
      else { $first_event_body .= '<td class="center">+0%</td>'; }

      // Print out any mods to the points
      $markup = $temp_robot_bonus_points == 1 ? '1 Point' : number_format($temp_robot_bonus_points, 0, '.', ',').' Points';
      if ($temp_robot_bonus_points > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
      elseif ($temp_robot_bonus_points < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
      else { $first_event_body .= '<td class="right">-</td>'; }

      // Print out any mods to the zenny
      $markup = $temp_robot_bonus_zenny == 1 ? '1 Zenny' : number_format($temp_robot_bonus_zenny, 0, '.', ',').' Zenny';
      if ($temp_robot_bonus_zenny > 0){ $first_event_body .= '<td class="right positive">+'.$markup.'</td>'; }
      elseif ($temp_robot_bonus_zenny < 0){ $first_event_body .= '<td class="right negative">'.$markup.'</td>'; }
      else { $first_event_body .= '<td class="right">-</td>'; }

    $first_event_body .= '</tr>';

  }
  // Else if defeated show what they were missing
  elseif ($this->battle_result == 'defeat'){

    // Print out the label and target vs actual turn stats
    $first_event_body .= '<tr>';

      $first_event_body .= '<td class="left">Target Turns</td>';
      $first_event_body .= '<td class="center">'.$this->counters['battle_turn'].' &nbsp;/&nbsp; '.$this->battle_turns.'</td>';

      // Print the markup for the empty fields
      $first_event_body .= '<td class="center">-</td>';
      $first_event_body .= '<td class="right">-</td>';
      $first_event_body .= '<td class="right">-</td>';

    $first_event_body .= '</tr>';

    // Print out the label and target vs actual robot stats
    $first_event_body .= '<tr>';

      $first_event_body .= '<td class="left">Target Robots</td>';
      $first_event_body .= '<td class="center">'.$this_player_info['counters']['robots_masters_total'].' &nbsp;/&nbsp; '.$this->battle_robot_limit.'</td>';

      // Print the markup for the empty fields
      $first_event_body .= '<td class="center">-</td>';
      $first_event_body .= '<td class="right">-</td>';
      $first_event_body .= '<td class="right">-</td>';

    $first_event_body .= '</tr>';

  }

  // Print out the final rewards for this battle
  $first_event_body .= '<tr>';
    $first_event_body .= '<td class="left"><strong>Final Rewards</strong></td>';
    $first_event_body .= '<td class="center"></td>';
    $first_event_body .= '<td class="center"></td>';
    $first_event_body .= '<td class="right"><strong>'.($temp_reward_points_final != 1 ? number_format($temp_reward_points_final, 0, '.', ',').' Points' : '1 Point').'</strong></td>';
    $first_event_body .= '<td class="right"><strong>'.($temp_reward_zenny_final != 1 ? number_format($temp_reward_zenny_final, 0, '.', ',').' Zenny' : '1 Zenny').'</strong></td>';
  $first_event_body .= '</tr>';

  // Finalize the table body for the results
$first_event_body .= '</tbody>';
$first_event_body .= '</table>';


// Print the battle complete message
$event_options = array();
$event_options['this_header_float'] = 'center';
$event_options['this_body_float'] = 'center';
$event_options['this_event_class'] = false;
$event_options['console_show_this'] = false;
$event_options['console_show_target'] = false;
$event_options['console_container_classes'] = 'field_type field_type_event field_type_'.($this->battle_result == 'victory' ? 'nature' : 'flame');
$this->events_create($target_robot, $this_robot, $first_event_header, $first_event_body, $event_options);

// Add the flag to prevent any further messages from appearing
$this->flags['battle_complete_message_created'] = true;

?>