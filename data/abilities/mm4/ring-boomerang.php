<?
// RING BOOMERANG
$ability = array(
  'ability_name' => 'Ring Boomerang',
  'ability_token' => 'ring-boomerang',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/029',
  'ability_master' => 'ring-man',
  'ability_number' => 'DCN-029',
  'ability_description' => 'The user throws a large, boomerang-like ring at the target, striking twice with perfect accuracy and inflicting damage each time!',
  'ability_type' => 'cutter',
  'ability_type2' => 'space',
  'ability_energy' => 8,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(1, 150, 0, 10, $this_robot->print_robot_name().' throws '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -100, 0, 10, 'The '.$this_ability->print_ability_name().' cut into the target!'),
      'failure' => array(0, -150, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(1, -100, 0, 10, 'The '.$this_ability->print_ability_name().' was enjoyed by the target!'),
      'failure' => array(0, -150, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);


    // If this attack returns and strikes a second time (as long as first didn't KO)
    if ($this_ability->ability_results['this_result'] != 'failure'
      && $target_robot->robot_status != 'disabled'){

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(-10, 0, 0),
        'success' => array(4, 100, 0, 10, 'And there\'s the second hit!'),
        'failure' => array(3, 150, 0, -10, 'The second hit missed!')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'kickback' => array(-5, 0, 0),
        'frame' => 'taunt',
        'success' => array(4, 100, 0, 10, 'Oh no! Not again!'),
        'failure' => array(3, 150, 0, -10, 'Oh! The second hit missed!')
        ));
      //$energy_damage_amount = $energy_damage_amount + 1;
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

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