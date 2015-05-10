<?
// NITRON ICE
$ability = array(
  'ability_name' => 'Nitron Ice',
  'ability_token' => 'nitron-ice',
  'ability_game' => 'MM03',
  'ability_class' => 'mecha',
  'ability_description' => 'The user sends a frozen pillar toward the target to inflict damage.',
  'ability_type' => 'freeze',
  'ability_energy' => 0,
  'ability_damage' => 12,
  'ability_accuracy' => 98,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 1);
    //if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 1, 'impact' => 1); }
    //elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 2, 'impact' => 2); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'kickback' => array(0, 30, 0),
      'success' => array($this_frames['target'], 0, -40, 10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 0, 0),
      'success' => array($this_frames['impact'], -20, 0, 10, 'The '.$this_ability->print_ability_name().' frosted the target!', 2),
      'failure' => array($this_frames['impact'], -60, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;', 2)
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(20, 0, 0),
      'success' => array($this_frames['impact'], -20, 0, 10, 'The '.$this_ability->print_ability_name().' chilled the target!', 2),
      'failure' => array($this_frames['impact'], -60, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;', 2)
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Return true on success
    return true;
      
    }
  );
?>