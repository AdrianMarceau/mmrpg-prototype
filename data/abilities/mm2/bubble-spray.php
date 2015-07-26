<?
// BUBBLE SPRAY
$ability = array(
  'ability_name' => 'Bubble Spray',
  'ability_token' => 'bubble-spray',
  'ability_game' => 'MM02',
  'ability_group' => 'MM02/Weapons/011',
  'ability_master' => 'bubble-man',
  'ability_number' => 'DWN-011',
  'ability_description' => 'The user sprays the target with a thick layer of bubbles, surrounding them in a wet foam and doubling the damage they receive from Electric type attacks!  Robots affected by this ability appear to take reduced damage from Flame type attacks, however,  so choose your targets with care.',
  'ability_type' => 'water',
  'ability_energy' => 4,
  'ability_damage' => 16,
  'ability_accuracy' => 98,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$target_robot->robot_id;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
    	'attachment_duration' => 9,
      'attachment_damage_input_booster_electric' => 2.0,
      'attachment_damage_input_breaker_flame' => 0.5,
    	'attachment_weaknesses' => array('electric'),
    	'attachment_create' => array(
        'trigger' => 'special',
        'kind' => '',
        'percent' => true,
        'frame' => 'defend',
        'rates' => array(100, 0, 0),
        'success' => array(9, -10, -5, -10, $target_robot->print_robot_name().' found itself in a puddle of foamy bubbles!<br /> '.$target_robot->print_robot_name().'&#39;s <span class="ability_name ability_type ability_type_electric">Electric</span> resistance was compromised&hellip;'),
        'failure' => array(9, -10, -5, -10, $target_robot->print_robot_name().' found itself in a puddle of foamy bubbles!<br /> '.$target_robot->print_robot_name().'&#39;s <span class="ability_name ability_type ability_type_electric">Electric</span> resistance was compromised&hellip;')
        ),
    	'attachment_destroy' => array(
        'trigger' => 'special',
        'kind' => '',
        'type' => '',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'taunt',
        'rates' => array(100, 0, 0),
        'success' => array(9, 0, -9999, 0,  'The foam surrounding '.$target_robot->print_robot_name().' faded away&hellip;<br /> '.$target_robot->print_robot_name().' is no longer vulnerable!'),
        'failure' => array(9, 0, -9999, 0, 'The foam surrounding '.$target_robot->print_robot_name().' faded away&hellip;<br /> '.$target_robot->print_robot_name().' is no longer vulnerable!')
        ),
      'ability_frame' => 0,
      'ability_frame_animate' => array(2, 3),
      'ability_frame_offset' => array('x' => 0, 'y' => -5, 'z' => 6)
      );

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 120, 5, 10, $this_robot->print_robot_name().' fires the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(5, 0, 0),
      'success' => array(1, 5, -10, 10, 'The '.$this_ability->print_ability_name().' surrounded into the target!'),
      'failure' => array(1, -65, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(5, 0, 0),
      'success' => array(1, 5, -10, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(1, -65, -10, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Inflect a break on speed if the robot wasn't disabled
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_speed > 0
      && $this_ability->ability_results['this_result'] != 'failure' && $this_ability->ability_results['this_amount'] > 0){

      // If the ability flag was not set, attach the Proto Shield to the target
      if (!isset($target_robot->robot_attachments[$this_attachment_token])){

        // Attach this ability attachment to the robot using it
        $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $target_robot->update_session();

        // Target this robot's self
        $this_robot->robot_frame = 'base';
        $this_robot->update_session();
        $this_ability->target_options_update($this_attachment_info['attachment_create']);
        $target_robot->trigger_target($target_robot, $this_ability);

      }
      // Else if the ability flag was set, reinforce the shield by one more duration point
      else {

        // Collect the attachment from the robot to back up its info
        $this_attachment_info = $target_robot->robot_attachments[$this_attachment_token];
        $this_attachment_info['attachment_duration'] = 4;
        $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $target_robot->update_session();

        // Target the opposing robot
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 85, -10, -10, $this_robot->print_robot_name().' refreshed the '.$this_ability->print_ability_name().' puddle!<br /> '.$target_robot->print_robot_name().'&#39;s vulnerability has been extended!')
          ));
        $target_robot->trigger_target($target_robot, $this_ability);

      }

    }

    // Either way, update this ability's settings to prevent recovery
    $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->update_session();

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

    }
  );
?>