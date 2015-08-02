<?
// OIL SLIDER
$ability = array(
  'ability_name' => 'Oil Slider',
  'ability_token' => 'oil-slider',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/00B',
  'ability_master' => 'oil-man',
  'ability_number' => 'DLN-00B',
  'ability_description' => 'The user quickly slides toward the target on a wave of crude oil, inflicting damage and raising the user\'s speed by {RECOVERY2}%!',
  'ability_type' => 'earth',
  'ability_type2' => 'impact',
  'ability_energy' => 8,
  'ability_speed' => 2,
  'ability_damage' => 24,
  'ability_recovery2' => 10,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 92,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'slide',
      'kickback' => array(150, 0, 0),
      'success' => array(0, 15, -10, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(15, 0, 0),
      'success' => array(1, -65, -10, 10, 'The '.$this_ability->print_ability_name().' crashes into the target!'),
      'failure' => array(0, -85, -5, -10, 'The '.$this_ability->print_ability_name().' continued past the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(1, -35, -10, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(1, -65, -5, -10, 'The '.$this_ability->print_ability_name().' continued past the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Randomly trigger a speed boost if the ability was successful
    if ($this_robot->robot_speed < MMRPG_SETTINGS_STATS_MAX
      && $this_ability->ability_results['this_result'] != 'failure'
      && $this_ability->ability_results['this_amount'] > 0){
      $this_ability->recovery_options_update(array(
        'kind' => 'speed',
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(2, -15, 10, -10, $this_robot->print_robot_name().'&#39;s mobility improved!'),
        'failure' => array(2, 0, 0, -9999, '')
        ));
      $this_ability->damage_options_update(array(
        'kind' => 'speed',
        'frame' => 'damage',
        'kickback' => array(0, 0, 0),
        'success' => array(2, -15, 10, -10, $this_robot->print_robot_name().'&#39;s mobility worsened!'),
        'failure' => array(2, 0, 0, -9999, '')
        ));
      $speed_damage_amount = ceil($this_robot->robot_speed * ($this_ability->ability_recovery2 / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $speed_damage_amount);
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