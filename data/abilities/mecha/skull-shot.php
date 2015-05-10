<?
// SKULL SHOT
$ability = array(
  'ability_name' => 'Skull Shot',
  'ability_token' => 'skull-shot',
  'ability_game' => 'MM04',
  'ability_class' => 'mecha',
  'ability_description' => 'The user fires a tiny skull-shaped bomb at the target that explodes to inflict damage.',
  'ability_energy' => 0,
  'ability_type' => 'shadow',
  'ability_damage' => 17,
  'ability_accuracy' => 94,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 1);
    //if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 1, 'impact' => 1); }
    //elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 2, 'impact' => 2); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array($this_frames['target'], 140, 50, 10, $this_robot->print_robot_name().' fires a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
      'class' => 'ability',
      'ability_token' => $this_ability->ability_token,
      'ability_frame' => 2,
      'ability_frame_animate' => array(2),
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 5)
      );
    
    // Attach this ability attachment to the target robot temporarily
    $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    $target_robot->update_session();
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 0, 0),
      'success' => array($this_frames['impact'], 35, -5, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array($this_frames['impact'], -35, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 0, 0),
      'success' => array($this_frames['impact'], 35, -5, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array($this_frames['impact'], -35, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Remove this ability attachment from the target robot
    unset($target_robot->robot_attachments[$this_attachment_token]);
    $target_robot->update_session();
    
    // Return true on success
    return true;
      
    }
  );
?>