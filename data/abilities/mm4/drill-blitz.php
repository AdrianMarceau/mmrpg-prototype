<?
// DRILL BLITZ
$ability = array(
  'ability_name' => 'Drill Blitz',
  'ability_token' => 'drill-blitz',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/027',
  'ability_master' => 'drill-man',
  'ability_number' => 'DCN-027',
  'ability_description' => 'The user generates a series of sharp drills that rush toward the target to deal massive damage, ignoring both resistance and immunity!',
  'ability_type' => 'earth',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_accuracy' => 90,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Attach three whirlwind attachments to the robot
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
      'class' => 'ability',
      'ability_token' => $this_ability->ability_token,
      'ability_frame' => 0,
      'ability_frame_animate' => array(0),
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 0)
      );
    $this_robot->robot_attachments[$this_attachment_token.'_1'] = $this_attachment_info;
    $this_robot->robot_attachments[$this_attachment_token.'_2'] = $this_attachment_info;
    $this_robot->robot_attachments[$this_attachment_token.'_1']['ability_frame_offset'] = array('x' => 75, 'y' => -25, 'z' => 10);
    $this_robot->robot_attachments[$this_attachment_token.'_2']['ability_frame_offset'] = array('x' => 95, 'y' => 25, 'z' => 10);
    $this_robot->update_session();

    // Backup the target robot's earth weakness, if it has one
    $temp_target_immunities_backup = array();
    if ($target_robot->has_immunity($this_ability->ability_type)){
      $temp_target_immunities_backup = $target_robot->robot_immunities;
      unset($target_robot->robot_immunities[array_search($this_ability->ability_type, $target_robot->robot_immunities)]);
      $target_robot->update_session();
    }

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 115, -25, 10, 'The '.$this_ability->print_ability_name().' summoned a triad of drills!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Update the two whirlwind's animation frames
    $this_robot->robot_attachments[$this_attachment_token.'_1']['ability_frame'] = 0;
    $this_robot->robot_attachments[$this_attachment_token.'_2']['ability_frame'] = 0;
    $this_robot->update_session();

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(5, 0, 0),
      'success' => array(1, -80, -25, 10, 'A drill hit!'),
      'failure' => array(1, -100, -25, -10, 'One of the drills missed!'),
      'options' => array(
        'apply_resistance_modifiers' => false,
        'apply_immunity_modifiers' => false,
        )
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -80, -25, 10, 'A drill was absorbed!'),
      'failure' => array(1, -100, -25, -10, 'One of the drills missed!'),
      'options' => array(
        'apply_resistance_modifiers' => false,
        'apply_immunity_modifiers' => false,
        )
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Ensure the target has not been disabled
    if ($target_robot->robot_status != 'disabled'){

      // Define the success/failure text variables
      $success_text = '';
      $failure_text = '';

      // Adjust damage/recovery text based on results
      if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another drill hit!'; }
      if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another drill missed!'; }

      // Remove the second extra whirlwind attached to the robot
      if (isset($this_robot->robot_attachments[$this_attachment_token.'_2'])){
        unset($this_robot->robot_attachments[$this_attachment_token.'_2']);
        $this_robot->update_session();
      }

      // Update the remaining whirlwind's animation frame
      $this_robot->robot_attachments[$this_attachment_token.'_1']['ability_frame'] = 0;
      $this_robot->update_session();

      // Attempt to trigger damage to the target robot again
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(10, 0, 0),
        'success' => array(1, -40, 25, 10, $success_text),
        'failure' => array(1, -60, 25, -10, $failure_text)
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(1, -40, 25, 10, $success_text),
        'failure' => array(1, -60, 25, -10, $failure_text)
        ));
      $target_robot->trigger_damage($this_robot, $this_ability,  $energy_damage_amount);

      // Ensure the target has not been disabled
      if ($target_robot->robot_status != 'disabled'){

        // Adjust damage/recovery text based on results again
        if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another drill hit!'; }
        elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A third drill hit!'; }
        if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another drill missed!'; }
        elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A third drill missed!'; }

        // Remove the first extra whirlwind
        if (isset($this_robot->robot_attachments[$this_attachment_token.'_1'])){
          unset($this_robot->robot_attachments[$this_attachment_token.'_1']);
          $this_robot->update_session();
        }

        // Attempt to trigger damage to the target robot a third time
        $this_ability->damage_options_update(array(
          'kind' => 'energy',
          'kickback' => array(15, 0, 0),
          'success' => array(1, -70, -25, 10, $success_text),
          'failure' => array(1, -90, -25, -10, $failure_text)
          ));
        $this_ability->recovery_options_update(array(
          'kind' => 'energy',
          'frame' => 'taunt',
          'kickback' => array(0, 0, 0),
          'success' => array(1, -70, -25, 10, $success_text),
          'failure' => array(1, -90, -25, -10, $failure_text)
          ));
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

      }

    }

    // Remove the second whirlwind
    if (isset($this_robot->robot_attachments[$this_attachment_token.'_2'])){
      unset($this_robot->robot_attachments[$this_attachment_token.'_2']);
      $this_robot->update_session();
    }

    // Remove the third whirlwind
    if (isset($this_robot->robot_attachments[$this_attachment_token.'_1'])){
      unset($this_robot->robot_attachments[$this_attachment_token.'_1']);
      $this_robot->update_session();
    }

    // If this robot has an immunity removed, re-add it and update session
    if (!empty($temp_target_immunities_backup)){
      $target_robot->robot_immunities = $temp_target_immunities_backup;
      $target_robot->update_session();
    }

    // Return true on success
    return true;

  }
  );
?>