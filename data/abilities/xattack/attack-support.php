<?
// ATTACK SUPPORT
$ability = array(
  'ability_name' => 'Attack Support',
  'ability_token' => 'attack-support',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/Attack2',
  'ability_description' => 'The user triggers weapon optimizations for all robots on their side of the field to boost attack by {RECOVERY}%!',
  'ability_energy' => 16,
  'ability_speed' => -1,
  'ability_recovery' => 15,
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

    // Increase this robot's attack stat
    $this_ability->recovery_options_update(array(
      'kind' => 'attack',
      'percent' => true,
      'frame' => 'taunt',
      'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().'&#39;s weapons powered up!'),
      'failure' => array(9, -2, 0, -10, $this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
      ));
    $attack_recovery_amount = ceil($this_robot->robot_base_attack * ($this_ability->ability_recovery / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount);

    // Attach this ability to all robots on this player's side of the field
    $backup_robots_active = $this_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through this player's benched robots, restoring attack one by one
      $this_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $this_robot->robot_id){ continue; }
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
        // Increase this robot's attack stat
        $this_ability->recovery_options_update(array(
          'kind' => 'attack',
          'percent' => true,
          'frame' => 'taunt',
          'success' => array(0, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s weapons powered up!'),
          'failure' => array(9, -2, 0, -10, $temp_this_robot->print_robot_name().'&#39;s weapons were not affected&hellip;')
          ));
        $attack_recovery_amount = ceil($temp_this_robot->robot_base_attack * ($this_ability->ability_recovery / 100));
        $temp_this_robot->trigger_recovery($temp_this_robot, $this_ability, $attack_recovery_amount);
        $this_key++;
      }
    }

    // Return true on success
    return true;

  }
  );
?>