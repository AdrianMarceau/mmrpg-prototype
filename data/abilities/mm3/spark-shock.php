<?
// SPARK SHOCK
$ability = array(
  'ability_name' => 'Spark Shock',
  'ability_token' => 'spark-shock',
  'ability_game' => 'MM03',
  'ability_description' => 'The user looses a large ball of electricity at the target, inflicting damage and occasionally lowering its defense by {DAMAGE2}%!',
  'ability_type' => 'electric',
  'ability_energy' => 4,
  'ability_damage' => 16,
  'ability_damage2' => 10,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(0, 110, 0, 10, $this_robot->print_name().' throws a '.$this_ability->print_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -90, 0, 10, 'The '.$this_ability->print_name().' zapped the target!'),
      'failure' => array(1, -100, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -45, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
      'failure' => array(1, -100, 0, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Randomly trigger a defense break if the ability was successful
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_speed > 0
      && $this_ability->ability_results['this_result'] != 'failure' && $this_ability->ability_results['this_amount'] > 0
      && $this_battle->critical_chance(50)){
      // Decrease the target robot's speed stat
      $this_ability->damage_options_update(array(
        'kind' => 'defense',
        'frame' => 'defend',
        'percent' => true,
        'modifiers' => false,
        'kickback' => array(10, 0, 0),
        'success' => array(2, 0, -6, 10, $target_robot->print_name().'&#39;s shields were damaged!'),
        'failure' => array(2, 0, -6, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'defense',
        'frame' => 'taunt',
        'percent' => true,
        'modifiers' => false,
        'kickback' => array(0, 0, 0),
        'success' => array(2, 0, -6, 10, $target_robot->print_name().'&#39;s shields improved!'),
        'failure' => array(2, 0, -6, -9999, '')
        ));
      $defense_damage_amount = ceil($target_robot->robot_defense * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount, true, array('apply_modifiers' => false));
    }

    // Return true on success
    return true;


    }
  );
?>