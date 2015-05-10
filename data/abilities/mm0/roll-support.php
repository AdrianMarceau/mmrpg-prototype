<?
// ROLL SUPPORT
$ability = array(
  'ability_name' => 'Roll Support',
  'ability_token' => 'roll-support',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Roll',
  'ability_description' => 'The user offers support to its own team by recoverying energy, attack, defense, and speed stats by {RECOVERY}% for all robots on the user\'s side of the field!',
  'ability_energy' => 16,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Increase this robot's energy stat
    if ($this_robot->robot_base < $this_robot->robot_base_energy){
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'frame' => 'taunt',
        'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().'&#39;s energy was restored!'),
        'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s energy was not affected&hellip;')
        ));
      $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);
    }
    // Increase this robot's attack stat
    if ($this_robot->robot_attack < MMRPG_SETTINGS_STATS_MAX){
      $this_ability->recovery_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'frame' => 'taunt',
        'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().'&#39;s weapons powered up!'),
        'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
        ));
      $attack_recovery_amount = ceil($this_robot->robot_base_attack * ($this_ability->ability_recovery / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount);
    }
    // Increase this robot's defense stat
    if ($this_robot->robot_defense < MMRPG_SETTINGS_STATS_MAX){
      $this_ability->recovery_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'frame' => 'taunt',
        'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().'&#39;s shields powered up!'),
        'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
        ));
      $defense_recovery_amount = ceil($this_robot->robot_base_defense * ($this_ability->ability_recovery / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $defense_recovery_amount);
    }
    // Increase this robot's speed stat
    if ($this_robot->robot_speed < MMRPG_SETTINGS_STATS_MAX){
      $this_ability->recovery_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'frame' => 'taunt',
        'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().'&#39;s mobility improved!'),
        'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
        ));
      $speed_recovery_amount = ceil($this_robot->robot_base_speed * ($this_ability->ability_recovery / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $speed_recovery_amount);
    }

    // Attach this ability to all robots on this player's side of the field
    $backup_robots_active = $this_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the this's benched robots, restoring energy one by one
      $this_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $this_robot->robot_id){ continue; }
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
        // Increase this robot's energy stat
        if ($temp_this_robot->robot_energy < $temp_this_robot->robot_base_energy){
          $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'frame' => 'taunt',
            'success' => array(0, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s energy was restored!'),
            'failure' => array(9, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s energy was not affected&hellip;')
            ));
          $energy_recovery_amount = ceil($temp_this_robot->robot_base_energy * ($this_ability->ability_recovery / 100));
          $temp_this_robot->trigger_recovery($temp_this_robot, $this_ability, $energy_recovery_amount);
        }
        // Increase this robot's attack stat
        if ($temp_this_robot->robot_attack < MMRPG_SETTINGS_STATS_MAX){
          $this_ability->recovery_options_update(array(
            'kind' => 'attack',
            'percent' => true,
            'frame' => 'taunt',
            'success' => array(0, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s weapons powered up!'),
            'failure' => array(9, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
            ));
          $attack_recovery_amount = ceil($temp_this_robot->robot_base_attack * ($this_ability->ability_recovery / 100));
          $temp_this_robot->trigger_recovery($temp_this_robot, $this_ability, $attack_recovery_amount);
        }
        // Increase this robot's defense stat
        if ($temp_this_robot->robot_defense < MMRPG_SETTINGS_STATS_MAX){
          $this_ability->recovery_options_update(array(
            'kind' => 'defense',
            'percent' => true,
            'frame' => 'taunt',
            'success' => array(0, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s shields powered up!'),
            'failure' => array(9, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s shields were not affected&hellip;')
            ));
          $defense_recovery_amount = ceil($temp_this_robot->robot_base_defense * ($this_ability->ability_recovery / 100));
          $temp_this_robot->trigger_recovery($temp_this_robot, $this_ability, $defense_recovery_amount);
        }
        // Increase this robot's speed stat
        if ($temp_this_robot->robot_speed < MMRPG_SETTINGS_STATS_MAX){
          $this_ability->recovery_options_update(array(
            'kind' => 'speed',
            'percent' => true,
            'frame' => 'taunt',
            'success' => array(0, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s mobility improved!'),
            'failure' => array(9, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
            ));
          $speed_recovery_amount = ceil($temp_this_robot->robot_base_speed * ($this_ability->ability_recovery / 100));
          $temp_this_robot->trigger_recovery($temp_this_robot, $this_ability, $speed_recovery_amount);
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