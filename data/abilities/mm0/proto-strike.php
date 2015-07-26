<?
// PROTO STRIKE
$ability = array(
  'ability_name' => 'Proto Strike',
  'ability_token' => 'proto-strike',
  'ability_game' => 'MM085',
  'ability_group' => 'MM00/Weapons/Proto',
  'ability_description' => 'The user unleashes a giant, lightspeed energy blast at the target to inflict massive damage with a {RECOVERY2}% chance of critical hit!',
  'ability_type' => '',
  'ability_energy' => 6,
  'ability_speed' => 6,
  'ability_damage' => 60,
  'ability_recovery2' => 40,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'kickback' => array(-10, 0, 0),
      'success' => array(1, 120, -10, -10, $this_robot->print_robot_name().' releases a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(60, 0, 0),
      'rates' => array('auto', 'auto', $this_ability->ability_recovery2),
      'success' => array(0, -120, -10, 10, 'The '.$this_ability->print_ability_name().' crashes into the target!'),
      'failure' => array(0, -140, -10, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(20, 0, 0),
      'rates' => array('auto', 'auto', $this_ability->ability_recovery2),
      'success' => array(0, -120, -10, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(0, -140, -10, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

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