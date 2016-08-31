<?
// RHYTHM BUSTER
$ability = array(
  'ability_name' => 'Rhythm Buster',
  'ability_token' => 'rhythm-buster',
  'ability_game' => 'MM00',
  'ability_description' => 'The user charges on the first turn to build power and recover life energy by {RECOVERY2}%, then releases a powerful shot on the second to inflict {DAMAGE}% damage to mobility sytems!',
  'ability_energy' => 2,
  'ability_damage' => 36,
  'ability_damage_percent' => true,
  'ability_recovery2' => 10,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
      'ability_frame' => 0,
      'ability_frame_animate' => array(1, 2, 1, 0),
      'ability_frame_offset' => array('x' => -10, 'y' => -10, 'z' => -20)
      );
    // Loop through each existing attachment and alter the start frame by one
    foreach ($this_robot->robot_attachments AS $key => $info){ array_push($this_attachment_info['ability_frame_animate'], array_shift($this_attachment_info['ability_frame_animate'])); }

    // If the ability flag was not set, this ability begins charging
    if (!isset($this_robot->robot_attachments[$this_attachment_token])){

      // Target this robot's self
      $this_ability->target_options_update(array(
        'frame' => 'defend',
        'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Increase this robot's speed stat slightly
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'rates' => array(100, 0, 0),
        'success' => array(2, -10, 0, -10, $this_robot->print_name().'&#39;s energy was restored!'),
        'failure' => array(2, -10, 0, -10, $this_robot->print_name().'&#39;s energy was not affected&hellip;')
        ));
      $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery2 / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);

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
        'frame' => 'shoot',
        'kickback' => array(-5, 0, 0),
        'success' => array(3, 100, -15, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!'),
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'speed',
        'percent' => true,
        'modifiers' => false,
        'kickback' => array(20, 0, 0),
        'success' => array(3, -110, -15, 10, 'A massive energy shot hit the target!'),
        'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed&hellip;')
        ));
      $speed_damage_amount = ceil($target_robot->robot_base_speed * ($this_ability->ability_damage / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount, false, array('apply_modifiers' => false));

    }

    // Return true on success
    return true;

    },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;

    // If the ability flag had already been set, reduce the weapon energy to zero
    if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_energy = 0; }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->ability_energy = $this_ability->ability_base_energy; }
    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>