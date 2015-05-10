<?
// BULLET TACKLE
$ability = array(
  'ability_name' => 'Bullet Tackle',
  'ability_token' => 'bullet-tackle',
  'ability_game' => 'MM02',
  'ability_class' => 'mecha',
  'ability_description' => 'The user dashes across the field until it crashes into the target and explodes to inflict massive damage, destroying itself in the process.',
  'ability_type' => 'explode',
  'ability_energy' => 0,
  'ability_damage' => 30,
  'ability_accuracy' => 92,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 1);
    if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 2, 'impact' => 3); }
    elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 4, 'impact' => 5); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'slide',
      'kickback' => array(40, 0, 0),
      'success' => array($this_frames['target'], 40, 0, 10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->robot_frame_styles = 'display: none; ';
    $this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(30, 0, 0),
      'success' => array($this_frames['impact'], 10, 0, 10, 'The '.$this_ability->print_ability_name().'&#39;s explosion hit the target!'),
      'failure' => array($this_frames['target'], -30, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], 10, 0, 10, 'The '.$this_ability->print_ability_name().'&#39;s explosion invigorated the target!'),
      'failure' => array($this_frames['target'], -30, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    $this_robot->robot_frame_styles = '';
    $this_robot->update_session();
    
    // If the ability was a success we must destroy this robot
    if ($this_ability->ability_results['this_result'] != 'failure'){
      
      // Decrease this robot's energy stat to zero
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'percent' => true,
        'modifiers' => false,
        'type' => '',
        'frame' => 'damage',
        'success' => array(6, -9999, 5, -10, 'The '.$this_robot->print_robot_name().' was damaged by the blast!'),
        'failure' => array(6, -9999, 5, -10, $this_robot->print_robot_name().' was not affected by the blast&hellip;')
        ));
      $energy_damage_amount = $this_robot->robot_energy;
      $this_robot->trigger_damage($target_robot, $this_ability, $energy_damage_amount, false);
      $this_robot->robot_frame = 'defeat';
      $this_robot->trigger_disabled($target_robot, $this_ability);
      
    }
    
    // Return true on success
    return true;
      
    }
  );
?>