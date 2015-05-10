<?
// RAIN FLUSH
$ability = array(
  'ability_name' => 'Rain Flush',
  'ability_token' => 'rain-flush',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/026',
  'ability_master' => 'toad-man',
  'ability_number' => 'DCN-026',
  'ability_image_sheets' => 2,
  'ability_description' => 'The user releases an acid rain-inducing capsule into the air that deals {DAMAGE}% damage to all other robots on the field! This ability\'s effects can even pierce through shields!',
  'ability_type' => 'water',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
      'ability_image' => $this_ability->ability_token.'-2',
      'ability_frame' => 0,
      'ability_frame_animate' => array(0, 1),
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 999),
      'ability_frame_classes' => 'sprite_fullscreen '
      );

    // Count the number of active robots on the target's side of the field
    $target_robots_active = $target_player->counters['robots_active'];

    // Change the image to the full-screen rain effect
    $this_ability->ability_image = 'rain-flush';
    $this_ability->ability_frame_classes = '';
    $this_ability->update_session();

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(1, 10, 100, 10, $this_robot->print_robot_name().' releases the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability, array('prevent_stats_text' => true));

    // Change the image to the full-screen rain effect
    $this_ability->ability_image = 'rain-flush-2';
    $this_ability->ability_frame_classes = 'sprite_fullscreen ';
    $this_ability->update_session();

    // Ensure this robot stays in the summon position for the duration of the attack
    $this_robot->robot_frame = 'summon';
    $this_robot->update_session();


    // -- DAMAGE TARGETS -- //

    // Attach this ability attachment to the robot using it
    //$this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    //$this_robot->update_session();

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'modifiers' => true,
      'kickback' => array(5, 0, 0),
      'success' => array(0, -5, 0, 99, 'The '.$this_ability->print_ability_name().' melts through the target!'),
      'failure' => array(0, -5, 0, 99,'The '. $this_ability->print_ability_name().' had no effect on '.$target_robot->print_robot_name().'&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'modifiers' => true,
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(0, -5, 0, 9, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(0, -5, 0, 9, 'The '.$this_ability->print_ability_name().' had no effect on '.$target_robot->print_robot_name().'&hellip;')
      ));
    $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
    $trigger_options = array('apply_modifiers' => true, 'apply_type_modifiers' => true, 'apply_core_modifiers' => true, 'apply_field_modifiers' => true, 'apply_stat_modifiers' => false);
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);

    // Loop through the target's benched robots, inflicting half base damage to each
    $backup_target_robots_active = $target_player->values['robots_active'];
    foreach ($backup_target_robots_active AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){ continue; }
      $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
      $this_ability->ability_results_reset();
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'modifiers' => true,
        'kickback' => array(5, 0, 0),
        'success' => array(($key % 2), -5, 0, 99, 'The '.$this_ability->print_ability_name().' melts through the target!'),
        'failure' => array(($key % 2), -5, 0, 99,'The '. $this_ability->print_ability_name().' had no effect on '.$temp_target_robot->print_robot_name().'&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'modifiers' => true,
        'frame' => 'taunt',
        'kickback' => array(5, 0, 0),
        'success' => array(($key % 2), -5, 0, 9, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
        'failure' => array(($key % 2), -5, 0, 9, 'The '.$this_ability->print_ability_name().' had no effect on '.$temp_target_robot->print_robot_name().'&hellip;')
        ));
      $energy_damage_amount = ceil($temp_target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
      $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
    }


    // -- DAMAGE SELF/TEAM -- //

    // Attach this ability attachment to the robot using it
    //$this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    //$this_robot->update_session();

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'modifiers' => true,
      'kickback' => array(5, 0, 0),
      'success' => array(0, -5, 0, 99, 'The '.$this_ability->print_ability_name().' melts through the target!'),
      'failure' => array(0, -5, 0, 99,'The '. $this_ability->print_ability_name().' had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'modifiers' => true,
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(0, -5, 0, 9, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(0, -5, 0, 9, 'The '.$this_ability->print_ability_name().' had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ));
    $energy_damage_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_damage / 100));
    $trigger_options = array('apply_modifiers' => true, 'apply_type_modifiers' => true, 'apply_core_modifiers' => true, 'apply_field_modifiers' => true, 'apply_stat_modifiers' => false);
    //if ($this_robot->robot_token != 'toad-man'){ $this_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options); }

    // Loop through this player's benched robots, inflicting half base damage to each
    $backup_this_robots_active = $this_player->values['robots_active'];
    foreach ($backup_this_robots_active AS $key => $info){
      if ($info['robot_id'] == $this_robot->robot_id){ continue; }
      $temp_team_robot = new mmrpg_robot($this_battle, $this_player, $info);
      $this_ability->ability_results_reset();
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'modifiers' => true,
        'kickback' => array(5, 0, 0),
        'success' => array(($key % 2), -5, 0, 99, 'The '.$this_ability->print_ability_name().' melts through the target!'),
        'failure' => array(($key % 2), -5, 0, 99,'The '. $this_ability->print_ability_name().' had no effect on '.$temp_team_robot->print_robot_name().'&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'modifiers' => true,
        'frame' => 'taunt',
        'kickback' => array(5, 0, 0),
        'success' => array(($key % 2), -5, 0, 9, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
        'failure' => array(($key % 2), -5, 0, 9, 'The '.$this_ability->print_ability_name().' had no effect on '.$temp_team_robot->print_robot_name().'&hellip;')
        ));
      $energy_damage_amount = ceil($temp_team_robot->robot_base_energy * ($this_ability->ability_damage / 100));
      $temp_team_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
    }


    // -- DISABLE FALLEN -- //

    // Trigger the disabled event on the targets now if necessary
    if ($target_robot->robot_status == 'disabled'){ $target_robot->trigger_disabled($this_robot, $this_ability); }
    else { $target_robot->robot_frame = 'base'; }
    $target_robot->update_session();
    foreach ($backup_target_robots_active AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){ continue; }
      $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
      if ($temp_target_robot->robot_energy <= 0 || $temp_target_robot->robot_status == 'disabled'){ $temp_target_robot->trigger_disabled($this_robot, $this_ability); }
      else { $temp_target_robot->robot_frame = 'base'; }
      $temp_target_robot->update_session();
    }


    // Trigger the disabled event on the targets now if necessary
    if ($this_robot->robot_status == 'disabled'){ $this_robot->trigger_disabled($target_robot, $this_ability); }
    else { $this_robot->robot_frame = 'base'; }
    $this_robot->update_session();
    foreach ($backup_this_robots_active AS $key => $info){
      if ($info['robot_id'] == $this_robot->robot_id){ continue; }
      $temp_team_robot = new mmrpg_robot($this_battle, $this_player, $info);
      if ($temp_team_robot->robot_energy <= 0 || $temp_team_robot->robot_status == 'disabled'){ $temp_team_robot->trigger_disabled($this_robot, $this_ability); }
      else { $temp_team_robot->robot_frame = 'base'; }
      $temp_team_robot->update_session();
    }


    // Change the image to the full-screen rain effect
    $this_ability->ability_image = 'rain-flush';
    $this_ability->ability_frame_classes = '';
    $this_ability->update_session();

    // Remove this ability attachment to the robot using it
    //unset($this_robot->robot_attachments[$this_attachment_token]);
    //$this_robot->update_session();

    // Return true on success
    return true;


    }
  );
?>