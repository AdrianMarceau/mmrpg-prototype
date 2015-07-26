<?
// TIME SLOW
$ability = array(
  'ability_name' => 'Time Slow',
  'ability_token' => 'time-slow',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/00A',
  'ability_master' => 'time-man',
  'ability_number' => 'DLN-00A',
  'ability_description' => 'The user charges on the first turn to build power then releases a wave of temporal energy on the second to slow down opposing targets for up to seven turns!',
  'ability_type' => 'time',
  'ability_energy' => 8,
  'ability_damage' => 20,
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
      'ability_frame' => 0,
      'ability_frame_animate' => array(1, 0),
      'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
      );

    // Define the charge required flag based on existing attachments of this ability
    $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;
    // If this robot is holding a Charge Module, bypass changing and set to false
    if ($this_robot->robot_item == 'item-charge-module'){ $this_charge_required = false; }

    // If the ability flag was not set, this ability begins charging
    if ($this_charge_required){

      // Target this robot's self
      $this_ability->target_options_update(array(
        'frame' => 'defend',
        'success' => array(1, -10, 0, -10, $this_robot->print_robot_name().' charges the '.$this_ability->print_ability_name().'&hellip;')
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Attach this ability attachment to the robot using it
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();

    }
    // Else if the ability flag was set, the ability is released at the target
    else {

      // Remove this ability attachment to the robot using it
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();

      // Update this ability's target options and trigger
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'kickback' => array(0, 0, 0),
        'success' => array(5, 5, 70, 10, $this_robot->print_robot_name().' releases the '.$this_ability->print_ability_name().'!'),
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Define this ability's attachment token
      $this_attachment_info = array(
      	'class' => 'ability',
      	'ability_token' => $this_ability->ability_token,
      	'attachment_duration' => 0,
        'attachment_speed' => 0,
      	'attachment_weaknesses' => array('swift'),
      	'attachment_create' => array(
          'kind' => 'speed',
          'trigger' => 'damage',
          'percent' => true,
          'frame' => 'defend',
          'rates' => array(100, 0, 0),
          'success' => array(5, 5, 70, -10, $target_robot->print_robot_name().'&#39;s mobility was slowed!'),
          'failure' => array(5, 5, 70, -10, $target_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
          ),
      	'attachment_destroy' => array(
          'kind' => 'speed',
          'trigger' => 'recovery',
          'type' => '',
          'type2' => '',
          'percent' => true,
          'modifiers' => false,
          'frame' => 'taunt',
          'rates' => array(100, 0, 0),
          'success' => array(0, 0, -9999, 0,  $target_robot->print_robot_name().'&#39;s mobility returned to normal!'),
          'failure' => array(0, 0, -9999, 0, $target_robot->print_robot_name().'&#39;s mobility was not affected&hellip;')
          ),
        'ability_frame' => 5,
        'ability_frame_animate' => array(5, 4, 3, 2),
        'ability_frame_offset' => array('x' => 5, 'y' => 70, 'z' => -10)
        );

      // Define the speed mod amount for this ability
      $this_attachment_info['attachment_duration'] += 1;
      $this_attachment_info['attachment_speed'] = ceil($target_robot->robot_speed * ($this_ability->ability_damage / 100));
      if (($target_robot->robot_speed - $this_attachment_info['attachment_speed']) < 1){ $this_attachment_info['attachment_speed'] = $target_robot->robot_speed - 1; }

      // Check to ensure the attachment hasn't already been created
      if (empty($target_robot->robot_attachments[$this_attachment_token])){

        // Decrease this robot's speed stat if the attachment does not already exist
        $this_ability->damage_options_update($this_attachment_info['attachment_create']);
        $this_ability->recovery_options_update($this_attachment_info['attachment_create']);
        $this_ability->update_session();
        $speed_damage_amount = $this_attachment_info['attachment_speed']; //ceil($this_robot->robot_speed * ($this_ability->ability_damage / 100));
        $target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);

        // Attach this ability attachment to the robot using it
        $this_ability->damage_options_update($this_attachment_info['attachment_destroy']);
        $this_ability->recovery_options_update($this_attachment_info['attachment_destroy']);
        $this_attachment_info['attachment_speed'] = $this_ability->ability_results['this_amount'];
        $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $target_robot->update_session();

      }
      // Otherwise, if the attachment already exists
      else {

        // Simply reset the timer on this ability
        $target_robot->robot_attachments[$this_attachment_token]['attachment_duration'] = $this_attachment_info['attachment_duration'] + 1;
        $target_robot->update_session();

      }

      // Randomly trigger a bench damage if the ability was successful
      $backup_robots_active = $target_player->values['robots_active'];
      $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
      if (true){

        // Loop through the target's benched robots, inflicting les and less damage to each
        $target_key = 0;
        foreach ($backup_robots_active AS $key => $info){
          if ($info['robot_id'] == $target_robot->robot_id){ continue; }
          $this_ability->ability_results_reset();
          $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);

          // Define the speed mod amount for this ability
          $this_attachment_info['attachment_duration'] += 1;
          $this_attachment_info['attachment_speed'] = ceil($temp_target_robot->robot_speed * ($this_ability->ability_damage / 100));
          if (($temp_target_robot->robot_speed - $this_attachment_info['attachment_speed']) < 1){ $this_attachment_info['attachment_speed'] = $temp_target_robot->robot_speed - 1; }

          // Check to ensure the attachment hasn't already been created
          if (empty($temp_target_robot->robot_attachments[$this_attachment_token])){

            // Decrease this robot's speed stat if the attachment does not already exist
            $this_ability->damage_options_update($this_attachment_info['attachment_create']);
            $this_ability->recovery_options_update($this_attachment_info['attachment_create']);
            $this_ability->update_session();
            $speed_damage_amount = $this_attachment_info['attachment_speed']; //ceil($this_robot->robot_speed * ($this_ability->ability_damage / 100));
            $temp_target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);

            // Attach this ability attachment to the robot using it
            $this_ability->damage_options_update($this_attachment_info['attachment_destroy']);
            $this_ability->recovery_options_update($this_attachment_info['attachment_destroy']);
            $this_attachment_info['attachment_speed'] = $this_ability->ability_results['this_amount'];
            $temp_target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $temp_target_robot->update_session();

          }
          // Otherwise, if the attachment already exists
          else {

            // Simply reset the timer on this ability
            $temp_target_robot->robot_attachments[$this_attachment_token]['attachment_duration'] = $this_attachment_info['attachment_duration'] + 1;
            $temp_target_robot->update_session();

          }

          // Increment the target key
          $target_key++;
        }

      }

      // Either way, update this ability's settings to prevent recovery
      $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
      $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
      $this_ability->update_session();

    }

    // Return true on success
    return true;

    },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;

    // Define the charge required flag based on existing attachments of this ability
    $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

    // If the ability flag had already been set, reduce the weapon energy to zero
    if (!$this_charge_required){ $this_ability->ability_energy = 0; }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->ability_energy = $this_ability->ability_base_energy; }

    // If this robot is holding a Charge Module, bypass changing but reduce the power of the ability
    if ($this_robot->robot_item == 'item-charge-module'){
      $this_charge_required = false;
      $temp_item_info = mmrpg_ability::get_index_info($this_robot->robot_item);
      $this_ability->ability_damage = ceil($this_ability->ability_base_damage * ($temp_item_info['ability_damage2'] / $temp_item_info['ability_recovery2']));
    } else {
      $this_ability->ability_damage = $this_ability->ability_base_damage;
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>