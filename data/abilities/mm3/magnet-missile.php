<?
// MAGNET MISSILE
$ability = array(
  'ability_name' => 'Magnet Missile',
  'ability_token' => 'magnet-missile',
  'ability_game' => 'MM03',
  'ability_group' => 'MM03/Weapons/018',
  'ability_master' => 'magnet-man',
  'ability_number' => 'DWN-018',
  'ability_description' => 'The user fires a large magnet-shaped missile at any target robot for guaranteed damage!',
  'ability_type' => 'missile',
  'ability_type2' => 'electric',
  'ability_energy' => 8,
  'ability_damage' => 24,
  'ability_accuracy' => 100,
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
      'kickback' => array(60, 0, 0),
      'success' => array(0, 50, 0, 10, 'The '.$this_ability->print_ability_name().' collided with the target!'),
      'failure' => array(0, -75, 0, -10, 'The '.$this_ability->print_ability_name().' somehow missed the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(10, 0, 0),
      'success' => array(0, -35, 0, 10, 'The '.$this_ability->print_ability_name().'&#39;s energy was absorbed by the target!'),
      'failure' => array(0, -75, 0, -10, 'The '.$this_ability->print_ability_name().' was ignored by the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Return true on success
    return true;

  }
  );
?>