<?
// ATTACK MODE
$ability = array(
  'ability_name' => 'Attack Mode',
  'ability_token' => 'attack-mode',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/Modes',
  'ability_description' => 'The user lowers its defense and speed by {DAMAGE}% to greatly raise attack and improve weapons by {RECOVERY}%!',
  'ability_energy' => 6,
  'ability_recovery' => 90,
  'ability_recovery_percent' => true,
  'ability_damage' => 45,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().' enters '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Decrease this robot's defense stat
    $this_ability->damage_options_update(array(
      'kind' => 'defense',
      'frame' => 'defend',
      'percent' => true,
      'success' => array(1, -2, 0, -10,  $this_robot->print_robot_name().'&#39;s shields powered down&hellip;'),
      'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
      ));
    $defense_damage_amount = ceil($this_robot->robot_defense * ($this_ability->ability_damage / 100));
    $this_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);

    // Decrease this robot's speed stat
    $this_ability->damage_options_update(array(
      'kind' => 'speed',
      'frame' => 'defend',
      'percent' => true,
      'success' => array(2, -4, 0, -10,  $this_robot->print_robot_name().'&#39;s mobility slowed&hellip;'),
      'failure' => array(9, -4, 0, -10, $this_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
      ));
    $speed_damage_amount = ceil($this_robot->robot_speed * ($this_ability->ability_damage / 100));
    $this_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);

    // Increase this robot's attack stat
    $this_ability->recovery_options_update(array(
      'kind' => 'attack',
      'frame' => 'taunt',
      'percent' => true,
      'success' => array(0, -6, 0, -10,  $this_robot->print_robot_name().'&#39;s weapons powered up&hellip;'),
      'failure' => array(9, -6, 0, -10, $this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
      ));
    $attack_recovery_amount = ceil($this_robot->robot_attack * ($this_ability->ability_recovery / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount);

    // Return true on success
    return true;

  }
  );
?>