<?
// TELLY BURNER
$ability = array(
  'ability_name' => 'Telly Burner',
  'ability_token' => 'telly-burner',
  'ability_game' => 'MM02',
  'ability_class' => 'mecha',
  'ability_description' => 'The user summons a large flare that appears by the target and burns them to inflict damage.',
  'ability_energy' => 0,
  'ability_type' => 'flame',
  'ability_damage' => 14,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 2, 'impact' => 3);
    if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 4, 'impact' => 5); }
    elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 0, 'impact' => 1); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array($this_frames['target'], 0, 5, -10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], 0, 5, -10, 'The '.$this_ability->print_ability_name().' scorched the target!'),
      'failure' => array($this_frames['impact'], 0, 5, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], 0, 5, -10, 'The '.$this_ability->print_ability_name().' ignited the target!'),
      'failure' => array($this_frames['impact'], 0, 5, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Return true on success
    return true;
      
    }
  );
?>