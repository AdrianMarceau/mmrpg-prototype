<?
// SHADOW BLADE
$ability = array(
  'ability_name' => 'Shadow Blade',
  'ability_token' => 'shadow-blade',
  'ability_game' => 'MM03',
  'ability_group' => 'MM03/Weapons/024',
  'ability_master' => 'shadow-man',
  'ability_number' => 'DWN-024',
  'ability_description' => 'The user swiftly throws a dark ninja star at the target to inflict damage and lower one of the target\'s overpowered stats by {DAMAGE2}%!',
  'ability_type' => 'shadow',
  'ability_type2' => 'cutter',
  'ability_energy' => 8,
  'ability_damage' => 22,
  'ability_damage2' => 20,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(0, 120, 0, 10, $this_robot->print_robot_name().' throws the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_ability_name().' rips through the target!'),
      'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' spun past the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_ability_name().' rips through target!'),
      'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' spun past the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Inflict a random break on critical chance
    if ($target_robot->robot_status != 'disabled'
      && $this_ability->ability_results['this_result'] != 'failure'
      && $this_ability->ability_results['this_amount']){
      // Define the break options for this ability
      $temp_break_options_index = array('attack' => 'weapons', 'defense' => 'shields', 'speed' => 'mobility');
      $temp_break_options = array('attack', 'defense', 'speed');
      $this_break_option = array('kind' => '', 'amount' => 0); //$temp_break_options[array_rand($temp_break_options)];
      if ($target_robot->robot_attack > $this_break_option['amount']){ $this_break_option = array('kind' => 'attack', 'amount' => $target_robot->robot_attack); }
      if ($target_robot->robot_defense > $this_break_option['amount']){ $this_break_option = array('kind' => 'defense', 'amount' => $target_robot->robot_defense); }
      if ($target_robot->robot_speed > $this_break_option['amount']){ $this_break_option = array('kind' => 'speed', 'amount' => $target_robot->robot_speed); }
      $this_break_option = $this_break_option['kind'];
      // Decrease the target robot's random stat
      $this_ability->damage_options_update(array(
        'kind' => $this_break_option,
        'type' => '',
        'type2' => '',
        'frame' => 'defend',
        'percent' => true,
        'kickback' => array(0, 0, 0),
        'success' => array(9, 65, 0, -9999, $target_robot->print_robot_name().'&#39;s '.$temp_break_options_index[$this_break_option].' '.($this_break_option == 'speed' ? 'was' : 'were').' damaged by the blade!'),
        'failure' => array(9, 85, 0, -9999, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => $this_break_option,
        'type' => '',
        'type2' => '',
        'frame' => 'taunt',
        'percent' => true,
        'kickback' => array(0, 0, 0),
        'success' => array(9, 65, 0, -9999, $target_robot->print_robot_name().'&#39;s '.$temp_break_options_index[$this_break_option].' '.($this_break_option == 'speed' ? 'was' : 'were').' improved by the blade!'),
        'failure' => array(9, 85, 0, -9999, '')
        ));
      if ($this_break_option == 'attack'){ $temp_damage_amount = ceil($target_robot->robot_attack * 0.10); }
      elseif ($this_break_option == 'defense'){ $temp_damage_amount = ceil($target_robot->robot_defense * 0.10); }
      elseif ($this_break_option == 'speed'){ $temp_damage_amount = ceil($target_robot->robot_speed * 0.10); }
      $target_robot->trigger_damage($this_robot, $this_ability, $temp_damage_amount);
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