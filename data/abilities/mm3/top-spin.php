<?
// TOP SPIN
$ability = array(
  'ability_name' => 'Top Spin',
  'ability_token' => 'top-spin',
  'ability_game' => 'MM03',
  'ability_group' => 'MM03/Weapons/021',
  'ability_master' => 'top-man',
  'ability_number' => 'DWN-021',
  'ability_description' => 'The user launches a large, top-shaped weapon at the target that spins around frantically and continues dealing damage until it misses!',
  'ability_type' => 'swift',
  'ability_energy' => 4,
  'ability_damage' => 3,
  'ability_accuracy' => 70,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(0, 100, 0, 10, $this_robot->print_robot_name().' throws a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(mt_rand(5, 10), 0, 0),
      'success' => array(1, -20, 0, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array(1, -80, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -20, 0, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array(1, -80, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Define a few random success messages to use
    $temp_success_messages = array('Oh! It hit again!', 'Wow! Another hit?!', 'Nice! One more time!', 'It just keeps spinning!', 'Oh wow! Another hit!', 'Awesome, another hit!');

    // If this attack returns and strikes a second time (random chance)
    $temp_hit_counter = 0;
    while ($this_ability->ability_results['this_result'] != 'failure'
      && $target_robot->robot_status != 'disabled'){

      // Define the offset variables
      $temp_frame = $temp_hit_counter == 0 || $temp_hit_counter % 2 == 0 ? 1 : 0;
      $temp_offset = 40 - ($temp_hit_counter * 10);
      $temp_offset = $temp_frame == 0 ? $temp_offset * -1 : ceil($temp_offset * 0.75);
      $temp_accuracy = $this_ability->ability_base_accuracy - $temp_hit_counter;
      if ($temp_accuracy < 1){ $temp_accuracy = 1; }
      $this_ability->ability_accuracy = $temp_accuracy;
      $this_ability->update_session();

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(mt_rand(0, 20), 0, 0),
        'success' => array($temp_frame, $temp_offset, mt_rand(0, 10), 10, $temp_success_messages[array_rand($temp_success_messages)]),
        'failure' => array($temp_frame, ($temp_offset* 2), 0, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'kickback' => array(0, 0, 0),
        'frame' => 'taunt',
        'success' => array($temp_frame, $temp_offset, mt_rand(0, 10), 10, $temp_success_messages[array_rand($temp_success_messages)]),
        'failure' => array($temp_frame, ($temp_offset * 2), 0, -10, '')
        ));
      $energy_damage_amount = ceil($energy_damage_amount * 1.10);
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

      // Increment the hit counter
      $temp_hit_counter++;

    }

    // Reset the accuracy back to base values
    $this_ability->ability_accuracy = $this_ability->ability_base_accuracy;
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