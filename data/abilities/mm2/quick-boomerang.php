<?
// QUICK BOOMERANG
$ability = array(
  'ability_name' => 'Quick Boomerang',
  'ability_token' => 'quick-boomerang',
  'ability_game' => 'MM02',
  'ability_group' => 'MM02/Weapons/012',
  'ability_master' => 'quick-man',
  'ability_number' => 'DWN-012',
  'ability_description' => 'The user throws a boomerang-like blade at blinding speed toward the target, striking up to four times at increasing strength!',
  'ability_type' => 'swift',
  'ability_type2' => 'cutter',
  'ability_energy' => 8,
  'ability_speed' => 4,
  'ability_damage' => 6,
  'ability_accuracy' => 80,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(1, 100, 0, 10, $this_robot->print_robot_name().' throws a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(5, 0, 0),
      'success' => array(0, 5, 0, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array(0, -50, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(0, 0, 5, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array(0, -50, 5, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);


    // If this attack returns and strikes a second time (random chance)
    if ($this_ability->ability_results['this_result'] != 'failure'
      && $target_robot->robot_status != 'disabled'
      && $this_battle->critical_chance($this_ability->ability_accuracy)){

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(10, 0, 0),
        'success' => array(1, -40, 10, 10, 'Oh! It hit again!'),
        'failure' => array(1, -90, 10, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'kickback' => array(0, 0, 0),
        'frame' => 'taunt',
        'success' => array(1, -40, 10, 10, 'Oh! It hit again!'),
        'failure' => array(1, -90, 10, -10, '')
        ));
      $energy_damage_amount = $energy_damage_amount + 1;
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

      // If this attack returns and strikes a third time (random chance)
      if ($this_ability->ability_results['this_result'] != 'failure'
        && $target_robot->robot_energy != 'disabled'
        && $this_battle->critical_chance($this_ability->ability_accuracy)){

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
          'kind' => 'energy',
          'kickback' => array(15, 0, 0),
          'success' => array(2, -10, 15, -10, 'Wow! A third hit?!?'),
          'failure' => array(2, 60, 15, -10, '')
          ));
        $this_ability->recovery_options_update(array(
          'kind' => 'energy',
          'frame' => 'taunt',
          'kickback' => array(0, 0, 0),
          'success' => array(2, 10, 15, -10, 'Wow! A third hit?!?'),
          'failure' => array(2, 60, 15, -10, '')
          ));
        $energy_damage_amount = $energy_damage_amount + 1;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // If this attack returns and strikes a fourth time (random chance)
        if ($this_ability->ability_results['this_result'] != 'failure'
          && $target_robot->robot_status != 'disabled'
          && $this_battle->critical_chance($this_ability->ability_accuracy)){

          // Inflict damage on the opposing robot
          $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(3, 50, 20, -10, 'Nice! One more time!'),
            'failure' => array(3, 90, 20, -10, '')
            ));
          $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(3, 50, 20, -10, 'Nice! One more time!'),
            'failure' => array(3, 90, 20, -10, '')
            ));
          $energy_damage_amount = $energy_damage_amount + 1;
          $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }

      }

    }

    // Return true on success
    return true;

  }
  );
?>