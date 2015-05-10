<?
// SNAPPER SOAKER
$ability = array(
  'ability_name' => 'Snapper Soaker',
  'ability_token' => 'snapper-soaker',
  'ability_game' => 'MM02',
  'ability_class' => 'mecha',
  'ability_description' => 'The user shoots a large blob of water at the target to inflict damage.',
  'ability_energy' => 0,
  'ability_type' => 'water',
  'ability_damage' => 15,
  'ability_accuracy' => 95,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 1);
    //if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 2, 'impact' => 3); }
    //elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 4, 'impact' => 5); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array($this_frames['target'], 90, 15, 10, $this_robot->print_robot_name().' sprays the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], -30, -10, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -30, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], -30, -10, 10, 'The '.$this_ability->print_ability_name().' was absorbed by the target!'),
      'failure' => array($this_frames['impact'], -30, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Return true on success
    return true;
      
    }
  );
?>