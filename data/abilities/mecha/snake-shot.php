<?
// SNAKE SHOT
$ability = array(
  'ability_name' => 'Snake Shot',
  'ability_token' => 'snake-shot',
  'ability_game' => 'MM03',
  'ability_class' => 'mecha',
  'ability_description' => 'The user fires a barrage of sepentine bullets at the target to inflict damage.',
  'ability_type' => 'nature',
  'ability_energy' => 0,
  'ability_damage' => 6,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Attach three bullet attachments to the robot
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array('class' => 'ability', 'ability_token' => $this_ability->ability_token, 'ability_frame' => 0, 'ability_frame_animate' => array(1, 2));
    $this_robot->robot_attachments[$this_attachment_token.'_1'] = $this_attachment_info;
    $this_robot->robot_attachments[$this_attachment_token.'_2'] = $this_attachment_info;
    $this_robot->robot_attachments[$this_attachment_token.'_1']['ability_frame_offset'] = array('x' => 75, 'y' => 24, 'z' => 10);
    $this_robot->robot_attachments[$this_attachment_token.'_2']['ability_frame_offset'] = array('x' => 95, 'y' => -24, 'z' => 10);
    $this_robot->update_session();
    
    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 115, 24, 10, $this_ability->print_ability_name().' fires a barrage of bullets!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Update the two bullet's animation frames
    $this_robot->robot_attachments[$this_attachment_token.'_1']['ability_frame'] = 1;
    $this_robot->robot_attachments[$this_attachment_token.'_2']['ability_frame'] = 1;
    $this_robot->update_session();
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(5, 0, 0),
      'success' => array(1, -80, 24, 10, 'A bullet hit!'),
      'failure' => array(1, -100, 24, -10, 'One of the bullets missed!')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -80, 24, 10, 'A bullet hit!'),
      'failure' => array(1, -100, 24, -10, 'One of the bullets missed!')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Ensure the target has not been disabled
    if ($target_robot->robot_status != 'disabled'){
      
      // Define the success/failure text variables
      $success_text = '';
      $failure_text = '';
      
      // Adjust damage/recovery text based on results
      if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another bullet hit!'; }
      if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another bullet missed!'; }
      
      // Remove the second extra bullet attached to the robot
      if (isset($this_robot->robot_attachments[$this_attachment_token.'_2'])){
        unset($this_robot->robot_attachments[$this_attachment_token.'_2']);
        $this_robot->update_session();
      }
      
      // Update the remaining bullet's animation frame
      $this_robot->robot_attachments[$this_attachment_token.'_1']['ability_frame'] = 2;
      $this_robot->update_session();
      
      // Attempt to trigger damage to the target robot again
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(10, 0, 0),
        'success' => array(2, -40, -24, 10, $success_text),
        'failure' => array(2, -60, -24, -10, $failure_text)
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(2, -40, -24, 10, $success_text),
        'failure' => array(2, -60, -24, -10, $failure_text)
        ));
      $target_robot->trigger_damage($this_robot, $this_ability,  $energy_damage_amount);
      
      // Ensure the target has not been disabled
      if ($target_robot->robot_status != 'disabled'){
        
        // Adjust damage/recovery text based on results again
        if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another bullet hit!'; }
        elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A third bullet hit!'; }
        if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another bullet missed!'; }
        elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A third bullet missed!'; }
        
        // Remove the first extra bullet
        if (isset($this_robot->robot_attachments[$this_attachment_token.'_1'])){
          unset($this_robot->robot_attachments[$this_attachment_token.'_1']);
          $this_robot->update_session();
        }
        
        // Attempt to trigger damage to the target robot a third time
        $this_ability->damage_options_update(array(
          'kind' => 'energy',
          'kickback' => array(15, 0, 0),
          'success' => array(1, -70, 24, 10, $success_text),
          'failure' => array(1, -90, 24, -10, $failure_text)
          ));
        $this_ability->recovery_options_update(array(
          'kind' => 'energy',
          'frame' => 'taunt',
          'kickback' => array(0, 0, 0),
          'success' => array(1, -70, 24, 10, $success_text),
          'failure' => array(1, -90, 24, -10, $failure_text)
          ));
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
        
      }
           
    }
    
    // Remove the second bullet
    if (isset($this_robot->robot_attachments[$this_attachment_token.'_2'])){
      unset($this_robot->robot_attachments[$this_attachment_token.'_2']);
      $this_robot->update_session();
    }
    
    // Remove the third bullet
    if (isset($this_robot->robot_attachments[$this_attachment_token.'_1'])){
      unset($this_robot->robot_attachments[$this_attachment_token.'_1']);
      $this_robot->update_session();
    }
    
    // Return true on success
    return true;
        
  }
  );
?>