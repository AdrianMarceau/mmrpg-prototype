<?
// ITEM : SUPER CAPSULE
$ability = array(
  'ability_name' => 'Super Capsule',
  'ability_token' => 'item-super-capsule',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Items/Super',
  'ability_class' => 'item',
  'ability_type' => '',
  'ability_type2' => 'shield',
  'ability_description' => 'A large weapon capsule that that boosts the attack, defense, and speed stat of one robot on the user\'s side of the field by {RECOVERY2}% each.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_recovery' => 60,
  'ability_recovery_percent' => true,
  'ability_recovery2' => 20,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'select_this',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 40, -2, 99,
        $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
        $target_robot->print_robot_name().' is given the '.$this_ability->print_ability_name().'!'
        )
      ));
    $target_robot->trigger_target($target_robot, $this_ability);

    // Increase this robot's life attack stat
    $this_ability->recovery_options_update(array(
      'kind' => 'attack',
      'percent' => true,
      'modifiers' => false,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapons powered up!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
      ));
    $attack_recovery_amount = ceil($target_robot->robot_base_attack * ($this_ability->ability_recovery2 / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $attack_recovery_amount);

    // Increase this robot's life defense stat
    $this_ability->recovery_options_update(array(
      'kind' => 'defense',
      'percent' => true,
      'modifiers' => false,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s shields powered up!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
      ));
    $defense_recovery_amount = ceil($target_robot->robot_base_defense * ($this_ability->ability_recovery2 / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $defense_recovery_amount);

    // Increase this robot's life speed stat
    $this_ability->recovery_options_update(array(
      'kind' => 'speed',
      'percent' => true,
      'modifiers' => false,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s mobility improved!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
      ));
    $speed_recovery_amount = ceil($target_robot->robot_base_speed * ($this_ability->ability_recovery2 / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $speed_recovery_amount);

    // Return true on success
    return true;

  }
  );
?>