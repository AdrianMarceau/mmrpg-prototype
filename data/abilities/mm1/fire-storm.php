<?
// FIRE STORM
$ability = array(
  'ability_name' => 'Fire Storm',
  'ability_token' => 'fire-storm',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/007',
  'ability_master' => 'fire-man',
  'ability_number' => 'DLN-007',
  'ability_description' => 'The user unleashes a blast of fire at the target, dealing damage and raising the user\'s defense by {RECOVERY2}%! This ability has three levels of charge, with damage multiplying on each successive blast until the limit is reached or another technique is used.',
  'ability_type' => 'flame',
  'ability_energy' => 4,
  'ability_damage' => 12,
  'ability_recovery2' => 8,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    if (!isset($this_robot->robot_attachments[$this_attachment_token])){
      $this_attachment_info = array(
      	'class' => 'ability',
      	'ability_token' => $this_ability->ability_token,
      	'attachment_duration' => 1,
        'attachment_power' => 0,
        'attachment_defense' => 0,
      	'attachment_weaknesses' => array('freeze', 'wind'),
      	'attachment_create' => array(
          'kind' => 'defense',
          'percent' => true,
          'frame' => 'taunt',
          'rates' => array(100, 0, 0),
          'success' => array(0, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s flame was bolstered!'),
          'failure' => array(0, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s flame was not affected&hellip;')
          ),
      	'attachment_destroy' => array(
          'kind' => 'defense',
          'type' => '',
          'type2' => '',
          'percent' => true,
          'modifiers' => false,
          'frame' => 'defend',
          'rates' => array(100, 0, 0),
          'success' => array(0, 0, 0, -9999,  'The '.$this_ability->print_ability_name().'&#39;s flame was lost&hellip;'),
          'failure' => array(0, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s flame was not affected&hellip;')
          ),
          'ability_frame' => 3,
          'ability_frame_animate' => array(3, 4, 5, 6),
          'ability_frame_offset' => array('x' => -15, 'y' => -5, 'z' => -10)
        );
    } else {
      $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
      $this_attachment_info['attachment_duration'] = 1;
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();
    }

    /*
    // If this ability is attached, remove it
    $this_attachment_backup = false;
    if (isset($this_robot->robot_attachments[$this_attachment_token])){
      $this_attachment_backup = $this_robot->robot_attachments[$this_attachment_token];
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();
      $this_attachment_info['attachment_defense'] = $this_attachment_backup['attachment_defense'];
    }
    */

    // Collect the shot power counter if set, otherwise default to level one
    $shot_power = !empty($this_attachment_info['attachment_power']) ? $this_attachment_info['attachment_power'] : 0;
    // Reward successive uses of this ability with boosts in power
    if (!empty($this_robot->history['triggered_abilities'])){
      // Collect up to the last three triggered abilities
      $ability_history_count = count($this_robot->history['triggered_abilities']);
      if ($ability_history_count <= 3){ $recent_ability_history = $this_robot->history['triggered_abilities']; }
      else { $recent_ability_history = array_slice($this_robot->history['triggered_abilities'], -3, 3, false); }
      $recent_ability_history = array_reverse($recent_ability_history, false);
      // If this ability was used last turn, increment the base power
      if (isset($recent_ability_history[1]) && $recent_ability_history[1] == $this_ability->ability_token){ $shot_power++; }
      else { $shot_power = 1; }
    }
    // Update this ability's internal shot power counter
    $this_attachment_info['attachment_power'] = $shot_power;

    // Update the text and animation frames
    $shot_power_text = 'A flare ';
    $shot_power_frame = 0;
    if ($shot_power == 2){ $shot_power_text = 'A powerful flare '; $shot_power_frame = 1; }
    elseif ($shot_power == 3){ $shot_power_text = 'A massive flare '; $shot_power_frame = 2; }

    // If the shot power is charging, attach this ability to the robot
    if ($shot_power < 3){
      if ($shot_power == 1){ $this_attachment_info['ability_frame_animate'] = array(3, 4, 5, 6); }
      elseif ($shot_power == 2){ $this_attachment_info['ability_frame_animate'] = array(7, 8); }
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();
    } else {
      unset($this_robot->robot_attachments[$this_attachment_token]);
      unset($this_ability->counters['shot_power']);
      $this_robot->update_session();
      $this_ability->update_session();
    }
    // Update the robot either way
    $this_robot->update_session();

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array($shot_power_frame, 100 + (30 * $shot_power), 0, 10, $this_robot->print_robot_name().' throws an '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(($shot_power * 10), 0, 0),
      'success' => array($shot_power_frame, (-20 - (40 * $shot_power)), 0, 10, $shot_power_text.' hit the target!'),
      'failure' => array($shot_power_frame, (-50 - (60 * $shot_power)), 0, -10, $this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array($shot_power_frame, (-20 - (40 * $shot_power)), 0, 10, $shot_power_text.' ignited the target!'),
      'failure' => array($shot_power_frame, (-50 - (60 * $shot_power)), 0, -10, $this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = ceil($this_ability->ability_damage * $shot_power);
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Decrease the target robot's defense stat
    $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->recovery_options_update($this_attachment_info['attachment_create'], true);

    // If the shot power has reached its limit, reset
    if ($shot_power >= 3){
      //unset($this_ability->counters['shot_power']);
      $defense_damage_amount = $this_attachment_info['attachment_defense'];
      $trigger_options = array('apply_modifiers' => false);
      $this_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount, true, $trigger_options);
    }
    // Otherwise, increase the user's defense by 10%
    else {
      // Trigger an defense boost if the ability was successful
      if ($this_robot->robot_status != 'disabled'
        && $this_robot->robot_defense < 9999
        && $this_ability->ability_results['this_result'] == 'success'){
        $defense_recovery_amount = ceil($this_robot->robot_defense * ($this_ability->ability_recovery2 / 100));
        $trigger_options = array('apply_type_modifiers' => false);
        $this_robot->trigger_recovery($this_robot, $this_ability, $defense_recovery_amount, true, $trigger_options);
        // Define the defense mod amount for this ability
        $this_attachment_info['attachment_defense'] += $this_ability->ability_results['this_amount']; //$defense_recovery_amount;
        if (($this_robot->robot_defense + $this_attachment_info['attachment_defense']) > 9999){ $this_attachment_info['attachment_defense'] = 9999 - $this_robot->robot_defense; }
        $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $this_robot->update_session();
      } elseif ($this_attachment_info['attachment_defense'] <= 0){
        unset($this_robot->robot_attachments[$this_attachment_token]);
        $this_robot->update_session();
      }
    }

    // Either way, update this ability's settings to prevent recovery
    $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->update_session();

    // Return true on success
    return true;

    },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      $this_ability->ability_target = 'select_target';
    } else {
      $this_ability->ability_target = $this_ability->ability_base_target;
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>