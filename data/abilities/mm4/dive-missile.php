<?
// DIVE MISSILE
$ability = array(
  'ability_name' => 'Dive Missile',
  'ability_token' => 'dive-missile',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/031',
  'ability_master' => 'dive-man',
  'ability_number' => 'DCN-031',
  'ability_description' => 'The user launches a slow but powerful heat-seeking missile at the target to deal massive damage! If the target is a Water Core robot, this ability does twice as much damage!',
  'ability_type' => 'missile',
  'ability_speed' => -1,
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_accuracy' => 99,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'kickback' => array(-10, 0, 0),
      'success' => array(0, 75, 0, 10, $this_robot->print_robot_name().' fires a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(30, 0, 0),
      'success' => array(1, 20, 0, 10, 'The '.$this_ability->print_ability_name().' collided with the target!'),
      'failure' => array(0, -75, 0, -10, 'The '.$this_ability->print_ability_name().' <em>just</em> missed the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(10, 0, 0),
      'success' => array(1, 20, 0, 10, 'The '.$this_ability->print_ability_name().'&#39;s energy was absorbed by the target!'),
      'failure' => array(0, -75, 0, -10, 'The '.$this_ability->print_ability_name().' was ignored by the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    if ($target_robot->robot_core == 'water' || $target_robot->robot_core2 == 'water'){ $energy_damage_amount = $energy_damage_amount * 2.0; }
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Return true on success
    return true;

  }
  );
?>