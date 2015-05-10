<?
// LEAP FROG
$ability = array(
  'ability_name' => 'Leap Frog',
  'ability_token' => 'leap-frog',
  'ability_game' => 'MM03',
  'ability_class' => 'mecha',
  'ability_description' => 'The user jumps up high and then lands on a target to inflict damage.',
  'ability_type' => 'impact',
  'ability_target' => 'select',
  'ability_energy' => 0,
  'ability_damage' => 18,
  'ability_accuracy' => 96,
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
      'kickback' => array(15, 30, 0),
      'success' => array($this_frames['target'], 0, 10, 10, $this_robot->print_robot_name().' uses the  '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->robot_frame_styles = 'display: none; ';
    $this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 10, 0),
      'success' => array($this_frames['impact'], 0, -10, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -30, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 10, 0),
      'success' => array($this_frames['impact'], 0, -10, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -30, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
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