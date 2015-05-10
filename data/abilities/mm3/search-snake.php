<?
// SEARCH SNAKE
$ability = array(
  'ability_name' => 'Search Snake',
  'ability_token' => 'search-snake',
  'ability_game' => 'MM03',
  'ability_group' => 'MM03/Weapons/022',
  'ability_master' => 'snake-man',
  'ability_number' => 'DWN-022',
  'ability_description' => 'The user fires a remote-controlled snake robot that seeks out any target to deal damage with perfect accuracy!',
  'ability_type' => 'nature',
  'ability_energy' => 4,
  'ability_speed' => 2,
  'ability_damage' => 14,
  'ability_accuracy' => 100,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 75, 0, 10, $this_robot->print_robot_name().' fires a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -30, 0, 10, 'The '.$this_ability->print_ability_name().' collided with the target!'),
      'failure' => array(1, -75, 0, -10, 'The '.$this_ability->print_ability_name().' slithered past the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -30, 0, 10, 'The '.$this_ability->print_ability_name().' repaired the target!'),
      'failure' => array(1, -75, 0, -10, 'The '.$this_ability->print_ability_name().' slithered past the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Return true on success
    return true;

  }
  );
?>