<?
// HYPER BOMB
$ability = array(
  'ability_name' => 'Hyper Bomb',
  'ability_token' => 'hyper-bomb',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/006',
  'ability_master' => 'bomb-man',
  'ability_number' => 'DLN-006',
  'ability_description' => 'The user throws a large bomb at the target that explodes to deal massive damage, occasionally hitting benched robots with the blast at half power!',
  'ability_type' => 'explode',
  'ability_energy' => 4,
  'ability_damage' => 16,
  'ability_accuracy' => 86,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // If this ability is attached, remove it
    $this_attachment_backup = false;
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    if (isset($this_robot->robot_attachments[$this_attachment_token])){
      $this_attachment_backup = $this_robot->robot_attachments[$this_attachment_token];
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();
    }

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'kickback' => array(0, 0, 0),
      'success' => array(0, 85, 35, 10, $this_robot->print_robot_name().' thows a '.$this_ability->print_ability_name().'!'),
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'frame' => 'damage',
      'kickback' => array(10, 5, 0),
      'success' => array(2, 30, 0, 10, 'The '.$this_ability->print_ability_name().' exploded on contact!'),
      'failure' => array(1, -65, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(2, 30, 0, 10, 'The '.$this_ability->print_ability_name().' exploded on contact!'),
      'failure' => array(1, -65, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

    // Randomly trigger a bench damage if the ability was successful
    $backup_robots_active = $target_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($this_ability->ability_results['this_result'] != 'failure'){

      // Loop through the target's benched robots, inflicting 10% base damage to each
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $target_robot->robot_id){ continue; }
        if (!$this_battle->critical_chance(ceil((9 - $info['robot_key']) * 10))){ break; }
        $this_ability->ability_results_reset();
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        // Update the ability options text
        $this_ability->damage_options_update(array(
          'success' => array(2, -20, -5, -5, $temp_target_robot->print_robot_name().' was damaged by the blast!'),
          'failure' => array(3, 0, 0, -9999, '')
          ));
        $this_ability->recovery_options_update(array(
          'success' => array(2, -20, -5, -5, $temp_target_robot->print_robot_name().' was refreshed by the blast!'),
          'failure' => array(3, 0, 0, -9999, '')
          ));
        $energy_damage_amount = ceil($this_ability->ability_damage * 0.20); //ceil($this_ability->ability_damage / $backup_robots_active_count);
        $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
        if ($this_ability->ability_results['this_result'] == 'failure'){ break; }
      }

    }

    // Trigger the disabled event on the targets now if necessary
    if ($target_robot->robot_energy < 1 || $target_robot->robot_status == 'disabled'){ $target_robot->trigger_disabled($this_robot, $this_ability); }
    foreach ($backup_robots_active AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){ continue; }
      $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
      if ($temp_target_robot->robot_energy <= 0 || $temp_target_robot->robot_status == 'disabled'){ $temp_target_robot->trigger_disabled($this_robot, $this_ability); }
    }

    // If there was a removed attachment, put it back
    if (!empty($this_attachment_backup)){
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_backup;
      $this_robot->update_session();
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

    },
  'ability_frame' => 1,
  'ability_frame_offset' => array('x' => -55, 'y' => 1, 'z' => -10)
  );
?>