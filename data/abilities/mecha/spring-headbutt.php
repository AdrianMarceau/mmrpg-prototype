<?
// SPRING HEADBUTT
$ability = array(
  'ability_name' => 'Spring Headbutt',
  'ability_token' => 'spring-headbutt',
  'ability_game' => 'MM02',
  'ability_class' => 'mecha',
  'ability_description' => 'The user slides across the ground toward the target and then springs suddenly inflict damage.',
  'ability_type' => 'swift',
  'ability_energy' => 0,
  'ability_speed' => 3,
  'ability_damage' => 14,
  'ability_accuracy' => 98,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 1);
    if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 2, 'impact' => 3); }
    elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 4, 'impact' => 5); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'kickback' => array(60, 0, 0),
      'success' => array($this_frames['target'], 60, 0, 10, $this_robot->print_robot_name().' uses the  '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->robot_frame_styles = 'display: none; ';
    $this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 40, 0),
      'success' => array($this_frames['impact'], 40, -50, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -120, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 30, 0),
      'success' => array($this_frames['impact'], 20, -40, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -120, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    $this_robot->robot_frame_styles = '';
    $this_robot->update_session();
    
    // Return true on success
    return true;
      
    }
  );
?>