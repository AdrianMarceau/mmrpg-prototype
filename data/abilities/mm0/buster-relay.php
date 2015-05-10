<?
// BUSTER RELAY
$ability = array(
  'ability_name' => 'Buster Relay',
  'ability_token' => 'buster-relay',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Weapons/T0',
  'ability_image_sheets' => 0,
  'ability_description' => 'The user relays a buster charge they are currently holding to another robot on their team, transferring any stat changes or elemental boosts to the new robot.',
  'ability_energy' => 0,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(0, 0, 0, 10, $this_robot->print_robot_name().' does something&hellip;')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Return true on success
    return true;

    },
  'ability_function_onload' => function($objects){


  }
  );
?>