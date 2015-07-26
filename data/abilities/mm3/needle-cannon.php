<?
// NEEDLE CANNON
$ability = array(
  'ability_name' => 'Needle Cannon',
  'ability_token' => 'needle-cannon',
  'ability_game' => 'MM03',
  'ability_group' => 'MM03/Weapons/017',
  'ability_master' => 'needle-man',
  'ability_number' => 'DWN-017',
  'ability_description' => 'The user fires a volley of three needle-like projectiles that pierce the target\'s defenses and inflict damage!',
  'ability_type' => 'cutter',
  'ability_type2' => 'missile',
  'ability_energy' => 8,
  'ability_damage' => 16,
  'ability_damage_percent' => true,
  'ability_accuracy' => 88,
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

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 115, -25, 10, $this_ability->print_ability_name().' fires a volley of needles!')
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
      'success' => array(1, -80, -25, 10, 'A needle hit!'),
      'failure' => array(1, -100, -25, -10, 'One of the needles missed!')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -80, -25, 10, 'A needle hit!'),
      'failure' => array(1, -100, -25, -10, 'One of the needles missed!')
      ));
    $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
    $trigger_options = array('apply_modifiers' => true, 'apply_type_modifiers' => true, 'apply_core_modifiers' => true, 'apply_field_modifiers' => true, 'apply_stat_modifiers' => false);
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, $trigger_options);

    // Ensure the target has not been disabled
    if ($target_robot->robot_status != 'disabled'){

      // Define the success/failure text variables
      $success_text = '';
      $failure_text = '';

      // Adjust damage/recovery text based on results
      if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another needle hit!'; }
      if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another needle missed!'; }

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
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, $trigger_options);

      // Ensure the target has not been disabled
      if ($target_robot->robot_status != 'disabled'){

        // Adjust damage/recovery text based on results again
        if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another needle hit!'; }
        elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A third needle hit!'; }
        if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another needle missed!'; }
        elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A third needle missed!'; }

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
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, $trigger_options);

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