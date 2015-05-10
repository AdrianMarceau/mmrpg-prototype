<?
// PINS 'N NEEDLES
$ability = array(
  'ability_name' => 'Pins \'n Needles',
  'ability_token' => 'pins-needles',
  'ability_game' => 'MM03',
  'ability_class' => 'mecha',
  'ability_description' => 'The user fires a volley of needles into the air that rains down on random targets to inflict damage.',
  'ability_type' => 'cutter',
  'ability_energy' => 0,
  'ability_damage' => 10,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, -50, 70, 10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!', 2),
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $temp_offset = 0;
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(0, -5, 0),
      'success' => array(2, $temp_offset, 20, 10, 'Falling needles strike the target!'),
      'failure' => array(2, ($temp_offset - 50), 20, -10, 'The falling needles missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(2, $temp_offset, 20, 10, 'The falling needles honed the target!'),
      'failure' => array(2, ($temp_offset - 50), 20, -10, 'The falling needles missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
    
    // Randomly trigger a bench damage if the ability was successful
    $backup_robots_active = $target_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    $last_ability_result = $this_ability->ability_results['this_result'];
    if (true){ //$this_ability->ability_results['this_result'] != 'failure'
        
      // Loop through the target's benched robots, inflicting les and less damage to each
      $target_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $target_robot->robot_id){ continue; }
        if (!$this_battle->critical_chance(ceil($this_ability->ability_accuracy))){ continue; }
        $this_ability->ability_results_reset();
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        // Update the ability options text
        $temp_frame = 2;
        $temp_offset = 0;
        $this_ability->damage_options_update(array(
          'kickback' => array(-10, -4, 0),
          'success' => array($temp_frame, $temp_offset, 20, 10, 'The falling needles strike '.($last_ability_result == 'success' ? 'another' : 'the').' target!'),
          'failure' => array($temp_frame, ($temp_offset - 50), 20, 10, '')
          ));
        $this_ability->recovery_options_update(array(
          'kickback' => array(-10, -4, 0),
          'success' => array($temp_frame, $temp_offset, 20, 10, 'The falling needles hone '.($last_ability_result == 'success' ? 'another' : 'the').' target!'),
          'failure' => array($temp_frame, ($temp_offset - 50), 20, 10, '')
          ));
        //$energy_damage_amount = ceil($this_ability->ability_damage / ($key + 2));
        $energy_damage_amount = ceil($this_ability->ability_damage / ($target_robot->robot_key + 2));
        $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
        $last_ability_result = $this_ability->ability_results['this_result'];
        $target_key++;
      }
      
    }
    
    // Trigger the disabled event on the targets now if necessary
    if ($target_robot->robot_status == 'disabled'){ $target_robot->trigger_disabled($this_robot, $this_ability); }
    foreach ($backup_robots_active AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){ continue; }
      $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
      if ($temp_target_robot->robot_energy <= 0 || $temp_target_robot->robot_status == 'disabled'){ $temp_target_robot->trigger_disabled($this_robot, $this_ability); }
    }
    
    // Return true on success
    return true;
        
  }
  );
?>