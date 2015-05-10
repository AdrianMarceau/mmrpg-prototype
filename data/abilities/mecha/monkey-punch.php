<?
// MONKEY PUNCH
$ability = array(
  'ability_name' => 'Monkey Punch',
  'ability_token' => 'monkey-punch',
  'ability_game' => 'MM03',
  'ability_class' => 'mecha',
  'ability_description' => 'The user leaps toward target and punches them with a powerful fist to inflict damage.',
  'ability_type' => 'impact',
  'ability_energy' => 0,
  'ability_damage' => 24,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 1, 'impact' => 0);
    if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 3, 'impact' => 2); }
    elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 5, 'impact' => 4); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'kickback' => array(40, 30, 0),
      'success' => array($this_frames['target'], -20, -15, -10, $this_robot->print_robot_name().' uses the  '.$this_ability->print_ability_name().'!')
      ));
    //$this_robot->robot_frame_styles = 'display: none; ';
    //$this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 10, 0),
      'success' => array($this_frames['impact'], -20, -10, 10, 'The '.$this_ability->print_ability_name().' strikes the target!'),
      'failure' => array($this_frames['impact'], -30, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 10, 0),
      'success' => array($this_frames['impact'], -20, -10, 10, 'The '.$this_ability->print_ability_name().' tempers the target!'),
      'failure' => array($this_frames['impact'], -30, -10, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_robot->robot_frame_styles = 'display: none; ';
    $this_robot->update_session();
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    $this_robot->robot_frame_styles = '';
    $this_robot->update_session();
    
    // Return true on success
    return true;
      
    }
  );
?>