<?
// POWER FAN
$ability = array(
  'ability_name' => 'Power Fan',
  'ability_token' => 'power-fan',
  'ability_game' => 'MM02',
  'ability_class' => 'mecha',
  'ability_description' => 'The user blasts the target with a powerful gust of air using its fan to inflict damage.',
  'ability_energy' => 0,
  'ability_type' => 'wind',
  'ability_damage' => 16,
  'ability_accuracy' => 98,
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
      'success' => array($this_frames['target'], 100, -10, 10, $this_robot->print_robot_name().' activated the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], -60, -10, 10, 'The '.$this_ability->print_ability_name().'&#39;s whirlwind crashed into the target!'),
      'failure' => array($this_frames['impact'], -60, -10, -10, 'The '.$this_ability->print_ability_name().'&#39;s whirlwind missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], -60, -10, 10, 'The '.$this_ability->print_ability_name().'&#39;s whirlwind was absorbed by the target!'),
      'failure' => array($this_frames['impact'], -60, -10, -10, 'The '.$this_ability->print_ability_name().'&#39;s whirlwind missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Return true on success
    return true;
      
    }
  );
?>