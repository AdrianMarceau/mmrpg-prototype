<?
// TIME ARROW
$ability = array(
  'ability_name' => 'Time Arrow',
  'ability_token' => 'time-arrow',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/00A',
  'ability_master' => 'time-man',
  'ability_number' => 'DLN-00A',
  'ability_description' => 'The user directs a mysterious arrow at the target, dealing temporal damage and cutting speed by {DAMAGE2}%!',
  'ability_type' => 'time',
  'ability_energy' => 4,
  'ability_damage' => 12,
  'ability_damage2' => 10,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(1, 125, 0, 10, $this_robot->print_robot_name().' throws a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -125, 0, 10, 'The '.$this_ability->print_ability_name().' sliced into the target!'),
      'failure' => array(1, -150, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -60, 0, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(1, -90, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Randomly trigger a speed break if the ability was successful
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_speed > 0
      && $this_ability->ability_results['this_result'] != 'failure'
      && $this_ability->ability_results['this_amount'] > 0){
      // Decrease the target robot's speed stat
      $this_ability->damage_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'frame' => 'defend',
        'success' => array(5, 5, 70, -10, $target_robot->print_robot_name().'&#39;s mobility was slowed!'),
        'failure' => array(5, 5, 70, -10, $target_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(0, 0, -9999, 0,  $target_robot->print_robot_name().'&#39;s mobility was hastened!'),
        'failure' => array(0, 0, -9999, 0, $target_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
        ));
      $speed_damage_amount = ceil($target_robot->robot_speed * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);
    }

    // Return true on success
    return true;


    },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      $this_ability->ability_target = 'select_target';
    } else {
      $this_ability->ability_target = $this_ability->ability_base_target;
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>