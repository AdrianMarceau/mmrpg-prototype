<?
// BUSTER CHARGE
$ability = array(
  'ability_name' => 'Buster Charge',
  'ability_token' => 'buster-charge',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Weapons/T0',
  'ability_image_sheets' => 0,
  'ability_description' => 'The user takes a defensive stance and charges themselves to restore depleted weapon energy by up to {RECOVERY}%.',
  'ability_energy' => 0,
  'ability_recovery' => 100,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(0, 0, 0, -10, $this_robot->print_robot_name().' starts charging weapon energy&hellip;')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Inflict recovery on the opposing robot
    $this_ability->recovery_options_update(array(
      'kind' => 'weapons',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(0, 0, 0, -10, 'The '.$this_ability->print_ability_name().' restored depleted power!'),
      'failure' => array(0, 0, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $weapons_recovery_amount = ceil($this_robot->robot_base_weapons * ($this_ability->ability_recovery / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $weapons_recovery_amount);

    // Return true on success
    return true;

    }
  );
?>