<?
// SUPER THROW
$ability = array(
  'ability_name' => 'Super Throw',
  'ability_token' => 'super-throw',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/004',
  'ability_master' => 'guts-man',
  'ability_number' => 'DLN-004',
  'ability_image_sheets' => 0,
  'ability_description' => 'The user waits for an opening then grabs hold of the target and throws them across the field, dealing damage and forcing the opponent to switch out for another robot!',
  'ability_type' => 'impact',
  'ability_energy' => 4,
  'ability_speed' => -3,
  'ability_damage' => 14,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$target_robot->robot_id;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
    	'attachment_duration' => 1,
      'attachment_switch_disabled' => true,
      'ability_frame' => 9,
      'ability_frame_animate' => array(9),
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 0),
    	'attachment_destroy' => false,
      );

    // Ensure this robot is not prevented from attacking by speed break
    if ($this_robot->robot_speed > 0){

      // Attach this ability attachment to the robot using it
      $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $target_robot->update_session();

      // Target the opposing robot
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(0, 0, 0, 10, $this_robot->print_robot_name().' prepares for the '.$this_ability->print_ability_name().'!')
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Inflict damage on the opposing robot
      $this_robot->robot_frame = 'throw';
      $this_robot->update_session();
      $target_robot->robot_position = 'bench';
      $target_robot->update_session();
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'frame' => 'damage',
        'kickback' => array(0, 0, 0),
        'success' => array(0, -65, 0, 10, $target_robot->print_robot_name().' is thrown to the bench!'),
        'failure' => array(0, -85, 0, -10, $target_robot->print_robot_name().' is thrown to the bench!')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(0, -65, 0, 10, $target_robot->print_robot_name().' is thrown to the bench!'),
        'failure' => array(0, -85, 0, -10, $target_robot->print_robot_name().' is thrown to the bench!')
        ));
      $energy_damage_amount = $this_ability->ability_damage;
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
      $this_robot->robot_frame = 'throw';
      $this_robot->update_session();

      // Clear the action queue to allow the player to pick a new ability
      $this_battle->actions_empty();

      // Automatically append an action if on autopilot
      if ($target_player->player_autopilot == true){

        // If the target robot was not destroyed by the hit, append a switch
        //if ($target_robot->robot_energy > 0 && $target_robot->robot_status != 'disabled'){
        if (true){

          // Default the switch target to the existing robot
          $switch_target_token = $target_robot->robot_id.'_'.$target_robot->robot_token;
          $switch_target_token_backup = $switch_target_token;

          // Randomly select a target for the opponent that isn't the same as the current
          if ($target_player->counters['robots_active'] > 1){
            $switch_robots_active = $target_player->values['robots_active'];
            shuffle($switch_robots_active);
            foreach ($switch_robots_active AS $key => $robot){
              $new_switch_target_token = $robot['robot_id'].'_'.$robot['robot_token'];
              if ($robot['robot_energy'] > 0 && $new_switch_target_token != $switch_target_token_backup){
                $switch_target_token = $new_switch_target_token;
                break;
              }
            }
          }

          // Trigger a switch on the opponent immediately
          $this_battle->actions_prepend(
            $target_player,
            $target_robot,
            $this_player,
            $this_robot,
            'switch',
            $switch_target_token
            );

        }

      }
      // Otherwise if the player only has one robot anyway
      elseif ($target_player->counters['robots_active'] == 1){

        // Remove this ability attachment from the robot using it
        unset($target_robot->robot_attachments[$this_attachment_token]);
        $target_robot->update_session();

        // Pull the robot back into play automatically
        $target_robot->robot_position = 'active';
        $target_robot->robot_frame = 'defend';
        $target_robot->update_session();

      }
      // Otherwise, clear the action queue and continue
      else {

        // Do nothing?

      }

      // Trigger the disabled function if necessary
      if ($target_robot->robot_energy == 0 || $target_robot->robot_status == 'disabled'){
        $target_robot->trigger_disabled($this_robot, $this_ability);
      }

    }
    // Otherwise, if the robot is in speed break
    else {

      // Target the opposing robot
      $temp_pronoun = in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm', 'spash-woman')) ? 'her' : 'him';
      $this_ability->target_options_update(array(
        'frame' => 'throw',
        'success' => array(0, 0, 0, 10, $this_ability->print_ability_name().' attempts to throw '.$target_robot->print_robot_name().' to the bench&hellip;<br />But speed break prevents '.$temp_pronoun.' from getting a lock on the target!')
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

    }

    // Return true on success
    return true;

  }
  );
?>