<?
/*
 * ROBOT CLASS FUNCTION TRIGGER DISABLED
 * public function trigger_disabled($target_robot, $this_ability){}
 */

// If the battle has already ended, return false
if (!empty($this->battle->flags['battle_complete_message_created'])){ return false; }

// Create references to save time 'cause I'm tired
// (rather than replace all target references to this references)
$this_battle = &$this->battle;
$this_player = &$this->player; // the player of the robot being disabled
$this_robot = &$this; // the robot being disabled
$target_player = &$target_robot->player; // the player of the other robot
$target_robot = &$target_robot; // the other robot that isn't this one

// If the target player is the same as the current or the target is dead
if ($this_player->player_id == $target_player->player_id){
  // Collect the actual target player from the battle values
  if (!empty($this->battle->values['players'])){
    foreach ($this->battle->values['players'] AS $id => $info){
      if ($this_player->player_id != $id){
        unset($target_player);
        $target_player = new mmrpg_player($this_battle, $info);
      }
    }
  }
  // Collect the actual target robot from the battle values
  if (!empty($target_player->values['robots_active'])){
    foreach ($target_player->values['robots_active'] AS $key => $info){
      if ($info['robot_position'] == 'active'){
        $target_robot->robot_load($info);
      }
    }
  }
}

// Update the target player's session
$this_player->update_session();

// Create the robot disabled event
$disabled_text = in_array($this_robot->robot_token, array('dark-frag', 'dark-spire', 'dark-tower')) || $this_robot->robot_core == 'empty' ? 'destroyed' : 'disabled';
$event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
$event_body = ($this_player->player_token != 'player' ? $this_player->print_player_name().'&#39;s ' : 'The target ').' '.$this_robot->print_robot_name().' was '.$disabled_text.'!<br />'; //'.($this_robot->robot_position == 'bench' ? ' and removed from battle' : '').'
if (isset($this_robot->robot_quotes['battle_defeat'])){
  $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
  $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
  $event_body .= $this_robot->print_robot_quote('battle_defeat', $this_find, $this_replace);
}
if ($target_robot->robot_status != 'disabled'){ $target_robot->robot_frame = 'base'; }
$this_robot->robot_frame = 'defeat';
$target_robot->update_session();
$this_robot->update_session();
$this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, array('console_show_target' => false, 'canvas_show_disabled_bench' => $this_robot->robot_id.'_'.$this_robot->robot_token));


/*
 * EFFORT VALUES / STAT BOOST BONUSES
 */

// Define the event options array
$event_options = array();
$event_options['this_ability_results']['total_actions'] = 0;

// Calculate the bonus boosts from defeating the target robot (if NOT player battle)
if ($target_player->player_side == 'left' && $this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && $target_robot->robot_status != 'disabled'){


  // Boost this robot's attack if a boost is in order
  if (empty($target_robot->flags['robot_stat_max_attack'])){
    $this_attack_boost = $this_robot->robot_base_attack / 100; //ceil($this_robot->robot_base_attack / 100);
    if ($this_robot->robot_class == 'mecha'){ $this_attack_boost = $this_attack_boost / 2; }
    if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_attack_boost = $this_attack_boost * 2; }
    if ($target_robot->robot_attack + $this_attack_boost > MMRPG_SETTINGS_STATS_MAX){
      $this_attack_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_attack) * -1;
      $this_attack_boost = $this_attack_boost - $this_attack_overboost;
    }
    $this_attack_boost = round($this_attack_boost);
  } else {
    $this_attack_boost = 0;
  }

  // Boost this robot's defense if a boost is in order
  if (empty($target_robot->flags['robot_stat_max_defense'])){
    $this_defense_boost = $this_robot->robot_base_defense / 100; //ceil($this_robot->robot_base_defense / 100);
    if ($this_robot->robot_class == 'mecha'){ $this_defense_boost = $this_defense_boost / 2; }
    if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_defense_boost = $this_defense_boost * 2; }
    if ($target_robot->robot_defense + $this_defense_boost > MMRPG_SETTINGS_STATS_MAX){
      $this_defense_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_defense) * -1;
      $this_defense_boost = $this_defense_boost - $this_defense_overboost;
    }
    $this_defense_boost = round($this_defense_boost);
  } else {
    $this_defense_boost = 0;
  }

  // Boost this robot's speed if a boost is in order
  if (empty($target_robot->flags['robot_stat_max_speed'])){
    $this_speed_boost = $this_robot->robot_base_speed / 100; //ceil($this_robot->robot_base_speed / 100);
    if ($this_robot->robot_class == 'mecha'){ $this_speed_boost = $this_speed_boost / 2; }
    if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_speed_boost = $this_speed_boost * 2; }
    if ($target_robot->robot_speed + $this_speed_boost > MMRPG_SETTINGS_STATS_MAX){
      $this_speed_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_speed) * -1;
      $this_speed_boost = $this_speed_boost - $this_speed_overboost;
    }
    $this_speed_boost = round($this_speed_boost);
  } else {
    $this_speed_boost = 0;
  }

  // If the target robot is holding a Growth Module, double the stat bonuses
  if ($target_robot->robot_item == 'item-growth-module'){
    if (!$this_attack_boost){ $this_attack_boost = $this_attack_boost * 2; }
    if (!$this_defense_boost){ $this_defense_boost = $this_defense_boost * 2; }
    if (!$this_speed_boost){ $this_speed_boost = $this_speed_boost * 2; }
  }

  // Define the temporary boost actions counter
  $temp_boost_actions = 1;

  // Increase reward if there are any pending stat boosts and clear session
  if ($target_player->player_side == 'left' && ($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') && $target_robot->robot_base_attack < MMRPG_SETTINGS_STATS_MAX){
    if (!empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'])){
      $this_attack_boost += $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'];
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'] = 0;
    }
  }

  // Increase reward if there are any pending stat boosts and clear session
  if ($target_player->player_side == 'left' && ($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') && $target_robot->robot_base_defense < MMRPG_SETTINGS_STATS_MAX){
    if (!empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'])){
      $this_defense_boost += $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'];
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'] = 0;
    }
  }

  // Increase reward if there are any pending stat boosts and clear session
  if ($target_player->player_side == 'left' && ($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') && $target_robot->robot_base_speed < MMRPG_SETTINGS_STATS_MAX){
    if (!empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'])){
      $this_speed_boost += $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'];
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'] = 0;
    }
  }

  // If the attack boost was not empty, process it
  if ($this_attack_boost > 0){

    // If the robot is under level 100, stat boosts are pending
    if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){

      // Update the session variables with the pending stat boost
      if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack_pending'] += $this_attack_boost;

    }
    // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
    elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->robot_base_attack < MMRPG_SETTINGS_STATS_MAX){

      // Define the base attack boost based on robot base stats
      $temp_attack_boost = ceil($this_attack_boost);

      // If this action would boost the robot over their stat limits
      if ($temp_attack_boost + $target_robot->robot_attack > MMRPG_SETTINGS_STATS_MAX){
        $temp_attack_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_attack;
      }

      // Increment this robot's attack by the calculated amount and display an event
      $target_robot->robot_attack = ceil($target_robot->robot_attack + $temp_attack_boost);
      $target_robot->robot_base_attack = ceil($target_robot->robot_base_attack + $temp_attack_boost);
      $event_options = array();
      $event_options['this_ability_results']['trigger_kind'] = 'recovery';
      $event_options['this_ability_results']['recovery_kind'] = 'attack';
      $event_options['this_ability_results']['recovery_type'] = '';
      $event_options['this_ability_results']['flag_affinity'] = true;
      $event_options['this_ability_results']['flag_critical'] = true;
      $event_options['this_ability_results']['this_amount'] = $temp_attack_boost;
      $event_options['this_ability_results']['this_result'] = 'success';
      $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
      $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
      $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
      $event_options['console_show_target'] = false;
      $event_body = $target_robot->print_robot_name().' downloads weapons data from the target robot! ';
      $event_body .= '<br />';
      $event_body .= $target_robot->print_robot_name().'&#39;s attack grew by <span class="recovery_amount">'.$temp_attack_boost.'</span>! ';
      $target_robot->robot_frame = 'shoot';
      $target_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

      // Update the session variables with the rewarded stat boost if not mecha
      if ($target_robot->robot_class == 'master'){
        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'] = 0; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack']);
        $temp_attack_session_boost = round($this_attack_boost);
        if ($temp_attack_session_boost < 1){ $temp_attack_session_boost = 1; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_attack'] += $temp_attack_session_boost;
      }


    }

  }

  // If the defense boost was not empty, process it
  if ($this_defense_boost > 0){
    // If the robot is under level 100, stat boosts are pending
    if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){
      // Update the session variables with the pending stat boost
      if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense_pending'] += $this_defense_boost;

    }
    // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
    elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->robot_base_defense < MMRPG_SETTINGS_STATS_MAX){

      // Define the base defense boost based on robot base stats
      $temp_defense_boost = ceil($this_defense_boost);

      // If this action would boost the robot over their stat limits
      if ($temp_defense_boost + $target_robot->robot_defense > MMRPG_SETTINGS_STATS_MAX){
        $temp_defense_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_defense;
      }

      // Increment this robot's defense by the calculated amount and display an event
      $target_robot->robot_defense = ceil($target_robot->robot_defense + $temp_defense_boost);
      $target_robot->robot_base_defense = ceil($target_robot->robot_base_defense + $temp_defense_boost);
      $event_options = array();
      $event_options['this_ability_results']['trigger_kind'] = 'recovery';
      $event_options['this_ability_results']['recovery_kind'] = 'defense';
      $event_options['this_ability_results']['recovery_type'] = '';
      $event_options['this_ability_results']['flag_affinity'] = true;
      $event_options['this_ability_results']['flag_critical'] = true;
      $event_options['this_ability_results']['this_amount'] = $temp_defense_boost;
      $event_options['this_ability_results']['this_result'] = 'success';
      $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
      $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
      $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
      $event_options['console_show_target'] = false;
      $event_body = $target_robot->print_robot_name().' downloads shield data from the target robot! ';
      $event_body .= '<br />';
      $event_body .= $target_robot->print_robot_name().'&#39;s defense grew by <span class="recovery_amount">'.$temp_defense_boost.'</span>! ';
      $target_robot->robot_frame = 'defend';
      $target_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

      // Update the session variables with the rewarded stat boost if not mecha
      if ($target_robot->robot_class == 'master'){
        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'] = 0; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense']);
        $temp_defense_session_boost = round($this_defense_boost);
        if ($temp_defense_session_boost < 1){ $temp_defense_session_boost = 1; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_defense'] += $temp_defense_session_boost;
      }

    }

  }

  // If the speed boost was not empty, process it
  if ($this_speed_boost > 0){
    // If the robot is under level 100, stat boosts are pending
    if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){
      // Update the session variables with the pending stat boost
      if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed_pending'] += $this_speed_boost;

    }
    // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
    elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->robot_base_speed < MMRPG_SETTINGS_STATS_MAX){

      // Define the base speed boost based on robot base stats
      $temp_speed_boost = ceil($this_speed_boost);

      // If this action would boost the robot over their stat limits
      if ($temp_speed_boost + $target_robot->robot_speed > MMRPG_SETTINGS_STATS_MAX){
        $temp_speed_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->robot_speed;
      }

      // Increment this robot's speed by the calculated amount and display an event
      $target_robot->robot_speed = ceil($target_robot->robot_speed + $temp_speed_boost);
      $target_robot->robot_base_speed = ceil($target_robot->robot_base_speed + $temp_speed_boost);
      $event_options = array();
      $event_options['this_ability_results']['trigger_kind'] = 'recovery';
      $event_options['this_ability_results']['recovery_kind'] = 'speed';
      $event_options['this_ability_results']['recovery_type'] = '';
      $event_options['this_ability_results']['flag_affinity'] = true;
      $event_options['this_ability_results']['flag_critical'] = true;
      $event_options['this_ability_results']['this_amount'] = $temp_speed_boost;
      $event_options['this_ability_results']['this_result'] = 'success';
      $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
      $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
      $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
      $event_options['console_show_target'] = false;
      $event_body = $target_robot->print_robot_name().' downloads mobility data from the target robot! ';
      $event_body .= '<br />';
      $event_body .= $target_robot->print_robot_name().'&#39;s speed grew by <span class="recovery_amount">'.$temp_speed_boost.'</span>! ';
      $target_robot->robot_frame = 'slide';
      $target_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

      // Update the session variables with the rewarded stat boost if not mecha
      if ($target_robot->robot_class == 'master'){
        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'] = 0; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed']);
        $temp_speed_session_boost = round($this_speed_boost);
        if ($temp_speed_session_boost < 1){ $temp_speed_session_boost = 1; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token]['robot_speed'] += $temp_speed_session_boost;
      }

    }

  }

  // Update the target robot frame
  $target_robot->robot_frame = 'base';
  $target_robot->update_session();

}

// Ensure player and robot variables are updated
$target_robot->update_session();
$target_player->update_session();
$this_robot->update_session();
$this_player->update_session();

/*
// DEBUG
$this->battle->events_create(false, false, 'DEBUG', 'we made it past the stat boosts... <br />'.
	'$this_robot->robot_token='.$this_robot->robot_token.'; $target_robot->robot_token='.$target_robot->robot_token.';<br />'.
  '$target_player->player_token='.$target_player->player_token.'; $target_player->player_side='.$target_player->player_side.';<br />'
  );
*/

/*
 * ITEM REWARDS / EXPERIENCE POINTS / LEVEL UP
 * Reward the player and robots with items and experience if not in demo mode
 */

if ($target_player->player_side == 'left' && $this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && empty($_SESSION['GAME']['DEMO'])){
  // -- EXPERIENCE POINTS / LEVEL UP -- //

  // Filter out robots who were active in this battle in at least some way
  $temp_robots_active = $target_player->values['robots_active'];
  usort($temp_robots_active, array('mmrpg_player','robot_sort_by_active'));


  // Define the boost multiplier and start out at zero
  $temp_boost_multiplier = 0;

  // DEBUG
  //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $this_robot->counters = <pre>'.print_r($this_robot->counters, true).'</pre>');
  //$this_battle->events_create(false, false, 'DEBUG', $event_body);

  // If the target has had any damage flags triggered, update the multiplier
  //if ($this_robot->flags['triggered_immunity']){ $temp_boost_multiplier += 0; }
  //if (!empty($this_robot->flags['triggered_resistance'])){ $temp_boost_multiplier -= $this_robot->counters['triggered_resistance'] * 0.10; }
  //if (!empty($this_robot->flags['triggered_affinity'])){ $temp_boost_multiplier -= $this_robot->counters['triggered_affinity'] * 0.10; }
  //if (!empty($this_robot->flags['triggered_weakness'])){ $temp_boost_multiplier += $this_robot->counters['triggered_weakness'] * 0.10; }
  //if (!empty($this_robot->flags['triggered_critical'])){ $temp_boost_multiplier += $this_robot->counters['triggered_critical'] * 0.10; }

  // If we're in DEMO mode, give a 100% experience boost
  //if (!empty($_SESSION['GAME']['DEMO'])){ $temp_boost_multiplier += 1; }

  // Ensure the multiplier has not gone below 100%
  if ($temp_boost_multiplier < -0.99){ $temp_boost_multiplier = -0.99; }
  elseif ($temp_boost_multiplier > 0.99){ $temp_boost_multiplier = 0.99; }

  // Define the boost text to match the multiplier
  $temp_boost_text = '';
  if ($temp_boost_multiplier < 0){ $temp_boost_text = 'a lowered '; }
  elseif ($temp_boost_multiplier > 0){ $temp_boost_text = 'a boosted '; }

  /*
  $event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.'<pre>'.print_r($this_robot->flags, true).'</pre>');
  //$this_battle->events_create(false, false, 'DEBUG', $event_body);

  $event_body = preg_replace('/\s+/', ' ', $target_robot->robot_token.'<pre>'.print_r($target_robot->flags, true).'</pre>');
  //$this_battle->events_create(false, false, 'DEBUG', $event_body);
  */


  // Define the base experience for the target robot
  $temp_experience = $this_robot->robot_base_energy + $this_robot->robot_base_attack + $this_robot->robot_base_defense + $this_robot->robot_base_speed;

  // DEBUG
  //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_boost_multiplier = '.$temp_boost_multiplier.'; $temp_experience = '.$temp_experience.'; ');
  //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $event_body);

  // Apply any boost multipliers to the experience earned
  if ($temp_boost_multiplier > 0 || $temp_boost_multiplier < 0){ $temp_experience += $temp_experience * $temp_boost_multiplier; }
  if ($temp_experience <= 0){ $temp_experience = 1; }
  $temp_experience = round($temp_experience);
  $temp_target_experience = array('level' => $this_robot->robot_level, 'experience' => $temp_experience);

  // DEBUG
  //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_target_experience = <pre>'.print_r($temp_target_experience, true).'</pre>');
  //$this_battle->events_create(false, false, 'DEBUG', $event_body);

  // Define the robot experience level and start at zero
  $target_robot_experience = 0;

  // Sort the active robots based on active or not
  /*
  function mmrpg_sort_temp_active_robots($info1, $info2){
    if ($info1['robot_position'] == 'active'){ return -1; }
    else { return 1; }
  }
  usort($temp_robots_active, 'mmrpg_sort_temp_active_robots');
  */

  // If the target was defeated with overkill, add it to the battle var
  if (!empty($this_robot->counters['defeat_overkill'])){
    $overkill_bonus = $this_robot->counters['defeat_overkill'];
    //$overkill_bonus = $overkill_bonus - ceil($overkill_bonus * 0.90);
    //$overkill_divider = $target_robot->robot_level >= 100 ? 0.01 : (100 - $target_robot->robot_level) / 100;
    //$overkill_bonus = floor($overkill_bonus * $overkill_divider);
    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$this_battle->battle_overkill' => $this_battle->battle_overkill, '$this_battle->battle_zenny' => $this_battle->battle_zenny), true)).'</pre>', $event_options);
    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$overkill_bonus' => $overkill_bonus), true)).'</pre>', $event_options);
    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$this_robot->robot_base_total' => $this_robot->robot_base_total, '$target_robot->robot_base_total' => $target_robot->robot_base_total), true)).'</pre>', $event_options);
    //if ($target_robot->robot_base_total > $this_robot->robot_base_total){ $overkill_bonus = floor($overkill_bonus * ($this_robot->robot_base_total / $target_robot->robot_base_total));   }
    //elseif ($target_robot->robot_base_total < $this_robot->robot_base_total){ $overkill_bonus = floor($overkill_bonus * ($target_robot->robot_base_total / $this_robot->robot_base_total));   }
    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$overkill_bonus' => $overkill_bonus), true)).'</pre>', $event_options);
    $this_battle->battle_overkill += $this_robot->counters['defeat_overkill'];
    if (empty($this_battle->flags['starter_battle'])){ $this_battle->battle_zenny += $overkill_bonus; }
    $this_battle->update_session();
    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r(array('$this_battle->battle_overkill' => $this_battle->battle_overkill, '$this_battle->battle_zenny' => $this_battle->battle_zenny), true)).'</pre>', $event_options);
  }

  // Increment each of this player's robots
  $temp_robots_active_num = count($temp_robots_active);
  $temp_robots_active_num2 = $temp_robots_active_num; // This will be decremented for each non-experience gaining level 100 robots
  $temp_robots_active = array_reverse($temp_robots_active, true);
  usort($temp_robots_active, array('mmrpg_player', 'robot_sort_by_active'));
  $temp_robot_active_position = false;
  foreach ($temp_robots_active AS $temp_id => $temp_info){
    $temp_robot = $target_robot->robot_id == $temp_info['robot_id'] ? $target_robot : new mmrpg_robot($this, $target_player, $temp_info);
    if ($temp_robot->robot_level >= 100 || $temp_robot->robot_class != 'master'){ $temp_robots_active_num2--; }
    if ($temp_robot->robot_position == 'active'){
      $temp_robot_active_position = $temp_robots_active[$temp_id];
      unset($temp_robots_active[$temp_id]);
    }
  }
  $temp_unshift = array_unshift($temp_robots_active, $temp_robot_active_position);

  foreach ($temp_robots_active AS $temp_id => $temp_info){
    // Collect or define the robot points and robot rewards variables
    $temp_robot = $target_robot->robot_id == $temp_info['robot_id'] ? $target_robot : new mmrpg_robot($this, $target_player, $temp_info);
    //if ($temp_robot->robot_class == 'mecha'){ continue; }
    $temp_robot_token = $temp_info['robot_token'];
    if ($temp_robot_token == 'robot'){ continue; }
    $temp_robot_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_info['robot_token']);
    $temp_robot_rewards = !empty($temp_info['robot_rewards']) ? $temp_info['robot_rewards'] : array();
    if (empty($temp_robots_active_num2)){ break; }

    // Continue if over already at level 100
    //if ($temp_robot->robot_level >= 100){ continue; }

    // Reset the robot experience points to zero
    $target_robot_experience = 0;

    // Continue with experience mods only if under level 100
    if ($temp_robot->robot_level < 100 && $temp_robot->robot_class == 'master'){
      // Give a proportionate amount of experience based on this and the target robot's levels
      if ($temp_robot->robot_level == $temp_target_experience['level']){
        $temp_experience_boost = $temp_target_experience['experience'];
      } elseif ($temp_robot->robot_level < $temp_target_experience['level']){
        $temp_experience_boost = $temp_target_experience['experience'] + round((($temp_target_experience['level'] - $temp_robot->robot_level) / 100)  * $temp_target_experience['experience']);
        //$temp_experience_boost = $temp_target_experience['experience'] + ((($temp_target_experience['level']) / $temp_robot->robot_level) * $temp_target_experience['experience']);
      } elseif ($temp_robot->robot_level > $temp_target_experience['level']){
        $temp_experience_boost = $temp_target_experience['experience'] - round((($temp_robot->robot_level - $temp_target_experience['level']) / 100)  * $temp_target_experience['experience']);
        //$temp_experience_boost = $temp_target_experience['experience'] - ((($temp_robot->robot_level - $temp_target_experience['level']) / 100) * $temp_target_experience['experience']);
      }

      // DEBUG
      //$event_body = 'START EXPERIENCE | ';
      //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
      //$this_battle->events_create(false, false, 'DEBUG', $event_body);

      //$temp_experience_boost = ceil($temp_experience_boost / 10);
      $temp_experience_boost = ceil($temp_experience_boost / $temp_robots_active_num);
      //$temp_experience_boost = ceil($temp_experience_boost / ($temp_robots_active_num * 2));
      //$temp_experience_boost = ceil($temp_experience_boost / ($temp_robots_active_num2 * 2));
      //$temp_experience_boost = ceil(($temp_experience_boost / $temp_robots_active_num2) * 1.00);

      if ($temp_experience_boost > MMRPG_SETTINGS_STATS_MAX){ $temp_experience_boost = MMRPG_SETTINGS_STATS_MAX; }
      $target_robot_experience += $temp_experience_boost;

      // DEBUG
      //$event_body = 'ACTIVE ROBOT DIVISION | ';
      //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robots_active_num = '.$temp_robots_active_num.'; $temp_robots_active_num2 = '.$temp_robots_active_num2.'; ');
      //$this_battle->events_create(false, false, 'DEBUG', $event_body);

      // If this robot has been traded, give it an additional experience boost
      $temp_experience_boost = 0;
      $temp_robot_boost_text = $temp_boost_text;
      $temp_player_boosted = false;
      if ($temp_robot->player_token != $temp_robot->robot_original_player){
        $temp_player_boosted = true;
        $temp_robot_boost_text = 'a player boosted ';
        $temp_experience_bak = $target_robot_experience;
        $target_robot_experience = $target_robot_experience * 2;
        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
        // DEBUG
        //$event_body = 'PLAYER BOOSTED | ';
        //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robot->player_token('.$temp_robot->player_token.') != $temp_robot->robot_original_player('.$temp_robot->robot_original_player.'); ');
        //$this_battle->events_create(false, false, 'DEBUG', $event_body);
      }

      // If the target robot is holding a Growth Module, double the experience bonus
      if ($temp_robot->robot_item == 'item-growth-module'){
        $temp_robot_boost_text = $temp_player_boosted ? 'a player and module boosted ' : 'a module boosted ';
        $temp_experience_bak = $target_robot_experience;
        $target_robot_experience = $target_robot_experience * 2;
        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
        // DEBUG
        //$event_body = 'MODULE BOOSTED | ';
        //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robot->robot_item = '.$temp_robot->robot_item.'; ');
        //$this_battle->events_create(false, false, 'DEBUG', $event_body);
      }

      // If there are field multipliers in place, apply them now
      $temp_experience_boost = 0;
      if (isset($this->field->field_multipliers['experience'])){
        //$temp_robot_boost_text = '(and '.$target_robot_experience.' multiplied by '.number_format($this->field->field_multipliers['experience'], 1).') ';
        $temp_experience_bak = $target_robot_experience;
        $target_robot_experience = ceil($target_robot_experience * $this->field->field_multipliers['experience']);
        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
      }

      // DEBUG
      //$event_body = 'FIELD MULTIPLIERS | ';
      //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
      //$this_battle->events_create(false, false, 'DEBUG', $event_body);

      /*
      // If this robot has any overkill, add that to the temp experience modifier
      $temp_experience_boost = 0;
      if (!empty($this_robot->counters['defeat_overkill'])){
        if (empty($temp_robot_boost_text)){ $temp_robot_boost_text = 'an overkill boosted '; }
        else { $temp_robot_boost_text = 'a player and overkill boosted '; }
        $temp_experience_bak = $target_robot_experience;
        $target_robot_experience += ceil($this_robot->counters['defeat_overkill'] / $temp_robots_active_num2);
        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
        //$this_battle->battle_overkill += $this_robot->counters['defeat_overkill'];
        //$this_battle->update_session();
        //$temp_robot_boost_text .= 'umm '.$this_battle->battle_overkill;
      }
      */

      // DEBUG
      //$event_body = 'OVERKILL BONUS | ';
      //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
      //$this_battle->events_create(false, false, 'DEBUG', $event_body);

      /*
      // If the target robot's core type has been boosted by starforce
      if (!empty($temp_robot->robot_core) && !empty($_SESSION['GAME']['values']['star_force'][$temp_robot->robot_core])){
        if (empty($temp_robot_boost_text)){ $temp_robot_boost_text = 'a starforce boosted '; }
        elseif ($temp_robot_boost_text == 'an overkill boosted '){ $temp_robot_boost_text = 'an overkill and starforce boosted '; }
        elseif ($temp_robot_boost_text == 'a player boosted '){ $temp_robot_boost_text = 'a player and starforce boosted '; }
        else { $temp_robot_boost_text = 'a player, overkill, and starforce boosted '; }
        $temp_starforce = $_SESSION['GAME']['values']['star_force'][$temp_robot->robot_core];
        $temp_experience_bak = $target_robot_experience;
        $target_robot_experience += ceil($target_robot_experience * ($temp_starforce / 10));
        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
      }
      */

      // DEBUG
      //$event_body = 'STARFORCE BONUS | ';
      //$event_body .= preg_replace('/\s+/', ' ', $temp_robot->robot_token.' : '.$temp_robot->robot_core.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
      //$this_battle->events_create(false, false, 'DEBUG', $event_body);

      // If the experience is greater then the max, level it off at the max (sorry guys!)
      if ($target_robot_experience > MMRPG_SETTINGS_STATS_MAX){ $target_robot_experience = MMRPG_SETTINGS_STATS_MAX; }
      if ($target_robot_experience < MMRPG_SETTINGS_STATS_MIN){ $target_robot_experience = MMRPG_SETTINGS_STATS_MIN; }

      // Collect the robot's current experience and level for reference later
      $temp_start_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_robot_token);
      $temp_start_level = mmrpg_prototype_robot_level($target_player->player_token, $temp_robot_token);

      // Increment this robots's points total with the battle points
      if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] = 1; }
      if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] = 0; }
      $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] += $target_robot_experience;

      // Define the new experience for this robot
      $temp_required_experience = mmrpg_prototype_calculate_experience_required($temp_robot->robot_level);
      $temp_new_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_info['robot_token']);// If the new experience is over the required, level up the robot
      $level_boost = 0;
      if ($temp_new_experience > $temp_required_experience){
        //$level_boost = floor($temp_new_experience / $temp_required_experience);

        while ($temp_new_experience > $temp_required_experience){
          $level_boost += 1;
          $temp_new_experience -= $temp_required_experience;
          $temp_required_experience = mmrpg_prototype_calculate_experience_required($temp_robot->robot_level + $level_boost);
        }

        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] += $level_boost;
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] = $temp_new_experience; //$level_boost * $temp_required_experience;
        if ($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] > 100){
          $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] = 100;
        }

        $temp_new_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_info['robot_token']);
      }

      // Define the new level for this robot
      $temp_new_level = mmrpg_prototype_robot_level($target_player->player_token, $temp_robot_token);

    }
    // Otherwise if this is a level 100 robot already
    else {
      // Collect the robot's current experience and level for reference later
      $temp_start_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_robot_token);
      $temp_start_level = mmrpg_prototype_robot_level($target_player->player_token, $temp_robot_token);

      // Define the new experience for this robot
      $temp_new_experience = $temp_start_experience;
      $temp_new_level = $temp_start_level;

    }

    // Define the event options
    $event_options = array();
    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
    $event_options['this_ability_results']['recovery_kind'] = 'experience';
    $event_options['this_ability_results']['recovery_type'] = '';
    $event_options['this_ability_results']['this_amount'] = $target_robot_experience;
    $event_options['this_ability_results']['this_result'] = 'success';
    $event_options['this_ability_results']['flag_affinity'] = true;
    $event_options['this_ability_results']['total_actions'] = 1;
    $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
    $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

    // Update player/robot frames and points for the victory
    $temp_robot->robot_frame = 'victory';
    $temp_robot->robot_level = $temp_new_level;
    $temp_robot->robot_experience = $temp_new_experience;
    $target_player->player_frame = 'victory';
    $temp_robot->update_session();
    $target_player->update_session();

    // Only display the event if the player is under level 100
    if ($temp_robot->robot_level < 100 && $temp_robot->robot_class == 'master'){
      // Display the win message for this robot with battle points
      $temp_robot->robot_frame = 'taunt';
      $temp_robot->robot_level = $temp_new_level;
      if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = mmrpg_prototype_calculate_experience_required($temp_robot->robot_level); }
      $target_player->player_frame = 'victory';
      $event_header = $temp_robot->robot_name.'&#39;s Rewards';
      $event_multiplier_text = $temp_robot_boost_text;
      $event_body = $temp_robot->print_robot_name().' collects '.$event_multiplier_text.'<span class="recovery_amount ability_type ability_type_cutter">'.$target_robot_experience.'</span> experience points! ';
      $event_body .= '<br />';
      if (isset($temp_robot->robot_quotes['battle_victory'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $temp_robot->robot_name);
        $event_body .= $temp_robot->print_robot_quote('battle_victory', $this_find, $this_replace);
      }
      //$event_options = array();
      $event_options['console_show_target'] = false;
      $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
      $temp_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);
      if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = $temp_new_experience; }
      if ($temp_robot->robot_core == 'copy'){
        $temp_robot->robot_image = $temp_robot->robot_base_image;
        $temp_robot->robot_image_overlay = '';
       }
      $temp_robot->update_session();
      $target_player->update_session();
    }

    // Floor the robot's experience with or without the event
    $target_player->player_frame = 'victory';
    $target_player->update_session();
    $temp_robot->robot_frame = 'base';
    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = 0; }
    $temp_robot->update_session();

    // If the level has been boosted, display the stat increases
    if ($temp_start_level != $temp_new_level){
      // Define the event options
      $event_options = array();
      $event_options['this_ability_results']['trigger_kind'] = 'recovery';
      $event_options['this_ability_results']['recovery_kind'] = 'level';
      $event_options['this_ability_results']['recovery_type'] = '';
      $event_options['this_ability_results']['flag_affinity'] = true;
      $event_options['this_ability_results']['flag_critical'] = true;
      $event_options['this_ability_results']['this_amount'] = $temp_new_level - $temp_start_level;
      $event_options['this_ability_results']['this_result'] = 'success';
      $event_options['this_ability_results']['total_actions'] = 2;
      $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
      $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

      // Display the win message for this robot with battle points
      $temp_robot->robot_frame = 'taunt';
      $temp_robot->robot_level = $temp_new_level;
      if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = mmrpg_prototype_calculate_experience_required($temp_robot->robot_level); }
      else { $temp_robot->robot_experience = $temp_new_experience; }
      $target_player->player_frame = 'victory';
      $event_header = $temp_robot->robot_name.'&#39;s Rewards';
      //$event_body = $temp_robot->print_robot_name().' grew to <span class="recovery_amount'.($temp_new_level >= 100 ? ' ability_type ability_type_electric' : '').'">Level '.$temp_new_level.'</span>!<br /> ';
      $event_body = $temp_robot->print_robot_name().' grew to <span class="recovery_amount ability_type ability_type_level">Level '.$temp_new_level.($temp_new_level >= 100 ? ' &#9733;' : '').'</span>!<br /> ';
      $event_body .= $temp_robot->robot_name.'&#39;s energy, weapons, shields, and mobility were upgraded!';
      //$event_options = array();
      $event_options['console_show_target'] = false;
      $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
      $temp_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);
      $temp_robot->robot_experience = 0;
      $temp_robot->update_session();

      // Collect the base robot template from the index for calculations
      $temp_index_robot = mmrpg_robot::get_index_info($temp_robot->robot_token);

      // Define the event options
      $event_options['this_ability_results']['trigger_kind'] = 'recovery';
      $event_options['this_ability_results']['recovery_type'] = '';
      $event_options['this_ability_results']['this_amount'] = $this_defense_boost;
      $event_options['this_ability_results']['this_result'] = 'success';
      $event_options['this_ability_results']['total_actions'] = 0;
      $event_options['this_ability_user'] = $this->robot_id.'_'.$this->robot_token;
      $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

      // Update the robot rewards array with any recent info
      $temp_robot_rewards = mmrpg_prototype_robot_rewards($target_player->player_token, $temp_robot->robot_token);
      //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r($temp_robot_rewards, true)).'</pre>', $event_options);

      // Define the base energy boost based on robot base stats
      $temp_energy_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_energy']));

      // If this robot has reached level 100, the max level, create the flag in their session
      if ($temp_new_level >= 100){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['flags']['reached_max_level'] = true; }

      // Check if there are eny pending energy stat boosts for level up
      if (!empty($temp_robot_rewards['robot_energy_pending'])){
        $temp_robot_rewards['robot_energy_pending'] = round($temp_robot_rewards['robot_energy_pending']);
        $temp_energy_boost += $temp_robot_rewards['robot_energy_pending'];
        if (!empty($temp_robot_rewards['robot_energy'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy'] += $temp_robot_rewards['robot_energy_pending']; }
        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy'] = $temp_robot_rewards['robot_energy_pending']; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy_pending'] = 0;
      }

      // Increment this robot's energy by the calculated amount and display an event
      $temp_robot->robot_energy += $temp_energy_boost;
      $temp_base_energy_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_energy']));
      $temp_robot->robot_base_energy += $temp_base_energy_boost;
      $event_options['this_ability_results']['recovery_kind'] = 'energy';
      $event_options['this_ability_results']['this_amount'] = $temp_energy_boost;
      $event_options['this_ability_results']['total_actions']++;
      $event_body = $temp_robot->print_robot_name().'&#39;s health improved! ';
      $event_body .= '<br />';
      $event_body .= $temp_robot->print_robot_name().'&#39;s energy grew by <span class="recovery_amount">'.$temp_energy_boost.'</span>! ';
      $temp_robot->robot_frame = 'summon';
      $temp_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


      // Define the base attack boost based on robot base stats
      $temp_attack_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_attack']));

      // Check if there are eny pending attack stat boosts for level up
      if (!empty($temp_robot_rewards['robot_attack_pending'])){
        $temp_robot_rewards['robot_attack_pending'] = round($temp_robot_rewards['robot_attack_pending']);
        $temp_attack_boost += $temp_robot_rewards['robot_attack_pending'];
        if (!empty($temp_robot_rewards['robot_attack'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack'] += $temp_robot_rewards['robot_attack_pending']; }
        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack'] = $temp_robot_rewards['robot_attack_pending']; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack_pending'] = 0;
      }

      // Increment this robot's attack by the calculated amount and display an event
      $temp_robot->robot_attack += $temp_attack_boost;
      $temp_base_attack_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_attack']));
      $temp_robot->robot_base_attack += $temp_base_attack_boost;
      $event_options['this_ability_results']['recovery_kind'] = 'attack';
      $event_options['this_ability_results']['this_amount'] = $temp_attack_boost;
      $event_options['this_ability_results']['total_actions']++;
      $event_body = $temp_robot->print_robot_name().'&#39;s weapons improved! ';
      $event_body .= '<br />';
      $event_body .= $temp_robot->print_robot_name().'&#39;s attack grew by <span class="recovery_amount">'.$temp_attack_boost.'</span>! ';
      $temp_robot->robot_frame = 'shoot';
      $temp_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


      // Define the base defense boost based on robot base stats
      $temp_defense_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_defense']));

      // Check if there are eny pending defense stat boosts for level up
      if (!empty($temp_robot_rewards['robot_defense_pending'])){
        $temp_robot_rewards['robot_defense_pending'] = round($temp_robot_rewards['robot_defense_pending']);
        $temp_defense_boost += $temp_robot_rewards['robot_defense_pending'];
        if (!empty($temp_robot_rewards['robot_defense'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense'] += $temp_robot_rewards['robot_defense_pending']; }
        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense'] = $temp_robot_rewards['robot_defense_pending']; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense_pending'] = 0;
      }

      // Increment this robot's defense by the calculated amount and display an event
      $temp_robot->robot_defense += $temp_defense_boost;
      $temp_base_defense_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_defense']));
      $temp_robot->robot_base_defense += $temp_base_defense_boost;
      $event_options['this_ability_results']['recovery_kind'] = 'defense';
      $event_options['this_ability_results']['this_amount'] = $temp_defense_boost;
      $event_options['this_ability_results']['total_actions']++;
      $event_body = $temp_robot->print_robot_name().'&#39;s shields improved! ';
      $event_body .= '<br />';
      $event_body .= $temp_robot->print_robot_name().'&#39;s defense grew by <span class="recovery_amount">'.$temp_defense_boost.'</span>! ';
      $temp_robot->robot_frame = 'defend';
      $temp_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


      // Define the base speed boost based on robot base stats
      $temp_speed_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_speed']));

      // Check if there are eny pending speed stat boosts for level up
      if (!empty($temp_robot_rewards['robot_speed_pending'])){
        $temp_robot_rewards['robot_speed_pending'] = round($temp_robot_rewards['robot_speed_pending']);
        $temp_speed_boost += $temp_robot_rewards['robot_speed_pending'];
        if (!empty($temp_robot_rewards['robot_speed'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_speed'] += $temp_robot_rewards['robot_speed_pending']; }
        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_speed'] = $temp_robot_rewards['robot_speed_pending']; }
        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_speed_pending'] = 0;
      }

      // Increment this robot's speed by the calculated amount and display an event
      $temp_robot->robot_speed += $temp_speed_boost;
      $event_options['this_ability_results']['recovery_kind'] = 'speed';
      $event_options['this_ability_results']['this_amount'] = $temp_speed_boost;
      $event_options['this_ability_results']['total_actions']++;
      $temp_base_speed_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_speed']));
      $temp_robot->robot_base_speed += $temp_base_speed_boost;
      $event_body = $temp_robot->print_robot_name().'&#39;s mobility improved! ';
      $event_body .= '<br />';
      $event_body .= $temp_robot->print_robot_name().'&#39;s speed grew by <span class="recovery_amount">'.$temp_speed_boost.'</span>! ';
      $temp_robot->robot_frame = 'slide';
      $temp_robot->update_session();
      $target_player->update_session();
      $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);

      // Update the robot frame
      $temp_robot->robot_frame = 'base';
      $temp_robot->update_session();

    }

    // Update the experience level for real this time
    $temp_robot->robot_experience = $temp_new_experience;
    $temp_robot->update_session();

    // Collect the robot info array
    $temp_robot_info = $temp_robot->export_array();

    // Collect the indexed robot rewards for new abilities
    $index_robot_rewards = $temp_robot_info['robot_rewards'];
    //$event_body = preg_replace('/\s+/', ' ', '<pre>'.print_r($index_robot_rewards, true).'</pre>');
    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

    // Loop through the ability rewards for this robot if set
    if ($temp_robot->robot_class != 'mecha' && ($temp_start_level == 100 || ($temp_start_level != $temp_new_level && !empty($index_robot_rewards['abilities'])))){
      $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
      foreach ($index_robot_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){
        // If this ability is already unlocked, continue
        if (mmrpg_prototype_ability_unlocked($target_player->player_token, $temp_robot_token, $ability_reward_info['token'])){ continue; }
        // If we're in DEMO mode, continue
        if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

        // Check if the required level has been met by this robot
        if ($temp_new_level >= $ability_reward_info['level']){
          // Collect the ability info from the index
          $ability_info = mmrpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
          // Create the temporary ability object for event creation
          $temp_ability = new mmrpg_ability($this->battle, $target_player, $temp_robot, $ability_info);

          // Collect or define the ability variables
          $temp_ability_token = $ability_info['ability_token'];

          // Display the robot reward message markup
          $event_header = $ability_info['ability_name'].' Unlocked';
          $event_body = '<span class="robot_name">'.$temp_info['robot_name'].'</span> unlocked new ability data!<br />';
          $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
          $event_options = array();
          $event_options['console_show_target'] = false;
          $event_options['this_header_float'] = $target_player->player_side;
          $event_options['this_body_float'] = $target_player->player_side;
          $event_options['this_ability'] = $temp_ability;
          $event_options['this_ability_image'] = 'icon';
          $event_options['console_show_this_player'] = false;
          $event_options['console_show_this_robot'] = false;
          $event_options['console_show_this_ability'] = true;
          $event_options['canvas_show_this_ability'] = false;
          $temp_robot->robot_frame = $ability_reward_key % 2 == 2 ? 'taunt' : 'victory';
          $temp_robot->update_session();
          $temp_ability->ability_frame = 'base';
          $temp_ability->update_session();
          $this_battle->events_create($temp_robot, false, $event_header, $event_body, $event_options);
          $temp_robot->robot_frame = 'base';
          $temp_robot->update_session();

          // Automatically unlock this ability for use in battle
          $this_reward = mmrpg_ability::get_index_info($temp_ability_token); //array('ability_token' => $temp_ability_token);
          $temp_player_info = $target_player->export_array();
          $show_event = !mmrpg_prototype_ability_unlocked('', '', $temp_ability_token) ? true : false;
          mmrpg_game_unlock_ability($temp_player_info, $temp_robot_info, $this_reward, $show_event);
          if ($temp_robot_info['robot_original_player'] == $temp_player_info['player_token']){ mmrpg_game_unlock_ability($temp_player_info, false, $this_reward); }
          else { mmrpg_game_unlock_ability(array('player_token' => $temp_robot_info['robot_original_player']), false, $this_reward); }
          //$_SESSION['GAME']['values']['battle_rewards'][$target_player_token]['player_robots'][$temp_robot_token]['robot_abilities'][$temp_ability_token] = $this_reward;

        }

      }
    }

  }


  // -- ITEM REWARDS -- //
  // Define the temp player rewards array
  $target_player_rewards = array();

  // Define the chance multiplier and start at one
  $temp_chance_multiplier = $trigger_options['item_multiplier'];

  // Increase the item chance multiplier if one is set for the stage
  if (isset($this_battle->field->field_multipliers['items'])){ $temp_chance_multiplier = ($temp_chance_multiplier * $this_battle->field->field_multipliers['items']); }

  // Define the available item drops for this battle
  $target_player_rewards['items'] = !empty($this_battle->battle_rewards['items']) ? $this_battle->battle_rewards['items'] : array();

  // Increase the multipliers if starter battle
  if (!empty($this_battle->flags['starter_battle'])){

    $temp_chance_multiplier = 4;

  }
  // Otherwise, define auto items
  else {

    // If the target holds a Fortune Module, increase the chance of dropps
    $temp_fortune_module = false;
    if ($target_robot->robot_item == 'item-fortune-module'){ $temp_fortune_module = true; }

    // If this robot was a MECHA class, it may drop SMALL SCREWS
    if ($this_robot->robot_class == 'mecha'){
      $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-screw-small', 'quantity' => mt_rand(1, ($temp_fortune_module ? 9 : 6)));
      // If this robot was an empty core, it drops other items too
      if (!empty($this_robot->robot_core) && $this_robot->robot_core == 'empty'){
        $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-super-pellet');
      }
    }

    // If this robot was a MASTER class, it may drop LARGE SCREWS
    if ($this_robot->robot_class == 'master'){
      $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-screw-large', 'quantity' => mt_rand(1, ($temp_fortune_module ? 6 : 3)));
      // If this robot was an empty core, it drops other items too
      if (!empty($this_robot->robot_core) && $this_robot->robot_core == 'empty'){
        $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-super-capsule');
      }
    }

    // If this robot was a BOSS class, it may drop EXTRA LIFE
    if ($this_robot->robot_class == 'boss'){
      $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-extra-life', 'quantity' => mt_rand(1, ($temp_fortune_module ? 3 : 1)));
    }

    // If this robot was holding an ITEM, it should also drop that at a high rate
    if (!empty($this_robot->robot_item)){
      $target_player_rewards['items'][] =  array('chance' => 100, 'token' => $this_robot->robot_item);
    }

  }



  // Precount the item values for later use
  $temp_value_total = 0;
  $temp_count_total = 0;
  foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_value_total += $item_reward_info['chance']; $temp_count_total += 1; }
  //$this_battle->events_create(false, false, 'DEBUG', '$temp_count_total = '.$temp_count_total.';<br /> $temp_value_total = '.$temp_value_total.'; ');

  // If this robot was a MECHA class and destroyed by WEAKNESS, it may drop a CORE
  if ($this_robot->robot_class == 'mecha' && !empty($this_robot->flags['triggered_weakness'])){
    $temp_shard_type = !empty($this->robot_core) ? $this->robot_core : 'none';
    $target_player_rewards['items'] = array();
    $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-shard-'.$temp_shard_type);
    }
  // If this robot was a MASTER OR BOSS class and destroyed by WEAKNESS, it may drop a CORE
  elseif (in_array($this_robot->robot_class, array('master', 'boss')) && !empty($this_robot->flags['triggered_weakness'])){
    $temp_core_type = !empty($this->robot_core) ? $this->robot_core : 'none';
    $target_player_rewards['items'] = array();
    $target_player_rewards['items'][] =  array('chance' => 100, 'token' => 'item-core-'.$temp_core_type);
    }

  // Recount the item values for later use
  $temp_value_total = 0;
  $temp_count_total = 0;
  foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_value_total += $item_reward_info['chance']; $temp_count_total += 1; }
  // Adjust item values for easier to understand percentages
  foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $target_player_rewards['items'][$item_reward_key]['chance'] = ceil(($item_reward_info['chance'] / $temp_value_total) * 100); }

  // Shuffle the rewards so it doesn't look to formulaic
  shuffle($target_player_rewards['items']);

  // DEBUG
  //$temp_string = '';
  //foreach ($target_player_rewards['items'] AS $info){ $temp_string .= $info['token'].' = '.$info['chance'].'%, '; }
  //$this_battle->events_create(false, false, 'DEBUG', '$target_player_rewards[\'items\'] = '.count($target_player_rewards['items']).'<br /> '.$temp_string);

  // Define a function for dealing with item drops
  if (!function_exists('temp_player_rewards_items')){
    function temp_player_rewards_items($this_battle, $target_player, $target_robot, $this_robot, $item_reward_key, $item_reward_info, $item_drop_count = 1){
      global $mmrpg_index;
      // Create the temporary ability object for event creation
      $temp_ability = new mmrpg_ability($this_battle, $target_player, $target_robot, $item_reward_info);
      $temp_ability->ability_name = $item_reward_info['ability_name'];
      $temp_ability->ability_image = $item_reward_info['ability_token'];
      $temp_ability->update_session();

      // Collect or define the ability variables
      $temp_item_token = $item_reward_info['ability_token'];
      $temp_item_name = $item_reward_info['ability_name'];
      $temp_item_colour = !empty($item_reward_info['ability_type']) ? $item_reward_info['ability_type'] : 'none';
      if (!empty($item_reward_info['ability_type2'])){ $temp_item_colour .= '_'.$item_reward_info['ability_type2']; }
      $temp_type_name = !empty($item_reward_info['ability_type']) ? ucfirst($item_reward_info['ability_type']) : 'Neutral';
      $allow_over_max = false;
      $temp_is_shard = preg_match('/^item-shard-/i', $temp_item_token) ? true : false;
      $temp_is_core = preg_match('/^item-core-/i', $temp_item_token) ? true : false;
      // Define the max quantity limit for this particular item
      if ($temp_is_shard){ $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY; $allow_over_max = true; }
      elseif ($temp_is_core){ $temp_item_quantity_max = MMRPG_SETTINGS_CORES_MAXQUANTITY; }
      else { $temp_item_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }
      // Create the session variable for this item if it does not exist and collect its value
      if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
      $temp_item_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
      // If this item is already at the quantity limit, skip it entirely
      if ($temp_item_quantity >= $temp_item_quantity_max){
        //$this_battle->events_create(false, false, 'DEBUG', 'max count for '.$temp_item_token.' of '.$temp_item_quantity_max.' has been reached ('.($allow_over_max ? 'allow' : 'disallow').')');
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_item_quantity_max;
        $temp_item_quantity = $temp_item_quantity_max;
        if (!$allow_over_max){ return true; }
      }

      // Define the new item quantity after increment
      $temp_item_quantity_new = $temp_item_quantity + $item_drop_count;
      $shards_remaining = false;
      // If this is a shard piece
      if ($temp_is_shard){
        // Define the number of shards remaining for a new core
        $temp_item_quantity_max = MMRPG_SETTINGS_SHARDS_MAXQUANTITY;
        $shards_remaining = $temp_item_quantity_max - $temp_item_quantity_new;
        // If this player has collected enough shards to create a new core
        if ($shards_remaining == 0){ $temp_body_addon = 'The other '.$temp_type_name.' Shards from the inventory started glowing&hellip;'; }
        // Otherwise, if more shards are required to create a new core
        else { $temp_body_addon = 'Collect '.$shards_remaining.' more shard'.($shards_remaining > 1 ? 's' : '').' to create a new '.$temp_type_name.' Core!'; }
      }
      // Else if this is a core
      elseif (preg_match('/^item-core-/i', $temp_item_token)){
        // Define the robot core drop text for displau
        $temp_body_addon = $target_player->print_player_name().' added the new core to the inventory.';
      }
      // Otherwise, if a normal item
      else {
        // Define the normal item drop text for display
        $temp_body_addon = $target_player->print_player_name().' added the dropped item'.($item_drop_count > 1 ? 's' : '').' to the inventory.';
      }

      // Display the robot reward message markup
      $event_header = $temp_item_name.' Item Drop';
      $event_body = mmrpg_battle::random_positive_word();
      $event_body .= ' The disabled '.$this_robot->print_robot_name().' dropped ';
      if ($item_drop_count == 1){ $event_body .= (preg_match('/^(a|e|i|o|u)/i', $temp_item_name) ? 'an' : 'a').' <span class="ability_name ability_type ability_type_'.$temp_item_colour.'">'.$temp_item_name.'</span>!<br />'; }
      else { $event_body .= 'x'.$item_drop_count.' <span class="ability_name ability_type ability_type_'.$temp_item_colour.'">'.$temp_item_name.'s</span>!<br />'; }
      $event_body .= $temp_body_addon;
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
      $event_options['canvas_show_this_ability'] = true;
      $target_player->player_frame = $item_reward_key % 3 == 0 ? 'victory' : 'taunt';
      $target_player->update_session();
      $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'taunt' : 'base';
      $target_robot->update_session();
      $temp_ability->ability_frame = 'base';
      $temp_ability->ability_frame_offset = array('x' => 220, 'y' => 0, 'z' => 10);
      $temp_ability->update_session();
      $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

      // Create and/or increment the session variable for this item increasing its quantity
      if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
      if ($temp_item_quantity < $temp_item_quantity_max){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] += $item_drop_count; }

      // If this was a shard, and it was the LAST shard
      if ($shards_remaining !== false && $shards_remaining < 1){

        // Define the new core token and increment value in session
        $temp_core_token = str_replace('shard', 'core', $temp_item_token);
        $temp_core_name = str_replace('Shard', 'Core', $temp_item_name);
        $item_core_info = array('ability_token' => $temp_core_token, 'ability_name' => $temp_core_name, 'ability_type' => $item_reward_info['ability_type']);

        // Create the temporary ability object for event creation
        $temp_core = new mmrpg_ability($this_battle, $target_player, $target_robot, $item_core_info);
        $temp_core->ability_name = $item_core_info['ability_name'];
        $temp_core->ability_image = $item_core_info['ability_token'];
        $temp_core->update_session();

        // Collect or define the ability variables
        //$temp_core_token = $item_core_info['ability_token'];
        //$temp_core_name = $item_core_info['ability_name'];
        $temp_type_name = !empty($item_core_info['ability_type']) ? ucfirst($item_core_info['ability_type']) : 'Neutral';
        $temp_core_colour = !empty($item_core_info['ability_type']) ? $item_core_info['ability_type'] : 'none';
        // Define the max quantity limit for this particular item
        $temp_core_quantity_max = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
        // Create the session variable for this item if it does not exist and collect its value
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
        $temp_core_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_core_token];
        // If this item is already at the quantity limit, skip it entirely
        if ($temp_core_quantity >= $temp_core_quantity_max){
          //$this_battle->events_create(false, false, 'DEBUG', 'max count for '.$temp_core_token.' of '.$temp_core_quantity_max.' has been reached');
          $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = $temp_core_quantity_max;
          $temp_core_quantity = $temp_core_quantity_max;
          return true;
        }

        // Display the robot reward message markup
        $event_header = $temp_core_name.' Item Fusion';
        $event_body = mmrpg_battle::random_positive_word().' The glowing shards fused to create a new <span class="ability_name ability_type ability_type_'.$temp_core_colour.'">'.$temp_core_name.'</span>!<br />';
        $event_body .= $target_player->print_player_name().' added the new core to the inventory.';
        $event_options = array();
        $event_options['console_show_target'] = false;
        $event_options['this_header_float'] = $target_player->player_side;
        $event_options['this_body_float'] = $target_player->player_side;
        $event_options['this_ability'] = $temp_core;
        $event_options['this_ability_image'] = 'icon';
        $event_options['event_flag_victory'] = true;
        $event_options['console_show_this_player'] = false;
        $event_options['console_show_this_robot'] = false;
        $event_options['console_show_this_ability'] = true;
        $event_options['canvas_show_this_ability'] = true;
        $target_player->player_frame = $item_reward_key + 1 % 3 == 0 ? 'taunt' : 'victory';
        $target_player->update_session();
        $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'base' : 'taunt';
        $target_robot->update_session();
        $temp_core->ability_frame = 'base';
        $temp_core->ability_frame_offset = array('x' => 220, 'y' => 0, 'z' => 10);
        $temp_core->update_session();
        $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

        // Create and/or increment the session variable for this item increasing its quantity
        if (empty($_SESSION['GAME']['values']['battle_items'][$temp_core_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] = 0; }
        if ($temp_core_quantity < $temp_core_quantity_max){ $_SESSION['GAME']['values']['battle_items'][$temp_core_token] += 1; }

        // Set the old shard counter back to zero now that they've fused
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0;
        $temp_item_quantity = 0;

      }

      // Return true on success
      return true;

    }
  }

  // Loop through the ability rewards for this robot if set and NOT demo mode
  if (empty($_SESSION['GAME']['DEMO']) && !empty($target_player_rewards['items']) && $this->player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID){
    $temp_items_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    // Define the default success rate and multiply by the modifier
    $temp_success_value = $this_robot->robot_class == 'master' ? 50 : 25;
    $temp_success_value = ceil($temp_success_value * $temp_chance_multiplier);
    // Empty cores always have item drops
    if (!empty($this_robot->robot_core) && $this_robot->robot_core == 'empty'){ $temp_success_value = 100; }
    // If the target holds a Fortune Module, increase the chance of dropps
    if ($target_robot->robot_item == 'item-fortune-module'){ $temp_success_value = $temp_success_value * 2; }
    // Fix success values over 100
    if ($temp_success_value > 100){ $temp_success_value = 100; }
    // Define the failure based on success rate
    $temp_failure_value = 100 - $temp_success_value;
    // Define the dropping result based on rates
    $temp_dropping_result = $temp_success_value == 100 ? 'success' : $this_battle->weighted_chance(array('success', 'failure'), array($temp_success_value, $temp_failure_value));
    //$this_battle->events_create(false, false, 'DEBUG', '..and the result of the drop ('.$temp_success_value.' / '.$temp_failure_value.') is '.$temp_dropping_result);
    if ($temp_dropping_result == 'success'){

      $temp_value_total = 0;
      $temp_count_total = 0;
      foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){
        $temp_value_total += $item_reward_info['chance'];
        $temp_count_total += 1;
      }

      $temp_item_counts = array();
      $temp_item_tokens = array();
      $temp_item_weights = array();
      if ($temp_value_total > 0){
        foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){
          $temp_item_tokens[] = $item_reward_info['token'];
          $temp_item_weights[] = ceil(($item_reward_info['chance'] / $temp_value_total) * 100);
          $temp_item_counts[$item_reward_info['token']] = isset($item_reward_info['quantity']) ? $item_reward_info['quantity'] : 1;
        }
      }

      $temp_random_item = $this_battle->weighted_chance($temp_item_tokens, $temp_item_weights);

      $item_index_info = mmrpg_ability::parse_index_info($temp_items_index[$temp_random_item]);
      $item_drop_count = $temp_item_counts[$temp_random_item];

      temp_player_rewards_items($this_battle, $target_player, $target_robot, $this, $item_reward_key, $item_index_info, $item_drop_count);
    }
  }

}

// DEBUG
//$this->battle->events_create(false, false, 'DEBUG', 'we made it past the experience boosts');

// If the player has replacement robots and the knocked-out one was active
if ($this_player->counters['robots_active'] > 0){
  // Try to find at least one active POSITION robot before requiring a switch
  $has_active_positon_robot = false;
  foreach ($this_player->values['robots_active'] AS $key => $robot){
    //if ($robot['robot_position'] == 'active'){ $has_active_positon_robot = true; }
  }

  // If the player does NOT have an active position robot, trigger a switch
  if (!$has_active_positon_robot){
    // If the target player is not on autopilot, require input
    if ($this_player->player_autopilot == false){
      // Empty the action queue to allow the player switch time
      $this_battle->actions = array();
    }
    // Otherwise, if the target player is on autopilot, automate input
    elseif ($this_player->player_autopilot == true){  // && $this_player->player_next_action != 'switch'
      // Empty the action queue to allow the player switch time
      $this_battle->actions = array();

      // Remove any previous switch actions for this player
      $backup_switch_actions = $this_battle->actions_extract(array(
        'this_player_id' => $this_player->player_id,
        'this_action' => 'switch'
        ));

      //$this_battle->events_create(false, false, 'DEBUG DEBUG', 'This is a test from inside the dead trigger ['.count($backup_switch_actions).'].');

      // If there were any previous switches removed
      if (!empty($backup_switch_actions)){
        // If the target robot was faster, it should attack first
        if ($this_robot->robot_speed > $target_robot->robot_speed){
          // Prepend an ability action for this robot
          $this_battle->actions_prepend(
            $this_player,
            $this_robot,
            $target_player,
            $target_robot,
            'ability',
            ''
            );
        }
        // Otherwise, if the target was slower, if should attack second
        else {
          // Prepend an ability action for this robot
          $this_battle->actions_append(
            $this_player,
            $this_robot,
            $target_player,
            $target_robot,
            'ability',
            ''
            );
        }
      }

      // Prepend a switch action for the target robot
      $this_battle->actions_prepend(
        $this_player,
        $this_robot,
        $target_player,
        $target_robot,
        'switch',
        ''
        );

    }

  }

}
// Otherwise, if the target is out of robots...
else {
  // Trigger a battle complete action
  $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');

}

// Either way, set the hidden flag on the robot
//if (($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1) && $this_robot->robot_position == 'bench'){
if ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1){
  //$this_robot->robot_status == 'disabled';
  $this_robot->flags['apply_disabled_state'] = true;
  if ($this_robot->robot_position == 'bench'){ $this_robot->flags['hidden'] = true; }
  $this_robot->update_session();
}

// -- ROBOT UNLOCKING STUFF!!! -- //

// Check if this target winner was a HUMAN player and update the robot database counter for defeats
if ($target_player->player_side == 'left'){
  // Add this robot to the global robot database array
  if (!isset($_SESSION['GAME']['values']['robot_database'][$this->robot_token])){ $_SESSION['GAME']['values']['robot_database'][$this->robot_token] = array('robot_token' => $this->robot_token); }
  if (!isset($_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated'])){ $_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated'] = 0; }
  $_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated']++;
}

// Check if this battle has any robot rewards to unlock and the winner was a HUMAN player
if ($target_player->player_side == 'left' && !empty($this->battle->battle_rewards['robots'])){
  // DEBUG
  //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | battle_rewards_robots = '.count($this->battle->battle_rewards['robots']).'');
  foreach ($this->battle->battle_rewards['robots'] AS $temp_reward_key => $temp_reward_info){
    // DEBUG
    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | checking '.$this->robot_token.' == '.preg_replace('/\s+/', ' ', print_r($temp_reward_info, true)).'...');
    // Check if this robot was part of the rewards for this battle
    if (!mmrpg_prototype_robot_unlocked(false, $temp_reward_info['token']) && $this->robot_token == $temp_reward_info['token']){
      // DEBUG
      //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | '.$this->robot_token.' == '.$temp_reward_info['token'].' is a match!');
      // Check if this robot has been attacked with any elemental moves
      if (!empty($this->history['triggered_damage_types'])){
        // Loop through all the damage types and check if they're not empty
        foreach ($this->history['triggered_damage_types'] AS $key => $types){
          if (!empty($types)){
            // DEBUG
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | '.$this->robot_token.' was attacked with a '.implode(', ', $types).' type ability!<br />Removing from the battle rewards!');

            // Generate the robot removed event showing the destruction
            /*
            $event_header = $this->robot_name.'&#39;s Data Destroyed';
            $event_body = $this->print_robot_name().'&#39;s battle data was damaged beyond repair!<br />';
            $event_body .= $this->print_robot_name().' could not be unlocked for use in battle&hellip;';
            $event_options = array();
            $event_options['console_show_target'] = false;
            $event_options['this_header_float'] = $this_player->player_side;
            $event_options['this_body_float'] = $this_player->player_side;
            $event_options['console_show_this_player'] = false;
            $event_options['console_show_this_robot'] = true;
            $this_robot->robot_frame = 'defeat';
            $this_robot->update_session();
            $this_battle->events_create($this, false, $event_header, $event_body, $event_options);
            */

            // Remove this robot from the battle rewards array
            unset($this->battle->battle_rewards['robots'][$temp_reward_key]);
            $this->battle->update_session();

            // Break, we know all we need to
            break;
          }
        }
      }
      // If this robot is somehow still a reward, print a message showing a good job
      if (!empty($this->battle->battle_rewards['robots'][$temp_reward_key])){
        // Collect this reward's information
        $robot_reward_info = $this->battle->battle_rewards['robots'][$temp_reward_key];

        // Collect or define the robot points and robot rewards variables
        //$this_robot_token = $robot_reward_info['token'];
        $this_robot_level = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
        $this_robot_experience = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
        $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

        // Create the temp new robot for the player
        //$temp_index_robot = mmrpg_robot::get_index_info($this_robot_token);
        $temp_index_robot['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID * 2;
        $temp_index_robot['robot_level'] = $this_robot_level;
        $temp_index_robot['robot_experience'] = $this_robot_experience;
        $temp_unlocked_robot = new mmrpg_robot($this_battle, $target_player, $temp_index_robot);

        // Automatically unlock this robot for use in battle
        //$temp_unlocked_player = $mmrpg_index['players'][$target_player->player_token];
        mmrpg_game_unlock_robot($temp_unlocked_player, $temp_index_robot, true, true);

        // Display the robot reward message markup
        //$event_header = $temp_unlocked_robot->robot_name.' Unlocked';
        $event_body = mmrpg_battle::random_positive_word().' '.$target_player->print_player_name().' unlocked new robot data!<br />';
        $event_body .= $temp_unlocked_robot->print_robot_name().' can now be used in battle!';
        $event_options = array();
        $event_options['console_show_target'] = false;
        $event_options['this_header_float'] = $target_player->player_side;
        $event_options['this_body_float'] = $target_player->player_side;
        $event_options['this_robot_image'] = 'mug';
        $temp_unlocked_robot->robot_frame = 'base';
        $temp_unlocked_robot->update_session();
        $this_battle->events_create($temp_unlocked_robot, false, $event_header, $event_body, $event_options);

      }

    }

  }
}



?>