<?
// REPAIR MODE
$ability = array(
  'ability_name' => 'Repair Mode',
  'ability_token' => 'repair-mode',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/Modes',
  'ability_description' => 'The user lowers its attack, defense, and speed by {DAMAGE}% to repair damaged systems and recover up to {RECOVERY}% energy!',
  'ability_energy' => 6,
  'ability_recovery' => 90,
  'ability_recovery_percent' => true,
  'ability_damage' => 30,
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

    // Decrease this robot's attack stat
    $this_ability->damage_options_update(array(
      'kind' => 'attack',
      'frame' => 'defend',
      'percent' => true,
      'success' => array(1, -2, 0, -10,  $this_robot->print_robot_name().'&#39;s weapons powered down&hellip;'),
      'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
      ));
    $attack_damage_amount = ceil($this_robot->robot_attack * ($this_ability->ability_damage / 100));
    $this_robot->trigger_damage($this_robot, $this_ability, $attack_damage_amount);

    // Decrease this robot's defense stat
    $this_ability->damage_options_update(array(
      'kind' => 'defense',
      'frame' => 'defend',
      'percent' => true,
      'success' => array(2, -4, 0, -10,  $this_robot->print_robot_name().'&#39;s shields powered down&hellip;'),
      'failure' => array(9, -4, 0, -10, $this_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
      ));
    $defense_damage_amount = ceil($this_robot->robot_defense * ($this_ability->ability_damage / 100));
    $this_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);

    // Decrease this robot's speed stat
    $this_ability->damage_options_update(array(
      'kind' => 'speed',
      'frame' => 'defend',
      'percent' => true,
      'success' => array(3, -6, 0, -10,  $this_robot->print_robot_name().'&#39;s mobility slowed&hellip;'),
      'failure' => array(9, -6, 0, -10, $this_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
      ));
    $speed_damage_amount = ceil($this_robot->robot_speed * ($this_ability->ability_damage / 100));
    $this_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);

    // Increase this robot's energy stat
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'percent' => true,
      'success' => array(0, -8, 0, -10,  $this_robot->print_robot_name().' recovered energy!'),
      'failure' => array(9, -8, 0, -10, $this_robot->print_robot_name().'&#39;s energy was not affected&hellip;')
      ));
    $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);

    // Return true on success
    return true;

  }
  );
?>