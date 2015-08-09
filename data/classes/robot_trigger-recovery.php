<?
/*
 * ROBOT CLASS FUNCTION TRIGGER RECOVERY
 * public function trigger_recovery($target_robot, $this_ability, $recovery_amount, $trigger_disabled = true){}
 */

// If the battle has already ended, return false
if ($this->battle->battle_status == 'complete'){ return false; }

// Backup this and the target robot's frames to revert later
$this_robot_backup_frame = $this->robot_frame;
$this_player_backup_frame = $this->player->player_frame;
$target_robot_backup_frame = $target_robot->robot_frame;
$target_player_backup_frame = $target_robot->player->player_frame;
$this_ability_backup_frame = $this_ability->ability_frame;

// Check if this robot is at full health before triggering
$this_robot_energy_start = $this->robot_energy;
$this_robot_energy_start_max = $this_robot_energy_start >= $this->robot_base_energy ? true : false;

// If this recovery has been referred, update target variables
if (!empty($trigger_options['referred_recovery'])){

  // If a referred player was provided, replace the target player object
  if (!empty($trigger_options['referred_player'])){
    $target_player = new mmrpg_player($this->battle, $trigger_options['referred_player']);
  }

  // If a referred player and robot were provided, replace the target robot object
  if (!empty($trigger_options['referred_player']) && !empty($trigger_options['referred_robot'])){
    $target_robot = new mmrpg_robot($this->battle, $target_player, $trigger_options['referred_robot']);
  }

  // Collect references to referred recovery stats if they exist
  $target_robot_energy_start = !empty($trigger_options['referred_energy']) ? $trigger_options['referred_energy'] : $target_robot->robot_energy;
  $target_robot_attack_start = !empty($trigger_options['referred_attack']) ? $trigger_options['referred_attack'] : $target_robot->robot_attack;
  $target_robot_defense_start = !empty($trigger_options['referred_defense']) ? $trigger_options['referred_defense'] : $target_robot->robot_defense;
  $target_robot_speed_start = !empty($trigger_options['referred_speed']) ? $trigger_options['referred_speed'] : $target_robot->robot_speed;

} else {

  // Collect references to the target robots stats
  $target_robot_energy_start = $target_robot->robot_energy;
  $target_robot_attack_start = $target_robot->robot_attack;
  $target_robot_defense_start = $target_robot->robot_defense;
  $target_robot_speed_start = $target_robot->robot_speed;

}

// Define the event console options
$event_options = array();
$event_options['console_container_height'] = 1;
$event_options['this_ability'] = $this_ability;
$event_options['this_ability_results'] = array();

// Empty any text from the previous ability result
$this_ability->ability_results['this_text'] = '';

// Update the recovery to whatever was supplied in the argument
//if ($this_ability->recovery_options['recovery_percent'] && $recovery_amount > 100){ $recovery_amount = 100; }
$this_ability->recovery_options['recovery_amount'] = $recovery_amount;

// Collect the recovery amount argument from the function
$this_ability->ability_results['this_amount'] = $recovery_amount;
// DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | trigger_recovery |  this('.$this->robot_id.':'.$this->robot_token.') vs target('.$target_robot->robot_id.':'.$target_robot->robot_token.') <br /> recovery_start_amount:'.$this_ability->ability_results['this_amount'].' | '.'percent:'.($this_ability->recovery_options['recovery_percent'] ? 'true' : 'false').' | '.'kind:'.$this_ability->recovery_options['recovery_kind'].' | type1:'.(!empty($this_ability->recovery_options['recovery_type']) ? $this_ability->recovery_options['recovery_type'] : 'none').' | type2:'.(!empty($this_ability->recovery_options['recovery_type2']) ? $this_ability->recovery_options['recovery_type2'] : 'none').''); }

// DEBUG
$debug = array();
foreach ($trigger_options AS $key => $value){ $debug[] = (!$value ? '<del>' : '<span>').preg_replace('/^apply_(.*)_modifiers$/i', '$1_modifiers', $key).(!$value ? '</del>' : '</span>'); }
if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | damage_trigger_options | '.implode(', ', $debug)); }

// Only apply modifiers if they have not been disabled
if ($trigger_options['apply_modifiers'] != false){

  // Skip all weakness, resistance, etc. calculations if robot is targetting self
  if ($trigger_options['apply_type_modifiers'] != false
    && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery'])){

    // If this robot has weakness to the ability (based on type)
    if ($trigger_options['apply_weakness_modifiers'] != false && $this->has_weakness($this_ability->recovery_options['recovery_type']) && !$this->has_affinity($this_ability->recovery_options['recovery_type2'])){
      //$this_ability->ability_results['counter_weaknesses'] += 1;
      //$this_ability->ability_results['flag_weakness'] = true;
      return $this->trigger_damage($target_robot, $this_ability, $recovery_amount);
    } else {
      $this_ability->ability_results['flag_weakness'] = false;
    }

    // If this robot has weakness to the ability (based on type2)
    if ($trigger_options['apply_weakness_modifiers'] != false && $this->has_weakness($this_ability->recovery_options['recovery_type2']) && !$this->has_affinity($this_ability->recovery_options['recovery_type'])){
      $this_ability->ability_results['counter_weaknesses'] += 1;
      $this_ability->ability_results['flag_weakness'] = true;
      return $this->trigger_damage($target_robot, $this_ability, $recovery_amount);
    }

    // If target robot has affinity to the ability (based on type)
    if ($trigger_options['apply_affinity_modifiers'] != false && $this->has_affinity($this_ability->recovery_options['recovery_type']) && !$this->has_weakness($this_ability->recovery_options['recovery_type2'])){
      $this_ability->ability_results['counter_affinities'] += 1;
      $this_ability->ability_results['flag_affinity'] = true;
    } else {
      $this_ability->ability_results['flag_affinity'] = false;
    }

    // If target robot has affinity to the ability (based on type2)
    if ($trigger_options['apply_affinity_modifiers'] != false && $this->has_affinity($this_ability->recovery_options['recovery_type2']) && !$this->has_weakness($this_ability->recovery_options['recovery_type'])){
      $this_ability->ability_results['counter_affinities'] += 1;
      $this_ability->ability_results['flag_affinity'] = true;
    }

    // If target robot has resistance to the ability (based on type)
    if ($trigger_options['apply_resistance_modifiers'] != false && $this->has_resistance($this_ability->recovery_options['recovery_type'])){
      $this_ability->ability_results['counter_resistances'] += 1;
      $this_ability->ability_results['flag_resistance'] = true;
    } else {
      $this_ability->ability_results['flag_resistance'] = false;
    }

    // If target robot has resistance to the ability (based on type2)
    if ($trigger_options['apply_resistance_modifiers'] != false && $this->has_resistance($this_ability->recovery_options['recovery_type2'])){
      $this_ability->ability_results['counter_resistances'] += 1;
      $this_ability->ability_results['flag_resistance'] = true;
    }

    // If target robot has immunity to the ability (based on type)
    if ($trigger_options['apply_immunity_modifiers'] != false && $this->has_immunity($this_ability->recovery_options['recovery_type'])){
      $this_ability->ability_results['counter_immunities'] += 1;
      $this_ability->ability_results['flag_immunity'] = true;
    } else {
      $this_ability->ability_results['flag_immunity'] = false;
    }

    // If target robot has immunity to the ability (based on type2)
    if ($trigger_options['apply_immunity_modifiers'] != false && $this->has_immunity($this_ability->recovery_options['recovery_type2'])){
      $this_ability->ability_results['counter_immunities'] += 1;
      $this_ability->ability_results['flag_immunity'] = true;
    }

  }

  // Apply position boosts if allowed to
  if ($trigger_options['apply_position_modifiers'] != false){

    // If this robot is not in the active position
    if ($this->robot_position != 'active'){
      // Collect the current key of the robot and apply recovery mods
      $temp_recovery_key = $this->robot_key + 1;
      $temp_recovery_resistor = (10 - $temp_recovery_key) / 10;
      $new_recovery_amount = round($recovery_amount * $temp_recovery_resistor);
      // DEBUG
      if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | position_modifier_recovery | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_recovery_resistor.') = '.$new_recovery_amount.''); }
      $recovery_amount = $new_recovery_amount;
    }

  }

}

// Collect the first and second ability type else "none" for multipliers
$temp_ability_recovery_type = !empty($this_ability->recovery_options['recovery_type']) ? $this_ability->recovery_options['recovery_type'] : 'none';
$temp_ability_recovery_type2 = !empty($this_ability->recovery_options['recovery_type2']) ? $this_ability->recovery_options['recovery_type2'] : '';

// Apply field multipliers preemtively if there are any
if ($trigger_options['apply_field_modifiers'] != false && $this_ability->recovery_options['recovery_modifiers'] && !empty($this->field->field_multipliers)){
  // Collect the multipliters for easier
  $field_multipliers = $this->field->field_multipliers;
  // If there's a recovery booster, apply that first
  if (isset($field_multipliers['recovery'])){
    $new_recovery_amount = round($recovery_amount * $field_multipliers['recovery']);
    // DEBUG
    if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_recovery | '.$recovery_amount.' = round('.$recovery_amount.' * '.$field_multipliers['recovery'].') = '.$new_recovery_amount.''); }
    $recovery_amount = $new_recovery_amount;
  }
  // Loop through all the other type multipliers one by one if this ability has a type
  $skip_types = array('recovery', 'damage', 'experience');
  if (true){ //!empty($this_ability->recovery_options['recovery_type'])
    // Loop through all the other type multipliers one by one if this ability has a type
    foreach ($field_multipliers AS $temp_type => $temp_multiplier){
      // Skip non-type and special fields for this calculation
      if (in_array($temp_type, $skip_types)){ continue; }
      // If this ability's type matches the multiplier, apply it
      if ($temp_ability_recovery_type == $temp_type || $temp_ability_recovery_type2 == $temp_type){
        $new_recovery_amount = round($recovery_amount * $temp_multiplier);
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.''); }
        $recovery_amount = $new_recovery_amount;
      }
    }
  }
  if (!empty($this_ability->recovery_options['recovery_type2'])){
    foreach ($field_multipliers AS $temp_type => $temp_multiplier){
      // Skip non-type and special fields for this calculation
      if (in_array($temp_type, $skip_types)){ continue; }
      // If this ability's type matches the multiplier, apply it
      if ($this_ability->recovery_options['recovery_type2'] == $temp_type){
        $new_recovery_amount = round($recovery_amount * $temp_multiplier);
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.''); }
        $recovery_amount = $new_recovery_amount;
      }
    }
  }
}

// Update the ability results with the the trigger kind and recovery details
$this_ability->ability_results['trigger_kind'] = 'recovery';
$this_ability->ability_results['recovery_kind'] = $this_ability->recovery_options['recovery_kind'];
$this_ability->ability_results['recovery_type'] = $this_ability->recovery_options['recovery_type'];
$this_ability->ability_results['recovery_type2'] = $this_ability->recovery_options['recovery_type2'];

// If the success rate was not provided, auto-calculate
if ($this_ability->recovery_options['success_rate'] == 'auto'){
  // If this robot is targetting itself, default to ability accuracy
  if ($this->robot_id == $target_robot->robot_id){
    // Update the success rate to the ability accuracy value
    $this_ability->recovery_options['success_rate'] = $this_ability->ability_accuracy;
  }
  // Otherwise, if this robot is in speed break or ability accuracy 100%
  elseif ($target_robot->robot_speed <= 0 && $this->robot_speed > 0){
    // Hard-code the success rate at 100% accuracy
      $this_ability->recovery_options['success_rate'] = 0;
  }
  // Otherwise, if this robot is in speed break or ability accuracy 100%
  elseif ($this->robot_speed <= 0 || $this_ability->ability_accuracy == 100){
    // Hard-code the success rate at 100% accuracy
      $this_ability->recovery_options['success_rate'] = 100;
  }
  // Otherwise, calculate the success rate based on relative speeds
  else {
    // Collect this ability's accuracy stat for modification
    $this_ability_accuracy = $this_ability->ability_accuracy;
    // If the target was faster/slower, boost/lower the ability accuracy
    if ($target_robot_speed_start > $this->robot_speed
      || $target_robot_speed_start < $this->robot_speed){
      $this_modifier = $target_robot_speed_start / $this->robot_speed;
      //$this_ability_accuracy = ceil($this_ability_accuracy * $this_modifier);
      $this_ability_accuracy = ceil($this_ability_accuracy * 0.95) + ceil(($this_ability_accuracy * 0.05) * $this_modifier);
      if ($this_ability_accuracy > 100){ $this_ability_accuracy = 100; }
      elseif ($this_ability_accuracy < 0){ $this_ability_accuracy = 0; }
    }
    // Update the success rate to the ability accuracy value
    $this_ability->recovery_options['success_rate'] = $this_ability_accuracy;
    //$this_ability->ability_results['this_text'] .= '';
  }
}

// If the failure rate was not provided, auto-calculate
if ($this_ability->recovery_options['failure_rate'] == 'auto'){
  // Set the failure rate to the difference of success vs failure (100% base)
  $this_ability->recovery_options['failure_rate'] = 100 - $this_ability->recovery_options['success_rate'];
  if ($this_ability->recovery_options['failure_rate'] < 0){
    $this_ability->recovery_options['failure_rate'] = 0;
  }
}

// If this robot is in speed break, increase success rate, reduce failure
if ($this->robot_speed == 0 && $this_ability->recovery_options['success_rate'] > 0){
  $this_ability->recovery_options['success_rate'] = ceil($this_ability->recovery_options['success_rate'] * 2);
  $this_ability->recovery_options['failure_rate'] = ceil($this_ability->recovery_options['failure_rate'] / 2);
}
// If the target robot is in speed break, decease the success rate, increase failure
elseif ($target_robot->robot_speed == 0 && $this_ability->recovery_options['success_rate'] > 0){
  $this_ability->recovery_options['success_rate'] = ceil($this_ability->recovery_options['success_rate'] / 2);
  $this_ability->recovery_options['failure_rate'] = ceil($this_ability->recovery_options['failure_rate'] * 2);
}

// If success rate is at 100%, auto-set the result to success
if ($this_ability->recovery_options['success_rate'] == 100){
  // Set this ability result as a success
  $this_ability->recovery_options['failure_rate'] = 0;
  $this_ability->ability_results['this_result'] = 'success';
}
// Else if the success rate is at 0%, auto-set the result to failure
elseif ($this_ability->recovery_options['success_rate'] == 0){
  // Set this ability result as a failure
  $this_ability->recovery_options['failure_rate'] = 100;
  $this_ability->ability_results['this_result'] = 'failure';
}
// Otherwise, use a weighted random generation to get the result
else {
  // Calculate whether this attack was a success, based on the success vs. failure rate
  $this_ability->ability_results['this_result'] = $this->battle->weighted_chance(
    array('success','failure'),
    array($this_ability->recovery_options['success_rate'], $this_ability->recovery_options['failure_rate'])
    );
}

// If this is ENERGY recovery and this robot is already at full health
if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->robot_energy >= $this->robot_base_energy){
  // Hard code the result to failure
  $this_ability->ability_results['this_result'] = 'failure';
}
// If this is WEAPONS recovery and this robot is already at full ammo
elseif ($this_ability->recovery_options['recovery_kind'] == 'weapons' && $this->robot_weapons >= $this->robot_base_weapons){
  // Hard code the result to failure
  $this_ability->ability_results['this_result'] = 'failure';
}
// Otherwise if ATTACK recovery but attack is already at 9999
elseif ($this_ability->recovery_options['recovery_kind'] == 'attack' && $this->robot_attack >= MMRPG_SETTINGS_STATS_MAX){
  // Hard code the result to failure
  $this_ability->ability_results['this_result'] = 'failure';
}
// Otherwise if DEFENSE recovery but defense is already at 9999
elseif ($this_ability->recovery_options['recovery_kind'] == 'defense' && $this->robot_defense >= MMRPG_SETTINGS_STATS_MAX){
  // Hard code the result to failure
  $this_ability->ability_results['this_result'] = 'failure';
}
// Otherwise if SPEED recovery but speed is already at 9999
elseif ($this_ability->recovery_options['recovery_kind'] == 'speed' && $this->robot_speed >= MMRPG_SETTINGS_STATS_MAX){
  // Hard code the result to failure
  $this_ability->ability_results['this_result'] = 'failure';
}

// If this robot has immunity to the ability, hard-code a failure result
if ($this_ability->ability_results['flag_immunity']){
  $this_ability->ability_results['this_result'] = 'failure';
  $this->flags['triggered_immunity'] = true;
  // Generate the status text based on flags
  $this_flag_name = 'immunity_text';
  if (isset($this_ability->recovery_options[$this_flag_name])){
    $this_ability->ability_results['this_text'] .= ' '.$this_ability->recovery_options[$this_flag_name].'<br /> ';
  }
}

// If the attack was a success, proceed normally
if ($this_ability->ability_results['this_result'] == 'success'){

  // Create the experience multiplier if not already set
  if (!isset($this->field->field_multipliers['experience'])){ $this->field->field_multipliers['experience'] = 1; }
  elseif ($this->field->field_multipliers['experience'] < 0.1){ $this->field->field_multipliers['experience'] = 0.1; }
  elseif ($this->field->field_multipliers['experience'] > 9.9){ $this->field->field_multipliers['experience'] = 9.9; }

  // If modifiers are not turned off
  if ($trigger_options['apply_modifiers'] != false){

    // Update this robot's internal flags based on ability effects
    if (!empty($this_ability->ability_results['flag_weakness'])){
      $this->flags['triggered_weakness'] = true;
      if (isset($this->counters['triggered_weakness'])){ $this->counters['triggered_weakness'] += 1; }
      else { $this->counters['triggered_weakness'] = 1; }
      if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){
        $this->field->field_multipliers['experience'] += 0.1;
        $this_ability->recovery_options['recovery_kickback']['x'] = ceil($this_ability->recovery_options['recovery_kickback']['x'] * 2);
      }
      //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
    }
    if (!empty($this_ability->ability_results['flag_affinity'])){
      $this->flags['triggered_affinity'] = true;
      if (isset($this->counters['triggered_affinity'])){ $this->counters['triggered_affinity'] += 1; }
      else { $this->counters['triggered_affinity'] = 1; }
      if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
      //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
    }
    if (!empty($this_ability->ability_results['flag_resistance'])){
      $this->flags['triggered_resistance'] = true;
      if (isset($this->counters['triggered_resistance'])){ $this->counters['triggered_resistance'] += 1; }
      else { $this->counters['triggered_resistance'] = 1; }
      if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
      //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
    }
    if (!empty($this_ability->ability_results['flag_critical'])){
      $this->flags['triggered_critical'] = true;
      if (isset($this->counters['triggered_critical'])){ $this->counters['triggered_critical'] += 1; }
      else { $this->counters['triggered_critical'] = 1; }
      if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){
        $this->field->field_multipliers['experience'] += 0.1;
        $this_ability->recovery_options['recovery_kickback']['x'] = ceil($this_ability->recovery_options['recovery_kickback']['x'] * 2);
      }
      //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
    }

  }

  // Update the field session with any changes
  $this->field->update_session();

  // Update this robot's frame based on recovery type
  $this->robot_frame = $this_ability->recovery_options['recovery_frame'];
  $this->player->player_frame = ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery']) ? 'taunt' : 'base';
  $this_ability->ability_frame = $this_ability->recovery_options['ability_success_frame'];
  $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_success_frame_offset'];

  // Display the success text, if text has been provided
  if (!empty($this_ability->recovery_options['success_text'])){
    $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['success_text'];
  }

  // Collect the recovery amount argument from the function
  $this_ability->ability_results['this_amount'] = $recovery_amount;

  // If we're not dealing with a percentage-based amount, apply stat mods
  if ($trigger_options['apply_stat_modifiers'] != false && !$this_ability->recovery_options['recovery_percent']){

    // Only apply ATTACK/DEFENSE mods if this robot is not targetting itself and it's ENERGY based recovery
    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery'])){

      // Backup the current ammount before stat multipliers
      $temp_amount_backup = $this_ability->ability_results['this_amount'];

      // If this robot's defense is at absolute zero, and the target's attack isnt, OHKO
      if ($this->robot_defense <= 0 && $target_robot_attack_start >= 1){
        // Set the new recovery amount to OHKO this robot
        $temp_new_amount = $this->robot_base_energy;
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$this->robot_token.'_defense_break | D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.''); }
        // Update the amount with the new calculation
        $this_ability->ability_results['this_amount'] = $temp_new_amount;
      }
      // Elseif the target robot's attack is at absolute zero, and the this's defense isnt, NOKO
      elseif ($target_robot_attack_start <= 0 && $this->robot_defense >= 1){
        // Set the new recovery amount to NOKO this robot
        $temp_new_amount = 0;
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break | A:'.$target_robot->robot_attack.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.''); }
        // Update the amount with the new calculation
        $this_ability->ability_results['this_amount'] = $temp_new_amount;
      }
      // Elseif this robot's defense is at absolute zero and the target's attack is too, NOKO
      elseif ($this->robot_defense <= 0 && $target_robot_attack_start <= 0){
        // Set the new recovery amount to NOKO this robot
        $temp_new_amount = 0;
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break and '.$this->robot_token.'_defense_break | A:'.$target_robot->robot_attack.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.''); }
        // Update the amount with the new calculation
        $this_ability->ability_results['this_amount'] = $temp_new_amount;
      }
      // Otherwise if both robots have normal stats, calculate the new amount normally
      else {

        // Check to make sure starforce is enabled right now
        $temp_starforce_enabled = true;
        if (!empty($this->player->counters['dark_elements'])){ $temp_starforce_enabled = false; }
        if (!empty($target_robot->player->counters['dark_elements'])){ $temp_starforce_enabled = false; }
        if (empty($target_robot->player->player_starforce[$temp_ability_recovery_type]) && empty($target_robot->player->player_starforce[$temp_ability_recovery_type2])){ $temp_starforce_enabled = false; }

        // Collect the target's attack stat and this robot's defense values
        $target_robot_attack = $target_robot_attack_start;
        $this_robot_defense = $this->robot_defense;

        if (MMRPG_CONFIG_DEBUG_MODE && $temp_starforce_enabled){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | attack_vs_defense | before_starforce |  A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' '); }

        // If the target player has any starforce for type1, apply it to the attack
        if ($temp_starforce_enabled && !empty($target_robot->player->player_starforce[$temp_ability_recovery_type])){
          $temp_attack_boost = $target_robot->player->player_starforce[$temp_ability_recovery_type] * MMRPG_SETTINGS_STARS_ATTACKBOOST;
          $temp_new_attack = $target_robot_attack + $temp_attack_boost;
          if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | target_starforce | '.$temp_ability_recovery_type.'_boost = '.$temp_attack_boost.' | robot_attack = round('.$target_robot_attack.' + '.$temp_attack_boost.') = '.$temp_new_attack.''); }
          $target_robot_attack = $temp_new_attack;
          // If the target player has any starforce for type2, apply it to the attack
          if (!empty($target_robot->player->player_starforce[$temp_ability_recovery_type2])){
            $temp_attack_boost = $target_robot->player->player_starforce[$temp_ability_recovery_type2] * MMRPG_SETTINGS_STARS_ATTACKBOOST;
            $temp_new_attack = $target_robot_attack + $temp_attack_boost;
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | target_starforce | '.$temp_ability_recovery_type2.'_boost = '.$temp_attack_boost.' | robot_attack = round('.$target_robot_attack.' + '.$temp_attack_boost.') = '.$temp_new_attack.''); }
            $target_robot_attack = $temp_new_attack;
          }
        }

        // If this player has any starforce for type2, apply it to the defense
        if ($temp_starforce_enabled && !empty($this->player->player_starforce[$temp_ability_recovery_type])){
          $temp_defense_boost = $this->player->player_starforce[$temp_ability_recovery_type] * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
          $temp_new_defense = $this_robot_defense + $temp_defense_boost;
          if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | this_starforce | '.$temp_ability_recovery_type.'_boost = '.$temp_defense_boost.' | robot_defense = round('.$this_robot_defense.' + '.$temp_defense_boost.') = '.$temp_new_defense.''); }
          $this_robot_defense = $temp_new_defense;
          // If the target player has any starforce for type2, apply it to the defense
          if (!empty($this->player->player_starforce[$temp_ability_recovery_type2])){
            $temp_defense_boost = $this->player->player_starforce[$temp_ability_recovery_type2] * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
            $temp_new_defense = $this_robot_defense + $temp_defense_boost;
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | this_starforce | '.$temp_ability_recovery_type2.'_boost = '.$temp_defense_boost.' | robot_defense = round('.$this_robot_defense.' + '.$temp_defense_boost.') = '.$temp_new_defense.''); }
            $this_robot_defense = $temp_new_defense;
          }
        }

        if (MMRPG_CONFIG_DEBUG_MODE && $temp_starforce_enabled){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | attack_vs_defense | after_starforce | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' '); }

        // Set the new recovery amount relative to this robot's defense and the target robot's attack
        $temp_new_amount = round($this_ability->ability_results['this_amount'] * ($target_robot_attack / $this_robot_defense));

        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | normal_recovery | A:'.$target_robot_attack.' vs. D:'.$this_robot_defense.' | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * ('.$target_robot_attack.' / '.$this_robot_defense.')) = '.$temp_new_amount.''); }

        // Update the amount with the new calculation
        $this_ability->ability_results['this_amount'] = $temp_new_amount;

      }

      // If this robot started out above zero but is now absolute zero, round up
      if ($temp_amount_backup > 0 && $this_ability->ability_results['this_amount'] == 0){ $this_ability->ability_results['this_amount'] = 1; }

    }

    // If this is a critical hit (lucky, based on turn and level)
    $temp_flag_critical = $this->battle->critical_turn($this->battle->counters['battle_turn'], $target_robot->robot_level, $target_robot->robot_item);
    if ($temp_flag_critical){
      $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] * $this_ability->recovery_options['critical_multiplier'];
      $this_ability->ability_results['flag_critical'] = true;
      // DEBUG
      if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_critical | x '.$this_ability->recovery_options['critical_multiplier'].' = '.$this_ability->ability_results['this_amount'].''); }
    } else {
      $this_ability->ability_results['flag_critical'] = false;
    }

  }

  // Only apply weakness, resistance, etc. if allowed to
  if ($trigger_options['apply_type_modifiers'] != false){

    // If this robot has an affinity to the ability (based on type)
    if ($this_ability->ability_results['flag_affinity']){
      $loop_count = $this_ability->ability_results['counter_affinities'] / ($this_ability->ability_results['total_strikes'] + 1);
      for ($i = 1; $i <= $loop_count; $i++){
        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['affinity_multiplier']);
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_affinity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['affinity_multiplier'].') = '.$temp_new_amount.''); }
        $this_ability->ability_results['this_amount'] = $temp_new_amount;
      }
    }

    // If target robot resists the ability (based on type)
    if ($this_ability->ability_results['flag_resistance']){
      $loop_count = $this_ability->ability_results['counter_resistances'] / ($this_ability->ability_results['total_strikes'] + 1);
      for ($i = 1; $i <= $loop_count; $i++){
        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['resistance_multiplier']);
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_resistance ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['resistance_multiplier'].') = '.$temp_new_amount.''); }
        $this_ability->ability_results['this_amount'] = $temp_new_amount;
      }
    }

    // If target robot is immune to the ability (based on type)
    if ($this_ability->ability_results['flag_immunity']){
      $loop_count = $this_ability->ability_results['counter_immunities'] / ($this_ability->ability_results['total_strikes'] + 1);
      for ($i = 1; $i <= $loop_count; $i++){
        $this_ability->ability_results['this_amount'] = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['immunity_multiplier']);
        // DEBUG
        if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_immunity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['immunity_multiplier'].') = '.$temp_new_amount.''); }
        $this_ability->ability_results['this_amount'] = $temp_new_amount;
      }
    }

  }

  // Only apply other modifiers if allowed to
  if ($trigger_options['apply_modifiers'] != false){

    // If this robot has an attachment with a recovery multiplier
    if (!empty($this->robot_attachments)){
      foreach ($this->robot_attachments AS $temp_token => $temp_info){
        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

        // First check to see if any basic boosters or breakers have been created for this robot
        if (true){
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_breaker'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_booster'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery input breaker value set
          if (isset($temp_info['attachment_recovery_input_breaker'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery input booster value set
          if (isset($temp_info['attachment_recovery_input_booster'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
        }
        // Next check to see if any boosters or breakers for either of this ability's types
        if (!empty($this_ability->ability_type)){
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
        }
        // Next check to see if any boosters or breakers for either of this ability's types
        if (!empty($this_ability->ability_type2)){
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
        }

      }
    }

    // If this robot has an attachment with a recovery multiplier
    if (!empty($target_robot->robot_attachments)){
      foreach ($target_robot->robot_attachments AS $temp_token => $temp_info){
        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

        // First check to see if any basic boosters or breakers have been created for this robot
        if (true){
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_breaker'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_booster'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery output breaker value set
          if (isset($temp_info['attachment_recovery_output_breaker'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery output booster value set
          if (isset($temp_info['attachment_recovery_output_booster'])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster']);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster'].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
        }
        // Next check to see if any boosters or breakers for either of this ability's types
        if (!empty($this_ability->ability_type)){
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
        }
        // Next check to see if any boosters or breakers for either of this ability's types
        if (!empty($this_ability->ability_type2)){
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery breaker value set
          if (isset($temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
          // If this robot's attachment has a recovery booster value set
          if (isset($temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2])){
            // Apply the recovery breaker multiplier to the current recovery amount
            $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2]);
            // DEBUG
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.''); }
            $this_ability->ability_results['this_amount'] = $temp_new_amount;
          }
        }

      }
    }


  }

  // Generate the flag string for easier parsing
  $this_flag_string = array();
  if ($this_ability->ability_results['flag_immunity']){ $this_flag_string[] = 'immunity'; }
  elseif ($trigger_options['apply_type_modifiers'] != false){
    if (!empty($this_ability->ability_results['flag_weakness'])){ $this_flag_string[] = 'weakness'; }
    if (!empty($this_ability->ability_results['flag_affinity'])){ $this_flag_string[] = 'affinity'; }
    if (!empty($this_ability->ability_results['flag_resistance'])){ $this_flag_string[] = 'resistance'; }
    if ($trigger_options['apply_modifiers'] != false && !$this_ability->recovery_options['recovery_percent']){
      if (!empty($this_ability->ability_results['flag_critical'])){ $this_flag_string[] = 'critical'; }
    }
  }
  $this_flag_name = (!empty($this_flag_string) ? implode('_', $this_flag_string).'_' : '').'text';

  // Generate the status text based on flags
  if (isset($this_ability->recovery_options[$this_flag_name])){
    //$event_options['console_container_height'] = 2;
    //$this_ability->ability_results['this_text'] .= '<br />';
    $this_ability->ability_results['this_text'] .= ' '.$this_ability->recovery_options[$this_flag_name];
  }

  // Display a break before the recovery amount if other text was generated
  if (!empty($this_ability->ability_results['this_text'])){
    $this_ability->ability_results['this_text'] .= '<br />';
  }

  // Ensure the recovery amount is always at least one, unless absolute zero
  if ($this_ability->ability_results['this_amount'] < 1 && $this_ability->ability_results['this_amount'] > 0){ $this_ability->ability_results['this_amount'] = 1; }

  // Reference the requested recovery kind with a shorter variable
  $this_ability->recovery_options['recovery_kind'] = strtolower($this_ability->recovery_options['recovery_kind']);
  $recovery_stat_name = 'robot_'.$this_ability->recovery_options['recovery_kind'];

  // Inflict the approiate recovery type based on the recovery options
  switch ($recovery_stat_name){

    // If this is an ATTACK type recovery trigger
    case 'robot_attack': {
      // Inflict attack recovery on the target's internal stat
      $this->robot_attack = $this->robot_attack + $this_ability->ability_results['this_amount'];
      // If the recovery put the robot's attack above 9999
      if ($this->robot_attack > MMRPG_SETTINGS_STATS_MAX){
        // Calculate the overkill amount
        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_attack) * -1;
        // Calculate the actual recovery amount
        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
        // Max out the robots attack
        $this->robot_attack = MMRPG_SETTINGS_STATS_MAX;
      }
      // Break from the ATTACK case
      break;
    }
    // If this is an DEFENSE type recovery trigger
    case 'robot_defense': {
      // Inflict defense recovery on the target's internal stat
      $this->robot_defense = $this->robot_defense + $this_ability->ability_results['this_amount'];
      // If the recovery put the robot's defense above 9999
      if ($this->robot_defense > MMRPG_SETTINGS_STATS_MAX){
        // Calculate the overkill amount
        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_defense) * -1;
        // Calculate the actual recovery amount
        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
        // Max out the robots defense
        $this->robot_defense = MMRPG_SETTINGS_STATS_MAX;
      }
      // Break from the DEFENSE case
      break;
    }
    // If this is an SPEED type recovery trigger
    case 'robot_speed': {
      // Inflict speed recovery on the target's internal stat
      $this->robot_speed = $this->robot_speed + $this_ability->ability_results['this_amount'];
      // If the recovery put the robot's speed above 9999
      if ($this->robot_speed > MMRPG_SETTINGS_STATS_MAX){
        // Calculate the overkill amount
        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_speed) * -1;
        // Calculate the actual recovery amount
        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
        // Max out the robots speed
        $this->robot_speed = MMRPG_SETTINGS_STATS_MAX;
      }
      // Break from the SPEED case
      break;
    }
    // If this is a WEAPONS type recovery trigger
    case 'robot_weapons': {
      // Inflict weapon recovery on the target's internal stat
      $this->robot_weapons = $this->robot_weapons + $this_ability->ability_results['this_amount'];
      // If the recovery put the robot's weapons above the base
      if ($this->robot_weapons > $this->robot_base_weapons){
        // Calculate the overcure amount
        $this_ability->ability_results['this_overkill'] = ($this->robot_base_weapons - $this->robot_weapons) * -1;
        // Calculate the actual recovery amount
        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
        // Max out the robots weapons
        $this->robot_weapons = $this->robot_base_weapons;
      }
      // Break from the WEAPONS case
      break;
    }
    // If this is an ENERGY type recovery trigger
    case 'robot_energy': default: {
      // Inflict the actual recovery on the robot
      $this->robot_energy = $this->robot_energy + $this_ability->ability_results['this_amount'];
      // If the recovery put the robot into overboost, recalculate the recovery
      if ($this->robot_energy > $this->robot_base_energy){
        // Calculate the overcure amount
        $this_ability->ability_results['this_overboost'] = ($this->robot_base_energy - $this->robot_energy) * -1;
        if ($this_ability->ability_results['this_overboost'] > $this->robot_base_energy){ $this_ability->ability_results['this_overboost'] = $this->robot_base_energy; }
        // Calculate the actual recovery amount
        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
        // Max out the robots energy
        $this->robot_energy = $this->robot_base_energy;
      }
      // If the robot's energy has dropped to zero, disable them
      if ($this->robot_energy == 0){
        // Change the status to disabled
        $this->robot_status = 'disabled';
        // Remove any attachments this robot has
        if (!empty($this->robot_attachments)){
          foreach ($this->robot_attachments AS $token => $info){
            if (empty($info['sticky'])){ unset($this->robot_attachments[$token]); }
          }
        }
      }
      // Break from the ENERGY case
      break;
    }

  }

  // Define the print variables to return
  $this_ability->ability_results['print_strikes'] = '<span class="recovery_strikes">'.(!empty($this_ability->ability_results['total_strikes']) ? $this_ability->ability_results['total_strikes'] : 0).'</span>';
  $this_ability->ability_results['print_misses'] = '<span class="recovery_misses">'.(!empty($this_ability->ability_results['total_misses']) ? $this_ability->ability_results['total_misses'] : 0).'</span>';
  $this_ability->ability_results['print_result'] = '<span class="recovery_result">'.(!empty($this_ability->ability_results['total_result']) ? $this_ability->ability_results['total_result'] : 0).'</span>';
  $this_ability->ability_results['print_amount'] = '<span class="recovery_amount">'.(!empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0).'</span>';
  $this_ability->ability_results['print_overkill'] = '<span class="recovery_overkill">'.(!empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0).'</span>';

  // Add the final recovery text showing the amount based on life energy recovery
  if ($this_ability->recovery_options['recovery_kind'] == 'energy'){
    $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} recovers {$this_ability->ability_results['print_amount']} life energy";
    //$this_ability->ability_results['this_text'] .= ($this_ability->ability_results['this_overkill'] > 0 ? " and {$this_ability->ability_results['print_overkill']} overkill" : '');
    $this_ability->ability_results['this_text'] .= '!<br />';
  }
  // Otherwise add the final recovery text showing the amount based on weapon energy recovery
  elseif ($this_ability->recovery_options['recovery_kind'] == 'weapons'){
    $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} recovers {$this_ability->ability_results['print_amount']} weapon energy";
    $this_ability->ability_results['this_text'] .= '!<br />';
  }
  // Otherwise, if this is one of the robot's other internal stats
  elseif ($this_ability->recovery_options['recovery_kind'] == 'attack'
    || $this_ability->recovery_options['recovery_kind'] == 'defense'
    || $this_ability->recovery_options['recovery_kind'] == 'speed'){
    // Print the result based on if the stat will go any lower
    if ($this_ability->ability_results['this_amount'] > 0){
      $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()}&#39;s {$this_ability->recovery_options['recovery_kind']} rose by {$this_ability->ability_results['print_amount']}";
      $this_ability->ability_results['this_text'] .= '!<br />';
    }
    // Otherwise if the stat wouldn't go any lower
    else {

      // Update this robot's frame based on recovery type
      $this_ability->ability_frame = $this_ability->recovery_options['ability_failure_frame'];
      $this_ability->ability_frame_span = $this_ability->recovery_options['ability_failure_frame_span'];
      $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_failure_frame_offset'];

      // Display the failure text, if text has been provided
      if (!empty($this_ability->recovery_options['failure_text'])){
        $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['failure_text'].' ';
      }
    }
  }

}
// Otherwise, if the attack was a failure
else {

  // Update this robot's frame based on recovery type
  $this_ability->ability_frame = $this_ability->recovery_options['ability_failure_frame'];
  $this_ability->ability_frame_span = $this_ability->recovery_options['ability_failure_frame_span'];
  $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_failure_frame_offset'];

  // Update the recovery and overkilll amounts to reflect zero recovery
  $this_ability->ability_results['this_amount'] = 0;
  $this_ability->ability_results['this_overkill'] = 0;

  // Display the failure text, if text has been provided
  if (!$this_ability->ability_results['flag_immunity'] && !empty($this_ability->recovery_options['failure_text'])){
    $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['failure_text'].' ';
  }

}

// Update this robot's history with the triggered recovery amount
$this->history['triggered_recovery'][] = $this_ability->ability_results['this_amount'];
// Update the robot's history with the triggered recovery types
if (!empty($this_ability->ability_results['recovery_type'])){
  $temp_types = array();
  $temp_types[] = $this_ability->ability_results['recovery_type'];
  if (!empty($this_ability->ability_results['recovery_type2'])){ $temp_types[] = $this_ability->ability_results['recovery_type2']; }
  $this->history['triggered_recovery_types'][] = $temp_types;
} else {
  $this->history['triggered_recovery_types'][] = array();
}
// Update this robot's history with the overboost if applicable
if (!empty($this_ability->ability_results['this_overboost'])){
  $this->counters['assist_overboost'] = isset($this->counters['assist_overboost']) ? $this->counters['assist_overboost'] + $this_ability->ability_results['this_overboost'] : $this_ability->ability_results['this_overboost'];
}

// Update the recovery result total variables
$this_ability->ability_results['total_amount'] += !empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0;
$this_ability->ability_results['total_overkill'] += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
if ($this_ability->ability_results['this_result'] == 'success'){ $this_ability->ability_results['total_strikes']++; }
else { $this_ability->ability_results['total_misses']++; }
$this_ability->ability_results['total_actions'] = $this_ability->ability_results['total_strikes'] + $this_ability->ability_results['total_misses'];
if ($this_ability->ability_results['total_result'] != 'success'){ $this_ability->ability_results['total_result'] = $this_ability->ability_results['this_result']; }
$event_options['this_ability_results'] = $this_ability->ability_results;

// Update internal variables
$target_robot->update_session();
$target_robot->player->update_session();
$this->update_session();
$this->player->update_session();

// If this robot was at full energy but is now at zero, it's a OHKO
$this_robot_energy_ohko = false;
if ($this->robot_energy <= 0 && $this_robot_energy_start_max){
  // DEBUG
  if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | damage_result_OHKO! | Start:'.$this_robot_energy_start.' '.($this_robot_energy_start_max ? '(MAX!)' : '-').' | Finish:'.$this->robot_energy); }
  // Ensure the attacking player was a human
  if ($this->player->player_side == 'right'){
    $this_robot_energy_ohko = true;
    // Increment the field multipliers for items
    //if (!isset($this->field->field_multipliers['items'])){ $this->field->field_multipliers['items'] = 1; }
    //$this->field->field_multipliers['items'] += 0.1;
    //$this->field->update_session();
  }
}

// Generate an event with the collected recovery results based on recovery type
if ($this->robot_id == $target_robot->robot_id){ //$this_ability->recovery_options['recovery_kind'] == 'energy'
  $event_options['console_show_target'] = false;
  $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
  $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
  $this->battle->events_create($target_robot, $this, $this_ability->recovery_options['recovery_header'], $this_ability->ability_results['this_text'], $event_options);
} else {
  $event_options['console_show_target'] = false;
  $event_options['this_ability_user'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
  $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;;
  $this->battle->events_create($this, $target_robot, $this_ability->recovery_options['recovery_header'], $this_ability->ability_results['this_text'], $event_options);
}

// Restore this and the target robot's frames to their backed up state
$this->robot_frame = $this_robot_backup_frame;
$this->player->player_frame = $this_player_backup_frame;
$target_robot->robot_frame = $target_robot_backup_frame;
$target_robot->player->player_frame = $target_player_backup_frame;
$this_ability->ability_frame = $this_ability_backup_frame;

// Update internal variables
$target_robot->update_session();
$target_robot->player->update_session();
$this->update_session();
$this->player->update_session();
$this_ability->update_session();

// If this robot has been disabled, add a defeat attachment
if ($this->robot_status == 'disabled'){
  // Define this ability's attachment token
  $temp_frames = array(0,4,1,5,2,6,3,7,4,8,5,9,0,1,2,3,4,5,6,7,8,9);
  shuffle($temp_frames);
  $this_attachment_token = 'ability_attachment-defeat';
  $this_attachment_info = array(
  	'class' => 'ability',
  	'ability_token' => 'attachment-defeat',
  	'attachment_flag_defeat' => true,
    'ability_frame' => 0,
    'ability_frame_animate' => $temp_frames,
    'ability_frame_offset' => array('x' => 0, 'y' => -10, 'z' => -10)
    );

  // If the attachment doesn't already exists, add it to the robot
  if (!isset($this->robot_attachments[$this_attachment_token])){
    $this->robot_attachments[$this_attachment_token] =  $this_attachment_info;
    $this->update_session();
  }

}

// If this robot was disabled, process experience for the target
if ($this->robot_status == 'disabled' && $trigger_disabled){
  $trigger_options = array();
  if ($this_robot_energy_ohko){ $trigger_options['item_multiplier'] = 2.0; }
  $this->trigger_disabled($target_robot, $this_ability, $trigger_options);
}
// Otherwise, if the target robot was not disabled
elseif ($this->robot_status != 'disabled'){
  // -- CHECK ATTACHMENTS -- //

  // Ensure the ability was a success before checking attachments
  if ($this_ability->ability_results['this_result'] == 'success'){
    // If this robot has any attachments, loop through them
    if (!empty($this->robot_attachments)){
      if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | has_attachments | '.implode(', ', array_keys($this->robot_attachments))); }
      foreach ($this->robot_attachments AS $attachment_token => $attachment_info){
        // Ensure this ability has a type before checking weaknesses, resistances, etc.
        if (!empty($this_ability->ability_type)){
          // If this attachment has weaknesses defined and this ability is a match
          if (!empty($attachment_info['attachment_weaknesses'])
            && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses']) || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))){
            if (MMRPG_CONFIG_DEBUG_MODE){ $this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses'); }
            // Remove this attachment and inflict damage on the robot
            unset($this->robot_attachments[$attachment_token]);
            $this->update_session();
            if ($attachment_info['attachment_destroy'] !== false){
              $temp_attachment = new mmrpg_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));
              $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
              //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
              //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
              if ($temp_trigger_type == 'damage'){
                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->update_session();
                $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                  $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                  $temp_trigger_options = array('apply_modifiers' => false);
                  $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                }
              } elseif ($temp_trigger_type == 'recovery'){
                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->update_session();
                $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                  $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                  $temp_trigger_options = array('apply_modifiers' => false);
                  $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                }
              } elseif ($temp_trigger_type == 'special'){
                $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                $temp_attachment->update_session();
                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
              }
            }
            // If this robot was disabled, process experience for the target
            if ($this->robot_status == 'disabled'){ break; }
          }

        }

      }
    }

  }

}
?>