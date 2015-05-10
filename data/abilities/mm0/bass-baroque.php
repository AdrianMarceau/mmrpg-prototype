<?
// BASS BAROQUE
$ability = array(
  'ability_name' => 'Bass Baroque',
  'ability_token' => 'bass-baroque',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Bass',
  'ability_description' => 'The user swiftly summons a dark aura around the target that inflicts massive damage with a {RECOVERY2}% chance of critical hit!',
  'ability_type' => '',
  'ability_energy' => 6,
  'ability_speed' => 6,
  'ability_damage' => 50,
  'ability_recovery2' => 50,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -10, 0, -10, $this_robot->print_robot_name().' summons the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(60, 0, 0),
      'rates' => array('auto', 'auto', $this_ability->ability_recovery2),
      'success' => array(0, -5, 0, -10, 'The '.$this_ability->print_ability_name().' rips into the target!'),
      'failure' => array(0, -50, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(20, 0, 0),
      'rates' => array('auto', 'auto', $this_ability->ability_recovery2),
      'success' => array(0, -5, 0, -10, 'The '.$this_ability->print_ability_name().' was enjoyed by the target!'),
      'failure' => array(0, -50, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Return true on success
    return true;

  }
  );
?>