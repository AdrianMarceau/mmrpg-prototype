<?
// DARK DRAIN
$ability = array(
  'ability_name' => 'Dark Drain',
  'ability_token' => 'dark-drain',
  'ability_game' => 'MMEXE',
  'ability_class' => 'mecha',
  'ability_type' => 'empty',
  'ability_description' => 'The user manipulates dark energy to drain one of the target\'s stats by {DAMAGE2}% while recovering its own by the same amount!',
  'ability_energy' => 0,
  'ability_damage2' => 25,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define the highest stat for the target
    $this_energy_percent = ceil(($target_robot->robot_energy / $target_robot->robot_base_energy) * 100);
    $this_energy_value = $this_energy_percent * 4;
    $temp_stat_values = array('energy' => $this_energy_value, 'attack' => $target_robot->robot_attack, 'defense' => $target_robot->robot_defense, 'speed' => $target_robot->robot_speed);
    asort($temp_stat_values, SORT_NUMERIC);
    end($temp_stat_values);
    $temp_stat_drain = key($temp_stat_values);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 75, 0, 10, $this_ability->print_ability_name().' steals '.$temp_stat_drain.' from the target!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // If an energy boost was requested by the above variables
    if ($temp_stat_drain == 'energy'){

      // Decrease the target robot's energy stat
      if ($target_robot->robot_energy > 0){
        $this_ability->damage_options_update(array(
          'kind' => 'energy',
          'percent' => true,
          'modifiers' => false,
          'frame' => 'damage',
          'success' => array(0, -2, 0, -10, $target_robot->print_robot_name().'&#39;s energy was drained!'),
          'failure' => array(9, -2, 0, -10, $target_robot->print_robot_name().'&#39;s energy was not affected&hellip;')
          ));
        $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_damage2 / 100));
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
        // Increase this robot's energy stat
        if ($this_robot->robot_energy < $this_robot->robot_base_energy){
          $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => false,
            'frame' => 'taunt',
            'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().'&#39;s energy was restored!'),
            'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s energy was not affected&hellip;')
            ));
          $energy_recovery_amount = $energy_damage_amount;
          $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);
        }
      }

    }
    // Else if an attack boost was requested by the above variables
    elseif ($temp_stat_drain == 'attack'){

      // Increase this robot's attack stat
      $this_ability->damage_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapons broke down!'),
        'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
        ));
      $attack_damage_amount = ceil($target_robot->robot_base_attack * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $attack_damage_amount);
      // Increase this robot's attack stat
      $this_ability->recovery_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'success' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s weapons powered up!'),
        'failure' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
        ));
      $attack_recovery_amount = $attack_damage_amount;
      $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount);

    }
    // Else if an defense boost was requested by the above variables
    elseif ($temp_stat_drain == 'defense'){

      // Increase this robot's defense stat
      $this_ability->damage_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s shields broke down!'),
        'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
        ));
      $defense_damage_amount = ceil($target_robot->robot_base_defense * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);
      // Increase this robot's defense stat
      $this_ability->recovery_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'success' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s shields powered up!'),
        'failure' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
        ));
      $defense_recovery_amount = $defense_damage_amount;
      $this_robot->trigger_recovery($this_robot, $this_ability, $defense_recovery_amount);

    }
    // Else if an speed boost was requested by the above variables
    elseif ($temp_stat_drain == 'speed'){

      // Increase this robot's speed stat
      $this_ability->damage_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'success' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s mobility degraded!'),
        'failure' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
        ));
      $speed_damage_amount = ceil($target_robot->robot_base_speed * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);
      // Increase this robot's speed stat
      $this_ability->recovery_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'success' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s mobility improved!'),
        'failure' => array(9, 0, 0, -9999, $this_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
        ));
      $speed_recovery_amount = $speed_damage_amount;
      $this_robot->trigger_recovery($this_robot, $this_ability, $speed_recovery_amount);

    }

    // Return true on success
    return true;

  }
  );
?>