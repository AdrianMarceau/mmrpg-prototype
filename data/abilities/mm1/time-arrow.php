<?
// TIME ARROW
$ability = array(
  'ability_name' => 'Time Arrow',
  'ability_token' => 'time-arrow',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/00A',
  'ability_master' => 'time-man',
  'ability_number' => 'DLN-00A',
  'ability_description' => 'The user directs a mysterious arrow at the target, dealing damage and cutting speed by {DAMAGE2}% for the duration of the next turn!',
  'ability_type' => 'time',
  'ability_energy' => 4,
  'ability_damage' => 12,
  'ability_damage2' => 20,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
    	'attachment_duration' => 1,
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

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(1, 125, 0, 10, $this_robot->print_robot_name().' throws a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -125, 0, 10, 'The '.$this_ability->print_ability_name().' sliced into the target!'),
      'failure' => array(1, -150, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -60, 0, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array(1, -90, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Inflect a break on speed if the robot wasn't disabled
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_speed > 0
      && $this_ability->ability_results['this_result'] != 'failure'
      && $this_ability->ability_results['this_amount'] > 0){

      // Define the speed mod amount for this ability
      $this_attachment_info['attachment_speed'] = ceil($target_robot->robot_speed * ($this_ability->ability_damage2 / 100));
      if (($target_robot->robot_speed - $this_attachment_info['attachment_speed']) < 1){ $this_attachment_info['attachment_speed'] = $target_robot->robot_speed - 1; }
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'ceil($target_robot->robot_speed * ($this_ability->ability_damage2 / 100))<br />ceil('.$target_robot->robot_speed.' * ('.$this_ability->ability_damage2.' / 100))');

      // Attach this ability attachment to the robot using it
      //$target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      //$target_robot->update_session();

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

    }

    // Either way, update this ability's settings to prevent recovery
    $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->update_session();

    // Return true on success
    return true;


    }
  );
?>