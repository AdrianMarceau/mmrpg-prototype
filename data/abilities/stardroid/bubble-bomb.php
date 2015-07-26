<?
// BUBBLE BOMB
$ability = array(
  'ability_name' => 'Bubble Bomb',
  'ability_token' => 'bubble-bomb',
  'ability_game' => 'MM30',
  'ability_group' => 'MMAZ/T2/Weapons/MM30',
  'ability_master' => 'venus',
  'ability_number' => 'SRN-003',
  'ability_description' => 'The user throws a large bubble at the target that explodes on contact, causing massive damage and occasionally lowering its attack by {DAMAGE2}%!',
  'ability_type' => 'explode',
  'ability_type2' => 'water',
  'ability_energy' => 8,
  'ability_damage' => 20,
  'ability_damage2' => 10,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 90,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(0, 85, 35, 10, $this_robot->print_robot_name().' thows a '.$this_ability->print_ability_name().'!'),
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 0, 0),
      'success' => array(2, -10, -10, 10, 'The '.$this_ability->print_ability_name().' burst on contact!'),
      'failure' => array(1, -65, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'frame' => 'taunt',
      'kickback' => array(10, 0, 0),
      'success' => array(2, -10, -10, 10, 'The '.$this_ability->print_ability_name().' burst on contact!'),
      'failure' => array(1, -65, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Randomly inflict a speed break on critical chance 75%
    if ($target_robot->robot_status != 'disabled'
      && $this_ability->ability_results['this_result'] != 'failure' && $this_ability->ability_results['this_amount'] > 0
      && $this_battle->critical_chance(50)){
      // Decrease the target robot's speed stat
      $this_ability->damage_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'frame' => 'defend',
        'kickback' => array(0, 0, 0),
        'success' => array(1, -10, -10, -10, $target_robot->print_robot_name().'&#39;s weapons were damaged!'),
        'failure' => array(1, -65, -10, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(1, -10, -10, -10, $target_robot->print_robot_name().'&#39;s weapons improved!'),
        'failure' => array(1, -65, -10, -9999, '')
        ));
      $attack_damage_amount = ceil($target_robot->robot_attack * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $attack_damage_amount);
    }

    // Return true on success
    return true;

  }
  );
?>