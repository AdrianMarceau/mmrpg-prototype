<?
// DEBUG
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

// Define variables for the human's rewards in this scenario
$temp_human_token = $target_player->player_side == 'left' ? $target_player->player_token : $this_player->player_token;
$temp_human_rewards = array();
$temp_human_rewards['battle_points'] = 0;
$temp_human_rewards['battle_zenny'] = 0;
$temp_human_rewards['battle_complete'] = isset($_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this->battle_token]['battle_count'] : 0;
$temp_human_rewards['battle_failure'] = isset($_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this->battle_token]['battle_count'] : 0;
$temp_human_rewards['checkpoint'] = 'start: ';

// (HUMAN) TARGET DEFEATED
// Check if the target was the human character
if ($target_player->player_side == 'left'){

  // DEBUG
  //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

  // Calculate the number of battle points for the target player
  $this_base_points = 0; //$this->battle_points;
  $this_turn_points = 100 * $this->counters['battle_turn'];
  $this_stat_points = 0;
  $target_battle_points = $this_base_points + $this_turn_points + $this_stat_points;
  // Prevent players from loosing points
  if ($target_battle_points == 0){ $target_battle_points = 1; }
  elseif ($target_battle_points < 0){ $target_battle_points = -1 * $target_battle_points; }

  // Calculate the number of battle zenny for the target player
  //$target_battle_zenny = $this->battle_zenny;

  // Increment the main game's points total with the battle points
  $_SESSION['GAME']['counters']['battle_points'] += $target_battle_points;
  //$_SESSION['GAME']['counters']['battle_zenny'] += $target_battle_zenny;

  // Increment this player's points total with the battle points
  $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'] += $target_battle_points;

  // Update the global variable with the points reward
  $temp_human_rewards['battle_points'] = $target_battle_points;
  //$temp_human_rewards['battle_zenny'] = $target_battle_zenny;

  // Update the GAME session variable with the failed battle token
  if ($this->battle_counts){
    // DEBUG
    //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
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
// (GHOST/COMPUTER) TARGET DEFEATED
// Otherwise if the target was a computer-controlled human character
elseif ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID){
  // DEBUG
  //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
  // Calculate the battle points based on how many turns they lasted
  $target_battle_points = $this->counters['battle_turn'] * 100 * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER;
  // Collect the target player's userinfo from the database

  // Add this player's username token to the temp session array
  //$DB->INDEX['LEADERBOARD']['targets_defeated'][] = $target_player->player_id;

}
// (COMPUTER) TARGET DEFEATED
// Otherwise, zero target battle points
else {
  // DEBUG
  //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
  // Target is computer, no battle points for them
  $target_battle_points = 0;
}


// NON-INVISIBLE PLAYER DEFEATED
// Display the defeat message for the target character if not default/hidden
if ($target_player->player_token != 'player'){

  // DEBUG
  //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;




  // (HUMAN) TARGET DEFEATED BY (INVISIBLE/COMPUTER)
  // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
  if ($this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left' && $this_robot->robot_class != 'mecha'){

    // Calculate how many points the other player is rewarded for winning
    $target_player_robots = $target_player->values['robots_disabled'];
    $target_player_robots_count = count($target_player_robots);
    $other_player_points = 0;
    $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
    foreach ($target_player_robots AS $disabled_robotinfo){ $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER; }

    // Collect the battle points from the function
    $other_battle_points_modded = $this->calculate_battle_points($target_player, $other_player_points, $other_player_turns);

    // Create the victory event for the target player
    $this_robot->robot_frame = 'victory';
    $this_robot->update_session();
    $event_header = $this_robot->robot_name.' Undefeated';
    $event_body = '';
    $event_body .= $this_robot->print_robot_name().' could not be defeated! ';
    //$event_body .= $this_robot->print_robot_name().' downloads the '.($target_robot->counters['robots_disabled'] > 1 ? 'targets#39;' : 'target#39;s').' battle data!';
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
      //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_victory']);
      //$this_text_colour = !empty($mmrpg_index['types'][$this_robot->robot_token]) ? $mmrpg_index['types'][$this_robot->robot_token]['type_colour_light'] : array(200, 200, 200);
      //$event_body .= '&quot;<em style="color: rgb('.implode(',', $this_text_colour).');">'.$this_quote_text.'</em>&quot;';
    }
    $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

  }

  $target_player->player_frame = 'defeat';
  $target_robot->update_session();
  $target_player->update_session();
  $event_header = $target_player->player_name.' Defeated';
  $event_body = $target_player->print_player_name().' was defeated'.($target_player->player_side == 'left' ? '&hellip;' : '!').' ';
  //if (!empty($target_battle_points)){ $event_body .= $target_player->print_player_name().' collects <span class="recovery_amount">'.number_format($target_battle_points, 0, '.', ',').'</span> battle points&hellip;'; }
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
    //$this_text_colour = !empty($mmrpg_index['types'][$target_player->player_token]) ? $mmrpg_index['types'][$target_player->player_token]['type_colour_light'] : array(200, 200, 200);
    //$event_body .= '&quot;<em style="color: rgb('.implode(',', $this_text_colour).');">'.$this_quote_text.'</em>&quot;';
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
    foreach ($target_player_robots AS $disabled_robotinfo){ $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER; }

    // Collect the battle points from the function
    $other_battle_points_modded = $this->calculate_battle_points($target_player, $other_player_points, $other_player_turns);

    // Create the victory event for the target player
    $this_player->player_frame = 'victory';
    $target_robot->update_session();
    $this_player->update_session();
    $event_header = $this_player->player_name.' Victorious';
    $event_body = $this_player->print_player_name().' was victorious! ';
    $event_body .= $this_player->print_player_name().' collects <span class="recovery_amount">'.number_format($other_battle_points_modded, 0, '.', ',').'</span> battle points!';
    //$event_body .= $this_player->print_player_name().' downloads the '.($target_player->counters['robots_disabled'] > 1 ? 'targets#39;' : 'target#39;s').' battle data!';
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
      //$this_quote_text = str_replace($this_find, $this_replace, $this_player->player_quotes['battle_victory']);
      //$this_text_colour = !empty($mmrpg_index['types'][$this_player->player_token]) ? $mmrpg_index['types'][$this_player->player_token]['type_colour_light'] : array(200, 200, 200);
      //$event_body .= '&quot;<em style="color: rgb('.implode(',', $this_text_colour).');">'.$this_quote_text.'</em>&quot;';
    }
    $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

    // Create the temp robot sprites for the database
    $temp_this_player_robots = array();
    $temp_target_player_robots = array();
    foreach ($target_player->player_robots AS $key => $info){ $temp_this_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
    foreach ($this_player->player_robots AS $key => $info){ $temp_target_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
    $temp_this_player_robots = !empty($temp_this_player_robots) ? implode(',', $temp_this_player_robots) : '';
    $temp_target_player_robots = !empty($temp_target_player_robots) ? implode(',', $temp_target_player_robots) : '';
    // Collect the userinfo for the target player
    //$target_player_userinfo = $DB->get_array("SELECT user_name, user_name_clean, user_name_public FROM mmrpg_users WHERE user_id = {$target_player->player_id};");
    //if (!isset($_SESSION['PROTOTYPE_TEMP']['player_targets_defeated'])){ $_SESSION['PROTOTYPE_TEMP']['player_targets_defeated'] = array(); }
    //$_SESSION['PROTOTYPE_TEMP']['player_targets_defeated'][] = $target_player_userinfo['user_name_clean'];
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
      'this_player_points' => $target_battle_points,
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

    // DEBUG
    //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

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
        // If we're in DEMO mode, continue
        //if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

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

  // DEBUG
  //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

  // Collect the battle points from the function
  $this_battle_points = $this->calculate_battle_points($this_player, $this->battle_points, $this->battle_turns);
  $this_battle_zenny = $this->battle_zenny;

  // Increment the main game's points total with the battle points
  $_SESSION['GAME']['counters']['battle_points'] += $this_battle_points;
  $_SESSION['GAME']['counters']['battle_zenny'] += $this_battle_zenny;

  // Reference the number of points this player gets
  $this_player_points = $this_battle_points;
  $this_player_zenny = $this_battle_zenny;

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
  //$event_body .= $this_player->print_player_name().' collects <span class="recovery_amount">'.number_format($this_player_points, 0, '.', ',').'</span> battle points!';
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
    //$this_quote_text = str_replace($this_find, $this_replace, $this_player->player_quotes['battle_victory']);
    //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
  }
  $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

  // If this was a PLAYER BATTLE and the human user won against them (this/human/victory | target/computer/defeat)
  if ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID && $this_player->player_side == 'left'){

    // DEBUG
    //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

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
      'target_player_points' => $target_battle_points,
      'target_player_result' => 'defeat',
      'target_reward_pending' => 1
      ));

  }


  /*
   * PLAYER REWARDS
   */

  // Check if the the player was a human character
  if ($this_player->player_side == 'left'){

    // DEBUG
    //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;


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
        // If this is the copy shot ability and we're in DEMO mode, continue
        //if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

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


  /*
   * ROBOT DATABASE UPDATE
   */

  // Loop through all the target robot's and add them to the database
  /*
  if (!empty($target_player->values['robots_disabled'])){
    foreach ($target_player->values['robots_disabled'] AS $temp_key => $temp_info){
      // Add this robot to the global robot database array
      if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']])){ $_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']] = array('robot_token' => $temp_info['robot_token']); }
      if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']]['robot_defeated'])){ $_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']]['robot_defeated'] = 0; }
      $_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']]['robot_defeated']++;
    }
  }
  */



}


/*
 * BATTLE REWARDS
 */

// Check if this player was the human player
if ($this_player->player_side == 'left'){

  // DEBUG
  //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

  // Update the GAME session variable with the completed battle token
  if ($this->battle_counts){
    // DEBUG
    //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
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

  // Collect or define the player variables
  $this_player_token = $this_player->player_token;
  $this_player_info = $this_player->export_array();

  // ROBOT REWARDS

  // Loop through any robot rewards for this battle
  $this_robot_rewards = !empty($this->battle_rewards['robots']) ? $this->battle_rewards['robots'] : array();
  if (!empty($this_robot_rewards)){
    foreach ($this_robot_rewards AS $robot_reward_key => $robot_reward_info){

      // If this is the copy shot ability and we're in DEMO mode, continue
      if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

      // If this robot has already been unlocked, continue
      //if (mmrpg_prototype_robot_unlocked($this_player_token, $robot_reward_info['token'])){ continue; }

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
      //$_SESSION['GAME']['values']['battle_rewards'][$this_player_token]['player_robots'][$this_robot_token] = $this_reward;

      // DEBUG
      //$debug_body = '<pre>$robot_reward_info:'.preg_replace('/\s+/', ' ', print_r($robot_reward_info, true)).'</pre>';
      //$debug_body .= '<pre>$this_reward:'.preg_replace('/\s+/', ' ', print_r($this_reward, true)).'</pre>';
      //$this->events_create(false, false, 'DEBUG', $debug_body);

      // Display the robot reward message markup
      /*
      $event_header = $robot_info['robot_name'].' Unlocked';
      $event_body = 'A new robot has been unlocked!<br />';
      $event_body .= '<span class="robot_name">'.$robot_info['robot_name'].'</span> can now be used in battle!';
      $event_options = array();
      $event_options['console_show_target'] = false;
      $event_options['this_header_float'] = $this_player->player_side;
      $event_options['this_body_float'] = $this_player->player_side;
      $event_options['this_robot_image'] = 'mug';
      $temp_robot->robot_frame = 'base';
      $temp_robot->update_session();
      $this->events_create($temp_robot, false, $event_header, $event_body, $event_options);
      */

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
        // DEBUG
        //$this->events_create(false, false, 'DEBUG', 'Checking '.$temp_info['robot_name'].' for compatibility with the '.$ability_info['ability_name']);
        //$debug_fragment = '';
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
      //$_SESSION['GAME']['values']['battle_rewards'][$this_player_token]['player_abilities'][$this_ability_token] = $this_reward;

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


// Define the first event body markup, regardless of player type
$first_event_header = $this->battle_name.($this->battle_result == 'victory' ? ' Complete' : ' Failure').' <span style="opacity:0.25;">|</span> '.$this->battle_field->field_name;
if ($this->battle_result == 'victory'){ $first_event_body = 'Mission complete! '.($temp_human_rewards['battle_complete'] > 1 ? mmrpg_battle::random_positive_word().' That&#39;s '.$temp_human_rewards['battle_complete'].' times now! ' : '').mmrpg_battle::random_victory_quote(); }
elseif ($this->battle_result == 'defeat'){ $first_event_body = 'Mission failure. '.($temp_human_rewards['battle_failure'] > 1 ? 'That&#39;s '.$temp_human_rewards['battle_failure'].' times now&hellip; ' : '').mmrpg_battle::random_defeat_quote(); }
$first_event_body .= '<br />';
// If this is a player battle
//if ($target_player_id != MMRPG_SETTINGS_TARGET_PLAYERID){ $first_event_body .= '| player battle | target_player_id : '.$target_player_id.' '; }
$first_event_body .= 'Turns : '.$this->counters['battle_turn'].' / '.$this->battle_turns.' <span style="opacity:0.25;">|</span> ';
if ($this->battle_result == 'victory'){
  if ($this->counters['battle_turn'] != $this->battle_turns){
    $first_event_body .= 'Reward : '.number_format($this->battle_points, 0, '.', ',').' <span style="opacity:0.25;">|</span> ';
    // If the user gets a turn BONUS
    if ($this->counters['battle_turn'] < $this->battle_turns){
      $temp_bonus = round((($this->battle_turns / $this->counters['battle_turn']) - 1) * 100);
      $first_event_body .= 'Bonus : +'.$temp_bonus.'% <span style="opacity:0.25;">|</span> ';
    }
    // Else if the user gets a turn PENALTY
    else {
      $temp_bonus = round((($this->battle_turns / $this->counters['battle_turn']) - 1) * 100) * -1;
      $first_event_body .= 'Penalty : -'.$temp_bonus.'% <span style="opacity:0.25;">|</span> ';
    }
  }
}
$first_event_body .= 'Points : '.number_format($temp_human_rewards['battle_points'], 0, '.', ',').' ';
if (!empty($temp_human_rewards['battle_zenny'])){
  $first_event_body .= ' <span style="opacity:0.25;">|</span> Zenny : '.number_format($temp_human_rewards['battle_zenny'], 0, '.', ',').' ';
}
//$first_event_body .= 'Battle Points : '.number_format($this->battle_points, 0, '.', ',').' / Actual Points : '.number_format($temp_human_rewards['battle_points'], 0, '.', ',').'';
//$first_event_body .= '<br />Counts ('.($this->battle_counts ? 'Yes' : 'No').') | Rewards : <pre>'.preg_replace('/\s+/', ' ', print_r($temp_human_rewards, true)).'</pre>';

// Print the battle complete message
$event_options = array();
$event_options['this_header_float'] = 'center';
$event_options['this_body_float'] = 'center';
$event_options['this_event_class'] = false;
$event_options['console_show_this'] = false;
$event_options['console_show_target'] = false;
$event_options['console_container_classes'] = 'field_type field_type_event field_type_'.($this->battle_result == 'victory' ? 'nature' : 'flame');
$this->events_create($target_robot, $this_robot, $first_event_header, $first_event_body, $event_options);

// Create one final frame for the blank frame
//$this->events_create(false, false, '', '');
?>