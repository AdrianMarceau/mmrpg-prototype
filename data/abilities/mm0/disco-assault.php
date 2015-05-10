<?
// DISCO ASSAULT
$ability = array(
  'ability_name' => 'Disco Assault',
  'ability_token' => 'disco-assault',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Disco',
  'ability_description' => 'The user assaults the opposing team by damaging energy, attack, defense, and speed stats by {DAMAGE}% for all robots on the target\'s side of the field!',
  'ability_energy' => 16,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Decrease the target robot's attack stat
    if ($target_robot->robot_energy > MMRPG_SETTINGS_STATS_MIN){
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'kickback' => array(10, 0, 0),
        'success' => array(0, -2, 0, -10, $target_robot->print_robot_name().'&#39;s life energy was damaged!'),
        'failure' => array(0, -2, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
        ));
      $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    }
    // Decrease the target robot's attack stat
    if ($target_robot->robot_attack > MMRPG_SETTINGS_STATS_MIN){
      $this_ability->damage_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'kickback' => array(10, 0, 0),
        'success' => array(0, -2, 0, -10, $target_robot->print_robot_name().'&#39;s weapons were damaged!'),
        'failure' => array(9, -2, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
        ));
      $attack_damage_amount = ceil($target_robot->robot_attack * ($this_ability->ability_damage / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $attack_damage_amount);
    }
    // Decrease the target robot's defense stat
    if ($target_robot->robot_defense > MMRPG_SETTINGS_STATS_MIN){
      $this_ability->damage_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'kickback' => array(10, 0, 0),
        'success' => array(0, -2, 0, -10, $target_robot->print_robot_name().'&#39;s shields were damaged!'),
        'failure' => array(9, -2, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
        ));
      $defense_damage_amount = ceil($target_robot->robot_defense * ($this_ability->ability_damage / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);
    }
    // Decrease the target robot's speed stat
    if ($target_robot->robot_speed > MMRPG_SETTINGS_STATS_MIN){
      $this_ability->damage_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'kickback' => array(10, 0, 0),
        'success' => array(0, -2, 0, -10, $target_robot->print_robot_name().'&#39;s mobility was damaged!'),
        'failure' => array(9, -2, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
        ));
      $speed_damage_amount = ceil($target_robot->robot_speed * ($this_ability->ability_damage / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);
    }

    // Attach this ability to all robots on the target player's side of the field
    $backup_robots_active = $target_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the this's benched robots, restoring energy one by one
      $this_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $target_robot->robot_id){ continue; }
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        // Increase this robot's energy stat
        if ($temp_target_robot->robot_energy > MMRPG_SETTINGS_STATS_MIN){
          $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -2, 0, -10, $temp_target_robot->print_robot_name().'&#39;s systems were damaged!'),
            'failure' => array(0, -2, 0, -10, 'It had no effect on '.$temp_target_robot->print_robot_name().'&hellip;')
            ));
          $energy_damage_amount = ceil($temp_target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
          $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
        }
        // Decrease the target robot's attack stat
        if ($temp_target_robot->robot_attack > MMRPG_SETTINGS_STATS_MIN){
          $this_ability->damage_options_update(array(
            'kind' => 'attack',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -2, 0, -10, $temp_target_robot->print_robot_name().'&#39;s weapons were damaged!'),
            'failure' => array(9, -2, 0, -10, 'It had no effect on '.$temp_target_robot->print_robot_name().'&hellip;')
            ));
          $attack_damage_amount = ceil($temp_target_robot->robot_attack * ($this_ability->ability_damage / 100));
          $temp_target_robot->trigger_damage($this_robot, $this_ability, $attack_damage_amount);
        }
        // Decrease the target robot's defense stat
        if ($temp_target_robot->robot_defense > MMRPG_SETTINGS_STATS_MIN){
          $this_ability->damage_options_update(array(
            'kind' => 'defense',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -2, 0, -10, $temp_target_robot->print_robot_name().'&#39;s shields were damaged!'),
            'failure' => array(9, -2, 0, -10, 'It had no effect on '.$temp_target_robot->print_robot_name().'&hellip;')
            ));
          $defense_damage_amount = ceil($temp_target_robot->robot_defense * ($this_ability->ability_damage / 100));
          $temp_target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);
        }
        // Decrease the target robot's speed stat
        if ($temp_target_robot->robot_speed > MMRPG_SETTINGS_STATS_MIN){
          $this_ability->damage_options_update(array(
            'kind' => 'speed',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -2, 0, -10, $temp_target_robot->print_robot_name().'&#39;s mobility was damaged!'),
            'failure' => array(9, -2, 0, -10, 'It had no effect on '.$temp_target_robot->print_robot_name().'&hellip;')
            ));
          $speed_damage_amount = ceil($temp_target_robot->robot_speed * ($this_ability->ability_damage / 100));
          $temp_target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);
        }
        // Increment the key counter
        $this_key++;
      }
    }

    // Return true on success
    return true;

  }
  );
?>