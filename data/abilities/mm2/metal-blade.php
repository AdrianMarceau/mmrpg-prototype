<?
// METAL BLADE
$ability = array(
  'ability_name' => 'Metal Blade',
  'ability_token' => 'metal-blade',
  'ability_game' => 'MM02',
  'ability_group' => 'MM02/Weapons/009',
  'ability_master' => 'metal-man',
  'ability_number' => 'DWN-009',
  'ability_description' => 'The user throws a sharp, disc-like blade in any direction that rips through the target for massive damage!',
  'ability_type' => 'cutter',
  'ability_energy' => 4,
  'ability_damage' => 18,
  'ability_accuracy' => 86,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(0, 65, 0, 10, $this_robot->print_robot_name().' throws a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(5, 0, 0),
      'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_ability_name().' rips through the target!'),
      'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' spun past the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_ability_name().' rips through target!'),
      'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' spun past the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Return true on success
    return true;

  }
  );
?>